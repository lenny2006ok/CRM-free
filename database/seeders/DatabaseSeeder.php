<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Plano;
use App\Models\Template;
use App\Models\Servidor;
use App\Models\CompanyDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            1 => 'admin',
            2 => 'master',
            3 => 'cliente',
            4 => 'revendedor',
        ];

        foreach ($roles as $id => $name) {
            Role::firstOrCreate(['id' => $id], ['name' => $name]);
        }

        $admin = User::firstOrCreate(
            ['whatsapp' => '5511999999999'],
            [
                'name' => 'Administrador',
                'email' => 'admin@gestor.local',
                'password' => Hash::make('admin123'),
                'role_id' => 1,
                'status' => 'ativo',
                'limite' => 10000,
                'creditos' => 9999,
            ]
        );

        $revendedor = User::firstOrCreate(
            ['whatsapp' => '5511988888888'],
            [
                'name' => 'Revenda Demo',
                'email' => 'revenda@gestor.local',
                'password' => Hash::make('revenda123'),
                'role_id' => 4,
                'status' => 'ativo',
                'limite' => 500,
                'creditos' => 50,
            ]
        );

        CompanyDetail::firstOrCreate(
            ['user_id' => $revendedor->id],
            [
                'company_name' => 'Minha Revenda IPTV',
                'company_whatsapp' => '5511988888888',
                'pix_manual' => 'revenda@pix.local',
            ]
        );

        $servidor = Servidor::firstOrCreate(
            ['user_id' => $revendedor->id, 'nome' => 'Servidor Principal Ultra']
        );

        $planoMensal = Plano::firstOrCreate(
            ['user_id' => $revendedor->id, 'nome' => 'Plano Mensal Completo'],
            ['preco' => 35.00, 'duracao' => 30]
        );

        $planoTrimestral = Plano::firstOrCreate(
            ['user_id' => $revendedor->id, 'nome' => 'Plano Trimestral Promo'],
            ['preco' => 90.00, 'duracao' => 90]
        );

        $defaultTemplates = [
            [
                'nome' => 'Aviso Vencimento (3 dias)',
                'finalidade' => 'vencimento_3dias',
                'tipo_mensagem' => 'texto',
                'conteudo' => "Olá, *{nome}*! 👋\n\nInformamos que sua assinatura vence em *3 dias* ({vencimento}).\nEvite a interrupção do seu serviço efetuando o pagamento via Pix:\n\n*Chave / Código Pix:* {pix_copia_cola}\n*Link de Pagamento:* {link_pix}\n\nQualquer dúvida estamos à disposição!",
            ],
            [
                'nome' => 'Aviso Vencimento (Hoje)',
                'finalidade' => 'vencimento_hoje',
                'tipo_mensagem' => 'texto',
                'conteudo' => "Atenção *{nome}*! ⏰\n\nSua assinatura vence *HOJE* ({vencimento}). Renove agora mesmo para continuar assistindo sem travas e sem bloqueio:\n\n*Código Pix Copia e Cola:* {pix_copia_cola}\n*Link:* {link_pix}\n\nAgradecemos pela preferência!",
            ],
            [
                'nome' => 'Aviso Vencido (1 dia após)',
                'finalidade' => 'vencido',
                'tipo_mensagem' => 'texto',
                'conteudo' => "Olá *{nome}*! ⚠️\n\nSeu acesso foi suspenso pois seu plano venceu em {vencimento}.\nPara reativar sua lista imediatamente, realize o pagamento via Pix:\n\n*Pix Copia e Cola:* {pix_copia_cola}\n*Pagar agora:* {link_pix}",
            ],
            [
                'nome' => 'Boas-Vindas ao Cliente',
                'finalidade' => 'boas_vindas',
                'tipo_mensagem' => 'texto',
                'conteudo' => "Seja bem-vindo(a), *{nome}*! 🎉\n\nSeu plano está ativo até *{vencimento}*.\nSeus dados de acesso:\nUsuário: {iptv_nome}\nSenha: {iptv_senha}\n\nBom entretenimento!",
            ],
            [
                'nome' => 'Confirmação de Pagamento',
                'finalidade' => 'cobranca',
                'tipo_mensagem' => 'texto',
                'conteudo' => "Obrigado, *{nome}*! ✅\n\nSeu pagamento no valor de R$ {valor} foi confirmado com sucesso!\nSua assinatura foi renovada até *{vencimento}*.\nTenha um ótimo uso!",
            ],
        ];

        foreach ($defaultTemplates as $tmpl) {
            Template::firstOrCreate(
                ['user_id' => $revendedor->id, 'finalidade' => $tmpl['finalidade']],
                $tmpl
            );
        }
    }
}
