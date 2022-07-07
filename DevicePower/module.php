<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ResourceModule.php';

class DevicePower extends RessourceModule
{
    const SERVICE = 'device_power';

    public static $Variables = [
        ['battery_level', 'Battery Level', VARIABLETYPE_INTEGER, '~Battery.100', false, true],
        ['battery_state', 'Battery State', VARIABLETYPE_STRING, '', false, true],
    ];

    protected function mapResultsToValues(array $Data)
    {
        if (array_key_exists('power_state', $Data)) {
            if (array_key_exists('battery_level', $Data['power_state'])) {
                $this->SetValue('battery_level', $Data['power_state']['battery_level']);
            }
            if (array_key_exists('battery_state', $Data['power_state'])) {
                $this->SetValue('battery_state', $Data['power_state']['battery_state']);
            }
        }
    }
}
