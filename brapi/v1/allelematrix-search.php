<?php
include('../../includes/bootstrap.inc');
$mysqli = connecti();

$self = $_SERVER['PHP_SELF'];
$script = $_SERVER["SCRIPT_NAME"]."/";
$rest = str_replace($script, "", $self);
$rest = explode("/", $rest);

$results['metadata']['status'] = array();
$results['metadata']['datafiles'] = array();

if (isset($_REQUEST['pageSize'])) {
    $pageSize = $_REQUEST['pageSize'];
} else {
    $pageSize = 1000;
}
if (isset($_REQUEST['page'])) {
    $currentPage = $_REQUEST['page'];
} else {
    $currentPage = 1;
}

header("Content-Type: application/json");

function dieNice($code, $msg)
{
    global $results;
    $results['metadata']['pagination'] = null;
    $results['metadata']['status'][] = array("code" => $code, "message" => "$msg");
    $results['result'] = null;
    $return = json_encode($results);
    die("$return");
}

if ($rest[0] == "status") {
    if (isset($rest[1])) {
        $unqStr = $rest[1];
    } else {
        dieNice("Error", "missing message id");
    }
    $results['metadata']['pagination'] = null;
    $results['result'] = null;
    $tmpFile = "/tmp/tht/download_" . $unqStr . ".txt";
    $statusFile = "/tmp/tht/status_" . $unqStr . ".txt";
    $results['metadata']['datafiles'] = array($tmpFile);
    if (file_exists($statusFile)) {
        if (filesize($statusFile) > 0) {
            $results['metadata']['status'][] = array("code" => "asyncstatus", "message" => "FAILED");
        } else {
            $results['metadata']['status'][] = array("code" => "asyncstatus", "message" => "FINISHED");
        }
    } else {
        $results['metadata']['status'][] = array("code" => "asyncstatus", "message" => "PENDING");
    }
    $return = json_encode($results);
    die("$return");
} elseif (isset($_REQUEST['markerprofileDbId'])) {
    $uniqueStr = chr(rand(65, 80)).chr(rand(65, 80)).chr(rand(65, 80)).chr(rand(65, 80));
    $errorFile = "/tmp/tht/error_" . $uniqueStr . ".txt";
    //list of markerprofileDbId can be in either format
    $tmp = $_REQUEST['markerprofileDbId'];
    if (preg_match("/,/", $tmp)) {
        $profile_list = explode(",", $tmp);
    } else {
        $request = $_SERVER["REQUEST_URI"];
        if (preg_match_all("/markerprofileDbId=([0-9_]+)/", $request, $match)) {
            foreach ($match[1] as $key => $val) {
                //echo "found $key $val[0] $val\n";
                $profile_list[] = $val;
            }
        }
        $tmp = implode(",", $profile_list);
    }

    //first query all data
    foreach ($profile_list as $item) {
        if (preg_match("/(\d+)_(\d+)/", $item, $match)) {
            $exp_ary[] = $match[2];
        } else {
            $results['metadata']['status'][] = array("code" => "parm", "message" => "Error: invalid format of marker profile id $item");
            continue;
        }
    }

    $countExp = count($profile_list);
    if ($countExp > 1) {
        $cmd = "php allelematrix-search-offline.php \"$tmp\" \"$uniqueStr\" > /dev/null 2> $errorFile";
        exec($cmd);
        dieNice("asynchid", "$uniqueStr");
    }
    $num_rows = 0;
    foreach ($profile_list as $item) {
        //echo "profile = $item\n";
        if (preg_match("/(\d+)_(\d+)/", $item, $match)) {
            $lineuid = $match[1];
            $expid = $match[2];
        } else {
            dieNice("Error", "invalid format of marker profile id $item");
        }

        //get marker_uid
        $sql = "select marker_index from allele_byline_expidx where experiment_uid = $expid";
        $res = mysqli_query($mysqli, $sql);
        if ($row = mysqli_fetch_row($res)) {
            $marker_index = $row[0];
            $marker_index = explode(",", $marker_index);
        } else {
            dieNice("Error", "invalid experiment $expid");
        }

        //now get just those selected
        $sql = "select alleles from allele_byline_exp where experiment_uid = $expid and line_record_uid = $lineuid";
        if ($currentPage == 1) {
        } else {
            $offset = ($currentPage - 1) * $pageSize;
            if ($offset < 1) {
                $offset = 1;
            }
        }
        $found = 0;
        if ($res = mysqli_query($mysqli, $sql)) {
            while ($row = mysqli_fetch_row($res)) {
                $found = 1;
                $alleles = $row[0];
                $alleles_ary = explode(",", $alleles);
                foreach ($alleles_ary as $i => $v) {
                    if ($v[0] == $v[1]) {
                        $v = $v[0];
                    } else {
                        $v = $v[0] . "/" . $v[1];
                    }
                    $num_rows++;
                    $marker_uid = $marker_index[$i];
                    $dataList[] = array( "$marker_index[$i]", "$item", "$v");
                }
            }
        } else {
            dieNice("SQL", mysqli_error($mysqli));
        }
        if ($found == 0) {
            dieNice("Error", "marker profile not found $item");
        }
        $resultProfile[] = $item;
    }
} else {
    //first query all data
    dieNice("Error", "need markerprofileDbId");
    $num_rows = 0;
    $profile_list = array();
    if ($currentPage == 1) {
        $offset = 0;
        $limit = $pageSize;
    } else {
        $offset = ($currentPage - 1) * $pageSize;
        $limit = $offset + $pageSize;
    }
    $sql = "select experiment_uid, line_record_uid, count from allele_byline_exp";
    $res = mysqli_query($mysqli, $sql);
    while ($row = mysqli_fetch_row($res)) {
        $expid = $row[0];
        $lineuid = $row[1];
        $count = $row[2];
        $item = $lineuid . "_" . $expid;
        if (($num_rows == 0) && ($offset == 0)) {
            $profile_list[] = $item;
        } elseif ($num_rows > $offset) {
            if (empty($profile_list)) {
                $profile_list[] = $item;
            } elseif ($num_rows < $limit) {
                $profile_list[] = $item;
            }
        }
        $num_rows += $count;
    }
    
    foreach ($profile_list as $item) {
        if (preg_match("/(\d+)_(\d+)/", $item, $match)) {
            $lineuid = $match[1];
            $expid = $match[2];
        } else {
            $results['metadata']['status'][] = array("code" => "parm", "message" => "Error: invalid format of marker profile id $item");
            continue;
        }

        //now get just those selected
        $sql = "select marker_index from allele_byline_expidx
              where experiment_uid = $expid";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        if ($row = mysqli_fetch_row($res)) {
            $tmp = $row[0];
            $marker_index = explode(",", $tmp);
        }
        $sql = "select alleles from allele_byline_exp
              where line_record_uid = $lineuid
              and experiment_uid = $expid";
        /**$sql = "select marker_uid, alleles from allele_cache
              where line_record_uid = $lineuid
              and experiment_uid = $expid
              and not alleles = '--'
              order by marker_uid"; **/
        $res = mysqli_query($mysqli, $sql);
        if ($row = mysqli_fetch_row($res)) {
            $tmp = $row[0];
            $alleles = explode(",", $tmp);
            foreach ($alleles as $key => $val) {
                $dataList[$marker_index[$key]][] = $val;
            }
        }
        $resultProfile[] = $item;
    }
}


$tot_pag = ceil($num_rows / $pageSize);
$pageList = array( "pageSize" => $pageSize, "currentPage" => $currentPage, "totalCount" => $num_rows, "totalPages" => $tot_pag );
$results['metadata']['pagination'] = $pageList;
$results['result']['data'] = $dataList;
echo json_encode($results);
