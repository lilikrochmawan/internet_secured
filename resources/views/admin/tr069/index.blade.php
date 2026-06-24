@extends('layouts.admin')

@section('title', 'TR-069 ACS Management')

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

    .btn-xs {
        padding: 4px 8px;
        font-size: 0.75rem;
        border-radius: 6px;
    }

    .btn-primary {
        background: var(--primary-gradient);
        color: white;
    }
    .btn-primary:hover {
        opacity: 0.9;
    }

    .btn-warning {
        background-color: #fff7ed;
        color: #ea580c;
    }
    .btn-warning:hover {
        background-color: #ffedd5;
    }

    .btn-danger {
        background-color: #fef2f2;
        color: #dc2626;
    }
    .btn-danger:hover {
        background-color: #fee2e2;
    }

    .btn-default {
        background-color: #f1f5f9;
        color: #475569;
    }
    .btn-default:hover {
        background-color: #e2e8f0;
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

    .badge-success {
        background-color: #dcfce7;
        color: #15803d;
    }

    .badge-danger {
        background-color: #fef2f2;
        color: #dc2626;
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

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .form-group label {
        font-weight: 600;
        font-size: 0.85rem;
    }

    .form-control {
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 8px 14px;
        font-size: 0.9rem;
        outline: none;
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
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-server"></i>
            <span>Daftar Perangkat TR-069 CPE (ONT)</span>
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
                <input type="text" id="tableSearch" class="form-control" placeholder="Cari CPE..." style="padding-left:36px; height:40px; border-radius:10px; width:100%;">
            </div>
        </div>
    </div>

    <div class="table-container">
        <table class="table" id="acsTable">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Serial Number</th>
                    <th>Produsen / Model</th>
                    <th>IP Address</th>
                    <th>Redaman</th>
                    <th>PPPoE</th>
                    <th>Versi Software</th>
                    <th>Last Inform</th>
                    <th>Status</th>
                    <th>Pelanggan</th>
                    <th style="width: 180px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cpes as $index => $row)
                    @php
                        $lastInform = strtotime($row->last_inform);
                        $timeDiff = time() - $lastInform;
                        $pppoeActive = !empty($row->pppoe_status) && in_array(strtolower($row->pppoe_status), ['connected', 'up']);
                        $online = ($timeDiff <= 900) || $pppoeActive;
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><strong>{{ $row->serial_number }}</strong></td>
                        <td>{{ $row->manufacturer }} / {{ $row->product_class }}</td>
                        <td><code>{{ $row->ip_address }}</code></td>
                        <td>
                            @if(!empty($row->rx_power))
                                @php
                                    $numericPower = (float) filter_var($row->rx_power, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                                    $colorStyle = 'background-color:#dcfce7; color:#15803d;';
                                    if ($numericPower < -27 || $numericPower > -8) {
                                        $colorStyle = 'background-color:#fef2f2; color:#dc2626;';
                                    } elseif ($numericPower < -24) {
                                        $colorStyle = 'background-color:#fff7ed; color:#ea580c;';
                                    }
                                @endphp
                                <span class="badge" style="{{ $colorStyle }} padding: 2px 6px; font-size:0.75rem; border-radius:4px; text-transform:none;">
                                    {{ $row->rx_power }}
                                </span>
                            @else
                                <span style="color: var(--text-gray); font-style: italic; font-size:0.8rem;">-</span>
                            @endif
                        </td>
                        <td>
                            @if(!empty($row->pppoe_username) || !empty($row->pppoe_status))
                                @if(!empty($row->pppoe_username))
                                    <code style="font-size:0.8rem;">{{ $row->pppoe_username }}</code>
                                @else
                                    <span style="color: var(--text-gray); font-style: italic; font-size:0.75rem;">(Username Kosong)</span>
                                @endif
                                @if(!empty($row->pppoe_status) && (strtolower($row->pppoe_status) === 'connected' || strtolower($row->pppoe_status) === 'up'))
                                    <span style="color:#15803d; font-weight:bold; font-size:0.75rem; margin-left:2px;" title="Connected">🟢</span>
                                @endif
                            @else
                                <span style="color: var(--text-gray); font-style: italic; font-size:0.8rem;">-</span>
                            @endif
                        </td>
                        <td>{{ $row->software_version }}</td>
                        <td>{{ $row->last_inform }}</td>
                        <td>
                            @if($online)
                                <span class="badge badge-success">🟢 Online</span>
                            @else
                                <span class="badge badge-danger">🔴 Offline</span>
                            @endif
                        </td>
                        <td>
                            @if(!empty($row->nama_pelanggan))
                                <span>{{ $row->nama_pelanggan }}</span>
                                <form action="{{ route('admin.tr069.unlink') }}" method="POST" style="display:inline-block; margin-left:6px;" onsubmit="return confirm('Apakah Anda yakin ingin melepas hubungan perangkat ini?')">
                                    @csrf
                                    <input type="hidden" name="id_cpe" value="{{ $row->id_cpe }}">
                                    <button type="submit" class="btn btn-xs btn-danger" title="Lepas Hubungan" style="padding: 2px 6px;">
                                        <i class="fa-solid fa-unlink"></i>
                                    </button>
                                </form>
                            @else
                                <button class="btn btn-xs btn-warning btn-link-cpe" 
                                        data-id="{{ $row->id_cpe }}" 
                                        data-serial="{{ $row->serial_number }}">
                                    <i class="fa-solid fa-link"></i> Hubungkan
                                </button>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <div style="display:inline-flex; gap:6px; justify-content:center;">
                                <a href="{{ route('admin.tr069.detail', $row->id_cpe) }}" class="btn btn-xs btn-primary" title="Kelola CPE">
                                    <i class="fa-solid fa-gears"></i> Kelola CPE
                                </a>
                                <form action="{{ route('admin.tr069.destroy') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus perangkat CPE dengan Serial Number {{ $row->serial_number }}?')">
                                    @csrf
                                    <input type="hidden" name="id_cpe" value="{{ $row->id_cpe }}">
                                    <button type="submit" class="btn btn-xs btn-danger" title="Hapus Perangkat">
                                        <i class="fa-solid fa-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" style="text-align: center; color: var(--text-gray); padding: 30px;">
                            Tidak ada perangkat TR-069 CPE yang terdeteksi di database.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div id="acsPagination"></div>
</div>

<!-- Modal Hubungkan Pelanggan -->
<div class="modal-backdrop" id="modalLinkCustomer">
    <div class="modal-content">
        <div class="modal-header">
            <span class="modal-title">Hubungkan CPE ke Pelanggan</span>
            <button class="modal-close" id="btnCloseModal">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.tr069.link') }}">
            @csrf
            <input type="hidden" name="id_cpe" id="modal_id_cpe">
            
            <div class="form-group" style="margin-bottom: 12px;">
                <label>Serial Number</label>
                <input type="text" class="form-control" id="modal_serial" readonly style="background-color: #f8fafc; font-weight: bold;">
            </div>
            
            <div class="form-group" style="margin-bottom: 16px;">
                <label>Pilih Pelanggan</label>
                <!-- Hidden input to store selected customer ID -->
                <input type="hidden" name="id_pelanggan" id="id_pelanggan" required>
                
                <!-- Custom Searchable Dropdown for Pelanggan -->
                <div class="custom-select-container" id="custom_pelanggan_select">
                    <div class="custom-select-trigger" id="custom_pelanggan_trigger">
                        <span id="custom_pelanggan_text">-- Pilih Pelanggan --</span>
                        <i class="fa-solid fa-chevron-down" style="font-size: 0.8rem; color: var(--text-gray);"></i>
                    </div>
                    <div class="custom-select-dropdown" id="custom_pelanggan_dropdown">
                        <div class="custom-select-search-wrapper">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" id="search_pelanggan" class="form-control custom-select-search-input" placeholder="Cari pelanggan..." autocomplete="off">
                        </div>
                        <div class="custom-select-options" id="custom_pelanggan_options">
                            <div class="custom-select-option selected" data-value="" data-text="-- Pilih Pelanggan --">
                                -- Pilih Pelanggan --
                            </div>
                            @foreach($pelanggan as $plg)
                                <div class="custom-select-option" data-value="{{ $plg->id_pelanggan }}" data-text="{{ $plg->nama_pelanggan }} ({{ $plg->no_telp }})">
                                    <strong>{{ $plg->nama_pelanggan }}</strong> <span style="font-size:0.78rem; color: var(--text-gray);">({{ $plg->no_telp }})</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            
            <div style="display:flex; justify-content: flex-end; gap:8px;">
                <button type="button" class="btn btn-default" id="btnCancelModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Hubungan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Initialize table pagination
        setupTablePagination("#acsTable", "#acsPagination", "#tableLimit", "#tableSearch");

        const modal = document.getElementById("modalLinkCustomer");
        const btnClose = document.getElementById("btnCloseModal");
        const btnCancel = document.getElementById("btnCancelModal");
        const modalIdCpe = document.getElementById("modal_id_cpe");
        const modalSerial = document.getElementById("modal_serial");

        const pelangganSelectContainer = document.getElementById('custom_pelanggan_select');
        const hiddenPelangganInput = document.getElementById('id_pelanggan');
        const triggerPelangganText = document.getElementById('custom_pelanggan_text');
        const optionsPelangganList = document.getElementById('custom_pelanggan_options');
        const searchPelangganInput = document.getElementById('search_pelanggan');

        document.querySelectorAll(".btn-link-cpe").forEach(btn => {
            btn.addEventListener("click", function () {
                const id = this.getAttribute("data-id");
                const serial = this.getAttribute("data-serial");
                
                modalIdCpe.value = id;
                modalSerial.value = serial;

                // Reset custom select dropdown to initial state
                if (hiddenPelangganInput) hiddenPelangganInput.value = '';
                if (triggerPelangganText) triggerPelangganText.textContent = '-- Pilih Pelanggan --';
                if (optionsPelangganList) {
                    const optionElements = optionsPelangganList.querySelectorAll('.custom-select-option');
                    optionElements.forEach(el => el.classList.remove('selected'));
                    if (optionElements[0]) optionElements[0].classList.add('selected');
                }
                
                modal.classList.add("show");
            });
        });

        // Searchable dropdown logic
        if (pelangganSelectContainer && hiddenPelangganInput && triggerPelangganText && optionsPelangganList && searchPelangganInput) {
            // Toggle dropdown
            document.getElementById('custom_pelanggan_trigger').addEventListener('click', function(e) {
                e.stopPropagation();
                pelangganSelectContainer.classList.toggle('active');
                if (pelangganSelectContainer.classList.contains('active')) {
                    searchPelangganInput.value = '';
                    searchPelangganInput.dispatchEvent(new Event('input'));
                    searchPelangganInput.focus();
                }
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!pelangganSelectContainer.contains(e.target)) {
                    pelangganSelectContainer.classList.remove('active');
                }
            });

            // Option selection using event delegation
            optionsPelangganList.onclick = function(e) {
                const opt = e.target.closest('.custom-select-option');
                if (!opt) return;

                const val = opt.getAttribute('data-value');
                const text = opt.getAttribute('data-text');

                hiddenPelangganInput.value = val;
                triggerPelangganText.textContent = text;

                // Update selected styling
                const optionElements = optionsPelangganList.querySelectorAll('.custom-select-option');
                optionElements.forEach(el => el.classList.remove('selected'));
                opt.classList.add('selected');

                pelangganSelectContainer.classList.remove('active');
            };

            // Filter on search input
            searchPelangganInput.oninput = function() {
                const query = this.value.toLowerCase().trim();
                const optionElements = optionsPelangganList.querySelectorAll('.custom-select-option');

                optionElements.forEach((opt, index) => {
                    if (index === 0) {
                        opt.style.display = 'block'; // Always show placeholder
                        return;
                    }
                    const text = opt.getAttribute('data-text').toLowerCase();
                    if (text.includes(query)) {
                        opt.style.display = 'block';
                    } else {
                        opt.style.display = 'none';
                    }
                });
            };

            // Prevent closing dropdown when clicking search input
            searchPelangganInput.onclick = function(e) {
                e.stopPropagation();
            };
        }

        function closeModal() {
            modal.classList.remove("show");
        }

        btnClose.addEventListener("click", closeModal);
        btnCancel.addEventListener("click", closeModal);

        // Click outside modal to close
        modal.addEventListener("click", function (e) {
            if (e.target === modal) {
                closeModal();
            }
        });
    });
</script>
@endsection
