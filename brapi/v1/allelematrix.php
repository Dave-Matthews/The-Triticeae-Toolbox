<?php
include('../../includes/bootstrap.inc');
$mysqli = connecti();

$self = $_SERVER['PHP_SELF'];
$script = $_SERVER["SCRIPT_NAME"]."/";

if (isset($_GET['uid'])) {
    $profileid = $_GET['uid'];
} elseif (isset($_POST['uid'])) {
    $profileid = $_POST['uid'];
} else {
    echo "missing uid parameter";
}

header("Content-Type: application/json");

$profile_ary = explode(",", $profileid);
foreach ($profile_ary as $item) {
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
$results["markerprofileID"] = $resultProfile;
$results["scores"] = $dataList;
echo json_encode($results);
