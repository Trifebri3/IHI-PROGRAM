@extends('layouts.public')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    
    <div class="max-w-3xl space-y-3 text-left mb-16">
        <div class="inline-block bg-emerald-50 text-emerald-700 text-xs font-bold px-3 py-1 rounded-md uppercase tracking-wider">
            Direktori Data Transparan
        </div>
        <h2 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">
            Pilih Program untuk <span class="text-emerald-600">Melihat Data</span>
        </h2>
        <p class="text-sm sm:text-base text-slate-500 leading-relaxed">
            Silakan pilih salah satu program aktif di bawah ini untuk meninjau persebaran demografi wilayah atau memeriksa daftar nama partisipan resmi.
        </p>
    </div>

    <!-- LEAFLET CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <!-- PETA PERSEBARAN TOTAL INTERAKTIF -->
    <div class="bg-white p-4 rounded-3xl border-2 border-slate-100 shadow-xs mb-12 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50 p-4 rounded-2xl border border-slate-100">
            <div>
                <div class="inline-block bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2.5 py-1 rounded-md uppercase tracking-widest mb-1.5">
                    Konsolidasi Geografis Nasional
                </div>
                <h3 class="text-xl font-black text-slate-950 tracking-tight">Peta Persebaran Kumulatif Seluruh Program</h3>
                <p class="text-xs text-slate-400 mt-0.5">Menunjukkan akumulasi konsentrasi peserta aktif dari seluruh program kerja Institut Hijau Indonesia.</p>
            </div>
            
            <div class="flex items-center gap-1.5 bg-white p-2 rounded-xl text-[9px] font-mono text-slate-600 border border-slate-100 self-start sm:self-center">
                <span class="font-sans font-bold text-slate-400">Sedikit</span>
                <span class="w-3 h-3 bg-[#a7f3d0] inline-block rounded-xs"></span>
                <span class="w-3 h-3 bg-[#34d399] inline-block rounded-xs"></span>
                <span class="w-3 h-3 bg-[#059669] inline-block rounded-xs"></span>
                <span class="w-3 h-3 bg-[#047857] inline-block rounded-xs"></span>
                <span class="font-sans font-bold text-slate-500">Banyak</span>
            </div>
        </div>

        <div class="relative">
            <div id="total-map" class="w-full h-[460px] rounded-2xl z-0 bg-slate-50"></div>
        </div>
    </div>

    <!-- LEAFLET JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var map = L.map('total-map', { 
                scrollWheelZoom: false,
                minZoom: 4
            }).setView([-2.5, 118.0], 5);

            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                attribution: '© OpenStreetMap © CARTO'
            }).addTo(map);

            const dataProvinsi = {};
            const dataKabupaten = {};

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

            function getColor(d) {
                return d > 100 ? '#047857' :
                       d > 50  ? '#059669' :
                       d > 20  ? '#10b981' :
                       d > 5   ? '#34d399' :
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
                    const loadingPopup = L.popup()
                        .setLatLng(bounds.getCenter())
                        .setContent('<div class="text-xs text-slate-500 font-bold p-1"><span class="animate-pulse">🔄 Loading Peta Kabupaten...</span></div>')
                        .openOn(map);

                    fetch('https://cdn.jsdelivr.net/gh/superpikar/indonesia-geojson@master/indonesia-regency-city.json')
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
                        <span class="text-[11px] text-slate-500">Total: <strong class="text-emerald-600">${count} Peserta Aktif</strong></span>
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

            fetch("{{ route('public.program.map.data.all') }}")
                .then(response => response.json())
                .then(data => {
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

                    return fetch('https://cdn.jsdelivr.net/gh/denyherianto/indonesia-geojson-topojson-maps-with-38-provinces@main/GeoJSON/indonesia-38-provinces.geojson');
                })
                .then(response => response.json())
                .then(geojsonData => {
                    geojsonLayer = L.geoJson(geojsonData, {
                        style: styleFeature,
                        onEachFeature: onEachFeature
                    }).addTo(map);
                })
                .catch(err => {
                    console.error('Gagal memproses visualisasi peta kumulatif:', err);
                    document.getElementById('total-map').innerHTML = `
                        <div class="flex items-center justify-center h-full text-xs text-slate-400 font-semibold p-4">
                            Gagal memuat visualisasi peta persebaran. Silakan muat ulang halaman.
                        </div>`;
                });
        });
    </script>

    <style>
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

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($programs as $index => $prog)
            <div class="bg-white rounded-2xl border-2 border-slate-100 hover:border-emerald-600 shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col justify-between group overflow-hidden relative">
                
                <div class="relative h-48 w-full bg-slate-100 overflow-hidden shrink-0">
                    @if($prog->banner_path)
                        <img src="{{ asset('storage/' . $prog->banner_path) }}" alt="Banner {{ $prog->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-emerald-800 to-emerald-950 flex items-center justify-center p-4">
                            <span class="text-emerald-500/10 font-black text-5xl tracking-widest select-none uppercase font-mono">
                                ACTIVE
                            </span>
                        </div>
                    @endif

                    <div class="absolute inset-0 bg-linear-to-t from-slate-950/30 via-transparent to-transparent"></div>

                    <div class="absolute top-4 left-4 w-14 h-14 rounded-xl bg-white/95 backdrop-blur-xs p-1.5 shadow-md border border-white/20 flex items-center justify-center overflow-hidden transition-transform group-hover:scale-105 duration-300">
                        @if($prog->logo_path)
                            <img src="{{ asset('storage/' . $prog->logo_path) }}" alt="Logo {{ $prog->name }}" class="w-full h-full object-contain rounded-lg">
                        @else
                            <div class="w-full h-full bg-emerald-50 text-emerald-700 flex items-center justify-center font-black font-mono text-base rounded-lg uppercase">
                                {{ substr($prog->name, 0, 2) }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="p-6 flex-1 flex flex-col justify-between bg-white">
                    <div>
                        <h3 class="font-bold text-lg text-slate-900 group-hover:text-emerald-600 transition-colors mb-2 leading-snug uppercase tracking-tight">
                            {{ $prog->name }}
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-500 mb-6 leading-relaxed line-clamp-3">
                            {{ $prog->description }}
                        </p>
                    </div>

                    <div class="space-y-2.5 pt-4 border-t border-slate-100">
                        
                        <a href="{{ route('public.program.stats', $prog->id) }}"
                           class="flex items-center justify-center gap-2 w-full text-center py-3 bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white text-xs font-bold rounded-xl transition-all">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                            <span>Statistik & Persebaran</span>
                        </a>

                        <a href="{{ route('public.program.participants', $prog->id) }}"
                           class="flex items-center justify-center gap-2 w-full text-center py-3 bg-white border border-slate-200 text-slate-700 hover:border-emerald-600 hover:text-emerald-600 text-xs font-bold rounded-xl transition-all">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <span>Peserta Resmi Program</span>
                        </a>
                        
                    </div>
                </div>

            </div>
        @endforeach
    </div>
</div>
@endsection