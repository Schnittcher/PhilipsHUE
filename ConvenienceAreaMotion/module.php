<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ResourceModule.php';

class ConvenienceAreaMotion extends RessourceModule
{
    const SERVICE = 'motion';

    public static $Variables = [
        ['motion', 'Motion', VARIABLETYPE_BOOLEAN, '~Motion', false, true],
        ['sensitivity_status', 'Sensitivity Status', VARIABLETYPE_BOOLEAN, '~Motion', false, true],
        ['sensitivity', 'Sensitivity', VARIABLETYPE_INTEGER, '~Motion', false, true],
        ['sensitivity_max', 'Sensitivity Max', VARIABLETYPE_INTEGER, '~Motion', false, true],
        ['enabled', 'Enabled', VARIABLETYPE_BOOLEAN, '~Switch', true, true]
    ];

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'enabled':
                $this->sendData($this->ReadPropertyString('ResourceID'), 'motion', json_encode(['enabled' => $Value]));
                break;
            case 'sensitivity':
                $sensitivity_max = $this->GetValue('sensitivity_max');
                if ($Value <= $sensitivity_max) {
                    $this->sendData($this->ReadPropertyString('ResourceID'), 'motion', json_encode(['sensitivity' => $Value]));
                }
                break;
            }
    }

    protected function mapResultsToValues(array $Data)
    {
        if (array_key_exists('motion', $Data)) {
            if (array_key_exists('motion', $Data['motion'])) {
                $this->SetValue('motion', $Data['motion']['motion_report']['motion']);
            }
        }
        if (array_key_exists('sensitivity', $Data)) {
            if (array_key_exists('sensitivity', $Data['sensitivity'])) {
                $this->SetValue('sensitivity', $Data['sensitivity']['sensitivity']);
            }
        }
        if (array_key_exists('sensitivity', $Data)) {
            if (array_key_exists('sensitivity_status', $Data['sensitivity'])) {
                $this->SetValue('sensitivity_status', $Data['sensitivity']['sensitivity_status']);
            }
        }
        if (array_key_exists('sensitivity', $Data)) {
            if (array_key_exists('sensitivity_max', $Data['sensitivity'])) {
                $this->SetValue('sensitivity_max', $Data['sensitivity']['sensitivity_max']);
            }
        }
        if (array_key_exists('enabled', $Data)) {
            $this->SetValue('Enabled', $Data['enabled']);
        }
    }
}
