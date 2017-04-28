<?php
include('../../includes/bootstrap.inc');
$mysqli = connecti();

$self = $_SERVER['PHP_SELF'];
$script = $_SERVER["SCRIPT_NAME"]."/";
$results['metadata']['status'] = array();
$results['metadata']['datafiles'] = array();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    die("does not support POST");
} else {
    die("missing uid parameter");
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

header("Content-Type: application/json");

function dieNice($msg)
{
    global $results;
    $results['metadata']['pagination'] = null;
    $results['metadata']['status'][] = array("code" => "SQL", "message" => "SQL Error: $msg");
    $results['result'] = null;
    $return = json_encode($results);
    die("$return");
}

if (isset($_GET['markerprofileDbId'])) {
    //list of markerprofileDbId can be in either format
    $tmp = $_GET['markerprofileDbId'];
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
    $exp_lst = implode(",", $exp_ary);
    $num_rows = 0;
    $sql = "select count from allele_byline_exp where experiment_uid IN ($exp_lst)";
    //$res = mysqli_query($mysqli, $sql) or dieNice("invalid experiment_uid");
    //while ($row = mysqli_fetch_row($res)) {
    //    $num_rows += $row[0];
    //}

    foreach ($profile_list as $item) {
        //echo "profile = $item\n";
        if (preg_match("/(\d+)_(\d+)/", $item, $match)) {
            $lineuid = $match[1];
            $expid = $match[2];
        } else {
            dieNice("invalid format of marker profile id $item");
        }

        //get marker_uid
        $sql = "select marker_index from allele_byline_expidx where experiment_uid = $expid";
        $res = mysqli_query($mysqli, $sql);
        if ($row = mysqli_fetch_row($res)) {
            $marker_index = $row[0];
            $marker_index = explode(",", $marker_index);
        } else {
            dieNice("invalid experiment $expid");
        }

        //now get just those selected
        $sql = "select marker_uid, alleles from allele_cache
              where line_record_uid = $lineuid
              and experiment_uid = $expid
              and not alleles = '--'
              order by marker_uid";
        $sql = "select alleles from allele_byline_exp where experiment_uid = $expid and line_record_uid = $lineuid)";
        if ($currentPage == 1) {
        } else {
            $offset = ($currentPage - 1) * $pageSize;
            if ($offset < 1) {
                $offset = 1;
            }
        }
        $found = 0;
        $res = mysqli_query($mysqli, $sql);
        while ($row = mysqli_fetch_row($res)) {
            $found = 1;
            $alleles = $row[0];
            $alleles_ary = explode(",", $alleles);
            foreach ($alleles_ary as $i => $v) {
                $num_rows++;
                $marker_uid = $marker_index[$i];
                $dataList[] = array( "$marker_index[$i]", "$item", "$v");
            }
        }
        if ($found == 0) {
            dieNice("marker profile not found $item");
        }
        $resultProfile[] = $item;
    }
} else {
    //first query all data
    dieNice("need markerprofileDbId");
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
