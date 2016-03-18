<?php

/**
 * This function displays a listing of all the mapsets in the mapset table
 * formatted for an HTML selection box who's value is the id and who's name is
 * displayed.
 *
 * @return nothing - this function echos its results.
 */
function showMapsetOptions()
{
    global $mysqli;
    $sql = "SELECT mapset_uid, mapset_name FROM mapset ORDER BY mapset_uid";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row = mysqli_fetch_assoc($res)) {
        echo "\t\t<option value=\"$row[mapset_id]\">$row[mapset_name]</option>\n";
    }
    return true;
}

function generic_sel_options($table)
{
    global $mysqli;
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
    } else {
        $ids=$pid;
    }
    $result = mysqli_query($mysqli, "SELECT $ids FROM $table"); // || mysqli_error($mysqli);
    $rstr="";
    $optcount=0;
    while ($row = mysqli_fetch_assoc($result)) {
        $sel = implode(" ", array_splice($row, 0, count($uniquekeys)));
        $pidval=$row[$pid];
        $rstr.="\n\t<option value=\"$pidval\">$sel</option>";
        if ($optcount++>30) {
            break; // maximum 30 options
        }
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
function showMarkerForLine($line_id, $order = "marker_name")
{
    global $mysqli;
    $found = 0;

    $query = "SELECT experiments.trial_code, marker_name, alleles
        FROM experiments, allele_cache
        WHERE allele_cache.line_record_uid = '$line_id'
        AND experiments.experiment_uid = allele_cache.experiment_uid
        ORDER BY $order";
    $resource = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));

    if (mysqli_num_rows($resource) > 0) {
        $found = 1;
        echo displayTable($resource, true);
        echo "<form action='dbtest/exportQueryResult.php' method='post'>
        <input type='submit' value='Export to CSV' />
        <input type='hidden' name='query_string' value='" . base64_encode($query) ."' />
        </form>";
    } else {
        $query = "SELECT experiments.trial_code, line_index
        FROM experiments, allele_bymarker_expidx
        where experiments.experiment_uid = allele_bymarker_expidx.experiment_uid";
        $resource = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));
        while ($row = mysqli_fetch_assoc($resource)) {
            $trial_code = $row['trial_code'];
            $lines = json_decode($row['line_index']);
            if (in_array( $line_id, $lines)) {
                $found = 1;
                echo "found in genotype experiment <a href=\"display_genotype.php?trial_code=$trial_code\">$trial_code</a><br><br>\n";
            }
        }
    }
    if ($found == 0) {
        echo "No genotype data found<br />";
    }
}

/**
 * This function will show the alleles of all lines for a particular marker.
 *
 * @return void - this function outputs its result .
 */
function showLineForMarker($marker_uid, $order = "line_record_name")
{
    global $mysqli;
    $query = "SELECT marker_type_name, A_allele, B_allele from markers, marker_types where markers.marker_type_uid = marker_types.marker_type_uid and marker_uid = ?";
    if ($stmt = mysqli_prepare($mysqli, $query)) {
        mysqli_stmt_bind_param($stmt, "i", $marker_uid);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $marker_type, $a_allele, $b_allele);
        if (mysqli_stmt_fetch($stmt)) {
            $allele = $a_allele . $b_allele;
        } else {
            $marker_type = "SNP";
        }
        mysqli_stmt_close($stmt);
    }
    $marker_uid = intval($marker_uid);
    $query = "SELECT experiments.trial_code, line_record_name, alleles
          FROM experiments, allele_cache
          WHERE allele_cache.marker_uid = $marker_uid 
          AND experiments.experiment_uid = allele_cache.experiment_uid
          ORDER BY $order";

    $resource = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));

    if (mysqli_num_rows($resource) < 1) {
        echo "No genotype data found<br />";
    } else {
        if (($marker_type == "GBS") || ($marker_type == "DArT Marker")) {
            $lookup = array(
            'A' => substr($allele, 0, 1),
            'B' => substr($allele, 1, 1),
            '-' => '-',
            '' => 'N'
            );
            echo displayTableConvert($resource, true, $lookup);
        } else {
            echo displayTable($resource, true);
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
 *                 FALSE if the line record DOES NOT.
 */
function lineHasGenotypeData($lineid)
{
    global $mysqli;
    $resource = mysqli_query($mysqli, "
	SELECT experiments.experiment_uid
	FROM tht_base, experiments, experiment_types
	WHERE tht_base.line_record_uid = '$lineid'
		AND experiments.experiment_uid = tht_base.experiment_uid
		AND experiment_types.experiment_type_uid = experiments.experiment_type_uid
		AND  experiment_types.experiment_type_name = 'genotype'
	") or die(mysqli_error($mysqli));

	return (mysqli_num_rows($resource) > 0);
}
