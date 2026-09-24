<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$cols = DB::select('SHOW COLUMNS FROM tbl_penggunamikrotik'); foreach ($cols as $c) { echo $c->Field . "\n"; }
