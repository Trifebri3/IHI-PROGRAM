<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificateTemplate extends Model
{
    protected $fillable = [
        'alumni_program_id',
        'template_path',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function alumniProgram(): BelongsTo
    {
        return $this->belongsTo(AlumniProgram::class);
    }
}
