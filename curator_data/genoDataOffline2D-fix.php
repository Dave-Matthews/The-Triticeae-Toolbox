<?php
//*********************************************
// Genotype data importer - also contains various   
// pieces of import code by Julie's team @ iowaStateU  

// 10/25/2011  JLee   Ignore "cut" portion of input file 

// 10/17/2011 JLee  Add username and resubmission entry to input file log table
// 10/17/2011 JLee  Create of input file log entry
// 4/11/2011 JLee   Add ability to handle zipped data files

// Written By: John Lee
//*********************************************
error_reporting(E_ALL ^ E_NOTICE);
$progPath = realpath(dirname(__FILE__).'/../').'/';

include($progPath. 'includes/bootstrap_curator.inc');
include($progPath . 'curator_data/lineuid.php');
require_once $progPath . 'includes/email.inc';

ini_set (auto_detect_line_endings,1);

$num_args = $_SERVER["argc"];
$fnames = $_SERVER["argv"];
$lineTransFile = $fnames[1];
$gDataFile = $fnames[2];
$emailAddr = $fnames[3];
$urlPath = $fnames[4];
$userName = $fnames[5];

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

$linkID = connect(); 

$target_Path = "/www/htdocs/cbirkett/t3/wheatplus/curator_data/uploads/freq-test";

$errorFile = $target_Path."importError.txt";
echo $errorFile."\n";
if (($errFile = fopen($errorFile, "w")) === FALSE) {
   echo "Unable to open the error log file.";
   exit(1);
}

$Name = "Genotype Data Importer"; 
$myEmail="claybirkett@gmail.com";
$mailheader = "From: ". $Name . " <" . $myEmail . ">\r\n"; //optional headerfields
$subject = "Genotype import results";

   $trialCodeStr = "NSGCwheat9K_4X";
   $trialCodeStr = "NSGCstriperust_2011_UCD";

    $res = mysql_query("SELECT experiment_uid FROM experiments WHERE trial_code = '$trialCodeStr'") 
        or exitFatal ($errFile, "Database Error: Experiment uid lookup - ".mysql_error());
    $exp_uid = implode(",",mysql_fetch_assoc($res));
                    
    $res = mysql_query("SELECT datasets_experiments_uid FROM datasets_experiments WHERE experiment_uid = '$exp_uid'")
        or exitFatal ($errFile, "Database Error: Dataset experiment uid lookup - ".mysql_error());
    $de_uid=implode(",",mysql_fetch_assoc($res));

    $curTrialCode = $trialCodeStr;
    $lineExpHash[$lineStr] = $exp_uid;
    $lineDsHash[$lineStr] = $de_uid;

echo "Start allele frequency calculation processing...\n";

// Do allele frequency calculations
$uniqExpID = array_unique($lineExpHash);

foreach ($uniqExpID AS $key=>$expID)  {

        if (empty($expID)) continue;

    // Step 1: get tht_base IDs for the experiment
    echo "Working on experiment id - " . $expID . "\n";
    $sql ="SELECT tht_base.tht_base_uid FROM tht_base WHERE tht_base.experiment_uid = $expID";
    $res = mysql_query($sql) or exitFatal ($errFile, "Database Error: tht_base lookup with experiment uid - ". $expID .
        " ". mysql_error() . ".\n\n$sql");

    while ($row = mysql_fetch_array($res)) {
        $tht_base_uid[] = $row['tht_base_uid'];
    }

    echo "Size of experiment look up in tht_base - ".  sizeof($tht_base_uid) ."\n";
    if (sizeof($tht_base_uid) == 0) continue;

    $tht_base_uids = implode(",",$tht_base_uid);
    // echo "\t tht_base_uids list - " . $tht_base_uids  . "\n";
    // Step 2: get distinct marker_uid's for these tht_base IDs
    $sql ="SELECT DISTINCT g.marker_uid FROM genotyping_data AS g WHERE g.tht_base_uid IN ($tht_base_uids)";
    $res = mysql_query($sql) or exitFatal ($errFile, "Database Error: genotyping_data lookup with experiment uid - ". $expID .
    " ". mysql_error(). ".\n\n$sql");
    while ($row = mysql_fetch_array($res)) {
        $mk_uid[] = $row['marker_uid'];
    }
    $mk_uids = array_unique($mk_uid);

    //$tstcnt = 0;
    $res = mysql_query("SHOW COLUMNS FROM allele_frequencies");
    while($row = mysql_fetch_object($res)){
        if(ereg(('set|enum'), $row->Type)) {
            eval(ereg_replace('set|enum', '$'.$row->Field.' = array', $row->Type).';');
        }
    }

    foreach ($mk_uids as $value) {

        if (empty($value)) continue;
        //get marker name
        $sql ="SELECT markers.marker_name FROM markers
                   WHERE marker_uid = $value";
        $res = mysql_query($sql) or exitFatal ($errFile, "Database Error: marker name retrieval - ". mysql_error() . ".\n\n$sql");
        $rdata = mysql_fetch_assoc($res);
        $mname = $rdata['marker_name'];
        echo "-+- marker name ".$mname." for marker ".$value."\n";

        // get genotype IDs for a marker
        $sql ="SELECT g.genotyping_data_uid AS gid FROM genotyping_data AS g
                    WHERE g.tht_base_uid IN ($tht_base_uids) AND g.marker_uid = $value";
        $res = mysql_query($sql) or exitFatal ($errFile, "Database Error: genotyping_data retrieval - ". mysql_error() . ".\n\n$sql");
        while ($row = mysql_fetch_array($res)) {
            $geno_uid[] = $row['gid'];
        }
        echo "--- num genotype ids ".count($geno_uid)." for marker ".$value."\n";
        $geno_uids = implode(",",$geno_uid);
        //print_r($geno_uids);
        if (strlen($geno_uids) == 0 ) echo "Oops, no Genotype_data_uid\n";

        // get alleles and gentrain score
        $sql ="SELECT a.allele_1,a.allele_2, a.GT_score FROM alleles AS a
                    WHERE a.genotyping_data_uid IN ($geno_uids)";
        $res = mysql_query($sql) or exitFatal ($errFile, "Database Error: genotyping_data retrieval - ". mysql_error() . ".\n\n$sql");

        while ($row = mysql_fetch_array($res)) {
            $a1[]=$row['allele_1'];
            $a2[]=$row['allele_2'];
            if ($row['GT_score'] == "" ) {
                $gt[] = NULL;
            } else {
                $gt[] =$row['GT_score'];
            }
        }
        /* for ($i = 0; $i < count($a1); $i++) {
        echo $i." alleles ".$a1[$i].$a2[$i].$gt[$i]."\n";}*/

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
                exitFatal ($errFile, $i." marker ".$value . " " . $a1[$i] . "not matching anything.");
            }
        }  //end for
        $total = $aacnt + $abcnt + $bbcnt + $misscnt;
        $aafreq = round($aacnt / $total,3);
        $bbfreq = round($bbcnt / $total,3);
        $abfreq = round($abcnt / $total,3);
        $maf = round(100 * min((2 * $aacnt + $abcnt) /$total, ($abcnt + 2 * $bbcnt) / $total),1);
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

        $result =mysql_query("SELECT allele_frequency_uid FROM allele_frequencies where experiment_uid = $expID and marker_uid = $value");
                $rgen=mysql_num_rows($result);
                if ($rgen < 1) {
                        $sql = "INSERT INTO allele_frequencies (marker_uid, experiment_uid, missing, aa_cnt, aa_freq, ab_cnt, ab_freq,
                bb_cnt, bb_freq, total, monomorphic, maf, gentrain_score, description,  updated_on, created_on)
                VALUES ($value, $expID, $misscnt, $aacnt, $aafreq, $abcnt, $abfreq, $bbcnt, $bbfreq, $total, '$mono',
                $maf, 0, '$mname', NOW(), NOW())";
        } else {
                        $sql = "UPDATE allele_frequencies
                                                SET missing = '$misscnt', aa_cnt = '$aacnt', aa_freq = $aafreq, ab_cnt = $abcnt, ab_freq = $abfreq, bb_cnt = $bbcnt,
                                                bb_freq = $bbfreq, total = $total, monomorphic = '$mono', maf= $maf,
                        description = '$mname', updated_on = NOW()
                                                WHERE experiment_uid = $expID and marker_uid = $value";
                }
        mysql_query($sql) or exitFatal ($errFile, "Database Error: during update or insertion into  allele_frequencies table - ". mysql_error() . "\n\n$sql");
        print "$sql";
	exit;
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
mail($emailAddr, $subject, $body, $mailheader);

echo "Genotype Data Import Done\n";
echo "Finish time - ". date("m/d/y : H:i:s", time()). "\n"; 

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
    mail($emailAddr, $subject, $body, $mailheader);
    exit(1);
}

?>
