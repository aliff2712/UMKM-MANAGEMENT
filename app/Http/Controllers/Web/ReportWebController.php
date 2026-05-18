<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;

/**
 * ReportWebController — halaman laporan bisnis berbasis periode untuk Blade.
 */
class ReportWebController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    /**
     * GET /reports/sales
     * Laporan penjualan per periode.
     */
    public function sales(Request $request)
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $outletId  = auth()->user()->outlet_id;
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate   = $request->get('end_date', now()->toDateString());

        $report       = $this->reportService->getSalesReport($outletId, $startDate, $endDate);
        $summary      = $report['summary'];
        $productSales = $report['product_sales'];
        $dailyRevenue = $report['daily_revenue'];
        $byPayment    = $report['by_payment_method'];

        return view('web.reports.sales', compact(
            'startDate',
            'endDate',
            'summary',
            'productSales',
            'dailyRevenue',
            'byPayment'
        ));
    }

    /**
     * GET /reports/expenses
     * Laporan pengeluaran per periode.
     */
    public function expenses(Request $request)
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $outletId  = auth()->user()->outlet_id;
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate   = $request->get('end_date', now()->toDateString());

        $report         = $this->reportService->getExpenseReport($outletId, $startDate, $endDate);
        $summary        = $report['summary'];
        $byCategory     = $report['by_category'];
        $dailyExpenses  = $report['daily_expenses'];

        return view('web.reports.expenses', compact(
            'startDate',
            'endDate',
            'summary',
            'byCategory',
            'dailyExpenses'
        ));
    }

    /**
     * [CORE] GET /reports/profit-loss
     * Laporan laba bersih: Revenue - COGS - Opex = Net Profit.
     */
    public function profitLoss(Request $request)
    {
        $request->validate([
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year'  => ['nullable', 'integer', 'min:2000'],
        ]);

        $outletId = auth()->user()->outlet_id;
        $month    = $request->get('month', now()->format('m'));
        $year     = $request->get('year', now()->format('Y'));

        $report     = $this->reportService->getProfitLossReport($outletId, $month, $year);

        // Ekstrak variabel agar lebih mudah di Blade
        $income     = $report['income'];
        $cogs       = $report['cogs'];
        $grossProfit = $report['gross_profit'];
        $grossMargin = $report['gross_margin'];
        $expenses   = $report['expenses'];
        $netProfit  = $report['net_profit'];
        $netMargin  = $report['net_margin'];
        $status     = $report['status']; // 'profit' atau 'loss'

        return view('web.reports.profit_loss', compact(
            'month',
            'year',
            'income',
            'cogs',
            'grossProfit',
            'grossMargin',
            'expenses',
            'netProfit',
            'netMargin',
            'status'
        ));
    }
}
