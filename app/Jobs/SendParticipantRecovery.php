<?php

namespace App\Jobs;

use App\Mail\ParticipantAccessRecovery;
use App\Services\ParticipantInvitation;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class SendParticipantRecovery implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [30, 120];

    public function __construct(private string $email)
    {
        $this->onQueue('emails');
    }

    public function handle(ParticipantInvitation $invitations): void
    {
        $links = $invitations->recoveryLinks($this->email);
        if ($links === []) {
            return;
        }

        try {
            Mail::to($this->email)->send(new ParticipantAccessRecovery($links));
        } catch (\Throwable) {
            throw new RuntimeException('Falha ao enviar a recuperação de acesso do participante.');
        }
    }
}
