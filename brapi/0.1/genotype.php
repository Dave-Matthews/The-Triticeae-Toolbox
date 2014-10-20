<?php
// brapi/0.1/genotype.php, DEM jun 2014
// Deliver genotyping data for a line according to 
// http://docs.breeding.apiary.io/

require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
connect();

// URI is something like genotype/{id}/[count][?analysisMethod={platform}][..]
// Extract the pseudo-path part of the REST args.
$self = $_SERVER['PHP_SELF'];
$script = $_SERVER["SCRIPT_NAME"]."/";
$rest = str_replace($script, "", $self);
$rest = explode("/", $rest);
$lineuid = $rest[0];
$command = $rest[1];
// Get the URI's querystring.
if ($_GET) {
  $getkeys = array_keys($_GET);
  if (count($getkeys) > 1) {
    $badkey = $getkeys[1];
    echo "<b>$badkey = $_GET[$badkey]</b>: Option not implemented<p>";
    exit;
  }
  $analmeth = $_GET['analysisMethod'];
}
// Is there a command?
if ($command) {
  if ($command == "count") {
  // "Get Marker Count By Germplasm Id"
  // URI is genotype/{id}/count[?analysisMethod={platform}]
  $linearray['germplasmId'] = $lineuid;
  // Get the number of non-missing allele data points for this line, by experiment.
  $sql = "select experiment_uid, count(experiment_uid) from allele_cache 
	    where line_record_uid = $lineuid 
	    and not alleles = '--' 
            group by experiment_uid;";
  $res = mysql_query($sql);
  while ($row = mysql_fetch_row($res)) {
    $runId = $row[0];
    $resultCount = $row[1];
    $analysisMethod = mysql_grab("select platform_name 
                                  from platform p, genotype_experiment_info g
                                  where p.platform_uid = g.platform_uid
                                  and g.experiment_uid = $runId");
    // Restrict to the requested analysis method if any.
    if (!$analmeth or $analmeth == $analysisMethod ) {
      $datarray['runId'] = $runId;
      $datarray['analysisMethod'] = $analysisMethod;
      $datarray['resultCount'] = $resultCount;
      $markerCountarray['markerCounts'][] = $datarray;
    }
  }
  $response = array($linearray, $markerCountarray);
  header("Content-Type: application/json");
  /* Requires PHP 5.4.0: */
  /* echo json_encode($response, JSON_PRETTY_PRINT); */
  echo json_encode($response);
  }
  else 
    echo "Unrecognized command <b>'$command'</b>";
}
else {
  // If no command, then it's a Line uid.
  // "Get Genotype By Id"
  // URI is something like genotype/{id}[?runId={runId}][&analysisMethod={method}][&pageSize={pageSize}&page={page}]
  $linearray['germplasmId'] = $lineuid;
  $sql = "select distinct experiment_uid from allele_cache 
	    where line_record_uid = $lineuid";
  $res = mysql_query($sql);
  while ($row = mysql_fetch_row($res))
    $runIds[] = $row[0];
  foreach ($runIds as $ri) {
    $analysisMethod = mysql_grab("select platform_name 
				    from platform p, genotype_experiment_info g
				    where p.platform_uid = g.platform_uid
				    and g.experiment_uid = $ri");
    $genotypesarray['runId'] = $ri;
    $genotypesarray['analysisMethod']= $analysisMethod;
    $genotypesarray['encoding']= "AA,BB,AB";
    $sql = "select marker_name, alleles from allele_cache
	      where line_record_uid = $lineuid
	      and experiment_uid = $ri
	      and not alleles = '--'
	      order by marker_name";
    $res = mysql_query($sql);
    while ($row = mysql_fetch_row($res)) 
      $data[] = array($row[0] => $row[1]);
    $genotypesarray['data'] = $data;
    $genotypes['genotypes'][] = $genotypesarray;
  }
  $response = array($linearray, $genotypes);
  header("Access-Control-Allow-Origin: *");
  header("Content-Type: application/json");
  /* Requires PHP 5.4.0: */
  /* echo json_encode($response, JSON_PRETTY_PRINT); */
  echo json_encode($response);
  /* print_h($response); */
}



?>
