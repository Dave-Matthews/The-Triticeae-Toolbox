<?php

/*
 * Logged in page initialization
 */
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/normal_header.php');
connect();
?>

<div id="primaryContentContainer">
<div id="primaryContent">
	<div class="box">
	<?php


if(isset($_GET['species'])) {
  $species = $_GET['species'];
  print ": $species</h2>\n";
  $count = 0;
  $sql = "select data_program_code from CAPdata_programs";
  $res = mysql_query($sql) or die(mysql_error());
  while ($row=mysql_fetch_row($res)) {
    $count++;
    $code = $row[0];
  }
  print "CAP Breeding Programs: $count<br>\n";
  $count = 0;
  $sql = "select line_record_uid, line_record_name from line_records where species='$species'";
  $res = mysql_query($sql) or die(mysql_error());
  while ($row=mysql_fetch_row($res)) {
    $count++;
    $uid = $row[0];
    $name = $row[1];
    $sql = "select count(*) from tht_base, genotyping_data where (tht_base.line_record_uid = $uid) and (tht_base.tht_base_uid = genotyping_data.tht_base_uid)";
    $res2 = mysql_query($sql) or die(mysql_error());
    if ($row2 = mysql_fetch_row($res2)) {
      if ($row2[0] > 0) {
	$count2++;
      }
    }
  }
  $count3 = "";
  $sql = "select distinct(breeding_program_code) from line_records where species='$species'";
  $res = mysql_query($sql) or die(mysql_error());
  while ($row = mysql_fetch_row($res)) {
    if ($count3 == "") {
      $count3 = $row[0];
    } else {
      $count3 = $count3 . ", " . $row[0];
    }
  }
  print "Line Records: $count<br>\n";
  print "Line Records with genotyping data: $count2<br>\n";
  print "Line Records program codes: $count3<br>\n";
  $sql = "select count(distinct(trial_code)) from experiments";
  $res = mysql_query($sql) or die(mysql_error());
  if ($row = mysql_fetch_row($res)) {
    $count = $row[0];
  }
  print "Trials submitted: $count<br>\n"; 

} else {
  $sql = "select database()";
  $res = mysql_query($sql) or die(mysql_error());
  if ($row = mysql_fetch_row($res)) {
    $db = $row[0];
    print "<h2>$db Data Submission Report</h2>";
  }

  $date = date_create(date('Y-m-d'));
  $this_week = date_create(date('Y-m-d'));
  $this_month = date_create(date('Y-m-d')); 
  //echo $date->format('Y-m-d') . "<br>\n";
  $this_week->sub(new DateInterval('P7D'));
  $this_month->sub(new DateInterval('P30D'));
  $this_week = $this_week->format('Y-m-d');
  $this_month = $this_month->format ('Y-m-d');
  print "<b>Trials</b>\n";
  print "<table>\n";
  $sql = "select count(*) from experiments"; 
  $res = mysql_query($sql) or die(mysql_error());
  if ($row = mysql_fetch_row($res)) {
    $count = $row[0];
    print "<tr><td>Trials submitted</td><td>$count</td></tr>\n";
  }
  $sql = "select count(distinct(capdata_programs_uid)) from experiments";
  $res = mysql_query($sql) or die(mysql_error());
  if ($row = mysql_fetch_row($res)) {
    $count = $row[0];
    print "<tr><td>CAP data programs</td><td>$count</td></tr>\n";
  } 
  print "</table><br>";

  print "<b>Lines</b>\n";
  print "<table>\n";
  $sql = "select count(*) from line_records";
  $res = mysql_query($sql) or die(mysql_error());
  if ($row = mysql_fetch_row($res)) {
    $count = $row[0];
    print "<tr><td>Line records<td>$count\n";
  }
  $sql = "select count(distinct(breeding_program_code)) from line_records";
  $res = mysql_query($sql) or die(mysql_error());
  if ($row = mysql_fetch_row($res)) {
    $count = $row[0];
    print "<tr><td>Breeding programs</td><td>$count</td>\n";
  }
  $sql = "select count(distinct(line_records.line_record_uid)) from line_records, tht_base, genotyping_data where (line_records.line_record_uid = tht_base.line_record_uid) and (tht_base.tht_base_uid = genotyping_data.tht_base_uid)";
  $res = mysql_query($sql) or die(mysql_error());
  if ($row = mysql_fetch_row($res)) {
    $count = $row[0];
    print "<tr><td>Lines with genotyping data<td>$count\n";
  }
  $sql = "select count(distinct(line_records.line_record_uid)) from line_records, tht_base, phenotype_data where (line_records.line_record_uid = tht_base.line_record_uid) and (tht_base.tht_base_uid = phenotype_data.tht_base_uid)";
  $res = mysql_query($sql) or die(mysql_error());
  if ($row = mysql_fetch_row($res)) {
    $count = $row[0];
    print "<tr><td>Lines with phenotype data<td>$count\n";
  }
  $sql = "select count(*) from line_records where created_on > '$this_week'";
  $res = mysql_query($sql) or die(mysql_error());
  if ($row = mysql_fetch_row($res)) {
    $count = $row[0];
    print "<tr><td>added since $this_week <td>$count\n";
  }
  $sql = "select count(*) from line_records where created_on > '$this_month'";
  $res = mysql_query($sql) or die(mysql_error());
  if ($row = mysql_fetch_row($res)) {
    $count = $row[0];
    print "<tr><td>added since $this_month <td>$count\n";
  }
  print "</table><br>\n";

  $count = 0;
  print "<b>Genotype Data</b>\n";
  print "<table>\n";
  $sql = "select count(*) from markers";
  $res = mysql_query($sql) or die(mysql_error());
  if ($row=mysql_fetch_row($res)) {
    $count = $row[0];
    print "<tr><td>Markers<td>$count\n";
  }
  $sql = "select count(distinct(markers.marker_uid)) from markers, genotyping_data where (markers.marker_uid = genotyping_data.marker_uid)";
  $res = mysql_query($sql) or die(mysql_error());
  if ($row=mysql_fetch_row($res)) {
    $count = $row[0];
    print "<tr><td>with genotyping data<td>$count\n";
  }  
  $sql = "select count(*) from genotyping_data";
  $res = mysql_query($sql) or die(mysql_error());
  $row = mysql_fetch_row($res);
  $count = $row[0];
  $count = number_format($count, 0, 0, ',');
  //printf("<tr><td>Total genotype data<td>%f\n",$count);
  print  "<tr><td>Total genotype data<td>$count\n";

  $sql = "select count(*) from markers where created_on > '$this_week'";
  $res = mysql_query($sql) or die(mysql_error());
  if ($row = mysql_fetch_row($res)) {
    $count = $row[0];
    print "<tr><td>markers added since $this_week <td>$count\n";
  }
  $sql = "select count(*) from markers where created_on > '$this_month'";
  $res = mysql_query($sql) or die(mysql_error());
  if ($row = mysql_fetch_row($res)) {
    $count = $row[0];
    print "<tr><td>markers added since $this_month <td>$count\n";
  }
  print "</table><br>\n";

  print "<b>Phenotype Data</b>\n";
  print "<table>\n";
  $sql = "select count(*) from phenotypes";
  $res = mysql_query($sql) or die(mysql_error());
  if ($row=mysql_fetch_row($res)) {
    $count = $row[0];
    print "<tr><td>Phenotypes<td>$count\n";
  }
  $sql = "select count(*) from phenotype_data";
  $res = mysql_query($sql) or die(mysql_error());
  if ($row=mysql_fetch_row($res)) {
    $count = $row[0];
    print "<tr><td>Total phenotype data<td>$count\n";
  }
  $sql = "select count(*) from phenotype_data where created_on > '$this_week'";
  $res = mysql_query($sql) or die(mysql_error());
  if ($row = mysql_fetch_row($res)) {
    $count = $row[0];
    print "<tr><td>data added since $this_week <td>$count\n";
  }
  $sql = "select count(*) from phenotype_data where created_on > '$this_month'";
  $res = mysql_query($sql) or die(mysql_error());
  if ($row = mysql_fetch_row($res)) {
    $count = $row[0];
    print "<tr><td>data added since $this_month <td>$count\n";
  }


  print "</table>\n";



}


 
$sql = "select distinct(date_format(created_on,'%m-%d-%Y')),count(created_on) from line_records group by created_on";
$res = mysql_query($sql) or die(mysql_error());
?>
</div>
</div>

<?php include("theme/footer.php");?>
