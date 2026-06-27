@extends('layouts.admin')

@section('title', 'Broadcast Notifikasi')

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
        cursor: pointer;
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

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    .form-group {
        margin-bottom: 20px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .form-group label {
        font-size: 0.88rem;
        font-weight: 600;
        color: #334155;
    }

    .form-control {
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 12px 16px;
        font-size: 0.95rem;
        outline: none;
        width: 100%;
        transition: border 0.2s;
    }

    .form-control:focus {
        border-color: #4f46e5;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
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

    /* Checkbox list styling */
    .channel-box {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        background-color: #f8fafc;
        cursor: pointer;
        transition: all 0.2s;
    }

    .channel-box:hover {
        border-color: #4f46e5;
        background-color: #eff6ff;
    }

    .channel-box input {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    /* Modal styling */
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
        width: min(600px, 100%);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        border: 1px solid var(--border-color);
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
        font-size: 1.5rem;
        cursor: pointer;
        opacity: 0.8;
    }

    .modal-close:hover {
        opacity: 1;
    }

    .modal-body {
        padding: 24px;
    }

    /* Table Container Styling */
    .table-container {
        border: 1px solid var(--border-color);
        border-radius: 14px;
        overflow-x: auto;
        margin-top: 10px;
        max-height: 250px;
        overflow-y: auto;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .table th, .table td {
        padding: 10px 14px;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.85rem;
    }

    .table th {
        font-weight: 600;
        color: var(--text-gray);
        background-color: #f8fafc;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .table tr {
        transition: background-color 0.2s;
    }

    .table tr:hover {
        background-color: #f8fafc;
    }

    /* Var helpers list */
    .var-badge {
        background-color: #f1f5f9;
        color: #475569;
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid #cbd5e1;
        display: inline-block;
        user-select: none;
    }

    .var-badge:hover {
        background-color: #e0e7ff;
        color: #4f46e5;
        border-color: #a5b4fc;
    }
</style>
@endsection

@section('content')
<!-- Navigation Tab Bar -->
<div class="monitoring-tabs">
    <div class="monitoring-tab active" onclick="switchTab('tab-general')" id="btn-tab-general">
        <i class="fa-solid fa-bullhorn"></i>
        <span>Notifikasi Umum (Broadcast)</span>
    </div>
    <div class="monitoring-tab" onclick="switchTab('tab-odp')" id="btn-tab-odp">
        <i class="fa-solid fa-network-wired"></i>
        <span>Gangguan / Pemeliharaan ODP</span>
    </div>
    <div class="monitoring-tab" onclick="switchTab('tab-odc')" id="btn-tab-odc">
        <i class="fa-solid fa-circle-nodes"></i>
        <span>Gangguan / Pemeliharaan ODC</span>
    </div>
</div>

<!-- Tab CONTENT 1: Notifikasi Umum -->
<div class="tab-content active" id="tab-general">
    <div class="form-row-2-1" style="align-items: flex-start;">
        <!-- Left: Form Input -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fa-solid fa-message"></i>
                    <span>Buat Notifikasi Umum</span>
                </div>
            </div>
            <form action="{{ route('admin.broadcast.general') }}" method="POST" id="form_general">
                @csrf
                <div class="form-group">
                    <label>Pilih Media Pengiriman *</label>
                    <div class="form-row-2" style="gap: 14px;">
                        <label class="channel-box" for="channel_app">
                            <input type="checkbox" name="channels[]" value="app" id="channel_app" checked onchange="toggleTitleInput()">
                            <div>
                                <strong style="display:block; font-size:0.9rem; color:var(--text-dark);">Aplikasi Pelanggan</strong>
                                <span style="font-size:0.75rem; color:var(--text-gray);">Tampil di Portal Login Pelanggan</span>
                            </div>
                        </label>
                        <label class="channel-box" for="channel_wa">
                            <input type="checkbox" name="channels[]" value="wa" id="channel_wa">
                            <div>
                                <strong style="display:block; font-size:0.9rem; color:var(--text-dark);">WhatsApp (Massal)</strong>
                                <span style="font-size:0.75rem; color:var(--text-gray);">Kirim pesan WA ke semua pelanggan</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="form-group" id="title_group">
                    <label for="judul_informasi">Judul Pengumuman *</label>
                    <input type="text" name="judul" id="judul_informasi" class="form-control" placeholder="Contoh: Pemeliharaan Server Bulanan" required>
                </div>

                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <label for="pesan_informasi">Isi Pesan Notifikasi *</label>
                        <div style="display: flex; gap: 6px;">
                            <span class="var-badge" onclick="insertVar('pesan_informasi', '$nama')">+ Nama Pelanggan</span>
                        </div>
                    </div>
                    <textarea name="pesan" id="pesan_informasi" rows="8" class="form-control" placeholder="Tulis isi pengumuman atau pesan broadcast..." required style="resize: vertical; font-family: inherit; line-height: 1.5;"></textarea>
                    <small style="color:var(--text-gray); font-style:italic;">Variabel `$nama` akan diganti dengan nama pelanggan masing-masing saat dikirim via WhatsApp.</small>
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 10px;">
                    <button type="submit" class="btn btn-primary" style="padding: 12px 24px;">
                        <i class="fa-solid fa-paper-plane"></i> Kirim Notifikasi
                    </button>
                </div>
            </form>
        </div>

        <!-- Right: Recent Portal Announcements -->
        <div class="card">
            <div class="card-header" style="margin-bottom:12px;">
                <div class="card-title" style="font-size:1rem;">
                    <i class="fa-solid fa-history"></i>
                    <span>Pengumuman Portal Aktif</span>
                </div>
            </div>
            <div style="display:flex; flex-direction:column; gap:14px;">
                @forelse($announcements as $ann)
                    <div style="padding:14px; border:1px solid var(--border-color); border-radius:12px; background-color:#fafafa; position:relative;">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:8px;">
                            <div style="font-weight:700; color:var(--text-dark); font-size:0.9rem; margin-bottom:4px; padding-right:24px;">{{ $ann->judul_informasi }}</div>
                            <form action="{{ route('admin.broadcast.delete_announcement', $ann->id_informasi) }}" method="POST" onsubmit="return confirm('Hapus pengumuman ini dari portal pelanggan?')" style="position:absolute; right:12px; top:12px;">
                                @csrf
                                <button type="submit" style="background:none; border:none; color:#ef4444; cursor:pointer; padding:4px;" title="Hapus Pengumuman">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                        <div style="font-size:0.82rem; color:var(--text-gray); line-height:1.4; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden; margin-top:4px;">{{ $ann->isi_informasi }}</div>
                    </div>
                @empty
                    <div style="text-align:center; color:var(--text-gray); font-style:italic; padding:20px; font-size:0.85rem;">
                        Belum ada riwayat pengumuman di portal pelanggan.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Tab CONTENT 2: Notifikasi Per ODP -->
<div class="tab-content" id="tab-odp">
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-network-wired"></i>
                <span>Broadcast WA Pemeliharaan/Gangguan per ODP</span>
            </div>
        </div>
        <form action="{{ route('admin.broadcast.odp') }}" method="POST" id="form_odp">
            @csrf
            <div class="form-row-2" style="align-items: flex-start;">
                <!-- Left Form Inputs -->
                <div>
                    <div class="form-group">
                        <label for="select_odp">Pilih Perangkat ODP *</label>
                        <select name="id_odp" id="select_odp" class="form-control" style="padding: 10px 14px; height: 44px; border-radius: 12px;">
                            <option value="" selected disabled>-- Pilih ODP --</option>
                            @foreach($odps as $odp)
                                <option value="{{ $odp->id_odp }}">{{ $odp->nama_odp }} (Kapasitas: {{ $odp->port_odp }} Port)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <label for="odp_message_template">Pesan Gangguan/Maintenance *</label>
                            <div style="display: flex; gap: 6px;">
                                <span class="var-badge" onclick="insertVar('odp_message_template', '$nama')">+ Nama</span>
                                <span class="var-badge" onclick="insertVar('odp_message_template', '$odp')">+ ODP</span>
                            </div>
                        </div>
                        <textarea name="pesan" id="odp_message_template" rows="8" class="form-control" placeholder="Tulis isi pesan gangguan/maintenance..." required style="resize: vertical; font-family: inherit; line-height: 1.5;"></textarea>
                        <small style="color:var(--text-gray); font-style:italic;">Variabel `$nama` dan `$odp` akan digantikan secara otomatis pada pesan tujuan.</small>
                    </div>
                </div>

                <!-- Right Target Client List -->
                <div id="odp_clients_container" style="display: none;">
                    <label style="font-size: 0.88rem; font-weight: 600; color: #334155; display:block; margin-bottom:8px;">Klien Terkoneksi Pada ODP</label>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th width="8%" style="text-align: center;"><input type="checkbox" id="check_all_clients" checked></th>
                                    <th width="25%">Kode Klien</th>
                                    <th>Nama Pelanggan</th>
                                    <th width="30%">No. WhatsApp</th>
                                </tr>
                            </thead>
                            <tbody id="odp_clients_tbody">
                                <!-- Loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top: 16px; font-size:0.8rem; color:var(--text-gray);">
                        * Hanya pelanggan yang dicentang yang akan menerima pesan siaran/broadcast WhatsApp ini.
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; margin-top: 24px; border-top: 1px solid var(--border-color); padding-top:16px;">
                <button type="submit" class="btn btn-primary" id="btn_send_odp" disabled style="padding: 12px 24px;">
                    <i class="fa-solid fa-bullhorn"></i> Kirim Broadcast ODP
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tab CONTENT 3: Notifikasi Per ODC -->
<div class="tab-content" id="tab-odc">
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-circle-nodes"></i>
                <span>Broadcast WA Pemeliharaan/Gangguan per ODC</span>
            </div>
        </div>
        <form action="{{ route('admin.broadcast.odc') }}" method="POST" id="form_odc">
            @csrf
            <div class="form-row-2" style="align-items: flex-start;">
                <!-- Left Form Inputs -->
                <div>
                    <div class="form-group">
                        <label for="select_odc">Pilih Perangkat ODC *</label>
                        <select name="id_odc" id="select_odc" class="form-control" style="padding: 10px 14px; height: 44px; border-radius: 12px;">
                            <option value="" selected disabled>-- Pilih ODC --</option>
                            @foreach($odcs as $odc)
                                <option value="{{ $odc->id_odc }}">{{ $odc->nama_odc }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <label for="odc_message_template">Pesan Gangguan/Maintenance *</label>
                            <div style="display: flex; gap: 6px;">
                                <span class="var-badge" onclick="insertVar('odc_message_template', '$nama')">+ Nama</span>
                                <span class="var-badge" onclick="insertVar('odc_message_template', '$odp')">+ ODP</span>
                                <span class="var-badge" onclick="insertVar('odc_message_template', '$odc')">+ ODC</span>
                            </div>
                        </div>
                        <textarea name="pesan" id="odc_message_template" rows="8" class="form-control" placeholder="Tulis isi pesan gangguan/maintenance..." required style="resize: vertical; font-family: inherit; line-height: 1.5;"></textarea>
                        <small style="color:var(--text-gray); font-style:italic;">Variabel `$nama`, `$odp`, dan `$odc` akan digantikan secara otomatis pada pesan tujuan.</small>
                    </div>
                </div>

                <!-- Right Target Client List -->
                <div id="odc_clients_container" style="display: none; width: 100%;">
                    <label style="font-size: 0.88rem; font-weight: 600; color: #334155; display:block; margin-bottom:8px;">Klien Terkoneksi Pada ODC (Melalui ODP Terkait)</label>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th width="8%" style="text-align: center;"><input type="checkbox" id="check_all_odc_clients" checked></th>
                                    <th width="20%">Kode Klien</th>
                                    <th>Nama Pelanggan</th>
                                    <th width="20%">ODP</th>
                                    <th width="25%">No. WhatsApp</th>
                                </tr>
                            </thead>
                            <tbody id="odc_clients_tbody">
                                <!-- Loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top: 16px; font-size:0.8rem; color:var(--text-gray);">
                        * Hanya pelanggan yang dicentang yang akan menerima pesan siaran/broadcast WhatsApp ini.
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; margin-top: 24px; border-top: 1px solid var(--border-color); padding-top:16px;">
                <button type="submit" class="btn btn-primary" id="btn_send_odc" disabled style="padding: 12px 24px;">
                    <i class="fa-solid fa-bullhorn"></i> Kirim Broadcast ODC
                </button>
            </div>
        </form>
    </div>
</div>

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
@endsection

@section('scripts')
<script>
    function switchTab(tabId) {
        // Switch tab classes
        document.querySelectorAll('.monitoring-tab').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

        if (tabId === 'tab-general') {
            document.getElementById('btn-tab-general').classList.add('active');
            document.getElementById('tab-general').classList.add('active');
        } else if (tabId === 'tab-odp') {
            document.getElementById('btn-tab-odp').classList.add('active');
            document.getElementById('tab-odp').classList.add('active');
        } else {
            document.getElementById('btn-tab-odc').classList.add('active');
            document.getElementById('tab-odc').classList.add('active');
        }
    }

    function toggleTitleInput() {
        const appChecked = document.getElementById('channel_app').checked;
        const titleGroup = document.getElementById('title_group');
        const titleInput = document.getElementById('judul_informasi');

        if (appChecked) {
            titleGroup.style.display = 'flex';
            titleInput.required = true;
        } else {
            titleGroup.style.display = 'none';
            titleInput.required = false;
        }
    }

    function insertVar(textareaId, varName) {
        const textarea = document.getElementById(textareaId);
        if (!textarea) return;

        const startPos = textarea.selectionStart;
        const endPos = textarea.selectionEnd;
        const textVal = textarea.value;

        textarea.value = textVal.substring(0, startPos) + varName + textVal.substring(endPos, textVal.length);
        textarea.focus();
        textarea.selectionStart = startPos + varName.length;
        textarea.selectionEnd = startPos + varName.length;
    }

    document.addEventListener("DOMContentLoaded", function () {
        toggleTitleInput();

        // ODP dropdown trigger to load clients via AJAX
        const selectOdp = document.getElementById('select_odp');
        if (selectOdp) {
            selectOdp.addEventListener('change', function() {
                const id = this.value;
                if (!id) {
                    document.getElementById('odp_clients_container').style.display = 'none';
                    return;
                }
                
                // Show loading placeholder
                const tbody = document.getElementById('odp_clients_tbody');
                tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; padding:20px; color:var(--text-gray);"><i class="fa-solid fa-circle-notch fa-spin"></i> Memuat data pelanggan...</td></tr>`;
                document.getElementById('odp_clients_container').style.display = 'block';

                fetch(`{{ url('administrator/broadcast/odp-clients') }}/${id}`)
                    .then(response => response.json())
                    .then(data => {
                        tbody.innerHTML = '';
                        if (data.length === 0) {
                            tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; color:var(--text-gray); padding:20px; font-style:italic;">Tidak ada pelanggan yang menggunakan ODP ini.</td></tr>`;
                            document.getElementById('btn_send_odp').disabled = true;
                            return;
                        }
                        
                        document.getElementById('btn_send_odp').disabled = false;
                        data.forEach((client) => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td align="center"><input type="checkbox" name="client_ids[]" value="${client.id_pelanggan}" class="client-checkbox" checked></td>
                                <td>${client.kode_pelanggan}</td>
                                <td><strong>${client.nama_pelanggan}</strong></td>
                                <td>${client.no_telp || '-'}</td>
                            `;
                            tbody.appendChild(tr);
                        });

                        // Prefill text template
                        const odpName = selectOdp.options[selectOdp.selectedIndex].text.split(" (")[0];
                        document.getElementById('odp_message_template').value = `Pemberitahuan: Yth. pelanggan kami Bapak/Ibu $nama. Terkait pemeliharaan jaringan pada area ODP ${odpName}, koneksi internet Anda akan mengalami gangguan sementara. Pemeliharaan sedang ditangani oleh petugas kami. Mohon maaf atas ketidaknyamanannya.`;
                    })
                    .catch(err => {
                        tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; color:#dc2626; padding:20px;">Gagal memuat data pelanggan: ${err.message}</td></tr>`;
                    });
            });
        }

        // Table select all trigger
        const checkAll = document.getElementById('check_all_clients');
        if (checkAll) {
            checkAll.addEventListener('change', function() {
                const checked = this.checked;
                document.querySelectorAll('.client-checkbox').forEach(cb => {
                    cb.checked = checked;
                });
            });
        }

        // General submit intercept
        const formGeneral = document.getElementById('form_general');
        if (formGeneral) {
            formGeneral.addEventListener('submit', function(e) {
                const channels = Array.from(document.querySelectorAll('input[name="channels[]"]:checked')).map(el => el.value);
                if (channels.length === 0) {
                    e.preventDefault();
                    alert('Silakan pilih setidaknya satu media pengiriman (Aplikasi Login atau WhatsApp).');
                    return;
                }

                // If WhatsApp is checked, handle via AJAX Progress Modal
                if (channels.includes('wa')) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (!confirm('Kirim notifikasi broadcast ke SEMUA pelanggan via WhatsApp?')) return;

                    const formData = new FormData(this);
                    const data = {
                        channels: channels,
                        judul: formData.get('judul'),
                        pesan: formData.get('pesan')
                    };

                    triggerBroadcastAjax('{{ route("admin.broadcast.general") }}', data, 'Broadcast WhatsApp');
                }
            });
        }

        // ODP submit intercept
        const formOdp = document.getElementById('form_odp');
        if (formOdp) {
            formOdp.addEventListener('submit', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const clientIds = Array.from(document.querySelectorAll('.client-checkbox:checked')).map(el => el.value);
                if (clientIds.length === 0) {
                    alert('Silakan pilih setidaknya satu pelanggan.');
                    return;
                }

                if (!confirm('Kirim broadcast maintenance ke pelanggan ODP terpilih via WhatsApp?')) return;

                const idOdp = document.getElementById('select_odp').value;
                const pesan = document.getElementById('odp_message_template').value;

                const data = {
                    id_odp: idOdp,
                    pesan: pesan,
                    client_ids: clientIds
                };

                triggerBroadcastAjax('{{ route("admin.broadcast.odp") }}', data, 'ODP Maintenance Broadcast');
            });
        }

        // ODC dropdown trigger to load clients via AJAX
        const selectOdc = document.getElementById('select_odc');
        if (selectOdc) {
            selectOdc.addEventListener('change', function() {
                const id = this.value;
                if (!id) {
                    document.getElementById('odc_clients_container').style.display = 'none';
                    return;
                }
                
                // Show loading placeholder
                const tbody = document.getElementById('odc_clients_tbody');
                tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; padding:20px; color:var(--text-gray);"><i class="fa-solid fa-circle-notch fa-spin"></i> Memuat data pelanggan...</td></tr>`;
                document.getElementById('odc_clients_container').style.display = 'block';

                fetch(`{{ url('administrator/broadcast/odc-clients') }}/${id}`)
                    .then(response => response.json())
                    .then(data => {
                        tbody.innerHTML = '';
                        if (data.length === 0) {
                            tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; color:var(--text-gray); padding:20px; font-style:italic;">Tidak ada pelanggan yang terhubung ke ODC ini.</td></tr>`;
                            document.getElementById('btn_send_odc').disabled = true;
                            return;
                        }
                        
                        document.getElementById('btn_send_odc').disabled = false;
                        data.forEach((client) => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td align="center"><input type="checkbox" name="client_ids[]" value="${client.id_pelanggan}" class="odc-client-checkbox" checked></td>
                                <td>${client.kode_pelanggan}</td>
                                <td><strong>${client.nama_pelanggan}</strong></td>
                                <td><span class="badge" style="background-color: #f1f5f9; color: #475569; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem;">${client.nama_odp}</span></td>
                                <td>${client.no_telp || '-'}</td>
                            `;
                            tbody.appendChild(tr);
                        });

                        // Prefill text template
                        const odcName = selectOdc.options[selectOdc.selectedIndex].text;
                        document.getElementById('odc_message_template').value = `Pemberitahuan: Yhat. pelanggan Bapak/Ibu $nama. Terkait adanya perbaikan/pemeliharaan jaringan pada perangkat utama ODC ${odcName}, saat ini koneksi internet Anda melalui ODP $odp mengalami gangguan sementara. Petugas kami sedang berupaya melakukan perbaikan secepatnya. Mohon maaf atas ketidaknyamanannya.`;
                    })
                    .catch(err => {
                        tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; color:#dc2626; padding:20px;">Gagal memuat data pelanggan: ${err.message}</td></tr>`;
                    });
            });
        }

        // ODC Table select all trigger
        const checkAllOdc = document.getElementById('check_all_odc_clients');
        if (checkAllOdc) {
            checkAllOdc.addEventListener('change', function() {
                const checked = this.checked;
                document.querySelectorAll('.odc-client-checkbox').forEach(cb => {
                    cb.checked = checked;
                });
            });
        }

        // ODC submit intercept
        const formOdc = document.getElementById('form_odc');
        if (formOdc) {
            formOdc.addEventListener('submit', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const clientIds = Array.from(document.querySelectorAll('.odc-client-checkbox:checked')).map(el => el.value);
                if (clientIds.length === 0) {
                    alert('Silakan pilih setidaknya satu pelanggan.');
                    return;
                }

                if (!confirm('Kirim broadcast maintenance ke pelanggan ODC terpilih via WhatsApp?')) return;

                const idOdc = document.getElementById('select_odc').value;
                const pesan = document.getElementById('odc_message_template').value;

                const data = {
                    id_odc: idOdc,
                    pesan: pesan,
                    client_ids: clientIds
                };

                triggerBroadcastAjax('{{ route("admin.broadcast.odc") }}', data, 'ODC Maintenance Broadcast');
            });
        }
    });

    function triggerBroadcastAjax(url, payload, title) {
        const modal = document.getElementById('broadcastProgressModal');
        const modalCloseBtn = document.getElementById('broadcastModalCloseBtn');
        const statusHeader = document.getElementById('broadcastStatusHeader');
        const progressBar = document.getElementById('broadcastProgressBar');
        const resultsList = document.getElementById('broadcastResultsList');
        const tutupBtn = document.getElementById('broadcastModalTutupBtn');
        const titleText = document.getElementById('broadcastModalTitle');

        modal.classList.add('active');
        titleText.textContent = title;
        modalCloseBtn.style.display = 'none';
        tutupBtn.style.display = 'none';
        progressBar.style.width = '0%';
        progressBar.style.backgroundColor = '#4f46e5';

        statusHeader.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin" style="color: #4f46e5; font-size: 1.2rem;"></i><span>Sedang memproses pengiriman WhatsApp...</span>';
        resultsList.innerHTML = '<div style="color: var(--text-gray); font-style: italic; text-align: center; padding: 20px;">Memulai koneksi ke server, mohon tunggu...</div>';

        let progress = 10;
        const progressInterval = setInterval(() => {
            if (progress < 90) {
                progress += 3;
                progressBar.style.width = progress + '%';
            }
        }, 800);

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify(payload)
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
            if (data.success) {
                progressBar.style.width = '100%';
                progressBar.style.backgroundColor = '#22c55e';
                statusHeader.innerHTML = `<i class="fa-solid fa-circle-check" style="color:#22c55e; font-size:1.25rem;"></i><span>Selesai! Berhasil: ${data.berhasil}, Gagal: ${data.gagal}</span>`;

                resultsList.innerHTML = '';
                if (data.results.length === 0) {
                    resultsList.innerHTML = '<div style="color:var(--text-gray); font-style:italic; padding:10px; text-align:center;">Tidak ada pesan yang dikirim (tidak ada penerima valid).</div>';
                } else {
                    data.results.forEach(res => {
                        const div = document.createElement('div');
                        div.style.display = 'flex';
                        div.style.justifyContent = 'space-between';
                        div.style.fontSize = '0.85rem';
                        div.style.padding = '4px 0';
                        div.style.borderBottom = '1px solid #f1f5f9';

                        if (res.status) {
                            div.innerHTML = `
                                <span><strong>${res.nama}</strong> (${res.no_telp})</span>
                                <span style="color:#16a34a; font-weight:600;"><i class="fa-solid fa-check-double"></i> Terkirim</span>
                            `;
                        } else {
                            div.innerHTML = `
                                <span><strong>${res.nama}</strong> (${res.no_telp})</span>
                                <span style="color:#dc2626; font-weight:600;"><i class="fa-solid fa-triangle-exclamation"></i> Gagal: ${res.message}</span>
                            `;
                        }
                        resultsList.appendChild(div);
                    });
                }
            } else {
                progressBar.style.width = '100%';
                progressBar.style.backgroundColor = '#dc2626';
                statusHeader.innerHTML = `<i class="fa-solid fa-circle-xmark" style="color:#dc2626; font-size:1.25rem;"></i><span>Gagal: ${data.message}</span>`;
                resultsList.innerHTML = `<div style="color:#dc2626; font-weight:600; padding:10px;">Error: ${data.message}</div>`;
            }

            modalCloseBtn.style.display = 'block';
            tutupBtn.style.display = 'block';
        })
        .catch(err => {
            clearInterval(progressInterval);
            progressBar.style.width = '100%';
            progressBar.style.backgroundColor = '#dc2626';
            statusHeader.innerHTML = `<i class="fa-solid fa-circle-xmark" style="color:#dc2626; font-size:1.25rem;"></i><span>Terjadi kesalahan sistem</span>`;
            
            let errMsg = err.message || 'Error tidak diketahui';
            if (errMsg.includes('<html') || errMsg.includes('<!DOCTYPE')) {
                errMsg = 'Sesi Anda telah kedaluwarsa atau token keamanan tidak cocok (Error 419 / 500). Silakan muat ulang halaman ini dan coba lagi.';
            }
            resultsList.innerHTML = `<div style="color:#dc2626; font-weight:600; padding:10px;">Error: ${errMsg}</div>`;

            modalCloseBtn.style.display = 'block';
            tutupBtn.style.display = 'block';
        });
    }

    function closeBroadcastProgressModal() {
        document.getElementById('broadcastProgressModal').classList.remove('active');
        window.location.reload();
    }
</script>
@endsection
