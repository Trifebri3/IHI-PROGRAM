<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramBiodataSchema extends Model
{
    // Kunci nama tabel sesuai phpMyAdmin agar tidak meleset secara gaib
    protected $table = 'program_biodata_schemas';

    protected $fillable = ['program_id', 'field_name', 'field_type', 'is_required'];
}
