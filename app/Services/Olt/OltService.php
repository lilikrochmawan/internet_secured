<?php

namespace App\Services\Olt;

use App\Models\Olt;
use App\Services\Olt\Drivers\ZteDriver;
use App\Services\Olt\Drivers\HuaweiDriver;
use App\Services\Olt\Drivers\HsgqDriver;
use App\Services\Olt\Drivers\VsolDriver;
use App\Services\Olt\Drivers\CdataDriver;
use App\Services\Olt\Drivers\GlobalDriver;

class OltService
{
    public static function getDriver(Olt $olt): OltDriverInterface
    {
        switch (strtolower($olt->tipe_olt)) {
            case 'zte':
                return new ZteDriver($olt);
            case 'huawei':
                return new HuaweiDriver($olt);
            case 'hsgq':
                return new HsgqDriver($olt);
            case 'vsol':
                return new VsolDriver($olt);
            case 'cdata':
                return new CdataDriver($olt);
            case 'global':
                return new GlobalDriver($olt);
            default:
                throw new \InvalidArgumentException("Driver OLT tipe '{$olt->tipe_olt}' tidak didukung.");
        }
    }
}
