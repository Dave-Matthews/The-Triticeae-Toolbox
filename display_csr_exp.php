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

  $count = 0;
  $sql = "select * from csr_measurement where experiment_uid =$experiment_uid";
  $res = mysql_query($sql) or die (mysql_error());
  echo "<h2>Field Book for $trial_code</h2>\n";
  echo "<table>";
  echo "<tr><td>Upwelling/Downwelling<td>Measurement date<td>Growth Stage<td>Start time<td>Stop time<td>";
  while ($row = mysql_fetch_assoc($res)) {
    $radiation_dir = $row["radiation_dir_uid"];
    $measure_date = $row["measure_date"];
    $growth_stage = $row["growth_stage"];
    $start_time = $row["start_time"];
    $end_time = $row["end_time"];
    $integration_time = $row["integration_gime"];
    $weather = $row["weather"];
    $spect_sys_uid = $row["spect_sys_uid"];
    $height_from_canopy = $row["height_from_canopy"];
    echo "<tr><td>$radiation_dir<td>$measer_date<td>$growth_stage<td>$start_time<td>$end_time\n";
    $count++;
  }
  echo "</table>";

