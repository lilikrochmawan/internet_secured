<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Jaringan</title>
    <style>
        :root {
            color-scheme: dark;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: radial-gradient(circle at top, rgba(79, 70, 229, .22), transparent 25%),
                        radial-gradient(circle at right, rgba(59, 130, 246, .14), transparent 15%),
                        linear-gradient(180deg, #0b1124 0%, #090d1d 100%);
            color: #e5e7eb;
        }
        .page {
            width: min(1100px, 100%);
            margin: 0 auto;
            padding: 24px;
        }
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 24px;
        }
        .topbar h1 {
            margin: 0;
            font-size: 1.35rem;
        }
        .topbar a {
            color: #a5b4fc;
            text-decoration: none;
            font-weight: 600;
        }
        .card {
            background: rgba(15, 23, 42, .72);
            border: 1px solid rgba(148, 163, 184, .12);
            border-radius: 28px;
            padding: 28px;
            backdrop-filter: blur(16px);
            display: grid;
            gap: 20px;
            margin-bottom: 28px;
        }
        .info-row {
            display: grid;
            gap: 8px;
            padding-bottom: 18px;
            border-bottom: 1px solid rgba(148, 163, 184, .12);
        }
        .info-row:last-child {
            padding-bottom: 0;
            border-bottom: 0;
        }
        .info-label {
            font-size: .82rem;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #a5b4fc;
            font-weight: 700;
        }
        .info-value {
            font-size: 1.1rem;
            color: #f8fafc;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 18px;
            border-radius: 16px;
            font-size: 1.15rem;
            font-weight: 800;
            width: fit-content;
        }
        .status-aktif {
            background: rgba(34, 197, 94, .15);
            color: #4ade80;
            border: 1px solid rgba(34, 197, 94, .4);
        }
        .status-terblokir {
            background: rgba(239, 68, 68, .15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, .4);
        }
        .status-unknown {
            background: rgba(148, 163, 184, .12);
            color: #cbd5e1;
            border: 1px solid rgba(148, 163, 184, .25);
        }
        .status-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: currentColor;
        }
        .hint {
            color: #94a3b8;
            font-size: .9rem;
            line-height: 1.5;
        }
        .upgrade-section h3 {
            margin: 0 0 8px;
            font-size: 1rem;
            color: #e5e7eb;
        }
        .upgrade-section p {
            margin: 0 0 18px;
            color: #94a3b8;
            font-size: .92rem;
        }
        .paket-card-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }
        .paket-card {
            overflow: hidden;
            border-radius: 22px;
            border: 1px solid rgba(124, 58, 237, .35);
            background: #fff;
            box-shadow: 0 18px 40px rgba(15, 23, 42, .18);
        }
        .paket-card-header {
            display: grid;
            grid-template-columns: 1fr auto auto;
            align-items: center;
            gap: 16px;
            padding: 18px 20px;
            background: linear-gradient(135deg, #6d28d9 0%, #7c3aed 55%, #8b5cf6 100%);
            color: #fff;
        }
        .paket-card-title {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
            line-height: 1.3;
        }
        .paket-card-divider {
            width: 1px;
            height: 52px;
            background: rgba(255, 255, 255, .45);
        }
        .paket-card-speed {
            text-align: center;
            min-width: 72px;
        }
        .paket-card-speed strong {
            display: block;
            font-size: 2rem;
            line-height: 1;
            font-weight: 800;
        }
        .paket-card-speed span {
            display: block;
            margin-top: 4px;
            font-size: .95rem;
            font-weight: 600;
            opacity: .95;
        }
        .paket-card-body {
            padding: 20px 22px 18px;
            background: #fff;
        }
        .paket-feature-list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            gap: 14px;
        }
        .paket-feature-list li {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            color: #334155;
            font-size: .95rem;
            line-height: 1.45;
        }
        .paket-feature-icon {
            width: 22px;
            height: 22px;
            flex: 0 0 22px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, #7c3aed, #6d28d9);
            color: #fff;
            font-size: .72rem;
            font-weight: 700;
        }
        .paket-card-footer {
            padding: 16px 22px 20px;
            border-top: 1px solid #e2e8f0;
            background: #fff;
        }
        .paket-price {
            display: flex;
            align-items: flex-end;
            gap: 4px;
            color: #7c3aed;
            font-weight: 800;
        }
        .paket-price-currency {
            font-size: 1rem;
            line-height: 1;
            margin-bottom: 6px;
        }
        .paket-price-main {
            font-size: 2.4rem;
            line-height: 1;
            letter-spacing: -.03em;
        }
        .paket-price-suffix {
            display: flex;
            flex-direction: column;
            font-size: .95rem;
            line-height: 1.15;
            margin-bottom: 2px;
        }
        .paket-card-empty {
            grid-column: 1 / -1;
            padding: 24px;
            border-radius: 22px;
            border: 1px dashed rgba(255,255,255,.2);
            color: #cbd5e1;
            text-align: center;
        }
        @media (max-width: 900px) {
            .paket-card-list {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 600px) {
            .page {
                padding: 16px 12px;
            }
            .topbar {
                flex-direction: column;
                align-items: stretch;
                gap: 16px;
            }
            .topbar a {
                text-align: left;
            }
            .card {
                padding: 20px 16px;
                border-radius: 20px;
                gap: 16px;
            }
            .info-row {
                padding-bottom: 14px;
            }
            .status-badge {
                font-size: 1rem;
                padding: 8px 14px;
            }
            .info-value {
                font-size: 1rem;
            }
            .card form button[type="submit"] {
                width: 100% !important;
                justify-content: center;
            }
            
            /* Wi-Fi Group Boxes Padding */
            .card form > div {
                padding: 14px !important;
            }
            
            /* Upgrade Packet Card Responsive */
            .paket-card-header {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 8px;
            }
            .paket-card-divider {
                display: none;
            }
            .paket-card-speed {
                display: flex;
                align-items: baseline;
                justify-content: center;
                gap: 4px;
                min-width: unset;
            }
            .paket-card-speed strong {
                display: inline;
                font-size: 1.6rem;
            }
            .paket-card-speed span {
                display: inline;
                font-size: 0.9rem;
            }
        }

        /* Gauge / RX Power Styles */
        .client-gauge-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-top: 10px;
        }

        .client-gauge-svg {
            width: 150px;
            height: 95px;
        }

        .client-gauge-value {
            font-size: 1.25rem;
            font-weight: 700;
            margin-top: 6px;
        }

        .client-gauge-status {
            font-size: 0.78rem;
            font-weight: 600;
            margin-top: 2px;
        }

        /* Connected Devices Table Styles */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(15, 23, 42, 0.4);
            margin-top: 10px;
        }

        .responsive-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.9rem;
        }

        .responsive-table th, .responsive-table td {
            padding: 12px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .responsive-table th {
            background: rgba(255, 255, 255, 0.03);
            color: #a5b4fc;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        .responsive-table tr:last-child td {
            border-bottom: none;
        }

        .responsive-table tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        .conn-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 8px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-wireless {
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        .badge-wired {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        @media (max-width: 600px) {
            .responsive-table {
                border: 0;
            }
            
            .responsive-table thead {
                display: none;
            }
            
            .responsive-table tr {
                display: block;
                border: 1px solid rgba(255, 255, 255, 0.08);
                border-radius: 12px;
                margin-bottom: 10px;
                padding: 6px;
                background: rgba(15, 23, 42, 0.2);
            }
            
            .responsive-table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                border-bottom: 1px solid rgba(255, 255, 255, 0.04);
                padding: 8px 10px;
                text-align: right;
            }
            
            .responsive-table td:last-child {
                border-bottom: 0;
            }
            
            .responsive-table td::before {
                content: attr(data-label);
                font-weight: 700;
                color: #a5b4fc;
                font-size: 0.75rem;
                text-transform: uppercase;
                text-align: left;
                margin-right: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="topbar">
            <h1>Status Jaringan</h1>
            <a href="{{ route('dashboard') }}">← Kembali</a>
        </div>

        <div class="card">
            <div class="info-row">
                <span class="info-label">Status Internet</span>
                <div>
                    @if ($pppStatus['status'] === 'aktif')
                        <span class="status-badge status-aktif">
                            <span class="status-dot"></span>
                            Aktif
                        </span>
                    @elseif ($pppStatus['status'] === 'terblokir')
                        <span class="status-badge status-terblokir">
                            <span class="status-dot"></span>
                            Terblokir
                        </span>
                    @else
                        <span class="status-badge status-unknown">
                            <span class="status-dot"></span>
                            {{ $pppStatus['label'] }}
                        </span>
                    @endif
                </div>
                <p class="hint">{{ $pppStatus['message'] }}</p>
            </div>
        </div>

        @if ($cpe)
            @php
                $lastInform = strtotime($cpe->last_inform);
                $timeDiff = time() - $lastInform;
                $pppoeActive = !empty($cpe->pppoe_status) && in_array(strtolower($cpe->pppoe_status), ['connected', 'up']);
                $online = ($timeDiff <= 900) || $pppoeActive;
            @endphp
            <div class="card">
                <div class="info-row" style="border-bottom: 1px solid rgba(148, 163, 184, .12); padding-bottom: 15px;">
                    <span class="info-label" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                        <span>Informasi Perangkat Modem</span>
                        @if($online)
                            <span style="color: #4ade80; font-size: 0.8rem; text-transform: uppercase; font-weight: 800;">🟢 Online</span>
                        @else
                            <span style="color: #f87171; font-size: 0.8rem; text-transform: uppercase; font-weight: 800;">🔴 Offline</span>
                        @endif
                    </span>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; padding-bottom: 15px; border-bottom: 1px solid rgba(148, 163, 184, .12);">
                    <div>
                        <div class="info-label" style="font-size: 0.75rem; color: #94a3b8;">Serial Number</div>
                        <div class="info-value" style="font-size: 0.95rem; font-weight: bold; margin-top: 4px;">{{ $cpe->serial_number }}</div>
                    </div>
                    <div>
                        <div class="info-label" style="font-size: 0.75rem; color: #94a3b8;">Tipe Perangkat</div>
                        <div class="info-value" style="font-size: 0.95rem; margin-top: 4px;">{{ $cpe->product_class }} ({{ $cpe->manufacturer ?: 'ZTE' }})</div>
                    </div>
                    <div>
                        <div class="info-label" style="font-size: 0.75rem; color: #94a3b8;">Redaman (Rx Power)</div>
                        <div class="info-value" style="font-size: 0.95rem; margin-top: 4px;">
                            @if(!empty($cpe->rx_power))
                                @php
                                    $numericPower = (float) filter_var($cpe->rx_power, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                                    
                                    // Clamp to range -30 to -10 for needle percentage calculation
                                    $clampedPower = max(-30, min(-10, $numericPower));
                                    $percent = ($clampedPower - (-30)) / 20;
                                    $needleAngle = -90 + ($percent * 180);

                                    $color = '#4ade80'; // normal green
                                    $statusText = 'Normal Good';
                                    if ($numericPower < -27 || $numericPower > -11) {
                                        $color = '#f87171'; // critical red
                                        $statusText = 'Critical';
                                    } elseif ($numericPower < -24) {
                                        $color = '#fb923c'; // warning orange
                                        $statusText = 'Low Signal';
                                    }
                                @endphp
                                <div class="client-gauge-wrapper">
                                    <svg viewBox="0 0 200 120" class="client-gauge-svg">
                                        <!-- Background Arc -->
                                        <path d="M20,100 A80,80 0 0,1 180,100" fill="none" stroke="rgba(255,255,255,0.12)" stroke-width="12" stroke-linecap="round" />
                                        
                                        <!-- Segments: -30 to -27 (Danger/Red), -27 to -24 (Warning/Yellow), -24 to -13 (Success/Green), -13 to -11 (Warning/Orange), -11 to -10 (Danger/Red) -->
                                        <path d="M20,100 A80,80 0 0,1 28.7,63.7" fill="none" stroke="#ef4444" stroke-width="12" />
                                        <path d="M28.7,63.7 A80,80 0 0,1 53,35.3" fill="none" stroke="#eab308" stroke-width="12" />
                                        <path d="M53,35.3 A80,80 0 0,1 171.3,63.7" fill="none" stroke="#10b981" stroke-width="12" />
                                        <path d="M171.3,63.7 A80,80 0 0,1 179,87.5" fill="none" stroke="#f97316" stroke-width="12" />
                                        <path d="M179,87.5 A80,80 0 0,1 180,100" fill="none" stroke="#ef4444" stroke-width="12" />
                                        
                                        <!-- Needle -->
                                        <g id="client-gauge-needle" style="transform: rotate({{ $needleAngle }}deg); transform-origin: 100px 100px; transition: transform 1s ease-in-out;">
                                            <polygon points="97,100 100,20 103,100" fill="#f8fafc" />
                                            <circle cx="100" cy="100" r="6" fill="#f8fafc" />
                                        </g>
                                    </svg>
                                    <div class="client-gauge-value" style="color: {{ $color }};">{{ $cpe->rx_power }}</div>
                                    <div class="client-gauge-status" style="color: {{ $color }}; font-weight: bold;">{{ $statusText }}</div>
                                    <div style="font-size: 0.7rem; color: #94a3b8; margin-top: 2px;">Range: -30 ~ -10 dBm</div>
                                </div>
                            @else
                                <span style="color: #94a3b8; font-style: italic;">Tidak ada data</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div style="margin-top: 10px;">
                    <div class="info-label" style="font-size: 0.85rem; color: #a5b4fc; margin-bottom: 15px; font-weight: bold;">🔧 Pengaturan Wi-Fi Mandiri</div>
                    
                    @if(session('success'))
                        <div style="background: rgba(34, 197, 94, .12); border: 1px solid rgba(34, 197, 94, .3); color: #4ade80; padding: 12px 16px; border-radius: 12px; font-size: 0.88rem; margin-bottom: 16px;">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div style="background: rgba(239, 68, 68, .12); border: 1px solid rgba(239, 68, 68, .3); color: #f87171; padding: 12px 16px; border-radius: 12px; font-size: 0.88rem; margin-bottom: 16px;">
                            <ul style="margin: 0; padding-left: 20px;">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('network.wifi.update') }}" style="display: grid; gap: 20px;">
                        @csrf
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 18px; border-radius: 18px;">
                            <div style="grid-column: 1 / -1; font-weight: bold; font-size: 0.9rem; color: #e2e8f0; display: flex; align-items: center; gap: 8px;">
                                📡 Wi-Fi 2.4 GHz
                            </div>
                            <div class="form-group">
                                <label for="wifi_ssid_24" style="color: #cbd5e1; font-size: 0.82rem; font-weight: 600; margin-bottom: 6px; display: inline-block;">Nama WiFi (SSID)</label>
                                <input type="text" name="wifi_ssid_24" id="wifi_ssid_24" class="form-control" value="{{ old('wifi_ssid_24', $cpe->wifi_ssid_24) }}" style="background: rgba(15, 23, 42, .4); border: 1px solid rgba(148, 163, 184, .2); color: #fff; width: 100%; border-radius: 12px; padding: 10px 14px;" required>
                            </div>
                            <div class="form-group">
                                <label for="wifi_password_24" style="color: #cbd5e1; font-size: 0.82rem; font-weight: 600; margin-bottom: 6px; display: inline-block;">Password Baru (Kosongkan jika tidak diubah)</label>
                                <div style="position: relative;">
                                    <input type="password" name="wifi_password_24" id="wifi_password_24" class="form-control" placeholder="Minimal 8 karakter" style="background: rgba(15, 23, 42, .4); border: 1px solid rgba(148, 163, 184, .2); color: #fff; width: 100%; border-radius: 12px; padding: 10px 60px 10px 14px;">
                                    <button type="button" onclick="togglePasswordVisibility('wifi_password_24', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #a5b4fc; cursor: pointer; font-size: 0.82rem; font-weight: 600; padding: 4px 8px; outline: none;">Lihat</button>
                                </div>
                            </div>
                        </div>
 
                        @if(!empty($cpe->wifi_ssid_5))
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 18px; border-radius: 18px;">
                                <div style="grid-column: 1 / -1; font-weight: bold; font-size: 0.9rem; color: #e2e8f0; display: flex; align-items: center; gap: 8px;">
                                    🚀 Wi-Fi 5 GHz
                                </div>
                                <div class="form-group">
                                    <label for="wifi_ssid_5" style="color: #cbd5e1; font-size: 0.82rem; font-weight: 600; margin-bottom: 6px; display: inline-block;">Nama WiFi (SSID)</label>
                                    <input type="text" name="wifi_ssid_5" id="wifi_ssid_5" class="form-control" value="{{ old('wifi_ssid_5', $cpe->wifi_ssid_5) }}" style="background: rgba(15, 23, 42, .4); border: 1px solid rgba(148, 163, 184, .2); color: #fff; width: 100%; border-radius: 12px; padding: 10px 14px;" required>
                                </div>
                                <div class="form-group">
                                    <label for="wifi_password_5" style="color: #cbd5e1; font-size: 0.82rem; font-weight: 600; margin-bottom: 6px; display: inline-block;">Password Baru (Kosongkan jika tidak diubah)</label>
                                    <div style="position: relative;">
                                        <input type="password" name="wifi_password_5" id="wifi_password_5" class="form-control" placeholder="Minimal 8 karakter" style="background: rgba(15, 23, 42, .4); border: 1px solid rgba(148, 163, 184, .2); color: #fff; width: 100%; border-radius: 12px; padding: 10px 60px 10px 14px;">
                                        <button type="button" onclick="togglePasswordVisibility('wifi_password_5', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #a5b4fc; cursor: pointer; font-size: 0.82rem; font-weight: 600; padding: 4px 8px; outline: none;">Lihat</button>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <button type="submit" style="background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); color: white; border: none; padding: 14px 28px; border-radius: 14px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 0.95rem; width: fit-content; transition: transform 0.2s; box-shadow: 0 10px 20px rgba(79, 70, 229, .25);" onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'">
                            💾 Simpan Perubahan WiFi
                        </button>
                    </form>
                </div>
            </div>

            @if ($online)
                @php
                    $devices = !empty($cpe->connected_devices) ? json_decode($cpe->connected_devices, true) : [];
                    $deviceCount = is_array($devices) ? count($devices) : 0;
                @endphp
                <div class="card">
                    <div class="info-row" style="border-bottom: 1px solid rgba(148, 163, 184, .12); padding-bottom: 15px;">
                        <span class="info-label" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; width: 100%;">
                            <span>💻 Perangkat Terhubung (Connected Clients)</span>
                            <span style="background: rgba(99, 102, 241, 0.2); color: #a5b4fc; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 800; border: 1px solid rgba(99, 102, 241, 0.4); text-transform: none; letter-spacing: normal; white-space: nowrap; display: inline-flex; align-items: center; justify-content: center; text-align: center;">
                                {{ $deviceCount }} Perangkat
                            </span>
                        </span>
                    </div>
                    
                    @if (!empty($devices))
                        <div class="table-responsive">
                            <table class="responsive-table">
                                <thead>
                                    <tr>
                                        <th>Nama Perangkat</th>
                                        <th>Alamat IP</th>
                                        <th>Alamat MAC</th>
                                        <th>Tipe Koneksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($devices as $device)
                                        @php
                                            $isWireless = isset($device['interface_type']) && (str_contains(strtolower($device['interface_type']), 'wireless') || str_contains($device['interface_type'], '802.11') || str_contains(strtolower($device['interface_type']), 'wlan'));
                                            $connType = $isWireless ? 'Wireless' : 'Wired / LAN';
                                            $badgeClass = $isWireless ? 'badge-wireless' : 'badge-wired';
                                            $icon = $isWireless ? 'fa-wifi' : 'fa-ethernet';
                                            $hostname = !empty($device['hostname']) ? $device['hostname'] : 'Perangkat Tanpa Nama';
                                        @endphp
                                        <tr>
                                            <td data-label="Nama Perangkat" style="font-weight: 600; color: #f8fafc;">
                                                {{ $hostname }}
                                            </td>
                                            <td data-label="Alamat IP" style="font-family: monospace;">{{ $device['ip_address'] ?? '-' }}</td>
                                            <td data-label="Alamat MAC" style="font-family: monospace; color: #cbd5e1;">{{ $device['mac_address'] ?? '-' }}</td>
                                            <td data-label="Tipe Koneksi">
                                                <span class="conn-type-badge {{ $badgeClass }}">
                                                    <i class="fa-solid {{ $icon }}"></i> {{ $connType }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div style="text-align: center; padding: 24px; color: #94a3b8; font-style: italic;">
                            <i class="fa-solid fa-circle-info" style="font-size: 1.25rem; margin-bottom: 8px; display: block; color: #a5b4fc;"></i>
                            Tidak ada perangkat yang terdeteksi terhubung saat ini.
                        </div>
                    @endif
                </div>
            @endif
        @endif


        @include('partials.bottom-nav')
    </div>
    
    <script>
        function togglePasswordVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            if (input.type === "password") {
                input.type = "text";
                btn.textContent = "Sembunyikan";
            } else {
                input.type = "password";
                btn.textContent = "Lihat";
            }
        }
    </script>
</body>
</html>
