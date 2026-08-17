{{-- resources/views/kebijakan-privasi.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Kebijakan Privasi - Institut Hijau Indonesia</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <style>
        /* CSS SAMA SEPERTI DI ATAS (copy dari syarat-ketentuan) */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Figtree', 'Inter', sans-serif;
            background: linear-gradient(135deg, #f7faf5 0%, #eef3ea 100%);
            min-height: 100vh;
        }
        .document-wrapper { max-width: 900px; margin: 0 auto; padding: 2rem 1.5rem; }
        .document-header { text-align: center; margin-bottom: 2.5rem; }
        .logo-link { display: inline-block; margin-bottom: 1.5rem; transition: transform 0.2s; }
        .logo-link:hover { transform: scale(1.02); }
        .logo-img { height: auto; width: min(120px, 25vw); max-height: 70px; }
        .document-title { font-size: 1.8rem; font-weight: 700; color: #1e4a2f; margin-bottom: 0.5rem; }
        .document-subtitle { color: #5b7c56; font-size: 0.85rem; }
        .document-card { background: #ffffff; border-radius: 1.5rem; box-shadow: 0 20px 35px -12px rgba(0,0,0,0.1); margin-bottom: 2rem; }
        .document-content { padding: 2rem; }
        .document-content h2 { font-size: 1.3rem; font-weight: 700; color: #1e4a2f; margin: 1.5rem 0 0.75rem 0; border-bottom: 2px solid #e2e8dc; }
        .document-content h2:first-of-type { margin-top: 0; }
        .document-content p { font-size: 0.9rem; line-height: 1.65; color: #2c3e2b; margin-bottom: 0.75rem; }
        .document-content ul { margin: 0.75rem 0 1rem 1.5rem; }
        .document-content li { font-size: 0.9rem; line-height: 1.6; margin-bottom: 0.4rem; }
        .highlight-box { background: #f1f8ef; border-left: 4px solid #2d6a4f; padding: 1rem 1.25rem; border-radius: 0.75rem; margin: 1rem 0; }
        .date-badge { background: #e8f0e5; padding: 0.25rem 0.75rem; border-radius: 2rem; font-size: 0.7rem; color: #2d6a4f; display: inline-block; margin-bottom: 1rem; }
        .back-button { display: inline-flex; align-items: center; gap: 0.5rem; background: #1e4a2f; color: white; text-decoration: none; padding: 0.7rem 1.5rem; border-radius: 2rem; font-weight: 600; font-size: 0.85rem; transition: all 0.2s; margin-bottom: 1rem; }
        .back-button:hover { background: #14381f; transform: translateY(-2px); }
        .document-footer { text-align: center; padding: 1.5rem; border-top: 1px solid #e2e8dc; }
        .footer-links { display: flex; justify-content: center; gap: 1.5rem; flex-wrap: wrap; margin-bottom: 1rem; }
        .footer-links a { color: #5b7c56; text-decoration: none; font-size: 0.8rem; }
        .footer-links a:hover { color: #1e4a2f; text-decoration: underline; }
        .copyright { font-size: 0.7rem; color: #8ba382; }
        @media (max-width: 768px) { .document-wrapper { padding: 1.25rem; } .document-content { padding: 1.25rem; } .document-title { font-size: 1.4rem; } }
        @media (max-width: 480px) { .document-wrapper { padding: 1rem; } .document-content { padding: 1rem; } }
        .link-green { color: #2d6a4f; text-decoration: none; font-weight: 600; }
        .link-green:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="document-wrapper">
        <div>
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('login') }}" class="back-button">← Kembali</a>
        </div>
        <div class="document-header">
            <a href="/" class="logo-link"><img src="https://lms.instituthijauindonesia.or.id/images/logo-light.png" alt="Institut Hijau Indonesia" class="logo-img"></a>
            <h1 class="document-title">Kebijakan Privasi</h1>
            <p class="document-subtitle">Perlindungan Data Pribadi Anda</p>
        </div>
        <div class="document-card">
            <div class="document-content">
                <div class="date-badge">Terakhir diperbarui: 23 April 2026</div>
                <p>Institut Hijau Indonesia (IHI) berkomitmen untuk melindungi privasi dan data pribadi Anda. Kebijakan ini menjelaskan bagaimana kami mengumpulkan, menggunakan, dan melindungi informasi Anda.</p>

                <h2>1. Informasi yang Dikumpulkan</h2>
                <p>Kami mengumpulkan informasi berikut:</p>
                <ul>
                    <li>Data identitas (nama, email, nomor telepon)</li>
                    <li>Data profil (foto, institusi, minat lingkungan)</li>
                    <li>Data aktivitas (riwayat kursus, sertifikasi, partisipasi forum)</li>
                    <li>Data teknis (alamat IP, jenis browser, perangkat)</li>
                </ul>

                <h2>2. Penggunaan Informasi</h2>
                <p>Informasi Anda digunakan untuk:</p>
                <ul>
                    <li>Memberikan akses ke platform pembelajaran</li>
                    <li>Mengirimkan notifikasi dan email verifikasi</li>
                    <li>Meningkatkan layanan dan materi pembelajaran</li>
                    <li>Menghubungi Anda untuk program beasiswa atau kegiatan hijau</li>
                </ul>

                <h2>3. Perlindungan Data</h2>
                <p>Kami menggunakan enkripsi dan protokol keamanan untuk melindungi data Anda. Informasi tidak akan dijual atau disewakan kepada pihak ketiga.</p>

                <h2>4. Hak Anda</h2>
                <p>Anda berhak untuk mengakses, memperbaiki, atau menghapus data pribadi Anda. Hubungi support@instituthijau.id untuk permohonan terkait data.</p>
            </div>
            <div class="document-footer">
                <div class="footer-links">
                    <a href="{{ route('syarat-ketentuan') }}">Syarat & Ketentuan</a>
                    <a href="{{ route('kebijakan-privasi') }}">Kebijakan Privasi</a>
                    <a href="/tentang-kami">Tentang Kami</a>
                </div>
                <div class="copyright">&copy; {{ date('Y') }} Institut Hijau Indonesia</div>
            </div>
        </div>
    </div>
</body>
</html>
