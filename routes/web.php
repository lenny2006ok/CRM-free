<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\GerenciamentoController;
use App\Http\Controllers\ClientePortalController;
use App\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| Webhook Routes (Mercado Pago e WhatsApp Evolution API)
| Isentos de CSRF via bootstrap/app.php
|--------------------------------------------------------------------------
*/
Route::post('/webhook/mercadopago', [WebhookController::class, 'mercadoPago'])->name('webhook.mercadopago');
Route::post('/webhook/evolution', [WebhookController::class, 'evolution'])->name('webhook.evolution');

/*
|--------------------------------------------------------------------------
| Central do Cliente (Pública / Portal do Assinante com Rate Limiting)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['throttle:30,1'])->group(function () {
    Route::get('/portal', [ClientePortalController::class, 'portal'])->name('cliente.portal');
    Route::get('/pagamento/{id}', [ClientePortalController::class, 'visualizacaoPagamento'])->name('cliente.pagamento');
    Route::get('/pagamento/{id}/status', [ClientePortalController::class, 'checarStatus'])->name('cliente.pagamento.status');
});

Route::post('/portal/pix/{id}', [ClientePortalController::class, 'gerarPix'])
    ->middleware(['throttle:10,1'])
    ->name('cliente.gerar-pix');

/*
|--------------------------------------------------------------------------
| Autenticação (Login, Registro e Logout com proteção contra Brute-Force)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Painel Administrativo / Revendedor (Autenticado com RBAC)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Clientes
    Route::resource('clientes', ClienteController::class);
    Route::post('/clientes/{cliente}/renovar', [ClienteController::class, 'renovarManual'])->name('clientes.renovar');
    Route::post('/clientes/{cliente}/enviar-pix', [ClienteController::class, 'enviarPix'])->name('clientes.enviar-pix');

    // Planos
    Route::get('/planos', [GerenciamentoController::class, 'planos'])->name('planos.index');
    Route::post('/planos', [GerenciamentoController::class, 'salvarPlano'])->name('planos.salvar');
    Route::delete('/planos/{plano}', [GerenciamentoController::class, 'deletarPlano'])->name('planos.destroy');

    // Servidores
    Route::get('/servidores', [GerenciamentoController::class, 'servidores'])->name('servidores.index');
    Route::post('/servidores', [GerenciamentoController::class, 'salvarServidor'])->name('servidores.salvar');
    Route::delete('/servidores/{servidor}', [GerenciamentoController::class, 'deletarServidor'])->name('servidores.destroy');

    // Templates de WhatsApp
    Route::get('/templates', [GerenciamentoController::class, 'templates'])->name('templates.index');
    Route::post('/templates', [GerenciamentoController::class, 'salvarTemplate'])->name('templates.salvar');
    Route::delete('/templates/{template}', [GerenciamentoController::class, 'deletarTemplate'])->name('templates.destroy');

    // WhatsApp / Conexão QR Code
    Route::get('/whatsapp', [GerenciamentoController::class, 'conexaoWhatsapp'])->name('whatsapp.index');
    Route::post('/whatsapp/conectar', [GerenciamentoController::class, 'conectarWhatsapp'])->name('whatsapp.conectar');
    Route::post('/whatsapp/desconectar', [GerenciamentoController::class, 'desconectarWhatsapp'])->name('whatsapp.desconectar');

    // Campanhas em Massa
    Route::get('/campanhas', [GerenciamentoController::class, 'campanhas'])->name('campanhas.index');
    Route::post('/campanhas', [GerenciamentoController::class, 'salvarCampanha'])->name('campanhas.salvar');

    // Relatórios de Pagamentos
    Route::get('/pagamentos', [GerenciamentoController::class, 'pagamentos'])->name('pagamentos.index');

    // Configurações da Empresa / Credenciais de API
    Route::get('/configuracoes', [GerenciamentoController::class, 'configuracoes'])->name('configuracoes.index');
    Route::post('/configuracoes', [GerenciamentoController::class, 'salvarConfiguracoes'])->name('configuracoes.salvar');
});
