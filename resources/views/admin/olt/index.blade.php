@extends('layouts.admin')

@section('title', 'Manajemen Smart OLT')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="font-family: 'Outfit', sans-serif; font-weight: 700; color: #0f172a; margin: 0;">Manajemen Smart OLT</h2>
    <a href="{{ route('olt.create') }}" class="btn btn-primary" style="height: 40px; border-radius: 10px; padding: 0 16px; display: inline-flex; align-items: center; gap: 8px; text-decoration: none;">
        <i class="fa-solid fa-plus"></i> Tambah OLT
    </a>
</div>

@if(session('success'))
    <div style="background-color: #dcfce7; color: #15803d; padding: 12px 18px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; font-size: 0.9rem;">
        {{ session('success') }}
    </div>
@endif

<div class="card" style="background: white; border-radius: 16px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05); padding: 20px;">
    <div style="overflow-x: auto;">
        <table class="table" style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 2px solid #f1f5f9; background-color: #f8fafc;">
                    <th style="padding: 14px; font-weight: 600; color: #475569;">No</th>
                    <th style="padding: 14px; font-weight: 600; color: #475569;">Nama OLT</th>
                    <th style="padding: 14px; font-weight: 600; color: #475569;">IP Address</th>
                    <th style="padding: 14px; font-weight: 600; color: #475569;">Tipe</th>
                    <th style="padding: 14px; font-weight: 600; color: #475569;">Protokol</th>
                    <th style="padding: 14px; font-weight: 600; color: #475569;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($olts as $idx => $olt)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 14px; color: #334155;">{{ $idx + 1 }}</td>
                        <td style="padding: 14px; color: #334155; font-weight: 600;">{{ $olt->nama_olt }}</td>
                        <td style="padding: 14px; color: #334155;">{{ $olt->ip_address }}:{{ $olt->port }}</td>
                        <td style="padding: 14px; color: #334155;"><span style="text-transform: uppercase; background-color: #eff6ff; color: #1d4ed8; padding: 4px 10px; border-radius: 9999px; font-size: 0.75rem; font-weight: 700;">{{ $olt->tipe_olt }}</span></td>
                        <td style="padding: 14px; color: #334155; text-transform: uppercase;">{{ $olt->protokol }}</td>
                        <td style="padding: 14px;">
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                <a href="{{ route('admin.olt.autofind', $olt->id_olt) }}" class="btn" style="background-color: #faf5ff; color: #7e22ce; font-size: 0.8rem; font-weight: 600; border-radius: 8px; padding: 6px 12px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
                                    <i class="fa-solid fa-wand-magic-sparkles"></i> Auto-Find
                                </a>
                                <a href="{{ route('admin.olt.monitoring', $olt->id_olt) }}" class="btn" style="background-color: #ecfdf5; color: #047857; font-size: 0.8rem; font-weight: 600; border-radius: 8px; padding: 6px 12px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
                                    <i class="fa-solid fa-chart-line"></i> Monitoring
                                </a>
                                <a href="{{ route('olt.edit', $olt->id_olt) }}" class="btn" style="background-color: #fef3c7; color: #d97706; font-size: 0.8rem; font-weight: 600; border-radius: 8px; padding: 6px 12px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
                                    <i class="fa-solid fa-edit"></i> Edit
                                </a>
                                <form method="POST" action="{{ route('olt.destroy', $olt->id_olt) }}" style="display: inline-block;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus OLT ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn" style="background-color: #fef2f2; color: #dc2626; font-size: 0.8rem; font-weight: 600; border-radius: 8px; padding: 6px 12px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; border: none;">
                                        <i class="fa-solid fa-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding: 30px; text-align: center; color: #64748b;">Belum ada perangkat OLT terdaftar. Silakan tambah OLT baru.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
