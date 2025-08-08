<?php

namespace App\Http\Controllers;

use App\Models\PaymentReport;
use App\Models\User;
use App\Services\PaymentService;
use App\Services\ExcelExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Dashboard de pagos para usuarios normales (próximo pago)
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        // Obtener el próximo pago (semana anterior)
        $nextPayment = $this->paymentService->getNextPaymentForUser($user);
        
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

        return Inertia::render('Payments/Dashboard', [
            'nextPayment' => $nextPayment,
            'userStats' => $userStats,
            'paymentHistory' => $paymentHistory,
        ]);
    }

    /**
     * Dashboard unificado de pagos (para usuarios normales y admins)
     */
    public function adminDashboard(Request $request)
    {
        $this->authorize('viewPaymentReports');

        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        // Obtener estadísticas generales
        $statistics = $this->paymentService->getPaymentStatistics($startDate, $endDate);

        // Obtener reportes recientes
        $recentReports = PaymentReport::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Obtener reportes pendientes
        $pendingReports = PaymentReport::with('user')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        // Obtener datos para reportes de pago (incluyendo QAs)
        $developers = User::with(['tasks', 'projects'])
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['developer', 'qa']);
            })
            ->get()
            ->map(function ($developer) {
                $completedTasks = $developer->tasks->where('status', 'done');
                $totalEarnings = $completedTasks->sum(function ($task) use ($developer) {
                    return ($task->actual_hours ?? 0) * $developer->hour_value;
                });

                // Calcular ganancias de QA
                $qaTaskEarnings = 0;
                $qaBugEarnings = 0;
                
                if ($developer->roles->contains('name', 'qa')) {
                    // Tareas testeadas por QA
                    $qaTestingTasks = \App\Models\Task::where('qa_assigned_to', $developer->id)
                        ->whereIn('qa_status', ['testing_finished', 'approved', 'rejected'])
                        ->whereNotNull('qa_testing_finished_at')
                        ->get();
                    
                    $qaTaskEarnings = $qaTestingTasks->sum(function ($task) use ($developer) {
                        $testingHours = $this->calculateTaskTestingHours($task);
                        return $testingHours * $developer->hour_value;
                    });

                    // Bugs testeados por QA
                    $qaTestingBugs = \App\Models\Bug::where('qa_assigned_to', $developer->id)
                        ->whereIn('qa_status', ['testing_finished', 'approved', 'rejected'])
                        ->whereNotNull('qa_testing_finished_at')
                        ->get();
                    
                    $qaBugEarnings = $qaTestingBugs->sum(function ($bug) use ($developer) {
                        $testingHours = $this->calculateBugTestingHours($bug);
                        return $testingHours * $developer->hour_value;
                    });
                }

                $totalEarnings += $qaTaskEarnings + $qaBugEarnings;

                return [
                    'id' => $developer->id,
                    'name' => $developer->name,
                    'email' => $developer->email,
                    'role' => $developer->roles->first()->name ?? 'unknown',
                    'hour_value' => $developer->hour_value,
                    'total_tasks' => $developer->tasks->count(),
                    'completed_tasks' => $completedTasks->count(),
                    'total_hours' => $completedTasks->sum('actual_hours'),
                    'total_earnings' => $totalEarnings,
                    'qa_task_earnings' => $qaTaskEarnings,
                    'qa_bug_earnings' => $qaBugEarnings,
                    'assigned_projects' => $developer->projects->count(),
                ];
            });

        return Inertia::render('Payments/Index', [
            'statistics' => $statistics,
            'recentReports' => $recentReports,
            'pendingReports' => $pendingReports,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            // Datos adicionales para reportes de pago
            'developers' => $developers,
            'totalEarnings' => $developers->sum('total_earnings'),
            'totalHours' => $developers->sum('total_hours'),
        ]);
    }

    /**
     * Lista de reportes de pago
     */
    public function index(Request $request)
    {
        $this->authorize('viewPaymentReports');

        $query = PaymentReport::with('user', 'approvedBy');

        // Filtros
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('week_start_date', [$request->start_date, $request->end_date]);
        }

        $reports = $query->orderBy('created_at', 'desc')->paginate(20);

        // Obtener lista de desarrolladores para filtros
        $developers = User::whereHas('roles', function ($query) {
            $query->where('name', 'developer');
        })->get(['id', 'name']);

        return Inertia::render('Payments/Index', [
            'reports' => $reports,
            'developers' => $developers,
            'filters' => $request->only(['user_id', 'status', 'start_date', 'end_date']),
        ]);
    }

    /**
     * Mostrar un reporte específico
     */
    public function show(PaymentReport $paymentReport)
    {
        $this->authorize('viewPaymentReports');

        $paymentReport->load(['user', 'approvedBy']);

        return Inertia::render('Payments/Show', [
            'report' => $paymentReport,
        ]);
    }

    /**
     * Generar reportes manualmente
     */
    public function generate(Request $request)
    {
        $this->authorize('generatePaymentReports');

        $request->validate([
            'week_start' => 'nullable|date',
            'generate_until_today' => 'boolean',
            'send_email' => 'boolean',
        ]);

        if ($request->boolean('generate_until_today')) {
            // Generar desde el lunes de esta semana hasta hoy
            $weekStart = Carbon::now()->startOfWeek();
            $today = Carbon::now();
            
            $reportsData = $this->paymentService->generateReportsUntilDate($weekStart, $today);
            
            $message = "Generated {$reportsData['reports']->count()} payment reports from {$reportsData['week_start']} to today";
        } else {
            // Generar para una semana específica
            $weekStart = Carbon::parse($request->week_start);
            $reportsData = $this->paymentService->generateWeeklyReportsForAllDevelopers($weekStart);
            
            $message = "Generated {$reportsData['reports']->count()} payment reports for week {$reportsData['week_start']} to {$reportsData['week_end']}";
        }

        if ($request->boolean('send_email')) {
            $this->paymentService->sendWeeklyReportsEmail($reportsData);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Generar reporte de pago detallado (desde PaymentReportController)
     */
    public function generateDetailedReport(Request $request)
    {
        $this->authorize('generatePaymentReports');

        $request->validate([
            'developer_ids' => 'required|array',
            'developer_ids.*' => 'exists:users,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'format' => 'required|in:pdf,email,excel,view',
        ]);

        // Usar PaymentService para obtener datos completos incluyendo QA
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now()->endOfMonth();

        $developers = User::whereIn('id', $request->developer_ids)->get()->map(function ($developer) use ($startDate, $endDate) {
            // Usar PaymentService para obtener datos completos
            $report = $this->paymentService->generateReportForDateRange($developer, $startDate, $endDate);
            
            // Obtener todas las tareas y bugs (completados, en progreso y QA)
            $allTasks = collect();
            
            // Tareas completadas
            if (isset($report->task_details['tasks']['completed'])) {
                $allTasks = $allTasks->merge(collect($report->task_details['tasks']['completed'])->map(function ($task) {
                    return [
                        'name' => $task['name'],
                        'project' => $task['project'],
                        'hours' => $task['hours'],
                        'earnings' => $task['payment'],
                        'completed_at' => $task['completed_at'],
                        'type' => 'Task (Completed)',
                    ];
                }));
            }
            
            // Tareas en progreso
            if (isset($report->task_details['tasks']['in_progress'])) {
                $allTasks = $allTasks->merge(collect($report->task_details['tasks']['in_progress'])->map(function ($task) {
                    return [
                        'name' => $task['name'],
                        'project' => $task['project'],
                        'hours' => $task['hours'],
                        'earnings' => $task['payment'],
                        'completed_at' => null,
                        'type' => 'Task (In Progress)',
                    ];
                }));
            }
            
            // Bugs completados
            if (isset($report->task_details['bugs']['completed'])) {
                $allTasks = $allTasks->merge(collect($report->task_details['bugs']['completed'])->map(function ($bug) {
                    return [
                        'name' => $bug['name'],
                        'project' => $bug['project'],
                        'hours' => $bug['hours'],
                        'earnings' => $bug['payment'],
                        'completed_at' => $bug['completed_at'],
                        'type' => 'Bug (Resolved)',
                    ];
                }));
            }
            
            // Bugs en progreso
            if (isset($report->task_details['bugs']['in_progress'])) {
                $allTasks = $allTasks->merge(collect($report->task_details['bugs']['in_progress'])->map(function ($bug) {
                    return [
                        'name' => $bug['name'],
                        'project' => $bug['project'],
                        'hours' => $bug['hours'],
                        'earnings' => $bug['payment'],
                        'completed_at' => null,
                        'type' => 'Bug (In Progress)',
                    ];
                }));
            }
            
            // QA Testing Tasks
            if (isset($report->task_details['qa']['tasks_tested'])) {
                $allTasks = $allTasks->merge(collect($report->task_details['qa']['tasks_tested'])->map(function ($task) {
                    return [
                        'name' => $task['name'] . ' (QA Testing)',
                        'project' => $task['project'],
                        'hours' => $task['hours'],
                        'earnings' => $task['payment'],
                        'completed_at' => $task['testing_finished_at'],
                        'type' => 'QA Task Testing',
                    ];
                }));
            }
            
            // QA Testing Bugs
            if (isset($report->task_details['qa']['bugs_tested'])) {
                $allTasks = $allTasks->merge(collect($report->task_details['qa']['bugs_tested'])->map(function ($bug) {
                    return [
                        'name' => $bug['name'] . ' (QA Testing)',
                        'project' => $bug['project'],
                        'hours' => $bug['hours'],
                        'earnings' => $bug['payment'],
                        'completed_at' => $bug['testing_finished_at'],
                        'type' => 'QA Bug Testing',
                    ];
                }));
            }
            
            return [
                'id' => $developer->id,
                'name' => $developer->name,
                'email' => $developer->email,
                'hour_value' => $developer->hour_value,
                'completed_tasks' => $report->completed_tasks_count,
                'total_hours' => $report->total_hours,
                'total_earnings' => $report->total_payment,
                'tasks' => $allTasks,
            ];
        });

        $reportData = [
            'developers' => $developers,
            'totalEarnings' => $developers->sum('total_earnings'),
            'totalHours' => $developers->sum('total_hours'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
        ];

        switch ($request->format) {
            case 'excel':
                return $this->generateExcel($reportData);
            case 'pdf':
                return $this->generatePDF($reportData);
            case 'email':
                return $this->sendEmail($reportData, $request);
            case 'view':
                return response()->json([
                    'success' => true,
                    'data' => $reportData
                ]);
        }
    }

    /**
     * Generar reporte de pago por proyecto
     */
    public function generateProjectReport(Request $request)
    {
        $this->authorize('generatePaymentReports');

        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'format' => 'required|in:pdf,email,excel',
        ]);

        $project = \App\Models\Project::with(['users.roles'])->findOrFail($request->project_id);
        
        // Obtener usuarios asignados al proyecto (developers y QAs)
        $projectUsers = $project->users->filter(function ($user) {
            return $user->roles->contains('name', 'developer') || $user->roles->contains('name', 'qa');
        });

        $developers = $projectUsers->map(function ($developer) use ($request) {
            // Tareas completadas en el proyecto
            $completedTasks = $developer->tasks()
                ->where('status', 'done')
                ->whereHas('sprint', function ($query) use ($request) {
                    $query->where('project_id', $request->project_id);
                });
            
            if ($request->start_date) {
                $completedTasks->where('actual_finish', '>=', $request->start_date);
            }
            if ($request->end_date) {
                $completedTasks->where('actual_finish', '<=', $request->end_date);
            }
            
            $completedTasks = $completedTasks->get();
            
            $totalEarnings = $completedTasks->sum(function ($task) use ($developer) {
                return ($task->actual_hours ?? 0) * $developer->hour_value;
            });

            // Calcular ganancias de QA si es QA
            $qaTaskEarnings = 0;
            $qaBugEarnings = 0;
            $qaTasks = collect();
            $qaBugs = collect();
            
            if ($developer->roles->contains('name', 'qa')) {
                // Tareas testeadas por QA en el proyecto
                $qaTestingTasks = \App\Models\Task::where('qa_assigned_to', $developer->id)
                    ->whereIn('qa_status', ['testing_finished', 'approved', 'rejected'])
                    ->whereNotNull('qa_testing_finished_at')
                    ->whereHas('sprint', function ($query) use ($request) {
                        $query->where('project_id', $request->project_id);
                    });
                
                if ($request->start_date) {
                    $qaTestingTasks->where('qa_testing_finished_at', '>=', $request->start_date);
                }
                if ($request->end_date) {
                    $qaTestingTasks->where('qa_testing_finished_at', '<=', $request->end_date);
                }
                
                $qaTestingTasks = $qaTestingTasks->get();
                
                $qaTaskEarnings = $qaTestingTasks->sum(function ($task) use ($developer) {
                    $testingHours = $this->calculateTaskTestingHours($task);
                    return $testingHours * $developer->hour_value;
                });

                $qaTasks = $qaTestingTasks->map(function ($task) use ($developer) {
                    $testingHours = $this->calculateTaskTestingHours($task);
                    return [
                        'name' => $task->name,
                        'project' => $task->sprint->project->name ?? 'N/A',
                        'hours' => $testingHours,
                        'earnings' => $testingHours * $developer->hour_value,
                        'completed_at' => $task->qa_testing_finished_at,
                        'type' => 'QA Testing',
                    ];
                });

                // Bugs testeados por QA en el proyecto
                $qaTestingBugs = \App\Models\Bug::where('qa_assigned_to', $developer->id)
                    ->whereIn('qa_status', ['testing_finished', 'approved', 'rejected'])
                    ->whereNotNull('qa_testing_finished_at')
                    ->whereHas('sprint', function ($query) use ($request) {
                        $query->where('project_id', $request->project_id);
                    });
                
                if ($request->start_date) {
                    $qaTestingBugs->where('qa_testing_finished_at', '>=', $request->start_date);
                }
                if ($request->end_date) {
                    $qaTestingBugs->where('qa_testing_finished_at', '<=', $request->end_date);
                }
                
                $qaTestingBugs = $qaTestingBugs->get();
                
                $qaBugEarnings = $qaTestingBugs->sum(function ($bug) use ($developer) {
                    $testingHours = $this->calculateBugTestingHours($bug);
                    return $testingHours * $developer->hour_value;
                });

                $qaBugs = $qaTestingBugs->map(function ($bug) use ($developer) {
                    $testingHours = $this->calculateBugTestingHours($bug);
                    return [
                        'name' => $bug->title,
                        'project' => $bug->project->name ?? 'N/A',
                        'hours' => $testingHours,
                        'earnings' => $testingHours * $developer->hour_value,
                        'completed_at' => $bug->qa_testing_finished_at,
                        'type' => 'QA Testing',
                    ];
                });
            }

            $totalEarnings += $qaTaskEarnings + $qaBugEarnings;

            return [
                'id' => $developer->id,
                'name' => $developer->name,
                'email' => $developer->email,
                'role' => $developer->roles->first()->name ?? 'unknown',
                'hour_value' => $developer->hour_value,
                'completed_tasks' => $completedTasks->count() + $qaTasks->count() + $qaBugs->count(),
                'total_hours' => $completedTasks->sum('actual_hours') + $qaTasks->sum('hours') + $qaBugs->sum('hours'),
                'total_earnings' => $totalEarnings,
                'qa_task_earnings' => $qaTaskEarnings,
                'qa_bug_earnings' => $qaBugEarnings,
                'tasks' => $completedTasks->map(function ($task) use ($developer) {
                    return [
                        'name' => $task->name,
                        'project' => $task->sprint->project->name ?? 'N/A',
                        'hours' => $task->actual_hours ?? 0,
                        'earnings' => ($task->actual_hours ?? 0) * $developer->hour_value,
                        'completed_at' => $task->actual_finish,
                        'type' => 'Development',
                    ];
                })->concat($qaTasks)->concat($qaBugs),
            ];
        });

        $reportData = [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
            ],
            'developers' => $developers,
            'totalEarnings' => $developers->sum('total_earnings'),
            'totalHours' => $developers->sum('total_hours'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'period' => [
                'start' => $request->start_date,
                'end' => $request->end_date,
            ],
        ];

        switch ($request->format) {
            case 'excel':
                return $this->generateProjectExcel($reportData);
            case 'pdf':
                return $this->generateProjectPDF($reportData);
            case 'email':
                return $this->sendProjectEmail($reportData, $request);
        }
    }

    /**
     * Generar reporte de pago por tipo de usuario
     */
    public function generateUserTypeReport(Request $request)
    {
        $this->authorize('generatePaymentReports');

        $request->validate([
            'user_type' => 'required|in:developer,qa,team_leader',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'format' => 'required|in:pdf,email,excel',
        ]);

        // Obtener usuarios del tipo especificado
        $users = User::whereHas('roles', function ($query) use ($request) {
            $query->where('name', $request->user_type);
        })->get();

        $userReports = $users->map(function ($user) use ($request) {
            // Tareas completadas
            $completedTasks = $user->tasks()
                ->where('status', 'done');
            
            if ($request->start_date) {
                $completedTasks->where('actual_finish', '>=', $request->start_date);
            }
            if ($request->end_date) {
                $completedTasks->where('actual_finish', '<=', $request->end_date);
            }
            
            $completedTasks = $completedTasks->get();
            
            $totalEarnings = $completedTasks->sum(function ($task) use ($user) {
                return ($task->actual_hours ?? 0) * $user->hour_value;
            });

            // Calcular ganancias de QA si es QA
            $qaTaskEarnings = 0;
            $qaBugEarnings = 0;
            $qaTasks = collect();
            $qaBugs = collect();
            
            if ($user->roles->contains('name', 'qa')) {
                // Tareas testeadas por QA
                $qaTestingTasks = \App\Models\Task::where('qa_assigned_to', $user->id)
                    ->whereIn('qa_status', ['testing_finished', 'approved', 'rejected'])
                    ->whereNotNull('qa_testing_finished_at');
                
                if ($request->start_date) {
                    $qaTestingTasks->where('qa_testing_finished_at', '>=', $request->start_date);
                }
                if ($request->end_date) {
                    $qaTestingTasks->where('qa_testing_finished_at', '<=', $request->end_date);
                }
                
                $qaTestingTasks = $qaTestingTasks->get();
                
                $qaTaskEarnings = $qaTestingTasks->sum(function ($task) use ($user) {
                    $testingHours = $this->calculateTaskTestingHours($task);
                    return $testingHours * $user->hour_value;
                });

                $qaTasks = $qaTestingTasks->map(function ($task) use ($user) {
                    $testingHours = $this->calculateTaskTestingHours($task);
                    return [
                        'name' => $task->name,
                        'project' => $task->sprint->project->name ?? 'N/A',
                        'hours' => $testingHours,
                        'earnings' => $testingHours * $user->hour_value,
                        'completed_at' => $task->qa_testing_finished_at,
                        'type' => 'QA Testing',
                    ];
                });

                // Bugs testeados por QA
                $qaTestingBugs = \App\Models\Bug::where('qa_assigned_to', $user->id)
                    ->whereIn('qa_status', ['testing_finished', 'approved', 'rejected'])
                    ->whereNotNull('qa_testing_finished_at');
                
                if ($request->start_date) {
                    $qaTestingBugs->where('qa_testing_finished_at', '>=', $request->start_date);
                }
                if ($request->end_date) {
                    $qaTestingBugs->where('qa_testing_finished_at', '<=', $request->end_date);
                }
                
                $qaTestingBugs = $qaTestingBugs->get();
                
                $qaBugEarnings = $qaTestingBugs->sum(function ($bug) use ($user) {
                    $testingHours = $this->calculateBugTestingHours($bug);
                    return $testingHours * $user->hour_value;
                });

                $qaBugs = $qaTestingBugs->map(function ($bug) use ($user) {
                    $testingHours = $this->calculateBugTestingHours($bug);
                    return [
                        'name' => $bug->title,
                        'project' => $bug->project->name ?? 'N/A',
                        'hours' => $testingHours,
                        'earnings' => $testingHours * $user->hour_value,
                        'completed_at' => $bug->qa_testing_finished_at,
                        'type' => 'QA Testing',
                    ];
                });
            }

            $totalEarnings += $qaTaskEarnings + $qaBugEarnings;

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->roles->first()->name ?? 'unknown',
                'hour_value' => $user->hour_value,
                'completed_tasks' => $completedTasks->count() + $qaTasks->count() + $qaBugs->count(),
                'total_hours' => $completedTasks->sum('actual_hours') + $qaTasks->sum('hours') + $qaBugs->sum('hours'),
                'total_earnings' => $totalEarnings,
                'qa_task_earnings' => $qaTaskEarnings,
                'qa_bug_earnings' => $qaBugEarnings,
                'tasks' => $completedTasks->map(function ($task) use ($user) {
                    return [
                        'name' => $task->name,
                        'project' => $task->sprint->project->name ?? 'N/A',
                        'hours' => $task->actual_hours ?? 0,
                        'earnings' => ($task->actual_hours ?? 0) * $user->hour_value,
                        'completed_at' => $task->actual_finish,
                        'type' => 'Development',
                    ];
                })->concat($qaTasks)->concat($qaBugs),
            ];
        });

        $reportData = [
            'user_type' => $request->user_type,
            'developers' => $userReports,
            'totalEarnings' => $userReports->sum('total_earnings'),
            'totalHours' => $userReports->sum('total_hours'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'period' => [
                'start' => $request->start_date,
                'end' => $request->end_date,
            ],
        ];

        switch ($request->format) {
            case 'excel':
                return $this->generateUserTypeExcel($reportData);
            case 'pdf':
                return $this->generateUserTypePDF($reportData);
            case 'email':
                return $this->sendUserTypeEmail($reportData, $request);
        }
    }

    /**
     * Aprobar un reporte
     */
    public function approve(PaymentReport $paymentReport)
    {
        $this->authorize('approvePaymentReports');

        $success = $this->paymentService->approveReport($paymentReport, Auth::user());

        if ($success) {
            return redirect()->back()->with('success', 'Payment report approved successfully');
        }

        return redirect()->back()->with('error', 'Failed to approve payment report');
    }

    /**
     * Marcar reporte como pagado
     */
    public function markAsPaid(PaymentReport $paymentReport)
    {
        $this->authorize('approvePaymentReports');

        $success = $this->paymentService->markReportAsPaid($paymentReport);

        if ($success) {
            return redirect()->back()->with('success', 'Payment report marked as paid');
        }

        return redirect()->back()->with('error', 'Failed to mark payment report as paid');
    }

    /**
     * Exportar reportes
     */
    public function export(Request $request)
    {
        $this->authorize('viewPaymentReports');

        $request->validate([
            'format' => 'required|in:pdf',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $reports = PaymentReport::with('user')
            ->whereBetween('week_start_date', [$request->start_date, $request->end_date])
            ->get();

         return $this->exportToPdf($reports, $request->start_date, $request->end_date);
    }

    /**
     * Método de prueba para verificar generación de reportes
     */
    public function testReportGeneration()
    {
        try {
            // Buscar proyecto
            $project = \App\Models\Project::first();
            if (!$project) {
                return response()->json(['error' => 'No se encontraron proyectos'], 404);
            }
            
            // Simular datos del reporte
            $reportData = [
                'project' => [
                    'id' => $project->id,
                    'name' => $project->name,
                    'description' => $project->description,
                ],
                'developers' => [],
                'totalEarnings' => 0,
                'totalHours' => 0,
                'generated_at' => now()->format('Y-m-d H:i:s'),
                'period' => [
                    'start' => null,
                    'end' => null,
                ],
            ];
            
            // Generar Excel de prueba
            $filename = 'test_report_' . date('Y-m-d_H-i-s') . '.csv';
            $csvContent = '';
            $csvContent .= chr(0xEF).chr(0xBB).chr(0xBF);
            $csvContent .= $this->arrayToCsv(['Test Report']) . "\n";
            $csvContent .= $this->arrayToCsv(['Project: ' . $project->name]) . "\n";
            $csvContent .= $this->arrayToCsv(['Generated: ' . $reportData['generated_at']]) . "\n";
            $csvContent .= $this->arrayToCsv(['Status: Working']) . "\n";
            
            return response($csvContent)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Cache-Control', 'no-cache, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0')
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
                
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Error generando reporte de prueba: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Método simplificado para generar reporte Excel
     */
    public function generateSimpleExcel(Request $request)
    {
        try {
            $request->validate([
                'project_id' => 'required|exists:projects,id',
            ]);

            $project = \App\Models\Project::findOrFail($request->project_id);
            
            $filename = 'simple_report_' . $project->name . '_' . date('Y-m-d_H-i-s') . '.csv';
            $csvContent = '';
            $csvContent .= chr(0xEF).chr(0xBB).chr(0xBF);
            
            $csvContent .= $this->arrayToCsv(['Simple Payment Report']) . "\n";
            $csvContent .= $this->arrayToCsv(['Project: ' . $project->name]) . "\n";
            $csvContent .= $this->arrayToCsv(['Generated: ' . now()->format('Y-m-d H:i:s')]) . "\n";
            $csvContent .= $this->arrayToCsv([]) . "\n";
            
            // Datos de prueba
            $csvContent .= $this->arrayToCsv(['Name', 'Role', 'Hours', 'Earnings']) . "\n";
            $csvContent .= $this->arrayToCsv(['Test User', 'Developer', '40', '$1200']) . "\n";
            $csvContent .= $this->arrayToCsv(['QA User', 'QA', '20', '$500']) . "\n";
            
            return response($csvContent)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Cache-Control', 'no-cache, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
                
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Error generando reporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método de prueba para verificar comunicación
     */
    public function testCommunication()
    {
        try {
            $project = \App\Models\Project::first();
            
            return response()->json([
                'success' => true,
                'message' => 'Comunicación exitosa',
                'project' => $project ? [
                    'id' => $project->id,
                    'name' => $project->name
                ] : null,
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);
                
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error en comunicación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar a PDF
     */
    private function exportToPdf($reports, $startDate, $endDate)
    {
        // Implementar exportación a PDF
        // Por ahora retornamos un mensaje
        return redirect()->back()->with('info', 'PDF export feature will be implemented soon');
    }



    /**
     * Generar Excel para reporte detallado
     */
    private function generateExcel($data)
    {
        $filename = 'payment_report_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        // Crear instancia del servicio de Excel
        $excelService = new ExcelExportService();
        
        // Preparar datos para el ExcelExportService
        $developersForExcel = [];
        
        foreach ($data['developers'] as $developer) {
            $tasksForExcel = [];
            
            // Procesar tareas y bugs para el formato esperado por ExcelExportService
            foreach ($developer['tasks'] as $task) {
                $taskData = [
                    'name' => $task['name'],
                    'project' => $task['project'],
                    'estimated_hours' => $task['hours'],
                    'actual_hours' => $task['hours'], // Para compatibilidad
                    'earnings' => $task['earnings'],
                    'completed_at' => $task['completed_at'],
                    'type' => $task['type'] ?? 'Task'
                ];
                
                $tasksForExcel[] = $taskData;
            }
            
            $developersForExcel[] = [
                'name' => $developer['name'],
                'email' => $developer['email'],
                'role' => $developer['role'] ?? 'Developer',
                'hour_value' => $developer['hour_value'],
                'total_earnings' => $developer['total_earnings'],
                'tasks' => $tasksForExcel
            ];
        }
        
        // Generar Excel con estilos usando ExcelExportService
        $spreadsheet = $excelService->generatePaymentReport(
            $developersForExcel,
            $data['period']['start'],
            $data['period']['end']
        );
        
        // Crear writer y generar archivo
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
        $writer->save($tempFile);
        
        // Leer contenido del archivo
        $content = file_get_contents($tempFile);
        unlink($tempFile); // Eliminar archivo temporal
        
        // Retornar descarga con Excel con estilos
        return response($content)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'no-cache, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
    
    /**
     * Convertir array a CSV string
     */
    private function arrayToCsv($array)
    {
        $output = fopen('php://temp', 'w+');
        fputcsv($output, $array);
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        return rtrim($csv, "\n\r");
    }

    /**
     * Generar PDF para reporte detallado
     */
    private function generatePDF($data)
    {
        $filename = 'payment_report_' . date('Y-m-d_H-i-s') . '.pdf';
        
        // Generar PDF usando DomPDF
        $pdf = PDF::loadView('reports.payment', $data);
        
        // Retornar descarga usando response() directo (método que funciona)
        return response($pdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'no-cache, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Enviar email con reporte detallado
     */
    private function sendEmail($data, $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
            ]);

            // Generar contenido HTML simple para el email
            $htmlContent = $this->generateSimpleEmailContent($data);
            
            // Simular envío de email (para evitar problemas de configuración)
            // En producción, esto debería usar Mail::send()
            
            // Log del contenido del email para debugging
            \Log::info('Email content generated for: ' . $request->email, [
                'content_length' => strlen($htmlContent),
                'data_summary' => [
                    'developers_count' => count($data['developers']),
                    'total_earnings' => $data['totalEarnings'],
                    'total_hours' => $data['totalHours']
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment report content generated successfully for ' . $request->email . ' (email simulation mode)'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error generating email content: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descarga directa de Excel (sin Inertia)
     */
    public function downloadExcel(Request $request)
    {
        // Validar request
        $request->validate([
            'developer_ids' => 'required|array',
            'developer_ids.*' => 'exists:users,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        // Usar el mismo método que generateDetailedReport
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now()->endOfMonth();

        $developers = User::whereIn('id', $request->developer_ids)->get()->map(function ($developer) use ($startDate, $endDate) {
            // Usar PaymentService para obtener datos completos
            $report = $this->paymentService->generateReportForDateRange($developer, $startDate, $endDate);
            
            // Obtener todas las tareas y bugs (completados, en progreso y QA)
            $allTasks = collect();
            
            // Tareas completadas
            if (isset($report->task_details['tasks']['completed'])) {
                $allTasks = $allTasks->merge(collect($report->task_details['tasks']['completed'])->map(function ($task) {
                    return [
                        'name' => $task['name'],
                        'project' => $task['project'],
                        'hours' => $task['hours'],
                        'earnings' => $task['payment'],
                        'completed_at' => $task['completed_at'],
                        'type' => 'Task (Completed)',
                    ];
                }));
            }
            
            // Tareas en progreso
            if (isset($report->task_details['tasks']['in_progress'])) {
                $allTasks = $allTasks->merge(collect($report->task_details['tasks']['in_progress'])->map(function ($task) {
                    return [
                        'name' => $task['name'],
                        'project' => $task['project'],
                        'hours' => $task['hours'],
                        'earnings' => $task['payment'],
                        'completed_at' => null,
                        'type' => 'Task (In Progress)',
                    ];
                }));
            }
            
            // Bugs completados
            if (isset($report->task_details['bugs']['completed'])) {
                $allTasks = $allTasks->merge(collect($report->task_details['bugs']['completed'])->map(function ($bug) {
                    return [
                        'name' => $bug['name'],
                        'project' => $bug['project'],
                        'hours' => $bug['hours'],
                        'earnings' => $bug['payment'],
                        'completed_at' => $bug['completed_at'],
                        'type' => 'Bug (Resolved)',
                    ];
                }));
            }
            
            // Bugs en progreso
            if (isset($report->task_details['bugs']['in_progress'])) {
                $allTasks = $allTasks->merge(collect($report->task_details['bugs']['in_progress'])->map(function ($bug) {
                    return [
                        'name' => $bug['name'],
                        'project' => $bug['project'],
                        'hours' => $bug['hours'],
                        'earnings' => $bug['payment'],
                        'completed_at' => null,
                        'type' => 'Bug (In Progress)',
                    ];
                }));
            }
            
            // QA Testing Tasks
            if (isset($report->task_details['qa']['tasks_tested'])) {
                $allTasks = $allTasks->merge(collect($report->task_details['qa']['tasks_tested'])->map(function ($task) {
                    return [
                        'name' => $task['name'] . ' (QA Testing)',
                        'project' => $task['project'],
                        'hours' => $task['hours'],
                        'earnings' => $task['payment'],
                        'completed_at' => $task['testing_finished_at'],
                        'type' => 'QA Task Testing',
                    ];
                }));
            }
            
            // QA Testing Bugs
            if (isset($report->task_details['qa']['bugs_tested'])) {
                $allTasks = $allTasks->merge(collect($report->task_details['qa']['bugs_tested'])->map(function ($bug) {
                    return [
                        'name' => $bug['name'] . ' (QA Testing)',
                        'project' => $bug['project'],
                        'hours' => $bug['hours'],
                        'earnings' => $bug['payment'],
                        'completed_at' => $bug['testing_finished_at'],
                        'type' => 'QA Bug Testing',
                    ];
                }));
            }
            
            return [
                'id' => $developer->id,
                'name' => $developer->name,
                'email' => $developer->email,
                'role' => $developer->roles->first() ? $developer->roles->first()->name : 'Developer',
                'hour_value' => $developer->hour_value,
                'completed_tasks' => $report->completed_tasks_count,
                'total_hours' => $report->total_hours,
                'total_earnings' => $report->total_payment,
                'tasks' => $allTasks,
            ];
        });

        $reportData = [
            'developers' => $developers,
            'totalEarnings' => $developers->sum('total_earnings'),
            'totalHours' => $developers->sum('total_hours'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
        ];

        return $this->generateExcel($reportData);
    }

    /**
     * Descarga directa de PDF (sin Inertia)
     */
    public function downloadPDF(Request $request)
    {
        // Validar request
        $request->validate([
            'developer_ids' => 'required|array',
            'developer_ids.*' => 'exists:users,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        // Usar el mismo método que generateDetailedReport
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now()->endOfMonth();

        $developers = User::whereIn('id', $request->developer_ids)->get()->map(function ($developer) use ($startDate, $endDate) {
            // Usar PaymentService para obtener datos completos
            $report = $this->paymentService->generateReportForDateRange($developer, $startDate, $endDate);
            
            // Obtener todas las tareas y bugs (completados, en progreso y QA)
            $allTasks = collect();
            
            // Tareas completadas
            if (isset($report->task_details['tasks']['completed'])) {
                $allTasks = $allTasks->merge(collect($report->task_details['tasks']['completed'])->map(function ($task) {
                    return [
                        'name' => $task['name'],
                        'project' => $task['project'],
                        'hours' => $task['hours'],
                        'earnings' => $task['payment'],
                        'completed_at' => $task['completed_at'],
                        'type' => 'Task (Completed)',
                    ];
                }));
            }
            
            // Tareas en progreso
            if (isset($report->task_details['tasks']['in_progress'])) {
                $allTasks = $allTasks->merge(collect($report->task_details['tasks']['in_progress'])->map(function ($task) {
                    return [
                        'name' => $task['name'],
                        'project' => $task['project'],
                        'hours' => $task['hours'],
                        'earnings' => $task['payment'],
                        'completed_at' => null,
                        'type' => 'Task (In Progress)',
                    ];
                }));
            }
            
            // Bugs completados
            if (isset($report->task_details['bugs']['completed'])) {
                $allTasks = $allTasks->merge(collect($report->task_details['bugs']['completed'])->map(function ($bug) {
                    return [
                        'name' => $bug['name'],
                        'project' => $bug['project'],
                        'hours' => $bug['hours'],
                        'earnings' => $bug['payment'],
                        'completed_at' => $bug['completed_at'],
                        'type' => 'Bug (Resolved)',
                    ];
                }));
            }
            
            // Bugs en progreso
            if (isset($report->task_details['bugs']['in_progress'])) {
                $allTasks = $allTasks->merge(collect($report->task_details['bugs']['in_progress'])->map(function ($bug) {
                    return [
                        'name' => $bug['name'],
                        'project' => $bug['project'],
                        'hours' => $bug['hours'],
                        'earnings' => $bug['payment'],
                        'completed_at' => null,
                        'type' => 'Bug (In Progress)',
                    ];
                }));
            }
            
            // QA Testing Tasks
            if (isset($report->task_details['qa']['tasks_tested'])) {
                $allTasks = $allTasks->merge(collect($report->task_details['qa']['tasks_tested'])->map(function ($task) {
                    return [
                        'name' => $task['name'] . ' (QA Testing)',
                        'project' => $task['project'],
                        'hours' => $task['hours'],
                        'earnings' => $task['payment'],
                        'completed_at' => $task['testing_finished_at'],
                        'type' => 'QA Task Testing',
                    ];
                }));
            }
            
            // QA Testing Bugs
            if (isset($report->task_details['qa']['bugs_tested'])) {
                $allTasks = $allTasks->merge(collect($report->task_details['qa']['bugs_tested'])->map(function ($bug) {
                    return [
                        'name' => $bug['name'] . ' (QA Testing)',
                        'project' => $bug['project'],
                        'hours' => $bug['hours'],
                        'earnings' => $bug['payment'],
                        'completed_at' => $bug['testing_finished_at'],
                        'type' => 'QA Bug Testing',
                    ];
                }));
            }
            
            return [
                'id' => $developer->id,
                'name' => $developer->name,
                'email' => $developer->email,
                'hour_value' => $developer->hour_value,
                'completed_tasks' => $report->completed_tasks_count,
                'total_hours' => $report->total_hours,
                'total_earnings' => $report->total_payment,
                'tasks' => $allTasks,
            ];
        });

        $reportData = [
            'developers' => $developers,
            'totalEarnings' => $developers->sum('total_earnings'),
            'totalHours' => $developers->sum('total_hours'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
        ];

        return $this->generatePDF($reportData);
    }

    /**
     * Calcular horas de testing de QA para una tarea específica
     */
    private function calculateTaskTestingHours($task)
    {
        if (!$task->qa_testing_started_at || !$task->qa_testing_finished_at) {
            return 0;
        }

        $startTime = Carbon::parse($task->qa_testing_started_at);
        $finishTime = Carbon::parse($task->qa_testing_finished_at);
        
        // Si hay pausas, calcular el tiempo real de testing
        if ($task->qa_testing_paused_at) {
            $pausedTime = Carbon::parse($task->qa_testing_paused_at);
            $resumeTime = $task->qa_testing_resumed_at ? Carbon::parse($task->qa_testing_resumed_at) : $finishTime;
            
            $activeTime = $pausedTime->diffInSeconds($startTime) + $finishTime->diffInSeconds($resumeTime);
        } else {
            $activeTime = $finishTime->diffInSeconds($startTime);
        }

        return round($activeTime / 3600, 2); // Convertir segundos a horas
    }

    /**
     * Calcular horas de testing de QA para un bug específico
     */
    private function calculateBugTestingHours($bug)
    {
        if (!$bug->qa_testing_started_at || !$bug->qa_testing_finished_at) {
            return 0;
        }

        $startTime = Carbon::parse($bug->qa_testing_started_at);
        $finishTime = Carbon::parse($bug->qa_testing_finished_at);
        
        // Si hay pausas, calcular el tiempo real de testing
        if ($bug->qa_testing_paused_at) {
            $pausedTime = Carbon::parse($bug->qa_testing_paused_at);
            $resumeTime = $bug->qa_testing_resumed_at ? Carbon::parse($bug->qa_testing_resumed_at) : $finishTime;
            
            $activeTime = $pausedTime->diffInSeconds($startTime) + $finishTime->diffInSeconds($resumeTime);
        } else {
            $activeTime = $finishTime->diffInSeconds($startTime);
        }

        return round($activeTime / 3600, 2); // Convertir segundos a horas
    }

    /**
     * Generar Excel para reporte por proyecto
     */
    private function generateProjectExcel($data)
    {
        $filename = 'project_payment_report_' . $data['project']['name'] . '_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        $csvContent = '';
        $csvContent .= chr(0xEF).chr(0xBB).chr(0xBF);
        
        $csvContent .= $this->arrayToCsv(['Project Payment Report']) . "\n";
        $csvContent .= $this->arrayToCsv(['Project: ' . $data['project']['name']]) . "\n";
        $csvContent .= $this->arrayToCsv(['Generated: ' . $data['generated_at']]) . "\n";
        $csvContent .= $this->arrayToCsv([]) . "\n";
        
        // Developer summary
        $csvContent .= $this->arrayToCsv(['Team Member Summary']) . "\n";
        $csvContent .= $this->arrayToCsv(['Name', 'Role', 'Email', 'Hour Rate ($)', 'Total Hours', 'Total Earnings ($)', 'QA Task Earnings ($)', 'QA Bug Earnings ($)']) . "\n";
        
        foreach ($data['developers'] as $developer) {
            $csvContent .= $this->arrayToCsv([
                $developer['name'],
                ucfirst($developer['role']),
                $developer['email'],
                number_format($developer['hour_value'], 2),
                number_format($developer['total_hours'], 2),
                number_format($developer['total_earnings'], 2),
                number_format($developer['qa_task_earnings'], 2),
                number_format($developer['qa_bug_earnings'], 2)
            ]) . "\n";
        }
        
        // Task details
        $csvContent .= $this->arrayToCsv([]) . "\n";
        $csvContent .= $this->arrayToCsv(['Work Details']) . "\n";
        $csvContent .= $this->arrayToCsv(['Team Member', 'Role', 'Work Item', 'Type', 'Project', 'Hours', 'Earnings ($)', 'Completed Date']) . "\n";
        
        foreach ($data['developers'] as $developer) {
            foreach ($developer['tasks'] as $task) {
                $csvContent .= $this->arrayToCsv([
                    $developer['name'],
                    ucfirst($developer['role']),
                    $task['name'],
                    $task['type'],
                    $task['project'],
                    number_format($task['hours'], 2),
                    number_format($task['earnings'], 2),
                    $task['completed_at'] ? date('Y-m-d', strtotime($task['completed_at'])) : 'N/A'
                ]) . "\n";
            }
        }
        
        // Summary
        $csvContent .= $this->arrayToCsv([]) . "\n";
        $csvContent .= $this->arrayToCsv(['Project Summary']) . "\n";
        $csvContent .= $this->arrayToCsv(['Total Team Members', 'Total Hours', 'Total Earnings ($)', 'Development Earnings ($)', 'QA Earnings ($)']) . "\n";
        
        $developmentEarnings = collect($data['developers'])->sum(function ($dev) {
            return $dev['total_earnings'] - $dev['qa_task_earnings'] - $dev['qa_bug_earnings'];
        });
        $qaEarnings = collect($data['developers'])->sum(function ($dev) {
            return $dev['qa_task_earnings'] + $dev['qa_bug_earnings'];
        });
        
        $csvContent .= $this->arrayToCsv([
            count($data['developers']),
            number_format($data['totalHours'], 2),
            number_format($data['totalEarnings'], 2),
            number_format($developmentEarnings, 2),
            number_format($qaEarnings, 2)
        ]) . "\n";
        
        return response($csvContent)
            ->header('Content-Type', 'application/octet-stream')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'no-cache, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Generar Excel para reporte por tipo de usuario
     */
    private function generateUserTypeExcel($data)
    {
        $filename = 'user_type_payment_report_' . $data['user_type'] . '_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        $csvContent = '';
        $csvContent .= chr(0xEF).chr(0xBB).chr(0xBF);
        
        $csvContent .= $this->arrayToCsv(['User Type Payment Report']) . "\n";
        $csvContent .= $this->arrayToCsv(['User Type: ' . ucfirst($data['user_type'])]) . "\n";
        $csvContent .= $this->arrayToCsv(['Generated: ' . $data['generated_at']]) . "\n";
        $csvContent .= $this->arrayToCsv([]) . "\n";
        
        // User summary
        $csvContent .= $this->arrayToCsv(['User Summary']) . "\n";
        $csvContent .= $this->arrayToCsv(['Name', 'Email', 'Hour Rate ($)', 'Total Hours', 'Total Earnings ($)', 'QA Task Earnings ($)', 'QA Bug Earnings ($)']) . "\n";
        
        foreach ($data['developers'] as $developer) {
            $csvContent .= $this->arrayToCsv([
                $developer['name'],
                $developer['email'],
                number_format($developer['hour_value'], 2),
                number_format($developer['total_hours'], 2),
                number_format($developer['total_earnings'], 2),
                number_format($developer['qa_task_earnings'], 2),
                number_format($developer['qa_bug_earnings'], 2)
            ]) . "\n";
        }
        
        // Work details
        $csvContent .= $this->arrayToCsv([]) . "\n";
        $csvContent .= $this->arrayToCsv(['Work Details']) . "\n";
        $csvContent .= $this->arrayToCsv(['User', 'Work Item', 'Type', 'Project', 'Hours', 'Earnings ($)', 'Completed Date']) . "\n";
        
        foreach ($data['developers'] as $developer) {
            foreach ($developer['tasks'] as $task) {
                $csvContent .= $this->arrayToCsv([
                    $developer['name'],
                    $task['name'],
                    $task['type'],
                    $task['project'],
                    number_format($task['hours'], 2),
                    number_format($task['earnings'], 2),
                    $task['completed_at'] ? date('Y-m-d', strtotime($task['completed_at'])) : 'N/A'
                ]) . "\n";
            }
        }
        
        // Summary
        $csvContent .= $this->arrayToCsv([]) . "\n";
        $csvContent .= $this->arrayToCsv(['Summary']) . "\n";
        $csvContent .= $this->arrayToCsv(['Total Users', 'Total Hours', 'Total Earnings ($)', 'Development Earnings ($)', 'QA Earnings ($)']) . "\n";
        
        $developmentEarnings = collect($data['developers'])->sum(function ($dev) {
            return $dev['total_earnings'] - $dev['qa_task_earnings'] - $dev['qa_bug_earnings'];
        });
        $qaEarnings = collect($data['developers'])->sum(function ($dev) {
            return $dev['qa_task_earnings'] + $dev['qa_bug_earnings'];
        });
        
        $csvContent .= $this->arrayToCsv([
            count($data['developers']),
            number_format($data['totalHours'], 2),
            number_format($data['totalEarnings'], 2),
            number_format($developmentEarnings, 2),
            number_format($qaEarnings, 2)
        ]) . "\n";
        
        return response($csvContent)
            ->header('Content-Type', 'application/octet-stream')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'no-cache, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Generar PDF para reporte por proyecto
     */
    private function generateProjectPDF($data)
    {
        $filename = 'project_payment_report_' . $data['project']['name'] . '_' . date('Y-m-d_H-i-s') . '.pdf';
        
        // Crear contenido PDF
        $pdfContent = view('reports.project-payment', $data)->render();
        
        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'no-cache, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Generar PDF para reporte por tipo de usuario
     */
    private function generateUserTypePDF($data)
    {
        $filename = 'user_type_payment_report_' . $data['user_type'] . '_' . date('Y-m-d_H-i-s') . '.pdf';
        
        // Crear contenido PDF
        $pdfContent = view('reports.user-type-payment', $data)->render();
        
        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'no-cache, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Enviar email con reporte por proyecto
     */
    private function sendProjectEmail($data, $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $pdf = PDF::loadView('reports.project-payment', $data);
        $filename = 'project_payment_report_' . $data['project']['name'] . '_' . date('Y-m-d_H-i-s') . '.pdf';

        Mail::send('emails.project-payment-report', $data, function ($message) use ($request, $pdf, $filename) {
            $message->to($request->email)
                    ->subject('Project Payment Report - ' . $data['project']['name'] . ' - ' . date('Y-m-d'))
                    ->attachData($pdf->output(), $filename);
        });

        return redirect()->back()->with('success', 'Project payment report sent to ' . $request->email);
    }

    /**
     * Enviar email con reporte por tipo de usuario
     */
    private function sendUserTypeEmail($data, $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $pdf = PDF::loadView('reports.user-type-payment', $data);
        $filename = 'user_type_payment_report_' . $data['user_type'] . '_' . date('Y-m-d_H-i-s') . '.pdf';

        Mail::send('emails.user-type-payment-report', $data, function ($message) use ($request, $pdf, $filename, $data) {
            $message->to($request->email)
                    ->subject('User Type Payment Report - ' . ucfirst($data['user_type']) . ' - ' . date('Y-m-d'))
                    ->attachData($pdf->output(), $filename);
        });

        return redirect()->back()->with('success', 'User type payment report sent to ' . $request->email);
    }

    /**
     * Generate simple HTML content for email
     */
    private function generateSimpleEmailContent($reportData)
    {
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
        .summary { background-color: #f5f5f5; padding: 15px; border-radius: 5px; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .total { font-weight: bold; color: #2c5aa0; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Payment Report</h1>
        <p>Generated: ' . $reportData['generated_at'] . '</p>
    </div>

    <div class="summary">
        <h3>Summary</h3>
        <p><strong>Total Developers:</strong> ' . count($reportData['developers']) . '</p>
        <p><strong>Total Hours:</strong> ' . number_format($reportData['totalHours'], 2) . '</p>
        <p><strong>Total Earnings:</strong> $' . number_format($reportData['totalEarnings'], 2) . '</p>
    </div>

    <h3>Developer Summary</h3>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Hour Rate ($)</th>
                <th>Completed Tasks</th>
                <th>Total Hours</th>
                <th>Total Earnings ($)</th>
            </tr>
        </thead>
        <tbody>';

        foreach ($reportData['developers'] as $developer) {
            $html .= '
            <tr>
                <td>' . htmlspecialchars($developer['name']) . '</td>
                <td>' . htmlspecialchars($developer['email']) . '</td>
                <td>$' . number_format($developer['hour_value'], 2) . '</td>
                <td>' . $developer['completed_tasks'] . '</td>
                <td>' . number_format($developer['total_hours'], 2) . '</td>
                <td class="total">$' . number_format($developer['total_earnings'], 2) . '</td>
            </tr>';
        }

        $html .= '
        </tbody>
    </table>

    <h3>Task Details</h3>
    <table>
        <thead>
            <tr>
                <th>Developer</th>
                <th>Task</th>
                <th>Project</th>
                <th>Hours</th>
                <th>Earnings ($)</th>
                <th>Completed Date</th>
            </tr>
        </thead>
        <tbody>';

        foreach ($reportData['developers'] as $developer) {
            foreach ($developer['tasks'] as $task) {
                $html .= '
                <tr>
                    <td>' . htmlspecialchars($developer['name']) . '</td>
                    <td>' . htmlspecialchars($task['name']) . '</td>
                    <td>' . htmlspecialchars($task['project']) . '</td>
                    <td>' . number_format($task['hours'], 2) . '</td>
                    <td>$' . number_format($task['earnings'], 2) . '</td>
                    <td>' . ($task['completed_at'] ? date('Y-m-d', strtotime($task['completed_at'])) : 'N/A') . '</td>
                </tr>';
            }
        }

        $html .= '
        </tbody>
    </table>

    <div class="footer">
        <p>This report was generated automatically by the Tracker system.</p>
        <p>If you have any questions, please contact the system administrator.</p>
    </div>
</body>
</html>';

        return $html;
    }
}
