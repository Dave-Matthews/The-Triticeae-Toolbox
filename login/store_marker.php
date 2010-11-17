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
	$mapset = $_POST['MapsetID'];

	print "<h2>Storing the markers from: " . basename($infilename) . "</h2><div class=\"boxContent\">";

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

	$oldmax = getNumEntries("markers");
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

			/*
			 * Dealing with the Map
			 */
			$mapname = $mapset . "_" . $line[2];
			$values = array(
				'mapset_id'=>$mapset,
				'map_name'=>$mapname,
				'map_start'=>$line[3],
				'map_end'=>$line[4],
				'created_on'=>'NOW()',
				'updated_on'=>'NOW()'
				);

			$map = add_array_attributes($values, array(1,0,1,1,1,1), "map", "map_name", $mapname, "map_id");
			/*
			 * Dealing with the Marker Type  (10)
			 */
			$markType = array_pop(add_attribute("description", $line[10], "marker_types", "marker_type_uid"));

			/*
			 * Dealing with the Unigene Stuff (12)
			 */
			$unigene = array_pop(add_attribute("unigene_name", $line[12], "unigene", "unigene_uid"));

			/*
			 * Dealing with the Markers (5,6,7)
			 */

			$values = array(
				'marker_type_uid'=>$markType,
				'unigene_uid'=>$unigene,
				'marker_name'=>$line[6],
				'access_id'=>$line[5],
				'alias'=>$line[7],
				'created_on'=>'NOW()',
				'updated_on'=>'NOW()'
				);

			$marker = add_array_attributes($values, array(1,1,0,1,0,1,1), "markers", "marker_name", $line[6], "marker_uid");

			if ($marker[0] < 0) {	//invalid
				$inum += 1;
			}
			elseif ($marker[0] == 0) {	//duplicate
				array_push($drds, $marker[1]);
			}


			/*
			 * Dealing with the Markers in Map Table   (8,9, 13,14)
			 */

			$values = array(
				'marker_uid'=>$marker[1],
				'map_id'=>$map[1],
				'start_position'=>$line[8],
				'end_position'=>$line[9],
				'bin_name'=>$line[13],
				'chromosome'=>$line[14]
				);

			$relation = add_array_attributes($values, array(1,1,1,1,0,0), "markers_in_maps", "1", "0", "marker_uid");
			$err = mysql_errno();

			if($err == 1062) {
				//duplicate
			}

		}

		// In PHP we can get away with this. We don't have to write out a deep copy. :)
		$preline = $line;

	}

	$newmax = getNumEntries("markers");

	echo "<p>Successfully Added: " . ($newmax - $oldmax) . " new markers</p>";
	print "<p>Number of duplicated entries: ".count($drds)."   <br /><a href=\"login/edit_markers.php\"> View and Edit these markers. </a> </p>";
	$_SESSION['DupMakerRecords']=$drds;
	print "<p>Number of invalid input entries: $inum </p>";

?>

	</div><!-- end boxContent -->

	<p><a href="login/">Go Home</a></p>
		</div><!-- end box -->
	</div>
</div>
</div>

<?php include("../theme/footer.php");?>
