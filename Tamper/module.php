<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ResourceModule.php';
eval('declare(strict_types=1);namespace PhilipsHUE {?>' . file_get_contents(__DIR__ . '/../libs/vendor/SymconModulHelper/VariableProfileHelper.php') . '}');

class HUETamper extends RessourceModule
{
    use \PhilipsHUE\VariableProfileHelper;

    const SERVICE = 'tamper';

    public static $Variables = [
        ['state', 'State', VARIABLETYPE_STRING, 'HUE.Tamper', false, true],
        ['changed', 'Changed', VARIABLETYPE_INTEGER, '', false, true]
    ];

    public function Create()
    {
        parent::Create();

        if (!IPS_VariableProfileExists('HUE.Tamper')) {
            $this->RegisterProfileStringEx('HUE.Tamper', 'Warning', '', '', [
                ['tampered', $this->Translate('Tampered'), '', 0xFF0000],
                ['not_tampered', $this->Translate('Not tampered'), '', 0x00FF00]
            ]);
        }
    }

    protected function mapResultsToValues(array $Data)
    {
        if (array_key_exists('tamper_reports', $Data)) {
            if (array_key_exists('state', $Data['tamper_reports'])) {
                $this->SetValue('state', $Data['tamper_reports']['state']);
            }
            if (array_key_exists('changed', $Data['tamper_reports'])) {
                $this->SetValue('changed', $Data['tamper_reports']['changed']);
            }
        }
        if (array_key_exists('enabled', $Data)) {
            $this->SetValue('Enabled', $Data['enabled']);
        }
    }
}