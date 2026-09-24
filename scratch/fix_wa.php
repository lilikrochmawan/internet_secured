<?php

$file = 'app/Http/Controllers/Admin/AdminNotificationController.php';
$content = file_get_contents($file);

// Replace 1: sendTemplateMessage
// Old:
// $isSent = $waService->sendTemplateMessage(
//     $pelanggan->no_telp,
//     $request->waba_template,
//     $templateParams,
//     $tokenInfo->token,
//     'id',
//     $tokenInfo->gateway ?? 'fonnte'
// );
$content = preg_replace(
    '/\$isSent\s*=\s*\$waService->sendTemplateMessage\(\s*\$pelanggan->no_telp,\s*\$request->waba_template,\s*\$templateParams,\s*.*?,\s*\'id\',\s*.*?\);/s',
    "\$isSent = \$waService->sendTemplateMessage(\n                    \$pelanggan->no_telp,\n                    \$customPesan,\n                    \$request->waba_template,\n                    \$templateParams,\n                    'id'\n                );",
    $content
);

// Replace 2: sendMessage
// Old:
// $isSent = $waService->sendMessage($pelanggan->no_telp, $customPesan, $tokenInfo->token ?? '', null, $tokenInfo->gateway ?? 'fonnte');
// OR
// $isSent = $waService->sendMessage($pelanggan->no_telp, $customPesan, $tokenInfo->token, null, $tokenInfo->gateway ?? 'fonnte');
$content = preg_replace(
    '/\$isSent\s*=\s*\$waService->sendMessage\(\$pelanggan->no_telp,\s*\$customPesan,\s*.*?,\s*null,\s*.*?\);/s',
    "\$isSent = \$waService->sendMessage(\$pelanggan->no_telp, \$customPesan);",
    $content
);

file_put_contents($file, $content);
echo "AdminNotificationController fixed.\n";
