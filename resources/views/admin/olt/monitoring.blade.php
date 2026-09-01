@extends('layouts.admin')

@section('title', 'Monitoring Redaman OLT')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 style="font-family: 'Outfit', sans-serif; font-weight: 700; color: #0f172a; margin: 0;">Monitoring Redaman OLT</h2>
        <p style="color: #64748b; margin: 4px 0 0 0;">OLT: <strong>{{ $olt->nama_olt }}</strong> ({{ $olt->ip_address }} | Tipe: {{ strtoupper($olt->tipe_olt) }})</p>
    </div>
    <a href="{{ route('olt.index') }}" class="btn btn-secondary" style="height: 40px; display: inline-flex; align-items: center; text-decoration: none;">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>
</div>

@if($error)
    <div style="background-color: #fef2f2; color: #dc2626; padding: 16px; border-radius: 12px; margin-bottom: 20px; font-weight: 600;">
        <i class="fa-solid fa-circle-exclamation"></i> Gagal Membaca Data Real OLT: {{ $error }}
    </div>
@endif

<div class="card" style="background: white; border-radius: 16px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05); padding: 20px;">
    <div style="overflow-x: auto;">
        <table class="table" style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 2px solid #f1f5f9; background-color: #f8fafc;">
                    <th style="padding: 14px; font-weight: 600; color: #475569;">No</th>
                    <th style="padding: 14px; font-weight: 600; color: #475569;">Serial Number</th>
                    <th style="padding: 14px; font-weight: 600; color: #475569;">Nama Pelanggan</th>
                    <th style="padding: 14px; font-weight: 600; color: #475569;">IP ONU</th>
                    <th style="padding: 14px; font-weight: 600; color: #475569;">Redaman ONU (CPE Rx)</th>
                    <th style="padding: 14px; font-weight: 600; color: #475569;">Redaman OLT (OLT Rx)</th>
                    <th style="padding: 14px; font-weight: 600; color: #475569;">Transmit OLT (OLT Tx)</th>
                    <th style="padding: 14px; font-weight: 600; color: #475569;">Last Check-In</th>
                </tr>
            </thead>
            <tbody>
                @forelse($monitoredCpes as $idx => $cpe)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 14px; color: #334155;">{{ $idx + 1 }}</td>
                        <td style="padding: 14px; color: #334155; font-family: monospace; font-weight: 600;">{{ $cpe['serial_number'] }}</td>
                        <td style="padding: 14px; color: #334155;">{{ $cpe['nama_pelanggan'] }}</td>
                        <td style="padding: 14px; color: #334155;">{{ $cpe['ip_address'] }}</td>
                        
                        <!-- CPE Side Rx Signal -->
                        <td style="padding: 14px;">
                            @php
                                $valCpe = floatval(preg_replace('/[^0-9.-]/', '', $cpe['rx_power_cpe']));
                                $colorCpe = '#15803d';
                                if ($valCpe <= -27) {
                                    $colorCpe = '#dc2626';
                                } elseif ($valCpe <= -24) {
                                    $colorCpe = '#d97706';
                                }
                            @endphp
                            <span style="font-weight: 700; color: {{ $colorCpe }};">{{ $cpe['rx_power_cpe'] }}</span>
                        </td>
                        
                        <!-- OLT Side Rx Signal -->
                        <td style="padding: 14px;">
                            @php
                                $valOlt = floatval(preg_replace('/[^0-9.-]/', '', $cpe['rx_power_olt']));
                                $colorOlt = '#15803d';
                                if ($valOlt <= -27) {
                                    $colorOlt = '#dc2626';
                                } elseif ($valOlt <= -24) {
                                    $colorOlt = '#d97706';
                                }
                            @endphp
                            <span style="font-weight: 700; color: {{ $colorOlt }};">{{ $cpe['rx_power_olt'] }}</span>
                        </td>

                        <td style="padding: 14px; color: #334155;">{{ $cpe['tx_power_olt'] }}</td>
                        <td style="padding: 14px; color: #64748b; font-size: 0.85rem;">{{ $cpe['last_inform'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="padding: 30px; text-align: center; color: #64748b;">Belum ada ONU yang terhubung ke ACS TR-069 untuk dipantau redamannya.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
