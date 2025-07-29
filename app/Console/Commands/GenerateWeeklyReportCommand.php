<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\User;
use App\Services\EmailService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateWeeklyReportCommand extends Command
{
    protected $signature = 'report:weekly {--date= : Specific date for report (Y-m-d format)}';
    protected $description = 'Generate weekly report of developer hours and payments';

    public function __construct(private EmailService $emailService)
    {
        parent::__construct();
    }

    public function handle()
    {
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : Carbon::now();
        $startOfWeek = $date->copy()->startOfWeek(Carbon::SUNDAY);
        $endOfWeek = $date->copy()->endOfWeek(Carbon::SATURDAY);

        $this->info("📊 Generating Weekly Report");
        $this->info("📅 Period: {$startOfWeek->format('M d, Y')} - {$endOfWeek->format('M d, Y')}");
        $this->info("");

        // Get all developers with tasks that have actual hours
        $developers = User::whereHas('tasks', function ($query) {
            $query->whereNotNull('actual_hours')
                  ->where('actual_hours', '>', 0);
        })->get();

        if ($developers->isEmpty()) {
            $this->warn("❌ No developers found with tasks in this period");
            return 0;
        }

        $totalPayment = 0;
        $reportData = [];

        foreach ($developers as $developer) {
            $developerReport = $this->generateDeveloperReport($developer, $startOfWeek, $endOfWeek);
            $reportData[] = $developerReport;
            $totalPayment += $developerReport['total_payment'];

            // Display developer report
            $this->displayDeveloperReport($developerReport);
        }

        // Save report to database
        $this->saveReport($startOfWeek, $endOfWeek, $reportData, $totalPayment);

        // Send email reports to developers
        $this->sendEmailReports($reportData, $startOfWeek, $endOfWeek);

        $this->info("");
        $this->info("💰 Total Payment: $" . number_format($totalPayment, 2));
        $this->info("✅ Weekly report generated successfully!");

        return 0;
    }

    private function generateDeveloperReport(User $developer, Carbon $startOfWeek, Carbon $endOfWeek): array
    {
        // Get tasks for this developer with actual hours
        $tasks = Task::where('user_id', $developer->id)
            ->whereNotNull('actual_hours')
            ->where('actual_hours', '>', 0)
            ->get();

        $totalHours = $tasks->sum('actual_hours');
        $totalPayment = $totalHours * $developer->hour_value;

        return [
            'developer_id' => $developer->id,
            'developer_name' => $developer->name,
            'developer_email' => $developer->email,
            'hour_value' => $developer->hour_value,
            'tasks_count' => $tasks->count(),
            'total_hours' => $totalHours,
            'total_payment' => $totalPayment,
            'tasks' => $tasks->map(function ($task) {
                return [
                    'id' => $task->id,
                    'name' => $task->name,
                    'hours' => $task->actual_hours,
                    'status' => $task->status,
                    'project' => $task->sprint->project->name ?? 'Unknown',
                ];
            })->toArray(),
        ];
    }

    private function displayDeveloperReport(array $report): void
    {
        $this->info("👤 {$report['developer_name']}");
        $this->info("   📧 {$report['developer_email']}");
        $this->info("   💰 Hour Value: \${$report['hour_value']}");
        $this->info("   📋 Tasks: {$report['tasks_count']}");
        $this->info("   ⏱️  Total Hours: {$report['total_hours']}");
        $this->info("   💵 Payment: $" . number_format($report['total_payment'], 2));
        
        if (!empty($report['tasks'])) {
            $this->info("   📝 Tasks:");
            foreach ($report['tasks'] as $task) {
                $this->info("      • {$task['name']} ({$task['project']}) - {$task['hours']}h - {$task['status']}");
            }
        }
        $this->info("");
    }

    private function saveReport(Carbon $startOfWeek, Carbon $endOfWeek, array $reportData, float $totalPayment): void
    {
        // Create weekly report record
        $report = \App\Models\WeeklyReport::create([
            'start_date' => $startOfWeek->format('Y-m-d'),
            'end_date' => $endOfWeek->format('Y-m-d'),
            'total_payment' => $totalPayment,
            'developers_count' => count($reportData),
            'report_data' => json_encode($reportData),
        ]);

        Log::info('Weekly report generated', [
            'report_id' => $report->id,
            'period' => $startOfWeek->format('Y-m-d') . ' to ' . $endOfWeek->format('Y-m-d'),
            'total_payment' => $totalPayment,
        ]);
    }

    private function sendEmailReports(array $reportData, Carbon $startOfWeek, Carbon $endOfWeek): void
    {
        foreach ($reportData as $developerReport) {
            if ($developerReport['total_hours'] > 0) {
                $this->emailService->sendWeeklyReportEmail(
                    $developerReport['developer_name'],
                    $developerReport['developer_email'],
                    $developerReport,
                    $startOfWeek->format('M d, Y'),
                    $endOfWeek->format('M d, Y')
                );
            }
        }
    }
} 