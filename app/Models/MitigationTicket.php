<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MitigationTicket extends Model
{
    protected $fillable = ['user_id', 'issue_type', 'description', 'status', 'resolved_by', 'resolved_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
