<?php
require 'config.php';
/*
 * Logged in page initialization
 * 
 * 8/16/2010 J.Lee  Fix significant digits display 
 * 3/29/2012 C.Birkett changed intersect option to use SESSION variable, then DispPhenotype will show trial only if it is in selected lines
 */
require $config['root_dir'] . 'includes/bootstrap.inc';
require $config['root_dir'] . 'theme/admin_header.php';
$mysqli = connecti();

/*******************************/ ?>

<div id="primaryContentContainer">
<div id="primaryContent">
<script type="text/javascript" src="theme/new.js"></script>
<?php

function dispCombinOpt()
{
    $count = count($_SESSION['selected_lines']);
    /*if POST varialble set then use this value, else if SESSION variable set then use this value, else default to replace*/
    $select_rep = "";
    $select_add = "";
    $select_yes = "";
    if ($_POST['selectWithin'] == "Replace") {
        $select_rep = "checked";
        $_SESSION['selectWithin'] = "Replace";
    } elseif ($_POST['selectWithin'] == "Add") {
        $select_add = "checked";
        $_SESSION['selectWithin'] = "Add";
    } elseif ($_POST['selectWithin'] == "Yes") {
        $select_yes = "checked";
        $_SESSION['selectWithin'] = "Yes";
    } elseif ($_SESSION['selectWithin'] == "Replace") {
        $select_rep = "checked";
    } elseif ($_SESSION['selectWithin'] == "Add") {
        $select_add = "checked";
    } elseif ($_SESSION['selectWithin'] == "Yes") {
        $select_yes = "checked";
    } else {
        $select_rep = "checked";
    }
    ?>
    Combine with <?php echo $count; ?> <font color=blue>currently selected lines</font>:<br>
    <input type="radio" name="selectWithin" value="Replace" <?php echo $select_rep; ?> onclick="this.form.submit();"/>Replace<br>
    <input type="radio" name="selectWithin" value="Add" <?php echo $select_add; ?> onclick="this.form.submit();"/>Add (OR)<br>
    <input type="radio" name="selectWithin" value="Yes" <?php echo $select_yes; ?> onclick="this.form.submit();"/>Intersect (AND)<br><br>
    <?php
}

// Create temporary directory if necessary.
if (! file_exists('/tmp/tht')) {
     mkdir('/tmp/tht');
}

if (isset($_POST['deselLines'])) {
  $selected_lines = $_SESSION['selected_lines'];
  foreach ($_POST['deselLines'] as $line_uid) {
    if (($lineidx = array_search($line_uid, $selected_lines)) !== false) {
      array_splice($selected_lines, $lineidx,1);
    }
  }
  $_SESSION['selected_lines']=$selected_lines;
}

  if (isset($_POST['selectWithin']) && ($_SESSION['selectWithin'] != $_POST['selectWithin'])) { //change in combine selection, no form submitted
    $_SESSION['selectWithin'] = $_POST['selectWithin'];
  } elseif(isset($_POST['phenotypecategory']) || isset($_GET['phenotype'])) {	//form has been submitted

    /* Deal with sorting */
	if(isset($_GET['sortby']) && isset($_GET['sorttype'])) {
		if($_GET['sortby'] == "value")	//make sure we're sorting correctly here.
			$_GET['sortby']  = "CAST(value AS DECIMAL(10,4))";

		$order = "ORDER BY " . $_GET['sortby'] . " " . $_GET['sorttype'];
	}
	else
		$order = "ORDER BY CAST(value AS DECIMAL(10,4)) DESC";

	/* Check for valid input */
	$phenotype = $_REQUEST['phenotype'];
	if($phenotype == "") {
		error(1, "No Phenotype Selected");
		die();
	}

	// Limit the queries for histogram and table to the specified trials.
	$in_these_trials = "";
	if(isset($_REQUEST['trial']) && $_REQUEST['trial'] != "") {
	  // Can't pass the array 'trial' via GET, so convert to comma-delimited string.
	  $triallist = implode(",", $_REQUEST['trial']);
	  $_GET['triallist'] = $triallist;
	  $in_these_trials = "AND e.experiment_uid IN (" . $triallist . ")";
	}
	else if(isset($_REQUEST['triallist']) && $_REQUEST['triallist'] != "") {
	  $in_these_trials = "AND e.experiment_uid IN (" . $_REQUEST['triallist'] . ")";
	}
        // DLH R plotting for histogram
        $phen_name = mysqli_query($mysqli, "select phenotypes_name,unit_name from phenotypes,units where phenotype_uid = $phenotype
                                        AND units.unit_uid = phenotypes.unit_uid;");
        $pname = mysqli_fetch_row($phen_name);
        $hist_query = mysqli_query($mysqli, "
	  select value from phenotype_data as pd, experiments as e, tht_base
	  where phenotype_uid = $phenotype 
	  and tht_base.tht_base_uid = pd.tht_base_uid
	  and e.experiment_uid = tht_base.experiment_uid
	  $in_these_trials"
				  ) or die(mysqli_error($mysqli));
        $x = 'x <- c(';
        while($row = mysqli_fetch_row($hist_query)) {
	  $x .= "$row[0],";
        }
        $x = trim($x, ",");
        $x .= ")";
        $date = date("Uu");
        /* $out = "jpeg(\\\"".$config['root_dir']."downloads/temp/bighistogram.jpg\\\", width=444, height=333)"; */
        $out = "jpeg(\\\"/tmp/tht/bighistogram.jpg\\\", width=444, height=333)";
        $title = "main='Histogram for " . $pname[0] . "'";
	$xlab = "xlab='" . html_entity_decode($pname[1]) . "'";
	//$xlab "xlab='" . $pname[1] . "'";
        $rcmd = "hist(x,$title,$xlab)";
        exec("echo \"$x;$out;$rcmd\" | R --vanilla");
        echo "<img src=\"/tmp/tht/bighistogram.jpg?d=$date\">\n";
	//

	// Get units.
	$unit = mysql_grab("select unit_name from units, phenotypes
	 where phenotypes.phenotype_uid = $phenotype 
	 and units.unit_uid=phenotypes.unit_uid");
	// Show mean, std. dev., and number of entries
	$meanquery = mysqli_query($mysqli, "
select avg(value) as avg,
       stddev_samp(value) as std,
       count(value) as num
from phenotypes, phenotype_data, tht_base, experiments as e
where phenotypes.phenotype_uid = $phenotype
and tht_base.experiment_uid = e.experiment_uid
and phenotype_data.tht_base_uid = tht_base.tht_base_uid
and phenotype_data.phenotype_uid = phenotypes.phenotype_uid
$in_these_trials
") or die(mysqli_error($mysqli));
	$row = mysqli_fetch_assoc($meanquery);
	$avg = number_format($row['avg'],1);
	$std = number_format($row['std'],1);
	$num = $row['num'];
	echo "<br>Mean: <b>$avg</b> &plusmn; <b>$std</b> $unit<br>";
	echo "n = <b>$num</b><br><hr><p>";

	//setting for sort callback
	$_GET['phenotype'] = $phenotype;

	//deal with case 1, ranges
	if( ! isset($_REQUEST['na_value'])) {

		$first = !$_REQUEST['first_value'] ? getMaxMinPhenotype("min", $phenotype) : $_REQUEST['first_value'];
		$last = !$_REQUEST['last_value'] ? getMaxMinPhenotype("max", $phenotype) : $_REQUEST['last_value'];

		//setting for sort callback
		$_GET['first_value'] = $first;
		$_GET['last_value'] = $last;

		$searchVal = "BETWEEN $first AND $last";
		
	}
	else {	//deal with case 2, single value

		//setting for sort callback
		$_GET['na_value'] = $_REQUEST['na_value'];

		$searchVal = "REGEXP '". $_REQUEST['na_value'] ."'";
	}

	$_GET['selectWithin'] = $_REQUEST['selectWithin'];
	$in_these_lines = "";
	if((is_array($_SESSION['selected_lines'])) && (count($_SESSION['selected_lines']) > 0) && ($_REQUEST['selectWithin'] == "Yes") ) {
		$in_these_lines = "AND lr.line_record_uid IN (" . implode(",", $_SESSION['selected_lines']) . ")";
	}
	$query = "	SELECT lr.line_record_uid, lr.line_record_name Line, lr.breeding_program_code Breeding_Program, pd.value, e.trial_code Trial
				FROM line_records as lr, tht_base, phenotype_data as pd, phenotypes as p, experiments as e
				WHERE e.experiment_uid = tht_base.experiment_uid
					AND lr.line_record_uid = tht_base.line_record_uid
					AND tht_base.tht_base_uid = pd.tht_base_uid
					AND pd.value $searchVal
					AND pd.phenotype_uid = p.phenotype_uid
					AND p.phenotype_uid = '$phenotype'
					$in_these_lines
                                        $in_these_trials
				$order";
	$search = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));

	if ($_REQUEST['selectWithin'] != "Add") {
	  // selectWithin = Yes or Replace
	  $_SESSION['selected_lines'] = array(); // Empty the session array.
	}  
	while($row = mysqli_fetch_assoc($search)) {
		if(!in_array($row['line_record_uid'], $_SESSION['selected_lines']))
			array_push($_SESSION['selected_lines'], $row['line_record_uid']);
	}
	// Store the selected lines in the database.
	$username=$_SESSION['username'];
	if ($username)
	  store_session_variables('selected_lines', $username);

	$query = str_replace("SELECT lr.line_record_uid, ", "SELECT ", $query);
	$search = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));

	/* Get the number of significant digits for this unit. */
	$getsigdig = "SELECT sigdigits_display FROM units, phenotypes
			WHERE phenotypes.phenotype_uid = '$phenotype'
			AND units.unit_uid = phenotypes.unit_uid";
	$r = mysqli_query($mysqli, $getsigdig) or die(mysqli_error($mysqli));
	$sigdig = mysqli_fetch_row($r);
	$sigdig = (int) $sigdig[0];

	/* Display Result */

        ?>
        <script type="text/javascript">
          update_side_menu();
        </script> 
	<div class='box'><h2>Results</h2><div class='boxContent'>
        <?php

        if(mysqli_num_rows($search) > 0) {
	  echo displayTableSigdig($search, true, $sigdig);
	  echo "<form action='".$config['base_url']."dbtest/exportQueryResult.php' method='post'><input type='submit' value='Export to CSV' /><input type='hidden' name='query_string' value='" . urlencode($query) ."' /></form>";
	  //echo "<br /><form action='".$config['base_url']."pedigree/pedigree_markers.php'><input type='submit' value='View Common Marker Values' /></form>";
	}
	else
	  echo "<b>No records found.</b><br>";

	echo "<br></div></div>";
  }
?>

<div class="box">
    <h2>Select Lines by Phenotype</h2>

    <div id="phenotypeSel" class="boxContent">
    <h3> Select Phenotype and Trials</h3>
    <form action="<?php echo $config['base_url']; ?>phenotype/compare.php" method="post">
    <?php
    if (isset($_SESSION['selected_lines']) && count($_SESSION['selected_lines']) > 0) {
      dispCombinOpt();
    }
    ?>

    <table id="phenotypeSelTab" class="tableclass1">
    <thead>
    <tr>
    <th>Category</th>
    <th width=200px>Trait</th>
    <th width=200px>Trial</th>
    </tr>
    </thead>
    <tbody>
    <tr class="nohover">
    <td>
    <select name='phenotypecategory' size=10 onfocus="DispPhenoSel(this.value, 'Category')" onchange="DispPhenoSel(this.value, 'Category')">
    <?php
    $sql = "select distinct(phenotype_category.phenotype_category_uid), phenotype_category_name from phenotype_category, phenotypes
      where phenotype_category.phenotype_category_uid = phenotypes.phenotype_category_uid";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row = mysqli_fetch_array($res)) {
        echo "<option value=\"$row[0]\">$row[1]</option>\n";
    }
    //showTableOptions("phenotype_category"); ?>
</select>
</td>
<td><p>Select a phenotype category.</p>
</td>
<td>
</td>
</tr>

<tr><td style="text-align: left;">
<?php
// Test for currently selected lines.
$username=$_SESSION['username'];
if ($username && !isset($_SESSION['selected_lines'])) {
    $stored = retrieve_session_variables('selected_lines', $username);
    if (-1 != $stored) {
        $_SESSION['selected_lines'] = $stored;
    }
}
?>
</td>

<td></td><td height=220></td></tr>
</tbody>
</table>
</div>
<br><br>
</div>
<?php
if (isset($_SESSION['selected_lines']) && count($_SESSION['selected_lines']) > 0) {
    print "<div class='boxContent'>";
    $selectedcount = count($_SESSION['selected_lines']);
    echo "<h3><font color=blue>Currently selected lines</font>: $selectedcount</h3>";

    $display = $_SESSION['selected_lines'] ? "":" style='display: none;'";
    print "<form id=\"deselLinesForm\" action=\"".$_SERVER['PHP_SELF']."\" method=\"post\" $display>";
    print "<select name=\"deselLines[]\" multiple=\"multiple\" style=\"height: 12em;width: 16em\">";
    foreach ($_SESSION['selected_lines'] as $lineuid) {
        $result=mysqli_query($mysqli, "select line_record_name from line_records where line_record_uid=$lineuid")
            or die("invalid line uid\n");
        while ($row=mysqli_fetch_assoc($result)) {
            $selval=$row['line_record_name'];
            print "<option value=\"$lineuid\">$selval</option>\n";
        }
    }
    print "</select>";
    print "<p><input type='submit' value='Deselect highlighted lines'>";
    print "</form>";
    print "</div>";
}
?>
  </div>
  </div>
  </div>

<?php require $config['root_dir'] . 'theme/footer.php';
