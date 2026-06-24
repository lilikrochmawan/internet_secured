@extends('layouts.admin')

@section('title', 'Kelola User PPPoE')

@section('styles')
<style>
    .monitoring-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 24px;
        background-color: white;
        padding: 8px;
        border-radius: 16px;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
        flex-wrap: wrap;
    }

    .monitoring-tab {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 12px;
        color: var(--text-gray);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }

    .monitoring-tab:hover {
        background-color: #f1f5f9;
        color: var(--text-dark);
    }

    .monitoring-tab.active {
        background: var(--primary-gradient);
        color: white;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);
    }

    .device-filter {
        background-color: white;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 16px 20px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .form-control {
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 10px 14px;
        font-size: 0.9rem;
        outline: none;
        background-color: white;
        width: 100%;
        box-sizing: border-box;
    }

    .form-control:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 9999px;
        font-size: 0.78rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .badge-aktif {
        background-color: #d1fae5;
        color: #065f46;
    }

    .badge-nonaktif {
        background-color: #fee2e2;
        color: #991b1b;
    }

    .secrets-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .secrets-table th, .secrets-table td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.9rem;
    }

    .secrets-table th {
        background-color: #f8fafc;
        font-weight: 600;
        color: var(--text-gray);
        text-align: left;
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        border: none;
    }

    .btn-primary-gradient {
        background: var(--primary-gradient);
        color: white;
        box-shadow: 0 4px 10px rgba(79, 70, 229, 0.15);
    }

    .btn-primary-gradient:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }

    .btn-warning-outline {
        background: transparent;
        border: 1px solid #f59e0b;
        color: #f59e0b;
    }

    .btn-warning-outline:hover {
        background-color: #f59e0b;
        color: white;
    }

    .btn-danger-outline {
        background: transparent;
        border: 1px solid #ef4444;
        color: #ef4444;
    }

    .btn-danger-outline:hover {
        background-color: #ef4444;
        color: white;
    }

    /* Modal Styles */
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(4px);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    .modal.show {
        display: flex;
        opacity: 1;
    }

    .modal-content {
        background-color: white;
        border-radius: 20px;
        width: 100%;
        max-width: 500px;
        padding: 28px;
        box-shadow: var(--shadow-lg);
        animation: modalSlide 0.2s ease;
        position: relative;
    }

    @keyframes modalSlide {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .modal-title {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 1.2rem;
        color: var(--text-dark);
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 1.25rem;
        color: var(--text-gray);
        cursor: pointer;
        transition: color 0.2s;
    }

    .modal-close:hover {
        color: #ef4444;
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
        color: var(--text-dark);
    }

    .btn-submit {
        background: var(--primary-gradient);
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        width: 100%;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);
        transition: all 0.2s;
        margin-top: 10px;
    }

    .btn-submit:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }
</style>
@endsection

@section('content')
<!-- Filter Perangkat -->
<div class="device-filter">
    <form method="GET" action="{{ route('admin.monitoring.pppoe') }}" style="display:flex; align-items:center; gap:10px; width:100%; flex-wrap:wrap;">
        <label for="device_id" style="font-weight:600; font-size:0.9rem;">Pilih Perangkat Mikrotik:</label>
        <select name="device_id" id="device_id" class="form-control" onchange="this.form.submit()" style="min-width: 250px; max-width: 100%;">
            @foreach($mikrotik_devices as $dev)
                <option value="{{ $dev->id_mikrotik }}" {{ $selected_device_id == $dev->id_mikrotik ? 'selected' : '' }}>
                    {{ $dev->nama_mikrotik ?: $dev->ip }} ({{ $dev->ip }})
                </option>
            @endforeach
        </select>
    </form>
</div>

<!-- Tab Navigasi Monitoring -->
<div class="monitoring-tabs">
    <a href="{{ route('admin.monitoring.index', ['device_id' => $selected_device_id]) }}" class="monitoring-tab">
        <i class="fa-solid fa-chart-line"></i> Traffic & Log Realtime
    </a>
    <a href="{{ route('admin.monitoring.active', ['device_id' => $selected_device_id]) }}" class="monitoring-tab">
        <i class="fa-solid fa-users"></i> Client Aktif & Remote ONT
    </a>
    <a href="{{ route('admin.monitoring.pppoe', ['device_id' => $selected_device_id]) }}" class="monitoring-tab active">
        <i class="fa-solid fa-user-shield"></i> User PPPoE
    </a>
    <a href="{{ route('admin.monitoring.profiles', ['device_id' => $selected_device_id]) }}" class="monitoring-tab">
        <i class="fa-solid fa-folder-tree"></i> Profil PPPoE
    </a>
</div>

@if(!$connected)
    <div class="card" style="border-left: 5px solid #ef4444;">
        <div style="display:flex; gap:16px; align-items:center;">
            <i class="fa-solid fa-circle-exclamation" style="font-size: 2.5rem; color:#ef4444;"></i>
            <div>
                <h3 style="color:#ef4444;">Koneksi MikroTik Gagal</h3>
                <p style="margin-top:4px; color:var(--text-gray);">{{ $error }}</p>
            </div>
        </div>
    </div>
@else
    <!-- Card Utama -->
    <div class="card">
        <!-- Search and Action -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
            <div style="font-family:'Outfit', sans-serif; font-size:1.1rem; font-weight:700; color:var(--text-dark); display:flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-user-shield" style="color:#4f46e5;"></i>
                <span>Pengelolaan User / Secret PPPoE</span>
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
                    <input type="text" id="tableSearch" class="form-control" placeholder="Cari user..." style="padding-left:36px; height:40px; border-radius:10px;">
                </div>
                
                <button type="button" class="btn-action btn-primary-gradient" onclick="openAddModal()">
                    <i class="fa-solid fa-plus"></i> Tambah User PPPoE
                </button>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="secrets-table" id="secretsTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Username</th>
                        <th>Password</th>
                        <th>Profile</th>
                        <th>Local IP</th>
                        <th>Remote IP</th>
                        <th>Status</th>
                        <th style="width: 200px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="secretsTableBody">
                    @forelse($secrets as $index => $sec)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><strong>{{ $sec['name'] ?? '-' }}</strong></td>
                            <td><code>{{ $sec['password'] ?? '-' }}</code></td>
                            <td><span style="background-color:#eff6ff; color:#1e40af; padding:4px 10px; border-radius:8px; font-weight:600; font-size:0.8rem;">{{ $sec['profile'] ?? '-' }}</span></td>
                            <td>{{ $sec['local-address'] ?? '-' }}</td>
                            <td><code>{{ $sec['remote-address'] ?? '-' }}</code></td>
                            <td>
                                @if(($sec['disabled'] ?? 'false') == 'true')
                                    <span class="badge badge-nonaktif">Nonaktif</span>
                                @else
                                    <span class="badge badge-aktif">Aktif</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <div style="display:inline-flex; gap:8px; justify-content:center;">
                                    <!-- Edit Button -->
                                    <button type="button" 
                                            class="btn-action btn-warning-outline" 
                                            onclick="openEditModal({
                                                name: '{{ $sec['name'] ?? '' }}',
                                                password: '{{ $sec['password'] ?? '' }}',
                                                profile: '{{ $sec['profile'] ?? '' }}',
                                                local_address: '{{ $sec['local-address'] ?? '' }}',
                                                remote_address: '{{ $sec['remote-address'] ?? '' }}'
                                            })"
                                            title="Edit User">
                                        <i class="fa-solid fa-edit"></i> Edit
                                    </button>

                                    <!-- Delete Button -->
                                    <form method="POST" action="{{ route('admin.monitoring.pppoe.delete') }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user PPPoE {{ $sec['name'] }}?')">
                                        @csrf
                                        <input type="hidden" name="device_id" value="{{ $selected_device_id }}">
                                        <input type="hidden" name="id" value="{{ $sec['.id'] }}">
                                        <button type="submit" class="btn-action btn-danger-outline" title="Hapus User">
                                            <i class="fa-solid fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--text-gray); padding:30px;">
                                Tidak ada data user PPPoE di perangkat Mikrotik ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div id="secretsPagination"></div>
    </div>
@endif

<!-- Add / Edit Modal -->
<div class="modal" id="secretModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Tambah User PPPoE Baru</h3>
            <button class="modal-close" onclick="closeModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        
        <form method="POST" action="{{ route('admin.monitoring.pppoe.store') }}">
            @csrf
            <input type="hidden" name="device_id" value="{{ $selected_device_id }}">
            <input type="hidden" name="old_name" id="old_name" value="">

            <div class="form-group">
                <label for="name">Username PPPoE <span style="color:#ef4444;">*</span></label>
                <input type="text" name="name" id="name" class="form-control" placeholder="Contoh: pelanggan_lulu" required>
            </div>

            <div class="form-group">
                <label for="password">Password <span style="color:#ef4444;">*</span></label>
                <input type="text" name="password" id="password" class="form-control" placeholder="Contoh: pass123" required>
            </div>

            <div class="form-group">
                <label for="profile">PPPoE Profile <span style="color:#ef4444;">*</span></label>
                <select name="profile" id="profile" class="form-control" required>
                    <option value="">-- Pilih Profile --</option>
                    @foreach($profiles as $prof)
                        <option value="{{ $prof['name'] }}">{{ $prof['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="local_address">Local IP Address (Optional)</label>
                <input type="text" name="local_address" id="local_address" class="form-control" placeholder="Contoh: 10.10.10.1">
            </div>

            <div class="form-group">
                <label for="remote_address">Remote IP Address (Optional / Static IP)</label>
                <input type="text" name="remote_address" id="remote_address" class="form-control" placeholder="Contoh: 10.10.10.100">
            </div>

            <button type="submit" class="btn-submit">Simpan User PPPoE</button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
@if($connected)
<script>
    const modal = document.getElementById("secretModal");
    const modalTitle = document.getElementById("modalTitle");
    const oldNameInput = document.getElementById("old_name");
    
    const nameInput = document.getElementById("name");
    const passwordInput = document.getElementById("password");
    const profileInput = document.getElementById("profile");
    const localInput = document.getElementById("local_address");
    const remoteInput = document.getElementById("remote_address");

    function openAddModal() {
        modalTitle.textContent = "Tambah User PPPoE Baru";
        oldNameInput.value = "";
        
        // Clear fields
        nameInput.value = "";
        passwordInput.value = "";
        profileInput.value = "";
        localInput.value = "";
        remoteInput.value = "";
        
        modal.classList.add("show");
    }

    function openEditModal(data) {
        modalTitle.textContent = "Edit User PPPoE";
        oldNameInput.value = data.name;
        
        // Populate fields
        nameInput.value = data.name;
        passwordInput.value = data.password;
        profileInput.value = data.profile;
        localInput.value = data.local_address;
        remoteInput.value = data.remote_address;
        
        modal.classList.add("show");
    }

    function closeModal() {
        modal.classList.remove("show");
    }

    // Close modal when clicking outside
    window.addEventListener("click", function(event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.addEventListener("DOMContentLoaded", function () {
        // Setup pagination & filtering
        setupTablePagination("#secretsTable", "#secretsPagination", "#tableLimit", "#tableSearch");
    });
</script>
@endif
@endsection
