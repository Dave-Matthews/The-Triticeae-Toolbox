<?php
// 12/14/2010 JLee  Change to use curator bootstrap

require 'config.php';
/*
 * Logged in page initialization
 */
include $config['root_dir'] . 'includes/bootstrap_curator.inc';

$mysqli = connecti();
loginTest();

/* ******************************* */
$row = loadUser($_SESSION['username']);

/* ****************************** */
ob_start();
include $config['root_dir'] . 'theme/admin_header.php';
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();

//connect_dev();  /* Connect with write-access. */

/*
 * Has an update been submitted?
 */
if( ($id = array_search("Update", $_POST)) != NULL) {
  foreach($_POST as $k=>$v)
    $_POST[$k] = addslashes($v);

  // Some validations on the line name.
  // Todo: If name is the same as another existing line or synonym, offer to merge them.
  $line = $_POST[line_record_name];
  if (empty($line))
    $msg = "Line name cannot be blank.";
  if (strpos($line, ' ')) 
    $msg = "Line name $line contains a blank. Replace with _ or remove." ;
  if ($line != strtoupper($line)) 
    $msg = "Line name $line contains lowercase characters.";
  $already = mysql_grab("select line_record_name from line_records where line_record_name like '$line'");
  if ($already == $line AND !empty($line)) {
    // Name already exists.  For this same record?
    $alreadyid = mysql_grab("select line_record_uid from line_records where line_record_name like '$line'");
    if ($alreadyid != $id)
      $msg = "Line name \"$already\" already exists.";
  }
  // If it's listed as a synonym, don't make it a line name too.
  $sql = "select line_record_name from line_synonyms ls, line_records lr
     where line_synonym_name = '$line' and ls.line_record_uid = lr.line_record_uid";
  $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
  if (mysqli_num_rows($res) > 0) {
    $rn = mysqli_fetch_row($res);
    $realname = $rn[0];
    // It's okay for a synonym to be the same as the name except for UPPER/Mixed case.
    if ($realname != $line)
      $msg = "$line is already a synonym for $realname and cannot be used as a line name too.";
  }
  if (!empty($msg))
    echo "<b><font color=red size=+1>Not changed. </font></b>". $msg . "<p>";
  else {
    if (strlen($line) < 4)  
      echo "<b>Warning:</b> '$line' is a short name and may not be unique.<p>";
    // No error, so do it.
    updateTable($_POST, "line_records", array("line_record_uid"=>$id));
  }
}


/*
 * Have we searched?
 */
if(isset($_REQUEST['search']) && $_REQUEST['search'] != "") {

	$tablesToSearch = array("line_records");
	$found = array();

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


/*
 * Set Starting point for which page we're on.
 */

//default
$start=0;

if(isset($_GET['start'])) {
	$start = $_GET['start'];
}


?>

<div id="primaryContentContainer">
	<div id="primaryContent">
		<div class="box">
		<h2>Search within Line Records</h2>
			<div class="boxContent">
			<form action="login/edit_line.php" method="post">
			<p><input type="text" name="search" size="50" /> <input type="submit" value="Go >>" /></p>
			</form>
			</div>
		</div>

<?php
	// attaching the query string to the callback URL.
	$self = $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING'];

	if(isset($_GET['line'])) {
		editSelectLines(array($_GET['line']), $self, $start);
	}
	else if(isset($drds)) {

		if(count($drds) > 0) {
			$self .= isset($_GET['search']) ? "" : "search=". $_REQUEST['search'];
			editSelectLines($drds, $self, $start);
		}
		else
			echo "<p>Search returned no results</p>";

	}
	else
		editAllLines($self, $start);
?>

	</div>
</div>
</div>

<?php include $config['root_dir'] . '/theme/footer.php';?>
