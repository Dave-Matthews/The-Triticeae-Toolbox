<?php
/**
 * Content Status
 *
 * PHP version 5.3
 *
 * @author  Clay Birkett <clb343@cornell.edu>
 * @license http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link    http://triticeaetoolbox.org/wheat/t3_report.php
 */

require 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';

//query for count of genotyping_data table takes too long
//cache the results of the queries 
//updating the cache is done every 24 hours

$mysqli = connecti();

$user_agent = $_SERVER['HTTP_USER_AGENT'];
$accept = $_SERVER['HTTP_ACCEPT'];

if (isset($_GET['output'])) {
    $output = $_GET['output'];
} else {
    $output = "";
}
if (isset($_REQUEST['query'])) {
    $query = $_REQUEST['query'];
    $opt = $_REQUEST['opt'];
} else {
    $query = "";
    $opt = "";
}
if (isset($_POST['startdate'])) {
    $startdate = $_POST['startdate'];
} else {
    $startdate = "";
}
if (isset($_POST['enddate'])) {
    $enddate = $_POST['enddate'];
} else {
    $enddate = "";
}

$sql = "select database()";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
if ($row = mysqli_fetch_row($res)) {
    $db = $row[0];
} else {
    print "error $sql<br>\n";
}
$cachefile = '/tmp/tht/cache_' . $db . '.txt';
$sql = "SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
if ($query == 'geno') {
    $count = 0;
    include $config['root_dir'].'theme/normal_header.php';
    print "markers with no genotype data<br>\n";
    print "<table border=0>";
    print "<tr><td>marker_uid<td>marker_name\n";
    $sql = "select markers.marker_uid, markers.marker_name from markers where marker_uid NOT IN (Select marker_uid from allele_frequencies)";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row = mysqli_fetch_row($res)) {
        $marker_uid = $row[0];
        $marker_name = $row[1];
        print "<tr><td>$marker_uid<td>$marker_name\n";
        $count++;
    }
    print "</table>\n";
    print "total $count markers missing genotype data<br>\n";
} elseif ($query == 'geno2') {
    $count = 0;
    include $config['root_dir'].'theme/normal_header.php';
    print "<h1>Genotyping data by experiment</h1>\n";
    print "<table border=0>";
    print "<tr><td>Trial Code<td>experiment name<td>genotyp markers\n";
    if (preg_match('/THT/', $db)) {
        $sql = "select experiment_short_name, count(marker_uid) from experiments as e, tht_base as tb, genotyping_data as gd where e.experiment_uid = tb.experiment_uid AND gd.tht_base_uid = tb.tht_base_uid group by e.experiment_uid";
    } else {
        $sql = "select trial_code, experiment_short_name, count(marker_uid) from experiments, allele_frequencies where experiments.experiment_uid = allele_frequencies.experiment_uid group by allele_frequencies.experiment_uid";
    }
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row = mysqli_fetch_row($res)) {
        $count=$count+$row[2];
        print "<tr><td><a href='".$config['base_url']."display_genotype.php?trial_code=$row[0]'>$row[0]</a><td>$row[1]<td>$row[2]\n";
        flush();
    }
    $count = number_format($count);
    print "<tr><td>total<td><td>$count\n";
    print "</table>";
} elseif ($query == 'linegeno') {
    include $config['root_dir'].'theme/normal_header.php';
    print "Lines with genotyping data\n";
    print "<table border=0>";
    print "<tr><td>breeding program code<td>count\n";
    $sql = "select distinct(breeding_program_code) from line_records";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row = mysqli_fetch_row($res)) {
        $program_code = $row[0];
        if (preg_match("/[A-Z0-9]+/", $program_code)) {
            $sql2 = "select count(distinct(line_records.line_record_uid)) from line_records, tht_base, genotyping_data where (line_records.line_record_uid = tht_base.line_record_uid) and (tht_base.tht_base_uid = genotyping_data.tht_base_uid) and (line_records.breeding_program_code = '$program_code')";
            $res2 = mysqli_query($mysqli, $sql2) or die(mysqli_error($mysqli));
            $row2 = mysqli_fetch_row($res2);
            $count = $row2[0];
            print "<tr><td>$program_code<td>$count\n";
        }
    }
    print "</table>\n";
} elseif ($query == 'linephen') {
    include $config['root_dir'].'theme/normal_header.php';
    print "Lines with phenotype data\n";
    print "<table border=0>";
    print "<tr><td>breeding program code<td>count\n";
    $sql = "select distinct(breeding_program_code) from line_records";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row = mysqli_fetch_row($res)) {
        $program_code = $row[0];
        if (preg_match("/[A-Z0-9]+/", $program_code)) {
            $sql2 = "select count(distinct(line_records.line_record_uid)) from line_records, tht_base, phenotype_data where (line_records.line_record_uid = tht_base.line_record_uid) and (tht_base.tht_base_uid = phenotype_data.tht_base_uid) and (line_records.breeding_program_code = '$program_code')";
            $res2 = mysqli_query($mysqli, $sql2) or die(mysqli_error($mysqli));
            $row2 = mysqli_fetch_row($res2);
            $count = $row2[0];
            print "<tr><td>$program_code<td>$count\n";
        }
    }
} elseif ($query == 'Lines') {
    include $config['root_dir'].'theme/normal_header.php';
    print "Top 100 Line names ordered by creation date<br><br>\n";
    print "<form action=t3_report.php method='POST'>";
    print "<input type=hidden name=query value=Lines />";
    print "Start Date: <input type=text name=startdate />";
    print "End Date: <input type=text name=enddate />";
    print "<input type=submit /> Use date format 2012-08-27";
    print "</form><br>";
    $sql = "select line_record_uid, line_record_name, date_format(created_on,'%m-%d-%y') from line_records";
    if (empty($startdate) || empty($enddate)) {
        $sql .= " order by created_on desc limit 100";
    } else {
        $sql .= " where (created_on > '$startdate') and (created_on < '$enddate')";
        $sql .= " order by created_on desc";
    }
    print "<table border=0>";
    print "<tr><td>Line name<td>created on\n";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row = mysqli_fetch_row($res)) {
        $uid = $row[0];
        $name = $row[1];
        $date = $row[2];
        print "<tr><td><a href='".$config['base_url']."view.php?table=line_records&uid=$uid'>$name</a><td>$date\n";
    }
} elseif ($query == 'Markers') {
    include $config['root_dir'].'theme/normal_header.php';
    if ($opt == "") {
        $msg_opt = "";
    } else {
        if ($stmt = mysqli_prepare($mysqli, "select marker_type_name from marker_types where marker_type_uid = ?")) {
            mysqli_stmt_bind_param($stmt, "i", $uid);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_bind_result($stmt, $msg_opt);
        }
    }
    print "Top 100 $msg_opt names ordered by creation date<br><br>\n";
    print "<form action=t3_report.php method='POST'>";
    print "<input type=hidden name=query value=Markers />";
    print "Start Date: <input type=text name=startdate />";
    print "End Date: <input type=text name=enddate />";
    print "<input type=submit /> Use date format 2012-08-27";
    print "</form><br>";
    print "<table border=0>";
    $sql = "select markers.marker_uid, marker_name, date_format(markers.created_on,'%m-%d-%y'), marker_type_name from markers, marker_types";
    if ($opt == "") {
        $sql_opt = "";
    } else {
        $sql_opt = " and markers.marker_type_uid = $opt";
    }
    if (empty($startdate) || empty($enddate)) {
        $sql .= " where markers.marker_type_uid = marker_types.marker_type_uid $sql_opt order by markers.created_on desc limit 100";
    } else {
        $sql .= " where (markers.created_on > '$startdate') and (markers.created_on < '$enddate') $sql_opt";
        $sql .= " and markers.marker_type_uid = marker_types.marker_type_uid order by markers.created_on desc";
    }
    print "<tr><td>Marker name<td>type<td>created on\n";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row = mysqli_fetch_row($res)) {
        $uid = $row[0];
        $name = $row[1];
        $date = $row[2];
        $type = $row[3];
        print "<tr><td><a href='".$config['base_url']."view.php?table=markers&uid=$uid'>$name</a><td>$type<td>$date\n";
    }
} elseif ($query == 'PTrials') {
    include $config['root_dir'].'theme/normal_header.php';
    print "Phenotype trials ordered by creation date<br><br>\n";
    print "<table border=0>";
    print "<tr><td>Trial Code<td>Experiment Name<td>Plot Level data<td>created on\n";
    $sql = "select distinct(experiment_uid) from phenotype_plot_data";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row = mysqli_fetch_row($res)) {
        $uid = $row[0];
        $plot_level[$uid] = 1;
    }
    $sql = "select trial_code, experiment_short_name, date_format(experiments.created_on, '%m-%d-%y'), experiment_uid from experiments, experiment_types
    where experiments.experiment_type_uid = experiment_types.experiment_type_uid and experiment_types.experiment_type_name = 'phenotype'";
    if (!authenticate(array(USER_TYPE_PARTICIPANT,
                            USER_TYPE_CURATOR,
                            USER_TYPE_ADMINISTRATOR))) {
                        $sql .= " and data_public_flag > 0";
    }
    $sql .= " order by experiments.created_on desc";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row = mysqli_fetch_row($res)) {
        $trial_code = $row[0];
        $short_name = $row[1];
        $date = $row[2];
        $uid = $row[3];
        if (isset($plot_level[$uid])) {
            $type = "Yes";
        } else {
            $type = "";
        }
        print "<tr><td><a href='".$config['base_url']."display_phenotype.php?trial_code=$trial_code'>$trial_code</a><td>$short_name<td>$type<td>$date\n";
    }
} elseif ($query == 'GTrials') {
    include $config['root_dir'].'theme/normal_header.php';
    print "Trials ordered by creation date<br><br>\n";
    print "<table border=0>";
    print "<tr><td>Trial Code<td>Experiment Name<td>type<td>created on\n";
    $sql = "select trial_code, experiment_short_name, date_format(experiments.created_on, '%m-%d-%y'), experiment_type_name from experiments, experiment_types
      where experiments.experiment_type_uid = experiment_types.experiment_type_uid and experiment_types.experiment_type_name = 'genotype'";
    if (!authenticate(array(USER_TYPE_PARTICIPANT,
                            USER_TYPE_CURATOR,
                            USER_TYPE_ADMINISTRATOR))) {
                        $sql .= " and data_public_flag > 0";
    }
    $sql .= " order by experiments.created_on desc";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row = mysqli_fetch_row($res)) {
        $trial_code = $row[0];
        $short_name = $row[1];
        $date = $row[2];
        $type = $row[3];
        print "<tr><td><a href='".$config['base_url']."display_genotype.php?trial_code=$trial_code'>$trial_code</a><td>$short_name<td>$type<td>$date\n";
    }
} elseif ($query == 'cache') {
     $sql = "select count(genotyping_data_uid) from genotyping_data";
     $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
     if ($row = mysqli_fetch_row($res)) {
       $allele_count = $row[0];
     } else {
       print "error $sql<br>\n";
     }
     $sql = "select date_format(max(created_on),'%m-%d-%Y') from genotyping_data";
     $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
     if ($row = mysqli_fetch_row($res)) {
       $allele_update = $row[0];
     } else {
       print "error $sql<br>\n";
     }
     $sql = "select count(distinct(line_records.line_record_uid)) from line_records, tht_base, genotyping_data where (line_records.line_record_uid = tht_base.line_record_uid) and (tht_base.tht_base_uid = genotyping_data.tht_base_uid)";
     $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
     if ($row = mysqli_fetch_row($res)) {
        $LinesWithGeno = $row[0];
     }
     $sql = "select count(markers.marker_uid) from markers where marker_uid IN (Select marker_uid from allele_frequencies)";
     $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
     if ($row=mysqli_fetch_row($res)) {
      $MarkersWithGeno = $row[0];
     }
     $sql = "select count(markers.marker_uid) from markers where marker_uid NOT IN (Select marker_uid from allele_frequencies)";
     $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
     if ($row=mysqli_fetch_row($res)) {
       $MarkersNoGeno = $row[0];
     }

     $fp = fopen($cachefile,'w');
     fwrite($fp,"$allele_count\n");
     fwrite($fp,"$allele_update\n");
     fwrite($fp,"$LinesWithGeno\n");
     fwrite($fp,"$MarkersWithGeno\n");
     fwrite($fp,"$MarkersNoGeno\n");
     fclose($fp);
} elseif ($query == "csr1") {
   include $config['root_dir'].'theme/normal_header.php';
   print "<h3>Trials with Canopy Spectral Reflectance (CSR) data</h3>\n";
   print "<table border=0>";
   print "<tr><td>Trial Code<td>Year<td>Files loaded\n";
   $sql = "select distinct(csr_measurement.experiment_uid), experiments.trial_code, experiments.experiment_year from csr_measurement, experiments where csr_measurement.experiment_uid = experiments.experiment_uid order by experiments.experiment_year";
   $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
   while ($row = mysqli_fetch_row($res)) {
       $uid = $row[0];
       $trial_code = $row[1];
       $year = $row[2];
       $sql = "select count(measurement_uid) from csr_measurement where experiment_uid = $uid";
       $res2 = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
       if ($row2 = mysqli_fetch_row($res2)) {
           $count = $row2[0];
       } else {
           $count = "";
       }
       print "<tr><td><a href='".$config['base_url']."display_phenotype.php?trial_code=$trial_code'>$trial_code</a><td>$year<td>$count\n";
   }
   print "</table>";
} else {
  if ($output == 'excel') {
    include 'Spreadsheet/Excel/Writer.php';
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition:attachment;filename=t3_report.xls');
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
  $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));
  if ($row = mysqli_fetch_row($res)) {
   $db = $row[0];
  } else {
   print "error $sql<br>\n";
  }
  /** read in from cache */
  $cachefile = '/tmp/tht/cache_' . $db . '.txt';
  $cachetime = 24 * 60 * 60; //24 hours
  if (file_exists($cachefile) && (time() - $cachetime < filemtime($cachefile))) {
     $fp = fopen($cachefile,'r');
     $allele_count = fgets($fp);
     $allele_update = fgets($fp);
     $LinesWithGeno = fgets($fp);
     $MarkersWithGeno = fgets($fp);
     $MarkersNoGeno = fgets($fp);
     fclose($fp);
  } else {
     $sql = "select count(genotyping_data_uid) from genotyping_data";
     $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));
     if ($row = mysqli_fetch_row($res)) {
       $allele_count = $row[0];
     } else {
       print "error $sql<br>\n";
     }
     $sql = "select date_format(max(created_on),'%m-%d-%Y') from genotyping_data";
     $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
     if ($row = mysqli_fetch_row($res)) {
       $allele_update = $row[0];
     }
     $sql = "select count(distinct(line_records.line_record_uid)) from line_records, tht_base, genotyping_data where (line_records.line_record_uid = tht_base.line_record_uid) and (tht_base.tht_base_uid = genotyping_data.tht_base_uid)";
     $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
     if ($row = mysqli_fetch_row($res)) {
        $LinesWithGeno = $row[0];
     }
     $sql = "select count(markers.marker_uid) from markers where marker_uid IN (Select marker_uid from allele_frequencies)";
     $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
     if ($row=mysqli_fetch_row($res)) {
      $MarkersWithGeno = $row[0];
     }
     $sql = "select count(markers.marker_uid) from markers where marker_uid NOT IN (Select marker_uid from allele_frequencies)";
     $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
     if ($row=mysqli_fetch_row($res)) {
       $MarkersNoGeno = $row[0];
     }

     $fp = fopen($cachefile,'w');
     fwrite($fp,"$allele_count\n");
     fwrite($fp,"$allele_update\n");
     fwrite($fp,"$LinesWithGeno\n");
     fwrite($fp,"$MarkersWithGeno\n");
     fwrite($fp,"$MarkersNoGeno\n");
     fclose($fp);
  }
  $allele_count = number_format($allele_count);
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
  $sql = "select count(experiment_uid) from experiments where experiment_type_uid = 1"; 
  $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));
  if ($row = mysqli_fetch_row($res)) {
    $count = $row[0];
  } else {
    print "error $sql<br>\n";
  }
  if ($output == "excel") {
    $worksheet->write(2, 0, "Phenotype Trials submitted");
    $worksheet->write(2, 1, "$count");
  } elseif ($output == "") {
    print "<tr><td>Phenotype Trials submitted</td><td>$count<td><a href='".$config['base_url']."t3_report.php?query=PTrials'>List all trials</a></td></tr>\n";
  }
  $sql = "select count(experiment_uid) from experiments where experiment_type_uid = 2";
  $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));
  if ($row = mysqli_fetch_row($res)) {
    $count = $row[0];
  } else {
    print "error $sql<br>\n";
  }
  if ($output == "excel") {
    $worksheet->write(3, 0, "Genotype Trials submitted");
    $worksheet->write(3, 1, "$count");
  } elseif ($output == "") {
    print "<tr><td>Genotype Trials submitted</td><td>$count<td><a href='".$config['base_url']."t3_report.php?query=GTrials'>List all trials</a></td></tr>\n";
  }

  $sql = "select count(distinct(capdata_programs_uid)) from experiments";
  $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));
  if ($row = mysqli_fetch_row($res)) {
    $count = $row[0];
  } else {
    print "error $sql<br>\n";
  }
  if ($output == "excel") {
    $worksheet->write(4, 0, "CAP data programs");
    $worksheet->write(4, 1, "$count");
  } elseif ($output == "") {
    print "<tr><td>CAP data programs</td><td>$count</td></tr>\n";
    print "</table><br>";
  } 

  $sql = "select count(line_record_uid) from line_records";
  $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));
  if ($row = mysqli_fetch_row($res)) {
    $count = $row[0];
  } else {
    print "error $sql<br>\n";
  }
  if ($output == "excel") {
    $worksheet->write(5, 0, "Lines", $format_header);
    $worksheet->write(6, 0, "Line records");
    $worksheet->write(6, 1, $count);
  } elseif ($output == "") {
    print "<b>Lines</b><table><tr><td>Line records<td>$count<td><a href='".$config['base_url']."t3_report.php?query=Lines'>List or query line names by creation date</a>\n";
  }
  $sql = "select count(distinct(breeding_program_code)) from line_records";
  $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));
  if ($row = mysqli_fetch_row($res)) {
    $count = $row[0];
  } 
  if ($output == "excel") {
    $worksheet->write(7, 0, "Breeding programs");
    $worksheet->write(7, 1, $count);
  } elseif ($output == "") {
    print "<tr><td>Breeding programs</td><td>$count</td>\n";
  }
  if ($output == "excel") {
    $worksheet->write(8, 0, "Lines with genotypeing data");
    $worksheet->write(8, 1, $LinesWithGeno);
  } elseif ($output == "") {
    print "<tr><td>Lines with genotyping data<td>$LinesWithGeno<td><a href='".$config['base_url']."t3_report.php?query=linegeno'>List lines with genotyping data</a>\n";
  }
  $sql = "select count(distinct(line_records.line_record_uid)) from line_records, tht_base, phenotype_data where (line_records.line_record_uid = tht_base.line_record_uid) and (tht_base.tht_base_uid = phenotype_data.tht_base_uid)";
  $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));
  if ($row = mysqli_fetch_row($res)) {
    $count = $row[0];
  }
  if ($output == "excel") {
    $worksheet->write(9, 0, "Lines with phenotype data");
    $worksheet->write(9, 1, $count);
    $worksheet->write(10, 0, "Species");
  } elseif ($output == "") {
    print "<tr><td>Lines with phenotype data<td>$count<td><a href='".$config['base_url']."t3_report.php?query=linephen'>List lines with phenotype data</a>\n";
    print "<tr><td>Species<td>";
  }
  $count = "";
  $sql = "select pv.value from property_values pv, properties p 
          where p.name = 'species' 
          and p.properties_uid = pv.property_uid";
  $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));
  while ($row = mysqli_fetch_row($res)) {
    $count = $count . "$row[0] ";
  }
  if ($output == "excel") {
    $worksheet->write(10, 1, $count);
  } elseif ($output == "") {
    print "$count\t";
  }

  $sql = "select date_format(max(created_on),'%m-%d-%Y') from line_records";
  $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
  if ($row = mysqli_fetch_row($res)) {
    $count = $row[0];
  }
  if ($output == "excel") {
    $worksheet->write(11, 0, "latest addition");
    $worksheet->write(11, 1, $count);
  } elseif ($output == "") {
    print "<tr><td>last addition<td>$count\n";
    print "</table><br>\n";
  }

  $index= 12;
  //* Phenotype data */
  $sql = "select count(phenotype_uid) from phenotypes";
  $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
  if ($row=mysqli_fetch_row($res)) {
    $count = $row[0];
  }
  if ($output == "excel") {
    $worksheet->write($index, 0, "Phenotype Data", $format_header);
    $index++;
    $worksheet->write($index, 0, "Traits");
    $worksheet->write($index, 1, $count);
    $index++;
  } else {
    print "<b>Phenotype Data</b>\n";
    print "<table>\n";
    print "<tr><td>Traits<td>$count<td><a href='".$config['base_url']."traits.php'>Trait descriptions and units</a>\n";
  }
  $sql = "select count(phenotype_uid) from phenotype_data";
  $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
  if ($row=mysqli_fetch_row($res)) {
    $count = $row[0];
  }
    if ($output == "excel") {
      $worksheet->write($index, 0, "Total phenotype data");
      $worksheet->write($index, 1, $count);
      $index++;
    } else {
        print "<tr><td>Total phenotype data<td>$count<td><a href='".$config['base_url']."phenotype_report.php'>List phenotype data by year and trait</a>\n";
  }
  $sql = "select date_format(max(created_on),'%m-%d-%Y') from phenotype_data";
  $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
  if ($row = mysqli_fetch_row($res)) {
      $count = $row[0];
  }
  if ($output == "excel") {
      $worksheet->write($index, 0, "last addition");
      $worksheet->write($index, 1, $count);
      $index++;
  } else {
      print "<tr><td>last addition<td>$count\n";
      print "</table><br>\n";
  }

  //* CSR data */
  $sql = "select count(distinct(experiment_uid)) from csr_measurement"; 
  $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
  if ($row=mysqli_fetch_row($res)) {
    $count = $row[0];
  }
  if ($output == "excel") {
  } else {
    print "<b>Canopy Spectral Reflectance (CSR) Data</b>\n";
    print "<table>\n";
    print "<tr><td>Trials<td>$count<td><a href='".$config['base_url']."t3_report.php?query=csr1'>List of experiments</a>\n";
  }
  $sql = "select count(measurement_uid) from csr_measurement";
  $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
  if ($row=mysqli_fetch_row($res)) {
    $count = $row[0];
  }
  if ($output == "excel") {
  } else {
    print "<tr><td>Files loaded<td>$count\n";
    print "</table><br>\n";
  }

  $count = "";
  $name = "";
  if ($output == "excel") {
    $worksheet->write($index, 0, "Genotype Data", $format_header);
    $index++;
  } else {
    print "<b>Genotype Data</b>\n";
    print "<table>\n";
  }
  $sql = "select count(marker_uid), marker_type_name, markers.marker_type_uid from markers, marker_types
    where markers.marker_type_uid = marker_types.marker_type_uid
    group by markers.marker_type_uid";
  $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
  while ($row=mysqli_fetch_row($res)) {
    $count = $row[0];
    $name = $row[1];
    $marker_type_uid = $row[2];
    if ($output == "excel") {
      $worksheet->write($index, 0, "Markers $name");
      $worksheet->write($index, 1, $count);
      $index++;
    } else {
      print "<tr><td>Markers $name<td>$count<td><a href='".$config['base_url']."t3_report.php?query=Markers&opt=$marker_type_uid'>List or query markers by creation date</a>\n";
    }
  }
  if ($output == "excel") {
    $worksheet->write($index, 0, "Markers with genotyping data");
    $worksheet->write($index, 1, $MarkersWithGeno);
    $index++;
  } else {
    print "<tr><td>Markers with genotyping data<td>$MarkersWithGeno\n";
  } 
  if ($output == "excel") {
    $worksheet->write($index, 0, "Markers without genotyping data");
    $worksheet->write($index, 1, $MarkersNoGeno);
    $index++;
  } else {
    print "<tr><td>Markers without genotyping data<td>$MarkersNoGeno<td><a href='".$config['base_url']."t3_report.php?query=geno'>Markers without genotyping data</a>\n";
  }
  if ($output == "excel") {
	$worksheet->write($index, 0, "Total genotype data");
        $worksheet->write($index, 1, "$allele_count");
        $index++;
  } else {
	echo "<tr><td>Total genotype data<td>$allele_count<td><a href='".$config['base_url']."t3_report.php?query=geno2'>List genotyping data by experiment</a>";
  }

  if ($output == "excel") {
    $worksheet->write($index, 0, "last addition");
    $worksheet->write($index, 1, $allele_update);
    $index++;
  } else {
    print "<tr><td>last addition<td>$allele_update\n";
    print "</table><br>\n";
  }

if ($output == "excel") {
    $workbook->close();
} else {
    print "</div></div>";
    include $config['root_dir'] . 'theme/footer.php';
}
}
