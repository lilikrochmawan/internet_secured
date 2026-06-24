<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pembayaran</title>
    <style>
        :root {
            color-scheme: dark;
            color: #0f172a;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(180deg, #eef2ff 0%, #f8fafc 100%);
        }
        .page {
            width: min(920px, 100%);
            margin: 0 auto;
            padding: 24px;
        }
        .card {
            background: #ffffff;
            border-radius: 28px;
            box-shadow: 0 30px 80px rgba(15, 23, 42, 0.12);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .card-header {
            background: linear-gradient(90deg, #6366f1, #7c3aed);
            color: white;
            padding: 28px 32px;
        }
        .card-header h1 {
            margin: 0;
            font-size: 1.35rem;
        }
        .card-header p {
            margin: 8px 0 0;
            color: rgba(255,255,255,0.86);
            font-size: .95rem;
        }
        .card-body {
            padding: 28px 32px;
            display: grid;
            gap: 24px;
        }
        .alert {
            border-radius: 18px;
            border: 1px solid #fde68a;
            background: #fef9c3;
            padding: 18px 20px;
            color: #92400e;
            display: flex;
            gap: 12px;
            align-items: center;
        }
        .section-card {
            background: #f8fafc;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            padding: 20px;
        }
        .section-card h2 {
            margin: 0 0 14px;
            font-size: 1rem;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: .08em;
        }
        .detail-list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            gap: 16px;
        }
        .detail-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 18px;
            background: white;
            border-radius: 18px;
            border: 1px solid #e2e8f0;
        }
        .detail-item strong {
            color: #0f172a;
        }
        .detail-item span {
            color: #475569;
            font-size: .95rem;
        }
        .invoice-box {
            display: grid;
            gap: 14px;
        }
        .invoice-item {
            padding: 16px 18px;
            border-radius: 18px;
            background: #eef2ff;
            border-left: 4px solid #6366f1;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .invoice-item strong {
            display: block;
            color: #1e293b;
        }
        .invoice-item .muted {
            color: #475569;
            font-size: .95rem;
        }
        .summary {
            display: grid;
            gap: 12px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 22px;
            padding: 20px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            color: #475569;
        }
        .summary-row strong {
            color: #0f172a;
        }
        .summary-total {
            border-top: 1px solid #e2e8f0;
            padding-top: 14px;
            display: flex;
            justify-content: space-between;
            font-size: 1.15rem;
            font-weight: 700;
            color: #111827;
        }
        .button-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }
        .button-primary {
            padding: 16px 20px;
            border-radius: 18px;
            border: none;
            color: white;
            background: linear-gradient(90deg, #6366f1, #7c3aed);
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }
        .button-secondary {
            padding: 16px 20px;
            border-radius: 18px;
            border: 1px solid #c7d2fe;
            background: white;
            color: #4338ca;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .disabled {
            opacity: .65;
            cursor: not-allowed;
        }
        @media (max-width: 600px) {
            .page {
                padding: 16px 12px;
            }
            .card-header {
                padding: 20px 16px;
            }
            .card-header h1 {
                font-size: 1.2rem;
            }
            .card-header p {
                font-size: 0.85rem;
                margin-top: 6px;
            }
            .card-body {
                padding: 20px 16px;
                gap: 20px;
            }
            .alert {
                padding: 12px 14px;
                font-size: 0.88rem;
                gap: 8px;
            }
            .section-card {
                padding: 16px 14px;
                border-radius: 16px;
            }
            .section-card h2 {
                font-size: 0.9rem;
                margin-bottom: 12px;
            }
            .detail-list {
                gap: 12px;
            }
            .detail-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 6px;
                padding: 12px 14px;
                border-radius: 14px;
            }
            .detail-item span {
                font-size: 0.88rem;
            }
            .detail-item strong {
                font-size: 0.95rem;
            }
            .invoice-item {
                padding: 12px 14px;
                border-radius: 14px;
            }
            .invoice-item strong {
                font-size: 0.95rem;
            }
            .invoice-item .muted {
                font-size: 0.88rem;
            }
            .summary {
                padding: 16px 14px;
                border-radius: 16px;
            }
            .summary-total {
                font-size: 1.05rem;
            }
            .button-row {
                flex-direction: column-reverse;
                align-items: stretch;
                gap: 12px;
                margin-top: 8px;
            }
            .button-primary, .button-secondary {
                width: 100%;
                padding: 14px;
                border-radius: 14px;
                font-size: 0.95rem;
                text-align: center;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
<div class="page">
    <div class="card">
        <div class="card-header">
            <h1>Detail Pembayaran</h1>
            <p>Informasi pembayaran tagihan internet dan biaya admin 0,7%.</p>
        </div>
        <div class="card-body">
            <div class="alert">
                <span>ℹ️</span>
                <div>Informasi: Anda akan membayar semua tagihan yang belum dibayar sekaligus.@if(($jumlahAkunGabung ?? 1) > 1) Tagihan digabung dari {{ $jumlahAkunGabung }} akun dengan nomor HP yang sama.@endif</div>
            </div>

            <div class="section-card">
                <h2>Nama Pelanggan</h2>
                <div class="detail-list">
                    <li class="detail-item">
                        <span>Nama Pelanggan</span>
                        <strong>{{ $pelanggan->nama_pelanggan }}</strong>
                    </li>
                    <li class="detail-item">
                        <span>Paket Internet</span>
                        <strong>{{ optional($pelanggan->paketDetail)->nama_paket ?? 'Paket ' . $pelanggan->paket }}</strong>
                    </li>
                    <li class="detail-item">
                        <span>Nomor Telepon</span>
                        <strong>{{ $pelanggan->no_telp }}</strong>
                    </li>
                </div>
            </div>

            <div class="section-card">
                <h2>Rincian Tagihan</h2>
                <div class="invoice-box">
                    @if($invoices->isEmpty())
                        <div class="invoice-item">
                            <strong>Tidak ada tagihan belum dibayar.</strong>
                            <span class="muted">Semua tagihan pelanggan sudah lunas.</span>
                        </div>
                    @else
                        @foreach($invoices as $invoice)
                            <div class="invoice-item">
                                <strong>Bulan/Tahun: {{ $invoice['month_year'] }}</strong>
                                @if (!empty($invoice['nama_pelanggan']))
                                    <span class="muted">Pelanggan: {{ $invoice['nama_pelanggan'] }}</span>
                                @endif
                                <span class="muted">Item: {{ $invoice['item'] }}</span>
                                <span><strong>{{ 'Rp ' . number_format($invoice['amount'], 0, ',', '.') }}</strong></span>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <div class="summary">
                <div class="summary-row">
                    <span>Total Tagihan:</span>
                    <strong>{{ 'Rp ' . number_format($subtotal, 0, ',', '.') }}</strong>
                </div>
                <div class="summary-row">
                    <span>Biaya Admin (0,7%):</span>
                    <strong>{{ 'Rp ' . number_format($adminFee, 0, ',', '.') }}</strong>
                </div>
                <div class="summary-total">
                    <span>Total Pembayaran:</span>
                    <strong>{{ 'Rp ' . number_format($totalPayment, 0, ',', '.') }}</strong>
                </div>
            </div>

            <div class="button-row">
                <a href="{{ url('dashboard') }}" class="button-secondary">← Kembali</a>
                <button id="pay-button" class="button-primary {{ $invoices->isEmpty() ? 'disabled' : '' }}" {{ $invoices->isEmpty() ? 'disabled' : '' }}>
                    Lanjut Pembayaran →
                </button>
            </div>
        </div>
    </div>
    @include('partials.bottom-nav')
</div>

@if(!$invoices->isEmpty())
    <script src="{{ $snapJsUrl }}" data-client-key="{{ $clientKey }}"></script>
    <script>
        const payButton = document.getElementById('pay-button');

        payButton.addEventListener('click', function () {
            payButton.disabled = true;
            payButton.textContent = 'Menyiapkan pembayaran...';

            const currentPath = window.location.pathname;
            const chargeUrl = currentPath.includes('/payment/detail')
                ? currentPath.replace(/\/payment\/detail$/, '/payment/charge')
                : '/payment/charge';
            const dashboardUrl = currentPath.includes('/payment/detail')
                ? currentPath.replace(/\/payment\/detail$/, '/dashboard')
                : '/dashboard';

            fetch(chargeUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.token) {
                    window.snap.pay(data.token, {
                        onSuccess: function(result){
                            window.location.href = dashboardUrl;
                        },
                        onPending: function(result){
                            window.location.href = dashboardUrl;
                        },
                        onError: function(result){
                            alert('Pembayaran gagal. Silakan coba lagi.');
                            payButton.disabled = false;
                            payButton.textContent = 'Lanjut Pembayaran →';
                        },
                        onClose: function(){
                            payButton.disabled = false;
                            payButton.textContent = 'Lanjut Pembayaran →';
                        }
                    });
                } else {
                    let errorText = data.error || 'Gagal membuat transaksi Midtrans.';
                    if (data.message) {
                        errorText += '\n' + data.message;
                    }
                    if (data.response) {
                        errorText += '\n' + JSON.stringify(data.response);
                    }
                    throw new Error(errorText);
                }
            })
            .catch(error => {
                console.error('Midtrans charge error:', error);
                alert(error.message || 'Terjadi kesalahan saat menghubungkan ke Midtrans.');
                payButton.disabled = false;
                payButton.textContent = 'Lanjut Pembayaran →';
            });
        });
    </script>
@endif
</body>
</html>
