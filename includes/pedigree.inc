<?php

/*
 * Multiflex-3
 */

/*
 * This function shows all the line records in the database in a list for easy viewing.
 *
 * @param $page - the list is a list of links each pointing to a location where their line records can be seen.
 *		$page is the page where this happens.
 * @return nothing - it echos the result, again use with output buffering if you wish.
 */
function showLines($page, $start = 0, $limit = 40)
{
    global $mysqli;
    $qstring = "SELECT line_record_uid, line_record_name
			FROM line_records
			ORDER BY line_record_name LIMIT ? OFFSET ?";

    echo "\n<ul>";
    if ($stmt = mysqli_prepare($mysqli, $qstring)) {
        mysqli_stmt_bind_param($stmt, "ii", $limit, $start);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $line_record_uid, $line_record_name);
        while (mysqli_stmt_fetch($stmt)) {
            echo "\n\t<li><a href=\"$page?line=".urlencode($line_record_uid)."\">$line_record_name</a></li>";
        }
        mysqli_stmt_close($stmt);
    }
    echo "\n</ul>";
    showPages($qstring, $page, $start, $limit);
}

/*
  To display something useful for a barley_pedigree_catalog_ref report in view.php.
*/
function show_barley_pedigree_catalog_ref($id)
{
    global $mysqli;
    // Show the associated line_record.
    $sql = "select l.line_record_uid, line_record_name from barley_pedigree_catalog_ref b, line_records l where barley_pedigree_catalog_ref_uid = $id and b.line_record_uid = l.line_record_uid";
    $res= mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    $row = mysqli_fetch_row($res);
    print "<p>Please see Line Record <a href='view.php?table=line_records&uid=$row[0]'>$row[1]</a><p>";
    // Display the basics.
    show_general("barley_pedigree_catalog_ref", $id);
}

/*
 * This function shows a line record in an easy to read table format.
 * It also displays the parents of the particular line record in a comma separated list.
 *
 * @param $id - the id of the record of which to retrieve.
 * @return nothing - it echos the result. Use in conjunction with output buffering if you wish to maniuplate.
 */
function show_line_records($id)
{
  global $mysqli;
  $name = getAccessionName($id);
  // Passport info
  echo "<div style='float: left; margin-left: 10px;'>";
  echo "<h3>Passport</h3>";
  echo "<table>";
  // Custom instead of show_general():
  $res= mysqli_query($mysqli, "
     select line_record_name, breeding_program_code, pedigree_string, generation, description, updated_on
     from line_records where line_record_uid = $id") or die(mysqli_error($mysqli));
  $row = mysqli_fetch_assoc($res);
  $bpcode = $row['breeding_program_code'];
  $bpname = mysql_grab("select data_program_name from CAPdata_programs where data_program_code = '$bpcode'");
  foreach ($row as $k=>$v) {
    // If there's no value, ignore completely.
    if (!empty($v)) {
      if ($k == 'breeding_program_code')
	echo "<tr><td><b>Breeding program</b><td>$bpname ($bpcode)";
      else {
	echo "<tr><td><strong>". ucfirst(str_replace("_", " ", $k)) . "</strong>";
	echo "<td>$v";
      }
    }
  }
  echo "</table></div>";

  // Synonyms and links to GRIN
  echo "<div style='float: left; margin-left: 10px;'>";
  echo "<h3>Synonyms</h3>";
  echo "<table cellspacing=0 cellpadding=0>";
  $synonyms = getSynonymNames($id);
  if ($synonyms===FALSE)
    echo "<tr><td>No synonyms";
  else 
    echo "<tr><td>". implode(", ",  $synonyms);
  $bpc = getBarleyPedigreeCatalogIDs($id);
  if ($bpc===FALSE)
    echo "<tr><td>No database links";
  else {
    $bpclink = getBarleyPedigreeCatalogLink($id);
    $n_bpc = count($bpclink);
    for ($i = 0;$i<$n_bpc;$i++) 
      echo "<tr><td><a href=".$bpclink[$i].">".$bpc[$i]."</a>";
  }
  echo "</table>";
  // Pheno/Geno data links
  // part of the Synonyms div
  echo "<h3>Phenotype,<br>Genotype Data</h3>";
  echo "<table>";
  $phenotype = lineHasPhenotypeData($id) ? "<a href=\"phenotype/show.php?line=$id\">Show</a>" : "None";
  echo "<tr><td><strong>Phenotype Results</strong>";
  echo "<td>$phenotype";
  $genotype = lineHasGenotypeData($id) ? "<a href=\"genotyping/show.php?line=$id\">Show</a>" : "None";
  echo "<tr>";
  echo "<td><strong>Marker Alleles</strong>";
  echo "<td>$genotype";
  echo "</table><br>";
  echo "</div>";

  // Properties
  echo "<div style='float: left; margin-left: 10px;'>";
  $sql = "select properties.name, pv.value
	from line_properties lp, property_values pv, properties
	where line_record_uid = $id
	and lp.property_value_uid = pv.property_values_uid
	and properties.properties_uid = pv.property_uid";
  $res = mysqli_query($mysqli, $sql) or die (mysqli_error($mysqli));
  if (mysqli_num_rows($res) > 0) {
    echo "<h3>Genetic Properties</h3>";
    echo "<table><tr>";
    echo "<th>Attribute/Gene<th>Value/Allele";
    while ($r = mysqli_fetch_row($res))
      echo "<tr><td>$r[0]<td>$r[1]";
    echo "</table>";
  }
  echo "</div>";


  // Pedigree
  echo "<div style='float: left; margin-left: 10px;'>";
  echo "<h3>Pedigree</h3>";
  echo "<table cellspacing=0 cellpadding=0>";
  $tree = getPedigrees($name);
  $pedigree = (!is_array($tree[$name])) ? "No Record" : tree2str($tree, $name);
  if (is_array($tree[$name])) {
    echo "<tr><td><strong>Ancestor<br>Expansion</strong>";
    // break-all required for long pedigrees like MOREX, except in Firefox
    echo "<td style='word-break:break-all;'>$pedigree<br>";
    echo "<a href=\"pedigree/pedigree_tree.php?line=".urlencode($name)."\">Graphical Tree</a>";
  }
  // Parents
  echo "<tr><td><strong>Parents</strong><td>";
  $prnts = getParents($id);
  if (empty($prnts))
    echo "Not found";
  else {
    foreach ($prnts as $pt) 
      $lnkpts .= "<a href=".$config['base_url']."view.php?table=line_records&name=$pt>$pt</a>, ";
    echo rtrim($lnkpts, ' ,');
  }
  // Children
  echo "<tr><td><strong>Children</strong><td>";
  $kids = implode(", ", getChildren($id));
  if (empty($kids))
    echo "Not found";
  else 
    echo "$kids<br><a href=\"pedigree/reverse_pedigree.php?line=".urlencode($name)."\">Descendants";
  echo "</table><br>";
  echo "</div>";

  // Panels
  $myid = 0;
  if (loginTest2()) {
    $mysession = loadUser($_SESSION['username']);
    $myid = $mysession['users_uid'];
  }
  $sql = "select name, line_ids, comment 
	from linepanels
	where users_uid = $myid or users_uid IS NULL";
  $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
  $i = 0;
  while ($row = mysqli_fetch_row($res)) {
    $lineset = explode(",", $row[1]);
    if (in_array($id, $lineset)) {
      $panelset[$i][name] = $row[0];
      $panelset[$i][desc] = $row[2];
      $i++;
    }
  }
  if (!empty($panelset)) {
    echo "<div style='float: left; margin-left: 10px;'>";
    echo "<h3>Panels</h3>";
    echo "<table cellspacing=0 cellpadding=0>";
    echo "<th>Name<th>Description";
    $i = 0;
    foreach ($panelset as $paname)
      echo "<tr><td>$paname[name]<td>$paname[desc]";
    echo "</table><br>";
    echo "</div>";
  }
}

/*
 * This function gets the parents of a particular line. 
 * It uses a subquery for efficency.
 *
 * @param $id - the id of the line of which to get the parents of.
 * @return $names - an array containing the names of the parents.
 */
function getParents($id) {
    global $mysqli;
	$parents = mysqli_query($mysqli, "
			SELECT line_record_name
			FROM line_records
			WHERE line_record_uid = ANY (
				SELECT parent_id 
				FROM pedigree_relations 
				WHERE line_record_uid = '$id'
				) 
			") or die(mysqli_error($mysqli));

	$names = array();

	while($row = mysqli_fetch_assoc($parents)) {
		array_push($names, $row['line_record_name']);
	}

	return $names;
}

/*
 * This function gets the children of a particular line. 
 * It uses a subquery for efficency.
 *
 * @param $id - the id of the line of which to get the children of.
 * @return $names - an array containing the names of the children.
 */
function getChildren($id) {
    global $mysqli;
	$children = mysqli_query($mysqli, "
			SELECT line_record_name
			FROM line_records
			WHERE line_record_uid = ANY (
				SELECT line_record_uid 
				FROM pedigree_relations 
				WHERE parent_id = '$id'
				) 
			") or die(mysqli_error($mysqli));

	$names = array();

	while($row = mysqli_fetch_assoc($children )) {
		array_push($names, $row['line_record_name']);
	}

	return $names;
}

function getPedigrees($name) {
	return array($name=>getPedigrees_r($name, array()));
}

/*
 * This function generates a pedigree tree. It has to have a starting element to act as root. 
 * The algorithm is slightly hard to follow and recursive in nature. 
 */
function getPedigrees_r ($qname, $nvisited) {
	global $nvisited;

	if(!is_array($nvisited))
		$nvisited = array();

	if (! isset($qname) || $qname==''){ 
		return "Error: Wrong Parameters!";
	}
	elseif (array_key_exists($qname, $nvisited)) {
		return "Folded";
	}
	else {

		$parents = getParents(getPedigreeId($qname));

		$nvisited[$qname] = 1;

		if (count($parents) < 1) {
			return "N/A";
		}
		else {
			$tree=array();
			foreach ($parents as $key=>$val) {
				$tree[$val] = getPedigrees_r($val, $nvisited);
			}
			return $tree;
		}
	}	
}


function getReversePedigrees($name) {
	return array($name=>getReversePedigrees_r($name, array()));
}

/*
 * This function generates a pedigree tree getting the children instead of the parents.
 * It has to have a starting element to act as root. 
 * The algorithm is slightly hard to follow and recursive in nature. 
 */
function getReversePedigrees_r($name, $visited=array()) {
	global $visited;

	if(!is_array($visited))
		$visited = array();

	if($name == "") {
		return "Error: Wrong Parameters";
	}
	elseif (array_key_exists($name, $visited)) {
		return "Folded";
	}
	else {

		$children = getChildren(getPedigreeId($name));

		$visited[$name] = 1;

		if (count($children)<1) {
			return "N/A";
		}
		else {
			$tree=array();
			foreach ($children as $key=>$val) {
				$tree[$val] = getReversePedigrees_r($val, $visited);
			}
			return $tree;
		}
	}	
}

/**
 * get_tree_depth get the depthe of a tree
 * @param array $arr The input tree
 * @return int $treedep The depth of the input tree
 */
function get_tree_depth (array $arr, $td=0) {
	global $maxtd;

	if ($td>$maxtd) {
		$maxtd=$td;
	}

	$ks = array_keys($arr);

	for ($i=0; $i<count($ks); $i++) {

		if (is_array($arr[$ks[$i]])) {		    
			get_tree_depth($arr[$ks[$i]],$td+1);
		}
	}
	return $maxtd;
}

/**
 * tree2str_wi will travers the tree in-order and keep the internal node's names
 * in a format node1/$height(internal-node-name)/node2...
 * @param array $parr the input tree
 * @param mixed $rtk the key for the root of the tree
 * @return string $tstr the packed string for the tree
 */

function tree2str_wi (array $parr, $rtk) {
	global $maxtd;
	global $tstr;
	$maxtd=0;
	$tstr="";
	$depth=get_tree_depth($parr, 0);
	get_tree_str($parr,$rtk,$depth,0, 1);
	return $tstr;
}

/**
 * get_tree_str this is the recursive part of tree2str_wi
 * @param array $arr the input tree
 * @param mixed $rtk the key of the root
 * @param int $depth the depth of the tree, for future use
 * @param int $td the depth of the current node, for future use
 * @param int $iswt the switch for whether include the internal node name, 1 for include, 0 for not
 */
function get_tree_str(array $arr,$rtk, $depth, $td, $iswt) {
	global $tstr;
	global $maxtd;
	if (is_array($arr[$rtk])) {
		$ks=array_keys($arr[$rtk]);
		$leftnode=$ks[0];
		get_tree_str($arr[$rtk], $leftnode, $depth, $td+1, $iswt);
		$maxtd=0;
		$ndepth=get_tree_depth($arr[$rtk],0);
		if ($iswt!=1) {
			if ($ndepth+1==1) $tstr.="/";
			elseif ($ndepth+1==2) $tstr.="//";
			else $tstr.="/".($ndepth+1)."/";
		}
		else {
			if ($ndepth+1==1) $tstr.="/1(".$rtk.")/";
			elseif ($ndepth+1==2) $tstr.="/2(".$rtk.")/";
			else $tstr.="/".($ndepth+1)."(".$rtk.")/";			
		}
		for ($i=1; $i<count($ks); $i++) {
			$rightnode=$ks[$i];
			get_tree_str($arr[$rtk],$rightnode,$depth, $td+1, $iswt);
		}
	}
	else {
		$tstr.="$rtk";
	}
	return;
}

function tree2str (array $parr, $rtk) {
	global $maxtd;
	global $tstr;
	$maxtd=0;
	$tstr="";
	$depth=get_tree_depth($parr, 0);
	get_tree_str($parr,$rtk,$depth,0,0);
	return $tstr;
}



/*
 * This function determines how many nodes are on each level of the tree
 * It has 2 helper methods.
 * 	1. getChart_r() - the recursive function
 *	2. getChartParse() - this function puts the chart together in a more understandable manner
 *
 * Ex: use the getPedigree_r() function to retrieve the tree-array structure then send that to getChart()
 *     to be parsed in a helpful format
 *
 * This array contains a list of indexes and values. The index represents the level of the tree.
 * Example:  [0] => 1  -- index 0 (or the root node) only has 1 element in it.
 * 	     [1] => 2  -- index 1 or the first level after the root, has 2 elements in it.
 * 	etc etc..
 *
 * The length of this array also gives the depth of the tree. So it does not need to be used in conjuction with get_tree_depth();
 *
 * This was not the most intuitive thing to write.
 */
function getChart($tree) {

	$chart = array();
	$chart[0] = "0-1";		//level 0 has 1 node (root)

	if(is_array($tree)) {

		$chart = array_merge($chart, getChart_r($tree, 1));
		$chart = getChartParse($chart);

	}
	return $chart;

}
function getChart_r($node, $level) {

	$chart = array();
	$count = 0;

	$chart[$level] = $level . "-" . count($node);

	$keys = array_keys($node);

	for($i=0; $i<count($node); $i++) {

		if(is_array($node[$keys[$i]])) {
			$chart = array_merge($chart, getChart_r($node[$keys[$i]], $level+1));
		}

	}

	return $chart;

}
function getChartParse($chart) {

	$new = array(); 

	for($i=0; $i<count($chart); $i++) {
		$k = explode("-", $chart[$i]);
		$new[$k[0]] += $k[1];
	}

	return $new;
}

function getNameChart(array $pediarr) {

	$level = getNameChart_r($pediarr);
	$level[0] = array_keys($pediarr);

	return $level;
}
function getNameChart_r(array $pediarr, $num=1) {
	static $level = array();	//globally passed through the recursion
	
	if(is_array($pediarr)) {
		$root = array_keys($pediarr);
		
		for($i=0; $i<count($root); $i++) {
			if(is_array($pediarr[$root[$i]])) {
				if(!is_array($level[$num])) {
					$level[$num] = array();
				}
				$level[$num] = array_merge($level[$num], array_keys($pediarr[$root[$i]]));	
				getNameChart_r($pediarr[$root[$i]], ($num+1));
			}
		}
	}
	return $level;
}

/*
 * This function will actually display the Line Record to be edited. 
 *
 * @param $where - sets the conditions of which to select the row(s). This makes it possible to select any number of rows.
 * @param $page - editing allows for updating and has a button that goes to a certain page to update. This variable sets that page
 * @param $limit - since there are so many line records its broken down into pages. This is default set at LIMIT 20 so there will only be 20 records shown at a time.
 * 
 * @return nothing - this function outputs to the screen.
 */
function editLineRecord($where, $page, $limit="40", $start="0") {

	$ignore = array("line_record_uid", "taxa_uid");
	editGeneral("line_records", $where, $page, $ignore, $limit, $start);
}

/*
 * These 2 functions are essentially quickwrappers for the above editLineRecord function. 
 * These have preprogrammed behavior to display a certain number of lines in them.
 */ 
 
 /*
  * This function displays the first 20 lines in the database.
  */
function editAllLines($page, $start) {

	editLineRecord("1", $page, 20, $start);

}


/*
 * This will select a list of line records to edit from a given array of IDs
 * 
 * If we have a bunch of IDs that we want to edit and there isn't a range
 * of them then we can use this function to display them. 
 *
 * @param $IDRange - an array of IDs to edit. This MUST be an array.
 * @param $page - the page that the update button will travel to
 *
 * @return nothing
 * @see editLineRecord()
 */ 
function editSelectLines($IDRange, $page, $start) {

	if(is_array($IDRange)) {

		$where = "";
		for($i=0; $i<count($IDRange); $i++) {
			if($i != 0) 
				$where .= " OR ";

			$where .= "line_record_uid = '$IDRange[$i]'";
		}

		editLineRecord($where, $page, "20", $start);
	}

}

/*
 * This function displays an edit form for all existing pedigree entries in the
 * pedigree relation table.
 *
 * This function includes a delete button.
 *
 * @param $line - the accession name of the line to retrieve.
 * @param $callback - the page that the form is to call to. That page will handle all the updating and deleting using functions here.
 *  
 * @return nothing - it echos its results.
 */
function editPedigree($line, $callback) {
    global $mysqli;
	$pedigree = mysqli_query($mysqli, "
			SELECT * 
			FROM pedigree_relations
			WHERE line_record_uid = (
				SELECT line_record_uid
				FROM line_records
				WHERE line_record_name = '$line'
				)
			") or die(mysqli_error($mysqli));

	$ignore = array("pedigree_relation_uid", "line_record_uid", "parent_id", "updated_on", "created_on");
	
	$count = 1;

	while($row = mysqli_fetch_assoc($pedigree)) {

		echo "<h2>Parent $count</h2>";
		echo "<div class=\"boxContent\">";
		echo "<form action=\"$callback\"  method=\"post\">";

		foreach($row as $k=>$v) {
			
			if($k == "parent_id") {
				echo "<strong>parent:</strong> ". getAccessionName($v) ."  <a href=\"login/edit_line.php?line=".urlencode($v)."\">Edit Line</a> <br />";
			}
			else if( !in_array($k, $ignore) ) {
				echo "<strong>$k:</strong>\t <input type=\"text\" name=\"$k\" value=\"$v\" /><br />";
			}
		}

		echo "<input type=\"submit\" name=\"submit-$row[line_record_uid]-$row[parent_id]\" value=\"Submit\" /> <input type=\"reset\" value=\"Reset\" /> <input type=\"submit\" name=\"delete-$row[line_record_uid]-$row[parent_id]\" value=\"Delete\" />";
		echo "</form>";
		echo "</div>";

		echo "<br />";
		$count++;
	}

	if($count == 1){
		echo "<p>No Parents Found!</p>";
	}
	
}


/*
 * This function is a helper function. It takes the line ID and returns its corresponding accession name. 
 * 
 * If used in conjunction with the function getPedigreeId() then the programmer
 * will have successfully accomplished nothing.
 *
 * @param $id - the id of the line who's name to retrieve
 * @return the corresponding name if it exists in the database. If it does not, an error message is echoed and 
 * 	the function returns FALSE.
 * @see getPedigreeId()
 */ 
function getAccessionName($id) {
    global $mysqli;
	$pedigree = mysqli_query($mysqli, "
			SELECT line_record_name
			FROM line_records
			WHERE line_record_uid = '$id'
			");

	if(mysqli_num_rows($pedigree) < 1) {
		error(1, "Line $id is not in our database");
		return FALSE;
	}

	$row = mysqli_fetch_assoc($pedigree);
	return $row['line_record_name'];
}

/*
 * This function is a helper function. It takes the line ID and returns its corresponding synonym names. 
 *
 * @param $id - the id of the line whose synonyms to retrieve
 * @return the corresponding synonyms if they exists in the database. If it does not,  * 	the function returns FALSE.
 */ 
function getSynonymNames($id) {
    global $mysqli;
    $sql = "SELECT line_synonym_name FROM line_synonyms WHERE line_record_uid = '$id'";
    $sql_syn = mysqli_query($mysqli, $sql) or die (mysqli_error($mysqli));
    if (mysqli_num_rows($sql_syn) < 1) {
        return false;
    }
    while ($row = mysqli_fetch_array($sql_syn)) {
        $synonyms[] = $row['line_synonym_name'];
    }
    return $synonyms;
}

/*
 * This function is a helper function. It takes the line ID and returns its corresponding links to barley pedigree
 * catalogs such as GRINS. 
 *
 * @param $id - the id of the line whose synonyms to retrieve
 * @return the corresponding synonyms if they exists in the database. If it does not,
 * 	the function returns FALSE.
 */
function getBarleyPedigreeCatalogIDs($id)
{
    global $mysqli;
    $sql_bpc = mysqli_query($mysqli, "
	SELECT b_ref.barley_ref_number as value, b.barley_pedigree_catalog_name as catname
	FROM barley_pedigree_catalog_ref AS b_ref, barley_pedigree_catalog AS b
	WHERE b_ref.line_record_uid = '$id'
	AND b.barley_pedigree_catalog_uid = b_ref.barley_pedigree_catalog_uid
	") or die(mysqli_error($mysqli));
    if (mysqli_num_rows($sql_bpc) < 1) {
	return FALSE;
    }

    while ($row = mysqli_fetch_array($sql_bpc)) {
        $bpc[] = $row['catname'].": ".$row['value'];
    }
    return $bpc;
}

/*
 * This function is a helper function. It takes the line ID and returns its corresponding links to barley pedigree
 * catalogs such as GRINS. 
 *
 * @param $id - the id of the line whose synonyms to retrieve
 * @return the corresponding synonyms if they exists in the database. If it does not,
 * 	the function returns FALSE.
 */
function getBarleyPedigreeCatalogLink($id)
{
    global $mysqli;
    $sql_bpc = mysqli_query($mysqli, "
	SELECT b_ref.barley_ref_number as value, b.link_out
	FROM barley_pedigree_catalog_ref AS b_ref, barley_pedigree_catalog AS b
	WHERE b_ref.line_record_uid = '$id'
	AND b.barley_pedigree_catalog_uid = b_ref.barley_pedigree_catalog_uid
	") or die(mysqli_error($mysqli));

    if (mysqli_num_rows($sql_bpc) < 1) {
        return false;
    }

    while ($row = mysqli_fetch_assoc($sql_bpc)) {
        // GRIN PI numbers include a required Space character.
        $value = urlencode($row['value']);
        $url_bpc[] = str_replace('XXXX', $value, $row['link_out']);
    }
    return $url_bpc;
}

/*
 * This function is a helper function. It takes line accession name and returns its corresponding ID. 
 * 
 * If used in conjunction with the function getAccessionName() then the programmer
 * will have successfully accomplished nothing.
 *
 * @param $name - the name of the line to retrieve.
 * @return the corresponding ID if it exists in the database. If it does not, an error message is echoed and 
 * 	the function returns FALSE.
 * @see getAccessionName()
 */ 
function getPedigreeId($name) {
    global $mysqli;
	$pedigree = mysqli_query($mysqli, "
			SELECT line_record_uid
			FROM line_records
			WHERE line_record_name = '$name'
			");

	if(mysqli_num_rows($pedigree) < 1) {
		error(1, "$name is not in our database");
		return FALSE;
	}

	$row = mysqli_fetch_assoc($pedigree);
	return $row['line_record_uid'];

}

/*
 * This function deletes a pedigree relation entry from the predigree_relations table
 * 
 * To do this it must possess the primary key - line_record_uid and parent_id
 * 
 * @param $line_record_uid - the line_record_uid
 * @param $parent_id - the parent's id
 *
 * @return FALSE on failure along with an echo of the mysql_error() function
 * 	   TRUE on successful deletion
 */
function deletePedigree($line_record_uid, $parent_id) {
    global $mysqli;
	$pedigree = mysqli_query($mysqli, "
			DELETE
			FROM pedigree_relations
			WHERE line_record_uid = '$line_record_uid' AND parent_id = '$parent_id'
			LIMIT 1
			");

	if(!$pedigree) {
		error(1, mysqli_error($mysqli));
		return FALSE;
	}
	else 
		return TRUE;

}

/*
 * This function checks to make sure that when a pedigree is added
 * that a parent is not one of its own children because that doesn't
 * make any sense.
 *
 * Essentially this function is a check to prevent circular loops
 * in the tree. 
 *
 * Ex: Cree is the parent of Morex, so therefore Morex can not be Cree's Parent.
 *
 * Somehow plants do end up being their own grandparent so this function
 * shouldn't be used everywhere. Use wisely. 
 *
 * This function first generates the reverse pedigree (children) tree using 
 * the child of the pedigree relationship. Then it searches the 
 * tree looking for any occurance of the $parent. This will prevent 
 * a child from being its own grandparent.
 *
 * @param $line - the accession name of child in the pedigree relationship
 * @param $parent - the accession name parent in the pedigree relationship
 */ 
function circularParentTest($line, $parent) {

	$passed = TRUE;

	$tree = getReversePedigrees($line);
	$level = getNameChart($tree);

	/* Iterating the name chart */
	for($i=0; $i<count($level); $i++) {
		for($j=0; $j<count($level[$i]); $j++) {
			if($level[$i][$j] == $parent) 
				$passed = FALSE;
		}
	}

	return $passed;
}


function pediStr2SQL($line, $str) {
    global $myslqi;
	$duplicates = 0;

	//generate array tree based off string 
	$mx = generate_pedigree_tree($str);
	if(is_array($mx)) {

		//generate the pedigree relationships based off array tree
		$relationships = buildRelationshps($mx, getMaxId("line_record", "line_record_uid"));

		//iterate through the relationships
		for($i=0; $i<count($relationships); $i++) {
			for($j=0; $j<count($relationships[$i]); $j++) {

				//get arrays
				$pedigree = explode("@@", $relationships[$i][$j]);
				$ids = array();

				//if the parent is not found then the child is root, root is given in $line
				//so we have to reverse 
				if($pedigree[1] == "") {
					$pedigree[1] = $line;
				}

				//add each parent and line to the line_record table
				$ids[0] = add_attribute ("line_record_name", $pedigree[0], "line_record", "line_record_uid");
				$ids[1] = add_attribute ("line_record_name", $pedigree[1], "line_record", "line_record_uid");

				//add the relationsihp to the pedigree_relations table
				$vals = array(
					"line_record_uid"=>$ids[1][1],
					"parent_id"=>$ids[0][1]
					);
				$ids = add_array_attributes($vals, array(1,1), "pedigree_relations", 1, 0, "line_record_uid");
				if(mysqli_errno($mysqli) == 1062) {	//duplicate error
					$duplicates++;
				}
			}
		}

		return $duplicates;
	}
	else {
		error(1, "Please enter a valid pedigree string");
		return FALSE;
	}
}

function buildRelationshps(array $tree, $unique=0, $level=0, $parent="") {

	static $rtree = array();

	$current = array();

	//iterate through the tree
	for($i=0; $i<count($tree); $i++) {

		if(is_array($tree[$i])) { 	//we have found an internal node
			$name = "internal" . $unique++ . "@@" . $parent;  //generate unique name
			array_push($current, $name); 
			buildRelationshps($tree[$i], $unique, $level+1, $name);  
		}
		else {	//we found a leaf
			array_push($current, trim($tree[$i]) . "@@" . $parent);
		}
	}
	
	//incase we've been to this level before...
	if(is_array($rtree[$level]))
		$rtree[$level] = array_merge($rtree[$level], $current);
	else
		$rtree[$level] = $current;

	//return the whole tree
	return $rtree;
}

function generate_pedigree_tree($pstr) {
	$pstr=preg_replace('/\[.*?\]|;/', '',$pstr);

	/*
	 * Special replacement to deal with all number line names (should be forbidden)
	 */
	preg_match_all('/\/(\d{3,})/', $pstr, $matches);
	foreach ($matches[1] as $match) {
		$pstr = preg_replace("/$match/", (" ".$match), $pstr);
	}

	$lvs=preg_split('/(\/\d+\({0,1}.*?\){0,1}\/|\/\/|\/)/',$pstr, -1, PREG_SPLIT_DELIM_CAPTURE); // consider single slash later
	$maxcol=0;
	$itnidx=array();
	$itnnms=array();
	$leaves=array();
	foreach ($lvs as $val) {
		if (preg_match('/\/(\d+)\({0,1}(.*?)\){0,1}\//',$val, $mts)) {
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
	   return parse_pedigree_matrix($mx, $maxcol-1, 0, $leaves);
}
