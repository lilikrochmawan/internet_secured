<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RestoreApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:restore';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Mengembalikan folder app dari folder cadangan sistem.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $appPath = base_path('app');
        $backupPath = storage_path('framework/cache/.sys_backup');
        $tempPath = storage_path('framework/cache/.sys_temp_delete');

        if (!File::exists($backupPath)) {
            $this->error('Folder cadangan sistem tidak ditemukan! Pemulihan dibatalkan.');
            return Command::FAILURE;
        }

        $this->info('Memulai proses pemulihan folder app...');

        // 1. Rename folder app aktif ke temp agar aman
        if (File::exists($appPath)) {
            try {
                File::moveDirectory($appPath, $tempPath);
            } catch (\Exception $e) {
                $this->error('Gagal mengamankan folder app aktif: ' . $e->getMessage());
                return Command::FAILURE;
            }
        }

        // 2. Salin dari backup ke app
        try {
            $this->info('Mengembalikan kode asli dari folder sistem aman...');
            File::copyDirectory($backupPath, $appPath);
            $this->info('Folder app berhasil dipulihkan ke kondisi semula!');

            // Hapus folder temp
            if (File::exists($tempPath)) {
                File::deleteDirectory($tempPath);
            }

            // Hapus folder backup agar folder project bersih kembali
            File::deleteDirectory($backupPath);
            $this->info('Folder cadangan sistem berhasil dibersihkan.');

        } catch (\Exception $e) {
            $this->error('Gagal memulihkan folder: ' . $e->getMessage());

            // Kembalikan folder asli dari temp jika gagal
            if (File::exists($tempPath)) {
                $this->info('Melakukan rollback folder app aktif...');
                if (File::exists($appPath)) {
                    File::deleteDirectory($appPath);
                }
                File::moveDirectory($tempPath, $appPath);
            }
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
