<?php
// 14feb13 dem Created, from store_traits.php.

require 'config.php';
include "../includes/bootstrap_curator.inc";
include "../theme/admin_header.php";
/*
 * Logged in page initialization
 */
$mysqli = connecti();
loginTest();
$row = loadUser($_SESSION['username']);
ob_start();
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();

$infilename = $_POST['infilename'];
$tmpdir=$_post['tmpdir'];
print "<h2>Storing the properties from: " . basename($infilename) . "</h2>";
print "<div class=\"boxContent\">";

require_once("../lib/Excel/reader.php");    //include excel reader

$data = new Spreadsheet_Excel_Reader();
$data->setOutputEncoding('CP1251');
$data->read($infilename);
//$data->trimSheet(0); 	//new function that I added to trim columns

error_reporting(E_ALL ^ E_NOTICE);

// Check for current version in cell B2.
$version = $data->sheets[0]['cells'][2][2];
$template = $config['root_dir']."curator_data/examples/T3/property_template.xls";
// Using check_version() from includes/common.inc.                        
if (!check_version($version, $template)) {
    echo "<b>Error</b>: The template file has been updated since your version, <b>\"$version\"</b>.<br> 
          Please use the new one.<br>";
    exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-2); return;\">");
};
echo "<p>";

// Check the column names.
$target = array('Name','Values','Category','Description');
for ($j = 1; $j <= 4; $j++) {
    if ($data->sheets[0]['cells'][3][$j] != $target[$j-1]) {
        die("Error: Header column $j must be '{$target[$j-1]}'.");
    }
}

$oldmax = getNumEntries("properties");
$drds = 0; // count of existing records updated
$inum = 0;  // count of data rows read

/* Parse the Sheet */
/* Iterate through rows, starting at row 4 */
for ($i = 4; $i <= $data->sheets[0]['numRows']; $i++) {
  $line=array();
  /* Iterate through the four columns */
  for ($j = 1; $j <= 4; $j++) 
    $line[$j]=trim($data->sheets[0]['cells'][$i][$j]);
  // Ignore rows with empty Name column.
  if (!empty($line[1])) {
    // Category ID.  
    if (empty($line[3])) {
      echo "Error in row $i: Category is required.<br>";
      print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-2);\">"; exit;
    }
    // DO NOT create a new category.  Die if it doesn't already exist.
    $sql = "select phenotype_category_uid from phenotype_category where phenotype_category_name = '$line[3]'";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    if(mysqli_num_rows($res) == 0) {
      echo "Category '$line[3]' in row $i doesn&apos;t exist yet.<br>";
      print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-2);\">";
      exit;
    }
    else {
      $r = mysqli_fetch_row($res);
      $cat_id = $r[0];
    }
    $pname = $line[1];
    $val_list = trim($line[2], ",");
    $desc = mysqli_real_escape_string($mysqli, $line[4]);

    //Add the new property.  First, check if already in database.
    $puid = mysql_grab("select properties_uid from properties where name = '$pname'");
    if (empty($puid))
      $sql = "insert into properties values (DEFAULT, $cat_id, '$pname', '$desc')";
    else {   // update
      $drds++;
      $sql = "update properties set name = '$pname', phenotype_category_uid = $cat_id, description = '$desc' where properties_uid = $puid";
    }
    mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli)."<br>Query was:<br>$sql");
    $puid = mysql_grab("select properties_uid from properties where name = '$pname'");
      
    //Now add the values to table property_values.
    $vals = explode(",", $val_list);
    foreach ($vals as $k=>$v)
      $vals[$k] = trim($v);
    // Get the existing values of this property.
    $loadedvals = array();
    $sql = "select property_values_uid, value from property_values where property_uid = $puid";
    $res = mysqli_query($mysqli, $sql) or die (mysqli_error($mysqli)."<br>Query was:<br>$sql");
    while ($r = mysqli_fetch_row($res)) {
      $valuid = $r[0];
      $loadedvals[] = $r[1];
      if (!in_array($r[1], $vals)) {
	// The user no longer wants it.  Okay to delete?
	// Check whether any line_properties have been loaded using the old values.
	$sql = "select line_properties_uid from line_properties where property_value_uid = $valuid";
	$res2 = mysqli_query($mysqli, $sql) or die (mysqli_error($mysqli)."<br>Query was:<br>$sql");
	$valused = mysqli_num_rows($res2);
	if ($valused > 0) 
	  $warns .= "$pname = $r[1]: <b>$valused</b><br>";
	else {
	  $sql = "delete from property_values where property_values_uid = $r[0]";
	  mysqli_query($mysqli, $sql) or die (mysqli_error($mysqli)."<br>Query was:<br>$sql");
	}
      }
    }
    // Are there new values to add?
    $newvals = array_diff($vals, $loadedvals);
    foreach ($newvals as $nv) {
      $sql = "insert into property_values values (DEFAULT, $puid, '$nv')";
      mysqli_query($mysqli, $sql) or die (mysqli_error($mysqli)."<br>Query was:<br>$sql");
    }
    $inum++;
  }
}
$newmax = getNumEntries("properties");
// Report the results.
echo "Properties found: <b>$inum</b><br>";
echo "Added: <b>".($newmax - $oldmax)."</b><br>";
echo "Updated: <b>$drds</b><p>";
if (!empty($warns))
    echo "The following values were not removed because they are assigned to the indicated number of lines.<br>$warns";

?>

</div> <!-- end boxContent -->

<input type="Button" value="Return" onClick="history.go(-2);">
</div>

<?php include "../theme/footer.php";?>
