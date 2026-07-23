<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('cache')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        if (!Schema::hasTable('cache_locks')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        if (!Schema::hasTable('failed_jobs')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        if (!Schema::hasTable('job_batches')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        if (!Schema::hasTable('jobs')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        if (!Schema::hasTable('password_reset_tokens')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        if (!Schema::hasTable('riwayat_backupdb')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `riwayat_backupdb` (
  `id_backup` int(11) NOT NULL AUTO_INCREMENT,
  `nama_db` text NOT NULL,
  `tanggal` date NOT NULL,
  PRIMARY KEY (`id_backup`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
        }

        if (!Schema::hasTable('sessions')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        if (!Schema::hasTable('tb_acs_queue')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tb_acs_queue` (
  `id_command` int(11) NOT NULL AUTO_INCREMENT,
  `serial_number` varchar(100) NOT NULL,
  `command_type` varchar(50) NOT NULL,
  `command_data` text DEFAULT NULL,
  `status` varchar(20) DEFAULT \'pending\',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_command`),
  KEY `serial_number` (`serial_number`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=294157 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
        }

        if (!Schema::hasTable('tb_branch')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tb_branch` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_branch` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        if (!Schema::hasTable('tb_cpe')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tb_cpe` (
  `id_cpe` int(11) NOT NULL AUTO_INCREMENT,
  `serial_number` varchar(100) NOT NULL,
  `oui` varchar(20) DEFAULT NULL,
  `product_class` varchar(100) DEFAULT NULL,
  `manufacturer` varchar(100) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `connection_request_url` varchar(255) DEFAULT NULL,
  `software_version` varchar(100) DEFAULT NULL,
  `hardware_version` varchar(100) DEFAULT NULL,
  `cwmp_model` varchar(20) DEFAULT \'tr098\',
  `last_inform` datetime DEFAULT NULL,
  `id_pelanggan` int(11) DEFAULT NULL,
  `rx_power` varchar(100) DEFAULT NULL,
  `pppoe_username` varchar(100) DEFAULT NULL,
  `pppoe_status` varchar(50) DEFAULT NULL,
  `wifi_ssid_24` varchar(100) DEFAULT NULL,
  `wifi_ssid_5` varchar(100) DEFAULT NULL,
  `wifi_ssid_5_index` int(11) DEFAULT NULL,
  `tx_power` varchar(100) DEFAULT NULL,
  `connected_devices` text DEFAULT NULL,
  `wifi_channel_24` varchar(50) DEFAULT NULL,
  `wifi_channel_5` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_cpe`),
  UNIQUE KEY `serial_number` (`serial_number`),
  KEY `serial_number_2` (`serial_number`),
  KEY `id_pelanggan` (`id_pelanggan`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
        }

        if (!Schema::hasTable('tb_kas')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tb_kas` (
  `id_kas` int(11) NOT NULL AUTO_INCREMENT,
  `id_transaksi` int(11) DEFAULT NULL,
  `tgl_kas` date DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `penerimaan` int(11) DEFAULT NULL,
  `pengeluaran` int(11) DEFAULT NULL,
  `jenis_kas` varchar(15) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `id_tagihan` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_kas`)
) ENGINE=InnoDB AUTO_INCREMENT=1216 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci');
        }

        if (!Schema::hasTable('tb_kas2')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tb_kas2` (
  `id_kas` int(11) NOT NULL AUTO_INCREMENT,
  `id_transaksi` int(11) DEFAULT NULL,
  `tgl_kas` date DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `penerimaan` int(11) DEFAULT NULL,
  `pengeluaran` int(11) DEFAULT NULL,
  `jenis_kas` varchar(15) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `id_tagihan` int(11) DEFAULT NULL,
  `id_pelanggan` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_kas`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci');
        }

        if (!Schema::hasTable('tb_paket')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tb_paket` (
  `id_paket` int(11) NOT NULL AUTO_INCREMENT,
  `nama_paket` varchar(255) NOT NULL,
  `harga` int(11) NOT NULL,
  `ppn` decimal(10,2) DEFAULT NULL,
  `id_pmikrotik` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_paket`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci');
        }

        if (!Schema::hasTable('tb_pelanggan')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tb_pelanggan` (
  `id_pelanggan` int(11) NOT NULL AUTO_INCREMENT,
  `kode_pelanggan` varchar(30) NOT NULL,
  `nik` varchar(18) DEFAULT NULL,
  `nama_pelanggan` varchar(255) NOT NULL,
  `alamat` varchar(255) NOT NULL,
  `no_telp` varchar(20) NOT NULL,
  `paket` int(11) NOT NULL,
  `ip_address` varchar(255) DEFAULT NULL,
  `tgl_pemasangan` datetime NOT NULL,
  `jatuh_tempo` datetime NOT NULL DEFAULT current_timestamp(),
  `location` varchar(255) DEFAULT NULL,
  `id_perangkat` varchar(11) DEFAULT NULL,
  `odp` int(11) DEFAULT NULL,
  `id_mikrotik` int(11) DEFAULT NULL,
  `id_branch` bigint(20) unsigned DEFAULT NULL,
  `id_sub_branch` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_pelanggan`),
  KEY `idx_pelanggan_kode` (`kode_pelanggan`),
  KEY `idx_pelanggan_telp` (`no_telp`),
  KEY `idx_pelanggan_paket` (`paket`),
  KEY `idx_pelanggan_mikrotik` (`id_mikrotik`),
  KEY `tb_pelanggan_id_branch_index` (`id_branch`),
  KEY `tb_pelanggan_id_sub_branch_index` (`id_sub_branch`)
) ENGINE=InnoDB AUTO_INCREMENT=171 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci');
        }

        if (!Schema::hasTable('tb_perangkat')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tb_perangkat` (
  `id_perangkat` int(11) NOT NULL AUTO_INCREMENT,
  `nama_perangkat` text NOT NULL,
  PRIMARY KEY (`id_perangkat`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
        }

        if (!Schema::hasTable('tb_profile')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tb_profile` (
  `id_profile` int(11) NOT NULL AUTO_INCREMENT,
  `nama_sekolah` varchar(255) NOT NULL,
  `alamat` text NOT NULL,
  `telpon` varchar(20) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `website` varchar(100) NOT NULL,
  `kota` varchar(100) NOT NULL,
  `bendahara` varchar(100) NOT NULL,
  `nip` varchar(30) NOT NULL,
  `foto` varchar(255) NOT NULL,
  `license_key` varchar(255) DEFAULT NULL,
  `license_client_name` varchar(255) DEFAULT NULL,
  `license_status` varchar(255) NOT NULL DEFAULT \'invalid\',
  `license_expires_at` datetime DEFAULT NULL,
  `license_plan_name` varchar(255) DEFAULT \'Lite\',
  `license_max_clients` int(11) NOT NULL DEFAULT 250,
  `license_last_checked` datetime DEFAULT NULL,
  `ktu` varchar(255) NOT NULL,
  `nip_ktu` varchar(30) NOT NULL,
  `tipe_jatuh_tempo` varchar(50) DEFAULT \'tanggal_tetap\',
  `hari_jatuh_tempo` int(11) DEFAULT 10,
  `sistem_billing` varchar(50) DEFAULT \'prabayar\',
  `auto_send_billing` tinyint(4) NOT NULL DEFAULT 0,
  `auto_send_date` int(11) NOT NULL DEFAULT 5,
  `auto_send_h_minus` int(11) NOT NULL DEFAULT 3,
  `adjust_due_date_late` tinyint(4) NOT NULL DEFAULT 0,
  `admin_fee_type` varchar(255) NOT NULL DEFAULT \'flat\',
  `admin_fee_flat` int(11) NOT NULL DEFAULT 2000,
  `admin_fee_qris_type` varchar(255) NOT NULL DEFAULT \'percentage\',
  `admin_fee_qris_value` decimal(8,2) NOT NULL DEFAULT 0.70,
  `admin_fee_va` int(11) NOT NULL DEFAULT 4000,
  `admin_fee_retail` int(11) NOT NULL DEFAULT 3000,
  `admin_fee_retail_status` tinyint(1) NOT NULL DEFAULT 1,
  `admin_fee_qris_status` tinyint(1) NOT NULL DEFAULT 1,
  `admin_fee_va_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_profile`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci');
        }

        if (!Schema::hasTable('tb_promo')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tb_promo` (
  `id_promo` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_promo` varchar(150) NOT NULL,
  `id_pelanggan` int(11) NOT NULL,
  `id_paket` int(11) NOT NULL,
  `mulai_bulan` int(11) NOT NULL,
  `mulai_tahun` int(11) NOT NULL,
  `selesai_bulan` int(11) NOT NULL,
  `selesai_tahun` int(11) NOT NULL,
  `nominal_tagihan` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_promo`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        if (!Schema::hasTable('tb_sub_branch')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tb_sub_branch` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_branch` bigint(20) unsigned NOT NULL,
  `nama_sub_branch` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tb_sub_branch_id_branch_foreign` (`id_branch`),
  CONSTRAINT `tb_sub_branch_id_branch_foreign` FOREIGN KEY (`id_branch`) REFERENCES `tb_branch` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        if (!Schema::hasTable('tb_tagihan')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tb_tagihan` (
  `id_tagihan` int(11) NOT NULL AUTO_INCREMENT,
  `id_pelanggan` int(11) NOT NULL,
  `bulan_tahun` varchar(30) NOT NULL,
  `jml_bayar` int(11) NOT NULL,
  `terbayar` int(11) DEFAULT NULL,
  `tgl_bayar` date DEFAULT NULL,
  `status_bayar` int(11) DEFAULT NULL,
  `no_invoice` varchar(100) DEFAULT NULL,
  `blokir_status` int(11) DEFAULT NULL,
  `terkirim` enum(\'belum\',\'terkirim\') NOT NULL,
  `waktu_bayar` datetime DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `manual_invoice` tinyint(1) NOT NULL DEFAULT 0,
  `bea_pemasangan` bigint(20) NOT NULL DEFAULT 0,
  `jasa_troubleshooting` bigint(20) NOT NULL DEFAULT 0,
  `lain_lain` bigint(20) NOT NULL DEFAULT 0,
  `item_tagihan` varchar(100) DEFAULT NULL,
  `jatuh_tempo` datetime DEFAULT NULL,
  PRIMARY KEY (`id_tagihan`),
  KEY `idx_tagihan_pelanggan` (`id_pelanggan`),
  KEY `idx_tagihan_bulan_tahun` (`bulan_tahun`),
  KEY `idx_tagihan_status_bayar` (`status_bayar`),
  KEY `idx_tagihan_manual_invoice` (`manual_invoice`),
  KEY `idx_tagihan_jatuh_tempo` (`jatuh_tempo`)
) ENGINE=InnoDB AUTO_INCREMENT=1423 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci');
        }

        if (!Schema::hasTable('tb_user')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tb_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `nama_user` varchar(255) NOT NULL,
  `password` varchar(100) NOT NULL,
  `level` varchar(30) NOT NULL,
  `foto` varchar(255) NOT NULL,
  `id_pelanggan` int(11) DEFAULT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_pelanggan` (`id_pelanggan`),
  KEY `idx_user_username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=1007 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci');
        }

        if (!Schema::hasTable('tb_user_branch_access')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tb_user_branch_access` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `id_branch` bigint(20) unsigned NOT NULL,
  `id_sub_branch` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tb_user_branch_access_id_branch_foreign` (`id_branch`),
  KEY `tb_user_branch_access_id_sub_branch_foreign` (`id_sub_branch`),
  KEY `tb_user_branch_access_id_user_index` (`id_user`),
  CONSTRAINT `tb_user_branch_access_id_branch_foreign` FOREIGN KEY (`id_branch`) REFERENCES `tb_branch` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tb_user_branch_access_id_sub_branch_foreign` FOREIGN KEY (`id_sub_branch`) REFERENCES `tb_sub_branch` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        if (!Schema::hasTable('tb_user_menu_access')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tb_user_menu_access` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `menu_key` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tb_user_menu_access_id_user_menu_key_unique` (`id_user`,`menu_key`),
  KEY `tb_user_menu_access_id_user_index` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=130 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        if (!Schema::hasTable('tbl_badmin')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tbl_badmin` (
  `id_badmin` int(11) NOT NULL AUTO_INCREMENT,
  `harga` varchar(255) DEFAULT NULL,
  `status` enum(\'saya\',\'pelanggan\') NOT NULL,
  PRIMARY KEY (`id_badmin`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');
        }

        if (!Schema::hasTable('tbl_blokir')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tbl_blokir` (
  `id_blokir` int(11) NOT NULL AUTO_INCREMENT,
  `status_blokir` enum(\'aktif\',\'tidakaktif\') NOT NULL,
  `set_waktu` int(11) DEFAULT NULL,
  `set_waktu2` varchar(30) DEFAULT NULL,
  `pesan_blokir` text DEFAULT NULL,
  PRIMARY KEY (`id_blokir`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
        }

        if (!Schema::hasTable('tbl_bukablokir')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tbl_bukablokir` (
  `id_bukablokir` int(11) NOT NULL AUTO_INCREMENT,
  `pesan_bukablokir` text DEFAULT NULL,
  PRIMARY KEY (`id_bukablokir`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
        }

        if (!Schema::hasTable('tbl_buktibayar')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tbl_buktibayar` (
  `id_buktibayar` int(11) NOT NULL AUTO_INCREMENT,
  `id_rekening` int(11) NOT NULL,
  `id_pelanggan` int(11) NOT NULL,
  `id_tagihan` int(11) NOT NULL,
  `gambar` text NOT NULL,
  `keterangan` text NOT NULL,
  `tanggal_terima` datetime NOT NULL,
  PRIMARY KEY (`id_buktibayar`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
        }

        if (!Schema::hasTable('tbl_deleted_pelanggan_history')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tbl_deleted_pelanggan_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_pelanggan` int(11) DEFAULT NULL,
  `nama_pelanggan` varchar(255) NOT NULL,
  `alamat` text DEFAULT NULL,
  `nik` varchar(50) DEFAULT NULL,
  `location` text DEFAULT NULL,
  `alasan_hapus` text NOT NULL,
  `deleted_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        if (!Schema::hasTable('tbl_informasi')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tbl_informasi` (
  `id_informasi` int(11) NOT NULL AUTO_INCREMENT,
  `judul_informasi` varchar(255) NOT NULL,
  `isi_informasi` text NOT NULL,
  PRIMARY KEY (`id_informasi`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
        }

        if (!Schema::hasTable('tbl_keluhan')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tbl_keluhan` (
  `id_keluhan` int(11) NOT NULL AUTO_INCREMENT,
  `id_pelanggan` int(11) DEFAULT NULL,
  `judul_keluhan` varchar(50) NOT NULL,
  `nomor_tiket` varchar(255) NOT NULL,
  `isi_keluhan` text NOT NULL,
  `gambar` text NOT NULL,
  `masalah` text DEFAULT NULL,
  `no_wa` varchar(15) DEFAULT NULL,
  `status_keluhan` varchar(50) NOT NULL DEFAULT \'menunggu\',
  `tanggal` datetime NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `teknisi_id` int(11) DEFAULT NULL,
  `assign_to_all` tinyint(1) NOT NULL DEFAULT 0,
  `tindakan` text DEFAULT NULL,
  `bukti_foto` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_keluhan`),
  KEY `tbl_keluhan_teknisi_id_foreign` (`teknisi_id`),
  CONSTRAINT `tbl_keluhan_teknisi_id_foreign` FOREIGN KEY (`teknisi_id`) REFERENCES `tb_user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
        }

        if (!Schema::hasTable('tbl_mikrotik')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tbl_mikrotik` (
  `id_mikrotik` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `port_mikrotik` varchar(255) DEFAULT NULL,
  `nama_mikrotik` varchar(255) DEFAULT NULL,
  `remote_host` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_mikrotik`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
        }

        if (!Schema::hasTable('tbl_mitra_config')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tbl_mitra_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `tipe_komisi` varchar(20) NOT NULL,
  `nilai_komisi` decimal(15,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_user` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
        }

        if (!Schema::hasTable('tbl_mitra_komisi_logs')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tbl_mitra_komisi_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `id_tagihan` int(11) NOT NULL,
  `id_pelanggan` int(11) NOT NULL,
  `jumlah_bayar` decimal(15,2) NOT NULL,
  `tipe_komisi` varchar(20) NOT NULL,
  `nilai_komisi` decimal(15,2) NOT NULL,
  `komisi_diterima` decimal(15,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
        }

        if (!Schema::hasTable('tbl_mitra_payouts')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tbl_mitra_payouts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `payout_month` int(11) NOT NULL,
  `payout_year` int(11) NOT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `tgl_payout` date NOT NULL,
  `catatan` text DEFAULT NULL,
  `bukti_transfer` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
        }

        if (!Schema::hasTable('tbl_nomorphone')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tbl_nomorphone` (
  `id_mynumber` int(11) NOT NULL AUTO_INCREMENT,
  `my_number` varchar(15) NOT NULL,
  `nama_pemilik` varchar(255) NOT NULL,
  PRIMARY KEY (`id_mynumber`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
        }

        if (!Schema::hasTable('tbl_notif')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tbl_notif` (
  `id_notifikasi` int(11) NOT NULL AUTO_INCREMENT,
  `status_notifikasi` enum(\'aktif\',\'tidakaktif\') NOT NULL,
  `pesan_notifikasi` text NOT NULL,
  PRIMARY KEY (`id_notifikasi`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
        }

        if (!Schema::hasTable('tbl_notifbayar')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tbl_notifbayar` (
  `id_notifbayar` int(11) NOT NULL AUTO_INCREMENT,
  `pesan_bayar` text NOT NULL,
  PRIMARY KEY (`id_notifbayar`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
        }

        if (!Schema::hasTable('tbl_notifpromo')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tbl_notifpromo` (
  `id_notifpromo` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pesan_promo` text NOT NULL,
  `status_promo` varchar(20) NOT NULL DEFAULT \'aktif\',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_notifpromo`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        if (!Schema::hasTable('tbl_notifreminder')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tbl_notifreminder` (
  `id_reminder` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `status_reminder` enum(\'aktif\',\'tidakaktif\') NOT NULL DEFAULT \'aktif\',
  `pesan_reminder` text NOT NULL,
  PRIMARY KEY (`id_reminder`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        if (!Schema::hasTable('tbl_npemasangan')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tbl_npemasangan` (
  `id_npemasangan` int(11) NOT NULL AUTO_INCREMENT,
  `status_notif` enum(\'aktif\',\'tidak\') NOT NULL,
  `pesan_notif` text NOT NULL,
  PRIMARY KEY (`id_npemasangan`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
        }

        if (!Schema::hasTable('tbl_odc')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tbl_odc` (
  `id_odc` int(11) NOT NULL AUTO_INCREMENT,
  `nama_odc` varchar(255) NOT NULL,
  `perangkat_odc` varchar(50) NOT NULL,
  `port_odc` varchar(30) NOT NULL,
  `location` text NOT NULL,
  `redaman` varchar(50) DEFAULT NULL,
  `tube` varchar(50) DEFAULT NULL,
  `core_number` int(11) DEFAULT NULL,
  `jenis_odc` varchar(20) NOT NULL DEFAULT \'utama\',
  `parent_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_odc`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
        }

        if (!Schema::hasTable('tbl_odp')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tbl_odp` (
  `id_odp` int(11) NOT NULL AUTO_INCREMENT,
  `nama_odp` varchar(255) NOT NULL,
  `port_odp` varchar(30) NOT NULL,
  `location` varchar(255) NOT NULL,
  `redaman` varchar(50) DEFAULT NULL,
  `odc` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_odp`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
        }

        if (!Schema::hasTable('tbl_order_pemasangan')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tbl_order_pemasangan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nik` varchar(50) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `paket` int(11) DEFAULT NULL,
  `alamat_ktp` text NOT NULL,
  `alamat_pemasangan` text NOT NULL,
  `koordinat_pemasangan` varchar(100) NOT NULL,
  `jadwal_pemasangan` datetime DEFAULT NULL,
  `foto_ktp` varchar(255) DEFAULT NULL,
  `foto_dokumentasi` varchar(255) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT \'pending\',
  `id_sales` int(11) DEFAULT NULL,
  `id_teknisi` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        if (!Schema::hasTable('tbl_paketmikrotik')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tbl_paketmikrotik` (
  `id_paketmikrotik` int(11) NOT NULL AUTO_INCREMENT,
  `status` enum(\'ya\',\'tidak\') NOT NULL,
  `ppn` enum(\'aktif\',\'tidak\') DEFAULT NULL,
  PRIMARY KEY (`id_paketmikrotik`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
        }

        if (!Schema::hasTable('tbl_penggunamikrotik')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tbl_penggunamikrotik` (
  `id_penggunamikrotik` int(11) NOT NULL AUTO_INCREMENT,
  `status` enum(\'ya\',\'tidak\') NOT NULL,
  `addppsecret` enum(\'ya\',\'tidak\') NOT NULL,
  `ippelanggan` enum(\'statik\',\'dynamic\') NOT NULL,
  `mapping` enum(\'aktif\',\'tidak\') DEFAULT NULL,
  `ip_pool` enum(\'ya\',\'tidak\') DEFAULT NULL,
  PRIMARY KEY (`id_penggunamikrotik`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
        }

        if (!Schema::hasTable('tbl_pengumuman')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tbl_pengumuman` (
  `id_pengumuman` int(11) NOT NULL AUTO_INCREMENT,
  `isi_pengumuman` text NOT NULL,
  PRIMARY KEY (`id_pengumuman`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
        }

        if (!Schema::hasTable('tbl_pesan_siaran')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tbl_pesan_siaran` (
  `id_pesan_siaran` int(11) NOT NULL AUTO_INCREMENT,
  `judul_pesan_siaran` varchar(255) NOT NULL,
  `isi_pesan` text NOT NULL,
  PRIMARY KEY (`id_pesan_siaran`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
        }

        if (!Schema::hasTable('tbl_pgate')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tbl_pgate` (
  `id_pgat` int(11) NOT NULL AUTO_INCREMENT,
  `tclientkey` varchar(255) DEFAULT NULL,
  `tserverkey` varchar(255) DEFAULT NULL,
  `mode` varchar(20) DEFAULT \'sandbox\',
  PRIMARY KEY (`id_pgat`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');
        }

        if (!Schema::hasTable('tbl_rekening')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tbl_rekening` (
  `id_rekening` int(11) NOT NULL AUTO_INCREMENT,
  `nama_bank` varchar(50) NOT NULL,
  `nomor_rekening` varchar(255) NOT NULL,
  `nama_rekening` varchar(255) NOT NULL,
  PRIMARY KEY (`id_rekening`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
        }

        if (!Schema::hasTable('tbl_token')) {
            DB::statement('CREATE TABLE IF NOT EXISTS `tbl_token` (
  `id_token` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(255) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT \'aktif\',
  PRIMARY KEY (`id_token`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_token');
        Schema::dropIfExists('tbl_rekening');
        Schema::dropIfExists('tbl_pgate');
        Schema::dropIfExists('tbl_pesan_siaran');
        Schema::dropIfExists('tbl_pengumuman');
        Schema::dropIfExists('tbl_penggunamikrotik');
        Schema::dropIfExists('tbl_paketmikrotik');
        Schema::dropIfExists('tbl_order_pemasangan');
        Schema::dropIfExists('tbl_odp');
        Schema::dropIfExists('tbl_odc');
        Schema::dropIfExists('tbl_npemasangan');
        Schema::dropIfExists('tbl_notifreminder');
        Schema::dropIfExists('tbl_notifpromo');
        Schema::dropIfExists('tbl_notifbayar');
        Schema::dropIfExists('tbl_notif');
        Schema::dropIfExists('tbl_nomorphone');
        Schema::dropIfExists('tbl_mitra_payouts');
        Schema::dropIfExists('tbl_mitra_komisi_logs');
        Schema::dropIfExists('tbl_mitra_config');
        Schema::dropIfExists('tbl_mikrotik');
        Schema::dropIfExists('tbl_keluhan');
        Schema::dropIfExists('tbl_informasi');
        Schema::dropIfExists('tbl_deleted_pelanggan_history');
        Schema::dropIfExists('tbl_buktibayar');
        Schema::dropIfExists('tbl_bukablokir');
        Schema::dropIfExists('tbl_blokir');
        Schema::dropIfExists('tbl_badmin');
        Schema::dropIfExists('tb_user_menu_access');
        Schema::dropIfExists('tb_user_branch_access');
        Schema::dropIfExists('tb_user');
        Schema::dropIfExists('tb_tagihan');
        Schema::dropIfExists('tb_sub_branch');
        Schema::dropIfExists('tb_promo');
        Schema::dropIfExists('tb_profile');
        Schema::dropIfExists('tb_perangkat');
        Schema::dropIfExists('tb_pelanggan');
        Schema::dropIfExists('tb_paket');
        Schema::dropIfExists('tb_kas2');
        Schema::dropIfExists('tb_kas');
        Schema::dropIfExists('tb_cpe');
        Schema::dropIfExists('tb_branch');
        Schema::dropIfExists('tb_acs_queue');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('riwayat_backupdb');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
    }
};
