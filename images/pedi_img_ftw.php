<?php
	include("../includes/bootstrap.inc");
/**
 * This function will draw the pedigree based on the pedigree matrix
 */
function draw_matrix (array $mx, $dstr) {
	$maxlv=count($mx);
	$maxcol=count($mx[0])-1;
	$cell_width=50;
	$cell_height=50;
	$hlw=1; // half of line width
	$bmg=5; // margin for button
	$cmg=10; // margin for characters
	$nwidth=20; // width of a character
	$nheight=$cell_height; // height of a character
	$imw=$maxcol*$cell_width+100+3*$cell_width+$nwidth*strlen($dstr);
	$imh=$maxlv*$cell_height+100;
	$x=50;
	$y=50;
	global $im;
	$im=imagecreatetruecolor($imw, $imh);
    $im_black=imagecolorallocate($im, 0x00, 0x00, 0x00);
    $im_white=imagecolorallocate($im, 0xFF, 0xFF, 0xFF);
    $im_blue=imagecolorallocate($im, 0x00, 0x00, 0xFF);
    $im_gray=imagecolorallocate($im, 0x66, 0x66, 0x66);
    $im_orange=imagecolorallocate($im, 0xFF, 0x33, 0x00);
    $im_green=imagecolorallocate($im, 0x00, 0xFF, 0x33);
    $im_grayblue=imagecolorallocate($im, 0x99, 0xCC, 0xFF);
    $im_graydeepblue=imagecolorallocate($im, 0x33, 0x66, 0xCC);
    $im_bgblue=imagecolorallocate($im, 0xE9, 0xF1, 0xFF);
    $im_purple=imagecolorallocate($im, 0xFF, 0x33, 0xFF);
    $style = array($im_blue, $im_blue, $im_blue, $im_blue, $im_blue, $im_bgblue, $im_bgblue, $im_bgblue, $im_bgblue, $im_bgblue);
    imagesetstyle($im, $style);
    imagefill($im, 0, 0, $im_bgblue);
    for ($i=$maxcol-1; $i>=0; $i--) {
    		for ($j=0; $j<$maxlv; $j++) {
    			$xcoor=$x+($maxcol-1-$i)*$cell_width;
    			$ycoor=$y+$j*$cell_height;
    			// if ($i==0) imageline($im, $x, $ycoor+$cell_height, $x+($maxcol+1)*$cell_width, $ycoor+$cell_height, IMG_COLOR_STYLED);
    			if ($mx[$j][$i]==2) { // draw a T
					imagefilledrectangle($im, $xcoor, $ycoor+$cell_height/2-$hlw, $xcoor+$bmg, $ycoor+$cell_height/2+$hlw, $im_black);
    				imagefilledrectangle($im, $xcoor+$cell_width/2-$hlw, $ycoor, $xcoor+$cell_width/2+$hlw, $ycoor+$bmg, $im_black);
    				imagefilledrectangle($im, $xcoor+$cell_width/2-$hlw, $ycoor+$cell_height-$bmg, $xcoor+$cell_width/2+$hlw, $ycoor+$cell_height, $im_black);
    				imagefilledrectangle($im, $xcoor+$bmg, $ycoor+$bmg, $xcoor+$cell_width-$bmg, $ycoor+$cell_height-$bmg, $im_green);
    				//$bstr=$mxnm[$j][$i];
    				//if (strlen($bstr)>6) {
    					//$bstr=substr($bstr, 0, 6);
    				//}
    				// imagestring($im, 3, $xcoor+$bmg+1, $ycoor+$cell_height/2-10, $bstr, $im_black);
    			}
    			elseif ($mx[$j][$i]==1) { // draw a -
					imagefilledrectangle($im, $xcoor, $ycoor+$cell_height/2-$hlw, $xcoor+$cell_width, $ycoor+$cell_height/2+$hlw, $im_black);
    			}
    			elseif ($mx[$j][$i]==0.5) { // draw a |
					imagefilledrectangle($im, $xcoor+$cell_width/2-$hlw, $ycoor, $xcoor+$cell_width/2+$hlw, $ycoor+$cell_height, $im_black);
    			}
    			elseif ($mx[$j][$i]==1.5) { // draw a L
					imagefilledrectangle($im, $xcoor+$cell_width/2-$hlw, $ycoor, $xcoor+$cell_width/2+$hlw, $ycoor+$cell_height/2, $im_black);
					imagefilledrectangle($im, $xcoor+$cell_width/2-$hlw, $ycoor+$cell_height/2-$hlw, $xcoor+$cell_width, $ycoor+$cell_height/2+$hlw, $im_black);
    			}
    			elseif ($mx[$j][$i]==1.8) { // draw a r
					imagefilledrectangle($im, $xcoor+$cell_width/2-$hlw, $ycoor+$cell_height/2, $xcoor+$cell_width/2+$hlw, $ycoor+$cell_height, $im_black);
					imagefilledrectangle($im, $xcoor+$cell_width/2-$hlw, $ycoor+$cell_height/2-$hlw, $xcoor+$cell_width, $ycoor+$cell_height/2+$hlw, $im_black);
    			}
    			else {
    				// leave blank
    			}
    		}
    }
    $xcoor=$x+($maxcol)*$cell_width;
    imagestring($im, 8, $xcoor, $y-30, "Consus", $im_black);
    $nx=$xcoor+2*$cell_width;
    $ny=$y-$cell_height;
    // draw the consensus sequence
    draw_sequence($im, $dstr, $nx, $ny, $nwidth, $nheight, $bmg, $cmg, $im_orange, $im_green, $im_blue, $im_purple, $im_gray, $im_black);
    for ($k=0; $k<$maxlv; $k++) {
    	$lstr=$mx[$k][$maxcol];
    	if (! isset($lstr) || $lstr=='') continue;
    	$ycoor=$y+$k*$cell_height;
    	imagefilledrectangle($im, $xcoor, $ycoor+$cell_height/2-$hlw, $xcoor+$bmg, $ycoor+$cell_height/2+$hlw, $im_black);
    	imagefilledrectangle($im, $xcoor+$bmg, $ycoor+$bmg, $xcoor+$cell_width-$bmg, $ycoor+$cell_height-$bmg, $im_grayblue);
    	if (strlen($lstr)>6) $lstr=substr($lstr, 0, 4)."..";
    	imagestring($im, 3, $xcoor+$bmg+1, $ycoor+$cell_height/2-10, $lstr, $im_black);
        draw_sequence($im, $dstr, $nx, $ycoor, $nwidth, $nheight, $bmg, $cmg, $im_orange, $im_green, $im_blue, $im_purple, $im_gray, $im_black);
    }
    // draw sequences

    Header('Content-type: image/png');
    imagepng($im);
}

function draw_sequence($im, $seq, $nx, $ny, $nwidth, $nheight, $bmg, $cmg, $im_orange, $im_green, $im_blue, $im_purple, $im_gray, $im_black) {
	global $im;
	
	$cls=array('A'=>'im_orange', 'T'=>'im_green', 'G'=>'im_blue', 'C'=>'im_purple', 'N'=>'im_gray');
    	for ($s=0; $s<strlen($seq); $s++) {
    		$chr=substr($seq, $s, 1);
    		$dnx=$nx+$s*$nwidth;
    		$dny=$ny;
    		imagefilledrectangle($im, $dnx+1, $dny+$cmg, $dnx+$nwidth-1, $dny+$nheight-$cmg, ${$cls[$chr]});
    		imagestring($im, 4, $dnx+$bmg+1, $dny+$nheight/2-10, $chr, $im_black);
    	}
}

connect();
loginTest();
ini_set("memory_limit","12M");
if (isset($_SESSION['draw_matrix'])) {
	$mx=$_SESSION['draw_matrix'];
	$dstr=$_SESSION['draw_snps'];
	draw_matrix($mx, $dstr);
}
?>