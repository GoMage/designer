<?php
/**
 * @todo implement other font formats (now only "ttf")
 */
class GoMage_ProductDesigner_Helper_Font extends Mage_Core_Helper_Abstract {
    public function hex2rgb( $colour ) {
        if ( $colour[0] == '#' ) {
            $colour = substr( $colour, 1 );
        }
        if ( strlen( $colour ) == 6 ) {
            list( $r, $g, $b ) = array( $colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5] );
        } elseif ( strlen( $colour ) == 3 ) {
            list( $r, $g, $b ) = array( $colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2] );
        } else {
            return false;
        }
        $r = hexdec( $r );
        $g = hexdec( $g );
        $b = hexdec( $b );
        return array( 'red' => $r, 'green' => $g, 'blue' => $b );
    }

    public function imagettftextoutline(&$textContainer, $size, $angle, $x, $y, $color,
                                        $outlineColor, $fontFile, $text, $width) {
        // For every X pixel to the left and the right
        for ($xc = $x - abs($width); $xc <= $x + abs($width); $xc++) {
            // For every Y pixel to the top and the bottom
            for ($yc = $y - abs($width); $yc <= $y + abs($width); $yc++) {
                // Draw the text in the outline color

                imagettftext($textContainer, $size, $angle, $xc, $yc, $outlineColor, $fontFile, $text);
            }
        }

        imagettftext($textContainer, $size, $angle, $x, $y, $color, $fontFile, $text);
    }

    function imagettfstroketext(&$image, $size, $angle, $x, $y, &$textcolor, &$strokecolor, $fontfile, $text, $px) {

        for($c1 = ($x-abs($px)); $c1 <= ($x+abs($px)); $c1++)
            for($c2 = ($y-abs($px)); $c2 <= ($y+abs($px)); $c2++)
                $bg = imagettftext($image, $size, $angle, $c1, $c2, $strokecolor, $fontfile, $text);

        return imagettftext($image, $size, $angle, $x, $y, $textcolor, $fontfile, $text);
    }

    public function getFontFileByFamilyName($fontFamily) {
        /**
         * @var $fontSingleton GoMage_ProductDesigner_Model_Font
         */

        $fontFile = $fontFamily . '.ttf';
        $fontSingleton = Mage::getSingleton('gmpd/font');
        $font = $fontSingleton->loadFontByFile($fontFile);
        return $font;
    }
}