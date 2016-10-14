<?php
require 'config.php';
require $config['root_dir'].'includes/bootstrap2.inc';
$mysqli = connecti();

require $config['root_dir'].'theme/admin_header2.php';

$sql = "select line_record_uid, line_record_name from line_records";
$result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
while ($row=mysqli_fetch_row($result)) {
    $uid = $row[0];
    $name = $row[1];
    $name_list[$uid] = $name;
}

echo "Also see <a href=genotyping/sum_exp.php>conflicts by experiment</a>, <a href=genotyping/sum_markers.php>conflicts by marker</a>";
echo ", and <a href=genotyping/allele_conflicts.php>All Allele Conflicts</a>.<br><br>\n";

if (isset($_GET['uid'])) {
    $uid = $_GET['uid'];
 
    //get list of trials
    $sql = "select distinct(e.trial_code), e.experiment_uid
    from allele_conflicts a, line_records l, markers m, experiments e
    where a.line_record_uid = l.line_record_uid
    and a.marker_uid = m.marker_uid
    and a.experiment_uid = e.experiment_uid
    and l.line_record_uid = ?";
    if ($stmt = mysqli_prepare($mysqli, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $uid);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $trial, $e_uid);
        while (mysqli_stmt_fetch($stmt)) {
            $empty[$trial] = "";
            $trial_list[$e_uid] = $trial;
        }
        mysqli_stmt_close($stmt);
    }

    if (isset($trial_list)) {
        echo "<h3>Allele Conflicts for $name_list[$uid] between experiments</h2>\n";
        echo "Each entry has number of conflicts / comparisons (percent conflicts).<br>\n";
    } else {
        echo "Invalid entry";
        return;
    }
    echo "<table>";
    echo "<tr><td>";
    foreach ($trial_list as $trial1 => $val1) {
        echo "<td>$val1";
    }
    $i = 1;
    foreach ($trial_list as $trial1 => $val1) {
        $i++;
        echo "<tr><td>$val1";
        $marker_list1 = array();
        $marker_all1 = array();
        $sql = "select marker_uid, alleles from allele_conflicts
        where line_record_uid = ? 
        and experiment_uid = $trial1";
        if ($stmt = mysqli_prepare($mysqli, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $uid);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $marker_uid, $alleles1);
            while (mysqli_stmt_fetch($stmt)) {
                $count1++;
                $marker_list1[$marker_uid] = $alleles1;
            }
            mysqli_stmt_close($stmt);
        }
        $sql = "select distinct marker_uid from allele_cache
        where line_record_uid = ? 
        and experiment_uid = $trial1";
        if ($stmt = mysqli_prepare($mysqli, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $uid);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $marker_uid);
            while (mysqli_stmt_fetch($stmt)) {
                $marker_all1[] = $marker_uid;
            }
            mysqli_stmt_close($stmt);
        }
        $j = 1;
        foreach ($trial_list as $trial2 => $val2) {
            $j++;
            if ($j > ($i + 0)) {
                break;
            }
            $marker_list2 = array();
            $marker_all2 = array();
            $sql = "select marker_uid, alleles from allele_conflicts
            where line_record_uid = ? 
            and experiment_uid = $trial2";
            if ($stmt = mysqli_prepare($mysqli, $sql)) {
                mysqli_stmt_bind_param($stmt, "i", $uid);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $marker_uid, $alleles1);
                while (mysqli_stmt_fetch($stmt)) {
                    $count2++;
                    $marker_list2[$marker_uid] = $alleles1;
                }
                mysqli_stmt_close($stmt);
            }
            $sql = "select distinct marker_uid from allele_cache
            where line_record_uid = ? 
            and experiment_uid = $trial2";
            if ($stmt = mysqli_prepare($mysqli, $sql)) {
                mysqli_stmt_bind_param($stmt, "i", $uid);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $marker_uid);
                while (mysqli_stmt_fetch($stmt)) {
                    $marker_all2[] = $marker_uid;
                }
                mysqli_stmt_close($stmt);
            }
            $count = 0;
            foreach ($marker_list1 as $marker_uid => $alleles1) {
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
            if ($count > 10) {
                $perc = round(100*($count/$tmp2), 0);
                echo "<td>$count/$tmp2 ($perc%)";
            } elseif ($count > 0) {
                $perc = round(100*($count/$tmp2), 1);
                echo "<td>$count/$tmp2 ($perc%)";
            } else {
                echo "<td>$count/$tmp2 (0%)";
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
    and l.line_record_uid = ? 
    order by m.marker_name";
    if ($stmt = mysqli_prepare($mysqli, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $uid);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $line_name, $marker_name, $alleles, $trial);
        $count = 0;
        $prev = "";
        echo "<h3>Allele Conflicts for $name_list[$uid] sorted by marker name</h3>\n";
        echo "<table>\n";
        echo "<tr><td>marker name\n";
        foreach ($empty as $trial => $allele) {
            echo "<td>$trial";
        }
        while (mysqli_stmt_fetch($stmt)) {
            if ($marker_name == $prev) {
                $allele_ary[$trial] = $alleles;
            } else {
                if ($count > 0) {
                    echo "<tr><td>$prev";
                    foreach ($allele_ary as $t1 => $a) {
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
    }
    echo "<tr><td>$prev";
    foreach ($allele_ary as $t1 => $a) {
        echo "<td>$a";
    }
    echo "\n";
} else {
    echo "<h2>Allele Conflicts between experiments by Line</h2>\n";

    // Update cache table if necessary. Empty?
    $sql = "select line_record_uid from allele_duplicates";
    $result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    if (mysqli_num_rows($result) == 0) {
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
        echo "<br>The database table is out of date.<br>\n";
        echo "A job has been scheduled to update the table<br>\n";
        echo "Please check the results again 30 minutes<br>\n";
        exec("php update-conflicts.php > /dev/null &");
    }

    if (isset($_SESSION['selected_lines'])) {
        $selectedlines = $_SESSION['selected_lines'];
        $count = count($selectedlines);
        echo "Only displaying lines from saved selection or $count lines.<br>\n";
        $lines_str = implode(",", $selectedlines);
        $sql = "select line_record_uid, duplicates, conflicts, percent_conf
        from allele_duplicates where line_record_uid IN ($lines_str)
        order by percent_conf DESC";
    } else {
        $sql = "select line_record_uid, duplicates, conflicts, percent_conf
        from allele_duplicates order by percent_conf DESC";
    }
    echo "Select the link for each line name to view the conflicts between experiments and by marker.<br>";
    echo "When there are more than 2 experiments the values are for the experiments that have the largest percentage of conflicts.<br>\n";
    echo "<table>";
    echo "<tr><td>line name<td>conflicts<td>comparisons<td>percent<br>conflicts\n";
    $result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row=mysqli_fetch_row($result)) {
        $uid = $row[0];
        $dupl = $row[1];
        $conf = $row[2];
        $perc = $row[3];
        echo "<tr><td><a href='".$config['base_url']."genotyping/sum_lines.php?uid=$uid'>$name_list[$uid]</a><td>$conf<td>$dupl<td>$perc\n";
    }
}
echo "</table></div>";
require $config['root_dir'].'theme/footer.php';
