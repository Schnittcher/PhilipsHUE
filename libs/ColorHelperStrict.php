<?php

declare(strict_types=1);

namespace PhilipsHUE;

/**
 * Farbumrechnung zwischen Symcon-Farbwert (Integer) und CIE xy für die Hue API.
 * Strict-taugliche Fassung der benötigten Funktionen aus SymconModulHelper/ColorHelper.
 */
trait ColorHelperStrict
{
    protected function HexToRGB(int $value): array
    {
        $RGB = [];
        $RGB[0] = (($value >> 16) & 0xFF);
        $RGB[1] = (($value >> 8) & 0xFF);
        $RGB[2] = ($value & 0xFF);
        $this->SendDebug('HexToRGB', 'R: ' . $RGB[0] . ' G: ' . $RGB[1] . ' B: ' . $RGB[2], 0);
        return $RGB;
    }

    protected function xyToHEX(float $x, float $y, int|float $bri): string
    {

        // Calculate XYZ values
        $z = 1 - $x - $y;
        $Y = $bri / 254; // Brightness coeff.
        if ($y == 0) {
            $X = 0;
            $Z = 0;
        } else {
            $X = ($Y / $y) * $x;
            $Z = ($Y / $y) * $z;
        }

        // Convert to sRGB D65 (official formula on meethue)
        // formula 2016
        $r = $X * 1.656492 - $Y * 0.354851 - $Z * 0.255038;
        $g = -$X * 0.707196 + $Y * 1.655397 + $Z * 0.036152;
        $b = $X * 0.051713 - $Y * 0.121364 + $Z * 1.011530;

        // Apply reverse gamma correction
        $r = ($r <= 0.0031308 ? 12.92 * $r : (1.055) * pow($r, (1 / 2.4)) - 0.055);
        $g = ($g <= 0.0031308 ? 12.92 * $g : (1.055) * pow($g, (1 / 2.4)) - 0.055);
        $b = ($b <= 0.0031308 ? 12.92 * $b : (1.055) * pow($b, (1 / 2.4)) - 0.055);

        // Calculate final RGB
        $r = ($r < 0 ? 0 : round($r * 255));
        $g = ($g < 0 ? 0 : round($g * 255));
        $b = ($b < 0 ? 0 : round($b * 255));

        $r = ($r > 255 ? 255 : $r);
        $g = ($g > 255 ? 255 : $g);
        $b = ($b > 255 ? 255 : $b);

        $this->SendDebug('RGB', 'R: ' . $r . ' G: ' . $g . ' B: ' . $b, 0);

        // Create a web RGB string (format #xxxxxx)
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    protected function RGBToXy(array $RGB): array
    {
        // Get decimal RGB
        $RGB = sprintf('#%02x%02x%02x', $RGB[0], $RGB[1], $RGB[2]);
        $r = hexdec(substr($RGB, 1, 2));
        $g = hexdec(substr($RGB, 3, 2));
        $b = hexdec(substr($RGB, 5, 2));

        // Calculate rgb as coef
        $r = $r / 255;
        $g = $g / 255;
        $b = $b / 255;

        // Apply gamma correction
        $r = ($r > 0.04045 ? pow(($r + 0.055) / 1.055, 2.4) : ($r / 12.92));
        $g = ($g > 0.04045 ? pow(($g + 0.055) / 1.055, 2.4) : ($g / 12.92));
        $b = ($b > 0.04045 ? pow(($b + 0.055) / 1.055, 2.4) : ($b / 12.92));

        // Convert to XYZ (official formula on meethue)
        // formula 2016
        $X = $r * 0.664511 + $g * 0.154324 + $b * 0.162028;
        $Y = $r * 0.283881 + $g * 0.668433 + $b * 0.047685;
        $Z = $r * 0.000088 + $g * 0.072310 + $b * 0.986039;

        // Calculate xy and bri
        if (($X + $Y + $Z) == 0) {
            $x = 0;
            $y = 0;
        } else { // round to 4 decimal max (=api max size)
            $x = round($X / ($X + $Y + $Z), 4);
            $y = round($Y / ($X + $Y + $Z), 4);
        }
        $bri = (int) round($Y * 254);
        if ($bri > 254) {
            $bri = 254;
        }

        $cie['x'] = $x;
        $cie['y'] = $y;
        $cie['bri'] = $bri;
        return $cie;
    }
}
