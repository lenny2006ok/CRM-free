<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Pagamento;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isCliente()) {
            return redirect()->route('cliente.portal');
        }

        $hoje = Carbon::today()->toDateString();

        // ⚡ Bolt Optimization: Consolidate 4 separate COUNT(*) queries into 1 conditional aggregation query.
        // Uses toBase() to avoid Eloquent model hydration overhead for aggregation stats.
        // date(vencimento) ensures clean calendar date comparison across database drivers.
        $clienteStats = Cliente::toBase()
            ->selectRaw('
                COUNT(*) as total,
                COUNT(CASE WHEN date(vencimento) >= ? THEN 1 END) as ativos,
                COUNT(CASE WHEN date(vencimento) < ? THEN 1 END) as vencidos,
                COUNT(CASE WHEN date(vencimento) = ? THEN 1 END) as vencendo_hoje
            ', [$hoje, $hoje, $hoje])
            ->first();

        $totalClientes = (int) ($clienteStats->total ?? 0);
        $clientesAtivos = (int) ($clienteStats->ativos ?? 0);
        $clientesVencidos = (int) ($clienteStats->vencidos ?? 0);
        $vencendoHoje = (int) ($clienteStats->vencendo_hoje ?? 0);

        $receitaPrevista = Cliente::join('planos', 'clientes.plano_id', '=', 'planos.id')
            ->sum('planos.preco');

        $receitaMes = Pagamento::where('status', 'approved')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('valor');

        $ultimosClientes = Cliente::with('plano', 'servidor')
            ->latest()
            ->take(5)
            ->get();

        $ultimosPagamentos = Pagamento::with('cliente', 'plano')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'totalClientes',
            'clientesAtivos',
            'clientesVencidos',
            'vencendoHoje',
            'receitaPrevista',
            'receitaMes',
            'ultimosClientes',
            'ultimosPagamentos'
        ));
    }
}
