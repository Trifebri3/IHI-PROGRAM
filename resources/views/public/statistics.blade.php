@extends('layouts.public')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-8">
    
    <div class="max-w-3xl space-y-2">
        <div class="inline-block bg-emerald-50 text-emerald-700 text-xs font-bold px-3 py-1 rounded-md uppercase tracking-wider">
            Monitoring Geografis & Statistik Total
        </div>
        <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">{{ $program->name }}</h1>
        <p class="text-sm sm:text-base text-slate-500">Data analisis persebaran peserta menyeluruh di seluruh wilayah Indonesia.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
        
        <div class="lg:col-span-3 bg-white p-6 rounded-2xl border-2 border-slate-100 flex flex-col justify-between min-h-[250px]">
            <div>
                <span class="text-[11px] font-bold tracking-widest text-slate-400 uppercase block mb-1">Total Partisipan Terverifikasi</span>
                <p class="text-6xl font-black text-emerald-600 tracking-tight">{{ $totalPeserta }}</p>
            </div>
            <div class="text-xs text-slate-400 border-t border-slate-100 pt-4 mt-4">
                Mencakup akumulasi data dari <strong class="text-slate-700">{{ $stats->count() }} Provinsi</strong> yang telah mendaftarkan anggotanya ke dalam sistem.
            </div>
        </div>

        <div class="lg:col-span-9 bg-white p-6 rounded-2xl border-2 border-slate-100 flex flex-col justify-between min-h-[400px]">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Grafik Perbandingan Seluruh Provinsi
                </h3>
                <span class="text-[11px] text-slate-400 italic">Scroll ke bawah pada grafik jika data memanjang</span>
            </div>
            <div class="relative w-full overflow-y-auto pr-2" style="max-height: 380px;">
                <div style="height: {{ max($stats->count() * 25, 350) }}px;">
                    <canvas id="participantChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <div class="lg:col-span-7 bg-white p-6 rounded-2xl border-2 border-slate-100 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-50 pb-2">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Kerapatan Geografis Provinsi
                    </h3>
                    <p class="text-xs text-slate-400">Arahkan kursor atau klik wilayah untuk detail cepat.</p>
                </div>
                <div class="flex items-center gap-1.5 bg-slate-50 p-1.5 rounded-lg text-[10px] font-mono text-slate-600 self-start">
                    <span>Min</span>
                    <span class="w-3 h-3 bg-[#a7f3d0] inline-block rounded-sm"></span>
                    <span class="w-3 h-3 bg-[#34d399] inline-block rounded-sm"></span>
                    <span class="w-3 h-3 bg-[#059669] inline-block rounded-sm"></span>
                    <span class="w-3 h-3 bg-[#047857] inline-block rounded-sm"></span>
                    <span>Max</span>
                </div>
            </div>
            <div id="map" class="w-full h-[450px] rounded-xl z-0 bg-slate-50 border border-slate-100"></div>
        </div>

        <div class="lg:col-span-5 bg-white p-6 rounded-2xl border-2 border-slate-100 flex flex-col justify-between h-fit">
            <div class="space-y-3 mb-4">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Tabel Rekapitulasi Wilayah
                </h3>
                <input type="text" id="tableSearch" placeholder="Cari nama provinsi..." class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-hidden focus:border-emerald-500 transition-colors">
            </div>

            <div class="overflow-y-auto pr-1 border border-slate-50 rounded-lg" style="max-height: 400px;">
                <table class="min-w-full divide-y divide-slate-100 text-xs text-left" id="provinsiTable">
                    <thead class="bg-slate-50 sticky top-0 font-bold text-slate-700 uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-2.5">Nama Provinsi</th>
                            <th class="px-4 py-2.5 text-right">Total Peserta</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-600">
                        @forelse($stats as $row)
                            <tr class="hover:bg-emerald-50/50 transition-colors">
                                <td class="px-4 py-3 font-medium uppercase text-slate-800">{{ $row->provinsi }}</td>
                                <td class="px-4 py-3 text-right font-bold text-emerald-700 font-mono">{{ $row->total }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-8 text-center text-slate-400 italic">Belum ada data terekam.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // DATA VARIABEL UTAMA DARI LARAVEL (Dipakai bersama oleh Chart & Map)
    const rawStatsData = @json($stats);
    const rawDataProvinsi = @json($stats->pluck('total', 'provinsi'));
    const participantsAddressData = @json($participantsData);
    const dataProvinsi = {};
    const dataKabupaten = {};

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
            .replace(/IRIAN\s+JAYA\s/g, 'PAPUA')
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

    function cleanKabupatenName(name) {
        if (!name) return "";
        return name.toUpperCase()
            .replace(/KABUPATEN\s+/g, '')
            .replace(/KAB\.\s+/g, '')
            .replace(/KOTA\s+/g, '')
            .trim();
    }

    // Normalisasikan key dari database agar siap dicocokkan
    participantsAddressData.forEach(p => {
        if (p.provinsi) {
            const provClean = cleanProvinceName(p.provinsi);
            dataProvinsi[provClean] = (dataProvinsi[provClean] || 0) + 1;

            if (p.kabupaten) {
                const kabClean = cleanKabupatenName(p.kabupaten);
                if (!dataKabupaten[provClean]) {
                    dataKabupaten[provClean] = {};
                }
                dataKabupaten[provClean][kabClean] = (dataKabupaten[provClean][kabClean] || 0) + 1;
            }
        }
    });

    // =========================================================
    // 1. GRAFIK BATANG HORIZONTAL (Mampu Menampung Banyak Data)
    // =========================================================
    const ctx = document.getElementById('participantChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: rawStatsData.map(item => item.provinsi.toUpperCase()),
            datasets: [{
                label: 'Jumlah Peserta Resmi',
                data: rawStatsData.map(item => item.total),
                backgroundColor: '#059669',
                hoverBackgroundColor: '#047857',
                borderRadius: 4,
                borderSkipped: false,
                barThickness: 14
            }]
        },
        options: {
            indexAxis: 'y', // Mengubah orientasi menjadi horizontal mendatar
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false } // Sembunyikan label kotak atas karena sudah jelas
            },
            scales: {
                x: {
                    grid: { color: '#f1f5f9' },
                    ticks: { color: '#64748b', font: { size: 10, weight: '600' }, stepSize: 1 }
                },
                y: {
                    grid: { display: false },
                    ticks: { color: '#334155', font: { size: 10, weight: '700' } }
                }
            }
        }
    });

    // =========================================================
    // 2. INTERAKTIVITAS PENCARIAN LIVE FILTER DI TABEL SAMPING
    // =========================================================
    document.getElementById('tableSearch').addEventListener('keyup', function() {
        const query = this.value.toUpperCase();
        const rows = document.querySelectorAll('#provinsiTable tbody tr');
        
        rows.forEach(row => {
            const provinceName = row.cells[0] ? row.cells[0].innerText : '';
            if (provinceName.includes(query)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });

    // =========================================================
    // 3. PETA PROVINSI (CHOROPLETH LEAFLET INTERAKTIF)
    // =========================================================
    var map = L.map('map', { scrollWheelZoom: false }).setView([-2.5, 118.0], 5);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '© OpenStreetMap © CARTO'
    }).addTo(map);

    // Fungsi Gradasi Warna Bersih (Hijau - Abu)
    function getColor(d) {
        return d > 50  ? '#047857' :
               d > 20  ? '#059669' :
               d > 10  ? '#34d399' :
               d > 0   ? '#a7f3d0' :
                         '#f8fafc';
    }

    function getRegencyColor(d) {
        return d > 10  ? '#047857' :
               d > 5   ? '#059669' :
               d > 2   ? '#10b981' :
               d > 0   ? '#34d399' :
                         '#f8fafc';
    }

    function styleFeature(feature) {
        const rawName = feature.properties.PROVINSI || feature.properties.Provinsi || feature.properties.provinsi || feature.properties.Propinsi || feature.properties.propinsi || feature.properties.NAME_1 || feature.properties.PROP_NAME || "";
        const cleanedName = cleanProvinceName(rawName);
        const count = dataProvinsi[cleanedName] || 0;
        
        return {
            fillColor: getColor(count),
            weight: 1.5,
            opacity: 1,
            color: '#ffffff',
            fillOpacity: 0.85
        };
    }

    let geojsonLayer;
    let regencyGeoJsonData = null;
    let regencyLayer = null;
    let isDrilledDown = false;
    let backButtonInstance = null;

    const BackButtonControl = L.Control.extend({
        options: { position: 'topright' },
        onAdd: function (map) {
            const container = L.DomUtil.create('div', 'leaflet-bar leaflet-control');
            const button = L.DomUtil.create('a', '', container);
            button.href = '#';
            button.innerHTML = '⬅ Peta Nasional';
            button.style.backgroundColor = 'white';
            button.style.padding = '6px 10px';
            button.style.width = 'auto';
            button.style.height = 'auto';
            button.style.fontSize = '11px';
            button.style.fontWeight = 'bold';
            button.style.color = '#047857';
            button.style.textDecoration = 'none';
            button.style.display = 'inline-flex';
            button.style.alignItems = 'center';
            button.style.borderRadius = '4px';

            L.DomEvent.on(button, 'click', function (e) {
                L.DomEvent.stopPropagation(e);
                L.DomEvent.preventDefault(e);
                resetToNationalMap();
            });

            return container;
        }
    });

    function drillDownToProvince(rawProvinceName, bounds) {
        const cleanedProvName = cleanProvinceName(rawProvinceName);
        isDrilledDown = true;
        map.fitBounds(bounds);

        if (!backButtonInstance) {
            backButtonInstance = new BackButtonControl();
            map.addControl(backButtonInstance);
        }

        if (regencyGeoJsonData) {
            renderRegenciesOfProvince(cleanedProvName);
        } else {
            // Tampilkan loading popup sementara mendownload file 12MB (di-stream/compress)
            const loadingPopup = L.popup()
                .setLatLng(bounds.getCenter())
                .setContent('<div class="text-xs text-slate-500 font-bold p-1"><span class="animate-pulse">🔄 Loading Peta Kabupaten...</span></div>')
                .openOn(map);

            fetch('https://raw.githubusercontent.com/superpikar/indonesia-geojson/master/indonesia-regency-city.json')
                .then(res => res.json())
                .then(data => {
                    regencyGeoJsonData = data;
                    map.closePopup(loadingPopup);
                    renderRegenciesOfProvince(cleanedProvName);
                })
                .catch(err => {
                    console.error("Gagal memuat peta kabupaten:", err);
                    map.closePopup(loadingPopup);
                    alert("Gagal memuat batas kabupaten/kota.");
                });
        }
    }

    function renderRegenciesOfProvince(cleanedProvName) {
        if (regencyLayer) {
            map.removeLayer(regencyLayer);
        }
        if (geojsonLayer) {
            map.removeLayer(geojsonLayer);
        }

        const filteredFeatures = regencyGeoJsonData.features.filter(f => {
            const fProv = cleanProvinceName(f.properties.state_name);
            return fProv === cleanedProvName;
        });

        const filteredGeoJson = {
            type: "FeatureCollection",
            features: filteredFeatures
        };

        regencyLayer = L.geoJson(filteredGeoJson, {
            style: function(feature) {
                const kabName = cleanKabupatenName(feature.properties.regency_na);
                const count = (dataKabupaten[cleanedProvName] && dataKabupaten[cleanedProvName][kabName]) || 0;
                return {
                    fillColor: getRegencyColor(count),
                    weight: 1.5,
                    opacity: 1,
                    color: '#ffffff',
                    fillOpacity: 0.85
                };
            },
            onEachFeature: function(feature, layer) {
                const kabRaw = feature.properties.regency_na;
                const kabName = cleanKabupatenName(kabRaw);
                const count = (dataKabupaten[cleanedProvName] && dataKabupaten[cleanedProvName][kabName]) || 0;
                
                layer.bindPopup(`
                    <div class="font-sans p-1 text-center">
                        <b class="text-xs text-slate-900 block border-b border-slate-100 pb-1 mb-1 uppercase tracking-wide">${kabRaw}</b>
                        <span class="text-[11px] text-slate-500">Total: <strong class="text-emerald-600">${count} Peserta</strong></span>
                    </div>
                `, { closeButton: false });

                layer.on({
                    mouseover: function (e) {
                        e.target.setStyle({ weight: 2.5, color: '#047857', fillOpacity: 0.95 });
                        e.target.openPopup();
                    },
                    mouseout: function (e) {
                        regencyLayer.resetStyle(e.target);
                        e.target.closePopup();
                    }
                });
            }
        }).addTo(map);
    }

    function resetToNationalMap() {
        isDrilledDown = false;
        if (regencyLayer) {
            map.removeLayer(regencyLayer);
            regencyLayer = null;
        }
        if (geojsonLayer) {
            geojsonLayer.addTo(map);
        }
        map.setView([-2.5, 118.0], 5);
        if (backButtonInstance) {
            map.removeControl(backButtonInstance);
            backButtonInstance = null;
        }
    }

    function onEachFeature(feature, layer) {
        const rawName = feature.properties.PROVINSI || feature.properties.Provinsi || feature.properties.provinsi || feature.properties.Propinsi || feature.properties.propinsi || feature.properties.NAME_1 || "Tidak Diketahui";
        const cleanedName = cleanProvinceName(rawName);
        const count = dataProvinsi[cleanedName] || 0;
        
        layer.bindPopup(`
            <div class="font-sans p-1 text-center">
                <b class="text-xs text-slate-900 block border-b border-slate-100 pb-1 mb-1 uppercase tracking-wide">${rawName}</b>
                <span class="text-[11px] text-slate-500">Total: <strong class="text-emerald-600">${count} Peserta</strong></span>
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
                if (!isDrilledDown) {
                    drillDownToProvince(rawName, e.target.getBounds());
                }
            }
        });
    }

    // Pemanggilan Data Berkas Batas Wilayah Indonesia Resmi
    fetch('https://raw.githubusercontent.com/denyherianto/indonesia-geojson-topojson-maps-with-38-provinces/main/GeoJSON/indonesia-38-provinces.geojson')
        .then(response => response.json())
        .then(geojsonData => {
            geojsonLayer = L.geoJson(geojsonData, {
                style: styleFeature,
                onEachFeature: onEachFeature
            }).addTo(map);
        })
        .catch(err => console.error('Gagal memuat struktur koordinat peta:', err));
</script>

<style>
    /* Styling Sederhana custom Scrollbar agar selaras dengan tema UI */
    .overflow-y-auto::-webkit-scrollbar {
        width: 5px;
    }
    .overflow-y-auto::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }
    .overflow-y-auto::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    .overflow-y-auto::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    .leaflet-popup-content-wrapper {
        border-radius: 8px !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
    }
</style>
@endsection