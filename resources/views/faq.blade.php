{{-- resources/views/faq.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pertanyaan Umum (FAQ) - Institut Hijau Indonesia</title>

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
            font-family: 'Figtree', 'Inter', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #f7faf5 0%, #eef3ea 100%);
            min-height: 100vh;
        }

        /* Layout Responsif */
        .faq-wrapper {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        /* Header */
        .faq-header {
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

        .faq-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1e4a2f;
            margin-bottom: 0.5rem;
            letter-spacing: -0.3px;
        }

        .faq-subtitle {
            color: #5b7c56;
            font-size: 0.9rem;
        }

        /* Search Bar */
        .search-container {
            max-width: 550px;
            margin: 1.5rem auto 2rem;
        }

        .search-box {
            display: flex;
            background: white;
            border-radius: 3rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8dc;
            transition: all 0.2s;
        }

        .search-box:focus-within {
            border-color: #2d6a4f;
            box-shadow: 0 0 0 3px rgba(45, 106, 79, 0.1);
        }

        .search-icon {
            padding: 0.9rem 0 0.9rem 1.2rem;
            color: #8ba382;
        }

        .search-input {
            flex: 1;
            padding: 0.9rem 1rem;
            border: none;
            background: transparent;
            font-size: 0.9rem;
            outline: none;
            font-family: inherit;
        }

        .search-clear {
            background: none;
            border: none;
            padding: 0 1.2rem;
            color: #b8cfb0;
            cursor: pointer;
            display: none;
            font-size: 1.1rem;
        }

        /* Kategori Tabs */
        .category-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            justify-content: center;
            margin-bottom: 2.5rem;
        }

        .category-btn {
            background: white;
            border: 1px solid #e2e8dc;
            padding: 0.6rem 1.3rem;
            border-radius: 2rem;
            font-size: 0.85rem;
            font-weight: 500;
            color: #5b7c56;
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
        }

        .category-btn:hover {
            border-color: #2d6a4f;
            color: #2d6a4f;
        }

        .category-btn.active {
            background: #1e4a2f;
            border-color: #1e4a2f;
            color: white;
        }

        /* FAQ List */
        .faq-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .faq-item {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            transition: all 0.2s;
            border: 1px solid #eef3ea;
        }

        .faq-item:hover {
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        }

        .faq-question {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.2rem 1.5rem;
            cursor: pointer;
            font-weight: 600;
            color: #1e4a2f;
            font-size: 1rem;
            transition: all 0.2s;
        }

        .faq-question:hover {
            color: #2d6a4f;
        }

        .faq-question .icon {
            font-size: 1.3rem;
            color: #8ba382;
            transition: transform 0.2s;
        }

        .faq-item.active .faq-question .icon {
            transform: rotate(180deg);
            color: #2d6a4f;
        }

        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
            padding: 0 1.5rem;
            border-top: none;
        }

        .faq-item.active .faq-answer {
            max-height: 500px;
            padding: 0 1.5rem 1.2rem 1.5rem;
            border-top: 1px solid #eef3ea;
        }

        .faq-answer p, .faq-answer ul {
            font-size: 0.9rem;
            line-height: 1.6;
            color: #4a5e44;
            margin-top: 1rem;
        }

        .faq-answer ul {
            margin-left: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .faq-answer li {
            margin-bottom: 0.3rem;
        }

        /* No Results */
        .no-results {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 1.5rem;
            color: #8ba382;
        }

        /* Still Need Help */
        .help-section {
            background: #f1f8ef;
            border-radius: 1.5rem;
            padding: 2rem;
            text-align: center;
            margin-top: 2.5rem;
        }

        .help-section h3 {
            color: #1e4a2f;
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
        }

        .help-section p {
            color: #5b7c56;
            margin-bottom: 1.2rem;
            font-size: 0.85rem;
        }

        .help-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-primary, .btn-outline {
            padding: 0.7rem 1.5rem;
            border-radius: 2rem;
            font-weight: 600;
            font-size: 0.85rem;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #1e4a2f;
            color: white;
        }

        .btn-primary:hover {
            background: #14381f;
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid #1e4a2f;
            color: #1e4a2f;
        }

        .btn-outline:hover {
            background: #1e4a2f;
            color: white;
        }

        /* Tombol Kembali */
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: transparent;
            color: #5b7c56;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-weight: 500;
            font-size: 0.85rem;
            transition: all 0.2s;
            margin-bottom: 1rem;
        }

        .back-button:hover {
            background: #e2e8dc;
            color: #1e4a2f;
        }

        /* Footer */
        .faq-footer {
            text-align: center;
            padding: 2rem 1rem 1rem;
            margin-top: 2rem;
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
            transition: color 0.2s;
        }

        .footer-links a:hover {
            color: #1e4a2f;
            text-decoration: underline;
        }

        .copyright {
            font-size: 0.7rem;
            color: #8ba382;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .faq-wrapper {
                padding: 1.25rem;
            }
            .faq-title {
                font-size: 1.5rem;
            }
            .faq-question {
                padding: 1rem 1.2rem;
                font-size: 0.9rem;
            }
            .faq-item.active .faq-answer {
                padding: 0 1.2rem 1rem 1.2rem;
            }
        }

        @media (max-width: 480px) {
            .faq-wrapper {
                padding: 1rem;
            }
            .category-btn {
                padding: 0.45rem 1rem;
                font-size: 0.75rem;
            }
            .help-buttons {
                flex-direction: column;
                align-items: center;
            }
            .btn-primary, .btn-outline {
                width: 80%;
                text-align: center;
            }
        }

        .link-green {
            color: #2d6a4f;
            text-decoration: none;
            font-weight: 500;
        }

        .link-green:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="faq-wrapper">
        <!-- Tombol Kembali -->
        <div>
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('login') }}" class="back-button">
                ← Kembali
            </a>
        </div>

        <!-- Header -->
        <div class="faq-header">
            <a href="/" class="logo-link">
                <img src="https://lms.instituthijauindonesia.or.id/images/logo-light.png"
                     alt="Institut Hijau Indonesia"
                     class="logo-img">
            </a>
            <h1 class="faq-title">Pertanyaan Umum (FAQ)</h1>
            <p class="faq-subtitle">Temukan jawaban atas pertanyaan Anda seputar Institut Hijau Indonesia</p>
        </div>

        <!-- Search Bar -->
        <div class="search-container">
            <div class="search-box">
                <span class="search-icon">🔍</span>
                <input type="text" id="searchInput" class="search-input" placeholder="Cari pertanyaan...">
                <button id="clearSearch" class="search-clear">✕</button>
            </div>
        </div>

        <!-- Kategori Tabs -->
        <div class="category-tabs">
            <button class="category-btn active" data-category="all">Semua</button>
            <button class="category-btn" data-category="akun">Akun & Pendaftaran</button>
            <button class="category-btn" data-category="pembelajaran">Pembelajaran & Sertifikasi</button>
            <button class="category-btn" data-category="teknisi">Teknis & Platform</button>
            <button class="category-btn" data-category="komunitas">Komunitas & Kontribusi</button>
            <button class="category-btn" data-category="lainnya">Lainnya</button>
        </div>

        <!-- FAQ List -->
        <div id="faqList" class="faq-list">
            <!-- FAQ items akan diisi via JavaScript -->
        </div>

        <!-- Still Need Help -->
        <div class="help-section">
            <h3>Masih butuh bantuan?</h3>
            <p>Tim support kami siap membantu Anda melalui berbagai saluran komunikasi</p>
            <div class="help-buttons">
                <a href="https://wa.me/6281234567890" class="btn-primary" target="_blank">Hubungi WhatsApp</a>
                <a href="mailto:support@instituthijau.id" class="btn-outline">Email Support</a>
                <a href="{{ route('kontak') }}" class="btn-outline">Kunjungi Kantor Kami</a>
            </div>
        </div>

        <!-- Footer -->
        <div class="faq-footer">
            <div class="footer-links">
                <a href="{{ route('syarat-ketentuan') }}">Syarat & Ketentuan</a>
                <a href="{{ route('kebijakan-privasi') }}">Kebijakan Privasi</a>
                <a href="/tentang-kami">Tentang Kami</a>
                <a href="{{ route('kontak') }}">Kontak</a>
            </div>
            <div class="copyright">
                &copy; {{ date('Y') }} Institut Hijau Indonesia.<br>
                Membangun peradaban hijau yang adil dan lestari
            </div>
        </div>
    </div>

    <script>
        // Data FAQ
        const faqData = [
            // Akun & Pendaftaran
            {
                category: "akun",
                question: "Bagaimana cara mendaftar akun di Institut Hijau Indonesia?",
                answer: "Anda dapat mendaftar dengan mengklik tombol \"Daftar\" di halaman utama. Isi formulir pendaftaran dengan nama lengkap, alamat email, nomor telepon, dan kata sandi. Setelah mendaftar, kami akan mengirimkan tautan verifikasi ke email Anda untuk mengaktifkan akun."
            },
            {
                category: "akun",
                question: "Apakah pendaftaran di IHI gratis?",
                answer: "Ya, pendaftaran akun dasar di Institut Hijau Indonesia sepenuhnya GRATIS. Anda dapat mengakses materi pembelajaran dasar, bergabung dengan komunitas, dan mengikuti event-event tertentu secara gratis. Untuk sertifikasi lanjutan dan kursus premium, mungkin dikenakan biaya yang terjangkau."
            },
            {
                category: "akun",
                question: "Saya lupa kata sandi, bagaimana cara meresetnya?",
                answer: "Klik \"Lupa kata sandi\" di halaman login. Masukkan alamat email Anda, dan kami akan mengirimkan tautan reset kata sandi. Pastikan untuk memeriksa folder spam jika tidak menemukan email dalam beberapa menit."
            },
            {
                category: "akun",
                question: "Bisakah saya mengubah alamat email yang terdaftar?",
                answer: "Ya, Anda dapat mengubah alamat email melalui pengaturan akun setelah login. Namun, perubahan email akan memerlukan verifikasi ulang melalui tautan yang dikirim ke email baru Anda."
            },
            // Pembelajaran & Sertifikasi
            {
                category: "pembelajaran",
                question: "Apa saja jenis kursus yang tersedia di IHI?",
                answer: "IHI menyediakan berbagai kursus tentang: keadilan sosial dan ekologi, energi terbarukan, kepemimpinan hijau, ekonomi berkelanjutan, pengelolaan sampah, pertanian organik, dan advokasi lingkungan. Semua materi disusun oleh para ahli di bidangnya."
            },
            {
                category: "pembelajaran",
                question: "Apakah sertifikasi IHI diakui secara nasional?",
                answer: "Sertifikasi dari Institut Hijau Indonesia diakui oleh berbagai mitra kami di sektor pemerintahan, swasta, dan organisasi lingkungan. Program sertifikasi kami dirancang sesuai dengan standar kompetensi nasional dan internasional."
            },
            {
                category: "pembelajaran",
                question: "Berapa lama waktu yang dibutuhkan untuk menyelesaikan sertifikasi?",
                answer: "Durasi bervariasi tergantung program. Sertifikasi dasar biasanya memakan waktu 2-4 minggu, sementara sertifikasi lanjutan dapat memakan waktu 2-3 bulan. Semua kursus dapat diikuti secara mandiri (self-paced)."
            },
            {
                category: "pembelajaran",
                question: "Apakah ada ujian untuk mendapatkan sertifikat?",
                answer: "Ya, setiap program sertifikasi memiliki ujian akhir yang harus dilalui dengan nilai minimal kelulusan. Ujian dapat diambil secara online. Anda diberikan maksimal 3 kali kesempatan ujian."
            },
            // Teknis & Platform
            {
                category: "teknisi",
                question: "Platform IHI bisa diakses dari perangkat apa saja?",
                answer: "Platform IHI dapat diakses melalui desktop/laptop (Windows, Mac, Linux) dan perangkat mobile (iOS, Android) melalui browser. Kami juga sedang mengembangkan aplikasi mobile khusus."
            },
            {
                category: "teknisi",
                question: "Apakah materi pembelajaran bisa diunduh?",
                answer: "Beberapa materi dalam bentuk PDF dan modul dapat diunduh untuk dipelajari secara offline. Namun, video pembelajaran dan kuis interaktif hanya dapat diakses secara online."
            },
            {
                category: "teknisi",
                question: "Bagaimana jika mengalami kendala teknis saat mengakses platform?",
                answer: "Hubungi tim support kami melalui WhatsApp +62 812-3456-7890 atau email support@instituthijau.id. Sertakan screenshot kendala yang Anda alami untuk membantu tim kami menangani masalah lebih cepat."
            },
            // Komunitas & Kontribusi
            {
                category: "komunitas",
                question: "Bagaimana cara bergabung dengan komunitas IHI?",
                answer: "Setelah mendaftar dan login, Anda dapat mengakses forum diskusi, grup belajar, dan ruang kolaborasi. Kami juga memiliki grup WhatsApp dan Telegram untuk diskusi rutin yang dapat diikuti dengan menghubungi admin."
            },
            {
                category: "komunitas",
                question: "Apakah saya bisa berkontribusi sebagai relawan atau pengajar?",
                answer: "Tentu! IHI selalu membuka kesempatan bagi relawan, fasilitator, dan pengajar tamu. Kirimkan profil dan portofolio Anda ke volunteer@instituthijau.id untuk informasi lebih lanjut."
            },
            {
                category: "komunitas",
                question: "Apakah IHI mengadakan event offline?",
                answer: "Ya, secara rutin IHI mengadakan seminar, workshop, dan pelatihan offline di berbagai kota. Informasi event dapat diakses melalui halaman berita di website atau melalui newsletter kami."
            },
            // Lainnya
            {
                category: "lainnya",
                question: "Bagaimana cara menjadi mitra atau donatur IHI?",
                answer: "IHI terbuka untuk kerjasama dengan berbagai pihak. Untuk informasi kemitraan, silakan hubungi partnerships@instituthijau.id. Untuk donasi, Anda dapat mentransfer ke rekening resmi Yayasan Peradaban Hijau Indonesia yang informasinya tersedia di halaman kontak."
            },
            {
                category: "lainnya",
                question: "Apakah IHI memiliki program beasiswa?",
                answer: "Ya, IHI menyediakan program beasiswa bagi peserta yang membutuhkan dukungan finansial. Kriteria dan pendaftaran beasiswa diumumkan setiap awal semester melalui email newsletter dan website resmi."
            },
            {
                category: "lainnya",
                question: "Bagaimana cara mendapatkan informasi terbaru dari IHI?",
                answer: "Daftarkan email Anda untuk newsletter mingguan, ikuti media sosial IHI (Instagram, LinkedIn, Facebook), atau pantau secara rutin halaman berita di website instituthijauindonesia.or.id."
            }
        ];

        let currentCategory = "all";
        let currentSearch = "";

        // Render FAQ
        function renderFAQ() {
            const filtered = faqData.filter(item => {
                const matchCategory = currentCategory === "all" || item.category === currentCategory;
                const matchSearch = currentSearch === "" ||
                    item.question.toLowerCase().includes(currentSearch.toLowerCase()) ||
                    item.answer.toLowerCase().includes(currentSearch.toLowerCase());
                return matchCategory && matchSearch;
            });

            const container = document.getElementById("faqList");

            if (filtered.length === 0) {
                container.innerHTML = `
                    <div class="no-results">
                        <p>Tidak ditemukan pertanyaan yang sesuai dengan pencarian Anda.</p>
                        <p>Silakan coba kata kunci lain atau hubungi support kami.</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = filtered.map((item, idx) => `
                <div class="faq-item" data-category="${item.category}">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <span>${escapeHtml(item.question)}</span>
                        <span class="icon">▼</span>
                    </div>
                    <div class="faq-answer">
                        <p>${formatAnswer(item.answer)}</p>
                    </div>
                </div>
            `).join("");
        }

        // Toggle FAQ item
        function toggleFAQ(element) {
            const item = element.closest(".faq-item");
            const isActive = item.classList.contains("active");

            // Tutup semua yang terbuka (opsional, untuk UX lebih rapi)
            document.querySelectorAll(".faq-item.active").forEach(activeItem => {
                if (activeItem !== item) {
                    activeItem.classList.remove("active");
                }
            });

            item.classList.toggle("active");
        }

        // Format answer (handle bullet points)
        function formatAnswer(answer) {
            if (answer.includes("\n")) {
                return answer.replace(/\n/g, "<br>");
            }
            return answer;
        }

        // Escape HTML
        function escapeHtml(text) {
            const div = document.createElement("div");
            div.textContent = text;
            return div.innerHTML;
        }

        // Event Listeners
        document.querySelectorAll(".category-btn").forEach(btn => {
            btn.addEventListener("click", () => {
                document.querySelectorAll(".category-btn").forEach(b => b.classList.remove("active"));
                btn.classList.add("active");
                currentCategory = btn.dataset.category;
                renderFAQ();
            });
        });

        const searchInput = document.getElementById("searchInput");
        const clearBtn = document.getElementById("clearSearch");

        searchInput.addEventListener("input", (e) => {
            currentSearch = e.target.value;
            clearBtn.style.display = currentSearch ? "block" : "none";
            renderFAQ();
        });

        clearBtn.addEventListener("click", () => {
            searchInput.value = "";
            currentSearch = "";
            clearBtn.style.display = "none";
            renderFAQ();
        });

        // Inisialisasi
        renderFAQ();
    </script>
</body>
</html>
