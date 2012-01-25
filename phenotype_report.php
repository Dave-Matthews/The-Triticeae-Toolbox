<?php
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/admin_header.php');
connect();
?>

<div id="primaryContentContainer">
  <div id="primaryContent">
  <h1>Phenotype data by year</h1>
  <div class="section">
  <table>
  <tr><th>Trait</th><th colspan=4>Experiments</th><th></th><th colspan=4>Data Points</th>
  <tr><th></th>

<?php
// Years that have data
$res = mysql_query("select distinct experiment_year from experiments order by experiment_year");
while ($row = mysql_fetch_row($res)) 
  $years[] = $row[0];
foreach ($years as $y) print "<th>$y</th>";
print "<th></th>";
foreach ($years as $y) print "<th>$y</th>";

// Traits that have data
$res = mysql_query("select distinct phenotypes_name
from tht_base, experiments, phenotypes, phenotype_data
where experiments.experiment_uid = tht_base.experiment_uid
and phenotype_data.tht_base_uid = tht_base.tht_base_uid
and phenotypes.phenotype_uid = phenotype_data.phenotype_uid
order by phenotypes_name");
while ($row = mysql_fetch_row($res)) 
  $traits[] = $row[0];

// Count of experiments and datapoints for each
foreach ($traits as $t) {
  print "<tr><td>$t</td>";
  foreach ($years as $y) {
    $res = mysql_query("select 
count(distinct(experiments.experiment_uid)),
count(tht_base.tht_base_uid)
from tht_base, experiments, phenotypes, phenotype_data
where experiments.experiment_uid = tht_base.experiment_uid
and phenotype_data.tht_base_uid = tht_base.tht_base_uid
and phenotypes.phenotype_uid = phenotype_data.phenotype_uid
and phenotypes_name = '$t'
and experiment_year = '$y'") or die(mysql_error());
    $row = mysql_fetch_row($res);
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
include($config['root_dir'].'theme/footer.php'); ?>