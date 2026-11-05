<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('planos_renovacao', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->decimal('preco', 10, 2);
            $table->integer('limite')->default(0);
            $table->integer('duracao')->default(30);
            $table->timestamps();
        });

        Schema::create('planos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('id_qpanel')->nullable();
            $table->string('nome');
            $table->decimal('preco', 8, 2);
            $table->integer('duracao')->default(30);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('name');
            $table->string('whatsapp')->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password');
            $table->unsignedBigInteger('role_id')->default(4);
            $table->string('status')->default('ativo');
            $table->timestamp('trial_ends_at')->nullable();
            $table->integer('limite')->default(100);
            $table->integer('creditos')->default(0);
            $table->foreignId('plano_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('servidores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index();
            $table->string('nome');
            $table->timestamps();
        });

        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index();
            $table->unsignedBigInteger('role_id')->default(3);
            $table->string('nome');
            $table->string('whatsapp');
            $table->string('password')->nullable();
            $table->date('vencimento');
            $table->foreignId('servidor_id')->nullable();
            $table->foreignId('plano_id')->nullable();
            $table->integer('numero_de_telas')->default(1);
            $table->string('iptv_nome')->nullable();
            $table->string('iptv_senha')->nullable();
            $table->string('mac')->nullable();
            $table->boolean('notificacoes')->default(true);
            $table->text('notas')->nullable();
            $table->timestamps();
        });

        Schema::create('pagamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index();
            $table->foreignId('cliente_id')->nullable()->index();
            $table->foreignId('plano_id')->nullable();
            $table->string('mercado_pago_id')->unique();
            $table->decimal('valor', 10, 2);
            $table->string('status')->default('pending');
            $table->date('payment_date')->nullable();
            $table->boolean('use_saldo_ganhos')->default(false);
            $table->text('qr_code')->nullable();
            $table->text('qr_code_base64')->nullable();
            $table->string('ticket_url')->nullable();
            $table->timestamps();
        });

        Schema::create('conexoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index();
            $table->string('whatsapp')->unique();
            $table->string('status')->default('disconnected');
            $table->string('tokenid')->nullable();
            $table->boolean('conn')->default(false);
            $table->string('instance_name')->nullable();
            $table->timestamps();
        });

        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('nome');
            $table->string('finalidade');
            $table->text('conteudo');
            $table->string('tipo_mensagem')->default('texto');
            $table->string('imagem')->nullable();
            $table->timestamps();
        });

        Schema::create('campanhas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index();
            $table->string('nome');
            $table->time('horario');
            $table->date('data')->nullable();
            $table->json('contatos')->nullable();
            $table->text('mensagem');
            $table->string('arquivo')->nullable();
            $table->boolean('enviar_diariamente')->default(false);
            $table->timestamp('ultima_execucao')->nullable();
            $table->timestamps();
        });

        Schema::create('company_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique();
            $table->string('company_name')->nullable();
            $table->string('company_whatsapp')->nullable();
            $table->text('access_token')->nullable();
            $table->text('public_key')->nullable();
            $table->string('pix_manual')->nullable();
            $table->string('evolution_api_url')->nullable();
            $table->text('evolution_api_key')->nullable();
            $table->string('qpanel_api_url')->nullable();
            $table->string('qpanel_api_key')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_details');
        Schema::dropIfExists('campanhas');
        Schema::dropIfExists('templates');
        Schema::dropIfExists('conexoes');
        Schema::dropIfExists('pagamentos');
        Schema::dropIfExists('clientes');
        Schema::dropIfExists('servidores');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('planos');
        Schema::dropIfExists('planos_renovacao');
        Schema::dropIfExists('roles');
    }
};
