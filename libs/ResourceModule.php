<?php

declare(strict_types=1);

class RessourceModule extends IPSModuleStrict
{
    // Darstellungen (Presentations) von Symcon
    private const PRESENTATION_SWITCH = '{60AE6B26-B3E2-BDB1-A3A1-BE232940664B}';
    private const PRESENTATION_SLIDER = '{6B9CAEEC-5958-C223-30F7-BD36569FC57A}';
    private const PRESENTATION_VALUE = '{3319437D-7CDE-699D-750A-3C6A3841FA75}';
    private const PRESENTATION_VALUE_INPUT = '{6F477326-1683-A2FD-D2E7-477F366ECB62}';
    private const PRESENTATION_ENUMERATION = '{52D9E126-D7D2-2CBB-5E62-4CF7BA7C5D82}';
    private const PRESENTATION_COLOR = '{05CC3CC2-A0B2-5837-A4A7-A07EA0B9DDFB}';

    public function Create(): void
    {
        parent::Create();

        $this->RegisterPropertyString('DeviceID', '');
        $this->RegisterPropertyString('RoomZoneID', '');
        $this->RegisterPropertyString('ResourceID', '');

        $Variables = [];
        foreach (static::$Variables as $Pos => $Variable) {
            $Variables[] = [
                'Ident'        => str_replace(' ', '', $Variable[0]),
                'Name'         => $this->Translate($Variable[1]),
                'VarType'      => $Variable[2],
                'Profile'      => $Variable[3],
                'Action'       => $Variable[4],
                'Pos'          => $Pos + 1,
                'Keep'         => $Variable[5]
            ];
        }
        $this->RegisterPropertyString('Variables', json_encode($Variables));
    }

    public function GetCompatibleParents(): string
    {
        return json_encode(['type' => 'connect', 'moduleIDs' => ['{6786AF05-B089-4BD0-BABA-B2B864CF92E3}']]);
    }

    public function GetConfigurationForm(): string
    {
        $childClassInfo = new ReflectionClass($this);
        $Form = json_decode(file_get_contents(dirname($childClassInfo->getFileName()) . '/form.json'), true);

        // Das Formular liest nur: Neue Variablen des Moduls erscheinen als Zeilen und werden mit dem Speichern übernommen.
        $Form['elements'][2]['values'] = $this->getVariableRows();
        return json_encode($Form);
    }

    public function ApplyChanges(): void
    {
        parent::ApplyChanges();

        //Setze Filter für ReceiveData
        $ResourceID = $this->ReadPropertyString('ResourceID');
        $this->SetReceiveDataFilter('.*' . $ResourceID . '.*');
        $this->maintainVariables();
        $this->updateValues();
    }
    public function updateValues(): void
    {
        if ($this->ReadPropertyString('ResourceID') != '') {
            $result = json_decode($this->getData($this->ReadPropertyString('ResourceID'), static::SERVICE), true);

            if (array_key_exists('data', $result)) {
                if (is_array($result['data'])) {
                    foreach ($result['data'] as $key => $data) {
                        if (array_key_exists('id', $data)) {
                            if ($this->ReadPropertyString('ResourceID') == $data['id']) {
                                $this->mapResultsToValues($data);
                            }
                        }
                    }
                }
            }
        }
    }

    public function ReceiveData(string $JSONString): string
    {
        $this->SendDebug('JSON', $JSONString, 0);
        $result = json_decode($JSONString, true);
        foreach ($result['Data'] as $key => $data) {
            if ($this->ReadPropertyString('ResourceID') == $data['id']) {
                $this->mapResultsToValues($data);
            }
        }
        return '';
    }

    public function getInfos(): void
    {
        $result = $this->getData($this->ReadPropertyString('ResourceID'), static::SERVICE);
    }

    protected function SetValue(string $Ident, mixed $Value): bool
    {
        if (@$this->GetIDForIdent($Ident)) {
            $this->SendDebug('SetValue :: ' . $Ident, $Value, 0);
            return parent::SetValue($Ident, $Value);
        }
        return false;
    }

    protected function GetValue(string $Ident): mixed
    {
        if (@$this->GetIDForIdent($Ident)) {
            return parent::GetValue($Ident);
        }
        return false;
    }
    protected function sendData(string $rid, string $endpoint, string $value)
    {
        $Data['DataID'] = '{03995C27-F41C-4E0C-85C9-099084294C3B}';
        $Buffer['Command'] = 'setResourceData';
        $Buffer['rid'] = $rid;
        $Buffer['endpoint'] = $endpoint;
        $Buffer['value'] = $value;
        $Data['Buffer'] = $Buffer;
        $Data = json_encode($Data);

        if (!$this->HasActiveParent()) {
            return [];
        }

        $this->SendDebug(__FUNCTION__, $Data, 0);
        $result = $this->SendDataToParent($Data);
        $this->SendDebug(__FUNCTION__, $result, 0);
    }

    protected function getData(string $rid, string $endpoint)
    {
        $Data['DataID'] = '{03995C27-F41C-4E0C-85C9-099084294C3B}';
        $Buffer['Command'] = 'getResourceData';
        $Buffer['rid'] = $rid;
        $Buffer['endpoint'] = $endpoint;
        $Data['Buffer'] = $Buffer;
        $Data = json_encode($Data);

        if (!$this->HasActiveParent()) {
            return '{"data":[{}]}';
        }

        $this->SendDebug(__FUNCTION__ . ' :: Data', $Data, 0);
        $result = $this->SendDataToParent($Data);
        $this->SendDebug(__FUNCTION__ . ' :: result', $result, 0);
        return $result;
    }

    /**
     * Liefert zum gespeicherten Profilnamen einer Variable die passende Darstellung.
     * Der Profilname bleibt Schlüssel in der Property "Variables", die Darstellung ersetzt das frühere Variablenprofil.
     * Unbekannte oder leere Profile werden unverändert zurückgegeben.
     */
    protected function presentationFor(string $Profile): array|string
    {
        switch ($Profile) {
            case '~Switch':
                return ['PRESENTATION' => self::PRESENTATION_SWITCH, 'USAGE_TYPE' => 0];
            case '~Intensity.100':
                return ['PRESENTATION' => self::PRESENTATION_SLIDER, 'MIN' => 0, 'MAX' => 100, 'STEP_SIZE' => 1, 'USAGE_TYPE' => 2, 'SUFFIX' => ' %'];
            case '~HexColor':
                return ['PRESENTATION' => self::PRESENTATION_COLOR];
            case 'PhilipsHUE.ColorTemperature':
                return ['PRESENTATION' => self::PRESENTATION_SLIDER, 'MIN' => 153, 'MAX' => 500, 'STEP_SIZE' => 1, 'USAGE_TYPE' => 1, 'GRADIENT_TYPE' => 4, 'SUFFIX' => ' mired'];
            case 'PhilipsHUE.ColorTemperatureKelvin':
                return ['PRESENTATION' => self::PRESENTATION_SLIDER, 'MIN' => 2000, 'MAX' => 6535, 'STEP_SIZE' => 1, 'USAGE_TYPE' => 1, 'GRADIENT_TYPE' => 2, 'SUFFIX' => ' K'];
            case 'PhilipsHUE.Transition':
                return ['PRESENTATION' => self::PRESENTATION_VALUE_INPUT, 'SUFFIX' => ' ms'];
            case '~Motion':
                return $this->valueOptions([
                    [false, 'No motion', 0x808080],
                    [true, 'Motion detected', 0x0000FF]
                ]);
            case '~Illumination':
                return ['PRESENTATION' => self::PRESENTATION_VALUE, 'DIGITS' => 0, 'SUFFIX' => ' lx'];
            case '~Temperature':
                return ['PRESENTATION' => self::PRESENTATION_VALUE, 'DIGITS' => 2, 'SUFFIX' => ' °C'];
            case '~Battery.100':
                return ['PRESENTATION' => self::PRESENTATION_VALUE, 'DIGITS' => 0, 'SUFFIX' => ' %'];
            case 'HUE.Contact':
                return $this->valueOptions([
                    ['no_contact', 'Opened', 0xFF0000],
                    ['contact', 'Closed', 0x00FF00]
                ]);
            case 'HUE.Tamper':
                return $this->valueOptions([
                    ['tampered', 'Tampered', 0xFF0000],
                    ['not_tampered', 'Not tampered', 0x00FF00]
                ]);
            case 'PhilipsHUE.LastEvent':
                return $this->valueOptions([
                    ['initial_press', 'Initial Press', 0x00FF00],
                    ['repeat', 'Repeat', 0xFF0000],
                    ['short_release', 'Short Release', 0xFF8800],
                    ['long_release', 'Long Release', 0x8800FF],
                    ['double_short_release', 'Double Short Release', 0xFFFF00]
                ]);
            case 'PhilipsHUE.ZigbeeState':
                return $this->valueOptions([
                    ['connected', 'Connected', 0x00FF00],
                    ['disconnected', 'Disconnected', 0xFF0000],
                    ['connectivity_issue', 'Connectivity Issue', 0xFF8800],
                    ['unidirectional_incoming', 'Unidirectional Incoming', 0xFF8800]
                ]);
            case 'PhilipsHUE.RelativeRotary.Action':
                return $this->valueOptions([
                    ['start', 'Start', 0x00FF00],
                    ['repeat', 'Repeat', 0xFF0000]
                ]);
            case 'PhilipsHUE.RelativeRotary.Direction':
                return $this->valueOptions([
                    ['clock_wise', 'Clock wise', 0x00FF00],
                    ['counter_clock_wise', 'Counter clock wise', 0xFF0000]
                ]);
            case 'PhilipsHUE.Scene':
                return $this->enumerationOptions([
                    ['activate', 'Activate', 0x00FF00]
                ]);
        }
        return $Profile;
    }

    /**
     * Wertanzeige mit Beschriftung und Farbe je Wert (nur lesend).
     *
     * @param array $Options Liste aus [Wert, englische Beschriftung, Farbe]
     */
    protected function valueOptions(array $Options): array
    {
        $List = [];
        foreach ($Options as [$Value, $Caption, $Color]) {
            $List[] = [
                'Value'              => $Value,
                'Caption'            => $this->Translate($Caption),
                'IconValue'          => '',
                'IconActive'         => false,
                'ColorActive'        => true,
                'ColorValue'         => $Color,
                'ContentColorActive' => false
            ];
        }
        return ['PRESENTATION' => self::PRESENTATION_VALUE, 'OPTIONS' => json_encode($List)];
    }

    /**
     * Aufzählung zum Auslösen oder Auswählen (steuerbare Variable).
     *
     * @param array $Options Liste aus [Wert, Beschriftung, Farbe]; die Beschriftung wird übersetzt, wenn $Translate gesetzt ist
     */
    protected function enumerationOptions(array $Options, bool $Translate = true): array
    {
        $List = [];
        foreach ($Options as [$Value, $Caption, $Color]) {
            $List[] = [
                'Value'      => $Value,
                'Caption'    => $Translate ? $this->Translate($Caption) : $Caption,
                'IconValue'  => '',
                'IconActive' => false,
                'Color'      => $Color
            ];
        }
        return ['PRESENTATION' => self::PRESENTATION_ENUMERATION, 'OPTIONS' => json_encode($List)];
    }
    /**
     * Gespeicherte Variablenzeilen, ergänzt um Variablen des Moduls, die noch nicht gespeichert sind.
     */
    private function getVariableRows(): array
    {
        $NewRows = static::$Variables;
        $NewPos = 0;
        $Variables = json_decode($this->ReadPropertyString('Variables'), true);
        foreach ($Variables as $Variable) {
            foreach ($NewRows as $Index => $Row) {
                if ($Variable['Ident'] == str_replace(' ', '', $Row[0])) {
                    unset($NewRows[$Index]);
                }
            }
            if ($NewPos < $Variable['Pos']) {
                $NewPos = $Variable['Pos'];
            }
        }

        foreach ($NewRows as $NewVariable) {
            $Variables[] = [
                'Ident'        => str_replace(' ', '', $NewVariable[0]),
                'Name'         => $this->Translate($NewVariable[1]),
                'VarType'      => $NewVariable[2],
                'Profile'      => $NewVariable[3],
                'Action'       => $NewVariable[4],
                'Pos'          => ++$NewPos,
                'Keep'         => $NewVariable[5]
            ];
        }
        return array_values($Variables);
    }

    /**
     * Legt die gespeicherten Variablen an oder entfernt sie, je nach Einstellung "Keep".
     */
    private function maintainVariables(): void
    {
        $Variables = json_decode($this->ReadPropertyString('Variables'), true);
        foreach ($Variables as $Variable) {
            @$this->MaintainVariable($Variable['Ident'], $Variable['Name'], $Variable['VarType'], $this->presentationFor($Variable['Profile']), $Variable['Pos'], $Variable['Keep']);
            if ($Variable['Action'] && $Variable['Keep']) {
                $this->EnableAction($Variable['Ident']);
            }
        }
    }
}