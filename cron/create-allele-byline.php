<?php
/**
 * create-allele-byline.php
 * create 2D table where rows contain line names and columns contain markers
 * this script should be run whenever the alleles, genotyping_data, or markers table is modified
 *
 * PHP version 5
 *
 * @category PHP
 * @package  T3
 * @author   Clay Birkett <claybirkett@gmail.com>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @version  GIT: 2
 * @link     http://triticeaetoolbox.org/wheat/downloads/downloads.php
*/

require 'config.php';
require $config['root_dir'].'includes/bootstrap_curator.inc';
connect();

echo "using database = $db_name\n";
$exp_list = array();
$line_uid_list = array();
$line_name_list = array();

$max_exp = 0;
$sql = "select experiment_uid from experiments order by experiment_uid";
$res = mysql_query($sql) or die(mysql_error());
while ($row = mysql_fetch_array($res)) {
    array_push($exp_list, $row[0]);
    $max_exp++;
}
echo "$max_exp experiments\n";

$max_lines=0;
$sql = "select line_record_uid, line_record_name from line_records order by line_record_uid";
$res = mysql_query($sql) or die(mysql_error());
while ($row = mysql_fetch_array($res)) {
    array_push($line_uid_list, $row[0]);
    array_push($line_name_list, $row[1]);
    $max_lines++;
}
echo "$max_lines lines\n";

/* select markers with genotype data */
$max_markers=0;
$sql = "select distinct genotyping_data.marker_uid, markers.marker_name from genotyping_data, markers where genotyping_data.marker_uid = markers.marker_uid order by genotyping_data.marker_uid";
$res = mysql_query($sql) or die(mysql_error());
while ($row = mysql_fetch_array($res)) {
    $marker_uid = $row[0];
    $marker_name = $row[1];
    $marker_list[$marker_uid] = $max_markers;
    $marker_name_list[$marker_uid] = $marker_name;
    $max_markers++;
}
echo "$max_markers markers\n";
$marker_uid_list_str = implode(',', $marker_list);
$marker_name_list_str = implode(',', $marker_name_list);
$sql = "create TABLE temp_allele (marker_uid INT NOT NULL, marker_name VARCHAR(50), PRIMARY KEY (marker_uid))";
echo "$sql\n";
$res = mysql_query($sql) or die(mysql_error());
$sql = "insert into temp_allele select distinct genotyping_data.marker_uid, markers.marker_name from genotyping_data, markers where genotyping_data.marker_uid = markers.marker_uid";
echo "$sql\n";
$res = mysql_query($sql) or die(mysql_error());
$sql = "DROP TABLE IF EXISTS allele_byline_idx";
echo "$sql\n";
$res = mysql_query($sql) or die(mysql_error());
$sql = "RENAME TABLE temp_allele to allele_byline_idx";
echo "$sql\n";
$res = mysql_query($sql) or die(mysql_error());
echo "done rewriting allele_byine_idx\n";

// dem 20feb:
//  $sql = "create table temp_allele (line_record_uid INT NOT NULL, line_record_name VARCHAR(50), alleles varchar(15000), PRIMARY KEY (line_record_uid))";
/* for markers that have been measured more then once, use majority value. if there is no majority then report value as missing */

$sql = "create table temp_allele (line_record_uid INT NOT NULL, line_record_name VARCHAR(50), alleles TEXT, PRIMARY KEY (line_record_uid))";
echo "$sql\n";
$res = mysql_query($sql) or die(mysql_error());
$empty = array_fill(0, $max_markers, '');
$k = 0;
echo "$max_lines lines\n";
for ($j=0; $j<$max_lines; $j++) { 
    $line_uid = $line_uid_list[$j];
    $line_name = $line_name_list[$j];
    $allele = $empty;
    $sql = "select marker_uid, alleles from allele_view where line_record_uid = $line_uid";
    $res = mysql_query($sql) or die(mysql_error());
    $count = 0;
    $dup = array();
    $dup1 = 0;	//count of duplicate markers within a line
    while ($row = mysql_fetch_array($res)) {
        $marker_uid = $row[0];
        $loc = $marker_list[$marker_uid];
        if ($allele[$loc] == '') {
            $allele[$loc] = $row[1];
        } else {
            if (isset($dup[$loc])) {
                $dup[$loc] .= $row[1] . ",";
            } else {
                $dup[$loc] = $allele[$loc] . "," . $row[1];
            }
        }
        $count++;
    }
    if ($count > 0) {
        $dup1 = count($dup);
        if ($dup1 > 0) {
            foreach ($dup as $loc => $value) {
                $duplicates = explode(',', $value);
                $cntaa = $cntbb = $cntab = $cntba = 0;
                foreach ($duplicates as $dup_allele) {
                    if ($dup_allele == 'AA') $cntaa++;
                    if ($dup_allele == 'BB') $cntbb++;
                    if ($dup_allele == 'AB') $cntab++;
                    if ($dup_allele == 'BA') $cntba++;
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
        $res = mysql_query($sql) or die(mysql_error());
        //echo "$j $line_uid $line_name dup=$dup1\n";
    }
}
$sql = "DROP TABLE IF EXISTS allele_byline";
echo "$sql\n";
$res = mysql_query($sql) or die(mysql_error());
$sql = "RENAME TABLE temp_allele to allele_byline";
echo "$sql\n";
$res = mysql_query($sql) or die(mysql_error());
echo "done with table allele_byline\n";
?>
