<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProfitLossReportExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        protected array $reportData,
        protected string $month,
        protected string $year,
        protected int $outletId
    ) {}

    public function sheets(): array
    {
        return [
            'Laporan' => new ProfitLossReportSheet($this->reportData),
        ];
    }
}
