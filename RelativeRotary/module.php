<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ResourceModule.php';

class HUERelativeRotary extends RessourceModule
{
    public const SERVICE = 'relative_rotary';

    public static $Variables = [
        ['action', 'Action', VARIABLETYPE_STRING, 'PhilipsHUE.RelativeRotary.Action', false, true],
        ['direction', 'Direction', VARIABLETYPE_STRING, 'PhilipsHUE.RelativeRotary.Direction', false, true],
        ['steps', 'Steps', VARIABLETYPE_INTEGER, '', false, true],
        ['duration', 'Duration', VARIABLETYPE_INTEGER, '', false, true]
    ];

    protected function mapResultsToValues(array $Data): void
    {
        if (array_key_exists('relative_rotary', $Data)) {
            if (array_key_exists('action', $Data['relative_rotary']['last_event'])) {
                $this->SetValue('action', $Data['relative_rotary']['last_event']['action']);
            }
            if (array_key_exists('direction', $Data['relative_rotary']['last_event']['rotation'])) {
                $this->SetValue('direction', $Data['relative_rotary']['last_event']['rotation']['direction']);
                $this->SetValue('steps', $Data['relative_rotary']['last_event']['rotation']['steps']);
                $this->SetValue('duration', $Data['relative_rotary']['last_event']['rotation']['duration']);
            }
        }
    }
}