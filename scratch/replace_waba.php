<?php
$file = 'resources/views/admin/notification/index.blade.php';
$content = file_get_contents($file);

// Add values to waba_template and waba_params
$content = str_replace(
    '<input type="text" name="waba_template" id="waba_template_general" class="form-control" style="font-size: 0.9rem;" placeholder="Contoh: gangguan_jaringan">',
    '<input type="text" name="waba_template" id="waba_template_general" class="form-control" style="font-size: 0.9rem;" placeholder="Contoh: gangguan_jaringan" value="{{ $profile->waba_broadcast_template ?? \'\' }}">',
    $content
);

$content = str_replace(
    '<input type="text" name="waba_template" id="waba_template_odp" class="form-control" style="font-size: 0.9rem;" placeholder="Contoh: gangguan_jaringan">',
    '<input type="text" name="waba_template" id="waba_template_odp" class="form-control" style="font-size: 0.9rem;" placeholder="Contoh: gangguan_jaringan" value="{{ $profile->waba_broadcast_template ?? \'\' }}">',
    $content
);

$content = str_replace(
    '<input type="text" name="waba_template" id="waba_template_odc" class="form-control" style="font-size: 0.9rem;" placeholder="Contoh: gangguan_jaringan">',
    '<input type="text" name="waba_template" id="waba_template_odc" class="form-control" style="font-size: 0.9rem;" placeholder="Contoh: gangguan_jaringan" value="{{ $profile->waba_broadcast_template ?? \'\' }}">',
    $content
);

$content = str_replace(
    '<input type="text" name="waba_params" id="waba_params_general" class="form-control" style="font-size: 0.9rem;" placeholder="Contoh: nama, $pesan">',
    '<input type="text" name="waba_params" id="waba_params_general" class="form-control" style="font-size: 0.9rem;" placeholder="Contoh: nama, $pesan" value="{{ $profile->waba_broadcast_params ?? \'\' }}">',
    $content
);

$content = str_replace(
    '<input type="text" name="waba_params" id="waba_params_odp" class="form-control" style="font-size: 0.9rem;" placeholder="Contoh: nama, odp, $pesan">',
    '<input type="text" name="waba_params" id="waba_params_odp" class="form-control" style="font-size: 0.9rem;" placeholder="Contoh: nama, odp, $pesan" value="{{ $profile->waba_broadcast_params ?? \'\' }}">',
    $content
);

$content = str_replace(
    '<input type="text" name="waba_params" id="waba_params_odc" class="form-control" style="font-size: 0.9rem;" placeholder="Contoh: nama, odc, $pesan">',
    '<input type="text" name="waba_params" id="waba_params_odc" class="form-control" style="font-size: 0.9rem;" placeholder="Contoh: nama, odc, $pesan" value="{{ $profile->waba_broadcast_params ?? \'\' }}">',
    $content
);

$content = str_replace(
    '<input type="checkbox" name="use_waba" id="useWabaGeneral" style="margin-right: 8px;" onchange="toggleWabaFields(\'general\')">',
    '<input type="checkbox" name="use_waba" id="useWabaGeneral" style="margin-right: 8px;" onchange="toggleWabaFields(\'general\')" {{ !empty($profile->waba_broadcast_template) ? \'checked\' : \'\' }}>',
    $content
);

$content = str_replace(
    '<input type="checkbox" name="use_waba" id="useWabaOdp" style="margin-right: 8px;" onchange="toggleWabaFields(\'odp\')">',
    '<input type="checkbox" name="use_waba" id="useWabaOdp" style="margin-right: 8px;" onchange="toggleWabaFields(\'odp\')" {{ !empty($profile->waba_broadcast_template) ? \'checked\' : \'\' }}>',
    $content
);

$content = str_replace(
    '<input type="checkbox" name="use_waba" id="useWabaOdc" style="margin-right: 8px;" onchange="toggleWabaFields(\'odc\')">',
    '<input type="checkbox" name="use_waba" id="useWabaOdc" style="margin-right: 8px;" onchange="toggleWabaFields(\'odc\')" {{ !empty($profile->waba_broadcast_template) ? \'checked\' : \'\' }}>',
    $content
);

// We also need to change the style="display:none;" on waba_fields_* so that they show if checked on load
$content = preg_replace(
    '/<div id="waba_fields_(general|odp|odc)" style="display:none; margin-top: 10px;">/',
    '<div id="waba_fields_$1" style="display: {{ !empty($profile->waba_broadcast_template) ? \'block\' : \'none\' }}; margin-top: 10px;">',
    $content
);

file_put_contents($file, $content);
echo "Replaced successfully\n";
