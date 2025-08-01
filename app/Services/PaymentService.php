<?php

namespace App\Services;

use App\Models\User;
use App\Models\Task;
use App\Models\PaymentReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaymentService
{
    /**
     * Generar reporte de pago para un desarrollador en una semana específica
     */
    public function generateWeeklyReport(User $user, Carbon $weekStart): PaymentReport
    {
        $weekEnd = $weekStart->copy()->endOfWeek();
        return $this->generateReportForDateRange($user, $weekStart, $weekEnd);
    }

    /**
     * Generar reporte de pago para un desarrollador en un rango de fechas específico
     */
    public function generateReportForDateRange(User $user, Carbon $startDate, Carbon $endDate): PaymentReport
    {
        // Obtener tareas completadas en el período
        $completedTasks = $user->tasks()
            ->where('status', 'done')
            ->whereBetween('actual_finish', [$startDate, $endDate])
            ->get();

        // Obtener tareas en progreso con horas registradas
        $inProgressTasks = $user->tasks()
            ->where('status', 'in progress')
            ->where('total_time_seconds', '>', 0)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('actual_start', [$startDate, $endDate])
                      ->orWhereBetween('updated_at', [$startDate, $endDate]);
            })
            ->get();

        // Calcular horas totales
        $completedHours = $completedTasks->sum('actual_hours');
        $inProgressHours = $inProgressTasks->sum('actual_hours');
        $totalHours = $completedHours + $inProgressHours;

        // Calcular pago total
        $totalPayment = $totalHours * $user->hour_value;

        // Preparar detalles de tareas
        $taskDetails = [
            'completed' => $completedTasks->map(function ($task) use ($user) {
                return [
                    'id' => $task->id,
                    'name' => $task->name,
                    'project' => $task->sprint->project->name ?? 'N/A',
                    'hours' => $task->actual_hours ?? 0,
                    'payment' => ($task->actual_hours ?? 0) * $user->hour_value,
                    'completed_at' => $task->actual_finish,
                ];
            }),
            'in_progress' => $inProgressTasks->map(function ($task) use ($user) {
                return [
                    'id' => $task->id,
                    'name' => $task->name,
                    'project' => $task->sprint->project->name ?? 'N/A',
                    'hours' => $task->actual_hours ?? 0,
                    'payment' => ($task->actual_hours ?? 0) * $user->hour_value,
                    'last_updated' => $task->updated_at,
                ];
            }),
        ];

        // Crear o actualizar reporte
        return PaymentReport::updateOrCreate(
            [
                'user_id' => $user->id,
                'week_start_date' => $startDate->toDateString(),
                'week_end_date' => $endDate->toDateString(),
            ],
            [
                'total_hours' => $totalHours,
                'hourly_rate' => $user->hour_value,
                'total_payment' => $totalPayment,
                'completed_tasks_count' => $completedTasks->count(),
                'in_progress_tasks_count' => $inProgressTasks->count(),
                'task_details' => $taskDetails,
                'status' => 'pending',
            ]
        );
    }

    /**
     * Generar reportes semanales para todos los desarrolladores
     */
    public function generateWeeklyReportsForAllDevelopers(Carbon $weekStart = null): array
    {
        if (!$weekStart) {
            $weekStart = Carbon::now()->startOfWeek();
        }

        $developers = User::whereHas('roles', function ($query) {
            $query->where('name', 'developer');
        })->get();

        $reports = [];
        $totalPayment = 0;

        foreach ($developers as $developer) {
            try {
                $report = $this->generateWeeklyReport($developer, $weekStart);
                $reports[] = $report;
                $totalPayment += $report->total_payment;
            } catch (\Exception $e) {
                Log::error("Error generating payment report for user {$developer->id}: " . $e->getMessage());
            }
        }

        return [
            'reports' => $reports,
            'total_payment' => $totalPayment,
            'week_start' => $weekStart->toDateString(),
            'week_end' => $weekStart->copy()->endOfWeek()->toDateString(),
        ];
    }

    /**
     * Generar reportes desde una fecha hasta otra fecha específica
     */
    public function generateReportsUntilDate(Carbon $startDate, Carbon $endDate): array
    {
        $developers = User::whereHas('roles', function ($query) {
            $query->where('name', 'developer');
        })->get();

        $reports = [];
        $totalPayment = 0;

        foreach ($developers as $developer) {
            try {
                $report = $this->generateReportForDateRange($developer, $startDate, $endDate);
                $reports[] = $report;
                $totalPayment += $report->total_payment;
            } catch (\Exception $e) {
                Log::error("Error generating payment report for user {$developer->id}: " . $e->getMessage());
            }
        }

        return [
            'reports' => $reports,
            'total_payment' => $totalPayment,
            'week_start' => $startDate->toDateString(),
            'week_end' => $endDate->toDateString(),
        ];
    }

    /**
     * Obtener el próximo pago para un desarrollador (semana anterior)
     */
    public function getNextPaymentForUser(User $user): ?PaymentReport
    {
        $lastWeekStart = Carbon::now()->subWeek()->startOfWeek();
        
        return PaymentReport::where('user_id', $user->id)
            ->where('week_start_date', $lastWeekStart->toDateString())
            ->first();
    }

    /**
     * Obtener estadísticas de pagos para admin
     */
    public function getPaymentStatistics($startDate = null, $endDate = null): array
    {
        $query = PaymentReport::with('user');

        if ($startDate && $endDate) {
            $query->whereBetween('week_start_date', [$startDate, $endDate]);
        } else {
            // Últimos 30 días por defecto
            $query->where('week_start_date', '>=', Carbon::now()->subDays(30));
        }

        $reports = $query->get();

        $statistics = [
            'total_reports' => $reports->count(),
            'total_payment' => $reports->sum('total_payment'),
            'total_hours' => $reports->sum('total_hours'),
            'pending_reports' => $reports->where('status', 'pending')->count(),
            'approved_reports' => $reports->where('status', 'approved')->count(),
            'paid_reports' => $reports->where('status', 'paid')->count(),
            'by_developer' => $reports->groupBy('user_id')->map(function ($userReports) {
                return [
                    'user' => $userReports->first()->user,
                    'total_payment' => $userReports->sum('total_payment'),
                    'total_hours' => $userReports->sum('total_hours'),
                    'reports_count' => $userReports->count(),
                ];
            }),
            'by_week' => $reports->groupBy('week_start_date')->map(function ($weekReports) {
                return [
                    'week_start' => $weekReports->first()->week_start_date,
                    'total_payment' => $weekReports->sum('total_payment'),
                    'total_hours' => $weekReports->sum('total_hours'),
                    'reports_count' => $weekReports->count(),
                ];
            }),
        ];

        return $statistics;
    }

    /**
     * Enviar reportes por email a los admins
     */
    public function sendWeeklyReportsEmail(array $reportsData): bool
    {
        try {
            $admins = User::whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            })->get();

            foreach ($admins as $admin) {
                Mail::send('emails.weekly-payment-reports', $reportsData, function ($message) use ($admin, $reportsData) {
                    $message->to($admin->email)
                            ->subject('Weekly Payment Reports - ' . $reportsData['week_start'] . ' to ' . $reportsData['week_end']);
                });
            }

            Log::info('Weekly payment reports sent to admins', [
                'week_start' => $reportsData['week_start'],
                'week_end' => $reportsData['week_end'],
                'total_payment' => $reportsData['total_payment'],
                'reports_count' => count($reportsData['reports']),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error sending weekly payment reports: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Aprobar un reporte de pago
     */
    public function approveReport(PaymentReport $report, User $approvedBy): bool
    {
        try {
            $report->approve($approvedBy->id);
            return true;
        } catch (\Exception $e) {
            Log::error('Error approving payment report: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Marcar reporte como pagado
     */
    public function markReportAsPaid(PaymentReport $report): bool
    {
        try {
            $report->markAsPaid();
            return true;
        } catch (\Exception $e) {
            Log::error('Error marking payment report as paid: ' . $e->getMessage());
            return false;
        }
    }
} 