<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ResourceModule.php';

class Button extends RessourceModule
{
    public static $Variables = [
        ['last_event', 'Last Event', VARIABLETYPE_STRING, '', false, true]
    ];

    public function ReceiveData($JSONString)
    {
        $this->SendDebug('JSON', $JSONString, 0);
        $Data = json_decode($JSONString, true)['Data'][0];

        if (array_key_exists('button', $Data)) {
            if (array_key_exists('last_event', $Data)) {
                $this->SetValue('last_event', $Data['button']['last_event']);
            }
        }
    }
}