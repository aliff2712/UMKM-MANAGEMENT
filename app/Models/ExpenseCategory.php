<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    // =========================================================
    // RELATIONS
    // =========================================================

    // [CORE] Satu kategori pengeluaran memiliki banyak record expenses
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
