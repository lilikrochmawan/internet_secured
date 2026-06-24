@extends('layouts.admin')

@section('title', 'Kas Masuk & Keluar')

@section('styles')
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

    .stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 24px;
    }

    .stat-card {
        background-color: white;
        border-radius: 20px;
        border: 1px solid var(--border-color);
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: var(--shadow-sm);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        font-size: 1.25rem;
    }

    .stat-green .stat-icon { background:#f0fdf4; color:#16a34a; }
    .stat-red .stat-icon { background:#fef2f2; color:#dc2626; }
    .stat-purple .stat-icon { background:#faf5ff; color:#7c3aed; }
    .stat-blue .stat-icon { background:#eff6ff; color:#2563eb; }

    .stat-info {
        display: flex;
        flex-direction: column;
    }

    .stat-value {
        font-family: 'Outfit', sans-serif;
        font-size: 1.25rem;
        font-weight: 700;
    }

    .stat-label {
        font-size: 0.82rem;
        color: var(--text-gray);
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
        width: min(460px, 100%);
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

    @media (max-width: 1024px) {
        .stats-row {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .stats-row {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<!-- Ringkasan Statistik Kas -->
<div class="stats-row">
    <div class="stat-card stat-green">
        <div class="stat-icon"><i class="fa-solid fa-arrow-trend-up"></i></div>
        <div class="stat-info">
            <span class="stat-value">Rp {{ number_format($total_masuk, 0, ',', '.') }}</span>
            <span class="stat-label">Total Kas Masuk</span>
        </div>
    </div>

    <div class="stat-card stat-blue">
        <div class="stat-icon"><i class="fa-solid fa-calendar-days"></i></div>
        <div class="stat-info">
            <span class="stat-value">Rp {{ number_format($pemasukan_bulan_ini, 0, ',', '.') }}</span>
            <span class="stat-label">Pemasukan Bulan Ini</span>
        </div>
    </div>
    
    <div class="stat-card stat-red">
        <div class="stat-icon"><i class="fa-solid fa-arrow-trend-down"></i></div>
        <div class="stat-info">
            <span class="stat-value">Rp {{ number_format($total_keluar, 0, ',', '.') }}</span>
            <span class="stat-label">Total Kas Keluar</span>
        </div>
    </div>

    <div class="stat-card stat-purple">
        <div class="stat-icon"><i class="fa-solid fa-wallet"></i></div>
        <div class="stat-info">
            <span class="stat-value">Rp {{ number_format($saldo, 0, ',', '.') }}</span>
            <span class="stat-label">Saldo Saat Ini</span>
        </div>
    </div>
</div>

<!-- Card Cetak Laporan Keuangan (Kas) -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-print"></i>
            <span>Cetak Laporan Keuangan (Kas)</span>
        </div>
    </div>
    <form action="{{ route('admin.kas.print') }}" method="GET" target="_blank">
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
                    <i class="fa-solid fa-print"></i> Cetak Laporan
                </button>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-money-bill-transfer"></i>
            <span>Buku Ledger Kas Masuk & Keluar</span>
        </div>
        <button class="btn btn-primary" onclick="openAddModal()">
            <i class="fa-solid fa-plus"></i> Catat Transaksi
        </button>
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
                <input type="text" id="tableSearch" class="form-control" placeholder="Cari keterangan..." style="padding-left:36px; height:40px; border-radius:10px; width:100%;">
            </div>
        </div>
    </div>

    <div class="table-container">
        <table class="table" id="kasTable">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th>Kas Masuk (Debit)</th>
                    <th>Kas Keluar (Kredit)</th>
                    <th>Status</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kas as $index => $k)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ Carbon\Carbon::parse($k->tgl_kas)->translatedFormat('d F Y') }}</td>
                        <td><strong>{{ html_entity_decode($k->keterangan) }}</strong></td>
                        <td style="color:#16a34a; font-weight:600;">{{ $k->penerimaan > 0 ? 'Rp ' . number_format($k->penerimaan, 0, ',', '.') : '-' }}</td>
                        <td style="color:#dc2626; font-weight:600;">{{ $k->pengeluaran > 0 ? 'Rp ' . number_format($k->pengeluaran, 0, ',', '.') : '-' }}</td>
                        <td>
                            @if($k->status == 1)
                                <span class="badge badge-success" style="background-color:#eff6ff; color:#2563eb;">Bisa Diedit</span>
                            @else
                                <span class="badge badge-danger" style="background-color:#f1f5f9; color:#64748b;">Terkunci</span>
                            @endif
                        </td>
                        <td align="center">
                            @if($k->status == 1)
                                <div style="display: flex; gap: 8px; justify-content:center;">
                                    <button class="btn btn-info" style="padding: 6px 12px; font-size: 0.8rem;" 
                                        onclick='openEditModal({!! json_encode($k) !!})'>
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </button>
                                    <button class="btn btn-danger" style="padding: 6px 12px; font-size: 0.8rem;" 
                                        onclick="openDeleteModal('{{ $k->id_kas }}')">
                                        <i class="fa-solid fa-trash-can"></i> Hapus
                                    </button>
                                </div>
                            @else
                                <span style="font-size:0.85rem; color:var(--text-gray); font-style:italic;">No Actions</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-gray); padding: 30px;">
                            Belum ada riwayat transaksi kas tercatat.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div id="kasPagination"></div>
</div>

<!-- Modal Tambah Kas -->
<div class="modal" id="addModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Catat Transaksi Kas</h3>
            <button class="modal-close" onclick="closeAddModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('admin.kas.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="tgl_kas">Tanggal Transaksi *</label>
                    <input type="date" id="tgl_kas" name="tgl_kas" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="form-group">
                    <label for="keterangan">Keterangan *</label>
                    <input type="text" id="keterangan" name="keterangan" class="form-control" required placeholder="Contoh: Beli modem cadangan">
                </div>

                <div class="form-group">
                    <label for="penerimaan">Pemasukan (Debit) - Isi 0 jika tidak ada *</label>
                    <input type="number" id="penerimaan" name="penerimaan" class="form-control" value="0" required>
                </div>

                <div class="form-group">
                    <label for="pengeluaran">Pengeluaran (Kredit) - Isi 0 jika tidak ada *</label>
                    <input type="number" id="pengeluaran" name="pengeluaran" class="form-control" value="0" required>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                    <button type="button" class="btn btn-secondary" onclick="closeAddModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Kas -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Ubah Transaksi Kas</h3>
            <button class="modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('admin.kas.update') }}" method="POST">
                @csrf
                <input type="hidden" name="id_kas" id="edit_id">

                <div class="form-group">
                    <label for="edit_tgl_kas">Tanggal Transaksi *</label>
                    <input type="date" id="edit_tgl_kas" name="tgl_kas" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="edit_keterangan">Keterangan *</label>
                    <input type="text" id="edit_keterangan" name="keterangan" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="edit_penerimaan">Pemasukan (Debit) *</label>
                    <input type="number" id="edit_penerimaan" name="penerimaan" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="edit_pengeluaran">Pengeluaran (Kredit) *</label>
                    <input type="number" id="edit_pengeluaran" name="pengeluaran" class="form-control" required>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Hapus Kas -->
<div class="modal" id="deleteModal">
    <div class="modal-content" style="width: min(400px, 100%);">
        <div class="modal-header" style="background:#dc2626;">
            <h3>Hapus Transaksi Kas</h3>
            <button class="modal-close" onclick="closeDeleteModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('admin.kas.destroy') }}" method="POST">
                @csrf
                <input type="hidden" name="id_kas" id="delete_id">
                <p style="font-size:0.95rem; margin-bottom:20px; line-height:1.5; color:#334155;">
                    Apakah Anda yakin ingin menghapus catatan transaksi kas ini?<br>
                    Tindakan ini tidak dapat dibatalkan.
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
        setupTablePagination("#kasTable", "#kasPagination", "#tableLimit", "#tableSearch");
        toggleReportFilters();
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

    function openAddModal() {
        document.getElementById('addModal').classList.add('active');
    }
    function closeAddModal() {
        document.getElementById('addModal').classList.remove('active');
    }

    function openEditModal(kas) {
        document.getElementById('edit_id').value = kas.id_kas;
        document.getElementById('edit_tgl_kas').value = kas.tgl_kas;
        // Decode HTML entities if any
        let txt = document.createElement("textarea");
        txt.innerHTML = kas.keterangan;
        document.getElementById('edit_keterangan').value = txt.value;
        document.getElementById('edit_penerimaan').value = kas.penerimaan;
        document.getElementById('edit_pengeluaran').value = kas.pengeluaran;
        document.getElementById('editModal').classList.add('active');
    }
    function closeEditModal() {
        document.getElementById('editModal').classList.remove('active');
    }

    function openDeleteModal(id) {
        document.getElementById('delete_id').value = id;
        document.getElementById('deleteModal').classList.add('active');
    }
    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.remove('active');
    }
</script>
@endsection
