<?php

require 'config.php';
include($config['root_dir'] . 'includes/bootstrap.inc');

connect();

  global $config;
  include($config['root_dir'] . 'theme/admin_header.php');
  if (isset($_GET['uid'])) {
    $experiment_uid = $_GET['uid'];
  } else {
    die("Error - no experiment found<br>\n");
  }
  $sql = "select trial_code from experiments where experiment_uid = $experiment_uid";
  $res = mysql_query($sql) or die (mysql_error());
  if ($row = mysql_fetch_assoc($res)) {
    $trial_code = $row["trial_code"];
  } else {
    die("Error - invalid uid $uid<br>\n");
  }

  $sql = "select * from csr_measurement_rd";
  $res = mysql_query($sql) or die (mysql_error());
  while ($row = mysql_fetch_assoc($res)) {
    $uid = $row["radiation_dir_uid"];
    $direction = $row["direction"];
    $dir_list[$uid] = $direction;
  }

  $sql = "select * from csr_system";
  $res = mysql_query($sql) or die (mysql_error());
  while ($row = mysql_fetch_assoc($res)) {
    $uid = $row["system_uid"];
    $name = $row["system_name"];
    $spect_list[$uid] = $name;
  }

  $count = 0;
  $sql = "select * from csr_measurement where experiment_uid =$experiment_uid";
  $res = mysql_query($sql) or die (mysql_error());
  echo "<h2>CSR Annotation for $trial_code</h2>\n";
  echo "<table>";
  echo "<tr><td>Upwelling/Downwelling<td>Measurement date<td>Growth stage<td>Start time<td>Stop time<td>Integration time<td>weather<td>Spect Sys<td>Number of<br>measurements<td>Height from<br>canopy<td>Incident<br>adjustment<td>Commments";
  while ($row = mysql_fetch_assoc($res)) {
    $rad_dir_uid = $row["radiation_dir_uid"];
    $measure_date = $row["measure_date"];
    $growth_stage = $row["growth_stage"];
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
    echo "<tr><td>$rad_dir<td>$measure_date<td>$growth_stage<td>$start_time<td>$end_time<td>$integration_time<td>$weather<td>$spect_sys<td>$num_measurements<td>$height_from_canopy<td>$incident_adj<td>$comments\n";
    $count++;
  }
  echo "</table>";

