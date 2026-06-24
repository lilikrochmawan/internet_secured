<?php
$dir = dirname(__FILE__) . '/../';
$files = [
    'app/Console/Commands/AutoBlockPelanggan.php',
    'app/Http/Controllers/Admin/AdminMonitoringController.php',
    'app/Http/Controllers/Admin/AdminPaketController.php',
    'app/Http/Controllers/Admin/AdminPelangganController.php',
    'app/Http/Controllers/Admin/AdminPenggunaController.php',
    'app/Http/Controllers/Admin/AdminTransaksiController.php',
    'app/Http/Controllers/PaymentController.php',
    'app/Services/MikrotikService.php'
];

foreach ($files as $relPath) {
    $path = $dir . $relPath;
    if (file_exists($path)) {
        $lines = explode("\n", file_get_contents($path));
        foreach ($lines as $i => $line) {
            if (strpos($line, 'Blokir') !== false || strpos($line, 'address-list') !== false || strpos($line, 'comment') !== false) {
                echo "$relPath:" . ($i + 1) . ": $line\n";
            }
        }
    }
}
