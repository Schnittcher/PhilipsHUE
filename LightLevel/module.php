<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ResourceModule.php';

class HUELightLevel extends RessourceModule
{
    const SERVICE = 'light_level';

    public static $Variables = [
        ['enabled', 'Enabled', VARIABLETYPE_BOOLEAN, '~Switch', true, true],
        ['light_level', 'Light Level', VARIABLETYPE_INTEGER, '~Illumination', false, true]
    ];

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'enabled':
                $this->sendData($this->ReadPropertyString('ResourceID'), 'light_level', json_encode(['enabled' => $Value]));
                break;
            }
    }

    protected function mapResultsToValues(array $Data)
    {
        if (array_key_exists('light', $Data)) {
            if (array_key_exists('light_level', $Data['light'])) {
                $this->SetValue('light_level', $Data['light']['light_level']);
            }
        }
        if (array_key_exists('enabled', $Data)) {
            $this->SetValue('enabled', $Data['enabled']);
        }
    }
}
