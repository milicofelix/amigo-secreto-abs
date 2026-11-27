<?php

namespace App\Http\Controllers\AmigoSecreto;

use App\Http\Controllers\Controller;
use App\Models\AmigoSecreto\Group;

class RevealController extends Controller
{
    public function legacy()
    {
        return redirect()->route('pwa.participant')->with(
            'invite_error',
            'Esse acesso foi substituído. Peça um convite atualizado ao organizador.'
        );
    }

    public function listRevealed(Group $group)
    {
        $revealed = $group->participants()->whereNotNull('first_revealed_at')
            ->oldest('first_revealed_at')->get(['name', 'first_revealed_at']);

        return view('amigo-secreto.reveal.list', compact('revealed', 'group'));
    }
}
