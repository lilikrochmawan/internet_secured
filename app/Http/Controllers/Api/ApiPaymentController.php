<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pgate;
use App\Services\TagihanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class ApiPaymentController extends Controller
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
            return response()->json([
                'success' => false,
                'message' => 'Pelanggan tidak ditemukan.'
            ], 404);
        }

        $pelangganIds = $this->tagihanService->getPelangganIdsByPhone($pelanggan->no_telp);
        $invoices = $this->tagihanService->getUnpaidInvoices($pelangganIds);
        $jumlahAkunGabung = count($pelangganIds);

        $subtotal = $invoices->sum('amount');
        
        $profile = DB::table('tb_profile')->where('id_profile', 1)->first();
        $feeType = $profile->admin_fee_type ?? 'flat';

        // Hitung biaya admin default (qris)
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

        if ($feeType === 'flat') {
            $adminFee = (int) ($profile->admin_fee_flat ?? 2000);
        } else {
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

        $isPpnActive = (($profile->tax_ppn_status ?? 'tidak') === 'aktif');
        $isPpnCharged = (($profile->tax_ppn_charged ?? 'ya') === 'ya');
        $showPpn = $isPpnActive && $isPpnCharged;
        $ppnRate = (double)($profile->tax_ppn_rate ?? 11.00);

        $ppnAmount = 0;
        $baseSubtotal = $subtotal;
        if ($showPpn) {
            $ppnAmount = (int) round($subtotal * ($ppnRate / 100));
            $totalPayment = $subtotal + $ppnAmount + $adminFee;
        } else {
            $totalPayment = $subtotal + $adminFee;
        }

        $pgate = Pgate::first();
        $clientKey = $pgate?->tclientkey;

        $isSandbox = $pgate && ($pgate->mode === 'sandbox' || (!$pgate->mode && str_starts_with($clientKey, 'SB-')));

        return response()->json([
            'success' => true,
            'data' => [
                'invoices' => $invoices,
                'subtotal' => $subtotal,
                'admin_fee' => $adminFee,
                'show_ppn' => $showPpn,
                'ppn_rate' => $ppnRate,
                'ppn_amount' => $ppnAmount,
                'base_subtotal' => $baseSubtotal,
                'total_payment' => $totalPayment,
                'client_key' => $clientKey,
                'is_sandbox' => $isSandbox,
                'jumlah_akun_gabung' => $jumlahAkunGabung,
            ]
        ]);
    }

    public function charge(Request $request)
    {
        $user = Auth::user();
        $pelanggan = $user->pelanggan;

        if (!$pelanggan) {
            return response()->json(['success' => false, 'message' => 'Pelanggan tidak ditemukan.'], 404);
        }

        $pgate = Pgate::first();
        if (!$pgate || !$pgate->tclientkey || !$pgate->tserverkey) {
            return response()->json(['success' => false, 'message' => 'Koneksi Midtrans belum tersedia.'], 500);
        }

        $clientKey = trim($pgate->tclientkey);
        $serverKey = trim($pgate->tserverkey);

        $usingSandboxClient = str_starts_with($clientKey, 'SB-');
        $usingSandboxServer = str_starts_with($serverKey, 'SB-');

        if ($usingSandboxClient !== $usingSandboxServer) {
            return response()->json([
                'success' => false,
                'message' => 'Sandbox/production key tidak konsisten.'
            ], 500);
        }

        $pelangganIds = $this->tagihanService->getPelangganIdsByPhone($pelanggan->no_telp);
        $invoices = $this->tagihanService->getUnpaidInvoices($pelangganIds);

        if ($invoices->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Tidak ada tagihan yang harus dibayar.'], 422);
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
                'name' => strlen($label) > 50 ? substr($label, 0, 47) . '...' : $label, // Midtrans name max 50 chars
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
                    return response()->json(['success' => false, 'message' => 'Metode pembayaran QRIS dinonaktifkan oleh administrator.'], 422);
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
                    return response()->json(['success' => false, 'message' => 'Metode pembayaran VA dinonaktifkan oleh administrator.'], 422);
                }
                $adminFee = (int) ($profile->admin_fee_va ?? 4000);
                $feeName = 'Biaya Admin VA';
            } elseif ($paymentMethod === 'retail') {
                if (($profile->admin_fee_retail_status ?? 1) == 0) {
                    return response()->json(['success' => false, 'message' => 'Metode pembayaran retail dinonaktifkan oleh administrator.'], 422);
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

        $orderId = 'tagihan-' . $pelanggan->id_pelanggan . '-' . time();
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
                'finish' => route('dashboard'), // will redirect back to web dashboard or handled in mobile app
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
                'success' => false,
                'message' => 'Gagal membuat transaksi Midtrans.',
                'error_detail' => $response->json(),
            ], 500);
        }

        $resData = $response->json();
        return response()->json([
            'success' => true,
            'token' => $resData['token'],
            'redirect_url' => $resData['redirect_url'],
        ]);
    }
}
