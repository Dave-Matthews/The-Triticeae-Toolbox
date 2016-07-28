<?php
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/admin_header.php');
$mysqli = connecti();
?>

<div id="primaryContentContainer">
  <div id="primaryContent">
  <h1>Phenotype data by year</h1>
  <div class="section">
  <table>

<?php
// Years that have data
$res = mysqli_query($mysqli, "select distinct experiment_year from experiments where experiment_type_uid=1 order by experiment_year");
while ($row = mysqli_fetch_row($res)) 
  $years[] = $row[0];
$numyrs = count($years);
print "<tr><th>Trait</th><th colspan=$numyrs>Experiments</th><th></th><th colspan=$numyrs>Data Points</th>";
print "<tr><th></th>";
foreach ($years as $y) print "<th>$y</th>";
print "<th></th>";
foreach ($years as $y) print "<th>$y</th>";

// Traits that have data
$res = mysqli_query($mysqli, "select distinct phenotypes_name
from tht_base, experiments, phenotypes, phenotype_data
where experiments.experiment_uid = tht_base.experiment_uid
and phenotype_data.tht_base_uid = tht_base.tht_base_uid
and phenotypes.phenotype_uid = phenotype_data.phenotype_uid
order by phenotypes_name");
while ($row = mysqli_fetch_row($res)) 
  $traits[] = $row[0];

// Count of experiments and datapoints for each
foreach ($traits as $t) {
  print "<tr><td>$t</td>";
  foreach ($years as $y) {
    $res = mysqli_query($mysqli, "select 
count(distinct(experiments.experiment_uid)),
count(tht_base.tht_base_uid)
from tht_base, experiments, phenotypes, phenotype_data
where experiments.experiment_uid = tht_base.experiment_uid
and phenotype_data.tht_base_uid = tht_base.tht_base_uid
and phenotypes.phenotype_uid = phenotype_data.phenotype_uid
and phenotypes_name = '$t'
and experiment_year = '$y'") or die(mysqli_error($mysqli));
    $row = mysqli_fetch_row($res);
    $exp[$y] = $row[0];
    $dp[$y] = $row[1];
  }
  foreach ($years as $y)
    print "<td style=text-align:right>$exp[$y]</td>";
  print "<td></td>";
  foreach ($years as $y)
    print "<td style=text-align:right>$dp[$y]</td>";
}
print "</table>";

print "</div></div></div>";
$footer_div=1;
include $config['root_dir'].'theme/footer.php'; ?>
