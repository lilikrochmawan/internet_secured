<?php
$file = 'app/Http/Controllers/Admin/AdminTransaksiController.php';
$content = file_get_contents($file);

$content = str_replace(
    "\$tagihan->jatuh_tempo ?? \$pelanggan->jatuh_tempo",
    "\$tagihan->jatuh_tempo ?? \$tx->jatuh_tempo ?? \$pelanggan->jatuh_tempo",
    $content
);

$content = str_replace(
    "number_format(\$tagihan->jml_bayar, 0, ',', '.')",
    "number_format(\$tagihan->jml_bayar ?? \$tx->jml_bayar ?? 0, 0, ',', '.')",
    $content
);

file_put_contents($file, $content);
echo "Fixed missing \$tx fallback in AdminTransaksiController\n";
