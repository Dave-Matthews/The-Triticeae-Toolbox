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
		    case 'type1':
		        $this->type1();
		        break;
		        
			case 'type1experiments':
				$this->type1_experiments(); /* display experiments */
				break;
				
			case 'typeDownload':
				$this->type_Download(); /* display experiments */
				break;
				
			case 'typeDownload2':
			     $this->type_Download2();
			     break;
						
			case 'typeFlapJack':
				$this->type_Flap_Jack(); /* Handle Flap Jack Compatible download */
				break;
				
			case 'typeFlapJack2':
			    $this->type_Flap_Jack2();
			    break;
			    
		    case 'refreshtitle':
				echo $this->refresh_title();
				break;
				
		    case 'step1breedprog':
		        echo $this->step1_breedprog();
		        break;
		      
		    case 'step1lines':
		        echo $this->step1_lines();
		        break;
		        
		    case 'step2lines':
		        echo $this->step2_lines();
		        break;
			
			default:
			    $this->type1_select();
				break;
			
		}	
	}

private function refresh_title()
{
   echo "<h2>Search </h2>";
   echo "<p><em><b>Select multiple files by holding down the Ctrl key while selecting </b></em>";
   if (isset($_SESSION['selected_lines'])) {
     ?>
     <input type="button" value="Clear current selection" onclick="javascript: use_normal();"/>
     <?php
   }
}

private function type1_select()
{
     global $config;
     include($config['root_dir'].'theme/normal_header.php');
     $this->type1_checksession();
     $footer_div = 1;
     include($config['root_dir'].'theme/footer.php');
}

private function type1()
{
  unset($_SESSION['selected_lines']);
  unset($_SESSION['phenotype']);
  unset($_SESSION['clicked_buttons']);

  ?>
  <p>1.
  <select name="select1" onchange="javascript: update_select1(this.options)">
  <option value="BreedingProgram">Data Program</option>
  <option <?php
  if (isset($_SESSION['selected_lines'])) {
      echo "selected='selected'";
  }
  ?> value="Lines">Lines</option>
  </select></p>
  <div id="step11" style="float: left; margin-bottom: 1.5em;">
  <?php
  $this->step1_breedprog();
  $footer_div = 1;
  ?>
  </div>
  <?php 
}
private function type1_checksession()
{
  ?>
  <style type="text/css">
  th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
  table {background: none; border-collapse: collapse}
  td {border: 0px solid #eee !important;}
  h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
  </style>
  <div id="title">
  <?php 
  if (isset($_SESSION['selected_lines'])) {
    $countLines = count($_SESSION['selected_lines']);
    $lines = $_SESSION['selected_lines'];
  }
  $this->refresh_title(); 
  echo "<img alt='spinner' id='spinner' src='images/ajax-loader.gif' style='display:none;' /></p>";
  ?>
  </div>
  <div id="step1" style="float: left; margin-bottom: 1.5em;">
  <p>1.
  <select name="select1" onchange="javascript: update_select1(this.options)">
  <option value="BreedingProgram">Data Program</option>
  <option <?php
  if (isset($_SESSION['selected_lines'])) {
      echo "selected='selected'";
  }
  ?> value="Lines">Lines</option>
  </select></p>
  <script type="text/javascript" src="downloads/genotype_flapjack.js"></script>
  <?php 
  if (isset($_SESSION['selected_lines'])) {
    $this->type1_lines_trial_trait();
  } else {
    $this->typeGenoType();
  }
  ?>
  <?php 
}

private function step1_breedprog()
{
  ?>
  <table>
  <tr>
  <th>Data Program</th>
  <th>Year</th>
  </tr>
  <tr>
  <td>
  <select name="breeding_programs" size="10" multiple="multiple" style="height: 12em;" onchange="javascript: update_breeding_programs(this.options)">
  <?php
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
  </td><td>
  <select name="year" size="10" multiple="multiple" style="height: 12em;" onchange="javascript: update_years(this.options)">
  <?php
 
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
  <?php 
}

private function type1_lines_trial_trait()
{
  ?>
  <div id="step11" style="float: left; margin-bottom: 1.5em;">
  <?php
  $this->step1_lines();
  ?>
  </div></div>
  <div id="step2" style="float: left; margin-bottom: 1.5em;">
  <?php
  $this->step2_lines();
  ?>
  </div>
  <div id="step3" style="float: left; margin-bottom: 1.5em;"></div>
  <div id="step4" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%">
  <?php
  $this->type_Flap_Jack2();
  ?>
  </div>
  <?php 
}

private function step1_lines()
{
        if (isset($_SESSION['selected_lines'])) {
            $selectedlines= $_SESSION['selected_lines'];
            $count = count($_SESSION['selected_lines']);
            ?>
            <table id="phenotypeSelTab" class="tableclass1">
            <tr>
            <th>Lines</th>
            </tr>
            <tr><td>
            <select name="lines" multiple="multiple" style="height: 12em;">
            <?php
            foreach($selectedlines as $uid) {
              $sql = "SELECT line_record_name from line_records where line_record_uid = $uid";
              $res = mysql_query($sql) or die(mysql_error());
              $row = mysql_fetch_assoc($res)
              ?>
              <option disabled="disabled" value="
              <?php $uid ?>">
              <?php echo $row['line_record_name'] ?>
              </option>
              <?php
            }
            ?>
            </select>
            </td>
            </table>
            <?php
        } else {
          echo "Please select lines before using this feature.<br>";
          echo "<a href=";
          echo $config['base_url'];
          echo "pedigree/line_properties.php>Select Lines by Properties</a>";
        }
}

private function step2_lines()
{
  if (isset($_SESSION['selected_lines'])) {
    $selectedlines= $_SESSION['selected_lines'];
    $count = count($_SESSION['selected_lines']);
    ?>
    <p>2.
    <select name="select2">
    <option value="trials">Trials</option>
    </select></p>
    <table id="linessel" class="tableclass1">
    <tr>
    <th>Trials</th>
    </tr>
    <tr><td>
    <select name="trials" multiple="multiple" style="height: 12em;">
    <?php
    $selectedlines= $_SESSION['selected_lines'];
    $selectedlines = implode(',', $selectedlines);
    
    $sql = "SELECT DISTINCT e.experiment_uid AS id, e.trial_code as name, e.experiment_year AS year, e.traits AS traits
    FROM experiments AS e, tht_base as tb, line_records as lr, experiment_types AS e_t
    WHERE e.experiment_uid = tb.experiment_uid
    AND lr.line_record_uid = tb.line_record_uid
    AND e.experiment_type_uid = e_t.experiment_type_uid
    AND e_t.experiment_type_name = 'genotype'
    AND lr.line_record_uid IN ($selectedlines)";
    if (!authenticate(array(USER_TYPE_PARTICIPANT,
      USER_TYPE_CURATOR,
      USER_TYPE_ADMINISTRATOR)))
     $sql .= " AND e.data_public_flag > 0";
    
    $res = mysql_query($sql) or die(mysql_error());
    while ($row = mysql_fetch_assoc($res))
    {
     ?>
    <option disabled="disabled" value="<?php echo $row['id'] ?>">
    <?php echo $row['name'] ?>
    </option>
    <?php
    }
    ?>
    </select></table>
    <?php 
  }
}

private function typeGenoType()
	{
		echo "<img alt='spinner' id='spinner' src='images/ajax-loader.gif' style='display:none;' /></p>";
			
		$this->type_GenoType_Display();

		$footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
	}
	
	
	private function type_GenoType_Display()
	{
    ?>
	
	<style type="text/css">
                   table.marker
                   {background: none; border-collapse: collapse}
                    th.marker
                    { background: #5b53a6; color: #fff; padding: 5px 0; border: 0; }
                    
                    td.marker
                    { padding: 5px 0; border: 0 !important; }
                </style>
		
		<div id ="step11" style="float: left; margin-bottom: 1.5em;">
		
			<table>
				<tr>
					<th>Data Program</th>
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
					</td></table>
			        </div></div>
			        <div id="step2" style="float: left; margin-bottom: 1.5em;">
			        <p>2.
			        <select name="select2"> 
			          <option value="DataProgram">Year</option>
			        </select></p>
			        <table>
			          <tr><th>Year</th>
					<tr><td>
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
		<div id="step2" style="float: left; margin-bottom: 1.5em;"></div>
		<div id="step3" style="float: left; margin-bottom: 1.5em;"></div>
		<div id="step4" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%"></div>

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

    <p>3.
    <select name="select1">
      <option value="DataProgram">Experiments</option>
    </select></p>

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



 

	
	<?php 
	}/* end of if condition */
	else
	{
	?>	<div class="section">
<p> There are no publicly available genotype datasets for this program and year in T3 at this time
 Registered users may see additional datasets after signing in.</p>
            </div>
  <?php 
	}/* end of else */
	} /* end of type1_experiments function */
	
	private function type_Flap_Jack()
	{	
	    ?>
		<b>If you are done with your selections and ready for download, press OK and wait for download button to appear </b> <br/><br/>		
		<input type="button" value="OK" onclick="javascript:download_tab_delimiter()" />
        <?php 	
	}/* end of type_Flap_Jack function */
	
	private function type_Flap_Jack2()
	{
	  if (isset($_SESSION['selected_lines'])) {
	    ?>
	    <b>If you are done with your selections and ready for download, press OK and wait for download button to appear </b> <br/><br/>
        <input type="button" value="OK" onclick="javascript:download_tab_delimiter2()" />
	    <?php
	  }
	}
	
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
	
	<!--  div style="padding: 0; height: 100px; width: 810px;  overflow: hidden; solid #5b53a6;"-->
		<p>
		<form action='<?php echo $myFile ?>'>
<input type='submit' value='Download'/>
</form>
</p>
<!--   /div -->




<?php



} /* end of function type_Download */

private function type_Download2()
{

 $myFile = "/tmp/tht/tht_FlapJack_genotype_".chr(rand(65,80)).chr(rand(65,80)).chr(rand(64,80)).".txt";
 if (! file_exists('/tmp/tht')) mkdir('/tmp/tht');
 $fh = fopen($myFile, 'w') or die("can't open file");

 $max_missing = 100;//IN PERCENT

 $min_maf = 0;//IN PERCENT

 $outputheader = '';
 $output = '';
 $doneheader = false;
 $delimiter ="\t";
 
 $lines= $_SESSION['selected_lines'];
 $lines_str = implode(",",$lines);
 
 //get lines and filter to get a list of markers which meet the criteria selected by the user
 if (preg_match('/[0-9]/',$markers_str)) {
 } else {
  //get genotype markers that correspond with the selected lines
  $sql_exp = "SELECT DISTINCT marker_uid
  FROM allele_cache
  WHERE
  allele_cache.line_record_uid in ($lines_str)";
  $res = mysql_query($sql_exp) or die(mysql_error() . "<br>" . $sql_exp);
  if (mysql_num_rows($res)>0) {
   while ($row = mysql_fetch_array($res)){
    $markers[] = $row["marker_uid"];
   }
  }
  $markers_str = implode(',',$markers);
 }
 
 //generate an array of selected markers that can be used with isset statement
 foreach ($markers as $temp) {
  $marker_lookup[$temp] = 1;
 }
 //echo "<pre>";
 //print_r($marker_lookup);
 //echo "</pre>";
 $sql = "select marker_uid, marker_name from allele_byline_idx order by marker_uid";
 $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
 $i=0;
 while ($row = mysql_fetch_array($res)) {
  $marker_list[$i] = $row[0];
  $marker_list_name[$i] = $row[1];
  $i++;
 }
 
 foreach ($lines as $line_record_uid) {
  $sql = "select alleles from allele_byline where line_record_uid = $line_record_uid";
  $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
  if ($row = mysql_fetch_array($res)) {
   $alleles = $row[0];
   $outarray = explode(',',$alleles);
   $i=0;
   foreach ($outarray as $allele) {
    if ($allele=='AA') $marker_aacnt[$i]++;
    if (($allele=='AB') or ($allele=='BA')) $marker_abcnt[$i]++;
    if ($allele=='BB') $marker_bbcnt[$i]++;
    if ($allele=='--') $marker_misscnt[$i]++;
    $i++;
   }
  }
  //echo "$line_record_uid<br>\n";
 }

 $num_maf = $num_miss = 0;
 
 foreach ($marker_list as $i => $marker_id) {
  $marker_name = $marker_list_name[$i];
  if (isset($marker_lookup[$marker_id])) {
   $total = $marker_aacnt[$i] + $marker_abcnt[$i] + $marker_bbcnt[$i] + $marker_misscnt[$i];
   if ($total>0) {
    $maf[$i] = round(100 * min((2 * $marker_aacnt[$i] + $marker_abcnt[$i]) /$total, ($marker_abcnt[$i] + 2 * $marker_bbcnt[$i]) / $total),1);
    $miss[$i] = round(100*$marker_misscnt[$i]/$total,1);
   } else {
    $maf[$i] = 0;
    $miss[$i] = 100;
   }
   if (($maf[$i] >= $min_maf) AND ($miss[$i]<=$max_missing)) {
    $marker_names[] = $marker_name;
    $outputheader .= $delimiter.$marker_name;
    $marker_uid[] = $marker_id;
    //echo "accept $marker_id $marker_name $maf[$i] $miss[$i]<br>\n";
   } else {
    //echo "reject $marker_id $marker_name $maf $miss<br>\n";
   }
  } else {
   //echo "rejected marker $marker_id<br>\n";
  }
 }
 
 $lookup = array(
   'AA' => 'AA',
   'BB' => 'BB',
   '--' => '-',
   'AB' => 'AB'
 );
 
 foreach ($lines as $line_record_uid) {
  $sql = "select line_record_name, alleles from allele_byline where line_record_uid = $line_record_uid";
  $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
  if ($row = mysql_fetch_array($res)) {
   $outarray2 = array();
   $outarray2[] = $row[0];
   $alleles = $row[1];
   $outarray = explode(',',$alleles);
   $i=0;
   foreach ($outarray as $allele) {
    $marker_id = $marker_list[$i];
    if (isset($marker_lookup[$marker_id])) {
     if (($maf[$i] >= $min_maf) AND ($miss[$i]<=$max_missing)) {
      $outarray2[]=$lookup[$allele];
     }
    }
    $i++;
   }
  } else {
   $sql = "select line_record_name from line_records where line_record_uid = $line_record_uid";
   $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
   if ($row = mysql_fetch_array($res)) {
    $outarray2 = array();
    $outarray2[] = $row[0];
   } else {
    die("error - could not find uid\n");
   }
  }
  $outarray = implode($delimiter,$outarray2);
  $output .= $outarray . "\n";
 }
 $nelem = count($marker_names);
 $num_lines = count($lines);
 if ($nelem == 0) {
  die("error - no genotype or marker data for this selection");
 }
 // make an empty line with the markers as array keys, set default value
 //  to the default missing value for either qtlminer or tassel
 // places where the lines may have different values
 
 if ($dtype =='qtlminer')  {
  $empty = array_combine($marker_names,array_fill(0,$nelem,'NA'));
 } else {
  $empty = array_combine($marker_names,array_fill(0,$nelem,'?'));
 }
 
  $stringData .= $outputheader."\n".$output;
  
  //echo $outputheader."\n".$output;
  fwrite($fh, $stringData);
  
  fclose($fh);
  ?>
  
  <p>
  <form action='<?php echo $myFile ?>'>
  <input type='submit' value='Download'/>
  </form>
  </p>

<?php



} /* end of function type_Download */


} /* end of class */

?>
