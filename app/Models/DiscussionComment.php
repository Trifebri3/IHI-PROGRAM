<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscussionComment extends Model
{
    protected $fillable = ['discussion_id', 'user_id', 'content'];

    // Relasi ke User yang berkomentar
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke diskusi induk
    public function discussion(): BelongsTo
    {
        return $this->belongsTo(Discussion::class);
    }
}
