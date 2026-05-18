<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Outlet extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
    ];

    // =========================================================
    // RELATIONS
    // =========================================================

    // [CORE] Outlet memiliki banyak pengguna (owner, admin, kasir)
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // [CORE] Semua produk yang dijual di outlet ini
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    // [CORE] Riwayat transaksi penjualan per outlet
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // [CORE] Pengeluaran operasional outlet
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    // [CORE] Log insight otomatis yang di-generate oleh InsightService
    public function insightLogs(): HasMany
    {
        return $this->hasMany(InsightLog::class);
    }

    // [CORE] Notifikasi yang ditargetkan ke outlet ini
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    // =========================================================
    // SCOPES
    // =========================================================

    /**
     * [CORE] Scope untuk filter outlet berdasarkan ID (dipakai di multi-outlet setup)
     */
    public function scopeByOutlet($query, int $outletId)
    {
        return $query->where('id', $outletId);
    }
}
