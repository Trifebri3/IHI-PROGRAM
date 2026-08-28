{{-- resources/views/auth/confirm-password.blade.php --}}
<x-guest-layout>
    <style>
        /* CSS Terisolasi untuk halaman konfirmasi password */
        .confirm-container {
            padding: 0.5rem 0;
        }

        .confirm-icon {
            width: 75px;
            height: 75px;
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
        }

        .confirm-icon svg {
            width: 40px;
            height: 40px;
            color: #16a34a;
        }

        .confirm-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1e4a2f;
            margin-bottom: 0.5rem;
            text-align: center;
        }

        .confirm-subtitle {
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

        .btn-confirm {
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

        .btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(22, 163, 74, 0.35);
            background: linear-gradient(135deg, #15803d 0%, #136935 100%);
        }

        .btn-confirm:active {
            transform: translateY(0);
        }

        .btn-confirm.loading {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
            pointer-events: none;
            position: relative;
        }

        .btn-confirm.loading span {
            opacity: 0.7;
        }

        .btn-confirm.loading::after {
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

        .alert-warning {
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
            border-radius: 1rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
            color: #92400e;
            text-align: left;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .alert-warning svg {
            flex-shrink: 0;
            margin-top: 0.1rem;
        }

        /* Responsive */
        @media (max-width: 640px) {
            .confirm-title {
                font-size: 1.2rem;
            }

            .confirm-subtitle {
                font-size: 0.8rem;
            }

            .input-field {
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
            }

            .btn-confirm {
                padding: 0.8rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .confirm-icon {
                width: 60px;
                height: 60px;
            }

            .confirm-icon svg {
                width: 32px;
                height: 32px;
            }

            .confirm-title {
                font-size: 1.1rem;
            }
        }
    </style>

    <div class="confirm-container">
        <!-- Icon Konfirmasi Password -->
        <div class="confirm-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
        </div>

        <h2 class="confirm-title">Konfirmasi Keamanan</h2>
        <p class="confirm-subtitle">
            Ini adalah area aman dari aplikasi. Harap konfirmasi kata sandi Anda sebelum melanjutkan.
        </p>

        <!-- Alert Info -->
        <div class="alert-warning">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
            <span>Untuk keamanan akun Anda, kami memerlukan verifikasi ulang kata sandi sebelum mengakses halaman ini.</span>
        </div>

        <form method="POST" action="{{ route('password.confirm') }}" id="confirmPasswordForm">
            @csrf

            <!-- Password -->
            <div class="input-group">
                <label class="form-label" for="password">Kata Sandi</label>
                <input id="password"
                       type="password"
                       name="password"
                       class="input-field @error('password') is-invalid @enderror"
                       required
                       autocomplete="current-password"
                       placeholder="Masukkan kata sandi Anda">
                @error('password')
                    <div class="input-error">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn-confirm" id="confirmBtn">
                <span>Konfirmasi & Lanjutkan</span>
            </button>

            <div class="back-link">
                <a href="{{ url()->previous() }}">
                    ← Kembali ke halaman sebelumnya
                </a>
            </div>
        </form>

        <!-- Informasi Tambahan -->
        <div class="info-box">
            <p><strong>🔒 Mengapa perlu konfirmasi ulang?</strong></p>
            <p>• Melindungi akun Anda dari akses yang tidak sah</p>
            <p>• Memastikan bahwa hanya Anda yang dapat mengubah pengaturan penting</p>
            <p>• Ini adalah prosedur keamanan standar untuk area sensitif</p>
            <p style="margin-top: 0.5rem;">• Lupa kata sandi? <a href="{{ route('password.request') }}" style="color: #16a34a; text-decoration: none; font-weight: 500;">Klik di sini untuk mereset</a></p>
        </div>
    </div>

    <script>
        (function() {
            const form = document.getElementById('confirmPasswordForm');
            const confirmBtn = document.getElementById('confirmBtn');
            const passwordInput = document.getElementById('password');

            if (form && confirmBtn) {
                form.addEventListener('submit', function(e) {
                    let hasError = false;
                    let errorMessage = '';

                    // Validasi password tidak boleh kosong
                    if (!passwordInput.value.trim()) {
                        hasError = true;
                        errorMessage = 'Kata sandi harus diisi!';
                        passwordInput.focus();
                    }

                    if (hasError) {
                        e.preventDefault();
                        showToast(errorMessage);
                        return;
                    }

                    // Tampilkan loading state
                    confirmBtn.classList.add('loading');
                    setTimeout(() => {
                        confirmBtn.disabled = true;
                    }, 50);
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
