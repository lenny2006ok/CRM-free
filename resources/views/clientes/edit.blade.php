@extends('layouts.app')

@section('title', 'Editar Assinante - Gestor IPTV')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-white tracking-tight">Editar Assinante</h2>
            <p class="text-sm text-slate-400">Atualize os dados e vencimento de {{ $cliente->nome }}.</p>
        </div>
        <a href="{{ route('clientes.index') }}" class="text-sm text-slate-400 hover:text-white">&larr; Voltar para a lista</a>
    </div>

    <form method="POST" action="{{ route('clientes.update', $cliente) }}" class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-6 shadow-sm">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Nome Completo *</label>
                <input type="text" name="nome" value="{{ old('nome', $cliente->nome) }}" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                @error('nome') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">WhatsApp (com DDD) *</label>
                <input type="text" name="whatsapp" value="{{ old('whatsapp', $cliente->whatsapp) }}" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                @error('whatsapp') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Data de Vencimento *</label>
                <input type="date" name="vencimento" value="{{ old('vencimento', $cliente->vencimento ? $cliente->vencimento->format('Y-m-d') : '') }}" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                @error('vencimento') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Plano Contratado</label>
                <select name="plano_id" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                    <option value="">Selecione um plano...</option>
                    @foreach($planos as $plano)
                        <option value="{{ $plano->id }}" {{ old('plano_id', $cliente->plano_id) == $plano->id ? 'selected' : '' }}>
                            {{ $plano->nome }} (R$ {{ number_format($plano->preco, 2, ',', '.') }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Servidor</label>
                <select name="servidor_id" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                    <option value="">Selecione o servidor...</option>
                    @foreach($servidores as $servidor)
                        <option value="{{ $servidor->id }}" {{ old('servidor_id', $cliente->servidor_id) == $servidor->id ? 'selected' : '' }}>
                            {{ $servidor->nome }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Número de Telas</label>
                <input type="number" name="numero_de_telas" value="{{ old('numero_de_telas', $cliente->numero_de_telas) }}" min="1" max="10" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Usuário IPTV</label>
                <input type="text" name="iptv_nome" value="{{ old('iptv_nome', $cliente->iptv_nome) }}" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Senha IPTV</label>
                <input type="text" name="iptv_senha" value="{{ old('iptv_senha', $cliente->iptv_senha) }}" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">MAC Address</label>
                <input type="text" name="mac" value="{{ old('mac', $cliente->mac) }}" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Anotações Internas</label>
                <textarea name="notas" rows="2" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">{{ old('notas', $cliente->notas) }}</textarea>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-800 flex items-center justify-between">
            <label class="flex items-center space-x-3 cursor-pointer">
                <input type="checkbox" name="notificacoes" value="1" {{ old('notificacoes', $cliente->notificacoes) ? 'checked' : '' }} class="w-4 h-4 rounded text-indigo-600 focus:ring-0 bg-slate-950 border-slate-700">
                <span class="text-sm text-slate-300">Receber notificações e cobranças automáticas</span>
            </label>

            <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm shadow-lg shadow-indigo-600/30 transition">
                Atualizar Assinante
            </button>
        </div>
    </form>
</div>
@endsection
