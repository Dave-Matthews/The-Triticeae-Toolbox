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

echo "Also see <a href=genotyping/sum_exp.php>conflicts by experiment</a>, <a href=genotyping/sum_markers.php>conflicts by marker</a>";
echo ", and <a href=genotyping/allele_conflicts.php>All Allele Conflicts</a>.<br><br>\n";

if (isset($_GET['uid'])) {
  $uid = $_GET['uid'];
  echo "<h3>Allele Conflicts for $name_list[$uid] between experiments</h2>\n";
 
  //get list of trials
  $sql = "select distinct(e.trial_code), e.experiment_uid
  from allele_conflicts a, line_records l, markers m, experiments e
  where a.line_record_uid = l.line_record_uid
  and a.marker_uid = m.marker_uid
  and a.experiment_uid = e.experiment_uid
  and l.line_record_uid = $uid";
  $result = mysql_query($sql) or die(mysql_error());
  while ($row=mysql_fetch_row($result)) {
    $trial = $row[0];
    $e_uid = $row[1];
    $empty[$trial] = "";
    $trial_list[$e_uid] = $trial;
  }

  echo "<table>";
  echo "<tr><td>";
  foreach ($trial_list as $trial1=>$val1) {
    echo "<td>$val1";
  }
  foreach ($trial_list as $trial1=>$val1) {
    echo "<tr><td>$val1";
    foreach ($trial_list as $trial2=>$val2) {
      $count = 0;
      unset($marker_list1);
      unset($marker_list2);
      $sql = "select marker_uid, alleles from allele_conflicts
        where line_record_uid = $uid
        and experiment_uid = $trial1";
      $result = mysql_query($sql) or die(mysql_error());
      while ($row=mysql_fetch_row($result)) {
          $count1++;
          $marker_uid = $row[0];
          $alleles1 = $row[1];
          $marker_list1[$marker_uid] = $alleles1;
      }
      $sql = "select marker_uid, alleles from allele_conflicts
        where line_record_uid = $uid
        and experiment_uid = $trial2";
      $result = mysql_query($sql) or die(mysql_error());
      while ($row=mysql_fetch_row($result)) {
          $count2++;
          $marker_uid = $row[0];
          $alleles1 = $row[1];
          $marker_list2[$marker_uid] = $alleles1;
      }
      foreach ($marker_list1 as $marker_uid=>$alleles1) {
        if (isset($marker_list2[$marker_uid])) {
          $alleles2 = $marker_list2[$marker_uid];
          if ($alleles1 == $alleles2) {
          } else {
            $count++;
          }
        }
      }
      echo "<td>$count";
    }
    echo "\n";
  }
  echo "</table><br>\n";
 
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
  echo "<h3>Allele Conflicts for $name_list[$uid] sorted by marker name</h3>\n";
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
    echo "<h2>Allele Conflicts by Line</h2>\n";
    echo "Top 100 conflicts<br>\n";
    echo "Select the link for each line name to view the conflicts between experiments and by marker.";
    echo "<table>";
    echo "<tr><td>line name<td>total<br>measured<td>conflicts<td>percent<br>conflicts\n";
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
               if ($allele != '') {
                   $total++;
               }
           }
       }
       $perc = round(100*$count/$total,2);
       echo "<tr><td><a href=genotyping/sum_lines.php?uid=$uid>$name_list[$uid]</a><td>$total<td>$count<td>$perc\n";
       flush();
    }
}
echo "</table></div>";
include $config['root_dir'].'theme/footer.php';
