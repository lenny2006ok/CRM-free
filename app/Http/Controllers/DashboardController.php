<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Pagamento;
use App\Models\Plano;
use App\Models\Conexao;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

        $totalClientes = Cliente::count();
        $clientesAtivos = Cliente::where('vencimento', '>=', $hoje)->count();
        $clientesVencidos = Cliente::where('vencimento', '<', $hoje)->count();
        $vencendoHoje = Cliente::where('vencimento', $hoje)->count();

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
