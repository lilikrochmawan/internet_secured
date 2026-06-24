<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuitansi - {{ $tagihan->no_invoice }}</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #0d9488; /* Teal theme for receipts */
            --primary-dark: #0f766e;
            --text-dark: #0f172a;
            --text-gray: #475569;
            --text-light: #94a3b8;
            --border-color: #cbd5e1;
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

        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #94a3b8;
            border-radius: 12px;
            padding: 40px;
            position: relative;
            background-color: #ffffff;
            background-image: radial-gradient(var(--bg-light) 1px, transparent 0);
            background-size: 24px 24px;
        }

        /* Diagonal PAID Stamp */
        .paid-stamp {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-15deg);
            border: 4px double #16a34a;
            color: #16a34a;
            font-family: 'Outfit', sans-serif;
            font-size: 4rem;
            font-weight: 800;
            padding: 10px 40px;
            border-radius: 16px;
            opacity: 0.14;
            pointer-events: none;
            user-select: none;
            z-index: 100;
            letter-spacing: 4px;
            text-shadow: 1px 1px 1px rgba(0,0,0,0.1);
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
            background: linear-gradient(135deg, var(--primary-color) 0%, #2dd4bf 100%);
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

        /* Receipt Title block */
        .receipt-title-block {
            text-align: center;
            margin-bottom: 30px;
        }

        .receipt-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--text-dark);
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-block;
            border-bottom: 1px solid var(--text-dark);
            padding-bottom: 4px;
        }

        .receipt-number {
            font-family: monospace;
            font-size: 0.95rem;
            color: var(--text-gray);
            margin-top: 6px;
            font-weight: 600;
        }

        /* Receipt Fields Layout */
        .receipt-row {
            display: flex;
            margin-bottom: 18px;
            align-items: flex-start;
            font-size: 0.95rem;
        }

        .receipt-label {
            width: 180px;
            font-weight: 600;
            color: var(--text-gray);
            position: relative;
        }

        .receipt-label::after {
            content: ":";
            position: absolute;
            right: 15px;
        }

        .receipt-value {
            flex: 1;
            color: var(--text-dark);
            border-bottom: 1px dashed var(--border-color);
            padding-bottom: 2px;
        }

        .receipt-value.terbilang {
            font-style: italic;
            font-weight: 500;
            background-color: var(--bg-light);
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
        }

        /* Amount Block */
        .amount-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 40px;
            margin-bottom: 30px;
        }

        .amount-box {
            background-color: #f0fdf4;
            border: 2px solid #bbf7d0;
            border-radius: 10px;
            padding: 12px 24px;
            display: inline-flex;
            align-items: center;
            gap: 12px;
        }

        .amount-label {
            font-family: 'Outfit', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            color: var(--success-color);
            text-transform: uppercase;
        }

        .amount-val {
            font-family: 'Outfit', sans-serif;
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--success-color);
        }

        .meta-timestamp {
            font-size: 0.85rem;
            color: var(--text-gray);
            text-align: right;
            line-height: 1.5;
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
            .receipt-container {
                border: 2px solid #000;
                box-shadow: none;
                padding: 20px;
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

    <!-- PHP Spelling Words Helper -->
    @php
    function penyebut($nilai) {
        $nilai = abs($nilai);
        $huruf = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
        $temp = "";
        if ($nilai < 12) {
            $temp = " " . $huruf[$nilai];
        } else if ($nilai < 20) {
            $temp = penyebut($nilai - 10). " belas";
        } else if ($nilai < 100) {
            $temp = penyebut($nilai/10)." puluh". penyebut($nilai % 10);
        } else if ($nilai < 200) {
            $temp = " seratus" . penyebut($nilai - 100);
        } else if ($nilai < 1000) {
            $temp = penyebut($nilai/100)." ratus". penyebut($nilai % 100);
        } else if ($nilai < 2000) {
            $temp = " seribu" . penyebut($nilai - 1000);
        } else if ($nilai < 1000000) {
            $temp = penyebut($nilai/1000)." ribu". penyebut($nilai % 1000);
        } else if ($nilai < 1000000000) {
            $temp = penyebut($nilai/1000000)." juta". penyebut($nilai % 1000000);
        } else if ($nilai < 1000000000000) {
            $temp = penyebut($nilai/1000000000)." milyar". penyebut(fmod($nilai,1000000000));
        } else if ($nilai < 1000000000000000) {
            $temp = penyebut($nilai/1000000000000)." trilyun". penyebut(fmod($nilai,1000000000000));
        }     
        return $temp;
    }
    
    function terbilang($nilai) {
        if($nilai<0) {
            $hasil = "minus ". trim(penyebut($nilai));
        } else {
            $hasil = trim(penyebut($nilai));
        }     		
        return ucwords($hasil) . " Rupiah";
    }
    @endphp

    <!-- Action Buttons for Preview Screen -->
    <div class="print-btn-bar">
        <button class="print-btn" onclick="window.print()">
            <i class="fa-solid fa-print"></i> Cetak Kuitansi
        </button>
        <button class="print-btn print-btn-secondary" onclick="window.close()">
            <i class="fa-solid fa-xmark"></i> Tutup Halaman
        </button>
    </div>

    <div class="receipt-container">
        <!-- LUNAS Watermark -->
        <div class="paid-stamp">LUNAS</div>

        <!-- Header -->
        <div class="header">
            <div class="company-logo">
                <img src="{{ asset('images/' . $profile->foto) }}" alt="Logo" style="width: 45px; height: 45px; object-fit: contain; border-radius: 12px; background: rgba(0,0,0,0.03); padding: 2px;">
                <div>
                    <span class="company-name">{{ $profile->nama_sekolah ?? 'Indotel Billing' }}</span>
                    <div style="font-size: 0.8rem; color: var(--text-gray); font-weight: 500; margin-top: 2px;">RECEIPT & PAYMENT PROOF</div>
                </div>
            </div>
            <div class="company-details">
                <p><strong>Alamat:</strong> {{ $profile->alamat ?? '-' }}</p>
                <p><strong>Telp:</strong> {{ $profile->telepon ?? '-' }}</p>
                <p><strong>Email:</strong> {{ $profile->email ?? '-' }}</p>
            </div>
        </div>

        <!-- Receipt Title -->
        <div class="receipt-title-block">
            <h2 class="receipt-title">Kuitansi Pembayaran</h2>
            <div class="receipt-number">Nomor: {{ str_replace('INV/', 'REC/', $tagihan->no_invoice) }}</div>
        </div>

        <!-- Receipt Fields -->
        <div class="receipt-row">
            <div class="receipt-label">Telah Diterima Dari</div>
            <div class="receipt-value" style="font-weight: 700;">
                {{ $tagihan->pelanggan->nama_pelanggan ?? 'Nama Pelanggan' }} (ID: {{ $tagihan->pelanggan->kode_pelanggan ?? '-' }})
            </div>
        </div>

        <div class="receipt-row">
            <div class="receipt-label">Uang Sejumlah</div>
            <div class="receipt-value terbilang">
                {{ terbilang($tagihan->jml_bayar) }}
            </div>
        </div>

        <div class="receipt-row">
            <div class="receipt-label">Untuk Pembayaran</div>
            <div class="receipt-value">
                @if($tagihan->manual_invoice == 1)
                    {{ $tagihan->item_tagihan ?? 'Tagihan Manual' }}
                @else
                    Tagihan Internet Bulanan - Paket: {{ $tagihan->pelanggan->paketDetail->nama_paket ?? '-' }} 
                    @if(!empty($tagihan->pelanggan->paketDetail->kecepatan))
                        ({{ $tagihan->pelanggan->paketDetail->kecepatan }})
                    @endif
                    - Periode:
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
                @endif
                @if(($tagihan->bea_pemasangan ?? 0) > 0 || ($tagihan->jasa_troubleshooting ?? 0) > 0 || ($tagihan->lain_lain ?? 0) > 0)
                    (Termasuk biaya tambahan/pemasangan)
                @endif
            </div>
        </div>

        <!-- Amount and Metadata Footer -->
        <div class="amount-footer">
            <div class="amount-box">
                <span class="amount-label">Jumlah</span>
                <span class="amount-val">Rp {{ number_format($tagihan->jml_bayar, 0, ',', '.') }}</span>
            </div>
            
            <div class="meta-timestamp">
                <p><strong>Tanggal Bayar:</strong> {{ $tagihan->waktu_bayar ? \Carbon\Carbon::parse($tagihan->waktu_bayar)->translatedFormat('d F Y H:i') . ' WIB' : '-' }}</p>
                <p><strong>Pencatat Kas:</strong> {{ $tagihan->penerima->nama_user ?? 'Staff Administrasi' }}</p>
            </div>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <p class="signature-title">Pelanggan,</p>
                <div class="signature-line"></div>
                <p><strong>{{ $tagihan->pelanggan->nama_pelanggan ?? 'Nama Pelanggan' }}</strong></p>
            </div>
            
            <div class="signature-box">
                <p class="signature-title">Penerima Pembayaran,</p>
                <div class="signature-line"></div>
                <p><strong>{{ $tagihan->penerima->nama_user ?? 'Staff Administrasi' }}</strong></p>
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
