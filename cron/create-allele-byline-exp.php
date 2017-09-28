<?php
/**
 * create-allele-byline-exp.php
 * create 2D table where rows contain lines and experiment
 * columns contain lines
 *
 * PHP version 5
 *
 * @author  Clay Birkett <clb343@cornell.edu>
 * @license http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link    http://triticeaetoolbox.org/wheat/cron/create-allele-byline.php
*/

require 'config.php';
require $config['root_dir'].'includes/bootstrap_curator.inc';
$mysqli = connecti();
ini_set('memory_limit', '2G');

if (isset($_SERVER["argv"])) {
    $fnames = $_SERVER["argv"];
    $experiment_uid = $fnames[1];
} elseif (isset($_GET['exp'])) {
    $experiment_uid = $_GET['exp'];
} else {
    die("Error: experiment not specified\n");
}

$sql = "select trial_code from experiments where experiment_uid = ?";
if ($stmt = mysqli_prepare($mysqli, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $experiment_uid);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $trial_code);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    echo "using experiment $trial_code<br>\n";
} else {
    die("invalid experiment_uid\n");
}

/*get list of experiments loaded so we know if new or update*/
$sql = "select experiment_uid from allele_byline_expidx";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
while ($row = mysqli_fetch_array($res)) {
    $uid = $row[0];
    $index_list[$uid] = 1;
}

/*get list of experiments loaded so we know if new or updates*/
$sql = "select experiment_uid, line_record_uid from allele_byline_exp";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
while ($row = mysqli_fetch_array($res)) {
    $exp_uid = $row[0];
    $line_record_uid = $row[1];
    $index = $exp_uid . $line_record_uid;
    $exp_list[$index] = 1;
}

$max_markers=0;
$marker_uid_list = array();
$marker_name_list = array();
$marker_list_loc = array();
$sql = "select distinct marker_uid, marker_name from allele_cache
        where experiment_uid = $experiment_uid
        order by marker_uid";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
while ($row = mysqli_fetch_array($res)) {
    $marker_uid = $row[0];
    $marker_name = $row[1];
    $marker_uid_list[] = $marker_uid;
    $marker_list_loc[$marker_uid] = $max_markers;
    $marker_name_list[] = $marker_name;
    $max_markers++;
}
echo "max_markers = $max_markers<br>\n";

$max_lines=0;
$line_uid_list = array();
$line_name_list = array();
$sql = "select distinct line_record_uid, line_record_name from allele_cache
    where experiment_uid = $experiment_uid";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
while ($row = mysqli_fetch_array($res)) {
    $line_record_uid = $row[0];
    $line_record_name = $row[1];
    $line_uid_list[$max_lines] = $line_record_uid;
    $line_name_list[$max_lines] = $line_record_name;
    $max_lines++;
}
echo "max_lines = $max_lines<br>\n";

if ($max_markers > 0) {
    $marker_uid_list_str = json_encode($marker_uid_list);
    $marker_name_list_str = json_encode($marker_name_list);
    if (isset($index_list[$experiment_uid])) {
        $sql = "update allele_byline_expidx set marker_index = '$marker_uid_list_str', marker_name_index = '$marker_name_list_str' where experiment_uid = $experiment_uid";
    } else {
        $sql = "insert into allele_byline_expidx (experiment_uid, marker_index, marker_name_index) values ($experiment_uid, '$marker_uid_list_str', '$marker_name_list_str')";
    }
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "\n$sql\n");
    echo "experiment = $experiment_uid $max_markers markers $max_lines lines $trial_code<br>\n";
    $empty = array_fill(0, $max_markers, '');
}

$k = 0;
$count_update = 0;
$count_new = 0;
for ($j=0; $j<$max_lines; $j++) {
    $line_uid = $line_uid_list[$j];
    $line_name = $line_name_list[$j];
    $allele = $empty;
    $sql = "select marker_uid, alleles from allele_cache where line_record_uid = $line_uid and experiment_uid = $experiment_uid";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
    $count = 0;
    while ($row = mysqli_fetch_array($res)) {
        $marker_uid = $row[0];
        $loc = $marker_list_loc[$marker_uid];
        $allele[$loc] = $row[1];
        $count++;
    }
    if ($count > 0) {
        $k++;
        $string = implode('\t', $allele);
        $length=strlen($string);
        if (isset($exp_list[$index])) {
            $sql = "update allele_byline_exp set alleles = \"$string\", count = $count  where experiment_uid = $experiment_uid and line_record_uid = $marker_uid";
            $count_update++;
        } else {
            $sql = "insert into allele_byline_exp (experiment_uid, line_record_uid, line_record_name, count, alleles) values ($experiment_uid, $line_uid, '$line_name', $count, '$string')";
            $count_new++;
        }
        echo "$sql<br>\n";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    } else {
        echo "skip $experiment_uid $marker_uid\n";
    }
}
echo "$k lines with data for $trial_code\n";
if ($count_new > 0) {
    echo "$count_new new entries\n";
}
if ($count_update > 0) {
    echo "$count_update updated\n";
}
