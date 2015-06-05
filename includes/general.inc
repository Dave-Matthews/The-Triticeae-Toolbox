<?php

/**
 * Functions specific to the general area of the database are stored here
 *
 * Related Tables:
 * users
 * user_types
 * breeding_programs
 * datasets
 * experiments
 * institutions
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
    if (is_null($o) || empty($arr)) {
        return false;
    }
    foreach ($arr as &$v) {
        if ($o->weakEquals($v)) {
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
function updateLastAccess($account, $time)
{
    global $mysqli;
    mysqli_query($mysqli, "UPDATE users SET lastaccess='$time' WHERE users_name = '$account'")
    or die(mysqli_error($mysqli));
    return;
}


/**
 * This function is used with the main index page after logging in. It loads all the user info into
 * an array for display on the page.
 *
 * @return an array containing all the user related information. The last access time is also
 *         formatted specifically for an easy to read date.
 * DEM mar2015: Use the $_SESSION values only, don't check the database again.
 *              We don't want table 'users' being examined anonymously.
 */
function loadUser($user)
{
    global $mysqli;
    // Move the values from $_SESSION to a mysqli resource.
    if ($user == $_SESSION['username']) {
        if (isset($_SESSION['userid'])) {
            $userid = $_SESSION['userid'];
            $pass = $_SESSION['password'];
            $name = $_SESSION['name'];
            $usertype = $_SESSION['usertype'];
            $sql = "select '$user' as users_name, '$name' as name, $userid as users_uid, '$pass' as password, $usertype as user_types_uid";
            $q = mysqli_query($mysqli, $sql) or die("Error in load user");
            $r = mysqli_fetch_assoc($q);
            return $r;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

/**
 * This function is a special function when viewing line_records.
 * It is used by view.php.
 *
 * @param $id - unique id of the dataset.
 */
// function show_line_records($id) {
//   Defined in includes/pedigree.inc
// }

/**
 * This function is a special function when viewing datasets. It will go ahead and retrieve the breeding
 * programs and associated experiments. It is used in correlation with view.php
 *
 * @param $id - unique id of the dataset.
 */
function show_datasets($id)
{
    global $mysqli;
    $q = mysqli_query($mysqli, "SELECT dataset_name, breeding_year
	  FROM datasets
	  WHERE datasets_uid = '$id'
	")
    or die("Error in show datasets");

    if (mysqli_num_rows($q) > 0) {
        $row = mysqli_fetch_assoc($q);

        echo "<table class=\"tableclass1\">\n";
        foreach ($row as $k => $v) {
            echo "\t<tr>\n";
            echo "\t\t<td><strong>". ucfirst(str_replace("_", " ", $k)) . "</strong></td>\n";
            echo "\t\t<td>$v</td>\n";
            echo "\t</tr>\n";
        }
        echo "</table>\n<br />\n";

        $bp = mysqli_query($mysqli, "SELECT CAPdata_programs.CAPdata_programs_uid, institutions_name as institution, data_program_name as data_program
		  FROM datasets, CAPdata_programs, institutions
		  WHERE datasets.datasets_uid = '$id'
			AND CAPdata_programs.CAPdata_programs_uid = datasets.CAPdata_programs_uid
			AND institutions.institutions_uid = CAPdata_programs.institutions_uid
		")
         or die("Error in show datasets");

        echo viewTable($bp, "CAPdata_programs");

	$exp = mysqli_query($mysqli, "SELECT experiments.experiment_uid, experiment_type_name as type, trial_code as trial_code, experiment_year as year
		FROM datasets, experiments, datasets_experiments, experiment_types
		WHERE datasets.datasets_uid = '$id'
                               AND datasets_experiments.datasets_uid = datasets.datasets_uid
                               AND experiments.experiment_uid = datasets_experiments.experiment_uid
			AND experiment_types.experiment_type_uid = experiments.experiment_type_uid
		")
    	      or die("Error in show datasets");

	echo viewTable($exp, "experiments");
		
	}

}

// Security mechanism
function show_users($id) {
// dem 6sep13: We decided to unrestrict.  Just omit the users.pass field.
	/* error(1, "Restricted"); */
  echo "<table>";
  $sql = "select name, email, institution, lastaccess from users where users_uid = $id";
  $res = mysqli_query($mysqli, $sql) or die("Error in show users\n");
  while ($r = mysqli_fetch_row($res)) {
    echo "<tr><td>Name<td>$r[0]";
    echo "<tr><td>Email<td>$r[1]";
    echo "<tr><td>Institution<td>$r[2]";
    echo "<tr><td>Last login<td>$r[3]";
  }
  echo "</table>";
}



/* *
 * This is a special case function used in view.php to view the experimetns table. This will select all the 
 * lines associated with an experiment in addition to the usual experiment data shown. 
 */
function show_experiments($id) {
    global $mysqli;
    echo "<div style=\"float: left;\"><br /><br />";

    //To simply things, just refer to this function.
    show_general("experiments", $id);

    echo "</div>";

    //in addition to the show_general() output, we want to show associated lines
    echo "<div style=\"margin-left: 300px;\">";

    $lines = mysqli_query($mysqli, "SELECT DISTINCT line_records.line_record_uid, line_record_name 
		FROM line_records, tht_base
		WHERE tht_base.experiment_uid = $id
		AND tht_base.line_record_uid = line_records.line_record_uid
		ORDER BY line_record_name
	") or die("Error in show experiments");

    echo viewTable($lines, "line_records") . "\n</div>\n";
}

/**
 * Show markers with view.php.  Example marker 11_11346.
 */
function show_markers($id) {
    global $mysqli;
    show_general("markers", $id);
    echo "<p><h3>Annotations</h3>";
    $sql = "SELECT mat.name_annotation, ma.value, ma.description, mat.linkout_string_for_annotation, mat.comments 
          FROM  markers m, marker_annotations ma, marker_annotation_types mat
          WHERE m.marker_uid = $id AND
	    m.marker_uid = ma.marker_uid AND
	    ma.marker_annotation_type_uid = mat.marker_annotation_type_uid 
          ORDER BY mat.name_annotation";
  $res = mysqli_query($mysqli, $sql) or die("Error in show markers");
  if (mysqli_num_rows($res) == 0) 
    echo "None";
  else {
    echo "<table><thead><tr><th>Dataset<th>Entry<th>Entry Description<th>Dataset Description</tr></thead>";
    while ($row = mysqli_fetch_assoc($res)) {
      $reg_pattern = "XXXX";
      $replace_string = $row['value'];
      $source_string = $row['linkout_string_for_annotation'];
      $linkString = ereg_replace($reg_pattern,$replace_string,$source_string);
      $url = "<a href=\"$linkString\" target=\"_blank\">";
      $url2 = "<form method='GET' target='_blank' action='$linkString'><input type='submit' value='View in GBrowse'></form>";

      if ($linkString == "") {
           echo "<tr><td>".$row['name_annotation']."<td>".$row['value']."<td>".$row['description']."<td>".$row['comments']."</tr>";
      } elseif ($row['name_annotation'] == "IWGSC CSS") {
          echo"<tr><td>".$row['name_annotation']."<td>".$url2."<td>".$row['value']." ".$row['description']."<td>".$row['comments']."</a></tr>";
      } else {
	  echo"<tr><td>".$row['name_annotation']."<td>".$url.$row['value']."<td>".$row['description']."<td>".$row['comments']."</a></tr>";
      }
    }
    echo "</table>";
  }

  // Marker Synonyms
  $sql = "select ms.marker_synonym_uid, ms.value, mst.name
	  from markers m, marker_synonyms ms, marker_synonym_types mst
	  where m.marker_uid = $id and
	    ms.marker_uid = m.marker_uid and
	    mst.marker_synonym_type_uid = ms.marker_synonym_type_uid";
  $synonyms = mysqli_query($mysqli, $sql) or die("Error in show markers");
  if (mysqli_num_rows($synonyms) > 0)
    echo viewTable($synonyms, "marker_synonyms");

  echo "<h3>Allele Information</h3>";
  echo "<table><tr><td>";
  echo "<strong>Line Data: </strong>";
  echo "<td><a href=\"".$config['base_url']."genotyping/showlines.php?marker=$id\">Show alleles for all lines</a>";
  echo "</tr></table>";

  // marker_selection.php wants the name of a map the marker is on.  Probably doesn't matter which.
  $sql = "select map_name from map, markers_in_maps mm where mm.marker_uid = $id and mm.map_uid = map.map_uid";
  $r = mysqli_query($mysqli, $sql);
  $row = mysqli_fetch_row($r);
  $mapname = $row[0];
  // Don't do it if the marker isn't mapped.
  if (!empty($mapname)) {
    echo "<h3>Map locations</h3>";
    // Display in table
    $sql = "select map_name, chromosome, start_position, end_position
	from map, markers_in_maps
	where marker_uid = $id and markers_in_maps.map_uid = map.map_uid
	";
    $r = mysqli_query($mysqli, $sql) or die("Error in show markers");
    if (mysqli_num_rows($r) > 0) {
      echo "<table><thead><tr><th>Map<th>Chromosome<th>Start<th>End";
      while ($row = mysqli_fetch_assoc($r)) 
	echo "<tr><td>$row[map_name]<td>$row[chromosome]<td>$row[start_position]<td>$row[end_position]";
      echo "</table>";
    }
    // View in GBrowse
    $sql = "select marker_name from markers where marker_uid = $id";
    $r = mysqli_query($mysqli, $sql);
    $row = mysqli_fetch_row($r);
    $markername = $row[0];
    echo "<form method='GET' action=http://".$_SERVER['HTTP_HOST']."/cgi-bin/gbrowse/tht>";
    echo "<input type='hidden' name='name' value='$markername'>";
    echo "<input type='submit' value='View in GBrowse'></p>";
    echo "</form>";
    // add to Selection List
    echo "<h3>Selection List</h3>";
    echo "<form method='POST' action=".$config['base_url']."genotyping/marker_selection.php>";
    echo "<input type='hidden' name='selMkrs[]' value='$id'>";
    echo "<input type='hidden' name='mapname' value='$mapname'>";
    echo "<input type='submit' value='Add this marker' style='color:blue' /></p>";
    echo "</form>";
  }
}

/**
 * Breeding program special case for view.php. This will show all the experiments associated with a CAPdata program. 
*/
function show_breeding_programs($id) {
    global $mysqli;
    show_general("CAPdata_programs", $id);

    $datasets = mysqli_query($mysqli, "
				SELECT datasets.datasets_uid, datasets.dataset_name,
					experiments.experiment_uid, experiments.trial_code
				FROM  datasets,  datasets_experiments, experiments, CAPdata_programs
				WHERE CAPdata_programs.CAPdata_programs_uid = $id
					AND datasets.CAPdata_programs_uid = CAPdata_programs.CAPdata_programs_uid
					AND datasets_experiments.datasets_uid = datasets.datasets_uid
					AND datasets_experiments.experiment_uid = experiments.experiment_uid
				ORDER BY dataset_name,experiments.trial_code
			") or die("Error in show breeding programs");

    echo viewTable($datasets, "datasets");
}

/**
 * Breeding program special case for view.php. This will show all the experiments associated with a CAPdata program. 
*/
function show_CAPdata_programs($id) {
    global $mysqli;
    show_general("CAPdata_programs", $id);

    $datasets = mysqli_query($mysqli, "SELECT experiments.experiment_uid, experiments.trial_code,experiment_year
				FROM  experiments, CAPdata_programs
				WHERE CAPdata_programs.CAPdata_programs_uid = experiments.CAPdata_programs_uid
					AND experiments.CAPdata_programs_uid = 15
				ORDER BY experiment_year, trial_code
			") or die("Error in show CAP data programs");

    echo viewTable($datasets, "datasets");
}

/**
 * Traits special case for view.php. This will show all the experiments associated with a trait ("phenotype").
*/
function show_phenotypes($id) {
  show_general("phenotypes", $id);

  // Get selected experiments and verify that the user is authorized to see the experiment.
  $sql="select distinct e.trial_code, e.experiment_year, e.experiment_uid
		FROM tht_base as tb, phenotype_data as pd, experiments as e
		WHERE pd.phenotype_uid='$id'
			AND tb.tht_base_uid = pd.tht_base_uid
			AND e.experiment_uid = tb.experiment_uid";
  if (!authenticate(array(USER_TYPE_PARTICIPANT,
			  USER_TYPE_CURATOR,
			  USER_TYPE_ADMINISTRATOR)))
    $sql .= " and e.data_public_flag > 0";
  $sql .= " order by e.experiment_year, e.trial_code asc";
  $res=mysql_query($sql) or die("Error in show phenotypes");
  if (mysql_num_rows($res) > 0) {
    ?>
<h3>Available Datasets for this Phenotype</h3>
<p>
  <table cellpadding="0" cellspacing="0">
    <tr>
      <th>Year</th>
      <th>Trial Name</th>
      <th>Traits</th>
    </tr>
<?php
     while($row_expuid=mysql_fetch_array($res)) {
       $trial_code=$row_expuid['trial_code'];
       $year=$row_expuid['experiment_year'];
       $expuid=$row_expuid['experiment_uid'];
       $traits=experimentListPhenotypes($expuid);
       echo( "<tr> <td>$year</td> <td><a href='display_phenotype.php?trial_code=$trial_code'>$trial_code</a> </td> <td>$traits</td> </tr>");
     }
    echo "</tbody></table>";
  }
}

function show_experiment_set ($id) {
  $sql="select distinct e.trial_code, e.experiment_year, pei.location
        from experiments as e, 
             phenotype_experiment_info as pei
        where experiment_set_uid = $id
        and e.experiment_uid = pei.experiment_uid";
  // Authenticate, or hide private trials.
  if (!authenticate(array(USER_TYPE_PARTICIPANT,
                          USER_TYPE_CURATOR,
                          USER_TYPE_ADMINISTRATOR))) {
    $sql .= " and e.data_public_flag > 0";
  }
  $sql .= " order by experiment_year, location asc";
  $res=mysql_query($sql) or die("Error in show experiment set");
  if (mysql_num_rows($res) > 0) {
    ?>
<h3>Trials in this Experiment</h3>
<p>
  <table cellpadding="0" cellspacing="0" style="text-align: left;">
    <tr>
      <th>Year</th>
      <th>Location</th>
      <th>Trial</th>
    </tr>
<?php
    while ($row = mysql_fetch_array($res)) {
      $trial=$row['trial_code'];
      $year=$row['experiment_year'];
      $loc=$row['location'];
       echo( "<tr> 
                <td>$year</td> 
                <td>$loc</td>
                <td><a href='display_phenotype.php?trial_code=$trial'>$trial</a></td>
              </tr>");
      /* echo "$year, $loc, $trial<br>"; */
    }
    echo "</table>";
  }
}

// Need a special function only because the foreign key is named 'category_uid'
// instead of 'phenotype_category_uid'.
function show_properties($id) {
  $sql = "select name, description from properties where properties_uid = $id";
  $res = mysql_query($sql) or die("Error in show properties");
  while ($r = mysql_fetch_row($res)) {
    $name = $r[0];
    $desc = $r[1];
  }
  $cat = mysql_grab("select phenotype_category_name from phenotype_category, properties
                     where phenotype_category_uid = category_uid and properties_uid = $id");
  echo "<table>";
  echo "<tr><td>Name<td>$name";
  echo "<tr><td>Description<td>$desc";
  echo "<tr><td>Category<td>$cat";
  echo "</table>";
}

// DEM apr2015: Need a special function to avert trying to show the users name, since we now 
// disallow access to table 'users'.
function show_rawfiles($id) {
  $sql = "select experiment_uid, name, description, directory, updated_on from rawfiles where rawfile_uid = $id";
  $res = mysql_query($sql) or die(mysql_error());
  while ($r = mysql_fetch_row($res)) {
    $exptid = $r[0];
    $trialcode = mysql_grab("select trial_code from experiments where experiment_uid = $exptid");
    $name = $r[1];
    $desc = $r[2];
    $dir = $r[3];
    $updt = $r[4];
  }
  echo "<table>";
  echo "<tr><td><b>Filename</b><td>$name";
  echo "<tr><td><b>Trial</b><td><a href='display_phenotype.php?trial_code=$trialcode'>$trialcode</a>";
  echo "<tr><td><b>Description</b><td>$desc";
  echo "<tr><td><b>Directory</b><td>$dir";
  echo "<tr><td><b>Date</b><td>$updt";
  echo "</table>";
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
  foreach($foreign_tables as $table) 
    $foreign_keys[$table] = get_pkey($table);
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
      $fpkey = $foreign_keys[$table];
      $qstring = getQueryJoin($table, $tabl, $fpkey, $id);
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
  // DEM 9dec10: Uncomment to get "Associated Marker Synonyms (4)".
  //$ret = "\n<h3>Associated " . beautifulTableName($table, 1) . " (" . mysql_num_rows($resource) . ")</h3><br />";
  $ret = "\n<h3>" . beautifulTableName($table, 1) . "</h3>";
  if(mysqli_num_rows($resource) < 1) 
    return $ret  .=  "<p>No Records Found </p>";
  $first = TRUE;
  /* dem aug2013: Change style of all tables controlled by this function! Hope it's okay. */  
  /* $ret .= "\n<table class=\"tableclass1\">\n<thead>\n\t<tr>\n"; */                         
  $ret .= "<table><thead><tr>";                                                               
  while($row = mysqli_fetch_assoc($resource)) {
    if($first) {
      foreach($row as $k=>$v) {
	if($k != $pkey)
	  $ret .= "<th>" . ucwords(str_replace("_", " ", $k)) . "</th>";
      }
      $ret.= "<th></tr></thead><tbody>";
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
