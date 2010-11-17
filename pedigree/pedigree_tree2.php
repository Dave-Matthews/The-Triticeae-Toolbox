<?php
require 'config.php';
/*
 * Logged in page initialization
 */

include($config['root_dir']."includes/bootstrap.inc");
connect();
loginTest();
/* ******************************* */
$row = loadUser($_SESSION['username']);
include($config['root_dir']."theme/cheader.php");

function generate_pedigree_matrix($pstr) {
	$pstr=preg_replace('/\[.*?\]|;/', '',$pstr);
	$lvs=preg_split('/(\/\d*\(.*?\)\/)/',$pstr, -1, PREG_SPLIT_DELIM_CAPTURE); // consider single slash later
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
?>

<p><pre>Currently, this page offers a testing of the pedigree relations
in the database, as well as the image generators for the pedigree.

Try, for example, tmp9, morex, tmp4, and cree.</pre></p>

<?php
if(isset($_REQUEST['line'])) {
	echo "<div class=\"box\" style=\"text-align: left;border:none\"><pre>\n";
	$nvisited=array();
	$pediarr=array($_REQUEST['line']=>getPedigrees_r($_REQUEST['line'],$nvisited));
	// Generate the PediTree style of pedigree annotation
	$treestr=tree2str($pediarr, $_REQUEST['line']);
	// Generate the pedigree annotation with the names of the internal nodes
	$treestr2=tree2str_wi($pediarr, $_REQUEST['line']);
	// print "PediTree Style Text Annotation:\n$treestr\n\nPedigree (internal nodes not displayed)\n\n";
	// print "<img src=\"login/pedi_img.php?pstr=".$treestr."\" alt='Pedigree Tree'>";
	// print "\n".$treestr2."\n";
	// include("../images/pedi_img_wi_imgmap.inc");
	// $lvs=preg_split('/(\/\d*\/|\/)/',$pstr, -1, PREG_SPLIT_DELIM_CAPTURE);
	// $ptree=generate_pedigree_matrix($lvs);
	// $ftm=generate_pedigree_matrix($lvs);
	//	$_SESSION['draw_matrix']=$ftm;
	// $_SESSION['draw_snps']=strtoupper("atgcnncttttcccc");
	// print "<a href=\"login/pedi_img_ftw.php\"> view</a>";
	// print "<img src=\"images/pedi_img_ftw.php\" alt='Pedigree Tree'>";
	$pdarr=generate_pedigree_matrix($treestr2);
	$_SESSION['draw_pedigree_matrix']=$pdarr;
	$_SESSION['draw_snps']=strtoupper("atgcnncttttcccc");
	//print "<a href=\"images/pedi_image.php\">View Image</a>";
	print "<img style=\"border:none\" src=\"images/pedi_image.php\" usemap='#pedimap' alt='Pedigree Tree with Internal Nodes'>";
	include("../images/pedi_image_imgmap.inc");
	$imgmap=get_imgmap($pdarr);


	// print "<img style=\"border:none\" src=\"images/pedi_img_wi.php? usemap='#peditreewi' alt='Pedigree Tree with Internal Nodes'>";

	echo "\n</pre></div>";
	print write_imagemap("pedimap", $imgmap);

}
?>

<form action="login/pedigree_tree2.php" method="post">
<p><strong>Line Name</strong><br />
<input type="text" name="line" value="<?php echo $_REQUEST['line']; ?>" /></p>

<p><input type="submit" value="Get Tree" /></p>
</form>

<p><?php echo $row['name']; ?> you last accessed the system on <?php echo $row['lastaccess']; ?></p>

<?php include($config['root_dir']."theme/footer.php");?>
