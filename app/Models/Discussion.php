<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Discussion extends Model
{
    protected $fillable = ['user_id', 'repost_of_id', 'title', 'slug', 'content', 'shares_count'];

    protected static function booted()
    {
        static::creating(function ($discussion) {
            if (empty($discussion->slug)) {
                $base = \Illuminate\Support\Str::slug($discussion->title) ?: 'topik';
                $slug = $base;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $base . '-' . \Illuminate\Support\Str::random(5);
                }
                $discussion->slug = $slug;
            }
        });
    }

    // Relasi ke User pembuat diskusi
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Diskusi Asli jika ini adalah posting ulang / quote
    public function originalDiscussion(): BelongsTo
    {
        return $this->belongsTo(Discussion::class, 'repost_of_id');
    }

    // Relasi ke posting ulang dari diskusi ini
    public function reposts(): HasMany
    {
        return $this->hasMany(Discussion::class, 'repost_of_id');
    }

    // Relasi ke komentar di diskusi ini
    public function comments(): HasMany
    {
        return $this->hasMany(DiscussionComment::class)->latest();
    }

    // Relasi ke reaksi di diskusi ini
    public function reactions(): HasMany
    {
        return $this->hasMany(DiscussionReaction::class);
    }

    // Relasi ke daftar favorit / bookmark
    public function favorites(): HasMany
    {
        return $this->hasMany(DiscussionFavorite::class);
    }

    // Relasi ke laporan diskusi
    public function reports(): HasMany
    {
        return $this->hasMany(DiscussionReport::class);
    }

    // Cek apakah difavoritkan oleh user tertentu
    public function isFavoritedBy(?int $userId = null): bool
    {
        $userId = $userId ?? auth()->id();
        if (!$userId) return false;

        return $this->favorites->contains('user_id', $userId);
    }

    // Cek apakah sudah direpost oleh user tertentu
    public function isRepostedBy(?int $userId = null): bool
    {
        $userId = $userId ?? auth()->id();
        if (!$userId) return false;

        return $this->reposts->contains('user_id', $userId);
    }

    // Cek apakah user berwenang menghapus diskusi ini
    public function canBeDeletedBy(?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if (!$user) return false;

        return $this->user_id === $user->id || $user->hasRole('Super Admin') || $user->hasRole('Admin Program');
    }

    // Helper untuk ringkasan reaksi grup
    public function reactionSummary()
    {
        $userId = auth()->id();
        return $this->reactions
            ->groupBy('reaction')
            ->map(function ($group, $emoji) use ($userId) {
                return [
                    'reaction' => $emoji,
                    'count' => $group->count(),
                    'has_reacted' => $group->contains('user_id', $userId),
                    'users' => $group->pluck('user.name')->take(5)->values()->toArray(),
                ];
            });
    }
}
