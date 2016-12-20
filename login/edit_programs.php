<?php
// 21jul2014 DEM Added ability to create an Institution.
// 30jan2012 DEM Taken from edit_traits.php.

require 'config.php';
include $config['root_dir'] . 'includes/bootstrap_curator.inc';
$mysqli = connecti();
loginTest();

ob_start();
include $config['root_dir'] . 'theme/admin_header.php';
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();

/*
 * Session variable stores duplicate records, do we wish to edit duplicates?
 */
if (isset($_SESSION['DupProgRecords'])) {
    sort($_SESSION['DupProgRecords']);
    $drds = $_SESSION['DupProgRecords'];
}
if (count($drds) == 0) {
    unset($_SESSION['DupProgRecords']);
    unset($drds);
}

/*
 * Has form data been submitted?  Then handle it.
 */
if (!empty($_POST[adding])) {
    // Add a new Program.
    $prcode = $_POST[code];
    $prname = $_POST[name];
    $prtype = $_POST[type];
    $prinst = $_POST[inst];
    $prcollab = $_POST[collab];
    $prdesc = $_POST[desc];
  // Validate
    if (empty($prcode)) {
        $adderr = "Program Code is required.  Nothing added.<br>";
    } else {
        $oldcode = mysql_grab("select data_program_code from CAPdata_programs where data_program_code = '$prcode'");
        if (!empty($oldcode)) {
            $adderr = "Can't add.  Program Code $oldcode already exists.<br>";
        }
    }
    if (empty($prname)) {
        $adderr .= "Program Name is required.  Nothing added.<br>";
    } else {
        $oldname = mysql_grab("select data_program_name from CAPdata_programs where data_program_name = '$prname'");
        if (!empty($oldname)) {
            $adderr .= "Can't add.  Program Name \"$oldname\" already exists.<br>";
        }
    }
    if (empty($prinst)) {
        $adderr .= "Institution is required.  Nothing added.<br>";
    }
    if (empty($prtype)) {
        $adderr .= "Program Type is required.  Nothing added.<br>";
    }
    if (empty($adderr)) {
    // Validated.  Add the data.
        $sql = "insert into CAPdata_programs (
	  data_program_code,
	  data_program_name,
	  institutions_uid,
	  program_type,
	  collaborator_name, 
	  description,
	  created_on ) 
       values (
	  '$prcode',
	  '$prname',
	  $prinst,
	  '$prtype',
	  '$prcollab',
	  '$prdesc',
	  NOW() )";
        mysqli_query($mysqli, $sql) or die("Insert failed.<br>".mysqli_error($mysqli));
        $adderr = "Program $prcode added.";
    }
}

if (!empty($_POST[instn])) {
  // Add a new Institution.
  $instname = $_POST[instname];
  $instabbr = $_POST[instabbr];
  $inststate = $_POST[inststate];
  $instcountry = $_POST[instcountry];
  if (empty($instname))
    $insterr .= "Institution Name is required.  Nothing added.<br>";
  else { 
    $oldname = mysql_grab("select institutions_name from institutions where institutions_name = '$instname'");
    if (!empty($oldname))
      $insterr .= "Can't add.  Institution Name \"$oldname\" already exists.<br>";
  }
  if (empty($instabbr))
    $insterr .= "Institution Abbreviation is required.  Nothing added.<br>";
  else { 
    $oldabbr = mysql_grab("select institute_acronym from institutions where institute_acronym = '$instabbr'");
    if (!empty($oldabbr))
      $insterr .= "Can't add.  Institution Abbreviation \"$oldabbr\" already exists.<br>";
  }
  if (empty($insterr)) {
    // Validated.  Add the data.  Ignore the institute_address field.
    $sql = "insert into institutions (
	  institutions_name,
	  institute_acronym,
	  institute_state,
	  institute_country,
          institute_address,
	  created_on ) 
       values (
	  '$instname',
	  '$instabbr',
	  '$inststate',
	  '$instcountry',
          '',
	  NOW() )";
    mysqli_query($mysqli, $sql) or die("Insert failed.<br>".mysqli_error($mysqli));
    $insterr = "Institution $instname added.";
  }
}

if( ($id = array_search("Update", $_POST)) != NULL) {
  // "Update" button
  foreach($_POST as $k=>$v)
    $_POST[$k] = addslashes($v);
  updateTable($_POST, "CAPdata_programs", array("CAPdata_programs_uid"=>$id));
}
if (!empty($_POST['Delete'])) {
  // "Delete" button
  $id = ($_POST['Delete']);
  $name = "";
  $sql = "select dataset_name from datasets where CAPdata_programs_uid=$id";
  $res = mysqli_query($mysqli, $sql);
  while ($row = mysqli_fetch_row($res)) {
      if ($name == "") {
          $name = $row[0];
      } else {
          $name .= ", $row[0]";
      }
  }
  $trial_code = "";
  $sql = "select trial_code from experiments where CAPdata_programs_uid= $id";
  $res = mysqli_query($mysqli, $sql);
  while ($row = mysqli_fetch_row($res)) {
      if ($name == "") {
          $trial_code = $row[0];
      } else {
          $trial_code .= ", $row[0]";
      }
  }
  $code = mysql_grab("select data_program_code from CAPdata_programs where CAPdata_programs_uid=$id");
  if ($trial_code != "") {
      echo "<font color=red><b>Can't delete.</b></font> Data program <b>$code</b> is used in experiment $trial_code.<br>\n";
      return;
  }

  echo "Attempting to delete CAP Data Program id = $id, code = $code...<p>";
  $sql = "delete from datasets where CAPdata_programs_uid = $id";
  $res = mysqli_query($mysqli, $sql);
  $err = mysqli_error($mysqli);
  if (!empty($err)) {
      echo "Error: $err<br>\n";
  } else {
      echo "Success. Data program <b>$name</b> deleted from datasets.<br>\n";
  }
  $sql = "delete from CAPdata_programs where CAPdata_programs_uid = $id";
  //  $res = mysql_query($sql) or die(mysql_error());
  $res = mysqli_query($mysqli, $sql);
  $err = mysqli_error($mysqli);
  if (!empty($err)) {
    if (strpos($err, "a foreign key constraint fails"))
      echo "<font color=red><b>Can't delete.</b></font> Other data is linked to this program. The error message is:<br>$err";
  }
  else 
    echo "Success.  Data program <b>$code</b> deleted from CAPdata_programs.<p>";
}

$searchstring = '';
if(isset($_REQUEST['search']) && $_REQUEST['search'] != "") {
  $tablesToSearch = array("CAPdata_programs");
  $found = array();
  $searchstring = $_REQUEST['search'];
  $words = explode(" ", $_REQUEST['search']);
  foreach($words as $q) {
    $found = array_merge($found, desperateTermSearch($tablesToSearch, $q));
  }
  $drds = array();
  if(count($found) > 0) {		//if we found results..
    for($i=0; $i<count($found); $i++) {
      $parts = explode("@@", $found[$i]);
      array_push($drds, $parts[2]);
    }
  }
}

// Which pagefull of existing records should we display?
$start = 0;
if(isset($_GET['start'])) 
  $start = $_GET['start'];
?>

<!-- Show the page and its forms. -->
<div class="box">
  <h2>Curate Data Programs</h2>
  <div class="boxContent">

<table><tr><td>
    <h3>Add a new Program</h3>
<?php
   if (!empty($adderr))
     echo "<font color=red><b>$adderr</b></font>";
?>   
<form method= post>
  <table>
    <tr><td>Program Code<td><input type=text name=code size=5 value = <?php echo $prcode ?>>	
    <tr><td>Name<td><input type=text name=name size=30 value = <?php echo $prname ?>>
    <tr><td>Type<td>
	<select name=type>
	  <option>
	  <option value=breeding>breeding
	  <option value=data>data
	  <option value=mapping>mapping
    <tr><td>Institution<td>
	<select name=inst><option value="">
<?php
  $res = mysqli_query($mysqli, "select institutions_uid, institutions_name from institutions") or die(mysqli_error($mysqli));
   while($r = mysqli_fetch_row($res))
     echo "<option value=$r[0]>$r[1]";
?>
	</select>
    <tr><td>Collaborator<td><input type=text name=collab value = <?php echo $prcollab ?>>	
    <tr><td>Description<td><input type=text name=desc size=50 value = <?php echo $prdesc ?>>	
  </table>
  <input type=submit value=Add name=adding>
</form>

    <td valign=top>
      <h3>Add a new Institution</h3>
<?php
   if (!empty($insterr))
     echo "<font color=red><b>$insterr</b></font>";
?>   
<form method= post>
  <table>
    <tr><td>Name<td><input type=text name=instname size=30>	
    <tr><td>Abbreviation<td><input type=text name=instabbr>	
    <tr><td>State<td><input type=text name=inststate>	
    <tr><td>Country<td><input type=text name=instcountry value='USA'>	
  </table>
  <input type=submit value=Add name=instn>
</form>
</table>

</div>

  <div class="boxContent">
    <h3>Edit or Delete a program</h3>
    <form action="<?php echo $config['base_url']; ?>login/edit_programs.php" method="post">
      <p>Show only items containing these words:<br>
	<input type="text" name="search" value="<?php echo $searchstring ?>" size="30" /> 
	<input type="submit" value="Search" /></p>
    </form>

<?php
// attaching the query string to the callback URL.
$self = $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING'];

if(isset($drds) && count($drds) > 0) {
  $self .= isset($_GET['search']) ? "" : "search=". $_REQUEST['search'];
  editSelectPrograms($drds, $self, $start);
}
else if(!isset($drds))
  editAllPrograms($self, $start);
else 
  echo "<p>Search returned no results</p>";

echo "</div></div></div>";
include $config['root_dir'] . 'theme/footer.php';



////////// The editing functions:

/*
 * This function will actually display the row. 
 *
 * @param $where - sets the conditions of which to select the row(s). This makes it possible to select any number of rows.
 * @param $page - editing allows for updating and has a button that goes to a certain page to update. This variable sets that page
 * 
 * @return nothing - this function outputs to the screen.
 */
function editProgramRow($where, $page, $start="0") {
  $ignore = array("CAPdata_programs_uid");
  // In includes/common.inc:
  editGeneral("CAPdata_programs", $where, $page, $ignore, "20", $start);
}

/*
 * This is an example of using the above function. This should display every line (minus the gramene data) in the same format 
 * as the spreadsheet. The problem is the 0 value in the units table. It's killing us unless we put something for 0 in there.
 */
function editAllPrograms($page, $start) {
  editProgramRow("1", $page, $start);
}

/*
 * This will select a range of traits to edit from a given id to a given id. 
 *
 * $minID - the lower boundary of id to get.
 * $maxID - the upper boundary of id to get.
 * $page - this is the page that the update button will travel to. 
 *
 * Note: These values are exclusive, meaning if $minID = 1 and $maxID = 5 then the results returned will be IDs: 2, 3, and 4.
 *
 * @return nothing
 * @see editProgramRow()
 */
function editRangePrograms($minID, $maxID, $page) {
	$where = "CAPdata_programs_uid < '$maxID' AND CAPdata_programs_uid > '$minID'";
	editProgramRow($where, $page);
}

/*
 * This will select a list of traits to edit from a given array of IDs
 * 
 * If we have a bunch of IDs that we want to edit and there isn't a range
 * of them then we can use this function to display them. 
 *
 * @param $IDRange - an array of IDs to edit. This MUST be an array.
 * @param $page - the page that the update button will travel to
 *
 * @return nothing
 * @see editProgramRow()
 */ 
function editSelectPrograms($IDRange, $page, $start) {
  if(is_array($IDRange)) {
    $where = "";
    for($i=0; $i<count($IDRange); $i++) {
      if($i != 0) 
	$where .= " OR ";
      $where .= "CAPdata_programs_uid = '$IDRange[$i]'";
    }
    editProgramRow($where, $page, $start);
  }
}

/*
 * This function will edit only a single row. 
 * 
 * WARNING: Do not use this function in a for loop if you have multiple IDs to edit
 *	    use the editSelectPrograms() function for that.
 *
 * @param $ID - the id of the row to edit
 * @param $page - the page that the update button will travel to
 * 
 * @return nothing
 * @see editProgramRow()
 */
function editSingleProgram($ID, $page) {
  $where = "CAPdata_programs_uid = '$ID'";
  editProgramRow($where, $page);
}

//////////

?>
