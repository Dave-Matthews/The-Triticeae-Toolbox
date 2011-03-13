<?php
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/admin_header.php');
connect();

$defaultclusters = 5;
if (isset($_SESSION['selected_lines'])) {
  $linecount = count($_SESSION['selected_lines']);
  $defaultclusters = min($defaultclusters, $linecount - 1);
 }
?>

<div id="primaryContentContainer">
  <div id="primaryContent">
  <h1>Cluster Lines by Genotype</h1>
  <div class="section">

  <p>  This R program will cluster the 
  <a href="<?php echo $config['base_url']; ?>pedigree/line_selection.php">
  <font color=blue>currently selected lines</font></a> according to their 
  alleles for all markers, using "pam" (Partitioning Around Medoids).  

  <p>The results will be displayed as a two-dimensional PCA plot with
  the clusters color-coded.  If you enter some line names below, the legend will indicate
  which cluster they fall into.  

  <p>When you have examined the results you can select the clusters you want to work with.

  <form action="cluster_show.php">
  <p>How many clusters should pam divide the lines into?  &nbsp;&nbsp;
  <input type=text name="clusters" value=<?php echo $defaultclusters ?> size="1">  (Maximum 8.)
  <p>Lines to label in the legend:<br>
  <textarea name="labels" rows=4></textarea>
  E.g. MERIT, FEG148-16, ND24205, VA07B-54

<?php 
// Use the incoming value of $time instead of a new one.  Does it work?
if (isset($_GET['time'])) $time = $_GET['time'];
 else $time = date("U");
print "<input type='hidden' name='time' value=$time>";
?>
  <p><input type="submit" value="Analyze">
  </form>
  </div>

<?php
// If we entered the script having picked a cluster in cluster_show.php,
// load them into $_SESSION['selected_lines'].
if (isset($_GET['mycluster'])) {
  $mycluster = $_GET['mycluster'];
  $where_in = "";
  $clustertable = file($config['root_dir']."downloads/temp/clustertable.txt".$time);
  unlink($config['root_dir']."downloads/temp/clustertable.txt".$time);
  $clustertable = preg_replace("/\n/", "", $clustertable);
  // Remove first line, "x".
  array_shift($clustertable);
  for ($i=0;$i<count($clustertable);$i++) {
    for ($j=0;$j<count($mycluster);$j++) {
      $line = explode("\t", $clustertable[$i]);
      if ($line[1] == $mycluster[$j]) {
	// Build query for line_record_uids for these names.
	$where_in .= "'".$line[0]."',";
      }
    }
  }
  $where_in = trim($where_in, ",");
  $query = "select line_record_uid, line_record_name 
     from line_records where line_record_name in (".$where_in.")
     order by line_record_name";
  $result = mysql_query($query) or die(mysql_error());
  $_SESSION['selected_lines'] = array();
  while ($row = mysql_fetch_row($result)) {
    array_push($_SESSION['selected_lines'], $row[0]);
  }
}

print "<div class='boxContent'>";
$selectedcount = count($_SESSION['selected_lines']);
echo "<h3><font color=blue>Currently selected lines</font>: $selectedcount</h3>";
if ($selectedcount != 0) {
  print "<textarea rows = 9>";
  foreach ($_SESSION['selected_lines'] as $lineuid) {
    $result=mysql_query("select line_record_name from line_records where line_record_uid=$lineuid") or die("invalid line uid\n");
    while ($row=mysql_fetch_assoc($result)) {
      $selval=$row['line_record_name'];
      print "$selval\n";
    }
  }
  print "</textarea>";
 }
// Clean up all old copies.
// No, bad idea, it could be another user's file.  Use a cron job.
//array_map("unlink", glob($config['root_dir']."downloads/temp/clustertable.txt*"));

// Adapted from download/downloads.php:
// 2D array of alleles for all markers x currently selected lines
if (!isset ($_SESSION['selected_lines']) || (count($_SESSION['selected_lines']) == 0) ) {
  // No lines selected so prompt to get some.
  echo "<a href=".$config['base_url']."pedigree/line_selection.php>Select lines.</a> ";
  echo "(Patience required for more than a few hundred lines.)";
 }
 else {
   $lines = implode(",", $_SESSION['selected_lines']);
   $delimiter =",";
      
   // Get all markers that have allele data, and their global allele frequencies.
   $sql = "select markers.marker_uid, marker_name, avg(aa_freq), avg(ab_freq)
    from markers, allele_frequencies
    where markers.marker_uid = allele_frequencies.marker_uid
    group by marker_name
    order by markers.marker_uid";
   $res = mysql_query($sql) or die(mysql_error());
   while ($row = mysql_fetch_array($res)){
     $marker_names[] = $row["marker_name"];
     $marker_uid[] = $row["marker_uid"];
     // Calculate the mean frequency of A over all this marker's alleles in the database.
     $afreq = number_format($row["avg(aa_freq)"] + 0.5*($row["avg(ab_freq)"]), 3);
     // "Empty" array of default allele scores, to be overwritten with the 
     // germplasm line's allele if it was determined.
     $empty[$row["marker_name"]] = $afreq;
     $outputheader .= $row["marker_name"].$delimiter;
   }
   // Save the list of marker names to the output file.
   // Todo: Make the filename unique to deal with concurrency.
   $outputheader = trim($outputheader, ",")."\n";
   $outfile = $config['root_dir']."downloads/temp/mrkData.csv".$time;
   file_put_contents($outfile, $outputheader);

   $markers = implode(",",$marker_uid);
   $lookup = array(
		  'AA' => '1',
		  'BB' => '0',
		  'AB' => '0.5',
		  '--' => '--'
		  );
   // Get the alleles for currently selected lines, all genotyped markers.	
   $sql = "SELECT lr.line_record_name, m.marker_name AS markername,
                    CONCAT(a.allele_1,a.allele_2) AS value
  	  FROM
            markers as m,
            line_records as lr,
            alleles as a,
            tht_base as tb,
            genotyping_data as gd
	  WHERE
            a.genotyping_data_uid = gd.genotyping_data_uid
	      AND m.marker_uid = gd.marker_uid
	      AND gd.marker_uid IN ($markers)
	      AND tb.line_record_uid = lr.line_record_uid
	      AND gd.tht_base_uid = tb.tht_base_uid
              AND lr.line_record_uid IN ($lines)
	  ORDER BY lr.line_record_name, m.marker_uid";
   $starttime = time();
   $res = mysql_query($sql) or die(mysql_error());
   $elapsed = time() - $starttime;
   $numrows = number_format(mysql_num_rows($res));
   echo "<p>Query time: $elapsed sec<br>";
   echo "$numrows alleles<br>";

  $outarray = $empty;
  $cnt = 0;
  $starttime = time();
  while ($row = mysql_fetch_array($res)){
    // First time through loop.
    if ($cnt==0) {
      $cnt = 1;
      $last_line = $row['line_record_name'];
    }
    if ($last_line != $row['line_record_name']) {  
      // Done collecting alleles for this germplasm line.  Output it.
      $mname = $row['markername'];				
      if ($lookup[$row['value']] != "--") {
	// (For missing data, use the default value.)
	$outarray[$mname] = $lookup[$row['value']];
      }
      $outarray = implode($delimiter,$outarray);
      file_put_contents($outfile, $last_line.$delimiter.$outarray."\n", FILE_APPEND);
      // Reset for the next line.
      $outarray = $empty;
      $last_line = $row['line_record_name'];
    } 
    else {
      // Still collecting alleles for the current germplasm line.
      $mname = $row['markername'];				
      if ($lookup[$row['value']] != "--") {
	// (For missing data, use the default value.)
	$outarray[$mname] = $lookup[$row['value']];
      }
    }
  }
  $elapsed = time() - $starttime;
  echo "Processing time: $elapsed sec";

  //Old note from downloads.php.  Still relevant?
  // //NOTE: there is a problem with the last line logic here. Must fix.
  //Save data from the last line.
  $outarray = implode($delimiter,$outarray);
  file_put_contents($outfile, $last_line.$delimiter.$outarray."\n", FILE_APPEND);
 }

echo "</div></div></div>";
$footer_div=1;
include($config['root_dir'].'theme/footer.php'); 

?>