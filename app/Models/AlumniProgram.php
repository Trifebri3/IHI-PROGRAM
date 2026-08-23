<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AlumniProgram extends Model
{
    protected $fillable = [
        'program_id',
        'name',
        'year',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_alumni')
                    ->withPivot(['id', 'uuid', 'verification_status', 'extra_info'])
                    ->withTimestamps();
    }

    public function template(): HasOne
    {
        return $this->hasOne(CertificateTemplate::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(AlumniCertificate::class);
    }
}
