<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItem extends Model
{
    /**
     * transaction_items tidak menggunakan updated_at
     */
    public $timestamps = false;

    protected $fillable = [
        'transaction_id',
        'product_id',
        'qty',
        'unit_price',
        'purchase_price',
        'subtotal',
    ];

    protected $casts = [
        'qty'            => 'integer',
        'unit_price'     => 'float',
        'purchase_price' => 'float',
        'subtotal'       => 'float',
        'created_at'     => 'datetime',
    ];

    // =========================================================
    // RELATIONS
    // =========================================================

    // [CORE] Item ini merupakan bagian dari satu transaksi
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    // [CORE] Snapshot produk yang dibeli (harga tersimpan di kolom unit_price & purchase_price)
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // =========================================================
    // ACCESSORS
    // =========================================================

    /**
     * [CORE] Hitung margin kotor per item (selisih selling - HPP × qty)
     */
    public function getGrossMarginAttribute(): float
    {
        return ($this->unit_price - $this->purchase_price) * $this->qty;
    }
}
