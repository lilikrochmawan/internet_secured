@extends('layouts.admin')

@section('title', 'Order Pemasangan Baru')

@section('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
        border: 1px solid #dbeafe;
    }
    .btn-info:hover {
        background-color: #dbeafe;
    }

    .btn-success {
        background-color: #f0fdf4;
        color: #16a34a;
        border: 1px solid #bbf7d0;
    }
    .btn-success:hover {
        background-color: #dcfce7;
    }

    .btn-danger {
        background-color: #fef2f2;
        color: #dc2626;
    }
    .btn-danger:hover {
        background-color: #fee2e2;
    }

    .order-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 24px;
        margin-top: 20px;
    }

    @media (max-width: 992px) {
        .order-grid {
            grid-template-columns: minmax(0, 1fr);
        }
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
        vertical-align: top;
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

    /* Status Badges */
    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 30px;
        font-size: 0.8rem;
        font-weight: 700;
        text-align: center;
    }

    .status-pending {
        background-color: #fef3c7;
        color: #d97706;
        border: 1px solid #fde68a;
    }

    .status-approved {
        background-color: #e0e7ff;
        color: #4338ca;
        border: 1px solid #c7d2fe;
    }

    .status-installed {
        background-color: #f5f3ff;
        color: #7c3aed;
        border: 1px solid #ddd6fe;
    }

    .status-completed {
        background-color: #dcfce7;
        color: #16a34a;
        border: 1px solid #bbf7d0;
    }

    .status-rejected {
        background-color: #fee2e2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }

    .status-cancelled {
        background-color: #f1f5f9;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }

    /* Modal Styling */
    .modal {
        display: none;
        position: fixed;
        inset: 0;
        background-color: rgba(15, 23, 42, 0.5);
        z-index: 1050;
        align-items: center;
        justify-content: center;
        padding: 20px;
        backdrop-filter: blur(4px);
        overflow-y: auto;
    }

    .modal.active {
        display: flex;
    }

    .modal-content {
        background-color: white;
        border-radius: 24px;
        width: min(560px, 100%);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        border: 1px solid var(--border-color);
        animation: modalFadeIn 0.3s ease;
        overflow: hidden;
        margin: auto;
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
        font-size: 1.2rem;
        font-weight: 700;
    }

    .modal-close {
        background: none;
        border: none;
        color: white;
        font-size: 1.2rem;
        cursor: pointer;
        opacity: 0.8;
    }

    .modal-close:hover {
        opacity: 1;
    }

    .modal-body {
        padding: 24px;
        max-height: 75vh;
        overflow-y: auto;
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

    @keyframes modalFadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Image Preview Styling */
    .preview-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-top: 12px;
        margin-bottom: 12px;
    }
    .preview-item {
        position: relative;
        aspect-ratio: 1;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #cbd5e1;
        background-color: #f8fafc;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .preview-remove {
        position: absolute;
        top: 4px;
        right: 4px;
        background-color: rgba(239, 68, 68, 0.9);
        color: white;
        border: none;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 0.75rem;
        font-weight: bold;
        transition: background-color 0.2s;
        padding: 0;
        line-height: 1;
    }
    .preview-remove:hover {
        background-color: rgba(220, 38, 38, 1);
    }

    /* Custom Searchable Dropdown Styling */
    .custom-select-container {
        position: relative;
        width: 100%;
        user-select: none;
    }
    .custom-select-trigger {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 10px 14px;
        font-size: 0.95rem;
        background-color: white;
        cursor: pointer;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .custom-select-trigger:hover {
        border-color: #94a3b8;
    }
    .custom-select-container.active .custom-select-trigger {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    .custom-select-dropdown {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        background-color: white;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        z-index: 9999;
        display: none;
        overflow: hidden;
    }
    .custom-select-container.active .custom-select-dropdown {
        display: block;
    }
    .custom-select-search-wrapper {
        position: relative;
        padding: 8px;
        border-bottom: 1px solid #e2e8f0;
        background-color: #f8fafc;
    }
    .custom-select-search-wrapper i {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.9rem;
    }
    .custom-select-search-input.form-control {
        width: 100%;
        padding: 8px 12px 8px 32px;
        font-size: 0.88rem;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background-color: white;
        height: 36px;
    }
    .custom-select-search-input.form-control:focus {
        border-color: #4f46e5;
        box-shadow: none;
    }
    .custom-select-options {
        max-height: 220px;
        overflow-y: auto;
    }
    .custom-select-option {
        padding: 10px 14px;
        font-size: 0.9rem;
        color: #334155;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .custom-select-option:hover {
        background-color: #f1f5f9;
    }
    .custom-select-option.selected {
        background-color: #e0e7ff;
        color: #4f46e5;
        font-weight: 600;
    }
    .custom-select-option[disabled] {
        opacity: 0.5;
        cursor: not-allowed;
        background-color: #f8fafc;
    }
    
    .custom-div-icon {
        background: none !important;
        border: none !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    /* KTP Scanner Styling */
    @keyframes ktpScanLine {
        0% { top: 0%; opacity: 0.9; }
        50% { top: 96%; opacity: 1; }
        100% { top: 0%; opacity: 0.9; }
    }
    .ktp-tab-btn.active {
        background: white !important;
        color: #4f46e5 !important;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08) !important;
    }
    .ktp-tab-btn:not(.active) {
        background: transparent !important;
        color: #64748b !important;
    }
    .ktp-tab-btn:hover:not(.active) {
        color: #1e293b !important;
    }
</style>
@endsection

@section('content')
@if(Auth::user()->level === 'admin')
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="card-title">
                <i class="fa-solid fa-chart-simple"></i>
                <span>Top 10 Teknisi Bulan Ini ({{ \Carbon\Carbon::now()->translatedFormat('F Y') }})</span>
            </div>
        </div>
        <div class="card-body" style="padding: 24px; display: flex; justify-content: center; align-items: center; min-height: 200px;">
            @if(count($topTeknisi) > 0)
                <div style="width: 100%; max-width: 800px; height: 320px; position: relative;">
                    <canvas id="topTeknisiChart"></canvas>
                </div>
            @else
                <div style="text-align: center; color: var(--text-gray); padding: 40px 0; width: 100%;">
                    <i class="fa-solid fa-chart-bar" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 12px; display: block;"></i>
                    <p style="font-weight: 600; font-size: 0.95rem;">Belum ada data pekerjaan selesai (pemasangan / tiket) di bulan ini.</p>
                    <p style="font-size: 0.8rem; color: #94a3b8; margin-top: 4px;">Data akan muncul secara realtime setelah teknisi menyelesaikan pemasangan atau tiket gangguan.</p>
                </div>
            @endif
        </div>
    </div>
@endif
@if(in_array(Auth::user()->level, ['admin', 'noc']))
    <!-- Card Cetak Laporan Order Pemasangan -->
    <div class="card" style="margin-bottom: 24px; width: 100%;">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-file-pdf"></i>
                <span>Cetak Laporan Order Pemasangan Baru</span>
            </div>
        </div>
        <form action="{{ route('admin.order_pemasangan.print') }}" method="GET" target="_blank" style="padding: 24px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; align-items: flex-end;">
                <div class="form-group" style="margin-bottom:0;">
                    <label for="report_teknisi">Pilih Teknisi</label>
                    <select name="id_teknisi" id="report_teknisi" class="form-control" style="height: 44px; border-radius: 12px;" required>
                        <option value="semua">-- Semua Teknisi --</option>
                        <option value="0">Semua Teknisi (Unassigned)</option>
                        @foreach($teknisis as $tek)
                            <option value="{{ $tek->id }}">{{ $tek->nama_user }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label for="report_sales">Pilih Sales</label>
                    <select name="id_sales" id="report_sales" class="form-control" style="height: 44px; border-radius: 12px;" required>
                        <option value="semua">-- Semua Sales --</option>
                        @foreach($salesList as $s)
                            <option value="{{ $s->id }}">{{ $s->nama_user }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label for="report_status">Status Order</label>
                    <select name="status" id="report_status" class="form-control" style="height: 44px; border-radius: 12px;">
                        <option value="semua" selected>Semua Status</option>
                        <option value="pending">Pending (Menunggu ACC)</option>
                        <option value="approved">ACC (Dalam Pemasangan)</option>
                        <option value="installed">Selesai Dipasang (Verifikasi Admin)</option>
                        <option value="completed">Selesai & Aktif</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label for="report_tgl_mulai">Tanggal Mulai</label>
                    <input type="date" name="tgl_mulai" id="report_tgl_mulai" class="form-control" value="{{ date('Y-m-01') }}" style="height: 44px; border-radius: 12px;">
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label for="report_tgl_selesai">Tanggal Selesai</label>
                    <input type="date" name="tgl_selesai" id="report_tgl_selesai" class="form-control" value="{{ date('Y-m-d') }}" style="height: 44px; border-radius: 12px;">
                </div>

                <div style="margin-bottom:0;">
                    <button type="submit" class="btn btn-primary" style="height: 44px; border-radius: 12px; width: 100%; justify-content: center; gap: 8px;">
                        <i class="fa-solid fa-print"></i> Cetak Laporan
                    </button>
                </div>
            </div>
        </form>
    </div>
@endif

<div class="order-grid">
    <!-- Kolom Kiri: Tabel Order Pemasangan -->
    <div class="card" style="margin: 0;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="card-title">
                <i class="fa-solid fa-truck-ramp-box"></i>
                <span>Daftar Order Pemasangan Baru</span>
            </div>
            @if(in_array(Auth::user()->level, ['admin', 'noc', 'sales', 'mitra']))
                <button type="button" class="btn btn-primary" onclick="openAddOrderModal()" style="padding: 8px 16px; font-size: 0.85rem; font-weight: 600; border-radius: 10px; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-plus"></i> Tambah Pemasangan Baru
                </button>
            @endif
        </div>

        <!-- Search & Row Limiter -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-top: 16px; margin-bottom:16px; flex-wrap:wrap; gap:12px; padding: 0 4px;">
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
                    <input type="text" id="tableSearch" class="form-control" placeholder="Cari data..." style="padding-left:36px; height:40px; border-radius:10px; width:100%;">
                </div>
            </div>
        </div>

        <div class="table-container">
            <table class="table" id="ordersTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Data Pelanggan</th>
                        <th>Alamat</th>
                        <th>GPS & Jadwal</th>
                        @if(Auth::user()->level !== 'teknisi')
                            <th>KTP & Bukti Pasang</th>
                        @endif
                        <th>Status / Sales / Teknisi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $index => $row)
                        <tr style="{{ ($row->status === 'rejected' || $row->status === 'cancelled') ? 'background-color: #fef2f2;' : '' }}">
                            <td>{{ $index + 1 }}</td>
                             <td>
                                <strong>{{ $row->nama }}</strong><br>
                                @if(!empty($row->warnings))
                                    <span class="badge" onclick='openDeletedReasonModal({!! json_encode($row->warnings) !!})' style="background-color: #fef2f2; color: #dc2626; border: 1px solid #fee2e2; font-size: 0.75rem; font-weight: bold; padding: 3px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px; margin-top: 4px; margin-bottom: 4px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.backgroundColor='#fee2e2'" onmouseout="this.style.backgroundColor='#fef2f2'">
                                        <i class="fa-solid fa-triangle-exclamation"></i> Kemiripan Eks-Pelanggan ({{ count($row->warnings) }})
                                    </span><br>
                                @endif
                                <span style="font-size: 0.8rem; color: var(--text-gray);">NIK: {{ $row->nik }}</span><br>
                                <span style="font-size: 0.8rem; color: var(--text-gray);">Telp: {{ $row->no_telp ?? '-' }}</span><br>
                                <span style="font-size: 0.8rem; color: var(--text-gray);">
                                    <strong>Paket Diminta:</strong> 
                                    @if($row->paketDetail)
                                        <span class="badge" style="background-color: #f1f5f9; color: #4f46e5; border: 1px solid #e2e8f0; font-size: 0.75rem; font-weight: bold; padding: 2px 6px; border-radius: 4px;">
                                            {{ $row->paketDetail->nama_paket }}
                                        </span>
                                    @else
                                        <span style="color:#dc2626; font-style:italic;">Belum Pilih Paket</span>
                                    @endif
                                </span>
                                @if($row->status === 'approved' || $row->status === 'installed' || $row->status === 'completed')
                                    @php
                                        $pelangganObj = \App\Models\Pelanggan::with('odpDetail')->where('nik', $row->nik)->first();
                                    @endphp
                                    @if($pelangganObj && $pelangganObj->odpDetail)
                                        <br>
                                        <span style="font-size: 0.8rem; color: var(--text-gray);">
                                            <strong>ODP Rekomendasi:</strong>
                                            <span class="badge" style="background-color: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; font-size: 0.75rem; font-weight: bold; padding: 2px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 3px;">
                                                <i class="fa-solid fa-map-pin" style="font-size: 0.7rem;"></i> {{ $pelangganObj->odpDetail->nama_odp }}
                                            </span>
                                        </span>
                                    @endif
                                @endif
                            </td>
                            <td>
                                <strong style="font-size: 0.8rem;">KTP:</strong> <span style="font-size: 0.8rem;">{{ $row->alamat_ktp }}</span><br>
                                <strong style="font-size: 0.8rem;">Pasang:</strong> <span style="font-size: 0.8rem;">{{ $row->alamat_pemasangan }}</span>
                            </td>
                            <td>
                                <span class="badge" style="background-color: #f1f5f9; color: #475569; padding: 4px 8px; border-radius: 6px; font-size: 0.8rem; font-weight: 600;">
                                    <i class="fa-solid fa-location-dot"></i> {{ $row->koordinat_pemasangan }}
                                </span>
                                @if($row->koordinat_pemasangan)
                                    <br>
                                    <a href="https://www.google.com/maps?q={{ urlencode($row->koordinat_pemasangan) }}" target="_blank" class="btn btn-info" style="padding: 4px 8px; font-size: 0.75rem; border-radius: 8px; margin-top: 4px; display: inline-flex; border: 1px solid #dbeafe;">
                                        <i class="fa-solid fa-map-location-dot"></i> Buka Maps
                                    </a>
                                @endif
                                <br>
                                <span style="font-size: 0.8rem; color: var(--text-gray); display: inline-block; margin-top: 6px;">
                                    <i class="fa-solid fa-calendar-days"></i> {{ $row->jadwal_pemasangan ? \Carbon\Carbon::parse($row->jadwal_pemasangan)->translatedFormat('d M Y H:i') : 'Sesuai Antrean' }}
                                </span>
                            </td>
                            @if(Auth::user()->level !== 'teknisi')
                                <td>
                                    @php
                                        // Collect all images for lightbox preview
                                        $lightboxImages = [];
                                        if ($row->foto_ktp) {
                                            $lightboxImages[] = [
                                                'title' => 'Foto KTP ' . $row->nama,
                                                'url' => route('admin.order_pemasangan.ktp', $row->foto_ktp)
                                            ];
                                        }
                                        
                                        $docs = [];
                                        if ($row->foto_dokumentasi) {
                                            $decoded = json_decode($row->foto_dokumentasi, true);
                                            if (is_array($decoded)) {
                                                $docs = $decoded;
                                            } else {
                                                $docs = [$row->foto_dokumentasi];
                                            }
                                        }
                                        
                                        foreach ($docs as $idx => $doc) {
                                            $lightboxImages[] = [
                                                'title' => 'Bukti Pasang ' . (count($docs) > 1 ? ($idx + 1) : '') . ' - ' . $row->nama,
                                                'url' => route('admin.order_pemasangan.dokumentasi', $doc)
                                            ];
                                        }
                                        
                                        $jsonImages = json_encode($lightboxImages);
                                    @endphp
                                    <div style="display: flex; flex-direction: column; gap: 6px;">
                                        @if($row->foto_ktp)
                                            <button type="button" class="btn btn-info" style="padding: 4px 8px; font-size: 0.75rem; border-radius: 8px; justify-content: center; width: 100%;" onclick='openImageLightbox({!! $jsonImages !!}, 0)'>
                                                <i class="fa-solid fa-image"></i> KTP
                                            </button>
                                        @else
                                            <span style="color:var(--text-gray); font-style:italic; font-size:0.75rem;">KTP: Tidak Ada</span>
                                        @endif

                                        @if(count($docs) > 0)
                                            @foreach($docs as $idx => $doc)
                                                @php
                                                    $buktiIndex = $row->foto_ktp ? ($idx + 1) : $idx;
                                                @endphp
                                                <button type="button" class="btn btn-success" style="padding: 4px 8px; font-size: 0.75rem; border-radius: 8px; justify-content: center; width: 100%; background-color: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0;" onclick='openImageLightbox({!! $jsonImages !!}, {{ $buktiIndex }})'>
                                                    <i class="fa-solid fa-camera"></i> Bukti {{ count($docs) > 1 ? ($idx + 1) : '' }}
                                                </button>
                                            @endforeach
                                        @elseif($row->status === 'installed' || $row->status === 'completed')
                                            <span style="color:var(--text-gray); font-style:italic; font-size:0.75rem;">Bukti: Tidak Ada</span>
                                        @endif
                                    </div>
                                </td>
                            @endif
                            <td>
                                @if($row->status === 'pending')
                                    <span class="status-badge status-pending">Pending (Menunggu ACC)</span>
                                    @if($row->alasan_pending)
                                        <div style="font-size: 0.7rem; color: #d97706; margin-top: 4px; font-weight: 500;">
                                            <strong>Alasan Pending:</strong> {{ $row->alasan_pending }}
                                        </div>
                                    @endif
                                @elseif($row->status === 'approved')
                                    <span class="status-badge status-approved">ACC (Dalam Pemasangan)</span>
                                @elseif($row->status === 'installed')
                                    <span class="status-badge status-installed">Selesai Dipasang (Verifikasi Admin)</span>
                                @elseif($row->status === 'completed')
                                    <span class="status-badge status-completed">Selesai & Aktif</span>
                                @elseif($row->status === 'rejected')
                                    <span class="status-badge status-rejected">Ditolak</span>
                                    @if($row->alasan_ditolak)
                                        <div style="font-size: 0.7rem; color: #dc2626; margin-top: 4px; font-weight: 500;">
                                            <strong>Alasan:</strong> {{ $row->alasan_ditolak }}
                                        </div>
                                    @endif
                                @elseif($row->status === 'cancelled')
                                    <span class="status-badge status-cancelled">Dibatalkan</span>
                                    @if($row->alasan_batal)
                                        <div style="font-size: 0.7rem; color: #64748b; margin-top: 4px; font-weight: 500;">
                                            <strong>Alasan:</strong> {{ $row->alasan_batal }}
                                        </div>
                                    @endif
                                @endif
                                <br>
                                <span style="font-size: 0.75rem; color: var(--text-gray); display: inline-block; margin-top: 4px;">
                                    <strong>Sales:</strong> {{ $row->sales?->nama_user ?? 'N/A' }}
                                </span><br>
                                <span style="font-size: 0.75rem; color: var(--text-gray);">
                                    <strong>Teknisi:</strong> 
                                    @if($row->id_teknisi === 0)
                                        <span class="badge" style="background-color: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd; font-size: 0.7rem; font-weight: bold; padding: 2px 6px; border-radius: 4px;">Semua Teknisi</span>
                                    @else
                                        {{ $row->teknisi?->nama_user ?? 'Belum Ditugaskan' }}
                                    @endif
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 6px;">
                                    <!-- Admin / NOC Actions -->
                                    @if(Auth::user()->level === 'admin' || Auth::user()->level === 'noc')
                                        @if($row->status === 'pending')
                                            <button class="btn btn-primary" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 8px; width: 100%; justify-content: center;" 
                                                onclick='openApproveModal({!! json_encode($row) !!})'>
                                                <i class="fa-solid fa-check"></i> ACC
                                            </button>
                                            <button class="btn btn-danger" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 8px; width: 100%; justify-content: center; background-color: #dc2626; border-color: #dc2626; color: white;" 
                                                onclick="openRejectOrderModal('{{ $row->id }}')">
                                                <i class="fa-solid fa-ban"></i> Tolak
                                            </button>
                                        @endif
                                        @if($row->status === 'approved')
                                            <button class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 8px; width: 100%; justify-content: center;" 
                                                onclick="openAssignModal('{{ $row->id }}')">
                                                <i class="fa-solid fa-user-plus"></i> {{ is_null($row->id_teknisi) ? 'Tugaskan Teknisi' : 'Ubah Teknisi' }}
                                            </button>
                                            <button class="btn btn-danger" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 8px; width: 100%; justify-content: center; background-color: #dc2626; border-color: #dc2626; color: white;" 
                                                onclick="openCancelOrderModal('{{ $row->id }}')">
                                                <i class="fa-solid fa-rectangle-xmark"></i> Batal Pemasangan
                                            </button>
                                            <button class="btn" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 8px; width: 100%; justify-content: center; background: linear-gradient(135deg, #f59e0b, #d97706); border: none; color: white;" 
                                                onclick="openPendingOrderModal('{{ $row->id }}')">
                                                <i class="fa-solid fa-clock"></i> Pending Pemasangan
                                            </button>
                                        @endif
                                        @if($row->status === 'rejected')
                                            <button class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 8px; width: 100%; justify-content: center; opacity: 0.6; cursor: not-allowed; background-color: #fee2e2; color: #dc2626; border: 1px solid #fecaca;" disabled>
                                                <i class="fa-solid fa-ban"></i> Ditolak
                                            </button>
                                        @elseif($row->status === 'cancelled')
                                            <button class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 8px; width: 100%; justify-content: center; opacity: 0.6; cursor: not-allowed; background-color: #fee2e2; color: #dc2626; border: 1px solid #fecaca;" disabled>
                                                <i class="fa-solid fa-rectangle-xmark"></i> Batal Pemasangan
                                            </button>
                                        @endif
                                    @endif

                                     <!-- Technician Actions -->
                                     @if(Auth::user()->level === 'teknisi')
                                         @if($row->status === 'approved')
                                             @if($row->id_teknisi === 0)
                                                 @php
                                                     $techActiveCount = \App\Models\OrderPemasangan::where('id_teknisi', Auth::id())
                                                         ->whereIn('status', ['approved', 'installed'])
                                                         ->count();
                                                 @endphp
                                                 @if($techActiveCount >= 2)
                                                     <button type="button" class="btn btn-primary" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 8px; width: 100%; justify-content: center; opacity: 0.5; cursor: not-allowed;" disabled title="Anda sudah memiliki 2 order pemasangan aktif! Selesaikan terlebih dahulu sebelum mengambil order baru.">
                                                         <i class="fa-solid fa-hand-holding-hand"></i> Ambil Order (Maks 2 Order Aktif)
                                                     </button>
                                                 @else
                                                     <form action="{{ route('admin.order_pemasangan.claim') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengambil order pemasangan ini?')">
                                                         @csrf
                                                         <input type="hidden" name="id_order" value="{{ $row->id }}">
                                                         <button type="submit" class="btn btn-primary" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 8px; width: 100%; justify-content: center; background: linear-gradient(135deg, #0ea5e9, #0284c7);">
                                                             <i class="fa-solid fa-hand-holding-hand"></i> Ambil Order {{ $techActiveCount == 1 ? '(Slot 2/2)' : '' }}
                                                         </button>
                                                     </form>
                                                 @endif
                                             @elseif($row->id_teknisi === Auth::id())
                                                 <button type="button" class="btn btn-success" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 8px; width: 100%; justify-content: center;" onclick="openCompleteModal('{{ $row->id }}')">
                                                     <i class="fa-solid fa-camera"></i> Selesai Pasang
                                                 </button>
                                             @endif
                                          @endif
                                      @endif
                                     
                                     <!-- NOC Selesai Pasang Action -->
                                     @if(Auth::user()->level === 'noc' && $row->status === 'approved')
                                         <button type="button" class="btn btn-success" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 8px; width: 100%; justify-content: center;" onclick="openCompleteModal('{{ $row->id }}')">
                                             <i class="fa-solid fa-camera"></i> Selesai Pasang
                                         </button>
                                     @endif
 
                                     <!-- Admin/NOC Verification Completion (ACC Selesai) -->
                                     @if((Auth::user()->level === 'admin' || Auth::user()->level === 'noc') && $row->status === 'installed')
                                         <form action="{{ route('admin.order_pemasangan.confirm') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengonfirmasi selesai pemasangan untuk pelanggan ini? Pastikan perangkat benar-benar hidup dan normal.')">
                                             @csrf
                                             <input type="hidden" name="id_order" value="{{ $row->id }}">
                                             <button type="submit" class="btn btn-success" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 8px; width: 100%; justify-content: center; background-color: #16a34a; color: white;">
                                                 <i class="fa-solid fa-circle-check"></i> Konfirmasi Selesai
                                             </button>
                                         </form>
                                     @endif
 
                                     @if($row->status === 'completed')
                                         <span style="color:#16a34a; font-size:0.8rem; font-weight:600;"><i class="fa-solid fa-check-double"></i> Selesai & Aktif</span>
                                     @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ Auth::user()->level === 'teknisi' ? 6 : 7 }}" style="text-align: center; color: var(--text-gray); padding: 30px;">
                                Belum ada order pemasangan baru yang masuk.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div id="ordersPagination"></div>
    </div>



</div>

<!-- Modal Tugaskan Teknisi (Admin Only) -->
<div class="modal" id="assignModal">
    <div class="modal-content" style="width: min(420px, 100%);">
        <div class="modal-header">
            <h3>Tugaskan Teknisi</h3>
            <button class="modal-close" onclick="closeAssignModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('admin.order_pemasangan.assign') }}" method="POST">
                @csrf
                <input type="hidden" name="id_order" id="assign_id">

                <div class="form-group">
                    <label for="id_teknisi">Pilih Teknisi Lapangan *</label>
                    <select id="id_teknisi" name="id_teknisi" class="form-control" required>
                        <option value="">-- Pilih Teknisi --</option>
                        <option value="0">-- Semua Teknisi (All) --</option>
                        @foreach($teknisis as $tek)
                            @php
                                $actCount = $tek->active_orders_count ?? (isset($tek->has_active_order) && $tek->has_active_order ? 2 : 0);
                                $isMax = $actCount >= 2;
                            @endphp
                            <option value="{{ $tek->id }}" {{ $isMax ? 'disabled style=color:var(--text-gray);' : '' }}>
                                {{ $tek->nama_user }} ({{ $tek->username }})
                                @if($isMax)
                                    — [Penuh: 2 Order Aktif]
                                @elseif($actCount == 1)
                                    — [1 Order Aktif (Sisa 1 Slot)]
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:24px;">
                    <button type="button" class="btn btn-secondary" onclick="closeAssignModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tolak Order Pemasangan (Admin Only) -->
<div class="modal" id="rejectOrderModal">
    <div class="modal-content" style="width: min(450px, 100%); border-top: 5px solid #dc2626; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.15);">
        <div class="modal-header" style="background: linear-gradient(135deg, #dc2626, #b91c1c) !important; color: white !important; padding: 16px 24px; border-bottom: none;">
            <h3 style="color: white !important; display: flex; align-items: center; gap: 8px; margin: 0; font-size: 1.15rem; font-weight: 700;">
                <i class="fa-solid fa-ban" style="color: white !important;"></i> Tolak Order Pemasangan
            </h3>
            <button class="modal-close" onclick="closeRejectOrderModal()" style="font-size: 1.5rem; color: white !important; opacity: 0.9; hover:opacity: 1; cursor: pointer;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 24px;">
            <form action="{{ route('admin.order_pemasangan.reject') }}" method="POST">
                @csrf
                <input type="hidden" name="id_order" id="reject_id">

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="alasan_ditolak" style="font-weight: 600; color: #7f1d1d; display: block; margin-bottom: 8px; font-size: 0.9rem;">Alasan Penolakan *</label>
                    <textarea id="alasan_ditolak" name="alasan_ditolak" class="form-control" rows="4" required placeholder="Masukkan alasan kenapa order pemasangan ini ditolak..." style="border-radius: 10px; border: 1px solid #fca5a5; padding: 12px; font-size: 0.9rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#dc2626'" onblur="this.style.borderColor='#fca5a5'"></textarea>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:24px;">
                    <button type="button" class="btn btn-secondary" onclick="closeRejectOrderModal()" style="border-radius: 10px; padding: 8px 16px; font-size: 0.85rem;">Batal</button>
                    <button type="submit" class="btn btn-danger" style="background: linear-gradient(135deg, #dc2626, #b91c1c); border: none; color: white; border-radius: 10px; padding: 8px 18px; font-size: 0.85rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-ban"></i> Tolak Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Batal Pemasangan (Admin Only) -->
<div class="modal" id="cancelOrderModal">
    <div class="modal-content" style="width: min(450px, 100%); border-top: 5px solid #dc2626; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.15);">
        <div class="modal-header" style="background: linear-gradient(135deg, #dc2626, #b91c1c) !important; color: white !important; padding: 16px 24px; border-bottom: none;">
            <h3 style="color: white !important; display: flex; align-items: center; gap: 8px; margin: 0; font-size: 1.15rem; font-weight: 700;">
                <i class="fa-solid fa-rectangle-xmark" style="color: white !important;"></i> Batalkan Pemasangan
            </h3>
            <button class="modal-close" onclick="closeCancelOrderModal()" style="font-size: 1.5rem; color: white !important; opacity: 0.9; hover:opacity: 1; cursor: pointer;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 24px;">
            <form action="{{ route('admin.order_pemasangan.cancel') }}" method="POST">
                @csrf
                <input type="hidden" name="id_order" id="cancel_id">

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="alasan_batal" style="font-weight: 600; color: #334155; display: block; margin-bottom: 8px; font-size: 0.9rem;">Alasan Pembatalan *</label>
                    <textarea id="alasan_batal" name="alasan_batal" class="form-control" rows="4" required placeholder="Masukkan alasan kenapa pemasangan ini dibatalkan..." style="border-radius: 10px; border: 1px solid #cbd5e1; padding: 12px; font-size: 0.9rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#dc2626'" onblur="this.style.borderColor='#cbd5e1'"></textarea>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:24px;">
                    <button type="button" class="btn btn-secondary" onclick="closeCancelOrderModal()" style="border-radius: 10px; padding: 8px 16px; font-size: 0.85rem;">Batal</button>
                    <button type="submit" class="btn" style="background: linear-gradient(135deg, #dc2626, #b91c1c); border: none; color: white; border-radius: 10px; padding: 8px 18px; font-size: 0.85rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-rectangle-xmark"></i> Batalkan Pemasangan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Pending Pemasangan (Admin Only) -->
<div class="modal" id="pendingOrderModal">
    <div class="modal-content" style="width: min(450px, 100%); border-top: 5px solid #d97706; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.15);">
        <div class="modal-header" style="background: linear-gradient(135deg, #f59e0b, #d97706) !important; color: white !important; padding: 16px 24px; border-bottom: none;">
            <h3 style="color: white !important; display: flex; align-items: center; gap: 8px; margin: 0; font-size: 1.15rem; font-weight: 700;">
                <i class="fa-solid fa-clock" style="color: white !important;"></i> Pending Pemasangan
            </h3>
            <button class="modal-close" onclick="closePendingOrderModal()" style="font-size: 1.5rem; color: white !important; opacity: 0.9; hover:opacity: 1; cursor: pointer;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 24px;">
            <form action="{{ route('admin.order_pemasangan.pending') }}" method="POST">
                @csrf
                <input type="hidden" name="id_order" id="pending_id">

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="alasan_pending" style="font-weight: 600; color: #92400e; display: block; margin-bottom: 8px; font-size: 0.9rem;">Alasan Pending *</label>
                    <textarea id="alasan_pending" name="alasan_pending" class="form-control" rows="4" required placeholder="Masukkan alasan kenapa pemasangan ini dipending..." style="border-radius: 10px; border: 1px solid #fde68a; padding: 12px; font-size: 0.9rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#d97706'" onblur="this.style.borderColor='#fde68a'"></textarea>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:24px;">
                    <button type="button" class="btn btn-secondary" onclick="closePendingOrderModal()" style="border-radius: 10px; padding: 8px 16px; font-size: 0.85rem;">Batal</button>
                    <button type="submit" class="btn" style="background: linear-gradient(135deg, #f59e0b, #d97706); border: none; color: white; border-radius: 10px; padding: 8px 18px; font-size: 0.85rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-clock"></i> Pending Pemasangan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal ACC / Approve Pemasangan & PPPoE Config (Admin Only) -->
<div class="modal" id="approveModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Persetujuan (ACC) & Setting PPPoE Pelanggan</h3>
            <button class="modal-close" onclick="closeApproveModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="approveOrderForm" action="{{ route('admin.order_pemasangan.approve') }}" method="POST">
                @csrf
                <input type="hidden" name="id_order" id="approve_id">

                <div style="background-color: #eff6ff; padding: 12px 16px; border-radius: 12px; margin-bottom: 18px; border: 1px solid #dbeafe;">
                    <span style="font-size: 0.8rem; color: #1e40af; font-weight: 600;">Data Order:</span><br>
                    <strong id="approve_label_nama" style="font-size: 1rem; color: #1e3a8a;"></strong><br>
                    <span id="approve_label_nik" style="font-size: 0.85rem; color: #1e40af;"></span><br>
                    <span id="approve_label_alamat" style="font-size: 0.85rem; color: #1e40af;"></span>
                </div>

                <!-- Warning Mantan Pelanggan Blacklist -->
                <div id="approve_warning_box" style="display: none; background-color: #fef2f2; border: 1px solid #fee2e2; border-left: 4px solid #dc2626; padding: 16px; border-radius: 12px; margin-bottom: 18px;">
                    <h4 style="margin: 0 0 8px 0; color: #991b1b; display: flex; align-items: center; gap: 6px; font-weight: 700; font-size: 0.95rem;">
                        <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.1rem; color: #dc2626;"></i> 
                        PERINGATAN: Kemiripan Eks-Pelanggan Terdeteksi!
                    </h4>
                    <div id="approve_warning_list" style="font-size: 0.85rem; color: #7f1d1d; line-height: 1.6; display: flex; flex-direction: column; gap: 8px;">
                        <!-- JS populated warnings -->
                    </div>
                </div>

                <div class="form-group">
                    <label for="username">Username PPPoE (Akun Client) *</label>
                    <input type="text" id="username" name="username" class="form-control" required placeholder="Contoh: budi_s">
                    <small style="color: var(--text-gray);">Akan digunakan untuk login ke WiFi/PPPoE client.</small>
                </div>

                <div class="form-group">
                    <label for="password">Password PPPoE *</label>
                    <input type="text" id="password" name="password" class="form-control" required placeholder="Contoh: 123456">
                </div>

                <div class="form-group">
                    <label for="no_telp_approve">No WhatsApp Aktif *</label>
                    <input type="text" id="no_telp_approve" name="no_telp" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="id_mikrotik">Pilih Router Mikrotik *</label>
                    <select id="id_mikrotik" name="id_mikrotik" class="form-control" required onchange="filterPaketByMikrotik(this.value)">
                        <option value="">-- Pilih Mikrotik --</option>
                        @foreach($mikrotiks as $m)
                            <option value="{{ $m->id_mikrotik }}">{{ $m->nama_mikrotik }} ({{ $m->ip }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="paket">Pilih Paket Internet *</label>
                    <select id="paket" name="paket" class="form-control" required disabled>
                        <option value="">-- Pilih Mikrotik Terlebih Dahulu --</option>
                        @foreach($pakets as $p)
                            <option value="{{ $p->id_paket }}" data-profile="{{ $p->id_pmikrotik }}">{{ $p->nama_paket }} ({{ $p->harga }} / Bulan)</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        <label style="margin: 0; font-weight: 600; font-size: 0.85rem; color: #334155;">Pilih ODP *</label>
                        <button type="button" class="btn btn-info" id="btn_map_odp" onclick="openOdpMapModal()" style="padding: 4px 10px; font-size: 0.75rem; border-radius: 8px; height: auto; display: flex; align-items: center; gap: 4px;">
                            <i class="fa-solid fa-map-location-dot"></i> Cari ODP Terdekat
                        </button>
                    </div>
                    <!-- Hidden input to store actual selected value -->
                    <input type="hidden" name="odp" id="approve_odp_id">

                    <!-- Custom Searchable Dropdown -->
                    <div class="custom-select-container" id="approve_odp_select">
                        <div class="custom-select-trigger" onclick="toggleApproveOdpDropdown(event)">
                            <span id="approve_odp_select_text">-- Pilih ODP (Opsional) --</span>
                            <i class="fa-solid fa-chevron-down" style="font-size: 0.8rem; color: var(--text-gray);"></i>
                        </div>
                        <div class="custom-select-dropdown" id="approve_odp_dropdown">
                            <div class="custom-select-search-wrapper">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" id="approve_search_odp" class="form-control custom-select-search-input" placeholder="Cari ODP..." autocomplete="off">
                            </div>
                            <div class="custom-select-options" id="approve_odp_options">
                                <div class="custom-select-option selected" data-value="" data-text="-- Pilih ODP (Opsional) --">
                                    -- Pilih ODP (Opsional) --
                                </div>
                                @foreach($odps as $o)
                                    @php
                                        $remaining = $o->port_odp - ($o->pelanggans_count ?? 0);
                                    @endphp
                                    <div class="custom-select-option {{ $remaining <= 0 ? 'disabled' : '' }}" 
                                         data-value="{{ $o->id_odp }}" 
                                         data-text="{{ $o->nama_odp }} (Sisa Port: {{ $remaining }} / {{ $o->port_odp }})"
                                         {!! $remaining <= 0 ? 'style="opacity: 0.5; pointer-events: none; background: #f8fafc;"' : '' !!}>
                                        <strong>{{ $o->nama_odp }}</strong> 
                                        <span style="font-size:0.8rem; color: var(--text-gray);">
                                            (Sisa Port: {{ $remaining }} / {{ $o->port_odp }})
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label>Pilih Branch</label>
                        <input type="hidden" name="id_branch" id="approve_branch_id">
                        
                        <div class="custom-select-container" id="approve_branch_select">
                            <div class="custom-select-trigger" onclick="toggleApproveBranchDropdown(event)">
                                <span id="approve_branch_select_text">-- Tanpa Branch --</span>
                                <i class="fa-solid fa-chevron-down" style="font-size: 0.8rem; color: var(--text-gray);"></i>
                            </div>
                            <div class="custom-select-dropdown" id="approve_branch_dropdown">
                                <div class="custom-select-search-wrapper">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                    <input type="text" id="approve_search_branch" class="form-control custom-select-search-input" placeholder="Cari Branch..." autocomplete="off">
                                </div>
                                <div class="custom-select-options" id="approve_branch_options">
                                    <div class="custom-select-option selected" data-value="" data-text="-- Tanpa Branch --">
                                        -- Tanpa Branch --
                                    </div>
                                    @foreach($branches as $b)
                                        <div class="custom-select-option" data-value="{{ $b->id }}" data-text="{{ $b->nama_branch }}">
                                            {{ $b->nama_branch }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Pilih Sub Branch</label>
                        <input type="hidden" name="id_sub_branch" id="approve_sub_branch_id">
                        
                        <div class="custom-select-container" id="approve_sub_branch_select">
                            <div class="custom-select-trigger" onclick="toggleApproveSubBranchDropdown(event)">
                                <span id="approve_sub_branch_select_text">-- Tanpa Sub Branch --</span>
                                <i class="fa-solid fa-chevron-down" style="font-size: 0.8rem; color: var(--text-gray);"></i>
                            </div>
                            <div class="custom-select-dropdown" id="approve_sub_branch_dropdown">
                                <div class="custom-select-search-wrapper">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                    <input type="text" id="approve_search_sub_branch" class="form-control custom-select-search-input" placeholder="Cari Sub Branch..." autocomplete="off">
                                </div>
                                <div class="custom-select-options" id="approve_sub_branch_options">
                                    <div class="custom-select-option selected" data-value="" data-text="-- Tanpa Sub Branch --">
                                        -- Tanpa Sub Branch --
                                    </div>
                                    @foreach($subBranches as $sb)
                                        <div class="custom-select-option" data-value="{{ $sb->id }}" data-branch="{{ $sb->id_branch }}" data-text="{{ $sb->nama_sub_branch }}">
                                            {{ $sb->nama_sub_branch }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:24px;">
                    <button type="button" class="btn btn-secondary" onclick="closeApproveModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">ACC & Daftarkan Pelanggan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detail Alasan Penghapusan Pelanggan -->
<div class="modal" id="deletedReasonModal" style="z-index: 10007;">
    <div class="modal-content" style="width: min(500px, 95vw); max-width: 500px;">
        <div class="modal-header" style="background: #dc2626; color: white; display: flex; align-items: center; justify-content: space-between; padding: 20px 24px;">
            <h3 style="margin: 0; font-family: 'Outfit', sans-serif; font-size: 1.2rem; font-weight: 700;">Detail Riwayat Pelanggan</h3>
            <button class="modal-close" onclick="closeDeletedReasonModal()" style="color: white; border: none; background: none; font-size: 1.2rem; cursor: pointer;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 20px;">
            <div id="deleted_reason_content" style="display: flex; flex-direction: column; gap: 12px;">
                <!-- Populated by JS -->
            </div>
            <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="closeDeletedReasonModal()">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Map ODP -->
<div class="modal" id="odpMapModal" style="z-index: 10005;">
    <div class="modal-content" style="width: min(850px, 95vw); max-width: 850px;">
        <div class="modal-header" style="background: var(--primary-gradient); color: white; display: flex; align-items: center; justify-content: space-between; padding: 20px 24px;">
            <h3 style="margin: 0; font-family: 'Outfit', sans-serif; font-size: 1.2rem; font-weight: 700;">Pilih ODP Terdekat (Peta Topologi)</h3>
            <button class="modal-close" onclick="closeOdpMapModal()" style="color: white; border: none; background: none; font-size: 1.2rem; cursor: pointer;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 24px; display: flex; flex-direction: column; gap: 16px;">
            <div style="font-size: 0.9rem; color: #475569; display: flex; align-items: center; gap: 8px; background: #eff6ff; padding: 12px; border-radius: 8px; border: 1px solid #dbeafe;">
                <i class="fa-solid fa-circle-info" style="color: var(--primary-color);"></i>
                <span>Berikut peta lokasi koordinat pasang dan sebaran ODP. Silakan klik penanda ODP untuk memilih ODP terdekat.</span>
            </div>
            
            <div id="odpMap" style="height: 450px; width: 100%; border-radius: 12px; border: 1px solid #cbd5e1; z-index: 1;"></div>
            
            <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeOdpMapModal()">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form Pemasangan Baru (Admin, NOC, Sales & Mitra) -->
@if(in_array(Auth::user()->level, ['admin', 'noc', 'sales', 'mitra']))
<div class="modal" id="addOrderModal" style="z-index: 10006;">
    <div class="modal-content" style="width: min(600px, 95vw); max-width: 600px;">
        <div class="modal-header" style="background: var(--primary-gradient); color: white; padding: 20px 24px; display: flex; align-items: center; justify-content: space-between;">
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.2rem; font-weight: 700; margin: 0;">Form Pemasangan Baru</h3>
            <button class="modal-close" onclick="closeAddOrderModal()" style="color: white; border: none; background: none; font-size: 1.2rem; cursor: pointer;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 24px; max-height: 80vh; overflow-y: auto;">
            
            <!-- Banner Pindai e-KTP Otomatis -->
            <div style="background: linear-gradient(135deg, #eff6ff 0%, #e0e7ff 100%); border: 1px dashed #6366f1; border-radius: 12px; padding: 12px 16px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 38px; height: 38px; border-radius: 10px; background: #4f46e5; color: white; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);">
                        <i class="fa-solid fa-id-card"></i>
                    </div>
                    <div>
                        <div style="font-weight: 700; color: #1e1b4b; font-size: 0.9rem;">Pindai e-KTP Otomatis</div>
                        <div style="font-size: 0.78rem; color: #4338ca;">Pindai foto KTP untuk mengisi NIK, Nama & Alamat secara otomatis.</div>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-primary" onclick="openKtpScanModal()" style="font-weight: 600; font-size: 0.82rem; border-radius: 8px; white-space: nowrap; padding: 7px 14px; background: #4f46e5; border-color: #4f46e5; box-shadow: 0 2px 6px rgba(79, 70, 229, 0.25);">
                    <i class="fa-solid fa-camera"></i> Pindai KTP
                </button>
            </div>

            <form action="{{ route('admin.order_pemasangan.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="nik_modal">NIK Pelanggan *</label>
                    <input type="text" id="nik_modal" name="nik" class="form-control" required placeholder="Masukkan NIK 16 digit" value="{{ old('nik') }}">
                </div>

                <div class="form-group">
                    <label for="nama_modal">Nama Lengkap *</label>
                    <input type="text" id="nama_modal" name="nama" class="form-control" required placeholder="Contoh: Budi Santoso" value="{{ old('nama') }}">
                </div>

                <div class="form-group">
                    <label for="no_telp_modal">Nomor WhatsApp Pelanggan *</label>
                    <input type="text" id="no_telp_modal" name="no_telp" class="form-control" required placeholder="Contoh: 081234567890" value="{{ old('no_telp') }}">
                </div>

                <div class="form-group">
                    <label for="paket_order_modal">Paket Internet Yang Diminta *</label>
                    <select id="paket_order_modal" name="paket" class="form-control" required>
                        <option value="">-- Pilih Paket --</option>
                        @foreach($pakets as $p)
                            <option value="{{ $p->id_paket }}" {{ old('paket') == $p->id_paket ? 'selected' : '' }}>
                                {{ $p->nama_paket }} (Rp {{ number_format($p->harga, 0, ',', '.') }} / Bulan)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="alamat_ktp_modal">Alamat KTP *</label>
                    <textarea id="alamat_ktp_modal" name="alamat_ktp" class="form-control" rows="2" required placeholder="Alamat lengkap sesuai KTP">{{ old('alamat_ktp') }}</textarea>
                </div>

                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                        <label for="alamat_pemasangan_modal" style="margin-bottom: 0;">Alamat Pemasangan *</label>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copyAlamatKTPModal()" style="padding: 2px 8px; font-size: 12px;">
                            <i class="fa-solid fa-copy"></i> Sama dengan KTP
                        </button>
                    </div>
                    <textarea id="alamat_pemasangan_modal" name="alamat_pemasangan" class="form-control" rows="2" required placeholder="Alamat lengkap titik pemasangan">{{ old('alamat_pemasangan') }}</textarea>
                </div>

                <div class="form-group">
                    <label for="koordinat_pemasangan_modal">Koordinat Lokasi (Lat, Lng) *</label>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" id="koordinat_pemasangan_modal" name="koordinat_pemasangan" class="form-control" required placeholder="Contoh: -7.12345, 110.12345" value="{{ old('koordinat_pemasangan') }}" style="min-width: 0; flex-grow: 1;">
                        <button type="button" class="btn btn-info" onclick="getGPSCoordinatesModal()" style="flex-shrink: 0; padding: 0 16px;">
                            <i class="fa-solid fa-location-crosshairs"></i> GPS HP
                        </button>
                    </div>
                    <small style="color:var(--text-gray);">Gunakan tombol GPS HP saat berada di lokasi pemasangan secara presisi.</small>
                </div>

                <div class="form-group">
                    <label for="jadwal_pemasangan_modal">Permintaan Jadwal Pasang (Opsional)</label>
                    <input type="datetime-local" id="jadwal_pemasangan_modal" name="jadwal_pemasangan" class="form-control" value="{{ old('jadwal_pemasangan') }}">
                </div>

                <div class="form-group">
                    <label for="foto_ktp_modal">Upload Foto KTP *</label>
                    <input type="file" id="foto_ktp_modal" name="foto_ktp" class="form-control" required accept="image/*" onchange="if(this.files && this.files[0]) updateKtpInputPreview(this.files[0]);">
                    <!-- Preview Thumbnail KTP -->
                    <div id="ktp_preview_container" style="display: none; margin-top: 10px;">
                        <div style="position: relative; display: inline-block;">
                            <img id="ktp_preview_img" src="" alt="Preview KTP" style="max-height: 140px; border-radius: 10px; border: 1px solid #cbd5e1; box-shadow: 0 2px 8px rgba(0,0,0,0.08); display: block;">
                            <span style="position: absolute; top: 6px; left: 6px; background: #16a34a; color: white; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; box-shadow: 0 2px 4px rgba(0,0,0,0.2); display: flex; align-items: center; gap: 4px;">
                                <i class="fa-solid fa-circle-check"></i> Foto KTP Terlampir
                            </span>
                        </div>
                    </div>
                    <small style="color:var(--text-gray);">Maksimal ukuran file 5 MB (Format: jpeg, png, webp).</small>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:24px;">
                    <button type="button" class="btn btn-secondary" onclick="closeAddOrderModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-paper-plane"></i> Kirim Order Pemasangan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Scanner e-KTP (OCR Pelanggan Baru) -->
<div class="modal" id="ktpScannerModal" style="z-index: 10020;">
    <div class="modal-content" style="width: min(640px, 95vw); max-width: 640px;">
        <div class="modal-header" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: white; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 10;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="background: rgba(255,255,255,0.2); width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                    <i class="fa-solid fa-id-card"></i>
                </div>
                <div>
                    <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 700; margin: 0; line-height: 1.2;">Pindai e-KTP Pelanggan</h3>
                    <p style="margin: 0; font-size: 0.78rem; opacity: 0.9; font-weight: 400;">Otomatis isi NIK, Nama Lengkap & Alamat KTP</p>
                </div>
            </div>
            <button class="modal-close" onclick="cancelKtpScan()" type="button" style="color: white; border: none; background: rgba(255,255,255,0.15); width: 32px; height: 32px; border-radius: 50%; font-size: 1.1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 20px; max-height: 84vh; overflow-y: auto;">
            
            <div id="ktp_source_selection">
            <!-- Pilihan Utama: Kamera HP Langsung (Jernih & Fokus Otomatis) -->
            <div style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); padding: 14px 16px; border-radius: 14px; margin-bottom: 14px; color: white; box-shadow: 0 4px 14px rgba(49, 46, 129, 0.2);">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(56, 189, 248, 0.2); display: flex; align-items: center; justify-content: center; font-size: 1.1rem; color: #38bdf8; flex-shrink: 0;">
                        <i class="fa-solid fa-camera"></i>
                    </div>
                    <div>
                        <div style="font-weight: 700; font-size: 0.92rem; line-height: 1.2;">Kamera HP Bawaan (Paling Jernih & Presisi)</div>
                        <div style="font-size: 0.74rem; color: #cbd5e1;">Gunakan kamera ponsel untuk foto tajam tanpa bayangan / blur.</div>
                    </div>
                </div>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('ktp_native_cam').click()" style="width: 100%; justify-content: center; background: #38bdf8; color: #0f172a; border: none; font-weight: 700; font-size: 0.9rem; padding: 11px 16px; border-radius: 10px; box-shadow: 0 4px 12px rgba(56, 189, 248, 0.35);">
                    <i class="fa-solid fa-camera-retro"></i> Buka Kamera HP & Ambil Foto KTP
                </button>
                <input type="file" id="ktp_native_cam" accept="image/*" capture="environment" style="display: none;" onchange="handleKtpFileSelected(this)">
            </div>

            <!-- Separator Opsi Lain -->
            <div style="display: flex; align-items: center; justify-content: center; margin: 10px 0 12px 0; position: relative;">
                <span style="background: white; padding: 0 12px; color: #94a3b8; font-size: 0.74rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; z-index: 1;">Atau Pilih Sumber Lain</span>
                <div style="position: absolute; left: 0; right: 0; height: 1px; background: #e2e8f0;"></div>
            </div>

            <!-- Tab Source Pilihan (Kamera Live vs Upload Galeri) -->
            <div style="display: flex; background: #f1f5f9; padding: 4px; border-radius: 12px; margin-bottom: 14px; gap: 4px;">
                <button type="button" id="tab_scan_camera" class="ktp-tab-btn" onclick="switchKtpSource('camera')" style="flex: 1; padding: 8px; border-radius: 8px; border: none; font-weight: 600; font-size: 0.82rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; transition: all 0.2s;">
                    <i class="fa-solid fa-video"></i> Kamera Live (Webcam)
                </button>
                <button type="button" id="tab_scan_upload" class="ktp-tab-btn" onclick="switchKtpSource('upload')" style="flex: 1; padding: 8px; border-radius: 8px; border: none; font-weight: 600; font-size: 0.82rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; transition: all 0.2s;">
                    <i class="fa-solid fa-images"></i> Pilih dari Galeri
                </button>
            </div>
            </div>

            <!-- Panel 1: Kamera Viewfinder Live -->
            <div id="ktp_panel_camera" style="display: none;">
                <div style="position: relative; width: 100%; aspect-ratio: 16/10; max-height: 280px; background: #020617; border-radius: 14px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                    <video id="ktp_video" playsinline autoplay muted style="width: 100%; height: 100%; object-fit: cover;"></video>
                    
                    <!-- Guide Frame KTP Overlay (Jernih tanpa bayangan gelap) -->
                    <div id="ktp_guide_frame" style="position: absolute; inset: 12px; border: 2px solid rgba(56, 189, 248, 0.7); border-radius: 12px; pointer-events: none; display: flex; flex-direction: column; justify-content: space-between; padding: 8px;">
                        <div style="display: flex; justify-content: space-between; width: 100%;">
                            <span style="border-top: 4px solid #38bdf8; border-left: 4px solid #38bdf8; width: 20px; height: 20px; border-top-left-radius: 6px;"></span>
                            <span style="background: rgba(15, 23, 42, 0.75); color: #38bdf8; font-size: 0.72rem; font-weight: 700; padding: 2px 10px; border-radius: 20px; backdrop-filter: blur(4px);">
                                <i class="fa-solid fa-id-card"></i> Posisikan e-KTP di dalam kotak
                            </span>
                            <span style="border-top: 4px solid #38bdf8; border-right: 4px solid #38bdf8; width: 20px; height: 20px; border-top-right-radius: 6px;"></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; width: 100%;">
                            <span style="border-bottom: 4px solid #38bdf8; border-left: 4px solid #38bdf8; width: 20px; height: 20px; border-bottom-left-radius: 6px;"></span>
                            <span style="color: white; font-size: 0.72rem; text-shadow: 0 1px 3px rgba(0,0,0,0.9); font-weight: 500;">
                                Dekatkan hingga teks KTP terbaca jelas
                            </span>
                            <span style="border-bottom: 4px solid #38bdf8; border-right: 4px solid #38bdf8; width: 20px; height: 20px; border-bottom-right-radius: 6px;"></span>
                        </div>
                    </div>

                    <!-- Kamera error placeholder -->
                    <div id="ktp_camera_error" style="display: none; position: absolute; inset: 0; background: #1e293b; color: white; padding: 20px; text-align: center; flex-direction: column; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-triangle-exclamation" style="font-size: 2rem; color: #f59e0b; margin-bottom: 8px;"></i>
                        <p style="margin: 0; font-size: 0.85rem;" id="ktp_camera_error_msg">Kamera tidak aktif atau izin ditolak.</p>
                        <div style="display: flex; gap: 8px; margin-top: 12px;">
                            <button type="button" class="btn btn-sm btn-outline-light" onclick="initKtpCamera()" style="font-size: 0.8rem; border-radius: 8px;">
                                <i class="fa-solid fa-rotate-right"></i> Coba Lagi
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" onclick="document.getElementById('ktp_native_cam').click()" style="font-size: 0.8rem; border-radius: 8px;">
                                <i class="fa-solid fa-camera"></i> Buka Kamera HP
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Camera Controls -->
                <div style="display: flex; gap: 8px; justify-content: center; align-items: center; margin-top: 10px;">
                    <button type="button" id="btn_switch_camera" class="btn btn-outline-secondary btn-sm" onclick="toggleCameraFacing()" style="border-radius: 10px; padding: 7px 12px; display: inline-flex; align-items: center; gap: 6px; font-size: 0.82rem;">
                        <i class="fa-solid fa-camera-rotate"></i> Putar Kamera
                    </button>
                    <button type="button" class="btn btn-primary" onclick="captureKtpPhoto()" style="background: #4f46e5; border-color: #4f46e5; border-radius: 10px; padding: 8px 20px; font-weight: 700; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35);">
                        <i class="fa-solid fa-circle-dot"></i> Jepret & Pindai
                    </button>
                </div>
            </div>

            <!-- Panel 2: Upload File View -->
            <div id="ktp_panel_upload" style="display: none;">
                <div id="ktp_dropzone" onclick="document.getElementById('ktp_file_input').click()" style="border: 2px dashed #cbd5e1; border-radius: 14px; padding: 28px 20px; text-align: center; cursor: pointer; background: #f8fafc; transition: all 0.2s;">
                    <div style="background: #e0e7ff; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px auto; color: #4f46e5; font-size: 1.4rem;">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </div>
                    <div style="font-weight: 600; color: #1e293b; font-size: 0.92rem; margin-bottom: 4px;">Pilih Foto e-KTP dari Galeri / File</div>
                    <div style="font-size: 0.76rem; color: #64748b;">Klik di sini untuk memilih foto KTP (JPG, PNG, WEBP)</div>
                </div>
                <input type="file" id="ktp_file_input" accept="image/*" style="display: none;" onchange="handleKtpFileSelected(this)">
            </div>

            <!-- Preview Image & Processing Area -->
            <div id="ktp_preview_section" style="display: none; margin-top: 14px;">
                <div style="position: relative; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; background: #0f172a; max-height: 250px; display: flex; align-items: center; justify-content: center;">
                    <img id="ktp_captured_img" src="" alt="Pratinjau KTP" style="max-height: 250px; width: 100%; object-fit: contain;">
                </div>

                <!-- Preview Controls: Rotate & Retake -->
                <div style="display: flex; gap: 8px; justify-content: center; margin-top: 8px;">
                    <button type="button" onclick="rotateKtpPhoto()" class="btn btn-sm btn-outline-secondary" style="font-size: 0.78rem; border-radius: 8px; padding: 5px 12px; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-rotate-right"></i> Putar Foto 90°
                    </button>
                    <button type="button" onclick="retakeKtpPhoto()" class="btn btn-sm btn-outline-danger" style="font-size: 0.78rem; border-radius: 8px; padding: 5px 12px; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-arrow-rotate-left"></i> Foto Ulang
                    </button>
                </div>

                <!-- OCR Progress Indicator -->
                <div id="ktp_ocr_progress_box" style="display: none; margin-top: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <span style="font-size: 0.82rem; font-weight: 600; color: #475569; display: inline-flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-circle-notch fa-spin text-primary"></i> <span id="ktp_ocr_status_text">Membaca teks e-KTP (OCR)...</span>
                        </span>
                        <span id="ktp_ocr_percent" style="font-size: 0.82rem; font-weight: 700; color: #4f46e5;">0%</span>
                    </div>
                    <div style="width: 100%; height: 6px; background: #e2e8f0; border-radius: 10px; overflow: hidden;">
                        <div id="ktp_ocr_progress_bar" style="width: 0%; height: 100%; background: linear-gradient(90deg, #4f46e5, #06b6d4); transition: width 0.3s ease;"></div>
                    </div>
                </div>
            </div>

            <!-- OCR Extracted Results Card -->
            <div id="ktp_result_card" style="display: none; margin-top: 14px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 14px; padding: 16px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <div style="font-weight: 700; color: #166534; font-size: 0.88rem; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-circle-check" style="color: #16a34a;"></i> Hasil Ekstraksi Data KTP
                    </div>
                    <span style="font-size: 0.72rem; color: #15803d; background: #dcfce7; padding: 2px 8px; border-radius: 12px; font-weight: 600;">
                        Bisa dikoreksi jika perlu
                    </span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <div>
                        <label style="font-size: 0.78rem; font-weight: 600; color: #334155; margin-bottom: 2px; display: block;">NIK Pelanggan (16 Digit):</label>
                        <div style="position: relative;">
                            <input type="text" id="scan_nik_result" class="form-control" style="font-size: 0.95rem; font-weight: 700; letter-spacing: 0.5px; border-radius: 8px;" placeholder="16 digit NIK">
                            <span id="scan_nik_badge" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: 0.72rem; font-weight: 700; padding: 2px 6px; border-radius: 4px;"></span>
                        </div>
                    </div>

                    <div>
                        <label style="font-size: 0.78rem; font-weight: 600; color: #334155; margin-bottom: 2px; display: block;">Nama Lengkap:</label>
                        <input type="text" id="scan_nama_result" class="form-control" style="font-size: 0.9rem; font-weight: 600; border-radius: 8px;" placeholder="Nama lengkap sesuai KTP">
                    </div>

                    <div>
                        <label style="font-size: 0.78rem; font-weight: 600; color: #334155; margin-bottom: 2px; display: block;">Alamat KTP:</label>
                        <textarea id="scan_alamat_result" class="form-control" rows="2" style="font-size: 0.85rem; border-radius: 8px;" placeholder="Alamat lengkap sesuai KTP"></textarea>
                    </div>
                </div>
            </div>

            <!-- OCR Notice -->
            <div id="ktp_ocr_hint" style="display: none; margin-top: 10px; font-size: 0.76rem; color: #64748b; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px;">
                <i class="fa-solid fa-circle-info text-info"></i> Pastikan data NIK, Nama, dan Alamat di atas sudah sesuai sebelum menekan tombol terapkan.
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 18px; gap: 10px; padding-top: 14px; border-top: 1px solid #e2e8f0;">
                <button type="button" class="btn btn-secondary" onclick="cancelKtpScan()" style="border-radius: 10px; padding: 9px 18px; font-size: 0.85rem;">
                    <i class="fa-solid fa-xmark"></i> Batal / Isi Manual
                </button>
                <button type="button" id="btn_apply_ktp" class="btn btn-primary" onclick="applyKtpDataToForm()" disabled style="border-radius: 10px; padding: 9px 20px; font-size: 0.88rem; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; opacity: 0.6; cursor: not-allowed; background: #16a34a; border-color: #16a34a;">
                    <i class="fa-solid fa-check"></i> Gunakan Data Ini & Lanjut
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Modal Upload Dokumentasi & Selesai Pasang (Technician Only) -->
<div class="modal" id="completeModal">
    <div class="modal-content" style="width: min(420px, 100%);">
        <div class="modal-header" style="background: var(--primary-gradient); color: white; padding: 20px 24px; display: flex; align-items: center; justify-content: space-between;">
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.2rem; font-weight: 700; margin: 0;">Upload Bukti & Selesai Pasang</h3>
            <button class="modal-close" onclick="closeCompleteModal()" style="background: none; border: none; color: white; font-size: 1.2rem; cursor: pointer; opacity: 0.8;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 24px;">
            <form action="{{ route('admin.order_pemasangan.complete') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id_order" id="complete_id">

                <div class="form-group" style="margin-bottom: 18px; display: flex; flex-direction: column; gap: 6px;">
                    <label for="foto_dokumentasi" style="font-size: 0.85rem; font-weight: 600; color: #334155;">Upload Foto Bukti Dokumentasi Pemasangan (Maksimal 3 Gambar) *</label>
                    <input type="file" id="foto_dokumentasi" name="foto_dokumentasi[]" class="form-control" required multiple accept="image/*">
                    <small style="color:var(--text-gray);">Maksimal 3 file gambar, masing-masing maksimal 5 MB (Format: jpeg, png, webp).</small>
                </div>

                <!-- Preview Grid -->
                <div id="preview-grid" class="preview-grid"></div>

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:24px;">
                    <button type="button" class="btn btn-secondary" onclick="closeCompleteModal()">Batal</button>
                    <button type="submit" class="btn btn-success" style="background-color: #16a34a; color: white; border: 1px solid #bbf7d0;">Kirim & Selesai</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Lightbox Modal for Viewing Images -->
<div class="modal" id="lightboxModal" style="background-color: rgba(15, 23, 42, 0.95); backdrop-filter: blur(8px);">
    <div style="position: absolute; top: 20px; right: 24px; display: flex; gap: 16px; z-index: 1100;">
        <button onclick="closeLightboxModal()" style="background: rgba(255,255,255,0.1); border: none; color: white; width: 44px; height: 44px; border-radius: 50%; font-size: 1.5rem; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s, transform 0.2s;">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Navigation Arrows -->
    <button id="lightboxPrev" onclick="prevLightboxImage()" style="position: absolute; left: 24px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.1); border: none; color: white; width: 50px; height: 50px; border-radius: 50%; font-size: 1.5rem; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 1060; transition: background 0.2s, transform 0.2s;">
        <i class="fa-solid fa-chevron-left"></i>
    </button>

    <button id="lightboxNext" onclick="nextLightboxImage()" style="position: absolute; right: 24px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.1); border: none; color: white; width: 50px; height: 50px; border-radius: 50%; font-size: 1.5rem; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 1060; transition: background 0.2s, transform 0.2s;">
        <i class="fa-solid fa-chevron-right"></i>
    </button>

    <!-- Main Lightbox Body -->
    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%; height: 100%; padding: 40px; box-sizing: border-box;">
        <div style="max-width: min(800px, 90%); max-height: 80vh; position: relative; display: flex; justify-content: center; align-items: center;">
            <img id="lightboxImage" src="" alt="Pratinjau Gambar" style="max-width: 100%; max-height: 80vh; object-fit: contain; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); border: 2px solid rgba(255,255,255,0.1);">
        </div>
        <!-- Title / Caption -->
        <div id="lightboxCaption" style="color: white; font-family: 'Outfit', sans-serif; font-size: 1.1rem; font-weight: 700; margin-top: 20px; background: rgba(15, 23, 42, 0.6); padding: 8px 20px; border-radius: 30px; border: 1px solid rgba(255,255,255,0.1);"></div>
    </div>
</div>
@endsection

@section('scripts')
@if(Auth::user()->level === 'admin' && count($topTeknisi) > 0)
    <!-- Load Chart.js from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const chartData = {!! json_encode($topTeknisi) !!};
            const ctx = document.getElementById('topTeknisiChart').getContext('2d');
            const labels = chartData.map(item => item.nama_user);
            const installations = chartData.map(item => item.installations);
            const tickets = chartData.map(item => item.tickets);

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Selesai Pemasangan',
                            data: installations,
                            backgroundColor: 'rgba(22, 163, 74, 0.8)',
                            borderColor: 'rgba(22, 163, 74, 1)',
                            borderWidth: 1.5,
                            borderRadius: 6,
                            barPercentage: 0.6,
                            categoryPercentage: 0.6
                        },
                        {
                            label: 'Selesai Tiket',
                            data: tickets,
                            backgroundColor: 'rgba(249, 115, 22, 0.8)',
                            borderColor: 'rgba(249, 115, 22, 1)',
                            borderWidth: 1.5,
                            borderRadius: 6,
                            barPercentage: 0.6,
                            categoryPercentage: 0.6
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
                                font: {
                                    family: 'Outfit',
                                    size: 11,
                                    weight: 'bold'
                                },
                                color: '#475569'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.9)',
                            titleFont: { family: 'Outfit', size: 13, weight: 'bold' },
                            bodyFont: { family: 'Inter', size: 12 },
                            padding: 10,
                            cornerRadius: 8,
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    return ` ${context.dataset.label}: ${context.raw}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    family: 'Inter',
                                    weight: '600',
                                    size: 11
                                },
                                color: '#64748b'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#e2e8f0',
                                drawBorder: false
                            },
                            ticks: {
                                stepSize: 1,
                                font: {
                                    family: 'Inter',
                                    size: 11
                                },
                                color: '#64748b'
                            }
                        }
                    }
                }
            });
        });
    </script>
@endif

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    let odpMapInstance = null;
    let clientMarker = null;
    let odpMarkers = [];
    let currentClientCoords = '';
    const odpsList = {!! json_encode($odps) !!};

    document.addEventListener("DOMContentLoaded", function () {
        setupTablePagination("#ordersTable", "#ordersPagination", "#tableLimit", "#tableSearch");
    });

    function getGPSCoordinates() {
        if ('geolocation' in navigator) {
            navigator.geolocation.getCurrentPosition(function (position) {
                var lat = position.coords.latitude.toFixed(6);
                var lng = position.coords.longitude.toFixed(6);
                document.getElementById('koordinat_pemasangan').value = lat + ', ' + lng;
            }, function (error) {
                console.error("Gagal mendapatkan lokasi:", error);
                alert("Gagal mendapatkan lokasi GPS HP: " + error.message);
            }, { enableHighAccuracy: true, maximumAge: 0, timeout: 20000 });
        } else {
            alert("Geolocation tidak didukung oleh browser ini.");
        }
    }

    // Assign Technician Modal
    function openAssignModal(id) {
        document.getElementById('assign_id').value = id;
        document.getElementById('assignModal').classList.add('active');
    }

    function closeAssignModal() {
        document.getElementById('assignModal').classList.remove('active');
    }

    // Reject & Cancel Order Modals
    function openRejectOrderModal(id) {
        document.getElementById('reject_id').value = id;
        document.getElementById('alasan_ditolak').value = '';
        document.getElementById('rejectOrderModal').classList.add('active');
    }
    function closeRejectOrderModal() {
        document.getElementById('rejectOrderModal').classList.remove('active');
    }

    function openCancelOrderModal(id) {
        document.getElementById('cancel_id').value = id;
        document.getElementById('alasan_batal').value = '';
        document.getElementById('cancelOrderModal').classList.add('active');
    }
    function closeCancelOrderModal() {
        document.getElementById('cancelOrderModal').classList.remove('active');
    }

    function openPendingOrderModal(id) {
        document.getElementById('pending_id').value = id;
        document.getElementById('alasan_pending').value = '';
        document.getElementById('pendingOrderModal').classList.add('active');
    }
    function closePendingOrderModal() {
        document.getElementById('pendingOrderModal').classList.remove('active');
    }

    // Dropdown toggling for Approve ODP Select
    function toggleApproveOdpDropdown(event) {
        event.stopPropagation();
        const container = document.getElementById('approve_odp_select');
        const searchInput = document.getElementById('approve_search_odp');
        
        // Close other dropdowns
        const branchContainer = document.getElementById('approve_branch_select');
        if (branchContainer) branchContainer.classList.remove('active');
        const subContainer = document.getElementById('approve_sub_branch_select');
        if (subContainer) subContainer.classList.remove('active');
        
        container.classList.toggle('active');
        if (container.classList.contains('active')) {
            searchInput.focus();
        }
    }

    // Dropdown toggling for Approve Branch Select
    function toggleApproveBranchDropdown(event) {
        event.stopPropagation();
        const container = document.getElementById('approve_branch_select');
        const searchInput = document.getElementById('approve_search_branch');
        
        // Close other dropdowns
        const odpContainer = document.getElementById('approve_odp_select');
        if (odpContainer) odpContainer.classList.remove('active');
        const subContainer = document.getElementById('approve_sub_branch_select');
        if (subContainer) subContainer.classList.remove('active');
        
        container.classList.toggle('active');
        if (container.classList.contains('active')) {
            searchInput.focus();
        }
    }

    // Dropdown toggling for Approve Sub Branch Select
    function toggleApproveSubBranchDropdown(event) {
        event.stopPropagation();
        const container = document.getElementById('approve_sub_branch_select');
        const searchInput = document.getElementById('approve_search_sub_branch');
        
        // Close other dropdowns
        const odpContainer = document.getElementById('approve_odp_select');
        if (odpContainer) odpContainer.classList.remove('active');
        const branchContainer = document.getElementById('approve_branch_select');
        if (branchContainer) branchContainer.classList.remove('active');
        
        container.classList.toggle('active');
        if (container.classList.contains('active')) {
            searchInput.focus();
        }
    }

    // Filter ODP options based on search text
    function filterApproveOdpOptions() {
        const query = document.getElementById('approve_search_odp').value.toLowerCase();
        const options = document.querySelectorAll('#approve_odp_options .custom-select-option');
        
        options.forEach(opt => {
            const text = opt.getAttribute('data-text').toLowerCase();
            if (text.includes(query)) {
                opt.style.display = 'block';
            } else {
                opt.style.display = 'none';
            }
        });
    }

    // Filter Branch options based on search text
    function filterApproveBranchOptions() {
        const query = document.getElementById('approve_search_branch').value.toLowerCase();
        const options = document.querySelectorAll('#approve_branch_options .custom-select-option');
        
        options.forEach(opt => {
            const text = opt.getAttribute('data-text').toLowerCase();
            if (text.includes(query)) {
                opt.style.display = 'block';
            } else {
                opt.style.display = 'none';
            }
        });
    }

    // Filter Sub Branch options based on search text and selected branch
    function filterApproveSubBranchOptions() {
        const query = document.getElementById('approve_search_sub_branch').value.toLowerCase();
        const branchId = document.getElementById('approve_branch_id').value;
        const options = document.querySelectorAll('#approve_sub_branch_options .custom-select-option');
        
        options.forEach(opt => {
            const text = opt.getAttribute('data-text').toLowerCase();
            const optBranch = opt.getAttribute('data-branch');
            const optVal = opt.getAttribute('data-value');
            
            if (optVal === "") {
                opt.style.display = text.includes(query) ? 'block' : 'none';
                return;
            }
            
            const matchesText = text.includes(query);
            const matchesBranch = (!branchId || optBranch === branchId);
            
            if (matchesText && matchesBranch) {
                opt.style.display = 'block';
            } else {
                opt.style.display = 'none';
            }
        });
    }

    // Filter Sub Branch options dynamically when branch changes
    function filterSubBranchByBranch() {
        const branchId = document.getElementById('approve_branch_id').value;
        const subBranchIdInput = document.getElementById('approve_sub_branch_id');
        const selectedSubBranchVal = subBranchIdInput.value;
        let selectedStillValid = false;
        
        const options = document.querySelectorAll('#approve_sub_branch_options .custom-select-option');
        
        options.forEach(opt => {
            const optBranch = opt.getAttribute('data-branch');
            const optVal = opt.getAttribute('data-value');
            
            if (optVal === "") {
                opt.style.display = 'block';
                return;
            }
            
            if (!branchId || optBranch === branchId) {
                opt.style.display = 'block';
                if (optVal === selectedSubBranchVal) {
                    selectedStillValid = true;
                }
            } else {
                opt.style.display = 'none';
                opt.classList.remove('selected');
            }
        });
        
        if (selectedSubBranchVal !== "" && !selectedStillValid) {
            subBranchIdInput.value = '';
            document.getElementById('approve_sub_branch_select_text').innerText = '-- Tanpa Sub Branch --';
            document.querySelectorAll('#approve_sub_branch_options .custom-select-option').forEach(opt => {
                opt.classList.remove('selected');
                if (opt.getAttribute('data-value') === '') {
                    opt.classList.add('selected');
                }
            });
        }
    }

    // ACC Approval Modal
    function openApproveModal(order) {
        document.getElementById('approve_id').value = order.id;
        document.getElementById('approve_label_nama').innerText = order.nama;
        document.getElementById('approve_label_nik').innerText = "NIK: " + order.nik;
        document.getElementById('approve_label_alamat').innerText = "Alamat Pasang: " + order.alamat_pemasangan;
        currentClientCoords = order.koordinat_pemasangan || '';
        
        // Populate and display warning box if matching deleted customer found
        const warningBox = document.getElementById('approve_warning_box');
        const warningList = document.getElementById('approve_warning_list');
        if (order.warnings && order.warnings.length > 0) {
            warningList.innerHTML = '';
            order.warnings.forEach(w => {
                const item = document.createElement('div');
                item.style.padding = '10px 14px';
                item.style.backgroundColor = 'rgba(220, 38, 38, 0.03)';
                item.style.borderRadius = '10px';
                item.style.border = '1px dashed rgba(220, 38, 38, 0.2)';
                item.style.marginBottom = '6px';
                item.innerHTML = `
                    <div style="font-weight: 700; color: #b91c1c; margin-bottom: 4px;">⚠️ ${w.matched_by}</div>
                    <div style="color: #475569;"><strong>Nama Pelanggan:</strong> ${w.nama_pelanggan}</div>
                    <div style="color: #475569;"><strong>Dihapus Pada:</strong> ${w.created_at}</div>
                    <div style="margin-top: 6px; padding: 6px 10px; background-color: #fee2e2; color: #991b1b; border-radius: 8px; font-weight: 500;">
                        <strong>Alasan Hapus:</strong> ${w.alasan_hapus}
                    </div>
                `;
                warningList.appendChild(item);
            });
            warningBox.style.display = 'block';
        } else {
            warningBox.style.display = 'none';
        }
        
        // Default username and password suggestion
        var nameSlug = order.nama.toLowerCase().replace(/[^a-z0-9]/g, '_').substring(0, 10);
        document.getElementById('username').value = nameSlug + '_' + Math.floor(100 + Math.random() * 900);
        document.getElementById('password').value = Math.floor(100000 + Math.random() * 900000);
        
        document.getElementById('no_telp_approve').value = order.no_telp || '';
        
        // Store requested package for later pre-selection
        window.requestedPaket = order.paket || '';
        document.getElementById('id_mikrotik').value = '';
        const paketSelect = document.getElementById('paket');
        paketSelect.innerHTML = '<option value="">-- Pilih Mikrotik Terlebih Dahulu --</option>';
        paketSelect.disabled = true;

        // Reset ODP custom dropdown selection
        document.getElementById('approve_odp_id').value = '';
        document.getElementById('approve_odp_select_text').innerText = '-- Pilih ODP (Opsional) --';
        document.getElementById('approve_search_odp').value = '';
        filterApproveOdpOptions();
        
        document.querySelectorAll('#approve_odp_options .custom-select-option').forEach(opt => {
            opt.classList.remove('selected');
            if (opt.getAttribute('data-value') === '') {
                opt.classList.add('selected');
            }
        });

        // Reset Branch custom dropdown selection
        document.getElementById('approve_branch_id').value = '';
        document.getElementById('approve_branch_select_text').innerText = '-- Tanpa Branch --';
        document.getElementById('approve_search_branch').value = '';
        filterApproveBranchOptions();
        
        document.querySelectorAll('#approve_branch_options .custom-select-option').forEach(opt => {
            opt.classList.remove('selected');
            if (opt.getAttribute('data-value') === '') {
                opt.classList.add('selected');
            }
        });

        // Reset Sub Branch custom dropdown selection
        document.getElementById('approve_sub_branch_id').value = '';
        document.getElementById('approve_sub_branch_select_text').innerText = '-- Tanpa Sub Branch --';
        document.getElementById('approve_search_sub_branch').value = '';
        filterApproveSubBranchOptions();
        
        document.querySelectorAll('#approve_sub_branch_options .custom-select-option').forEach(opt => {
            opt.classList.remove('selected');
            if (opt.getAttribute('data-value') === '') {
                opt.classList.add('selected');
            }
        });

        // Filter Sub Branch list by Branch selection
        filterSubBranchByBranch();

        document.getElementById('approveModal').classList.add('active');
    }

    // Close ACC Approval Modal
    function closeApproveModal() {
        document.getElementById('approveModal').classList.remove('active');
        
        // Close any active custom dropdowns
        const odpContainer = document.getElementById('approve_odp_select');
        if (odpContainer) odpContainer.classList.remove('active');
        const branchContainer = document.getElementById('approve_branch_select');
        if (branchContainer) branchContainer.classList.remove('active');
        const subContainer = document.getElementById('approve_sub_branch_select');
        if (subContainer) subContainer.classList.remove('active');
    }

    // ODP Map Modal Functions
    function openOdpMapModal() {
        document.getElementById('odpMapModal').classList.add('active');
        
        setTimeout(function() {
            let clientLat = -7.504893; // default fallback
            let clientLng = 110.855417; // default fallback
            
            if (currentClientCoords) {
                const parts = currentClientCoords.split(',');
                if (parts.length === 2) {
                    clientLat = parseFloat(parts[0].trim());
                    clientLng = parseFloat(parts[1].trim());
                }
            }
            
            if (odpMapInstance === null) {
                odpMapInstance = L.map('odpMap').setView([clientLat, clientLng], 15);
                var streets = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                                        maxZoom: 24,
maxNativeZoom: 21,
                        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                    attribution: '&copy; Google Maps'
                });
                var satellite = L.tileLayer('https://{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
                                        maxZoom: 24,
maxNativeZoom: 21,
                        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                    attribution: '&copy; Google Maps'
                });
                streets.addTo(odpMapInstance);
                addCustomMapToggle(odpMapInstance, streets, satellite);
            } else {
                odpMapInstance.setView([clientLat, clientLng], 15);
                if (clientMarker) {
                    odpMapInstance.removeLayer(clientMarker);
                }
                odpMarkers.forEach(marker => {
                    odpMapInstance.removeLayer(marker);
                });
                odpMarkers = [];
            }
            
            // Custom Icons (Google Maps style teardrop pins)
            const clientIcon = L.divIcon({
                className: 'custom-div-icon',
                html: "<i class='fa-solid fa-location-dot' style='color:#4f46e5; font-size:2.2rem; filter: drop-shadow(0px 3px 4px rgba(0,0,0,0.4));'></i>",
                iconSize: [24, 32],
                iconAnchor: [12, 32],
                popupAnchor: [0, -32]
            });
            
            const activeOdpIcon = L.divIcon({
                className: 'custom-div-icon',
                html: "<i class='fa-solid fa-location-dot' style='color:#16a34a; font-size:1.8rem; filter: drop-shadow(0px 2px 3px rgba(0,0,0,0.4));'></i>",
                iconSize: [20, 26],
                iconAnchor: [10, 26],
                popupAnchor: [0, -26]
            });
            
            const fullOdpIcon = L.divIcon({
                className: 'custom-div-icon',
                html: "<i class='fa-solid fa-location-dot' style='color:#dc2626; font-size:1.8rem; filter: drop-shadow(0px 2px 3px rgba(0,0,0,0.4));'></i>",
                iconSize: [20, 26],
                iconAnchor: [10, 26],
                popupAnchor: [0, -26]
            });
            
            // Add Client Marker
            clientMarker = L.marker([clientLat, clientLng], { icon: clientIcon }).addTo(odpMapInstance);
            clientMarker.bindPopup("<strong>Lokasi Pemasangan Baru</strong><br>Koordinat: " + clientLat + ", " + clientLng).openPopup();
            
            // Haversine formula
            function calculateDistance(lat1, lon1, lat2, lon2) {
                const R = 6371e3; // metres
                const φ1 = lat1 * Math.PI/180;
                const φ2 = lat2 * Math.PI/180;
                const Δφ = (lat2-lat1) * Math.PI/180;
                const Δλ = (lon2-lon1) * Math.PI/180;

                const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                          Math.cos(φ1) * Math.cos(φ2) *
                          Math.sin(Δλ/2) * Math.sin(Δλ/2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                return R * c;
            }
            
            // Populate ODP markers
            odpsList.forEach(odp => {
                if (odp.location) {
                    const coords = odp.location.split(',');
                    if (coords.length === 2) {
                        const odpLat = parseFloat(coords[0].trim());
                        const odpLng = parseFloat(coords[1].trim());
                        
                        const dist = calculateDistance(clientLat, clientLng, odpLat, odpLng);
                        const usage = odp.pelanggans_count || 0;
                        const totalPort = parseInt(odp.port_odp) || 0;
                        const remaining = totalPort - usage;
                        
                        const isFull = remaining <= 0;
                        const icon = isFull ? fullOdpIcon : activeOdpIcon;
                        
                        const marker = L.marker([odpLat, odpLng], { icon: icon }).addTo(odpMapInstance);
                        
                        let popupHtml = "<div style='font-family: sans-serif; font-size: 13px; line-height: 1.4;'>"
                                      + "<strong>ODP: " + odp.nama_odp + "</strong><br>"
                                      + "Sisa Port: <span style='font-weight:bold; color:" + (isFull ? '#dc2626' : '#16a34a') + ";'>" + remaining + " / " + totalPort + "</span><br>"
                                      + "Jarak: " + dist.toFixed(1) + " meter<br>";
                                      
                        if (isFull) {
                            popupHtml += "<span style='color:#dc2626; font-weight:bold; font-size:11px; display:inline-block; margin-top:4px;'>[PORT ODP PENUH]</span>";
                        } else {
                            popupHtml += "<button type='button' class='btn btn-primary' onclick='selectOdpFromMap(\"" + odp.id_odp + "\", \"" + odp.nama_odp + "\", " + remaining + ", " + totalPort + ")' style='margin-top:8px; padding:6px 10px; font-size:0.75rem; border-radius:6px; height:auto; color:white; width:100%; justify-content:center;'>Pilih ODP Ini</button>";
                        }
                        popupHtml += "</div>";
                        
                        marker.bindPopup(popupHtml);
                        odpMarkers.push(marker);
                    }
                }
            });
            
            odpMapInstance.invalidateSize();
        }, 300);
    }
    
    function closeOdpMapModal() {
        document.getElementById('odpMapModal').classList.remove('active');
    }
    
    window.selectOdpFromMap = function(id, name, remaining, total) {
        document.getElementById('approve_odp_id').value = id;
        document.getElementById('approve_odp_select_text').innerText = name + " (Sisa Port: " + remaining + " / " + total + ")";
        
        document.querySelectorAll('#approve_odp_options .custom-select-option').forEach(opt => {
            opt.classList.remove('selected');
            if (opt.getAttribute('data-value') === id) {
                opt.classList.add('selected');
            }
        });
        
        closeOdpMapModal();
    };

    // Bind event listeners for custom select on DOM load
    document.addEventListener('DOMContentLoaded', function() {
        // --- ODP ---
        const searchInput = document.getElementById('approve_search_odp');
        if (searchInput) {
            searchInput.addEventListener('keyup', filterApproveOdpOptions);
            searchInput.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
        
        const optionsContainer = document.getElementById('approve_odp_options');
        if (optionsContainer) {
            optionsContainer.addEventListener('click', function(e) {
                const option = e.target.closest('.custom-select-option');
                if (!option) return;
                
                if (option.classList.contains('disabled')) return;
                
                const val = option.getAttribute('data-value');
                const text = option.getAttribute('data-text');
                
                document.getElementById('approve_odp_id').value = val;
                document.getElementById('approve_odp_select_text').innerText = text;
                
                document.querySelectorAll('#approve_odp_options .custom-select-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                option.classList.add('selected');
                
                document.getElementById('approve_odp_select').classList.remove('active');
            });
        }
        
        // --- Branch ---
        const searchBranch = document.getElementById('approve_search_branch');
        if (searchBranch) {
            searchBranch.addEventListener('keyup', filterApproveBranchOptions);
            searchBranch.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
        
        const optionsBranch = document.getElementById('approve_branch_options');
        if (optionsBranch) {
            optionsBranch.addEventListener('click', function(e) {
                const option = e.target.closest('.custom-select-option');
                if (!option) return;
                
                const val = option.getAttribute('data-value');
                const text = option.getAttribute('data-text');
                
                document.getElementById('approve_branch_id').value = val;
                document.getElementById('approve_branch_select_text').innerText = text;
                
                document.querySelectorAll('#approve_branch_options .custom-select-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                option.classList.add('selected');
                
                // Dynamically filter sub-branches when branch changes
                filterSubBranchByBranch();
                
                document.getElementById('approve_branch_select').classList.remove('active');
            });
        }

        // --- Sub Branch ---
        const searchSubBranch = document.getElementById('approve_search_sub_branch');
        if (searchSubBranch) {
            searchSubBranch.addEventListener('keyup', filterApproveSubBranchOptions);
            searchSubBranch.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
        
        const optionsSubBranch = document.getElementById('approve_sub_branch_options');
        if (optionsSubBranch) {
            optionsSubBranch.addEventListener('click', function(e) {
                const option = e.target.closest('.custom-select-option');
                if (!option) return;
                
                const val = option.getAttribute('data-value');
                const text = option.getAttribute('data-text');
                
                document.getElementById('approve_sub_branch_id').value = val;
                document.getElementById('approve_sub_branch_select_text').innerText = text;
                
                document.querySelectorAll('#approve_sub_branch_options .custom-select-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                option.classList.add('selected');
                
                document.getElementById('approve_sub_branch_select').classList.remove('active');
            });
        }
        
        // --- Click outside closure ---
        document.addEventListener('click', function(e) {
            const odpContainer = document.getElementById('approve_odp_select');
            if (odpContainer && !odpContainer.contains(e.target)) {
                odpContainer.classList.remove('active');
            }
            const branchContainer = document.getElementById('approve_branch_select');
            if (branchContainer && !branchContainer.contains(e.target)) {
                branchContainer.classList.remove('active');
            }
            const subContainer = document.getElementById('approve_sub_branch_select');
            if (subContainer && !subContainer.contains(e.target)) {
                subContainer.classList.remove('active');
            }
        });
    });

    // Complete Installation Modal (Technician)
    let selectedDocs = [];

    function updateFileInput() {
        const fileInput = document.getElementById('foto_dokumentasi');
        if (!fileInput) return;

        const dataTransfer = new DataTransfer();
        selectedDocs.forEach(file => {
            dataTransfer.items.add(file);
        });
        fileInput.files = dataTransfer.files;
        
        // Toggle required attribute based on files presence
        if (selectedDocs.length > 0) {
            fileInput.removeAttribute('required');
        } else {
            fileInput.setAttribute('required', 'required');
        }
    }

    function renderPreviews() {
        const previewGrid = document.getElementById('preview-grid');
        if (!previewGrid) return;

        previewGrid.innerHTML = '';
        selectedDocs.forEach((file, index) => {
            const previewItem = document.createElement('div');
            previewItem.className = 'preview-item';

            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            previewItem.appendChild(img);

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'preview-remove';
            removeBtn.innerHTML = '&times;';
            removeBtn.onclick = function() {
                selectedDocs.splice(index, 1);
                renderPreviews();
                updateFileInput();
            };
            previewItem.appendChild(removeBtn);

            previewGrid.appendChild(previewItem);
        });
    }

    function openCompleteModal(id) {
        selectedDocs = [];
        const fileInput = document.getElementById('foto_dokumentasi');
        if (fileInput) {
            fileInput.value = '';
            fileInput.setAttribute('required', 'required');
        }
        renderPreviews();
        document.getElementById('complete_id').value = id;
        document.getElementById('completeModal').classList.add('active');
    }

    function closeCompleteModal() {
        document.getElementById('completeModal').classList.remove('active');
    }

    // JS handler for multiple images previewing and adding
    document.addEventListener("DOMContentLoaded", function() {
        const fileInput = document.getElementById('foto_dokumentasi');
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                const newFiles = Array.from(this.files);
                
                if (selectedDocs.length + newFiles.length > 3) {
                    alert('Maksimal hanya boleh mengunggah 3 foto dokumentasi!');
                    updateFileInput();
                    return;
                }

                // Append and avoid duplicate object references if chosen twice
                selectedDocs = selectedDocs.concat(newFiles);
                renderPreviews();
                updateFileInput();
            });
        }
    });

    // Global variables for Lightbox
    let lightboxItems = [];
    let lightboxIndex = 0;

    function openImageLightbox(items, startIndex) {
        lightboxItems = items;
        lightboxIndex = startIndex;
        
        if (lightboxItems.length === 0) return;

        document.getElementById('lightboxModal').classList.add('active');
        updateLightboxContent();
    }

    function closeLightboxModal() {
        document.getElementById('lightboxModal').classList.remove('active');
    }

    function updateLightboxContent() {
        if (lightboxItems.length === 0) return;

        const currentItem = lightboxItems[lightboxIndex];
        document.getElementById('lightboxImage').src = currentItem.url;
        document.getElementById('lightboxCaption').innerText = currentItem.title;

        // Show/hide navigation buttons
        const prevBtn = document.getElementById('lightboxPrev');
        const nextBtn = document.getElementById('lightboxNext');

        if (lightboxItems.length > 1) {
            prevBtn.style.display = 'flex';
            nextBtn.style.display = 'flex';
        } else {
            prevBtn.style.display = 'none';
            nextBtn.style.display = 'none';
        }
    }

    function prevLightboxImage() {
        if (lightboxItems.length <= 1) return;
        lightboxIndex = (lightboxIndex - 1 + lightboxItems.length) % lightboxItems.length;
        updateLightboxContent();
    }

    function nextLightboxImage() {
        if (lightboxItems.length <= 1) return;
        lightboxIndex = (lightboxIndex + 1) % lightboxItems.length;
        updateLightboxContent();
    }

    // Keyboard support for Lightbox
    document.addEventListener('keydown', function(e) {
        const lightbox = document.getElementById('lightboxModal');
        if (lightbox && lightbox.classList.contains('active')) {
            if (e.key === 'ArrowLeft') {
                prevLightboxImage();
            } else if (e.key === 'ArrowRight') {
                nextLightboxImage();
            } else if (e.key === 'Escape') {
                closeLightboxModal();
            }
        }
    });

    function openDeletedReasonModal(warnings) {
        const contentDiv = document.getElementById('deleted_reason_content');
        contentDiv.innerHTML = '';
        
        warnings.forEach((w, index) => {
            const item = document.createElement('div');
            item.style.padding = '14px';
            item.style.backgroundColor = '#f8fafc';
            item.style.borderRadius = '12px';
            item.style.border = '1px solid #e2e8f0';
            if (index > 0) {
                item.style.marginTop = '10px';
            }
            item.innerHTML = `
                <div style="font-weight: 700; color: #dc2626; margin-bottom: 6px; font-size: 0.95rem;">
                    ⚠️ Kriteria Cocok: ${w.matched_by}
                </div>
                <div style="font-size: 0.85rem; color: #475569; line-height: 1.5;">
                    <strong>Nama Pelanggan:</strong> ${w.nama_pelanggan}<br>
                    <strong>Dihapus Pada:</strong> ${w.created_at}
                </div>
                <div style="margin-top: 8px; padding: 10px; background-color: #fee2e2; color: #991b1b; border-radius: 8px; font-size: 0.85rem; font-weight: 600; line-height: 1.4;">
                    <strong>Alasan Dihapus:</strong><br>
                    ${w.alasan_hapus}
                </div>
            `;
            contentDiv.appendChild(item);
        });
        
        document.getElementById('deletedReasonModal').classList.add('active');
    }

    function closeDeletedReasonModal() {
        document.getElementById('deletedReasonModal').classList.remove('active');
    }

    document.getElementById('approveOrderForm').addEventListener('submit', async function(e) {
        // Prevent default submission
        e.preventDefault();
        
        // Show loading spinner
        if (typeof showLoading === 'function') {
            showLoading();
        }
        
        // Get phone number
        const noTelp = document.getElementById('no_telp_approve').value.trim();
        
        // Check if there is already a confirmed flag
        if (this.querySelector('input[name="confirm_same_phone"]')) {
            this.submit();
            return;
        }
        
        try {
            const response = await fetch('{{ route("admin.pelanggan.check_phone") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ no_telp: noTelp })
            });
            const data = await response.json();
            
            if (data.exists) {
                if (typeof hideLoading === 'function') {
                    hideLoading();
                }
                const confirmSave = confirm(`Peringatan: Nomor HP/WhatsApp (${noTelp}) ini sudah digunakan oleh pelanggan "${data.nama_pelanggan}" (${data.kode_pelanggan}).\n\nApakah Anda yakin ingin tetap melakukan ACC dengan nomor HP yang sama?`);
                if (confirmSave) {
                    if (typeof showLoading === 'function') {
                        showLoading();
                    }
                    const confirmInput = document.createElement('input');
                    confirmInput.type = 'hidden';
                    confirmInput.name = 'confirm_same_phone';
                    confirmInput.value = '1';
                    this.appendChild(confirmInput);
                    this.submit();
                }
            } else {
                this.submit();
            }
        } catch (err) {
            console.error(err);
            this.submit();
        }
    });
    function openAddOrderModal() {
        document.getElementById('addOrderModal').classList.add('active');
        openKtpScanModal();
    }

    function closeAddOrderModal() {
        cancelKtpScan();
        document.getElementById('addOrderModal').classList.remove('active');
    }

    function copyAlamatKTPModal() {
        var alamatKTP = document.getElementById('alamat_ktp_modal').value;
        if (alamatKTP.trim() !== '') {
            document.getElementById('alamat_pemasangan_modal').value = alamatKTP;
        } else {
            alert('Alamat KTP masih kosong. Silakan isi terlebih dahulu.');
        }
    }

    function getGPSCoordinatesModal() {
        if ('geolocation' in navigator) {
            navigator.geolocation.getCurrentPosition(function (position) {
                var lat = position.coords.latitude.toFixed(6);
                var lng = position.coords.longitude.toFixed(6);
                document.getElementById('koordinat_pemasangan_modal').value = lat + ', ' + lng;
            }, function (error) {
                console.error("Gagal mendapatkan lokasi:", error);
                alert("Gagal mendapatkan lokasi GPS HP: " + error.message);
            }, { enableHighAccuracy: true, maximumAge: 0, timeout: 20000 });
        } else {
            alert("Geolocation tidak didukung oleh browser ini.");
        }
    }
    window.allPakets = [
        @foreach($pakets as $p)
        { id: "{{ $p->id_paket }}", nama: "{{ $p->nama_paket }}", harga: "{{ number_format($p->harga, 0, ',', '.') }}", profile: "{{ $p->id_pmikrotik }}" },
        @endforeach
    ];

    function filterPaketByMikrotik(mikrotikId) {
        const paketSelect = document.getElementById('paket');
        if (!mikrotikId) {
            paketSelect.disabled = true;
            paketSelect.innerHTML = '<option value="">-- Pilih Mikrotik Terlebih Dahulu --</option>';
            return;
        }

        paketSelect.disabled = true;
        paketSelect.innerHTML = '<option value="">Sedang memuat profil...</option>';

        fetch('{{ route("admin.paket.get_mikrotik_profiles") }}?device_id=' + mikrotikId, {
            headers: { 'Accept': 'application/json' }
        })
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    const profiles = data.profiles; // array of strings
                    let html = '<option value="">-- Pilih Paket --</option>';
                    let count = 0;
                    window.allPakets.forEach(p => {
                        // Check if the package's profile exists in this mikrotik's profiles
                        if (profiles.includes(p.profile)) {
                            html += `<option value="${p.id}">${p.nama} (${p.harga} / Bulan)</option>`;
                            count++;
                        }
                    });

                    if (count === 0) {
                        html = '<option value="">-- Tidak ada paket yang sesuai di Mikrotik ini --</option>';
                    }
                    
                    paketSelect.innerHTML = html;
                    paketSelect.disabled = (count === 0);

                    // Re-select the requested paket if available
                    if (window.requestedPaket) {
                        paketSelect.value = window.requestedPaket;
                    }
                } else {
                    paketSelect.innerHTML = '<option value="">Gagal memuat profil</option>';
                    alert(data.message || 'Gagal memuat profil Mikrotik.');
                }
            })
            .catch(err => {
                paketSelect.innerHTML = '<option value="">Error memuat profil</option>';
                alert('Terjadi kesalahan saat memuat profil: ' + err.message);
            });
    }
</script>

    <!-- Tesseract OCR Library -->
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
    <script>
    let ktpCameraStream = null;
    let currentFacingMode = 'environment';
    let currentKtpSource = 'none';
    window.currentKtpFile = null;
    window.ktpRotation = 0;
    window.lastRawSourceImg = null;

    function openKtpScanModal() {
        const modal = document.getElementById('ktpScannerModal');
        if (modal) {
            modal.classList.add('active');
            window.ktpRotation = 0;
            const srcSec = document.getElementById('ktp_source_selection');
            if (srcSec) srcSec.style.display = 'block';

            if (window.innerWidth >= 992) {
                switchKtpSource('camera');
            } else {
                switchKtpSource('none');
            }
        }
    }

    function cancelKtpScan() {
        stopKtpCamera();
        const scannerModal = document.getElementById('ktpScannerModal');
        if (scannerModal) {
            scannerModal.classList.remove('active');
        }
        const addModal = document.getElementById('addOrderModal');
        if (addModal && !addModal.classList.contains('active')) {
            addModal.classList.add('active');
        }
    }

    function switchKtpSource(source) {
        currentKtpSource = source;
        const btnCamera = document.getElementById('tab_scan_camera');
        const btnUpload = document.getElementById('tab_scan_upload');
        const panelCamera = document.getElementById('ktp_panel_camera');
        const panelUpload = document.getElementById('ktp_panel_upload');
        const previewSec = document.getElementById('ktp_preview_section');

        if (previewSec && previewSec.style.display !== 'none') {
            return;
        }

        if (source === 'camera') {
            if (btnCamera) btnCamera.classList.add('active');
            if (btnUpload) btnUpload.classList.remove('active');
            if (panelCamera) panelCamera.style.display = 'block';
            if (panelUpload) panelUpload.style.display = 'none';
            initKtpCamera();
        } else if (source === 'upload') {
            if (btnCamera) btnCamera.classList.remove('active');
            if (btnUpload) btnUpload.classList.add('active');
            if (panelCamera) panelCamera.style.display = 'none';
            if (panelUpload) panelUpload.style.display = 'block';
            stopKtpCamera();
        } else {
            if (btnCamera) btnCamera.classList.remove('active');
            if (btnUpload) btnUpload.classList.remove('active');
            if (panelCamera) panelCamera.style.display = 'none';
            if (panelUpload) panelUpload.style.display = 'none';
            stopKtpCamera();
        }
    }

    async function initKtpCamera() {
        stopKtpCamera();
        const video = document.getElementById('ktp_video');
        const errBox = document.getElementById('ktp_camera_error');
        const errMsg = document.getElementById('ktp_camera_error_msg');
        const guideFrame = document.getElementById('ktp_guide_frame');

        if (errBox) errBox.style.display = 'none';
        if (guideFrame) guideFrame.style.display = 'flex';

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            if (errBox && errMsg) {
                errMsg.textContent = 'Browser ini tidak mendukung akses kamera langsung. Silakan gunakan tombol Buka Kamera HP di atas.';
                errBox.style.display = 'flex';
            }
            return;
        }

        try {
            const constraints = {
                video: {
                    facingMode: { ideal: currentFacingMode },
                    width: { min: 1280, ideal: 1920 },
                    height: { min: 720, ideal: 1080 }
                },
                audio: false
            };

            let stream;
            try {
                stream = await navigator.mediaDevices.getUserMedia(constraints);
            } catch (fallbackErr) {
                stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
            }

            ktpCameraStream = stream;
            if (video) {
                video.srcObject = stream;
                await video.play();

                const track = stream.getVideoTracks()[0];
                if (track && track.applyConstraints) {
                    try {
                        await track.applyConstraints({
                            advanced: [{ focusMode: 'continuous' }]
                        });
                    } catch (e) {}
                }
            }
        } catch (err) {
            console.error('KTP Camera Error:', err);
            if (errBox && errMsg) {
                errMsg.textContent = 'Gagal mengakses kamera: ' + (err.message || 'Izin kamera ditolak.');
                errBox.style.display = 'flex';
            }
        }
    }

    function stopKtpCamera() {
        if (ktpCameraStream) {
            ktpCameraStream.getTracks().forEach(track => track.stop());
            ktpCameraStream = null;
        }
        const video = document.getElementById('ktp_video');
        if (video) video.srcObject = null;
    }

    function toggleCameraFacing() {
        currentFacingMode = (currentFacingMode === 'environment') ? 'user' : 'environment';
        initKtpCamera();
    }

    function captureKtpPhoto() {
        const video = document.getElementById('ktp_video');
        if (!video || !video.videoWidth) {
            alert('Kamera belum siap atau tidak aktif. Silakan tunggu atau gunakan tombol Kamera HP.');
            return;
        }

        const guide = document.getElementById('ktp_guide_frame');
        let cropX = 0, cropY = 0, cropW = video.videoWidth, cropH = video.videoHeight;

        if (guide && video.clientWidth && video.clientHeight) {
            const videoRect = video.getBoundingClientRect();
            const guideRect = guide.getBoundingClientRect();

            const scaleX = video.videoWidth / videoRect.width;
            const scaleY = video.videoHeight / videoRect.height;

            const relX = Math.max(0, guideRect.left - videoRect.left);
            const relY = Math.max(0, guideRect.top - videoRect.top);
            const relW = Math.min(videoRect.width - relX, guideRect.width);
            const relH = Math.min(videoRect.height - relY, guideRect.height);

            cropX = Math.round(relX * scaleX);
            cropY = Math.round(relY * scaleY);
            cropW = Math.round(relW * scaleX);
            cropH = Math.round(relH * scaleY);
        }

        const canvas = document.createElement('canvas');
        canvas.width = cropW;
        canvas.height = cropH;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, cropX, cropY, cropW, cropH, 0, 0, cropW, cropH);

        canvas.toBlob(function(blob) {
            const file = new File([blob], "ktp_scan_" + Date.now() + ".jpg", { type: "image/jpeg" });
            window.currentKtpFile = file;

            const img = document.getElementById('ktp_captured_img');
            const dataUrl = canvas.toDataURL('image/jpeg', 0.95);
            if (img) img.src = dataUrl;

            // Hide source selection buttons so preview and results are immediately visible without scrolling
            const srcSec = document.getElementById('ktp_source_selection');
            if (srcSec) srcSec.style.display = 'none';
            document.getElementById('ktp_panel_camera').style.display = 'none';
            document.getElementById('ktp_preview_section').style.display = 'block';
            stopKtpCamera();

            const tempImg = new Image();
            tempImg.onload = function() {
                window.lastRawSourceImg = tempImg;
                window.ktpRotation = 0;
                const processedCanvas = preprocessKtpCanvas(tempImg, 0);
                processKtpOcr(processedCanvas);
            };
            tempImg.src = dataUrl;
        }, 'image/jpeg', 0.95);
    }

    function handleKtpFileSelected(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            window.currentKtpFile = file;

            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('ktp_captured_img');
                img.src = e.target.result;

                const srcSec = document.getElementById('ktp_source_selection');
                if (srcSec) srcSec.style.display = 'none';
                document.getElementById('ktp_panel_camera').style.display = 'none';
                document.getElementById('ktp_panel_upload').style.display = 'none';
                document.getElementById('ktp_preview_section').style.display = 'block';
                stopKtpCamera();

                const tempImg = new Image();
                tempImg.onload = function() {
                    window.lastRawSourceImg = tempImg;
                    window.ktpRotation = 0;
                    const processedCanvas = preprocessKtpCanvas(tempImg, 0);
                    processKtpOcr(processedCanvas);
                };
                tempImg.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }

    function rotateKtpPhoto() {
        if (!window.lastRawSourceImg) return;
        window.ktpRotation = (window.ktpRotation + 90) % 360;

        const displayCanvas = document.createElement('canvas');
        const origW = window.lastRawSourceImg.naturalWidth || window.lastRawSourceImg.width;
        const origH = window.lastRawSourceImg.naturalHeight || window.lastRawSourceImg.height;

        const isRotated = (window.ktpRotation % 180 !== 0);
        displayCanvas.width = isRotated ? origH : origW;
        displayCanvas.height = isRotated ? origW : origH;

        const ctx = displayCanvas.getContext('2d');
        ctx.translate(displayCanvas.width / 2, displayCanvas.height / 2);
        ctx.rotate((window.ktpRotation * Math.PI) / 180);
        ctx.drawImage(window.lastRawSourceImg, -origW / 2, -origH / 2);

        const rotatedDataUrl = displayCanvas.toDataURL('image/jpeg', 0.95);
        document.getElementById('ktp_captured_img').src = rotatedDataUrl;

        displayCanvas.toBlob(function(blob) {
            window.currentKtpFile = new File([blob], "ktp_scan_rot_" + Date.now() + ".jpg", { type: "image/jpeg" });
        }, 'image/jpeg', 0.95);

        const processedCanvas = preprocessKtpCanvas(window.lastRawSourceImg, window.ktpRotation);
        processKtpOcr(processedCanvas);
    }

    function retakeKtpPhoto() {
        document.getElementById('ktp_preview_section').style.display = 'none';
        document.getElementById('ktp_result_card').style.display = 'none';
        document.getElementById('ktp_ocr_progress_box').style.display = 'none';
        document.getElementById('ktp_ocr_hint').style.display = 'none';
        const applyBtn = document.getElementById('btn_apply_ktp');
        applyBtn.disabled = true;
        applyBtn.style.opacity = '0.6';
        applyBtn.style.cursor = 'not-allowed';

        window.ktpRotation = 0;
        window.lastRawSourceImg = null;

        const srcSec = document.getElementById('ktp_source_selection');
        if (srcSec) srcSec.style.display = 'block';

        if (currentKtpSource === 'camera') {
            document.getElementById('ktp_panel_camera').style.display = 'block';
            initKtpCamera();
        } else if (currentKtpSource === 'upload') {
            document.getElementById('ktp_panel_upload').style.display = 'block';
            document.getElementById('ktp_file_input').value = '';
        } else {
            document.getElementById('ktp_native_cam').click();
        }
    }

    function preprocessKtpCanvas(sourceElement, rotateDeg = 0) {
        const canvas = document.createElement('canvas');
        let origWidth = sourceElement.naturalWidth || sourceElement.videoWidth || sourceElement.width;
        let origHeight = sourceElement.naturalHeight || sourceElement.videoHeight || sourceElement.height;

        let width = (rotateDeg % 180 === 0) ? origWidth : origHeight;
        let height = (rotateDeg % 180 === 0) ? origHeight : origWidth;

        // Scale to optimal OCR resolution (max 1800px)
        const maxDim = 1800;
        if (width > maxDim || height > maxDim) {
            if (width > height) {
                height = Math.round((height * maxDim) / width);
                width = maxDim;
            } else {
                width = Math.round((width * maxDim) / height);
                height = maxDim;
            }
        }

        canvas.width = width;
        canvas.height = height;
        const ctx = canvas.getContext('2d');

        if (rotateDeg !== 0) {
            ctx.translate(width / 2, height / 2);
            ctx.rotate((rotateDeg * Math.PI) / 180);
            const drawW = (rotateDeg % 180 === 0) ? width : height;
            const drawH = (rotateDeg % 180 === 0) ? height : width;
            ctx.drawImage(sourceElement, -drawW / 2, -drawH / 2, drawW, drawH);
            ctx.setTransform(1, 0, 0, 1, 0, 0);
        } else {
            ctx.drawImage(sourceElement, 0, 0, width, height);
        }

        try {
            const imgData = ctx.getImageData(0, 0, width, height);
            const d = imgData.data;

            // Preserve full grayscale gradients with balanced contrast curve
            // DO NOT convert to hard 1-bit black/white, because Leptonica/Tesseract internal engine
            // uses grayscale anti-aliasing to distinguish character strokes under variable lighting!
            for (let i = 0; i < d.length; i += 4) {
                const r = d[i], g = d[i + 1], b = d[i + 2];
                let gray = 0.299 * r + 0.587 * g + 0.114 * b;
                
                // Moderate contrast boost (1.35x)
                gray = ((gray - 128) * 1.35) + 128;
                if (gray < 0) gray = 0;
                if (gray > 255) gray = 255;

                d[i] = gray;
                d[i + 1] = gray;
                d[i + 2] = gray;
            }
            ctx.putImageData(imgData, 0, 0);
        } catch (e) {
            console.warn('Canvas pre-processing skipped:', e);
        }

        return canvas;
    }

    async function processKtpOcr(canvas) {
        const progressBox = document.getElementById('ktp_ocr_progress_box');
        const progressBar = document.getElementById('ktp_ocr_progress_bar');
        const progressPercent = document.getElementById('ktp_ocr_percent');
        const statusText = document.getElementById('ktp_ocr_status_text');
        const resultCard = document.getElementById('ktp_result_card');
        const applyBtn = document.getElementById('btn_apply_ktp');
        const hintBox = document.getElementById('ktp_ocr_hint');

        progressBox.style.display = 'block';
        progressBar.style.width = '15%';
        progressPercent.textContent = '15%';
        statusText.textContent = 'Memindai teks e-KTP...';

        try {
            if (typeof Tesseract === 'undefined') {
                throw new Error('Pustaka OCR Tesseract sedang memuat atau tidak tersedia.');
            }

            const { data: { text } } = await Tesseract.recognize(
                canvas,
                'ind+eng',
                {
                    logger: m => {
                        if (m.status === 'recognizing text') {
                            const pct = Math.round(m.progress * 100);
                            progressBar.style.width = Math.max(20, pct) + '%';
                            progressPercent.textContent = Math.max(20, pct) + '%';
                            statusText.textContent = 'Mengekstrak karakter (' + pct + '%)...';
                        }
                    }
                }
            );

            console.log('--- RAW OCR RESULT ---');
            console.log(text);

            progressBar.style.width = '100%';
            progressPercent.textContent = '100%';
            statusText.textContent = 'Pemindaian selesai!';

            const parsed = parseKtpOcr(text);

            document.getElementById('scan_nik_result').value = parsed.nik || '';
            document.getElementById('scan_nama_result').value = parsed.nama || '';
            document.getElementById('scan_alamat_result').value = parsed.alamat || '';

            validateScanNikBadge(parsed.nik);

            resultCard.style.display = 'block';
            hintBox.style.display = 'block';

            applyBtn.disabled = false;
            applyBtn.style.opacity = '1';
            applyBtn.style.cursor = 'pointer';

        } catch (err) {
            console.error('OCR Error:', err);
            statusText.textContent = 'Pemindaian selesai (hasil dapat disesuaikan manual).';
            progressBar.style.background = '#64748b';
            
            resultCard.style.display = 'block';
            hintBox.style.display = 'block';
            applyBtn.disabled = false;
            applyBtn.style.opacity = '1';
            applyBtn.style.cursor = 'pointer';
        }
    }

    function cleanName(val) {
        if (!val) return '';
        let cleaned = val.trim();

        // 1. Strip label patterns like 'Nama :', 'NAMA :', 'Narna :', 'N ama :', etc.
        cleaned = cleaned.replace(/^(?:N[a|A|4][m|rn|M][a|A|4]|N\s*ama|Narna|Hama|Mama|Name)[\s:\.;\-_]*/i, '');
        
        // 2. Strip leading colon, dashes, symbols
        cleaned = cleaned.replace(/^[:\s\-_\.]+/, '');

        // 3. Strip trailing fields if merged (Tempat/Tgl Lahir, Gol Darah, etc.)
        cleaned = cleaned.replace(/\b(?:TEMPAT|TGL|LAHIR|GOL|DARAH|JENIS|KELAMIN|AGAMA|WNI|ISLAM|NIK|KTP)\b.*$/i, '');

        // 4. Tokenize and remove noise words
        let tokens = cleaned.split(/\s+/).filter(Boolean);
        let validTokens = [];
        const noiseWords = ['ENG', 'INA', 'IDN', 'LAKI', 'PEREMPUAN', 'PROV', 'KAB', 'KOTA', 'REPUBLIK', 'INDONESIA'];
        for (let t of tokens) {
            let upper = t.toUpperCase().replace(/[^A-Z]/g, '');
            if (noiseWords.includes(upper)) continue;
            if (upper.length === 1 && validTokens.length > 0) continue;
            validTokens.push(t);
        }
        cleaned = validTokens.join(' ');
        cleaned = cleaned.replace(/[^a-zA-Z\s\.,'\-]/g, '').trim();

        // 5. Fix common ligature misread: 'LI' read as 'U' (e.g. ULIK -> LILIK)
        cleaned = cleaned.replace(/^ULIK([A-Z])/i, 'LILIK $1').replace(/^ULIK\b/i, 'LILIK');

        return cleaned;
    }

    function extractNama(lines, nikLineIdx) {
        // Strategy 1: Look for explicit 'Nama' label on any line
        const namaLabelRegex = /(?:N[a|A|4][m|rn|M][a|A|4]|N\s*ama|Narna|Hama|Mama|Name)[\s:\.;\-_]*/i;
        for (let i = 0; i < lines.length; i++) {
            let line = lines[i].trim();
            if (namaLabelRegex.test(line)) {
                let extracted = cleanName(line);
                if (extracted.length >= 3) {
                    return extracted;
                }
                // If the line only contained the label 'Nama :' with no value, check the NEXT line!
                if (i + 1 < lines.length) {
                    let nextLine = lines[i + 1].trim();
                    if (!/tempat|tgl|lahir|jenis|alamat|nik|agama|prov/i.test(nextLine)) {
                        let nextExtracted = cleanName(nextLine);
                        if (nextExtracted.length >= 3) {
                            return nextExtracted;
                        }
                    }
                }
            }
        }

        // Strategy 2: Positional detection between NIK and Tempat Lahir / Alamat
        // In every Indonesian e-KTP, the name is strictly located after NIK and before Lahir/Alamat
        if (nikLineIdx !== -1) {
            for (let i = nikLineIdx + 1; i < Math.min(lines.length, nikLineIdx + 4); i++) {
                let line = lines[i].trim();
                // Skip pure digit lines or NIK residue
                if (/^\d+$/.test(line.replace(/\s/g, ''))) continue;
                // Stop if reached Tempat Lahir / Alamat / Jenis Kelamin
                if (/tempat|tgl|lahir|jenis|kelamin|alamat|gol/i.test(line)) break;
                
                let candidate = cleanName(line);
                if (candidate.length >= 3) {
                    return candidate;
                }
            }
        }

        // Strategy 3: Look for line with colon that has a person's name format (2-4 uppercase words)
        for (let i = 1; i < Math.min(lines.length, 7); i++) {
            let line = lines[i].trim();
            if (/tempat|tgl|lahir|jenis|alamat|nik|prov|kab|kota|agama/i.test(line)) continue;
            if (/^\d+$/.test(line.replace(/\s/g, ''))) continue;
            let candidate = cleanName(line);
            if (candidate.length >= 3 && candidate.split(' ').length >= 1) {
                return candidate;
            }
        }

        return '';
    }

    function cleanAddressPart(val, type) {
        if (!val) return '';
        let cleaned = val.replace(/^[:\s\-_\+\.]+/, '').trim();

        if (type === 'jalan') {
            cleaned = cleaned.replace(/\s+[A-Z]{1,2}\s+\d.*$/, '');
            cleaned = cleaned.replace(/[^\w\s\.,\/\-]/g, '').trim();
        } else if (type === 'kelurahan' || type === 'kecamatan') {
            let words = cleaned.split(/\s+/).filter(Boolean);
            let validWords = [];
            for (let w of words) {
                let cleanW = w.replace(/[^A-Za-z]/g, '');
                if (cleanW.length < 2) continue;
                if (/^[a-z]+$/.test(w) && cleanW.length <= 3) continue;
                if (validWords.length > 0 && cleanW.length <= 2) continue;
                if (/^(AGAMA|STATUS|KAWIN|WNI|PEKERJAAN|BERLAKU)$/i.test(cleanW)) break;
                validWords.push(cleanW.toUpperCase());
                if (validWords.length >= 2) break;
            }
            cleaned = validWords.join(' ');
        }
        return cleaned;
    }

    function parseKtpOcr(rawText) {
        let result = { nik: '', nama: '', alamat: '' };
        if (!rawText) return result;

        const lines = rawText.split('\n').map(l => l.trim()).filter(Boolean);

        // 1. EXTRACT NIK (16 Digits)
        let nikLineIdx = -1;
        const digit16 = rawText.replace(/[^\d\s]/g, ' ').match(/\b\d{16}\b/);
        if (digit16) {
            result.nik = digit16[0];
            nikLineIdx = lines.findIndex(l => l.includes(result.nik) || /N[I|!1l\s]*K/i.test(l));
        } else {
            for (let i = 0; i < lines.length; i++) {
                let line = lines[i];
                if (/N[I|!1l\s]*K/i.test(line)) {
                    let clean = line.replace(/N[I|!1l\s]*K/i, '')
                                    .replace(/[:\s\-=._]/g, '')
                                    .replace(/[oO]/g, '0')
                                    .replace(/[dD]/g, '0')
                                    .replace(/[iIl|!]/g, '1')
                                    .replace(/[bB]/g, '8')
                                    .replace(/[sS]/g, '5')
                                    .replace(/[zZ]/g, '2')
                                    .replace(/\D/g, '');
                    if (clean.length >= 16) {
                        result.nik = clean.slice(0, 16);
                        nikLineIdx = i;
                        break;
                    }
                }
            }
        }

        if (nikLineIdx === -1) {
            nikLineIdx = lines.findIndex(l => /N[I|!1l\s]*K/i.test(l));
        }

        // 2. EXTRACT NAMA (Multi-strategy)
        result.nama = extractNama(lines, nikLineIdx);

        // 3. EXTRACT ALAMAT (Jalan, RT/RW, Kelurahan, Kecamatan)
        let jalan = '';
        let rtrw = '';
        let kel = '';
        let kec = '';

        for (let i = 0; i < lines.length; i++) {
            let line = lines[i];

            if (!jalan && /(?:ALAMAT|Alamat|AL4MAT)[\s:\.;\-_]+/i.test(line)) {
                let val = line.replace(/.*(?:ALAMAT|Alamat|AL4MAT)[\s:\.;\-_]+/i, '').trim();
                jalan = cleanAddressPart(val, 'jalan');
                if (!jalan && i + 1 < lines.length && !/rt|rw|kel|kec/i.test(lines[i + 1])) {
                    jalan = cleanAddressPart(lines[i + 1], 'jalan');
                }
            }

            if (!rtrw && /(?:RT[\s\/\.]*RW|RT\/RW|RTRW)[\s:\.;\-_]+/i.test(line)) {
                let match = line.match(/(\d{1,3}\s*[\/\-]\s*\d{1,3})/);
                if (match) {
                    rtrw = 'RT/RW ' + match[1].replace(/\s+/g, '');
                }
            }

            if (!kel && /(?:Kel[\/\s\.]*Desa|Kelurahan[\/\s\.]*Desa|Kelurahan|Desa)[\s:\.;\-_]+/i.test(line)) {
                let val = line.replace(/.*(?:Kel[\/\s\.]*Desa|Kelurahan[\/\s\.]*Desa|Kelurahan|Desa)[\s:\.;\-_]+/i, '').trim();
                let cleanedKel = cleanAddressPart(val, 'kelurahan');
                if (cleanedKel) kel = 'Kel. ' + cleanedKel;
            }

            if (!kec && /(?:Kecamatan|Kec)[\s:\.;\-_]+/i.test(line)) {
                let val = line.replace(/.*(?:Kecamatan|Kec)[\s:\.;\-_]+/i, '').trim();
                let cleanedKec = cleanAddressPart(val, 'kecamatan');
                if (cleanedKec) kec = 'Kec. ' + cleanedKec;
            }
        }

        let alamatParts = [jalan, rtrw, kel, kec].filter(Boolean);
        result.alamat = alamatParts.join(', ');

        return result;
    }

    function validateScanNikBadge(nik) {
        const badge = document.getElementById('scan_nik_badge');
        if (!badge) return;
        const cleanNik = (nik || '').replace(/\D/g, '');
        if (cleanNik.length === 16) {
            badge.textContent = '✓ 16 Digit';
            badge.style.background = '#dcfce7';
            badge.style.color = '#15803d';
        } else if (cleanNik.length > 0) {
            badge.textContent = cleanNik.length + ' Digit';
            badge.style.background = '#fef3c7';
            badge.style.color = '#b45309';
        } else {
            badge.textContent = 'Belum terdeteksi';
            badge.style.background = '#fee2e2';
            badge.style.color = '#b91c1c';
        }
    }

    function applyKtpDataToForm() {
        const nik = document.getElementById('scan_nik_result').value.trim();
        const nama = document.getElementById('scan_nama_result').value.trim();
        const alamat = document.getElementById('scan_alamat_result').value.trim();

        if (nik) document.getElementById('nik_modal').value = nik;
        if (nama) document.getElementById('nama_modal').value = nama;
        if (alamat) document.getElementById('alamat_ktp_modal').value = alamat;

        if (window.currentKtpFile) {
            try {
                const dt = new DataTransfer();
                dt.items.add(window.currentKtpFile);
                const fileInput = document.getElementById('foto_ktp_modal');
                fileInput.files = dt.files;
            } catch (err) {
                console.warn('DataTransfer not supported:', err);
            }
            updateKtpInputPreview(window.currentKtpFile);
        }

        stopKtpCamera();
        const scannerModal = document.getElementById('ktpScannerModal');
        if (scannerModal) scannerModal.classList.remove('active');

        const addModal = document.getElementById('addOrderModal');
        if (addModal) addModal.classList.add('active');

        const telpInput = document.getElementById('no_telp_modal');
        if (telpInput) telpInput.focus();

        showKtpToast('Data e-KTP berhasil diterapkan ke formulir!');
    }

    function updateKtpInputPreview(file) {
        const container = document.getElementById('ktp_preview_container');
        const img = document.getElementById('ktp_preview_img');
        if (container && img && file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
                container.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    }

    function showKtpToast(message) {
        const toast = document.createElement('div');
        toast.style.position = 'fixed';
        toast.style.bottom = '24px';
        toast.style.right = '24px';
        toast.style.background = '#1e293b';
        toast.style.color = '#f8fafc';
        toast.style.padding = '12px 20px';
        toast.style.borderRadius = '10px';
        toast.style.boxShadow = '0 10px 25px -5px rgba(0,0,0,0.3)';
        toast.style.zIndex = '10050';
        toast.style.fontSize = '0.9rem';
        toast.style.display = 'flex';
        toast.style.alignItems = 'center';
        toast.style.gap = '10px';
        toast.style.animation = 'fadeIn 0.3s ease';
        toast.innerHTML = '<i class="fa-solid fa-circle-check" style="color: #4ade80; font-size: 1.1rem;"></i> <span>' + message + '</span>';
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.5s ease';
            setTimeout(() => toast.remove(), 500);
        }, 4000);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const nikInput = document.getElementById('scan_nik_result');
        if (nikInput) {
            nikInput.addEventListener('input', function() {
                validateScanNikBadge(this.value);
            });
        }

        const dropzone = document.getElementById('ktp_dropzone');
        if (dropzone) {
            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.style.borderColor = '#4f46e5';
                    dropzone.style.background = '#eef2ff';
                }, false);
            });
            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.style.borderColor = '#cbd5e1';
                    dropzone.style.background = '#f8fafc';
                }, false);
            });
            dropzone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const files = dt.files;
                if (files && files.length > 0) {
                    const input = document.getElementById('ktp_file_input');
                    input.files = files;
                    handleKtpFileSelected(input);
                }
            }, false);
        }
    });
    </script>
@endsection
