<?php

namespace App\Http\Controllers\Api\V1;

use App\Exports\ExpenseReportExport;
use App\Exports\ProfitLossReportExport;
use App\Exports\SalesReportExport;
use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * ReportController — endpoint laporan bisnis berbasis periode.
 */
class ReportController extends Controller
{
    public function __construct(protected ReportService $reportService) {}

    /**
     * GET /api/v1/reports/sales
     * Laporan penjualan per periode: rekap transaksi, produk terlaris, revenue harian.
     */
    public function sales(Request $request): JsonResponse
    {
        $request->validate([
            'outlet_id'  => ['required', 'integer', 'exists:outlets,id'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $data = $this->reportService->getSalesReport(
            outletId:  (int) $request->outlet_id,
            startDate: $request->start_date,
            endDate:   $request->end_date
        );

        return response()->json([
            'success' => true,
            'message' => 'Laporan penjualan berhasil dimuat.',
            'data'    => $data,
        ]);
    }

    /**
     * GET /api/v1/reports/expenses
     * Laporan pengeluaran per periode: total, rekap per kategori, tren harian.
     */
    public function expenses(Request $request): JsonResponse
    {
        $request->validate([
            'outlet_id'  => ['required', 'integer', 'exists:outlets,id'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $data = $this->reportService->getExpenseReport(
            outletId:  (int) $request->outlet_id,
            startDate: $request->start_date,
            endDate:   $request->end_date
        );

        return response()->json([
            'success' => true,
            'message' => 'Laporan pengeluaran berhasil dimuat.',
            'data'    => $data,
        ]);
    }

    /**
     * [CORE] GET /api/v1/reports/profit-loss
     * Laporan laba bersih per bulan: Revenue - COGS - Opex = Net Profit.
     */
    public function profitLoss(Request $request): JsonResponse
    {
        $request->validate([
            'outlet_id' => ['required', 'integer', 'exists:outlets,id'],
            'month'     => ['required', 'string', 'regex:/^\d{2}$/'],
            'year'      => ['required', 'string', 'regex:/^\d{4}$/'],
        ]);

        $data = $this->reportService->getProfitLossReport(
            outletId: (int) $request->outlet_id,
            month:    $request->month,
            year:     $request->year
        );

        return response()->json([
            'success' => true,
            'message' => 'Laporan laba rugi berhasil dimuat.',
            'data'    => $data,
        ]);
    }

    /**
     * GET /api/v1/reports/sales/export
     * Ekspor laporan penjualan ke format Excel
     */
    public function exportSalesReport(Request $request): StreamedResponse
    {
        $request->validate([
            'outlet_id'  => ['required', 'integer', 'exists:outlets,id'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $data = $this->reportService->getSalesReport(
            outletId:  (int) $request->outlet_id,
            startDate: $request->start_date,
            endDate:   $request->end_date
        );

        $filename = "Laporan-Penjualan-{$request->start_date}-{$request->end_date}-Outlet{$request->outlet_id}.xlsx";

        return (new SalesReportExport(
            $data,
            $request->start_date,
            $request->end_date,
            (int) $request->outlet_id
        ))->download($filename);
    }

    /**
     * GET /api/v1/reports/expenses/export
     * Ekspor laporan pengeluaran ke format Excel
     */
    public function exportExpenseReport(Request $request): StreamedResponse
    {
        $request->validate([
            'outlet_id'  => ['required', 'integer', 'exists:outlets,id'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $data = $this->reportService->getExpenseReport(
            outletId:  (int) $request->outlet_id,
            startDate: $request->start_date,
            endDate:   $request->end_date
        );

        $filename = "Laporan-Pengeluaran-{$request->start_date}-{$request->end_date}-Outlet{$request->outlet_id}.xlsx";

        return (new ExpenseReportExport(
            $data,
            $request->start_date,
            $request->end_date,
            (int) $request->outlet_id
        ))->download($filename);
    }

    /**
     * GET /api/v1/reports/profit-loss/export
     * Ekspor laporan laba rugi ke format Excel
     */
    public function exportProfitLossReport(Request $request): StreamedResponse
    {
        $request->validate([
            'outlet_id' => ['required', 'integer', 'exists:outlets,id'],
            'month'     => ['required', 'string', 'regex:/^\d{2}$/'],
            'year'      => ['required', 'string', 'regex:/^\d{4}$/'],
        ]);

        $data = $this->reportService->getProfitLossReport(
            outletId: (int) $request->outlet_id,
            month:    $request->month,
            year:     $request->year
        );

        $filename = "Laporan-Laba-Rugi-{$request->year}-{$request->month}-Outlet{$request->outlet_id}.xlsx";

        return (new ProfitLossReportExport(
            $data,
            $request->month,
            $request->year,
            (int) $request->outlet_id
        ))->download($filename);
    }
}
