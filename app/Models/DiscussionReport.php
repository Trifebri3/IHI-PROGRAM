<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscussionReport extends Model
{
    protected $fillable = [
        'user_id',
        'discussion_id',
        'reason',
        'notes',
        'status',
        'action_taken',
        'action_taken_by'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function discussion(): BelongsTo
    {
        return $this->belongsTo(Discussion::class);
    }

    public function actionAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_taken_by');
    }
}
