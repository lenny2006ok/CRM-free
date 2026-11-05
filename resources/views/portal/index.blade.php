<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Central do Assinante - Consulta de Vencimento e Renovação</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased flex flex-col items-center justify-center p-4">
    <div class="max-w-md w-full space-y-6">
        <div class="text-center">
            <div class="w-14 h-14 rounded-2xl bg-indigo-600 flex items-center justify-center font-bold text-2xl text-white mx-auto shadow-lg shadow-indigo-600/40">
                📺
            </div>
            <h2 class="mt-4 text-2xl font-bold tracking-tight text-white">Central do Assinante</h2>
            <p class="text-sm text-slate-400">Consulte o status do seu plano e renove instantaneamente via Pix</p>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
            <form method="GET" action="{{ route('cliente.portal') }}" class="space-y-3">
                <label class="block text-xs font-semibold uppercase text-slate-400">Digite seu WhatsApp cadastrado</label>
                <div class="flex items-center space-x-2">
                    <input type="text" name="whatsapp" value="{{ request('whatsapp') }}" required placeholder="Ex: 5511999999999" class="flex-1 bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm transition">
                        Buscar
                    </button>
                </div>
            </form>
        </div>

        @if(request()->filled('whatsapp') && !$cliente)
            <div class="bg-rose-500/10 border border-rose-500/20 text-rose-400 p-4 rounded-2xl text-center text-xs">
                Nenhuma assinatura encontrada para o número informado. Verifique os dados com seu fornecedor.
            </div>
        @endif

        @if($cliente)
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-5">
                <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                    <div>
                        <h3 class="font-bold text-white text-lg">{{ $cliente->nome }}</h3>
                        <p class="text-xs text-slate-400">{{ $cliente->user?->companyDetail?->company_name ?? 'Serviço de IPTV' }}</p>
                    </div>
                    @if($cliente->isVencido())
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                            Vencido
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            Ativo
                        </span>
                    @endif
                </div>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-slate-400">
                        <span>Plano Contratado:</span>
                        <strong class="text-white">{{ $cliente->plano?->nome ?? 'Padrão' }}</strong>
                    </div>
                    <div class="flex justify-between text-slate-400">
                        <span>Vencimento Atual:</span>
                        <strong class="{{ $cliente->isVencido() ? 'text-rose-400' : 'text-emerald-400' }}">
                            {{ $cliente->vencimento ? $cliente->vencimento->format('d/m/Y') : '-' }}
                        </strong>
                    </div>
                    @if($cliente->iptv_nome)
                        <div class="flex justify-between text-slate-400 font-mono text-xs pt-2 border-t border-slate-800">
                            <span>Usuário IPTV:</span>
                            <span class="text-indigo-300">{{ $cliente->iptv_nome }}</span>
                        </div>
                    @endif
                </div>

                @if($cliente->plano)
                    <div class="pt-4 border-t border-slate-800">
                        <form method="POST" action="{{ route('cliente.gerar-pix', $cliente->id) }}">
                            @csrf
                            <button type="submit" class="w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm shadow-lg shadow-emerald-600/30 transition flex items-center justify-center space-x-2">
                                <span>📲 Gerar Pix de Renovação (R$ {{ number_format($cliente->plano->preco, 2, ',', '.') }})</span>
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        @endif

        <div class="text-center text-xs text-slate-500">
            <a href="{{ route('login') }}" class="hover:text-slate-400">Acesso Revendedor / Administrativo &rarr;</a>
        </div>
    </div>
</body>
</html>
