<?php
/**
 * brapi/v1/germplasm.php, DEM jul 2014
 * Deliver Line names according to http://docs.breeding.apiary.io/
 *
 */

require '../../includes/bootstrap.inc';
$mysqli = connecti();

$self = $_SERVER['PHP_SELF'];
$script = $_SERVER["SCRIPT_NAME"]."/";
$rest = str_replace($script, "", $self);
$rest = explode("/", $rest);
header("Content-Type: application/json");

if (!empty($rest[0])) {
    $lineuid = $rest[0];
} elseif (isset($_GET['germplasmDbId'])) {
    $lineuid = $_GET['germplasmDbId'];
}

// Extract the URI's querystring, ie "name={name}".
if (isset($_GET['matchMethod'])) {
    $matchMethod = $_GET['matchMethod'];
} else {
    $matchMethod = null;
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

$r['metadata']['status'] = array();
$r['metadata']['datafiles'] = array();

$sql = "select value from settings where name = \"species\"";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
if ($row = mysqli_fetch_array($res)) {
    $species = $row[0];
} else {
    $species = null;
}

// Is there a requiest for line_record_uid?
if (isset($lineuid)) {
    $sql = "select line_record_name, pedigree_string from line_records where line_record_uid = ?";
    if ($stmt = mysqli_prepare($mysqli, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $lineuid);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $line_record_name, $pedigree);
        if (mysqli_stmt_fetch($stmt)) {
            $response["germplasmDbId"] = $lineuid;
            $response['defaultDisplayName'] = $line_record_name;
            $response['accessionNumber'] = null;
            $response['germplasmName'] = $line_record_name;
            $response['germplasmPUI'] = null;
            $response['pedigree'] = null;
            $response['seedSource'] = null;
            $response['synonyms'] = null;
            $response['commonCropName'] = $species;
            $response['instituteCode'] = "";
        } else {
            $response = null;
            $r['metadata']['status'][] = array("code" => "not found", "message" => "germplasm id not found");
        }
        mysqli_stmt_close($stmt);
    }
    $sql = "select line_synonym_name from line_synonyms where line_record_uid = ?";
    $stmt = mysqli_prepare($mysqli, $sql);
    mysqli_stmt_bind_param($stmt, "i", $lineuid);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $line_synonyms);
    while (mysqli_stmt_fetch($stmt)) {
        $response['synonyms'][] = $line_synonyms;
    }
    mysqli_stmt_close($stmt);
    $sql = "select barley_ref_number from barley_pedigree_catalog_ref where line_record_uid = ?";
    $stmt = mysqli_prepare($mysqli, $sql);
    mysqli_stmt_bind_param($stmt, "i", $lineuid);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $barley_ref_number);
    if (mysqli_stmt_fetch($stmt)) {
        $response['accessionNumber'] = $barley_ref_number;
    }
    mysqli_stmt_close($stmt);
    $r['metadata']['pagination']['pageSize'] = 1;
    $r['metadata']['pagination']['currentPage'] = $currentPage;
    $r['metadata']['pagination']['totalCount'] = 1;
    $r['metadata']['pagination']['totalPages'] = 1;
    $r['result'] = $response;
    header("Access-Control-Allow-Origin: *");
    echo json_encode($r);
} elseif (!empty($_GET['germplasmName'])) {
    // "Germplasm ID by Name".  URI is germplasm?name={name}
    $linename = $_GET['germplasmName'];
    if ($matchMethod == "wildcard") {
        $sql = "select line_record_uid, line_record_name, pedigree_string from line_records where line_record_name like ?";
        $linename = "%" . $linename . "%";
    } else {
        $sql = "select line_record_uid, line_record_name, pedigree_string from line_records where line_record_name = ?";
    }

    //first query all data
    if ($stmt = mysqli_prepare($mysqli, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $linename);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $num_rows = mysqli_stmt_num_rows($stmt);
        mysqli_stmt_close($stmt);
    } else {
        die(mysqli_error($mysqli));
    }
    if ($currentPage == 0) {
        $sql .= " limit $pageSize";
    } else {
        $offset = $currentPage * $pageSize;
        if ($offset < 0) {
            $offset = 0;
        }
        $sql .= " limit $offset, $pageSize";
    }
    //echo "$linename $sql\n";
    $response = null;
    $r['metadata']['status'] = null;
    if ($stmt = mysqli_prepare($mysqli, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $linename);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        mysqli_stmt_bind_result($stmt, $lineuid, $line_record_name, $pedigree);
        while (mysqli_stmt_fetch($stmt)) {
            $temp['germplasmDbId'] = $lineuid;
            $temp['defaultDisplayName'] = $line_record_name;
            $temp['germplasmName'] = $line_record_name;
            $temp['accessionNumber'] = null;
            $temp['germplasmPUI'] = null;
            $temp['pedigree'] = null;
            $temp['seedSource'] = null;
            $temp['synonyms'] = array();
            $response[] = $temp;
        }
        mysqli_stmt_close($stmt);
        if (empty($response)) {
            $r['metadata']['status'][] = array("code" => "not found", "message" => "germplasm name not found");
        }
        foreach ($response as $key => $item) {
            $lineuid = $item['germplasmDbId'];
            $sql = "select line_synonym_name from line_synonyms where line_record_uid = $lineuid";
            $temp = array();
            $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
            while ($row = mysqli_fetch_row($res)) {
                $response[$key]['synonyms'][] = $row[0];
            }

            $sql = "select barley_ref_number from barley_pedigree_catalog_ref where line_record_uid = $lineuid";
            $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
            if ($row = mysqli_fetch_row($res)) {
                $response[$key]['accessionNumber'] = $row[0];
            }
        }
    }
    $r['metadata']['pagination']['pageSize'] = $pageSize;
    $r['metadata']['pagination']['currentPage'] = $currentPage;
    $r['metadata']['pagination']['totalCount'] = $num_rows;
    $r['metadata']['pagination']['totalPages'] = ceil($num_rows / $pageSize);
    $r['result']['data'] = $response;
    header("Access-Control-Allow-Origin: *");
    echo json_encode($r);
} else {
    $sql = "select line_record_uid, line_record_name, pedigree_string from line_records";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    $num_rows = mysqli_num_rows($res);

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
    while ($row = mysqli_fetch_array($res)) {
        $temp['germplasmDbId'] = $row[0];
        $temp['defaultDisplayName'] = $row[1];
        $temp['accessionNumber'] = null;
        $temp['germplasmName'] = $row[1];
        $temp['germplasmPUI'] = null;
        if (empty($row[2])) {
            $temp['pedigree'] = null;
        } else {
            $temp['pedigree'] = htmlentities($row[2]);
        }
        $temp['seedSource'] = null;
        $temp['synonyms'] = array();
        $temp['commonCropName'] = $species;
        $temp['instituteCode'] = "";
        $response[] = $temp;
    }
  
    foreach ($response as $key => $item) {
        $lineuid = $item['germplasmDbId'];
        $sql = "select line_synonym_name from line_synonyms where line_record_uid = $lineuid";
        //echo "$key $sql\n";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        while ($row = mysqli_fetch_row($res)) {
            $response[$key]['synonyms'][] = $row[0];
        }

        $sql = "select barley_ref_number from barley_pedigree_catalog_ref where line_record_uid = $lineuid";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        if ($row = mysqli_fetch_row($res)) {
            $response[$key]['accessionNumber'] = $row[0];
        }
    }
        
    $r['metadata']['pagination']['pageSize'] = $pageSize;
    $r['metadata']['pagination']['currentPage'] = $currentPage;
    $r['metadata']['pagination']['totalCount'] = $num_rows;
    $r['metadata']['pagination']['totalPages'] = ceil($num_rows / $pageSize);
    $r['result']['data'] = $response;
    header("Access-Control-Allow-Origin: *");
    echo json_encode($r);
}
