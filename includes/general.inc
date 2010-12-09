<?php


/**
 * Functions specific to the general area of the database are stored here
 *
 * Related Tables:
 *	users
 *	user_types
 *	breeding_programs
 *      datasets
 *      experiments
 *	institutions 
 */

/**
 * Determines if object o exists in array arr
 *
 * Object o must have a public weakEquals function
 *
 * @param array the array to search
 * @param object the object in question
 * @return boolean TRUE if the object o is in the array arr
 */
function weakContains(&$arr, &$o)
{
	if (is_null($o) || empty($arr)) return FALSE;
	foreach($arr as &$v){
		if ($o->weakEquals($v)){
			return true;
		}
	}
	return false;
}
 

/**
 * This will update the last access time for a given account
 *
 * @param $account - the unique username for the given account
 * @param $time - the last access time stored in a session variable right after the last login time.
 * @return void
 */
function updateLastAccess($account, $time) {

	mysql_query("UPDATE users SET lastaccess='$time' WHERE users_name = '$account'") 
		   or die(mysql_error());
	return;
}


/**
 * This function is used with the main index page after logging in. It loads all the user info into
 * an array for display on the page. 
 * 
 * @return an array containing all the user related information. The last access time is also 
 *         formatted specifically for an easy to read date. 
 */
function loadUser($user) {

	$q = mysql_query("SELECT * 
			  FROM users 
			  WHERE users_name = '$user'
			")
	     or die(mysql_error());

	$r = mysql_fetch_assoc($q);
	$r['lastaccess'] = date("F j, Y \a\\t g:i:s a", strtotime($r['lastaccess']));
	
	return $r;

}

/**
 * This function is a special function when viewing datasets. It will go ahead and retrieve the breeding
 * programs and associated experiments. It is used in correlation with view.php
 *
 * @param $id - unique id of the dataset.
 */
function show_datasets($id) {

	$q = mysql_query("SELECT dataset_name, breeding_year
			  FROM datasets
			  WHERE datasets_uid = '$id'
			")
	     or die(mysql_error());

	if(mysql_num_rows($q) > 0) {
		$row = mysql_fetch_assoc($q);

		echo "<table class=\"tableclass1\">\n";				
		foreach($row as $k=>$v) {
			echo "\t<tr>\n";
			echo "\t\t<td><strong>". ucfirst(str_replace("_", " ", $k)) . "</strong></td>\n";
			echo "\t\t<td>$v</td>\n";
			echo "\t</tr>\n";
		}
		echo "</table>\n<br />\n";

		$bp = mysql_query("SELECT CAPdata_programs.CAPdata_programs_uid, institutions_name as institution, data_program_name as data_program
			  FROM datasets, CAPdata_programs, institutions
			  WHERE datasets.datasets_uid = '$id'
				AND CAPdata_programs.CAPdata_programs_uid = datasets.CAPdata_programs_uid
				AND institutions.institutions_uid = CAPdata_programs.institutions_uid
			")
	    	      or die(mysql_error());

		echo viewTable($bp, "CAPdata_programs");

		$exp = mysql_query("SELECT experiments.experiment_uid, experiment_type_name as type, trial_code as trial_code, experiment_year as year
			FROM datasets, experiments, datasets_experiments, experiment_types
			WHERE datasets.datasets_uid = '$id'
                                AND datasets_experiments.datasets_uid = datasets.datasets_uid
                                AND experiments.experiment_uid = datasets_experiments.experiment_uid
				AND experiment_types.experiment_type_uid = experiments.experiment_type_uid
			")
	    	      or die(mysql_error());

		echo viewTable($exp, "experiments");
		
	}

}

// Security mechanism
function show_users($id) {
	error(1, "Restricted");
}

/* *
 * This is a special case function used in view.php to view the experimetns table. This will select all the 
 * lines associated with an experiment in addition to the usual experiment data shown. 
 */
function show_experiments($id) {

	echo "<div style=\"float: left;\"><br /><br />";

	//To simply things, just refer to this function.
	show_general("experiments", $id);

	echo "</div>";

	//in addition to the show_general() output, we want to show associated lines
	echo "<div style=\"margin-left: 300px;\">";

	$lines = mysql_query("SELECT DISTINCT line_records.line_record_uid, line_record_name 
			FROM line_records, tht_base
			WHERE tht_base.experiment_uid = $id
				AND tht_base.line_record_uid = line_records.line_record_uid
			ORDER BY line_record_name
		") or die(mysql_error());

	echo viewTable($lines, "line_records") . "\n</div>\n";
}

/**
 * Show markers with view.php.  Example marker 11_11346.
 */
function show_markers($id) {

  show_general("markers", $id);

  echo "<p><h3>Annotations</h3>";
  echo "<table><thead><tr><th>Dataset<th>Entry</tr></thead>";
  $sql = "SELECT mat.name_annotation, ma.value, mat.linkout_string_for_annotation 
          FROM  markers m, marker_annotations ma, marker_annotation_types mat
          WHERE m.marker_uid = $id AND
	    m.marker_uid = ma.marker_uid AND
	    ma.marker_annotation_type_uid = mat.marker_annotation_type_uid 
          ORDER BY mat.name_annotation";
  $res = mysql_query($sql) or die(mysql_error());
  while ($row = mysql_fetch_assoc($res)) {
    $reg_pattern = "XXXX";
    $replace_string = $row['value'];
    $source_string = $row['linkout_string_for_annotation'];
    $linkString = ereg_replace($reg_pattern,$replace_string,$source_string);
    $url = "<a href=\"$linkString\" target=\"_blank\">";
    if ($linkString == "")
      echo "<tr><td>".$row['name_annotation']."<td>".$row['value']."</tr>";
    else 
      echo"<tr><td>".$row['name_annotation']."<td>".$url.$row['value']."</a></tr>";    
  }
  echo "</table>";

  $sql = "select ms.marker_synonym_uid, ms.value, mst.name
	  from markers m, marker_synonyms ms, marker_synonym_types mst
	  where m.marker_uid = $id and
	    ms.marker_uid = m.marker_uid and
	    mst.marker_synonym_type_uid = ms.marker_synonym_type_uid";
  $synonyms = mysql_query($sql) or die(mysql_error());
  if (mysql_num_rows($synonyms) > 0)
    echo viewTable($synonyms, "marker_synonyms");

  echo "<h3>Allele Information</h3>";
  echo "<table><tr><td>";
  echo "<strong>Line Data: </strong>";
  echo "<td><a href=\"genotyping/showlines.php?marker=$id\">Show alleles for all lines</a>";
  echo "</tr></table>";

}

/**
 * Breeding program special case for view.php. This will show all the experiments associated with a CAPdata program. 
*/
function show_breeding_programs($id) {
	
	show_general("CAPdata_programs", $id);

	$datasets = mysql_query("
				SELECT datasets.datasets_uid, datasets.dataset_name,
					experiments.experiment_uid, experiments.trial_code
				FROM  datasets,  datasets_experiments, experiments, CAPdata_programs
				WHERE CAPdata_programs.CAPdata_programs_uid = $id
					AND datasets.CAPdata_programs_uid = CAPdata_programs.CAPdata_programs_uid
					AND datasets_experiments.datasets_uid = datasets.datasets_uid
					AND datasets_experiments.experiment_uid = experiments.experiment_uid
				ORDER BY dataset_name,experiments.trial_code
			") or die(mysql_error());

	echo viewTable($datasets, "datasets");
}

/**
 * Breeding program special case for view.php. This will show all the experiments associated with a CAPdata program. 
*/
function show_CAPdata_programs($id) {
	
	show_general("CAPdata_programs", $id);

	$datasets = mysql_query("SELECT experiments.experiment_uid, experiments.trial_code,experiment_year
				FROM  experiments, CAPdata_programs
				WHERE CAPdata_programs.CAPdata_programs_uid = experiments.CAPdata_programs_uid
					AND experiments.CAPdata_programs_uid = 15
				ORDER BY experiment_year, trial_code
			") or die(mysql_error());

	echo viewTable($datasets, "datasets");
}
/**
 * This function is a general function that will display the contents of a particular row
 * in a particular table. It is used in correlation with view.php as the default action.
 *
 * Note: This function also supports getting the associated names of the foreign keys 
 *       for readabilty with an option of viewing the foreign keys. This being generalized,
 *	 is a mechanism for browsing all the data in the database.
 *
 * @param $table - the table of which to search
 * @param $id - the unique id for that particular row
 * @resource - [OPTIONAL] optional param to hand this function a query resource as to prevent
 * 		querying the database again.
 */
function show_general($tabl, $id, $resource="") {

	$pkey = get_pkey($tabl);

	// Get foreign keys
	$foreign_tables = chk_tbl_references($tabl);
	$foreign_keys = array();

	foreach($foreign_tables as $table) {
		$foreign_keys[$table] = get_pkey($table);
	}

	if($resource == "") {	//if there is no resource, get a resource

		$resource = mysql_query("
				SELECT *
				FROM $tabl
				WHERE $pkey = '$id'
			") or die(mysql_error());

		if(mysql_num_rows($resource) < 1)
			error(1, "No Record Found");
	}

	while($row = mysql_fetch_assoc($resource)) {
		// Display the main table

		echo "<table class=\"tableclass1\">\n";				
		foreach($row as $k=>$v) {

			if(!in_array($k, $foreign_keys)) {
			  // Omit the _uid and created_on columns.
			  if (! strpos($k, "_uid") && ($k != "created_on")) {
			  //if ( ($k != "created_on")) {
				echo "\t<tr>\n";
				echo "\t\t<td><strong>". ucfirst(str_replace("_", " ", $k)) . "</strong></td>\n";
				echo "\t\t<td>$v</td>\n";
				echo "\t</tr>\n";
			}
			}

		}
		echo "</table>";

		// Display the associated tables
		foreach($foreign_tables as $table) {

			//echo "<h3>Associated " . beautifulTableName($table, 1) . "</h3><br />";
			$fpkey = $foreign_keys[$table];

			$qstring = getQueryJoin($table, $tabl, $fpkey, $id);

			//echo $qstring;

			$q = mysql_query($qstring) or die(mysql_error());

			//Display the table
			
			echo viewTable($q, $table);
		}
	}

}

/**
 * This is a helper function for the show_general(). This function will grab the name of
 * a foreign key field for a particular row. It uses the get_pkey and get_unique_name 
 * functions for easy maintainability. This function has been tested and only works
 * IF the foreign key is stored in the calling table. 
 *
 * That's complicated but it does work. Debugging this could possibily give you a headache.
 *
 * @param $foreign_table - the foreign table of which to retrieve from
 * @param $main_table - this the table that references the foreign table.
 * @param $foreign_key - we have to know what the foreign key is, this key is the common key between both tables
 * @param $main_id - this is a unique identifier for the row in the $main table, without this we won't know
 *                   which row to use in the main table to find the appropriate foreign key value to link
 *                   to the specific row in the foreign_table.  Make sense? 
 * @return the SQL query string that will fetch the appropriate results.
 */
function getQueryJoin($foreign_table, $main_table, $foreign_key, $main_id) {

	$pkey = get_pkey($main_table);

	$name = get_unique_name($foreign_table);

	if(!chk_tbl_field($foreign_table, $name)) {	//we're in a many to one situation
		error(1, "Many to Many relationship has been improperly implemented between $main_table and $foreign_table");
	}

	return "SELECT $foreign_table.$foreign_key, $foreign_table.$name
		FROM $main_table, $foreign_table
		WHERE $main_table.$pkey = '$main_id'
			AND $main_table.$foreign_key = $foreign_table.$foreign_key";

}

/**
 * This function is like the display_table() function except this one includes a "view" link to
 * the primary key. Essentially display_table() will simply dump the contents of a mysql resouce.
 * This function is specifically designed to dump the contents of a mysql_query AND deal with 
 * foreign keys.
 *
 * @param $resource - the result of a mysql_query()
 * @param $table - the table in question, this is used to get the primary key for the view link.
 * @return a string containing the HTML table. This does not echo its result.
 */
function viewTable($resource, $table) {

	$pkey = get_pkey($table);

       $ret = "\n<h3>Associated " . beautifulTableName($table, 1) . " (" . mysql_num_rows($resource) . ")</h3><br />";

	if(mysql_num_rows($resource) < 1) {
		return $ret  .=  "<p>No Records Found </p>";
	}

	$first = TRUE;
	$ret .= "\n<table class=\"tableclass1\">\n<thead>\n\t<tr>\n";

	while($row = mysql_fetch_assoc($resource)) {
		if($first) {

			foreach($row as $k=>$v) {
				if($k != $pkey)
					$ret .= "\t\t<td><strong>" . ucwords(str_replace("_", " ", $k)) . "</strong></td>\n";
			}

			$ret.= "\t\t<td><strong>View</strong></td>\n\t</tr>\n</thead><tbody>\n";		
			$first = FALSE;
		}

		$ret .= "\t<tr>\n";
		foreach($row as $k=>$v) {
			if($k != $pkey)
				$ret .= "\t<td>$v</td>\n";
		}

		$ret .= "\t<td><a href=\"view.php?table=$table&uid=$row[$pkey]\">View</a></td>\n\t</tr>\n";
	}

	$ret .= "</tbody>\n</table>\n";

	return $ret;

}



function typecast($old_object, $new_classname) {
    if(class_exists($new_classname)) {
        $old_serialized_object = serialize($old_object);
        $old_object_name_length = strlen(get_class($old_object));
        $subtring_offset = $old_object_name_length + strlen($old_object_name_length) + 6;
        $new_serialized_object  = 'O:' . strlen($new_classname) . ':"' . $new_classname . '":';
        $new_serialized_object .= substr($old_serialized_object, $subtring_offset);
        return unserialize($new_serialized_object);
     } else {
         return false;
     }
}

?>
