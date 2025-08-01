<?php

namespace App\Http\Controllers;

use App\Models\PaymentReport;
use App\Models\User;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

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
     * Dashboard de pagos para admins
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

        return Inertia::render('Payments/AdminDashboard', [
            'statistics' => $statistics,
            'recentReports' => $recentReports,
            'pendingReports' => $pendingReports,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
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
            'format' => 'required|in:csv,pdf',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $reports = PaymentReport::with('user')
            ->whereBetween('week_start_date', [$request->start_date, $request->end_date])
            ->get();

        if ($request->format === 'csv') {
            return $this->exportToCsv($reports, $request->start_date, $request->end_date);
        } else {
            return $this->exportToPdf($reports, $request->start_date, $request->end_date);
        }
    }

    /**
     * Exportar a CSV
     */
    private function exportToCsv($reports, $startDate, $endDate)
    {
        $filename = "payment_reports_{$startDate}_to_{$endDate}.csv";
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Header
        fputcsv($output, ['Payment Reports Export']);
        fputcsv($output, ['Period: ' . $startDate . ' to ' . $endDate]);
        fputcsv($output, ['Generated: ' . now()->format('Y-m-d H:i:s')]);
        fputcsv($output, []);
        
        // Data headers
        fputcsv($output, ['Developer', 'Week', 'Hours', 'Rate', 'Payment', 'Status', 'Created']);
        
        foreach ($reports as $report) {
            fputcsv($output, [
                $report->user->name,
                $report->week_range,
                $report->total_hours,
                '$' . $report->hourly_rate,
                '$' . number_format($report->total_payment, 2),
                $report->status,
                $report->created_at->format('Y-m-d'),
            ]);
        }
        
        fclose($output);
        exit;
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
}
