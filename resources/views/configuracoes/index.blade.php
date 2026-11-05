@extends('layouts.app')

@section('title', 'Configurações & APIs - Gestor IPTV')

@section('content')
<div class="max-w-4xl space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-white tracking-tight">Configurações da Empresa e Credenciais</h2>
        <p class="text-sm text-slate-400">Tokens e chaves secretas são protegidos com criptografia no banco de dados.</p>
    </div>

    <form method="POST" action="{{ route('configuracoes.salvar') }}" class="space-y-6">
        @csrf

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-5">
            <h3 class="text-base font-bold text-white border-b border-slate-800 pb-3">Informações da Sua Revenda</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-400 mb-1">Nome da Empresa / Painel</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $company->company_name) }}" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-400 mb-1">WhatsApp de Suporte</label>
                    <input type="text" name="company_whatsapp" value="{{ old('company_whatsapp', $company->company_whatsapp) }}" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold uppercase text-slate-400 mb-1">Chave Pix Manual (Fallback)</label>
                    <input type="text" name="pix_manual" value="{{ old('pix_manual', $company->pix_manual) }}" placeholder="Ex: chave@pix.com.br ou CPF" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                    <p class="text-xs text-slate-500 mt-1">Usada caso a API do Mercado Pago esteja temporariamente indisponível.</p>
                </div>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-5">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Mercado Pago (Pix Automático)</h3>
                <span class="text-xs px-2.5 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Criptografado</span>
            </div>
            <p class="text-xs text-slate-400">Insira suas credenciais da aplicação do Mercado Pago para gerar QR Code Pix com baixa automática.</p>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-400 mb-1">Access Token (Produção)</label>
                    <input type="password" name="access_token" value="{{ old('access_token', $company->access_token) }}" placeholder="APP_USR-xxxxxxxxxxxxxxxxxxxx" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-400 mb-1">Public Key</label>
                    <input type="text" name="public_key" value="{{ old('public_key', $company->public_key) }}" placeholder="APP_USR-xxxxxxxx" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div class="bg-slate-950 p-4 rounded-xl border border-slate-800 text-xs text-slate-400">
                    <span class="font-bold text-slate-200">URL do Webhook para cadastrar no Mercado Pago:</span>
                    <p class="font-mono text-indigo-400 mt-1">{{ url('/webhook/mercadopago') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-5">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Servidor Evolution API (WhatsApp)</h3>
                <span class="text-xs px-2.5 py-0.5 rounded bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">Opcional</span>
            </div>
            <p class="text-xs text-slate-400">Configure caso sua revenda possua uma instância dedicada na Evolution API.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-400 mb-1">URL da Evolution API</label>
                    <input type="url" name="evolution_api_url" value="{{ old('evolution_api_url', $company->evolution_api_url) }}" placeholder="http://meu-servidor:8080" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-400 mb-1">API Key da Evolution</label>
                    <input type="password" name="evolution_api_key" value="{{ old('evolution_api_key', $company->evolution_api_key) }}" placeholder="Sua chave apikey" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm shadow-lg shadow-indigo-600/30 transition">
                Salvar Todas as Configurações
            </button>
        </div>
    </form>
</div>
@endsection
