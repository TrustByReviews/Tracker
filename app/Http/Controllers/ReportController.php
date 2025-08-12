<?php

namespace App\Http\Controllers;

use App\Models\WeeklyReport;
use App\Models\User;
use App\Models\Task;
use App\Models\Project;
use App\Models\Sprint;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;

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

    /**
     * Show project analytics dashboard
     */
    public function projectAnalytics(): Response
    {
        // Check if user is admin
        $user = auth()->user();
        if (!$user || !$user->roles->where('name', 'admin')->count()) {
            abort(403, 'Access denied. Only administrators can view analytics.');
        }
        
        $projects = Project::with(['sprints.tasks', 'users', 'creator'])->get();
        
        $analytics = [
            'total_projects' => $projects->count(),
            'active_projects' => $projects->where('status', 'active')->count(),
            'completed_projects' => $projects->where('status', 'completed')->count(),
            'paused_projects' => $projects->where('status', 'paused')->count(),
            'total_budget' => $projects->sum('estimated_budget'),
            'used_budget' => $projects->sum('used_budget'),
            'budget_efficiency' => $projects->sum('estimated_budget') > 0 
                ? round(($projects->sum('used_budget') / $projects->sum('estimated_budget')) * 100, 2)
                : 0,
            'average_progress' => $projects->avg('progress_percentage'),
            'total_tasks' => $projects->sum(function ($project) {
                return $project->sprints->sum(function ($sprint) {
                    return $sprint->tasks->count();
                });
            }),
            'completed_tasks' => $projects->sum(function ($project) {
                return $project->sprints->sum(function ($sprint) {
                    return $sprint->tasks->where('status', 'done')->count();
                });
            }),
        ];

        // Project performance by category
        $categoryPerformance = $projects->groupBy('category')->map(function ($categoryProjects) {
            return [
                'count' => $categoryProjects->count(),
                'average_progress' => $categoryProjects->avg('progress_percentage'),
                'total_budget' => $categoryProjects->sum('estimated_budget'),
                'used_budget' => $categoryProjects->sum('used_budget'),
            ];
        });

        // Project performance by priority
        $priorityPerformance = $projects->groupBy('priority')->map(function ($priorityProjects) {
            return [
                'count' => $priorityProjects->count(),
                'average_progress' => $priorityProjects->avg('progress_percentage'),
                'total_budget' => $priorityProjects->sum('estimated_budget'),
                'used_budget' => $priorityProjects->sum('used_budget'),
            ];
        });

        // Technology stack analysis
        $technologyUsage = [];
        $projects->each(function ($project) use (&$technologyUsage) {
            if ($project->technologies) {
                foreach ($project->technologies as $tech) {
                    if (!isset($technologyUsage[$tech])) {
                        $technologyUsage[$tech] = 0;
                    }
                    $technologyUsage[$tech]++;
                }
            }
        });
        arsort($technologyUsage);

        // Monthly project creation trend
        $monthlyTrend = $projects->groupBy(function ($project) {
            return $project->created_at->format('Y-m');
        })->map(function ($monthProjects) {
            return $monthProjects->count();
        })->sortKeys();

        return Inertia::render('Reports/ProjectAnalytics', [
            'analytics' => $analytics,
            'categoryPerformance' => $categoryPerformance,
            'priorityPerformance' => $priorityPerformance,
            'technologyUsage' => $technologyUsage,
            'monthlyTrend' => $monthlyTrend,
        ]);
    }

    /**
     * Show team performance analytics
     */
    public function teamAnalytics(): Response
    {
        // Check if user is admin
        $user = auth()->user();
        if (!$user || !$user->roles->where('name', 'admin')->count()) {
            abort(403, 'Access denied. Only administrators can view analytics.');
        }
        
        $users = User::with(['projects', 'tasks'])->get();
        
        $teamAnalytics = [
            'total_team_members' => $users->count(),
            'active_developers' => $users->whereHas('roles', function ($query) {
                $query->where('name', 'developer');
            })->count(),
            'total_projects_assigned' => $users->sum(function ($user) {
                return $user->projects->count();
            }),
            'total_tasks_assigned' => $users->sum(function ($user) {
                return $user->tasks->count();
            }),
            'completed_tasks' => $users->sum(function ($user) {
                return $user->tasks->where('status', 'done')->count();
            }),
        ];

        // Individual performance
        $individualPerformance = $users->map(function ($user) {
            $userTasks = $user->tasks;
            $completedTasks = $userTasks->where('status', 'done');
            $inProgressTasks = $userTasks->where('status', 'in progress');
            
            return [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->roles->first()?->name ?? 'Unknown',
                'projects_count' => $user->projects->count(),
                'total_tasks' => $userTasks->count(),
                'completed_tasks' => $completedTasks->count(),
                'in_progress_tasks' => $inProgressTasks->count(),
                'completion_rate' => $userTasks->count() > 0 
                    ? round(($completedTasks->count() / $userTasks->count()) * 100, 2)
                    : 0,
                'average_task_hours' => $userTasks->whereNotNull('actual_hours')->avg('actual_hours') ?? 0,
                'total_hours' => $userTasks->whereNotNull('actual_hours')->sum('actual_hours'),
            ];
        })->sortByDesc('completion_rate');

        // Role-based performance
        $rolePerformance = $users->groupBy(function ($user) {
            return $user->roles->first()?->name ?? 'Unknown';
        })->map(function ($roleUsers) {
            $totalTasks = $roleUsers->sum(function ($user) {
                return $user->tasks->count();
            });
            $completedTasks = $roleUsers->sum(function ($user) {
                return $user->tasks->where('status', 'done')->count();
            });
            
            return [
                'member_count' => $roleUsers->count(),
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'completion_rate' => $totalTasks > 0 
                    ? round(($completedTasks / $totalTasks) * 100, 2)
                    : 0,
                'average_hours' => $roleUsers->avg(function ($user) {
                    return $user->tasks->whereNotNull('actual_hours')->avg('actual_hours') ?? 0;
                }),
            ];
        });

        return Inertia::render('Reports/TeamAnalytics', [
            'teamAnalytics' => $teamAnalytics,
            'individualPerformance' => $individualPerformance,
            'rolePerformance' => $rolePerformance,
        ]);
    }

    /**
     * Show budget and financial analytics
     */
    public function budgetAnalytics(): Response
    {
        // Check if user is admin
        $user = auth()->user();
        if (!$user || !$user->roles->where('name', 'admin')->count()) {
            abort(403, 'Access denied. Only administrators can view analytics.');
        }
        
        $projects = Project::with(['sprints.tasks'])->get();
        
        $budgetAnalytics = [
            'total_estimated_budget' => $projects->sum('estimated_budget'),
            'total_used_budget' => $projects->sum('used_budget'),
            'budget_efficiency' => $projects->sum('estimated_budget') > 0 
                ? round(($projects->sum('used_budget') / $projects->sum('estimated_budget')) * 100, 2)
                : 0,
            'over_budget_projects' => $projects->where('used_budget', '>', 'estimated_budget')->count(),
            'under_budget_projects' => $projects->where('used_budget', '<', 'estimated_budget')->count(),
            'on_budget_projects' => $projects->where('used_budget', '=', 'estimated_budget')->count(),
        ];

        // Budget by project status
        $budgetByStatus = $projects->groupBy('status')->map(function ($statusProjects) {
            return [
                'count' => $statusProjects->count(),
                'estimated_budget' => $statusProjects->sum('estimated_budget'),
                'used_budget' => $statusProjects->sum('used_budget'),
                'efficiency' => $statusProjects->sum('estimated_budget') > 0 
                    ? round(($statusProjects->sum('used_budget') / $statusProjects->sum('estimated_budget')) * 100, 2)
                    : 0,
            ];
        });

        // Budget by category
        $budgetByCategory = $projects->groupBy('category')->map(function ($categoryProjects) {
            return [
                'count' => $categoryProjects->count(),
                'estimated_budget' => $categoryProjects->sum('estimated_budget'),
                'used_budget' => $categoryProjects->sum('used_budget'),
                'efficiency' => $categoryProjects->sum('estimated_budget') > 0 
                    ? round(($categoryProjects->sum('used_budget') / $categoryProjects->sum('estimated_budget')) * 100, 2)
                    : 0,
            ];
        });

        // Monthly budget trend
        $monthlyBudgetTrend = $projects->groupBy(function ($project) {
            return $project->created_at->format('Y-m');
        })->map(function ($monthProjects) {
            return [
                'estimated_budget' => $monthProjects->sum('estimated_budget'),
                'used_budget' => $monthProjects->sum('used_budget'),
                'project_count' => $monthProjects->count(),
            ];
        })->sortKeys();

        return Inertia::render('Reports/BudgetAnalytics', [
            'budgetAnalytics' => $budgetAnalytics,
            'budgetByStatus' => $budgetByStatus,
            'budgetByCategory' => $budgetByCategory,
            'monthlyBudgetTrend' => $monthlyBudgetTrend,
        ]);
    }
} 