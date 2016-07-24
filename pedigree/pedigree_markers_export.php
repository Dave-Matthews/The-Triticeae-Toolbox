<?php
/**
 * Display Haplotype Data for Selected Lines and Markers
 *
 * @category PHP
 * @package  T3
 *
 */
require 'config.php';
include $config['root_dir'] . 'includes/bootstrap.inc';
$mysqli = connecti();
session_start();

/**
 * Generate the image map
 *
 * @param array $blks the drawing blocks
 * @param string $umapname the name of the image map
 * @return string $mapstr
 */

if (isset($_SESSION['phenotype'])) {
    $phenotype = $_SESSION['phenotype'];
    /* if more than one phenotype selected then only use first one or else script will fail */
    $ntraits=substr_count($_SESSION['phenotype'], ',')+1;
    if ($ntraits > 1) {
        $phenotype_ary = explode(",", $_SESSION['phenotype']);
        $phenotype = $phenotype_ary[0];
        echo "warning - only using one trait<br>\n";
    }
    $r = mysqli_query($mysqli, "select phenotypes_name from phenotypes where phenotype_uid = $phenotype");
    $row  = mysqli_fetch_assoc($r);
    $phenotypename = $row['phenotypes_name'];
}
if (isset($_SESSION['experiments'])) {
    $experiments = $_SESSION['experiments'];
}
if (isset($_SESSION['selected_lines']) && isset($_SESSION['clicked_buttons'])) {
    $slines=$_SESSION['selected_lines'];
    $smkrs_all=$_SESSION['clicked_buttons'];
    $cnt_all=count($smkrs_all);
    $mkrppg=20; // display 20 markers per page
    $page=0; // default page number to 0,
    if (isset($_GET['pagenum'])) $page=$_GET['pagenum'];
    if ($page>floor((count($smkrs_all)-1)/$mkrppg)) $page=floor((count($smkrs_all)-1)/$mkrppg);
    if ($page<0) $page=0;
    $spl_len=$mkrppg;
    if ((count($smkrs_all)-$page*$mkrppg)<$mprppg) $spl_len=count($smkrs_all)-$page*$mkrppg;
    $smkrs=array_splice($smkrs_all, $page*$mkrppg, $spl_len);

    /* If a phenotype is selected, sort the lines by the value of that phenotype. */
 	if (isset($phenotype)) {
 	  $sorted_lines=array(); 
 	  $in_these_experiments = "";
 	  if (isset($experiments)) {
 	    $in_these_experiments = "and tb.experiment_uid in ($experiments)";
 	  }
 	  for ($i=0; $i < count($slines); $i++) {
 	    $lineuid = $slines[$i];
 	    $trtval = -9999;
 	    // Show mean over selected experiments.
 	    $result = mysqli_query($mysqli, "
 			      select avg(value)
 			      from line_records as lr, phenotype_data as pd, tht_base as tb
 			      where lr.line_record_uid = tb.line_record_uid
 			      and tb.tht_base_uid = pd.tht_base_uid
 			      and pd.phenotype_uid = $phenotype
 			      and lr.line_record_uid = $lineuid
                               $in_these_experiments
                               -- and value is not null
 			      ") or die (mysqli_error($mysqli));
 	    if (mysqli_num_rows($result) > 0) {
 	      $row = mysqli_fetch_assoc($result);
 	      $trtval = $row['avg(value)'];
 	    }
	    $sorted_lines[$lineuid] = $trtval;
 	  }
	  // Sort descending.
 	  arsort($sorted_lines, SORT_NUMERIC);
	  $slines = array_keys($sorted_lines);
	}
 
        $name = "THT-HaplotypeData.csv";
        header("Content-type: application/octet-stream");
        header("Content-Disposition: attachment; filename=$name");
        header("Pragma: no-cache");
        header("Expires: 0");


	/* draw the lines */
	$line_names=array();
	for ($i=0; $i<count($slines); $i++) {
		// get the line_name from line_uid
		$lineuid=$slines[$i];
		$linename="";
		$result=mysqli_query($mysqli, "select line_record_name from line_records where line_record_uid=$lineuid") or die("invalid line uid\n");
		while ($row=mysqli_fetch_assoc($result)) {
			$linename=$row['line_record_name'];
		}
		array_push($line_names, $linename);
		$dispname=$linename;
                $marker_value[$i] = $linename;
		if (strlen($linename)>$disp_len) $dispname=substr($linename, 0, $disp_len)."\\";
		$xcoord=$x;
		$ycoord=$y+$cht*($i+1);
	}


	// draw the markers
	$nx=$xcoord;
	$ny=$y;
	for ($i=0; $i<count($smkrs); $i++) {
		$mkrname="";
    	$result=mysqli_query($mysqli, "SELECT marker_name from markers where marker_uid=".$smkrs[$i]);
    	if (mysqli_num_rows($result)>=1) {
			$row = mysqli_fetch_assoc($result);
			$mkrname=$row['marker_name'];
                        echo ",$mkrname";
    	}

	}
        echo "\n";

	// draw the allele values
	$line_mkr=array(); // to avoid duplications
	for ($i=0; $i<count($slines); $i++) {
	  $lineuid=$slines[$i];
	  if (array_key_exists($lineuid, $line_mkr)) continue;
	  else {
	    $line_mkr[$lineuid]=1;
	    for ($j=0; $j<count($smkrs); $j++) {
	      $mkruid=$smkrs[$j];
	      $mkrval="";
	      $result=mysqli_query($mysqli, "
		select marker_name, line_record_name, allele_1, allele_2 
		from markers as A, genotyping_data as B, alleles as C, tht_base as D, line_records as E
		where A.marker_uid=B.marker_uid 
		and B.genotyping_data_uid=C.genotyping_data_uid 
		and B.tht_base_uid=D.tht_base_uid
		and D.line_record_uid=E.line_record_uid 
		and E.line_record_uid=$lineuid and A.marker_uid=$mkruid
		") 
		or die (mysqli_error($mysqli));
	      if (mysqli_num_rows($result)>=1) {
		$row = mysqli_fetch_assoc($result);
		$mkrval=$row['allele_1'].$row['allele_2'];
	      }
	      else {
		// print "$linename no marker information\n";
	      }
	      if (! isset($mkrval) || strlen($mkrval)<1) $mkrval="N";
                //echo "$mkrval\t";
                if (isset($marker_value[$i])) {
                  $marker_value[$i] = $marker_value[$i] . ",$mkrval";
                } else {
                  $marker_value[$i] = $mkrval;
                }
	    }
            $lineuid = $slines[$i];
            $trtval = -9999;
	  }
	}

	// draw the trait values
	if (isset($phenotype)) {
	  $line_trt=array(); // to avoid duplications
	  $in_these_experiments = "";
	  if (isset($experiments)) {
	    $in_these_experiments = "and tb.experiment_uid in ($experiments)";
	  }
	  for ($i=0; $i<count($slines); $i++) {
	    $lineuid=$slines[$i];
	    if (array_key_exists($lineuid, $line_trt)) continue;
	    else {
	      $line_trt[$lineuid]=1;
	      $trtval = "";
	      // Show mean over selected experiments.
	      $result=mysqli_query($mysqli, "
			      select avg(value), count(value)
			      from line_records as lr, phenotype_data as pd, tht_base as tb
			      where lr.line_record_uid = tb.line_record_uid
			      and tb.tht_base_uid = pd.tht_base_uid
			      and pd.phenotype_uid = $phenotype
			      and lr.line_record_uid = $lineuid
                              $in_these_experiments
                              -- and value is not null
			      ") or die (mysqli_error($mysqli));
	      if (mysqli_num_rows($result)>=1) {
		$row = mysqli_fetch_assoc($result);
		$trtval = $row['avg(value)'];
		$cntval = $row['count(value)'];
		$dispval = number_format($trtval,1);
		if ($cntval == 0) { $trtval = ""; }
		$dny=$y+7+$cht*($i);
                //echo "$i $dispval\n";
                $marker_value[$i] = $marker_value[$i] . ",$dispval"; 
              }
	    }
	  }
	}

       for ($i=0; $i<count($slines); $i++) {
          echo "$marker_value[$i]\n";
       }
}
else if(count($_SESSION['selected_lines']) < 1) {
        echo "<p>No lines have been selected</p><ul><li><a href=\"pedigree/line_properties.php\">Select Lines</a></li><li><a href=\"phenotype/compare.php\">Select Lines by Phenotype</a></li></ul>";
}
else if(count($_SESSION['clicked_buttons']) < 1){
       echo "<p>No markers selected - <a href='genotyping/marker_selection.php'>Select Markers</a></p>";
}
?>
