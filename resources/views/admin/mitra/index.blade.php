@extends('layouts.admin')

@section('title', $isAdmin ? 'Mitra' : 'Dashboard Mitra')

@section('styles')
<style>
    .mitra-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }
    .stat-card {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        background-color: #eff6ff;
        color: #2563eb;
    }
    .stat-info {
        display: flex;
        flex-direction: column;
    }
    .stat-label {
        font-size: 0.8rem;
        color: var(--text-gray);
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .stat-value {
        font-size: 1.35rem;
        font-weight: 700;
        color: #1e293b;
        margin-top: 4px;
        font-family: 'Outfit', sans-serif;
    }
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-bottom: 16px;
    }
    .form-control {
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 8px 12px;
        font-size: 0.9rem;
        outline: none;
        background-color: white;
    }
    .badge-komisi {
        background-color: #f1f5f9;
        color: #334155;
        padding: 4px 8px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.82rem;
        border: 1px solid #e2e8f0;
    }

    /* Modal Styling */
    .modal {
        display: none;
        position: fixed;
        inset: 0;
        background-color: rgba(15, 23, 42, 0.5);
        z-index: 10000;
        align-items: center;
        justify-content: center;
        padding: 20px;
        backdrop-filter: blur(4px);
    }
    .modal.active {
        display: flex;
    }
    .modal-content {
        background-color: white;
        border-radius: 24px;
        width: min(450px, 100%);
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        border: 1px solid var(--border-color);
        animation: modalFadeIn 0.3s ease;
        display: flex;
        flex-direction: column;
    }
    .modal-header {
        background: var(--primary-gradient);
        color: white;
        padding: 20px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .modal-header h3 {
        font-family: 'Outfit', sans-serif;
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0;
    }
    .modal-close {
        background: none;
        border: none;
        color: white;
        font-size: 1.25rem;
        cursor: pointer;
        opacity: 0.8;
        padding: 0;
        line-height: 1;
    }
    .modal-close:hover {
        opacity: 1;
    }
    .modal-body {
        padding: 24px;
    }
    /* Button Styling */
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
    .btn-secondary {
        background-color: #e2e8f0;
        color: #334155;
    }
    .btn-secondary:hover {
        background-color: #cbd5e1;
    }
    .btn-info {
        background-color: #eff6ff;
        color: #2563eb;
    }
    .btn-info:hover {
        background-color: #dbeafe;
    }
    .btn-danger {
        background-color: #fef2f2;
        color: #dc2626;
    }
    .btn-danger:hover {
        background-color: #fee2e2;
    }
    .btn-xs {
        padding: 5px 10px !important;
        font-size: 0.8rem !important;
        border-radius: 8px !important;
    }
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div class="card-title">
            <i class="fa-solid fa-handshake" style="color: #4f46e5; margin-right: 6px;"></i>
            {{ $isAdmin ? 'Mitra' : 'Dashboard Mitra' }}
        </div>
        <div>
            <a href="{{ route('admin.mitra.laporan') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 10px; font-size: 0.85rem; font-weight: 600; text-decoration:none; color:white; background: var(--primary-gradient);">
                <i class="fa-solid fa-chart-line"></i> Laporan Bulanan & Tahunan
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin: 16px 24px; padding: 12px 16px; border-radius: 8px; background-color: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; display:flex; align-items:center; gap:8px;">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="card-body" style="padding: 24px;">
        @if($isAdmin)
            <!-- ================= ADMIN DASHBOARD ================= -->
            <div style="font-size: 0.9rem; color: var(--text-gray); margin-bottom: 20px;">
                Kelola pembagian hasil kerja sama dari masing-masing user berlevel **Mitra** berdasarkan wilayah cakupan (Branch & Sub-Branch) yang diizinkan.
            </div>

            <div style="overflow-x: auto;">
                <table class="table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #f8fafc; border-bottom: 1px solid var(--border-color);">
                            <th style="padding: 14px; text-align: left;">Nama Mitra</th>
                            <th style="padding: 14px; text-align: left;">Username</th>
                            <th style="padding: 14px; text-align: left;">Tipe Komisi</th>
                            <th style="padding: 14px; text-align: left;">Nilai Tarif Komisi</th>
                            <th style="padding: 14px; text-align: center;">Jumlah Pelanggan</th>
                            <th style="padding: 14px; text-align: right;">Total Akumulasi Komisi</th>
                            <th style="padding: 14px; text-align: right;">Saldo Belum Dibayar</th>
                            <th style="padding: 14px; text-align: center; width: 320px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mitras as $mitra)
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 14px;"><strong>{{ $mitra->nama_user }}</strong></td>
                                <td style="padding: 14px;"><code>{{ $mitra->username }}</code></td>
                                <td style="padding: 14px;">
                                    @if($mitra->config)
                                        <span class="badge" style="background-color: {{ $mitra->config->tipe_komisi == 'flat' ? '#eff6ff' : '#faf5ff' }}; color: {{ $mitra->config->tipe_komisi == 'flat' ? '#2563eb' : '#7c3aed' }}; padding: 4px 8px; border-radius: 6px; font-weight:600; font-size: 0.78rem;">
                                            {{ $mitra->config->tipe_komisi == 'flat' ? 'Rupiah (Flat)' : 'Persentase (%)' }}
                                        </span>
                                    @else
                                        <span style="color:#94a3b8; font-style:italic; font-size:0.85rem;">Belum Diatur</span>
                                    @endif
                                </td>
                                <td style="padding: 14px;">
                                    @if($mitra->config)
                                        <strong style="color: #334155;">
                                            {{ $mitra->config->tipe_komisi == 'flat' ? 'Rp ' . number_format($mitra->config->nilai_komisi, 0, ',', '.') : $mitra->config->nilai_komisi . '%' }}
                                        </strong>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td style="padding: 14px; text-align: center;">
                                    <span class="badge" style="background-color:#f1f5f9; color:#475569; padding:4px 8px; border-radius:6px; font-weight:600;">
                                        {{ $mitra->customer_count }} Pelanggan
                                    </span>
                                </td>
                                <td style="padding: 14px; text-align: right; font-weight: 700; color: #16a34a; font-family: 'Outfit', sans-serif;">
                                    Rp {{ number_format($mitra->total_komisi, 0, ',', '.') }}
                                </td>
                                <td style="padding: 14px; text-align: right; font-weight: 700; color: {{ $mitra->saldo_belum_dibayar > 0 ? '#dc2626' : '#16a34a' }}; font-family: 'Outfit', sans-serif;">
                                    Rp {{ number_format($mitra->saldo_belum_dibayar, 0, ',', '.') }}
                                </td>
                                <td style="padding: 14px; text-align: center; display: flex; gap: 8px; justify-content: center;">
                                    <button class="btn btn-secondary btn-xs" style="padding: 5px 10px; font-size: 0.8rem;" 
                                        onclick="openModalConfig('{{ $mitra->id }}', '{{ $mitra->nama_user }}', '{{ $mitra->config->tipe_komisi ?? 'flat' }}', '{{ $mitra->config->nilai_komisi ?? 0 }}')">
                                        <i class="fa-solid fa-sliders"></i> Atur Komisi
                                    </button>
                                    <button class="btn btn-primary btn-xs" style="padding: 5px 10px; font-size: 0.8rem;" 
                                        onclick="openModalPayout('{{ $mitra->id }}', '{{ $mitra->nama_user }}', {{ json_encode($mitraMonthlyData[$mitra->id] ?? []) }})">
                                        <i class="fa-solid fa-wallet"></i> Bayar Payout
                                    </button>
                                    <a href="{{ route('admin.mitra.laporan', ['id_user' => $mitra->id]) }}" class="btn btn-info btn-xs" style="padding: 5px 10px; font-size: 0.8rem; text-decoration:none;">
                                        <i class="fa-solid fa-list-check"></i> Detail Laporan
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; color: var(--text-gray); padding: 40px;">
                                    <i class="fa-solid fa-user-slash" style="font-size: 2.5rem; margin-bottom: 12px; color: #cbd5e1;"></i>
                                    <p>Belum ada data user dengan level **Mitra**.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        @else
            <!-- ================= MITRA DASHBOARD ================= -->
            <div class="mitra-stats">
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: #f5f3ff; color: #7c3aed;">
                        <i class="fa-solid fa-handshake-angle"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Tarif Bagi Hasil Anda</span>
                        <span class="stat-value">
                            @if($config)
                                {{ $config->tipe_komisi == 'flat' ? 'Rp ' . number_format($config->nilai_komisi, 0, ',', '.') : $config->nilai_komisi . '%' }}
                            @else
                                <span style="font-size:0.85rem; color:#94a3b8; font-weight:normal;">Belum Ditetapkan</span>
                            @endif
                        </span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background-color: #eff6ff; color: #2563eb;">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Pelanggan Aktif Anda</span>
                        <span class="stat-value">{{ $pelanggans->total() }} Client</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background-color: #ecfdf5; color: #059669;">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Komisi Bulan Ini</span>
                        <span class="stat-value" style="color: #059669;">Rp {{ number_format($komisi_bulan_ini, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background-color: #fff7ed; color: #ea580c;">
                        <i class="fa-solid fa-piggy-bank"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Akumulasi Komisi</span>
                        <span class="stat-value" style="color: #ea580c;">Rp {{ number_format($total_komisi, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background-color: #fef2f2; color: #dc2626;">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Saldo Belum Dibayar</span>
                        <span class="stat-value" style="color: #dc2626;">Rp {{ number_format($saldo_belum_dibayar, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- TABEL 1: Pelanggan di Wilayah Kerja Mitra -->
            <div style="margin-top: 32px; margin-bottom: 32px;">
                <h4 style="font-family: 'Outfit', sans-serif; font-weight: 700; color: #334155; margin-bottom: 14px; font-size: 1.1rem; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-location-dot" style="color:#8b5cf6;"></i> Daftar Pelanggan di Wilayah Kerja Anda
                </h4>
                <div style="overflow-x: auto; border: 1px solid var(--border-color); border-radius: 12px; background: white;">
                    <table class="table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background-color: #f8fafc; border-bottom: 1px solid var(--border-color);">
                                <th style="padding: 12px 14px; text-align: left;">Nama Pelanggan</th>
                                <th style="padding: 12px 14px; text-align: left;">Alamat</th>
                                <th style="padding: 12px 14px; text-align: left;">Branch</th>
                                <th style="padding: 12px 14px; text-align: left;">Sub-Branch</th>
                                <th style="padding: 12px 14px; text-align: center;">IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pelanggans as $p)
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <td style="padding: 12px 14px;"><strong>{{ $p->nama_pelanggan }}</strong></td>
                                    <td style="padding: 12px 14px; font-size: 0.85rem; color: #475569;">{{ $p->alamat ?? '-' }}</td>
                                    <td style="padding: 12px 14px;"><span style="color:#2563eb; font-weight:600; font-size:0.85rem;">{{ $p->nama_branch ?? '-' }}</span></td>
                                    <td style="padding: 12px 14px;"><span style="color:#7c3aed; font-weight:600; font-size:0.85rem;">{{ $p->nama_sub_branch ?? '-' }}</span></td>
                                    <td style="padding: 12px 14px; text-align: center;"><code>{{ $p->ip_address ?? '-' }}</code></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--text-gray); padding: 30px; font-style: italic;">
                                        Tidak ada pelanggan aktif di dalam wilayah kerja Anda saat ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($pelanggans->total() > 0)
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; flex-wrap: wrap; gap: 12px;">
                        <div style="font-size: 0.85rem; color: var(--text-gray);">
                            Menampilkan {{ $pelanggans->firstItem() ?? 0 }} - {{ $pelanggans->lastItem() ?? 0 }} dari {{ $pelanggans->total() }} Pelanggan
                        </div>
                        @if($pelanggans->lastPage() > 1)
                            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                @if($pelanggans->onFirstPage())
                                    <span style="padding: 8px 12px; border-radius: 8px; background: #f1f5f9; color: #94a3b8; font-size: 0.85rem; font-weight: 600; cursor: not-allowed;"><i class="fa-solid fa-angle-left"></i></span>
                                @else
                                    <a href="{{ $pelanggans->previousPageUrl() }}" style="padding: 8px 12px; border-radius: 8px; background: #e2e8f0; color: #334155; font-size: 0.85rem; text-decoration: none; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.background='#cbd5e1'" onmouseout="this.style.background='#e2e8f0'"><i class="fa-solid fa-angle-left"></i></a>
                                @endif

                                @php
                                    $start = max(1, $pelanggans->currentPage() - 2);
                                    $end = min($pelanggans->lastPage(), $pelanggans->currentPage() + 2);
                                @endphp

                                @if($start > 1)
                                    <a href="{{ $pelanggans->url(1) }}" style="padding: 8px 14px; border-radius: 8px; background: #e2e8f0; color: #334155; font-size: 0.85rem; text-decoration: none; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.background='#cbd5e1'" onmouseout="this.style.background='#e2e8f0'">1</a>
                                    @if($start > 2)
                                        <span style="color: var(--text-gray); padding: 0 4px;">...</span>
                                    @endif
                                @endif

                                @for($p = $start; $p <= $end; $p++)
                                    @if($p == $pelanggans->currentPage())
                                        <span style="padding: 8px 14px; border-radius: 8px; background: var(--primary-gradient); color: white; font-size: 0.85rem; font-weight: 700; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.2);">{{ $p }}</span>
                                    @else
                                        <a href="{{ $pelanggans->url($p) }}" style="padding: 8px 14px; border-radius: 8px; background: #e2e8f0; color: #334155; font-size: 0.85rem; text-decoration: none; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.background='#cbd5e1'" onmouseout="this.style.background='#e2e8f0'">{{ $p }}</a>
                                    @endif
                                @endfor

                                @if($end < $pelanggans->lastPage())
                                    @if($end < $pelanggans->lastPage() - 1)
                                        <span style="color: var(--text-gray); padding: 0 4px;">...</span>
                                    @endif
                                    <a href="{{ $pelanggans->url($pelanggans->lastPage()) }}" style="padding: 8px 14px; border-radius: 8px; background: #e2e8f0; color: #334155; font-size: 0.85rem; text-decoration: none; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.background='#cbd5e1'" onmouseout="this.style.background='#e2e8f0'">{{ $pelanggans->lastPage() }}</a>
                                @endif

                                @if($pelanggans->hasMorePages())
                                    <a href="{{ $pelanggans->nextPageUrl() }}" style="padding: 8px 12px; border-radius: 8px; background: #e2e8f0; color: #334155; font-size: 0.85rem; text-decoration: none; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.background='#cbd5e1'" onmouseout="this.style.background='#e2e8f0'"><i class="fa-solid fa-angle-right"></i></a>
                                @else
                                    <span style="padding: 8px 12px; border-radius: 8px; background: #f1f5f9; color: #94a3b8; font-size: 0.85rem; font-weight: 600; cursor: not-allowed;"><i class="fa-solid fa-angle-right"></i></span>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- TABEL 2: 10 Riwayat Pembayaran Komisi Terbaru -->
            <div style="margin-bottom: 32px;">
                <h4 style="font-family: 'Outfit', sans-serif; font-weight: 700; color: #334155; margin-bottom: 14px; font-size: 1.1rem; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-clock-rotate-left" style="color:#10b981;"></i> 10 Riwayat Penerimaan Komisi Terbaru
                </h4>
                <div style="overflow-x: auto; border: 1px solid var(--border-color); border-radius: 12px; background: white;">
                    <table class="table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background-color: #f8fafc; border-bottom: 1px solid var(--border-color);">
                                <th style="padding: 12px 14px; text-align: left;">Tanggal</th>
                                <th style="padding: 12px 14px; text-align: left;">Pelanggan</th>
                                <th style="padding: 12px 14px; text-align: right;">Jumlah Bayar</th>
                                <th style="padding: 12px 14px; text-align: left;">Tarif Komisi</th>
                                <th style="padding: 12px 14px; text-align: right;">Komisi Diterima</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent_logs as $log)
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <td style="padding: 12px 14px; font-size:0.85rem; color:#64748b;">
                                        {{ \Carbon\Carbon::parse($log->created_at)->translatedFormat('d F Y H:i') }}
                                    </td>
                                    <td style="padding: 12px 14px;"><strong>{{ $log->nama_pelanggan }}</strong></td>
                                    <td style="padding: 12px 14px; text-align: right;">Rp {{ number_format($log->jumlah_bayar, 0, ',', '.') }}</td>
                                    <td style="padding: 12px 14px;">
                                        <span class="badge-komisi">
                                            {{ $log->tipe_komisi == 'flat' ? 'Rp ' . number_format($log->nilai_komisi, 0, ',', '.') : $log->nilai_komisi . '%' }}
                                        </span>
                                    </td>
                                    <td style="padding: 12px 14px; text-align: right; font-weight:700; color: #16a34a;">
                                        + Rp {{ number_format($log->komisi_diterima, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--text-gray); padding: 30px; font-style: italic;">
                                        Belum ada riwayat penerimaan komisi bagi hasil.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TABEL 3: Riwayat Pembayaran Payout -->
            <div>
                <h4 style="font-family: 'Outfit', sans-serif; font-weight: 700; color: #334155; margin-bottom: 14px; font-size: 1.1rem; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-file-shield" style="color:#d97706;"></i> Riwayat Pembayaran Payout Anda
                </h4>
                <div style="overflow-x: auto; border: 1px solid var(--border-color); border-radius: 12px; background: white;">
                    <table class="table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background-color: #f8fafc; border-bottom: 1px solid var(--border-color);">
                                <th style="padding: 12px 14px; text-align: left;">Tanggal Bayar</th>
                                <th style="padding: 12px 14px; text-align: right;">Jumlah Payout</th>
                                <th style="padding: 12px 14px; text-align: left;">Catatan / Deskripsi</th>
                                <th style="padding: 12px 14px; text-align: center;">Bukti Transfer</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payout_logs as $pLog)
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <td style="padding: 12px 14px; font-size:0.85rem; color:#64748b;">
                                        {{ \Carbon\Carbon::parse($pLog->tgl_payout)->translatedFormat('d F Y') }}
                                    </td>
                                    <td style="padding: 12px 14px; text-align: right; font-weight:700; color:#2563eb;">
                                        Rp {{ number_format($pLog->jumlah, 0, ',', '.') }}
                                    </td>
                                    <td style="padding: 12px 14px; font-size:0.85rem; color:#475569;">
                                        {{ $pLog->catatan ?? '-' }}
                                    </td>
                                    <td style="padding: 12px 14px; text-align: center;">
                                        @if($pLog->bukti_transfer)
                                            <a href="{{ asset('uploads/mitra_payouts/' . $pLog->bukti_transfer) }}" target="_blank" class="btn btn-info btn-xs" style="padding: 4px 8px; font-size: 0.75rem;">
                                                <i class="fa-solid fa-receipt"></i> Lihat Bukti
                                            </a>
                                        @else
                                            <span style="color:#94a3b8; font-style:italic;">Tidak Ada</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align: center; color: var(--text-gray); padding: 30px; font-style: italic;">
                                        Belum ada riwayat pembayaran payout kepada Anda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        @endif
    </div>
</div>

@if($isAdmin)
    <!-- MODAL: ATUR CONFIG BAGI HASIL MITRA -->
    <div class="modal" id="modalConfigMitra" style="z-index: 10005;">
        <div class="modal-content" style="width: min(450px, 100%);">
            <div class="modal-header" style="background: var(--primary-gradient); color: white;">
                <h3>Konfigurasi Komisi Mitra</h3>
                <button class="modal-close" onclick="closeModalConfig()" style="color: white; border: none; background: none; font-size: 1.2rem; cursor: pointer;">&times;</button>
            </div>
            <form action="{{ route('admin.mitra.update_config') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding: 20px;">
                    <input type="hidden" name="id_user" id="config_id_user">
                    
                    <div class="form-group">
                        <label>Nama Mitra</label>
                        <input type="text" id="config_nama_user" class="form-control" readonly style="background-color: #f1f5f9;">
                    </div>

                    <div class="form-group">
                        <label for="tipe_komisi">Tipe Bagi Hasil / Komisi *</label>
                        <select name="tipe_komisi" id="config_tipe_komisi" class="form-control" required onchange="toggleTipeSuffix(this.value)">
                            <option value="flat">Nominal Rupiah (Flat per Pelanggan)</option>
                            <option value="persentase">Persentase (%) dari Total Bayar Paket</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="nilai_komisi" id="label_nilai_komisi">Nilai Komisi *</label>
                        <div style="position: relative;">
                            <input type="number" step="0.01" name="nilai_komisi" id="config_nilai_komisi" class="form-control" required style="width: 100%; padding-right: 40px;">
                            <span id="nilai_suffix" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-weight: 600; color: #475569;">Rp</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 16px 20px; display: flex; justify-content: flex-end; gap: 8px; border-top: 1px solid var(--border-color);">
                    <button type="button" class="btn btn-secondary" onclick="closeModalConfig()">Batal</button>
                    <button type="submit" class="btn btn-primary" style="background: var(--primary-gradient); color: white;">Simpan Pengaturan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: INPUT PEMBAYARAN PAYOUT MITRA -->
    <div class="modal" id="modalPayoutMitra" style="z-index: 10005;">
        <div class="modal-content" style="width: min(480px, 100%);">
            <div class="modal-header" style="background: var(--primary-gradient); color: white;">
                <h3>Catat Pembayaran Payout Mitra</h3>
                <button class="modal-close" onclick="closeModalPayout()" style="color: white; border: none; background: none; font-size: 1.25rem; cursor: pointer; padding:0; line-height:1;">&times;</button>
            </div>
            <form action="{{ route('admin.mitra.store_payout') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body" style="padding: 20px;">
                    <input type="hidden" name="id_user" id="payout_id_user">
                    
                    <div class="form-group">
                        <label>Nama Mitra</label>
                        <input type="text" id="payout_nama_user" class="form-control" readonly style="background-color: #f1f5f9;">
                    </div>

                    <div class="form-group">
                        <label for="payout_month_year">Bulan & Tahun Payout *</label>
                        <select name="payout_month_year" id="payout_month_year" class="form-control" required onchange="onPayoutMonthChange(this)">
                            <!-- Populated dynamically via JS -->
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="jumlah">Jumlah Pembayaran Payout (Rp) *</label>
                        <input type="number" name="jumlah" id="payout_jumlah" class="form-control" required min="1" readonly style="background-color: #f1f5f9;">
                        <small style="color: #64748b; margin-top:2px;" id="payout_info_saldo">Pilih bulan untuk melihat besaran komisi.</small>
                    </div>

                    <div class="form-group">
                        <label for="tgl_payout">Tanggal Bayar *</label>
                        <input type="date" name="tgl_payout" id="payout_tgl_payout" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>

                    <div class="form-group">
                        <label for="catatan">Catatan / Keterangan Transfer</label>
                        <textarea name="catatan" id="payout_catatan" class="form-control" rows="3" placeholder="Contoh: Transfer ke Mandiri rek 123456789 AN Mitra"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="bukti_transfer">Upload Bukti Transfer *</label>
                        <input type="file" name="bukti_transfer" id="payout_bukti_transfer" class="form-control" required accept="image/*">
                        <small style="color: #e24c4c; font-weight:500; margin-top:4px;">Format gambar (PNG/JPG/JPEG). Maks 2MB.</small>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 16px 20px; display: flex; justify-content: flex-end; gap: 8px; border-top: 1px solid var(--border-color);">
                    <button type="button" class="btn btn-secondary" onclick="closeModalPayout()">Batal</button>
                    <button type="submit" class="btn btn-primary" style="background: var(--primary-gradient); color: white;">Catat Payout</button>
                </div>
            </form>
        </div>
    </div>
@endif

@endsection

@section('scripts')
@if($isAdmin)
<script>
    function openModalConfig(userId, namaUser, tipeKomisi, nilaiKomisi) {
        document.getElementById('config_id_user').value = userId;
        document.getElementById('config_nama_user').value = namaUser;
        document.getElementById('config_tipe_komisi').value = tipeKomisi;
        document.getElementById('config_nilai_komisi').value = nilaiKomisi;
        
        toggleTipeSuffix(tipeKomisi);
        document.getElementById('modalConfigMitra').classList.add('active');
    }

    function closeModalConfig() {
        document.getElementById('modalConfigMitra').classList.remove('active');
    }

    function toggleTipeSuffix(tipe) {
        const suffix = document.getElementById('nilai_suffix');
        const label = document.getElementById('label_nilai_komisi');
        if (tipe === 'flat') {
            suffix.textContent = 'Rp';
            label.textContent = 'Nominal Rupiah (Rp) *';
        } else {
            suffix.textContent = '%';
            label.textContent = 'Persentase Komisi (%) *';
        }
    }

    let currentMitraMonthlyData = [];

    function openModalPayout(userId, namaUser, monthlyDataList) {
        document.getElementById('payout_id_user').value = userId;
        document.getElementById('payout_nama_user').value = namaUser;
        
        currentMitraMonthlyData = monthlyDataList;
        
        // Populate monthly options
        const select = document.getElementById('payout_month_year');
        select.innerHTML = '<option value="">-- Pilih Bulan & Tahun --</option>';
        
        const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });
        
        monthlyDataList.forEach(data => {
            const opt = document.createElement('option');
            opt.value = data.bulan + '-' + data.tahun;
            if (data.is_paid) {
                opt.disabled = true;
                opt.textContent = data.label + ' (SUDAH DIBAYAR - BLOCKED)';
            } else {
                opt.disabled = false;
                opt.textContent = data.label + ' (Komisi: ' + formatter.format(data.komisi) + ')';
            }
            select.appendChild(opt);
        });
        
        // Reset amount and description
        document.getElementById('payout_jumlah').value = '';
        document.getElementById('payout_info_saldo').textContent = 'Pilih bulan untuk melihat besaran komisi.';
        
        document.getElementById('modalPayoutMitra').classList.add('active');
    }

    function closeModalPayout() {
        document.getElementById('modalPayoutMitra').classList.remove('active');
    }

    function onPayoutMonthChange(selectElem) {
        const val = selectElem.value;
        if (!val) {
            document.getElementById('payout_jumlah').value = '';
            document.getElementById('payout_info_saldo').textContent = 'Pilih bulan untuk melihat besaran komisi.';
            return;
        }
        
        const parts = val.split('-');
        const month = parseInt(parts[0]);
        const year = parseInt(parts[1]);
        
        const matched = currentMitraMonthlyData.find(d => d.bulan === month && d.tahun === year);
        if (matched) {
            document.getElementById('payout_jumlah').value = matched.komisi;
            const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });
            document.getElementById('payout_info_saldo').textContent = 'Besaran Komisi: ' + formatter.format(matched.komisi);
        }
    }

    // Explicitly show loading overlay on submit for config & payout forms
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('#modalConfigMitra form, #modalPayoutMitra form').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!e.defaultPrevented) {
                    const overlay = document.getElementById('global-loading-overlay');
                    if (overlay) {
                        overlay.style.display = 'flex';
                    }
                }
            });
        });
    });
</script>
@endif
@endsection
