<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetOtpEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $userName;
    public $otp;
    public $expiresAt;

    /**
     * Create a new message instance.
     */
    public function __construct($userName, $otp, $expiresAt = null)
    {
        $this->userName = $userName;
        $this->otp = $otp;
        $this->expiresAt = $expiresAt ?? now()->addMinutes(10)->format('g:i A');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Password Reset OTP - Tracker',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset-otp',
            with: [
                'userName' => $this->userName,
                'otp' => $this->otp,
                'expiresAt' => $this->expiresAt,
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