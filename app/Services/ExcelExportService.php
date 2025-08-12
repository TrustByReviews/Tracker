<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;

class ExcelExportService
{
    /**
     * Generate payment report with table formatting
     */
    public function generatePaymentReport($developers, $startDate = null, $endDate = null)
    {
        $spreadsheet = new Spreadsheet();
        
        // Usar la hoja por defecto para el resumen
        $this->createSummarySheet($spreadsheet, $developers, $startDate, $endDate);

                // Create Details sheet
        $this->createDetailsSheet($spreadsheet, $developers, $startDate, $endDate);
        
        // Create Rework sheet
        $this->createReworkSheet($spreadsheet, $developers, $startDate, $endDate);
        
        // Create Statistics sheet
        $this->createStatisticsSheet($spreadsheet, $developers, $startDate, $endDate);

        return $spreadsheet;
    }

    /**
     * Create activity summary sheet
     */
    private function createActivitySummarySheet($sheet, $developers, $startDate, $endDate)
    {
        // Title
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'DEVELOPER ACTIVITY REPORT - TASK TRACKING SYSTEM');
        $this->styleTitle($sheet, 'A1:H1');

        // Report info
        $sheet->mergeCells('A3:H3');
        $sheet->setCellValue('A3', 'Generated on: ' . now()->format('Y-m-d H:i:s'));
        $this->styleSubtitle($sheet, 'A3:H3');

        $sheet->mergeCells('A4:H4');
        $period = $startDate && $endDate ? "Period: {$startDate} to {$endDate}" : "Period: All available data";
        $sheet->setCellValue('A4', $period);
        $this->styleSubtitle($sheet, 'A4:H4');

        // Summary table
        $sheet->setCellValue('A6', 'Developer');
        $sheet->setCellValue('B6', 'Total Sessions');
        $sheet->setCellValue('C6', 'Avg Session (min)');
        $sheet->setCellValue('D6', 'Task Activities');
        $sheet->setCellValue('E6', 'Preferred Time');
        $sheet->setCellValue('F6', 'Morning');
        $sheet->setCellValue('G6', 'Afternoon');
        $sheet->setCellValue('H6', 'Night');

        $this->styleHeader($sheet, 'A6:H6');

        $row = 7;
        foreach ($developers as $developer) {
            $sheet->setCellValue("A{$row}", $developer['developer']);
            $sheet->setCellValue("B{$row}", $developer['total_sessions']);
            $sheet->setCellValue("C{$row}", $developer['avg_session_duration_minutes']);
            $sheet->setCellValue("D{$row}", $developer['task_activities']);
            $sheet->setCellValue("E{$row}", $developer['preferred_work_time']);
            $sheet->setCellValue("F{$row}", $developer['activity_by_period']['morning'] ?? 0);
            $sheet->setCellValue("G{$row}", $developer['activity_by_period']['afternoon'] ?? 0);
            $sheet->setCellValue("H{$row}", $developer['activity_by_period']['night'] ?? 0);
            $row++;
        }

        $this->styleTable($sheet, "A6:H" . ($row - 1));
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(12);
    }

    /**
     * Create activity details sheet
     */
    private function createActivityDetailsSheet($sheet, $developers)
    {
        // Title
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'ACTIVITY DETAILS BY DEVELOPER');
        $this->styleTitle($sheet, 'A1:G1');

        // Headers
        $sheet->setCellValue('A3', 'Developer');
        $sheet->setCellValue('B3', 'Period');
        $sheet->setCellValue('C3', 'Total Sessions');
        $sheet->setCellValue('D3', 'Avg Session (min)');
        $sheet->setCellValue('E3', 'Task Activities');
        $sheet->setCellValue('F3', 'Preferred Time');
        $sheet->setCellValue('G3', 'Most Active Hours');

        $this->styleHeader($sheet, 'A3:G3');

        $row = 4;
        foreach ($developers as $developer) {
            $sheet->setCellValue("A{$row}", $developer['developer']);
            $sheet->setCellValue("B{$row}", $developer['period']['start'] . ' to ' . $developer['period']['end']);
            $sheet->setCellValue("C{$row}", $developer['total_sessions']);
            $sheet->setCellValue("D{$row}", $developer['avg_session_duration_minutes']);
            $sheet->setCellValue("E{$row}", $developer['task_activities']);
            $sheet->setCellValue("F{$row}", $developer['preferred_work_time']);
            
            $activeHours = [];
            foreach ($developer['most_active_hours'] as $hour => $count) {
                $activeHours[] = "{$hour}:00 ({$count})";
            }
            $sheet->setCellValue("G{$row}", implode(', ', $activeHours));
            $row++;
        }

        $this->styleTable($sheet, "A3:G" . ($row - 1));
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(18);
        $sheet->getColumnDimension('G')->setWidth(40);
    }

    /**
     * Create activity statistics sheet
     */
    private function createActivityStatisticsSheet($sheet, $developers)
    {
        // Title
        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A1', 'ACTIVITY STATISTICS');
        $this->styleTitle($sheet, 'A1:D1');

        // Team statistics
        $totalSessions = collect($developers)->sum('total_sessions');
        $avgSessionDuration = collect($developers)->avg('avg_session_duration_minutes');
        $totalTaskActivities = collect($developers)->sum('task_activities');

        $sheet->setCellValue('A3', 'Metric');
        $sheet->setCellValue('B3', 'Value');
        $sheet->setCellValue('C3', 'Description');

        $this->styleHeader($sheet, 'A3:C3');

        $stats = [
            ['Total Developers', count($developers), 'Number of developers in the report'],
            ['Total Sessions', $totalSessions, 'Total login sessions across all developers'],
            ['Average Session Duration', round($avgSessionDuration, 2) . ' min', 'Average session duration in minutes'],
            ['Total Task Activities', $totalTaskActivities, 'Total task-related activities (start, pause, resume, finish)'],
        ];

        $row = 4;
        foreach ($stats as $stat) {
            $sheet->setCellValue("A{$row}", $stat[0]);
            $sheet->setCellValue("B{$row}", $stat[1]);
            $sheet->setCellValue("C{$row}", $stat[2]);
            $row++;
        }

        $this->styleTable($sheet, "A3:C" . ($row - 1));
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(50);
    }

    /**
     * Generate activity report with table formatting
     */
    public function generateActivityReport($developers, $startDate = null, $endDate = null)
    {
        $spreadsheet = new Spreadsheet();
        
        // Configurar propiedades del documento
        $spreadsheet->getProperties()
            ->setCreator('Tracker System')
            ->setLastModifiedBy('Tracker System')
            ->setTitle('Payment Report')
            ->setSubject('Payment Report for Developers')
            ->setDescription('Detailed payment report with table formatting')
            ->setKeywords('payment report excel')
            ->setCategory('Reports');

        // Crear hoja de resumen
        $this->createSummarySheet($spreadsheet, $developers, $startDate, $endDate);
        
        // Crear hoja de detalles
        $this->createDetailsSheet($spreadsheet, $developers, $startDate, $endDate);
        
        // Crear hoja de estadísticas
        $this->createStatisticsSheet($spreadsheet, $developers, $startDate, $endDate);

        // Generar archivo
        $writer = new Xlsx($spreadsheet);
        $filename = 'payment_report_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        // Guardar en memoria
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();
        
        return [
            'content' => $content,
            'filename' => $filename
        ];
    }

    /**
     * Create developer summary sheet
     */
    private function createSummarySheet($spreadsheet, $developers, $startDate, $endDate)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Developer Summary');
        
        // Configure column widths
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(18);
        $sheet->getColumnDimension('G')->setWidth(18);
        $sheet->getColumnDimension('H')->setWidth(20);
        $sheet->getColumnDimension('I')->setWidth(20);

        // Report title
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'PAYMENT REPORT - TASK TRACKING SYSTEM');
        $this->styleTitle($sheet, 'A1:I1');

        // Report information
        $sheet->mergeCells('A2:I2');
        $sheet->setCellValue('A2', 'Generated on: ' . now()->format('Y-m-d H:i:s'));
        $this->styleSubtitle($sheet, 'A2:I2');

        $sheet->mergeCells('A3:I3');
        $periodText = $startDate && $endDate ? 
            "Period: {$startDate} to {$endDate}" : 
            "Period: All available data";
        $sheet->setCellValue('A3', $periodText);
        $this->styleSubtitle($sheet, 'A3:I3');

        // Table headers
        $headers = [
            'Developer',
            'Role',
            'Email',
            'Hourly Rate ($)',
            'Completed Tasks',
            'Rework Items',
            'Estimated Hours',
            'Actual Hours',
            'Efficiency (%)',
            'Total Earned ($)'
        ];

        $sheet->fromArray($headers, null, 'A5');
        $this->styleTableHeader($sheet, 'A5:J5');

        // Developer data
        $row = 6;
        foreach ($developers as $developer) {
            $completedTasks = $developer['tasks'] ?? [];
            $reworkTasks = $developer['rework_tasks'] ?? [];
            $reworkBugs = $developer['rework_bugs'] ?? [];
            $totalReworkItems = count($reworkTasks) + count($reworkBugs);
            
            $totalEstimatedHours = collect($completedTasks)->sum('estimated_hours');
            $totalActualHours = collect($completedTasks)->sum('actual_hours');
            $efficiency = $totalEstimatedHours > 0 ? 
                round((($totalEstimatedHours - $totalActualHours) / $totalEstimatedHours) * 100, 1) : 0;

            $sheet->setCellValue("A{$row}", $developer['name']);
            $sheet->setCellValue("B{$row}", $developer['role'] ?? 'Developer');
            $sheet->setCellValue("C{$row}", $developer['email']);
            $sheet->setCellValue("D{$row}", $developer['hour_value']);
            $sheet->setCellValue("E{$row}", count($completedTasks));
            $sheet->setCellValue("F{$row}", $totalReworkItems);
            $sheet->setCellValue("G{$row}", $totalEstimatedHours);
            $sheet->setCellValue("H{$row}", $totalActualHours);
            $sheet->setCellValue("I{$row}", $efficiency);
            $sheet->setCellValue("J{$row}", $developer['total_earnings']);

            // Apply currency format
            $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('$#,##0.00');
            $sheet->getStyle("J{$row}")->getNumberFormat()->setFormatCode('$#,##0.00');

            // Apply percentage format
            $sheet->getStyle("I{$row}")->getNumberFormat()->setFormatCode('0.0%');

            // Apply conditional formatting for efficiency
            if ($efficiency > 0) {
                $sheet->getStyle("I{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D4EDDA');
                $sheet->getStyle("I{$row}")->getFont()->setColor(new Color('155724'));
            } elseif ($efficiency < 0) {
                $sheet->getStyle("I{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8D7DA');
                $sheet->getStyle("I{$row}")->getFont()->setColor(new Color('721C24'));
            }

            // Apply conditional formatting for rework items
            if ($totalReworkItems > 0) {
                $sheet->getStyle("F{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF3CD');
                $sheet->getStyle("F{$row}")->getFont()->setColor(new Color('856404'));
            }

            $row++;
        }

        // Apply table format
        $this->styleTable($sheet, "A5:J" . ($row - 1));

        // Add filters
        $sheet->setAutoFilter("A5:J5");
    }

    /**
     * Create task details sheet
     */
    private function createDetailsSheet($spreadsheet, $developers, $startDate, $endDate)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Task Details');
        
        // Configure column widths
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(18);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(20);
        $sheet->getColumnDimension('I')->setWidth(20);
        $sheet->getColumnDimension('J')->setWidth(15);
        $sheet->getColumnDimension('K')->setWidth(20);

        // Report title
        $sheet->mergeCells('A1:K1');
        $sheet->setCellValue('A1', 'TASK DETAILS');
        $this->styleTitle($sheet, 'A1:K1');

        // Table headers
        $headers = [
            'Developer',
            'Task',
            'Project',
            'Type',
            'Estimated Hours',
            'Actual Hours',
            'Hourly Rate ($)',
            'Task Earnings ($)',
            'Efficiency (%)',
            'Completed Date',
            'QA Tester'
        ];

        $sheet->fromArray($headers, null, 'A3');
        $this->styleTableHeader($sheet, 'A3:K3');

        // Task data
        $row = 4;
        foreach ($developers as $developer) {
            $completedTasks = $developer['tasks'] ?? [];
            
            foreach ($completedTasks as $task) {
                // For QA tasks, there are no estimated hours, only actual hours
                $taskType = $task['type'] ?? 'Task';
                $isQATask = strpos($taskType, 'QA') !== false;
                
                if ($isQATask) {
                    // For QA, there is no efficiency because there is no estimation
                    $efficiency = 'N/A';
                } else {
                    $efficiency = $task['estimated_hours'] > 0 ? 
                        round((($task['estimated_hours'] - $task['actual_hours']) / $task['estimated_hours']) * 100, 1) : 0;
                }

                // Determine the QA tester
                $qaTester = '';
                if ($isQATask) {
                    $qaTester = $developer['name']; // The developer who is doing QA
                }

                $sheet->setCellValue("A{$row}", $developer['name']);
                $sheet->setCellValue("B{$row}", $task['name']);
                $sheet->setCellValue("C{$row}", $task['project']);
                $sheet->setCellValue("D{$row}", $taskType);
                $sheet->setCellValue("E{$row}", $isQATask ? 'N/A' : $task['estimated_hours']);
                $sheet->setCellValue("F{$row}", $task['actual_hours']);
                $sheet->setCellValue("G{$row}", $developer['hour_value']);
                $sheet->setCellValue("H{$row}", $task['earnings']);
                $sheet->setCellValue("I{$row}", $efficiency);
                $sheet->setCellValue("J{$row}", $task['completed_at'] ? date('Y-m-d', strtotime($task['completed_at'])) : 'N/A');
                $sheet->setCellValue("K{$row}", $qaTester);

                // Apply currency format
                $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('$#,##0.00');
                $sheet->getStyle("H{$row}")->getNumberFormat()->setFormatCode('$#,##0.00');

                // Apply percentage format only if not QA
                if (!$isQATask) {
                    $sheet->getStyle("I{$row}")->getNumberFormat()->setFormatCode('0.0%');

                    // Apply conditional formatting for efficiency
                    if ($efficiency > 0) {
                        $sheet->getStyle("I{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D4EDDA');
                        $sheet->getStyle("I{$row}")->getFont()->setColor(new Color('155724'));
                    } elseif ($efficiency < 0) {
                        $sheet->getStyle("I{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8D7DA');
                        $sheet->getStyle("I{$row}")->getFont()->setColor(new Color('721C24'));
                    }
                }

                $row++;
            }
        }

        // Apply table format
        $this->styleTable($sheet, "A3:K" . ($row - 1));

        // Add filters
        $sheet->setAutoFilter("A3:K3");
    }

    /**
     * Create rework details sheet
     */
    private function createReworkSheet($spreadsheet, $developers, $startDate, $endDate)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Rework Details');
        
        // Configure column widths
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(25);
        $sheet->getColumnDimension('F')->setWidth(18);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(20);
        $sheet->getColumnDimension('I')->setWidth(20);
        $sheet->getColumnDimension('J')->setWidth(20);
        $sheet->getColumnDimension('K')->setWidth(20);
        $sheet->getColumnDimension('L')->setWidth(30);

        // Report title
        $sheet->mergeCells('A1:L1');
        $sheet->setCellValue('A1', 'REWORK DETAILS');
        $this->styleTitle($sheet, 'A1:L1');

        // Table headers
        $headers = [
            'Developer',
            'Task/Bug',
            'Project',
            'Type',
            'Rework Type',
            'Rework Hours',
            'Hourly Rate ($)',
            'Rework Earnings ($)',
            'Original Completion',
            'Rework Date',
            'Status',
            'Rework Reason'
        ];

        $sheet->fromArray($headers, null, 'A3');
        $this->styleTableHeader($sheet, 'A3:L3');

        // Rework data
        $row = 4;
        foreach ($developers as $developer) {
            $reworkTasks = $developer['rework_tasks'] ?? [];
            $reworkBugs = $developer['rework_bugs'] ?? [];
            
            // Process rework tasks
            foreach ($reworkTasks as $task) {
                $sheet->setCellValue("A{$row}", $developer['name']);
                $sheet->setCellValue("B{$row}", $task['name']);
                $sheet->setCellValue("C{$row}", $task['project']);
                $sheet->setCellValue("D{$row}", 'Task');
                $sheet->setCellValue("E{$row}", $task['rework_type']);
                $sheet->setCellValue("F{$row}", $task['hours']);
                $sheet->setCellValue("G{$row}", $developer['hour_value']);
                $sheet->setCellValue("H{$row}", $task['payment']);
                $sheet->setCellValue("I{$row}", $task['original_completion'] ? date('Y-m-d', strtotime($task['original_completion'])) : 'N/A');
                $sheet->setCellValue("J{$row}", $task['rework_date'] ? date('Y-m-d', strtotime($task['rework_date'])) : 'N/A');
                $sheet->setCellValue("K{$row}", 'Completed');
                $sheet->setCellValue("L{$row}", $task['rework_reason']);

                // Apply currency format
                $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('$#,##0.00');
                $sheet->getStyle("H{$row}")->getNumberFormat()->setFormatCode('$#,##0.00');

                // Apply conditional formatting for rework type
                if (strpos($task['rework_type'], 'QA') !== false) {
                    $sheet->getStyle("E{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8D7DA');
                    $sheet->getStyle("E{$row}")->getFont()->setColor(new Color('721C24'));
                } elseif (strpos($task['rework_type'], 'Team Leader') !== false) {
                    $sheet->getStyle("E{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF3CD');
                    $sheet->getStyle("E{$row}")->getFont()->setColor(new Color('856404'));
                }

                $row++;
            }
            
            // Process rework bugs
            foreach ($reworkBugs as $bug) {
                $sheet->setCellValue("A{$row}", $developer['name']);
                $sheet->setCellValue("B{$row}", $bug['name']);
                $sheet->setCellValue("C{$row}", $bug['project']);
                $sheet->setCellValue("D{$row}", 'Bug');
                $sheet->setCellValue("E{$row}", $bug['rework_type']);
                $sheet->setCellValue("F{$row}", $bug['hours']);
                $sheet->setCellValue("G{$row}", $developer['hour_value']);
                $sheet->setCellValue("H{$row}", $bug['payment']);
                $sheet->setCellValue("I{$row}", $bug['original_completion'] ? date('Y-m-d', strtotime($bug['original_completion'])) : 'N/A');
                $sheet->setCellValue("J{$row}", $bug['rework_date'] ? date('Y-m-d', strtotime($bug['rework_date'])) : 'N/A');
                $sheet->setCellValue("K{$row}", 'Completed');
                $sheet->setCellValue("L{$row}", $bug['rework_reason']);

                // Apply currency format
                $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('$#,##0.00');
                $sheet->getStyle("H{$row}")->getNumberFormat()->setFormatCode('$#,##0.00');

                // Apply conditional formatting for rework type
                if (strpos($bug['rework_type'], 'QA') !== false) {
                    $sheet->getStyle("E{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8D7DA');
                    $sheet->getStyle("E{$row}")->getFont()->setColor(new Color('721C24'));
                } elseif (strpos($bug['rework_type'], 'Team Leader') !== false) {
                    $sheet->getStyle("E{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF3CD');
                    $sheet->getStyle("E{$row}")->getFont()->setColor(new Color('856404'));
                }

                $row++;
            }
        }

        // Apply table format
        $this->styleTable($sheet, "A3:L" . ($row - 1));

        // Add filters
        $sheet->setAutoFilter("A3:L3");
    }

    /**
     * Create statistics sheet
     */
    private function createStatisticsSheet($spreadsheet, $developers, $startDate, $endDate)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Statistics');
        
        // Configure column widths
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(20);

        // Report title
        $sheet->mergeCells('A1:B1');
        $sheet->setCellValue('A1', 'GENERAL STATISTICS');
        $this->styleTitle($sheet, 'A1:B1');

        // Calculate statistics
        $totalDevelopers = count($developers);
        $totalTasks = collect($developers)->sum(function($dev) {
            return count($dev['tasks'] ?? []);
        });
        $totalReworkItems = collect($developers)->sum(function($dev) {
            return count($dev['rework_tasks'] ?? []) + count($dev['rework_bugs'] ?? []);
        });
        $totalEstimatedHours = collect($developers)->sum(function($dev) {
            return collect($dev['tasks'] ?? [])->sum('estimated_hours');
        });
        $totalActualHours = collect($developers)->sum(function($dev) {
            return collect($dev['tasks'] ?? [])->sum('actual_hours');
        });
        $totalEarnings = collect($developers)->sum('total_earnings');
        $averageEfficiency = $totalEstimatedHours > 0 ? 
            round((($totalEstimatedHours - $totalActualHours) / $totalEstimatedHours) * 100, 1) : 0;

        // Statistics data
        $stats = [
            ['Total Developers', $totalDevelopers],
            ['Total Completed Tasks', $totalTasks],
            ['Total Rework Items', $totalReworkItems],
            ['Total Estimated Hours', $totalEstimatedHours],
            ['Total Actual Hours', $totalActualHours],
            ['Average Efficiency (%)', $averageEfficiency],
            ['Total Paid ($)', $totalEarnings],
        ];

        $sheet->fromArray($stats, null, 'A3');
        
        // Apply format
        $sheet->getStyle("A3:A9")->getFont()->setBold(true);
        $sheet->getStyle("B3:B9")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        
        // Currency format for total paid
        $sheet->getStyle("B9")->getNumberFormat()->setFormatCode('$#,##0.00');
        
        // Percentage format for efficiency
        $sheet->getStyle("B8")->getNumberFormat()->setFormatCode('0.0%');

        // Apply table format
        $this->styleTable($sheet, "A3:B9");

        // Create developer efficiency chart
        $this->createEfficiencyChart($sheet, $developers);
    }

    /**
     * Create efficiency chart
     */
    private function createEfficiencyChart($sheet, $developers)
    {
        // Prepare data for the chart
        $chartData = [];
        $row = 10;
        
        $sheet->setCellValue("A{$row}", 'Developer Efficiency Chart');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
        $row += 2;

        // Chart headers
        $sheet->setCellValue("A{$row}", 'Developer');
        $sheet->setCellValue("B{$row}", 'Efficiency (%)');
        $sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);
        $row++;

        // Chart data
        foreach ($developers as $developer) {
            $completedTasks = $developer['tasks'] ?? [];
            $totalEstimatedHours = collect($completedTasks)->sum('estimated_hours');
            $totalActualHours = collect($completedTasks)->sum('actual_hours');
            $efficiency = $totalEstimatedHours > 0 ? 
                round((($totalEstimatedHours - $totalActualHours) / $totalEstimatedHours) * 100, 1) : 0;

            $sheet->setCellValue("A{$row}", $developer['name']);
            $sheet->setCellValue("B{$row}", $efficiency);
            $row++;
        }

        // Create chart
        $dataSeriesLabels = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Statistics!$B$' . ($row - count($developers)), null, 1),
        ];
        
        $xAxisTickValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Statistics!$A$' . ($row - count($developers)) . ':$A$' . ($row - 1), null, count($developers)),
        ];
        
        $dataSeriesValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'Statistics!$B$' . ($row - count($developers)) . ':$B$' . ($row - 1), null, count($developers)),
        ];

        $series = new DataSeries(
            DataSeries::TYPE_BARCHART,
            DataSeries::GROUPING_STANDARD,
            range(0, count($dataSeriesValues) - 1),
            $dataSeriesLabels,
            $xAxisTickValues,
            $dataSeriesValues
        );

        $plot = new PlotArea(null, [$series]);
        $legend = new Legend(Legend::POSITION_RIGHT, null, false);
        $title = new Title('Efficiency by Developer');

        $chart = new Chart(
            'chart1',
            $title,
            $legend,
            $plot
        );

        $chart->setTopLeftPosition('D10');
        $chart->setBottomRightPosition('K25');

        $sheet->addChart($chart);
    }

    /**
     * Apply title style
     */
    private function styleTitle($sheet, $range)
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => '2C3E50'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'ECF0F1'],
            ],
        ]);
    }

    /**
     * Apply subtitle style
     */
    private function styleSubtitle($sheet, $range)
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'size' => 12,
                'color' => ['rgb' => '7F8C8D'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);
    }

    /**
     * Apply table header style
     */
    private function styleTableHeader($sheet, $range)
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '3498DB'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '2980B9'],
                ],
            ],
        ]);
    }

    /**
     * Apply table style
     */
    private function styleTable($sheet, $range)
    {
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'BDC3C7'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Alternate row colors
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        
        for ($row = 6; $row <= $highestRow; $row++) {
            if ($row % 2 == 0) {
                $sheet->getStyle("A{$row}:{$highestColumn}{$row}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F8F9FA');
            }
        }
    }
} 