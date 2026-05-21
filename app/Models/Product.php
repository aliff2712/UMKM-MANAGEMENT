<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id',
        'category_id',
        'name',
        'sku',
        'unit',
        'purchase_price',
        'selling_price',
        'stock_qty',
        'stock_minimum',
        'is_active',
        'image_path',
    ];

    protected $casts = [
        'purchase_price' => 'float',
        'selling_price'  => 'float',
        'stock_qty'      => 'integer',
        'stock_minimum'  => 'integer',
        'is_active'      => 'boolean',
    ];

    // =========================================================
    // RELATIONS
    // =========================================================

    // [CORE] Produk milik satu outlet
    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    // [CORE] Produk memiliki satu kategori (nullable)
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // [CORE] Produk muncul di banyak baris transaction_items
    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    // [CORE] Rekaman pergerakan stok masuk/keluar/adjustment produk ini
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    // =========================================================
    // SCOPES
    // =========================================================

    /**
     * [CORE] Hanya tampilkan produk yang aktif dijual
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * [CORE] Filter produk berdasarkan outlet — digunakan di semua query multi-tenant
     */
    public function scopeByOutlet($query, int $outletId)
    {
        return $query->where('outlet_id', $outletId);
    }

    /**
     * [CORE] Produk dengan stok di bawah atau sama dengan stock_minimum
     * Digunakan oleh StockService::checkLowStock & InsightService::generateStockInsight
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_qty', '<=', 'stock_minimum');
    }

    /**
     * Filter berdasarkan kategori produk
     */
    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    // =========================================================
    // ACCESSORS
    // =========================================================

    /**
     * [CORE] Hitung margin keuntungan per unit dalam persen
     */
    public function getMarginPercentAttribute(): float
    {
        if ($this->purchase_price == 0) {
            return 0;
        }
        return round((($this->selling_price - $this->purchase_price) / $this->purchase_price) * 100, 2);
    }

    /**
     * Accessor untuk URL gambar produk.
     * Jika tidak ada gambar, mengembalikan placeholder.
     */
    public function getImageUrlAttribute(): string
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : asset('images/placeholder.png');
    }
}