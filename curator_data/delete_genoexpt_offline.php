<?php
/**
 * Delete Genotype Experiment
 * PHP version 5.3
 */

$progPath = realpath(dirname(__FILE__).'/../').'/';

require "$progPath" . "includes/bootstrap_curator.inc";
require_once "$progPath" . "includes/email.inc";

ini_set('mysql.connect_timeout', '0');

$num_args  = $_SERVER["argc"];
$fnames    = $_SERVER["argv"];
$uid       = $fnames[1];
$emailAddr = $fnames[2];

if ($num_args != 3) {
    die("Error: missing arguement $num_args $uid $emailAddr");
}

if (preg_match("/[A-Za-z0-9]+@[A-Za-z0-9]+/", $emailAddr)) {
    echo "sending email to $emailAddr\n";
} else {
    die("Error: missing email\n");
}

$mysqli = connecti();

// Order necessary: first delete alleles, then genotyping_data, then tht_base, then experiment.
$sql = "select tht_base_uid from tht_base where experiment_uid = $uid";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<p>Query was: $sql");
while ($row = mysqli_fetch_array($res)) {
    $tht_base_uid = $row['tht_base_uid'];
    echo "deleting alleles tht_base_uid = $tht_base_uid\n";
    $sql  = "select genotyping_data_uid from genotyping_data where tht_base_uid = $tht_base_uid";
    $res2 = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<p>Query was: $sql");
    while ($row2 = mysqli_fetch_array($res2)) {
        $genotyping_data_uid = $row2['genotyping_data_uid'];
        $sql = "delete from alleles where genotyping_data_uid = $genotyping_data_uid";
        $r = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<p>Query was: $sql");
    }
}

echo "deleting from allele_bymarker_exp_101\n";
$sql = "delete from allele_bymarker_exp_101 where experiment_uid = $uid";
$res = mysqli_query($mysqli, $sql);
if (!$res) {
    echo "mysqli_error($mysqli)";
}

echo "deleting from allele_bymarker_exp_ACTG\n";
$sql = "delete from allele_bymarker_exp_ACTG where experiment_uid = $uid";
$res = mysqli_query($mysqli, $sql);
if (!$res) {
    echo "mysqli_error($mysqli)";
}

echo "deleting from allele_bymarker_expidx\n";
$sql = "delete from allele_bymarker_expidx where experiment_uid = $uid";
$res = mysqli_query($mysqli, $sql);
if (!$res) {
    echo "mysqli_error($mysqli)";
}


$sql = "select tht_base_uid from tht_base where experiment_uid = $uid";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error() . "<p>Query was: $sql");
while ($row = mysqli_fetch_array($res)) {
    $tht_base_uid = $row['tht_base_uid'];
    echo "deleting genotype_data tht_base_uid = $tht_base_uid\n";
    $sql = "delete from genotyping_data where tht_base_uid = $tht_base_uid";
    $r = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<p>Query was: $sql");
}

$sql = "delete from tht_base where experiment_uid = $uid";
$r = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<p>Query was: $sql");
$sql = "delete from genotype_experiment_info where experiment_uid = $uid";
$r = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<p>Query was: $sql");
$sql = "delete from experiments where experiment_uid = $uid";
$r = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<p>Query was: $sql");

$message = "Finished deleting genotype experiment uid = $uid";
echo "$message\n";
mail($emailAddr, "Delete genotype trial", $message);
