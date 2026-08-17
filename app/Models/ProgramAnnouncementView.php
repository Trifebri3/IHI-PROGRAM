<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramAnnouncementView extends Model
{
    protected $fillable = ['user_id', 'program_announcement_id', 'confirmed_at'];
}
