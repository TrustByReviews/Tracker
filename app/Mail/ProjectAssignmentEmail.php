<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProjectAssignmentEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $userName;
    public $projectName;
    public $projectUrl;
    public $assignedBy;

    /**
     * Create a new message instance.
     */
    public function __construct($userName, $projectName, $projectUrl = null, $assignedBy = null)
    {
        $this->userName = $userName;
        $this->projectName = $projectName;
        $this->projectUrl = $projectUrl ?? config('app.url') . '/projects';
        $this->assignedBy = $assignedBy ?? 'System';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You have been assigned to a new project - Tracker',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.project-assignment',
            with: [
                'userName' => $this->userName,
                'projectName' => $this->projectName,
                'projectUrl' => $this->projectUrl,
                'assignedBy' => $this->assignedBy,
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