<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreStockMovementRequest;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * StockMovementController — riwayat dan manajemen pergerakan stok.
 */
class StockMovementController extends Controller
{
    public function __construct(
        protected StockService $stockService
    ) {}

    /**
     * GET /api/v1/stock-movements
     * Riwayat pergerakan stok per produk atau per outlet.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'outlet_id'  => ['nullable', 'integer', 'exists:outlets,id'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'type'       => ['nullable', 'in:in,out,adjustment'],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
            'per_page'   => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $query = StockMovement::with([
            'product:id,name,sku,unit',
            'user:id,name',
        ])->latest('created_at');

        if ($request->filled('product_id')) {
            $query->byProduct((int) $request->product_id);
        }

        if ($request->filled('outlet_id')) {
            $query->whereHas('product', fn ($q) => $q->where('outlet_id', $request->outlet_id));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->inPeriod($request->start_date, $request->end_date);
        }

        $movements = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'message' => 'Riwayat pergerakan stok berhasil dimuat.',
            'data'    => $movements,
        ]);
    }

    /**
     * POST /api/v1/stock-movements
     * Tambah/kurangi stok secara manual (restock, koreksi, dll).
     */
    public function store(StoreStockMovementRequest $request): JsonResponse
    {
        try {
            $movement = $this->stockService->recordMovement(
                productId: (int) $request->product_id,
                userId:    $request->user()->id,
                type:      $request->type,
                qty:       (int) $request->qty,
                note:      $request->note ?? ''
            );

            return response()->json([
                'success' => true,
                'message' => 'Pergerakan stok berhasil dicatat.',
                'data'    => $movement->load('product:id,name,sku,stock_qty', 'user:id,name'),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => null,
            ], 422);
        }
    }
}
