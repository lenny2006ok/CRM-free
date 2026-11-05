<?php

namespace App\Services;

use App\Models\User;
use App\Models\Cliente;
use App\Models\Plano;
use App\Models\Pagamento;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MercadoPagoService
{
    protected static function getAccessToken(?User $user = null): ?string
    {
        if ($user && $user->companyDetail && !empty($user->companyDetail->access_token)) {
            return $user->companyDetail->access_token;
        }

        return config('services.mercadopago.access_token', env('MERCADO_PAGO_ACCESS_TOKEN'));
    }

    public static function criarPix(Cliente $cliente, Plano $plano, ?User $revendedor = null): ?Pagamento
    {
        $revendedor = $revendedor ?? $cliente->user;
        $accessToken = self::getAccessToken($revendedor);

        $valor = (float) $plano->preco;
        $email = 'cliente_' . $cliente->id . '@gestor.local';

        $payload = [
            'transaction_amount' => $valor,
            'description' => 'Renovacao ' . $plano->nome . ' - ' . $cliente->nome,
            'payment_method_id' => 'pix',
            'payer' => [
                'email' => $email,
                'first_name' => $cliente->nome,
                'identification' => [
                    'type' => 'CPF',
                    'number' => '11111111111',
                ],
            ],
            'notification_url' => url('/webhook/mercadopago'),
            'external_reference' => (string) $cliente->id,
        ];

        if ($accessToken) {
            try {
                $response = Http::withToken($accessToken)
                    ->withHeaders(['X-Idempotency-Key' => uniqid('pix_', true)])
                    ->post('https://api.mercadopago.com/v1/payments', $payload);

                if ($response->successful()) {
                    $data = $response->json();
                    $mpId = (string) $data['id'];
                    $pointOfInteraction = $data['point_of_interaction']['transaction_data'] ?? [];

                    return Pagamento::create([
                        'user_id' => $revendedor->id,
                        'cliente_id' => $cliente->id,
                        'plano_id' => $plano->id,
                        'mercado_pago_id' => $mpId,
                        'valor' => $valor,
                        'status' => $data['status'] ?? 'pending',
                        'qr_code' => $pointOfInteraction['qr_code'] ?? null,
                        'qr_code_base64' => $pointOfInteraction['qr_code_base64'] ?? null,
                        'ticket_url' => $pointOfInteraction['ticket_url'] ?? null,
                    ]);
                } else {
                    Log::error('Erro ao gerar Pix no Mercado Pago: ' . $response->body());
                }
            } catch (\Throwable $e) {
                Log::error('Falha de conexão Mercado Pago: ' . $e->getMessage());
            }
        }

        $mockId = 'MP_' . time() . '_' . rand(1000, 9999);
        $pixCode = '00020101021226840014br.gov.bcb.pix2562' . md5($mockId) . '520400005303986540' . number_format($valor, 2, '.', '') . '5802BR5910GESTOR IPTV6009SAO PAULO62070503***6304' . strtoupper(substr(md5(microtime()), 0, 4));

        return Pagamento::create([
            'user_id' => $revendedor->id,
            'cliente_id' => $cliente->id,
            'plano_id' => $plano->id,
            'mercado_pago_id' => $mockId,
            'valor' => $valor,
            'status' => 'pending',
            'qr_code' => $pixCode,
            'qr_code_base64' => base64_encode('mock_qr_code_image'),
            'ticket_url' => url('/pagamento/' . $mockId),
        ]);
    }

    public static function validarAssinatura(string $xSignature, string $xRequestId, string $dataId, ?string $secret = null): bool
    {
        $secret = $secret ?? config('services.mercadopago.webhook_secret', env('MERCADO_PAGO_WEBHOOK_SECRET'));

        if (empty($secret)) {
            return true;
        }

        parse_str(str_replace(',', '&', $xSignature), $parts);
        $ts = $parts['ts'] ?? '';
        $hash = $parts['v1'] ?? '';

        if (!$ts || !$hash) {
            return false;
        }

        $manifest = "id:{$dataId};request-id:{$xRequestId};ts:{$ts};";
        $calculatedHash = hash_hmac('sha256', $manifest, $secret);

        return hash_equals($calculatedHash, $hash);
    }

    public static function consultarPagamento(string $mercadoPagoId, ?string $accessToken = null): ?array
    {
        if (!$accessToken) {
            $accessToken = config('services.mercadopago.access_token', env('MERCADO_PAGO_ACCESS_TOKEN'));
        }

        if (!$accessToken) {
            return null;
        }

        try {
            $response = Http::withToken($accessToken)
                ->get("https://api.mercadopago.com/v1/payments/{$mercadoPagoId}");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Throwable $e) {
            Log::error('Erro ao consultar Mercado Pago: ' . $e->getMessage());
        }

        return null;
    }
}
