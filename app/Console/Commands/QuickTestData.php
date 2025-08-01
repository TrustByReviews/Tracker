<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\PaymentReport;
use Carbon\Carbon;

class QuickTestData extends Command
{
    protected $signature = 'test:quick-data';
    protected $description = 'Generate quick test data for payments';

    public function handle()
    {
        $this->info("🚀 Generating quick test data...");

        // Establecer valores por defecto para hour_value
        User::where('hour_value', 0)->orWhereNull('hour_value')->update(['hour_value' => 25]);

        // Generar algunos reportes de pago de prueba
        $users = User::all();
        
        foreach ($users as $user) {
            // Generar reporte para la semana anterior
            $lastWeek = Carbon::now()->subWeek();
            $weekStart = $lastWeek->copy()->startOfWeek();
            $weekEnd = $lastWeek->copy()->endOfWeek();
            
            $hours = rand(20, 40);
            $rate = $user->hour_value ?? 25;
            $payment = $hours * $rate;
            
            PaymentReport::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'week_start_date' => $weekStart->toDateString(),
                    'week_end_date' => $weekEnd->toDateString(),
                ],
                [
                    'total_hours' => $hours,
                    'hourly_rate' => $rate,
                    'total_payment' => $payment,
                    'status' => 'pending',
                    'task_details' => json_encode(['completed_tasks' => rand(3, 8)]),
                ]
            );
            
            $this->line("✅ {$user->name}: {$hours}h × \${$rate}/h = \${$payment}");
        }

        $this->info("🎉 Quick test data generated!");
        return 0;
    }
} 