<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\PaymentReport;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateTestPaymentData extends Command
{
    protected $signature = 'payments:generate-test-data {--weeks=4 : Number of weeks to generate}';
    protected $description = 'Generate test payment data for development';

    public function handle()
    {
        $weeks = $this->option('weeks');
        $this->info("🧪 Generating test payment data for {$weeks} weeks...");

        // Obtener todos los usuarios (no solo desarrolladores)
        $users = User::all();

        if ($users->isEmpty()) {
            $this->error('❌ No users found. Please run the role seeder first.');
            return 1;
        }

        $this->info("👥 Found {$users->count()} users");

        // Generar datos para las últimas semanas
        $totalReports = 0;
        $totalPayment = 0;

        for ($i = $weeks; $i >= 1; $i--) {
            $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();

            $this->info("📅 Generating data for week {$weekStart->format('Y-m-d')} to {$weekEnd->format('Y-m-d')}");

            foreach ($users as $user) {
                // Generar horas aleatorias (entre 20 y 45 horas por semana para desarrolladores, menos para otros roles)
                $isDeveloper = $user->roles->contains('name', 'developer');
                $hours = $isDeveloper ? rand(20, 45) : rand(5, 15);
                $hourlyRate = $user->hour_value ?? ($isDeveloper ? 25 : 35);
                $totalPaymentAmount = $hours * $hourlyRate;

                // Generar estado aleatorio (más probable que esté pagado para semanas anteriores)
                $statusOptions = ['pending', 'approved', 'paid'];
                $weights = $i > 2 ? [0.1, 0.2, 0.7] : [0.3, 0.3, 0.4]; // Más pagados para semanas anteriores
                $status = $this->weightedRandom($statusOptions, $weights);

                // Crear o actualizar reporte
                $report = PaymentReport::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'week_start_date' => $weekStart->toDateString(),
                        'week_end_date' => $weekEnd->toDateString(),
                    ],
                    [
                        'total_hours' => $hours,
                        'hourly_rate' => $hourlyRate,
                        'total_payment' => $totalPaymentAmount,
                        'status' => $status,
                        'task_details' => json_encode([
                            'completed_tasks' => rand(3, 8),
                            'in_progress_tasks' => rand(1, 3),
                            'total_tasks' => rand(4, 10)
                        ]),
                        'notes' => $status === 'paid' ? 'Payment processed successfully' : null,
                        'approved_at' => $status === 'approved' || $status === 'paid' ? now() : null,
                        'paid_at' => $status === 'paid' ? now() : null,
                        'approved_by' => $status === 'approved' || $status === 'paid' ? User::whereHas('roles', function ($query) { $query->where('name', 'admin'); })->first()?->id : null,
                    ]
                );

                $totalReports++;
                $totalPayment += $totalPaymentAmount;

                $role = $user->roles->first()?->name ?? 'no role';
                $this->line("   ✅ {$user->name} ({$role}): {$hours}h × \${$hourlyRate}/h = \${$totalPaymentAmount} ({$status})");
            }
        }

        // Generar algunas tareas de ejemplo para que los reportes tengan contexto
        $developers = $users->filter(function ($user) {
            return $user->roles->contains('name', 'developer');
        });
        $this->generateSampleTasks($developers);

        $this->info("\n🎉 Test data generation completed!");
        $this->info("📊 Summary:");
        $this->info("   - Total reports generated: {$totalReports}");
        $this->info("   - Total payment amount: \${$totalPayment}");
        $this->info("   - Weeks covered: {$weeks}");
        $this->info("   - Users: {$users->count()}");

        return 0;
    }

    private function weightedRandom($options, $weights)
    {
        $totalWeight = array_sum($weights);
        $random = mt_rand(1, $totalWeight);
        
        $currentWeight = 0;
        foreach ($options as $index => $option) {
            $currentWeight += $weights[$index];
            if ($random <= $currentWeight) {
                return $option;
            }
        }
        
        return $options[0]; // Fallback
    }

    private function generateSampleTasks($users)
    {
        $this->info("📋 Generating sample tasks...");

        // Obtener o crear un sprint de ejemplo
        $sprint = \App\Models\Sprint::first();
        if (!$sprint) {
            $sprint = \App\Models\Sprint::create([
                'name' => 'Sample Sprint',
                'description' => 'Sprint for testing payment reports',
                'start_date' => Carbon::now()->subDays(30),
                'end_date' => Carbon::now()->addDays(7),
                'project_id' => \App\Models\Project::first()?->id ?? null,
            ]);
        }

        $taskNames = [
            'Implement user authentication',
            'Create dashboard layout',
            'Add payment processing',
            'Fix responsive design issues',
            'Optimize database queries',
            'Add email notifications',
            'Create API endpoints',
            'Update documentation',
            'Fix bug in login form',
            'Add data validation',
            'Implement search functionality',
            'Create admin panel',
            'Add file upload feature',
            'Optimize page loading',
            'Fix mobile navigation'
        ];

        foreach ($users as $developer) {
            // Generar 5-10 tareas por desarrollador
            $numTasks = rand(5, 10);
            
            for ($i = 0; $i < $numTasks; $i++) {
                $taskName = $taskNames[array_rand($taskNames)];
                $startDate = Carbon::now()->subDays(rand(1, 30));
                $endDate = $startDate->copy()->addDays(rand(1, 7));
                
                $status = ['in progress', 'done'][array_rand(['in progress', 'done'])];
                $totalTime = $status === 'done' ? rand(2, 8) * 3600 : rand(0, 4) * 3600; // en segundos
                
                Task::create([
                    'name' => $taskName,
                    'description' => "Sample task for testing payment reports",
                    'status' => $status,
                    'priority' => ['low', 'medium', 'high'][array_rand(['low', 'medium', 'high'])],
                    'actual_start' => $startDate,
                    'actual_finish' => $status === 'done' ? $endDate : null,
                    'total_time_seconds' => $totalTime,
                    'user_id' => $developer->id,
                    'project_id' => \App\Models\Project::first()?->id ?? null,
                    'sprint_id' => $sprint->id,
                ]);
            }
        }

        $this->info("   ✅ Generated sample tasks for all developers");
    }
} 