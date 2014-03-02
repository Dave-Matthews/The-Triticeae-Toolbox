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
 
  //get list of trials
  $sql = "select distinct(e.trial_code)
  from allele_conflicts a, line_records l, markers m, experiments e
  where a.line_record_uid = l.line_record_uid
  and a.marker_uid = m.marker_uid
  and a.experiment_uid = e.experiment_uid
  and l.line_record_uid = $uid";
  $result = mysql_query($sql) or die(mysql_error());
  while ($row=mysql_fetch_row($result)) {
    $trial = $row[0];
    $empty[$trial] = "";
  }
 
  $sql = "select l.line_record_name, m.marker_name, a.alleles, e.trial_code
  from allele_conflicts a, line_records l, markers m, experiments e
  where a.line_record_uid = l.line_record_uid
  and a.marker_uid = m.marker_uid
  and a.experiment_uid = e.experiment_uid
  and a.alleles != '--'
  and l.line_record_uid = $uid
  order by m.marker_name";
  $result = mysql_query($sql) or die(mysql_error());
  $count = 0;
  $prev = "";
  echo "Conflicts for line $name_list[$uid]<br>\n";
  echo "<table>\n";
  echo "<tr><td>marker name\n";
  foreach ($empty as $trial=>$allele) {
      echo "<td>$trial";
  }
  while ($row=mysql_fetch_row($result)) {
      $line_name = $row[0];
      $marker_name = $row[1];
      $alleles = $row[2];
      $trial = $row[3];
      if ($marker_name == $prev) {
        $allele_ary[$trial] = $alleles;
      } else {
        if ($count > 0) {
          echo "<tr><td>$prev";
          foreach ($allele_ary as $t1=>$a) {
              echo "<td>$a";
          }
          echo "\n";
        }
        $prev = $marker_name;
        $allele_ary = $empty;
        $allele_ary[$trial] = $alleles;
        $count++;
      }
  }
} else {
    echo "<table>";
    echo "<tr><td>line name<td>size<td>conflicts<td>percent\n";
    $sql = "select line_record_uid, count(line_record_uid) as temp from allele_conflicts group by line_record_uid order by temp DESC limit 100";
    $result = mysql_query($sql) or die(mysql_error());
    while ($row=mysql_fetch_row($result)) {
       $uid = $row[0];
       $count = $row[1];
       $total = 0;
       $sql = "select alleles from allele_byline where line_record_uid = $uid";
       $result2 = mysql_query($sql) or die(mysql_error());
       if ($row2=mysql_fetch_row($result2)) {
           $alleles = $row2[0];
           $outarray = explode(',', $alleles);
           foreach ($outarray as $allele) {
               if (preg_match("/[AB]/", $allele)) {
                   $total++;
               }
           }
       }
       $perc = round(100*$count/$total,2);
       echo "<tr><td><a href=genotyping/sum_lines.php?uid=$uid>$name_list[$uid]</a><td>$total<td>$count<td>$perc\n";
    }
}
echo "</table>";
