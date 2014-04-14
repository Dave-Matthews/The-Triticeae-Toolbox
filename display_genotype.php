<?php 
/**
 * Report for a single genotyping experiment.
 *
 * PHP version 5.3
 * 
 * @category PHP
 * @package  T3
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @version  GIT: 2
 * @link     http://triticeaetoolbox.org/wheat/display_genotype.php
 *
 * dem 23mar12 Handle large dataset downloads. Output one row at a time
 *             instead of catenating the whole thing into $output first.
 * J.Lee 5/9/2011 Fix problem with query while restricting mmaf and max missing
 *	          values, prevent download operation when 0 markers match condition.
 * J.Lee 8/17/2010 Modify alelle download to work in Linux and Solaris 
 */

// dem 23mar12: Default 30 sec is too short for experiment 2011_9K_NB_allplates.
ini_set("max_execution_time", "300");
// dem 23mar12: Default 500M is too small for experiment 2011_9K_NB_allplates.
ini_set('memory_limit', '4096M');
require 'config.php';
require $config['root_dir'] . 'includes/bootstrap.inc';
require_once 'Spreadsheet/Excel/Writer.php';
connect();

/* new ShowData($_GET['function']); */
new ShowData($_REQUEST['function']);

/** Using a PHP class to implement the report feature
 * 
 * @category PHP
 * @package  T3
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/display_genotype.php
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
        switch($function) {
        case 'typeTabDelimiter':
            $this->type_Tab_Delimiter();  /* Displaying in tab delimited fashion */
            break;
        case 'select_lines':
            $this->type_SelectLines();
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
    private function type_SelectLines()
    {
        $_SESSION[selected_lines] = explode(",", $_POST[linelist]);
        echo "<meta http-equiv=\"refresh\" content=\"0;url=".$config['base_url']."pedigree/line_properties.php\">";
    }

  // The wrapper action for the type1 download. Handles outputting the header
  // and footer and calls the first real action of the type1 download.
  private function typeData() {
    global $config;
    include $config['root_dir'].'theme/normal_header.php';

    $trial_code=$_GET['trial_code'];
    echo " <h2>Genotyping experiment ".$trial_code. "</h2>";
    $this->type_DataInformation($trial_code);

    $footer_div = 1;
    include $config['root_dir'].'theme/footer.php';
  }

  private function type_DataInformation($trial_code) {
    $line_ids = array();
    $sql = "SELECT CAPdata_programs_uid, experiment_type_uid, experiment_uid, experiment_short_name FROM experiments where trial_code = '".$trial_code."' ";
    $res = mysql_query($sql) or die("Error: unable to retrieve experiment record with trial code.<br>".mysql_error());
    $row = mysql_fetch_assoc($res);
    $experiment_uid = $row['experiment_uid'];
    $CAPdata_programs_uid = $row['CAPdata_programs_uid'];	
    $experiment_short_name = $row['experiment_short_name'];
		
    $sql_data_code = "SELECT data_program_code, data_program_name FROM CAPdata_programs where CAPdata_programs_uid = '".$CAPdata_programs_uid."' ";
    $res_data_code = mysql_query($sql_data_code) or die("Error: unable to retrieve CAP data info from data prog id.<br>".mysql_error());
    $row_data_code = mysql_fetch_assoc($res_data_code);
    $data_program_code = $row_data_code['data_program_code'];
    $data_program_name = $row_data_code['data_program_name'];

    $sql_lines = "select line_record_uid from tht_base where experiment_uid = $experiment_uid";
    $res_lines = mysql_query($sql_lines) or die("Error: unable to retrieve lines for this experiment.<br>" . mysql_error());
    while ($line = mysql_fetch_row($res_lines))
      $line_ids[] = $line[0];
    $line_total = count($line_ids);
    $line_list = implode(",", $line_ids);

    $sql_Gen_Info = "SELECT * FROM genotype_experiment_info where experiment_uid = '".$experiment_uid."' ";
    $res_Gen_Info = mysql_query($sql_Gen_Info) or die("Error: No experiment information for genotype experiment $trial_code..<br> " .mysql_error());
    $row_Gen_Info = mysql_fetch_assoc($res_Gen_Info);
    $manifest_file_name = $row_Gen_Info['manifest_file_name'];
    $cluster_file_name = $row_Gen_Info['cluster_file_name'];
    $OPA_name = $row_Gen_Info['OPA_name'];
    $sample_sheet_filename = $row_Gen_Info['sample_sheet_filename'];
    $raw_datafile_archive = $row_Gen_Info['raw_datafile_archive'];
    $genotype_experiment_info_uid = $row_Gen_Info['genotype_experiment_info_uid'];
    $comments = $row_Gen_Info['comments'];
    $platform_uid = $row_Gen_Info['platform_uid'];
    $sql = "SELECT platform_name from platform where platform_uid = $platform_uid";;
    $res = mysql_query($sql) or die("Error: No platform information for genotype experiment $trial_code..<br> " .mysql_error());
    $row = mysql_fetch_assoc($res);
    $platform_name = $row['platform_name'];
?>

<script type="text/javascript">

	function load_tab_delimiter(experiment_uid, max_missing, min_maf) {
	    //alert (experiment_uid);
	    var url='<?php echo $_SERVER[PHP_SELF];?>?function=typeTabDelimiter'+ '&expuid=' + experiment_uid+ '&mm='+max_missing+'&mmaf='+min_maf;
	    // Opens the url in the same window
	    window.open(url, "_self");
	}
	
	function mrefresh(trial_code) {
            var mm = $('mm').getValue();
            var mmaf = $('mmaf').getValue();
            var url='<?php echo $_SERVER[PHP_SELF];?>?function=typeData'+ '&mm='+mm+'&mmaf='+mmaf+ '&trial_code='+trial_code;
	    // Opens the url in the same window
	    window.open(url, "_self");
        }

</script>
	
<?php
    $max_missing = 99; //IN PERCENT
	if (isset($_GET['mm']) && !empty($_GET['mm']) && is_numeric($_GET['mm']))
	  $max_missing = $_GET['mm'];
	if ($max_missing > 100)
	  $max_missing = 100;
	elseif ($max_missing < 0)
	  $max_missing = 0;
        $min_maf = 0.1; //IN PERCENT
        if (isset($_GET['mmaf']) && !empty($_GET['mmaf']) && is_numeric($_GET['mmaf']))
	  $min_maf = $_GET['mmaf'];
	if ($min_maf > 100)
	  $min_maf = 100;
	elseif ($min_maf < 0)
	  $min_maf = 0;
	
	$sql_mstat = "SELECT af.marker_uid as marker, SUM(af.aa_cnt) as sumaa, SUM(af.missing) as summis, 
		    SUM(af.bb_cnt) as sumbb, SUM(af.total) as total, SUM(af.ab_cnt) AS sumab
		    FROM allele_frequencies AS af
		    WHERE af.experiment_uid = '".$experiment_uid."'
		    group by af.marker_uid"; 
	$res = mysql_query($sql_mstat) or die("Error: Unable to sum allele frequency values.<br>".mysql_error());
	$num_mark = mysql_num_rows($res);
	$num_maf = $num_miss = 0;
			
	while ($row = mysql_fetch_array($res)){
	  $marker_uid[] = $row["marker"];
	  $maf = round(100*min((2*$row["sumaa"]+$row["sumab"])/(2*$row["total"]),($row["sumab"]+2*$row["sumbb"])/(2*$row["total"])),1);
	  $miss = round(100*$row["summis"]/$row["total"],1);
	  if ($maf >= $min_maf)
	    $num_maf++;
	  if ($miss > $max_missing)
	    $num_miss++;
	}
	echo "<h3>Description</h3><p>";
	echo "<table>";
	echo "<tr> <td>Experiment Short Name</td><td>".$experiment_short_name."</td></tr>";
        echo "<tr> <td>Platform</td><td>".$platform_name."</td></tr>";
	echo "<tr> <td>Data Program</td><td>".$data_program_name." (".$data_program_code.")</td></tr>";
	echo "<tr> <td>OPA Name</td><td>".$row_Gen_Info['OPA_name']."</td></tr>";
	echo "<tr> <td>Processing Date</td><td>".$row_Gen_Info['processing_date']."</td></tr>";
	echo "<tr> <td>Software</td><td>".$row_Gen_Info['analysis_software']."</td></tr>";
	echo "<tr> <td>Software version</td><td>".$row_Gen_Info['BGST_version_number']."</td></tr>";
	echo "<tr> <td>Comments</td><td>".$row_Gen_Info['comments']."</td></tr>";
	echo "</table><p>";
?>

<h3>Download</h3>
<form method=POST action="<?php echo $SERVER[PHP_SELF] ?>">
<b><?php echo ($num_mark) ?></b> markers were assayed for <b><?php echo ($line_total) ?></b> lines.
<input type=hidden name=function value=select_lines>
<input type=hidden name=linelist value=<?php echo $line_list ?>>
<input type="submit" value="Select these lines" style="color:blue">
</form>

<p>
<b><?php echo ($num_miss) ?></b> markers are missing at least <b><?php echo ($max_missing) ?></b>% of measurements.<br>
<b><?php echo ($num_maf) ?></b> markers have a minor allele frequency (MAF) larger than <b><?php echo ($min_maf) ?></b>%.<br>
Maximum Missing Data: <input type="text" name="mm" id="mm" size="1" value="<?php echo ($max_missing) ?>" />%&nbsp;&nbsp;&nbsp;&nbsp;
  Minimum MAF: <input type="text" name="mmaf" id="mmaf" size="1" value="<?php echo ($min_maf) ?>" />%&nbsp;&nbsp;&nbsp;&nbsp;
  <input type="button" value="Refresh" onclick="javascript:mrefresh('<?php echo $trial_code ?>');return false;" /><br>
  <input type="button" value="Download allele data" onclick="javascript:load_tab_delimiter('<?php echo $experiment_uid ?>','<?php echo $max_missing ?>','<?php echo $min_maf ?>');"/>
<p><br>

     <?php
	echo "<h3>Additional files available</h3><p>";
	echo "<table>";
			
	echo "<tr> <td>Samples (germplasm lines)</td><td><a href='".$config['base_url']."raw/genotype/".$row_Gen_Info['sample_sheet_filename']."'>".$row_Gen_Info['sample_sheet_filename']."</a></td></tr>";
	echo "<tr> <td>Manifest (markers used)</td><td><a href='".$config['base_url']."raw/genotype/".$row_Gen_Info['manifest_file_name']."'>". $row_Gen_Info['manifest_file_name']." </a></td></tr>";
			
	echo "<tr> <td>Cluster File</td><td><a href='".$config['base_url']."raw/genotype/".$row_Gen_Info['cluster_file_name']."'>".$row_Gen_Info['cluster_file_name']."</a></td></tr>";
			
	echo "<tr> <td>Raw data</td><td><a href='".$config['base_url']."raw/genotype/".$row_Gen_Info['raw_datafile_archive']."'>".$row_Gen_Info['raw_datafile_archive']."</a></td></tr>";
	echo "</table>";
  
  } /* End of function type_DataInformation*/
  
  private function type_Tab_Delimiter() {
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
			    AND af.experiment_uid ='".$experiment_uid."'
		    group by af.marker_uid"; 
    $res = mysql_query($sql_mstat) or die("Error: user criteria select query.<br>".mysql_error());
    $num_mark = mysql_num_rows($res);
    $num_maf = $num_miss = 0;

    while ($row = mysql_fetch_array($res)){
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
    $resource = mysql_query($query) or die(mysql_error());
    while ($row = mysql_fetch_assoc($resource)) {
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
    $res = mysql_query($sql) or die("Error:allele output dataset<br>". mysql_error());
    $outarray = $empty;
    $cnt = $num_lines = 0;
    while ($row = mysql_fetch_array($res)) {
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
    $outarray = implode($delimiter,$outarray);
    $output .= $outarray."\n";
    $num_lines++;
    echo $output;
  }

} /* End of class*/
?>

