<?php
// analyze/table.php, DEM jun2015
// Display Lines vs. Trials for a Trait in tabular format.

require 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
require $config['root_dir'].'theme/admin_header2.php';
require 'heatmap_colors.inc';
$mysqli = connecti();
?>
<style type=text/css>
table tr { min-height: 10px; }
table td { padding: 2px; text-align: center; background-color:white}
</style>

<h1>Summary of Trait values in selected Trials</h1>

<div class='section' style='font-size:100%'>
<b>Legend</b><br>
The least squares mean (<em>LSmean</em>) of a line is the best estimate of that
line's mean based on a linear model.  If a dataset has missing data, the
LSmean adjusts for the expected value of the missing data based on the
model, so that the LSmean is less sensitive to missingness than the
arithmetic mean.<br><br>
If two lines have the same true mean value, their estimated mean values are
only expected to differ by more than the Least Significant Difference (<em>LSD</em>)
in 5% of experiments. 
If a number of lines have the same true mean value, the maximum difference
between any pair of lines is only expected to exceed the Honestly
Significant Difference (<em>HSD</em>) in 5% of experiments.<br>

</div>

<?php
$traits = $_SESSION['selected_traits'];
$trials = $_SESSION['selected_trials'];
$selected_lines = array();
if (isset($_SESSION['selected_lines'])) {
    $line_uids = $_SESSION['selected_lines'];
    foreach ($line_uids as $line) {
        $sql = "select line_record_name from line_records where line_record_uid = $line";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>\n$sql");
        $row = mysqli_fetch_array($res);
        $selected_lines[] = $row[0];
    }
}

if (!$traits or !$trials) {
    echo "Please select at least one trait and one trial, <a href='$config[base_url]phenotype/phenotype_selection.php'>Traits and Trials</a>.<p>";
} else {
    // Retrieve the data into array $vals.
    $lines = array();
    foreach ($traits as $trait) {
        foreach ($trials as $trial) {
            $sql = "select trial_code from experiments where experiment_uid = ?";
            if ($stmt = mysqli_prepare($mysqli, $sql)) {
                mysqli_stmt_bind_param($stmt, "i", $trial);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $trialname);
                mysqli_stmt_fetch($stmt);
                mysqli_stmt_close($stmt);
            }
            $sql = "select lr.line_record_name, pd.value
	      from line_records lr, tht_base t, phenotype_data pd
	      where t.experiment_uid = $trial
	      and pd.phenotype_uid = $trait
              and lr.line_record_uid = t.line_record_uid
	      and t.tht_base_uid = pd.tht_base_uid";
            $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
            while ($row = mysqli_fetch_array($res)) {
                $linename = $row[0];
                $lines[] = $linename;
                $val = $row[1];
                // Calculate max and min.
                if ($max[$trait][$trial] < $val or !$max[$trait][$trial]) {
                    $max[$trait][$trial] = $val;
                }
                if ($min[$trait][$trial] > $val or !$min[$trait][$trial]) {
                    $min[$trait][$trial] = $val;
                }
                $vals[$trait][$trial][$linename] = $val;
            }
        }
        if (empty($lines)) {
            $trtname = mysql_grab("select phenotypes_name from phenotypes where phenotype_uid = $trait");
            echo "Warning: no lines found for trait = $trtname\n";
        } else {
            $lines = array_unique($lines);
            sort($lines);
        }
    }

    // Optionally remove Lines that have any missing values for a Trait/Trial.
    $sparselines = array();
    foreach ($traits as $trait) {
        foreach ($trials as $trial) {
            foreach ($lines as $line) {
                if (!$vals[$trait][$trial][$line]) {
                    $sparselines[] = $line;
                    $missingdata = true;
                }
            }
            if (!empty($sparselines)) {
                $sparselines = array_unique($sparselines);
                sort($sparselines);
            }
        }
    }
    if ($_GET['balance'] == 'yes') {
        $lines = array_diff($lines, $sparselines);
    }

  // Write $vals to a temporary file for R to calculate LSDs.
  // output row = Traitname, Trialname, Linename, Value
  // use uid to make it easier to match results with input
    $outdata = "";
    foreach ($traits as $trait) {
        $trtname = mysql_grab("select phenotypes_name from phenotypes where phenotype_uid = $trait");
        foreach ($trials as $trial) {
            $trlname = mysql_grab("select trial_code from experiments where experiment_uid = $trial");
            foreach ($lines as $line) {
                $val = $vals[$trait][$trial][$line];
                if (!$val) {
                    $val = "NA";
                }
                $outdata .= $trait .",". $trial .",". $line .",". $val ."\n";
            }
        }
    }
    // Check for no data
    if ($outdata == "") {
        echo "Error: No data selected<br>\n";
    }
    // Make the filename unique to deal with concurrency.
    $time = time();
    $outfile = "/tmp/tht/trialdata.csv".$time;
    if (! file_exists('/tmp/tht')) {
         mkdir('/tmp/tht');
    }
    file_put_contents($outfile, $outdata);

    // Run TableReportParameters.R to calculate LSD.
    // On tcap, the imbedded '\n' doesn't work.  Use chr(10) instead.
    /* $setupR = 'oneCol <- read.csv("'.$outfile.'", header=FALSE, stringsAsFactors=FALSE)\noutFile <-c("/tmp/tht/TableReportOut.txt'.$time.'")\n'; */
    $setupR = 'oneCol <- read.csv("'.$outfile.'", header=FALSE, colClasses=c("factor", "factor", "character", "numeric"))'.chr(10).'outFile <-c("/tmp/tht/TableReportOut.txt'.$time.'")'.chr(10);
    // for debugging:
    /* echo "<pre>"; system("echo '$setupR' | cat - ../R/TableReportParameters.R | R --vanilla 2>&1"); */
    exec("echo '$setupR' | cat - ../R/TableReportParameters.R | R --vanilla > /dev/null 2> /tmp/tht/stderr.txt$time");
    // Show resulting file.
    if (file_exists("/tmp/tht/TableReportOut.txt".$time)) {
        $r = fopen("/tmp/tht/TableReportOut.txt".$time, "r");
    // Parse the contents, which look like this:
/* grain protein   grain yield */
/* lsmeans c(13.1983026714983, 14.208479386932, 14.02772900566, 14.241295785412) c(5032.09333333334, 4361.38666666667, 4520.99333333333, 4153.36) */
/* leastSigDiff    0.988636404642669       984.993961827093 */
/* tukeysHSD       1.64895568849516        1642.88042485672 */
/* trialMeans      c(14.4973829682375, 13.7909232364558, 13.4685489324335) c(4828.55, 4780.13, 3942.195) */
        $linenum = 0;
        while ($line = fgets($r)) {
        /* echo "$line<br>"; */
            if ($linenum == 0) {
                // The first line is the names of the traits.
                $rtraits = explode("\t", rtrim($line));
                $rtraitcount = count($rtraits);
                $linenum++;
            } else {
                // Extract the first tab-terminated string.
                preg_match('/(^[^\t]*\t)/', $line, $sublines);
                $firstword = rtrim($sublines[1]);
                $linepieces = explode("\t", rtrim($line));
                if ($firstword == 'lsmeans') {
                    $lsmeansline = $line;
                } elseif ($firstword == 'leastSigDiff') {
                    $lsds = $linepieces;
                    array_shift($lsds);
                } elseif ($firstword == 'tukeysHSD') {
                    $hsds = $linepieces;
                    array_shift($hsds);
                } elseif ($firstword == 'trialNames') {
                    $trialnamesline = $line;
                } elseif ($firstword == 'trialMeans') {
                    $trialmeansline = $line;
                } else {
                    // It must be a continuation line of the lsmeans.
                    $lsmeansline .= $line;
                }
            }
            // All lines of the file have now been read in.
            $lsmeanslists = explode("\t", rtrim($lsmeansline));
            $trialnameslists = explode("\t", rtrim($trialnamesline));
            $trialmeanslists = explode("\t", rtrim($trialmeansline));
            array_shift($lsmeanslists);
            array_shift($trialnameslists);
            array_shift($trialmeanslists);
            $trialnames = array();
            for ($i=0; $i < $rtraitcount; $i++) {
            // Result is formatted like "c(12.65, 11.915, ...)".
                $lsmeans[$i] = explode(", ", preg_replace("/^c\(|\)$/", "", $lsmeanslists[$i]));
                $trialnames[$i] = explode(", ", preg_replace("/^c\(|\)$/", "", $trialnameslists[$i]));
                $trialmeans[$i] = explode(", ", preg_replace("/^c\(|\)$/", "", $trialmeanslists[$i]));
            }
            if (empty($trialmeans)) {
                echo "Error: No traits selected<br>\n";
                break;
            }
        }
        fclose($r);
        $count = count($lines);
        print " $count lines used in LSmean model. ";
    } else {
        echo "<br>Error: No output from R script /tmp/tht/TableReportOut.txt.$time<br>\n";
    }

    // If there's any missing data offer to remove it.
    if ($missingdata) {
        if ($_GET['balance'] == 'yes') {
            $cbox = "checked";
        }
        print "<input type=checkbox $cbox onclick='balancedata(this)'> Remove lines with missing data. ";
    }

  // if there are selected lines offer to display only selected
    if (isset($_SESSION['selected_lines'])) {
        if ($_GET['filter'] == 'no') {
            $cbox2 = "";
        } else {
            $cbox2 = "checked";
        }
        print "<input type=checkbox $cbox2 onclick='filter(this)'> Show only selected lines. ";
    }
    print "<p>";

  // If only one trial then do not need lsmeans, just show means
  // Display table summary
    $countTrials = count($trialnames[0]);
    if ($countTrials == 1) {
        $trial = $trialnames[0][0];
        $trialname = mysql_grab("select trial_code from experiments where experiment_uid = $trial");
        print "Trial means: $trialname<table><tr><th>";
        $traitnumber = 0;
        foreach ($traits as $trait) {
            $trtname = mysql_grab("select phenotypes_name from phenotypes where phenotype_uid = $trait");
            $unit_name = mysql_grab("select unit_name from phenotypes, units where phenotypes.unit_uid = units.unit_uid and phenotype_uid = $trait");
            print "<th>$trtname<br>$unit_name";
        }
        foreach ($lines as $line) {
            if ($_GET['filter'] == 'no') {
            } elseif (isset($_SESSION['selected_lines']) && !in_array($line, $selected_lines)) {
                continue;
            }
            print "<tr><td>$line";
            foreach ($traits as $trait) {
                $traitnumber = 0;
                foreach ($trialnames[$traitnumber] as $trial) {
                    $mn = $min[$trait][$trial];
                    $mx = $max[$trait][$trial];
                    // Omit missing values.  round() seems to return "0" for empty values.
                    unset($val);
                    if ($vals[$trait][$trial][$line]) {
                        $val = round($vals[$trait][$trial][$line], 1);
                        // Calculate the color. Red is 0, pale yellow is 15.
                        // Colors greater than 10 are too pale to read.
                        $col = 10 - floor(10 * ($val - $mn) / ($mx - $mn));
                        print "<td><font color=$color[$col]><b>$val</b></font>";
                    } else {
                        print "<td>--";
                    }
                }
                $traitnumber++;
            }
  }
  print "<tr><td><font color=brown>Trial Mean</font>";
  $traitnumber = 0;
  foreach ($traits as $trait) {
      foreach ($trialmeans[$traitnumber] as $mean) {
          if (!$mean) {
            $tm = "--";
          } else {
            $tm = round($mean, 1);
          }
          print "<td>$tm";
      }
      $traitnumber++;
  }
  print "</table><br>";
  } else {

  // Display summary with links
  $traitnumber = 0;
  print "<table>";
  print "<tr><th colspan=4>LSmeans<tr><th>";
  foreach ($traits as $trait) {
    $trtname = mysql_grab("select phenotypes_name from phenotypes where phenotype_uid = $trait");
    $unit_name = mysql_grab("select unit_name from phenotypes, units where phenotypes.unit_uid = units.unit_uid and phenotype_uid = $trait");
    $url = $config['base_url'] . "analyze/table.php#$traitnumber";
    print "<th><a href=\"$url\">$trtname<br>$unit_name</a>\n";
  }
  $linenumber = 0;
  foreach ($lines as $line) {
      if ($_GET['filter'] == 'no') {
      } elseif (isset($_SESSION['selected_lines']) && !in_array($line, $selected_lines)) {
          continue;
      }
      print "<tr><td>$line";
      $lsm = 0;
      $traitnumber = 0;
      foreach ($traits as $trait) {
          $lsm = round($lsmeans[$traitnumber][$linenumber], 1);
          print "<td>$lsm";
          $traitnumber++;
      }
      $linenumber++;
  }
  print "</table><br>";

  // Display the table on the page.
  $traitnumber = 0;
  foreach ($traits as $trait) {
    $trtname = mysql_grab("select phenotypes_name from phenotypes where phenotype_uid = $trait");
    $unit_name = mysql_grab("select unit_name from phenotypes, units where phenotypes.unit_uid = units.unit_uid and phenotype_uid = $trait");
    if (!$lsds[$traitnumber]) {
      $lsdround = "--";
    } else {
      $lsdround = round($lsds[$traitnumber], 2);
    }
    if (!$hsds[$traitnumber]) {
      $hsdround = "--";
    } else {
      $hsdround = round($hsds[$traitnumber], 2);
    }
    print "<p id=\"$traitnumber\"></p>";
    print "<table><tr><th>Trait: $trtname<br>$unit_name<br>LSD = $lsdround<br>HSD = $hsdround";
    foreach ($trialnames[$traitnumber] as $trial) {
      $trialname = mysql_grab("select trial_code from experiments where experiment_uid = $trial");
      print "<th><a href='display_phenotype.php?trial_code=$trialname'>$trialname</a>";
    }
    print "<th>LSmeans";
    $linenumber = 0;
    foreach ($lines as $line) {
      if ($_GET['filter'] == 'no') {
      } elseif (isset($_SESSION['selected_lines']) && !in_array($line, $selected_lines)) {
          continue;
      }
      print "<tr><td>$line";
      foreach ($trialnames[$traitnumber] as $trial) {
        $trial = preg_replace("/\"/", "", $trial);
	$mn = $min[$trait][$trial];
	$mx = $max[$trait][$trial];
	// Omit missing values.  round() seems to return "0" for empty values.
	unset($val);
	if ($vals[$trait][$trial][$line]) {
	  $val = round($vals[$trait][$trial][$line], 1);
	  // Calculate the color. Red is 0, pale yellow is 15. 
	  // Colors greater than 10 are too pale to read.
	  $col = 10 - floor(10 * ($val - $mn) / ($mx - $mn));
	  print "<td><font color=$color[$col]><b>$val</b></font>";
	} else {
	  print "<td>--";
        }
      }
      $lsm = round($lsmeans[$traitnumber][$linenumber], 1);
      /* $col = 10 - floor(10 * ($lsm - $mn) / ($mx - $mn)); */
      /* print "<td><font color=$color[$col]><b>$lsm</b></font>"; */
      print "<td>$lsm";
      $linenumber++;
    }
    print "<tr><td><font color=brown><b>Trial means</b></font>";
    foreach ($trialmeans[$traitnumber] as $mean) {
      if (!$mean) {
        $tm = "--";
      } else {
        $tm = round($mean, 1);
      }
      print "<td>$tm";
    }
    $trialcount = count($trials);
    print "</table><p>";
    $traitnumber++;
  }
  }
}

?>

<script type = "text/javascript">
    function balancedata(cbox) {
	if (cbox.checked) 
	    window.location = "<?php echo $_SERVER['PHP_SELF'] ?>" + "?balance=yes";
	else 
	    window.location = "<?php echo $_SERVER['PHP_SELF'] ?>" + "?balance=no";
    }
    function filter(cbox2) {
        if (cbox2.checked) 
            window.location = "<?php echo $_SERVER['PHP_SELF'] ?>" + "?filter=yes";
        else 
            window.location = "<?php echo $_SERVER['PHP_SELF'] ?>" + "?filter=no";
    }

</script>

<?php
$footer_div=1;
require $config['root_dir'].'theme/footer.php';
