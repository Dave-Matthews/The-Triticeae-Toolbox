<?php
// brapi/0.1/germplasm.php, DEM jul 2014
// Deliver Line names according to http://docs.breeding.apiary.io/
// from ./genotype.php

// Cassavabase response:
/* % curl "http://cassava-test.sgn.cornell.edu/brapi/0.1/germplasm/find?q=95NA-00063" */
/* [{"queryName":"95NA-00063","germplasmId":29417,"uniqueName":"95NA-00063"}] */

require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
connect();

// URI is something like germplasm/find?q={name}
// Extract the pseudo-path part of the REST args, ie "find".
$self = $_SERVER['PHP_SELF'];
$script = $_SERVER["SCRIPT_NAME"]."/";
$rest = str_replace($script, "", $self);
$rest = explode("/", $rest);
$command = $rest[0];
// Extract the URI's querystring, ie "q={name}".
if ($_GET) {
  // There should be at most 1.
  $getkeys = array_keys($_GET);
  if (count($getkeys) > 1) {
    $badkey = $getkeys[1];
    echo "<b>$badkey = $_GET[$badkey]</b>: Option not implemented<p>";
    exit;
  }
}
// Is there a command?
if ($command) {
  if ($command != "find") {
    echo "Unknown germplasm command <b>'$command'</b>. <p>";
    exit;
  }
  // "Germplasm ID by Name".  URI is germplasm/find?q={name}
  $linename = $_GET['q'];
  $lineuid = mysql_grab("select line_record_uid from line_records where line_record_name = '$linename'");
  $lineuid = intval($lineuid);
  $syns = array();
  $response["queryName"] = $linename;
  $response["germplasmId"] = $lineuid;
  $response["uniqueName"] = $linename;
  /* $response["synonyms"] = $syns ; */
  $r = array($response);
  header("Access-Control-Allow-Origin: *");
  header("Content-Type: application/json");
  /* Requires PHP 5.4.0: */
  /* echo json_encode($response, JSON_PRETTY_PRINT); */
  /* echo json_encode($response); */
  echo json_encode($r);
}
else {
  // $rest[1] is undefined, no command.
  echo "<b>germplasm/$rest[0]</b>: No action implemented yet.<p>";
}

?>
