<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ResourceModule.php';
eval('declare(strict_types=1);namespace PhilipsHUE {?>' . file_get_contents(__DIR__ . '/../libs/vendor/SymconModulHelper/VariableProfileHelper.php') . '}');

class HUEContact extends RessourceModule
{
    use \PhilipsHUE\VariableProfileHelper;

    const SERVICE = 'contact';

    public static $Variables = [
        ['state', 'State', VARIABLETYPE_STRING, 'HUE.Contact', false, true],
        ['changed', 'Changed', VARIABLETYPE_INTEGER, '', false, true],
        ['enabled', 'Enabled', VARIABLETYPE_BOOLEAN, '~Switch', true, true]
    ];

    public function Create()
    {
        parent::Create();

        if (!IPS_VariableProfileExists('HUE.Contact')) {
            $this->RegisterProfileStringEx('HUE.Contact', 'Window', '', '', [
                ['no_contact', $this->Translate('Opened'), '', 0xFF0000],
                ['contact', $this->Translate('Closed'), '', 0x00FF00]
            ]);
        }
    }

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'enabled':
                $this->sendData($this->ReadPropertyString('ResourceID'), 'contact', json_encode(['enabled' => $Value]));
                break;
            }
    }

    protected function mapResultsToValues(array $Data)
    {
        if (array_key_exists('contact_report', $Data)) {
            if (array_key_exists('state', $Data['contact_report'])) {
                $this->SetValue('state', $Data['contact_report']['state']);
            }
            if (array_key_exists('changed', $Data['contact_report'])) {
                $this->SetValue('changed', $Data['contact_report']['changed']);
            }
        }
        if (array_key_exists('enabled', $Data)) {
            $this->SetValue('Enabled', $Data['enabled']);
        }
    }
}
