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

// For live website file
require_once 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
set_include_path(GET_INCLUDE_PATH() . PATH_SEPARATOR . '../pear/');
date_default_timezone_set('America/Los_Angeles');

require_once $config['root_dir'].'includes/MIME/Type.php';
require_once $config['root_dir'].'includes/File_Archive/Archive.php';

// connect to database
connect();

new Downloads($_GET['function']);

/** Using a PHP class to implement the "Download Gateway" feature
 * 
 * @category PHP
 * @package  T3
 * @author   Clay Birkett <claybirkett@gmail.com>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/downloads/downloads.php
 **/
class Downloads
{   
    /**
     * delimiter used for output files
     */
    public $delimiter = "\t";
    
    /** 
     * Using the class's constructor to decide which action to perform
     * @param string $function action to perform
     */
    public function __construct($function = null)
    {	
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
            case 'step1locations':
                $this->step1_locations();
                break;
            case 'step2locations':
                $this->step2_locations();
                break;
			case 'step3locations':
			    $this->step3_locations();
			    break;
			case 'step5locations':
			    $this->step5_locations();
			    break;
			case 'step5programs':
			     $this->step5_programs();
			     break;
			case 'step2lines':
				$this->step2_lines();
				break;
			case 'step3lines':
				$this->step3_lines();
				break;
		 	case 'step4lines':
				$this->step4_lines();
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
			case 'step1yearprog':
			    $this->step1_yearprog();
			    break;
			case 'type1traits':
				$this->type1_traits();
				break;
			case 'type1markers':
				$this->type1_markers();
				break;
			case 'type2markers':
			    $this->type2_markers();
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
			case 'download_session_v2':
			    echo $this->type1_session(V2);
			    break;
			case 'download_session_v3':
			    echo $this->type1_session(V3);
			    break;
			case 'download_session_v4':
			    echo $this->type1_session(V4);
			    break;
			case 'type2_build_tassel_v2':
			    echo $this->type2_build_tassel(V2);
			    break;
			case 'type2_build_tassel_v3':
			    echo $this->type2_build_tassel(V3);
			    break;
			case 'type2_build_tassel_v4':
			    echo $this->type2_build_tassel(V4);
			    break;
			case 'refreshtitle':
			    echo $this->refresh_title();
			    break;
			default:
				$this->type1_select();
				break;
				
		}	
	}

	/**
	 * load header and footer then check session to use existing data selection
	 */
	private function type1_select()
	{
		global $config;
                include($config['root_dir'].'theme/normal_header.php');
		$phenotype = "";
                $lines = "";
		$markers = "";
		$saved_session = "";
		$this->type1_checksession();
		include($config['root_dir'].'theme/footer.php');
	}	
	
	/**
	 * When there is no data saved in session this handles outputting the header and footer and calls the first real action of the type1 download.
	 */ 
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
		
		?>
		<p>1.
		<select name="select1" onchange="javascript: update_select1(this.options)">
		<option value="BreedingProgram">Program</option>
		<option value="Lines">Lines</option>
		<option value="Locations">Locations</option>
		<option value="Phenotypes">Trait Category</option>
		</select></p>
		<?php 
		$this->step1_breedprog();
		$footer_div = 1;
        #	include($config['root_dir'].'theme/footer.php');
	}
	
	/**
	 * Checks the session variable, if there is lines data saved then go directly to the lines menu
	 */
	private function type1_checksession()
    {
            ?>
            <style type="text/css">
			th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
			table {background: none; border-collapse: collapse}
			td {border: 1px solid #eee !important;}
			h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
		</style>
		<div id="title">
		<?php
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
            $this->refresh_title();
         ?>        
        </div>
		<div id="step1" style="float: left; margin-bottom: 1.5em;">
		<p>1. 
		<select name="select1" onchange="javascript: update_select1(this.options)">
		  <option value="BreedingProgram">Program</option>
		  <!-- option value="Years">Year</option-->
		  <option <?php 
		  if (isset($_SESSION['selected_lines'])) {
		       echo "selected='selected'";
		  }
		  ?> value="Lines">Lines</option>
		  <option value="Locations">Locations</option>
		  <option value="Phenotypes">Trait Category</option>
		</select></p>
		        <script type="text/javascript" src="downloads/downloads.js"></script>
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

    /**
     * 1. display a spinning activity image when a slow function is running
     * 2. show button to clear sessin data
     * 3. show button to save current selection
     */    
    private function refresh_title() {
      $command = (isset($_GET['cmd']) && !empty($_GET['cmd'])) ? $_GET['cmd'] : null;
      ?>
      <h2>Tassel Download</h2>
      <p>
      <em>Select multiple options by holding down the Ctrl key while clicking.</em> 
      <img alt="spinner" id="spinner" src="images/ajax-loader.gif" style="display:none;" />
      <?php 
      $selection_ready = 0;
      if (isset($_SESSION['selected_lines'])) {
        ?>
        <input type="button" value="Clear current selection" onclick="javascript: use_normal();"/>
        <?php 
      }
      if (isset($_GET['lines']) && !empty($_GET['lines'])) {
        $selection_ready = 1;
      } elseif (isset($_GET['pi']) && !empty($_GET['pi'])) {
        $selection_ready = 1;
      } elseif (isset($_GET['e']) && !empty($_GET['e'])) {
        $selection_ready = 1;
      }
      if ($command == "save") {
        if (!empty($_GET['lines'])) {
          $lines_str = $_GET['lines'];
          $lines = explode(',', $lines_str);
          $_SESSION['selected_lines'] = $lines;
        } elseif ((!empty($_GET['pi'])) && (!empty($_GET['exps']))) {
          $phen_item = $_GET['pi'];
          $experiments = $_GET['exps'];
          $sql = "SELECT DISTINCT lr.line_record_uid as id, lr.line_record_name as name
          FROM tht_base as tb, phenotype_data as pd, phenotypes as p, line_records as lr
          WHERE
          pd.tht_base_uid = tb.tht_base_uid
          AND p.phenotype_uid = pd.phenotype_uid
          AND lr.line_record_uid = tb.line_record_uid
          AND pd.phenotype_uid IN ($phen_item)
          AND tb.experiment_uid IN ($experiments)
          ORDER BY lr.line_record_name";
          $lines = array();
          $res = mysql_query($sql) or die(mysql_error() . $sql);
          while ($row = mysql_fetch_assoc($res))
          {
            array_push($lines,$row['id']);
          }
          $lines_str = implode(",", $lines);
          $_SESSION['selected_lines'] = $lines;
        } else {
          echo "error - no selection found";
        }
        $username=$_SESSION['username'];
        if ($username) {
          store_session_variables('selected_lines', $username);
        }
      } elseif ($selection_ready) {
        ?>
        <input type="button" value="Save current selection" onclick="javascript: load_title('save');"/>
       <?php
      }
      ?>
      </p>
      <?php 
    }
    
    /**
     * use this download when selecting program and year
     * @param string $version Tassel version of output
     */
    private function type1_session($version)
	{
	    $experiments_t = (isset($_GET['e']) && !empty($_GET['e'])) ? $_GET['e'] : null;
	    $datasets_exp = "";
		if (isset($_SESSION['selected_lines'])) {
			$selectedcount = count($_SESSION['selected_lines']);
			$lines = $_SESSION['selected_lines'];
			$lines_str = implode(",", $lines);
		} else {
			$lines = "";
			$lines_str = "";
		}
		if (isset($_SESSION['clicked_buttons'])) {
		    $selectcount = $_SESSION['clicked_buttons'];
		    $markers = $_SESSION['clicked_buttons'];
		    //$markers = implode(",", $_SESSION['clicked_buttons']);
		} else {
		    $markers = array();
		}
		if (isset($_SESSION['phenotype'])) {
		    $phenotype = $_SESSION['phenotype'];
		} else {
		    $phenotype = "";
		}
		
		//get genotype experiments
		$sql_exp = "SELECT DISTINCT e.experiment_uid AS exp_uid
		FROM experiments e, experiment_types as et, line_records as lr, tht_base as tb
		WHERE
		e.experiment_type_uid = et.experiment_type_uid
		AND lr.line_record_uid = tb.line_record_uid
		AND e.experiment_uid = tb.experiment_uid
		AND lr.line_record_uid in ($lines_str)
		AND et.experiment_type_name = 'genotype'";
		$res = mysql_query($sql_exp) or die(mysql_error() . "<br>" . $sql_exp);
		if (mysql_num_rows($res)>0) {
		 while ($row = mysql_fetch_array($res)){
		  $exp[] = $row["exp_uid"];
		 }
		 $experiments_g = implode(',',$exp);
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
        
        if (isset($_SESSION['phenotype'])) {
		  $zip->newFile("traits.txt");
		  $zip->writeData($this->type1_build_tassel_traits_download($experiments_t,$phenotype,$datasets_exp,$subset));
        }
        if ($version == "V2") {
          $zip->newFile("annotated_alignment.txt");
          $zip->writeData($this->type1_build_annotated_align($experiments_g));
        } elseif ($version == "V3") {
          $zip->newFile("geneticMap.txt");
          $zip->writeData($this->type1_build_geneticMap($experiments_g));
          $zip->newFile("snpfile.txt");
          $zip->writeData($this->type2_build_markers_download($lines,$markers,$dtype));
        } elseif ($version == "V4") {
          $zip->newFile("genotype_hapmap.txt");
          $zip->writeData($this->type3_build_markers_download($lines,$markers,$dtype));
        }
        $zip->newFile("allele_conflict.txt");
        $zip->writeData($this->type2_build_conflicts_download($lines,$markers));
        $zip->close();
        
        header("Location: ".$dir.$filename);
	}
	
	/**
	 * this is the main entry point when there are no lines saved in session variable
	 */
    private function type1_breeding_programs_year()
	{
		?>	
			<div id="step11">
			<table>
				<tr>
					<th>Breeding Program</th>
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
				<option value="<?php echo $row['id'] ?>"><?php echo $row['name']." (".$row['code'].")" ?></option>
			<?php
		}
		?>
						</select>
			</table>
			</div></div>
					
			<div id="step2" style="float: left; margin-bottom: 1.5em;">
			<p>2.
		<select name="select2">
		  <option value="BreedingProgram">Year</option>
		</select></p>
			<table>
					<tr>
					    <th>Year</th>
					</tr>
					<tr>
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
		<div id="step3" style="float: left; margin-bottom: 1.5em;"></div>
		<div id="step4" style="float: left; margin-bottom: 1.5em;"></div>
		<div id="step4b" style="float: left; margin-bottom: 1.5em;"></div>
		<div id="step5" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%"></div>
		
<?php
	}
	
	/**
	 * starting with phenotype display phenotype categories
	 */
	private function step1_phenotype()
	{
		?>
		<div id="step11" style="float: left; margin-bottom: 1.5em;">
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
		<?php
	}

	/**
	 * starting with phenotype display phenotype items
	 */
	private function step2_phenotype()
    {  
		$phen_cat = $_GET['pc'];
		?>
		<p>2.
		<select name="select1">
		  <option value="BreedingProgram">Trait</option>
		</select></p>
        <table id="phenotypeSelTab" class="tableclass1">
		<tr>
			<th>Traits</th>
		</tr>
		<tr><td>
		<select id="traitsbx" name="phenotype_items" multiple="multiple" style="height: 12em;" onchange="javascript: update_phenotype_items(this.options)">
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
    
    /**
     * starting with phenotype display trials
     */
	private function step3_phenotype()
    {  
		$phen_item = $_GET['pi'];
		$trait_cmb = (isset($_GET['trait_cmb']) && !empty($_GET['trait_cmb'])) ? $_GET['trait_cmb'] : null;
		if ($trait_cmb == "all") {
		   $any_ckd = ""; $all_ckd = "checked";
		} else {
		   $trait_cmb = "any";
		   $any_ckd = "checked"; $all_ckd = "";
		}
		?>
		<p>3.
		<select name="select1">
		  <option value="BreedingProgram">Trials</option>
		</select></p>
        <table id="phenotypeSelTab" class="tableclass1">
		<tr>
			<th>Trials</th>
		</tr>
		<tr><td>
		<select name="trials" multiple="multiple" style="height: 12em;" onchange="javascript: update_phenotype_trial(this.options)">
                <?php

		$sql = "SELECT DISTINCT tb.experiment_uid as id, e.trial_code as name, p.phenotype_uid 
	 FROM experiments as e, tht_base as tb, phenotype_data as pd, phenotypes as p
	 WHERE
	 e.experiment_uid = tb.experiment_uid
	 AND pd.tht_base_uid = tb.tht_base_uid
	 AND p.phenotype_uid = pd.phenotype_uid
	 AND pd.phenotype_uid IN ($phen_item)";
	 if (!authenticate(array(USER_TYPE_PARTICIPANT, USER_TYPE_CURATOR, USER_TYPE_ADMINISTRATOR)))
	 $sql .= " and data_public_flag > 0";
	 $sql .= " ORDER BY e.experiment_year DESC, e.trial_code";
		$res = mysql_query($sql) or die(mysql_error());
		while ($row = mysql_fetch_assoc($res))
		{
		  $exp_uid = $row['id'];
		  $pi = $row['phenotype_uid'];
		  $sel_list[$exp_uid] = $row['name'];
		  $pi_list[$exp_uid][$pi] = 1;        //*array of traits for each trial
		}
		$phen_array = explode(",",$phen_item);
		foreach ($sel_list as $id=>$name) {
		  $found = 1;
		  foreach ($phen_array as $item) {    //*check if trial contains all trait
		    if (!isset($pi_list[$id][$item])) {
		       $found = 0;
		    }
		  }
		  if ($found || ($trait_cmb == "any")) {
		  ?>
		  <option value="<?php echo $id ?>">
		  <?php echo $name ?>
		  </option>
		  <?php
		  }
		}
		?>
		</select>
		</table>
		<?php 
		$tmp = count($phen_array);
		if ($tmp > 1) {
		  ?>
		  <input type="radio" id="trait_cmb" value="all" <?php echo "$all_ckd"; ?> onchange="javascript: update_phenotype_trialb(this.value)">trials with all traits<br>
		  <input type="radio" id="trait_cmb" value="any" <?php echo "$any_ckd"; ?> onchange="javascript: update_phenotype_trialb(this.value)">trials with any trait<br>
		  <?php
		}
    }
    
    /**
     * starting with phenotype display lines
     */
	private function step4_phenotype()
    {  
    	$phen_item = $_GET['pi'];
		$experiments = $_GET['e'];
		$subset = (isset($_GET['subset']) && !empty($_GET['subset'])) ? $_GET['subset'] : null;
		$selected_lines = array();
		$_SESSION['phenotype'] = $phen_item; // Empty the session array.
		
		if (count($_SESSION['selected_lines']) > 0) {
		 $sub_ckd = ""; $all_ckd = "checked";
		} else {
		 $sub_ckd = "disabled"; $all_ckd = "checked";
		}
		if ($subset == "yes") {
		 $sub_ckd = "checked"; $all_ckd = "";
		} elseif ($subset == "no") {
		 $sub_ckd = ""; $all_ckd = "checked";
		} elseif ($subset == "comb") {
		 $sub_ckd = ""; $cmb_ckd = "checked";
		}
		?>
		<p>4.
		<select name="select1">
		  <option value="BreedingProgram">Lines</option>
		</select></p>
		
        <table id="phenotypeSelTab" class="tableclass1">
		<tr>
			<th>Lines</th>
		</tr>
		<tr><td>
		<select name="lines" multiple="multiple" style="height: 12em;" onchange="javascript: update_phenotype_lines(this.options)">
                <?php
        if ($sub_ckd == "checked") {
          $lines_list = array();
          $lines_new = "";
          $selected_lines = $_SESSION['selected_lines'];
          foreach ($selected_lines as $line) {
            $sql = "SELECT line_record_uid as id, line_record_name as name from line_records where line_record_uid = $line";
            $res = mysql_query($sql) or die(mysql_error());
            $row = mysql_fetch_assoc($res);
            ?>
            <option selected value="<?php echo $row['id'] ?>">
            <?php echo $row['name'] ?>
            </option>
            <?php
          }
        } elseif ($cmb_ckd == "checked") {
          $selected_lines = $_SESSION['selected_lines'];
          foreach ($selected_lines as $line) {
            $sql = "SELECT line_record_uid as id, line_record_name as name from line_records where line_record_uid = $line";
            $res = mysql_query($sql) or die(mysql_error());
            $row = mysql_fetch_assoc($res);
            ?>
           <option selected value="<?php echo $row['id'] ?>">
           <?php echo $row['name'] ?>
           </option>
           <?php
         }
         $sql = "SELECT DISTINCT lr.line_record_uid as id, lr.line_record_name as name
         FROM tht_base as tb, phenotype_data as pd, phenotypes as p, line_records as lr
         WHERE
         pd.tht_base_uid = tb.tht_base_uid
         AND p.phenotype_uid = pd.phenotype_uid
         AND lr.line_record_uid = tb.line_record_uid
         AND pd.phenotype_uid IN ($phen_item)
         AND tb.experiment_uid IN ($experiments)
         ORDER BY lr.line_record_name";
         $res = mysql_query($sql) or die(mysql_error());
         while ($row = mysql_fetch_assoc($res))
         {
           $temp1 = $row['name'];
           $temp2 = $row['id'];
           if (isset($lines_list[$temp2])) {
           } else {
             if ($lines_new == "") {
               $lines_new = $temp1;
               ?>
               <option disabled="disabled">--added--
               </option>
               <?php
             }
             ?>
             <option selected value="<?php echo $row['id'] ?>">
             <?php echo $row['name'] ?>
             </option>
             <?php
           }
         }
        } else {
		$sql = "SELECT DISTINCT lr.line_record_uid as id, lr.line_record_name as name 
	 FROM tht_base as tb, phenotype_data as pd, phenotypes as p, line_records as lr 
	 WHERE
	 pd.tht_base_uid = tb.tht_base_uid
	 AND p.phenotype_uid = pd.phenotype_uid
	 AND lr.line_record_uid = tb.line_record_uid
	 AND pd.phenotype_uid IN ($phen_item)
	 AND tb.experiment_uid IN ($experiments)
	 ORDER BY lr.line_record_name";
		//$_SESSION['selected_lines'] = array(); // Empty the session array.
		$res = mysql_query($sql) or die(mysql_error());
		while ($row = mysql_fetch_assoc($res))
		{
		 //array_push($_SESSION['selected_lines'],$row['id']);
		 ?>
				<option selected value="<?php echo $row['id'] ?>">
					<?php echo $row['name'] ?>
				</option>
				<?php
		}
        }
		?>
		</select>
		</table>	
		<?php
		if (count($_SESSION['selected_lines']) > 0) {
		  ?>
		  <input type="radio" name="subset" id="subset" value="yes" <?php echo "$sub_ckd"; ?> onchange="javascript: update_phenotype_linesb(this.value)">Include only <a href="<?php echo $config['base_url']; ?>pedigree/line_selection.php">currently
		  selected lines</a><br>
		  <input type="radio" name="subset" id="subset" value="no" <?php echo "$all_ckd"; ?> onchange="javascript: update_phenotype_linesb(this.value)">Use lines with selected <b>Trials</b> and <b>Traits</b><br>
		  <input type="radio" name="subset" id="subset" value="comb" <?php echo "$cmb_ckd"; ?> onchange="javascript: update_phenotype_linesb(this.value)">Combine two sets<br>
		  <?php
		}
    }
    
    /**
     * starting with phenotype display marker data
     */
    private function step5_phenotype()
    {
     $phen_item = $_GET['pi'];
     $experiments = $_GET['e'];
     $subset = (isset($_GET['subset']) && !empty($_GET['subset'])) ? $_GET['subset'] : null;
     
     if (empty($_GET['lines'])) {
       if ((($subset == "yes") || ($subset == "comb")) && (count($_SESSION['selected_lines'])>0)) {
         $lines = $_SESSION['selected_lines'];
         $selectedlines = implode(",", $_SESSION['selected_lines']);
         $count = count($lines);
       } else {
         $lines = array();
         $sql = "SELECT DISTINCT lr.line_record_uid as id, lr.line_record_name as name
         FROM tht_base as tb, phenotype_data as pd, phenotypes as p, line_records as lr
         WHERE
         pd.tht_base_uid = tb.tht_base_uid
         AND p.phenotype_uid = pd.phenotype_uid
         AND lr.line_record_uid = tb.line_record_uid
         AND pd.phenotype_uid IN ($phen_item)
         AND tb.experiment_uid IN ($experiments)
         ORDER BY lr.line_record_name";
         $res = mysql_query($sql) or die(mysql_error());
         while ($row = mysql_fetch_assoc($res))
         {
           array_push($lines,$row['id']);
         }
         $selectedlines = implode(",", $lines);
         $count = count($lines);
       }
       if ($subset == "comb") {
         $sql = "SELECT DISTINCT lr.line_record_uid as id, lr.line_record_name as name
         FROM tht_base as tb, phenotype_data as pd, phenotypes as p, line_records as lr
         WHERE
         pd.tht_base_uid = tb.tht_base_uid
         AND p.phenotype_uid = pd.phenotype_uid
         AND lr.line_record_uid = tb.line_record_uid
         AND pd.phenotype_uid IN ($phen_item)
         AND tb.experiment_uid IN ($experiments)
         ORDER BY lr.line_record_name";
         $res = mysql_query($sql) or die(mysql_error());
         while ($row = mysql_fetch_assoc($res))
         {
           array_push($lines,$row['id']);
         }
         $selectedlines = implode(",", $lines);
         $count = count($lines);
       }
     } else {
         $selectedlines = $_GET['lines'];
         $lines = explode(',', $selectedlines);
         $count = count($lines);
     }
         
     echo "current data selection = $count lines<br>";
     
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
     
     $this->calculate_af($lines, $min_maf, $max_missing);
     
     ?>
     <input type="hidden" name="subset" id="subset" value="yes" /><br>
     <input type="button" value="Download for Tassel V3" onclick="javascript:getdownload_tassel('v3');" />
     <h4> or </h4>
     <input type="button" value="Download for Tassel V4" onclick="javascript:getdownload_tassel('v4');" /> <br>
 
    <?php
    }
    
    /**
     * starting with year
     */
    private function step1_yearprog()
    {
     ?>
    <div id="step11" style="float: left; margin-bottom: 1.5em;">
    <table id="phenotypeSelTab" class="tableclass1">
    <tr>
    <th>Year</th>
    </tr>
    <tr><td>
    <select name="year" multiple="multiple" style="height: 12em;" onchange="javascript: update_years(this.options)">
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
    <?php
    }
    
    /**
     * starting with breeding program display breeding program and year
     */
	private function step1_breedprog()
	{
		$CAPdata_programs = $_GET['bp']; //"'" . implode("','", explode(',',$_GET['bp'])) . "'";
                $years = $_GET['yrs']; //"'" . implode("','", explode(',',$_GET['yrs'])) . "'";
?>
                <div id="step11" style="float: left; margin-bottom: 1.5em;">
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
                                <option value="<?php echo $row['id'] ?>"><?php echo $row['name']." (".$row['code'].")" ?></option>
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
	
	/**
	 * starting with data program display dataprogram and year
	 */
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
	
	/**
	 * main entry point when there is a line selection in session variable
	 */
    private function type1_lines_trial_trait()
    {
		?>
		<div id="step11">
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
	    <div id="step4" style="float: left; margin-bottom: 1.5em;"></div>
	    <div id="step4b" style="float: left; margin-bottom: 1.5em;"></div>
	    <div id="step5" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%">
	    <script type="text/javascript">
	      var mm = 99.9;
	      var mmaf = 0.01; 
          window.onload = load_markers_lines( mm, mmaf);
	    </script>
	    </div>
	     <?php 	
	}
	
	/**
	 * starting with lines display the selected lines
	 */
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
	        echo "pedigree/line_selection.php>Select Lines by Properties</a>";
	    }
	}
	
	/**
	 * starting with lines display trials
	 */
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
	    <select name="trials" multiple="multiple" style="height: 12em;" onchange="javascript: update_line_trial(this.options)">
	    <?php
	    $selectedlines= $_SESSION['selected_lines'];
	    $selectedlines = implode(',', $selectedlines);
	    $sql="SELECT DISTINCT tb.experiment_uid as id, e.trial_code as name, e.experiment_year as year
	    FROM experiments as e, tht_base as tb, line_records as lr
	    WHERE
	    e.experiment_uid = tb.experiment_uid
	    AND lr.line_record_uid = tb.line_record_uid
	    AND e.experiment_type_uid = 1
	    AND lr.line_record_uid IN ($selectedlines)";
	    if (!authenticate(array(USER_TYPE_PARTICIPANT, USER_TYPE_CURATOR, USER_TYPE_ADMINISTRATOR)))
	    $sql .= " and data_public_flag > 0";
            $sql .= " ORDER BY e.experiment_year DESC, e.trial_code";
		$res = mysql_query($sql) or die(mysql_error());
                $last_year = NULL;
		while ($row = mysql_fetch_assoc($res))
		{
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
		    <option value="<?php echo $row['id'] ?>">
		     <?php echo $row['name'] ?>
		    </option>
		    <?php
		} 
	    ?>
	    </optgroup></select></table>
	    <?php
	    } 
	}
	
	/**
	 * starting with lines display phenotype items
	 */
	private function step3_lines()
	{
	    $experiments = $_GET['e'];
	    if (isset($_GET['pi'])) {
	      if (preg_match("/\d/",$phen_item)) {
	         $_SESSION['phenotype'] = $phen_item;
	       } else {
	         unset($_SESSION['phenotype']);
	       }
	    }
		?>
	    <p>3.
	    <select name="select3">
	      <option value="phenotypes">Traits</option>
	    </select></p>
	    <table id="" class="tableclass1">
	    <tr>
	    <th>Traits</th>
	    </tr>
	    <tr><td>
            <select id="traitsbx" name="traits" multiple="multiple" style="height: 12em;" onchange="javascript: update_line_pheno(this.options)">
	    <?php
		$sql = "SELECT DISTINCT p.phenotype_uid AS id, phenotypes_name AS name from phenotypes as p, tht_base as tb, phenotype_data as pd
	        where pd.tht_base_uid = tb.tht_base_uid
                AND p.phenotype_uid = pd.phenotype_uid
	        AND tb.experiment_uid in ($experiments)";
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
	     <?php 		
	}
	
	/**
	 * starting with lines display marker data
	 */
	private function step4_lines() {
	 $experiments = $_GET['e'];
	 
	$saved_session = "";
	$message2 = "";

	if (isset($_GET['pi'])) {
	  $phen_item = $_GET['pi'];
	  if (preg_match("/\d/",$phen_item)) {
	     $_SESSION['phenotype'] = $phen_item;
	  } else {
	     unset($_SESSION['phenotype']);
	  }
	}	
	if (isset($_SESSION['phenotype'])) {
	    $phenotype = $_SESSION['phenotype'];
	    $message2 = "download phenotype and genotype data";
	} else {
	    $phenotype = "";
	    $message2 = " download genotype data";
	}
	 if (isset($_SESSION['selected_lines'])) {
	     $countLines = count($_SESSION['selected_lines']);
	     $lines = $_SESSION['selected_lines'];
	     $selectedlines = implode(",", $_SESSION['selected_lines']);
	     if ($saved_session == "") {
	      $saved_session = "$countLines lines";
	     } else {
	      $saved_session = $saved_session . ", $countLines lines";
	     }
	 } else {
	     $countLines = 0;
	 }
	 if (isset($_SESSION['clicked_buttons'])) {
	    $tmp = count($_SESSION['clicked_buttons']);
	    $saved_session = $saved_session . ", $tmp markers";
	    $markers = $_SESSION['clicked_buttons']; 
	    $marker_str = implode(',',$markers);
	 } else {
	    $markers = "";
	    $marker_str = "";
	 }
	 
	 if ($saved_session != "") {
	  echo "current data selection = $saved_session<br>";
	 }
	 
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
	 
	 $this->calculate_af($lines, $min_maf, $max_missing); 
	 
	 if ($saved_session != "") {
	     if ($countLines == 0) {
	       echo "Choose one or more lines before using a saved selection. ";
	       echo "<a href=";
	       echo $config['base_url'];
	       echo "pedigree/line_selection.php> Select lines</a><br>";
	     } else {
	       echo "<br>Use existing selection to $message2<br>";
	       ?>
	       <input type="button" value="Download for Tassel V3" onclick="javascript:use_session('v3');" />
	       <input type="button" value="Download for Tassel V4" onclick="javascript:use_session('v4');"  />
	       <?php    
	     }
	  }
	}
	
	/**
	 * used by uasort() to order an array
	 * @param integer $a
	 * @param integer $b
	 * @return number
	 */
	private function cmp($a, $b) {
	  if ($a == $b) {
	    return 0;
	  }
	  return ($a < $b) ? -1 : 1;
	}
	
	/**
	 * display minor allele frequence and missing data using selected lines
	 * @param array $lines
	 * @param floats $min_maf
	 * @param floats $max_missing
	 */
	function calculate_af(&$lines, $min_maf, $max_missing) {
	 //calculate allele frequencies using 2D table
	
	 if (isset($_SESSION['clicked_buttons'])) {
	   $tmp = count($_SESSION['clicked_buttons']);
	   $saved_session = $saved_session . ", $tmp markers";
	   $markers = $_SESSION['clicked_buttons'];
	   $marker_str = implode(',',$markers);
	 } else {
	   $markers = array();
	   $marker_str = "";
	 }
	 
	 if (!preg_match('/[0-9]/',$marker_str)) {
	   //get genotype markers that correspond with the selected lines
	   $selectedlines = implode(",",$lines);
	   $sql_exp = "SELECT marker_uid
	   FROM allele_cache
	   WHERE
	   allele_cache.line_record_uid in ($selectedlines)";
	   $res = mysql_query($sql_exp) or die(mysql_error() . "<br>" . $sql_exp);
	   if (mysql_num_rows($res)>0) {
	     while ($row = mysql_fetch_array($res)){
	       $uid = $row["marker_uid"];
	       $markers[$uid] = 1;
	     }
	    }
	   $marker_str = implode(',',$markers);
	   $num_mark = mysql_num_rows($res);
	   //echo "$num_mark markers in selected lines<br>\n";
	 }
	 
	 //get location information for markers
	 $sql = "select marker_uid from allele_byline_idx order by marker_uid";
	 $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
	 $i=0;
	 while ($row = mysql_fetch_array($res)) {
	  $marker_list[$i] = $row[0];
	  $i++;
	 }
	
	 //calculate allele frequence and missing
	 foreach ($lines as $line_record_uid) {
	  $sql = "select alleles from allele_byline where line_record_uid = $line_record_uid";
	  $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
	  if ($row = mysql_fetch_array($res)) {
	    $alleles = $row[0];
	    $outarray = explode(',',$alleles);
	    $i=0;
	    foreach ($outarray as $allele) {
              if ($allele=='AA') { $marker_aacnt[$i]++; }
              elseif (($allele=='AB') or ($allele=='BA')) { $marker_abcnt[$i]++; }
              elseif ($allele=='BB') { $marker_bbcnt[$i]++; }
              elseif (($allele=='--') or ($allele=='')) { $marker_misscnt[$i]++; }
              else { echo "illegal genotype value $allele for marker $marker_list_name[$i]<br>"; }
	      $i++;
	    }
          }
	 }
	 $i=0;
	 $num_mark = 0;
	 $num_maf = $num_miss = $num_removed = 0;
	 foreach ($marker_list as $marker_uid) {
	   if (isset($markers[$marker_uid])) {
	   $total = $marker_aacnt[$i] + $marker_abcnt[$i] + $marker_bbcnt[$i] + $marker_misscnt[$i];
	   if ($total > 0) {
	     $maf = round(100 * min((2 * $marker_aacnt[$i] + $marker_abcnt[$i]) /$total, ($marker_abcnt[$i] + 2 * $marker_bbcnt[$i]) / $total),1);
	     $miss = round(100*$marker_misscnt[$i]/$total,1);
	     if ($maf >= $min_maf) $num_maf++;
	     if ($miss > $max_missing) $num_miss++;
	     if (($miss > $max_missing) OR ($maf < $min_maf)) $num_removed++;
	     $num_mark++;
	   }
	   }
	  $i++;
	 }
	 
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
	<?php
	}
	
	/**
	 * starting with location display all locations
	 */
	private function step1_locations() {
	 ?>
	 <table id="phenotypeSelTab" class="tableclass1">
	 <tr>
	 <th>Location</th>
	 </tr>
	 <tr><td>
	 <select name="lines" multiple="multiple" style="height: 12em;" onchange="javascript:update_locations(this.options)">
	 <?php
	 $sql = "SELECT distinct location as name from phenotype_experiment_info where location is not NULL order by location";
	 $res = mysql_query($sql) or die(mysql_error());
	 while ($row = mysql_fetch_assoc($res)) {
	   ?>
	   <option value="<?php echo $row['name'] ?>"><?php echo $row['name'] ?></option>
	   <?php 
	 }
	 ?>
	 </select>
	 </td>
	 </table>
	 <?php
	}
	
	/**
	 * starting with location display years
	 */
	private function step2_locations() {
	 $locations = $_GET['loc'];
	 $locations = stripslashes($locations);
	 ?>
	 <p><select>
	 <option>Year</option>
	 </select>
	 </p>
	 <table id="phenotypeSelTab" class="tableclass1">
	 <tr>
	 <th>Year</th>
	 </tr>
	 <tr><td>
	 <select name="year" multiple="multiple" style="height: 12em;" onchange="javascript:update_years(this.options)">
	 <?php
	 $sql = "SELECT e.experiment_year AS year FROM experiments AS e, experiment_types AS et, phenotype_experiment_info AS p_e
	 WHERE e.experiment_uid = p_e.experiment_uid
	 AND e.experiment_type_uid = et.experiment_type_uid
	 AND et.experiment_type_name = 'phenotype'
	 AND p_e.location IN ($locations)
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
	 </table>
	 <?php
	}
	
	/**
	 * starting with location display experiments
	 */
	private function step3_locations() {
	 $locations = $_GET['loc']; //"'" . implode("','", $_GET['loc']) . "'";
	 $years = $_GET['yrs']; //"'" . implode("','", explode(',',$_GET['yrs'])) . "'";
	 $locations = stripslashes($locations);
	 ?>
	 <p>3.
	 <select name="select1">
	 <option value="BreedingProgram">Trials</option>
	 </select></p>
	 <table id="phenotypeSelTab" class="tableclass1">
	 <tr>
	 <th>Trials</th>
	 </tr>
	 <tr><td>
	 <select name="year" multiple="multiple" style="height: 12em;" onchange="javascript: update_experiments(this.options)">
	 <?php
	 $sql = "SELECT DISTINCT e.experiment_uid AS id, e.trial_code as name, e.experiment_year AS year
	 FROM experiments AS e, experiment_types AS e_t, phenotype_experiment_info AS p_e
	 WHERE e.experiment_uid = p_e.experiment_uid
	 AND p_e.location IN ($locations)
	 AND e.experiment_year IN ($years)
	 AND e.experiment_type_uid = e_t.experiment_type_uid
	 AND e_t.experiment_type_name = 'phenotype'";
	 if (!authenticate(array(USER_TYPE_PARTICIPANT, USER_TYPE_CURATOR, USER_TYPE_ADMINISTRATOR)))
	 $sql .= " and data_public_flag > 0";
	 $sql .= " ORDER BY e.experiment_year DESC, e.trial_code";
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
	 </td>
	 </table>
	 <?php
	}
	
	/**
	 * starting with locations display marker information
	 */
	private function step5_locations() {
	 // parse url
	 $experiments = $_GET['exps'];
	 $phen_item = $_GET['pi'];
	 $subset = (isset($_GET['subset']) && !empty($_GET['subset'])) ? $_GET['subset'] : null;
     //$_SESSION['phenotype'] = $phen_item;
	 
	 /**
	  * Use currently selected lines?
	  */
	 if (count($_SESSION['selected_lines']) > 0) {
	   $sub_ckd = ""; $all_ckd = "checked";
	 } else {
	   $sub_ckd = "disabled"; $all_ckd = "checked";
	 }
	 if ($subset == "yes") {
	   $sub_ckd = "checked"; $all_ckd = "";
	 } elseif ($subset == "no") {
	   $sub_ckd = ""; $all_ckd = "checked";
	 } elseif ($subset == "comb") {
	   $sub_ckd = ""; $cmb_ckd = "checked";
	 }
	 ?>
	 <p>5.<select name="select1">
	 <option value="BreedingProgram">Lines</option>
	 </select></p>
	 
	 <table id="phenotypeSelTab" class="tableclass1">
	 <tr>
	 <th>Lines</th>
	 </tr>
	 <tr><td>
	 <select name="lines" multiple="multiple" style="height: 12em;" onchange="javascript: update_phenotype_lines(this.options)">
	 <?php
	 //if (count($_SESSION['selected_lines']) > 0) {
	 if ($sub_ckd == "checked") {
	 	$selected_lines = $_SESSION['selected_lines'];
	 	foreach ($selected_lines as $line) {
	 		$sql = "SELECT line_record_uid as id, line_record_name as name from line_records where line_record_uid = $line";
	 		$res = mysql_query($sql) or die(mysql_error());
	 		$row = mysql_fetch_assoc($res);
	 		?>
	 		<option selected value="<?php echo $row['id'] ?>">
	        <?php echo $row['name'] ?>
	        </option>
	        <?php
	 	}
	 } elseif ($cmb_ckd == "checked") {
	   $lines_list = array();
	   $lines_new = "";
	   $selected_lines = $_SESSION['selected_lines'];
	   foreach ($selected_lines as $line) {
	     $sql = "SELECT line_record_uid as id, line_record_name as name from line_records where line_record_uid = $line";
	     $res = mysql_query($sql) or die(mysql_error());
	     $row = mysql_fetch_assoc($res);
	     $temp = $row['id'];
	     $lines_list[$temp] = 1;
	     ?>
	    <option selected value="<?php echo $row['id'] ?>">
	    <?php echo $row['name'] ?>
	    </option>
	    <?php
	   }
	   $sql = "SELECT DISTINCT lr.line_record_uid as id, lr.line_record_name as name
	   FROM tht_base as tb, phenotype_data as pd, phenotypes as p, line_records as lr
	   WHERE
	   pd.tht_base_uid = tb.tht_base_uid
	   AND p.phenotype_uid = pd.phenotype_uid
	   AND lr.line_record_uid = tb.line_record_uid
	   AND pd.phenotype_uid IN ($phen_item)
	   AND tb.experiment_uid IN ($experiments)
	   ORDER BY lr.line_record_name";
	   $res = mysql_query($sql) or die(mysql_error());
	   while ($row = mysql_fetch_assoc($res))
	   {
	      $temp1 = $row['name'];
	      $temp2 = $row['id'];
	      if (isset($lines_list[$temp2])) {
	      } else {
	        if ($lines_new == "") {
	           $lines_new = $temp1;
	           ?>
	           <option disabled="disabled">--added--
	           </option>
	           <?php 
	        }
	        ?>
	        <option selected value="<?php echo $row['id'] ?>">
	        <?php echo $temp1 ?>
	        </option>
	        <?php
	      }
	   }
	 } else {
	   $sql = "SELECT DISTINCT lr.line_record_uid as id, lr.line_record_name as name
	   FROM tht_base as tb, phenotype_data as pd, phenotypes as p, line_records as lr
	   WHERE
	   pd.tht_base_uid = tb.tht_base_uid
	   AND p.phenotype_uid = pd.phenotype_uid
	   AND lr.line_record_uid = tb.line_record_uid
	   AND pd.phenotype_uid IN ($phen_item)
	   AND tb.experiment_uid IN ($experiments)
	   ORDER BY lr.line_record_name";
	   //$_SESSION['selected_lines'] = array(); // Empty the session array.
	   //$lines = array();
	   $res = mysql_query($sql) or die(mysql_error());
	   while ($row = mysql_fetch_assoc($res))
	   {
	   //array_push($_SESSION['selected_lines'],$row['id']);
	   //array_push($lines,$row['id']);
	   ?>
	   <option selected value="<?php echo $row['id'] ?>">
	   <?php echo $row['name'] ?>
	   </option>
	   <?php
	   }
	 }
	 ?>
	 </select>
	 </table>
	 <?php 
	 if (count($_SESSION['selected_lines']) > 0) {
	   ?>
	   <input type="radio" name="subset" id="subset" value="yes" <?php echo "$sub_ckd"; ?> onchange="javascript: update_phenotype_linesb(this.value)">Include only <a href="<?php echo $config['base_url']; ?>pedigree/line_selection.php">currently 
selected lines</a><br>
	   <input type="radio" name="subset" id="subset" value="no" <?php echo "$all_ckd"; ?> onchange="javascript: update_phenotype_linesb(this.value)">Use lines with selected <b>Trials</b> and <b>Traits</b><br>
	   <input type="radio" name="subset" id="subset" value="comb" <?php echo "$cmb_ckd"; ?> onchange="javascript: update_phenotype_linesb(this.value)">Combine two sets<br>
	   <?php
	 } 
	}
	
	/**
	 * starting with breeding programs display marker information
	 */
	private function step5_programs() {
	  $experiments = $_GET['exps'];
	  $CAPdataprogram = $_GET['bp'];
	  $years = $_GET['yrs'];
	  $subset = (isset($_GET['subset']) && !empty($_GET['subset'])) ? $_GET['subset'] : null;
	 
	  /** Use currently selected lines? */
	  if (count($_SESSION['selected_lines']) > 0) {
	    $sub_ckd = ""; $all_ckd = "checked";
	  } else {
	    $sub_ckd = "disabled"; $all_ckd = "checked";
	  }
	  if ($subset == "yes") {
	    $sub_ckd = "checked"; $all_ckd = "";
	  } elseif ($subset == "no") {
	    $sub_ckd = ""; $all_ckd = "checked";
	  } elseif ($subset == "comb") {
	    $sub_ckd = ""; $cmb_ckd = "checked";
	  }
	  ?>
	  <p>5.<select name="select1">
	  <option value="BreedingProgram">Lines</option>
	  </select></p>
	  
	  <table id="phenotypeSelTab" class="tableclass1">
	  <tr>
	  <th>Lines</th>
	  </tr>
	  <tr><td>
	  <select name="lines" multiple="multiple" style="height: 12em;" onchange="javascript: update_phenotype_lines(this.options)">
	  <?php
	  if ($sub_ckd == "checked") {
	    $selected_lines = $_SESSION['selected_lines'];
	    foreach ($selected_lines as $line) {
	      $sql = "SELECT line_record_uid as id, line_record_name as name from line_records where line_record_uid = $line";
	      $res = mysql_query($sql) or die(mysql_error());
	      $row = mysql_fetch_assoc($res);
	      ?>
	      <option selected value="<?php echo $row['id'] ?>">
	      <?php echo $row['name'] ?>
	      </option>
	     <?php
	    }
	  } elseif ($cmb_ckd == "checked") {
	    $lines_list = array();
	    $lines_new = "";
	    $selected_lines = $_SESSION['selected_lines'];
	    foreach ($selected_lines as $line) {
	      $sql = "SELECT line_record_uid as id, line_record_name as name from line_records where line_record_uid = $line";
	      $res = mysql_query($sql) or die(mysql_error());
	      $row = mysql_fetch_assoc($res);
	      $temp = $row['id'];
	      $lines_list[$temp] = 1;
	      ?>
	      <option selected value="<?php echo $row['id'] ?>">
	      <?php echo $row['name'] ?>
	      </option>
	      <?php
	    }
	    $sql_option = "";
	    if (preg_match("/\d/",$experiments)) {
	      $sql_option .= "AND tht_base.experiment_uid IN ($experiments)";
	    }
	    if (preg_match("/\d/",$datasets)) {
	      $sql_option .= "AND ((tht_base.datasets_experiments_uid in ($datasets) AND tht_base.check_line='no') OR (tht_base.check_line='yes'))";
	    }
	    $sql = "SELECT DISTINCT line_records.line_record_name as name, line_records.line_record_uid as id
	    FROM line_records, tht_base
	    WHERE line_records.line_record_uid=tht_base.line_record_uid
	    $sql_option";
	    $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
	    while($row = mysql_fetch_array($res)) {
	      $temp1 = $row['name'];
	      $temp2 = $row['id'];
	      if (isset($lines_list[$temp2])) {
	      } else {
	        if ($lines_new == "") {
	          $lines_new = $temp1;
	          ?>
	          <option disabled="disabled">--added--
	          </option>
	          <?php
	        }
	        ?>
	        <option selected value="<?php echo $row['id'] ?>">
	        <?php echo $row['name'] ?>
	        </option>
	        <?php
	      }
	    }
	  } else {
	    $sql_option = "";
	    if (preg_match("/\d/",$experiments)) {
	      $sql_option .= "AND tht_base.experiment_uid IN ($experiments)";
	    }
	    if (preg_match("/\d/",$datasets)) {
	      $sql_option .= "AND ((tht_base.datasets_experiments_uid in ($datasets) AND tht_base.check_line='no') OR (tht_base.check_line='yes'))";
	    }
	    $sql = "SELECT DISTINCT line_records.line_record_name as name, line_records.line_record_uid as id
	    FROM line_records, tht_base
	    WHERE line_records.line_record_uid=tht_base.line_record_uid
	    $sql_option";
	    $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
	    while($row = mysql_fetch_array($res)) {
	      ?>
	      <option selected value="<?php echo $row['id'] ?>">
	      <?php echo $row['name'] ?>
	      </option>
	      <?php 
	    }
	  }
	  ?>
	  </select>
	  </table>
	  <?php 
	  if (count($_SESSION['selected_lines']) > 0) {
	    ?>
	    <input type="radio" name="subset" id="subset" value="yes" <?php echo "$sub_ckd"; ?> onchange="javascript: update_phenotype_linesb(this.value)">Include only <a href="<?php echo $config['base_url']; ?>pedigree/line_selection.php">currently
	    selected lines</a><br>
	    <input type="radio" name="subset" id="subset" value="no" <?php echo "$all_ckd"; ?> onchange="javascript: update_phenotype_linesb(this.value)">Use lines with selected <b>Trials</b> and <b>Traits</b><br>
	    <input type="radio" name="subset" id="subset" value="comb" <?php echo "$cmb_ckd"; ?> onchange="javascript: update_phenotype_linesb(this.value)">Combine two sets<br>
	    <?php
	  }
	}

	/**
	 * allow entry of lines, this function is not used at this time
	 */
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
	
	/**
	 * display a list of experiments
	 */
	private function type1_experiments()
	{
		$CAPdata_programs = $_GET['bp']; //"'" . implode("','", explode(',',$_GET['bp'])) . "'";
		$years = $_GET['yrs']; //"'" . implode("','", explode(',',$_GET['yrs'])) . "'";
?>
<p>3. 
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
				AND e_t.experiment_type_name = 'phenotype'";
		        if (!authenticate(array(USER_TYPE_PARTICIPANT, USER_TYPE_CURATOR, USER_TYPE_ADMINISTRATOR)))
		        $sql .= " and data_public_flag > 0";
				$sql .= " ORDER BY e.experiment_year DESC, e.trial_code";
				
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
	
	/**
	 * display traits given a list of experiments
	 */
	private function type1_traits()
	{
		$experiments = $_GET['exps'];
		
		if (empty($experiments))
		{
			echo "
				4. <select><option>Traits</option></select>
				<div>
					<p><em>No Trials Selected</em></p>
				</div>";
		}
		else
		{
?>
<p>4. 
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
		<select id="traitsbx" name="traits" multiple="multiple" style="height: 12em" onchange="javascript: update_phenotype_items(this.options)">
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
	 * displays key marker data for the selected breeding programs
	 */
	function type1_markers()
	{
		// parse url
        $experiments = $_GET['exps'];
		$CAPdataprogram = $_GET['bp'];
		$subset = (isset($_GET['subset']) && !empty($_GET['subset'])) ? $_GET['subset'] : null;
		
		if (empty($_GET['lines'])) {
		if ((($subset == "yes") || ($subset == "comb")) && (count($_SESSION['selected_lines'])>0)) {
		  $lines = $_SESSION['selected_lines'];
		  $lines_str = implode(",", $lines);
		  $count = count($_SESSION['selected_lines']);
		} else {
		  $sql_option = "";
		  $lines = array();
		  if ($subset == "yes" && count($_SESSION['selected_lines']) > 0) {
		    $selectedlines = implode(",", $_SESSION['selected_lines']);
		    $sql_option = " AND line_records.line_record_uid IN ($selectedlines)";
		  }
		  if (preg_match("/\d/",$experiments)) {
		    $sql_option .= "AND tht_base.experiment_uid IN ($experiments)";
		  }
		  if (preg_match("/\d/",$datasets)) {
		    $sql_option .= "AND ((tht_base.datasets_experiments_uid in ($datasets) AND tht_base.check_line='no') OR (tht_base.check_line='yes'))";
		  }
		  $sql = "SELECT DISTINCT line_records.line_record_name, line_records.line_record_uid FROM line_records, tht_base
		  WHERE line_records.line_record_uid=tht_base.line_record_uid $sql_option";
		  $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
		  while($row = mysql_fetch_array($res)) {
		    $lines[] = $row['line_record_uid'];
		  }
		  $lines_str = implode(",", $lines);
		  $count = count($lines);
		}
		//overide these setting is radio button checked
		if ($subset == "no") {
		  $sql_option = "";
		  $lines = array();
		  if (preg_match("/\d/",$experiments)) {
		    $sql_option .= "AND tht_base.experiment_uid IN ($experiments)";
		  }
		  if (preg_match("/\d/",$datasets)) {
		    $sql_option .= "AND ((tht_base.datasets_experiments_uid in ($datasets) AND tht_base.check_line='no') OR (tht_base.check_line='yes'))";
		  }
		  $sql = "SELECT DISTINCT line_records.line_record_name, line_records.line_record_uid FROM line_records, tht_base
		  WHERE line_records.line_record_uid=tht_base.line_record_uid $sql_option";
		  $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
		  while($row = mysql_fetch_array($res)) {
		    $lines[] = $row['line_record_uid'];
		  }
		  $lines_str = implode(",", $lines);
		  $count = count($lines);
		} elseif ($subset == "comb") {
		  if (preg_match("/\d/",$experiments)) {
		    $sql_option .= "AND tht_base.experiment_uid IN ($experiments)";
		  }
		  if (preg_match("/\d/",$datasets)) {
		    $sql_option .= "AND ((tht_base.datasets_experiments_uid in ($datasets) AND tht_base.check_line='no') OR (tht_base.check_line='yes'))";
		  }
		  $sql = "SELECT DISTINCT line_records.line_record_name, line_records.line_record_uid FROM line_records, tht_base
		  WHERE line_records.line_record_uid=tht_base.line_record_uid $sql_option";
		  $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
		  while($row = mysql_fetch_array($res)) {
		    $lines[] = $row['line_record_uid'];
		  }
		  $lines_str = implode(",", $lines);
		  $count = count($lines);
		}
		} else {
	      $lines_str = $_GET['lines'];
	      $lines = explode(',', $lines_str);
	      $count = count($lines);
		}
		echo "current data selection = $count lines<br>";
		?>
        <h3>6. Markers</h3>
		<div>
		<?php
		    		
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
		
		$this->calculate_af($lines, $min_maf, $max_missing);
		
		?>
		<table> <tr> <td COLSPAN="3">
		<input type="hidden" name="subset" id="subset" value="yes" /><br>
		<input type="button" value="Download for Tassel V3" onclick="javascript:getdownload_tassel('v3');" />
		<h4> or </h4>
		<input type="button" value="Download for Tassel V4" onclick="javascript:getdownload_tassel('v4');" /> <br>
		</td> </tr> </table>
		<?php
		
		?></div><?php
			
	}
	
	/**
	 * displays key marker data when given a set of experiments and phenotypes
	 */
	function type2_markers()
	{
	 // parse url
	 $experiments = $_GET['exps'];
	 $phen_item = $_GET['pi'];
	 $subset = (isset($_GET['subset']) && !empty($_GET['subset'])) ? $_GET['subset'] : null;
	 
	 if (empty($_GET['lines'])) {
	   if ((($subset == "yes") || ($subset == "comb")) && (count($_SESSION['selected_lines'])>0)) {
	   	 $lines = $_SESSION['selected_lines'];
	     $lines_str = implode(",", $lines);
	     $count = count($_SESSION['selected_lines']);
	   } else {
	   	 $sql = "SELECT DISTINCT lr.line_record_uid as id, lr.line_record_name as name
	   	 FROM tht_base as tb, phenotype_data as pd, phenotypes as p, line_records as lr
	   	 WHERE
	   	 pd.tht_base_uid = tb.tht_base_uid
	   	 AND p.phenotype_uid = pd.phenotype_uid
	   	 AND lr.line_record_uid = tb.line_record_uid
	   	 AND pd.phenotype_uid IN ($phen_item)
	   	 AND tb.experiment_uid IN ($experiments)
	   	 ORDER BY lr.line_record_name";
	   	 $lines = array();
	   	 $res = mysql_query($sql) or die(mysql_error() . $sql);
	   	 while ($row = mysql_fetch_assoc($res))
	   	 {
	   		array_push($lines,$row['id']);
	     }
	     $lines_str = implode(",", $lines);
	     $count = count($lines);
	     //$_SESSION['selected_lines'] = $lines;
	   }
	   //overide these setting is radio button checked
	   if ($subset == "no") {
	   	 $sql = "SELECT DISTINCT lr.line_record_uid as id, lr.line_record_name as name
	   	 FROM tht_base as tb, phenotype_data as pd, phenotypes as p, line_records as lr
	   	 WHERE
	   	 pd.tht_base_uid = tb.tht_base_uid
	   	 AND p.phenotype_uid = pd.phenotype_uid
	   	 AND lr.line_record_uid = tb.line_record_uid
	   	 AND pd.phenotype_uid IN ($phen_item)
	   	 AND tb.experiment_uid IN ($experiments)
	   	 ORDER BY lr.line_record_name";
	   	 $lines = array();
	   	 $res = mysql_query($sql) or die(mysql_error() . $sql);
	   	 while ($row = mysql_fetch_assoc($res))
	   	 {
	   	 	array_push($lines,$row['id']);
	   	 }
	   	 $lines_str = implode(",", $lines);
	   	 $count = count($lines);
	   } elseif ($subset == "comb") {
	     $sql = "SELECT DISTINCT lr.line_record_uid as id, lr.line_record_name as name
	     FROM tht_base as tb, phenotype_data as pd, phenotypes as p, line_records as lr
	     WHERE
	     pd.tht_base_uid = tb.tht_base_uid
	     AND p.phenotype_uid = pd.phenotype_uid
	     AND lr.line_record_uid = tb.line_record_uid
	     AND pd.phenotype_uid IN ($phen_item)
	     AND tb.experiment_uid IN ($experiments)
	     ORDER BY lr.line_record_name";
	     $res = mysql_query($sql) or die(mysql_error() . $sql);
	     while ($row = mysql_fetch_assoc($res))
	     {
	       array_push($lines,$row['id']);
	     }
	     $lines_str = implode(",", $lines);
	     $count = count($lines);
	   }
	 } else {
	   $lines_str = $_GET['lines'];
	   $lines = explode(',', $lines_str);
	   $count = count($lines);
	   //$_SESSION['selected_lines'] = $lines;
	 }
	 echo "current data selection = $count lines<br>";
	
	 ?>
	
	 <h3>6. Markers</h3>
	 <div>
	 <?php
	
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
	
	$this->calculate_af($lines, $min_maf, $max_missing);
	
	?>
	<table> <tr> <td COLSPAN="3">
	<input type="hidden" name="subset" id="subset" value="yes" /><br>
	<input type="button" value="Download for Tassel V3" onclick="javascript:get_download_loc('v3');" /> <br>
	<h4> or </h4>
	<input type="button" value="Download for Tassel V4" onclick="javascript:get_download_loc('v4');" /> <br>
	</td> </tr> </table>
	<?php
	
	?></div><?php
	
	}
	
	/**
	 * creates output files in qtlminer format
	 */
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
	
	/**
	 * build download files for tassel V2
	 */
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
		$zip->newFile("allele_conflict.txt");
		$zip->writeData($this->type1_build_conflicts_download($experiments_g, $dtype));
		$zip->newFile("annotated_alignment.txt");
		$zip->writeData($this->type1_build_annotated_align($experiments_g));
		// $firephp->log("after alignment marker file".$experiments_g);

		$zip->close();
		
		header("Location: ".$dir.$filename);
	}

	/**
	 * build download files for tassel V3
	 */
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
		$zip->newFile("allele_conflict.txt");
		$zip->writeData($this->type1_build_conflicts_download($experiments_g, $dtype));
		$zip->newFile("geneticMap.txt");
		$zip->writeData($this->type1_build_geneticMap($experiments_g));
		// $firephp->log("after alignment marker file".$experiments_g);

		$zip->close();
		
		header("Location: ".$dir.$filename);
	}
	
	/**
	 * build download files for tassel (V2,V3,V4) when given a set of experiments, traits, and phenotypes
	 * @param string $version
	 */
	function type2_build_tassel($version) {
	  //used for download starting with location
	  $experiments = (isset($_GET['e']) && !empty($_GET['e'])) ? $_GET['e'] : null;
	  $traits = (isset($_GET['t']) && !empty($_GET['t'])) ? $_GET['t'] : null;
	  $subset = (isset($_GET['subset']) && !empty($_GET['subset'])) ? $_GET['subset'] : null;
	  $phen_item = (isset($_GET['pi']) && !empty($_GET['pi'])) ? $_GET['pi'] : null;
	 
	  $dtype = "tassel";
	  if (empty($_GET['lines'])) {
	    if ((($subset == "yes") || ($subset == "comb")) && count($_SESSION['selected_lines'])>0) {
	      $lines = $_SESSION['selected_lines'];
	      $lines_str = implode(",", $lines);
	      $count = count($_SESSION['selected_lines']);
	    } else {
	      $sql = "SELECT DISTINCT lr.line_record_uid as id, lr.line_record_name as name
	      FROM tht_base as tb, phenotype_data as pd, phenotypes as p, line_records as lr
	      WHERE
	      pd.tht_base_uid = tb.tht_base_uid
	      AND p.phenotype_uid = pd.phenotype_uid
	      AND lr.line_record_uid = tb.line_record_uid
	      AND pd.phenotype_uid IN ($phen_item)
	      AND tb.experiment_uid IN ($experiments)
	      ORDER BY lr.line_record_name";
	      $lines = array();
	      $res = mysql_query($sql) or die(mysql_error() . $sql);
	      while ($row = mysql_fetch_assoc($res))
	      {
	        array_push($lines,$row['id']);
	      }
	      $lines_str = implode(",", $lines);
	      $count = count($lines);
	    }
	    //overide these setting is radio button checked
	    if ($subset == "no") {
	      $sql = "SELECT DISTINCT lr.line_record_uid as id, lr.line_record_name as name
	      FROM tht_base as tb, phenotype_data as pd, phenotypes as p, line_records as lr
	      WHERE
	      pd.tht_base_uid = tb.tht_base_uid
	      AND p.phenotype_uid = pd.phenotype_uid
	      AND lr.line_record_uid = tb.line_record_uid
	      AND pd.phenotype_uid IN ($phen_item)
	      AND tb.experiment_uid IN ($experiments)
	      ORDER BY lr.line_record_name";
	      $lines = array();
	      $res = mysql_query($sql) or die(mysql_error() . $sql);
	      while ($row = mysql_fetch_assoc($res))
	      {
	        array_push($lines,$row['id']);
	      }
	      $lines_str = implode(",", $lines);
	      $count = count($lines);
	    }
	    if ($subset == "comb") {
	      $sql = "SELECT DISTINCT lr.line_record_uid as id, lr.line_record_name as name
	      FROM tht_base as tb, phenotype_data as pd, phenotypes as p, line_records as lr
	      WHERE
	      pd.tht_base_uid = tb.tht_base_uid
	      AND p.phenotype_uid = pd.phenotype_uid
	      AND lr.line_record_uid = tb.line_record_uid
	      AND pd.phenotype_uid IN ($phen_item)
	      AND tb.experiment_uid IN ($experiments)
	      ORDER BY lr.line_record_name";
	      $res = mysql_query($sql) or die(mysql_error() . $sql);
	      while ($row = mysql_fetch_assoc($res))
	      {
	        array_push($lines,$row['id']);
	      }
	      $lines_str = implode(",", $lines);
	    }
	  } else {
	    $lines_str = $_GET['lines'];
	    $lines = explode(',', $lines_str);
	  }
	  
	  if (!preg_match('/[0-9]/',$marker_str)) {
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
	  } else {
	    $markers = array();
	  }
	  
	  //get genotype experiments
	  $sql_exp = "SELECT DISTINCT e.experiment_uid AS exp_uid
	  FROM experiments e, experiment_types as et, line_records as lr, tht_base as tb
	  WHERE
	  e.experiment_type_uid = et.experiment_type_uid
	  AND lr.line_record_uid = tb.line_record_uid
	  AND e.experiment_uid = tb.experiment_uid
	  AND lr.line_record_uid in ($lines_str)
	  AND et.experiment_type_name = 'genotype'";
	  $res = mysql_query($sql_exp) or die(mysql_error() . "<br>" . $sql_exp);
	  if (mysql_num_rows($res)>0) {
	   while ($row = mysql_fetch_array($res)){
	    $exp[] = $row["exp_uid"];
	   }
	   $experiments_g = implode(',',$exp);
	  }
	  
	  $dir = '/tmp/tht/';
	  $filename = 'THTdownload_tassel_'.chr(rand(65,80)).chr(rand(65,80)).chr(rand(64,80)).'.zip';
	  
	  // File_Archive doesn't do a good job of creating files, so we'll create it first
	  if(!file_exists($dir.$filename)){
	    $h = fopen($dir.$filename, "w+");
	    fclose($h);
	  }
	  $zip = File_Archive::toArchive($dir.$filename, File_Archive::toFiles());
	  
	  if (($version == "V2") || ($version == "V3") || ($version == "V4")) {
	    $zip->newFile("traits.txt");
	    $zip->writeData($this->type2_build_tassel_traits_download($experiments,$phen_item,$lines,$subset));
	  }
	  if (($version == "V2")) {
	    $zip->newFile("annotated_alignment.txt");
	    $zip->writeData($this->type1_build_annotated_align($experiments_g));
	  } elseif (($version == "V3")) {
	    $zip->newFile("geneticMap.txt");
	    $zip->writeData($this->type1_build_geneticMap($experiments_g));
	    $zip->newFile("snpfile.txt");
	    $zip->writeData($this->type2_build_markers_download($lines,$markers,$dtype));
	  } elseif (($version == "V4")) {
	    $zip->newFile("genotype_hapmap.txt");
	    $zip->writeData($this->type3_build_markers_download($lines,$markers,$dtype));
	  }
	  $zip->newFile("allele_conflict.txt");
	  $zip->writeData($this->type2_build_conflicts_download($lines,$markers));
	  $zip->close();
	  
	  header("Location: ".$dir.$filename);
	}

	/**
	 * generate download files in qltminer format
	 * @param unknown_type $experiments
	 * @param unknown_type $traits
	 * @param unknown_type $datasets
	 */
	function type1_build_traits_download($experiments, $traits, $datasets)
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

    /**
     * Build trait download file for Tassel program interface
     * @param unknown_type $experiments
     * @param unknown_type $traits
     * @param unknown_type $datasets
     * @param unknown_type $subset
     * @return string
     */
    function type1_build_tassel_traits_download($experiments, $traits, $datasets, $subset)
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
      if ($experiments=="") {
        $sql_option = "";
      } else {
        $sql_option = "AND tb.experiment_uid IN ($experiments)";
      }
      $selectedlines = implode(",", $_SESSION['selected_lines']);
      if (count($_SESSION['selected_lines']) > 0) {
         $sql_option = $sql_option . " AND tb.line_record_uid IN ($selectedlines)";
      }
      $sql = "SELECT DISTINCT e.trial_code, e.experiment_uid, p.phenotypes_name,p.phenotype_uid
               FROM experiments as e, tht_base as tb, phenotype_data as pd, phenotypes as p
               WHERE 
                  e.experiment_uid = tb.experiment_uid
                  $sql_option
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
      $nexp=$ncols;
		//$firephp->log("trait_location information ".$outputheader2."  ".$outputheader3);
		// $firephp->table('keys label ', $keys); 

		// dem 5jan11: If $subset="yes", use $_SESSION['selected_lines'].
		$sql_option = "";
		if ($subset == "yes" && count($_SESSION['selected_lines']) > 0) {
		  $selectedlines = implode(",", $_SESSION['selected_lines']);
		  $sql_option = " AND line_records.line_record_uid IN ($selectedlines)";
		} 
		if (preg_match("/\d/",$experiments)) {
		  $sql_option .= "AND tht_base.experiment_uid IN ($experiments)";
		}
		if (preg_match("/\d/",$datasets)) {
		  $sql_option .= "AND ((tht_base.datasets_experiments_uid in ($datasets) AND tht_base.check_line='no') OR (tht_base.check_line='yes'))";
		}
			
          // get a list of all line names in the selected datasets and experiments,
	  // INCLUDING the check lines // AND tht_base.check_line IN ('no')
      $sql = "SELECT DISTINCT line_records.line_record_name, line_records.line_record_uid
               FROM line_records, tht_base
	       WHERE line_records.line_record_uid=tht_base.line_record_uid
                 $sql_option";
      $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
      while($row = mysql_fetch_array($res)) {
         $lines[] = $row['line_record_name'];
         $line_uid[] = $row['line_record_uid'];
      }
      $nlines = count($lines);
      //die($sql . "<br>" . $nlines);

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
			if (preg_match("/\d/",$experiments)) {
			  $sql_option = " WHERE tb.experiment_uid IN ($experiments) AND ";
			} else {
			  $sql_option = " WHERE ";
			}
			$sql = "SELECT pd.value as value,pd.phenotype_uid,tb.experiment_uid 
					FROM tht_base as tb, phenotype_data as pd
					$sql_option
						tb.line_record_uid  = $line_uid[$i]
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

	/**
	 * Build trait download file for Tassel program interface
	 * @param string $experiments
	 * @param unknown_type $traits
	 * @param unknown_type $lines
	 * @param unknown_type $subset
	 * @return string
	 */
	function type2_build_tassel_traits_download($experiments, $traits, $lines, $subset)
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
	
	 // figure out which traits are at which location
	 if ($experiments=="") {
	   $sql_option = "";
	 } else {
	   $sql_option = "AND tb.experiment_uid IN ($experiments)";
	 }

	 $selectedlines = implode(",", $lines);
	 $sql_option = $sql_option . " AND tb.line_record_uid IN ($selectedlines)";
	 $sql = "SELECT DISTINCT e.trial_code, e.experiment_uid, p.phenotypes_name,p.phenotype_uid
	 FROM experiments as e, tht_base as tb, phenotype_data as pd, phenotypes as p
	 WHERE
	 e.experiment_uid = tb.experiment_uid
	 $sql_option
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
	 $nexp=$ncols;
	 
	 $sql = "SELECT DISTINCT line_records.line_record_name, line_records.line_record_uid
	 FROM line_records
	 where line_record_uid IN ($selectedlines)";
	 $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
	 while($row = mysql_fetch_array($res)) {
	   $lines_names[] = $row['line_record_name'];
	   $line_uid[] = $row['line_record_uid'];
	}
	$nlines = count($lines);
	//die($sql . "<br>" . $nlines);
	
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
	 $outline = $lines_names[$i].$delimiter;
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
	 if (preg_match("/\d/",$experiments)) {
	 $sql_option = " WHERE tb.experiment_uid IN ($experiments) AND ";
	 } else {
	 $sql_option = " WHERE ";
	 }
	 $sql = "SELECT pd.value as value,pd.phenotype_uid,tb.experiment_uid
	 FROM tht_base as tb, phenotype_data as pd
	 $sql_option
	 tb.line_record_uid  = $line_uid[$i]
	 AND pd.tht_base_uid = tb.tht_base_uid
	 AND pd.phenotype_uid IN ($traits)
	 GROUP BY tb.tht_base_uid, pd.phenotype_uid";
	 //echo "$i $nlines $sql <br>";
	 $res = mysql_query($sql) or die(mysql_error() . "<br>$i $sql");
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
	
	/**
	 * build genotype data file for tassle V2 and V3
	 * @param unknown_type $experiments
	 * @param unknown_type $dtype
	 */
	function type1_build_markers_download($experiments,$dtype)
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
		  ORDER BY a.line_record_uid, a.marker_uid";


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
	
	/**
	 * build file listing conflicts in genotype data
	 * @param unknown_type $experiments
	 * @param unknown_type $dtype
	 */
	function type1_build_conflicts_download($experiments,$dtype) {
	 
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
	      $marker_uid[] = $row["marker"];
	    }
	  }
	  $marker_uid = implode(",",$marker_uid);
	  $output = "line name\tmarker name\talleles\texperiment\n";
	  $query = "select l.line_record_name, m.marker_name, a.alleles, e.trial_code
	  from allele_conflicts a, line_records l, markers m, experiments e
	  where a.line_record_uid = l.line_record_uid
	  and a.marker_uid = m.marker_uid
	  and a.experiment_uid = e.experiment_uid
	  and a.alleles != '--'
	  and a.marker_uid IN ($marker_uid)
	  order by l.line_record_name, m.marker_name, e.trial_code";
	  $res = mysql_query($query) or die(mysql_error() . "<br>" . $sql_exp);
	  if (mysql_num_rows($res)>0) {
	   while ($row = mysql_fetch_row($res)){
	    $output.= "$row[0]\t$row[1]\t$row[2]\t$row[3]\n";
	   }
	  }
	  return $output;
	}
	
	/**
	 * build genotype data file when given set of lines and markers
	 * @param unknown_type $lines
	 * @param unknown_type $markers
	 * @param unknown_type $dtype
	 */
	function type2_build_markers_download($lines,$markers,$dtype)
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
		
		if (count($markers)>0) {
		  $markers_str = implode(",", $markers);
		} else {
		  $markers_str = "";
		}
		if (count($lines)>0) {
		  $lines_str = implode(",", $lines);
		} else {
		  $lines_str = "";
		}
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
		      if ($allele=='AA') { $marker_aacnt[$i]++; }
		      elseif (($allele=='AB') or ($allele=='BA')) { $marker_abcnt[$i]++; }
		      elseif ($allele=='BB') { $marker_bbcnt[$i]++; }
		      elseif (($allele=='--') or ($allele=='')) { $marker_misscnt[$i]++; }
                      else { echo "illegal genotype value $allele for marker $marker_list_name[$i]<br>"; }
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
				$outputheader .= $marker_name.$delimiter;
				$marker_uid[] = $marker_id;
				//echo "accept $marker_id $marker_name $maf[$i] $miss[$i]<br>\n";
		    } else {
		      //echo "reject $marker_id $marker_name $maf $miss<br>\n";
		    }
		  } else {
		    //echo "rejected marker $marker_id<br>\n";
		  }
		}
		
		if ($dtype=='qtlminer') {
		 $lookup = array(
		   'AA' => '1',
		   'BB' => '-1',
		   '--' => 'NA',
		   'AB' => '0',
		   '' => 'NA'
		 );
		} else {
		 $lookup = array(
		   'AA' => '1:1',
		   'BB' => '2:2',
		   '--' => '?',
		   'AB' => '1:2',
		   '' => '?'
		 );
		}
		
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
		
		if ($dtype =='qtlminer')  {
			return $outputheader."\n".$output;
		} else {
			return $num_lines.$delimiter.$nelem.":2\n".$outputheader."\n".$output;
		}
	}
  
	/**
	 * build genotype data files for tassel V4
	 * @param unknown_type $lines
	 * @param unknown_type $markers
	 * @param unknown_type $dtype
	 */
	function type3_build_markers_download($lines,$markers,$dtype)
	{
	 $output = '';
	 $outputheader = '';
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
	
	 if (count($markers)>0) {
	  $markers_str = implode(",", $markers);
	 } else {
	  $markers_str = "";
	 }
	 if (count($lines)>0) {
	  $lines_str = implode(",", $lines);
	 } else {
	  $lines_str = "";
	 }
	 
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
	 
	 //order the markers by map location
	 $sql = "select markers.marker_uid,  mim.chromosome, mim.start_position from markers, markers_in_maps as mim, map, mapset
	 where markers.marker_uid IN ($markers_str)
	 AND mim.marker_uid = markers.marker_uid
	 AND mim.map_uid = map.map_uid
	 AND map.mapset_uid = mapset.mapset_uid
	 AND mapset.mapset_uid = 1
	 order by mim.chromosome, mim.start_position";
	 $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
	 while ($row = mysql_fetch_array($res)) {
           $marker_uid = $row[0];
           $chr = $row[1];
           $pos = $row[2];
           if (preg_match("/(\d+)/",$chr,$match)) {
             $chr = $match[0];
             $rank = (1000*$chr) + $pos;
           } else {
             $rank = 99999;
           }
	   $marker_list_mapped[$marker_uid] = $rank;
	 }
	
	 //generate an array of selected markers and add map position if available
         $sql = "select marker_uid, marker_name, A_allele, B_allele from markers
         where marker_uid IN ($markers_str)";
         $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
         while ($row = mysql_fetch_array($res)) {
           $marker_uid = $row[0];
           $marker_name = $row[1];
           if (isset($marker_list_mapped[$marker_uid])) {
             $marker_list_all[$marker_uid] = $marker_list_mapped[$marker_uid];
           } else {
             $marker_list_all[$marker_uid] = 0;
           }
           if (preg_match("/[A-Z]/",$row[2]) && preg_match("/[A-Z]/",$row[3])) {
                $allele = $row[2] . "/" . $row[3];
           } else {
                $allele = "N/N";
           }
           $marker_list_name[$marker_uid] = $marker_name;
           $marker_list_allele[$marker_uid] = $allele;
         }

         //sort marker_list_all by map location if available
         if (uasort($marker_list_all, array($this,'cmp'))) {
         } else {
           die("could not sort marker list\n");
         }

	 $lookup = array(
	   'AA' => 'AA',
	   'BB' => 'CC',
	   '--' => 'NN',
	   'AB' => 'AC',
	   '' => 'NN'
	 );
	 
	 foreach ($lines as $i => $line_record_uid) {
	  $sql = "select alleles from allele_byline where line_record_uid = $line_record_uid";
	  $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
	  if ($row = mysql_fetch_array($res)) {
	   $alleles = $row[0];
	   $outarray = explode(',',$alleles);
	   $i = 0;
	   foreach ($outarray as $allele) {
            if ($allele=='AA') { $marker_aacnt[$i]++; }
            elseif (($allele=='AB') or ($allele=='BA')) { $marker_abcnt[$i]++; }
            elseif ($allele=='BB') { $marker_bbcnt[$i]++; }
            elseif (($allele=='--') or ($allele=='')) { $marker_misscnt[$i]++; }
            else { echo "illegal genotype value $allele for marker $marker_list_name[$i]<br>"; }
	    $i++;
	   }
	  }
	 }
	 
	 //get location in allele_byline for each marker
	 $sql = "select marker_uid, marker_name from allele_byline_idx order by marker_uid";
	 $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
	 $i=0;
	 while ($row = mysql_fetch_array($res)) {
	   $marker_idx_list[$row[0]] = $i;
	   $i++;
	 }
	 
	 //get header
	 $empty = array();
	 $outputheader = "rs#\talleles\tchrom\tpos\tstrand\tassembly#\tcenter\tprotLSID\tassayLSID\tpanelLSID\tQCcode";
	 $sql = "select line_record_name from line_records where line_record_uid IN ($lines_str)";
	 $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
	 while ($row = mysql_fetch_array($res)) {
	  $name = $row[0];
	  $outputheader .= "\t$name";
	  $empty[$name] = "NN";
	 }
	 
	 $lookup_chrom = array(
	   '1H' => '1','2H' => '2','3H' => '3','4H' => '4','5H' => '5',
	   '6H' => '6','7H' => '7','UNK'  => '10');
	
	 //using a subset of markers so we have to translate into correct index
	 foreach ($marker_list_all as $marker_id => $rank) {
	  $marker_idx = $marker_idx_list[$marker_id];
          $marker_name = $marker_list_name[$marker_id];
          $allele = $marker_list_allele[$marker_id];
	   $total = $marker_aacnt[$marker_idx] + $marker_abcnt[$marker_idx] + $marker_bbcnt[$marker_idx] + $marker_misscnt[$marker_idx];
	   if ($total>0) {
	    $maf[$marker_idx] = round(100 * min((2 * $marker_aacnt[$marker_idx] + $marker_abcnt[$marker_idx]) /$total, ($marker_abcnt[$marker_idx] + 2 * $marker_bbcnt[$marker_idx]) / $total),1);
	    $miss[$marker_idx] = round(100*$marker_misscnt[$marker_idx]/$total,1);
	   } else {
	    $maf[$marker_idx] = 0;
	    $miss[$marker_idx] = 100;
	   }
	   if (($maf[$marker_idx] >= $min_maf) AND ($miss[$marker_idx]<=$max_missing)) {
	     $sql = "select A_allele, B_allele, mim.chromosome, mim.start_position from markers, markers_in_maps as mim, map, mapset where markers.marker_uid = $marker_id
	         AND mim.marker_uid = markers.marker_uid
	         AND mim.map_uid = map.map_uid
	         AND map.mapset_uid = mapset.mapset_uid
	         AND mapset.mapset_uid = 1";
	     $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
	     if ($row = mysql_fetch_array($res)) {
	        $chrom = $lookup_chrom[$row[2]];
	        $pos = 100 * $row[3];
	     } else {
	        $chrom = 0;
	        $pos = 0;
	     }
	     $output .= "$marker_name\t$allele\t$chrom\t$pos\t\t\t\t\t\t\t";
	     $allele_list = $empty;
	     $sql = "select line_record_name, alleles from allele_cache where marker_uid = $marker_id and line_record_uid IN ($lines_str)";
	     $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
	     while ($row = mysql_fetch_array($res)) {
	       if (preg_match("/[A-Z]/",$row[1])) {
	         $allele = $row[1];
	         $allele = $lookup[$allele];
	       } else {
	         $allele = "NN";
	       }
	       $allele_list[$row[0]] = $allele;
	     }
	     $allele_str = implode("\t",$allele_list);
	     $output .= "\t$allele_str\n";
	  } else {
	   //echo "rejected marker $marker_id<br>\n";
	  }
	 }
	 return $outputheader."\n".$output;
	}
	
	/**
	 * build genotype conflicts file when given set of lines and markers
	 * @param unknown_type $lines
	 * @param unknown_type $markers
	 * @return string
	 */
	function type2_build_conflicts_download($lines,$markers) {
	 
	  if (count($markers)>0) {
	    $markers_str = implode(",",$markers);
	  } else {
	    $markers_str = "";
	  }
	  if (count($lines)>0) {
	    $lines_str = implode(",",$lines);
	  } else {
	    $lines_str = "";
	  }
	  //get lines and filter to get a list of markers which meet the criteria selected by the user
	  if (preg_match('/[0-9]/',$markers_str)) {
	  } else {
	  //get genotype markers that correspond with the selected lines
	    $sql_exp = "SELECT DISTINCT marker_uid FROM allele_cache
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
	  $output = "line name\tmarker name\talleles\texperiment\n";
	  $query = "select l.line_record_name, m.marker_name, a.alleles, e.trial_code
	  from allele_conflicts a, line_records l, markers m, experiments e
	  where a.line_record_uid = l.line_record_uid
	  and a.marker_uid = m.marker_uid
	  and a.experiment_uid = e.experiment_uid
	  and a.alleles != '--'
	  and a.line_record_uid IN ($lines_str)
	  and a.marker_uid IN ($markers_str)
	  order by l.line_record_name, m.marker_name, e.trial_code";
	  $res = mysql_query($query) or die(mysql_error() . "<br>" . $sql_exp);
	  if (mysql_num_rows($res)>0) {
	    while ($row = mysql_fetch_row($res)){
	      $output.= "$row[0]\t$row[1]\t$row[2]\t$row[3]\n";
	    }
	  }
	  return $output;
	}

	/**
	 * create map file in Tassel V2 format
	 * @param string $experiments
	 * @return string
	 */
	function type1_build_annotated_align($experiments)
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

	/**
	 * create map file for tassel V3
	 * @param string $experiments
	 * @return string
	 */
	function type1_build_geneticMap($experiments)
	{
		$delimiter ="\t";
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
		
		$lookup_chrom = array(
		  '1H' => '1','2H' => '2','3H' => '3','4H' => '4','5H' => '5',
		  '6H' => '6','7H' => '7','UNK'  => '10'
		);

		$sql = "select markers.marker_uid,  mim.chromosome, mim.start_position from markers, markers_in_maps as mim, map, mapset
		where mim.marker_uid = markers.marker_uid
		AND mim.map_uid = map.map_uid
		AND map.mapset_uid = mapset.mapset_uid
		AND mapset.mapset_uid = 1";
		$res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
		while ($row = mysql_fetch_array($res)) {
		  $uid = $row[0];
		  $chr = $lookup_chrom[$row[1]];
		  $pos = $row[2];
		  $marker_list_mapped[$uid] = "$chr\t$pos";
		  if (preg_match("/(\d+)/",$chr,$match)) {
		    $chr = $match[0];
		    $rank = (1000*$chr) + $pos;
		  } else {
		    $rank = 99999;
		  }  
		  $marker_list_rank[$uid] = $rank; 
		}
		
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
						$marker_uid[] = $row["marker"];
						$uid = $row["marker"];
						if (isset($marker_list_mapped[$uid])) {
						  $marker_list_name[$uid] = $row["name"];
						  $marker_list_all[$uid] = $marker_list_rank[$uid];
						}
					}
		}
		if (count($marker_list_all) == 0) {
		   $output = "no mapped data found";
		   return $output;
		}
		
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
		$line_str = implode($delimiter,$line_names);
		// $firephp = log($nelem." ".$n_lines);
			
		// write output file header
		$outputheader = "<Map>\n";
	    // $firephp = log($outputheader);

		// get marker map data, line and marker names; use latest consensus map
		// as the map default
        $mapset = 1;	

        //sort marker_list by map location
        if (uasort($marker_list_all, array($this,'cmp'))) {
        } else {
          die("could not sort marker list\n");
        }
        
		$num_markers = 0;
		/* foreach( $marker_uid as $cnt => $uid) { */
		foreach($marker_list_all as $uid=>$value) {
		    $marker_name = $marker_list_name[$uid];
		    $map_loc = $marker_list_mapped[$uid];
		    $output .= "$marker_name\t$map_loc\n";
		    $num_markers++;
		}
		
	  return $outputheader.$output;
    }

    /**
     * create pedigree output file for qtlminer
     * @param string $experiments
     */
	function type1_build_pedigree_download($experiments)
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
	
	/**
	 * create output file for qtlminer
	 * @param string $experiments
	 * @return string
	 */
	function type1_build_inbred_download($experiments)
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
