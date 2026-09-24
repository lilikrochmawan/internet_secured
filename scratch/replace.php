<?php
$content = file_get_contents('app/Http/Controllers/Admin/AdminNotificationController.php');
$find1 = "            \$waService = app(\App\Services\WhatsAppService::class);\n            \$isSent = \$waService->sendMessage(\$pelanggan->no_telp, \$customPesan);";
$find2 = "            \$waService = app(\App\Services\WhatsAppService::class);\r\n            \$isSent = \$waService->sendMessage(\$pelanggan->no_telp, \$customPesan);";

$replace = "            \$waService = app(\App\Services\WhatsAppService::class);
            \$isSent = false;
            if (\$request->has('use_waba') && \$request->use_waba === 'on' && !empty(\$request->waba_template)) {
                \$templateParams = [];
                if (!empty(\$request->waba_params)) {
                    \$paramsList = explode(',', \$request->waba_params);
                    foreach (\$paramsList as \$param) {
                        \$param = trim(\$param);
                        if (\$param === 'nama') {
                            \$templateParams[] = \$pelanggan->nama_pelanggan;
                        } elseif (\$param === '\$pesan') {
                            \$templateParams[] = \$customPesan;
                        } elseif (\$param === 'odp') {
                            \$templateParams[] = \$odp->nama_odp ?? \$odpName ?? '';
                        } elseif (\$param === 'odc') {
                            \$templateParams[] = \$odc->nama_odc ?? '';
                        } else {
                            \$templateParams[] = \$param;
                        }
                    }
                }
                \$isSent = \$waService->sendTemplateMessage(
                    \$pelanggan->no_telp,
                    \$request->waba_template,
                    \$templateParams,
                    \$tokenInfo->token ?? '',
                    'id',
                    \$tokenInfo->gateway ?? 'fonnte'
                );
            } else {
                \$isSent = \$waService->sendMessage(\$pelanggan->no_telp, \$customPesan, \$tokenInfo->token ?? '', null, \$tokenInfo->gateway ?? 'fonnte');
            }";

$content = str_replace($find1, $replace, $content);
$content = str_replace($find2, $replace, $content);

file_put_contents('app/Http/Controllers/Admin/AdminNotificationController.php', $content);
echo "Replaced occurrences: " . substr_count($content, "use_waba");
