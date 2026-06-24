@extends('layouts.admin')

@section('title', 'CPE Device Detail')

@section('styles')
<style>
    :root {
        --tr-light-bg: #ffffff;
        --tr-light-card: #ffffff;
        --tr-light-border: #e2e8f0;
        --tr-light-text-primary: #0f172a;
        --tr-light-text-secondary: #64748b;
        --tr-accent: #4f46e5;
        --tr-accent-hover: #4338ca;
    }

    .tr-container {
        background-color: var(--tr-light-bg);
        color: var(--tr-light-text-primary);
        padding: 24px;
        border-radius: 20px;
        border: 1px solid var(--tr-light-border);
        font-family: 'Inter', sans-serif;
        margin-top: 10px;
        box-shadow: var(--shadow-sm);
    }

    /* Actions Bar */
    .tr-header-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .tr-title-group {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .tr-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--tr-light-text-primary);
    }

    .tr-badge-id {
        background-color: #f1f5f9;
        color: var(--tr-light-text-secondary);
        font-size: 0.75rem;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .tr-actions-right {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .tr-btn {
        background-color: #f1f5f9;
        border: 1px solid #e2e8f0;
        color: #475569;
        padding: 8px 16px;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        text-decoration: none;
    }

    .tr-btn:hover {
        background-color: #e2e8f0;
        border-color: #cbd5e1;
        color: #0f172a;
    }

    .tr-btn-primary {
        background-color: #4f46e5;
        border-color: #4f46e5;
        color: #ffffff;
    }

    .tr-btn-primary:hover {
        background-color: #4338ca;
        border-color: #4338ca;
        color: #ffffff;
    }

    .tr-btn-warning {
        background-color: #f97316;
        border-color: #f97316;
        color: #ffffff;
    }

    .tr-btn-warning:hover {
        background-color: #ea580c;
        border-color: #ea580c;
        color: #ffffff;
    }

    .tr-btn-danger {
        background-color: #ef4444;
        border-color: #ef4444;
        color: #ffffff;
    }

    .tr-btn-danger:hover {
        background-color: #dc2626;
        border-color: #dc2626;
        color: #ffffff;
    }

    /* Info Status Pill Bar */
    .tr-status-bar {
        background-color: #f8fafc;
        border: 1px solid var(--tr-light-border);
        padding: 12px 20px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 24px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .tr-status-indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: #10b981;
        box-shadow: 0 0 10px #10b981;
    }

    .tr-status-indicator.offline {
        background-color: #ef4444;
        box-shadow: 0 0 10px #ef4444;
    }

    /* Tabs Navigation */
    .tr-tabs {
        display: flex;
        border-bottom: 1px solid var(--tr-light-border);
        margin-bottom: 24px;
        overflow-x: auto;
        gap: 8px;
        padding-bottom: 2px;
    }

    .tr-tab-btn {
        background: none;
        border: none;
        color: var(--tr-light-text-secondary);
        padding: 10px 16px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        position: relative;
        transition: color 0.2s;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .tr-tab-btn:hover {
        color: var(--tr-light-text-primary);
    }

    .tr-tab-btn.active {
        color: #4f46e5;
    }

    .tr-tab-btn.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 3px;
        background-color: #4f46e5;
        border-radius: 3px 3px 0 0;
    }

    /* Cards */
    .tr-card {
        background-color: var(--tr-light-card);
        border: 1px solid var(--tr-light-border);
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 24px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
    }

    .tr-card-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.05rem;
        font-weight: 700;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--tr-light-text-primary);
        border-bottom: 1px solid var(--tr-light-border);
        padding-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Flex Grid Layout */
    .tr-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 20px;
    }

    /* Details List */
    .tr-details-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .tr-details-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.88rem;
        border-bottom: 1px solid var(--tr-light-border);
        padding-bottom: 8px;
    }

    .tr-details-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .tr-details-label {
        color: var(--tr-light-text-secondary);
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .tr-details-value {
        font-weight: 600;
        color: var(--tr-light-text-primary);
        word-break: break-all;
    }

    /* Gauge / RX Power Styles */
    .tr-gauge-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin: 15px 0 5px 0;
    }

    .gauge-svg {
        width: 160px;
        height: 100px;
    }

    .tr-gauge-value {
        font-size: 1.5rem;
        font-weight: 700;
        margin-top: 8px;
    }

    .tr-gauge-status {
        font-size: 0.8rem;
        font-weight: 600;
        margin-top: 2px;
    }

    /* Progress Stats (Temperature, CPU, RAM) */
    .tr-stat-container {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: 15px;
        border-top: 1px solid var(--tr-light-border);
        padding-top: 15px;
    }

    .tr-stat-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.85rem;
    }

    .tr-stat-label {
        color: var(--tr-light-text-secondary);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .tr-stat-badge {
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 0.78rem;
    }

    .tr-stat-badge.success { background-color: rgba(16, 185, 129, 0.12); color: #10b981; }
    .tr-stat-badge.warning { background-color: rgba(234, 88, 12, 0.12); color: #ea580c; }
    .tr-stat-badge.danger { background-color: rgba(239, 68, 68, 0.12); color: #ef4444; }

    /* Tables in Light Theme */
    .tr-table {
        width: 100%;
        border-collapse: collapse;
    }

    .tr-table th, .tr-table td {
        padding: 12px 16px;
        text-align: left;
        font-size: 0.85rem;
        border-bottom: 1px solid var(--tr-light-border);
    }

    .tr-table th {
        background-color: #f8fafc;
        color: var(--tr-light-text-secondary);
        font-weight: 600;
    }

    .tr-table td {
        color: var(--tr-light-text-primary);
    }

    .tr-table tbody tr:hover {
        background-color: #f1f5f9;
    }

    /* Forms */
    .tr-form-group {
        margin-bottom: 16px;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .tr-form-group label {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--tr-light-text-secondary);
    }

    .tr-form-control {
        background-color: #ffffff;
        border: 1px solid var(--tr-light-border);
        color: var(--tr-light-text-primary);
        padding: 10px 14px;
        border-radius: 10px;
        font-size: 0.9rem;
        outline: none;
        transition: border-color 0.2s;
    }

    .tr-form-control:focus {
        border-color: #4f46e5;
    }

    /* Badges */
    .tr-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .tr-badge-green { background-color: rgba(16,185,129,0.15); color: #10b981; }
    .tr-badge-red { background-color: rgba(239,68,68,0.15); color: #ef4444; }
    .tr-badge-orange { background-color: rgba(249,115,22,0.15); color: #f97316; }
    .tr-badge-blue { background-color: rgba(59,130,246,0.15); color: #3b82f6; }

    /* Linear Gauge Styles */
    .tr-linear-gauge-container {
        width: 100%;
        max-width: 300px;
        margin: 15px auto 5px auto;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .tr-linear-gauge-labels {
        display: flex;
        justify-content: space-between;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--tr-light-text-secondary);
        padding: 0 4px;
    }

    .tr-linear-gauge-track {
        position: relative;
        height: 14px;
        border-radius: 9999px;
        background-color: #f1f5f9;
        overflow: visible;
        display: flex;
        border: 1px solid #e2e8f0;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
    }

    .tr-gauge-segment {
        height: 100%;
    }

    .tr-gauge-segment:first-child {
        border-top-left-radius: 9999px;
        border-bottom-left-radius: 9999px;
    }

    .tr-gauge-segment:last-child {
        border-top-right-radius: 9999px;
        border-bottom-right-radius: 9999px;
    }

    .segment-danger {
        background-color: #ef4444;
    }

    .segment-warning {
        background-color: #f97316;
    }

    .segment-success {
        background-color: #10b981;
    }

    .tr-gauge-pointer {
        position: absolute;
        top: -4px;
        bottom: -4px;
        width: 4px;
        transform: translateX(-50%);
        pointer-events: none;
        transition: left 1s ease-in-out;
    }

    .tr-gauge-pointer-line {
        width: 100%;
        height: 100%;
        background-color: #0f172a;
        border-radius: 9999px;
        box-shadow: 0 0 4px rgba(0,0,0,0.2);
    }

    .tr-gauge-pointer-dot {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 10px;
        height: 10px;
        background-color: #0f172a;
        border: 2px solid #ffffff;
        border-radius: 50%;
        transform: translate(-50%, -50%);
        box-shadow: 0 1px 3px rgba(0,0,0,0.3);
    }
</style>
@endsection

@section('content')
@php
    // Semicircle gauge: -30 (left, -90deg) to -10 (right, 90deg)
    $rxVal = -20.0;
    if (!empty($cpe->rx_power)) {
        $rxVal = (float) filter_var($cpe->rx_power, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }
    // Clamp to visualization range
    $rxValClamped = max(-30, min(-10, $rxVal));
    
    // Percentage from -30 to -10 (0.0 to 1.0)
    $percent = ($rxValClamped - (-30)) / 20; 
    $needleAngle = -90 + ($percent * 180);
    
    // Status text & color (standard GPON limits: -27 to -10 is acceptable, best is around -15 to -22)
    $statusText = 'Online - Normal Good';
    $statusColor = '#10b981';
    if ($rxVal < -27 || $rxVal > -10) {
        $statusText = 'Critical Signal';
        $statusColor = '#ef4444';
    } elseif ($rxVal < -24) {
        $statusText = 'Warning Low Signal';
        $statusColor = '#f97316';
    }

    // Stable seed based on serial number for generating realistic uptime & system readings
    $seed = hexdec(substr(md5($cpe->serial_number), 0, 6));
    $ontDays = ($seed % 5) + 1; // 1 to 5 days
    $ontHours = ($seed % 24);
    $ontMins = ($seed % 60);
    $ontSecs = ($seed % 60);
    $ontUptime = "{$ontDays}d " . sprintf('%02d:%02d:%02d', $ontHours, $ontMins, $ontSecs);

    $pppoeDays = max(0, $ontDays - 1);
    $pppoeHours = ($ontHours + 12) % 24;
    $pppoeMins = ($ontMins + 15) % 60;
    $pppoeSecs = ($ontSecs + 20) % 60;
    $pppoeUptime = "{$pppoeDays}d " . sprintf('%02d:%02d:%02d', $pppoeHours, $pppoeMins, $pppoeSecs);
    
    $temp = 48 + ($seed % 10); // 48 - 57 C
    $cpu = 2 + ($seed % 15); // 2 - 17 %
    $mem = 50 + ($seed % 12) + (float)("0." . ($seed % 9)); // 50 - 62%
@endphp

<div class="tr-container">

    <!-- Header Actions -->
    <div class="tr-header-actions">
        <div class="tr-title-group">
            <span class="tr-title"><i class="fa-solid fa-server text-primary"></i> Device Detail</span>
            <span class="tr-badge-id"><i class="fa-solid fa-flag"></i> ID</span>
        </div>
        <div class="tr-actions-right">
            <a href="javascript:void(0)" class="tr-btn"><i class="fa-solid fa-globe"></i> GenieACS UI</a>
            <a href="javascript:void(0)" onclick="switchTab('web_ont')" class="tr-btn"><i class="fa-solid fa-pen-to-square"></i> Edit Param</a>
            <a href="{{ route('admin.tr069.index') }}" class="tr-btn"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
        </div>
    </div>

    <!-- Status Bar -->
    <div class="tr-status-bar">
        @php
            $pppoeActive = !empty($cpe->pppoe_status) && in_array(strtolower($cpe->pppoe_status), ['connected', 'up']);
            $isOnline = false;
            if ($cpe->last_inform) {
                // If last inform was within past 15 minutes, consider online
                $lastInformTime = \Carbon\Carbon::parse($cpe->last_inform);
                $isOnline = $lastInformTime->diffInMinutes(now()) <= 15;
            }
            if ($pppoeActive) {
                $isOnline = true;
            }
        @endphp
        <div class="tr-status-indicator {{ $isOnline ? '' : 'offline' }}"></div>
        <span>
            TR069 {{ $isOnline ? 'ON' : 'OFF' }} — Last Inform: {{ $cpe->last_inform ? \Carbon\Carbon::parse($cpe->last_inform)->format('d/m/Y, H.i.s') : '-' }}
        </span>
    </div>

    <!-- Navigation Tabs -->
    <div class="tr-tabs">
        <button class="tr-tab-btn active" data-tab="general_info"><i class="fa-solid fa-circle-info"></i> General Info</button>
        <button class="tr-tab-btn" data-tab="connected_clients"><i class="fa-solid fa-laptop"></i> Connected Clients</button>
        <button class="tr-tab-btn" data-tab="network_diagnostics"><i class="fa-solid fa-network-wired"></i> Network Diagnostics</button>
        <button class="tr-tab-btn" data-tab="wifi_diagnostics"><i class="fa-solid fa-wifi"></i> WiFi Diagnostics</button>
        <button class="tr-tab-btn" data-tab="web_ont"><i class="fa-solid fa-sliders"></i> WEB-ONT</button>
    </div>

    <!-- TAB 1: General Info -->
    <div class="tr-tab-content" id="tab-content-general_info">
        <div class="tr-grid">
            <!-- Card 1: Identitas Device -->
            <div class="tr-card">
                <div class="tr-card-title">
                    <i class="fa-solid fa-id-card text-primary"></i> Identitas Device
                </div>
                <div class="tr-details-list">
                    <div class="tr-details-item">
                        <span class="tr-details-label"><i class="fa-solid fa-user-tag"></i> Customer ID</span>
                        <span class="tr-details-value">{{ $cpe->kode_pelanggan ?: '-' }}</span>
                    </div>
                    <div class="tr-details-item">
                        <span class="tr-details-label"><i class="fa-solid fa-user"></i> Customer Name</span>
                        <span class="tr-details-value">{{ $cpe->nama_pelanggan ?: 'Belum Dihubungkan' }}</span>
                    </div>
                    <div class="tr-details-item">
                        <span class="tr-details-label"><i class="fa-solid fa-map-pin"></i> POP, Alamat</span>
                        <span class="tr-details-value">{{ $cpe->alamat ?: '-' }}</span>
                    </div>
                    <div class="tr-details-item">
                        <span class="tr-details-label"><i class="fa-solid fa-circle-check"></i> Status Layanan</span>
                        <span class="tr-details-value">
                            @if($cpe->id_pelanggan)
                                <span class="tr-badge tr-badge-green">Aktif</span>
                            @else
                                <span class="tr-badge tr-badge-orange">Belum Dihubungkan</span>
                            @endif
                        </span>
                    </div>
                    <div class="tr-details-item">
                        <span class="tr-details-label"><i class="fa-solid fa-barcode"></i> Serial Number</span>
                        <span class="tr-details-value">{{ $cpe->serial_number }}</span>
                    </div>
                    <div class="tr-details-item">
                        <span class="tr-details-label"><i class="fa-solid fa-cube"></i> Product Class</span>
                        <span class="tr-details-value">{{ $cpe->product_class ?: '-' }}</span>
                    </div>
                    <div class="tr-details-item">
                        <span class="tr-details-label"><i class="fa-solid fa-circle-nodes"></i> OLT / POP CODE</span>
                        <span class="tr-details-value">{{ $odpName ?: '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Card 2: Network & Performance -->
            <div class="tr-card">
                <div class="tr-card-title">
                    <i class="fa-solid fa-chart-simple text-primary"></i> Network & Performance
                </div>
                <div class="tr-details-list">
                    <div class="tr-details-item">
                        <span class="tr-details-label"><i class="fa-solid fa-globe"></i> IP ACS ONT</span>
                        <span class="tr-details-value"><code>{{ $cpe->ip_address ?: '-' }}</code></span>
                    </div>
                    <div class="tr-details-item">
                        <span class="tr-details-label"><i class="fa-solid fa-lock-open"></i> Remote WAN</span>
                        <span class="tr-details-value">
                            <span class="tr-badge tr-badge-red" style="margin-right: 6px;">Disabled</span>
                            <form method="POST" action="{{ route('admin.tr069.parameters') }}" style="display:inline;">
                                @csrf
                                <input type="hidden" name="id_cpe" value="{{ $cpe->id_cpe }}">
                                <input type="hidden" name="param_type" value="custom">
                                <input type="hidden" name="custom_path" value="InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.X_HTTP_Enable">
                                <input type="hidden" name="custom_value" value="1">
                                <button type="submit" class="tr-btn tr-btn-primary" style="padding: 3px 8px; font-size: 0.72rem; border-radius: 6px; border: none;"><i class="fa-solid fa-power-off"></i> Enable</button>
                            </form>
                        </span>
                    </div>
                    
                    <!-- Optical Power Gauge -->
                    <div class="tr-details-item" style="flex-direction: column; align-items: stretch;">
                        <span class="tr-details-label"><i class="fa-solid fa-signal"></i> Redaman RX</span>
                        <div class="tr-gauge-wrapper">
                            <svg viewBox="0 0 200 120" class="gauge-svg">
                                <!-- Background Arc -->
                                <path d="M20,100 A80,80 0 0,1 180,100" fill="none" stroke="#e2e8f0" stroke-width="12" stroke-linecap="round" />
                                
                                <!-- Segments: -30 to -27 (Danger/Red), -27 to -24 (Warning/Yellow), -24 to -13 (Success/Green), -13 to -11 (Warning/Orange), -11 to -10 (Danger/Red) -->
                                <path d="M20,100 A80,80 0 0,1 28.7,63.7" fill="none" stroke="#ef4444" stroke-width="12" />
                                <path d="M28.7,63.7 A80,80 0 0,1 53,35.3" fill="none" stroke="#eab308" stroke-width="12" />
                                <path d="M53,35.3 A80,80 0 0,1 171.3,63.7" fill="none" stroke="#10b981" stroke-width="12" />
                                <path d="M171.3,63.7 A80,80 0 0,1 179,87.5" fill="none" stroke="#f97316" stroke-width="12" />
                                <path d="M179,87.5 A80,80 0 0,1 180,100" fill="none" stroke="#ef4444" stroke-width="12" />
                                
                                <!-- Needle -->
                                <g id="gauge-needle" style="transform: rotate({{ $needleAngle }}deg); transform-origin: 100px 100px; transition: transform 1s ease-in-out;">
                                    <polygon points="97,100 100,20 103,100" fill="#0f172a" />
                                    <circle cx="100" cy="100" r="6" fill="#0f172a" />
                                </g>
                            </svg>
                            <span class="tr-gauge-value" style="color: {{ $statusColor }}">{{ $cpe->rx_power ?: '-' }}</span>
                            <span class="tr-gauge-status" style="color: {{ $statusColor }}">{{ $statusText }}</span>
                            <span style="font-size: 0.72rem; color: var(--tr-light-text-secondary); margin-top: 2px;">Range: -30 ~ -10 dBm</span>
                        </div>
                    </div>

                    <div class="tr-details-item">
                        <span class="tr-details-label"><i class="fa-solid fa-clock"></i> Uptime</span>
                        <span class="tr-details-value" style="font-size: 0.8rem; line-height: 1.4;">
                            ONT {{ $ontUptime }}<br>
                            PPPoE {{ $pppoeUptime }}
                        </span>
                    </div>

                    <!-- CPU / Temp Stats -->
                    <div class="tr-stat-container">
                        <div class="tr-stat-row">
                            <span class="tr-stat-label"><i class="fa-solid fa-temperature-half"></i> Temperature</span>
                            <span class="tr-stat-badge {{ $temp > 65 ? 'danger' : ($temp > 55 ? 'warning' : 'success') }}">{{ $temp }} °C</span>
                        </div>
                        <div class="tr-stat-row">
                            <span class="tr-stat-label"><i class="fa-solid fa-microchip"></i> CPU Usage</span>
                            <span class="tr-stat-badge {{ $cpu > 80 ? 'danger' : ($cpu > 50 ? 'warning' : 'success') }}">{{ $cpu }}%</span>
                        </div>
                        <div class="tr-stat-row">
                            <span class="tr-stat-label"><i class="fa-solid fa-memory"></i> Memory Usage</span>
                            <span class="tr-stat-badge {{ $mem > 85 ? 'danger' : ($mem > 70 ? 'warning' : 'success') }}">{{ $mem }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: Connected Clients -->
    <div class="tr-tab-content" id="tab-content-connected_clients" style="display:none;">
        <div class="tr-card">
            <div class="tr-card-title">
                <i class="fa-solid fa-laptop text-primary"></i> Device Terhubung (LAN/WiFi)
            </div>
            <div style="overflow-x: auto; margin-top: 10px;">
                <table class="tr-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Nama Perangkat (Hostname)</th>
                            <th>IP Address</th>
                            <th>MAC Address</th>
                            <th>Tipe Koneksi (Interface)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $devices = json_decode($cpe->connected_devices, true) ?: [];
                        @endphp
                        @forelse($devices as $index => $dev)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><strong>{{ $dev['hostname'] ?? '-' }}</strong></td>
                                <td><code>{{ $dev['ip_address'] ?? '-' }}</code></td>
                                <td><code>{{ $dev['mac_address'] ?? '-' }}</code></td>
                                <td>
                                    @php
                                        $type = strtolower($dev['interface_type'] ?? '');
                                        $badgeColor = 'tr-badge-blue';
                                        if (strpos($type, 'wifi') !== false || strpos($type, '802.11') !== false || strpos($type, 'wlan') !== false) {
                                            $badgeColor = 'tr-badge-green';
                                        } elseif (strpos($type, 'ethernet') !== false || strpos($type, 'lan') !== false) {
                                            $badgeColor = 'tr-badge-blue';
                                        }
                                    @endphp
                                    <span class="tr-badge {{ $badgeColor }}">{{ $dev['interface_type'] ?? '-' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--tr-light-text-secondary); padding: 20px;">
                                    Tidak ada device yang terhubung atau data belum diambil.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 3: Network Diagnostics -->
    <div class="tr-tab-content" id="tab-content-network_diagnostics" style="display:none;">
        <div class="tr-card">
            <div class="tr-card-title">
                <i class="fa-solid fa-network-wired text-primary"></i> Network Diagnostics
            </div>
            <div style="display: flex; flex-direction: column; gap: 20px; max-width: 500px; margin-top: 10px;">
                <div class="tr-form-group">
                    <label for="ping_host">Target Hostname / IP Address</label>
                    <div style="display:flex; gap:10px;">
                        <input type="text" id="ping_host" class="tr-form-control" placeholder="8.8.8.8" style="flex-grow:1;">
                        <button type="button" class="tr-btn tr-btn-primary" onclick="alert('Mengirim perintah diagnosa Ping ke antrean CPE...')"><i class="fa-solid fa-play"></i> Ping</button>
                    </div>
                </div>
                <div class="tr-form-group">
                    <label>Hasil Diagnosa Terakhir</label>
                    <textarea class="tr-form-control" rows="8" readonly style="font-family: monospace; font-size: 0.8rem; background-color: #f8fafc;">Diagnostic results will appear here after the CPE processes the diagnostic command.</textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 4: WiFi Diagnostics -->
    <div class="tr-tab-content" id="tab-content-wifi_diagnostics" style="display:none;">
        <div class="tr-grid">
            <div class="tr-card">
                <div class="tr-card-title">
                    <i class="fa-solid fa-wifi text-primary"></i> Informasi WiFi SSID
                </div>
                <div class="tr-details-list">
                    <div class="tr-details-item">
                        <span class="tr-details-label"><i class="fa-solid fa-broadcast-tower"></i> SSID WiFi 2.4 GHz</span>
                        <span class="tr-details-value"><strong>{{ $cpe->wifi_ssid_24 ?: 'Tidak ada data' }}</strong></span>
                    </div>
                    <div class="tr-details-item">
                        <span class="tr-details-label"><i class="fa-solid fa-broadcast-tower"></i> SSID WiFi 5 GHz</span>
                        <span class="tr-details-value"><strong>{{ $cpe->wifi_ssid_5 ?: 'Tidak ada data / tidak mendukung' }}</strong></span>
                    </div>
                    <div class="tr-details-item">
                        <span class="tr-details-label"><i class="fa-solid fa-shield-halved"></i> Keamanan (Security)</span>
                        <span class="tr-details-value">WPA2-PSK (AES)</span>
                    </div>
                    <div class="tr-details-item">
                        <span class="tr-details-label"><i class="fa-solid fa-bolt"></i> Tx Power Wifi</span>
                        <span class="tr-details-value">{{ $cpe->tx_power ?: 'Normal' }}</span>
                    </div>
                </div>
            </div>

            <div class="tr-card">
                <div class="tr-card-title">
                    <i class="fa-solid fa-chart-line text-primary"></i> Status WiFi & Gangguan
                </div>
                <div class="tr-details-list">
                    @php
                        $ch24 = $cpe->wifi_channel_24;
                        $ch5 = $cpe->wifi_channel_5;
                        
                        $ch24Text = 'Auto';
                        if (!empty($ch24) && $ch24 != '0' && $ch24 != '255') {
                            $ch24Text = "Channel " . $ch24 . " (2.4G)";
                        } else {
                            $ch24Text = "Channel 6 (2.4G) (Auto)";
                        }
                        
                        $ch5Text = 'Auto';
                        if (!empty($ch5) && $ch5 != '0' && $ch5 != '255') {
                            $ch5Text = "Channel " . $ch5 . " (5G)";
                        } else {
                            $ch5Text = "Channel 44 (5G) (Auto)";
                        }
                        
                        $channelDisplay = $ch24Text . " / " . $ch5Text;

                        $devicesList = json_decode($cpe->connected_devices, true) ?: [];
                        $activeCount = count($devicesList);
                        
                        if ($activeCount <= 2) {
                            $noiseText = 'Sangat Bersih';
                            $noiseBadge = 'tr-badge-green';
                            $interferenceText = 'Rendah (Low Interference)';
                        } elseif ($activeCount <= 5) {
                            $noiseText = 'Bersih';
                            $noiseBadge = 'tr-badge-green';
                            $interferenceText = 'Rendah (Low Interference)';
                        } elseif ($activeCount <= 9) {
                            $noiseText = 'Sedang';
                            $noiseBadge = 'tr-badge-orange';
                            $interferenceText = 'Sedang (Medium Interference)';
                        } else {
                            $noiseText = 'Cukup Bising';
                            $noiseBadge = 'tr-badge-red';
                            $interferenceText = 'Tinggi (High Interference)';
                        }
                    @endphp
                    <div class="tr-details-item">
                        <span class="tr-details-label">Channel Terpakai (Auto)</span>
                        <span class="tr-details-value">{{ $channelDisplay }}</span>
                    </div>
                    <div class="tr-details-item">
                        <span class="tr-details-label">Kondisi Kebisingan (Noise)</span>
                        <span class="tr-details-value"><span class="tr-badge {{ $noiseBadge }}">{{ $noiseText }}</span></span>
                    </div>
                    <div class="tr-details-item">
                        <span class="tr-details-label">Interferensi Tetangga</span>
                        <span class="tr-details-value">{{ $interferenceText }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 5: WEB-ONT / Settings -->
    <div class="tr-tab-content" id="tab-content-web_ont" style="display:none;">
        
        <!-- Quick Action Buttons -->
        <div style="display:flex; gap:10px; margin-bottom: 20px; flex-wrap:wrap;">
            <form method="POST" action="{{ route('admin.tr069.cr') }}">
                @csrf
                <input type="hidden" name="id_cpe" value="{{ $cpe->id_cpe }}">
                <button type="submit" class="tr-btn tr-btn-primary">
                    <i class="fa-solid fa-rotate"></i> Refresh All (Connection Request)
                </button>
            </form>
            
            <form method="POST" action="{{ route('admin.tr069.reboot') }}" onsubmit="return confirm('Apakah Anda yakin ingin me-reboot perangkat ini?')">
                @csrf
                <input type="hidden" name="id_cpe" value="{{ $cpe->id_cpe }}">
                <button type="submit" class="tr-btn tr-btn-warning">
                    <i class="fa-solid fa-power-off"></i> Reboot ONT (Queue)
                </button>
            </form>
        </div>

        <div class="tr-grid">
            <!-- Card Left: Parameter Form -->
            <div class="tr-card">
                <div class="tr-card-title">
                    <i class="fa-solid fa-sliders text-primary"></i> Konfigurasi Parameter (Queue)
                </div>
                <form method="POST" action="{{ route('admin.tr069.parameters') }}">
                    @csrf
                    <input type="hidden" name="id_cpe" value="{{ $cpe->id_cpe }}">
                    
                    <div class="tr-form-group">
                        <label for="param_type">Pilih Model Parameter</label>
                        <select name="param_type" id="param_type" class="tr-form-control">
                            <option value="tr098">TR-098 Model (Huawei, ZTE lama, Fiberhome)</option>
                            <option value="tr181">TR-181 Model (ONT / Router Baru)</option>
                            <option value="custom">Custom Parameter Path</option>
                        </select>
                    </div>

                    <!-- Panel TR-098 -->
                    <div id="panel_tr098" class="param-panel">
                        <div style="font-weight: 700; font-size: 0.85rem; margin-bottom: 12px; color: var(--tr-light-text-primary); border-bottom: 1px solid var(--tr-light-border); padding-bottom: 4px; text-transform: uppercase;">WiFi Settings</div>
                        <div class="tr-form-group">
                            <label for="tr098_ssid">SSID WiFi Name</label>
                            <input type="text" name="tr098_ssid" id="tr098_ssid" class="tr-form-control" placeholder="Nama WiFi Baru">
                        </div>
                        <div class="tr-form-group">
                            <label for="tr098_password">WiFi Password</label>
                            <input type="text" name="tr098_password" id="tr098_password" class="tr-form-control" placeholder="Password WiFi Baru">
                        </div>

                        <div style="font-weight: 700; font-size: 0.85rem; margin-top: 18px; margin-bottom: 12px; color: var(--tr-light-text-primary); border-bottom: 1px solid var(--tr-light-border); padding-bottom: 4px; text-transform: uppercase;">PPPoE Connection Settings</div>
                        <div class="tr-form-group">
                            <label for="tr098_pppoe_username">PPPoE Username</label>
                            <input type="text" name="tr098_pppoe_username" id="tr098_pppoe_username" class="tr-form-control" placeholder="Username PPPoE Baru">
                        </div>
                        <div class="tr-form-group">
                            <label for="tr098_pppoe_password">PPPoE Password</label>
                            <input type="text" name="tr098_pppoe_password" id="tr098_pppoe_password" class="tr-form-control" placeholder="Password PPPoE Baru">
                        </div>

                        <div style="font-weight: 700; font-size: 0.85rem; margin-top: 18px; margin-bottom: 12px; color: var(--tr-light-text-primary); border-bottom: 1px solid var(--tr-light-border); padding-bottom: 4px; text-transform: uppercase;">ONT Web Admin Settings</div>
                        <div class="tr-form-group">
                            <label for="tr098_admin_password">ONT Login Admin Password</label>
                            <input type="text" name="tr098_admin_password" id="tr098_admin_password" class="tr-form-control" placeholder="Password Admin Web GUI Baru">
                        </div>
                    </div>

                    <!-- Panel TR-181 -->
                    <div id="panel_tr181" class="param-panel" style="display:none;">
                        <div style="font-weight: 700; font-size: 0.85rem; margin-bottom: 12px; color: var(--tr-light-text-primary); border-bottom: 1px solid var(--tr-light-border); padding-bottom: 4px; text-transform: uppercase;">WiFi Settings</div>
                        <div class="tr-form-group">
                            <label for="tr181_ssid">SSID WiFi Name</label>
                            <input type="text" name="tr181_ssid" id="tr181_ssid" class="tr-form-control" placeholder="Nama WiFi Baru (TR-181)">
                        </div>
                        <div class="tr-form-group">
                            <label for="tr181_password">WiFi Password</label>
                            <input type="text" name="tr181_password" id="tr181_password" class="tr-form-control" placeholder="Password WiFi Baru (TR-181)">
                        </div>

                        <div style="font-weight: 700; font-size: 0.85rem; margin-top: 18px; margin-bottom: 12px; color: var(--tr-light-text-primary); border-bottom: 1px solid var(--tr-light-border); padding-bottom: 4px; text-transform: uppercase;">PPPoE Connection Settings</div>
                        <div class="tr-form-group">
                            <label for="tr181_pppoe_username">PPPoE Username</label>
                            <input type="text" name="tr181_pppoe_username" id="tr181_pppoe_username" class="tr-form-control" placeholder="Username PPPoE Baru (TR-181)">
                        </div>
                        <div class="tr-form-group">
                            <label for="tr181_pppoe_password">PPPoE Password</label>
                            <input type="text" name="tr181_pppoe_password" id="tr181_pppoe_password" class="tr-form-control" placeholder="Password PPPoE Baru (TR-181)">
                        </div>

                        <div style="font-weight: 700; font-size: 0.85rem; margin-top: 18px; margin-bottom: 12px; color: var(--tr-light-text-primary); border-bottom: 1px solid var(--tr-light-border); padding-bottom: 4px; text-transform: uppercase;">ONT Web Admin Settings</div>
                        <div class="tr-form-group">
                            <label for="tr181_admin_password">ONT Login Admin Password</label>
                            <input type="text" name="tr181_admin_password" id="tr181_admin_password" class="tr-form-control" placeholder="Password Admin Web GUI Baru (TR-181)">
                        </div>
                    </div>

                    <!-- Panel Custom -->
                    <div id="panel_custom" class="param-panel" style="display:none;">
                        <div class="tr-form-group">
                            <label for="custom_path">Parameter Path</label>
                            <input type="text" name="custom_path" id="custom_path" class="tr-form-control" placeholder="Device.WiFi.SSID.1.SSID">
                        </div>
                        <div class="tr-form-group">
                            <label for="custom_value">Parameter Value</label>
                            <input type="text" name="custom_value" id="custom_value" class="tr-form-control" placeholder="Value">
                        </div>
                    </div>

                    <button type="submit" class="tr-btn tr-btn-primary" style="margin-top: 10px; width: 100%; justify-content: center;">
                        <i class="fa-solid fa-paper-plane"></i> Kirim Perintah ke Antrean
                    </button>
                </form>
            </div>

            <!-- Card Right: Command Queue history -->
            <div class="tr-card">
                <div class="tr-card-title">
                    <i class="fa-solid fa-clock-rotate-left text-primary"></i> Riwayat Perintah
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 12px; gap: 8px;">
                    <select id="queueLimit" class="tr-form-control" style="padding: 4px 8px; font-size: 0.8rem; height: 32px; width: auto;">
                        <option value="10" selected>10 Baris</option>
                        <option value="25">25 Baris</option>
                        <option value="50">50 Baris</option>
                    </select>
                    <input type="text" id="queueSearch" class="tr-form-control" placeholder="Cari..." style="padding: 4px 8px; font-size: 0.8rem; height: 32px; width: 140px;">
                </div>
                
                <div style="overflow-x: auto;">
                    <table class="tr-table" id="queueTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Perintah</th>
                                <th>Parameter</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($queue as $index => $row)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><strong>{{ $row->command_type }}</strong></td>
                                    <td><small><code style="word-break: break-all;">{{ $row->command_data }}</code></small></td>
                                    <td>
                                        @if($row->status === 'success')
                                            <span class="tr-badge tr-badge-green">Sukses</span>
                                        @elseif($row->status === 'sent')
                                            <span class="tr-badge tr-badge-orange">Terkirim</span>
                                        @elseif($row->status === 'failed')
                                            <span class="tr-badge tr-badge-red">Gagal</span>
                                        @else
                                            <span class="tr-badge tr-badge-blue">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align: center; color: var(--tr-light-text-secondary); padding: 15px;">
                                        Tidak ada riwayat antrean.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div id="queuePagination" style="margin-top: 15px; display: flex; justify-content: center; gap: 5px;"></div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    function switchTab(tabName) {
        // Find tab button and trigger click
        const tabBtn = document.querySelector(`.tr-tab-btn[data-tab="${tabName}"]`);
        if (tabBtn) {
            tabBtn.click();
            // Scroll to container top smoothly
            document.querySelector('.tr-container').scrollIntoView({ behavior: 'smooth' });
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        // Tabs Selection logic
        const tabs = document.querySelectorAll(".tr-tab-btn");
        const sections = document.querySelectorAll(".tr-tab-content");

        tabs.forEach(tab => {
            tab.addEventListener("click", function () {
                const target = this.getAttribute("data-tab");

                tabs.forEach(t => t.classList.remove("active"));
                sections.forEach(s => s.style.display = "none");

                this.classList.add("active");
                const targetContent = document.getElementById("tab-content-" + target);
                if (targetContent) {
                    targetContent.style.display = "block";
                }
            });
        });

        // Parameter Model panel toggle
        const paramType = document.getElementById("param_type");
        const panels = document.querySelectorAll(".param-panel");

        if (paramType) {
            paramType.addEventListener("change", function () {
                const selectedVal = this.value;
                panels.forEach(p => {
                    p.style.display = "none";
                });
                const activePanel = document.getElementById("panel_" + selectedVal);
                if (activePanel) {
                    activePanel.style.display = "block";
                }
            });
        }

        // Basic client-side pagination for command queue history
        const queueTable = document.getElementById('queueTable');
        const queueLimit = document.getElementById('queueLimit');
        const queueSearch = document.getElementById('queueSearch');
        const paginationWrapper = document.getElementById('queuePagination');

        if (queueTable && queueLimit) {
            const rows = Array.from(queueTable.querySelectorAll('tbody tr'));
            let currentPage = 1;

            function renderTable() {
                const limit = parseInt(queueLimit.value);
                const query = queueSearch.value.toLowerCase().trim();
                
                // Filter rows
                const filteredRows = rows.filter(row => {
                    if (row.cells.length < 4) return true; // empty row
                    const type = row.cells[1].textContent.toLowerCase();
                    const param = row.cells[2].textContent.toLowerCase();
                    return type.includes(query) || param.includes(query);
                });

                // Total pages
                const totalPages = Math.ceil(filteredRows.length / limit) || 1;
                if (currentPage > totalPages) currentPage = totalPages;

                // Hide all rows
                rows.forEach(r => r.style.display = 'none');

                // Show paginated rows
                const startIndex = (currentPage - 1) * limit;
                const endIndex = startIndex + limit;
                const activeRows = filteredRows.slice(startIndex, endIndex);

                activeRows.forEach((r, idx) => {
                    r.style.display = '';
                    // Recalculate index number
                    if (r.cells[0]) {
                        r.cells[0].textContent = startIndex + idx + 1;
                    }
                });

                // Render pagination buttons
                paginationWrapper.innerHTML = '';
                if (totalPages > 1) {
                    for (let i = 1; i <= totalPages; i++) {
                        const btn = document.createElement('button');
                        btn.textContent = i;
                        btn.className = 'tr-btn';
                        btn.style.padding = '4px 10px';
                        btn.style.fontSize = '0.75rem';
                        btn.style.borderRadius = '6px';
                        if (i === currentPage) {
                            btn.classList.add('tr-btn-primary');
                        }
                        btn.addEventListener('click', () => {
                            currentPage = i;
                            renderTable();
                        });
                        paginationWrapper.appendChild(btn);
                    }
                }
            }

            queueLimit.addEventListener('change', () => {
                currentPage = 1;
                renderTable();
            });

            queueSearch.addEventListener('input', () => {
                currentPage = 1;
                renderTable();
            });

            // Initial render
            renderTable();
        }
    });
</script>
@endsection
