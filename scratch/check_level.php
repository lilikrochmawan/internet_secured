<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$user = \App\Models\User::where('nama_user', 'like', '%Lilik%')->orWhere('level', 'sales')->get();
foreach($user as $u) {
    echo "ID: {$u->id}, Name: {$u->nama_user}, Level: {$u->level}\n";
}
