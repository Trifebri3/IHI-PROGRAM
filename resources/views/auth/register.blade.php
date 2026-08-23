{{-- resources/views/auth/register.blade.php --}}
<x-guest-layout>
    <style>
        /* CSS MANDIRI TERISOLASI UNTUK FORM MAHAL - RESPONSIF */
        .input-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            margin-left: 0.25rem;
            transition: color 0.3s ease;
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

        textarea.input-field {
            resize: vertical;
            min-height: 80px;
        }

        .phone-group {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .phone-group select {
            width: auto;
            min-width: 100px;
            flex-shrink: 0;
        }

        .phone-group .input-field {
            flex: 1;
            min-width: 150px;
        }

        .btn-green {
            width: 100%;
            padding: 1rem;
            background: #16a34a;
            color: white;
            border: none;
            border-radius: 1.1rem;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.4s ease;
            box-shadow: 0 10px 20px -5px rgba(22, 163, 74, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-green:hover {
            background: #15803d;
            transform: translateY(-2px);
            box-shadow: 0 15px 25px -5px rgba(22, 163, 74, 0.4);
        }

        .btn-green:active {
            transform: translateY(0);
        }

        /* Loading state pada tombol */
        .btn-green.loading {
            opacity: 0.8;
            cursor: not-allowed;
            transform: none;
            pointer-events: none;
        }

        .btn-green.loading .btn-text {
            opacity: 0.7;
        }

        .btn-green.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 1.2rem;
            height: 1.2rem;
            margin-left: -0.6rem;
            margin-top: -0.6rem;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .link-green {
            color: #16a34a;
            text-decoration: none;
            transition: all 0.3s;
        }

        .link-green:hover {
            color: #15803d;
            text-decoration: underline;
        }

        .checkbox-custom {
            width: 1.2rem;
            height: 1.2rem;
            border-radius: 0.4rem;
            border: 2px solid #e5e7eb;
            accent-color: #16a34a;
            flex-shrink: 0;
        }

        .info-badge {
            margin-top: 2.5rem;
            padding: 1.25rem;
            background: #f0fdf4;
            border-left: 4px solid #22c55e;
            border-radius: 1rem;
            color: #166534;
            font-size: 0.85rem;
        }

        .photo-upload-zone {
            border: 2px dashed #cbd5e1;
            border-radius: 1rem;
            padding: 1.25rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #fafafa;
            display: block;
            width: 100%;
        }

        .photo-upload-zone:hover {
            border-color: #22c55e;
            background: #f0fdf4;
        }

        .photo-preview {
            margin-top: 1rem;
            text-align: center;
            display: none;
        }

        .photo-preview img {
            max-width: 100px;
            max-height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #22c55e;
            padding: 3px;
        }

        .file-name {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.5rem;
            display: block;
            word-break: break-all;
        }

        /* Grid responsif */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        /* LOADING OVERLAY - ANIMASI FULLSCREEN */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            visibility: hidden;
            opacity: 0;
            transition: visibility 0.3s, opacity 0.3s;
        }

        .loading-overlay.active {
            visibility: visible;
            opacity: 1;
        }

        .loading-card {
            background: white;
            border-radius: 2rem;
            padding: 2rem 2.5rem;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: fadeInScale 0.3s ease-out;
            max-width: 90%;
            width: 320px;
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Spinner animasi */
        .spinner-circle {
            width: 60px;
            height: 60px;
            margin: 0 auto 1.5rem;
            position: relative;
        }

        .spinner-circle .circle {
            width: 100%;
            height: 100%;
            border: 4px solid #e5e7eb;
            border-top-color: #16a34a;
            border-right-color: #22c55e;
            border-radius: 50%;
            animation: spinCircle 0.8s linear infinite;
        }

        @keyframes spinCircle {
            to { transform: rotate(360deg); }
        }

        /* Pulsa animasi */
        .pulse-text {
            animation: pulse 1.5s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.6; }
            50% { opacity: 1; }
        }

        /* Progress bar animasi */
        .progress-steps {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1.5rem;
        }

        .progress-dot {
            width: 8px;
            height: 8px;
            background: #d1d5db;
            border-radius: 50%;
            animation: dotPulse 1.4s ease-in-out infinite;
        }

        .progress-dot:nth-child(1) { animation-delay: 0s; }
        .progress-dot:nth-child(2) { animation-delay: 0.2s; }
        .progress-dot:nth-child(3) { animation-delay: 0.4s; }
        .progress-dot:nth-child(4) { animation-delay: 0.6s; }
        .progress-dot:nth-child(5) { animation-delay: 0.8s; }

        @keyframes dotPulse {
            0%, 100% {
                background: #d1d5db;
                transform: scale(1);
            }
            50% {
                background: #16a34a;
                transform: scale(1.2);
            }
        }

        /* Responsive breakpoints */
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .phone-group {
                flex-direction: column;
            }

            .phone-group select {
                width: 100%;
            }

            .input-field {
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
            }

            .btn-green {
                padding: 0.85rem;
                font-size: 0.95rem;
            }

            .info-badge {
                padding: 1rem;
                font-size: 0.8rem;
            }

            .loading-card {
                padding: 1.5rem;
                width: 280px;
            }
        }

        @media (max-width: 480px) {
            .input-group {
                margin-bottom: 1rem;
            }

            .form-label {
                font-size: 0.8rem;
            }

            .photo-upload-zone {
                padding: 0.9rem;
            }
        }

        /* Toast notification */
        .toast-error {
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
        }

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
    </style>

    <div class="mb-8 text-center">
        <h2 class="text-2xl font-bold text-gray-900">Registrasi Akun IHI</h2>
        <p class="text-gray-500 text-sm mt-1">Bergabunglah dengan ekosistem pendidikan hijau kami</p>
    </div>

    <form method="POST" action="{{ route('register') }}" id="registerForm" enctype="multipart/form-data">
        @csrf

        <input type="hidden" name="continue" value="{{ request('continue') }}">

        <div class="form-grid">
            <div class="input-group">
                <label class="form-label" for="name">Nama Lengkap</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required
                       class="input-field" placeholder="Contoh: Budi Santoso">
                @error('name') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="input-group">
                <label class="form-label" for="username">Username</label>
                <input id="username" type="text" name="username" value="{{ old('username') }}" required
                       class="input-field" placeholder="budisnt_99">
                @error('username') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="input-group">
            <label class="form-label" for="email">Alamat Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required
                   class="input-field" placeholder="budi@email.com">
            @error('email') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="input-group">
            <label class="form-label" for="phone_number">Nomor Telepon</label>
            <div class="phone-group">
                <select name="country_code" class="input-field" style="width: auto; min-width: 100px;">
                    <option value="+62">+62 (Indonesia)</option>
                    <option value="+60">+60 (Malaysia)</option>
                    <option value="+65">+65 (Singapura)</option>
                    <option value="+61">+61 (Australia)</option>
                    <option value="+1">+1 (Amerika Serikat)</option>
                </select>
                <input id="phone_number" type="tel" name="phone_number" value="{{ old('phone_number') }}" required
                       class="input-field" placeholder="812 3456 7890">
            </div>
            @error('phone_number') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="input-group">
            <label class="form-label" for="address">Alamat Domisili</label>
            <textarea id="address" name="address" rows="2" class="input-field" placeholder="Jl. Hijau No. 12, Bandung">{{ old('address') }}</textarea>
            @error('address') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="input-group">
            <label class="form-label">Foto Profil</label>
            <label class="photo-upload-zone" id="uploadZone">
                <input type="file" name="profile_photo" id="profilePhoto" accept="image/*" style="display: none;">
                <span id="uploadText" class="text-sm text-gray-500">Klik atau taruh foto di sini (Max 2MB)</span>
                <span id="fileName" class="file-name"></span>
            </label>
            <div id="photoPreview" class="photo-preview">
                <img id="previewImg" src="#" alt="Preview Foto">
            </div>
            @error('profile_photo') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="form-grid">
            <div class="input-group">
                <label class="form-label" for="password">Kata Sandi</label>
                <input id="password" type="password" name="password" required
                       class="input-field" placeholder="••••••••">
                @error('password') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="input-group">
                <label class="form-label" for="password_confirmation">Konfirmasi Kata Sandi</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required
                       class="input-field" placeholder="••••••••">
            </div>
        </div>

        <div class="input-group mb-6">
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="terms" id="terms" value="1" required class="checkbox-custom mt-1">
                <span class="text-xs text-gray-600 leading-relaxed">
                    Saya menyetujui <a href="{{ route('syarat-ketentuan') }}" class="link-green font-bold" target="_blank">Syarat & Ketentuan</a> serta
                    <a href="{{ route('kebijakan-privasi') }}" class="link-green font-bold" target="_blank">Kebijakan Privasi</a>.
                    Data yang saya berikan akan dienkripsi secara aman.
                </span>
            </label>
            @error('terms') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn-green" id="submitBtn">
            <span class="btn-text">Daftar Akun IHI</span>
        </button>

        <div class="text-center mt-8 pt-6 border-t border-gray-100 text-sm text-gray-600">
            Sudah punya akun?
            <a href="{{ route('login', ['continue' => request('continue')]) }}" class="link-green font-bold">Masuk di sini</a>
        </div>
    </form>

    <div class="info-badge">
        <div class="font-bold mb-1 text-green-800">Keamanan Data Terjamin</div>
        <div class="leading-relaxed">
            Seluruh data sensitif Anda (Nomor Telepon & Alamat) akan dienkripsi secara otomatis menggunakan standar industri sebelum disimpan di database kami.
        </div>
    </div>

    <!-- LOADING OVERLAY ANIMATION -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-card">
            <div class="spinner-circle">
                <div class="circle"></div>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">Memproses Pendaftaran</h3>
            <p id="loadingMessage" class="text-sm text-gray-500">Mengamankan data Anda...</p>
            <div class="progress-steps">
                <div class="progress-dot"></div>
                <div class="progress-dot"></div>
                <div class="progress-dot"></div>
                <div class="progress-dot"></div>
                <div class="progress-dot"></div>
            </div>
            <p class="text-xs text-gray-400 mt-4 pulse-text">Mohon tunggu, jangan tutup halaman ini</p>
        </div>
    </div>

    <script>
        (function() {
            // ========== UPLOAD FOTO FUNCTIONALITY ==========
            const fileInput = document.getElementById('profilePhoto');
            const uploadZone = document.getElementById('uploadZone');
            const uploadText = document.getElementById('uploadText');
            const fileNameSpan = document.getElementById('fileName');
            const previewDiv = document.getElementById('photoPreview');
            const previewImg = document.getElementById('previewImg');

            if (uploadZone) {
                uploadZone.addEventListener('click', function(e) {
                    if (e.target !== fileInput) {
                        fileInput.click();
                    }
                });
            }

            if (fileInput) {
                fileInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        if (file.size > 2 * 1024 * 1024) {
                            alert('Ukuran file terlalu besar! Maksimal 2MB.');
                            fileInput.value = '';
                            fileNameSpan.textContent = '';
                            previewDiv.style.display = 'none';
                            uploadText.innerHTML = 'Klik atau taruh foto di sini (Max 2MB)';
                            return;
                        }

                        if (!file.type.startsWith('image/')) {
                            alert('Hanya file gambar yang diperbolehkan (JPG, PNG, GIF, dll).');
                            fileInput.value = '';
                            fileNameSpan.textContent = '';
                            previewDiv.style.display = 'none';
                            uploadText.innerHTML = 'Klik atau taruh foto di sini (Max 2MB)';
                            return;
                        }

                        fileNameSpan.textContent = file.name;
                        uploadText.innerHTML = '✓ Foto dipilih';

                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewImg.src = e.target.result;
                            previewDiv.style.display = 'block';
                        };
                        reader.readAsDataURL(file);
                    } else {
                        fileNameSpan.textContent = '';
                        previewDiv.style.display = 'none';
                        uploadText.innerHTML = 'Klik atau taruh foto di sini (Max 2MB)';
                    }
                });
            }

            // Drag and drop
            if (uploadZone) {
                uploadZone.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    uploadZone.style.borderColor = '#22c55e';
                    uploadZone.style.background = '#f0fdf4';
                });

                uploadZone.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    uploadZone.style.borderColor = '#cbd5e1';
                    uploadZone.style.background = '#fafafa';
                });

                uploadZone.addEventListener('drop', function(e) {
                    e.preventDefault();
                    uploadZone.style.borderColor = '#cbd5e1';
                    uploadZone.style.background = '#fafafa';

                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        fileInput.files = files;
                        const changeEvent = new Event('change', { bubbles: true });
                        fileInput.dispatchEvent(changeEvent);
                    }
                });
            }

            // ========== FORM VALIDASI DAN LOADING ANIMATION ==========
            const form = document.getElementById('registerForm');
            const submitBtn = document.getElementById('submitBtn');
            const loadingOverlay = document.getElementById('loadingOverlay');
            const loadingMessage = document.getElementById('loadingMessage');

            // Pesan loading berganti-ganti untuk animasi teks
            const loadingMessages = [
                'Mengamankan data Anda...',
                'Memverifikasi informasi...',
                'Menyiapkan akun Anda...',
                'Hampir selesai...'
            ];
            let messageIndex = 0;

            if (form) {
                form.addEventListener('submit', function(e) {
                    const password = document.getElementById('password');
                    const passwordConfirm = document.getElementById('password_confirmation');
                    const terms = document.getElementById('terms');

                    // Validasi password match
                    if (password.value !== passwordConfirm.value) {
                        e.preventDefault();
                        showToast('Konfirmasi kata sandi tidak cocok!');
                        passwordConfirm.focus();
                        return;
                    }

                    // Validasi panjang password minimal 6 karakter
                    if (password.value.length < 6) {
                        e.preventDefault();
                        showToast('Kata sandi minimal 6 karakter!');
                        password.focus();
                        return;
                    }

                    // Validasi terms & conditions
                    if (!terms.checked) {
                        e.preventDefault();
                        showToast('Anda harus menyetujui Syarat & Ketentuan dan Kebijakan Privasi.');
                        terms.focus();
                        return;
                    }

                    // TAMPILKAN LOADING OVERLAY DENGAN ANIMASI
                    loadingOverlay.classList.add('active');
                    submitBtn.classList.add('loading');

                    // Animasi teks loading berganti setiap 1.5 detik
                    const messageInterval = setInterval(() => {
                        messageIndex = (messageIndex + 1) % loadingMessages.length;
                        if (loadingMessage) {
                            loadingMessage.style.opacity = '0';
                            setTimeout(() => {
                                loadingMessage.textContent = loadingMessages[messageIndex];
                                loadingMessage.style.opacity = '1';
                            }, 150);
                        }
                    }, 1500);

                    // Simpan interval untuk dibersihkan jika diperlukan
                    window.loadingMessageInterval = messageInterval;

                    // Submit tetap berjalan (tidak preventDefault lagi)
                    return true;
                });
            }

            // Fungsi toast notifikasi
            function showToast(message) {
                const existingToast = document.querySelector('.toast-error');
                if (existingToast) existingToast.remove();

                const toast = document.createElement('div');
                toast.className = 'toast-error';
                toast.textContent = message;
                document.body.appendChild(toast);

                setTimeout(() => {
                    toast.style.opacity = '0';
                    setTimeout(() => toast.remove(), 300);
                }, 3000);
            }

            // Hapus loading overlay jika ada error dari server (setelah halaman selesai load)
            // Ini akan tetap aktif sampai halaman redirect, tidak masalah.
        })();
    </script>
</x-guest-layout>
