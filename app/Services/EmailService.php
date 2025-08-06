<?php

namespace App\Services;

use App\Mail\WelcomeEmail;
use App\Mail\PasswordResetEmail;
use App\Mail\PasswordResetOtpEmail;
use App\Mail\ProjectAssignmentEmail;
use App\Mail\UserPasswordMail;
use App\Mail\WeeklyReportEmail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailService
{
    /**
     * Send welcome email to new user
     */
    public function sendWelcomeEmail(User $user, string $password): bool
    {
        try {
            Mail::to($user->email)->send(new WelcomeEmail(
                $user->name,
                $password,
                $user->email
            ));
            
            Log::info('Welcome email sent successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send welcome email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail(User $user, string $resetUrl, string $expiresAt = null): bool
    {
        try {
            Mail::to($user->email)->send(new PasswordResetEmail(
                $user->name,
                $resetUrl,
                $expiresAt
            ));
            
            Log::info('Password reset email sent successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Send password reset OTP email
     */
    public function sendPasswordResetOtpEmail(User $user, string $otp, string $expiresAt = null): bool
    {
        try {
            Mail::to($user->email)->send(new PasswordResetOtpEmail(
                $user->name,
                $otp,
                $expiresAt
            ));
            
            Log::info('Password reset OTP email sent successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'otp' => $otp
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send password reset OTP email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Send project assignment notification
     */
    public function sendProjectAssignmentEmail(User $user, string $projectName, string $projectUrl = null, string $assignedBy = null): bool
    {
        try {
            Mail::to($user->email)->send(new ProjectAssignmentEmail(
                $user->name,
                $projectName,
                $projectUrl,
                $assignedBy
            ));
            
            Log::info('Project assignment email sent successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'project_name' => $projectName
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send project assignment email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'project_name' => $projectName,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Send legacy password email (for backward compatibility)
     */
    public function sendPasswordEmail(User $user, string $password): bool
    {
        try {
            Mail::to($user->email)->send(new UserPasswordMail(
                $user->name,
                $password,
                $user->email
            ));
            
            Log::info('Password email sent successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send password email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Send bulk emails to multiple users
     */
    public function sendBulkEmails(array $users, string $subject, string $message): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($users as $user) {
            try {
                // You can create a generic email template for bulk emails
                Mail::to($user->email)->send(new \App\Mail\GenericEmail($subject, $message));
                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Send weekly report email
     */
    public function sendWeeklyReportEmail(string $developerName, string $developerEmail, array $reportData, string $startDate, string $endDate): bool
    {
        try {
            Mail::to($developerEmail)->send(new WeeklyReportEmail(
                $developerName,
                $reportData,
                $startDate,
                $endDate
            ));
            
            Log::info('Weekly report email sent successfully', [
                'developer_email' => $developerEmail,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send weekly report email', [
                'developer_email' => $developerEmail,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Send generic HTML email
     */
    public function sendEmail(string $to, string $subject, string $htmlMessage): bool
    {
        try {
            Mail::to($to)->send(new \App\Mail\GenericEmail($subject, $htmlMessage));
            
            Log::info('Generic email sent successfully', [
                'to' => $to,
                'subject' => $subject
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send generic email', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
} 