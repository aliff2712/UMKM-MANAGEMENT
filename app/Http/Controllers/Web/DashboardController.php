<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\InsightService;
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
        protected StockService $stockService
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

        // Ambil semua data yang dibutuhkan via Service
        $stockInsight     = $this->insightService->generateStockInsight($outletId);
        $financialInsight = $this->insightService->generateFinancialInsight($outletId, $period);
        $topProducts      = $this->insightService->getTopSellingProducts($outletId, 5);
        $decliningProducts = $this->insightService->getDecliningProducts($outletId);
        $lowStockProducts = $this->stockService->checkLowStock($outletId);

        // Semua variable di bawah otomatis tersedia di Blade via compact()
        return view('web.dashboard', compact(
            'user',
            'period',
            'stockInsight',
            'financialInsight',
            'topProducts',
            'decliningProducts',
            'lowStockProducts'
        ));
    }
}
