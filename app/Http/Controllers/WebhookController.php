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
    /**
     * Webhook de Notificação de Pagamento do Mercado Pago.
     * Processa notificações e consulta diretamente a API oficial do Mercado Pago para validação.
     */
    public function mercadoPago(Request $request)
    {
        $xSignature = $request->header('x-signature', '');
        $xRequestId = $request->header('x-request-id', '');
        $dataId = (string) ($request->input('data.id') ?? $request->input('id') ?? '');

        // 1. Validação obrigatória de assinatura quando configurada
        $webhookSecret = config('services.mercadopago.webhook_secret', env('MERCADO_PAGO_WEBHOOK_SECRET'));
        if (!empty($webhookSecret)) {
            if (empty($xSignature) || !MercadoPagoService::validarAssinatura($xSignature, $xRequestId, $dataId, $webhookSecret)) {
                Log::warning('Webhook Mercado Pago rejeitado por assinatura inválida ou ausente.', [
                    'ip' => $request->ip(),
                    'data_id' => $dataId,
                ]);
                return response()->json(['error' => 'Assinatura inválida'], 401);
            }
        }

        $type = $request->input('type') ?? $request->input('action');

        if ($type === 'payment' || $request->has('data.id') || $request->has('id')) {
            $paymentId = $dataId;

            if (empty($paymentId)) {
                return response()->json(['error' => 'ID do pagamento não informado'], 400);
            }

            $pagamento = Pagamento::withoutGlobalScopes()->where('mercado_pago_id', $paymentId)->first();

            if (!$pagamento) {
                // Pagamento não pertence a este sistema ou já foi tratado
                return response()->json(['status' => 'ignored', 'message' => 'Pagamento não localizado'], 200);
            }

            $newStatus = null;

            // 2. SEGURANÇA CRÍTICA: NUNCA confiar no status enviado no corpo da requisição!
            // Consulta a API oficial do Mercado Pago usando o token do revendedor ou da aplicação
            if ($pagamento->user) {
                $accessToken = $pagamento->user->companyDetail?->access_token;
                $consultado = MercadoPagoService::consultarPagamento($paymentId, $accessToken);

                if ($consultado && isset($consultado['status'])) {
                    // Validação de integridade do valor pago para prevenir fraude de valor alterado
                    $valorPago = (float) ($consultado['transaction_amount'] ?? 0);
                    $valorEsperado = (float) $pagamento->valor;

                    if ($consultado['status'] === 'approved' && $valorPago < $valorEsperado) {
                        Log::error("Alerta de fraude no pagamento MP #{$paymentId}: valor pago (R$ {$valorPago}) inferior ao valor do plano (R$ {$valorEsperado}).");
                        return response()->json(['error' => 'Valor divergente'], 400);
                    }

                    $newStatus = $consultado['status'];
                }
            }

            // Fallback exclusivo para testes locais mockados (MP_...)
            if (!$newStatus && str_starts_with($paymentId, 'MP_') && app()->isLocal()) {
                $newStatus = $request->input('status');
            }

            if ($newStatus) {
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
        if (!$revendedor) {
            return;
        }

        // Busca o template customizado do revendedor ou o template padrão do sistema
        $template = Template::withoutGlobalScopes()
            ->where(function ($q) use ($revendedor) {
                $q->where('user_id', $revendedor->id)
                  ->orWhereNull('user_id');
            })
            ->where('finalidade', 'cobranca')
            ->orderByRaw('user_id IS NULL ASC')
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
