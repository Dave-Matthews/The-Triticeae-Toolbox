<?php
//**********************************************  
// Genotype annotation importer


// 10/25/2011  JLee   Ignore "cut" portion in annotation input file 
//
//
//**********************************************  

require 'config.php';
//require_once("../includes/common_import.inc");
/*
 * Logged in page initialization
 */
include($config['root_dir'] . 'includes/bootstrap_curator.inc');
include($config['root_dir'] . 'curator_data/lineuid.php');

connect();
loginTest();

/* ******************************* */
$row = loadUser($_SESSION['username']);

////////////////////////////////////////////////////////////////////////////////
ob_start();

authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();


new Annotations_Check($_GET['function']);

class Annotations_Check {

    private $delimiter = "\t";
    private $storageArr = array (array());
	// Using the class's constructor to decide which action to perform
	public function __construct($function = null) {	
		switch($function) 	{
			case 'typeDatabase':
				$this->type_Database(); /* update database */
				break;
				
			default:
				$this->typeAnnotationCheck(); /* intial case*/
				break;
			
		}	
	}

    private function typeAnnotationCheck() 	{
		global $config;
		include($config['root_dir'] . 'theme/admin_header.php');

		echo "<h2> Enter/Update Annotation Information: Validation</h2>"; 
			
		$this->type_Annotation();
		$footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
	}
	
	private function type_Annotation() 	{
	?>
	<script type="text/javascript">
	
        function update_database(filepath, filename, username, data_public_flag) 	{
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
        table.marker {background: none; border-collapse: collapse}
        th.marker { background: #5b53a6; color: #fff; padding: 5px 0; border: 0; }
        td.marker { padding: 5px 0; border: 0 !important; }
    </style>
<?php
        $error_flag = 0;
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
        if($_SERVER['REQUEST_METHOD'] == "POST") 	{
            $data_public_flag = $_POST['flag']; //1:yes, 0:no
            //echo" we got the value for data flag".$data_public_flag1;
        }
	
        if ($_FILES['file']['name'][0] == "") {
            error(1, "No File Uploaded");
            print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
        }
        else {
			$uploadfile=$_FILES['file']['name'][0];
 				
            $uftype=$_FILES['file']['type'][0];
            if (strpos($uploadfile, ".txt") === FALSE) {
                error(1, "Expecting an tab-delimited text file. <br> The type of the uploaded file is ".$uftype);
                print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
            }
            else {
			    if(move_uploaded_file($_FILES['file']['tmp_name'][0], $target_path.$uploadfile)) 	{
                    /* start reading the input */
                    $annotfile = $target_path.$uploadfile;
                    //echo "Annotate file - " . $annotfile . "<br>";
                    /* Read the annotation file */
                    if (($reader = fopen($annotfile, "r")) == FALSE) {
                        error(1, "Unable to access file.");
                        exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
                    }
                    // Check first line for header information
                    if (($line = fgets($reader)) == FALSE) {
                        error(1, "Unable to locate header names on first line of file.");
                        exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
                    }     
                     $header = str_getcsv($line,"\t");
                     
                    // Set up header column; all columns are required
                    $breedingProgIdx = implode(find("Breeding Program", $header),"");
                    $capDataProgIdx = implode(find("CAPdata Program", $header),"");
                    $yearIdx = implode(find("Year", $header),"");
                    $shortNameIdx = implode(find("Short Name", $header),"");
                    $trialCodeIdx = implode(find("Trial Code", $header),"");
                    $traitsIdx = implode(find("Traits", $header),"");
                    $processingDateIdx = implode(find("Processing Date", $header),"");
                    $manifestFileIdx = implode(find("Manifest File", $header),"");
                    $clusterFileIdx = implode(find("Cluster File", $header),"");
                    $opaNameIdx = implode(find("OPA Name", $header),"");
                    $analysisSWIdx = implode(find("Analysis Software", $header),"");
                    $swVersionIdx = implode(find("Software Version", $header),"");
                    $sampleSheetIdx = implode(find("Sample Sheet", $header),"");
  
                    // Check if a required col is missing
                    if (($breedingProgIdx == "")||($capDataProgIdx == "")||($yearIdx == "")||
                        ($shortNameIdx = "")||($trialCodeIdx == "")|| ($traitsIdx == "") || 
                        ($processingDateIdx == "") || ($manifestFileIdx == "") || ($clusterFileIdx == "") ||
                        ($opaNameIdx == "")  || ($analysisSWIdx == "") || ($swVersionIdx == "") ||
                        ($sampleSheetIdx == "") ) {
                        echo "ERROR: Missing One of these required Columns. Please correct it and upload again: <br> Breeding Program - ".$breedingProgIdx.
                                "<br>"." CAPdata Program - ".$capDataProgIdx."<br>"." Year - ". $yearIdx .
                                "<br>"." Short Name - ".$shortNameIdx."<br>"." Trial Code - ".$trialCodeIdx.
                                "<br>"." Traits - ". $traitsIdx ."<br>" ." Processong Date - ". $processingDateIdx.
                                "<br>"." Manifest File - ".  $manifestFileIdx. "<br>" ." Cluster File - ". $clusterFileIdx.
                                "<br>"." OPA Name - ". $opaNameIdx."<br>" ." Analysis Software - ". $analysisSWIdx.
                                "<br>"." Software Version - ". $swVersionIdx."<br>" ." Sample - ". $sampleSheetIdx."<br>";
                        exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
                    }
                      
                    // Store header
                    $i = 0;
                    foreach ($header as &$value)  {                     
                        $storageArr[0][$i++] = $value;   
                    }
                    // Store individual records
                    $i = 1;
                    while(($line = fgets($reader)) !== FALSE) { 
                        if (feof($reader)) {
                            break;
                        }

                        if (trim($line) == '') {
                            continue;
                        }


						if ((stripos($line, '- cut -') > 0 )) break;


                        $j = 0;
                        $data = str_getcsv($line,"\t");
                        
                        //Check for junk line
                        if (count($data) < 13)  break;   
                                                
                        foreach ($data as $value)  {
                            //echo $value."<br>";
                            if (empty($value)) {
                                $error_flag = 1;
                            }
                            $storageArr[$i][$j++] = $value;   
                        }
                        $i ++;
                    }  
                    unset ($value);
                    fclose($reader);   
   
                    if ($error_flag > 0)  {
                        echo "ERROR DETECT: One or more fields contained blank values"."<br/>";
                        print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
                    } 	else {
                        // display input data into table for validation 
                        echo "<h3>We are reading following data from the uploaded Input Annotation File</h3>";
                        echo "<table >";
                        echo "<thead>";
                        echo "<tr>";
                        echo "<th >" . $storageArr[0][$breedingProgIdx] . "</th>";
                        echo "<th >" . $storageArr[0][$capDataProgIdx] . "</th>";
                        echo "<th >" . $storageArr[0][$yearIdx] . "</th>";
                        echo "<th >" . $storageArr[0][$shortNameIdx] . "</th>";
                        echo "<th >" . $storageArr[0][$trialCodeIdx] . "</th>";
                        echo "<th >" . $storageArr[0][$traitsIdx] . "</th>";
                        echo "<th >" . $storageArr[0][$processingDateIdx] . "</th>";
                        echo "<th >" . $storageArr[0][$manifestFileIdx] . "</th>";
                        echo "<th >" . $storageArr[0][$clusterFileIdx] . "</th>";
                        echo "<th >" . $storageArr[0][$opaNameIdx] . "</th>";
                        echo "<th >" . $storageArr[0][$analysisSWIdx] . "</th>";
                        echo "<th >" . $storageArr[0][$swVersionIdx] . "</th>";
                        echo "<th >" . $storageArr[0][$sampleSheetIdx] . "</th>";
                        echo "</tr>"."<br/>";
                        echo "<thead>"."<br/>";
                        ?>                   
                        <tbody style="padding: 0; height: 200px; width: 2000px;  overflow: scroll;border: 1px solid #5b53a6;">	
                        <?php
                        for ($i = 1; $i <= count($storageArr) ; $i++)  {
                            //Extract data
                        ?>
                            <tr>
                            <td >
                            <?php $newtext = wordwrap($storageArr[$i][$breedingProgIdx], 10, "<br>", true);  echo $newtext ?>
                            </td> 
                            <td >
                            <?php $newtext = wordwrap($storageArr[$i][$capDataProgIdx], 10, "<br>", true); echo $newtext ?>
                            </td>
                            <td>
                            <?php $newtext = wordwrap($storageArr[$i][$yearIdx], 10, "<br>", true); echo $newtext ?>
                            </td> 
                            <td >
                            <?php $newtext = wordwrap($storageArr[$i][$shortNameIdx], 10, "<br>", true); echo $newtext ?>
                            </td> 
                            <td >
                            <?php $newtext = wordwrap($storageArr[$i][$trialCodeIdx], 10, "<br>", true); echo $newtext ?>
                            </td> 
                            <td >
                            <?php $newtext = wordwrap($storageArr[$i][$traitsIdx], 10, "<br>", true); echo $newtext ?>
                            </td> 
                            <td >
                            <?php $newtext = wordwrap($storageArr[$i][$processingDateIdx], 10, "<br>", true); echo $newtext ?>
                            </td> 
                            <td >
                            <?php $newtext = wordwrap($storageArr[$i][$manifestFileIdx], 10, "<br>", true); echo $newtext ?>
                            </td>
                            <td >
                            <?php $newtext = wordwrap($storageArr[$i][$clusterFileIdx], 10, "<br>", true); echo $newtext ?>
                            </td> 
                            <td >
                            <?php $newtext = wordwrap($storageArr[$i][$opaNameIdx], 10, "<br>", true); echo $newtext ?>
                            </td> 
                            <td >
                            <?php $newtext = wordwrap($storageArr[$i][$analysisSWIdx], 10, "<br>", true); echo $newtext ?>
                            </td>
                            <td >
                            <?php $newtext = wordwrap($storageArr[$i][$swVersionIdx], 10, "<br>", true); echo $newtext ?>
                            </td>
                            <td >
                            <?php $newtext = wordwrap($storageArr[$i][$sampleSheetIdx], 10, "<br>", true); echo $newtext ?>
                            </td>
                            </tr>
                        <?php
                        }/* end of for loop */
                        ?>
                        </tbody>
                        </table>
                        <br>
                        <input type="Button" value="Accept" 
                        onclick="javascript: update_database('<?php echo $annotfile?>','<?php echo $uploadfile?>','<?php echo $username?>','<?php echo $data_public_flag?>' )"/>
                        <input type="Button" value="Cancel" onclick="history.go(-1); return;"/>
                        <?php
                    }
                }    
			}
        }    
    } /* end of type_GenoTypeAnnot_Display function*/
        
    private function type_Database() {
        global $config;
        include($config['root_dir'] . 'theme/admin_header.php');
        $datafile = $_GET['linedata'];
        $filename_old = $_GET['file_name'];
        $filename = $filename_old.rand();
        $username = $_GET['user_name'];
        $data_public_flag = $_GET['data_public_flag'];

        //echo "Datafile - ". $datafile . "<br>";
        //echo "Flag - " . $data_public_flag . "<br>";

        if (($reader = fopen($datafile, "r")) == FALSE) {
            error(1, "Unable to access file.");
            exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
       }
        // Check first line for header information
        if (($line = fgets($reader)) == FALSE) {
            error(1, "Unable to locate header names on first line of file.");
            exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
        }     
        $header = str_getcsv($line,"\t");
                    
         // Set up header column; all columns are required
        $breedingProgIdx = implode(find("Breeding Program", $header),"");
        $capDataProgIdx = implode(find("CAPdata Program", $header),"");
        $yearIdx = implode(find("Year", $header),"");
        $shortNameIdx = implode(find("Short Name", $header),"");
        $trialCodeIdx = implode(find("Trial Code", $header),"");
        $traitsIdx = implode(find("Traits", $header),"");
        $processingDateIdx = implode(find("Processing Date", $header),"");
        $manifestFileIdx = implode(find("Manifest File", $header),"");
        $clusterFileIdx = implode(find("Cluster File", $header),"");
        $opaNameIdx = implode(find("OPA Name", $header),"");
        $analysisSWIdx = implode(find("Analysis Software", $header),"");
        $swVersionIdx = implode(find("Software Version", $header),"");
        $sampleSheetIdx = implode(find("Sample Sheet", $header),"");
 
         // Store individual records
        $i = 1;
        while(($line = fgets($reader)) !== FALSE) { 
            if (feof($reader)) {
                break;
            }
            
			// Handle blank line 
			if (trim($line) == '') {
                continue;
            }

			if ((stripos($line, '- cut -') > 0 )) break;

            $j = 0;
            $data = str_getcsv($line,"\t");
                        
            //Check for junk line
            if (count($data) < 13)  break;   
                                                
            foreach ($data as $value)  {
                //echo $value."<br>";
                if (empty($value)) {
                    $error_flag = 1;
                }
                $storageArr[$i][$j++] = trim($value);   
            }
            $i ++;
        }  
        unset ($value);
        fclose($reader);   
   
        if ($error_flag > 0)  {
            echo "ERROR DETECT: One or more fields contained blank values"."<br/>";
            print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
        } 	else {
            $linkID = connect();  
            for ($i = 1; $i <= count($storageArr) ; $i++)  {

                $year = $storageArr[$i][$yearIdx];
                $bp_code = $storageArr[$i][$breedingProgIdx];
                $capDataProg = $storageArr[$i][$capDataProgIdx];
                $trialCode = $storageArr[$i][$trialCodeIdx];
                $shortName = $storageArr[$i][$shortNameIdx];
                $traits = $storageArr[$i][$traitsIdx];
                $processDate = $storageArr[$i][$processingDateIdx];
                $manifestF = $storageArr[$i][$manifestFileIdx];
                $clusterF =$storageArr[$i][$clusterFileIdx];
                $opaName = $storageArr[$i][$opaNameIdx];
                $analysisSW = $storageArr[$i][$analysisSWIdx];
                $swVer = $storageArr[$i][$swVersionIdx];
                $sampleSht =$storageArr[$i][$sampleSheetIdx];
                
                /* get dataset and BP uid*/
                $sql = "SELECT CAPdata_programs_uid
                        FROM CAPdata_programs
                        WHERE CAPdata_programs.data_program_code ='$bp_code'";
                //echo "bp lookup sql - " . $sql . "<br>";       
                $res = mysql_query($sql) or die("Database Error: Breeding Program lookup - ".mysql_error());
                $rdata = mysql_fetch_assoc($res);
                $bp_uid=$rdata['CAPdata_programs_uid'];
                //echo "bp_uid ".$bp_uid."<br>";
        
                $sql = "SELECT CAPdata_programs_uid
                            FROM CAPdata_programs
                            WHERE CAPdata_programs.data_program_code ='$capDataProg'";
                $res = mysql_query($sql) or die("Database Error: CAPdata Program lookup - ". mysql_error());
                $rdata = mysql_fetch_assoc($res);
                $cpData_uid=$rdata['CAPdata_programs_uid'];
                //echo "cpData_uid ".$cpData_uid."<br>";
               
                $sql = "SELECT datasets_uid
                            FROM datasets 
                            WHERE datasets.breeding_year='$year'
                            AND datasets.CAPdata_programs_uid = '$bp_uid'";
                $res = mysql_query($sql) or die("Database Error: Dataset lookup - ". mysql_error());
                $rdata = mysql_fetch_assoc($res);
                $datasets_uid=$rdata['datasets_uid'];
                //echo " datasets_uid ".$datasets_uid."<br>";
               
                $tmp = str_split($storageArr[$i][$yearIdx],2);
                $year_last2 = $tmp[1]; 

                /* Check trial code to duplicate entries */
                $sql = "SELECT experiment_uid
                            FROM experiments 
                            WHERE trial_code = '$trialCode'";
                $res = mysql_query($sql) or die("Database Error: Trial Code lookup failed - ". mysql_error());
                $e_uid = mysql_fetch_assoc($res);
                if ( !empty($e_uid) ) {
                    error(1, "Trial code - ". $trialCode . " already exist. <br>Please fix and re-import starting at this location." );
                    exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
                }
                
                /* If dataset does not exist, then create it, and get ID */
                if ($datasets_uid===NULL) {
                    $ds_name = $storageArr[$i][$breedingProgIdx].$year_last2;
                    $sql = "INSERT INTO datasets (CAPdata_programs_uid, breeding_year, dataset_name, updated_on, created_on)
                       VALUES ($bp_uid, $year, '$ds_name', NOW(), NOW())";
                    $res = mysql_query($sql) or die("Database Error: Dataset insertion failed - ". mysql_error());
                    //echo "Dataset sql code: ".$sql . "<br>";
                    $sql = "SELECT MAX(datasets_uid) AS dataid FROM datasets";
                    $res = mysql_query($sql) or die("Database Error: Unable to lookup last dataset uid - ". mysql_error());
                    $rdata = mysql_fetch_assoc($res);
                    $datasets_uid=$rdata['dataid'];
                    //echo " datasets_uid ".$datasets_uid."\n"; 
                }
              
                /* Enter a new experiment and fill in datasets_experiments table*/
                $sql = "INSERT INTO experiments (experiment_type_uid, CAPdata_programs_uid, experiment_short_name, trial_code, traits,
                        experiment_year,data_public_flag, updated_on, created_on)
                        VALUES ('2', $cpData_uid, '$shortName', '$trialCode', '$traits',
                        $year, $data_public_flag, NOW(), NOW())";
				//echo "Experiment insert " .$sql."<br>";
                $res = mysql_query($sql) or die("Database Error: Experiment record insertion failed - ". mysql_error());
                //echo "result code experiment table:".$res; 
                
                /* Get experiment ID*/
                $sql = "SELECT MAX(experiment_uid) AS expid FROM experiments";
                $res = mysql_query($sql) or die("Database Error: Can't determined last experiment uid - ". mysql_error());
                $rdata = mysql_fetch_assoc($res);
                $exp_uid=$rdata['expid'];
                //echo " exp_uid ".$exp_uid."\n";
                    
                $sql = "INSERT INTO datasets_experiments (experiment_uid, datasets_uid, updated_on, created_on)
                        VALUES ('$exp_uid', '$datasets_uid', NOW(), NOW())";
                $res = mysql_query($sql) or die("Database Error: Dataset experiment record insertion failed - ". mysql_error());
                //echo "result code for ds_experiment table:".$res;
                $sql = "SELECT datasets_experiments_uid FROM datasets_experiments WHERE experiment_uid = $exp_uid AND datasets_uid = $datasets_uid";
                $res = mysql_query($sql) or die("Database Error: Unable to retrieve dataset experiment info - ". mysql_error());
                $rdata = mysql_fetch_assoc($res);
                $de_uid=$rdata['datasets_experiments_uid'];
                //echo " de_uid ".$de_uid."\n"; 
                    
                /*  Fill in genotype_experiments table */
                $sql = "INSERT INTO genotype_experiment_info (experiment_uid, processing_date, manifest_file_name, cluster_file_name, OPA_name,
                    analysis_software, BGST_version_number, sample_sheet_filename, raw_datafile_archive, updated_on, created_on)
                    VALUES ('$exp_uid', '$processDate', '$manifestF', '$clusterF',
                        '$opaName', '$analysisSW', '$swVer', '$sampleSht',NULL , NOW(), NOW())";
                $res = mysql_query($sql) or die("Database Error: Genotype record insertion failed - ". mysql_error());
                //echo "result code for exp info table:".$res."\n"; 
            }
        }
        echo " <b>The Data is inserted/updated successfully </b><br>";
        echo "<br>Please email to <a href='mailto:tht_curator@graingenes.org' > tht_curator@graingenes.org</a> the indicated cluster and samplesheet files for inclusion into our system. <br> Thank you.";     
        echo "<br/><br/>";
?>
        <a href="./curator_data/genotype_annotations_upload.php"> Go Back To Main Page </a>
    <?php
        $sql = "INSERT INTO input_file_log (file_name,users_name)
			VALUES('$filename', '$username')";
						
        $lin_table=mysql_query($sql) or die("Database Error: Log record insertion failed - ". mysql_error());
        $footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
    } /* end of function type_database */
} /* end of class */
  
?>

