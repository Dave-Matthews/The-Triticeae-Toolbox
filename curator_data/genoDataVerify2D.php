<?php
/**
 * 2D Genotype data importer
 *
 * PHP version 5.3
 * Prototype version 1.5.0
 *
 * @author  Clay Birkett <clb343@cornell.edu>
 * @license http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link    http://triticeaetoolbox.org/wheat/curator_data/genoDataVerify.php
 */
$progPath = realpath(dirname(__FILE__).'/../').'/';

require "$progPath" . "includes/bootstrap_curator.inc";
require_once "$progPath" . "includes/email.inc";

ini_set("auto_detect_line_endings", true);
ini_set('memory_limit', '2G');

$num_args = $_SERVER["argc"];
$fnames = $_SERVER["argv"];
$lineTransFile = $fnames[1];
$gDataFile = $fnames[2];
$emailAddr = $fnames[3];
$urlPath = $fnames[4];
$userName = $fnames[5];
$filename = stristr($gDataFile, basename($gDataFile));

$error_flag = 0;
$lineExpHash = array ();
$lineDsHash = array ();
$curTrialCode = '';
$gName = '';

echo "Start time - ". date("m/d/y : H:i:s", time()) ."\n"; 
echo "Start time - " . microtime(true) ."\n";
echo "Translate File - ". $lineTransFile. "\n";
echo "Genotype Data File - ". $gDataFile. "\n";
echo "URL - " . $urlPath . "\n";
echo "Email - ". $emailAddr."\n";

/**
 * look for unambiguous base at location specified by offset
 * http://www.illumina.com/documents/products/technotes/technote_topbot.pdf
 * 
 * @param string $snp    marker sequence
 * @param number $offset position in squence
 * 
 * @return number (0=not found 1=found)
 */
function findUnambig ($snp, $offset)
{
    global $strand, $a_allele, $b_allele;
    $pattern = "/([A-Z])\/([A-Z])/";
    if (preg_match($pattern, $snp, $match)) {
        $snp_pos1 = $match[1];
        $snp_pos2 = $match[2];
    } else {
        echo "Error: bad SNP sequence $snp\n";
    }
    if ($offset > 0) {
        $pattern = "/([A-Z])[A-Z]{" . $offset . "}\[[A-Z]\/[A-Z]\][A-Z]{" . $offset . "}([A-Z])/";
    }
    if (preg_match($pattern, $snp, $match)) {
        $found = 1;
        if (($match[1] == "A") &&  (($match[2] == "C") || ($match[2] == "G"))) {
            $strand = "TOP";
            $a_allele = $match[1];
            $b_allele = $match[2];
        } elseif (($match[2] == "A") &&  (($match[1] == "C") || ($match[1] == "G"))) {
            $strand = "TOP";
            $a_allele = $match[2];
            $b_allele = $match[1];
        } elseif (($match[1] == "T") &&  (($match[2] == "C") || ($match[2] == "G"))) {
            $strand = "BOT";
            $a_allele = $match[1];
            $b_allele = $match[2];
        } elseif (($match[2] == "T") &&  (($match[1] == "C") || ($match[1] == "G"))) {
            $strand = "BOT";
            $a_allele = $match[2];
            $b_allele = $match[1];
        } else {
            $found = 0;
        }
    } else {
        echo "Error: not enough flanking sequence $snp offset=$offset\n";
    }
    if ($offset > 0) {
        if (($match[1] == "A") || ($match[1] == "T")) {
            $strand = "TOP";
            $a_allele = $snp_pos1;
            $b_allele = $snp_pos2;
        }
        if (($match[2] == "A") || ($match[2] == "T")) {
            $strand = "BOT";
            $a_allele = $snp_pos2;
            $b_allele = $snp_pos1;
        }
    }
    if ($found) {
        return 0;
    } else {
        return 1;
    }
}

/**
 * step through the offset until unambigous base found
 * 
 * @param string $seq       sequence from marker table
 * @param string $marker_ab snp as defined by the A_allele B_allele in marker table
 * 
 * @return NULL
 */
function findIllumina ($seq, $marker_ab)
{
    global $strand, $a_allele, $b_allele;
    $strand = "";
    $a_allele = "";
    $b_allele = "";
    $a_allele = substr($marker_ab, 0, 1);
    $b_allele = substr($marker_ab, 1, 1);
}

/**
 * convert ACTG to Illumina AB format
 * 
 * @param string $alleles ACTG base calls
 * 
 * @return string converted base calls
 */
function convert2Illumina ($alleles)
{
    global $a_allele, $b_allele;
    $results = "";
    if (($a_allele == "") || ($b_allele == "")) {
        echo "Error: A allele and B allele undetermined\n";
    } elseif ($alleles == $a_allele) {
        $results = 'AA';
    } elseif ($alleles == $b_allele) {
        $results = 'BB';
    } elseif ($alleles == 'N') {
        $results = '--';
    } elseif ($alleles == 'H') {
        $results = 'AB';
    } else {
        echo "Error: allele is not valid SNP $alleles ($a_allele/$b_allele)\n";
    }
    return $results;
}

/**
 * convert 0,1 to Illumina AB format
 * 
 * @param string $alleles 0,1 base calls
 * 
 * @return string converted base calls
 */
function convertDArT2Illumina ($alleles)
{
    global $a_allele, $b_allele;
    $results = "";
    if (($a_allele == "") || ($b_allele == "")) {
        echo "Error: A allele and B allele undetermined\n";
    } elseif ($alleles == $a_allele) {  //1 = Present
        $results = 'AA';
    } elseif ($alleles == $b_allele) {  //0 = Absent
        $results = 'BB';
    } elseif ($alleles == '-') {  //missing
        $results = '--';
    } else {
        echo "Error: allele is not valid SNP $a_allele, $b_allele, $alleles\n";
    }
    return $results;
}
 
connect(); 
$mysqli = connecti();

$target_Path = substr($lineTransFile, 0, strrpos($lineTransFile, '/')+1);
$tPath = str_replace('./', '', $target_Path);

$errorFile = $target_Path."importError.txt";
echo "Error file - ".$errorFile."\n";
if (($errFile = fopen($errorFile, "w")) === false) {
    echo "Unable to open the error log file.";
    exit(1);
}

//get marker seq
$sql = "SELECT marker_uid, A_allele, B_allele from markers";
$res = mysqli_query($mysqli, $sql) or die("Database Error: setting lookup - ". mysqli_error($mysqli)."\n\n$sql");
while ($row = mysqli_fetch_array($res)) {
    $marker_uid = $row['marker_uid'];
    $marker_snp[$marker_uid] = $row['A_allele'] . $row['B_allele'];
}

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
    exitFatal($errFile,  "No Line Translation File Uploaded.");
}
  
if ($gDataFile == "") {
    exitFatal($errFile, "No Genotype Data Uploaded.");
}  

if ($emailAddr == "") {
    echo "No email address. \n";
    exit (1);
}  

// Check for zip file
if (strpos($gDataFile, ".zip") == true) {
    echo "Unzipping the genotype data file...\n";
    $zip = new ZipArchive;
    $zip->open($gDataFile) || exitFatal($errFile, "Unable to open zip file, please check zip format.");
    $gName = $zip->getNameIndex(0);
    $zip->extractTo($target_Path) || exitFatal($errFile, "Failed to extract file from the zip file.");
    $zip->close()  || exitFatal($errFile, "Failed to close zip file.");
    $gDataFile = $target_Path . $gName;
    echo "Genotype data unzipping done.\n";
}

/* Read the file */
if (($reader = fopen($lineTransFile, "r")) == false) {
    exitFatal($errFile, "Unable to access translate file.");
}
            
 // Check first line for header information
if (($inputrow = fgets($reader)) == false) {
    exitFatal($errFile, "Unable to locate header names on first line of file.");
}     

echo "\nProcessing line translation file...\n";

$header = str_getcsv($inputrow, "\t");
 // Set up header column; all columns are required
$lineNameIdx = implode(find("Line Name", $header), "");
$trialCodeIdx = implode(find("Trial Code", $header), "");
echo "Using Line Name column = $lineNameIdx, Trial Code column = $trialCodeIdx\n";
            
if (($lineNameIdx == "")||($trialCodeIdx == "")) {
    exitFatal($errFile, "ERROR: Missing one of the required columns in Line Translation file. Please correct it and try upload again.");
}
  
// Store individual records
$num = 0;
$linenumber = 0;
while (($line = fgets($reader)) !== false) { 
    $linenumber++;
    $origline = $line;
    chop($line, "\r");
    if ((stripos($line, '- cut -') > 0 )) break;

    if (preg_match('/ /', $line)) {
        echo "removing illegal blank character from $line";
        $line = preg_replace('/ /', '', $line);
    }
    if (strlen($line) < 2) continue;
    if (empty($line)) continue;
                
    $data = str_getcsv($line, "\t");
                        
    //Check for junk line
    if (count($data) != 2) {
        //exitFatal ($errFile, "ERROR: Invalid entry in Line Translation file - '$line' ");
        $parsed = print_r($data, true);
        exitFatal($errFile, "ERROR: Invalid entry in line number $linenumber of Line Translation file.\n Text of line: '$origline'\nContents parsed as: $parsed");
    }
    $trialCodeStr = $data[$trialCodeIdx];
    $lineStr = $data[$lineNameIdx];
                
    //echo  $lineStr . " - ". $trialCodeStr. "<br>"; 
    // Trial Code processing
    if (($curTrialCode != $trialCodeStr) && ($trialCodeStr != '')) {
        $sql = "SELECT experiment_uid FROM experiments WHERE trial_code = '$trialCodeStr'";
        $res = mysqli_query($mysqli, $sql)
            or exitFatal($errFile, "Database Error: Experiment uid lookup - ".mysqli_error($mysqli));
        if ($row = mysqli_fetch_assoc($res)) {
            $exp_uid = implode(",", $row);
        } else {
            exitFatal($errFile, "not found - $sql");
        }
        
        $sql = "SELECT datasets_experiments_uid FROM datasets_experiments WHERE experiment_uid = '$exp_uid'";            
        $res = mysqli_query($mysqli,$sql)
            or exitFatal($errFile, "Database Error: Dataset experiment uid lookup - ".mysqli_error($mysqli));
        if ($row = mysqli_fetch_assoc($res)) {
            $de_uid=implode(",", $row);
        } else {
            exitFatal($errFile, "not found - $sql");
        }

        $curTrialCode = $trialCodeStr;
        $num++;
    }
    $lineExpHash[$lineStr] = $exp_uid;
    $lineDsHash[$lineStr] = $de_uid;

    $line_uid = get_lineuid($lineStr);
    if ($line_uid == false) {
        echo "In Line Translation file, germplasm line '$lineStr' can not be found in the database.\n";
    } else {
        $line_uid = implode(",",$line_uid);
        $sql = "SELECT tht_base_uid FROM tht_base WHERE experiment_uid= '$exp_uid' AND line_record_uid='$line_uid' ";
        $res = mysqli_query($mysqli,$sql) or exitFatal($errFile, "Database Error: tht_base lookup - ". mysqli_error($mysqli) . ".\n\n$sql");
        if ($row = mysqli_fetch_assoc($res)) {
            $thtuid = $row['tht_base_uid'];
            $thtuid_lookup[$lineStr] = $thtuid;
        } else {
            $thtuid = null;
        }
        echo "Line $lineStr, id $line_uid. Experiment $trialCodeStr, id $exp_uid.\n";
    }
    if (feof($reader)) break;
}    
fclose($reader);   
echo "Line translation file processing done. Number of experiments: $num\n";

//send email so user can check on status of import
$body = "The offline genotype data import has started.\n".
  "Additional information can be found at ".$urlPath.'curator_data/'.$tPath."genoProc.out\n";
mail($emailAddr, "Genotype  verification started", $body, $mailheader);

//echo "Start genotyping record creation process...\n";
echo "\nProcessing genotype data file...\n";
//Process Genotype data
/* start reading the input */
//echo "genotype file - " . $gDataFile . "<br>";

/* Read the file */
if (($reader = fopen($gDataFile, "r")) == false) {
  exitFatal($errFile, "Unable to access genotype data file.");
}
        
//Advance to data header area
while (!feof($reader))  {
  $inputrow = fgets($reader);
  if (preg_match("/^SNP\t/", $inputrow)) {
    echo "Header line found.\n";
    break;
  } else {
    exitFatal($errFile, "Could not find the header information in file \n$gDataFile\n, line 1. The first line must begin with 'SNP'.\n");    
  }
}
        
if (feof($reader)) {
  exitFatal($errFile, "Unable to locate genotype header line.");
}

//Get column location  
$header = str_getcsv($inputrow,"\t");
$num = count($header);
for ($x = 0; $x < $num; $x++) {
  $line_name = $header[$x];
  switch ($line_name) {
  case 'SNP':
    $nameIdx = $x;
    $dataIdx = $x + 1;
    break;
  default:
    $line_uid = get_lineuid($line_name);
    if ($line_uid == false) {
      $colnum = $x + 1; // Human-oriented column numbering.
      echo "column $colnum:\tLine name '$header[$x]' is not in the database.\n";
    } else {
      $line_uid = implode(",",$line_uid);
      $lineuid_lookup[$line_name] = $line_uid;
    }
    break;
  }
}
                     
$rowNum = 0;
$line_name = "qwerty";
$errLines = 0;
$data = array();

$gen_uid = 0;
$allele1 = "";
$allele2 = "";

//for imports that take a long time there may be a deadlock when the allele cache does its daily refresh
//this statement causes the locks to be released earlier
$sql = "SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED";
$res = mysqli_query($mysqli,$sql) or exitFatal($errFile, "Database Error: - ". mysqli_error($mysqli)."\n\n$sql");
    
while ($inputrow= fgets($reader))  {
  // If we have too many errors stop processing - something is wrong
  if (strlen($inputrow) < 2) continue;
  if (empty($inputrow)) continue;
  if ($inputrow===false) break;
  $data = str_getcsv($inputrow,"\t");
  $marker = $data[$nameIdx];
  $num = count($data);		// number of fields
  $linecount = $num - 1;   // number of germplasm lines, i.e. data-containing columns.
  /* echo "working on marker $marker with $num of lines\n"; */
  /* echo "Reading alleles for marker $marker, $linecount germplasm lines.\n";
    
  /* check if marker is EST synonym, if not found, then check name */
  $sql ="SELECT ms.marker_uid FROM  marker_synonyms AS ms WHERE ms.value='$marker'";
  $res = mysqli_query($mysqli,$sql) or exitFatal($errFile, "Database Error: Marker synonym lookup - ". mysqli_error($mysqli)."\n\n$sql");
  // fwrite($errFile,$sql);
  $rdata = mysqli_fetch_assoc($res);
  $marker_uid=$rdata['marker_uid'];
  if (empty($marker_uid)) {
    $sql = "SELECT m.marker_uid FROM  markers AS m WHERE m.marker_name ='$marker'";
    $res = mysqli_query($mysqli,$sql) or exitFatal($errFile, "Database Error: Marker lookup - ". mysqli_error($mysqli)."\n\n$sql");
    // fwrite($errFile,$sql);
    if (mysqli_num_rows($res) < 1) {
      $markerflag = 1;
      $msg = "ERROR: marker '$marker' not found.\n";
      fwrite($errFile, $msg);
      $errLines++;
      continue;
    } else {
      $rdata = mysqli_fetch_assoc($res);
      $marker_uid=$rdata['marker_uid'];
    }
  }
  echo "Reading alleles for marker $marker, id $marker_uid, line $linecount ";

  $sql = "SELECT genotyping_data_uid from genotyping_data where marker_uid=$marker_uid";
  $res = mysqli_query($mysqli,$sql);
  if (null === ($rqgen=mysqli_fetch_assoc($res))) {
      $found_genotype_data = false;
      echo "genotype data not found in db\n";
  } else {
      $found_genotype_data = true;
      echo "genotype data found in db\n";
  }
    
  if (isset($marker_snp[$marker_uid])) {
    $marker_ab = $marker_snp[$marker_uid];
    $a_allele = substr($marker_ab,0,1);
    $b_allele = substr($marker_ab,1,1);
  } else {
    $msg = "ERROR: marker SNP not found for marker_uid = $marker_uid";
    fwrite($errFile, $msg);
    continue;
  }
  if (is_null($marker_ab) || (empty($marker_ab))) {
    $msg = "ERROR: allele A/B information not found for marker $marker.\n";
    exitFatal($errFile, $msg);
  }

  $rowNum++;		// Which row number of the file, 1 being the first marker.
  $markerflag = 0;        //flag for checking marker existence
  $data_pt = 0;
    for ($data_pt = $dataIdx; $data_pt < $num; $data_pt++) {
      $line_name = $header[$data_pt];

      if ($markerflag == 0) {
	  /* get line record ID */ 
	  //echo $line_name,"\n";
            $msg = "line name = " . $line_name. "\n";
            if (isset($lineuid_lookup[$line_name])) {
              $line_uid = $lineuid_lookup[$line_name];
            } else {
              /* $msg = "missing from line records $line_name. rowNum = $rowNum, marker = '$marker'. No such germplasm line '$line_name' in the database.\n"; */
              $msg = "Invalid line name '$line_name' for field $data_pt of row $rowNum, marker '$marker'.\n";
              fwrite($errFile, $msg);
            }
            if (isset($lineExpHash[$line_name])) {
              $exp_uid = $lineExpHash[$line_name];
	    } else {
              /* $msg = "missing from experiments $line_name $line_uid" . "\n"; */
	      /* fwrite($errFile, $msg); */
            }
 	    if (isset($lineDsHash[$line_name])) {
              $de_uid = $lineDsHash[$line_name];
            } else {
	      /* $msg = "missing from dataset experiments $line_name $line_uid" . "\n"; */
	      /* fwrite($errFile, $msg); */
            }

            /* get thtbase_uid. If null, then we have to create this ID */
            if (isset($thtuid_lookup[$line_name])) {				
              $tht_uid = $thtuid_lookup[$line_name];
            }

            //if this is a new marker then we don't need to query for uid before inserting
            $gen_uid = null;
            if ($found_genotype_data) {
                $sql ="SELECT genotyping_data_uid FROM genotyping_data WHERE marker_uid=$marker_uid AND tht_base_uid=$tht_uid ";
            }

	/* Read in the rest of the variables */
        $alleles = $data[$data_pt];
        $allele1 = substr($data[$data_pt],0,1);
	    $allele2 = substr($data[$data_pt],1,1);
        if (($alleles == 'A') || ($alleles == 'C') || ($alleles == 'T') || ($alleles == 'G') || ($alleles == 'N') || ($alleles == 'H')) {
          $results = convert2Illumina($alleles);
          if ($results == "") {
            $msg = "Error: could not convert ACTG to Illumina AB format $alleles $a_allele $b_allele\n";
            fwrite($errFile, $msg);
            $alleles = $marker_ab;
          } else {
            $alleles = $results;
          }
          $allele1 = substr($alleles,0,1);
          $allele2 = substr($alleles,1,1);
        } elseif (($alleles == '0') || ($alleles == '1') || ($alleles == '-')) {
          $results = convertDArt2Illumina($alleles);
          if ($results == "") {
            $msg = "Error: could not convert DArT to Illumina AB format\n";
            fwrite($errFile, $msg);
            $alleles = $marker_ab;
          } else {
            $alleles = $results;
          }
          $allele1 = substr($alleles,0,1);
          $allele2 = substr($alleles,1,1);
        }

	if (($alleles == 'AA') || ($alleles == 'BB') || ($alleles == '--') || ($alleles == 'AB') || ($alleles == 'BA')) {
        } elseif ($alleles == '') {
 	} else {
 	    	$msg = "bad data at $line_name $marker " . $data[$data_pt] . " $alleles\n";
                fwrite($errFile, $msg);
                $errLines++;
 	}
      }
    }
} // End of while data 
fclose($reader);

// Send out final email.
if (filesize($errorFile)  > 0) {
    $body = "There was a problem during the offline verification process.\n".
        "Please have the curator review the error file at " . $urlPath.'curator_data/'.$tPath . "\n";
    echo "Genotype Data Import processing encountered some errors, check error file ". $errorFile , " for more information\n";
    
} else {
    $body = "The offline genotype data verification completed successfully.\n".
			"Genotyping data import completed at - ". date("m/d/y : H:i:s", time()). "\n\n".
            "Additional information can be found at ".$urlPath.'curator_data/'.$tPath."genoProc.out\n";
    echo "Genotype Verification Processing Successfully Completed\n";
}
mail($emailAddr, $subject, $body, $mailheader);

fclose($errFile);

exit(0);

/**
 * Fatal error - send message then exit
 * 
 * @param file   $handle error file
 * @param string $msg    contains error message
 * 
 * @return NULL
 */
function exitFatal ($handle, $msg)
{
    global $emailAddr;
    global $mailheader;
    global $tPath; 
	global $urlPath; 
    
    // Send to stdout
    echo $msg;
    // send to error log
    fwrite($handle, $msg);
    fclose($handle);
    // Send email
    $subject = 'Fatal Import Error';
    $body = "There was a fatal problem during the offline importing process.\n". $msg. "\n\n" .
        "Additional information can be found at ".$urlPath.'curator_data/'.$tPath. "\n";      
    mail($emailAddr, $subject, $body, $mailheader);
    exit(1);
}
