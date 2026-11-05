@extends('layouts.app')

@section('title', 'WhatsApp / Evolution API - Gestor IPTV')

@section('content')
<div class="max-w-4xl space-y-6" x-data="{
    loading: false,
    status: '{{ $conexao?->status ?? 'disconnected' }}',
    qrcode: null,
    async conectar() {
        this.loading = true;
        try {
            const res = await axios.post('{{ route('whatsapp.conectar') }}');
            this.status = res.data.status;
            this.qrcode = res.data.qrcode;
        } catch (e) {
            alert('Erro ao conectar com Evolution API');
        } finally {
            this.loading = false;
        }
    }
}">
    <div>
        <h2 class="text-2xl font-bold text-white tracking-tight">Conexão WhatsApp (Evolution API)</h2>
        <p class="text-sm text-slate-400">Escaneie o QR Code para conectar a instância do WhatsApp e disparar cobranças automáticas.</p>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl" :class="status === 'connected' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20'">
                <span x-text="status === 'connected' ? '🟢' : '🔴'"></span>
            </div>
            <div>
                <h3 class="text-lg font-bold text-white flex items-center space-x-2">
                    <span>Instância do WhatsApp</span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full uppercase font-medium" :class="status === 'connected' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400'" x-text="status === 'connected' ? 'Conectado' : 'Desconectado'"></span>
                </h3>
                <p class="text-xs text-slate-400 mt-1">
                    Número vinculado: <strong class="text-slate-200">{{ auth()->user()->whatsapp }}</strong>
                </p>
            </div>
        </div>

        <div class="flex items-center space-x-3">
            <template x-if="status !== 'connected'">
                <button @click="conectar()" :disabled="loading" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white font-medium text-sm shadow-lg shadow-emerald-600/30 transition flex items-center space-x-2">
                    <span x-show="!loading">📲 Conectar WhatsApp</span>
                    <span x-show="loading">Gerando QR Code...</span>
                </button>
            </template>

            <template x-if="status === 'connected'">
                <form method="POST" action="{{ route('whatsapp.desconectar') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-rose-600/10 hover:bg-rose-600/20 text-rose-400 border border-rose-500/20 font-medium text-sm transition">
                        Desconectar Sessão
                    </button>
                </form>
            </template>
        </div>
    </div>

    <div x-show="qrcode" style="display: none;" class="bg-slate-900 border border-slate-800 rounded-2xl p-8 flex flex-col items-center justify-center text-center space-y-4">
        <h3 class="text-lg font-bold text-white">Escaneie o QR Code abaixo com seu celular</h3>
        <p class="text-xs text-slate-400 max-w-sm">Abra o WhatsApp no celular &gt; Aparelhos conectados &gt; Conectar um aparelho.</p>

        <div class="bg-white p-4 rounded-2xl shadow-xl">
            <img :src="qrcode.startsWith('data:') ? qrcode : 'data:image/png;base64,' + qrcode" alt="QR Code" class="w-64 h-64 object-contain">
        </div>

        <p class="text-xs text-indigo-400 font-mono animate-pulse">Aguardando leitura do QR Code...</p>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
        <h4 class="text-base font-bold text-white mb-2">Endpoint e Credenciais da Evolution API</h4>
        <p class="text-xs text-slate-400 mb-4">Caso possua um servidor Evolution API dedicado para sua revenda, personalize os dados na aba de <a href="{{ route('configuracoes.index') }}" class="text-indigo-400 underline">Configurações</a>.</p>
        <div class="text-xs font-mono bg-slate-950 p-4 rounded-xl border border-slate-800 text-slate-300 space-y-1">
            <div>URL Atual: <span class="text-indigo-300">{{ $company?->evolution_api_url ?: env('EVOLUTION_API_URL', 'http://localhost:8080') }}</span></div>
            <div>Webhook Eventos: <span class="text-emerald-300">{{ url('/webhook/evolution') }}</span></div>
        </div>
    </div>
</div>
@endsection
