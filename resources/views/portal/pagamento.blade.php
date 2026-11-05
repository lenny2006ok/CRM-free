<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Pix - Gestor IPTV</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full antialiased flex flex-col items-center justify-center p-4" x-data="{
    copiado: false,
    status: '{{ $pagamento->status }}',
    copiarPix() {
        navigator.clipboard.writeText('{{ $pagamento->qr_code }}');
        this.copiado = true;
        setTimeout(() => this.copiado = false, 3000);
    },
    checarStatus() {
        fetch('{{ route('cliente.pagamento.status', $pagamento->mercado_pago_id) }}')
            .then(res => res.json())
            .then(data => {
                if (data.aprovado) {
                    this.status = 'approved';
                }
            });
    },
    init() {
        if (this.status !== 'approved') {
            setInterval(() => this.checarStatus(), 4000);
        }
    }
}">
    <div class="max-w-md w-full space-y-6">
        <div class="text-center">
            <h2 class="text-2xl font-bold tracking-tight text-white">Pagamento Pix</h2>
            <p class="text-sm text-slate-400">Renovação de Assinatura IPTV</p>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6 text-center">
            <div x-show="status === 'approved'" style="display: none;" class="space-y-4">
                <div class="w-16 h-16 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center text-3xl mx-auto">
                    ✅
                </div>
                <h3 class="text-xl font-bold text-white">Pagamento Confirmado!</h3>
                <p class="text-xs text-slate-400">Sua assinatura foi renovada com sucesso. Obrigado pela preferência!</p>
                <a href="{{ route('cliente.portal') }}" class="inline-block mt-4 px-6 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-medium">Voltar ao Início</a>
            </div>

            <div x-show="status !== 'approved'" class="space-y-6">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Valor a Pagar</span>
                    <p class="text-3xl font-extrabold text-emerald-400 mt-1">R$ {{ number_format($pagamento->valor, 2, ',', '.') }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">Plano: {{ $pagamento->plano?->nome ?? 'Assinatura' }}</p>
                </div>

                <div class="bg-white p-4 rounded-2xl inline-block shadow-md">
                    @if($pagamento->qr_code_base64 && $pagamento->qr_code_base64 !== 'mock_qr_code_image')
                        <img src="data:image/png;base64,{{ $pagamento->qr_code_base64 }}" alt="QR Code Pix" class="w-48 h-48 mx-auto">
                    @else
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($pagamento->qr_code) }}" alt="QR Code Pix" class="w-48 h-48 mx-auto">
                    @endif
                </div>

                <div class="space-y-2 text-left">
                    <label class="block text-xs font-semibold uppercase text-slate-400">Código Pix Copia e Cola:</label>
                    <div class="flex items-center space-x-2">
                        <input type="text" readonly value="{{ $pagamento->qr_code }}" class="flex-1 bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-mono text-slate-300 focus:outline-none">
                        <button type="button" @click="copiarPix()" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-xs transition">
                            <span x-text="copiado ? 'Copiado!' : 'Copiar'"></span>
                        </button>
                    </div>
                </div>

                <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800 text-xs text-indigo-400 flex items-center justify-center space-x-2">
                    <span class="animate-spin inline-block w-3 h-3 border-2 border-indigo-400 border-t-transparent rounded-full"></span>
                    <span>Aguardando identificação do pagamento...</span>
                </div>
            </div>
        </div>

        <div class="text-center text-xs text-slate-500">
            <a href="{{ route('cliente.portal') }}" class="hover:text-slate-400">&larr; Voltar para a Central do Assinante</a>
        </div>
    </div>
</body>
</html>
