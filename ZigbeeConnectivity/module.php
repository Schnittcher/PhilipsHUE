<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ResourceModule.php';

class HUEZigbeeConnectivity extends RessourceModule
{
    public const SERVICE = 'zigbee_connectivity';

    public static $Variables = [
        ['State', 'Status', VARIABLETYPE_STRING, 'PhilipsHUE.ZigbeeState', false, true]
    ];

    protected function mapResultsToValues(array $Data): void
    {
        if (array_key_exists('status', $Data)) {
            $this->SetValue('State', $Data['status']);
        }
    }
}
