<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\AmigoSecreto\Participant;

class SecretFriendNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $giver;
    public $receiver;
    public $group;

    public function __construct(Participant $giver, Participant $receiver, $group)
    {
        $this->giver = $giver;
        $this->receiver = $receiver;
        $this->group = $group;
    }

    public function build()
    {
        return $this->subject('🎁 Seu Amigo Secreto foi sorteado!')
                    ->markdown('emails.secret_friend');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Secret Friend Notification',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.secret_friend',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
