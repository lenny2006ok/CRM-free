<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\CompanyDetail;
use App\Models\Pagamento;
use App\Models\Plano;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GestorCrmTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_autenticacao_login_e_redirecionamento()
    {
        $response = $this->post('/login', [
            'whatsapp' => '5511988888888',
            'password' => 'revenda123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_registro_de_nova_revenda()
    {
        $response = $this->post('/register', [
            'name' => 'Novo Revendedor',
            'whatsapp' => '5511977777777',
            'email' => 'novo@revenda.com',
            'password' => 'senha1234',
            'password_confirmation' => 'senha1234',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', ['whatsapp' => '5511977777777', 'role_id' => 4]);
        $this->assertDatabaseHas('company_details', ['company_whatsapp' => '5511977777777']);
    }

    public function test_multi_tenant_isolamento_de_clientes()
    {
        $revenda1 = User::where('whatsapp', '5511988888888')->first();

        $revenda2 = User::create([
            'name' => 'Revenda 2',
            'whatsapp' => '5511966666666',
            'password' => bcrypt('123456'),
            'role_id' => 4,
            'limite' => 100,
        ]);

        $cliente1 = Cliente::create([
            'user_id' => $revenda1->id,
            'nome' => 'Cliente da Revenda 1',
            'whatsapp' => '5511911111111',
            'vencimento' => now()->addDays(15),
        ]);

        $cliente2 = Cliente::create([
            'user_id' => $revenda2->id,
            'nome' => 'Cliente da Revenda 2',
            'whatsapp' => '5511922222222',
            'vencimento' => now()->addDays(20),
        ]);

        $this->actingAs($revenda1);
        $clientesRevenda1 = Cliente::all();
        $this->assertTrue($clientesRevenda1->contains('id', $cliente1->id));
        $this->assertFalse($clientesRevenda1->contains('id', $cliente2->id));

        $this->actingAs($revenda2);
        $clientesRevenda2 = Cliente::all();
        $this->assertTrue($clientesRevenda2->contains('id', $cliente2->id));
        $this->assertFalse($clientesRevenda2->contains('id', $cliente1->id));
    }

    public function test_webhook_mercadopago_renova_vencimento_cliente()
    {
        $revenda = User::where('whatsapp', '5511988888888')->first();
        $plano = Plano::where('user_id', $revenda->id)->first();

        $cliente = Cliente::create([
            'user_id' => $revenda->id,
            'nome' => 'Assinante Teste MP',
            'whatsapp' => '5511933333333',
            'vencimento' => Carbon::today()->addDays(2),
            'plano_id' => $plano->id,
            'notificacoes' => true,
        ]);

        $pagamento = Pagamento::create([
            'user_id' => $revenda->id,
            'cliente_id' => $cliente->id,
            'plano_id' => $plano->id,
            'mercado_pago_id' => 'MP_TEST_12345',
            'valor' => 35.00,
            'status' => 'pending',
        ]);

        $dataVencimentoAnterior = $cliente->vencimento->format('Y-m-d');

        $response = $this->postJson('/webhook/mercadopago', [
            'type' => 'payment',
            'data' => [
                'id' => 'MP_TEST_12345',
            ],
            'status' => 'approved',
        ]);

        $response->assertStatus(200);

        $pagamento->refresh();
        $this->assertEquals('approved', $pagamento->status);

        $cliente->refresh();
        $esperado = Carbon::parse($dataVencimentoAnterior)->addDays($plano->duracao)->format('Y-m-d');
        $this->assertEquals($esperado, $cliente->vencimento->format('Y-m-d'));
    }

    public function test_comando_cron_verificar_vencimentos()
    {
        $revenda = User::where('whatsapp', '5511988888888')->first();
        $plano = Plano::where('user_id', $revenda->id)->first();

        $clienteVenceHoje = Cliente::create([
            'user_id' => $revenda->id,
            'nome' => 'Cliente Vence Hoje',
            'whatsapp' => '5511944444444',
            'vencimento' => Carbon::today()->format('Y-m-d'),
            'plano_id' => $plano->id,
            'notificacoes' => true,
        ]);

        $this->artisan('cron:verificar-vencimentos')
            ->expectsOutputToContain('Encontrados 1 cliente(s) elegíveis')
            ->assertExitCode(0);

        $this->assertDatabaseHas('pagamentos', [
            'cliente_id' => $clienteVenceHoje->id,
            'status' => 'pending',
        ]);
    }

    public function test_portal_do_cliente_consulta_e_geracao_de_pix()
    {
        $revenda = User::where('whatsapp', '5511988888888')->first();
        $plano = Plano::where('user_id', $revenda->id)->first();

        $cliente = Cliente::create([
            'user_id' => $revenda->id,
            'nome' => 'Cliente Consulta Web',
            'whatsapp' => '5511955555555',
            'vencimento' => Carbon::today()->addDays(5),
            'plano_id' => $plano->id,
            'notificacoes' => true,
        ]);

        $response = $this->get('/portal?whatsapp=5511955555555');
        $response->assertStatus(200);
        $response->assertSee('Cliente Consulta Web');

        $responsePix = $this->post("/portal/pix/{$cliente->id}");
        $responsePix->assertRedirect();

        $this->assertDatabaseHas('pagamentos', [
            'cliente_id' => $cliente->id,
            'status' => 'pending',
        ]);
    }

    public function test_criptografia_de_tokens_em_company_details()
    {
        $revenda = User::where('whatsapp', '5511988888888')->first();
        $company = CompanyDetail::withoutGlobalScopes()->where('user_id', $revenda->id)->first();

        $secretToken = 'TEST_MP_SECRET_TOKEN_123456';
        $company->access_token = $secretToken;
        $company->save();

        $company->refresh();
        $this->assertEquals($secretToken, $company->access_token);

        $rawToken = DB::table('company_details')->where('id', $company->id)->value('access_token');
        $this->assertNotEquals($secretToken, $rawToken);
    }

    public function test_dashboard_agrega_metricas_de_clientes_corretamente()
    {
        $revenda = User::where('whatsapp', '5511988888888')->first();
        $hoje = Carbon::today();

        Cliente::create([
            'user_id' => $revenda->id,
            'nome' => 'Cliente Ativo Futuro',
            'whatsapp' => '5511900000001',
            'vencimento' => $hoje->copy()->addDays(5)->toDateString(),
        ]);

        Cliente::create([
            'user_id' => $revenda->id,
            'nome' => 'Cliente Vencendo Hoje',
            'whatsapp' => '5511900000002',
            'vencimento' => $hoje->copy()->toDateString(),
        ]);

        Cliente::create([
            'user_id' => $revenda->id,
            'nome' => 'Cliente Ja Vencido',
            'whatsapp' => '5511900000003',
            'vencimento' => $hoje->copy()->subDays(2)->toDateString(),
        ]);

        $this->actingAs($revenda);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);

        $response->assertViewHas('totalClientes', 3);
        $response->assertViewHas('clientesAtivos', 2);
        $response->assertViewHas('clientesVencidos', 1);
        $response->assertViewHas('vencendoHoje', 1);
    }
}
