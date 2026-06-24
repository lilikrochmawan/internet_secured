<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $tagihan->no_invoice }}</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-dark: #3730a3;
            --text-dark: #0f172a;
            --text-gray: #475569;
            --text-light: #94a3b8;
            --border-color: #e2e8f0;
            --bg-light: #f8fafc;
            --success-color: #15803d;
            --success-bg: #dcfce7;
            --danger-color: #b91c1c;
            --danger-bg: #fee2e2;
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
            font-size: 14px;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            position: relative;
            background-color: #ffffff;
        }

        /* Header Style */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 25px;
            margin-bottom: 30px;
        }

        .company-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #4f46e5 0%, #818cf8 100%);
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

        /* Meta & Client Details Layout */
        .details-grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 40px;
            margin-bottom: 30px;
        }

        .billing-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-gray);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 6px;
        }

        .info-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
        }

        .info-label {
            font-weight: 600;
            color: var(--text-gray);
        }

        .info-value {
            text-align: right;
            color: var(--text-dark);
        }

        .client-info {
            line-height: 1.6;
        }

        .client-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 4px;
        }

        .client-code {
            font-family: monospace;
            font-size: 0.9rem;
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 8px;
        }

        .client-address {
            color: var(--text-gray);
            font-size: 0.9rem;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 5px;
        }

        .status-unpaid {
            background-color: var(--danger-bg);
            color: var(--danger-color);
        }

        .status-paid {
            background-color: var(--success-bg);
            color: var(--success-color);
        }

        /* Table Style */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table th {
            background-color: var(--bg-light);
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            color: var(--text-gray);
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            padding: 12px 16px;
            border-bottom: 2px solid var(--border-color);
            text-align: left;
        }

        .items-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.9rem;
            vertical-align: top;
        }

        .item-desc {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 4px;
        }

        .item-subdesc {
            font-size: 0.8rem;
            color: var(--text-gray);
        }

        .text-right {
            text-align: right !important;
        }

        /* Totals Block */
        .totals-container {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 40px;
        }

        .totals-table {
            width: 300px;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 8px 12px;
            font-size: 0.9rem;
        }

        .totals-table tr.grand-total {
            border-top: 2px solid var(--border-color);
        }

        .totals-table tr.grand-total td {
            padding-top: 12px;
            font-size: 1.15rem;
            font-weight: 700;
        }

        .grand-total-val {
            color: var(--primary-color);
            font-family: 'Outfit', sans-serif;
        }

        /* Footer Notes */
        .footer-notes {
            text-align: center;
            border-top: 1px dashed var(--border-color);
            padding-top: 20px;
            margin-bottom: 50px;
            font-size: 0.85rem;
            color: var(--text-gray);
        }

        /* Signatures block */
        .signature-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 40px;
            padding: 0 20px;
        }

        .signature-box {
            text-align: center;
            width: 200px;
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
            .invoice-container {
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
                margin: 20mm;
            }
        }
    </style>
</head>
<body>

    <!-- Action Buttons for Preview Screen -->
    <div class="print-btn-bar">
        <button class="print-btn" onclick="window.print()">
            <i class="fa-solid fa-print"></i> Cetak Dokumen
        </button>
        <button class="print-btn print-btn-secondary" onclick="window.close()">
            <i class="fa-solid fa-xmark"></i> Tutup Halaman
        </button>
    </div>

    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div class="company-logo">
                <img src="{{ asset('images/' . $profile->foto) }}" alt="Logo" style="width: 45px; height: 45px; object-fit: contain; border-radius: 12px; background: rgba(0,0,0,0.03); padding: 2px;">
                <div>
                    <span class="company-name">{{ $profile->nama_sekolah ?? 'Indotel Billing' }}</span>
                    <div style="font-size: 0.8rem; color: var(--text-gray); font-weight: 500; margin-top: 2px;">INTERNET SERVICE PROVIDER</div>
                </div>
            </div>
            <div class="company-details">
                <p><strong>Alamat:</strong> {{ $profile->alamat ?? '-' }}</p>
                <p><strong>Telp:</strong> {{ $profile->telepon ?? '-' }}</p>
                <p><strong>Email:</strong> {{ $profile->email ?? '-' }}</p>
            </div>
        </div>

        <!-- Details Grid -->
        <div class="details-grid">
            <!-- Client Info -->
            <div>
                <h3 class="billing-title">
                    <i class="fa-solid fa-user"></i> Tagihan Kepada
                </h3>
                <div class="client-info">
                    <div class="client-name">{{ $tagihan->pelanggan->nama_pelanggan ?? 'Nama Pelanggan' }}</div>
                    <div class="client-code">ID: {{ $tagihan->pelanggan->kode_pelanggan ?? '-' }}</div>
                    <div class="client-address">
                        <strong>Alamat:</strong> {{ $tagihan->pelanggan->alamat ?? '-' }}<br>
                        <strong>No. Telp:</strong> {{ $tagihan->pelanggan->no_telp ?? '-' }}
                    </div>
                </div>
            </div>
            <!-- Invoice Meta -->
            <div>
                <h3 class="billing-title">
                    <i class="fa-solid fa-circle-info"></i> Informasi Invoice
                </h3>
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">No. Invoice:</span>
                        <span class="info-value" style="font-family: monospace; font-weight: 600;">{{ $tagihan->no_invoice }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Periode:</span>
                        <span class="info-value">
                            @php
                                $bulan = substr($tagihan->bulan_tahun, 0, 2);
                                $tahun = substr($tagihan->bulan_tahun, 2);
                                try {
                                    $date = \Carbon\Carbon::create()->month((int)$bulan)->year((int)$tahun);
                                    echo $date->translatedFormat('F Y');
                                } catch(\Exception $e) {
                                    echo $bulan . '-' . $tahun;
                                }
                            @endphp
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Jatuh Tempo:</span>
                        <span class="info-value">
                            {{ $tagihan->jatuh_tempo ? \Carbon\Carbon::parse($tagihan->jatuh_tempo)->translatedFormat('d F Y') : '-' }}
                        </span>
                    </div>
                    <div class="info-item" style="align-items: center;">
                        <span class="info-label">Status:</span>
                        <span class="info-value">
                            @if($tagihan->status_bayar == 1)
                                <span class="status-badge status-paid">LUNAS</span>
                            @else
                                <span class="status-badge status-unpaid">BELUM BAYAR</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 55px; text-align: center;">No</th>
                    <th>Layanan & Deskripsi</th>
                    <th class="text-right" style="width: 180px;">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @if($tagihan->manual_invoice == 1)
                    <!-- Manual Invoice Item -->
                    <tr>
                        <td style="text-align: center;">{{ $no++ }}</td>
                        <td>
                            <div class="item-desc">{{ $tagihan->item_tagihan ?? 'Tagihan Manual' }}</div>
                            <div class="item-subdesc">Invoice manual untuk kebutuhan khusus pelanggan</div>
                        </td>
                        <td class="text-right">Rp {{ number_format($tagihan->jml_bayar, 0, ',', '.') }}</td>
                    </tr>
                @else
                    <!-- Generated Standard Billing Item -->
                    <tr>
                        <td style="text-align: center;">{{ $no++ }}</td>
                        <td>
                            <div class="item-desc">Biaya Langganan Paket Internet</div>
                            <div class="item-subdesc">
                                Paket: {{ $tagihan->pelanggan->paketDetail->nama_paket ?? '-' }} 
                                @if(!empty($tagihan->pelanggan->paketDetail->kecepatan))
                                    ({{ $tagihan->pelanggan->paketDetail->kecepatan }})
                                @endif
                            </div>
                        </td>
                        <td class="text-right">Rp {{ number_format($tagihan->jml_bayar - ($tagihan->bea_pemasangan ?? 0) - ($tagihan->jasa_troubleshooting ?? 0) - ($tagihan->lain_lain ?? 0), 0, ',', '.') }}</td>
                    </tr>
                @endif

                <!-- Additional fee lines if any -->
                @if(($tagihan->bea_pemasangan ?? 0) > 0)
                    <tr>
                        <td style="text-align: center;">{{ $no++ }}</td>
                        <td>
                            <div class="item-desc">Biaya Pemasangan / Instalasi Alat</div>
                            <div class="item-subdesc">Pemasangan perangkat ONT/Kabel baru</div>
                        </td>
                        <td class="text-right">Rp {{ number_format($tagihan->bea_pemasangan, 0, ',', '.') }}</td>
                    </tr>
                @endif
                @if(($tagihan->jasa_troubleshooting ?? 0) > 0)
                    <tr>
                        <td style="text-align: center;">{{ $no++ }}</td>
                        <td>
                            <div class="item-desc">Jasa Perbaikan / Troubleshooting</div>
                            <div class="item-subdesc">Kunjungan teknisi dan perbaikan jaringan internal</div>
                        </td>
                        <td class="text-right">Rp {{ number_format($tagihan->jasa_troubleshooting, 0, ',', '.') }}</td>
                    </tr>
                @endif
                @if(($tagihan->lain_lain ?? 0) > 0)
                    <tr>
                        <td style="text-align: center;">{{ $no++ }}</td>
                        <td>
                            <div class="item-desc">Biaya Lain-lain</div>
                            <div class="item-subdesc">Biaya tambahan lainnya</div>
                        </td>
                        <td class="text-right">Rp {{ number_format($tagihan->lain_lain, 0, ',', '.') }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-container">
            <table class="totals-table">
                <tr>
                    <td class="info-label">Subtotal:</td>
                    <td class="text-right">Rp {{ number_format($tagihan->jml_bayar, 0, ',', '.') }}</td>
                </tr>
                <tr class="grand-total">
                    <td class="info-label">Total Tagihan:</td>
                    <td class="text-right grand-total-val">Rp {{ number_format($tagihan->jml_bayar, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <!-- Notes -->
        <div class="footer-notes">
            <p><strong>Syarat & Ketentuan Pembayaran:</strong></p>
            <p style="margin-top: 4px;">Harap lakukan pembayaran sebelum tanggal jatuh tempo untuk menghindari isolir/blokir layanan internet secara otomatis.</p>
            <p style="margin-top: 10px; font-weight: 600; color: var(--primary-color);">Terima kasih atas kepercayaan Anda menggunakan layanan {{ $profile->nama_sekolah ?? 'Indotel' }}</p>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <p class="signature-title">Pelanggan,</p>
                <div class="signature-line"></div>
                <p><strong>{{ $tagihan->pelanggan->nama_pelanggan ?? 'Nama Pelanggan' }}</strong></p>
            </div>
            
            <div class="signature-box">
                <p class="signature-title">Petugas Layanan,</p>
                <div class="signature-line"></div>
                <p><strong>{{ Auth::user()->nama_user ?? 'Kasir / Admin' }}</strong></p>
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
