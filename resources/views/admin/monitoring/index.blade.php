@extends('layouts.admin')

@section('title', 'Mikrotik Monitoring')

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
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
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

    .icon-yellow { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
    .icon-green { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
    .icon-indigo { background: var(--primary-gradient); }

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
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--text-dark);
    }

    .stat-desc {
        font-size: 0.78rem;
        color: var(--text-gray);
    }

    .traffic-boxes {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
        margin-top: 14px;
    }

    .traffic-box {
        padding: 16px;
        border-radius: 14px;
        border: 1px solid var(--border-color);
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .traffic-box.rx {
        background-color: #eff6ff;
        border-color: #bfdbfe;
        color: #1e40af;
    }

    .traffic-box.tx {
        background-color: #fff7ed;
        border-color: #fed7aa;
        color: #c2410c;
    }

    .traffic-box-title {
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .traffic-box-value {
        font-family: 'Outfit', sans-serif;
        font-size: 1.5rem;
        font-weight: 800;
    }

    .chart-wrapper {
        position: relative;
        width: 100%;
        height: 320px;
        margin-top: 20px;
    }

    .logs-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 14px;
    }

    .logs-table th, .logs-table td {
        padding: 12px 16px;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.85rem;
    }

    .logs-table th {
        background-color: #f8fafc;
        font-weight: 600;
        color: var(--text-gray);
        text-align: left;
    }
</style>
@endsection

@section('content')
<!-- Filter Perangkat -->
<div class="device-filter">
    <form method="GET" action="{{ route('admin.monitoring.index') }}" style="display:flex; align-items:center; gap:10px; width:100%; flex-wrap:wrap;">
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
    <a href="{{ route('admin.monitoring.index', ['device_id' => $selected_device_id]) }}" class="monitoring-tab active">
        <i class="fa-solid fa-chart-line"></i> Traffic & Log Realtime
    </a>
    <a href="{{ route('admin.monitoring.active', ['device_id' => $selected_device_id]) }}" class="monitoring-tab">
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
            <div class="stat-icon icon-yellow">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div class="stat-info">
                <span class="stat-title">Tanggal & Waktu</span>
                <span class="stat-value" id="routerClock">{{ $date }} {{ $time }}</span>
                <span class="stat-desc" id="routerUptime">Uptime: {{ $uptime }}</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon icon-green">
                <i class="fa-solid fa-microchip"></i>
            </div>
            <div class="stat-info">
                <span class="stat-title">Perangkat Winbox</span>
                <span class="stat-value">{{ $board_name }}</span>
                <span class="stat-desc">Model: {{ $model }} | Versi OS: {{ $version }}</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon icon-indigo">
                <i class="fa-solid fa-server"></i>
            </div>
            <div class="stat-info">
                <span class="stat-title">Resource CPU & RAM</span>
                <span class="stat-value" id="routerCpu">CPU: {{ $cpu_load }} %</span>
                <span class="stat-desc" id="routerMemoryHdd">RAM Bebas: {{ $free_memory }} MB | HDD: {{ $total_hdd }}</span>
            </div>
        </div>
    </div>

    <!-- Monitoring Traffic Realtime -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-chart-area"></i>
                <span>Monitoring Traffic Realtime</span>
            </div>
        </div>

        <div style="display: flex; gap:20px; align-items: flex-start; flex-wrap: wrap; margin-top:10px;">
            <div style="flex: 1; min-width: 250px;">
                <label for="etherSelect" style="font-weight:600; font-size:0.85rem; display:block; margin-bottom:6px;">Pilih Interface (Ethernet / VLAN)</label>
                <select id="etherSelect" class="form-control" style="width:100%;">
                    <option value="">Memuat interface...</option>
                </select>
                <small id="trafficStatus" style="display:block; margin-top:8px; color:var(--text-gray);"></small>

                <div class="traffic-boxes">
                    <div class="traffic-box rx">
                        <span class="traffic-box-title">RX (Download)</span>
                        <span class="traffic-box-value" id="rxNow">-</span>
                    </div>
                    <div class="traffic-box tx">
                        <span class="traffic-box-title">TX (Upload)</span>
                        <span class="traffic-box-value" id="txNow">-</span>
                    </div>
                </div>
            </div>

            <div style="flex: 3; min-width: 320px; width: 100%;">
                <div class="chart-wrapper">
                    <canvas id="trafficChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs Mikrotik -->
    <div class="card">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <div class="card-title">
                <i class="fa-solid fa-list-check"></i>
                <span>Log Aktivitas Mikrotik</span>
            </div>
            
            <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 600; cursor: pointer; color: var(--text-dark); user-select: none; margin: 0;">
                    <input type="checkbox" id="hideApiLogs" checked style="width: 16px; height: 16px; accent-color: #4f46e5;">
                    Sembunyikan Log Login API
                </label>
                
                <div style="display: flex; align-items: center; gap: 6px;">
                    <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-dark);">Tampilkan:</span>
                    <select id="logLimit" class="form-control" style="padding: 4px 10px; height: 32px; border-radius: 8px; font-size: 0.82rem; min-width: 80px; width: auto; margin: 0;">
                        <option value="10" selected>10 Baris</option>
                        <option value="20">20 Baris</option>
                        <option value="50">50 Baris</option>
                        <option value="100">100 Baris</option>
                        <option value="200">200 Baris</option>
                    </select>
                </div>

                <div style="position:relative; min-width:180px;">
                    <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--text-gray); font-size:0.8rem;"></i>
                    <input type="text" id="logSearch" class="form-control" placeholder="Cari log..." style="padding-left:30px; height:32px; border-radius:8px; width:100%; font-size:0.82rem; margin: 0;">
                </div>
            </div>
        </div>
        
        <div style="overflow-x: auto;">
            <table class="logs-table" id="logsTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th style="width: 150px;">Waktu</th>
                        <th style="width: 180px;">Topics</th>
                        <th>Message / Keterangan</th>
                    </tr>
                </thead>
                <tbody id="mikrotikLogsBody">
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--text-gray); padding:20px;">
                            Memuat log aktivitas...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="logsPagination"></div>
    </div>
@endif
@endsection

@section('scripts')
@if($connected)
<!-- Load Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const deviceId = "{{ $selected_device_id }}";
        const etherSelect = document.getElementById("etherSelect");
        const rxNow = document.getElementById("rxNow");
        const txNow = document.getElementById("txNow");
        const trafficStatus = document.getElementById("trafficStatus");
        const logsBody = document.getElementById("mikrotikLogsBody");

        let trafficTimer = null;
        let chart = null;
        const maxPoints = 20;
        const labels = [];
        const rxData = [];
        const txData = [];

        function formatBps(bps) {
            let v = Number(bps || 0);
            if (v >= 1000000) {
                return (v / 1000000).toFixed(2) + " Mbps";
            } else if (v >= 1000) {
                return (v / 1000).toFixed(2) + " Kbps";
            }
            return v + " bps";
        }

        // Initialize Chart
        function initChart() {
            const ctx = document.getElementById("trafficChart").getContext("2d");
            chart = new Chart(ctx, {
                type: "line",
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: "RX (Download)",
                            borderColor: "#2563eb",
                            backgroundColor: "rgba(37, 99, 235, 0.08)",
                            data: rxData,
                            fill: true,
                            tension: 0.3,
                            borderWidth: 2,
                            pointRadius: 0
                        },
                        {
                            label: "TX (Upload)",
                            borderColor: "#ea580c",
                            backgroundColor: "rgba(234, 88, 12, 0.05)",
                            data: txData,
                            fill: true,
                            tension: 0.3,
                            borderWidth: 2,
                            pointRadius: 0
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            grid: { display: false }
                        },
                        y: {
                            ticks: {
                                callback: function (value) {
                                    return formatBps(value);
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: { position: "top" }
                    }
                }
            });
        }

        function addChartData(rx, tx) {
            if (!chart) return;
            const timeStr = new Date().toLocaleTimeString();
            
            labels.push(timeStr);
            rxData.push(rx);
            txData.push(tx);

            if (labels.length > maxPoints) {
                labels.shift();
                rxData.shift();
                txData.shift();
            }

            chart.update();
        }

        // Fetch List of Interfaces
        function loadInterfaces() {
            trafficStatus.textContent = "Memuat interface...";
            fetch("{{ route('admin.monitoring.interfaces') }}?device_id=" + deviceId)
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        if (res.data && res.data.length > 0) {
                            etherSelect.innerHTML = "";
                            res.data.forEach(it => {
                                const opt = document.createElement("option");
                                opt.value = it.name;
                                opt.textContent = `${it.name} [${it.type.toUpperCase()}] ${it.running ? '(running)' : ''}`;
                                etherSelect.appendChild(opt);
                            });
                            
                            // Start monitoring on the first interface
                            startMonitoring(etherSelect.value);
                        } else {
                            etherSelect.innerHTML = "<option value=''>Interface tidak ditemukan</option>";
                            trafficStatus.textContent = "Tidak ada interface aktif.";
                        }
                    } else {
                        etherSelect.innerHTML = "<option value=''>Koneksi Gagal</option>";
                        trafficStatus.innerHTML = `<span style="color:#ef4444;"><i class="fa-solid fa-triangle-exclamation"></i> Gagal terhubung ke Router: ${res.message || 'API error.'}</span>`;
                    }
                })
                .catch(err => {
                    trafficStatus.innerHTML = '<span style="color:#ef4444;"><i class="fa-solid fa-triangle-exclamation"></i> Gagal memuat interface.</span>';
                });
        }

        function startMonitoring(iface) {
            if (trafficTimer) clearInterval(trafficTimer);
            
            // Clear current data
            labels.length = 0;
            rxData.length = 0;
            txData.length = 0;
            if (chart) chart.destroy();
            
            initChart();

            trafficStatus.textContent = `Memantau interface ${iface} (update tiap 2 detik)...`;

            trafficTimer = setInterval(() => {
                const params = new URLSearchParams();
                params.append('device_id', deviceId);
                params.append('iface', iface);

                fetch("{{ route('admin.monitoring.traffic') }}", {
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
                        const rxBps = res.data.rx_bps;
                        const txBps = res.data.tx_bps;

                        rxNow.textContent = formatBps(rxBps);
                        txNow.textContent = formatBps(txBps);

                        addChartData(rxBps, txBps);
                    }
                })
                .catch(err => {
                    console.error("Traffic monitor error:", err);
                });
            }, 2000);
        }

        etherSelect.addEventListener("change", function () {
            startMonitoring(this.value);
        });

        let currentLogs = [];
        let logCurrentPage = 1;
        const hideApiCheckbox = document.getElementById("hideApiLogs");
        const logLimitSelect = document.getElementById("logLimit");
        const logSearchInput = document.getElementById("logSearch");
        const logsPagination = document.getElementById("logsPagination");

        function renderLogs() {
            const limit = parseInt(logLimitSelect.value) || 10;
            const hideApi = hideApiCheckbox.checked;
            const query = logSearchInput.value.toLowerCase();

            let filtered = currentLogs;
            if (hideApi) {
                filtered = filtered.filter(log => {
                    const msg = (log.message || '').toLowerCase();
                    return !(msg.includes("via api") && (msg.includes("logged in") || msg.includes("logged out")));
                });
            }

            if (query) {
                filtered = filtered.filter(log => {
                    return (log.message || '').toLowerCase().includes(query) ||
                           (log.topics || '').toLowerCase().includes(query) ||
                           (log.time || '').toLowerCase().includes(query);
                });
            }

            const totalItems = filtered.length;
            const totalPages = Math.ceil(totalItems / limit) || 1;

            if (logCurrentPage > totalPages) logCurrentPage = totalPages;
            if (logCurrentPage < 1) logCurrentPage = 1;

            const startIndex = (logCurrentPage - 1) * limit;
            const endIndex = startIndex + limit;

            const sliced = filtered.slice(startIndex, endIndex);

            if (sliced.length > 0) {
                let html = "";
                sliced.forEach((log, index) => {
                    html += `
                        <tr>
                            <td>${startIndex + index + 1}</td>
                            <td>${log.time}</td>
                            <td><span style="background-color:#f1f5f9; padding:2px 6px; border-radius:4px; font-size:0.75rem; font-weight:600; color:var(--text-gray);">${log.topics}</span></td>
                            <td>${log.message}</td>
                        </tr>
                    `;
                });
                logsBody.innerHTML = html;
            } else {
                logsBody.innerHTML = `<tr><td colspan="4" style="text-align:center; color:var(--text-gray); padding: 20px;">Tidak ada log yang sesuai filter.</td></tr>`;
            }

            renderLogControls(totalPages);
        }

        function renderLogControls(totalPages) {
            logsPagination.innerHTML = "";
            if (totalPages <= 1) {
                logsPagination.style.display = "none";
                return;
            }
            logsPagination.style.display = "flex";
            logsPagination.className = "pagination-wrapper";

            // Previous button
            const prevBtn = document.createElement("button");
            prevBtn.type = "button";
            prevBtn.textContent = "Sebelumnya";
            prevBtn.className = "page-btn" + (logCurrentPage === 1 ? " disabled" : "");
            prevBtn.disabled = logCurrentPage === 1;
            prevBtn.onclick = () => {
                if (logCurrentPage > 1) {
                    logCurrentPage--;
                    renderLogs();
                }
            };
            logsPagination.appendChild(prevBtn);

            // Pages
            let startPage = Math.max(1, logCurrentPage - 2);
            let endPage = Math.min(totalPages, startPage + 4);
            if (endPage - startPage < 4) {
                startPage = Math.max(1, endPage - 4);
            }

            if (startPage > 1) {
                const firstBtn = document.createElement("button");
                firstBtn.type = "button";
                firstBtn.textContent = "1";
                firstBtn.className = "page-btn";
                firstBtn.onclick = () => { logCurrentPage = 1; renderLogs(); };
                logsPagination.appendChild(firstBtn);

                if (startPage > 2) {
                    const ellipsis = document.createElement("span");
                    ellipsis.textContent = "...";
                    ellipsis.className = "page-ellipsis";
                    logsPagination.appendChild(ellipsis);
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                const pageBtn = document.createElement("button");
                pageBtn.type = "button";
                pageBtn.textContent = i;
                pageBtn.className = "page-btn" + (logCurrentPage === i ? " active" : "");
                pageBtn.onclick = () => { logCurrentPage = i; renderLogs(); };
                logsPagination.appendChild(pageBtn);
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    const ellipsis = document.createElement("span");
                    ellipsis.textContent = "...";
                    ellipsis.className = "page-ellipsis";
                    logsPagination.appendChild(ellipsis);
                }

                const lastBtn = document.createElement("button");
                lastBtn.type = "button";
                lastBtn.textContent = totalPages;
                lastBtn.className = "page-btn";
                lastBtn.onclick = () => { logCurrentPage = totalPages; renderLogs(); };
                logsPagination.appendChild(lastBtn);
            }

            // Next button
            const nextBtn = document.createElement("button");
            nextBtn.type = "button";
            nextBtn.textContent = "Selanjutnya";
            nextBtn.className = "page-btn" + (logCurrentPage === totalPages ? " disabled" : "");
            nextBtn.disabled = logCurrentPage === totalPages;
            nextBtn.onclick = () => {
                if (logCurrentPage < totalPages) {
                    logCurrentPage++;
                    renderLogs();
                }
            };
            logsPagination.appendChild(nextBtn);
        }

        // Load logs (fetch 200 logs to ensure enough records remain after API filter)
        function loadLogs() {
            fetch("{{ route('admin.monitoring.logs') }}?device_id=" + deviceId + "&limit=200")
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        currentLogs = res.data || [];
                        renderLogs();
                    } else {
                        logsBody.innerHTML = `<tr><td colspan="4" style="text-align:center; color:#ef4444; padding: 20px;">Gagal memuat log: ${res.message || 'API error.'}</td></tr>`;
                    }
                })
                .catch(err => {
                    logsBody.innerHTML = `<tr><td colspan="4" style="text-align:center; color:#ef4444; padding: 20px;">Gagal memuat log aktivitas.</td></tr>`;
                });
        }

        // Add event listeners for filters
        hideApiCheckbox.addEventListener("change", () => {
            logCurrentPage = 1;
            renderLogs();
        });
        logLimitSelect.addEventListener("change", function() {
            logCurrentPage = 1;
            renderLogs();
        });
        logSearchInput.addEventListener("keyup", function() {
            logCurrentPage = 1;
            renderLogs();
        });

        loadInterfaces();
        
        // Load logs after 500ms delay to prevent concurrent Mikrotik API connection limit exhaustion
        setTimeout(loadLogs, 500);
        
        // Refresh logs every 10 seconds
        setInterval(loadLogs, 10000);

        // Refresh Mikrotik resources (Date, Time, CPU & Memory) every 2 seconds
        function loadResources() {
            if (!document.getElementById('routerClock')) return;
            
            fetch("{{ route('admin.monitoring.resources') }}?device_id=" + deviceId)
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        document.getElementById('routerClock').textContent = res.date + " " + res.time;
                        document.getElementById('routerUptime').textContent = "Uptime: " + res.uptime;
                        document.getElementById('routerCpu').textContent = "CPU: " + res.cpu_load + " %";
                        document.getElementById('routerMemoryHdd').textContent = "RAM Bebas: " + res.free_memory + " MB | HDD: " + res.total_hdd;
                    }
                })
                .catch(err => console.error("Gagal memperbarui resource:", err));
        }

        // Initialize resource refresh (wait 2 seconds for first load to prevent concurrent connection spike on load)
        setInterval(loadResources, 2000);
    });
</script>
@endif
@endsection
