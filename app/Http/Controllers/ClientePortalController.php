<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Pagamento;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;

class ClientePortalController extends Controller
{
    public function portal(Request $request)
    {
        $cliente = null;
        $pagamentoPendente = null;

        if ($request->filled('whatsapp')) {
            $whats = preg_replace('/\D/', '', $request->whatsapp);
            $cliente = Cliente::withoutGlobalScopes()
                ->where('whatsapp', 'like', "%{$whats}%")
                ->with(['plano', 'servidor', 'user.companyDetail'])
                ->first();
        }

        if ($cliente) {
            $pagamentoPendente = Pagamento::withoutGlobalScopes()
                ->where('cliente_id', $cliente->id)
                ->where('status', 'pending')
                ->latest()
                ->first();
        }

        return view('portal.index', compact('cliente', 'pagamentoPendente'));
    }

    public function gerarPix(Request $request, $id)
    {
        $cliente = Cliente::withoutGlobalScopes()->with(['plano', 'user.companyDetail'])->findOrFail($id);

        if (!$cliente->plano) {
            return back()->with('error', 'Nenhum plano associado a esta assinatura. Contate o suporte.');
        }

        $pagamento = MercadoPagoService::criarPix($cliente, $cliente->plano, $cliente->user);

        return redirect()->route('cliente.pagamento', ['id' => $pagamento->mercado_pago_id]);
    }

    public function visualizacaoPagamento($id)
    {
        $pagamento = Pagamento::withoutGlobalScopes()->with(['cliente.user', 'plano'])->where('mercado_pago_id', $id)->firstOrFail();

        return view('portal.pagamento', compact('pagamento'));
    }

    public function checarStatus($id)
    {
        $pagamento = Pagamento::withoutGlobalScopes()->where('mercado_pago_id', $id)->firstOrFail();

        return response()->json([
            'status' => $pagamento->status,
            'aprovado' => ($pagamento->status === 'approved'),
        ]);
    }
}
