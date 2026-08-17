<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBiodataValue extends Model
{
    protected $fillable = ['user_id', 'biodata_field_id', 'value'];
}
