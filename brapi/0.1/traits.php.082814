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
    $sql = "select phenotype_uid, phenotypes_name, description from phenotypes";
    $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
    while ($row = mysqli_fetch_row($res)) {
        $uid = $row[0];
        $name = $row[1];
        $desc = $row[2];
        $temp["phenoID"][] = $row[0];
        $temp["Name"][] = $row[1];
        $temp["Description"][] = $row[2];
    }
    $return = json_encode($temp);
    echo "$return";
} elseif ($uid != "") {
    $pheno_list =  explode(",", $uid);
    //allowed values for Android Field Book are numeric, qualitative, percent, date, boolean, text, audio
    $sql = "select phenotype_uid, phenotypes_name, datatype, description, unit_name from phenotypes, units where phenotypes.unit_uid = units.unit_uid";
    $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
    while ($row = mysqli_fetch_row($res)) {
            $uid= $row[0];
            $trait[$uid] = $row[1];
            $fmt = $row[2];
            $detail[$uid] = $row[3];
            $units = $row[4];
            if ($units == "percent") {
                $fmt = "percent";
            } elseif ($fmt == "continuous") {
                $fmt = "numeric";
            } elseif ($fmt == "discrete") {
                $fmt = "numeric";
            }
            $format[$uid] = $fmt;
    }
    $pos = 1;
    $results["header"] = array("trait","format","defaultValue","minimum","maximum","details","categories","isVisible","realPosition");
    foreach ($pheno_list as $item) {
           $results["trait"][] = array("$trait[$item]","$format[$item]","","","","$detail[$item]","","TRUE","$pos");
           $pos++;
    }

    $return = json_encode($results);
    echo "$return";
} else {
    echo "Error: missing experiment id<br>\n";
}
?>
