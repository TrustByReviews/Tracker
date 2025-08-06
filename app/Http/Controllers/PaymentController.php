<?php

namespace App\Http\Controllers;

use App\Models\PaymentReport;
use App\Models\User;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

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

        // Obtener datos para reportes de pago (desde PaymentReportController)
        $developers = User::with(['tasks', 'projects'])
            ->whereHas('roles', function ($query) {
                $query->where('name', '!=', 'admin');
            })
            ->get()
            ->map(function ($developer) {
                $completedTasks = $developer->tasks->where('status', 'done');
                $totalEarnings = $completedTasks->sum(function ($task) use ($developer) {
                    return ($task->actual_hours ?? 0) * $developer->hour_value;
                });

                return [
                    'id' => $developer->id,
                    'name' => $developer->name,
                    'email' => $developer->email,
                    'hour_value' => $developer->hour_value,
                    'total_tasks' => $developer->tasks->count(),
                    'completed_tasks' => $completedTasks->count(),
                    'total_hours' => $completedTasks->sum('actual_hours'),
                    'total_earnings' => $totalEarnings,
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
            'format' => 'required|in:pdf,email,excel',
        ]);

        $developers = User::with(['tasks' => function ($query) use ($request) {
            $query->where('status', 'done');
            if ($request->start_date) {
                $query->where('actual_finish', '>=', $request->start_date);
            }
            if ($request->end_date) {
                $query->where('actual_finish', '<=', $request->end_date);
            }
        }, 'projects'])
        ->whereIn('id', $request->developer_ids)
        ->get()
        ->map(function ($developer) {
            $completedTasks = $developer->tasks;
            $totalEarnings = $completedTasks->sum(function ($task) use ($developer) {
                return ($task->actual_hours ?? 0) * $developer->hour_value;
            });

            return [
                'id' => $developer->id,
                'name' => $developer->name,
                'email' => $developer->email,
                'hour_value' => $developer->hour_value,
                'completed_tasks' => $completedTasks->count(),
                'total_hours' => $completedTasks->sum('actual_hours'),
                'total_earnings' => $totalEarnings,
                'tasks' => $completedTasks->map(function ($task) use ($developer) {
                    return [
                        'name' => $task->name,
                        'project' => $task->sprint->project->name ?? 'N/A',
                        'hours' => $task->actual_hours ?? 0,
                        'earnings' => ($task->actual_hours ?? 0) * $developer->hour_value,
                        'completed_at' => $task->actual_finish,
                    ];
                }),
            ];
        });

        $reportData = [
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
                return $this->generateExcel($reportData);
            case 'pdf':
                return $this->generatePDF($reportData);
            case 'email':
                return $this->sendEmail($reportData, $request);
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
        
        // Crear contenido CSV
        $csvContent = '';
        
        // BOM para UTF-8
        $csvContent .= chr(0xEF).chr(0xBB).chr(0xBF);
        
        // Crear contenido CSV
        $csvContent .= $this->arrayToCsv(['Payment Report']) . "\n";
        $csvContent .= $this->arrayToCsv(['Generated: ' . $data['generated_at']]) . "\n";
        $csvContent .= $this->arrayToCsv([]) . "\n";
        
        // Developer summary
        $csvContent .= $this->arrayToCsv(['Developer Summary']) . "\n";
        $csvContent .= $this->arrayToCsv(['Name', 'Email', 'Hour Rate ($)', 'Total Hours', 'Total Earnings ($)']) . "\n";
        
        foreach ($data['developers'] as $developer) {
            $csvContent .= $this->arrayToCsv([
                $developer['name'],
                $developer['email'],
                number_format($developer['hour_value'], 2),
                number_format($developer['total_hours'], 2),
                number_format($developer['total_earnings'], 2)
            ]) . "\n";
        }
        
        // Task details
        $csvContent .= $this->arrayToCsv([]) . "\n";
        $csvContent .= $this->arrayToCsv(['Task Details']) . "\n";
        $csvContent .= $this->arrayToCsv(['Developer', 'Task', 'Project', 'Hours', 'Earnings ($)', 'Completed Date']) . "\n";
        
        foreach ($data['developers'] as $developer) {
            foreach ($developer['tasks'] as $task) {
                $csvContent .= $this->arrayToCsv([
                    $developer['name'],
                    $task['name'],
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
        $csvContent .= $this->arrayToCsv(['Total Developers', 'Total Hours', 'Total Earnings ($)']) . "\n";
        $csvContent .= $this->arrayToCsv([
            count($data['developers']),
            number_format($data['totalHours'], 2),
            number_format($data['totalEarnings'], 2)
        ]) . "\n";
        
        // Retornar descarga usando response() directo (método que funciona)
        return response($csvContent)
            ->header('Content-Type', 'application/octet-stream')
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
        
        // Crear contenido PDF
        $pdfContent = view('reports.payment', $data)->render();
        
        // Retornar descarga usando response() directo (método que funciona)
        return response($pdfContent)
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
        $request->validate([
            'email' => 'required|email',
        ]);

        $pdf = PDF::loadView('reports.payment', $data);
        $filename = 'payment_report_' . date('Y-m-d_H-i-s') . '.pdf';

        Mail::send('emails.payment-report', $data, function ($message) use ($request, $pdf, $filename) {
            $message->to($request->email)
                    ->subject('Payment Report - ' . date('Y-m-d'))
                    ->attachData($pdf->output(), $filename);
        });

        return redirect()->back()->with('success', 'Payment report sent to ' . $request->email);
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

        // Preparar datos
        $developers = User::with(['tasks' => function ($query) use ($request) {
            $query->where('status', 'done');
            if ($request->start_date) {
                $query->where('actual_finish', '>=', $request->start_date);
            }
            if ($request->end_date) {
                $query->where('actual_finish', '<=', $request->end_date);
            }
        }, 'projects'])
        ->whereIn('id', $request->developer_ids)
        ->get()
        ->map(function ($developer) {
            $completedTasks = $developer->tasks;
            $totalEarnings = $completedTasks->sum(function ($task) use ($developer) {
                return ($task->actual_hours ?? 0) * $developer->hour_value;
            });

            return [
                'id' => $developer->id,
                'name' => $developer->name,
                'email' => $developer->email,
                'hour_value' => $developer->hour_value,
                'completed_tasks' => $completedTasks->count(),
                'total_hours' => $completedTasks->sum('actual_hours'),
                'total_earnings' => $totalEarnings,
                'tasks' => $completedTasks->map(function ($task) use ($developer) {
                    return [
                        'name' => $task->name,
                        'project' => $task->sprint->project->name ?? 'N/A',
                        'hours' => $task->actual_hours ?? 0,
                        'earnings' => ($task->actual_hours ?? 0) * $developer->hour_value,
                        'completed_at' => $task->actual_finish,
                    ];
                }),
            ];
        });

        $reportData = [
            'developers' => $developers,
            'totalEarnings' => $developers->sum('total_earnings'),
            'totalHours' => $developers->sum('total_hours'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'period' => [
                'start' => $request->start_date,
                'end' => $request->end_date,
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

        // Preparar datos (mismo código que downloadExcel)
        $developers = User::with(['tasks' => function ($query) use ($request) {
            $query->where('status', 'done');
            if ($request->start_date) {
                $query->where('actual_finish', '>=', $request->start_date);
            }
            if ($request->end_date) {
                $query->where('actual_finish', '<=', $request->end_date);
            }
        }, 'projects'])
        ->whereIn('id', $request->developer_ids)
        ->get()
        ->map(function ($developer) {
            $completedTasks = $developer->tasks;
            $totalEarnings = $completedTasks->sum(function ($task) use ($developer) {
                return ($task->actual_hours ?? 0) * $developer->hour_value;
            });

            return [
                'id' => $developer->id,
                'name' => $developer->name,
                'email' => $developer->email,
                'hour_value' => $developer->hour_value,
                'completed_tasks' => $completedTasks->count(),
                'total_hours' => $completedTasks->sum('actual_hours'),
                'total_earnings' => $totalEarnings,
                'tasks' => $completedTasks->map(function ($task) use ($developer) {
                    return [
                        'name' => $task->name,
                        'project' => $task->sprint->project->name ?? 'N/A',
                        'hours' => $task->actual_hours ?? 0,
                        'earnings' => ($task->actual_hours ?? 0) * $developer->hour_value,
                        'completed_at' => $task->actual_finish,
                    ];
                }),
            ];
        });

        $reportData = [
            'developers' => $developers,
            'totalEarnings' => $developers->sum('total_earnings'),
            'totalHours' => $developers->sum('total_hours'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'period' => [
                'start' => $request->start_date,
                'end' => $request->end_date,
            ],
        ];

        return $this->generatePDF($reportData);
    }
}
