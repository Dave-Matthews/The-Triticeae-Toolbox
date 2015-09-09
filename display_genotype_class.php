<?php
/**
 * Report for a single genotyping experiment.
 *
 * PHP version 5.3
 *
 * @author  Clay Birkett <clb343@cornell.edu>
 * @license http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link    http://triticeaetoolbox.org/wheat/display_genotype.php
 *
 * dem 23mar12 Handle large dataset downloads. Output one row at a time
 *         instead of catenating the whole thing into $output first.
 * J.Lee 5/9/2011 Fix problem with query while restricting mmaf and max missing
 *         values, prevent download operation when 0 markers match condition.
 * J.Lee 8/17/2010 Modify alelle download to work in Linux and Solaris
 */

/** Using a PHP class to implement the report feature
 *
 * @author  Clay Birkett <clb343@cornell.edu>
 * @license http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link    http://triticeaetoolbox.org/wheat/display_genotype.php
 **/

class ShowData
{
    public $delimiter = "\t";
    /**
     * Using the class's constructor to decide which action to perform
     *
     * @param string $function action to perform
     */
    public function __construct($function = null)
    {
        switch ($function) {
            case 'typeTabDelimiter':
                $this->type_Tab_Delimiter();  /* Displaying in tab delimited fashion */
                break;
            case 'typeTabDelimiterGBS':
                $this->type_Tab_Delimiter_GBS();
                break;
            case 'select_lines':
                $this->typeSelectLines();
                break;
            case 'select_markers':
                $this->typeSelectMarkers();
                break;
            default:
                $this->typeData();
                break;
        }
    }

    /**
     * Store the lines from this experiment in a session variable, and jump to Select by Properties.
     *
     * @return null
     */
    private function typeSelectLines()
    {
        $_SESSION[selected_lines] = explode(",", $_POST[linelist]);
        echo "<meta http-equiv=\"refresh\" content=\"0;url=".$config['base_url']."pedigree/line_properties.php\">";
    }

    /** Store the genotype experiment selection in a session variable, and jump to genotype experiments list*/
    private function typeSelectMarkers()
    {
        global $mysqli;
        $_SESSION[selected_lines] = explode(",", $_POST[linelist]);
        $exps_str = $_POST[genoexp];
        $experiments = explode(',', $exps_str);
        $_SESSION['geno_exps'] = $experiments;
        $sql = "select count(marker_uid) from allele_bymarker_exp_101 where experiment_uid in ($exps_str)";
        if ($stmt = mysqli_prepare($mysqli, "select count(marker_uid) from allele_bymarker_exp_101 where experiment_uid in (?)")) {
            mysqli_stmt_bind_param($stmt, "s", $exps_str);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $countMarkers);
            if (mysqli_stmt_fetch($stmt)) {
                $_SESSION['geno_exps_cnt'] = $countMarkers;
                echo "<meta http-equiv=\"refresh\" content=\"0;url=".$config['base_url']."genotyping/genotype_selection.php\">";
            } else {
                echo "Error: no markers could be found";
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "Error: " . mysqli_error($mysqli);
        }
    }

    // The wrapper action for the type1 download. Handles outputting the header
    // and footer and calls the first real action of the type1 download.
    private function typeData()
    {
        global $config;
        include $config['root_dir'].'theme/admin_header.php';

        $trial_code=$_GET['trial_code'];
        echo " <h2>Genotyping experiment ".$trial_code. "</h2>";
        $this->type_DataInformation($trial_code);

        $footer_div = 1;
        include $config['root_dir'].'theme/footer.php';
        ?>
        <script src="//code.jquery.com/jquery-1.11.1.js"></script>
        <script type="text/javascript" src="display_genotype.js"></script>
        <?php
    }

    private function type_DataInformation($trial_code)
    {
        global $mysqli;
        $line_ids = array();
        $sql = "SELECT CAPdata_programs_uid, experiment_type_uid, experiment_uid, experiment_short_name FROM experiments where trial_code = ?";
        if (!authenticate(array(USER_TYPE_PARTICIPANT,
                            USER_TYPE_CURATOR,
                            USER_TYPE_ADMINISTRATOR))) {
                        $sql .= " and data_public_flag > 0";
        }

        if ($stmt = mysqli_prepare($mysqli, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $trial_code);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $CAPdata_programs_uid, $experiment_type_uid, $experiment_uid, $experiment_short_name);
            if (mysqli_stmt_fetch($stmt)) {
                mysqli_stmt_close($stmt);
                $sql_data_code = "SELECT data_program_code, data_program_name FROM CAPdata_programs where CAPdata_programs_uid = '".$CAPdata_programs_uid."' ";
                $res_data_code = mysqli_query($mysqli, $sql_data_code) or die("Error: unable to retrieve CAP data info from data prog id.<br>".mysqli_error($mysqli));
                $row_data_code = mysqli_fetch_assoc($res_data_code);
                $data_program_code = $row_data_code['data_program_code'];
                $data_program_name = $row_data_code['data_program_name'];
            } else {
                mysqli_stmt_close($stmt);
                echo "Error: no experiment found";
                return;
            }
        } else {
            echo "Error: " . mysqli_error($mysqli);
        }

        $sql_lines = "select line_record_uid from tht_base where experiment_uid = $experiment_uid";
        $res_lines = mysqli_query($mysqli, $sql_lines) or die("Error: unable to retrieve lines for this experiment.<br>" . mysqli_error($mysqli) . $sql_lines);
        while ($line = mysqli_fetch_row($res_lines)) {
            $line_ids[] = $line[0];
        }
        $line_total = count($line_ids);
        if ($line_total == 0) {
            $sql_lines = "select line_index from allele_bymarker_expidx where experiment_uid = $experiment_uid";
            $res_lines = mysqli_query($mysqli, $sql_lines) or die("Error: unable to retrieve lines for this experiment.<br>" . mysqli_error($mysqli));
            if ($line = mysqli_fetch_row($res_lines)) {
                $gbs_exp = "yes";
                $tmp = $line[0];
                $line_ids = json_decode($tmp, true);
                $line_total = count($line_ids);
                $line_list = implode(",", $line_ids);
            }
        } else {
            $line_list = implode(",", $line_ids);
        }

        $sql_Gen_Info = "SELECT * FROM genotype_experiment_info where experiment_uid = '".$experiment_uid."' ";
        $res_Gen_Info = mysqli_query($mysqli, $sql_Gen_Info) or die("Error: No experiment information for genotype experiment $trial_code..<br> " .mysqli_error($mysqli));
        $row_Gen_Info = mysqli_fetch_assoc($res_Gen_Info);
        $manifest_file_name = $row_Gen_Info['manifest_file_name'];
        $cluster_file_name = $row_Gen_Info['cluster_file_name'];
        $OPA_name = $row_Gen_Info['OPA_name'];
        $sample_sheet_filename = $row_Gen_Info['sample_sheet_filename'];
        $raw_datafile_archive = $row_Gen_Info['raw_datafile_archive'];
        $genotype_experiment_info_uid = $row_Gen_Info['genotype_experiment_info_uid'];
        $comments = $row_Gen_Info['comments'];
        $platform_uid = $row_Gen_Info['platform_uid'];
        $sql = "SELECT platform_name from platform where platform_uid = $platform_uid";
        $res = mysqli_query($mysqli, $sql) or die("Error: No platform information for genotype experiment $trial_code..<br> " .mysqli_error($mysqli));
        $row = mysqli_fetch_assoc($res);
        $platform_name = $row['platform_name'];
        //$dataset = mysql_grab("select datasets_uid from datasets_experiments where experiment_uid = $experiment_uid");
        $sql_DS = "select datasets_uid from datasets_experiments where experiment_uid = $experiment_uid";
        $res_DS = mysqli_query($mysqli, $sql_DS) or die(mysqli_error($mysqli));
        while ($row_DS = mysqli_fetch_assoc($res_DS)) {
            $dataset = $row_DS['datasets_uid'];
            $sql_BP = "select cp.data_program_name, cp.data_program_code
               from datasets ds, CAPdata_programs cp
	       where ds.datasets_uid = $dataset
	       and ds.CAPdata_programs_uid = cp.CAPdata_programs_uid";
            $res_BP = mysqli_query($mysqli, $sql_BP) or die(mysqli_error($mysqli));
            $row_BP = mysqli_fetch_assoc($res_BP);
            if ($breeding_program_name == "") {
                $breeding_program_name = $row_BP['data_program_name'] . " (" . $row_BP['data_program_code'] . ")";
            } else {
                $breeding_program_name .= ", " . $row_BP['data_program_name'] . " (" . $row_BP['data_program_code'] . ")";
            }
        }
    if (isset($_GET['mm']) && is_numeric($_GET['mm'])) {
      $max_missing = $_GET['mm'];
    } else {
      $max_missing = 10; //IN PERCENT
    }
    if ($max_missing > 100)
      $max_missing = 100;
    elseif ($max_missing < 0)
      $max_missing = 0;
    if (isset($_GET['mmaf']) && is_numeric($_GET['mm'])) {
      $min_maf = $_GET['mmaf'];
    } else {
      $min_maf = 5; //IN PERCENT
    }
    if ($min_maf > 100)
      $min_maf = 100;
    elseif ($min_maf < 0)
      $min_maf = 0;

    $sql_mstat = "SELECT marker_uid, maf, missing, total 
		    FROM allele_frequencies
		    WHERE experiment_uid = $experiment_uid";
    $res = mysqli_query($mysqli, $sql_mstat) or
      die("Error: Unable to sum allele frequency values.<br>".mysqli_error($mysqli));
    $num_mark = mysqli_num_rows($res);
    $num_maf = $num_miss = 0;

   $count_remain = 0;
    while ($row = mysqli_fetch_array($res)) {
        $maf = $row["maf"];
        $miss = $row["missing"];
        if ($row["total"] > 0) {
            $miss = round(100*$miss/$row["total"], 1);
            if ($maf > $min_maf) {
                $num_maf++;
            }
            if ($miss > $max_missing) {
                $num_miss++;
            }
            if (($miss < $max_missing) and ($maf > $min_maf)) {
                $count_remain++;
            }
        }
    }

    echo "<h3>Description</h3><p>";
    echo "<table>";
    echo "<tr> <td>Experiment Short Name</td><td>".$experiment_short_name."</td></tr>";
    echo "<tr> <td>Platform</td><td>".$platform_name."</td></tr>";
    echo "<tr> <td>Data Program</td><td>".$data_program_name." (".$data_program_code.")</td></tr>";
    echo "<tr> <td>Breeding Program</td><td>".$breeding_program_name."</td></tr>";
    echo "<tr> <td>OPA Name</td><td>".$row_Gen_Info['OPA_name']."</td></tr>";
    echo "<tr> <td>Processing Date</td><td>".$row_Gen_Info['processing_date']."</td></tr>";
    echo "<tr> <td>Software</td><td>".$row_Gen_Info['analysis_software']."</td></tr>";
    echo "<tr> <td>Software version</td><td>".$row_Gen_Info['BGST_version_number']."</td></tr>";
    echo "<tr> <td>Comments</td><td>".$row_Gen_Info['comments']."</td></tr>";
    echo "</table><p>";
?>

<h3>Download</h3>
<b><?php echo ($num_mark) ?></b> markers were assayed for <b><?php echo ($line_total) ?></b> lines.
<form method=POST action="<?php echo $SERVER[PHP_SELF] ?>">
<input type=hidden name=function value=select_lines>
<input type=hidden name=linelist value=<?php echo "\"$line_list\""; ?>>
<input type="submit" value="Select lines" style="color:blue">
</form>
<form method=POST action="<?php echo $SERVER[PHP_SELF] ?>">
<input type=hidden name=function value=select_markers>
<input type=hidden name=linelist value=<?php echo "\"$line_list\""; ?>>
<input type=hidden name=genoexp value=<?php echo "\"$experiment_uid\""; ?>>
<input type="submit" value="Select experiment" style="color:blue"> (lines and markers)
</form>

<p>
<b><?php echo ($num_miss) ?></b> markers are missing at least <b><?php echo ($max_missing) ?></b>% of measurements.<br>
<b><?php echo ($num_maf) ?></b> markers have a minor allele frequency (MAF) larger than <b><?php echo ($min_maf) ?></b>%.<br>
<b><?php echo ($count_remain) ?></b> markers remaining<br>
Maximum Missing Data: <input type="text" name="mm" id="mm" size="1" value="<?php echo ($max_missing) ?>" />%&nbsp;&nbsp;&nbsp;&nbsp;
  Minimum MAF: <input type="text" name="mmaf" id="mmaf" size="1" value="<?php echo ($min_maf) ?>" />%&nbsp;&nbsp;&nbsp;&nbsp;
  <input type="button" value="Refresh" onclick="javascript:mrefresh('<?php echo $trial_code ?>');return false;" /><br>
  <div id="status"></div>
  <div id="results">
  <img alt="creating download file" id="spinner" src="images/ajax-loader.gif" style="display:none;">
    <?php
    if ($gbs_exp == "yes") {
        ?>
        <input type="button" value="Download allele data" onclick="javascript:load_tab_delimiter_GBS('<?php echo $experiment_uid ?>','<?php echo $max_missing ?>','<?php echo $min_maf ?>');"/>
        <?php
    } else {
        ?>
        <input type="button" value="Download allele data" onclick="javascript:load_tab_delimiter('<?php echo $experiment_uid ?>','<?php echo $max_missing ?>','<?php echo $min_maf ?>');"/>
        <?php
    }
    $url = "genotyping/display_markers.php?geno_exp=" . $experiment_uid;
    ?>
    <button onclick="location.href='<?php echo $url ?>'">Download marker data</button><br>
    <?php
    echo "</div><p><br>";
    echo "<h3>Additional files available</h3><p>";
    echo "<table>";

    echo "<tr> <td>Samples (germplasm lines)</td><td><a href='".$config['base_url']."raw/genotype/".$row_Gen_Info['sample_sheet_filename']."'>".$row_Gen_Info['sample_sheet_filename']."</a></td></tr>";
    echo "<tr> <td>Manifest (markers used)</td><td><a href='".$config['base_url']."raw/genotype/".$row_Gen_Info['manifest_file_name']."'>". $row_Gen_Info['manifest_file_name']." </a></td></tr>";

    echo "<tr> <td>Cluster File</td><td><a href='".$config['base_url']."raw/genotype/".$row_Gen_Info['cluster_file_name']."'>".$row_Gen_Info['cluster_file_name']."</a></td></tr>";

    echo "<tr> <td>Raw data</td><td><a href='".$config['base_url']."raw/genotype/".$row_Gen_Info['raw_datafile_archive']."'>".$row_Gen_Info['raw_datafile_archive']."</a></td></tr>";
    echo "</table>";
  
  } /* End of function type_DataInformation*/

  private function type_Tab_Delimiter_GBS() {
      global $mysqli;
      $experiment_uid = $_GET['expuid'];
      $max_missing = 99.9;//IN PERCENT
      if (isset($_GET['mm']) && !empty($_GET['mm']) && is_numeric($_GET['mm']))
          $max_missing = $_GET['mm'];
      if ($max_missing > 100)
          $max_missing = 100;
      elseif ($max_missing < 0)
          $max_missing = 0;
      $min_maf = 0.01;//IN PERCENT
      if (isset($_GET['mmaf']) && !is_null($_GET['mmaf']) && is_numeric($_GET['mmaf']))
          $min_maf = $_GET['mmaf'];
      if ($min_maf > 100)
          $min_maf = 100;
      elseif ($min_maf < 0)
          $min_maf = 0;

      $unique_str = chr(rand(65, 90)) .chr(rand(65, 90)) .chr(rand(65, 90)) .chr(rand(65, 90));
      $filename = "download_" . $unique_str;
      mkdir("/tmp/tht/$filename");
      $sql = "SELECT marker_uid from allele_bymarker_exp_ACTG where experiment_uid = $experiment_uid";
      $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
      while ($row = mysqli_fetch_row($res)) {
          $uid = $row[0];
          $markers[] = $uid;
      }

      $filename = "genotype.hmp.txt";
      $h = fopen("/tmp/tht/download_$unique_str/$filename", "w");
      $output = \type4BuildMarkersDownload($experiment_uid, $min_maf, $max_missing, $dtype, $h);
      fclose($h);
      $filename = "/tmp/tht/download_" . $unique_str . ".zip";
      exec("cd /tmp/tht; /usr/bin/zip -r $filename download_$unique_str");
      ?>
      <input type="button" value="Download Zip file of results" onclick="javascript:window.open('<?php echo "$filename"; ?>');" />
      <?php
  }
  
  private function type_Tab_Delimiter() {
    global $mysqli;
    $experiment_uid = $_GET['expuid'];
    $max_missing = 99.9;//IN PERCENT
    if (isset($_GET['mm']) && !empty($_GET['mm']) && is_numeric($_GET['mm']))
      $max_missing = $_GET['mm'];
    if ($max_missing > 100)
      $max_missing = 100;
    elseif ($max_missing < 0)
      $max_missing = 0;
    $min_maf = 0.01;//IN PERCENT
    if (isset($_GET['mmaf']) && !is_null($_GET['mmaf']) && is_numeric($_GET['mmaf']))
      $min_maf = $_GET['mmaf'];
    if ($min_maf > 100)
      $min_maf = 100;
    elseif ($min_maf < 0)
      $min_maf = 0;
    //$firephp = FirePHP::getInstance(true);
    $outputheader = '';
    $output = '';
    $doneheader = false;
    $delimiter ="\t";
    //get lines and filter to get a list of markers which meet the criteria selected by the user
         
    $sql_mstat = "SELECT af.marker_uid as marker, m.marker_name as name, SUM(af.aa_cnt) as sumaa, SUM(af.missing)as summis, SUM(af.bb_cnt) as sumbb,
		    SUM(af.total) as total, SUM(af.ab_cnt) AS sumab
		    FROM allele_frequencies AS af, markers as m
		    WHERE m.marker_uid = af.marker_uid
			    AND af.experiment_uid = $experiment_uid
		    group by af.marker_uid"; 
    $res = mysqli_query($mysqli, $sql_mstat) or die("Error: user criteria select query.<br>".mysqli_error($mysqli));
    $num_mark = mysqli_num_rows($res);
    $num_maf = $num_miss = 0;

    while ($row = mysqli_fetch_array($res)){
      $maf = round(100*min((2*$row["sumaa"]+$row["sumab"])/(2*$row["total"]),($row["sumab"]+2*$row["sumbb"])/(2*$row["total"])),1);
      $miss = round(100*$row["summis"]/$row["total"],1);
      if (($maf >= $min_maf) AND ($miss <= $max_missing)) {
	$marker_names[] = $row["name"];
	$outputheader .= $delimiter.$row["name"];
	$marker_uid[] = $row["marker"];
      }
    }

    //get a list of GBS markers used to convert format
    $query = "SELECT marker_uid, marker_type_name, A_allele, B_allele from markers, marker_types 
                  where markers.marker_type_uid = marker_types.marker_type_uid and marker_type_name = 'GBS'";
    $resource = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));
    while ($row = mysqli_fetch_assoc($resource)) {
      $uid = $row['marker_uid'];
      $a_allele = $row['A_allele'];
      $b_allele = $row['B_allele'];
      $lookupGBS[$uid] = array(
			       'AA' => $a_allele, 
			       'BB' => $b_allele,
			       '--' => 'N',
			       );
    }
          
    // Begin output to file.
    // Prepend HTML header to trigger browser's "Open or Save?" dialog. 
    $date = date("m-d-Y-His");
    $name = "THT-allele_query-$date.txt";
    // JLee force url context change
    header('Cache-Control:');
    header('Pragma:');
    header('Content-type: text/plain');
    header("Content-Disposition: attachment; filename=$name");
    header('Pragma: no-cache');
    header('Expires: 0');
    echo $outputheader."\n";

    sort($marker_uid,SORT_NUMERIC);
    $nelem = count($marker_uid);
    $marker_uid = implode(",",$marker_uid);
    if ($nelem == 0) {
      error(1, "There are no markers matching the current conditions, try again with different set of criteria.");
      exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
    }
    $lookup = array(
		    'AA' => 'AA',
		    'BB' => 'BB',
		    '--' => '-',
		    'AB' => 'AB'
		    );
	    
    // make an empty line with the markers as array keys, set default value
    //  to the default missing value for either qtlminer or tassel
    // places where the lines may have different values
    $empty = array_combine($marker_names,array_fill(0,$nelem,'NA'));
			
    $sql = "SELECT line_record_name, marker_name AS name, alleles AS value, marker_uid
        FROM allele_cache
        WHERE marker_uid IN ($marker_uid)
        AND experiment_uid =$experiment_uid
        ORDER BY line_record_name, marker_uid";
    $last_line = "some really silly name that no one would call a plant";
    $res = mysqli_query($mysqli, $sql) or die("Error:allele output dataset<br>". mysqli_error($mysqli));
    $outarray = $empty;
    $cnt = $num_lines = 0;
    while ($row = mysqli_fetch_array($res)) {
      //first time through loop
      $uid = $row['marker_uid'];
      if ($cnt == 0) 
	$last_line = $row['line_record_name'];
      if ($last_line != $row['line_record_name']) {  
	// Close out the last line
	$output .= "$last_line\t";
	$outarray = implode($delimiter,$outarray);
	$output .= $outarray."\n";
	echo $output;
	$output = "";
	//reset output arrays for the next line
	$outarray = $empty;
	$mname = $row['name'];
	if (isset($lookupGBS[$uid])) {
	  $outarray[$mname] = $lookupGBS[$uid][$row['value']]; 
	} else {				
	  $outarray[$mname] = $lookup[$row['value']];
	}
	$last_line = $row['line_record_name'];
	$num_lines++;
      } else {
	$mname = $row['name'];	
	if (isset($lookupGBS[$uid])) {
	  $outarray[$mname] = $lookupGBS[$uid][$row['value']];
	} else {			
	  $outarray[$mname] = $lookup[$row['value']];
	}
      }
      $cnt++;
    }
    //save data from the last line
    $output .= "$last_line$delimiter";
    $outarray = implode($delimiter, $outarray);
    $output .= $outarray."\n";
    $num_lines++;
    echo $output;
  }
}
