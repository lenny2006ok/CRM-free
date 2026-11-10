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

        $hoje = Carbon::today()->toDateString();

        // ⚡ Performance optimization: Consolidate 4 separate COUNT queries on `clientes` into 1 single
        // conditional aggregation query. Reduces database roundtrips and table scans by 75%.
        // Use date(vencimento) to handle both DATE strings and DATETIME representations uniformly across drivers.
        $clienteStats = Cliente::toBase()
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN date(vencimento) >= ? THEN 1 ELSE 0 END) as ativos,
                SUM(CASE WHEN date(vencimento) < ? THEN 1 ELSE 0 END) as vencidos,
                SUM(CASE WHEN date(vencimento) = ? THEN 1 ELSE 0 END) as hoje
            ', [$hoje, $hoje, $hoje])
            ->first();

        $totalClientes = (int) ($clienteStats->total ?? 0);
        $clientesAtivos = (int) ($clienteStats->ativos ?? 0);
        $clientesVencidos = (int) ($clienteStats->vencidos ?? 0);
        $vencendoHoje = (int) ($clienteStats->hoje ?? 0);

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
