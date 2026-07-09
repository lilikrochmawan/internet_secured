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
            <p>Informasi pembayaran tagihan internet dan biaya admin.</p>
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

            @if(($profile->admin_fee_type ?? 'flat') === 'payment_method')
            @php
                $defaultSelected = 'qris';
                if (($profile->admin_fee_qris_status ?? 1) == 0) {
                    if (($profile->admin_fee_va_status ?? 1) == 1) {
                        $defaultSelected = 'va';
                    } elseif (($profile->admin_fee_retail_status ?? 1) == 1) {
                        $defaultSelected = 'retail';
                    } else {
                        $defaultSelected = 'none';
                    }
                }
            @endphp
            <div class="section-card" style="margin-top: 20px;">
                <h2>Pilih Metode Pembayaran</h2>
                <div style="display: grid; gap: 12px; margin-top: 10px;">
                    @if(($profile->admin_fee_qris_status ?? 1) == 1)
                    <label class="payment-method-option" style="display: flex; align-items: center; justify-content: space-between; padding: 16px; border: 2px solid {{ $defaultSelected === 'qris' ? '#6366f1' : '#e2e8f0' }}; border-radius: 16px; cursor: pointer; background: white; transition: all 0.2s;" id="label-qris">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <input type="radio" name="payment_method" value="qris" {{ $defaultSelected === 'qris' ? 'checked' : '' }} style="width: 20px; height: 20px; accent-color: #6366f1;">
                            <div>
                                <strong style="color: #0f172a; display: block;">QRIS / E-Wallet</strong>
                                <span style="font-size: 0.85rem; color: #64748b;">Gopay, ShopeePay, LinkAja, Dana, OVO, dll</span>
                            </div>
                        </div>
                        <strong style="color: #4f46e5;">
                            @if(($profile->admin_fee_qris_type ?? 'percentage') === 'percentage')
                                +{{ $profile->admin_fee_qris_value ?? 0.70 }}%
                            @else
                                +Rp {{ number_format($profile->admin_fee_qris_value ?? 0, 0, ',', '.') }}
                            @endif
                        </strong>
                    </label>
                    @endif

                    @if(($profile->admin_fee_va_status ?? 1) == 1)
                    <label class="payment-method-option" style="display: flex; align-items: center; justify-content: space-between; padding: 16px; border: 2px solid {{ $defaultSelected === 'va' ? '#6366f1' : '#e2e8f0' }}; border-radius: 16px; cursor: pointer; background: white; transition: all 0.2s;" id="label-va">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <input type="radio" name="payment_method" value="va" {{ $defaultSelected === 'va' ? 'checked' : '' }} style="width: 20px; height: 20px; accent-color: #6366f1;">
                            <div>
                                <strong style="color: #0f172a; display: block;">Virtual Account (Transfer Bank)</strong>
                                <span style="font-size: 0.85rem; color: #64748b;">BCA, Mandiri, BNI, BRI, Permata, dll</span>
                            </div>
                        </div>
                        <strong style="color: #4f46e5;">+Rp {{ number_format($profile->admin_fee_va ?? 4000, 0, ',', '.') }}</strong>
                    </label>
                    @endif

                    @if(($profile->admin_fee_retail_status ?? 1) == 1)
                    <label class="payment-method-option" style="display: flex; align-items: center; justify-content: space-between; padding: 16px; border: 2px solid {{ $defaultSelected === 'retail' ? '#6366f1' : '#e2e8f0' }}; border-radius: 16px; cursor: pointer; background: white; transition: all 0.2s;" id="label-retail">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <input type="radio" name="payment_method" value="retail" {{ $defaultSelected === 'retail' ? 'checked' : '' }} style="width: 20px; height: 20px; accent-color: #6366f1;">
                            <div>
                                <strong style="color: #0f172a; display: block;">Gerai Retail</strong>
                                <span style="font-size: 0.85rem; color: #64748b;">Alfamart, Indomaret</span>
                            </div>
                        </div>
                        <strong style="color: #4f46e5;">+Rp {{ number_format($profile->admin_fee_retail ?? 3000, 0, ',', '.') }}</strong>
                    </label>
                    @endif
                </div>
            </div>
            @endif

            <div class="summary">
                <div class="summary-row">
                    <span>Total Tagihan:</span>
                    <strong>{{ 'Rp ' . number_format($subtotal, 0, ',', '.') }}</strong>
                </div>
                <div class="summary-row">
                    <span id="fee-name-label">
                        @if(($profile->admin_fee_type ?? 'flat') === 'flat')
                            Biaya Admin:
                        @else
                            @if($defaultSelected === 'qris')
                                Biaya Admin QRIS ({{ ($profile->admin_fee_qris_type ?? 'percentage') === 'percentage' ? ($profile->admin_fee_qris_value ?? '0.7') . '%' : 'Rp ' . number_format($profile->admin_fee_qris_value ?? 0, 0, ',', '.') }}):
                            @elseif($defaultSelected === 'va')
                                Biaya Admin VA:
                            @elseif($defaultSelected === 'retail')
                                Biaya Admin Retail:
                            @else
                                Biaya Admin:
                            @endif
                        @endif
                    </span>
                    <strong id="fee-amount-label">{{ 'Rp ' . number_format($adminFee, 0, ',', '.') }}</strong>
                </div>
                <div class="summary-total">
                    <span>Total Pembayaran:</span>
                    <strong id="total-payment-label">{{ 'Rp ' . number_format($totalPayment, 0, ',', '.') }}</strong>
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

            const selectedMethodEl = document.querySelector('input[name="payment_method"]:checked');
            const selectedMethod = selectedMethodEl ? selectedMethodEl.value : 'qris';

            fetch(chargeUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    payment_method: selectedMethod
                })
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const subtotal = {{ $subtotal }};
            const feeType = '{{ $profile->admin_fee_type ?? 'flat' }}';
            
            // Flat settings
            const flatFee = {{ $profile->admin_fee_flat ?? 2000 }};
            
            // QRIS settings
            const qrisType = '{{ $profile->admin_fee_qris_type ?? 'percentage' }}';
            const qrisVal = {{ $profile->admin_fee_qris_value ?? 0.70 }};
            
            // VA and Retail settings
            const vaFee = {{ $profile->admin_fee_va ?? 4000 }};
            const retailFee = {{ $profile->admin_fee_retail ?? 3000 }};

            const feeNameLabel = document.getElementById('fee-name-label');
            const feeAmountLabel = document.getElementById('fee-amount-label');
            const totalPaymentLabel = document.getElementById('total-payment-label');

            function formatRupiah(value) {
                return 'Rp ' + value.toLocaleString('id-ID');
            }

            function updatePaymentDetails() {
                if (feeType === 'flat') {
                    return; // No dynamic change if flat
                }

                const selectedEl = document.querySelector('input[name="payment_method"]:checked');
                if (!selectedEl) return;

                const method = selectedEl.value;
                let fee = 0;
                let feeName = '';

                // Reset borders
                const labelQris = document.getElementById('label-qris');
                const labelVa = document.getElementById('label-va');
                const labelRetail = document.getElementById('label-retail');

                if (labelQris) labelQris.style.borderColor = '#e2e8f0';
                if (labelVa) labelVa.style.borderColor = '#e2e8f0';
                if (labelRetail) labelRetail.style.borderColor = '#e2e8f0';
                
                // Highlight active option border
                const activeLabel = document.getElementById('label-' + method);
                if (activeLabel) activeLabel.style.borderColor = '#6366f1';

                if (method === 'qris') {
                    if (qrisType === 'percentage') {
                        fee = Math.round(subtotal * (qrisVal / 100));
                        feeName = 'Biaya Admin QRIS (' + qrisVal + '%):';
                    } else {
                        fee = qrisVal;
                        feeName = 'Biaya Admin QRIS:';
                    }
                } else if (method === 'va') {
                    fee = vaFee;
                    feeName = 'Biaya Admin VA:';
                } else if (method === 'retail') {
                    fee = retailFee;
                    feeName = 'Biaya Admin Retail:';
                }

                const total = subtotal + fee;

                if (feeNameLabel) feeNameLabel.textContent = feeName;
                if (feeAmountLabel) feeAmountLabel.textContent = formatRupiah(fee);
                if (totalPaymentLabel) totalPaymentLabel.textContent = formatRupiah(total);
            }

            const radios = document.querySelectorAll('input[name="payment_method"]');
            radios.forEach(radio => {
                radio.addEventListener('change', updatePaymentDetails);
            });
        });
    </script>
@endif
</body>
</html>
