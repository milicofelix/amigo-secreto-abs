<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\AmigoSecreto\WhatsAppLog;
use Illuminate\Support\Facades\Cache;

class WhatsAppService
{
    protected string $token;
    // protected string $phoneId;
    protected ?string $phoneId = null;

    public function __construct()
    {
        // $this->token = config('services.whatsapp.token');
        // $this->phoneId = config('services.whatsapp.phone_id');
        // $this->token = config('services.whatsapp.token');
        // $this->phoneId = Cache::get('whatsapp_phone_id'); // tenta pegar do cache

        // if (!$this->phoneId) {
        //     $this->phoneId = $this->fetchPhoneNumberId(); // busca automaticamente se não tiver
        //     Cache::put('whatsapp_phone_id', $this->phoneId, now()->addHours(6)); // guarda por 6h
        // }

        $this->token = config('services.whatsapp.token');
        $this->phoneId = config('services.whatsapp.phone_id'); // Prioriza o valor do .env

        // Se não existir, tenta buscar e cachear
        if (!$this->phoneId) {
            $this->phoneId = Cache::remember('whatsapp_phone_id', now()->addHours(6), function () {
                return $this->fetchPhoneNumberId();
            });
        }
    }

    /**
     * Obtém o Phone Number ID associado à conta do WhatsApp Business (WABA)
     */
    protected function fetchPhoneNumberId(): ?string
    {
        $wabaId = config('services.whatsapp.waba_id');

        if (!$wabaId) {
            Log::error('[WHATSAPP] WABA_ID ausente no .env');
            return null;
        }

        $url = "https://graph.facebook.com/v20.0/{$wabaId}/phone_numbers";

        try {
            $response = Http::withToken($this->token)->get($url);

            if ($response->failed()) {
                Log::error('[WHATSAPP] Falha ao buscar Phone Number ID', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);
                return null;
            }

            $data = $response->json();
            $phoneId = $data['data'][0]['id'] ?? null;

            if ($phoneId) {
                Log::info("[WHATSAPP] Phone Number ID detectado: {$phoneId}");
            } else {
                Log::warning('[WHATSAPP] Nenhum número de telefone encontrado na conta WABA');
            }

            return $phoneId;
        } catch (\Throwable $e) {
            Log::error('[WHATSAPP] Erro ao obter Phone Number ID', [
                'exception' => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function sendMessage(string $to, string $message): bool
    {
        if (!$this->phoneId) {
            Log::error('[WHATSAPP] Falha: Phone ID não disponível.');
            return false;
        }
        
        $url = "https://graph.facebook.com/v20.0/{$this->phoneId}/messages";
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->sanitizePhone($to),
            'type' => 'text',
            'text' => [
                'preview_url' => false,
                'body' => $message,
            ],
        ];

        try {
            $response = Http::withOptions([
                    'force_ip_resolve' => 'v4',
                    'verify' => true,
                ])
                ->withToken($this->token)
                ->timeout(45)
                ->post($url, $payload);

            if ($response->failed()) {
                 WhatsAppLog::create([
                    'phone' => $to,
                    'message' => $message,
                    'status' => 'failed',
                    'response' => $response->json(),
                    'error_message' => json_encode($response->json()),
                ]);
                Log::error('[WHATSAPP] Falha ao enviar mensagem', [
                    'to' => $to,
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);
                return false;
            }

             WhatsAppLog::create([
                'phone' => $to,
                'message' => $message,
                'status' => 'success',
                'response' => $response->json(),
            ]);

            Log::info('[WHATSAPP] Mensagem enviada com sucesso', [
                'to' => $to,
                'response' => $response->json(),
            ]);

            return true;

        } catch (\Throwable $e) {
            WhatsAppLog::create([
                'phone' => $to,
                'message' => $message,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            Log::error('[WHATSAPP] Erro de conexão', [
                'to' => $to,
                'exception' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function sendTemplate(string $to, ?string $templateName = null): bool
    {
        $templateName = $templateName ?? config('services.whatsapp.template_name', 'as2025');
        $apiUrl = rtrim(config('services.whatsapp.api_url', 'https://graph.facebook.com/v20.0'), '/');
        $url = "{$apiUrl}/{$this->phoneId}/messages";

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->sanitizePhone($to),
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => 'en_US'],
            ],
        ];

        try {
            $response = Http::withToken($this->token)->timeout(45)->post($url, $payload);

            if ($response->failed()) {
                WhatsAppLog::create([
                    'phone' => $to,
                    'message' => "TEMPLATE: {$templateName}",
                    'status' => 'failed',
                    'response' => $response->json(),
                    'error_message' => json_encode($response->json()),
                ]);
                Log::error('[WHATSAPP] Falha ao enviar template', [
                    'to' => $to,
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);
                return false;
            }

            WhatsAppLog::create([
                'phone' => $to,
                'message' => "TEMPLATE: {$templateName}",
                'status' => 'success',
                'response' => $response->json(),
            ]);

            Log::info('[WHATSAPP] Template enviado com sucesso', [
                'to' => $to,
                'response' => $response->json(),
            ]);

            return true;

        } catch (\Throwable $e) {
            WhatsAppLog::create([
                'phone' => $to,
                'message' => "TEMPLATE: {$templateName}",
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            Log::error('[WHATSAPP] Erro ao enviar template', [
                'to' => $to,
                'exception' => $e->getMessage(),
            ]);
            return false;
        }
    }


    private function sanitizePhone(string $phone): string
    {
        return preg_replace('/\D/', '', $phone);
    }
}
