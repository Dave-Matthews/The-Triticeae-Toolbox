<?php
// Genotype annotation importer
// 10/25/2011  JLee   Ignore "cut" portion in annotation input file 

require 'config.php';
/*
 * Logged in page initialization
 */
include($config['root_dir'] . 'includes/bootstrap_curator.inc');
include($config['root_dir'] . 'curator_data/lineuid.php');
ini_set('auto_detect_line_endings', true);

connect();
loginTest();
$row = loadUser($_SESSION['username']);

ob_start();
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();

new Annotations_Check($_POST['function']);

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

  private function typeAnnotationCheck()  {
    global $config;
    include($config['root_dir'] . 'theme/admin_header.php');
    echo "<h2> Enter/Update Annotation Information: Validation</h2>"; 
    $this->type_Annotation();
    $footer_div = 1;
    include($config['root_dir'].'theme/footer.php');
  }
	
  private function type_Annotation() {
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
    umask(0);
    if(!file_exists($tmp_dir) || !is_dir($tmp_dir)) 
      mkdir($tmp_dir, 0777);
    $target_path=$tmp_dir."/";
    if($_SERVER['REQUEST_METHOD'] == "POST") 	
      $data_public_flag = $_POST['flag']; // 1:yes, 0:no
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
	if (move_uploaded_file($_FILES['file']['tmp_name'][0], $target_path.$uploadfile))  {
	  /* Start reading the input */
	  $annotfile = $target_path.$uploadfile;
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

	  // Set up header column; all columns are required, except Breeding Program and Comments.
	  $breedingProgIdx = implode(find("Breeding Program", $header),"");
	  $capDataProgIdx = implode(find("CAPdata Program", $header),"");
	  $yearIdx = implode(find("Year", $header),"");
	  $shortNameIdx = implode(find("Short Name", $header),"");
	  $trialCodeIdx = implode(find("Trial Code", $header),"");
	  $traitsIdx = implode(find("Traits", $header),"");
	  $platformIdx = implode(find("Platform", $header),"");
	  $processingDateIdx = implode(find("Processing Date", $header),"");
	  $manifestFileIdx = implode(find("Manifest File", $header),"");
	  $clusterFileIdx = implode(find("Cluster File", $header),"");
	  $opaNameIdx = implode(find("OPA Name", $header),"");
	  $analysisSWIdx = implode(find("Analysis Software", $header),"");
	  $swVersionIdx = implode(find("Software Version", $header),"");
	  $sampleSheetIdx = implode(find("Sample Sheet", $header),"");
	  $commentIdx = implode(find("Comments", $header),"");
  
	  // Check if a required column header is missing
	  if (($capDataProgIdx == "")||($yearIdx == "")||
	      ($shortNameIdx == "")||($trialCodeIdx == "")|| ($traitsIdx == "") || 
	      ($processingDateIdx == "") || ($manifestFileIdx == "") || ($clusterFileIdx == "") ||
	      ($opaNameIdx == "")  || ($analysisSWIdx == "") || ($swVersionIdx == "") ||
	      ($sampleSheetIdx == "") ) {
	    echo "ERROR: Missing one of these required columns. Please correct and upload again:" .
	      "<br>"." CAPdata Program: ".$capDataProgIdx."<br>"." Year: ". $yearIdx .
	      "<br>"." Short Name: ".$shortNameIdx."<br>"." Trial Code: ".$trialCodeIdx.
	      "<br>"." Traits: ". $traitsIdx ."<br>" ." Processing Date: ". $processingDateIdx.
	      "<br>"." Manifest File: ".  $manifestFileIdx. "<br>" ." Cluster File: ". $clusterFileIdx.
	      "<br>"." OPA Name: ". $opaNameIdx."<br>" ." Analysis Software: ". $analysisSWIdx.
	      "<br>"." Software Version: ". $swVersionIdx."<br>" ." Sample Sheet: ". $sampleSheetIdx."<br>";
	    exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
	  }
                      
	  // Store header
	  $i = 0;
	  foreach ($header as $value) 
	    $storageArr[0][$i++] = $value;   
	  // Store individual records
	  $i = 1;
	  $file_count = 1;
	  while(($line = fgets($reader)) !== FALSE) { 
	    if (feof($reader)) break;
	    if ($line == '') continue;
	    if ((stripos($line, '- cut -') > 0 )) break;
	    $j = 0;
	    $data = str_getcsv($line,"\t");
	    //Check for junk line
	    if (count($data) != 15)  die("There must be 15 content-containing columns.");;   
	    foreach ($data as $value)  {
	      if (empty($value) AND $header[$j] != "Breeding Program" AND $header[$j] != "Comments") {
		$error_flag = 1;
		echo "Field $j, $header[$j], is empty on data row $i.<br>";
	      }
	      $storageArr[$i][$j++] = $value;   
	    }
	    $i++;
	  }  
	  unset ($value);
	  fclose($reader);   
   
	  if ($error_flag > 0)  {
	    echo "ERROR: One or more fields contained blank values.<br>";
	    print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
	  } 	else {
	    // Display input data in a table for validation.
	    echo "<h3>We are reading the following data from the uploaded Annotation File.</h3>";
	    echo "<table><thead><tr>";
	    echo "<th >" . $storageArr[0][$breedingProgIdx] . "</th>";
	    echo "<th >" . $storageArr[0][$capDataProgIdx] . "</th>";
	    echo "<th >" . $storageArr[0][$yearIdx] . "</th>";
	    echo "<th >" . $storageArr[0][$shortNameIdx] . "</th>";
	    echo "<th >" . $storageArr[0][$trialCodeIdx] . "</th>";
	    echo "<th >" . $storageArr[0][$traitsIdx] . "</th>";
	    echo "<th >" . $storageArr[0][$platformIdx] . "</th>";
	    echo "<th >" . $storageArr[0][$processingDateIdx] . "</th>";
	    echo "<th >" . $storageArr[0][$manifestFileIdx] . "</th>";
	    echo "<th >" . $storageArr[0][$clusterFileIdx] . "</th>";
	    echo "<th >" . $storageArr[0][$opaNameIdx] . "</th>";
	    echo "<th >" . $storageArr[0][$analysisSWIdx] . "</th>";
	    echo "<th >" . $storageArr[0][$swVersionIdx] . "</th>";
	    echo "<th >" . $storageArr[0][$sampleSheetIdx] . "</th>";
	    echo "<th >" . $storageArr[0][$commentIdx] . "</th>";
	    echo "<tbody style='padding: 0; height: 200px; width: 2000px;  overflow: scroll;border: 1px solid #5b53a6;'>";
	    for ($i = 1; $i <= count($storageArr) ; $i++)  {
	      //Extract data
	      echo "<tr>";
	      $newtext = wordwrap($storageArr[$i][$breedingProgIdx], 10, "<br>", true);  echo "<td>$newtext"; 
	      $newtext = wordwrap($storageArr[$i][$capDataProgIdx], 10, "<br>", true); echo "<td>$newtext"; 
	      $newtext = wordwrap($storageArr[$i][$yearIdx], 10, "<br>", true); echo "<td>$newtext"; 
	      $newtext = wordwrap($storageArr[$i][$shortNameIdx], 10, "<br>", true); echo "<td>$newtext"; 
	      $newtext = wordwrap($storageArr[$i][$trialCodeIdx], 10, "<br>", true); echo "<td>$newtext"; 
	      $newtext = wordwrap($storageArr[$i][$traitsIdx], 10, "<br>", true); echo "<td>$newtext"; 
	      $newtext = wordwrap($storageArr[$i][$platformIdx], 10, "<br>", true); echo "<td>$newtext"; 
	      $newtext = wordwrap($storageArr[$i][$processingDateIdx], 10, "<br>", true); echo "<td>$newtext"; 
	      $newtext = wordwrap($storageArr[$i][$manifestFileIdx], 10, "<br>", true); echo "<td>$newtext";
	      $filelist[$file_count] = $storageArr[$i][$manifestFileIdx];
	      $file_count++; 
	      $newtext = wordwrap($storageArr[$i][$clusterFileIdx], 10, "<br>", true); echo "<td>$newtext";
	      $filelist[$file_count] = $storageArr[$i][$clusterFileIdx];
	      $file_count++; 
	      $newtext = wordwrap($storageArr[$i][$opaNameIdx], 10, "<br>", true); echo "<td>$newtext"; 
	      $newtext = wordwrap($storageArr[$i][$analysisSWIdx], 10, "<br>", true); echo "<td>$newtext"; 
	      $newtext = wordwrap($storageArr[$i][$swVersionIdx], 10, "<br>", true); echo "<td>$newtext"; 
	      $newtext = wordwrap($storageArr[$i][$sampleSheetIdx], 10, "<br>", true); echo "<td>$newtext";
	      $filelist[$file_count] = $storageArr[$i][$sampleSheetIdx];
	      $file_count++; 
	      $newtext = wordwrap($storageArr[$i][$commentIdx], 10, "<br>", true); echo "<td>$newtext";
	    }/* end of for loop */
	    ?>
	    </tbody></table><br>
		<b>Upload supporting files</b>
		<form action="curator_data/genotype_annotations_check.php" method="POST" enctype="multipart/form-data">
		<input type=hidden name=function value=typeDatabase><br>
		<input type=hidden name=linedata value='<?php echo $annotfile?>'/>
		<input type=hidden name=file_name value='<?php echo $uploadfile?>'/>
		<input type=hidden name=user_name value='<?php echo $username?>'/>
		<input type=hidden name=data_public_flag value='<?php echo $data_public_flag?>'/>
<?php
  	    $i = 1;
	    foreach ($filelist as $file) {
	      if (preg_match('/[A-Za-z]/',$file)) {
		echo "$i <input id='file[]' type='file' name='file[]' />$file<br>";
		$i++;
	      }
	    }
	    $filelist = implode(',',$filelist);
	    echo "<br>";
	    ?>
	    <input type="submit">
	       </form>
	       <!--input type="Button" value="Accept" --> 
	       <!--onclick="javascript: update_database('<?php echo $annotfile?>','<?php echo $uploadfile?>','<?php echo $username?>','<?php echo $data_public_flag?>','<?php echo $filelist?>' )"/-->
	       <!--input type="Button" value="Cancel" onclick="history.go(-1); return;"/><br-->
	       <?php
	       }
	}    
      }
    }    
  } /* end of type_GenoTypeAnnot_Display function*/
        
  private function type_Database() {
    global $config;
    include($config['root_dir'] . 'theme/admin_header.php');
    $datafile = $_POST['linedata'];
    $filename_old = $_POST['file_name'];
    $filename = $filename_old.rand();
    $username = $_POST['user_name'];
    $data_public_flag = $_POST['data_public_flag'];

    if (($reader = fopen($datafile, "r")) == FALSE) {
      error(1, "Unable to access datafile file $datafile");
      exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
    }
    // Check first line for header information
    if (($line = fgets($reader)) == FALSE) {
      error(1, "Unable to locate header names on first line of file.");
      exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
    }     
    $header = str_getcsv($line,"\t");
                    
    // Set up header column.
    $breedingProgIdx = implode(find("Breeding Program", $header),"");
    $capDataProgIdx = implode(find("CAPdata Program", $header),"");
    $yearIdx = implode(find("Year", $header),"");
    $shortNameIdx = implode(find("Short Name", $header),"");
    $trialCodeIdx = implode(find("Trial Code", $header),"");
    $traitsIdx = implode(find("Traits", $header),"");
    $platformIdx = implode(find("Platform", $header),"");
    $processingDateIdx = implode(find("Processing Date", $header),"");
    $manifestFileIdx = implode(find("Manifest File", $header),"");
    $clusterFileIdx = implode(find("Cluster File", $header),"");
    $opaNameIdx = implode(find("OPA Name", $header),"");
    $analysisSWIdx = implode(find("Analysis Software", $header),"");
    $swVersionIdx = implode(find("Software Version", $header),"");
    $sampleSheetIdx = implode(find("Sample Sheet", $header),"");
    $commentIdx = implode(find("Comments", $header),"");

    //upload files
    $i = 1;
    $raw_path= "../raw/genotype";
    foreach ($_FILES['file']['error'] as $key => $error) {
      if ($error == UPLOAD_ERR_OK) {
	$tmp_name = $_FILES['file']['tmp_name'][$key];
	$name = $_FILES['file']['name'][$key];
	move_uploaded_file($tmp_name, "$raw_path/$name");
	echo "successfully uploaded $name<br>\n";
      } else {
	echo "no file upload for entry $i<br>\n";
	//print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1);\"><br>";
      }
      $i++;
    }
 
    // Store individual records
    $i = 1;
    while(($line = fgets($reader)) !== FALSE) { 
      if (feof($reader)) break;
      // Handle blank line 
      if ($line == '') continue;
      if ((stripos($line, '- cut -') > 0 )) break;
      $j = 0;
      $data = str_getcsv($line,"\t");
      foreach ($data as $value)  
	$storageArr[$i][$j++] = trim($value);   
      $i++;
    }  
    unset ($value);
    fclose($reader);   
   
    $linkID = connect();  
    for ($i = 1; $i <= count($storageArr) ; $i++)  {
	$year = $storageArr[$i][$yearIdx];
	$bp_code = $storageArr[$i][$breedingProgIdx];
	$capDataProg = $storageArr[$i][$capDataProgIdx];
	$trialCode = $storageArr[$i][$trialCodeIdx];
	$shortName = $storageArr[$i][$shortNameIdx];
	$traits = $storageArr[$i][$traitsIdx];
	$platform = $storageArr[$i][$platformIdx];
	$processDate = $storageArr[$i][$processingDateIdx];
	$manifestF = $storageArr[$i][$manifestFileIdx];
	$clusterF =$storageArr[$i][$clusterFileIdx];
	$opaName = $storageArr[$i][$opaNameIdx];
	$analysisSW = $storageArr[$i][$analysisSWIdx];
	$swVer = $storageArr[$i][$swVersionIdx];
	$sampleSht =$storageArr[$i][$sampleSheetIdx];
	$comment = $storageArr[$i][$commentIdx];

	/* check if files are uploaded */
	$raw_path = "../raw/genotype";
	$tmp = "$raw_path/$manifestF";
	if (!file_exists($tmp)) {
	  echo "The Manifest file is missing, $manifestF<br>\n";
	  $manifestF = NULL;
	}
	$tmp = "$raw_path/$clusterF";
	if (!file_exists($tmp)) {
	  echo "The Cluster file is missing, $clusterF<br>\n";
	  $clusterF = NULL;
	}
	$tmp = "$raw_path/$sampleSht";
	if (!file_exists($tmp)) {
	  echo "The Sample Sheet file has is missing, $sampleSht<br>\n";
	  $sampleSht = NULL;
	}
                
	/* get dataset and BP uid*/
	$sql = "SELECT CAPdata_programs_uid
                        FROM CAPdata_programs
                        WHERE CAPdata_programs.data_program_code ='$bp_code'";
	//echo "bp lookup sql - " . $sql . "<br>";       
	$res = mysql_query($sql) or die("Database Error: Breeding Program lookup - ".mysql_error());
	$rdata = mysql_fetch_assoc($res);
	$bp_uid=$rdata['CAPdata_programs_uid'];
	if (empty($bp_uid)) {
	  error(1, "Breeding program '$bp_code' is not in the database.");
	  exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
	}
        
	$sql = "SELECT CAPdata_programs_uid
                            FROM CAPdata_programs
                            WHERE CAPdata_programs.data_program_code ='$capDataProg'";
	$res = mysql_query($sql) or die("Database Error: CAPdata Program lookup - ". mysql_error());
	$rdata = mysql_fetch_assoc($res);
	$cpData_uid=$rdata['CAPdata_programs_uid'];
	if (empty($cpData_uid)) {
	  error(1, "CAP data program '$capDataProg' is not in the database.");
	  exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
	}
               
	$sql = "SELECT datasets_uid
                            FROM datasets 
                            WHERE datasets.breeding_year='$year'
                            AND datasets.CAPdata_programs_uid = '$bp_uid'";
	$res = mysql_query($sql) or die("Database Error: Dataset lookup - ". mysql_error());
	$rdata = mysql_fetch_assoc($res);
	$datasets_uid=$rdata['datasets_uid'];
              
	$sql = "SELECT platform_uid 
                        FROM platform
                        WHERE platform_name = '$platform'";
	$res = mysql_query($sql) or die("Database Error: Dataset lookup - ". mysql_error());
	$rdata = mysql_fetch_assoc($res);
	$platform_uid = $rdata['platform_uid'];
	if (empty($platform_uid)) {
	  error(1, "Genotype platform '$platform' is not in the database.");
	  exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
	}
 
	$tmp = str_split($storageArr[$i][$yearIdx],2);
	$year_last2 = $tmp[1]; 

	/* Check trial code to duplicate entries */
	$sql = "SELECT experiment_uid
                            FROM experiments 
                            WHERE trial_code = '$trialCode'";
	$res = mysql_query($sql) or die("Database Error: Trial Code lookup failed - ". mysql_error());
	$e_uid = mysql_fetch_assoc($res);
	if ( !empty($e_uid) ) {
	  $exp_uid = $e_uid['experiment_uid'];
	  echo "update experiment $trialCode<br>\n";
	  $sql = "UPDATE experiments set experiment_short_name = '$shortName', traits = '$traits', experiment_year = $year, data_public_flag = $data_public_flag
                        WHERE trial_code = '$trialCode'";
	  $res = mysql_query($sql) or die("Database Error: Experiment record update failed - ". mysql_error());
	  //echo "$sql<br>\n";
	  $sql = "UPDATE genotype_experiment_info set processing_date = '$processDate', manifest_file_name = '$manifestF', cluster_file_name = '$clusterF', OPA_name = '$opaName', 
                        sample_sheet_filename = '$sampleSht', comments = '$comment', platform_uid = $platform_uid
                        WHERE experiment_uid = '$exp_uid'";
	  $res = mysql_query($sql) or die("Database Error: Genotype record update failed - ". mysql_error());
	  //echo "$sql<br>\n";
	} else {
                
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
	  $sql = "INSERT INTO genotype_experiment_info (experiment_uid, platform_uid, processing_date, manifest_file_name, cluster_file_name, OPA_name,
                    analysis_software, BGST_version_number, sample_sheet_filename, raw_datafile_archive, comments, updated_on, created_on)
                    VALUES ('$exp_uid', $platform_uid, '$processDate', '$manifestF', '$clusterF',
                        '$opaName', '$analysisSW', '$swVer', '$sampleSht',NULL , '$comment', NOW(), NOW())";
	  $res = mysql_query($sql) or die("Database Error: Genotype record insertion failed - ". mysql_error());
	  //echo "result code for exp info table:".$res."\n"; 
	}
      }

    echo " <b>The Data is inserted/updated successfully </b><br>";
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

