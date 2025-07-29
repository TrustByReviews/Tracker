<?php

namespace App\Console\Commands;

use App\Services\EmailService;
use App\Models\User;
use Illuminate\Console\Command;

class TestResendCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resend:test {email} {--type=welcome} {--from=resend}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Resend email sending without custom domain';

    /**
     * Execute the console command.
     */
    public function handle(EmailService $emailService)
    {
        $email = $this->argument('email');
        $type = $this->option('type');
        $from = $this->option('from');

        $this->info("🧪 Testing Resend email configuration...");
        $this->info("📧 To: {$email}");
        $this->info("📝 Type: {$type}");
        $this->info("📤 From: {$from}");

        // Verificar configuración
        $this->checkConfiguration();

        // Crear o encontrar usuario de prueba
        $user = $this->createTestUser($email);

        try {
            $this->info("📨 Sending {$type} email...");
            
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
                    $this->error("❌ Unknown email type: {$type}");
                    $this->info("Available types: welcome, password-reset, project-assignment, generic");
                    return 1;
            }

            if ($success) {
                $this->info("✅ Email sent successfully!");
                $this->info("📬 Check your email inbox and spam folder.");
                $this->info("🔍 You can also check the Resend dashboard for delivery status.");
            } else {
                $this->error("❌ Failed to send email");
                $this->error("Check the logs for more details: storage/logs/laravel.log");
            }

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            $this->error("🔧 Make sure your RESEND_API_KEY is configured correctly");
            return 1;
        }

        return 0;
    }

    /**
     * Check if Resend is properly configured
     */
    private function checkConfiguration()
    {
        $this->info("🔍 Checking configuration...");

        // Verificar API key
        if (!config('resend.api_key')) {
            $this->error("❌ RESEND_API_KEY not found in .env file");
            $this->info("💡 Add this to your .env file:");
            $this->info("   RESEND_API_KEY=re_your_api_key_here");
            exit(1);
        }

        // Verificar mailer
        if (config('mail.default') !== 'resend') {
            $this->warn("⚠️  MAIL_MAILER is not set to 'resend'");
            $this->info("💡 Add this to your .env file:");
            $this->info("   MAIL_MAILER=resend");
        }

        // Verificar from address
        $fromAddress = config('mail.from.address');
        if (!$fromAddress || $fromAddress === 'hello@example.com') {
            $this->warn("⚠️  MAIL_FROM_ADDRESS not configured");
            $this->info("💡 Add this to your .env file:");
            $this->info("   MAIL_FROM_ADDRESS=noreply@resend.dev");
        }

        $this->info("✅ Configuration looks good!");
    }

    /**
     * Create a test user for email testing
     */
    private function createTestUser($email)
    {
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->warn("👤 User not found. Creating test user...");
            $user = User::create([
                'name' => 'Test User',
                'nickname' => 'test',
                'email' => $email,
                'password' => bcrypt('password'),
                'hour_value' => 50,
                'work_time' => 'full',
            ]);
            $this->info("✅ Test user created: {$user->name} ({$user->email})");
        } else {
            $this->info("✅ Using existing user: {$user->name} ({$user->email})");
        }

        return $user;
    }
} 