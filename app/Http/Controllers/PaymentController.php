<?php

namespace App\Http\Controllers;

use App\Models\Pgate;
use App\Models\Pelanggan;
use App\Models\SaldoMutasi;
use App\Services\TagihanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        private TagihanService $tagihanService
    ) {
    }

    public function detail()
    {
        $user = Auth::user();
        $pelanggan = $user->pelanggan;

        if (!$pelanggan) {
            abort(404, 'Pelanggan tidak ditemukan.');
        }

        $pelangganIds = $this->tagihanService->getPelangganIdsByPhone($pelanggan->no_telp);
        $invoices = $this->tagihanService->getUnpaidInvoices($pelangganIds);
        $jumlahAkunGabung = count($pelangganIds);

        $subtotal = $invoices->sum('amount');
        
        $profile = DB::table('tb_profile')->where('id_profile', 1)->first();
        $feeType = $profile->admin_fee_type ?? 'flat';

        if ($feeType === 'flat') {
            $adminFee = (int) ($profile->admin_fee_flat ?? 2000);
        } else {
            // Sesuai metode pembayaran - default ke metode pertama yang aktif
            $defaultMethod = 'qris';
            if (($profile->admin_fee_qris_status ?? 1) == 0) {
                if (($profile->admin_fee_va_status ?? 1) == 1) {
                    $defaultMethod = 'va';
                } elseif (($profile->admin_fee_retail_status ?? 1) == 1) {
                    $defaultMethod = 'retail';
                } else {
                    $defaultMethod = 'none';
                }
            }

            if ($defaultMethod === 'qris') {
                if (($profile->admin_fee_qris_type ?? 'percentage') === 'percentage') {
                    $adminFee = (int) round($subtotal * (($profile->admin_fee_qris_value ?? 0.70) / 100));
                } else {
                    $adminFee = (int) ($profile->admin_fee_qris_value ?? 0);
                }
            } elseif ($defaultMethod === 'va') {
                $adminFee = (int) ($profile->admin_fee_va ?? 4000);
            } elseif ($defaultMethod === 'retail') {
                $adminFee = (int) ($profile->admin_fee_retail ?? 3000);
            } else {
                $adminFee = 0;
            }
        }
        $totalPayment = $subtotal + $adminFee;

        $pgate = Pgate::first();
        $clientKey = $pgate?->tclientkey;
        $serverKey = $pgate?->tserverkey;

        $isSandbox = $pgate && ($pgate->mode === 'sandbox' || (!$pgate->mode && str_starts_with($clientKey, 'SB-')));
        $snapJsUrl = $isSandbox
            ? 'https://app.sandbox.midtrans.com/snap/snap.js'
            : 'https://app.midtrans.com/snap/snap.js';

        $isPpnActive = (($profile->tax_ppn_status ?? 'tidak') === 'aktif');
        $isPpnCharged = (($profile->tax_ppn_charged ?? 'ya') === 'ya');
        $showPpn = $isPpnActive && $isPpnCharged;
        $ppnRate = (double)($profile->tax_ppn_rate ?? 11.00);

        $ppnAmount = 0;
        $baseSubtotal = $subtotal;
        if ($showPpn) {
            $baseSubtotal = $subtotal;
            $ppnAmount = (int) round($subtotal * ($ppnRate / 100));
            $totalPayment = $subtotal + $ppnAmount + $adminFee;
        } else {
            $totalPayment = $subtotal + $adminFee;
        }

        return view('payment.detail', [
            'pelanggan' => $pelanggan,
            'saldo' => (int) ($pelanggan->saldo ?? 0),
            'invoices' => $invoices,
            'subtotal' => $subtotal,
            'adminFee' => $adminFee,
            'totalPayment' => $totalPayment,
            'clientKey' => $clientKey,
            'snapJsUrl' => $snapJsUrl,
            'isSandbox' => $isSandbox,
            'jumlahAkunGabung' => $jumlahAkunGabung,
            'showPpn' => $showPpn,
            'ppnRate' => $ppnRate,
            'ppnAmount' => $ppnAmount,
            'baseSubtotal' => $baseSubtotal,
            'profile' => $profile
        ]);
    }

    public function charge(Request $request)
    {
        $user = Auth::user();
        $pelanggan = $user->pelanggan;

        if (!$pelanggan) {
            return response()->json(['error' => 'Pelanggan tidak ditemukan.'], 404);
        }

        $pgate = Pgate::first();
        if (!$pgate || !$pgate->tclientkey || !$pgate->tserverkey) {
            return response()->json(['error' => 'Koneksi Midtrans belum tersedia.'], 500);
        }

        $clientKey = trim($pgate->tclientkey);
        $serverKey = trim($pgate->tserverkey);

        $usingSandboxClient = str_starts_with($clientKey, 'SB-');
        $usingSandboxServer = str_starts_with($serverKey, 'SB-');

        if ($usingSandboxClient !== $usingSandboxServer) {
            return response()->json([
                'error' => 'Sandbox/production key tidak konsisten.',
                'message' => 'Pastikan client key dan server key berasal dari mode yang sama (keduanya sandbox atau keduanya production).',
                'client_key' => $clientKey,
                'server_key' => $serverKey,
            ], 500);
        }

        $pelangganIds = $this->tagihanService->getPelangganIdsByPhone($pelanggan->no_telp);
        $invoices = $this->tagihanService->getUnpaidInvoices($pelangganIds);

        if ($invoices->isEmpty()) {
            return response()->json(['error' => 'Tidak ada tagihan yang harus dibayar.'], 422);
        }

        $items = [];
        $subtotal = 0;

        foreach ($invoices as $invoice) {
            $amount = $invoice['amount'];
            $subtotal += $amount;
            $label = $invoice['item'];
            if (!empty($invoice['nama_pelanggan'])) {
                $label .= ' (' . $invoice['nama_pelanggan'] . ')';
            }
            $items[] = [
                'id' => 'TAGIHAN-' . $invoice['id'],
                'price' => $amount,
                'quantity' => 1,
                'name' => $label,
            ];
        }

        $profile = DB::table('tb_profile')->where('id_profile', 1)->first();
        $feeType = $profile->admin_fee_type ?? 'flat';
        $paymentMethod = $request->input('payment_method', 'qris');

        if ($feeType === 'flat') {
            $adminFee = (int) ($profile->admin_fee_flat ?? 2000);
            $feeName = 'Biaya Admin';
        } else {
            if ($paymentMethod === 'qris') {
                if (($profile->admin_fee_qris_status ?? 1) == 0) {
                    return response()->json(['error' => 'Metode pembayaran QRIS dinonaktifkan oleh administrator.'], 422);
                }
                if (($profile->admin_fee_qris_type ?? 'percentage') === 'percentage') {
                    $adminFee = (int) round($subtotal * (($profile->admin_fee_qris_value ?? 0.70) / 100));
                    $feeName = 'Biaya Admin QRIS (' . ($profile->admin_fee_qris_value ?? 0.70) . '%)';
                } else {
                    $adminFee = (int) ($profile->admin_fee_qris_value ?? 0);
                    $feeName = 'Biaya Admin QRIS';
                }
            } elseif ($paymentMethod === 'va') {
                if (($profile->admin_fee_va_status ?? 1) == 0) {
                    return response()->json(['error' => 'Metode pembayaran VA dinonaktifkan oleh administrator.'], 422);
                }
                $adminFee = (int) ($profile->admin_fee_va ?? 4000);
                $feeName = 'Biaya Admin VA';
            } elseif ($paymentMethod === 'retail') {
                if (($profile->admin_fee_retail_status ?? 1) == 0) {
                    return response()->json(['error' => 'Metode pembayaran retail dinonaktifkan oleh administrator.'], 422);
                }
                $adminFee = (int) ($profile->admin_fee_retail ?? 3000);
                $feeName = 'Biaya Admin Retail';
            } else {
                $adminFee = (int) round($subtotal * 0.007);
                $feeName = 'Biaya Admin (0,7%)';
            }
        }

        $isPpnActive = (($profile->tax_ppn_status ?? 'tidak') === 'aktif');
        $isPpnCharged = (($profile->tax_ppn_charged ?? 'ya') === 'ya');
        $showPpn = $isPpnActive && $isPpnCharged;
        $ppnRate = (double)($profile->tax_ppn_rate ?? 11.00);

        if ($showPpn) {
            $ppnAmount = (int) round($subtotal * ($ppnRate / 100));
            $items[] = [
                'id' => 'PPN-001',
                'price' => $ppnAmount,
                'quantity' => 1,
                'name' => 'PPN (' . $ppnRate . '%)',
            ];
            $totalPayment = $subtotal + $ppnAmount + $adminFee;
        } else {
            $totalPayment = $subtotal + $adminFee;
        }

        if ($adminFee > 0) {
            $items[] = [
                'id' => 'ADMIN-001',
                'price' => $adminFee,
                'quantity' => 1,
                'name' => $feeName,
            ];
        }

                $useSaldo = $request->boolean('use_saldo');
        $saldoDeduct = (int) $request->input('saldo_deduct', 0);
        $customerSaldo = (int) ($pelanggan->saldo ?? 0);

        if ($useSaldo && $saldoDeduct > 0) {
            if ($saldoDeduct > $customerSaldo) {
                return response()->json(['error' => 'Nominal saldo yang digunakan melebihi saldo akun Anda.'], 422);
            }
            if ($saldoDeduct >= $totalPayment) {
                return response()->json(['error' => 'Saldo Anda mencukupi untuk pembayaran penuh. Silakan gunakan tombol Bayar dengan Saldo.'], 422);
            }

            // Kurangi sisa pembayaran Midtrans
            $totalPayment = $totalPayment - $saldoDeduct;
            $items[] = [
                'id' => 'POTONGAN-SALDO',
                'price' => -$saldoDeduct,
                'quantity' => 1,
                'name' => 'Potongan Saldo Pelanggan',
            ];
            $orderId = 'tagihan-' . $pelanggan->id_pelanggan . '-' . time() . '-s' . $saldoDeduct;
        } else {
            $orderId = 'tagihan-' . $pelanggan->id_pelanggan . '-' . time();
        }

        // URL notification diarahkan ke route Laravel native
        $notificationUrl = route('payment.notification');

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $totalPayment,
            ],
            'item_details' => $items,
            'customer_details' => [
                'first_name' => $pelanggan->nama_pelanggan,
                'phone' => $pelanggan->no_telp,
                'email' => $user->email ?? null,
            ],
            'callbacks' => [
                'finish' => route('dashboard'),
            ],
            'notification_url' => $notificationUrl,
        ];

        if ($feeType === 'payment_method') {
            if ($paymentMethod === 'qris') {
                $payload['enabled_payments'] = ['gopay', 'shopeepay', 'qris', 'other_qris'];
            } elseif ($paymentMethod === 'va') {
                $payload['enabled_payments'] = ['bca_va', 'bni_va', 'bri_va', 'cimb_va', 'other_va'];
            } elseif ($paymentMethod === 'retail') {
                $payload['enabled_payments'] = ['indomaret', 'alfamart'];
            }
        }

        $isSandbox = $pgate->mode === 'sandbox' || (!$pgate->mode && str_starts_with($pgate->tclientkey, 'SB-'));
        $apiUrl = $isSandbox
            ? 'https://app.sandbox.midtrans.com/snap/v1/transactions'
            : 'https://app.midtrans.com/snap/v1/transactions';

        $response = Http::withBasicAuth($pgate->tserverkey, '')
            ->withHeaders(['Accept' => 'application/json'])
            ->post($apiUrl, $payload);

        if (!$response->successful()) {
            return response()->json([
                'error' => 'Gagal membuat transaksi Midtrans.',
                'status' => $response->status(),
                'body' => $response->body(),
                'response' => $response->json(),
            ], 500);
        }

        return response()->json($response->json());
    }

        public function topupCharge(Request $request)
    {
        $user = Auth::user();
        $pelanggan = $user->pelanggan;

        if (!$pelanggan) {
            return response()->json(['error' => 'Pelanggan tidak ditemukan.'], 404);
        }

        $request->validate([
            'nominal' => 'required|numeric|min:10000',
        ], [
            'nominal.min' => 'Minimal top up saldo adalah Rp 10.000.',
            'nominal.required' => 'Nominal top up wajib diisi.',
        ]);

        $nominal = (int) $request->nominal;

        $pgate = Pgate::first();
        if (!$pgate || !$pgate->tclientkey || !$pgate->tserverkey) {
            return response()->json(['error' => 'Koneksi Midtrans belum tersedia.'], 500);
        }

        $orderId = 'TOPUP-' . $pelanggan->id_pelanggan . '-' . time();
        $notificationUrl = route('payment.notification');

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $nominal,
            ],
            'item_details' => [
                [
                    'id' => 'TOPUP-' . $pelanggan->id_pelanggan,
                    'price' => $nominal,
                    'quantity' => 1,
                    'name' => 'Top Up Saldo Akun Pelanggan',
                ]
            ],
            'customer_details' => [
                'first_name' => $pelanggan->nama_pelanggan,
                'phone' => $pelanggan->no_telp,
                'email' => $user->email ?? null,
            ],
            'callbacks' => [
                'finish' => route('dashboard'),
            ],
            'notification_url' => $notificationUrl,
            // Sesuai ketentuan: pembayaran menggunakan Midtrans QRIS
            'enabled_payments' => ['qris', 'gopay', 'shopeepay', 'other_qris'],
        ];

        $isSandbox = $pgate->mode === 'sandbox' || (!$pgate->mode && str_starts_with($pgate->tclientkey, 'SB-'));
        $apiUrl = $isSandbox
            ? 'https://app.sandbox.midtrans.com/snap/v1/transactions'
            : 'https://app.midtrans.com/snap/v1/transactions';

        $response = Http::withBasicAuth($pgate->tserverkey, '')
            ->withHeaders(['Accept' => 'application/json'])
            ->post($apiUrl, $payload);

        if (!$response->successful()) {
            return response()->json([
                'error' => 'Gagal membuat transaksi Midtrans QRIS.',
                'status' => $response->status(),
                'response' => $response->json(),
            ], 500);
        }

        session(['last_topup_order_id' => $orderId]);
        $responseData = $response->json();
        $responseData['order_id'] = $orderId;
        $responseData['nominal'] = $nominal;

        return response()->json($responseData);
    }

    public function topupFinish(Request $request)
    {
        $user = Auth::user();
        $pelanggan = $user->pelanggan;

        if (!$pelanggan) {
            return response()->json(['status' => 'error', 'message' => 'Pelanggan tidak ditemukan.'], 404);
        }

        $orderId = trim($request->input('order_id', ''));
        if (!$orderId) {
            $orderId = session('last_topup_order_id', '');
        }

        if (!$orderId || !str_starts_with($orderId, 'TOPUP-')) {
            return response()->json(['status' => 'error', 'message' => 'Order ID top up tidak ditemukan.'], 422);
        }

        $parts = explode('-', $orderId);
        if (count($parts) < 3 || (int)$parts[1] !== (int)$pelanggan->id_pelanggan) {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak: Order ID ini bukan milik akun Anda.'], 403);
        }

        // Cek jika sudah pernah diproses sebelumnya (idempoten)
        $existing = SaldoMutasi::where('keterangan', 'like', "%{$orderId}%")->first();
        if ($existing) {
            $freshSaldo = (int) ($pelanggan->fresh()->saldo ?? 0);
            return response()->json([
                'status' => 'success',
                'message' => 'Top up saldo telah berhasil diproses!',
                'saldo' => $freshSaldo,
                'saldo_formatted' => 'Rp ' . number_format($freshSaldo, 0, ',', '.'),
            ]);
        }

        // Verifikasi ke Midtrans API secara langsung menggunakan Server Key
        $pgate = Pgate::first();
        if (!$pgate || !$pgate->tserverkey) {
            return response()->json(['status' => 'error', 'message' => 'Koneksi Midtrans belum tersedia.'], 500);
        }

        $isSandbox = $pgate->mode === 'sandbox' || (!$pgate->mode && str_starts_with($pgate->tclientkey, 'SB-'));
        $baseUrl = $isSandbox ? 'https://api.sandbox.midtrans.com/v2' : 'https://api.midtrans.com/v2';

        $response = Http::withBasicAuth($pgate->tserverkey, '')
            ->get("{$baseUrl}/{$orderId}/status");

        if (!$response->successful()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi belum ditemukan di Midtrans (Status: ' . $response->status() . ').',
                'response' => $response->json(),
            ], 400);
        }

        $statusData = $response->json();
        $transactionStatus = $statusData['transaction_status'] ?? '';
        $fraudStatus = $statusData['fraud_status'] ?? '';

        $isSettled = ($transactionStatus === 'settlement') ||
                     ($transactionStatus === 'capture' && $fraudStatus === 'accept');

        if (!$isSettled) {
            return response()->json([
                'status' => 'pending',
                'transaction_status' => $transactionStatus,
                'message' => 'Pembayaran belum diselesaikan (Status Midtrans: ' . $transactionStatus . '). Silakan selesaikan pembayaran QRIS Anda terlebih dahulu.',
            ]);
        }

        return $this->processTopupSettlement($orderId, $statusData);
    }

    public function processTopupSettlement($orderId, $payload = [])
    {
        $parts = explode('-', $orderId);
        if (count($parts) < 3) {
            return response()->json(['status' => 'error', 'message' => 'Format Order ID tidak valid.'], 400);
        }

        $id_pelanggan = (int) $parts[1];
        $grossAmount = isset($payload['gross_amount']) ? (int) round((float) $payload['gross_amount']) : 0;

        // Cek jika sudah pernah dicatat mutasinya
        $existingMutasi = SaldoMutasi::where('keterangan', 'like', "%{$orderId}%")->first();
        if ($existingMutasi) {
            $currentSaldo = (int) ($existingMutasi->pelanggan->saldo ?? 0);
            return response()->json([
                'status' => 'success',
                'message' => 'Top up saldo telah berhasil diproses sebelumnya.',
                'saldo' => $currentSaldo,
                'saldo_formatted' => 'Rp ' . number_format($currentSaldo, 0, ',', '.'),
            ]);
        }

        $pelanggan = Pelanggan::find($id_pelanggan);
        if (!$pelanggan) {
            Log::error("Topup order {$orderId} failed: Pelanggan ID {$id_pelanggan} not found.");
            return response()->json(['status' => 'error', 'message' => 'Pelanggan tidak ditemukan.'], 404);
        }

        // Jika grossAmount tidak disertakan di payload, ambil langsung dari Midtrans API
        if ($grossAmount <= 0) {
            $pgate = Pgate::first();
            if ($pgate && $pgate->tserverkey) {
                $isSandbox = $pgate->mode === 'sandbox' || (!$pgate->mode && str_starts_with($pgate->tclientkey, 'SB-'));
                $baseUrl = $isSandbox ? 'https://api.sandbox.midtrans.com/v2' : 'https://api.midtrans.com/v2';
                $res = Http::withBasicAuth($pgate->tserverkey, '')->get("{$baseUrl}/{$orderId}/status");
                if ($res->successful()) {
                    $json = $res->json();
                    $grossAmount = (int) round((float) ($json['gross_amount'] ?? 0));
                }
            }
        }

        if ($grossAmount <= 0) {
            Log::error("Topup order {$orderId} failed: Gross amount 0 or invalid.");
            return response()->json(['status' => 'error', 'message' => 'Nominal top up tidak valid.'], 400);
        }

        DB::transaction(function() use ($pelanggan, $grossAmount, $orderId) {
            $locked = Pelanggan::lockForUpdate()->find($pelanggan->id_pelanggan);
            $saldoAwal = (int) ($locked->saldo ?? 0);
            $saldoAkhir = $saldoAwal + $grossAmount;
            $locked->update(['saldo' => $saldoAkhir]);

            SaldoMutasi::create([
                'id_pelanggan' => $locked->id_pelanggan,
                'tipe' => 'masuk',
                'jumlah' => $grossAmount,
                'saldo_awal' => $saldoAwal,
                'saldo_akhir' => $saldoAkhir,
                'kategori' => 'topup_qris',
                'keterangan' => "Top Up Saldo via Midtrans QRIS (Order ID: {$orderId})",
                'created_by' => null,
            ]);

            // Catat di Buku Kas sesuai ketentuan pembukuan
            DB::table('tb_kas')->insert([
                'tgl_kas' => date('Y-m-d'),
                'keterangan' => "[TOPUP SALDO QRIS] " . $locked->nama_pelanggan . " (" . $locked->kode_pelanggan . ") - Order: " . $orderId,
                'penerimaan' => $grossAmount,
                'pengeluaran' => 0,
                'bayar_saldo' => 0,
                'jenis_kas' => 'topup',
                'is_saldo' => 0,
                'id_tagihan' => null,
                'status' => 1,
            ]);
        });

        Log::info("Topup order {$orderId} successfully completed for Pelanggan {$pelanggan->nama_pelanggan} (+Rp {$grossAmount}).");

        $updatedPelanggan = Pelanggan::find($id_pelanggan);
        return response()->json([
            'status' => 'success',
            'message' => 'Top Up Saldo sebesar Rp ' . number_format($grossAmount, 0, ',', '.') . ' berhasil ditambahkan ke akun Anda!',
            'saldo' => (int) ($updatedPelanggan->saldo ?? 0),
            'saldo_formatted' => 'Rp ' . number_format($updatedPelanggan->saldo ?? 0, 0, ',', '.'),
        ]);
    }

    public function payWithFullSaldo(Request $request)
    {
        $user = Auth::user();
        $pelanggan = $user->pelanggan;

        if (!$pelanggan) {
            return response()->json(['error' => 'Pelanggan tidak ditemukan.'], 404);
        }

        $pelangganIds = $this->tagihanService->getPelangganIdsByPhone($pelanggan->no_telp);
        $invoices = $this->tagihanService->getUnpaidInvoices($pelangganIds);

        if ($invoices->isEmpty()) {
            return response()->json(['error' => 'Tidak ada tagihan yang harus dibayar.'], 422);
        }

        $subtotal = (int) $invoices->sum('amount');
        $currentSaldo = (int) ($pelanggan->saldo ?? 0);

        if ($currentSaldo < $subtotal) {
            return response()->json([
                'error' => 'Saldo akun Anda tidak mencukupi untuk membayar penuh. Saldo: Rp ' . number_format($currentSaldo, 0, ',', '.') . ', Total Tagihan: Rp ' . number_format($subtotal, 0, ',', '.'),
            ], 422);
        }

        $id_tagihan_list = $invoices->pluck('id')->toArray();

        try {
            DB::transaction(function() use ($pelanggan, $id_tagihan_list, $subtotal) {
                // Lock pelanggan row
                $lockedPelanggan = Pelanggan::lockForUpdate()->find($pelanggan->id_pelanggan);
                $saldoAwal = (int) ($lockedPelanggan->saldo ?? 0);
                if ($saldoAwal < $subtotal) {
                    throw new \Exception('Saldo pelanggan tidak mencukupi.');
                }
                $saldoAkhir = $saldoAwal - $subtotal;
                $lockedPelanggan->update(['saldo' => $saldoAkhir]);

                // Catat mutasi saldo keluar
                SaldoMutasi::create([
                    'id_pelanggan' => $lockedPelanggan->id_pelanggan,
                    'tipe' => 'keluar',
                    'jumlah' => $subtotal,
                    'saldo_awal' => $saldoAwal,
                    'saldo_akhir' => $saldoAkhir,
                    'kategori' => 'bayar_tagihan',
                    'keterangan' => 'Pembayaran Penuh Tagihan Internet menggunakan Saldo',
                    'reference_id' => 'SALDO-' . $lockedPelanggan->id_pelanggan . '-' . time(),
                ]);

                // Format nomor invoice
                $latestInvoice = DB::table('tb_tagihan')->orderBy('no_invoice', 'desc')->first();
                $no_spt = $latestInvoice ? $latestInvoice->no_invoice : '00000.BLR.MST.';
                $urut = (int) substr($no_spt, 0, 5);
                $format = str_pad($urut + 1, 5, '0', STR_PAD_LEFT) . '.BLR.MST.';

                $tgl_bayar = date('Y-m-d');
                $tanggal_sekarang = date('Y-m-d');

                $firstId = $id_tagihan_list[0];
                $data_tagihan = DB::table('tb_tagihan')
                    ->join('tb_pelanggan', 'tb_pelanggan.id_pelanggan', '=', 'tb_tagihan.id_pelanggan')
                    ->join('tb_paket', 'tb_paket.id_paket', '=', 'tb_pelanggan.paket')
                    ->where('tb_tagihan.id_tagihan', $firstId)
                    ->select('tb_tagihan.*', 'tb_pelanggan.*', 'tb_paket.nama_paket')
                    ->first();

                $jatuh_tempo = $data_tagihan ? $data_tagihan->jatuh_tempo : null;
                $settings = DB::table('tb_profile')->first();
                $tipe = $settings->tipe_jatuh_tempo ?? 'tanggal_tetap';
                $default_hari = $settings->hari_jatuh_tempo ?? 10;
                $adjust_late = $settings->adjust_due_date_late ?? 0;

                $due_day = $default_hari;
                if ($jatuh_tempo) {
                    $due_day = (int) date('d', strtotime($jatuh_tempo));
                } elseif ($tipe === 'tanggal_pasang' && $data_tagihan && !empty($data_tagihan->tgl_pemasangan)) {
                    $due_day = (int) date('d', strtotime($data_tagihan->tgl_pemasangan));
                }

                $is_late = false;
                if ($jatuh_tempo && $tanggal_sekarang > date('Y-m-d', strtotime($jatuh_tempo))) {
                    $is_late = true;
                }

                if ($is_late && $adjust_late == 1) {
                    $due_day = (int) date('d', strtotime($tanggal_sekarang));
                }

                if ($tanggal_sekarang < $jatuh_tempo) {
                    $jatuh_tempo_obj = new \DateTime($jatuh_tempo);
                } else {
                    $jatuh_tempo_obj = new \DateTime($tanggal_sekarang);
                }
                $jatuh_tempo_obj->modify('+1 Month');
                $next_year = (int)$jatuh_tempo_obj->format('Y');
                $next_month = (int)$jatuh_tempo_obj->format('m');

                $days_in_month = (int) date('t', strtotime($next_year . '-' . sprintf('%02d', $next_month) . '-01'));
                if ($due_day > $days_in_month) {
                    $due_day = $days_in_month;
                }

                $tgl_jatuh_tempo = sprintf('%04d-%02d-%02d 23:59:00', $next_year, $next_month, $due_day);

                foreach ($id_tagihan_list as $id_tagihan) {
                    $tagihan = DB::table('tb_tagihan')->where('id_tagihan', $id_tagihan)->first();
                    if (!$tagihan) continue;

                    $jml_bayar = (int) $tagihan->jml_bayar;

                    DB::table('tb_tagihan')->where('id_tagihan', $id_tagihan)->update([
                        'terbayar' => $jml_bayar,
                        'bayar_saldo' => $jml_bayar,
                        'bayar_non_saldo' => 0,
                        'status_bayar' => 1,
                        'tgl_bayar' => $tgl_bayar,
                        'blokir_status' => null,
                        'no_invoice' => $format,
                        'waktu_bayar' => now(),
                        'user_id' => null,
                        'metode_pembayaran' => 'Saldo',
                    ]);

                    $existsInKas = DB::table('tb_kas')->where('id_tagihan', $id_tagihan)->exists();
                    if (!$existsInKas) {
                        $ket = "[SALDO] Pembayaran Internet AN. " . ($data_tagihan->nama_pelanggan ?? $lockedPelanggan->nama_pelanggan) . ", Paket " . ($data_tagihan->nama_paket ?? '-');
                        DB::table('tb_kas')->insert([
                            'tgl_kas' => $tgl_bayar,
                            'keterangan' => $ket,
                            'penerimaan' => $jml_bayar,
                            'bayar_saldo' => $jml_bayar,
                            'jenis_kas' => 'saldo',
                            'is_saldo' => 1,
                            'id_tagihan' => $id_tagihan,
                            'status' => 1
                        ]);
                        \App\Models\Tagihan::allocateMitraCommission($id_tagihan);
                    }
                }

                $lockedPelanggan->update([
                    'jatuh_tempo' => $tgl_jatuh_tempo
                ]);
            });

            // Mikrotik unblock
            try {
                $checkUser = DB::table('tbl_penggunamikrotik')->first();
                $paidTagihans = DB::table('tb_tagihan')
                    ->join('tb_pelanggan', 'tb_pelanggan.id_pelanggan', '=', 'tb_tagihan.id_pelanggan')
                    ->leftJoin('tb_user', 'tb_user.id_pelanggan', '=', 'tb_pelanggan.id_pelanggan')
                    ->whereIn('tb_tagihan.id_tagihan', $id_tagihan_list)
                    ->select('tb_tagihan.id_tagihan', 'tb_pelanggan.*', 'tb_user.username as ppp_username')
                    ->get();

                $mikrotikGroups = [];
                foreach ($paidTagihans as $pt) {
                    $id_mikrotik = $pt->id_mikrotik ?: 1;
                    $mikrotikGroups[$id_mikrotik][] = $pt;
                }

                require_once base_path('include/routeros_api.php');

                foreach ($mikrotikGroups as $id_mikrotik => $items) {
                    $mikrotik = DB::table('tbl_mikrotik')->where('id_mikrotik', $id_mikrotik)->first();
                    if (!$mikrotik) continue;

                    $API = new \RouterosAPI();
                    if ($API->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
                        foreach ($items as $item) {
                            $ip_address = ($checkUser && ($checkUser->status == 'ya' || $checkUser->ippelanggan == 'dynamic'))
                                ? $item->ppp_username
                                : $item->ip_address;

                            if (!$ip_address) continue;

                            if ($checkUser && $checkUser->ippelanggan == 'statik') {
                                $commentToSearch = "Blokir Bulanan " . $ip_address;
                                $API->write('/ip/firewall/address-list/print', false);
                                $API->write('?comment=' . $commentToSearch);
                                $ips = $API->read();

                                if (!empty($ips)) {
                                    $API->write('/ip/firewall/address-list/remove', false);
                                    $API->write('=.id=' . $ips[0]['.id']);
                                    $API->read();
                                }
                            } else {
                                $paket = DB::table('tb_paket')->where('id_paket', $item->paket)->first();
                                $profile = $paket ? $paket->id_pmikrotik : '';

                                $API->write('/ppp/secret/print', false);
                                $API->write('?name=' . $ip_address);
                                $secrets = $API->read();

                                if (!empty($secrets)) {
                                    $currentProfile = $secrets[0]['profile'] ?? '';
                                    $isDisabled = ($secrets[0]['disabled'] ?? 'false') === 'true';

                                    if ($currentProfile === 'pppoe-isolir' || $isDisabled) {
                                        $API->comm('/ppp/secret/set', [
                                            'numbers' => $ip_address,
                                            'profile' => $profile
                                        ]);
                                        $API->comm('/ppp/secret/enable', ['numbers' => $ip_address]);

                                        $activeConnections = $API->comm('/ppp/active/print', [
                                            '?name' => $ip_address,
                                        ]);
                                        foreach ($activeConnections as $conn) {
                                            $API->comm('/ppp/active/remove', [
                                                '.id' => $conn['.id'],
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                        $API->disconnect();
                    }
                }
            } catch (\Exception $e) {
                Log::error('Full Saldo Mikrotik Unblock Error: ' . $e->getMessage());
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Pembayaran tagihan sebesar Rp ' . number_format($subtotal, 0, ',', '.') . ' berhasil dibayar menggunakan saldo akun Anda!',
                'redirect' => route('dashboard'),
            ]);
        } catch (\Exception $e) {
            Log::error('Full Saldo Payment Error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal memproses pembayaran saldo: ' . $e->getMessage()], 500);
        }
    }

public function notification(Request $request)
    {
        $pgate = Pgate::first();
        if (!$pgate) {
            return response('Configuration not found', 404);
        }

        $payload = $request->all();
        $orderId = $payload['order_id'] ?? '';

        // Cek jika ini adalah tes notifikasi dari Dashboard Midtrans
        if ($orderId && str_starts_with($orderId, 'payment_notif_test')) {
            Log::info('Midtrans Webhook: Test notification received successfully.', ['payload' => $payload]);
            return response()->json(['status' => 'ok', 'message' => 'Test notification received successfully']);
        }

        // Cek jika ini adalah notifikasi non-transaksi (seperti subscription atau account linking)
        if (!$orderId) {
            Log::info('Midtrans Webhook: Non-transaction notification received.', ['payload' => $payload]);
            return response()->json(['status' => 'ok', 'message' => 'Non-transaction notification received successfully']);
        }

        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $signatureKey = $payload['signature_key'] ?? '';

        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . trim($pgate->tserverkey));
        if (!hash_equals($expectedSignature, $signatureKey)) {
            Log::warning('Midtrans Webhook: Signature mismatch', [
                'order_id' => $orderId,
                'signature' => $signatureKey,
                'expected' => $expectedSignature
            ]);
            return response('Invalid signature', 403);
        }

        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus = $payload['fraud_status'] ?? '';

        $isSettled = ($transactionStatus === 'settlement') ||
                     ($transactionStatus === 'capture' && $fraudStatus === 'accept');

        if (!$isSettled) {
            return response()->json(['status' => 'ok', 'message' => 'Transaction status: ' . $transactionStatus]);
        }

        // Webhook otomatis untuk Top Up Saldo Pelanggan
        if (str_starts_with($orderId, 'TOPUP-')) {
            Log::info("Midtrans Webhook: Memproses penambahan saldo untuk {$orderId}", ['payload' => $payload]);
            return $this->processTopupSettlement($orderId, $payload);
        }

        $id_tagihan_list = [];

        if (str_starts_with($orderId, 'COMBINED-')) {
            $parts = explode('-', $orderId);
            array_shift($parts); // remove COMBINED
            array_pop($parts);   // remove timestamp
            $id_tagihan_list = $parts;
        } elseif (str_starts_with($orderId, 'tagihan-')) {
            $parts = explode('-', $orderId);
            $id_pelanggan = $parts[1];
            $timestamp = isset($parts[2]) ? (int)$parts[2] : time();
            $targetMonth = date('mY', $timestamp);
            $splitSaldoDeduct = 0;
            if (isset($parts[3]) && str_starts_with($parts[3], 's')) {
                $splitSaldoDeduct = (int) substr($parts[3], 1);
            }

            $pelanggan = DB::table('tb_pelanggan')->where('id_pelanggan', $id_pelanggan)->first();

            if ($pelanggan && $pelanggan->no_telp) {
                $allIds = DB::table('tb_pelanggan')
                    ->where('no_telp', $pelanggan->no_telp)
                    ->pluck('id_pelanggan')
                    ->toArray();
                $inIds = $allIds;
            } else {
                $inIds = [$id_pelanggan];
            }

            $id_tagihan_list = $this->tagihanService
                ->getUnpaidInvoices($inIds, $targetMonth)
                ->pluck('id')
                ->toArray();
        } else {
            $pieces = explode('-', $orderId);
            $id_tagihan_list = [$pieces[0]];
        }

        if (empty($id_tagihan_list)) {
            return response()->json(['status' => 'ok', 'message' => 'No unpaid tagihan list found.']);
        }

        $total_jml_bayar = 0;
        $data_tagihan = null;

        try {
            $splitSaldoParam = $splitSaldoDeduct ?? 0;
            DB::transaction(function() use ($id_tagihan_list, &$total_jml_bayar, &$data_tagihan, $splitSaldoParam, $orderId) {
                $latestInvoice = DB::table('tb_tagihan')->orderBy('no_invoice', 'desc')->first();
                $no_spt = $latestInvoice ? $latestInvoice->no_invoice : '00000.BLR.MST.';
                $urut = (int) substr($no_spt, 0, 5);
                $format = str_pad($urut + 1, 5, '0', STR_PAD_LEFT) . '.BLR.MST.';

                $tgl_bayar = date('Y-m-d');
                $jam_sekarang = date('H:i:s');
                $tanggal_sekarang = date('Y-m-d');

                $firstId = $id_tagihan_list[0];
                $data_tagihan = DB::table('tb_tagihan')
                    ->join('tb_pelanggan', 'tb_pelanggan.id_pelanggan', '=', 'tb_tagihan.id_pelanggan')
                    ->join('tb_paket', 'tb_paket.id_paket', '=', 'tb_pelanggan.paket')
                    ->leftJoin('tb_user', 'tb_user.id_pelanggan', '=', 'tb_pelanggan.id_pelanggan')
                    ->where('tb_tagihan.id_tagihan', $firstId)
                    ->select('tb_tagihan.*', 'tb_pelanggan.*', 'tb_paket.nama_paket', 'tb_user.username as ppp_username')
                    ->first();

                if (!$data_tagihan) {
                    throw new \Exception("Tagihan data not found for id: " . $firstId);
                }

                $id_pelanggan = $data_tagihan->id_pelanggan;
                $jatuh_tempo = $data_tagihan->jatuh_tempo;

                // Ambil pengaturan jatuh tempo global
                $settings = DB::table('tb_profile')->first();
                $tipe = $settings->tipe_jatuh_tempo ?? 'tanggal_tetap';
                $default_hari = $settings->hari_jatuh_tempo ?? 10;
                $adjust_late = $settings->adjust_due_date_late ?? 0;

                $due_day = $default_hari;
                if ($jatuh_tempo) {
                    $due_day = (int) date('d', strtotime($jatuh_tempo));
                } elseif ($tipe === 'tanggal_pasang' && $data_tagihan && !empty($data_tagihan->tgl_pemasangan)) {
                    $due_day = (int) date('d', strtotime($data_tagihan->tgl_pemasangan));
                }

                $is_late = false;
                if ($jatuh_tempo && $tanggal_sekarang > date('Y-m-d', strtotime($jatuh_tempo))) {
                    $is_late = true;
                }

                if ($is_late && $adjust_late == 1) {
                    // Bergeser ke tanggal pembayaran terakhir (hari ini)
                    $due_day = (int) date('d', strtotime($tanggal_sekarang));
                }

                if ($tanggal_sekarang < $jatuh_tempo) {
                    $jatuh_tempo_obj = new \DateTime($jatuh_tempo);
                } else {
                    $jatuh_tempo_obj = new \DateTime($tanggal_sekarang);
                }
                $jatuh_tempo_obj->modify('+1 Month');
                $next_year = (int)$jatuh_tempo_obj->format('Y');
                $next_month = (int)$jatuh_tempo_obj->format('m');

                // Cari jumlah hari maksimum di bulan target
                $days_in_month = (int) date('t', strtotime($next_year . '-' . sprintf('%02d', $next_month) . '-01'));
                if ($due_day > $days_in_month) {
                    $due_day = $days_in_month;
                }

                $tgl_jatuh_tempo = sprintf('%04d-%02d-%02d 23:59:00', $next_year, $next_month, $due_day);

                $saldoSisaDipotong = $splitSaldoParam;
                if ($splitSaldoParam > 0) {
                    $lockedPel = \App\Models\Pelanggan::lockForUpdate()->find($id_pelanggan);
                    if ($lockedPel) {
                        $saldoAwal = (int) ($lockedPel->saldo ?? 0);
                        $potongNyata = min($saldoAwal, $splitSaldoParam);
                        $saldoAkhir = $saldoAwal - $potongNyata;
                        $lockedPel->update(['saldo' => $saldoAkhir]);

                        \App\Models\SaldoMutasi::create([
                            'id_pelanggan' => $id_pelanggan,
                            'tipe' => 'keluar',
                            'jumlah' => $potongNyata,
                            'saldo_awal' => $saldoAwal,
                            'saldo_akhir' => $saldoAkhir,
                            'kategori' => 'bayar_tagihan',
                            'keterangan' => 'Pembayaran Sebagian Tagihan Internet via Saldo (Split Midtrans)',
                            'reference_id' => $orderId,
                        ]);
                        $saldoSisaDipotong = $potongNyata;
                    }
                }

                $total_jml_bayar = 0;
                foreach ($id_tagihan_list as $id_tagihan) {
                    $tagihan = DB::table('tb_tagihan')->where('id_tagihan', $id_tagihan)->first();
                    if (!$tagihan) continue;

                    $jml_bayar = (int) $tagihan->jml_bayar;
                    $total_jml_bayar += $jml_bayar;

                    $tagihanSaldo = 0;
                    if ($saldoSisaDipotong > 0) {
                        $tagihanSaldo = min($jml_bayar, $saldoSisaDipotong);
                        $saldoSisaDipotong -= $tagihanSaldo;
                    }
                    $tagihanNonSaldo = $jml_bayar - $tagihanSaldo;

                    DB::table('tb_tagihan')->where('id_tagihan', $id_tagihan)->update([
                        'terbayar' => $jml_bayar,
                        'bayar_saldo' => $tagihanSaldo,
                        'bayar_non_saldo' => $tagihanNonSaldo,
                        'status_bayar' => 1,
                        'tgl_bayar' => $tgl_bayar,
                        'blokir_status' => null,
                        'no_invoice' => $format,
                        'waktu_bayar' => now(),
                        'user_id' => null,
                        'metode_pembayaran' => $tagihanSaldo > 0 ? 'Saldo + Midtrans' : 'Midtrans',
                    ]);

                    $existsInKas = DB::table('tb_kas')->where('id_tagihan', $id_tagihan)->exists();
                    if (!$existsInKas) {
                        $ket = "Pembayaran Internet AN. " . $data_tagihan->nama_pelanggan . ", Paket " . $data_tagihan->nama_paket;
                        if ($tagihanSaldo > 0) {
                            $ket = "[SPLIT SALDO] " . $ket . " (Midtrans: Rp " . number_format($tagihanNonSaldo, 0, ',', '.') . ", Saldo: Rp " . number_format($tagihanSaldo, 0, ',', '.') . ")";
                        }
                        DB::table('tb_kas')->insert([
                            'tgl_kas' => $tgl_bayar,
                            'keterangan' => $ket,
                            'penerimaan' => $tagihanNonSaldo,
                            'bayar_saldo' => $tagihanSaldo,
                            'is_saldo' => 0,
                            'jenis_kas' => 'tagihan',
                            'id_tagihan' => $id_tagihan,
                            'status' => 1
                        ]);
                        \App\Models\Tagihan::allocateMitraCommission($id_tagihan);
                    }
                }

                DB::table('tb_pelanggan')->where('id_pelanggan', $id_pelanggan)->update([
                    'jatuh_tempo' => $tgl_jatuh_tempo
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Midtrans Webhook Database Transaction Error: ' . $e->getMessage());
            return response('Database error', 500);
        }

        // Buka blokir Mikrotik untuk semua tagihan yang dibayar
        if (!empty($id_tagihan_list)) {
            try {
                $checkUser = DB::table('tbl_penggunamikrotik')->first();
                
                $paidTagihans = DB::table('tb_tagihan')
                    ->join('tb_pelanggan', 'tb_pelanggan.id_pelanggan', '=', 'tb_tagihan.id_pelanggan')
                    ->leftJoin('tb_user', 'tb_user.id_pelanggan', '=', 'tb_pelanggan.id_pelanggan')
                    ->whereIn('tb_tagihan.id_tagihan', $id_tagihan_list)
                    ->select('tb_tagihan.id_tagihan', 'tb_pelanggan.*', 'tb_user.username as ppp_username')
                    ->get();

                // Group by mikrotik to reuse connection
                $mikrotikGroups = [];
                foreach ($paidTagihans as $pt) {
                    $id_mikrotik = $pt->id_mikrotik ?: 1;
                    $mikrotikGroups[$id_mikrotik][] = $pt;
                }

                require_once base_path('include/routeros_api.php');

                foreach ($mikrotikGroups as $id_mikrotik => $items) {
                    $mikrotik = DB::table('tbl_mikrotik')->where('id_mikrotik', $id_mikrotik)->first();
                    if (!$mikrotik) continue;

                    $API = new \RouterosAPI();
                    if ($API->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
                        foreach ($items as $item) {
                            $ip_address = ($checkUser && ($checkUser->status == 'ya' || $checkUser->ippelanggan == 'dynamic'))
                                ? $item->ppp_username
                                : $item->ip_address;

                            if (!$ip_address) continue;

                            if ($checkUser && $checkUser->ippelanggan == 'statik') {
                                $commentToSearch = "Blokir Bulanan " . $ip_address;
                                $API->write('/ip/firewall/address-list/print', false);
                                $API->write('?comment=' . $commentToSearch);
                                $ips = $API->read();

                                if (!empty($ips)) {
                                    foreach ($ips as $ip_data) {
                                        $API->write('/ip/firewall/address-list/remove', false);
                                        $API->write('=.id=' . $ip_data['.id']);
                                        $API->read();
                                    }
                                }
                            } else {
                                $paket = DB::table('tb_paket')->where('id_paket', $item->paket)->first();
                                $profile = $paket ? $paket->id_pmikrotik : 'default';

                                $secrets = $API->comm('/ppp/secret/print', [
                                    '?name' => $ip_address,
                                ]);

                                $isCurrentlyBlocked = false;
                                if (!empty($secrets)) {
                                    $secret = $secrets[0];
                                    $currentProfile = $secret['profile'] ?? '';
                                    $isDisabled = ($secret['disabled'] ?? 'false') === 'true';
                                    if ($currentProfile === 'pppoe-isolir' || $isDisabled) {
                                        $isCurrentlyBlocked = true;
                                    }
                                }

                                if ($isCurrentlyBlocked) {
                                    $API->comm('/ppp/secret/set', [
                                        'numbers' => $ip_address,
                                        'profile' => $profile
                                    ]);
                                    $API->comm('/ppp/secret/enable', ['numbers' => $ip_address]);

                                    // Putuskan koneksi aktif agar langsung dial ulang dengan profile baru
                                    $activeConnections = $API->comm('/ppp/active/print', [
                                        '?name' => $ip_address,
                                    ]);
                                    foreach ($activeConnections as $conn) {
                                        $API->comm('/ppp/active/remove', [
                                            '.id' => $conn['.id'],
                                        ]);
                                    }
                                }
                            }
                        }
                        $API->disconnect();
                    }
                }
            } catch (\Exception $e) {
                Log::error('Midtrans Webhook Mikrotik Bulk Unblock Error: ' . $e->getMessage());
            }
        }

        // WhatsApp Notification
        if ($data_tagihan) {
            try {
                $row_token = DB::table('tbl_token')->where('id_token', 1)->where('status', 'aktif')->first();
                $bayar = DB::table('tbl_notifbayar')->first();

                if ($row_token && $bayar && !empty($bayar->pesan_bayar)) {
                    $sekarangs = date('d F Y H:i:s');
                    $pesanBayar = $bayar->pesan_bayar;
                    $pesanBayar = str_replace('$nama', $data_tagihan->nama_pelanggan, $pesanBayar);
                    $pesanBayar = str_replace('$tagihan', 'Rp. ' . number_format($total_jml_bayar, 0, ',', '.'), $pesanBayar);
                    $pesanBayar = str_replace('$harinin', $sekarangs, $pesanBayar);
                    $pesanBayar = str_replace('$no_telp', $data_tagihan->no_telp, $pesanBayar);

                    $templateParams = [];
                    if (!empty($bayar->template_params)) {
                        $paramsList = explode(',', $bayar->template_params);
                        foreach ($paramsList as $param) {
                            $param = trim($param);
                            if ($param === 'nama') $templateParams[] = $data_tagihan->nama_pelanggan ?? $data_tagihan->nama ?? '';
                            elseif ($param === 'no_telp') $templateParams[] = $data_tagihan->no_telp ?? '';
                            elseif ($param === 'jatuh_tempo') $templateParams[] = \Carbon\Carbon::parse($data_tagihan->jatuh_tempo ?? $data_tagihan->jatuh_tempo)->translatedFormat('d F Y');
                                elseif ($param === 'tagihan') $templateParams[] = number_format($data_tagihan->jml_bayar, 0, ',', '.');
                            elseif ($param === 'hari_ini') $templateParams[] = \Carbon\Carbon::now()->translatedFormat('d F Y');
                            else $templateParams[] = $param;
                        }
                    }
                    app(\App\Services\WhatsAppService::class)->sendTemplateMessage($data_tagihan->no_telp, $pesanBayar, $bayar->template_name ?? null, $templateParams, $bayar->template_language ?? 'id');
                }
            } catch (\Exception $e) {
                Log::error('Midtrans Webhook WhatsApp Notification Error: ' . $e->getMessage());
            }
        }

        return response('OK', 200);
    }

    public function printInvoice($id)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $pelanggan = $user->pelanggan;
        
        if (!$pelanggan) {
            abort(404, 'Pelanggan tidak ditemukan.');
        }

        $pelangganIds = $this->tagihanService->getPelangganIdsByPhone($pelanggan->no_telp);

        $tagihan = \App\Models\Tagihan::with(['pelanggan.paketDetail'])->whereIn('id_pelanggan', $pelangganIds)->findOrFail($id);

        $profile = \Illuminate\Support\Facades\DB::table('tb_profile')->first();
        if ($profile && !isset($profile->telepon)) {
            $profile->telepon = $profile->telpon ?? '';
        }

        if (empty($tagihan->no_invoice)) {
            $tagihan->no_invoice = 'INV/' . $tagihan->bulan_tahun . '/' . str_pad($tagihan->id_tagihan, 4, '0', STR_PAD_LEFT);
        }

        return view('admin.transaksi.print_invoice', compact('tagihan', 'profile'));
    }
}
