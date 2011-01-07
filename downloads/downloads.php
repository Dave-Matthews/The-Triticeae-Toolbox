<?php
// +----------------------------------------------------------------------+
// | PHP version 5.0                                                      |
// | Prototype version 1.5.0                                              |
// +----------------------------------------------------------------------+
// | "Download Gateway"                                                   |
// |                                                                      |
// | The purpose of this script is to provide the user with an interface  |
// | for downloading certain kinds of files from THT.                     |
// +----------------------------------------------------------------------+
// | Authors:  Gavin Monroe <gemonroe@iastate.edu>  						|
// | Updated: December 2008 by Julie A Dickerson, julied@iastate.edu	  |
// +----------------------------------------------------------------------+
// +----------------------------------------------------------------------+
// | Change log															  |
// | 1/5/01:  JLee - Add support to generate datafile for Tassel V3       |  
// |                                                                      |
// | 2/28/09: removed table summarizing all allelles to avoid timeout	  |
// |          problems when getting SNP data across multiple programs
// |May 2009: added in tassel support functionality and commented out
// | 			routines for QTLMiner
// | September 2009: added in the ability put check lines into the output |
// |			file for traits; if there are multiple check lines of the |
// | 			same name, then the mean is used.	Also added in seesions  |
// | 			to verify that data is available for a user.			  |
// +----------------------------------------------------------------------+
set_time_limit(0);
// this is the relative path to this file
$cfg_file_rel_path = 'downloads/downloads.php';
// For live website file
require_once('config.php');
include($config['root_dir'].'includes/bootstrap.inc');
set_include_path(get_include_path . PATH_SEPARATOR . '../pear/');


require_once($config['root_dir'].'includes/MIME/Type.php');
require_once($config['root_dir'].'includes/File_Archive/Archive.php');

// for debugging only, comment out in production version to avoid security holes
//require_once('FirePHPCore/FirePHP.class.php');
//ob_start();
//$firephp = FirePHP::getInstance(true);

// connect to database
connect();


new Downloads($_GET['function']);
//
// Using a PHP class to implement the "Download Gateway" feature
class Downloads
{
    
    private $delimiter = "\t";
    
	//
	// Using the class's constructor to decide which action to perform
	public function __construct($function = null)
	{	
		switch($function)
		{
			case 'type1':
				$this->type1();
				break;
			case 'type1experiments':
				$this->type1_experiments();
				break;
			case 'type1traits':
				$this->type1_traits();
				break;
			case 'type1markers':
				$this->type1_markers();
				break;
			case 'type1build_qtlminer':
				$this->type1_build_qtlminer();
				break;
			case 'type1build_tassel':
				echo $this->type1_build_tassel();
				break;
			case 'type1build_tassel_v3':
				echo $this->type1_build_tassel_v3();
				break;

			default:
				$this->type1();
				break;
				
		}	
	}
	
	//
	// The wrapper action for the type1 download. Handles outputting the header
	// and footer and calls the first real action of the type1 download.
	private function type1()
	{
		global $config;
		include($config['root_dir'].'theme/normal_header.php');

		echo "<h2>Tassel Download</h2>";
		echo "<p><em>Select multiple options by holding down the Ctrl key while clicking.
		</em></p>";
	
		$this->type1_breeding_programs_year();

		$footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
	}
	
	//
	// The first real action of the type1 download. Handles outputting the
	// Breeding Program and Year selection boxes as well as outputting the
	// javascript code required by itself and the other type1 actions.
	private function type1_breeding_programs_year()
	{
		?>
		<script type="text/javascript">
			
			var breeding_programs_str = "";
			var years_str = "";
			var experiments_str = "";
			var experiments_loaded = false;
			var traits_loaded = false;
			var markers_loaded = false;
            
            var markerids = null;
            var selmarkerids = new Array();
            
            var markers_loading = false;
            var traits_loading = false;
            
            var title = document.title;
			
			function update_breeding_programs(options) {
				breeding_programs_str = "";
				experiments_str = "";
				$A(options).each(function(breeding_program) {
					if (breeding_program.selected) {
						breeding_programs_str += (breeding_programs_str == "" ? "" : ",") + breeding_program.value;
					}
				});
				if (breeding_programs_str != "" && years_str != "")
					load_experiments();
			}
			
			function update_years(options) {
				years_str = "";
				experiments_str = "";
				$A(options).each(function(year) {
					if (year.selected) {
						years_str += (years_str == "" ? "" : ",") + year.value;
					}
				});
				if (breeding_programs_str != "" && years_str != "")
					load_experiments();
			}
			
			function update_experiments(options) {
				experiments_str = ""; // clears experiment setting to avoid trait perserverance
				$A(options).each(function(experiment) {
					if (experiment.selected) {
						experiments_str += (experiments_str == "" ? "" : ",") + experiment.value;
					}
				});
				load_traits();
				load_markers('', '', 100, 0);
			}

			function load_experiments()
			{
                $('experiments_loader').hide();
                document.title='Loading Trials...';
				new Ajax.Updater(
                    $('experiments_loader'),
                    '<?php echo $_SERVER['PHP_SELF'] ?>?function=type1experiments&bp=' + breeding_programs_str + '&yrs=' + years_str,
					{ 
                        onComplete: function() {
                            $('experiments_loader').show();
                            document.title=title;
                        }
                    }
				);
				experiments_loaded = true;
				if (traits_loaded == true)
					load_traits();
				if (markers_loaded == true)
					load_markers(100, 0);
			}
			
			function load_traits()
			{		
                traits_loading = true;		
                $('traits_loader').hide();
                document.title='Loading Traits...';
				new Ajax.Updater(
				    $('traits_loader'),
				    '<?php echo $_SERVER['PHP_SELF'] ?>?function=type1traits&exps='+experiments_str,
					{onComplete: function() {
                        $('traits_loader').show();  
                        if (markers_loading == false) {
                            document.title = title;
                        }
                        traits_loading = false;
                        traits_loaded = true;
                    }}
				);
			}
            

			function load_markers( mm, mmaf) {
                markers_loading = true;
				$('markers_loader').hide();
                document.title='Loading Markers...';
				//changes are right here
                new Ajax.Updater($('markers_loader'), '<?php echo $_SERVER['PHP_SELF'] ?>?function=type1markers&bp='+ breeding_programs_str+'&yrs='+ years_str+'&exps='+experiments_str+'&mm='+mm+'&mmaf='+mmaf,
				//new Ajax.Updater($('markers_loader'), '<?php echo $_SERVER['PHP_SELF'] ?>?function=type1markers&exps='+experiments_str+'&o='+o+'&d='+d+'&m='+selmarkerids.join(',')+'&mm='+mm+'&mmaf='+mmaf,
					{onComplete: function() {
                         $('markers_loader').show();
                        if (traits_loading == false) {
                            document.title = title;
                        }
                        markers_loading = false;
                        markers_loaded = true;
                    }}
				);
			}
			
			function selectedTraits() {
				var ret = '';
				$A($('traitsbx').options).each(function(trait){
				 	if (trait.selected)
					{
						ret += (ret == '' ? '' : ',') + trait.value;
					}			 
				});
				return ret;
			}
			

			function getdownload_qtlminer()
			{
				if (selectedTraits() == '') {
					alert('Please select at least one trait!');
					return false;
				}
			    var mm = $('mm').getValue();
                var mmaf = $('mmaf').getValue();
				
					document.location = '<?php echo $_SERVER['PHP_SELF'] ?>?function=type1build_qtlminer&bp='+ breeding_programs_str+'&yrs='+ years_str+'&t='+selectedTraits()+'&e='+experiments_str+'&mm='+mm+'&mmaf='+mmaf;
				
			}

			function getdownload_tassel()
			{
				if (selectedTraits() == '') {
					alert('Please select at least one trait!');
					return false;
				}
			    var mm = $('mm').getValue();
                var mmaf = $('mmaf').getValue();

<<<<<<< HEAD
			    var subset = $('subset').getValue();

				document.location = '<?php echo $_SERVER['PHP_SELF'] ?>?function=type1build_tassel&bp='+ breeding_programs_str+'&yrs='+ years_str+'&t='+selectedTraits()+'&e='+experiments_str+'&mm='+mm+'&mmaf='+mmaf+'&subset='+subset;
=======
			var subset = $('subset').getValue();

					document.location = '<?php echo $_SERVER['PHP_SELF'] ?>?function=type1build_tassel&bp='+ breeding_programs_str+'&yrs='+ years_str+'&t='+selectedTraits()+'&e='+experiments_str+'&mm='+mm+'&mmaf='+mmaf+'&subset='+subset;
>>>>>>> 469bffd094e6fd3b5510c99bcc50a9338ddd9345

			}

			function getdownload_tassel_v3()
			{
				if (selectedTraits() == '') {
					alert('Please select at least one trait!');
					return false;
				}
			    var mm = $('mm').getValue();
                var mmaf = $('mmaf').getValue();

    			var subset = $('subset').getValue();

				document.location = '<?php echo $_SERVER['PHP_SELF'] ?>?function=type1build_tassel_v3&bp='+ breeding_programs_str+'&yrs='+ years_str+'&t='+selectedTraits()+'&e='+experiments_str+'&mm='+mm+'&mmaf='+mmaf+'&subset='+subset;

			}

            function mrefresh() {
                var mm = $('mm').getValue();
                var mmaf = $('mmaf').getValue();
                load_markers( mm, mmaf);
            }
            

		</script>
		<style type="text/css">
			th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
			table {background: none; border-collapse: collapse}
			td {border: 1px solid #eee !important;}
			h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
		</style>
		<div style="float: left; margin-bottom: 1.5em;">
		<h3>1. Breeding Program & Year</h3>
			<table>
				<tr>
					<th>Breeding Program</th>
					<th>Year</th>
				</tr>
				<tr>
					<td>
						<select name="breeding_programs" multiple="multiple" style="height: 12em;" onchange="javascript: update_breeding_programs(this.options)">
		<?php
		// Original:
		//$sql = "SELECT breeding_programs_uid AS id, breeding_programs_name AS name FROM breeding_programs";
		// Select breeding programs for the drop down menu
		$sql = "SELECT CAPdata_programs_uid AS id, data_program_name AS name, data_program_code AS code
				FROM CAPdata_programs WHERE program_type='breeding' ORDER BY name";

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
						<select name="year" multiple="multiple" style="height: 12em;" onchange="javascript: update_years(this.options)">
		<?php

		// set up drop down menu with data showing year
		// should this be phenotype experiments only? No

		$sql = "SELECT e.experiment_year AS year FROM experiments AS e, experiment_types AS et
				WHERE e.experiment_type_uid = et.experiment_type_uid
					AND et.experiment_type_name = 'phenotype'";
		if (!authenticate(array(USER_TYPE_PARTICIPANT,
					USER_TYPE_CURATOR,
					USER_TYPE_ADMINISTRATOR)))
			$sql .= " and data_public_flag > 0";
		$sql .= " GROUP BY e.experiment_year ASC";
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
		<div id="traits_loader" style="float: left; margin-bottom: 1.5em;"></div>
		<div id="markers_loader" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%"></div>
<?php
	}
	
	private function type1_experiments()
	{
		$CAPdata_programs = $_GET['bp']; //"'" . implode("','", explode(',',$_GET['bp'])) . "'";
		$years = $_GET['yrs']; //"'" . implode("','", explode(',',$_GET['yrs'])) . "'";
?>
<h3>2. Trials</h3>
<div>

<table>
	<tr><th>Trials</th></tr>
	<tr><td>
		<select name="experiments" multiple="multiple"
		  style="height: 12em" onchange="javascript: update_experiments(this.options)">
<?php
//	List phenotype experiments associated with a list of breeding programs and years selected by the user,
//  needs to used datasets/experiments 
//	linking table.

		$sql = "SELECT DISTINCT e.experiment_uid AS id, e.trial_code as name, e.experiment_year AS year
				FROM experiments AS e, datasets AS ds, datasets_experiments AS d_e, experiment_types AS e_t
				WHERE e.experiment_uid = d_e.experiment_uid
				AND d_e.datasets_uid = ds.datasets_uid
				AND ds.breeding_year IN ($years)
				AND ds.CAPdata_programs_uid IN ($CAPdata_programs)
				AND e.experiment_type_uid = e_t.experiment_type_uid
				AND e_t.experiment_type_name = 'phenotype'
				ORDER BY e.experiment_year DESC, e.trial_code";
				
		$res = mysql_query($sql) or die(mysql_error());
		$last_year = NULL;
		while ($row = mysql_fetch_assoc($res)) {			
			if ($last_year == NULL) {
?>
			<optgroup label="<?php echo $row['year'] ?>">
<?php
				$last_year = $row['year'];
			} else if ($row['year'] != $last_year) {
?>
			</optgroup>
			<optgroup label="<?php echo $row['year'] ?>">
<?php
				$last_year = $row['year'];
			}
?>
				<option value="<?php echo $row['id'] ?>"><?php echo $row['name'] ?></option>
<?php
		}
?>
			</optgroup>
		</select>
	</td></tr>
</table>
</div>
<?php
	}
	
	private function type1_traits()
	{
		$experiments = $_GET['exps'];
		
		if (empty($experiments))
		{
			echo "
				<h3>3. Traits</h3>
				<div>
					<p><em>No Trials Selected</em></p>
				</div>";
		}
		else
		{
?>
<h3>3. Traits</h3>
<div>
<?php
// List all traits associated with a list of experiments


			$sql = "SELECT p.phenotype_uid AS id, p.phenotypes_name AS name
					FROM phenotypes AS p, tht_base AS t, phenotype_data AS pd
					WHERE pd.tht_base_uid = t.tht_base_uid
					AND p.phenotype_uid = pd.phenotype_uid
					AND t.experiment_uid IN ($experiments)
					GROUP BY p.phenotype_uid";

			$res = mysql_query($sql) or die(mysql_error());
			if (mysql_num_rows($res) >= 1)
			{
?>
<table>
	<tr><th>Trait</th></tr>
	<tr><td>
		<select id="traitsbx" name="traits" multiple="multiple" style="height: 12em">
<?php
				while ($row = mysql_fetch_assoc($res))
				{
?>
			<option value="<?php echo $row['id'] ?>"><?php echo $row['name'] ?></option>
<?php
				}
?>
		</select>
	</td></tr>
</table>
<?php
			}
			else
			{
?>
		<p style="font-weight: bold;">No Data</p>
<?php
			}
?>
</div>
<?php
		}
	}
	
	/**
	 * Gets the key marker data for the selected breeding programs
	 */
	private function type1_markers()
	{
		//global $cfg_file_rel_path;//do we need this?
		
        // parse url
        $experiments = $_GET['exps'];
		$CAPdataprogram = $_GET['bp'];
		$years = $_GET['yrs'];
		
	/**
	 * Use currently selected lines?
	 */
	if (count($_SESSION['selected_lines']) > 0) {
	  $sub_ckd = "checked"; $all_ckd = "";
	}
	else {
	  $sub_ckd = "disabled"; $all_ckd = "checked";
	}
		?>
	<h3>4. Lines</h3>
				<input type="radio" name="subset" id="subset" value="yes" <?php echo "$sub_ckd"; ?>>Include 
only <a href="<?php echo $config['base_url']; ?>pedigree/line_selection.php">currently 
selected lines</a>.<br>
				<input type="radio" name="subset" id="subset" value="no" <?php echo "$all_ckd"; ?>>Include all.<br>

        <h3>5. Markers</h3>
		<div>
		<?php
		//// $firephp = FirePHP::getInstance(true);
		//// $firephp->log($experiments);
		    		
		// initialize markers and flags if not already set
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
       // // $firephp->log($min_maf," maf");
		//// $firephp->log($max_missing," max missing");

			//get genotype experiments that correspond with the Datasets (BP and year) selected for the experiments
			$sql_exp = "SELECT DISTINCT cd.data_program_name as name,e.experiment_uid AS exp_uid, e.trial_code, e.experiment_year as year
							FROM experiments e, experiment_types et, datasets as ds, datasets_experiments as dse, CAPdata_programs as cd
							WHERE
								e.experiment_type_uid = et.experiment_type_uid
								AND et.experiment_type_name = 'genotype'
								AND e.experiment_uid = dse.experiment_uid
								AND dse.datasets_uid = ds.datasets_uid
								AND cd.CAPdata_programs_uid = ds.CAPdata_programs_uid
								AND ds.breeding_year IN ($years)
								AND ds.CAPdata_programs_uid IN ($CAPdataprogram)
							ORDER BY name, year ";
			$res = mysql_query($sql_exp) or die(mysql_error());
			
			if (mysql_num_rows($res)>0) {
				while ($row = mysql_fetch_array($res)){
					$exp[] = $row["exp_uid"];
				}
				
				$exp = implode(',',$exp);
				//// $firephp->log($exp,"genotype experiment");
				
				 $sql_mstat = "SELECT af.marker_uid as marker, SUM(af.aa_cnt) as sumaa, SUM(af.missing)as summis, SUM(af.bb_cnt) as sumbb,
						SUM(af.total) as total, SUM(af.ab_cnt) AS sumab
						FROM allele_frequencies AS af
						WHERE af.experiment_uid in ($exp)
						group by af.marker_uid"; 
	
				$res = mysql_query($sql_mstat) or die(mysql_error());
				$num_mark = mysql_num_rows($res);
				$num_maf = $num_miss = $num_removed = 0;
				
				while ($row = mysql_fetch_array($res)){
					$marker_uid[] = $row["marker"];
// 					$maf = round(100*min((2*$row["sumaa"]+$row["sumab"])/$row["total"],($row["sumab"]+2*$row["sumbb"])/$row["total"]),1);
					$maf = round(100*min((2*$row["sumaa"]+$row["sumab"])/(2*$row["total"]),($row["sumab"]+2*$row["sumbb"])/(2*$row["total"])),1);
					$miss = round(100*$row["summis"]/$row["total"],1);
					if ($maf>$min_maf)
						$num_maf++;
					if ($miss>=$max_missing)
						$num_miss++;
					if (($miss>=$max_missing) OR ($maf<=$min_maf))
						$num_removed++; 
					//$mmarray[$row["marker"]]= array("maf"=>$maf,"miss"=>$miss);
				}
				
	
				//// $firephp->log($num_maf,"number of maf");
				//// $firephp->log($num_miss,"number with too many missing");
				//// $firephp->log($num_mark,"number of markers");
				if (mysql_num_rows($res) >= 1) {
				  ?>
				  <p>Minimum MAF: <input type="text" name="mmaf" id="mmaf" size="2" value="<?php echo ($min_maf) ?>" />%
				    &nbsp;&nbsp;&nbsp;&nbsp;
				  Maximum missing data: <input type="text" name="mm" id="mm" size="2" value="<?php echo ($max_missing) ?>" />%
				    <i>
				    <br></i><b><?php echo ($num_maf) ?></b><i> markers have a minor allele frequency (MAF) larger than </i><b><?php echo ($min_maf) ?></b><i>%.
				    <br></i><b><?php echo ($num_miss) ?></b><i> markers are missing at least </i><b><?php echo ($max_missing) ?></b><i>% of measurements.
				    <br></i><b><?php echo ($num_removed) ?></b><i> of </i><b><?php echo ($num_mark) ?></b><i> distinct markers will be removed.
				    <br>Note: monomorphic markers (MAF = 0) will </i><b>not</b><i> be included in the download.
				    </i>

				    <br><input type="button" value="Refresh" onclick="javascript:mrefresh();return false;" /><br>
					<table ALIGN="left" > <tr> <td COLSPAN="3">
				    <br><input type="button" value="Download for Tassel V2" onclick="javascript:getdownload_tassel();return false;" />
					<h4> or </h4>
				    <input type="button" value="Download for Tassel V3" onclick="javascript:getdownload_tassel_v3();return false;" /> <br>
					</td> </tr> </table>	
				    <?php
				     } else {
				  ?><p style="font-weight: bold">No Data</p><?php
				    }
			
				?></div><?php
				      }else { //NO genotype experiments exist for these lines
			  ?>
			  <h3>There are no genotype experiments available for this Breeding Program/Year combination</h3>
			  <div>
			  <?php
			}
	}
	
	function type1_build_qtlminer()
	{
		$experiments_t = (isset($_GET['e']) && !empty($_GET['e'])) ? $_GET['e'] : null;
		$traits = (isset($_GET['t']) && !empty($_GET['t'])) ? $_GET['t'] : null;
		$CAPdataprogram = (isset($_GET['bp']) && !empty($_GET['bp'])) ? $_GET['bp'] : null;
		$years = (isset($_GET['yrs']) && !empty($_GET['yrs'])) ? $_GET['yrs'] : null;
		
		$dtype = "qtlminer";	
		// Get dataset-exp IDs
			$sql_exp = "SELECT DISTINCT dse.datasets_experiments_uid as id
							FROM  datasets as ds, CAPdata_programs as cd, datasets_experiments as dse
							WHERE cd.CAPdata_programs_uid = ds.CAPdata_programs_uid
								AND dse.datasets_uid = ds.datasets_uid
								AND ds.breeding_year IN ($years)
								AND ds.CAPdata_programs_uid IN ($CAPdataprogram)";
			$res = mysql_query($sql_exp) or die(mysql_error());
			
			while ($row = mysql_fetch_array($res)){
				$datasets[] = $row["id"];
			}
			
			$datasets_exp = implode(',',$datasets);		
		
		// Get genotype experiments
		$sql_exp = "SELECT DISTINCT e.experiment_uid AS exp_uid
				FROM experiments e, experiment_types et, datasets_experiments as dse
				WHERE
					e.experiment_type_uid = et.experiment_type_uid
					AND et.experiment_type_name = 'genotype'
					AND e.experiment_uid = dse.experiment_uid
					AND dse.datasets_experiments_uid IN ($datasets_exp)";
		$res = mysql_query($sql_exp) or die(mysql_error());
			
		while ($row = mysql_fetch_array($res)){
				$exp[] = $row["exp_uid"];
		}
		$experiments_g = implode(',',$exp);
		// $firephp = FirePHP::getInstance(true);
		
		
		//set up download file name in temp directory
		$dir = 'temp/';
		$filename = 'thtdownload_qtlminer'.chr(rand(65,90)).chr(rand(65,90)).chr(rand(65,90)).'.zip';
		// $firephp->log($dir.$filename);
		
        // File_Archive doesn't do a good job of creating files, so we'll create it first
		if(!file_exists($dir.$filename)){
			$h = fopen($dir.$filename, "w+");
			// $firephp->log($h);
			fclose($h);
		}
		// $firephp->log("before traits".$datasets_exp);
        // Now let File_Archive do its thing
		$zip = File_Archive::toArchive($dir.$filename, File_Archive::toFiles());
		
		$zip->newFile("traits.txt");
		// $firephp->log("before traits".$experiments_t);
		$zip->writeData($this->type1_build_traits_download($experiments_t, $traits, $datasets_exp));
			// $firephp->log("after traits".$experiments_g."  ".$dtype);
		$zip->newFile("markers.txt");
		$zip->writeData($this->type1_build_markers_download($experiments_g,$dtype));
		// $firephp->log("after markers".$experiments_g);
		$zip->newFile("pedigree.txt");
		$zip->writeData($this->type1_build_pedigree_download($experiments_g));
		// $firephp->log(" after pedigree".$experiments_g);
		$zip->newFile("inbreds.txt");
		$zip->writeData($this->type1_build_inbred_download($experiments_g));
		// $firephp->log(" after inbreds".$experiments_g);
		$zip->close();
	
		header("Location: ".$dir.$filename);
	
	}
	
	function type1_build_tassel()
	{
		$experiments_t = (isset($_GET['e']) && !empty($_GET['e'])) ? $_GET['e'] : null;
		$traits = (isset($_GET['t']) && !empty($_GET['t'])) ? $_GET['t'] : null;
		$CAPdataprogram = (isset($_GET['bp']) && !empty($_GET['bp'])) ? $_GET['bp'] : null;
		$years = (isset($_GET['yrs']) && !empty($_GET['yrs'])) ? $_GET['yrs'] : null;
		$subset = (isset($_GET['subset']) && !empty($_GET['subset'])) ? $_GET['subset'] : null;

		$dtype = "tassel";
		
				// Get dataset IDs
			$sql_exp = "SELECT DISTINCT dse.datasets_experiments_uid as id
							FROM  datasets as ds, CAPdata_programs as cd, datasets_experiments as dse
							WHERE cd.CAPdata_programs_uid = ds.CAPdata_programs_uid
								AND dse.datasets_uid = ds.datasets_uid
								AND ds.breeding_year IN ($years)
								AND ds.CAPdata_programs_uid IN ($CAPdataprogram)";
			$res = mysql_query($sql_exp) or die(mysql_error());
			
			while ($row = mysql_fetch_array($res)){
				$datasets[] = $row["id"];
			}
			
			$datasets_exp = implode(',',$datasets);		
		
		// Get genotype experiments
		$sql_exp = "SELECT DISTINCT e.experiment_uid AS exp_uid
				FROM experiments e, experiment_types et, datasets_experiments as dse
				WHERE
					e.experiment_type_uid = et.experiment_type_uid
					AND et.experiment_type_name = 'genotype'
					AND e.experiment_uid = dse.experiment_uid
					AND dse.datasets_experiments_uid IN ($datasets_exp)";
		$res = mysql_query($sql_exp) or die(mysql_error());
			
		while ($row = mysql_fetch_array($res)){
				$exp[] = $row["exp_uid"];
		}
		$experiments_g = implode(',',$exp);
		//$firephp = FirePHP::getInstance(true);

		//$firephp->error("Curent location: ". getcwd());
		$dir = 'temp/';
		$filename = 'THTdownload_tassel_'.chr(rand(65,80)).chr(rand(65,80)).chr(rand(64,80)).'.zip';
		
        // File_Archive doesn't do a good job of creating files, so we'll create it first
		if(!file_exists($dir.$filename)){
			$h = fopen($dir.$filename, "w+");
			fclose($h);
		}
		
        // Now let File_Archive do its thing
		$zip = File_Archive::toArchive($dir.$filename, File_Archive::toFiles());
		$zip->newFile("traits.txt");
		// $firephp->log("into traits ".$experiments_t." N".$traits." N".$datasets_exp);
		$zip->writeData($this->type1_build_tassel_traits_download($experiments_t, $traits, $datasets_exp, $subset));
		// $firephp->log("after traits 1 ".$experiments_t);

		$zip->newFile("snpfile.txt");
		// $firephp->log("before first marker file".$experiments_g);
		$zip->writeData($this->type1_build_markers_download($experiments_g, $dtype));
		// $firephp->log("after first marker file".$experiments_g);
		$zip->newFile("annotated_alignment.txt");
		$zip->writeData($this->type1_build_annotated_align($experiments_g));
		// $firephp->log("after alignment marker file".$experiments_g);

		$zip->close();
		
		header("Location: ".$dir.$filename);
	}

	function type1_build_tassel_v3()
	{
		$experiments_t = (isset($_GET['e']) && !empty($_GET['e'])) ? $_GET['e'] : null;
		$traits = (isset($_GET['t']) && !empty($_GET['t'])) ? $_GET['t'] : null;
		$CAPdataprogram = (isset($_GET['bp']) && !empty($_GET['bp'])) ? $_GET['bp'] : null;
		$years = (isset($_GET['yrs']) && !empty($_GET['yrs'])) ? $_GET['yrs'] : null;
		$subset = (isset($_GET['subset']) && !empty($_GET['subset'])) ? $_GET['subset'] : null;
		
		$dtype = "tassel";
		
				// Get dataset IDs
			$sql_exp = "SELECT DISTINCT dse.datasets_experiments_uid as id
							FROM  datasets as ds, CAPdata_programs as cd, datasets_experiments as dse
							WHERE cd.CAPdata_programs_uid = ds.CAPdata_programs_uid
								AND dse.datasets_uid = ds.datasets_uid
								AND ds.breeding_year IN ($years)
								AND ds.CAPdata_programs_uid IN ($CAPdataprogram)";
			$res = mysql_query($sql_exp) or die(mysql_error());
			
			while ($row = mysql_fetch_array($res)){
				$datasets[] = $row["id"];
			}
			
			$datasets_exp = implode(',',$datasets);		
		
		// Get genotype experiments
		$sql_exp = "SELECT DISTINCT e.experiment_uid AS exp_uid
				FROM experiments e, experiment_types et, datasets_experiments as dse
				WHERE
					e.experiment_type_uid = et.experiment_type_uid
					AND et.experiment_type_name = 'genotype'
					AND e.experiment_uid = dse.experiment_uid
					AND dse.datasets_experiments_uid IN ($datasets_exp)";
		$res = mysql_query($sql_exp) or die(mysql_error());
			
		while ($row = mysql_fetch_array($res)){
				$exp[] = $row["exp_uid"];
		}
		$experiments_g = implode(',',$exp);
		//$firephp = FirePHP::getInstance(true);

		//$firephp->error("Curent location: ". getcwd());
		$dir = 'temp/';
		$filename = 'THTdownload_tasselV3_'.chr(rand(65,80)).chr(rand(65,80)).chr(rand(64,80)).'.zip';
		
        // File_Archive doesn't do a good job of creating files, so we'll create it first
		if(!file_exists($dir.$filename)){
			$h = fopen($dir.$filename, "w+");
			fclose($h);
		}
		
        // Now let File_Archive do its thing
		$zip = File_Archive::toArchive($dir.$filename, File_Archive::toFiles());
		$zip->newFile("traits.txt");
		// $firephp->log("into traits ".$experiments_t." N".$traits." N".$datasets_exp);
		$zip->writeData($this->type1_build_tassel_traits_download($experiments_t, $traits, $datasets_exp, $subset));
		// $firephp->log("after traits 1 ".$experiments_t);

		$zip->newFile("snpfile.txt");
		// $firephp->log("before first marker file".$experiments_g);
		$zip->writeData($this->type1_build_markers_download($experiments_g, $dtype));
		// $firephp->log("after first marker file".$experiments_g);
		$zip->newFile("geneticMap.txt");
		$zip->writeData($this->type1_build_geneticMap($experiments_g));
		// $firephp->log("after alignment marker file".$experiments_g);

		$zip->close();
		
		header("Location: ".$dir.$filename);
	}


	
	private function type1_build_traits_download($experiments, $traits, $datasets)
	{
		
		$output = 'Experiment' . $this->delimiter . 'Inbred';
		$traits = explode(',', $traits);
		
		
		$select = "SELECT experiments.trial_code, line_records.line_record_name";
		$from = " FROM tht_base
				JOIN experiments ON experiments.experiment_uid = tht_base.experiment_uid
				JOIN line_records ON line_records.line_record_uid = tht_base.line_record_uid ";
		foreach ($traits as $trait) {
			$from .= " JOIN (
					SELECT p.phenotypes_name, pd.value, pd.tht_base_uid, pmd.number_replicates, pmd.experiment_uid
					FROM phenotypes AS p, phenotype_data AS pd, phenotype_mean_data AS pmd               
					WHERE pd.phenotype_uid = p.phenotype_uid
					    AND pmd.phenotype_uid = p.phenotype_uid
					    AND p.phenotype_uid = ($trait)) AS t$trait
						
					    ON t$trait.tht_base_uid = tht_base.tht_base_uid AND t$trait.experiment_uid = tht_base.experiment_uid";
			$select .= ", t$trait.phenotypes_name as name$trait, t$trait.value as value$trait, t$trait.number_replicates as nreps$trait";
			}
		$where = " WHERE tht_base.experiment_uid IN ($experiments)
					AND tht_base.check_line = 'no'
					AND tht_base.datasets_experiments_uid in ($datasets)";
		
		$res = mysql_query($select.$from.$where) or die(mysql_error());

		$namevaluekeys = null;
		$valuekeys = array();
		while($row = mysql_fetch_assoc($res)) {
			if ($namevaluekeys == null)
			{
				$namevaluekeys = array_keys($row);
				unset($namevaluekeys[array_search('trial_code', $namevaluekeys)]);
				//unset($namevaluekeys[array_search('number_replications', $namevaluekeys)]);
				unset($namevaluekeys[array_search('line_record_name', $namevaluekeys)]);
				
				foreach($namevaluekeys as $namevaluekey) {
					if (stripos($namevaluekey, 'name') !== FALSE) {
						$output .= $this->delimiter . "{$row[$namevaluekey]}" . $this->delimiter . "N";
					} else {
						array_push($valuekeys, $namevaluekey);
					}
				}
				$output .= "\n";
			}
			$output .= "{$row['trial_code']}" . $this->delimiter . "{$row['line_record_name']}";
			foreach($valuekeys as $valuekey) {
				if (is_null($row[$valuekey]))
					$row[$valuekey] = 'N/A';
				$output .= $this->delimiter . "{$row[$valuekey]}" ;
			}
			$output .= "\n";
		}
		
		return $output;
	}

/* Build trait download file for Tassel program interface */
    private function type1_build_tassel_traits_download($experiments, $traits, $datasets, $subset)
	{
     	//$firephp = FirePHP::getInstance(true);
		$delimiter = "\t";
		$output = '';
		$outputheader1 = '';
		$outputheader2 = '';
		$outputheader3 = '';
      
      //count number of traits and number of experiments
	$ntraits=substr_count($traits, ',')+1;
      $nexp=substr_count($experiments, ',')+1;
      
      //$traits = explode(',', $traits);
      //$experiments = explode(',', $experiments);
      
      // figure out which traits are at which location
      $sql = "SELECT DISTINCT e.trial_code, e.experiment_uid, p.phenotypes_name,p.phenotype_uid
               FROM experiments as e, tht_base as tb, phenotype_data as pd, phenotypes as p
               WHERE 
                  e.experiment_uid = tb.experiment_uid
                  AND tb.experiment_uid IN ($experiments)
                  AND pd.tht_base_uid = tb.tht_base_uid
                  AND p.phenotype_uid = pd.phenotype_uid
                  AND pd.phenotype_uid IN ($traits)  
               ORDER BY p.phenotype_uid,e.experiment_uid";
      $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
      $ncols = mysql_num_rows($res);
      while($row = mysql_fetch_array($res)) {
         $outputheader2 .= str_replace(" ","_",$row['phenotypes_name']).$delimiter;
         $outputheader3 .= $row['trial_code'].$delimiter;
         $keys[] = $row['phenotype_uid'].$row['experiment_uid'];
      }
		//$firephp->log("trait_location information ".$outputheader2."  ".$outputheader3);
		// $firephp->table('keys label ', $keys); 

		// dem 5jan11: If $subset="yes", use $_SESSION['selected_lines'].
		$intheselines = "";
		if ($subset == "yes" && count($_SESSION['selected_lines']) > 0) {
		  $selectedlines = implode(",", $_SESSION['selected_lines']);
		  $intheselines = "AND line_records.line_record_uid IN ($selectedlines)";
		}
      // get a list of all line names in the selected datasets and experiments,
	  // INCLUDING the check lines // AND tht_base.check_line IN ('no')
      $sql = "SELECT DISTINCT line_records.line_record_name, line_records.line_record_uid
               FROM line_records, tht_base
               WHERE tht_base.experiment_uid IN ($experiments)
                 $intheselines
                 AND line_records.line_record_uid=tht_base.line_record_uid
                 AND ((tht_base.datasets_experiments_uid in ($datasets)AND tht_base.check_line='no') 
                  	OR (tht_base.check_line='yes'))";
      $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
      while($row = mysql_fetch_array($res)) {
         $lines[] = $row['line_record_name'];
         $line_uid[] = $row['line_record_uid'];
      }
      $nlines = count($lines);

	  if ($nexp ===1){
			$nheaderlines = 1;
		} else {
			$nheaderlines = 2;
		}
      $outputheader1 = "$nlines".$delimiter."$ncols".$delimiter.$nheaderlines;
      //if (DEBUG>1) echo $outputheader1."\n".$outputheader2."\n".$outputheader3."\n";
      // $firephp->log("number traits and lines ".$outputheader1);
	  if ($nexp ===1){
			$output = $outputheader1."\n".$outputheader2."\n";
		} else {
			$output = $outputheader1."\n".$outputheader2."\n".$outputheader3."\n";
		}
	  
	  
	  // loop through all the lines in the file
		for ($i=0;$i<$nlines;$i++) {
            $outline = $lines[$i].$delimiter;
			// get selected traits for this line in the selected experiments, change for multiple check lines
           /* $sql = "SELECT pd.phenotype_uid, pd.value, tb.experiment_uid
                     FROM tht_base as tb, phenotype_data as pd
                     WHERE 
                        tb.line_record_uid =  $line_uid[$i]
                        AND tb.experiment_uid IN ($experiments)
                        AND pd.tht_base_uid = tb.tht_base_uid
                       AND pd.phenotype_uid IN ($traits)  
                     ORDER BY pd.phenotype_uid,tb.experiment_uid";*/
// dem 8oct10: Don't round the data.
//			$sql = "SELECT avg(cast(pd.value AS DECIMAL(9,1))) as value,pd.phenotype_uid,tb.experiment_uid 
			$sql = "SELECT pd.value as value,pd.phenotype_uid,tb.experiment_uid 
					FROM tht_base as tb, phenotype_data as pd
					WHERE tb.experiment_uid IN ($experiments)
						AND tb.line_record_uid  = $line_uid[$i]
						AND pd.tht_base_uid = tb.tht_base_uid
						AND pd.phenotype_uid IN ($traits) 
					GROUP BY tb.tht_base_uid, pd.phenotype_uid";
			
            $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
           // // $firephp->log("sql ".$i." ".$sql);
			$outarray = array_fill(0,$ncols,-999);
			//// $firephp->table('outarray label values', $outarray); 
			//$outarray = array_fill_keys( $keys  , -999);
			$outarray = array_combine($keys  , $outarray);
			//// $firephp->table('outarray label ', $outarray); 
            while ($row = mysql_fetch_array($res)) {
               $keyval = $row['phenotype_uid'].$row['experiment_uid'];
			   // $firephp->log("keyvals ".$keyval." ".$row['value']);
               $outarray[$keyval]= $row['value'];
            }
            $outline .= implode($delimiter,$outarray)."\n";
			//// $firephp->log("outputline ".$i." ".$outline);
            $output .= $outline;

      }

		return $output;
	}
	
	private function type1_build_markers_download($experiments,$dtype)
	{
		// $firephp = FirePHP::getInstance(true);
		$outputheader = '';
		$output = '';
		$doneheader = false;
		$delimiter ="\t";
      
		 if (isset($_GET['mm']) && !empty($_GET['mm']) && is_numeric($_GET['mm']))
            $max_missing = $_GET['mm'];
		if ($max_missing>100)
			$max_missing = 100;
		elseif ($max_missing<0)
			$max_missing = 0;
			// $firephp->log("in sort markers2");
        $min_maf = 0;//IN PERCENT
        if (isset($_GET['mmaf']) && !empty($_GET['mmaf']) && is_numeric($_GET['mmaf']))
            $min_maf = $_GET['mmaf'];
		if ($min_maf>100)
			$min_maf = 100;
		elseif ($min_maf<0)
			$min_maf = 0;
			// $firephp->log("in sort markers".$max_missing."  ".$min_maf);

	 //get lines and filter to get a list of markers which meet the criteria selected by the user
          $sql_mstat = "SELECT af.marker_uid as marker, m.marker_name as name, SUM(af.aa_cnt) as sumaa, SUM(af.missing)as summis, SUM(af.bb_cnt) as sumbb,
					SUM(af.total) as total, SUM(af.ab_cnt) AS sumab
					FROM allele_frequencies AS af, markers as m
					WHERE m.marker_uid = af.marker_uid
						AND af.experiment_uid in ($experiments)
					group by af.marker_uid"; 

			$res = mysql_query($sql_mstat) or die(mysql_error());
			$num_maf = $num_miss = 0;
			while ($row = mysql_fetch_array($res)){
			  $maf = round(100*min((2*$row["sumaa"]+$row["sumab"])/(2*$row["total"]),($row["sumab"]+2*$row["sumbb"])/(2*$row["total"])),1);
			  $miss = round(100*$row["summis"]/$row["total"],1);
			  //if (($maf>=$min_maf)AND ($miss<=$max_missing)) {   Note: $maf must be > , not >= .
			  if (($maf > $min_maf)AND ($miss<=$max_missing)) {
			    $marker_names[] = $row["name"];
			    $outputheader .= $row["name"].$delimiter;
			    $marker_uid[] = $row["marker"];
			  }
			}
			$nelem = count($marker_names);
			$marker_uid = implode(",",$marker_uid);
        
		if ($dtype=='qtlminer') {
		  $lookup = array(
			  'AA' => '1',
			  'BB' => '-1',
			  '--' => 'NA',
			  'AB' => '0'
		  );
	   } else {
		  $lookup = array(
			  'AA' => '1:1',
			  'BB' => '2:2',
			  '--' => '?',
			  'AB' => '1:2'
		  );
		}
		
			// make an empty line with the markers as array keys, set default value
			//  to the default missing value for either qtlminer or tassel
			// places where the lines may have different values
			
		  if ($dtype =='qtlminer')  {
				$empty = array_combine($marker_names,array_fill(0,$nelem,'NA'));
		  } else {
				$empty = array_combine($marker_names,array_fill(0,$nelem,'?'));
		  }
			
			
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
				AND tb.experiment_uid IN ($experiments)
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
		//NOTE: there is a problem with the last line logic here. Must fix.
		  //save data from the last line
		  $output .= "$last_line\t";
		  $outarray = implode($delimiter,$outarray);
		  $output .= $outarray."\n";
		  $num_lines++;
		  
		if ($dtype =='qtlminer')  {
		  return $outputheader."\n".$output;
		} else {
		  return $num_lines.$delimiter.$nelem.":2\n".$outputheader."\n".$output;
	   }
	}
	
	private function type1_build_annotated_align($experiments)
	{
		$delimiter ="\t";
		// $firephp = FirePHP::getInstance(true);
		$output = '';
		$doneheader = false;
		        if (isset($_GET['mm']) && !empty($_GET['mm']) && is_numeric($_GET['mm']))
            $max_missing = $_GET['mm'];
		if ($max_missing>100)
			$max_missing = 100;
		elseif ($max_missing<0)
			$max_missing = 0;
			// $firephp->log("in sort markers2");
        $min_maf = 0;//IN PERCENT
        if (isset($_GET['mmaf']) && !empty($_GET['mmaf']) && is_numeric($_GET['mmaf']))
            $min_maf = $_GET['mmaf'];
		if ($min_maf>100)
			$min_maf = 100;
		elseif ($min_maf<0)
			$min_maf = 0;
			// $firephp->log("in sort markers".$max_missing."  ".$min_maf);

	 //get lines and filter to get a list of markers which meet the criteria selected by the user
          $sql_mstat = "SELECT af.marker_uid as marker, m.marker_name as name, SUM(af.aa_cnt) as sumaa, SUM(af.missing)as summis, SUM(af.bb_cnt) as sumbb,
					SUM(af.total) as total, SUM(af.ab_cnt) AS sumab
					FROM allele_frequencies AS af, markers as m
					WHERE m.marker_uid = af.marker_uid
						AND af.experiment_uid in ($experiments)
					group by af.marker_uid"; 

			$res = mysql_query($sql_mstat) or die(mysql_error());
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
			// $firephp->log($marker_uid);
   		
		  $lookup = array(
			  'AA' => 'A','BB' => 'B','--' => '-','AB' => 'C'
		  );
		  $lookup_chrom = array(
			  '1H' => '1','2H' => '2','3H' => '3','4H' => '4','5H' => '5',
			  '6H' => '6','7H' => '7','UNK'  => '10'
		  );
		
		  // finish writing file header using a list of line names
		  $sql = "SELECT DISTINCT lr.line_record_name AS line_name
					 FROM line_records AS lr, tht_base AS tb
					 WHERE
						  lr.line_record_uid = tb.line_record_uid
						  AND tb.experiment_uid IN ($experiments)
						  ORDER BY line_name";
		  $res = mysql_query($sql) or die(mysql_error());
		  while ($row = mysql_fetch_array($res)) {
				$line_names[] = $row['line_name'];
			  }
			  
			// make an empty marker with the lines as array keys 
			$nelem = count($marker_uid);
			$n_lines = count($line_names);
			$empty = array_combine($line_names,array_fill(0,$n_lines,'-'));
			$nemp = count($empty);
			$marker_uid = implode(",",$marker_uid);
			$line_str = implode($delimiter,$line_names);
			// $firephp = log($nelem." ".$n_lines);
			
			// write output file header
			$outputheader = "<Annotated>\n<Transposed>".$delimiter."Yes\n";
			$outputheader .= "<Taxa_Number>".$delimiter.$n_lines."\n";
			$outputheader .= "<Locus_Number>".$delimiter.$nelem."\n";
			$outputheader .= "<Poly_Type>".$delimiter."Catagorical\n";
			$outputheader .= "<Delimited_Values>".$delimiter."No\n";
			$outputheader .= "<Taxon_Name>".$delimiter.$line_str."\n";
			$outputheader .= "<Chromosome_Number>".$delimiter."<Genetic_Position>".$delimiter."<Locus_Name>".$delimiter."<Value>\n";
		// $firephp = log($outputheader);

			// get marker map data, line and marker names; use latest consensus map
			// as the map default
		$mapset = 1;	
         $sql = "SELECT mim.chromosome, mim.start_position, lr.line_record_name as lname, m.marker_name AS mname,
                    CONCAT(a.allele_1,a.allele_2) AS value
			FROM
            markers as m,
			markers_in_maps as mim,
			map,
			mapset,
            line_records as lr,
            alleles as a,
            tht_base as tb,
            genotyping_data as gd
			WHERE
            a.genotyping_data_uid = gd.genotyping_data_uid
				AND mim.marker_uid = m.marker_uid
				AND m.marker_uid = gd.marker_uid
				AND gd.marker_uid IN ($marker_uid)
				AND mim.map_uid = map.map_uid
				AND map.mapset_uid = mapset.mapset_uid
				AND mapset.mapset_uid = '$mapset'
				AND tb.line_record_uid = lr.line_record_uid
				AND gd.tht_base_uid = tb.tht_base_uid
				AND tb.experiment_uid IN ($experiments)
		  ORDER BY mim.chromosome,mim.start_position, m.marker_uid, lname";


		$last_marker = "somemarkername";
		$res = mysql_query($sql) or die(mysql_error());
		
		$outarray = $empty;
		$cnt = $num_markers = 0;
		while ($row = mysql_fetch_array($res)){
				//first time through loop
				if ($cnt==0) {
					$last_marker = $row['mname'];
					$pos = $row['start_position'];
					$chrom = $lookup_chrom[$row['chromosome']];
				}
				
			if ($last_marker != $row['mname']){  
					// Close out the last marker
					$output .= "$chrom\t$pos\t$last_marker\t";
					$outarray = implode("",$outarray);
					$output .= $outarray."\n";
					//reset output arrays for the next line
					$outarray = $empty;
					$lname = $row['lname'];	//start new line			
					$outarray[$lname] = $lookup[$row['value']];
					$last_marker = $row['mname'];
					$pos = $row['start_position'];
					$chrom = $lookup_chrom[$row['chromosome']];
					$num_markers++;
			} else {
					 $lname = $row['lname'];				
					 $outarray[$lname] = $lookup[$row['value']];
			}
			$cnt++;
		}
		
		  //save data from the last line
		  $output .= "$chrom\t$pos\t$last_marker\t";
		  $outarray = implode("",$outarray);
		  $output .= $outarray."\n";
		  $num_markers++;
		  

		  return $outputheader.$output;

	}

	private function type1_build_geneticMap($experiments)
	{
		$delimiter ="\t";
		// $firephp = FirePHP::getInstance(true);
		$output = '';
		$doneheader = false;
		if (isset($_GET['mm']) && !empty($_GET['mm']) && is_numeric($_GET['mm']))
            $max_missing = $_GET['mm'];
		if ($max_missing>100)
			$max_missing = 100;
		elseif ($max_missing<0)
			$max_missing = 0;
			// $firephp->log("in sort markers2");
        $min_maf = 0;//IN PERCENT
        if (isset($_GET['mmaf']) && !empty($_GET['mmaf']) && is_numeric($_GET['mmaf']))
            $min_maf = $_GET['mmaf'];
		if ($min_maf>100)
			$min_maf = 100;
		elseif ($min_maf<0)
			$min_maf = 0;
		// $firephp->log("in sort markers".$max_missing."  ".$min_maf);

        //get lines and filter to get a list of markers which meet the criteria selected by the user
        $sql_mstat = "SELECT af.marker_uid as marker, m.marker_name as name, SUM(af.aa_cnt) as sumaa, SUM(af.missing)as summis, SUM(af.bb_cnt) as sumbb,
					SUM(af.total) as total, SUM(af.ab_cnt) AS sumab
					FROM allele_frequencies AS af, markers as m
					WHERE m.marker_uid = af.marker_uid
						AND af.experiment_uid in ($experiments)
					group by af.marker_uid"; 

		$res = mysql_query($sql_mstat) or die(mysql_error());
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
		// $firephp->log($marker_uid);
   		
	    $lookup_chrom = array(
		  '1H' => '1','2H' => '2','3H' => '3','4H' => '4','5H' => '5',
		  '6H' => '6','7H' => '7','UNK'  => '10'
        );
		
        // finish writing file header using a list of line names
        $sql = "SELECT DISTINCT lr.line_record_name AS line_name
			 FROM line_records AS lr, tht_base AS tb
			 WHERE
				  lr.line_record_uid = tb.line_record_uid
				  AND tb.experiment_uid IN ($experiments)
				  ORDER BY line_name";
        $res = mysql_query($sql) or die(mysql_error());
        while ($row = mysql_fetch_array($res)) {
            $line_names[] = $row['line_name'];
        }
			  
        // make an empty marker with the lines as array keys 
        $nelem = count($marker_uid);
        $n_lines = count($line_names);
		$empty = array_combine($line_names,array_fill(0,$n_lines,'-'));
		$nemp = count($empty);
		$marker_uid = implode(",",$marker_uid);
		$line_str = implode($delimiter,$line_names);
		// $firephp = log($nelem." ".$n_lines);
			
		// write output file header
		$outputheader = "<Map>\n";
	    // $firephp = log($outputheader);

		// get marker map data, line and marker names; use latest consensus map
		// as the map default
        $mapset = 1;	

        $sql = "SELECT mim.chromosome, mim.start_position, lr.line_record_name as lname, m.marker_name AS mname                    
			FROM
            markers as m,
			markers_in_maps as mim,
			map,
			mapset,
            line_records as lr,
            alleles as a,
            tht_base as tb,
            genotyping_data as gd
			WHERE
            a.genotyping_data_uid = gd.genotyping_data_uid
				AND mim.marker_uid = m.marker_uid
				AND m.marker_uid = gd.marker_uid
				AND gd.marker_uid IN ($marker_uid)
				AND mim.map_uid = map.map_uid
				AND map.mapset_uid = mapset.mapset_uid
				AND mapset.mapset_uid = '$mapset'
				AND tb.line_record_uid = lr.line_record_uid
				AND gd.tht_base_uid = tb.tht_base_uid
				AND tb.experiment_uid IN ($experiments)
		  ORDER BY mim.chromosome,mim.start_position, m.marker_uid, lname";

        $last_marker = "somemarkername";
		$res = mysql_query($sql) or die(mysql_error());
		
		$cnt = $num_markers = 0;
		while ($row = mysql_fetch_array($res)){
				//first time through loop
            if ($cnt==0) {
                $last_marker = $row['mname'];
				$pos = $row['start_position'];
				$chrom = $lookup_chrom[$row['chromosome']];
			}
				
			if ($last_marker != $row['mname']){  
				// Close out the last marker
				$output .= "$last_marker\t$chrom\t$pos\t";
				$output .= "\n";
				$lname = $row['lname'];	//start new line			
				$last_marker = $row['mname'];
				$pos = $row['start_position'];
				$chrom = $lookup_chrom[$row['chromosome']];
				$num_markers++;
    		} else {
				 $lname = $row['lname'];				
			}
			$cnt++;
		}
		
	  //save data from the last line
	  $output .= "$last_marker\t$chrom\t$pos\t";
	  $output .= "\n";
	  $num_markers++;

	  return $outputheader.$output;
    }

	
	private function type1_build_pedigree_download($experiments)
	{
		$delimiter ="\t";
		// output file header for QTL Miner Pedigree files
		$outputheader = "Inbred" . $delimiter . "Parent1" . $delimiter . "Parent2" . $delimiter . "Contrib1" . $delimiter . "Contrib2";
		//echo "Inbred  Parent1   Parent2 Contrib1  Contrib2";
        // get all line records in the incoming experiments
      //// $firephp = FirePHP::getInstance(true);
		//// $firephp->log($outputheader);  
		$sql = "SELECT DISTINCT datasets_uid
					FROM datasets_experiments
					WHERE experiment_uid IN ($experiments)";

		$res=	mysql_query($sql) or die(mysql_error());
        		
		//loop through the datasets
		$output = '';
		while($row=mysql_fetch_array($res))
		{
			$datasets_uid[]=$row['datasets_uid'];
			//// $firephp->log($row['datasets_uid']); 
		}
		foreach($datasets_uid as $ds){
			
		  $sql_ds = "SELECT datasets_pedigree_data FROM datasets WHERE datasets_uid = $ds";
		  //// $firephp->log($sql_ds);
		  $res=	mysql_query($sql_ds) or die(mysql_error());
		  $resdata=mysql_fetch_array($res);
		   $outdata=$resdata['datasets_pedigree_data'];
		   //// $firephp->log($outdata);
		  $output .= $outdata;
		}

		return $outputheader."\n".$output;
	}
	
	private function type1_build_inbred_download($experiments)
	{
		$newline ="\n";
		// output file header for QTL Miner Pedigree files
		$output = "Inbred\n";
		
        // get all line records in the incoming experiments
      //// $firephp = FirePHP::getInstance(true);
		//// $firephp->log($outputheader);  
		$sql = "SELECT DISTINCT line_record_name
					FROM tht_base,line_records
					WHERE line_records.line_record_uid=tht_base.line_record_uid
					AND experiment_uid IN ($experiments)";

		$res=	mysql_query($sql) or die(mysql_error());
        		
		//loop through the lines

		while($row=mysql_fetch_array($res))
		{
			$output .=$row['line_record_name']."\n";
			//// $firephp->log($row['datasets_uid']); 
		}

		return $output;
	}	
	
}// end class
?>
