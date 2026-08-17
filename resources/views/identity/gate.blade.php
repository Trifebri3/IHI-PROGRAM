<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Identitas | Gatekeeper</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-4xl w-full bg-white rounded-3xl shadow-2xl shadow-slate-200 overflow-hidden flex flex-col md:flex-row border border-slate-100">

        <!-- SIDEBAR DEKORATIF -->
        <div class="bg-slate-900 p-8 text-white w-full md:w-1/3 flex flex-col justify-between">
            <div>
                <h1 class="text-2xl font-black tracking-tighter">GATEKEEPER</h1>
                <p class="text-[10px] text-slate-400 mt-2 uppercase tracking-widest font-bold">Verifikasi Identitas Resmi</p>
            </div>
            <div class="space-y-4 mt-8">
                <div class="text-[10px] text-slate-500 font-bold uppercase">Status Keamanan</div>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                    <span class="text-xs font-bold text-emerald-400">DATA TERENKRIPSI</span>
                </div>
            </div>
        </div>

        <!-- FORM INPUT -->
        <div class="p-8 md:p-12 w-full md:w-2/3">
            <h2 class="text-xl font-black text-slate-900">Lengkapi Data Identitas</h2>
            <p class="text-xs text-slate-400 mt-1 mb-8">Data ini wajib diisi untuk membuka akses penuh ke dalam ekosistem program kerja.</p>

            @if ($errors->any())
                <div class="bg-rose-50 text-rose-600 p-4 rounded-xl text-[11px] font-bold mb-6 border border-rose-100">
                    <ul class="list-disc pl-4">
                        @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('identity.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <!-- UPLOAD FOTO -->
                <div class="border-2 border-dashed border-slate-200 rounded-2xl p-6 text-center hover:border-emerald-500 transition cursor-pointer">
                    <input type="file" name="photo" id="photo" class="hidden" onchange="document.getElementById('file-label').innerText = this.files[0].name" required>
                    <label for="photo" class="cursor-pointer">
                        <span id="file-label" class="text-xs font-bold text-slate-600">Klik untuk unggah Foto Profil (.jpg/.png)</span>
                    </label>
                </div>

                <!-- GRID ALAMAT DENGAN DROPDOWN PROVINSI + INPUT BIASA UNTUK KABUPATEN, KECAMATAN, DESA, KAMPUNG -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Negara (Select/Dropdown) -->
                    <select name="negara" id="negara" class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-emerald-500" required>
                        <option value="">Pilih Negara</option>
                        <option value="Indonesia" selected>Indonesia</option>
                        <option value="Malaysia">Malaysia</option>
                        <option value="Singapura">Singapura</option>
                        <option value="Thailand">Thailand</option>
                    </select>
                    <!-- Provinsi (Select/Dropdown) -->
                    <select name="provinsi" id="provinsi" class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-emerald-500" required>
                        <option value="">Pilih Provinsi</option>
                    </select>

                    <!-- Kabupaten (Input Biasa) -->
                    <input 
                        type="text" 
                        name="kabupaten" 
                        id="kabupaten" 
                        placeholder="Kabupaten/Kota"
                        class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-emerald-500"
                        required
                    >

                    <!-- Kecamatan (Input Biasa) -->
                    <input 
                        type="text" 
                        name="kecamatan" 
                        id="kecamatan" 
                        placeholder="Kecamatan"
                        class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-emerald-500"
                        required
                    >

                    <!-- Desa (Input Biasa) -->
                    <input 
                        type="text" 
                        name="desa" 
                        id="desa" 
                        placeholder="Desa/Kelurahan"
                        class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-emerald-500"
                        required
                    >

                    <!-- Kampung (Input Biasa) -->
                    <input 
                        type="text" 
                        name="kampung" 
                        id="kampung" 
                        placeholder="Kampung/Dusun (Opsional)"
                        class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-emerald-500"
                    >
                </div>

                <!-- Detail Alamat -->
                <input type="text" name="detail_alamat" placeholder="Detail Alamat / Patokan Jalan (RT/RW, No Rumah, dll)" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-emerald-500">

                <button type="submit" class="w-full py-4 bg-emerald-600 text-white font-black text-xs uppercase tracking-widest rounded-xl hover:bg-emerald-700 transition shadow-lg">
                    Verifikasi Data & Buka Akses
                </button>
            </form>
        </div>
    </div>

    <script>
        // DATA WILAYAH (Perbaiki struktur: setiap negara memiliki array provinsi)
        const dataWilayah = {
            Indonesia: {
                provinsi: [
                    "Nanggroe Aceh Darussalam",
                    "Sumatera Utara",
                    "Sumatera Barat",
                    "Riau",
                    "Kepulauan Riau",
                    "Jambi",
                    "Bengkulu",
                    "Sumatera Selatan",
                    "Kepulauan Bangka Belitung",
                    "Lampung",
                    "Banten",
                    "DKI Jakarta",
                    "Jawa Barat",
                    "Jawa Tengah",
                    "Daerah Istimewa Yogyakarta",
                    "Jawa Timur",
                    "Kalimantan Barat",
                    "Kalimantan Tengah",
                    "Kalimantan Selatan",
                    "Kalimantan Timur",
                    "Kalimantan Utara",
                    "Bali",
                    "Nusa Tenggara Barat",
                    "Nusa Tenggara Timur",
                    "Sulawesi Utara",
                    "Sulawesi Tengah",
                    "Sulawesi Selatan",
                    "Sulawesi Tenggara",
                    "Gorontalo",
                    "Sulawesi Barat",
                    "Maluku",
                    "Maluku Utara",
                    "Papua",
                    "Papua Barat",
                    "Papua Selatan",
                    "Papua Tengah",
                    "Papua Pegunungan",
                    "Papua Barat Daya"
                ]
            },
            Malaysia: {
                provinsi: ["Selangor", "Kuala Lumpur", "Johor", "Penang", "Sabah", "Sarawak"]
            },
            Singapura: {
                provinsi: ["Central Region", "East Region", "North Region", "North-East Region", "West Region"]
            },
            Thailand: {
                provinsi: ["Bangkok", "Chiang Mai", "Phuket", "Pattaya", "Ayutthaya"]
            }
        };

        // DOM Elements
        const negaraSelect = document.getElementById('negara');
        const provinsiSelect = document.getElementById('provinsi');
        
        // Fungsi untuk mengisi dropdown provinsi berdasarkan negara yang dipilih
        function updateProvinsi() {
            const selectedNegara = negaraSelect.value;
            
            // Reset dropdown provinsi ke opsi default
            provinsiSelect.innerHTML = '<option value="">Pilih Provinsi</option>';
            
            // Jika tidak ada negara yang dipilih atau negara tidak ada dalam dataWilayah
            if (!selectedNegara || !dataWilayah[selectedNegara]) {
                provinsiSelect.disabled = true;
                return;
            }
            
            // Ambil daftar provinsi dari dataWilayah
            const provinsiList = dataWilayah[selectedNegara].provinsi;
            
            // Jika provinsiList ada dan merupakan array
            if (provinsiList && Array.isArray(provinsiList) && provinsiList.length > 0) {
                provinsiSelect.disabled = false;
                
                // Tambahkan option untuk setiap provinsi
                provinsiList.forEach(prov => {
                    const option = document.createElement('option');
                    option.value = prov;
                    option.textContent = prov;
                    provinsiSelect.appendChild(option);
                });
            } else {
                // Jika tidak ada data provinsi, disable dropdown
                provinsiSelect.disabled = true;
            }
        }
        
        // Event listener untuk perubahan pada dropdown negara
        negaraSelect.addEventListener('change', updateProvinsi);
        
        // Inisialisasi pertama kali (karena default Indonesia sudah terpilih)
        // Panggil updateProvinsi() setelah halaman siap untuk mengisi provinsi Indonesia
        // Gunakan setTimeout atau DOMContentLoaded untuk memastikan semua elemen sudah siap
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                updateProvinsi();
            });
        } else {
            updateProvinsi();
        }
        
        // Catatan: Kabupaten, Kecamatan, Desa, Kampung bersifat input teks biasa.
        // Pengguna bebas mengetik manual, tidak terpengaruh pilihan provinsi/negara.
    </script>

    <style>
        /* Tambahan styling agar placeholder lebih terbaca */
        input::placeholder {
            font-size: 11px;
            color: #94a3b8;
            font-weight: 500;
        }
        select option {
            font-size: 12px;
        }
        /* Styling untuk dropdown yang disabled */
        select:disabled {
            background-color: #f1f5f9;
            color: #94a3b8;
            cursor: not-allowed;
        }
    </style>

</body>
</html>