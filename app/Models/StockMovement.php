<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    /**
     * stock_movements hanya memiliki created_at
     */
    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'user_id',
        'type',
        'qty',
        'note',
    ];

    protected $casts = [
        'qty'        => 'integer',
        'created_at' => 'datetime',
    ];

    // =========================================================
    // RELATIONS
    // =========================================================

    // [CORE] Pergerakan stok terkait satu produk
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // [CORE] User yang mencatat pergerakan stok (bisa kasir atau admin)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // =========================================================
    // SCOPES
    // =========================================================

    /**
     * [CORE] Filter riwayat stok berdasarkan produk tertentu
     */
    public function scopeByProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * [CORE] Filter hanya pergerakan tipe masuk (restock)
     */
    public function scopeStockIn($query)
    {
        return $query->where('type', 'in');
    }

    /**
     * [CORE] Filter hanya pergerakan tipe keluar (penjualan)
     */
    public function scopeStockOut($query)
    {
        return $query->where('type', 'out');
    }

    /**
     * Filter adjustment/koreksi stok opname
     */
    public function scopeAdjustment($query)
    {
        return $query->where('type', 'adjustment');
    }

    /**
     * [CORE] Filter pergerakan stok dalam rentang tanggal
     */
    public function scopeInPeriod($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
    }
}
