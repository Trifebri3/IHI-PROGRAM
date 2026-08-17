<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GtuConsultation extends Model
{
    protected $table = 'gtu_consultations';

    protected $fillable = [
        'program_id',
        'user_id',
        'subject',
        'question',
        'reply',
        'status',
        'answered_at'
    ];

    protected $casts = [
        'answered_at' => 'datetime',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
