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

    // Replace maxZoom: 20 with nothing first so we don't duplicate
    $content = str_replace("maxZoom: 20,\n", "", $content);
    $content = str_replace("maxZoom: 20,\r\n", "", $content);
    
    // Add maxZoom: 22 to subdomains: ['mt0'...
    $content = preg_replace(
        '/subdomains:\s*\[([^\]]+)\],/',
        "maxZoom: 22,\n                        subdomains: [$1],",
        $content
    );

    file_put_contents($filepath, $content);
}
echo "Done maxZoom.";
