<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class PaymentReportExport implements FromArray, WithHeadings, WithTitle
{
    protected $data;
    protected $title;

    public function __construct($data, $title = 'Payment Report')
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
            'Developer',
            'Email',
            'Hourly Rate ($)',
            'Completed Tasks',
            'Estimated Hours',
            'Actual Hours',
            'Efficiency (%)',
            'Total Earned ($)'
        ];
    }

    public function title(): string
    {
        return $this->title;
    }
} 