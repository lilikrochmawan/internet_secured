<?php

$files = [
    'c:/xampp/htdocs/internet/resources/views/admin/mapping/index.blade.php',
    'c:/xampp/htdocs/internet/resources/views/admin/odp/index.blade.php',
    'c:/xampp/htdocs/internet/resources/views/admin/odc/index.blade.php',
    'c:/xampp/htdocs/internet/resources/views/admin/pelanggan/index.blade.php',
    'c:/xampp/htdocs/internet/resources/views/admin/order_pemasangan/index.blade.php'
];

foreach ($files as $filepath) {
    $content = file_get_contents($filepath);

    $content = str_replace("maxZoom: 22,", "maxZoom: 24,\nmaxNativeZoom: 21,", $content);

    file_put_contents($filepath, $content);
}
echo "Done maxNativeZoom.";
