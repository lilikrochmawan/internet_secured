@extends('layouts.admin')

@section('title', 'Pengaturan Umum')

@section('styles')
<style>
    .settings-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
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

    .btn-default {
        background-color: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
    }
    .btn-default:hover {
        background-color: #e2e8f0;
    }

    .btn-xs {
        padding: 4px 8px;
        font-size: 0.72rem;
        border-radius: 6px;
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

    .backup-banner {
        background-color: #faf5ff;
        border: 1px solid #e9d5ff;
        border-radius: 20px;
        padding: 24px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    }

    .backup-info {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .backup-icon {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        background-color: #f3e8ff;
        color: #7c3aed;
        display: grid;
        place-items: center;
        font-size: 1.5rem;
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

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th, .table td {
        padding: 10px 12px;
        font-size: 0.85rem;
        border-bottom: 1px solid var(--border-color);
    }

    .table th {
        background-color: #f8fafc;
        font-weight: 600;
        color: var(--text-gray);
        text-align: left;
    }

    @media (max-width: 991px) {
        .settings-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<!-- Bagian Backup Database -->
<div class="backup-banner">
    <div class="backup-info">
        <div class="backup-icon"><i class="fa-solid fa-database"></i></div>
        <div>
            <h4 style="font-family:'Outfit', sans-serif; font-size:1.05rem; font-weight:700; color:#581c87; margin-bottom:4px;">Cadangkan Basis Data Sistem (Database SQL)</h4>
            <p style="font-size:0.85rem; color:#6b21a8; max-width:600px; line-height:1.4;">Unduh salinan penuh database tagihan billing internet Anda secara langsung untuk kebutuhan backup berkala, pemulihan data, atau relokasi hosting server.</p>
        </div>
    </div>
    <a href="{{ route('admin.pengaturan.backup') }}" class="btn btn-primary" style="background:linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);">
        <i class="fa-solid fa-download"></i> Unduh File Backup SQL
    </a>
</div>

<div class="settings-grid">
    <!-- Card 1: Profil Usaha -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-hotel"></i>
                <span>Profil Lembaga & Usaha Billing</span>
            </div>
        </div>
        
        <form action="{{ route('admin.pengaturan.profile') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="nama_sekolah">Nama Usaha / Lembaga *</label>
                <input type="text" id="nama_sekolah" name="nama_sekolah" class="form-control" value="{{ $profile->nama_sekolah ?? '' }}" required>
            </div>

            <div class="form-group">
                <label for="telepon">Nomor Telepon Kontak *</label>
                <input type="text" id="telepon" name="telepon" class="form-control" value="{{ $profile->telepon ?? '' }}" required>
            </div>

            <div class="form-group">
                <label for="email">Alamat Email *</label>
                <input type="email" id="email" name="email" class="form-control" value="{{ $profile->email ?? '' }}" required>
            </div>

            <div class="form-group">
                <label for="alamat">Alamat Kantor Lengkap *</label>
                <textarea id="alamat" name="alamat" class="form-control" rows="3" required>{{ $profile->alamat ?? '' }}</textarea>
            </div>

            <div class="form-group">
                <label for="logo">Logo  (Format: PNG, JPG, JPEG - Maks. 2MB)</label>
                <div style="display:flex; align-items:center; gap:16px; margin-top:6px;">
                    @if(!empty($profile->foto) && file_exists(public_path('images/' . $profile->foto)))
                        <img id="logo-preview" src="{{ asset('images/' . $profile->foto) }}?v={{ time() }}" alt="Logo Usaha" style="width:70px; height:70px; object-fit:contain; border-radius:12px; border:1px solid var(--border-color); padding: 4px; background: #fafafa;">
                    @else
                        <img id="logo-preview" src="{{ asset('images/ion.png') }}" alt="Default Logo" style="width:70px; height:70px; object-fit:contain; border-radius:12px; border:1px solid var(--border-color); padding: 4px; background: #fafafa;">
                    @endif
                    <input type="file" id="logo" name="logo" class="form-control" accept="image/*" style="padding: 8px 12px; height: auto;" onchange="previewImage(this)">
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; margin-top:20px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Profil & Logo
                </button>
            </div>
        </form>
    </div>

    <!-- Card: Waktu Jatuh Tempo -->
    <div class="card" style="margin-top: 24px;">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-calendar-days"></i>
                <span>Pengaturan Waktu Jatuh Tempo</span>
            </div>
        </div>
        
        <form action="{{ route('admin.pengaturan.jatuh_tempo') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="sistem_billing">Sistem Billing Pembayaran *</label>
                <select id="sistem_billing" name="sistem_billing" class="form-control" style="padding: 10px 14px; height: auto; border-radius: 12px; font-size: 0.95rem; width: 100%; margin: 0; background-color: #f8fafc;" required>
                    <option value="prabayar" {{ ($profile->sistem_billing ?? 'prabayar') === 'prabayar' ? 'selected' : '' }}>Prabayar (Bayar Dulu Baru Pakai - Jatuh Tempo Bulan Ini)</option>
                    <option value="pascabayar" {{ ($profile->sistem_billing ?? '') === 'pascabayar' ? 'selected' : '' }}>Pascabayar (Pakai Dulu Baru Bayar - Jatuh Tempo Bulan Depan)</option>
                </select>
                <small style="color:var(--text-gray); margin-top:4px;">Tentukan apakah tagihan jatuh tempo pada bulan penagihan yang sama (Prabayar) atau pada bulan berikutnya (Pascabayar).</small>
            </div>

            <div class="form-group">
                <label for="tipe_jatuh_tempo">Tipe Tanggal Jatuh Tempo *</label>
                <select id="tipe_jatuh_tempo" name="tipe_jatuh_tempo" class="form-control" style="padding: 10px 14px; height: auto; border-radius: 12px; font-size: 0.95rem; width: 100%; margin: 0; background-color: #f8fafc;" required>
                    <option value="tanggal_tetap" {{ ($profile->tipe_jatuh_tempo ?? 'tanggal_tetap') === 'tanggal_tetap' ? 'selected' : '' }}>Tanggal Tetap Setiap Bulan (Flat)</option>
                    <option value="tanggal_pasang" {{ ($profile->tipe_jatuh_tempo ?? '') === 'tanggal_pasang' ? 'selected' : '' }}>Sesuai Tanggal Pemasangan Pelanggan</option>
                </select>
                <small style="color:var(--text-gray); margin-top:4px;">Pilih apakah tanggal jatuh tempo tagihan diatur seragam setiap bulannya atau mengikuti tanggal pasang masing-masing pelanggan.</small>
            </div>

            <div class="form-group" id="group_hari_jatuh_tempo" style="{{ ($profile->tipe_jatuh_tempo ?? 'tanggal_tetap') === 'tanggal_pasang' ? 'display:none;' : '' }}">
                <label for="hari_jatuh_tempo">Hari Jatuh Tempo Bulanan (Tanggal 1-31) *</label>
                <input type="number" id="hari_jatuh_tempo" name="hari_jatuh_tempo" class="form-control" min="1" max="31" value="{{ $profile->hari_jatuh_tempo ?? 10 }}">
                <small style="color:var(--text-gray); margin-top:4px;">Masukkan tanggal (1 sampai 31) untuk hari jatuh tempo flat setiap bulannya (misal: 10).</small>
            </div>

            <!-- Fitur Kirim Tagihan Otomatis -->
            <div class="form-group" style="margin-top: 15px; border-top: 1px solid #e2e8f0; padding-top: 15px;">
                <label for="auto_send_billing" style="font-weight: 700; color: #4f46e5;"><i class="fa-solid fa-paper-plane"></i> Pengiriman Tagihan Otomatis (WhatsApp)</label>
                <select id="auto_send_billing" name="auto_send_billing" class="form-control" style="padding: 10px 14px; height: auto; border-radius: 12px; font-size: 0.95rem; width: 100%; margin: 0; background-color: #f8fafc;" required>
                    <option value="0" {{ ($profile->auto_send_billing ?? 0) == 0 ? 'selected' : '' }}>Nonaktif</option>
                    <option value="1" {{ ($profile->auto_send_billing ?? 0) == 1 ? 'selected' : '' }}>Aktif</option>
                </select>
                <small style="color:var(--text-gray); margin-top:4px;">Pilih apakah tagihan bulanan akan otomatis dikirimkan ke WhatsApp pelanggan sesuai jadwal.</small>
            </div>

            <!-- Group Tanggal Kirim Otomatis (untuk tipe_jatuh_tempo = tanggal_tetap) -->
            <div class="form-group" id="group_auto_send_date" style="{{ (($profile->auto_send_billing ?? 0) == 1 && ($profile->tipe_jatuh_tempo ?? 'tanggal_tetap') === 'tanggal_tetap') ? '' : 'display:none;' }}">
                <label for="auto_send_date">Tanggal Kirim Tagihan Otomatis (Tanggal 1-31) *</label>
                <input type="number" id="auto_send_date" name="auto_send_date" class="form-control" min="1" max="31" value="{{ $profile->auto_send_date ?? 5 }}">
                <small style="color:var(--text-gray); margin-top:4px;">Masukkan tanggal (1 sampai 31) untuk pengiriman tagihan otomatis flat setiap bulannya (misal: 5).</small>
            </div>

            <!-- Group H-Minus Kirim Otomatis (untuk tipe_jatuh_tempo = tanggal_pasang) -->
            <div class="form-group" id="group_auto_send_h_minus" style="{{ (($profile->auto_send_billing ?? 0) == 1 && ($profile->tipe_jatuh_tempo ?? 'tanggal_tetap') === 'tanggal_pasang') ? '' : 'display:none;' }}">
                <label for="auto_send_h_minus">Kurang Berapa Hari Sebelum Jatuh Tempo (H-x) *</label>
                <input type="number" id="auto_send_h_minus" name="auto_send_h_minus" class="form-control" min="0" max="30" value="{{ $profile->auto_send_h_minus ?? 3 }}">
                <small style="color:var(--text-gray); margin-top:4px;">Masukkan jumlah hari sebelum tanggal jatuh tempo untuk mengirimkan tagihan secara otomatis (misal: 3 hari sebelum).</small>
            </div>

            <div style="display:flex; justify-content:flex-end; margin-top:20px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Jatuh Tempo
                </button>
            </div>
        </form>
    </div>
    
    <script>
        function previewImage(input) {
            const preview = document.getElementById('logo-preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const tipeSelect = document.getElementById('tipe_jatuh_tempo');
            const hariGroup = document.getElementById('group_hari_jatuh_tempo');
            const hariInput = document.getElementById('hari_jatuh_tempo');

            const autoSendSelect = document.getElementById('auto_send_billing');
            const autoSendDateGroup = document.getElementById('group_auto_send_date');
            const autoSendDateInput = document.getElementById('auto_send_date');
            const autoSendHMinusGroup = document.getElementById('group_auto_send_h_minus');
            const autoSendHMinusInput = document.getElementById('auto_send_h_minus');
            
            function updateVisibility() {
                const tipe = tipeSelect.value;
                const autoSend = autoSendSelect.value === '1';

                // Tipe Jatuh Tempo
                if (tipe === 'tanggal_pasang') {
                    hariGroup.style.display = 'none';
                    hariInput.removeAttribute('required');
                } else {
                    hariGroup.style.display = 'flex';
                    hariInput.setAttribute('required', 'required');
                }

                // Auto Send Billing
                if (autoSend) {
                    if (tipe === 'tanggal_tetap') {
                        autoSendDateGroup.style.display = 'flex';
                        autoSendDateInput.setAttribute('required', 'required');
                        autoSendHMinusGroup.style.display = 'none';
                        autoSendHMinusInput.removeAttribute('required');
                    } else {
                        autoSendHMinusGroup.style.display = 'flex';
                        autoSendHMinusInput.setAttribute('required', 'required');
                        autoSendDateGroup.style.display = 'none';
                        autoSendDateInput.removeAttribute('required');
                    }
                } else {
                    autoSendDateGroup.style.display = 'none';
                    autoSendDateInput.removeAttribute('required');
                    autoSendHMinusGroup.style.display = 'none';
                    autoSendHMinusInput.removeAttribute('required');
                }
            }

            if (tipeSelect && autoSendSelect) {
                tipeSelect.addEventListener('change', updateVisibility);
                autoSendSelect.addEventListener('change', updateVisibility);
                updateVisibility(); // initial call
            }
        });
    </script>

    <!-- Column 2: Router & WA Settings -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <!-- Card 2: Winbox Mikrotik API (Multiple Devices) -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fa-solid fa-circle-nodes"></i>
                    <span>Koneksi API Mikrotik Router</span>
                </div>
                <button type="button" class="btn btn-primary btn-xs id-btn-add-mikrotik">
                    <i class="fa-solid fa-plus"></i> Tambah Device
                </button>
            </div>
            
            <div style="overflow-x: auto; margin-top: 10px;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama Device</th>
                            <th>IP / Port</th>
                            <th>Username</th>
                            <th style="text-align: center; width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mikrotik_devices as $dev)
                            <tr>
                                <td><strong>{{ $dev->nama_mikrotik ?: 'Unnamed' }}</strong></td>
                                <td><code>{{ $dev->ip }}:{{ $dev->port_mikrotik }}</code></td>
                                <td>{{ $dev->username }}</td>
                                <td style="text-align: center;">
                                    <div style="display: inline-flex; gap: 4px; justify-content: center; width: 100%;">
                                        <button type="button" class="btn btn-info btn-xs id-btn-edit-mikrotik"
                                            data-id="{{ $dev->id_mikrotik }}"
                                            data-nama="{{ $dev->nama_mikrotik }}"
                                            data-ip="{{ $dev->ip }}"
                                            data-port="{{ $dev->port_mikrotik }}"
                                            data-username="{{ $dev->username }}"
                                            data-password="{{ $dev->password }}">
                                            <i class="fa-solid fa-edit"></i>
                                        </button>
                                        <form action="{{ route('admin.pengaturan.mikrotik.delete') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus device Mikrotik ini?')" style="margin: 0;">
                                            @csrf
                                            <input type="hidden" name="id_mikrotik" value="{{ $dev->id_mikrotik }}">
                                            <button type="submit" class="btn btn-danger btn-xs">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 20px; color: var(--text-gray);">
                                    Belum ada device Mikrotik yang dikonfigurasi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Card 3: Token WA Fonnte -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fa-brands fa-whatsapp"></i>
                    <span>Gateway Fonnte WhatsApp API</span>
                </div>
            </div>
            
            <form action="{{ route('admin.pengaturan.token') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="token">Token API Fonnte *</label>
                    <input type="text" id="token" name="token" class="form-control" value="{{ $token->token ?? '' }}" required placeholder="Masukkan token fonnte Anda">
                    <small style="color:var(--text-gray); margin-top:4px;">Token ini digunakan oleh server untuk mengirim notifikasi penagihan tagihan internet otomatis & manual ke no WhatsApp pelanggan.</small>
                </div>

                <div style="display:flex; justify-content:flex-end; margin-top:20px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Token WA
                    </button>
                </div>
            </form>
        </div>

        <!-- Card 4: Kredensial Midtrans Payment Gateway -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fa-solid fa-credit-card"></i>
                    <span>Kredensial Midtrans Payment Gateway</span>
                </div>
            </div>
            
            <form action="{{ route('admin.pengaturan.midtrans') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="tclientkey">Midtrans Client Key *</label>
                    <input type="text" id="tclientkey" name="tclientkey" class="form-control" value="{{ $midtrans->tclientkey ?? '' }}" required placeholder="Masukkan Client Key Midtrans">
                </div>

                <div class="form-group">
                    <label for="tserverkey">Midtrans Server Key *</label>
                    <input type="text" id="tserverkey" name="tserverkey" class="form-control" value="{{ $midtrans->tserverkey ?? '' }}" required placeholder="Masukkan Server Key Midtrans">
                </div>

                <div class="form-group">
                    <label for="mode">Mode Transaksi Midtrans *</label>
                    <select id="mode" name="mode" class="form-control" style="padding: 10px 14px; height: auto; border-radius: 12px; font-size: 0.95rem; width: 100%; margin: 0; background-color: #f8fafc;" required>
                        <option value="sandbox" {{ ($midtrans->mode ?? 'sandbox') === 'sandbox' ? 'selected' : '' }}>Sandbox (Uji Coba)</option>
                        <option value="live" {{ ($midtrans->mode ?? '') === 'live' ? 'selected' : '' }}>Live (Produksi)</option>
                    </select>
                </div>

                <div style="display:flex; justify-content:flex-end; margin-top:20px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Kredensial
                    </button>
                </div>
            </form>
        </div>

        <!-- Card 5: Pengaturan Lisensi Aplikasi -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fa-solid fa-key"></i>
                    <span>Lisensi Aplikasi Billing</span>
                </div>
            </div>
            
            <form action="{{ route('admin.pengaturan.license') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="license_key">License Key *</label>
                    <input type="text" id="license_key" name="license_key" class="form-control" value="{{ $profile->license_key ?? '' }}" required placeholder="BILL-XXXX-XXXX-XXXX-XXXX">
                </div>

                <div style="margin-top: 12px; display: flex; flex-direction: column; gap: 8px;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.88rem; border-bottom: 1px solid var(--border-color); padding-bottom: 6px;">
                        <span style="color: var(--text-gray); font-weight: 500;">Status Lisensi:</span>
                        <span style="font-weight: 700; color: {{ ($profile->license_status ?? '') === 'active' ? '#10b981' : '#ef4444' }}; text-transform: uppercase;">
                            {{ $profile->license_status ?? 'INVALID' }}
                        </span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.88rem; border-bottom: 1px solid var(--border-color); padding-bottom: 6px;">
                        <span style="color: var(--text-gray); font-weight: 500;">Nama Pelanggan:</span>
                        <span style="font-weight: 700; color: #334155;">
                            {{ $profile->license_client_name ?? '-' }}
                        </span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.88rem; border-bottom: 1px solid var(--border-color); padding-bottom: 6px;">
                        <span style="color: var(--text-gray); font-weight: 500;">Paket Lisensi:</span>
                        <span style="font-weight: 700; color: var(--primary);">
                            {{ $profile->license_plan_name ?? 'Lite' }}
                        </span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.88rem; border-bottom: 1px solid var(--border-color); padding-bottom: 6px;">
                        <span style="color: var(--text-gray); font-weight: 500;">Batas Pelanggan:</span>
                        <span style="font-weight: 600; color: {{ (($profile->license_max_clients ?? 250) > 0 && DB::table('tb_pelanggan')->count() >= ($profile->license_max_clients ?? 250)) ? '#ef4444' : '#0f172a' }};">
                            {{ DB::table('tb_pelanggan')->count() }} / {{ ($profile->license_max_clients ?? 250) > 0 ? ($profile->license_max_clients ?? 250) : 'Unlimited' }}
                        </span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.88rem; border-bottom: 1px solid var(--border-color); padding-bottom: 6px;">
                        <span style="color: var(--text-gray); font-weight: 500;">Masa Berlaku:</span>
                        <span style="font-weight: 600;">
                            @if(empty($profile->license_expires_at))
                                {{ !empty($profile->license_key) && ($profile->license_status ?? '') === 'active' ? 'Lifetime (Seumur Hidup)' : '-' }}
                            @else
                                {{ \Carbon\Carbon::parse($profile->license_expires_at)->format('d-m-Y H:i') }}
                            @endif
                        </span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.88rem; padding-bottom: 6px;">
                        <span style="color: var(--text-gray); font-weight: 500;">Pengecekan Terakhir:</span>
                        <span style="font-weight: 500; color: var(--text-gray);">
                            {{ $profile->license_last_checked ? \Carbon\Carbon::parse($profile->license_last_checked)->format('d-m-Y H:i') : 'Belum pernah' }}
                        </span>
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; margin-top:20px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-unlock"></i> Simpan & Verifikasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Form Device Mikrotik (Add / Edit) -->
<div class="modal-backdrop" id="modalMikrotik">
    <div class="modal-content">
        <div class="modal-header">
            <span class="modal-title" id="mikrotikModalTitle">Tambah Device Mikrotik</span>
            <button type="button" class="modal-close" id="btnCloseMikrotikModal">&times;</button>
        </div>
        <form id="mikrotikForm" action="{{ route('admin.pengaturan.mikrotik') }}" method="POST">
            @csrf
            <input type="hidden" name="id_mikrotik" id="modal_id_mikrotik">
            
            <div class="form-group">
                <label for="modal_nama_mikrotik">Nama / Lokasi Mikrotik *</label>
                <input type="text" name="nama_mikrotik" id="modal_nama_mikrotik" class="form-control" required placeholder="Contoh: Router Utama">
            </div>

            <div class="form-row-2-1">
                <div class="form-group">
                    <label for="modal_ip">IP Address Router *</label>
                    <input type="text" name="ip" id="modal_ip" class="form-control" required placeholder="Contoh: 192.168.88.1">
                </div>
                <div class="form-group">
                    <label for="modal_port_mikrotik">API Port *</label>
                    <input type="number" name="port_mikrotik" id="modal_port_mikrotik" class="form-control" required value="8728">
                </div>
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label for="modal_username">Username Winbox *</label>
                    <input type="text" name="username" id="modal_username" class="form-control" required placeholder="admin">
                </div>
                <div class="form-group">
                    <label for="modal_password">Password Winbox *</label>
                    <input type="password" name="password" id="modal_password" class="form-control" required placeholder="password">
                </div>
            </div>

            <div style="display:flex; justify-content: flex-end; gap:8px; margin-top: 10px;">
                <button type="button" class="btn btn-default" id="btnCancelMikrotikModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Device</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const modal = document.getElementById("modalMikrotik");
        const btnAdd = document.querySelector(".id-btn-add-mikrotik");
        const btnClose = document.getElementById("btnCloseMikrotikModal");
        const btnCancel = document.getElementById("btnCancelMikrotikModal");
        const form = document.getElementById("mikrotikForm");
        
        // Modal input elements
        const mTitle = document.getElementById("mikrotikModalTitle");
        const mId = document.getElementById("modal_id_mikrotik");
        const mNama = document.getElementById("modal_nama_mikrotik");
        const mIp = document.getElementById("modal_ip");
        const mPort = document.getElementById("modal_port_mikrotik");
        const mUser = document.getElementById("modal_username");
        const mPass = document.getElementById("modal_password");

        function openModal(isEdit = false, data = {}) {
            if (isEdit) {
                mTitle.textContent = "Ubah Device Mikrotik";
                mId.value = data.id || "";
                mNama.value = data.nama || "";
                mIp.value = data.ip || "";
                mPort.value = data.port || "8728";
                mUser.value = data.username || "";
                mPass.value = data.password || "";
            } else {
                mTitle.textContent = "Tambah Device Mikrotik";
                mId.value = "";
                mNama.value = "";
                mIp.value = "";
                mPort.value = "8728";
                mUser.value = "";
                mPass.value = "";
            }
            modal.classList.add("show");
        }

        function closeModal() {
            modal.classList.remove("show");
        }

        if (btnAdd) {
            btnAdd.addEventListener("click", () => openModal(false));
        }

        document.querySelectorAll(".id-btn-edit-mikrotik").forEach(btn => {
            btn.addEventListener("click", function () {
                const data = {
                    id: this.getAttribute("data-id"),
                    nama: this.getAttribute("data-nama"),
                    ip: this.getAttribute("data-ip"),
                    port: this.getAttribute("data-port"),
                    username: this.getAttribute("data-username"),
                    password: this.getAttribute("data-password")
                };
                openModal(true, data);
            });
        });

        if (btnClose) btnClose.addEventListener("click", closeModal);
        if (btnCancel) btnCancel.addEventListener("click", closeModal);

        // Click outside modal to close
        modal.addEventListener("click", function (e) {
            if (e.target === modal) {
                closeModal();
            }
        });
    });
</script>
@endsection
