<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ObfuscateApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:obfuscate';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Melakukan obfuscation (pengaburan kode) pada seluruh file PHP di dalam folder app.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $appPath = base_path('app');
        $backupPath = storage_path('framework/cache/.sys_backup');

        $this->info('Memulai proses obfuscation folder app...');

        // 1. Validasi backup
        if (File::exists($backupPath)) {
            $this->error('Folder backup sistem sudah ada! Proses dihentikan.');
            $this->warn('Jika aplikasi saat ini sudah ter-obfuscate dan Anda ingin mengulanginya,');
            $this->warn('silakan jalankan "php artisan app:restore" terlebih dahulu untuk mengembalikan kode asli.');
            return Command::FAILURE;
        }

        // 2. Lakukan pencadangan (backup) folder app
        try {
            $this->info('Mencadangkan folder app asli ke folder sistem aman...');
            File::copyDirectory($appPath, $backupPath);
            $this->info('Pencadangan berhasil!');
        } catch (\Exception $e) {
            $this->error('Gagal membuat cadangan folder app: ' . $e->getMessage());
            return Command::FAILURE;
        }

        // 3. Ambil semua file PHP di folder app
        $files = File::allFiles($appPath);
        $obfuscatedCount = 0;

        $excludeFiles = [
            'ObfuscateApp.php',
            'RestoreApp.php',
        ];

        foreach ($files as $file) {
            // Hanya proses file PHP
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $filename = $file->getFilename();

            // Lewati file command obfuscate dan restore agar tetap bisa terbaca
            if (in_array($filename, $excludeFiles)) {
                continue;
            }

            $filePath = $file->getRealPath();
            $content = File::get($filePath);

            // Cegah double obfuscation jika file sudah ter-obfuscate
            if (str_contains($content, 'Protected by Laravel Obfuscator') || str_contains($content, 'eval(gzinflate(')) {
                $this->line("Melewati (sudah ter-obfuscate): {$file->getRelativePathname()}");
                continue;
            }

            // Bersihkan tag pembuka php (<?php atau <?) di bagian awal
            $cleanContent = preg_replace('/^<\?(php)?\s*/i', '', $content);

            // Kompresi dan encode konten PHP
            $compressed = gzdeflate($cleanContent, 9);
            $encoded = base64_encode($compressed);

            // Format pembungkus obfuscation
            $newContent = "<?php\n" .
                          "// Protected by Laravel Obfuscator\n" .
                          "eval(gzinflate(base64_decode('{$encoded}')));\n";

            // Tulis kembali ke file asli di folder app
            File::put($filePath, $newContent);
            $obfuscatedCount++;
        }

        $this->info("Proses selesai! Berhasil meng-obfuscate {$obfuscatedCount} file PHP di dalam folder app.");
        $this->comment('Catatan: Kode asli tersimpan dengan aman di folder backup sistem. Jangan menghapusnya secara manual.');

        return Command::SUCCESS;
    }
}
