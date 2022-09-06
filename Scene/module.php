<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ResourceModule.php';
eval('declare(strict_types=1);namespace PhilipsHUE {?>' . file_get_contents(__DIR__ . '/../libs/vendor/SymconModulHelper/VariableProfileHelper.php') . '}');

class HUEScene extends RessourceModule
{
    use \PhilipsHUE\VariableProfileHelper;
    const SERVICE = 'scene';

    public static $Variables = [
        ['scene', 'Scene', VARIABLETYPE_STRING, 'PhilipsHUE.Scene', true, true]

    ];

    public function Create()
    {
        parent::Create();

        $this->RegisterProfileStringEx('PhilipsHUE.Scene', 'Information', '', '', [
            ['activate', $this->Translate('Activate'), '', 0x00FF00]
        ]);
    }

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case
             'scene':
                $this->sendData($this->ReadPropertyString('ResourceID'), 'scene', json_encode(['recall' => ['action' => 'active']]));
                break;
        }
    }

    protected function mapResultsToValues(array $Data)
    {
        //Nothing
    }
}
