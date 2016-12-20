<?php
require 'config.php';
//*********************************************
// 6feb13 dem: Don't use this input file to create new trait Categories or Units.
// 8/18/11  DEM   Fixed bug in call to add_array_attributes().
// 6/24/11  JLee  Fix to use the generic Excel parser
//                & population of input records
//*********************************************

include("../includes/bootstrap_curator.inc");
include("../theme/admin_header.php");
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
print "<h2>Storing the traits from: " . basename($infilename) . "</h2>";
print "<div class=\"boxContent\">";

require_once "../lib/Excel/reader.php"; //include excel reader

/* Creating the object */
$data = new Spreadsheet_Excel_Reader();
$data->setOutputEncoding('CP1251');
$data->read($infilename);

//$data->trimSheet(0); 	//new function that I added to trim columns

/* Setting Error Reporting */
error_reporting(E_ALL ^ E_NOTICE);

/* Parse the Sheet */
$colnames=array();
$preline=array();

$oldmax = getNumEntries("phenotypes");
$drds = array(); // ids of duplicated phenotypes
$inum = 0;

/* Iterate through row starting at row 1 */
for ($i = 1; $i <= $data->sheets[0]['numRows']; $i++) {
  $line=array();

  /* Iterate through each column */
  for ($j = 1; $j <= $data->sheets[0]['numCols']; $j++) {
    if ($i == 1) { //First column?
      //get column names
      $colnames[$j]=$data->sheets[0]['cells'][$i][$j];
    }
    else {
      $ele=trim($data->sheets[0]['cells'][$i][$j]);
      //special "Same As Above" check
      if (preg_match('/same\sas\sabove/',$ele) || $ele=="saa") {
	$line[$j]=$preline[$j];
      }
      else {
	$line[$j]=$ele;
      }
    }
  }

  //after iterating through columns, if this is not the first row we check...
  if ($i!=1) {
    //Category ID
    // DO NOT create a new category.  Die if it doesn't already exist.
    /* $cat_id = array_pop(add_attribute("phenotype_category_name",$line[1],"phenotype_category", "phenotype_category_uid")); */
    $sql = "select phenotype_category_uid from phenotype_category where phenotype_category_name = '$line[1]'";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    if(mysqli_num_rows($res) == 0) {
      echo "Trait Category '$line[1]' in row $i is invalid.<br>";
      print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-2);\">";
      exit;
    }
    else {
      $row = mysqli_fetch_row($res);
      $cat_id = $row[0];
    }

    //Unit ID
    // DO NOT create a new unit.  Die if it doesn't already exist.
    /* $unit_id = array_pop(add_attribute("unit_name", addslashes($line[4]),"units", "unit_uid")); */
    $sql = "select unit_uid from units where unit_name = '$line[4]'";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    if(mysqli_num_rows($res) == 0) {
      echo "Unit '$line[4]' in row $i is invalid.<br>";
      print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-2);\">";
      exit;
    }
    else {
      $row = mysqli_fetch_row($res);
      $unit_id = $row[0];
    }

    //Attach category name to the front of phenotype name and replace all spaces with "_"
    $cline = explode(' ',$line[1]);
    //$pname = $cline[0]."_".implode("_",explode(' ',$line[2]));
    $pname = $line[2];
    $minVal = $line[7];
    $maxVal = $line[8];

    //Add the new phenotype
    if (strtoupper($line[5]) == 'TEXT') {
      $keyarr = array('phenotype_category_uid'=>$cat_id, 'unit_uid'=>$unit_id, 'phenotypes_name'=>$pname,'alternate_name' => addslashes($line[3]),'description'=>addslashes($line[6]),'TO_number'=>strtoupper($line[9]), 'datatype' => $line[5]);
      $isnum = array(1,1,0,0,0,0,0);
    } else {
      $keyarr = array('phenotype_category_uid'=>$cat_id, 'unit_uid'=>$unit_id, 'phenotypes_name'=>$pname,'alternate_name' => addslashes($line[3]),'description'=>addslashes($line[6]),'min_pheno_value'=>$minVal,'max_pheno_value'=>$maxVal,'TO_number'=>strtoupper($line[9]), 'datatype' => $line[5]);
      $isnum = array(1,1,0,0,0,1,1,0,0);
    }
    // Insert into MySQL database. Function defined in includes/common.inc.
    // 			$pres = add_array_attributes($keyarr, $isnum, "phenotypes", "phenotypes_name", $pname, "phenotype_uid");
    $pres = add_array_attributes($keyarr, $isnum, "phenotypes", "phenotypes_name", $pname, "phenotypes_name");
    if ($pres[0] < 0) {
      $inum+=1;
    }
    elseif ($pres[0] == 0) {
      array_push($drds, $pres[1]);
    }
    else {
    }
  }
  // In PHP we can get away with this. We don't have to write out a deep copy. :)
  $preline = $line;
}
$newmax = getNumEntries("phenotypes");

echo "<p>Successfully Added: " . ($newmax - $oldmax) . " new traits</p>";
print "<p>Number of duplicated entries: ".count($drds)."   <br /><a href=\"login/edit_traits.php\"> View and Edit these traits. </a> </p>";
$_SESSION['DupTraitRecords']=$drds;
print "<p>Number of invalid input entries: $inum </p>";

?>

</div> <!-- end boxContent -->

<p><a href="login/">Go Home</a></p>
</div>


<?php include "../theme/footer.php";?>
