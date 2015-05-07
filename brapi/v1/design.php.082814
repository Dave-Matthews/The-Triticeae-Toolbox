<?php
include('../../includes/bootstrap.inc');
connect();
$mysqli = connecti();

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    //echo "cmd = $action<br>\n";
}
if (isset($_GET['uid'])) {
    $uid = $_REQUEST['uid'];
    //echo "uid = $uid<br>\n";
}
header("Content-Type: application/json");
if ($action == "list") {
    $sql = "select distinct(fieldbook.experiment_uid), trial_code from fieldbook, experiments where fieldbook.experiment_uid = experiments.experiment_uid";
    $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
    while ($row = mysqli_fetch_row($res)) {
        $uid = $row[0];
        $trial = $row[1];
        $temp["experimentID"][] = $row[0];
        $temp["experimentName"][] = $row[1];
    }
    $return = json_encode($temp);
    echo "$return";
} elseif ($uid != "") {
    $sql = "select line_record_uid, line_record_name from line_records";
    $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
    while ($row = mysqli_fetch_row($res)) {
                $line_uid= $row[0];
                $line_record_name = $row[1];
                $name_list[$line_uid] = $line_record_name;
    }
        $results["header"] = "plot_id,range,plot,column,name,replication,block";
        $sql = "select plot_uid, plot, block, row_id, column_id, replication, check_id, line_uid from fieldbook where experiment_uid = $uid order by row_id, plot";
        $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
        while ($row = mysqli_fetch_row($res)) {
                $plot_id = $row[0];
                $plot = $row[1];
                $block = $row[2];
                $row_id = $row[3];
                $column_id = $row[4];
                $replication = $row[5];
                $check_id = $row[6];
                $line_uid = $row[7];
                $line_record_name = $name_list[$line_uid];
                if (!preg_match("/\d+/",$row_id)) {
                    $error = 1;
                }
                if (!preg_match("/\d+/",$column_id)) {
                    $error = 1;
                }
                $results["plot"][] = "$plot_id,$row_id,$plot,$column_id,$line_record_name,$replication,$block";
        }
    $return = json_encode($results);
    echo "$return";
} else {
    echo "Error: missing experiment id<br>\n";
}
?>
