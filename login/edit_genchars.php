<?php
// login/edit_gen_chars.php, dem jun2015.  From:
// login/edit_synonym.php, dem 13feb2012

require 'config.php';
include($config['root_dir'] . 'includes/bootstrap_curator.inc');

connect();
loginTest();
ob_start();
include($config['root_dir'] . 'theme/admin_header.php');
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();
$mysqli = connecti();
?>
<div class="boxContent">
<h2>Edit Genetic Characters for a Line</h2>
  <!-- Select which line to edit. -->
  <form method="get">
    <p><strong>Line Name</strong><br>
      <input type="text" name="line" value="<?php echo $_GET['line']; ?>">
      <input type="submit" value="Search" /></p>
  </form>
<?php
// Has "Accept" been clicked to update the values ?
if(!is_null($_POST['y'])) {
  $line_uid = $_POST['line_uid'];
  for ($i = 0; $i < count($_POST['x']); $i++) 
    /* $propvals[] = array ($_POST['x'][$i] => $_POST['y'][$i]); */
    $propvals[$_POST['x'][$i]] = $_POST['y'][$i];
  $changed = array("unchanged", "updated");
  $flag = 0;
  foreach($propvals as $k=>$v) {
    if (empty($v)) {
      // Delete the record.
      /* $sql = "delete from barley_pedigree_catalog_ref where barley_pedigree_catalog_ref_uid = $k"; */
      /* $res = mysql_query($sql) or die(mysql_error()."<br>Query: ".$sql); */
      $flag = 1;
    }
    else {
      $oldval = mysql_grab("select value
		    from line_properties lp, property_values pv, properties p
		    where lp.property_value_uid = pv.property_values_uid
		    and pv.property_uid = p.properties_uid
		    and lp.line_record_uid = $line_uid
		    and p.name = '$k'");
      if ($oldval != $v) {
      	// Edit the value.
	$oldvalid = mysql_grab("select property_values_uid
			    from property_values pv, properties p
			    where p.name = '$k'
			    and pv.value = '$oldval'
			    and pv.property_uid = p.properties_uid");
	$pvid = mysql_grab("select property_values_uid from property_values
		    where property_uid = (select properties_uid from properties where name = '$k')
		    and value = '$v'");
	if (!$pvid) 
	  echo "Value <b>'$v'</b> is not defined for property <b>'$k'</b><p>";
	else {
	  $sql = "update line_properties set property_value_uid = $pvid
		where line_record_uid = $line_uid
		and property_value_uid = $oldvalid";
	  $res = mysqli_query($mysqli, $sql) or die("Error updating property value<br>".mysqli_error($mysqli));
	  $flag = 1;
	}
      }
    }
  }
  echo "Database <font color=green><b>$changed[$flag]</b></font>.<p>";
}

// Have we searched for a particular Line?
if(isset($_GET['line'])) {
  $line = $_GET['line'];
  $line_uid = mysql_grab("select line_record_uid from line_records where line_record_name = '$line'");
  if (empty($line_uid))
    echo "Line name not found.<p>";
  else {
    // Get this Line's properties and their values.
    $sql = "select p.name, p.properties_uid, pv.value, pv.property_values_uid
	    from properties p, property_values pv, line_properties lp
	    where lp.line_record_uid = $line_uid
	    and lp.property_value_uid = pv.property_values_uid
	    and pv.property_uid = p.properties_uid";
    $res = mysqli_query($mysqli, $sql) or die("Error finding property values");
    $r = 0;
    while ($row = mysqli_fetch_row($res)) {
      $prop[$r] = $row[0];
      $propid[$r] = $row[1];
      $val[$r] = $row[2];
      $valid[$r] = $row[3];
      $r++;
    }
    // Display the data for editing.
    echo "<form method=POST>";
    echo "<b>Genetic Characters</b><br>";
    echo "<table><tr><th>Attribute/Gene<th>Value/Allele";
    for ($i = 0; $i < count($prop); $i++) {
      echo "<tr><td>$prop[$i]";
      echo "<td><input type=text name='y[]' value = '$val[$i]' size = 8>";
      echo "<input type=hidden name='x[]' value = '$prop[$i]'>";
    }
    echo "<input type=hidden name='line_uid' value='$line_uid'>";
    echo "<input type=hidden name='line' value=$line>";
    echo "</table>";
    echo "<input type=submit value='Accept'></form>";
  }
}


echo "</div>";
echo "</div>";
include($config['root_dir'] . '/theme/footer.php');?>
