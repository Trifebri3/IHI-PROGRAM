<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRegistration extends Model
{
    protected $fillable = [
        'user_id', 'event_id', 'form_values', 'attended_at',
        'guest_name', 'guest_email', 'guest_phone', 'ticket_number', 'attendance_form_values',
        'is_eligible_for_certificate'
    ];

    protected $casts = [
        'form_values' => 'array',
        'attendance_form_values' => 'array',
        'attended_at' => 'datetime',
        'is_eligible_for_certificate' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
