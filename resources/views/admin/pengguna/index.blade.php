@extends('layouts.admin')

@section('title', 'Manajemen Pengguna Staff')

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

    .badge-admin {
        background-color: #faf5ff;
        color: #7c3aed;
    }

    .badge-kasir {
        background-color: #eff6ff;
        color: #2563eb;
    }

    .badge-teknisi {
        background-color: #fff7ed;
        color: #ea580c;
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
        width: min(450px, 100%);
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
<!-- Card Manajemen Pengguna Staff -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-users-gear"></i>
            <span>Daftar Pengguna Staff & Administrator</span>
        </div>
        <button class="btn btn-primary" onclick="openAddModal()">
            <i class="fa-solid fa-user-plus"></i> Tambah Akun Staff
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
                <input type="text" id="tableSearch" class="form-control" placeholder="Cari staff..." style="padding-left:36px; height:40px; border-radius:10px; width:100%;">
            </div>
        </div>
    </div>

    <div class="table-container">
        <table class="table" id="penggunaTable">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama User</th>
                    <th>Username</th>
                    <th>Password</th>
                    <th>Level Akses</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $index => $u)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $u->nama_user }}</strong><br>
                            @if($u->phone_number)
                                <small style="color:var(--text-gray);"><i class="fa-solid fa-phone" style="font-size:0.72rem;"></i> {{ $u->phone_number }}</small>
                            @else
                                <small style="color:#ef4444;"><i class="fa-solid fa-triangle-exclamation" style="font-size:0.72rem;"></i> Belum diatur</small>
                            @endif
                        </td>
                        <td><span style="font-family: monospace; font-weight:600; color:#4f46e5;">{{ $u->username }}</span></td>
                        <td><span style="font-family: monospace; color: #94a3b8;">••••••••</span></td>
                        <td>
                            @if($u->level == 'admin')
                                <span class="badge badge-admin">Administrator</span>
                            @elseif($u->level == 'kasir')
                                <span class="badge badge-kasir">Kasir</span>
                            @elseif($u->level == 'teknisi')
                                <span class="badge badge-teknisi">Teknisi Lapangan</span>
                            @elseif($u->level == 'sales')
                                <span class="badge" style="background-color: #fffbeb; color: #b45309;">Sales</span>
                            @elseif($u->level == 'mitra')
                                <span class="badge" style="background-color: #faf5ff; color: #6b21a8;">Mitra</span>
                            @else
                                <span class="badge" style="background:#f1f5f9; color:#64748b;">{{ $u->level }}</span>
                            @endif
                        </td>
                        <td align="center">
                            <div style="display: flex; gap: 8px; justify-content:center;">
                                <button class="btn btn-info" style="padding: 6px 12px; font-size: 0.8rem;" 
                                    onclick='openEditModal({!! json_encode($u) !!})'>
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </button>
                                
                                @if($u->id !== auth()->id())
                                    <button class="btn btn-danger" style="padding: 6px 12px; font-size: 0.8rem;" 
                                        onclick="openDeleteModal('{{ $u->id }}', '{{ $u->nama_user }}', false)">
                                        <i class="fa-solid fa-trash-can"></i> Hapus
                                    </button>
                                @else
                                    <button class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.8rem; opacity:0.6; cursor:not-allowed;" disabled>
                                        <i class="fa-solid fa-lock"></i> Active
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-gray); padding: 30px;">
                            Belum ada akun staff terdaftar di database.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div id="penggunaPagination"></div>
</div>

<!-- Card Manajemen Pengguna Pelanggan -->
<div class="card" style="margin-top: 30px;">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-user-group"></i>
            <span>Daftar Pengguna Pelanggan (Client)</span>
        </div>
    </div>
    
    <!-- Info Banner -->
    <div class="alert alert-success" style="margin-bottom: 20px; background-color: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe;">
        <i class="fa-solid fa-circle-info" style="font-size: 1.2rem; color: #2563eb;"></i>
        <div style="line-height: 1.5;">
            Akun login pelanggan otomatis dibuat saat Anda menambahkan pelanggan baru di menu 
            <a href="{{ route('admin.pelanggan.index') }}" style="font-weight: 700; color: #2563eb; text-decoration: underline;">Data Pelanggan</a>.
        </div>
    </div>

    <!-- Search and Row Limiter for Pelanggan -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-top: 10px; margin-bottom:16px; flex-wrap:wrap; gap:12px;">
        <div></div>
        <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-gray);">Tampilkan:</span>
                <select id="pelangganLimit" class="form-control" style="padding: 4px 8px; height: 40px; border-radius: 10px; font-size: 0.85rem; width: auto; margin: 0;">
                    <option value="10" selected>10 Baris</option>
                    <option value="25">25 Baris</option>
                    <option value="50">50 Baris</option>
                    <option value="100">100 Baris</option>
                </select>
            </div>
            <div style="position:relative; min-width:220px;">
                <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--text-gray); font-size:0.9rem;"></i>
                <input type="text" id="pelangganSearch" class="form-control" placeholder="Cari pelanggan..." style="padding-left:36px; height:40px; border-radius:10px; width:100%;">
            </div>
        </div>
    </div>

    <div class="table-container">
        <table class="table" id="pelangganTable">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Pelanggan</th>
                    <th>Username</th>
                    <th>Password</th>
                    <th>Paket Internet</th>
                    <th>No. Telp / Alamat</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customerUsers as $index => $cu)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $cu->nama_user }}</strong>
                            @if($cu->pelanggan)
                                <br><small style="color:var(--text-gray); font-family: monospace;">{{ $cu->pelanggan->kode_pelanggan }}</small>
                            @endif
                        </td>
                        <td><span style="font-family: monospace; font-weight:600; color:#4f46e5;">{{ $cu->username }}</span></td>
                        <td>
                            <div style="display:flex; align-items:center; gap:6px;">
                                <span style="font-family: monospace; color: #64748b;" id="cu-pass-text-{{ $cu->id }}">••••••••</span>
                                <button type="button" style="background:none; border:none; color:#94a3b8; cursor:pointer; padding:2px; display: inline-flex; align-items: center;" 
                                    onclick="toggleShowPassword('{{ $cu->id }}', '{{ addslashes($cu->password) }}')">
                                    <i class="fa-solid fa-eye" id="cu-pass-icon-{{ $cu->id }}" style="font-size:0.85rem;"></i>
                                </button>
                            </div>
                        </td>
                        <td>
                            @if($cu->pelanggan && $cu->pelanggan->paketDetail)
                                <span class="badge animate-fade" style="background-color: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; text-transform: none;">
                                    {{ $cu->pelanggan->paketDetail->nama_paket }}
                                </span>
                            @else
                                <span class="badge" style="background-color: #f1f5f9; color: #64748b;">-</span>
                            @endif
                        </td>
                        <td>
                            @if($cu->pelanggan)
                                <span style="font-size:0.85rem; font-weight: 500;">{{ $cu->pelanggan->no_telp }}</span>
                                <br><span style="font-size:0.8rem; color:var(--text-gray);">{{ Str::limit($cu->pelanggan->alamat, 40) }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td align="center">
                            <div style="display: flex; gap: 8px; justify-content:center;">
                                <button class="btn btn-info" style="padding: 6px 12px; font-size: 0.8rem;" 
                                    onclick='openEditModal({!! json_encode($cu) !!})'>
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </button>
                                
                                <button class="btn btn-danger" style="padding: 6px 12px; font-size: 0.8rem;" 
                                    onclick="openDeleteModal('{{ $cu->id }}', '{{ $cu->nama_user }}', true)">
                                    <i class="fa-solid fa-trash-can"></i> Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-gray); padding: 30px;">
                            Belum ada akun pelanggan terdaftar di database.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div id="pelangganPagination"></div>
</div>

<!-- Modal Tambah Pengguna -->
<div class="modal" id="addModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Tambah Akun Staff Baru</h3>
            <button class="modal-close" onclick="closeAddModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('admin.pengguna.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="nama_user">Nama Staff Lengkap *</label>
                    <input type="text" id="nama_user" name="nama_user" class="form-control" required placeholder="Contoh: Budi Santoso">
                </div>

                <div class="form-group">
                    <label for="username">Username Login *</label>
                    <input type="text" id="username" name="username" class="form-control" required placeholder="Contoh: budi_staff">
                </div>

                <div class="form-group">
                    <label for="password">Password Login *</label>
                    <input type="text" id="password" name="password" class="form-control" required placeholder="Contoh: Budi@123">
                </div>

                <div class="form-group">
                    <label for="level">Level Otoritas Hak Akses *</label>
                    <select id="level" name="level" class="form-control" required>
                        <option value="admin">Administrator (Akses Penuh)</option>
                        <option value="noc">NOC</option>
                        <option value="kasir">Kasir (Kelola Transaksi & Keuangan)</option>
                        <option value="teknisi">Teknisi Lapangan (Kelola Gangguan & Mikrotik)</option>
                        <option value="sales">Sales</option>
                        <option value="mitra">Mitra</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="phone_number">Nomor Telepon / WhatsApp *</label>
                    <input type="text" id="phone_number" name="phone_number" class="form-control" required placeholder="Contoh: 081234567890">
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                    <button type="button" class="btn btn-secondary" onclick="closeAddModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Pengguna -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="edit_modal_title">Ubah Akun Staff</h3>
            <button class="modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('admin.pengguna.update') }}" method="POST">
                @csrf
                <input type="hidden" name="id" id="edit_id">

                <div class="form-group">
                    <label for="edit_nama_user" id="edit_nama_label">Nama Staff Lengkap *</label>
                    <input type="text" id="edit_nama_user" name="nama_user" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="edit_username">Username Login *</label>
                    <input type="text" id="edit_username" name="username" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="edit_password">Password Login (Kosongkan jika tidak ingin diubah)</label>
                    <input type="text" id="edit_password" name="password" class="form-control" placeholder="Masukkan password baru untuk mengubah">
                </div>

                <div class="form-group" id="edit_level_group">
                    <label for="edit_level">Level Otoritas Hak Akses *</label>
                    <select id="edit_level" name="level" class="form-control" required>
                        <option value="admin">Administrator</option>
                        <option value="noc">NOC</option>
                        <option value="kasir">Kasir</option>
                        <option value="teknisi">Teknisi Lapangan</option>
                        <option value="sales">Sales</option>
                        <option value="mitra">Mitra</option>
                        <option value="user">Pelanggan</option>
                    </select>
                </div>

                <div class="form-group" id="edit_phone_group">
                    <label for="edit_phone_number">Nomor Telepon / WhatsApp *</label>
                    <input type="text" id="edit_phone_number" name="phone_number" class="form-control" placeholder="Contoh: 081234567890">
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Hapus Pengguna -->
<div class="modal" id="deleteModal">
    <div class="modal-content" style="width: min(450px, 100%);">
        <div class="modal-header" style="background:#dc2626;">
            <h3 id="delete_modal_title">Hapus Akun Staff</h3>
            <button class="modal-close" onclick="closeDeleteModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('admin.pengguna.destroy') }}" method="POST">
                @csrf
                <input type="hidden" name="id" id="delete_id">
                <p id="delete_warning_desc" style="font-size:0.95rem; margin-bottom:15px; line-height:1.5; color:#334155;">
                    Apakah Anda yakin ingin menghapus akun staff <strong id="delete_name"></strong>?<br>
                    Tindakan ini tidak dapat dibatalkan.
                </p>
                <div class="form-group" id="group_delete_alasan" style="text-align: left; margin-bottom: 20px; display: none;">
                    <label for="delete_alasan_hapus" style="font-weight: 600; font-size: 0.85rem; color: #334155; display: block; margin-bottom: 6px;">Alasan Penghapusan *</label>
                    <textarea name="alasan_hapus" id="delete_alasan_hapus" rows="3" class="form-control" placeholder="Masukkan alasan kenapa pelanggan ini dihapus..." style="border: 1px solid #cbd5e1; border-radius: 10px; padding: 8px 12px; font-size: 0.9rem; outline: none; width: 100%; resize: vertical; font-family: inherit;"></textarea>
                </div>
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
        setupTablePagination("#penggunaTable", "#penggunaPagination", "#tableLimit", "#tableSearch");
        setupTablePagination("#pelangganTable", "#pelangganPagination", "#pelangganLimit", "#pelangganSearch");
    });

    function toggleShowPassword(id, plainPassword) {
        const textSpan = document.getElementById('cu-pass-text-' + id);
        const icon = document.getElementById('cu-pass-icon-' + id);
        if (textSpan && icon) {
            if (textSpan.innerText === '••••••••') {
                textSpan.innerText = plainPassword;
                icon.className = 'fa-solid fa-eye-slash';
            } else {
                textSpan.innerText = '••••••••';
                icon.className = 'fa-solid fa-eye';
            }
        }
    }

    function openAddModal() {
        document.getElementById('addModal').classList.add('active');
    }
    function closeAddModal() {
        document.getElementById('addModal').classList.remove('active');
    }

    function openEditModal(user) {
        document.getElementById('edit_id').value = user.id;
        document.getElementById('edit_nama_user').value = user.nama_user;
        document.getElementById('edit_username').value = user.username;
        document.getElementById('edit_password').value = ''; // Kosongkan password saat edit agar aman
        
        const levelSelect = document.getElementById('edit_level');
        const levelGroup = document.getElementById('edit_level_group');
        const modalTitle = document.getElementById('edit_modal_title');
        const namaLabel = document.getElementById('edit_nama_label');
        
        const phoneInput = document.getElementById('edit_phone_number');
        const phoneGroup = document.getElementById('edit_phone_group');
        
        levelSelect.value = user.level;
        
        if (user.level === 'user') {
            levelGroup.style.display = 'none';
            phoneGroup.style.display = 'none';
            phoneInput.removeAttribute('required');
            phoneInput.value = '';
            modalTitle.innerText = 'Ubah Akun Pelanggan';
            namaLabel.innerText = 'Nama Pelanggan Lengkap *';
        } else {
            levelGroup.style.display = 'flex';
            phoneGroup.style.display = 'flex';
            phoneInput.setAttribute('required', 'required');
            phoneInput.value = user.phone_number || '';
            modalTitle.innerText = 'Ubah Akun Staff';
            namaLabel.innerText = 'Nama Staff Lengkap *';
        }
        
        document.getElementById('editModal').classList.add('active');
    }
    function closeEditModal() {
        document.getElementById('editModal').classList.remove('active');
    }

    function openDeleteModal(id, name, isCustomer = false) {
        document.getElementById('delete_id').value = id;
        
        const modalTitle = document.getElementById('delete_modal_title');
        const warningDesc = document.getElementById('delete_warning_desc');
        const alasanGroup = document.getElementById('group_delete_alasan');
        const alasanTextarea = document.getElementById('delete_alasan_hapus');
        
        alasanTextarea.value = '';
        
        if (isCustomer) {
            modalTitle.innerText = 'Hapus Akun Pelanggan';
            warningDesc.innerHTML = 'Apakah Anda yakin ingin menghapus akun pelanggan <strong>' + name + '</strong>?<br><br>' +
                                    '<span style="color:#dc2626; font-weight:700; display:inline-flex; align-items:center; gap:6px;">' +
                                    '<i class="fa-solid fa-triangle-exclamation"></i> PENTING: Tindakan ini juga akan menghapus data profil pelanggan & langganan mereka secara permanen dari database!' +
                                    '</span>';
            alasanGroup.style.display = 'block';
            alasanTextarea.setAttribute('required', 'required');
        } else {
            modalTitle.innerText = 'Hapus Akun Staff';
            warningDesc.innerHTML = 'Apakah Anda yakin ingin menghapus akun staff <strong>' + name + '</strong>?<br>Tindakan ini tidak dapat dibatalkan.';
            alasanGroup.style.display = 'none';
            alasanTextarea.removeAttribute('required');
        }
        
        document.getElementById('deleteModal').classList.add('active');
    }
    function closeDeleteModal() {
        document.getElementById('delete_alasan_hapus').value = '';
        document.getElementById('deleteModal').classList.remove('active');
    }
</script>
@endsection
