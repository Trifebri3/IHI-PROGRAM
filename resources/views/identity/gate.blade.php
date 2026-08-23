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
                <div class="border-2 border-dashed border-slate-200 rounded-2xl p-6 text-center hover:border-emerald-500 transition cursor-pointer flex flex-col items-center justify-center">
                    <input type="file" name="photo" id="photo" class="hidden" accept="image/png, image/jpeg, image/jpg" onchange="previewPhoto(this)" required>
                    <label for="photo" class="cursor-pointer flex flex-col items-center space-y-3">
                        <div id="photo-preview-container" class="hidden w-24 h-24 rounded-full border border-slate-200 overflow-hidden shadow-sm flex items-center justify-center bg-slate-50">
                            <img id="photo-preview-img" src="" class="w-full h-full object-cover">
                        </div>
                        <span id="file-label" class="text-xs font-bold text-slate-600 block">Klik untuk unggah Foto Profil (.jpg/.png)</span>
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

                    <!-- Kabupaten (Select/Dropdown) -->
                    <select name="kabupaten" id="kabupaten" class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-emerald-500" required>
                        <option value="">Pilih Kabupaten/Kota</option>
                    </select>

                    <!-- Kecamatan (Select/Dropdown) -->
                    <select name="kecamatan" id="kecamatan" class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-emerald-500" required>
                        <option value="">Pilih Kecamatan</option>
                    </select>

                    <!-- Desa (Select/Dropdown) -->
                    <select name="desa" id="desa" class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-emerald-500" required>
                        <option value="">Pilih Desa/Kelurahan</option>
                    </select>

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
                <input type="text" name="detail_alamat" placeholder="Detail Alamat / Patokan Jalan (RT/RW, No Rumah, dll) (Opsional)" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-emerald-500">

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
                provinsi: [] // Akan dimuat dinamis dari API wilayah
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
        
        function toTitleCase(str) {
            return str.toLowerCase().replace(/(^|\s|-)\S/g, function(L) {
                return L.toUpperCase();
            });
        }

        // Preview Foto Profil sebelum diunggah
        function previewPhoto(input) {
            const file = input.files[0];
            const previewContainer = document.getElementById('photo-preview-container');
            const previewImg = document.getElementById('photo-preview-img');
            const fileLabel = document.getElementById('file-label');

            if (file) {
                if (!file.type.startsWith('image/')) {
                    alert('Berkas harus berupa gambar (JPG/PNG)!');
                    input.value = '';
                    previewContainer.classList.add('hidden');
                    fileLabel.innerText = 'Klik untuk unggah Foto Profil (.jpg/.png)';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewContainer.classList.remove('hidden');
                    fileLabel.innerText = file.name;
                };
                reader.readAsDataURL(file);
            } else {
                previewContainer.classList.add('hidden');
                fileLabel.innerText = 'Klik untuk unggah Foto Profil (.jpg/.png)';
            }
        }

        function switchToSelect(id, placeholder, name) {
            const el = document.getElementById(id);
            if (el && el.tagName === 'SELECT') return el;
            
            const select = document.createElement('select');
            select.id = id;
            select.name = name;
            select.required = true;
            select.className = "p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-emerald-500";
            select.innerHTML = `<option value="">Pilih ${placeholder}</option>`;
            
            if (el) el.parentNode.replaceChild(select, el);
            return select;
        }

        function switchToInput(id, placeholder, name, isRequired = true) {
            const el = document.getElementById(id);
            if (el && el.tagName === 'INPUT') return el;
            
            const input = document.createElement('input');
            input.type = 'text';
            input.id = id;
            input.name = name;
            input.placeholder = placeholder;
            if (isRequired) {
                input.required = true;
            }
            input.className = "p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-emerald-500";
            
            if (el) el.parentNode.replaceChild(input, el);
            return input;
        }

        function updateRegions() {
            const selectedNegara = negaraSelect.value;
            const provinsiSelect = switchToSelect('provinsi', 'Provinsi', 'provinsi');
            
            if (selectedNegara === 'Indonesia') {
                // Switch Kabupaten, Kecamatan, Desa ke Select Dropdown untuk Indonesia
                const kabupatenSelect = switchToSelect('kabupaten', 'Kabupaten/Kota', 'kabupaten');
                const kecamatanSelect = switchToSelect('kecamatan', 'Kecamatan', 'kecamatan');
                const desaSelect = switchToSelect('desa', 'Desa/Kelurahan', 'desa');
                
                // Matikan dropdown dinamis sebelum data diisi
                kabupatenSelect.disabled = true;
                kecamatanSelect.disabled = true;
                desaSelect.disabled = true;

                // Ambil data Provinsi Indonesia dari API Wilayah via Local Proxy
                provinsiSelect.innerHTML = '<option value="">Memuat Provinsi...</option>';
                provinsiSelect.disabled = true;
                
                fetch('/api-wilayah/provinces')
                    .then(res => {
                        if (!res.ok) throw new Error('API Respon tidak OK');
                        return res.json();
                    })
                    .then(data => {
                        provinsiSelect.innerHTML = '<option value="">Pilih Provinsi</option>';
                        provinsiSelect.disabled = false;
                        data.forEach(prov => {
                            const opt = document.createElement('option');
                            const rawName = prov.nama || prov.name || '';
                            const formattedName = toTitleCase(rawName);
                            opt.value = formattedName;
                            opt.dataset.id = prov.id;
                            opt.textContent = formattedName;
                            provinsiSelect.appendChild(opt);
                        });
                    })
                    .catch(err => {
                        console.error('Gagal memuat provinsi:', err);
                        provinsiSelect.innerHTML = '<option value="">Gagal memuat data (silakan muat ulang halaman)</option>';
                    });

                // Handler ketika Provinsi terpilih berubah
                provinsiSelect.onchange = () => {
                    const selectedOpt = provinsiSelect.options[provinsiSelect.selectedIndex];
                    const provinceId = selectedOpt ? selectedOpt.dataset.id : null;
                    
                    kabupatenSelect.innerHTML = '<option value="">Pilih Kabupaten/Kota</option>';
                    kabupatenSelect.disabled = true;
                    kecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
                    kecamatanSelect.disabled = true;
                    desaSelect.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
                    desaSelect.disabled = true;

                    if (!provinceId) return;

                    kabupatenSelect.innerHTML = '<option value="">Memuat Kabupaten/Kota...</option>';
                    fetch(`/api-wilayah/regencies/${provinceId}`)
                        .then(res => {
                            if (!res.ok) throw new Error('API Respon tidak OK');
                            return res.json();
                        })
                        .then(data => {
                            kabupatenSelect.innerHTML = '<option value="">Pilih Kabupaten/Kota</option>';
                            kabupatenSelect.disabled = false;
                            data.forEach(reg => {
                                const opt = document.createElement('option');
                                const rawName = reg.nama || reg.name || '';
                                const formattedName = toTitleCase(rawName);
                                opt.value = formattedName;
                                opt.dataset.id = reg.id;
                                opt.textContent = formattedName;
                                kabupatenSelect.appendChild(opt);
                            });
                        })
                        .catch(err => {
                            console.error('Gagal memuat kabupaten:', err);
                            kabupatenSelect.innerHTML = '<option value="">Gagal memuat data (silakan ganti provinsi untuk mengulang)</option>';
                        });
                };

                // Handler ketika Kabupaten terpilih berubah
                kabupatenSelect.onchange = () => {
                    const selectedOpt = kabupatenSelect.options[kabupatenSelect.selectedIndex];
                    const regencyId = selectedOpt ? selectedOpt.dataset.id : null;
                    
                    kecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
                    kecamatanSelect.disabled = true;
                    desaSelect.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
                    desaSelect.disabled = true;

                    if (!regencyId) return;

                    kecamatanSelect.innerHTML = '<option value="">Memuat Kecamatan...</option>';
                    fetch(`/api-wilayah/districts/${regencyId}`)
                        .then(res => {
                            if (!res.ok) throw new Error('API Respon tidak OK');
                            return res.json();
                        })
                        .then(data => {
                            kecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
                            kecamatanSelect.disabled = false;
                            data.forEach(dist => {
                                const opt = document.createElement('option');
                                const rawName = dist.nama || dist.name || '';
                                const formattedName = toTitleCase(rawName);
                                opt.value = formattedName;
                                opt.dataset.id = dist.id;
                                opt.textContent = formattedName;
                                kecamatanSelect.appendChild(opt);
                            });
                        })
                        .catch(err => {
                            console.error('Gagal memuat kecamatan:', err);
                            kecamatanSelect.innerHTML = '<option value="">Gagal memuat data (silakan ganti kabupaten untuk mengulang)</option>';
                        });
                };

                // Handler ketika Kecamatan terpilih berubah
                kecamatanSelect.onchange = () => {
                    const selectedOpt = kecamatanSelect.options[kecamatanSelect.selectedIndex];
                    const districtId = selectedOpt ? selectedOpt.dataset.id : null;
                    
                    desaSelect.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
                    desaSelect.disabled = true;

                    if (!districtId) return;

                    desaSelect.innerHTML = '<option value="">Memuat Desa/Kelurahan...</option>';
                    fetch(`/api-wilayah/villages/${districtId}`)
                        .then(res => {
                            if (!res.ok) throw new Error('API Respon tidak OK');
                            return res.json();
                        })
                        .then(data => {
                            desaSelect.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
                            desaSelect.disabled = false;
                            data.forEach(vil => {
                                const opt = document.createElement('option');
                                const rawName = vil.nama || vil.name || '';
                                const formattedName = toTitleCase(rawName);
                                opt.value = formattedName;
                                opt.dataset.id = vil.id;
                                opt.textContent = formattedName;
                                desaSelect.appendChild(opt);
                            });
                        })
                        .catch(err => {
                            console.error('Gagal memuat desa:', err);
                            desaSelect.innerHTML = '<option value="">Gagal memuat data (silakan ganti kecamatan untuk mengulang)</option>';
                        });
                };

            } else {
                // Negara Selain Indonesia: Kembalikan Kabupaten, Kecamatan, Desa ke Input Teks Biasa
                switchToInput('kabupaten', 'Kabupaten/Kota', 'kabupaten');
                switchToInput('kecamatan', 'Kecamatan', 'kecamatan');
                switchToInput('desa', 'Desa/Kelurahan', 'desa');

                // Muat provinsi statis untuk negara tersebut
                provinsiSelect.innerHTML = '<option value="">Pilih Provinsi</option>';
                provinsiSelect.disabled = !selectedNegara;
                
                if (selectedNegara && dataWilayah[selectedNegara]) {
                    dataWilayah[selectedNegara].provinsi.forEach(prov => {
                        const opt = document.createElement('option');
                        opt.value = prov;
                        opt.textContent = prov;
                        provinsiSelect.appendChild(opt);
                    });
                }
            }
        }

        negaraSelect.addEventListener('change', updateRegions);
        document.addEventListener('DOMContentLoaded', updateRegions);

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