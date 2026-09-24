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

    $content = preg_replace(
        '/L\.control\.layers\(\{"Default \(Streets\)":\s*(.+?),\s*"Satelit \(Hybrid\)":\s*(.+?)\}\)\.addTo\((.+?)\);/',
        'addCustomMapToggle($3, $1, $2);',
        $content
    );
    
    $content = preg_replace(
        '/L\.control\.layers\(\{"Streets":\s*(.+?),\s*"Satelit":\s*(.+?)\}\)\.addTo\((.+?)\);/',
        'addCustomMapToggle($3, $1, $2);',
        $content
    );

    file_put_contents($filepath, $content);
}
echo "Done.";
