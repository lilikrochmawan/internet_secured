@extends('layouts.admin')

@section('title', 'Keluhan Pelanggan')

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
<!-- Card Cetak Laporan Keluhan & Gangguan -->
<div class="card" style="margin-bottom: 24px;">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-print"></i>
            <span>Cetak Laporan Keluhan & Gangguan</span>
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
                    <i class="fa-solid fa-print"></i> Cetak Laporan
                </button>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>Daftar Keluhan & Tiket Gangguan</span>
        </div>
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
                        <td><span style="font-family: monospace; font-weight:700; color:#4f46e5;">#{{ $k->nomor_tiket }}</span></td>
                        <td>
                            <strong>{{ $k->pelanggan->nama_pelanggan ?? 'N/A' }}</strong><br>
                            <small style="color:var(--text-gray);">{{ $k->no_wa }}</small>
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
                        </td>
                        <td>{{ $k->tanggal }}</td>
                        <td>
                            @if($k->status_keluhan == 'menunggu')
                                <span class="badge badge-menunggu">Menunggu</span>
                            @elseif($k->status_keluhan == 'proses')
                                <span class="badge badge-proses">Diproses</span>
                            @elseif($k->status_keluhan == 'selesai')
                                <span class="badge badge-selesai">Selesai</span>
                            @else
                                <span class="badge" style="background:#f1f5f9; color:#64748b;">{{ $k->status_keluhan }}</span>
                            @endif
                        </td>
                        <td align="center">
                            @if($k->status_keluhan == 'menunggu')
                                <form action="{{ route('admin.keluhan.proses') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="id_keluhan" value="{{ $k->id_keluhan }}">
                                    <button type="submit" class="btn btn-info">
                                        <i class="fa-solid fa-spinner"></i> Proses Keluhan
                                    </button>
                                </form>
                            @elseif($k->status_keluhan == 'proses')
                                <button type="button" class="btn btn-success open-selesai-modal" data-id="{{ $k->id_keluhan }}" data-tiket="{{ $k->nomor_tiket }}">
                                    <i class="fa-solid fa-circle-check"></i> Selesaikan
                                </button>
                            @else
                                <span style="font-size:0.85rem; color:#64748b; font-style:italic;">Tiket Selesai</span>
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
<div class="modal-backdrop" id="imageViewerModal">
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

<!-- Modal Selesaikan Keluhan -->
<div class="modal-backdrop" id="selesaiModal">
    <div class="modal-content" style="max-width: 480px; padding: 24px;">
        <div class="modal-header">
            <span class="modal-title">Selesaikan Tiket Keluhan</span>
            <button type="button" class="modal-close" onclick="closeSelesaiModal()">&times;</button>
        </div>
        <div class="modal-body" style="padding: 10px 0;">
            <form action="{{ route('admin.keluhan.selesai') }}" method="POST">
                @csrf
                <input type="hidden" name="id_keluhan" id="selesai_id_keluhan">
                
                <div style="font-size: 0.9rem; color: #475569; margin-bottom: 16px; text-align: left;">
                    Anda akan menyelesaikan Tiket Gangguan <strong id="selesai_tiket_label">#000</strong>. Silakan isi penyebab/masalah keluhan ini terlebih dahulu.
                </div>

                <div class="form-group" style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; text-align: left;">
                    <label for="input_masalah" style="font-weight: 600; font-size: 0.85rem; color: #334155;">Penyebab Keluhan / Solusi *</label>
                    <textarea name="masalah" id="input_masalah" rows="4" class="form-control" placeholder="Contoh: Kabel fiber putus tertabrak truk, sudah disambung kembali" required style="border: 1px solid #cbd5e1; border-radius: 12px; padding: 10px 14px; font-size: 0.95rem; outline: none; width: 100%; transition: border 0.2s; font-family: inherit; resize: vertical;"></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                    <button type="button" class="btn btn-secondary" onclick="closeSelesaiModal()" style="padding: 10px 16px; border-radius: 12px; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: all 0.2s; border: none; background-color: #e2e8f0; color: #334155;">Batal</button>
                    <button type="submit" class="btn btn-primary" style="padding: 10px 16px; border-radius: 12px; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: all 0.2s; border: none; background: var(--primary-gradient); color: white; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);">Simpan & Selesaikan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
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

        // Selesaikan modal event delegation logic
        if (keluhanTable) {
            keluhanTable.addEventListener("click", function(e) {
                const btn = e.target.closest(".open-selesai-modal");
                if (btn) {
                    const id = btn.getAttribute("data-id");
                    const tiket = btn.getAttribute("data-tiket");
                    openSelesaiModal(id, tiket);
                }
            });
        }

        // Close selesai modal clicking outside content
        const selesaiModal = document.getElementById("selesaiModal");
        if (selesaiModal) {
            selesaiModal.addEventListener("click", function (e) {
                if (e.target === selesaiModal) {
                    closeSelesaiModal();
                }
            });
        }
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

    function openSelesaiModal(id, ticketNumber) {
        document.getElementById('selesai_id_keluhan').value = id;
        document.getElementById('selesai_tiket_label').textContent = '#' + ticketNumber;
        document.getElementById('input_masalah').value = '';
        document.getElementById('selesaiModal').classList.add('show');
    }

    function closeSelesaiModal() {
        document.getElementById('selesaiModal').classList.remove('show');
    }
</script>
@endsection
