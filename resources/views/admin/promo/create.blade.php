@extends('layouts.admin')

@section('title', 'Tambah Promo Baru')

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

    .form-group {
        margin-bottom: 20px;
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
        background-color: white;
    }

    .form-control:focus {
        border-color: #4f46e5;
    }

    .form-control[readonly] {
        background-color: #f1f5f9;
        color: #64748b;
        cursor: not-allowed;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    @media (max-width: 640px) {
        .form-row {
            grid-template-columns: 1fr;
        }
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

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-8px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection

@section('content')
<div class="card" style="max-width: 700px; margin: 0 auto;">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-tags"></i>
            <span>Tambah Promo Pelanggan Baru</span>
        </div>
        <a href="{{ route('admin.promo.index') }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div style="padding: 24px;">
        @if($errors->any())
            <div style="background-color: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; padding: 12px 16px; border-radius: 12px; margin-bottom: 20px;">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('admin.promo.store') }}" method="POST" id="promoForm">
            @csrf

            <!-- Nama Promo -->
            <div class="form-group">
                <label for="nama_promo">Nama Promo</label>
                <input type="text" name="nama_promo" id="nama_promo" class="form-control" placeholder="Contoh: Promo Merdeka 3 Bulan / Promo Cashback" value="{{ old('nama_promo') }}" required>
            </div>

            <!-- Pilih Pelanggan (Searchable Select) -->
            <div class="form-group">
                <label>Nama Pelanggan</label>
                <input type="hidden" name="id_pelanggan" id="id_pelanggan" value="{{ old('id_pelanggan') }}" required>
                
                <div class="custom-select-container" id="custom_pelanggan_select">
                    <div class="custom-select-trigger" id="custom_select_trigger">
                        <span id="custom_select_text">-- Pilih Pelanggan --</span>
                        <i class="fa-solid fa-chevron-down" style="font-size: 0.8rem; color: var(--text-gray);"></i>
                    </div>
                    <div class="custom-select-dropdown">
                        <div class="custom-select-search-wrapper">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" id="search_pelanggan" class="form-control custom-select-search-input" placeholder="Cari nama atau kode pelanggan..." autocomplete="off">
                        </div>
                        <div class="custom-select-options" id="custom_select_options">
                            <div class="custom-select-option selected" data-value="" data-text="-- Pilih Pelanggan --" data-paket-name="-" data-paket-harga="0">
                                -- Pilih Pelanggan --
                            </div>
                            @foreach($pelanggan as $p)
                                <div class="custom-select-option" 
                                     data-value="{{ $p->id_pelanggan }}" 
                                     data-text="{{ $p->nama_pelanggan }} ({{ $p->kode_pelanggan }})"
                                     data-paket-name="{{ $p->paketDetail->nama_paket ?? '-' }}"
                                     data-paket-harga="{{ $p->paketDetail->harga ?? 0 }}">
                                    <strong>{{ $p->nama_pelanggan }}</strong> <span style="font-family: monospace; font-size:0.78rem; color: var(--text-gray);">({{ $p->kode_pelanggan }})</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Paket yang Diambil (Readonly) -->
            <div class="form-group">
                <label for="paket_nama">Paket Yang Diambil (Sesuai Data Master Pelanggan)</label>
                <input type="text" id="paket_nama" class="form-control" value="-" readonly>
            </div>

            <!-- Periode Mulai Promo -->
            <div class="form-row">
                <div class="form-group">
                    <label for="mulai_bulan">Mulai Bulan</label>
                    <select name="mulai_bulan" id="mulai_bulan" class="form-control" required style="height: auto; padding: 10px 14px;">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ old('mulai_bulan', date('n')) == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(date('Y'), $m, 1)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="mulai_tahun">Mulai Tahun</label>
                    <select name="mulai_tahun" id="mulai_tahun" class="form-control" required style="height: auto; padding: 10px 14px;">
                        @foreach(range(date('Y'), date('Y') + 3) as $y)
                            <option value="{{ $y }}" {{ old('mulai_tahun', date('Y')) == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Periode Selesai Promo -->
            <div class="form-row">
                <div class="form-group">
                    <label for="selesai_bulan">Sampai Bulan</label>
                    <select name="selesai_bulan" id="selesai_bulan" class="form-control" required style="height: auto; padding: 10px 14px;">
                        @php
                            $nextMonth = date('n') == 12 ? 12 : date('n') + 1;
                        @endphp
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ old('selesai_bulan', $nextMonth) == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(date('Y'), $m, 1)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="selesai_tahun">Sampai Tahun</label>
                    <select name="selesai_tahun" id="selesai_tahun" class="form-control" required style="height: auto; padding: 10px 14px;">
                        @foreach(range(date('Y'), date('Y') + 3) as $y)
                            <option value="{{ $y }}" {{ old('selesai_tahun', date('Y')) == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Input Tagihan Awal -->
            <div class="form-group">
                <label for="nominal_tagihan">Input Tagihan Awal (Rp)</label>
                <input type="number" name="nominal_tagihan" id="nominal_tagihan" class="form-control" placeholder="Masukkan jumlah nominal pembayaran awal, misal: 100000" value="{{ old('nominal_tagihan') }}" required>
                <small style="color:var(--text-gray); font-size:0.78rem; margin-top:2px;">
                    * Tagihan awal ini akan masuk sebagai tagihan belum lunas untuk bulan pertama promo dimulai (atau bulan berikutnya jika bulan pertama tersebut sudah lunas).
                </small>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 30px;">
                <a href="{{ route('admin.promo.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Promo
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const selectContainer = document.getElementById("custom_pelanggan_select");
        const selectTrigger = document.getElementById("custom_select_trigger");
        const selectText = document.getElementById("custom_select_text");
        const hiddenInput = document.getElementById("id_pelanggan");
        const searchInput = document.getElementById("search_pelanggan");
        const optionsList = document.getElementById("custom_select_options");
        const optionElements = Array.from(optionsList.querySelectorAll(".custom-select-option"));
        
        const paketNameInput = document.getElementById("paket_nama");
        const nominalTagihanInput = document.getElementById("nominal_tagihan");
        const form = document.getElementById("promoForm");

        // Toggle custom dropdown
        selectTrigger.addEventListener("click", function (e) {
            e.stopPropagation();
            selectContainer.classList.toggle("active");
            if (selectContainer.classList.contains("active")) {
                searchInput.focus();
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener("click", function () {
            selectContainer.classList.remove("active");
        });

        // Search options
        searchInput.addEventListener("click", function (e) {
            e.stopPropagation();
        });

        searchInput.addEventListener("input", function () {
            const query = this.value.toLowerCase().trim();
            optionElements.forEach((opt, index) => {
                if (index === 0) {
                    opt.style.display = "block"; // Always show placeholder
                    return;
                }
                const text = opt.getAttribute("data-text").toLowerCase();
                if (text.includes(query)) {
                    opt.style.display = "block";
                } else {
                    opt.style.display = "none";
                }
            });
        });

        // Select option
        optionElements.forEach(opt => {
            opt.addEventListener("click", function () {
                const val = this.getAttribute("data-value");
                const text = this.getAttribute("data-text");
                const paketName = this.getAttribute("data-paket-name");
                const paketHarga = this.getAttribute("data-paket-harga");

                // Set values
                hiddenInput.value = val;
                selectText.textContent = text;
                paketNameInput.value = paketName;
                
                if (paketHarga > 0) {
                    nominalTagihanInput.value = paketHarga;
                } else {
                    nominalTagihanInput.value = "";
                }

                // Update selected class
                optionElements.forEach(el => el.classList.remove("selected"));
                this.classList.add("selected");

                selectContainer.classList.remove("active");
            });
        });

        // Confirm save popup
        form.addEventListener("submit", function (e) {
            if (!hiddenInput.value) {
                alert("Harap pilih pelanggan terlebih dahulu.");
                e.preventDefault();
                return;
            }

            const namaPelangganText = selectText.textContent;
            const namaPromo = document.getElementById("nama_promo").value;
            const nominal = parseFloat(nominalTagihanInput.value) || 0;
            
            const formatter = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0
            });

            const confirmMessage = `Apakah Anda (Admin/NOC) sudah yakin dengan data berikut?\n\n` +
                                   `- Nama Promo: ${namaPromo}\n` +
                                   `- Pelanggan: ${namaPelangganText}\n` +
                                   `- Paket: ${paketNameInput.value}\n` +
                                   `- Tagihan Awal Promo: ${formatter.format(nominal)}\n\n` +
                                   `Menekan OK akan mendaftarkan promo, membuat tagihan awal berstatus Lunas, dan langsung mengirimkan tagihan WhatsApp ke pelanggan.`;

            if (!confirm(confirmMessage)) {
                e.preventDefault();
            }
        });

        // Restore values if validation failed (old data)
        const oldVal = hiddenInput.value;
        if (oldVal) {
            const selectedOpt = optionElements.find(opt => opt.getAttribute("data-value") === oldVal);
            if (selectedOpt) {
                selectedOpt.click();
            }
        }
    });
</script>
@endsection
