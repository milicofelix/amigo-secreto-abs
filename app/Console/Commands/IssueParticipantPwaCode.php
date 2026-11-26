<?php

namespace App\Console\Commands;

use App\Models\AmigoSecreto\Participant;
use App\Services\ParticipantInvitation;
use Illuminate\Console\Command;

class IssueParticipantPwaCode extends Command
{
    protected $signature = 'amigo-secreto:codigo-pwa {participant : ID do participante} {--rotate : Substitui o convite anterior}';

    protected $description = 'Gera um código privado de acesso ao portal para um participante';

    public function handle(ParticipantInvitation $invitations): int
    {
        $participant = Participant::find($this->argument('participant'));
        $code = $participant ? $invitations->issue($participant, $this->option('rotate')) : null;

        if (!$code) {
            $this->error('Participante inexistente ou convite ativo. Use --rotate para substituir um convite ativo.');

            return self::FAILURE;
        }

        $this->line($code);
        $this->info('Entregue este código em particular ao participante. Ele é exibido apenas agora e vale por 30 dias.');

        return self::SUCCESS;
    }
}
