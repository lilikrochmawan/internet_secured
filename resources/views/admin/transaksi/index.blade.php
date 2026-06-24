@extends('layouts.admin')

@section('title', 'Transaksi Pembayaran')

@section('styles')
<style>
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

    .btn-success {
        background-color: #dcfce7;
        color: #15803d;
    }
    .btn-success:hover {
        background-color: #bbf7d0;
    }

    .btn-danger {
        background-color: #fef2f2;
        color: #dc2626;
    }
    .btn-danger:hover {
        background-color: #fee2e2;
    }

    .btn-warning {
        background-color: #fff7ed;
        color: #ea580c;
    }
    .btn-warning:hover {
        background-color: #ffedd5;
    }

    .btn-info {
        background-color: #eff6ff;
        color: #2563eb;
    }
    .btn-info:hover {
        background-color: #dbeafe;
    }

    .btn-primary {
        background: var(--primary-gradient);
        color: white;
    }
    .btn-primary:hover {
        opacity: 0.9;
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

    .table tr.row-lunas {
        background-color: #f0fdf4 !important;
    }

    .table tr.row-lunas:hover {
        background-color: #dcfce7 !important;
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

    .badge-success {
        background-color: #dcfce7;
        color: #15803d;
    }

    .badge-danger {
        background-color: #fef2f2;
        color: #dc2626;
    }

    .badge-warning {
        background-color: #fff7ed;
        color: #ea580c;
    }

    .filter-bar {
        background-color: #f8fafc;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 16px 20px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    }

    .filter-inputs {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        flex-grow: 1;
    }

    .form-control {
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 8px 14px;
        font-size: 0.9rem;
        outline: none;
        background-color: white;
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
        text-align: left;
    }

    .form-group .form-control {
        width: 100%;
    }

    @keyframes modalFadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
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

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-8px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 12px; align-items: center;">
        <div class="card-title">
            <i class="fa-solid fa-receipt"></i>
            <span>Kelola Tagihan & Pembayaran Pelanggan</span>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
            <button type="button" class="btn btn-warning" onclick="triggerBroadcast('broadcast')" style="background-color: #fff7ed; color: #ea580c; border: 1px solid #ffedd5;">
                <i class="fa-solid fa-bullhorn"></i> Broadcast Tagihan (WA)
            </button>

            <button type="button" class="btn btn-danger" onclick="triggerBroadcast('reminder')" style="background-color: #fef2f2; color: #dc2626; border: 1px solid #fee2e2;">
                <i class="fa-solid fa-bell"></i> Reminder Tagihan (WA)
            </button>

            <button type="button" class="btn btn-danger" onclick="triggerBroadcast('bulk_blokir')" style="background-color: #fef2f2; color: #b91c1c; border: 1px solid #fee2e2;">
                <i class="fa-solid fa-ban"></i> Blokir Massal Belum Bayar
            </button>

            <button type="button" class="btn btn-info" onclick="openManualInvoiceModal()">
                <i class="fa-solid fa-file-invoice-dollar"></i> Tambah Invoice Manual
            </button>

            <a href="{{ route('admin.transaksi.show_generate') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Generate Tagihan Bulanan
            </a>
        </div>
    </div>

    <!-- Filter & Pencarian Bar -->
    <div class="filter-bar">
        <form method="GET" action="{{ route('admin.transaksi.index') }}" class="filter-inputs">
            <input type="hidden" name="filter" value="1">
            <input type="text" name="search" class="form-control" placeholder="Cari nama / kode pelanggan..." value="{{ $search }}" style="min-width: 260px;">
            
            <select name="status" class="form-control">
                <option value="">-- Semua Status --</option>
                <option value="lunas" {{ $status == 'lunas' ? 'selected' : '' }}>Lunas</option>
                <option value="belum_bayar" {{ $status == 'belum_bayar' ? 'selected' : '' }}>Belum Bayar</option>
            </select>

            <select name="bulan" class="form-control">
                <option value="">-- Pilih Bulan --</option>
                @php
                    $months = [
                        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                    ];
                @endphp
                @foreach($months as $val => $name)
                    <option value="{{ $val }}" {{ ($selectedMonth ?? '') == $val ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>

            <select name="tahun" class="form-control">
                <option value="">-- Pilih Tahun --</option>
                @for ($y = 2024; $y <= 2030; $y++)
                    <option value="{{ $y }}" {{ ($selectedYear ?? '') == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>

            <button type="submit" class="btn btn-primary" style="padding: 8px 16px;">
                <i class="fa-solid fa-magnifying-glass"></i> Cari
            </button>
            
            @if($search || $status || $selectedMonth || $selectedYear)
                <a href="{{ route('admin.transaksi.index') }}" class="btn btn-secondary" style="padding: 8px 16px;">
                    Clear Filter
                </a>
            @endif
        </form>
    </div>

    <!-- Search and Row Limiter -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-top: 10px; margin-bottom:16px; flex-wrap:wrap; gap:12px;">
        <div>
            <button type="button" class="btn btn-primary" id="btnLihatTransaksi" onclick="openPembayaranModal()" style="display: inline-flex; align-items: center; gap: 8px; height: 40px; border-radius: 12px; font-weight: 700;">
                <i class="fa-solid fa-receipt"></i> Lihat Transaksi Pembayaran
            </button>
        </div>
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
                <input type="text" id="tableSearch" class="form-control" placeholder="Cari transaksi..." style="padding-left:36px; height:40px; border-radius:10px; width:100%;">
            </div>
        </div>
    </div>

    <div class="table-container">
        <table class="table" id="transaksiTable">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Pelanggan</th>
                    <th>Bulan/Tahun</th>
                    <th>Tagihan</th>
                    <th>Status Bayar</th>
                    <th>Waktu Pembayaran</th>
                    <th>Status Kirim WA</th>
                    <th>Status Client</th>
                    <th style="min-width: 280px; text-align:center;">Aksi Pengelolaan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tagihan as $index => $tx)
                    <tr class="{{ $tx->status_bayar == 1 ? 'row-lunas' : '' }}">
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $tx->pelanggan->nama_pelanggan ?? 'N/A' }}</strong><br>
                            <small style="color:var(--text-gray); font-family: monospace;">{{ $tx->pelanggan->kode_pelanggan ?? '-' }}</small>
                            @if($tx->manual_invoice == 1)
                                <br>
                                <span class="badge badge-info" style="font-size: 0.65rem; padding: 2px 6px; margin-top: 4px; display: inline-block;">Manual Invoice</span>
                                @if($tx->item_tagihan)
                                    <div style="font-size: 0.8rem; color: #475569; font-style: italic; margin-top: 2px;">
                                        Item: {{ $tx->item_tagihan }}
                                    </div>
                                @endif
                            @endif
                        </td>
                        <td>{{ substr($tx->bulan_tahun, 0, 2) . '-' . substr($tx->bulan_tahun, 2) }}</td>
                        <td><strong>Rp {{ number_format($tx->jml_bayar, 0, ',', '.') }}</strong></td>
                        <td>
                            @if($tx->status_bayar == 1)
                                <span class="badge badge-success">Lunas</span>
                            @else
                                <span class="badge badge-danger">Belum Bayar</span>
                            @endif
                        </td>
                        <td>{{ $tx->waktu_bayar ?? '-' }}</td>
                        <td>
                            @if($tx->terkirim == 'terkirim')
                                <span class="badge badge-success">Terkirim</span>
                            @else
                                <span class="badge badge-warning">Belum Dikirim</span>
                            @endif
                        </td>
                        <td>
                            @if($tx->blokir_status == 1)
                                <span class="badge badge-danger">Terblokir</span>
                            @else
                                <span class="badge badge-success">Aktif</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; gap: 6px; justify-content: center; flex-wrap: wrap;">
                                @if($tx->status_bayar != 1)
                                    <!-- Aksi Pembayaran Manual -->
                                    <form action="{{ route('admin.transaksi.bayar') }}" method="POST" onsubmit="return confirm('Catat pembayaran tagihan manual untuk pelanggan ini?')">
                                        @csrf
                                        <input type="hidden" name="id_tagihan" value="{{ $tx->id_tagihan }}">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fa-solid fa-money-bill-wave"></i> Bayar
                                        </button>
                                    </form>

                                    <!-- Aksi Cetak Invoice -->
                                    <a href="{{ route('admin.transaksi.print_invoice', $tx->id_tagihan) }}" target="_blank" class="btn btn-info" style="background-color: #eff6ff; color: #2563eb; border: 1px solid #dbeafe;">
                                        <i class="fa-solid fa-file-invoice"></i> Invoice
                                    </a>

                                    <!-- Aksi Kirim Notif WhatsApp -->
                                    <form action="{{ route('admin.transaksi.notif') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="id_tagihan" value="{{ $tx->id_tagihan }}">
                                        <button type="submit" class="btn btn-warning">
                                            <i class="fa-brands fa-whatsapp"></i> Tagih WA
                                        </button>
                                    </form>

                                    <!-- Aksi Kirim Reminder WhatsApp -->
                                    <form action="{{ route('admin.transaksi.notif_reminder') }}" method="POST" onsubmit="return confirm('Kirim pengingat tagihan (reminder) via WhatsApp ke pelanggan ini?')">
                                        @csrf
                                        <input type="hidden" name="id_tagihan" value="{{ $tx->id_tagihan }}">
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fa-solid fa-bell"></i> Remind WA
                                        </button>
                                    </form>

                                    <!-- Aksi Blokir Mikrotik -->
                                    @if($tx->blokir_status != 1)
                                        <form action="{{ route('admin.transaksi.blokir') }}" method="POST" onsubmit="return confirm('Blokir akses internet pelanggan ini di Mikrotik?')">
                                            @csrf
                                            <input type="hidden" name="id_tagihan" value="{{ $tx->id_tagihan }}">
                                            <button type="submit" class="btn btn-danger" style="background-color:#fee2e2; color:#ef4444;">
                                                <i class="fa-solid fa-ban"></i> Blokir
                                            </button>
                                        </form>
                                    @else
                                        <!-- Aksi Buka Blokir Mikrotik -->
                                        <form action="{{ route('admin.transaksi.unblokir') }}" method="POST" onsubmit="return confirm('Buka blokir akses internet pelanggan ini di Mikrotik?')">
                                            @csrf
                                            <input type="hidden" name="id_tagihan" value="{{ $tx->id_tagihan }}">
                                            <button type="submit" class="btn btn-info">
                                                <i class="fa-solid fa-unlock-keyhole"></i> Unblock
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <!-- Aksi Cetak Invoice -->
                                    <a href="{{ route('admin.transaksi.print_invoice', $tx->id_tagihan) }}" target="_blank" class="btn btn-info" style="background-color: #eff6ff; color: #2563eb; border: 1px solid #dbeafe;">
                                        <i class="fa-solid fa-file-invoice"></i> Invoice
                                    </a>

                                    <!-- Aksi Cetak Bukti Bayar -->
                                    <a href="{{ route('admin.transaksi.print_receipt', $tx->id_tagihan) }}" target="_blank" class="btn btn-success" style="background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;">
                                        <i class="fa-solid fa-receipt"></i> Bukti Bayar
                                    </a>

                                    <!-- Aksi Batalkan Pembayaran (Batal) -->
                                    <form action="{{ route('admin.transaksi.batal') }}" method="POST" onsubmit="return confirm('Batalkan catatan pembayaran untuk tagihan ini?')">
                                        @csrf
                                        <input type="hidden" name="id_tagihan" value="{{ $tx->id_tagihan }}">
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fa-solid fa-rotate-left"></i> Batal Lunas
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; color: var(--text-gray); padding: 30px;">
                            <i class="fa-solid fa-magnifying-glass" style="font-size: 1.5rem; color: #cbd5e1; display: block; margin-bottom: 8px;"></i>
                            Tidak ada data transaksi penagihan yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div id="transaksiPagination"></div>
</div>

<!-- Modal Tambah Invoice Manual -->
<div class="modal" id="manualInvoiceModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Tambah Invoice Manual</h3>
            <button class="modal-close" onclick="closeManualInvoiceModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('admin.transaksi.store_manual') }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label>Pilih Pelanggan</label>
                    <!-- Hidden input to store actual selected value -->
                    <input type="hidden" name="id_pelanggan" id="manual_id_pelanggan" required>
                    
                    <!-- Custom Searchable Dropdown -->
                    <div class="custom-select-container" id="custom_pelanggan_select">
                        <div class="custom-select-trigger" onclick="toggleCustomDropdown()">
                            <span id="custom_select_text">-- Pilih Pelanggan --</span>
                            <i class="fa-solid fa-chevron-down" style="font-size: 0.8rem; color: var(--text-gray);"></i>
                        </div>
                        <div class="custom-select-dropdown" id="custom_select_dropdown">
                            <div class="custom-select-search-wrapper">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" id="search_pelanggan" class="form-control custom-select-search-input" placeholder="Cari nama atau kode pelanggan..." autocomplete="off">
                            </div>
                            <div class="custom-select-options" id="custom_select_options">
                                <div class="custom-select-option selected" data-value="" data-text="-- Pilih Pelanggan --" data-harga="0">
                                    -- Pilih Pelanggan --
                                </div>
                                @foreach($pelanggan as $p)
                                    <div class="custom-select-option" data-value="{{ $p->id_pelanggan }}" data-text="{{ $p->nama_pelanggan }} ({{ $p->kode_pelanggan }})" data-harga="{{ $p->paketDetail->harga ?? 0 }}">
                                        <strong>{{ $p->nama_pelanggan }}</strong> <span style="font-family: monospace; font-size:0.78rem; color: var(--text-gray);">({{ $p->kode_pelanggan }})</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label for="manual_bulan">Bulan Periode</label>
                        <select name="bulan" id="manual_bulan" class="form-control" required style="height: auto; padding: 10px 14px;">
                            @foreach(range(1, 12) as $m)
                                @php $mVal = str_pad($m, 2, '0', STR_PAD_LEFT); @endphp
                                <option value="{{ $mVal }}" {{ date('m') == $mVal ? 'selected' : '' }}>
                                    {{ Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="manual_tahun">Tahun Periode</label>
                        <select name="tahun" id="manual_tahun" class="form-control" required style="height: auto; padding: 10px 14px;">
                            @foreach(range(date('Y') - 1, date('Y') + 2) as $y)
                                <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="manual_jml_bayar">Nominal Tagihan (Rp)</label>
                    <input type="number" name="jml_bayar" id="manual_jml_bayar" class="form-control" placeholder="Masukkan jumlah tagihan, misal: 150000" required>
                </div>

                <div class="form-group">
                    <label for="manual_jatuh_tempo">Tanggal Jatuh Tempo</label>
                    <input type="date" name="jatuh_tempo" id="manual_jatuh_tempo" class="form-control" value="{{ date('Y-m-10') }}" required>
                </div>

                <div class="form-group">
                    <label for="manual_item_tagihan">Item / Deskripsi Tagihan</label>
                    <textarea name="item_tagihan" id="manual_item_tagihan" class="form-control" rows="3" placeholder="Contoh: Tagihan Bulan Mei 2026 / Biaya Pemasangan Tambahan" style="resize: vertical; font-family: inherit; height: auto;"></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px;">
                    <button type="button" class="btn btn-secondary" onclick="closeManualInvoiceModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Transaksi Pembayaran 2 Bulan Terakhir -->
<div class="modal" id="pembayaranModal">
    <div class="modal-content" style="width: min(800px, 100%);">
        <div class="modal-header">
            <h3 style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-receipt" style="color: white; font-size: 1.2rem;"></i>
                <span>Transaksi Pembayaran (Bulan Ini & Bulan Kemarin)</span>
            </h3>
            <button type="button" class="modal-close" onclick="closePembayaranModal()">&times;</button>
        </div>
        <div class="modal-body" style="padding: 24px; text-align: left;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
                <div style="font-size: 0.9rem; color: var(--text-gray);">
                    Menampilkan data transaksi pembayaran sukses untuk bulan ini dan bulan kemarin.
                </div>
                <div style="position: relative; min-width: 250px;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-gray); font-size: 0.9rem;"></i>
                    <input type="text" id="pembayaranModalSearch" oninput="debounceSearchPembayaran()" class="form-control" placeholder="Cari pelanggan/kasir..." style="padding-left: 36px; height: 40px; border-radius: 10px; width: 100%; margin: 0;">
                </div>
            </div>
            
            <div class="table-container" style="margin-top: 0; max-height: 400px; overflow-y: auto; border: 1px solid var(--border-color); border-radius: 12px;">
                <table class="table" style="font-size: 0.85rem; margin: 0;">
                    <thead>
                        <tr style="position: sticky; top: 0; background-color: #f8fafc; z-index: 10;">
                            <th>No</th>
                            <th>Pelanggan</th>
                            <th>Periode</th>
                            <th>Jumlah Bayar</th>
                            <th>Waktu Pembayaran</th>
                            <th>Metode/Kasir</th>
                        </tr>
                    </thead>
                    <tbody id="pembayaranModalBody">
                        <!-- Loaded dynamically via AJAX -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination Container -->
            <div id="pembayaranModalPagination" style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; flex-wrap: wrap; gap: 12px; font-size: 0.85rem;">
                <div id="pembayaranModalInfo" style="color: var(--text-gray); font-weight: 500;">
                    Showing 0 to 0 of 0 entries
                </div>
                <div id="pembayaranModalPages" style="display: flex; gap: 4px;">
                    <!-- pagination buttons -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        setupTablePagination("#transaksiTable", "#transaksiPagination", "#tableLimit", "#tableSearch");
        setupCustomSelect();
    });

    // Toggle custom dropdown dropdown container
    function toggleCustomDropdown() {
        const container = document.getElementById('custom_pelanggan_select');
        if (container) {
            container.classList.toggle('active');
            if (container.classList.contains('active')) {
                const searchInput = document.getElementById('search_pelanggan');
                if (searchInput) {
                    searchInput.value = '';
                    searchInput.dispatchEvent(new Event('input')); // Reset list on open
                    searchInput.focus();
                }
            }
        }
    }

    // Close custom dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const container = document.getElementById('custom_pelanggan_select');
        if (container && !container.contains(e.target)) {
            container.classList.remove('active');
        }
    });

    // Custom searchable select functionality
    function setupCustomSelect() {
        const container = document.getElementById('custom_pelanggan_select');
        const hiddenInput = document.getElementById('manual_id_pelanggan');
        const triggerText = document.getElementById('custom_select_text');
        const optionsList = document.getElementById('custom_select_options');
        const searchInput = document.getElementById('search_pelanggan');

        if (!container || !hiddenInput || !triggerText || !optionsList || !searchInput) return;

        const optionElements = Array.from(optionsList.querySelectorAll('.custom-select-option'));

        optionElements.forEach(opt => {
            opt.addEventListener('click', function() {
                const val = this.getAttribute('data-value');
                const text = this.getAttribute('data-text');
                const harga = parseFloat(this.getAttribute('data-harga')) || 0;

                // Set actual value and text
                hiddenInput.value = val;
                triggerText.textContent = text;

                // Update styling state
                optionElements.forEach(el => el.classList.remove('selected'));
                this.classList.add('selected');

                // Autofill default package price
                if (harga > 0) {
                    document.getElementById('manual_jml_bayar').value = harga;
                } else {
                    document.getElementById('manual_jml_bayar').value = '';
                }

                // Hide custom dropdown list
                container.classList.remove('active');
            });
        });

        // Filter dropdown option elements when search input changes
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();

            optionElements.forEach((opt, index) => {
                if (index === 0) {
                    opt.style.display = 'block'; // Always display the placeholder option
                    return;
                }
                const text = opt.getAttribute('data-text').toLowerCase();
                if (text.includes(query)) {
                    opt.style.display = 'block';
                } else {
                    opt.style.display = 'none';
                }
            });
        });

        // Prevent standard click events inside dropdown search box from closing the menu
        searchInput.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

    function openManualInvoiceModal() {
        document.getElementById('manualInvoiceModal').classList.add('active');
        
        // Reset selections
        const hiddenInput = document.getElementById('manual_id_pelanggan');
        const triggerText = document.getElementById('custom_select_text');
        const searchInput = document.getElementById('search_pelanggan');
        const optionsList = document.getElementById('custom_select_options');

        if (hiddenInput) hiddenInput.value = '';
        if (triggerText) triggerText.textContent = '-- Pilih Pelanggan --';
        if (searchInput) {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
        }
        if (optionsList) {
            const selectedOpt = optionsList.querySelector('.custom-select-option[data-value=""]');
            const optionElements = optionsList.querySelectorAll('.custom-select-option');
            optionElements.forEach(el => el.classList.remove('selected'));
            if (selectedOpt) selectedOpt.classList.add('selected');
        }

        // Reset nominal field
        document.getElementById('manual_jml_bayar').value = '';
    }

    function closeManualInvoiceModal() {
        document.getElementById('manualInvoiceModal').classList.remove('active');
    }

    // Trigger Broadcast, Reminder, and Bulk Blokir AJAX sending process
    function triggerBroadcast(type) {
        let confirmMsg = '';
        if (type === 'broadcast') {
            confirmMsg = 'Kirim siaran/broadcast tagihan ke SEMUA pelanggan yang belum membayar via WhatsApp?';
        } else if (type === 'reminder') {
            confirmMsg = 'Kirim pesan pengingat/reminder tagihan ke SEMUA pelanggan yang belum membayar via WhatsApp?';
        } else if (type === 'bulk_blokir') {
            confirmMsg = 'Blokir akses internet SEMUA pelanggan yang belum membayar di Mikrotik dan kirim notifikasi WhatsApp?';
        }

        if (!confirm(confirmMsg)) return;

        // Open progress modal
        const modal = document.getElementById('broadcastProgressModal');
        const modalCloseBtn = document.getElementById('broadcastModalCloseBtn');
        const statusHeader = document.getElementById('broadcastStatusHeader');
        const progressBar = document.getElementById('broadcastProgressBar');
        const resultsList = document.getElementById('broadcastResultsList');
        const tutupBtn = document.getElementById('broadcastModalTutupBtn');
        const titleText = document.getElementById('broadcastModalTitle');

        if (!modal) return;

        modal.classList.add('active');
        
        let title = '';
        let initialStatus = '';
        let url = '';
        
        if (type === 'broadcast') {
            title = 'Broadcast WhatsApp';
            initialStatus = 'Sedang memproses pengiriman WhatsApp...';
            url = '{{ route("admin.transaksi.broadcast") }}';
        } else if (type === 'reminder') {
            title = 'Reminder WhatsApp';
            initialStatus = 'Sedang memproses pengiriman WhatsApp...';
            url = '{{ route("admin.transaksi.reminder") }}';
        } else if (type === 'bulk_blokir') {
            title = 'Blokir Massal & WhatsApp';
            initialStatus = 'Sedang memproses pemblokiran Mikrotik & pengiriman WhatsApp...';
            url = '{{ route("admin.transaksi.bulk_blokir") }}';
        }
        
        titleText.textContent = title;
        
        // Reset modal state
        modalCloseBtn.style.display = 'none';
        tutupBtn.style.display = 'none';
        progressBar.style.width = '0%';
        progressBar.style.backgroundColor = '#4f46e5'; // Primary theme blue-purple
        statusHeader.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin" style="color: #4f46e5; font-size: 1.2rem;"></i><span>${initialStatus}</span>`;
        resultsList.innerHTML = '<div style="color: var(--text-gray); font-style: italic; text-align: center; padding: 20px;">Memulai koneksi ke server, mohon tunggu...</div>';

        // Perform AJAX request
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

        // Animate progress bar incrementally as indeterminate
        let progress = 10;
        const progressInterval = setInterval(() => {
            if (progress < 90) {
                progress += 5;
                progressBar.style.width = progress + '%';
            }
        }, 600);

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': token
            }
        })
        .then(response => {
            clearInterval(progressInterval);
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(text || `HTTP error! status: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            progressBar.style.width = '100%';
            
            if (data.success) {
                progressBar.style.backgroundColor = '#22c55e'; // Green on success
                statusHeader.innerHTML = `<span><i class="fa-solid fa-circle-check" style="color: #22c55e; font-size: 1.2rem; margin-right: 6px;"></i> Selesai! Berhasil: ${data.berhasil}, Gagal: ${data.gagal}</span>`;
                
                resultsList.innerHTML = '';
                if (data.results.length === 0) {
                    resultsList.innerHTML = '<div style="color: var(--text-gray); font-style: italic; text-align: center; padding: 20px;">Tidak ada pesan yang dikirim.</div>';
                } else {
                    data.results.forEach(res => {
                        const itemDiv = document.createElement('div');
                        if (res.status) {
                            itemDiv.style.cssText = 'background-color: #f0fdf4; border: 1px solid #dcfce7; border-radius: 8px; padding: 10px 14px; display: flex; align-items: flex-start; gap: 8px; color: #166534; font-size: 0.88rem;';
                            itemDiv.innerHTML = `
                                <i class="fa-solid fa-circle-check" style="color: #22c55e; margin-top: 3px;"></i>
                                <div>
                                    <strong>${res.nama}</strong> (${res.no_telp}) &mdash; ${res.message}
                                </div>
                            `;
                        } else {
                            itemDiv.style.cssText = 'background-color: #fef2f2; border: 1px solid #fee2e2; border-radius: 8px; padding: 10px 14px; display: flex; align-items: flex-start; gap: 8px; color: #991b1b; font-size: 0.88rem;';
                            itemDiv.innerHTML = `
                                <i class="fa-solid fa-circle-xmark" style="color: #ef4444; margin-top: 3px;"></i>
                                <div>
                                    <strong>${res.nama}</strong> (${res.no_telp}) &mdash; ${res.message}
                                </div>
                            `;
                        }
                        resultsList.appendChild(itemDiv);
                    });
                }
            } else {
                progressBar.style.backgroundColor = '#ef4444'; // Red on fail
                statusHeader.innerHTML = `<span><i class="fa-solid fa-circle-xmark" style="color: #ef4444; font-size: 1.2rem; margin-right: 6px;"></i> Gagal memproses: ${data.message}</span>`;
                resultsList.innerHTML = `<div style="color: #ef4444; padding: 10px; font-weight: 500;">Error: ${data.message}</div>`;
            }

            // Show close button
            modalCloseBtn.style.display = 'block';
            tutupBtn.style.display = 'block';
        })
        .catch(err => {
            clearInterval(progressInterval);
            progressBar.style.width = '100%';
            progressBar.style.backgroundColor = '#ef4444'; // Red on error
            statusHeader.innerHTML = `<span><i class="fa-solid fa-circle-xmark" style="color: #ef4444; font-size: 1.2rem; margin-right: 6px;"></i> Terjadi Kesalahan Koneksi</span>`;
            
            let errMsg = err.message || 'Error tidak diketahui';
            if (errMsg.includes('<html') || errMsg.includes('<!DOCTYPE')) {
                errMsg = 'Sesi Anda telah kedaluwarsa atau token keamanan tidak cocok (Error 419 / 500). Silakan muat ulang halaman ini dan coba lagi.';
            }
            resultsList.innerHTML = `<div style="color: #ef4444; padding: 10px; font-weight: 500;">Kesalahan: ${errMsg}</div>`;
            
            // Show close button
            modalCloseBtn.style.display = 'block';
            tutupBtn.style.display = 'block';
        });
    }

    function closeBroadcastProgressModal() {
        document.getElementById('broadcastProgressModal').classList.remove('active');
        // Reload page to reflect updated status (like terkirim state)
        window.location.reload();
    }

    // Modal Lihat Transaksi Pembayaran
    let searchPembayaranTimeout = null;
    function debounceSearchPembayaran() {
        clearTimeout(searchPembayaranTimeout);
        searchPembayaranTimeout = setTimeout(() => {
            loadPembayaranData(1);
        }, 300); // Debounce delay 300ms
    }

    function openPembayaranModal() {
        const modal = document.getElementById('pembayaranModal');
        if (modal) {
            const searchInput = document.getElementById('pembayaranModalSearch');
            if (searchInput) {
                searchInput.value = '';
            }
            modal.classList.add('active');
            loadPembayaranData(1);
        }
    }

    function closePembayaranModal() {
        const modal = document.getElementById('pembayaranModal');
        if (modal) {
            modal.classList.remove('active');
        }
    }

    // Helper format rupiah
    function formatRupiah(amount) {
        return 'Rp ' + new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0 }).format(amount);
    }

    function loadPembayaranData(page) {
        const body = document.getElementById('pembayaranModalBody');
        const info = document.getElementById('pembayaranModalInfo');
        const pages = document.getElementById('pembayaranModalPages');

        if (!body) return;

        body.innerHTML = `
            <tr>
                <td colspan="6" style="text-align: center; color: var(--text-gray); padding: 30px;">
                    <i class="fa-solid fa-spinner fa-spin" style="margin-right: 6px;"></i> Memuat data transaksi...
                </td>
            </tr>
        `;

        const searchInput = document.getElementById('pembayaranModalSearch');
        const searchQuery = searchInput ? encodeURIComponent(searchInput.value) : '';

        fetch(`{{ route('admin.transaksi.pembayaran_json') }}?page=${page}&search=${searchQuery}`)
            .then(res => {
                if (!res.ok) {
                    throw new Error('Gagal mengambil data dari server');
                }
                return res.json();
            })
            .then(data => {
                body.innerHTML = '';
                
                if (!data.data || data.data.length === 0) {
                    const hasSearch = searchQuery !== '';
                    body.innerHTML = `
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-gray); padding: 30px;">
                                ${hasSearch ? 'Tidak ada transaksi pembayaran yang cocok dengan pencarian Anda.' : 'Belum ada transaksi pembayaran dalam 2 bulan terakhir.'}
                            </td>
                        </tr>
                    `;
                    if (info) info.textContent = 'Showing 0 to 0 of 0 entries';
                    if (pages) pages.innerHTML = '';
                    return;
                }

                // Render rows
                data.data.forEach((tx, idx) => {
                    const rowNo = data.from + idx;
                    const customerName = tx.pelanggan ? tx.pelanggan.nama_pelanggan : 'N/A';
                    const customerCode = tx.pelanggan ? tx.pelanggan.kode_pelanggan : '-';
                    
                    // Format period (bulan_tahun is mY format, e.g. 062026)
                    let period = tx.bulan_tahun || '-';
                    if (period.length === 6) {
                        period = period.substring(0, 2) + '-' + period.substring(2);
                    }

                    const amount = formatRupiah(tx.jml_bayar || 0);
                    const time = tx.waktu_bayar || '-';
                    
                    // Cashier/Method
                    let cashier = 'KASIR LAPANGAN';
                    let badgeClass = 'badge-kasir';
                    if (!tx.user_id) {
                        cashier = 'MIDTRANS GATEWAY';
                        badgeClass = 'badge-midtrans';
                    } else if (tx.penerima) {
                        cashier = tx.penerima.nama_user;
                    }

                    // Style badge
                    let methodBadge = `<span class="badge-payment ${badgeClass}" style="display: inline-flex; align-items: center; padding: 4px 12px; border-radius: 9999px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; ${
                        badgeClass === 'badge-midtrans' ? 'background-color: #ecfdf5; color: #059669;' : 'background-color: #f5f3ff; color: #7c3aed;'
                    }">${cashier}</span>`;

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${rowNo}</td>
                        <td>
                            <strong>${customerName}</strong><br>
                            <small style="color:var(--text-gray); font-family: monospace;">${customerCode}</small>
                        </td>
                        <td>${period}</td>
                        <td><strong>${amount}</strong></td>
                        <td>${time}</td>
                        <td>${methodBadge}</td>
                    `;
                    body.appendChild(tr);
                });

                // Update pagination info
                if (info) {
                    info.textContent = `Showing ${data.from} to ${data.to} of ${data.total} entries`;
                }

                // Render pagination buttons
                if (pages) {
                    pages.innerHTML = '';

                    // Previous Button
                    const prevBtn = document.createElement('button');
                    prevBtn.type = 'button';
                    prevBtn.textContent = 'Sebelumnya';
                    prevBtn.style.cssText = 'padding: 6px 12px; border-radius: 8px; border: 1px solid var(--border-color); background: white; font-weight: 600; cursor: pointer;';
                    if (data.current_page === 1) {
                        prevBtn.disabled = true;
                        prevBtn.style.opacity = '0.5';
                        prevBtn.style.cursor = 'not-allowed';
                    } else {
                        prevBtn.onclick = () => loadPembayaranData(data.current_page - 1);
                    }
                    pages.appendChild(prevBtn);

                    // Page numbers
                    const startPage = Math.max(1, data.current_page - 2);
                    const endPage = Math.min(data.last_page, data.current_page + 2);

                    for (let p = startPage; p <= endPage; p++) {
                        const pageBtn = document.createElement('button');
                        pageBtn.type = 'button';
                        pageBtn.textContent = p;
                        pageBtn.style.cssText = `padding: 6px 12px; border-radius: 8px; border: 1px solid ${p === data.current_page ? 'transparent' : 'var(--border-color)'}; background: ${p === data.current_page ? 'var(--primary-gradient)' : 'white'}; color: ${p === data.current_page ? 'white' : 'var(--text-dark)'}; font-weight: 600; cursor: pointer; margin: 0 2px;`;
                        if (p === data.current_page) {
                            pageBtn.style.cursor = 'default';
                        } else {
                            pageBtn.onclick = () => loadPembayaranData(p);
                        }
                        pages.appendChild(pageBtn);
                    }

                    // Next Button
                    const nextBtn = document.createElement('button');
                    nextBtn.type = 'button';
                    nextBtn.textContent = 'Selanjutnya';
                    nextBtn.style.cssText = 'padding: 6px 12px; border-radius: 8px; border: 1px solid var(--border-color); background: white; font-weight: 600; cursor: pointer;';
                    if (data.current_page === data.last_page) {
                        nextBtn.disabled = true;
                        nextBtn.style.opacity = '0.5';
                        nextBtn.style.cursor = 'not-allowed';
                    } else {
                        nextBtn.onclick = () => loadPembayaranData(data.current_page + 1);
                    }
                    pages.appendChild(nextBtn);
                }
            })
            .catch(err => {
                body.innerHTML = `
                    <tr>
                        <td colspan="6" style="text-align: center; color: #dc2626; padding: 30px;">
                            <i class="fa-solid fa-circle-xmark" style="margin-right: 6px;"></i> ${err.message || 'Terjadi kesalahan koneksi'}
                        </td>
                    </tr>
                `;
            });
    }
</script>
@endsection

<!-- Modal Progress Broadcast WhatsApp -->
<div class="modal" id="broadcastProgressModal">
    <div class="modal-content" style="width: min(600px, 100%);">
        <div class="modal-header">
            <h3 style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-brands fa-whatsapp" style="color: #25d366; font-size: 1.4rem;"></i>
                <span id="broadcastModalTitle">Broadcast WhatsApp</span>
            </h3>
            <button class="modal-close" id="broadcastModalCloseBtn" onclick="closeBroadcastProgressModal()" style="display: none;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 24px; text-align: left;">
            <!-- Status Header -->
            <div id="broadcastStatusHeader" style="display: flex; align-items: center; gap: 10px; font-weight: 600; font-size: 1.05rem; margin-bottom: 16px; color: var(--text-dark);">
                <i class="fa-solid fa-circle-notch fa-spin" style="color: #4f46e5; font-size: 1.2rem;"></i>
                <span>Sedang memproses pengiriman WhatsApp...</span>
            </div>

            <!-- Progress Bar -->
            <div style="background-color: #e2e8f0; height: 10px; border-radius: 9999px; margin-bottom: 20px; overflow: hidden; position: relative;">
                <div id="broadcastProgressBar" style="background-color: #22c55e; height: 100%; width: 0%; transition: width 0.3s ease;"></div>
            </div>

            <!-- Log Results Container -->
            <div style="border: 1px solid var(--border-color); border-radius: 12px; background-color: #f8fafc; padding: 14px; max-height: 250px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px;" id="broadcastResultsList">
                <div style="color: var(--text-gray); font-style: italic; text-align: center; padding: 20px;">
                    Menunggu respons API...
                </div>
            </div>

            <!-- Close Action Button -->
            <div style="display: flex; justify-content: flex-end; margin-top: 24px;">
                <button type="button" class="btn btn-primary" id="broadcastModalTutupBtn" onclick="closeBroadcastProgressModal()" style="display: none; padding: 10px 24px;">Tutup</button>
            </div>
        </div>
    </div>
</div>
