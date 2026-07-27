<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "--- Columns in tb_pelanggan ---\n";
print_r(Schema::getColumnListing('tb_pelanggan'));

echo "--- Columns in tb_tagihan ---\n";
print_r(Schema::getColumnListing('tb_tagihan'));

echo "--- Columns in tb_promo ---\n";
print_r(Schema::getColumnListing('tb_promo'));

echo "--- A sample row of tb_pelanggan ---\n";
print_r((array) DB::table('tb_pelanggan')->first());

echo "--- A sample row of tb_tagihan ---\n";
print_r((array) DB::table('tb_tagihan')->first());
