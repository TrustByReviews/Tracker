<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PaymentReportMultiSheet implements WithMultipleSheets
{
    protected $developerData;
    protected $taskData;

    public function __construct($developerData, $taskData)
    {
        $this->developerData = $developerData;
        $this->taskData = $taskData;
    }

    public function sheets(): array
    {
        return [
            'Developer Summary' => new PaymentReportExport($this->developerData, 'Developer Summary'),
            'Task Details' => new TaskDetailsExport($this->taskData, 'Task Details')
        ];
    }
} 