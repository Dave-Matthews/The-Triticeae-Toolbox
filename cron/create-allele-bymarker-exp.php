<?php
/**
 * create-allele-bymarker-exp.php
 * create 2D table where rows contain marker and experiment
 * columns contain lines 
 *
 * PHP version 5
 *
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/cron/create-allele-bymarker.php
*/

require 'config.php';
require $config['root_dir'].'includes/bootstrap_curator.inc';
connect();
ini_set('memory_limit', '2G');

$fnames = $_SERVER["argv"];
$experiment_uid = $fnames[1];

$sql = "select trial_code from experiments where experiment_uid = $experiment_uid";
$res = mysql_query($sql) or die(mysql_error());
if ($row = mysql_fetch_array($res)) {
    $trial_code = $row[0];
    echo "using experiment $trial_code\n";
} else {
    die("invalid trial");
}

/*get list of experiments loaded so we know if new or update*/
$sql = "select experiment_uid from allele_bymarker_expidx";
$res = mysql_query($sql) or die(mysql_error());
while ($row = mysql_fetch_array($res)) {
    $uid = $row[0];
    $index_list[$uid] = 1;
}

/*get list of experiments loaded so we know if new or updates*/
$sql = "select experiment_uid, marker_uid from allele_bymarker_exp_101";
$res = mysql_query($sql) or die(mysql_error());
while ($row = mysql_fetch_array($res)) {
    $exp_uid = $row[0];
    $marker_uid = $row[1];
    $index = $exp_uid . $marker_uid;
    $exp101_list[$index] = 1;
}

    $max_markers=0;
    $marker_uid_list = array();
    $marker_name_list = array();
    $marker_list_loc = array();
    $sql = "select distinct marker_uid, marker_name from allele_cache
        where experiment_uid = $experiment_uid
        order by marker_uid";
    $res = mysql_query($sql) or die(mysql_error());
    while ($row = mysql_fetch_array($res)) {
        $marker_uid = $row[0];
        $marker_name = $row[1];
        $marker_uid_list[] = $marker_uid;
        $marker_list_loc[$marker_uid] = $max_markers;
        $marker_name_list[] = $marker_name;
        $max_markers++;
    }
    echo "max_markers = $max_markers\n";

    $sql = "select marker_uid, A_allele, B_allele from markers";
    $res = mysql_query($sql) or die(mysql_error());
    while ($row = mysql_fetch_array($res)) {
        $marker_uid = $row[0];
        $allele = $row[1] . $row[2];
        if (isset($marker_list_loc[$marker_uid])) {
            $j = $marker_list_loc[$marker_uid];
            $marker_allele_list[$j] = $allele;
        }
    }
    $max_lines=0;
    $line_uid_list = array();
    $line_name_list = array();
    $line_list_loc = array();
    $sql = "select distinct line_record_uid, line_record_name from allele_cache
        where experiment_uid = $experiment_uid";
    $res = mysql_query($sql) or die(mysql_error());
    while ($row = mysql_fetch_array($res)) {
        $line_record_uid = $row[0];
        $line_record_name = $row[1];
        $line_uid_list[$max_lines] = $line_record_uid;
        $line_list_loc[$line_record_uid] = $max_lines;
        $line_name_list[$max_lines] = $line_record_name;
        $max_lines++;
    }
    echo "max_lines = $max_lines\n";

    if ($max_markers > 0) {
        //$line_uid_list_str = implode(",", $line_uid_list);
        //$line_name_list_str = implode(",", $line_name_list);
        $line_uid_list_str = json_encode($line_uid_list);
        $line_name_list_str = json_encode($line_name_list);
        if (isset($index_list[$experiment_uid])) {
            $sql = "update allele_bymarker_expidx set line_index = \"$line_uid_list_str\", line_name_index = \"$line_name_list_str\" where experiment_uid = $experiment_uid";
        } else {
            $sql = "insert into allele_bymarker_expidx (experiment_uid, line_index, line_name_index) values ($experiment_uid, \"$line_uid_list_str\",\"$line_name_list_str\")";
        }
        $res = mysql_query($sql) or die(mysql_error() . $sql);
        //echo "$sql\n";
        echo "experiment = $experiment_uid markers = $max_markers lines =  $max_lines\n";
        $empty = array_fill(0, $max_lines, '');
    }
    $lookup_101 = array(
        'AA' => '1',
        'BB' => '-1',
        '--' => 'NA',
        'AB' => '0'
    );
    $k = 0;
    $count_update = 0;
    $count_new = 0;
    for ($j=0; $j<$max_markers; $j++) {
        $marker_uid = $marker_uid_list[$j];
        $marker_name = $marker_name_list[$j];
        $marker_allele = $marker_allele_list[$j];
        $lookup_actg = array(
            'AA' => substr($marker_allele,0,1) . substr($marker_allele,0,1),
            'BB' => substr($marker_allele,1,1) . substr($marker_allele,1,1),
            '--' => 'NN',
            'AB' => substr($marker_allele,0,1) . substr($marker_allele,1,1),
            'BA' => substr($marker_allele,1,1) . substr($marker_allele,0,1),
            '' => 'NN'
        );
        $alleles_101 = $empty;
        $alleles_ACTG = $empty;
        $sql = "select line_record_uid, alleles from allele_cache where marker_uid = $marker_uid and experiment_uid = $experiment_uid";
        $res = mysql_query($sql) or die(mysql_error());
        $count = 0;
        while ($row = mysql_fetch_array($res)) {
            $line_uid = $row[0];
            $allele = $row[1];
            $allele_101 = $lookup_101[$allele];
            $allele_ACTG = $lookup_actg[$allele];
            $loc = $line_list_loc[$line_uid];
            $alleles_101[$loc] = $allele_101;
            $alleles_ACTG[$loc] = $allele_ACTG;
            $count++;
        }
        if ($count > 0) {
            $k++;
            $index = $experiment_uid . $marker_uid;
            $string = implode(',', $alleles_101);
            if (isset($exp101_list[$index])) {
                $sql = "update allele_bymarker_exp_101 set alleles = \"$string\" where experiment_uid = $experiment_uid and marker_uid = $marker_uid";
                $count_update++;
            } else {
                $sql = "insert into allele_bymarker_exp_101 (experiment_uid, marker_uid, marker_name, alleles) values ($experiment_uid, $marker_uid, '$marker_name', '$string')";
                $count_new++;
            }
            $res = mysql_query($sql) or die(mysql_error());
            $string = implode(',', $alleles_ACTG);
            if (isset($exp101_list[$index])) {
                $sql = "update allele_bymarker_exp_ACTG set alleles = \"$string\" where experiment_uid = $experiment_uid and marker_uid = $marker_uid";
            } else {
                $sql = "insert into allele_bymarker_exp_ACTG (experiment_uid, marker_uid, marker_name, alleles) values ($experiment_uid, $marker_uid, '$marker_name', '$string')";
            }
            $res = mysql_query($sql) or die(mysql_error());
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

