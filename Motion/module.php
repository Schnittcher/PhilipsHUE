<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ResourceModule.php';

class Motion extends RessourceModule
{
    const SERVICE = 'motion';

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

    protected function mapResultsToValues(array $Data)
    {
        if (array_key_exists('motion', $Data)) {
            if (array_key_exists('motion', $Data['motion'])) {
                $this->SetValue('motion', $Data['motion']['motion']);
            }
        }
        if (array_key_exists('enabled', $Data)) {
            $this->SetValue('Enabled', $Data['enabled']);
        }
    }
}
