@extends('layouts.app')

@section('title', 'Servidores IPTV - Gestor IPTV')

@section('content')
<div class="space-y-6" x-data="{ openModal: false, servidorEdit: {} }">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-white tracking-tight">Servidores IPTV / P2P</h2>
            <p class="text-sm text-slate-400">Organize os servidores e painéis que você utiliza para atender seus clientes.</p>
        </div>
        <button @click="servidorEdit = {}; openModal = true" class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm shadow-lg shadow-indigo-600/30 transition">
            <span>➕</span> <span>Novo Servidor</span>
        </button>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($servidores as $servidor)
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-sm flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-white text-base">{{ $servidor->nome }}</h3>
                    <p class="text-xs text-slate-400 mt-1">{{ $servidor->clientes_count }} cliente(s) vinculados</p>
                </div>

                <div class="flex items-center space-x-2">
                    <button @click="servidorEdit = {{ json_encode($servidor) }}; openModal = true" class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs text-slate-300">
                        ✏️
                    </button>

                    <form method="POST" action="{{ route('servidores.destroy', $servidor) }}" onsubmit="return confirm('Deseja remover este servidor?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 rounded-lg bg-rose-600/10 hover:bg-rose-600/20 text-rose-400 text-xs">
                            🗑️
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-3 bg-slate-900 border border-slate-800 rounded-2xl p-8 text-center text-slate-500">
                Nenhum servidor cadastrado. Adicione um servidor para categorizar suas linhas.
            </div>
        @endforelse
    </div>

    <div x-show="openModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="openModal = false" class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 space-y-5">
            <h3 class="text-lg font-bold text-white" x-text="servidorEdit.id ? 'Editar Servidor' : 'Adicionar Servidor'"></h3>

            <form method="POST" action="{{ route('servidores.salvar') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="id" :value="servidorEdit.id || ''">

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-400 mb-1">Nome do Servidor</label>
                    <input type="text" name="nome" :value="servidorEdit.nome || ''" required placeholder="Ex: Servidor Gold Ultra" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>

                <div class="pt-4 flex items-center justify-end space-x-3">
                    <button type="button" @click="openModal = false" class="px-4 py-2 text-xs font-medium text-slate-400 hover:text-white">Cancelar</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-xs shadow-lg shadow-indigo-600/30 transition">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
