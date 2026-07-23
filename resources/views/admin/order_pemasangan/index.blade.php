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
        grid-template-columns: {{ in_array(Auth::user()->level, ['sales', 'mitra']) ? 'minmax(0, 1.2fr) minmax(0, 0.8fr)' : 'minmax(0, 1fr)' }};
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

<div class="order-grid">
    <!-- Kolom Kiri: Tabel Order Pemasangan -->
    <div class="card" style="margin: 0;">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-truck-ramp-box"></i>
                <span>Daftar Order Pemasangan Baru</span>
            </div>
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
                        <tr>
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
                                @elseif($row->status === 'approved')
                                    <span class="status-badge status-approved">ACC (Dalam Pemasangan)</span>
                                @elseif($row->status === 'installed')
                                    <span class="status-badge status-installed">Selesai Dipasang (Verifikasi Admin)</span>
                                @elseif($row->status === 'completed')
                                    <span class="status-badge status-completed">Selesai & Aktif</span>
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
                                        @endif
                                        @if($row->status === 'approved')
                                            <button class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 8px; width: 100%; justify-content: center;" 
                                                onclick="openAssignModal('{{ $row->id }}')">
                                                <i class="fa-solid fa-user-plus"></i> {{ is_null($row->id_teknisi) ? 'Tugaskan Teknisi' : 'Ubah Teknisi' }}
                                            </button>
                                        @endif
                                    @endif

                                     <!-- Technician Actions -->
                                     @if(Auth::user()->level === 'teknisi')
                                         @if($row->status === 'approved')
                                             @if($row->id_teknisi === 0)
                                                 @php
                                                     $techHasActive = \App\Models\OrderPemasangan::where('id_teknisi', Auth::id())
                                                         ->whereIn('status', ['approved', 'installed'])
                                                         ->exists();
                                                 @endphp
                                                 @if($techHasActive)
                                                     <button type="button" class="btn btn-primary" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 8px; width: 100%; justify-content: center; opacity: 0.5; cursor: not-allowed;" disabled title="Selesaikan order aktif Anda terlebih dahulu sebelum mengambil order baru!">
                                                         <i class="fa-solid fa-hand-holding-hand"></i> Ambil Order (Ada Order Aktif)
                                                     </button>
                                                 @else
                                                     <form action="{{ route('admin.order_pemasangan.claim') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengambil order pemasangan ini?')">
                                                         @csrf
                                                         <input type="hidden" name="id_order" value="{{ $row->id }}">
                                                         <button type="submit" class="btn btn-primary" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 8px; width: 100%; justify-content: center; background: linear-gradient(135deg, #0ea5e9, #0284c7);">
                                                             <i class="fa-solid fa-hand-holding-hand"></i> Ambil Order
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

    <!-- Kolom Kanan: Form Upload Pelanggan Baru (Hanya untuk Sales/Marketing/Mitra) -->
    @if(in_array(Auth::user()->level, ['sales', 'mitra', 'admin']))
        <div class="card" style="margin: 0;">
            <div class="card-header">
                <div class="card-title">
                    <i class="fa-solid fa-file-circle-plus"></i>
                    <span>Form Pemasangan Baru</span>
                </div>
            </div>
            <div class="card-body" style="padding: 16px 0 0 0;">
                <form action="{{ route('admin.order_pemasangan.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="nik">NIK Pelanggan *</label>
                        <input type="text" id="nik" name="nik" class="form-control" required placeholder="Masukkan NIK 16 digit" value="{{ old('nik') }}">
                    </div>

                    <div class="form-group">
                        <label for="nama">Nama Lengkap *</label>
                        <input type="text" id="nama" name="nama" class="form-control" required placeholder="Contoh: Budi Santoso" value="{{ old('nama') }}">
                    </div>

                    <div class="form-group">
                        <label for="no_telp">Nomor WhatsApp Pelanggan *</label>
                        <input type="text" id="no_telp" name="no_telp" class="form-control" required placeholder="Contoh: 081234567890" value="{{ old('no_telp') }}">
                    </div>

                    <div class="form-group">
                        <label for="paket_order">Paket Internet Yang Diminta *</label>
                        <select id="paket_order" name="paket" class="form-control" required>
                            <option value="">-- Pilih Paket --</option>
                            @foreach($pakets as $p)
                                <option value="{{ $p->id_paket }}" {{ old('paket') == $p->id_paket ? 'selected' : '' }}>
                                    {{ $p->nama_paket }} (Rp {{ number_format($p->harga, 0, ',', '.') }} / Bulan)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="alamat_ktp">Alamat KTP *</label>
                        <textarea id="alamat_ktp" name="alamat_ktp" class="form-control" rows="2" required placeholder="Alamat lengkap sesuai KTP">{{ old('alamat_ktp') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="alamat_pemasangan">Alamat Pemasangan *</label>
                        <textarea id="alamat_pemasangan" name="alamat_pemasangan" class="form-control" rows="2" required placeholder="Alamat lengkap titik pemasangan">{{ old('alamat_pemasangan') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="koordinat_pemasangan">Koordinat Lokasi (Lat, Lng) *</label>
                        <div style="display: flex; gap: 8px;">
                            <input type="text" id="koordinat_pemasangan" name="koordinat_pemasangan" class="form-control" required placeholder="Contoh: -7.12345, 110.12345" value="{{ old('koordinat_pemasangan') }}" style="min-width: 0; flex-grow: 1;">
                            <button type="button" class="btn btn-info" onclick="getGPSCoordinates()" style="flex-shrink: 0; padding: 0 16px;">
                                <i class="fa-solid fa-location-crosshairs"></i> GPS HP
                            </button>
                        </div>
                        <small style="color:var(--text-gray);">Gunakan tombol GPS HP saat berada di lokasi pemasangan secara presisi.</small>
                    </div>

                    <div class="form-group">
                        <label for="jadwal_pemasangan">Permintaan Jadwal Pasang (Opsional)</label>
                        <input type="datetime-local" id="jadwal_pemasangan" name="jadwal_pemasangan" class="form-control" value="{{ old('jadwal_pemasangan') }}">
                    </div>

                    <div class="form-group">
                        <label for="foto_ktp">Upload Foto KTP *</label>
                        <input type="file" id="foto_ktp" name="foto_ktp" class="form-control" required accept="image/*">
                        <small style="color:var(--text-gray);">Maksimal ukuran file 5 MB (Format: jpeg, png, webp).</small>
                    </div>

                    <div style="margin-top:24px;">
                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; height: 44px;">
                            <i class="fa-solid fa-paper-plane"></i> Kirim Order Pemasangan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
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
                            <option value="{{ $tek->id }}" {{ isset($tek->has_active_order) && $tek->has_active_order ? 'disabled style=color:var(--text-gray);' : '' }}>
                                {{ $tek->nama_user }} ({{ $tek->username }}) {!! isset($tek->has_active_order) && $tek->has_active_order ? '— [Ada Order Aktif]' : '' !!}
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
                    <label for="paket">Pilih Paket Internet *</label>
                    <select id="paket" name="paket" class="form-control" required>
                        <option value="">-- Pilih Paket --</option>
                        @foreach($pakets as $p)
                            <option value="{{ $p->id_paket }}">{{ $p->nama_paket }} ({{ $p->harga }} / Bulan)</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="id_mikrotik">Pilih Router Mikrotik *</label>
                    <select id="id_mikrotik" name="id_mikrotik" class="form-control" required>
                        <option value="">-- Pilih Mikrotik --</option>
                        @foreach($mikrotiks as $m)
                            <option value="{{ $m->id_mikrotik }}">{{ $m->nama_mikrotik }} ({{ $m->ip }})</option>
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
            }, { enableHighAccuracy: true });
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
        
        // Pre-select the requested package
        if (order.paket) {
            document.getElementById('paket').value = order.paket;
        } else {
            document.getElementById('paket').value = '';
        }

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
                L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                    maxZoom: 20,
                    subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                    attribution: '&copy; Google Maps'
                }).addTo(odpMapInstance);
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
</script>
@endsection
