<?php

/* 
 * This file contains most of the functions used when searching
 */


/*
 * The following functions are general term searching functions, match the term in the table. 
 * Nothing fancy.
 */


/* 
 * These functions all return the following:
 * 	- name of the table where the match was found
 *	- name of the field in the table that the match was found
 *	- uid of specific row in the table that the match was found
 * 
 * The format that these things are returned are as follows: 
 *	array( 
 *		[x] => "Table Name @@ Field Name @@ row UID"; 
 *	     )
 */


/*
 * This function is a simple fetching function. This doesn't do any matching between tables, it simply
 * looks in a set of tables for a certain value or set of values. 
 * 
 * You can search multiple tables and multiple fields in those tables.  
 *
 * @param $tableTree - 2 level deep tree. 
 *	1st level - the tables in which we're going to look through
 * 	2nd level - the fields in each of the tables that we're going to look through.
 *
 * @param $searchQ, this is the string that will attempted to be matched. 
 *
 * This functionality is very simple. 1 query per table. 
 */
function generalTermSearch(array $tableTree, $searchQ) {

	//Special % escape. PHP does not have a function to do this for us.
	//$searchQ = preg_replace('/%/', '\\%', $searchQ);

  // Square brackets don't work in a regexp search, must be escaped.
  $searchQ = preg_replace('/\[/', '\\\\\[', $searchQ);
  $searchQ = preg_replace('/\]/', '\\\\\]', $searchQ);

	$tablenames = array_keys($tableTree);
	$tables = count($tablenames);

	$ret = array();

	for($i=0; $i<$tables; $i++) {

		$table = $tablenames[$i];
		if(is_array($tableTree[$table])) { 	//proper tree format
			
			$pkey = get_pkey($table);	

			for($j=0; $j<count($tableTree[$table]); $j++) {

				$field = $tableTree[$table][$j];

 				/*echo "SELECT $pkey 
						     FROM $table 
						     WHERE $field LIKE '$searchQ%'
						    <br />";*/

 				//echo "SELECT $pkey FROM $table WHERE $field REGEXP \"$searchQ\"";

				$find = mysql_query("SELECT $pkey 
						     FROM $table 
						     WHERE $field REGEXP '$searchQ'
						    ") or die(mysql_error());
	
				while($row = mysql_fetch_assoc($find)) {
					$str = $table . "@@" . $field . "@@" . $row[$pkey];
					array_push($ret, $str);
				}
				
			}

		} 

	}

	return $ret;

}


/*
 * This function is a desperate search function. It merely requires a list of tables
 * to search and what to search for. It will search every column in that table for 
 * anything that remotely resembles $searchQ.
 *
 * @param $tables - the tables to search through.
 * @param $searchQ - what to search for.
 *
 */
function desperateTermSearch(array $tables, $searchQ) {

	//Special % escape. PHP does not have a function to do this for us.
	$searchQ = preg_replace('/%/', '\\%', $searchQ);

	$ret = array();

	for($i=0; $i<count($tables); $i++) {

		$visited = array();		//refresh the visited array each time.

		$pkey = get_pkey($tables[$i]);
		$info = get_table_info($tables[$i]);


		/* Deep search through the table first, ignoring foreign keys */
		for($j=0; $j<count($info); $j++) {

			if( ! preg_match('/_uid/', $info[$j]['field']) && ! preg_match('/_on/', $info[$j]['field']) ) {	//we can search this field.

				$field = $info[$j]['field'];

				//echo "SELECT $pkey FROM $tables[$i] WHERE $field LIKE '%$searchQ%'\n<br/>";

				$find = mysql_query("SELECT $pkey 
						     FROM $tables[$i] 
						     WHERE $field LIKE '%$searchQ%'
						    ") or die(mysql_error());

				while($row = mysql_fetch_assoc($find)) {

					if(!in_array($row[$pkey], $visited)) {		//no duplicate rows allowed.

						$str = $tables[$i] . "@@" . $field . "@@" . $row[$pkey];
						array_push($ret, $str);
						array_push($visited, $row[$pkey]);
					}
				}
			}
		}

		/* Quick search through the foreign tables. If the unique name matches then search through the original */
		/* and get those results as well. */

		// Get foreign tables
		$ftables = chk_tbl_references($tables[$i]);

		foreach($ftables as $ftab) {

			$fpkey = get_pkey($ftab);
			$ukeys = get_ukey($ftab);

			foreach($ukeys as $ukey) {

				$foreign_find = mysql_query("SELECT $pkey 
						     FROM $tables[$i]
						     WHERE $fpkey = ANY (
								SELECT $fpkey 
								FROM $ftab
								WHERE $ukey LIKE '%$searchQ%')
						    ") or die(mysql_error());

				while($row = mysql_fetch_assoc($foreign_find)) {

					if(!in_array($row[$pkey], $visited)) {		//no duplicate rows allowed.

						$str = $tables[$i] . "@@" . $fpkey . "@@" . $row[$pkey];
						array_push($ret, $str);
						array_push($visited, $row[$pkey]);
					}
				}
			}
		}
	}
	return $ret;
}

/**
 * This function is specifically designed to take the formatted output of all the functions in this file and display them in
 * a paragraph list. They aren't shown in actual HTML list format but rather <p> format. 
 * 
 * Example:  morex - Line Record
 * 
 * Note: This will perform a fairly simple query on the database to pull the desired information. Sometimes in the
 *          search, this information is already obtained, in which case you may just want to display those results 
 *          independent of this function. However, for the sake of code reuse, this function provides a sure fire 
 *          way for consistent output. 
 * 
 *  @param array $found. The parameter given must be an array. It's contents must be in the formatted order or else warnings
 *                will be generated.
 */
function displayTermSearchResults(array $found) {

	foreach($found as $v) {

		$line = explode("@@", $v);
		$key = get_pkey($line[0]);

		$type = beautifulTableName($line[0]);

		// Intercept marker synonyms that are identical to marker name.
		$skip = "";
		if (($line[0] == "marker_synonyms") && ($line[1] == "value")) {
		  $msquery = mysql_query("select marker_name 
                    from markers, marker_synonyms 
                    where marker_synonym_uid = '$line[2]'
                    and markers.marker_uid = marker_synonyms.marker_uid
                    and markers.marker_name = marker_synonyms.value");
		  if (mysql_num_rows($msquery) > 0) { $skip = "yes"; }
		}
		if (! $skip) {
		  $query = mysql_query("SELECT $line[1] FROM $line[0] WHERE $key = '$line[2]'");
		  while($row = mysql_fetch_assoc($query)) {
echo "<p title=\"$type\"><strong><a href=\"view.php?table=" . urlencode($line[0]) . "&uid=" . urlencode($line[2]) ."\">" . $row[$line[1]] . "</a></strong> &mdash; " . $type . "</p>\n";
		  }
		}
		
	}

}

/**
 * The database contains around 30 tables. Some of these tables store information that has some relationship to another table.
 * These groups of tables are considered to be a 'region'. This function has hardcoded all the tables associated with each 
 * region. 
 *
 * This function was written for the login/index.php.
 *
 */
function table_by_type ($tabletype) {
	$tables=array();
	$tables['all']=explode(" ","alleles barley_pedigree_catalog breeding_programs datasets datasets_breeders datasets_experiments experiment_types experiments genotyping_data genotyping_status gramene institutions line_records map mapset marker_stat marker_types markers markers_in_maps pedigree_relations phenotype_category phenotype_data phenotype_descstat phenotypes taxa tht_base unigene units user_types users");
	$tables['pedigree']=array('barley_pedigree_catalog', 'line_records', 'pedigree_relations', 'taxa', 'tht_base' );
	$tables['genotype']=array('genotyping_data', 'genotyping_status', 'alleles', 'map', 'mapset', 'marker_stat', 'marker_types', 'markers', 'markers_in_maps','unigene');
	$tables['phenotype']=array('gramene', 'phenotype_category', 'phenotype_data', 'phenotype_descstat', 'phenotypes', 'units');
	$tables['experiment']=array('datasets', 'experiment_types', 'experiments');
	$tables['general']=array('breeding_programs', 'datasets_breeders', 'institutions', 'user_types', 'users');
	if (isset($tabletype) && isset($tables[$tabletype])) {
		return $tables[$tabletype];
	}
	else {
		return array();
	}
}



?>
