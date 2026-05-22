<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportPaymentSheet implements FromArray, WithHeadings, WithStyles
{
    public function __construct(protected array $reportData) {}

    public function headings(): array
    {
        return ['Metode', 'Jumlah', 'Total'];
    }

    public function array(): array
    {
        $data = [];
        foreach ($this->reportData['by_payment_method'] as $method => $info) {
            $data[] = [$method, $info['count'], $info['amount']];
        }
        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
