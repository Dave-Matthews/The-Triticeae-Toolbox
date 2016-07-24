<?php
/**
 * create-allele-byline.php
 * create 2D table where rows contain line names and columns contain markers
 * this script should be run whenever the alleles, genotyping_data, or markers table is modified
 *
 * PHP version 5
 *
 * @author  Clay Birkett <clb343@cornell.edu>
 * @license http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link    http://triticeaetoolbox.org/wheat/create-allele_byline.php
*/

require 'config.php';
require $config['root_dir'].'includes/bootstrap_curator.inc';
$mysqli = connecti();
set_time_limit(7200);  /* allow script up to 2 hours */

$exp_list = array();
$line_uid_list = array();
$line_name_list = array();

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

$max_lines=0;
$sql = "select line_record_uid, line_record_name from line_records order by line_record_uid";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
while ($row = mysqli_fetch_array($res)) {
    array_push($line_uid_list, $row[0]);
    array_push($line_name_list, $row[1]);
    $max_lines++;
}
echo "$max_lines lines\n";

/* select markers with genotype data */
$max_markers=0;
$sql = "select distinct genotyping_data.marker_uid, markers.marker_name from genotyping_data, markers where genotyping_data.marker_uid = markers.marker_uid order by genotyping_data.marker_uid";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
while ($row = mysqli_fetch_array($res)) {
    $marker_uid = $row[0];
    $marker_name = $row[1];
    $marker_list[$marker_uid] = $max_markers;
    $marker_name_list[$marker_uid] = $marker_name;
    $max_markers++;
}
echo "$max_markers markers\n";
$marker_uid_list_str = implode(',', $marker_list);
$marker_name_list_str = implode(',', $marker_name_list);
$sql = "DROP TABLE if exists temp_allele";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
$sql = "create TABLE temp_allele (marker_uid INT NOT NULL, marker_name VARCHAR(50), PRIMARY KEY (marker_uid))";
echo "$sql\n";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
$sql = "insert into temp_allele select distinct genotyping_data.marker_uid, markers.marker_name from genotyping_data, markers where genotyping_data.marker_uid = markers.marker_uid order by genotyping_data.marker_uid";
echo "$sql\n";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
$sql = "DROP TABLE IF EXISTS allele_byline_idx";
echo "$sql\n";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
$sql = "RENAME TABLE temp_allele to allele_byline_idx";
echo "$sql\n";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
echo "done rewriting allele_byine_idx\n";

// dem 20feb:
//  $sql = "create table temp_allele (line_record_uid INT NOT NULL, line_record_name VARCHAR(50), alleles varchar(15000), PRIMARY KEY (line_record_uid))";
/* for markers that have been measured more then once, use majority value. if there is no majority then report value as missing */

$sql = "create table temp_allele (line_record_uid INT NOT NULL, line_record_name VARCHAR(50), alleles MEDIUMTEXT, PRIMARY KEY (line_record_uid))";
echo "$sql\n";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
$empty = array_fill(0, $max_markers, '');
$k = 0;
echo "$max_lines lines\n";
for ($j=0; $j<$max_lines; $j++) {
    $line_uid = $line_uid_list[$j];
    $line_name = $line_name_list[$j];
    $allele = $empty;
    $sql = "select marker_uid, alleles from allele_cache where line_record_uid = $line_uid";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    $count = 0;
    $count_dup = 0;
    $dup = array();
    $dup1 = 0;  //count of duplicate markers within a line
    while ($row = mysqli_fetch_array($res)) {
        $marker_uid = $row[0];
        $loc = $marker_list[$marker_uid];
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
                //echo "$line_uid $loc $value $result\n";
                $allele[$loc] = $result;
            }
        }
        $string = implode(',', $allele);
        $length=strlen($string);
        $sql = "insert into temp_allele (line_record_uid, line_record_name, alleles) values ($line_uid, '$line_name', '$string')";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        //echo "$j $line_uid $line_name dup=$count_dup\n";
    }
}
$sql = "ALTER TABLE temp_allele add index (line_record_uid)";
echo "$sql\n";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
$sql = "DROP TABLE IF EXISTS allele_byline";
echo "$sql\n";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
$sql = "RENAME TABLE temp_allele to allele_byline";
echo "$sql\n";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
echo "done with table allele_byline\n";
