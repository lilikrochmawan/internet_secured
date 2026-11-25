@extends('layouts.admin')

@section('title', 'Top Up Saldo Pelanggan')

@section('styles')
<style>
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        text-decoration: none;
    }
    .btn-primary {
        background: var(--primary-gradient);
        color: white;
    }
    .btn-primary:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }
    .btn-success {
        background-color: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
    }
    .btn-success:hover {
        background-color: #d1fae5;
    }
    .btn-info {
        background-color: #eff6ff;
        color: #2563eb;
        border: 1px solid #bfdbfe;
    }
    .btn-info:hover {
        background-color: #dbeafe;
    }
    .btn-secondary {
        background-color: #f1f5f9;
        color: #475569;
        border: 1px solid #cbd5e1;
    }
    .btn-secondary:hover {
        background-color: #e2e8f0;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .stat-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03);
    }
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
    }

    .table-container {
        overflow-x: auto;
        margin-top: 14px;
    }
    .table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }
    .table th, .table td {
        padding: 12px 16px;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.88rem;
    }
    .table th {
        font-weight: 600;
        color: var(--text-gray);
        background-color: #f8fafc;
    }
    .table tr:hover {
        background-color: #f8fafc;
    }

    .badge-saldo {
        font-size: 0.95rem;
        font-weight: 700;
        color: #4f46e5;
        background: #eef2ff;
        padding: 4px 10px;
        border-radius: 8px;
        border: 1px solid #c7d2fe;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .badge-tipe-masuk {
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 0.78rem;
    }
    .badge-tipe-keluar {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 0.78rem;
    }

    .quick-chip {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 8px;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        color: #334155;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s;
        margin-right: 6px;
        margin-top: 6px;
    }
    .quick-chip:hover {
        background: #e0e7ff;
        border-color: #818cf8;
        color: #4338ca;
    }

    /* Modal styling */
    .modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        align-items: center;
        justify-content: center;
        padding: 16px;
    }
    .modal.active {
        display: flex;
    }
    .modal-content {
        background-color: #fff;
        border-radius: 20px;
        width: min(580px, 100%);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        overflow: hidden;
        animation: modalSlide 0.2s ease-out;
    }
    @keyframes modalSlide {
        from { opacity: 0; transform: translateY(-20px) scale(0.96); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    .modal-header {
        padding: 18px 24px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #f8fafc;
    }
    .modal-header h3 {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .modal-close {
        background: transparent;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #64748b;
    }
    .modal-body {
        padding: 24px;
        max-height: 80vh;
        overflow-y: auto;
    }

    /* Custom Searchable Dropdown Styling */
    .custom-select-container {
        position: relative;
        width: 100%;
        user-select: none;
    }
    .custom-select-container.active {
        z-index: 100;
    }
    .custom-select-trigger {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 0 14px;
        font-size: 0.92rem;
        background-color: white;
        cursor: pointer;
        transition: border-color 0.2s, box-shadow 0.2s;
        height: 42px;
        box-sizing: border-box;
    }
    .custom-select-trigger:hover {
        border-color: #94a3b8;
    }
    .custom-select-container.active .custom-select-trigger {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
    }
    .custom-select-container.active .custom-select-trigger i.fa-chevron-down {
        transform: rotate(180deg);
    }
    .custom-select-dropdown {
        display: none;
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        background-color: white;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        box-shadow: 0 12px 28px -4px rgba(0, 0, 0, 0.15), 0 6px 12px -4px rgba(0, 0, 0, 0.08);
        z-index: 1000;
        overflow: hidden;
        animation: dropdownFade 0.15s ease-out;
    }
    @keyframes dropdownFade {
        from { opacity: 0; transform: translateY(-6px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .custom-select-container.active .custom-select-dropdown {
        display: block;
    }
    .custom-select-search-wrapper {
        position: relative;
        padding: 8px 10px;
        background-color: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    .custom-select-search-wrapper i {
        position: absolute;
        left: 22px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.82rem;
    }
    .custom-select-search-input {
        width: 100%;
        padding: 8px 12px 8px 34px !important;
        height: 36px;
        font-size: 0.88rem;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background-color: white;
        box-sizing: border-box;
    }
    .custom-select-search-input:focus {
        border-color: #4f46e5;
        outline: none;
        box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.15);
    }
    .custom-select-options {
        max-height: 220px;
        overflow-y: auto;
    }
    .custom-select-option {
        padding: 10px 14px;
        font-size: 0.88rem;
        color: #1e293b;
        cursor: pointer;
        transition: background-color 0.15s;
        text-align: left;
        border-bottom: 1px solid #f1f5f9;
    }
    .custom-select-option:last-child {
        border-bottom: none;
    }
    .custom-select-option:hover {
        background-color: #f8fafc;
    }
    .custom-select-option.selected {
        background-color: #eef2ff;
        color: #4338ca;
        font-weight: 600;
    }
</style>
@endsection

@section('content')
<!-- Ringkasan Statistik -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:#eef2ff; color:#4f46e5;">
            <i class="fa-solid fa-wallet"></i>
        </div>
        <div>
            <span style="font-size: 0.82rem; color: #64748b; font-weight: 500; display: block;">Total Saldo Mengendap</span>
            <strong style="font-size: 1.3rem; color: #1e293b; display: block;">Rp {{ number_format($totalSaldoMengendap, 0, ',', '.') }}</strong>
            <span style="font-size: 0.75rem; color: #4f46e5; font-weight: 600;">{{ $pelangganBersaldoCount }} Pelanggan Memiliki Saldo</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background:#ecfdf5; color:#059669;">
            <i class="fa-solid fa-circle-arrow-down"></i>
        </div>
        <div>
            <span style="font-size: 0.82rem; color: #64748b; font-weight: 500; display: block;">Top Up Masuk (Bulan Ini)</span>
            <strong style="font-size: 1.3rem; color: #059669; display: block;">Rp {{ number_format($totalTopupBulanIni, 0, ',', '.') }}</strong>
            <span style="font-size: 0.75rem; color: #64748b;">Penerimaan Saldo Baru</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background:#fef2f2; color:#dc2626;">
            <i class="fa-solid fa-circle-arrow-up"></i>
        </div>
        <div>
            <span style="font-size: 0.82rem; color: #64748b; font-weight: 500; display: block;">Saldo Digunakan (Bulan Ini)</span>
            <strong style="font-size: 1.3rem; color: #dc2626; display: block;">Rp {{ number_format($totalPakaiBulanIni, 0, ',', '.') }}</strong>
            <span style="font-size: 0.75rem; color: #64748b;">Pembayaran Tagihan / Mutasi</span>
        </div>
    </div>
</div>

<!-- Card 1: Daftar Saldo Pelanggan -->
<div class="card" style="margin-bottom: 24px;">
    <div class="card-header" style="flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between;">
        <div class="card-title">
            <i class="fa-solid fa-wallet" style="color: #4f46e5;"></i>
            <span>Daftar Saldo & Pengelolaan Top Up Pelanggan</span>
        </div>
        <button type="button" class="btn btn-primary" onclick="openTopupModal('', '', 0)">
            <i class="fa-solid fa-plus-circle"></i> + Top Up Saldo Pelanggan
        </button>
    </div>

    <!-- Toolbar Pencarian & Limit -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-top: 14px; margin-bottom:12px; flex-wrap:wrap; gap:12px;">
        <div style="display:flex; align-items:center; gap:8px;">
            <span style="font-size: 0.85rem; color: var(--text-gray);">Tampilkan:</span>
            <select id="tableLimit" class="form-control" style="padding: 4px 8px; height: 38px; border-radius: 8px; font-size: 0.85rem; width: auto; margin: 0;">
                <option value="10" selected>10 Baris</option>
                <option value="25">25 Baris</option>
                <option value="50">50 Baris</option>
                <option value="100">100 Baris</option>
            </select>
        </div>
        <div style="position:relative; min-width:240px;">
            <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--text-gray); font-size:0.9rem;"></i>
            <input type="text" id="tableSearch" class="form-control" placeholder="Cari nama, kode, atau telepon..." style="padding-left:36px; height:38px; border-radius:10px; width:100%;">
        </div>
    </div>

    <div class="table-container">
        <table class="table" id="saldoTable">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nama Pelanggan</th>
                    <th>Paket Internet</th>
                    <th>Nomor WhatsApp</th>
                    <th>Sisa Saldo Saat Ini</th>
                    <th style="text-align: center; width: 220px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pelangganList as $index => $p)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $p->nama_pelanggan }}</strong>
                            <div style="font-size: 0.78rem; font-family: monospace; color: #64748b;">
                                {{ $p->kode_pelanggan }}
                            </div>
                            @if(!empty($p->alamat))
                                <div style="font-size: 0.75rem; color: #94a3b8; display: flex; align-items: center; gap: 4px;">
                                    <i class="fa-solid fa-location-dot"></i> {{ $p->alamat }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="badge" style="background:#f1f5f9; color:#334155; font-weight:600;">
                                {{ $p->paketDetail->nama_paket ?? '-' }}
                            </span>
                        </td>
                        <td>
                            <span style="font-family: monospace; font-size: 0.85rem;">{{ $p->no_telp ?: '-' }}</span>
                        </td>
                        <td>
                            <div class="badge-saldo">
                                <i class="fa-solid fa-coins" style="color: #6366f1;"></i>
                                Rp {{ number_format($p->saldo ?? 0, 0, ',', '.') }}
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <div style="display: inline-flex; gap: 6px;">
                                <button type="button" class="btn btn-success" title="Top Up / Sesuaikan Saldo" onclick="openTopupModal('{{ $p->id_pelanggan }}', '{{ addslashes($p->nama_pelanggan) }} ({{ $p->kode_pelanggan }})', {{ (int)($p->saldo ?? 0) }})">
                                    <i class="fa-solid fa-money-bill-transfer"></i> Top Up
                                </button>
                                <button type="button" class="btn btn-info" title="Lihat Riwayat Mutasi Saldo" onclick="showCustomerHistory('{{ $p->id_pelanggan }}')">
                                    <i class="fa-solid fa-clock-rotate-left"></i> Riwayat
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #94a3b8; padding: 24px;">Belum ada data pelanggan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div id="saldoPagination"></div>
</div>

<!-- Card 2: Riwayat Keluar Masuk Saldo Keseluruhan -->
<div class="card">
    <div class="card-header" style="justify-content: space-between; align-items: center;">
        <div class="card-title">
            <i class="fa-solid fa-list-check" style="color: #0284c7;"></i>
            <span>Riwayat Keluar Masuk Saldo Terkini (Log Mutasi)</span>
        </div>
        <span style="font-size: 0.78rem; color: #64748b;">50 Transaksi Mutasi Terakhir</span>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Waktu / Tanggal</th>
                    <th>Pelanggan</th>
                    <th>Jenis Mutasi</th>
                    <th>Nominal</th>
                    <th>Saldo Awal &rarr; Akhir</th>
                    <th>Kategori</th>
                    <th>Keterangan</th>
                    <th>Operator</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mutasiKeseluruhan as $idx => $m)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>
                            <div style="font-weight: 600; color: #1e293b;">
                                {{ \Carbon\Carbon::parse($m->created_at)->translatedFormat('d M Y') }}
                            </div>
                            <div style="font-size: 0.75rem; color: #64748b;">
                                {{ \Carbon\Carbon::parse($m->created_at)->format('H:i:s') }} WIB
                            </div>
                        </td>
                        <td>
                            <strong>{{ $m->pelanggan->nama_pelanggan ?? 'Pelanggan #' . $m->id_pelanggan }}</strong>
                            <div style="font-size: 0.75rem; color: #64748b; font-family: monospace;">
                                {{ $m->pelanggan->kode_pelanggan ?? '-' }}
                            </div>
                        </td>
                        <td>
                            @if($m->tipe === 'masuk')
                                <span class="badge-tipe-masuk"><i class="fa-solid fa-arrow-down"></i> Saldo Masuk</span>
                            @else
                                <span class="badge-tipe-keluar"><i class="fa-solid fa-arrow-up"></i> Saldo Keluar</span>
                            @endif
                        </td>
                        <td>
                            <strong style="color: {{ $m->tipe === 'masuk' ? '#059669' : '#dc2626' }};">
                                {{ ($m->tipe === 'masuk' ? '+' : '-') }} Rp {{ number_format($m->jumlah, 0, ',', '.') }}
                            </strong>
                        </td>
                        <td>
                            <div style="font-size: 0.8rem; color: #64748b;">
                                Rp {{ number_format($m->saldo_awal, 0, ',', '.') }} &rarr; <strong style="color:#1e293b;">Rp {{ number_format($m->saldo_akhir, 0, ',', '.') }}</strong>
                            </div>
                        </td>
                        <td>
                            @php
                                $kategoriLabel = [
                                    'topup_manual' => 'Top Up Manual',
                                    'topup_midtrans' => 'Midtrans QRIS',
                                    'bayar_tagihan' => 'Bayar Tagihan',
                                    'penyesuaian' => 'Penyesuaian',
                                    'refund' => 'Refund'
                                ][$m->kategori] ?? ucfirst($m->kategori);
                            @endphp
                            <span class="badge" style="background:#f1f5f9; color:#475569; font-size:0.75rem;">
                                {{ $kategoriLabel }}
                            </span>
                        </td>
                        <td>
                            <span style="font-size: 0.82rem; color: #334155;">{{ $m->keterangan ?: '-' }}</span>
                        </td>
                        <td>
                            <span style="font-size: 0.78rem; color: #64748b;">{{ $m->creator->nama_user ?? 'Sistem / Midtrans' }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; color: #94a3b8; padding: 24px;">Belum ada riwayat mutasi saldo tercatat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal 1: Form Top Up / Penyesuaian Saldo -->
<div class="modal" id="topupModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fa-solid fa-wallet" style="color: #4f46e5;"></i> Top Up / Penyesuaian Saldo</h3>
            <button class="modal-close" onclick="closeTopupModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('admin.saldo.topup') }}" method="POST" id="formTopup">
                @csrf
                <div class="form-group" style="margin-bottom: 16px;">
                    <label style="font-weight: 600; font-size: 0.85rem; color: #334155; display: block; margin-bottom: 6px;">
                        Pilih Pelanggan *
                    </label>
                    <!-- Hidden input to store selected id_pelanggan -->
                    <input type="hidden" name="id_pelanggan" id="topup_pelanggan_id">

                    <!-- Custom Searchable Dropdown -->
                    <div class="custom-select-container" id="topup_pelanggan_select">
                        <div class="custom-select-trigger" onclick="toggleTopupSelectDropdown(event)">
                            <span id="topup_select_text" style="color: #64748b; font-size: 0.92rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">-- Cari / Pilih Pelanggan --</span>
                            <i class="fa-solid fa-chevron-down" style="font-size: 0.8rem; color: #64748b; transition: transform 0.2s;"></i>
                        </div>
                        <div class="custom-select-dropdown" id="topup_select_dropdown">
                            <div class="custom-select-search-wrapper">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" id="topup_search_pelanggan" class="form-control custom-select-search-input" placeholder="Cari nama, kode (000...), no. WA..." autocomplete="off">
                            </div>
                            <div class="custom-select-options" id="topup_select_options">
                                <div class="custom-select-option selected" data-value="" data-text="-- Cari / Pilih Pelanggan --" data-saldo="0" data-search="">
                                    <span style="color: #64748b;">-- Cari / Pilih Pelanggan --</span>
                                </div>
                                @foreach($pelangganList as $p)
                                    <div class="custom-select-option" 
                                         data-value="{{ $p->id_pelanggan }}" 
                                         data-text="{{ $p->nama_pelanggan }} ({{ $p->kode_pelanggan }})" 
                                         data-saldo="{{ (int)($p->saldo ?? 0) }}"
                                         data-search="{{ strtolower($p->nama_pelanggan . ' ' . $p->kode_pelanggan . ' ' . ($p->no_wa ?? '')) }}">
                                        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                                            <div>
                                                <strong style="color: #1e293b; font-size: 0.9rem;">{{ $p->nama_pelanggan }}</strong>
                                                <span style="font-family: monospace; font-size: 0.78rem; color: #64748b; margin-left: 4px;">({{ $p->kode_pelanggan }})</span>
                                                @if(!empty($p->no_wa))
                                                    <small style="color: #94a3b8; font-size: 0.75rem; display: block;"><i class="fa-brands fa-whatsapp" style="font-size: 0.75rem;"></i> {{ $p->no_wa }}</small>
                                                @endif
                                            </div>
                                            <div style="text-align: right; margin-left: 10px; flex-shrink: 0;">
                                                <span style="font-size: 0.78rem; font-weight: 700; color: {{ ($p->saldo ?? 0) > 0 ? '#059669' : '#64748b' }}; background: {{ ($p->saldo ?? 0) > 0 ? '#ecfdf5' : '#f1f5f9' }}; padding: 2px 8px; border-radius: 6px; border: 1px solid {{ ($p->saldo ?? 0) > 0 ? '#a7f3d0' : '#e2e8f0' }};">
                                                    Rp {{ number_format($p->saldo ?? 0, 0, ',', '.') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                <div id="topup_pelanggan_empty" style="display: none; padding: 16px; text-align: center; color: #94a3b8; font-size: 0.85rem;">
                                    <i class="fa-solid fa-magnifying-glass" style="margin-right: 4px;"></i> Pelanggan tidak ditemukan
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="saldoInfoBox" style="display: none; background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 12px; padding: 12px 16px; margin-bottom: 16px;">
                    <div style="font-size: 0.8rem; color: #4338ca;">Saldo Pelanggan Saat Ini:</div>
                    <div id="saldoInfoValue" style="font-size: 1.25rem; font-weight: 700; color: #3730a3;">Rp 0</div>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label style="font-weight: 600; font-size: 0.85rem; color: #334155; display: block; margin-bottom: 6px;">
                        Jenis Transaksi *
                    </label>
                    <div style="display: flex; gap: 12px;">
                        <label style="flex: 1; display: flex; align-items: center; gap: 8px; padding: 10px 14px; border: 2px solid #a7f3d0; border-radius: 10px; cursor: pointer; background: #f0fdf4;">
                            <input type="radio" name="tipe" value="masuk" checked style="accent-color: #059669; width: 18px; height: 18px;">
                            <span style="font-weight: 700; color: #065f46; font-size: 0.88rem;">+ Tambah Saldo (Top Up)</span>
                        </label>
                        <label style="flex: 1; display: flex; align-items: center; gap: 8px; padding: 10px 14px; border: 2px solid #fecaca; border-radius: 10px; cursor: pointer; background: #fef2f2;">
                            <input type="radio" name="tipe" value="keluar" style="accent-color: #dc2626; width: 18px; height: 18px;">
                            <span style="font-weight: 700; color: #991b1b; font-size: 0.88rem;">- Kurangi Saldo</span>
                        </label>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label for="topup_jumlah" style="font-weight: 600; font-size: 0.85rem; color: #334155; display: block; margin-bottom: 6px;">
                        Nominal Saldo (Rp) *
                    </label>
                    <input type="number" name="jumlah" id="topup_jumlah" class="form-control" required min="1000" placeholder="Contoh: 50000" style="border: 1px solid #cbd5e1; border-radius: 10px; height: 42px; font-size: 1rem; font-weight: 600;">
                    
                    <div style="margin-top: 8px;">
                        <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">Pilihan Cepat:</span><br>
                        <span class="quick-chip" onclick="setNominal(20000)">+20.000</span>
                        <span class="quick-chip" onclick="setNominal(50000)">+50.000</span>
                        <span class="quick-chip" onclick="setNominal(100000)">+100.000</span>
                        <span class="quick-chip" onclick="setNominal(150000)">+150.000</span>
                        <span class="quick-chip" onclick="setNominal(200000)">+200.000</span>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label for="topup_keterangan" style="font-weight: 600; font-size: 0.85rem; color: #334155; display: block; margin-bottom: 6px;">
                        Keterangan / Catatan
                    </label>
                    <textarea name="keterangan" id="topup_keterangan" rows="2" class="form-control" placeholder="Contoh: Pembayaran tunai di loket kasir" style="border: 1px solid #cbd5e1; border-radius: 10px; padding: 8px 12px; font-size: 0.9rem; width: 100%;"></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 24px; padding: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;">
                    <label style="display: flex; align-items: center; gap: 10px; margin: 0; cursor: pointer;">
                        <input type="checkbox" name="catat_di_kas" value="1" checked style="width: 18px; height: 18px; accent-color: #4f46e5;">
                        <span style="font-size: 0.85rem; color: #1e293b; font-weight: 600;">
                            Catat transaksi ini ke Buku Kas (tb_kas)
                        </span>
                    </label>
                    <small style="color: #64748b; font-size: 0.75rem; display: block; margin-top: 4px; margin-left: 28px;">
                        Centang ini jika uang tunai fisik/transfer diterima langsung di kas kantor agar Buku Kas terupdate otomatis.
                    </small>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-secondary" onclick="closeTopupModal()">Batal</button>
                    <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">
                        <i class="fa-solid fa-check"></i> Proses Saldo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Riwayat Mutasi Per Pelanggan (AJAX) -->
<div class="modal" id="historyModal">
    <div class="modal-content" style="width: min(750px, 100%);">
        <div class="modal-header">
            <h3><i class="fa-solid fa-clock-rotate-left" style="color: #2563eb;"></i> Riwayat Mutasi Saldo Pelanggan</h3>
            <button class="modal-close" onclick="closeHistoryModal()">&times;</button>
        </div>
        <div class="modal-body">
            <!-- Header Profile Pelanggan -->
            <div id="historyClientInfo" style="display: flex; justify-content: space-between; align-items: center; padding: 14px 18px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h4 id="historyClientNama" style="margin: 0; font-size: 1.05rem; color: #1e293b;">-</h4>
                    <span id="historyClientSub" style="font-size: 0.8rem; color: #64748b;">-</span>
                </div>
                <div style="text-align: right;">
                    <span style="font-size: 0.75rem; color: #64748b; display: block;">Saldo Saat Ini:</span>
                    <strong id="historyClientSaldo" style="font-size: 1.2rem; color: #4f46e5;">Rp 0</strong>
                </div>
            </div>

            <div id="historyLoading" style="text-align: center; padding: 30px; color: #64748b;">
                <i class="fa-solid fa-spinner fa-spin" style="font-size: 1.5rem; color: #4f46e5;"></i>
                <div style="margin-top: 8px;">Memuat riwayat saldo...</div>
            </div>

            <div class="table-container" id="historyTableContainer" style="display: none; max-height: 400px; overflow-y: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Jenis</th>
                            <th>Nominal</th>
                            <th>Saldo Awal &rarr; Akhir</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody id="historyTableBody">
                    </tbody>
                </table>
            </div>
            
            <div id="historyEmpty" style="display: none; text-align: center; padding: 24px; color: #94a3b8;">
                Belum ada riwayat mutasi saldo untuk pelanggan ini.
            </div>

            <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="closeHistoryModal()">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        if (typeof setupTablePagination === 'function') {
            setupTablePagination("#saldoTable", "#saldoPagination", "#tableLimit", "#tableSearch");
        }
        setupTopupCustomSelect();
    });

    function setupTopupCustomSelect() {
        const searchInput = document.getElementById('topup_search_pelanggan');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                filterTopupPelangganOptions(this.value);
            });
            searchInput.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }

        const optionsContainer = document.getElementById('topup_select_options');
        if (optionsContainer) {
            optionsContainer.querySelectorAll('.custom-select-option').forEach(opt => {
                opt.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const val = this.getAttribute('data-value') || '';
                    const text = this.getAttribute('data-text') || '-- Cari / Pilih Pelanggan --';
                    const saldo = this.getAttribute('data-saldo') || '0';
                    selectTopupPelanggan(val, text, saldo);
                });
            });
        }

        const formTopup = document.getElementById('formTopup');
        if (formTopup) {
            formTopup.addEventListener('submit', function(e) {
                const pid = document.getElementById('topup_pelanggan_id').value;
                if (!pid) {
                    e.preventDefault();
                    alert('Silakan pilih pelanggan terlebih dahulu!');
                    toggleTopupSelectDropdown();
                    return false;
                }
            });
        }
    }

    function toggleTopupSelectDropdown(e) {
        if (e) e.stopPropagation();
        const container = document.getElementById('topup_pelanggan_select');
        if (!container) return;
        
        const isOpen = container.classList.contains('active');
        if (isOpen) {
            container.classList.remove('active');
        } else {
            container.classList.add('active');
            const searchInput = document.getElementById('topup_search_pelanggan');
            if (searchInput) {
                searchInput.value = '';
                filterTopupPelangganOptions('');
                setTimeout(() => searchInput.focus(), 60);
            }
        }
    }

    function filterTopupPelangganOptions(query) {
        const optionsContainer = document.getElementById('topup_select_options');
        if (!optionsContainer) return;
        const items = optionsContainer.querySelectorAll('.custom-select-option');
        const emptyMsg = document.getElementById('topup_pelanggan_empty');
        let hasMatch = false;

        const q = (query || '').toLowerCase().trim();

        items.forEach(opt => {
            const val = opt.getAttribute('data-value');
            if (!val) {
                opt.style.display = q === '' ? 'block' : 'none';
                return;
            }
            const searchData = (opt.getAttribute('data-search') || '').toLowerCase();
            if (searchData.includes(q)) {
                opt.style.display = 'block';
                hasMatch = true;
            } else {
                opt.style.display = 'none';
            }
        });

        if (emptyMsg) {
            emptyMsg.style.display = (hasMatch || q === '') ? 'none' : 'block';
        }
    }

    function selectTopupPelanggan(val, text, saldo) {
        const hiddenInput = document.getElementById('topup_pelanggan_id');
        const triggerText = document.getElementById('topup_select_text');
        const container = document.getElementById('topup_pelanggan_select');
        const optionsContainer = document.getElementById('topup_select_options');

        if (hiddenInput) hiddenInput.value = val;
        if (triggerText) {
            triggerText.innerText = text;
            triggerText.style.color = val ? '#1e293b' : '#64748b';
            triggerText.style.fontWeight = val ? '600' : 'normal';
        }

        if (optionsContainer) {
            optionsContainer.querySelectorAll('.custom-select-option').forEach(el => {
                if ((el.getAttribute('data-value') || '') === (val || '')) {
                    el.classList.add('selected');
                } else {
                    el.classList.remove('selected');
                }
            });
        }

        const infoBox = document.getElementById('saldoInfoBox');
        const infoVal = document.getElementById('saldoInfoValue');
        if (val) {
            const numSaldo = parseInt(saldo) || 0;
            if (infoVal) infoVal.innerText = 'Rp ' + numSaldo.toLocaleString('id-ID');
            if (infoBox) infoBox.style.display = 'block';
        } else {
            if (infoBox) infoBox.style.display = 'none';
        }

        if (container) container.classList.remove('active');
    }

    function setNominal(val) {
        document.getElementById('topup_jumlah').value = val;
    }

    function openTopupModal(pelangganId, pelangganNama, currentSaldo) {
        const modal = document.getElementById('topupModal');
        
        if (pelangganId) {
            selectTopupPelanggan(pelangganId, pelangganNama, currentSaldo);
        } else {
            selectTopupPelanggan('', '-- Cari / Pilih Pelanggan --', 0);
        }
        
        document.getElementById('topup_jumlah').value = '';
        document.getElementById('topup_keterangan').value = '';
        modal.classList.add('active');
    }

    function closeTopupModal() {
        const modal = document.getElementById('topupModal');
        if (modal) modal.classList.remove('active');
        const container = document.getElementById('topup_pelanggan_select');
        if (container) container.classList.remove('active');
    }

    function openHistoryModal() {
        document.getElementById('historyModal').classList.add('active');
    }

    function closeHistoryModal() {
        document.getElementById('historyModal').classList.remove('active');
    }

    function showCustomerHistory(idPelanggan) {
        openHistoryModal();
        const loading = document.getElementById('historyLoading');
        const container = document.getElementById('historyTableContainer');
        const empty = document.getElementById('historyEmpty');
        const tbody = document.getElementById('historyTableBody');

        loading.style.display = 'block';
        container.style.display = 'none';
        empty.style.display = 'none';
        tbody.innerHTML = '';

        fetch('{{ url("administrator/saldo/history") }}/' + idPelanggan)
            .then(res => res.json())
            .then(data => {
                loading.style.display = 'none';
                if (data.status === 'success') {
                    document.getElementById('historyClientNama').innerText = data.pelanggan.nama_pelanggan;
                    document.getElementById('historyClientSub').innerText = data.pelanggan.kode_pelanggan + ' • Paket: ' + data.pelanggan.paket;
                    document.getElementById('historyClientSaldo').innerText = data.pelanggan.saldo_formatted;

                    if (data.mutasi.length === 0) {
                        empty.style.display = 'block';
                    } else {
                        data.mutasi.forEach(m => {
                            const tr = document.createElement('tr');
                            const isMasuk = m.tipe === 'masuk';
                            const badge = isMasuk
                                ? '<span class="badge-tipe-masuk"><i class="fa-solid fa-arrow-down"></i> Masuk</span>'
                                : '<span class="badge-tipe-keluar"><i class="fa-solid fa-arrow-up"></i> Keluar</span>';

                            tr.innerHTML = `
                                <td>${m.tanggal}</td>
                                <td>${badge}</td>
                                <td><strong style="color: ${isMasuk ? '#059669' : '#dc2626'}">${m.jumlah_formatted}</strong></td>
                                <td style="font-size:0.8rem; color:#64748b;">${m.saldo_awal_formatted} &rarr; <strong style="color:#1e293b;">${m.saldo_akhir_formatted}</strong></td>
                                <td style="font-size:0.82rem;">${m.keterangan} <br><small style="color:#94a3b8;">${m.kategori} • ${m.admin}</small></td>
                            `;
                            tbody.appendChild(tr);
                        });
                        container.style.display = 'block';
                    }
                }
            })
            .catch(err => {
                loading.style.display = 'none';
                empty.style.display = 'block';
                empty.innerText = 'Gagal memuat data riwayat.';
                console.error(err);
            });
    }

    // Close modal & dropdown on click outside
    window.addEventListener('click', function(e) {
        const topupModal = document.getElementById('topupModal');
        const historyModal = document.getElementById('historyModal');
        if (e.target === topupModal) closeTopupModal();
        if (e.target === historyModal) closeHistoryModal();

        const selectContainer = document.getElementById('topup_pelanggan_select');
        if (selectContainer && !selectContainer.contains(e.target)) {
            selectContainer.classList.remove('active');
        }
    });
</script>
@endsection
