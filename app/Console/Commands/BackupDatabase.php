<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup';
    protected $description = 'Backup the MySQL database to a SQL file';

    public function handle()
    {
        $connection = config('database.default');
        $dbConfig = config("database.connections.{$connection}");

        if ($connection !== 'mysql') {
            $this->error('Hanya koneksi mysql yang didukung.');
            return 1;
        }

        $host = $dbConfig['host'];
        $port = $dbConfig['port'] ?? 3306;
        $database = $dbConfig['database'];
        $username = $dbConfig['username'];
        $password = $dbConfig['password'];

        $backupDir = '/var/www/html/internet/backupdb';
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $backupDir = base_path('backupdb');
        }
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filename = "backup-{$database}-" . Carbon::now()->format('Y-m-d_H-i-s') . ".sql";
        $backupPath = $backupDir . DIRECTORY_SEPARATOR . $filename;

        // Menentukan path mysqldump secara otomatis
        $mysqldump = 'mysqldump';
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $xamppPath = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
            if (file_exists($xamppPath)) {
                $mysqldump = '"' . $xamppPath . '"';
            }
        }

        // Build command (tanpa redirect shell agar aman)
        if (!empty($password)) {
            $escapedPassword = addcslashes($password, '"\\$');
            $command = "{$mysqldump} --host={$host} --port={$port} --user={$username} --password=\"{$escapedPassword}\" {$database}";
        } else {
            $command = "{$mysqldump} --host={$host} --port={$port} --user={$username} {$database}";
        }

        $this->info("Menjalankan backup database...");

        $descriptorspec = [
            0 => ["pipe", "r"], // stdin
            1 => ["file", $backupPath, "w"], // stdout goes directly to SQL file
            2 => ["pipe", "w"]  // stderr
        ];

        $process = proc_open($command, $descriptorspec, $pipes);

        if (is_resource($process)) {
            fclose($pipes[0]);

            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            $returnValue = proc_close($process);

            if ($returnValue === 0) {
                $this->info("Database berhasil di-backup ke: {$backupPath}");
                Log::info("Database backup sukses: {$filename}");
                
                // Hapus backup lama (lebih dari 7 hari)
                $this->cleanOldBackups($backupDir);
                
                return 0;
            } else {
                if (file_exists($backupPath)) {
                    unlink($backupPath);
                }
                $this->error("Gagal melakukan backup database. Code: {$returnValue}");
                $this->error("Error: " . trim($stderr));
                Log::error("Database backup gagal. Code: {$returnValue}. Error: " . trim($stderr));
                return 1;
            }
        }

        $this->error("Gagal menjalankan proses mysqldump.");
        return 1;
    }

    private function cleanOldBackups($dir)
    {
        $files = glob($dir . '/*.sql');
        $now = time();
        $daysToKeep = 14;
        
        foreach ($files as $file) {
            if (is_file($file)) {
                if ($now - filemtime($file) >= 60 * 60 * 24 * $daysToKeep) {
                    unlink($file);
                    $this->info("Backup lama dihapus: " . basename($file));
                }
            }
        }
    }
}
