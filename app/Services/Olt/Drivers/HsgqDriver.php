<?php

namespace App\Services\Olt\Drivers;

use App\Services\Olt\BaseOltDriver;

class HsgqDriver extends BaseOltDriver
{
    private $opticalCache = null;

    public function connect(): bool
    {
        $connected = parent::connect();
        if ($connected && !$this->isMock()) {
            try {
                $this->executeCommand("enable");
                $this->executeCommand("terminal length 0");
                $this->executeCommand("configure");
            } catch (\Exception $e) {
                // Ignore silent errors during initialization
            }
        }
        return $connected;
    }

    public function getUnregisteredOnus(): array
    {
        if ($this->isMock()) {
            return [
                ['gpon_port' => '1', 'sn' => 'HSGQ12345678', 'model' => 'HSGQ-ONU'],
            ];
        }

        $output = $this->executeCommand('show ont-autofind all');
        
        $onus = [];
        $lines = explode("\n", $output);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '---') !== false || strpos($line, 'PON') !== false || strpos($line, 'automatically found') !== false || strpos($line, 'do not exist') !== false) {
                continue;
            }
            $cols = preg_split('/\s+/', $line);
            if (count($cols) >= 3) {
                $onus[] = [
                    'gpon_port' => $cols[0],
                    'sn' => $cols[1],
                    'model' => $cols[2]
                ];
            }
        }
        return $onus;
    }

    public function registerOnu(array $onuData): bool
    {
        if ($this->isMock()) return true;

        $port = $onuData['gpon_port'] ?? '1';
        $sn = $onuData['sn'] ?? '';
        $onuId = $onuData['onu_id'] ?? '1';
        
        $gponPort = strtolower($port);
        if (strpos($gponPort, 'gpon') === false) {
            $gponPort = 'gpon 0/' . $port;
        } else {
            $gponPort = preg_replace('/gpon(\d+)\/(\d+)/i', 'gpon $1/$2', $gponPort);
            $gponPort = preg_replace('/gpon-olt_(\d+)\/(\d+)\/(\d+)/i', 'gpon $2/$3', $gponPort);
        }

        $cmd = "interface $gponPort\r";
        $cmd .= "ont add $onuId sn $sn\r";
        $cmd .= "exit\r";
        
        $this->executeCommand($cmd);
        return true;
    }

    public function deleteOnu(string $gponPort, string $onuIndex): bool
    {
        if ($this->isMock()) return true;

        $gponPort = strtolower($gponPort);
        if (strpos($gponPort, 'gpon') === false) {
            $gponPort = 'gpon 0/' . $gponPort;
        } else {
            $gponPort = preg_replace('/gpon(\d+)\/(\d+)/i', 'gpon $1/$2', $gponPort);
            $gponPort = preg_replace('/gpon-olt_(\d+)\/(\d+)\/(\d+)/i', 'gpon $2/$3', $gponPort);
        }

        $cmd = "interface $gponPort\r";
        $cmd .= "ont delete $onuIndex\r";
        $cmd .= "exit\r";
        
        $this->executeCommand($cmd);
        return true;
    }

    private function loadOpticalCache()
    {
        if ($this->opticalCache !== null) {
            return;
        }

        $this->opticalCache = [];
        $output = $this->executeCommand('show ont-optical all');
        
        $lines = explode("\n", $output);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '---') !== false || strpos($line, 'PON/') !== false) {
                continue;
            }
            $cols = preg_split('/\s+/', $line);
            if (count($cols) >= 8) {
                $portOnu = $cols[0]; // e.g. "1/0" or "1/1"
                $sn = strtoupper($cols[1]); // e.g. "ZICG275E148E"
                
                $txPower = 'N/A';
                $rxPower = 'N/A';
                
                foreach ($cols as $key => $val) {
                    if ($val === 'dBm') {
                        if (isset($cols[$key - 1])) {
                            $powerVal = $cols[$key - 1] . ' dBm';
                            if ($txPower === 'N/A') {
                                $txPower = $powerVal;
                            } else {
                                $rxPower = $powerVal;
                            }
                        }
                    }
                }
                
                $data = [
                    'rx_power' => $rxPower,
                    'tx_power' => $txPower,
                ];
                
                $this->opticalCache[$sn] = $data;
                $this->opticalCache[$portOnu] = $data;
            }
        }
    }

    public function getOnuOpticalPower(string $gponPort, string $onuIndex, string $serialNumber = ''): array
    {
        if ($this->isMock()) {
            return [
                'rx_power' => '-19.20 dBm',
                'tx_power' => '1.50 dBm'
            ];
        }

        $this->loadOpticalCache();

        // 1. Try look up by serial number
        if (!empty($serialNumber)) {
            $cleanSn = strtoupper(trim($serialNumber));
            if (isset($this->opticalCache[$cleanSn])) {
                return $this->opticalCache[$cleanSn];
            }
        }

        // 2. Try look up by port/onu_id from $gponPort
        $ponId = '1';
        $onuId = $onuIndex;

        if (preg_match('/_1\/(\d+)\/(\d+):(\d+)/', $gponPort, $m)) {
            $ponId = $m[2];
            $onuId = $m[3];
        } elseif (preg_match('/(\d+)\/(\d+)/', $gponPort, $m)) {
            $ponId = $m[1];
            $onuId = $m[2];
        }

        $key = "$ponId/$onuId";
        if (isset($this->opticalCache[$key])) {
            return $this->opticalCache[$key];
        }

        // Fallback to individual query
        $cmd = "show ont-optical gpon 0/$ponId $onuId\r";
        $output = $this->executeCommand($cmd);
        
        $tx = 'N/A';
        $rx = 'N/A';
        if (preg_match('/([\d.-]+)\s*dBm\s+([\d.-]+)\s*dBm/i', $output, $m)) {
            $tx = $m[1] . ' dBm';
            $rx = $m[2] . ' dBm';
        }
        
        return [
            'rx_power' => $rx,
            'tx_power' => $tx
        ];
    }

    protected function getMockResponse(string $command): string
    {
        return 'success';
    }
}
