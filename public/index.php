<?php

if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'order-pemasangan') !== false) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: text/plain');
        echo "HIT INDEX.PHP POST SUCCESSFULLY!\n";
        echo "POST DATA: " . print_r($_POST, true) . "\n";
        echo "FILES DATA: " . print_r($_FILES, true) . "\n";
        exit;
    }
}

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
