<?php
/**
 * brapi/v1/maps.php
 * Deliver genome maps according to http://docs.brapi.apiary.io
 *
 */

require '../../includes/bootstrap.inc';
$mysqli = connecti();
mysqli_set_charset($mysqli, 'utf8');

$self = $_SERVER['PHP_SELF'];
$script = $_SERVER["SCRIPT_NAME"]."/";
$rest = str_replace($script, "", $self);
$rest = explode("/", $rest);
header("Content-Type: application/json");
//echo "self = $self script = $script rest[0] = $rest[0] rest[1] = $rest[1] rest[2] = $rest[2]\n";
if (isset($_GET['action'])) {
    $uid = $_GET['action'];
    //echo "cmd = $uid<br>\n";
}
$uid_ary = null;
if ($rest[1] == "positions") {
    $action = "MapData";
    $uid = $rest[0];
    if (isset($_GET['linkageGroupIdList'])) {
        $uid_lst = $_GET['linkageGroupIdList'];
        $uid_ary = explode(",", $uid_lst);
    } elseif ($rest[2] == "linkageGroupId") {
        $action = "MapDataRange";
        $uid = $rest[0];
        $min = $_GET['min'];
        $max = $_GET['max'];
    }
    //echo "action = $action\n";
} elseif (is_numeric($rest[0])) {
    $uid = $rest[0];
    $action = "details";
} else {
    $action = "list";
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
if (isset($_GET['uid'])) {
    $uid = $_REQUEST['uid'];
}
if ($action == "list") {
    $linearray['metadata']['status'] = array();
    $linearray['metadata']['datafiles'] = array();
    //first query all data
    $sql = "select mapset.mapset_uid, mapset_name, species, map_type, map_unit, published_on, comments
    from mapset, markers_in_maps as mim, map
    WHERE mim.map_uid = map.map_uid
    AND map.mapset_uid = mapset.mapset_uid
    GROUP by mapset.mapset_uid";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    $num_rows = mysqli_num_rows($res);
    $tot_pag = ceil($num_rows / $pageSize);
    $pageList = array( "pageSize" => $pageSize, "currentPage" => 0, "totalCount" => $num_rows, "totalPages" => $tot_pag );
    $linearray['metadata']['pagination'] = $pageList;

    $sql = "select count(*), mapset.mapset_uid, mapset_name, species, map_type, map_unit, published_on, comments
    from mapset, markers_in_maps as mim, map
    WHERE mim.map_uid = map.map_uid
    AND map.mapset_uid = mapset.mapset_uid
    GROUP BY mapset.mapset_uid";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row = mysqli_fetch_row($res)) {
        $uid = $row[1];
        $temp["mapDbId"] = (integer) $row[1];
        $temp["name"] = $row[2];
        $temp["species"] = $row[3];
        $temp["type"] = $row[4];
        $temp["unit"] = $row[5];
        $temp["publishedDate"] = $row[6];
        // Handle values 0000-00-00.
        if (preg_match("/0000-00-00/", $temp["publishedDate"])) {
        } else {
            $timestamp = strtotime($temp["publishedDate"]);
            // Handle missing values.
            if ($timestamp == 0) {
            } else {
                $temp['publishedDate'] = date("Y-m-d", $timestamp);
            }
        }
        $temp["markerCount"] = (integer) $row[0];
        $sql = "select count(distinct(chromosome)) from markers_in_maps, map
        where map.map_uid = markers_in_maps.map_uid
        and mapset_uid = $uid";
        $res2 = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        if ($row2 = mysqli_fetch_row($res2)) {
            $temp["linkageGroupCount"] = (integer) $row2[0];
        } else {
            $temp["linkageGroupCount"] = "Error";
        }
        $temp["comments"] = $row[7];
        $linearray['result']['data'][] = $temp;
    }
    $return = json_encode($linearray);
    echo "$return";
} elseif ($action === "details") {
    $linearray['metadata']['status'] = array();
    $linearray['metadata']['datafiles'] = array();
    //first query all data
    $sql = "select chromosome
        from markers_in_maps, markers, map
        where markers_in_maps.marker_uid = markers.marker_uid
        AND map.map_uid = markers_in_maps.map_uid
        AND mapset_uid = $uid 
        GROUP by chromosome";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    $num_rows = mysqli_num_rows($res);
    $results['metadata']['status'][] = array("code" => "sql error", "message" => "$error");
    $tot_pag = ceil($num_rows / $pageSize);
    $pageList = array( "pageSize" => $pageSize, "currentPage" => 0, "totalCount" => $num_rows, "totalPages" => $tot_pag );
    $linearray['metadata']['pagination'] = $pageList;

    $sql = "select mapset_name, map_type, map_unit from mapset where mapset_uid = ?";
    if ($stmt = mysqli_prepare($mysqli, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $uid);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $mapset_name, $map_type, $map_unit);
        mysqli_stmt_fetch($stmt);
        $results["mapDbId"] = $uid;
        $results["name"] = $mapset_name;
        $results["type"] = $map_type;
        $results["unit"] = $map_unit;
        mysqli_stmt_close($stmt);
    } else {
        $results['metadata']['status'][] = array("code" => "sql error", "message" => "error connecting to database");
    }
    $sql = "select count(markers.marker_uid), max(end_position) ,chromosome
        from markers_in_maps, markers, map
        where markers_in_maps.marker_uid = markers.marker_uid
        AND map.map_uid = markers_in_maps.map_uid
        AND mapset_uid = $uid
        GROUP BY chromosome";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row = mysqli_fetch_row($res)) {
        $temp['linkageGroups'] = $row[2];
        $temp['numberMarkers'] = $row[0];
        $temp['maxPosition'] = $row[1];
        $results['linkageGroups'][] = $temp;
    }
    $linearray['result'] = $results;

    $return = json_encode($linearray);
    echo "$return";
} elseif ($action == "MapData") {
    $linearray['metadata']['status'] = null;
    $num_rows = 0;
    if (empty($uid_ary)) {
        $sql = "select markers.marker_uid, markers.marker_name, start_position, chromosome, arm
            from markers_in_maps, markers, map
            where markers_in_maps.marker_uid = markers.marker_uid
            AND map.map_uid = markers_in_maps.map_uid
            AND mapset_uid = ?";
        if ($stmt = mysqli_prepare($mysqli, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $uid);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            $num_rows = mysqli_stmt_num_rows($stmt);
            mysqli_stmt_close($stmt);
        }
    } else {
        foreach ($uid_ary as $chr) {
            $sql = "select markers.marker_uid, markers.marker_name, start_position, chromosome, arm
            from markers_in_maps, markers, map
            where markers_in_maps.marker_uid = markers.marker_uid
            AND map.map_uid = markers_in_maps.map_uid
            AND mapset_uid = ?
            AND chromosome = ?";
            if ($stmt = mysqli_prepare($mysqli, $sql)) {
                mysqli_stmt_bind_param($stmt, "is", $uid, $chr);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);
                $num_rows += mysqli_stmt_num_rows($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    }
    $tot_pag = ceil($num_rows / $pageSize);
    $pageList = array( "pageSize" => $pageSize, "currentPage" => 0, "totalCount" => $num_rows, "totalPages" => $tot_pag );
    $linearray['metadata']['pagination'] = $pageList;

    if (empty($uid_ary)) {
        $sql = "select markers.marker_uid, markers.marker_name, start_position, chromosome, arm
            from markers_in_maps, markers, map
            where markers_in_maps.marker_uid = markers.marker_uid
            AND map.map_uid = markers_in_maps.map_uid
            AND mapset_uid = ? 
            order BY chromosome, start_position";
        if ($currentPage == 0) {
            $sql .= " limit $pageSize";
        } else {
            $offset = $currentPage * $pageSize;
            if ($offset < 0) {
                $offset = 0;
            }
            $sql .= " limit $offset, $pageSize";
        }

        if ($stmt = mysqli_prepare($mysqli, $sql)) {
            mysqli_stmt_bind_param($stmt, 'i', $uid);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $marker_uid, $marker_name, $start_position, $chromosome, $arm);
            while (mysqli_stmt_fetch($stmt)) {
                 $temp2["markerDbId"] = (integer) $marker_uid;
                 $temp2["markerName"] = $marker_name;
                 $temp2["location"] = $start_position;
                 $temp2["linkageGroup"] = $chromosome;
                 $linearray['result']['data'][] = $temp2;
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        foreach ($uid_ary as $chr) {
            $sql = "select markers.marker_uid, markers.marker_name, start_position, chromosome, arm
            from markers_in_maps, markers, map
            where markers_in_maps.marker_uid = markers.marker_uid
            AND map.map_uid = markers_in_maps.map_uid
            AND mapset_uid = ? 
            AND chromosome = ?
            order by start_position";
            if ($stmt = mysqli_prepare($mysqli, $sql)) {
                mysqli_stmt_bind_param($stmt, 'is', $uid, $chr);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $marker_uid, $marker_name, $start_position, $chromosome, $arm);
                while (mysqli_stmt_fetch($stmt)) {
                    $temp2["markerId"] = (integer) $marker_uid;
                    $temp2["markerName"] = $marker_name;
                    $temp2["location"] = $start_position;
                    $temp2["linkageGroup"] = $chromosome;
                    $linearray['result']['data'][] = $temp2;
                }
                mysqli_stmt_close($stmt);
            }
        }
    }

    $return = json_encode($linearray);
    echo "$return";
} elseif ($action == "MapDataRange") {
    echo "MapDataRange\n";
} else {
    $return = json_encode($linearray);
    echo "$return";
}
