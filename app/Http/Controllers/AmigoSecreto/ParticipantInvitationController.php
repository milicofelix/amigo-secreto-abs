<?php

namespace App\Http\Controllers\AmigoSecreto;

use App\Http\Controllers\Controller;
use App\Models\AmigoSecreto\Group;
use App\Models\AmigoSecreto\Participant;
use App\Services\ParticipantInvitation;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class ParticipantInvitationController extends Controller
{
    public function show(Group $group, Participant $participant)
    {
        $code = $participant->hasActiveInvite() ? $participant->invite_code : null;
        $url = $code ? route('pwa.participant').'#convite='.$code : null;
        $qr = $url ? (new PngWriter())->write(QrCode::create($url)->setSize(430)->setMargin(20))->getDataUri() : null;

        return response()->view('amigo-secreto.participants.qr-premium', compact('group', 'participant', 'code', 'url', 'qr'))
            ->header('Referrer-Policy', 'no-referrer');
    }

    public function store(Group $group, Participant $participant, ParticipantInvitation $invitations)
    {
        $invitations->issue($participant, replace: true);

        return redirect()->route('participants.qr', [$group, $participant])
            ->with('invite_status', 'Convite pronto. Você pode voltar a esta tela para copiar o link.');
    }

    public function destroy(Group $group, Participant $participant, ParticipantInvitation $invitations)
    {
        $invitations->revoke($participant);

        return redirect()->route('participants.qr', [$group, $participant])
            ->with('invite_status', 'Convite revogado. As sessões que usavam esse convite foram encerradas.');
    }
}
