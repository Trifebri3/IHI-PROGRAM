<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'user_id', 'negara', 'provinsi', 'kabupaten',
        'kecamatan', 'desa', 'kampung', 'detail_alamat'
    ];
}
