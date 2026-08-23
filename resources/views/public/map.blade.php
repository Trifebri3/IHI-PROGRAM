@extends('layouts.public')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-6">
    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border-2 border-slate-100">
        <div>
            <div class="inline-block bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2.5 py-1 rounded-md uppercase tracking-widest mb-1.5">
                Visualisasi Densitas Geografis
            </div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Peta Persebaran Peserta Resmi</h2>
            <p class="text-xs text-slate-400 mt-0.5">Warna hijau pekat mengindikasikan konsentrasi jumlah peserta yang lebih tinggi di provinsi tersebut.</p>
        </div>
        
        <div class="flex items-center gap-1.5 bg-slate-50 p-2 rounded-xl text-[10px] font-mono text-slate-600 self-start sm:self-center border border-slate-100">
            <span class="font-sans font-bold text-slate-400">Sedikit</span>
            <span class="w-3 h-3 bg-[#a7f3d0] inline-block rounded-xs"></span>
            <span class="w-3 h-3 bg-[#34d399] inline-block rounded-xs"></span>
            <span class="w-3 h-3 bg-[#059669] inline-block rounded-xs"></span>
            <span class="w-3 h-3 bg-[#047857] inline-block rounded-xs"></span>
            <span class="font-sans font-bold text-slate-500">Banyak</span>
        </div>
    </div>

    <div class="bg-white p-3 rounded-3xl border-2 border-slate-100 shadow-xs relative">
        <div id="map" class="w-full h-[520px] rounded-2xl z-0 bg-slate-50"></div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // 1. Inisialisasi Peta (Titik Tengah Indonesia agar proposional)
    var map = L.map('map', { 
        scrollWheelZoom: false,
        minZoom: 4,
        maxZoom: 7
    }).setView([-2.5, 118.0], 5);

    // Menggunakan base tile map abu-abu terang minimalis agar warna hijau provinsi terlihat kontras
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '© OpenStreetMap © CARTO'
    }).addTo(map);

    // Object penampung hitungan jumlah peserta per provinsi
    const dataProvinsi = {};

    // Fungsi standarisasi penulisan teks nama provinsi agar sinkron dengan file GeoJSON
    function cleanProvinceName(name) {
        if (!name) return "";
        let cleaned = name.toUpperCase()
            .replace(/PROVINSI\s+/g, '')
            .replace(/PROV\.\s+/g, '')
            .replace(/KEP\./g, 'KEPULAUAN')
            .replace(/KEPULAUAN\s+/g, '')
            .replace(/DI\.\s+/g, '')
            .replace(/DAERAH\s+ISTIMEWA\s+/g, '')
            .replace(/PROBANTEN/g, 'BANTEN')
            .replace(/IRIAN\s+JAYA\s+BARAT/g, 'PAPUA BARAT')
            .replace(/IRIAN\s+JAYA\s+TIMUR/g, 'PAPUA')
            .replace(/IRIAN\s+JAYA\s+TENGAH/g, 'PAPUA')
            .replace(/IRIAN\s+JAYA/g, 'PAPUA')
            .trim();
            
        const mappings = {
            'BANGKA BELITUNG': 'BANGKA BELITUNG',
            'KEPULAUAN BANGKA BELITUNG': 'BANGKA BELITUNG',
            'YOGYAKARTA': 'YOGYAKARTA',
            'DI YOGYAKARTA': 'YOGYAKARTA',
            'DKI JAKARTA': 'JAKARTA',
            'JAKARTA': 'JAKARTA',
            'ACEH': 'ACEH',
            'DI ACEH': 'ACEH',
            'NANGGROE ACEH DARUSSALAM': 'ACEH',
        };
        
        return mappings[cleaned] || cleaned;
    }

    // Fungsi pemberi warna gradasi hijau berdasarkan volume jumlah
    function getColor(d) {
        return d > 100 ? '#047857' :
               d > 50  ? '#059669' :
               d > 20  ? '#10b981' :
               d > 5   ? '#34d399' :
               d > 0   ? '#a7f3d0' :
                         '#f8fafc'; // Abu-abu keputihan jika kosong (0 data)
    }

    // Mengatur style polygon batas wilayah provinsi
    function styleFeature(feature) {
        // Cek properti nama di file GeoJSON umum
        const rawName = feature.properties.PROVINSI || feature.properties.Provinsi || feature.properties.provinsi || feature.properties.Propinsi || feature.properties.propinsi || feature.properties.NAME_1 || feature.properties.PROP_NAME || "";
        const cleanedName = cleanProvinceName(rawName);
        const count = dataProvinsi[cleanedName] || 0;
        
        return {
            fillColor: getColor(count),
            weight: 1.5,
            opacity: 1,
            color: '#ffffff', // Garis pembatas putih bersih antar provinsi
            fillOpacity: 0.85
        };
    }

    let geojsonLayer;

    // Menambahkan fungsi pop-up interaktif & highlight hover
    function onEachFeature(feature, layer) {
        const rawName = feature.properties.PROVINSI || feature.properties.Provinsi || feature.properties.provinsi || feature.properties.Propinsi || feature.properties.propinsi || feature.properties.NAME_1 || "Tidak Diketahui";
        const cleanedName = cleanProvinceName(rawName);
        const count = dataProvinsi[cleanedName] || 0;
        
        layer.bindPopup(`
            <div class="font-sans p-1 text-center">
                <b class="text-xs text-slate-900 block border-b border-slate-100 pb-1 mb-1 uppercase tracking-wide">${rawName}</b>
                <span class="text-[11px] text-slate-500">Total: <strong class="text-emerald-600">${count} Peserta Resmi</strong></span>
            </div>
        `, { closeButton: false });

        layer.on({
            mouseover: function (e) {
                var l = e.target;
                l.setStyle({ weight: 2, color: '#047857', fillOpacity: 0.95 });
                l.openPopup();
            },
            mouseout: function (e) {
                geojsonLayer.resetStyle(e.target);
                e.target.closePopup();
            },
            click: function (e) {
                map.fitBounds(e.target.getBounds());
            }
        });
    }

    // 2. LANGKAH FETCHING DATA & MERENDER PETA
    // Ambil data asli dari controller
    fetch("{{ route('public.program.map.data', $programId) }}")
        .then(response => response.json())
        .then(data => {
            
            // Loop data dari DB untuk dihitung (agregasi) jumlah per provinsinya
            data.forEach(p => {
                if (p.provinsi) {
                    const cleanedName = cleanProvinceName(p.provinsi);
                    dataProvinsi[cleanedName] = (dataProvinsi[cleanedName] || 0) + 1;
                }
            });

            // Setelah data terhitung, baru load file GeoJSON Peta Indonesia Resmi (CDN Publik Stabil)
            return fetch('https://raw.githubusercontent.com/denyherianto/indonesia-geojson-topojson-maps-with-38-provinces/main/GeoJSON/indonesia-38-provinces.geojson');
        })
        .then(response => response.json())
        .then(geojsonData => {
            // Render layer warna-warni provinsi di atas peta dasar
            geojsonLayer = L.geoJson(geojsonData, {
                style: styleFeature,
                onEachFeature: onEachFeature
            }).addTo(map);
        })
        .catch(err => {
            console.error('Gagal memproses visualisasi peta choropleth:', err);
            document.getElementById('map').innerHTML = `
                <div class="flex items-center justify-center h-full text-xs text-slate-400 font-semibold">
                    Gagal memuat visualisasi warna wilayah peta.
                </div>`;
        });
</script>

<style>
    /* Custom style popup balon informasi Leaflet agar modern */
    .leaflet-popup-content-wrapper {
        border-radius: 10px !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.06) !important;
        border: 1px solid #f1f5f9;
    }
    .leaflet-popup-tip {
        background: white;
        box-shadow: none !important;
    }
</style>
@endsection