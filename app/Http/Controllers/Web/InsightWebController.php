<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\InsightService;
use Illuminate\Http\Request;

/**
 * InsightWebController — halaman insight bisnis untuk Blade templates.
 * Menggunakan InsightService yang sama dengan API, hanya return-nya view().
 */
class InsightWebController extends Controller
{
    public function __construct(
        protected InsightService $insightService
    ) {}

    /**
     * GET /insights
     * Halaman ringkasan semua insight outlet.
     */
    public function index(Request $request)
    {
        $outletId = auth()->user()->outlet_id;
        $period   = $request->get('period', now()->format('Y-m'));

        $stockInsight      = $this->insightService->generateStockInsight($outletId);
        $financialInsight  = $this->insightService->generateFinancialInsight($outletId, $period);
        $topProducts       = $this->insightService->getTopSellingProducts($outletId, 5);
        $decliningProducts = $this->insightService->getDecliningProducts($outletId);

        return view('web.insights.index', compact(
            'period',
            'stockInsight',
            'financialInsight',
            'topProducts',
            'decliningProducts'
        ));
    }

    /**
     * GET /insights/sales
     * Halaman insight penjualan: produk terlaris & produk menurun.
     */
    public function sales(Request $request)
    {
        $outletId = auth()->user()->outlet_id;
        $period   = $request->get('period', now()->format('Y-m'));

        $salesInsight      = $this->insightService->generateSalesInsight($outletId, $period);
        $topProducts       = $salesInsight['top_products'];
        $decliningProducts = $salesInsight['declining_products'];

        return view('web.insights.sales', compact(
            'period',
            'topProducts',
            'decliningProducts'
        ));
    }

    /**
     * GET /insights/stock
     * Halaman insight stok: produk di bawah minimum.
     */
    public function stock()
    {
        $outletId     = auth()->user()->outlet_id;
        $stockInsight = $this->insightService->generateStockInsight($outletId);

        // Pisah critical (stok = 0) dan warning (stok < minimum)
        $criticalProducts = collect($stockInsight['critical']);
        $warningProducts  = collect($stockInsight['warning']);
        $totalLowStock    = $stockInsight['total_low_stock'];

        return view('web.insights.stock', compact(
            'criticalProducts',
            'warningProducts',
            'totalLowStock'
        ));
    }

    /**
     * GET /insights/financial
     * Halaman insight keuangan: profit/loss, cashflow ratio.
     */
    public function financial(Request $request)
    {
        $outletId         = auth()->user()->outlet_id;
        $period           = $request->get('period', now()->format('Y-m'));
        $financialInsight = $this->insightService->generateFinancialInsight($outletId, $period);

        // Ekstrak key agar lebih mudah dipakai di Blade
        $totalRevenue  = $financialInsight['total_revenue'];
        $totalExpenses = $financialInsight['total_expenses'];
        $netProfit     = $financialInsight['net_profit'];
        $profitMargin  = $financialInsight['profit_margin'];
        $status        = $financialInsight['status']; // 'profit' atau 'loss'

        return view('web.insights.financial', compact(
            'period',
            'financialInsight',
            'totalRevenue',
            'totalExpenses',
            'netProfit',
            'profitMargin',
            'status'
        ));
    }
}
