<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AlumniCertificate extends Model
{
    protected $fillable = [
        'user_id',
        'alumni_program_id',
        'certificate_number',
        'file_path',
        'uuid',
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

    public function verificationLogs(): HasMany
    {
        return $this->hasMany(QrVerificationLog::class);
    }
}
