<?php

/* Index/trait.php, DEM 26jun13 */
/* Create a user-defined Selection Index, a weighted combination of Trait values
   in a specified set of Trials. */
require 'config.php';
require $config['root_dir'] . 'includes/bootstrap.inc';
include($config['root_dir'] . 'theme/admin_header.php');
connect();
$mysqli = connecti();
$row = loadUser($_SESSION['username']);

// Scale the measured phenotype values according to user's chosen method.
function scaled($rawvalue, $trait, $trial) {
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
    $percent = 100 * $rawvalue / $basevalue[$trait][$trial];
    return $percent;
  }
}

?>
<style type="text/css">
/* Make the tables more compact. */
  table td { padding-top: 2px; padding-bottom: 2px;}
</style>

<h2>Selection Index</h2>


<?php
// Get the Currently Selected Traits.
$i = 0;
foreach($_SESSION[selected_traits] as $traitid) {
  $traitids[$i] = $traitid;
  $traitnames[$i] = mysql_grab("select phenotypes_name from phenotypes where phenotype_uid=$traitid");
  $i++;
}
$traitcount = count($traitids);
$traitlist = implode(',', $traitids);
if ($traitcount == 0) {
  echo "Please <a href=".$config[base_url]."phenotype/phenotype_selection.php>choose a set of traits</a> to combine.";
  finish();
}
// Currently Selected Trials
$j = 0;
foreach($_SESSION[selected_trials] as $trialid) {
  $trialids[$j] = $trialid;
  $trialnames[$j] = mysql_grab("select trial_code from experiments where experiment_uid = $trialid");
  $j++;
}
$trialcount = count($trialids);
$triallist = implode(',', $trialids);

// Lines in common among all these trials, as array of (name, uid) pairs.
$started = 0;
foreach ($trialids as $tid) {
  $res = mysql_query("select line_record_uid from tht_base where experiment_uid = $tid") or die (mysql_error());
  $entries = array();
  while ($row = mysql_fetch_row($res)) 
    $entries[] = $row[0];  
  if ($started > 0) 
    $commonlines = array_intersect($commonlines, $entries);
  else {
    $commonlines = $entries;
    $started = 1;
  }
}
/* print_h($commonlines); */
/* $ct = count($commonlines); */
/* echo "count of commonlines = $ct<p>"; */

if (empty($_REQUEST)) { // Initial entry to the script.
?>
Choose relative weights and a scaling method to combine the traits into an index.
<br>If smaller values of a trait are better, reverse the scale.
<br>Data will come from the <a href=<?php echo $config[base_url]?>phenotype/phenotype_selection.php>currently selected trials</a>.
<br><br>
<form>
<table>
<tr><th>Trait<th>Weight<th>Reverse scale

<?php
  // User hasn't yet specified the relative weights.  Divide equally by default.
foreach ($traitnames as $tn) { 
  $weight[$tn] = intval(100 / $traitcount);
  echo "<tr><td>$tn";
  echo "<td><input type=text name='wt[$tn]' value=$weight[$tn] size=3>";
  echo "<td style=text-align:center><input type=checkbox name='reverse[$tn]'>";
}
?>
</table>
<br><h3>Scaling of trait values</h3>
<input type=radio name=scaling value=normalized checked>Normalized, subtracting the trial mean and dividing by the standard deviation
<br><input type=radio name=scaling value=actual>Actual measured value
<br><input type=radio name=scaling value=percent>Percent of line: 
<!-- <select multiple style="vertical-align: middle"> -->
<select name=base-line>
<option value=0>Choose...</option>
<?php
foreach ($commonlines as $cl) {
  $linename = mysql_grab("select line_record_name from line_records where line_record_uid = $cl");
  echo "<option value = $cl>$linename</option>";
}
?>
</select>
<br><input type=radio name=scaling value=rank disabled>Rank in trial

<p><input type=submit value="Submit">
</form>

<?php

    } // end of if (empty($_REQUEST))
else { // Submit button was clicked.
  $weight = $_REQUEST[wt];
  $totalwt = array_sum($weight); // Needn't add up to 100.
  $reverse = $_REQUEST[reverse];
  $scaling = $_REQUEST[scaling];
  if ($_REQUEST['base-line'] != 0)
    $scaling = "percent";
  echo "Scaling method: <b>$scaling</b>";
  if ($scaling == 'percent') {
    $baselineuid = $_REQUEST['base-line'];
    $baselinename = mysql_grab("select line_record_name from line_records where line_record_uid = $baselineuid");
    echo " of <b>$baselinename</b>";
  }
  echo "<p>";
  $lines = array();
  // Fetch the data.
  if (!empty($triallist))
    $trialsubset = "and tb.experiment_uid in ($triallist)";
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
  $res = mysql_query($sql) or finish("<p>MySQL error: ". mysql_error() . "<br>Query was:<br>". $sql);
  // Read it into the master array $actual, indexed by (trait, trial, line).
  while ($row = mysql_fetch_array($res)) {
    $actual[$row[phenotypes_name]][$row[trial_code]][$row[line_record_name]] = $row[value];
    // Get the names of the lines.
    if (!in_array($row[5], $lines))  
      $lines[] = $row[5];
  }

  if ($scaling == 'normalized') {
    // To normalize we need the mean and SD of each trait/trial combination.
    foreach ($traitnames as $tn) {
      foreach ($trialnames as $trial) {
	$sum = 0; $linecount = 0; 
	foreach ($lines as $line) {
	  $sum += $actual[$tn][$trial][$line];
	  $linecount++;
	} 
	$mean[$tn][$trial] = $sum / $linecount;
	// Get the sum of (deviations from mean)^2.
	foreach ($lines as $line) 
	  $devsq += pow($mean[$tn][$trial] - $actual[$tn][$trial][$line], 2);
	$SD[$tn][$trial] = sqrt($devsq / $linecount);
      }
    }
  }

  if ($scaling == 'percent') {
    // Get the values for the "base" line used as reference.
    foreach ($traitnames as $tn) 
      foreach ($trialnames as $trial) 
	$basevalue[$tn][$trial] = $actual[$tn][$trial][$baselinename];
  }

  // Calculate Index.
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
	  $weightedval = ($weight[$tn] * scaled($actual[$tn][$trial][$line], $tn, $trial)) / $totalwt;
	  if ($reverse[$tn] == 'on')
	    $weightedval = - $weightedval;
	  $wv[$tn] = $weightedval;
	}
	$Index[$trial][$line] = round(array_sum($wv), 2);
      }
    }
  }

  // Average Index value for each line over all trials with data.:
  foreach ($lines as $line) {
    foreach ($trialnames as $trial) {
      if (!empty($Index[$trial][$line])) {
	$sumndx[$line] += $Index[$trial][$line];
	$trialct[$line]++;
      }
      if (!empty($sumndx[$line])) {
	$avgndx[$line] = round($sumndx[$line] / $trialct[$line], 2);
	// Sort with highest first.
	arsort($avgndx);
      }
    }
  }

  // Display.
  echo "<h3>Selection Index values</h3>";
  echo "<table><tr><th>Line<th>Index";
  foreach ($avgndx as $ln => $ndx) 
    echo "<tr><td>$ln<td>$ndx";
  echo "</table>";

  // Show with a column for each trait.
  echo "<h3>Details by trial</h3>";
  // table header
  echo "<table><tr><th>Trial<th>Line<th>Index";
  foreach ($traitnames as $tn)
    echo "<th>$tn";
  // table body
  foreach ($Index as $trial => $linendx) {
    // Re-sort by Index value within this trial.
    arsort($linendx);
    foreach ($linendx as $line => $ndx) {
      echo "<tr><td>$trial<td>$line<td>$ndx";
      foreach ($traitnames as $tn)
	echo "<td style=text-align:center>".$actual[$tn][$trial][$line];
	/* echo "<td style=text-align:center>".scaled($actual[$tn][$trial][$line], $tn, $trial); */
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
