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
	print "<h2>Storing the pedigrees from: " . basename($infilename) . "</h2>";
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

	$parr=array();
	$nform_flag=1;

	$oldmax = getNumEntries("pedigree_relations");
	$duplicates = 0;
	$inum = 0;

	$addedRcd=array();
	$dupRcd=array();
	/* Iterate through row starting at row 2 */
	for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++) {
		$line = array();

		/* Iterate through each column */
		for ($j = 1; $j <= $data->sheets[0]['numCols']; $j++) {

			if ($i==2) {
				//get column names
				$colnames[$j]=strtolower($data->sheets[0]['cells'][$i][$j]);
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

		// store the content of a line to the database
		// if the input is not in exemplar format, we will try our best to parse it
		if ($i!=2) {
			// first, identify the column indexes for different values in the line
			$lns=preg_grep('/indivi.*?|line.*?/i',$colnames);
			$pns=preg_grep('/parent.*?/i', $colnames);
			$cts=preg_grep('/contri.*?/i', $colnames);
			$sfs=preg_grep('/self.*?/i', $colnames);
			$cms=preg_grep('/commen.*?/i',$colnames);
			$lnidx=array_shift(array_keys($lns));
			$cmidx=array_shift(array_keys($cms)); // assume only one comment

		// next, test adding the line accession name,
           	// if it is not in the database, add a record, log this addition in $addedRcd
			if (! isset($line[$lnidx]) || $line[$lnidx]=='') continue;

			$ladd=add_attribute("line_record_name", $line[$lnidx], "line_records", "line_record_uid");
			$lineId = $ladd[1];

			if ($ladd[0]>0)
				array_push($addedRcd, $lineId);

			$pks=array_keys($pns);
			$cks=array_keys($cts);
			$sks=array_keys($sfs);

			for ($ii=0; $ii<count($pns); $ii++) {

				// for each parent, add the parent name, contribution, selfing and comment
				// when adding the parent name, test adding in the same way as for the line

				$pnidx=array_shift($pks);

				$padd=add_attribute("line_record_name", $line[$pnidx], "line_records", "line_record_uid");
				$parentId = $padd[1];

				if ($padd[0]>0)
					array_push($addedRcd, $parentId);

				if($ladd[0] < 0 || $padd[0] < 0) {
					// error(1, "Line or Parent is not specified on line $i");
					continue;
				}
				$ctidx=array_shift($cks);
				$sfidx=array_shift($sks);
				$vals = array("line_record_uid"=>$lineId, "parent_id"=>$parentId, "contribution"=>$line[$ctidx], "selfing"=>$line[$sfidx],"comments"=>$line[$cmidx]);
				$pladd=add_array_attributes($vals, array(1,1,1,0,0), "pedigree_relations", "1", "0", "");

				if ($pladd[0]==0)
					array_push($dupRcd, $pladd[1]);

				if(mysql_errno() == 1062) { 	//we have a duplicate
					$duplicates++;
				}
				else if(mysql_errno() > 0) {
					// error(1, mysql_error());
				}
			}
		}
		$preline = $line;

	}

	$newmax = getNumEntries("pedigree_relations");
	$_SESSION['SitualAddRecords']=$addedRcd;
	$_SESSION['DupPedRels']=$dupRcd;
	echo "<p>Successfully Added: " . ($newmax - $oldmax) . " new pedigrees</p>";
	print "<a href=\"edit_pedilines.php\">View and edit the situaltionally added lines</a>";
	print "<p>Number of duplicated entries that were ignored: ".$duplicates." </p>";
	print "<a href=\"edit_pedirels.php\">View and edit the duplicated pedigree relations</a>";
	print "<p>Number of invalid input entries: ". $inum ." </p>";

?>
			</div> <!-- end boxContent -->
		</div>

	<p><a href="login/">Go Home</a></p>
	</div>
</div>
</div>


<?php include("../theme/footer.php");?>
