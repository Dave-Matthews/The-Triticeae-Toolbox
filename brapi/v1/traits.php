<?php
require '../../includes/bootstrap.inc';
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
if (isset($_GET['pageSize'])) {
    $pageSize = $_GET['pageSize'];
} else {
    $pageSize = 1000;
}
if (isset($_GET['page'])) {
    $currentPage = $_GET['page'];
} else {
    $currentPage = 0;
}

header("Content-Type: application/json");
if ($action == "list") {
    $count = 0;
    $metadata['metadata']['status'] = array();
    $metadata['metadata']['datafiles'] = array();
    $sql = "select phenotype_uid, phenotypes_name, TO_number, unit_name, description
    from phenotypes, units
    WHERE phenotypes.unit_uid = units.unit_uid";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
    while ($row = mysqli_fetch_row($res)) {
        $count++;
        $temp["traitDbId"] = $row[0];
        if (preg_match("/[A-Za-z]/", $row[2])) {
            $temp["traitId"] = $row[2];
        } else {
            $temp["traitId"] = $row[1];
        }
        $temp["name"] = $row[1];
        $temp["description"] = $row[4];
        $temp["observationVariables"] = array($row[3]);
        $linearray['result']['data'][] = $temp;
    }
    $metadata['metadata']['pagination']['pageSize'] = $pageSize;
    $metadata['metadata']['pagination']['currentPage'] = $currentPage;
    $metadata['metadata']['pagination']['totalCount'] = $count;
    $metadata['metadata']['pagination']['totalPages'] = ceil($count / $pageSize);
    $response = array_merge($metadata, $linearray);
    echo json_encode($response);
} elseif ($uid != "") {
    $pos = 1;
    //$pheno_list =  explode(",", $uid);
    //allowed values for Android Field Book are numeric, qualitative, percent, date, boolean, text, audio
    $sql = "select phenotype_uid, phenotypes_name, TO_number, datatype, unit_name, description
        from phenotypes, units
        WHERE phenotypes.unit_uid = units.unit_uid
        AND phenotype_uid = ?";
    if ($stmt = mysqli_prepare($mysqli, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $uid);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $temp["traitId"], $temp["name"], $observ, $fmt, $temp["unit"], $temp["description"]);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        if ($units == "percent") {
            $fmt = "percent";
        } elseif ($fmt == "continuous") {
            $fmt = "numeric";
        } elseif ($fmt == "discrete") {
            $fmt = "numeric";
        }
        $temp["observationVariables"] = array($observ);
        $temp["format"] = $fmt;
        $temp["defaultValue"] = null;
        $temp["minimum"] = null;
        $temp["maximum"] = null;
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
