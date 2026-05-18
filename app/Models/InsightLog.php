<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsightLog extends Model
{
    /**
     * insight_logs hanya memiliki created_at
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'outlet_id',
        'type',
        'title',
        'message',
        'severity',
        'period_start',
        'period_end',
        'metadata',
        'is_dismissed',
    ];

    protected $casts = [
        'metadata'     => 'array',
        'period_start' => 'date',
        'period_end'   => 'date',
        'is_dismissed' => 'boolean',
        'created_at'   => 'datetime',
    ];

    // =========================================================
    // RELATIONS
    // =========================================================

    // [CORE] Log insight dimiliki oleh satu outlet
    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    // =========================================================
    // SCOPES
    // =========================================================

    /**
     * [CORE] Hanya insight yang belum di-dismiss oleh pemilik
     */
    public function scopeActive($query)
    {
        return $query->where('is_dismissed', false);
    }

    /**
     * [CORE] Filter insight berdasarkan outlet
     */
    public function scopeByOutlet($query, int $outletId)
    {
        return $query->where('outlet_id', $outletId);
    }

    /**
     * Filter berdasarkan tingkat keparahan insight
     */
    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Filter berdasarkan tipe insight (cashflow, slow_moving, dll)
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
