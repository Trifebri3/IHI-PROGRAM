<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramAnnouncement extends Model
{
    protected $fillable = ['program_id', 'title', 'content', 'type'];

    public function program(): BelongsTo {
        return $this->belongsTo(Program::class);
    }
}
