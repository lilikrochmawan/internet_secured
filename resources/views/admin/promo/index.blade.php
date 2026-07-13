@extends('layouts.admin')

@section('title', 'Data Promo Pelanggan')

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

    .btn-danger {
        background-color: #fef2f2;
        color: #dc2626;
    }
    .btn-danger:hover {
        background-color: #fee2e2;
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

    .table tr {
        transition: background-color 0.2s;
    }

    .table tr:hover {
        background-color: #f8fafc;
    }

    .form-control {
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 10px 14px;
        font-size: 0.95rem;
        outline: none;
        width: 100%;
        transition: border 0.2s;
    }

    .form-control:focus {
        border-color: #4f46e5;
    }
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-tags"></i>
            <span>Daftar Promo Pelanggan</span>
        </div>
        <a href="{{ route('admin.promo.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Tambah Promo
        </a>
    </div>

    <!-- Search Form -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-top: 10px; margin-bottom:16px; flex-wrap:wrap; gap:12px;">
        <div></div>
        <div>
            <form method="GET" action="{{ route('admin.promo.index') }}" style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                <div style="position:relative; min-width:260px;">
                    <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--text-gray); font-size:0.9rem;"></i>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Cari nama promo / pelanggan..." style="padding-left:36px; height:40px; border-radius:10px; width:100%;">
                </div>
                <button type="submit" class="btn btn-primary" style="padding: 8px 14px; height:40px; border-radius:10px;">
                    Cari
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.promo.index') }}" class="btn btn-secondary" style="padding: 8px 14px; height:40px; border-radius:10px; display:inline-flex; align-items:center; background-color:#e2e8f0; color:#334155;">
                        Reset
                    </a>
                @endif
            </form>
        </div>
    </div>

    <!-- Session Alerts -->
    @if(session('success'))
        <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 12px 16px; border-radius: 12px; margin-bottom: 16px;">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div style="background-color: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; padding: 12px 16px; border-radius: 12px; margin-bottom: 16px;">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Promo</th>
                    <th>Pelanggan</th>
                    <th>Paket Internet</th>
                    <th>Periode Promo</th>
                    <th style="text-align: right;">Tagihan Awal</th>
                    <th>Tgl Dibuat</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($promos as $index => $promo)
                    @php
                        $mulaiDate = \Carbon\Carbon::create($promo->mulai_tahun, $promo->mulai_bulan, 1);
                        $selesaiDate = \Carbon\Carbon::create($promo->selesai_tahun, $promo->selesai_bulan, 1);
                    @endphp
                    <tr>
                        <td>{{ $promos->firstItem() + $index }}</td>
                        <td><strong>{{ $promo->nama_promo }}</strong></td>
                        <td>
                            @if($promo->pelanggan)
                                {{ $promo->pelanggan->nama_pelanggan }} <br>
                                <span style="font-size:0.75rem; color:var(--text-gray);">
                                    Kode: {{ $promo->pelanggan->kode_pelanggan }} | HP: {{ $promo->pelanggan->no_telp }}
                                </span>
                            @else
                                <span style="color:red; font-style:italic;">Data Pelanggan Dihapus</span>
                            @endif
                        </td>
                        <td>{{ $promo->paket->nama_paket ?? '-' }}</td>
                        <td>
                            <span style="background-color: #e0e7ff; color: #3730a3; padding: 3px 8px; border-radius: 6px; font-size: 0.8rem; font-weight: 600;">
                                {{ $mulaiDate->translatedFormat('F Y') }} - {{ $selesaiDate->translatedFormat('F Y') }}
                            </span>
                        </td>
                        <td style="text-align: right; font-weight: 600;">Rp {{ number_format($promo->nominal_tagihan, 0, ',', '.') }}</td>
                        <td>{{ $promo->created_at ? $promo->created_at->format('d/m/Y H:i') : '-' }}</td>
                        <td style="text-align: center;">
                            <form action="{{ route('admin.promo.destroy', $promo->id_promo) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus promo ini? Tindakan ini tidak akan menghapus tagihan yang sudah terlanjur lunas.')" style="display:inline-block;">
                                @csrf
                                <button type="submit" class="btn btn-danger" style="padding: 6px 10px; border-radius:8px; font-size:0.8rem;" title="Hapus Promo">
                                    <i class="fa-solid fa-trash-can"></i> Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-gray); padding: 30px;">
                            Tidak ada data promo pelanggan ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Custom Pagination Component -->
    @if($promos->total() > 0)
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px; flex-wrap: wrap; gap: 12px;">
            <div style="font-size: 0.85rem; color: var(--text-gray); font-weight: 500;">
                Menampilkan {{ $promos->firstItem() ?? 0 }} - {{ $promos->lastItem() ?? 0 }} dari {{ $promos->total() }} Promo
            </div>
            
            @if($promos->lastPage() > 1)
                <div style="display: flex; align-items: center; gap: 6px;">
                    {{-- Previous Button --}}
                    @if($promos->onFirstPage())
                        <span style="padding: 8px 12px; border-radius: 8px; background: #f1f5f9; color: #94a3b8; font-size: 0.85rem; cursor: not-allowed; font-weight: 600;">
                            <i class="fa-solid fa-chevron-left"></i>
                        </span>
                    @else
                        <a href="{{ $promos->previousPageUrl() }}" style="padding: 8px 12px; border-radius: 8px; background: #e2e8f0; color: #334155; font-size: 0.85rem; text-decoration: none; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.background='#cbd5e1'" onmouseout="this.style.background='#e2e8f0'">
                            <i class="fa-solid fa-chevron-left"></i>
                        </a>
                    @endif

                    {{-- Page Numbers --}}
                    @php
                        $start = max(1, $promos->currentPage() - 2);
                        $end = min($promos->lastPage(), $promos->currentPage() + 2);
                    @endphp
                    
                    @if($start > 1)
                        <a href="{{ $promos->url(1) }}" style="padding: 8px 14px; border-radius: 8px; background: #e2e8f0; color: #334155; font-size: 0.85rem; text-decoration: none; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.background='#cbd5e1'" onmouseout="this.style.background='#e2e8f0'">1</a>
                        @if($start > 2)
                            <span style="color: var(--text-gray); padding: 0 4px;">...</span>
                        @endif
                    @endif

                    @for($p = $start; $p <= $end; $p++)
                        @if($p == $promos->currentPage())
                            <span style="padding: 8px 14px; border-radius: 8px; background: var(--primary-gradient); color: white; font-size: 0.85rem; font-weight: 700; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.2);">{{ $p }}</span>
                        @else
                            <a href="{{ $promos->url($p) }}" style="padding: 8px 14px; border-radius: 8px; background: #e2e8f0; color: #334155; font-size: 0.85rem; text-decoration: none; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.background='#cbd5e1'" onmouseout="this.style.background='#e2e8f0'">{{ $p }}</a>
                        @endif
                    @endfor

                    @if($end < $promos->lastPage())
                        @if($end < $promos->lastPage() - 1)
                            <span style="color: var(--text-gray); padding: 0 4px;">...</span>
                        @endif
                        <a href="{{ $promos->url($promos->lastPage()) }}" style="padding: 8px 14px; border-radius: 8px; background: #e2e8f0; color: #334155; font-size: 0.85rem; text-decoration: none; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.background='#cbd5e1'" onmouseout="this.style.background='#e2e8f0'">{{ $promos->lastPage() }}</a>
                    @endif

                    {{-- Next Button --}}
                    @if($promos->hasMorePages())
                        <a href="{{ $promos->nextPageUrl() }}" style="padding: 8px 12px; border-radius: 8px; background: #e2e8f0; color: #334155; font-size: 0.85rem; text-decoration: none; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.background='#cbd5e1'" onmouseout="this.style.background='#e2e8f0'">
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    @else
                        <span style="padding: 8px 12px; border-radius: 8px; background: #f1f5f9; color: #94a3b8; font-size: 0.85rem; cursor: not-allowed; font-weight: 600;">
                            <i class="fa-solid fa-chevron-right"></i>
                        </span>
                    @endif
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
