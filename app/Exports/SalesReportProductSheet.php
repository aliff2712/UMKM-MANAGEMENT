<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportProductSheet implements FromArray, WithHeadings, WithStyles
{
    public function __construct(protected array $reportData) {}

    public function headings(): array
    {
        return ['SKU', 'Nama Produk', 'Jumlah Terjual', 'Total Penjualan', 'Laba Kotor'];
    }

    public function array(): array
    {
        return collect($this->reportData['product_sales'])->map(function ($product) {
            return [
                $product['sku'] ?? '-',
                $product['name'] ?? '-',
                $product['total_qty'] ?? 0,
                $product['total_sales'] ?? 0,
                $product['gross_margin'] ?? 0,
            ];
        })->toArray();
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
