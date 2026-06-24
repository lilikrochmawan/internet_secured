<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwal otomatis blokir pelanggan jatuh tempo berjalan setiap hari jam 00.10
Schedule::command('app:auto-block-pelanggan')->dailyAt('00:10');

// Jadwal pengiriman tagihan otomatis setiap hari pukul 08.00 pagi
Schedule::command('app:send-auto-billing-notifications')->dailyAt('08:00');

// Jadwal generate transaksi bulanan setiap tanggal 1 pukul 01.00 wib
Schedule::command('app:generate-bulanan-tagihan')->monthlyOn(1, '01:00')->timezone('Asia/Jakarta');

// Jadwal pemeriksaan lisensi otomatis setiap hari pukul 00.00 wib
Schedule::command('license:check')->dailyAt('00:00')->timezone('Asia/Jakarta');
