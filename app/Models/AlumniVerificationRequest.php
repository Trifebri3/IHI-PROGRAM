<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlumniVerificationRequest extends Model
{
    protected $fillable = [
        'user_id',
        'alumni_program_id',
        'certificate_scan_path',
        'status', // pending, approved, rejected, revision
        'admin_notes',
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
