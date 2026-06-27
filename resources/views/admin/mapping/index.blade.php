@extends('layouts.admin')

@section('title', 'Map Client & Topologi Jaringan')

@section('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 12px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        text-decoration: none;
    }

    .btn-primary {
        background: var(--primary-gradient);
        color: white;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);
    }
    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 15px rgba(79, 70, 229, 0.25);
    }

    .btn-info {
        background-color: #eff6ff;
        color: #2563eb;
    }
    .btn-info:hover {
        background-color: #dbeafe;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 24px;
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
    }

    .stat-card {
        background-color: white;
        border-radius: 20px;
        padding: 20px;
        border: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: var(--shadow-sm);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .stat-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .stat-value {
        font-family: 'Outfit', sans-serif;
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--text-dark);
    }

    .stat-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-gray);
    }

    .stat-icon {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
    }

    .stat-icon.odc {
        background-color: #eff6ff;
        color: #2563eb;
    }
    .stat-icon.odp {
        background-color: #f0fdf4;
        color: #10b981;
    }
    .stat-icon.client {
        background-color: #faf5ff;
        color: #8b5cf6;
    }

    /* Map Box */
    .map-box {
        display: flex;
        flex-direction: column;
        position: relative;
        overflow: visible !important;
    }

    .map-legend {
        background-color: white;
        border-radius: 12px;
        padding: 14px;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-md);
        font-size: 0.85rem;
        line-height: 1.5;
        z-index: 1000;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 6px;
    }
    .legend-item:last-child {
        margin-bottom: 0;
    }

    .legend-color {
        width: 16px;
        height: 16px;
        border-radius: 4px;
        display: inline-block;
    }

    .legend-line {
        height: 3px;
        width: 20px;
        display: inline-block;
    }

    .map-link {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        color: #4f46e5;
        text-decoration: none;
        font-weight: 600;
        margin-top: 6px;
    }
    .map-link:hover {
        text-decoration: underline;
    }

    /* Map UI Adjustments */
    .filter-panel {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        padding: 12px 16px;
        background-color: #f8fafc;
        border-bottom: 1px solid var(--border-color);
    }

    /* Search Pelanggan on Map styles */
    .search-container {
        position: relative;
        display: inline-block;
    }
    .search-input {
        padding: 8px 14px 8px 36px;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        font-size: 0.85rem;
        font-weight: 500;
        width: 200px;
        outline: none;
        transition: all 0.3s;
        background-color: #f8fafc;
        color: var(--text-dark);
    }
    .search-input:focus {
        width: 260px;
        border-color: #4f46e5;
        background-color: white;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    .search-icon-inside {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-gray);
        font-size: 0.85rem;
        pointer-events: none;
    }
    .search-results-dropdown {
        position: absolute;
        top: calc(100% + 6px);
        right: 0;
        width: 280px;
        background-color: white;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-lg);
        max-height: 250px;
        overflow-y: auto;
        display: none;
        z-index: 1050;
    }
    .search-result-item {
        padding: 10px 14px;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        transition: background-color 0.2s;
        display: flex;
        flex-direction: column;
        gap: 2px;
        text-align: left;
    }
    .search-result-item:last-child {
        border-bottom: none;
    }
    .search-result-item:hover {
        background-color: #f8fafc;
    }
    .search-result-name {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-dark);
    }
    .search-result-meta {
        font-size: 0.75rem;
        color: var(--text-gray);
    }
    .search-result-no-match {
        padding: 12px;
        text-align: center;
        font-size: 0.8rem;
        color: var(--text-gray);
    }
</style>
@endsection

@section('content')
<!-- Stats Cards -->
<div class="stats-grid">
    <!-- Card ODC -->
    <div class="stat-card">
        <div class="stat-info">
            <span class="stat-value">{{ $totalOdc }}</span>
            <span class="stat-label">ODC Berkoordinat</span>
        </div>
        <div class="stat-icon odc">
            <i class="fa-solid fa-circle-nodes"></i>
        </div>
    </div>

    <!-- Card ODP -->
    <div class="stat-card">
        <div class="stat-info">
            <span class="stat-value">{{ $totalOdp }}</span>
            <span class="stat-label">ODP Berkoordinat</span>
        </div>
        <div class="stat-icon odp">
            <i class="fa-solid fa-diagram-project"></i>
        </div>
    </div>

    <!-- Card Pelanggan -->
    <div class="stat-card">
        <div class="stat-info">
            <span class="stat-value">{{ $totalPelanggan }}</span>
            <span class="stat-label">Pelanggan Berkoordinat</span>
        </div>
        <div class="stat-icon client">
            <i class="fa-solid fa-user-group"></i>
        </div>
    </div>
</div>

<!-- Map Card -->
<div class="card map-box" style="margin: 0;">
    <div class="card-header" style="justify-content: space-between;">
        <div class="card-title">
            <i class="fa-solid fa-map-location-dot"></i>
            <span>Peta Master Topologi Jaringan & Lokasi Client</span>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <!-- Search Pelanggan Dropdown -->
            <div class="search-container">
                <i class="fa-solid fa-magnifying-glass search-icon-inside"></i>
                <input type="text" id="search-pelanggan" class="search-input" placeholder="Cari pelanggan..." autocomplete="off">
                <div id="search-results" class="search-results-dropdown"></div>
            </div>
            
            <button class="btn btn-info" id="btn-lokasi-saya" style="padding: 6px 12px; font-size: 0.8rem;">
                <i class="fa-solid fa-location-crosshairs"></i> Lokasi Saya
            </button>
        </div>
    </div>

    <!-- Map Filter Toolbar -->
    <div class="filter-panel">
        <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-dark); margin-right: 8px;">Tampilkan:</span>
        
        <label style="display:flex; align-items:center; gap:6px; font-size:0.85rem; font-weight:600; cursor:pointer; color:#1e293b;">
            <input type="checkbox" id="chk-odc" checked style="width:16px; height:16px; accent-color:#2563eb;">
            <span>ODC (Biru)</span>
        </label>
        
        <label style="display:flex; align-items:center; gap:6px; font-size:0.85rem; font-weight:600; cursor:pointer; color:#1e293b;">
            <input type="checkbox" id="chk-odp" checked style="width:16px; height:16px; accent-color:#10b981;">
            <span>ODP (Hijau)</span>
        </label>
        
        <label style="display:flex; align-items:center; gap:6px; font-size:0.85rem; font-weight:600; cursor:pointer; color:#1e293b;">
            <input type="checkbox" id="chk-pelanggan" checked style="width:16px; height:16px; accent-color:#8b5cf6;">
            <span>Pelanggan (Ungu)</span>
        </label>

        <label style="display:flex; align-items:center; gap:6px; font-size:0.85rem; font-weight:700; cursor:pointer; color:#4f46e5; margin-left: 10px;">
            <input type="checkbox" id="chk-topology" checked style="width:16px; height:16px; accent-color:#4f46e5;">
            <span>Jalur Topologi (ODC &rarr; ODP &rarr; Client)</span>
        </label>
    </div>

    <!-- Map Area -->
    <div class="card-body" style="padding: 0; position: relative; z-index: 1;">
        <div id="master-map" style="height: 600px; z-index: 1;"></div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        initMasterMap();
        setupSearchPelanggan();
    });

    var map;
    var odcLayerGroup;
    var odpLayerGroup;
    var pelangganLayerGroup;
    var lineLayerGroup;
    var pelangganMarkers = {}; // Store marker references by pelanggan code

    var odcCoordinates = []; // List of {nama_odc, lat, lng}
    var odpCoordinates = []; // List of {nama_odp, lat, lng, nama_odc}
    var pelangganCoordinates = []; // List of {nama_pelanggan, lat, lng, odp_name}

    var odcMap = {}; // name -> [lat, lng]
    var odcIdMap = {}; // id -> [lat, lng]
    var odpMap = {}; // name -> [lat, lng]

    function initMasterMap() {
        // Default Center
        map = L.map('master-map').setView([-2.548926, 118.014863], 5);

        // Layers
        var googleStreets = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
            attribution: '&copy; Google Maps'
        });

        var googleHybrid = L.tileLayer('https://{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
            attribution: '&copy; Google Maps'
        });

        var googleSatellite = L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
            attribution: '&copy; Google Maps'
        });

        var osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        });

        // Add Google Streets as default
        googleStreets.addTo(map);

        var baseLayers = {
            "Google Maps (Jalan)": googleStreets,
            "Google Maps (Satelit)": googleSatellite,
            "Google Maps (Hibrida)": googleHybrid,
            "OpenStreetMap": osm
        };

        L.control.layers(baseLayers).addTo(map);

        // Group Layers
        odcLayerGroup = L.layerGroup().addTo(map);
        odpLayerGroup = L.layerGroup().addTo(map);
        pelangganLayerGroup = L.layerGroup().addTo(map);
        lineLayerGroup = L.layerGroup().addTo(map);

        // Add Legend
        addMapLegend();

        // Load data in sequence to resolve topology lines correctly
        loadDataAndPlot();

        // Listen for filter toggles
        setupFilterListeners();

        // Geolocation locate
        setupGeolocation();
    }

    function addMapLegend() {
        var legend = L.control({ position: 'bottomright' });
        legend.onAdd = function (map) {
            var div = L.DomUtil.create('div', 'map-legend');
            div.innerHTML = `
                <h4 style="font-family:'Outfit',sans-serif; margin: 0 0 8px 0; font-size:0.9rem; color:var(--text-dark); border-bottom:1px solid #e2e8f0; padding-bottom:4px;">Legenda Peta</h4>
                <div class="legend-item">
                    <span class="legend-color" style="background-color:#ef4444;"></span>
                    <span>ODC Utama</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color" style="background-color:#2563eb;"></span>
                    <span>ODC Distribusi</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color" style="background-color:#10b981;"></span>
                    <span>Splitter ODP</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color" style="background-color:#8b5cf6;"></span>
                    <span>Pelanggan (Client)</span>
                </div>
                <div class="legend-item">
                    <span class="legend-line" style="border-top:3px dashed #e11d48; margin-top:2px;"></span>
                    <span>Koneksi ODC Utama &rarr; Distribusi</span>
                </div>
                <div class="legend-item">
                    <span class="legend-line" style="border-top:2.5px dashed #4f46e5; margin-top:2px;"></span>
                    <span>Koneksi ODC Distribusi &rarr; ODP</span>
                </div>
                <div class="legend-item">
                    <span class="legend-line" style="border-top:2px solid #10b981; margin-top:2px;"></span>
                    <span>Koneksi ODP &rarr; Client</span>
                </div>
            `;
            return div;
        };
        legend.addTo(map);
    }

    function loadDataAndPlot() {
        // Step 1: Load ODC
        fetch('{{ route("admin.odc.coordinates") }}')
            .then(res => res.json())
            .then(odcs => {
                odcCoordinates = odcs;
                odcs.forEach(o => {
                    odcMap[o.nama_odc] = [o.lat, o.lng];
                    odcIdMap[o.id_odc] = [o.lat, o.lng];
                });

                // Step 2: Load ODP
                return fetch('{{ route("admin.odp.coordinates") }}');
            })
            .then(res => res.json())
            .then(odps => {
                odpCoordinates = odps;
                odps.forEach(o => {
                    odpMap[o.nama_odp] = [o.lat, o.lng];
                });

                // Step 3: Load Pelanggan
                return fetch('{{ route("admin.mapping.coordinates") }}');
            })
            .then(res => res.json())
            .then(pelanggan => {
                pelangganCoordinates = pelanggan;

                // Plot all markers and topology lines
                renderMapElements();
            })
            .catch(err => console.error("Gagal memuat peta master:", err));
    }

    function renderMapElements() {
        odcLayerGroup.clearLayers();
        odpLayerGroup.clearLayers();
        pelangganLayerGroup.clearLayers();
        lineLayerGroup.clearLayers();
        pelangganMarkers = {}; // Reset marker references

        var bounds = [];

        // 1. Plot ODC (Red for Utama, Blue for Distribusi)
        if (document.getElementById('chk-odc').checked) {
            odcCoordinates.forEach(o => {
                var markerColor = o.jenis_odc === 'utama' ? 'red' : 'blue';
                var typeLabel = o.jenis_odc === 'utama' ? 'Utama (Main)' : 'Distribusi';
                var marker = L.marker([o.lat, o.lng], {
                    icon: L.icon({
                        iconUrl: `https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-${markerColor}.png`,
                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    })
                }).bindPopup(`
                    <div style="font-family:'Inter',sans-serif; font-size:0.85rem; line-height:1.4;">
                        <h4 style="font-family:'Outfit',sans-serif; margin:0 0 6px 0; color:#2563eb; font-size:0.95rem;">[ODC] ${o.nama_odc}</h4>
                        <strong>Jenis ODC:</strong> <span style="font-weight:700;">${typeLabel}</span><br>
                        <strong>Perangkat:</strong> ${o.perangkat_odc}<br>
                        <strong>Kapasitas Port:</strong> ${o.port_odc} Port<br>
                        <strong>Redaman:</strong> <span style="color:#ea580c; font-weight:600;">${o.redaman || '-'}</span><br>
                        <strong>Tube/Core:</strong> ${o.tube ? `<span style="font-weight:600;">${o.tube}</span>` : '-'} / ${o.core_number ? `Core ${o.core_number}` : '-'}<br>
                        <a href="https://www.google.com/maps?q=${o.lat},${o.lng}" target="_blank" class="map-link" style="margin-top:8px;">
                            <i class="fa-solid fa-map-location-dot"></i> Buka di Google Maps
                        </a>
                    </div>
                `);
                odcLayerGroup.addLayer(marker);
                bounds.push([o.lat, o.lng]);
            });
        }

        // 2. Plot ODP (Green)
        if (document.getElementById('chk-odp').checked) {
            odpCoordinates.forEach(o => {
                var clientsHtml = "";
                if (o.clients && o.clients.length > 0) {
                    clientsHtml = `<div style="margin-top: 8px; border-top: 1px solid #e2e8f0; padding-top: 6px;">
                        <strong style="color: #475569; font-size: 0.8rem;">Client Terhubung (${o.clients.length}):</strong>
                        <ul style="margin: 4px 0 0 0; padding-left: 16px; font-size: 0.8rem; color: #475569; max-height: 100px; overflow-y: auto;">`;
                    o.clients.forEach(c => {
                        clientsHtml += `<li><strong>${c.nama}</strong> (${c.kode})</li>`;
                    });
                    clientsHtml += `</ul></div>`;
                } else {
                    clientsHtml = `<div style="margin-top: 8px; border-top: 1px solid #e2e8f0; padding-top: 6px; font-style: italic; font-size: 0.8rem; color: #94a3b8;">
                        Belum ada client terhubung.
                    </div>`;
                }

                var marker = L.marker([o.lat, o.lng], {
                    icon: L.icon({
                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    })
                }).bindPopup(`
                    <div style="font-family:'Inter',sans-serif; font-size:0.85rem; line-height:1.4; min-width: 210px;">
                        <h4 style="font-family:'Outfit',sans-serif; margin:0 0 6px 0; color:#10b981; font-size:0.95rem;">[ODP] ${o.nama_odp}</h4>
                        <strong>ODC Induk:</strong> ${o.nama_odc}<br>
                        <strong>Kapasitas Port:</strong> ${o.port_odp} Port<br>
                        <strong>Redaman ODP:</strong> <span style="color:#ea580c; font-weight:600;">${o.redaman}</span><br>
                        ${clientsHtml}
                        <a href="https://www.google.com/maps?q=${o.lat},${o.lng}" target="_blank" class="map-link">
                            <i class="fa-solid fa-map-location-dot"></i> Buka di Google Maps
                        </a>
                    </div>
                `);
                odpLayerGroup.addLayer(marker);
                bounds.push([o.lat, o.lng]);
            });
        }

        // 3. Plot Pelanggan (Violet)
        if (document.getElementById('chk-pelanggan').checked) {
            pelangganCoordinates.forEach(p => {
                var marker = L.marker([p.lat, p.lng], {
                    icon: L.icon({
                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-violet.png',
                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    })
                }).bindPopup(`
                    <div style="font-family:'Inter',sans-serif; font-size:0.85rem; line-height:1.4; min-width: 180px;">
                        <h4 style="font-family:'Outfit',sans-serif; margin:0 0 6px 0; color:#8b5cf6; font-size:0.95rem;">${p.nama_pelanggan}</h4>
                        <strong>Kode:</strong> ${p.kode_pelanggan}<br>
                        <strong>No. Telp:</strong> ${p.no_telp}<br>
                        <strong>Alamat:</strong> ${p.alamat}<br>
                        <strong>Koneksi ODP:</strong> ${p.odp_name}<br>
                        <div style="display:flex; justify-content:space-between; margin-top:8px; align-items:center; gap:10px;">
                            <a href="https://www.google.com/maps?q=${p.lat},${p.lng}" target="_blank" class="map-link" style="margin:0;">
                                <i class="fa-solid fa-map-location-dot"></i> Maps
                            </a>
                            <a href="{{ route('admin.pelanggan.index') }}?search=${p.kode_pelanggan}" style="font-size:0.8rem; font-weight:600; text-decoration:none; color:#4f46e5;">
                                <i class="fa-solid fa-user"></i> Lihat Profil
                            </a>
                        </div>
                    </div>
                `);
                pelangganLayerGroup.addLayer(marker);
                bounds.push([p.lat, p.lng]);
                pelangganMarkers[p.kode_pelanggan] = marker; // Save reference
            });
        }

        // 4. Draw Lines (Topology)
        if (document.getElementById('chk-topology').checked) {
            // Draw ODC Utama -> ODC Distribusi lines
            odcCoordinates.forEach(o => {
                if (o.jenis_odc === 'distribusi' && o.parent_id) {
                    var parentCoord = odcIdMap[o.parent_id];
                    if (parentCoord && document.getElementById('chk-odc').checked) {
                        var polyline = L.polyline([[o.lat, o.lng], parentCoord], {
                            color: '#e11d48', // Rose color for ODC Utama -> Distribusi connection line
                            weight: 3,
                            opacity: 0.85,
                            dashArray: '4, 4'
                        });
                        lineLayerGroup.addLayer(polyline);
                    }
                }
            });

            // Draw ODC -> ODP lines (Solid Purple/Indigo Line)
            odpCoordinates.forEach(odp => {
                var odcCoord = odcMap[odp.nama_odc];
                if (odcCoord && document.getElementById('chk-odc').checked && document.getElementById('chk-odp').checked) {
                    var polyline = L.polyline([[odp.lat, odp.lng], odcCoord], {
                        color: '#6366f1',
                        weight: 2.5,
                        opacity: 0.7,
                        dashArray: '6, 6'
                    });
                    lineLayerGroup.addLayer(polyline);
                }
            });

            // Draw ODP -> Client lines (Solid Emerald Green Line)
            pelangganCoordinates.forEach(pel => {
                var odpCoord = odpMap[pel.odp_name];
                if (odpCoord && document.getElementById('chk-odp').checked && document.getElementById('chk-pelanggan').checked) {
                    var polyline = L.polyline([[pel.lat, pel.lng], odpCoord], {
                        color: '#10b981',
                        weight: 1.8,
                        opacity: 0.55
                    });
                    lineLayerGroup.addLayer(polyline);
                }
            });
        }

        // Adjust map bounds to fit all plotted elements
        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [40, 40] });
        }
    }

    function setupFilterListeners() {
        var filters = ['chk-odc', 'chk-odp', 'chk-pelanggan', 'chk-topology'];
        filters.forEach(id => {
            document.getElementById(id).addEventListener('change', function () {
                renderMapElements();
            });
        });
    }

    function setupGeolocation() {
        document.getElementById('btn-lokasi-saya').addEventListener('click', function () {
            if ('geolocation' in navigator) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    var lat = position.coords.latitude;
                    var lng = position.coords.longitude;
                    map.setView([lat, lng], 15);

                    L.marker([lat, lng], {
                        icon: L.icon({
                            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                            iconSize: [25, 41],
                            iconAnchor: [12, 41],
                            popupAnchor: [1, -34],
                            shadowSize: [41, 41]
                        })
                    }).addTo(map).bindPopup("Lokasi Saya saat ini").openPopup();
                }, function (error) {
                    console.error("Gagal mendapatkan lokasi:", error);
                    var errMsg = error.message;
                    if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
                        errMsg = "Only secure origins are allowed. Halaman ini diakses melalui HTTP biasa, sedangkan Google Chrome hanya mengizinkan akses GPS pada koneksi aman (HTTPS). Silakan akses website via HTTPS atau pilih lokasi secara manual di peta.";
                    }
                    alert("Gagal mendapatkan lokasi: " + errMsg);
                }, { enableHighAccuracy: true });
            } else {
                alert("Geolocation tidak didukung oleh browser ini.");
            }
        });
    }

    function setupSearchPelanggan() {
        var searchInput = document.getElementById('search-pelanggan');
        var searchResults = document.getElementById('search-results');

        searchInput.addEventListener('input', function() {
            var query = this.value.trim().toLowerCase();
            if (query.length < 1) {
                searchResults.style.display = 'none';
                return;
            }

            var matches = pelangganCoordinates.filter(function(p) {
                return p.nama_pelanggan.toLowerCase().includes(query) || 
                       p.kode_pelanggan.toLowerCase().includes(query);
            });

            var limit = 10;
            var displayMatches = matches.slice(0, limit);

            if (displayMatches.length === 0) {
                searchResults.innerHTML = '<div class="search-result-no-match">Tidak ada pelanggan ditemukan</div>';
            } else {
                var html = '';
                displayMatches.forEach(function(p) {
                    html += `
                        <div class="search-result-item" onclick="zoomToPelanggan('${p.kode_pelanggan}', ${p.lat}, ${p.lng})">
                            <span class="search-result-name">${p.nama_pelanggan}</span>
                            <span class="search-result-meta">${p.kode_pelanggan} • ODP: ${p.odp_name}</span>
                        </div>
                    `;
                });
                
                if (matches.length > limit) {
                    html += `<div class="search-result-no-match" style="border-top:1px solid #f1f5f9; padding:6px 12px; font-size:0.75rem;">Menampilkan ${limit} dari ${matches.length} hasil</div>`;
                }
                
                searchResults.innerHTML = html;
            }
            searchResults.style.display = 'block';
        });

        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });

        searchInput.addEventListener('focus', function() {
            if (this.value.trim().length >= 1) {
                searchResults.style.display = 'block';
            }
        });
    }

    function zoomToPelanggan(kodePelanggan, lat, lng) {
        document.getElementById('search-results').style.display = 'none';
        document.getElementById('search-pelanggan').value = '';

        var chkPelanggan = document.getElementById('chk-pelanggan');
        if (!chkPelanggan.checked) {
            chkPelanggan.checked = true;
            renderMapElements();
        }

        var marker = pelangganMarkers[kodePelanggan];
        if (marker) {
            map.setView([lat, lng], 17);
            marker.openPopup();
        } else {
            map.setView([lat, lng], 17);
        }
    }
</script>
@endsection
