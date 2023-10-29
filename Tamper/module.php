<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ResourceModule.php';

class HUETamper extends RessourceModule
{
    const SERVICE = 'tamper';

    public static $Variables = [
        ['state', 'State', VARIABLETYPE_STRING, '', false, true],
        ['changed', 'Changed', VARIABLETYPE_INTEGER, '', false, true]
    ];

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
