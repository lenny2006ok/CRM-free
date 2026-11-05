@extends('layouts.app')

@section('title', 'Novo Assinante - Gestor IPTV')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-white tracking-tight">Cadastrar Novo Assinante</h2>
            <p class="text-sm text-slate-400">Preencha os dados do cliente e de acesso ao serviço IPTV.</p>
        </div>
        <a href="{{ route('clientes.index') }}" class="text-sm text-slate-400 hover:text-white">&larr; Voltar para a lista</a>
    </div>

    <form method="POST" action="{{ route('clientes.store') }}" class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-6 shadow-sm">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Nome Completo *</label>
                <input type="text" name="nome" value="{{ old('nome') }}" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                @error('nome') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">WhatsApp (com DDD) *</label>
                <input type="text" name="whatsapp" value="{{ old('whatsapp') }}" required placeholder="Ex: 5511999999999" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                @error('whatsapp') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Data de Vencimento *</label>
                <input type="date" name="vencimento" value="{{ old('vencimento', now()->addDays(30)->format('Y-m-d')) }}" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                @error('vencimento') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Plano Contratado</label>
                <select name="plano_id" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                    <option value="">Selecione um plano...</option>
                    @foreach($planos as $plano)
                        <option value="{{ $plano->id }}" {{ old('plano_id') == $plano->id ? 'selected' : '' }}>
                            {{ $plano->nome }} (R$ {{ number_format($plano->preco, 2, ',', '.') }} - {{ $plano->duracao }} dias)
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Servidor</label>
                <select name="servidor_id" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                    <option value="">Selecione o servidor...</option>
                    @foreach($servidores as $servidor)
                        <option value="{{ $servidor->id }}" {{ old('servidor_id') == $servidor->id ? 'selected' : '' }}>
                            {{ $servidor->nome }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Número de Telas</label>
                <input type="number" name="numero_de_telas" value="{{ old('numero_de_telas', 1) }}" min="1" max="10" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Usuário IPTV</label>
                <input type="text" name="iptv_nome" value="{{ old('iptv_nome') }}" placeholder="Login na lista/aplicativo" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Senha IPTV</label>
                <input type="text" name="iptv_senha" value="{{ old('iptv_senha') }}" placeholder="Senha na lista/aplicativo" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">MAC Address (opcional)</label>
                <input type="text" name="mac" value="{{ old('mac') }}" placeholder="00:1A:79:XX:XX:XX" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Anotações Internas</label>
                <textarea name="notas" rows="2" placeholder="Observações sobre o cliente..." class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">{{ old('notas') }}</textarea>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-2">
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" name="notificacoes" value="1" checked class="w-4 h-4 rounded text-indigo-600 focus:ring-0 bg-slate-950 border-slate-700">
                    <span class="text-sm text-slate-300">Receber lembretes de cobrança automáticos</span>
                </label>
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" name="enviar_boas_vindas" value="1" checked class="w-4 h-4 rounded text-indigo-600 focus:ring-0 bg-slate-950 border-slate-700">
                    <span class="text-sm text-slate-300">Enviar mensagem de boas-vindas com dados de acesso agora</span>
                </label>
            </div>

            <button type="submit" class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm shadow-lg shadow-indigo-600/30 transition">
                Salvar Assinante
            </button>
        </div>
    </form>
</div>
@endsection
