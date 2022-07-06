<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ResourceModule.php';

class Light extends RessourceModule
{
    public static $Variables = [
        ['on', 'State', VARIABLETYPE_BOOLEAN, '~Switch', true, true],
        ['brightness', 'Brightness', VARIABLETYPE_INTEGER, '~Intensity.100', true, true],
        ['color', 'Color', VARIABLETYPE_INTEGER, '~HexColor', true, true],
        ['color_temperature', 'Color Temperature', VARIABLETYPE_INTEGER, '', true, true],

    ];

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'on':
                $this->sendData($this->ReadPropertyString('ResourceID'), 'light', json_encode(['on' => ['on' => $Value]]));
                break;
            }
    }

    public function ReceiveData($JSONString)
    {
        $this->SendDebug('JSON', $JSONString, 0);

        $Data = json_decode($JSONString, true)['Data'][0];

        if (array_key_exists('motion', $Data)) {
            $this->SetValue('Motion', $Data['motion']['motion']);
        }
        if (array_key_exists('enabled', $Data)) {
            $this->SetValue('Enabled', $Data['enabled']);
        }
    }
}
