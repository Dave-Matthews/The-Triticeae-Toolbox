<?php
/*
 * Genotype data deleter, DEM Feb2014, from:
 * 2D Genotype data importer
 * @author   Clay Birkett <clb343@cornell.edu>
 * @link     http://triticeaetoolbox.org/wheat/curator_data/genoDataOffline2D.php
 *
 * Recalculate the cached allele tables after deleting results for
 * some lines in a single experiment.  Tables: allele_frequencies, allele_cache, 
 * allele_conflicts, allele_byline, allele_bymarker
 */
$progPath = realpath(dirname(__FILE__).'/../').'/';

require "$progPath" . "includes/bootstrap_curator.inc";
require_once "$progPath" . "includes/email.inc";

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

$mysqli = connecti();

$target_Path = substr($lineTransFile, 0, strrpos($lineTransFile, '/')+1);
$tPath = str_replace('./', '', $target_Path);

$errorFile = $target_Path."importError.txt";
echo "Error file - ".$errorFile."\n";
if (($errFile = fopen($errorFile, "w")) === false) {
    echo "Unable to open the error log file.";
    exit(1);
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
if ($emailAddr == "") {
    echo "No email address. \n";
    exit (1);
}  

                     
$rowNum = 0;
$line_name = "qwerty";
$errLines = 0;
$data = array();

//for imports that take a long time there may be a deadlock when the allele cache does its daily refresh
//this statement causes the locks to be released earlier
$sql = "SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED";
$res = mysqli_query($mysqli,$sql) or exitFatal($errFile, "Database Error: - ". mysqli_error($mysqli)."\n\n$sql");
    


////////

// Do allele frequency calculations
$uniqExpID = array_unique($lineExpHash);

foreach ($uniqExpID AS $key=>$expID)  {

        if (empty($expID)) continue;

    // Step 1: get tht_base IDs for the experiment
    echo "Working on experiment id - " . $expID . "\n";
    $sql ="SELECT tht_base.tht_base_uid FROM tht_base WHERE tht_base.experiment_uid = $expID";
    $res = mysqli_query($mysqli, $sql) or
        exitFatal($errFile, "Database Error: tht_base lookup with experiment uid - ". $expID . " ". mysqli_error($mysqli) . ".\n\n$sql");

    while ($row = mysqli_fetch_array($res)) {
        $tht_base_uid[] = $row['tht_base_uid'];
    }

//    echo "Size of experiment look up in tht_base - ".  sizeof($tht_base_uid) ."\n";
    if (sizeof($tht_base_uid) == 0) continue;

    $tht_base_uids = implode(",",$tht_base_uid);
    // echo "\t tht_base_uids list - " . $tht_base_uids  . "\n";
    // Step 2: get distinct marker_uid's for these tht_base IDs
    $sql ="SELECT DISTINCT g.marker_uid FROM genotyping_data AS g WHERE g.tht_base_uid IN ($tht_base_uids)";
    $res = mysqli_query($mysqli, $sql) or 
        exitFatal($errFile, "Database Error: genotyping_data lookup with experiment uid - ". $expID . " ". mysqli_error($mysqli). ".\n\n$sql");
    while ($row = mysqli_fetch_array($res)) {
        $mk_uid[] = $row['marker_uid'];
    }
    $mk_uids = array_unique($mk_uid);

    //$tstcnt = 0;
    $res = mysqli_query($mysqli, "SHOW COLUMNS FROM allele_frequencies");
    while ($row = mysqli_fetch_object($res)) {
        if(ereg(('set|enum'), $row->Type)) {
            eval(preg_replace('set|enum', '$'.$row->Field.' = array', $row->Type).';');
        }
    }

    foreach ($mk_uids as $value) {

        if (empty($value)) continue;
        //get marker name
        $sql ="SELECT markers.marker_name FROM markers
                   WHERE marker_uid = $value";
        $res = mysqli_query($mysqli, $sql) or exitFatal($errFile, "Database Error: marker name retrieval - ". mysqli_error($mysqli) . ".\n\n$sql");
        $rdata = mysqli_fetch_assoc($res);
        $mname = $rdata['marker_name'];
        echo "-+- marker name ".$mname." for marker ".$value."\n";

        // get genotype IDs for a marker
        $sql ="SELECT g.genotyping_data_uid AS gid FROM genotyping_data AS g
                    WHERE g.tht_base_uid IN ($tht_base_uids) AND g.marker_uid = $value";
        $res = mysqli_query($mysqli, $sql) or exitFatal($errFile, "Database Error: genotyping_data retrieval - ". mysqli_error($mysqli) . ".\n\n$sql");
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
        $res = mysqli_query($mysqli, $sql) or exitFatal($errFile, "Database Error: genotyping_data retrieval - ". mysqli_error($mysqli) . ".\n\n$sql");

        while ($row = mysqli_fetch_array($res)) {
            $a1[]=$row['allele_1'];
            $a2[]=$row['allele_2'];
            if ($row['GT_score'] == "" ) {
                $gt[] = null;
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
                exitFatal($errFile, $i." marker ".$value . " " . $a1[$i] . "not matching anything.");
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

        $result =mysqli_query($mysqli, "SELECT allele_frequency_uid FROM allele_frequencies where experiment_uid = $expID and marker_uid = $value");
                $rgen=mysqli_num_rows($result);
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
        mysqli_query($mysqli, $sql) or exitFatal($errFile, "Database Error: during update or insertion into  allele_frequencies table - ". mysqli_error($mysqli) . "\n\n$sql");
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

echo "Allele frequency calculations completed.\nNow updating the allele cache table.\n";
$body = "Allele frequency calculations completed.\nNow updating the allele cache table.\n";
mail($emailAddr, "Genotype import step 1", $body, $mailheader);

// Shortcut function for mysqli_query().
function mysqlq($command) {
  global $mysqli;
  mysqli_query($mysqli, $command);
  $errmsg = mysqli_error($mysqli);
  if (!empty($errmsg)) {
    $exitmsg = $errmsg . "\nCommand was: \n" . $command . "\n";
    exitFatal($errFile, $exitmsg);
  }
}

// Update table allele_cache.
mysqlq("DROP TABLE IF EXISTS ac_temp");
// This takes a while, and Quick Search freezes because it does a 'SHOW CREATE TABLE' for all tables.
//mysqlq("CREATE TABLE ac_temp (select * from allele_view)");
mysqlq("CREATE TABLE ac_temp (                                                    
  marker_uid int(10) unsigned,                                                    
  marker_name varchar(255),                                                       
  line_record_uid int(10) unsigned,                                               
  line_record_name varchar(255),                                                  
  experiment_uid int(10) unsigned,                                                
  allele_uid int(12) unsigned,                                                    
  alleles varchar(2)                                                              
) ");
mysqlq("insert into ac_temp (marker_uid, marker_name, line_record_uid, line_record_name, experiment_uid, allele_uid, alleles) select * from allele_view");

mysqlq("DROP TABLE IF EXISTS allele_cache");
mysqlq("RENAME TABLE ac_temp TO allele_cache");
mysqlq("ALTER TABLE allele_cache add index (experiment_uid), add index (line_record_uid), add index (marker_uid)");
$body = "Allele cache table updated.\nNow updating the allele_conflicts table.\n";
echo $body;
mail($emailAddr, "Genotype import step 2", $body, $mailheader);

// Update table allele_conflicts.
mysqlq("drop table if exists acxyz");
mysqlq("create TABLE acxyz (line_record_uid int(10) unsigned, line_record_name varchar(255), marker_uid int(10) unsigned, marker_name varchar(255), count int(10))");
//$sql = "select marker_uid, marker_name from markers order by marker_name";
$sql = "select distinct marker_uid from allele_cache order by marker_name";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
while ($row = mysqli_fetch_array($res)) 
  /* array_push($marker_uid_list, $row[0]); */
  $marker_uid_list[] = $row[0];

foreach ($marker_uid_list as $marker_uid) 
  mysqlq("insert into acxyz (line_record_uid, line_record_name, marker_uid, marker_name, count)
          select line_record_uid, line_record_name, marker_uid, marker_name, count(distinct alleles)
          from allele_cache where alleles != '--'
          and marker_uid = $marker_uid
          group by line_record_name
          having count(distinct alleles) > 1");
mysqlq("ALTER TABLE acxyz add index (line_record_uid), add index (marker_uid)");

mysqlq("drop table if exists allele_conflicts_temp");
mysqlq("create table allele_conflicts_temp (line_record_uid int(10) unsigned, marker_uid int(10) unsigned, alleles varchar(2), experiment_uid int(10) unsigned)");
foreach ($marker_uid_list as $marker_uid) 
  mysqlq("insert into allele_conflicts_temp (line_record_uid, marker_uid, alleles, experiment_uid)
          select a.line_record_uid, a.marker_uid, a.alleles, a.experiment_uid
          from allele_cache a, acxyz
          where a.marker_uid = $marker_uid
          and a.marker_uid = acxyz.marker_uid
          and a.line_record_uid = acxyz.line_record_uid
          and a.alleles != '--'
          order by a.line_record_name, a.experiment_uid");

mysqlq("drop table if exists allele_conflicts");
mysqlq("rename table allele_conflicts_temp to allele_conflicts");

$body = "Allele conflicts table updated.\nNow updating the allele_bylines and allele_bymarker tables.\n";
echo $body;
mail($emailAddr, "Genotype import step 3", $body, $mailheader);

$cmd = "/usr/bin/php " . $progPath . "cron/create-allele-byline.php";
exec($cmd);
$cmd = "/usr/bin/php " . $progPath . "cron/create-allele-bymarker.php";
exec($cmd);

// Send out final email.
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

// Declare completion.
echo "Genotype Data Import Done\n";
echo "Finish time - ". date("m/d/y : H:i:s", time()). "\n";
fclose($errFile);

// Append to or update the input_file_log.
$sql = "SELECT input_file_log_uid from input_file_log 
	WHERE file_name = '$filename'";
$res = mysqli_query($mysqli, $sql) or die("Database Error: input_file lookup  - ". mysqli_error($mysqli) ."<br>".$sql);
$rdata = mysqli_fetch_assoc($res);
$input_uid = $rdata['input_file_log_uid'];
        
if (empty($input_uid)) {
	$sql = "INSERT INTO input_file_log (file_name,users_name, created_on)
		VALUES('$filename', '$userName', NOW())";
} else {
	$sql = "UPDATE input_file_log SET users_name = '$userName', created_on = NOW()
		WHERE input_file_log_uid = '$input_uid'"; 
}
mysqli_query($mysqli, $sql) or die("Database Error: Input file log entry creation failed - " . mysqli_error($mysqli) . "\n\n$sql");

$filename = stristr($lineTransFile,basename($lineTransFile));
$sql = "SELECT input_file_log_uid from input_file_log 
        WHERE file_name = '$filename'";
$res = mysqli_query($mysqli, $sql) or die("Database Error: input_file lookup  - ". mysqli_error($mysqli) ."<br>".$sql);
$rdata = mysqli_fetch_assoc($res);
$input_uid = $rdata['input_file_log_uid'];

if (empty($input_uid)) {
        $sql = "INSERT INTO input_file_log (file_name,users_name, created_on)
                VALUES('$filename', '$userName', NOW())";
} else {
        $sql = "UPDATE input_file_log SET users_name = '$userName', created_on = NOW()
                WHERE input_file_log_uid = '$input_uid'";
}
mysqli_query($mysqli, $sql) or die("Database Error: Input file log entry creation failed - " . mysqli_error($mysqli) . "\n\n$sql");

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

?>
