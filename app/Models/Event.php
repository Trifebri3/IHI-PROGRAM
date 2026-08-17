<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = ['title', 'description', 'form_schema', 'location', 'event_date', 'event_time', 'quota', 'banner_path', 'attendance_token', 'certificate_template_path',  'is_attendance_open'];

    // WAJIB: Casting otomatis format teks blob menjadi array
    protected $casts = [
        'form_schema' => 'array'
    ];

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }
}
