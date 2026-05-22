<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExpenseReportSummarySheet implements FromArray, WithStyles
{
    public function __construct(protected array $reportData) {}

    public function array(): array
    {
        $data = $this->reportData;
        return [
            ['LAPORAN PENGELUARAN'],
            ['Periode', $data['period']['start'] . ' hingga ' . $data['period']['end']],
            [],
            ['RINGKASAN'],
            ['Total Pengeluaran', $data['summary']['total_amount']],
            ['Total Catatan', $data['summary']['total_records']],
            ['Rata-rata Harian', $data['summary']['average_daily']],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            4 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
