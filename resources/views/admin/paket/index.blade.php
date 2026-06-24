@extends('layouts.admin')

@section('title', 'Data Paket Internet')

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
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-box-archive"></i>
            <span>Daftar Paket Internet</span>
        </div>
        <button class="btn btn-primary" onclick="openAddModal()">
            <i class="fa-solid fa-plus"></i> Tambah Paket
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
                <input type="text" id="tableSearch" class="form-control" placeholder="Cari paket..." style="padding-left:36px; height:40px; border-radius:10px; width:100%;">
            </div>
        </div>
    </div>

    <div class="table-container">
        <table class="table" id="paketTable">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Paket</th>
                    <th>Harga Bulanan</th>
                    <th>PPN (%)</th>
                    <th>Profile Mikrotik (Name)</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paket as $index => $p)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><strong>{{ $p->nama_paket }}</strong></td>
                        <td>Rp {{ number_format($p->harga, 0, ',', '.') }}</td>
                        <td>{{ $p->ppn }}%</td>
                        <td><span style="font-family: monospace; font-weight:600; color:#7c3aed;">{{ $p->id_pmikrotik ?: '-' }}</span></td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                <button class="btn btn-info" style="padding: 6px 12px; font-size: 0.8rem;" 
                                    onclick='openEditModal({!! json_encode($p) !!})'>
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </button>
                                <button class="btn btn-danger" style="padding: 6px 12px; font-size: 0.8rem;" 
                                    onclick="openDeleteModal('{{ $p->id_paket }}', '{{ $p->nama_paket }}')">
                                    <i class="fa-solid fa-trash-can"></i> Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-gray); padding: 30px;">
                            Belum ada data paket di database.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div id="paketPagination"></div>
</div>

<!-- Modal Tambah Paket -->
<div class="modal" id="addModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Tambah Paket Baru</h3>
            <button class="modal-close" onclick="closeAddModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('admin.paket.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="nama_paket">Nama Paket *</label>
                    <input type="text" id="nama_paket" name="nama_paket" class="form-control" required placeholder="Contoh: Paket Family 20 Mbps">
                </div>

                <div class="form-group">
                    <label for="harga">Harga Langganan Bulanan (Rp) *</label>
                    <input type="number" id="harga" name="harga" class="form-control" required placeholder="Contoh: 150000">
                </div>

                <div class="form-group">
                    <label for="ppn">PPN (%)</label>
                    <input type="number" id="ppn" name="ppn" class="form-control" value="0" placeholder="0">
                </div>

                <div class="form-group">
                    <label for="id_pmikrotik">Nama PPP Profile di Mikrotik</label>
                    <input type="text" id="id_pmikrotik" name="id_pmikrotik" class="form-control" placeholder="Contoh: 20Mbps_Profile">
                    <small style="color:var(--text-gray); display:block; margin-bottom:6px;">Sesuaikan dengan nama Profile yang ada di Winbox Mikrotik.</small>
                    
                    @if(isset($mikrotiks) && !$mikrotiks->isEmpty())
                        <div style="margin-top: 8px; padding: 12px; background-color: #f8fafc; border-radius: 12px; border: 1px solid var(--border-color);">
                            <label style="font-size: 0.8rem; font-weight: 600; color: var(--text-gray); display: block; margin-bottom: 6px;">
                                <i class="fa-solid fa-cloud-arrow-down" style="color: #4f46e5;"></i> Ambil dari MikroTik (Multi-Router)
                            </label>
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <select class="form-control router-select" style="font-size: 0.8rem; height: 36px; padding: 4px 8px; flex: 1;">
                                    <option value="">-- Pilih Router --</option>
                                    @foreach($mikrotiks as $m)
                                        <option value="{{ $m->id_mikrotik }}">{{ $m->nama_mikrotik ?: $m->ip }} ({{ $m->ip }})</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-info fetch-profiles-btn" style="padding: 0 12px; height: 36px; font-size: 0.8rem; margin: 0; white-space: nowrap;">
                                    Load
                                </button>
                            </div>
                            <div class="profiles-dropdown-container" style="margin-top: 8px; display: none;">
                                <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-gray); display: block; margin-bottom: 4px;">Pilih Profile Terdeteksi:</label>
                                <select class="form-control profiles-select" style="font-size: 0.8rem; height: 36px; padding: 4px 8px;">
                                    <option value="">-- Pilih Profile --</option>
                                </select>
                            </div>
                        </div>
                    @endif
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                    <button type="button" class="btn btn-secondary" onclick="closeAddModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Paket</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Paket -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Ubah Paket</h3>
            <button class="modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('admin.paket.update') }}" method="POST">
                @csrf
                <input type="hidden" name="id_paket" id="edit_id">

                <div class="form-group">
                    <label for="edit_nama_paket">Nama Paket *</label>
                    <input type="text" id="edit_nama_paket" name="nama_paket" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="edit_harga">Harga Langganan Bulanan (Rp) *</label>
                    <input type="number" id="edit_harga" name="harga" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="edit_ppn">PPN (%)</label>
                    <input type="number" id="edit_ppn" name="ppn" class="form-control">
                </div>

                <div class="form-group">
                    <label for="edit_id_pmikrotik">Nama PPP Profile di Mikrotik</label>
                    <input type="text" id="edit_id_pmikrotik" name="id_pmikrotik" class="form-control">
                    
                    @if(isset($mikrotiks) && !$mikrotiks->isEmpty())
                        <div style="margin-top: 8px; padding: 12px; background-color: #f8fafc; border-radius: 12px; border: 1px solid var(--border-color);">
                            <label style="font-size: 0.8rem; font-weight: 600; color: var(--text-gray); display: block; margin-bottom: 6px;">
                                <i class="fa-solid fa-cloud-arrow-down" style="color: #4f46e5;"></i> Ambil dari MikroTik (Multi-Router)
                            </label>
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <select class="form-control router-select" style="font-size: 0.8rem; height: 36px; padding: 4px 8px; flex: 1;">
                                    <option value="">-- Pilih Router --</option>
                                    @foreach($mikrotiks as $m)
                                        <option value="{{ $m->id_mikrotik }}">{{ $m->nama_mikrotik ?: $m->ip }} ({{ $m->ip }})</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-info fetch-profiles-btn" style="padding: 0 12px; height: 36px; font-size: 0.8rem; margin: 0; white-space: nowrap;">
                                    Load
                                </button>
                            </div>
                            <div class="profiles-dropdown-container" style="margin-top: 8px; display: none;">
                                <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-gray); display: block; margin-bottom: 4px;">Pilih Profile Terdeteksi:</label>
                                <select class="form-control profiles-select" style="font-size: 0.8rem; height: 36px; padding: 4px 8px;">
                                    <option value="">-- Pilih Profile --</option>
                                </select>
                            </div>
                        </div>
                    @endif
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Hapus Paket -->
<div class="modal" id="deleteModal">
    <div class="modal-content" style="width: min(400px, 100%);">
        <div class="modal-header" style="background:#dc2626;">
            <h3>Hapus Paket</h3>
            <button class="modal-close" onclick="closeDeleteModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('admin.paket.destroy') }}" method="POST">
                @csrf
                <input type="hidden" name="id_paket" id="delete_id">
                <p style="font-size:0.95rem; margin-bottom:20px; line-height:1.5; color:#334155;">
                    Apakah Anda yakin ingin menghapus paket <strong id="delete_name"></strong>?<br>
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
        setupTablePagination("#paketTable", "#paketPagination", "#tableLimit", "#tableSearch");

        // AJAX profile retrieval handlers
        document.querySelectorAll('.fetch-profiles-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const container = this.closest('div').parentElement;
                const routerSelect = container.querySelector('.router-select');
                const deviceId = routerSelect.value;

                if (!deviceId) {
                    alert('Silakan pilih router MikroTik terlebih dahulu.');
                    return;
                }

                const profilesDropdownContainer = container.querySelector('.profiles-dropdown-container');
                const profilesSelect = container.querySelector('.profiles-select');

                this.disabled = true;
                this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Load...';

                fetch(`{{ route('admin.paket.get_mikrotik_profiles') }}?device_id=${deviceId}`)
                    .then(res => res.json())
                    .then(data => {
                        this.disabled = false;
                        this.innerHTML = 'Load';

                        if (data.success) {
                            profilesSelect.innerHTML = '<option value="">-- Pilih Profile --</option>';
                            data.profiles.forEach(prof => {
                                const opt = document.createElement('option');
                                opt.value = prof;
                                opt.textContent = prof;
                                profilesSelect.appendChild(opt);
                            });
                            profilesDropdownContainer.style.display = 'block';
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(err => {
                        this.disabled = false;
                        this.innerHTML = 'Load';
                        alert('Gagal mengambil data profile dari MikroTik.');
                    });
            });
        });

        document.querySelectorAll('.profiles-select').forEach(select => {
            select.addEventListener('change', function() {
                const val = this.value;
                if (val) {
                    const modalBody = this.closest('.modal-body');
                    const profileInput = modalBody.querySelector('[name="id_pmikrotik"]');
                    if (profileInput) {
                        profileInput.value = val;
                    }
                }
            });
        });
    });

    function openAddModal() {
        document.getElementById('addModal').classList.add('active');
    }
    function closeAddModal() {
        document.getElementById('addModal').classList.remove('active');
        // Reset selections
        const modal = document.getElementById('addModal');
        const routerSelect = modal.querySelector('.router-select');
        if (routerSelect) {
            routerSelect.value = '';
            modal.querySelector('.profiles-dropdown-container').style.display = 'none';
            modal.querySelector('.profiles-select').innerHTML = '<option value="">-- Pilih Profile --</option>';
        }
    }

    function openEditModal(paket) {
        document.getElementById('edit_id').value = paket.id_paket;
        document.getElementById('edit_nama_paket').value = paket.nama_paket;
        document.getElementById('edit_harga').value = paket.harga;
        document.getElementById('edit_ppn').value = paket.ppn;
        document.getElementById('edit_id_pmikrotik').value = paket.id_pmikrotik || '';
        document.getElementById('editModal').classList.add('active');
    }
    function closeEditModal() {
        document.getElementById('editModal').classList.remove('active');
        // Reset selections
        const modal = document.getElementById('editModal');
        const routerSelect = modal.querySelector('.router-select');
        if (routerSelect) {
            routerSelect.value = '';
            modal.querySelector('.profiles-dropdown-container').style.display = 'none';
            modal.querySelector('.profiles-select').innerHTML = '<option value="">-- Pilih Profile --</option>';
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
</script>
@endsection
