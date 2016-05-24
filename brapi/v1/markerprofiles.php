<?php
/**
 * brapi/0.1/genotype.php, DEM jun 2014
 * Deliver genotyping data for a line according to
 * http://docs.breeding.apiary.io/
 * 120414 - changed structure of output to match API CLB
 * 082115 - changed structure of output to match ABP CLB
 */

require 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
$mysqli = connecti();

// URI is something like genotype/{id}/[count][?analysisMethod={platform}][..]
// Extract the pseudo-path part of the REST args.
$self = $_SERVER['PHP_SELF'];
$script = $_SERVER["SCRIPT_NAME"]."/";
$rest = str_replace($script, "", $self);
$rest = explode("/", $rest);
$profileid = $rest[0];
//$command = $rest[1];
$command = "";
$lineuid = "";
$expuid = "";
$analmeth = "";
if (isset($_GET['germplasmId'])) {
    $command = "find";
    $lineuid = $_GET['germplasmId'];
}
if (isset($_GET['extractId'])) {
    $command = "find";
    $expuid = $_GET['extractId'];
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
// Get the URI's querystring.
if ($_GET) {
    $getkeys = array_keys($_GET);
    $analmeth = $_GET['method'];
}

function dieNice($msg)
{
    $linearray['metadata']['pagination'] = null;
    $linearray['metadata']['status'] = array("code" => 1, "message" => "SQL Error: $msg");
    $linearray['result'] = null;
    $return = json_encode($linearray);
    header("Content-Type: application/json");
    die("$return");
}

// Is there a command?
if ($command) {
    if (($lineuid != "") && ($expuid != "")) {
        // "Get Marker Count By Germplasm Id"
        // URI is genotype/{id}/count[?analysisMethod={platform}]
        $linearray['result']['markerprofileDbId'] = $lineuid;

        $linearray['germplasmDbId'] = $lineuid;
        // Get the number of non-missing allele data points for this line, by experiment.
        $sql = "select count(experiment_uid) from allele_cache 
	    where line_record_uid = $lineuid 
            and experiment_uid = $expuid
	    and not alleles = '--'";
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
                $linearray['result']['extractDbId'] = $expuid;
                $linearray['result']['analysisMethod'] = $analysisMethod;
                //$linearray['result']['resultCount'] = $resultCount;
            }
        }
    } elseif ($lineuid != "") {
        $sql = "select experiment_uid, count(experiment_uid) from allele_cache 
            where line_record_uid = $lineuid 
            and not alleles = '--' 
            group by experiment_uid;";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        while ($row = mysqli_fetch_row($res)) {
            $result = array();
            $expuid = $row[0];
            $result['markerProfileId'] = $lineuid . "_" . $row[0];
            $result['extractId'] = $row[0];
            $result['resultCount'] = $row[1];
            $analysisMethod = mysql_grab(
                "select platform_name from platform p, genotype_experiment_info g
                where p.platform_uid = g.platform_uid
                and g.experiment_uid = $expuid"
            );
            $result['analysisMethod'] = $analysisMethod;
            $linearray['result'][] = $result;
        }
    } elseif ($expuid != "") {
        $count = 0;
        $linearray['results'] = array();
        $metadata['metadata']['status'] = null;
        $sql = "select line_record_uid, count(line_record_uid) from allele_cache 
            where experiment_uid = $expuid 
            and not alleles = '--' 
            group by line_record_uid;";
        if ($res = mysqli_query($mysqli, $sql) == false) {
            $metadata['metadata']['status'][] = array("code" => "sql error", "message" => mysqli_error($mysqli));
        } else {
            while ($row = mysqli_fetch_row($res)) {
                $count++;
                $line_record_uid = $row[0];
                $result['markerProfileId'] = $row[0] . "_" . $expuid;
                $result['germplasmID'] = $row[0];
                $result['extractId'] = $expuid;
                $result['resultCount'] = $row[1];
                $analysisMethod = mysql_grab(
                    "select platform_name from platform p, genotype_experiment_info g
                    where p.platform_uid = g.platform_uid
                    and g.experiment_uid = $expuid"
                );
                $result['analysisMethod'] = $analysisMethod;
                $linearray['result'][] = $result;
            }
            if ($count == 0) {
                $metadata['metadata']['status'][] = array("code" => "not found", "message" => "No entries in database");
            }
        }
        $metadata['metadata']['pagination']['pageSize'] = $pageSize;
        $metadata['metadata']['pagination']['currentPage'] = $currentPage;
        $metadata['metadata']['pagination']['totalCount'] = $count;
        $metadata['metadata']['pagination']['totalPages'] = ceil($count / $pageSize);
    }
    header("Content-Type: application/json");
    /* Requires PHP 5.4.0: */
    /* echo json_encode($response, JSON_PRETTY_PRINT); */
    $response = array_merge($metadata, $results);
    echo json_encode($response);
} else {
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
    $linearray['result']['extractDbId'] = $expid;
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
    if ($currentPage == 1) {
        $sql .= " limit $pageSize";
    } else {
        $offset = ($currentPage - 1) * $pageSize;
        if ($offset < 1) {
            $offset = 1;
        }
        $sql .= " limit $offset, $pageSize";
    }
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    $tot_pag = ceil($num_rows / $pageSize);
    $pageList = array( "pageSize" => $pageSize, "currentPage" => 1, "totalCount" => $num_rows, "totalPages" => $tot_pag );
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
}
