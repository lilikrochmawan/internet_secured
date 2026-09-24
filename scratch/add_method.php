<?php
$file = 'app/Http/Controllers/PaymentController.php';
$content = file_get_contents($file);

$insertPos = strrpos($content, '}'); // Find last closing brace

$method = '
    public function printInvoice($id)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $pelanggan = $user->pelanggan;
        
        if (!$pelanggan) {
            abort(404, \'Pelanggan tidak ditemukan.\');
        }

        $pelangganIds = $this->tagihanService->getPelangganIdsByPhone($pelanggan->no_telp);

        $tagihan = \App\Models\Tagihan::with([\'pelanggan.paketDetail\'])->whereIn(\'id_pelanggan\', $pelangganIds)->findOrFail($id);

        $profile = \Illuminate\Support\Facades\DB::table(\'tb_profile\')->first();
        if ($profile && !isset($profile->telepon)) {
            $profile->telepon = $profile->telpon ?? \'\';
        }

        if (empty($tagihan->no_invoice)) {
            $tagihan->no_invoice = \'INV/\' . $tagihan->bulan_tahun . \'/\' . str_pad($tagihan->id_tagihan, 4, \'0\', STR_PAD_LEFT);
        }

        return view(\'admin.transaksi.print_invoice\', compact(\'tagihan\', \'profile\'));
    }
';

$newContent = substr_replace($content, $method . "}\n", $insertPos);
file_put_contents($file, $newContent);
echo "Method added to PaymentController.\n";
