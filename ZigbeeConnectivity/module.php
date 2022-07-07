<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ResourceModule.php';
eval('declare(strict_types=1);namespace PhilipsHUE {?>' . file_get_contents(__DIR__ . '/../libs/vendor/SymconModulHelper/VariableProfileHelper.php') . '}');

class ZigbeeConnectivity extends RessourceModule
{
    use \PhilipsHUE\VariableProfileHelper;
    const SERVICE = 'zigbee_connectivity';

    public static $Variables = [
        ['State', 'Status', VARIABLETYPE_STRING, 'PhilipsHUE.ZigbeeState', false, true]
    ];

    public function Create()
    {
        parent::Create();

        $this->RegisterProfileStringEx('PhilipsHUE.ZigbeeState', 'Information', '', '', [
            ['connected', $this->Translate('Connected'), '', 0x00FF00],
            ['disconnected', $this->Translate('Disconnected'), '', 0xFF0000],
            ['connectivity_issue', $this->Translate('Connectivity Issue'), '', 0xFF8800],
            ['unidirectional_incoming', $this->Translate('Inidirectional Incoming'), '', 0xFF8800]
        ]);
    }

    protected function mapResultsToValues(array $Data)
    {
        if (array_key_exists('status', $Data)) {
            $this->SetValue('Status', $Data['status']);
        }
    }
}
