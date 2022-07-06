<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ResourceModule.php';

class Temperature extends RessourceModule
{
    public static $Variables = [
        ['status', 'Status', VARIABLETYPE_STRING, '', false, true],
        ['temperature', 'Temperature', VARIABLETYPE_FLOAT, '~Temperature', false, true]
    ];

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'enabled':
                $this->sendData($this->ReadPropertyString('ResourceID'), 'temperature', json_encode(['enabled' => $Value]));
                break;
            }
    }

    public function ReceiveData($JSONString)
    {
        $this->SendDebug('JSON', $JSONString, 0);
        $Data = json_decode($JSONString, true)['Data'][0];

        if (array_key_exists('status', $Data)) {
            $this->SetValue('status', $Data['status']);
        }
        if (array_key_exists('temperature', $Data)) {
            if (array_key_exists('temperature', $Data['temperature'])) {
                $this->SetValue('temperature', $Data['temperature']['temperature']);
            }
        }
    }
}
