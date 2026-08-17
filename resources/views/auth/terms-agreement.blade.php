<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Kebijakan Privasi - Institut Hijau Indonesia</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,600,700&display=swap" rel="stylesheet" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Figtree', sans-serif; background: #f7faf5; color: #2c3e2b; min-height: 100vh; padding: 2rem 1rem; }
        .document-wrapper { max-width: 800px; margin: 0 auto; }

        .header { text-align: center; margin-bottom: 2rem; }
        .logo { max-height: 60px; margin-bottom: 1rem; }

        .card { background: #ffffff; border-radius: 1.5rem; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); padding: 2.5rem; }

        h1 { font-size: 1.75rem; color: #1e4a2f; margin-bottom: 0.5rem; }
        h2 { font-size: 1.1rem; color: #1e4a2f; margin: 1.5rem 0 0.75rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e2e8dc; }
        p { font-size: 0.95rem; line-height: 1.7; margin-bottom: 1rem; }
        ul { margin: 0 0 1.5rem 1.5rem; }
        li { font-size: 0.95rem; margin-bottom: 0.5rem; }

        .agreement-section { background: #f8fafc; border-top: 1px solid #e2e8dc; padding: 2rem; margin-top: 2rem; border-radius: 0 0 1.5rem 1.5rem; }
        .checkbox-group { display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem; }
        .btn-submit { width: 100%; padding: 1rem; background: #0f172a; color: white; border: none; border-radius: 0.75rem; font-weight: 800; text-transform: uppercase; cursor: pointer; transition: background 0.2s; }
        .btn-submit:hover { background: #000; }

        .footer { text-align: center; margin-top: 2rem; font-size: 0.75rem; color: #8ba382; }
    </style>
</head>
<body>
    <div class="document-wrapper">
        <div class="header">
            <img src="https://lms.instituthijauindonesia.or.id/images/logo-light.png" alt="IHI" class="logo">
            <h1>Kebijakan Privasi</h1>
        </div>

        <div class="card">
            <div style="padding-bottom: 2rem;">
                <p>Institut Hijau Indonesia (IHI) berkomitmen untuk melindungi privasi dan data pribadi Anda. Kebijakan ini menjelaskan bagaimana kami mengumpulkan, menggunakan, dan melindungi informasi Anda.</p>

                <h2>1. Informasi yang Dikumpulkan</h2>
                <ul>
                    <li>Data identitas (nama, email, nomor telepon)</li>
                    <li>Data profil (foto, institusi, minat lingkungan)</li>
                    <li>Data aktivitas (riwayat kursus, sertifikasi, partisipasi forum)</li>
                    <li>Data teknis (alamat IP, jenis browser, perangkat)</li>
                </ul>

                <h2>2. Penggunaan Informasi</h2>
                <p>Informasi Anda digunakan untuk menyediakan akses platform, notifikasi, serta peningkatan layanan pembelajaran kami.</p>

                <h2>3. Perlindungan & Hak Anda</h2>
                <p>Kami menggunakan standar enkripsi keamanan tinggi. Anda berhak mengakses, memperbaiki, atau meminta penghapusan data pribadi Anda dengan menghubungi support@instituthijau.id.</p>
            </div>

            <div class="agreement-section">
                <form action="{{ route('terms.store') }}" method="POST">
                    @csrf
                    <div class="checkbox-group">
                        <input type="checkbox" name="agree" id="agree" required style="margin-top: 4px;">
                        <label for="agree" style="font-size: 0.9rem; font-weight: 600;">
                            Saya telah membaca, memahami, dan menyetujui Kebijakan Privasi serta Syarat & Ketentuan yang berlaku di Institut Hijau Indonesia.
                        </label>
                    </div>

                    @error('agree')
                        <p style="color: #e11d48; font-size: 0.8rem; margin-bottom: 1rem; font-weight: 700;">{{ $message }}</p>
                    @enderror

                    <button type="submit" class="btn-submit">Setuju & Lanjutkan ke Dashboard</button>
                </form>
            </div>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Institut Hijau Indonesia. All rights reserved.
        </div>
    </div>
</body>
</html>
