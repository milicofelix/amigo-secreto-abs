<?php

namespace App\Http\Controllers\AmigoSecreto;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AmigoSecreto\Group;
use App\Models\AmigoSecreto\Participant;
use Illuminate\Support\Facades\Storage;

class ParticipantController extends Controller
{
    public function create(Group $group)
    {
        if ($group->draws()->active()->exists()) {
            return redirect()->route('groups.edit', $group);
        }

        return view('amigo-secreto.participants.create', compact('group'));
    }

    public function store(Request $request, Group $group)
    {
        $validated = $this->validateParticipant($request);

        // 🔐 Gera a chave secreta automaticamente
        $validated['secret_key'] = strtoupper(\Str::random(6));

        $group->modifyRoster(function (Group $locked) use ($request, $validated) {
            if ($request->hasFile('photo')) {
                $validated['photo'] = $request->file('photo')->store('participants', 'local');
            }

            $locked->participants()->create($validated);
        });

        return redirect()->route('groups.edit', $group)->with('success', 'Participante adicionado!');
    }

    public function edit(Group $group, Participant $participant)
    {
        if ($group->draws()->active()->exists()) {
            return redirect()->route('groups.edit', $group);
        }

        return view('amigo-secreto.participants.edit', compact('group', 'participant'));
    }

    public function update(Request $request, Group $group, Participant $participant)
    {
        $validated = $this->validateParticipant($request);

        $group->modifyRoster(function (Group $locked) use ($request, $validated, $participant) {
            if ($request->hasFile('photo')) {
                $validated['photo'] = $request->file('photo')->store('participants', 'local');
            }

            $locked->participants()->findOrFail($participant->id)->update($validated);
        });

        return redirect()->route('groups.edit', $group)->with('success', 'Participante atualizado!');
    }

    private function validateParticipant(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'whatsapp' => 'nullable|string',
            'wishlist' => 'nullable|string',
            'photo' => 'nullable|bail|image|max:2048',
        ], [
            'photo.image' => 'Escolha novamente uma imagem válida em JPG, PNG, WebP, GIF ou BMP.',
            'photo.max' => 'A foto deve ter até 2 MB. Escolha uma imagem menor.',
            'photo.uploaded' => 'Não foi possível receber a foto. Selecione novamente uma imagem de até 2 MB.',
        ]);
    }

    public function destroy(Group $group, Participant $participant)
    {
        $group->modifyRoster(fn (Group $locked) => $locked->participants()->findOrFail($participant->id)->delete());
        return redirect()->route('groups.edit', $group)->with('success', 'Participante removido!');
    }

    public function photo(Group $group, Participant $participant)
    {
        $path = $participant->photo;
        abort_unless(is_string($path) && preg_match('~\Aparticipants/[A-Za-z0-9_-]+\.(?:jpe?g|png|webp|gif|bmp)\z~i', $path), 404);

        $disk = Storage::disk('local');
        if (!$disk->exists($path)) {
            $disk = Storage::disk('public');
        }
        abort_unless($disk->exists($path), 404);

        return $disk->response($path, headers: [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
