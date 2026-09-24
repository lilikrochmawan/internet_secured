<?php

require 'c:/xampp/htdocs/internet/vendor/autoload.php';
$app = require_once 'c:/xampp/htdocs/internet/bootstrap/app.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- COLUMNS OF tb_profile ---\n";
$columns = Schema::getColumnListing('tb_profile');
print_r($columns);

echo "\n--- ROW OF tb_profile ---\n";
$row = DB::table('tb_profile')->where('id_profile', 1)->first();
print_r($row);
