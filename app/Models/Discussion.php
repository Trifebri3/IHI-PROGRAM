<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Discussion extends Model
{
    protected $fillable = ['user_id', 'title', 'content'];

    // Relasi ke User yang membuat diskusi
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke komentar di diskusi ini
    public function comments(): HasMany
    {
        return $this->hasMany(DiscussionComment::class)->latest();
    }
}
