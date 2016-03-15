<?php session_start();

//  2feb12 dem: Use new generalized tables, line_properties etc.
// 29mar12 dem: Show Line Info for the newly found lines too.
// 26mar12 dem: Improve layout of bottom box, Lines Found / Currently Selected.
// 12mar12 dem: Add wildcard '*' for name search.
// 2/14/2011 JLee  Fix to handle hector case
// 2/2/2011  JLee  Add ability to parse tab-delimited and comma separate line inputs
// 1/28/2011  JLee  Add ability to add multiple lines and synonym translation

require 'config.php';
require $config['root_dir'] . 'includes/bootstrap.inc';
$mysqli = connecti();
require $config['root_dir'] . 'theme/admin_header.php';

// Clear propvals cookie on initial entry, or if the last action was to save $_SESSION['selected_lines'].
if (empty($_POST) or $_POST['WhichBtn']) {
    unset($_SESSION['propvals']);
} else {
    // Store what the user's previous selections were so we can
    // redisplay them as the page is redrawn.
    $name = $_POST['LineSearchInput'];
    if (is_array($_POST['breedingprogramcode'])) {
        foreach ($_POST['breedingprogramcode'] as $key => $value) {
            $breeding[$value] = 'selected="selected"';
        }
    }
    if (is_array($_POST['year'])) {
        foreach ($_POST['year'] as $key => $value) {
            $yr[$value] = 'selected="selected"';
        }
    }
    if (is_array($_POST['species'])) {
        foreach ($_POST['species'] as $key => $value) {
            $species[$value] = 'selected="selected"';
        }
    }
    if (is_array($_SESSION['propvals'])) {
        // array of array(uid, name, value)
        $propvals = $_SESSION['propvals'];
    }
    if (is_array($_POST['panel'])) {
        foreach ($_POST['panel'] as $key => $value) {
            $panelselect[$value] = 'selected="selected"';
            $panel[] = $value;
        }
    }
}
?>

<style type="text/css">
  h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
  table tr {min-height: 0px;}
  table td {padding: 3px; /* border: none; */
    text-align: center; vertical-align: top;}
  table th {text-align: center;}
</style>
<script type="text/javascript" src="theme/new.js"></script>

<h2> Select Lines by Properties</h2>
<div class="boxContent">

  <table width="650px">
    <tr style="height:20px"><td style="text-align:left;">
	<h3>Passport data</h3>
      <td><td><td>
	<form id="searchLines" action="<?php echo $_SERVER['SCRIPT_NAME'] ?>" method="POST">
	  <!--  <input type=hidden name="search" value="yes"> -->
	  
	  <tr style="vertical-align: top">
	    <td><b>Name</b> <br>
	      <textarea name="LineSearchInput" rows="3" cols="18" style="height: 6em;">
<?php
$nm = explode('\r\n', $name);
foreach ($nm as $n) {
    if ($n) {
        echo $n."\n";
    }
}
?>
</textarea>
	      <br> E.g. Cayuga, tur*ey, iwa860*<br>
	      Synonyms will be translated.<br>
	    <td><b> Source </b> <br>
	      <select name="breedingprogramcode[]" multiple="multiple" size="6" style="width: 22em; height: 8em;">
    <?php
    $sql = "SELECT DISTINCT(l.breeding_program_code), c.data_program_name FROM line_records l, CAPdata_programs c WHERE l.breeding_program_code = c.data_program_code ORDER by l.breeding_program_code";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($resp = mysqli_fetch_assoc($res)) {
    ?>
	<option value="<?php echo $resp['breeding_program_code'] ?>" <?php echo $breeding[$resp['breeding_program_code']]?>><?php echo $resp['breeding_program_code'] ?><?php echo "--".$resp['data_program_name'] ?></option>
<?php
    }
?>
	      </select><br><br>
	    <td><b>Year</b><br>
	      <select name="year[]" multiple="multiple" size="6">
<?php
$sql = "select distinct experiment_year from experiments order by experiment_year DESC";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
while ($resp = mysqli_fetch_assoc($res)) {
    ?>
    <option value="<?php echo $resp['experiment_year'] ?>" <?php echo $yr[$resp['experiment_year']]?>> <?php echo $resp['experiment_year'] ?> </option>
    <?php
}
?>
	      </select><br><br></td>
	    <td> <b>Species</b> <br>
	      <select name="species[]" multiple="multiple" size="6" style="height: 8em;">
<?php
/* $sql = "SELECT DISTINCT(species) FROM line_records WHERE species NOT LIKE 'NULL' AND NOT species = ''"; */
$sql = "select pv.value from properties p, property_values pv
        where p.properties_uid = pv.property_uid and p.name = 'Species'";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
while ($resp = mysqli_fetch_row($res)) {
    $s = $resp[0];
    echo "<option value='$s' $species[$s]>$s</option>";
}
?>
  </select><br><br>
  </table>    

  <table width=650px>
    <tr><td style="text-align:left;">
	<h3>Genetic characters</h3>
	<table id="PropertySelTable" style="table-layout: fixed; width: 350px;">
	  <tr>
	    <th width=120px>Category
	    <th width=150px>Property/Gene
	    <th width=80px>Value/Allele
	  </tr>
	  <tr class="nohover">
	    <td>
              <!-- DispPropSel() in includes/core.js passes further activity to  -->
	      <!-- includes/ajaxlib.php functions DispPropCategorySel() etc. -->
	      <select size=5 
		      onfocus="DispPropSel(this.value, 'PropCategory')" 
		      onchange="DispPropSel(this.value, 'PropCategory')">
	        <?php showTableOptions("phenotype_category"); ?>
	      </select>
	    <td><p>Select a Category.</p>
	    <td>
	    <td>
	  </tr>
	  <!-- Empty row to display the resulting choices and show the previous ones. -->
	  <tr><td colspan=3>
<?php
if (!empty($propvals)) {
    foreach ($propvals as $pv) {
        echo "$pv[1] = $pv[2], ";
    }
}
?>
	  </tr>
	</table>
      <td style="padding-left:30px"><h3>Preselected line sets</h3>
	<table>
	  <tr style="vertical-align:top">
	    <th>Panel
	      <tr>
		<td><select name="panel[]" multiple="multiple" size="6" style="width: 12em; height: 88px;">
<?php
if (loginTest2()) {
    $row = loadUser($_SESSION['username']);
    $myid = $row['users_uid'];
    $sql = "SELECT linepanels_uid, name FROM linepanels where users_uid = $myid";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    if (mysqli_num_rows($res) > 0) {
        while ($resp = mysqli_fetch_row($res)) {
            $lpid = $resp[0];
            $s = $resp[1];
            echo "<option value='$lpid' $panelselect[$lpid]>$s</option>";
        }
        echo "<option disabled>Everybody's:</option>";
    }
}
$sql = "SELECT linepanels_uid, name FROM linepanels where users_uid IS NULL";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
while ($resp = mysqli_fetch_row($res)) {
    $lpid = $resp[0];
    $s = $resp[1];
    echo "<option value='$lpid' $panelselect[$lpid]>$s</option>";
}
?>
		</select>
	      </tr>
	</table>
  </table>
  <input type="submit" value="Search"/>
<?php
  $url = $_SERVER['PHP_SELF'];
  echo "<input type=button value='Clear' onclick='location.href=\"$url\"'>";
  echo "</form><p>";
echo "</div><div class='boxContent'><table ><tr><td>";

/* The Search */
if (!empty($_POST)) {
    $linenames = $_POST['LineSearchInput'];
    $breedingProgram = $_POST['breedingprogramcode'];
    $year = $_POST['year'];
    $species = $_POST['species'];
    // just the ids, property_values.property_values_uid
    if (!empty($propvals)) {
        foreach ($propvals as $pv) {
            $propvalids[] = $pv[0];
        }
    }
    $lineArr = array();
    $nonHits = array();

    // the Name box
    if (strlen($linenames) != 0)  {
      // Assume input is punctuated either with commas, tabs or linebreaks. Change to commas.
      $linenames = str_replace(array('\r\n', ', '), '\t', $linenames);
      $lineList = explode('\t', $linenames);
      foreach ($lineList as $word) {
	  $found = FALSE;
	  $word = str_replace('*', '%', $word);  // Handle "*" wildcards.
	  $word = str_replace('&amp;', '&', $word);  // Allow "&" character in line names.
	  // First check line_records.line_record_name.
          $sql = "SELECT line_record_name from line_records where line_record_name like ?";
                if ($stmt = mysqli_prepare($mysqli, $sql)) {
                    mysqli_stmt_bind_param($stmt, "s", $word);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_bind_result($stmt, $hits);
                    while (mysqli_stmt_fetch($stmt)) {
                        $linesFound[] = $hits;
			$found = TRUE;
                    }
                    mysqli_stmt_close($stmt);
                    /* if (isset($linesFound)) { */
                    /*     $found = true; */
                    /* } */
                }
	// Now check line_synonyms.line_synonym_name.
        $sql = "select line_record_name from line_synonyms ls, line_records lr where line_synonym_name like ? and ls.line_record_uid = lr.line_record_uid";
                if ($stmt = mysqli_prepare($mysqli, $sql)) {
                    mysqli_stmt_bind_param($stmt, "s", $word);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_bind_result($stmt, $hits);
                    while (mysqli_stmt_fetch($stmt)) {
                        $linesFound[] = $hits;
			$found = TRUE;
                    }
                    mysqli_stmt_close($stmt);
                    /* if (isset($linesFound)) { */
                    /*     $found = true; */
                    /* } */
                }
	if ($found === false) {
	  $nonHits[] = $word;
        }
      }
      // Generate the translated line names
      if (count($linesFound) > 0)
	$linenames = implode("','", $linesFound);
    } // end if (strlen($linenames) != 0)
    if (count($breedingProgram) != 0) {
        $tmp = implode("','", $breedingProgram);
        if (preg_match("/([A-Z,']+)/", $tmp, $match)) {
            $breedingCode = $match[1];
        } else {
            $breedingCode = "";
        }
    }
    if (count($species) != 0) {
        $tmp = implode("','", $species);
        if (preg_match("/([a-z,']+)/", $tmp, $match)) {
            $speciesStr = $match[1];
        } else {
            $speciesStr = "";
        }
    }
    if (count($year) != 0) {
        $tmp = implode("','", $year);
        if (preg_match("/([0-9,']+)/", $tmp, $match)) {
            $yearStr = $match[1];
        } else {
            $yearStr = "";
        }
    }

    /* Build the search string $where. */
    $count = 0;
    if (strlen($linenames) > 0)		{
      if ($count == 0)    	
    	$where .= "line_record_name in ('".$linenames."')";
      else    	
	$where .= " AND line_record_name in ('".$linenames."')";
      $count++;
    }
    if (count($breedingProgram) != 0)    {
      if ($count == 0)        
	$where .= "breeding_program_code IN ('".$breedingCode."')";
      else	
	$where .= " AND breeding_program_code IN ('".$breedingCode."')";
      $count++;
    }
    if (count($year) != 0)      {
      if ($count == 0)    	
	$where .= "line_record_uid IN (select line_record_uid from tht_base, experiments
where experiment_year IN ('".$yearStr."') and tht_base.experiment_uid = experiments.experiment_uid)";
      else	  
	$where .= " AND line_record_uid IN (select line_record_uid from tht_base, experiments 
where experiment_year IN ('".$yearStr."') and tht_base.experiment_uid = experiments.experiment_uid)";
      $count++;
    }
    if (count($species) != 0)    {
      // Include as a Property.
      foreach ($species as $spcs) {
        if ($stmt = mysqli_prepare($mysqli, "select property_values_uid from property_values where value = ?")) {
            mysqli_stmt_bind_param($stmt, "s", $spcs);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $pvid);
            mysqli_stmt_fetch($stmt);
            $propvalids[] = $pvid;
            mysqli_stmt_close($stmt);
        }
      }
    }
    if (count($propvalids) != 0)    {
      foreach ($propvalids as $pvid) {
	if ($count == 0)   
	  $where .= "line_record_uid IN (select line_record_uid from line_properties where property_value_uid = $pvid)";
	else    	
	  $where .= " AND line_record_uid IN (select line_record_uid from line_properties where property_value_uid = $pvid)";
	$count++;
      }
    }
    if (count($panel) != 0)    {
      $sql = "select line_ids from linepanels where linepanels_uid = ?";
      foreach($panel as $p) {
        if ($stmt = mysqli_prepare($mysqli, $sql)) {
          mysqli_stmt_bind_param($stmt, "i", $p);
          mysqli_stmt_execute($stmt);
          mysqli_stmt_bind_result($stmt, $uid);
          mysqli_stmt_fetch($stmt);
          $idlist .= "$uid,";
          mysqli_stmt_close($stmt);
        }
      }
      $idlist = rtrim($idlist, ',');
      if ($count == 0)    	
    	$where .= "line_record_uid IN ($idlist)";
      else    	
	$where .= " AND line_record_uid IN ($idlist)";
      $count++;
    }

    /* Do The Search */
    if ( (strlen($linenames) == 0)
	 AND (count($breedingProgram) == 0)
	 AND (count($year) == 0)
	 AND (count($species) == 0)
         AND (count($propvalids) == 0)
         AND (count($panel) == 0))
      $linesfound = 0;
    else  {
      $TheQuery = "select line_record_uid, line_record_name from line_records where $where";
      $result=mysqli_query($mysqli, $TheQuery) or die(mysqli_error($mysqli)."<br>Query was:<br>".$TheQuery);
      $linesfound = mysqli_num_rows($result);
    }

    /* Search Results: */
    /* echo "</div><div class='boxContent'><table width=500px><tr><td>"; */
    echo "<form name='lines' action=".$_SERVER['PHP_SELF']." method='post'>";
    // Show failures from the Name box that don't match any line names.
    foreach ($nonHits as $i) 
      if ($i != '') echo "<font color=red><b>Line \"$i\" not found.</font></b><br>";
    print "<b>Lines found: $linesfound </b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

    /* If any hits. */
    if ($linesfound > 0) {
      if (!isset($_SESSION['selected_lines']) OR count($_SESSION['selected_lines']) == 0) 
	echo " <input type='submit' name='WhichBtn' value='Add to Selected' style='color:blue; font-size:9pt'>";
      print "<br><select name='selLines[]' multiple='multiple' style='height: 17em; width: 13em'>";
      $_SESSION['linesfound'] = array();
      while ($row = mysqli_fetch_assoc($result)) {
	$line_record_name = $row['line_record_name'];
	$line_record_uid = $row['line_record_uid'];
	echo "<option value='$line_record_uid' selected>$line_record_name</option>";
	$_SESSION['linesfound'][] = $line_record_uid;
      }
      print "</select><br>";
      print "<button type='button' onclick=\"location.href='".$config['base_url']."pedigree/pedigree_info.php?lf=yes'\">Show line information</button>";

      // If any Currently Selected, offer to combine.
      if (isset($_SESSION['selected_lines']) AND count($_SESSION['selected_lines']) != 0) {   
	?>
	<td style="width: 130px; padding: 8px">Combine with <font color=blue>currently<br>selected lines</font>:<br>
	  <input type="radio" name="selectWithin" value="Replace" checked>Replace<br>
	  <input type="radio" name="selectWithin" value="Add">Add (OR)<br>
	  <input type="radio" name="selectWithin" value="Yes">Intersect (AND)<br>
	  <input type="submit" name='WhichBtn' value="Combine" style='color:blue'></td>
	  <?php 
	  } // end if(isset($_SESSION['selected_lines'])...
    } // end if ($linesfound > 0)
    print "</form>";
  } // end if(!empty($_POST))

// Combine found lines with cookie, REPLACE/AND/OR.
$verify_selected_lines = $_POST['selLines'];
$verify_session = $_SESSION['selected_lines'];
if (count($verify_selected_lines)!=0 OR count($verify_session)!=0) {
  //  echo "</div><div class='boxContent'>";
  if (isset($_POST['selLines'])) {  
    if ($_POST['selectWithin'] == "Replace") 
      $_SESSION['selected_lines'] = $_POST['selLines'];
    elseif ($_POST['selectWithin'] == "Yes")
      $_SESSION['selected_lines'] = array_intersect($_SESSION['selected_lines'], $_POST['selLines']);
    else {  // Add.
      $selLines = $_POST['selLines'];
      $selected_lines = $_SESSION['selected_lines'];
      if (!isset($selected_lines))
	$selected_lines = array();
      foreach($selLines as $line_uid) {
	if (!in_array($line_uid, $selected_lines)) 
	  array_push($selected_lines, $line_uid);
      }
      $_SESSION['selected_lines'] = $selected_lines;
    }
    ?>
    <script type="text/javascript">
       update_side_menu();
    </script>
	<?php
	}
  // Deselect highlighted cookie lines.
  if (isset($_POST['deselLines'])) {
    $selected_lines = $_SESSION['selected_lines'];
    foreach ($_POST['deselLines'] as $line_uid) 
      if (($lineidx = array_search($line_uid, $selected_lines)) !== false) 
	array_splice($selected_lines, $lineidx,1);
    $_SESSION['selected_lines']=$selected_lines;
  }
  // If logged in, retrieve cookie selection from database.
  $username=$_SESSION['username'];
  if ($username && !isset($_SESSION['selected_lines'])) {
    $stored = retrieve_session_variables('selected_lines', $username);
    if (-1 != $stored)
      $_SESSION['selected_lines'] = $stored;
    ?>
    <script type="text/javascript">
       update_side_menu();
    </script>
	<?php
	}
  // Show "Currently selected lines" box.
  $selectedcount = count($_SESSION['selected_lines']);
  $display = $_SESSION['selected_lines'] ? "":" style='display: none;'";
  echo "<div id='squeeze' $display>";
  echo "<td><b><font color=blue>Currently selected lines</font>: $selectedcount</b>";
  //print "<form id=\"deselLinesForm\" action=\"".$_SERVER['PHP_SELF']."\" method=\"post\" $display>";
  print "<form id=\"deselLinesForm\" action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">";
  print "<select name=\"deselLines[]\" multiple=\"multiple\" style=\"height: 15em;width: 13em\">";
  foreach ($_SESSION['selected_lines'] as $lineuid) {
    $result=mysqli_query($mysqli, "select line_record_name from line_records where line_record_uid=$lineuid") or die("invalid line uid\n");
    while ($row=mysqli_fetch_assoc($result)) {
      $selval=$row['line_record_name'];
      print "<option value=\"$lineuid\" selected>$selval</option>\n";
    }
  }
  print "</select>";
  print "<br><input type='submit' name='WhichBtn' value='Deselect highlighted lines' />";
  print "</form>";
	
  $display1 = $_SESSION['selected_lines'] ? "":" style='display: none;'";	
  /* print "<form id='showPedigreeInfo' action='pedigree/pedigree_info.php' method='post' $display1>"; */
  /* print "<input type='submit' name='WhichBtn' value='Show line information'></form>"; */
  print "<button onclick=\"location.href='".$config['base_url']."pedigree/pedigree_info.php'\">Show line information</button>";
  print "</div>";  // id=squeeze
  print "</table>";
  // Store the selected lines in the database.
  if ($username)
    store_session_variables('selected_lines', $username);
}
print "</table>";
print "</div></div>";
require $config['root_dir'] . 'theme/footer.php';
?>
