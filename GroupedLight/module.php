<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ResourceModule.php';
// Der Trait wird nur einmal deklariert, auch wenn mehrere Module der Bibliothek im selben PHP-Prozess geladen werden.
if (!trait_exists('PhilipsHUE\DebugHelper', false)) {
    eval('declare(strict_types=1);namespace PhilipsHUE {?>' . file_get_contents(__DIR__ . '/../libs/vendor/SymconModulHelper/DebugHelper.php') . '}');
}
require_once __DIR__ . '/../libs/ColorHelperStrict.php';

class HUEGroupedLight extends RessourceModule
{
    use \PhilipsHUE\DebugHelper;
    use \PhilipsHUE\ColorHelperStrict;
    public const SERVICE = 'grouped_light';

    public static $Variables = [
        ['on', 'State', VARIABLETYPE_BOOLEAN, '~Switch', true, true],
        ['brightness', 'Brightness', VARIABLETYPE_INTEGER, '~Intensity.100', true, true],
        ['color', 'Color', VARIABLETYPE_INTEGER, '~HexColor', true, true],
        ['color_xy', 'Color XY', VARIABLETYPE_STRING, 'PhilipsHUE.ColorXY', true, true],
        ['color_temperature', 'Color Temperature', VARIABLETYPE_INTEGER, 'PhilipsHUE.ColorTemperature', true, true],
        ['color_temperature_kelvin', 'Color Temperature', VARIABLETYPE_INTEGER, 'PhilipsHUE.ColorTemperatureKelvin', true, true],
        ['transition', 'Transition', VARIABLETYPE_INTEGER, 'PhilipsHUE.Transition', true, true],
        ['scene', 'Scene', VARIABLETYPE_STRING, '', true, true],
    ];

    public function Create(): void
    {
        parent::Create();

        $this->RegisterPropertyBoolean('stausColorColorTemperature', false);
    }

    public function ApplyChanges(): void
    {
        parent::ApplyChanges();

        // Die Szenen der Gruppe sind Teil der Darstellung von "scene" und gehen beim Pflegen der Variablen verloren.
        if ($this->HasActiveParent()) {
            $this->updateSceneProfileNeu();
        }
    }

    public function RequestAction(string $Ident, mixed $Value): void
    {
        switch ($Ident) {
            case 'on':
                $this->sendData($this->ReadPropertyString('ResourceID'), 'grouped_light', json_encode(['on' => ['on' => $Value]]));
                break;
            case 'brightness':
                $duration = $this->GetValue('transition') != false ? $this->GetValue('transition') : 0;
                if ($Value > 0) {
                    $this->sendData($this->ReadPropertyString('ResourceID'), 'grouped_light', json_encode(['on' => ['on' => true], 'dimming' => ['brightness' => $Value], 'dynamics' => ['duration' => $duration]]));
                } else {
                    $this->sendData($this->ReadPropertyString('ResourceID'), 'grouped_light', json_encode(['on' => ['on' => false], 'dynamics' => ['duration' => $duration]]));
                }
                break;
            case 'color_temperature':
                $duration = $this->GetValue('transition') != false ? $this->GetValue('transition') : 0;
                $this->sendData($this->ReadPropertyString('ResourceID'), 'grouped_light', json_encode(['on' => ['on' => true], 'color_temperature' => ['mirek' => $Value], 'dynamics' => ['duration' => $duration]]));
                if ($this->ReadPropertyBoolean('stausColorColorTemperature')) {
                    $this->SetValue('color_temperature', $Value);
                }
                break;
            case 'color_temperature_kelvin':
                $duration = $this->GetValue('transition') != false ? $this->GetValue('transition') : 0;
                $mirek = round(1000000 / $Value);
                $this->sendData($this->ReadPropertyString('ResourceID'), 'light', json_encode(['on' => ['on' => true], 'color_temperature' => ['mirek' => $mirek], 'dynamics' => ['duration' => $duration]]));
                if ($this->ReadPropertyBoolean('stausColorColorTemperature')) {
                    $this->SetValue('color_temperature_kelvin', $Value);
                }
                break;
            case 'transition':
                $this->SetValue('transition', $Value);
                break;
            case 'color':
                $duration = $this->GetValue('transition') != false ? $this->GetValue('transition') : 0;
                $RGB = $this->HexToRGB((int) $Value);
                $this->SendDebug('RGB', $RGB, 0);
                $cie = $this->RGBToXy($RGB);
                $this->SendDebug('Color', $cie, 0);
                $this->sendData($this->ReadPropertyString('ResourceID'), 'grouped_light', json_encode(['on' => ['on' => true], 'color' => ['xy' => ['x' => $cie['x'], 'y' => $cie['y']]], 'dynamics' => ['duration' => $duration]]));
                if ($this->ReadPropertyBoolean('stausColorColorTemperature')) {
                    // Wie eine Meldung der Bridge: beide Farbvariablen aus xy und der aktuellen Helligkeit setzen
                    $this->updateColor(['color' => ['xy' => ['x' => $cie['x'], 'y' => $cie['y']]]]);
                }
                break;
            case 'color_xy':
                $Xy = $this->parseXy($Value);
                if ($Xy === null) {
                    $this->SendDebug('color_xy', 'Invalid value: ' . json_encode($Value), 0);
                    break;
                }
                $duration = $this->GetValue('transition') != false ? $this->GetValue('transition') : 0;
                $this->sendData($this->ReadPropertyString('ResourceID'), 'grouped_light', json_encode(['on' => ['on' => true], 'color' => ['xy' => $Xy], 'dynamics' => ['duration' => $duration]]));
                if ($this->ReadPropertyBoolean('stausColorColorTemperature')) {
                    $this->updateColor(['color' => ['xy' => $Xy]]);
                }
                break;
            case 'scene':
                $this->sendData($Value, 'scene', json_encode(['recall' => ['action' => 'active']]));
                break;
        }
    }

    public function setAlert(): void
    {
        $this->sendData($this->ReadPropertyString('ResourceID'), 'grouped_light', json_encode(['alert' => ['action' => 'breathe']]));
    }

    public function setColor(int $color, array $OptParams): void
    {
        $RGB = $this->HexToRGB($color);
        $this->SendDebug('RGB', $RGB, 0);
        $cie = $this->RGBToXy($RGB);

        $params = ['color' => ['xy' => ['x' => $cie['x'], 'y' => $cie['y']]]];
        $params = array_merge($params, $this->limitBrightness($OptParams));
        $params = json_encode($params);
        $this->SendDebug('setColor :: Params', $params, 0);
        $this->sendData($this->ReadPropertyString('ResourceID'), 'grouped_light', $params);
    }

    public function updateSceneProfileNeu(): void
    {
        $scenes = $this->getScenesbyGroup();
        $RoomZoneID = $this->ReadPropertyString('RoomZoneID');

        if (array_key_exists($RoomZoneID, $scenes)) {
            $Options = [];
            foreach ($scenes[$RoomZoneID] as $scene) {
                $Options[] = [$scene['id'], $scene['metadata']['name'], -1];
            }

            $variableID = $this->GetIDForIdent('scene');
            if (IPS_VariableExists($variableID)) {
                $position = IPS_GetObject($variableID)['ObjectPosition'];
                $this->RegisterVariableString('scene', $this->Translate('Scene'), $this->enumerationOptions($Options, false), $position);
            }
        }
    }

    public function getOwnerInfo(): ?array
    {
        $groupedLight = json_decode($this->getData($this->ReadPropertyString('ResourceID'), 'grouped_light'), true);
        $groupedLightOwnerType = $groupedLight['data'][0]['owner']['rtype'];
        $result = json_decode($this->getData($this->ReadPropertyString('RoomZoneID'), $groupedLightOwnerType), true);
        return $result['data'][0];
    }

    protected function mapResultsToValues(array $Data): void
    {
        if (array_key_exists('on', $Data)) {
            if (array_key_exists('on', $Data)) {
                $this->SetValue('on', $Data['on']['on']);
            }
        }
        if (array_key_exists('dimming', $Data)) {
            if (array_key_exists('brightness', $Data['dimming'])) {
                $this->SetValue('brightness', $Data['dimming']['brightness']);
            }
        }
        if (array_key_exists('dynamics', $Data)) {
            if (array_key_exists('transition', $Data['dynamics'])) {
                $this->SetValue('transition', $Data['dynamics']['transition']);
            }
        }

        if (array_key_exists('color_temperature', $Data)) {
            if (array_key_exists('mirek', $Data['color_temperature'])) {
                if ($Data['color_temperature']['mirek'] != null) { //Bei Lampen, welche nicht von Philips sind, kann es zu Problemen mit der Farbtemperatur kommen
                    $this->SetValue('color_temperature', $Data['color_temperature']['mirek']);
                }
            }
        }

        $this->updateColor($Data);
    }

    private function getScenesbyGroup()
    {
        $Data = [];
        $Buffer = [];

        $Data['DataID'] = '{03995C27-F41C-4E0C-85C9-099084294C3B}';
        $Buffer['Command'] = 'getScenesbyGroup';
        $Buffer['Params'] = '';
        $Data['Buffer'] = $Buffer;
        $Data = json_encode($Data);
        $result = json_decode((string) $this->SendDataToParent($Data), true);
        if (!$result) {
            return [];
        }
        return $result;
    }
}
