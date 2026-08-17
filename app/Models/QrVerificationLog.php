<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrVerificationLog extends Model
{
    protected $fillable = [
        'alumni_certificate_id',
        'scanned_uuid',
        'ip_address',
        'user_agent',
        'scanned_at',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    public function alumniCertificate(): BelongsTo
    {
        return $this->belongsTo(AlumniCertificate::class);
    }
}
