<?php

namespace App\Http\Controllers\Web;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Services\InsightService;
use App\Services\ReportService;
use App\Services\StockService;
use Illuminate\Http\Request;

/**
 * DashboardController — halaman utama ringkasan bisnis UMKM.
 * Menggabungkan insight stok, keuangan, dan penjualan dalam satu view.
 */
class DashboardController extends Controller
{
    public function __construct(
        protected InsightService $insightService,
        protected StockService $stockService,
        protected ReportService $reportService
    ) {}

    /**
     * GET /dashboard
     * Render halaman dashboard dengan data ringkasan outlet.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user     = auth()->user();
        $outletId = $user->outlet_id;
        $period   = $request->get('period', now()->format('Y-m'));

        $startDate = $period . '-01';
        $endDate   = Carbon::parse($period)->endOfMonth()->format('Y-m-d');

        // ── Service calls ─────────────────────────────────────────────
        $stockInsight     = $this->insightService->generateStockInsight($outletId);
        $financialInsight = $this->insightService->generateFinancialInsight($outletId, $period);
        $lowStockProducts = $this->stockService->checkLowStock($outletId);
        $salesReport      = $this->reportService->getSalesReport($outletId, $startDate, $endDate);

        // ── topProducts: total_sold → total_qty, total_revenue → total_sales ──
        $topProducts = $this->insightService
            ->getTopSellingProducts($outletId, 5)
            ->map(function ($p) {
                $p->total_qty   = $p->total_sold;
                $p->total_sales = $p->total_revenue;
                return $p;
            });

        // ── decliningProducts: current_qty → current_sales, dst ──────
        $decliningProducts = $this->insightService
            ->getDecliningProducts($outletId)
            ->map(fn($dp) => [
                'name'            => $dp['name'],
                'current_sales'   => $dp['current_qty'],
                'prev_sales'      => $dp['last_qty'],
                'drop_percentage' => $dp['decline_percent'],
            ]);

        // ── financialInsight: pastikan semua key ada ──────────────────
        $financialInsight = array_merge([
            'total_revenue'  => 0,
            'total_expenses' => 0,
            'net_profit'     => 0,
            'profit_margin'  => 0,
            'status'         => 'N/A',
        ], $financialInsight ?? []);

        // ── dailyRevenue ──────────────────────────────────────────────
        $dailyRevenue = collect($salesReport['daily_revenue'] ?? [])
            ->map(fn($day) => [
                'date'  => $day['date']    ?? $day['day']     ?? '-',
                'total' => $day['total']   ?? $day['revenue'] ?? 0,
            ])
            ->values()
            ->toArray();

        // ── byPayment ─────────────────────────────────────────────────
        $byPayment = collect($salesReport['by_payment_method'] ?? [])
            ->map(fn($data) => [
                'amount' => is_array($data)
                    ? ($data['amount'] ?? $data['total'] ?? 0)
                    : (int) $data,
            ])
            ->toArray();

        return view('web.dashboard', compact(
            'user',
            'period',
            'stockInsight',
            'financialInsight',
            'topProducts',
            'decliningProducts',
            'lowStockProducts',
            'dailyRevenue',
            'byPayment'
        ));
    }
} // ← penutup class