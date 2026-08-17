<!DOCTYPE html>
<html>
<body style="font-family: sans-serif; background: #f4f7f6; padding: 20px;">
    <div style="max-width: 600px; margin: auto; background: white; padding: 30px; border-radius: 15px; border-top: 5px solid #1e4a2f;">
        <h1 style="color: #1e4a2f;">Halo, {{ $user->name }}!</h1>
        <p style="color: #555; line-height: 1.6;">
            Selamat bergabung di <b>Institut Hijau Indonesia</b>. Untuk mulai mengakses seluruh fitur eksklusif kami, silakan verifikasi alamat email Anda melalui tombol di bawah ini:
        </p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $url }}" style="background: #2d6a4f; color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; font-weight: bold;">
                Verifikasi Email Sekarang
            </a>
        </div>

        <p style="color: #888; font-size: 12px;">
            Jika Anda tidak merasa mendaftar akun ini, silakan abaikan email ini.
        </p>
    </div>
</body>
</html>