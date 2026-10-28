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

    /**
     * Farbwert (#rrggbb) zu einer Farbe der Bridge in CIE xy.
     * Die Helligkeit gehört nicht in den Farbwert, sie steht in der eigenen Variable. Die Farbe wird deshalb normalisiert:
     * Der stärkste Kanal ist voll ausgesteuert und der Farbton bleibt erhalten (kein Abschneiden einzelner Kanäle).
     * Hinweis: Die Konsole von Symcon färbt xy anders ein (abgeschnitten, deshalb gelblicher). Genau ist color_xy.
     */
    protected function xyToHEX(float $x, float $y): string
    {
        if ($y <= 0) {
            return '#000000';
        }

        // XYZ bei voller Helligkeit (Y = 1)
        $X = $x / $y;
        $Y = 1.0;
        $Z = (1 - $x - $y) / $y;

        // Linear nach sRGB D65 (official formula on meethue, formula 2016)
        $r = $X * 1.656492 - $Y * 0.354851 - $Z * 0.255038;
        $g = -$X * 0.707196 + $Y * 1.655397 + $Z * 0.036152;
        $b = $X * 0.051713 - $Y * 0.121364 + $Z * 1.011530;

        // Farben außerhalb von sRGB haben negative Anteile, sie werden auf 0 gesetzt
        $r = max(0.0, $r);
        $g = max(0.0, $g);
        $b = max(0.0, $b);

        // Normalisieren: der stärkste Kanal wird 1
        $max = max($r, $g, $b);
        if ($max > 0) {
            $r /= $max;
            $g /= $max;
            $b /= $max;
        }

        // Apply reverse gamma correction
        $r = ($r <= 0.0031308 ? 12.92 * $r : (1.055) * pow($r, (1 / 2.4)) - 0.055);
        $g = ($g <= 0.0031308 ? 12.92 * $g : (1.055) * pow($g, (1 / 2.4)) - 0.055);
        $b = ($b <= 0.0031308 ? 12.92 * $b : (1.055) * pow($b, (1 / 2.4)) - 0.055);

        $r = (int) min(255, max(0, round($r * 255)));
        $g = (int) min(255, max(0, round($g * 255)));
        $b = (int) min(255, max(0, round($b * 255)));

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
    /**
     * Liest eine Farbe in xy aus dem Wert einer Variable mit der Darstellung Farbe (Kodierung xy): {"x":0.5041,"y":0.4144}.
     * Liefert null, wenn der Wert keine gültige Farbart ist.
     */
    protected function parseXy(mixed $Value): ?array
    {
        $Xy = is_string($Value) ? json_decode($Value, true) : null;
        if (!is_array($Xy) || !isset($Xy['x'], $Xy['y']) || !is_numeric($Xy['x']) || !is_numeric($Xy['y'])) {
            return null;
        }
        $x = (float) $Xy['x'];
        $y = (float) $Xy['y'];
        if ($x < 0 || $x > 1 || $y < 0 || $y > 1) {
            return null;
        }
        return ['x' => $x, 'y' => $y];
    }
    /**
     * Pflegt die Variablen "color" und "color_xy" aus den Daten der Bridge. Die Bridge meldet nur, was sich ändert, die Farbe
     * also nur mit xy. Der Farbwert hängt nicht von der Helligkeit ab, die in der eigenen Variable steht.
     */
    protected function updateColor(array $Data): void
    {
        $Xy = $Data['color']['xy'] ?? null;
        if (!is_array($Xy) || !isset($Xy['x'], $Xy['y'])) {
            return;
        }

        // Die Farbe in xy, wie sie die Bridge meldet, ohne Umrechnung
        $this->SetValue('color_xy', json_encode(['x' => (float) $Xy['x'], 'y' => (float) $Xy['y']]));

        $RGB = $this->xyToHEX((float) $Xy['x'], (float) $Xy['y']);
        if (preg_match('/^#[a-f0-9]{6}$/i', strval($RGB))) {
            $this->SetValue('color', hexdec(ltrim($RGB, '#')));
        }
    }
}
