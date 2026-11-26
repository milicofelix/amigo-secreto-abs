<?php

namespace App\Http\Controllers\AmigoSecreto;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AmigoSecreto\Group;
use App\Models\AmigoSecreto\Participant;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeMode;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\Alignment\LabelAlignmentCenter;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use App\Helpers\AvatarHelper;

class ParticipantController extends Controller
{
    public function create(Group $group)
    {
        return view('amigo-secreto.participants.create', compact('group'));
    }

    public function store(Request $request, Group $group)
    {
        $validated = $this->validateParticipant($request);

        // 🔐 Gera a chave secreta automaticamente
        $validated['secret_key'] = strtoupper(\Str::random(6));

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('participants', 'public');
        }

        $group->participants()->create($validated);

        return redirect()->route('groups.edit', $group)->with('success', 'Participante adicionado!');
    }

    public function edit(Group $group, Participant $participant)
    {
        return view('amigo-secreto.participants.edit', compact('group', 'participant'));
    }

    public function update(Request $request, Group $group, Participant $participant)
    {
        $validated = $this->validateParticipant($request);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('participants', 'public');
        }

        $participant->update($validated);

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
        $participant->delete();
        return redirect()->route('groups.edit', $group)->with('success', 'Participante removido!');
    }

    public function accessByKey(Group $group, $id)
    {
        $participant = Participant::findOrFail($id);
        $group = Group::findOrFail($group->id);

        // 🔗 URL secreta
        $url = route('reveal.by.key', ['key' => $participant->secret_key]);

        // 🎨 QR personalizado
        $qrCode = QrCode::create($url)
            ->setEncoding(new Encoding('UTF-8'))
            ->setSize(430)
            ->setMargin(40)
            ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->setForegroundColor(new Color(79, 70, 229))  // Roxo
            ->setBackgroundColor(new Color(255, 255, 255));

        $writer = new PngWriter();

        $result = $writer->write($qrCode);
        $qrBase64 = base64_encode($result->getString());

        // 📌 Caso queira um logo no meio do QR (opcional)
        // $logo = Logo::create(public_path('logo.png'))->setResizeToWidth(70);

        // Foto ou emoji
        $photo = $participant->photo
            ? asset('storage/' . $participant->photo)
            : AvatarHelper::emoji($participant->gender);

        // 📌 Legenda bonita com nome
        // $label = Label::create("🎁 {$participant->name}")
        //     ->setTextColor(new Color(60, 60, 60))
        //     ->setAlignment(new LabelAlignmentCenter());

        //$result = $writer->write($qrCode, null, $label);
        //$qrBase64 = base64_encode($result->getString());

        return view('amigo-secreto.participants.qr-premium', compact('participant', 'group', 'qrBase64', 'photo'));
    }
}
