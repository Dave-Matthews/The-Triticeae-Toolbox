<?php

/**
   Index/trait.php, DEM 26jun13
   Create a user-defined Selection Index, a weighted combination of Trait values
   in a specified set of Trials.
   dem 7jan13: Use mean over trials to calculate index, in case of
      missing data in some trials.  Fixed bug in normalization.
   Todo: - Show correlation of each trait vs. Index.
         - Allow download of the result tables.
  11/30/17 - allow any line to be sellected as common line then skip if not present
 */

require 'config.php';
require $config['root_dir'] . 'includes/bootstrap.inc';
require $config['root_dir'] . 'theme/admin_header2.php';
$mysqli = connecti();
$row = loadUser($_SESSION['username']);

/**
   Scale the measured phenotype values according to user's chosen method.
 */
function scaled($rawvalue, $trait, $trial)
{
    global $scaling, $mean, $SD, $basevalue;
    if ($scaling == 'actual') {
        return $rawvalue;
    }
    if ($scaling == 'normalized') {
        // For each trait, subtract the trial mean from $actual and divide by SD.
        $normalized = ($rawvalue - $mean[$trait][$trial]) / $SD[$trait][$trial];
        return $normalized;
    }
    if ($scaling == 'percent') {
        if (empty($basevalue[$trait][$trial])) {
            $percent = null;
        } else {
            $percent = 100 * $rawvalue / $basevalue[$trait][$trial];
        }
        return $percent;
    }
}

?>
<style type="text/css">
/* Make the tables more compact. */
  table td { padding-top: 0px; padding-bottom: 0px;}
</style>

<h2>Selection Index</h2>

<?php

// Get the Currently Selected Traits.
if (!isset($_SESSION['selected_traits'])) {
    finish("Please <a href=".$config[base_url]."phenotype/phenotype_selection.php>choose a set of traits</a> to combine.");
}
$i = 0;
foreach ($_SESSION[selected_traits] as $traitid) {
    $traitids[$i] = $traitid;
    $traitnames[$i] = mysql_grab("select phenotypes_name from phenotypes where phenotype_uid=$traitid");
    $i++;
}
$traitcount = count($traitids);
$traitlist = implode(',', $traitids);
if ($traitcount == 0) {
    finish("Please <a href=".$config[base_url]."phenotype/phenotype_selection.php>choose a set of traits</a> to combine.");
}
// Currently Selected Trials
$j = 0;
foreach ($_SESSION[selected_trials] as $trialid) {
    $trialids[$j] = $trialid;
    $trialnames[$j] = mysql_grab("select trial_code from experiments where experiment_uid = $trialid");
    $j++;
}
$trialcount = count($trialids);
$triallist = implode(',', $trialids);

// Lines in common among all these trials, as array of (name, uid) pairs.
$started = 0;
foreach ($trialids as $tid) {
    $sql = "select line_record_uid from tht_base where experiment_uid = $tid";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    $entries = array();
    while ($row = mysqli_fetch_row($res)) {
        $entries[] = $row[0];
    }
    if ($started > 0) {
        $commonlines = array_intersect($commonlines, $entries);
    } else {
        $commonlines = $entries;
        $started = 1;
    }
}

if (empty($_GET) or $_REQUEST['reselect']) {
    // Initial entry to the script or resetting parameters.
    ?>
    Choose relative weights and a scaling method to combine the traits into an index.
    <br>If smaller values of a trait are better, reverse the scale.
    <br>Data will come from the <a href=<?php echo $config[base_url]?>phenotype/phenotype_selection.php>currently selected trials</a>.
    <br><br>
    <form>
    <table>
    <tr><th>Trait<th>Weight<th>Reverse scale

    <?php
    // If reselecting parameters, read in the old values.
    $weight = $_REQUEST[wt];
    $reverse = $_REQUEST[reverse];
    // If user hasn't yet specified the relative weights, divide equally.
    foreach ($traitnames as $tn) {
        if (!$weight[$tn]) {
            $weight[$tn] = intval(100 / $traitcount);
        }
        echo "<tr><td>$tn";
        echo "<td><input type=text name='wt[$tn]' value=$weight[$tn] size=3>";
        echo "<td style=text-align:center>";
        // Previously reversed?
        $ck = "";
        if ($reverse[$tn] == 'on') {
            $ck = "checked";
        }
        echo "<input type=checkbox name='reverse[$tn]' $ck>";
    }
?>
  </table>
<br><h3>Scaling of trait values</h3>
<input type=radio name=scaling value=normalized checked>Normalized, subtracting the trial mean and dividing by the standard deviation
<br><input type=radio name=scaling value=actual>Actual measured value

<?php
// Disallow "Percent of common line" if there are no lines in common across all trials.
$dsbl = "";
$choices = "Choose...";
if (empty($commonlines)) {
    $dsbl = "disabled";
    $choices = "None";
}
echo "<br><input type=radio name=scaling value=percent $dsbl>Percent of common line: ";
echo "<select name=base-line>";
echo "<option value=0>$choices</option>";
//foreach ($commonlines as $cl) {
foreach ($entries as $cl) {
    $linename = mysql_grab("select line_record_name from line_records where line_record_uid = $cl");
    echo "<option value = $cl>$linename</option>";
}
?>
</select>
<br><input type=radio name=scaling value=rank>Rank in trial
<p><input type=submit value="Submit">
</form>

<?php
    // end of if (empty($_REQUEST))
} else { // Submit button was clicked.
    $weight = $_REQUEST[wt];
    $totalwt = array_sum($weight); // Needn't add up to 100.
    $reverse = $_REQUEST[reverse];
    $scaling = $_REQUEST[scaling];
    if ($_REQUEST['base-line'] != 0) {
        $scaling = "percent";
    }
    echo "<form method=POST>";
    echo "Scaling method: <b>$scaling</b>";
    if ($scaling == 'percent') {
        $baselineuid = $_REQUEST['base-line'];
        $sql = "select line_record_name from line_records where line_record_uid = $baselineuid";
        $res = mysqli_query($mysqli, $sql) or finish("<p>MySQL error: ". mysqli_error($mysqli));
        if ($row = mysqli_fetch_array($res)) {
            $baselinename = $row[0];
        } else {
            finish("<br>Error: please select common line<br>\n");
        }
        echo " of <b>$baselinename</b>";
    }
    echo ". Weights: <b>".implode($weight, ', ')."</b>. ";
    echo "<input type=submit name=reselect value=Reselect>";
    echo "</form>";

    $lines = array();
    // Fetch the data.
    if (!empty($triallist)) {
        $trialsubset = "and tb.experiment_uid in ($triallist)";
    }
    $sql = "select pd.phenotype_uid, phenotypes_name, tb.experiment_uid, trial_code, 
            tb.line_record_uid, line_record_name, value
	from phenotype_data pd, tht_base tb, phenotypes p, line_records lr, experiments e
	where pd.phenotype_uid in ($traitlist)
	$trialsubset
	and pd.tht_base_uid = tb.tht_base_uid
	and p.phenotype_uid = pd.phenotype_uid
	and lr.line_record_uid = tb.line_record_uid
	and e.experiment_uid = tb.experiment_uid
        order by phenotypes_name, trial_code, abs(value) desc";
    $res = mysqli_query($mysqli, $sql) or finish("<p>MySQL error: ". mysqli_error($mysqli));
  // Read it into the master array $actual, indexed by (trait, trial, line).
    while ($row = mysqli_fetch_array($res)) {
        $actual[$row[phenotypes_name]][$row[trial_code]][$row[line_record_name]] = $row[value];
        // Get the names of the lines.
        if (!in_array($row[5], $lines)) {
            $lines[] = $row[5];
        }
    }

    if ($scaling == 'normalized') {
        // To normalize we need the mean and SD of each trait/trial combination.
        foreach ($traitnames as $tn) {
            foreach ($trialnames as $trial) {
                $sum = 0;
                $linecount = 0;
                $devsq = 0;
                foreach ($lines as $line) {
                    if ($actual[$tn][$trial][$line]) {
                        $sum += $actual[$tn][$trial][$line];
                        $linecount++;
                    }
                }
                $mean[$tn][$trial] = $sum / $linecount;
                foreach ($lines as $line) {
                    if ($actual[$tn][$trial][$line]) {
                    // Get the sum of (deviations from mean)^2.
                        $devsq += pow($mean[$tn][$trial] - $actual[$tn][$trial][$line], 2);
                        if ($linecount == 0) {
                            $SD[$tn][$trial] = 0;
                        } else {
                            $SD[$tn][$trial] = sqrt($devsq / $linecount);
                        }
                    }
                }
            }
        }
    }

    if ($scaling == "rank") {
        foreach ($traitnames as $tn) {
            foreach ($trialnames as $trial) {
                $sum = 0;
                $linecount = 0;
                $devsq = 0;
                $tmp1 = array();
                $tmp2 = array();
                foreach ($lines as $line) {
                    if ($actual[$tn][$trial][$line]) {
                        $tmp1[] = $actual[$tn][$trial][$line];
                        $tmp2[] = $line;
                    }
                }
                array_multisort($tmp1, $tmp2);
                $rankIndex[$tn][$trial] = $tmp1;
                $rankName[$tn][$trial] = $tmp2;
            }
        }
    }

    if ($scaling == 'percent') {
        // Get the values for the "base" line used as reference.
        foreach ($traitnames as $tn) {
            foreach ($trialnames as $trial) {
                if (isset($actual[$tn][$trial][$baselinename])) {
                    $basevalue[$tn][$trial] = $actual[$tn][$trial][$baselinename];
                }
            }
        }
    }

    // Average the (scaled) trait scores over trials, for each line.
    foreach ($traitnames as $tn) {
        foreach ($lines as $line) {
            $sum = 0;
            $N = 0;
            foreach ($trialnames as $trial) {
                if (!empty($actual[$tn][$trial][$line])) {
                    if ($scaling == 'rank') {
                        $sum += array_search($line, $rankName[$tn][$trial]);
                        $N++;
                    } else {
                        $tmp = scaled($actual[$tn][$trial][$line], $tn, $trial);
                        if (!empty($tmp)) {
                            $sum += $tmp;
                            $N++;
                        }
                    }
                }
            }
            if ($N > 0) {
                $avg[$tn][$line] = $sum / $N;
            }
        }
    }

    // Calculate Index from the scaled average over trials.
    foreach ($lines as $line) {
        // Don't calculate an Index if there is no value for one or more traits.
        $missing = false;
        foreach ($traitnames as $tn) {
            if (empty($avg[$tn][$line])) {
                $missing = true;
            } else {
                $weightedval = ($weight[$tn] * $avg[$tn][$line] ) / $totalwt;
                if ($reverse[$tn] == 'on') {
                    $weightedval = - $weightedval;
                }
                $wv[$tn] = $weightedval;
            }
        }
        if (!$missing) {
            $avgndx[$line] = round(array_sum($wv), 2);
        }
    }
  // Sort with highest first.
  arsort($avgndx);

  // Calculate Index within each trial.
  foreach ($trialnames as $trial) {
    foreach ($lines as $line) {
      // If the value of any trait is missing for this line and trial, ignore.
      $missing = FALSE;
      foreach ($traitnames as $tn) {
        if (empty($actual[$tn][$trial][$line]))
          $missing = TRUE;
      }
      // Otherwise calculate.
      if (!$missing) {
        foreach ($traitnames as $tn) {
          if ($scaling == 'rank') {
            $weightedval = ($weight[$tn] * array_search($line, $rankName[$tn][$trial])) / $totalwt; 
          } else {
	    $weightedval = ($weight[$tn] * scaled($actual[$tn][$trial][$line], $tn, $trial)) / $totalwt;
          }
	  if ($reverse[$tn] == 'on')
	    $weightedval = - $weightedval;
	  $wv[$tn] = $weightedval;
	}
	$Index[$trial][$line] = round(array_sum($wv), 2);
      }
    }
  }

  // Display.
  echo "<h3>Selection Index values</h3>";
  echo "Individual trait values, after scaling and averaging over trials, are also shown.<p>";
  // table header
  echo "<table><tr><th>Line<th>Index";
  foreach ($traitnames as $tn)
    echo "<th>$tn";
  // table contents
  foreach ($avgndx as $ln => $ndx) {
    echo "<tr><td>$ln<td>$ndx";
    foreach ($traitnames as $tn) {
      if(empty($avg[$tn][$ln]))
	$avgscaled = "";
      else 
	$avgscaled = round($avg[$tn][$ln], 2);
      echo "<td>$avgscaled";
    }
  }
  echo "</table>";

  // Show per-trial, with a column for each trait.
  echo "<h3>Details, by Trial</h3>";
  echo "<table><tr><th>Trial<th>Line<th>Index";
  foreach ($traitnames as $tn)
    echo "<th>$tn";
  // table contents
  foreach ($Index as $trial => $linendx) {
    // Re-sort by Index value within this trial.
    arsort($linendx);
    foreach ($linendx as $line => $ndx) {
      echo "<tr><td>$trial<td>$line<td>$ndx";
      foreach ($traitnames as $tn)
	echo "<td style=text-align:center>".round($actual[$tn][$trial][$line], 2);
    }
  }
  echo "</table>";

} // end of else Submit button was clicked. 

finish('');

function finish($message) {
  echo $message;
  $footer_div = 1;
  global $config;
  include($config['root_dir'].'theme/footer.php');
  exit;
}

?>
