<?php

namespace App\Services\Olt\Drivers;

use App\Services\Olt\BaseOltDriver;

class ZteDriver extends BaseOltDriver
{
    public function getUnregisteredOnus(): array
    {
        $output = $this->executeCommand('show gpon onu uncfg');
        
        $onus = [];
        $lines = explode("\n", $output);
        $currentOnu = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (preg_match('/^Onu interface\s+:\s+(gpon-onu_\d+\/\d+\/\d+:\d+)/i', $line, $m)) {
                if (!empty($currentOnu)) {
                    $onus[] = $currentOnu;
                }
                $currentOnu = ['gpon_port' => $m[1]];
            } elseif (preg_match('/^Sn\s+:\s+([a-zA-Z0-9]+)/i', $line, $m)) {
                $currentOnu['sn'] = $m[1];
            } elseif (preg_match('/^Type\s+:\s+([a-zA-Z0-9-_]+)/i', $line, $m)) {
                $currentOnu['model'] = $m[1];
            }
        }

        if (!empty($currentOnu)) {
            $onus[] = $currentOnu;
        }

        // Fallback for mock
        if (empty($onus) && ($this->connection === 'mock-ssh' || $this->connection === 'mock-telnet')) {
            $onus = [
                ['gpon_port' => 'gpon-olt_1/1/1', 'sn' => 'PTEGA5687192', 'model' => 'F670L'],
                ['gpon_port' => 'gpon-olt_1/1/2', 'sn' => 'CMDCA8F874DC', 'model' => 'F670T'],
            ];
        }

        return $onus;
    }

    public function registerOnu(array $onuData): bool
    {
        $port = $onuData['gpon_port'] ?? 'gpon-olt_1/1/1';
        $onuId = $onuData['onu_id'] ?? '1';
        $type = $onuData['model'] ?? 'F670L';
        $sn = $onuData['sn'] ?? '';

        $cmd = "interface $port\n  onu $onuId type $type sn $sn\nexit";
        $this->executeCommand($cmd);
        return true;
    }

    public function deleteOnu(string $gponPort, string $onuIndex): bool
    {
        $cmd = "interface $gponPort\n  no onu $onuIndex\nexit";
        $this->executeCommand($cmd);
        return true;
    }

    public function getOnuOpticalPower(string $gponPort, string $onuIndex, string $serialNumber = ''): array
    {
        $cmd = "show pon power attenuation $gponPort:$onuIndex";
        $output = $this->executeCommand($cmd);

        $rx = null;
        $tx = null;

        if (preg_match('/Rx power\s+:\s+(-?\d+\.?\d*)/i', $output, $m)) {
            $rx = $m[1] . ' dBm';
        }
        if (preg_match('/Tx power\s+:\s+(-?\d+\.?\d*)/i', $output, $m)) {
            $tx = $m[1] . ' dBm';
        }

        if ($rx === null && ($this->connection === 'mock-ssh' || $this->connection === 'mock-telnet')) {
            $rx = '-18.50 dBm';
            $tx = '2.10 dBm';
        }

        return ['rx_power' => $rx, 'tx_power' => $tx];
    }

    protected function getMockResponse(string $command): string
    {
        if (strpos($command, 'show gpon onu uncfg') !== false) {
            return "Onu interface  : gpon-onu_1/1/1:1\nType           : F670L\nSn             : PTEGA5687192\n\nOnu interface  : gpon-onu_1/1/2:1\nType           : F670T\nSn             : CMDCA8F874DC\n";
        }
        if (strpos($command, 'show pon power attenuation') !== false) {
            return "Rx power : -18.50 dBm\nTx power : 2.10 dBm\n";
        }
        return 'success';
    }
}
