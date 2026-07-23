@extends('layouts.admin')

@section('title', 'Data Pelanggan')

@section('styles')
<!-- Load Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
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
        border-color: #cbd5e1;
    }

    .custom-select-container.active .custom-select-trigger {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .custom-select-dropdown {
        display: none;
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        background-color: white;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        z-index: 1010;
        overflow: hidden;
        animation: slideDown 0.2s ease;
    }

    .custom-select-container.active .custom-select-dropdown {
        display: block;
    }

    .custom-select-search-wrapper {
        position: relative;
        padding: 10px 12px;
        border-bottom: 1px solid var(--border-color);
        background-color: #f8fafc;
    }

    .custom-select-search-wrapper i {
        position: absolute;
        left: 22px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-gray);
        font-size: 0.85rem;
    }

    .custom-select-search-input.form-control {
        padding-left: 32px !important;
        height: 36px;
        font-size: 0.88rem;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background-color: white;
    }

    .custom-select-options {
        max-height: 200px;
        overflow-y: auto;
    }

    .custom-select-option {
        padding: 10px 14px;
        font-size: 0.92rem;
        color: var(--text-dark);
        cursor: pointer;
        transition: background-color 0.15s;
        text-align: left;
    }

    .custom-select-option:hover {
        background-color: #f1f5f9;
    }

    .custom-select-option.selected {
        background-color: #eff6ff;
        color: #2563eb;
        font-weight: 600;
    }

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

    /* Modal Styling */
    .modal {
        display: none;
        position: fixed;
        inset: 0;
        background-color: rgba(15, 23, 42, 0.5);
        z-index: 1000;
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
        width: min(560px, 100%);
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        border: 1px solid var(--border-color);
        animation: modalFadeIn 0.3s ease;
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
    @media (max-width: 600px) {
        .custom-select-container {
            width: 100%;
        }
        .modal-content {
            border-radius: 16px;
        }
        .modal-body {
            padding: 16px;
        }
    }

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
        display: inline-flex !important;
        align-items: center !important;
        width: 100% !important;
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
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-users"></i>
            <span>Daftar Pelanggan Billing Internet</span>
        </div>
        <button class="btn btn-primary" onclick="openAddModal()">
            <i class="fa-solid fa-plus"></i> Tambah Pelanggan
        </button>
    </div>

    <!-- Pilihan device mikrotik untuk sinkronisasi -->
    @if($checkUser && $checkUser->status == 'ya')
        <div style="background-color: #f1f5f9; padding: 16px; border-radius: 14px; margin-bottom: 20px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px;">
            <div style="font-size:0.9rem; color: #475569; font-weight:500;">
                <i class="fa-solid fa-circle-nodes" style="margin-right:6px; color:#4f46e5;"></i>
                Ambil data PPP Secret dari Mikrotik Device aktif:
            </div>
            <form method="GET" action="{{ route('admin.pelanggan.index') }}" id="deviceForm" style="width: 100%; max-width: 320px;">
                <select class="form-control" name="device_id" onchange="this.form.submit()" style="width: 100%; max-width: 100%; min-width: 0; display:inline-block;">
                    @foreach($mikrotiks as $m)
                        <option value="{{ $m->id_mikrotik }}" {{ $m->id_mikrotik == $selected_device_id ? 'selected' : '' }}>
                            {{ $m->nama_mikrotik ?: 'Router #' . $m->id_mikrotik }} ({{ $m->ip }})
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    @endif

    @if(isset($mikrotikError) && $mikrotikError)
        <div style="background-color: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; padding: 14px 18px; border-radius: 14px; margin-bottom: 20px; font-size: 0.9rem; display: flex; align-items: center; gap: 10px; box-shadow: var(--shadow-sm);">
            <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.2rem; color: #dc2626;"></i>
            <span><strong>Masalah Koneksi Router:</strong> {{ $mikrotikError }}</span>
        </div>
    @endif

    <!-- Search and Row Limiter & Branch Filters -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-top: 10px; margin-bottom:16px; flex-wrap:wrap; gap:12px;">
        <form method="GET" action="{{ route('admin.pelanggan.index') }}" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin:0;" id="filterForm">
            <select name="branch_id" id="filter_branch" class="form-control" style="width: auto; height: 40px; border-radius: 10px; font-size: 0.85rem; margin: 0;" onchange="filterSubBranches(this.value, 'filter_sub_branch'); this.form.submit();">
                <option value="">-- Semua Branch --</option>
                @foreach($branches as $b)
                    <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->nama_branch }}</option>
                @endforeach
            </select>
            <select name="sub_branch_id" id="filter_sub_branch" class="form-control" style="width: auto; height: 40px; border-radius: 10px; font-size: 0.85rem; margin: 0;" onchange="this.form.submit();">
                <option value="">-- Semua Sub-Branch --</option>
                @foreach($subBranches as $s)
                    <option value="{{ $s->id }}" data-branch="{{ $s->id_branch }}" {{ request('sub_branch_id') == $s->id ? 'selected' : '' }} style="{{ request('branch_id') && request('branch_id') != $s->id_branch ? 'display:none;' : '' }}">{{ $s->nama_sub_branch }}</option>
                @endforeach
            </select>
            @if(request('branch_id') || request('sub_branch_id'))
                <a href="{{ route('admin.pelanggan.index') }}" class="btn btn-secondary" style="height: 40px; padding: 0 12px; border-radius: 10px; display:inline-flex; align-items:center; font-size:0.85rem;"><i class="fa-solid fa-rotate-left"></i> Reset</a>
            @endif
        </form>
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
                <input type="text" id="tableSearch" class="form-control" placeholder="Cari pelanggan..." value="{{ request('search') }}" style="padding-left:36px; height:40px; border-radius:10px; width:100%;">
            </div>
        </div>
    </div>

    <div class="table-container">
        <table class="table" id="pelangganTable">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Nama Pelanggan</th>
                    <th>Branch / Sub</th>
                    <th>Alamat</th>
                    <th>No Telp</th>
                    <th>Paket</th>
                    <th>IP Address</th>
                    <th>Jatuh Tempo</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pelanggan as $index => $p)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><span style="font-family: monospace; font-weight: 600; color:#4f46e5;">{{ $p->kode_pelanggan }}</span></td>
                        <td><strong>{{ $p->nama_pelanggan }}</strong></td>
                        <td>
                            @if($p->branch)
                                <span style="font-size:0.85rem; font-weight:600; color:#4f46e5;">{{ $p->branch->nama_branch }}</span>
                                @if($p->subBranch)
                                    <div style="font-size:0.75rem; color:var(--text-gray);">{{ $p->subBranch->nama_sub_branch }}</div>
                                @endif
                            @else
                                <span style="font-size:0.8rem; color:#94a3b8; font-style:italic;">Belum diatur</span>
                            @endif
                        </td>
                        <td>{{ $p->alamat }}</td>
                        <td>{{ $p->no_telp }}</td>
                        <td>{{ $p->paketDetail->nama_paket ?? 'N/A' }}</td>
                        <td>{{ $p->ip_address ?? '-' }}</td>
                        <td>{{ $p->jatuh_tempo }}</td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                <button class="btn btn-info" style="padding: 6px 12px; font-size: 0.8rem;" 
                                    onclick='openEditModal({!! json_encode($p) !!})'>
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </button>
                                <button class="btn btn-danger" style="padding: 6px 12px; font-size: 0.8rem;" 
                                    onclick="openDeleteModal('{{ $p->id_pelanggan }}', '{{ $p->nama_pelanggan }}')">
                                    <i class="fa-solid fa-trash-can"></i> Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; color: var(--text-gray); padding: 30px;">
                            Belum ada data pelanggan di database.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div id="pelangganPagination"></div>
</div>

<!-- Modal Tambah Pelanggan -->
<div class="modal" id="addModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Tambah Pelanggan Baru</h3>
            <button class="modal-close" onclick="closeAddModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="addPelangganForm" action="{{ route('admin.pelanggan.store') }}" method="POST">
                @csrf
                <input type="hidden" name="id_mikrotik" value="{{ $selected_device_id }}">

                @if($checkUser && $checkUser->status == 'ya')
                    <div class="form-group" style="background:#f8fafc; padding:12px; border-radius:12px; border: 1px dashed #cbd5e1;">
                        <label>Ambil Data Dari Secret Mikrotik (Opsional)</label>
                        <!-- Hidden input to store selected secret -->
                        <input type="hidden" id="import_mikrotik" name="import_mikrotik_val">
                        
                        <!-- Custom Searchable Dropdown for Mikrotik Secret -->
                        <div class="custom-select-container" id="custom_secret_select">
                            <div class="custom-select-trigger" onclick="toggleSecretDropdown()">
                                <span id="custom_secret_text">-- Pilih Secret --</span>
                                <i class="fa-solid fa-chevron-down" style="font-size: 0.8rem; color: var(--text-gray);"></i>
                            </div>
                            <div class="custom-select-dropdown" id="custom_secret_dropdown">
                                <div class="custom-select-search-wrapper">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                    <input type="text" id="search_secret" class="form-control custom-select-search-input" placeholder="Cari secret..." autocomplete="off">
                                </div>
                                <div class="custom-select-options" id="custom_secret_options">
                                    <div class="custom-select-option selected" data-value="" data-text="-- Pilih Secret --" data-pass="">
                                        -- Pilih Secret --
                                    </div>
                                    <div id="secrets_loading_status" style="padding:10px; text-align:center; color: var(--text-gray); font-size:0.85rem;">
                                        <i class="fa-solid fa-spinner fa-spin" style="margin-right:6px;"></i> Menghubungkan ke Mikrotik...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="form-group">
                    <label for="nik">NIK KTP</label>
                    <input type="text" id="nik" name="nik" class="form-control" placeholder="Masukkan NIK KTP (kosongkan jika tidak ada)">
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label for="username">Username Akun *</label>
                        <input type="text" id="username" name="username" class="form-control" required placeholder="Untuk login & Mikrotik">
                    </div>
                    <div class="form-group">
                        <label for="password">Password Akun *</label>
                        <input type="text" id="password" name="password" class="form-control" required placeholder="Password login & Mikrotik">
                    </div>
                </div>

                <div class="form-group">
                    <label for="nama">Nama Pelanggan *</label>
                    <input type="text" id="nama" name="nama" class="form-control" required placeholder="Nama lengkap pelanggan">
                </div>

                <div class="form-group">
                    <label for="alamat">Alamat Lengkap</label>
                    <textarea id="alamat" name="alamat" class="form-control" rows="2" placeholder="Alamat pemasangan"></textarea>
                </div>

                <div class="form-group">
                    <label for="no_telp">No. Telepon / WhatsApp *</label>
                    <input type="text" id="no_telp" name="no_telp" class="form-control" required placeholder="Contoh: 089612345678">
                </div>

                <div class="form-group">
                    <label for="ip_address">IP Address Pelanggan</label>
                    <input type="text" id="ip_address" name="ip_address" class="form-control" placeholder="Contoh: 192.168.10.50">
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label for="add_branch">Branch *</label>
                        <select id="add_branch" name="id_branch" class="form-control" required onchange="filterSubBranches(this.value, 'add_sub_branch')">
                            <option value="">-- Pilih Branch --</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->nama_branch }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="add_sub_branch">Sub-Branch *</label>
                        <select id="add_sub_branch" name="id_sub_branch" class="form-control" required>
                            <option value="">-- Pilih Sub-Branch --</option>
                            @foreach($subBranches as $s)
                                <option value="{{ $s->id }}" data-branch="{{ $s->id_branch }}">{{ $s->nama_sub_branch }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label for="paket">Paket Langganan *</label>
                        <select id="paket" name="paket" class="form-control" required>
                            @foreach($pakets as $paket)
                                <option value="{{ $paket->id_paket }}">{{ $paket->nama_paket }} (Rp {{ number_format($paket->harga, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="nama_perangkat">Perangkat Modem</label>
                        <select id="nama_perangkat" name="nama_perangkat" class="form-control">
                            <option value="NULL">-- Tanpa Perangkat --</option>
                            @foreach($perangkats as $device)
                                <option value="{{ $device->id_perangkat }}">{{ $device->nama_perangkat }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label for="odp" style="margin: 0; font-weight: 600;">Mulai Pemasangan Dari ODP</label>
                        <button type="button" class="btn btn-info" id="btn_rekomendasi_odp_add" onclick="openOdpRecommendationModal('add')" style="padding: 4px 10px; font-size: 0.75rem; border-radius: 8px; height: auto; display: flex; align-items: center; gap: 4px;">
                            <i class="fa-solid fa-map-location-dot"></i> Rekomendasi ODP
                        </button>
                    </div>
                    <select id="odp" name="odp" class="form-control" style="display: none;">
                        <option value="NULL">-- Tanpa ODP --</option>
                        @foreach($odps as $odp)
                            <option value="{{ $odp->id_odp }}">{{ $odp->nama_odp }} (Port: {{ $odp->port_odp }})</option>
                        @endforeach
                    </select>
                    
                    <!-- Custom Searchable Dropdown for ODP -->
                    <div class="custom-select-container" id="custom_odp_select">
                        <div class="custom-select-trigger" onclick="toggleOdpDropdown('add')">
                            <span id="custom_odp_text">-- Tanpa ODP --</span>
                            <i class="fa-solid fa-chevron-down" style="font-size: 0.8rem; color: var(--text-gray);"></i>
                        </div>
                        <div class="custom-select-dropdown" id="custom_odp_dropdown_add">
                            <div class="custom-select-search-wrapper">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" id="search_odp_add" class="form-control custom-select-search-input" placeholder="Cari ODP..." autocomplete="off">
                            </div>
                            <div class="custom-select-options" id="custom_odp_options_add">
                                <div class="custom-select-option selected" data-value="NULL" data-text="-- Tanpa ODP --">
                                    -- Tanpa ODP --
                                </div>
                                @foreach($odps as $odp)
                                    <div class="custom-select-option" data-value="{{ $odp->id_odp }}" data-text="{{ $odp->nama_odp }} (Port: {{ $odp->port_odp }})">
                                        {{ $odp->nama_odp }} (Port: {{ $odp->port_odp }})
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="mapping">Koordinat Lokasi (Lat, Lng)</label>
                    <div style="display: flex; gap: 8px; margin-bottom: 8px;">
                        <input type="text" id="mapping" name="mapping" class="form-control" placeholder="Contoh: -6.200000,106.816666">
                        <button type="button" class="btn btn-info" id="btn-gps-add" style="padding: 10px; border-radius: 12px;" title="Ambil GPS HP"><i class="fa-solid fa-location-crosshairs"></i></button>
                        <button type="button" class="btn btn-primary" id="btn-map-add-toggle" style="padding: 10px; border-radius: 12px;" title="Pilih dari Peta"><i class="fa-solid fa-map-location-dot"></i></button>
                    </div>
                    <div id="map-add-picker" style="height: 200px; border-radius: 12px; border: 1px solid var(--border-color); display: none; z-index: 1;"></div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                    <button type="button" class="btn btn-secondary" onclick="closeAddModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Pelanggan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Pelanggan -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Ubah Detail Pelanggan</h3>
            <button class="modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('admin.pelanggan.update') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin akan merubah data pelanggan tersebut?');">
                @csrf
                <input type="hidden" name="id_pelanggan" id="edit_id">

                <div class="form-group">
                    <label for="edit_nik">NIK KTP</label>
                    <input type="text" id="edit_nik" name="nik" class="form-control">
                </div>

                <div class="form-group">
                    <label for="edit_nama">Nama Pelanggan *</label>
                    <input type="text" id="edit_nama" name="nama_pelanggan" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="edit_alamat">Alamat Lengkap</label>
                    <textarea id="edit_alamat" name="alamat" class="form-control" rows="2"></textarea>
                </div>

                <div class="form-group">
                    <label for="edit_no_telp">No. Telepon / WhatsApp *</label>
                    <input type="text" id="edit_no_telp" name="no_telp" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="edit_ip_address">IP Address Pelanggan</label>
                    <input type="text" id="edit_ip_address" name="ip_address" class="form-control">
                </div>

                <div class="form-group">
                    <label for="edit_jatuh_tempo">Tanggal Jatuh Tempo *</label>
                    <input type="date" id="edit_jatuh_tempo" name="jatuh_tempo" class="form-control" required>
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label for="edit_branch">Branch *</label>
                        <select id="edit_branch" name="id_branch" class="form-control" required onchange="filterSubBranches(this.value, 'edit_sub_branch')">
                            <option value="">-- Pilih Branch --</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->nama_branch }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_sub_branch">Sub-Branch *</label>
                        <select id="edit_sub_branch" name="id_sub_branch" class="form-control" required>
                            <option value="">-- Pilih Sub-Branch --</option>
                            @foreach($subBranches as $s)
                                <option value="{{ $s->id }}" data-branch="{{ $s->id_branch }}">{{ $s->nama_sub_branch }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label for="edit_paket">Paket Langganan *</label>
                        <select id="edit_paket" name="paket" class="form-control" required>
                            @foreach($pakets as $paket)
                                <option value="{{ $paket->id_paket }}">{{ $paket->nama_paket }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_nama_perangkat">Perangkat Modem</label>
                        <select id="edit_nama_perangkat" name="nama_perangkat" class="form-control">
                            <option value="NULL">-- Tanpa Perangkat --</option>
                            @foreach($perangkats as $device)
                                <option value="{{ $device->id_perangkat }}">{{ $device->nama_perangkat }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_id_mikrotik">Pilih Router Mikrotik *</label>
                    <select id="edit_id_mikrotik" name="id_mikrotik" class="form-control" required>
                        @foreach($mikrotiks as $m)
                            <option value="{{ $m->id_mikrotik }}">{{ $m->nama_mikrotik ?: 'Router #' . $m->id_mikrotik }} ({{ $m->ip }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label for="edit_odp" style="margin: 0; font-weight: 600;">Mulai Pemasangan Dari ODP</label>
                        <button type="button" class="btn btn-info" id="btn_rekomendasi_odp_edit" onclick="openOdpRecommendationModal('edit')" style="padding: 4px 10px; font-size: 0.75rem; border-radius: 8px; height: auto; display: flex; align-items: center; gap: 4px;">
                            <i class="fa-solid fa-map-location-dot"></i> Rekomendasi ODP
                        </button>
                    </div>
                    <select id="edit_odp" name="odp" class="form-control" style="display: none;">
                        <option value="NULL">-- Tanpa ODP --</option>
                        @foreach($odps as $odp)
                            <option value="{{ $odp->id_odp }}">{{ $odp->nama_odp }}</option>
                        @endforeach
                    </select>

                    <!-- Custom Searchable Dropdown for Edit ODP -->
                    <div class="custom-select-container" id="custom_edit_odp_select">
                        <div class="custom-select-trigger" onclick="toggleOdpDropdown('edit')">
                            <span id="custom_edit_odp_text">-- Tanpa ODP --</span>
                            <i class="fa-solid fa-chevron-down" style="font-size: 0.8rem; color: var(--text-gray);"></i>
                        </div>
                        <div class="custom-select-dropdown" id="custom_odp_dropdown_edit">
                            <div class="custom-select-search-wrapper">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" id="search_odp_edit" class="form-control custom-select-search-input" placeholder="Cari ODP..." autocomplete="off">
                            </div>
                            <div class="custom-select-options" id="custom_odp_options_edit">
                                <div class="custom-select-option selected" data-value="NULL" data-text="-- Tanpa ODP --">
                                    -- Tanpa ODP --
                                </div>
                                @foreach($odps as $odp)
                                    <div class="custom-select-option" data-value="{{ $odp->id_odp }}" data-text="{{ $odp->nama_odp }}">
                                        {{ $odp->nama_odp }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_mapping">Koordinat Lokasi (Lat, Lng)</label>
                    <div style="display: flex; gap: 8px; margin-bottom: 8px;">
                        <input type="text" id="edit_mapping" name="mapping" class="form-control" placeholder="Contoh: -6.200000,106.816666">
                        <button type="button" class="btn btn-info" id="btn-gps-edit" style="padding: 10px; border-radius: 12px;" title="Ambil GPS HP"><i class="fa-solid fa-location-crosshairs"></i></button>
                        <button type="button" class="btn btn-primary" id="btn-map-edit-toggle" style="padding: 10px; border-radius: 12px;" title="Pilih dari Peta"><i class="fa-solid fa-map-location-dot"></i></button>
                    </div>
                    <div id="map-edit-picker" style="height: 200px; border-radius: 12px; border: 1px solid var(--border-color); display: none; z-index: 1;"></div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Hapus Pelanggan -->
<div class="modal" id="deleteModal">
    <div class="modal-content" style="width: min(440px, 100%);">
        <div class="modal-header" style="background:#dc2626;">
            <h3>Hapus Pelanggan</h3>
            <button class="modal-close" onclick="closeDeleteModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('admin.pelanggan.destroy') }}" method="POST">
                @csrf
                <input type="hidden" name="id_pelanggan" id="delete_id">
                <p style="font-size:0.95rem; margin-bottom:15px; line-height:1.5; color:#334155;">
                    Apakah Anda yakin ingin menghapus pelanggan <strong id="delete_name"></strong>?<br>
                    Tindakan ini juga akan menghapus akun login pelanggan dan secret PPPOE di router Mikrotik jika sinkronisasi aktif.
                </p>
                <div class="form-group" style="text-align: left; margin-bottom: 20px;">
                    <label for="delete_alasan_hapus" style="font-weight: 600; font-size: 0.85rem; color: #334155; display: block; margin-bottom: 6px;">Alasan Penghapusan *</label>
                    <textarea name="alasan_hapus" id="delete_alasan_hapus" rows="3" class="form-control" placeholder="Masukkan alasan kenapa pelanggan ini dihapus..." required style="border: 1px solid #cbd5e1; border-radius: 10px; padding: 8px 12px; font-size: 0.9rem; outline: none; width: 100%; resize: vertical; font-family: inherit;"></textarea>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Batal</button>
                    <button type="submit" class="btn btn-danger" style="background-color:#dc2626; color:white;">Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Map ODP -->
<div class="modal" id="odpMapModal" style="z-index: 10005;">
    <div class="modal-content" style="width: min(850px, 95vw); max-width: 850px;">
        <div class="modal-header" style="background: var(--primary-gradient); color: white; display: flex; align-items: center; justify-content: space-between; padding: 20px 24px;">
            <h3 style="margin: 0; font-family: 'Outfit', sans-serif; font-size: 1.2rem; font-weight: 700;">Rekomendasi ODP Terdekat (Peta Topologi)</h3>
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
@endsection

@section('scripts')
<!-- Load jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Load Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    let allSubBranchOptionsAdd = [];
    let allSubBranchOptionsEdit = [];

    document.addEventListener("DOMContentLoaded", function () {
        setupTablePagination("#pelangganTable", "#pelangganPagination", "#tableLimit", "#tableSearch");
        setupCustomSecretSelect();
        setupCustomOdpSelect('add');
        setupCustomOdpSelect('edit');

        // Save original sub-branch options once
        document.querySelectorAll('#add_sub_branch option').forEach(opt => {
            allSubBranchOptionsAdd.push({
                value: opt.value,
                text: opt.textContent,
                branch: opt.getAttribute('data-branch')
            });
        });
        document.querySelectorAll('#edit_sub_branch option').forEach(opt => {
            allSubBranchOptionsEdit.push({
                value: opt.value,
                text: opt.textContent,
                branch: opt.getAttribute('data-branch')
            });
        });

        // Initialize Select2 with dynamic configurations
        $('#add_branch').select2({ placeholder: "-- Pilih Branch --", allowClear: true, width: '100%' });
        $('#add_sub_branch').select2({ placeholder: "-- Pilih Sub-Branch --", allowClear: true, width: '100%' });
        $('#edit_branch').select2({ placeholder: "-- Pilih Branch --", allowClear: true, width: '100%' });
        $('#edit_sub_branch').select2({ placeholder: "-- Pilih Sub-Branch --", allowClear: true, width: '100%' });
    });

    // Modal Management
    function openAddModal() {
        document.getElementById('mapping').value = '';
        document.getElementById('map-add-picker').style.display = 'none';
        
        // Reset custom secret select
        const hiddenSecret = document.getElementById('import_mikrotik');
        if (hiddenSecret) {
            hiddenSecret.value = '';
            document.getElementById('custom_secret_text').textContent = '-- Pilih Secret --';
            const options = document.querySelectorAll('#custom_secret_options .custom-select-option');
            options.forEach(el => el.classList.remove('selected'));
            if (options[0]) options[0].classList.add('selected');
        }

        // Reset fields
        document.getElementById('username').value = '';
        document.getElementById('password').value = '';
        document.getElementById('nama').value = '';
        $('#add_branch').val('').trigger('change.select2');
        $('#add_sub_branch').val('').trigger('change.select2');
        
        document.getElementById('odp').value = 'NULL';
        syncCustomOdpText('add');

        document.getElementById('addModal').classList.add('active');
        
        // Load secrets asynchronously
        loadMikrotikSecretsAsync();
    }
    function closeAddModal() {
        document.getElementById('addModal').classList.remove('active');
    }

    function openEditModal(pelanggan) {
        document.getElementById('edit_id').value = pelanggan.id_pelanggan;
        document.getElementById('edit_nik').value = pelanggan.nik || '';
        document.getElementById('edit_nama').value = pelanggan.nama_pelanggan;
        document.getElementById('edit_alamat').value = pelanggan.alamat || '';
        document.getElementById('edit_no_telp').value = pelanggan.no_telp;
        document.getElementById('edit_ip_address').value = pelanggan.ip_address || '';
        document.getElementById('edit_paket').value = pelanggan.paket;
        document.getElementById('edit_nama_perangkat').value = pelanggan.id_perangkat || 'NULL';
        document.getElementById('edit_odp').value = pelanggan.odp || 'NULL';
        syncCustomOdpText('edit');
        
        document.getElementById('edit_id_mikrotik').value = pelanggan.id_mikrotik || 1;
        document.getElementById('edit_mapping').value = pelanggan.location || '';
        document.getElementById('edit_jatuh_tempo').value = pelanggan.jatuh_tempo ? pelanggan.jatuh_tempo.substring(0, 10) : '';
        
        // Set branch & sub-branch values
        $('#edit_branch').val(pelanggan.id_branch || '').trigger('change.select2');
        filterSubBranches(pelanggan.id_branch || '', 'edit_sub_branch');
        $('#edit_sub_branch').val(pelanggan.id_sub_branch || '').trigger('change.select2');
        
        document.getElementById('map-edit-picker').style.display = 'none';
        document.getElementById('editModal').classList.add('active');
    }
    function closeEditModal() {
        document.getElementById('editModal').classList.remove('active');
    }

    // Dynamic filtering for sub-branches based on branch selection (Select2 compatible)
    function filterSubBranches(branchId, targetSelectId) {
        const select = $('#' + targetSelectId);
        if (select.length === 0) return;
        
        // Clear current options
        select.empty();
        
        let visibleCount = 0;
        const originalOptions = (targetSelectId === 'add_sub_branch') ? allSubBranchOptionsAdd : allSubBranchOptionsEdit;
        
        // Filter and append
        originalOptions.forEach(opt => {
            if (!opt.branch) {
                // Keep placeholder
                select.append(new Option(opt.text, opt.value));
            } else if (branchId === '' || opt.branch == branchId) {
                select.append(new Option(opt.text, opt.value));
                visibleCount++;
            }
        });
        
        const label = document.getElementById(targetSelectId).parentElement.querySelector('label');
        const nativeSelect = document.getElementById(targetSelectId);

        // If there are no sub-branches for this branch, make it optional
        if (visibleCount === 0 && branchId !== '') {
            nativeSelect.required = false;
            if (label) {
                label.innerHTML = 'Sub-Branch <span style="font-size: 0.8rem; color: #94a3b8; font-weight: normal;">(Opsional/Tidak Ada)</span>';
            }
            select.find('option[value=""]').text('-- Tidak Ada Sub-Branch --');
        } else {
            nativeSelect.required = true;
            if (label) {
                label.innerHTML = 'Sub-Branch <span style="color: #ef4444;">*</span>';
            }
            select.find('option[value=""]').text('-- Pilih Sub-Branch --');
        }
        
        // Re-trigger Select2 change without recursively calling filterSubBranches
        select.val('').trigger('change.select2');
    }

    function openDeleteModal(id, name) {
        document.getElementById('delete_id').value = id;
        document.getElementById('delete_name').innerText = name;
        document.getElementById('delete_alasan_hapus').value = '';
        document.getElementById('deleteModal').classList.add('active');
    }
    function closeDeleteModal() {
        document.getElementById('delete_alasan_hapus').value = '';
        document.getElementById('deleteModal').classList.remove('active');
    }

    // Toggle custom dropdown
    function toggleSecretDropdown() {
        const container = document.getElementById('custom_secret_select');
        if (container) {
            container.classList.toggle('active');
            if (container.classList.contains('active')) {
                const searchInput = document.getElementById('search_secret');
                if (searchInput) {
                    searchInput.value = '';
                    searchInput.dispatchEvent(new Event('input'));
                    searchInput.focus();
                }
            }
        }
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const container = document.getElementById('custom_secret_select');
        if (container && !container.contains(e.target)) {
            container.classList.remove('active');
        }

        const odpContainer = document.getElementById('custom_odp_select');
        if (odpContainer && !odpContainer.contains(e.target)) {
            odpContainer.classList.remove('active');
        }

        const editOdpContainer = document.getElementById('custom_edit_odp_select');
        if (editOdpContainer && !editOdpContainer.contains(e.target)) {
            editOdpContainer.classList.remove('active');
        }
    });

    // Toggle ODP dropdowns
    function toggleOdpDropdown(type) {
        const container = document.getElementById(type === 'add' ? 'custom_odp_select' : 'custom_edit_odp_select');
        if (container) {
            container.classList.toggle('active');
            if (container.classList.contains('active')) {
                const searchInput = document.getElementById(type === 'add' ? 'search_odp_add' : 'search_odp_edit');
                if (searchInput) {
                    searchInput.value = '';
                    searchInput.dispatchEvent(new Event('input'));
                    searchInput.focus();
                }
            }
        }
    }

    function setupCustomOdpSelect(type) {
        const container = document.getElementById(type === 'add' ? 'custom_odp_select' : 'custom_edit_odp_select');
        const hiddenSelect = document.getElementById(type === 'add' ? 'odp' : 'edit_odp');
        const triggerText = document.getElementById(type === 'add' ? 'custom_odp_text' : 'custom_edit_odp_text');
        const optionsList = document.getElementById(type === 'add' ? 'custom_odp_options_add' : 'custom_odp_options_edit');
        const searchInput = document.getElementById(type === 'add' ? 'search_odp_add' : 'search_odp_edit');

        if (!container || !hiddenSelect || !triggerText || !optionsList || !searchInput) return;

        // Use event delegation for option clicks
        optionsList.onclick = function(e) {
            const opt = e.target.closest('.custom-select-option');
            if (!opt) return;

            const val = opt.getAttribute('data-value');
            const text = opt.getAttribute('data-text');

            // Set values
            hiddenSelect.value = val;
            triggerText.textContent = text;

            // Trigger change event
            hiddenSelect.dispatchEvent(new Event('change'));

            // Update styling
            const optionElements = optionsList.querySelectorAll('.custom-select-option');
            optionElements.forEach(el => el.classList.remove('selected'));
            opt.classList.add('selected');

            // Hide dropdown
            container.classList.remove('active');
        };

        // Filter options on search
        searchInput.oninput = function() {
            const query = this.value.toLowerCase().trim();
            const optionElements = optionsList.querySelectorAll('.custom-select-option');

            optionElements.forEach((opt, index) => {
                if (index === 0) {
                    opt.style.display = 'block'; // Always show placeholder
                    return;
                }
                const text = opt.getAttribute('data-text').toLowerCase();
                if (text.includes(query)) {
                    opt.style.display = 'block';
                } else {
                    opt.style.display = 'none';
                }
            });
        };

        // Prevent closing menu when typing in search input
        searchInput.onclick = function(e) {
            e.stopPropagation();
        };
    }

    function syncCustomOdpText(type) {
        const hiddenSelect = document.getElementById(type === 'add' ? 'odp' : 'edit_odp');
        const triggerText = document.getElementById(type === 'add' ? 'custom_odp_text' : 'custom_edit_odp_text');
        const optionsList = document.getElementById(type === 'add' ? 'custom_odp_options_add' : 'custom_odp_options_edit');

        if (!hiddenSelect || !triggerText || !optionsList) return;

        const val = hiddenSelect.value;
        const selectedOption = optionsList.querySelector(`.custom-select-option[data-value="${val}"]`);
        
        optionsList.querySelectorAll('.custom-select-option').forEach(el => el.classList.remove('selected'));

        if (selectedOption) {
            triggerText.textContent = selectedOption.getAttribute('data-text');
            selectedOption.classList.add('selected');
        } else {
            triggerText.textContent = '-- Tanpa ODP --';
            const defaultOption = optionsList.querySelector('.custom-select-option[data-value="NULL"]');
            if (defaultOption) defaultOption.classList.add('selected');
        }
    }

    let secretsLoaded = false;
    function loadMikrotikSecretsAsync() {
        const container = document.getElementById('custom_secret_select');
        if (!container) return; // Feature not active
        
        const optionsList = document.getElementById('custom_secret_options');
        const loadingStatus = document.getElementById('secrets_loading_status');
        
        if (secretsLoaded) return;

        if (loadingStatus) {
            loadingStatus.style.display = 'block';
            loadingStatus.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="margin-right:6px;"></i> Menghubungkan ke Mikrotik...';
        }

        // Clear previous options except the first one (placeholder)
        const options = optionsList.querySelectorAll('.custom-select-option');
        options.forEach((opt, idx) => {
            if (idx > 0) opt.remove();
        });

        const deviceId = "{{ $selected_device_id }}";
        const url = "{{ route('admin.pelanggan.mikrotik_secrets') }}?device_id=" + deviceId;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (loadingStatus) loadingStatus.style.display = 'none';
                    
                    data.secrets.forEach(secret => {
                        const opt = document.createElement('div');
                        opt.className = 'custom-select-option';
                        opt.setAttribute('data-value', secret.name);
                        opt.setAttribute('data-text', secret.name + ' (Profile: ' + secret.profile + ')');
                        opt.setAttribute('data-pass', secret.password || '');
                        opt.innerHTML = '<strong>' + secret.name + '</strong> <span style="font-size:0.78rem; color: var(--text-gray);">(Profile: ' + secret.profile + ')</span>';
                        optionsList.appendChild(opt);
                    });
                    
                    secretsLoaded = true;
                } else {
                    if (loadingStatus) {
                        loadingStatus.innerHTML = '<i class="fa-solid fa-circle-xmark" style="color:#dc2626; margin-right:6px;"></i> ' + data.message;
                    }
                }
            })
            .catch(error => {
                if (loadingStatus) {
                    loadingStatus.innerHTML = '<i class="fa-solid fa-circle-xmark" style="color:#dc2626; margin-right:6px;"></i> Gagal memuat data dari router.';
                }
                console.error('Error fetching Mikrotik secrets:', error);
            });
    }

    // Custom searchable select for Secrets (using delegation)
    function setupCustomSecretSelect() {
        const container = document.getElementById('custom_secret_select');
        const hiddenInput = document.getElementById('import_mikrotik');
        const triggerText = document.getElementById('custom_secret_text');
        const optionsList = document.getElementById('custom_secret_options');
        const searchInput = document.getElementById('search_secret');

        if (!container || !hiddenInput || !triggerText || !optionsList || !searchInput) return;

        // Use event delegation for option clicks
        optionsList.onclick = function(e) {
            const opt = e.target.closest('.custom-select-option');
            if (!opt) return;

            const val = opt.getAttribute('data-value');
            const text = opt.getAttribute('data-text');
            const pass = opt.getAttribute('data-pass');

            // Set values
            hiddenInput.value = val;
            triggerText.textContent = text;

            // Update styling
            const optionElements = optionsList.querySelectorAll('.custom-select-option');
            optionElements.forEach(el => el.classList.remove('selected'));
            opt.classList.add('selected');

            // Auto-fill form fields
            if (val) {
                document.getElementById('username').value = val;
                document.getElementById('password').value = pass;
                document.getElementById('nama').value = val;
            } else {
                document.getElementById('username').value = '';
                document.getElementById('password').value = '';
                document.getElementById('nama').value = '';
            }

            // Hide dropdown
            container.classList.remove('active');
        };

        // Filter options on search
        searchInput.oninput = function() {
            const query = this.value.toLowerCase().trim();
            const optionElements = optionsList.querySelectorAll('.custom-select-option');

            optionElements.forEach((opt, index) => {
                if (index === 0) {
                    opt.style.display = 'block'; // Always show placeholder
                    return;
                }
                const text = opt.getAttribute('data-text').toLowerCase();
                if (text.includes(query)) {
                    opt.style.display = 'block';
                } else {
                    opt.style.display = 'none';
                }
            });
        };

        // Prevent closing menu when typing in search input
        searchInput.onclick = function(e) {
            e.stopPropagation();
        };
    }
</script>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    var mapAdd = null, mapEdit = null;
    var markerAdd = null, markerEdit = null;

    // Map picker for Add Modal
    document.getElementById('btn-map-add-toggle').addEventListener('click', function() {
        var mapContainer = document.getElementById('map-add-picker');
        if (mapContainer.style.display === 'none') {
            mapContainer.style.display = 'block';
            if (!mapAdd) {
                mapAdd = L.map('map-add-picker').setView([-6.200000, 106.816666], 13);
                L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                    subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                    attribution: '&copy; Google Maps'
                }).addTo(mapAdd);

                mapAdd.on('click', function(e) {
                    var lat = e.latlng.lat.toFixed(6);
                    var lng = e.latlng.lng.toFixed(6);
                    document.getElementById('mapping').value = lat + ',' + lng;

                    if (markerAdd) {
                        mapAdd.removeLayer(markerAdd);
                    }
                    markerAdd = L.marker(e.latlng).addTo(mapAdd).bindPopup("Titik Terpilih").openPopup();
                });
            }
            setTimeout(function() {
                mapAdd.invalidateSize();
                var val = document.getElementById('mapping').value;
                if (val) {
                    var parts = val.split(',');
                    if (parts.length === 2) {
                        var lat = parseFloat(parts[0]);
                        var lng = parseFloat(parts[1]);
                        mapAdd.setView([lat, lng], 15);
                        if (markerAdd) mapAdd.removeLayer(markerAdd);
                        markerAdd = L.marker([lat, lng]).addTo(mapAdd).bindPopup("Titik Terpilih").openPopup();
                    }
                }
            }, 200);
        } else {
            mapContainer.style.display = 'none';
        }
    });

    document.getElementById('btn-gps-add').addEventListener('click', function() {
        if ('geolocation' in navigator) {
            navigator.geolocation.getCurrentPosition(function(position) {
                var lat = position.coords.latitude.toFixed(6);
                var lng = position.coords.longitude.toFixed(6);
                document.getElementById('mapping').value = lat + ',' + lng;
                alert("GPS berhasil diambil: " + lat + "," + lng);

                if (mapAdd && document.getElementById('map-add-picker').style.display === 'block') {
                    var latlng = [parseFloat(lat), parseFloat(lng)];
                    mapAdd.setView(latlng, 15);
                    if (markerAdd) mapAdd.removeLayer(markerAdd);
                    markerAdd = L.marker(latlng).addTo(mapAdd).bindPopup("Titik GPS").openPopup();
                }
            }, function(err) {
                var errMsg = err.message;
                if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
                    errMsg = "Only secure origins are allowed. Halaman ini diakses melalui HTTP biasa, sedangkan Google Chrome hanya mengizinkan akses GPS pada koneksi aman (HTTPS). Silakan akses website via HTTPS atau pilih lokasi secara manual di peta.";
                }
                alert("Gagal mengambil GPS: " + errMsg);
            }, { enableHighAccuracy: true });
        } else {
            alert("Geolocation tidak didukung browser ini.");
        }
    });

    // Map picker for Edit Modal
    document.getElementById('btn-map-edit-toggle').addEventListener('click', function() {
        var mapContainer = document.getElementById('map-edit-picker');
        if (mapContainer.style.display === 'none') {
            mapContainer.style.display = 'block';
            if (!mapEdit) {
                mapEdit = L.map('map-edit-picker').setView([-6.200000, 106.816666], 13);
                L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                    subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                    attribution: '&copy; Google Maps'
                }).addTo(mapEdit);

                mapEdit.on('click', function(e) {
                    var lat = e.latlng.lat.toFixed(6);
                    var lng = e.latlng.lng.toFixed(6);
                    document.getElementById('edit_mapping').value = lat + ',' + lng;

                    if (markerEdit) {
                        mapEdit.removeLayer(markerEdit);
                    }
                    markerEdit = L.marker(e.latlng).addTo(mapEdit).bindPopup("Titik Terpilih").openPopup();
                });
            }
            setTimeout(function() {
                mapEdit.invalidateSize();
                var val = document.getElementById('edit_mapping').value;
                if (val) {
                    var parts = val.split(',');
                    if (parts.length === 2) {
                        var lat = parseFloat(parts[0]);
                        var lng = parseFloat(parts[1]);
                        mapEdit.setView([lat, lng], 15);
                        if (markerEdit) mapEdit.removeLayer(markerEdit);
                        markerEdit = L.marker([lat, lng]).addTo(mapEdit).bindPopup("Titik Terpilih").openPopup();
                    }
                }
            }, 200);
        } else {
            mapContainer.style.display = 'none';
        }
    });

    document.getElementById('btn-gps-edit').addEventListener('click', function() {
        if ('geolocation' in navigator) {
            navigator.geolocation.getCurrentPosition(function(position) {
                var lat = position.coords.latitude.toFixed(6);
                var lng = position.coords.longitude.toFixed(6);
                document.getElementById('edit_mapping').value = lat + ',' + lng;
                alert("GPS berhasil diambil: " + lat + "," + lng);

                if (mapEdit && document.getElementById('map-edit-picker').style.display === 'block') {
                    var latlng = [parseFloat(lat), parseFloat(lng)];
                    mapEdit.setView(latlng, 15);
                    if (markerEdit) mapEdit.removeLayer(markerEdit);
                    markerEdit = L.marker(latlng).addTo(mapEdit).bindPopup("Titik GPS").openPopup();
                }
            }, function(err) {
                var errMsg = err.message;
                if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
                    errMsg = "Only secure origins are allowed. Halaman ini diakses melalui HTTP biasa, sedangkan Google Chrome hanya mengizinkan akses GPS pada koneksi aman (HTTPS). Silakan akses website via HTTPS atau pilih lokasi secara manual di peta.";
                }
                alert("Gagal mengambil GPS: " + errMsg);
            }, { enableHighAccuracy: true });
        } else {
            alert("Geolocation tidak didukung browser ini.");
        }
    });

    // ODP Map Recommendation Logic
    let odpMapInstance = null;
    let odpMarkers = [];
    let clientMarker = null;
    let currentModalType = 'add'; // 'add' or 'edit'
    const odpsList = @json($odps);

    window.openOdpRecommendationModal = function(type) {
        currentModalType = type;
        const coordInputId = type === 'add' ? 'mapping' : 'edit_mapping';
        const coordsValue = document.getElementById(coordInputId).value.trim();
        
        if (!coordsValue) {
            alert('Wajib mengisikan koordinat lokasi dahulu sebelum mencari rekomendasi ODP.');
            return;
        }

        const parts = coordsValue.split(',');
        if (parts.length !== 2 || isNaN(parseFloat(parts[0])) || isNaN(parseFloat(parts[1]))) {
            alert('Format koordinat tidak valid. Gunakan format: Latitude, Longitude (misal: -6.200000,106.816666).');
            return;
        }

        const clientLat = parseFloat(parts[0].trim());
        const clientLng = parseFloat(parts[1].trim());

        openOdpMapModal(clientLat, clientLng);
    };

    function openOdpMapModal(clientLat, clientLng) {
        document.getElementById('odpMapModal').classList.add('active');
        
        setTimeout(function() {
            if (odpMapInstance === null) {
                odpMapInstance = L.map('odpMap').setView([clientLat, clientLng], 15);
                L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                    maxZoom: 20,
                    subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
                }).addTo(odpMapInstance);
            } else {
                odpMapInstance.setView([clientLat, clientLng], 15);
                // Clear existing markers
                odpMarkers.forEach(marker => odpMapInstance.removeLayer(marker));
                odpMarkers = [];
                if (clientMarker) {
                    odpMapInstance.removeLayer(clientMarker);
                }
            }

            // Client/Installation Icon
            const clientIcon = L.divIcon({
                className: 'custom-div-icon',
                html: "<i class='fa-solid fa-house-chimney-user' style='color:#4f46e5; font-size:2rem; filter: drop-shadow(0px 2px 3px rgba(0,0,0,0.4));'></i>",
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
            clientMarker.bindPopup("<strong>Lokasi Pemasangan Pelanggan</strong><br>Koordinat: " + clientLat + ", " + clientLng).openPopup();
            
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
    
    window.closeOdpMapModal = function() {
        document.getElementById('odpMapModal').classList.remove('active');
    };
    
    window.selectOdpFromMap = function(id, name, remaining, total) {
        const hiddenSelectId = currentModalType === 'add' ? 'odp' : 'edit_odp';
        const hiddenSelect = document.getElementById(hiddenSelectId);
        
        if (hiddenSelect) {
            hiddenSelect.value = id;
            hiddenSelect.dispatchEvent(new Event('change'));
            syncCustomOdpText(currentModalType);
        }
        
        closeOdpMapModal();
    };

    document.getElementById('addPelangganForm').addEventListener('submit', async function(e) {
        // Prevent default submission
        e.preventDefault();
        
        // Show loading spinner (from the global layout loading system)
        if (typeof showLoading === 'function') {
            showLoading();
        }
        
        // Get phone number
        const noTelp = document.getElementById('no_telp').value.trim();
        
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
                const confirmSave = confirm(`Peringatan: Nomor HP/WhatsApp (${noTelp}) ini sudah digunakan oleh pelanggan "${data.nama_pelanggan}" (${data.kode_pelanggan}).\n\nApakah Anda yakin ingin tetap menyimpan dengan nomor HP yang sama?`);
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
