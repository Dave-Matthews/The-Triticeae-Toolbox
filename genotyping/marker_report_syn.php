<?php
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
$mysqli = connecti();

include $config['root_dir'].'theme/admin_header.php';

$sql = "select experiment_uid, trial_code from experiments, experiment_types
   where experiments.experiment_type_uid = experiment_types.experiment_type_uid
   and experiment_type_name = \"genotype\"
   order by experiment_uid desc";
$result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
while ($row=mysqli_fetch_row($result)) {
  $uid = $row[0];
  $name = $row[1];
  $trial_name_list[$uid] = $name;
}

echo "<h2>Marker Synonyms Report</h2>\n";
echo "<b>BLAST Analysis:</b> This analysis uses BLAST to find markers within the Triticeae Toolbox database that have similar sequence. ";
echo "A marker is identified as a match if either<br>\n"; 
echo "1. the sequence homology is > 99% and alignment length is > 95% of the query sequence.<br>\n";
echo "2. there is only one mismatch and the alignment length is > 95% of the query sequence.<br>\n";
echo "The analysis uses blastn v2.2.28+ with the following arguments \"-outfmt 6 -dust no -word_size 16 -task megablast -evalue 1e-08\". ";
echo "The top level page gives a summary of the matching markers for each experiment. Select the experiment link to give the second level ";
echo "of a detailed comparison for one experiment. Select the \"match to other markers\" to get the list of individual BLAST results. \n";
echo "Reference: <a target=_new href=http://www.ncbi.nlm.nih.gov/books/NBK1763>BLAST Help</a><br><br>\n";
echo "<b>Included Data:</b> GBS markers that are already anchored to the reference genome (labeled as WCSS1) where not analyzed. The BLAST database included all markers loaded in T3 as or April 26, 2015. <br><br>\n";
echo "<b>Interpretation</b>: The blast hits between a marker and itself have been removed. When an experiment is compared to itself ";
echo "you will still see a small number of matches because the markers may match within the experiment.\n";
echo "Before 2013 the markers were checked for sequence matches when imported and if a match was found a synonym was created. \n";
echo "For the large GBS experiments the marker sequence was not checked for sequence matches to the existing database of markers.<br><br>\n";

if (isset($_GET['uid1']) && isset($_GET['uid2'])) {
   $uid1 = $_GET['uid1'];
   $uid2 = $_GET['uid2'];
   $sql = "select marker1_name, marker2_name, perc, length from marker_report_synonyms
     inner join allele_frequencies af1
      on marker_report_synonyms.marker1_uid=af1.marker_uid
      and af1.experiment_uid = $uid1
      inner join allele_frequencies af2
      on marker_report_synonyms.marker2_uid=af2.marker_uid
      and af2.experiment_uid = $uid2";
   echo "<table><tr><td>marker 1<td>marker 2<td>% homology<td>length\n";
   $result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
   while ($row=mysqli_fetch_row($result)) {
      echo "<tr><td>$row[0]<td>$row[1]<td>$row[2]<td>$row[3]\n"; 
   }
   echo "</table>";
} elseif (isset($_GET['uid1'])) {
  $uid = $_GET['uid1'];

  echo "BLAST matches for experiment $trial_name_list[$uid]<br>\n";
  echo "<table>\n";
  echo "<tr><td>Experiment 1<td>Experiment 2<td>matches to markers<br>between trials\n";
  foreach ($trial_name_list as $uid2=>$name) {
      $sql = "select distinct(marker1_uid) from marker_report_synonyms
      inner join allele_frequencies af1
      on marker_report_synonyms.marker1_uid=af1.marker_uid
      and af1.experiment_uid = $uid
      inner join allele_frequencies af2
      on marker_report_synonyms.marker2_uid=af2.marker_uid
      and af2.experiment_uid = $uid2";
      $count = 0;
      unset($unique_count);
      $result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
      while ($row=mysqli_fetch_row($result)) {
          $marker_uid = $row[0];
          if (isset($unique_count[$marker_uid])) {
          } else {
            $count++;
            $unique_count[$marker_uid] = 1;
          }
      }
      echo "<tr><td>$trial_name_list[$uid]<td>$name<td><a href=genotyping/marker_report_syn.php?uid1=$uid&uid2=$uid2>$count</a>\n";
  }
  echo "</table>";
} else {
    $sql = "select experiment_uid, count(*) from allele_frequencies
    group by experiment_uid"; 
    $result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row=mysqli_fetch_row($result)) {
        $uid = $row[0];
        $count = $row[1];
        $total_marker_list[$uid] = $count;
    }
    
    echo "<table>";
    echo "<tr><td>Experiment<td>match to markers<br>in other trials<td>total markers<br>in trial<td>match/total\n";
    $sql = "select experiment_uid, count(distinct(marker1_uid)), count(marker_uid) from marker_report_synonyms, allele_frequencies
      where marker_report_synonyms.marker1_uid=allele_frequencies.marker_uid
      group by experiment_uid order by experiment_uid desc";
    $result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row=mysqli_fetch_row($result)) {
        $uid = $row[0];
        $count1 = $row[1];
        $count2 = $row[2];
        $total = $total_marker_list[$uid];
        $perc = round(100*$count1/$total,0);
        echo "<tr><td><a href=genotyping/marker_report_syn.php?uid1=$uid>$trial_name_list[$uid]</a><td>$count1<td>$total<td>$perc%\n";
    }
}
echo "</table></div>";
include $config['root_dir'].'theme/footer.php';
