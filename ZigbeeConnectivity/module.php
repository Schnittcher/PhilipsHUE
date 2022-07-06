<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ResourceModule.php';

class ZigbeeConnectivity extends RessourceModule
{
    public static $Variables = [
        ['Status', 'Status', VARIABLETYPE_STRING, '', false, true]
    ];

    public function ReceiveData($JSONString)
    {
        $this->SendDebug('JSON', $JSONString, 0);
        $Data = json_decode($JSONString,true)['Data'][0];

        if (array_key_exists('status',$Data)) {
            $this->SetValue('Status',$Data['status']);
        }
    }
}
