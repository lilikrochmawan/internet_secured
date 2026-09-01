@extends('layouts.admin')

@section('title', 'Edit Perangkat OLT')

@section('content')
<div style="margin-bottom: 20px;">
    <h2 style="font-family: 'Outfit', sans-serif; font-weight: 700; color: #0f172a; margin: 0;">Edit Perangkat OLT</h2>
</div>

<div class="card" style="background: white; border-radius: 16px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05); padding: 24px; max-width: 600px;">
    <form method="POST" action="{{ route('olt.update', $olt->id_olt) }}">
        @csrf
        @method('PUT')
        <div style="margin-bottom: 16px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Nama OLT</label>
            <input type="text" name="nama_olt" class="form-control" placeholder="Contoh: ZTE C320 Kantor Utama" required value="{{ old('nama_olt', $olt->nama_olt) }}">
        </div>

        <div style="display: flex; gap: 16px; margin-bottom: 16px;">
            <div style="flex: 2;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">IP Address</label>
                <input type="text" name="ip_address" class="form-control" placeholder="Contoh: 10.10.4.5" required value="{{ old('ip_address', $olt->ip_address) }}">
            </div>
            <div style="flex: 1;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Port</label>
                <input type="number" name="port" class="form-control" value="{{ old('port', $olt->port) }}" required>
            </div>
        </div>

        <div style="display: flex; gap: 16px; margin-bottom: 16px;">
            <div style="flex: 1;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Protokol</label>
                <select name="protokol" class="form-control">
                    <option value="ssh" {{ $olt->protokol === 'ssh' ? 'selected' : '' }}>SSH</option>
                    <option value="telnet" {{ $olt->protokol === 'telnet' ? 'selected' : '' }}>Telnet</option>
                </select>
            </div>
            <div style="flex: 1;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Tipe OLT</label>
                <select name="tipe_olt" class="form-control">
                    <option value="zte" {{ $olt->tipe_olt === 'zte' ? 'selected' : '' }}>ZTE C300/C320</option>
                    <option value="huawei" {{ $olt->tipe_olt === 'huawei' ? 'selected' : '' }}>Huawei</option>
                    <option value="hsgq" {{ $olt->tipe_olt === 'hsgq' ? 'selected' : '' }}>HSGQ</option>
                    <option value="vsol" {{ $olt->tipe_olt === 'vsol' ? 'selected' : '' }}>VSOL</option>
                    <option value="cdata" {{ $olt->tipe_olt === 'cdata' ? 'selected' : '' }}>CData</option>
                    <option value="global" {{ $olt->tipe_olt === 'global' ? 'selected' : '' }}>Global</option>
                </select>
            </div>
        </div>

        <div style="display: flex; gap: 16px; margin-bottom: 16px;">
            <div style="flex: 1;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Username</label>
                <input type="text" name="username" class="form-control" placeholder="admin" required value="{{ old('username', $olt->username) }}">
            </div>
            <div style="flex: 1;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required value="{{ old('password', $olt->password) }}">
            </div>
        </div>

        <div style="margin-bottom: 24px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">SNMP Read Community</label>
            <input type="text" name="snmp_community" class="form-control" value="{{ old('snmp_community', $olt->snmp_community) }}" required>
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end;">
            <a href="{{ route('olt.index') }}" class="btn btn-secondary" style="height: 42px; display: inline-flex; align-items: center; text-decoration: none;">Kembali</a>
            <button type="submit" class="btn btn-primary" style="height: 42px;">Perbarui Konfigurasi</button>
        </div>
    </form>
</div>
@endsection
