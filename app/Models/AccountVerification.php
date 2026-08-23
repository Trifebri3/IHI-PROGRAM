<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Contracts\Encryption\DecryptException;

class AccountVerification extends Model
{
    protected $fillable = [
        'user_id', 'nik', 'ktp_path', 'photo_path',
        'status', 'rejection_reason', 'verified_by', 'verified_at'
    ];

    // Enterprise Security: Handle datetime cast, while nik encryption is managed gracefully via accessors
    protected $casts = [
        'verified_at' => 'datetime'
    ];

    /**
     * Graceful Decryption Accessor for NIK
     */
    public function getNikAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            // If the value is already encrypted, decrypt it. If it is plain, return it.
            return decrypt($value);
        } catch (\Throwable $e) {
            // If it's a plain text NIK (16 digits), return it. Otherwise, indicate decryption failure.
            if (strlen($value) === 16 && is_numeric($value)) {
                return $value;
            }
            return '[Gagal Dekripsi - Key Berubah]';
        }
    }

    /**
     * Encryption Mutator for NIK
     */
    public function setNikAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['nik'] = null;
        } else {
            $this->attributes['nik'] = encrypt($value);
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
