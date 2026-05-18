<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id',
        'user_id',
        'invoice_number',
        'total_amount',
        'discount_amount',
        'paid_amount',
        'change_amount',
        'payment_method',
        'note',
    ];

    protected $casts = [
        'total_amount'    => 'float',
        'discount_amount' => 'float',
        'paid_amount'     => 'float',
        'change_amount'   => 'float',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    // =========================================================
    // RELATIONS
    // =========================================================

    // [CORE] Transaksi terjadi di satu outlet
    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    // [CORE] User (kasir) yang mencatat transaksi ini
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // [CORE] Detail item-item yang dibeli dalam satu transaksi
    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    // =========================================================
    // SCOPES
    // =========================================================

    /**
     * [CORE] Filter transaksi berdasarkan outlet
     */
    public function scopeByOutlet($query, int $outletId)
    {
        return $query->where('outlet_id', $outletId);
    }

    /**
     * [CORE] Filter transaksi dalam rentang tanggal — dipakai di ReportService
     */
    public function scopeInPeriod($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
    }

    /**
     * [CORE] Transaksi dalam bulan dan tahun tertentu
     */
    public function scopeInMonth($query, string $month, string $year)
    {
        return $query->whereYear('created_at', $year)->whereMonth('created_at', $month);
    }

    /**
     * Filter berdasarkan metode pembayaran
     */
    public function scopeByPaymentMethod($query, string $method)
    {
        return $query->where('payment_method', $method);
    }
}
