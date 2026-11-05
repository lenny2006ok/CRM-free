@extends('layouts.app')

@section('title', 'Histórico de Pagamentos - Gestor IPTV')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-white tracking-tight">Histórico de Cobranças Pix</h2>
            <p class="text-sm text-slate-400">Acompanhamento em tempo real de pagamentos gerados e recebidos.</p>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="text-xs uppercase bg-slate-800/60 text-slate-400">
                    <tr>
                        <th class="py-3.5 px-4">ID Transação</th>
                        <th class="py-3.5 px-4">Cliente</th>
                        <th class="py-3.5 px-4">Plano</th>
                        <th class="py-3.5 px-4">Valor</th>
                        <th class="py-3.5 px-4">Data Pagamento</th>
                        <th class="py-3.5 px-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($pagamentos as $pag)
                        <tr class="hover:bg-slate-800/40">
                            <td class="py-3.5 px-4 font-mono text-xs text-indigo-300">
                                {{ $pag->mercado_pago_id }}
                            </td>
                            <td class="py-3.5 px-4 font-medium text-white">
                                {{ $pag->cliente?->nome ?? 'Cliente Não Informado' }}
                                <div class="text-xs text-slate-500 font-mono">{{ $pag->cliente?->whatsapp }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                {{ $pag->plano?->nome ?? 'Plano Padrão' }}
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-white">
                                R$ {{ number_format($pag->valor, 2, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-xs text-slate-400">
                                {{ $pag->payment_date ? $pag->payment_date->format('d/m/Y') : '-' }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if($pag->status === 'approved')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        Aprovado
                                    </span>
                                @elseif($pag->status === 'rejected')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                        Rejeitado
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                        Pendente Pix
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">
                                Nenhum registro de pagamento encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-800">
            {{ $pagamentos->links() }}
        </div>
    </div>
</div>
@endsection
