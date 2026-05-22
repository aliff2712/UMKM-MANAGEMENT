<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportSummarySheet implements FromArray, WithHeadings, WithStyles
{
    public function __construct(protected array $reportData) {}

    public function array(): array
    {
        $data = $this->reportData;
        return [
            ['LAPORAN PENJUALAN'],
            ['Periode', $data['period']['start'] . ' hingga ' . $data['period']['end']],
            [],
            ['RINGKASAN'],
            ['Total Revenue', $data['summary']['total_revenue']],
            ['Total Diskon', $data['summary']['total_discount']],
            ['Total Transaksi', $data['summary']['total_transactions']],
            ['Rata-rata Transaksi', $data['summary']['average_transaction']],
        ];
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            4 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
