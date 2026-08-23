<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    // Disable default timestamps because we only have created_at
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'action', 'target_user_id', 'details', 'ip_address', 'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime'
    ];

    /**
     * Set the created_at timestamp on creation
     */
    protected static function booted()
    {
        static::creating(function ($log) {
            $log->created_at = $log->created_at ?? now();
        });
    }

    /**
     * User who executed the action (Admin)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Target User affected by the action (if any)
     */
    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}
