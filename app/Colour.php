<?php

namespace vendor\WebtreesModules\gvexport;

/**
 * A colour object for operations on colours
 */
class Colour
{
    private $hex;
    private $red;
    private $blue;
    private $green;

    /**
     * Create colour instance using hex code
     *
     * @param $hex_col string the colour hex code for this colour
     */
    function __construct(string $hex_col) {
        $this->hex = $hex_col;
        list($this->red, $this->green, $this->blue) = $this->hexToRgb($hex_col);
    }

    function hexToRgb($hex_col) {
        return sscanf($hex_col, '#%02x%02x%02x');
    }
    function mergeWithColour($colour, $ratio): string
    {
        list($hue1, $saturation1, $value1) = $this->RGBtoHSV($this->red, $this->green, $this->blue);
        list($red, $green, $blue) = $this->hexToRgb($colour);
        list($hue2, $saturation2, $value2) = $this->RGBtoHSV($red, $green, $blue);
        $intermediate_hsv = [];
        $intermediate_hsv['h'] = $hue1 + ($hue2 - $hue1) * $ratio;
        $intermediate_hsv['s'] = $saturation1 + ($saturation2 - $saturation1) * $ratio;
        $intermediate_hsv['v'] = $value1 + ($value2 - $value1) * $ratio;
        $rgb = $this->hsv2rgb($intermediate_hsv['h'], $intermediate_hsv['s'], $intermediate_hsv['v']);

        return $rgb['html'];
    }

    /**
     * Convert RGB to HSV
     * From https://stackoverflow.com/questions/1773698/rgb-to-hsv-in-php
    */
    function RGBtoHSV($R, $G, $B): array    // RGB values:    0-255, 0-255, 0-255
    {                                       // HSV values:    0-360, 0-100, 0-100
        // Convert the RGB byte-values to percentages
        $R = ($R / 255);
        $G = ($G / 255);
        $B = ($B / 255);

        // Calculate a few basic values, the maximum value of R,G,B, the
        //   minimum value, and the difference of the two (chroma).
        $maxRGB = max($R, $G, $B);
        $minRGB = min($R, $G, $B);
        $chroma = $maxRGB - $minRGB;

        // Value (also called Brightness) is the easiest component to calculate,
        //   and is simply the highest value among the R,G,B components.
        // We multiply by 100 to turn the decimal into a readable percent value.
        $computedV = 100 * $maxRGB;

        // Special case if hueless (equal parts RGB make black, white, or grays)
        // Note that Hue is technically undefined when chroma is zero, as
        //   attempting to calculate it would cause division by zero (see
        //   below), so most applications simply substitute a Hue of zero.
        // Saturation will always be zero in this case, see below for details.
        if ($chroma == 0)
            return array(0, 0, $computedV);

        // Saturation is also simple to compute, and is simply the chroma
        //   over the Value (or Brightness)
        // Again, multiplied by 100 to get a percentage.
        $computedS = 100 * ($chroma / $maxRGB);

        // Calculate Hue component
        // Hue is calculated on the "chromacity plane", which is represented
        //   as a 2D hexagon, divided into six 60-degree sectors. We calculate
        //   the bisecting angle as a value 0 <= x < 6, that represents which
        //   portion of which sector the line falls on.
        if ($R == $minRGB)
            $h = 3 - (($G - $B) / $chroma);
        elseif ($B == $minRGB)
            $h = 1 - (($R - $G) / $chroma);
        else // $G == $minRGB
            $h = 5 - (($B - $R) / $chroma);

        // After we have the sector position, we multiply it by the size of
        //   each sector's arc (60 degrees) to obtain the angle in degrees.
        $computedH = 60 * $h;

        return array($computedH, $computedS, $computedV);
    }


    /**
     * Convert HSV values to RGB
     *
     * @param $hue
     * @param $sat
     * @param $val
     * @return int[]
     */
    function hsv2rgb($hue, $sat, $val): array
    {
        $rgb = array(0,0,0);
        //calc rgb for 100% SV, go +1 for BR-range
        for($i=0;$i<4;$i++) {
            if (abs($hue - $i*120)<120) {
                $distance = max(60,abs($hue - $i*120));
                $rgb[$i % 3] = 1 - (($distance-60) / 60);
            }
        }
        //desaturate by increasing lower levels
        $max = max($rgb);
        $factor = 255 * ($val/100);
        for($i=0;$i<3;$i++) {
            //use distance between 0 and max (1) and multiply with value
            $rgb[$i] = round(($rgb[$i] + ($max - $rgb[$i]) * (1 - $sat/100)) * $factor);
        }
        $rgb['html'] = sprintf('#%02X%02X%02X', $rgb[0], $rgb[1], $rgb[2]);
        return $rgb;
    }
}