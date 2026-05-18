<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    /**
     * notifications hanya memiliki created_at (read_at dikelola manual)
     */
    const UPDATED_AT = null;

    protected $table = 'notifications';

    protected $fillable = [
        'outlet_id',
        'type',
        'title',
        'message',
        'data',
        'is_read',
        'target_role',
        'read_at',
    ];

    protected $casts = [
        'data'       => 'array',
        'is_read'    => 'boolean',
        'created_at' => 'datetime',
        'read_at'    => 'datetime',
    ];

    // =========================================================
    // RELATIONS
    // =========================================================

    // [CORE] Notifikasi dikirim ke outlet tertentu
    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    // =========================================================
    // SCOPES
    // =========================================================

    /**
     * [CORE] Hanya notifikasi yang belum dibaca
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * [CORE] Filter notifikasi berdasarkan outlet
     */
    public function scopeByOutlet($query, int $outletId)
    {
        return $query->where('outlet_id', $outletId);
    }

    /**
     * [CORE] Filter notifikasi yang ditargetkan ke role tertentu atau semua role
     */
    public function scopeForRole($query, string $role)
    {
        return $query->where(function ($q) use ($role) {
            $q->where('target_role', $role)->orWhere('target_role', 'all');
        });
    }

    // =========================================================
    // METHODS
    // =========================================================

    /**
     * [CORE] Tandai notifikasi sebagai sudah dibaca
     */
    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }
}
