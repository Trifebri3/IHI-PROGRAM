{{-- resources/views/kebijakan-privasi.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kebijakan Privasi - Institut Hijau Indonesia</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Figtree', 'Inter', system-ui, sans-serif;
            background: linear-gradient(135deg, #f7faf5 0%, #eef3ea 100%);
            min-height: 100vh;
        }

        .document-wrapper {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        .document-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .logo-link {
            display: inline-block;
            margin-bottom: 1.5rem;
            transition: transform 0.2s;
        }

        .logo-link:hover {
            transform: scale(1.02);
        }

        .logo-img {
            height: auto;
            width: min(120px, 25vw);
            max-height: 70px;
        }

        .document-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1e4a2f;
            margin-bottom: 0.5rem;
        }

        .document-subtitle {
            color: #5b7c56;
            font-size: 0.85rem;
        }

        .document-card {
            background: #ffffff;
            border-radius: 1.5rem;
            box-shadow: 0 20px 35px -12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .document-content {
            padding: 2rem 2rem;
        }

        .document-content h2 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1e4a2f;
            margin: 1.5rem 0 0.75rem 0;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e2e8dc;
        }

        .document-content h2:first-of-type {
            margin-top: 0;
        }

        .document-content p {
            font-size: 0.9rem;
            line-height: 1.65;
            color: #2c3e2b;
            margin-bottom: 0.75rem;
        }

        .document-content ul {
            margin: 0.75rem 0 1rem 1.5rem;
        }

        .document-content li {
            font-size: 0.9rem;
            line-height: 1.6;
            color: #2c3e2b;
            margin-bottom: 0.4rem;
        }

        .highlight-box {
            background: #f1f8ef;
            border-left: 4px solid #2d6a4f;
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            margin: 1rem 0;
        }

        .date-badge {
            display: inline-block;
            background: #e8f0e5;
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            color: #2d6a4f;
            margin-bottom: 1rem;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #1e4a2f;
            color: white;
            text-decoration: none;
            padding: 0.7rem 1.5rem;
            border-radius: 2rem;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s;
            margin-bottom: 1rem;
        }

        .back-button:hover {
            background: #14381f;
            transform: translateY(-2px);
        }

        .link-green {
            color: #2d6a4f;
            text-decoration: none;
            font-weight: 500;
        }

        .link-green:hover {
            text-decoration: underline;
        }

        .document-footer {
            text-align: center;
            padding: 1.5rem;
            border-top: 1px solid #e2e8dc;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .footer-links a {
            color: #5b7c56;
            text-decoration: none;
            font-size: 0.8rem;
        }

        .footer-links a:hover {
            color: #1e4a2f;
            text-decoration: underline;
        }

        .copyright {
            font-size: 0.7rem;
            color: #8ba382;
        }

        @media (max-width: 768px) {
            .document-wrapper {
                padding: 1.25rem;
            }
            .document-content {
                padding: 1.25rem;
            }
            .document-title {
                font-size: 1.4rem;
            }
            .document-content h2 {
                font-size: 1.15rem;
            }
        }

        @media (max-width: 480px) {
            .document-wrapper {
                padding: 1rem;
            }
            .document-content {
                padding: 1rem;
            }
            .document-content p,
            .document-content li {
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <div class="document-wrapper">
        <div>
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('login') }}" class="back-button">
                ← Kembali
            </a>
        </div>

        <div class="document-header">
            <a href="/" class="logo-link">
                <img src="https://lms.instituthijauindonesia.or.id/images/logo-light.png"
                     alt="Institut Hijau Indonesia"
                     class="logo-img">
            </a>
            <h1 class="document-title">Kebijakan Privasi</h1>
            <p class="document-subtitle">Institut Hijau Indonesia</p>
        </div>

        <div class="document-card">
            <div class="document-content">
                <div class="date-badge">Terakhir diperbarui: 23 April 2026</div>

                <p>
                    Institut Hijau Indonesia ("IHI") berkomitmen untuk melindungi data pribadi Anda.
                    Kebijakan ini menjelaskan bagaimana kami mengelola informasi yang Anda berikan saat menggunakan layanan kami.
                </p>

                <div class="highlight-box">
                    <strong>Prinsip Kami</strong><br>
                    Kami menghormati privasi Anda. Data pribadi Anda aman dan tidak akan kami jual kepada pihak manapun.
                </div>

                <h2>1. Informasi yang Kami Kumpulkan</h2>
                <p>
                    Kami mengumpulkan informasi dasar yang Anda berikan saat mendaftar, seperti nama, alamat email, dan nomor telepon.
                    Kami juga mengumpulkan data aktivitas Anda di platform untuk meningkatkan layanan pembelajaran.
                </p>

                <h2>2. Penggunaan Informasi</h2>
                <p>
                    Informasi Anda kami gunakan untuk:
                </p>
                <ul>
                    <li>Mengelola akun dan memberikan akses ke layanan pembelajaran</li>
                    <li>Mengirimkan notifikasi dan informasi penting seputar kegiatan IHI</li>
                    <li>Meningkatkan kualitas platform dan materi pembelajaran</li>
                    <li>Verifikasi keamanan akun Anda</li>
                </ul>

                <h2>3. Perlindungan Data</h2>
                <p>
                    Kami menerapkan langkah-langkah keamanan standar untuk melindungi data Anda dari akses tidak sah.
                    Kata sandi Anda disimpan dalam bentuk terenkripsi, dan kami menggunakan protokol keamanan untuk semua transmisi data.
                </p>

                <h2>4. Berbagi Data dengan Pihak Ketiga</h2>
                <p>
                    Kami tidak menjual atau menyewakan data pribadi Anda. Kami hanya dapat membagikan data dengan:
                </p>
                <ul>
                    <li>Penyedia layanan teknis yang membantu operasional platform (dengan perjanjian kerahasiaan)</li>
                    <li>Instansi berwenang jika diwajibkan oleh hukum yang berlaku</li>
                </ul>

                <h2>5. Cookies</h2>
                <p>
                    Website kami menggunakan cookies untuk meningkatkan pengalaman Anda. Cookies membantu kami mengingat preferensi Anda
                    dan menganalisis penggunaan platform. Anda dapat mengatur browser untuk menolak cookies jika diinginkan.
                </p>

                <h2>6. Hak Anda sebagai Pengguna</h2>
                <p>
                    Anda berhak untuk mengakses, memperbaiki, atau menghapus data pribadi Anda.
                    Silakan hubungi kami jika ingin menggunakan hak-hak tersebut.
                </p>

                <h2>7. Perubahan Kebijakan</h2>
                <p>
                    Kebijakan Privasi ini dapat diperbarui sewaktu-waktu. Perubahan akan diumumkan melalui platform atau email.
                    Dengan terus menggunakan layanan IHI, Anda dianggap menyetujui kebijakan yang berlaku.
                </p>

                <h2>8. Kontak Kami</h2>
                <p>
                    Jika Anda memiliki pertanyaan tentang Kebijakan Privasi ini, silakan hubungi:
                </p>
                <ul>
                    <li><strong>Email</strong> : <a href="mailto:support@instituthijau.id" class="link-green">support@instituthijau.id</a></li>
                    <li><strong>WhatsApp</strong> : +62 812-3456-7890</li>
                    <li><strong>Alamat</strong> : Jl. Palapa XVII No.3, Ps. Minggu, Jakarta Selatan 12520</li>
                </ul>

                <div class="highlight-box" style="margin-top: 1.5rem;">
                    Dengan menggunakan layanan Institut Hijau Indonesia, Anda menyetujui Kebijakan Privasi ini.
                </div>
            </div>

            <div class="document-footer">
                <div class="footer-links">
                    <a href="{{ route('syarat-ketentuan') }}">Syarat & Ketentuan</a>
                    <a href="{{ route('kebijakan-privasi') }}">Kebijakan Privasi</a>
                    <a href="/tentang-kami">Tentang Kami</a>
                    <a href="/faq">FAQ</a>
                    <a href="/kontak">Kontak</a>
                </div>
                <div class="copyright">
                    &copy; {{ date('Y') }} Institut Hijau Indonesia
                </div>
            </div>
        </div>
    </div>
</body>
</html>
