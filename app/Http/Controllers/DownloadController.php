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
     * Download Excel report with table formatting using PhpSpreadsheet
     */
    public function downloadExcel(Request $request)
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
            ->get()
            ->map(function ($developer) {
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
                            'estimated_hours' => $task->estimated_hours ?? 0,
                            'actual_hours' => $hoursWorked,
                            'hour_value' => $developer->hour_value,
                            'earnings' => $hoursWorked * $developer->hour_value,
                            'completed_at' => $task->actual_finish,
                            'created_at' => $task->created_at,
                        ];
                    }),
                ];
            });

            // Generate Excel report with table formatting
            $result = $this->excelExportService->generatePaymentReport(
                $developers->toArray(),
                $request->start_date,
                $request->end_date
            );

            // Retornar descarga
            return response($result['content'])
                ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                ->header('Content-Disposition', 'attachment; filename="' . $result['filename'] . '"')
                ->header('Content-Length', strlen($result['content']))
                ->header('Cache-Control', 'no-cache, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Download PDF report
     */
    public function downloadPDF(Request $request)
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
                    'total_estimated_hours' => $allTasks->sum('estimated_hours'),
                    'total_earnings' => $totalEarnings,
                    'tasks' => $allTasks->map(function ($task) use ($developer, $reportDate) {
                        $hoursWorked = $this->calculateHoursWorked($task, $reportDate);
                        return [
                            'name' => $task->name,
                            'project' => $task->sprint->project->name ?? 'N/A',
                            'status' => $task->status,
                            'estimated_hours' => $task->estimated_hours ?? 0,
                            'actual_hours' => $hoursWorked,
                            'hour_value' => $developer->hour_value,
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

            // Generate PDF using DomPDF
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.payment', $reportData);
            
            // Configure PDF
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'Arial'
            ]);

            // Generate PDF content
            $filename = 'payment_report_' . date('Y-m-d_H-i-s') . '.pdf';
            $pdfContent = $pdf->output();

            // Retornar descarga
            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Content-Length', strlen($pdfContent))
                ->header('Cache-Control', 'no-cache, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
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