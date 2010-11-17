<?php
/**
 * This function will draw the pedigree based on the pedigree matrix
 */
function draw_imgmap (array $mx, $maxcol, array $leaves, array $mxnm) {
	$maxlv=count($leaves);
	$cell_width=50;
	$cell_height=50;
	$hlw=1; // half of line width
	$bmg=5; // margin for button
	$imw=$maxcol*$cell_width+100+3*$cell_width;
	$imh=$maxlv*$cell_height+100;
	$x=50;
	$y=50;
	$imgmap=array();
    for ($i=$maxcol-1; $i>=0; $i--) {
    		for ($j=0; $j<$maxlv; $j++) {
    			$xcoor=$x+($maxcol-1-$i)*$cell_width;
    			$ycoor=$y+$j*$cell_height;
    			if ($mx[$j][$i]==2) { // draw a T
					array_push($imgmap, array('shape'=>'rect', 'coords'=>implode(",",array ($xcoor+$bmg, $ycoor+$bmg, $xcoor+$cell_width-$bmg, $ycoor+$cell_height-5)), 'href'=>$_SERVER['PHP_SELF'], 'alt'=>$mxnm[$j][$i], 'title'=>$mxnm[$j][$i]));
    			}
      		}
    }
    $xcoor=$x+($maxcol)*$cell_width;
    for ($k=0; $k<$maxlv; $k++) {
    	$ycoor=$y+$k*$cell_height;
    	array_push($imgmap, array('shape'=>'rect', 'coords'=>implode(",",array ($xcoor, $ycoor+$bmg, $xcoor+$cell_width, $ycoor+$cell_height-$bmg)), 'href'=>$_SERVER['PHP_SELF'], 'alt'=>$leaves[$k], 'title'=>$leaves[$k]));
    }
	return $imgmap;
}

function generate_imgmap(array $lvs) {
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
	 return draw_imgmap($mx, $maxcol,$leaves, $mxnm);		    
}

function get_imgmap($pstr) {
	$pstr=preg_replace('/\[.*?\]|;/', '',$pstr);
	$lvs=preg_split('/(\/\d*\(.*?\)\/)/',$pstr, -1, PREG_SPLIT_DELIM_CAPTURE);
	return generate_imgmap($lvs);
}
?>