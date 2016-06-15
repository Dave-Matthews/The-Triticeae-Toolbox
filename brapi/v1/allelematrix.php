<?php
include('../../includes/bootstrap.inc');
$mysqli = connecti();

$self = $_SERVER['PHP_SELF'];
$script = $_SERVER["SCRIPT_NAME"]."/";
$results['metadata']['status'] = null;

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
} else {
    echo "missing uid parameter";
    continue;
}

if (isset($_GET['pageSize'])) {
    $pageSize = $_GET['pageSize'];
} else {
    $pageSize = 100;
}
if (isset($_GET['page'])) {
    $currentPage = $_GET['page'];
} else {
    $currentPage = 1;
}

header("Content-Type: application/json");

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
        }
    }
    $exp_lst = implode(",", $exp_ary);
    $sql = "select max(count) from allele_byline_exp where experiment_uid IN ($exp_lst)";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    if ($row = mysqli_fetch_row($res)) {
        $num_rows = $row[0];
    } else {
        die("Error: experiment not found\n");
    }

    $sql = "select alleles from allele_byline_exp where experiment_uid IN ($exp_lst)";
    if ($currentPage == 1) {
        $sql .= " limit $pageSize";
    } else {
        $offset = ($currentPage - 1) * $pageSize;
        if ($offset < 1) {
            $offset = 1;
        }
        $sql .= " limit $offset, $pageSize";
    }

    foreach ($profile_list as $item) {
        if (preg_match("/(\d+)_(\d+)/", $item, $match)) {
            $lineuid = $match[1];
            $expid = $match[2];
        } else {
            echo "Error: invalid format of marker profile id $lineuid<br>\n";
            continue;
        }

        //now get just those selected
        $sql = "select marker_uid, alleles from allele_cache
              where line_record_uid = $lineuid
              and experiment_uid = $expid
              and not alleles = '--'
              order by marker_name";
        $res = mysqli_query($mysqli, $sql);
        while ($row = mysqli_fetch_row($res)) {
            $count++;
            $dataList[$row[0]][] = $row[1];
        }
        $resultProfile[] = $item;
    }
} else {
    $pageSize = 1; //return all markers for each line
    //first query all data
    $sql = "select count(*) from allele_byline_exp";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    if ($row = mysqli_fetch_row($res)) {
        $num_rows = $row[0];
    } else {
        die("Error: experiment not found\n");
    }

    $sql = "select experiment_uid, line_record_uid, alleles from allele_byline_exp";
    if ($currentPage == 1) {
        $sql .= " limit $pageSize";
    } else {
        $offset = $currentPage;
        $sql .= " limit $offset, $pageSize";
    }
    //echo "$sql\n";

    $res = mysqli_query($mysqli, $sql);
    while ($row = mysqli_fetch_row($res)) {
        $count++;
        $exp_uid = $row[0];
        $lin_uid = $row[1];
        $alleles = $row[2];
        $item = $exp_uid . "_" . $lin_uid;
        $resultProfile[] = $item;
        $sql = "select marker_index from allele_byline_expidx where experiment_uid = $exp_uid";
        $res2 = mysqli_query($mysqli, $sql);
        $row2 = mysqli_fetch_row($res2);
        $marker_ary = explode(",", $row2[0]);
        $allele_ary = explode(",", $alleles);
        foreach ($allele_ary as $key => $val) {
            $dataList[$marker_ary[$key]][] = $val;
        }
    }
}


foreach ($dataList as $key => $val) {
    $dataList2[] = array($key => $val);
}
$tot_pag = ceil($num_rows / $pageSize);
$pageList = array( "pageSize" => $pageSize, "currentPage" => $currentPage, "totalCount" => $num_rows, "totalPages" => $tot_pag );
$results['metadata']['pagination'] = $pageList;
$results['result']['markerprofileDbIds'] = $resultProfile;
$results['result']['data'] = $dataList2;
echo json_encode($results);
