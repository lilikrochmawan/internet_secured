@extends('layouts.admin')

@section('title', 'Client Isolir & Non-Aktif')

@section('styles')
<style>
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
        padding: 8px 14px;
        font-size: 0.9rem;
        outline: none;
        background-color: white;
    }

    .grid-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: 20px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: var(--shadow-sm);
    }

    .stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        color: white;
    }

    .icon-indigo { background: var(--primary-gradient); }
    .icon-red { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
    .icon-slate { background: linear-gradient(135deg, #64748b 0%, #475569 100%); }

    .stat-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .stat-title {
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--text-gray);
        letter-spacing: 0.5px;
    }

    .stat-value {
        font-family: 'Outfit', sans-serif;
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--text-dark);
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

    .badge-terisolir {
        background-color: #fee2e2;
        color: #991b1b;
    }

    .badge-tidak-aktif {
        background-color: #f1f5f9;
        color: #475569;
    }

    .clients-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .clients-table th, .clients-table td {
        padding: 12px 14px;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.88rem;
    }

    .clients-table th {
        background-color: #f8fafc;
        font-weight: 600;
        color: var(--text-gray);
        text-align: left;
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        border: none;
    }

    .btn-success-outline {
        background: transparent;
        border: 1px solid #22c55e;
        color: #16a34a;
    }

    .btn-success-outline:hover {
        background-color: #22c55e;
        color: white;
    }

    .btn-info-outline {
        background: transparent;
        border: 1px solid #3b82f6;
        color: #2563eb;
    }

    .btn-info-outline:hover {
        background-color: #3b82f6;
        color: white;
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

    .btn-action i {
        font-size: 0.95rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">

    <!-- Device Selector if multiple devices exist -->
    @if(isset($mikrotik_devices) && $mikrotik_devices->count() > 1)
        <div class="device-filter">
            <span style="font-weight: 600; color: var(--text-dark); font-size: 0.95rem;">
                <i class="fa-solid fa-server" style="color: #4f46e5; margin-right: 4px;"></i> Pilih Router MikroTik:
            </span>
            <form method="GET" action="{{ route('admin.teknisi.clients') }}" id="deviceForm">
                <select name="device_id" class="form-control" onchange="document.getElementById('deviceForm').submit()" style="height: 40px; border-radius: 10px;">
                    @foreach($mikrotik_devices as $dev)
                        <option value="{{ $dev->id_mikrotik }}" {{ $selected_device_id == $dev->id_mikrotik ? 'selected' : '' }}>
                            {{ $dev->nama_mikrotik }} ({{ $dev->ip }})
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    @endif

    @if($connected)
        @php
            $countTerisolir = count(array_filter($clientsList, function($c) { return $c['status'] == 'terisolir'; }));
            $countTidakAktif = count(array_filter($clientsList, function($c) { return $c['status'] == 'tidak_aktif'; }));
            $countTotal = count($clientsList);
        @endphp

        <!-- Stats Grid -->
        <div class="grid-stats">
            <div class="stat-card">
                <div class="stat-icon icon-indigo">
                    <i class="fa-solid fa-users-slash"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-title">Total Off / Isolir</span>
                    <span class="stat-value">{{ $countTotal }} Client</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon icon-red">
                    <i class="fa-solid fa-ban"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-title">Total Terisolir</span>
                    <span class="stat-value">{{ $countTerisolir }} Client</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon icon-slate">
                    <i class="fa-solid fa-circle-notch"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-title">Total Tidak Aktif (Off)</span>
                    <span class="stat-value">{{ $countTidakAktif }} Client</span>
                </div>
            </div>
        </div>

        <!-- Main Listing Card -->
        <div class="card">
            <!-- Table Controls & Header -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:12px;">
                <div style="font-family:'Outfit', sans-serif; font-size:1.05rem; font-weight:700; color:var(--text-dark); display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-users-viewfinder" style="color:#4f46e5;"></i>
                    <span>Daftar Client Non-Aktif & Terisolir</span>
                </div>
                
                <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                    <!-- Filter Status -->
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-gray);">Status:</span>
                        <select id="statusFilter" class="form-control" style="padding: 4px 8px; height: 38px; border-radius: 10px; font-size: 0.85rem; width: auto; margin: 0;">
                            <option value="semua" selected>Semua</option>
                            <option value="terisolir">Terisolir</option>
                            <option value="tidak_aktif">Tidak Aktif</option>
                        </select>
                    </div>

                    <!-- Filter Rows Limit -->
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-gray);">Tampilkan:</span>
                        <select id="tableLimit" class="form-control" style="padding: 4px 8px; height: 38px; border-radius: 10px; font-size: 0.85rem; width: auto; margin: 0;">
                            <option value="10" selected>10 Baris</option>
                            <option value="25">25 Baris</option>
                            <option value="50">50 Baris</option>
                            <option value="100">100 Baris</option>
                        </select>
                    </div>

                    <!-- Search Input -->
                    <div style="position:relative; min-width:240px;">
                        <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--text-gray); font-size:0.85rem;"></i>
                        <input type="text" id="tableSearch" class="form-control" placeholder="Cari nama, username, ODP..." style="padding-left:32px; height:38px; border-radius:10px; width:100%;">
                    </div>
                </div>
            </div>

            <!-- Table Container -->
            <div style="overflow-x: auto;" class="table-container">
                <table class="clients-table" id="teknisiClientsTable">
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Username PPPoE</th>
                            <th>Nama Pelanggan</th>
                            <th>Alamat</th>
                            <th>No. Telepon</th>
                            <th>IP Address</th>
                            <th>ODP</th>
                            <th>Status</th>
                            <th style="width: 180px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="teknisiClientsTableBody">
                        @forelse($clientsList as $index => $client)
                            <tr data-status="{{ $client['status'] }}">
                                <td>{{ $index + 1 }}</td>
                                <td><strong>{{ $client['username'] }}</strong></td>
                                <td>{{ $client['nama_pelanggan'] }}</td>
                                <td><span style="font-size: 0.82rem; color: var(--text-gray);">{{ $client['alamat'] }}</span></td>
                                <td><code>{{ $client['no_telp'] }}</code></td>
                                <td><code>{{ $client['ip_address'] }}</code></td>
                                <td>
                                    @if(!empty($client['odp']) && $client['odp'] !== '-')
                                        @if(!empty($client['odp_location']))
                                            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode(trim($client['odp_location'])) }}" target="_blank" class="badge" style="background-color:#e0f2fe; color:#0369a1; text-decoration:none;" title="Buka Lokasi ODP di Google Maps">
                                                <i class="fa-solid fa-diagram-project" style="font-size: 0.7rem;"></i> {{ $client['odp'] }}
                                            </a>
                                        @else
                                            <span class="badge" style="background-color:#e0f2fe; color:#0369a1;" title="Lokasi ODP tidak diset">
                                                <i class="fa-solid fa-diagram-project" style="font-size: 0.7rem;"></i> {{ $client['odp'] }}
                                            </span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($client['status'] == 'terisolir')
                                        <span class="badge badge-terisolir">
                                            <i class="fa-solid fa-circle" style="font-size:0.5rem; color:#ef4444;"></i> Terisolir
                                        </span>
                                    @else
                                        <span class="badge badge-tidak-aktif">
                                            <i class="fa-solid fa-circle-notch" style="font-size:0.5rem; color:#64748b;"></i> Tidak Aktif
                                        </span>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    <div style="display:inline-flex; gap:6px; justify-content:center;">
                                        @if(!empty($client['no_telp']) && $client['no_telp'] !== '-')
                                            @php
                                                $cleanPhone = preg_replace('/[^0-9]/', '', $client['no_telp']);
                                                if (str_starts_with($cleanPhone, '0')) {
                                                    $cleanPhone = '62' . substr($cleanPhone, 1);
                                                } elseif (str_starts_with($cleanPhone, '8')) {
                                                    $cleanPhone = '62' . $cleanPhone;
                                                }
                                            @endphp
                                            <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="btn-action btn-success-outline" title="Hubungi via WhatsApp">
                                                <i class="fa-brands fa-whatsapp"></i> Chat
                                            </a>
                                        @else
                                            <button class="btn-action btn-success-outline" style="opacity: 0.4; cursor: not-allowed;" disabled>
                                                <i class="fa-brands fa-whatsapp"></i> Chat
                                            </button>
                                        @endif

                                        @if(!empty($client['location']))
                                            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode(trim($client['location'])) }}" target="_blank" class="btn-action btn-info-outline" title="Buka Lokasi di Google Maps">
                                                <i class="fa-solid fa-map-location-dot"></i> Map
                                            </a>
                                        @else
                                            <button class="btn-action btn-info-outline" style="opacity: 0.4; cursor: not-allowed;" disabled title="Lokasi tidak diset">
                                                <i class="fa-solid fa-map-location-dot"></i> Map
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="empty-state">
                                <td colspan="9" style="text-align: center; color: var(--text-gray); padding: 40px 20px;">
                                    <i class="fa-solid fa-circle-check" style="font-size: 2.5rem; color: #10b981; margin-bottom: 12px; display: block;"></i>
                                    <strong>Hebat! Tidak ada client terisolir atau tidak aktif saat ini.</strong>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Container -->
            <div id="tablePagination" class="pagination-wrapper"></div>
        </div>
    @else
        <!-- Connection Failure Display -->
        <div class="card" style="text-align: center; padding: 50px 20px;">
            <i class="fa-solid fa-triangle-exclamation" style="font-size: 3.5rem; color: #ef4444; margin-bottom: 20px;"></i>
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.4rem; font-weight: 700; color: var(--text-dark); margin-bottom: 10px;">
                Gagal Membaca Status Client
            </h3>
            <p style="color: var(--text-gray); max-width: 500px; margin: 0 auto 24px;">
                {{ $error ?? 'Koneksi ke Router MikroTik API terputus. Pastikan IP, Port, API Service, dan Kredensial sudah benar.' }}
            </p>
            <a href="{{ route('admin.dashboard') }}" class="btn-action btn-primary-gradient" style="padding: 10px 20px;">
                <i class="fa-solid fa-house"></i> Kembali ke Dashboard
            </a>
        </div>
    @endif

</div>

@if($connected && count($clientsList) > 0)
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const table = document.getElementById("teknisiClientsTable");
        const tbody = document.getElementById("teknisiClientsTableBody");
        const searchInput = document.getElementById("tableSearch");
        const statusFilter = document.getElementById("statusFilter");
        const limitSelect = document.getElementById("tableLimit");
        const paginationContainer = document.getElementById("tablePagination");

        if (!table || !tbody || !paginationContainer) return;

        const allRows = Array.from(tbody.querySelectorAll("tr:not(.empty-state)"));
        let filteredRows = [...allRows];
        let currentPage = 1;

        function filterAndPaginate() {
            const searchQuery = searchInput.value.toLowerCase().trim();
            const selectedStatus = statusFilter.value;
            const limit = parseInt(limitSelect.value) || 10;

            // 1. Filter rows by search input and status dropdown
            filteredRows = allRows.filter(row => {
                const textMatch = Array.from(row.cells).slice(1, 7).some(cell => 
                    cell.textContent.toLowerCase().includes(searchQuery)
                );
                
                const rowStatus = row.getAttribute("data-status");
                const statusMatch = (selectedStatus === "semua" || rowStatus === selectedStatus);

                return textMatch && statusMatch;
            });

            // Handle empty search results state
            let emptyStateRow = tbody.querySelector(".empty-search-state");
            if (filteredRows.length === 0) {
                if (!emptyStateRow) {
                    emptyStateRow = document.createElement("tr");
                    emptyStateRow.className = "empty-search-state";
                    emptyStateRow.innerHTML = `
                        <td colspan="9" style="text-align: center; color: var(--text-gray); padding: 30px;">
                            Tidak ada client yang cocok dengan filter pencarian Anda.
                        </td>
                    `;
                    tbody.appendChild(emptyStateRow);
                }
                // Hide normal empty state
                const origEmptyState = tbody.querySelector(".empty-state");
                if (origEmptyState) origEmptyState.style.display = "none";
            } else {
                if (emptyStateRow) emptyStateRow.remove();
            }

            // 2. Paginate filtered rows
            const totalItems = filteredRows.length;
            const totalPages = Math.ceil(totalItems / limit) || 1;

            if (currentPage > totalPages) currentPage = totalPages;
            if (currentPage < 1) currentPage = 1;

            const startIndex = (currentPage - 1) * limit;
            const endIndex = startIndex + limit;

            // Hide all rows first
            allRows.forEach(row => row.style.display = "none");

            // Show and number only the filtered rows for the current page
            filteredRows.forEach((row, index) => {
                if (index >= startIndex && index < endIndex) {
                    row.style.display = "";
                    row.cells[0].textContent = index + 1; // update visual row index
                } else {
                    row.style.display = "none";
                }
            });

            // 3. Render Pagination Buttons
            renderPaginationControls(totalPages);
        }

        function renderPaginationControls(totalPages) {
            paginationContainer.innerHTML = "";

            if (totalPages <= 1) return;

            // Prev Button
            const prevBtn = document.createElement("button");
            prevBtn.className = "page-btn";
            prevBtn.disabled = (currentPage === 1);
            prevBtn.innerHTML = '<i class="fa-solid fa-chevron-left"></i>';
            prevBtn.addEventListener("click", () => {
                if (currentPage > 1) {
                    currentPage--;
                    filterAndPaginate();
                }
            });
            paginationContainer.appendChild(prevBtn);

            // Page Buttons
            for (let i = 1; i <= totalPages; i++) {
                if (totalPages > 6) {
                    // Show ellipsis logic for large page counts
                    if (i !== 1 && i !== totalPages && Math.abs(i - currentPage) > 1) {
                        if (i === 2 || i === totalPages - 1) {
                            const ellipsis = document.createElement("span");
                            ellipsis.className = "page-ellipsis";
                            ellipsis.textContent = "...";
                            paginationContainer.appendChild(ellipsis);
                        }
                        continue;
                    }
                }

                const pageBtn = document.createElement("button");
                pageBtn.className = `page-btn ${i === currentPage ? "active" : ""}`;
                pageBtn.textContent = i;
                pageBtn.addEventListener("click", () => {
                    currentPage = i;
                    filterAndPaginate();
                });
                paginationContainer.appendChild(pageBtn);
            }

            // Next Button
            const nextBtn = document.createElement("button");
            nextBtn.className = "page-btn";
            nextBtn.disabled = (currentPage === totalPages);
            nextBtn.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';
            nextBtn.addEventListener("click", () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    filterAndPaginate();
                }
            });
            paginationContainer.appendChild(nextBtn);
        }

        // Attach event listeners for real-time reactivity
        searchInput.addEventListener("input", () => {
            currentPage = 1;
            filterAndPaginate();
        });

        statusFilter.addEventListener("change", () => {
            currentPage = 1;
            filterAndPaginate();
        });

        limitSelect.addEventListener("change", () => {
            currentPage = 1;
            filterAndPaginate();
        });

        // Initial render
        filterAndPaginate();
    });
</script>
@endif
@endsection
