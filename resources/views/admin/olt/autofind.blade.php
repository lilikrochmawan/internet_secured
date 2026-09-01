@extends('layouts.admin')

@section('title', 'Auto-Find ONU Baru')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 style="font-family: 'Outfit', sans-serif; font-weight: 700; color: #0f172a; margin: 0;">Auto-Find ONU Baru</h2>
        <p style="color: #64748b; margin: 4px 0 0 0;">OLT: <strong>{{ $olt->nama_olt }}</strong> ({{ $olt->ip_address }} | Tipe: {{ strtoupper($olt->tipe_olt) }})</p>
    </div>
    <a href="{{ route('olt.index') }}" class="btn btn-secondary" style="height: 40px; display: inline-flex; align-items: center; text-decoration: none;">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>
</div>

@if(session('success'))
    <div style="background-color: #dcfce7; color: #15803d; padding: 12px 18px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; font-size: 0.9rem;">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div style="background-color: #fef2f2; color: #dc2626; padding: 12px 18px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; font-size: 0.9rem;">
        {{ $errors->first() }}
    </div>
@endif

@if($error)
    <div style="background-color: #fef2f2; color: #dc2626; padding: 16px; border-radius: 12px; margin-bottom: 20px; font-weight: 600;">
        <i class="fa-solid fa-circle-exclamation"></i> {{ $error }}
    </div>
@endif

<div class="card" style="background: white; border-radius: 16px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05); padding: 20px;">
    <div style="overflow-x: auto;">
        <table class="table" style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 2px solid #f1f5f9; background-color: #f8fafc;">
                    <th style="padding: 14px; font-weight: 600; color: #475569;">No</th>
                    <th style="padding: 14px; font-weight: 600; color: #475569;">Port GPON</th>
                    <th style="padding: 14px; font-weight: 600; color: #475569;">Serial Number (SN)</th>
                    <th style="padding: 14px; font-weight: 600; color: #475569;">Tipe / Model</th>
                    <th style="padding: 14px; font-weight: 600; color: #475569;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($onus as $idx => $onu)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 14px; color: #334155;">{{ $idx + 1 }}</td>
                        <td style="padding: 14px; color: #334155; font-weight: 600;">{{ $onu['gpon_port'] }}</td>
                        <td style="padding: 14px; color: #334155; font-family: monospace; font-size: 0.95rem;">{{ $onu['sn'] }}</td>
                        <td style="padding: 14px; color: #334155;">{{ $onu['model'] }}</td>
                        <td style="padding: 14px;">
                            <button type="button" class="btn" style="background-color: #eff6ff; color: #1d4ed8; font-size: 0.8rem; font-weight: 600; border-radius: 8px; padding: 6px 12px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; border: none;" onclick="openRegisterModal('{{ $onu['gpon_port'] }}', '{{ $onu['sn'] }}', '{{ $onu['model'] }}')">
                                <i class="fa-solid fa-circle-check"></i> Authorize / Register
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="padding: 30px; text-align: center; color: #64748b;">
                            <i class="fa-solid fa-circle-info" style="font-size: 1.2rem; margin-bottom: 6px; display: block;"></i>
                            Tidak ada ONU baru yang berstatus Autofind di port GPON saat ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Register Modal -->
<div id="registerModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
    <div style="background: white; border-radius: 16px; width: 100%; max-width: 480px; padding: 24px; box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1);">
        <h3 style="font-family: 'Outfit', sans-serif; font-weight: 700; margin: 0 0 16px 0; color: #0f172a;">Authorize Perangkat ONU</h3>
        
        <form method="POST" action="{{ route('admin.olt.register', $olt->id_olt) }}">
            @csrf
            <input type="hidden" name="gpon_port" id="modal_gpon_port">
            <input type="hidden" name="sn" id="modal_sn">
            <input type="hidden" name="model" id="modal_model">

            <div style="margin-bottom: 12px; background: #f8fafc; padding: 12px; border-radius: 10px; border: 1px solid #e2e8f0; font-size: 0.88rem;">
                <div style="margin-bottom: 4px;">Port GPON: <strong id="lbl_gpon_port"></strong></div>
                <div style="margin-bottom: 4px;">Serial Number: <strong id="lbl_sn" style="font-family: monospace;"></strong></div>
                <div>Model: <strong id="lbl_model"></strong></div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Tentukan ONU ID</label>
                <input type="number" name="onu_id" class="form-control" value="1" min="1" max="128" required>
                <small style="color: #64748b; margin-top: 4px; display: block;">Masukkan ID urutan ONU yang kosong pada port tersebut (1 - 128).</small>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeRegisterModal()" style="height: 42px;">Batal</button>
                <button type="submit" class="btn btn-primary" style="height: 42px;">Authorize ONU</button>
            </div>
        </form>
    </div>
</div>

<script>
function openRegisterModal(port, sn, model) {
    document.getElementById('modal_gpon_port').value = port;
    document.getElementById('modal_sn').value = sn;
    document.getElementById('modal_model').value = model;

    document.getElementById('lbl_gpon_port').innerText = port;
    document.getElementById('lbl_sn').innerText = sn;
    document.getElementById('lbl_model').innerText = model;

    document.getElementById('registerModal').style.display = 'flex';
}

function closeRegisterModal() {
    document.getElementById('registerModal').style.display = 'none';
}
</script>
@endsection
