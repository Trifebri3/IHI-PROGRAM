{{-- resources/views/auth/reset-password.blade.php --}}
<x-guest-layout>
    <style>
        /* CSS Terisolasi untuk halaman reset password */
        .reset-container {
            padding: 0.5rem 0;
        }

        .reset-icon {
            width: 75px;
            height: 75px;
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
        }

        .reset-icon svg {
            width: 40px;
            height: 40px;
            color: #16a34a;
        }

        .reset-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1e4a2f;
            margin-bottom: 0.5rem;
            text-align: center;
        }

        .reset-subtitle {
            color: #6b7280;
            font-size: 0.85rem;
            text-align: center;
            margin-bottom: 1.75rem;
        }

        .input-group {
            margin-bottom: 1.25rem;
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

        .btn-reset {
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
            margin-top: 0.5rem;
        }

        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(22, 163, 74, 0.35);
            background: linear-gradient(135deg, #15803d 0%, #136935 100%);
        }

        .btn-reset:active {
            transform: translateY(0);
        }

        .btn-reset.loading {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
            pointer-events: none;
            position: relative;
        }

        .btn-reset.loading span {
            opacity: 0.7;
        }

        .btn-reset.loading::after {
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
            .reset-title {
                font-size: 1.2rem;
            }

            .reset-subtitle {
                font-size: 0.8rem;
            }

            .input-field {
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
            }

            .btn-reset {
                padding: 0.8rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .reset-icon {
                width: 60px;
                height: 60px;
            }

            .reset-icon svg {
                width: 32px;
                height: 32px;
            }

            .reset-title {
                font-size: 1.1rem;
            }
        }
    </style>

    <div class="reset-container">
        <!-- Icon Reset Password -->
        <div class="reset-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
            </svg>
        </div>

        <h2 class="reset-title">Atur Ulang Kata Sandi</h2>
        <p class="reset-subtitle">Buat kata sandi baru untuk akun Anda</p>

        <form method="POST" action="{{ route('password.store') }}" id="resetPasswordForm">
            @csrf

            <!-- Password Reset Token -->
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <!-- Email Address -->
            <div class="input-group">
                <label class="form-label" for="email">Alamat Email</label>
                <input id="email"
                       type="email"
                       name="email"
                       class="input-field @error('email') is-invalid @enderror"
                       value="{{ old('email', $request->email) }}"
                       required autofocus autocomplete="username"
                       placeholder="nama@email.com">
                @error('email')
                    <div class="input-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div class="input-group">
                <label class="form-label" for="password">Kata Sandi Baru</label>
                <input id="password"
                       type="password"
                       name="password"
                       class="input-field @error('password') is-invalid @enderror"
                       required autocomplete="new-password"
                       placeholder="Minimal 8 karakter">
                @error('password')
                    <div class="input-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="input-group">
                <label class="form-label" for="password_confirmation">Konfirmasi Kata Sandi Baru</label>
                <input id="password_confirmation"
                       type="password"
                       name="password_confirmation"
                       class="input-field"
                       required autocomplete="new-password"
                       placeholder="Ulangi kata sandi baru">
            </div>

            <button type="submit" class="btn-reset" id="resetBtn">
                <span>Atur Ulang Kata Sandi</span>
            </button>

            <div class="back-link">
                <a href="{{ route('login') }}">← Kembali ke halaman login</a>
            </div>
        </form>

        <!-- Informasi Tambahan -->
        <div class="info-box">
            <p><strong>Tips Keamanan Kata Sandi:</strong></p>
            <p>• Gunakan minimal 8 karakter</p>
            <p>• Kombinasikan huruf besar, huruf kecil, angka, dan simbol</p>
            <p>• Jangan gunakan kata sandi yang sama dengan akun lain</p>
        </div>
    </div>

    <script>
        (function() {
            const form = document.getElementById('resetPasswordForm');
            const resetBtn = document.getElementById('resetBtn');
            const password = document.getElementById('password');
            const passwordConfirm = document.getElementById('password_confirmation');

            if (form && resetBtn) {
                form.addEventListener('submit', function(e) {
                    let hasError = false;
                    let errorMessage = '';

                    // Validasi password match
                    if (password.value !== passwordConfirm.value) {
                        hasError = true;
                        errorMessage = 'Konfirmasi kata sandi tidak cocok!';
                        passwordConfirm.focus();
                    }

                    // Validasi panjang password minimal 8 karakter
                    if (!hasError && password.value.length < 8) {
                        hasError = true;
                        errorMessage = 'Kata sandi minimal 8 karakter!';
                        password.focus();
                    }

                    // Validasi password tidak boleh kosong
                    if (!hasError && password.value.trim() === '') {
                        hasError = true;
                        errorMessage = 'Kata sandi tidak boleh kosong!';
                        password.focus();
                    }

                    if (hasError) {
                        e.preventDefault();
                        showToast(errorMessage);
                        return;
                    }

                    // Tampilkan loading state
                    resetBtn.classList.add('loading');
                    setTimeout(() => {
                        resetBtn.disabled = true;
                    }, 50);
                });
            }

            // Fungsi toast notifikasi
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

                const style = document.createElement('style');
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

                document.body.appendChild(toast);

                setTimeout(() => {
                    toast.style.opacity = '0';
                    setTimeout(() => toast.remove(), 300);
                }, 3000);
            }
        })();
    </script>
</x-guest-layout>
