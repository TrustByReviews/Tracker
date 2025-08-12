<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Task Details Export Class
 * 
 * This class handles the export of detailed task information to Excel format.
 * It provides granular data about individual tasks including time tracking,
 * payment calculations, and efficiency metrics for each developer.
 * 
 * Features:
 * - Exports detailed task-by-task data
 * - Includes time tracking information
 * - Calculates task-specific payments
 * - Provides efficiency metrics per task
 * - Supports custom sheet titles
 * 
 * @package App\Exports
 * @author System
 * @version 1.0
 */
class TaskDetailsExport implements FromArray, WithHeadings, WithTitle
{
    /**
     * The task details data to be exported
     * 
     * @var array
     */
    protected $data;

    /**
     * The title for the Excel sheet
     * 
     * @var string
     */
    protected $title;

    /**
     * Constructor
     * 
     * Initializes the export with task details data and optional sheet title
     * 
     * @param array $data Task details data array containing individual task information
     * @param string $title Optional sheet title (defaults to 'Task Details')
     */
    public function __construct($data, $title = 'Task Details')
    {
        $this->data = $data;
        $this->title = $title;
    }

    /**
     * Get the data array for Excel export
     * 
     * This method is required by the FromArray interface.
     * Returns the task details data that will be written to the Excel file.
     * 
     * @return array The task details data to export
     */
    public function array(): array
    {
        return $this->data;
    }

    /**
     * Get the column headers for the Excel file
     * 
     * This method is required by the WithHeadings interface.
     * Defines the column structure and labels for the task details report.
     * 
     * @return array Array of column headers
     */
    public function headings(): array
    {
        return [
            'Developer',        // Developer name assigned to the task
            'Task',            // Task name or description
            'Project',         // Project name the task belongs to
            'Estimated Hours', // Original time estimate for the task
            'Actual Hours',    // Actual time spent on the task
            'Hourly Rate ($)', // Developer's hourly rate for this task
            'Task Payment ($)', // Payment earned for this specific task
            'Efficiency (%)',  // Efficiency percentage for this task
            'Completion Date'  // Date when the task was completed
        ];
    }

    /**
     * Get the sheet title for the Excel file
     * 
     * This method is required by the WithTitle interface.
     * Sets the name of the worksheet in the Excel file.
     * 
     * @return string The sheet title
     */
    public function title(): string
    {
        return $this->title;
    }
} 