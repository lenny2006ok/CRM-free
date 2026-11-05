@extends('layouts.app')

@section('title', 'Campanhas em Massa - Gestor IPTV')

@section('content')
<div class="space-y-6" x-data="{ openModal: false }">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-white tracking-tight">Disparos em Massa</h2>
            <p class="text-sm text-slate-400">Envie informativos, novidades ou comunicados para todos os seus assinantes.</p>
        </div>
        <button @click="openModal = true" class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm shadow-lg shadow-indigo-600/30 transition">
            <span>📢</span> <span>Nova Campanha</span>
        </button>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="text-xs uppercase bg-slate-800/60 text-slate-400">
                    <tr>
                        <th class="py-3.5 px-4">Nome da Campanha</th>
                        <th class="py-3.5 px-4">Horário / Data</th>
                        <th class="py-3.5 px-4">Recorrente Diária</th>
                        <th class="py-3.5 px-4">Último Disparo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($campanhas as $camp)
                        <tr class="hover:bg-slate-800/40">
                            <td class="py-3.5 px-4 font-medium text-white">{{ $camp->nome }}</td>
                            <td class="py-3.5 px-4 text-xs">
                                <div>Horário: {{ $camp->horario }}</div>
                                <div class="text-slate-400">Data: {{ $camp->data ? $camp->data->format('d/m/Y') : 'Imediato/Todos os dias' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($camp->enviar_diariamente)
                                    <span class="px-2 py-0.5 rounded text-xs bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Sim (Diário)</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-xs bg-slate-800 text-slate-400">Único</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-xs text-slate-400">
                                {{ $camp->ultima_execucao ? $camp->ultima_execucao->format('d/m/Y H:i') : 'Pendente' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-slate-500">
                                Nenhuma campanha criada até o momento.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="openModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="openModal = false" class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 space-y-5">
            <h3 class="text-lg font-bold text-white">Agendar Disparo em Massa</h3>

            <form method="POST" action="{{ route('campanhas.salvar') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-400 mb-1">Nome da Campanha</label>
                    <input type="text" name="nome" required placeholder="Ex: Aviso Manutenção / Novos Canais" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-400 mb-1">Horário do Disparo</label>
                        <input type="time" name="horario" required value="10:00" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-400 mb-1">Data Específica</label>
                        <input type="date" name="data" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-400 mb-1">Texto da Mensagem</label>
                    <textarea name="mensagem" rows="4" required placeholder="Escreva a mensagem aqui..." class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500"></textarea>
                </div>

                <div>
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" name="enviar_diariamente" value="1" class="w-4 h-4 rounded text-indigo-600 focus:ring-0 bg-slate-950 border-slate-700">
                        <span class="text-sm text-slate-300">Repetir envio todos os dias neste horário</span>
                    </label>
                </div>

                <div class="pt-4 flex items-center justify-end space-x-3">
                    <button type="button" @click="openModal = false" class="px-4 py-2 text-xs font-medium text-slate-400 hover:text-white">Cancelar</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-xs shadow-lg shadow-indigo-600/30 transition">Criar Disparo</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
