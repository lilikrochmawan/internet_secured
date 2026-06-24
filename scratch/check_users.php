<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;

$users = DB::table('tb_user')->where('level', '!=', 'user')->get();
foreach ($users as $user) {
    echo "ID: {$user->id} | Name: {$user->nama_user} | Username: {$user->username} | Level: {$user->level}\n";
}
