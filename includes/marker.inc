<?php

/*
 * This function displays a listing of all the mapsets in the mapset table
 * formatted for an HTML selection box who's value is the id and who's name is
 * displayed.
 *
 * @return nothing - this function echos its results.  
 */
function showMapsetOptions() {

	$res = mysql_query("
		SELECT mapset_uid, mapset_name
		FROM mapset
		ORDER BY mapset_uid
		") or die(mysql_error());

	while($row = mysql_fetch_assoc($res)) {
		echo "\t\t<option value=\"$row[mapset_id]\">$row[mapset_name]</option>\n";
	}

	return TRUE;
}

function generic_sel_options ($table) {
	$uniquekeys=get_ukey($table);
	$ids="";
	for ($i=0; $i<count($uniquekeys); $i++) {
		$ids.=$uniquekeys[$i];
		if ($i==count($uniquekeys)-2) {
			$ids.=",";
		}
	}
	$pid = get_pkey($table);
	if (strlen($ids)>3) {
		$ids.=", $pid";
	}
	else {
		$ids=$pid;
	}
	$result = mysql_query("SELECT $ids FROM $table"); // || mysql_error();
	$rstr="";
	$optcount=0;
	while($row = mysql_fetch_assoc($result)) {
		$sel = implode(" ", array_splice($row, 0, count($uniquekeys)));
		$pidval=$row[$pid];
		$rstr.="\n\t<option value=\"$pidval\">$sel</option>";
		if ($optcount++>30) break; // maximum 30 options
	}
	return $rstr;
}

/**
 * This function will show all the markers measured for a particular line. 
 * Steps:
 * 1. get all tht_base_uids and experiment_uids for this line
 * 2. Get all markers measured in this experiment
 * 
 * @return void - this function outputs its result .
 */
function showMarkerForLine($line_id, $order="marker_name") {
	

$query = 	"SELECT experiments.trial_code, marker_type_name, marker_name, alleles.allele_1,alleles.allele_2
			FROM experiments, tht_base, genotyping_data, alleles, markers, marker_types, experiment_types
			WHERE tht_base.line_record_uid = '$line_id'
			AND experiments.experiment_uid = tht_base.experiment_uid
				AND experiment_types.experiment_type_uid = experiments.experiment_type_uid
				AND  experiment_types.experiment_type_name = 'genotype'
				AND genotyping_data.marker_uid = markers.marker_uid
				AND genotyping_data.tht_base_uid = tht_base.tht_base_uid
				AND alleles.genotyping_data_uid = genotyping_data.genotyping_data_uid
				AND marker_types.marker_type_uid = markers.marker_type_uid
			ORDER BY $order";

	$resource = mysql_query($query) or die(mysql_error());

	if(mysql_num_rows($resource) < 1) {
		echo "No genotype data found<br />";
	}
	else {
		echo displayTable($resource, TRUE);
		echo "<form action='dbtest/exportQueryResult.php' method='post'>
		<input type='submit' value='Export to CSV' />
		<input type='hidden' name='query_string' value='" . base64_encode($query) ."' />
		</form>";
	}
}


/**
 * This function will show the alleles of all lines for a particular marker.
 * 
 * @return void - this function outputs its result .
 */
function showLineForMarker($markername, $order="ORDER BY line_record_name") {

  if(isset($_GET['sortby']) && isset($_GET['sorttype'])) {
    $order = "ORDER BY " . $_GET['sortby'] . " " . $_GET['sorttype'];
  }
  $query = "SELECT marker_type_name, A_allele, B_allele from markers, marker_types where markers.marker_type_uid = marker_types.marker_type_uid and marker_name = '$markername'";
  $resource = mysql_query($query) or die(mysql_error());
  if ($row = mysql_fetch_assoc($resource)) {
    $marker_type = $row['marker_type_name'];
    $allele = $row['A_allele'] . $row['B_allele'];
  } else {
    $marker_type = "SNP";
  }
  
  $query = "SELECT lr.line_record_name, a.allele_1, a.allele_2           
	    FROM markers as m, line_records as lr, alleles as a,                                                      
		 tht_base as tb, genotyping_data as gd
            WHERE m.marker_name = '$markername'
	      AND m.marker_uid = gd.marker_uid               
	      AND a.genotyping_data_uid = gd.genotyping_data_uid                     
	      AND tb.line_record_uid = lr.line_record_uid    
	      AND gd.tht_base_uid = tb.tht_base_uid
            $order";
  $resource = mysql_query($query) or die(mysql_error());

  if(mysql_num_rows($resource) < 1) {
    echo "No genotype data found<br />";
  }
  else {
    if (($marker_type == "GBS") || ($marker_type == "DArT Marker")) {
      $lookup = array(
        'A' => substr($allele,0,1),
        'B' => substr($allele,1,1),
        '-' => '-',
        '' => 'N' 
      );
      echo displayTableConvert($resource, TRUE, $lookup);
    } else {
      echo displayTable($resource, TRUE);
    }
    echo "<form action='dbtest/exportQueryResult.php' method='post'>
		<input type='submit' value='Export to CSV' />
		<input type='hidden' name='query_string' value='" . base64_encode($query) ."' />
		</form>";
  }
}


/**
 * This function determines whether or not a particular line record 
 * has ANY genotype data associated with it
 *
 * @param $lineid - requires the line_record_uid of the line record
 * @return Boolean TRUE if the line record DOES have phenotype data associated with it
 *		   FALSE if the line record DOES NOT.
 */
function lineHasGenotypeData($lineid) {

	$resource = mysql_query("
			SELECT experiments.experiment_uid
			FROM tht_base, experiments, experiment_types
			WHERE tht_base.line_record_uid = '$lineid'
				AND experiments.experiment_uid = tht_base.experiment_uid
				AND experiment_types.experiment_type_uid = experiments.experiment_type_uid
				AND  experiment_types.experiment_type_name = 'genotype'
			") or die(mysql_error());

	return (mysql_num_rows($resource) > 0);
}


?>
