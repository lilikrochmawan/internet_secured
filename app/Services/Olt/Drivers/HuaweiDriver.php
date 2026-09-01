<?php

namespace App\Services\Olt\Drivers;

use App\Services\Olt\BaseOltDriver;

class HuaweiDriver extends BaseOltDriver
{
    public function getUnregisteredOnus(): array
    {
        $output = $this->executeCommand('display ont autofind all');
        
        $onus = [];
        $lines = explode("\n", $output);
        $currentOnu = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (preg_match('/^F\/S\/P\s+:\s+(\d+\/\d+\/\d+)/i', $line, $m)) {
                if (!empty($currentOnu)) {
                    $onus[] = $currentOnu;
                }
                $currentOnu = ['gpon_port' => $m[1]];
            } elseif (preg_match('/^Ont SN\s+:\s+([a-zA-Z0-9]+)/i', $line, $m)) {
                $currentOnu['sn'] = $m[1];
            } elseif (preg_match('/^Equipment ID\s+:\s+([a-zA-Z0-9-_]+)/i', $line, $m)) {
                $currentOnu['model'] = $m[1];
            }
        }

        if (!empty($currentOnu)) {
            $onus[] = $currentOnu;
        }

        if (empty($onus) && ($this->connection === 'mock-ssh' || $this->connection === 'mock-telnet')) {
            $onus = [
                ['gpon_port' => '0/1/1', 'sn' => 'HWTC12345678', 'model' => 'HG8546M'],
                ['gpon_port' => '0/1/2', 'sn' => 'HWTC87654321', 'model' => 'EG8145V5'],
            ];
        }

        return $onus;
    }

    public function registerOnu(array $onuData): bool
    {
        $portParts = explode('/', $onuData['gpon_port'] ?? '0/1/1');
        $frame = $portParts[0] ?? '0';
        $slot = $portParts[1] ?? '1';
        $port = $portParts[2] ?? '1';
        $ontId = $onuData['onu_id'] ?? '1';
        $sn = $onuData['sn'] ?? '';

        $cmd = "interface gpon $frame/$slot\n  ont add $port $ontId sn-auth $sn omci ont-lineprofile-id 10 ont-srvprofile-id 10\nquit";
        $this->executeCommand($cmd);
        return true;
    }

    public function deleteOnu(string $gponPort, string $onuIndex): bool
    {
        $portParts = explode('/', $gponPort);
        $frame = $portParts[0] ?? '0';
        $slot = $portParts[1] ?? '1';
        $port = $portParts[2] ?? '1';

        $cmd = "interface gpon $frame/$slot\n  ont delete $port $onuIndex\nquit";
        $this->executeCommand($cmd);
        return true;
    }

    public function getOnuOpticalPower(string $gponPort, string $onuIndex, string $serialNumber = ''): array
    {
        $portParts = explode('/', $gponPort);
        $frame = $portParts[0] ?? '0';
        $slot = $portParts[1] ?? '1';
        $port = $portParts[2] ?? '1';

        $cmd = "display ont optical-info $frame $slot $port $onuIndex";
        $output = $this->executeCommand($cmd);

        $rx = null;
        $tx = null;

        if (preg_match('/Rx optical power\(dBm\)\s+:\s+(-?\d+\.?\d*)/i', $output, $m)) {
            $rx = $m[1] . ' dBm';
        }
        if (preg_match('/Tx optical power\(dBm\)\s+:\s+(-?\d+\.?\d*)/i', $output, $m)) {
            $tx = $m[1] . ' dBm';
        }

        if ($rx === null && ($this->connection === 'mock-ssh' || $this->connection === 'mock-telnet')) {
            $rx = '-19.45 dBm';
            $tx = '1.80 dBm';
        }

        return ['rx_power' => $rx, 'tx_power' => $tx];
    }

    protected function getMockResponse(string $command): string
    {
        if (strpos($command, 'display ont autofind') !== false) {
            return " F/S/P                   : 0/1/1\n Ont SN                  : HWTC12345678\n Equipment ID            : HG8546M\n\n F/S/P                   : 0/1/2\n Ont SN                  : HWTC87654321\n Equipment ID            : EG8145V5\n";
        }
        if (strpos($command, 'display ont optical-info') !== false) {
            return " Rx optical power(dBm)                  : -19.45\n Tx optical power(dBm)                  : 1.80\n";
        }
        return 'success';
    }
}
