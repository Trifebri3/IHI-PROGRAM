<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Mitigasi: Timpa data/email yang sudah terdaftar tetapi belum terverifikasi email
        // dan belum melakukan pengisian biodata secara sempurna (broken registration).
        if ($request->filled('email')) {
            $existingUser = User::where('email', strtolower(trim($request->email)))->first();
            if ($existingUser) {
                $hasNoBiodata = DB::table('user_biodata_values')
                    ->where('user_id', $existingUser->id)
                    ->where('biodata_field_id', '!=', 3)
                    ->count() == 0;

                if ($existingUser->email_verified_at === null && $hasNoBiodata) {
                    $existingUser->delete();
                }
            }
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'country_code' => ['required', 'string'],
            'phone_number' => ['required', 'string'],
        ]);

        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Inisialisasi profile dan address kosong agar tidak null pointer di view yang sudah ada
            $user->profile()->create([
                'profile_photo_path' => null,
            ]);

            $user->address()->create([
                'negara' => '-',
                'provinsi' => '-',
                'kabupaten' => '-',
                'kecamatan' => '-',
                'desa' => '-',
                'kampung' => '-',
                'detail_alamat' => '-',
            ]);

            // Simpan nomor telepon gabungan ke tabel user_biodata_values (Field ID 3 = Nomor WhatsApp)
            $phone = $request->country_code . ltrim($request->phone_number, '0');
            DB::table('user_biodata_values')->insert([
                'user_id' => $user->id,
                'biodata_field_id' => 3, // Nomor WhatsApp
                'value' => $phone,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $user;
        });

        try {
            event(new Registered($user));
        } catch (\Exception $e) {
            // Log error tetapi jangan biarkan proses registrasi gagal (hindari error 500)
            \Log::error('Gagal memproses event registrasi / mengirim email: ' . $e->getMessage());
        }

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
