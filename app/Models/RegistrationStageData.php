<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationStageData extends Model
{
    protected $fillable = ['registration_id', 'program_stage_id', 'form_values', 'status', 'reviewer_notes'];

    protected $casts = [
        'form_values' => 'array'
    ];

    public function stage(): BelongsTo { return $this->belongsTo(ProgramStage::class, 'program_stage_id'); }

    public function registration(): BelongsTo { return $this->belongsTo(Registration::class, 'registration_id'); }
}
