<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $user = \App\Models\User::where('email', $this->email)->first();
        $isMitigationActive = (\App\Models\SystemSetting::getVal('mitigation_mode', '0') === '1');
        $shouldBypass = false;

        if ($user) {
            if ($user->must_change_password) {
                $shouldBypass = true;
            } elseif ($isMitigationActive) {
                // Cek apakah biodata belum lengkap (tidak memiliki data biodata penting selain telepon register)
                $hasNoBiodata = \Illuminate\Support\Facades\DB::table('user_biodata_values')
                    ->where('user_id', $user->id)
                    ->where('biodata_field_id', '!=', 3)
                    ->count() === 0;
                
                if ($hasNoBiodata) {
                    $shouldBypass = true;
                    // Tandai secara permanen agar middleware ForcePasswordChange tahu dia harus ganti password
                    $user->must_change_password = true;
                    $user->save();
                }
            }
        }

        if ($shouldBypass) {
            // Bypass password check dan login langsung karena wajib ganti password setelah masuk
            Auth::login($user, $this->boolean('remember'));
        } else {
            if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    'email' => trans('auth.failed'),
                ]);
            }
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
