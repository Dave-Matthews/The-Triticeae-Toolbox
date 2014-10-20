<?php
// brapi/0.1/germplasm.php, DEM jul 2014
// Deliver Line names according to http://docs.breeding.apiary.io/
// from ./genotype.php
// Todo: everything

require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
connect();

// URI is something like germplasm/find?q={name}
// Extract the pseudo-path part of the REST args.
$self = $_SERVER['PHP_SELF'];
$script = $_SERVER["SCRIPT_NAME"]."/";
$rest = str_replace($script, "", $self);
$rest = explode("/", $rest);
$command = $rest[0];
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
  if ($command != "find") {
    echo "Unknown germplasm command <b>'$rest[1]'</b>. <p>";
    exit;
  }
  // "Germplasm ID by Name"
  // URI is germplasm/find?q={name}
  $linename = $_GET['q'];
  $lineuid = mysql_grab("select line_record_uid from line_records where line_record_name = '$linename'");
  $syns = array();
  $response[] = array("queryName" => "$linename");
  $response[] = array("uniqueName" => "$linename");
  $response[] = array("synonyms" => $syns );
  $response[] = array("germplasmId" => $lineuid);
  header("Access-Control-Allow-Origin: *");
  header("Content-Type: application/json");
  /* Requires PHP 5.4.0: */
  /* echo json_encode($response, JSON_PRETTY_PRINT); */
  echo json_encode($response);

}
else {
  // $rest[1] is undefined, no command.
  echo "<b>germplasm/$rest[0]</b>: No action implemented yet.<p>";
  // "Get Genotype By Id"
  // URI is something like genotype/{id}[?runId={runId}][&analysisMethod={method}][&pageSize={pageSize}&page={page}]

}

?>
