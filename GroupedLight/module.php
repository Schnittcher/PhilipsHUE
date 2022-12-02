<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ResourceModule.php';
eval('declare(strict_types=1);namespace PhilipsHUE {?>' . file_get_contents(__DIR__ . '/../libs/vendor/SymconModulHelper/DebugHelper.php') . '}');
eval('declare(strict_types=1);namespace PhilipsHUE {?>' . file_get_contents(__DIR__ . '/../libs/vendor/SymconModulHelper/ColorHelper.php') . '}');
eval('declare(strict_types=1);namespace PhilipsHUE {?>' . file_get_contents(__DIR__ . '/../libs/vendor/SymconModulHelper/VariableProfileHelper.php') . '}');

class HUEGroupedLight extends RessourceModule
{
    use \PhilipsHUE\DebugHelper;
    use \PhilipsHUE\ColorHelper;
    use \PhilipsHUE\VariableProfileHelper;
    const SERVICE = 'grouped_light';

    public static $Variables = [
        ['on', 'State', VARIABLETYPE_BOOLEAN, '~Switch', true, true],
        ['brightness', 'Brightness', VARIABLETYPE_INTEGER, '~Intensity.100', true, true],
        ['color', 'Color', VARIABLETYPE_INTEGER, '~HexColor', true, true],
        ['color_temperature', 'Color Temperature', VARIABLETYPE_INTEGER, 'PhilipsHUE.ColorTemperature', true, true],
        ['transition', 'Transition', VARIABLETYPE_INTEGER, 'PhilipsHUE.Transition', true, true],
        ['scene', 'Scene', VARIABLETYPE_STRING, '', true, true],
    ];

    public function Create()
    {
        parent::Create();

        $this->RegisterPropertyBoolean('stausColorColorTemperature', false);
        $this->RegisterProfileInteger('PhilipsHUE.ColorTemperature', 'Intensity', '', ' mired', 153, 500, 1);
        $this->RegisterProfileInteger('PhilipsHUE.Transition', 'Intensity', '', ' ms', 0, 0, 1);
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();
    }

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'on':
                $duration = $this->GetValue('transition') != false ? $this->GetValue('transition') : 0;
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
            case 'transition':
                $this->SetValue('transition', $Value);
                break;
            case 'color':
                $duration = $this->GetValue('transition') != false ? $this->GetValue('transition') : 0;
                $RGB = $this->HexToRGB($Value);
                $this->SendDebug('RGB', $RGB, 0);
                $XY = $this->RGBToCIE($RGB[0], $RGB[1], $RGB[2]);
                $this->SendDebug('Color', $XY, 0);
                $this->sendData($this->ReadPropertyString('ResourceID'), 'grouped_light', json_encode(['color' => ['xy' => ['x' => $XY['x'], 'y' => $XY['y']]], 'dynamics' => ['duration' => $duration]]));
                if ($this->ReadPropertyBoolean('stausColorColorTemperature')) {
                    $this->SetValue('color', $Value);
                }
                break;
            case 'transition':
                $this->SetValue('transition', $Value);
                break;
            case 'scene':
                $this->sendData($Value, 'scene', json_encode(['recall' => ['action' => 'active']]));
                break;
            }
    }

    public function setColor($color, $OptParams)
    {
        $RGB = $this->HexToRGB($color);
        $this->SendDebug('RGB', $RGB, 0);
        $XY = $this->RGBToCIE($RGB[0], $RGB[1], $RGB[2]);

        $params = ['color' => ['xy' => ['x' => $XY['x'], 'y' => $XY['y']]]];
        $params = array_merge($params, $OptParams);
        $params = json_encode($params);
        $this->SendDebug('setColor :: Params', $params, 0);
        $this->sendData($this->ReadPropertyString('ResourceID'), 'light', $params);
        return;
    }

    public function updateSceneProfileNeu()
    {
        $ProfileName = 'PHUE.Scene.' . $this->ReadPropertyString('ResourceID');
        if (IPS_VariableProfileExists($ProfileName)) {
            IPS_DeleteVariableProfile($ProfileName);
        }
        $scenes = $this->getScenesbyGroup();
        $Associations = [];

        $RoomZoneID = $this->ReadPropertyString('RoomZoneID');

        if (array_key_exists($RoomZoneID, $scenes)) {
            foreach ($scenes[$RoomZoneID] as $key => $scene) {
                $Associations[] = [$scene['id'], $scene['metadata']['name'], '', 0x000000];
            }
            $this->RegisterProfileStringEx($ProfileName, 'Light', '', '', $Associations);

            $variableID = $this->GetIDForIdent('scene');
            if (IPS_VariableExists($variableID)) {
                IPS_SetVariableCustomProfile($variableID, $ProfileName);
            }
        }
    }

    protected function mapResultsToValues(array $Data)
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

        if (array_key_exists('color', $Data)) {
            if (array_key_exists('xy', $Data['color'])) {
                $RGB = $this->CIEToRGB($Data['color']['xy']['x'], $Data['color']['xy']['y'], $this->GetValue('brightness'));
                if (preg_match('/^#[a-f0-9]{6}$/i', strval($RGB))) {
                    $DecColor = hexdec(ltrim($RGB, '#'));
                }
                $this->SetValue('color', $DecColor);
            }
        }
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
        $result = json_decode($this->SendDataToParent($Data), true);
        if (!$result) {
            return [];
        }
        return $result;
    }
}
