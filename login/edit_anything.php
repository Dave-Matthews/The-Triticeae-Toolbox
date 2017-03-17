<?php
// 7feb2013 dem: Generalized table editor.  Taken from login/edit_traits.php.
// 12/14/2010 JLee  Change to use curator bootstrap
require 'config.php';
include($config['root_dir'] . 'includes/bootstrap_curator.inc');
include($config['root_dir'] . 'theme/admin_header.php');
/*
 * Logged in page initialization
 */
$mysqli = connecti();
loginTest();
$row = loadUser($_SESSION['username']);
ob_start();
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();
?>

<h2>Edit Anything</h2>
<div class="boxContent">

<?php if(empty($_REQUEST['table'])) { 
?>
  <p>You&apos;re dreaming, right?
  <p>This is a new and relatively untested functionality.  Please use
  with caution and report any problems.  The interface only allows
  changing one record at a time, so how much trouble can you get into?
  <p>You must know the name of the MySQL database table you want to edit.
    Some tables that seem to be working: 
    <br>phenotype_category, units, mapset, phenotype_experiment_info ...
    <form action="<?php echo $config['base_url']; ?>login/edit_anything.php" method="get">
      <p>Which table do you wish to edit?
	<input type="text" name="table" size="20"> 
	<input type="submit" value="Go" /></p>
    </form>
<?php 
			  }
else {
  // A table has been chosen for editing.  Sanitize to avoid disasters.
  $table = ($_REQUEST['table']);
  $forbidden = array('users','user_types','settings','session_variables','input_file_log');
  if (in_array($table, $forbidden)) {
    echo "Table <b>$table</b> can only be edited by an Administrator.<p>";
    exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");	 
  }
  if (strpos($table, ".")) {
    echo "<b>$table</b>: Table name may not contain a \".\" Only this database can be edited.<p>";
    exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");	 
  }
  $res = mysqli_query($mysqli, "show tables like '$table'");
  if (mysqli_num_rows($res) == 0) {
    echo "Table <b>$table</b> does not exist.<p>";
    exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
}
  echo "Editing table <b>'$table'</b>.  ";
  $here = $_SERVER['PHP_SELF'];
  echo "<input type=button value='Reselect' onClick=\"window.location='$here'\"><p>";
  $pkey = get_pkey($table);

  // Has an update been submitted?
  if( ($id = array_search("Update", $_POST)) != NULL) {
    foreach($_POST as $k=>$v)
      $_POST[$k] = addslashes($v);
    updateTable($_POST, $table, array($pkey=>$id));
    // updateTable() is in includes/common.inc.
  }

  // Search for desired records.
  $tablesToSearch = array($table);
  $searchstring = '';
  if(isset($_REQUEST['search']) && $_REQUEST['search'] != "") {
    $found = array();
    $searchstring = $_REQUEST['search'];
    $words = explode(" ", $_REQUEST['search']);
    foreach($words as $q) 
      $found = array_merge($found, desperateTermSearch($tablesToSearch, $q));
    $drds = array();  // An array of uids of the records found.
    if(count($found) > 0) {		//if we found results..
      for($i=0; $i<count($found); $i++) {
	$parts = explode("@@", $found[$i]);
	array_push($drds, $parts[2]);
      }
    }
  }

  $start = 0;
  if(isset($_GET['start'])) 
    $start = $_GET['start'];
?>
    <form action="<?php echo $here ?>" method="get">
    <p>Show only items containing these words:<br>
	<input type="text" name="search" value="<?php echo $searchstring ?>" size="30" /> 
	<input type="hidden" name="table" value="<?php echo $table ?>"> 
	<input type="submit" value="Search" /></p>
    </form>
<?php
  // attaching the query string to the callback URL.
  $self = $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING'];
  if(isset($drds) && count($drds) > 0) {
    $self .= isset($_GET['search']) ? "" : "search=". $_REQUEST['search'];
    editSelectRows($table, $drds, $self, $start); 
  }
  else if(!isset($drds))
    editAllRows($table, $self, $start);
  else
    echo "<p>Search returned no results</p>";
}
echo "</div></div>";
include($config['root_dir'] . 'theme/footer.php');

/*
 * This function will actually display the table row. 
 * @param $table - specifies which table to edit.
 * @param $where - sets the conditions of which to select the row(s). This makes it possible to select any number of rows.
 * @param $page - editing allows for updating and has a button that goes to a certain page to update. This variable sets that page
 */
function editTableRow($table, $where, $page, $start="0") {
  $pkey = get_pkey($table);
  $ignore = array($pkey, "datatype");
  // editGeneral() is in includes/common.inc.
  editGeneral($table, $where, $page, $ignore, "20", $start);
}

function editAllRows($table, $page, $start) {
  editTableRow($table, "1", $page, $start);
}

/*
 * This will select a list of records to edit from a given array of IDs
 * If we have a bunch of IDs that we want to edit and there isn't a range
 * of them then we can use this function to display them. 
 * @param $ID_set - an array of IDs to edit. This MUST be an array.
 * @param $page - the page that the update button will travel to
 */ 
function editSelectRows($table, $ID_set, $page, $start) {
  $pkey = get_pkey($table);
  if(is_array($ID_set)) {
    $where = "";
    for($i=0; $i<count($ID_set); $i++) {
      if($i != 0)
	$where .= " OR ";
      $where .= "$pkey = '$ID_set[$i]'";
    }
    editTableRow($table, $where, $page, $start);
  }
}
