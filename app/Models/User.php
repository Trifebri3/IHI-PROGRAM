<?php

namespace App\Models;


use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\QueuedVerifyEmail;
use Spatie\Permission\Traits\HasRoles; // 1. Import Trait Spatie
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    // 2. Tambahkan HasRoles di sini
    use HasApiTokens, HasFactory, Notifiable, HasRoles;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
protected $fillable = [
    'name', 'email', 'is_dummy', 'password', 'avatar', 'google_id', 'sso_token', 'sso_token_expires_at'
];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Override method bawaan Laravel untuk mengirim email verifikasi.
     * Alihkan ke notifikasi yang sudah menggunakan Queue.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new QueuedVerifyEmail);
    }

    // Tambahkan relasi di dalam class User
    public function verification()
    {
        return $this->hasOne(AccountVerification::class);
    }

    // Helper untuk cek status centang biru di Blade / Controller
    public function isVerifiedAccount(): bool
    {
        return $this->verification?->status === 'verified';
    }

    // Relasi ke Program yang dikelola oleh Admin ini
    public function managedPrograms(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Program::class, 'program_manager');
    }
    // app/Models/User.php

public function registrations() {
    return $this->hasMany(Registration::class);
}

public function eventRegistrations() {
    return $this->hasMany(EventRegistration::class);
}
public function profile() {
    return $this->hasOne(UserProfile::class);
}

public function address() {
    return $this->hasOne(Address::class);
}
// Tambahkan ini di app/Models/User.php
public function userProfile()
{
    return $this->profile();
}

public function biodataValues()
{
    return $this->hasMany(UserBiodataValue::class, 'user_id');
}

public function alumniProfile()
{
    return $this->hasOne(AlumniProfile::class);
}

public function alumniPrograms()
{
    return $this->belongsToMany(AlumniProgram::class, 'user_alumni')
                ->withPivot(['id', 'uuid', 'verification_status', 'extra_info'])
                ->withTimestamps();
}

public function alumniCertificates()
{
    return $this->hasMany(AlumniCertificate::class);
}

public function alumniVerificationRequests()
{
    return $this->hasMany(AlumniVerificationRequest::class);
}
}
