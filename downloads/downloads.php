<?php
/**
 * Download Gateway
 * 
 * PHP version 5.3
 * Prototype version 1.5.0
 * 
 * @category PHP
 * @package  T3
 * @author   Clay Birkett <cbirkett@gmail.com>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @version  GIT: 2
 * @link     http://triticeaetoolbox.org/wheat/downloads/downloads.php
 * 
 */
// |                                                                      |
// | The purpose of this script is to provide the user with an interface  |
// | for downloading certain kinds of files from THT.                     |
// +----------------------------------------------------------------------+
// | Authors:  Gavin Monroe <gemonroe@iastate.edu>  						|
// | Updated: December 2008 by Julie A Dickerson, julied@iastate.edu	  |
// +----------------------------------------------------------------------+
// +----------------------------------------------------------------------+
// | Change log								  |
// | 2/8/11:  DEM - Include markers with MAF = 0 too if user wishes.      |
// | 1/5/01:  JLee - Add support to generate datafile for Tassel V3       |  
// |                                                                      |
// | 2/28/09: removed table summarizing all allelles to avoid timeout	  |
// |          problems when getting SNP data across multiple programs
// | 5/20/09: added in tassel support functionality and commented out
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
require_once 'config.php';
include $config['root_dir'].'includes/bootstrap.inc';
set_include_path(GET_INCLUDE_PATH . PATH_SEPARATOR . '../pear/');
date_default_timezone_set('America/Los_Angeles');

require_once $config['root_dir'].'includes/MIME/Type.php';
require_once $config['root_dir'].'includes/File_Archive/Archive.php';

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
		?>
		<script type="text/javascript" src="/cbirkett/t3/wheatplus/downloads/downloads.js"></script>
		<?php
		switch($function)
		{
			case 'type1':
				$this->type1();
				break;
			case 'type1preselect':
				$this->type1_preselect();
				break;
			case 'type1experiments':
				$this->type1_experiments();
				break;
			case 'step1dataprog':
				$this->step1_dataprog();
				break;
			case 'enterlines':
				$this->enter_lines();
				break;
			case 'step1lines':
				$this->step1_lines();
				break;
			case 'step2lines':
				$this->step2_lines();
				break;
			case 'step1breedprog':
				$this->step1_breedprog();
				break;
			case 'step1phenotype':
				$this->step1_phenotype();
				break;
			case 'step2phenotype':
				$this->step2_phenotype();
				break;
			case 'step3phenotype':
				$this->step3_phenotype();
				break;
			case 'step4phenotype':
				$this->step4_phenotype();
				break;
			case 'step5phenotype':
			    $this->step5_phenotype();
			    break;
			case 'updatelines_tassel':
			    $this->updatelines_tassel();
			    break;
			case 'step1yearprog':
			    $this->step1_yearprog();
			    break;
			case 'type1traits':
				$this->type1_traits();
				break;
			case 'type1markers':
				$this->type1_markers();
				break;
			case 'type1markersselect':
				$this->type1_markers_select();
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
			case 'step2lines':
				echo $this->step2_lines();
				break;
			case 'searchLines':
				echo $this->step1_search_lines();
				break;
			case 'download_session':
				echo $this->type1_session();
				break;
			case 'download_session_v2':
			    echo $this->type1_session(V2);
			    break;
			case 'download_session_v3':
			    echo $this->type1_session(v3);
			    break;
			default:
				$this->type1_select();
				break;
				
		}	
	}

	// Select to use existing data or create a new selection
	private function type1_select()
	{
		global $config;
                include($config['root_dir'].'theme/normal_header.php');
		$phenotype = "";
                $lines = "";
		$markers = "";
		$saved_session = "";
		$this->type1_checksession();
		echo "</div>";
		include($config['root_dir'].'theme/footer.php');
	}	
	private function type1_preselect()
	{
		global $config;
                include($config['root_dir'].'theme/normal_header.php');
		$this->type1_markers();
		include($config['root_dir'].'theme/footer.php');
	}
	// The wrapper action for the type1 download. Handles outputting the header
	// and footer and calls the first real action of the type1 download.
	private function type1()
	{
		global $config;
		#include($config['root_dir'].'theme/normal_header.php');

		#echo "<h2>Tassel Download</h2>";
		#echo "<p><em>Select multiple options by holding down the Ctrl key while clicking.
		#</em></p>";
		unset($_SESSION['selected_lines']);
		unset($_SESSION['phenotype']);
		unset($_SESSION['clicked_buttons']);
		
		$this->type1_breeding_programs_year();
		$footer_div = 1;
        #	include($config['root_dir'].'theme/footer.php');
	}
	
	//
	// The first real action of the type1 download. Handles outputting the
	// Breeding Program and Year selection boxes as well as outputting the
	// javascript code required by itself and the other type1 actions.
	
	private function type1_checksession()
    {
            $phenotype = "";
            $lines = "";
            $markers = "";
            $saved_session = "";
		    $message1 = $message2 = "";

            if (isset($_SESSION['phenotype'])) {
                    $tmp = count($_SESSION['phenotype']);
                    if ($tmp==1) {
                        $saved_session = "$tmp phenotype ";
                    } else {
                        $saved_session = "$tmp phenotypes ";
                    }
                    $message2 = "download phenotype and genotype data";
                    $phenotype = $_SESSION['phenotype'];
            } else {
				$message1 = "0 phenotypes";
				$message2 = " download genotype data";
			}
            if (isset($_SESSION['selected_lines'])) {
                    $countLines = count($_SESSION['selected_lines']);
                    if ($saved_session == "") {
                        $saved_session = "$countLines lines";
                    } else {
                        $saved_session = $saved_session . ", $countLines lines";
                    }
                    $lines = $_SESSION['selected_lines'];
            }
            if (isset($_SESSION['clicked_buttons'])) {
                    $tmp = count($_SESSION['clicked_buttons']);
                    $saved_session = $saved_session . ", $tmp markers";
                    $markers = $_SESSION['clicked_buttons'];
            } else {
			    if ($message2 == "") {
			      $message1 = "0 markers ";
			      $message2 = "for all markers.";
			    } else {
			  	  $message1 = $message1 . ", 0 markers ";
			  	  $message2 = $message2 . " for all markers";
				}
			}	
            ?>
                <h2>Tassel Download</h2>
                <div id="step1">
                <p>
                <em>Select multiple options by holding down the Ctrl key while clicking.</em> 
            <?php 
                if ($saved_session != "") {
            ?>
                <button type="button" value="Clear current selection" onclick="javascript: use_normal()">Clear current selection</button>
            <?php
                }
            ?>        
                </p>
                <?php 
                if (isset($_SESSION['selected_lines'])) {
                    $this->type1_lines_trial_trait();
                } else {
                    $this->type1_breeding_programs_year();
                }
                ?>
                </div>
                <?php
        }

    private function type1_session($version)
	{
	    $experiments_t = (isset($_GET['e']) && !empty($_GET['e'])) ? $_GET['e'] : null;
		if (isset($_SESSION['selected_lines'])) {
			$selectedcount = count($_SESSION['selected_lines']);
			$lines = implode(",", $_SESSION['selected_lines']);
		} else {
			$lines = "";
		}
		if (isset($_SESSION['clicked_buttons'])) {
		    $selectcount = $_SESSION['clicked_buttons'];
		    $markers = implode(",", $_SESSION['clicked_buttons']);
		} else {
		    $markers = "";
		}
		if (isset($_SESSION['phenotype'])) {
		    $phenotype = $_SESSION['phenotype'];
		} else {
		    $phenotype = "";
		}
		$dir = '/tmp/tht/';
                $filename = 'THTdownload_tassel_'.chr(rand(65,80)).chr(rand(65,80)).chr(rand(64,80)).'.zip';

	        // File_Archive doesn't do a good job of creating files, so we'll create it first
                if(!file_exists($dir.$filename)){
                        $h = fopen($dir.$filename, "w+");
                        fclose($h);
                }
        $zip = File_Archive::toArchive($dir.$filename, File_Archive::toFiles());
        $subset = "yes";
        
        if (($version == "V2") || ($version == "V3")) {
		  $zip->newFile("traits.txt");
		  if ($experiments_t == "") {
		    $zip->writeData($this->type3_build_tassel_traits_download($phenotype,$subset));
		  } else {
		    $zip->writeData($this->type2_build_tassel_traits_download($experiments_t,$phenotype,$subset));
		  }
        }
		$zip->newFile("snpfile.txt");
        $zip->writeData($this->type2_build_markers_download($lines,$markers,$dtype));
        $zip->close();
        header("Location: ".$dir.$filename);
	}
	
    private function type1_breeding_programs_year()
	{
		?>
		<style type="text/css">
			th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
			table {background: none; border-collapse: collapse}
			td {border: 1px solid #eee !important;}
			h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
		</style>
		<div id="step1" style="float: left; margin-bottom: 1.5em;">
		<p>1. 
		<select name="select1" onchange="javascript: update_select1(this.options)">
		  <option value="BreedingProgram">Breeding Program and Year</option>
		  <!--  <option value="Years">Year and Trial</option> -->
		<?php 
		  if (isset($_SESSION['selected_lines'])) {
		  ?>
		  <option value="Lines">Lines</option>
		<?php 
		  }
		?>
		  <option value="Phenotypes">Traits</option>
		</select></p>
		
			<div id="step11">
			<table>
				<tr>
					<th>Breeding Program</th>
					<th>Year</th>
				</tr>
				<tr>
					<td>
						<select name="breeding_programs" multiple="multiple" style="height: 12em;" onchange="javascript: update_breeding_programs(this.options)">
		<?php

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
		</div></div>
		<div id="step2" style="float: left; margin-bottom: 1.5em;"></div>
		<div id="step3" style="float: left; margin-bottom: 1.5em;"></div>
		<div id="step4" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%"></div>
		
<?php
	}
	
	private function step1_phenotype()
	{
		?>
		<div id="step1" style="float: left; margin-bottom: 1.5em;">
        <table id="phenotypeSelTab" class="tableclass1">
		<tr>
			<th>Category</th>
		</tr>
		<tr><td>
			<select name="phenotype_categories" multiple="multiple" style="height: 12em;" onchange="javascript: update_phenotype_categories(this.options)">
                <?php
		$sql = "SELECT phenotype_category_uid AS id, phenotype_category_name AS name from phenotype_category";
		$res = mysql_query($sql) or die(mysql_error());
		while ($row = mysql_fetch_assoc($res))
		{
		 ?>
				<option value="<?php echo $row['id'] ?>">
					<?php echo $row['name'] ?>
				</option>
				<?php
		}
		?>
		</select>
		</td>
		</table>
		</div>
		<div id="step2" style="float: left; margin-bottom: 1.5em;"></div>
		<div id="step3" style="float: left; margin-bottom: 1.5em;"></div>
		<div id="step4" style="float: left; margin-bottom: 1.5em;"></div>
		<div id="step5" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%"></div>
		<?php
	}

	private function step2_phenotype()
    {  
		$phen_cat = $_GET['pc'];
		?>
        
        <table id="phenotypeSelTab" class="tableclass1">
		<tr>
			<th>Traits</th>
		</tr>
		<tr><td>
		<select name="phenotype_items" multiple="multiple" style="height: 12em;" onchange="javascript: update_phenotype_items(this.options)">
                <?php

		$sql = "SELECT phenotype_uid AS id, phenotypes_name AS name from phenotypes, phenotype_category
		 where phenotypes.phenotype_category_uid = phenotype_category.phenotype_category_uid and phenotype_category.phenotype_category_uid in ($phen_cat)";
		$res = mysql_query($sql) or die(mysql_error());
		while ($row = mysql_fetch_assoc($res))
		{
		 ?>
		    <option value="<?php echo $row['id'] ?>">
		     <?php echo $row['name'] ?>
		    </option>
		    <?php
		}
		?>
		</select>
		</table>
		
		<?php
    }
    
	private function step3_phenotype()
    {  
		$phen_item = $_GET['pi'];
		?>
		
        <table id="phenotypeSelTab" class="tableclass1">
		<tr>
			<th>Trials</th>
		</tr>
		<tr><td>
		<select name="trials" multiple="multiple" style="height: 12em;" onchange="javascript: update_phenotype_trial(this.options)">
                <?php

		$sql = "SELECT DISTINCT tb.experiment_uid as id, e.trial_code as name 
	 FROM experiments as e, tht_base as tb, phenotype_data as pd, phenotypes as p
	 WHERE
	 e.experiment_uid = tb.experiment_uid
	 AND pd.tht_base_uid = tb.tht_base_uid
	 AND p.phenotype_uid = pd.phenotype_uid
	 AND pd.phenotype_uid IN ($phen_item)";
		$res = mysql_query($sql) or die(mysql_error());
		while ($row = mysql_fetch_assoc($res))
		{
		 ?>
		    <option value="<?php echo $row['id'] ?>">
		     <?php echo $row['name'] ?>
		    </option>
		    <?php
		}
		?>
		</select>
		</table>
		
		<?php
    }
    
	private function step4_phenotype()
    {  
    	$phen_item = $_GET['pi'];
		$experiments = $_GET['e'];
		$selected_lines = array();
		$_SESSION['phenotype'] = $phen_item; // Empty the session array.
		?>
		
        <table id="phenotypeSelTab" class="tableclass1">
		<tr>
			<th>Lines</th>
		</tr>
		<tr><td>
		<select name="lines" multiple="multiple" style="height: 12em;" onchange="javascript: update_phenotype_lines(this.options)">
                <?php

		$sql = "SELECT DISTINCT lr.line_record_uid as id, lr.line_record_name as name 
	 FROM tht_base as tb, phenotype_data as pd, phenotypes as p, line_records as lr 
	 WHERE
	 pd.tht_base_uid = tb.tht_base_uid
	 AND p.phenotype_uid = pd.phenotype_uid
	 AND lr.line_record_uid = tb.line_record_uid
	 AND pd.phenotype_uid IN ($phen_item)
	 AND tb.experiment_uid IN ($experiments)
	 ORDER BY lr.line_record_name";
		$_SESSION['selected_lines'] = array(); // Empty the session array.
		$res = mysql_query($sql) or die(mysql_error());
		while ($row = mysql_fetch_assoc($res))
		{
		 array_push($_SESSION['selected_lines'],$row['id']);
		 ?>
				<option selected value="<?php echo $row['id'] ?>">
					<?php echo $row['name'] ?>
				</option>
				<?php
		}
		?>
		</select>
		</table>	
		<?php
    }
    
    private function step5_phenotype()
    {
     $phen_item = $_GET['pi'];
     $experiments = $_GET['e'];
     if (!empty($_GET['lines'])) {
         $selectedlines = $_GET['lines'];
         $selectedlines = explode(',', $selectedlines);
         $_SESSION['selected_lines'] = $selectedlines;
     }
     $count = count($_SESSION['selected_lines']);
     echo "selected lines = $count<br>";
     ?>
    
    <input type='button' value='Download for Tassel V2' onclick='javascript:use_session_v2();'></input>
    <br><b>or</b><br>
    <input type='button' value='Download for Tassel V3' onclick='javascript:use_session_v3();'></input>
    
    <?php
    }
    
    private function step1_yearprog()
    {
     ?>
    <div id="step1" style="float: left; margin-bottom: 1.5em;">
    <table id="phenotypeSelTab" class="tableclass1">
    <tr>
    <th>Year</th>
    </tr>
    <tr><td>
    <select name="year" multiple="multiple" style="height: 12em;" onchange="javascript: update_yearprog(this.options)">
    <?php
    $sql = "SELECT e.experiment_year AS year FROM experiments AS e, experiment_types AS et
    WHERE e.experiment_type_uid = et.experiment_type_uid
    AND et.experiment_type_name = 'phenotype'
    GROUP BY e.experiment_year ASC";
    $res = mysql_query($sql) or die(mysql_error());
    while ($row = mysql_fetch_assoc($res))
    {
    ?>
    <option value="<?php echo $row['year'] ?>"><?php echo $row['year'] ?></option>
    <?php
    }
    ?>
    </select>
    </td>
    </table>
    </div>
    <div id="step2" style="float: left; margin-bottom: 1.5em;"></div>
    <div id="step3" style="float: left; margin-bottom: 1.5em;"></div>
    <div id="step4" style="float: left; margin-bottom: 1.5em;"></div>
    <div id="step5" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%"></div>
    <?php
    }

    private function updatelines_tassel()
    {
    }
    
	private function step1_breedprog()
	{
		$CAPdata_programs = $_GET['bp']; //"'" . implode("','", explode(',',$_GET['bp'])) . "'";
                $years = $_GET['yrs']; //"'" . implode("','", explode(',',$_GET['yrs'])) . "'";
?>
                <table>
                <tr>
                        <th>Breeding Program</th>
                        <th>Year</th>
                </tr>
		<tr>
                                        <td>
                                                <select name="breeding_programs" multiple="multiple" style="height: 12em;" onchange="javascript: update_breeding_programs(this.options)">
                <?php

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
<?php	
	}
	private function step1_dataprog()
	{
		$CAPdata_programs = $_GET['bp']; //"'" . implode("','", explode(',',$_GET['bp'])) . "'";
                $years = $_GET['yrs']; //"'" . implode("','", explode(',',$_GET['yrs'])) . "'";
?>		
		<table>
		<tr>
			<th>Data Program</th>
			<th>Year</th>
		</tr>
<tr><td><select name="breeding_programs" multiple="multiple" style="height: 12em;" onchange="javascript: update_breeding_programs(this.options)">
<?php
		$sql = "SELECT CAPdata_programs_uid AS id, data_program_name AS name, data_program_code AS code
                                FROM CAPdata_programs WHERE program_type='data' ORDER BY name";
      		$res = mysql_query($sql) or die(mysql_error());
		while ($row = mysql_fetch_assoc($res)) {
			?>
			<option value="<?php echo $row['id'] ?>"><?php echo $row['name']."(".$row['code'].")" ?></option>
			<?php
		}
?>
</select>
	</td><td>
<select name="year" multiple="multiple" style="height: 12em;" onchange="javascript: update_years(this.options)">
<?php
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
<?php
	}	
    private function type1_lines_trial_trait()
    {
	    ?>
	    <style type="text/css">
			th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
			table {background: none; border-collapse: collapse}
			td {border: 1px solid #eee !important;}
			h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
		</style>
		<div id="step1" style="float: left; margin-bottom: 1.5em;">
		<p>1. 
		<select name="select1" onchange="javascript: update_select1(this.options)">
		  <option value="BreedingProgram">Breeding Program and Year</option>
		  <!--  <option value="Years">Year and Trial</option> -->
		  <option selected value="Lines">Lines</option>
		  <option value="Phenotypes">Traits</option>
		</select></p>
		<?php 
		if (isset($_SESSION['selected_lines'])) {
			$selectedlines= $_SESSION['selected_lines'];
	        $count = count($_SESSION['selected_lines']);
		?>
		<div id="step11">
	    <table id="phenotypeSelTab" class="tableclass1">
	    <tr>
	    <th>Lines</th>
	    </tr>
	    <tr><td>
	    <select multiple="multiple" name="lines" style="height: 12em;">
	    <?php
	    foreach($selectedlines as $uid) {
	      $sql = "SELECT line_record_name from line_records where line_record_uid = $uid";
	      $res = mysql_query($sql) or die(mysql_error());
	      $row = mysql_fetch_assoc($res);
	      ?>
	      <option selected value="
	      <?php $uid ?>">
	      <?php echo $row['line_record_name'] ?>
	      </option>
	      <?php     
	    }
	    ?>
	    </select>
	    </td>
	    </table>
	    </div></div>
	    <div id="step2" style="float: left; margin-bottom: 1.5em;">
	    <p>2.
	    <select name="select2">
	      <option value="trials">Trials</option>
	    </select></p>
	    <table id="linessel" class="tableclass1">
	    <tr>
	    <th>Trials</th>
	    </tr>
	    <tr><td>
	    <select name="trials" multiple="multiple" style="height: 12em;" onchange="javascript: update_line_trial(this.options)">
	    <?php
	    $selectedlines = implode(',', $selectedlines);
	    $experiment_str = "";
	    $sql="SELECT DISTINCT tb.experiment_uid as id, e.trial_code as name 
	    FROM experiments as e, tht_base as tb, line_records as lr
	    WHERE
	    e.experiment_uid = tb.experiment_uid
	    AND lr.line_record_uid = tb.line_record_uid
	    AND lr.line_record_uid IN ($selectedlines)";
		$res = mysql_query($sql) or die(mysql_error() . $sql);
		while ($row = mysql_fetch_assoc($res))
		{
		 ?>
		    <option value="<?php echo $row['id'] ?>">
		     <?php echo $row['name'];
		     if ($experiment_str == "") {
		     	$experiment_str = $row['id'];
		     } else {
		     	$experiment_str = $experiment_str . "," . $row['id'];
		     }
		      ?>
		    </option>
		    <?php
		} 
	    ?>
	    </select></table>
	    </div>
	    <div id="step3" style="float: left; margin-bottom: 1.5em;">
	    <p>3.
	    <select name="select3">
	      <option value="phenotypes">Traits</option>
	    </select></p>
	    <table id="" class="tableclass1">
	    <tr>
	    <th>Traits</th>
	    </tr>
	    <tr><td>
	    <select name="traits" multiple="multiple" style="height: 12em;">
	    <?php
	    $sql = "SELECT DISTINCT p.phenotype_uid AS id, phenotypes_name AS name from phenotypes as p, tht_base as tb, phenotype_data as pd
	      where pd.tht_base_uid = tb.tht_base_uid
         AND p.phenotype_uid = pd.phenotype_uid
	     AND tb.experiment_uid in ($experiment_str)";
		$res = mysql_query($sql) or die(mysql_error() . $sql);
		while ($row = mysql_fetch_assoc($res))
		{
		 ?>
		    <option value="<?php echo $row['id'] ?>">
		     <?php echo $row['name'] ?>
		    </option>
		    <?php
		}
	    ?>
	    </select></table>
	    </div>
	    <div id="step4" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%">
	    <?php
	    
	     $this->step4_lines();
	     ?></div>
	     <?php 	
	    } else {
	     echo "Please select lines before using this feature.<br>";
	     echo "<a href=";
	     echo $config['base_url'];
	     echo "pedigree/line_selection.php>Select Lines by Properties</a>";
	    }
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
	      <option selected value="
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
	        echo "pedigree/line_selection.php>Select Lines by Properties</a>";
	    }
	}
	
	private function step2_lines()
	{
		?>
	    <div id="step2" style="float: left; margin-bottom: 1.5em;">
	    <p>2.
	    <select name="select2">
	      <option value="trials">Trials</option>
	    </select></p>
	    <table id="linessel" class="tableclass1">
	    <tr>
	    <th>Trials</th>
	    </tr>
	    <tr><td>
	    <select name="trials" multiple="multiple" style="height: 12em;" onchange="javascript: update_line_trial(this.options)">
	    <?php
	    $selectedlines= $_SESSION['selected_lines'];
	    $selectedlines = implode(',', $selectedlines);
	    $sql="SELECT DISTINCT tb.experiment_uid as id, e.trial_code as name 
	    FROM experiments as e, tht_base as tb, line_records as lr
	    WHERE
	    e.experiment_uid = tb.experiment_uid
	    AND lr.line_record_uid = tb.line_record_uid
	    AND lr.line_record_uid IN ($selectedlines)";
		$res = mysql_query($sql) or die(mysql_error());
		while ($row = mysql_fetch_assoc($res))
		{
		 ?>
		    <option value="<?php echo $row['id'] ?>">
		     <?php echo $row['name'] ?>
		    </option>
		    <?php
		} 
	    ?>
	    </select></table>
	    <?php 
	}
	
	private function step3_lines()
	{
		?>
	    </div>
	    <div id="step3" style="float: left; margin-bottom: 1.5em;">
	    <p>3.
	    <select name="select3">
	      <option value="phenotypes">Traits</option>
	    </select></p>
	    <table id="" class="tableclass1">
	    <tr>
	    <th>Traits</th>
	    </tr>
	    <tr><td>
	    <select name="">
	    <?php
	    $sql = "SELECT phenotype_uid AS id, phenotypes_name AS name from phenotypes, tht_base, phenotype_data
	      where pd.tht_base_uid = tb.tht_base_uid
         AND p.phenotype_uid = pd.phenotype_uid
	     AND tb.experiment_uid in ($experiment_str)";
		$res = mysql_query($sql) or die(mysql_error() . $sql);
		while ($row = mysql_fetch_assoc($res))
		{
		 ?>
		    <option value="<?php echo $row['id'] ?>">
		     <?php echo $row['name'] ?>
		    </option>
		    <?php
		}
	    ?>
	    </select></table>
	    </div>
	    <div id="step4" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%">
	    <?php
	     $this->type1_markers();
	     ?></div>
	     <?php 		
	}
	
	private function step4_lines() {
	$saved_session = "";
	 if (isset($_SESSION['selected_lines'])) {
	     $countLines = count($_SESSION['selected_lines']);
	     if ($saved_session == "") {
	      $saved_session = "$countLines lines";
	     } else {
	      $saved_session = $saved_session . ", $countLines lines";
	     }
	 } else {
	     $countLines = 0;
	 }
	 if (isset($_SESSION['phenotype'])) {
	     $phenotype = $_SESSION['phenotype'];
	 } else {
	     $phenotype = "";
	 }
	 if (isset($_SESSION['clicked_buttons'])) {
	    $tmp = count($_SESSION['clicked_buttons']);
	    $saved_session = $saved_session . ", $tmp markers";
	    $markers = $_SESSION['clicked_buttons'];   
	 } else {
	     $markers = "";
	 }
	 if ($saved_session != "") {
	     echo "current data selection = $saved_session<br>";
	     if ($countLines == 0) {
	       echo "Choose one or more lines before using a saved selection. ";
	       echo "<a href=";
	       echo $config['base_url'];
	       echo "pedigree/line_selection.php> Select lines</a><br>";
	     } elseif ( $phenotype != "" ) {
	       echo "<br>Use existing selection to $message2<br>";
	       echo "<input type='button' value='Download for Tassel V2' onclick='javascript:use_session_v2();'</input>";
	       echo "<input type='button' value='Download for Tassel V3' onclick='javascript:use_session_v3();'</input>";
	     } else {
	       "<br>Use existing selection to $message2<br>";
	       echo "<input type='button' value='Download for Tassel' onclick='javascript:use_session();'</input>";
	     }
	  }
	}

	private function enter_lines()
	{
		if($_SERVER['REQUEST_METHOD'] == "GET")
  // Store what the user's previous selections were so we can
  // redisplay them as the page is redrawn.
 		{
		    $name = $_GET['LineSearchInput'];
		    echo "$names<br>";
		}
		?>
		<form id="searchLines" action="<?php echo $_SERVER['SCRIPT_NAME'] ?>" method="GET">
                <input type="hidden" name="function" value="enterlines">

      		<b>Name</b> <br/><br/>
      		<textarea name="LineSearchInput" rows="3" cols="20" style="height: 6em;"><?php $nm = explode('\r\n', $name); foreach ($nm as $n) echo $n."\n"; ?></textarea>
      		<br> Eg: Cayuga, Doyce<br>
      		Synonyms will be translated.
		<input type="submit" value=Search>
		</form>
		<?php
		if (isset($_GET['LineSearchInput'])) {
			$linenames = $_POST['LineSearchInput'];
			echo "made it here\n";
			if (strlen($linenames) != 0)
    
        if (strpos($linenames, ',') > 0 ) {
                        $linenames = str_replace(", ",",", $linenames);
                        $lineList = explode(',',$linenames);
                } elseif (preg_match("/\t/", $linenames)) {
                        $lineList = explode("\t",$linenames);
                } else {
                        $lineList = explode('\r\n',$linenames);
                }

        $items = implode("','", $lineList);
        $mStatment = "SELECT distinct (lr.line_record_name) FROM line_records lr left join line_synonyms ls on ls.line_record_uid = lr.line_record_uid where ls.line_synonym_name in ('" .$items. "') or lr.line_record_name in ('". $items. "');";

        $res = mysql_query($mStatment) or die(mysql_error());

        if (mysql_num_rows($res) != 0) {
        while($myRow = mysql_fetch_assoc($res)) {
          array_push ($lineArr,$myRow['line_record_name']);
        }
        // Generate the translated line names
        $linenames =  implode("','", $lineArr);
      } else {
        $linenames = '';
      }
	}
	}
	private function type1_experiments()
	{
		$CAPdata_programs = $_GET['bp']; //"'" . implode("','", explode(',',$_GET['bp'])) . "'";
		$years = $_GET['yrs']; //"'" . implode("','", explode(',',$_GET['yrs'])) . "'";
?>
<p>2. 
<select>
  <option>Trials</option>
</select></p>
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
				3. <select><option>Traits</option></select>
				<div>
					<p><em>No Trials Selected</em></p>
				</div>";
		}
		else
		{
?>
<p>3. 
<select><option>Traits</option></select></p>
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

	private function type1_markers_select()
	{
		?>
		<h3>Select markers by name</h3>
  <form action="<?php echo $config['base_url']; ?>genotyping/marker_selection.php" method="post">
  <table><tr><td>
  <textarea rows=6 cols=10 name=selMarkerstring></textarea>
  <td>Synonyms will be translated.
  <p><input type=submit value=Select style=color:blue>
  </tr></table>
  </form>
	<?php		
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
        $max_missing = 99.9;//IN PERCENT
        if (isset($_GET['mm']) && !empty($_GET['mm']) && is_numeric($_GET['mm']))
            $max_missing = $_GET['mm'];
		if ($max_missing>100)
			$max_missing = 100;
		elseif ($max_missing<0)
			$max_missing = 0;
        $min_maf = 0.01;//IN PERCENT
        if (isset($_GET['mmaf']) && !is_null($_GET['mmaf']) && is_numeric($_GET['mmaf']))
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
					if ($maf >= $min_maf)
						$num_maf++;
					if ($miss > $max_missing)
						$num_miss++;
					if (($miss > $max_missing) OR ($maf < $min_maf))
						$num_removed++; 
					//$mmarray[$row["marker"]]= array("maf"=>$maf,"miss"=>$miss);
				}
				
	
				//// $firephp->log($num_maf,"number of maf");
				//// $firephp->log($num_miss,"number with too many missing");
				//// $firephp->log($num_mark,"number of markers");
				if (mysql_num_rows($res) >= 1) {
				  ?>
				  <p>Minimum MAF &ge; <input type="text" name="mmaf" id="mmaf" size="2" value="<?php echo ($min_maf) ?>" />%
				    &nbsp;&nbsp;&nbsp;&nbsp;
				  Maximum missing data &le; <input type="text" name="mm" id="mm" size="2" value="<?php echo ($max_missing) ?>" />%
				    <i>
				    <br></i><b><?php echo ($num_maf) ?></b><i> markers have a minor allele frequency (MAF) at least </i><b><?php echo ($min_maf) ?></b><i>%.
				    <br></i><b><?php echo ($num_miss) ?></b><i> markers are missing more than </i><b><?php echo ($max_missing) ?></b><i>% of measurements.
				    <br></i><b><?php echo ($num_removed) ?></b><i> of </i><b><?php echo ($num_mark) ?></b><i> distinct markers will be removed.
				    </i>

				    <br><input type="button" value="Refresh" onclick="javascript:mrefresh();" /><br>
					<table> <tr> <td COLSPAN="3">
				    <br><input type="button" value="Download for Tassel V2" onclick="javascript:getdownload_tassel();" />
					<h4> or </h4>
				    <input type="button" value="Download for Tassel V3" onclick="javascript:getdownload_tassel_v3();" /> <br>
					</td> </tr> </table>	
				    <?php
				     } else {
				  ?><p style="font-weight: bold">No Data</p><?php
				    }
			
				?></div><?php
				      }else { //NO genotype experiments exist for these lines
			  ?>
			  <h3>There are no genotype experiments available for this Breeding Program/Year combination</h3>

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
		if (! file_exists('/tmp/tht')) mkdir('/tmp/tht');			
		$dir = '/tmp/tht/';
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
		if (! file_exists('/tmp/tht')) mkdir('/tmp/tht');			
		$dir = '/tmp/tht/';
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
		if (! file_exists('/tmp/tht')) mkdir('/tmp/tht');			
		$dir = '/tmp/tht/';
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
	
	/* Build trait download file for Tassel program interface */
	private function type2_build_tassel_traits_download($experiments,$traits, $subset)
	{
	 // $firephp = FirePHP::getInstance(true);
	 $delimiter = "\t";
	 $output = '';
	 $outputheader1 = '';
	 $outputheader2 = '';
	 $outputheader3 = "";
	
	 //count number of traits and number of experiments
	 $ntraits=substr_count($traits, ',')+1;
	 $nexp=substr_count($experiments, ',')+1;
	
	 //$traits = explode(',', $traits);
	 //$experiments = explode(',', $experiments);
	
	 // figure out which traits are at which location
	 $selectedlines = implode(",", $_SESSION['selected_lines']);
	 $sql = "SELECT DISTINCT e.trial_code, tb.experiment_uid, p.phenotypes_name,p.phenotype_uid
	 FROM experiments as e, tht_base as tb, phenotype_data as pd, phenotypes as p
	 WHERE
	 e.experiment_uid = tb.experiment_uid
	 AND tb.experiment_uid IN ($experiments)
	 AND tb.line_record_uid IN ($selectedlines) 
	 AND pd.tht_base_uid = tb.tht_base_uid
	 AND p.phenotype_uid = pd.phenotype_uid
	 AND pd.phenotype_uid IN ($traits)
	 ORDER BY p.phenotype_uid,tb.experiment_uid";
	 $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
	 $ncols = mysql_num_rows($res);
	 while($row = mysql_fetch_array($res)) {
	 $outputheader2 .= str_replace(" ","_",$row['phenotypes_name']).$delimiter;
	 $outputheader3 .= $row['trial_code'].$delimiter;
	 $keys[] = $row['phenotype_uid'].$row['experiment_uid'];
	}
	$nexp=$ncols;
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
	   AND line_records.line_record_uid=tht_base.line_record_uid";
	   $res = mysql_query($sql) or die(mysql_error() . "<br>type2 $sql");
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
	 //$firephp->log("sql ".$i." ".$sql);
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
	
	/* Build trait download file for Tassel program interface */
	private function type3_build_tassel_traits_download($traits, $subset)
	{
	 // $firephp = FirePHP::getInstance(true);
	 $delimiter = "\t";
	 $output = '';
	 $outputheader1 = '';
	 $outputheader2 = '';
	 $outputheader3 = "";
	
	 //count number of traits and number of experiments
	 $ntraits=substr_count($traits, ',')+1;
	 $nexp=substr_count($experiments, ',')+1;
	
	 //$traits = explode(',', $traits);
	 //$experiments = explode(',', $experiments);
	
	 // figure out which traits are at which location
	 $selectedlines = implode(",", $_SESSION['selected_lines']);
	 $sql = "SELECT DISTINCT e.trial_code, tb.experiment_uid, p.phenotypes_name,p.phenotype_uid
	 FROM experiments as e, tht_base as tb, phenotype_data as pd, phenotypes as p
	 WHERE
	 e.experiment_uid = tb.experiment_uid
	 AND tb.line_record_uid IN ($selectedlines)
	 AND pd.tht_base_uid = tb.tht_base_uid
	 AND p.phenotype_uid = pd.phenotype_uid
	 AND pd.phenotype_uid IN ($traits)
	 ORDER BY p.phenotype_uid,tb.experiment_uid";
	 $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
	 $ncols = mysql_num_rows($res);
	 while($row = mysql_fetch_array($res)) {
	 $outputheader2 .= str_replace(" ","_",$row['phenotypes_name']).$delimiter;
	 $outputheader3 .= $row['trial_code'].$delimiter;
	 $keys[] = $row['phenotype_uid'].$row['experiment_uid'];
	}
	 $nexp=$ncols;
	 //$firephp->log("trait_location information ".$outputheader2."  ".$outputheader3);
	 // $firephp->table('keys label ', $keys);
	
	 // dem 5jan11: If $subset="yes", use $_SESSION['selected_lines'].
	 $intheselines = "";
	 if ($subset == "yes" && count($_SESSION['selected_lines']) > 0) {
	 $selectedlines = implode(",", $_SESSION['selected_lines']);
	 $intheselines = " line_records.line_record_uid IN ($selectedlines)";
	} else {
	  $count = count($_SESSION['selected_lines']);
	  echo "count selected lines = $count";
	  echo "subset $subset";
	  die("error in selected lines");
	}
	 // get a list of all line names in the selected datasets and experiments,
	 // INCLUDING the check lines // AND tht_base.check_line IN ('no')
	 $sql = "SELECT DISTINCT line_records.line_record_name, line_records.line_record_uid
	 FROM line_records, tht_base
	 WHERE
	   $intheselines
	   AND line_records.line_record_uid=tht_base.line_record_uid";
	   $res = mysql_query($sql) or die(mysql_error() . "<br>type3 $sql");
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
	AND pd.tht_base_uid = tb.tht_base_uid
	AND pd.phenotype_uid IN ($traits)
	ORDER BY pd.phenotype_uid,tb.experiment_uid";*/
	// dem 8oct10: Don't round the data.
	//			$sql = "SELECT avg(cast(pd.value AS DECIMAL(9,1))) as value,pd.phenotype_uid,tb.experiment_uid
	$sql = "SELECT pd.value as value,pd.phenotype_uid,tb.experiment_uid
	FROM tht_base as tb, phenotype_data as pd
	WHERE tb.line_record_uid  = $line_uid[$i]
	AND pd.tht_base_uid = tb.tht_base_uid
	AND pd.phenotype_uid IN ($traits)
	GROUP BY tb.tht_base_uid, pd.phenotype_uid";
	
	$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
	//$firephp->log("sql ".$i." ".$sql);
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
        $min_maf = 0.01;//IN PERCENT
        if (isset($_GET['mmaf']) && !is_null($_GET['mmaf']) && is_numeric($_GET['mmaf']))
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
			  if (($maf >= $min_maf)AND ($miss<=$max_missing)) {
			    $marker_names[] = $row["name"];
			    $outputheader .= $row["name"].$delimiter;
			    $marker_uid[] = $row["marker"];
			  }
			}
			$nelem = count($marker_names);
			if ($nelem == 0) {
			    die("error - no genotype or marker data for this experiment, experiment_uid=$experiments");
			}
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
			
			
         $sql = "SELECT line_record_name, marker_name AS name,
                    alleles AS value
			FROM
            allele_cache as a
			WHERE
				a.marker_uid IN ($marker_uid)
				AND a.experiment_uid IN ($experiments)
		  ORDER BY a.line_record_name, a.marker_uid";


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
	
	private function type2_build_markers_download($lines,$markers,$dtype)
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
		$min_maf = 0.01;//IN PERCENT
		if (isset($_GET['mmaf']) && !is_null($_GET['mmaf']) && is_numeric($_GET['mmaf']))
			$min_maf = $_GET['mmaf'];
		if ($min_maf>100)
			$min_maf = 100;
		elseif ($min_maf<0)
		$min_maf = 0;
		// $firephp->log("in sort markers".$max_missing."  ".$min_maf);
		
		//get lines and filter to get a list of markers which meet the criteria selected by the user
		$subset = "";
		if ($markers != "") {
		    $subset = "AND af.marker_uid IN ($markers)";
		}
		$sql_mstat = "SELECT af.marker_uid as marker, m.marker_name as name, SUM(af.aa_cnt) as sumaa, SUM(af.missing)as summis, SUM(af.bb_cnt) as sumbb,
		SUM(af.total) as total, SUM(af.ab_cnt) AS sumab
		FROM allele_frequencies AS af, markers as m
		WHERE m.marker_uid = af.marker_uid " . $subset .
		" group by af.marker_uid";
		
		$res = mysql_query($sql_mstat) or die(mysql_error());
		$num_maf = $num_miss = 0;
		while ($row = mysql_fetch_array($res)){
				$marker_names[] = $row["name"];
				$outputheader .= $row["name"].$delimiter;
				$marker_uid[] = $row["marker"];
		}
		$nelem = count($marker_names);
		if ($nelem == 0) {
		   die("error - no genotype or marker data for this selection");
		}
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
		
		$subset = "";
		if ($markers != "") {
		   $subset = "WHERE a.marker_uid IN ($markers)";
		}
		if ($lines != "") {
		   if ($subset == "") {
		       $subset = "WHERE a.line_records_uid in ($lines)";
		   } else {
		       $subset = $subset . " and a.line_records_uid in ($lines) ";
		   }
		}
		   
		$sql = "SELECT line_record_name, marker_name AS name,
		alleles AS value
		FROM
		allele_cache as a " . $subset .
		" ORDER BY a.line_record_name, a.marker_uid";
		
		
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
        $min_maf = 0.01;//IN PERCENT
        if (isset($_GET['mmaf']) && !is_null($_GET['mmaf']) && is_numeric($_GET['mmaf']))
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
					if (($maf >= $min_maf)AND ($miss<=$max_missing)) {
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
        $min_maf = 0.01;//IN PERCENT
        if (isset($_GET['mmaf']) && !is_null($_GET['mmaf']) && is_numeric($_GET['mmaf']))
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
					if (($maf >= $min_maf)AND ($miss<=$max_missing)) {
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
