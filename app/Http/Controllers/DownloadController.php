<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PaymentService;
use App\Services\ExcelExportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use App\Exports\PaymentReportExport;
use App\Exports\TaskDetailsExport;
use App\Exports\PaymentReportMultiSheet;
use Maatwebsite\Excel\Facades\Excel;

class DownloadController extends Controller
{
    protected $paymentService;
    protected $excelExportService;

    public function __construct(PaymentService $paymentService, ExcelExportService $excelExportService)
    {
        $this->paymentService = $paymentService;
        $this->excelExportService = $excelExportService;
    }

    /**
     * Helper function to convert array to CSV
     */
    private function arrayToCsv($array) {
        $output = fopen('php://temp', 'w+');
        fputcsv($output, $array);
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        return rtrim($csv, "\n\r");
    }

    /**
     * Download Excel report using CSV format (compatible with Excel)
     */
    public function downloadExcel(Request $request)
    {
        try {
            // Validación básica
            if (!$request->has('developer_ids') || !is_array($request->developer_ids)) {
                return response()->json(['error' => 'developer_ids is required and must be an array'], 422);
            }

            // Obtener datos usando el mismo método que funciona en PaymentController
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

            // Generar contenido CSV
            $filename = 'payment_report_' . date('Y-m-d_H-i-s') . '.csv';
            $csvContent = '';
            $csvContent .= chr(0xEF).chr(0xBB).chr(0xBF); // BOM para UTF-8
            
            $csvContent .= $this->arrayToCsv(['Payment Report']) . "\n";
            $csvContent .= $this->arrayToCsv(['Generated: ' . now()->format('Y-m-d H:i:s')]) . "\n";
            $csvContent .= $this->arrayToCsv([]) . "\n";
            
            // Developer summary
            $csvContent .= $this->arrayToCsv(['Developer Summary']) . "\n";
            $csvContent .= $this->arrayToCsv(['Name', 'Email', 'Hour Rate ($)', 'Completed Tasks', 'Total Hours', 'Total Earnings ($)']) . "\n";
            
            foreach ($developers as $developer) {
                $csvContent .= $this->arrayToCsv([
                    $developer['name'],
                    $developer['email'],
                    number_format($developer['hour_value'], 2),
                    $developer['completed_tasks'],
                    number_format($developer['total_hours'], 2),
                    number_format($developer['total_earnings'], 2)
                ]) . "\n";
            }
            
            // Task details
            $csvContent .= $this->arrayToCsv([]) . "\n";
            $csvContent .= $this->arrayToCsv(['Task Details']) . "\n";
            $csvContent .= $this->arrayToCsv(['Developer', 'Task', 'Project', 'Hours', 'Earnings ($)', 'Completed Date']) . "\n";
            
            foreach ($developers as $developer) {
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

            // Retornar descarga con headers correctos
            return response($csvContent)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Cache-Control', 'no-cache, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Download PDF report using simple HTML approach
     */
    public function downloadPDF(Request $request)
    {
        try {
            // Validación básica
            if (!$request->has('developer_ids') || !is_array($request->developer_ids)) {
                return response()->json(['error' => 'developer_ids is required and must be an array'], 422);
            }

            // Obtener datos usando el mismo método que funciona
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

            // Generar contenido HTML simple para PDF
            $htmlContent = $this->generateSimplePDFContent($reportData);
            
            // Retornar como HTML que se puede convertir a PDF
            return response($htmlContent)
                ->header('Content-Type', 'text/html; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="payment_report_' . date('Y-m-d_H-i-s') . '.html"')
                ->header('Cache-Control', 'no-cache, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate simple HTML content for PDF
     */
    private function generateSimplePDFContent($reportData)
    {
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
        .summary { background-color: #f5f5f5; padding: 15px; border-radius: 5px; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .total { font-weight: bold; color: #2c5aa0; }
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
</body>
</html>';

        return $html;
    }

    /**
     * Calculate hours worked until the report date
     */
    private function calculateHoursWorked($task, $reportDate)
    {
        if ($task->status === 'done') {
            // For completed tasks, use actual recorded hours
            return $task->actual_hours ?? 0;
        } elseif ($task->status === 'in_progress') {
            // For in-progress tasks, calculate hours worked until the report date
            $startDate = $task->created_at;
            $endDate = min($reportDate, $task->actual_finish ?? $reportDate);
            
            // Calculate worked days
            $daysWorked = $startDate->diffInDays($endDate) + 1;
            
            // Estimate hours worked based on estimated hours and progress
            $estimatedHours = $task->estimated_hours ?? 0;
            $progressPercentage = min(($daysWorked / 30) * 100, 100); // Assume 30 days as typical time
            
            return round(($estimatedHours * $progressPercentage) / 100, 2);
        }
        
        return 0;
    }

    /**
     * Show report in the system
     */
    public function showReport(Request $request)
    {
        try {
            // Validación básica sin usar Validator facade
            if (!$request->has('developer_ids') || !is_array($request->developer_ids)) {
                return response()->json(['error' => 'developer_ids is required and must be an array'], 422);
            }
            // Obtener datos - incluir tareas completadas y en desarrollo
            $developers = User::with(['tasks' => function ($query) use ($request) {
                $query->whereIn('status', ['done', 'in_progress']);
                if ($request->start_date) {
                    $query->where(function($q) use ($request) {
                        $q->where('actual_finish', '>=', $request->start_date)
                          ->orWhere('created_at', '>=', $request->start_date);
                    });
                }
                if ($request->end_date) {
                    $query->where(function($q) use ($request) {
                        $q->where('actual_finish', '<=', $request->end_date)
                          ->orWhere('created_at', '<=', $request->end_date);
                    });
                }
            }, 'projects'])
            ->whereIn('id', $request->developer_ids)
            ->get();

            // Mapear datos
            $mappedDevelopers = $developers->map(function ($developer) {
                $allTasks = $developer->tasks;
                $reportDate = now();
                
                $totalEarnings = $allTasks->sum(function ($task) use ($developer, $reportDate) {
                    $hoursWorked = $this->calculateHoursWorked($task, $reportDate);
                    return $hoursWorked * $developer->hour_value;
                });

                $completedTasks = $allTasks->where('status', 'done');
                $inProgressTasks = $allTasks->where('status', 'in_progress');

                return [
                    'id' => $developer->id,
                    'name' => $developer->name,
                    'email' => $developer->email,
                    'hour_value' => $developer->hour_value,
                    'completed_tasks' => $completedTasks->count(),
                    'in_progress_tasks' => $inProgressTasks->count(),
                    'total_tasks' => $allTasks->count(),
                    'total_hours' => $allTasks->sum(function ($task) use ($reportDate) {
                        return $this->calculateHoursWorked($task, $reportDate);
                    }),
                    'total_earnings' => $totalEarnings,
                    'tasks' => $allTasks->map(function ($task) use ($developer, $reportDate) {
                        $hoursWorked = $this->calculateHoursWorked($task, $reportDate);
                        return [
                            'name' => $task->name,
                            'project' => $task->sprint->project->name ?? 'N/A',
                            'status' => $task->status,
                            'hours' => $hoursWorked,
                            'earnings' => $hoursWorked * $developer->hour_value,
                            'completed_at' => $task->actual_finish,
                            'created_at' => $task->created_at,
                        ];
                    }),
                ];
            });

            $reportData = [
                'developers' => $mappedDevelopers,
                'totalEarnings' => $mappedDevelopers->sum('total_earnings'),
                'totalHours' => $mappedDevelopers->sum('total_hours'),
                'generated_at' => now()->format('Y-m-d H:i:s'),
                'period' => [
                    'start' => $request->start_date,
                    'end' => $request->end_date,
                ],
            ];

            // Generate Excel content with table formatting
            $filename = 'payment_report_' . date('Y-m-d_H-i-s') . '.xlsx';
            $csvContent = '';
            
            // BOM for UTF-8
            $csvContent .= chr(0xEF).chr(0xBB).chr(0xBF);
            
            // Report header with HTML formatting for Excel
            $csvContent .= $this->arrayToCsv(['PAYMENT REPORT - TASK TRACKING SYSTEM']) . "\n";
            $csvContent .= $this->arrayToCsv(['Generated on: ' . $reportData['generated_at']]) . "\n";
            $csvContent .= $this->arrayToCsv(['Period: ' . $request->start_date . ' to ' . $request->end_date]) . "\n";
            $csvContent .= $this->arrayToCsv([]) . "\n";
            
            // Developer summary with table formatting
            $csvContent .= $this->arrayToCsv(['DEVELOPER SUMMARY']) . "\n";
            $csvContent .= $this->arrayToCsv(['Developer', 'Email', 'Hourly Rate ($)', 'Completed Tasks', 'Estimated Hours', 'Actual Hours', 'Efficiency (%)', 'Total Earned ($)']) . "\n";
            
            foreach ($reportData['developers'] as $developer) {
                $efficiency = $developer['total_estimated_hours'] > 0 ? 
                    round((($developer['total_estimated_hours'] - $developer['total_hours']) / $developer['total_estimated_hours']) * 100, 1) : 0;
                
                // Aplicar formato condicional para eficiencia con símbolos
                $efficiencyFormatted = $efficiency > 0 ? "✓ {$efficiency}%" : ($efficiency < 0 ? "✗ {$efficiency}%" : "= {$efficiency}%");
                
                $csvContent .= $this->arrayToCsv([
                    $developer['name'],
                    $developer['email'],
                    '$' . number_format($developer['hour_value'], 2),
                    $developer['completed_tasks'],
                    number_format($developer['total_estimated_hours'], 2) . 'h',
                    number_format($developer['total_hours'], 2) . 'h',
                    $efficiencyFormatted,
                    '$' . number_format($developer['total_earnings'], 2)
                ]) . "\n";
            }
            
            // Separator line
            $csvContent .= $this->arrayToCsv([]) . "\n";
            $csvContent .= $this->arrayToCsv(['TASK DETAILS']) . "\n";
            $csvContent .= $this->arrayToCsv(['Developer', 'Task', 'Project', 'Estimated Hours', 'Actual Hours', 'Hourly Rate ($)', 'Task Payment ($)', 'Efficiency (%)', 'Completion Date']) . "\n";
            
            foreach ($reportData['developers'] as $developer) {
                foreach ($developer['tasks'] as $task) {
                    $efficiency = $task['estimated_hours'] > 0 ? 
                        round((($task['estimated_hours'] - $task['actual_hours']) / $task['estimated_hours']) * 100, 1) : 0;
                    
                    // Aplicar formato condicional para eficiencia con símbolos
                    $efficiencyFormatted = $efficiency > 0 ? "✓ {$efficiency}%" : ($efficiency < 0 ? "✗ {$efficiency}%" : "= {$efficiency}%");
                    
                    $csvContent .= $this->arrayToCsv([
                        $developer['name'],
                        $task['name'],
                        $task['project'],
                        number_format($task['estimated_hours'], 2) . 'h',
                        number_format($task['actual_hours'], 2) . 'h',
                        '$' . number_format($task['hour_value'], 2),
                        '$' . number_format($task['earnings'], 2),
                        $efficiencyFormatted,
                        $task['completed_at'] ? date('Y-m-d', strtotime($task['completed_at'])) : 'N/A'
                    ]) . "\n";
                }
            }
            
            // General summary
            $csvContent .= $this->arrayToCsv([]) . "\n";
            $csvContent .= $this->arrayToCsv(['GENERAL SUMMARY']) . "\n";
            $csvContent .= $this->arrayToCsv(['Total Developers', 'Total Tasks', 'Total Estimated Hours', 'Total Actual Hours', 'Average Efficiency (%)', 'Total Paid ($)']) . "\n";
            
            $totalEstimatedHours = $mappedDevelopers->sum('total_estimated_hours');
            $totalActualHours = $mappedDevelopers->sum('total_hours');
            $averageEfficiency = $totalEstimatedHours > 0 ? 
                round((($totalEstimatedHours - $totalActualHours) / $totalEstimatedHours) * 100, 1) : 0;
            $totalTasks = $mappedDevelopers->sum('completed_tasks');
            
            $csvContent .= $this->arrayToCsv([
                count($reportData['developers']),
                $totalTasks,
                number_format($totalEstimatedHours, 2) . 'h',
                number_format($totalActualHours, 2) . 'h',
                $averageEfficiency > 0 ? "✓ {$averageEfficiency}%" : ($averageEfficiency < 0 ? "✗ {$averageEfficiency}%" : "= {$averageEfficiency}%"),
                '$' . number_format($reportData['totalEarnings'], 2)
            ]) . "\n";
            
            // Additional notes with improved formatting
            $csvContent .= $this->arrayToCsv([]) . "\n";
            $csvContent .= $this->arrayToCsv(['IMPORTANT NOTES:']) . "\n";
            $csvContent .= $this->arrayToCsv(['✓ Positive efficiency: Completed before estimated time']) . "\n";
            $csvContent .= $this->arrayToCsv(['✗ Negative efficiency: Took longer than estimated']) . "\n";
            $csvContent .= $this->arrayToCsv(['= Neutral efficiency: Completed exactly in estimated time']) . "\n";
            $csvContent .= $this->arrayToCsv(['• This report includes both completed and in-progress tasks in the specified period']) . "\n";
            $csvContent .= $this->arrayToCsv(['• Monetary values are in US dollars']) . "\n";
            $csvContent .= $this->arrayToCsv(['• To apply filters in Excel: Select the entire table and use Data > Filter']) . "\n";
            $csvContent .= $this->arrayToCsv(['• For table format: Select the data and use Insert > Table']) . "\n";

            // Retornar descarga
            return response($csvContent)
                ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Cache-Control', 'no-cache, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 