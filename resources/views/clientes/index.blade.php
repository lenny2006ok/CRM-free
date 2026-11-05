@extends('layouts.app')

@section('title', 'Clientes - Gestor IPTV')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-white tracking-tight">Gerenciamento de Assinantes</h2>
            <p class="text-sm text-slate-400">Controle completo de prazos, telas, servidores e cobranças Pix.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('clientes.create') }}" class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm shadow-lg shadow-indigo-600/30 transition">
                <span>➕</span> <span>Adicionar Cliente</span>
            </a>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl flex flex-wrap items-center gap-4">
        <form method="GET" action="{{ route('clientes.index') }}" class="flex-1 flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[240px]">
                <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Buscar por nome, WhatsApp ou usuário IPTV..." class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3.5 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500">
            </div>
            <div>
                <select name="status" class="bg-slate-950 border border-slate-700 rounded-lg px-3.5 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    <option value="">Todos os Status</option>
                    <option value="ativo" {{ request('status') === 'ativo' ? 'selected' : '' }}>Ativos</option>
                    <option value="vencido" {{ request('status') === 'vencido' ? 'selected' : '' }}>Vencidos</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg text-sm font-medium transition">
                🔍 Filtrar
            </button>
            @if(request()->hasAny(['busca', 'status']))
                <a href="{{ route('clientes.index') }}" class="px-3 py-2 text-xs text-slate-400 hover:text-white">Limpar</a>
            @endif
        </form>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="text-xs uppercase bg-slate-800/60 text-slate-400">
                    <tr>
                        <th class="py-3.5 px-4">Nome & WhatsApp</th>
                        <th class="py-3.5 px-4">Plano & Servidor</th>
                        <th class="py-3.5 px-4">IPTV / Telas</th>
                        <th class="py-3.5 px-4">Vencimento</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Ações Rápidas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($clientes as $cliente)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-white">{{ $cliente->nome }}</div>
                                <div class="text-xs text-slate-400 font-mono">{{ $cliente->whatsapp }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="text-white">{{ $cliente->plano?->nome ?? 'Sem plano' }}</div>
                                <div class="text-xs text-slate-400">{{ $cliente->servidor?->nome ?? 'Sem servidor' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-mono text-xs text-indigo-300">User: {{ $cliente->iptv_nome ?: '-' }}</div>
                                <div class="text-xs text-slate-400">{{ $cliente->numero_de_telas }} tela(s)</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-medium {{ $cliente->isVencido() ? 'text-rose-400' : 'text-slate-200' }}">
                                    {{ $cliente->vencimento ? $cliente->vencimento->format('d/m/Y') : '-' }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    @if($cliente->isVencido())
                                        Há {{ abs($cliente->diasAteVencimento()) }} dia(s)
                                    @else
                                        Em {{ $cliente->diasAteVencimento() }} dia(s)
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($cliente->isVencido())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                        Vencido
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        Ativo
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <form method="POST" action="{{ route('clientes.enviar-pix', $cliente) }}">
                                        @csrf
                                        <button type="submit" title="Enviar Cobrança Pix via WhatsApp" class="p-1.5 rounded-lg bg-emerald-600/10 hover:bg-emerald-600/20 text-emerald-400 text-xs font-medium transition">
                                            📲 Pix
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('clientes.renovar', $cliente) }}">
                                        @csrf
                                        <button type="submit" title="Renovar +30 dias" class="p-1.5 rounded-lg bg-indigo-600/10 hover:bg-indigo-600/20 text-indigo-400 text-xs font-medium transition">
                                            🔄 Renovar
                                        </button>
                                    </form>

                                    <a href="{{ route('clientes.edit', $cliente) }}" title="Editar" class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs transition">
                                        ✏️
                                    </a>

                                    <form method="POST" action="{{ route('clientes.destroy', $cliente) }}" onsubmit="return confirm('Deseja excluir este cliente?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Excluir" class="p-1.5 rounded-lg bg-rose-600/10 hover:bg-rose-600/20 text-rose-400 text-xs transition">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">
                                Nenhum cliente encontrado. Adicione novos assinantes para começar!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-800">
            {{ $clientes->links() }}
        </div>
    </div>
</div>
@endsection
