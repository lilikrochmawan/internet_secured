@extends('layouts.admin')

@section('title', 'Dashboard')

@section('styles')
<style>
    .welcome-banner {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        color: white;
        padding: 24px;
        border-radius: 20px;
        margin-bottom: 24px;
        box-shadow: var(--shadow-sm);
        position: relative;
        overflow: hidden;
    }

    .welcome-banner::before {
        content: '';
        position: absolute;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 50%;
        top: -50px;
        right: -50px;
    }

    .welcome-banner h2 {
        font-family: 'Outfit', sans-serif;
        font-size: 1.6rem;
        font-weight: 800;
        margin-bottom: 6px;
    }

    .welcome-banner p {
        opacity: 0.9;
        font-size: 0.9rem;
        max-width: 700px;
        line-height: 1.4;
    }

    .dashboard-row-1 {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(0, 0.9fr) minmax(0, 1.1fr);
        gap: 20px;
        margin-bottom: 24px;
    }
    .dashboard-row-2 {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1.1fr) minmax(0, 1.3fr);
        gap: 20px;
        margin-bottom: 24px;
    }
    .dashboard-row-3 {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1.6fr);
        gap: 20px;
        margin-bottom: 24px;
    }

    .dashboard-row-1.row-teknisi {
        grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr);
    }
    .dashboard-row-2.row-teknisi {
        grid-template-columns: minmax(min(300px, 100%), 480px);
    }
    .dashboard-row-3.row-kasir {
        grid-template-columns: minmax(0, 1fr);
    }
    .dashboard-row-1.row-mitra {
        grid-template-columns: minmax(0, 1fr);
    }
    .dashboard-row-2.row-mitra {
        grid-template-columns: minmax(0, 1fr);
    }

    .mikrotik-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
    }
    .infra-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 6px;
    }
    @media (max-width: 576px) {
        .mikrotik-grid {
            grid-template-columns: minmax(0, 1fr);
        }
        .infra-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 1200px) {
        .dashboard-row-1, .dashboard-row-2, .dashboard-row-3 {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    @media (max-width: 576px) {
        .welcome-banner {
            padding: 18px 16px;
            border-radius: 16px;
            margin-bottom: 16px;
        }
        .welcome-banner h2 {
            font-size: 1.3rem;
            line-height: 1.2;
        }
        .welcome-banner p {
            font-size: 0.8rem;
            margin-top: 4px;
        }
        .dashboard-row-1, .dashboard-row-2, .dashboard-row-3 {
            gap: 16px;
            margin-bottom: 16px;
        }
        .card {
            padding: 14px 12px !important;
            border-radius: 12px !important;
        }
        .block-box {
            padding: 10px 12px;
            border-radius: 10px;
        }
        .block-val {
            font-size: 1.25rem;
        }
        .block-lbl {
            font-size: 0.65rem;
        }
        .device-row {
            flex-wrap: wrap;
            gap: 8px;
            padding: 10px;
        }
        .device-time {
            width: 100%;
            border-top: 1px dashed var(--border-color);
            padding-top: 6px;
            margin-top: 4px;
            text-align: left;
        }
    }

    @media (max-width: 480px) {
        .card-header-custom {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }
        .card-header-custom .badge-pill,
        .card-header-custom a {
            align-self: flex-start;
        }
    }

    .card {
        background-color: white;
        border-radius: 20px;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
        padding: 20px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 16px;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .card-header-custom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 12px;
        margin-bottom: 4px;
    }

    .card-title-custom {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .card-title-custom h3 {
        font-family: 'Outfit', sans-serif;
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--text-dark);
    }

    .card-title-custom span {
        font-size: 0.78rem;
        color: var(--text-gray);
    }

    .badge-pill {
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .badge-financial { background-color: #ecfdf5; color: #059669; }
    .badge-demographics { background-color: #eff6ff; color: #2563eb; }
    .badge-stabil { background-color: #ecfeff; color: #0891b2; }
    .badge-live { background-color: #f0f9ff; color: #0284c7; }
    .badge-attention { background-color: #fff1f2; color: #e11d48; }
    .badge-radius { background-color: #f8fafc; color: #64748b; }
    .badge-realtime { background-color: #f5f3ff; color: #7c3aed; }

    .block-grid-2x2 {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 12px;
    }

    .block-box {
        padding: 12px 16px;
        border-radius: 14px;
        color: white;
        display: flex;
        flex-direction: column;
        gap: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    .block-box-blue { background-color: #2563eb; }
    .block-box-green { background-color: #10b981; }
    .block-box-orange { background-color: #f97316; }
    .block-box-red { background-color: #ef4444; }
    .block-box-cyan { background-color: #06b6d4; }
    
    .block-val {
        font-family: 'Outfit', sans-serif;
        font-size: 1.5rem;
        font-weight: 800;
        line-height: 1.1;
    }
    .block-lbl {
        font-size: 0.7rem;
        font-weight: 700;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .isolation-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 12px;
    }
    .isolation-box-full {
        grid-column: span 2;
    }

    .device-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        background-color: #f8fafc;
        margin-top: 8px;
        font-size: 0.82rem;
    }
    .device-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .device-name {
        font-weight: 700;
        color: var(--text-dark);
    }
    .device-ip {
        font-size: 0.72rem;
        color: var(--text-gray);
    }
    .device-status {
        display: flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
    }
    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }
    .dot-online { background-color: #10b981; }
    .dot-offline { background-color: #ef4444; }
    .text-online { color: #10b981; }
    .text-offline { color: #ef4444; }

    .device-time {
        font-size: 0.72rem;
        color: var(--text-gray);
        text-align: right;
    }

    .table-container {
        overflow-x: auto;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .table th, .table td {
        padding: 10px 12px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.82rem;
    }

    .table th {
        font-weight: 600;
        color: var(--text-gray);
        background-color: #f8fafc;
    }

    .badge-act {
        display: inline-flex;
        align-items: center;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 700;
    }
    .badge-isolate {
        background-color: #fee2e2;
        color: #ef4444;
    }
    .badge-restore {
        background-color: #dcfce7;
        color: #16a34a;
    }
    .badge-payment {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .badge-midtrans {
        background-color: #ecfdf5;
        color: #059669;
    }
    .badge-kasir {
        background-color: #f5f3ff;
        color: #7c3aed;
    }
    .text-success {
        color: #16a34a;
        font-weight: 600;
    }
</style>
@endsection

@section('content')
<div class="welcome-banner">
    <h2>Selamat Datang Kembali, {{ Auth::user()->nama_user }}!</h2>
    <p>
        @if(Auth::user()->level === 'admin')
            Pusat kendali operasional jaringan Anda. Pantau performa bisnis, respon keluhan pelanggan, dan tinjau transaksi real-time secara instan dalam satu sistem terintegrasi database & MikroTik.
        @elseif(Auth::user()->level === 'mitra')
            Pusat kendali operasional wilayah Anda. Pantau performa cabang, respon keluhan pelanggan, dan tinjau transaksi real-time secara instan dalam satu sistem terintegrasi database & MikroTik.
        @elseif(Auth::user()->level === 'teknisi')
            Pusat monitoring infrastruktur jaringan. Tangani laporan gangguan pelanggan secara cepat, pantau status pelanggan terisolir, dan kelola distribusi port ODC/ODP secara real-time.
        @elseif(Auth::user()->level === 'kasir')
            Pusat manajemen administrasi keuangan. Pantau riwayat transaksi terbaru dan kelola pembukuan pembayaran masuk secara praktis dan real-time.
        @else
            Selamat bertugas! Optimalkan aktivitas pemasaran, pantau cakupan wilayah layanan, dan tingkatkan pertumbuhan pelanggan baru di wilayah Anda.
        @endif
    </p>
</div>

@if(Auth::user()->level === 'mitra')
<!-- Layout Khusus Mitra: Status Pelanggan & Status Pembayaran Sejajar (2 Kolom) -->
<div class="dashboard-row-1" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr); gap: 20px; margin-bottom: 24px;">
    <!-- Card 2: Status Pelanggan -->
    <div class="card">
        <div class="card-header-custom">
            <div class="card-title-custom">
                <h3>Status Pelanggan</h3>
                <span>Komposisi status saat ini.</span>
            </div>
            <span class="badge-pill badge-demographics">Demographics</span>
        </div>
        <div style="height: 220px; display: flex; align-items: center; justify-content: center; position: relative;">
            <canvas id="customerStatusChart" style="max-height: 180px; max-width: 180px;"></canvas>
        </div>
    </div>

    <!-- Card 4: Status pembayaran -->
    <div class="card">
        <div class="card-header-custom">
            <div class="card-title-custom">
                <h3>Status pembayaran</h3>
                <span>Lunas: {{ $suksesBayarCount }} • Belum Bayar: {{ $belumTerbayarCount }}</span>
            </div>
            <span class="badge-pill badge-financial">Pembayaran</span>
        </div>
        
        <div class="isolation-grid" style="height: 220px; display: flex; flex-direction: column; justify-content: space-around; padding: 10px 0; gap: 8px;">
            <div style="display: flex; gap: 12px; width: 100%;">
                <div class="block-box block-box-red" style="flex: 1; padding: 12px;">
                    <span class="block-val">{{ $belumTerbayarCount }}</span>
                    <span class="block-lbl">Belum Terbayar</span>
                </div>
                <div class="block-box block-box-green" style="flex: 1; padding: 12px;">
                    <span class="block-val">{{ $suksesBayarCount }}</span>
                    <span class="block-lbl">Sukses Bayar</span>
                </div>
            </div>
            <div class="block-box block-box-orange" style="width: 100%; padding: 12px;">
                <span class="block-val">{{ $bukaSementaraCount }}</span>
                <span class="block-lbl">Buka Sementara</span>
            </div>
        </div>
    </div>
</div>
@else
    <!-- Row 1: Charts & Keluhan -->
    @if(in_array(Auth::user()->level, ['admin', 'teknisi']))
    <div class="dashboard-row-1 {{ Auth::user()->level === 'teknisi' ? 'row-teknisi' : '' }}">
        <!-- Card 1: Tren Pemasukan & Pengeluaran -->
        @if(Auth::user()->level === 'admin')
        <div class="card">
            <div class="card-header-custom">
                <div class="card-title-custom">
                    <h3>Tren Pemasukan & Pengeluaran</h3>
                    <span>Perbandingan 6 bulan terakhir.</span>
                </div>
                <span class="badge-pill badge-financial">Financial</span>
            </div>
            <div style="height: 220px; position: relative;">
                <canvas id="financialChart"></canvas>
            </div>
        </div>
        @endif

        <!-- Card 2: Status Pelanggan -->
        <div class="card">
            <div class="card-header-custom">
                <div class="card-title-custom">
                    <h3>Status Pelanggan</h3>
                    <span>Komposisi status saat ini.</span>
                </div>
                <span class="badge-pill badge-demographics">Demographics</span>
            </div>
            <div style="height: 220px; display: flex; align-items: center; justify-content: center; position: relative;">
                <canvas id="customerStatusChart" style="max-height: 180px; max-width: 180px;"></canvas>
            </div>
        </div>

        <!-- Card 3: Laporan Gangguan -->
        <div class="card">
            <div class="card-header-custom">
                <div class="card-title-custom">
                    <h3>Laporan Gangguan</h3>
                    <span>Status tiket gangguan dari portal pelanggan.</span>
                </div>
                <a href="{{ route('admin.keluhan.index') }}" class="btn-xs" style="text-decoration: none; border: 1px solid var(--border-color); padding: 4px 8px; border-radius: 8px; font-size: 0.75rem; font-weight: 600; color: var(--text-dark); background-color: #f8fafc;">Lihat semua</a>
            </div>
            
            <div class="block-grid-2x2">
                <div class="block-box block-box-blue">
                    <span class="block-val">{{ $keluhanTotal }}</span>
                    <span class="block-lbl">Total Tiket</span>
                </div>
                <div class="block-box block-box-green">
                    <span class="block-val">{{ $keluhanMenunggu }}</span>
                    <span class="block-lbl">Open</span>
                </div>
                <div class="block-box block-box-orange">
                    <span class="block-val">{{ $keluhanProses }}</span>
                    <span class="block-lbl">Ditangani</span>
                </div>
                <div class="block-box block-box-red">
                    <span class="block-val">{{ $keluhanSelesai }}</span>
                    <span class="block-lbl">Selesai</span>
                </div>
            </div>
            <small style="color: var(--text-gray); font-size: 0.75rem; text-align: center; margin-top: 4px;">Data gangguan terbaru.</small>
        </div>
    </div>
    @endif

    <!-- Row 2: Status Jaringan & Port -->
    @if(in_array(Auth::user()->level, ['admin', 'teknisi', 'kasir']))
    <div class="dashboard-row-2 {{ in_array(Auth::user()->level, ['teknisi', 'kasir']) ? 'row-teknisi' : '' }}">
        <!-- Card 4: Status pembayaran -->
        @if(in_array(Auth::user()->level, ['admin', 'kasir']))
        <div class="card">
            <div class="card-header-custom">
                <div class="card-title-custom">
                    <h3>Status pembayaran</h3>
                    <span>Lunas: {{ $suksesBayarCount }} • Belum Bayar: {{ $belumTerbayarCount }}</span>
                </div>
                <span class="badge-pill badge-financial">Pembayaran</span>
            </div>
            
            <div class="isolation-grid">
                <div class="block-box block-box-red">
                    <span class="block-val">{{ $belumTerbayarCount }}</span>
                    <span class="block-lbl">Belum Terbayar</span>
                </div>
                <div class="block-box block-box-green">
                    <span class="block-val">{{ $suksesBayarCount }}</span>
                    <span class="block-lbl">Sukses Bayar</span>
                </div>
                <div class="block-box block-box-orange isolation-box-full">
                    <span class="block-val">{{ $bukaSementaraCount }}</span>
                    <span class="block-lbl">Buka Sementara</span>
                </div>
            </div>
        </div>
        @endif

        <!-- Card 5: Status Mikrotik -->
        @if(Auth::user()->level === 'admin')
        <div class="card">
            <div class="card-header-custom">
                <div class="card-title-custom">
                    <h3>Status Mikrotik</h3>
                    <span>Pantau perangkat RouterOS dan konektivitas API.</span>
                </div>
                <span class="badge-pill badge-stabil">Stabil</span>
            </div>
            
            <div class="mikrotik-grid">
                <div class="block-box block-box-blue" style="padding: 10px;">
                    <span class="block-val" style="font-size: 1.25rem;">{{ $totalMikrotik }}</span>
                    <span class="block-lbl" style="font-size: 0.62rem;">Total Perangkat</span>
                </div>
                <div class="block-box block-box-green" style="padding: 10px;">
                    <span class="block-val" style="font-size: 1.25rem;">{{ $mikrotikOnline }}</span>
                    <span class="block-lbl" style="font-size: 0.62rem;">Online</span>
                </div>
                <div class="block-box block-box-red" style="padding: 10px;">
                    <span class="block-val" style="font-size: 1.25rem;">{{ $mikrotikOffline }}</span>
                    <span class="block-lbl" style="font-size: 0.62rem;">Offline</span>
                </div>
            </div>
            
            <small style="color: var(--text-gray); font-size: 0.72rem; margin-top: 4px;">Terakhir cek: {{ date('d M Y, H:i') }}</small>
            
            <div style="max-height: 110px; overflow-y: auto;">
                @foreach($deviceList as $dev)
                    <div class="device-row">
                        <div class="device-info">
                            <span class="device-name">{{ $dev->nama }}</span>
                            <span class="device-ip">{{ $dev->ip }}</span>
                        </div>
                        <div class="device-status">
                            <span class="status-dot {{ $dev->online ? 'dot-online' : 'dot-offline' }}"></span>
                            <span class="{{ $dev->online ? 'text-online' : 'text-offline' }}">{{ $dev->online ? 'Online' : 'Offline' }}</span>
                        </div>
                        <span class="device-time">Terhubung: {{ $dev->waktu }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Card 6: Infrastruktur ODC / ODP -->
        @if(in_array(Auth::user()->level, ['admin', 'teknisi']))
        <div class="card">
            <div class="card-header-custom">
                <div class="card-title-custom">
                    <h3>Infrastruktur ODC / ODP</h3>
                    <span>Status distribusi titik jaringan dan pemakaian port.</span>
                </div>
                <span class="badge-pill badge-live">Live</span>
            </div>
            
            <div class="infra-grid">
                <div class="block-box block-box-blue" style="padding: 8px;">
                    <span class="block-val" style="font-size: 1.1rem;">{{ $totalOdc }}</span>
                    <span class="block-lbl" style="font-size: 0.58rem;">Total ODC</span>
                </div>
                <div class="block-box block-box-green" style="padding: 8px;">
                    <span class="block-val" style="font-size: 1.1rem;">{{ $totalOdp }}</span>
                    <span class="block-lbl" style="font-size: 0.58rem;">Total ODP</span>
                </div>
                <div class="block-box block-box-orange" style="padding: 8px;">
                    <span class="block-val" style="font-size: 1.1rem;">{{ $portTerpakai }}</span>
                    <span class="block-lbl" style="font-size: 0.58rem;">Port Terpakai</span>
                </div>
                <div class="block-box block-box-cyan" style="padding: 8px;">
                    <span class="block-val" style="font-size: 1.1rem;">{{ $portTersedia }}</span>
                    <span class="block-lbl" style="font-size: 0.58rem;">Port Tersedia</span>
                </div>
            </div>
            
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-top: 6px; border-top: 1px solid #f1f5f9; padding-top: 10px;">
                <div style="display: flex; flex-direction: column; gap: 8px; font-size: 0.8rem; color: var(--text-dark); font-weight: 500;">
                    <span><strong>Distribusi Port</strong></span>
                    <span style="display: flex; align-items: center; gap: 6px;"><span style="width: 8px; height: 8px; border-radius: 50%; background-color: #06b6d4;"></span> Tersedia</span>
                    <span style="display: flex; align-items: center; gap: 6px;"><span style="width: 8px; height: 8px; border-radius: 50%; background-color: #f97316;"></span> Terpakai</span>
                </div>
                <div style="height: 100px; width: 100px; position: relative;">
                    <canvas id="portDistributionChart" style="max-height: 90px; max-width: 90px;"></canvas>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif
@endif

<!-- Row 3: Activities & Transactions -->
@if(in_array(Auth::user()->level, ['admin', 'mitra', 'kasir']))
<div class="dashboard-row-3 {{ in_array(Auth::user()->level, ['kasir', 'mitra']) ? 'row-kasir' : '' }}">
    <!-- Card 7: Aktivitas Terbaru -->
    @if(Auth::user()->level === 'admin')
    <div class="card">
        <div class="card-header-custom">
            <div class="card-title-custom">
                <h3>Aktivitas Terbaru</h3>
                <span>Log isolasi, restore dan aksi sistem terakhir.</span>
            </div>
            <span class="badge-pill badge-realtime">Real-time</span>
        </div>
        
        <div class="table-container" style="max-height: 380px; overflow-y: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>WAKTU</th>
                        <th>CUSTOMER</th>
                        <th>AKSI</th>
                        <th>STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($aktivitasTerbaru as $act)
                        <tr>
                            <td>{{ $act->waktu }}</td>
                            <td><strong>{{ $act->customer }}</strong></td>
                            <td>
                                <span class="badge-act {{ $act->aksi === 'ISOLATE' ? 'badge-isolate' : 'badge-restore' }}">
                                    {{ $act->aksi }}
                                </span>
                            </td>
                            <td class="text-success">{{ $act->status }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-gray); padding: 30px;">
                                Belum ada log aktivitas sistem hari ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Card 8: Transaksi Terbaru -->
    <div class="card">
        <div class="card-header-custom">
            <div class="card-title-custom">
                <h3><i class="fa-solid fa-clock-rotate-left" style="color: var(--text-gray); margin-right: 6px;"></i> Transaksi Terbaru</h3>
                <span>Riwayat pembayaran tagihan masuk terkini.</span>
            </div>
            <span class="badge-pill badge-live">LATEST</span>
        </div>
        
        <div class="table-container" style="max-height: 380px; overflow-y: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Pelanggan</th>
                        <th>Bulan/Tahun</th>
                        <th>Tagihan</th>
                        <th>Waktu Pembayaran</th>
                        <th>Metode / Kasir</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksiTerbaru as $index => $tx)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><strong>{{ $tx->pelanggan->nama_pelanggan ?? 'N/A' }}</strong></td>
                            <td>{{ substr($tx->bulan_tahun, 0, 2) . '-' . substr($tx->bulan_tahun, 2) }}</td>
                            <td>Rp {{ number_format($tx->jml_bayar, 0, ',', '.') }}</td>
                            <td>{{ $tx->waktu_bayar ?? '-' }}</td>
                            <td>
                                @if(is_null($tx->user_id))
                                    <span class="badge-payment badge-midtrans">MIDTRANS GATEWAY</span>
                                @else
                                    <span class="badge-payment badge-kasir">{{ $tx->penerima->nama_user ?? 'KASIR LAPANGAN' }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-gray); padding: 30px;">
                                Belum ada riwayat transaksi pembayaran masuk.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<!-- Load Chart.js from CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // 1. Chart Tren Pemasukan & Pengeluaran (Line Chart)
        const canvasFinancial = document.getElementById('financialChart');
        if (canvasFinancial) {
            const ctxFinancial = canvasFinancial.getContext('2d');
            const financialData = @json($keuangan6Bulan);
            
            const labels = financialData.map(item => item.label);
            const pemasukanData = financialData.map(item => item.pemasukan);
            const pengeluaranData = financialData.map(item => item.pengeluaran);

            new Chart(ctxFinancial, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Pemasukan',
                            data: pemasukanData,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 3,
                            pointRadius: 4,
                            pointBackgroundColor: '#10b981'
                        },
                        {
                            label: 'Pengeluaran',
                            data: pengeluaranData,
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.05)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: 3,
                            pointBackgroundColor: '#ef4444'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                boxWidth: 15,
                                font: {
                                    size: 11,
                                    family: 'Inter'
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f1f5f9'
                            },
                            ticks: {
                                font: {
                                    size: 10,
                                    family: 'Inter'
                                },
                                callback: function(value) {
                                    if (value >= 1000000) return 'Rp ' + (value / 1000000) + 'jt';
                                    if (value >= 1000) return 'Rp ' + (value / 1000) + 'rb';
                                    return 'Rp ' + value;
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 10,
                                    family: 'Inter'
                                }
                            }
                        }
                    }
                }
            });
        }

        // 2. Chart Status Pelanggan (Donut Chart)
        const canvasCustomer = document.getElementById('customerStatusChart');
        if (canvasCustomer) {
            const ctxCustomer = canvasCustomer.getContext('2d');
            new Chart(ctxCustomer, {
                type: 'doughnut',
                data: {
                    labels: ['Aktif', 'Terisolir', 'Non-aktif'],
                    datasets: [{
                        data: [{{ $aktifCount }}, {{ $terisolirCount }}, {{ $nonaktifCount }}],
                        backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                        borderWidth: 2,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                boxWidth: 10,
                                padding: 10,
                                font: {
                                    size: 10,
                                    family: 'Inter'
                                }
                            }
                        }
                    },
                    cutout: '70%'
                }
            });
        }

        // 3. Chart Port Distribusi (Donut Chart)
        const canvasPort = document.getElementById('portDistributionChart');
        if (canvasPort) {
            const ctxPort = canvasPort.getContext('2d');
            new Chart(ctxPort, {
                type: 'doughnut',
                data: {
                    labels: ['Tersedia', 'Terpakai'],
                    datasets: [{
                        data: [{{ $portTersedia }}, {{ $portTerpakai }}],
                        backgroundColor: ['#06b6d4', '#f97316'],
                        borderWidth: 1,
                        hoverOffset: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    cutout: '65%'
                }
            });
        }
    });
</script>

@if($showLicenseWarning)
<!-- Modal Warning Jatuh Tempo Lisensi -->
<div class="modal fade" id="licenseWarningModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border: none; border-radius: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); background: white;">
            <div class="modal-body text-center p-5">
                <div class="d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px; background-color: #fffbeb; color: #d97706; border-radius: 20px; font-size: 2.5rem; box-shadow: 0 10px 15px -3px rgba(217, 119, 6, 0.1);">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h4 class="fw-bold mb-2 text-dark" style="font-family: 'Outfit', sans-serif;">Peringatan Lisensi!</h4>
                <p class="text-muted mb-4" style="font-size: 0.95rem; line-height: 1.5;">
                    Masa berlaku lisensi program billing internet Anda akan segera habis dalam waktu <b>{{ $licenseDaysRemaining }} hari</b> lagi. Harap segera lakukan perpanjangan lisensi agar operasional billing tetap berjalan normal.
                </p>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light w-100 py-2.5 rounded-3 fw-semibold" data-bs-dismiss="modal" style="font-size: 0.9rem; border: 1px solid #e2e8f0;">Nanti Saja</button>
                    <a href="{{ route('admin.pengaturan.index') }}" class="btn btn-warning w-100 py-2.5 rounded-3 text-white fw-semibold" style="font-size: 0.9rem; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border: none; display: flex; align-items: center; justify-content: center; gap: 6px; text-decoration: none;">
                        Perbarui Lisensi <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var myModal = new bootstrap.Modal(document.getElementById('licenseWarningModal'));
        myModal.show();
    });
</script>
@endif
@endsection
