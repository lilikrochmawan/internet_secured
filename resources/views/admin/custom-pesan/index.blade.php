@extends('layouts.admin')

@section('title', 'Custom Pesan WhatsApp Templates')

@section('styles')
<style>
    .grid-forms {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
        gap: 24px;
        margin-top: 15px;
    }

    @media (max-width: 768px) {
        .grid-forms {
            grid-template-columns: 1fr;
        }
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-bottom: 14px;
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
        background-color: white;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        text-decoration: none;
    }

    .btn-primary {
        background: var(--primary-gradient);
        color: white;
    }
    .btn-primary:hover {
        opacity: 0.9;
    }

    .btn-info {
        background-color: #eff6ff;
        color: #2563eb;
    }
    .btn-info:hover {
        background-color: #dbeafe;
    }

    .card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 10px;
    }

    .helper-box {
        background-color: #f8fafc;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 12px 14px;
        font-size: 0.8rem;
        color: var(--text-gray);
        display: none;
        margin-top: 10px;
        animation: fadeIn 0.3s ease;
    }

    .helper-box.show {
        display: block;
    }

    .helper-tag {
        background-color: #e2e8f0;
        color: #334155;
        padding: 2px 6px;
        border-radius: 4px;
        font-family: monospace;
        font-weight: 600;
    }
</style>
@endsection

@section('content')
<div class="grid-forms">
    <!-- 1. Notifikasi Tagihan Otomatis -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-receipt"></i>
                <span>Notifikasi Tagihan Bulanan</span>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.custom_pesan.notif') }}">
            @csrf
            <div class="form-group">
                <label for="notif_status">Status Notifikasi</label>
                <select name="status" id="notif_status" class="form-control">
                    <option value="aktif" {{ ($notif->status_notifikasi ?? 'aktif') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="tidakaktif" {{ ($notif->status_notifikasi ?? 'aktif') === 'tidakaktif' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
            </div>
            <div class="form-group">
                <label for="pesan_notifikasi">Isi Pesan Notifikasi</label>
                <textarea name="pesan_notifikasi" id="pesan_notifikasi" rows="7" class="form-control" placeholder="Tulis format notifikasi tagihan bulanan...">{{ $notif->pesan_notifikasi ?? '' }}</textarea>
            </div>
            
            <div class="helper-box" id="help-notif">
                <p style="margin-bottom:6px; font-weight: 600; color:var(--text-dark);">Variabel yang tersedia:</p>
                <ul style="list-style: none; display:flex; flex-direction:column; gap:4px;">
                    <li><span class="helper-tag">$nama</span> : Nama Pelanggan</li>
                    <li><span class="helper-tag">$no_telp</span> : No Telepon Pelanggan</li>
                    <li><span class="helper-tag">$jatuh_tempo</span> : Tanggal Jatuh Tempo</li>
                    <li><span class="helper-tag">$tagihan</span> : Jumlah Nominal Tagihan</li>
                    <li><span class="helper-tag">$hari_ini</span> : Tanggal Hari Ini</li>
                </ul>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan
                </button>
                <button type="button" class="btn btn-info btn-help" data-target="help-notif">
                    <i class="fa-solid fa-circle-question"></i> Format Tulisan
                </button>
            </div>
        </form>
    </div>

    <!-- Reminder Tagihan -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-bell"></i>
                <span>Reminder Tagihan</span>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.custom_pesan.reminder') }}">
            @csrf
            <div class="form-group">
                <label for="reminder_status">Status Reminder</label>
                <select name="status_reminder" id="reminder_status" class="form-control">
                    <option value="aktif" {{ ($reminder->status_reminder ?? 'aktif') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="tidakaktif" {{ ($reminder->status_reminder ?? 'aktif') === 'tidakaktif' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
            </div>
            <div class="form-group">
                <label for="pesan_reminder">Isi Pesan Reminder</label>
                <textarea name="pesan_reminder" id="pesan_reminder" rows="7" class="form-control" placeholder="Tulis format reminder tagihan...">{{ $reminder->pesan_reminder ?? '' }}</textarea>
            </div>
            
            <div class="helper-box" id="help-reminder">
                <p style="margin-bottom:6px; font-weight: 600; color:var(--text-dark);">Variabel yang tersedia:</p>
                <ul style="list-style: none; display:flex; flex-direction:column; gap:4px;">
                    <li><span class="helper-tag">$nama</span> : Nama Pelanggan</li>
                    <li><span class="helper-tag">$no_telp</span> : No Telepon Pelanggan</li>
                    <li><span class="helper-tag">$tagihan</span> : Nominal Tagihan</li>
                    <li><span class="helper-tag">$jatuh_tempo</span> : Tanggal Jatuh Tempo</li>
                    <li><span class="helper-tag">$sekarang_format</span> : Waktu Sekarang (Format WIB)</li>
                </ul>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan
                </button>
                <button type="button" class="btn btn-info btn-help" data-target="help-reminder">
                    <i class="fa-solid fa-circle-question"></i> Format Tulisan
                </button>
            </div>
        </form>
    </div>

    <!-- 2. Blokir / Isolir Otomatis -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-ban"></i>
                <span>Isolir / Blokir Otomatis</span>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.custom_pesan.blokir') }}">
            @csrf
            <div class="form-group">
                <label for="blokir_status">Status Blokir</label>
                <select name="status_blokir" id="blokir_status" class="form-control">
                    <option value="aktif" {{ ($blokir->status_blokir ?? 'aktif') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="tidakaktif" {{ ($blokir->status_blokir ?? 'aktif') === 'tidakaktif' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
            </div>



            <div class="form-group">
                <label for="pesan_blokir">Isi Pesan Isolir Otomatis</label>
                <textarea name="pesan_blokir" id="pesan_blokir" rows="5" class="form-control" placeholder="Tulis format notifikasi isolir otomatis...">{{ $blokir->pesan_blokir ?? '' }}</textarea>
            </div>

            <div class="helper-box" id="help-blokir">
                <p style="margin-bottom:6px; font-weight: 600; color:var(--text-dark);">Variabel yang tersedia:</p>
                <ul style="list-style: none; display:flex; flex-direction:column; gap:4px;">
                    <li><span class="helper-tag">$nama</span> : Nama Pelanggan</li>
                    <li><span class="helper-tag">$no_telp</span> : No Telepon Pelanggan</li>
                    <li><span class="helper-tag">$tagihan</span> : Nominal Tagihan</li>
                    <li><span class="helper-tag">$jatuh_tempo</span> : Tanggal Jatuh Tempo</li>
                    <li><span class="helper-tag">$sekarang_format</span> : Waktu Sekarang (Format WIB)</li>
                </ul>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan
                </button>
                <button type="button" class="btn btn-info btn-help" data-target="help-blokir">
                    <i class="fa-solid fa-circle-question"></i> Format Tulisan
                </button>
            </div>
        </form>
    </div>

    <!-- 3. Notifikasi Pembayaran (Receipt) -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-receipt"></i>
                <span>Notifikasi Pembayaran (Kuitansi)</span>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.custom_pesan.bayar') }}">
            @csrf
            <div class="form-group">
                <label for="pesan_bayar">Pesan Bukti Pembayaran</label>
                <textarea name="pesan_bayar" id="pesan_bayar" rows="7" class="form-control" placeholder="Tulis format notifikasi bukti pembayaran...">{{ $notifbayar->pesan_bayar ?? '' }}</textarea>
            </div>

            <div class="helper-box" id="help-bayar">
                <p style="margin-bottom:6px; font-weight: 600; color:var(--text-dark);">Variabel yang tersedia:</p>
                <ul style="list-style: none; display:flex; flex-direction:column; gap:4px;">
                    <li><span class="helper-tag">$nama</span> : Nama Pelanggan</li>
                    <li><span class="helper-tag">$tagihan</span> : Jumlah Pembayaran</li>
                    <li><span class="helper-tag">$no_telp</span> : Nomor Telepon</li>
                    <li><span class="helper-tag">$harinin</span> : Hari / Waktu Pembayaran Saat Ini</li>
                </ul>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan
                </button>
                <button type="button" class="btn btn-info btn-help" data-target="help-bayar">
                    <i class="fa-solid fa-circle-question"></i> Format Tulisan
                </button>
            </div>
        </form>
    </div>

    <!-- 4. Notifikasi Buka Blokir (Unblock Alert) -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-unlock-keyhole"></i>
                <span>Notifikasi Buka Blokir (Re-active)</span>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.custom_pesan.bukablokir') }}">
            @csrf
            <div class="form-group">
                <label for="pesan_bukablokir">Pesan Re-aktifasi Akses</label>
                <textarea name="pesan_bukablokir" id="pesan_bukablokir" rows="7" class="form-control" placeholder="Tulis format notifikasi unblock...">{{ $bukablokir->pesan_bukablokir ?? '' }}</textarea>
            </div>

            <div class="helper-box" id="help-bukablokir">
                <p style="margin-bottom:6px; font-weight: 600; color:var(--text-dark);">Variabel yang tersedia:</p>
                <ul style="list-style: none; display:flex; flex-direction:column; gap:4px;">
                    <li><span class="helper-tag">$nama</span> : Nama Pelanggan</li>
                    <li><span class="helper-tag">$no_telp</span> : Nomor Telepon</li>
                    <li><span class="helper-tag">$harinin</span> : Waktu Pengaktifan Kembali</li>
                </ul>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan
                </button>
                <button type="button" class="btn btn-info btn-help" data-target="help-bukablokir">
                    <i class="fa-solid fa-circle-question"></i> Format Tulisan
                </button>
            </div>
        </form>
    </div>

    <!-- Promo WhatsApp Notifikasi -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-tags"></i>
                <span>Notifikasi Promo Baru</span>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.custom_pesan.promo') }}">
            @csrf
            <div class="form-group">
                <label for="promo_status">Status Notifikasi</label>
                <select name="status_promo" id="promo_status" class="form-control">
                    <option value="aktif" {{ ($promo->status_promo ?? 'aktif') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="tidakaktif" {{ ($promo->status_promo ?? 'aktif') === 'tidakaktif' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
            </div>
            <div class="form-group">
                <label for="pesan_promo">Isi Pesan Promo</label>
                <textarea name="pesan_promo" id="pesan_promo" rows="7" class="form-control" placeholder="Tulis format notifikasi promo...">{{ $promo->pesan_promo ?? '' }}</textarea>
            </div>
            
            <div class="helper-box" id="help-promo">
                <p style="margin-bottom:6px; font-weight: 600; color:var(--text-dark);">Variabel yang tersedia:</p>
                <ul style="list-style: none; display:flex; flex-direction:column; gap:4px;">
                    <li><span class="helper-tag">$nama</span> : Nama Pelanggan</li>
                    <li><span class="helper-tag">$no_telp</span> : No Telepon Pelanggan</li>
                    <li><span class="helper-tag">$nama_promo</span> : Nama Promo</li>
                    <li><span class="helper-tag">$tagihan</span> : Nominal Tagihan Awal</li>
                    <li><span class="helper-tag">$mulai_promo</span> : Bulan Mulai Promo</li>
                    <li><span class="helper-tag">$selesai_promo</span> : Bulan Selesai Promo</li>
                </ul>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan
                </button>
                <button type="button" class="btn btn-info btn-help" data-target="help-promo">
                    <i class="fa-solid fa-circle-question"></i> Format Tulisan
                </button>
            </div>
        </form>
    </div>

    <!-- 5. Notifikasi Awal Pemasangan -->
    <div class="card" style="margin-bottom: 0; grid-column: 1 / -1;">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-circle-check"></i>
                <span>Notifikasi Awal Pemasangan (Registrasi)</span>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.custom_pesan.pemasangan') }}">
            @csrf
            <div class="form-row-1-2">
                <div>
                    <div class="form-group">
                        <label for="pemasangan_status">Status Notifikasi</label>
                        <select name="status_npemasangan" id="pemasangan_status" class="form-control">
                            <option value="aktif" {{ ($pemasangan->status_notif ?? 'aktif') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="tidak" {{ ($pemasangan->status_notif ?? 'aktif') === 'tidak' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>

                    <div class="helper-box show" style="display:block; margin-top:10px;">
                        <p style="margin-bottom:6px; font-weight: 600; color:var(--text-dark);">Variabel yang tersedia:</p>
                        <ul style="list-style: none; display:flex; flex-direction:column; gap:4px;">
                            <li><span class="helper-tag">$nama</span> : Nama Pelanggan</li>
                            <li><span class="helper-tag">$alamat</span> : Alamat Pemasangan</li>
                            <li><span class="helper-tag">$no_telp</span> : No Telepon Pelanggan</li>
                            <li><span class="helper-tag">$paket</span> : Nama Paket Internet</li>
                            <li><span class="helper-tag">$tgl_pemasangan</span> : Tanggal Pemasangan</li>
                            <li><span class="helper-tag">$username</span> : Username Akun Pelanggan</li>
                            <li><span class="helper-tag">$password</span> : Password Akun Pelanggan</li>
                        </ul>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label for="pesan_npemasangan">Isi Pesan Pemasangan</label>
                    <textarea name="pesan_npemasangan" id="pesan_npemasangan" rows="9" class="form-control" placeholder="Tulis format notifikasi pemasangan...">{{ $pemasangan->pesan_notif ?? '' }}</textarea>
                    
                    <button type="submit" class="btn btn-primary" style="margin-top: 14px; align-self: flex-start;">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Pemasangan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".btn-help").forEach(btn => {
            btn.addEventListener("click", function () {
                const targetId = this.getAttribute("data-target");
                const targetEl = document.getElementById(targetId);
                if (targetEl) {
                    targetEl.classList.toggle("show");
                }
            });
        });
    });
</script>
@endsection
