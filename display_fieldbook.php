<?php

require 'config.php';
include($config['root_dir'] . 'includes/bootstrap.inc');

$mysqli = connecti();

global $config;
include($config['root_dir'] . 'theme/admin_header.php');
if (isset($_GET['uid'])) {
  $experiment_uid = intval($_GET['uid']);
} else {
  die("Error - no experiment found<br>\n");
}
$sql = "select trial_code from experiments where experiment_uid = $experiment_uid";
$res = mysqli_query($mysqli, $sql) or die (mysqli_error($mysqli));
if ($row = mysqli_fetch_assoc($res)) {
  $trial_code = $row["trial_code"];
} else {
  die("Error - invalid uid $experiment_uid<br>\n");
}

  //get line names
  $sql = "select line_record_uid, line_record_name from line_records";
  $res = mysqli_query($mysqli, $sql) or die (mysqli_error($mysqli));
  while ($row = mysqli_fetch_assoc($res)) {
    $uid = $row["line_record_uid"];
    $line_name = $row["line_record_name"];
    $line_list[$uid] = $line_name;
  }

  echo "<h2>Field Book for $trial_code</h2>\n";

  $sql = "select fieldbook_file_name from fieldbook_info where experiment_uid = $experiment_uid";
  $res = mysqli_query($mysqli, $sql) or die (mysqli_error($mysqli));
  if ($row = mysqli_fetch_array($res)) {
      $raw_file = $row[0];
      echo "<a href=\"$raw_file\">Fieldbook file (Download)</a>";
  } else {
      echo "Error: could not find import file for this fieldbook\n";
  }

  
  $count = 0;
  $sql = "select * from fieldbook where experiment_uid = $experiment_uid order by plot";
  $res = mysqli_query($mysqli, $sql) or die (mysqli_error($mysqli));

  echo "<br><br><table>";
  echo "<tr><td>plot<td>line_name<td>row<td>column<td>entry<td>replication<td>block<td>subblock<td>treatment<td>block_tmt<td>subblock_tmt<td>check<td>Field_ID<td>note";
  while ($row = mysqli_fetch_assoc($res)) {
    $expr = $row["experiment_uid"];
    $range = $row["range_id"];
    $plot = $row["plot"];
    $entry = $row["entry"];
    $line_uid = $row["line_uid"];
    $field_id = $row["field_id"];
    $note = $row["note"];
    $rep = $row["replication"];
    $block = $row["block"];
    $subblock = $row["subblock"];
    $row_id = $row["row_id"];
    $col_id = $row["column_id"];
    $treatment = $row["treatment"];
    $main_plot_tmt = $row["block_tmt"];
    $subblock_tmt = $row["subblock_tmt"];
    $check = $row["check_id"];
    echo "<tr><td>$plot<td>$line_list[$line_uid]<td>$row_id<td>$col_id<td>$entry<td>$rep<td>$block<td>$subblock<td>$treatment<td>$main_plot_tmt<td>$subblock_tmt<td>$check<td>$field_id<td>$note\n";
    $count++;
  }
  echo "</table>";

