<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WeeklyReportEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $developerName;
    public $reportData;
    public $startDate;
    public $endDate;

    public function __construct($developerName, $reportData, $startDate, $endDate)
    {
        $this->developerName = $developerName;
        $this->reportData = $reportData;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Weekly Report - {$this->startDate} to {$this->endDate}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.weekly-report',
            with: [
                'developerName' => $this->developerName,
                'reportData' => $this->reportData,
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
} 