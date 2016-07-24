<?php
/**
 * update-conflicts.php
 * rebuild allele_duplicates table
 *
 * PHP version 5
 *
 * @author Clay Birkett <claybirkett@gmail.com>
*/
require 'config.php';
include $config['root_dir'].'includes/bootstrap_curator.inc';
$mysqli = connect();

// Update cache table if necessary. Empty?
if (mysqli_num_rows(mysqli_query($mysqli, "select line_record_uid from allele_duplicates")) == 0) {
    $update = true;
}
// Out of date?
$sql = "select if( datediff(
        (select max(updated_on) from allele_frequencies),
        (select max(updated_on) from allele_duplicates)
        ) > 0, 'need_update', 'okay')";
$need = mysql_grab($sql);
if ($need == 'need_update') {
    $update = true;
}

if ($update) {
    //update table

    $sql = "delete from allele_duplicates";
    set_time_limit(0);
    $result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));

    $sql = "select line_record_uid, count(distinct(marker_uid)) as temp from allele_conflicts
      group by line_record_uid order by temp DESC";
    $result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row=mysqli_fetch_row($result)) {
        $uid = $row[0];

        $sql = "select distinct(e.trial_code), e.experiment_uid
          from allele_conflicts a, line_records l, markers m, experiments e
          where a.line_record_uid = l.line_record_uid
          and a.marker_uid = m.marker_uid
          and a.experiment_uid = e.experiment_uid
          and l.line_record_uid = $uid";
        $result2 = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        while ($row2=mysqli_fetch_row($result2)) {
          $trial = $row2[0];
          $e_uid = $row2[1];
          $trial_list[$e_uid] = $trial;
        }

        $count_duplicate = 0;
        $count_conflict = 0;
        $max_perc = 0;
        foreach ($trial_list as $trial1=>$val1) {
          $count1 = 0;
          $marker_list1 = array();
          $marker_all1 = array();
          $sql = "select marker_uid, alleles from allele_conflicts
             where line_record_uid = $uid
             and experiment_uid = $trial1";
          $result2 = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
          while ($row2=mysqli_fetch_row($result2)) {
             $count1++;
             $marker_uid = $row2[0];
             $alleles1 = $row2[1];
             $marker_list1[$marker_uid] = $alleles1;
          }
          $sql = "select marker_uid from allele_cache
              where line_record_uid = $uid and experiment_uid = $trial1";
          //echo "$sql<br>\n";
          $result2 = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
          while ($row2=mysqli_fetch_row($result2)) {
             $count1++;
             $marker_uid = $row2[0];
             $marker_all1[] = $marker_uid;
          }
          foreach ($trial_list as $trial2=>$val2) {
            $count2 = 0;
            $marker_list2 = array();
            $marker_all2 = array();
            $sql = "select marker_uid, alleles from allele_conflicts
              where line_record_uid = $uid
              and experiment_uid = $trial2";
            $result2 = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
            while ($row2=mysqli_fetch_row($result2)) {
               $count2++;
               $marker_uid = $row2[0];
               $alleles1 = $row2[1];
               $marker_list2[$marker_uid] = $alleles1;
            }
            $sql = "select marker_uid from allele_cache where line_record_uid = $uid and experiment_uid = $trial2";
            //echo "$sql<br>\n";
            $result3 = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
            while ($row3=mysqli_fetch_row($result3)) {
              $count2++;
              $marker_uid = $row3[0];
              $marker_all2[] = $marker_uid;
            }
            $count_conflict = 0;
            foreach ($marker_list1 as $marker_uid=>$alleles1) {
              if (isset($marker_list2[$marker_uid])) {
                $alleles2 = $marker_list2[$marker_uid];
                if ($alleles1 == $alleles2) {
                } else {
                  $count_conflict++;
                }
              }
            }
            $tmp1 = array_intersect($marker_all1, $marker_all2);
            $count_duplicate = count($tmp1);
            if (($count_conflict > 0) && ($count_duplicate > 0)) {
              $perc = $count_conflict/$count_duplicate;
              if ($perc > $max_perc) {
                $max_perc = $perc;
                $max_count_dup = $count_duplicate;
                $max_count_con = $count_conflict;
                echo "perc = $perc count duplicate = $count_duplicate count conflict = $count_conflict<br>\n";
              }
            }
          }
        }
        $max_perc = round(100*$max_perc, 0);
        $sql = "insert into allele_duplicates (line_record_uid, duplicates, conflicts, percent_conf) values ($uid, $max_count_dup, $max_count_con, $max_perc)";
        $result2 = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        echo "$uid $sql<br>\n";
        flush();
    }
} else {
  echo "Update not needed\n";
}
