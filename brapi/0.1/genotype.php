<?php
// brapi/0.1/genotype.php, DEM jun 2014
// Deliver genotyping data for a line according to 
// http://docs.breeding.apiary.io/

require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
connect();

// URI is genotype/{id}/count?analysisMethod={platform}
// Extract the pseudo-path part of the REST args.
$self = $_SERVER['PHP_SELF'];
$script = $_SERVER["SCRIPT_NAME"]."/";
$rest = str_replace($script, "", $self);
$rest = explode("/", $rest);
$lineuid = $rest[0];
$command = $rest[1];
if ($rest[1]) {
  if ($rest[1] != count)
    echo "Unknown genotype command '$rest[1]'. <br>";
  else {
    /* $alleles = mysql_grab("select alleles from allele_byline where line_record_uid = $lineuid"); */
    /* $alleles = explode(",", $alleles); */
    /* // Total markers that have genotyping data: */
    /* $allelect = count($alleles); */
    /* // Function noval() finds which allele calls are missing or "--". */
    /* $missing = array_filter($alleles, "noval"); */
    /* $missingct = count($missing); */
    /* $validct = $allelect - $missingct; */
    /* $resp['total_markers'] = $allelect; */
    /* $resp['valid_markers'] = $validct; */
    /* echo "allelect = $allelect<br>"; */
    /* echo "missing = ".count($missing); */

    // "Get Marker Count By Germplasm Id"
    $linearray['gid'] = $lineuid;
    // Get the number of non-missing allele data points for this line, by experiment.
    $sql = "select experiment_uid, count(experiment_uid) from allele_cache 
             where line_record_uid = $lineuid 
             and not alleles = '--' 
            group by experiment_uid;";
    $res = mysql_query($sql);
    while ($row = mysql_fetch_row($res)) {
      $runId = $row[0];
      $resultCount = $row[1];
      $analysisMethod = mysql_grab("select platform_name from platform p, genotype_experiment_info g
                                 where p.platform_uid = g.platform_uid
                                 and g.experiment_uid = $runId");
      $datarray['runId'] = $runId;
      $datarray['analysisMethod'] = $analysisMethod;
      $datarray['resultCount'] = $resultCount;
      $markerCountarray['markerCounts'][] = $datarray;
    }
    $response = array($linearray, $markerCountarray);
    /* Requires PHP 5.4.0: */
    /* echo json_encode($response, JSON_PRETTY_PRINT); */
    echo json_encode($response);
  }
}
else {
  // $rest[1] is undefined.
  echo "<b>genotype/$rest[0]</b>: No action implemented yet.";
}

/* function noval($x) { */
/*   if (empty($x) OR $x == "--") */
/*     return TRUE; */
/*   else  */
/*     return FALSE; */
/* } */

?>
