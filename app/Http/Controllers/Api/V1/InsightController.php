<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\InsightService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * InsightController — endpoint untuk analisis bisnis UMKM berbasis data real.
 */
class InsightController extends Controller
{
    public function __construct(
        protected InsightService $insightService
    ) {}

    /**
     * GET /api/v1/insights
     * Ringkasan insight outlet: stok rendah, keuangan, dan penjualan periode berjalan.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'outlet_id' => ['required', 'integer', 'exists:outlets,id'],
        ]);

        $outletId = (int) $request->outlet_id;
        $period   = $request->get('period', now()->format('Y-m'));

        $stock     = $this->insightService->generateStockInsight($outletId);
        $financial = $this->insightService->generateFinancialInsight($outletId, $period);
        $topProducts = $this->insightService->getTopSellingProducts($outletId, 5);

        return response()->json([
            'success' => true,
            'message' => 'Ringkasan insight berhasil dimuat.',
            'data'    => [
                'period'       => $period,
                'stock'        => $stock,
                'financial'    => $financial,
                'top_products' => $topProducts,
            ],
        ]);
    }

    /**
     * GET /api/v1/insights/sales
     * Insight penjualan: produk terlaris & produk yang mengalami penurunan.
     */
    public function sales(Request $request): JsonResponse
    {
        $request->validate([
            'outlet_id' => ['required', 'integer', 'exists:outlets,id'],
            'period'    => ['nullable', 'string', 'regex:/^\d{4}-\d{2}$/'],
        ]);

        $outletId = (int) $request->outlet_id;
        $period   = $request->get('period', now()->format('Y-m'));

        $data = $this->insightService->generateSalesInsight($outletId, $period);

        return response()->json([
            'success' => true,
            'message' => 'Insight penjualan berhasil dimuat.',
            'data'    => $data,
        ]);
    }

    /**
     * GET /api/v1/insights/stock
     * Insight stok: produk di bawah minimum stok.
     */
    public function stock(Request $request): JsonResponse
    {
        $request->validate([
            'outlet_id' => ['required', 'integer', 'exists:outlets,id'],
        ]);

        $data = $this->insightService->generateStockInsight((int) $request->outlet_id);

        return response()->json([
            'success' => true,
            'message' => 'Insight stok berhasil dimuat.',
            'data'    => $data,
        ]);
    }

    /**
     * GET /api/v1/insights/financial
     * Insight keuangan: profit/loss, cashflow ratio, margin.
     */
    public function financial(Request $request): JsonResponse
    {
        $request->validate([
            'outlet_id' => ['required', 'integer', 'exists:outlets,id'],
            'period'    => ['nullable', 'string', 'regex:/^\d{4}-\d{2}$/'],
        ]);

        $outletId = (int) $request->outlet_id;
        $period   = $request->get('period', now()->format('Y-m'));

        $data = $this->insightService->generateFinancialInsight($outletId, $period);

        return response()->json([
            'success' => true,
            'message' => 'Insight keuangan berhasil dimuat.',
            'data'    => $data,
        ]);
    }
}
