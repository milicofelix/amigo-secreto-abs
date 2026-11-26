<?php

namespace App\Services;

use App\Models\AmigoSecreto\Participant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ParticipantInvitation
{
    public function issue(Participant $participant, bool $replace = false): ?string
    {
        return DB::transaction(function () use ($participant, $replace) {
            $current = $participant->newQuery()->lockForUpdate()->findOrFail($participant->id);
            if ($current->hasActiveInvite() && !$replace) {
                return null;
            }

            $code = Str::random(24);
            $current->forceFill([
                'pwa_access_hash' => hash('sha256', $code),
                'invite_expires_at' => now()->addDays(30),
            ])->save();

            return $code;
        });
    }

    public function revoke(Participant $participant): void
    {
        $participant->newQuery()->whereKey($participant->id)->update(['pwa_access_hash' => null, 'invite_expires_at' => null]);
    }
}
