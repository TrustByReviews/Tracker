<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Resend\Resend;

class TestResendDirectCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resend:direct {email} {--type=welcome}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Resend email sending directly using the Resend SDK';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $type = $this->option('type');

        $this->info("🧪 Testing Resend email directly...");
        $this->info("📧 To: {$email}");
        $this->info("📝 Type: {$type}");

        // Verificar configuración
        $this->checkConfiguration();

        // Crear o encontrar usuario de prueba
        $user = $this->createTestUser($email);

        try {
            $this->info("📨 Sending {$type} email...");
            
            $resend = new Resend(env('RESEND_API_KEY'));
            
            switch ($type) {
                case 'welcome':
                    $result = $this->sendWelcomeEmail($resend, $user);
                    break;
                    
                case 'password-reset':
                    $result = $this->sendPasswordResetEmail($resend, $user);
                    break;
                    
                case 'project-assignment':
                    $result = $this->sendProjectAssignmentEmail($resend, $user);
                    break;
                    
                case 'generic':
                    $result = $this->sendGenericEmail($resend, $user);
                    break;
                    
                default:
                    $this->error("❌ Unknown email type: {$type}");
                    $this->info("Available types: welcome, password-reset, project-assignment, generic");
                    return 1;
            }

            if ($result) {
                $this->info("✅ Email sent successfully!");
                $this->info("📬 Check your email inbox and spam folder.");
                $this->info("🔍 You can also check the Resend dashboard for delivery status.");
            } else {
                $this->error("❌ Failed to send email");
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
        if (!env('RESEND_API_KEY')) {
            $this->error("❌ RESEND_API_KEY not found in .env file");
            $this->info("💡 Add this to your .env file:");
            $this->info("   RESEND_API_KEY=re_your_api_key_here");
            exit(1);
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

    /**
     * Send welcome email
     */
    private function sendWelcomeEmail($resend, $user)
    {
        $html = view('emails.welcome', [
            'userName' => $user->name,
            'password' => 'testpassword123',
            'email' => $user->email,
            'loginUrl' => config('app.url') . '/login'
        ])->render();

        $result = $resend->emails->create([
            'from' => env('MAIL_FROM_ADDRESS', 'noreply@resend.dev'),
            'to' => $user->email,
            'subject' => 'Welcome to Tracker - Your Account Has Been Created',
            'html' => $html,
        ]);

        return $result;
    }

    /**
     * Send password reset email
     */
    private function sendPasswordResetEmail($resend, $user)
    {
        $resetUrl = config('app.url') . '/reset-password?token=test-token';
        $expiresAt = now()->addHours(24)->format('M d, Y \a\t g:i A');

        $html = view('emails.password-reset', [
            'userName' => $user->name,
            'resetUrl' => $resetUrl,
            'expiresAt' => $expiresAt
        ])->render();

        $result = $resend->emails->create([
            'from' => env('MAIL_FROM_ADDRESS', 'noreply@resend.dev'),
            'to' => $user->email,
            'subject' => 'Password Reset Request - Tracker',
            'html' => $html,
        ]);

        return $result;
    }

    /**
     * Send project assignment email
     */
    private function sendProjectAssignmentEmail($resend, $user)
    {
        $html = view('emails.project-assignment', [
            'userName' => $user->name,
            'projectName' => 'Test Project',
            'projectUrl' => config('app.url') . '/projects',
            'assignedBy' => 'System'
        ])->render();

        $result = $resend->emails->create([
            'from' => env('MAIL_FROM_ADDRESS', 'noreply@resend.dev'),
            'to' => $user->email,
            'subject' => 'You have been assigned to a new project - Tracker',
            'html' => $html,
        ]);

        return $result;
    }

    /**
     * Send generic email
     */
    private function sendGenericEmail($resend, $user)
    {
        $html = view('emails.generic', [
            'subject' => 'Test Subject',
            'message' => 'This is a test message from Tracker.',
            'userName' => $user->name
        ])->render();

        $result = $resend->emails->create([
            'from' => env('MAIL_FROM_ADDRESS', 'noreply@resend.dev'),
            'to' => $user->email,
            'subject' => 'Test Subject',
            'html' => $html,
        ]);

        return $result;
    }
} 