<?php
$routesFile = dirname(__FILE__) . '/../routes/web.php';
if (file_exists($routesFile)) {
    echo "--- web.php Blokir Massal references ---\n";
    $lines = file($routesFile);
    foreach ($lines as $num => $line) {
        if (stripos($line, 'blokir') !== false) {
            echo ($num + 1) . ": " . trim($line) . "\n";
        }
    }
}
?>
