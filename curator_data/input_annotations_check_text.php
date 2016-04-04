<?php

// 01/25/2011 JLee  Check 'number of entries' and 'number of replition' input values 
// 12/14/2010 JLee  Change to use curator bootstrap

define("DEBUG", 0);
require 'config.php';
//require_once("../includes/common_import.inc");
/*
 * Logged in page initialization
 */
require $config['root_dir'] . 'includes/bootstrap_curator.inc';
//include($config['root_dir'] . 'includes/common_import.inc');

//include($config['root_dir'] . 'SumanDirectory/bootstrap_dev.inc');

//include($config['root_dir'] . 'SumanDirectory/annotations_link.php');

require_once "../lib/Excel/excel_reader2.php"; // Microsoft Excel library
ini_set(auto_detect_line_endings,1);

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
			
			
			var url='<?php echo $_SERVER[PHP_SELF]; ?>?function=typeDatabase&linedata=' + filepath + '&file_name=' + filename + '&user_name=' + username + '&public=' + data_public_flag;
	
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
		      // dem 3dec10: Must include these files again, don't know why.
		      require 'config.php';
		      //include($config['root_dir'] . 'curator_data/lineuid.php');

  $row = loadUser($_SESSION['username']);
	
	ini_set("memory_limit","24M");
	
	$username=$row['name'];

		      //        $tmp_dir="uploads/tmpdir_".$username."_".rand();	
		      $tmp_dir=$config['root_dir']."curator_data/uploads/tmpdir_".$username."_".rand();
	
//	$raw_path= "rawdata/".$_FILES['file']['name'][1];
//	copy($_FILES['file']['tmp_name'][1], $raw_path);
	umask(0);
	
	//connect_dev();
	//connect();

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
	//	$rawdatafile = $_FILES['file']['name'][1];
				
		$uftype=$_FILES['file']['type'][0];
		if (strpos($uploadfile, ".txt") === FALSE) {
			error(1, "Expecting a text file. <br> The type of the uploaded file is ".$uftype);
			print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
		}
		else {
			if(move_uploaded_file($_FILES['file']['tmp_name'][0], $target_path.$uploadfile)) 
			{


    			/* start reading the excel */
		
		
		$annotfile = $target_path.$uploadfile;
		
						
/* Read the annotation file */
	 $handle = fopen($annotfile, "r");
	 $header = fgetcsv($handle, 0, "\t");

        // Set up column indices; all columns are required
        $crop_idx = implode(find("Crop", $header),"");
        $bp_idx =  implode(find("Breeding Program Code", $header),"");
        $location_idx = implode(find("Location", $header),"");
        $lat_idx = implode(find("Latitude of field", $header),"");
        $long_idx = implode(find("Longitude of field", $header),"");
        $collab_idx = implode(find("Collaborator", $header),"");
        $collabcode_idx = implode(find("Collaborator Code", $header),"");
        //$test_idx = implode(find("Testing", $header),"");
        $experiment_idx = implode(find("Experiment Code", $header),"");
        $descexperiment_idx = implode(find("Experiment Description", $header),"");
        $trialcode_idx = implode(find("Trial Code", $header),"");
				$planting_date_idx = implode(find("Planting date", $header),"");
				$harvest_date_idx = implode(find("Harvest date", $header),"");
				$weather_date_idx = implode(find("Begin weather date", $header),"");
				$seeding_rate_idx = implode(find("Seeding rate", $header),"");
				$experimental_design_idx = implode(find("Experimental design", $header),"");
				$num_of_entries_idx = implode(find("Number of entries", $header),"");
				$num_of_replications_idx = implode(find("Number of replications", $header),"");
				$plot_size_idx = implode(find("Plot size", $header),"");
				$harvested_area_idx = implode(find("Harvested area", $header),"");
				$irrigation_idx = implode(find("Irrigation", $header),"");
				$other_remarks_idx = implode(find("Other remarks", $header),"");
				
				$collab_idx = substr($collab_idx,0,1);
				$experiment_idx = substr($experiment_idx,0,1);
				if (DEBUG>2) {
				  echo " indexes". "plant date".$planting_date_idx. "harvest date". $harvest_date_idx . "seed rate". $seeding_rate_idx . "exp design" . $experimental_design_idx
					. "num of entries" . $num_of_entries_idx . "num of repli" . $num_of_replications_idx . "plot" . $plot_size_idx .
					"har area" . $harvested_area_idx . "irri" . $irrigation_idx . "harv date" . $harvest_date_idx . "weath date" . $weather_date_idx . "other" . $other_remarks_idx;
				}
	
		//  Step 2. Read in data, a line at a time  
        $run = 0;
        while (($data = fgetcsv($handle, 0, "\t")) !== FALSE) {
            // echo "reading in new line<br>";
            if ($harvest_date_idx!==FALSE) 
						{
						/* check if year is  missing */
						$cap_year = trim($data[$harvest_date_idx]);
						
						if(!empty($cap_year))
						{
						  preg_match("/[0-9]+$/",$data[$harvest_date_idx],$matches);
						$capyear[] = $matches[0];
						}
						
						else
						{
							echo " Year is missing from Harvest Date. Please enter the year and upload again"."<br/>";
							exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
						}
						
						}
            
            
						$bp = trim($data[$bp_idx]);
            $bp_data[] = $bp;
            $location = trim($data[$location_idx]);//location
            $location_data[] = $location;
            $lat_data[] = trim($data[$lat_idx]);
            $long_data[] = trim($data[$long_idx]);
            $weather_date[] = trim($data[$weather_date_idx]);
            $planting_date[] = trim($data[$planting_date_idx]);
            $harvest_date[] = trim($data[$harvest_date_idx]);
            $collab[] = trim($data[$collab_idx]);//collabarator
            /* checking for a valid cap code */
            
            $collabCodeTest = trim($data[$collabcode_idx]);
            
            //echo "collab code" . $collabCodeTest;
            $collabCode[] = $collabCodeTest;
					//	$collabCode[] = trim($data[$collabcode_idx]); //collabarator code
            
            $sql = "SELECT CAPdata_programs_uid FROM CAPdata_programs
										WHERE data_program_code= '$collabCodeTest'";
										
							$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
							
							if (1 == mysql_num_rows($res))
							{
								$row = mysql_fetch_assoc($res);
								
								$capdata_uid = $row['CAPdata_programs_uid'];
							}
							else
							{
								echo "CAP data program ID ".$collabCodeTest." does not exist "."<br/>";
								$error_flag = ($error_flag)&(2);
								exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
							}
					        
            
           /* check if the experiment short name is empty */
            
            $experiment_short = trim($data[$experiment_idx]);//experiemnt
            
            //echo "short name".$experiment_short;
            
            $experiment[] = $experiment_short;
            	if (is_null($experiment_short)) 
							{
								echo "Short Name  is empty "."<br/>";
								$error_flag = ($error_flag)&(16);
								exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
							}
            
            
            $descExperiment[] = trim($data[$descexperiment_idx]);//descriptive experiment
            
            /* check if trial code is empty */
            
            $trialcode = addslashes(trim($data[$trialcode_idx]));//trial code
           
            //echo "trial code is:" . $trialcode . "<br/>";
            if (is_null($trialcode))
						{
							echo "Trial code ".$trialcode." is empty "."<br/>";
							exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
						} 
						else 
						{
								/* adding the trial code to the array */
									$trialcode_data[] = $trialcode;

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
						
						/* reading in all other remaing values */
						            
           	$seedingRate[] = trim($data[$seeding_rate_idx]);//seeding Rate
           	$expDesign[] = trim($data[$experimental_design_idx]);//Experimental Design

           	//$numEntries[] = trim($data[$num_of_entries_idx]);//number of entries

            // Check for floating point values 
            if ((strpos($data[$num_of_entries_idx], '.') != 0) || (strpos($data[$num_of_replications_idx], '.') != 0)) {
                echo "<b>ERROR: Not an integer value encountered in either the 'Number of entries' or 'Number of replication' field. </b><br/><br/>";
                exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
            }

            //Check number of entries
            if ((is_numeric($data[$num_of_entries_idx])) || ($data[$num_of_entries_idx] == '' )) {
                $numEntries[] = trim($data[$num_of_entries_idx]);//number of entries
            } else {
				echo "<b>ERROR: Value for 'Number of entries' must be an integer </b><br/><br/>";
				exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
            }
 
             //Check Number of replications column 
            if ((is_numeric($data[$num_of_replications_idx])) || ($data[$num_of_replications_idx] == '' )) {
           	    $numReplications[] = trim($data[$num_of_replications_idx]);//number of replications
            } else {
				echo "<b>ERROR: Value for 'Number of replications' must be an integer </b><br/><br/>";
				exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
            }

            $plotSize[] = trim($data[$plot_size_idx]);//Plot Size
            $harvestArea[] = trim($data[$harvested_area_idx]);//Harvested Area
            $irrigation[] = trim($data[$irrigation_idx]);//Irrigation
            $otherRemarks[] = trim($data[$other_remarks_idx]);//Other Remarks
            
          } /* end of while*/  
          
     //var_dump($trialcode_data);  
			
		//	echo "count" .count($trialcode_data);   
?>
		
		<h3>We are reading following data from the uploaded Input Data File</h3>
		
		<table >
		<thead>
	<tr>
	<th> Year </th>
	<th> Breeding Program(s) </th>
	<th >Location </th>
	<th  >Latitude of field </th>
	<th> Longitude of field </th>
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
				for ($i = 0; $i<count($trialcode_data); $i++)
				{
				//Extract data
			
			?>
			
			<tr>
			<td >
			<?php
			$newtext = wordwrap($capyear[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			<td >
			<?php
			$newtext = wordwrap($bp_data[$i], 6, "\n", true);
			echo $newtext ?>
			</td>
			<td>
			<?php
			$newtext = wordwrap($location_data[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			<td >
			<?php
			$newtext = wordwrap($lat_data[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			<td >
			<?php
			$newtext = wordwrap($long_data[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			<td >
			<?php
			$newtext = wordwrap($collab[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			<td >
		<?php
			$newtext = wordwrap($collabCode[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			<td >
			<?php
			$newtext = wordwrap($experiment[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			<td >
			<?php
			$newtext = wordwrap($descExperiment[$i], 6, "\n", true);
			echo $newtext ?>
			</td>
			<td >
			<?php
			$newtext = wordwrap($trialcode_data[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
		
			<td >
			<?php
			$newtext = wordwrap($planting_date[$i], 10, "\n", true);
			echo $newtext ?>
			</td> 
			
			<td >
			<?php
			$newtext = wordwrap($seedingRate[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			
			<td >
			<?php
			$newtext = wordwrap($expDesign[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			
			<td >
			<?php
			$newtext = wordwrap($numEntries[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			
			<td >
			<?php
			$newtext = wordwrap($numReplications[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			
			<td >
			<?php
			$newtext = wordwrap($plotSize[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			
			<td >
			<?php
			$newtext = wordwrap($harvestArea[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			
			<td >
			<?php
			$newtext = wordwrap($irrigation[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			
			<td >
			<?php
			$newtext = wordwrap($harvest_date[$i], 10, "\n", true);
			echo $newtext ?>
			</td> 
			
			<td >
			<?php
			$newtext = wordwrap($otherRemarks[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
		
			</tr>
			<?php
				}/* end of for loop */
			?>
			</tbody>
			</table>
			
		
		
		<input type="Button" value="Accept" onclick="javascript: update_database('<?php echo $annotfile?>','<?php echo $uploadfile?>','<?php echo $username?>','<?php echo $data_public_flag?>' )">
        <input type="Button" value="Cancel" onclick="history.go(-1);">
        
	
		<?php
			
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
	//dem 3dec10: Must include this again.  Don't know why.
	//	include($config['root_dir'] . 'curator_data/lineuid.php');

	connect();
	
	$datafile = $_GET['linedata'];
	$filename_old = $_GET['file_name'];
	$filename = $filename_old.rand();
	$username = $_GET['user_name'];
	$data_public_flag = $_GET['public'];
	
	/* Read the annotation file */
	 $handle = fopen($datafile, "r");
        $header = fgetcsv($handle, 0, "\t");

        // Set up column indices; all columns are required
        
        $bp_idx =  implode(find("Breeding_Program", $header),"");
        $location_idx = implode(find("Location", $header),"");
        $lat_idx = implode(find("Latitude of field", $header),"");
        $long_idx = implode(find("Longitude of field", $header),"");
        $collab_idx = implode(find("Collaborator", $header),"");
        $collabcode_idx = implode(find("Collaborator Code", $header),"");
        $test_idx = implode(find("Testing", $header),"");
        $experiment_idx = implode(find("Experiment", $header),"");
        $descexperiment_idx = implode(find("Descriptive Name of Experiment", $header),"");
        $trialcode_idx = implode(find("Trial Code", $header),"");
				$planting_date_idx = implode(find("Planting date", $header),"");
				$harvest_date_idx = implode(find("Harvest date", $header),"");
				$weather_date_idx = implode(find("Begin weather date", $header),"");
				$seeding_rate_idx = implode(find("Seeding rate", $header),"");
				$experimental_design_idx = implode(find("Experimental design", $header),"");
				$num_of_entries_idx = implode(find("Number of entries", $header),"");
				$num_of_replications_idx = implode(find("Number of replications", $header),"");
				$plot_size_idx = implode(find("Plot size", $header),"");
				$harvested_area_idx = implode(find("Harvested area", $header),"");
				$irrigation_idx = implode(find("Irrigation", $header),"");
				$harvest_date_idx = implode(find("Harvest date", $header),"");
				$other_remarks_idx = implode(find("Other remarks", $header),"");
				
				$collab_idx = substr($collab_idx,0,1);
				$experiment_idx = substr($experiment_idx,0,1);
	                //	echo "exp short". $experiment_idx . "collab code" . $collabcode_idx;
				
        /* echo " indexes". "plant date".$planting_date_idx."seed rate". $seeding_rate_idx . "exp design" . $experimental_design_idx
					. "num of entries" . $num_of_entries_idx . "num of repli" . $num_of_replications_idx . "plot" . $plot_size_idx .
					"har area" . $harvested_area_idx . "irri" . $irrigation_idx . "harv date" . $harvest_date_idx . "other" . $other_remarks_idx;
        */
	
		//  Step 2. Read in data, a line at a time  
        $run = 0;
        while (($data = fgetcsv($handle, 0, "\t")) !== FALSE) 
				{
            if ($harvest_date_idx!==FALSE) 
						{
						/* check if year is  missing */
						$cap_year = trim($data[$harvest_date_idx]);
						
						if(!empty($cap_year))
						{
						 preg_match("/[0-9]+$/",$data[$harvest_date_idx],$matches);
						 $capyear[] = $matches[0];
						}
						
						else
						{
							echo " Year is missing. Please enter the year and upload again"."<br/>";
							exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
						}
						
						} else {
						    echo "bad line<br>";
						}
            
            
						$bp = trim($data[$bp_idx]);
            $bp_data[] = $bp;
            $location = trim($data[$location_idx]);//location
            $location_data[] = $location;
            $lat_data[] = trim($data[$lat_idx]);
            $long_data[] = trim($data[$long_idx]);
            $weather_date[] = trim($data[$weather_date_idx]);
            $planting_date[] = trim($data[$planting_date_idx]);
            $harvest_date[] = trim($data[$harvest_date_idx]);
            
            $collab[] = trim($data[$collab_idx]);//collabarator
            /* checking for a valid cap code */
            
            $collabCodeTest = trim($data[$collabcode_idx]);
            
            //echo "collab code" . $collabCodeTest;
            $collabCode[] = $collabCodeTest;
					//	$collabCode[] = trim($data[$collabcode_idx]); //collabarator code
            
            $sql = "SELECT CAPdata_programs_uid FROM CAPdata_programs
										WHERE data_program_code= '$collabCodeTest'";
										
							$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
							
							if (1 == mysql_num_rows($res))
							{
								$row = mysql_fetch_assoc($res);
								
								$capdata_uid = $row['CAPdata_programs_uid'];
							}
							else
							{
								echo "CAP data program ID ".$collabCodeTest." does not exist "."<br/>";
								$error_flag = ($error_flag)&(2);
								exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
							}
					        
            
           /* check if the experiment short name is empty */
            
            $experiment_short = trim($data[$experiment_idx]);//experiemnt
            
            //echo "short name".$experiment_short;
            
            $experiment[] = $experiment_short;
            	if (is_null($experiment_short)) 
							{
								echo "Short Name  is empty "."<br/>";
								$error_flag = ($error_flag)&(16);
								exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
							}
            
            
            $descExperiment[] = trim($data[$descexperiment_idx]);//descriptive experiment
            
            /* check if trial code is empty */
            
            $trialcode = addslashes(trim($data[$trialcode_idx]));//trial code
            if (is_null($trialcode)) 
						{
							echo "Trial code ".$trialcode." is empty "."<br/>";
							exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
						} 
						else 
						{
						  /* adding the trial code to the array */
						  $trialcode_data[] = $trialcode;
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
						
						/* reading in all other remaing values */
						            
           	$seedingRate[] = trim($data[$seeding_rate_idx]);//seeding Rate
           	$expDesign[] = trim($data[$experimental_design_idx]);//Experimental Design
           	
            // Check for floating point values 
            if ((strpos($data[$num_of_entries_idx], '.') != 0) || (strpos($data[$num_of_replications_idx], '.') != 0)) {
                echo "<b>ERROR: Not an integer value encountered in either the 'Number of entries' or 'Number of replication' field. </b><br/><br/>";
                exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
            }

            //Check number of entries column
            if ((is_numeric($data[$num_of_entries_idx])) || ($data[$num_of_entries_idx] == '' )) {
                $numEntries[] = trim($data[$num_of_entries_idx]);//number of entries
            } else {
				echo "<b>ERROR: Value for 'Number of entries' must be an integer </b><br/><br/>";
				exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
            }

            //Check Number of replications column 
            if ((is_numeric($data[$num_of_replications_idx])) || ($data[$num_of_replications_idx] == '' )) {
           	    $numReplications[] = trim($data[$num_of_replications_idx]);//number of replications
            } else {
				echo "<b>ERROR: Value for 'Number of replications' must be an integer </b><br/><br/>";
				exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
            }

            $plotSize[] = trim($data[$plot_size_idx]);//Plot Size
            $harvestArea[] = trim($data[$harvested_area_idx]);//Harvested Area
            $irrigation[] = trim($data[$irrigation_idx]);//Irrigation
            $otherRemarks[] = trim($data[$other_remarks_idx]);//Other Remarks
            
          } /* end of while*/ 
          
          
          
          /* database manipulation */
          for ($i=0;$i<sizeof($trialcode_data);$i++)
					{
						/* dealing with escaping and other stuff */
						
						$trialcode = mysql_real_escape_string(trim($trialcode_data[$i]));
						$exp_short = mysql_real_escape_string(trim($experiment[$i]));
						$location = addslashes($location_data[$i]);
						$latitude = mysql_real_escape_string($lat_data[$i]);
						$longitude = mysql_real_escape_string($long_data[$i]);
						$collaborator = mysql_real_escape_string($collab[$i]);
						
					
						$teststr= addcslashes(trim($planting_date[$i]),"\0..\37!@\177..\377");
						if (preg_match("/\d+\/\d+\/\d+/",$teststr)) 
						{
							$plantingdate = $teststr;
						} else {
							$plantingdate = '';
							echo "<b>ERROR: Please use correct format for planting date (4/14/2009) </b><br>";
                        				exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
						}
						$descExpt = mysql_real_escape_string(trim($descExperiment[$i]));
						$exptDesign = mysql_real_escape_string($expDesign[$i]);
						$numEntries = intval($numEntries[$i]);
						$numReplications = intval($numReplications[$i]);
						$plotSize = mysql_real_escape_string($plotSize[$i]);
							// Harvest Date
							// convert Microsoft Excel timestamp to Unix timestamp
						
						$teststr= addcslashes(trim($harvest_date[$i]),"\0..\37!@\177..\377");
						if (preg_match("/\d+\/\d+\/\d+/",$teststr)) 
						{
							$harvestdate = $teststr;
						} else {
							$harvestdate = '';
							echo "<b>ERROR: Please use correct format for planting date (4/14/2009) </b><br>";
                        				exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
						}
						$teststr= addcslashes(trim($weather_date[$i]),"\0..\37!@\177..\377");
                                                if (!empty($teststr))
                                                {       
                                                        $beginweatherdate = $teststr;
                                                } else {
							                            $beginweatherdate = "";
                                                }       

					// Harvest Area
					if (DEBUG>2) {
					   echo "beginw weather $beginweatherdate array $weather_date[$i]<br>";
					}
					$harvestedarea = mysql_real_escape_string($harvestArea[$i]);
					// irrigation
					if (FALSE !== stripos($irrigation[$i], "yes"))
					{
						$irrigation = 'yes';
					}
					else
					{
						$irrigation = 'no';
					}
					
					// Other Remarks
					$otherremarks = mysql_real_escape_string(htmlentities($otherRemarks[$i]));
					
					
					
					
						// Get CAPdata collaborator code id
						$CAPcode = $collabCode[$i];
					
					
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
						if (preg_match("/[A-Za-z0-9]+/",$exp_short)) {
						 $sql = "SELECT experiment_set_uid FROM experiment_set WHERE experiment_set_name = '$exp_short'";
						 $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
						 if (mysql_num_rows($res)==0) //no, experiment not found, so isert
						 {
						  $sql = "insert into experiment_set set
						  experiment_set_name = '$exp_short'";
						  echo "SQL ".$sql."<br>\n";
						  mysql_query($sql) or die(mysql_error());
						  $sql = "select experiment_set_uid from experiment_set where experiment_set_name = '$exp_short'";
						  echo "SQL ".$sql."<br>\n";
						  $res = mysql_query($sql) or die(mysql_error());
						  $row = mysql_fetch_assoc($res);
						  $experiment_set_uid = $row['experiment_set_uid'];
						  print "experiment found $experiment_set_uid<br>\n";
						  } else {
						  $row = mysql_fetch_assoc($res);
						  $experiment_set_uid = $row['experiment_set_uid'];
						  print "experiment found $exp_short<br>$sql<br>\n";
						  }
						  } else {
						  $experiment_set_uid = "NULL";
						}
						//print "$sql<br>\n";
						
						// Insert or update experiment table data
						// First check if this trial code is in the database, if yes, then update all fields;
						// if no then insert into table
						$sql = "SELECT experiment_uid FROM experiments WHERE trial_code = '{$trialcode}'";
						$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");

						if (mysql_num_rows($res)!==0) //yes, experiment found, so update
						{
								$row = mysql_fetch_assoc($res);
								$exp_id = $row['experiment_uid'];
								if(empty($weather_date[$i])) {
                                                			$sql_optional = '';
                                        			} else {
                                                			$sql_optional = "begin_weather_date = str_to_date('$weather_date[$i]','%m/%d/%Y'),";
                                        			}

								if (DEBUG>1) {echo "exp ID ".$exp_id."\n";}
					
								//update experiment information
								$sql = "UPDATE experiments
											SET
												experiment_type_uid = $exptype_id,
												CAPdata_programs_uid = $capdata_uid,
												experiment_set_uid = $experiment_set_uid,
												experiment_short_name = '{$exp_short}',
												experiment_desc_name = '{$descExpt}',
												trial_code = '{$trialcode}',
												experiment_year = '{$capyear[$i]}',
												data_public_flag = '$data_public_flag',
												created_on = NOW()
											WHERE experiment_uid = $exp_id";
								echo "Table <b>experiments</b> updated.<br>\n";
								
								mysql_query($sql) or die(mysql_error() . "<br>$sql");
								//update phenotype experiment information
								$sql = " UPDATE phenotype_experiment_info
											set
												collaborator = '{$collaborator}',
												planting_date = str_to_date('{$plantingdate}','%m/%d/%Y'),
												seeding_rate = '{$seedingRate[$i]}',
												experiment_design = '{$exptDesign}',
												number_replications = '{$numReplications}',
												number_entries = '{$numEntries}',
												plot_size = '{$plotSize}',
												harvest_area = '{$harvestedarea}',
												harvest_date = str_to_date('{$harvestdate}','%m/%d/%Y'),
												irrigation = '{$irrigation}',
												other_remarks = '{$otherremarks}',
												location = '{$location}',
												latitude = '{$latitude}',
									            longitude = '{$longitude}',
												$sql_optional
												updated_on = NOW()
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
												experiment_set_uid = $experiment_set_uid,
												experiment_short_name = '{$exp_short}',
												experiment_desc_name = '{$descExpt}',
												trial_code = '{$trialcode}',
												experiment_year = '{$capyear[$i]}',
												data_public_flag = '$data_public_flag',
										created_on = NOW()
								";
								//if (DEBUG>2) {echo "insert exp SQL ".$sql."\n";}
								
								mysql_query($sql) or die(mysql_error());
								
								//get experiment_uid set genotype experiments info table
								$sql = "SELECT experiment_uid FROM experiments
												WHERE trial_code = '{$trialcode}' limit 1";
								$res = mysql_query($sql) or die(mysql_error());
								$row = mysql_fetch_assoc($res);
								$exp_id = $row['experiment_uid'];
							//	if (DEBUG>1) {echo "exp ID ".$exp_id."\n";}
								$sql = "
									insert into
										phenotype_experiment_info
									set
										experiment_uid = $exp_id,
										collaborator = '{$collaborator}',
										planting_date = str_to_date('{$plantingdate}','%m/%d/%Y'),
										seeding_rate = '{$seedingRate[$i]}',
										experiment_design = '{$exptDesign}',
										number_replications = '{$numReplications}',
										number_entries = '{$numEntries}',
										plot_size = '{$plotSize}',
										harvest_area = '{$harvestedarea}',
										harvest_date = str_to_date('{$harvestdate}','%m/%d/%Y'),
										irrigation = '{$irrigation}',
										other_remarks = '{$otherremarks}',
										location = '{$location}',
										latitude = '{$latitude}',
									    longitude = '{$longitude}',
										$sql_optional
										created_on = NOW()
								";
							//	if (DEBUG>2) {echo "insert phenotype exp SQL ".$sql."\n";}
								mysql_query($sql) or die(mysql_error());
							
							
						
						} 
					}// end of for loop


        echo " <b>The Data is inserted/updated successfully </b>";
				echo"<br/><br/>";
	?>
	<a href="<?php echo $config['base_url']; ?>curator_data/input_annotations_upload_text.php"> Go Back To Main Page </a>
	<?php
	
	$sql = "INSERT INTO input_file_log (file_name,users_name)
										VALUES('$filename', '$username')";
					
					
	$lin_table=mysql_query($sql) or die(mysql_error());  
          
	
		$footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
	
	
	
	} /* end of function type_database */


} /* end of class */

