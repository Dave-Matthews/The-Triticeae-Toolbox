<?php
// analyze/table.php, DEM jun2015
// Display Lines vs. Trials for a Trait in tabular format.

require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/admin_header.php');
include('heatmap_colors.inc');
connect();
$mysqli = connecti();
?>
<style type=text/css>
table tr { min-height: 10px; }
table td { padding: 2px; text-align: center; background-color:white}
</style>

<h1>Summary of Trait values in selected Trials</h1>

<?php 
$traits = $_SESSION['selected_traits'];
$trials = $_SESSION['selected_trials'];
if (!$traits OR !$trials) 
  echo "Please select at least one <a href='$config[base_url]phenotype/phenotype_selection.php'>Trait and Trial</a>.<p>";
else {
  // Retrieve the data into array $vals.
  foreach ($traits as $trait) {
    foreach ($trials as $trial) {
      $trialname = mysql_grab("select trial_code from experiments where experiment_uid = $trial");
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
	if ($max[$trait][$trial] < $val OR !$max[$trait][$trial])
	  $max[$trait][$trial] = $val;
	if ($min[$trait][$trial] > $val OR !$min[$trait][$trial])
	  $min[$trait][$trial] = $val;
	$vals[$trait][$trial][$linename] = $val;
      }
    }
    $lines = array_unique($lines);
    sort($lines);
  }

  // Optionally remove Lines that have any missing values for a Trait/Trial.
  foreach ($traits as $trait) {
    foreach ($trials as $trial) {
      foreach ($lines as $line) {
	if (!$vals[$trait][$trial][$line]) {
	  $sparselines[] = $line;
	  $missingdata = TRUE;
	}
      }
      if (!empty($sparselines)) {
	$sparselines = array_unique($sparselines);
	sort($sparselines);
      }
    }
  }
  if ($_GET['balance'] == 'yes') 
    $lines = array_diff($lines, $sparselines);

  // Write $vals to a temporary file for R to calculate LSDs.
  // output row = Traitname, Trialname, Linename, Value
  foreach ($traits as $trait) {
    $trtname = mysql_grab("select phenotypes_name from phenotypes where phenotype_uid = $trait");
    foreach ($trials as $trial) {
      $trlname = mysql_grab("select trial_code from experiments where experiment_uid = $trial");
      foreach ($lines as $line) {
	$val = $vals[$trait][$trial][$line];
	if (!$val) 
	  $val = "NA";
	$outdata .= $trtname .",". $trlname .",". $line .",". $val ."\n";
      }
    }
  }
  // Make the filename unique to deal with concurrency.
  $time = time();
  $outfile = "/tmp/tht/trialdata.csv".$time;
  if (! file_exists('/tmp/tht')) mkdir('/tmp/tht');
  file_put_contents($outfile, $outdata);

  // Display the table on the page.
  foreach ($traits as $trait) {
    $trtname = mysql_grab("select phenotypes_name from phenotypes where phenotype_uid = $trait");
    print "<table><tr><th>Trait: <b>$trtname</b>";
    foreach ($trials as $trial) {
      $trialname = mysql_grab("select trial_code from experiments where experiment_uid = $trial");
      print "<th><a href='display_phenotype.php?trial_code=$trialname'>$trialname</a>";

      $mx = $max[$trait][$trial]; $mn = $min[$trait][$trial];
      /* echo "max $mx; min $mn<br>"; */
    }
    foreach ($lines as $line) {
      print "<tr><td>$line";
      foreach ($trials as $trial) {
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
	}
      }
    }
    print "</table><p>";
  }

  // If there's any missing data offer to remove it.
  if ($missingdata) {
    if ($_GET['balance'] == 'yes')
      $cbox = "checked";
    print "<input type=checkbox $cbox onclick='javascript:balancedata(this)'> Remove lines with missing data.<P>";
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
</script>

<div class='section' style='font-size:90%'>
<b>Legend</b><br> 
  LSD to be calculated among Lines in each Trial, and among Trials for each Line
</div>

<?php
$footer_div=1;
include($config['root_dir'].'theme/footer.php'); 
?>
