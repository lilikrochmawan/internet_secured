@extends('layouts.admin')

@section('title', 'Generate Tagihan Bulanan')

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

    .btn-success {
        background-color: #dcfce7;
        color: #15803d;
    }
    .btn-success:hover {
        background-color: #bbf7d0;
    }

    .btn-primary {
        background: var(--primary-gradient);
        color: white;
    }
    .btn-primary:hover {
        opacity: 0.9;
    }

    .filter-bar {
        background-color: #f8fafc;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 16px 20px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .form-group label {
        font-weight: 600;
        font-size: 0.85rem;
        color: var(--text-dark);
    }

    .form-control {
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 8px 14px;
        font-size: 0.9rem;
        outline: none;
        background-color: white;
        min-width: 150px;
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

    .info-callout {
        background: linear-gradient(135deg, #eff6ff 0%, #f5f3ff 100%);
        border: 1px solid #e0e7ff;
        border-radius: 14px;
        padding: 16px 20px;
        margin-bottom: 24px;
        color: #4338ca;
        font-weight: 500;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-file-invoice-dollar"></i>
            <span>Generate Tagihan Baru</span>
        </div>
        <a href="{{ route('admin.transaksi.index') }}" class="btn btn-primary" style="background: #e2e8f0; color: #475569;">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Transaksi
        </a>
    </div>

    <!-- Form Filter Periode -->
    <form method="GET" action="{{ route('admin.transaksi.show_generate') }}" class="filter-bar">
        <div class="form-group">
            <label for="bulan">Pilih Bulan</label>
            <select name="bulan" id="bulan" class="form-control">
                @php
                    $months = [
                        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', 
                        '04' => 'April', '05' => 'Mei', '06' => 'Juni', 
                        '07' => 'Juli', '08' => 'Agustus', '09' => 'September', 
                        '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                    ];
                @endphp
                @foreach($months as $val => $label)
                    <option value="{{ $val }}" {{ $bulan == $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="tahun">Pilih Tahun</label>
            <select name="tahun" id="tahun" class="form-control">
                @php
                    $currentYear = date('Y');
                @endphp
                @for($i = $currentYear - 3; $i <= $currentYear + 1; $i++)
                    <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top: 22px;">
            <i class="fa-solid fa-filter"></i> Filter Pelanggan
        </button>
    </form>

    <!-- Info Status Periode -->
    <div class="info-callout">
        <div>
            <i class="fa-solid fa-circle-info"></i>
            <span>Menampilkan pelanggan yang belum memiliki tagihan pada Periode: <strong>{{ $months[$bulan] }} {{ $tahun }}</strong> ({{ $bulantahun }})</span>
        </div>
        <div>
            @if($ppn_aktif)
                <span style="background-color: #dbeafe; color: #1e40af; padding: 4px 10px; border-radius: 20px; font-size: 0.78rem; font-weight: 600; text-transform: uppercase;">PPN Aktif</span>
            @else
                <span style="background-color: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 20px; font-size: 0.78rem; font-weight: 600; text-transform: uppercase;">PPN Non-aktif</span>
            @endif
        </div>
    </div>

    @if($pelanggan->count() > 0)
        <!-- Form Simpan Tagihan -->
        <form method="POST" action="{{ route('admin.transaksi.generate') }}">
            @csrf
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Nama Pelanggan</th>
                            <th>Kode Pelanggan</th>
                            <th>Paket Internet</th>
                            <th>Harga Paket</th>
                            <th style="width: 150px;">Total Tagihan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pelanggan as $index => $plg)
                            @php
                                $harga_paket = $plg->paketDetail->harga ?? 0;
                                $ppn_rate = $plg->paketDetail->ppn ?? 0;
                                if ($ppn_aktif) {
                                    $total_tagihan = $harga_paket + ($harga_paket * $ppn_rate);
                                } else {
                                    $total_tagihan = $harga_paket;
                                }
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $plg->nama_pelanggan }}</strong>
                                    <input type="hidden" name="id_pelanggan[]" value="{{ $plg->id_pelanggan }}">
                                    <input type="hidden" name="bulan2[]" value="{{ $bulantahun }}">
                                </td>
                                <td><span style="font-family: monospace;">{{ $plg->kode_pelanggan }}</span></td>
                                <td>{{ $plg->paketDetail->nama_paket ?? '-' }}</td>
                                <td>Rp {{ number_format($harga_paket, 0, ',', '.') }} @if($ppn_aktif && $ppn_rate > 0) <small style="color: #6366f1;">+ PPN {{ $ppn_rate * 100 }}%</small> @endif</td>
                                <td>
                                    <input type="text" name="harga[]" value="{{ $total_tagihan }}" class="form-control" style="width: 130px; font-weight: bold; background-color: #f8fafc;" readonly>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 24px; display: flex; justify-content: flex-end; align-items: center; gap: 16px;">
                <p style="color: var(--text-gray); font-size: 0.9rem;">Maksimal generate 300 data sekali simpan.</p>
                <button type="submit" class="btn btn-success" style="padding: 12px 24px; font-size: 0.95rem;">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan & Generate {{ $pelanggan->count() }} Tagihan
                </button>
            </div>
        </form>
    @else
        <div style="text-align: center; padding: 40px; color: var(--text-gray);">
            <i class="fa-solid fa-circle-check" style="font-size: 3rem; color: #10b981; margin-bottom: 16px;"></i>
            <h3>Semua tagihan sudah digenerate!</h3>
            <p style="margin-top: 8px;">Tidak ada pelanggan aktif yang belum memiliki tagihan pada periode {{ $months[$bulan] }} {{ $tahun }}.</p>
        </div>
    @endif
</div>
@endsection
