<?php
require 'config.php';
/*
 * Logged in page initialization
 */
include("../includes/bootstrap.inc");

connect();
loginTest();

/* ******************************* */
$row = loadUser($_SESSION['username']);

/* ****************************** */
////////////////////////////////////////////////////////////////////////////////
ob_start();
include("../theme/admin_header.php");
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();
////////////////////////////////////////////////////////////////////////////////

?>

<div id="primaryContentContainer">
	<div id="primaryContent">
  		<div class="box">

<?php
	$infilename = $_POST['infilename'];
	$tmpdir=$_post['tmpdir'];
	print "<h2>Storing the traits from: " . basename($infilename) . "</h2>";
	print "<div class=\"boxContent\">";

	require_once("../includes/excel/reader.php");	//include excel reader

	/* Creating the object */
	$data = new Spreadsheet_Excel_Reader();
	$data->setOutputEncoding('CP1251');
	$data->read($infilename);

	$data->trimSheet(0); 	//new function that I added to trim columns

	/* Setting Error Reporting */
	error_reporting(E_ALL ^ E_NOTICE);

	/* Parse the Sheet */
	$colnames=array();
	$preline=array();

	$oldmax = getNumEntries("phenotypes");
	$drds = array(); // ids of duplicated phenotypes
	$inum = 0;

	/* Iterate through row starting at row 2 */
	for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++) {
		$line=array();

		/* Iterate through each column */
		for ($j = 1; $j <= $data->sheets[0]['numCols']; $j++) {

			if ($i==2) { //First column?
				//get column names
				$colnames[$j]=$data->sheets[0]['cells'][$i][$j];
			}
			else {
				$ele=trim(strtolower($data->sheets[0]['cells'][$i][$j]));

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
		if ($i!=2) {

			//Category ID
			$cat_id = array_pop(add_attribute("phenotype_category_name",$line[1],"phenotype_category", "phenotype_category_uid"));

			//Unit ID
			$unit_id = array_pop(add_attribute("unit_name", addslashes($line[3]),"units", "unit_uid"));

			//Attach category name to the front of phenotype name and replace all spaces with "_"
			$cline = explode(' ',$line[1]);
			//$pname = $cline[0]."_".implode("_",explode(' ',$line[2]));
			$pname = $line[2];

			//Add the new phenotype
			$keyarr = array('phenotype_category_uid'=>$cat_id, 'unit_uid'=>$unit_id, 'phenotypes_name'=>$pname, 'description'=>addslashes($line[4]));
			$isnum = array(1,1,0,0);
			$pres = add_array_attributes($keyarr, $isnum, "phenotypes", "phenotypes_name", $pname, "phenotype_uid");
			if ($pres[0]<0) {
				$inum+=1;
			}
			elseif ($pres[0]==0) {
				array_push($drds, $pres[1]);
			}
			else {
			}
			//Add the gramene
			if($line[8] != "" && $pres[0]>0) {
				$gkeys = array('gramene_uid'=>$line[8], 'phenotype_uid'=>$pres[1], 'term'=>$line[7], 'definition'=>$line[9]);
				$isnum = array(0,1,0,0);
				$gins = add_array_attributes($gkeys, $isnum, "gramene", "gramene_uid", $line[8], "gramene_uid");
				// print "<p> ".implode(" ",$gins)."</p>";
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

	// move things to the upload directory
	// delete the temporary directory

	clean_up_temporary($tmpdir);
?>

			</div> <!-- end boxContent -->
		</div>

	<p><a href="login/">Go Home</a></p>
	</div>
</div>
</div>


<?php include("../theme/footer.php");?>