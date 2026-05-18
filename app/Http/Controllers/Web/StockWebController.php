<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreStockMovementRequest;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\Request;

/**
 * StockWebController — manajemen stok via Blade.
 */
class StockWebController extends Controller
{
    public function __construct(
        protected StockService $stockService
    ) {}

    /**
     * GET /stock
     * Ringkasan stok outlet + daftar produk stok rendah.
     */
    public function index()
    {
        $outletId         = auth()->user()->outlet_id;
        $lowStockProducts = $this->stockService->checkLowStock($outletId);

        // Statistik stok untuk summary card
        $totalProducts  = Product::byOutlet($outletId)->active()->count();
        $totalLowStock  = $lowStockProducts->count();
        $totalOutOfStock = $lowStockProducts->where('stock_qty', 0)->count();

        return view('web.stock.index', compact(
            'lowStockProducts',
            'totalProducts',
            'totalLowStock',
            'totalOutOfStock'
        ));
    }

    /**
     * GET /stock/movements
     * Riwayat pergerakan stok semua produk di outlet.
     */
    public function movements(Request $request)
    {
        $outletId = auth()->user()->outlet_id;

        $query = StockMovement::with(['product:id,name,sku', 'user:id,name'])
            ->whereHas('product', fn ($q) => $q->where('outlet_id', $outletId))
            ->latest('created_at');

        if ($request->filled('product_id')) {
            $query->byProduct((int) $request->product_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->inPeriod($request->start_date, $request->end_date);
        }

        $movements = $query->paginate(25)->withQueryString();
        $products  = Product::byOutlet($outletId)->active()->get(['id', 'name', 'sku']);
        $filters   = $request->only(['product_id', 'type', 'start_date', 'end_date']);

        return view('web.stock.movements', compact('movements', 'products', 'filters'));
    }

    /**
     * GET /stock/adjust
     * Form manual adjustment stok (restock / koreksi).
     */
    public function adjust()
    {
        $outletId = auth()->user()->outlet_id;
        $products = Product::byOutlet($outletId)->active()->orderBy('name')->get(['id', 'name', 'sku', 'stock_qty', 'unit']);

        return view('web.stock.adjust', compact('products'));
    }

    /**
     * [CORE] POST /stock/movements
     * Simpan pergerakan stok manual.
     */
    public function storeMovement(StoreStockMovementRequest $request)
    {
        try {
            $this->stockService->recordMovement(
                productId: (int) $request->product_id,
                userId:    auth()->id(),
                type:      $request->type,
                qty:       (int) $request->qty,
                note:      $request->note ?? ''
            );

            $product = Product::find($request->product_id);

            return redirect()
                ->route('stock.movements')
                ->with('success', "Stok \"{$product->name}\" berhasil diperbarui.");

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}
