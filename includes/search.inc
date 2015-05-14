<?php
//*****************************************************************************
//
//
// 07/05/2011  JLee   Update table names to reflect current schema
//*****************************************************************************
/* 
 * This file contains most of the functions used when searching with Quick Search.
 * Special handling of certain data classes such as Trial is routed
 * in function displayTermSearchResults().
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
  ini_set('memory_limit', '2G');
  //Special % escape. PHP does not have a function to do this for us.
  //$searchQ = preg_replace('/%/', '\\%', $searchQ);
  // Square brackets don't work in a regexp search, must be escaped.
  $searchQ = preg_replace('/\[/', '\\\\\[', $searchQ);
  $searchQ = preg_replace('/\]/', '\\\\\]', $searchQ);
  // If the $searchQ came from a POST form, "&" will have been replaced. Put it back.
  $searchQ = str_replace('&amp;', '&', $searchQ);  
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
		      ") or die("Error in includes/search.inc:generalTermSearch()<br>".mysql_error());
	while($row = mysql_fetch_assoc($find)) {
	  $str = $table . "@@" . $field . "@@" . $row[$pkey];
	  array_push($ret, $str);
	}
      }
    } 
  }
  // DEM apr2015: Set a limit, for memory and execution time's sake.
  $limit = 1000000;
  if (count($ret) > $limit)
    die("More than 1 million records were found.  Please narrow your search.<p>");
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
 * Example:  MOREX - Line Record
 * 
 * Note: This will perform a fairly simple query on the database to pull the desired information. Sometimes in the
 *          search, this information is already obtained, in which case you may just want to display those results 
 *          independent of this function. However, for the sake of code reuse, this function provides a sure fire 
 *          way for consistent output. 
 * 
 *  @param array $found. The parameter given must be an array. Its contents must be in the formatted order or else warnings
 *                will be generated.  
 *
 * DEM mar2015: First show only the number of hits in each class.
 */
function displayTermSearchResults(array $found, $keywords) {
  foreach ($found as $v) {
    // $v is "<table>@@<column>@@<uid>".
    $line = explode("@@", $v);
    $type = beautifulTableName($line[0]);
    // Intercept marker synonyms that are identical to marker name.
    $skip = "";
    if (($line[0] == "marker_synonyms") && ($line[1] == "value")) {
      $msquery = mysql_query("select marker_name 
                    from markers, marker_synonyms 
                    where marker_synonym_uid = '$line[2]'
                    and markers.marker_uid = marker_synonyms.marker_uid
                    and markers.marker_name = marker_synonyms.value");
      if (mysql_num_rows($msquery) > 0) 
	$skip = "yes"; 
    }
    // Intercept experiments and route to display_phenotype.php or display_genotype.php.
    if ($type == "Experiment") {
      $type = "Trial";
      $trialcode = mysql_grab("select trial_code from experiments where experiment_uid = $line[2]");
      $expttype = mysql_grab("select experiment_type_uid from experiments where experiment_uid = $line[2]");
      /* if ($expttype == 1)  */
      /* 	echo "<p><strong><a href=\"display_phenotype.php?trial_code=$trialcode\">$trialcode</a></strong> &mdash; " . $type . "</p>\n";		   */
      /* else */
      /* 	echo "<p><strong><a href=\"display_genotype.php?trial_code=$trialcode\">$trialcode</a></strong> &mdash; " . $type . "</p>\n"; */
    }
    // Use a better class name than the table name:
    if ($type == 'Experiment Set')
      $type = 'Experiment';
    if (! $skip) {
      $uids[$type][] = $line[2];
      // Compute number of hits in each class.
      $hits[$type]++;
      $table[$type] = $line[0];
      $key[$type] = get_pkey($line[0]);
      $namecolumn[$type] = $line[1];
    }
  }
  ksort($hits);
  foreach (array_keys($hits) as $u) {
    // Limit to 900 or the URL constructed will be too big.  1000 bounces off PHPIDS.
    $uu = array_slice($uids[$u], 0, 900);
    $uidlist[$u] = implode(",", $uu);
  }
  // Show the number of hits in each class.
?>
<style type=text/css>
   table td { text-align: center; }
</style>
<?php
  print "<table><tr><th>Data Class<th>Records";
  foreach (array_keys($hits) as $type) {
    /* print "<tr><td><a href='browse.php?table=$table[$type]&namecol=$namecolumn[$type]&hits=$uidlist[$type]'>$type</a>"; */
    /* print "<tr><td><a href='search.php?table=$table[$type]&keywords=$keywords'>$type</a>"; */
    print "<tr><td><a href='browse.php?table=$table[$type]&col=$namecolumn[$type]&keywords=$keywords'>$type</a>";
    print "<td>$hits[$type]";
  }
  print "</table>";
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
	$tables['all']=explode(" ","CAPdata_programs allele_frequencies alleles barley_pedigree_catalog barley_pedigree_catalog_ref data_providers datasets datasets_experiments experiment_types experiments file_process filter_sets filters genotype_experiment_info genotyping_data input_file_log institutions line_records line_synonyms map mapset marker_annotation_types marker_annotations marker_synonym_types marker_synonyms marker_types markers markers_in_maps pedigree_relations phenotype_category phenotype_data phenotype_descstat phenotype_experiment_info phenotype_mean_data phenotypes session_variables settings tht_base trait_ontology_term units user_types users");
	$tables['pedigree']=array('barley_pedigree_catalog','barley_pedigree_catalog_ref','line_records','line_synonyms','pedigree_relations','tht_base','trait_ontology_term');
	$tables['genotype']=array('genotyping_data','allele_frequencies','alleles','map','mapset','marker_annotation_types','marker_annotations','marker_synonym_types','marker_synonyms','marker_types','markers','markers_in_maps');
	$tables['phenotype']=array('phenotype_category','phenotype_data','phenotype_descstat','phenotype_mean_data','phenotypes','units');
	$tables['experiment']=array('datasets','datasets_experiments','experiment_types','experiments','genotype_experiment','phenotype_experiment_info','CAPdata_programs','data_providers','institutions');
	$tables['general']=array('file_process','filter_sets','filters_info','input_file_log','session_variables','settings','user_types','users');
	if (isset($tabletype) && isset($tables[$tabletype])) {
		return $tables[$tabletype];
	}
	else {
		return array();
	}
}



?>
