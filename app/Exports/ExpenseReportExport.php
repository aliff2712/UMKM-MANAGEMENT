<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ExpenseReportExport implements WithMultipleSheets
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
            'Ringkasan' => new ExpenseReportSummarySheet($this->reportData),
            'Per Kategori' => new ExpenseReportCategorySheet($this->reportData),
            'Harian' => new ExpenseReportDailySheet($this->reportData),
        ];
    }
}
