<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id',
        'user_id',
        'expense_category_id',
        'amount',
        'description',
        'expense_date',
        'receipt_image',
    ];

    protected $casts = [
        'amount'       => 'float',
        'expense_date' => 'date',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    // =========================================================
    // RELATIONS
    // =========================================================

    // [CORE] Pengeluaran terjadi di satu outlet
    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    // [CORE] User yang mencatat pengeluaran ini
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // [CORE] Pengeluaran diklasifikasikan ke satu kategori pengeluaran
    public function expenseCategory(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class);
    }

    // =========================================================
    // SCOPES
    // =========================================================

    /**
     * [CORE] Filter pengeluaran berdasarkan outlet
     */
    public function scopeByOutlet($query, int $outletId)
    {
        return $query->where('outlet_id', $outletId);
    }

    /**
     * [CORE] Filter pengeluaran dalam rentang tanggal (berdasarkan expense_date)
     */
    public function scopeInPeriod($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('expense_date', [$startDate, $endDate]);
    }

    /**
     * [CORE] Filter pengeluaran dalam bulan berjalan — dipakai ChatbotService
     */
    public function scopeThisMonth($query)
    {
        return $query->whereYear('expense_date', now()->year)
                     ->whereMonth('expense_date', now()->month);
    }

    /**
     * Filter berdasarkan kategori pengeluaran
     */
    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('expense_category_id', $categoryId);
    }
}
