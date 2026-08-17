<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramStage extends Model
{
    protected $fillable = ['program_id', 'name', 'sequence', 'start_date', 'end_date', 'form_schema', 'pass_announcement', 'fail_announcement', 'instruction'];

    protected $casts = [
        'form_schema' => 'array' // Otomatis konversi JSON ke Array PHP
    ];
}
