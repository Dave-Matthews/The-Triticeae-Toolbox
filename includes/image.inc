<?php

/**
 * This function generate the grids (a 2x2 matrix) for drawing the pedigretree
 * 
 * @param string $pstr the string based annotation a tree
 * @return array $pdarr same as the output from ftm
 */
function generate_pedigree_matrix($pstr) {
	$pstr=preg_replace('/\[.*?\]|;/', '',$pstr);

	preg_match_all('/\/(\d{3,})/', $pstr, $matches);
	foreach ($matches[1] as $match) {
		$pstr = preg_replace("/$match/", (" ".$match), $pstr);
	}

	$lvs=preg_split('/(\/\d+\({0,1}.*?\){0,1}\/|\/\/|\/)/',$pstr, -1, PREG_SPLIT_DELIM_CAPTURE); // consider single slash later
	$maxcol=0;
	$itnidx=array();
	$itnnms=array();
	$leaves=array();
	foreach ($lvs as $val) {
		if (preg_match('/\/(\d+)\({0,1}(.*?)\){0,1}\//',$val, $mts)) {
	 		array_push($itnidx, $mts[1]);
			array_push($itnnms, $mts[2]);
		    if ($mts[1]>$maxcol) $maxcol=$mts[1];
		 }
		 elseif ($val=='/') {
		    array_push($itnidx, 1);
		    array_push($itnnms, '');
		    if (1>$maxcol) $maxcol=1;
		 }
		 elseif ($val=='//') {
		    array_push($itnidx,2);
		    array_push($itnnms, '');
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
	 return ftm($mx, $leaves, $maxcol, $mxnm);		    
}

/**
 * This function (ftm: further transform the matrix) transform the matrix
 * from generate_pedigree_matrix to a clade format.
 * 
 * @param array $mx the matrix generated from generate_pedigree_matrix, containing the drawing instructions for each grid
 * @param array $leaves the ordered array of the names of the leaves
 * @param int $maxcol the number of columns, the number of layers of internal nodes
 * @param array $mxnm containing the string information for each grid
 * @return array the array of the drawing matrix ($ftm), the array of the strings ($ftmn), and the largest column nuber.
 */
function ftm(array $mx, array $leaves, $maxcol, array $mxnm) {
	$ftm=array();
	$ftmn=array();
	for ($i=0; $i<count($leaves); $i++) {
		array_push($ftm, $mx[$i]);
		array_push($ftmn, $mxnm[$i]);
		$ck=count($ftm)-1;
		array_push($ftm[$ck], $leaves[$i]);
		array_push($ftmn[$ck], $leaves[$i]);
		for ($j=0; $j<$maxcol; $j++) {
			if ($mx[$i][$j]==2) {
				$ftm[$ck][$j]=1.8;
				$ftmn[$ck][$j]='';
				for($k=$j+1; $k<$maxcol; $k++) {
					if ($ftm[$ck][$k]==1.5 ) $ftm[$ck][$k]=0.5;
					if ($ftm[$ck][$k]!=0.5 && $ftm[$ck][$k]!=1.5  )	$ftm[$ck][$k]=0;
				}
				array_push($ftm, $mx[$i]);
				array_push($ftmn, $mxnm[$i]);
				$ck=count($ftm)-1;
				array_push($ftm[$ck], '');
				array_push($ftmn[$ck], '');
				for ($l=0; $l<$j; $l++) {
					if ($mx[$i+1][$l]==0.5 || $mx[$i+1][$l]==1.5) $ftm[$ck][$l]=0.5;
					else $ftm[$ck][$l]=0;
				}
			}
		}
	}
	return array($ftm, $ftmn, $maxcol);
}


/**
 * This function generates the codes for the image map
 * 
 * @param string $mapname the name of the image map
 * @param array $imgmap
 * @return sring $mapstr the image map codes
 */
function write_imagemap ($mapname, $imgmap) {
	$mapstr="<map name=\"$mapname\">"; 
	foreach ($imgmap as $marr) {
		$mapstr.="<area ";
		foreach ($marr as $mk=>$mv) {
			$mapstr.="$mk=\"$mv\"";
		}
		$mapstr.=">\n";
	}
	$mapstr.="</map>";
	return $mapstr;
}

/**
 * This function will regenerate a tree (array based) from the pedigree matrix 
 *
 */

function parse_pedigree_matrix (array $mx, $rtc, $rtr, array $leaves) {
	 $tree=array();
	 // search for the left branch
	 $lflag=0;
	 for ($i=$rtc-1; $i>=0; $i--) {
	     if ($mx[$rtr][$i]==2) {
	     	$tree['0']=parse_pedigree_matrix($mx, $i, $rtr, $leaves);
		$lflag++;
		break;
	     }
	 }
	 if ($lflag==0) $tree['0']=$leaves[$rtr];
	 // search for the right branch
     $rflag=0;
	 $nrow=$rtr+1;
	 for ($j=$rtr+1; $j<count($leaves); $j++) {
	 	if ($j==count($leaves)-1) {
	    	$nrow=$j;
			break;
	    }
	    elseif ($mx[$j][$rtc]!=0.5) {
	     	$nrow=$j;
			break;
	    }
	 }
	 for ($k=$rtc-1; $k>=0; $k--) {
	     if ($mx[$nrow][$k]==2) {
	     	$tree['1']=parse_pedigree_matrix($mx, $k, $nrow, $leaves);
			$rflag++;
			break;
	     }
	 }
	 if ($rflag==0) $tree['1']=$leaves[$nrow];
	 return $tree;
}

/**
 * This function will generate a matrix for parsing the pedigree
 * The columns of the matrix correspond to the height of the internal nodes
 * The rows of the matrix correspond to the leaf nodes
 */
function get_pedigree_matrix(array $lvs) {
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
	 // $ptree=parse_pedigree_matrix($mx, $maxcol-1, 0, $leaves);
	 // return $ptree;	
	 $tmx=get_ftm($mx, $leaves, $maxcol);
	 return $tmx;	 	    
}

function get_ftm(array $mx, array $leaves, $maxcol) {
	$ftm=array();
	for ($i=0; $i<count($leaves); $i++) {
		array_push($ftm, $mx[$i]);
		$ck=count($ftm)-1;
		array_push($ftm[$ck], $leaves[$i]);
		for ($j=0; $j<$maxcol; $j++) {
			if ($mx[$i][$j]==2) {
				$ftm[$ck][$j]=1.8;
				for($k=$j+1; $k<$maxcol; $k++) {
					if ($ftm[$ck][$k]==1.5 ) $ftm[$ck][$k]=0.5;
					if ($ftm[$ck][$k]!=0.5 && $ftm[$ck][$k]!=1.5  )	$ftm[$ck][$k]=0;
				}
				array_push($ftm, $mx[$i]);
				$ck=count($ftm)-1;
				array_push($ftm[$ck], '');
				for ($l=0; $l<$j; $l++) {
					if ($mx[$i+1][$l]==0.5 || $mx[$i+1][$l]==1.5) $ftm[$ck][$l]=0.5;
					else $ftm[$ck][$l]=0;
				}
			}
		}
	}
	return $ftm;
}
?>
