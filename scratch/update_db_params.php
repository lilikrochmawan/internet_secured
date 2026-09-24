<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

DB::table('tbl_notif')->update([
    'template_params' => 'https://s3.bablast.id/user_5705/waba-templates/1788969166876-165749520-Gemini_Generated_Image_6p275p6p275p6p27-1.jpg,nama,nama,tagihan,jatuh_tempo'
]);
echo "Updated tbl_notif with public Bablast S3 image URL\n";
