<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ResourceModule.php';
eval('declare(strict_types=1);namespace PhilipsHUE {?>' . file_get_contents(__DIR__ . '/../libs/vendor/SymconModulHelper/VariableProfileHelper.php') . '}');

class HUERelativeRotary extends RessourceModule
{
    use \PhilipsHUE\VariableProfileHelper;
    const SERVICE = 'relative_rotary';

    public static $Variables = [
        ['action', 'Action', VARIABLETYPE_STRING, 'PhilipsHUE.RelativeRotary.Action', false, true],
        ['direction', 'Direction', VARIABLETYPE_STRING, 'PhilipsHUE.RelativeRotary.Direction', false, true],
        ['steps', 'Steps', VARIABLETYPE_INTEGER, '', false, true],
        ['duration', 'Duration', VARIABLETYPE_INTEGER, '', false, true]
    ];

    public function Create()
    {
        parent::Create();

        $this->RegisterProfileStringEx('PhilipsHUE.RelativeRotary.Action', 'Information', '', '', [
            ['start', $this->Translate('Start'), '', 0x00FF00],
            ['repeat', $this->Translate('Repeat'), '', 0xFF0000]
        ]);

        $this->RegisterProfileStringEx('PhilipsHUE.RelativeRotary.Direction', 'Information', '', '', [
            ['clock_wise', $this->Translate('Clock wise'), '', 0x00FF00],
            ['counter_clock_wise', $this->Translate('Counter clock wise'), '', 0xFF0000]
        ]);
    }

    protected function mapResultsToValues(array $Data)
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