<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Payment Report Multi-Sheet Export Class
 * 
 * This class creates comprehensive Excel reports with multiple worksheets.
 * It combines developer summary data and detailed task information into
 * a single Excel file with organized sheets for better data analysis.
 * 
 * Features:
 * - Creates multi-sheet Excel reports
 * - Combines summary and detailed views
 * - Provides organized data structure
 * - Supports comprehensive payment analysis
 * 
 * @package App\Exports
 * @author System
 * @version 1.0
 */
class PaymentReportMultiSheet implements WithMultipleSheets
{
    /**
     * Developer summary data for the first sheet
     * 
     * @var array
     */
    protected $developerData;

    /**
     * Detailed task data for the second sheet
     * 
     * @var array
     */
    protected $taskData;

    /**
     * Constructor
     * 
     * Initializes the multi-sheet export with both developer summary
     * and detailed task data
     * 
     * @param array $developerData Summary data aggregated by developer
     * @param array $taskData Detailed task-by-task data
     */
    public function __construct($developerData, $taskData)
    {
        $this->developerData = $developerData;
        $this->taskData = $taskData;
    }

    /**
     * Define the sheets for the Excel file
     * 
     * This method is required by the WithMultipleSheets interface.
     * Creates two worksheets: one for developer summary and another
     * for detailed task information.
     * 
     * @return array Array of sheet configurations
     */
    public function sheets(): array
    {
        return [
            'Developer Summary' => new PaymentReportExport($this->developerData, 'Developer Summary'),
            'Task Details' => new TaskDetailsExport($this->taskData, 'Task Details')
        ];
    }
} 