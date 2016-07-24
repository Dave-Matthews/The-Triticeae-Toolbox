<?php

require 'config.php';
require $config['root_dir'] . 'includes/bootstrap.inc';

$mysqli = connecti();

  global $config;
  require $config['root_dir'] . 'theme/admin_header.php';
  if (isset($_GET['uid'])) {
    $uid = $_GET['uid'];
  } else {
    die("Error - no experiment found<br>\n");
  }
  $sql = "select trial_code from experiments, csr_measurement where experiments.experiment_uid = csr_measurement.experiment_uid and measurement_uid = $uid";
  $res = mysqli_query($mysqli, $sql) or die (mysqli_error($mysqli));
  if ($row = mysqli_fetch_assoc($res)) {
    $trial_code = $row["trial_code"];
  } else {
    die("Error - invalid uid $uid<br>\n");
  }

  $sql = "select * from csr_measurement_rd";
  $res = mysqli_query($mysqli, $sql) or die (mysqli_error($mysqli));
  while ($row = mysqli_fetch_assoc($res)) {
    $rd_uid = $row["radiation_dir_uid"];
    $direction = $row["direction"];
    $dir_list[$rd_uid] = $direction;
  }

  $sql = "select * from csr_system";
  $res = mysqli_query($mysqli, $sql) or die (mysqli_error($mysqli));
  while ($row = mysqli_fetch_assoc($res)) {
    $sy_uid = $row["system_uid"];
    $name = $row["system_name"];
    $spect_list[$sy_uid] = $name;
  }

  $count = 0;
  $sql = "select * from csr_measurement where measurement_uid = $uid";
  $res = mysqli_query($mysqli, $sql) or die (mysqli_error($mysqli));
  echo "<h2>CSR Annotation for $trial_code</h2>\n";
  echo "<table>";
  while ($row = mysqli_fetch_assoc($res)) {
    $rad_dir_uid = $row["radiation_dir_uid"];
    $measure_date = $row["measure_date"];
    $growth_stage = $row["growth_stage"];
    $growth_stage_name = $row["growth_stage_name"];
    $start_time = $row["start_time"];
    $end_time = $row["end_time"];
    $integration_time = $row["integration_time"];
    $weather = $row["weather"];
    $spect_sys_uid = $row["spect_sys_uid"];
    $num_measurements = $row["num_measurements"];
    $height_from_canopy = $row["height_from_canopy"];
    $incident_adj = $row["incident_adj"];
    $comments = $row["comments"];

    $spect_sys = $spect_list[$spect_sys_uid];
    $rad_dir = $dir_list[$rad_dir_uid];
    echo "<tr><td>Upwelling/Downwelling<td>$rad_dir";
    echo "<tr><td>Measurement date<td>$measure_date";
    echo "<tr><td>Growth stage<td><a href=http://plantontology.org/amigo/go.cgi?view=details&search_constraint=terms&query=$growth_stage target=_blank>$growth_stage</a>";
    echo "<tr><td>Growth stage name<td>$growth_stage_name";
    echo "<tr><td>Start time<td>$start_time";
    echo "<tr><td>Stop time<td>$end_time";
    echo "<tr><td>Integration time<td>$integration_time";
    echo "<tr><td>Weather<td>$weather";
    echo "<tr><td>Spect Sys<td>$spect_sys";
    echo "<tr><td>Number of<br>measurements<td>$num_measurements";
    echo "<tr><td>Height from<br>canopy<td>$height_from_canopy";
    echo "<tr><td>Incident<br>adjustment<td>$incident_adj";
    echo "<tr><td>Comments<td>$comments\n";
    $count++;
  }
  echo "</table>";

