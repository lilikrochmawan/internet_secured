<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Pelanggan;
use App\Models\Tagihan;

class GenerateBulananTagihan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-bulanan-tagihan {--month= : Bulan format MM} {--year= : Tahun format YYYY}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate regular monthly bills for customers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $bulan = $this->option('month') ?: date('m');
        $tahun = $this->option('year') ?: date('Y');
        $bulantahun = $bulan . $tahun;

        $this->info("Starting billing generation for period: {$bulan}-{$tahun} ({$bulantahun})...");
        Log::info("GenerateBulananTagihan: Starting monthly billing generation for period: {$bulantahun}");

        // PPN setting
        $ppn_aktif = false;
        $paketSettings = DB::table('tbl_paketmikrotik')->first();
        if ($paketSettings && isset($paketSettings->ppn) && $paketSettings->ppn === 'aktif') {
            $ppn_aktif = true;
        }

        // Global billing settings
        $settings = DB::table('tb_profile')->first();
        $tipe = $settings->tipe_jatuh_tempo ?? 'tanggal_tetap';
        $default_hari = $settings->hari_jatuh_tempo ?? 10;
        $sistem = $settings->sistem_billing ?? 'prabayar';

        // Find customers who do not have a bill for this month-year
        $pelanggans = Pelanggan::with('paketDetail')
            ->whereNotExists(function ($query) use ($bulantahun) {
                $query->select(DB::raw(1))
                    ->from('tb_tagihan')
                    ->whereColumn('tb_tagihan.id_pelanggan', 'tb_pelanggan.id_pelanggan')
                    ->where('tb_tagihan.bulan_tahun', $bulantahun);
            })
            ->get();

        if ($pelanggans->isEmpty()) {
            $this->info('No eligible customers found for this period. All bills might already be generated.');
            Log::info("GenerateBulananTagihan: No eligible customers found for period: {$bulantahun}");
            return Command::SUCCESS;
        }

        $generated = 0;

        foreach ($pelanggans as $plg) {
            $harga_paket = $plg->paketDetail->harga ?? 0;
            $ppn_rate = $plg->paketDetail->ppn ?? 0;
            
            if ($ppn_aktif) {
                $total_tagihan = $harga_paket + ($harga_paket * $ppn_rate);
            } else {
                $total_tagihan = $harga_paket;
            }

            // Skip if price is 0 or not configured properly (to prevent empty invoices unless package is free)
            if ($total_tagihan <= 0) {
                continue;
            }

            // Calculate due date (jatuh tempo)
            $target_date = \Carbon\Carbon::create((int)$tahun, (int)$bulan, 1);
            
            // Jika pascabayar, jatuh tempo adalah di bulan selanjutnya (+1 bulan)
            if ($sistem === 'pascabayar') {
                $target_date->addMonth();
            }

            $due_year = $target_date->year;
            $due_month = $target_date->month;

            $due_day = $default_hari;
            if (!empty($plg->jatuh_tempo)) {
                $due_day = (int) date('d', strtotime($plg->jatuh_tempo));
            } elseif ($tipe === 'tanggal_pasang' && !empty($plg->tgl_pemasangan)) {
                $due_day = (int) date('d', strtotime($plg->tgl_pemasangan));
            }

            // Cari jumlah hari maksimum di bulan target
            $days_in_month = (int) date('t', strtotime($due_year . '-' . sprintf('%02d', $due_month) . '-01'));
            if ($due_day > $days_in_month) {
                $due_day = $days_in_month;
            }

            $tgl_jatuh_tempo = sprintf('%04d-%02d-%02d 23:59:00', $due_year, $due_month, $due_day);

            // Cek apakah pelanggan sedang dalam masa promo aktif pada bulan & tahun target
            $activePromo = \App\Models\Promo::getActivePromoForPeriod($plg->id_pelanggan, $bulan, $tahun);

            DB::transaction(function () use ($plg, $bulantahun, $total_tagihan, $tgl_jatuh_tempo, $activePromo) {
                $status_bayar = $activePromo ? 1 : null;
                $terbayar = $activePromo ? $total_tagihan : null;
                $waktu_bayar = $activePromo ? now()->format('Y-m-d H:i:s') : null;

                // Insert bill
                DB::table('tb_tagihan')->insert([
                    'id_pelanggan' => $plg->id_pelanggan,
                    'bulan_tahun' => $bulantahun,
                    'jml_bayar' => $total_tagihan,
                    'terbayar' => $terbayar,
                    'status_bayar' => $status_bayar,
                    'waktu_bayar' => $waktu_bayar,
                    'manual_invoice' => 0,
                    'jatuh_tempo' => $tgl_jatuh_tempo
                ]);

                // Update customer due date
                DB::table('tb_pelanggan')
                    ->where('id_pelanggan', $plg->id_pelanggan)
                    ->update(['jatuh_tempo' => $tgl_jatuh_tempo]);
            });

            $generated++;
        }

        $this->info("Successfully generated {$generated} bills for period {$bulan}-{$tahun}.");
        Log::info("GenerateBulananTagihan: Successfully generated {$generated} bills for period {$bulantahun}");

        return Command::SUCCESS;
    }
}
