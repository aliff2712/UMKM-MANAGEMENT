<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProfitLossReportSheet implements FromArray, WithStyles
{
    public function __construct(protected array $reportData) {}

    public function array(): array
    {
        $data = $this->reportData;
        return [
            ['LAPORAN LABA & RUGI'],
            ['Periode', $data['period']['month'] . '/' . $data['period']['year']],
            [],
            ['PENDAPATAN'],
            ['Gross Revenue (Sebelum Diskon)', $data['income']['gross_revenue']],
            ['Diskon', $data['income']['total_discount']],
            ['Net Revenue', $data['income']['net_revenue']],
            [],
            ['HARGA POKOK PENJUALAN (HPP)'],
            ['Total HPP', $data['cogs']],
            [],
            ['LABA KOTOR'],
            ['Gross Profit', $data['gross_profit']],
            ['Gross Margin (%)', $data['gross_margin']],
            [],
            ['PENGELUARAN OPERASIONAL'],
            ...$this->formatExpenses($data['expenses']['breakdown']),
            ['Total Pengeluaran', $data['expenses']['total']],
            [],
            ['LABA BERSIH'],
            ['Net Profit', $data['net_profit']],
            ['Net Margin (%)', $data['net_margin']],
            ['Status', $data['status']],
        ];
    }

    private function formatExpenses($expenses): array
    {
        return collect($expenses)->map(fn($exp) => [$exp['category'], $exp['total']])->all();
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            4 => ['font' => ['bold' => true, 'size' => 12]],
            9 => ['font' => ['bold' => true, 'size' => 12]],
            12 => ['font' => ['bold' => true, 'size' => 12]],
            16 => ['font' => ['bold' => true, 'size' => 12]],
            20 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
