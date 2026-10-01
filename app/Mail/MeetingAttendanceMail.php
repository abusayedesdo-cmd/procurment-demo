<?php

namespace App\Mail;

use App\Models\Meeting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
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
        return new Content(
            view: 'emails.meeting-attendance',
            with: [
                'meeting' => $this->meeting,
                'recipientName' => $this->recipientName,
                'designation' => $this->designation,
            ],
        );
    }
}