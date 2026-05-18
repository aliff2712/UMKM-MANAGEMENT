<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    // =========================================================
    // RELATIONS
    // =========================================================

    // [CORE] Satu kategori bisa memiliki banyak produk
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    // =========================================================
    // SCOPES
    // =========================================================

    /**
     * [CORE] Filter kategori yang memiliki minimal 1 produk aktif
     */
    public function scopeWithActiveProducts($query)
    {
        return $query->whereHas('products', fn ($q) => $q->where('is_active', true));
    }
}
