@extends('layouts.admin')

@section('title', 'Laporan Mitra')

@section('styles')
<style>
    .report-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-top: 20px;
    }
    @media (max-width: 1024px) {
        .report-grid {
            grid-template-columns: 1fr;
        }
    }
    .filter-section {
        background: #f8fafc;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 24px;
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        align-items: flex-end;
    }
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
        flex: 1;
        min-width: 180px;
    }
    .form-control {
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 8px 12px;
        font-size: 0.9rem;
        outline: none;
        background-color: white;
    }
    .stat-badge {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 1.1rem;
        color: #1e293b;
    }
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div class="card-title">
            <i class="fa-solid fa-file-invoice-dollar" style="color: #4f46e5; margin-right: 6px;"></i>
            Laporan Mitra: <span style="color:#4f46e5;">{{ $targetUser->nama_user }}</span>
        </div>
        <div>
            <a href="{{ route('admin.mitra.index') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 10px; font-size: 0.85rem; font-weight: 600; text-decoration:none; color:#475569; background:#f1f5f9;">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    <div class="card-body" style="padding: 24px;">
        <!-- SECTION FILTER LAPORAN -->
        <form action="{{ route('admin.mitra.laporan') }}" method="GET" class="filter-section">
            @if($isAdmin)
                <div class="form-group">
                    <label for="id_user" style="font-weight:600; font-size:0.85rem;">Pilih Mitra *</label>
                    <select name="id_user" id="id_user" class="form-control" required>
                        @foreach($mitras as $mitra)
                            <option value="{{ $mitra->id }}" {{ $targetUser->id == $mitra->id ? 'selected' : '' }}>
                                {{ $mitra->nama_user }} ({{ $mitra->username }})
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="id_user" value="{{ $targetUser->id }}">
            @endif

            <div class="form-group" style="max-width: 150px; min-width: 100px;">
                <label for="tahun" style="font-weight:600; font-size:0.85rem;">Tahun Laporan</label>
                <select name="tahun" id="tahun" class="form-control">
                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div class="form-group" style="max-width: 180px; min-width: 120px;">
                <label for="bulan" style="font-weight:600; font-size:0.85rem;">Bulan Laporan</label>
                <select name="bulan" id="bulan" class="form-control">
                    @for($m = 1; $m <= 12; $m++)
                        @php
                            $monthName = \Carbon\Carbon::create()->month($m)->translatedFormat('F');
                        @endphp
                        <option value="{{ sprintf('%02d', $m) }}" {{ $bulan == sprintf('%02d', $m) ? 'selected' : '' }}>
                            {{ $monthName }}
                        </option>
                    @endfor
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="height: 38px; display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 10px; font-size: 0.85rem; font-weight: 600; background: var(--primary-gradient); color:white; border:none; cursor:pointer;">
                <i class="fa-solid fa-filter"></i> Terapkan Filter
            </button>
        </form>

        <!-- LAYOUT GRID DENGAN DUA REKAPITULASI UTAMA -->
        <div class="report-grid">
            <!-- PANEL KIRI: REKAP BULANAN -->
            <div style="border: 1px solid var(--border-color); border-radius: 14px; padding: 20px; background: white;">
                <h4 style="font-family: 'Outfit', sans-serif; font-weight: 700; color: #334155; margin-bottom: 14px; font-size: 1.15rem; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-calendar-days" style="color:#2563eb;"></i> Rekap Bulanan — Tahun {{ $tahun }}
                </h4>
                <div style="overflow-x: auto;">
                    <table class="table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background-color: #f8fafc; border-bottom: 1px solid var(--border-color);">
                                <th style="padding: 10px 12px; text-align: left;">Bulan</th>
                                <th style="padding: 10px 12px; text-align: center;">Transaksi</th>
                                <th style="padding: 10px 12px; text-align: right;">Total Omset</th>
                                <th style="padding: 10px 12px; text-align: right;">Komisi Mitra</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalTahunKomisi = 0;
                                $totalTahunOmset = 0;
                                $totalTahunTx = 0;
                            @endphp
                            @forelse($rekapBulanan as $r)
                                @php
                                    $monthName = \Carbon\Carbon::create()->month($r->bulan)->translatedFormat('F');
                                    $totalTahunKomisi += $r->total_komisi;
                                    $totalTahunOmset += $r->total_omset;
                                    $totalTahunTx += $r->total_transaksi;
                                @endphp
                                <tr style="border-bottom: 1px solid var(--border-color); {{ $bulan == sprintf('%02d', $r->bulan) ? 'background-color: #f0fdf4;' : '' }}">
                                    <td style="padding: 10px 12px;"><strong>{{ $monthName }}</strong></td>
                                    <td style="padding: 10px 12px; text-align: center;"><span class="badge" style="background:#f1f5f9; color:#475569; padding:2px 6px;">{{ $r->total_transaksi }}x</span></td>
                                    <td style="padding: 10px 12px; text-align: right;">Rp {{ number_format($r->total_omset, 0, ',', '.') }}</td>
                                    <td style="padding: 10px 12px; text-align: right; font-weight:700; color: #16a34a;">Rp {{ number_format($r->total_komisi, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align: center; color: var(--text-gray); padding: 24px; font-style: italic;">
                                        Tidak ada catatan transaksi bagi hasil di tahun {{ $tahun }}.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($rekapBulanan->isNotEmpty())
                            <tfoot>
                                <tr style="background-color:#f8fafc; font-weight:700; border-top: 2px solid var(--border-color);">
                                    <td style="padding: 12px;">Total Tahun {{ $tahun }}</td>
                                    <td style="padding: 12px; text-align: center;">{{ $totalTahunTx }}x</td>
                                    <td style="padding: 12px; text-align: right;">Rp {{ number_format($totalTahunOmset, 0, ',', '.') }}</td>
                                    <td style="padding: 12px; text-align: right; color:#16a34a;">Rp {{ number_format($totalTahunKomisi, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            <!-- PANEL KANAN: REKAP TAHUNAN KUMULATIF -->
            <div style="border: 1px solid var(--border-color); border-radius: 14px; padding: 20px; background: white; height: fit-content;">
                <h4 style="font-family: 'Outfit', sans-serif; font-weight: 700; color: #334155; margin-bottom: 14px; font-size: 1.15rem; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-cubes" style="color:#7c3aed;"></i> Rekap Kumulatif Tahunan
                </h4>
                <div style="overflow-x: auto;">
                    <table class="table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background-color: #f8fafc; border-bottom: 1px solid var(--border-color);">
                                <th style="padding: 10px 12px; text-align: left;">Tahun</th>
                                <th style="padding: 10px 12px; text-align: center;">Transaksi</th>
                                <th style="padding: 10px 12px; text-align: right;">Total Omset</th>
                                <th style="padding: 10px 12px; text-align: right;">Komisi Mitra</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rekapTahunan as $rt)
                                <tr style="border-bottom: 1px solid var(--border-color); {{ $tahun == $rt->tahun_log ? 'background-color: #f0fdf4;' : '' }}">
                                    <td style="padding: 10px 12px;"><strong>Tahun {{ $rt->tahun_log }}</strong></td>
                                    <td style="padding: 10px 12px; text-align: center;"><span class="badge" style="background:#f1f5f9; color:#475569; padding:2px 6px;">{{ $rt->total_transaksi }}x</span></td>
                                    <td style="padding: 10px 12px; text-align: right;">Rp {{ number_format($rt->total_omset, 0, ',', '.') }}</td>
                                    <td style="padding: 10px 12px; text-align: right; font-weight:700; color: #7c3aed;">Rp {{ number_format($rt->total_komisi, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align: center; color: var(--text-gray); padding: 24px; font-style: italic;">
                                        Belum ada riwayat bagi hasil tahunan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- DETAIL TRANSAKSI BULAN & TAHUN TERPILIH (DI BAWAH) -->
        <div style="margin-top: 32px; border: 1px solid var(--border-color); border-radius: 14px; padding: 20px; background: white;">
            @php
                $targetMonthName = \Carbon\Carbon::create()->month((int)$bulan)->translatedFormat('F');
            @endphp
            <h4 style="font-family: 'Outfit', sans-serif; font-weight: 700; color: #334155; margin-bottom: 14px; font-size: 1.15rem; display:flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-list-check" style="color:#10b981;"></i> Rincian Detail Transaksi — {{ $targetMonthName }} {{ $tahun }}
            </h4>
            <div style="overflow-x: auto;">
                <table class="table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #f8fafc; border-bottom: 1px solid var(--border-color);">
                            <th style="padding: 12px 14px; text-align: left;">Tanggal Bayar</th>
                            <th style="padding: 12px 14px; text-align: left;">Nama Pelanggan</th>
                            <th style="padding: 12px 14px; text-align: right;">Total Bayar Tagihan</th>
                            <th style="padding: 12px 14px; text-align: left;">Ketentuan Tarif Komisi</th>
                            <th style="padding: 12px 14px; text-align: right;">Komisi Diterima</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalBulanKomisi = 0;
                            $totalBulanOmset = 0;
                        @endphp
                        @forelse($detailTransaksi as $dt)
                            @php
                                $totalBulanKomisi += $dt->komisi_diterima;
                                $totalBulanOmset += $dt->jumlah_bayar;
                            @endphp
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 12px 14px; font-size: 0.85rem; color:#64748b;">
                                    {{ $dt->waktu_bayar ? \Carbon\Carbon::parse($dt->waktu_bayar)->translatedFormat('d F Y H:i') : \Carbon\Carbon::parse($dt->created_at)->translatedFormat('d F Y H:i') }}
                                </td>
                                <td style="padding: 12px 14px;"><strong>{{ $dt->nama_pelanggan }}</strong></td>
                                <td style="padding: 12px 14px; text-align: right;">Rp {{ number_format($dt->jumlah_bayar, 0, ',', '.') }}</td>
                                <td style="padding: 12px 14px;">
                                    <span class="badge" style="background:#f8fafc; border:1px solid #e2e8f0; color:#475569; padding:2px 6px; border-radius:6px; font-size:0.8rem; font-weight:600;">
                                        {{ $dt->tipe_komisi == 'flat' ? 'Rp ' . number_format($dt->nilai_komisi, 0, ',', '.') : $dt->nilai_komisi . '%' }}
                                    </span>
                                </td>
                                <td style="padding: 12px 14px; text-align: right; font-weight: 700; color: #16a34a;">
                                    + Rp {{ number_format($dt->komisi_diterima, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--text-gray); padding: 30px; font-style: italic;">
                                    Tidak ada rincian transaksi bagi hasil pada bulan {{ $targetMonthName }} {{ $tahun }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($detailTransaksi->isNotEmpty())
                        <tfoot>
                            <tr style="background-color:#f8fafc; font-weight:700; border-top: 2px solid var(--border-color);">
                                <td colspan="2" style="padding: 12px 14px;">Total Akhir Bulan {{ $targetMonthName }}</td>
                                <td style="padding: 12px 14px; text-align: right;">Rp {{ number_format($totalBulanOmset, 0, ',', '.') }}</td>
                                <td style="padding: 12px 14px;">-</td>
                                <td style="padding: 12px 14px; text-align: right; color:#16a34a;">Rp {{ number_format($totalBulanKomisi, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <!-- DETAIL PAYOUT HISTORI (DI BAWAH) -->
        <div style="margin-top: 32px; border: 1px solid var(--border-color); border-radius: 14px; padding: 20px; background: white;">
            <h4 style="font-family: 'Outfit', sans-serif; font-weight: 700; color: #334155; margin-bottom: 14px; font-size: 1.15rem; display:flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-file-shield" style="color:#d97706;"></i> Riwayat Pembayaran Payout Kepada Mitra
            </h4>
            <div style="overflow-x: auto;">
                <table class="table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #f8fafc; border-bottom: 1px solid var(--border-color);">
                            <th style="padding: 12px 14px; text-align: left;">Tanggal Bayar</th>
                            <th style="padding: 12px 14px; text-align: right;">Jumlah Payout</th>
                            <th style="padding: 12px 14px; text-align: left;">Catatan / Deskripsi</th>
                            <th style="padding: 12px 14px; text-align: center;">Bukti Transfer</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalBulanPayout = 0;
                        @endphp
                        @forelse($payouts as $p)
                            @php
                                $totalBulanPayout += $p->jumlah;
                            @endphp
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 12px 14px; font-size: 0.85rem; color:#64748b;">
                                    {{ \Carbon\Carbon::parse($p->tgl_payout)->translatedFormat('d F Y') }}
                                </td>
                                <td style="padding: 12px 14px; text-align: right; font-weight:700; color:#2563eb;">
                                    Rp {{ number_format($p->jumlah, 0, ',', '.') }}
                                </td>
                                <td style="padding: 12px 14px; font-size: 0.85rem; color:#475569;">
                                    {{ $p->catatan ?? '-' }}
                                </td>
                                <td style="padding: 12px 14px; text-align: center;">
                                    @if($p->bukti_transfer)
                                        <a href="{{ asset('uploads/mitra_payouts/' . $p->bukti_transfer) }}" target="_blank" class="btn btn-info btn-xs" style="padding: 4px 8px; font-size: 0.75rem;">
                                            <i class="fa-solid fa-receipt"></i> Lihat Bukti
                                        </a>
                                    @else
                                        <span style="color:#94a3b8; font-style:italic;">Tidak Ada</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--text-gray); padding: 30px; font-style: italic;">
                                    Belum ada riwayat pembayaran payout kepada mitra ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($payouts->isNotEmpty())
                        <tfoot>
                            <tr style="background-color:#f8fafc; font-weight:700; border-top: 2px solid var(--border-color);">
                                <td style="padding: 12px 14px;">Total Akumulasi Payout Terbayar</td>
                                <td style="padding: 12px 14px; text-align: right; color:#2563eb;">Rp {{ number_format($totalBulanPayout, 0, ',', '.') }}</td>
                                <td colspan="2" style="padding: 12px 14px;">-</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
