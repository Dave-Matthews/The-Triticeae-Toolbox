<?php session_start();

// 2/2/2011  JLee  Add ability to parse tab-delimited and comma separate line inputs
// 1/28/2011  JLee  Add ability to add muliple lines and synonym translation

require 'config.php';
include($config['root_dir'] . 'includes/bootstrap.inc');
connect();
include($config['root_dir'] . 'theme/admin_header.php');

if($_SERVER['REQUEST_METHOD'] == "POST")
  // Store what the user's previous selections were so we can
  // redisplay them as the page is redrawn.
  {
    $name = $_POST['LineSearchInput'];
    $hullType = $_POST['Hull'];
    $rowType = $_POST['RowType'];
    $severity = $_POST['severity'];
    $description = $_POST['description'];
    $typeSelected[$rowType] = 'checked="checked"';
    $hullSelected[$hullType] = 'checked="checked"';
    if(is_array($_POST['breedingprogramcode']))
      {
	foreach ($_POST['breedingprogramcode'] as $key => $value)
	  {
	    // echo "Problem Type $key: $value<br/>";
	    $breeding[$value] = 'selected="selected"';
	  }
      }  
    if(is_array($_POST['primaryenduse']))
      {
	foreach ($_POST['primaryenduse'] as $key => $value)
	  {
	    $primary[$value] = 'selected="selected"';
	  }
      }  
    if(is_array($_POST['growthhabit']))
      {
	foreach ($_POST['growthhabit'] as $key => $value)
	  {
	    $growth[$value] = 'selected="selected"';
	  }
      } 
    if(is_array($_POST['year']))
      {
	foreach ($_POST['year'] as $key => $value)
	  {
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
  <!-- <input type="text" name="LineSearchInput" value="<?php echo $name?>"/> --> 
  <textarea name="LineSearchInput" rows="3" cols="20" style="height: 6em;"></textarea>
  <br/> Eg: M25, FEG148-16, Doyce<br/>
  Synonyms will be translated.
  <br></td>


  <td> 
	<b> Data program </b> <br/><br/>
		
	
	<select name="breedingprogramcode[]" multiple="multiple" size="6" style="width: 12em height: 12em;">
				<?php 
		
		$sql = "SELECT DISTINCT(l.breeding_program_code), c.data_program_name FROM line_records l, CAPdata_programs c WHERE l.breeding_program_code = c.data_program_code ";
		$res = mysql_query($sql) or die(mysql_error());
		while ($resp = mysql_fetch_assoc($res))
		{
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


	<td> <b> Primary end use </b> <br/><br/>
	
	<select name="primaryenduse[]" multiple="multiple" size="6" style="width: 12em height: 12em;">
				<?php
		
		
		$sql = "SELECT DISTINCT(primary_end_use) FROM line_records WHERE primary_end_use NOT LIKE 'NULL' AND NOT primary_end_use = ''";
		$res = mysql_query($sql) or die(mysql_error());
		while ($resp = mysql_fetch_assoc($res))
		{
			
			?>
				<option value="<?php echo $resp['primary_end_use'] ?>" <?php echo $primary[$resp['primary_end_use']]?>><?php echo $resp['primary_end_use'] ?></option>
			<?php
		}
		?>
						</select><br/><br/>
	</td>
  </tr>

  <tr>
  <td>
  <b>Growth habit </b> <br/> <br/>
	
	<select name="growthhabit[]" multiple="multiple" size="4" style="width: 10em;height: 3em;">
				<?php
		
		$sql = "SELECT DISTINCT(growth_habit) FROM line_records WHERE growth_habit NOT LIKE 'NULL' AND NOT growth_habit = ''";
		$res = mysql_query($sql) or die(mysql_error());
		//$count = 1;
		while ($resp = mysql_fetch_assoc($res))
		{
			
			?>
				<option value="<?php echo $resp['growth_habit'] ?>" <?php echo $growth[$resp['growth_habit']]?>><?php echo $resp['growth_habit'] ?></option>
			<?php
			//	$count++;
		}
		?>
			    </select>
<br><br>	
	</td>
  <td>
  <b>Row type </b> <br/><br/>
      <input type="radio" name="RowType" value="2" <?php echo $typeSelected['2'] ?>/> 2<br>
      <input type="radio" name="RowType" value="6" <?php echo $typeSelected['6'] ?>/> 6<br><br>
	</td>
	<td>
	<b> Hull type </b> <br/><br/>
      <input type="radio" name="Hull" value="hulled" <?php echo $hullSelected['hulled']?>/>&nbsp;&nbsp;Hulled<br>
      <input type="radio" name="Hull" value="hulless" <?php echo $hullSelected['hulless']?>/>Hulless<br><br>
	</td>
<td></td>
	</tr>
  </table>

  <p ><input type="submit" style="height:2em; width:6em;" value="Search"/></p>
</form>
</div>


	<?php 
  if (isset($_POST['LineSearchInput'])) {
    $linenames = $_POST['LineSearchInput'];
    $breedingProgram = $_POST['breedingprogramcode'];
    $growthHabit = $_POST['growthhabit'];
    $rowType = $_POST['RowType'];
    $hull = $_POST['Hull'];
    $primaryEndUse = $_POST['primaryenduse'];
    $year = $_POST['year'];
    $lineArr = array();

    // Translate synonym
    if (strlen($linenames) != 0)
    {
     	if (strpos($linenames, ',') > 0 ) {
			$linenames = str_replace(", ",",", $linenames);	
			$lineList = explode(',',$linenames);
		} elseif (preg_match("/\t/", $linenames)) {
			$lineList = explode("\t",$linenames);
		} else {
			$lineList = explode('\r\n',$linenames);
		}
	   	
        $items = implode("','", $lineList);
        $mStatment = "SELECT distinct (lr.line_record_name) FROM line_synonyms ls, line_records lr where ls.line_record_uid = lr.line_record_uid and (ls.line_synonym_name in ('" .$items. "') or lr.line_record_name in ('". $items. "'));";
 
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
    
    if (count($breedingProgram) != 0)
    {
    $breedingCode = implode("','", $breedingProgram);
    }
    
    if (count($growthHabit) != 0)
    {
    $growthStr = implode("','", $growthHabit);
    }
    
    if (count($primaryEndUse) != 0)
    {
    $primaryUse = implode("','", $primaryEndUse);
    }
    if (count($year) != 0)
      {
	$yearStr = implode("','", $year);
      }
    $count = 0;
    
    
    if (count($breedingProgram) != 0)
    {
    	if ($count == 0)
    	{
			$where .= "breeding_program_code IN ('".$breedingCode."')";
			}
			else
			{
			$where .= " AND breeding_program_code IN ('".$breedingCode."')";
			}
			$count++;
		}
		
		if (count($growthHabit) != 0)
    {
    if ($count == 0)
    	{
			$where .= "growth_habit IN ('".$growthStr."')";
			}
		else
			{
				$where .= " AND growth_habit IN ('".$growthStr."')";
			}
			$count++;
		}
		
		if (count($primaryEndUse) != 0)
    {
    if ($count == 0)
    	{
    	$where .= "primary_end_use IN ('".$primaryUse."')";
    	}
    else
    	{
			$where .= " AND primary_end_use IN ('".$primaryUse."')";
			}
			
			$count++;
		}
		
		if (strlen($linenames) > 0)
		{
		if ($count == 0)
    	{
    	$where .= "line_record_name in ('".$linenames."')";
    	}
    else
    	{
			$where .= " AND line_record_name in ('".$linenames."')";
			}
			$count++;
		}
		
		if (strlen($rowType) > 0)
		{
		if ($count == 0)
    	{
    	$where .= "row_type IN ('".$rowType."')";
    	}
    else
    	{
			$where .= " AND row_type IN ('".$rowType."')";
			}
			$count++;
		}
		
		if (strlen($hull) > 0)
		{
		if ($count == 0)
    	{
    	$where .= "hull IN ('".$hull."')";
    	}
    else
    	{
			$where .= " AND hull IN ('".$hull."')";
			}
			$count++;
		}

    if (count($year) != 0)
      {
    	if ($count == 0)
    	{
	  $where .= "line_record_uid IN (select line_record_uid from tht_base, experiments
where experiment_year IN ('".$yearStr."') and tht_base.experiment_uid = experiments.experiment_uid)";
	}
	else
	  {
	    $where .= " AND line_record_uid IN (select line_record_uid from tht_base, experiments 
where experiment_year IN ('".$yearStr."') and tht_base.experiment_uid = experiments.experiment_uid)";
	  }
      }
		
    
    // $test = "'CC','SM'";
    
   // echo "WHere VAlue =".$where ;
    
    //$escaped = mysql_real_escape_string($where);
    
   // echo "excaped VAlue =".$escaped ;
    
   /* echo "<br/>";
    var_dump($escaped);
    var_dump($lineStr);
    var_dump($test); */
   // var_dump($breedingCode);
    
   // var_dump ($breedingProgram);
    //var_dump ($breedingCode);
    
/* 
    var_dump($breedingProgram);
    var_dump($growthHabit);
    var_dump($primaryEndUse); 
    
    if (strlen($linenames) < 1)
      $linenames = ".";
   
    if (count($breedingProgram) < 1)
      $breedingProgram = ".";
      
    if (count($growthHabit) < 1)
      $growthHabit = ".";
      
    if (strlen($rowType) < 1)
      $rowType = ".";  
		
		if (strlen($hull) < 1)
      $hull = ".";     
		
		if (count($primaryEndUse) < 1)
      $primaryEndUse = ".";   

*/
    if ( (strlen($linenames) < 1) AND (strlen($hull) < 1) AND (strlen($rowType) < 1) AND (count($breedingProgram) == 0) AND  (count($growthHabit) == 0) AND (count($primaryEndUse) == 0) AND (count($year) == 0)  ) {
        $result = mysql_query("SELECT line_record_name FROM line_records where line_record_name = NULL");
	} else  {
    // echo "select line_record_uid, line_record_name from line_records where line_record_name regexp \"$linenames\"";
// echo "I am here <br>";   
   // $result=mysql_query("select line_record_uid, line_record_name from line_records where line_record_name regexp '".$linenames."' ");
    
    //$result=mysql_query("select line_record_uid, line_record_name from line_records where ('".$where."') ");
		$result=mysql_query("select line_record_uid, line_record_name from line_records where $where ");
  //  echo "<div style="padding: 0; width: 810px; height: 300px; overflow: scroll; border: 1px">";
  	
  	//	var_dump($result);
  	}
    $linesfound = mysql_num_rows($result);

    echo "<div class='boxContent'>";
    if (count($nonHits) != 0 ){
        foreach ($nonHits as &$i) {
            echo "<font color=red>\"$i\" not found.</font><br>";
        }
        echo "<br>";        
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
    <input type="radio" name="selectWithin" value="Yes" checked/>Intersect (AND)<br>
    <input type="radio" name="selectWithin" value="Add"/>Add (OR)<br>
    <input type="radio" name="selectWithin" value="Replace"/>Replace<br>
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
