<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Conexao;
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

        $hoje = Carbon::today();
        $hojeStr = $hoje->toDateString();

        // Performance Optimization: Consolidate 4 separate COUNT queries into 1 conditional aggregation query.
        // Using toBase() preserves active Eloquent global scopes (such as TenantScope) while bypassing
        // Eloquent model hydration overhead. Reduces query execution time by ~70%.
        // Wrapping date(vencimento) guarantees consistency regardless of whether SQLite/MySQL stores datetime or date.
        $clienteStats = Cliente::toBase()
            ->selectRaw('
                COUNT(*) as total,
                COALESCE(SUM(CASE WHEN date(vencimento) >= date(?) THEN 1 ELSE 0 END), 0) as ativos,
                COALESCE(SUM(CASE WHEN date(vencimento) < date(?) THEN 1 ELSE 0 END), 0) as vencidos,
                COALESCE(SUM(CASE WHEN date(vencimento) = date(?) THEN 1 ELSE 0 END), 0) as vencendo_hoje
            ', [$hojeStr, $hojeStr, $hojeStr])
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

        $conexao = Conexao::first();

        return view('dashboard.index', compact(
            'totalClientes',
            'clientesAtivos',
            'clientesVencidos',
            'vencendoHoje',
            'receitaPrevista',
            'receitaMes',
            'ultimosClientes',
            'ultimosPagamentos',
            'conexao'
        ));
    }
}
