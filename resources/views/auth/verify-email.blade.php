{{-- resources/views/auth/verify-email.blade.php --}}
<x-guest-layout>
    <style>
        /* CSS Terisolasi untuk halaman verifikasi email */
        .verify-container {
            text-align: center;
            padding: 0.5rem 0;
        }

        .verify-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .verify-icon svg {
            width: 45px;
            height: 45px;
            color: #16a34a;
        }

        .verify-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e4a2f;
            margin-bottom: 0.75rem;
        }

        .verify-message {
            color: #4b5563;
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .verify-message strong {
            color: #1e4a2f;
            font-weight: 600;
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
        }

        .button-group {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
            margin-top: 1rem;
        }

        .btn-resend {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            color: white;
            border: none;
            padding: 0.85rem 1.8rem;
            border-radius: 3rem;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(22, 163, 74, 0.25);
        }

        .btn-resend:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(22, 163, 74, 0.35);
            background: linear-gradient(135deg, #15803d 0%, #136935 100%);
        }

        .btn-resend:active {
            transform: translateY(0);
        }

        .btn-resend.loading {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
            pointer-events: none;
        }

        .btn-resend.loading span {
            opacity: 0.7;
        }

        .btn-resend.loading::after {
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

        .btn-logout {
            background: transparent;
            color: #6b7280;
            border: 1.5px solid #e5e7eb;
            padding: 0.85rem 1.8rem;
            border-radius: 3rem;
            font-weight: 500;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-logout:hover {
            background: #f9fafb;
            border-color: #d1d5db;
            color: #374151;
        }

        .info-box {
            background: #f1f8ef;
            border-radius: 1rem;
            padding: 1rem;
            margin-top: 1.5rem;
            font-size: 0.75rem;
            color: #2d6a4f;
            text-align: left;
        }

        .info-box p {
            margin-bottom: 0.5rem;
        }

        .info-box p:last-child {
            margin-bottom: 0;
        }

        /* Responsive */
        @media (max-width: 640px) {
            .verify-title {
                font-size: 1.3rem;
            }

            .verify-message {
                font-size: 0.85rem;
            }

            .button-group {
                flex-direction: column;
                gap: 0.75rem;
            }

            .btn-resend, .btn-logout {
                width: 100%;
                padding: 0.75rem 1.5rem;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .verify-icon {
                width: 65px;
                height: 65px;
            }

            .verify-icon svg {
                width: 35px;
                height: 35px;
            }

            .verify-title {
                font-size: 1.2rem;
            }
        }
    </style>

    <div class="verify-container">
        <!-- Icon Email -->
        <div class="verify-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
            </svg>
        </div>

        <h2 class="verify-title">Verifikasi Alamat Email</h2>

        <div class="verify-message">
            Terima kasih telah mendaftar di <strong>Institut Hijau Indonesia</strong>.<br>
            Sebelum memulai, harap verifikasi alamat email Anda dengan mengklik tautan yang telah kami kirimkan ke email Anda.
        </div>

        <!-- Status success -->
        @if (session('status') == 'verification-link-sent')
            <div class="alert-success">
                <svg xmlns="http://www.w3.org/2000/svg" class="inline-block w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Tautan verifikasi baru telah dikirim ke alamat email yang Anda daftarkan.
            </div>
        @endif

        <!-- Tombol Aksi -->
        <div class="button-group">
<form method="POST" action="{{ route('verification.send') }}" id="resendForm">
    @csrf
    <button type="submit" class="btn-resend" id="resendBtn">
        <span>Kirim Ulang Email Verifikasi</span>
    </button>
</form>

            <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                @csrf
                <button type="submit" class="btn-logout">
                    Keluar / Logout
                </button>
            </form>
        </div>

        </div>

        <!-- Help/Mitigation Desk -->
        @if(\App\Models\SystemSetting::getVal('mitigation_mode', '0') === '1')
            <div style="margin-top: 1.5rem; border-top: 1px dashed #e2e8f0; padding-top: 1.5rem; text-align: left;">
                <div style="background: #fffbeb; border: 1px solid #fef3c7; border-radius: 1rem; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <h3 style="font-size: 0.85rem; font-weight: 700; color: #b45309; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; margin-top: 0;">
                        <span>🆘</span> Layanan Mitigasi &amp; Bantuan
                    </h3>

                    @php
                        $pendingTicket = \App\Models\MitigationTicket::where('user_id', auth()->id())
                            ->where('status', 'pending')
                            ->first();
                    @endphp

                    @if($pendingTicket)
                        <div style="background: #f0fdf4; border: 1px solid #dcfce7; border-radius: 0.75rem; padding: 0.75rem; font-size: 0.75rem; color: #166534; margin-top: 0.5rem; line-height: 1.5;">
                            <strong>Aduan Terkirim:</strong>
                            <p style="margin-top: 0.25rem;">Kendala: 
                                <span style="font-weight: 700;">
                                    @if($pendingTicket->issue_type === 'no_email')
                                        Tidak menerima email verifikasi
                                    @elseif($pendingTicket->issue_type === 'password')
                                        Masalah password
                                    @else
                                        Lainnya
                                    @endif
                                </span>
                            </p>
                            <p style="margin-top: 0.25rem; font-style: italic;">"{{ $pendingTicket->description }}"</p>
                            <p style="margin-top: 0.5rem; font-size: 0.7rem; font-weight: 700; color: #15803d; display: flex; align-items: center; gap: 0.25rem; margin-bottom: 0;">
                                <span>⏳</span> Laporan sedang diproses oleh Admin. Mohon periksa berkala halaman ini.
                            </p>
                        </div>
                    @else
                        <!-- Form to submit a new ticket -->
                        <p style="font-size: 0.75rem; color: #6b7280; line-height: 1.5; margin-bottom: 0.75rem; margin-top: 0;">
                            Jika Anda tidak mendapatkan email verifikasi setelah beberapa kali kirim ulang, atau mengalami kendala lain, silakan laporkan di bawah untuk verifikasi manual oleh Admin.
                        </p>

                        <!-- Alerts for success/error inside the card -->
                        @if(session('success'))
                            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; border-radius: 0.75rem; padding: 0.75rem; font-size: 0.75rem; margin-bottom: 0.75rem;">
                                {{ session('success') }}
                            </div>
                        @endif
                        @if(session('error'))
                            <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 0.75rem; padding: 0.75rem; font-size: 0.75rem; margin-bottom: 0.75rem;">
                                {{ session('error') }}
                            </div>
                        @endif

                        <form action="{{ route('verification.mitigation.store') }}" method="POST">
                            @csrf
                            <div>
                                <label style="display: block; font-size: 10px; font-weight: 700; color: #4b5563; text-transform: uppercase; margin-bottom: 0.25rem;">Pilih Jenis Kendala</label>
                                <select name="issue_type" required style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.75rem; color: #374151; background-color: #fff; outline: none; margin-bottom: 0.75rem;">
                                    <option value="no_email">Tidak menerima email verifikasi</option>
                                    <option value="password">Masalah password</option>
                                    <option value="other">Masalah lainnya / kendala teknis</option>
                                </select>
                            </div>

                            <div>
                                <label style="display: block; font-size: 10px; font-weight: 700; color: #4b5563; text-transform: uppercase; margin-bottom: 0.25rem;">Rincian Keluhan / Kontak WA Aktif</label>
                                <textarea name="description" required placeholder="Tulis rincian kendala Anda di sini..." style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.75rem; color: #374151; outline: none; min-height: 60px; margin-bottom: 0.75rem; box-sizing: border-box;"></textarea>
                            </div>

                            <button type="submit" style="width: 100%; background: #d97706; color: white; border: none; padding: 0.6rem; border-radius: 2rem; font-weight: 700; font-size: 0.75rem; cursor: pointer; transition: background 0.2s;">
                                Kirim Laporan Bantuan &rarr;
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endif
    </div>

<script>
    (function() {
        const resendForm = document.getElementById('resendForm');
        const resendBtn = document.getElementById('resendBtn');
        const logoutForm = document.getElementById('logoutForm');

        if (resendForm && resendBtn) {
            resendForm.addEventListener('submit', function(e) {
                // Menghindari double submit
                if (resendBtn.classList.contains('loading')) return;

                // Transisi teks yang halus
                const originalText = resendBtn.querySelector('span');
                if (originalText) {
                    originalText.style.opacity = '0.5';
                    originalText.innerText = 'Mengirim Tautan...';
                }

                // Tambahkan class loading (untuk spinner CSS Anda)
                resendBtn.classList.add('loading');
                resendBtn.style.pointerEvents = 'none'; // Lock interaksi

                return true;
            });
        }

        // Logout dengan UI Confirm yang lebih clean
        if (logoutForm) {
            logoutForm.addEventListener('submit', function(e) {
                const confirmLogout = confirm('Anda akan keluar dari sesi verifikasi. Lanjutkan?');
                if (!confirmLogout) {
                    e.preventDefault();
                }
            });
        }
    })();
</script>
</x-guest-layout>
