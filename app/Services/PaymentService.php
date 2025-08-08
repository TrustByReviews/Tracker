<?php

namespace App\Services;

use App\Models\User;
use App\Models\Task;
use App\Models\PaymentReport;
use App\Models\Bug; // Added this import for Bug model
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaymentService
{
    /**
     * Generate payment report for a developer in a specific week
     */
    public function generateWeeklyReport(User $user, Carbon $weekStart): PaymentReport
    {
        $weekEnd = $weekStart->copy()->endOfWeek();
        return $this->generateReportForDateRange($user, $weekStart, $weekEnd);
    }

    /**
     * Generate payment report for a developer in a specific date range
     */
    public function generateReportForDateRange(User $user, Carbon $startDate, Carbon $endDate): PaymentReport
    {
        // Get completed tasks in the period
        $completedTasks = $user->tasks()
            ->where('status', 'done')
            ->whereBetween('actual_finish', [$startDate, $endDate])
            ->get();

        // Get in-progress tasks with recorded hours
        $inProgressTasks = $user->tasks()
            ->where('status', 'in progress')
            ->where('total_time_seconds', '>', 0)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('actual_start', [$startDate, $endDate])
                      ->orWhereBetween('updated_at', [$startDate, $endDate]);
            })
            ->get();

        // Get resolved bugs in the period
        $completedBugs = $user->bugs()
            ->whereIn('status', ['resolved', 'verified', 'closed'])
            ->whereBetween('resolved_at', [$startDate, $endDate])
            ->get();

        // Get in-progress bugs with recorded hours
        $inProgressBugs = $user->bugs()
            ->whereIn('status', ['assigned', 'in progress'])
            ->where('total_time_seconds', '>', 0)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('assigned_at', [$startDate, $endDate])
                      ->orWhereBetween('updated_at', [$startDate, $endDate]);
            })
            ->get();

        // Get QA work (testing of tasks and bugs)
        $qaTestingTasks = Task::where('qa_assigned_to', $user->id)
            ->whereIn('qa_status', ['testing_finished', 'approved', 'rejected'])
            ->whereBetween('qa_testing_finished_at', [$startDate, $endDate])
            ->get();

        $qaTestingBugs = Bug::where('qa_assigned_to', $user->id)
            ->whereIn('qa_status', ['testing_finished', 'approved', 'rejected'])
            ->whereBetween('qa_testing_finished_at', [$startDate, $endDate])
            ->get();

        // Calculate total development hours
        $completedTaskHours = $completedTasks->sum('actual_hours');
        $inProgressTaskHours = $inProgressTasks->sum('actual_hours');
        $completedBugHours = $completedBugs->sum('actual_hours');
        $inProgressBugHours = $inProgressBugs->sum('actual_hours');
        
        // Calculate total QA hours
        $qaTaskHours = $this->calculateQaTestingHours($qaTestingTasks);
        $qaBugHours = $this->calculateQaTestingHoursBugs($qaTestingBugs);
        
        $totalHours = $completedTaskHours + $inProgressTaskHours + $completedBugHours + $inProgressBugHours + $qaTaskHours + $qaBugHours;

        // Calculate total payment
        $totalPayment = $totalHours * $user->hour_value;

        // Prepare task details
        $taskDetails = [
            'completed' => $completedTasks->map(function ($task) use ($user) {
                return [
                    'id' => $task->id,
                    'name' => $task->name,
                    'type' => 'task',
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
                    'type' => 'task',
                    'project' => $task->sprint->project->name ?? 'N/A',
                    'hours' => $task->actual_hours ?? 0,
                    'payment' => ($task->actual_hours ?? 0) * $user->hour_value,
                    'last_updated' => $task->updated_at,
                ];
            }),
        ];

        // Prepare bug details
        $bugDetails = [
            'completed' => $completedBugs->map(function ($bug) use ($user) {
                return [
                    'id' => $bug->id,
                    'name' => $bug->title,
                    'type' => 'bug',
                    'project' => $bug->project->name ?? 'N/A',
                    'hours' => $bug->actual_hours ?? 0,
                    'payment' => ($bug->actual_hours ?? 0) * $user->hour_value,
                    'completed_at' => $bug->resolved_at,
                    'importance' => $bug->importance,
                    'bug_type' => $bug->bug_type,
                ];
            }),
            'in_progress' => $inProgressBugs->map(function ($bug) use ($user) {
                return [
                    'id' => $bug->id,
                    'name' => $bug->title,
                    'type' => 'bug',
                    'project' => $bug->project->name ?? 'N/A',
                    'hours' => $bug->actual_hours ?? 0,
                    'payment' => ($bug->actual_hours ?? 0) * $user->hour_value,
                    'last_updated' => $bug->updated_at,
                    'importance' => $bug->importance,
                    'bug_type' => $bug->bug_type,
                ];
            }),
        ];

        // Prepare QA details
        $qaDetails = [
            'tasks_tested' => $qaTestingTasks->map(function ($task) use ($user) {
                $testingHours = $this->calculateTaskTestingHours($task);
                return [
                    'id' => $task->id,
                    'name' => $task->name,
                    'type' => 'qa_task',
                    'project' => $task->sprint->project->name ?? 'N/A',
                    'hours' => $testingHours,
                    'payment' => $testingHours * $user->hour_value,
                    'testing_finished_at' => $task->qa_testing_finished_at,
                    'qa_status' => $task->qa_status,
                    'qa_notes' => $task->qa_notes ?? null,
                ];
            }),
            'bugs_tested' => $qaTestingBugs->map(function ($bug) use ($user) {
                $testingHours = $this->calculateBugTestingHours($bug);
                return [
                    'id' => $bug->id,
                    'name' => $bug->title,
                    'type' => 'qa_bug',
                    'project' => $bug->project->name ?? 'N/A',
                    'hours' => $testingHours,
                    'payment' => $testingHours * $user->hour_value,
                    'testing_finished_at' => $bug->qa_testing_finished_at,
                    'qa_status' => $bug->qa_status,
                    'qa_notes' => $bug->qa_notes ?? null,
                ];
            }),
        ];

        // Combine task, bug, and QA details
        $allDetails = [
            'tasks' => $taskDetails,
            'bugs' => $bugDetails,
            'qa' => $qaDetails,
        ];

        // Create or update report
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
                'completed_tasks_count' => $completedTasks->count() + $completedBugs->count() + $qaTestingTasks->count() + $qaTestingBugs->count(),
                'in_progress_tasks_count' => $inProgressTasks->count() + $inProgressBugs->count(),
                'task_details' => $allDetails,
                'status' => 'pending',
            ]
        );
    }

    /**
     * Generate weekly reports for all developers
     */
    public function generateWeeklyReportsForAllDevelopers(Carbon $weekStart = null): array
    {
        if (!$weekStart) {
            $weekStart = Carbon::now()->startOfWeek();
        }

        $developers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['developer', 'qa']);
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
     * Generate reports from a date to another specific date
     */
    public function generateReportsUntilDate(Carbon $startDate, Carbon $endDate): array
    {
        $developers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['developer', 'qa']);
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
     * Get the next payment for a developer (previous week)
     */
    public function getNextPaymentForUser(User $user): ?PaymentReport
    {
        $lastWeekStart = Carbon::now()->subWeek()->startOfWeek();
        
        return PaymentReport::where('user_id', $user->id)
            ->where('week_start_date', $lastWeekStart->toDateString())
            ->first();
    }

    /**
     * Get payment statistics for admin
     */
    public function getPaymentStatistics($startDate = null, $endDate = null): array
    {
        $query = PaymentReport::with('user');

        if ($startDate && $endDate) {
            $query->whereBetween('week_start_date', [$startDate, $endDate]);
        } else {
            // Default to last 30 days
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
     * Send payment reports by email to admins
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
     * Approve a payment report
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
     * Mark report as paid
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

    /**
     * Calculate QA testing hours for a collection of tasks
     */
    private function calculateQaTestingHours($tasks)
    {
        return $tasks->sum(function ($task) {
            return $this->calculateTaskTestingHours($task);
        });
    }

    /**
     * Calculate QA testing hours for a specific task
     */
    private function calculateTaskTestingHours($task)
    {
        if (!$task->qa_testing_started_at || !$task->qa_testing_finished_at) {
            return 0;
        }

        $startTime = Carbon::parse($task->qa_testing_started_at);
        $finishTime = Carbon::parse($task->qa_testing_finished_at);
        
        // If there are pauses, calculate the actual testing time
        if ($task->qa_testing_paused_at) {
            $pausedTime = Carbon::parse($task->qa_testing_paused_at);
            $resumeTime = $task->qa_testing_resumed_at ? Carbon::parse($task->qa_testing_resumed_at) : $finishTime;
            
            $activeTime = $startTime->diffInSeconds($pausedTime) + $resumeTime->diffInSeconds($finishTime);
        } else {
            $activeTime = $startTime->diffInSeconds($finishTime);
        }

        return round($activeTime / 3600, 2); // Convert seconds to hours
    }

    /**
     * Calculate QA testing hours for a collection of bugs
     */
    private function calculateQaTestingHoursBugs($bugs)
    {
        return $bugs->sum(function ($bug) {
            return $this->calculateBugTestingHours($bug);
        });
    }

    /**
     * Calculate QA testing hours for a specific bug
     */
    private function calculateBugTestingHours($bug)
    {
        if (!$bug->qa_testing_started_at || !$bug->qa_testing_finished_at) {
            return 0;
        }

        $startTime = Carbon::parse($bug->qa_testing_started_at);
        $finishTime = Carbon::parse($bug->qa_testing_finished_at);
        
        // If there are pauses, calculate the actual testing time
        if ($bug->qa_testing_paused_at) {
            $pausedTime = Carbon::parse($bug->qa_testing_paused_at);
            $resumeTime = $bug->qa_testing_resumed_at ? Carbon::parse($bug->qa_testing_resumed_at) : $finishTime;
            
            $activeTime = $startTime->diffInSeconds($pausedTime) + $resumeTime->diffInSeconds($finishTime);
        } else {
            $activeTime = $startTime->diffInSeconds($finishTime);
        }

        return round($activeTime / 3600, 2); // Convert seconds to hours
    }
} 