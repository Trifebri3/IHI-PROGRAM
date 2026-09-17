<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PiagamLayout extends Model
{
    protected $guarded = [];

    public function elements() {
        return $this->hasMany(PiagamLayoutElement::class, 'layout_id');
    }
}



