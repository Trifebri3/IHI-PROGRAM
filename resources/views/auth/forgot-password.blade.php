{{-- resources/views/auth/forgot-password.blade.php --}}
<x-guest-layout>
    <style>
        /* CSS Terisolasi untuk halaman lupa password */
        .forgot-container {
            padding: 0.5rem 0;
        }

        .forgot-icon {
            width: 75px;
            height: 75px;
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
        }

        .forgot-icon svg {
            width: 40px;
            height: 40px;
            color: #16a34a;
        }

        .forgot-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1e4a2f;
            margin-bottom: 0.5rem;
            text-align: center;
        }

        .forgot-subtitle {
            color: #6b7280;
            font-size: 0.85rem;
            text-align: center;
            margin-bottom: 1.75rem;
            line-height: 1.5;
        }

        .input-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            margin-left: 0.25rem;
        }

        .input-field {
            width: 100%;
            padding: 0.85rem 1.1rem;
            background: #fdfdfd;
            border: 1.5px solid #e5e7eb;
            border-radius: 1rem;
            font-size: 0.95rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            color: #1f2937;
        }

        .input-field:focus {
            outline: none;
            background: #ffffff;
            border-color: #22c55e;
            box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.08);
            transform: translateY(-1px);
        }

        .input-error {
            color: #dc2626;
            font-size: 0.7rem;
            margin-top: 0.4rem;
            margin-left: 0.25rem;
        }

        .alert-success {
            background: #f0fdf4;
            border-left: 4px solid #22c55e;
            border-radius: 1rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
            color: #166534;
            text-align: left;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .alert-success svg {
            flex-shrink: 0;
            margin-top: 0.1rem;
        }

        .btn-submit {
            width: 100%;
            padding: 0.9rem;
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            color: white;
            border: none;
            border-radius: 3rem;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(22, 163, 74, 0.25);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(22, 163, 74, 0.35);
            background: linear-gradient(135deg, #15803d 0%, #136935 100%);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit.loading {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
            pointer-events: none;
            position: relative;
        }

        .btn-submit.loading span {
            opacity: 0.7;
        }

        .btn-submit.loading::after {
            content: '';
            display: inline-block;
            width: 1rem;
            height: 1rem;
            margin-left: 0.6rem;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            vertical-align: middle;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .back-link {
            text-align: center;
            margin-top: 1.5rem;
        }

        .back-link a {
            color: #6b7280;
            text-decoration: none;
            font-size: 0.8rem;
            transition: color 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .back-link a:hover {
            color: #16a34a;
            text-decoration: underline;
        }

        .info-box {
            background: #f1f8ef;
            border-radius: 1rem;
            padding: 0.9rem 1rem;
            margin-top: 1.5rem;
            font-size: 0.75rem;
            color: #2d6a4f;
            text-align: left;
        }

        .info-box p {
            margin-bottom: 0.25rem;
        }

        .info-box p:last-child {
            margin-bottom: 0;
        }

        /* Responsive */
        @media (max-width: 640px) {
            .forgot-title {
                font-size: 1.2rem;
            }

            .forgot-subtitle {
                font-size: 0.8rem;
            }

            .input-field {
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
            }

            .btn-submit {
                padding: 0.8rem;
                font-size: 0.9rem;
            }

            .alert-success {
                font-size: 0.8rem;
                padding: 0.85rem;
            }
        }

        @media (max-width: 480px) {
            .forgot-icon {
                width: 60px;
                height: 60px;
            }

            .forgot-icon svg {
                width: 32px;
                height: 32px;
            }

            .forgot-title {
                font-size: 1.1rem;
            }
        }
    </style>

    <div class="forgot-container">
        <!-- Icon Lupa Password -->
        <div class="forgot-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
            </svg>
        </div>

        <h2 class="forgot-title">Lupa Kata Sandi?</h2>
        <p class="forgot-subtitle">
            Tidak masalah. Masukkan alamat email Anda dan kami akan mengirimkan tautan untuk mereset kata sandi.
        </p>

        <!-- Session Status (Success Message) -->
        @if (session('status'))
            <div class="alert-success">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Tautan reset kata sandi telah dikirim ke email Anda. Silakan periksa kotak masuk atau folder spam.</span>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" id="forgotPasswordForm">
            @csrf

            <!-- Email Address -->
            <div class="input-group">
                <label class="form-label" for="email">Alamat Email</label>
                <input id="email"
                       type="email"
                       name="email"
                       class="input-field @error('email') is-invalid @enderror"
                       value="{{ old('email') }}"
                       required autofocus
                       placeholder="nama@email.com">
                @error('email')
                    <div class="input-error">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn-submit" id="submitBtn">
                <span>Kirim Tautan Reset Password</span>
            </button>

            <div class="back-link">
                <a href="{{ route('login') }}">
                    ← Kembali ke halaman login
                </a>
            </div>
        </form>

        <!-- Informasi Tambahan -->
        <div class="info-box">
            <p><strong>Belum menerima email?</strong></p>
            <p>• Periksa folder <strong>Spam</strong> atau <strong>Junk</strong> di email Anda</p>
            <p>• Pastikan alamat email yang Anda masukkan sudah benar</p>
            <p>• Tambahkan <strong>support@instituthijau.id</strong> ke kontak Anda</p>
            <p>• Tunggu beberapa menit, lalu coba kirim ulang permintaan</p>
        </div>
    </div>

    <script>
        (function() {
            const form = document.getElementById('forgotPasswordForm');
            const submitBtn = document.getElementById('submitBtn');
            const emailInput = document.getElementById('email');

            if (form && submitBtn) {
                form.addEventListener('submit', function(e) {
                    let hasError = false;
                    let errorMessage = '';

                    // Validasi email tidak boleh kosong
                    if (!emailInput.value.trim()) {
                        hasError = true;
                        errorMessage = 'Alamat email harus diisi!';
                        emailInput.focus();
                    }

                    // Validasi format email sederhana
                    if (!hasError && emailInput.value.trim()) {
                        const emailPattern = /^[^\s@]+@([^\s@]+\.)+[^\s@]+$/;
                        if (!emailPattern.test(emailInput.value.trim())) {
                            hasError = true;
                            errorMessage = 'Format email tidak valid! Contoh: nama@domain.com';
                            emailInput.focus();
                        }
                    }

                    if (hasError) {
                        e.preventDefault();
                        showToast(errorMessage);
                        return;
                    }

                    // Tampilkan loading state
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                });
            }

            // Fungsi toast notifikasi error
            function showToast(message) {
                const existingToast = document.querySelector('.toast-error');
                if (existingToast) existingToast.remove();

                const toast = document.createElement('div');
                toast.className = 'toast-error';
                toast.textContent = message;
                toast.style.cssText = `
                    position: fixed;
                    bottom: 2rem;
                    left: 50%;
                    transform: translateX(-50%);
                    background: #ef4444;
                    color: white;
                    padding: 0.75rem 1.5rem;
                    border-radius: 3rem;
                    font-size: 0.85rem;
                    font-weight: 500;
                    z-index: 10000;
                    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
                    animation: slideUp 0.3s ease-out;
                    white-space: nowrap;
                `;

                // Tambahkan style animasi jika belum ada
                if (!document.querySelector('#toast-style')) {
                    const style = document.createElement('style');
                    style.id = 'toast-style';
                    style.textContent = `
                        @keyframes slideUp {
                            from {
                                opacity: 0;
                                transform: translateX(-50%) translateY(20px);
                            }
                            to {
                                opacity: 1;
                                transform: translateX(-50%) translateY(0);
                            }
                        }
                    `;
                    document.head.appendChild(style);
                }

                document.body.appendChild(toast);

                setTimeout(() => {
                    toast.style.opacity = '0';
                    setTimeout(() => toast.remove(), 300);
                }, 3000);
            }
        })();
    </script>
</x-guest-layout>
