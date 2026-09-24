<?php

function replaceParamsInFile($file, $oldLinePattern, $newVarName, $pelangganVar, $tagihanVar = null) {
    if (!file_exists($file)) {
        echo "File not found: $file\n";
        return;
    }
    
    $content = file_get_contents($file);
    
    $tagihanCode = $tagihanVar ? 
        "elseif (\$param === 'jatuh_tempo') \$templateParams[] = \\Carbon\\Carbon::parse({$tagihanVar}->jatuh_tempo ?? {$pelangganVar}->jatuh_tempo)->translatedFormat('d F Y');
                                elseif (\$param === 'tagihan') \$templateParams[] = number_format({$tagihanVar}->jml_bayar, 0, ',', '.');" 
        : 
        "elseif (\$param === 'jatuh_tempo') \$templateParams[] = \\Carbon\\Carbon::parse({$pelangganVar}->jatuh_tempo)->translatedFormat('d F Y');
                                elseif (\$param === 'tagihan') \$templateParams[] = '0';";

    $replacement = "\$templateParams = [];
                    if (!empty({$newVarName})) {
                        \$paramsList = explode(',', {$newVarName});
                        foreach (\$paramsList as \$param) {
                            \$param = trim(\$param);
                            if (\$param === 'nama') \$templateParams[] = {$pelangganVar}->nama_pelanggan ?? {$pelangganVar}->nama ?? '';
                            elseif (\$param === 'no_telp') \$templateParams[] = {$pelangganVar}->no_telp ?? '';
                            $tagihanCode
                            elseif (\$param === 'hari_ini') \$templateParams[] = \\Carbon\\Carbon::now()->translatedFormat('d F Y');
                            else \$templateParams[] = \$param;
                        }
                    }";

    $newContent = preg_replace($oldLinePattern, $replacement, $content);
    if ($newContent !== $content) {
        file_put_contents($file, $newContent);
        echo "Replaced in $file\n";
    } else {
        echo "No match found in $file\n";
    }
}

// 1. PaymentController.php
replaceParamsInFile(
    'app/Http/Controllers/PaymentController.php',
    '/\$templateParams\s*=\s*\$bayar->template_params\s*\?\s*explode\(\',\',\s*\$bayar->template_params\)\s*:\s*\[\];/s',
    '$bayar->template_params',
    '$data_tagihan',
    '$data_tagihan'
);

// 2. AutoBlockPelanggan.php
replaceParamsInFile(
    'app/Console/Commands/AutoBlockPelanggan.php',
    '/\$templateParams\s*=\s*\$blokirSetting->template_params\s*\?\s*explode\(\',\',\s*\$blokirSetting->template_params\)\s*:\s*\[\];/s',
    '$blokirSetting->template_params',
    '$pelanggan',
    '$tagihan'
);

// 3. AdminOrderPemasanganController.php
replaceParamsInFile(
    'app/Http/Controllers/Admin/AdminOrderPemasanganController.php',
    '/\$templateParams\s*=\s*\$notifikasi->template_params\s*\?\s*explode\(\',\',\s*\$notifikasi->template_params\)\s*:\s*\[\];/s',
    '$notifikasi->template_params',
    '$orderPemasangan'
);

// 4. AdminPelangganController.php
replaceParamsInFile(
    'app/Http/Controllers/Admin/AdminPelangganController.php',
    '/\$templateParams\s*=\s*\$notifikasi->template_params\s*\?\s*explode\(\',\',\s*\$notifikasi->template_params\)\s*:\s*\[\];/s',
    '$notifikasi->template_params',
    '$pelanggan'
);

// 5. AdminPromoController.php
replaceParamsInFile(
    'app/Http/Controllers/Admin/AdminPromoController.php',
    '/\$templateParams\s*=\s*\$notifPromo->template_params\s*\?\s*explode\(\',\',\s*\$notifPromo->template_params\)\s*:\s*\[\];/s',
    '$notifPromo->template_params',
    '$pelanggan'
);

// 6. AdminTransaksiController.php (Multiple occurrences)
$atc = 'app/Http/Controllers/Admin/AdminTransaksiController.php';
// a. bayar->template_params
replaceParamsInFile(
    $atc,
    '/\$templateParams\s*=\s*\$bayar->template_params\s*\?\s*explode\(\',\',\s*\$bayar->template_params\)\s*:\s*\[\];/s',
    '$bayar->template_params',
    '$pelanggan',
    '$tagihan'
);
// b. blokirSetting->template_params
replaceParamsInFile(
    $atc,
    '/\$templateParams\s*=\s*\$blokirSetting->template_params\s*\?\s*explode\(\',\',\s*\$blokirSetting->template_params\)\s*:\s*\[\];/s',
    '$blokirSetting->template_params',
    '$pelanggan',
    '$tagihan'
);
// c. reminderSetting->template_params (in broadcast method, tagihan var is $tx)
$atcContent = file_get_contents($atc);
$atcContent = preg_replace(
    '/\$templateParams\s*=\s*\$reminderSetting->template_params\s*\?\s*explode\(\',\',\s*\$reminderSetting->template_params\)\s*:\s*\[\];/s',
    "\$templateParams = [];
                    if (!empty(\$reminderSetting->template_params)) {
                        \$paramsList = explode(',', \$reminderSetting->template_params);
                        foreach (\$paramsList as \$param) {
                            \$param = trim(\$param);
                            if (\$param === 'nama') \$templateParams[] = \$pelanggan->nama_pelanggan ?? \$pelanggan->nama ?? '';
                            elseif (\$param === 'no_telp') \$templateParams[] = \$pelanggan->no_telp ?? '';
                            elseif (\$param === 'jatuh_tempo') \$templateParams[] = \\Carbon\\Carbon::parse(\$tagihan->jatuh_tempo ?? \$tx->jatuh_tempo ?? \$pelanggan->jatuh_tempo)->translatedFormat('d F Y');
                            elseif (\$param === 'tagihan') \$templateParams[] = number_format(\$tagihan->jml_bayar ?? \$tx->jml_bayar ?? 0, 0, ',', '.');
                            elseif (\$param === 'hari_ini') \$templateParams[] = \\Carbon\\Carbon::now()->translatedFormat('d F Y');
                            else \$templateParams[] = \$param;
                        }
                    }",
    $atcContent
);
file_put_contents($atc, $atcContent);
echo "Replaced reminderSetting in $atc\n";

