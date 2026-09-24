<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
DB::statement('ALTER TABLE tb_profile ADD COLUMN waba_broadcast_template VARCHAR(255) NULL');
DB::statement('ALTER TABLE tb_profile ADD COLUMN waba_broadcast_params TEXT NULL');
echo 'Added columns to tb_profile';
