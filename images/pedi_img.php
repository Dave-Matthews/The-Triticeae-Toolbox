<?php
	include("../includes/bootstrap.inc");
/**
 * This function will draw the pedigree based on the pedigree matrix
 */
function draw_matrix (array $mx, $maxcol, array $leaves) {
	$maxlv=count($leaves);
	$cell_width=50;
	$cell_height=50;
	$imw=$maxcol*$cell_width+100+3*$cell_width;
	$imh=$maxlv*$cell_height+100;
	$x=50;
	$y=50;
	$im=imagecreatetruecolor($imw, $imh);
    $im_black=imagecolorallocate($im, 0x00, 0x00, 0x00);
    $im_white=imagecolorallocate($im, 0xFF, 0xFF, 0xFF);
    $im_blue=imagecolorallocate($im, 0x00, 0x00, 0xFF);
    $im_gray=imagecolorallocate($im, 0x66, 0x66, 0x66);
    $im_orange=imagecolorallocate($im, 0xFF, 0x33, 0x00);
    $im_green=imagecolorallocate($im, 0x00, 0xFF, 0x33);
    $im_grayblue=imagecolorallocate($im, 0x99, 0xCC, 0xFF);
    imagefill($im, 0, 0, $im_white);
    for ($i=$maxcol-1; $i>=0; $i--) {
    		for ($j=0; $j<$maxlv; $j++) {
    			$xcoor=$x+($maxcol-1-$i)*$cell_width;
    			$ycoor=$y+$j*$cell_height;
    			if ($mx[$j][$i]==2) { // draw a T
    				imagefilledrectangle($im, $xcoor, $ycoor+$cell_height/2-2, $xcoor+$cell_width, $ycoor+$cell_height/2+2, $im_black);
    				imagefilledrectangle($im, $xcoor+$cell_width/2-2, $ycoor+$cell_height/2, $xcoor+$cell_width/2+2, $ycoor+$cell_height, $im_black);
    			}
    			elseif ($mx[$j][$i]==1) { // draw a -
					imagefilledrectangle($im, $xcoor, $ycoor+$cell_height/2-2, $xcoor+$cell_width, $ycoor+$cell_height/2+2, $im_black);
    			}
    			elseif ($mx[$j][$i]==0.5) { // draw a |
					imagefilledrectangle($im, $xcoor+$cell_width/2-2, $ycoor, $xcoor+$cell_width/2+2, $ycoor+$cell_height, $im_black);
    			}
    			elseif ($mx[$j][$i]==1.5) { // draw a L
					imagefilledrectangle($im, $xcoor+$cell_width/2-2, $ycoor, $xcoor+$cell_width/2+2, $ycoor+$cell_height/2, $im_black);
					imagefilledrectangle($im, $xcoor+$cell_width/2-2, $ycoor+$cell_height/2-2, $xcoor+$cell_width, $ycoor+$cell_height/2+2, $im_black);
    			}
    			else {
    				// leave blank
    			}
    		}
    }
    $xcoor=$x+($maxcol)*$cell_width;
    for ($k=0; $k<$maxlv; $k++) {
    	$ycoor=$y+$k*$cell_height;
    	imagefilledrectangle($im, $xcoor, $ycoor+2, $xcoor+4*$cell_width, $ycoor+$cell_height-2, $im_grayblue);
    	imagestring($im, 12, $xcoor+2, $ycoor+10, $leaves[$k], $im_black);
    }
    Header('Content-type: image/png');
    imagepng($im);
}

function generate_pedigree_matrix(array $lvs) {
	 $maxcol=0;
	 $itnidx=array();
	 $leaves=array();
	 foreach ($lvs as $val) {
	 	 if (preg_match('/\/(\d+)\//',$val, $mts)) {
		    array_push($itnidx, $mts[1]);
		    if ($mts[1]>$maxcol) $maxcol=$mts[1];
		 }
		 elseif ($val=='/') {
		    array_push($itnidx, 1);
		    if (1>$maxcol) $maxcol=1;
		 }
		 elseif ($val=='//') {
		    array_push($itnidx,2);
		    if (2>$maxcol) $maxcol=2;
		 }
		 else {
		    array_push($leaves, $val);
		 }
	}
	$mx=array();
	for ($i=0; $i<count($leaves); $i++) {
	    $mx[$i]=array();
	    if ($i==0) {
	       for ($j=0; $j<$maxcol; $j++) {
	       	   $mx[$i][$j]=1;
	       }
	    }
	    else {
	    	for($j=0; $j<$maxcol; $j++) {
	       	   	if ($j<$itnidx[$i-1]) {
	       	    	$mx[$i][$j]=1;
		   	   	}
		   		else {
		    		$mx[$i][$j]=0;
		   		}		   
	       	}
	       	$mx[$i][$itnidx[$i-1]-1]=1.5;
	       	for ($k=$i-1; $k>=0; $k--) {
	       		if ($mx[$k][$itnidx[$i-1]-1]>0) {
		      		$mx[$k][$itnidx[$i-1]-1]++;
		      		break;
		   		}
		   		else {
		      		$mx[$k][$itnidx[$i-1]-1]=0.5;
		   		}
			}
	    }
	 }
	 draw_matrix($mx, $maxcol,$leaves);		    
}

$pstr=$_REQUEST['pstr'];
if(isset($pstr) && $pstr!=='') {
	$pstr=preg_replace('/\[.*?\]|;/', '',$pstr);
	$lvs=preg_split('/(\/\d*\/|\/)/',$pstr, -1, PREG_SPLIT_DELIM_CAPTURE);
	generate_pedigree_matrix($lvs);
}
?>