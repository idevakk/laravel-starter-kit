<?php

namespace App\Helper;

use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\Pure;

class ColorHelper
{
    /**
     * Convert RGB to OKLab color space.
     */
    public static function rgbToOklab($r, $g, $b): array
    {
        // Normalize RGB values to the range [0, 1]
        $r /= 255;
        $g /= 255;
        $b /= 255;

        // Linearize RGB values
        $r = $r <= 0.04045 ? $r / 12.92 : (($r + 0.055) / 1.055) ** 2.4;
        $g = $g <= 0.04045 ? $g / 12.92 : (($g + 0.055) / 1.055) ** 2.4;
        $b = $b <= 0.04045 ? $b / 12.92 : (($b + 0.055) / 1.055) ** 2.4;

        // Convert to linear light values
        $l = 0.4122214708 * $r + 0.5363325363 * $g + 0.0514459929 * $b;
        $m = 0.2119034982 * $r + 0.6806995451 * $g + 0.1073969566 * $b;
        $s = 0.0883024619 * $r + 0.2817188376 * $g + 0.6299787005 * $b;

        // Apply the OKLab transformation
        $l_ = $l ** (1 / 3);
        $m_ = $m ** (1 / 3);
        $s_ = $s ** (1 / 3);

        $L = 0.2104542553 * $l_ + 0.7936177850 * $m_ - 0.0040720468 * $s_;
        $a = 1.9779984951 * $l_ - 2.4285922050 * $m_ + 0.4505937099 * $s_;
        $b = 0.0259040371 * $l_ + 0.7827717662 * $m_ - 0.8086757660 * $s_;

        return ['L' => $L, 'a' => $a, 'b' => $b];
    }

    /**
     * Convert OKLab to OKLCH color space.
     */
    public static function oklabToOklch($L, $a, $b): array
    {
        $C = sqrt($a * $a + $b * $b); // Chroma
        $h = atan2($b, $a); // Hue in radians

        // Convert hue to degrees and ensure it's in [0, 360)
        $H = rad2deg($h);
        if ($H < 0) {
            $H += 360;
        }

        return ['L' => $L, 'C' => $C, 'H' => $H];
    }

    /**
     * Convert RGB to OKLCH color space.
     */
    public static function rgbToOklch($r, $g, $b)
    {
        $oklab = self::rgbToOklab($r, $g, $b);
        return self::oklabToOklch($oklab['L'], $oklab['a'], $oklab['b']);
    }

    /**
     * Mix RGB color with white or black.
     */
    public static function mixRgb(int $r, int $g, int $b, float $t, bool $toWhite): array
    {
        $target = $toWhite ? 255 : 0;

        $rMix = (int) ($r + ($target - $r) * $t);
        $gMix = (int) ($g + ($target - $g) * $t);
        $bMix = (int) ($b + ($target - $b) * $t);

        return [
            max(0, min(255, $rMix)),
            max(0, min(255, $gMix)),
            max(0, min(255, $bMix)),
        ];
    }

    /**
     * Generate OKLCH shades from RGB string.
     */
    public static function generateOklchFromRGBShades(string $rgbString): ?string
    {
        if (in_array(preg_match('/rgb\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})\)/', $rgbString, $matches), [0, false], true)) {
            return null;
        }

        [$_, $r, $g, $b] = $matches;
        $r = (int)$r;
        $g = (int)$g;
        $b = (int)$b;

        $shades = [
            50 => 0.95,
            100 => 0.9,
            200 => 0.75,
            300 => 0.6,
            400 => 0.3,
            500 => 0.0,
            600 => 0.1,
            700 => 0.25,
            800 => 0.45,
            900 => 0.65,
            950 => 0.85,
        ];

        $result = [];

        foreach ($shades as $key => $t) {
            $toWhite = $key <= 500;
            [$rAdj, $gAdj, $bAdj] = self::mixRgb($r, $g, $b, $t, $toWhite);
            $oklch = self::rgbToOklch($rAdj, $gAdj, $bAdj);

            $result[$key] = sprintf(
                'oklch(%.3f %.3f %.3f)',
                $oklch['L'],
                $oklch['C'],
                $oklch['H']
            );
        }

        return json_encode($result);
    }

    /**
     * Get panel colors from database configuration.
     */
    public static function getPanelColor(string $panelId): array
    {
        try {
            $panelColor = [];
            if ($panelId !== "") {
                $colors = db_config('panel.panel_color_'.$panelId) ?? [];
                $isRGB = db_config('panel.panel_color_isRGB_'.$panelId) ?? false;
                foreach ($colors as $color) {
                    $colorName = $color['panel_color_name_'.$panelId];
                    $colorRGB = $color['panel_color_'.$panelId];
                    $colorOKLCH = json_decode((string) $color['panel_color_shade_'.$panelId], true);
                    $panelColor[$colorName] = $isRGB ? $colorRGB : $colorOKLCH;
                }
            }
            return $panelColor;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return [];
        }

    }
}
