<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$templates = DB::table('tbl_template_wa')->get();
foreach ($templates as $t) {
    echo "ID: $t->id_template, Name: $t->template_name, Params: $t->template_params\n";
}
