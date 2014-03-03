<?php
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
connect();

include $config['root_dir'].'theme/admin_header.php';

$sql = "select marker_uid, marker_name from markers";
$result = mysql_query($sql) or die(mysql_error());
while ($row=mysql_fetch_row($result)) {
  $uid = $row[0];
  $name = $row[1];
  $name_list[$uid] = $name;
}

echo "<h2>Allele Conflicts by Markers</h2>\n";
if (isset($_GET['uid'])) {
  $uid = $_GET['uid'];

  //get list of trials
  $sql = "select distinct(e.trial_code)
  from allele_conflicts a, line_records l, markers m, experiments e
  where a.line_record_uid = l.line_record_uid
  and a.marker_uid = m.marker_uid
  and a.experiment_uid = e.experiment_uid
  and m.marker_uid = $uid";
  $result = mysql_query($sql) or die(mysql_error());
  $count = 0;
  while ($row=mysql_fetch_row($result)) {
    $count++;
    $trial = $row[0];
    $empty[$trial] = "";
    $empty_cnt[$trial] = $count;
  }

  $sql = "select l.line_record_name, m.marker_name, a.alleles, e.trial_code
  from allele_conflicts a, line_records l, markers m, experiments e
  where a.line_record_uid = l.line_record_uid
  and a.marker_uid = m.marker_uid
  and a.experiment_uid = e.experiment_uid
  and a.alleles != '--'
  and m.marker_uid = $uid
  order by l.line_record_name, m.marker_name, e.trial_code";
  $result = mysql_query($sql) or die(mysql_error());
  $count = 0;
  $prev = "";
  echo "Conflicts for marker $name_list[$uid]<br>\n";
  foreach ($empty_cnt as $trial=>$cnt) {
    echo "$cnt.$trial<br>\n";
  }
  echo "<table>\n";
  echo "<tr><td>Line name\n";
  foreach ($empty_cnt as $trial=>$cnt) {
      echo "<td>$cnt";
  }
  while ($row=mysql_fetch_row($result)) {
      $line_name = $row[0];
      $marker_name = $row[1];
      $alleles = $row[2];
      $trial = $row[3];
      if ($line_name == $prev) {
        $allele_ary[$trial] = $alleles;
      } else {
        if ($count > 0) {
          echo "<tr><td>$prev";
          foreach ($allele_ary as $t1=>$a) {
              echo "<td>$a";
          }
          echo "\n";
        }
        $prev = $line_name;
        $allele_ary = $empty;
        $allele_ary[$trial] = $alleles;
        $count++;
    }
    //  echo "<tr><td>$line_name<td>$alleles<td>$trial\n";
  }
} else {
    echo "Top 100 conflicts\n";
echo "<table>";
echo "<tr><td>marker name<td>conflicts\n";
$sql = "select marker_uid, count(marker_uid) as temp from allele_conflicts group by marker_uid order by temp DESC limit 100";
$result = mysql_query($sql) or die(mysql_error());
while ($row=mysql_fetch_row($result)) {
  $uid = $row[0];
  $count = $row[1];
  echo "<tr><td><a href=genotyping/sum_markers.php?uid=$uid>$name_list[$uid]</a><td>$count\n";
}
}
echo "</table></div>";
include $config['root_dir'].'theme/footer.php';
