<?php

namespace App\Console\Commands;

use App\Models\AmigoSecreto\Participant;
use App\Models\AmigoSecreto\ParticipantPushSubscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IssueParticipantPwaCode extends Command
{
    protected $signature = 'amigo-secreto:codigo-pwa {participant : ID do participante} {--rotate : Revoga o código e as inscrições anteriores}';

    protected $description = 'Gera um código privado de acesso ao PWA para um participante';

    public function handle(): int
    {
        $code = DB::transaction(function () {
            $participant = Participant::lockForUpdate()->find($this->argument('participant'));
            if (!$participant || ($participant->pwa_access_hash && !$this->option('rotate'))) {
                return null;
            }

            $code = Str::random(24);
            $participant->forceFill(['pwa_access_hash' => hash('sha256', $code)])->save();
            ParticipantPushSubscription::where('participant_id', $participant->id)->delete();

            return $code;
        });

        if (!$code) {
            $this->error('Participante inexistente ou código já emitido. Use --rotate apenas para revogar o acesso anterior.');

            return self::FAILURE;
        }

        $this->line($code);
        $this->info('Entregue este código em particular ao participante. Ele é exibido apenas agora.');

        return self::SUCCESS;
    }
}
