<?php
include('../../includes/bootstrap.inc');
connect();
$mysqli = connecti();

$self = $_SERVER['PHP_SELF'];
$script = $_SERVER["SCRIPT_NAME"]."/";
$rest = str_replace($script, "", $self);
$rest = explode("/", $rest);
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    //echo "cmd = $action<br>\n";
}
if (is_numeric($rest[0])) {
    $uid = $rest[0];
} else {
    $action = "list";
}
if (isset($_GET['uid'])) {
    $uid = $_REQUEST['uid'];
    //echo "uid = $uid<br>\n";
}
header("Content-Type: application/json");
if ($action == "list") {
    $sql = "select phenotype_uid, phenotypes_name, unit_name, description
    from phenotypes, units
    WHERE phenotypes.unit_uid = units.unit_uid";
    $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
    while ($row = mysqli_fetch_row($res)) {
        $temp["traitId"] = $row[0];
        $temp["name"] = $row[1];
        $temp["unit"] = $row[2];
        $temp["method"] = $row[3];
        $results[] = $temp;
    }
    $return = json_encode($results);
    echo "$return";
} elseif ($uid != "") {
    $pos = 1;
    $pheno_list =  explode(",", $uid);
    //allowed values for Android Field Book are numeric, qualitative, percent, date, boolean, text, audio
    $sql = "select phenotype_uid, phenotypes_name, datatype, unit_name, description
        from phenotypes, units
        WHERE phenotypes.unit_uid = units.unit_uid
        AND phenotype_uid IN ($uid)";
    $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
    while ($row = mysqli_fetch_row($res)) {
        $temp["traitId"]= $row[0];
        $temp["name"] = $row[1];
        $fmt = $row[2];
        $units = $row[4];
        if ($units == "percent") {
            $fmt = "percent";
        } elseif ($fmt == "continuous") {
            $fmt = "numeric";
        } elseif ($fmt == "discrete") {
            $fmt = "numeric";
        }
        $temp["format"] = $fmt;
        $temp["unit"] = $row[3];
        $temp["method"] = $row[4];
        $temp["defaultValue"] = "";
        $temp["minimum"] = "";
        $temp["maximum"] = "";
        $temp["categories"] = "";
        $temp["isVisible"] = "";
        $temp["realPosition"] = $pos;
        $results[] = $temp;
        $pos++;
    }

    $return = json_encode($temp);
    echo "$return";
} else {
    echo "Error: missing experiment id<br>\n";
}
?>
