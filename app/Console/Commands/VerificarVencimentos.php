<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use App\Models\Template;
use App\Services\EvolutionApiService;
use App\Services\MercadoPagoService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class VerificarVencimentos extends Command
{
    protected $signature = 'cron:verificar-vencimentos';

    protected $description = 'Verifica vencimentos dos clientes (-3 dias, hoje e +1 dia vencido) e envia cobrança/notificação com Pix via WhatsApp';

    public function handle()
    {
        $hoje = Carbon::today();
        $this->info("Iniciando rotina de verificação de vencimentos para a data: {$hoje->format('d/m/Y')}");

        $tresDiasFrente = $hoje->copy()->addDays(3)->format('Y-m-d');
        $hojeStr = $hoje->format('Y-m-d');
        $umDiaAtras = $hoje->copy()->subDay()->format('Y-m-d');

        $clientes = Cliente::withoutGlobalScopes()
            ->with(['user.companyDetail', 'plano'])
            ->where('notificacoes', true)
            ->where(function ($q) use ($tresDiasFrente, $hojeStr, $umDiaAtras) {
                $q->whereDate('vencimento', $tresDiasFrente)
                    ->orWhereDate('vencimento', $hojeStr)
                    ->orWhereDate('vencimento', $umDiaAtras);
            })
            ->get();

        $this->info("Encontrados {$clientes->count()} cliente(s) elegíveis para notificação de vencimento.");

        /**
         * ⚡ Bolt Performance Optimization:
         * Eliminate N+1 query problem by pre-fetching templates in a single batch query
         * for all relevant revendedores and caching them in memory keyed by user_id and finalidade.
         * For N eligible clients, this reduces template queries from O(N) to O(1).
         */
        $revendedorIds = $clientes->pluck('user_id')->filter()->unique()->values()->all();
        $templatesLookup = [];

        if (! empty($revendedorIds)) {
            $templates = Template::withoutGlobalScopes()
                ->whereIn('user_id', $revendedorIds)
                ->whereIn('finalidade', ['vencimento_3dias', 'vencimento_hoje', 'vencido'])
                ->get();

            foreach ($templates as $tmpl) {
                $templatesLookup["{$tmpl->user_id}_{$tmpl->finalidade}"] = $tmpl;
            }
        }

        foreach ($clientes as $cliente) {
            $revendedor = $cliente->user;
            if (! $revendedor) {
                continue;
            }

            $vencimentoStr = $cliente->vencimento->format('Y-m-d');
            $finalidade = match ($vencimentoStr) {
                $tresDiasFrente => 'vencimento_3dias',
                $hojeStr => 'vencimento_hoje',
                $umDiaAtras => 'vencido',
                default => null,
            };

            if (! $finalidade) {
                continue;
            }

            $templateKey = "{$revendedor->id}_{$finalidade}";
            $template = $templatesLookup[$templateKey] ?? null;

            if (! $template) {
                $this->warn("Revendedor #{$revendedor->id} não possui template configurado para '{$finalidade}'.");

                continue;
            }

            $pagamento = null;
            if ($cliente->plano) {
                $pagamento = MercadoPagoService::criarPix($cliente, $cliente->plano, $revendedor);
            }

            $linkPix = $pagamento ? ($pagamento->ticket_url ?: url('/pagamento/'.$pagamento->mercado_pago_id)) : ($revendedor->companyDetail?->pix_manual ?: 'Chave não cadastrada');
            $pixCopiaCola = $pagamento ? ($pagamento->qr_code ?: '') : ($revendedor->companyDetail?->pix_manual ?: '');

            $variaveis = [
                'nome' => $cliente->nome,
                'vencimento' => $cliente->vencimento->format('d/m/Y'),
                'link_pix' => $linkPix,
                'pix_copia_cola' => $pixCopiaCola,
                'plano' => $cliente->plano?->nome ?? 'Padrão',
                'valor' => $cliente->plano ? number_format((float) $cliente->plano->preco, 2, ',', '.') : '0,00',
                'iptv_nome' => $cliente->iptv_nome ?? '',
                'iptv_senha' => $cliente->iptv_senha ?? '',
            ];

            $mensagem = $template->renderizar($variaveis);

            if ($template->tipo_mensagem === 'imagem' && $template->imagem) {
                EvolutionApiService::enviarMidia($revendedor, $cliente->whatsapp, $template->imagem, $mensagem);
            } else {
                EvolutionApiService::enviarTexto($revendedor, $cliente->whatsapp, $mensagem);
            }

            $this->info("Mensagem [{$finalidade}] enviada com sucesso para {$cliente->nome} ({$cliente->whatsapp})");
            Log::info("Notificação [{$finalidade}] enviada para o cliente #{$cliente->id}");
        }

        $this->info('Rotina de vencimentos concluída com sucesso!');

        return Command::SUCCESS;
    }
}
