<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'outlet_id',
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
    ];

    // =========================================================
    // RELATIONS
    // =========================================================

    // [CORE] User terdaftar & bekerja di satu outlet
    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    // [CORE] Transaksi penjualan yang diinput oleh user (kasir) ini
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // [CORE] Pengeluaran yang diinput oleh user ini
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    // [CORE] Pergerakan stok yang dicatat oleh user ini
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    // =========================================================
    // SCOPES
    // =========================================================

    /**
     * [CORE] Hanya user yang aktif/tidak dinonaktifkan
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * [CORE] Filter user berdasarkan outlet
     */
    public function scopeByOutlet($query, int $outletId)
    {
        return $query->where('outlet_id', $outletId);
    }

    /**
     * Filter user berdasarkan role (owner/admin/kasir)
     */
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    // =========================================================
    // HELPERS
    // =========================================================

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isKasir(): bool
    {
        return $this->role === 'kasir';
    }
}
