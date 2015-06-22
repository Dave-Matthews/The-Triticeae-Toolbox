<?php
include('../../includes/bootstrap.inc');
$mysqli = connecti();

$self = $_SERVER['PHP_SELF'];
$script = $_SERVER["SCRIPT_NAME"]."/";

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
} else {
    echo "missing uid parameter";
    continue;
}

header("Content-Type: application/json");

foreach ($_GET as $item) {
  if (preg_match("/(\d+)_(\d+)/", $item, $match)) {
      $lineuid = $match[1];
      $expid = $match[2];
  } else {
      echo "Error: invalid format of marker profile id $lineuid<br>\n";
      continue;
  }
  $sql = "select marker_name, alleles from allele_cache
              where line_record_uid = $lineuid
              and experiment_uid = $expid
              and not alleles = '--'
              order by marker_name";
  $res = mysqli_query($mysqli, $sql);
  while ($row = mysqli_fetch_row($res)) {
      $dataList[$row[0]][] = $row[1];
  }
  $resultProfile[] = $item;
}
$results["metadata"] = array(status => "", pageination => "");
$results["markerprofileIds"] = $resultProfile;
$results["scores"] = $dataList;
echo json_encode($results);
