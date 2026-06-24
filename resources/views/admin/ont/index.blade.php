@extends('layouts.admin')

@section('title', 'Data ONT / Modem')

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
        background-color: #f8fafc;
    }

    .form-control:focus {
        border-color: #4f46e5;
        background-color: white;
    }

    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div class="card-title">
            <i class="fa-solid fa-hard-drive"></i>
            <span>Daftar Perangkat ONT / Modem</span>
        </div>
        <button class="btn btn-primary" id="btnTambahPerangkat">
            <i class="fa-solid fa-plus"></i> Tambah ONT
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
                <input type="text" id="tableSearch" class="form-control" placeholder="Cari ONT..." style="padding-left:36px; height:40px; border-radius:10px; width:100%;">
            </div>
        </div>
    </div>

    <div class="table-container">
        <table class="table" id="ontTable">
            <thead>
                <tr>
                    <th style="width: 80px;">No</th>
                    <th>Nama Perangkat ONT / Modem</th>
                    <th>Pelanggan Terhubung</th>
                    <th style="width: 200px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ontDevices as $index => $device)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><strong>{{ $device->nama_perangkat }}</strong></td>
                        <td>
                            @if($device->pelanggan->count() > 0)
                                <div style="max-height: 120px; overflow-y: auto; font-size: 0.85rem;">
                                    <ul style="padding-left: 16px; margin: 0; color: #475569;">
                                        @foreach($device->pelanggan as $plg)
                                            <li style="margin-bottom: 2px;">
                                                <strong>{{ $plg->nama_pelanggan }}</strong> 
                                                <span style="color: var(--text-gray); font-size: 0.8rem;">({{ $plg->kode_pelanggan }})</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @else
                                <span style="color: var(--text-gray); font-style: italic; font-size: 0.82rem;">Belum ada pelanggan terhubung</span>
                            @endif
                        </td>
                        <td align="center">
                            <div style="display: flex; gap: 8px; justify-content: center;">
                                <button class="btn btn-info btn-edit" 
                                        data-id="{{ $device->id_perangkat }}" 
                                        data-nama="{{ $device->nama_perangkat }}" 
                                        style="padding: 6px 12px; font-size: 0.8rem;">
                                    <i class="fa-solid fa-edit"></i> Edit
                                </button>
                                <form action="{{ route('admin.ont.destroy') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ONT ini?');" style="display: inline-block; margin: 0;">
                                    @csrf
                                    <input type="hidden" name="id_perangkat" value="{{ $device->id_perangkat }}">
                                    <button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size: 0.8rem;">
                                        <i class="fa-solid fa-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--text-gray); padding: 30px;">
                            Belum ada data ONT. Silakan tambahkan baru.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div id="ontPagination"></div>
</div>

<!-- Modal Tambah Perangkat -->
<div class="modal" id="modalAddPerangkat">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Tambah Perangkat ONT Baru</h3>
            <button class="modal-close" id="btnCloseAddPerangkat">&times;</button>
        </div>
        <form action="{{ route('admin.ont.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="add_nama_perangkat">Nama Perangkat ONT / Modem *</label>
                    <input type="text" name="nama_perangkat" id="add_nama_perangkat" class="form-control" required placeholder="Contoh: ZTE F609">
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                    <button type="button" class="btn btn-secondary" id="btnCancelAddPerangkat">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan ONT</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Perangkat -->
<div class="modal" id="modalEditPerangkat">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Perangkat ONT</h3>
            <button class="modal-close" id="btnCloseEditPerangkat">&times;</button>
        </div>
        <form action="{{ route('admin.ont.update') }}" method="POST">
            @csrf
            <input type="hidden" name="id_perangkat" id="edit_id_perangkat">
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_nama_perangkat">Nama Perangkat ONT / Modem *</label>
                    <input type="text" name="nama_perangkat" id="edit_nama_perangkat" class="form-control" required placeholder="Contoh: ZTE F609">
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                    <button type="button" class="btn btn-secondary" id="btnCancelEditPerangkat">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Setup pagination utility
        setupTablePagination("#ontTable", "#ontPagination", "#tableLimit", "#tableSearch");

        // Elements
        const modalAdd = document.getElementById('modalAddPerangkat');
        const modalEdit = document.getElementById('modalEditPerangkat');
        const btnTambah = document.getElementById('btnTambahPerangkat');

        // Form fields for editing
        const editIdInput = document.getElementById('edit_id_perangkat');
        const editNamaInput = document.getElementById('edit_nama_perangkat');

        // Functions to open/close modals
        function openModal(modal) {
            modal.classList.add('active');
        }

        function closeModal(modal) {
            modal.classList.remove('active');
        }

        // Add Modal Event Listeners
        if (btnTambah) {
            btnTambah.addEventListener('click', () => openModal(modalAdd));
        }
        document.getElementById('btnCloseAddPerangkat').addEventListener('click', () => closeModal(modalAdd));
        document.getElementById('btnCancelAddPerangkat').addEventListener('click', () => closeModal(modalAdd));

        // Edit Modal Event Listeners
        document.getElementById('btnCloseEditPerangkat').addEventListener('click', () => closeModal(modalEdit));
        document.getElementById('btnCancelEditPerangkat').addEventListener('click', () => closeModal(modalEdit));

        // Event delegation for Edit button
        document.getElementById('ontTable').addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-edit');
            if (btn) {
                const id = btn.getAttribute('data-id');
                const nama = btn.getAttribute('data-nama');

                editIdInput.value = id;
                editNamaInput.value = nama;

                openModal(modalEdit);
            }
        });

        // Click outside to close modals
        window.addEventListener('click', (e) => {
            if (e.target === modalAdd) closeModal(modalAdd);
            if (e.target === modalEdit) closeModal(modalEdit);
        });
    });
</script>
@endsection
