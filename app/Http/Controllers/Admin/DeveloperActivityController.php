<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DeveloperActivityTrackingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DeveloperActivityController extends Controller
{
    protected $activityTrackingService;

    public function __construct(DeveloperActivityTrackingService $activityTrackingService)
    {
        $this->activityTrackingService = $activityTrackingService;
    }

    /**
     * Show the developer activity dashboard
     */
    public function index(Request $request)
    {
        try {
            $startDate = $request->get('start_date') 
                ? Carbon::parse($request->get('start_date'))
                : Carbon::now()->subDays(30);
                
            $endDate = $request->get('end_date')
                ? Carbon::parse($request->get('end_date'))
                : Carbon::now();

            $selectedDeveloperId = $request->get('developer_id');

            // Get all developers
            $developers = User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['developer', 'team_leader']);
            })->get(['id', 'name', 'email']);

            // Get team overview
            $teamOverview = $this->activityTrackingService->getTeamActivityOverview($startDate, $endDate);

            // Get individual developer stats if selected
            $developerStats = null;
            if ($selectedDeveloperId) {
                $developer = User::find($selectedDeveloperId);
                if ($developer) {
                    $developerStats = $this->activityTrackingService->getDeveloperStats($developer, $startDate, $endDate);
                }
            }

            // Get daily patterns
            $dailyPatterns = $this->activityTrackingService->getDailyActivityPatterns(
                $selectedDeveloperId ? User::find($selectedDeveloperId) : null,
                $startDate,
                $endDate
            );

            // Get timezone information
            $timezoneInfo = $this->activityTrackingService->getTimeZoneInfo();

            return Inertia::render('Admin/DeveloperActivity/Index', [
                'developers' => $developers,
                'teamOverview' => $teamOverview,
                'developerStats' => $developerStats,
                'dailyPatterns' => $dailyPatterns,
                'timezoneInfo' => $timezoneInfo,
                'filters' => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'developer_id' => $selectedDeveloperId
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in DeveloperActivityController::index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Inertia::render('Admin/DeveloperActivity/Index', [
                'developers' => [],
                'teamOverview' => [],
                'developerStats' => null,
                'dailyPatterns' => [],
                'timezoneInfo' => [],
                'filters' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get developer activity data for API
     */
    public function getDeveloperActivity(Request $request)
    {
        $request->validate([
            'developer_id' => 'required|uuid|exists:users,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date'
        ]);

        $developer = User::findOrFail($request->developer_id);
        
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->subDays(30);
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();

        $stats = $this->activityTrackingService->getDeveloperStats($developer, $startDate, $endDate);
        $dailyPatterns = $this->activityTrackingService->getDailyActivityPatterns($developer, $startDate, $endDate);

        return response()->json([
            'developer' => $developer->only(['id', 'name', 'email']),
            'stats' => $stats,
            'dailyPatterns' => $dailyPatterns
        ]);
    }

    /**
     * Get team activity overview for API
     */
    public function getTeamActivity(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date'
        ]);

        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->subDays(30);
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();

        $teamOverview = $this->activityTrackingService->getTeamActivityOverview($startDate, $endDate);
        $dailyPatterns = $this->activityTrackingService->getDailyActivityPatterns(null, $startDate, $endDate);

        return response()->json([
            'teamOverview' => $teamOverview,
            'dailyPatterns' => $dailyPatterns
        ]);
    }

    /**
     * Export activity report
     */
    public function exportReport(Request $request)
    {
        $request->validate([
            'format' => 'required|in:excel,pdf',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'developer_id' => 'nullable|uuid|exists:users,id'
        ]);

        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->subDays(30);
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();

        if ($request->developer_id) {
            $developer = User::findOrFail($request->developer_id);
            $data = $this->activityTrackingService->getDeveloperStats($developer, $startDate, $endDate);
            $title = "Developer Activity Report - {$developer->name}";
        } else {
            $data = $this->activityTrackingService->getTeamActivityOverview($startDate, $endDate);
            $title = "Team Activity Report";
        }

        if ($request->format === 'excel') {
            return $this->exportToExcel($data, $title, $startDate, $endDate);
        } else {
            return $this->exportToPdf($data, $title, $startDate, $endDate);
        }
    }

    /**
     * Export to Excel
     */
    protected function exportToExcel($data, $title, $startDate, $endDate)
    {
        try {
            $excelService = app(\App\Services\ExcelExportService::class);
            
            if (isset($data['developer'])) {
                // Individual developer report
                $developers = [$data];
                $filename = "developer_activity_{$data['developer']}_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}.xlsx";
            } else {
                // Team report
                $developers = $data['developers'] ?? [];
                $filename = "team_activity_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}.xlsx";
            }
            
            $spreadsheet = $excelService->generateActivityReport($developers, $startDate, $endDate);
            
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            $tempFile = tempnam(sys_get_temp_dir(), 'activity_report_');
            $writer->save($tempFile);
            
            return response()->download($tempFile, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend();
            
        } catch (\Exception $e) {
            \Log::error('Error exporting Excel activity report', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Error generating Excel report: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Export to PDF
     */
    protected function exportToPdf($data, $title, $startDate, $endDate)
    {
        try {
            if (isset($data['developer'])) {
                // Individual developer report
                $developers = [$data];
                $filename = "developer_activity_{$data['developer']}_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}.pdf";
            } else {
                // Team report
                $developers = $data['developers'] ?? [];
                $filename = "team_activity_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}.pdf";
            }
            
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.activity', [
                'developers' => $developers,
                'title' => $title,
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d'),
                'generated_at' => now()->format('Y-m-d H:i:s'),
                'timezoneInfo' => app(\App\Services\DeveloperActivityTrackingService::class)->getTimeZoneInfo()
            ]);
            
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            \Log::error('Error exporting PDF activity report', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Error generating PDF report: ' . $e->getMessage()], 500);
        }
    }
}
