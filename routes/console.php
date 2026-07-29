<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwal otomatis blokir pelanggan jatuh tempo berjalan setiap hari jam 00.10 tanpa tumpang tindih
Schedule::command('app:auto-block-pelanggan')->dailyAt('00:10')->timezone('Asia/Jakarta')->withoutOverlapping();

// Jadwal pengiriman tagihan otomatis berjalan sekali sehari jam 08.00 WIB tanpa tumpang tindih
Schedule::command('app:send-auto-billing-notifications')->dailyAt('08:00')->withoutOverlapping();

// Jadwal generate transaksi bulanan setiap tanggal 1 pukul 00.00 wib
Schedule::command('app:generate-bulanan-tagihan')->monthlyOn(1, '00:00')->timezone('Asia/Jakarta');

// Jadwal pemeriksaan lisensi otomatis setiap hari pukul 00.00 wib
Schedule::command('license:check')->dailyAt('00:00')->timezone('Asia/Jakarta');

// Jadwal backup database otomatis setiap hari pukul 02.00 wib
Schedule::command('db:backup')->dailyAt('02:00')->timezone('Asia/Jakarta')->withoutOverlapping();
