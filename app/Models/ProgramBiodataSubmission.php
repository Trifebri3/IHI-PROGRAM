<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramBiodataSubmission extends Model
{
    protected $fillable = ['user_id', 'program_id', 'submitted_answers'];

    protected $casts = [
        'submitted_answers' => 'array'
    ];
}
