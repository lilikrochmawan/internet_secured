@extends('layouts.admin')

@section('title', 'Client Aktif & Remote ONT')

@section('styles')
<style>
    .monitoring-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 24px;
        background-color: white;
        padding: 8px;
        border-radius: 16px;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
        flex-wrap: wrap;
    }

    .monitoring-tab {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 12px;
        color: var(--text-gray);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }

    .monitoring-tab:hover {
        background-color: #f1f5f9;
        color: var(--text-dark);
    }

    .monitoring-tab.active {
        background: var(--primary-gradient);
        color: white;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);
    }

    .device-filter {
        background-color: white;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 16px 20px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .form-control {
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 8px 14px;
        font-size: 0.9rem;
        outline: none;
        background-color: white;
    }

    .grid-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: 20px;
        padding: 24px;
        display: flex;
        align-items: center;
        gap: 20px;
        box-shadow: var(--shadow-sm);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
    }

    .icon-indigo { background: var(--primary-gradient); }
    .icon-green { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
    .icon-red { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }

    .stat-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .stat-title {
        font-size: 0.82rem;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--text-gray);
        letter-spacing: 0.5px;
    }

    .stat-value {
        font-family: 'Outfit', sans-serif;
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--text-dark);
    }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 9999px;
        font-size: 0.78rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .badge-aktif {
        background-color: #d1fae5;
        color: #065f46;
    }

    .badge-terisolir {
        background-color: #fee2e2;
        color: #991b1b;
    }

    .badge-tidak-aktif {
        background-color: #f1f5f9;
        color: #475569;
    }

    .clients-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .clients-table th, .clients-table td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.9rem;
    }

    .clients-table th {
        background-color: #f8fafc;
        font-weight: 600;
        color: var(--text-gray);
        text-align: left;
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        border: none;
    }

    .btn-danger-outline {
        background: transparent;
        border: 1px solid #ef4444;
        color: #ef4444;
    }

    .btn-danger-outline:hover {
        background-color: #ef4444;
        color: white;
    }

    .btn-primary-gradient {
        background: var(--primary-gradient);
        color: white;
        box-shadow: 0 4px 10px rgba(79, 70, 229, 0.15);
    }

    .btn-primary-gradient:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }

    .btn-primary-gradient:disabled {
        background: #cbd5e1;
        box-shadow: none;
        cursor: not-allowed;
        transform: none;
    }

    /* Modal loading style */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        color: white;
        flex-direction: column;
        gap: 16px;
    }

    .spinner {
        width: 50px;
        height: 50px;
        border: 5px solid rgba(255,255,255,0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: spin 1s infinite linear;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Modal Styles */
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(4px);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    .modal.show {
        display: flex;
        opacity: 1;
    }

    .modal-content {
        background-color: white;
        border-radius: 20px;
        width: 100%;
        max-width: 500px;
        padding: 28px;
        box-shadow: var(--shadow-lg);
        animation: modalSlide 0.2s ease;
        position: relative;
    }

    @keyframes modalSlide {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .modal-title {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 1.2rem;
        color: var(--text-dark);
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 1.25rem;
        color: var(--text-gray);
        cursor: pointer;
        transition: color 0.2s;
    }

    .modal-close:hover {
        color: #ef4444;
    }

    .form-group {
        margin-bottom: 16px;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .form-group label {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-dark);
    }

    .btn-submit {
        background: var(--primary-gradient);
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        width: 100%;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);
        transition: all 0.2s;
        margin-top: 10px;
    }

    .btn-submit:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }
</style>
@endsection

@section('content')
<!-- Filter Perangkat -->
<div class="device-filter">
    <form method="GET" action="{{ route('admin.monitoring.active') }}" style="display:flex; align-items:center; gap:10px; width:100%; flex-wrap:wrap;">
        <label for="device_id" style="font-weight:600; font-size:0.9rem;">Pilih Perangkat Mikrotik:</label>
        <select name="device_id" id="device_id" class="form-control" onchange="this.form.submit()" style="min-width: 250px; max-width: 100%;">
            @foreach($mikrotik_devices as $dev)
                <option value="{{ $dev->id_mikrotik }}" {{ $selected_device_id == $dev->id_mikrotik ? 'selected' : '' }}>
                    {{ $dev->nama_mikrotik ?: $dev->ip }} ({{ $dev->ip }})
                </option>
            @endforeach
        </select>
    </form>
</div>

<!-- Tab Navigasi Monitoring -->
<div class="monitoring-tabs">
    <a href="{{ route('admin.monitoring.index', ['device_id' => $selected_device_id]) }}" class="monitoring-tab">
        <i class="fa-solid fa-chart-line"></i> Traffic & Log Realtime
    </a>
    <a href="{{ route('admin.monitoring.active', ['device_id' => $selected_device_id]) }}" class="monitoring-tab active">
        <i class="fa-solid fa-users"></i> Client Aktif & Remote ONT
    </a>
    <a href="{{ route('admin.monitoring.pppoe', ['device_id' => $selected_device_id]) }}" class="monitoring-tab">
        <i class="fa-solid fa-user-shield"></i> User PPPoE
    </a>
    <a href="{{ route('admin.monitoring.profiles', ['device_id' => $selected_device_id]) }}" class="monitoring-tab">
        <i class="fa-solid fa-folder-tree"></i> Profil PPPoE
    </a>
</div>

@if(!$connected)
    <div class="card" style="border-left: 5px solid #ef4444;">
        <div style="display:flex; gap:16px; align-items:center;">
            <i class="fa-solid fa-circle-exclamation" style="font-size: 2.5rem; color:#ef4444;"></i>
            <div>
                <h3 style="color:#ef4444;">Koneksi MikroTik Gagal</h3>
                <p style="margin-top:4px; color:var(--text-gray);">{{ $error }}</p>
            </div>
        </div>
    </div>
@else
    <!-- Statistik Grid -->
    <div class="grid-stats">
        <div class="stat-card">
            <div class="stat-icon icon-indigo">
                <i class="fa-solid fa-signal"></i>
            </div>
            <div class="stat-info">
                <span class="stat-title">PPPoE Online / Total</span>
                <span class="stat-value">{{ $totalActive }} / {{ $totalAll }} Client</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon icon-green">
                <i class="fa-solid fa-user-check"></i>
            </div>
            <div class="stat-info">
                <span class="stat-title">Client Aktif</span>
                <span class="stat-value">
                    {{ count(array_filter($clientsList, function($c) { return $c['status'] == 'aktif'; })) }} Client
                </span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon icon-red">
                <i class="fa-solid fa-user-slash"></i>
            </div>
            <div class="stat-info">
                <span class="stat-title">Terisolir / Mati</span>
                <span class="stat-value">
                    {{ count(array_filter($clientsList, function($c) { return $c['status'] == 'terisolir'; })) }} Client
                </span>
            </div>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="card">
        <!-- Search, Title, and Setting NAT -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:12px;">
            <div style="font-family:'Outfit', sans-serif; font-size:1.1rem; font-weight:700; color:var(--text-dark); display:flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-users" style="color:#4f46e5;"></i>
                <span>Daftar Client PPPoE & Remote ONT</span>
            </div>
            
            <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-gray);">Tampilkan:</span>
                    <select id="tableLimit" class="form-control" style="padding: 4px 8px; height: 40px; border-radius: 10px; font-size: 0.85rem; width: auto; margin: 0;">
                        <option value="10" selected>10 Baris</option>
                        <option value="25">25 Baris</option>
                        <option value="50">50 Baris</option>
                        <option value="100">100 Baris</option>
                    </select>
                </div>

                <div style="position:relative; min-width:220px;">
                    <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--text-gray); font-size:0.9rem;"></i>
                    <input type="text" id="tableSearch" class="form-control" placeholder="Cari nama atau IP..." style="padding-left:36px; height:40px; border-radius:10px; width:100%;">
                </div>
                
                <button type="button" class="btn-action btn-primary-gradient" onclick="openNatSettingsModal()">
                    <i class="fa-solid fa-gears"></i> Setting Firewall NAT
                </button>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="clients-table" id="clientsTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Username</th>
                        <th>IP Address</th>
                        <th>Last Logout</th>
                        <th>Status</th>
                        <th style="width: 250px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="clientsTableBody">
                    @forelse($clientsList as $index => $client)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $client['username'] }}</strong>
                                @if(!empty($client['nama_pelanggan']))
                                    <span style="font-weight: 500; font-size: 0.82rem; color: #64748b; margin-left: 6px;">({{ $client['nama_pelanggan'] }})</span>
                                @endif
                            </td>
                            <td><code>{{ $client['ip_address'] }}</code></td>
                            <td>{{ $client['last_logout'] }}</td>
                            <td>
                                @if($client['status'] == 'aktif')
                                    <span class="badge badge-aktif">
                                        <i class="fa-solid fa-circle" style="font-size:0.55rem; color:#10b981;"></i> Aktif
                                    </span>
                                @elseif($client['status'] == 'terisolir')
                                    <span class="badge badge-terisolir">
                                        <i class="fa-solid fa-circle" style="font-size:0.55rem; color:#ef4444;"></i> Terisolir
                                    </span>
                                @else
                                    <span class="badge badge-tidak-aktif">
                                        <i class="fa-solid fa-circle-notch" style="font-size:0.55rem; color:#64748b;"></i> Off
                                    </span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <div style="display:inline-flex; gap:8px; justify-content:center;">
                                    <!-- Remote ONT button -->
                                    <button type="button" 
                                            class="btn-action btn-primary-gradient remote-ont-btn" 
                                            data-ip="{{ $client['ip_address'] }}" 
                                            {{ $client['ip_address'] == '-' ? 'disabled' : '' }}
                                            title="Remote ONT (Forward NAT)">
                                        <i class="fa-solid fa-globe"></i> Remote
                                    </button>

                                    <!-- Disconnect active session -->
                                    @if($client['active_id'])
                                        <form method="POST" action="{{ route('admin.monitoring.active.disconnect') }}" onsubmit="return confirm('Apakah Anda yakin ingin memutuskan koneksi aktif user {{ $client['username'] }}?')">
                                            @csrf
                                            <input type="hidden" name="device_id" value="{{ $selected_device_id }}">
                                            <input type="hidden" name="id" value="{{ $client['active_id'] }}">
                                            <button type="submit" class="btn-action btn-danger-outline" title="Kick / Putuskan Sesi Aktif">
                                                <i class="fa-solid fa-user-slash"></i> Kick
                                            </button>
                                        </form>
                                    @else
                                        <button class="btn-action btn-danger-outline" style="opacity: 0.4; cursor: not-allowed;" disabled title="User offline">
                                            <i class="fa-solid fa-user-slash"></i> Kick
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-gray); padding:30px;">
                                Tidak ada data client PPPoE di perangkat Mikrotik ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div id="clientsPagination"></div>
    </div>
@endif

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
    <div style="font-family:'Outfit',sans-serif; font-size:1.2rem; font-weight:700;">Mengalihkan Port-Forwarding...</div>
    <div style="font-size:0.9rem; opacity:0.8;">Sedang mengupdate rule NAT "FORWARD MODEM" di Mikrotik.</div>
</div>

<!-- Modal NAT Settings -->
<div class="modal" id="natSettingsModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Setting NAT Firewall Remote ONT</h3>
            <button class="modal-close" onclick="closeNatSettingsModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        
        <div id="natSettingsLoading" style="text-align: center; padding: 20px 0; color: var(--text-gray);">
            <i class="fa-solid fa-circle-notch fa-spin" style="font-size: 2.0rem; color: #4f46e5;"></i>
            <p style="margin-top: 10px;">Mengambil data NAT dari MikroTik...</p>
        </div>

        <form id="natSettingsForm" style="display: none;" onsubmit="saveNatSettings(event)">
            <div class="form-group">
                <label for="nat_protocol">Protokol</label>
                <select name="protocol" id="nat_protocol" class="form-control" required>
                    <option value="tcp">tcp</option>
                    <option value="udp">udp</option>
                </select>
            </div>

            <div class="form-group">
                <label for="nat_remote_host">Domain/IP Publik Remote ONT <span style="color:#ef4444;">*</span></label>
                <input type="text" name="remote_host" id="nat_remote_host" class="form-control" placeholder="Contoh: rmtsg3.perwiramedia.com" required>
                <small style="color: var(--text-gray); font-size: 0.75rem;">Domain DDNS atau IP Publik yang diarahkan ke ONT (contoh: rmtsg3.perwiramedia.com)</small>
            </div>

            <div class="form-group">
                <label for="nat_dst_port">Dst. Port (Port Publik Router) <span style="color:#ef4444;">*</span></label>
                <input type="text" name="dst_port" id="nat_dst_port" class="form-control" placeholder="Contoh: 8063" required>
                <small style="color: var(--text-gray); font-size: 0.75rem;">Port luar yang diakses (misal: http://ip-publik:8063)</small>
            </div>

            <div class="form-group">
                <label for="nat_to_ports">To Ports (Port Lokal ONT) <span style="color:#ef4444;">*</span></label>
                <input type="text" name="to_ports" id="nat_to_ports" class="form-control" placeholder="Contoh: 80" required>
                <small style="color: var(--text-gray); font-size: 0.75rem;">Port halaman web admin ONT client (biasanya 80 atau 8080)</small>
            </div>

            <div class="form-group">
                <label for="nat_in_interface">In. Interface (Optional)</label>
                <select name="in_interface" id="nat_in_interface" class="form-control">
                    <option value="">-- any / all --</option>
                </select>
                <small style="color: var(--text-gray); font-size: 0.75rem;">Interface sumber traffic luar masuk (biasanya port WAN, contoh: ether1)</small>
            </div>

            <div class="form-group">
                <label>Comment Rule</label>
                <input type="text" class="form-control" value="FORWARD MODEM" disabled style="background-color: #f1f5f9; cursor: not-allowed;">
            </div>

            <button type="submit" class="btn-submit">Simpan Setelan NAT</button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
@if($connected)
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const tableSearch = document.getElementById("tableSearch");
        const clientsTableBody = document.getElementById("clientsTableBody");
        const loadingOverlay = document.getElementById("loadingOverlay");
        const deviceId = "{{ $selected_device_id }}";

        const natModal = document.getElementById("natSettingsModal");
        const natLoading = document.getElementById("natSettingsLoading");
        const natForm = document.getElementById("natSettingsForm");
        const natProtocol = document.getElementById("nat_protocol");
        const natRemoteHost = document.getElementById("nat_remote_host");
        const natDstPort = document.getElementById("nat_dst_port");
        const natToPorts = document.getElementById("nat_to_ports");
        const natInInterface = document.getElementById("nat_in_interface");

        // Setup pagination & filtering
        setupTablePagination("#clientsTable", "#clientsPagination", "#tableLimit", "#tableSearch");

        // Remote ONT Ajax
        document.querySelectorAll(".remote-ont-btn").forEach(btn => {
            btn.addEventListener("click", function () {
                const clientIp = this.getAttribute("data-ip");
                if (!clientIp || clientIp === "-") return;

                if (!confirm(`Apakah Anda yakin ingin membuka Remote ONT untuk IP ${clientIp}?\nTindakan ini akan mengarahkan port forward 'FORWARD MODEM' di MikroTik ke IP ini.`)) {
                    return;
                }

                // Immediately open a blank new tab synchronously to bypass browser pop-up blockers
                const newTab = window.open('about:blank', '_blank');

                loadingOverlay.style.display = "flex";

                const params = new URLSearchParams();
                params.append('device_id', deviceId);
                params.append('ip', clientIp);

                fetch("{{ route('admin.monitoring.active.remote') }}", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Content-Type": "application/x-www-form-urlencoded",
                        "Accept": "application/json"
                    },
                    body: params
                })
                .then(async r => {
                    const contentType = r.headers.get("content-type");
                    if (contentType && contentType.indexOf("application/json") !== -1) {
                        const data = await r.json();
                        return { ok: r.ok, status: r.status, data };
                    } else {
                        const text = await r.text();
                        return { ok: false, status: r.status, errorText: text };
                    }
                })
                .then(res => {
                    loadingOverlay.style.display = "none";
                    if (res.ok && res.data && res.data.success) {
                        // Load the verified ONT remote URL into the opened tab
                        newTab.location.href = res.data.url;
                    } else {
                        newTab.close();
                        const errMsg = res.data ? res.data.message : (res.errorText || "Respon server tidak valid");
                        alert("Gagal melakukan remote ONT: " + errMsg);
                    }
                })
                .catch(err => {
                    loadingOverlay.style.display = "none";
                    newTab.close();
                    alert("Terjadi kesalahan jaringan atau server saat melakukan port forwarding.");
                    console.error("Fetch/URL Error:", err);
                });
            });
        });

        // NAT Settings functions
        window.openNatSettingsModal = function() {
            natModal.classList.add("show");
            natLoading.style.display = "block";
            natForm.style.display = "none";

            fetch(`{{ route('admin.monitoring.active.nat_settings') }}?device_id=${deviceId}`)
                .then(r => r.json())
                .then(res => {
                    natLoading.style.display = "none";
                    if (res.success) {
                        // Populate form
                        natProtocol.value = res.rule.protocol || 'tcp';
                        natRemoteHost.value = res.remote_host || 'rmtsg3.perwiramedia.com';
                        natDstPort.value = res.rule.dst_port || '8063';
                        natToPorts.value = res.rule.to_ports || '80';

                        // Populate interfaces dropdown
                        natInInterface.innerHTML = '<option value="">-- any / all --</option>';
                        res.interfaces.forEach(iface => {
                            const opt = document.createElement("option");
                            opt.value = iface;
                            opt.textContent = iface;
                            if (iface === res.rule.in_interface) {
                                opt.selected = true;
                            }
                            natInInterface.appendChild(opt);
                        });

                        natForm.style.display = "block";
                    } else {
                        alert("Gagal memuat pengaturan NAT: " + res.message);
                        closeNatSettingsModal();
                    }
                })
                .catch(err => {
                    natLoading.style.display = "none";
                    alert("Terjadi kesalahan koneksi server.");
                    closeNatSettingsModal();
                });
        }

        window.closeNatSettingsModal = function() {
            natModal.classList.remove("show");
        }

        window.saveNatSettings = function(e) {
            e.preventDefault();

            const params = new URLSearchParams();
            params.append('device_id', deviceId);
            params.append('protocol', natProtocol.value);
            params.append('remote_host', natRemoteHost.value);
            params.append('dst_port', natDstPort.value);
            params.append('to_ports', natToPorts.value);
            params.append('in_interface', natInInterface.value);

            fetch("{{ route('admin.monitoring.active.update_nat') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: params
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    alert(res.message);
                    closeNatSettingsModal();
                } else {
                    alert("Gagal menyimpan setelan NAT: " + res.message);
                }
            })
            .catch(err => {
                alert("Terjadi kesalahan koneksi server saat menyimpan.");
                console.error(err);
            });
        }

        // Close modal when clicking outside
        window.addEventListener("click", function(event) {
            if (event.target === natModal) {
                closeNatSettingsModal();
            }
        });
    });
</script>
@endif
@endsection
