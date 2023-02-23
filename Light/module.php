<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ResourceModule.php';

eval('declare(strict_types=1);namespace PhilipsHUE {?>' . file_get_contents(__DIR__ . '/../libs/vendor/SymconModulHelper/DebugHelper.php') . '}');
eval('declare(strict_types=1);namespace PhilipsHUE {?>' . file_get_contents(__DIR__ . '/../libs/vendor/SymconModulHelper/ColorHelper.php') . '}');
eval('declare(strict_types=1);namespace PhilipsHUE {?>' . file_get_contents(__DIR__ . '/../libs/vendor/SymconModulHelper/VariableProfileHelper.php') . '}');

class HUELight extends RessourceModule
{
    use \PhilipsHUE\DebugHelper;
    use \PhilipsHUE\ColorHelper;
    use \PhilipsHUE\VariableProfileHelper;

    const SERVICE = 'light';

    public static $Variables = [
        ['on', 'State', VARIABLETYPE_BOOLEAN, '~Switch', true, true],
        ['brightness', 'Brightness', VARIABLETYPE_INTEGER, '~Intensity.100', true, true],
        ['color', 'Color', VARIABLETYPE_INTEGER, '~HexColor', true, true],
        ['color_temperature', 'Color Temperature', VARIABLETYPE_INTEGER, 'PhilipsHUE.ColorTemperature', true, true],
        ['transition', 'Transition', VARIABLETYPE_INTEGER, 'PhilipsHUE.Transition', true, true],

    ];

    public function Create()
    {
        parent::Create();
        $this->RegisterProfileInteger('PhilipsHUE.ColorTemperature', 'Intensity', '', ' mired', 153, 500, 1);
        $this->RegisterProfileInteger('PhilipsHUE.Transition', 'Intensity', '', ' ms', 0, 0, 1);
    }

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case
             'on':
                $this->sendData($this->ReadPropertyString('ResourceID'), 'light', json_encode(['on' => ['on' => $Value]]));
                break;
            case 'brightness':
                $duration = $this->GetValue('transition') != false ? $this->GetValue('transition') : 0;
                if ($Value > 0) {
                    $this->sendData($this->ReadPropertyString('ResourceID'), 'light', json_encode(['on' => ['on' => true], 'dimming' => ['brightness' => $Value], 'dynamics' => ['duration' => $duration]]));
                } else {
                    $this->sendData($this->ReadPropertyString('ResourceID'), 'light', json_encode(['on' => ['on' => false], 'dynamics' => ['duration' => $duration]]));
                }
                break;
            case 'color_temperature':
                $duration = $this->GetValue('transition') != false ? $this->GetValue('transition') : 0;
                $this->sendData($this->ReadPropertyString('ResourceID'), 'light', json_encode(['color_temperature' => ['mirek' => $Value], 'dynamics' => ['duration' => $duration]]));
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
                $this->sendData($this->ReadPropertyString('ResourceID'), 'light', json_encode(['color' => ['xy' => ['x' => $XY['x'], 'y' => $XY['y']]], 'dynamics' => ['duration' => $duration]]));
                break;
            }
    }

    public function setAlert()
    {
        $this->sendData($this->ReadPropertyString('ResourceID'), 'light', json_encode(['alert' => ['action' => 'breathe']]));
    }

    public function setEffect(string $effect, int $duration)
    {
        if ($duration == 0) {
            $this->sendData($this->ReadPropertyString('ResourceID'), 'light', json_encode(['effect' => ['effect' => $effect]]));
        } else {
            $this->sendData($this->ReadPropertyString('ResourceID'), 'light', json_encode(['timed_effects' => ['effect' => $effect], ['duration' => $duration]]));
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
}
