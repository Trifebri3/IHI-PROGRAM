{{-- resources/views/layouts/guest.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Institut Hijau Indonesia</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <style>
        /* RESET & GLOBAL - Responsive penuh, tanpa batasan kontainer kaku */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Figtree', 'Inter', system-ui, -apple-system, sans-serif;
            background: linear-gradient(145deg, #f7faf5 0%, #eef3ea 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Layout utama - FLEXIBLE, TIDAK DIKUNCI */
        .auth-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
            width: 100%;
        }

        /* Logo - responsif */
        .logo-container {
            text-align: center;
            margin-bottom: 1.75rem;
            transition: transform 0.25s ease;
        }

        .logo-container:hover {
            transform: scale(1.02);
        }

        .logo-img {
            height: auto;
            width: min(140px, 25vw);
            max-height: 80px;
            object-fit: contain;
        }

        /* Card utama - LEBAR MENGIKUTI, TIDAK DIPAKSA */
        .auth-card {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 2rem;
            box-shadow: 0 25px 45px -12px rgba(0, 0, 0, 0.15), 0 1px 2px rgba(0, 0, 0, 0.03);
            transition: all 0.3s cubic-bezier(0.2, 0, 0, 1);
            border: 1px solid rgba(100, 130, 80, 0.15);
        }

        .auth-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 30px 50px -15px rgba(0, 0, 0, 0.2);
        }

        /* Slot padding - nyaman di semua layar */
        .card-content {
            padding: 2rem 1.75rem;
        }

        /* Animasi masuk */
        @keyframes fadeSlideUp {
            from {
                opacity: 0;
                transform: translateY(24px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .auth-card {
            animation: fadeSlideUp 0.45s ease-out;
        }

        /* Footer responsif */
        .auth-footer {
            text-align: center;
            margin-top: 2rem;
            padding: 1rem;
            font-size: 0.75rem;
            color: #6b7a64;
            width: 100%;
        }

        /* ========== KOMPONEN FORM YANG BAKAL DIPAKAI DI SLOT ========== */
        /* Input group premium */
        .input-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #2c3e2b;
            margin-bottom: 0.5rem;
            letter-spacing: -0.2px;
        }

        .input-field {
            width: 100%;
            padding: 0.9rem 1.2rem;
            background-color: #fbfdf9;
            border: 1.5px solid #e2e8dc;
            border-radius: 1.25rem;
            font-size: 0.95rem;
            transition: all 0.2s;
            outline: none;
            font-family: inherit;
        }

        .input-field:focus {
            border-color: #2d6a4f;
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(45, 106, 79, 0.12);
        }

        /* Group nomor telepon */
        .phone-group {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .phone-group select {
            width: auto;
            min-width: 95px;
            flex-shrink: 0;
            cursor: pointer;
            background-color: #fbfdf9;
        }

        .phone-group .input-field {
            flex: 1;
            min-width: 160px;
        }

        /* Tombol utama hijau */
        .btn-green {
            width: 100%;
            background: linear-gradient(105deg, #1e4a2f 0%, #2d6a4f 100%);
            border: none;
            padding: 0.95rem;
            border-radius: 2rem;
            font-weight: 700;
            font-size: 1rem;
            color: white;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 0.5rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }

        .btn-green:hover {
            transform: scale(0.98);
            background: linear-gradient(105deg, #14381f 0%, #1f563d 100%);
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.1);
        }

        /* link hijau */
        .link-green {
            color: #2d6a4f;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
            border-bottom: 1px solid transparent;
        }

        .link-green:hover {
            border-bottom-color: #2d6a4f;
        }

        /* Badge informasi */
        .info-badge {
            background: #eef6ea;
            border-left: 4px solid #2d6a4f;
            border-radius: 1rem;
            padding: 0.9rem 1.2rem;
            font-size: 0.75rem;
            color: #2b4b2a;
            margin-top: 1.5rem;
            line-height: 1.4;
        }

        /* checkbox custom */
        .checkbox-custom {
            accent-color: #2d6a4f;
            width: 1.1rem;
            height: 1.1rem;
            margin-right: 0.5rem;
        }

        /* Responsive total - tidak ada max-width yang ngejepit konten, bebas napas */
        @media (max-width: 640px) {
            .auth-wrapper {
                padding: 1.25rem;
                justify-content: flex-start;
                padding-top: 2rem;
            }
            .card-content {
                padding: 1.5rem 1.25rem;
            }
            .auth-card {
                border-radius: 1.5rem;
            }
            .btn-green {
                padding: 0.85rem;
            }
            .phone-group {
                flex-direction: column;
                gap: 0.6rem;
            }
            .phone-group select {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .card-content {
                padding: 1.25rem;
            }
            .input-field {
                padding: 0.8rem 1rem;
                font-size: 0.9rem;
            }
        }

        /* Toast notifikasi smooth */
        .toast-message {
            position: fixed;
            bottom: 1.5rem;
            left: 50%;
            transform: translateX(-50%);
            background: #1f2e1c;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 3rem;
            font-size: 0.8rem;
            font-weight: 500;
            z-index: 1000;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(8px);
            background: rgba(30, 50, 28, 0.92);
            max-width: 85vw;
            text-align: center;
            white-space: nowrap;
        }

        @media (max-width: 500px) {
            .toast-message {
                white-space: normal;
                max-width: 90vw;
                font-size: 0.75rem;
            }
        }

        /* Efek Loading Overlay */
[x-cloak] { display: none !important; }

.loading-overlay {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(4px);
    transition: all 0.3s ease;
}

/* Animasi Spinner Hijau IHI */
.spinner {
    width: 40px;
    height: 40px;
    border: 3px solid rgba(34, 197, 94, 0.1);
    border-top: 3px solid #16a34a;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Animasi saat tombol ditekan */
.btn-loading {
    position: relative;
    color: transparent !important;
    pointer-events: none;
}

.btn-loading::after {
    content: "";
    position: absolute;
    width: 20px;
    height: 20px;
    top: 50%;
    left: 50%;
    margin-top: -10px;
    margin-left: -10px;
    border: 2px solid rgba(255,255,255,0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}

    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('components.pwa-meta')
</head>
<body>
    <div class="auth-wrapper">
        <!-- Logo Institut Hijau Indonesia -->
        <div class="logo-container">
            <a href="/">
                <img src="{{ asset('images/logo.webp') }}"
                     alt="Institut Hijau Indonesia"
                     class="logo-img">
            </a>
        </div>

        <!-- Card utama untuk slot -->
        <div class="auth-card">
            <div class="card-content">
                {{ $slot }}
            </div>
        </div>

        <!-- Footer -->
        <div class="auth-footer">
            &copy; {{ date('Y') }} Institut Hijau Indonesia. Seluruh hak cipta dilindungi.
        </div>
    </div>

    <script>
        (function() {
            document.addEventListener('DOMContentLoaded', function() {
                // Fungsi toast notifikasi tanpa emoji
                window.toast = function(pesan, tipe = 'info') {
                    let existing = document.querySelector('.toast-message');
                    if (existing) existing.remove();

                    let toast = document.createElement('div');
                    toast.className = 'toast-message';

                    let icon = '';
                    if (tipe === 'success') icon = '✓ ';
                    else if (tipe === 'error') icon = '✗ ';
                    else if (tipe === 'warning') icon = '! ';

                    toast.innerText = icon + pesan;
                    document.body.appendChild(toast);

                    setTimeout(() => {
                        toast.style.opacity = '0';
                        setTimeout(() => toast.remove(), 300);
                    }, 2800);
                };

                // Validasi frontend untuk form login & register (jika ada di dalam slot)
                const loginForm = document.getElementById('loginForm');
                const registerForm = document.getElementById('registerForm');

                if (loginForm) {
                    loginForm.addEventListener('submit', function(e) {
                        let email = document.getElementById('email');
                        let password = document.getElementById('password');
                        if (email && !email.value.trim()) {
                            e.preventDefault();
                            toast('Email harus diisi', 'warning');
                            email.focus();
                        } else if (password && !password.value.trim()) {
                            e.preventDefault();
                            toast('Kata sandi harus diisi', 'warning');
                            password.focus();
                        }
                    });
                }

                if (registerForm) {
                    registerForm.addEventListener('submit', function(e) {
                        let name = document.getElementById('name');
                        let email = document.getElementById('email');
                        let phone = document.getElementById('phone');
                        let pin = document.getElementById('pin');
                        let pinConfirm = document.getElementById('pin_confirmation');

                        if (name && !name.value.trim()) {
                            e.preventDefault();
                            toast('Nama lengkap wajib diisi', 'warning');
                            name.focus();
                        } else if (email && !email.value.trim()) {
                            e.preventDefault();
                            toast('Alamat email wajib diisi', 'warning');
                            email.focus();
                        } else if (phone && !phone.value.trim()) {
                            e.preventDefault();
                            toast('Nomor telepon wajib diisi', 'warning');
                            phone.focus();
                        } else if (pin && pin.value.length !== 6) {
                            e.preventDefault();
                            toast('PIN harus 6 digit angka', 'warning');
                            pin.focus();
                        } else if (pinConfirm && pinConfirm.value !== pin.value) {
                            e.preventDefault();
                            toast('Konfirmasi PIN tidak cocok', 'warning');
                            pinConfirm.focus();
                        }
                    });
                }

                // Tab switching jika ada (opsional buat halaman yang pakai tab login/register)
                const loginTab = document.getElementById('loginTabBtn');
                const registerTab = document.getElementById('registerTabBtn');
                const loginPanel = document.getElementById('loginPanel');
                const registerPanel = document.getElementById('registerPanel');

                if (loginTab && registerTab && loginPanel && registerPanel) {
                    const aktifkanTab = (tab) => {
                        if (tab === 'login') {
                            loginTab.classList.add('active-tab');
                            registerTab.classList.remove('active-tab');
                            loginPanel.classList.remove('hidden-panel');
                            registerPanel.classList.add('hidden-panel');
                        } else {
                            registerTab.classList.add('active-tab');
                            loginTab.classList.remove('active-tab');
                            registerPanel.classList.remove('hidden-panel');
                            loginPanel.classList.add('hidden-panel');
                        }
                    };
                    loginTab.addEventListener('click', (e) => { e.preventDefault(); aktifkanTab('login'); });
                    registerTab.addEventListener('click', (e) => { e.preventDefault(); aktifkanTab('register'); });
                }

                // filter input PIN hanya angka
                const pinInput = document.getElementById('pin');
                if (pinInput) {
                    pinInput.addEventListener('input', function() {
                        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
                    });
                }
                const pinConfirmInput = document.getElementById('pin_confirmation');
                if (pinConfirmInput) {
                    pinConfirmInput.addEventListener('input', function() {
                        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
                    });
                }
            });
        })();
    </script>
</body>
</html>
