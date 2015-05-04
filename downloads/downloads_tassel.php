<?php
/**
 * Download Gateway New
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
$mysqli = connecti();

require_once $config['root_dir'].'downloads/marker_filter.php';

new DownloadsJNLP($_GET['function']);

/** creaate temporary files use by TASSEL and JNLP
 * 
 * @category PHP
 * @package  T3
 * @author   Clay Birkett <claybirkett@gmail.com>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/downloads/downloads.php
 **/
class DownloadsJNLP
{
    /**
     * delimiter used for output files
     */
    public $delimiter = "\t";
    
    /** 
     * Using the class's constructor to decide which action to perform
     *
     * @param string $function action to perform
     */
    public function __construct($function = null)
    {	
        switch($function)
        {
        case 'step1lines':
            $this->step1_lines();
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
        case 'step5lines':
             $this->step5_lines();
             break;
        case 'step1yearprog':
             $this->step1_yearprog();
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
		<script type="text/javascript" src="downloads/downloads_tassel.js"></script>
         <?php 
                if (empty($_SESSION['selected_lines'])) {
                    echo "Please select lines before using this feature.<br><br>";
                    echo "<a href=";
                    echo $config['base_url'];
                    echo "pedigree/line_properties.php>Select Lines by Properties</a><br><br>";
                    echo "<a href=";
                    echo $config['base_url'];
                    echo "downloads/select_all.php>Wizard (Lines, Traits, Trials)</a>";
                    echo "</div>";
                } elseif (empty($_SESSION['selected_map'])) {
                    echo "Select map before using this feature.<br><br>";
                    echo "<a href=\"" . $config['base_url'];
                    echo "maps/select_map.php\">Genetic Map</a><br><br></div>";
                } else {
                    $this->type1_lines_trial_trait();
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
      <h2>Open Tassel with selected data</h2>
      <p> 
      <img alt="spinner" id="spinner" src="images/ajax-loader.gif" style="display:none;" />
      <?php 
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
	    $datasets_exp = "";
            $subset = "yes";
                if (isset($_SESSION['selected_trials'])) {
                        $experiments_t = $_SESSION['selected_trials'];
                        $experiments_t = implode(",",$experiments_t);
                } else {
                        $experiments_t = "";
                }
		if (isset($_SESSION['selected_lines'])) {
			$selectedcount = count($_SESSION['selected_lines']);
			$lines = $_SESSION['selected_lines'];
			$lines_str = implode(",", $lines);
		} else {
			$lines = "";
			$lines_str = "";
		}
		if (isset($_SESSION['filtered_markers'])) {
		    $selectcount = $_SESSION['filtered_markers'];
		    $markers = $_SESSION['filtered_markers'];
		    $markers_str = implode(",", $markers);
		} else {
		    $markers = array();
                    $markers_str = "";
		}
		if (isset($_SESSION['phenotype'])) {
		    $phenotype = $_SESSION['phenotype'];
		} else {
		    $phenotype = "";
		}
 
                if (!preg_match('/[0-9]/',$markers_str)) {
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
                $unique_str = chr(rand(65,80)).chr(rand(65,80)).chr(rand(64,80));
                $filename1 = 'download_tassel_hapmap_' . $unique_str . '.txt';
                $filename2 = 'download_tassel_' . $unique_str . '.jnlp';
                $filename3 = 'download_tassel_traits_' . $unique_str . '.txt';
                $filename5 = 'download_tassel_large_' . $unique_str . '.jnlp';

                if(!file_exists($dir.$filename1)){
                        $h = fopen($dir.$filename1, "w+");
                }
        
                fwrite($h,$this->type3_build_markers_download($lines,$markers,$dtype));
                fclose($h);
                if (isset($_SESSION['phenotype'])) {
                        $filename4 = "tassel_gp.jnlp";
                        $h = fopen($dir.$filename3, "w");
                        fwrite($h,$this->type1_build_tassel_traits_download($experiments_t,$phenotype,$datasets_exp,$subset));
                } else {
                        $filename4 = "tassel_g.jnlp";
                }
                if(!file_exists($dir.$filename2)) {
                        $h = fopen($dir.$filename2, "w+");
                        $h2 = fopen($dir.$filename5, "w+");
                }
                $hi = fopen($filename4, "r");
                $contents = fread($hi, filesize($filename4));
                fclose($hi);
                $url = $_SERVER['SERVER_NAME'];
                $contents = str_replace("chr1.diversity_arabidopsis.daa1.live.jnlp","$filename2",$contents);
                $contents = str_replace("malt.pw.usda.gov","$url",$contents);
                $contents = str_replace("genotype_hapmap.txt","$filename1",$contents);
                $contents = str_replace("traits.txt","$filename3",$contents);
                fwrite($h,"$contents");
                $contents = str_replace("2048m","950m",$contents);
                $contents = str_replace("$filename2","$filename5",$contents);
                fwrite($h2,"$contents");
                fclose($h);
                fclose($h2);
                $temp = $dir.$filename2;
                $temp2 = $dir.$filename5;
          echo "<form method=\"LINK\" action=\"$temp\">";
          echo "<input type=\"submit\" value=\"Open file with TASSEL\">";
          echo "</form><br>";
          echo "<form method=\"LINK\" action=\"$temp2\">";
          echo "<input type=\"submit\" value=\"Open file with TASSEL\"> (950Mb Heap Size) Use if Error Creating Java Virtual Machine";
          echo "</form><br>";
          echo "Note:<br>\n";
          echo "1. For Safari and other browsers that will not execute programs from the internet, ";
          echo "go to your download folder and open the .jnlp file<br>";
          echo "2. You will have to change the Java security from high to medium if you receive an error message ";
          echo "that self-signed applications are blocked<br><br>";
          echo "TASSEL (Trait Analysis by Association, Evolution and Linkage)<br>";
          echo "provided by Buckler Lab for Maize Genetics and Diversity<br>";
          echo "<a href=\"http://www.maizegenetics.net/tassel\">www.maizegenetics.net/tassel</a>";
        
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
	    ?></div>
	    <div id="step3" style="float: left; margin-bottom: 1.5em;">
            <?php
            $this->step3_lines();
            ?></div>
	    <div id="step4" style="float: left; margin-bottom: 1.5em;">
            <?php
            $this->step4_lines();
            ?></div>
	    <div id="step4b" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%"></div>
	    <div id="step5" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%">
	    <script type="text/javascript">
	      var mm = 10;
	      var mmaf = 5; 
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
	        echo "pedigree/line_properties.php>Select Lines by Properties</a>";
	    }
	}
	
	/**
	 * starting with lines display trials
	 */
	private function step2_lines()
	{
	    ?>
	    <table id="linessel" class="tableclass1">
	    <tr>
	    <th>Markers</th>
	    </tr>
	    <tr><td>
	    <?php 
	    if (isset($_SESSION['clicked_buttons'])) {
	      $selected = $_SESSION['clicked_buttons'];
		  ?>
	      <select name="markers" multiple="multiple" style="height: 12em;">
	      <?php
	      foreach($selected as $uid) {
	        $sql = "SELECT marker_name from markers where marker_uid = $uid";
	        $res = mysql_query($sql) or die(mysql_error());
	        $row = mysql_fetch_assoc($res)
	        ?>
	        <option disabled="disabled" value="
	        <?php $uid ?>">
	        <?php echo $row['marker_name'] ?>
	        </option>
	        <?php
	      }
	    } else {
	      echo "All";
	    }
	    ?>
	    </select>
	    </td>
	    </table>
	    <?php  
	}
	
	/**
	 * starting with lines display phenotype items
	 */
	private function step3_lines()
	{
	    ?>
	    <table id="" class="tableclass1">
	    <tr>
	    <th>Traits</th>
	    </tr>
	    <tr><td>
	    <?php
            if (isset($_SESSION['selected_traits'])) {
              $selected = $_SESSION['selected_traits'];
              ?>
              <select name="traits" multiple="multiple" style="height: 12em;">
              <?php
              foreach($selected as $uid) {
                $sql = "SELECT phenotypes_name from phenotypes where phenotype_uid = $uid";
                $res = mysql_query($sql) or die(mysql_error());
                $row = mysql_fetch_assoc($res)
                ?>
                    <option disabled="disabled" value="<?php echo $row['phenotypes_name'] ?>">
                     <?php echo $row['phenotypes_name'] ?>
                    </option>
                    <?php
                }
            } else {
              echo "none selected";
            }
            ?>
            </select></table>
             <?php
        }

        /**
         * starting with lines display phenotype items
         */
        private function step4_lines()
        {
            ?>
            <table id="" class="tableclass1">
            <tr>
            <th>Trials</th>
            </tr>
            <tr><td>
            <?php
            if (isset($_SESSION['selected_trials'])) {
              $selected = $_SESSION['selected_trials'];
              ?>
              <select name="traits" multiple="multiple" style="height: 12em;">
              <?php
              foreach($selected as $uid) {
                $sql = "SELECT trial_code from experiments where experiment_uid = $uid";
                $res = mysql_query($sql) or die(mysql_error());
                $row = mysql_fetch_assoc($res)
                ?>
                    <option disabled="disabled" value="<?php echo $row['trial_code'] ?>">
                     <?php echo $row['trial_code'] ?>
                    </option>
                    <?php
                }
            } else {
              echo "none selected";
            }
            ?>
            </select></table>
             <?php
        }


	/**
	 * starting with lines display marker data
	 */
	private function step5_lines() {
	 
	$saved_session = "";
	$message2 = "";

        if (isset($_GET['use_line']) && ($_GET['use_line'] == "yes")) {
          $use_database = 0;
        } else {
          $use_database = 1;
        }

	if (isset($_SESSION['phenotype'])) {
	    $phenotype = $_SESSION['phenotype'];
	    $message2 = "create phenotype and genotype data files";
	} else {
	    $phenotype = "";
	    $message2 = "create genotype data file";
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
         if (isset($_SESSION['selected_map'])) {
             $selected_map = $_SESSION['selected_map'];
         } else {
             $selected_map = 1;
         }
         $sql = "select mapset_name from mapset where mapset_uid = $selected_map";
         $res = mysql_query($sql) or die(mysql_error());
         $row = mysql_fetch_assoc($res);
         $map_name = $row['mapset_name'];
	 
	 echo "selected data = $saved_session<br>";
         echo "selected map = $map_name<br>";
	 
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
         $max_miss_line = 10;
         if (isset($_GET['mml']) && !empty($_GET['mml']) && is_numeric($_GET['mml']))
           $max_miss_line = $_GET['mml'];
         ?>
        <p>
        Minimum MAF &ge; <input type="text" name="mmaf" id="mmaf" size="2" value="<?php echo ($min_maf) ?>" />
        &nbsp;&nbsp;&nbsp;&nbsp;
        Remove markers missing &gt; <input type="text" name="mm" id="mm" size="2" value="<?php echo ($max_missing) ?>" />% of data
        &nbsp;&nbsp;&nbsp;&nbsp;
        Remove lines missing &gt <input type="text" name="mml" id="mml" size="2" value="<?php echo ($max_miss_line) ?>" />% of data
        <?php
         if ($use_database) {
           calculate_db($lines, $min_maf, $max_missing, $max_miss_line);
         } else { 
	   calculate_af($lines, $min_maf, $max_missing, $max_miss_line); 
         }

	 if ($saved_session != "") {
	     if ($countLines == 0) {
	       echo "Choose one or more lines before using a saved selection. ";
	       echo "<a href=";
	       echo $config['base_url'];
	       echo "pedigree/line_properties.php> Select lines</a><br>";
	     } elseif ($use_database) {
	       echo "<br>Filter markers and lines then $message2<br><br>";
	       ?>
	       <input type="button" value="Create Tassel Import" onclick="javascript:use_session('v4');"  />
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
	    <input type="radio" name="subset" id="subset" value="yes" <?php echo "$sub_ckd"; ?> onchange="javascript: update_phenotype_linesb(this.value)">Include only <a href="<?php echo $config['base_url']; ?>pedigree/line_properties.php">currently
	    selected lines</a><br>
	    <input type="radio" name="subset" id="subset" value="no" <?php echo "$all_ckd"; ?> onchange="javascript: update_phenotype_linesb(this.value)">Use lines with selected <b>Trials</b> and <b>Traits</b><br>
	    <input type="radio" name="subset" id="subset" value="comb" <?php echo "$cmb_ckd"; ?> onchange="javascript: update_phenotype_linesb(this.value)">Combine two sets<br>
	    <?php
	  }
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
		$filename = 'download_tassel_'.chr(rand(65,80)).chr(rand(65,80)).chr(rand(64,80)).'.zip';
		
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
		$filename = 'download_tasselV3_'.chr(rand(65,80)).chr(rand(65,80)).chr(rand(64,80)).'.zip';
		
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

          if (isset($_SESSION['clicked_buttons'])) {
            $selectcount = $_SESSION['clicked_buttons'];
            $markers = $_SESSION['clicked_buttons'];
            $markers_str = implode(",", $_SESSION['clicked_buttons']);
          } else {
            $markers = array();
            $markers_str = "";
          }
	  
	  if (!preg_match('/[0-9]/',$markers_str)) {
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
	  $filename = 'download_tassel_'.chr(rand(65,80)).chr(rand(65,80)).chr(rand(64,80)).'.zip';
	  
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
	    $zip->writeData($this->type1_build_geneticMap($lines,$markers));
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
	
                //generate an array of selected markers that can be used with isset statement
                foreach ($markers as $temp) {
                  $marker_lookup[$temp] = 1;
                }
	
		$sql = "select marker_uid, marker_name from allele_byline_idx order by marker_uid";
		$res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
		$i=0;
		while ($row = mysql_fetch_array($res)) {
		   $marker_list[$i] = $row[0];
		   $marker_list_name[$i] = $row[1];
		   $i++;
		}

	        foreach ($marker_list as $i => $marker_id) {
                  $marker_name = $marker_list_name[$i];
                  if (isset($marker_lookup[$marker_id])) {
                    $marker_names[] = $marker_name;
                    if ($outputheader == '') {
                       $outputheader .= $marker_name;
                    } else {
                       $outputheader .= $delimiter.$marker_name;
                    }
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
		  	  $outarray2[]=$lookup[$allele];
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

         if (isset($_SESSION['selected_map'])) {
           $selected_map = $_SESSION['selected_map'];
         } else {
           $selected_map = 1;
         }
	
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
	
         //generate an array of selected lines that can be used with isset statement
         foreach ($lines as $temp) {
           $line_lookup[$temp] = 1;
         }

         $sql = "select line_record_uid, line_record_name from allele_bymarker_idx order by line_record_uid";
         $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
         $i=0;
         while ($row = mysql_fetch_array($res)) {
            $line_list[$i] = $row[0];
            $line_list_name[$i] = $row[1];
            $i++;
         }
 
	 //order the markers by map location
         //tassel v5 needs markers sorted when position is not unique
         $sql = "select markers.marker_uid, CAST(1000*mim.start_position as UNSIGNED), mim.chromosome from markers, markers_in_maps as mim, map, mapset
	 where markers.marker_uid IN ($markers_str)
	 AND mim.marker_uid = markers.marker_uid
	 AND mim.map_uid = map.map_uid
	 AND map.mapset_uid = mapset.mapset_uid
	 AND mapset.mapset_uid = $selected_map 
	 order by mim.chromosome, CAST(1000*mim.start_position as UNSIGNED), BINARY markers.marker_name";
	 $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
	 while ($row = mysql_fetch_array($res)) {
           $marker_uid = $row[0];
           $pos = $row[1];
           $chr = $row[2];
	   $marker_list_mapped[$marker_uid] = $pos;
           $marker_list_chr[$marker_uid] = $chr;
	 }

         $marker_list_all = $marker_list_mapped;	
	 //generate an array of selected markers and add map position if available
         $sql = "select marker_uid, marker_name, A_allele, B_allele, marker_type_name from markers, marker_types
         where marker_uid IN ($markers_str)
         and markers.marker_type_uid = marker_types.marker_type_uid
         order by BINARY marker_name";
         $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
         while ($row = mysql_fetch_array($res)) {
           $marker_uid = $row[0];
           $marker_name = $row[1];
           if (isset($marker_list_mapped[$marker_uid])) {
           } else {
             $marker_list_all[$marker_uid] = 0;
           }
           if (preg_match("/[A-Z]/",$row[2]) && preg_match("/[A-Z]/",$row[3])) {
                $allele = $row[2] . "/" . $row[3];
           } elseif (preg_match("/[0-9]/",$row[2]) && preg_match("/[0-9]/",$row[3])) {
                $allele = $row[2] . "/" . $row[3];
           } else {
                $allele = "N/N";
           }
           $marker_list_name[$marker_uid] = $marker_name;
           $marker_list_allele[$marker_uid] = $allele;
           $marker_list_type[$marker_uid] = $row[4];
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
	 $sql = "select line_record_name from line_records where line_record_uid IN ($lines_str) order by line_record_uid";
	 $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
	 while ($row = mysql_fetch_array($res)) {
	  $name = $row[0];
	  $outputheader .= "\t$name";
	  $empty[$name] = "NN";
	 }
	 
	 //using a subset of markers so we have to translate into correct index
         //if there is no map then use chromosome 0 and index for position
         $pos_index = 0;
	 foreach ($marker_list_all as $marker_id => $rank) {
	  $marker_idx = $marker_idx_list[$marker_id];
          $marker_name = $marker_list_name[$marker_id];
          $allele = $marker_list_allele[$marker_id];
          $marker_type = $marker_list_type[$marker_id];
          if (isset($marker_list_mapped[$marker_id])) {
            $chrom = $marker_list_chr[$marker_id];
            $pos = $marker_list_mapped[$marker_id];
          } else {
            $chrom = 'UNK';
            $pos = $pos_index;
            $pos_index += 10;
          }

          if (preg_match("/DArT/", $marker_type)) {
              $lookup = array(
              'AA' => '+',
              'BB' => '-',
              '--' => 'N'
              );
          } else {
              $lookup = array(
              'AA' => substr($allele,0,1) . substr($allele,0,1),
              'BB' => substr($allele,2,1) . substr($allele,2,1),
              '--' => 'NN',
              'AB' => substr($allele,0,1) . substr($allele,2,1),
              'BA' => substr($allele,2,1) . substr($allele,0,1),
              '' => 'NN'
              );
          }

             if ($dtype == "qtlminer") {
               $output .= "$marker_name\t$allele\t$chrom\t$pos";
             } else {
               $output .= "$marker_name\t$allele\t$chrom\t$pos\t\t\t\t\t\t\t";
             }
             $outarray2 = array();
             $sql = "select marker_name, alleles from allele_bymarker where marker_uid = $marker_id";
             $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
             if ($row = mysql_fetch_array($res)) {
               $alleles = $row[1];
               $outarray = explode(',',$alleles);
               $i=0;
               foreach ($outarray as $allele) {
                 $line_id = $line_list[$i];
                 if (isset($line_lookup[$line_id])) {
                   $outarray2[]=$lookup[$allele];
                 }
                 $i++;
               }
             } else {
               die("Error - could not find $marker_id<br>\n");
             }
             $allele_str = implode("\t",$outarray2);
             $output .= "\t$allele_str\n";
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
         if (isset($_SESSION['selected_map'])) {
           $selected_map = $_SESSION['selected_map'];
         } else {
           $selected_map = 1;
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
				AND mapset.mapset_uid = $selected_map 
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
         * @param array $lines
         * @param array $markers
	 * @return string
	 */
	function type1_build_geneticMap($lines,$markers)
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
        if (isset($_SESSION['selected_map'])) {
           $selected_map = $_SESSION['selected_map'];
         } else {
           $selected_map = 1;
         }
		
		$lookup_chrom = array(
		  '1H' => '1','2H' => '2','3H' => '3','4H' => '4','5H' => '5',
		  '6H' => '6','7H' => '7','UNK'  => '10'
		);

                //generate an array of selected markers that can be used with isset statement
                foreach ($markers as $temp) {
                  $marker_lookup[$temp] = 1;
                }

                $sql = "select marker_uid, marker_name from allele_byline_idx order by marker_uid";
                $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
                $i=0;
                while ($row = mysql_fetch_array($res)) {
                  $marker_list[$i] = $row[0];
                  $marker_list_name[$i] = $row[1];
                  $i++;
                }

		$sql = "select markers.marker_uid,  mim.chromosome, mim.start_position from markers, markers_in_maps as mim, map, mapset
		where mim.marker_uid = markers.marker_uid
		AND mim.map_uid = map.map_uid
		AND map.mapset_uid = mapset.mapset_uid
		AND mapset.mapset_uid = $selected_map";
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
	
                foreach ($lines as $line_record_uid) {
                  $sql = "select alleles from allele_byline where line_record_uid = $line_record_uid";
                  $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
                  if ($row = mysql_fetch_array($res)) {
                    $alleles = $row[0];
                    $outarray = explode(',',$alleles);
                    $i=0;
                    foreach ($outarray as $allele) {
                      if ($allele=='AA') {
                        $marker_aacnt[$i]++;
                      }
                      elseif (($allele=='AB') or ($allele=='BA')) {
                        $marker_abcnt[$i]++;
                      }
                      elseif ($allele=='BB') {
                        $marker_bbcnt[$i]++;
                      }
                      elseif (($allele=='--') or ($allele=='')) {
                        $marker_misscnt[$i]++;
                      }
                      else { echo "illegal genotype value $allele for marker $marker_list_name[$i]<br>";
                      }
                      $i++;
                    }
                  }
                  //echo "$line_record_uid<br>\n";
                }

                //get lines and filter to get a list of markers which meet the criteria selected by the user
                $num_maf = $num_miss = 0;
                foreach ($marker_list as $i => $uid) {
                  $marker_name = $marker_list_name[$i];
                  if (isset($marker_lookup[$uid])) {
                    $total = $marker_aacnt[$i] + $marker_abcnt[$i] + $marker_bbcnt[$i] + $marker_misscnt[$i];
                    if ($total>0) {
                      $maf[$i] = round(100 * min((2 * $marker_aacnt[$i] + $marker_abcnt[$i]) /$total, ($marker_abcnt[$i] + 2 * $marker_bbcnt[$i]) / $total),1);
                      $miss[$i] = round(100*$marker_misscnt[$i]/$total,1);
                    } else {
                      $maf[$i] = 0;
                      $miss[$i] = 100;
                    }
                    if (($maf[$i] >= $min_maf)AND ($miss[$i]<=$max_missing)) {
                      if (isset($marker_list_mapped[$uid])) {
                        $marker_list_all_name[$uid] = $marker_name;
                        $marker_list_all[$uid] = $marker_list_rank[$uid];
                      }
                    }
                  }
                }
                if (count($marker_list_all) == 0) {
                   $output = "no mapped data found";
                   return $output;
                }

        // make an empty marker with the lines as array keys 
        $nelem = count($marker_uid);
        $n_lines = count($lines);
                $empty = array_combine($lines,array_fill(0,$n_lines,'-'));
                $nemp = count($empty);
                $line_str = implode($delimiter,$lines);
                // $firephp = log($nelem." ".$n_lines);

                // write output file header
                $outputheader = "<Map>\n";
            // $firephp = log($outputheader);

        //sort marker_list by map location
        if (uasort($marker_list_all, array($this,'cmp'))) {
        } else {
          die("could not sort marker list\n");
        }
        
		$num_markers = 0;
		/* foreach( $marker_uid as $cnt => $uid) { */
		foreach($marker_list_all as $uid=>$value) {
		    $marker_name = $marker_list_all_name[$uid];
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
