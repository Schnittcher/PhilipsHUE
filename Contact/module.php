<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ResourceModule.php';

class HUEContact extends RessourceModule
{
    const SERVICE = 'contact';

    public static $Variables = [
        ['state', 'State', VARIABLETYPE_BOOLEAN, '~Window', false, true],
        ['changed', 'Changed', VARIABLETYPE_INTEGER, '', false, true],
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
