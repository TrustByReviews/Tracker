<?php

namespace App\Console\Commands;

use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateWeeklyPaymentReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:generate-weekly {--week= : Specific week start date (Y-m-d format)} {--send-email : Send email to admins}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate weekly payment reports for all developers';

    /**
     * Execute the console command.
     */
    public function handle(PaymentService $paymentService)
    {
        $this->info('Starting weekly payment reports generation...');

        try {
            // Determinar la semana a procesar
            if ($weekOption = $this->option('week')) {
                $weekStart = Carbon::parse($weekOption)->startOfWeek();
                $this->info("Processing week: {$weekStart->format('Y-m-d')} to {$weekStart->copy()->endOfWeek()->format('Y-m-d')}");
            } else {
                $weekStart = Carbon::now()->subWeek()->startOfWeek(); // Semana anterior por defecto
                $this->info("Processing previous week: {$weekStart->format('Y-m-d')} to {$weekStart->copy()->endOfWeek()->format('Y-m-d')}");
            }

            // Generar reportes
            $reportsData = $paymentService->generateWeeklyReportsForAllDevelopers($weekStart);

            $this->info("Generated {$reportsData['reports']->count()} payment reports");
            $this->info("Total payment: $" . number_format($reportsData['total_payment'], 2));

            // Mostrar resumen de reportes
            $this->table(
                ['Developer', 'Hours', 'Payment', 'Status'],
                $reportsData['reports']->map(function ($report) {
                    return [
                        $report->user->name,
                        $report->total_hours . ' hrs',
                        '$' . number_format($report->total_payment, 2),
                        $report->status,
                    ];
                })->toArray()
            );

            // Enviar email si se solicita
            if ($this->option('send-email')) {
                $this->info('Sending email to admins...');
                $emailSent = $paymentService->sendWeeklyReportsEmail($reportsData);
                
                if ($emailSent) {
                    $this->info('Email sent successfully to admins');
                } else {
                    $this->error('Failed to send email to admins');
                }
            }

            Log::info('Weekly payment reports generated successfully', [
                'week_start' => $reportsData['week_start'],
                'week_end' => $reportsData['week_end'],
                'total_payment' => $reportsData['total_payment'],
                'reports_count' => $reportsData['reports']->count(),
            ]);

            $this->info('Weekly payment reports generation completed successfully!');
            return 0;

        } catch (\Exception $e) {
            $this->error('Error generating weekly payment reports: ' . $e->getMessage());
            Log::error('Error generating weekly payment reports: ' . $e->getMessage());
            return 1;
        }
    }
}
