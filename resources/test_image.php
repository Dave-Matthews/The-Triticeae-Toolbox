<?php
$height=200;
$width=200;
$im=ImageCreateTrueColor($width, $height);
$white=ImageColorAllocate($im, 255, 255, 255);
$blue=ImageColorAllocate($im, 0, 0, 64);
ImageFill($im, 0, 0, $blue);
ImageLine($im, 0,0,$width, $height, $white);
ImageString($im, 4,50, 150, 'Sales', $white);
Header('Content-type: image/png');
ImagePng($im);
ImageDestroy($im);
?>