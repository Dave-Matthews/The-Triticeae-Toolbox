<?php
include('../../includes/bootstrap.inc');
$mysqli = connecti();

$self = $_SERVER['PHP_SELF'];
$script = $_SERVER["SCRIPT_NAME"]."/";
$rest = str_replace($script, "", $self);
$rest = explode("/", $rest);
if (isset($_GET['action'])) {
    $uid = $_GET['action'];
    //echo "cmd = $uid<br>\n";
}
if (is_numeric($rest[0])) {
    $uid = $rest[0];
} else {
    $action = "list";
}
if (isset($_GET['uid'])) {
    $uid = $_REQUEST['uid'];
}
header("Content-Type: application/json");
if ($action == "list") {
    $sql = "select count(*), mapset.mapset_uid, mapset_name, species, map_type, map_unit, published_on, comments
    from mapset, markers_in_maps as mim, map
    WHERE mim.map_uid = map.map_uid
    AND map.mapset_uid = mapset.mapset_uid
    GROUP BY mapset.mapset_uid";
    $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
    while ($row = mysqli_fetch_row($res)) {
      $uid = $row[1];
      $temp["mapId"] = (integer) $row[1];
      $temp["name"] = $row[2];
      $temp["species"] = $row[3];
      $temp["type"] = $row[4];
      $temp["unit"] = $row[5];
      $temp["publishedDate"] = $row[6];
      $timestamp = strtotime($temp["publishedDate"]);
      // Handle missing values 0000-00-00.
      if ($timestamp == 0)
	unset($temp["publishedDate"]);
      else
	$temp['publishedDate'] = date("Y-m-d", $timestamp);
      $temp["markerCount"] = (integer) $row[0];
      $sql = "select count(distinct(chromosome)) from markers_in_maps, map
        where map.map_uid = markers_in_maps.map_uid
        and mapset_uid = $uid";
      $res2 = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
      if ($row2 = mysqli_fetch_row($res2)) {
	$temp["chromosomeCount"] = (integer) $row2[0];
      } else {
	$temp["chromosomeCount"] = "Error";
      }
      $temp["comments"] = $row[7];
      $results[] = $temp;
    }
    $return = json_encode($results);
    echo "$return";
} elseif ($uid != "") {
    $sql = "select mapset_name, species, map_unit from mapset where mapset_uid = $uid";
    $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
    if ($row = mysqli_fetch_row($res)) {
        $results["name"] = $row[0];
        $results["type"] = $row[1];
        $results["unit"] = $row[2];
    }
    $sql = "select markers.marker_uid, markers.marker_name, start_position, chromosome, arm
        from markers_in_maps, markers, map
        where markers_in_maps.marker_uid = markers.marker_uid
        AND map.map_uid = markers_in_maps.map_uid
        AND mapset_uid = $uid";
    $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
    while ($row = mysqli_fetch_row($res)) {
        $temp2["markerID"]= $row[0];
        $temp2["markerName"] = $row[1];
        $temp2["location"] = $row[2];
        $temp2["chromosome"] = $row[3];
        $entries[] = $temp2;
    }
    $results["entries"] = $entries;

    $return = json_encode($results);
    echo "$return";
} else {
    echo "Error: missing experiment id<br>\n";
}
?>
