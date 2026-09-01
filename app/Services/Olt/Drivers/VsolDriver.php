<?php

namespace App\Services\Olt\Drivers;

use App\Services\Olt\BaseOltDriver;

class VsolDriver extends BaseOltDriver
{
    public function getUnregisteredOnus(): array
    {
        $output = $this->executeCommand('show ont autofind');
        $onus = [];
        if ($this->connection === 'mock-ssh' || $this->connection === 'mock-telnet') {
            $onus = [
                ['gpon_port' => 'GPON0/1', 'sn' => 'VSOL12345678', 'model' => 'VSOL-ONT'],
            ];
        }
        return $onus;
    }

    public function registerOnu(array $onuData): bool
    {
        $port = $onuData['gpon_port'] ?? 'GPON0/1';
        $sn = $onuData['sn'] ?? '';
        $cmd = "interface $port\n  ont add $sn\nexit";
        $this->executeCommand($cmd);
        return true;
    }

    public function deleteOnu(string $gponPort, string $onuIndex): bool
    {
        $cmd = "interface $gponPort\n  ont delete $onuIndex\nexit";
        $this->executeCommand($cmd);
        return true;
    }

    public function getOnuOpticalPower(string $gponPort, string $onuIndex, string $serialNumber = ''): array
    {
        return [
            'rx_power' => '-18.90 dBm',
            'tx_power' => '1.95 dBm'
        ];
    }

    protected function getMockResponse(string $command): string
    {
        return 'success';
    }
}
