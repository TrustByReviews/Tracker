<?php

namespace App\Http\Controllers;

use App\Models\WeeklyReport;
use App\Models\User;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    /**
     * Show the reports dashboard
     */
    public function index(): Response
    {
        $reports = WeeklyReport::orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $totalReports = WeeklyReport::count();
        $totalPayment = WeeklyReport::sum('total_payment');

        return Inertia::render('Reports/Index', [
            'reports' => $reports,
            'totalReports' => $totalReports,
            'totalPayment' => $totalPayment,
        ]);
    }

    /**
     * Show a specific weekly report
     */
    public function show($id): Response
    {
        $report = WeeklyReport::findOrFail($id);
        
        return Inertia::render('Reports/Show', [
            'report' => $report,
            'developerReports' => $report->developer_reports,
        ]);
    }

    /**
     * Generate a new weekly report
     */
    public function generate(Request $request)
    {
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::now();
        
        // Run the command
        $output = shell_exec("php artisan report:weekly --date={$date->format('Y-m-d')} 2>&1");
        
        return back()->with('success', 'Weekly report generated successfully!');
    }

    /**
     * Show current week summary
     */
    public function currentWeek(): Response
    {
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::SUNDAY);
        $endOfWeek = Carbon::now()->endOfWeek(Carbon::SATURDAY);

        // Get developers with tasks this week
        $developers = User::whereHas('tasks', function ($query) {
            $query->whereNotNull('actual_hours')
                  ->where('actual_hours', '>', 0);
        })->get();

        $developerReports = [];
        $totalPayment = 0;

        foreach ($developers as $developer) {
            $tasks = Task::where('user_id', $developer->id)
                ->whereNotNull('actual_hours')
                ->where('actual_hours', '>', 0)
                ->get();

            $totalHours = $tasks->sum('actual_hours');
            $payment = $totalHours * $developer->hour_value;
            $totalPayment += $payment;

            $developerReports[] = [
                'developer_id' => $developer->id,
                'developer_name' => $developer->name,
                'developer_email' => $developer->email,
                'hour_value' => $developer->hour_value,
                'tasks_count' => $tasks->count(),
                'total_hours' => $totalHours,
                'total_payment' => $payment,
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

        return Inertia::render('Reports/CurrentWeek', [
            'startDate' => $startOfWeek->format('M d, Y'),
            'endDate' => $endOfWeek->format('M d, Y'),
            'developerReports' => $developerReports,
            'totalPayment' => $totalPayment,
        ]);
    }
} 