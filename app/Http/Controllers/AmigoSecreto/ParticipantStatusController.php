<?php

namespace App\Http\Controllers\AmigoSecreto;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AmigoSecreto\Participant;
use Illuminate\Support\Facades\Schema;

class ParticipantStatusController extends Controller
{
    public function update(Request $request)
    {
        $participant = Participant::findOrFail($request->participant_id);

        $field = $request->field; // 'shared_gift_idea' ou 'bought_gift'
        $value = (int) $request->value;

        // 🔒 se já está marcado, não mexe mais
        if ($participant->$field == 1) {
            return response()->json(['locked' => true]);
        }

        if ($value === 1) {
            $participant->$field = 1;

            // se existir campo *_at, preenche
            $atField = $field . '_at';
            if (Schema::hasColumn('participants', $atField)) {
                $participant->$atField = now();
            }

            $participant->save();
        }

        return response()->json(['success' => true]);
    }

}
