<?php
/**
 * create-allele-bymarker.php
 * create 2D table where rows contain marker names and columns contain lines
 *
 * PHP version 5
 *
 * @author  Clay Birkett <clb343@cornell.edu>
 * @license http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link    http://triticeaetoolbox.org/wheat/cron/create-allele-bymarker.php
*/

require 'config.php';
require $config['root_dir'].'includes/bootstrap_curator.inc';
$mysqli = connecti();
set_time_limit(7200);  /* allow script up to 2 hours */

$exp_list = array();
$marker_uid_list = array();
$marker_name_list = array();

$sql = "SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));

$max_exp = 0;
$sql = "select experiment_uid from experiments order by experiment_uid";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
while ($row = mysqli_fetch_array($res)) {
    array_push($exp_list, $row[0]);
    $max_exp++;
}
echo "$max_exp experiments\n";

$max_markers=0;
$sql = "select marker_uid, marker_name from markers order by marker_uid";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
while ($row = mysqli_fetch_array($res)) {
    array_push($marker_uid_list, $row[0]);
    array_push($marker_name_list, $row[1]);
    $max_markers++;
}
echo "$max_markers markers\n";

/* select markers with genotype data */
$max_lines=0;
$sql = "select line_records.line_record_uid, line_records.line_record_name from line_records order by line_record_uid";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
while ($row = mysqli_fetch_array($res)) {
    $line_record_uid = $row[0];
    $line_record_name = $row[1];
    $line_list[$line_record_uid] = $max_lines;
    $line_name_list[$line_record_uid] = $line_record_name;
    $max_lines++;
}
echo "$max_lines lines\n";
$marker_uid_list_str = implode(',', $line_list);
$marker_name_list_str = implode(',', $line_name_list);
$sql = "DROP TABLE if exists temp_allele";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
$sql = "create TABLE temp_allele (line_record_uid INT NOT NULL, line_record_name VARCHAR(50), PRIMARY KEY (line_record_uid))";
echo "$sql\n";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
$sql = "insert into temp_allele select line_records.line_record_uid, line_records.line_record_name from line_records order by line_record_uid";
echo "$sql\n";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
$sql = "DROP TABLE IF EXISTS allele_bymarker_idx";
echo "$sql\n";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
$sql = "RENAME TABLE temp_allele to allele_bymarker_idx";
echo "$sql\n";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
echo "done rewriting allele_bymarker_idx\n";

// dem 20feb:
//  $sql = "create table temp_allele (line_record_uid INT NOT NULL, line_record_name VARCHAR(50), alleles varchar(15000), PRIMARY KEY (line_record_uid))";
/* for markers that have been measured more then once, use majority value. if there is no majority then report value as missing */

$sql = "create table temp_allele (marker_uid INT NOT NULL, marker_name VARCHAR(50), alleles MEDIUMTEXT, PRIMARY KEY (marker_uid))";
echo "$sql\n";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
$empty = array_fill(0, $max_lines, '');
$k = 0;
echo "$max_markers markers\n";
for ($j=0; $j<$max_markers; $j++) {
    $marker_uid = $marker_uid_list[$j];
    $marker_name = $marker_name_list[$j];
    $allele = $empty;
    $sql = "select line_record_uid, alleles from allele_cache where marker_uid = $marker_uid";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    $count = 0;
    $count_dup = 0;
    $dup = array();
    $dup1 = 0;    //count of duplicate markers within a line
    while ($row = mysqli_fetch_array($res)) {
        $line_record_uid = $row[0];
        $loc = $line_list[$line_record_uid];
        if ($allele[$loc] == '') {
            $allele[$loc] = $row[1];
        } else {
            $count_dup++;
            if (isset($dup[$loc])) {
                $dup[$loc] .= "," . $row[1];
            } else {
                $dup[$loc] = $allele[$loc] . "," . $row[1];
            }
        }
        $count++;
    }
    if ($count > 0) {
        if ($count_dup > 0) {
            /* echo "$j duplicates found $count_dup\n"; */
            foreach ($dup as $loc => $value) {
                $duplicates = explode(',', $value);
                $cntaa = $cntbb = $cntab = $cntba = 0;
                foreach ($duplicates as $dup_allele) {
                    if ($dup_allele == 'AA') {
                        $cntaa++;
                    }
                    if ($dup_allele == 'BB') {
                        $cntbb++;
                    }
                    if ($dup_allele == 'AB') {
                        $cntab++;
                    }
                    if ($dup_allele == 'BA') {
                        $cntba++;
                    }
                }
                $max = 0;
                if ($cntaa == $max) {
                    $result = '--';
                }
                if ($cntaa > $max) {
                    $result = 'AA';
                    $max = $cntaa;
                }
                if ($cntbb == $max) {
                    $result = '--';
                }
                if ($cntbb > $max) {
                    $result = 'BB';
                    $max = $cntbb;
                }
                if ($cntab == $max) {
                    $result = '--';
                }
                if ($cntab > $max) {
                    $result = 'AB';
                    $max = $cntab;
                }
                if ($cntba == $max) {
                    $result = '--';
                }
                if ($cntba > $max) {
                    $result = 'BA';
                    $max = $cntba;
                }
                //echo "$marker_uid $loc $value $result\n";
                $allele[$loc] = $result;
            }
        }
        $string = implode(',', $allele);
        $length=strlen($string);
        $sql = "insert into temp_allele (marker_uid, marker_name, alleles) values ($marker_uid, '$marker_name', '$string')";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    }
}
$sql = "DROP TABLE IF EXISTS allele_bymarker";
echo "$sql\n";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
$sql = "RENAME TABLE temp_allele to allele_bymarker";
echo "$sql\n";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
echo "done with table allele_bymarker\n";
