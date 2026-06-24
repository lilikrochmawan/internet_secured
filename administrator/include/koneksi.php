<?php
// Default database configuration
$db_host = '127.0.0.1';
$db_user = 'root';
$db_pass = '';
$db_name = 'tagihan_lotus';
$db_port = 3306;

// Try to load from Laravel's .env file dynamically
$envPath = dirname(dirname(dirname(__FILE__))) . '/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $name = trim($parts[0]);
            $value = trim($parts[1], " \t\n\r\0\x0B\"'");
            
            if ($name === 'DB_HOST') {
                $db_host = $value;
            } elseif ($name === 'DB_USERNAME') {
                $db_user = $value;
            } elseif ($name === 'DB_PASSWORD') {
                $db_pass = $value;
            } elseif ($name === 'DB_DATABASE') {
                $db_name = $value;
            } elseif ($name === 'DB_PORT') {
                $db_port = (int)$value;
            }
        }
    }
}

// Establish MySQLi connection
$koneksi = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);

if ($koneksi->connect_error) {
    error_log("Database connection failed: " . $koneksi->connect_error);
}
?>
