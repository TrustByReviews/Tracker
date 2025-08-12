<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Payment Report Export Class
 * 
 * This class handles the export of payment reports to Excel format.
 * It implements the Laravel Excel package interfaces to generate
 * structured Excel files with payment data for developers.
 * 
 * Features:
 * - Exports developer payment data to Excel
 * - Includes efficiency calculations
 * - Supports custom sheet titles
 * - Provides standardized column headers
 * 
 * @package App\Exports
 * @author System
 * @version 1.0
 */
class PaymentReportExport implements FromArray, WithHeadings, WithTitle
{
    /**
     * The payment data to be exported
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
     * Initializes the export with payment data and optional sheet title
     * 
     * @param array $data Payment data array containing developer information
     * @param string $title Optional sheet title (defaults to 'Payment Report')
     */
    public function __construct($data, $title = 'Payment Report')
    {
        $this->data = $data;
        $this->title = $title;
    }

    /**
     * Get the data array for Excel export
     * 
     * This method is required by the FromArray interface.
     * Returns the payment data that will be written to the Excel file.
     * 
     * @return array The payment data to export
     */
    public function array(): array
    {
        return $this->data;
    }

    /**
     * Get the column headers for the Excel file
     * 
     * This method is required by the WithHeadings interface.
     * Defines the column structure and labels for the payment report.
     * 
     * @return array Array of column headers
     */
    public function headings(): array
    {
        return [
            'Developer',        // Developer name
            'Email',           // Developer email address
            'Hourly Rate ($)', // Developer's hourly rate in USD
            'Completed Tasks', // Number of completed tasks
            'Estimated Hours', // Total estimated hours for completed tasks
            'Actual Hours',    // Total actual hours spent on tasks
            'Efficiency (%)',  // Efficiency percentage (estimated vs actual)
            'Total Earned ($)' // Total payment earned
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