<?php
// 6/25/2010 J.Lee  Make the back page url relative and not hardwired to server
// 12/14/2010 JLee  Change to use curator bootstrap

require 'config.php';
//require_once("../includes/common_import.inc");
/*
 * Logged in page initialization
 */
require $config['root_dir'] . 'includes/bootstrap_curator.inc';
//include($config['root_dir'] . 'includes/common_import.inc');

//include($config['root_dir'] . 'SumanDirectory/bootstrap_dev.inc');

//include($config['root_dir'] . 'SumanDirectory/annotations_link.php');

require_once "../lib/Excel/reader.php"; // Microsoft Excel library

connect();
loginTest();

/* ******************************* */
$row = loadUser($_SESSION['username']);

////////////////////////////////////////////////////////////////////////////////
ob_start();

authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();


new Annotations_Check($_GET['function']);

class Annotations_Check
{

    private $delimiter = "\t";
    
	
	// Using the class's constructor to decide which action to perform
	public function __construct($function = null)
	{	
		switch($function)
		{
			case 'typeDatabase':
				$this->type_Database(); /* update database */
				break;
				
			case 'typeLineData':
				$this->type_Line_Data(); /* Handle Line Data */
				break;
			
			default:
				$this->typeAnnotationCheck(); /* intial case*/
				break;
			
		}	
	}


private function typeAnnotationCheck()
	{
		global $config;
		include($config['root_dir'] . 'theme/admin_header.php');

		echo "<h2> Enter/Update Annotation Information: Validation</h2>"; 
		
			
		$this->type_Annotation();

		$footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
	}
	
	
	private function type_Annotation()
	{
	?>
	<script type="text/javascript">
	
	function update_database(filepath, filename, username, data_public_flag)
	{
			
			
			var url='<?php echo $_SERVER[PHP_SELF];?>?function=typeDatabase&linedata=' + filepath + '&file_name=' + filename + '&user_name=' + username + '&data_public_flag=' + data_public_flag;
	
			// Opens the url in the same window
	   	window.open(url, "_self");
	}
	
	
	
	
	</script>
	
	<style type="text/css">
			th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
			table {background: none; border-collapse: collapse}
			td {border: 0px solid #eee !important;}
			h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
		</style>
		
		<style type="text/css">
                   table.marker
                   {background: none; border-collapse: collapse}
                    th.marker
                    { background: #5b53a6; color: #fff; padding: 5px 0; border: 0; }
                    
                    td.marker
                    { padding: 5px 0; border: 0 !important; }
                </style>
		
		
		
<?php




  $row = loadUser($_SESSION['username']);
	
	ini_set("memory_limit","24M");
	
	$username=$row['name'];
	
	$tmp_dir="./uploads/tmpdir_".$username."_".rand();
	
//	$raw_path= "rawdata/".$_FILES['file']['name'][1];
//	copy($_FILES['file']['tmp_name'][1], $raw_path);
	umask(0);
	
	
	if(!file_exists($tmp_dir) || !is_dir($tmp_dir)) {
		mkdir($tmp_dir, 0777);
	}

	$target_path=$tmp_dir."/";

	if($_SERVER['REQUEST_METHOD'] == "POST")
	{
		$data_public_flag = $_POST['flag']; //1:yes, 0:no
		//echo" we got the value for data flag".$data_public_flag1;
	}
	
	if ($_FILES['file']['name'][0] == ""){
		error(1, "No File Uploaded");
		print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
	}
	else {
		
		$uploadfile=$_FILES['file']['name'][0];
	//	$rawdatafile = $_FILES['file']['name'][1];
				
		$uftype=$_FILES['file']['type'][0];
		if (strpos($uploadfile, ".xls") === FALSE) {
			error(1, "Expecting an Excel file. <br> The type of the uploaded file is ".$uftype);
			print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
		}
		else {
		
		
			if(move_uploaded_file($_FILES['file']['tmp_name'][0], $target_path.$uploadfile)) 
			{


    			/* start reading the excel */
		
		
		$annotfile = $target_path.$uploadfile;
		
						
		/* Read the annotation file */
	$reader = new Spreadsheet_Excel_Reader();
	$reader->setOutputEncoding('CP1251');
	if (strpos($annotfile,'.xls')>0)
	{
		$reader->read($annotfile);
	}else {
		$reader->read($annotfile . ".xls");
	}
	
	$annots = $reader->sheets[0];
	$cols = $reader->sheets[0]['numCols'];
	$rows = $reader->sheets[0]['numRows'];
	
	//	echo "nrows ".$rows." ncols ".$cols."\n";
	
	
	
	// find location for each row of data; find where data starts in file
	for ($i = 1; $i <= $rows; $i++) {
	  
	//	echo "annots 0 cells i 1 is set to ".$annots['cells'][$i][1]."\n";
	  if (stripos($annots['cells'][$i][1],'year')!==FALSE) {
		  $CAPYEAR = $i;
	  } elseif (stripos($annots['cells'][$i][1],'breeding')!==FALSE){
		$BREEDINGPROGRAM = $i;
	  } elseif (stripos($annots['cells'][$i][1],'location')!==FALSE){
		$LOCATION = $i;
	  } elseif (stripos($annots['cells'][$i][1],'long')!==FALSE){
		$LAT_LONG = $i;
	  } elseif (stripos($annots['cells'][$i][1],'collaborator code')!==FALSE){
		$COLLABORATORCODE = $i;
	  } elseif (stripos($annots['cells'][$i][1],'collaborator')!==FALSE){
		$COLLABORATOR = $i;
	  } elseif (stripos($annots['cells'][$i][1],'(short name)')!==FALSE){
		$EXPERIMENT_SHORTNAME = $i;
	  } elseif (stripos($annots['cells'][$i][1],'descriptive name')!==FALSE){
		$EXPERIMENT_NAME = $i;
	  } elseif (stripos($annots['cells'][$i][1],'trial code')!==FALSE){
		$TRIALCODE = $i;
	  } elseif (stripos($annots['cells'][$i][1],'planting')!==FALSE){
		$PLANTINGDATE = $i;
	  } elseif (stripos($annots['cells'][$i][1],'seeding')!==FALSE){
		$SEEDINGRATE = $i;
	  } elseif (stripos($annots['cells'][$i][1],'experimental')!==FALSE){
		$EXPERIMENTALDESIGN = $i;
	  } elseif (stripos($annots['cells'][$i][1],'entries')!==FALSE){
		$NUMBEROFENTRIES = $i;
	  } elseif (stripos($annots['cells'][$i][1],'replications')!==FALSE){
		$NUMBEROFREPLICATIONS = $i;
	  }elseif (stripos($annots['cells'][$i][1],'plot')!==FALSE){
		$PLOTSIZE = $i;
	  }elseif (stripos($annots['cells'][$i][1],'harvested')!==FALSE){
		$HARVESTEDAREA = $i;
	  }elseif (stripos($annots['cells'][$i][1],'irrigation')!==FALSE){
		$IRRIGATION = $i;
	  }elseif (stripos($annots['cells'][$i][1],'harvest date')!==FALSE){
		$HARVESTDATE = $i;
	  }elseif (stripos($annots['cells'][$i][1],'other')!==FALSE){
		$OTHERREMARKS = $i;
	  }
	}

		//echo "experiment".$EXPERIMENT_SHORTNAME."short name";
	//	echo $CAPYEAR." ".$BREEDINGPROGRAM." ".$LOCATION." ".$COLLABORATORCODE." ".$TRIALCODE." ".$OTHERREMARKS."\n";
		
		
		// Check if required rows are present
// Required rows are: CAPYear, breeding program, location, collaborator, collaborator code,
// Experiment (short name), Trial code, number of replications; If any of these column is missing (empty), then
// the annotation fil must be corrected

/*
 * Process the annotations file
 */
  
	
	$experiments = array();
	for ($i = 0; $i < ($cols-1); $i++)
	{
		$experiments[$i] = new experiment();
	}
	
	$year_row =					$annots['cells'][$CAPYEAR];
	$bp_row =						$annots['cells'][$BREEDINGPROGRAM];
	$location_row =				$annots['cells'][$LOCATION];
	$latlong_row =				$annots['cells'][$LAT_LONG];
	$collaborator_row =			$annots['cells'][$COLLABORATOR];
	$collabcode_row =			$annots['cells'][$COLLABORATORCODE];
	$trialcode_row =			$annots['cells'][$TRIALCODE];
	$plantingdate_row =			$annots['cells'][$PLANTINGDATE];
	$seedingrate_row =			$annots['cells'][$SEEDINGRATE];
	$experimentshortname_row =	$annots['cells'][$EXPERIMENT_SHORTNAME];
	$experimentname_row =	$annots['cells'][$EXPERIMENT_NAME];
	$experimentaldesign_row =	$annots['cells'][$EXPERIMENTALDESIGN];
	$numberofentries_row =	$annots['cells'][$NUMBEROFENTRIES];
	$numberofreplications_row =	$annots['cells'][$NUMBEROFREPLICATIONS];
	$plotsize_row =				$annots['cells'][$PLOTSIZE];
	$harvestedarea_row =		$annots['cells'][$HARVESTEDAREA];
	$irrigation_row =			$annots['cells'][$IRRIGATION];
	$harvestdate_row =		$annots['cells'][$HARVESTDATE];
	$otherremarks_row =			$annots['cells'][$OTHERREMARKS+$offset];
	
	//echo " we go tthe data from excel". $year_row.$trialcode_row.$seedingrate_row;
	
	//	connect_dev();	/* connecting to development database */
		
		
			$n_trials = 0;
	for ($i = 2; $cols >= $i; $i++)
	{
			//echo "i got year info". $year_row[$i];
			//echo " date string is".$harvestdate_row[$i];
			
			$error_flag = 0;
			//echo " testing the data".$experimentshortname_row[$i]."experiment name";
		// sometimes Excel introduces extra columns in the data files
		// stop reading at first column where year (required field) is zero.
		if (empty($year_row[$i]))
		{
		//	echo" i'm here";
			
			echo " Year is missing. Please enter the year and upload again"."<br/>";
				exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
			
			break;
		}
		$n_trials++;
		$index = $i - 2;
		
// Check key data fields in the experiment to ensure valid values
// Required fields are: year, CAP data program code (who performed experiment)
// a unique trial_code, experiment short name

		$experiments[$index]->year = intval(trim($year_row[$i]));
		$today = getdate();
		$curr_year = $today['year'];
		if (DEBUG>1) {echo "curr_year ".$curr_year." exp year: ".$experiments[$index]->year."\n";}
		if (($experiments[$index]->year<2006)OR ($year>$curr_year)) {
			echo "Year value not in range [2006-current year]: ".$year."<br/>";
			$error_flag = ($error_flag)&(1);
			exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
		}
		
		$experiments[$index]->collabcode = trim($collabcode_row[$i]);
		$CAPcode = $experiments[$index]->collabcode;
		$sql = "SELECT CAPdata_programs_uid FROM CAPdata_programs
					WHERE data_program_code= '$CAPcode'";
		$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
		if (1 == mysql_num_rows($res))
		{
			$row = mysql_fetch_assoc($res);
			$capdata_uid = $row['CAPdata_programs_uid'];
		}else{
			echo "CAP data program ID ".$CAPcode." does not exist "."<br/>";
			$error_flag = ($error_flag)&(2);
			exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
		}
		if (DEBUG>1) {
			echo "CAPcode ".$CAPcode." uid ".$capdata_uid."\n";
		}
		
		$trialcode = $experiments[$index]->trialcode = mysql_real_escape_string(trim($trialcode_row[$i]));
		
//	echo" the trial code is". $trialcode;	
		
		if (DEBUG>1) {echo "experiments trialcode [".$i."] is set to".$experiments[$index]->trialcode."\n";}
		// Trial code-verify not null, then see if it is in db AND unique
		if (is_null($trialcode)) {
			echo "Trial code ".$trialcode." is empty "."<br/>";
			$error_flag = ($error_flag)&(4);
			exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
		} else {
			
			$sql = "SELECT experiment_uid FROM experiments WHERE trial_code = '{$trialcode}'";
			$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
	
			if (mysql_num_rows($res)==1) //yes, experiment found once
			{
				$row = mysql_fetch_assoc($res);
				$exp_id = $row['experiment_uid'];
				if (DEBUG>1) {echo "exp ID ".$exp_id."\n";}
			} elseif (mysql_num_rows($res)>1) //yes, experiment found more than once, bad
			{
				if (DEBUG>1) {echo "Trial code ".$trialcode." linked to multiple experiments-must fix"."<br/>";}
				$error_flag = ($error_flag)&(8);
				exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
			}
		}
		
		
		$experiments[$index]->experimentshortname = mysql_real_escape_string(trim($experimentshortname_row[$i]));
		if (is_null($experiments[$index]->experimentshortname)) {
			echo "Short Name  is empty "."<br/>";
			$error_flag = ($error_flag)&(16);
			exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
		}
		
		$experiments[$index]->bp = trim($bp_row[$i]);
		if (DEBUG>1) {echo "experiments bp [".$i."] is set to".$experiments[$index]->bp."\n";}
		$experiments[$index]->location = addslashes($location_row[$i]);
		$experiments[$index]->latlong = mysql_real_escape_string($latlong_row[$i]);
		$experiments[$index]->collaborator = mysql_real_escape_string($collaborator_row[$i]);
		$experiments[$index]->collabcode = $collabcode_row[$i];

		// Planting Date
		$teststr= addcslashes(trim($plantingdate_row[$i]),"\0..\37!@\177..\377");
		if (DEBUG>2) {echo $teststr."  ".$datetime."\n";}
		if (!empty($teststr)){
			//if (DEBUG>2) {echo $teststr."\n";}
			
		//	echo "date string in planting date is". $teststr;
			//list($day,$month,$year) = split('[/.-]', $teststr);
			//$teststr="$day-$month-$year";
		//	echo "date string is". $teststr;
			
	//	$datetime = date_create($teststr);
		//	$datetime = date_format($datetime, 'j F Y'); 
			//if (DEBUG>2) {echo $teststr."  ".$datetime."\n";}
			$experiments[$index]->plantingdate = $teststr;
		} else {
			$experiments[$index]->plantingdate = '';
		}

	
	
		$experiments[$index]->seedingrate = $seedingrate_row[$i];
		

		$experiments[$index]->experimentname = mysql_real_escape_string(trim($experimentname_row[$i]));
		$experiments[$index]->experimentaldesign = mysql_real_escape_string($experimentaldesign_row[$i]);
		
		// Number of Replications
		$experiments[$index]->numberofentries = intval($numberofentries_row[$i]);
		$experiments[$index]->numberofreplications = intval($numberofreplications_row[$i]);
		
		// Plot Size
		$experiments[$index]->plotsize = mysql_real_escape_string($plotsize_row[$i]);
		
			// Harvest Date
		// convert Microsoft Excel timestamp to Unix timestamp
		$teststr= addcslashes(trim($harvestdate_row[$i]),"\0..\37!@\177..\377");
		if (DEBUG>2) {echo $teststr."\n";}
		
		if (!empty($teststr)){
			if (DEBUG>2) {echo $teststr."\n";}
			
			//echo "date string is". $teststr;
		//	list($day,$month,$year) = split('[/.-]', $teststr);
			//$teststr="$day-$month-$year";
			//echo "date string is". $teststr;
			
		//$datetime = date_create($teststr);
		//	$datetime = date_format($datetime, 'j F Y'); 
			//if (DEBUG>2) {echo $teststr."  ".$datetime."\n";}
			$experiments[$index]->harvestdate = $teststr;
		} else {
			$experiments[$index]->harvestdate = '';
		}

	
		// Harvest Area
		$experiments[$index]->harvestedarea = mysql_real_escape_string($harvestedarea_row[$i]);
		// irrigation
		if (FALSE !== stripos($irrigation_row[$i], "yes"))
		{
			$experiments[$index]->irrigation = 'yes';
		}
		else
		{
			$experiments[$index]->irrigation = 'no';
		}
		
		// Other Remarks
		$experiments[$index]->otherremarks = mysql_real_escape_string(htmlentities($otherremarks_row[$i]));
		
	}
	
	//echo "number of trials ".$n_trials."\n";
	
	if ($error_flag>0)  {
		echo "FATAL ERROR: problems with one or more required fields: year, trialcode, experiment short name or collaborator code"."<br/>";
		print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
	}
	
	else {

	// Insert data into experiments and phenotype_experiments  table
		
		?>
		
		<h3>We are reading following data from the uploaded Input Data File</h3>
		
		<table >
		<thead>
	<tr>
	<th >CAP Year</th>
	<th >Breeding Program(s) </th>
	<th >Location </th>
	<th  >Lat/Long of field </th>
	<th  >Collaborator </th>
	<th >Collaborator Code </th>
	<th  >Experiment </th>
	<th  >Descriptive Name of Experiment</th>
	<th  >Trial Code </th>
	<th >Planting date </th>
	<th  >Seeding rate (plants/m2) </th>
	<th >Experimental design</th>
	<th >Number of entries </th>
	<th  >Number of replications </th>
	<th  >Plot size (m2) </th>
	<th >Harvested area (m2) </th>
	<th  >Irrigation (yes or no) </th>
	<th  >Harvest date </th>
	<th  >Other remarks </th>
	</tr>
	<thead>
	
<tbody style="padding: 0; height: 200px; width: 1600px;  overflow: scroll;border: 1px solid #5b53a6;">	

			<?php
				for ($i = 2; $cols >= $i; $i++)
				{
				//Extract data
			
			?>
			
			<tr>
			<td >


			<?php 
			$newtext = wordwrap($year_row[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			<td >
			<?php
			$newtext1 = wordwrap($bp_row[$i], 6, "\n", true);
			echo $newtext1 ?>
			</td>
			<td>
			<?php 
			$newtext2 = wordwrap($location_row[$i], 6, "\n", true);
			echo $newtext2 ?>
			</td> 
			<td >
			<?php 
			$newtext = wordwrap($latlong_row[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			<td >
			<?php 
			$newtext = wordwrap($collaborator_row[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			<td >
			<?php 
			$newtext = wordwrap($collabcode_row[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			<td >
			<?php 
			$newtext = wordwrap($experimentshortname_row[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			<td >
			<?php 
			$newtext = wordwrap($experimentname_row[$i], 6, "\n", true);
			echo $newtext ?>
			</td>
			<td >
			<?php 
			$newtext = wordwrap($trialcode_row[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			<td >
			<?php 
			$newtext = wordwrap($plantingdate_row[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			<td >
			<?php 
			$newtext = wordwrap($seedingrate_row[$i], 6, "\n", true);
			echo $newtext ?>
			</td>
				
			<td >
			<?php 
			$newtext = wordwrap($experimentaldesign_row[$i], 6, "\n", true);
			echo $newtext ?>
			</td>
			<td >
			<?php 
			$newtext = wordwrap($numberofentries_row[$i], 6, "\n", true);
			echo $newtext ?>
			</td>
			<td >
			<?php 
			$newtext = wordwrap($numberofreplications_row[$i], 6, "\n", true);
			echo $newtext ?>
			</td>
			<td >
			<?php 
			$newtext = wordwrap($plotsize_row[$i], 6, "\n", true);
			echo $newtext ?>
			</td>
			<td >
			<?php 
			$newtext = wordwrap($harvestedarea_row[$i], 6, "\n", true);
			echo $newtext ?>
			</td>
			<td >
			<?php 
			$newtext = wordwrap($irrigation_row[$i], 6, "\n", true);
			echo $newtext ?>
			</td>
			<td >
			<?php 
			$newtext = wordwrap($harvestdate_row[$i], 6, "\n", true);
			echo $newtext ?>
			</td>
			<td >
			<?php 
			$newtext = wordwrap($otherremarks_row[$i], 6, "\n", true);
			echo htmlentities($newtext) ?>
			</td>
			</tr>
			<?php
				}/* end of for loop */
			?>
			</tbody>
			</table>
			
		
		
		<input type="Button" value="Accept" onclick="javascript: update_database('<?php echo $annotfile?>','<?php echo $uploadfile?>','<?php echo $username?>','<?php echo $data_public_flag?>' )"/>
    <input type="Button" value="Cancel" onclick="history.go(-1); return;"/>
	
		<?php
		
	
	}
			
   			}
				 
				 else {
    				error(1,"There was an error uploading the file, please try again!");
							}
			
		}
	
	}

	} /* end of type_GenoType_Display function*/
	
	private function type_Database()
	{
	
	global $config;
		include($config['root_dir'] . 'theme/admin_header.php');

	
	//connect_dev();
	
	$datafile = $_GET['linedata'];
	$filename_old = $_GET['file_name'];
	$filename = $filename_old.rand();
	$username = $_GET['user_name'];
	$data_public_flag = $_GET['data_public_flag'];

	$reader = new Spreadsheet_Excel_Reader();
	$reader->setOutputEncoding('CP1251');
	if (strpos($datafile,'.xls')>0)
	{
		$reader->read($datafile);
	}else {
		$reader->read($datafile . ".xls");
	}
	
	$annots = $reader->sheets[0];
	$cols = $reader->sheets[0]['numCols'];
	$rows = $reader->sheets[0]['numRows'];
	
	
	
	
	
	// find location for each row of data; find where data starts in file
	for ($i = 1; $i <= $rows; $i++) {
	  
  if (stripos($annots['cells'][$i][1],'year')!==FALSE) {
		  $CAPYEAR = $i;
	  } elseif (stripos($annots['cells'][$i][1],'breeding')!==FALSE){
		$BREEDINGPROGRAM = $i;
	  } elseif (stripos($annots['cells'][$i][1],'location')!==FALSE){
		$LOCATION = $i;
	  } elseif (stripos($annots['cells'][$i][1],'long')!==FALSE){
		$LAT_LONG = $i;
	  } elseif (stripos($annots['cells'][$i][1],'collaborator code')!==FALSE){
		$COLLABORATORCODE = $i;
	  } elseif (stripos($annots['cells'][$i][1],'collaborator')!==FALSE){
		$COLLABORATOR = $i;
	  } elseif (stripos($annots['cells'][$i][1],'(short name)')!==FALSE){
		$EXPERIMENT_SHORTNAME = $i;
	  } elseif (stripos($annots['cells'][$i][1],'descriptive name')!==FALSE){
		$EXPERIMENT_NAME = $i;
	  } elseif (stripos($annots['cells'][$i][1],'trial code')!==FALSE){
		$TRIALCODE = $i;
	  } elseif (stripos($annots['cells'][$i][1],'planting')!==FALSE){
		$PLANTINGDATE = $i;
	  } elseif (stripos($annots['cells'][$i][1],'seeding')!==FALSE){
		$SEEDINGRATE = $i;
	  } elseif (stripos($annots['cells'][$i][1],'experimental')!==FALSE){
		$EXPERIMENTALDESIGN = $i;
	  } elseif (stripos($annots['cells'][$i][1],'entries')!==FALSE){
		$NUMBEROFENTRIES = $i;
	  } elseif (stripos($annots['cells'][$i][1],'replications')!==FALSE){
		$NUMBEROFREPLICATIONS = $i;
	  }elseif (stripos($annots['cells'][$i][1],'plot')!==FALSE){
		$PLOTSIZE = $i;
	  }elseif (stripos($annots['cells'][$i][1],'harvested')!==FALSE){
		$HARVESTEDAREA = $i;
	  }elseif (stripos($annots['cells'][$i][1],'irrigation')!==FALSE){
		$IRRIGATION = $i;
	  }elseif (stripos($annots['cells'][$i][1],'harvest date')!==FALSE){
		$HARVESTDATE = $i;
	  }elseif (stripos($annots['cells'][$i][1],'other')!==FALSE){
		$OTHERREMARKS = $i;
	  }
	}
	
		
		
		
		
		// Check if required rows are present
// Required rows are: CAPYear, breeding program, location, collaborator, collaborator code,
// Experiment (short name), Trial code, number of replications; If any of these column is missing (empty), then
// the annotation fil must be corrected



/*
 * Process the annotations file
 */
	
	$experiments = array();
	for ($i = 0; $i < ($cols-1); $i++)
	{
		$experiments[$i] = new experiment();
	}
	
	$year_row =					$annots['cells'][$CAPYEAR];
	$bp_row =						$annots['cells'][$BREEDINGPROGRAM];
	$location_row =				$annots['cells'][$LOCATION];
	$latlong_row =				$annots['cells'][$LAT_LONG];
	$collaborator_row =			$annots['cells'][$COLLABORATOR];
	$collabcode_row =			$annots['cells'][$COLLABORATORCODE];
	$trialcode_row =			$annots['cells'][$TRIALCODE];
	$plantingdate_row =			$annots['cells'][$PLANTINGDATE];
	$seedingrate_row =			$annots['cells'][$SEEDINGRATE];
	$experimentshortname_row =	$annots['cells'][$EXPERIMENT_SHORTNAME];
	$experimentname_row =	$annots['cells'][$EXPERIMENT_NAME];
	$experimentaldesign_row =	$annots['cells'][$EXPERIMENTALDESIGN];
	$numberofentries_row =	$annots['cells'][$NUMBEROFENTRIES];
	$numberofreplications_row =	$annots['cells'][$NUMBEROFREPLICATIONS];
	$plotsize_row =				$annots['cells'][$PLOTSIZE];
	$harvestedarea_row =		$annots['cells'][$HARVESTEDAREA];
	$irrigation_row =			$annots['cells'][$IRRIGATION];
	$harvestdate_row =		$annots['cells'][$HARVESTDATE];
	$otherremarks_row =			$annots['cells'][$OTHERREMARKS+$offset];
	
		//connect_dev();	/* connecting to development database */
		
			$n_trials = 0;
	for ($i = 2; $cols >= $i; $i++)
	{
	
			
		// sometimes Excel introduces extra columns in the data files
		// stop reading at first column where year (required field) is zero.
		if (empty($year_row[$i]))
		{
			break;
		}
		$n_trials++;
		$index = $i - 2;
		
		$error_flag = 0;
// Check key data fields in the experiment to ensure valid values
// Required fields are: year, CAP data program code (who performed experiment)
// a unique trial_code, experiment short name

		$experiments[$index]->year = intval(trim($year_row[$i]));
		$today = getdate();
		$curr_year = $today['year'];
		if (DEBUG>1) {echo "curr_year ".$curr_year." exp year: ".$experiments[$index]->year."\n";}
		if (($experiments[$index]->year<2006)OR ($year>$curr_year)) {
			echo "Year value not in range [2006-current year]: ".$year."\n";
			$error_flag = ($error_flag)&(1);
		}
		
		$experiments[$index]->collabcode = trim($collabcode_row[$i]);
		$CAPcode = $experiments[$index]->collabcode;
		$sql = "SELECT CAPdata_programs_uid FROM CAPdata_programs
					WHERE data_program_code= '$CAPcode'";
		$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
		if (1 == mysql_num_rows($res))
		{
			$row = mysql_fetch_assoc($res);
			$capdata_uid = $row['CAPdata_programs_uid'];
		}else{
			echo "CAP data program ID ".$CAPcode." does not exist \n";
			$error_flag = ($error_flag)&(2);
		}
		if (DEBUG>1) {
			echo "CAPcode ".$CAPcode." uid ".$capdata_uid."\n";
		}
		
		$trialcode = $experiments[$index]->trialcode = mysql_real_escape_string(trim($trialcode_row[$i]));
		if (DEBUG>1) {echo "experiments trialcode [".$i."] is set to".$experiments[$index]->trialcode."\n";}
		// Trial code-verify not null, then see if it is in db AND unique
		if (is_null($trialcode)) {
			echo "Trial code ".$trialcode." is empty \n";
			$error_flag = ($error_flag)&(4);
		} else {
			
			$sql = "SELECT experiment_uid FROM experiments WHERE trial_code = '{$trialcode}'";
			$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
	
			if (mysql_num_rows($res)==1) //yes, experiment found once
			{
				$row = mysql_fetch_assoc($res);
				$exp_id = $row['experiment_uid'];
				if (DEBUG>1) {echo "exp ID ".$exp_id."\n";}
			} elseif (mysql_num_rows($res)>1) //yes, experiment found more than once, bad
			{
				if (DEBUG>1) {echo "Trial code ".$trialcode." linked to multiple experiments-must fix\n";}
				$error_flag = ($error_flag)&(8);
			}
		}
		
		
		$experiments[$index]->experimentshortname = mysql_real_escape_string(trim($experimentshortname_row[$i]));
		if (is_null($experiments[$index]->experimentshortname)) {
			echo "Short Name  is empty \n";
			$error_flag = ($error_flag)&(16);
		}
		
		$experiments[$index]->bp = trim($bp_row[$i]);
		if (DEBUG>1) {echo "experiments bp [".$i."] is set to".$experiments[$index]->bp."\n";}
		$experiments[$index]->location = addslashes($location_row[$i]);
		$experiments[$index]->latlong = mysql_real_escape_string($latlong_row[$i]);
		$experiments[$index]->collaborator = mysql_real_escape_string($collaborator_row[$i]);
		$experiments[$index]->collabcode = $collabcode_row[$i];

		// Planting Date
		$teststr= addcslashes(trim($plantingdate_row[$i]),"\0..\37!@\177..\377");
		if (DEBUG>2) {echo $teststr."  ".$datetime."\n";}
		if (!empty($teststr)){
			if (DEBUG>2) {echo $teststr."\n";}
			
			//echo "date string is". $teststr;
			//list($day,$month,$year) = split('[/.-]', $teststr);
			//$teststr="$day-$month-$year";
			//echo "date string is". $teststr;
			
		//$datetime = date_create($teststr);
		//	$datetime = date_format($datetime, 'j F Y'); 
			//if (DEBUG>2) {echo $teststr."  ".$datetime."\n";}
			$experiments[$index]->plantingdate = $teststr;
		} else {
			$experiments[$index]->plantingdate = '';
		}

	
	
		$experiments[$index]->seedingrate = $seedingrate_row[$i];
		

		$experiments[$index]->experimentname = mysql_real_escape_string(trim($experimentname_row[$i]));
		$experiments[$index]->experimentaldesign = mysql_real_escape_string($experimentaldesign_row[$i]);
		
		// Number of Replications
		$experiments[$index]->numberofentries = intval($numberofentries_row[$i]);
		$experiments[$index]->numberofreplications = intval($numberofreplications_row[$i]);
		
		// Plot Size
		$experiments[$index]->plotsize = mysql_real_escape_string($plotsize_row[$i]);
		
			// Harvest Date
		// convert Microsoft Excel timestamp to Unix timestamp
		$teststr= addcslashes(trim($harvestdate_row[$i]),"\0..\37!@\177..\377");
		if (DEBUG>2) {echo $teststr."\n";}
		
		if (!empty($teststr)){
			if (DEBUG>2) {echo $teststr."\n";}
			
			//echo "date string is". $teststr;
			//list($day,$month,$year) = split('[/.-]', $teststr);
			//$teststr="$day-$month-$year";
			//echo "date string is". $teststr;
			
		//$datetime = date_create($teststr);
		//	$datetime = date_format($datetime, 'j F Y'); 
			//if (DEBUG>2) {echo $teststr."  ".$datetime."\n";}
			$experiments[$index]->harvestdate = $teststr;
		} else {
			$experiments[$index]->harvestdate = '';
		}
		
		
	
		// Harvest Area
		$experiments[$index]->harvestedarea = mysql_real_escape_string($harvestedarea_row[$i]);
		// irrigation
		if (FALSE !== stripos($irrigation_row[$i], "yes"))
		{
			$experiments[$index]->irrigation = 'yes';
		}
		else
		{
			$experiments[$index]->irrigation = 'no';
		}
		
		// Other Remarks
		$experiments[$index]->otherremarks = mysql_real_escape_string(htmlentities($otherremarks_row[$i]));
		
	}
	
		
	if ($error_flag>0) {
		echo "FATAL ERROR: problems with one or more required fields: year, trialcode, experiment short name or collaborator code";
		print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
	}
	
	else {

	// Insert data into experiments and phenotype_experiments  table
		$myind=0;
		$experiments_real = array();
		for ($i = 0; $i < $n_trials; $i++)
		{
			$experiments_real[$i] = $experiments[$i];
		}
	
	
		foreach ($experiments_real as $experiment)
		{
			// Get CAPdata collaborator code id
			$CAPcode = $experiments[$myind]->collabcode;
		
		
			$sql = "SELECT CAPdata_programs_uid FROM CAPdata_programs
							WHERE data_program_code= '$CAPcode'";
			$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
			if (1 == mysql_num_rows($res))
			{
				$row = mysql_fetch_assoc($res);
				$capdata_uid = $row['CAPdata_programs_uid'];
			}
		
		
		// Get code for phenotype experiments
			$sql = "SELECT experiment_type_uid FROM experiment_types
							WHERE experiment_type_name = 'phenotype'";
			$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
			$row = mysql_fetch_assoc($res);
			$exptype_id = $row['experiment_type_uid'];
			
			// Insert or update experiment table data
			// First check if this trial code is in the database, if yes, then update all fields;
			// if no then insert into table
			$sql = "SELECT experiment_uid FROM experiments WHERE trial_code = '{$experiment->trialcode}'";
			$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
			
			if (mysql_num_rows($res)!==0) //yes, experiment found, so update
			{
					$row = mysql_fetch_assoc($res);
					$exp_id = $row['experiment_uid'];
					if (DEBUG>1) {echo "exp ID ".$exp_id."\n";}
		
					//update experiment information
					$sql = "UPDATE experiments
								SET
									experiment_type_uid = $exptype_id,
									CAPdata_programs_uid = $capdata_uid,
									experiment_short_name = '{$experiment->experimentshortname}',
									experiment_desc_name = '{$experiment->experimentname}',
									trial_code = '{$experiment->trialcode}',
									experiment_year = '{$experiment->year}',
									data_public_flag = '$data_public_flag',
									created_on = NOW()
								WHERE experiment_uid = $exp_id";
					if (DEBUG>2) {echo "update exp SQL ".$sql."\n";}
					
					mysql_query($sql) or die(mysql_error() . "<br>$sql");
					//update phenotype experiment information
					$sql = " UPDATE phenotype_experiment_info
								set
									collaborator = '{$experiment->collaborator}',
									planting_date = '{$experiment->plantingdate}',
									seeding_rate = '{$experiment->seedingrate}',
									experiment_design = '{$experiment->experimentaldesign}',
									number_replications = '{$experiment->numberofreplications}',
									number_entries = '{$experiment->numberofentries}',
									plot_size = '{$experiment->plotsize}',
									harvest_area = '{$experiment->harvestedarea}',
									harvest_date = '{$experiment->harvestdate}',
									irrigation = '{$experiment->irrigation}',
									other_remarks = '{$experiment->otherremarks}',
									location = '{$experiment->location}',
									latitude_longitude = '{$experiment->latlong}',
									created_on = NOW()
								WHERE experiment_uid = $exp_id";
					if (DEBUG>2) {echo "update phenotypeexp SQL ".$sql."\n";}
					
					mysql_query($sql) or die(mysql_error() . "<br>$sql");
			} else {
					$sql = "
						insert into
							experiments
						set
							experiment_type_uid = $exptype_id,
							CAPdata_programs_uid = $capdata_uid,
							experiment_short_name = '{$experiment->experimentshortname}',
							experiment_desc_name = '{$experiment->experimentname}',
							trial_code = '{$experiment->trialcode}',
							experiment_year = '{$experiment->year}',
							data_public_flag = '$data_public_flag',
							created_on = NOW()
					";
					//if (DEBUG>2) {echo "insert exp SQL ".$sql."\n";}
					
					mysql_query($sql) or die(mysql_error());
					
					//get experiment_uid set genotype experiments info table
					$sql = "SELECT experiment_uid FROM experiments
									WHERE trial_code = '{$experiment->trialcode}' limit 1";
					$res = mysql_query($sql) or die(mysql_error());
					$row = mysql_fetch_assoc($res);
					$exp_id = $row['experiment_uid'];
				//	if (DEBUG>1) {echo "exp ID ".$exp_id."\n";}
					$sql = "
						insert into
							phenotype_experiment_info
						set
							experiment_uid = $exp_id,
							collaborator = '{$experiment->collaborator}',
							planting_date = '{$experiment->plantingdate}',
							seeding_rate = '{$experiment->seedingrate}',
							experiment_design = '{$experiment->experimentaldesign}',
							number_replications = '{$experiment->numberofreplications}',
							number_entries = '{$experiment->numberofentries}',
							plot_size = '{$experiment->plotsize}',
							harvest_area = '{$experiment->harvestedarea}',
							harvest_date = '{$experiment->harvestdate}',
							irrigation = '{$experiment->irrigation}',
							other_remarks = '{$experiment->otherremarks}',
							location = '{$experiment->location}',
							latitude_longitude = '{$experiment->latlong}',
							created_on = NOW()
					";
				//	if (DEBUG>2) {echo "insert phenotype exp SQL ".$sql."\n";}
					mysql_query($sql) or die(mysql_error());
				
				
			
			} 
		}// end foreach
	}
		

	
	echo " <b>The Data is inserted/updated successfully </b>";
	echo"<br/><br/>";
	?>
	<a href="./curator_data/input_annotations_upload.php"> Go Back To Main Page </a>
	<?php
	
	$sql = "INSERT INTO input_file_log (file_name,users_name)
										VALUES('$filename', '$username')";
					
					
	$lin_table=mysql_query($sql) or die(mysql_error());




		$footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
	
	
		
	} /* end of function type_database */


} /* end of class */


class experiment
	{
		var $year;
		var $bp;
		var $location;
		var $latlong;
		var $collaborator;
		var $collabcode;
		var $descname_exp;
		var $trialcode;
		var $plantingdate;
		var $seedingrate;
		var $experimentshortname;
		var $experimentname;
		var $experimentaldesign;
		var $number_entries;
		var $numberofreplications;
		var $plotsize;
		var $harvestedarea;
		var $irrigation;
		var $harvestdate;
		var $otherremarks;
	}

?>

