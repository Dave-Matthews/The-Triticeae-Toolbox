<?php 

// J.Lee 5/9/2011	Fix problem with query while restricting mmaf and max missing values,
//					prevent download operation when 0 markers match condition.
// J.Lee 8/17/2010  Modify alelle download to work in Linux and Solaris 
//****************************************************************************

require 'config.php';
include($config['root_dir'] . 'includes/bootstrap.inc');
require_once 'Spreadsheet/Excel/Writer.php';
connect();


new ShowData($_GET['function']);

class ShowData
{
    
    private $delimiter = "\t";
    
	//
	// Using the class's constructor to decide which action to perform
	public function __construct($function = null)
	{	
		switch($function)
		{
			
			case 'typeTabDelimiter':
				$this->type_Tab_Delimiter();  /* Displaying in tab demilited fashion */
				break;
			
			default:
				$this->typeData();
				break;
			}
			
	}
	
	//
	// The wrapper action for the type1 download. Handles outputting the header
	// and footer and calls the first real action of the type1 download.
	private function typeData()
	{
		global $config;
		include($config['root_dir'].'theme/normal_header.php');

		$trial_code=$_GET['trial_code'];
		echo " <h2>".$trial_code. "</h2>";
		
		$this->type_DataInformation($trial_code);

		$footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
	}


private function type_DataInformation($trial_code)
	{
	
	/* Query for getting the experiment type uid to check the type of experiment */
	
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
		
		/* Checking if the experiment is a phenotype or genetype experiment and displaying details only for phenotype experiment*/
		
		/* Currently not implemented */
		
		if ($row['experiment_type_uid'] == 1)
		{
			$this->type_PhenoInformation($trial_code,$experiment_uid);
		}
		
		/* Displaying data for genetype experiments */
		
		else
		{
		
	/* Displaying the Data For Genptype Experiment */

	$sql_CAP = "SELECT data_program_code FROM CAPdata_programs where CAPdata_programs_uid = '".$CAPdata_programs_uid."' ";
		$res_CAP = mysql_query($sql_CAP) or die("Error: Unable to retrieve data program code. <br>".mysql_error());
		$row_CAP = mysql_fetch_assoc($res_CAP);
		$data_program_code = $row_CAP['data_program_code'];
		
		//$sql_Gen_Info = "SELECT manifest_file_name, cluster_file_name, OPA_name, sample_sheet_filename, raw_datafile_archive, genotype_experiment_info_uid FROM genotype_experiment_info where experiment_uid = '".$experiment_uid."' ";
	$sql_Gen_Info = "SELECT * FROM genotype_experiment_info where experiment_uid = '".$experiment_uid."' ";
	//$res_Gen_Info = mysql_query($sql_Gen_Info) or die("Error: Unable to retrieve data file names.<br> " .mysql_error());
		$res_Gen_Info = mysql_query($sql_Gen_Info) or die("Error: No experiment information for genotype experiment $trial_code..<br> " .mysql_error());
		$row_Gen_Info = mysql_fetch_assoc($res_Gen_Info);
		
		$manifest_file_name = $row_Gen_Info['manifest_file_name'];
		$cluster_file_name = $row_Gen_Info['cluster_file_name'];
		$OPA_name = $row_Gen_Info['OPA_name'];
		$sample_sheet_filename = $row_Gen_Info['sample_sheet_filename'];
		$raw_datafile_archive = $row_Gen_Info['raw_datafile_archive'];
		$genotype_experiment_info_uid = $row_Gen_Info['genotype_experiment_info_uid'];
?>

<script type="text/javascript">
	
	function load_tab_delimiter(experiment_uid, max_missing, min_maf)
	{
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

	$max_missing = 99;//IN PERCENT
        if (isset($_GET['mm']) && !empty($_GET['mm']) && is_numeric($_GET['mm']))
            $max_missing = $_GET['mm'];
		if ($max_missing > 100)
			$max_missing = 100;
		elseif ($max_missing < 0)
			$max_missing = 0;
        $min_maf = 0.1;//IN PERCENT
        if (isset($_GET['mmaf']) && !empty($_GET['mmaf']) && is_numeric($_GET['mmaf']))
            $min_maf = $_GET['mmaf'];
		if ($min_maf > 100)
			$min_maf = 100;
		elseif ($min_maf < 0)
			$min_maf = 0;
	
	$sql_mstat = "SELECT af.marker_uid as marker, SUM(af.aa_cnt) as sumaa, SUM(af.missing) as summis, SUM(af.bb_cnt) as sumbb,
					SUM(af.total) as total, SUM(af.ab_cnt) AS sumab
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
	
	/* DEM 29aug11: Omit the Summary table of allele frequencies etc for all markers. */	
?>	

     There are <b><?php echo ($num_mark) ?></b> distinct markers.<br>
     <b><?php echo ($num_miss) ?></b> markers are missing at least <b><?php echo ($max_missing) ?></b>% of measurements.<br>
     <b><?php echo ($num_maf) ?></b> markers have a minor allele frequency (MAF) larger than <b><?php echo ($min_maf) ?></b>%.<br>
     <p>Maximum Missing Data: <input type="text" name="mm" id="mm" size="1" value="<?php echo ($max_missing) ?>" />%&nbsp;&nbsp;&nbsp;&nbsp;
     Minimum MAF: <input type="text" name="mmaf" id="mmaf" size="1" value="<?php echo ($min_maf) ?>" />%&nbsp;&nbsp;&nbsp;&nbsp;
     <input type="button" value="Refresh" onclick="javascript:mrefresh('<?php echo $trial_code ?>');return false;" /><br>
     <input type="button" value="Download Allele Data" onclick="javascript:load_tab_delimiter('<?php echo $experiment_uid ?>','<?php echo $max_missing ?>','<?php echo $min_maf ?>');"/>
<p><br>

<?php

	echo "<b>Experiment description</b><p>";
echo "<table>";
     echo "<tr> <td>Experiment Short Name</td><td>".$experiment_short_name."</td></tr>";
     echo "<tr> <td>Data Program</td><td>".$data_program_name." (".$data_program_code.")</td></tr>";
     echo "<tr> <td>OPA Name</td><td>".$row_Gen_Info['OPA_name']."</td></tr>";
     echo "<tr> <td>Processing Date</td><td>".$row_Gen_Info['processing_date']."</td></tr>";
     echo "<tr> <td>Software</td><td>".$row_Gen_Info['analysis_software']."</td></tr>";
     echo "<tr> <td>Software version</td><td>".$row_Gen_Info['BGST_version_number']."</td></tr>";
echo "</table><p>";

echo "<b>Additional files available</b><p>";
echo "<table>";
			
			  echo "<tr> <td>Manifest File Name</td><td><a href='/tht/raw/genotype/".$row_Gen_Info['manifest_file_name']."'>". $row_Gen_Info['manifest_file_name']." </a></td></tr>";
			
			echo "<tr> <td>Cluster File Name</td><td><a href='/tht/raw/genotype/".$row_Gen_Info['cluster_file_name']."'>".$row_Gen_Info['cluster_file_name']."</a></td></tr>";
			
			echo "<tr> <td>Sample Sheet File Name</td><td><a href='/tht/raw/genotype/".$row_Gen_Info['sample_sheet_filename']."'>".$row_Gen_Info['sample_sheet_filename']."</a></td></tr>";
			echo "<tr> <td>Raw Data File Archive </td><td><a href='/tht/raw/genotype/".$row_Gen_Info['raw_datafile_archive']."'>".$row_Gen_Info['raw_datafile_archive']."</a></td></tr>";
			echo "</table>";
			  echo "<br>";
?>

  <?php 
 
  } /* end of else */
  
  } /* End of function type_DataInformation*/
  
  private function type_Tab_Delimiter()
  {
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
		  
			
			
         $sql = "SELECT lr.line_record_name, m.marker_name AS name,
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
				AND gd.marker_uid IN ($marker_uid)
				AND tb.line_record_uid = lr.line_record_uid
				AND gd.tht_base_uid = tb.tht_base_uid
				AND tb.experiment_uid ='".$experiment_uid."'
		  ORDER BY lr.line_record_name, m.marker_uid";

		//echo "allele output query " . $sql . "<br>";
		$last_line = "some really silly name that no one would call a plant";
		$res = mysql_query($sql) or die("Error:allele output dataset<br>". mysql_error());
		
		$outarray = $empty;
		$cnt = $num_lines = 0;
		while ($row = mysql_fetch_array($res)){
				//first time through loop
				if ($cnt == 0) {
					$last_line = $row['line_record_name'];
				}
				
			if ($last_line != $row['line_record_name']){  
					// Close out the last line
					$output .= "$last_line\t";
					$outarray = implode($delimiter,$outarray);
					$output .= $outarray."\n";
					//reset output arrays for the next line
					$outarray = $empty;
					$mname = $row['name'];				
					$outarray[$mname] = $lookup[$row['value']];
					$last_line = $row['line_record_name'];
					$num_lines++;
			} else {
					 $mname = $row['name'];				
					 $outarray[$mname] = $lookup[$row['value']];
			}
			$cnt++;
		}
		
		  //save data from the last line
		  
		  $output .= "$last_line$delimiter";
		  $outarray = implode($delimiter,$outarray);
		  $output .= $outarray."\n";
		  $num_lines++;

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
	
		echo $outputheader."\n".$output;
		
  }
  

  } /* End of class*/
?>

