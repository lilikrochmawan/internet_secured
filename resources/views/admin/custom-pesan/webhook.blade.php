@extends('layouts.admin')

@section('title', 'Template Auto-Reply Webhook')

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
        color: white;
    }
    
    .btn-primary {
        background-color: #3b82f6;
    }

    .btn-primary:hover {
        background-color: #2563eb;
    }

    .card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .card-title {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 16px;
        color: #1f2937;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 12px;
    }

    .help-text {
        font-size: 0.8rem;
        color: #64748b;
        margin-top: 4px;
    }

    .img-preview {
        max-width: 100%;
        max-height: 200px;
        border-radius: 8px;
        margin-top: 10px;
        border: 1px solid #e2e8f0;
    }
</style>
@endsection

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h1 style="font-size: 1.5rem; font-weight: 700; color: #1e293b; margin: 0;">Template Webhook & Auto-Reply</h1>
</div>

@if(session('success'))
    <div style="background-color: #dcfce7; color: #166534; padding: 12px 16px; border-radius: 12px; margin-bottom: 24px; font-weight: 500;">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="background-color: #fee2e2; color: #991b1b; padding: 12px 16px; border-radius: 12px; margin-bottom: 24px; font-weight: 500;">
        {{ session('error') }}
    </div>
@endif

<div class="grid-forms">
    <!-- Template Halo -->
    <div class="card">
        <h2 class="card-title">Pesan Sapaan (Halo)</h2>
        <form action="{{ route('admin.webhook_template.update') }}" method="POST">
            @csrf
            <input type="hidden" name="tipe" value="halo">
            
            <div class="form-group">
                <label>Keyword Pemicu (Pisahkan dengan koma)</label>
                <input type="text" name="keyword" class="form-control" value="{{ $templates['halo']->keyword ?? 'HALO, PING' }}">
                <div class="help-text">Jika pelanggan mengetik salah satu kata ini, pesan sapaan akan dikirim otomatis.</div>
            </div>

            <div class="form-group">
                <label>Isi Pesan Sapaan</label>
                <textarea name="pesan" class="form-control" rows="5" required>{{ $templates['halo']->pesan ?? '' }}</textarea>
                <div class="help-text">Variabel tersedia: {nama}</div>
            </div>

            <button type="submit" class="btn btn-primary">
                Simpan Sapaan
            </button>
        </form>
    </div>

    <!-- Template Paket Internet -->
    <div class="card">
        <h2 class="card-title">Brosur & Info Paket Internet</h2>
        <form action="{{ route('admin.webhook_template.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="tipe" value="paket_internet">
            
            <div class="form-group">
                <label>Keyword Pemicu (Pisahkan dengan koma)</label>
                <input type="text" name="keyword" class="form-control" value="{{ $templates['paket_internet']->keyword ?? 'PAKET, BROSUR, HARGA' }}">
                <div class="help-text">Jika pelanggan menanyakan paket, brosur akan dikirim otomatis.</div>
            </div>

            <div class="form-group">
                <label>Teks Pengantar Brosur</label>
                <textarea name="pesan" class="form-control" rows="4" required>{{ $templates['paket_internet']->pesan ?? '' }}</textarea>
                <div class="help-text">Variabel tersedia: {nama}</div>
            </div>

            <div class="form-group">
                <label>Gambar Brosur (Opsional)</label>
                <input type="file" name="media" class="form-control" accept="image/*">
                
                @if(isset($templates['paket_internet']) && $templates['paket_internet']->media_path)
                    <div style="margin-top: 10px;">
                        <p style="font-size: 0.8rem; margin-bottom: 5px;">Gambar Saat Ini:</p>
                        <img src="{{ asset($templates['paket_internet']->media_path) }}" class="img-preview" alt="Brosur Paket">
                        <div>
                            <label style="font-weight: normal; font-size: 0.8rem; margin-top: 5px; display: inline-flex; align-items: center; gap: 5px;">
                                <input type="checkbox" name="remove_media" value="1"> Hapus gambar ini
                            </label>
                        </div>
                    </div>
                @endif
            </div>

            <button type="submit" class="btn btn-primary">
                Simpan Info Paket
            </button>
        </form>
    </div>

    <!-- Template Tagihan Lunas -->
    <div class="card">
        <h2 class="card-title">Info Tagihan (Status: LUNAS)</h2>
        <form action="{{ route('admin.webhook_template.update') }}" method="POST">
            @csrf
            <input type="hidden" name="tipe" value="tagihan_lunas">
            
            <div class="form-group">
                <label>Keyword Pemicu (Berlaku juga untuk tunggakan)</label>
                <input type="text" name="keyword" class="form-control" value="{{ $templates['tagihan_lunas']->keyword ?? 'CEK TAGIHAN, INFO TAGIHAN' }}">
                <div class="help-text">Gunakan keyword ini untuk mengecek tagihan lunas maupun belum.</div>
            </div>

            <div class="form-group">
                <label>Isi Pesan (Jika Lunas)</label>
                <textarea name="pesan" class="form-control" rows="5" required>{{ $templates['tagihan_lunas']->pesan ?? '' }}</textarea>
                <div class="help-text">Variabel tersedia: {nama}</div>
            </div>

            <button type="submit" class="btn btn-primary">
                Simpan Template Lunas
            </button>
        </form>
    </div>

    <!-- Template Tagihan Tunggakan -->
    <div class="card">
        <h2 class="card-title">Info Tagihan (Status: NUNGGAK)</h2>
        <form action="{{ route('admin.webhook_template.update') }}" method="POST">
            @csrf
            <input type="hidden" name="tipe" value="tagihan_tunggak">
            
            <div class="form-group">
                <label>Keyword Pemicu</label>
                <input type="text" class="form-control" value="Mengikuti keyword dari Tagihan Lunas" disabled style="background-color: #f1f5f9;">
            </div>

            <div class="form-group">
                <label>Isi Pesan (Jika Ada Tunggakan)</label>
                <textarea name="pesan" class="form-control" rows="8" required>{{ $templates['tagihan_tunggak']->pesan ?? '' }}</textarea>
                <div class="help-text">
                    Variabel tersedia:<br>
                    - {nama}<br>
                    - {list_tagihan} (akan diganti otomatis dengan rincian bulan dan nominal)<br>
                    - {total_tunggakan}
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                Simpan Template Nunggak
            </button>
        </form>
    </div>

    <!-- Template Kustom Tambahan -->
    @foreach($customTemplates as $ct)
    <div class="card" style="border: 1px solid #e2e8f0;">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px;">
            <h2 class="card-title" style="border-bottom: none; margin-bottom: 0; padding-bottom: 0;">Template Custom: {{ ucwords(str_replace(['_', 'custom '], [' ', ''], $ct->tipe)) }}</h2>
            <form action="{{ route('admin.webhook_template.destroy', $ct->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus template ini?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn" style="background-color: #ef4444; padding: 4px 10px; font-size: 0.75rem;">Hapus</button>
            </form>
        </div>
        
        <form action="{{ route('admin.webhook_template.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="tipe" value="{{ $ct->tipe }}">
            
            <div class="form-group">
                <label>Keyword Pemicu (Pisahkan dengan koma)</label>
                <input type="text" name="keyword" class="form-control" value="{{ $ct->keyword }}">
            </div>

            <div class="form-group">
                <label>Isi Pesan</label>
                <textarea name="pesan" class="form-control" rows="4" required>{{ $ct->pesan }}</textarea>
                <div class="help-text">Variabel tersedia: {nama}</div>
            </div>

            <div class="form-group">
                <label>Gambar / Media (Opsional)</label>
                <input type="file" name="media" class="form-control" accept="image/*">
                
                @if($ct->media_path)
                    <div style="margin-top: 10px;">
                        <img src="{{ asset($ct->media_path) }}" class="img-preview" alt="Media Custom">
                        <div>
                            <label style="font-weight: normal; font-size: 0.8rem; margin-top: 5px; display: inline-flex; align-items: center; gap: 5px;">
                                <input type="checkbox" name="remove_media" value="1"> Hapus gambar ini
                            </label>
                        </div>
                    </div>
                @endif
            </div>

            <button type="submit" class="btn btn-primary">
                Simpan Perubahan
            </button>
        </form>
    </div>
    @endforeach

    <!-- Form Tambah Template Kustom -->
    <div class="card" style="background-color: #f8fafc; border: 2px dashed #cbd5e1;">
        <h2 class="card-title">➕ Tambah Template Auto-Reply Baru</h2>
        <form action="{{ route('admin.webhook_template.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="form-group">
                <label>Nama Template</label>
                <input type="text" name="nama_template" class="form-control" placeholder="Misal: Info Gangguan" required>
            </div>

            <div class="form-group">
                <label>Keyword Pemicu (Pisahkan dengan koma)</label>
                <input type="text" name="keyword" class="form-control" placeholder="Misal: GANGGUAN, LEMOT, MATI" required>
            </div>

            <div class="form-group">
                <label>Isi Pesan Balasan</label>
                <textarea name="pesan" class="form-control" rows="4" placeholder="Ketik pesan otomatis di sini..." required></textarea>
                <div class="help-text">Variabel tersedia: {nama}</div>
            </div>

            <div class="form-group">
                <label>Sertakan Gambar (Opsional)</label>
                <input type="file" name="media" class="form-control" accept="image/*">
            </div>

            <button type="submit" class="btn" style="background-color: #10b981; margin-top: 10px;">
                <i class="fa-solid fa-plus"></i> Tambah Template
            </button>
        </form>
    </div>
</div>
@endsection
