<?php
/**
 * Quick search
 *
 * PHP version 5.3
 * Prototype version 1.5.0
 *
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/search.php
 *
 * DEM apr2015 added Deep Search
 */
include("includes/bootstrap.inc");
include("theme/normal_header.php");
$mysqli = connecti();
$table_name = strip_tags($_REQUEST['table']);
?>

<div class="box">
<h2>Quick Search <?php echo beautifulTableName($table_name, 1) ?></h2>
<div class="boxContent">

<?php
/****************************************************************************************/
/* Deep Search */
if (isset($_REQUEST['deep'])) {
    /* In-depth search request has been submitted from the search.php not-found page. */
    /* So use search.inc:desperateTermSearch() instead of generalTermSearch(). */
    $keywords = $_REQUEST['keywords'];
    $found = array();
    $deepTables = array('line_records', 'experiments', 'markers', 'map', 'mapset', 
		     'phenotype_experiment_info', 'genotype_experiment_info', 'experiment_set',
		     'CAPdata_programs', 'csr_system', 'line_synonyms', 
		     'marker_synonyms', 'phenotypes', 'properties',
		      'units', 'csr_measurement', 'fieldbook_info');
    // Remove the \ characters inserted before quotes by magic_quotes_gpc.
    $keywords = stripslashes($keywords);
    $kwywords = strip_tags($keywords);
    // If the input is doublequoted, don't split at <space>s.
    if (preg_match('/^".*"$/', $keywords)) {
        $keywords = trim($keywords, "\"");
        $found = desperateTermSearch($deepTables, $keywords);
    } else {
    /* Break into separate words and query for each. */
    $words = explode(" ", $keywords);
    for($i=0; $i<count($words); $i++) {
      if(trim($words[$i]) != "") 
	// Return only items that contain _all_ words (AND) instead of _any_ of them (OR). 
	$partial[$i] = desperateTermSearch($deepTables, $words[$i]);
    }
    $found = $partial[0];
    for ($i = 1; $i < count($words); $i++) {
      $found = array_intersect($found, $partial[$i]);
      // Reset the (numeric) key of the array to start at [0].
      $found = array_merge($found);
    }
  }
}

/****************************************************************************************/
/* Quick Search */
else if (isset($_REQUEST['keywords'])) {
  /* sidebar general search term has been submitted */
  $keywords = $_REQUEST['keywords'];
  $allTables = array();
  $searchTree = array();
  $found = array();

  /* Populate the allTables array */
  if (isset($_REQUEST['table'])) 
    array_push($allTables, mysqli_real_escape_string($mysqli, $_REQUEST['table']));
  else {
    $tableQ = mysqli_query($mysqli, "SHOW TABLES");
    while($row = mysqli_fetch_row($tableQ)) 
      array_push($allTables, $row[0]);
  }

  /* get unique keys of each table */
  foreach($allTables as $table) {
    $ukeys = get_ukey($table);
    $names = array();
    /* do not search through _uids */
    /* do not add duplicates */
    for($i=0; $i<count($ukeys); $i++) {
      if (strpos($ukeys[$i], "_uid")  === FALSE) {
	if (!in_array($ukeys[$i],$names)) 
	  array_push($names, $ukeys[$i] );
      }
    }
    /* add this table to the search tree if there are fields to search */
    if(count($names) > 0) {
      $searchTree[$table] = $names;
    }
  }  // end foreach($allTables)
  // Cool! Here are all the unique keys in the database:
  //print_h($searchTree); 

  // Remove the \ characters inserted before quotes by magic_quotes_gpc.
  $keywords = stripslashes($keywords);
  // If the input is doublequoted, don't split at <space>s.
  if (preg_match('/^".*"$/', $keywords)) {
    $keywords = trim($keywords, "\"");
    $found = generalTermSearch($searchTree, $keywords);
  }
  else {
    /* Break into separate words and query for each. */
    $words = explode(" ", $keywords);
    for($i=0; $i<count($words); $i++) {
      if(trim($words[$i]) != "") 
	/* $found = array_merge($found, generalTermSearch($searchTree, $words[$i])); */
	// Return only items that contain _all_ words (AND) instead of _any_ of them (OR). 
	$partial[$i] = generalTermSearch($searchTree, $words[$i]);
    }
    $found = $partial[0];
    for ($i = 1; $i < count($words); $i++) {
      $found = array_intersect($found, $partial[$i]);
      // Reset the (numeric) key of the array to start at [0].
      $found = array_merge($found);
    }
  }
}

/* Handle the results */
// If no hits.
if (count($found) < 1) {
  if (isset($_REQUEST['deep'])) {
    print "<h3>In-Depth Search executed.</h3>";
    print "<p>Keyword \"$keywords\" not found.<p>";
  }
  else {
    print "<p>Keyword \"$keywords\" not found.<p>";
    print <<<_SEARCHFORM
    <form method="post" action="search.php">
    <div>
    <p><strong>Search deeper: </strong>
    <input type="hidden" name="deep" value="yes">
    <input type="text" size=30 name="keywords" value=$keywords> 
    <input type="submit" class="button" value="Go"><br>
    </div>
    </form>
_SEARCHFORM;
  }
}
// If there's only one hit, jump directly to it.
else if (count($found) == 1) {
  $line = explode("@@", $found[0]);
  echo "Single result, redirecting.<br>";

  // Intercept experiments and route to display_phenotype.php or display_genotype.php.
  if ($line[0] == "experiments") {
    $trialcode = mysql_grab("select trial_code from experiments where experiment_uid = $line[2]");
    $expttype = mysql_grab("select experiment_type_uid from experiments where experiment_uid = $line[2]");
    if ($expttype == 1)
      echo "<meta http-equiv=\"refresh\" content=\"0;url=".$config['base_url']."display_phenotype.php?trial_code=$trialcode\">";
    else
      echo "<meta http-equiv=\"refresh\" content=\"0;url=".$config['base_url']."display_genotype.php?trial_code=$trialcode\">";
  }
  else 
    echo "<meta http-equiv=\"refresh\" content=\"0;url=".$config['base_url']."view.php?table=".urlencode($line[0])."&uid=$line[2]\">";
}
// There is more than one hit.
else {
  if ($_REQUEST['table']) {
    // We have requested a specific table (in includes/search.inc:displayTermSearchResults()).
    $table = $_REQUEST['table'];
    $columnlist = implode(',', $searchTree[$table]);
    echo "<meta http-equiv=\"refresh\" content=\"0;url=browse.php?table=$table&cols=$columnlist&keywords=$keywords\">";
  }
  else   
    // There is more than one hit and we haven't requested a particular table yet.
    displayTermSearchResults($found, $keywords);
}


/****************************************************************************************/
/* Haplotype Search */
/* DEM june 2013: Currently unused?  Replaced by haplotype_search.php? */
/* identify the lines with all the specified allele values */
if(isset($_POST['haplotype'])) {
  /* Get the Marker Uids */
  $markers = array();
  foreach($_POST as $k=>$v) {
    if(strpos(strtolower($k), "marker") !== FALSE) {
      $tm = explode("_", $k);
      $markers[$tm[1]] = $v;
    }
  }
  $marker_instr=implode("," , array_keys($markers));
  if(count($markers) < 1) 
    error(1, "No Markers Selected");
  $query_str="select A.line_record_name, A.line_record_uid, D.marker_uid, 
	      concat(allele_1,allele_2) as value 
	      from line_records as A, tht_base as B, genotyping_data as C, markers as D, alleles as E 
	      where A.line_record_uid=B.line_record_uid and B.tht_base_uid=C.tht_base_uid and 
	      C.marker_uid=D.marker_uid and C.genotyping_data_uid=E.genotyping_data_uid and 
  	      D.marker_uid in (".$marker_instr.")";
  $result=mysqli_query($mysqli, $query_str);
  $lines = array();
  $line_uids=array();
  $line_names=array();
  while ($row=mysqli_fetch_assoc($result)) {
    $linename=$row['line_record_name'];
    $lineuid=$row['line_record_uid'];
    $mkruid=$row['marker_uid'];
    $alleleval=$row['value'];
    $line_uids[$linename]=$lineuid;
    $line_names[$lineuid]=$linename;
    if (! isset($lines[$linename])) $lines[$linename]=array();
    if (! isset($lines[$linename][$mkruid])) $lines[$linename][$mkruid]=$alleleval;	 
  }
  $selLines=array();
  foreach ($lines as $lnm=>$lmks) {
    $flag=0;
    foreach ($markers as $mkr=>$val) {
      if (strtolower($lmks[$mkr])!==strtolower($val)) {
	// print strtolower($lmks[$mkr])."***".strtolower($val)."<br>";
	$flag++;
      }
    }
    if ($flag==0) 
      array_push($selLines, $line_uids[$lnm]);
  }
  if(count($selLines) > 0) {
    $_SESSION['selected_lines']=$selLines;
    sort($selLines);
    print "<p><a href=\"pedigree/pedigree_markers.php\">Display the lines and markers</a>";
    print "<table class='tableclass1'><thead><tr><td>Line names</td></tr></thead><tbody>";
    foreach ($selLines as $luid) {
      print "<tr><td>";
      print "<a href=\"pedigree/show_pedigree.php?line=$luid\">".$line_names[$luid]."</a>";
      print "</td></tr>";
    }
    print "</tbody></table>";
  }
  else 
    echo "<p>Sorry, no records found<p>";
}

/*****************************************************************************************/
/* DEM jun 2013: This section currently unused?  Replaced by phenotype/compare.php? */
  //phenotype search has been made.
  if(isset($_POST['phenotypecategory'])) {
    // Find all lines associated with the given phenotype data.
    $phenotype = $_POST['phenotype'];
    if(isset($_POST['na_value']) && $_POST['na_value'] != "") {	// no range specified, single value
      $value = $_POST['na_value'] == "" ? " " : $_POST['na_value'];
      $search = mysqli_query($mysqli, "
		  SELECT line_records.line_record_uid, line_record_name
		  FROM line_records, tht_base, phenotype_data
		  WHERE value REGEXP '$value'
			  AND line_records.line_record_uid = tht_base.line_record_uid
			  AND tht_base.tht_base_uid = phenotype_data.tht_base_uid
			  AND phenotype_data.phenotype_uid = '$phenotype'
		  ") or die(mysqli_error($mysqli));
    }
    else {
      $first = $_POST['first_value'] == "" ? getMaxMinPhenotype("min", $phenotype) : $_POST['first_value'];
      $last = $_POST['last_value'] == "" ? getMaxMinPhenotype("max", $phenotype) : $_POST['last_value'];
      $search = mysqli_query($mysqli, "
		  SELECT line_records.line_record_uid, line_record_name
		  FROM line_records, tht_base, phenotype_data
		  WHERE value BETWEEN $first AND $last
			  AND line_records.line_record_uid = tht_base.line_record_uid
			  AND tht_base.tht_base_uid = phenotype_data.tht_base_uid
			  AND phenotype_data.phenotype_uid = '$phenotype'
		  ") or die(mysqli_error($mysqli));
    }
    if(mysqli_num_rows($search) < 1) 
      echo "<p>Sorry, no records found<p>";
    else {
      $found = array();
      while($line = mysqli_fetch_assoc($search)) 
	array_push($found, "line_records@@line_record_name@@$line[line_record_uid]");
    }
  }

/*****************************************************************************************/
		
?>
	    </div>
</div>
</div>

<?php
mysqli_close($mysqli);
include("theme/footer.php");
