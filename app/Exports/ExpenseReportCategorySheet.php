<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExpenseReportCategorySheet implements FromArray, WithHeadings, WithStyles
{
    public function __construct(protected array $reportData) {}

    public function headings(): array
    {
        return ['Kategori', 'Jumlah', 'Total'];
    }

    public function array(): array
    {
        return collect($this->reportData['by_category'])->map(function ($cat) {
            return [
                $cat['category_name'],
                $cat['count'],
                $cat['total'],
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
