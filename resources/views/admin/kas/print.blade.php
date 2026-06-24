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
            max-width: 1000px;
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
        }

        .report-table th {
            background-color: var(--bg-light);
            font-weight: 600;
            color: var(--text-dark);
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* Summary Cards */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 40px;
        }

        .summary-card {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 14px 18px;
            background-color: var(--bg-light);
        }

        .summary-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--text-gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-value {
            font-family: 'Outfit', sans-serif;
            font-size: 1.25rem;
            font-weight: 700;
            margin-top: 4px;
        }

        .text-success { color: #16a34a; }
        .text-danger { color: #dc2626; }
        .text-primary { color: #4f46e5; }

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
                size: A4;
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
                    <div style="font-size: 0.8rem; color: var(--text-gray); font-weight: 500; margin-top: 2px;">LAPORAN KAS MASUK & KELUAR</div>
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
            <h2 class="report-title">Laporan Keuangan</h2>
            <div class="report-meta">{{ $title }}</div>
        </div>

        <!-- Summary Cards -->
        <div class="summary-grid">
            <div class="summary-card">
                <span class="summary-label">Total Pemasukan (Debit)</span>
                <div class="summary-value text-success">Rp {{ number_format($total_masuk, 0, ',', '.') }}</div>
            </div>
            <div class="summary-card">
                <span class="summary-label">Total Pengeluaran (Kredit)</span>
                <div class="summary-value text-danger">Rp {{ number_format($total_keluar, 0, ',', '.') }}</div>
            </div>
            <div class="summary-card">
                <span class="summary-label">Selisih (Saldo Bersih)</span>
                <div class="summary-value text-primary">Rp {{ number_format($saldo, 0, ',', '.') }}</div>
            </div>
        </div>

        <!-- Table -->
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 5%;" class="text-center">No</th>
                    <th style="width: 15%;">Tanggal</th>
                    <th style="width: 50%;">Keterangan</th>
                    <th style="width: 15%;" class="text-right">Kas Masuk (Debit)</th>
                    <th style="width: 15%;" class="text-right">Kas Keluar (Kredit)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kas as $index => $k)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($k->tgl_kas)->translatedFormat('d M Y') }}</td>
                        <td>{{ html_entity_decode($k->keterangan) }}</td>
                        <td class="text-right text-success" style="font-weight: 500;">
                            {{ $k->penerimaan > 0 ? 'Rp ' . number_format($k->penerimaan, 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-right text-danger" style="font-weight: 500;">
                            {{ $k->pengeluaran > 0 ? 'Rp ' . number_format($k->pengeluaran, 0, ',', '.') : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-gray); padding: 30px;">
                            Tidak ada data transaksi kas yang tercatat pada periode ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="background-color: var(--bg-light); font-weight: 700;">
                    <td colspan="3" class="text-right">Total Akhir:</td>
                    <td class="text-right text-success">Rp {{ number_format($total_masuk, 0, ',', '.') }}</td>
                    <td class="text-right text-danger">Rp {{ number_format($total_keluar, 0, ',', '.') }}</td>
                </tr>
                <tr style="background-color: #f1f5f9; font-weight: 700;">
                    <td colspan="3" class="text-right">Saldo Bersih (Debit - Kredit):</td>
                    <td colspan="2" class="text-center text-primary" style="font-size: 1rem;">
                        Rp {{ number_format($saldo, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <p class="signature-title">Disiapkan Oleh,</p>
                <div class="signature-line"></div>
                <p><strong>Staff Keuangan / Kasir</strong></p>
            </div>
            
            <div class="signature-box">
                <p class="signature-title">Disetujui Oleh,</p>
                <div class="signature-line"></div>
                <p><strong>Pimpinan Lembaga</strong></p>
            </div>
        </div>
    </div>

    <!-- Auto-print on load -->
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 300);
        };
    </script>
</body>
</html>
