<?php
//Obtain text and border via GET
//Border can be out, in, or flat
$text = $_GET['text'];
$border = $_GET['border'];

$font = getcwd() . '/media/gomage/productdesigner/fonts/c/u/cupertino.ttf'; //(str) "fonts/sasquatchlives.ttf"
$fontsize = 60; //(int) pixels in GD 1, or points in GD 2

//Register box
$box = imagettfbbox ($fontsize, 0, $font, $text);
//Find out the width and height of the text box
$textW = $box[2] - $box[0];
$textH= $box[3]-$box[5];
//Add padding
$paddingx = 10;
$paddingy = 10;
//Set image dimentions
$width = $textW+$paddingx;
$height= $textH+$paddingy;

//Bottom left corner of text
$textx = $paddingx/2;
$texty = $height - $paddingy/2;

//Shadow offset (pixels)
$shadoffx = 1;
$shadoffy = 1;

//Create the image
 $img = imagecreatetruecolor($width,$height);
//Define some colors
 $white = imagecolorallocate($img,255,255,255);
 $black = imagecolorallocate($img,0,0,0);
 $lightgrey = imagecolorallocate($img,200,200,200);
 $grey = imagecolorallocate($img,100,100,100);
//Define Text (fg) and background (bg) colors
 $bgcol = imagecolorallocate($img,192,213,196); //Celadon (light pastel green)
 $fgcol = imagecolorallocate($img,243,104,88); //Peach
// Fill image with background color

//imagealphablending($img, false);
//imagesavealpha($img,true);
//$transparent = imagecolorallocatealpha($img, 255, 255, 255, 127);
imagecolortransparent($img);
// imagefill($img,0,0,$transparent);

//Write Shadow
imagettftext($img, $fontsize, 0, $textx+$shadoffx, $texty+$shadoffy, $grey, $font, $text);

//Write Text
imagettftext($img, $fontsize, 0, $textx, $texty, $fgcol, $font, $text);

////Bordering
//
//   //Embossed border (button-looking)
// if ($border == "out")
// {
//     imageline ($img,0,0,$width,0,$white);imageline ($img,0,0,0,$height,$white);
//     imageline ($img,1,1,$width,1,$lightgrey);imageline ($img,1,1,1,$height-1,$lightgrey);
//     imageline ($img,0,$height-1,$width-1,$height-1,$black);imageline ($img,$width-1,$height-1,$width-1,0,$black);
//     imageline ($img,2,$height-2,$width-2,$height-2,$grey);imageline ($img,$width-2,$height-2,$width-2,2,$grey);
//
// }
//    //Flat border
// if ($border == "flat")
// {
//     imageline ($img,0,0,$width,0,$white);imageline ($img,0,0,0,$height,$white);
//     imageline ($img,1,1,$width,1,$grey);imageline ($img,1,1,1,$height-1,$grey);
//     imageline ($img,0,$height-1,$width-1,$height-1,$white);imageline ($img,$width-1,$height-1,$width-1,0,$white);
//     imageline ($img,2,$height-2,$width-2,$height-2,$grey);imageline ($img,$width-2,$height-2,$width-2,2,$grey);
// }
//
//    //Engraved border (pushed button)
// if ($border == "in")
// {
//     imageline ($img,0,0,$width,0,$black);imageline ($img,0,0,0,$height,$black);
//     imageline ($img,1,1,$width,1,$grey);imageline ($img,1,1,1,$height-1,$grey);
//     imageline ($img,0,$height-1,$width-1,$height-1,$white);imageline ($img,$width-1,$height-1,$width-1,0,$white);
//     imageline ($img,2,$height-2,$width-2,$height-2,$lightgrey);imageline ($img,$width-2,$height-2,$width-2,2,$lightgrey);
// }

// Header info
 header("Content-type: image/png");
//Sends the image
 imagepng($img);
 imagedestroy($img);
?>