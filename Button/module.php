<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ResourceModule.php';
eval('declare(strict_types=1);namespace PhilipsHUE {?>' . file_get_contents(__DIR__ . '/../libs/vendor/SymconModulHelper/VariableProfileHelper.php') . '}');

class Button extends RessourceModule
{
    use \PhilipsHUE\VariableProfileHelper;

    public static $Variables = [
        ['last_event', 'Last Event', VARIABLETYPE_STRING, 'PhilipsHUE.LastEvent', false, true]
    ];

    public function Create()
    {
        parent::Create();

        $this->RegisterProfileStringEx('PhilipsHUE.LastEvent', 'Information', '', '', [
            ['initial_press', $this->Translate('Initial Press'), '', 0x00FF00],
            ['repeat', $this->Translate('Repeat'), '', 0xFF0000],
            ['short_release', $this->Translate('Short Release'), '', 0xFF8800],
            ['long_release', $this->Translate('Long Release'), '', 0x8800FF],
            ['double_short_release', $this->Translate('Double Short Release'), '', 0xFFFF00]
        ]);
    }

    public function ReceiveData($JSONString)
    {
        $this->SendDebug('JSON', $JSONString, 0);
        $Data = json_decode($JSONString, true)['Data'][0];

        if (array_key_exists('button', $Data)) {
            if (array_key_exists('last_event', $Data['button'])) {
                $this->SetValue('last_event', $Data['button']['last_event']);
            }
        }
    }
}