@extends('layouts.app')

@section('title', 'Templates de Mensagens - Gestor IPTV')

@section('content')
<div class="space-y-6" x-data="{ openModal: false, templateEdit: { tipo_mensagem: 'texto' } }">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-white tracking-tight">Templates de Mensagens WhatsApp</h2>
            <p class="text-sm text-slate-400">Personalize os disparos automáticos para avisos de vencimento, Pix e boas-vindas.</p>
        </div>
        <button @click="templateEdit = { tipo_mensagem: 'texto' }; openModal = true" class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm shadow-lg shadow-indigo-600/30 transition">
            <span>➕</span> <span>Novo Template</span>
        </button>
    </div>

    <div class="bg-indigo-950/40 border border-indigo-800/50 p-4 rounded-xl">
        <p class="text-xs font-semibold uppercase text-indigo-400 mb-2">💡 Variáveis Dinâmicas Disponíveis para os Textos:</p>
        <div class="flex flex-wrap gap-2 text-xs">
            <span class="bg-indigo-900/60 px-2.5 py-1 rounded-md text-indigo-200 font-mono">{nome}</span>
            <span class="bg-indigo-900/60 px-2.5 py-1 rounded-md text-indigo-200 font-mono">{vencimento}</span>
            <span class="bg-indigo-900/60 px-2.5 py-1 rounded-md text-indigo-200 font-mono">{link_pix}</span>
            <span class="bg-indigo-900/60 px-2.5 py-1 rounded-md text-indigo-200 font-mono">{pix_copia_cola}</span>
            <span class="bg-indigo-900/60 px-2.5 py-1 rounded-md text-indigo-200 font-mono">{valor}</span>
            <span class="bg-indigo-900/60 px-2.5 py-1 rounded-md text-indigo-200 font-mono">{iptv_nome}</span>
            <span class="bg-indigo-900/60 px-2.5 py-1 rounded-md text-indigo-200 font-mono">{iptv_senha}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($templates as $tmpl)
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 flex flex-col justify-between shadow-sm">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-bold text-white text-base">{{ $tmpl->nome }}</h3>
                        <span class="text-xs px-2.5 py-1 rounded-full bg-slate-800 text-slate-300 border border-slate-700">
                            {{ $tmpl->finalidade }}
                        </span>
                    </div>

                    <div class="bg-slate-950 border border-slate-800/80 rounded-xl p-4 text-xs font-mono text-slate-300 whitespace-pre-line leading-relaxed">
                        {{ $tmpl->conteudo }}
                    </div>

                    @if($tmpl->tipo_mensagem === 'imagem' && $tmpl->imagem)
                        <div class="mt-3 text-xs text-indigo-400">
                            🖼️ Anexo: {{ $tmpl->imagem }}
                        </div>
                    @endif
                </div>

                <div class="mt-5 pt-4 border-t border-slate-800 flex items-center justify-between">
                    <button @click="templateEdit = {{ json_encode($tmpl) }}; openModal = true" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium">
                        ✏️ Editar Mensagem
                    </button>

                    <form method="POST" action="{{ route('templates.destroy', $tmpl) }}" onsubmit="return confirm('Remover este template?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-rose-400 hover:text-rose-300">
                            🗑️ Excluir
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-2 bg-slate-900 border border-slate-800 rounded-2xl p-8 text-center text-slate-500">
                Nenhum template configurado.
            </div>
        @endforelse
    </div>

    <div x-show="openModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="openModal = false" class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 space-y-5">
            <h3 class="text-lg font-bold text-white" x-text="templateEdit.id ? 'Editar Template' : 'Novo Template'"></h3>

            <form method="POST" action="{{ route('templates.salvar') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="id" :value="templateEdit.id || ''">

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-400 mb-1">Título do Template</label>
                    <input type="text" name="nome" :value="templateEdit.nome || ''" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-400 mb-1">Finalidade / Gatilho</label>
                    <select name="finalidade" :value="templateEdit.finalidade || 'vencimento_3dias'" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                        <option value="vencimento_3dias">Aviso 3 dias antes do vencimento</option>
                        <option value="vencimento_hoje">Aviso no dia do vencimento</option>
                        <option value="vencido">Aviso 1 dia após o vencimento</option>
                        <option value="boas_vindas">Boas-Vindas com dados de acesso</option>
                        <option value="cobranca">Confirmação de Pagamento Recebido</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-400 mb-1">Tipo de Mensagem</label>
                    <select name="tipo_mensagem" x-model="templateEdit.tipo_mensagem" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                        <option value="texto">Apenas Texto</option>
                        <option value="imagem">Imagem com Legenda</option>
                    </select>
                </div>

                <div x-show="templateEdit.tipo_mensagem === 'imagem'">
                    <label class="block text-xs font-semibold uppercase text-slate-400 mb-1">URL da Imagem</label>
                    <input type="url" name="imagem" :value="templateEdit.imagem || ''" placeholder="https://exemplo.com/banner.png" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-400 mb-1">Conteúdo da Mensagem</label>
                    <textarea name="conteudo" rows="5" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white font-mono focus:outline-none focus:border-indigo-500" :value="templateEdit.conteudo || ''"></textarea>
                </div>

                <div class="pt-4 flex items-center justify-end space-x-3">
                    <button type="button" @click="openModal = false" class="px-4 py-2 text-xs font-medium text-slate-400 hover:text-white">Cancelar</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-xs shadow-lg shadow-indigo-600/30 transition">Salvar Template</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
