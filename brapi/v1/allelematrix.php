<?php
include('../../includes/bootstrap.inc');
$mysqli = connecti();

$self = $_SERVER['PHP_SELF'];
$script = $_SERVER["SCRIPT_NAME"]."/";
$results['metadata']['status'] = null;

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
} else {
    echo "missing uid parameter";
    continue;
}

header("Content-Type: application/json");

if (isset($_GET['markerprofileDbId'])) {
    $tmp = $_GET['markerprofileDbId'];
    $profile_list = explode(",", $tmp);
} else {
    $results['metadata']['status'][] = array("code" => "par error", "message" => "invalid parameter");
}
foreach ($profile_list as $item) {
  if (preg_match("/(\d+)_(\d+)/", $item, $match)) {
      $lineuid = $match[1];
      $expid = $match[2];
  } else {
      echo "Error: invalid format of marker profile id $lineuid<br>\n";
      continue;
  }
  $count = 0;
  $sql = "select marker_name, alleles from allele_cache
              where line_record_uid = $lineuid
              and experiment_uid = $expid
              and not alleles = '--'
              order by marker_name";
  $res = mysqli_query($mysqli, $sql);
  while ($row = mysqli_fetch_row($res)) {
      $count++;
      if (isset($dataList[$row[0]])) {
          $dataList[$row[0]] .= ",$row[1]";
      } else {
          $dataList[$row[0]] = $row[1];
      }
  }
  $resultProfile[] = $item;
}
foreach ($dataList as $key => $val) {
  $dataList2[$key] = explode(",", $val);
}
$pageList = array( "pageSize" => $pageSize, "currentPage" => 1, "totalCount" => $num_rows, "totalPages" => $tot_pag );
$results['metadata']['pagination'] = $pageList;
$results['result']['markerprofileDbIds'] = $resultProfile;
$results['result']['data'][] = $dataList2;
echo json_encode($results);
