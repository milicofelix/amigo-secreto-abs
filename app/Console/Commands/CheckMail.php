<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class CheckMail extends Command
{
    protected $signature = 'check:mail
                            {--to= : Email de destino (default = MAIL_FROM_ADDRESS)}
                            {--subject=Teste de E-mail : Assunto do teste}
                            {--timeout=5 : Timeout TCP em segundos}';

    protected $description = 'Verifica conexão SMTP e tenta enviar um e-mail de teste.';

    public function handle()
    {
        $to = $this->option('to') ?: config('mail.from.address');
        $subject = $this->option('subject');
        $timeout = (int) $this->option('timeout');

        $this->info("Usando remetente: " . config('mail.from.address'));
        $this->info("Tentando destinatário: {$to}");

        // 1) Pegando dados do mailer SMTP (se existir)
        $mailer = config('mail.default') ?: 'smtp';
        $mailerConfig = config("mail.mailers.{$mailer}");

        if (!$mailerConfig) {
            $this->warn("Config do mailer '{$mailer}' não encontrada. config/mail.php revisa.");
        } else {
            $host = $mailerConfig['host'] ?? '(host não configurado)';
            $port = $mailerConfig['port'] ?? '(porta não configurada)';
            $this->info("Mailer: {$mailer} -> {$host}:{$port}");
        }

        // 2) Teste TCP básico (porta aberta?)
        if (!empty($host) && is_string($host) && is_numeric($port)) {
            $this->info("Teste TCP: conectando em {$host}:{$port} (timeout {$timeout}s)...");
            $fp = @fsockopen($host, (int)$port, $errno, $errstr, $timeout);
            if ($fp) {
                fclose($fp);
                $this->info("✅ Conexão TCP bem-sucedida com {$host}:{$port}");
            } else {
                $this->error("❌ Falha TCP: [{$errno}] {$errstr} — pode ser bloqueio de rede/porta ou host incorreto.");
            }
        } else {
            $this->warn("Ignorando teste TCP porque host/porta não detectados.");
        }

        // 3) Tenta enviar um e-mail de teste (síncrono)
        $this->info("Tentando enviar e-mail de teste (envio síncrono)...");
        try {
            Mail::raw("Mensagem de teste enviada em " . now()->toDateTimeString(), function ($m) use ($to, $subject) {
                $m->to($to)->subject($subject);
            });

            $this->info("✅ Envio tentado. Verifique logs / painel do provedor (ou a caixa de entrada).");
            return 0;
        } catch (Exception $e) {
            $this->error("❌ Erro ao enviar e-mail: " . $e->getMessage());
            Log::error('[check:mail] Erro ao enviar e-mail de teste', ['exception' => $e]);
            return 2;
        }
    }
}
