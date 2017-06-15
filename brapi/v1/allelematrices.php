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
if (isset($_GET['studyType'])) {
    $studyType = $_GET['studyType'];
}

function dieNice($msg)
{
    $linearray["metadata"]["pagination"] = null;
    $linearray["metadata"]["status"] = array("code" => 1, "message" => "SQL Error: $msg");
    $linearray["metadata"]["datafiles"] = array();
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
    $options = " and experiment_type_name = \"genotype\"";
    $sql = "select experiment_uid, trial_code, CAPdata_programs_uid, experiments.updated_on 
        from experiments, experiment_types
        where experiments.experiment_type_uid = experiment_types.experiment_type_uid
        $options";
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
        $data["name"] = $row[1];
        $data["matrixDbId"] = $row[0];
        $data["description"] = null;
        $CAP_uid = $row[2];
        $data["lastUpdated"] = $row[3];
        $data["studyDbId"] = $row[0];
        $sql = "select data_program_name from CAPdata_programs where CAPdata_programs_uid = $CAP_uid";
        $res2 = mysqli_query($mysqli, $sql) or dieNice(mysqli_error($mysqli));
        if ($row2 = mysqli_fetch_row($res2)) {
            $data["description"] = $row2[0];
        }
        $temp[] = $data;
    }
    $linearray['result']['data'] = $temp;
    $return = json_encode($linearray);
    header("Content-Type: application/json");
    echo "$return";
} elseif ($uid != "") {
    $linearray['metadata']['pagination'] = null;
    $linearray['metadata']['status'] = array();
    $linearray['metadata']['datafiles'] = array();

    $pageList = array( "pageSize" => $pageSize, "currentPage" => 0, "totalCount" => 1, "totalPages" => 1 );
    $linearray['metadata']['pagination'] = $pageList;

    $sql = "select line_record_uid, line_record_name from line_records";
    $res = mysqli_query($mysqli, $sql) or dieNice(mysqli_error($mysqli));
    while ($row = mysqli_fetch_row($res)) {
                $line_uid= $row[0];
                $line_record_name = $row[1];
                $name_list[$line_uid] = $line_record_name;
    }
    $sql = "select experiment_type_name, experiment_set_uid
        from experiments, experiment_types
        where experiments.experiment_type_uid = experiment_types.experiment_type_uid
        and experiment_uid = $uid";
    $res = mysqli_query($mysqli, $sql) or dieNice(mysqli_error($mysqli));
    if ($row = mysqli_fetch_row($res)) {
        $type = $row[0];
        $set = $row[1];
    } else {
        $set = null;
    }
    $sql = "select trial_code, data_program_name, marker_type_uid, processing_date, data_program_name 
         from experiments, genotype_experiment_info, CAPdata_programs
         where experiments.experiment_uid = genotype_experiment_info.experiment_uid
         and experiments.CAPdata_programs_uid = CAPdata_programs.CAPdata_programs_uid
         and experiments.experiment_uid = $uid";
    $res = mysqli_query($mysqli, $sql) or dieNice(mysqli_error($mysqli));
    if ($row = mysqli_fetch_row($res)) {
        $data["name"] = $row[0];
        $data["matrixDbId"] = $uid;
        $data["description"] = $row[1];
        $data["studyDbId"] = $uid;
        $data["studyName"] = $row[0];
        $data["startDate"] = $row[3];
    } else {
        $results = null;
        $return = json_encode($results);
        header("Content-Type: application/json");
        echo "$return";
        die();
    }
    if ($type == "genotype") {
        $sql = "select platform_name
         from experiments, genotype_experiment_info, platform
         where experiments.experiment_uid = genotype_experiment_info.experiment_uid
         and experiments.experiment_uid = $uid";
        $res = mysqli_query($mysqli, $sql) or dieNice(mysqli_error($mysqli));
        if ($row = mysqli_fetch_row($res)) {
            $data["additionalInfo"] =  $row[0];
        }
    }
    if (isset($data["trialDbId"])) {
        $sql = "select experiment_set_name from experiment_set where experiment_set_uid = $uid";
        $res = mysqli_query($mysqli, $sql) or dieNice(mysqli_error($mysqli) . "<br>$sql");
        if ($row = mysqli_fetch_row($res)) {
            $data["trialName"] = $row[0];
        }
    }

    $linearray['result']['data'] = $data;
    if ($outFormat == "json") {
        $return = json_encode($linearray);
        header("Content-Type: application/json");
    } else {
        $return = "";
        foreach ($results["design"] as $entry) {
            $return .= implode(",", $entry) . "\n";
        }
        header("Content-Type: text/csv");
    }
    echo "$return";
} else {
    echo "Error: missing experiment id<br>\n";
}
