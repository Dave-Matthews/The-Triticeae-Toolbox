<?php
require 'config.php';
//Need write access to update the cache table.
//include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'includes/bootstrap_curator.inc');
connect();

print "<div class='boxContent'>";
$selectedcount = count($_SESSION['selected_lines']);
echo "<h3><font color=blue>Currently Selected Lines</font>: $selectedcount</h3>";
if ($selectedcount != 0) {
  print "<textarea rows = 9>";
  foreach ($_SESSION['selected_lines'] as $lineuid) {
    $result=mysql_query("select line_record_name from line_records where line_record_uid=$lineuid") or die("invalid line uid\n");
    while ($row=mysql_fetch_assoc($result)) {
      $selval=$row['line_record_name'];
      print "$selval\n";
    }
  }
  print "</textarea><p>";
}
// Clean up all old copies.
// No, bad idea, it could be another user's file.  Use a cron job.
//array_map("unlink", glob($config['root_dir']."downloads/temp/clustertable.txt*"));

if (!isset ($_SESSION['selected_lines']) || (count($_SESSION['selected_lines']) == 0) ) {
  // No lines selected so prompt to get some.
  echo "<a href=".$config['base_url']."pedigree/line_selection.php>Select lines.</a> ";
  echo "(Patience required for more than a few hundred lines.)";
}
else {
  $sel_lines = implode(",", $_SESSION['selected_lines']);
  $delimiter =",";
  // Adapted from download/downloads.php:
  // 2D array of alleles for all markers x currently selected lines

  // Get all markers that have allele data, in marker_uid order as they are in allele_byline.alleles.
  $sql = "select marker_uid, marker_name from allele_byline_idx order by marker_uid";
  $res = mysql_query($sql) or die(mysql_error());
  while ($row = mysql_fetch_row($res)) {
    $markerids[] = $row[0];
    // First row of output file mrkData.csv is list of marker names.
    $outputheader .= $row[1] . $delimiter;
  }

  // Create cache table if necessary.
  $n = mysql_num_rows(mysql_query("show tables like 'allele_byline_clust'"));
  if ($n == 0) {
    $sql = "create table allele_byline_clust (
	      line_record_uid int(11) NOT NULL,
              line_record_name varchar(50),
	      alleles TEXT  COMMENT 'Up to 2^16 (65K) characters. Use MEDIUMTEXT for 2^24.',
              updated_on timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	      PRIMARY KEY (line_record_uid)
	    ) COMMENT 'Cache created from table allele_byline.'";
    $res = mysql_query($sql) or die (mysql_error());
    $update = TRUE;
  }
  else {
    // Update cache table if necessary. Empty?
    if(mysql_num_rows(mysql_query("select * from allele_byline_clust")) == 0)
      $update = TRUE;
    // Out of date?
    $sql = "select if( datediff(
	    (select max(updated_on) from allele_frequencies),
	    (select max(updated_on) from allele_byline_clust)
  	  ) > 0, 'need_update', 'okay')";
    $need = mysql_grab($sql);
    if ($need == 'need_update') $update = TRUE;
  }
  if ($update) {
    echo "Updating table allele_byline_clust...<p>";
    set_time_limit(300);  // Default 30sec runs out in ca. line 105.  
    mysql_query("truncate table allele_byline_clust") or die(mysql_error());
    $lookup = array('AA' => '1',
		    'BB' => '0',
		    'AB' => '0.5');
    // Compute global allele frequencies.
    $sql = "select marker_uid, aa_cnt, ab_cnt, total from allele_frequencies";
    $res = mysql_query($sql) or die(mysql_error());
    while ($row = mysql_fetch_array($res)){
      $aa_sum[$row[0]] += $row[1];
      $ab_sum[$row[0]] += $row[2];
      $total_sum[$row[0]] += $row[3];
    }
    // Store in the same order as $markerids[], i.e. table allele_byline_idx.
    foreach ($markerids as $id) {
      $afreq[$id] = ($aa_sum[$id] + 0.5 * $ab_sum[$id]) / $total_sum[$id];
      $afreq[$id] = number_format($afreq[$id], 3);
    } 
    // Read in the allele_byline table.
    $sql = "select * from allele_byline";
    $res = mysql_query($sql) or die(mysql_error());
    while ($row = mysql_fetch_array($res)) {
      $lineid = $row['line_record_uid'];
      $line = $row['line_record_name'];
      $alleles = explode(',', $row['alleles']);
      for ($i=0; $i<count($alleles); $i++) {
	if ($alleles[$i] == '' or $alleles[$i] == '--')
	  // Substitute global frequency for missing values.
	  $alleles[$i] = $afreq[$markerids[$i]];
	else
	  // Translate to numeric score.
	  $alleles[$i] = $lookup[$alleles[$i]];
      }
      $alleles = implode(',', $alleles);
      // Store in cache table.
      $sql = "insert into allele_byline_clust values (
         $lineid, '$line', '$alleles', NOW() )";
      mysql_query($sql) or die(mysql_error()."<br>Query:<br>$sql");
    }
  } // end of if($update)

  // Save the list of marker names to the output file.
  $outputheader = trim($outputheader, ",")."\n";
  // Make the filename unique to deal with concurrency.
  $time = $_GET['time'];
  if (! file_exists('/tmp/tht')) mkdir('/tmp/tht');
  $outfile = "/tmp/tht/mrkData.csv".$time;
  file_put_contents($outfile, $outputheader);

  // Get the alleles for currently selected lines, all genotyped markers.	
  $sql = "select line_record_name, alleles from allele_byline_clust
          where line_record_uid in ($sel_lines)
          order by line_record_name";
  $starttime = time();
  $res = mysql_query($sql) or die(mysql_error());
  $elapsed = time() - $starttime;
  echo "<p>Query time: $elapsed sec<br>";
  while ($row = mysql_fetch_array($res)) 
    file_put_contents($outfile, $row[0].$delimiter.$row[1]."\n", FILE_APPEND);
  
  // Get phenotype data
  $outfile = "/tmp/tht/phenoData.csv".$time;
  $outputheader = "value\n";
  $trait = $_SESSION['selected_traits'];
  $trait = $trait[0];
  if (isset($_SESSION['selected_trials'])) {
    $tmp = $_SESSION['selected_trials'];
    $experiments = implode(",",$tmp);
    file_put_contents($outfile, $experiments."\n");
  } elseif (isset($_SESSION['selected_traits'])) {
    $trait = $_SESSION['selected_traits'];
    $trait = $trait[0];
    $tmp = array();
    $sql = "SELECT distinct tb.experiment_uid
    FROM tht_base as tb, phenotype_data as pd, line_records as lr
    WHERE pd.tht_base_uid = tb.tht_base_uid
    AND lr.line_record_uid = tb.line_record_uid
    AND pd.phenotype_uid IN ($trait)
    AND lr.line_record_uid IN ($sel_lines)";
    $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
    while ($row = mysql_fetch_array($res)) {
      $exper = $row[0];
      $tmp[] = $exper;
    }
    $experiments = implode(",",$tmp);
    file_put_contents($outfile, $experiments."\n");
  } else {
    file_put_contents($outfile, "empty\n");
  }
  $empty = array();
  foreach ($tmp as $id) {
    $empty[$id] = NA;
  }
  
  if (isset($_SESSION['selected_trials'])) {
    $sql_opt = "AND tb.experiment_uid IN ($experiments)";
  } else {
    $sql_opt = "";
  }
  $sql = "SELECT lr.line_record_name as name, pd.value as value, tb.experiment_uid
  FROM tht_base as tb, phenotype_data as pd, line_records as lr
  WHERE pd.tht_base_uid = tb.tht_base_uid
  AND lr.line_record_uid = tb.line_record_uid
  AND pd.phenotype_uid IN ($trait)
  AND lr.line_record_uid IN ($sel_lines)
  $sql_opt
  order by line_record_name";
  $new_name = "";
  $delimiter = ",";
  $pheno_array = $empty;
  $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
  while ($row = mysql_fetch_array($res)) {
      $line_name = $row[0];
      $exper = $row[2];
      if ($new_name == "") {
        $new_name = $line_name;
        $pheno_array[$exper] = $row[1];
      } elseif ($new_name == $line_name){
        $pheno_array[$exper] = $row[1];
      } else {
        $pheno_str = implode(",",$pheno_array);
        file_put_contents($outfile, $new_name.$delimiter.$pheno_str."\n", FILE_APPEND);
        $pheno_array = $empty;
        $pheno_array[$exper] = $row[1];
        $new_name = $line_name;
      }
  }
  file_put_contents($outfile, $new_name.$delimiter.$pheno_str."\n", FILE_APPEND);
}

echo "</div></div></div>";

?>
