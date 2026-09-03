<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotification extends Model
{
    protected $fillable = [
        'user_id',
        'actor_id',
        'discussion_id',
        'comment_id',
        'type',
        'data',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function discussion(): BelongsTo
    {
        return $this->belongsTo(Discussion::class, 'discussion_id');
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(DiscussionComment::class, 'comment_id');
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function markAsRead()
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }

    // Format pesan ramah pengguna
    public function getFormattedMessageAttribute(): string
    {
        $actorName = $this->actor?->name ?? 'Seseorang';
        $title = $this->discussion ? \Illuminate\Support\Str::limit($this->discussion->title, 40) : 'diskusi';

        return match ($this->type) {
            'reaction' => "memberikan reaksi {$this->data['emoji']} pada diskusi Anda: '{$title}'",
            'comment' => "mengomentari diskusi Anda: '{$title}'",
            'reply' => "membalas komentar Anda pada: '{$title}'",
            'mention' => "menyebut Anda dalam komentar pada: '{$title}'",
            'repost' => "memposting ulang diskusi Anda: '{$title}'",
            default => "berinteraksi dengan Anda di Green Forum",
        };
    }
}
