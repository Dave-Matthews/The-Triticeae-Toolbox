<?php session_start();

// 2/2/2011  JLee  Add ability to parse tab-delimited and comma separate line inputs
// 1/28/2011  JLee  Add ability to add multiple lines and synonym translation

require 'config.php';
include($config['root_dir'] . 'includes/bootstrap.inc');
connect();
include($config['root_dir'] . 'theme/admin_header.php');

if($_SERVER['REQUEST_METHOD'] == "POST")
  // Store what the user's previous selections were so we can
  // redisplay them as the page is redrawn.
  {
    $name = $_POST['LineSearchInput'];
    $hardness = $_POST['hardness'];
    $color = $_POST['color'];
    $awned =  $_POST['awned'];
    $hardSelected[$hardness] = 'checked="checked"';
    $colorSelected[$color] = 'checked="checked"';
    $awnSelected[$awned] = 'checked="checked"';
    if(is_array($_POST['breedingprogramcode'])) {
	foreach ($_POST['breedingprogramcode'] as $key => $value) {
	    $breeding[$value] = 'selected="selected"';
	}
    }  
    if(is_array($_POST['species'])) {
	foreach ($_POST['species'] as $key => $value) {
	    $species[$value] = 'selected="selected"';
	}
    }  
    if(is_array($_POST['growthhabit'])) {
	foreach ($_POST['growthhabit'] as $key => $value) {
	  $growth[$value] = 'selected="selected"';
	}
    } 
    if(is_array($_POST['year'])) {
      foreach ($_POST['year'] as $key => $value) {
	$yr[$value] = 'selected="selected"';
      }
    } 
  }
?>

<script type="text/javascript">
//var test = new Array("<?/*php echo $selLines*/?>");
//test1 =  test.length;
// Select All
  function exclude_all() {
  count = document.lines.elements.length;
  for (i=0; i < count; i++) 
    {
      if(document.lines.elements[i].checked == 0)
    	{document.lines.elements[i].checked = 1; }
    }
  document.lines.btn1.checked = "checked";                     
}

function exclude_none()
{
  count = document.lines.elements.length;
  for (i=0; i < count; i++) 
    {
      if(document.lines.elements[i].checked == 1)
    	{document.lines.elements[i].checked = 0; }
    }
}
</script>

<style type="text/css">
  th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
  // h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
</style>


<div id="primaryContentContainer">
  <div id="primaryContent">
  <h2> Select Lines by Properties</h2>
  <div class="boxContent">
  <h3> Select any combination of properties </h3>
  <table width="650px">
  <form id="searchLines" action="<?php echo $_SERVER['SCRIPT_NAME'] ?>" method="POST">
  
      <tr> <td>
      <b>Name</b> <br/><br/>
      <textarea name="LineSearchInput" rows="3" cols="20" style="height: 6em;"><?php $nm = explode('\r\n', $name); foreach ($nm as $n) echo $n."\n"; ?></textarea>
      <br> Eg: Cayuga, Doyce<br>
      Synonyms will be translated.
      <br></td>

      <td> 
      <b> Data program </b> <br/><br/>
      <select name="breedingprogramcode[]" multiple="multiple" size="6" style="width: 12em height: 12em;">
      <?php 
      $sql = "SELECT DISTINCT(l.breeding_program_code), c.data_program_name FROM line_records l, CAPdata_programs c WHERE l.breeding_program_code = c.data_program_code ";
      $res = mysql_query($sql) or die(mysql_error());
      while ($resp = mysql_fetch_assoc($res)) {
	?>
	<option value="<?php echo $resp['breeding_program_code'] ?>" <?php echo $breeding[$resp['breeding_program_code']]?>><?php echo $resp['breeding_program_code'] ?><?php echo "--".$resp['data_program_name'] ?></option>
	  <?php
	  }
      ?>
      </select><br/><br/>
      </td>

<td><b>Year</b><br><br>
<select name="year[]" multiple="multiple" size="6">
<?php
$sql = "select distinct experiment_year from experiments";
		$res = mysql_query($sql) or die(mysql_error());
		while ($resp = mysql_fetch_assoc($res))
		{
		  ?>
		  <option value="<?php echo $resp['experiment_year'] ?>" <?php echo $yr[$resp['experiment_year']]?>> <?php echo $resp['experiment_year'] ?> </option>
<?php
		    }
?>
</select>
<br><br></td>

      <td> <b>Species</b> <br/><br/>
      <select name="species[]" multiple="multiple" size="6" style="width: 12em height: 12em;">
      <?php
      $sql = "SELECT DISTINCT(species) FROM line_records WHERE species NOT LIKE 'NULL' AND NOT species = ''";
      $res = mysql_query($sql) or die(mysql_error());
      while ($resp = mysql_fetch_row($res)) {
	$s = $resp[0];
 	echo "<option value='$s' $species[$s]>$s</option>";
      }
      ?>
      </select><br/><br/>
      </td>
      </tr>

      <tr>
      <td>
      <b>Hardness</b> <br/><br/>
      <input type="radio" name="hardness" value="H" <?php echo $hardSelected['H'] ?>/>Hard<br>
      <input type="radio" name="hardness" value="S" <?php echo $hardSelected['S'] ?>/>&nbsp;&nbsp;Soft<br><br>
      </td>
      <td>
      <b>Color</b> <br/><br/>
      <input type="radio" name="color" value="R" <?php echo $colorSelected['R']?>/>&nbsp;&nbsp;Red<br>
      <input type="radio" name="color" value="W" <?php echo $colorSelected['W']?>/>White<br><br>
      </td>

      <td>
      <b>Growth habit </b> <br/> <br/>
      <select name="growthhabit[]" multiple="multiple" size="4" style="width: 3em;height: 5em;">
      <?php
      $sql = "SELECT DISTINCT(growth_habit) FROM line_records WHERE growth_habit NOT LIKE 'NULL' AND NOT growth_habit = ''";
      $res = mysql_query($sql) or die(mysql_error());
      //$count = 1;
      while ($resp = mysql_fetch_assoc($res))
	{
	  ?>
	  <option value="<?php echo $resp['growth_habit'] ?>" <?php echo $growth[$resp['growth_habit']]?>><?php echo $resp['growth_habit'] ?></option>
	    <?php
	    }
      ?>
      </select>
      <br><br>	
      </td>

      <td>
      <b>Awns</b> <br/><br/>
      <input type="radio" name="awned" value="A" <?php echo $awnSelected['A']?>/>&nbsp;&nbsp;&nbsp;Awned<br>
      <input type="radio" name="awned" value="N" <?php echo $awnSelected['N']?>/>Awnless<br><br>
      </td>

      </tr>
      </table>

      <p><input type="submit" value="Search"/>
      <?php
      $url = $config['base_url']."pedigree/line_selection.php";
      echo "<input type=button value='Clear' onclick='location.href=\"$url\"'>";
      ?>
      </form>
      </div>

      <?php 
      /* The Search */
  if (isset($_POST['LineSearchInput'])) {
    $linenames = $_POST['LineSearchInput'];
    $breedingProgram = $_POST['breedingprogramcode'];
    $year = $_POST['year'];
    $species = $_POST['species'];
    $hardness = $_POST['hardness'];
    $color = $_POST['color'];
    $growthHabit = $_POST['growthhabit'];
    $awned =  $_POST['awned'];
    $lineArr = array();

    // Translate synonym
    if (strlen($linenames) != 0) {
      if (strpos($linenames, ',') > 0 ) {
	$linenames = str_replace(", ",",", $linenames);	
	$lineList = explode(',',$linenames);
      } elseif (preg_match("/\t/", $linenames)) {
	$lineList = explode("\t",$linenames);
      } else {
	$lineList = explode('\r\n',$linenames);
      }
      $items = implode("','", $lineList);
      $mStatment = "SELECT distinct (lr.line_record_name) FROM line_records lr left join line_synonyms ls on ls.line_record_uid = lr.line_record_uid where ls.line_synonym_name in ('" .$items. "') or lr.line_record_name in ('". $items. "');";
      $res = mysql_query($mStatment) or die(mysql_error());

      if (mysql_num_rows($res) != 0) {         
	while($myRow = mysql_fetch_assoc($res)) {
	  array_push ($lineArr,$myRow['line_record_name']);
	}  
	// Generate the translated line names
	$linenames =  implode("','", $lineArr);         
      } else {
	$linenames = ''; 
      }

        // Find any none hit items 
        $mStatment = "SELECT distinct (ls.line_synonym_name) FROM line_synonyms ls where ls.line_synonym_name in ('" .$items. "');";
        $res = mysql_query($mStatment) or die(mysql_error());
        while($myRow = mysql_fetch_assoc($res)) {
            $items = str_ireplace($myRow['line_synonym_name'], '', $items);
            $items = str_replace(",,",",", $items);
        }

        $items = trim($items,',');
        if (strlen($items) != 0) {
            $mStatment = "SELECT distinct (lr.line_record_name) FROM line_records lr where lr.line_record_name in ('" .$items. "');";
            $res = mysql_query($mStatment) or die(mysql_error());
            while($myRow = mysql_fetch_assoc($res)) {
                $items = str_ireplace($myRow['line_record_name'], '', $items);
                $items = str_replace(",,",",", $items);
            }
        }
        $items = str_replace("'","", $items);
        $items = trim($items,',');
        if (strlen($items) != 0) {
            $nonHits = explode(',',$items);
        } else {
            $nonHits = array();
        }
    }
    
    if (count($breedingProgram) != 0) $breedingCode = implode("','", $breedingProgram);
    if (count($growthHabit) != 0) $growthStr = implode("','", $growthHabit);
    if (count($species) != 0) $speciesStr = implode("','", $species);
    if (count($year) != 0) $yearStr = implode("','", $year);


    /* Build the search string $where. */
    $count = 0;

    if (strlen($linenames) > 0)		{
      if ($count == 0)    	{
    	$where .= "line_record_name in ('".$linenames."')";
      }
      else    	{
	$where .= " AND line_record_name in ('".$linenames."')";
      }
      $count++;
    }

    if (count($breedingProgram) != 0)    {
      if ($count == 0)    	{
	$where .= "breeding_program_code IN ('".$breedingCode."')";
      }
      else	{
	$where .= " AND breeding_program_code IN ('".$breedingCode."')";
      }
      $count++;
    }
		
    if (count($year) != 0)      {
      if ($count == 0)    	{
	$where .= "line_record_uid IN (select line_record_uid from tht_base, experiments
where experiment_year IN ('".$yearStr."') and tht_base.experiment_uid = experiments.experiment_uid)";
      }
      else	  {
	$where .= " AND line_record_uid IN (select line_record_uid from tht_base, experiments 
where experiment_year IN ('".$yearStr."') and tht_base.experiment_uid = experiments.experiment_uid)";
      }
      $count++;
    }

    if (count($species) != 0)    {
      if ($count == 0)    	{
    	$where .= "species IN ('".$speciesStr."')";
      }
      else    	{
	$where .= " AND species IN ('".$speciesStr."')";
      }
      $count++;
    }
    if (strlen($hardness) != 0)    {
      if ($count == 0)    	{
	$where .= "hardness IN ('".$hardness."')";
      }
      else			{
	$where .= " AND hardness IN ('".$hardness."')";
      }
      $count++;
    }

    if (strlen($color) != 0)    {
      if ($count == 0)    	{
	$where .= "color IN ('".$color."')";
      }
      else			{
	$where .= " AND color IN ('".$color."')";
      }
      $count++;
    }

    if (count($growthHabit) != 0)    {
      if ($count == 0)    	{
	$where .= "growth_habit IN ('".$growthStr."')";
      }
      else			{
	$where .= " AND growth_habit IN ('".$growthStr."')";
      }
      $count++;
    }

    if (strlen($awned) != 0)    {
      if ($count == 0)    	{
	$where .= "awned IN ('".$awned."')";
      }
      else			{
	$where .= " AND awned IN ('".$awned."')";
      }
      $count++;
    }

		
    /* Do The Search */
    if ( (strlen($linenames) == 0)
	 AND (count($breedingProgram) == 0)
	 AND (count($year) == 0)
	 AND (count($species) == 0)
	 AND (strlen($hardness) == 0)
	 AND (strlen($color) == 0)
	 AND (count($growthHabit) == 0)
	 AND (strlen($awned) == 0) )
      $result = mysql_query("SELECT line_record_name FROM line_records where line_record_name = NULL");
    else  {
      $TheQuery = "select line_record_uid, line_record_name from line_records where $where";
      $result=mysql_query($TheQuery) or die(mysql_error()."<br>Query was:<br>".$TheQuery);
      //echo $TheQuery;
    }
    $linesfound = mysql_num_rows($result);

    echo "<div class='boxContent'>";

    // Show failures from the Name box that don't match any line names.
    if (count($nonHits) != 0 ){
      echo "<p>";
        foreach ($nonHits as &$i) {
            echo "<font color=red><b>\"$i\" not found.</font></b><br>";
        }
    }   
    ?>

    <h3>Lines found: <?php echo "$linesfound"; ?></h3>
    <div style="width: 420px; height: 200px; overflow: scroll;border: 1px solid #5b53a6;">
    <table width='400px' id='linesTab' class='tableclass1'>
    <tr><th>Check <br/>
    <input type="radio" name="btn1" value="ALL" onclick="javascript:exclude_all();"/>All
    <input type="radio" name="btn1" value="NONE" onclick="javascript:exclude_none();"/>None</th><th><b>Line name</b></th></tr>
    <?php
		
    echo "<form name='lines' id='selectLines' action='pedigree/line_selection.php' method='post'>";
    if (mysql_num_rows($result) > 0) {
      while($row = mysql_fetch_assoc($result)) {
	$line_record_name = $row['line_record_name'];
	$line_record_uid = $row['line_record_uid'];
	?>
	<tr>
	<td><input type='checkbox' checked value="<?php echo $line_record_uid?>" name='selLines[]'id="exbx_<?php echo $line_record_uid ?>"/>
	</td>
	<td>
	 <?php echo $line_record_name ?> 
	</td>
	</tr>
	<?php
      }
    }
    ?>
  </table>    
  </div>
    <?php
    if (!isset($_SESSION['selected_lines']) || count($_SESSION['selected_lines']) == 0) {   
      echo "<p><input type='submit' value='Add to Selected' style='color:blue'>";
    }
    else {
?>
    <p>Combine with <font color=blue>currently selected lines</font>:<br>
    <input type="radio" name="selectWithin" value="Replace" checked>Replace<br>
    <input type="radio" name="selectWithin" value="Add">Add (OR)<br>
    <input type="radio" name="selectWithin" value="Yes">Intersect (AND)<br>
    <input type="submit" value="Combine" style='color:blue'>
<?php }
    echo "</form>";
    echo "</div>";
  }

$verify_selected_lines = $_POST['selLines'];
$verify_session = $_SESSION['selected_lines'];
if (count($verify_selected_lines)!=0 OR count($verify_session)!=0)
{
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

$username=$_SESSION['username'];
if ($username && !isset($_SESSION['selected_lines'])) {
  $stored = retrieve_session_variables('selected_lines', $username);
  if (-1 != $stored)
    $_SESSION['selected_lines'] = $stored;
 }
$display = $_SESSION['selected_lines'] ? "":" style='display: none;'";

print "<div class='boxContent'>";
$selectedcount = count($_SESSION['selected_lines']);
echo "<h3><font color=blue>Currently selected lines</font>: $selectedcount</h3>";

print "<form id=\"deselLinesForm\" action=\"".$_SERVER['PHP_SELF']."\" method=\"post\" $display>";
print "<select name=\"deselLines[]\" multiple=\"multiple\" style=\"height: 12em;width: 16em\">";
foreach ($_SESSION['selected_lines'] as $lineuid) {
  $result=mysql_query("select line_record_name from line_records where line_record_uid=$lineuid") or die("invalid line uid\n");
  while ($row=mysql_fetch_assoc($result)) {
    $selval=$row['line_record_name'];
    print "<option value=\"$lineuid\">$selval</option>\n";
  }
}
print "</select>";
print "<p><input type=\"submit\" value=\"Deselect highlighted lines\" /></p>";
print "</form>";
	
$display1 = $_SESSION['selected_lines'] ? "":" style='display: none;'";	
print "<form id=\"showPedigreeInfo\" action=\"pedigree/pedigree_info.php\" method=\"post\" $display1>";
print "<p><input type=\"submit\" value=\"Show line information\" /></p></form>";

// Store the selected lines in the database.
if ($username)
  store_session_variables('selected_lines', $username);
 print "</div>";
}
 print "</div>";
 print "</div>";
 print "</div>";

require $config['root_dir'] . 'theme/footer.php';
?>
