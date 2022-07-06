<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ResourceModule.php';

class Motion extends RessourceModule
{
    public static $Variables = [
        ['motion', 'Motion', VARIABLETYPE_BOOLEAN, '~Motion', false, true],
        ['enabled', 'Enabled', VARIABLETYPE_BOOLEAN, '~Switch', true, true]
    ];

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'enabled':
                $this->sendData($this->ReadPropertyString('ResourceID'), 'motion', json_encode(['enabled' => $Value]));
                break;
            }
    }

    public function ReceiveData($JSONString)
    {
        $this->SendDebug('JSON', $JSONString, 0);

        $Data = json_decode($JSONString, true)['Data'][0];

        if (array_key_exists('motion', $Data)) {
            if (array_key_exists('motion', $Data['motion'])) {
                $this->SetValue('Motion', $Data['motion']['motion']);
            }
        }
        if (array_key_exists('enabled', $Data)) {
            $this->SetValue('Enabled', $Data['enabled']);
        }
    }
}
