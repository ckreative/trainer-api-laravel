<?php

namespace App\Mail;

use App\Models\TrainerInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TrainerInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public TrainerInvitation $invitation
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You're invited to join as a Trainer",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $dashboardUrl = config('app.dashboard_url', 'http://localhost:3000');
        $setupUrl = $dashboardUrl.'/setup/'.$this->invitation->token;

        return new Content(
            view: 'emails.trainer-invitation',
            with: [
                'firstName' => $this->invitation->first_name,
                'setupUrl' => $setupUrl,
                'expiresAt' => $this->invitation->expires_at->format('F j, Y'),
            ],
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
