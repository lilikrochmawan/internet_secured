<?php
$files = [
    'app/Http/Controllers/Admin/AdminMonitoringController.php',
    'app/Http/Controllers/Admin/AdminPenggunaController.php'
];

foreach ($files as $file) {
    $path = dirname(__FILE__) . '/../' . $file;
    if (file_exists($path)) {
        echo "=== $file ===\n";
        $content = file_get_contents($path);
        // Find lines with ppp/secret or ppp/active
        $lines = explode("\n", $content);
        foreach ($lines as $num => $line) {
            if (strpos($line, 'ppp/secret') !== false || strpos($line, 'ppp/active') !== false) {
                echo ($num + 1) . ": " . trim($line) . "\n";
            }
        }
    }
}
?>
