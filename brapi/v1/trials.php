<?php
require '../../includes/bootstrap.inc';
$mysqli = connecti();

$self = $_SERVER['PHP_SELF'];
$script = $_SERVER["SCRIPT_NAME"]."/";
$rest = str_replace($script, "", $self);
$rest = explode("/", $rest);
if (is_numeric($rest[0])) {
    $uid = $rest[0];
} else {
    $action = "list";
}
if (isset($rest[1]) && ($rest[1] == "table")) {
    $outFormat = "table";
} else {
    $outFormat = "json";
}
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    //echo "cmd = $action<br>\n";
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

function dieNice($msg)
{
    $linearray["metadata"]["pagination"] = null;
    $linearray["metadata"]["status"] = array("code" => 1, "message" => "SQL Error: $msg");
    $linearray["result"] = null;
    $return = json_encode($linearray);
    die("$return");
}

//header("Content-Type: application/json");
if ($action == "list") {
    if (isset($_GET['program'])) {
        $cap_uid = $_GET['program'];
        $sql_opt = "and CAPdata_programs_uid = $cap_uid";
    } else {
        $sql_opt = "";
    }
    $linearray['metadata']['pagination'] = $pageList;
    $linearray['metadata']['status'] = array();
    $linearray['metadata']['datafiles'] = array();

    //first query all data
    $sql = "select experiment_uid, experiment_set_name, trial_code, CAPdata_programs_uid, experiment_year
        from experiments, experiment_set
        where experiments.experiment_set_uid = experiment_set.experiment_set_uid";
    $sql = "select experiment_set_uid, experiment_set_name, description from experiment_set";
    $res = mysqli_query($mysqli, $sql) or dieNice(mysqli_error($mysqli));
    $num_rows = mysqli_num_rows($res);
    $tot_pag = ceil($num_rows / $pageSize);
    $pageList = array( "pageSize" => $pageSize, "currentPage" => 0, "totalCount" => $num_rows, "totalPages" => $tot_pag );
    $linearray['metadata']['pagination'] = $pageList;

    //now get just those selected
    if ($currentPage == 0) {
        $sql .= " limit $pageSize";
    } else {
        $offset = $currentPage * $pageSize;
        if ($offset < 0) {
            $offset = 0;
        }
        $sql .= " limit $offset, $pageSize";
    }
    $res = mysqli_query($mysqli, $sql) or dieNice(mysqli_error($mysqli));
    while ($row = mysqli_fetch_row($res)) {
        $uid = $row[0];
        $data["trialDbId"] = $row[0];
        $data["trialName"] = $row[1];
        $data['studies'] = array();
        $sql = "select experiments.experiment_uid, trial_code, location
            from experiments, phenotype_experiment_info
            where experiments.experiment_uid = phenotype_experiment_info.experiment_uid
            and experiment_set_uid = $uid";
        $res2 = mysqli_query($mysqli, $sql) or dieNice(mysqli_error($mysqli) . $sql);
        while ($row2 = mysqli_fetch_row($res2)) {
            $tmp['studyDbId'] = $row2[0];
            $tmp['studyName'] = $row2[1];
            $tmp['locationName'] = $row2[2];
            $data['studies'][] = $tmp;
        }
        $temp[] = $data;
    }
    $linearray['result']['data'] = $temp;
    $return = json_encode($linearray);
    header("Content-Type: application/json");
    echo "$return";
} elseif ($uid != "") {
    $sql = "select experiment_type_name
        from experiments, experiment_types
        where experiments.experiment_type_uid = experiment_types.experiment_type_uid
        and experiment_uid = $uid";
    $sql = "select experiment_set_uid, experiment_set_name, description from experiment_set where experiment_set_uid = $uid";
    $res = mysqli_query($mysqli, $sql) or dieNice(mysqli_error($mysqli));
    $res = mysqli_query($mysqli, $sql) or dieNice(mysqli_error($mysqli));
    if ($row = mysqli_fetch_row($res)) {
        $results["trialDbId"] = $uid;
        $results["trialName"] = $row[1];
        $results['studies'] = array();
    } else {
        $results = null;
        $return = json_encode($results);
        header("Content-Type: application/json");
        echo "$return";
        die();
    }
    $sql = "select experiments.experiment_uid, trial_code, location
            from experiments, phenotype_experiment_info
            where experiments.experiment_uid = phenotype_experiment_info.experiment_uid
            and experiment_set_uid = $uid";
    $res2 = mysqli_query($mysqli, $sql) or dieNice(mysqli_error($mysqli) . $sql);
    while ($row2 = mysqli_fetch_row($res2)) {
        $tmp['studyDbId'] = $row2[0];
        $tmp['studyName'] = $row2[1];
        $tmp['locationName'] = $row2[2];
        $results['studies'][] = $tmp;
    }
    $temp[] = $data;
    $return = json_encode($results);
    header("Content-Type: application/json");
    echo "$return";
} else {
    echo "Error: missing experiment id<br>\n";
}
