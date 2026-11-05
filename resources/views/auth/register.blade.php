<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Conta de Revenda - Gestor IPTV</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-4 antialiased">
    <div class="max-w-md w-full space-y-6 bg-slate-900 border border-slate-800 p-8 rounded-3xl shadow-2xl">
        <div class="text-center">
            <div class="w-14 h-14 rounded-2xl bg-indigo-600 flex items-center justify-center font-bold text-2xl text-white mx-auto shadow-lg shadow-indigo-600/40">
                🚀
            </div>
            <h2 class="mt-4 text-2xl font-bold tracking-tight text-white">Criar Conta de Revenda</h2>
            <p class="mt-1 text-sm text-slate-400">Comece a gerenciar suas assinaturas e disparar Pix automáticos.</p>
        </div>

        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-300 mb-1">Seu Nome / Nome da Empresa</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Ex: João da Silva" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase text-slate-300 mb-1">WhatsApp (com DDD)</label>
                <input type="text" name="whatsapp" value="{{ old('whatsapp') }}" required placeholder="Ex: 5511999999999" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase text-slate-300 mb-1">E-mail (opcional)</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="joao@exemplo.com" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase text-slate-300 mb-1">Senha de Acesso</label>
                <input type="password" name="password" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase text-slate-300 mb-1">Confirme a Senha</label>
                <input type="password" name="password_confirmation" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
            </div>

            <button type="submit" class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm shadow-lg shadow-indigo-600/30 transition">
                Criar Minha Conta Grátis
            </button>
        </form>

        <div class="text-center pt-2 border-t border-slate-800">
            <p class="text-xs text-slate-400">
                Já possui uma conta?
                <a href="{{ route('login') }}" class="text-indigo-400 hover:underline font-medium">Faça login</a>
            </p>
        </div>
    </div>
</body>
</html>
