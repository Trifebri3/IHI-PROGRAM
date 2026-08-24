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
        minZoom: 4
    }).setView([-2.5, 118.0], 5);

    // Menggunakan base tile map abu-abu terang minimalis agar warna hijau provinsi terlihat kontras
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '© OpenStreetMap © CARTO'
    }).addTo(map);

    // Object penampung hitungan jumlah peserta per provinsi dan kabupaten
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

    function cleanKabupatenName(name) {
        if (!name) return "";
        return name.toUpperCase()
            .replace(/KABUPATEN\s+/g, '')
            .replace(/KAB\.\s+/g, '')
            .replace(/KOTA\s+/g, '')
            .trim();
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

    function getRegencyColor(d) {
        return d > 10  ? '#047857' :
               d > 5   ? '#059669' :
               d > 2   ? '#10b981' :
               d > 0   ? '#34d399' :
                         '#f8fafc';
    }

    // Mengatur style polygon batas wilayah provinsi
    function styleFeature(feature) {
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
                if (!isDrilledDown) {
                    drillDownToProvince(rawName, e.target.getBounds());
                }
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

                    if (p.kabupaten) {
                        const kabClean = cleanKabupatenName(p.kabupaten);
                        if (!dataKabupaten[cleanedName]) {
                            dataKabupaten[cleanedName] = {};
                        }
                        dataKabupaten[cleanedName][kabClean] = (dataKabupaten[cleanedName][kabClean] || 0) + 1;
                    }
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