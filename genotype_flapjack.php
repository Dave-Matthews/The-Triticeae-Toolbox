<?php 

// 01/24/2011 JLee  Redirect flapjack download file to /tmp/tht, address possible concurrency issue 


require_once('config.php');
include($config['root_dir'].'includes/bootstrap.inc');
connect();


new GenoType_FlapJack($_GET['function']);

class GenoType_FlapJack
{
    
    private $delimiter = "\t";
    
	
	// Using the class's constructor to decide which action to perform
	public function __construct($function = null)
	{	
		switch($function)
		{
			case 'type1experiments':
				$this->type1_experiments(); /* display experiments */
				break;
				
			case 'typeDownload':
				$this->type_Download(); /* display experiments */
				break;
				
				
				
			case 'typeFlapJack':
				$this->type_Flap_Jack(); /* Handle Flap Jack Compatible download */
				break;
			
			default:
				$this->typeGenoType(); /* intial case*/
				break;
			
		}	
	}


private function typeGenoType()
	{
		global $config;
		include($config['root_dir'].'theme/normal_header.php');

		echo "<h2>Search </h2>"; 
		echo "<p><em><b>Select multiple files by holding down the Ctrl key while selecting </b>
		</em>";
		echo "<img alt='spinner' id='spinner' src='images/ajax-loader.gif' style='display:none;' /></p>";
			
		$this->type_GenoType_Display();

		$footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
	}
	
	
	private function type_GenoType_Display()
	{
?>
<script type="text/javascript">
	
	var breeding_programs_str = "";
			var years_str = "";
			var experiments_str = "";
		
			

	  
	  function update_breeding_programs(options) {
				breeding_programs_str = "";
				
				$A(options).each(function(breeding_program) {
					if (breeding_program.selected) {
						breeding_programs_str += (breeding_programs_str == "" ? "" : ",") + breeding_program.value;
					}
				});
				
				
				if (breeding_programs_str != "" && years_str != "")
				{
					
					load_experiments();
					}
			}
			
			function update_years(options) {
				years_str = "";
				
				$A(options).each(function(year) {
					if (year.selected) {
						years_str += (years_str == "" ? "" : ",") + year.value;
					}
				});
				if ((breeding_programs_str != "") && (years_str != ""))
				{
				
					load_experiments();
					}
			}
			
			function load_experiments()
			{
                $('experiments_loader').hide();
                
				new Ajax.Updater(
                    $('experiments_loader'),
                    '<?php echo $_SERVER['PHP_SELF'] ?>?function=type1experiments&bp=' + breeding_programs_str + '&yrs=' + years_str,
					{ 
                        onComplete: function() {
                            $('experiments_loader').show();
                           
                        }
                    }
				);
				
			}
			
			/* tab delimiter function */
			function load_tab_delimiter(options)
			{
				 
				experiments_str = "";
				
				
				
				$A(options).each(function(experiments) {
					
					if (experiments.selected) {
					
						
						experiments_str +=  (experiments_str == "" ? "\"" : ",\"") + experiments.value + "\""  ;
						
					}
					
				});
				
				
				$('download_loader').hide();
                $('flapjack_loader').hide();
                
				new Ajax.Updater(
                    $('flapjack_loader'),
                    '<?php echo $_SERVER['PHP_SELF'] ?>?function=typeFlapJack&trialcode=' + experiments_str,
					{ 
                        onComplete: function() {
                            $('flapjack_loader').show();
                           
                        }
                    }
				); 
				
			}
			
			
			function download_tab_delimiter()
			{
			
			$('flapjack_loader').hide();
			$('download_loader').hide();
                
				new Ajax.Updater(
                    $('download_loader'),
                    '<?php echo $_SERVER['PHP_SELF'] ?>?function=typeDownload&trialcode=' + experiments_str,
					{   onCreate: function() { Element.show('spinner'); },
                        onComplete: function() {
                            $('download_loader').show();
                            Element.hide('spinner');
                        }
                    }
				); 
			
			
			}
			
</script>
	<style type="text/css">
			th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
			table {background: none; border-collapse: collapse}
			td {border: 0px solid #eee !important;}
			h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
		</style>
	<style type="text/css">
                   table.marker
                   {background: none; border-collapse: collapse}
                    th.marker
                    { background: #5b53a6; color: #fff; padding: 5px 0; border: 0; }
                    
                    td.marker
                    { padding: 5px 0; border: 0 !important; }
                </style>
		
		<div style="float: left; margin-bottom: 1.5em;">
		<h3>Data Program & Year</h3>
			<table>
				<tr>
					<th>Data Program</th>
					<th>Year</th>
				</tr>
				<tr>
					<td>
						<select name="breeding_programs" size="10" multiple="multiple" style="height: 12em;" onchange="javascript: update_breeding_programs(this.options)">
		<?php 
		// Original:
		//$sql = "SELECT breeding_programs_uid AS id, breeding_programs_name AS name FROM breeding_programs";
		// Select breeding programs for the drop down menu
		//Note this will need to change to allow data from all programs, breeding and mapping
		$sql = "SELECT CAPdata_programs_uid AS id, data_program_name AS name, data_program_code AS code
				FROM CAPdata_programs WHERE program_type='breeding' OR program_type='mapping' ORDER BY name";

		$res = mysql_query($sql) or die(mysql_error());
		while ($row = mysql_fetch_assoc($res))
		{
			?>
				<option value="<?php echo $row['id'] ?>"><?php echo $row['name']."(".$row['code'].")" ?></option>
			<?php
		}
		?>
						</select>
					</td>
					<td>
						<select name="year" size="10" multiple="multiple" style="height: 12em;" onchange="javascript: update_years(this.options)">
		<?php

		// set up drop down menu with data showing year
		

		$sql = "SELECT e.experiment_year AS year FROM experiments AS e, experiment_types AS et
				WHERE e.experiment_type_uid = et.experiment_type_uid
					AND et.experiment_type_name = 'genotype'
				GROUP BY e.experiment_year ASC";
		$res = mysql_query($sql) or die(mysql_error());
		while ($row = mysql_fetch_assoc($res)) {
			?>
				<option value="<?php echo $row['year'] ?>"><?php echo $row['year'] ?></option>
			<?php
		}
		?>
						</select>
					</td>
				</tr>
			</table>
		</div>
		<div id="experiments_loader" style="float: left; margin-bottom: 1.5em;"></div>
		<div id="flapjack_loader" style="float: left; margin-bottom: 1.5em;"></div>
		<div id="download_loader" style="float: left; margin-bottom: 1.5em;"></div>

<?php 
	} /* end of type_GenoType_Display function*/
	
	private function type1_experiments()
	{
		$CAPdata_programs = $_GET['bp']; 
		$years = $_GET['yrs']; 
	
	
	/* Query for getting experiment id, trial code and year */
	$sql = "SELECT DISTINCT e.experiment_uid AS id, e.trial_code as name, e.experiment_year AS year, e.traits AS traits
				FROM experiments AS e, datasets AS ds, datasets_experiments AS d_e, experiment_types AS e_t
				WHERE e.experiment_uid = d_e.experiment_uid
				AND d_e.datasets_uid = ds.datasets_uid
				AND ds.breeding_year IN ($years)
				AND ds.CAPdata_programs_uid IN ($CAPdata_programs)
				AND e.experiment_type_uid = e_t.experiment_type_uid
				AND e_t.experiment_type_name = 'genotype'";
	if (!authenticate(array(USER_TYPE_PARTICIPANT,
				USER_TYPE_CURATOR,
				USER_TYPE_ADMINISTRATOR)))
				$sql .= " AND e.data_public_flag > 0";

	$sql .= " ORDER BY e.experiment_year DESC";

		
	$res = mysql_query($sql) or die(mysql_error());
	$num_mark = mysql_num_rows($res);
	//check if any experiments are visible for this user
	if ($num_mark>0) {
?>

<div style="float: left; margin-bottom: 1.5em;">
		<h3> Experiments</h3>

<table>
	
	<tr><th>Experiment Details</th></tr>
	<tr><td>
		<select name="experiments" multiple="multiple" size="10" style="height: 12em" onchange="javascript:load_tab_delimiter(this.options)">
<?php
	
		while ($row = mysql_fetch_array($res)) {
			?>
			<!-- Display Map names-->		
				<option value="<?php echo $row['name'] ?>"><?php echo $row['name'] ?></option>
			<?php
		}
		?>
	
		</select>
	</td></tr>
</table>
</div>


 

	
	<?php 
	}/* end of if condition */
	else
	{
	?>	<div class="section">
<p>There are no publicly available genotype datasets for this program and year in T3 at this time. 
 Registered users may see additional datasets after signing in.
            </div>
  <?php 
	}/* end of else */
	} /* end of type1_experiments function */
	private function type_Flap_Jack()
	{
		
		
		echo " <b>If you are done with your selections and ready for download, press OK and wait for download button to appear </b> <br/><br/>";
		?>
		
		<input type="button" value="OK" onclick="javascript:download_tab_delimiter()" />
		
		
		
<?php 	
	}/* end of type_Flap_Jack function */
	
private function type_Download()
{
	// J.Lee Mod to support magic_quote_gpc being on 
    $trial_code = stripslashes($_GET['trialcode']);
    $trial_code = stripslashes($trial_code);
		
		$myFile = "/tmp/tht/tht_FlapJack_genotype_".chr(rand(65,80)).chr(rand(65,80)).chr(rand(64,80)).".txt";
		if (! file_exists('/tmp/tht')) mkdir('/tmp/tht');			
		$fh = fopen($myFile, 'w') or die("can't open file"); 
		
		$sql = "SELECT CAPdata_programs_uid, experiment_type_uid, experiment_uid, experiment_short_name FROM experiments where trial_code IN ($trial_code) ";

		$res = mysql_query($sql) or die(mysql_error());
		
		while ($row = mysql_fetch_assoc($res)){
	
		$experiment_uid[] = $row['experiment_uid'];
		
		}/* end of while loop */
		
		$experiment_uid = implode(",",$experiment_uid);
		
		
		
		$max_missing = 100;//IN PERCENT
        
		$min_maf = 0;//IN PERCENT
        
	
		$outputheader = '';
		$output = '';
		$doneheader = false;
		$delimiter ="\t";
		

	 //get lines and filter to get a list of markers which meet the criteria selected by the user
         
				  $sql_mstat = "SELECT af.marker_uid as marker, m.marker_name as name, SUM(af.aa_cnt) as sumaa, SUM(af.missing)as summis, SUM(af.bb_cnt) as sumbb,
					SUM(af.total) as total, SUM(af.ab_cnt) AS sumab
					FROM allele_frequencies AS af, markers as m
					WHERE m.marker_uid = af.marker_uid
						AND af.experiment_uid IN ($experiment_uid)
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
			$marker_uid = implode(",",$marker_uid);

        
		
		  $lookup = array(
			  'AA' => 'AA',
			  'BB' => 'BB',
			  '--' => '-',
			  'AB' => 'AB'
		  );
	    
	
		// get a list of marker names which meet the criteria selected by the user
		  if (empty($marker_uid)) {
		    echo "No marker allele data found.<p>";
		  }
		  else {
					$sql_mstat = "SELECT marker_name as name
					FROM markers
					WHERE marker_uid IN ($marker_uid)"; 
					
			$res = mysql_query($sql_mstat) or die("At script line 414:".mysql_error());

			while ($row = mysql_fetch_array($res)){
						$marker_names[] = $row["name"];
			}
		  }
			$nelem = count($marker_uid);

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
				AND tb.experiment_uid IN ($experiment_uid)
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
		  
	
				
		  
		 $stringData .= $outputheader."\n".$output;
		  
		
				
			
			//echo $outputheader."\n".$output;
			fwrite($fh, $stringData);
			
			fclose($fh);
?>
	
	<div style="padding: 0; height: 100px; width: 810px;  overflow: hidden; solid #5b53a6;">
		<table>
		<tr>
		<td>
		<form action='<?php echo $myFile ?>'>
<input type='submit' value='Download'/>
</form>
</tr>
</td>
</table>
</div>




<?php



} /* end of function type_Download */


} /* end of class */

?>
