<?php
/**
 * brapi/0.1/genotype.php, DEM jun 2014
 * Deliver genotyping data for a line according to
 * http://docs.breeding.apiary.io/
 * 120414 - changed structure of output to match API CLB
 */

require 'config.php';
include $config['root_dir'].'includes/bootstrap.inc';
connect();

// URI is something like genotype/{id}/[count][?analysisMethod={platform}][..]
// Extract the pseudo-path part of the REST args.
$self = $_SERVER['PHP_SELF'];
$script = $_SERVER["SCRIPT_NAME"]."/";
$rest = str_replace($script, "", $self);
$rest = explode("/", $rest);
$profileid = $rest[0];
//$command = $rest[1];
$command = "";
$lineuid = "";
$expuid = "";
$analmeth = "";
if (isset($_GET['germplasm'])) {
    $command = "find";
    $lineuid = $_GET['germplasm'];
}
if (isset($_GET['extract'])) {
    $command = "find";
    $expuid = $_GET['extract'];
}
// Get the URI's querystring.
if ($_GET) {
    $getkeys = array_keys($_GET);
    $analmeth = $_GET['analysisMethod'];
}
// Is there a command?
if ($command) {
    if (($lineuid != "") && ($expuid != "")) {
        // "Get Marker Count By Germplasm Id"
        // URI is genotype/{id}/count[?analysisMethod={platform}]
        $linearray['markerProfileId'] = $lineuid;

        $linearray['germplasmId'] = $lineuid;
        // Get the number of non-missing allele data points for this line, by experiment.
        $sql = "select experiment_uid, count(experiment_uid) from allele_cache 
	    where line_record_uid = $lineuid 
	    and not alleles = '--' 
            group by experiment_uid;";
        $res = mysql_query($sql) or die(mysql_error());
        while ($row = mysql_fetch_row($res)) {
            $runId = $row[0];
            $resultCount = $row[1];
            $analysisMethod = mysql_grab(
                "select platform_name from platform p, genotype_experiment_info g
                where p.platform_uid = g.platform_uid
                and g.experiment_uid = $runId"
            );
          // Restrict to the requested analysis method if any.
          if (!$analmeth or $analmeth == $analysisMethod ) {
              $linearray['extractId'] = $expuid;
              $linearray['analysisMethod'] = $analysisMethod;
              $linearray['resultCount'] = $resultCount;
          }
      }
    } elseif ($lineuid != "") {
          $linearray['germplasmId'] = $lineuid;
          $sql = "select experiment_uid, count(experiment_uid) from allele_cache 
            where line_record_uid = $lineuid 
            and not alleles = '--' 
            group by experiment_uid;";
        $res = mysql_query($sql) or die(mysql_error());
    } elseif ($expuid != "") {
          $linearray['extractId'] = $expuid;
          $sql = "select experiment_uid, count(experiment_uid) from allele_cache 
            where experiment_uid = $lineuid 
            and not alleles = '--' 
            group by line_record_uid;";
        $res = mysql_query($sql) or die(mysql_error());
    }
  header("Content-Type: application/json");
  /* Requires PHP 5.4.0: */
  /* echo json_encode($response, JSON_PRETTY_PRINT); */
  echo json_encode($linearray);
} else {
  // If no command, then it's a marker profile id (Line Expreriment).
  // "Get Genotype By Id"
  // URI is something like genotype/{id}[?runId={runId}][&analysisMethod={method}][&pageSize={pageSize}&page={page}]
  if (preg_match("/(\d+)_(\d+)/", $profileid, $match)) {
      $lineuid = $match[1];
      $expid = $match[2];
  } else {
      echo "Error: invalid format of marker profile id $lineuid<br>\n";
  }
  $linearray['markerprofileId'] = $profileid;
  $linearray['germplasmId'] = $lineuid;
  $linearray['extractId'] = $expid;
  //$sql = "select distinct experiment_uid from allele_cache 
  //	    where line_record_uid = $lineuid";
  //$res = mysql_query($sql);
  //while ($row = mysql_fetch_row($res))
  //  $runIds[] = $row[0];
  //foreach ($runIds as $ri) {
    $linearray['analysisMethod'] = mysql_grab("select platform_name 
				    from platform p, genotype_experiment_info g
				    where p.platform_uid = g.platform_uid
				    and g.experiment_uid = $expid");
    $linearray['encoding']= "AA,BB,AB";
    $sql = "select marker_name, alleles from allele_cache
	      where line_record_uid = $lineuid
	      and experiment_uid = $expid
	      and not alleles = '--'
	      order by marker_name";
    $res = mysql_query($sql);
    while ($row = mysql_fetch_row($res)) {
      /*$data[] = array($row[0] => $row[1]);*/
      $data[$row[0]] = $row[1];
      $linearray['data'] = $data;
    }
  /*$response = array($linearray, $genotypes);*/
  header("Access-Control-Allow-Origin: *");
  header("Content-Type: application/json");
  /* Requires PHP 5.4.0: */
  /* echo json_encode($response, JSON_PRETTY_PRINT); */
  /* echo json_encode($response);*/
  echo json_encode($linearray);
  /* print_h($response); */
}



?>
