<?php

/*
 * Logged in page initialization
 */
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
connect();

$user_agent = $_SERVER['HTTP_USER_AGENT'];
$accept = $_SERVER['HTTP_ACCEPT'];
if (preg_match("/Mobile/i","$user_agent")) {
}

if (isset($_GET['output'])) {
  header('Content-Type: application/vnd.ms-excel');
  header('Content-Disposition:attachment;filename=t3_report.xls');
} else {
  include($config['root_dir'].'theme/normal_header.php');
  print "<div id='primaryContentContainer'>";
  print "<div id='primaryContent'>";
  print "<div class='box'>";
}
  $date = date_create(date('Y-m-d'));
  $date = $date->format('Y-m-d');
  $sql = "select database()";
  $res = mysql_query($sql) or die(mysql_error());
  if ($row = mysql_fetch_row($res)) {
    $db = $row[0];
    print "<h2>$db Data Submission Report $date</h2>";
  }
  if(!isset($_GET['output'])) {
    print "<a href=http:t3_report.php?output=excel>Export to MS Excel</a><br><br>\n";
  }

  $this_week = date_create(date('Y-m-d'));
  $this_month = date_create(date('Y-m-d')); 
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
  print "<tr><td>Species<td>";
  $sql = "select distinct(species) from line_records";
  $res = mysql_query($sql) or die(mysql_error());
  while ($row = mysql_fetch_row($res)) {
    $count = $row[0];
    print "$count\t";
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

if(isset($_GET['output'])) {
} elseif ($mobile) {
} else {
  print "</div></div>";
  include("theme/footer.php");
}
