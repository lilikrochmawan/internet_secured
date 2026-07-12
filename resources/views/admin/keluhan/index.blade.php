@extends('layouts.admin')

@section('title', 'Keluhan & Tiket')

@section('styles')
<!-- Load Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Styling to make Select2 match our premium theme */
    .select2-container--default .select2-selection--single {
        border: 1px solid #cbd5e1 !important;
        border-radius: 12px !important;
        height: 44px !important;
        padding: 8px 12px !important;
        font-family: inherit !important;
        font-size: 0.95rem !important;
        outline: none !important;
        transition: border 0.2s !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-container--default .select2-selection--single .select2-selection--rendered {
        line-height: 26px !important;
        padding-left: 0 !important;
        color: #1e293b !important;
    }
    .select2-container--default .select2-selection--single .select2-selection--arrow {
        height: 42px !important;
        right: 10px !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-dropdown {
        border: 1px solid #cbd5e1 !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
        overflow: hidden !important;
        z-index: 99999 !important;
    }
    .select2-search--dropdown .select2-search__field {
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        padding: 6px 10px !important;
        outline: none !important;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background: var(--primary-gradient) !important;
        color: white !important;
    }
    .select2-container {
        width: 100% !important;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        border-radius: 10px;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        text-decoration: none;
    }

    .btn-info {
        background-color: #eff6ff;
        color: #2563eb;
    }
    .btn-info:hover {
        background-color: #dbeafe;
    }

    .btn-success {
        background-color: #dcfce7;
        color: #15803d;
    }
    .btn-success:hover {
        background-color: #bbf7d0;
    }

    .table-container {
        margin-top: 20px;
        overflow-x: auto;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .table th, .table td {
        padding: 14px 18px;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.9rem;
    }

    .table th {
        font-weight: 600;
        color: var(--text-gray);
        background-color: #f8fafc;
    }

    .table tr {
        transition: background-color 0.2s;
    }

    .table tr:hover {
        background-color: #f8fafc;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 9999px;
        font-size: 0.78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .badge-menunggu {
        background-color: #fef2f2;
        color: #dc2626;
    }

    .badge-proses {
        background-color: #eff6ff;
        color: #2563eb;
    }

    .badge-selesai {
        background-color: #f0fdf4;
        color: #16a34a;
    }

    .badge-verifikasi {
        background-color: #fffbeb;
        color: #d97706;
    }

    /* Modal dialog styling */
    .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0,0,0,0.5);
        z-index: 1000;
        display: none;
        align-items: center;
        justify-content: center;
    }

    .modal-backdrop.show {
        display: flex;
    }

    .modal-content {
        background: white;
        border-radius: 20px;
        padding: 24px;
        width: 100%;
        max-width: 480px;
        box-shadow: var(--shadow-lg);
        display: flex;
        flex-direction: column;
        gap: 16px;
        animation: slideDown 0.2s ease;
    }

    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 12px;
    }

    .modal-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-dark);
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 1.25rem;
        cursor: pointer;
        color: var(--text-gray);
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

    .form-group {
        margin-bottom: 18px;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .form-group label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #334155;
    }

    .form-control {
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 10px 14px;
        font-size: 0.95rem;
        outline: none;
        width: 100%;
        transition: border 0.2s;
    }

    .form-control:focus {
        border-color: #4f46e5;
    }
</style>
@endsection

@section('content')
<!-- Card Lihat Laporan Keluhan & Tiket -->
<div class="card" style="margin-bottom: 24px;">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-file-lines"></i>
            <span>Lihat Laporan Keluhan & Tiket</span>
        </div>
    </div>
    <form action="{{ route('admin.keluhan.print') }}" method="GET" target="_blank" style="padding: 24px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; align-items: flex-end;">
            <div class="form-group" style="margin-bottom:0;">
                <label for="filter_tipe">Tipe Laporan</label>
                <select name="tipe" id="filter_tipe" class="form-control" onchange="toggleReportFilters()" style="height: 44px; border-radius: 12px;">
                    <option value="harian">Laporan Harian</option>
                    <option value="mingguan">Laporan Mingguan</option>
                    <option value="bulanan" selected>Laporan Bulanan</option>
                    <option value="tahunan">Laporan Tahunan</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom:0;">
                <label for="filter_status">Status Tiket</label>
                <select name="status" id="filter_status" class="form-control" style="height: 44px; border-radius: 12px;">
                    <option value="semua" selected>Semua Status</option>
                    <option value="menunggu">Menunggu</option>
                    <option value="proses">Diproses</option>
                    <option value="selesai">Selesai</option>
                </select>
            </div>

            <!-- Harian Filter -->
            <div class="form-group filter-group" id="filter_harian_wrapper" style="display:none; margin-bottom:0;">
                <label for="filter_tgl">Pilih Tanggal</label>
                <input type="date" name="tanggal" id="filter_tgl" class="form-control" value="{{ date('Y-m-d') }}" style="height: 44px; border-radius: 12px;">
            </div>

            <!-- Mingguan Filter -->
            <div class="filter-group" id="filter_mingguan_wrapper" style="display:none; gap: 12px; margin-bottom:0;">
                <div class="form-group" style="margin-bottom:0; flex: 1;">
                    <label for="filter_tgl_mulai">Tanggal Mulai</label>
                    <input type="date" name="tgl_mulai" id="filter_tgl_mulai" class="form-control" value="{{ date('Y-m-d', strtotime('-6 days')) }}" style="height: 44px; border-radius: 12px;">
                </div>
                <div class="form-group" style="margin-bottom:0; flex: 1;">
                    <label for="filter_tgl_selesai">Tanggal Selesai</label>
                    <input type="date" name="tgl_selesai" id="filter_tgl_selesai" class="form-control" value="{{ date('Y-m-d') }}" style="height: 44px; border-radius: 12px;">
                </div>
            </div>

            <!-- Bulanan Filter -->
            <div class="filter-group" id="filter_bulanan_wrapper" style="display:flex; gap: 12px; margin-bottom:0;">
                <div class="form-group" style="margin-bottom:0; flex: 1;">
                    <label for="filter_bulan">Bulan</label>
                    <select name="bulan" id="filter_bulan" class="form-control" style="height: 44px; border-radius: 12px; padding: 4px 10px;">
                        @foreach(range(1, 12) as $m)
                            @php $mVal = str_pad($m, 2, '0', STR_PAD_LEFT); @endphp
                            <option value="{{ $mVal }}" {{ date('m') == $mVal ? 'selected' : '' }}>
                                {{ Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0; flex: 1;">
                    <label for="filter_tahun_bulan">Tahun</label>
                    <select name="tahun_bulan" id="filter_tahun_bulan" class="form-control" style="height: 44px; border-radius: 12px; padding: 4px 10px;">
                        @foreach(range(date('Y') - 5, date('Y') + 2) as $y)
                            <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Tahunan Filter -->
            <div class="form-group filter-group" id="filter_tahunan_wrapper" style="display:none; margin-bottom:0;">
                <label for="filter_tahun">Tahun</label>
                <select name="tahun" id="filter_tahun" class="form-control" style="height: 44px; border-radius: 12px; padding: 4px 10px;">
                    @foreach(range(date('Y') - 5, date('Y') + 2) as $y)
                        <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom:0;">
                <button type="submit" class="btn btn-primary" style="height: 44px; width: 100%; justify-content: center; border-radius: 12px;">
                    <i class="fa-solid fa-eye"></i> Lihat Laporan
                </button>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div class="card-title">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>Daftar Keluhan & Tiket</span>
        </div>
        @if(in_array(Auth::user()->level, ['admin', 'noc']))
            <button type="button" class="btn btn-primary" onclick="openCreateTicketModal()" style="height: 40px; border-radius: 12px; font-size: 0.85rem; padding: 8px 16px;">
                <i class="fa-solid fa-plus"></i> Buat Tiket
            </button>
        @endif
    </div>

    <!-- Search and Row Limiter -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-top: 10px; margin-bottom:16px; flex-wrap:wrap; gap:12px;">
        <div></div>
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
                <input type="text" id="tableSearch" class="form-control" placeholder="Cari keluhan..." value="{{ request('search') }}" style="padding-left:36px; height:40px; border-radius:10px; width:100%;">
            </div>
        </div>
    </div>

    <div class="table-container">
        <table class="table" id="keluhanTable">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nomor Tiket</th>
                    <th>Nama Pelanggan</th>
                    <th>Judul Keluhan</th>
                    <th>Isi Keluhan</th>
                    <th>Tanggal Gangguan</th>
                    <th>Status Tiket</th>
                    <th style="text-align: center;">Tindakan Staff</th>
                </tr>
            </thead>
            <tbody>
                @forelse($keluhan as $index => $k)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <span style="font-family: monospace; font-weight:700; color:#4f46e5;">#{{ $k->nomor_tiket }}</span><br>
                            @if($k->assign_to_all)
                                <span class="badge" style="background:#e0f2fe; color:#0369a1; padding: 2px 6px; font-size: 0.68rem; margin-top: 4px; text-transform: none;">Semua Teknisi</span>
                            @elseif($k->teknisi)
                                <span class="badge" style="background:#f3e8ff; color:#6b21a8; padding: 2px 6px; font-size: 0.68rem; margin-top: 4px; text-transform: none;">Teknisi: {{ $k->teknisi->nama_user }}</span>
                            @else
                                <span class="badge" style="background:#f1f5f9; color:#64748b; padding: 2px 6px; font-size: 0.68rem; margin-top: 4px; text-transform: none;">Belum Ditugaskan</span>
                            @endif
                        </td>
                        <td>
                            @if($k->id_pelanggan)
                                <strong>{{ $k->pelanggan->nama_pelanggan ?? 'N/A' }}</strong><br>
                                <small style="color:var(--text-gray);">{{ $k->no_wa }}</small>
                                <div style="display: flex; gap: 4px; flex-wrap: wrap; margin-top: 4px;">
                                    @if($k->no_wa)
                                        @php
                                            $cleanWa = preg_replace('/[^0-9]/', '', $k->no_wa);
                                            if (str_starts_with($cleanWa, '0')) {
                                                $cleanWa = '62' . substr($cleanWa, 1);
                                            }
                                            $waUrl = 'https://wa.me/' . $cleanWa;
                                        @endphp
                                        <a href="{{ $waUrl }}" target="_blank" class="btn" style="padding: 3px 6px; font-size: 0.7rem; background-color: #dcfce7; color: #15803d; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; font-weight: 700; text-decoration: none;">
                                            <i class="fa-brands fa-whatsapp" style="font-size: 0.85rem;"></i> WA
                                        </a>
                                    @endif
                                    @if($k->pelanggan && !empty($k->pelanggan->location))
                                        @php
                                            $loc = trim($k->pelanggan->location);
                                            $mapsUrl = (str_starts_with($loc, 'http://') || str_starts_with($loc, 'https://')) ? $loc : 'https://www.google.com/maps/search/?api=1&query=' . urlencode($loc);
                                        @endphp
                                        <a href="{{ $mapsUrl }}" target="_blank" class="btn" style="padding: 3px 6px; font-size: 0.7rem; background-color: #dbeafe; color: #1d4ed8; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; font-weight: 700; text-decoration: none;">
                                            <i class="fa-solid fa-map-location-dot" style="font-size: 0.8rem;"></i> Maps
                                        </a>
                                    @else
                                        <span class="btn" style="padding: 3px 6px; font-size: 0.7rem; background-color: #f1f5f9; color: #94a3b8; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; font-weight: 700; cursor: not-allowed; opacity: 0.7;" title="Lokasi tidak diatur">
                                            <i class="fa-solid fa-map-location-dot" style="font-size: 0.8rem;"></i> Maps
                                        </span>
                                    @endif
                                </div>
                            @else
                                <span class="badge" style="background:#e0f2fe; color:#0369a1; font-weight:700; padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; text-transform: none; display: inline-flex; align-items: center; gap: 4px;">
                                    <i class="fa-solid fa-screwdriver-wrench"></i> Internal / Maintenance
                                </span>
                            @endif
                        </td>
                        <td><strong>{{ $k->judul_keluhan }}</strong></td>
                        <td>
                            <div>{{ $k->isi_keluhan }}</div>
                            @if($k->gambar)
                                <div style="margin-top: 8px;">
                                    <a href="javascript:void(0)" class="view-attachment-btn" data-src="{{ route('admin.keluhan.gambar', ['filename' => $k->gambar]) }}" data-title="Lampiran Tiket #{{ $k->nomor_tiket }}" style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.78rem; color: #4f46e5; text-decoration: none; font-weight: 600; background: #e0e7ff; padding: 4px 8px; border-radius: 6px; transition: all 0.2s;">
                                        <i class="fa-solid fa-paperclip"></i> Lihat Lampiran
                                    </a>
                                </div>
                            @endif
                            @if($k->tindakan)
                                <div style="margin-top: 8px; background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 6px 10px; font-size: 0.8rem; color: #166534; text-align: left;">
                                    <span style="font-weight: 700; color: #15803d; font-size: 0.72rem; text-transform: uppercase; display: block; margin-bottom: 2px;">Tindakan Staff/Teknisi:</span>
                                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap;">
                                        <span>{{ $k->tindakan }}</span>
                                        @if($k->bukti_foto)
                                            <a href="javascript:void(0)" class="view-attachment-btn" data-src="{{ route('admin.keluhan.gambar', ['filename' => $k->bukti_foto]) }}" data-title="Bukti Pekerjaan Tiket #{{ $k->nomor_tiket }}" style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.72rem; color: #15803d; text-decoration: none; font-weight: 700; background: #dcfce7; padding: 3px 6px; border-radius: 6px; transition: all 0.2s;">
                                                <i class="fa-solid fa-image"></i> Lihat Foto Bukti
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @elseif($k->masalah)
                                <div style="margin-top: 8px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 10px; font-size: 0.8rem; color: #334155; text-align: left;">
                                    <span style="font-weight: 700; color: #475569; font-size: 0.72rem; text-transform: uppercase; display: block; margin-bottom: 2px;">Solusi / Masalah Akhir:</span>
                                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap;">
                                        <span>{{ $k->masalah }}</span>
                                        @if($k->bukti_foto)
                                            <a href="javascript:void(0)" class="view-attachment-btn" data-src="{{ route('admin.keluhan.gambar', ['filename' => $k->bukti_foto]) }}" data-title="Bukti Pekerjaan Tiket #{{ $k->nomor_tiket }}" style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.72rem; color: #1d4ed8; text-decoration: none; font-weight: 700; background: #dbeafe; padding: 3px 6px; border-radius: 6px; transition: all 0.2s;">
                                                <i class="fa-solid fa-image"></i> Lihat Foto Bukti
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </td>
                        <td>{{ $k->tanggal }}</td>
                        <td>
                            @if($k->status_keluhan == 'menunggu')
                                <span class="badge badge-menunggu">Menunggu</span>
                            @elseif($k->status_keluhan == 'proses')
                                <span class="badge badge-proses">Diproses</span>
                            @elseif($k->status_keluhan == 'perlu_verifikasi')
                                <span class="badge badge-verifikasi">Perlu Verifikasi</span>
                            @elseif($k->status_keluhan == 'selesai')
                                <span class="badge badge-selesai">Selesai</span>
                            @else
                                <span class="badge" style="background:#f1f5f9; color:#64748b;">{{ $k->status_keluhan }}</span>
                            @endif
                        </td>
                        <td align="center">
                            @if(Auth::user()->level !== 'teknisi')
                                @if($k->status_keluhan == 'menunggu')
                                    <div style="display: flex; gap: 8px; justify-content: center; flex-wrap: wrap;">
                                        <button type="button" class="btn btn-info open-assign-modal" data-id="{{ $k->id_keluhan }}" data-tiket="{{ $k->nomor_tiket }}">
                                            <i class="fa-solid fa-user-plus"></i> Tugaskan
                                        </button>
                                        <form action="{{ route('admin.keluhan.proses') }}" method="POST" style="display:inline;">
                                            @csrf
                                            <input type="hidden" name="id_keluhan" value="{{ $k->id_keluhan }}">
                                            <input type="hidden" name="assign_type" value="self">
                                            <button type="submit" class="btn btn-success" style="background-color: #dcfce7; color: #15803d;">
                                                <i class="fa-solid fa-spinner"></i> Proses
                                            </button>
                                        </form>
                                    </div>
                                @elseif($k->status_keluhan == 'proses')
                                    <div style="display: flex; gap: 8px; justify-content: center; flex-wrap: wrap;">
                                        <button type="button" class="btn btn-success open-verifikasi-modal" data-id="{{ $k->id_keluhan }}" data-tiket="{{ $k->nomor_tiket }}" data-tindakan="" data-foto="" data-is-direct="true">
                                            <i class="fa-solid fa-circle-check"></i> Selesaikan
                                        </button>
                                        <button type="button" class="btn btn-info open-assign-modal" data-id="{{ $k->id_keluhan }}" data-tiket="{{ $k->nomor_tiket }}" data-current-type="{{ $k->assign_to_all ? 'all' : 'specific' }}" data-current-id="{{ $k->teknisi_id }}" style="background-color: #f1f5f9; color: #475569;">
                                            <i class="fa-solid fa-user-pen"></i> Re-Tugaskan
                                        </button>
                                    </div>
                                @elseif($k->status_keluhan == 'perlu_verifikasi')
                                    <button type="button" class="btn btn-success open-verifikasi-modal" data-id="{{ $k->id_keluhan }}" data-tiket="{{ $k->nomor_tiket }}" data-tindakan="{{ $k->tindakan }}" data-foto="{{ route('admin.keluhan.gambar', ['filename' => $k->bukti_foto]) }}">
                                        <i class="fa-solid fa-clipboard-check"></i> Verifikasi Pekerjaan
                                    </button>
                                @else
                                    <span style="font-size:0.85rem; color:#64748b; font-style:italic;">Tiket Selesai</span>
                                @endif
                            @else
                                {{-- For Technician level --}}
                                @if($k->status_keluhan == 'proses')
                                    <button type="button" class="btn btn-success open-teknisi-selesai-modal" data-id="{{ $k->id_keluhan }}" data-tiket="{{ $k->nomor_tiket }}">
                                        <i class="fa-solid fa-circle-check"></i> Selesaikan
                                    </button>
                                @elseif($k->status_keluhan == 'perlu_verifikasi')
                                    <span style="font-size:0.85rem; color:#d97706; font-weight:600;"><i class="fa-solid fa-spinner fa-spin"></i> Menunggu Verifikasi</span>
                                @else
                                    <span style="font-size:0.85rem; color:#16a34a; font-style:italic;"><i class="fa-solid fa-check-double"></i> Selesai</span>
                                @endif
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-gray); padding: 30px;">
                            Tidak ada keluhan pelanggan saat ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div id="keluhanPagination"></div>
</div>

<!-- Modal Viewer Gambar -->
<div class="modal-backdrop" id="imageViewerModal" style="z-index: 99999;">
    <div class="modal-content" style="max-width: 640px; padding: 16px;">
        <div class="modal-header" style="border-bottom: none; padding-bottom: 0;">
            <span class="modal-title" id="imageViewerTitle">Lampiran Keluhan</span>
            <button type="button" class="modal-close" id="btnCloseImageViewer">&times;</button>
        </div>
        <div style="display: flex; justify-content: center; align-items: center; padding: 12px 0;">
            <img id="imageViewerSrc" src="" alt="Lampiran" style="max-width: 100%; max-height: 480px; border-radius: 12px; box-shadow: var(--shadow-sm); object-fit: contain; background-color: #f8fafc;">
        </div>
        <div style="display: flex; justify-content: flex-end; margin-top: 8px;">
            <a id="imageDownloadBtn" href="" download class="btn btn-default" style="font-size: 0.85rem; padding: 8px 14px; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-download"></i> Unduh Gambar
            </a>
        </div>
    </div>
</div>

<!-- Modal Tugaskan/Assign Teknisi -->
<div class="modal-backdrop" id="assignModal">
    <div class="modal-content" style="max-width: 480px; padding: 24px;">
        <div class="modal-header">
            <span class="modal-title">Tugaskan Teknisi</span>
            <button type="button" class="modal-close" onclick="closeAssignModal()">&times;</button>
        </div>
        <div class="modal-body" style="padding: 10px 0;">
            <form action="{{ route('admin.keluhan.proses') }}" method="POST">
                @csrf
                <input type="hidden" name="id_keluhan" id="assign_id_keluhan">
                
                <div style="font-size: 0.9rem; color: #475569; margin-bottom: 16px; text-align: left;">
                    Tugaskan teknisi untuk menangani Tiket Gangguan <strong id="assign_tiket_label">#000</strong>.
                </div>

                <div class="form-group" style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; text-align: left;">
                    <label for="assign_type" style="font-weight: 600; font-size: 0.85rem; color: #334155;">Jenis Penugasan *</label>
                    <select name="assign_type" id="assign_type" class="form-control" required onchange="toggleAssignType()" style="height: 44px; border-radius: 12px; padding: 4px 10px;">
                        <option value="all">Semua Teknisi</option>
                        <option value="specific">Teknisi Tertentu</option>
                    </select>
                </div>

                <div class="form-group" id="specific_teknisi_group" style="display: none; flex-direction: column; gap: 6px; margin-bottom: 16px; text-align: left;">
                    <label for="assign_teknisi_id" style="font-weight: 600; font-size: 0.85rem; color: #334155;">Pilih Teknisi *</label>
                    <select name="teknisi_id" id="assign_teknisi_id" class="form-control" style="height: 44px; border-radius: 12px; padding: 4px 10px;">
                        <option value="" disabled selected>-- Pilih Teknisi --</option>
                        @foreach($teknisiList as $t)
                            <option value="{{ $t->id }}">{{ $t->nama_user }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                    <button type="button" class="btn btn-secondary" onclick="closeAssignModal()" style="padding: 10px 16px; border-radius: 12px; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: all 0.2s; border: none; background-color: #e2e8f0; color: #334155;">Batal</button>
                    <button type="submit" class="btn btn-primary" style="padding: 10px 16px; border-radius: 12px; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: all 0.2s; border: none; background: var(--primary-gradient); color: white; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);">Simpan & Tugaskan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Teknisi Selesaikan Tiket -->
<div class="modal-backdrop" id="teknisiSelesaiModal">
    <div class="modal-content" style="max-width: 480px; padding: 24px;">
        <div class="modal-header">
            <span class="modal-title">Laporkan Pekerjaan Selesai</span>
            <button type="button" class="modal-close" onclick="closeTeknisiSelesaiModal()">&times;</button>
        </div>
        <div class="modal-body" style="padding: 10px 0;">
            <form action="{{ route('admin.keluhan.teknisi_selesai') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id_keluhan" id="teknisi_selesai_id_keluhan">
                
                <div style="font-size: 0.9rem; color: #475569; margin-bottom: 16px; text-align: left;">
                    Lengkapi laporan penyelesaian untuk Tiket Gangguan <strong id="teknisi_selesai_tiket_label">#000</strong>.
                </div>

                <div class="form-group" style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; text-align: left;">
                    <label for="input_tindakan" style="font-weight: 600; font-size: 0.85rem; color: #334155;">Tindakan yang Dilakukan *</label>
                    <textarea name="tindakan" id="input_tindakan" rows="4" class="form-control" placeholder="Contoh: Menyambung kabel FO yang putus di tiang A, merapikan kabel dropcore" required style="border: 1px solid #cbd5e1; border-radius: 12px; padding: 10px 14px; font-size: 0.95rem; outline: none; width: 100%; transition: border 0.2s; font-family: inherit; resize: vertical;"></textarea>
                </div>

                <div class="form-group" style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; text-align: left;">
                    <label for="input_bukti_foto" style="font-weight: 600; font-size: 0.85rem; color: #334155;">Upload Bukti Foto *</label>
                    <input type="file" name="bukti_foto" id="input_bukti_foto" class="form-control" required accept="image/*" style="border: 1px solid #cbd5e1; border-radius: 12px; padding: 10px 14px; font-size: 0.95rem; outline: none; width: 100%; transition: border 0.2s;">
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                    <button type="button" class="btn btn-secondary" onclick="closeTeknisiSelesaiModal()" style="padding: 10px 16px; border-radius: 12px; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: all 0.2s; border: none; background-color: #e2e8f0; color: #334155;">Batal</button>
                    <button type="submit" class="btn btn-primary" style="padding: 10px 16px; border-radius: 12px; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: all 0.2s; border: none; background: var(--primary-gradient); color: white; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);">Kirim Laporan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Verifikasi Keluhan -->
<div class="modal-backdrop" id="verifikasiModal">
    <div class="modal-content" style="max-width: 520px; padding: 24px;">
        <div class="modal-header">
            <span class="modal-title">Verifikasi Penyelesaian Gangguan</span>
            <button type="button" class="modal-close" onclick="closeVerifikasiModal()">&times;</button>
        </div>
        <div class="modal-body" style="padding: 10px 0;">
            <form action="{{ route('admin.keluhan.verifikasi') }}" method="POST">
                @csrf
                <input type="hidden" name="id_keluhan" id="verifikasi_id_keluhan">
                
                <div style="font-size: 0.9rem; color: #475569; margin-bottom: 16px; text-align: left;">
                    Review pekerjaan teknisi untuk Tiket Gangguan <strong id="verifikasi_tiket_label">#000</strong>.
                </div>

                <!-- Detail dari Teknisi -->
                <div id="verifikasi_teknisi_detail" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px; margin-bottom: 16px; text-align: left;">
                    <div style="font-size: 0.8rem; font-weight: 700; color: #64748b; margin-bottom: 8px; text-transform: uppercase;">Laporan Teknisi:</div>
                    <div style="font-size: 0.95rem; color: #1e293b; font-weight: 500; margin-bottom: 12px;" id="verifikasi_tindakan_text">
                        Tindakan teknisi...
                    </div>
                    <div style="font-size: 0.8rem; font-weight: 700; color: #64748b; margin-bottom: 6px; text-transform: uppercase;">Foto Bukti:</div>
                    <div>
                        <a href="javascript:void(0)" id="verifikasi_foto_link" style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.8rem; color: #4f46e5; text-decoration: none; font-weight: 600; background: #e0e7ff; padding: 6px 12px; border-radius: 6px;">
                            <i class="fa-solid fa-image"></i> Lihat Foto Bukti Pekerjaan
                        </a>
                    </div>
                </div>

                <div class="form-group" style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; text-align: left;">
                    <label for="verifikasi_input_masalah" style="font-weight: 600; font-size: 0.85rem; color: #334155;">Penyebab Keluhan / Solusi Akhir *</label>
                    <textarea name="masalah" id="verifikasi_input_masalah" rows="4" class="form-control" placeholder="Contoh: Kabel fiber putus tertabrak truk, sudah disambung kembali oleh teknisi" required style="border: 1px solid #cbd5e1; border-radius: 12px; padding: 10px 14px; font-size: 0.95rem; outline: none; width: 100%; transition: border 0.2s; font-family: inherit; resize: vertical;"></textarea>
                    <small style="color: #64748b; font-size: 0.78rem; text-align: left; margin-top: 4px;">Pesan ini akan dikirimkan kepada pelanggan melalui WhatsApp.</small>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                    <button type="button" class="btn btn-secondary" onclick="closeVerifikasiModal()" style="padding: 10px 16px; border-radius: 12px; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: all 0.2s; border: none; background-color: #e2e8f0; color: #334155;">Batal</button>
                    <button type="submit" class="btn btn-primary" style="padding: 10px 16px; border-radius: 12px; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: all 0.2s; border: none; background: var(--primary-gradient); color: white; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);">Verifikasi & Selesaikan Tiket</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Buat Tiket (Admin/NOC) -->
<div class="modal-backdrop" id="createTicketModal">
    <div class="modal-content" style="max-width: 520px; padding: 24px;">
        <div class="modal-header">
            <span class="modal-title">Buat Tiket Keluhan Baru</span>
            <button type="button" class="modal-close" onclick="closeCreateTicketModal()">&times;</button>
        </div>
        <div class="modal-body" style="padding: 10px 0;">
            <form action="{{ route('admin.keluhan.store_ticket') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="form-group" style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; text-align: left;">
                    <label for="create_tipe_tiket" style="font-weight: 600; font-size: 0.85rem; color: #334155;">Tipe Tiket *</label>
                    <select name="tipe_tiket" id="create_tipe_tiket" class="form-control" onchange="toggleCreateTicketType()" style="height: 44px; border-radius: 12px; padding: 4px 10px;">
                        <option value="pelanggan" selected>Pengaduan Pelanggan</option>
                        <option value="internal">Internal / Maintenance (Penarikan Kabel, Perawatan Rutin, dll)</option>
                    </select>
                </div>

                <div id="create_pelanggan_wrapper" class="form-group" style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; text-align: left;">
                    <label for="create_id_pelanggan" style="font-weight: 600; font-size: 0.85rem; color: #334155;">Pilih Pelanggan *</label>
                    <select name="id_pelanggan" id="create_id_pelanggan" class="form-control" required style="height: 44px; border-radius: 12px; padding: 4px 10px;">
                        <option value="" disabled selected>-- Pilih Pelanggan --</option>
                        @foreach($pelangganList as $p)
                            <option value="{{ $p->id_pelanggan }}">{{ $p->nama_pelanggan }} ({{ $p->kode_pelanggan ?? 'N/A' }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; text-align: left;">
                    <label for="create_judul_keluhan" style="font-weight: 600; font-size: 0.85rem; color: #334155;">Keluhan / Masalah Utama *</label>
                    <input type="text" name="judul_keluhan" id="create_judul_keluhan" class="form-control" placeholder="Contoh: Koneksi Lambat, LOS Merah, Kabel Putus" required style="border: 1px solid #cbd5e1; border-radius: 12px; padding: 10px 14px; font-size: 0.95rem; outline: none; width: 100%; transition: border 0.2s;">
                </div>

                <div class="form-group" style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; text-align: left;">
                    <label for="create_isi_keluhan" style="font-weight: 600; font-size: 0.85rem; color: #334155;">Detail Keluhan / Informasi Tambahan *</label>
                    <textarea name="isi_keluhan" id="create_isi_keluhan" rows="4" class="form-control" placeholder="Jelaskan detail keluhan/pekerjaan disini..." required style="border: 1px solid #cbd5e1; border-radius: 12px; padding: 10px 14px; font-size: 0.95rem; outline: none; width: 100%; transition: border 0.2s; font-family: inherit; resize: vertical;"></textarea>
                </div>

                <div class="form-group" style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; text-align: left;">
                    <label for="create_gambar" style="font-weight: 600; font-size: 0.85rem; color: #334155;">Lampiran Gambar (Opsional)</label>
                    <input type="file" name="gambar" id="create_gambar" class="form-control" accept="image/*" style="border: 1px solid #cbd5e1; border-radius: 12px; padding: 10px 14px; font-size: 0.95rem; outline: none; width: 100%; transition: border 0.2s;">
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                    <button type="button" class="btn btn-secondary" onclick="closeCreateTicketModal()" style="padding: 10px 16px; border-radius: 12px; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: all 0.2s; border: none; background-color: #e2e8f0; color: #334155;">Batal</button>
                    <button type="submit" class="btn btn-primary" style="padding: 10px 16px; border-radius: 12px; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: all 0.2s; border: none; background: var(--primary-gradient); color: white; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);">Buat Tiket</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        setupTablePagination("#keluhanTable", "#keluhanPagination", "#tableLimit", "#tableSearch");
        toggleReportFilters();

        // Image viewer logic
        const modalViewer = document.getElementById("imageViewerModal");
        const closeBtn = document.getElementById("btnCloseImageViewer");
        const viewerTitle = document.getElementById("imageViewerTitle");
        const viewerSrc = document.getElementById("imageViewerSrc");
        const downloadBtn = document.getElementById("imageDownloadBtn");

        function openViewer(src, title) {
            viewerTitle.textContent = title;
            viewerSrc.src = src;
            downloadBtn.href = src;
            modalViewer.classList.add("show");
        }

        function closeViewer() {
            modalViewer.classList.remove("show");
            viewerSrc.src = "";
        }

        // Use event delegation because of client-side pagination
        const keluhanTable = document.getElementById("keluhanTable");
        if (keluhanTable) {
            keluhanTable.addEventListener("click", function(e) {
                const btn = e.target.closest(".view-attachment-btn");
                if (btn) {
                    const src = btn.getAttribute("data-src");
                    const title = btn.getAttribute("data-title");
                    openViewer(src, title);
                }
            });
        }

        if (closeBtn) closeBtn.addEventListener("click", closeViewer);
        
        if (modalViewer) {
            modalViewer.addEventListener("click", function (e) {
                if (e.target === modalViewer) {
                    closeViewer();
                }
            });
        }

        // Assign modal event delegation logic
        if (keluhanTable) {
            keluhanTable.addEventListener("click", function(e) {
                const btn = e.target.closest(".open-assign-modal");
                if (btn) {
                    const id = btn.getAttribute("data-id");
                    const tiket = btn.getAttribute("data-tiket");
                    const type = btn.getAttribute("data-current-type") || 'all';
                    const currentId = btn.getAttribute("data-current-id") || '';
                    openAssignModal(id, tiket, type, currentId);
                }
            });
        }

        // Teknisi selesai modal event delegation logic
        if (keluhanTable) {
            keluhanTable.addEventListener("click", function(e) {
                const btn = e.target.closest(".open-teknisi-selesai-modal");
                if (btn) {
                    const id = btn.getAttribute("data-id");
                    const tiket = btn.getAttribute("data-tiket");
                    openTeknisiSelesaiModal(id, tiket);
                }
            });
        }

        // Verifikasi modal event delegation logic
        if (keluhanTable) {
            keluhanTable.addEventListener("click", function(e) {
                const btn = e.target.closest(".open-verifikasi-modal");
                if (btn) {
                    const id = btn.getAttribute("data-id");
                    const tiket = btn.getAttribute("data-tiket");
                    const tindakan = btn.getAttribute("data-tindakan") || '';
                    const foto = btn.getAttribute("data-foto") || '';
                    const isDirect = btn.getAttribute("data-is-direct") === "true";
                    openVerifikasiModal(id, tiket, tindakan, foto, isDirect);
                }
            });
        }

        // Close modals clicking outside content
        const assignModal = document.getElementById("assignModal");
        if (assignModal) {
            assignModal.addEventListener("click", function (e) {
                if (e.target === assignModal) {
                    closeAssignModal();
                }
            });
        }

        const teknisiSelesaiModal = document.getElementById("teknisiSelesaiModal");
        if (teknisiSelesaiModal) {
            teknisiSelesaiModal.addEventListener("click", function (e) {
                if (e.target === teknisiSelesaiModal) {
                    closeTeknisiSelesaiModal();
                }
            });
        }

        const verifikasiModal = document.getElementById("verifikasiModal");
        if (verifikasiModal) {
            verifikasiModal.addEventListener("click", function (e) {
                if (e.target === verifikasiModal) {
                    closeVerifikasiModal();
                }
            });
        }

        const createTicketModal = document.getElementById("createTicketModal");
        if (createTicketModal) {
            createTicketModal.addEventListener("click", function (e) {
                if (e.target === createTicketModal) {
                    closeCreateTicketModal();
                }
            });
        }

        // Initialize Select2 for customer select dropdown
        $('#create_id_pelanggan').select2({
            dropdownParent: $('#createTicketModal'),
            placeholder: '-- Pilih Pelanggan --',
            allowClear: true
        });
    });

    function toggleReportFilters() {
        const tipe = document.getElementById('filter_tipe').value;
        
        // Hide all first
        document.getElementById('filter_harian_wrapper').style.display = 'none';
        document.getElementById('filter_mingguan_wrapper').style.display = 'none';
        document.getElementById('filter_bulanan_wrapper').style.display = 'none';
        document.getElementById('filter_tahunan_wrapper').style.display = 'none';
        
        // Show selected
        if (tipe === 'harian') {
            document.getElementById('filter_harian_wrapper').style.display = 'block';
        } else if (tipe === 'mingguan') {
            document.getElementById('filter_mingguan_wrapper').style.display = 'flex';
        } else if (tipe === 'bulanan') {
            document.getElementById('filter_bulanan_wrapper').style.display = 'flex';
        } else if (tipe === 'tahunan') {
            document.getElementById('filter_tahunan_wrapper').style.display = 'block';
        }
    }

    // Modal Assign functions
    function openAssignModal(id, ticketNumber, type, currentId) {
        document.getElementById('assign_id_keluhan').value = id;
        document.getElementById('assign_tiket_label').textContent = '#' + ticketNumber;
        document.getElementById('assign_type').value = type;
        
        const teknisiSelect = document.getElementById('assign_teknisi_id');
        if (currentId) {
            teknisiSelect.value = currentId;
        } else {
            teknisiSelect.selectedIndex = 0;
        }
        
        toggleAssignType();
        document.getElementById('assignModal').classList.add('show');
    }

    function closeAssignModal() {
        document.getElementById('assignModal').classList.remove('show');
    }

    function toggleAssignType() {
        const type = document.getElementById('assign_type').value;
        const group = document.getElementById('specific_teknisi_group');
        const teknisiSelect = document.getElementById('assign_teknisi_id');
        
        if (type === 'specific') {
            group.style.display = 'flex';
            teknisiSelect.setAttribute('required', 'required');
        } else {
            group.style.display = 'none';
            teknisiSelect.removeAttribute('required');
        }
    }

    // Modal Teknisi Selesai functions
    function openTeknisiSelesaiModal(id, ticketNumber) {
        document.getElementById('teknisi_selesai_id_keluhan').value = id;
        document.getElementById('teknisi_selesai_tiket_label').textContent = '#' + ticketNumber;
        document.getElementById('input_tindakan').value = '';
        document.getElementById('input_bukti_foto').value = '';
        document.getElementById('teknisiSelesaiModal').classList.add('show');
    }

    function closeTeknisiSelesaiModal() {
        document.getElementById('teknisiSelesaiModal').classList.remove('show');
    }

    // Modal Verifikasi functions
    function openVerifikasiModal(id, ticketNumber, tindakan, fotoSrc, isDirect = false) {
        document.getElementById('verifikasi_id_keluhan').value = id;
        document.getElementById('verifikasi_tiket_label').textContent = '#' + ticketNumber;
        
        const detailBlock = document.getElementById('verifikasi_teknisi_detail');
        if (isDirect) {
            detailBlock.style.display = 'none';
            document.getElementById('verifikasi_input_masalah').value = '';
        } else {
            detailBlock.style.display = 'block';
            document.getElementById('verifikasi_tindakan_text').textContent = tindakan;
            
            const fotoLink = document.getElementById('verifikasi_foto_link');
            fotoLink.setAttribute('data-src', fotoSrc);
            fotoLink.setAttribute('data-title', 'Bukti Pekerjaan Tiket #' + ticketNumber);
            
            // Use event delegation helper to make sure it opens using imageViewerModal
            fotoLink.onclick = function() {
                const viewerModal = document.getElementById("imageViewerModal");
                const viewerTitle = document.getElementById("imageViewerTitle");
                const viewerSrc = document.getElementById("imageViewerSrc");
                const downloadBtn = document.getElementById("imageDownloadBtn");
                
                viewerTitle.textContent = this.getAttribute("data-title");
                viewerSrc.src = this.getAttribute("data-src");
                downloadBtn.href = this.getAttribute("data-src");
                viewerModal.classList.add("show");
            };
            
            // Populate final solution textarea with technician's action taken as default
            document.getElementById('verifikasi_input_masalah').value = tindakan;
        }
        document.getElementById('verifikasiModal').classList.add('show');
    }

    function closeVerifikasiModal() {
        document.getElementById('verifikasiModal').classList.remove('show');
    }

    function openCreateTicketModal() {
        document.getElementById('create_tipe_tiket').value = 'pelanggan';
        toggleCreateTicketType();
        
        // Reset Select2 selection
        $('#create_id_pelanggan').val('').trigger('change');
        
        document.getElementById('create_judul_keluhan').value = '';
        document.getElementById('create_isi_keluhan').value = '';
        document.getElementById('create_gambar').value = '';
        document.getElementById('createTicketModal').classList.add('show');
    }

    function closeCreateTicketModal() {
        document.getElementById('createTicketModal').classList.remove('show');
    }

    function toggleCreateTicketType() {
        const type = document.getElementById('create_tipe_tiket').value;
        const wrapper = document.getElementById('create_pelanggan_wrapper');
        const select = document.getElementById('create_id_pelanggan');
        
        if (type === 'internal') {
            wrapper.style.display = 'none';
            select.removeAttribute('required');
            $('#create_id_pelanggan').val('').trigger('change');
        } else {
            wrapper.style.display = 'flex';
            select.setAttribute('required', 'required');
        }
    }
</script>
@endsection
