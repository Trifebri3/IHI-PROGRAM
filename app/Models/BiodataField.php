<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BiodataField extends Model
{
    protected $fillable = ['name', 'type', 'is_required', 'description', 'example', 'options'];

protected $casts = [
    'is_required' => 'boolean',
    'options' => 'array', // Pastikan ini array, bukan yang lain
];
}
