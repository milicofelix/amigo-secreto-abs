<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ParticipantAccessRecovery extends Mailable
{
    public function __construct(public array $links) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Recupere seu acesso ao Amigo Secreto');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.participant_access_recovery');
    }
}
