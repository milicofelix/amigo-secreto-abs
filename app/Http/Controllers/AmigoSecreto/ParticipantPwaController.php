<?php

namespace App\Http\Controllers\AmigoSecreto;

use App\Http\Controllers\Controller;
use App\Jobs\SendDrawWebPush;
use App\Models\AmigoSecreto\Participant;
use App\Models\AmigoSecreto\ParticipantPushSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ParticipantPwaController extends Controller
{
    public function show(Request $request)
    {
        $participant = $request->attributes->get('pwa.participant');
        $assignment = $participant?->givenAssignments()
            ->whereHas('draw', fn ($query) => $query->where('status', 'completed'))
            ->with('receiver')->latest('id')->first();

        return view('amigo-secreto.pwa.participant', compact('participant', 'assignment'));
    }

    public function verify(Request $request)
    {
        $data = $request->validate(['code' => ['required', 'string', 'size:24']], [
            'code.required' => 'Informe seu código privado.',
            'code.size' => 'Código privado inválido.',
        ]);
        $hash = hash('sha256', $data['code']);
        $participant = Participant::where('pwa_access_hash', $hash)->first();

        if (!$participant) {
            throw ValidationException::withMessages(['code' => 'Código privado inválido.']);
        }

        $request->session()->regenerate(true);
        $request->session()->put('pwa_participant', ['id' => $participant->id, 'hash' => $hash]);

        return redirect()->route('pwa.participant');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('pwa_participant');
        $request->session()->regenerate(true);

        return redirect()->route('pwa.participant');
    }

    public function subscribe(Request $request)
    {
        $participant = $request->attributes->get('pwa.participant');
        abort_unless($participant, 401);
        abort_unless(SendDrawWebPush::enabled(), 503);

        $subscription = $request->validate([
            'endpoint' => ['required', 'url:https', 'max:2048'],
            'keys' => ['required', 'array:p256dh,auth'],
            'keys.p256dh' => ['required', 'regex:/^[A-Za-z0-9_-]{87}$/D'],
            'keys.auth' => ['required', 'regex:/^[A-Za-z0-9_-]{22}$/D'],
        ]);

        $endpoint = parse_url($subscription['endpoint']);
        $host = strtolower($endpoint['host'] ?? '');
        $trustedHost = in_array($host, ['fcm.googleapis.com', 'updates.push.services.mozilla.com'], true)
            || str_ends_with($host, '.push.apple.com') || str_ends_with($host, '.notify.windows.com');

        if (!$trustedHost || isset($endpoint['user']) || isset($endpoint['fragment'])
            || ($endpoint['port'] ?? 443) !== 443) {
            throw ValidationException::withMessages(['endpoint' => 'Serviço de notificações incompatível.']);
        }

        DB::transaction(function () use ($participant, $subscription) {
            $currentParticipant = Participant::lockForUpdate()->find($participant->id);
            abort_unless($currentParticipant?->pwa_access_hash === $participant->pwa_access_hash, 401);
            $record = ParticipantPushSubscription::firstOrCreate(
                ['endpoint_hash' => hash('sha256', $subscription['endpoint'])],
                ['participant_id' => $participant->id, 'subscription' => $subscription],
            );
            abort_unless($record->participant_id === $participant->id, 409);
            $record->update(['subscription' => $subscription]);
        });

        return response()->noContent();
    }

    public function unsubscribe(Request $request)
    {
        $participant = $request->attributes->get('pwa.participant');
        abort_unless($participant, 401);
        $data = $request->validate(['endpoint' => ['required', 'string', 'max:2048']]);

        ParticipantPushSubscription::where('participant_id', $participant->id)
            ->where('endpoint_hash', hash('sha256', $data['endpoint']))->delete();

        return response()->noContent();
    }
}
