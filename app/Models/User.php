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
    'name', 'email', 'is_dummy', 'password', 'avatar', 'google_id', 'sso_token', 'sso_token_expires_at', 'must_change_password', 'is_blocked', 'is_forum_restricted'
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
            'must_change_password' => 'boolean',
            'is_blocked' => 'boolean',
            'is_forum_restricted' => 'boolean',
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

    /**
     * Override hasVerifiedEmail untuk mendukung bypass verifikasi email global saat mitigasi aktif
     */
    public function hasVerifiedEmail(): bool
    {
        if (\App\Models\SystemSetting::getVal('mitigation_mode', '0') === '1') {
            return true;
        }

        return ! is_null($this->email_verified_at);
    }

    // Tambahkan relasi di dalam class User
    public function verification()
    {
        return $this->hasOne(AccountVerification::class);
    }

    // Helper untuk cek status centang biru di Blade / Controller
    public function isVerifiedAccount(): bool
    {
        return $this->verification?->status === 'verified'
            || $this->hasRole('Super Admin')
            || $this->hasRole('Admin Program');
    }

    // Helper cek apakah akun dibatasi di Green Forum
    public function isForumRestricted(): bool
    {
        return (bool) $this->is_forum_restricted;
    }

    // Relasi notifikasi pengguna
    public function notifications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserNotification::class, 'user_id');
    }

    public function unreadNotificationsCount(): int
    {
        return $this->notifications()->whereNull('read_at')->count();
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
