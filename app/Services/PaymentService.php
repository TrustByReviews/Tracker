<?php

namespace App\Services;

use App\Models\User;
use App\Models\Task;
use App\Models\PaymentReport;
use App\Models\Bug;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Payment Service Class
 * 
 * This service handles all payment-related operations including report generation,
 * payment calculations, and automated payment processing for developers and QA testers.
 * 
 * Features:
 * - Weekly and custom date range payment reports
 * - Automated payment calculations based on hourly rates
 * - Support for both development and QA work
 * - Bulk report generation for all developers
 * - Payment approval and status management
 * - Email notifications for payment reports
 * 
 * @package App\Services
 * @author System
 * @version 1.0
 */
class PaymentService
{
    /**
     * Generate a weekly payment report for a specific developer
     * 
     * Creates a payment report for a single developer covering a specific week.
     * The report includes all completed tasks, bugs, and QA work within that period.
     * 
     * @param User $user The developer to generate the report for
     * @param Carbon $weekStart Start date of the week (Monday)
     * @return PaymentReport The generated payment report
     */
    public function generateWeeklyReport(User $user, Carbon $weekStart): PaymentReport
    {
        $weekEnd = $weekStart->copy()->endOfWeek();
        return $this->generateReportForDateRange($user, $weekStart, $weekEnd);
    }

    /**
     * Generate a payment report for a developer within a specific date range
     * 
     * This is the main method that calculates all payment data including:
     * - Completed tasks and bugs
     * - In-progress work with recorded hours
     * - QA testing work
     * - Rework (changes requested by TL or QA rejections)
     * - Efficiency calculations
     * - Total payment amounts
     * 
     * @param User $user The developer to generate the report for
     * @param Carbon $startDate Start date of the reporting period
     * @param Carbon $endDate End date of the reporting period
     * @return PaymentReport The generated payment report
     */
    public function generateReportForDateRange(User $user, Carbon $startDate, Carbon $endDate, $projectId = null): PaymentReport
    {
        // Get completed tasks in the period
        $completedTasksQuery = $user->tasks()
            ->where('status', 'done')
            ->whereBetween('actual_finish', [$startDate, $endDate]);
            
        if ($projectId) {
            $completedTasksQuery->whereHas('sprint.project', function ($query) use ($projectId) {
                $query->where('id', $projectId);
            });
        }
        
        $completedTasks = $completedTasksQuery->get();

        // Get in-progress tasks with recorded hours
        $inProgressTasksQuery = $user->tasks()
            ->where('status', 'in progress')
            ->where('total_time_seconds', '>', 0)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('actual_start', [$startDate, $endDate])
                      ->orWhereBetween('updated_at', [$startDate, $endDate]);
            });
            
        if ($projectId) {
            $inProgressTasksQuery->whereHas('sprint.project', function ($query) use ($projectId) {
                $query->where('id', $projectId);
            });
        }
        
        $inProgressTasks = $inProgressTasksQuery->get();

        // Get resolved bugs in the period
        $completedBugsQuery = $user->bugs()
            ->whereIn('status', ['resolved', 'verified', 'closed'])
            ->whereBetween('resolved_at', [$startDate, $endDate]);
            
        if ($projectId) {
            $completedBugsQuery->whereHas('sprint.project', function ($query) use ($projectId) {
                $query->where('id', $projectId);
            });
        }
        
        $completedBugs = $completedBugsQuery->get();

        // Get in-progress bugs with recorded hours
        $inProgressBugsQuery = $user->bugs()
            ->whereIn('status', ['assigned', 'in progress'])
            ->where('total_time_seconds', '>', 0)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('assigned_at', [$startDate, $endDate])
                      ->orWhereBetween('updated_at', [$startDate, $endDate]);
            });
            
        if ($projectId) {
            $inProgressBugsQuery->whereHas('sprint.project', function ($query) use ($projectId) {
                $query->where('id', $projectId);
            });
        }
        
        $inProgressBugs = $inProgressBugsQuery->get();

        // Get QA work (testing of tasks and bugs)
        $qaTestingTasks = Task::where('qa_assigned_to', $user->id)
            ->whereIn('qa_status', ['testing_finished', 'approved', 'rejected'])
            ->whereBetween('qa_testing_finished_at', [$startDate, $endDate])
            ->get();

        $qaTestingBugs = Bug::where('qa_assigned_to', $user->id)
            ->whereIn('qa_status', ['testing_finished', 'approved', 'rejected'])
            ->whereBetween('qa_testing_finished_at', [$startDate, $endDate])
            ->get();

        // Get rework data (tasks and bugs with changes requested or rejected)
        $reworkTasks = $this->getReworkTasks($user, $startDate, $endDate, $projectId);
        $reworkBugs = $this->getReworkBugs($user, $startDate, $endDate, $projectId);

        // Calculate total development hours
        $completedTaskHours = $completedTasks->sum('actual_hours');
        $inProgressTaskHours = $inProgressTasks->sum('actual_hours');
        $completedBugHours = $completedBugs->sum('actual_hours');
        $inProgressBugHours = $inProgressBugs->sum('actual_hours');
        
        // Calculate total QA hours
        $qaTaskHours = $this->calculateQaTestingHours($qaTestingTasks);
        $qaBugHours = $this->calculateQaTestingHoursBugs($qaTestingBugs);
        
        // Calculate rework hours
        $reworkTaskHours = $this->calculateReworkHours($reworkTasks);
        $reworkBugHours = $this->calculateReworkHours($reworkBugs);
        
        $totalHours = $completedTaskHours + $inProgressTaskHours + $completedBugHours + $inProgressBugHours + 
                     $qaTaskHours + $qaBugHours + $reworkTaskHours + $reworkBugHours;

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

        // Prepare rework details
        $reworkDetails = [
            'tasks' => $reworkTasks->map(function ($task) use ($user) {
                $reworkHours = $this->calculateTaskReworkHours($task);
                return [
                    'id' => $task->id,
                    'name' => $task->name,
                    'type' => 'rework_task',
                    'project' => $task->sprint->project->name ?? 'N/A',
                    'hours' => $reworkHours,
                    'payment' => $reworkHours * $user->hour_value,
                    'rework_type' => $this->getReworkType($task),
                    'rework_reason' => $this->getReworkReason($task),
                    'rework_date' => $this->getReworkDate($task),
                    'original_completion' => $task->actual_finish,
                ];
            }),
            'bugs' => $reworkBugs->map(function ($bug) use ($user) {
                $reworkHours = $this->calculateBugReworkHours($bug);
                return [
                    'id' => $bug->id,
                    'name' => $bug->title,
                    'type' => 'rework_bug',
                    'project' => $bug->project->name ?? 'N/A',
                    'hours' => $reworkHours,
                    'payment' => $reworkHours * $user->hour_value,
                    'rework_type' => $this->getReworkType($bug),
                    'rework_reason' => $this->getReworkReason($bug),
                    'rework_date' => $this->getReworkDate($bug),
                    'original_completion' => $bug->resolved_at,
                ];
            }),
        ];

        // Combine task, bug, QA, and rework details
        $allDetails = [
            'tasks' => $taskDetails,
            'bugs' => $bugDetails,
            'qa' => $qaDetails,
            'rework' => $reworkDetails,
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
                'completed_tasks_count' => $completedTasks->count() + $completedBugs->count() + $qaTestingTasks->count() + $qaTestingBugs->count() + $reworkTasks->count() + $reworkBugs->count(),
                'in_progress_tasks_count' => $inProgressTasks->count() + $inProgressBugs->count(),
                'task_details' => $allDetails,
                'status' => 'pending',
            ]
        );
    }

    /**
     * Generate weekly payment reports for all developers and QA testers
     * 
     * This method processes all users with developer or QA roles and generates
     * weekly payment reports for each one. It's typically used for automated
     * weekly report generation.
     * 
     * @param Carbon|null $weekStart Start date of the week (defaults to current week)
     * @return array Array containing all reports, total payment, and date range
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
     * Generate payment reports for all developers within a custom date range
     * 
     * This method allows generating reports for any date range, not just weekly.
     * Useful for generating monthly, quarterly, or custom period reports.
     * 
     * @param Carbon $startDate Start date of the reporting period
     * @param Carbon $endDate End date of the reporting period
     * @return array Array containing all reports, total payment, and date range
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
     * Get the next pending payment report for a specific user
     * 
     * Retrieves the most recent pending payment report for a developer.
     * This is typically used to show users their current payment status.
     * 
     * @param User $user The user to get the payment report for
     * @return PaymentReport|null The pending payment report or null if none exists
     */
    public function getNextPaymentForUser(User $user): ?PaymentReport
    {
        return PaymentReport::where('user_id', $user->id)
            ->where('status', 'pending')
            ->orderBy('week_start_date', 'desc')
            ->first();
    }

    /**
     * Get payment statistics for administrative purposes
     * 
     * Provides aggregated payment statistics for admin dashboards.
     * Includes total payments, average payments, and payment trends.
     * 
     * @param Carbon|null $startDate Start date for statistics (defaults to last 30 days)
     * @param Carbon|null $endDate End date for statistics (defaults to today)
     * @return array Array containing various payment statistics
     */
    public function getPaymentStatistics($startDate = null, $endDate = null): array
    {
        $query = PaymentReport::query();
        
        if ($startDate && $endDate) {
            $query->whereBetween('week_start_date', [$startDate, $endDate]);
        } else {
            // Default to last 30 days
            $query->where('week_start_date', '>=', Carbon::now()->subDays(30));
        }

        $reports = $query->get();

        return [
            'total_reports' => $reports->count(),
            'total_payment' => $reports->sum('total_payment'),
            'average_payment' => $reports->avg('total_payment'),
            'total_hours' => $reports->sum('total_hours'),
            'average_hours' => $reports->avg('total_hours'),
            'pending_reports' => $reports->where('status', 'pending')->count(),
            'approved_reports' => $reports->where('status', 'approved')->count(),
            'paid_reports' => $reports->where('status', 'paid')->count(),
        ];
    }

    /**
     * Send weekly payment reports via email to administrators
     * 
     * This method sends email notifications containing payment report summaries
     * to administrators for review and approval.
     * 
     * @param array $reportsData Array containing report data and statistics
     * @return bool True if emails were sent successfully, false otherwise
     */
    public function sendWeeklyReportsEmail(array $reportsData): bool
    {
        try {
            Mail::to(config('mail.admin_email', 'admin@example.com'))
                ->send(new WeeklyReportEmail($reportsData));
            
            Log::info('Weekly payment reports email sent successfully');
            return true;
        } catch (\Exception $e) {
            Log::error('Error sending weekly payment reports email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Approve a payment report
     * 
     * Changes the status of a payment report from 'pending' to 'approved'.
     * This action is typically performed by administrators after reviewing the report.
     * 
     * @param PaymentReport $report The payment report to approve
     * @param User $approvedBy The user who approved the report
     * @return bool True if approval was successful, false otherwise
     */
    public function approveReport(PaymentReport $report, User $approvedBy): bool
    {
        try {
            $report->update([
                'status' => 'approved',
                'approved_by' => $approvedBy->id,
                'approved_at' => now(),
            ]);
            
            Log::info("Payment report {$report->id} approved by user {$approvedBy->id}");
            return true;
        } catch (\Exception $e) {
            Log::error("Error approving payment report {$report->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark a payment report as paid
     * 
     * Changes the status of a payment report from 'approved' to 'paid'.
     * This indicates that the payment has been processed and sent to the developer.
     * 
     * @param PaymentReport $report The payment report to mark as paid
     * @return bool True if status change was successful, false otherwise
     */
    public function markReportAsPaid(PaymentReport $report): bool
    {
        try {
            $report->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
            
            Log::info("Payment report {$report->id} marked as paid");
            return true;
        } catch (\Exception $e) {
            Log::error("Error marking payment report {$report->id} as paid: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Calculate total QA testing hours for a collection of tasks
     * 
     * This method processes a collection of tasks and calculates the total
     * time spent on QA testing activities.
     * 
     * @param \Illuminate\Support\Collection $tasks Collection of tasks with QA testing data
     * @return float Total QA testing hours
     */
    private function calculateQaTestingHours($tasks)
    {
        return $tasks->sum(function ($task) {
            return $this->calculateTaskTestingHours($task);
        });
    }

    /**
     * Calculate QA testing hours for a specific task
     * 
     * Calculates the actual time spent on QA testing for a single task,
     * taking into account any pauses in testing activities.
     * 
     * @param Task $task The task to calculate QA testing hours for
     * @return float QA testing hours for the task
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
            $activeTime = $pausedTime->diffInSeconds($startTime);
        } else {
            $activeTime = $finishTime->diffInSeconds($startTime);
        }

        return round($activeTime / 3600, 2); // Convert seconds to hours
    }

    /**
     * Calculate total QA testing hours for a collection of bugs
     * 
     * This method processes a collection of bugs and calculates the total
     * time spent on QA testing activities for bug resolution.
     * 
     * @param \Illuminate\Support\Collection $bugs Collection of bugs with QA testing data
     * @return float Total QA testing hours for bugs
     */
    private function calculateQaTestingHoursBugs($bugs)
    {
        return $bugs->sum(function ($bug) {
            return $this->calculateBugTestingHours($bug);
        });
    }

    /**
     * Calculate QA testing hours for a specific bug
     * 
     * Calculates the actual time spent on QA testing for a single bug,
     * taking into account any pauses in testing activities.
     * 
     * @param Bug $bug The bug to calculate QA testing hours for
     * @return float QA testing hours for the bug
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
            $activeTime = $pausedTime->diffInSeconds($startTime);
        } else {
            $activeTime = $finishTime->diffInSeconds($startTime);
        }

        return round($activeTime / 3600, 2); // Convert seconds to hours
    }

    /**
     * Get tasks that required rework (changes requested by TL or QA rejections)
     * 
     * @param User $user The developer
     * @param Carbon $startDate Start date of the reporting period
     * @param Carbon $endDate End date of the reporting period
     * @param int|null $projectId Optional project ID to filter by
     * @return \Illuminate\Support\Collection Collection of tasks with rework
     */
    private function getReworkTasks(User $user, Carbon $startDate, Carbon $endDate, $projectId = null)
    {
        $query = $user->tasks()
            ->where(function ($query) {
                $query->where('team_leader_requested_changes', true)
                      ->orWhereNotNull('qa_rejection_reason');
            })
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('team_leader_requested_changes_at', [$startDate, $endDate])
                      ->orWhereBetween('qa_reviewed_at', [$startDate, $endDate])
                      ->orWhereBetween('retwork_started_at', [$startDate, $endDate]);
            });
            
        if ($projectId) {
            $query->whereHas('sprint.project', function ($query) use ($projectId) {
                $query->where('id', $projectId);
            });
        }
        
        return $query->get();
    }

    /**
     * Get bugs that required rework (changes requested by TL or QA rejections)
     * 
     * @param User $user The developer
     * @param Carbon $startDate Start date of the reporting period
     * @param Carbon $endDate End date of the reporting period
     * @param int|null $projectId Optional project ID to filter by
     * @return \Illuminate\Support\Collection Collection of bugs with rework
     */
    private function getReworkBugs(User $user, Carbon $startDate, Carbon $endDate, $projectId = null)
    {
        $query = $user->bugs()
            ->where(function ($query) {
                $query->where('team_leader_requested_changes', true)
                      ->orWhereNotNull('qa_rejection_reason');
            })
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('team_leader_requested_changes_at', [$startDate, $endDate])
                      ->orWhereBetween('qa_reviewed_at', [$startDate, $endDate]);
            });
            
        if ($projectId) {
            $query->whereHas('sprint.project', function ($query) use ($projectId) {
                $query->where('id', $projectId);
            });
        }
        
        return $query->get();
    }

    /**
     * Calculate total rework hours for a collection of items (tasks or bugs)
     * 
     * @param \Illuminate\Support\Collection $items Collection of tasks or bugs
     * @return float Total rework hours
     */
    private function calculateReworkHours($items)
    {
        return $items->sum(function ($item) {
            if ($item instanceof Task) {
                return $this->calculateTaskReworkHours($item);
            } else {
                return $this->calculateBugReworkHours($item);
            }
        });
    }

    /**
     * Calculate rework hours for a specific task
     * 
     * @param Task $task The task to calculate rework hours for
     * @return float Rework hours for the task
     */
    private function calculateTaskReworkHours($task)
    {
        // If there's specific rework time tracking, use it
        if ($task->retwork_time_seconds && $task->retwork_time_seconds > 0) {
            return round($task->retwork_time_seconds / 3600, 2);
        }

        // Otherwise, calculate based on total time vs original time
        if ($task->total_time_seconds && $task->original_time_seconds) {
            $additionalTime = $task->total_time_seconds - $task->original_time_seconds;
            if ($additionalTime > 0) {
                return round($additionalTime / 3600, 2);
            }
        }

        // Fallback: estimate rework time based on complexity
        if ($task->team_leader_requested_changes || $task->qa_rejection_reason) {
            // Estimate 25% of original time as rework
            $originalHours = $task->original_time_seconds ? round($task->original_time_seconds / 3600, 2) : 0;
            return round($originalHours * 0.25, 2);
        }

        return 0;
    }

    /**
     * Calculate rework hours for a specific bug
     * 
     * @param Bug $bug The bug to calculate rework hours for
     * @return float Rework hours for the bug
     */
    private function calculateBugReworkHours($bug)
    {
        // For bugs, estimate rework time based on complexity and type
        $baseHours = 0;
        
        switch ($bug->importance) {
            case 'critical':
                $baseHours = 2.0;
                break;
            case 'high':
                $baseHours = 1.5;
                break;
            case 'medium':
                $baseHours = 1.0;
                break;
            case 'low':
                $baseHours = 0.5;
                break;
            default:
                $baseHours = 1.0;
        }

        // If there's specific rework time tracking, use it
        if ($bug->total_time_seconds && $bug->total_time_seconds > 0) {
            return round($bug->total_time_seconds / 3600, 2);
        }

        // Otherwise, use estimated base hours
        return $baseHours;
    }

    /**
     * Get the type of rework for an item
     * 
     * @param Task|Bug $item The task or bug
     * @return string Type of rework
     */
    private function getReworkType($item)
    {
        if ($item->team_leader_requested_changes) {
            return 'Team Leader Changes Requested';
        } elseif ($item->qa_rejection_reason) {
            return 'QA Rejection';
        } else {
            return 'General Rework';
        }
    }

    /**
     * Get the reason for rework
     * 
     * @param Task|Bug $item The task or bug
     * @return string Reason for rework
     */
    private function getReworkReason($item)
    {
        if ($item->team_leader_change_notes) {
            return $item->team_leader_change_notes;
        } elseif ($item->qa_rejection_reason) {
            return $item->qa_rejection_reason;
        } else {
            return 'No specific reason provided';
        }
    }

    /**
     * Get the date when rework was requested
     * 
     * @param Task|Bug $item The task or bug
     * @return string|null Date of rework request
     */
    private function getReworkDate($item)
    {
        if ($item->team_leader_requested_changes_at) {
            return $item->team_leader_requested_changes_at;
        } elseif ($item->qa_reviewed_at) {
            return $item->qa_reviewed_at;
        } elseif ($item->retwork_started_at) {
            return $item->retwork_started_at;
        } else {
            return null;
        }
    }
} 