<?php
include('../../includes/bootstrap.inc');
connect();
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
        $temp["mapId"] = $row[1];
        $temp["name"] = $row[2];
        $temp["species"] = $row[3];
        $temp["type"] = $row[4];
        $temp["unit"] = $row[5];
        $temp["publishedDate"] = $row[6];
        $temp["count"] = $row[0];
        $temp["comments"] = $row[7];
        $results[] = $temp;
    }
    $return = json_encode($results);
    echo "$return";
} elseif ($uid != "") {
    $pos = 1;
    $sql = "select markers.marker_uid, markers.marker_name, start_position, chromosome, arm
        from markers_in_maps, markers
        where markers_in_maps.marker_uid = markers.marker_uid
        AND map_uid = $uid";
    $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
    while ($row = mysqli_fetch_row($res)) {
        $temp["markerID"]= $row[0];
        $temp["markerName"] = $row[1];
        $temp["location"] = $row[2];
        $temp["chromosome"] = $row[3];
        $entries[] = $temp;
    }
    $results["entries"] = $entries;

    $return = json_encode($results);
    echo "$return";
} else {
    echo "Error: missing experiment id<br>\n";
}
?>
