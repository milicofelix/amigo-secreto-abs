<?php

namespace App\Http\Controllers\AmigoSecreto;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AmigoSecreto\Group;
use App\Models\AmigoSecreto\Restriction;

class RestrictionController extends Controller
{
     public function store(Request $request, Group $group)
    {
        $validated = $request->validate([
            'participant_id'   => 'required|exists:participants,id',
            'cannot_draw_id'   => 'required|exists:participants,id|different:participant_id',
        ]);

        Restriction::create([
            'group_id'          => $group->id,
            'participant_id'    => $validated['participant_id'],
            'cannot_draw_id'    => $validated['cannot_draw_id'],
        ]);

        return redirect()->route('groups.edit', $group)->with('success', 'Restrição adicionada!');
    }

    public function destroy(Group $group, Restriction $restriction)
    {
        $restriction->delete();
        return redirect()->route('groups.edit', $group)->with('success', 'Restrição removida!');
    }
}
