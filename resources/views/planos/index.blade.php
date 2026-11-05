@extends('layouts.app')

@section('title', 'Planos de Venda - Gestor IPTV')

@section('content')
<div class="space-y-6" x-data="{ openModal: false, planoEdit: {} }">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-white tracking-tight">Planos Oferecidos</h2>
            <p class="text-sm text-slate-400">Configure os valores e durações dos planos vendidos aos seus clientes.</p>
        </div>
        <button @click="planoEdit = {}; openModal = true" class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm shadow-lg shadow-indigo-600/30 transition">
            <span>➕</span> <span>Novo Plano</span>
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @forelse($planos as $plano)
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 relative flex flex-col justify-between shadow-sm">
                <div>
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-lg text-white">{{ $plano->nome }}</h3>
                        <span class="text-xs px-2.5 py-1 rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 font-medium">
                            {{ $plano->duracao }} dias
                        </span>
                    </div>

                    <div class="mt-4 flex items-baseline">
                        <span class="text-3xl font-extrabold text-white">R$ {{ number_format($plano->preco, 2, ',', '.') }}</span>
                        <span class="ml-1 text-xs text-slate-400">/ ciclo</span>
                    </div>

                    <p class="mt-4 text-xs text-slate-400">
                        {{ $plano->clientes_count }} cliente(s) utilizando este plano
                    </p>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-800 flex items-center justify-between">
                    <button @click="planoEdit = {{ json_encode($plano) }}; openModal = true" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium">
                        ✏️ Editar
                    </button>

                    <form method="POST" action="{{ route('planos.destroy', $plano) }}" onsubmit="return confirm('Excluir este plano?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-rose-400 hover:text-rose-300">
                            🗑️ Excluir
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-3 bg-slate-900 border border-slate-800 rounded-2xl p-8 text-center text-slate-500">
                Nenhum plano cadastrado. Clique no botão acima para criar seu primeiro plano!
            </div>
        @endforelse
    </div>

    <div x-show="openModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="openModal = false" class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 space-y-5">
            <h3 class="text-lg font-bold text-white" x-text="planoEdit.id ? 'Editar Plano' : 'Criar Novo Plano'"></h3>

            <form method="POST" action="{{ route('planos.salvar') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="id" :value="planoEdit.id || ''">

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-400 mb-1">Nome do Plano</label>
                    <input type="text" name="nome" :value="planoEdit.nome || ''" required placeholder="Ex: Mensal Completo" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-400 mb-1">Preço (R$)</label>
                        <input type="number" step="0.01" name="preco" :value="planoEdit.preco || ''" required placeholder="35.00" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-400 mb-1">Duração (Dias)</label>
                        <input type="number" name="duracao" :value="planoEdit.duracao || 30" required placeholder="30" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end space-x-3">
                    <button type="button" @click="openModal = false" class="px-4 py-2 text-xs font-medium text-slate-400 hover:text-white">Cancelar</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-xs shadow-lg shadow-indigo-600/30 transition">Salvar Plano</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
