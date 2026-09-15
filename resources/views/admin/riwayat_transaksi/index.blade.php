@extends('layouts.admin')

@section('title', 'Riwayat Transaksi')

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

    .content-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .page-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .page-title i {
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .card {
        background-color: white;
        border-radius: 20px;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
        margin-bottom: 24px;
        overflow: hidden;
    }

    .card-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-color);
        background-color: #f8fafc;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .card-header h3 {
        font-family: 'Outfit', sans-serif;
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0;
    }

    .card-body {
        padding: 24px;
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
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .badge-info {
        background-color: #eff6ff;
        color: #2563eb;
    }
    
    .badge-danger {
        background-color: #fef2f2;
        color: #dc2626;
    }
    
    .badge-secondary {
        background-color: #f1f5f9;
        color: #64748b;
    }

    .form-group {
        margin-bottom: 18px;
        display: flex;
        flex-direction: column;
        gap: 6px;
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
    
    .filter-container {
        display: flex;
        gap: 12px;
        align-items: flex-end;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }
    
    .pagination {
        display: flex;
        list-style: none;
        gap: 5px;
        padding: 0;
        margin-top: 20px;
        justify-content: flex-end;
    }
    
    .pagination li {
        display: inline-block;
    }
    
    .pagination li a, .pagination li span {
        padding: 8px 12px;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        color: var(--text-dark);
        text-decoration: none;
        font-size: 0.9rem;
    }
    
    .pagination li.active span {
        background: var(--primary-gradient);
        color: white;
        border: none;
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
        margin: 0;
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

    @keyframes modalFadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection

@section('content')
<div class="content-header">
    <h1 class="page-title"><i class="fa-solid fa-clock-rotate-left"></i> Riwayat Transaksi</h1>
</div>

<!-- TOP 10 Menunggak -->
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; cursor: pointer;" onclick="toggleTop10()">
        <h3 style="margin: 0;">Top 10 Pelanggan Menunggak</h3>
        <span id="top10Icon" style="font-size: 1.2rem; color: #64748b; transition: transform 0.3s;">▼</span>
    </div>
    <div class="card-body" id="top10Body" style="padding: 0; display: none;">
        <div class="table-container" style="margin-top: 0;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Pelanggan</th>
                        <th>Jml Tunggakan</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topMenunggak as $tunggak)
                        <tr>
                            <td>
                                <strong>{{ $tunggak->nama_pelanggan }}</strong><br>
                                <span style="color: var(--text-gray); font-size: 0.85rem;">{{ $tunggak->no_telp }}</span>
                            </td>
                            <td>
                                <span class="badge badge-danger">{{ $tunggak->jumlah_tunggakan }} Bulan</span>
                            </td>
                            <td>Rp {{ number_format($tunggak->total_tunggakan, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align: center;">Tidak ada pelanggan menunggak.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Riwayat Pembayaran Bulanan -->
<div class="card">
    <div class="card-header">
        <h3>Riwayat Pembayaran Bulanan</h3>
        <button type="button" class="btn btn-info" onclick="openLaporanModal()">
            <i class="fa-solid fa-file-invoice"></i> Laporan Petugas
        </button>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.riwayat_transaksi.index') }}" method="GET" class="filter-container">
            <div style="flex: 1; max-width: 300px;">
                <label for="search" style="font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">Cari Pelanggan</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Nama pelanggan..." value="{{ request('search') }}">
            </div>
            <div style="flex: 1; max-width: 300px;">
                <label for="metode_pembayaran" style="font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">Metode Pembayaran</label>
                <select class="form-control" id="metode_pembayaran" name="metode_pembayaran">
                    <option value="">-- Semua Metode --</option>
                    <option value="manual" {{ $metode == 'manual' ? 'selected' : '' }}>Manual/Tunai</option>
                    <option value="gateway" {{ $metode == 'gateway' ? 'selected' : '' }}>Payment Gateway</option>
                    @foreach($metodeList as $m)
                        @if($m != 'Manual/Tunai' && $m != 'Payment Gateway')
                        <option value="{{ $m }}" {{ $metode == $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Filter</button>
                @if($metode || request('search'))
                    <a href="{{ route('admin.riwayat_transaksi.index') }}" class="btn btn-secondary">Reset</a>
                @endif
            </div>
        </form>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal Bayar</th>
                        <th>Pelanggan</th>
                        <th>Bulan Tagihan</th>
                        <th>Metode</th>
                        <th>Petugas</th>
                        <th>Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riwayat as $r)
                        <tr>
                            <td>{{ $r->waktu_bayar ? \Carbon\Carbon::parse($r->waktu_bayar)->format('d/m/Y H:i') : \Carbon\Carbon::parse($r->tgl_bayar)->format('d/m/Y') }}</td>
                            <td>
                                @if($r->pelanggan)
                                    {{ $r->pelanggan->nama_pelanggan }}
                                @else
                                    <span style="color: #dc2626;">Pelanggan Dihapus</span>
                                @endif
                            </td>
                            <td>{{ $r->bulan_tahun }}</td>
                            <td>
                                @if($r->metode_pembayaran)
                                    <span class="badge badge-info">{{ $r->metode_pembayaran }}</span>
                                @elseif($r->user_id)
                                    <span class="badge badge-secondary">Manual/Tunai</span>
                                @else
                                    <span class="badge badge-info">Payment Gateway</span>
                                @endif
                            </td>
                            <td>
                                @if($r->penerima)
                                    {{ $r->penerima->nama_user }}
                                @elseif(!$r->user_id)
                                    <span style="color: var(--text-gray); font-style: italic;">Sistem</span>
                                @else
                                    - 
                                @endif
                            </td>
                            <td>Rp {{ number_format($r->terbayar > 0 ? $r->terbayar : $r->jml_bayar, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center;">Tidak ada riwayat transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div style="margin-top: 20px;">
            {{ $riwayat->links() }}
        </div>
    </div>
</div>

<!-- Modal Laporan Petugas -->
<div class="modal" id="laporanModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Laporan Petugas Kasir</h3>
            <button class="modal-close" onclick="closeLaporanModal()"><i class="fa-solid fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div style="display: flex; gap: 12px; margin-bottom: 20px;">
                <div style="flex: 1;">
                    <label for="lap_bulan" style="font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">Bulan</label>
                    <select id="lap_bulan" class="form-control">
                        @for($i=1; $i<=12; $i++)
                            <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" {{ date('m') == $i ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}</option>
                        @endfor
                    </select>
                </div>
                <div style="flex: 1;">
                    <label for="lap_tahun" style="font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">Tahun</label>
                    <select id="lap_tahun" class="form-control">
                        @for($i=date('Y')-2; $i<=date('Y'); $i++)
                            <option value="{{ $i }}" {{ date('Y') == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div style="display: flex; align-items: flex-end;">
                    <button class="btn btn-primary" type="button" onclick="loadLaporan()"><i class="fa-solid fa-search"></i> Tampilkan</button>
                </div>
            </div>

            <div class="table-container" style="margin-top: 0; max-height: 300px; overflow-y: auto;">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nama Petugas</th>
                            <th>Jml Transaksi</th>
                            <th>Total Nominal</th>
                        </tr>
                    </thead>
                    <tbody id="laporan_body">
                        <tr>
                            <td colspan="3" style="text-align: center;">Silakan klik tampilkan.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleTop10() {
        const body = document.getElementById('top10Body');
        const icon = document.getElementById('top10Icon');
        if (body.style.display === 'none') {
            body.style.display = 'block';
            icon.style.transform = 'rotate(180deg)';
        } else {
            body.style.display = 'none';
            icon.style.transform = 'rotate(0deg)';
        }
    }

    function openLaporanModal() {
        document.getElementById('laporanModal').classList.add('active');
        loadLaporan();
    }

    function closeLaporanModal() {
        document.getElementById('laporanModal').classList.remove('active');
    }

    function loadLaporan() {
        const bulan = document.getElementById('lap_bulan').value;
        const tahun = document.getElementById('lap_tahun').value;
        const tbody = document.getElementById('laporan_body');
        
        tbody.innerHTML = '<tr><td colspan="3" style="text-align: center;">Memuat data...</td></tr>';

        fetch(`{{ route('admin.riwayat_transaksi.laporan') }}?bulan=${bulan}&tahun=${tahun}`)
            .then(response => response.json())
            .then(data => {
                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="3" style="text-align: center;">Tidak ada data pada periode ini.</td></tr>';
                    return;
                }

                let html = '';
                let totalTransaksi = 0;
                let totalNominal = 0;

                data.forEach(row => {
                    totalTransaksi += parseInt(row.total_transaksi);
                    totalNominal += parseInt(row.total_nominal);
                    
                    let pelangganHtml = '';
                    if(row.pelanggan_list && row.pelanggan_list.length > 0) {
                        pelangganHtml = row.pelanggan_list.map(p => `<span style="display:inline-block; background:#f1f5f9; padding:2px 8px; border-radius:12px; font-size:0.75rem; margin:2px;">${p}</span>`).join('');
                    }

                    html += `
                        <tr>
                            <td>
                                <strong>${row.nama_user}</strong>
                                <div style="margin-top: 6px; display: flex; flex-wrap: wrap; gap: 4px;">
                                    ${pelangganHtml}
                                </div>
                            </td>
                            <td style="text-align: center; vertical-align: top; padding-top: 14px;">${row.total_transaksi}</td>
                            <td style="text-align: right; vertical-align: top; padding-top: 14px;">Rp ${parseInt(row.total_nominal).toLocaleString('id-ID')}</td>
                        </tr>
                    `;
                });

                html += `
                    <tr style="background-color: #f8fafc; font-weight: bold;">
                        <td style="text-align: right;">TOTAL</td>
                        <td style="text-align: center;">${totalTransaksi}</td>
                        <td style="text-align: right;">Rp ${totalNominal.toLocaleString('id-ID')}</td>
                    </tr>
                `;

                tbody.innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                tbody.innerHTML = '<tr><td colspan="3" style="text-align: center; color: red;">Terjadi kesalahan saat memuat data.</td></tr>';
            });
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target == document.getElementById('laporanModal')) {
            closeLaporanModal();
        }
    }
</script>
@endsection
