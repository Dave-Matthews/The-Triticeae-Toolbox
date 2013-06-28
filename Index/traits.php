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

?>
<style type="text/css">
/* Make the tables more compact. */
table td { padding: 4px; }
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

if (empty($_REQUEST)) { // Initial entry to the script.
?>
Choose relative weights and a scaling method to combine the traits into an index.
<br>If smaller values of a trait are better, invert the scale.
<br>Data will come from the <a href=<?php echo $config[base_url]?>phenotype/phenotype_selection.php>currently selected trials</a>.
<br><br>
<form>
<table>
<tr><th>Trait<th>Weight<th>Inverted scale

<?php
  // User hasn't yet specified the relative weights.  Divide equally by default.
foreach ($traitnames as $tn) { 
  $weight[$tn] = intval(100 / $traitcount);
  echo "<tr><td>$tn";
  echo "<td><input type=text name='wt[$tn]' value=$weight[$tn] size=4>";
  echo "<td style=text-align:center><input type=checkbox name='invert[$tn]'>";
}
?>
</table>
<br><h3>Scaling of trait values</h3>
<input type=radio name=scaling value=normal disabled>Normalized, subtracting the trial mean and dividing by the standard deviation
<br><input type=radio name=scaling value=check disabled>Percent of trial check(s)
<br><input type=radio name=scaling value=rank disabled>Rank in trial
<br><input type=radio name=scaling value=actual checked>Actual measured value
<p><input type=submit value="Submit">
</form>

<?php

    } // end of if (empty($_REQUEST))
else { // Submit button was clicked.
  $weight = $_REQUEST[wt];
  $totalwt = array_sum($weight); // Needn't add up to 100.
  $invert = $_REQUEST[invert];
  $scaling = $_REQUEST[scaling];
  echo "Scaling method: <b>$scaling</b><p>";
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
	  $weightedval = ($weight[$tn] * $actual[$tn][$trial][$line]) / $totalwt;
	  if ($invert[$tn] == 'on')
	    $weightedval = - $weightedval;
	  $wv[$tn] = $weightedval;
	}
      }
      $Index[$trial][$line] = array_sum($wv);
    }
  }

  // Average Index value for each line over all trials with data.:
  foreach ($lines as $line) {
    foreach ($trialnames as $trial) {
      if (!empty($Index[$trial][$line])) {
	$sumndx[$line] += $Index[$trial][$line];
	$trialct[$line]++;
      }
    }
    $avgndx[$line] = $sumndx[$line] / $trialct[$line];
    // Sort with highest first.
    arsort($avgndx);
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
  foreach ($trialnames as $trial) {
    foreach ($lines as $line) {
      // If the value of any trait is missing for this line and trial, ignore.
      $missing = FALSE;
      foreach ($traitnames as $tn) {
	if (empty($actual[$tn][$trial][$line]))
	  $missing = TRUE;
      }
      // Otherwise show them.
      if (!$missing) {
	$ndx = $Index[$trial][$line];
	echo "<tr><td>$trial<td>$line<td>".$Index[$trial][$line];
	foreach ($traitnames as $tn) 
	  echo "<td style=text-align:center>".$actual[$tn][$trial][$line];
      }
    }
  }
  echo "</table>";

  /* print_h($weight); */
  /* print_h($invert); */
} // end of else Submit button was clicked. 

finish();

function finish($message) {
  echo $message;
  $footer_div = 1;
  global $config;
  include($config['root_dir'].'theme/footer.php');
  exit;
}

?>
