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

  // Run TableReportParameters.R to calculate LSD.
  // On tcap, the imbedded '\n' doesn't work.  Use chr(10) instead.
  /* $setupR = 'oneCol <- read.csv("'.$outfile.'", header=FALSE, stringsAsFactors=FALSE)\noutFile <-c("/tmp/tht/TableReportOut.txt'.$time.'")\n'; */
  $setupR = 'oneCol <- read.csv("'.$outfile.'", header=FALSE, stringsAsFactors=FALSE)'.chr(10).'outFile <-c("/tmp/tht/TableReportOut.txt'.$time.'")'.chr(10);
  // for debugging:
  /* echo "<pre>"; system("echo '$setupR' | cat - ../R/TableReportParameters.R | R --vanilla 2>&1"); */
  exec("echo '$setupR' | cat - ../R/TableReportParameters.R | R --vanilla > /dev/null 2> /tmp/tht/stderr.txt$time");
  // Show resulting file.
  $r = fopen("/tmp/tht/TableReportOut.txt".$time,"r");
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
    }
    else {
      // Extract the first tab-terminated string.
      preg_match('/(^[^\t]*\t)/', $line, $sublines);
      $firstword = rtrim($sublines[1]);
      $linepieces = explode("\t", rtrim($line));
      if ($firstword == 'lsmeans')
	$lsmeansline = $line;
      elseif ($firstword == 'leastSigDiff') {
	$lsds = $linepieces;
	array_shift($lsds);
      }
      elseif ($firstword == 'tukeysHSD') {
	$hsds = $linepieces;
	array_shift($hsds);
      }
      elseif ($firstword == 'trialMeans') {
	$trialmeanslists = $linepieces;
	array_shift($trialmeanslists);
      }
      else {
	// It must be a continuation line of the lsmeans.
	$lsmeansline .= $line;
      }
    }
    // All lines of the file have now been read in.
    $lsmeanslists = explode("\t", $lsmeansline);
    array_shift($lsmeanslists);
    for ($i=0; $i < $rtraitcount; $i++) {
      // Result is formatted like "c(12.65, 11.915, ...)".
      $lsmeans[$i] = explode(", ", preg_replace("/^c\(|\)$/", "", $lsmeanslists[$i]));
    }
    for ($i=0; $i < $rtraitcount; $i++) {
      $trialmeans[$i] = explode(", ", preg_replace("/^c\(|\)$/", "", $trialmeanslists[$i]));
    }
  }
  fclose($r);

  // Display the table on the page.
  $traitnumber = 0;
  foreach ($traits as $trait) {
    $trtname = mysql_grab("select phenotypes_name from phenotypes where phenotype_uid = $trait");
    if (!$lsds[$traitnumber])
      $lsdround = "--";
    else
      $lsdround = round($lsds[$traitnumber], 2);
    if (!$hsds[$traitnumber])
      $hsdround = "--";
    else
      $hsdround = round($hsds[$traitnumber], 2);
    print "<table><tr><th>Trait: $trtname<br>LSD = $lsdround<br>HSD = $hsdround";
    foreach ($trials as $trial) {
      $trialname = mysql_grab("select trial_code from experiments where experiment_uid = $trial");
      print "<th><a href='display_phenotype.php?trial_code=$trialname'>$trialname</a>";
    }
    print "<th>LSmeans";
    $linenumber = 0;
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
	else
	  print "<td>--";
      }
      $lsm = round($lsmeans[$traitnumber][$linenumber], 1);
      /* $col = 10 - floor(10 * ($lsm - $mn) / ($mx - $mn)); */
      /* print "<td><font color=$color[$col]><b>$lsm</b></font>"; */
      print "<td>$lsm";
      $linenumber++;
    }
    print "<tr><td><font color=brown><b>Trial means</b></font>";
    $trialcount = count($trials);
    for ($i=0; $i < $trialcount; $i++) {
      if (!$trialmeans[$traitnumber][$i])
	$tm = "--";
      else
	$tm = round($trialmeans[$traitnumber][$i], 1);
      print "<td>$tm";
    }
    print "</table><p>";
    $traitnumber++;
  }
}

// If there's any missing data offer to remove it.
if ($missingdata) {
  if ($_GET['balance'] == 'yes')
    $cbox = "checked";
  print "<input type=checkbox $cbox onclick='javascript:balancedata(this)'> Remove lines with missing data.<P>";
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

<hr>
<div class='section' style='font-size:100%'>
<b>Legend</b><p> 
The least squares mean (<em>LSmean</em>) of a line is the best estimate of that
line's mean based on a linear model.  If a dataset has missing data, the
LSmean adjusts for the expected value of the missing data based on the
model, so that the LSmean is less sensitive to missingness than the
arithmetic mean.<p>
If two lines have the same true mean value, their estimated mean values are
only expected to differ by more than the Least Significant Difference (<em>LSD</em>)
in 5% of experiments.<p>
If a number of lines have the same true mean value, the maximum difference
between any pair of lines is only expected to exceed the Honestly
Significant Difference (<em>HSD</em>) in 5% of experiments.<p>

</div>

<?php
$footer_div=1;
include($config['root_dir'].'theme/footer.php'); 
?>
