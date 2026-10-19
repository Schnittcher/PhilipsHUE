<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ResourceModule.php';

class HUEScene extends RessourceModule
{
    public const SERVICE = 'scene';

    public static $Variables = [
        ['scene', 'Scene', VARIABLETYPE_STRING, 'PhilipsHUE.Scene', true, true]

    ];

    public function RequestAction(string $Ident, mixed $Value): void
    {
        switch ($Ident) {
            case 'scene':
                $this->sendData($this->ReadPropertyString('ResourceID'), 'scene', json_encode(['recall' => ['action' => 'active']]));
                break;
        }
    }

    public function ActivateSceneExt(string $action): void
    {
        $this->sendData($this->ReadPropertyString('ResourceID'), 'scene', json_encode(['recall' => ['action' => $action]]));
    }

    protected function mapResultsToValues(array $Data): void
    {
        //Nothing
    }
}
