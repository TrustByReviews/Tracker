<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class PaymentReportExport implements FromArray, WithHeadings, WithTitle
{
    protected $data;
    protected $title;

    public function __construct($data, $title = 'Reporte de Pagos')
    {
        $this->data = $data;
        $this->title = $title;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Desarrollador',
            'Email',
            'Valor/Hora ($)',
            'Tareas Completadas',
            'Horas Estimadas',
            'Horas Reales',
            'Eficiencia (%)',
            'Total Ganado ($)'
        ];
    }

    public function title(): string
    {
        return $this->title;
    }
} 