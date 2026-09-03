<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscussionComment extends Model
{
    protected $fillable = ['discussion_id', 'parent_comment_id', 'user_id', 'content'];

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

    // Relasi ke komentar induk (jika ini balasan ke komentar lain)
    public function parent(): BelongsTo
    {
        return $this->belongsTo(DiscussionComment::class, 'parent_comment_id');
    }

    // Relasi ke balasan-balasan untuk komentar ini
    public function replies(): HasMany
    {
        return $this->hasMany(DiscussionComment::class, 'parent_comment_id');
    }
}
