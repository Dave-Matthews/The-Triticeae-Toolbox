<?php
include('../../includes/bootstrap.inc');
$mysqli = connecti();

$num_args = $_SERVER["argc"];

$markerProfile = $_SERVER['argv'][1];
$unqStr = $_SERVER['argv'][2];

$tmpFile = "/tmp/tht/download_" . $unqStr . ".txt";
$statusFile = "/tmp/tht/status_" . $unqStr . ".txt";
$fh = fopen($tmpFile, "w") or die("Error can not open file $tmpFile");

function dieNice($code, $msg)
{
    global $fh;
    global $statusFile;
    $results['metadata']['pagination'] = null;
    $results['metadata']['status'][] = array("code" => $code, "message" => "$msg");
    $results['result'] = null;
    fwrite($fh, json_encode($results));
    fclose($fh);
    $fh = fopen($statusFile, "w") or die("Error can not open file $tmpFile");
    fwrite($fh, "$code: $msg");
    fclose($fh);
    die();
}

if (preg_match("/,/", $markerProfile)) {
    $profile_list = explode(",", $markerProfile);
    $countExp = count($profile_list);
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
        $res = mysqli_query($mysqli, $sql) or die("Error mysqli_error($mysqli)\n");
        if ($row = mysqli_fetch_row($res)) {
            $marker_index = $row[0];
            $marker_index = explode(",", $marker_index);
        } else {
            dieNice("Error", "invalid experiment $expid");
        }

        //now get just those selected
        $sql = "select alleles from allele_byline_exp where experiment_uid = $expid and line_record_uid = $lineuid";
        if ($currentPage == 0) {
        } else {
            $offset = $currentPage * $pageSize;
            if ($offset < 0) {
                $offset = 0;
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
            dieNice("SQL", "mysqli_error($mysqli)");
        }
        if ($found == 0) {
            dieNice("Error", "marker profile not found $item");
        }
        $resultProfile[] = $item;
    }
}
$tot_pag = null;
$pageList = array( "pageSize" => $pageSize, "currentPage" => $currentPage, "totalCount" => $num_rows, "totalPages" => $tot_pag );
$results['metadata']['pagination'] = $pageList;
$results['result']['data'] = $dataList;
fwrite($fh, json_encode($results, JSON_UNESCAPED_SLASHES));
fclose($fh);
$fh = fopen($statusFile, "w") or die("Error can not open file $tmpFile");
fclose($fh);
