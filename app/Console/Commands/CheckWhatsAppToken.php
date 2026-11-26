<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CheckWhatsAppToken extends Command
{
    /**
     * O nome e a assinatura do comando.
     *
     * Exemplo de uso:
     * php artisan check:whatsapp-token
     */
    protected $signature = 'check:whatsapp-token';

    /**
     * A descrição do comando.
     */
    protected $description = 'Verifica se o token do WhatsApp Cloud API ainda é válido e funcional';

    /**
     * Executa o comando.
     */
    public function handle()
    {
        $token = env('WHATSAPP_TOKEN');
        $phoneId = env('WHATSAPP_PHONE_ID');

        if (!$token || !$phoneId) {
            $this->error('❌ Variáveis WHATSAPP_TOKEN ou WHATSAPP_PHONE_ID não configuradas no .env');
            return Command::FAILURE;
        }

        $this->info('🔍 Verificando status do token do WhatsApp Cloud API...');

        try {
            // Faz uma requisição simples à API de mensagens (sem enviar nada)
            $response = Http::withToken($token)->get("https://graph.facebook.com/v22.0/{$phoneId}");

            if ($response->successful()) {
                $this->info('✅ Token ativo! O WhatsApp Cloud API está acessível.');
                \Log::info('[WHATSAPP] Token válido e API acessível.');
                return Command::SUCCESS;
            } elseif ($response->status() === 400 || $response->status() === 401) {
                $this->warn('⚠️ Token expirado ou inválido.');
                Log::warning('[WHATSAPP] Token expirado ou inválido. Gere um novo no painel da Meta.');
            } else {
                $this->error('❌ Erro inesperado: ' . $response->body());
                \Log::error('[WHATSAPP] Erro inesperado ao verificar token: ' . $response->body());
            }
        } catch (\Exception $e) {
            $this->error('❌ Falha de conexão: ' . $e->getMessage());
            \Log::error('[WHATSAPP] Falha ao verificar token: ' . $e->getMessage());
        }

        return Command::FAILURE;
    }
}
