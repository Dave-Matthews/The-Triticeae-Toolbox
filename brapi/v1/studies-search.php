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
    $currentPage = 1;
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
    if (isset($studyType)) {
        $options = " and experiment_type_name = \"$studyType\"";
    } else {
        $options = "";
    }
    $sql = "select experiment_uid, experiment_set_uid, experiment_type_name, trial_code, CAPdata_programs_uid, experiment_year
        from experiments, experiment_types
        where experiments.experiment_type_uid = experiment_types.experiment_type_uid
        $options";
    $res = mysqli_query($mysqli, $sql) or dieNice(mysqli_error($mysqli));
    $num_rows = mysqli_num_rows($res);
    $tot_pag = ceil($num_rows / $pageSize);
    $pageList = array( "pageSize" => $pageSize, "currentPage" => 1, "totalCount" => $num_rows, "totalPages" => $tot_pag );
    $linearray['metadata']['pagination'] = $pageList;

    //now get just those selected
    if ($currentPage == 1) {
        $sql .= " limit $pageSize";
    } else {
        $offset = ($currentPage - 1) * $pageSize;
        if ($offset < 1) {
            $offset = 1;
        }
        $sql .= " limit $offset, $pageSize";
    }
    $res = mysqli_query($mysqli, $sql) or dieNice(mysqli_error($mysqli));
    while ($row = mysqli_fetch_row($res)) {
        $uid = $row[0];
        $trial = $row[1];
        $data["studyDbId"] = $row[0];
        $data["trialDbID"] = $row[1];
        $data["studyType"] = $row[2];
        $data["studyName"] = $row[3];
        $data["trialName"] = null;
        $CAP_uid = $row[4];
        $data["years"] = $row[5];
        $data["programDbId"] = $row[4];
        if (isset($data["$trialDBID"])) {
            $sql = "select experiment_set_name from experiment_set where experiment_set_uid = $row[1]";
            $res2 = mysqli_query($mysqli, $sql) or dieNice(mysqli_error($mysqli) . "<br>$sql");
            if ($row2 = mysqli_fetch_row($res2)) {
                $data["trialName"] = $row2[0];
            }
        }
        $sql = "select location, planting_date, harvest_date from phenotype_experiment_info where experiment_uid = $row[0]";
        $res2 = mysqli_query($mysqli, $sql) or dieNice(mysqli_error($mysqli));
        if ($row2 = mysqli_fetch_row($res2)) {
            $data["locationDbId"] = $row2[0];
            $data["optionalInfo"]["startDate"] = $row2[1];
            $data["optionalInfo"]["endDate"] = $row2[2];
        } else {
            $data["locationDbId"] = null;
            $data["optionalInfo"] = null;
        }
        $sql = "select data_program_name from CAPdata_programs where CAPdata_programs_uid = $CAP_uid";
        $res2 = mysqli_query($mysqli, $sql) or dieNice(mysqli_error($mysqli));
        if ($row2 = mysqli_fetch_row($res2)) {
            $program = $row2[0];
        }
        $temp[] = $data;
    }
    $linearray['result']['data'] = $temp;
    $return = json_encode($linearray);
    header("Content-Type: application/json");
    echo "$return";
} elseif ($uid != "") {
    $sql = "select line_record_uid, line_record_name from line_records";
    $res = mysqli_query($mysqli, $sql) or dieNice(mysqli_error($mysqli));
    while ($row = mysqli_fetch_row($res)) {
                $line_uid= $row[0];
                $line_record_name = $row[1];
                $name_list[$line_uid] = $line_record_name;
    }
    $sql = "select experiment_type_name
        from experiments, experiment_types
        where experiments.experiment_type_uid = experiment_types.experiment_type_uid
        and experiment_uid = $uid";
    $res = mysqli_query($mysqli, $sql) or dieNice(mysqli_error($mysqli));
    if ($row = mysqli_fetch_row($res)) {
        $type = $row[0];
    }
    if ($type == "genotype") {
        $sql = "select trial_code, marker_type_uid, platform_uid
            from experiments, genotype_experiment_info
            where experiments.experiment_uid = genotype_experiment_info.experiment_uid";
    } else {
        $sql = "select trial_code, planting_date, collaborator, location, experiment_design
         from experiments, phenotype_experiment_info
         where phenotype_experiment_info.experiment_uid = experiments.experiment_uid
         and experiments.experiment_uid = $uid";
    }
    $res = mysqli_query($mysqli, $sql) or dieNice(mysqli_error($mysqli));
    if ($row = mysqli_fetch_row($res)) {
        $results["studyId"] = $uid;
        $results["studyType"] = "trial";
        $results["name"] = $row[0];
        $results["objective"] = "";
        $results["startDate"] = $row[1];
        $results["keyContact"] = $row[2];
        $results["locationName"] = $row[3];
        $results["designType"] = $row[4];
    } else {
        $results = null;
        $return = json_encode($results);
        header("Content-Type: application/json");
        echo "$return";
        die();
    }
    $sql = "select distinct(line_record_uid) from tht_base where experiment_uid = $uid";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
    while ($row = mysqli_fetch_row($res)) {
        $temp['germplasmId'] = $row[0];
        $temp["germplasmName"] = $name_list[$row[0]];
        $results["design"][] = $temp;
    }
    $sql = "select plot_uid, plot, block, row_id, column_id, replication, check_id, line_uid from fieldbook
        where experiment_uid = $uid order by row_id, plot";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
    while ($row = mysqli_fetch_row($res)) {
        $temp["plotId"] = $row[0];
        $temp["plot"] = $row[1];
        $temp["blockId"] = $row[2];
        $temp["rowId"] = $row[3];
        $temp["columnId"] = $row[4];
        $temp["replication"] = $row[5];
        $temp["checkId"] = $row[6];
        $temp["germplasmId"] = $row[7];
        $temp["germplasmName"] = $name_list[$row[7]];
        if (!preg_match("/\d+/", $row_id)) {
              $error = 1;
        }
        if (!preg_match("/\d+/", $column_id)) {
              $error = 1;
        }
        $results["design"][] = $temp;
    }
    if ($outFormat == "json") {
        $return = json_encode($results);
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
