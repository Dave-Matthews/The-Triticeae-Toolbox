<?php
//*********************************************
// Genotype data importer - also contains various   
// pieces of import code by Julie's team @ iowaStateU  

// 11/01/2011  JLee   Reinstate Allele freq calculations 
// 10/25/2011  JLee   Ignore "cut" portion of input file 

// 10/18/2011 JLee  Replace loop control "next" with "continue"
// 10/17/2011 JLee  Add username and resubmission entry to 
//					input file log table
// 10/17/2011  JLee Create of input file log entry
// 9/16/2011  JLee  Modify to support new 1D format 
// 9/2/2011   JLee  Modify to remove allele freq stuff   

// 5/9/2011	 JLee	Fix formula	for calculating MAF value	
// 4/11/2011 JLee  Add ability to handle zipped data files
//

// Temporary patch code for 1D data

// Written By: John Lee
//*********************************************
$progPath = realpath(dirname(__FILE__).'/../').'/';

require $progPath. 'includes/bootstrap_curator.inc';
require_once $progPath . 'includes/email.inc';

$num_args = $_SERVER["argc"];
$fnames = $_SERVER["argv"];
$lineTransFile = $fnames[1];
$gDataFile = $fnames[2];
$emailAddr = $fnames[3];
$urlPath = $fnames[4];
$userName = $fnames[5];
$filename = stristr ($gDataFile,basename ($gDataFile));

$error_flag = 0;
$lineExpHash = array ();
$lineDsHash = array ();
$curTrialCode = '';
$gName = '';

echo "Start time - ". date("m/d/y : H:i:s", time()) ."\n"; 
echo "Translate File - ". $lineTransFile. "\n";
echo "Genotype Data File - ". $gDataFile. "\n";
echo "URL - " . $urlPath . "\n";
echo "Email - ". $emailAddr."\n";

$mysqli = connecti(); 

$target_Path = substr($lineTransFile, 0, strrpos($lineTransFile, '/')+1);
$tPath = str_replace('./','',$target_Path);

$errorFile = $target_Path."importError.txt";
echo $errorFile."\n";
if (($errFile = fopen($errorFile, "w")) === FALSE) {
   echo "Unable to open the error log file.";
   exit(1);
}

// Testing for non-processing
//exit (1);
// ******* Email Stuff *********
//senders name
$Name = "Genotype Data Importer"; 
//senders e-mail adress
$sql ="SELECT value FROM  settings WHERE  name = 'capmail'";
$res = mysqli_query($mysqli, $sql) or die("Database Error: setting lookup - ". mysqli_error($mysqli)."\n\n$sql");
$rdata = mysqli_fetch_assoc($res);
$myEmail=$rdata['value'];
$mailheader = "From: ". $Name . " <" . $myEmail . ">\r\n"; //optional headerfields
$subject = "Genotype import results";

//Check inputs
 if ($lineTransFile == "") {
    exitFatal ($errFile,  "No Line Translation File Uploaded.");
}  
  
if ($gDataFile == "") {
    exitFatal ($errFile, "No Genotype Data Uploaded.");
}  

if ($emailAddr == "") {
    echo "No email address. \n";
    exit (1);
}  

// Check for zip file
if (strpos($gDataFile, ".zip") == TRUE) {
	echo "Unzipping the genotype data file...\n";
	$zip = new ZipArchive;
	$zip->open($gDataFile) || exitFatal ($errFile, "Unable to open zip file, please check zip format.");
	$gName = $zip->getNameIndex(0);
	$zip->extractTo($target_Path) || exitFatal ($errFile, "Failed to extract file from the zip file.");
    $zip->close()  || exitFatal ($errFile, "Failed to close zip file.");
	$gDataFile = $target_Path . $gName;
	echo "Genotype data unzipping done.\n";
}

 /* Read the file */
 if (($reader = fopen($lineTransFile, "r")) == FALSE) {
    exitFatal ($errFile, "Unable to access translate file.");
}
            
 // Check first line for header information
if (($line = fgets($reader)) == FALSE) {
    exitFatal ($errFile, "Unable to locate header names on first line of file.");
}     

echo "Processing line translation file...\n";

$header = str_getcsv($line,"\t");
 // Set up header column; all columns are required
$lineNameIdx = implode(find("Line Name", $header),"");
$trialCodeIdx = implode(find("Trial Code", $header),"");
            
if (($lineNameIdx == "")||($trialCodeIdx == "")) {
   exitFatal ($errFile,"ERROR: Missing one or more of the required columns in the line translation file. Please correct it and try upload again.");
}
  
// Store individual records
while(($line = fgets($reader)) !== FALSE) { 
    //chop ($line, "\r");
    if (strlen($line) < 2) continue;
    if (feof($reader)) break;
    if (empty($line)) continue;
    if ((stripos($line, '- cut -') > 0 )) break;

    //echo "$line <br>";  
                
    $data = str_getcsv($line,"\t");
                        
    //Check for junk line
    if (count($data) != 2) {
        exitFatal ($errFile, "ERROR: Invalid entry in Line Translation file - '$line' ");
    }
    $trialCodeStr = $data[$trialCodeIdx];
    $lineStr = $data[$lineNameIdx];
                
    //echo  $lineStr . " - ". $trialCodeStr. "<br>"; 
    // Trial Code processing
    if (($curTrialCode != $trialCodeStr) && ($trialCodeStr != '')) {
                    
        $sql = "SELECT experiment_uid FROM experiments WHERE trial_code = '$trialCodeStr'"; 
        $res = mysqli_query($mysqli, $sql)
            or exitFatal ($errFile, "Database Error: Experiment uid lookup - ".mysqli_error($mysqli));
        if ($row = mysqli_fetch_assoc($res)) {
          $exp_uid = implode(",", $row);
        } else {
          exitFatal($errFile, "not found - $sql");
        }
        
        $sql = "SELECT datasets_experiments_uid FROM datasets_experiments WHERE experiment_uid = '$exp_uid'";            
        $res = mysqli_query($mysqli, $sql)
            or exitFatal ($errFile, "Database Error: Dataset experiment uid lookup - ".mysqli_error($mysqli));
        if ($row = mysqli_fetch_assoc($res)) {
          $de_uid=implode(",", $row);
        } else {
          exitFatal($errFile, "not found - $sql");
        }
        $curTrialCode = $trialCodeStr;
    } 
    $lineExpHash[$lineStr] = $exp_uid;
    $lineDsHash[$lineStr] = $de_uid;
}    
fclose($reader);   
echo "Line translation file processing done.\n";

echo "Start genotyping record creation process...\n";
//Process Genotype data
/* start reading the input */
//echo "genotype file - " . $gDataFile . "<br>";

/* Read the file */
if (($reader = fopen($gDataFile, "r")) == FALSE) {
    exitFatal ($errFile, "Unable to access genotype data file.");
}
        
//Advance to data header area
while(!feof($reader))  {
    $line = fgets($reader);
    if (stripos($line, 'SNP Name') !== false)  break;    
}
        
if (feof($reader)) {
    exitFatal ($errFile, "Unable to locate genotype header line.");
}
  
$header = str_getcsv($line,"\t");
                     
// Set up header column; all columns are required
$markerIdx = implode(find("SNP Name", $header),"");
$lineNameIdx = implode(find("Sample ID", $header),"");
//$gtScoreIdx = implode(find("GT Score", $header),"");
//$gcScoreIdx = implode(find("GC Score", $header),"");
//$thetaIdx = implode(find("Theta", $header),"");
//$rIdx = implode(find("R", $header),",");
//$xIdx = implode(find("X", $header),",");
//$yIdx = implode(find("Y", $header),",");
//$xRawIdx = implode(find("X Raw", $header),"");
//$yRawIdx = implode(find("Y Raw", $header),"");
$allele1Idx = implode(find("Allele1 - AB", $header),"");
$allele2Idx = implode(find("Allele2 - AB", $header),"");

if (($lineNameIdx == "")||($lineNameIdx == "") || ($allele1Idx == "") || ($allele2Idx == "")) {

    exitFatal ($errFile, "ERROR: Missing One of these required columns. Please correct it and upload again: \n SNP Name - ".$markerIdx.
        "\n"." Sample ID - ".$lineNameIdx."\n". " Allele1 - AB - ". $allele1Idx. "\n"." Allele2 - AB - ". $allele2Idx);
}
$tArray = explode (',',$rIdx);
$rIdx = $tArray[0];
$tArray = explode (',',$xIdx);
$xIdx = $tArray[0];
$tArray = explode (',',$yIdx);
$yIdx = $tArray[0];
unset($tArray);
    
$rowNum = 0;
$line_name = "qwerty";
$errLines = 0;
    
while (!feof($reader))  {
    // If we have too many errors stop processing - something is wrong
    If ($errLines > 1000) {
       exitFatal ($errFile, "ERROR: Too many import lines have problem."); 
    }    
    $line = fgets($reader);
    if (strlen($line) < 2) continue;
    if (empty($line)) continue;
    if (feof($reader)) break;
    //echo "$line <br>";
    $data = str_getcsv($line,"\t");
    $num = count($data);		// number of fields
    // Check line for missing column    
    if ($num != 4) { 
        $msg = "ERROR: Wrong number of entries for line - " . $line;
        fwrite($errFile, $msg);
        $errLines++;
        continue;
    }    
    
    $rowNum++;		// number of lines
    $markerflag = 0;        //flag for checking marker existence
    $marker = $data[$markerIdx];
    echo "+ working on ". $marker ." ". $data[$lineNameIdx]."\n";
            
    /* check if marker is EST synonym, if not found, then check name */
    $sql ="SELECT ms.marker_uid FROM  marker_synonyms AS ms WHERE ms.value='$marker'";
    $res = mysqli_query($mysqli, $sql) or exitFatal ($errFile, "Database Error: Marker synonym lookup - ". mysqli_error($mysqli)."\n\n$sql");
    $rdata = mysqli_fetch_assoc($res);
    $marker_uid=$rdata['marker_uid'];
    if (empty($marker_uid)) {
        $sql = "SELECT m.marker_uid FROM  markers AS m WHERE m.marker_name ='$marker'";
        $res = mysqli_query($mysqli, $sql) or exitFatal ($errFile, "Database Error: Marker lookup - ". mysqli_error($mysqli)."\n\n$sql");
	    if (mysqli_num_rows($res) < 1) {
            $markerflag = 1;
            $msg = 'ERROR:  marker not found '.$marker.'\t'. $line;
            fwrite($errFile, $msg);
            $errLines++;
            continue;
        } else {
		    $rdata = mysql_fetch_assoc($res);
		    $marker_uid=$rdata['marker_uid'];
        }
    }
    
    if ($markerflag == 0) {
	/* get line record ID only do if line name changed*/
	//echo $line_name,"\n";
        if ($line_name != $data[$lineNameIdx]) {
	        $line_name = $data[$lineNameIdx];
            //echo "line name = " . $line_name. "<br>";
            $line_uid = get_lineuid ($line_name);
            if ($line_uid == FALSE) {
                $msg = $line_name . " cannot be found, upload stopped\n";
                exitFatal ($errFile, $msg);
            }
            $line_uid = implode(",",$line_uid);
            $exp_uid = $lineExpHash[$line_name];
            //echo "exp_uid = " . $exp_uid . "<br>";
            $de_uid = $lineDsHash[$line_name];
            //echo "de_uid = " . $exp_uid . "<br>";
        }
				
        /* get thtbase_uid. If null, then we have to create this ID */
	    $sql = "SELECT tht_base_uid FROM tht_base WHERE experiment_uid= '$exp_uid' AND line_record_uid='$line_uid' ";
	    $rtht = mysqli_query($mysqli, $sql) or exitFatal ($errFile, "Database Error: tht_base lookup - ". mysqli_error($mysqli) . ".\n\n$sql");
	    $rqtht = mysqli_fetch_assoc($rtht);
	    $tht_uid = $rqtht['tht_base_uid'];
				
	    if (empty($tht_uid)) {
            $sql ="INSERT INTO tht_base (line_record_uid, experiment_uid, datasets_experiments_uid, updated_on, created_on)
					VALUES ('$line_uid', $exp_uid, $de_uid, NOW(), NOW())" ;
            $res = mysqli_query($mysqli, $sql) or exitFatal ($errFile, "Database Error: tht_base insert failed - ". mysqli_error($mysqli) . ".\n\n$sql");
            $sql = "SELECT tht_base_uid FROM tht_base WHERE experiment_uid = '$exp_uid' AND line_record_uid = '$line_uid'";
            $rtht=mysqli_query($sql) or exitFatal ($errFile, "Database Error: post tht_base insert - ". mysqli_error($mysqli). ".\n\n$sql");
            $rqtht=mysqli_fetch_assoc($rtht);
            $tht_uid=$rqtht['tht_base_uid'];
        }
					
    	/* get the genotyping_data_uid */
    	$sql ="SELECT genotyping_data_uid FROM genotyping_data WHERE marker_uid=$marker_uid AND tht_base_uid=$tht_uid ";
    	$rgen=mysqli_query($mysqli, $sql) or exitFatal ($errFile, "Database Error: genotype_data lookup - ". mysqli_error($mysqli). ".\n\n$sql");
    	$rqgen=mysqli_fetch_assoc($rgen);    
    	$gen_uid=$rqgen['genotyping_data_uid'];
				
    	if (empty($gen_uid)) {
    		$sql="INSERT INTO genotyping_data (tht_base_uid, marker_uid, updated_on, created_on)
					VALUES ($tht_uid, $marker_uid, NOW(), NOW())" ;
            $res = mysqli_query($mysqli, $sql) or exitFatal ($errFile, "Database Error: genotype_data insert - ". mysqli_error($mysqli) . ".\n\n$sql");
            $sql ="SELECT genotyping_data_uid FROM genotyping_data WHERE marker_uid = $marker_uid AND tht_base_uid=$tht_uid ";
            $rgen=mysqli_query($mysqli, $sql) or exitFatal ($errFile, "Database Error: post genotype_data lookup - ". mysqli_error($mysqli). ".\n\n$sql");
            $rqgen=mysqli_fetch_assoc($rgen);
            $gen_uid=$rqgen['genotyping_data_uid'];
	    }
		// echo "gen_uid".$gen_uid."\n";
		/* Read in the rest of the variables */
        //$gtscore = $data[$gtScoreIdx];
        //$gcscore = $data[$gcScoreIdx];
        //$theta = $data[$thetaIdx ];
	    //$r = $data[$rIdx];
	    //$x = $data[$xIdx];
	    //$y = $data[$yIdx];
	    //$xraw = $data[$xRawIdx ];
	    //$yraw = $data[$yRawIdx ];

        $allele1 = $data[$allele1Idx];
	    $allele2 = $data[$allele2Idx];
		
        // Force NaN entries to default
		/*
		if ($r == 'NaN') {
			$r=99999;
		}
		if ($theta == 'NaN') {
			$theta = 99999;
		}
        if ($gcscore == 'NaN') {
			$gcscore = 99999;
		}
		if ($gtscore == 'NaN') {
			$gtscore = 99999;
		}
		*/
        $result =mysqli_query($mysqli, "SELECT genotyping_data_uid FROM alleles WHERE genotyping_data_uid = $gen_uid");
		$rgen=mysqli_num_rows($result);
		if ($rgen < 1) {
			//$sql = "INSERT INTO alleles (genotyping_data_uid,allele_1,allele_2,
			//			theta, R,X,Y,X_raw,Y_raw,GC_score, GT_score, updated_on, created_on)
			//			VALUES ($gen_uid,'$allele1','$allele2',$theta,$r,$x,$y,$xraw,$yraw,$gcscore, $gtscore, NOW(), NOW()) ";
			$sql = "INSERT INTO alleles (genotyping_data_uid,allele_1,allele_2, updated_on, created_on)
						VALUES ($gen_uid,'$allele1','$allele2',NOW(), NOW()) ";
 
       } else {
		$sql = "UPDATE alleles
			SET allele_1='$allele1',allele_2='$allele2', updated_on=NOW() 
			WHERE genotyping_data_uid = $gen_uid";
		}
		$res = mysqli_query($mysqli, $sql) or exitFatal ($errFile, "Database Error: alleles processing - ". mysqli_error($mysqli) . ".\n\n$sql");
		if ($res != 1) { 
            $msg = "ERROR:  Allele not loaded! row = " . $rowNum ."\t" . $line;
            fwrite($errFile, $msg);
            $errLines++;
        }
	} // end marker flag loop
} // End of while data 
fclose($reader);
echo "Genotyping record creation completed.\n";

echo "Start allele frequency calculation processing...\n";

// Do allele frequency calculations
$uniqExpID = array_unique($lineExpHash);
    
foreach ($uniqExpID AS $key=>$expID)  {
    if (empty($expID)) continue; 

    // Step 1: get tht_base IDs for the experiment
    echo "Working on experiment id - " . $expID . "\n";
    $sql ="SELECT tht_base.tht_base_uid FROM tht_base WHERE tht_base.experiment_uid = $expID";
    $res = mysqli_query($mysqli, $sql) or exitFatal ($errFile, "Database Error: tht_base lookup with experiment uid - ". $expID .
        " ". mysqli_error($mysqli) . ".\n\n$sql");
	
    while ($row = mysqli_fetch_array($res)) {
        $tht_base_uid[] = $row['tht_base_uid'];
    }
    
//    echo "Size of experiment look up in tht_base - ".  sizeof($tht_base_uid) ."\n";
    if (sizeof($tht_base_uid) == 0) continue; 
    
    $tht_base_uids = implode(",",$tht_base_uid);
    echo "\t tht_base_uids list - " . $tht_base_uids  . "\n";    
    // Step 2: get distinct marker_uid's for these tht_base IDs
    $sql ="SELECT DISTINCT g.marker_uid FROM genotyping_data AS g WHERE g.tht_base_uid IN ($tht_base_uids)";
    $res = mysqli_query($mysqli, $sql) or exitFatal ($errFile, "Database Error: genotyping_data lookup with experiment uid - ". $expID .
    " ". mysqli_error($mysqli). ".\n\n$sql");
    while ($row = mysqli_fetch_array($res)) {
        $mk_uid[] = $row['marker_uid'];
    }
    $mk_uids = array_unique($mk_uid);
    
    //$tstcnt = 0;
    $res = mysqli_query($mysqli, "SHOW COLUMNS FROM allele_frequencies");
    while($row = mysqli_fetch_object($res)){
        if(ereg(('set|enum'), $row->Type)) {
            eval(preg_replace('set|enum', '$'.$row->Field.' = array', $row->Type).';');
        }
    }
         
    foreach ($mk_uids as $value) {
         
    	if (empty($value)) continue; 	
        //get marker name
        $sql ="SELECT markers.marker_name FROM markers
                   WHERE marker_uid = $value";
        $res = mysqli_query($mysqli, $sql) or exitFatal ($errFile, "Database Error: marker name retrieval - ". mysqli_error($mysqli) . ".\n\n$sql");
        $rdata = mysqli_fetch_assoc($res);
        $mname = $rdata['marker_name'];
        echo "-+- marker name ".$mname." for marker ".$value."\n";
                
        // get genotype IDs for a marker
        $sql ="SELECT g.genotyping_data_uid AS gid FROM genotyping_data AS g
                    WHERE g.tht_base_uid IN ($tht_base_uids) AND g.marker_uid = $value";
        $res = mysqli_query($mysqli, $sql) or exitFatal ($errFile, "Database Error: genotyping_data retrieval - ". mysqli_error($mysqli) . ".\n\n$sql");
        while ($row = mysqli_fetch_array($res)) {
            $geno_uid[] = $row['gid'];
        }
        echo "--- num genotype ids ".count($geno_uid)." for marker ".$value."\n";
        $geno_uids = implode(",",$geno_uid);
        //print_r($geno_uids);
        if (strlen($geno_uids) == 0 ) echo "Oops, no Genotype_data_uid\n";
        
        // get alleles and gentrain score
        $sql ="SELECT a.allele_1,a.allele_2, a.GT_score FROM alleles AS a
                    WHERE a.genotyping_data_uid IN ($geno_uids)";
        $res = mysqli_query($mysqli, $sql) or exitFatal ($errFile, "Database Error: genotyping_data retrieval - ". mysqli_error() . ".\n\n$sql");

        while ($row = mysqli_fetch_array($res)) {
            $a1[]=$row['allele_1'];
            $a2[]=$row['allele_2'];
            if ($row['GT_score'] == "" ) {
                $gt[] = NULL;
            } else {
                $gt[] =$row['GT_score'];
            }
        }
        //for ($i = 0; $i < count($a1); $i++) {
        //echo $i." alleles ".$a1[$i].$a2[$i].$gt[$i]."\n";}
                
        // Loop through markers to get a count
        $aacnt = 0;
        $abcnt = 0;
        $bbcnt = 0;
        $misscnt =0;
        for ($i = 0; $i < count($a1); $i++) {
            if (($a1[$i] == 'A') and ($a2[$i] == 'A')) {
                $aacnt++;
            } elseif (($a1[$i] == 'B') and ($a2[$i] == 'B')) {
                $bbcnt++; 
            } elseif ((($a1[$i] == 'A') and ($a2[$i] == 'B')) or (($a1[$i] == 'B') and ($a2[$i] == 'A'))) {
                $abcnt++;
            } elseif (($a1[$i] == '-') and ($a2[$i] == '-')) {
                $misscnt++;
            } else {
                exitFatal ($errFile, $i." marker ".$value." not matching anything.");
            }
        }  //end for
        $total = $aacnt + $abcnt + $bbcnt + $misscnt;
        $aafreq = round($aacnt / $total,3);
        $bbfreq = round($bbcnt / $total,3);
        $abfreq = round($abcnt / $total,3);
        $maf = round(100 * min((2 * $aacnt + $abcnt) /(2 * $total), ($abcnt + 2 * $bbcnt) / (2 * $total)),1);
        $gtscore =max($gt);
        if (($aacnt == $total) or ($abcnt == $total) or ($bbcnt == $total)) {
            $mono = $monomorphic[0];//is monomorphic
        } else {
            $mono = $monomorphic[1];
        }
                   
       //echo $mono." Miss: ".$misscnt." AA ".$aacnt." BB ".$bbcnt." AB ".$abcnt." MAF ".$maf." total ".$total."\n";
       //$tstcnt++;
        
        //if ($tstcnt > 1600) {
        //    exitFatal ($errFile, "Error: tstcnt > 1600");
        //}
        
        $result =mysqli_query($mysqli, "SELECT allele_frequency_uid FROM allele_frequencies where experiment_uid = $expID and marker_uid = $value");
		$rgen=mysqli_num_rows($result);
		if ($rgen < 1) {
			$sql = "INSERT INTO allele_frequencies (marker_uid, experiment_uid, missing, aa_cnt, aa_freq, ab_cnt, ab_freq,
                bb_cnt, bb_freq, total, monomorphic, maf, gentrain_score, description,  updated_on, created_on)
                VALUES ($value, $expID, $misscnt, $aacnt, $aafreq, $abcnt, $abfreq, $bbcnt, $bbfreq, $total, '$mono',
                $maf, $gtscore, '$mname', NOW(), NOW())";
        } else {
			$sql = "UPDATE allele_frequencies 
						SET missing = '$misscnt', aa_cnt = '$aacnt', aa_freq = $aafreq, ab_cnt = $abcnt, ab_freq = $abfreq, bb_cnt = $bbcnt,
						bb_freq = $bbfreq, total = $total, monomorphic = '$mono', maf= $maf,  gentrain_score = $gtscore, 
                        description = '$mname', updated_on = NOW() 
						WHERE experiment_uid = $expID and marker_uid = $value";
		}
        mysqli_query($mysqli, $sql) or exitFatal ($errFile, "Database Error: during update or insertion into  allele_frequencies table - ". mysqli_error($mysqli) . "\n\n$sql");
        //reset key variables
        unset($geno_uid);
        unset($a1);
        unset($a2);
        unset($gt);
    }
    unset ($mk_uid);
    unset ($mk_uids);
    unset ($tht_base_uid);
}
fclose($errFile);

echo "Allele frequency calculations completed.\n";


// Send out status email
if (filesize($errorFile)  > 0) {
    $body = "There was a problem during the offline importing process.\n".
        "Please have the curator review the error file at " . $urlPath.'curator_data/'.$tPath . "\n";
    echo "Genotype Data Import processing encountered some errors, check error file ". $errorFile , " for more information\n";
    
} else {
    $body = "The offline genotype data import completed successfully.\n".
			"Genotyping data import completed at - ". date("m/d/y : H:i:s", time()). "\n\n".
            "Additional information can be found at ".$urlPath.'curator_data/'.$tPath."genoProc.out\n";
    echo "Genotype Data Import Processing Successfully Completed\n";
}
send_email($emailAddr, $subject, $body);

echo "Genotype Data Import Done\n";
echo "Finish time - ". date("m/d/y : H:i:s", time()). "\n"; 

$sql = "SELECT input_file_log_uid from input_file_log 
	WHERE file_name = '$filename'";
$res = mysqli_query($mysqli, $sql) or die("Database Error: input_file lookup  - ". mysqli_error($mysqli) ."<br>".$sql);
$rdata = mysqli_fetch_assoc($res);
$input_uid = $rdata['input_file_log_uid'];
        
if (empty($input_uid)) {
	$sql = "INSERT INTO input_file_log (file_name,users_name, created_on)
		VALUES('$filename', '$username', NOW())";
} else {
	$sql = "UPDATE input_file_log SET users_name = '$username', created_on = NOW()
		WHERE input_file_log_uid = '$input_uid'"; 
}
mysqli_query($mysqli, $sql) or die("Database Error: Input file log entry creation failed - " . mysqli_error($mysqli) . "\n\n$sql");

exit(0);

//********************************************************
function exitFatal ($handle, $msg) {

    global $emailAddr;
    global $mailheader;
    global $tPath; 
	global $urlPath; 
    
    // Send to stdout
    echo $msg;
    // send to error log
    fwrite($handle, $msg);
    fclose ($handle);
    // Send email
    $subject = 'Fatal Import Error';
    $body = "There was a fatal problem during the offline importing process.\n". $msg. "\n\n" .
        "Additional information can be found at ".$urlPath.'curator_data/'.$tPath. "\n";      
    send_email($emailAddr, $subject, $body);
    exit(1);
}
