<?

require 'config.php';
//require_once("../includes/common_import.inc");
/*
 * Logged in page initialization
 */
include($config['root_dir'] . 'includes/bootstrap.inc');
//include($config['root_dir'] . 'includes/common_import.inc');

//include($config['root_dir'] . 'SumanDirectory/bootstrap_dev.inc');

//include($config['root_dir'] . 'SumanDirectory/annotations_link.php');
include($config['root_dir'] . 'SumanDirectory/lineuid.php');



require_once("../lib/Excel/reader.php"); // Microsoft Excel library

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
	
	function update_database(filepath, filename, username)
	{
			
			
			var url='<?php echo $_SERVER[PHP_SELF];?>?function=typeDatabase&linedata=' + filepath + '&file_name=' + filename + '&user_name=' + username;
	
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
		
		
		
<?




  $row = loadUser($_SESSION['username']);
	
	ini_set("memory_limit","24M");
	
	$username=$row['name'];

        $tmp_dir="uploads/tmpdir_".$username."_".rand();	
		      //$tmp_dir=$config['base_url']."curator_data/uploads/tmpdir_".$username."_".rand();
	
//	$raw_path= "rawdata/".$_FILES['file']['name'][1];
//	copy($_FILES['file']['tmp_name'][1], $raw_path);
	umask(0);
	
	connect_dev();
	
	
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
        $capyear_idx = implode(find("CAP_Year", $header),"");
        $bp_idx =  implode(find("Breeding_Program", $header),"");
        $location_idx = implode(find("Location", $header),"");
        $latlong_idx = implode(find("Lat/Long_of_field", $header),"");
        $collab_idx = implode(find("Collaborator", $header),"");
        $collabcode_idx = implode(find("Collaborator_Code", $header),"");
        //$test_idx = implode(find("Testing", $header),"");
        $experiment_idx = implode(find("Experiment", $header),"");
        $descexperiment_idx = implode(find("Descriptive_Name_of_Experiment", $header),"");
        $trialcode_idx = implode(find("Trial_Code", $header),"");
				$planting_date_idx = implode(find("Planting date", $header),"");
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
			//	echo "cap year". $capyear_idx;
				
/*	echo " indexes". "plant date".$planting_date_idx."seed rate". $seeding_rate_idx . "exp design" . $experimental_design_idx
					. "num of entries" . $num_of_entries_idx . "num of repli" . $num_of_replications_idx . "plot" . $plot_size_idx .
					"har area" . $harvested_area_idx . "irri" . $irrigation_idx . "harv date" . $harvest_date_idx . "other" . $other_remarks_idx;
	*/
	
		//  Step 2. Read in data, a line at a time  
        $run = 0;
        while (($data = fgetcsv($handle, 0, "\t")) !== FALSE) {
            if ($capyear_idx!==FALSE) 
						{
						/* check if year is  missing */
						$cap_year = trim($data[$capyear_idx]);
						
						if(!empty($cap_year))
						{
						
						$capyear[] = trim($data[$capyear_idx]);
						}
						
						else
						{
							echo " Year is missing. Please enter the year and upload again"."<br/>";
							exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
						}
						
						}
            
            
						$bp = trim($data[$bp_idx]);
            $bp_data[] = $bp;
            
							    
            
            
            
          
            
            $location = trim($data[$location_idx]);//location
            
          
            
            
            $location_data[] = $location;
            
            
            $latlong = trim($data[$latlong_idx]);//latlong
            $latlong_data[] = $latlong;
            
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
						            
            $plantingDate[] = trim($data[$planting_date_idx]);//planting date
           	$seedingRate[] = trim($data[$seeding_rate_idx]);//seeding Rate
           	$expDesign[] = trim($data[$experimental_design_idx]);//Experimental Design
           	$numEntries[] = trim($data[$num_of_entries_idx]);//number of entries
           	$numReplications[] = trim($data[$num_of_replications_idx]);//number of replications
            $plotSize[] = trim($data[$plot_size_idx]);//Plot Size
            $harvestArea[] = trim($data[$harvested_area_idx]);//Harvested Area
            $irrigation[] = trim($data[$irrigation_idx]);//Irrigation
            $harvestDate[] = trim($data[$harvest_date_idx]);//Harvest Date
            $otherRemarks[] = trim($data[$other_remarks_idx]);//Other Remarks
            
          } /* end of while*/  
            
            
            
     //var_dump($trialcode_data);  
			
		//	echo "count" .count($trialcode_data);   
            
	
	
	
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

			<?
				for ($i = 0; $i<count($trialcode_data); $i++)
				{
				//Extract data
			
			?>
			
			<tr>
			<td >
			<? 
			$newtext = wordwrap($capyear[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			<td >
			<?
			$newtext = wordwrap($bp_data[$i], 6, "\n", true);
			echo $newtext ?>
			</td>
			<td>
			<? 
			$newtext = wordwrap($location_data[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			<td >
			<? 
			$newtext = wordwrap($latlong_data[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			<td >
			<? 
			$newtext = wordwrap($collab[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			<td >
			<? 
			$newtext = wordwrap($collabCode[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			<td >
			<? 
			$newtext = wordwrap($experiment[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			<td >
			<? 
			$newtext = wordwrap($descExperiment[$i], 6, "\n", true);
			echo $newtext ?>
			</td>
			<td >
			<? 
			$newtext = wordwrap($trialcode_data[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
		
			<td >
			<? 
			$newtext = wordwrap($plantingDate[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			
			<td >
			<? 
			$newtext = wordwrap($seedingRate[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			
			<td >
			<? 
			$newtext = wordwrap($expDesign[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			
			<td >
			<? 
			$newtext = wordwrap($numEntries[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			
			<td >
			<? 
			$newtext = wordwrap($numReplications[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			
			<td >
			<? 
			$newtext = wordwrap($plotSize[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			
			<td >
			<? 
			$newtext = wordwrap($harvestArea[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			
			<td >
			<? 
			$newtext = wordwrap($irrigation[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			
			<td >
			<? 
			$newtext = wordwrap($harvestDate[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
			
			<td >
			<? 
			$newtext = wordwrap($otherRemarks[$i], 6, "\n", true);
			echo $newtext ?>
			</td> 
		
			</tr>
			<?
				}/* end of for loop */
			?>
			</tbody>
			</table>
			
		
		
		<input type="Button" value="Accept" onclick="javascript: update_database('<?echo $annotfile?>','<?echo $uploadfile?>','<?echo $username?>' )"/>
    <input type="Button" value="Cancel" onclick="history.go(-1); return;"/>
	
		<?
		
	
	
			
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

	
	connect_dev();
	
	$datafile = $_GET['linedata'];
	$filename_old = $_GET['file_name'];
	$filename = $filename_old.rand();
	$username = $_GET['user_name'];
	
	/* Read the annotation file */
	 $handle = fopen($datafile, "r");
        $header = fgetcsv($handle, 0, "\t");
        
        // Set up column indices; all columns are required
        $capyear_idx = implode(find("CAP_Year", $header),"");
        $bp_idx =  implode(find("Breeding_Program", $header),"");
        $location_idx = implode(find("Location", $header),"");
        $latlong_idx = implode(find("Lat/Long_of_field", $header),"");
        $collab_idx = implode(find("Collaborator", $header),"");
        $collabcode_idx = implode(find("Collaborator_Code", $header),"");
        $test_idx = implode(find("Testing", $header),"");
        $experiment_idx = implode(find("Experiment", $header),"");
        $descexperiment_idx = implode(find("Descriptive_Name_of_Experiment", $header),"");
        $trialcode_idx = implode(find("Trial_Code", $header),"");
				$planting_date_idx = implode(find("Planting date", $header),"");
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
				
/*	echo " indexes". "plant date".$planting_date_idx."seed rate". $seeding_rate_idx . "exp design" . $experimental_design_idx
					. "num of entries" . $num_of_entries_idx . "num of repli" . $num_of_replications_idx . "plot" . $plot_size_idx .
					"har area" . $harvested_area_idx . "irri" . $irrigation_idx . "harv date" . $harvest_date_idx . "other" . $other_remarks_idx;
	*/
	
		//  Step 2. Read in data, a line at a time  
        $run = 0;
        while (($data = fgetcsv($handle, 0, "\t")) !== FALSE) 
				{
            if ($capyear_idx!==FALSE) 
						{
						/* check if year is  missing */
						$cap_year = trim($data[$capyear_idx]);
						
						if(!empty($cap_year))
						{
						
						$capyear[] = trim($data[$capyear_idx]);
						}
						
						else
						{
							echo " Year is missing. Please enter the year and upload again"."<br/>";
							exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
						}
						
						}
            
            
						$bp = trim($data[$bp_idx]);
            $bp_data[] = $bp;
            
							    
            
            
            
          
            
            $location = trim($data[$location_idx]);//location
            
          
            
            
            $location_data[] = $location;
            
            
            $latlong = trim($data[$latlong_idx]);//latlong
            $latlong_data[] = $latlong;
            
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
			
							$sql = "SELECT experiment_uid FROM experiments WHERE trial_code = '{$trialcode}'";
							$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
					
							if (mysql_num_rows($res)==1) //yes, experiment found once
							{
								$row = mysql_fetch_assoc($res);
								$exp_id = $row['experiment_uid'];
							
								/* adding the trial code to the array */
									$trialcode_data[] = $trialcode;
								
								if (DEBUG>1) {echo "exp ID ".$exp_id."\n";}
							} elseif (mysql_num_rows($res)>1) //yes, experiment found more than once, bad
							{
								if (DEBUG>1) {echo "Trial code ".$trialcode." linked to multiple experiments-must fix"."<br/>";}
								$error_flag = ($error_flag)&(8);
								exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
							}
						}
						
						/* reading in all other remaing values */
						            
            $plantingDate[] = trim($data[$planting_date_idx]);//planting date
           	$seedingRate[] = trim($data[$seeding_rate_idx]);//seeding Rate
           	$expDesign[] = trim($data[$experimental_design_idx]);//Experimental Design
           	$numEntries[] = trim($data[$num_of_entries_idx]);//number of entries
           	$numReplications[] = trim($data[$num_of_replications_idx]);//number of replications
            $plotSize[] = trim($data[$plot_size_idx]);//Plot Size
            $harvestArea[] = trim($data[$harvested_area_idx]);//Harvested Area
            $irrigation[] = trim($data[$irrigation_idx]);//Irrigation
            $harvestDate[] = trim($data[$harvest_date_idx]);//Harvest Date
            $otherRemarks[] = trim($data[$other_remarks_idx]);//Other Remarks
            
          } /* end of while*/ 
          
          
          
          /* database manipulation */
          
          for ($i=0;$i<sizeof($trialcode_data);$i++)
					{
					
						/* dealing with escaping and other stuff */
						
						$trialcode = mysql_real_escape_string(trim($trialcode_data[$i]));
						$exp_short = mysql_real_escape_string(trim($experiment[$i]));
						$location = addslashes($location_data[$i]);
						$latlong = mysql_real_escape_string($latlong_data[$i]);
						$collaborator = mysql_real_escape_string($collab[$i]);
						
					
						$teststr= addcslashes(trim($plantingDate[$i]),"\0..\37!@\177..\377");
							
						if (!empty($teststr))
						{
							$plantingdate = $teststr;
						} 
						else
						{
							$plantingdate = '';
						}
						$descExpt = mysql_real_escape_string(trim($descExperiment[$i]));
						$exptDesign = mysql_real_escape_string($expDesign[$i]);
						$numEntries = intval($numEntries[$i]);
						$numReplications = intval($numReplications[$i]);
						$plotSize = mysql_real_escape_string($plotSize[$i]);
							// Harvest Date
							// convert Microsoft Excel timestamp to Unix timestamp
						
						$teststr= addcslashes(trim($harvestDate[$i]),"\0..\37!@\177..\377");
						
							//$teststr= addcslashes($harvestDate[$i]);
							
							
							if (!empty($teststr))
							{
								$harvestdate = $teststr;
							} 
							else
							{
								$harvestdate = '';
							}
					// Harvest Area
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
						
						// Insert or update experiment table data
						// First check if this trial code is in the database, if yes, then update all fields;
						// if no then insert into table
						$sql = "SELECT experiment_uid FROM experiments WHERE trial_code = '{$trialcode}'";
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
												experiment_short_name = '{$exp_short}',
												experiment_desc_name = '{$descExpt}',
												trial_code = '{$trialcode}',
												experiment_year = '{$capyear[$i]}',
												data_public_flag = '$data_public_flag',
												created_on = NOW()
											WHERE experiment_uid = $exp_id";
								if (DEBUG>2) {echo "update exp SQL ".$sql."\n";}
								
								mysql_query($sql) or die(mysql_error() . "<br>$sql");
								//update phenotype experiment information
								$sql = " UPDATE phenotype_experiment_info
											set
												collaborator = '{$collaborator}',
												planting_date = '{$plantingdate}',
												seeding_rate = '{$seedingRate[$i]}',
												experiment_design = '{$exptDesign}',
												number_replications = '{$numReplications}',
												number_entries = '{$numEntries}',
												plot_size = '{$plotSize}',
												harvest_area = '{$harvestedarea}',
												harvest_date = '{$harvestdate}',
												irrigation = '{$irrigation}',
												other_remarks = '{$otherremarks}',
												location = '{$location}',
												latitude_longitude = '{$latlong}',
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
										collaborator = '{$collaborator}',
										planting_date = '{$plantingdate}',
										seeding_rate = '{$seedingRate[$i]}',
										experiment_design = '{$exptDesign}',
										number_replications = '{$numReplications}',
										number_entries = '{$numEntries}',
										plot_size = '{$plotSize}',
										harvest_area = '{$harvestedarea}',
										harvest_date = '{$harvestdate}',
										irrigation = '{$irrigation}',
										other_remarks = '{$otherremarks}',
										location = '{$location}',
										latitude_longitude = '{$latlong}',
										created_on = NOW()
								";
							//	if (DEBUG>2) {echo "insert phenotype exp SQL ".$sql."\n";}
								mysql_query($sql) or die(mysql_error());
							
							
						
						} 
					}// end of for loop
          
          
        echo " <b>The Data is inserted/updated successfully </b>";
				echo"<br/><br/>";
	?>
	<a href="http://tht.vrac.iastate.edu:8080/SumanDirectory/input_annotations_upload_text.php"> Go Back To Main Page </a>
	<?
	
	$sql = "INSERT INTO input_file_log (file_name,users_name)
										VALUES('$filename', '$username')";
					
					
	$lin_table=mysql_query($sql) or die(mysql_error());  
          
	
		$footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
	
	
	
	} /* end of function type_database */


} /* end of class */




?>

