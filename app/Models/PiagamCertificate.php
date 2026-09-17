<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PiagamCertificate extends Model
{
        protected $guarded = [];

    public function template()
    {
        return $this->belongsTo(PiagamTemplate::class, 'template_id');
    }

    public function participant()
    {
        return $this->belongsTo(Registration::class, 'participant_id');
    }

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }
}



