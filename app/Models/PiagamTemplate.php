<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PiagamTemplate extends Model
{
    protected $guarded = [];
    public function programs() {
        return $this->hasMany(Program::class, 'piagam_template_id');
    }    public function layouts() {
        return $this->hasMany(PiagamLayout::class, 'template_id');
    }
}
