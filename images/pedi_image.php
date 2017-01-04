<?php
include("../includes/bootstrap.inc");
/**
 * This function will draw the pedigree based on the pedigree matrix
 */
function draw_matrix(array $mx, $maxcol, array $leaves, array $mxnm)
{
    $maxlv=count($leaves);
    $cell_width=50;
    $cell_height=50;
    $hlw=1; // half of line width
    $bmg=5; // margin for button
    $imw=$maxcol*$cell_width+100+3*$cell_width;
    $imh=$maxlv*$cell_height+100;
    $x=50;
    $y=50;
    $im=imagecreatetruecolor($imw, $imh);
    $im_black=imagecolorallocate($im, 0x00, 0x00, 0x00);
    $im_white=imagecolorallocate($im, 0xFF, 0xFF, 0xFF);
    $im_blue=imagecolorallocate($im, 0x00, 0x00, 0xFF);
    $im_gray=imagecolorallocate($im, 0xC0, 0xC0, 0xC0);
    $im_orange=imagecolorallocate($im, 0xFF, 0xA5, 0x00);
    $im_green=imagecolorallocate($im, 0x00, 0xFF, 0x33);
    $im_grayblue=imagecolorallocate($im, 0x99, 0xCC, 0xFF);
    $im_graydeepblue=imagecolorallocate($im, 0x33, 0x66, 0xCC);
    $im_bgblue=imagecolorallocate($im, 0xE9, 0xF1, 0xFF);
    $im_whitesmoke=imagecolorallocate($im, 0xF5, 0xF5, 0xF5);
    $im_tomato=imagecolorallocate($im, 0xFF, 0x63, 0x47);
    $im_royalblue=imagecolorallocate($im, 0x41, 0x69, 0xE1);
    $im_salmon=imagecolorallocate($im, 0xFA, 0x80, 0x72);
    $im_seagreen=imagecolorallocate($im, 0x2E, 0x8B, 0x57);
    $im_purple=imagecolorallocate($im, 0xFF, 0x33, 0xFF);
    imagefill($im, 0, 0, $im_white);
    for ($i=$maxcol-1; $i>=0; $i--) {
        for ($j=0; $j<$maxlv; $j++) {
            $xcoor=$x+($maxcol-1-$i)*$cell_width;
            $ycoor=$y+$j*$cell_height;
            if ($mx[$j][$i]==2) { // draw a T
                imagefilledrectangle($im, $xcoor, $ycoor+$cell_height/2-$hlw, $xcoor+$bmg, $ycoor+$cell_height/2+$hlw, $im_black);
                imagefilledrectangle($im, $xcoor+$cell_width-$bmg, $ycoor+$cell_height/2-$hlw, $xcoor+$cell_width, $ycoor+$cell_height/2+$hlw, $im_black);
    		imagefilledrectangle($im, $xcoor+$cell_width/2-$hlw, $ycoor+$cell_height-$bmg, $xcoor+$cell_width/2+$hlw, $ycoor+$cell_height, $im_black);
    		imagefilledrectangle($im, $xcoor+$bmg, $ycoor+$bmg, $xcoor+$cell_width-$bmg, $ycoor+$cell_height-5, $im_green);
    		$bstr=$mxnm[$j][$i];
    		if (strlen($bstr)>6) {
    			$bstr=substr($bstr, 0, 6);
    		}
    		imagestring($im, 3, $xcoor+$bmg+1, $ycoor+$cell_height/2-10, $bstr, $im_black);
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
    		else {
    			// leave blank
    		}
    	}
    }
    $xcoor=$x+($maxcol)*$cell_width;
    for ($k=0; $k<$maxlv; $k++) {
    	$ycoor=$y+$k*$cell_height;
    	imagefilledrectangle($im, $xcoor, $ycoor+$cell_height/2-$hlw, $xcoor+$bmg, $ycoor+$cell_height/2+$hlw, $im_black);
    	imagefilledrectangle($im, $xcoor+$bmg, $ycoor+$bmg, $xcoor+$cell_width-$bmg, $ycoor+$cell_height-$bmg, $im_grayblue);
    	$lstr=$leaves[$k];
    	if (strlen($lstr)>6) $lstr=substr($lstr, 0, 4)."..";
    	imagestring($im, 3, $xcoor+$bmg+1, $ycoor+$cell_height/2-10, $lstr, $im_black);
    }
    Header('Content-type: image/png');
    imagepng($im);
    imagedestroy($im);
}

function draw_sequence($im, $seq, $nx, $ny, $nwidth, $nheight, $bmg, $cmg, $im_tomato, $im_seagreen, $im_royalblue, $im_salmon, $im_gray, $im_black) {
	global $im;
	
	$cls=array('A'=>'im_tomato', 'T'=>'im_royalblue', 'G'=>'im_salmon', 'C'=>'im_seagreen', 'N'=>'im_gray');
    	for ($s=0; $s<strlen($seq); $s++) {
    		$chr=substr($seq, $s, 1);
    		$dnx=$nx+$s*$nwidth;
    		$dny=$ny;
    		imagefilledrectangle($im, $dnx+1, $dny+$cmg, $dnx+$nwidth-1, $dny+$nheight-$cmg, ${$cls[$chr]});
    		imagestring($im, 4, $dnx+$bmg+1, $dny+$nheight/2-10, $chr, $im_black);
    	}
}

function draw_character($im, $fontsize, $chr, $dx1, $dy1, $dx2, $dy2, $im_tomato, $im_seagreen, $im_royalblue, $im_salmon, $im_gray, $im_black) {
	global $im;
	$chrval=$chr;
	$im_purple=imagecolorallocate($im, 0xFF, 0x33, 0xFF);
	$cls=array('AA'=>'im_tomato', 'BB'=>'im_royalblue', 'AB'=>'im_purple', '--'=>'im_gray', 'N'=>'im_gray');
	//		$cls=array('A'=>'im_tomato', 'B'=>'im_royalblue', '-'=>'im_seagreen', 'N'=>'im_gray');
	if (!isset($chr) || $chr=="" || ! array_key_exists($chr, $cls)) {
		$chr='N';
	}
	imagefilledrectangle($im, $dx1, $dy1+3, $dx2, $dy2-3, ${$cls[$chr]});
	imagestring($im, $fontsize, $dx1+1, $dy1+($dy2-$dy1)/2-6, $chrval, $im_black);
}

function draw_cladematrix (array $mx, array $mxnm, $dstr, $cell_size) {
	$maxlv=count($mx);
	$maxcol=count($mx[0])-1;
	$cell_width=50;
	$cell_height=50;
	if ($cell_size>10 && $cell_size<50) {
		$cell_width=$cell_size;
		$cell_height=$cell_size;
	}
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
    $im_gray=imagecolorallocate($im, 0xC0, 0xC0, 0xC0);
    $im_orange=imagecolorallocate($im, 0xFF, 0xA5, 0x00);
    $im_green=imagecolorallocate($im, 0x00, 0xFF, 0x33);
    $im_grayblue=imagecolorallocate($im, 0x99, 0xCC, 0xFF);
    $im_graydeepblue=imagecolorallocate($im, 0x33, 0x66, 0xCC);
    $im_bgblue=imagecolorallocate($im, 0xE9, 0xF1, 0xFF);
    $im_purple=imagecolorallocate($im, 0xFF, 0x33, 0xFF);
    $style = array($im_blue, $im_blue, $im_blue, $im_blue, $im_blue, $im_bgblue, $im_bgblue, $im_bgblue, $im_bgblue, $im_bgblue);
    imagesetstyle($im, $style);
    $im_whitesmoke=imagecolorallocate($im, 0xF5, 0xF5, 0xF5);
    $im_tomato=imagecolorallocate($im, 0xFF, 0x63, 0x47);
    $im_royalblue=imagecolorallocate($im, 0x41, 0x69, 0xE1);
    $im_salmon=imagecolorallocate($im, 0xFA, 0x80, 0x72);
    $im_seagreen=imagecolorallocate($im, 0x2E, 0x8B, 0x57);
    $im_mediumseagreen=imagecolorallocate($im, 0x3C, 0xB3, 0x71);
    $im_skyblue=imagecolorallocate($im, 0x87, 0xCE, 0xEB);
    imagefill($im, 0, 0, $im_whitesmoke);
    for ($i=$maxcol-1; $i>=0; $i--) {
    		for ($j=0; $j<$maxlv; $j++) {
    			$xcoor=$x+($maxcol-1-$i)*$cell_width;
    			$ycoor=$y+$j*$cell_height;
    			// if ($i==0) imageline($im, $x, $ycoor+$cell_height, $x+($maxcol+1)*$cell_width, $ycoor+$cell_height, IMG_COLOR_STYLED);
    			if ($mx[$j][$i]==2) { // draw a T
					imagefilledrectangle($im, $xcoor, $ycoor+$cell_height/2-$hlw, $xcoor+$bmg, $ycoor+$cell_height/2+$hlw, $im_black);
    				imagefilledrectangle($im, $xcoor+$cell_width/2-$hlw, $ycoor, $xcoor+$cell_width/2+$hlw, $ycoor+$bmg, $im_black);
    				imagefilledrectangle($im, $xcoor+$cell_width/2-$hlw, $ycoor+$cell_height-$bmg, $xcoor+$cell_width/2+$hlw, $ycoor+$cell_height, $im_black);
    				imagefilledrectangle($im, $xcoor+$bmg, $ycoor+$bmg, $xcoor+$cell_width-$bmg, $ycoor+$cell_height-$bmg, $im_mediumseagreen);
    				$bstr=$mxnm[$j][$i];
    				$display_string_len=6;
    				if ($cell_size<50) $display_string_len=4;
    				if (strlen($bstr)>$display_string_len) {
    					$bstr=substr($bstr, 0, $display_string_len).".";
    				}
    				imagestring($im, 2, $xcoor+$bmg+1, $ycoor+$cell_height/2-10, $bstr, $im_black);
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
    for ($k=0; $k<$maxlv; $k++) {
    	$lstr=$mx[$k][$maxcol];
    	if (! isset($lstr) || $lstr=='') continue;
    	$ycoor=$y+$k*$cell_height;
    	imagefilledrectangle($im, $xcoor, $ycoor+$cell_height/2-$hlw, $xcoor+$bmg, $ycoor+$cell_height/2+$hlw, $im_black);
    	imagefilledrectangle($im, $xcoor+$bmg, $ycoor+$bmg, $xcoor+$cell_width-$bmg, $ycoor+$cell_height-$bmg, $im_skyblue);
    	$display_string_len=6;
    	if ($cell_size<50) $display_string_len=4;
    	if (strlen($lstr)>$display_string_len) $lstr=substr($lstr, 0, $display_string_len).".";
    	imagestring($im, 2, $xcoor+$bmg+1, $ycoor+$cell_height/2-10, $lstr, $im_black);
        // draw_sequence($im, $dstr, $nx, $ycoor, $nwidth, $nheight, $bmg, $cmg, $im_orange, $im_green, $im_blue, $im_purple, $im_gray, $im_black);
    }	
    // draw the consensus sequence
    
    imagestring($im, 8, $xcoor, $y-30, "Consus", $im_black);
    $nx=$xcoor+2*$cell_width;
    $ny=$y-$cell_height;
    draw_sequence($im, $dstr, $nx, $ny, $nwidth, $nheight, $bmg, $cmg, $im_orange, $im_seagreen, $im_royalblue, $im_tomato, $im_gray, $im_black);
    for ($k=0; $k<$maxlv; $k++)  {
    	$ycoor=$y+$k*$cell_height;
    	draw_sequence($im, $dstr, $nx, $ycoor, $nwidth, $nheight, $bmg, $cmg, $im_orange, $im_seagreen, $im_royalblue, $im_tomato, $im_gray, $im_black);
	}
    	
    // draw sequences
    Header('Content-type: image/png');
    imagepng($im);
}



/**
 * draw pedigree trees in purdy notation, consider the recurrent parent in backcross
 */
function draw_purdy (array $mx, array $mxnm, $dstr, $cell_size) {
    global $mysqli;
	$maxlv=count($mx);
	$maxcol=count($mx[0])-1;
	if ($cell_size>10 && $cell_size<50) {
		$cell_width=$cell_size;
		$cell_height=$cell_size;
	}
	//dem 31dec10, tinkering.
	//Note: These changes also need to be made in the imagemap so the mouseover
	//regions match where the objects are displayed.
	$cell_width=60; // for line names
	$cell_height=11; // for line names
	$hlw=0.5; // half of line width for connector lines
	$bmg=1; // margin for button. The gap between line-name rectangles.  Larger values shrink the rectangle.
	$cmg=2; // vertical margin for allele values.
	$nwidth=14; // width of allele values
//  	$nheight=$cell_height; // height of a character
 	$nheight=11; // height of a character. Used for marker alleles
	// $imw=$maxcol*$cell_width+100+3*$cell_width+$nwidth*strlen($dstr);
	$nummkrs=5; // default marker numbers
	// if (isset($_SESSION['clicked_buttons']) && count($_SESSION['clicked_buttons'])>10) $nummkrs=count($_SESSION['clicked_buttons']);
	$imw=$maxcol*$cell_width+100+3*$cell_width+$nwidth*$nummkrs;
	$imh=$maxlv*$cell_height+100;
//	if ($imw*$imh>450000) ini_set("memory_limit","48M");
	if ($imw*$imh>450000) ini_set("memory_limit","4200M");
//	if ($imw*$imh>600000) ini_set("memory_limit","64M");
	if ($imw*$imh>600000) ini_set("memory_limit","6600M");
	$x=50;
	$y=50;
	global $im;
	$im=imagecreatetruecolor($imw, $imh);
    $im_black=imagecolorallocate($im, 0x00, 0x00, 0x00);
    $im_white=imagecolorallocate($im, 0xFF, 0xFF, 0xFF);
    $im_blue=imagecolorallocate($im, 0x00, 0x00, 0xFF);
    $im_gray=imagecolorallocate($im, 0xC0, 0xC0, 0xC0);
    $im_orange=imagecolorallocate($im, 0xFF, 0xA5, 0x00);
    $im_green=imagecolorallocate($im, 0x00, 0xFF, 0x33);
    $im_grayblue=imagecolorallocate($im, 0x99, 0xCC, 0xFF);
    $im_graydeepblue=imagecolorallocate($im, 0x33, 0x66, 0xCC);
    $im_bgblue=imagecolorallocate($im, 0xE9, 0xF1, 0xFF);
    $style = array($im_blue, $im_blue, $im_blue, $im_blue, $im_blue, $im_bgblue, $im_bgblue, $im_bgblue, $im_bgblue, $im_bgblue);
    imagesetstyle($im, $style);
    $im_whitesmoke=imagecolorallocate($im, 0xF5, 0xF5, 0xF5);
    $im_tomato=imagecolorallocate($im, 0xFF, 0x63, 0x47);
    $im_royalblue=imagecolorallocate($im, 0x41, 0x69, 0xE1);
    $im_salmon=imagecolorallocate($im, 0xFA, 0x80, 0x72);
    $im_seagreen=imagecolorallocate($im, 0x2E, 0x8B, 0x57);
    $im_purple=imagecolorallocate($im, 0xFF, 0x33, 0xFF);
    $im_mediumseagreen=imagecolorallocate($im, 0x3C, 0xB3, 0x71);
    $im_skyblue=imagecolorallocate($im, 0x87, 0xCE, 0xEB);
    $im_darkyellow=imagecolorallocate($im, 0xFF, 0x99, 0x00);
    imagefill($im, 0, 0, $im_whitesmoke);
    $inner_lines=array();
    for ($i=$maxcol-1; $i>=0; $i--) {
    		for ($j=0; $j<$maxlv; $j++) {
    			$xcoor=$x+($maxcol-1-$i)*$cell_width;
    			$ycoor=$y+$j*($cell_height);
    			// if ($i==0) imageline($im, $x, $ycoor+$cell_height, $x+($maxcol+1)*$cell_width, $ycoor+$cell_height, IMG_COLOR_STYLED);
    			if ($mx[$j][$i]==2) { // draw a T
 					imagefilledrectangle($im, $xcoor, $ycoor+$cell_height/2-$hlw, $xcoor+$bmg, $ycoor+$cell_height/2+$hlw, $im_black);
    				imagefilledrectangle($im, $xcoor+$cell_width/2-$hlw, $ycoor, $xcoor+$cell_width/2+$hlw, $ycoor+$bmg, $im_black);
    				imagefilledrectangle($im, $xcoor+$cell_width/2-$hlw, $ycoor+$cell_height-$bmg, $xcoor+$cell_width/2+$hlw, $ycoor+$cell_height, $im_black);
    				imagefilledrectangle($im, $xcoor+$bmg, $ycoor+$bmg, $xcoor+$cell_width-$bmg, $ycoor+$cell_height-$bmg, $im_salmon);
    				$bstr=$mxnm[$j][$i];
    				// if it is number*name then draw the number in the upper left corner of the block 
					if (preg_match('/(\d\*)(.*?)/', $bstr, $mts)) {
						$bstr=$mts[2];
						imagestring($im, 2, $xcoor, $ycoor,$mts[1], $im_black);
					}
					elseif (preg_match('/(.*?)(\*\d)/', $bstr, $mts)) {
						$bstr=$mts[1];
						imagestring($im, 2, $xcoor+$cell_width-$bmg, $ycoor+$cell_height-$bmg, $mts[2], $im_black);
					}
					$inner_lines[$j]=$bstr;
      				$display_string_len=10;
//       				$display_string_len=6;
//      				if ($cell_size<50) $display_string_len=4;
if (strlen($bstr)>$display_string_len) {
     					$bstr=substr($bstr, 0, $display_string_len-1)."\\";
    				}
    				imagestring($im, 2, $xcoor+$bmg+1, $ycoor+$cell_height/2-6, $bstr, $im_black);
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
    // Draw the leaves of the tree.
    for ($k=0; $k<$maxlv; $k++) {
    	$lstr=$mx[$k][$maxcol];
    	if (! isset($lstr) || $lstr=='') continue;
    	$ycoor=$y+$k*$cell_height;
    	imagefilledrectangle($im, $xcoor, $ycoor+$cell_height/2-$hlw, $xcoor+$bmg, $ycoor+$cell_height/2+$hlw, $im_black);
    	imagefilledrectangle($im, $xcoor+$bmg, $ycoor+$bmg, $xcoor+$cell_width-$bmg, $ycoor+$cell_height-$bmg, $im_salmon);
    	if (preg_match('/^(\d+\*)(.*)/', $lstr, $mts)) {
			$lstr=$mts[2];
			imagestring($im, 2, $xcoor, $ycoor,$mts[1], $im_black);
		}
		elseif (preg_match('/(.*?)(\*\d+)/', $lstr, $mts)) {
			$lstr=$mts[1];
			imagestring($im, 2, $xcoor+$cell_width-$bmg*2, $ycoor+$cell_height-$bmg*2, $mts[2], $im_black);
		}
        $display_string_len=10;
//     	$display_string_len=6;
//     	if ($cell_size<50) $display_string_len=4;
    	if (strlen($lstr)>$display_string_len) $lstr=substr($lstr, 0, $display_string_len-1)."\\";
    	imagestring($im, 2, $xcoor+$bmg+1, $ycoor+$cell_height/2-6, $lstr, $im_black);
        // draw_sequence($im, $dstr, $nx, $ycoor, $nwidth, $nheight, $bmg, $cmg, $im_orange, $im_green, $im_blue, $im_purple, $im_gray, $im_black);
    }	
    
    // Draw the markers
    
//     imagestring($im, 8, $xcoor-10, $y-30, "Markers->", $im_black);
    $nx=$xcoor+$cell_width;
    $ny=$y-$cell_height-2;
    // $mkrmap=array();
    $selected_markers=array();
    if (isset($_SESSION['clicked_buttons'])) { 
   	// draw a block for each marker
		// the marker names will be show as title
		$selected_markers_all=$_SESSION['clicked_buttons'];
		$cnt_all=count($selected_markers_all);
		$mkrppg=20; // display 20 markers per page
		$page=0; // default page number to 0, 
		if (isset($_GET['pagenum'])) $page=$_GET['pagenum'];
		$lnm="";
		if (isset($_GET['linename'])) $lnm=$_GET['linename'];
		if ($page>floor(($cnt_all-1)/$mkrppg)) $page=floor((count($smkrs_all)-1)/$mkrppg);
		if ($page<0) $page=0;
		$spl_len=$mkrppg;
		if (($cnt_all-$page*$mkrppg)<$mprppg) $spl_len=$cnt_all-$page*$mkrppg;
		$selected_markers=array_splice($selected_markers_all, $page*$mkrppg, $spl_len);
		
    	/* draw a more sign if there is more markers outside smkrs */
		$leftlink="";
		$leftsign="Start";
		$rightlink="";
		$rightsign="End";
		if ($page>0) {
			$leftlink="pedigree/pedigree_tree.php?pagenum=".($page-1)."&line=$lnm";
			$leftsign=" <<";
		}
		if ($cnt_all>$page*$mkrppg+$spl_len) {
			$rightlink=$_SERVER['PHP_SELF']."?pagenum=".($page+1)."&line=$lnm";
			$rightsign=" >>";
		}
// Omit the "Start" and "End" buttons.
// 		imagefilledrectangle($im, $bmg, $bmg, $cell_width-$bmg, $cell_height-$bmg, $im_darkyellow);
// 		imagestring($im, 2, $bmg+1, $bmg+1, $leftsign, $im_black);
// 		imagefilledrectangle($im, $cell_width+$bmg, $bmg, $cell_width*2-$bmg, $cell_height-$bmg, $im_darkyellow);
// 		imagestring($im, 2, $cell_width+$bmg+1, $bmg+1, $rightsign, $im_black);
// 		imagestring($im, 2, $nx+$bmg+1, $bmg, "Markers from number ".($page*$mkrppg+1)." to ".($page*$mkrppg+1+$spl_len), $im_black);
		imagestring($im, 2, $nx+$bmg+1, $cell_height+$bmg, "Markers from number ".($page*$mkrppg+1)." to ".$cnt_all, $im_black);
    	for ($i=0; $i<count($selected_markers); $i++) {
    		$mkrname="";
    		$result=mysqli_query($mysqli, "SELECT marker_name from markers where marker_uid=".$selected_markers[$i]);
    		if (mysqli_num_rows($result)>=1) {
				$row = mysqli_fetch_assoc($result);
				$mkrname=$row['marker_name'];
    		}
    		else continue;
    			
    		$dnx=$nx+$i*$nwidth;
    		$dny=$ny;
    		imagefilledrectangle($im, $dnx+1, $dny+1, $dnx+$nwidth-1, $dny+$nheight+1, $im_grayblue);
    		$ipadding=2; // compensation for the length of i
			if (strlen($i)>1) $ipadding=-3;
    		imagestring($im, 2, $dnx+$ipadding, $dny+$nheight/2-5, $i+1, $im_black);
    		/*array_push($mkrmap, array('coords'=>array($dnx+1, $dny+$cmg, $dnx+$nwidth-1, $dny+$nheight-$cmg), 
									  'imgclr'=>$im_grayblue, 
									  'text'=>"", 
									  'textsize'=>2,
							          'border'=>1,
							          'border_color'=>'im_khaki3',
							          'link'=>"",
							          'title'=>$mkrname));
			*/
    	}
    }
    $line_mkr=array(); 
    for ($k=0; $k<$maxlv; $k++)  {
    	$ycoor=$y+$k*$cell_height;
    	$linename=$mx[$k][$maxcol];
    	
    	if (array_key_exists($linename, $line_mkr)) continue;
    	if (! isset($linename) || $linename=='') {
				// for ($l=0; $l<$maxcol; $l++) {
					// if (strlen($mxnm[$k][$l])>1) {
						// $linename=$mxnm[$k][$l];
							 
					// }
				// }
				$linename=$inner_lines[$k];
    	}
    	// imagestring($im, 2, $nx-20, $ycoor,$linename, $im_black);	
    	if (! isset($linename) || $linename=='') continue;
    	if (isset($_SESSION['clicked_buttons'])) {
			// $selected_markers=$_SESSION['clicked_buttons'];
			
    		for ($i=0; $i<count($selected_markers); $i++) {
    			$mkruid=$selected_markers[$i];
    			$mkrval="";
                        $sql = "select marker_name, line_record_name, allele_1, allele_2 from markers as A, genotyping_data as B, alleles as C, tht_base as D, line_records as E 
                                                                         where A.marker_uid=B.marker_uid and B.genotyping_data_uid=C.genotyping_data_uid and B.tht_base_uid=D.tht_base_uid 
                                     and D.line_record_uid=E.line_record_uid and line_record_name=\"$linename\" and A.marker_uid=$mkruid";

    			$result=mysqli_query($mysqli, $sql) or die (mysqli_error($mysqli));
    			if (mysqli_num_rows($result)>=1) {
					$row = mysqli_fetch_assoc($result);
					$mkrval=$row['allele_1'].$row['allele_2'];
    			}
    			else {
    				// print "$linename no marker information\n";
    			}
    			
    			$dnx=$nx+$i*$nwidth;
    			$dny=$ycoor;
    			// imagefilledrectangle($im, $dnx+1, $dny+$cmg, $dnx+$nwidth-1, $dny+$nheight-$cmg, $im_grayblue);
    			draw_character($im, 2, $mkrval, $dnx+1, $dny-$cmg, $dnx+$nwidth-1, $dny+$nheight+$cmg, $im_tomato, $im_seagreen, $im_grayblue, $im_salmon, $im_gray, $im_black);
    		}
		}
		$line_mkr[$linename]=1;
    }
    	
    // draw sequences
    Header('Content-type: image/png');
    imagepng($im);
}

$mysqli = connecti();
session_start();
// ini_set("memory_limit","36M");
if (isset($_SESSION['draw_pedigree_matrix'])) {
	$pdarr=$_SESSION['draw_pedigree_matrix'];
	$dstr=$_SESSION['draw_snps'];
	$mx=$pdarr[0];
	$mxnm=$pdarr[1];
	$mxcol=$pdarr[2];
	$mxrow=count($mx);
	$mxarea=$mxcol*$mxrow;
	if ($mxarea>=20 && $mxarea<100) ini_set("memory_limit","3600M");
	elseif ($mxarea>=100 && $mxarea<160) ini_set("memory_limit","5400M");
	elseif ($mxarea>160) ini_set("memory_limit","7600M");
	draw_purdy($mx, $mxnm, $dstr, 40);
	// draw_cladematrix($mx, $mxnm, $dstr, 40);
}
else {
	// print_r($_SESSION);
}

?>
