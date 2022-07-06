<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ResourceModule.php';

class DevicePower extends RessourceModule
{
    public static $Variables = [
        ['battery_level', 'Battery Level', VARIABLETYPE_INTEGER, '~Battery.100', false, true],
        ['battery_state', 'Battery State', VARIABLETYPE_STRING, '', false, true],
    ];

    public function ReceiveData($JSONString)
    {
        $this->SendDebug('JSON', $JSONString, 0);
        $Data = json_decode($JSONString, true)['Data'][0];

        if (array_key_exists('power_state', $Data)) {
            if (array_key_exists('battery_level', $Data)) {
                $this->SetValue('battery_level', $Data['power_state']['battery_level']);
            }
            if (array_key_exists('battery_state', $Data)) {
                $this->SetValue('battery_state', $Data['power_state']['battery_state']);
            }
        }
    }
}
