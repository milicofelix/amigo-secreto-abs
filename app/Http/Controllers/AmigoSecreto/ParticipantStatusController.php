<?php

namespace App\Http\Controllers\AmigoSecreto;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ParticipantStatusController extends Controller
{
    public function update(Request $request)
    {
        $participant = $request->attributes->get('pwa.participant');
        abort_unless($participant, 401);
        $data = $request->validate([
            'participant_id' => ['required', 'integer'],
            'field' => ['required', 'in:bought_gift,shared_gift_idea'],
            'value' => ['required', 'in:1'],
        ]);
        abort_unless((int) $data['participant_id'] === $participant->id, 403);
        abort_unless($participant->givenAssignments()->whereHas(
            'draw', fn ($query) => $query->where('status', 'completed')
        )->exists(), 409);

        $field = $data['field'];
        $participant->newQuery()->withActiveInvite()->whereKey($participant->id)
            ->where('pwa_access_hash', $participant->pwa_access_hash)
            ->where($field, false)
            ->update([$field => true, $field.'_at' => now()]);
        $participant->refresh();
        abort_unless($participant->hasActiveInvite() && $participant->pwa_access_hash === $request->session()->get('pwa_participant.hash'), 401);
        abort_unless($participant->{$field}, 409);

        return response()->noContent();
    }

}
