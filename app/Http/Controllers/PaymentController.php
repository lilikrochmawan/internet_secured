<?php

namespace App\Http\Controllers;

use App\Models\Pgate;
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

        return view('payment.detail', [
            'pelanggan' => $pelanggan,
            'invoices' => $invoices,
            'subtotal' => $subtotal,
            'adminFee' => $adminFee,
            'totalPayment' => $totalPayment,
            'clientKey' => $clientKey,
            'snapJsUrl' => $snapJsUrl,
            'isSandbox' => $isSandbox,
            'jumlahAkunGabung' => $jumlahAkunGabung,
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

        if ($adminFee > 0) {
            $items[] = [
                'id' => 'ADMIN-001',
                'price' => $adminFee,
                'quantity' => 1,
                'name' => $feeName,
            ];
        }

        $totalPayment = $subtotal + $adminFee;
        $orderId = 'tagihan-' . $pelanggan->id_pelanggan . '-' . time();

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

            $tagihans = DB::table('tb_tagihan')
                ->whereIn('id_pelanggan', $inIds)
                ->where(function($q) {
                    $q->whereNull('status_bayar')
                      ->orWhereIn('status_bayar', [0, '0', 'belum', '']);
                })
                ->where(function($q) use ($targetMonth) {
                    $q->where(function($sub) use ($targetMonth) {
                        $sub->where(function($sub2) {
                                $sub2->whereNull('manual_invoice')->orWhere('manual_invoice', 0);
                            })
                            ->where('bulan_tahun', $targetMonth);
                    })->orWhere('manual_invoice', 1);
                })
                ->pluck('id_tagihan')
                ->toArray();

            $id_tagihan_list = $tagihans;
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
            DB::transaction(function() use ($id_tagihan_list, &$total_jml_bayar, &$data_tagihan) {
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

                if ($tanggal_sekarang < $jatuh_tempo) {
                    $jatuh_tempo_obj = new \DateTime($jatuh_tempo);
                } else {
                    $jatuh_tempo_obj = new \DateTime($tanggal_sekarang);
                }
                $jatuh_tempo_obj->modify('+1 Month');
                $next_year = (int)$jatuh_tempo_obj->format('Y');
                $next_month = (int)$jatuh_tempo_obj->format('m');

                // Ambil pengaturan jatuh tempo global
                $settings = DB::table('tb_profile')->first();
                $tipe = $settings->tipe_jatuh_tempo ?? 'tanggal_tetap';
                $default_hari = $settings->hari_jatuh_tempo ?? 10;

                $due_day = $default_hari;
                if ($tipe === 'tanggal_pasang' && $data_tagihan && !empty($data_tagihan->tgl_pemasangan)) {
                    $due_day = (int) date('d', strtotime($data_tagihan->tgl_pemasangan));
                }

                // Cari jumlah hari maksimum di bulan target
                $days_in_month = (int) date('t', strtotime($next_year . '-' . sprintf('%02d', $next_month) . '-01'));
                if ($due_day > $days_in_month) {
                    $due_day = $days_in_month;
                }

                $tgl_jatuh_tempo = sprintf('%04d-%02d-%02d 23:59:00', $next_year, $next_month, $due_day);

                $total_jml_bayar = 0;
                foreach ($id_tagihan_list as $id_tagihan) {
                    $tagihan = DB::table('tb_tagihan')->where('id_tagihan', $id_tagihan)->first();
                    if (!$tagihan) continue;

                    $jml_bayar = $tagihan->jml_bayar;
                    $total_jml_bayar += $jml_bayar;

                    DB::table('tb_tagihan')->where('id_tagihan', $id_tagihan)->update([
                        'terbayar' => $jml_bayar,
                        'status_bayar' => 1,
                        'tgl_bayar' => $tgl_bayar,
                        'blokir_status' => null,
                        'no_invoice' => $format,
                        'waktu_bayar' => now(),
                        'user_id' => null
                    ]);

                    $existsInKas = DB::table('tb_kas')->where('id_tagihan', $id_tagihan)->exists();
                    if (!$existsInKas) {
                        $ket = "Pembayaran Internet AN. " . $data_tagihan->nama_pelanggan . ", Paket " . $data_tagihan->nama_paket;
                        DB::table('tb_kas')->insert([
                            'tgl_kas' => $tgl_bayar,
                            'keterangan' => $ket,
                            'penerimaan' => $jml_bayar,
                            'id_tagihan' => $id_tagihan
                        ]);
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

        // Buka blokir Mikrotik
        if ($data_tagihan) {
            try {
                $checkUser = DB::table('tbl_penggunamikrotik')->first();
                $id_mikrotik = $data_tagihan->id_mikrotik ?: 1;
                $mikrotik = DB::table('tbl_mikrotik')->where('id_mikrotik', $id_mikrotik)->first();

                $ip_address = ($checkUser && ($checkUser->status == 'ya' || $checkUser->ippelanggan == 'dynamic'))
                    ? $data_tagihan->ppp_username
                    : $data_tagihan->ip_address;

                if ($mikrotik && $ip_address) {
                    require_once base_path('include/routeros_api.php');
                    $API = new \RouterosAPI();

                    if ($API->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
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
                            $paket = DB::table('tb_paket')->where('id_paket', $data_tagihan->paket)->first();
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
                        $API->disconnect();
                    }
                }
            } catch (\Exception $e) {
                Log::error('Midtrans Webhook Mikrotik Error: ' . $e->getMessage());
            }
        }

        // WhatsApp Notification
        if ($data_tagihan) {
            try {
                $row_token = DB::table('tbl_token')->where('id_token', 1)->where('status', 'aktif')->first();
                $bayar = DB::table('tbl_notifbayar')->first();

                if ($row_token && !empty($row_token->token) && $bayar && !empty($bayar->pesan_bayar)) {
                    $sekarangs = date('d F Y H:i:s');
                    $pesanBayar = $bayar->pesan_bayar;
                    $pesanBayar = str_replace('$nama', $data_tagihan->nama_pelanggan, $pesanBayar);
                    $pesanBayar = str_replace('$tagihan', 'Rp. ' . number_format($total_jml_bayar, 0, ',', '.'), $pesanBayar);
                    $pesanBayar = str_replace('$harinin', $sekarangs, $pesanBayar);
                    $pesanBayar = str_replace('$no_telp', $data_tagihan->no_telp, $pesanBayar);

                    Http::withHeaders([
                        'Authorization' => $row_token->token
                    ])->post('https://api.fonnte.com/send', [
                        'target' => $data_tagihan->no_telp,
                        'message' => $pesanBayar,
                        'countryCode' => '62'
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Midtrans Webhook WhatsApp Notification Error: ' . $e->getMessage());
            }
        }

        return response('OK', 200);
    }
}
