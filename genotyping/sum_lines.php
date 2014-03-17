<?php
require 'config.php';
include($config['root_dir'].'includes/bootstrap_curator.inc');
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
  echo "Each entry has number of conflicts, duplicate markers, percentage of conflicts.<br>\n";
 
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
    unset($marker_list1);
    unset($marker_all1);
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
    $sql = "select marker_uid from allele_cache
        where line_record_uid = $uid
        and experiment_uid = $trial1";
    $result = mysql_query($sql) or die(mysql_error());
    while ($row=mysql_fetch_row($result)) {
        $marker_uid = $row[0];
        $marker_all1[] = $marker_uid;
    }
    foreach ($trial_list as $trial2=>$val2) {
      $count = 0;
      unset($marker_list2);
      unset($marker_all2);
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
      $sql = "select marker_uid from allele_cache
        where line_record_uid = $uid
        and experiment_uid = $trial2";
      $result = mysql_query($sql) or die(mysql_error());
      while ($row=mysql_fetch_row($result)) {
        $marker_uid = $row[0];
        $marker_all2[] = $marker_uid;
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
      $tmp1 = array_intersect($marker_all1, $marker_all2);
      $tmp2 = count($tmp1);
      if ($count > 0) {
        $perc = round(100*($count/$tmp2), 0);
        echo "<td>$count $tmp2 ($perc%)";
      } else {
        echo "<td>$count $tmp2";
      }
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
    echo "<h2>Allele Conflicts between experiments by Line</h2>\n";
    echo "Select the link for each line name to view the conflicts between experiments and by marker.<br>";
    echo "When there are more than 2 experiments the values are for the experiments that have the largest percentage of conflicts.<br>\n";

    // Update cache table if necessary. Empty?
    if(mysql_num_rows(mysql_query("select line_record_uid from allele_duplicates")) == 0)
      $update = TRUE;

    // Out of date?
    $sql = "select if( datediff(
            (select max(updated_on) from allele_frequencies),
            (select max(updated_on) from allele_duplicates)
          ) > 0, 'need_update', 'okay')";
    $need = mysql_grab($sql);
    if ($need == 'need_update') {
    //    $update = TRUE;
    }

    if ($update) {
    //update table
    echo "allele conflicts table is out of date, recalculating .....<br>\n";
    echo "Please wait, this may take 30 minutes<br>\n";
    $sql = "delete from allele_duplicates";
    set_time_limit(0);
    $result = mysql_query($sql) or die(mysql_error() . "<br>$sql");
    $sql = "select line_record_uid, count(distinct(marker_uid)) as temp from allele_conflicts
      group by line_record_uid order by temp DESC";
    $result = mysql_query($sql) or die(mysql_error());
    while ($row=mysql_fetch_row($result)) {
       $uid = $row[0];
       $count = $row[1];
       $sql = "insert into  allele_duplicates (line_record_uid, conflicts) values ($uid, $count)";
       $result2 = mysql_query($sql) or die(mysql_error() . "<br>$sql");

        $sql = "select distinct(e.trial_code), e.experiment_uid
          from allele_conflicts a, line_records l, markers m, experiments e
          where a.line_record_uid = l.line_record_uid
          and a.marker_uid = m.marker_uid
          and a.experiment_uid = e.experiment_uid
          and l.line_record_uid = $uid";
        $result2 = mysql_query($sql) or die(mysql_error());
        while ($row2=mysql_fetch_row($result2)) {
          $trial = $row2[0];
          $e_uid = $row2[1];
          $trial_list[$e_uid] = $trial;
        }

        $total = 0;
        foreach ($trial_list as $trial1=>$val1) {
          $count1 = 0;
          unset($measured1);
          $sql = "select marker_uid from allele_cache where line_record_uid = $uid and experiment_uid = $trial1";
          //echo "$sql<br>\n";
          $result2 = mysql_query($sql) or die(mysql_error() . "<br>$sql");
          while ($row2=mysql_fetch_row($result2)) {
             $count1++;
             $marker_uid = $row2[0];
             $measured1[] = $marker_uid;
          }
          foreach ($trial_list as $trial2=>$val2) {
            $count2 = 0;
            unset($measured2);
            $sql = "select marker_uid from allele_cache where line_record_uid = $uid and experiment_uid = $trial2";
            //echo "$sql<br>\n";
            $result3 = mysql_query($sql) or die(mysql_error() . "<br>$sql");
            while ($row3=mysql_fetch_row($result3)) {
              $count2++;
              $marker_uid = $row3[0];
              $measured2[] = $marker_uid;
            }
            if (($count1 > 0) && ($count2 > 0) && ($trial1 != $trial2)) {
              $tmp1 = array_intersect($measured1, $measured2);
              $tmp2 = count($tmp1);
              $total = $total + $tmp2;
            }
            //echo "$uid $trial1 $trial2 $tmp2 $total<br>\n";
          }
        }
        $total = $total / 2;
        $sql = "update allele_duplicates set duplicates = $total where line_record_uid = $uid";
        $result2 = mysql_query($sql) or die(mysql_error() . "<br>$sql");
        //echo "$uid $sql<br>\n";
    }

    }

    echo "<table>";
    echo "<tr><td>line name<td>conflicts<td>duplicate<br>entries<td>percent<br>conflicts\n";
    $sql = "select line_record_uid, duplicates, conflicts, percent_conf
      from allele_duplicates order by percent_conf DESC";
    $result = mysql_query($sql) or die(mysql_error());
    while ($row=mysql_fetch_row($result)) {
       $uid = $row[0];
       $dupl = $row[1];
       $conf = $row[2];
       $perc = $row[3];
       echo "<tr><td><a href=genotyping/sum_lines.php?uid=$uid>$name_list[$uid]</a><td>$conf<td>$dupl<td>$perc\n";
    }
}
echo "</table></div>";
include $config['root_dir'].'theme/footer.php';
