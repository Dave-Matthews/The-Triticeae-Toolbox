<?php

/*
 * This function will actually display the phenotype row. 
 *
 * @param $where - sets the conditions of which to select the row(s). This makes it possible to select any number of rows.
 * @param $page - editing allows for updating and has a button that goes to a certain page to update. This variable sets that page
 * 
 * @return nothing - this function outputs to the screen.
 */
function editPhenotypeRow($where, $page, $start = "0")
{
    $ignore = array("phenotype_uid", "datatype");
    // see includes/common.inc
    editGeneral("phenotypes", $where, $page, $ignore, "20", $start);
}

/*
 * This is an example of using the above function. This should display every line (minus the gramene data) in the same format 
 * as the spreadsheet. The problem is the 0 value in the units table. It's killing us unless we put something for 0 in there.
 */
function editAllPhenotypes($page, $start)
{
    editPhenotypeRow("1", $page, $start);
}

/*
 * This will select a range of traits to edit from a given id to a given id. 
 *
 * $minID - the lower boundary of id to get.
 * $maxID - the upper boundary of id to get.
 * $page - this is the page that the update button will travel to. 
 *
 * Note: These values are exclusive, meaning if $minID = 1 and $maxID = 5 then the results returned will be IDs: 2, 3, and 4.
 *
 * @return nothing
 * @see editPhenotypeRow()
 */
function editRangePhenotypes($minID, $maxID, $page)
{
    $where = "phenotype_uid < '$maxID' AND phenotype_uid > '$minID'";
    editPhenotypeRow($where, $page);
}

/*
 * This will select a list of traits to edit from a given array of IDs
 * 
 * If we have a bunch of IDs that we want to edit and there isn't a range
 * of them then we can use this function to display them. 
 *
 * @param $IDRange - an array of IDs to edit. This MUST be an array.
 * @param $page - the page that the update button will travel to
 *
 * @return nothing
 * @see editPhenotypeRow()
 */
function editSelectPhenotypes($IDRange, $page, $start)
{
    if (is_array($IDRange)) {
        $where = "";
		for($i=0; $i<count($IDRange); $i++) {
			if($i != 0) 
				$where .= " OR ";

			$where .= "phenotype_uid = '$IDRange[$i]'";
		}

		editPhenotypeRow($where, $page, $start);
	}

}

/*
 * This function will edit only a single row. 
 * 
 * WARNING: Do not use this function in a for loop if you have multiple IDs to edit
 *	    use the editSelectPhenotypes() function for that.
 *
 * @param $ID - the id of the row to edit
 * @param $page - the page that the update button will travel to
 * 
 * @return nothing
 * @see editPhenotypeRow()
 */
function editSinglePhenotype($ID, $page) {

	$where = "phenotype_uid = '$ID'";
	editPhenotypeRow($where, $page);

}

//////////////
/*
 * This function will actually display the property row.
 *
 * @param $where - sets the conditions of which to select the row(s). This makes it possible to select any number of rows.
 * @param $page - editing allows for updating and has a button that goes to a certain page to update. This variable sets that page
 *
 * @return nothing - this function outputs to the screen.
 */
function editPropertyRow($where, $page, $start = "0")
{
    $ignore = array("properties_uid", "datatype");
    // see includes/common.inc
    editGeneral("properties", $where, $page, $ignore, "20", $start);
}

/*
 * This is an example of using the above function. This should display every line (minus the gramene data) in the same format
 * as the spreadsheet. The problem is the 0 value in the units table. It's killing us unless we put something for 0 in there.
 */
function editAllProperties($page, $start)
{
    editPropertyRow("1", $page, $start);
}

/*
 * This will select a range of traits to edit from a given id to a given id.
 *
 * $minID - the lower boundary of id to get.
 * $maxID - the upper boundary of id to get.
 * $page - this is the page that the update button will travel to.
 *
 * Note: These values are exclusive, meaning if $minID = 1 and $maxID = 5 then the results returned will be IDs: 2, 3, and 4.
 *
 * @return nothing
 * @see editPropertyRow()
 */
function editRangeProperties($minID, $maxID, $page)
{
    $where = "properties_uid < '$maxID' AND properties_uid > '$minID'";
    editPropertyRow($where, $page);
}

/*
 * This will select a list of traits to edit from a given array of IDs
 *
 * If we have a bunch of IDs that we want to edit and there isn't a range
 * of them then we can use this function to display them.
 *
 * @param $IDRange - an array of IDs to edit. This MUST be an array.
 * @param $page - the page that the update button will travel to
 *
 * @return nothing
 * @see editPropertyRow()
 */
function editSelectProperties($IDRange, $page, $start)
{
    if (is_array($IDRange)) {
        $where = "";
		for($i=0; $i<count($IDRange); $i++) {
			if($i != 0)
				$where .= " OR ";

			$where .= "properties_uid = '$IDRange[$i]'";
		}

		editPropertyRow($where, $page, $start);
	}

}

/*
 * This function will edit only a single row.
 *
 * WARNING: Do not use this function in a for loop if you have multiple IDs to edit
 *	    use the editSelectProperties() function for that.
 *
 * @param $ID - the id of the row to edit
 * @param $page - the page that the update button will travel to
 *
 * @return nothing
 * @see editPropertyRow()
 */
function editSingleProperty($ID, $page) {

	$where = "properties_uid = '$ID'";
	editPropertyRow($where, $page);

}

/////////////

/*
 * This function retrieves the name of a category stored in the database. 
 * 
 * @return name - the name of the associated category.
 */
function getCategoryName($id) {
    global $mysqli;
	$resource = mysqli_query($mysqli, "
			SELECT phenotype_category_name
			FROM phenotype_category
			WHERE phenotype_category_uid = '$id'
			") or die(mysqli_error($mysqli));

	$row = mysqli_fetch_assoc($resource);
	return $row['name'];
}


/**
 * This function will show all the phenotype data associated with a particular line. 
 *
 * 	NOTE: This function has to join 7 tables to get its data. Due to our indexes, this function operates
 *	      pretty fast. However, I would not recommend running this thing thousands of times
 *	      inside another function. 
 * 
 * @return void - this function outputs its result.
 */
function showPhenotypeForLine($line_id, $order="phenotype_category_name") {
    global $mysqli;
	// Show only public trials unless signed in as at least Participant.
	if( authenticate( array( USER_TYPE_PARTICIPANT, USER_TYPE_CURATOR, USER_TYPE_ADMINISTRATOR ) ) )
	  $filter = "";
	else
	  $filter = " AND data_public_flag = 1";
	// dem dec13: Change the order of the columns.
	/* $query = "SELECT experiments.trial_code,  */
	/* 			phenotype_category.phenotype_category_name, phenotypes.phenotypes_name,  */
	/* 			phenotype_data.value, units.unit_name */
	/* 		FROM line_records, phenotype_category, phenotypes, phenotype_data, units,  */
	/* 			experiment_types, tht_base, experiments */
	/* 		WHERE line_records.line_record_uid = '$line_id' */
	/* 			AND phenotype_category.phenotype_category_uid = phenotypes.phenotype_category_uid */
	/* 			AND phenotypes.unit_uid = units.unit_uid */
	/* 			AND line_records.line_record_uid = tht_base.line_record_uid */
	/* 			AND phenotype_data.tht_base_uid = tht_base.tht_base_uid */
	/* 			AND experiments.experiment_uid = tht_base.experiment_uid */
	/* 			AND experiment_types.experiment_type_uid = experiments.experiment_type_uid */
	/* 			AND  experiment_types.experiment_type_name = 'phenotype' */
	/* 			AND phenotypes.phenotype_uid = phenotype_data.phenotype_uid */
        /*                         $filter */
	/* 		ORDER BY $order"; */
	$query = "SELECT phenotypes.phenotypes_name as Trait, 
                       experiments.trial_code as Trial, 
		       phenotype_data.value, units.unit_name as Unit,
		       phenotype_category.phenotype_category_name as Category
	       FROM line_records, phenotype_category, phenotypes, phenotype_data, units, 
		       experiment_types, tht_base, experiments
	       WHERE line_records.line_record_uid = '$line_id'
		       AND phenotype_category.phenotype_category_uid = phenotypes.phenotype_category_uid
		       AND phenotypes.unit_uid = units.unit_uid
		       AND line_records.line_record_uid = tht_base.line_record_uid
		       AND phenotype_data.tht_base_uid = tht_base.tht_base_uid
		       AND experiments.experiment_uid = tht_base.experiment_uid
		       AND experiment_types.experiment_type_uid = experiments.experiment_type_uid
		       AND  experiment_types.experiment_type_name = 'phenotype'
		       AND phenotypes.phenotype_uid = phenotype_data.phenotype_uid
		       $filter
	       ORDER BY Trait, Trial";
	$resource = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));
	if(mysqli_num_rows($resource) < 1) 
	  echo "No phenotype data found<br />";
	else {
	  /* echo displayTable($resource, TRUE); */
	  echo displayLinePheno($resource, FALSE, 1);  // includes/common.inc
	  echo "<p><form action='dbtest/exportQueryResult.php' method='post'><input type='submit' value='Export to CSV' /><input type='hidden' name='query_string' value='" . base64_encode($query) ."' /></form>";
  	}
}


/**
 * This functions checks to see if there is phenotype data available for a specific phenotype.
 */
function isPhenotypeData($uid) {
    global $mysqli;
	$query = mysqli_query($mysqli, "SELECT COUNT(*) FROM phenotype_data WHERE phenotype_uid = $uid") or die(mysqli_error($mysqli));
	$r = mysqli_fetch_row($query);
	return $r[0] > 0;
}



/**
 * This function determines whether or not a particular line record 
 * has ANY phenotype data associated with it
 *
 * @param $lineid - requires the line_record_uid of the line record
 * @return Boolean TRUE if the line record DOES have phenotype data associated with it
 *		   FALSE if the line record DOES NOT.
 */
function lineHasPhenotypeData($lineid) {
    global $mysqli;
	$resource = mysqli_query($mysqli, "
			SELECT experiments.experiment_uid
			FROM tht_base, experiments, experiment_types
			WHERE tht_base.line_record_uid = '$lineid'
				AND experiments.experiment_uid = tht_base.experiment_uid
				AND experiment_types.experiment_type_uid = experiments.experiment_type_uid
				AND  experiment_types.experiment_type_name = 'phenotype'
			") or die(mysqli_error($mysqli));

	return (mysqli_num_rows($resource) > 0);
}

/**
 * This function determines what phenotypes are measured in a phenotype experiment.
 *
 * @param $expid - requires the line_record_uid of the line record
 * @return string with phenotypes if the experiment DOES have phenotype data associated with it
 *		   FALSE if the experiment DOES NOT.
 */
function experimentListPhenotypes($expid) {
    global $mysqli;
	$resource = mysqli_query($mysqli, "
			SELECT DISTINCT phenotypes.phenotypes_name
			FROM tht_base, experiments, experiment_types, phenotypes, phenotype_data
			WHERE phenotypes.phenotype_uid = phenotype_data.phenotype_uid
				AND phenotype_data.tht_base_uid = tht_base.tht_base_uid
				AND experiments.experiment_uid = tht_base.experiment_uid
				AND  tht_base.experiment_uid = '$expid'
				AND experiments.experiment_type_uid=experiment_types.experiment_type_uid
				AND  experiment_types.experiment_type_name = 'phenotype'
			GROUP BY phenotypes.phenotype_uid
			ORDER BY phenotypes.phenotypes_name
			") or die(mysqli_error($mysqli));

	if (mysqli_num_rows($resource) > 0){
		while ($row = mysqli_fetch_array($resource)) {
			$phenoarray[] = $row['phenotypes_name'];
		}
		$phenostr = implode(",",$phenoarray);
		return($phenostr);
	}else {
		return(FALSE);
	}	 
}


function calcPhenoStats_mysql($phenoids)
/* This function uses MySQL functions to calculate the mean, standard deviation, max and min
 * for the phenotype uids found in the input $phenoids, the output is an associative array
 * with the fields "phenotype_uid" "meanval" "stdval" "maxval" and "minval". There seem to be problems in the max
 * and min computations, so you may want to use the function getMaxMinPhenotype to find those values
 */
{
    global $mysqli;
	$sql_c ="SELECT p.phenotype_uid, avg(cast(pd.value AS DECIMAL(9,1))) as mean_val, count(pd.value) as sample_size,
					stddev_samp(cast(pd.value AS DECIMAL(9,1))) as std_val, max(cast(pd.value AS DECIMAL(9,3))) as max_val, min(cast(pd.value AS DECIMAL(9,3))) as min_val
				FROM phenotypes as p, phenotype_data as pd
--				WHERE p.datatype IN ('continuous')
				WHERE p.datatype IN ('numeric')
					AND pd.phenotype_uid = p.phenotype_uid
				GROUP BY p.phenotype_uid";
				
				
	$sql_d ="SELECT p.phenotype_uid, avg(cast(pd.value AS signed)) as mean_val, count(pd.value) as sample_size,
					stddev_samp(cast(pd.value AS signed)) as std_val, max(cast(pd.value AS signed)) as max_val, min(cast(pd.value AS signed)) as min_val
				FROM phenotypes as p, phenotype_data as pd
				WHERE p.datatype IN ('discrete')
					AND pd.phenotype_uid = p.phenotype_uid
				GROUP BY p.phenotype_uid";
	$res_c = mysqli_query($mysqli, $sql_c) or die(mysqli_error($mysqli) . "<br>$sql");
	$res_d = mysqli_query($mysqli, $sql_d) or die(mysqli_error($mysqli) . "<br>$sql");
	if ((count($res_c)>0)||(count($res_d)>0)) {
		while ($row = mysqli_fetch_array($res_c)) {
			$output[] =$row;
		}
		while ($row = mysqli_fetch_array($res_d)) {
			$output[] =$row;
		}
		return($output);
	}else{
		return(FALSE);
	}		
}

/**
 * This function gets the maximum or minimum value out of the phenotype_data table corresponding to the given phenotype_uid
 * It takes N/A values into consideration and is more reliable than the MySQL Max and Min functions.
 * Function currently assumes just one $phenoID input per time
 * 
 * @param $maxmin - string of either "max" or "min".  this determines what to return, max or min
 * @param $phenoID - the unique id of the phenotype to search through the phenotype_data table for.
 */
function getMaxMinPhenotype($maxmin, $phenoID) {
    global $mysqli;
	$query = mysqli_query($mysqli, "SELECT max_val,min_val from phenotype_descstat 
					WHERE phenotype_uid = $phenoID") or die(mysqli_error($mysqli));
	if (mysqli_num_rows($query)<1) {
		$max_val = -99999;
		$min_val = -99999;
	}else{
		$row = mysqli_fetch_assoc($query);
		if (is_numeric($row['max_val']))
			$max_val = $row['max_val'];
		else
			$max_val = -99999;
		if (is_numeric($row['min_val']))
			$min_val =$row['min_val'];
		else
			$max_val = -99999;
		 
	}
	
	if ($maxmin == "max")
		return $max_val;
	else
		return $min_val;

}
