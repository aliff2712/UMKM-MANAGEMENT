<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportDailySheet implements FromArray, WithHeadings, WithStyles
{
    public function __construct(protected array $reportData) {}

    public function headings(): array
    {
        return ['Tanggal', 'Jumlah Transaksi', 'Total Revenue'];
    }

    public function array(): array
    {
        return collect($this->reportData['daily_revenue'])->map(function ($day) {
            return [
                $day['date'],
                $day['count'],
                $day['total'],
            ];
        })->all();
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
