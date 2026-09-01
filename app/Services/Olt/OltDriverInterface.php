<?php

namespace App\Services\Olt;

interface OltDriverInterface
{
    /**
     * Connect to the OLT device.
     *
     * @return bool
     */
    public function connect(): bool;

    /**
     * Fetch the list of unregistered ONUs (Autofind list).
     *
     * @return array
     */
    public function getUnregisteredOnus(): array;

    /**
     * Register/Authorize an ONU on the OLT.
     *
     * @param array $onuData
     * @return bool
     */
    public function registerOnu(array $onuData): bool;

    /**
     * Delete an ONU from the OLT GPON config.
     *
     * @param string $gponPort
     * @param string $onuIndex
     * @return bool
     */
    public function deleteOnu(string $gponPort, string $onuIndex): bool;

    /**
     * Fetch optical power (redaman) in dBm.
     *
     * @param string $gponPort
     * @param string $onuIndex
     * @param string $serialNumber
     * @return array Array with 'rx_power' and 'tx_power' or nulls
     */
    public function getOnuOpticalPower(string $gponPort, string $onuIndex, string $serialNumber = ''): array;

    /**
     * Close the connection to the OLT.
     *
     * @return void
     */
    public function disconnect(): void;
}
