<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SalesReportExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        protected array $reportData,
        protected string $startDate,
        protected string $endDate,
        protected int $outletId
    ) {}

    public function sheets(): array
    {
        return [
            'Ringkasan' => new SalesReportSummarySheet($this->reportData),
            'Per Produk' => new SalesReportProductSheet($this->reportData),
            'Harian' => new SalesReportDailySheet($this->reportData),
            'Metode Bayar' => new SalesReportPaymentSheet($this->reportData),
        ];
    }
}
