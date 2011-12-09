<?php

/**
 * Logged in page initialization
 */
require 'config.php';
require($config['root_dir'].'includes/bootstrap.inc');
require_once 'includes/excel/Writer.php';

#query for count of genotyping_data table takes too long
#cache the results of the queries 
#updating the cache is done every 12 hours

connect();

$user_agent = $_SERVER['HTTP_USER_AGENT'];
$accept = $_SERVER['HTTP_ACCEPT'];

if (isset($_GET['output'])) {
  $output = $_GET['output'];
} else {
  $output = "";
}
if (isset($_GET['query'])) {
  $query = $_GET['query'];
} else {
  $query = "";
}
if ($query == 'geno') {
  $count = 0;
  include($config['root_dir'].'theme/normal_header.php');
  print "markers with no genotype data<br>\n";
  print "<table border=0>";
  print "<tr><td>marker_uid<td>marker_name\n";
  $sql = "select marker_uid, marker_name from markers";
  $res = mysql_query($sql) or die(mysql_error());
  while ($row = mysql_fetch_row($res)) {
    $marker_uid = $row[0];
    $marker_name = $row[1];
    $sql = "select marker_uid from genotyping_data where marker_uid = $marker_uid"; 
    $res2 = mysql_query($sql) or die(mysql_error());
    if ($row2 = mysql_fetch_row($res2)) {  
    } else {
      print "<tr><td>$marker_uid<td>$marker_name\n";
      $count++;
    }
  }
  print "</table>\n";
  print "total $count markers missing genotype data<br>\n";
} elseif ($query == 'linegeno') {
  include($config['root_dir'].'theme/normal_header.php');
  print "Lines with genotyping data\n";
  print "<table border=0>";
  print "<tr><td>breeding program code<td>count\n";
  $sql = "select distinct(breeding_program_code) from line_records";
  $res = mysql_query($sql) or die(mysql_error());
  while ($row = mysql_fetch_row($res)) {
    $program_code = $row[0];
    if (preg_match("/[A-Z0-9]+/",$program_code)) {
    $sql2 = "select count(distinct(line_records.line_record_uid)) from line_records, tht_base, genotyping_data where (line_records.line_record_uid = tht_base.line_record_uid) and (tht_base.tht_base_uid = genotyping_data.tht_base_uid) and (line_records.breeding_program_code = '$program_code')";
    $res2 = mysql_query($sql2) or die(mysql_error());
    $row2 = mysql_fetch_row($res2);
    $count = $row2[0];
    print "<tr><td>$program_code<td>$count\n";
    }
  }
  print "</table>\n";
} elseif ($query == 'linephen') {
  include($config['root_dir'].'theme/normal_header.php');
  print "Lines with phenotype data\n";
  print "<table border=0>";  print "<tr><td>breeding program code<td>count\n";
  $sql = "select distinct(breeding_program_code) from line_records";
  $res = mysql_query($sql) or die(mysql_error());
  while ($row = mysql_fetch_row($res)) {
    $program_code = $row[0];
    if (preg_match("/[A-Z0-9]+/", $program_code)) {  
    $sql2 = "select count(distinct(line_records.line_record_uid)) from line_records, tht_base, phenotype_data where (line_records.line_record_uid = tht_base.line_record_uid) and (tht_base.tht_base_uid = phenotype_data.tht_base_uid) and (line_records.breeding_program_code = '$program_code')";
    $res2 = mysql_query($sql2) or die(mysql_error());
    $row2 = mysql_fetch_row($res2);
    $count = $row2[0];
    print "<tr><td>$program_code<td>$count\n";
    }
  }
} elseif ($query == 'Lines') {
  include($config['root_dir'].'theme/normal_header.php');
  print "Top 100 Line names ordered by creation date<br><br>\n";
  print "<table border=0>"; print "<tr><td>Line name<td>created on\n";
  $sql = "select line_record_name, date_format(created_on,'%m-%d-%y') from line_records order by created_on desc limit 100";
  $res = mysql_query($sql) or die(mysql_error());
  while ($row = mysql_fetch_row($res)) {
    $name = $row[0];
    $date = $row[1];
    print "<tr><td>$name<td>$date\n";
  }
} elseif ($query == 'Markers') {
  include($config['root_dir'].'theme/normal_header.php');
  print "Top 100 Marker names ordered by creation date<br><br>\n";
  print "<table border=0>"; print "<tr><td>Line name<td>created on\n";
  $sql = "select marker_name, date_format(created_on,'%m-%d-%y') from markers order by created_on desc limit 100";
  $res = mysql_query($sql) or die(mysql_error());
  while ($row = mysql_fetch_row($res)) {
    $name = $row[0];
    $date = $row[1];
    print "<tr><td>$name<td>$date\n";
  }
} elseif ($query == 'Trials') {
  include($config['root_dir'].'theme/normal_header.php');
  print "Trials ordered by creation date<br><br>\n";
  print "<table border=0>"; print "<tr><td>Trial Code<td>Experiment Name<td>created on\n";
  $sql = "select trial_code, experiment_short_name, date_format(created_on, '%m-%d-%y') from experiments order by created_on desc limit 100";
  $res = mysql_query($sql) or die(mysql_error());
  while ($row = mysql_fetch_row($res)) {
    $trial_code = $row[0];
    $short_name = $row[1];
    $date = $row[2];
    print "<tr><td>$trial_code<td>$short_name<td>$date\n";
  }
} else {
  if ($output == 'html') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition:attachment;filename=t3_report.xls');
  } elseif ($output == 'excel') {
    $workbook = new Spreadsheet_Excel_Writer();
    $workbook->send('t3_report.xls');
    $format_header =& $workbook->addFormat();
    $format_header->setBold();
    $format_title =& $workbook->addFormat();
    $format_title->setBold();
    $format_title->setAlign('merge');
    $worksheet =& $workbook->addWorksheet();
  } else {
    include($config['root_dir'].'theme/normal_header.php');
    print "<div class=box>";
  }
  
  $sql = "select database()";
  $res = mysql_query($sql) or die(mysql_error());
  if ($row = mysql_fetch_row($res)) {
   $db = $row[0];
  } else {
   print "error $sql<br>\n";
  }
  /** read in from cache */
  $cachefile = '/tmp/tht/cache_' . $db . '.txt';
  $cachetime = 12 * 60 * 60; //12 hours
  if (file_exists($cachefile) && (time() - $cachetime < filemtime($cachefile))) {
     $fp = fopen($cachefile,'r');
     $allele_count = fgets($fp);
     $LinesWithGeno = fgets($fp);
     $MarkersWithGeno = fgets($fp);
     $MarkersNoGeno = fgets($fp);
     fclose($fp);
  } else {
     $sql = "select count(genotyping_data_uid) from genotyping_data";
     $res = mysql_query($sql) or die(mysql_error());
     if ($row = mysql_fetch_row($res)) {
       $allele_count = $row[0];
     } else {
       print "error $sql<br>\n";
     }
     $sql = "select count(distinct(line_records.line_record_uid)) from line_records, tht_base, genotyping_data where (line_records.line_record_uid = tht_base.line_record_uid) and (tht_base.tht_base_uid = genotyping_data.tht_base_uid)";
     $res = mysql_query($sql) or die(mysql_error());
     if ($row = mysql_fetch_row($res)) {
        $LinesWithGeno = $row[0];
     }
     $sql = "select count(distinct(markers.marker_uid)) from markers, genotyping_data where (markers.marker_uid = genotyping_data.marker_uid)";
     $res = mysql_query($sql) or die(mysql_error());
     if ($row=mysql_fetch_row($res)) {
      $MarkersWithGeno = $row[0];
     }
     $sql = "select distinct count(marker_uid) from markers where not exists (select genotyping_data_uid from genotyping_data where markers.marker_uid = genotyping_data.marker_uid)";
     $res = mysql_query($sql) or die(mysql_error());
     if ($row=mysql_fetch_row($res)) {
       $MarkersNoGeno = $row[0];
     }

     $fp = fopen($cachefile,'w');
     fwrite($fp,"$allele_count\n");
     fwrite($fp,"$LinesWithGeno\n");
     fwrite($fp,"$MarkersWithGeno\n");
     fwrite($fp,"$MarkersNoGeno\n");
     fclose($fp);
  }
  $date = date_create(date('Y-m-d'));
  $date = $date->format('Y-m-d');
  if ($output == "excel") {
    $worksheet->write(0, 0, "$db Data Submission Report $date", $format_title);
    $worksheet->write(0, 1, "", $format_title);
    $worksheet->write(0, 2, "", $format_title);
    $worksheet->write(0, 3, "", $format_title);
  } elseif ($output == "") {
    print "<h2>$db Data Submission Report $date</h2>";
  }
  if($output == "") {
    print "<form action=t3_report.php method='get'>";
    print "<input type=hidden name='output' value='excel'>";
    print "<input type='submit' value='Download tables to MS Excel'>";
    print "</form><br>";
  }

  $this_week = date_create(date('Y-m-d'));
  $this_month = date_create(date('Y-m-d')); 
  $this_week->sub(new DateInterval('P7D'));
  $this_month->sub(new DateInterval('P30D'));
  $this_week = $this_week->format('Y-m-d');
  $this_month = $this_month->format ('Y-m-d');
  if ($output == "excel") {
    $worksheet->write(1, 0, "Trials", $format_header);
  } else {
    print "<b>Trials</b>\n";
    print "<table>\n";
  }
  $sql = "select count(experiment_uid) from experiments"; 
  $res = mysql_query($sql) or die(mysql_error());
  if ($row = mysql_fetch_row($res)) {
    $count = $row[0];
  } else {
    print "error $sql<br>\n";
  }
  if ($output == "excel") {
    $worksheet->write(2, 0, "Trials submitted");
    $worksheet->write(2, 1, "$count");
  } elseif ($output == "") {
    print "<tr><td>Trials submitted</td><td><a href=t3_report.php?query=Trials TITLE='List all trials'>$count</a></td></tr>\n";
  }
  $sql = "select count(distinct(capdata_programs_uid)) from experiments";
  $res = mysql_query($sql) or die(mysql_error());
  if ($row = mysql_fetch_row($res)) {
    $count = $row[0];
  } else {
    print "error $sql<br>\n";
  }
  if ($output == "excel") {
    $worksheet->write(3, 0, "CAP data programs");
    $worksheet->write(3, 1, "$count");
  } elseif ($output == "") {
    print "<tr><td>CAP data programs</td><td>$count</td></tr>\n";
    print "</table><br>";
  } 

  $sql = "select count(line_record_uid) from line_records";
  $res = mysql_query($sql) or die(mysql_error());
  if ($row = mysql_fetch_row($res)) {
    $count = $row[0];
  } else {
    print "error $sql<br>\n";
  }
  if ($output == "excel") {
    $worksheet->write(4, 0, "Lines", $format_header);
    $worksheet->write(5, 0, "Line records");
    $worksheet->write(5, 1, $count);
  } elseif ($output == "") {
    print "<b>Lines</b><table><tr><td>Line records<td><a href=t3_report.php?query=Lines TITLE='List top 100 line names ordered by creation date'>$count</a>\n";
  }
  $sql = "select count(distinct(breeding_program_code)) from line_records";
  $res = mysql_query($sql) or die(mysql_error());
  if ($row = mysql_fetch_row($res)) {
    $count = $row[0];
  } 
  if ($output == "excel") {
    $worksheet->write(6, 0, "Breeding programs");
    $worksheet->write(6, 1, $count);
  } elseif ($output == "") {
    print "<tr><td>Breeding programs</td><td>$count</td>\n";
  }
  if ($output == "excel") {
    $worksheet->write(7, 0, "Lines with genotypeing data");
    $worksheet->write(7, 1, $LinesWithGeno);
  } elseif ($output == "") {
    print "<tr><td>Lines with genotyping data<td><a href=t3_report.php?query=linegeno>$LinesWithGeno</a>\n";
  }
  $sql = "select count(distinct(line_records.line_record_uid)) from line_records, tht_base, phenotype_data where (line_records.line_record_uid = tht_base.line_record_uid) and (tht_base.tht_base_uid = phenotype_data.tht_base_uid)";
  $res = mysql_query($sql) or die(mysql_error());
  if ($row = mysql_fetch_row($res)) {
    $count = $row[0];
  }
  if ($output == "excel") {
    $worksheet->write(8, 0, "Lines with phenotype data");
    $worksheet->write(8, 1, $count);
    $worksheet->write(9, 0, "Species");
  } elseif ($output == "") {
    print "<tr><td>Lines with phenotype data<td><a href=t3_report.php?query=linephen>$count</a>\n";
    print "<tr><td>Species<td>";
  }
  $count = "";
  $sql = "select distinct(species) from line_records";
  $res = mysql_query($sql) or die(mysql_error());
  while ($row = mysql_fetch_row($res)) {
    $count = $count . "$row[0] ";
  }
  if ($output == "excel") {
    $worksheet->write(9, 1, $count);
  } elseif ($output == "") {
    print "$count\t";
  }

  $sql = "select count(line_record_uid) from line_records where created_on > '$this_week'";
  $sql = "select max(date_format(created_on,'%m-%d-%Y')) from line_records";
  $res = mysql_query($sql) or die(mysql_error());
  if ($row = mysql_fetch_row($res)) {
    $count = $row[0];
  }
  if ($output == "excel") {
    $worksheet->write(10, 0, "latest addition");
    $worksheet->write(10, 1, $count);
  } elseif ($output == "") {
    print "<tr><td>last addition<td>$count\n";
    print "</table><br>\n";
  }

  $count = 0;
  $sql = "select count(marker_uid) from markers";
  $res = mysql_query($sql) or die(mysql_error());
  if ($row=mysql_fetch_row($res)) {
    $count = $row[0];
  }
  if ($output == "excel") {
    $worksheet->write(12, 0, "Genotype Data", $format_header);
    $worksheet->write(13, 0, "Markers");
    $worksheet->write(13, 1, $count);
  } else {
    print "<b>Genotype Data</b>\n";
    print "<table>\n";
    print "<tr><td>Markers<td><a href=t3_report.php?query=Markers Title='List top 100 markers orderd by creation date'>$count</a>\n";
  }
  if ($output == "excel") {
    $worksheet->write(14, 0, "Markers with genotyping data");
    $worksheet->write(14, 1, $MarkersWithGeno);
  } else {
    print "<tr><td>Markers with genotyping data<td>$MarkersWithGeno\n";
  } 
  if ($output == "excel") {
    $worksheet->write(15, 0, "Markers without genotyping data");
    $worksheet->write(15, 1, $MarkersNoGeno);
  } else {
    print "<tr><td>Markers without genotyping data<td><a href=t3_report.php?query=geno>$MarkersNoGeno</a>\n";
  }
  if ($output == "excel") {
	$worksheet->write(16, 0, "Total genotype data");
        $worksheet->write(16, 1, "$allele_count");
  } else {
	echo "<tr><td>Total genotype data<td>$allele_count";
  }

  $sql = "select count(marker_uid) from markers where created_on > '$this_week'";
  $sql = "select max(date_format(created_on,'%m-%d-%Y')) from markers";
  $res = mysql_query($sql) or die(mysql_error());
  if ($row = mysql_fetch_row($res)) {
    $count = $row[0];
  }
  if ($output == "excel") {
    $worksheet->write(17, 0, "last addition");
    $worksheet->write(17, 1, $count);
  } else {
    print "<tr><td>last addition<td>$count\n";
    print "</table><br>\n";
  }

  $sql = "select count(phenotype_uid) from phenotypes";
  $res = mysql_query($sql) or die(mysql_error());
  if ($row=mysql_fetch_row($res)) {
    $count = $row[0];
  }
  if ($output == "excel") {
    $worksheet->write(19, 0, "Phenotype Data", $format_header);
    $worksheet->write(20, 0, "Phenotypes");
    $worksheet->write(20, 1, $count);
  } else {
    print "<b>Phenotype Data</b>\n";
    print "<table>\n";
    print "<tr><td>Phenotypes<td>$count\n";
  }
  $sql = "select count(phenotype_uid) from phenotype_data";
  $res = mysql_query($sql) or die(mysql_error());
  if ($row=mysql_fetch_row($res)) {
    $count = $row[0];
  }
    if ($output == "excel") {
      $worksheet->write(21, 0, "Total phenotype data");
      $worksheet->write(21, 1, $count);
    } else {
        print "<tr><td>Total phenotype data<td>$count\n";
  }
  $sql = "select max(date_format(created_on,'%m-%d-%Y')) from phenotype_data";
  $res = mysql_query($sql) or die(mysql_error());
  if ($row = mysql_fetch_row($res)) {
      $count = $row[0];
  }
  if ($output == "excel") {
      $worksheet->write(22, 0, "last addition");
      $worksheet->write(22, 1, $count);
  } else {
      print "<tr><td>last addition<td>$count\n";
      print "</table><br>\n";
  }

if ($output == "excel") {
    $workbook->close();
} else {
    print "</div></div>";
    include($config['root_dir'] . 'theme/footer.php');
}
}
