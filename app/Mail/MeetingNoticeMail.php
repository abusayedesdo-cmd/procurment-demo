<?php

namespace App\Mail;

use App\Models\Meeting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MeetingNoticeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Meeting $meeting,
        public string $recipientName,
        public ?string $senderName = null,
        public ?string $senderDesignation = null,
    ) {
    }

    public function envelope(): Envelope
    {
        $ref = $this->meeting->procurementCase->ref ?? '';

        return new Envelope(
            subject: 'Meeting Notice — ' . ucfirst($this->meeting->meeting_type) . ' Meeting'
                . ($ref ? " ({$ref})" : ''),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.meeting-notice',
            with: [
                'meeting' => $this->meeting,
                'recipientName' => $this->recipientName,
                // The user who sent this notice — name + own designation (never the role).
                'authorName' => $this->senderName ?: $this->meeting->recordedBy?->name,
                'authorDesignation' => $this->senderName ? $this->senderDesignation : $this->meeting->recordedBy?->designation,
            ],
        );
    }
}