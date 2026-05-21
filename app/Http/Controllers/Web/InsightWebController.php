<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\InsightService;
use Illuminate\Http\Request;

class InsightWebController extends Controller
{
    public function __construct(
        protected InsightService $insightService
    ) {}

    // ── Helper: normalisasi topProducts ──────────────────────────
    // Service return: total_sold, total_revenue
    // Blade expects:  total_qty,  total_sales
    private function normalizeTopProducts($products)
    {
        return collect($products)->map(function ($p) {
            $p->total_qty   = $p->total_sold;
            $p->total_sales = $p->total_revenue;
            return $p;
        });
    }

    // ── Helper: normalisasi decliningProducts ─────────────────────
    // Service return: current_qty, last_qty, decline_percent
    // Blade expects:  current_sales, prev_sales, drop_percentage
    private function normalizeDecliningProducts($products)
    {
        return collect($products)->map(fn($dp) => [
            'name'            => $dp['name'],
            'current_sales'   => $dp['current_qty'],
            'prev_sales'      => $dp['last_qty'],
            'drop_percentage' => $dp['decline_percent'],
        ]);
    }

    /**
     * GET /insights
     */
    public function index(Request $request)
    {
        $outletId = auth()->user()->outlet_id;
        $period   = $request->get('period', now()->format('Y-m'));

        $stockInsight     = $this->insightService->generateStockInsight($outletId);
        $financialInsight = $this->insightService->generateFinancialInsight($outletId, $period);

        $topProducts       = $this->normalizeTopProducts(
            $this->insightService->getTopSellingProducts($outletId, 5)
        );
        $decliningProducts = $this->normalizeDecliningProducts(
            $this->insightService->getDecliningProducts($outletId)
        );

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
     */
    public function sales(Request $request)
    {
        $outletId = auth()->user()->outlet_id;
        $period   = $request->get('period', now()->format('Y-m'));

        $salesInsight = $this->insightService->generateSalesInsight($outletId, $period);

        $topProducts       = $this->normalizeTopProducts($salesInsight['top_products']);
        $decliningProducts = $this->normalizeDecliningProducts($salesInsight['declining_products']);

        return view('web.insights.sales', compact(
            'period',
            'topProducts',
            'decliningProducts'
        ));
    }

    /**
     * GET /insights/stock
     */
    public function stock()
    {
        $outletId     = auth()->user()->outlet_id;
        $stockInsight = $this->insightService->generateStockInsight($outletId);

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
     */
    public function financial(Request $request)
    {
        $outletId         = auth()->user()->outlet_id;
        $period           = $request->get('period', now()->format('Y-m'));
        $financialInsight = $this->insightService->generateFinancialInsight($outletId, $period);

        $totalRevenue  = $financialInsight['total_revenue'];
        $totalExpenses = $financialInsight['total_expenses'];
        $netProfit     = $financialInsight['net_profit'];
        $profitMargin  = $financialInsight['profit_margin'];
        $status        = $financialInsight['status'];

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