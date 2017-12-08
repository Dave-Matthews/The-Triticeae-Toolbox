<?php

require 'config.php';
include $config['root_dir'] . 'includes/bootstrap.inc';

$mysqli = connecti();

global $config;
include $config['root_dir'] . 'theme/admin_header.php';
if (isset($_GET['uid'])) {
    $uid = $_GET['uid'];
} else {
    die("Error - no experiment found<br>\n");
}
$sql = "select trial_code from experiments, phenotype_plot_data where experiments.experiment_uid = phenotype_plot_data.experiment_uid and experiments.experiment_uid = $uid";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
if ($row = mysqli_fetch_assoc($res)) {
    $trial_code = $row["trial_code"];
} else {
    die("Error - invalid uid $uid<br>\n");
}

$sql = "select phenotype_uid,  phenotypes_name from phenotypes";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
while ($row = mysqli_fetch_assoc($res)) {
    $phen_uid = $row["phenotype_uid"];
    $name = $row["phenotypes_name"];
    $phen_list[$phen_uid] = $name;
}
 
$count = 0;
$sql = "select fieldbook.plot, phenotype_uid, value from phenotype_plot_data, fieldbook where phenotype_plot_data.plot_uid = fieldbook.plot_uid AND phenotype_plot_data.experiment_uid = $uid";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
echo "<h2>Phenotype results for $trial_code</h2>\n";
echo "<table>";
echo "<tr><td>count<td>plot<td>trait<td>value";
while ($row = mysqli_fetch_assoc($res)) {
    $uid = $row["phenotype_uid"];
    $plot = $row["plot"];
    $val = $row["value"];
    $name = $phen_list[$uid];
    echo "<tr><td>$count<td>$plot<td>$name<td>$val\n";
    $count++;
}
echo "</table>";
