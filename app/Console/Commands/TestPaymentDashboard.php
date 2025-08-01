<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\PaymentService;
use Carbon\Carbon;

class TestPaymentDashboard extends Command
{
    protected $signature = 'payments:test-dashboard {email}';
    protected $description = 'Test payment dashboard data for a specific user';

    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("❌ User with email '{$email}' not found.");
            return 1;
        }

        $this->info("🧪 Testing payment dashboard for: {$user->name} ({$email})");
        $this->newLine();

        // Simular el PaymentService
        $paymentService = new PaymentService();

        // Obtener el próximo pago (semana anterior)
        $nextPayment = $paymentService->getNextPaymentForUser($user);
        
        // Obtener estadísticas del usuario
        $userStats = [
            'total_earned' => $user->paymentReports()->sum('total_payment'),
            'total_hours' => $user->paymentReports()->sum('total_hours'),
            'reports_count' => $user->paymentReports()->count(),
            'hourly_rate' => $user->hour_value,
        ];

        // Obtener historial de pagos (últimos 5)
        $paymentHistory = $user->paymentReports()
            ->orderBy('week_start_date', 'desc')
            ->limit(5)
            ->get();

        $this->info("📊 Dashboard Data:");
        $this->newLine();

        $this->info("💰 Next Payment:");
        if ($nextPayment) {
            $this->line("  - Week: {$nextPayment->week_start_date} to {$nextPayment->week_end_date}");
            $this->line("  - Hours: {$nextPayment->total_hours}");
            $this->line("  - Rate: \${$nextPayment->hourly_rate}/hr");
            $this->line("  - Payment: \${$nextPayment->total_payment}");
            $this->line("  - Status: {$nextPayment->status}");
        } else {
            $this->line("  - No payment report available");
        }
        $this->newLine();

        $this->info("📈 User Stats:");
        $this->line("  - Total Earned: \${$userStats['total_earned']}");
        $this->line("  - Total Hours: {$userStats['total_hours']}");
        $this->line("  - Reports Count: {$userStats['reports_count']}");
        $this->line("  - Hourly Rate: \${$userStats['hourly_rate']}");
        $this->newLine();

        $this->info("📋 Payment History ({$paymentHistory->count()} reports):");
        if ($paymentHistory->count() > 0) {
            foreach ($paymentHistory as $payment) {
                $this->line("  - Week {$payment->week_start_date}: {$payment->total_hours}h × \${$payment->hourly_rate} = \${$payment->total_payment} ({$payment->status})");
            }
        } else {
            $this->line("  - No payment history available");
        }
        $this->newLine();

        $this->info("🔐 Permissions Check:");
        $this->line("  - payments.view: " . ($user->hasPermission('payments.view') ? '✅ YES' : '❌ NO'));
        $this->line("  - payment-reports.view: " . ($user->hasPermission('payment-reports.view') ? '✅ YES' : '❌ NO'));

        return 0;
    }
} 