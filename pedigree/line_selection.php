<?php session_start();

// 29mar12 dem: Show Line Info for the newly found lines too.
// 26mar12 dem: Improve layout of bottom box, Lines Found / Currently Selected.
// 12mar12 dem: Add wildcard '*' for name search.
// 2/14/2011 JLee  Fix to handle hector case
// 2/2/2011  JLee  Add ability to parse tab-delimited and comma separate line inputs
// 1/28/2011  JLee  Add ability to add multiple lines and synonym translation

require 'config.php';
include $config['root_dir'] . 'includes/bootstrap.inc';
$mysqli = connecti();
include $config['root_dir'] . 'theme/admin_header.php';

if($_SERVER['REQUEST_METHOD'] == "POST") {
    // Store what the user's previous selections were so we can
    // redisplay them as the page is redrawn.
    $name = $_POST['LineSearchInput'];
    $hardness = $_POST['hardness'];
    $color = $_POST['color'];
    $awned =  $_POST['awned'];
    $hardSelected[$hardness] = 'checked="checked"';
    $colorSelected[$color] = 'checked="checked"';
    $awnSelected[$awned] = 'checked="checked"';
    if(is_array($_POST['breedingprogramcode'])) 
      foreach ($_POST['breedingprogramcode'] as $key => $value) 
        $breeding[$value] = 'selected="selected"';
    if(is_array($_POST['species'])) 
      foreach ($_POST['species'] as $key => $value) 
        $species[$value] = 'selected="selected"';
    if(is_array($_POST['panel'])) 
      foreach ($_POST['panel'] as $key => $value) {
        $panelselect[$value] = 'selected="selected"';
        $panel[] = $value;
      }
  if(is_array($_POST['growthhabit'])) 
    foreach ($_POST['growthhabit'] as $key => $value) 
      $growth[$value] = 'selected="selected"';
  if(is_array($_POST['year'])) 
    foreach ($_POST['year'] as $key => $value) 
      $yr[$value] = 'selected="selected"';
}
?>

<style type="text/css">
  table th {background: #5B53A6 !important; color: white !important; text-align: left; padding: 3px;}
  <!-- h3 {border-left: 4px solid #5B53A6; padding-left: .5em;} -->
  table tr {min-height: 0px;}
  table td {padding: 3px;}
</style>

<div id="primaryContentContainer">
  <div id="primaryContent">
  <script type="text/javascript" src="theme/new.js"></script>
  <h2> Select Lines by Properties</h2>
  <div class="boxContent">
  <h3> Select any combination of properties </h3>
  <table width="650px">
  <form id="searchLines" action="<?php echo $_SERVER['SCRIPT_NAME'] ?>" method="POST">
  <input type=hidden name="search" value="yes">
  
  <tr><td><b>Name</b> <br/>
  <textarea name="LineSearchInput" rows="3" cols="18" style="height: 6em;"><?php $nm = explode('\r\n', $name); foreach ($nm as $n) echo $n."\n"; ?></textarea>
  <br> E.g. Cayuga, tur*ey, iwa860*<br>
  Synonyms will be translated.<br></td>
  <td colspan=2><b> Data program </b> <br/>
  <select name="breedingprogramcode[]" multiple="multiple" size="6" style="width: 22em; height: 8em;">
<?php 
$sql = "SELECT DISTINCT(l.breeding_program_code), c.data_program_name FROM line_records l, CAPdata_programs c WHERE l.breeding_program_code = c.data_program_code ";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
while ($resp = mysqli_fetch_assoc($res)) {
?>
	<option value="<?php echo $resp['breeding_program_code'] ?>" <?php echo $breeding[$resp['breeding_program_code']]?>><?php echo $resp['breeding_program_code'] ?><?php echo "--".$resp['data_program_name'] ?></option>
<?php
	  }
?>
      </select><br/><br/></td>
<td><b>Trial Year</b><br>
<select name="year[]" multiple="multiple" size="6">
<?php
$sql = "select distinct experiment_year from experiments order by experiment_year DESC";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
while ($resp = mysqli_fetch_assoc($res))
{
  ?>
  <option value="<?php echo $resp['experiment_year'] ?>" <?php echo $yr[$resp['experiment_year']]?>> <?php echo $resp['experiment_year'] ?> </option>
<?php
    }
?>
       </select><br><br></td>
      <td> <b>Species</b> <br/>
      <select name="species[]" multiple="multiple" size="6" style="height: 8em;">
      <?php
      $sql = "SELECT DISTINCT(species) FROM line_records WHERE species NOT LIKE 'NULL' AND NOT species = ''";
      $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
      while ($resp = mysqli_fetch_row($res)) {
	$s = $resp[0];
 	echo "<option value='$s' $species[$s]>$s</option>";
      }
      ?>
      </select><br/><br/></td></tr>

      <tr style="vertical-align:top">
      <td><b>Panel</b> <br/>
      <select name="panel[]" multiple="multiple" size="6" style="width: 12em; height: 6em;">
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
      </select></td>
      <td><b>Hardness</b> <br/>
      <input type="radio" name="hardness" value="H" <?php echo $hardSelected['H'] ?>/> Hard<br>
      <input type="radio" name="hardness" value="S" <?php echo $hardSelected['S'] ?>/> Soft<br><br></td>
      <td><b>Color</b> <br/>
      <input type="radio" name="color" value="R" <?php echo $colorSelected['R']?>/> Red<br>
      <input type="radio" name="color" value="W" <?php echo $colorSelected['W']?>/> White<br><br></td>

      <td><b>Growth habit </b> <br/>
      <select name="growthhabit[]" multiple="multiple" size="4" style="width: 3em;height: 5em;">
<?php
      $sql = "SELECT DISTINCT(growth_habit) FROM line_records WHERE growth_habit NOT LIKE 'NULL' AND NOT growth_habit = ''";
      $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
      while ($resp = mysqli_fetch_assoc($res))
	{
?>
	  <option value="<?php echo $resp['growth_habit'] ?>" <?php echo $growth[$resp['growth_habit']]?>><?php echo $resp['growth_habit'] ?></option>
<?php
	    }
?>
      </select>
      <br><br></td>
      <td><b>Awns</b> <br/>
      <input type="radio" name="awned" value="A" <?php echo $awnSelected['A']?>/> Awned<br>
      <input type="radio" name="awned" value="N" <?php echo $awnSelected['N']?>/> Awnless<br><br>
      </td></tr>
      </table>

      <p><input type="submit" value="Search"/>
<?php
  $url = $config['base_url']."pedigree/line_selection.php";
  echo "<input type=button value='Clear' onclick='location.href=\"$url\"'>";
  echo "</form><p>";

  /* The Search */
  if (isset($_POST)) {
    $linenames = $_POST['LineSearchInput'];
    $breedingProgram = $_POST['breedingprogramcode'];
    $year = $_POST['year'];
    $species = $_POST['species'];
    $hardness = $_POST['hardness'];
    $color = $_POST['color'];
    $growthHabit = $_POST['growthhabit'];
    $awned =  $_POST['awned'];
    $lineArr = array();
    $nonHits = array();

    // the Name box
    if (strlen($linenames) != 0)  {
      // Assume input is punctuated either with commas, tabs or linebreaks.
      // Change to commas.
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
                    }
                    mysqli_stmt_close($stmt);
                    if (isset($linesFound)) {
                        $found = true;
                    }
                }
	// Now check line_synonyms.line_synonym_name.
        $sql = "select line_record_name from line_synonyms ls, line_records lr where line_synonym_name like ? and ls.line_record_uid = lr.line_record_uid";
                if ($stmt = mysqli_prepare($mysqli, $sql)) {
                    mysqli_stmt_bind_param($stmt, "s", $word);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_bind_result($stmt, $hits);
                    while (mysqli_stmt_fetch($stmt)) {
                        $linesFound[] = $hits;
                    }
                    mysqli_stmt_close($stmt);
                    if (isset($linesFound)) {
                        $found = true;
                    }
                }
	if ($found === false) {
	  $nonHits[] = $word;
        }
      }
      // Generate the translated line names
      if (count($linesFound) > 0)
	$linenames = implode("','", $linesFound);
    }

    if (count($breedingProgram) != 0) $breedingCode = implode("','", $breedingProgram);
    if (count($growthHabit) != 0) $growthStr = implode("','", $growthHabit);
    if (count($species) != 0) $speciesStr = implode("','", $species);
    if (count($year) != 0) $yearStr = implode("','", $year);

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
      if ($count == 0)    	
    	$where .= "species IN ('".$speciesStr."')";
      else    	
	$where .= " AND species IN ('".$speciesStr."')";
      $count++;
    }
    if (count($panel) != 0)    {
      foreach($panel as $p) 
	$idlist .= mysql_grab("select line_ids from linepanels where linepanels_uid = $p") . ",";
      $idlist = trim($idlist, ',');
      if ($count == 0)    	
    	$where .= "line_record_uid IN ($idlist)";
      else    	
	$where .= " AND line_record_uid IN ($idlist)";
      $count++;
    }
    if (strlen($hardness) != 0)    {
      if ($count == 0)    	
	$where .= "hardness IN ('".$hardness."')";
      else			
	$where .= " AND hardness IN ('".$hardness."')";
      $count++;
    }
    if (strlen($color) != 0)    {
      if ($count == 0)    	
	$where .= "color IN ('".$color."')";
      else			
	$where .= " AND color IN ('".$color."')";
      $count++;
    }
    if (count($growthHabit) != 0)  {
      if ($count == 0)    	
	$where .= "growth_habit IN ('".$growthStr."')";
      else			
	$where .= " AND growth_habit IN ('".$growthStr."')";
      $count++;
    }
    if (strlen($awned) != 0)    {
      if ($count == 0)    	
	$where .= "awned IN ('".$awned."')";
      else			
	$where .= " AND awned IN ('".$awned."')";
      $count++;
    }

    if ($_POST['search'] == "yes") {
    /* Do The Search */
    echo "</div><div class='boxContent'><table><tr><td>";
    if ( (strlen($linenames) == 0)
	 AND (count($breedingProgram) == 0)
	 AND (count($year) == 0)
	 AND (count($species) == 0)
	 AND (strlen($hardness) == 0)
	 AND (strlen($color) == 0)
	 AND (count($growthHabit) == 0)
	 AND (strlen($awned) == 0) 
         AND (count($panel) == 0))
      $result = mysqli_query($mysqli, "SELECT line_record_name FROM line_records where line_record_name = NULL");
    else  {
      $TheQuery = "select line_record_uid, line_record_name from line_records where $where";
      $result=mysqli_query($mysqli, $TheQuery) or die(mysqli_error($mysqli)."<br>Query was:<br>".$TheQuery);
    }
    $linesfound = mysqli_num_rows($result);

    // Show failures from the Name box that don't match any line names.
    foreach ($nonHits as $i) 
      if ($i != '') echo "<font color=red><b>Line \"$i\" not found.</font></b><br>";

    // Results:
    echo "<form name='lines' action=".$_SERVER['PHP_SELF']." method='post'>";
    print "<b>Lines found: $linesfound </b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    if (!isset($_SESSION['selected_lines']) OR count($_SESSION['selected_lines']) == 0) 
      echo " <input type='submit' name='WhichBtn' value='Add to Selected' style='color:blue; font-size:9pt'>";
    print "<br><select name='selLines[]' multiple='multiple' style='height: 17em; width: 13em'>";
    if ($linesfound > 0) {
      $_SESSION['linesfound'] = array();
      while($row = mysql_fetch_assoc($result)) {
	$line_record_name = $row['line_record_name'];
	$line_record_uid = $row['line_record_uid'];
	echo "<option value='$line_record_uid' selected>$line_record_name</option>";
	$_SESSION['linesfound'][] = $line_record_uid;
      }
    }
    print "</select><br>";
    print "<button type='button' onclick=\"location.href='".$config['base_url']."pedigree/pedigree_info.php?lf=yes'\">Show line information</button>";
    }  // end if($_POST[search]=yes)

    if (isset($_SESSION['selected_lines']) AND count($_SESSION['selected_lines']) != 0) {   
      if ($_POST['search'] == "yes") {
?>
      </td><td style="width: 130px; padding: 8px">Combine with <font color=blue>currently<br>selected lines</font>:<br>
      <input type="radio" name="selectWithin" value="Replace" checked>Replace<br>
      <input type="radio" name="selectWithin" value="Add">Add (OR)<br>
      <input type="radio" name="selectWithin" value="Yes">Intersect (AND)<br>
      <input type="submit" name='WhichBtn' value="Combine" style='color:blue'></td>
<?php 
      }
    } // end if(SESSION[selectedlines])
    else
      print "</table>";
    print "</form>";
  } // end if(isset($_POST))

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
    $result=mysql_query("select line_record_name from line_records where line_record_uid=$lineuid") or die("invalid line uid\n");
    while ($row=mysql_fetch_assoc($result)) {
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
  echo "</div>";  // id=squeeze
  print "</td></tr></table>";
  // Store the selected lines in the database.
  if ($username)
    store_session_variables('selected_lines', $username);
}
print "</div>";
print "</div>";
print "</div>";
print "</div>";

require $config['root_dir'] . 'theme/footer.php';
?>
