<?php

namespace App\Http\Middleware;

use App\Models\AmigoSecreto\Participant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyPwaParticipant
{
    public function handle(Request $request, Closure $next): Response
    {
        $id = $request->session()->get('pwa_participant.id');
        $hash = $request->session()->get('pwa_participant.hash');
        $participant = $id ? Participant::find($id) : null;

        if ($participant?->hasActiveInvite() && is_string($hash)
            && hash_equals($participant->pwa_access_hash, $hash)) {
            $request->attributes->set('pwa.participant', $participant);
        } else {
            $request->session()->forget('pwa_participant');
        }

        $response = $next($request);
        $response->headers->set('Cache-Control', 'private, no-store');
        $response->headers->set('Referrer-Policy', 'no-referrer');

        return $response;
    }
}
