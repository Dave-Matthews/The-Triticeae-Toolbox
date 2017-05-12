<?php
/**
 * Download Gateway New
 *
 * PHP version 5.3
 * Prototype version 1.5.0
 *
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/cluster_getalleles.php
 *
 */

require 'config.php';
//Need write access to update the cache table.
include $config['root_dir'].'includes/bootstrap_curator.inc';
set_time_limit(3000);

$mysqli = connecti();

include $config['root_dir'].'downloads/marker_filter.php';

if (isset($_SESSION['selected_lines']) && (count($_SESSION['selected_lines']) > 0)) {
    foreach ($_SESSION['selected_lines'] as $lineuid) {
        $result=mysqli_query($mysqli, "select line_record_name from line_records where line_record_uid=$lineuid") or die("invalid line uid\n");
        while ($row=mysqli_fetch_assoc($result)) {
            $selval=$row['line_record_name'];
        }
    }
} else {
    // No lines selected so prompt to get some.
    echo "<br><a href=".$config['base_url']."pedigree/line_properties.php>Select lines.</a> ";
    echo "(Patience required for more than a few hundred lines.)";
    die();
}

$starttime = time();
if (isset($_GET['mmaf'])) {
    $min_maf = $_GET['mmaf'];
} else {
    $min_maf = 5;
}
if (isset($_GET['mmm'])) {
    $max_missing = $_GET['mmm'];
} else {
    $max_missing = 10;
}
if (isset($_GET['mml'])) {
    $max_miss_line = $_GET['mml'];
} else {
    $max_miss_line = 10;
}
?>
<script type="text/javascript" src="downloads/download_gs.js"></script>
<?php
if (isset($_SESSION['selected_lines'])) {
    $selected_lines = $_SESSION['selected_lines'];
    if (isset($_SESSION['geno_exps'])) {
        $experiment_uid = $_SESSION['geno_exps'][0];
        calculate_afe($experiment_uid, $min_maf, $max_missing, $max_miss_line);
    } else {
        calculate_af($selected_lines, $min_maf, $max_missing, $max_miss_line);
    }
}

if (!isset($_SESSION['filtered_markers'])) {
    echo "Error: filtering routine did not work<br>\n";
    die();
} elseif (isset($_SESSION['geno_exps'])) {
    echo "<br><font color=red>Error: This tool does not work with a Genotype Experiment selection</font>";
    die();
} else {
    $sel_lines = implode(",", $_SESSION['filtered_lines']);
    $delimiter =",";
  // Adapted from download/downloads.php:
  // 2D array of alleles for all markers x currently selected lines

  // Get all markers that have allele data, in marker_uid order as they are in allele_byline.alleles.
    $sql = "select marker_uid, marker_name from allele_byline_idx order by marker_uid";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row = mysqli_fetch_row($res)) {
        $markerids[] = $row[0];
        // First row of output file mrkData.csv is list of marker names.
        $outputheader .= $row[1] . $delimiter;
    }

  // Create cache table if necessary.
    $n = mysqli_num_rows(mysqli_query($mysqli, "show tables like 'allele_byline_clust'"));
    if ($n == 0) {
        $sql = "create table allele_byline_clust (
	      line_record_uid int(11) NOT NULL,
              line_record_name varchar(50),
	      alleles MEDIUMTEXT  COMMENT 'TEXT up to 2^16 (65K) characters. Use MEDIUMTEXT for 2^24.',
              updated_on timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	      PRIMARY KEY (line_record_uid)
	    ) COMMENT 'Cache created from table allele_byline.'";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        $update = true;
    } else {
    // Update cache table if necessary. Empty?
        if (mysqli_num_rows(mysqli_query($mysqli, "select line_record_uid from allele_byline_clust")) == 0) {
            $update = true;
        }
    // Out of date?
        $sql = "select if( datediff(
	    (select max(updated_on) from allele_frequencies),
	    (select max(updated_on) from allele_byline_clust)
  	  ) > 0, 'need_update', 'okay')";
        $need = mysql_grab($sql);
        if ($need == 'need_update') {
            $update = true;
        }
    }
    if ($update) {
        echo "Updating table allele_byline_clust...<p>";
        ini_set('memory_limit', '4G');
        mysqli_query($mysqli, "truncate table allele_byline_clust") or die(mysqli_error($mysqli));
        $lookup = array('AA' => '1',
        'BB' => '0',
        'AB' => '0.5');
        // Compute global allele frequencies.
        $sql = "select marker_uid, maf from allele_frequencies";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        while ($row = mysqli_fetch_array($res)) {
            $afreq[$row[0]] = $row[1];
        }
        // Read in the allele_byline table.
        $sql = "select * from allele_byline";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        while ($row = mysqli_fetch_array($res)) {
            $lineid = $row['line_record_uid'];
            $line = $row['line_record_name'];
            $alleles = explode(',', $row['alleles']);
            for ($i=0; $i<count($alleles); $i++) {
                if ($alleles[$i] == '' or $alleles[$i] == '--') {
                    // Substitute global frequency for missing values.
                    $alleles[$i] = $afreq[$markerids[$i]];
                } else {
                    // Translate to numeric score.
	            $alleles[$i] = $lookup[$alleles[$i]];
                }
          }
        $alleles = implode(',', $alleles);
        // Store in cache table.
        $sql = "insert into allele_byline_clust values (
         $lineid, '$line', '$alleles', NOW() )";
        mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
      }
  } // end of if($update)

  $sql = "select marker_uid, marker_name from allele_byline_idx order by marker_uid";
                $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
                $i=0;
                while ($row = mysqli_fetch_array($res)) {
                   $marker_list[$i] = $row[0];
                   $marker_list_name[$i] = $row[1];
                   $i++;
                }

  $markers = $_SESSION['filtered_markers'];
  foreach ($markers as $temp) {
    $marker_lookup[$temp] = 1;
  }
  // Save the list of marker names to the output file.
  //$outputheader = trim($outputheader, ",")."\n";
  $outputheader = '';
  foreach ($marker_list as $i => $marker_id) {
    $marker_name = $marker_list_name[$i];
    if (isset($marker_lookup[$marker_id])) {
      if ($outputheader == '') {
         $outputheader .= $marker_name;
      } else {
         $outputheader .= $delimiter.$marker_name;
      }
    }
  }
  $outputheader .= "\n";
  // Make the filename unique to deal with concurrency.
  $time = intval($_GET['time']);
  if (! file_exists('/tmp/tht')) {
       mkdir('/tmp/tht');
  }
  $outfile = "/tmp/tht/mrkData.csv".$time;
  file_put_contents($outfile, $outputheader);

  // Get the alleles for currently selected lines, all genotyped markers.
  foreach ($_SESSION['filtered_lines'] as $lineuid) {
    $sql = "select line_record_name, alleles from allele_byline_clust
          where line_record_uid = $lineuid";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    if ($row = mysqli_fetch_array($res)) {
      $outarray2 = array();
      $line_name = $row[0];
      $alleles = $row[1];
      //echo "$line_name $alleles\n";
      $outarray = explode(',', $alleles);
      $i=0;
      foreach ($outarray as $allele) {
        $marker_id = $marker_list[$i];
        if (isset($marker_lookup[$marker_id])) {
          $outarray2[]=$allele;
        }
        $i++;
      }
      $outarray = implode($delimiter, $outarray2);
      file_put_contents($outfile, $line_name.$delimiter.$outarray."\n", FILE_APPEND);
    }
    $elapsed = time() - $starttime;
    $_SESSION['timmer'] = $elapsed;
  }
}
