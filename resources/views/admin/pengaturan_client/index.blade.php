@extends('layouts.admin')

@section('title', 'Pengaturan Akses')

@section('styles')
<style>
    /* Tabs System Styling */
    .tabs-nav {
        display: flex;
        gap: 12px;
        border-bottom: 2px solid var(--border-color);
        margin-bottom: 24px;
        padding-bottom: 2px;
    }

    .tab-link {
        padding: 12px 20px;
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text-gray);
        cursor: pointer;
        border: none;
        background: none;
        position: relative;
        transition: color 0.2s ease;
    }

    .tab-link:hover {
        color: var(--text-dark);
    }

    .tab-link.active {
        color: #4f46e5;
    }

    .tab-link.active::after {
        content: '';
        position: absolute;
        bottom: -4px;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--primary-gradient);
        border-radius: 2px;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
        animation: fadeIn 0.3s ease;
    }

    /* Sub-Branch list indentation & tree styling */
    .branch-tree-node {
        background: #f8fafc;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        margin-bottom: 16px;
        padding: 16px;
        transition: box-shadow 0.2s;
    }

    .branch-tree-node:hover {
        box-shadow: var(--shadow-sm);
    }

    .branch-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 12px;
        margin-bottom: 12px;
    }

    .sub-branch-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding-left: 12px;
    }

    .sub-branch-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: white;
        padding: 10px 14px;
        border-radius: 10px;
        border: 1px solid #edf2f7;
    }

    /* Tree View Checkboxes for Access Modals */
    .tree-checkbox-container {
        max-height: 250px;
        overflow-y: auto;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 14px;
        background-color: #f8fafc;
    }

    .tree-branch-group {
        margin-bottom: 14px;
    }

    .tree-branch-header {
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 6px;
        color: var(--text-dark);
    }

    .tree-sub-list {
        list-style: none;
        padding-left: 24px;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .tree-sub-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
        color: var(--text-gray);
    }

    /* Badge styles */
    .role-badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .role-admin { background-color: #fef2f2; color: #dc2626; }
    .role-kasir { background-color: #f0fdf4; color: #166534; }
    .role-teknisi { background-color: #eff6ff; color: #1e40af; }
    .role-sales { background-color: #fffbeb; color: #b45309; }
    .role-mitra { background-color: #faf5ff; color: #6b21a8; }
    .role-noc { background-color: #ecfeff; color: #0891b2; }

    /* Modals styling */
    .modal-backdrop {
        position: fixed;
        inset: 0;
        background-color: rgba(15, 23, 42, 0.5);
        z-index: 1000;
        display: none;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(4px);
    }

    .modal-backdrop.show {
        display: flex;
    }

    .modal-content {
        background: white;
        border-radius: 24px;
        width: min(500px, 100%);
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: var(--shadow-lg);
        animation: slideDown 0.2s ease;
    }

    .modal-header {
        background: var(--primary-gradient);
        color: white;
        padding: 18px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .modal-title {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 1.15rem;
    }

    .modal-close {
        background: none;
        border: none;
        color: white;
        font-size: 1.3rem;
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
        margin-bottom: 16px;
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
        transition: border 0.2s;
    }

    .form-control:focus {
        border-color: #4f46e5;
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

    .btn-secondary {
        background-color: #e2e8f0;
        color: #334155;
    }

    .btn-danger {
        background-color: #fef2f2;
        color: #dc2626;
    }
    
    .btn-xs {
        padding: 6px 10px;
        font-size: 0.78rem;
        border-radius: 8px;
    }
</style>
@endsection

@section('content')
<div class="card">
    <div class="tabs-nav">
        <button class="tab-link active" onclick="switchTab(event, 'tab-branch')">
            <i class="fa-solid fa-sitemap" style="margin-right: 6px;"></i> Struktur Branch & Sub-Branch
        </button>
        <button class="tab-link" onclick="switchTab(event, 'tab-access')">
            <i class="fa-solid fa-user-lock" style="margin-right: 6px;"></i> Hak Akses & Role Staff
        </button>
    </div>

    <!-- Error/Success Alerts -->
    @if($errors->any())
        <div class="alert alert-danger">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- TAB 1: Branch & Sub-Branch -->
    <div id="tab-branch" class="tab-content active">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap; flex: 1;">
                <div style="position: relative; width: 100%; max-width: 320px;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.9rem;"></i>
                    <input type="text" id="search-branch-input" placeholder="Cari branch atau sub-branch..." 
                        style="width: 100%; padding: 8px 12px 8px 36px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.9rem; outline: none; transition: border-color 0.2s;"
                        oninput="filterBranches()">
                </div>
            </div>
            <button class="btn btn-primary" onclick="openModal('modalAddBranch')">
                <i class="fa-solid fa-plus"></i> Tambah Branch
            </button>
        </div>

        <div style="display: grid; grid-template-columns: 1fr; gap: 16px;">
            @forelse($branches as $branch)
                <div class="branch-tree-node">
                    <div class="branch-header">
                        <div>
                            <span style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 700; color: #1e293b;">
                                <i class="fa-solid fa-building-user" style="color: #4f46e5; margin-right: 6px;"></i>
                                {{ $branch->nama_branch }}
                            </span>
                            @if($branch->deskripsi)
                                <div style="font-size: 0.82rem; color: var(--text-gray); margin-top: 2px;">{{ $branch->deskripsi }}</div>
                            @endif
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <button class="btn btn-secondary btn-xs" onclick="openModalAddSubBranch('{{ $branch->id }}', '{{ $branch->nama_branch }}')">
                                <i class="fa-solid fa-circle-plus"></i> Tambah Sub-Branch
                            </button>
                            <button class="btn btn-secondary btn-xs" onclick="openModalEditBranch('{{ $branch->id }}', '{{ $branch->nama_branch }}', '{{ $branch->deskripsi }}')">
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </button>
                            <button class="btn btn-danger btn-xs" onclick="openModalDeleteBranch('{{ $branch->id }}', '{{ $branch->nama_branch }}')">
                                <i class="fa-solid fa-trash-can"></i> Hapus
                            </button>
                        </div>
                    </div>

                    <div class="sub-branch-list">
                        <div style="font-size: 0.78rem; text-transform: uppercase; font-weight: 700; color: var(--text-gray); margin-bottom: 4px; letter-spacing: 0.5px;">
                            Sub-Branch / Wilayah Kerja:
                        </div>
                        @forelse($branch->subBranches as $sub)
                            <div class="sub-branch-item">
                                <div>
                                    <strong style="color: #475569; font-size: 0.92rem;">
                                        <i class="fa-solid fa-location-dot" style="margin-right: 6px; color: #8b5cf6;"></i>
                                        {{ $sub->nama_sub_branch }}
                                    </strong>
                                    @if($sub->deskripsi)
                                        <span style="font-size: 0.8rem; color: var(--text-gray); margin-left: 8px;">— {{ $sub->deskripsi }}</span>
                                    @endif
                                </div>
                                <div style="display: flex; gap: 6px;">
                                    <button class="btn btn-secondary btn-xs" style="padding: 4px 8px; font-size:0.72rem;" 
                                        onclick="openModalEditSubBranch('{{ $sub->id }}', '{{ $sub->nama_sub_branch }}', '{{ $sub->deskripsi }}')">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <button class="btn btn-danger btn-xs" style="padding: 4px 8px; font-size:0.72rem;" 
                                        onclick="openModalDeleteSubBranch('{{ $sub->id }}', '{{ $sub->nama_sub_branch }}')">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div style="font-size: 0.85rem; color: var(--text-gray); padding: 6px 12px; font-style: italic;">
                                Belum ada sub-branch wilayah kerja di bawah branch ini.
                            </div>
                        @endforelse
                    </div>
                </div>
            @empty
                <div style="text-align: center; color: var(--text-gray); padding: 40px;">
                    <i class="fa-solid fa-network-wired" style="font-size: 2.5rem; margin-bottom: 12px; color: #cbd5e1;"></i>
                    <p>Belum ada Kantor Cabang (Branch) yang didaftarkan.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- TAB 2: Hak Akses & Role Staff -->
    <div id="tab-access" class="tab-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
            <div style="font-size: 0.9rem; color: var(--text-gray); flex: 1;">
                Atur dan sesuaikan hak akses wilayah kerja (Branch & Sub-Branch) untuk masing-masing user Administrator, Kasir, Teknisi, Sales, dan Mitra.
            </div>
            <div style="position: relative; width: 100%; max-width: 320px;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.9rem;"></i>
                <input type="text" id="search-staff-input" placeholder="Cari nama staff, username, role..." 
                    style="width: 100%; padding: 8px 12px 8px 36px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.9rem; outline: none; transition: border-color 0.2s;"
                    oninput="filterStaff()">
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #f8fafc; border-bottom: 1px solid var(--border-color);">
                        <th style="padding: 14px; text-align: left;">Nama Staff</th>
                        <th style="padding: 14px; text-align: left;">Username</th>
                        <th style="padding: 14px; text-align: left;">Role / Level</th>
                        <th style="padding: 14px; text-align: left;">Cakupan Wilayah Kerja (Hak Akses)</th>
                        <th style="padding: 14px; text-align: left;">Akses Menu Sidebar</th>
                        <th style="padding: 14px; text-align: center; width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        <tr class="staff-row" style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 14px;"><strong>{{ $u->nama_user }}</strong></td>
                            <td style="padding: 14px;"><code>{{ $u->username }}</code></td>
                            <td style="padding: 14px;">
                                @php
                                    $roleClass = 'role-mitra';
                                    if ($u->level == 'admin') $roleClass = 'role-admin';
                                    elseif ($u->level == 'noc') $roleClass = 'role-noc';
                                    elseif ($u->level == 'kasir') $roleClass = 'role-kasir';
                                    elseif ($u->level == 'teknisi') $roleClass = 'role-teknisi';
                                    elseif ($u->level == 'sales') $roleClass = 'role-sales';
                                @endphp
                                <span class="role-badge {{ $roleClass }}">{{ $u->level == 'teknisi' ? 'teknisi lapangan' : $u->level }}</span>
                            </td>
                            <td style="padding: 14px; max-width: 350px;">
                                @if($u->level == 'admin')
                                    <span style="color: #2563eb; font-weight: 600;"><i class="fa-solid fa-globe"></i> Semua Wilayah (Akses Penuh)</span>
                                @else
                                    @php
                                        $displayAccess = [];
                                        foreach($u->access_list as $acc) {
                                            $br = $branches->firstWhere('id', $acc->id_branch);
                                            if ($br) {
                                                if (is_null($acc->id_sub_branch)) {
                                                    $displayAccess[] = "<strong>" . $br->nama_branch . " (Semua)</strong>";
                                                } else {
                                                    $sub = $br->subBranches->firstWhere('id', $acc->id_sub_branch);
                                                    if ($sub) {
                                                        $displayAccess[] = $br->nama_branch . " > " . $sub->nama_sub_branch;
                                                    }
                                                }
                                            }
                                        }
                                    @endphp
                                    
                                    @if(count($displayAccess) > 0)
                                        <div style="display:flex; flex-wrap:wrap; gap:6px;">
                                            {!! implode(', ', $displayAccess) !!}
                                        </div>
                                    @else
                                        <span style="color: #94a3b8; font-style: italic;">Tidak ada hak akses wilayah (Akses Ditolak)</span>
                                    @endif
                                @endif
                            </td>
                            <td style="padding: 14px; max-width: 300px;">
                                @if($u->level == 'admin')
                                    <span style="color: #2563eb; font-weight: 600;"><i class="fa-solid fa-square-check"></i> Semua Menu (Akses Penuh)</span>
                                @else
                                    @if(count($u->menu_access_list) > 0)
                                        <div style="display:flex; flex-wrap:wrap; gap:4px;">
                                            @php
                                                $menuNames = [
                                                    'dashboard' => 'Dashboard',
                                                    'monitoring' => 'Monitoring',
                                                    'tr069' => 'TR-069',
                                                    'odc' => 'ODC',
                                                    'odp' => 'ODP',
                                                    'mapping' => 'Map & Topologi',
                                                    'custom_pesan' => 'Custom WA',
                                                    'broadcast' => 'Broadcast WA',
                                                    'pelanggan' => 'Pelanggan',
                                                    'paket' => 'Paket',
                                                    'ont' => 'ONT',
                                                    'promo' => 'Promo',
                                                    'transaksi' => 'Transaksi',
                                                    'kas' => 'Kas',
                                                    'keluhan' => 'Keluhan',
                                                    'pengguna' => 'Pengguna',
                                                    'order_pemasangan' => 'Order Pemasangan'
                                                ];
                                            @endphp
                                            @foreach($u->menu_access_list as $mKey)
                                                @php
                                                    $mName = $menuNames[$mKey] ?? $mKey;
                                                @endphp
                                                <span class="badge" style="background-color: #f1f5f9; color: #475569; padding: 2px 6px; border-radius: 6px; font-size: 0.75rem; border: 1px solid #e2e8f0;">{{ $mName }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span style="color: #94a3b8; font-style: italic;">Tidak ada akses menu</span>
                                    @endif
                                @endif
                            </td>
                            <td style="padding: 14px; text-align: center;">
                                <button class="btn btn-primary btn-xs" onclick='openModalEditAccess({!! json_encode($u) !!})'>
                                    <i class="fa-solid fa-user-shield"></i> Atur Akses
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-gray); padding: 30px;">
                                Belum ada data akun staff.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ================= MODALS SECTION ================= -->

<!-- Modal: Tambah Branch -->
<div class="modal-backdrop" id="modalAddBranch">
    <div class="modal-content">
        <div class="modal-header">
            <span class="modal-title">Tambah Branch Baru</span>
            <button type="button" class="modal-close" onclick="closeModal('modalAddBranch')">&times;</button>
        </div>
        <form action="{{ route('admin.branch.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="add_nama_branch">Nama Branch / Cabang *</label>
                    <input type="text" id="add_nama_branch" name="nama_branch" class="form-control" placeholder="Contoh: Cabang Wonogiri" required>
                </div>
                <div class="form-group">
                    <label for="add_desc_branch">Deskripsi Area</label>
                    <textarea id="add_desc_branch" name="deskripsi" class="form-control" rows="3" placeholder="Opsional"></textarea>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:10px;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modalAddBranch')">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Branch</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Branch -->
<div class="modal-backdrop" id="modalEditBranch">
    <div class="modal-content">
        <div class="modal-header">
            <span class="modal-title">Ubah Detail Branch</span>
            <button type="button" class="modal-close" onclick="closeModal('modalEditBranch')">&times;</button>
        </div>
        <form action="{{ route('admin.branch.update') }}" method="POST">
            @csrf
            <input type="hidden" name="id" id="edit_branch_id">
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_nama_branch">Nama Branch / Cabang *</label>
                    <input type="text" id="edit_nama_branch" name="nama_branch" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_desc_branch">Deskripsi Area</label>
                    <textarea id="edit_desc_branch" name="deskripsi" class="form-control" rows="3"></textarea>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:10px;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditBranch')">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Hapus Branch -->
<div class="modal-backdrop" id="modalDeleteBranch">
    <div class="modal-content" style="max-width: 440px;">
        <div class="modal-header" style="background: #dc2626;">
            <span class="modal-title">Hapus Branch</span>
            <button type="button" class="modal-close" onclick="closeModal('modalDeleteBranch')">&times;</button>
        </div>
        <form action="{{ route('admin.branch.destroy') }}" method="POST">
            @csrf
            <input type="hidden" name="id" id="delete_branch_id">
            <div class="modal-body">
                <p style="font-size: 0.95rem; line-height: 1.5; color: #334155; margin-bottom: 20px;">
                    Apakah Anda yakin ingin menghapus branch <strong id="delete_branch_name"></strong> beserta seluruh sub-branch di bawahnya?
                </p>
                <div style="display:flex; justify-content:flex-end; gap:8px;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modalDeleteBranch')">Batal</button>
                    <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Tambah Sub-Branch -->
<div class="modal-backdrop" id="modalAddSubBranch">
    <div class="modal-content">
        <div class="modal-header">
            <span class="modal-title">Tambah Sub-Branch di <span id="add_sub_parent_name" style="font-weight:700;"></span></span>
            <button type="button" class="modal-close" onclick="closeModal('modalAddSubBranch')">&times;</button>
        </div>
        <form action="{{ route('admin.sub_branch.store') }}" method="POST">
            @csrf
            <input type="hidden" name="id_branch" id="add_sub_branch_parent_id">
            <div class="modal-body">
                <div class="form-group">
                    <label for="add_nama_sub_branch">Nama Sub-Branch / Wilayah Kerja *</label>
                    <input type="text" id="add_nama_sub_branch" name="nama_sub_branch" class="form-control" placeholder="Contoh: RT 02 / RW 04" required>
                </div>
                <div class="form-group">
                    <label for="add_desc_sub_branch">Deskripsi Wilayah</label>
                    <textarea id="add_desc_sub_branch" name="deskripsi" class="form-control" rows="3" placeholder="Opsional"></textarea>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:10px;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modalAddSubBranch')">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Sub-Branch</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Sub-Branch -->
<div class="modal-backdrop" id="modalEditSubBranch">
    <div class="modal-content">
        <div class="modal-header">
            <span class="modal-title">Ubah Detail Sub-Branch</span>
            <button type="button" class="modal-close" onclick="closeModal('modalEditSubBranch')">&times;</button>
        </div>
        <form action="{{ route('admin.sub_branch.update') }}" method="POST">
            @csrf
            <input type="hidden" name="id" id="edit_sub_branch_id">
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_nama_sub_branch">Nama Sub-Branch / Wilayah Kerja *</label>
                    <input type="text" id="edit_nama_sub_branch" name="nama_sub_branch" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_desc_sub_branch">Deskripsi Wilayah</label>
                    <textarea id="edit_desc_sub_branch" name="deskripsi" class="form-control" rows="3"></textarea>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:10px;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditSubBranch')">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Hapus Sub-Branch -->
<div class="modal-backdrop" id="modalDeleteSubBranch">
    <div class="modal-content" style="max-width: 440px;">
        <div class="modal-header" style="background: #dc2626;">
            <span class="modal-title">Hapus Sub-Branch</span>
            <button type="button" class="modal-close" onclick="closeModal('modalDeleteSubBranch')">&times;</button>
        </div>
        <form action="{{ route('admin.sub_branch.destroy') }}" method="POST">
            @csrf
            <input type="hidden" name="id" id="delete_sub_branch_id">
            <div class="modal-body">
                <p style="font-size: 0.95rem; line-height: 1.5; color: #334155; margin-bottom: 20px;">
                    Apakah Anda yakin ingin menghapus sub-branch <strong id="delete_sub_branch_name"></strong>?
                </p>
                <div style="display:flex; justify-content:flex-end; gap:8px;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modalDeleteSubBranch')">Batal</button>
                    <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Hak Akses Staff -->
<div class="modal-backdrop" id="modalEditAccess">
    <div class="modal-content" style="width: min(560px, 100%);">
        <div class="modal-header">
            <span class="modal-title">Atur Akses Staff: <span id="access_staff_name" style="font-weight: 700;"></span></span>
            <button type="button" class="modal-close" onclick="closeModal('modalEditAccess')">&times;</button>
        </div>
        <form action="{{ route('admin.access.update') }}" method="POST">
            @csrf
            <input type="hidden" name="id_user" id="access_staff_id">
            <div class="modal-body">
                <!-- Role / Level Selector -->
                <div class="form-group">
                    <label for="access_level">Level / Jabatan Staff *</label>
                    <select name="level" id="access_level" class="form-control" style="background-color: white;" onchange="toggleAccessSectionByRole(this.value)">
                        <option value="admin">Administrator (Semua Area)</option>
                        <option value="noc">NOC</option>
                        <option value="kasir">Kasir</option>
                        <option value="teknisi">Teknisi Lapangan</option>
                        <option value="sales">Sales</option>
                        <option value="mitra">Mitra</option>
                    </select>
                </div>

                <!-- Tree Checkbox Section -->
                <div class="form-group" id="access_tree_section">
                    <label style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                        <span>Pilih Cakupan Hak Akses Area Operasional (Branch & Sub-Branch)</span>
                        <label style="font-size: 0.85rem; font-weight: normal; margin-bottom: 0; cursor: pointer; display: flex; align-items: center; gap: 4px;">
                            <input type="checkbox" id="toggle_all_areas" onchange="toggleAllAreas(this)"> Pilih Semua
                        </label>
                    </label>
                    <div class="tree-checkbox-container">
                        @foreach($branches as $b)
                            <div class="tree-branch-group">
                                <div class="tree-branch-header">
                                    <input type="checkbox" name="branches[]" value="{{ $b->id }}" id="check_branch_{{ $b->id }}" class="branch-checkbox" onchange="toggleBranchChildren(this, '{{ $b->id }}'); updateAllAreasCheckbox();">
                                    <label for="check_branch_{{ $b->id }}" style="font-size: 0.95rem; font-weight: 600; cursor:pointer;">
                                        <i class="fa-solid fa-building" style="color:#4f46e5; margin-right:4px;"></i> {{ $b->nama_branch }}
                                    </label>
                                </div>
                                <ul class="tree-sub-list">
                                    @foreach($b->subBranches as $s)
                                        <li class="tree-sub-item">
                                            <input type="checkbox" name="sub_branches[]" value="{{ $s->id }}" id="check_sub_{{ $s->id }}" class="sub-checkbox branch-{{ $b->id }}-child" onchange="toggleChildInfluence(this, '{{ $b->id }}'); updateAllAreasCheckbox();">
                                            <label for="check_sub_{{ $s->id }}" style="font-weight: 400; cursor:pointer;">
                                                <i class="fa-solid fa-location-arrow" style="font-size:0.75rem; color:#8b5cf6; margin-right:2px;"></i> {{ $s->nama_sub_branch }}
                                            </label>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                    <small style="color: var(--text-gray); margin-top: 6px; line-height:1.4;">
                        <i class="fa-solid fa-circle-info"></i> Mencentang nama Branch akan otomatis memberikan akses penuh ke seluruh Sub-Branch di bawahnya.
                    </small>
                </div>

                <!-- Menu Access Section -->
                <div class="form-group" id="access_menu_section" style="margin-top: 20px;">
                    <label style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                        <span>Pilih Akses Menu Sidebar</span>
                        <label style="font-size: 0.85rem; font-weight: normal; margin-bottom: 0; cursor: pointer; display: flex; align-items: center; gap: 4px;">
                            <input type="checkbox" id="toggle_all_menus" onchange="toggleAllMenus(this)"> Pilih Semua
                        </label>
                    </label>
                    <div class="menu-checkbox-container" style="max-height: 250px; overflow-y: auto; border: 1px solid #cbd5e1; border-radius: 12px; padding: 14px; background-color: #f8fafc; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div class="menu-checkbox-item" style="display: flex; align-items: center; gap: 8px; pointer-events: none; opacity: 0.8;">
                            <input type="checkbox" name="menus[]" value="dashboard" id="menu_dashboard" class="menu-checkbox" checked>
                            <label for="menu_dashboard" style="font-size: 0.9rem; font-weight: 600; cursor: default; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-chart-line" style="color: #4f46e5; width: 16px;"></i> Dashboard
                            </label>
                        </div>
                        <div class="menu-checkbox-item" style="display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" name="menus[]" value="monitoring" id="menu_monitoring" class="menu-checkbox">
                            <label for="menu_monitoring" style="font-size: 0.9rem; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-network-wired" style="color: #4f46e5; width: 16px;"></i> Monitoring
                            </label>
                        </div>
                        <div class="menu-checkbox-item" style="display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" name="menus[]" value="tr069" id="menu_tr069" class="menu-checkbox">
                            <label for="menu_tr069" style="font-size: 0.9rem; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-server" style="color: #4f46e5; width: 16px;"></i> TR-069 ACS
                            </label>
                        </div>
                        <div class="menu-checkbox-item" style="display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" name="menus[]" value="odc" id="menu_odc" class="menu-checkbox">
                            <label for="menu_odc" style="font-size: 0.9rem; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-circle-nodes" style="color: #4f46e5; width: 16px;"></i> Kelola ODC
                            </label>
                        </div>
                        <div class="menu-checkbox-item" style="display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" name="menus[]" value="odp" id="menu_odp" class="menu-checkbox">
                            <label for="menu_odp" style="font-size: 0.9rem; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-diagram-project" style="color: #4f46e5; width: 16px;"></i> Kelola ODP
                            </label>
                        </div>
                        <div class="menu-checkbox-item" style="display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" name="menus[]" value="mapping" id="menu_mapping" class="menu-checkbox">
                            <label for="menu_mapping" style="font-size: 0.9rem; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-map-location-dot" style="color: #4f46e5; width: 16px;"></i> Map & Topologi
                            </label>
                        </div>
                        <div class="menu-checkbox-item" style="display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" name="menus[]" value="custom_pesan" id="menu_custom_pesan" class="menu-checkbox">
                            <label for="menu_custom_pesan" style="font-size: 0.9rem; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-comment-dots" style="color: #4f46e5; width: 16px;"></i> Custom Pesan WA
                            </label>
                        </div>
                        <div class="menu-checkbox-item" style="display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" name="menus[]" value="broadcast" id="menu_broadcast" class="menu-checkbox">
                            <label for="menu_broadcast" style="font-size: 0.9rem; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-bullhorn" style="color: #4f46e5; width: 16px;"></i> Broadcast WA
                            </label>
                        </div>
                        <div class="menu-checkbox-item" style="display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" name="menus[]" value="pelanggan" id="menu_pelanggan" class="menu-checkbox">
                            <label for="menu_pelanggan" style="font-size: 0.9rem; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-user-group" style="color: #4f46e5; width: 16px;"></i> Data Pelanggan
                            </label>
                        </div>
                        <div class="menu-checkbox-item" style="display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" name="menus[]" value="paket" id="menu_paket" class="menu-checkbox">
                            <label for="menu_paket" style="font-size: 0.9rem; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-box-archive" style="color: #4f46e5; width: 16px;"></i> Data Paket
                            </label>
                        </div>
                        <div class="menu-checkbox-item" style="display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" name="menus[]" value="ont" id="menu_ont" class="menu-checkbox">
                            <label for="menu_ont" style="font-size: 0.9rem; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-hard-drive" style="color: #4f46e5; width: 16px;"></i> Data ONT
                            </label>
                        </div>
                        <div class="menu-checkbox-item" style="display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" name="menus[]" value="promo" id="menu_promo" class="menu-checkbox">
                            <label for="menu_promo" style="font-size: 0.9rem; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-tags" style="color: #4f46e5; width: 16px;"></i> Promo
                            </label>
                        </div>
                        <div class="menu-checkbox-item" style="display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" name="menus[]" value="transaksi" id="menu_transaksi" class="menu-checkbox">
                            <label for="menu_transaksi" style="font-size: 0.9rem; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-receipt" style="color: #4f46e5; width: 16px;"></i> Transaksi
                            </label>
                        </div>
                        <div class="menu-checkbox-item" style="display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" name="menus[]" value="kas" id="menu_kas" class="menu-checkbox">
                            <label for="menu_kas" style="font-size: 0.9rem; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-money-bill-transfer" style="color: #4f46e5; width: 16px;"></i> Kas Masuk/Keluar
                            </label>
                        </div>
                        <div class="menu-checkbox-item" style="display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" name="menus[]" value="keluhan" id="menu_keluhan" class="menu-checkbox">
                            <label for="menu_keluhan" style="font-size: 0.9rem; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-circle-exclamation" style="color: #4f46e5; width: 16px;"></i> Keluhan
                            </label>
                        </div>
                        <div class="menu-checkbox-item" style="display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" name="menus[]" value="pengguna" id="menu_pengguna" class="menu-checkbox">
                            <label for="menu_pengguna" style="font-size: 0.9rem; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-users-gear" style="color: #4f46e5; width: 16px;"></i> Pengguna / Staff
                            </label>
                        </div>
                        <div class="menu-checkbox-item" style="display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" name="menus[]" value="order_pemasangan" id="menu_order_pemasangan" class="menu-checkbox">
                            <label for="menu_order_pemasangan" style="font-size: 0.9rem; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-truck-ramp-box" style="color: #4f46e5; width: 16px;"></i> Order Pemasangan
                            </label>
                        </div>
                    </div>
                    <small style="color: var(--text-gray); margin-top: 6px; line-height:1.4;">
                        <i class="fa-solid fa-circle-info"></i> Pilih menu mana saja yang boleh diakses dan tampil pada sidebar staff ini.
                    </small>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:20px;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditAccess')">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Hak Akses</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Switch tabs logic
    function switchTab(e, tabId) {
        document.querySelectorAll('.tab-link').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

        e.currentTarget.classList.add('active');
        document.getElementById(tabId).classList.add('active');
    }

    // Live search filter for branches and sub-branches
    function filterBranches() {
        const query = document.getElementById('search-branch-input').value.toLowerCase().trim();
        const nodes = document.querySelectorAll('.branch-tree-node');
        
        nodes.forEach(node => {
            const branchNameEl = node.querySelector('.branch-header span');
            const branchName = branchNameEl ? branchNameEl.textContent.toLowerCase() : '';
            
            const branchDescEl = node.querySelector('.branch-header div div');
            const branchDesc = branchDescEl ? branchDescEl.textContent.toLowerCase() : '';
            
            const branchMatches = branchName.includes(query) || branchDesc.includes(query);
            
            const subItems = node.querySelectorAll('.sub-branch-item');
            let anySubMatches = false;
            
            subItems.forEach(sub => {
                const subTextEl = sub.querySelector('strong');
                const subText = subTextEl ? subTextEl.textContent.toLowerCase() : '';
                
                const subDescEl = sub.querySelector('span');
                const subDesc = subDescEl ? subDescEl.textContent.toLowerCase() : '';
                
                const subMatches = subText.includes(query) || subDesc.includes(query);
                
                if (subMatches) {
                    sub.style.display = 'flex';
                    anySubMatches = true;
                } else {
                    if (branchMatches && query !== '') {
                        sub.style.display = 'flex';
                    } else if (query !== '') {
                        sub.style.display = 'none';
                    } else {
                        sub.style.display = 'flex'; // Reset
                    }
                }
            });
            
            if (branchMatches || anySubMatches || query === '') {
                node.style.display = '';
            } else {
                node.style.display = 'none';
            }
        });
    }

    // Live search filter for staff accounts
    function filterStaff() {
        const query = document.getElementById('search-staff-input').value.toLowerCase().trim();
        const rows = document.querySelectorAll('.staff-row');
        
        rows.forEach(row => {
            const nameEl = row.querySelector('td:nth-child(1)');
            const name = nameEl ? nameEl.textContent.toLowerCase() : '';
            
            const usernameEl = row.querySelector('td:nth-child(2)');
            const username = usernameEl ? usernameEl.textContent.toLowerCase() : '';
            
            const roleEl = row.querySelector('td:nth-child(3)');
            const role = roleEl ? roleEl.textContent.toLowerCase() : '';
            
            const regionEl = row.querySelector('td:nth-child(4)');
            const region = regionEl ? regionEl.textContent.toLowerCase() : '';
            
            const matches = name.includes(query) || username.includes(query) || role.includes(query) || region.includes(query);
            
            if (matches || query === '') {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Modal management logic
    function openModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.add('show');
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.remove('show');
    }

    // Custom Open Modals for structures
    function openModalEditBranch(id, name, desc) {
        document.getElementById('edit_branch_id').value = id;
        document.getElementById('edit_nama_branch').value = name;
        document.getElementById('edit_desc_branch').value = desc === 'null' ? '' : desc;
        openModal('modalEditBranch');
    }

    function openModalDeleteBranch(id, name) {
        document.getElementById('delete_branch_id').value = id;
        document.getElementById('delete_branch_name').textContent = name;
        openModal('modalDeleteBranch');
    }

    function openModalAddSubBranch(parentId, parentName) {
        document.getElementById('add_sub_branch_parent_id').value = parentId;
        document.getElementById('add_sub_parent_name').textContent = parentName;
        document.getElementById('add_nama_sub_branch').value = '';
        document.getElementById('add_desc_sub_branch').value = '';
        openModal('modalAddSubBranch');
    }

    function openModalEditSubBranch(id, name, desc) {
        document.getElementById('edit_sub_branch_id').value = id;
        document.getElementById('edit_nama_sub_branch').value = name;
        document.getElementById('edit_desc_sub_branch').value = desc === 'null' ? '' : desc;
        openModal('modalEditSubBranch');
    }

    function openModalDeleteSubBranch(id, name) {
        document.getElementById('delete_sub_branch_id').value = id;
        document.getElementById('delete_sub_branch_name').textContent = name;
        openModal('modalDeleteSubBranch');
    }

    // Open Modal Edit Hak Akses
    function openModalEditAccess(user) {
        document.getElementById('access_staff_id').value = user.id;
        document.getElementById('access_staff_name').textContent = user.nama_user;
        document.getElementById('access_level').value = user.level;

        // Reset check boxes first
        document.querySelectorAll('.branch-checkbox').forEach(cb => cb.checked = false);
        document.querySelectorAll('.sub-checkbox').forEach(cb => cb.checked = false);
        document.querySelectorAll('.menu-checkbox').forEach(cb => {
            if (cb.id === 'menu_dashboard') {
                cb.checked = true;
            } else {
                cb.checked = false;
            }
        });

        // Fill based on current access list
        if (user.access_list && user.access_list.length > 0) {
            user.access_list.forEach(acc => {
                if (acc.id_sub_branch === null) {
                    const branchCb = document.getElementById('check_branch_' + acc.id_branch);
                    if (branchCb) {
                        branchCb.checked = true;
                        // Checking parent branch should visually check children too
                        toggleBranchChildren(branchCb, acc.id_branch);
                    }
                } else {
                    const subCb = document.getElementById('check_sub_' + acc.id_sub_branch);
                    if (subCb) {
                        subCb.checked = true;
                    }
                }
            });
        }

        // Fill based on current menu access list
        if (user.menu_access_list && user.menu_access_list.length > 0) {
            user.menu_access_list.forEach(menuKey => {
                const menuCb = document.getElementById('menu_' + menuKey);
                if (menuCb) {
                    menuCb.checked = true;
                }
            });
        }

        updateAllAreasCheckbox();
        updateAllMenusCheckbox();

        toggleAccessSectionByRole(user.level);
        openModal('modalEditAccess');
    }

    // Toggle all operational area checkboxes
    function toggleAllAreas(allCb) {
        document.querySelectorAll('.branch-checkbox').forEach(cb => {
            cb.checked = allCb.checked;
        });
        document.querySelectorAll('.sub-checkbox').forEach(cb => {
            cb.checked = allCb.checked;
        });
    }

    // Toggle all menu checkboxes (excluding dashboard)
    function toggleAllMenus(allCb) {
        document.querySelectorAll('.menu-checkbox').forEach(cb => {
            if (cb.id !== 'menu_dashboard') {
                cb.checked = allCb.checked;
            }
        });
    }

    // Update "Pilih Semua" checkbox state for areas
    function updateAllAreasCheckbox() {
        const allAreasCb = document.getElementById('toggle_all_areas');
        if (!allAreasCb) return;
        const total = document.querySelectorAll('.branch-checkbox, .sub-checkbox').length;
        const checked = document.querySelectorAll('.branch-checkbox:checked, .sub-checkbox:checked').length;
        allAreasCb.checked = (total > 0 && total === checked);
    }

    // Update "Pilih Semua" checkbox state for menus
    function updateAllMenusCheckbox() {
        const allMenusCb = document.getElementById('toggle_all_menus');
        if (!allMenusCb) return;
        const total = document.querySelectorAll('.menu-checkbox:not(#menu_dashboard)').length;
        const checked = document.querySelectorAll('.menu-checkbox:not(#menu_dashboard):checked').length;
        allMenusCb.checked = (total > 0 && total === checked);
    }

    // Bind event listeners to menu checkboxes
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll('.menu-checkbox').forEach(cb => {
            if (cb.id !== 'menu_dashboard') {
                cb.addEventListener('change', updateAllMenusCheckbox);
            }
        });
    });

    // Control visibility of branch tree by role level
    function toggleAccessSectionByRole(role) {
        const branchSection = document.getElementById('access_tree_section');
        const menuSection = document.getElementById('access_menu_section');
        if (role === 'admin') {
            branchSection.style.display = 'none';
            menuSection.style.display = 'none';
        } else {
            branchSection.style.display = 'block';
            menuSection.style.display = 'block';
        }
    }

    // Toggle child sub-branches when checking parent branch
    function toggleBranchChildren(parentCb, branchId) {
        const children = document.querySelectorAll('.branch-' + branchId + '-child');
        children.forEach(cb => {
            cb.checked = parentCb.checked;
        });
    }

    // Ensure parent branch checkbox is unchecked if any child is unchecked
    function toggleChildInfluence(childCb, branchId) {
        if (!childCb.checked) {
            const parent = document.getElementById('check_branch_' + branchId);
            if (parent) parent.checked = false;
        }
    }

    // Click outside to close modals
    window.addEventListener('click', function(e) {
        document.querySelectorAll('.modal-backdrop').forEach(modal => {
            if (e.target === modal) {
                modal.classList.remove('show');
            }
        });
    });
</script>
@endsection
