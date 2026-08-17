<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAlumni extends Model
{
    protected $table = 'user_alumni';

    protected $fillable = [
        'user_id',
        'alumni_program_id',
        'uuid',
        'verification_status',
        'extra_info',
    ];

    protected $casts = [
        'extra_info' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function alumniProgram(): BelongsTo
    {
        return $this->belongsTo(AlumniProgram::class);
    }
}
