<?php

namespace App\Http\Controllers\AmigoSecreto;

use App\Http\Controllers\Controller;
use App\Jobs\SendParticipantRecovery;
use App\Models\AmigoSecreto\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class ParticipantRecoveryController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:255']]);
        $email = trim($data['email']);

        RateLimiter::attempt('participant-recovery:'.Participant::emailLookupHash($email), 3, function () use ($email) {
            try {
                SendParticipantRecovery::dispatch($email);
            } catch (\Throwable) {
                Log::warning('Falha ao iniciar a recuperação de acesso do participante.');
            }
        }, 900);

        return redirect()->route('pwa.participant')->with('recovery_status',
            'Se houver um convite associado a esse e-mail, você receberá um link de acesso. Você também pode pedir o link ao organizador.'
        );
    }
}
