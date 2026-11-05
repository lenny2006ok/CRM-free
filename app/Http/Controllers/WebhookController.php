<?php

namespace App\Http\Controllers;

use App\Models\Pagamento;
use App\Models\Cliente;
use App\Models\Template;
use App\Services\MercadoPagoService;
use App\Services\EvolutionApiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function mercadoPago(Request $request)
    {
        $xSignature = $request->header('x-signature', '');
        $xRequestId = $request->header('x-request-id', '');
        $dataId = (string) ($request->input('data.id') ?? $request->input('id') ?? '');

        if (!empty($xSignature) && !MercadoPagoService::validarAssinatura($xSignature, $xRequestId, $dataId)) {
            Log::warning('Webhook Mercado Pago rejeitado por assinatura inválida.');
            return response()->json(['error' => 'Assinatura inválida'], 401);
        }

        $type = $request->input('type') ?? $request->input('action');

        if ($type === 'payment' || $request->has('data.id') || $request->has('id')) {
            $paymentId = $dataId;
            $pagamento = Pagamento::withoutGlobalScopes()->where('mercado_pago_id', $paymentId)->first();
            $newStatus = $request->input('status') ?? $request->input('data.status');

            if (!$newStatus && $pagamento && $pagamento->user) {
                $consultado = MercadoPagoService::consultarPagamento($paymentId, $pagamento->user->companyDetail?->access_token);
                if ($consultado) {
                    $newStatus = $consultado['status'] ?? null;
                }
            }

            if ($pagamento && $newStatus) {
                $statusAnterior = $pagamento->status;
                $pagamento->status = $newStatus;

                if ($newStatus === 'approved') {
                    $pagamento->payment_date = now();
                }
                $pagamento->save();

                if ($newStatus === 'approved' && $statusAnterior !== 'approved') {
                    $this->processarRenovacaoCliente($pagamento);
                }
            }
        }

        return response()->json(['status' => 'ok'], 200);
    }

    protected function processarRenovacaoCliente(Pagamento $pagamento): void
    {
        $cliente = Cliente::withoutGlobalScopes()->find($pagamento->cliente_id);
        if (!$cliente) {
            return;
        }

        $diasPlano = 30;
        if ($pagamento->plano) {
            $diasPlano = $pagamento->plano->duracao ?: 30;
        }

        $baseDate = ($cliente->vencimento && $cliente->vencimento->isFuture())
            ? $cliente->vencimento
            : Carbon::today();

        $novaData = $baseDate->copy()->addDays($diasPlano);
        $cliente->vencimento = $novaData;
        $cliente->save();

        Log::info("Cliente #{$cliente->id} ({$cliente->nome}) renovado até {$novaData->format('Y-m-d')}.");

        $revendedor = $cliente->user;
        $template = Template::withoutGlobalScopes()
            ->where('user_id', $revendedor->id)
            ->where('finalidade', 'cobranca')
            ->first();

        if ($template && $cliente->notificacoes) {
            $msg = $template->renderizar([
                'nome' => $cliente->nome,
                'vencimento' => $novaData->format('d/m/Y'),
                'valor' => number_format((float) $pagamento->valor, 2, ',', '.'),
            ]);

            EvolutionApiService::enviarTexto($revendedor, $cliente->whatsapp, $msg);
        }
    }

    public function evolution(Request $request)
    {
        $token = $request->header('apikey') ?? $request->header('Authorization') ?? $request->query('token');
        $expectedToken = config('services.evolution.webhook_token', env('EVOLUTION_WEBHOOK_TOKEN'));

        if (!empty($expectedToken) && $token !== $expectedToken && $token !== 'Bearer ' . $expectedToken) {
            return response()->json(['error' => 'Não autorizado'], 401);
        }

        return response()->json(['status' => 'received'], 200);
    }
}
