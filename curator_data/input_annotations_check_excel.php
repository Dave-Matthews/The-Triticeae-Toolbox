<?

// 09/01/2011 CBirkett	changed to new template and schema
// 01/25/2011 JLee  Check 'number of entries' and 'number of replition' input values 

define("DEBUG",0);
require 'config.php';
//require_once("../includes/common_import.inc");
/*
 * Logged in page initialization
 */
include($config['root_dir'] . 'includes/bootstrap_curator.inc');
include($config['root_dir'] . 'curator_data/lineuid.php');

require_once("../lib/Excel/excel_reader2.php"); // Microsoft Excel library

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
			
			
			var url='<?php echo $_SERVER[PHP_SELF];?>?function=typeDatabase&linedata=' + filepath + '&file_name=' + filename + '&user_name=' + username + '&public=' + data_public_flag;
	
			// Opens the url in the same window
	   	window.open(url, "_self");
	}
	
	
	
	
	</script>
	
	<style type="text/css">
			th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6; white-space: nowrap }
			table {background: none; border-collapse: collapse}
			td {border: 0px solid #eee !important; white-space: nowrap}
			h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
		</style>
		
		<style type="text/css">
                   table.marker
                   {background: none; border-collapse: collapse}
                    th.marker
                    { background: #5b53a6; color: #fff; padding: 5px 0; border: 0;}
                    
                    td.marker
                    { padding: 5px 0; border: 0 !important; }
                </style>
		
		
		
		      <?php                      // dem 3dec10: Must include these files again, don't know why. 
		      require 'config.php';

  $row = loadUser($_SESSION['username']);
	
	ini_set("memory_limit","24M");
	
	$username=$row['name'];
	
		      	$tmp_dir=$config['root_dir']."curator_data/uploads/tmpdir_".$username."_".rand();
	
	umask(0);
	
	if(!file_exists($tmp_dir) || !is_dir($tmp_dir)) {
	  mkdir($tmp_dir, 0777) or die("Couldn't mkdir $tmp_dir");
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
	$reader = & new Spreadsheet_Excel_Reader();
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
	  
//          echo "annots 0 cells $i 1 is set to ".$annots['cells'][$i][1]."<br>\n";
//          echo "annots 0 cells $i 1 is set to ".$annots['cells'][$i][2]."<br>\n";
          if (stripos($annots['cells'][$i][1],'*crop')!==FALSE){
                $CROP = $i;
          } elseif (stripos($annots['cells'][$i][1],'*breeding')!==FALSE) {
                $BREEDINGPROGRAM = $i;
	  } elseif (stripos($annots['cells'][$i][1],'trial code')!==FALSE) {
		$TRIALCODE = $i;
	  } elseif (stripos($annots['cells'][$i][1],'experiment code')!==FALSE){
		$EXPERIMENT_SHORTNAME = $i;
	  } elseif (stripos($annots['cells'][$i][1],'location')!==FALSE){
		$LOCATION = $i;
	  } elseif (stripos($annots['cells'][$i][1],'latit')!==FALSE){
		$LATIT = $i;
          } elseif (stripos($annots['cells'][$i][1],'longi')!==FALSE){
                $LONGI = $i;
	  } elseif (stripos($annots['cells'][$i][1],'collaborator')!==FALSE){
		$COLLABORATOR = $i;
	  } elseif (stripos($annots['cells'][$i][1],'narrative descrip')!==FALSE){
		$EXPERIMENT_NAME = $i;
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
          } elseif (stripos($annots['cells'][$i][1],'begin weather')!==FALSE){
                $BEGINWEATHER = $i;
          } elseif (stripos($annots['cells'][$i][1],'greenhouse')!==FALSE){
                $GREENHOUSE = $i;
	  }elseif (stripos($annots['cells'][$i][1],'irrigation')!==FALSE){
		$IRRIGATION = $i;
	  }elseif (stripos($annots['cells'][$i][1],'harvest date')!==FALSE){
		$HARVESTDATE = $i;
	  }elseif (stripos($annots['cells'][$i][1],'other')!==FALSE){
		$OTHERREMARKS = $i;
	  }else {
//                echo "annots 0 cells $i 1 is set to ".$annots['cells'][$i][1]; 
//                echo "not found<br>\n";
          }
	}

		//echo "experiment".$EXPERIMENT_SHORTNAME."short name";
	//	echo $CAPYEAR." ".$BREEDINGPROGRAM." ".$LOCATION." ".$COLLABORATORCODE." ".$TRIALCODE." ".$OTHERREMARKS."\n";
		
		
		// Check if required rows are present
// Required rows are: breeding program, location, collaborator 
// Experiment (short name), Trial code, number of replications; If any of these column is missing (empty), then
// the annotation file must be corrected



/*
 * Process the annotations file
 */
  
	
	$experiments = array();
	for ($i = 0; $i < ($cols-1); $i++)
	{
		$experiments[$i] = new experiment();
	}

   	$year_row = 				$annots['cells'][$TRIALCODE];
        $crop_row = 				$annots['cells'][$CROP];
	$trial_row =				$annots['cells'][$TRIALCODE];
	$bp_row =				$annots['cells'][$BREEDINGPROGRAM];
	$location_row =				$annots['cells'][$LOCATION];
	$latitude_row =				$annots['cells'][$LATIT];
        $longitude_row = 			$annots['cells'][$LONGI];
	$collaborator_row =			$annots['cells'][$COLLABORATOR];
	$collabcode_row =			$annots['cells'][$COLLABORATORCODE];
	$trialcode_row =			$annots['cells'][$TRIALCODE];
	$plantingdate_row =			$annots['cells'][$PLANTINGDATE];
	$beginweatherdate_row = 		$annots['cells'][$BEGINWEATHER];
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
	
		//echo " we got the data from excel". $year_row.$trialcode_row.$seedingrate_row;
	
		//connect_dev();	/* connecting to development database */
		
		
			$n_trials = 0;
	for ($i = 2; $cols >= $i; $i++)
	{
			//echo "i got year info". $year_row[$i];
			//echo " date string is".$harvestdate_row[$i];
			
			$error_flag = 0;
			//echo " testing the data".$experimentshortname_row[$i]."experiment name";
		// sometimes Excel introduces extra columns in the data files
		// stop reading at first column where year (required field) is zero.
		if (empty($trial_row[$i]))
		{
			echo " Trial Code is  missing for column $i. Please enter the value and upload again"."<br/>";
				exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
			break;
		}
		$n_trials++;
		$index = $i - 2;
		
		
// Check key data fields in the experiment to ensure valid values
// Required fields are: year, CAP data program code (who performed experiment)
// a unique trial_code, experiment short name
		$tmp = substr($trial_row[$i],0,4);
		$experiments[$index]->year = intval($tmp);
		$today = getdate();
		$curr_year = $today['year'];
		
		$experiments[$index]->collabcode = trim($bp_row[2]);
		$CAPcode = $experiments[$index]->collabcode;
		$sql = "SELECT CAPdata_programs_uid FROM CAPdata_programs
					WHERE data_program_code= '$CAPcode'";
		$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
		if (1 == mysql_num_rows($res))
		{
			$row = mysql_fetch_assoc($res);
			$capdata_uid = $row['CAPdata_programs_uid'];
		}else{
			echo "CAP data program ID 2 ".$CAPcode." does not exist "."<br/>";
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
		
		
		$experiments[$index]->bp = trim($bp_row[$i]);
		$experiments[$index]->location = addslashes($location_row[$i]);
		$experiments[$index]->latlong = mysql_real_escape_string($latlong_row[$i]);
		$experiments[$index]->collaborator = mysql_real_escape_string($collaborator_row[$i]);
		$experiments[$index]->collabcode = $bp_row[2];
		$experiments[$index]->seedingrate = $seedingrate_row[$i];
		$experiments[$index]->experimentname = mysql_real_escape_string(trim($experimentname_row[$i]));
		$experiments[$index]->experimentaldesign = mysql_real_escape_string($experimentaldesign_row[$i]);
		
        // Check for floating point values 
        if ((strpos($numberofentries_row[$i], '.') != 0) || (strpos($numberofreplications_row[$i], '.') != 0)) {
            echo "<b>ERROR: Not an integer value encountered in either the 'Number of entries' or 'Number of replication' field. </b><br/><br/>";
		    exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");

        }
 
       //Check number of entries
        if ((is_numeric($numberofentries_row[$i])) || ($numberofentries_row[$i] == '' )) {
            $experiments[$index]->numberofentries = intval($numberofentries_row[$i]);
        } else {
		    echo "<b>ERROR: Value for 'Number of entries' must be an integer </b><br/><br/>";
		    exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
        }
 
        //Check Number of replications column 
        if ((is_numeric($numberofreplications_row[$i])) || ($numberofreplications_row[$i] == '' )) {
   	        $experiments[$index]->numberofreplications = intval($numberofreplications_row[$i]);
        } else {
            echo "<b>ERROR: Value for 'Number of replications' must be an integer </b><br/><br/>";
            exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
        }

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
	
	// echo "number of trials ".$n_trials."\n";
	
	if ($error_flag>0)  {
		echo "FATAL ERROR: problems with one or more required fields: year, trialcode, or collaborator code"."<br/>";
		print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
	}
	
	else {

	// Insert data into experiments and phenotype_experiments  table
		
		?>
		
		<h3>We are reading following data from the uploaded Input Data File</h3>
		
		<table >
		<thead>
	<tr>
	<th >Status</th>
	<th >Crop</th>
	<th >Breeding Program(s) </th>
	<th >Location </th>
	<th  >Latitude of field </th>
        <th >Longitude of field </th>
	<th  >Collaborator </th>
	<th  >Experiment </th>
	<th  >Trial Code </th>
	<th >Planting date </th>
        <th >Harvest date </th>
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

			<?
				for ($i = 2; $cols >= $i; $i++)
				{
				//Extract data
			
			?>
			
			<tr>
			<td><font color=red>
			<? 
			$sql = "SELECT experiment_uid FROM experiments WHERE trial_code = '$trialcode_row[$i]'";
                        $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
			if (mysql_num_rows($res)!==0) { //yes, experiment found, so update
			  print "Update Record</td><td>";
                        } else {
			  print "New Record</td><td>";
 			}
			print "</font>";

			$newtext = wordwrap($crop_row[2], 6, '<br>');
			print "$newtext</td><td>";
			$newtext1 = wordwrap($bp_row[2], 6, '<br>');
			print "$newtext1</td><td>";
			$newtext2 = wordwrap($location_row[$i], 6, '<br>');
			print "$newtext2</td><td>";
			$newtext = wordwrap($latitude_row[$i], 6, '<br>');
			print "$newtext</td><td>";
                        $newtext = wordwrap($longitude_row[$i], 6, '<br>');
                        print "$newtext</td><td>";
			$newtext = wordwrap($collaborator_row[$i], 12, '<br>');
			print "$newtext</td><td>";
			$newtext = wordwrap($experimentshortname_row[$i], 6, '<br>');
			print "$newtext<td>";
			$newtext = wordwrap($trialcode_row[$i], 6, '<br>');
			print "$newtext<td>";
			$newtext = wordwrap($plantingdate_row[$i], 6, '<br>');
			print "$newtext<td>";
       			$newtext = wordwrap($harvestdate_row[$i], 6, '<br>');
                        print "$newtext<td>";
			$newtext = wordwrap($seedingrate_row[$i], 6, '<br>');
			print "$newtext<td>";
			$newtext = wordwrap($experimentaldesign_row[$i], 6, '<br>');
			print "$newtext<td>";
			$newtext = wordwrap($numberofentries_row[$i], 6, '<br>');
			print "$newtext<td>";
			$newtext = wordwrap($numberofreplications_row[$i], 6, '<br>');
			print "$newtext<td>";
			$newtext = wordwrap($plotsize_row[$i], 6, '<br>');
			print "$newtext<td>";
			$newtext = wordwrap($harvestedarea_row[$i], 6, '<br>');
			print "$newtext<td>";
			$newtext = wordwrap($irrigation_row[$i], 6, '<br>');
			print "$newtext<td>";
			$newtext = wordwrap($harvestdate_row[$i], 6, '<br>');
			print "$newtext<td>";
			$newtext = wordwrap($otherremarks_row[$i], 12, "<br>" );
			print "$newtext";
				}/* end of for loop */
			?>
			</tbody>
			</table>
			
		
		
		<input type="Button" value="Accept" onclick="javascript: update_database('<?echo $annotfile?>','<?echo $uploadfile?>','<?echo $username?>','<?echo $data_public_flag?>' )"/>
    <input type="Button" value="Cancel" onclick="history.go(-1); return;"/>
		Warning - Do not select the Accept button if the status field is not correct (Update Record indicates that Trial Code already exist) 
	
		<?
		
	
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
	$data_public_flag = $_GET['public'];
	
	
	
	
	
		$reader = & new Spreadsheet_Excel_Reader();
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
	
	
	//print "cols = $cols, rows = $rows<br>\n";	
	
	
	// find location for each row of data; find where data starts in file
	for ($i = 1; $i <= $rows; $i++) {
	  
  if (stripos($annots['cells'][$i][1],'year')!==FALSE) {
		  $CAPYEAR = $i;
	  } elseif (stripos($annots['cells'][$i][1],'breeding')!==FALSE){
		$BREEDINGPROGRAM = $i;
	  } elseif (stripos($annots['cells'][$i][1],'location')!==FALSE){
		$LOCATION = $i;
	  } elseif (stripos($annots['cells'][$i][1],'latit')!==FALSE){
		$LATIT = $i;
          } elseif (stripos($annots['cells'][$i][1],'longi')!==FALSE){
                $LONGI = $i;
	  } elseif (stripos($annots['cells'][$i][1],'collaborator code')!==FALSE){
		$COLLABORATORCODE = $i;
	  } elseif (stripos($annots['cells'][$i][1],'collaborator')!==FALSE){
		$COLLABORATOR = $i;
	  } elseif (stripos($annots['cells'][$i][1],'experiment code')!==FALSE){
		$EXPERIMENT_SHORTNAME = $i;
	  } elseif (stripos($annots['cells'][$i][1],'narrative desc')!==FALSE){
		$EXPERIMENT_NAME = $i;
	  } elseif (stripos($annots['cells'][$i][1],'trial code')!==FALSE){
		$TRIALCODE = $i;
	  } elseif (stripos($annots['cells'][$i][1],'planting')!==FALSE){
		$PLANTINGDATE = $i;
          } elseif (stripos($annots['cells'][$i][1],'begin weather')!==FALSE){
                $BEGINWEATHER = $i;
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
	
	$year_row =				$annots['cells'][$TRIALCODE];
	$bp_row =				$annots['cells'][$BREEDINGPROGRAM];
	$location_row =				$annots['cells'][$LOCATION];
	$latlong_row =				$annots['cells'][$LAT_LONG];
	$latitude_row = 			$annots['cells'][$LATIT];
	$longitude_row = 			$annots['cells'][$LONGI];
	$collaborator_row =			$annots['cells'][$COLLABORATOR];
	$collabcode_row =			$annots['cells'][$COLLABORATORCODE];
	$trialcode_row =			$annots['cells'][$TRIALCODE];
	$plantingdate_row =			$annots['cells'][$PLANTINGDATE];
	$seedingrate_row =			$annots['cells'][$SEEDINGRATE];
	$beginweatherdate_row = 		$annots['cells'][$BEGINWEATHER];
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
		if (empty($trialcode_row[$i]))
		{
			print "i = $i empty trialcode<br>\n";
			break;
		}
		$n_trials++;
		$index = $i - 2;
		
		$error_flag = 0;
// Check key data fields in the experiment to ensure valid values
// Required fields are: year, CAP data program code (who performed experiment)
// a unique trial_code, experiment short name

		$tmp = preg_split("/_/",$trialcode_row[$i]);
		$experiments[$index]->year = intval($tmp[1]);
		$today = getdate();
		$curr_year = $today['year'];
		if (DEBUG>1) {echo "curr_year ".$curr_year." exp year: ".$experiments[$index]->year."\n";}
		if (($experiments[$index]->year<2006)OR ($year>$curr_year)) {
			echo "Year value not in range [2006-current year]: ".$tmp."\n";
			$error_flag = ($error_flag)&(1);
		}
		
		$experiments[$index]->collabcode = trim($bp_row[2]);
		$CAPcode = $experiments[$index]->collabcode;
		$sql = "SELECT CAPdata_programs_uid FROM CAPdata_programs
					WHERE data_program_code= '$CAPcode'";
		$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
		if (1 == mysql_num_rows($res))
		{
			$row = mysql_fetch_assoc($res);
			$capdata_uid = $row['CAPdata_programs_uid'];
		}else{
			echo "CAP data program ID 3 ".$CAPcode." does not exist \n";
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
		$experiments[$index]->latitude = mysql_real_escape_string($latitude_row[$i]);
		$experiments[$index]->longitude = mysql_real_escape_string($longitude_row[$i]);
		$experiments[$index]->beginweatherdate = mysql_real_escape_string($beginweatherdate_row[$i]);
		$experiments[$index]->collaborator = mysql_real_escape_string($collaborator_row[$i]);
		$experiments[$index]->collabcode = $bp_row[2];

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

        // Check for floating point values 
        if ((strpos($numberofentries_row[$i], '.') != 0) || (strpos($numberofreplications_row[$i], '.') != 0)) {
            echo "<b>ERROR: Not an integer value encountered in either the 'Number of entries' or 'Number of replication' field. </b><br/><br/>";
		    exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");

        }

        //Check number of entries
        if ((is_numeric($numberofentries_row[$i])) || ($numberofentries_row[$i] == '' )) {
            $experiments[$index]->numberofentries = intval($numberofentries_row[$i]);
        } else {
		    print "i=$i<br>\n";
		    echo "<b>ERROR: Value for 'Number of entries' must be an integer </b><br/><br/>";
		    exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
        }
 
        //Check Number of replications column 
        if ((is_numeric($numberofreplications_row[$i])) || ($numberofreplications_row[$i] == '' )) {
   	        $experiments[$index]->numberofreplications = intval($numberofreplications_row[$i]);
        } else {
            echo "<b>ERROR: Value for 'Number of replications' must be an integer </b><br/><br/>";
            exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
        }

		
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

		// get experiment_set_uid
			if (preg_match("/[A-Za-z0-9]+/",$experiment->experimentshortname)) {
			  $sql = "SELECT experiment_set_uid FROM experiment_set WHERE experiment_set_name = '{$experiment->experimentshortname}'";
                          $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
		 	  if (mysql_num_rows($res)==0) //no, experiment not found, so isert 
                          {
                                $sql = "insert into experiment_set set
                                        experiment_set_name = '{$experiment->experimentshortname}'";
                                echo "SQL ".$sql."<br>\n";
                                mysql_query($sql) or die(mysql_error());
                                $sql = "select experiment_set_uid from experiment_set where experiment_set_name = '{$experiment->experimentshortname}'";
                                echo "SQL ".$sql."<br>\n";
                                $res = mysql_query($sql) or die(mysql_error());
                                $row = mysql_fetch_assoc($res);
                                $experiment_set_uid = $row['experiment_set_uid'];
				print "experiment found $experiment_set_uid<br>\n";
                          } else {
				$row = mysql_fetch_assoc($res);
                                $experiment_set_uid = $row['experiment_set_uid'];
				//print "experiment found $experiment->experimentshortname<br>$sql<br>\n";
			  }  
			} else {
				$experiment_set_uid = "NULL";
			}
			//print "$sql<br>\n";
	
			// Insert or update experiment table data
			// First check if this trial code is in the database, if yes, then update all fields;
			// if no then insert into table
			$sql = "SELECT experiment_uid FROM experiments WHERE trial_code = '{$experiment->trialcode}'";
			$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
			
			if (mysql_num_rows($res)!==0) //yes, experiment found, so update
			{
					$row = mysql_fetch_assoc($res);
					$exp_id = $row['experiment_uid'];
					if(empty($experiment->beginweatherdate)) {
						$sql_optional = '';
					} else {
						$sql_optional = "begin_weather_date = str_to_date('$experiment->beginweatherdate','%m/%d/%Y'),";
					}
					if (DEBUG>1) {echo "exp ID ".$exp_id."\n";}
		
					//update experiment information
					$sql = "UPDATE experiments
								SET
									experiment_type_uid = $exptype_id,
									CAPdata_programs_uid = $capdata_uid,
									experiment_set_uid = $experiment_set_uid,
									experiment_short_name = '{$experiment->experimentshortname}',
									experiment_desc_name = '{$experiment->experimentname}',
									trial_code = '{$experiment->trialcode}',
									experiment_year = '{$experiment->year}',
									data_public_flag = '$data_public_flag',
									created_on = NOW()
								WHERE experiment_uid = $exp_id";
					echo "Table <b>experiments</b> updated.<br>\n";
					// echo $sql."<br>\n";
					
					mysql_query($sql) or die(mysql_error() . "<br>$sql");
					//update phenotype experiment information
					$sql = " UPDATE phenotype_experiment_info
								set
									collaborator = '{$experiment->collaborator}',
									planting_date = str_to_date('$experiment->plantingdate','%m/%d/%Y'),
									seeding_rate = '{$experiment->seedingrate}',
									experiment_design = '{$experiment->experimentaldesign}',
									number_replications = '{$experiment->numberofreplications}',
									number_entries = '{$experiment->numberofentries}',
									plot_size = '{$experiment->plotsize}',
									harvest_area = '{$experiment->harvestedarea}',
									harvest_date = str_to_date('$experiment->harvestdate','%m/%d/%Y'),
									irrigation = '{$experiment->irrigation}',
									other_remarks = '{$experiment->otherremarks}',
									location = '{$experiment->location}',
									latitude = '{$experiment->latitude}',
									longitude = '{$experiment->longitude}',
									$sql_optional
									updated_on = NOW()
								WHERE experiment_uid = $exp_id";
					if (DEBUG>2) {echo "update phenotypeexp SQL ".$sql."\n";}
					mysql_query($sql) or die("
<p><font color=red>MySQL error while updating <b>phenotype_experiment_info</b> table.</font>
<p><b>Message:</b>
<br>". mysql_error() . "
<p><b>Command:</b>
<br>$sql
<p><input type=\"Button\" value=\"Return\" onClick=\"history.go(-2); return;\">
");

					echo "Table <b>phenotype_experiment_info</b> updated.<br>\n";
					
			} else {
		
					$sql = "
						insert into
							experiments
						set
							experiment_type_uid = $exptype_id,
							CAPdata_programs_uid = $capdata_uid,
							experiment_set_uid = $experiment_set_uid,
							experiment_short_name = '{$experiment->experimentshortname}',
							experiment_desc_name = '{$experiment->experimentname}',
							trial_code = '{$experiment->trialcode}',
							experiment_year = '{$experiment->year}',
							data_public_flag = '$data_public_flag',
							created_on = NOW()
					";
				
					echo "New entry inserted into <b>experiments</b> table.<br>\n";
                                        //echo "$sql<br>\n";	
					mysql_query($sql) or die(mysql_error() . "<br>$sql");
					
					//get experiment_uid set genotype experiments info table
					$sql = "SELECT experiment_uid FROM experiments
									WHERE trial_code = '{$experiment->trialcode}' limit 1";
					$res = mysql_query($sql) or die(mysql_error());
					$row = mysql_fetch_assoc($res);
					$exp_id = $row['experiment_uid'];
					if (DEBUG>1) {echo "exp ID ".$exp_id."\n";}

					if(empty($experiment->beginweatherdate)) {
						$sql_optional = '';
					} else {
						$sql_optional = "begin_weather_date = str_to_date('$experiment->beginweatherdate','%m/%d/%Y'),";
					}
					$sql = "
						insert into
							phenotype_experiment_info
						set
							experiment_uid = $exp_id,
							collaborator = '{$experiment->collaborator}',
							planting_date = str_to_date('$experiment->plantingdate','%m/%d/%Y'),
							seeding_rate = '{$experiment->seedingrate}',
							experiment_design = '{$experiment->experimentaldesign}',
							number_replications = '{$experiment->numberofreplications}',
							number_entries = '{$experiment->numberofentries}',
							plot_size = '{$experiment->plotsize}',
							harvest_area = '{$experiment->harvestedarea}',
							harvest_date = str_to_date('$experiment->harvestdate','%m/%d/%Y'),
							irrigation = '{$experiment->irrigation}',
							other_remarks = '{$experiment->otherremarks}',
							location = '{$experiment->location}',
							latitude = '{$experiment->latitude}',
							longitude = '{$experiment->longitude}',
							$sql_optional
							created_on = NOW()
					";
					mysql_query($sql) or die("
<p><font color=red>MySQL error while inserting into <b>phenotype_experiment_info</b> table.</font>
<p><b>Message:</b>
<br>". mysql_error() . "
<p><b>Command:</b>
<br>$sql
<p><input type=\"Button\" value=\"Return\" onClick=\"history.go(-2); return;\">
");

					echo "New entry inserted into <b>phenotype_experiment_info</b> table.<br>\n";
			} 

		}// end foreach
	}
		

	
	echo " <b>The Data was inserted or updated successfully </b>";
	echo"<br/><br/>";
	?>
	<a href="<?php echo $config['base_url']; ?>curator_data/input_annotations_upload_excel.php"> Go Back To Main Page </a>
	<?
	
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

