@extends('layouts.admin')

@section('title', 'Kelola ODC (Optical Distribution Cabinet)')

@section('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
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

    .odc-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(0, 0.8fr);
        gap: 24px;
        margin-top: 20px;
    }

    @media (max-width: 992px) {
        .odc-grid {
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

    /* Map Popup Style */
    .map-link {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        color: #4f46e5;
        text-decoration: none;
        font-weight: 600;
        margin-top: 6px;
    }
    .map-link:hover {
        text-decoration: underline;
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
    }

    .modal.active {
        display: flex;
    }

    .modal-content {
        background-color: white;
        border-radius: 24px;
        width: min(480px, 100%);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        border: 1px solid var(--border-color);
        animation: modalFadeIn 0.3s ease;
        overflow: hidden;
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
</style>
@endsection

@section('content')
<div class="odc-grid">
    <!-- Kolom Kiri: Tabel ODC -->
    <div class="card" style="margin: 0;">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-circle-nodes"></i>
                <span>Daftar ODC (Optical Distribution Cabinet)</span>
            </div>
            <button class="btn btn-primary" onclick="openAddModal()">
                <i class="fa-solid fa-plus"></i> Tambah ODC
            </button>
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
                    <input type="text" id="tableSearch" class="form-control" placeholder="Cari ODC..." style="padding-left:36px; height:40px; border-radius:10px; width:100%;">
                </div>
            </div>
        </div>

        <div class="table-container">
            <table class="table" id="odcTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama ODC</th>
                        <th>Perangkat</th>
                        <th>Port</th>
                        <th>Redaman</th>
                        <th>Kabel (Tube/Core)</th>
                        <th>Jumlah ODP</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($odc as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $row->nama_odc }}</strong><br>
                                @if($row->jenis_odc == 'utama')
                                    <span class="badge" style="background-color: #fee2e2; color: #ef4444; padding: 2px 6px; border-radius: 4px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase;">Utama</span>
                                @else
                                    <span class="badge" style="background-color: #e0f2fe; color: #0284c7; padding: 2px 6px; border-radius: 4px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase;">Distribusi</span>
                                    @if($row->parentOdc)
                                        <span style="font-size:0.75rem; color:var(--text-gray); display:block; margin-top:2px;">
                                            <i class="fa-solid fa-turn-up fa-rotate-90" style="margin-right:2px; font-size:0.7rem;"></i> Induk: {{ $row->parentOdc->nama_odc }}
                                        </span>
                                    @endif
                                @endif
                            </td>
                            <td>{{ $row->perangkat_odc }}</td>
                            <td><span style="font-family: monospace; font-weight:600; color:#7c3aed;">{{ $row->port_odc }} Port</span></td>
                            <td><strong style="color: #ea580c;">{{ $row->redaman ?? '-' }}</strong></td>
                            <td>
                                @if($row->tube || $row->core_number)
                                    <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                                        @if($row->tube)
                                            @if($row->tube == 'Orange')
                                                <span class="badge" style="background-color: #ffedd5; color: #ea580c; border: 1px solid #fed7aa; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 0.8rem;">Orange</span>
                                            @elseif($row->tube == 'Biru')
                                                <span class="badge" style="background-color: #dbeafe; color: #2563eb; border: 1px solid #bfdbfe; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 0.8rem;">Biru</span>
                                            @elseif($row->tube == 'Hijau')
                                                <span class="badge" style="background-color: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 0.8rem;">Hijau</span>
                                            @elseif($row->tube == 'Coklat')
                                                <span class="badge" style="background-color: #f5e0c3; color: #854d0e; border: 1px solid #ebd3b0; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 0.8rem;">Coklat</span>
                                            @else
                                                <span class="badge" style="background-color: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 0.8rem;">{{ $row->tube }}</span>
                                            @endif
                                        @endif
                                        @if($row->core_number)
                                            <span style="font-family: monospace; font-weight: 600; color: #475569; background-color: #f1f5f9; padding: 4px 8px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 0.8rem;">Core {{ $row->core_number }}</span>
                                        @endif
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($row->odps_count > 0)
                                    <button class="btn" style="background-color:#eff6ff; color:#2563eb; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 0.8rem; cursor:pointer; gap:0; border:none; display:inline-block;" 
                                        onclick='showOdpsModal({!! json_encode($row->odps) !!}, "{{ $row->nama_odc }}")'>
                                        {{ $row->odps_count }} ODP
                                    </button>
                                @else
                                    <span style="color: var(--text-gray); font-size: 0.85rem; padding: 4px 8px;">0 ODP</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <button class="btn btn-info" style="padding: 6px 12px; font-size: 0.8rem;" 
                                        onclick='openEditModal({!! json_encode($row) !!})'>
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </button>
                                    <button class="btn btn-danger" style="padding: 6px 12px; font-size: 0.8rem;" 
                                        onclick="openDeleteModal('{{ $row->id_odc }}', '{{ $row->nama_odc }}')">
                                        <i class="fa-solid fa-trash-can"></i> Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--text-gray); padding: 30px;">
                                Belum ada data ODC di database.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div id="odcPagination"></div>
    </div>

    <!-- Kolom Kanan: Peta ODC -->
    <div class="card" style="margin: 0; display: flex; flex-direction: column;">
        <div class="card-header" style="justify-content: space-between;">
            <div class="card-title">
                <i class="fa-solid fa-map-location-dot"></i>
                <span>Titik Sebaran ODC</span>
            </div>
            <button class="btn btn-info" id="btn-lokasi-saya" style="padding: 6px 12px; font-size: 0.8rem;">
                <i class="fa-solid fa-location-crosshairs"></i> Lokasi Saya
            </button>
        </div>
        <div class="card-body" style="padding: 16px; flex-grow: 1; display: flex; flex-direction: column; gap: 12px;">
            <div id="titikodc" style="height: 520px; border-radius: 16px; border: 1px solid var(--border-color); z-index: 1;"></div>
            <div style="font-size: 0.8rem; color: var(--text-gray); display: flex; align-items: flex-start; gap: 6px; line-height: 1.4;">
                <i class="fa-solid fa-info-circle" style="color: #4f46e5; margin-top: 2px;"></i>
                <span>Klik peta saat Modal Tambah/Edit aktif untuk mengambil koordinat lokasi secara presisi.</span>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah ODC -->
<div class="modal" id="addModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Tambah ODC Baru</h3>
            <button class="modal-close" onclick="closeAddModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('admin.odc.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="nama_odc">Nama ODC *</label>
                    <input type="text" id="nama_odc" name="nama_odc" class="form-control" required placeholder="Contoh: ODC-SPL-01">
                </div>

                <div class="form-group">
                    <label for="jenis_odc">Jenis ODC *</label>
                    <select id="jenis_odc" name="jenis_odc" class="form-control" required onchange="toggleParentOdcSelector(this.value, 'parent_odc_group')">
                        <option value="utama" selected>ODC Utama (Main)</option>
                        <option value="distribusi">ODC Distribusi</option>
                    </select>
                </div>

                <div class="form-group" id="parent_odc_group" style="display: none;">
                    <label for="parent_id">Menginduk ke ODC Utama *</label>
                    <select id="parent_id" name="parent_id" class="form-control">
                        <option value="" selected disabled>-- Pilih ODC Utama --</option>
                        @foreach($mainOdcs as $mo)
                            <option value="{{ $mo->id_odc }}">{{ $mo->nama_odc }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="perangkat_odc">Nama Perangkat / Brand *</label>
                    <input type="text" id="perangkat_odc" name="perangkat_odc" class="form-control" required placeholder="Contoh: Huawei GPON">
                </div>

                <div class="form-group">
                    <label for="port_odc">Jumlah Port ODC *</label>
                    <input type="number" id="port_odc" name="port_odc" class="form-control" required placeholder="Contoh: 12">
                </div>

                <div class="form-group">
                    <label for="redaman">Redaman ODC (dB/dBm)</label>
                    <input type="text" id="redaman" name="redaman" class="form-control" placeholder="Contoh: -18 dB">
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label for="tube">Tube</label>
                        <select id="tube" name="tube" class="form-control">
                            <option value="">-- Pilih Tube --</option>
                            <option value="Orange">Orange</option>
                            <option value="Biru">Biru</option>
                            <option value="Hijau">Hijau</option>
                            <option value="Coklat">Coklat</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="core_number">Core Number</label>
                        <input type="number" id="core_number" name="core_number" class="form-control" placeholder="Contoh: 1">
                    </div>
                </div>

                <div class="form-group">
                    <label for="location">Koordinat Lokasi (Lat, Lng) *</label>
                    <div style="display: flex; gap: 8px; margin-bottom: 8px;">
                        <input type="text" id="location" name="location" class="form-control" required placeholder="Contoh: -6.200000,106.816666">
                        <button type="button" class="btn btn-info" id="btn-gps-add" style="padding: 10px; border-radius: 12px;" title="Ambil GPS HP"><i class="fa-solid fa-location-crosshairs"></i></button>
                        <button type="button" class="btn btn-primary" id="btn-map-add-toggle" style="padding: 10px; border-radius: 12px; background: var(--primary-gradient);" title="Pilih dari Peta"><i class="fa-solid fa-map-location-dot"></i></button>
                    </div>
                    <div id="map-add-picker" style="height: 200px; border-radius: 12px; border: 1px solid var(--border-color); display: none; z-index: 1; margin-bottom: 8px;"></div>
                    <small style="color:var(--text-gray);">Isi manual, klik tombol GPS, atau klik tombol peta untuk memilih titik.</small>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                    <button type="button" class="btn btn-secondary" onclick="closeAddModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan ODC</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit ODC -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Ubah ODC</h3>
            <button class="modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('admin.odc.update') }}" method="POST">
                @csrf
                <input type="hidden" name="id_odc" id="edit_id">

                <div class="form-group">
                    <label for="edit_nama_odc">Nama ODC *</label>
                    <input type="text" id="edit_nama_odc" name="nama_odc" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="edit_jenis_odc">Jenis ODC *</label>
                    <select id="edit_jenis_odc" name="jenis_odc" class="form-control" required onchange="toggleParentOdcSelector(this.value, 'edit_parent_odc_group')">
                        <option value="utama">ODC Utama (Main)</option>
                        <option value="distribusi">ODC Distribusi</option>
                    </select>
                </div>

                <div class="form-group" id="edit_parent_odc_group" style="display: none;">
                    <label for="edit_parent_id">Menginduk ke ODC Utama *</label>
                    <select id="edit_parent_id" name="parent_id" class="form-control">
                        <option value="" selected disabled>-- Pilih ODC Utama --</option>
                        @foreach($mainOdcs as $mo)
                            <option value="{{ $mo->id_odc }}">{{ $mo->nama_odc }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="edit_perangkat_odc">Nama Perangkat / Brand *</label>
                    <input type="text" id="edit_perangkat_odc" name="perangkat_odc" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="edit_port_odc">Jumlah Port ODC *</label>
                    <input type="number" id="edit_port_odc" name="port_odc" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="edit_redaman">Redaman ODC (dB/dBm)</label>
                    <input type="text" id="edit_redaman" name="redaman" class="form-control" placeholder="Contoh: -18 dB">
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label for="edit_tube">Tube</label>
                        <select id="edit_tube" name="tube" class="form-control">
                            <option value="">-- Pilih Tube --</option>
                            <option value="Orange">Orange</option>
                            <option value="Biru">Biru</option>
                            <option value="Hijau">Hijau</option>
                            <option value="Coklat">Coklat</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_core_number">Core Number</label>
                        <input type="number" id="edit_core_number" name="core_number" class="form-control" placeholder="Contoh: 1">
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_location">Koordinat Lokasi (Lat, Lng) *</label>
                    <div style="display: flex; gap: 8px; margin-bottom: 8px;">
                        <input type="text" id="edit_location" name="location" class="form-control" required>
                        <button type="button" class="btn btn-info" id="btn-gps-edit" style="padding: 10px; border-radius: 12px;" title="Ambil GPS HP"><i class="fa-solid fa-location-crosshairs"></i></button>
                        <button type="button" class="btn btn-primary" id="btn-map-edit-toggle" style="padding: 10px; border-radius: 12px; background: var(--primary-gradient);" title="Pilih dari Peta"><i class="fa-solid fa-map-location-dot"></i></button>
                    </div>
                    <div id="map-edit-picker" style="height: 200px; border-radius: 12px; border: 1px solid var(--border-color); display: none; z-index: 1; margin-bottom: 8px;"></div>
                    <small style="color:var(--text-gray);">Ubah manual, klik tombol GPS, atau klik tombol peta untuk memilih titik.</small>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Hapus ODC -->
<div class="modal" id="deleteModal">
    <div class="modal-content" style="width: min(400px, 100%);">
        <div class="modal-header" style="background:#dc2626;">
            <h3>Hapus ODC</h3>
            <button class="modal-close" onclick="closeDeleteModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('admin.odc.destroy') }}" method="POST">
                @csrf
                <input type="hidden" name="id_odc" id="delete_id">
                <p style="font-size:0.95rem; margin-bottom:20px; line-height:1.5; color:#334155;">
                    Apakah Anda yakin ingin menghapus ODC <strong id="delete_name"></strong>?<br>
                    Tindakan ini tidak dapat dibatalkan dan ODC tidak boleh memiliki ODP yang terhubung.
                </p>
                <div style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Batal</button>
                    <button type="submit" class="btn btn-danger" style="background-color:#dc2626; color:white;">Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detail ODP ODC -->
<div class="modal" id="odpsModal">
    <div class="modal-content" style="width: min(540px, 100%);">
        <div class="modal-header">
            <h3>Daftar ODP Terhubung - <span id="odps_odc_name"></span></h3>
            <button class="modal-close" onclick="closeOdpsModal()">&times;</button>
        </div>
        <div class="modal-body" style="padding: 20px;">
            <div class="table-container" style="margin-top: 0; max-height: 350px; overflow-y: auto;">
                <table class="table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="padding: 10px 12px; font-size:0.85rem;">No</th>
                            <th style="padding: 10px 12px; font-size:0.85rem;">Nama ODP</th>
                            <th style="padding: 10px 12px; font-size:0.85rem;">Kapasitas Port</th>
                            <th style="padding: 10px 12px; font-size:0.85rem;">Jumlah Pelanggan</th>
                        </tr>
                    </thead>
                    <tbody id="odps_table_body">
                        <!-- Filled by JS -->
                    </tbody>
                </table>
            </div>
            <div style="display:flex; justify-content:flex-end; margin-top:20px;">
                <button type="button" class="btn btn-secondary" onclick="closeOdpsModal()">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    var mapAddPicker = null, mapEditPicker = null;
    var markerAddPicker = null, markerEditPicker = null;

    document.addEventListener("DOMContentLoaded", function () {
        setupTablePagination("#odcTable", "#odcPagination", "#tableLimit", "#tableSearch");
        initMap();

        // Add Modal Picker
        document.getElementById('btn-map-add-toggle').addEventListener('click', function() {
            var mapContainer = document.getElementById('map-add-picker');
            if (mapContainer.style.display === 'none') {
                mapContainer.style.display = 'block';
                if (!mapAddPicker) {
                    mapAddPicker = L.map('map-add-picker').setView([-6.200000, 106.816666], 13);
                    L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                        attribution: '&copy; Google Maps'
                    }).addTo(mapAddPicker);

                    mapAddPicker.on('click', function(e) {
                        var lat = e.latlng.lat.toFixed(6);
                        var lng = e.latlng.lng.toFixed(6);
                        document.getElementById('location').value = lat + ',' + lng;

                        if (markerAddPicker) {
                            mapAddPicker.removeLayer(markerAddPicker);
                        }
                        markerAddPicker = L.marker(e.latlng).addTo(mapAddPicker).bindPopup("Titik Terpilih").openPopup();
                    });
                }
                setTimeout(function() {
                    mapAddPicker.invalidateSize();
                    var val = document.getElementById('location').value;
                    if (val) {
                        var parts = val.split(',');
                        if (parts.length === 2) {
                            var lat = parseFloat(parts[0]);
                            var lng = parseFloat(parts[1]);
                            mapAddPicker.setView([lat, lng], 15);
                            if (markerAddPicker) mapAddPicker.removeLayer(markerAddPicker);
                            markerAddPicker = L.marker([lat, lng]).addTo(mapAddPicker).bindPopup("Titik Terpilih").openPopup();
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
                    document.getElementById('location').value = lat + ',' + lng;
                    alert("GPS berhasil diambil: " + lat + "," + lng);

                    if (mapAddPicker && document.getElementById('map-add-picker').style.display === 'block') {
                        var latlng = [parseFloat(lat), parseFloat(lng)];
                        mapAddPicker.setView(latlng, 15);
                        if (markerAddPicker) mapAddPicker.removeLayer(markerAddPicker);
                        markerAddPicker = L.marker(latlng).addTo(mapAddPicker).bindPopup("Titik GPS").openPopup();
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

        // Edit Modal Picker
        document.getElementById('btn-map-edit-toggle').addEventListener('click', function() {
            var mapContainer = document.getElementById('map-edit-picker');
            if (mapContainer.style.display === 'none') {
                mapContainer.style.display = 'block';
                if (!mapEditPicker) {
                    mapEditPicker = L.map('map-edit-picker').setView([-6.200000, 106.816666], 13);
                    L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                        attribution: '&copy; Google Maps'
                    }).addTo(mapEditPicker);

                    mapEditPicker.on('click', function(e) {
                        var lat = e.latlng.lat.toFixed(6);
                        var lng = e.latlng.lng.toFixed(6);
                        document.getElementById('edit_location').value = lat + ',' + lng;

                        if (markerEditPicker) {
                            mapEditPicker.removeLayer(markerEditPicker);
                        }
                        markerEditPicker = L.marker(e.latlng).addTo(mapEditPicker).bindPopup("Titik Terpilih").openPopup();
                    });
                }
                setTimeout(function() {
                    mapEditPicker.invalidateSize();
                    var val = document.getElementById('edit_location').value;
                    if (val) {
                        var parts = val.split(',');
                        if (parts.length === 2) {
                            var lat = parseFloat(parts[0]);
                            var lng = parseFloat(parts[1]);
                            mapEditPicker.setView([lat, lng], 15);
                            if (markerEditPicker) mapEditPicker.removeLayer(markerEditPicker);
                            markerEditPicker = L.marker([lat, lng]).addTo(mapEditPicker).bindPopup("Titik Terpilih").openPopup();
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
                    document.getElementById('edit_location').value = lat + ',' + lng;
                    alert("GPS berhasil diambil: " + lat + "," + lng);

                    if (mapEditPicker && document.getElementById('map-edit-picker').style.display === 'block') {
                        var latlng = [parseFloat(lat), parseFloat(lng)];
                        mapEditPicker.setView(latlng, 15);
                        if (markerEditPicker) mapEditPicker.removeLayer(markerEditPicker);
                        markerEditPicker = L.marker(latlng).addTo(mapEditPicker).bindPopup("Titik GPS").openPopup();
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
    });

    function openAddModal() {
        document.getElementById('addModal').classList.add('active');
        document.getElementById('map-add-picker').style.display = 'none';
        // Reset click marker
        if (clickMarker) {
            map.removeLayer(clickMarker);
            clickMarker = null;
        }
    }
    function closeAddModal() {
        document.getElementById('addModal').classList.remove('active');
    }

    function toggleParentOdcSelector(jenis, groupId) {
        const group = document.getElementById(groupId);
        const parentSelect = group.querySelector('select');
        if (jenis === 'distribusi') {
            group.style.display = 'block';
            parentSelect.required = true;
        } else {
            group.style.display = 'none';
            parentSelect.required = false;
            parentSelect.value = '';
        }
    }

    function openEditModal(odc) {
        document.getElementById('edit_id').value = odc.id_odc;
        document.getElementById('edit_nama_odc').value = odc.nama_odc;
        document.getElementById('edit_perangkat_odc').value = odc.perangkat_odc;
        document.getElementById('edit_port_odc').value = odc.port_odc;
        document.getElementById('edit_location').value = odc.location;
        document.getElementById('edit_redaman').value = odc.redaman || '';
        document.getElementById('edit_tube').value = odc.tube || '';
        document.getElementById('edit_core_number').value = odc.core_number || '';
        
        document.getElementById('edit_jenis_odc').value = odc.jenis_odc || 'utama';
        document.getElementById('edit_parent_id').value = odc.parent_id || '';
        toggleParentOdcSelector(odc.jenis_odc || 'utama', 'edit_parent_odc_group');

        document.getElementById('editModal').classList.add('active');
        document.getElementById('map-edit-picker').style.display = 'none';
        
        // Show current edit marker as a preview
        if (odc.location) {
            var parts = odc.location.split(',');
            if (parts.length === 2) {
                var lat = parseFloat(parts[0]);
                var lng = parseFloat(parts[1]);
                map.setView([lat, lng], 15);
                
                if (clickMarker) {
                    map.removeLayer(clickMarker);
                }
                clickMarker = L.marker([lat, lng], {
                    icon: L.icon({
                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-gold.png',
                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    })
                }).addTo(map).bindPopup("Titik Terpilih: " + odc.location).openPopup();
            }
        }
    }
    function closeEditModal() {
        document.getElementById('editModal').classList.remove('active');
    }

    function openDeleteModal(id, name) {
        document.getElementById('delete_id').value = id;
        document.getElementById('delete_name').innerText = name;
        document.getElementById('deleteModal').classList.add('active');
    }
    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.remove('active');
    }

    // Leaflet Map Handlers
    var map;
    var markerGroup;
    var clickMarker = null;

    function initMap() {
        // Center of Indonesia as a starting default
        map = L.map('titikodc').setView([-2.548926, 118.014863], 5);

        // Layers
        var googleStreets = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
            attribution: '&copy; Google Maps'
        });

        var googleSatellite = L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
            attribution: '&copy; Google Maps'
        });

        var osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        });

        googleStreets.addTo(map);

        var baseLayers = {
            "Google Maps (Jalan)": googleStreets,
            "Google Maps (Satelit)": googleSatellite,
            "OpenStreetMap": osm
        };

        L.control.layers(baseLayers).addTo(map);
        markerGroup = L.layerGroup().addTo(map);

        // Click map handler
        map.on('click', function(e) {
            var addModal = document.getElementById('addModal');
            var editModal = document.getElementById('editModal');
            
            // Map only records coordinates if one of the modals is open
            if (addModal.classList.contains('active') || editModal.classList.contains('active')) {
                var lat = e.latlng.lat.toFixed(6);
                var lng = e.latlng.lng.toFixed(6);
                var coords = lat + ',' + lng;

                if (addModal.classList.contains('active')) {
                    document.getElementById('location').value = coords;
                } else {
                    document.getElementById('edit_location').value = coords;
                }

                if (clickMarker) {
                    map.removeLayer(clickMarker);
                }

                clickMarker = L.marker(e.latlng, {
                    icon: L.icon({
                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-gold.png',
                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    })
                }).addTo(map).bindPopup("Titik Terpilih: " + coords).openPopup();
            }
        });

        loadOdcMarkers();
        locateUser();
    }

    function loadOdcMarkers() {
        fetch('{{ route("admin.odc.coordinates") }}')
            .then(response => response.json())
            .then(data => {
                markerGroup.clearLayers();
                var bounds = [];

                // Plot markers
                data.forEach(odc => {
                    var markerColor = odc.jenis_odc === 'utama' ? 'red' : 'blue';
                    var markerTypeLabel = odc.jenis_odc === 'utama' ? 'Utama (Main)' : 'Distribusi';
                    var marker = L.marker([odc.lat, odc.lng], {
                        icon: L.icon({
                            iconUrl: `https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-${markerColor}.png`,
                            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                            iconSize: [25, 41],
                            iconAnchor: [12, 41],
                            popupAnchor: [1, -34],
                            shadowSize: [41, 41]
                        })
                    }).bindPopup(
                        `<div style="font-family:'Inter',sans-serif; font-size:0.85rem; line-height:1.4;">` +
                        `<h4 style="font-family:'Outfit',sans-serif; margin:0 0 6px 0; color:#4f46e5; font-size:0.95rem;">${odc.nama_odc}</h4>` +
                        `<strong>Jenis ODC:</strong> <span style="font-weight:700;">${markerTypeLabel}</span><br>` +
                        `<strong>Perangkat:</strong> ${odc.perangkat_odc}<br>` +
                        `<strong>Kapasitas Port:</strong> ${odc.port_odc} Port<br>` +
                        `<strong>Redaman:</strong> <span style="color:#ea580c; font-weight:600;">${odc.redaman || '-'}</span><br>` +
                        `<strong>Tube/Core:</strong> ${odc.tube ? `<span style="font-weight:600;">${odc.tube}</span>` : '-'} / ${odc.core_number ? `Core ${odc.core_number}` : '-'}<br>` +
                        `<a href="https://www.google.com/maps?q=${odc.lat},${odc.lng}" target="_blank" class="map-link" style="margin-top:8px;">` +
                        `<i class="fa-solid fa-map-location-dot"></i> Buka di Google Maps</a>` +
                        `</div>`
                    );
                    markerGroup.addLayer(marker);
                    bounds.push([odc.lat, odc.lng]);
                });

                // Build a map of ODC ID to Coordinates
                var odcIdMap = {};
                data.forEach(odc => {
                    odcIdMap[odc.id_odc] = [odc.lat, odc.lng];
                });

                // Draw lines between ODC Utama and ODC Distribusi
                data.forEach(odc => {
                    if (odc.jenis_odc === 'distribusi' && odc.parent_id) {
                        var parentCoord = odcIdMap[odc.parent_id];
                        if (parentCoord) {
                            var polyline = L.polyline([[odc.lat, odc.lng], parentCoord], {
                                color: '#e11d48', // Rose color for ODC Utama -> Distribusi connection line
                                weight: 3,
                                opacity: 0.85,
                                dashArray: '4, 4'
                            });
                            markerGroup.addLayer(polyline);
                        }
                    }
                });

                if (bounds.length > 0) {
                    map.fitBounds(bounds, { padding: [50, 50] });
                }
            })
            .catch(error => console.error('Error fetching ODC coordinates:', error));
    }

    function locateUser() {
        document.getElementById('btn-lokasi-saya').addEventListener('click', function () {
            if ('geolocation' in navigator) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    var lat = position.coords.latitude;
                    var lng = position.coords.longitude;
                    map.setView([lat, lng], 15);

                    L.marker([lat, lng], {
                        icon: L.icon({
                            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                            iconSize: [25, 41],
                            iconAnchor: [12, 41],
                            popupAnchor: [1, -34],
                            shadowSize: [41, 41]
                        })
                    }).addTo(map).bindPopup("Lokasi Saya saat ini").openPopup();
                }, function (error) {
                    console.error("Gagal mendapatkan lokasi:", error);
                    var errMsg = error.message;
                    if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
                        errMsg = "Only secure origins are allowed. Halaman ini diakses melalui HTTP biasa, sedangkan Google Chrome hanya mengizinkan akses GPS pada koneksi aman (HTTPS). Silakan akses website via HTTPS atau pilih lokasi secara manual di peta.";
                    }
                    alert("Gagal mendapatkan lokasi: " + errMsg);
                }, { enableHighAccuracy: true });
            } else {
                alert("Geolocation tidak didukung oleh browser ini.");
            }
        });
    }

    // Modal ODP List Handlers
    function showOdpsModal(odps, odcName) {
        document.getElementById('odps_odc_name').innerText = odcName;
        const tbody = document.getElementById('odps_table_body');
        tbody.innerHTML = '';

        if (odps && odps.length > 0) {
            odps.forEach((odp, index) => {
                const tr = document.createElement('tr');
                
                const noTd = document.createElement('td');
                noTd.textContent = index + 1;
                noTd.style.padding = '10px 12px';
                noTd.style.fontSize = '0.85rem';
                tr.appendChild(noTd);

                const nameTd = document.createElement('td');
                nameTd.innerHTML = `<strong>${odp.nama_odp}</strong>`;
                nameTd.style.padding = '10px 12px';
                nameTd.style.fontSize = '0.85rem';
                tr.appendChild(nameTd);

                const portTd = document.createElement('td');
                portTd.innerHTML = `<span style="font-family: monospace; font-weight:600; color:#10b981;">${odp.port_odp} Port</span>`;
                portTd.style.padding = '10px 12px';
                portTd.style.fontSize = '0.85rem';
                tr.appendChild(portTd);

                const clientTd = document.createElement('td');
                const clientCount = odp.pelanggans ? odp.pelanggans.length : 0;
                clientTd.innerHTML = `<span class="badge" style="background-color:#f0fdf4; color:#15803d; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 0.8rem;">${clientCount} Client</span>`;
                clientTd.style.padding = '10px 12px';
                clientTd.style.fontSize = '0.85rem';
                tr.appendChild(clientTd);

                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; color:var(--text-gray); padding:20px;">Tidak ada ODP terhubung ke ODC ini.</td></tr>`;
        }

        document.getElementById('odpsModal').classList.add('active');
    }

    function closeOdpsModal() {
        document.getElementById('odpsModal').classList.remove('active');
    }
</script>
@endsection
