<?php
/**
 * brapi/0.1/genotype.php, DEM jun 2014
 * Deliver genotyping data for a line according to
 * http://docs.breeding.apiary.io/
 * 120414 - changed structure of output to match API CLB
 * 082115 - changed structure of output to match ABP CLB
 */

require '../../includes/bootstrap.inc';
$mysqli = connecti();
ini_set('memory_limit', '2G');

// URI is something like genotype/{id}/[count][?analysisMethod={platform}][..]
// Extract the pseudo-path part of the REST args.
$self = $_SERVER['PHP_SELF'];
$script = $_SERVER["SCRIPT_NAME"]."/";
$rest = str_replace($script, "", $self);
$rest = explode("/", $rest);
//echo "rest[0] = $rest[0], rest[1] = $rest[1]\n";
if (!empty($rest[0])) {
    $profileid = $rest[0];
}
//$command = $rest[1];
$command = "";
$lineuid = "";
$expuid = "";
$analmeth = "";
if (isset($_GET['germplasmDbId'])) {
    $command = "find";
    $lineuid = $_GET['germplasmDbId'];
}
if (isset($_GET['extractDbId'])) {
    dieNice("extractDbId not supported");
}
if (isset($_GET['studyDbId'])) {
    $command = "find";
    $expuid = $_GET['studyDbId'];
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
// Get the URI's querystring.
if ($_GET) {
    $getkeys = array_keys($_GET);
    $analmeth = $_GET['methodDbId'];
}

function dieNice($msg)
{
    $linearray['metadata']['pagination'] = null;
    $linearray['metadata']['status'] = array("code" => 1, "message" => "Error: $msg");
    $linearray['result'] = null;
    $return = json_encode($linearray);
    header("Content-Type: application/json");
    die("$return");
}

$response['metadata']['status'] = array();
$response['metadata']['datafiles'] = array();

// Is there a command?
if ($command) {
    if (($lineuid != "") && ($expuid != "")) {
        // "Get Marker Count By Germplasm Id"
        // URI is genotype/{id}/count[?analysisMethod={platform}]
        $linearray['markerprofileDbId'] = $lineuid;
        $linearray['germplasmDbId'] = $lineuid;
        // Get the number of non-missing allele data points for this line, by experiment.
        $sql = "select count from allele_byline_exp
            where line_record_uid = $line_uid
            and experiment_uid = $exp_uid";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        while ($row = mysqli_fetch_row($res)) {
            $resultCount = $row[0];
            $analysisMethod = mysql_grab(
                "select platform_name from platform p, genotype_experiment_info g
                where p.platform_uid = g.platform_uid
                and g.experiment_uid = $expuid"
            );
            // Restrict to the requested analysis method if any.
            if (!$analmeth or $analmeth == $analysisMethod) {
                //$linearray['extractDbId'] = $expuid;
                //$linearray['analysisMethod'] = $analysisMethod;
                //$linearray['result']['resultCount'] = $resultCount;
                $response['data'][] = $linearray;
            }
        }
        $response['metadata']['pagination']['pageSize'] = $pageSize;
        $response['metadata']['pagination']['currentPage'] = $currentPage;
        $response['metadata']['pagination']['totalCount'] = $count;
        $response['metadata']['pagination']['totalPages'] = ceil($count / $pageSize);
    } elseif ($lineuid != "") {
        $pageList = array();
        $response['metadata']['pagination'] = $pageList;
        $sql = "select experiment_uid, count from allele_byline_exp
            where line_record_uid = $lineuid";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        $count = mysqli_num_rows($res);
        while ($row = mysqli_fetch_row($res)) {
            $expuid = $row[0];
            $resultCount = $row[1];
            $linearray['markerProfileDbId'] = $lineuid . "_" . $row[0];
            $linearray['germplasmDbId'] = $lineuid;
            $linearray['extractDbId'] = null;
            $analysisMethod = mysql_grab(
                "select platform_name from platform p, genotype_experiment_info g
                where p.platform_uid = g.platform_uid
                and g.experiment_uid = $expuid"
            );
            $linearray['analysisMethod'] = $analysisMethod;
            $linearray['resultCount'] = $resultCount;
            $data[] = $linearray;
        }
        $response['result']['data'] = $data;
        $response['metadata']['pagination']['pageSize'] = $pageSize;
        $response['metadata']['pagination']['currentPage'] = $currentPage;
        $response['metadata']['pagination']['totalCount'] = $count;
        $response['metadata']['pagination']['totalPages'] = ceil($count / $pageSize);
    } elseif ($expuid != "") {
        $pageList = array();
        $response['metadata']['pagination'] = $pageList;
        $sql = "select line_record_uid, count from allele_byline_exp
            where experiment_uid = $expuid";
        $res = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($res);
        if ($count == 0) {
            dieNice("experiment $expuid not found");
        } elseif ($res == false) {
            $response['metadata']['status'][] = array("code" => "sql error", "message" => mysqli_error($mysqli));
        } else {
            while ($row = mysqli_fetch_row($res)) {
                $count++;
                $line_record_uid = $row[0];
                $alleles = $row[1];
                $resultCount = $row[2];
                $linearray['markerProfileDbId'] = $row[0] . "_" . $expuid;
                $linearray['germplasmDbId'] = $row[0];
                $linearray['extractDbId'] = null;
                $analysisMethod = mysql_grab(
                    "select platform_name from platform p, genotype_experiment_info g
                    where p.platform_uid = g.platform_uid
                    and g.experiment_uid = $expuid"
                );
                $linearray['analysisMethod'] = $analysisMethod;
                $linearray['resultCount'] = $resultCount;
                $data[] = $linearray;
            }
            if ($count == 0) {
                $metadata['metadata']['status'][] = array("code" => "not found", "message" => "No entries in database");
            }
        }
        $response['result']['data'] = $data;
        $response['metadata']['pagination']['pageSize'] = $pageSize;
        $response['metadata']['pagination']['currentPage'] = $currentPage;
        $response['metadata']['pagination']['totalCount'] = $count;
        $response['metadata']['pagination']['totalPages'] = ceil($count / $pageSize);
    }
    header("Content-Type: application/json");
    echo json_encode($response);
} elseif (isset($profileid)) {
    // If no command, then it's a marker profile id (Line Expreriment).
    // "Get Genotype By Id"
    // URI is something like genotype/{id}[?runId={runId}][&analysisMethod={method}][&pageSize={pageSize}&page={page}]
    if (preg_match("/(\d+)_(\d+)/", $profileid, $match)) {
        $lineuid = $match[1];
        $expid = $match[2];
    } else {
        dieNice("Error: invalid format of marker profile id $lineuid");
    }
    $pageList = array();
    $linearray['metadata']['pagination'] = $pageList;
    $linearray['metadata']['status'] = null;
    $linearray['result']['markerprofileDbId'] = $profileid;
    $linearray['result']['germplasmDbId'] = $lineuid;
    $linearray['result']['extractDbId'] = null;
    $linearray['result']['encoding'] = "AA,BB,AB";

    $linearray['result']['analysisMethod'] = mysql_grab("select platform_name 
				    from platform p, genotype_experiment_info g
				    where p.platform_uid = g.platform_uid
				    and g.experiment_uid = $expid");
    $data = array();
    //first query all data
    $sql = "select marker_name, alleles from allele_cache
	      where line_record_uid = $lineuid
	      and experiment_uid = $expid
	      and not alleles = '--'
	      order by marker_name";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    $num_rows = mysqli_num_rows($res);

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
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    $tot_pag = ceil($num_rows / $pageSize);
    $pageList = array( "pageSize" => $pageSize, "currentPage" => 0, "totalCount" => $num_rows, "totalPages" => $tot_pag );
    $linearray['metadata']['pagination'] = $pageList;
    while ($row = mysqli_fetch_row($res)) {
        $count++;
        $data[] = array($row[0] => $row[1]);
    }
    $linearray['result']['data'] = $data;
    //$response = array($linearray, $genotypes);
    $response = $linearray;
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json");
    /* Requires PHP 5.4.0: */
    /* echo json_encode($response, JSON_PRETTY_PRINT); */
    echo json_encode($response);
    /* echo json_encode($linearray);
    /* print_h($response); */
} else {
    // if no command, then list all marker profiles
    //first query all data
    $sql = "select line_record_uid, experiment_uid, count from allele_byline_exp order by line_record_uid, experiment_uid";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    $num_rows = mysqli_num_rows($res);

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
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    $tot_pag = ceil($num_rows / $pageSize);
    $pageList = array( "pageSize" => $pageSize, "currentPage" => $currentPage, "totalCount" => $num_rows, "totalPages" => $tot_pag );
    $response['metadata']['pagination'] = $pageList;
    $response['metadata']['status'] = null;
    while ($row = mysqli_fetch_row($res)) {
        $line_uid = $row[0];
        $exp_uid = $row[1];
        $resultCount = $row[2];
        $profileid = $line_uid . "_" . $exp_uid;
        $linearray['markerprofileDbId'] = $profileid;
        $linearray['germplasmDbId'] = $line_uid;
        $linearray['extractDbId'] = null;
        $analysisMethod = mysql_grab(
            "select platform_name from platform p, genotype_experiment_info g
            where p.platform_uid = g.platform_uid
            and g.experiment_uid = $exp_uid"
        );
        // Restrict to the requested analysis method if any.
        if (!$analmeth or $analmeth == $analysisMethod) {
            $linearray['extractDbId'] = null;
            $linearray['analysisMethod'] = $analysisMethod;
            $linearray['resultCount'] = $resultCount;
            $response['result']['data'][] = $linearray;
        }
    }
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json");
    echo json_encode($response);
}
