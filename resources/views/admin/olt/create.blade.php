@extends('layouts.admin')

@section('title', 'Tambah Perangkat OLT')

@section('content')
<div style="margin-bottom: 20px;">
    <h2 style="font-family: 'Outfit', sans-serif; font-weight: 700; color: #0f172a; margin: 0;">Tambah Perangkat OLT Baru</h2>
</div>

<div class="card" style="background: white; border-radius: 16px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05); padding: 24px; max-width: 600px;">
    <form method="POST" action="{{ route('olt.store') }}">
        @csrf
        <div style="margin-bottom: 16px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Nama OLT</label>
            <input type="text" name="nama_olt" class="form-control" placeholder="Contoh: ZTE C320 Kantor Utama" required value="{{ old('nama_olt') }}">
        </div>

        <div style="display: flex; gap: 16px; margin-bottom: 16px;">
            <div style="flex: 2;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">IP Address</label>
                <input type="text" name="ip_address" class="form-control" placeholder="Contoh: 10.10.4.5" required value="{{ old('ip_address') }}">
            </div>
            <div style="flex: 1;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Port</label>
                <input type="number" name="port" class="form-control" value="22" required>
            </div>
        </div>

        <div style="display: flex; gap: 16px; margin-bottom: 16px;">
            <div style="flex: 1;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Protokol</label>
                <select name="protokol" class="form-control">
                    <option value="ssh">SSH</option>
                    <option value="telnet">Telnet</option>
                </select>
            </div>
            <div style="flex: 1;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Tipe OLT</label>
                <select name="tipe_olt" class="form-control">
                    <option value="zte">ZTE C300/C320</option>
                    <option value="huawei">Huawei</option>
                    <option value="hsgq">HSGQ</option>
                    <option value="vsol">VSOL</option>
                    <option value="cdata">CData</option>
                    <option value="global">Global</option>
                </select>
            </div>
        </div>

        <div style="display: flex; gap: 16px; margin-bottom: 16px;">
            <div style="flex: 1;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Username</label>
                <input type="text" name="username" class="form-control" placeholder="admin" required value="{{ old('username') }}">
            </div>
            <div style="flex: 1;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
        </div>

        <div style="margin-bottom: 24px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">SNMP Read Community</label>
            <input type="text" name="snmp_community" class="form-control" value="public" required>
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end;">
            <a href="{{ route('olt.index') }}" class="btn btn-secondary" style="height: 42px; display: inline-flex; align-items: center; text-decoration: none;">Kembali</a>
            <button type="submit" class="btn btn-primary" style="height: 42px;">Simpan Perangkat</button>
        </div>
    </form>
</div>
@endsection
