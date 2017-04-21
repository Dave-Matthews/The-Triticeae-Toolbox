<?php

function get_imgmap_cladematrix(array $mx, array $mxnm, $cell_size, $lnm)
{
    global $mysqli;
    $maxlv=count($mx);
    $maxcol=count($mx[0])-1;
    if ($cell_size>10 && $cell_size<50) {
        $cell_width=$cell_size;
        $cell_height=$cell_size;
    }
    $cell_width=60; // for line names
    $cell_height=11; // for line names
    $hlw=0.5; // half of line width
    $bmg=1; // margin for button. The gap between line-name rectangles.  Larger values shrink the rectangle.
	$cmg=2; // vertical margin for allele values.
	$nwidth=14; // width of allele values
// 	$nheight=$cell_height; // height of a character
 	$nheight=11; // height of a character. Used for marker alleles
	$imw=$maxcol*$cell_width+100+3*$cell_width+$nwidth*strlen($dstr);
	$imh=$maxlv*$cell_height+100;
	$x=50;
	$y=50;
	$imgmap=array();
    for ($i=$maxcol-1; $i>=0; $i--) {
    		for ($j=0; $j<$maxlv; $j++) {
    			$xcoor=$x+($maxcol-1-$i)*$cell_width;
    			$ycoor=$y+$j*$cell_height;
    			// if ($i==0) imageline($im, $x, $ycoor+$cell_height, $x+($maxcol+1)*$cell_width, $ycoor+$cell_height, IMG_COLOR_STYLED);
    			if ($mx[$j][$i]==2) { // draw a T
					// for purdy compatibility
					$bstr=$mxnm[$j][$i];
					$bstr=preg_replace('/^\d+\*/','', $bstr);
					$bstr=preg_replace('/\*\d+$/','',$bstr);
					array_push($imgmap, array('shape'=>'rect', 'coords'=>implode(",",array ($xcoor+$bmg, $ycoor+$bmg, $xcoor+$cell_width-$bmg, $ycoor+$cell_height-5)), 'href'=>"pedigree/show_pedigree.php?line=".urlencode($bstr), 'alt'=>$bstr, 'title'=>$bstr));
    				// array_push($imgmap, array('shape'=>'rect', 'coords'=>implode(",",array ($xcoor+$bmg, $ycoor+$bmg, $xcoor+$cell_width-$bmg, $ycoor+$cell_height-5)), 'href'=>"pedigree/show_pedigree.php?line=".urlencode($mxnm[$j][$i]), 'alt'=>$mxnm[$j][$i], 'title'=>$mxnm[$j][$i]));
    				
    			}
    		}
    }
    $xcoor=$x+($maxcol)*$cell_width;
    for ($k=0; $k<$maxlv; $k++) {
    	$lstr=$mx[$k][$maxcol];
    	if (! isset($lstr) || $lstr=='') continue;
    	$ycoor=$y+$k*$cell_height;
    	if (preg_match('/^(\d+\*)(.*)/', $lstr, $mts)) {
			$lstr=$mts[2];
		}
		elseif (preg_match('/(.*?)(\*\d+)$/', $lstr, $mts)) {
			$lstr=$mts[1];
		}
    	array_push($imgmap, array('shape'=>'rect', 'coords'=>implode(",",array ($xcoor+$bmg, $ycoor+$bmg, $xcoor+$cell_width-$bmg, $ycoor+$cell_height-5)), 'href'=>"pedigree/show_pedigree.php?line=".urlencode($lstr), 'alt'=>$lstr, 'title'=>$lstr));
        // draw_sequence($im, $dstr, $nx, $ycoor, $nwidth, $nheight, $bmg, $cmg, $im_orange, $im_green, $im_blue, $im_purple, $im_gray, $im_black);
    }
    	
    // image map for the markers
	$nx=$xcoor+$cell_width;
    $ny=$y-$cell_height-1;
    if (isset($_SESSION['clicked_buttons'])) {
		$selected_markers=$_SESSION['clicked_buttons'];
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
    		array_push($imgmap, array('shape'=>'rect',
    								  'coords'=>implode(",",array($dnx+1, $dny+$cmg, $dnx+$nwidth-1, $dny+$nheight-$cmg)), 
									  'href'=>"view.php?table=markers&name=$mkrname",//$_SERVER['PHP_SELF']."#",
							          'alt'=>"",
							          'title'=>$mkrname));
    	}
    }

    // image map for pages
	$selected_markers_all=$_SESSION['clicked_buttons'];
	$cnt_all=count($selected_markers_all);
	$mkrppg=20; // display 20 markers per page
	$page=0; // default page number to 0, 
	if (isset($_GET['pagenum'])) $page=$_GET['pagenum'];
	if ($page>floor(($cnt_all-1)/$mkrppg)) $page=floor((count($smkrs_all)-1)/$mkrppg);
	if ($page<0) $page=0;
	$leftlink="";
	$rightlink="";
	$imgrand=rand();
	$spl_len=$mkrppg;
	if (($cnt_all-$page*$mkrppg)<$mprppg) $spl_len=$cnt_all-$page*$mkrppg;
	if ($page>0) $leftlink="pedigree/pedigree_tree.php?pagenum=".($page-1)."&line=$lnm";
	if ($cnt_all>$page*$mkrppg+$spl_len) $rightlink="pedigree/pedigree_tree.php?pagenum=".($page+1)."&line=$lnm";
	array_push($imgmap, array('shape'=>'rect',
    						  'coords'=>implode(",",array($bmg, $bmg, $cell_width-$bmg, $cell_height-$bmg)), 
							  'href'=>$leftlink,
							   'alt'=>"",
							   'title'=>"Display the previous 20 markers"));
	array_push($imgmap, array('shape'=>'rect',
    						  'coords'=>implode(",",array($cell_width+$bmg, $bmg, $cell_width*2-$bmg, $cell_height-$bmg)), 
							  'href'=>$rightlink,
							   'alt'=>"",
							   'title'=>"Display the next 20 markers"));
    return $imgmap;
}

function get_imgmap ($pdarr) {
	$mx=$pdarr[0];
	$mxnm=$pdarr[1];
	return get_imgmap_cladematrix($mx, $mxnm, 40, null);
}

function get_imgmap2 ($pdarr, $linename) {
	$mx=$pdarr[0];
	$mxnm=$pdarr[1];
	return get_imgmap_cladematrix($mx, $mxnm, 40, $linename);
}
