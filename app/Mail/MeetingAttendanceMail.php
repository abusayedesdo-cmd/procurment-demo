<?php

namespace App\Mail;

use App\Models\Meeting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MeetingAttendanceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Meeting $meeting,
        public string $recipientName,
        public string $designation,
        public ?string $prNumber = null,
        public ?string $prPdf = null,
        public ?string $senderName = null,
        public ?string $senderDesignation = null,
    ) {
    }

    public function envelope(): Envelope
    {
        $ref = $this->meeting->procurementCase->ref ?? '';

        return new Envelope(
            subject: 'Attendance Confirmed — ' . ucfirst($this->meeting->meeting_type) . ' Meeting'
                . ($ref ? " ({$ref})" : ''),
        );
    }

    public function content(): Content
    {
        // Sender = the user who actually recorded the attendance / sent this mail.
        // Designation only — never the system role. (Falls back to whoever recorded the
        // meeting if the mail is sent without a logged-in user.)
        $author = $this->meeting->recordedBy;

        return new Content(
            view: 'emails.meeting-attendance',
            with: [
                'meeting' => $this->meeting,
                'recipientName' => $this->recipientName,
                'designation' => $this->designation,
                'prNumber' => $this->prNumber,
                'hasPrAttachment' => $this->prPdf !== null,
                'authorName' => $this->senderName ?: $author?->name,
                'authorDesignation' => $this->senderName ? $this->senderDesignation : $author?->designation,
            ],
        );
    }

    /** The meeting's Purchase Requisition, attached as a PDF so recipients can download it. */
    public function attachments(): array
    {
        if ($this->prPdf === null) {
            return [];
        }

        $name = 'PR-' . preg_replace('/[^A-Za-z0-9._-]+/', '-', (string) ($this->prNumber ?: 'document')) . '.pdf';

        return [
            Attachment::fromData(fn () => $this->prPdf, $name)->withMime('application/pdf'),
        ];
    }
}