<?php

namespace App\Services;

use App\Models\User;
use App\Models\Conexao;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvolutionApiService
{
    protected static function getBaseUrl(?User $user = null): string
    {
        if ($user && $user->companyDetail && !empty($user->companyDetail->evolution_api_url)) {
            return rtrim($user->companyDetail->evolution_api_url, '/');
        }

        return rtrim(config('services.evolution.url', env('EVOLUTION_API_URL', 'http://localhost:8080')), '/');
    }

    protected static function getApiKey(?User $user = null): ?string
    {
        if ($user && $user->companyDetail && !empty($user->companyDetail->evolution_api_key)) {
            return $user->companyDetail->evolution_api_key;
        }

        return config('services.evolution.api_key', env('EVOLUTION_API_KEY'));
    }

    public static function formatNumber(string $number): string
    {
        $cleaned = preg_replace('/\D/', '', $number);
        if (strlen($cleaned) === 10 || strlen($cleaned) === 11) {
            $cleaned = '55' . $cleaned;
        }
        return $cleaned;
    }

    public static function criarInstancia(User $user, string $instanceName): array
    {
        $baseUrl = self::getBaseUrl($user);
        $apiKey = self::getApiKey($user);

        if (!$apiKey) {
            return [
                'status' => 'mock',
                'instance' => $instanceName,
                'qrcode' => 'mock_qrcode_base64_data',
            ];
        }

        try {
            $response = Http::withHeaders(['apikey' => $apiKey])
                ->post("{$baseUrl}/instance/create", [
                    'instanceName' => $instanceName,
                    'token' => md5($instanceName . time()),
                    'qrcode' => true,
                ]);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('Erro ao criar instância Evolution API: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public static function obterQrCode(User $user, string $instanceName): array
    {
        $baseUrl = self::getBaseUrl($user);
        $apiKey = self::getApiKey($user);

        if (!$apiKey) {
            return [
                'status' => 'connected',
                'qrcode' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY44YAAAAASUVORK5CYII=',
            ];
        }

        try {
            $response = Http::withHeaders(['apikey' => $apiKey])
                ->get("{$baseUrl}/instance/connect/{$instanceName}");

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('Erro ao obter QRCode Evolution API: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public static function statusInstancia(User $user, string $instanceName): array
    {
        $baseUrl = self::getBaseUrl($user);
        $apiKey = self::getApiKey($user);

        if (!$apiKey) {
            return ['instance' => ['state' => 'open']];
        }

        try {
            $response = Http::withHeaders(['apikey' => $apiKey])
                ->get("{$baseUrl}/instance/connectionState/{$instanceName}");

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('Erro ao verificar status da instância Evolution API: ' . $e->getMessage());
            return ['instance' => ['state' => 'close']];
        }
    }

    public static function enviarTexto(User $user, string $numero, string $texto, ?string $instanceName = null): bool
    {
        $baseUrl = self::getBaseUrl($user);
        $apiKey = self::getApiKey($user);
        $instance = $instanceName ?? ('user_' . $user->id);
        $numeroFormatado = self::formatNumber($numero);

        if (!$apiKey) {
            Log::info("[MOCK WHATSAPP] Enviado para {$numeroFormatado}: {$texto}");
            return true;
        }

        try {
            $response = Http::withHeaders(['apikey' => $apiKey])
                ->post("{$baseUrl}/message/sendText/{$instance}", [
                    'number' => $numeroFormatado,
                    'text' => $texto,
                    'options' => [
                        'delay' => 1200,
                        'presence' => 'composing',
                    ],
                ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('Erro ao enviar mensagem Evolution API: ' . $e->getMessage());
            return false;
        }
    }

    public static function enviarMidia(User $user, string $numero, string $mediaUrl, string $caption = '', ?string $instanceName = null): bool
    {
        $baseUrl = self::getBaseUrl($user);
        $apiKey = self::getApiKey($user);
        $instance = $instanceName ?? ('user_' . $user->id);
        $numeroFormatado = self::formatNumber($numero);

        if (!$apiKey) {
            Log::info("[MOCK WHATSAPP MEDIA] Enviado para {$numeroFormatado}: {$mediaUrl} - Legenda: {$caption}");
            return true;
        }

        try {
            $response = Http::withHeaders(['apikey' => $apiKey])
                ->post("{$baseUrl}/message/sendMedia/{$instance}", [
                    'number' => $numeroFormatado,
                    'mediaMessage' => [
                        'mediatype' => 'image',
                        'media' => $mediaUrl,
                        'caption' => $caption,
                    ],
                ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('Erro ao enviar mídia Evolution API: ' . $e->getMessage());
            return false;
        }
    }
}
