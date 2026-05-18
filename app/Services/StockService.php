<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * StockService — mengelola integritas data stok produk.
 * Setiap perubahan stok WAJIB melalui service ini untuk audit trail lengkap.
 */
class StockService
{
    /**
     * [CORE] Catat pergerakan stok DAN update stock_qty produk secara atomic.
     *
     * @param  int    $productId
     * @param  int    $userId
     * @param  string $type  'in' | 'out' | 'adjustment'
     * @param  int    $qty
     * @param  string $note
     * @return StockMovement
     * @throws \Exception
     */
    public function recordMovement(int $productId, int $userId, string $type, int $qty, string $note = ''): StockMovement
    {
        // [CORE] Operasi atomic: insert movement + update stok dalam satu DB transaction
        return DB::transaction(function () use ($productId, $userId, $type, $qty, $note) {
            /** @var Product $product */
            $product = Product::lockForUpdate()->findOrFail($productId);

            $stockDelta = match ($type) {
                'in'         => $qty,
                'out'        => -$qty,
                'adjustment' => $qty,
                default      => throw new \InvalidArgumentException("Tipe movement tidak valid: {$type}"),
            };

            if ($type === 'out' && $product->stock_qty < $qty) {
                throw new \Exception(
                    "Stok \"{$product->name}\" tidak mencukupi. Tersedia: {$product->stock_qty}, Diminta: {$qty}."
                );
            }

            $movement = StockMovement::create([
                'product_id' => $productId,
                'user_id'    => $userId,
                'type'       => $type,
                'qty'        => $qty,
                'note'       => $note,
            ]);

            // [CORE] Update stock_qty produk secara langsung
            $product->increment('stock_qty', $stockDelta);

            return $movement;
        });
    }

    /**
     * [CORE] Kembalikan produk aktif yang stoknya di bawah atau sama dengan stock_minimum.
     *
     * @param  int $outletId
     * @return Collection<Product>
     */
    public function checkLowStock(int $outletId): Collection
    {
        return Product::byOutlet($outletId)
            ->active()
            ->lowStock()
            ->with('category:id,name')
            ->orderByRaw('stock_qty / NULLIF(stock_minimum, 0) ASC')
            ->get(['id', 'name', 'sku', 'unit', 'stock_qty', 'stock_minimum', 'category_id']);
    }

    /**
     * [CORE] Stock opname: set stok ke nilai baru (bukan delta), catat sebagai 'adjustment'.
     *
     * @param  int    $productId
     * @param  int    $userId
     * @param  int    $newStockQty  Nilai aktual hasil hitung fisik
     * @param  string $note
     * @return StockMovement
     */
    public function adjustStock(int $productId, int $userId, int $newStockQty, string $note = 'Stock opname'): StockMovement
    {
        $product = Product::findOrFail($productId);
        $delta   = $newStockQty - $product->stock_qty;

        return $this->recordMovement($productId, $userId, 'adjustment', $delta, $note);
    }
}
