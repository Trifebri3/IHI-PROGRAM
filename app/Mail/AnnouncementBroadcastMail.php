<?php

namespace App\Mail;

use App\Models\ProgramAnnouncement;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AnnouncementBroadcastMail extends Mailable
{
    use Queueable, SerializesModels;

    public $announcement;
    public $userName;

    // KUNCI PERBAIKAN: Fungsi Constructor murni standar PHP Core
    public function __construct(ProgramAnnouncement $announcement, $userName)
    {
        $this->announcement = $announcement;
        $this->userName = $userName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔔 PENGUMUMAN PENTING: ' . $this->announcement->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.announcement_custom',
        );
    }
}
