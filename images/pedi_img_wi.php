<?php
	include("../includes/bootstrap.inc");
/**
 * This function will draw the pedigree based on the pedigree matrix
 */
function draw_matrix (array $mx, $maxcol, array $leaves, array $mxnm) {
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
    $im_gray=imagecolorallocate($im, 0x66, 0x66, 0x66);
    $im_orange=imagecolorallocate($im, 0xFF, 0x33, 0x00);
    $im_green=imagecolorallocate($im, 0x00, 0xFF, 0x33);
    $im_grayblue=imagecolorallocate($im, 0x99, 0xCC, 0xFF);
    $im_graydeepblue=imagecolorallocate($im, 0x33, 0x66, 0xCC);
    $im_bgblue=imagecolorallocate($im, 0xE9, 0xF1, 0xFF);
    imagefill($im, 0, 0, $im_bgblue);
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
}

function generate_pedigree_matrix(array $lvs) {
	 $maxcol=0;
	 $itnidx=array();
	 $itnnms=array();
	 $leaves=array();
	 foreach ($lvs as $val) {
	 	if (preg_match('/\/(\d+)\((.*?)\)\//',$val, $mts)) {
	 		array_push($itnidx, $mts[1]);
			array_push($itnnms, $mts[2]);
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
	$mxnm=array(); // the correspondent name matrix for $mx
	for ($i=0; $i<count($leaves); $i++) {
	    $mx[$i]=array();
	    $mxnm[$i]=array();
	    if ($i==0) {
	       for ($j=0; $j<$maxcol; $j++) {
	       	   $mx[$i][$j]=1;
	       	   $mxnm[$i][$j]='';
	       }
	    }
	    else {
	    	for($j=0; $j<$maxcol; $j++) {
	       	   	if ($j<$itnidx[$i-1]) {
	       	    	$mx[$i][$j]=1;
	       	    	$mxnm[$i][$j]='';
		   	   	}
		   		else {
		    		$mx[$i][$j]=0;
		    		$mxnm[$i][$j]='';
		   		}		   
	       	}
	       	$mx[$i][$itnidx[$i-1]-1]=1.5;
	       	$mxnm[$i][$itnidx[$i-1]-1]='';
	       	for ($k=$i-1; $k>=0; $k--) {
	       		if ($mx[$k][$itnidx[$i-1]-1]>0) {
		      		$mx[$k][$itnidx[$i-1]-1]++;
		      		$mxnm[$k][$itnidx[$i-1]-1]=$itnnms[$i-1];
		      		break;
		   		}
		   		else {
		      		$mx[$k][$itnidx[$i-1]-1]=0.5;
		      		$mxnm[$k][$itnidx[$i-1]-1]='';
		   		}
			}
	    }
	 }
	 draw_matrix($mx, $maxcol,$leaves, $mxnm);		    
}

$pstr=$_REQUEST['pstr'];
if(isset($pstr) && $pstr!=='') {
	$pstr=preg_replace('/\[.*?\]|;/', '',$pstr);
	$lvs=preg_split('/(\/\d*\(.*?\)\/)/',$pstr, -1, PREG_SPLIT_DELIM_CAPTURE);
	generate_pedigree_matrix($lvs);
}
?>