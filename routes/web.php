<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\KeluhanController;
use App\Http\Controllers\NetworkStatusController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});
Route::post('/payment/notification', [PaymentController::class, 'notification'])->name('payment.notification');

Route::middleware(['auth', 'client'])->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/router-stats', [AuthController::class, 'getRouterStats'])->name('dashboard.router_stats');
    Route::get('/payment/detail', [PaymentController::class, 'detail'])->name('payment.detail');
    Route::post('/payment/charge', [PaymentController::class, 'charge'])->name('payment.charge');
    Route::get('/jaringan/status', [NetworkStatusController::class, 'index'])->name('network.status');
    Route::post('/jaringan/status/wifi', [NetworkStatusController::class, 'updateWifi'])->name('network.wifi.update');
    Route::get('/laporan', [KeluhanController::class, 'index'])->name('keluhan.index');
    Route::get('/laporan/buat', [KeluhanController::class, 'create'])->name('keluhan.create');
    Route::post('/laporan/buat', [KeluhanController::class, 'store'])->name('keluhan.store');
    Route::get('/profil', [AuthController::class, 'profil'])->name('profile');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Administrator Routes Group
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminPelangganController;
use App\Http\Controllers\Admin\AdminPaketController;
use App\Http\Controllers\Admin\AdminTransaksiController;
use App\Http\Controllers\Admin\AdminKasController;
use App\Http\Controllers\Admin\AdminKeluhanController;
use App\Http\Controllers\Admin\AdminPenggunaController;
use App\Http\Controllers\Admin\AdminPengaturanController;
use App\Http\Controllers\Admin\AdminMonitoringController;
use App\Http\Controllers\Admin\AdminAcsController;
use App\Http\Controllers\Admin\AdminCustomPesanController;
use App\Http\Controllers\Admin\AdminOdcController;
use App\Http\Controllers\Admin\AdminOrderPemasanganController;
use App\Http\Controllers\Admin\AdminOdpController;
use App\Http\Controllers\Admin\AdminMapController;
use App\Http\Controllers\Admin\AdminOntController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\AdminPengaturanClientController;
use App\Http\Controllers\Admin\AdminLogController;

Route::prefix('administrator')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.post');
    });

    Route::middleware(['admin'])->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/dashboard', [AdminDashboardController::class, 'index']);
        
        // Halaman Lisensi Kadaluarsa / Belum Aktif
        Route::get('/unlicensed', [AdminPengaturanController::class, 'showUnlicensed'])->name('admin.unlicensed');
        Route::post('/unlicensed', [AdminPengaturanController::class, 'activateLicense'])->name('admin.unlicensed.activate');

        // CRUD Pelanggan
        Route::get('/pelanggan', [AdminPelangganController::class, 'index'])->name('admin.pelanggan.index');
        Route::get('/pelanggan/mikrotik-secrets', [AdminPelangganController::class, 'getMikrotikSecrets'])->name('admin.pelanggan.mikrotik_secrets');
        Route::post('/pelanggan', [AdminPelangganController::class, 'store'])->name('admin.pelanggan.store');
        Route::post('/pelanggan/update', [AdminPelangganController::class, 'update'])->name('admin.pelanggan.update');
        Route::post('/pelanggan/delete', [AdminPelangganController::class, 'destroy'])->name('admin.pelanggan.destroy');

        // CRUD Paket
        Route::get('/paket', [AdminPaketController::class, 'index'])->name('admin.paket.index');
        Route::get('/paket/get-mikrotik-profiles', [AdminPaketController::class, 'getMikrotikProfiles'])->name('admin.paket.get_mikrotik_profiles');
        Route::post('/paket', [AdminPaketController::class, 'store'])->name('admin.paket.store');
        Route::post('/paket/update', [AdminPaketController::class, 'update'])->name('admin.paket.update');
        Route::post('/paket/delete', [AdminPaketController::class, 'destroy'])->name('admin.paket.destroy');

        // CRUD ONT / Perangkat
        Route::get('/ont', [AdminOntController::class, 'index'])->name('admin.ont.index');
        Route::post('/ont', [AdminOntController::class, 'store'])->name('admin.ont.store');
        Route::post('/ont/update', [AdminOntController::class, 'update'])->name('admin.ont.update');
        Route::post('/ont/delete', [AdminOntController::class, 'destroy'])->name('admin.ont.destroy');

        // Transaksi Pembayaran
        Route::get('/transaksi', [AdminTransaksiController::class, 'index'])->name('admin.transaksi.index');
        Route::get('/transaksi/pembayaran-json', [AdminTransaksiController::class, 'pembayaranJson'])->name('admin.transaksi.pembayaran_json');
        Route::get('/transaksi/generate', [AdminTransaksiController::class, 'showGenerate'])->name('admin.transaksi.show_generate');
        Route::post('/transaksi/generate', [AdminTransaksiController::class, 'generate'])->name('admin.transaksi.generate');
        Route::post('/transaksi/bayar', [AdminTransaksiController::class, 'bayar'])->name('admin.transaksi.bayar');
        Route::post('/transaksi/batal', [AdminTransaksiController::class, 'batal'])->name('admin.transaksi.batal');
        Route::post('/transaksi/blokir', [AdminTransaksiController::class, 'blokir'])->name('admin.transaksi.blokir');
        Route::post('/transaksi/unblokir', [AdminTransaksiController::class, 'unblokir'])->name('admin.transaksi.unblokir');
        Route::post('/transaksi/wa-notif', [AdminTransaksiController::class, 'sendNotif'])->name('admin.transaksi.notif');
        Route::post('/transaksi/wa-reminder', [AdminTransaksiController::class, 'sendReminder'])->name('admin.transaksi.notif_reminder');
        Route::post('/transaksi/manual', [AdminTransaksiController::class, 'storeManual'])->name('admin.transaksi.store_manual');
        Route::post('/transaksi/broadcast', [AdminTransaksiController::class, 'broadcast'])->name('admin.transaksi.broadcast');
        Route::post('/transaksi/reminder', [AdminTransaksiController::class, 'reminder'])->name('admin.transaksi.reminder');
        Route::post('/transaksi/bulk-blokir', [AdminTransaksiController::class, 'bulkBlokir'])->name('admin.transaksi.bulk_blokir');
        Route::get('/transaksi/print-invoice/{id}', [AdminTransaksiController::class, 'printInvoice'])->name('admin.transaksi.print_invoice');
        Route::get('/transaksi/print-receipt/{id}', [AdminTransaksiController::class, 'printReceipt'])->name('admin.transaksi.print_receipt');

        // Kas Masuk & Keluar
        Route::get('/kas', [AdminKasController::class, 'index'])->name('admin.kas.index');
        Route::get('/kas/print', [AdminKasController::class, 'printReport'])->name('admin.kas.print');
        Route::post('/kas', [AdminKasController::class, 'store'])->name('admin.kas.store');
        Route::post('/kas/update', [AdminKasController::class, 'update'])->name('admin.kas.update');
        Route::post('/kas/delete', [AdminKasController::class, 'destroy'])->name('admin.kas.destroy');

        // Keluhan
        Route::get('/keluhan', [AdminKeluhanController::class, 'index'])->name('admin.keluhan.index');
        Route::get('/keluhan/print', [AdminKeluhanController::class, 'printReport'])->name('admin.keluhan.print');
        Route::post('/keluhan/proses', [AdminKeluhanController::class, 'proses'])->name('admin.keluhan.proses');
        Route::post('/keluhan/selesai', [AdminKeluhanController::class, 'selesai'])->name('admin.keluhan.selesai');
        Route::post('/keluhan/teknisi-selesai', [AdminKeluhanController::class, 'teknisiSelesai'])->name('admin.keluhan.teknisi_selesai');
        Route::post('/keluhan/verifikasi', [AdminKeluhanController::class, 'verifikasi'])->name('admin.keluhan.verifikasi');
        Route::post('/keluhan/buat-tiket', [AdminKeluhanController::class, 'storeTicket'])->name('admin.keluhan.store_ticket');
        Route::get('/keluhan/gambar/{filename}', [AdminKeluhanController::class, 'showGambar'])->name('admin.keluhan.gambar');

        // Pengguna (tb_user)
        Route::get('/pengguna', [AdminPenggunaController::class, 'index'])->name('admin.pengguna.index');
        Route::post('/pengguna', [AdminPenggunaController::class, 'store'])->name('admin.pengguna.store');
        Route::post('/pengguna/update', [AdminPenggunaController::class, 'update'])->name('admin.pengguna.update');
        Route::post('/pengguna/delete', [AdminPenggunaController::class, 'destroy'])->name('admin.pengguna.destroy');
        Route::post('/ganti-password', [AdminPenggunaController::class, 'gantiPasswordSelf'])->name('admin.pengguna.ganti_password_self');

        // Pengaturan & Profile
        Route::get('/pengaturan', [AdminPengaturanController::class, 'index'])->name('admin.pengaturan.index');
        Route::post('/pengaturan/profile', [AdminPengaturanController::class, 'updateProfile'])->name('admin.pengaturan.profile');
        Route::post('/pengaturan/mikrotik', [AdminPengaturanController::class, 'updateMikrotik'])->name('admin.pengaturan.mikrotik');
        Route::post('/pengaturan/mikrotik/delete', [AdminPengaturanController::class, 'deleteMikrotik'])->name('admin.pengaturan.mikrotik.delete');
        Route::post('/pengaturan/token', [AdminPengaturanController::class, 'updateToken'])->name('admin.pengaturan.token');
        Route::post('/pengaturan/midtrans', [AdminPengaturanController::class, 'updateMidtrans'])->name('admin.pengaturan.midtrans');
        Route::post('/pengaturan/jatuh-tempo', [AdminPengaturanController::class, 'updateJatuhTempo'])->name('admin.pengaturan.jatuh_tempo');
        Route::post('/pengaturan/biaya-admin', [AdminPengaturanController::class, 'updateBiayaAdmin'])->name('admin.pengaturan.biaya_admin');
        Route::post('/pengaturan/license', [AdminPengaturanController::class, 'updateLicense'])->name('admin.pengaturan.license');
        Route::get('/pengaturan/backup', [AdminPengaturanController::class, 'backupDb'])->name('admin.pengaturan.backup');

        // Pengaturan Client (Branch & Staff Access)
        Route::get('/pengaturan-client', [AdminPengaturanClientController::class, 'index'])->name('admin.pengaturan_client.index');
        Route::post('/branch/store', [AdminPengaturanClientController::class, 'storeBranch'])->name('admin.branch.store');
        Route::post('/branch/update', [AdminPengaturanClientController::class, 'updateBranch'])->name('admin.branch.update');
        Route::post('/branch/delete', [AdminPengaturanClientController::class, 'destroyBranch'])->name('admin.branch.destroy');
        Route::post('/sub-branch/store', [AdminPengaturanClientController::class, 'storeSubBranch'])->name('admin.sub_branch.store');
        Route::post('/sub-branch/update', [AdminPengaturanClientController::class, 'updateSubBranch'])->name('admin.sub_branch.update');
        Route::post('/sub-branch/delete', [AdminPengaturanClientController::class, 'destroySubBranch'])->name('admin.sub_branch.destroy');
        Route::post('/access/update', [AdminPengaturanClientController::class, 'updateAccess'])->name('admin.access.update');

        // Monitoring Mikrotik (Realtime, Active Clients, PPPoE Secrets, Profiles, Remote ONT)
        Route::get('/monitoring', [AdminMonitoringController::class, 'index'])->name('admin.monitoring.index');
        Route::get('/monitoring/resources', [AdminMonitoringController::class, 'getResources'])->name('admin.monitoring.resources');
        Route::get('/monitoring/interfaces', [AdminMonitoringController::class, 'getInterfaces'])->name('admin.monitoring.interfaces');
        Route::post('/monitoring/traffic', [AdminMonitoringController::class, 'getTraffic'])->name('admin.monitoring.traffic');
        Route::get('/monitoring/logs', [AdminMonitoringController::class, 'getLogs'])->name('admin.monitoring.logs');
        Route::get('/monitoring/active', [AdminMonitoringController::class, 'activeClients'])->name('admin.monitoring.active');
        Route::post('/monitoring/active/disconnect', [AdminMonitoringController::class, 'disconnectActive'])->name('admin.monitoring.active.disconnect');
        // AJAX endpoint for remote ONT NAT update
        Route::post('/monitoring/active/remote', [AdminMonitoringController::class, 'remoteOnt'])->name('admin.monitoring.active.remote');
        Route::get('/monitoring/active/nat-settings', [AdminMonitoringController::class, 'getNatSettings'])->name('admin.monitoring.active.nat_settings');
        Route::post('/monitoring/active/update-nat', [AdminMonitoringController::class, 'updateNatSettings'])->name('admin.monitoring.active.update_nat');
        Route::get('/teknisi/clients', [AdminMonitoringController::class, 'teknisiClients'])->name('admin.teknisi.clients');

        Route::get('/monitoring/pppoe', [AdminMonitoringController::class, 'pppoeSecrets'])->name('admin.monitoring.pppoe');
        Route::post('/monitoring/pppoe/store', [AdminMonitoringController::class, 'storeSecret'])->name('admin.monitoring.pppoe.store');
        Route::post('/monitoring/pppoe/delete', [AdminMonitoringController::class, 'deleteSecret'])->name('admin.monitoring.pppoe.delete');

        Route::get('/monitoring/profiles', [AdminMonitoringController::class, 'pppoeProfiles'])->name('admin.monitoring.profiles');
        Route::post('/monitoring/profiles/store', [AdminMonitoringController::class, 'storeProfile'])->name('admin.monitoring.profiles.store');
        Route::post('/monitoring/profiles/delete', [AdminMonitoringController::class, 'deleteProfile'])->name('admin.monitoring.profiles.delete');

        // TR-069 ACS CPE Management
        Route::get('/tr069', [AdminAcsController::class, 'index'])->name('admin.tr069.index');
        Route::post('/tr069/link', [AdminAcsController::class, 'linkCustomer'])->name('admin.tr069.link');
        Route::post('/tr069/unlink', [AdminAcsController::class, 'unlinkCustomer'])->name('admin.tr069.unlink');
        Route::get('/tr069/detail/{id}', [AdminAcsController::class, 'detail'])->name('admin.tr069.detail');
        Route::post('/tr069/reboot', [AdminAcsController::class, 'reboot'])->name('admin.tr069.reboot');
        Route::post('/tr069/parameters', [AdminAcsController::class, 'setParameters'])->name('admin.tr069.parameters');
        Route::post('/tr069/connection-request', [AdminAcsController::class, 'triggerConnectionRequest'])->name('admin.tr069.cr');
        Route::post('/tr069/delete', [AdminAcsController::class, 'destroy'])->name('admin.tr069.destroy');

        // Custom Pesan WhatsApp Templates
        Route::get('/custom-pesan', [AdminCustomPesanController::class, 'index'])->name('admin.custom_pesan.index');
        Route::post('/custom-pesan/notif', [AdminCustomPesanController::class, 'updateNotif'])->name('admin.custom_pesan.notif');
        Route::post('/custom-pesan/bayar', [AdminCustomPesanController::class, 'updateBayar'])->name('admin.custom_pesan.bayar');
        Route::post('/custom-pesan/pemasangan', [AdminCustomPesanController::class, 'updatePemasangan'])->name('admin.custom_pesan.pemasangan');
        Route::post('/custom-pesan/blokir', [AdminCustomPesanController::class, 'updateBlokir'])->name('admin.custom_pesan.blokir');
        Route::post('/custom-pesan/bukablokir', [AdminCustomPesanController::class, 'updateBukaBlokir'])->name('admin.custom_pesan.bukablokir');
        Route::post('/custom-pesan/reminder', [AdminCustomPesanController::class, 'updateReminder'])->name('admin.custom_pesan.reminder');

        // Broadcast Notifikasi & ODP/ODC Maintenance
        Route::get('/broadcast', [AdminNotificationController::class, 'index'])->name('admin.broadcast.index');
        Route::get('/broadcast/odp-clients/{id}', [AdminNotificationController::class, 'getOdpClients'])->name('admin.broadcast.odp_clients');
        Route::get('/broadcast/odc-clients/{id}', [AdminNotificationController::class, 'getOdcClients'])->name('admin.broadcast.odc_clients');
        Route::post('/broadcast/general', [AdminNotificationController::class, 'sendGeneral'])->name('admin.broadcast.general');
        Route::post('/broadcast/odp', [AdminNotificationController::class, 'sendOdp'])->name('admin.broadcast.odp');
        Route::post('/broadcast/odc', [AdminNotificationController::class, 'sendOdc'])->name('admin.broadcast.odc');
        Route::post('/broadcast/delete-announcement/{id}', [AdminNotificationController::class, 'deleteAnnouncement'])->name('admin.broadcast.delete_announcement');
        Route::get('/notifications/fetch', [AdminNotificationController::class, 'fetchNotifications'])->name('admin.notifications.fetch');

        // Order Pemasangan Management
        Route::get('/order-pemasangan', [AdminOrderPemasanganController::class, 'index'])->name('admin.order_pemasangan.index');
        Route::post('/order-pemasangan', [AdminOrderPemasanganController::class, 'store'])->name('admin.order_pemasangan.store');
        Route::post('/order-pemasangan/assign', [AdminOrderPemasanganController::class, 'assign'])->name('admin.order_pemasangan.assign');
        Route::post('/order-pemasangan/approve', [AdminOrderPemasanganController::class, 'approve'])->name('admin.order_pemasangan.approve');
        Route::post('/order-pemasangan/complete', [AdminOrderPemasanganController::class, 'complete'])->name('admin.order_pemasangan.complete');
        Route::post('/order-pemasangan/confirm', [AdminOrderPemasanganController::class, 'confirm'])->name('admin.order_pemasangan.confirm');
        Route::post('/order-pemasangan/claim', [AdminOrderPemasanganController::class, 'claim'])->name('admin.order_pemasangan.claim');
        Route::get('/order-pemasangan/ktp/{filename}', [AdminOrderPemasanganController::class, 'showKtp'])->name('admin.order_pemasangan.ktp');
        Route::get('/order-pemasangan/dokumentasi/{filename}', [AdminOrderPemasanganController::class, 'showDokumentasi'])->name('admin.order_pemasangan.dokumentasi');

        // ODC Management
        Route::get('/odc', [AdminOdcController::class, 'index'])->name('admin.odc.index');
        Route::post('/odc', [AdminOdcController::class, 'store'])->name('admin.odc.store');
        Route::post('/odc/update', [AdminOdcController::class, 'update'])->name('admin.odc.update');
        Route::post('/odc/delete', [AdminOdcController::class, 'destroy'])->name('admin.odc.destroy');
        Route::get('/odc/coordinates', [AdminOdcController::class, 'getCoordinates'])->name('admin.odc.coordinates');

        // ODP Management
        Route::get('/odp', [AdminOdpController::class, 'index'])->name('admin.odp.index');
        Route::post('/odp', [AdminOdpController::class, 'store'])->name('admin.odp.store');
        Route::post('/odp/update', [AdminOdpController::class, 'update'])->name('admin.odp.update');
        Route::post('/odp/delete', [AdminOdpController::class, 'destroy'])->name('admin.odp.destroy');
        Route::get('/odp/coordinates', [AdminOdpController::class, 'getCoordinates'])->name('admin.odp.coordinates');

        // Topology & Client Map
        Route::get('/mapping', [AdminMapController::class, 'index'])->name('admin.mapping.index');
        Route::get('/mapping/coordinates', [AdminMapController::class, 'getCoordinates'])->name('admin.mapping.coordinates');

        // Log Aktivitas
        Route::get('/logs', [AdminLogController::class, 'index'])->name('admin.logs.index');

        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
    });
});
