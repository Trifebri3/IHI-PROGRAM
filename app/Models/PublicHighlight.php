<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicHighlight extends Model
{
    protected $fillable = [
        'title',
        'content',
        'banner_path',
        'link_text',
        'link_url',
        'theme',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}
