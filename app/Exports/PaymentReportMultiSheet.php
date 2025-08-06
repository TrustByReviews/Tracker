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
            'Resumen por Desarrollador' => new PaymentReportExport($this->developerData, 'Resumen por Desarrollador'),
            'Detalles por Tarea' => new TaskDetailsExport($this->taskData, 'Detalles por Tarea')
        ];
    }
} 