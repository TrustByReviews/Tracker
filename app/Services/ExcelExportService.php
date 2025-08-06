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
        $spreadsheet->removeSheetByIndex(0); // Remove default sheet

        // Create Summary sheet
        $summarySheet = $spreadsheet->createSheet();
        $summarySheet->setTitle('Summary');
        $this->createSummarySheet($summarySheet, $developers, $startDate, $endDate);

        // Create Details sheet
        $detailsSheet = $spreadsheet->createSheet();
        $detailsSheet->setTitle('Details');
        $this->createDetailsSheet($detailsSheet, $developers);

        // Create Statistics sheet
        $statsSheet = $spreadsheet->createSheet();
        $statsSheet->setTitle('Statistics');
        $this->createStatisticsSheet($statsSheet, $developers);

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
     * Crear hoja de resumen por desarrollador
     */
    private function createSummarySheet($spreadsheet, $developers, $startDate, $endDate)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Resumen por Desarrollador');
        
        // Configurar ancho de columnas
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(18);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getColumnDimension('H')->setWidth(20);

        // Título del reporte
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'PAYMENT REPORT - TASK TRACKING SYSTEM');
        $this->styleTitle($sheet, 'A1:H1');

        // Información del reporte
        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A2', 'Generado el: ' . now()->format('Y-m-d H:i:s'));
        $this->styleSubtitle($sheet, 'A2:H2');

        $sheet->mergeCells('A3:H3');
        $periodText = $startDate && $endDate ? 
            "Período: {$startDate} a {$endDate}" : 
            "Período: Todos los datos disponibles";
        $sheet->setCellValue('A3', $periodText);
        $this->styleSubtitle($sheet, 'A3:H3');

        // Encabezados de tabla
        $headers = [
            'Desarrollador',
            'Email',
            'Valor/Hora ($)',
            'Tareas Completadas',
            'Horas Estimadas',
            'Horas Reales',
            'Eficiencia (%)',
            'Total Ganado ($)'
        ];

        $sheet->fromArray($headers, null, 'A5');
        $this->styleTableHeader($sheet, 'A5:H5');

        // Datos de desarrolladores
        $row = 6;
        foreach ($developers as $developer) {
            $completedTasks = $developer['tasks'] ?? [];
            $totalEstimatedHours = collect($completedTasks)->sum('estimated_hours');
            $totalActualHours = collect($completedTasks)->sum('actual_hours');
            $efficiency = $totalEstimatedHours > 0 ? 
                round((($totalEstimatedHours - $totalActualHours) / $totalEstimatedHours) * 100, 1) : 0;

            $sheet->setCellValue("A{$row}", $developer['name']);
            $sheet->setCellValue("B{$row}", $developer['email']);
            $sheet->setCellValue("C{$row}", $developer['hour_value']);
            $sheet->setCellValue("D{$row}", count($completedTasks));
            $sheet->setCellValue("E{$row}", $totalEstimatedHours);
            $sheet->setCellValue("F{$row}", $totalActualHours);
            $sheet->setCellValue("G{$row}", $efficiency);
            $sheet->setCellValue("H{$row}", $developer['total_earnings']);

            // Aplicar formato de moneda
            $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('$#,##0.00');
            $sheet->getStyle("H{$row}")->getNumberFormat()->setFormatCode('$#,##0.00');

            // Aplicar formato de porcentaje
            $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('0.0%');

            // Aplicar formato condicional para eficiencia
            if ($efficiency > 0) {
                $sheet->getStyle("G{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D4EDDA');
                $sheet->getStyle("G{$row}")->getFont()->setColor(new Color('155724'));
            } elseif ($efficiency < 0) {
                $sheet->getStyle("G{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8D7DA');
                $sheet->getStyle("G{$row}")->getFont()->setColor(new Color('721C24'));
            }

            $row++;
        }

        // Aplicar formato de tabla
        $this->styleTable($sheet, "A5:H" . ($row - 1));

        // Agregar filtros
        $sheet->setAutoFilter("A5:H5");
    }

    /**
     * Crear hoja de detalles por tarea
     */
    private function createDetailsSheet($spreadsheet, $developers, $startDate, $endDate)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Detalles por Tarea');
        
        // Configurar ancho de columnas
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getColumnDimension('H')->setWidth(20);
        $sheet->getColumnDimension('I')->setWidth(15);

        // Título del reporte
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'DETALLES POR TAREA');
        $this->styleTitle($sheet, 'A1:I1');

        // Encabezados de tabla
        $headers = [
            'Desarrollador',
            'Tarea',
            'Proyecto',
            'Horas Estimadas',
            'Horas Reales',
            'Valor/Hora ($)',
            'Pago por Tarea ($)',
            'Eficiencia (%)',
            'Fecha Completada'
        ];

        $sheet->fromArray($headers, null, 'A3');
        $this->styleTableHeader($sheet, 'A3:I3');

        // Datos de tareas
        $row = 4;
        foreach ($developers as $developer) {
            $completedTasks = $developer['tasks'] ?? [];
            
            foreach ($completedTasks as $task) {
                $efficiency = $task['estimated_hours'] > 0 ? 
                    round((($task['estimated_hours'] - $task['actual_hours']) / $task['estimated_hours']) * 100, 1) : 0;

                $sheet->setCellValue("A{$row}", $developer['name']);
                $sheet->setCellValue("B{$row}", $task['name']);
                $sheet->setCellValue("C{$row}", $task['project']);
                $sheet->setCellValue("D{$row}", $task['estimated_hours']);
                $sheet->setCellValue("E{$row}", $task['actual_hours']);
                $sheet->setCellValue("F{$row}", $developer['hour_value']);
                $sheet->setCellValue("G{$row}", $task['earnings']);
                $sheet->setCellValue("H{$row}", $efficiency);
                $sheet->setCellValue("I{$row}", $task['completed_at'] ? date('Y-m-d', strtotime($task['completed_at'])) : 'N/A');

                // Aplicar formato de moneda
                $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode('$#,##0.00');
                $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('$#,##0.00');

                // Aplicar formato de porcentaje
                $sheet->getStyle("H{$row}")->getNumberFormat()->setFormatCode('0.0%');

                // Aplicar formato condicional para eficiencia
                if ($efficiency > 0) {
                    $sheet->getStyle("H{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D4EDDA');
                    $sheet->getStyle("H{$row}")->getFont()->setColor(new Color('155724'));
                } elseif ($efficiency < 0) {
                    $sheet->getStyle("H{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8D7DA');
                    $sheet->getStyle("H{$row}")->getFont()->setColor(new Color('721C24'));
                }

                $row++;
            }
        }

        // Aplicar formato de tabla
        $this->styleTable($sheet, "A3:I" . ($row - 1));

        // Agregar filtros
        $sheet->setAutoFilter("A3:I3");
    }

    /**
     * Crear hoja de estadísticas
     */
    private function createStatisticsSheet($spreadsheet, $developers, $startDate, $endDate)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Estadísticas');
        
        // Configurar ancho de columnas
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(20);

        // Título del reporte
        $sheet->mergeCells('A1:B1');
        $sheet->setCellValue('A1', 'ESTADÍSTICAS GENERALES');
        $this->styleTitle($sheet, 'A1:B1');

        // Calcular estadísticas
        $totalDevelopers = count($developers);
        $totalTasks = collect($developers)->sum(function($dev) {
            return count($dev['tasks'] ?? []);
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

        // Datos de estadísticas
        $stats = [
            ['Total Desarrolladores', $totalDevelopers],
            ['Total Tareas Completadas', $totalTasks],
            ['Total Horas Estimadas', $totalEstimatedHours],
            ['Total Horas Reales', $totalActualHours],
            ['Eficiencia Promedio (%)', $averageEfficiency],
            ['Total Pagado ($)', $totalEarnings],
        ];

        $sheet->fromArray($stats, null, 'A3');
        
        // Aplicar formato
        $sheet->getStyle("A3:A8")->getFont()->setBold(true);
        $sheet->getStyle("B3:B8")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        
        // Formato de moneda para total pagado
        $sheet->getStyle("B8")->getNumberFormat()->setFormatCode('$#,##0.00');
        
        // Formato de porcentaje para eficiencia
        $sheet->getStyle("B7")->getNumberFormat()->setFormatCode('0.0%');

        // Aplicar formato de tabla
        $this->styleTable($sheet, "A3:B8");

        // Crear gráfico de eficiencia por desarrollador
        $this->createEfficiencyChart($sheet, $developers);
    }

    /**
     * Crear gráfico de eficiencia
     */
    private function createEfficiencyChart($sheet, $developers)
    {
        // Preparar datos para el gráfico
        $chartData = [];
        $row = 10;
        
        $sheet->setCellValue("A{$row}", 'Gráfico de Eficiencia por Desarrollador');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
        $row += 2;

        // Encabezados del gráfico
        $sheet->setCellValue("A{$row}", 'Desarrollador');
        $sheet->setCellValue("B{$row}", 'Eficiencia (%)');
        $sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);
        $row++;

        // Datos del gráfico
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

        // Crear gráfico
        $dataSeriesLabels = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Estadísticas!$B$' . ($row - count($developers)), null, 1),
        ];
        
        $xAxisTickValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Estadísticas!$A$' . ($row - count($developers)) . ':$A$' . ($row - 1), null, count($developers)),
        ];
        
        $dataSeriesValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'Estadísticas!$B$' . ($row - count($developers)) . ':$B$' . ($row - 1), null, count($developers)),
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
        $title = new Title('Eficiencia por Desarrollador');

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
     * Aplicar estilo de título
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
     * Aplicar estilo de subtítulo
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
     * Aplicar estilo de encabezado de tabla
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
     * Aplicar estilo de tabla
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

        // Alternar colores de filas
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