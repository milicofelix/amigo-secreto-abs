<?php

namespace App\Jobs;

use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class SendWhatsAppMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $phone;
    public string $message;

    /**
     * Cria o Job com os parâmetros necessários.
     */
    public function __construct(string $phone, string $message)
    {
        $this->phone = $phone;
        $this->message = $message;
        $this->onQueue('whatsapp');
    }

    /**
     * Executa o Job.
     */
    public function handle(WhatsAppService $whatsapp): void
    {
        Log::info('[QUEUE] Enviando WhatsApp para ' . $this->phone);
        $whatsapp->sendMessage($this->phone, $this->message);
    }
}
