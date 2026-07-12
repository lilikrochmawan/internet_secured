<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #4f46e5;
            --text-dark: #0f172a;
            --text-gray: #475569;
            --border-color: #cbd5e1;
            --bg-light: #f8fafc;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
            line-height: 1.5;
            padding: 40px;
            background-color: #ffffff;
            font-size: 13px;
        }

        .report-container {
            max-width: 1100px;
            margin: 0 auto;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 40px;
            position: relative;
            background-color: #ffffff;
        }

        /* Header Style */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid var(--text-dark);
            padding-bottom: 20px;
            margin-bottom: 25px;
        }

        .company-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, var(--primary-color) 0%, #7c3aed 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .company-name {
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary-color);
        }

        .company-details {
            font-size: 0.85rem;
            color: var(--text-gray);
            line-height: 1.4;
            max-width: 320px;
            text-align: right;
        }

        /* Report Title block */
        .report-title-block {
            text-align: center;
            margin-bottom: 30px;
        }

        .report-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-dark);
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-block;
            border-bottom: 1px solid var(--text-dark);
            padding-bottom: 4px;
        }

        .report-meta {
            font-size: 0.9rem;
            color: var(--text-gray);
            margin-top: 6px;
            font-weight: 600;
        }

        /* Table Style */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .report-table th, .report-table td {
            border: 1px solid var(--border-color);
            padding: 10px 12px;
            text-align: left;
            vertical-align: top;
        }

        .report-table th {
            background-color: var(--bg-light);
            font-weight: 600;
            color: var(--text-dark);
        }

        .text-center {
            text-align: center;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 8px;
            border-radius: 9999px;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-menunggu { background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .badge-proses { background-color: #eff6ff; color: #2563eb; border: 1px solid #dbeafe; }
        .badge-selesai { background-color: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }

        /* Summary Grid */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 30px;
        }

        .summary-card {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 14px 18px;
            background-color: var(--bg-light);
            text-align: center;
        }

        .summary-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-value {
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            margin-top: 4px;
        }

        /* Signatures block */
        .signature-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 50px;
            padding: 0 20px;
            page-break-inside: avoid;
        }

        .signature-box {
            text-align: center;
            width: 220px;
        }

        .signature-line {
            border-bottom: 1px solid var(--text-dark);
            margin-bottom: 8px;
            height: 70px;
        }

        .signature-title {
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--text-gray);
        }

        .print-btn-bar {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
            gap: 12px;
        }

        .print-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 0.9rem;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: opacity 0.2s;
        }

        .print-btn:hover {
            opacity: 0.9;
        }

        .print-btn-secondary {
            background-color: #64748b;
        }

        /* Print Media Styles */
        @media print {
            body {
                padding: 0;
            }
            .report-container {
                border: none;
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            .print-btn-bar {
                display: none !important;
            }
            @page {
                size: A4 landscape;
                margin: 15mm;
            }
        }
    </style>
</head>
<body>

    <!-- Action Buttons for Preview Screen -->
    <div class="print-btn-bar">
        <button class="print-btn" onclick="window.print()">
            <i class="fa-solid fa-print"></i> Cetak Laporan
        </button>
        <button class="print-btn print-btn-secondary" onclick="window.close()">
            <i class="fa-solid fa-xmark"></i> Tutup Halaman
        </button>
    </div>

    <div class="report-container">
        <!-- Header -->
        <div class="header">
            <div class="company-logo">
                <img src="{{ asset('images/' . $profile->foto) }}" alt="Logo" style="width: 45px; height: 45px; object-fit: contain; border-radius: 12px; background: rgba(0,0,0,0.03); padding: 2px;">
                <div>
                    <span class="company-name">{{ $profile->nama_sekolah ?? 'Indotel Billing' }}</span>
                    <div style="font-size: 0.8rem; color: var(--text-gray); font-weight: 500; margin-top: 2px;">LAPORAN TIKET KELUHAN PELANGGAN</div>
                </div>
            </div>
            <div class="company-details">
                <p><strong>Alamat:</strong> {{ $profile->alamat ?? '-' }}</p>
                <p><strong>Telp:</strong> {{ $profile->telepon ?? '-' }}</p>
                <p><strong>Email:</strong> {{ $profile->email ?? '-' }}</p>
            </div>
        </div>

        <!-- Report Title -->
        <div class="report-title-block">
            <h2 class="report-title">Laporan Tiket Keluhan</h2>
            <div class="report-meta">{{ $title }}</div>
        </div>

        <!-- Summary Statistics Grid -->
        @php
            $totalTiket = $keluhan->count();
            $menungguCount = $keluhan->where('status_keluhan', 'menunggu')->count();
            $prosesCount = $keluhan->where('status_keluhan', 'proses')->count();
            $selesaiCount = $keluhan->where('status_keluhan', 'selesai')->count();
        @endphp
        <div class="summary-grid">
            <div class="summary-card" style="border-left: 4px solid #4f46e5;">
                <span class="summary-label">Total Tiket</span>
                <div class="summary-value" style="color:#4f46e5;">{{ $totalTiket }}</div>
            </div>
            <div class="summary-card" style="border-left: 4px solid #dc2626;">
                <span class="summary-label">Menunggu</span>
                <div class="summary-value" style="color:#dc2626;">{{ $menungguCount }}</div>
            </div>
            <div class="summary-card" style="border-left: 4px solid #2563eb;">
                <span class="summary-label">Diproses</span>
                <div class="summary-value" style="color:#2563eb;">{{ $prosesCount }}</div>
            </div>
            <div class="summary-card" style="border-left: 4px solid #16a34a;">
                <span class="summary-label">Selesai</span>
                <div class="summary-value" style="color:#16a34a;">{{ $selesaiCount }}</div>
            </div>
        </div>

        <!-- Table -->
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 5%;" class="text-center">No</th>
                    <th style="width: 12%;">Nomor Tiket</th>
                    <th style="width: 18%;">Pelanggan</th>
                    <th style="width: 15%;">Tanggal</th>
                    <th style="width: 25%;">Detail Keluhan</th>
                    <th style="width: 8%;" class="text-center">Status</th>
                    <th style="width: 17%;">Penyebab / Solusi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($keluhan as $index => $k)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td style="font-family: monospace; font-weight:700;">#{{ $k->nomor_tiket }}</td>
                        <td>
                            <strong>{{ $k->pelanggan->nama_pelanggan ?? 'N/A' }}</strong><br>
                            <small style="color:var(--text-gray);">{{ $k->no_wa }}</small>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($k->tanggal)->translatedFormat('d M Y H:i') }} WIB</td>
                        <td>
                            <strong>{{ $k->judul_keluhan }}</strong><br>
                            <span style="font-size:0.82rem; color:var(--text-gray);">{{ $k->isi_keluhan }}</span>
                        </td>
                        <td class="text-center">
                            @if($k->status_keluhan == 'menunggu')
                                <span class="badge badge-menunggu">Menunggu</span>
                            @elseif($k->status_keluhan == 'proses')
                                <span class="badge badge-proses">Diproses</span>
                            @elseif($k->status_keluhan == 'selesai')
                                <span class="badge badge-selesai">Selesai</span>
                            @else
                                <span class="badge" style="background:#f1f5f9; color:#64748b;">{{ $k->status_keluhan }}</span>
                            @endif
                        </td>
                        <td style="font-style: italic; color: #334155;">
                            {{ $k->masalah ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-gray); padding: 30px;">
                            Tidak ada data tiket keluhan yang tercatat pada periode ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <p class="signature-title">Disiapkan Oleh,</p>
                <div class="signature-line"></div>
                <p><strong>Customer Service / Teknisi</strong></p>
            </div>
            
            <div class="signature-box">
                <p class="signature-title">Disetujui Oleh,</p>
                <div class="signature-line"></div>
                <p><strong>Pimpinan Lembaga</strong></p>
            </div>
        </div>
    </div>

</body>
</html>
