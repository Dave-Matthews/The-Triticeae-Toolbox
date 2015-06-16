<?php
include('../../includes/bootstrap.inc');
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
    if (isset($_GET['program'])) {
        $cap_uid = $_GET['program'];
        $sql_opt = "and CAPdata_programs_uid = $cap_uid";
    } else {
        $sql_opt = "";
    }
    $sql = "select distinct(fieldbook.experiment_uid), trial_code, planting_date, collaborator, location, experiment_design, CAPdata_programs_uid
        from fieldbook, experiments, phenotype_experiment_info
        where fieldbook.experiment_uid = experiments.experiment_uid
        and phenotype_experiment_info.experiment_uid = experiments.experiment_uid " . $sql_opt;
    $sql = "select distinct(experiments.experiment_uid), trial_code, planting_date, collaborator, location, experiment_design, CAPdata_programs_uid
        from experiments, phenotype_experiment_info
        where phenotype_experiment_info.experiment_uid = experiments.experiment_uid " . $sql_opt;
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
    while ($row = mysqli_fetch_row($res)) {
        $uid = $row[0];
        $trial = $row[1];
        $temp["studyId"] = $row[0];
        $temp["studyType"] = "trial";
        $temp["name"] = $row[1];
        $CAP_uid = $row[6];
        $temp["programName"] = $program;
        $temp["startDate"] = $row[2];
        $temp["keyContact"] = $row[3];
        $temp["locationName"] = $row[4];
        $temp["designType"] = $row[5];
        $sql = "select data_program_name from CAPdata_programs where CAPdata_programs_uid = $CAP_uid";
        $res2 = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
        if ($row2 = mysqli_fetch_row($res2)) {
            $program = $row2[0];
        }
        $temp["programName"] = $program;
        $results[] = $temp;
    }
    $return = json_encode($results);
    echo "$return";
} elseif ($uid != "") {
    $sql = "select line_record_uid, line_record_name from line_records";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
    while ($row = mysqli_fetch_row($res)) {
                $line_uid= $row[0];
                $line_record_name = $row[1];
                $name_list[$line_uid] = $line_record_name;
    }
    $sql = "select trial_code, planting_date, collaborator, location, experiment_design from experiments, phenotype_experiment_info
         where phenotype_experiment_info.experiment_uid = experiments.experiment_uid
         and experiments.experiment_uid = $uid";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
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
        echo "$return";
        die();
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
    $return = json_encode($results);
    echo "$return";
} else {
    echo "Error: missing experiment id<br>\n";
}
