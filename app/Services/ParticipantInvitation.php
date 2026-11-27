<?php

namespace App\Services;

use App\Models\AmigoSecreto\Participant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ParticipantInvitation
{
    public const CODE_LENGTH = 24;

    public const RECOVERY_LENGTH = 48;

    private const CLEAR_RECOVERY = ['invite_recovery_hash' => null, 'invite_recovery_expires_at' => null];

    public function issue(Participant $participant, bool $replace = false): ?string
    {
        return DB::transaction(function () use ($participant, $replace) {
            $current = $participant->newQuery()->lockForUpdate()->findOrFail($participant->id);
            if ($current->hasActiveInvite() && !$replace) {
                return null;
            }

            return $this->writeInvite($current);
        });
    }

    public function revoke(Participant $participant): void
    {
        $participant->newQuery()->whereKey($participant->id)->update([
            'pwa_access_hash' => null, 'invite_code' => null, 'invite_expires_at' => null,
            ...self::CLEAR_RECOVERY,
        ]);
    }

    public function recoveryLinks(string $email): array
    {
        $lookup = Participant::emailLookupHash($email);
        $links = [];

        $participants = Participant::with('group:id,name')
            ->where('email_lookup_hash', $lookup)->whereNotNull('pwa_access_hash');

        foreach ($participants->lazyById() as $participant) {
            $code = Str::random(self::RECOVERY_LENGTH);
            $updated = Participant::whereKey($participant->id)->where('email_lookup_hash', $lookup)
                ->where('pwa_access_hash', $participant->pwa_access_hash)
                ->update([
                    'invite_recovery_hash' => hash('sha256', $code),
                    'invite_recovery_expires_at' => now()->addMinutes(15),
                ]);

            if ($updated) {
                $links[] = ['label' => $participant->name.' · '.$participant->group->name, 'url' => route('pwa.participant').'#convite='.$code];
            }
        }

        return $links;
    }

    public function redeem(string $code): ?Participant
    {
        $hash = hash('sha256', $code);

        return DB::transaction(function () use ($code, $hash) {
            if (strlen($code) === self::RECOVERY_LENGTH) {
                $participant = Participant::whereNotNull('pwa_access_hash')->where('invite_recovery_hash', $hash)
                    ->where('invite_recovery_expires_at', '>', now())->lockForUpdate()->first();

                if ($participant && !$participant->hasActiveInvite()) {
                    $this->writeInvite($participant);
                } elseif ($participant) {
                    $participant->forceFill(self::CLEAR_RECOVERY)->save();
                }
            } else {
                $participant = Participant::withActiveInvite()->where('pwa_access_hash', $hash)->lockForUpdate()->first();
            }

            $participant?->update(['last_access_at' => now()]);

            return $participant;
        });
    }

    private function writeInvite(Participant $participant): string
    {
        $code = Str::random(self::CODE_LENGTH);
        $participant->forceFill([
            'pwa_access_hash' => hash('sha256', $code),
            'invite_code' => $code,
            'invite_expires_at' => now()->addDays(30),
            ...self::CLEAR_RECOVERY,
        ])->save();

        return $code;
    }
}
