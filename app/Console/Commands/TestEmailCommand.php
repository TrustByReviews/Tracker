<?php

namespace App\Console\Commands;

use App\Services\EmailService;
use App\Models\User;
use Illuminate\Console\Command;

class TestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {email} {--type=welcome}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email sending with Resend';

    /**
     * Execute the console command.
     */
    public function handle(EmailService $emailService)
    {
        $email = $this->argument('email');
        $type = $this->option('type');

        $this->info("Testing {$type} email to: {$email}");

        // Create a test user or find existing one
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->warn("User with email {$email} not found. Creating a test user...");
            $user = User::create([
                'name' => 'Test User',
                'nickname' => 'test',
                'email' => $email,
                'password' => bcrypt('password'),
                'hour_value' => 50,
                'work_time' => 'full',
            ]);
        }

        try {
            switch ($type) {
                case 'welcome':
                    $success = $emailService->sendWelcomeEmail($user, 'testpassword123');
                    break;
                    
                case 'password-reset':
                    $resetUrl = config('app.url') . '/reset-password?token=test-token';
                    $success = $emailService->sendPasswordResetEmail($user, $resetUrl);
                    break;
                    
                case 'project-assignment':
                    $success = $emailService->sendProjectAssignmentEmail($user, 'Test Project');
                    break;
                    
                case 'generic':
                    $success = $emailService->sendBulkEmails([$user], 'Test Subject', 'This is a test message from Tracker.');
                    $success = $success['success'] > 0;
                    break;
                    
                default:
                    $this->error("Unknown email type: {$type}");
                    $this->info("Available types: welcome, password-reset, project-assignment, generic");
                    return 1;
            }

            if ($success) {
                $this->info("✅ {$type} email sent successfully!");
                $this->info("Check your email inbox and spam folder.");
            } else {
                $this->error("❌ Failed to send {$type} email");
            }

        } catch (\Exception $e) {
            $this->error("❌ Error sending email: " . $e->getMessage());
            $this->error("Make sure your RESEND_API_KEY is configured correctly in .env");
            return 1;
        }

        return 0;
    }
} 