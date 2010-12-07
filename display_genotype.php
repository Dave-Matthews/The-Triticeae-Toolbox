<?php 

// J.Lee 8/17/2010  Modify alelle download to work in Linux and Solaris 

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
		$res = mysql_query($sql) or die(mysql_error());
		$row = mysql_fetch_assoc($res);
		
		$experiment_uid = $row['experiment_uid'];
		$CAPdata_programs_uid = $row['CAPdata_programs_uid'];	
		$experiment_short_name = $row['experiment_short_name'];
		
		$sql_data_code = "SELECT data_program_code, data_program_name FROM CAPdata_programs where CAPdata_programs_uid = '".$CAPdata_programs_uid."' ";
		$res_data_code = mysql_query($sql_data_code) or die(mysql_error());
		$row_data_code = mysql_fetch_assoc($res_data_code);
		
		$data_program_code = $row_data_code['data_program_code'];
		$data_program_name = $row_data_code['data_program_name'];
		
		/* Checking if the experiment is a phenotype or genetype experiment and displaying details only for phenotype experiment*/
		
		/* Currently not implemented */
		
		if ($row['experiment_type_uid']==1)
		{
			$this->type_PhenoInformation($trial_code,$experiment_uid);
		}
		
		/* Displaying data for genetype experiments */
		
		else
		{
		
	/* Displaying the Data For Genptype Experiment */

	
		$sql_Gen_Info = "SELECT manifest_file_name, cluster_file_name, OPA_name, sample_sheet_filename, raw_datafile_archive FROM genotype_experiment_info where experiment_uid = '".$experiment_uid."' ";
		$res_Gen_Info = mysql_query($sql_Gen_Info) or die(mysql_error());
		$row_Gen_Info = mysql_fetch_assoc($res_Gen_Info);	
	

	
	$sql_CAP = "SELECT data_program_code FROM CAPdata_programs where CAPdata_programs_uid = '".$CAPdata_programs_uid."' ";
		$res_CAP = mysql_query($sql_CAP) or die(mysql_error());
		$row_CAP = mysql_fetch_assoc($res_CAP);
		$data_program_code = $row_CAP['data_program_code'];
		
	$sql_Gen_Info = "SELECT manifest_file_name, cluster_file_name, OPA_name, sample_sheet_filename, raw_datafile_archive, genotype_experiment_info_uid FROM genotype_experiment_info where experiment_uid = '".$experiment_uid."' ";
		$res_Gen_Info = mysql_query($sql_Gen_Info) or die(mysql_error());
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

	$max_missing = 100;//IN PERCENT
        if (isset($_GET['mm']) && !empty($_GET['mm']) && is_numeric($_GET['mm']))
            $max_missing = $_GET['mm'];
		if ($max_missing>100)
			$max_missing = 100;
		elseif ($max_missing<0)
			$max_missing = 0;
        $min_maf = 0;//IN PERCENT
        if (isset($_GET['mmaf']) && !empty($_GET['mmaf']) && is_numeric($_GET['mmaf']))
            $min_maf = $_GET['mmaf'];
		if ($min_maf>100)
			$min_maf = 100;
		elseif ($min_maf<0)
			$min_maf = 0;
	
	$sql_mstat = "SELECT af.marker_uid as marker, SUM(af.aa_cnt) as sumaa, SUM(af.missing)as summis, SUM(af.bb_cnt) as sumbb,
					SUM(af.total) as total, SUM(af.ab_cnt) AS sumab
					FROM allele_frequencies AS af
					WHERE af.experiment_uid = '".$experiment_uid."'
					group by af.marker_uid"; 

			$res = mysql_query($sql_mstat) or die(mysql_error());
			$num_mark = mysql_num_rows($res);
			$num_maf = $num_miss = 0;
			
			while ($row = mysql_fetch_array($res)){
				$marker_uid[] = $row["marker"];
			  $maf = round(100*min((2*$row["sumaa"]+$row["sumab"])/(2*$row["total"]),($row["sumab"]+2*$row["sumbb"])/(2*$row["total"])),1);
			  $miss = round(100*$row["summis"]/$row["total"],1);
			  if ($maf>$min_maf)
			    $num_maf++;
			  if ($miss>=$max_missing)
			    $num_miss++;
			}
	
	
	
	/* Computing the summary and other details for the experiment */
	
	
	
	
	
	$sql_Gen_Stat = "
                SELECT
                    m.marker_uid,
                    CONCAT(m.marker_name,' (',ms.value,')') AS name,
                    CONCAT(map.map_name,' ',cast(mim.start_position as char),' cM') as position,
                    a.missing,
                    a.aa_freq,
                    a.ab_freq,
                    a.bb_freq,
                    a.total,
                    a.monomorphic,
                    ROUND(a.maf, 2) AS maf
                FROM
                    markers m INNER JOIN (allele_frequencies a, marker_synonyms ms, markers_in_maps mim, map)
                        ON m.marker_uid = a.marker_uid
                        AND m.marker_uid = ms.marker_uid
                        AND m.marker_uid = mim.marker_uid
                        AND mim.map_uid = map.map_uid
						AND a.experiment_uid = '".$experiment_uid."'
               WHERE
                    a.missing / a.total <= '".$max_missing."'
						AND a.maf >= '".$min_maf."'
						AND ms.marker_synonym_type_uid = '1'
				GROUP BY name
                
                 ";
$res_Gen_Stat = mysql_query($sql_Gen_Stat) or die(mysql_error());
	
?>
<style type="text/css">
			th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
			table {background: none; border-collapse: collapse}
			td {border: 1px solid #eee !important;}
			h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
		</style>
  
<div style="width: 840px;"><b>Summary:</b><br><br>
<table >


	<tr> 
		
		<th style="width: 180px;" > Marker Name </th>
		<th style="width: 100px;" > Map Name and Position  </th>
		<th style="width: 60px;" >  Missing    </th>
		<th style="width: 100px;" >  AA Freq    </th>
		<th style="width: 100px;" >  AB Freq    </th>
		<th style="width: 100px;">  BB Freq    </th>
		<th style="width: 100px;" >  Total    </th>
		<th style="width: 20px;" >  Monomorphic </th>
		<th style="width: 100px;" >  MAF    </th>

		
	</tr>
 </table>
 </div>
 	
 	
 	<div style="padding: 0; width: 840px; height: 400px; overflow: scroll; border: 1px solid #5b53a6; clear: both">



<?php
  echo "<table>";
	while ($row_Gen_Stat = mysql_fetch_assoc($res_Gen_Stat)) 
	{
	
	
?>

  <tr>
  
 <td style="width: 100px;" >
  <?php echo $row_Gen_Stat['name']; ?>
  </td>
  <td style="width: 100px;" >
  <?php echo $row_Gen_Stat['position']; ?>
  </td>
  <td style="width: 80px;" >
  <?php echo $row_Gen_Stat['missing']; ?>
  </td>
  <td style="width: 100px;" >
  <?php echo $row_Gen_Stat['aa_freq']; ?>
  </td>
  <td style="width: 100px;"  >
  <?php echo $row_Gen_Stat['ab_freq']; ?>
  </td>
  <td style="width: 100px;" >
  <?php echo $row_Gen_Stat['bb_freq']; ?>
  </td>
  <td style="width: 100px;">
  <?php echo $row_Gen_Stat['total']; ?>
  </td>
  <td style="width: 120px;">
  <?php echo $row_Gen_Stat['monomorphic']; ?>
  </td>
  <td style="width: 70px;">
  <?php echo $row_Gen_Stat['maf']; ?>
  </td>
  </tr>
  
  	

  
  
  <?php
  }/* End of while loop*/
  
  ?>
  </table>
  </div>
  <br/>
<div style="padding-left: 20px;border: 1px">
					<p style="font-style: italic">There are <?php echo ($num_mark) ?> distinct markers.</p>
					<p style="font-style: italic"><?php echo ($num_maf) ?> markers have a minor allele frequency (MAF) larger than <?php echo ($min_maf) ?>%.</p>
					<p style="font-style: italic"><?php echo ($num_miss) ?> markers are missing at least <?php echo ($max_missing) ?> % of measurements.</p>
                    Maximum Missing Data (%): <input type="text" name="mm" id="mm" size="3" value="<?php echo ($max_missing) ?>" />&nbsp;&nbsp;&nbsp;&nbsp;
                    Minimum MAF (%): <input type="text" name="mmaf" id="mmaf" size="3" value="<?php echo ($min_maf) ?>" />&nbsp;&nbsp;&nbsp;&nbsp;
                    <input type="button" value="Refresh" onclick="javascript:mrefresh('<?php echo $trial_code ?>');return false;" />
<br/><br><input type="button" value="Download Allele Data" onclick="javascript:load_tab_delimiter('<?php echo $experiment_uid ?>','<?php echo $max_missing ?>','<?php echo $min_maf ?>');"/>
    </div><br>

<br><br>

<?php
echo "<b>Additional files available:</b><p>";
echo "<table>";
			
			  echo "<tr> <td>Manifest File Name</td><td><a href='/tht/raw/genotype/".$row_Gen_Info['manifest_file_name']."'>". $row_Gen_Info['manifest_file_name']." </a></td></tr>";
			
			echo "<tr> <td>Cluster File Name</td><td><a href='/tht/raw/genotype/".$row_Gen_Info['cluster_file_name']."'>".$row_Gen_Info['cluster_file_name']."</a></td></tr>";
			
			echo "<tr> <td>Sample Sheet File Name</td><td><a href='/tht/raw/genotype/".$row_Gen_Info['sample_sheet_filename']."'>".$row_Gen_Info['sample_sheet_filename']."</a></td></tr>";
			echo "<tr> <td>Raw Data File Archive </td><td><a href='/tht/raw/genotype/".$row_Gen_Info['raw_datafile_archive']."'>".$row_Gen_Info['raw_datafile_archive']."</a></td></tr>";
			echo "<tr> <td>OPA Name</td><td>".$row_Gen_Info['OPA_name']."</td></tr>";
			echo "<tr> <td>Experiment Short Name</td><td>".$experiment_short_name."</td></tr>";
			echo "<tr> <td>Data Program Code</td><td>".$data_program_code."</td></tr>";
			echo "<tr> <td>Data Program Name</td><td>".$data_program_name."</td></tr>";
			
			echo "</table>";
			  echo "<br>";
?>

  <?php 
 
  } /* end of else */
  
  } /* End of function type_DataInformation*/
  
  private function type_Tab_Delimiter()
  {
    $experiment_uid = $_GET['expuid'];
    
    $max_missing = 100;//IN PERCENT
        if (isset($_GET['mm']) && !empty($_GET['mm']) && is_numeric($_GET['mm']))
            $max_missing = $_GET['mm'];
		if ($max_missing>100)
			$max_missing = 100;
		elseif ($max_missing<0)
			$max_missing = 0;
        $min_maf = 0;//IN PERCENT
        if (isset($_GET['mmaf']) && !empty($_GET['mmaf']) && is_numeric($_GET['mmaf']))
            $min_maf = $_GET['mmaf'];
		if ($min_maf>100)
			$min_maf = 100;
		elseif ($min_maf<0)
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

			$res = mysql_query($sql_mstat) or die(mysql_error());
			$num_mark = mysql_num_rows($res);
			$num_maf = $num_miss = 0;

			while ($row = mysql_fetch_array($res)){
			  $maf = round(100*min((2*$row["sumaa"]+$row["sumab"])/(2*$row["total"]),($row["sumab"]+2*$row["sumbb"])/(2*$row["total"])),1);
			  $miss = round(100*$row["summis"]/$row["total"],1);
			  if (($maf > $min_maf)AND ($miss<=$max_missing)) {
			    $marker_names[] = $row["name"];
			    $outputheader .= $delimiter.$row["name"];
			    $marker_uid[] = $row["marker"];
			  }
			}
			$nelem = count($marker_uid);
			$marker_uid = implode(",",$marker_uid);
		
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


		$last_line = "some really silly name that noone would call a plant";
		$res = mysql_query($sql) or die(mysql_error());
		
		$outarray = $empty;
		$cnt = $num_lines = 0;
		while ($row = mysql_fetch_array($res)){
				//first time through loop
				if ($cnt==0) {
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

