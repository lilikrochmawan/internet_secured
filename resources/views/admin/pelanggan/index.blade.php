@extends('layouts.admin')

@section('title', 'Data Pelanggan')

@section('styles')
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
            <form action="{{ route('admin.pelanggan.store') }}" method="POST">
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
                    <label for="odp">Mulai Pemasangan Dari ODP</label>
                    <select id="odp" name="odp" class="form-control">
                        <option value="NULL">-- Tanpa ODP --</option>
                        @foreach($odps as $odp)
                            <option value="{{ $odp->id_odp }}">{{ $odp->nama_odp }} (Port: {{ $odp->port_odp }})</option>
                        @endforeach
                    </select>
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
                    <label for="edit_odp">Mulai Pemasangan Dari ODP</label>
                    <select id="edit_odp" name="odp" class="form-control">
                        <option value="NULL">-- Tanpa ODP --</option>
                        @foreach($odps as $odp)
                            <option value="{{ $odp->id_odp }}">{{ $odp->nama_odp }}</option>
                        @endforeach
                    </select>
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
                <p style="font-size:0.95rem; margin-bottom:20px; line-height:1.5; color:#334155;">
                    Apakah Anda yakin ingin menghapus pelanggan <strong id="delete_name"></strong>?<br>
                    Tindakan ini juga akan menghapus akun login pelanggan dan secret PPPOE di router Mikrotik jika sinkronisasi aktif.
                </p>
                <div style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Batal</button>
                    <button type="submit" class="btn btn-danger" style="background-color:#dc2626; color:white;">Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        setupTablePagination("#pelangganTable", "#pelangganPagination", "#tableLimit", "#tableSearch");
        setupCustomSecretSelect();
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
        document.getElementById('add_branch').value = '';
        document.getElementById('add_sub_branch').value = '';

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
        document.getElementById('edit_id_mikrotik').value = pelanggan.id_mikrotik || 1;
        document.getElementById('edit_mapping').value = pelanggan.location || '';
        document.getElementById('edit_jatuh_tempo').value = pelanggan.jatuh_tempo ? pelanggan.jatuh_tempo.substring(0, 10) : '';
        
        // Set branch & sub-branch values
        document.getElementById('edit_branch').value = pelanggan.id_branch || '';
        filterSubBranches(pelanggan.id_branch || '', 'edit_sub_branch');
        document.getElementById('edit_sub_branch').value = pelanggan.id_sub_branch || '';
        
        document.getElementById('map-edit-picker').style.display = 'none';
        document.getElementById('editModal').classList.add('active');
    }
    function closeEditModal() {
        document.getElementById('editModal').classList.remove('active');
    }

    // Dynamic filtering for sub-branches based on branch selection
    function filterSubBranches(branchId, targetSelectId) {
        const select = document.getElementById(targetSelectId);
        if (!select) return;
        
        const label = select.parentElement.querySelector('label');
        
        let visibleCount = 0;
        const options = select.querySelectorAll('option');
        options.forEach(opt => {
            const optBranchId = opt.getAttribute('data-branch');
            if (!optBranchId) {
                opt.style.display = 'block'; // Keep placeholder options
            } else if (branchId === '' || optBranchId == branchId) {
                opt.style.display = 'block';
                visibleCount++;
            } else {
                opt.style.display = 'none';
            }
        });
        
        // Reset selected value if the selected option is now hidden
        const activeOption = select.querySelector('option[value="' + select.value + '"]');
        if (activeOption && activeOption.style.display === 'none') {
            select.value = '';
        }

        // If there are no sub-branches for this branch, make it optional
        if (visibleCount === 0 && branchId !== '') {
            select.required = false;
            if (label) {
                label.innerHTML = 'Sub-Branch <span style="font-size: 0.8rem; color: #94a3b8; font-weight: normal;">(Opsional/Tidak Ada)</span>';
            }
            const placeholderOpt = select.querySelector('option[value=""]');
            if (placeholderOpt) {
                placeholderOpt.textContent = '-- Tidak Ada Sub-Branch --';
            }
        } else {
            select.required = true;
            if (label) {
                label.innerHTML = 'Sub-Branch <span style="color: #ef4444;">*</span>';
            }
            const placeholderOpt = select.querySelector('option[value=""]');
            if (placeholderOpt) {
                placeholderOpt.textContent = '-- Pilih Sub-Branch --';
            }
        }
    }

    function openDeleteModal(id, name) {
        document.getElementById('delete_id').value = id;
        document.getElementById('delete_name').innerText = name;
        document.getElementById('deleteModal').classList.add('active');
    }
    function closeDeleteModal() {
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
    });

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
</script>
@endsection
