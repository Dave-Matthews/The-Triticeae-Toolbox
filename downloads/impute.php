<?php
require 'config.php';
require_once $config['root_dir'].'includes/bootstrap.inc';
require_once $config['root_dir'].'theme/normal_header.php';

$mysqli = connecti();
$ref_genotype = "2014_HapMap_WEC";

?>

<h2>Imputation using experiment: 2014_HapMap_WEC</h2>

This tool uses <a href="http://faculty.washington.edu/browning/beagle/beagle.html">Beagle version 4.0</a> to impute genotype data using these inputs<br><br>
<?php
if (isset($_SESSION['geno_exps'])) {
  $geno_exps = $_SESSION['geno_exps'];
  $geno_exps = $geno_exps[0];
  $sql = "select trial_code from experiments where experiment_uid = $geno_exps";
  $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . $sql);
  if ($row = mysqli_fetch_array($res)) { 
      $trial_code = $row[0];
      echo "Reference experiment: $ref_genotype<br>\n";
      echo "Genotype experiment: $trial_code<br>\n";
      $sql = "select count(*) from allele_frequencies where experiment_uid = $geno_exps";
      $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . $sql);
      if ($row = mysqli_fetch_array($res)) {
          $count = $row[0];
          echo "Genotype experiment count: $count<br>\n";
      } else {
          echo "Genotype experiment count: none<br>\n";
      }
      $sql = "select count(distinct(marker_report_reference.marker1_uid)), count(allele_frequencies.marker_uid) from marker_report_reference, allele_frequencies
      where marker_report_reference.marker1_uid=allele_frequencies.marker_uid
      and experiment_uid = $geno_exps";
      $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . $sql);
      echo "Match to contigs:";
      if ($row = mysqli_fetch_array($res)) {
          $count = $row[0];
          echo "$count<br>\n";
       } else {
          echo "none<br>\n";
       }
  } else {
      echo "Experiment: unknown<br>\n";
  }
} else {
  echo "Please select a genotype experiment\n";
}
