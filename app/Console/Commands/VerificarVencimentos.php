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

        foreach ($clientes as $cliente) {
            $revendedor = $cliente->user;
            if (!$revendedor) {
                continue;
            }

            $vencimentoStr = $cliente->vencimento->format('Y-m-d');
            $finalidade = match ($vencimentoStr) {
                $tresDiasFrente => 'vencimento_3dias',
                $hojeStr => 'vencimento_hoje',
                $umDiaAtras => 'vencido',
                default => null,
            };

            if (!$finalidade) {
                continue;
            }

            // Busca o template customizado do revendedor ou o template padrão do sistema
            $template = Template::withoutGlobalScopes()
                ->where(function ($q) use ($revendedor) {
                    $q->where('user_id', $revendedor->id)
                      ->orWhereNull('user_id');
                })
                ->where('finalidade', $finalidade)
                ->orderByRaw('user_id IS NULL ASC')
                ->first();

            if (!$template) {
                $this->warn("Nenhum template encontrado para '{$finalidade}' (nem customizado nem global).");
                continue;
            }

            $pagamento = null;
            if ($cliente->plano) {
                $pagamento = MercadoPagoService::criarPix($cliente, $cliente->plano, $revendedor);
            }

            $linkPix = $pagamento ? ($pagamento->ticket_url ?: url('/pagamento/' . $pagamento->mercado_pago_id)) : ($revendedor->companyDetail?->pix_manual ?: 'Chave não cadastrada');
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

        $this->info("Rotina de vencimentos concluída com sucesso!");
        return Command::SUCCESS;
    }
}
