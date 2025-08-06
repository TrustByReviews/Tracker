<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class TaskDetailsExport implements FromArray, WithHeadings, WithTitle
{
    protected $data;
    protected $title;

    public function __construct($data, $title = 'Detalles por Tarea')
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
            'Tarea',
            'Proyecto',
            'Horas Estimadas',
            'Horas Reales',
            'Valor/Hora ($)',
            'Pago por Tarea ($)',
            'Eficiencia (%)',
            'Fecha Completada'
        ];
    }

    public function title(): string
    {
        return $this->title;
    }
} 