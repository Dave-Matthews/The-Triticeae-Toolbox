<?php
// login/edit_synonym.php, dem 13feb2012

require 'config.php';
/*
 * Logged in page initialization
 */
include($config['root_dir'] . 'includes/bootstrap_curator.inc');

connect();
loginTest();

ob_start();
include($config['root_dir'] . 'theme/admin_header.php');
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();
?>

<div id="primaryContentContainer">
  <div id="primaryContent">
    <div class="box">
      <h2>Edit Line Synonyms</h2>
      <div class="boxContent">

        <!-- Select which line to edit. -->
	<form action="<?php echo $config['base_url']; ?>login/edit_synonym.php" method="get">
	  <p><strong>Line Name</strong><br>
	    <input type="text" name="line" value="<?php echo $_REQUEST['line']; ?>">
	  <input type="submit" value="Search" /></p>
	</form>

<?php
/*
 * Has a Synonym update been submitted?
 */
if(!is_null($_REQUEST['newsyn'])) {
  $input = $_REQUEST;
  foreach($input as $k=>$v)
    $input[$k] = addslashes($v);
  array_pop($input); // Remove line name.
  $line_uid = array_pop($input);
  $newsyn = array_pop($input);
  $changed = array("unchanged", "updated");
  $flag = 0;
  if (!empty($newsyn)) {
    // Add a new value.
    $sql = "insert into line_synonyms 
           (line_record_uid, line_synonym_name, updated_on, created_on) 
           values ($line_uid, '$newsyn', now(), now())";
    $res = mysql_query($sql) or die(mysql_error()."<br>Query: ".$sql);
    $flag = 1;
  }
  foreach($input as $k=>$v) {
    if (empty($v)) {
      // Delete the record.
      $sql = "delete from line_synonyms where line_synonyms_uid = $k";
      $res = mysql_query($sql) or die(mysql_error()."<br>Query: ".$sql);
      $flag = 1;
    }
    else {
      $oldval = mysql_grab("select line_synonym_name from line_synonyms where line_synonyms_uid = $k");
      if ($oldval != $v) {
	// Edit the value.
	$res = mysql_query("update line_synonyms set line_synonym_name = '$v', updated_on = now()
             where line_synonyms_uid = $k") or die(mysql_error()."<br>Query: ".$sql);
	$flag = 1;
      }
    }
  }
  echo "Database <font color=red><b>$changed[$flag]</b></font>.<p>";
}

/*
 * Has a GRIN Accession update been submitted?
 */
if(!is_null($_REQUEST['newgrin'])) {
  $input = $_REQUEST;
  foreach($input as $k=>$v)
    $input[$k] = addslashes($v);
  array_pop($input); // Remove line name.
  $line_uid = array_pop($input);
  $newgrin = array_pop($input);
  $changed = array("unchanged", "updated");
  $flag = 0;
  if (!empty($newgrin)) {
    // Add a new value.
    $sql = "insert into barley_pedigree_catalog_ref
           (barley_pedigree_catalog_uid, line_record_uid, barley_ref_number, updated_on, created_on) 
           values (2, $line_uid, '$newgrin', now(), now())";
    $res = mysql_query($sql) or die(mysql_error()."<br>Query: ".$sql);
    $flag = 1;
  }
  foreach($input as $k=>$v) {
    if (empty($v)) {
      // Delete the record.
      $sql = "delete from barley_pedigree_catalog_ref where barley_pedigree_catalog_ref_uid = $k";
      $res = mysql_query($sql) or die(mysql_error()."<br>Query: ".$sql);
      $flag = 1;
    }
    else {
      $oldval = mysql_grab("select barley_ref_number from barley_pedigree_catalog_ref 
                            where barley_pedigree_catalog_ref_uid = $k");
      if ($oldval != $v) {
	// Edit the value.
	$res = mysql_query("update barley_pedigree_catalog_ref set barley_ref_number = '$v', updated_on = now()
             where barley_pedigree_catalog_ref_uid = $k") or die(mysql_error()."<br>Query: ".$sql);
	$flag = 1;
      }
    }
  }
  echo "Database <font color=green><b>$changed[$flag]</b></font>.<p>";
}

/*
 * Have we searched?
 */
if(isset($_REQUEST['line'])) {
  $self = $_SERVER['PHP_SELF'];
  $line = $_REQUEST['line'];
  $line_uid = mysql_grab("select line_record_uid from line_records where line_record_name = '$line'");
  if (empty($line_uid))
    echo "Line name not found.<p>";
  else {
    echo "<table><tr><td style='vertical-align:top'>";
    echo "<form action=$self>";
    echo "<b>Synonyms</b><br>";
    $sql = "select line_synonyms_uid, line_synonym_name
            from line_synonyms where line_record_uid = $line_uid";
    $res = mysql_query($sql) or die(mysql_error());
    while($row = mysql_fetch_row($res)) {
      echo "<input type=text name='$row[0]' value='$row[1]'><br>";
    }
    echo "<input type=text name='newsyn'><br>";
    echo "<input type=hidden name='line_uid' value='$line_uid'>";
    echo "<input type=hidden name='line' value=$line>";
    echo "<input type=submit value='Accept'>";
    echo "</form></td>";

    echo "<td style='vertical-align:top'><form action=$self>";
    echo "<b>GRIN Accessions</b><br>";
    $sql = "select barley_pedigree_catalog_ref_uid, barley_ref_number
            from barley_pedigree_catalog_ref where line_record_uid = $line_uid
            and barley_pedigree_catalog_uid = 2";
    $res = mysql_query($sql) or die(mysql_error());
    while($row = mysql_fetch_row($res)) {
      echo "<input type=text name='$row[0]' value='$row[1]'><br>";
    }
    echo "<input type=text name='newgrin'><br>";
    echo "<input type=hidden name='line_uid' value='$line_uid'>";
    echo "<input type=hidden name='line' value=$line>";
    echo "<input type=submit value='Accept'>";
    echo "</form></td></tr></table>";
  }
}

?>
      </div>
    </div>
  </div>
</div>
</div>

<?php include($config['root_dir'] . '/theme/footer.php');?>



