@extends('layouts.app')

@section('title', 'Dashboard - Gestor IPTV')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-white tracking-tight">Painel de Controle</h2>
            <p class="text-sm text-slate-400">Visão geral do seu negócio e status dos seus assinantes.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('clientes.create') }}" class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm shadow-lg shadow-indigo-600/30 transition">
                <span>➕</span> <span>Novo Assinante</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total de Clientes</span>
                <span class="p-2 rounded-lg bg-indigo-500/10 text-indigo-400">👥</span>
            </div>
            <p class="mt-4 text-3xl font-extrabold text-white">{{ $totalClientes }}</p>
            <p class="mt-1 text-xs text-emerald-400 font-medium">{{ $clientesAtivos }} ativos</p>
        </div>

        <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Vencendo Hoje</span>
                <span class="p-2 rounded-lg bg-amber-500/10 text-amber-400">⏰</span>
            </div>
            <p class="mt-4 text-3xl font-extrabold text-white">{{ $vencendoHoje }}</p>
            <p class="mt-1 text-xs text-amber-400 font-medium">Cobranças agendadas</p>
        </div>

        <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Vencidos</span>
                <span class="p-2 rounded-lg bg-rose-500/10 text-rose-400">🚫</span>
            </div>
            <p class="mt-4 text-3xl font-extrabold text-white">{{ $clientesVencidos }}</p>
            <p class="mt-1 text-xs text-rose-400 font-medium">Requer atenção</p>
        </div>

        <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Receita Prevista</span>
                <span class="p-2 rounded-lg bg-emerald-500/10 text-emerald-400">💰</span>
            </div>
            <p class="mt-4 text-3xl font-extrabold text-white">R$ {{ number_format($receitaPrevista, 2, ',', '.') }}</p>
            <p class="mt-1 text-xs text-slate-400">Recebido este mês: <strong class="text-emerald-400">R$ {{ number_format($receitaMes, 2, ',', '.') }}</strong></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold text-white">Últimos Assinantes</h3>
                <a href="{{ route('clientes.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium">Ver todos &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="text-xs uppercase bg-slate-800/50 text-slate-400">
                        <tr>
                            <th class="py-3 px-3">Cliente</th>
                            <th class="py-3 px-3">Vencimento</th>
                            <th class="py-3 px-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse($ultimosClientes as $c)
                            <tr class="hover:bg-slate-800/30">
                                <td class="py-3 px-3 font-medium text-white">{{ $c->nome }}</td>
                                <td class="py-3 px-3">{{ $c->vencimento ? $c->vencimento->format('d/m/Y') : '-' }}</td>
                                <td class="py-3 px-3">
                                    @if($c->isVencido())
                                        <span class="px-2 py-0.5 rounded text-xs bg-rose-500/10 text-rose-400 border border-rose-500/20">Vencido</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-xs bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Ativo</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-4 text-center text-slate-500">Nenhum cliente cadastrado ainda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold text-white">Últimos Pagamentos Pix</h3>
                <a href="{{ route('pagamentos.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium">Ver histórico &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="text-xs uppercase bg-slate-800/50 text-slate-400">
                        <tr>
                            <th class="py-3 px-3">Cliente</th>
                            <th class="py-3 px-3">Valor</th>
                            <th class="py-3 px-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse($ultimosPagamentos as $p)
                            <tr class="hover:bg-slate-800/30">
                                <td class="py-3 px-3 font-medium text-white">{{ $p->cliente?->nome ?? 'Cliente' }}</td>
                                <td class="py-3 px-3">R$ {{ number_format($p->valor, 2, ',', '.') }}</td>
                                <td class="py-3 px-3">
                                    @if($p->status === 'approved')
                                        <span class="px-2 py-0.5 rounded text-xs bg-emerald-500/10 text-emerald-400">Aprovado</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-xs bg-amber-500/10 text-amber-400">Pendente</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-4 text-center text-slate-500">Nenhum pagamento registrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
