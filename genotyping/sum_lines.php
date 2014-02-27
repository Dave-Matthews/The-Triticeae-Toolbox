<?php
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
connect();

include $config['root_dir'].'theme/admin_header.php';

$sql = "select line_record_uid, line_record_name from line_records";
$result = mysql_query($sql) or die(mysql_error());
while ($row=mysql_fetch_row($result)) {
  $uid = $row[0];
  $name = $row[1];
  $name_list[$uid] = $name;
}

echo "<h2>Allele Conflicts by Line</h2>\n";
if (isset($_GET['uid'])) {
  $uid = $_GET['uid'];
  $sql = "select l.line_record_name, m.marker_name, a.alleles, e.trial_code
  from allele_conflicts a, line_records l, markers m, experiments e
  where a.line_record_uid = l.line_record_uid
  and a.marker_uid = m.marker_uid
  and a.experiment_uid = e.experiment_uid
  and a.alleles != '--'
  and l.line_record_uid = $uid
  order by l.line_record_name, m.marker_name, e.trial_code";
  $result = mysql_query($sql) or die(mysql_error());
  echo "Conflicts for line $name_list[$uid]<br>\n";
  echo "<table>\n";
  echo "<tr><td>marker name<td>alleles<td>experiment\n";
  while ($row=mysql_fetch_row($result)) {
      $line_name = $row[0];
      $marker_name = $row[1];
      $alleles = $row[2];
      $trial = $row[3];
      echo "<tr><td>$marker_name<td>$alleles<td>$trial\n";
  }
} else {

echo "<table>";
echo "<tr><td>line name<td>count of conflicts\n";
$sql = "select line_record_uid, count(line_record_uid) as temp from allele_conflicts group by line_record_uid order by temp DESC limit 20";
$result = mysql_query($sql) or die(mysql_error());
while ($row=mysql_fetch_row($result)) {
  $uid = $row[0];
  $count = $row[1];
  echo "<tr><td><a href=genotyping/sum_lines.php?uid=$uid>$name_list[$uid]</a><td>$count\n";
}
}
echo "</table>";
