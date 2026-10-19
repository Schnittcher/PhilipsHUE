<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ResourceModule.php';

class HUEButton extends RessourceModule
{
    public const SERVICE = 'button';

    public static $Variables = [
        ['last_event', 'Last Event', VARIABLETYPE_STRING, 'PhilipsHUE.LastEvent', false, true]
    ];

    protected function mapResultsToValues(array $Data): void
    {
        if (array_key_exists('button', $Data)) {
            if (array_key_exists('last_event', $Data['button'])) {
                $this->SetValue('last_event', $Data['button']['last_event']);
            }
        }
    }
}