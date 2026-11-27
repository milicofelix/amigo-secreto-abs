<?php

namespace App\Jobs;

use App\Mail\SecretFriendNotification;
use App\Models\AmigoSecreto\AsDraw;
use App\Models\AmigoSecreto\Participant;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendDrawEmail implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [30, 120];

    public int $uniqueFor = 3600;

    public function __construct(public int $drawId)
    {
        $this->onQueue('emails');
    }

    public function uniqueId(): string
    {
        return (string) $this->drawId;
    }

    public function handle(): void
    {
        $draw = AsDraw::active()->find($this->drawId);
        if (!$draw) {
            return;
        }

        $recipients = Participant::whereIn('id', $draw->assignments()->select('giver_id'))
            ->where('group_id', $draw->group_id)->where('draw_email_enabled', true)->whereNotNull('email');
        $retry = false;
        foreach ($recipients->lazyById() as $participant) {
            if (!AsDraw::active()->whereKey($draw->id)->exists()) {
                return;
            }

            if (!filter_var($participant->email, FILTER_VALIDATE_EMAIL)
                || $draw->events()->where('type', 'email_sent')->where('details->participant_id', $participant->id)->exists()) {
                continue;
            }

            $type = 'email_sent';
            try {
                Mail::to($participant->email)->send(new SecretFriendNotification);
            } catch (\Throwable) {
                $type = 'email_failed';
                $retry = true;
            }
            $draw->events()->create(['type' => $type, 'details' => ['participant_id' => $participant->id]]);
        }

        if ($retry) {
            throw new \RuntimeException('Falha ao processar um ou mais avisos por e-mail.');
        }
    }
}
