@extends('layouts.admin')

@section('title', 'Piutang Pelanggan')

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

    .card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.05);
        margin-bottom: 24px;
        overflow: hidden;
    }

    .card-body {
        padding: 0;
    }

    .table-responsive {
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
        background: #f8fafc;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
    }

    .table tr:hover {
        background: #f1f5f9;
    }
</style>
@endsection

@section('content')
<div class="content-header" style="margin-bottom: 20px;">
    <div class="header-left">
        <h2 style="font-weight: 700; color: #1f2937; margin: 0;">Piutang Pelanggan</h2>
        <p style="color: #6b7280; margin: 4px 0 0 0;">Daftar tagihan yang dicatat sebagai piutang.</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Pelanggan</th>
                        <th>Bulan Piutang</th>
                        <th>Jumlah Piutang / Tagihan</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($piutangs as $index => $piutang)
                        <tr>
                            <td>{{ $piutangs->firstItem() + $index }}</td>
                            <td>
                                <strong>{{ $piutang->pelanggan->nama_pelanggan ?? 'N/A' }}</strong><br>
                                <span style="font-size: 0.85rem; color: #6b7280;">{{ $piutang->pelanggan->no_telp ?? '-' }}</span>
                            </td>
                            <td>
                                {{ \Carbon\Carbon::createFromFormat('mY', $piutang->bulan_tahun)->translatedFormat('F Y') }}
                            </td>
                            <td>
                                <strong style="color: #ef4444;">Rp {{ number_format($piutang->jml_bayar, 0, ',', '.') }}</strong>
                            </td>
                            <td style="text-align: center;">
                                <form action="{{ route('admin.transaksi.bayar') }}" method="POST" onsubmit="return confirm('Catat pembayaran PIUTANG untuk pelanggan ini?')">
                                    @csrf
                                    <input type="hidden" name="id_tagihan" value="{{ $piutang->id_tagihan }}">
                                    <input type="hidden" name="from_piutang" value="1">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fa-solid fa-money-bill-wave"></i> Bayar Piutang
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding: 30px; text-align: center; color: #6b7280;">
                                Tidak ada data piutang saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div style="margin-top: 20px;">
    {{ $piutangs->links('pagination::bootstrap-5') }}
</div>

@endsection
