<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ProgramStage;
use App\Models\ProgramBiodataSchema;

class Program extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'banner_path',
        'logo_path',
        'quota',
        'start_date',
        'end_date',
        'status',
        'is_open', // Ditambahkan agar kolom baru bisa di-update lewat Eloquent Laravel
        'is_pinned',
        'score_schema',
        'total_hours',
        'program_certificate_template',
        'gtu_email'
    ];

    protected $casts = [
        'score_schema' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_open' => 'boolean', // Ditambahkan agar otomatis dikonversi jadi true/false di Laravel
        'is_pinned' => 'boolean'
    ];

    // Otomatis buat slug saat nama program diisi
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($program) {
            $program->slug = Str::slug($program->name) . '-' . Str::random(5);
        });
    }

    // Relasi ke Admin Program yang mengelola program ini
    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'program_manager');
    }

    /**
     * Hubungan ke model Stage (Tahapan)
     */
    public function stages(): HasMany
    {
        return $this->hasMany(ProgramStage::class);
    }

    /**
     * Hubungan ke model BiodataSchema (Form Biodata Wajib)
     */
    public function biodataSchemas(): HasMany
    {
        return $this->hasMany(ProgramBiodataSchema::class);
    }

    /**
     * Hubungan ke model GtuConsultation (Konsultasi GTU)
     */
    public function gtuConsultations(): HasMany
    {
        return $this->hasMany(GtuConsultation::class);
    }

    /**
     * Hubungan ke model Registration (Pendaftaran)
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }
}