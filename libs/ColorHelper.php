<?php

declare(strict_types=1);
trait ColorHelper
{
    private function convertRGBToXY($red, $green, $blue)
    {
        // Normalize the values to 1
        $normalizedToOne['red'] = $red / 255;
        $normalizedToOne['green'] = $green / 255;
        $normalizedToOne['blue'] = $blue / 255;
        // Make colors more vivid
        foreach ($normalizedToOne as $key => $normalized) {
            if ($normalized > 0.04045) {
                $color[$key] = pow(($normalized + 0.055) / (1.0 + 0.055), 2.4);
            } else {
                $color[$key] = $normalized / 12.92;
            }
        }
        // Convert to XYZ using the Wide RGB D65 formula
        $xyz['x'] = $color['red'] * 0.664511 + $color['green'] * 0.154324 + $color['blue'] * 0.162028;
        $xyz['y'] = $color['red'] * 0.283881 + $color['green'] * 0.668433 + $color['blue'] * 0.047685;
        $xyz['z'] = $color['red'] * 0.000000 + $color['green'] * 0.072310 + $color['blue'] * 0.986039;
        // Calculate the x/y values
        if (array_sum($xyz) == 0) {
            $x = 0;
            $y = 0;
        } else {
            $x = $xyz['x'] / array_sum($xyz);
            $y = $xyz['y'] / array_sum($xyz);
        }
        return [
            'x'   => $x,
            'y'   => $y,
            'bri' => round($xyz['y'] * 255)
        ];
    }

    private function convertXYToRGB($x, $y, $bri)
    {
        // Calculate XYZ values Convert using the following formulas
        $z = 1.0 - $x - $y;
        $Y = $bri;
        if (($x != 0) || ($y != 0)) {
            $X = ($Y / $y) * $x;
            $Z = ($Y / $y) * $z;
        } else {
            $color['red'] = 0;
            $color['green'] = 0;
            $color['blue'] = 0;
            return $color;
        }

        // Convert to RGB using Wide RGB D65 conversion
        $r = $X * 1.656492 - $Y * 0.354851 - $Z * 0.255038;
        $g = -$X * 0.707196 + $Y * 1.655397 + $Z * 0.036152;
        $b = $X * 0.051713 - $Y * 0.121364 + $Z * 1.011530;

        // Apply reverse gamma correction
        $r = $r <= 0.0031308 ? 12.92 * $r : (1.0 + 0.055) * pow($r, (1.0 / 2.4)) - 0.055;
        $g = $g <= 0.0031308 ? 12.92 * $g : (1.0 + 0.055) * pow($g, (1.0 / 2.4)) - 0.055;
        $b = $b <= 0.0031308 ? 12.92 * $b : (1.0 + 0.055) * pow($b, (1.0 / 2.4)) - 0.055;

        if (($maxValue = max($r, $g, $b)) && $maxValue > 1) {
            $r /= $maxValue;
            $g /= $maxValue;
            $b /= $maxValue;
        }
        $color['red'] = (int) max(0, min(255, $r * 255));
        $color['green'] = (int) max(0, min(255, $g * 255));
        $color['blue'] = (int) max(0, min(255, $b * 255));

        return $color;
    }

    private function decToRGB($Value)
    {
        $rgb['r'] = (($Value >> 16) & 0xFF);
        $rgb['g'] = (($Value >> 8) & 0xFF);
        $rgb['b'] = ($Value & 0xFF);

        return $rgb;
    }

    private function HUEConvertToHSB($HUE)
    {
        $Factor = 65535 / 360; //Max HUE Werte ist 65535
        return round($HUE * $Factor);
    }
}
