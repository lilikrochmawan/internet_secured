<?php

$file = 'app/Http/Controllers/Admin/AdminMonitoringController.php';
$content = file_get_contents($file);

$oldLogic1 = <<<EOD
                if (\$isActive && !in_array(\$ipActive, \$isolirList) && \$profile !== 'pppoe-isolir') {
                    \$status = 'aktif';
                } elseif ((in_array(\$ipActive, \$isolirList) && \$ipActive != "") || \$disabled == 'true' || \$profile === 'pppoe-isolir') {
                    \$status = 'terisolir';
                } else {
                    \$status = 'tidak_aktif';
                }
EOD;

$newLogic1 = <<<EOD
                \$isIsolir = (in_array(\$ipAddress, \$isolirList) && \$ipAddress != "") || \$profile === 'pppoe-isolir';

                if (\$isIsolir) {
                    \$status = 'terisolir';
                } elseif (\$isActive && \$disabled !== 'true') {
                    \$status = 'aktif';
                } else {
                    \$status = 'tidak_aktif';
                }
EOD;

$content = str_replace($oldLogic1, $newLogic1, $content);

$oldLogic2 = <<<EOD
                if (\$isActive && !in_array(\$ipActive, \$isolirList) && \$profile !== 'pppoe-isolir') {
                    \$status = 'aktif';
                    \$sortPriority = 1;
                } elseif ((in_array(\$ipActive, \$isolirList) && \$ipActive != "") || \$disabled == 'true' || \$profile === 'pppoe-isolir') {
                    \$status = 'terisolir';
                    \$sortPriority = 2;
                } else {
                    \$status = 'tidak_aktif';
                    \$sortPriority = 3;
                }
EOD;

$newLogic2 = <<<EOD
                \$isIsolir = (in_array(\$ipAddress, \$isolirList) && \$ipAddress != "") || \$profile === 'pppoe-isolir';

                if (\$isIsolir) {
                    \$status = 'terisolir';
                    \$sortPriority = 2;
                } elseif (\$isActive && \$disabled !== 'true') {
                    \$status = 'aktif';
                    \$sortPriority = 1;
                } else {
                    \$status = 'tidak_aktif';
                    \$sortPriority = 3;
                }
EOD;

$content = str_replace($oldLogic2, $newLogic2, $content);

file_put_contents($file, $content);
echo "AdminMonitoringController updated.\n";


$file2 = 'app/Http/Controllers/Admin/AdminDashboardController.php';
$content2 = file_get_contents($file2);

$oldLogic3 = <<<EOD
                        if (\$isActive && !in_array(\$ipActive, \$isolirList) && \$profile !== 'pppoe-isolir') {
                            \$aktifCount++;
                        } elseif ((in_array(\$ipActive, \$isolirList) && \$ipActive != "") || \$disabled == 'true' || \$profile === 'pppoe-isolir') {
                            \$terisolirCount++;
                        } else {
                            \$nonaktifCount++;
                        }
EOD;

$newLogic3 = <<<EOD
                        \$ipAddress = \$ipActive !== "" ? \$ipActive : (\$secret['remote-address'] ?? '-');
                        \$isIsolir = (in_array(\$ipAddress, \$isolirList) && \$ipAddress != "") || \$profile === 'pppoe-isolir';

                        if (\$isIsolir) {
                            \$terisolirCount++;
                        } elseif (\$isActive && \$disabled !== 'true') {
                            \$aktifCount++;
                        } else {
                            \$nonaktifCount++;
                        }
EOD;

$content2 = str_replace($oldLogic3, $newLogic3, $content2);

file_put_contents($file2, $content2);
echo "AdminDashboardController updated.\n";
