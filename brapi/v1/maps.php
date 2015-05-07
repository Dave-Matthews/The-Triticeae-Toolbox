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
    $sql = "select mapset_uid, mapset_name, species, map_type, map_unit, published_on, comments
    from mapset";
    $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
    while ($row = mysqli_fetch_row($res)) {
        $temp["uid"] = $row[0];
        $temp["name"] = $row[1];
        $temp["species"] = $row[2];
        $temp["map_type"] = $row[3];
        $temp["unit"] = $row[4];
        $temp["published"] = $row[5];
        $temp["comments"] = $row[6];
        $results[] = $temp;
    }
    $return = json_encode($results);
    echo "$return";
} elseif ($uid != "") {
    $pos = 1;
    $sql = "select marker_uid, start_position, chromosome, arm
        from markers_in_maps
        WHERE map_uid = $uid";
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
