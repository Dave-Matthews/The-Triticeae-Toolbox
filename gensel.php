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
ini_set('memory_limit', '2G');

// For live website file
require_once 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
set_include_path(GET_INCLUDE_PATH() . PATH_SEPARATOR . '../pear/');
date_default_timezone_set('America/Los_Angeles');

require_once $config['root_dir'].'includes/MIME/Type.php';
require_once $config['root_dir'].'includes/File_Archive/Archive.php';
require_once $config['root_dir'].'downloads/marker_filter.php';

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
            case 'genomic_prediction':
                $this->genomic_prediction();
                break;
            case 'run_histo':
                $this->run_histo();
                break;
            case 'run_gwa':
                $this->run_gwa();
                break;
            case 'run_rscript':
                $this->run_rscript();
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
                        case 'gwas_status':
                            echo $this->status_gwas();
                            break;
                        case 'pred_status':
                            echo $this->status_pred();
                            break;
                        case 'filter_lines':
                            echo $this->filter_lines();
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
            <script type="text/javascript" src="downloads/download_gs.js"></script>
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
                if (empty($_SESSION['training_traits'])) {
                   if (empty($_SESSION['phenotype'])) { 
                     echo "Select training set using ";
                     //echo $config['base_url'];
                     //echo "phenotype/phenotype_selection.php>Trials and Traits</a> or ";
                     echo "<a href=";
                     echo $config['base_url'];
                     echo "downloads/select_all.php>Wizard</a>.<br><br>";
                   }
                } elseif (empty($_SESSION['selected_lines'])) {
                    echo "<br>Select validation set containing trait measurements to plot prediction vs observed. ";
                    echo "<a href=";
                    echo $config['base_url'];
                    echo "downloads/select_all.php>Wizard</a><br>";
                    echo "Select prediction set without trait measurements to predict the traits. ";
                    echo "<a href=";
                    echo $config['base_url'];
                    echo "pedigree/line_selection.php>Lines by Properties</a><br>";
                } elseif (empty($_SESSION['phenotype']) && empty($_SESSION['training_traits'])) {
                    echo "Please select traits before using this feature.<br><br>";
                    echo "<a href=";
                    echo $config['base_url'];
                    echo "phenotype/phenotype_selection.php>Select Traits</a><br><br>";
                    echo "<a href=";
                    echo $config['base_url'];
                    echo "downloads/select_all.php>Wizard (Lines, Traits, Trials)</a>";
                } 
                if (!empty($_SESSION['training_lines']) && !empty($_SESSION['selected_lines'])) {
                   if (empty($_SESSION['selected_trials'])) {
                     echo "<tr><td>Prediction<td>";
                   } else {
                     echo "<tr><td>Validation<td>";
                     $tmp = $_SESSION['selected_trials'];
                     $e_uid = implode(",",$tmp);
        $sql = "select trial_code from experiments where experiment_uid IN ($e_uid)";
        $res = mysql_query($sql) or die(mysql_error() . $sql);
        while ($row = mysql_fetch_array($res)) {
          echo "$row[0]<br>";
        }
        }
             
                   $count = count($_SESSION['selected_lines']);
                   echo "<td>$count";
                   ?>
                   <td>
                   <form method="LINK" action="gensel.php">
                   <input type="hidden" value="step1gensel" name="function">
                   <input type="hidden" value="clear_p" name="cmd">
                   <input type="submit" value="Clear Selection">
                   </form>
                   <?php
                   //check if these are unique
                   $count = 0;
                   $count_dup = 0;
                   $tmp1 = $_SESSION['training_lines'];
                   $tmp2 = $_SESSION['selected_lines'];
                   $count_t = count($tmp2);
                   foreach ($tmp2 as $uid) {
                     if(in_array($uid,$tmp1)) {
                       $count_dup++;
                     } else{
                       $count++;
                     }
                   }
                   if ($count < 5) {
                     echo " <font color=red>(Error - $count unique lines in prediction set)";
                   }
                }
                echo "</table>";
                if ($count_dup > 0) {
                     echo " Warning - $count_dup lines removed from prediction set because they are in training set";
                }
                $min_maf = 5;
                $max_missing = 10;
                $max_miss_line = 10;
                $unique_str = chr(rand(65,80)).chr(rand(65,80)).chr(rand(65,80)).chr(rand(65,80));
                ?>
                </div>
                <?php
                if (!empty($_SESSION['training_lines']) && !empty($_SESSION['selected_lines'])) {
                  $min_maf = 5;
                  $max_missing = 10;
                  $max_miss_line = 10;
                  $unique_str = chr(rand(65,80)).chr(rand(65,80)).chr(rand(65,80)).chr(rand(65,80));
                  ?>
                  <p>Minimum MAF &ge; <input type="text" name="mmaf" id="mmaf" size="2" value="<?php echo ($min_maf) ?>" />%
        &nbsp;&nbsp;&nbsp;&nbsp;
        Remove markers missing &gt; <input type="text" name="mmm" id="mmm" size="2" value="<?php echo ($max_missing) ?>" />% of data
        &nbsp;&nbsp;&nbsp;&nbsp;
        Remove lines missing &gt; <input type="text" name="mml" id="mml" size="2" value="<?php echo ($max_miss_line) ?>" />% of data
                  <div id="step1" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%">
                  <img alt="spinner" id="spinner" src="images/ajax-loader.gif" style="display:none;" /></div>
                  <div id="step2" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%">
                  <table>
                  <!--tr><td><td>fixed effect (trial is always included)-->
                  <tr><td><input type="button" value="rrBLUP Analysis" onclick="javascript:load_genomic_prediction('$unique_str')">
                  <!-td-->
                  </table><br>
                  </div>
                  <div id="step3" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%"></div>
                  <div id="step4" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%"></div>
                  <div id="step5" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%">

                  <?php
                  echo "</div>";
                }
                echo "</div>";
        }

    function filter_lines() {
      if (isset($_GET['maf'])) {
           $min_maf = $_GET['maf'];
      } else {
           $min_maf = 5;
      }
      if (isset($_GET['mmm'])) {
           $max_missing = $_GET['mmm'];
      } else {
           $max_missing = 10;
      }
      if (isset($_GET['mml'])) {
           $max_miss_line = $_GET['mml'];
      } else {
           $max_miss_line = 10;
      }
      $lines = $_SESSION['selected_lines'];
      calculate_af($lines, $min_maf, $max_missing, $max_miss_line);
      ?>
      <img alt="spinner" id="spinner" src="images/ajax-loader.gif" style="display:none;" />
      <?php
    }

    /**
     * 1. display a spinning activity image when a slow function is running
     * 2. show button to clear sessin data
     * 3. show button to save current selection
     */    
    private function refresh_title() {
      $command = (isset($_GET['cmd']) && !empty($_GET['cmd'])) ? $_GET['cmd'] : null;
      echo "<h2>Genomic Association and Prediction</h2>";
      if (!empty($_SESSION['training_traits'])) {
        $tmp = $_SESSION['training_traits'];
        $tmp = $tmp[0];
        $sql = "select phenotypes_name from phenotypes where phenotype_uid = '$tmp'";
        $res = mysql_query($sql) or die(mysql_error());
        $row = mysql_fetch_array($res);
        echo "<h3>Trait: $row[0]</h3>";
      }
      if ($command == "save_t") {
        if (!empty($_SESSION['selected_traits'])) {
           $_SESSION['training_traits'] = $_SESSION['selected_traits'];
           $_SESSION['training_trials'] = $_SESSION['selected_trials'];
           $_SESSION['training_lines'] = $_SESSION['selected_lines'];
           unset($_SESSION['selected_trials']);
           unset($_SESSION['selected_lines']);
           unset($_SESSION['filtered_lines']);
           unset($_SESSION['filtered_markers']);
           unset($_SESSION['clicked_buttons']);
        } else {
          echo "error - no selection found";
        }
      } elseif ($command == "save_p") {
           $_SESSION['predict_traits'] = $_SESSION['selected_traits'];
           $_SESSION['predict_trials'] = $_SESSION['selected_trials'];
           $_SESSION['predict_lines'] = $_SESSION['selected_lines'];
      } elseif ($command == "clear") {
           unset($_SESSION['selected_traits']);
           unset($_SESSION['selected_trials']);
           unset($_SESSION['selected_lines']);
           unset($_SESSION['training_traits']);
           unset($_SESSION['training_trials']);
           unset($_SESSION['training_lines']);
           unset($_SESSION['filtered_lines']);
           unset($_SESSION['phenotype']);
      } elseif ($command== "clear_p") {
          unset($_SESSION['selected_traits']);
          unset($_SESSION['selected_trials']);
          unset($_SESSION['selected_lines']);
      }
      if (empty($_SESSION['selected_lines']) || empty($_SESSION['training_lines'])) {
        ?>
        <p><b>Genome Wide Association</b><br>
        1. Select a <a href="downloads/select_all.php">set of lines</a> for one or more trials and one trait.<br>
        2. Select the <a href="maps/select_map.php">genetic map</a> which has the best coverage for this set.<br>
        3. Return to this page and select model options then GWAS Analysis<br>

        <p><b>Genomic Prediction</b><br>
        1. Select a <a href="downloads/select_all.php">set of lines</a> for one or more trials and one trait.<br>
        2. Return to this page and select rrBLUP Analysis for cross-validation of the training set. Then save Training Set.<br>
        3. To select a validation set, select a new set of lines using a different trial, then return to this page for analysis.<br>
        4. To select a prediction set, select a new set of lines without phenotype measurements, then return to this page for analysis.<br>
        
        <p><a href="downloads/genomic-tools.php">Additional notes on rrBLUP and methods</a><br>
        <?php
      }
      if (!empty($_SESSION['training_traits']) && !empty($_SESSION['training_trials'])) {
        echo "<table>";
        echo "<tr><td>Set<td>Trials<td>Lines<td>";
        $p_uid = $_SESSION['training_traits'];
        $p_uid = $p_uid[0];
        $sql = "select phenotypes_name from phenotypes where phenotype_uid = $p_uid";
        $res = mysql_query($sql) or die(mysql_error());
        $row = mysql_fetch_array($res); 
        echo "<tr><td>Training<td>";
        if (!empty($_SESSION['training_trials'])) {
          $tmp = $_SESSION['training_trials'];
          $e_uid = implode(",",$tmp);
          $sql = "select trial_code from experiments where experiment_uid IN ($e_uid)";
          $res = mysql_query($sql) or die(mysql_error() . $sql);
          while ($row = mysql_fetch_array($res)) {
            echo "$row[0]<br>";
          }
        }
        echo "<td>";
        if (count($_SESSION['training_lines']) > 0) {
                  $selectedlines = implode(",", $_SESSION['training_lines']);
                  $sql_option = " AND lr.line_record_uid IN ($selectedlines)";
        } else {
           $sql_option = "";
        }
        $sql = "SELECT count(DISTINCT lr.line_record_uid) 
                FROM tht_base as tb, phenotype_data as pd, phenotypes as p, line_records as lr
                WHERE pd.tht_base_uid = tb.tht_base_uid
                $sql_option
                AND p.phenotype_uid = pd.phenotype_uid
                AND lr.line_record_uid = tb.line_record_uid
                AND pd.phenotype_uid = $p_uid
                AND tb.experiment_uid IN  ($e_uid)";
        $res = mysql_query($sql) or die(mysql_error() . $sql);
        $row = mysql_fetch_array($res);
        echo "$row[0]";
        ?>
        <td>
        <form method="LINK" action="gensel.php">
        <input type="hidden" value="step1gensel" name="function">
        <input type="hidden" value="clear" name="cmd">
        <input type="submit" value="Clear Selection">
        </form>
        <?php
        if (empty($_SESSION['selected_lines'])) {
            echo "</table>";
        }
      //} elseif (!empty($_SESSION['selected_traits']) && !empty($_SESSION['selected_trials'])) { use when I get this working for multiple traits
      } elseif (!empty($_SESSION['phenotype']) && !empty($_SESSION['selected_trials'])) {
        ?>
        <table>
        <tr><td>Traits<td>Trials<td>Lines
        <tr><td>
        <?php
        $traits = $_SESSION['phenotype'];
        //$traits= implode(",",$tmp); use when I get this working for multiple traits
        $sql = "select phenotypes_name from phenotypes where phenotype_uid IN ($traits)";
        $res = mysql_query($sql) or die(mysql_error());
        while ($row = mysql_fetch_array($res)) {
          echo "$row[0]<br>";
        }
        echo "<td>";
        $tmp = $_SESSION['selected_trials'];
        $e_uid = implode(",",$tmp);
        $sql = "select trial_code from experiments where experiment_uid IN ($e_uid)";
        $res = mysql_query($sql) or die(mysql_error() . $sql);
        while ($row = mysql_fetch_array($res)) {
          echo "$row[0]<br>";
        }
        echo "<td>";
        if (count($_SESSION['selected_lines']) > 0) {
                  $selectedlines = implode(",", $_SESSION['selected_lines']);
                  $sql_option = " AND lr.line_record_uid IN ($selectedlines)";
        } else {
           $sql_option = "";
        }
        $sql = "SELECT count(DISTINCT lr.line_record_uid) 
                FROM tht_base as tb, phenotype_data as pd, phenotypes as p, line_records as lr
                WHERE pd.tht_base_uid = tb.tht_base_uid
                $sql_option
                AND p.phenotype_uid = pd.phenotype_uid
                AND lr.line_record_uid = tb.line_record_uid
                AND pd.phenotype_uid IN ($traits) 
                AND tb.experiment_uid IN  ($e_uid)";
        $res = mysql_query($sql) or die(mysql_error() . $sql);
        $row = mysql_fetch_array($res);
        $count = $row[0];
        echo "$count</table>";
        $min_maf = 5;
        $max_missing = 10;
        $max_miss_line = 10;
        $unique_str = chr(rand(65,80)).chr(rand(65,80)).chr(rand(65,80)).chr(rand(65,80));
        if ($count > 0) {
          ?>
          <p>Minimum MAF &ge; <input type="text" name="mmaf" id="mmaf" size="2" value="<?php echo ($min_maf) ?>" />%
        &nbsp;&nbsp;&nbsp;&nbsp;
        Remove markers missing &gt; <input type="text" name="mmm" id="mmm" size="2" value="<?php echo ($max_missing) ?>" />% of data
        &nbsp;&nbsp;&nbsp;&nbsp;
        Remove lines missing &gt; <input type="text" name="mml" id="mml" size="2" value="<?php echo ($max_miss_line) ?>" />% of data
        &nbsp;&nbsp;&nbsp;&nbsp;
          <input type="button" value="Filter Lines and Markers" onclick="javascript:filter_lines();"/>
          </div>
          <div id="filter" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%">
          <img alt="spinner" id="spinner" src="images/ajax-loader.gif" style="display:none;" /></div>
          <div id="step1" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%"></div>
          <div id="step2" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%">

          <table border=0>
          <tr><td>
          <input type="button" value="Analyze" onclick="javascript:load_genomic_gwas('<?php echo $unique_str; ?>')"> GWAS
          <td>
          <select name="model2" onchange="javascript: update_fixed(this.value)">
          <option>0</option>
          <option>1</option>
          <option>2</option>
          <option>3</option>
          <option>4</option>
          <option>5</option>
          </select>principal components
          <!--td><input type="checkbox" value="PC3D">PC3D -->
          <tr><td><input type="button" value="Analyze" onclick="javascript:load_genomic_prediction('<?php echo $unique_str; ?>')"> rrBLUP
          <td>
          <!--td><input type="checkbox" name="model3" value="reduce">reduce -->
          </table><br>
          <form action="gensel.php">
          <input type="hidden" value="step1gensel" name="function">
          <input type="hidden" value="save_t" name="cmd">
          <input type="submit" value="Save Training Set">
          then continue to select prediction set
          </form>

          </div>
          <div id="step3" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%"></div>
          <div id="step4" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%"></div>
          <div id="step5" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%">

          <?php
        } else {
          echo "<font color=red>Warning, not a valid combination of traits, trials, and lines</font>";
        }
      }
      ?>
      </p>
      <?php 
    }

    /**
     * setup results page for R stat analysis
     */
    private function genomic_prediction() {
        ?>
        <h2>Genomic Selection</h2>
        <img alt="spinner" id="spinner" src="images/ajax-loader.gif" style="display:none;" />
        <?php
    }
    private function run_histo() {
        $unique_str = $_GET['unq'];
        $dir = '/tmp/tht/';
        $filename1 = 'THTdownload_hapmap_' . $unique_str . '.txt';
        $filename2 = 'THTdownload_traits_' . $unique_str . '.txt';
        $filename3 = 'THTdownload_histo_' . $unique_str . '.R';
        $filename4 = 'THTdownload_histo_' . $unique_str . '.png';
        $filename5 = 'process_error_histo_' . $unique_str . '.txt';
        if (isset($_SESSION['training_traits'])) {
            $phenotype = $_SESSION['training_traits'];
            $phenotype = $phenotype[0];
        } elseif (isset($_SESSION['selected_traits'])) {
            $phenotype = $_SESSION['selected_traits'];
            $phenotype = $phenotype[0];
        }
            $sql = "select phenotypes_name, unit_name from phenotypes, units
               where phenotypes.unit_uid = units.unit_uid
               and phenotype_uid = $phenotype";
            $res = mysql_query($sql) or die(mysql_error());
            $row = mysql_fetch_array($res);
            $phenolabel = $row[0];
            $phenounit = $row[1]; 
        
        $ntrials = 0;
        $triallabel = "";
        if (isset($_SESSION['selected_trials'])) {
          $trials = $_SESSION['selected_trials'];
          foreach ($trials as $uid) {
            $sql = "select trial_code from experiments where experiment_uid = $uid";
            $res = mysql_query($sql) or die(mysql_error());
            if ($row = mysql_fetch_array($res)) {
                $trial = $row[0];
            }
            if ($triallabel == "") {
              $triallabel = "triallabel <- list()\n";
            }
            $triallabel .= "triallabel[$uid] <- \"$trial\"\n";
            $ntrials++;
          }
        }

        if (isset($_SESSION['training_trials'])) {
          $trials = $_SESSION['training_trials'];
          foreach ($trials as $uid) {
            $sql = "select trial_code from experiments where experiment_uid = $uid";
            $res = mysql_query($sql) or die(mysql_error());
            if ($row = mysql_fetch_array($res)) {
              $trial = $row[0];
            }
            if ($triallabel == "") {
              $triallabel= "triallabel <- list()\n";
            }
            $triallabel .= "triallabel[$uid] <- \"$trial\"\n";
            $ntrials++;
          }
        }

        $histo_width = 800;
        if ($ntrials > 3) {
          $histo_width = 800 + ($ntrials - 3) * 200;
        }
        
        if(!file_exists($dir.$filename3)){
            $h = fopen($dir.$filename3, "w+");
            $png = "png(\"$dir$filename4\", width=$histo_width, height=300)\n";
            $cmd1 = "phenoData <- as.matrix(read.table(\"$dir$filename2\", header=TRUE, na.strings=\"-999\", stringsAsFactors=FALSE, sep=\"\\t\", row.names=1))\n";
            $cmd1 = "phenoData <- read.table(\"$dir$filename2\", header=TRUE, na.strings=\"-999\", stringsAsFactors=FALSE, sep=\"\\t\", row.names=NULL)\n";
            $cmd2 = "phenolabel <- \"$phenolabel\"\n";
            $cmd3 = "phenounit <- \"$phenounit\"\n";
            $cmd4 = $triallabel;
            fwrite($h, $png);
            fwrite($h, $cmd1);
            fwrite($h, $cmd2);
            fwrite($h, $cmd3);
            fwrite($h, $cmd4);
            fclose($h);
        }
        exec("cat /tmp/tht/$filename3 R/GShisto.R | R --vanilla > /dev/null 2> /tmp/tht/$filename5");
        if (file_exists("/tmp/tht/$filename5")) {
            $h = fopen("/tmp/tht/$filename5", "r");
            while ($line=fgets($h)) {
              echo "$line<br>\n";
            }
            fclose($h);
        }
        if (file_exists("/tmp/tht/$filename4")) {
                  print "<img src=\"/tmp/tht/$filename4\" /><br>";
        } else {
                  echo "Error in R script R/GShisto.R<br>\n";
        }
    }

    private function status_gwas() {
        $unique_str = $_GET['unq'];
        $dir = '/tmp/tht/';
        $filename9 = 'THTdownload_hmp_' . $unique_str. '.txt';
        $filename2 = 'THTdownload_traits_' . $unique_str . '.txt';
        $filename3 = 'THTdownload_gwa_' . $unique_str . '.R';
        $filename4 = 'THTdownload_gwa1_' . $unique_str . '.png';
        $filename7 = 'THTdownload_gwa2_' . $unique_str . '.png';
        $filename10 = 'THTdownload_gwa3_' . $unique_str . '.png';
        $filename5 = 'process_error_gwa_' . $unique_str . '.txt';
        $filename6 = 'R_error_gwa_' . $unique_str . '.txt';
        $filename1 = 'THT_result_' . $unique_str . '.csv';
        $filenameK = 'Kinship_matrix_' . $unique_str . '.csv';
        if (file_exists("/tmp/tht/$filename7")) {
                  print "<img src=\"/tmp/tht/$filename7\" /><br>";
        } else {
                  echo "Not finished analysis.<br>\n";
        }
        if (file_exists("/tmp/tht/$filename10")) {
                  print "<img src=\"/tmp/tht/$filename10\" /><br>";
        } else {
                  echo "Not finished analysis.<br>\n";
        }
        if (file_exists("/tmp/tht/$filename4")) {
                  print "<img src=\"/tmp/tht/$filename4\" /><br>";
                  print "<a href=/tmp/tht/$filename1 target=\"_blank\" type=\"text/csv\">Export GWAS results to CSV file</a> ";
                  print "with columns for marker name, chromosome, position, marker score<br><br>";
                  print "<a href=/tmp/tht/$filenameK target=\"_blank\" type=\"text/csv\">Export Kinship matrix</a> ";
        }
        if (file_exists("/tmp/tht/$filename5")) {
           $h = fopen("/tmp/tht/$filename5", "r");
           while ($line=fgets($h)) {
               echo "$line<br>\n";
           }
           fclose($h);
        }
    }
  
    private function run_gwa() {
        $unique_str = $_GET['unq'];
        $model_opt = $_GET['fixed2'];
        if (isset($_SESSION['training_traits'])) {
            $phenotype = $_SESSION['training_traits'];
            $phenotype = $phenotype[0];
        //} elseif (isset($_SESSION['selected_traits'])) {  use when multiple traits is working
          } elseif (isset($_SESSION['phenotype'])) {
            $phenotype = $_SESSION['phenotype'];
        }
        $sql = "select phenotypes_name, unit_name from phenotypes, units
               where phenotypes.unit_uid = units.unit_uid
               and phenotype_uid = $phenotype";
        $res = mysql_query($sql) or die(mysql_error());
        $row = mysql_fetch_array($res);
        $phenolabel = $row[0];
        //$unique_fld = chr(rand(65,80)).chr(rand(65,80)).chr(rand(65,80)).chr(rand(65,80));
        //mkdir("/tmp/tht/$unique_fld");  it would be better to put all files in directory
        $dir = '/tmp/tht/';
        $filename9 = 'THTdownload_hmp_' . $unique_str. '.txt';
        $filename2 = 'THTdownload_traits_' . $unique_str . '.txt';
        $filename3 = 'THTdownload_gwa_' . $unique_str . '.R';
        $filename4 = 'THTdownload_gwa1_' . $unique_str . '.png';
        $filename7 = 'THTdownload_gwa2_' . $unique_str . '.png';
        $filename10 = 'THTdownload_gwa3_' . $unique_str . '.png';
        $filename5 = 'process_error_gwa_' . $unique_str . '.txt';
        $filename6 = 'R_error_gwa_' . $unique_str . '.txt';
        $filename1 = 'THT_result_' . $unique_str . '.csv';
        $filenameK = 'Kinship_matrix_' . $unique_str . '.csv';
        if(!file_exists($dir.$filename3)){
            $h = fopen($dir.$filename3, "w+");
            $png1 = "png(\"$dir$filename4\", width=800, height=400)\n";
            $png2 = "png(\"$dir$filename7\", width=800, height=400)\n";
            $png3 = "png(\"$dir$filename10\", width=800, height=400)\n"; 
            $png4 = "dev.set(3)\n";
            $cmd3 = "phenoData <- read.table(\"$dir$filename2\", header=TRUE, na.strings=\"-999\", stringsAsFactors=FALSE, sep=\"\\t\", row.names=NULL)\n";
            $cmd4 = "hmpData <- read.table(\"$dir$filename9\", header=TRUE, stringsAsFactors=FALSE, sep=\"\\t\", check.names = FALSE)\n";
            $cmd5 = "phenolabel <- \"$phenolabel\"\n";
            $cmd6 = "fileerr <- \"$dir$filename6\"\n";
            $cmd7 = "fileout <- \"$filename1\"\n";
            $cmd8 = "model_opt <- \"$model_opt\"\n";
            $cmd9 = "fileK <- \"$filenameK\"\n";
            fwrite($h, $png1);
            fwrite($h, $png2);
            fwrite($h, $png3);
            fwrite($h, $png4);
            fwrite($h, $cmd3);
            fwrite($h, $cmd4);
            fwrite($h, $cmd5);
            fwrite($h, $cmd6);
            fwrite($h, $cmd7);
            fwrite($h, $cmd8);
            fwrite($h, $cmd9);
            fwrite($h, "setwd(\"/tmp/tht/\")\n");
            fclose($h);
        }
        exec("cat /tmp/tht/$filename3 R/GSforGWA.R | R --vanilla > /dev/null 2> /tmp/tht/$filename5");
        if (file_exists("/tmp/tht/$filename7")) {
                  print "<img src=\"/tmp/tht/$filename7\" /><br>";
        } else {
                  echo "Error in R script<br>\n";
                  echo "cat /tmp/tht/$filename3 R/GSforT3.R | R --vanilla <br>";
        }
        if (file_exists("/tmp/tht/$filename10")) {
                  print "<img src=\"/tmp/tht/$filename10\" /><br>";
        }
        if (file_exists("/tmp/tht/$filename4")) {
                  print "<img src=\"/tmp/tht/$filename4\" /><br>";
                  print "<a href=/tmp/tht/$filename1 target=\"_blank\" type=\"text/csv\">Export GWAS results to CSV file</a> ";
                  print "with columns for marker name, chromosome, position, marker score<br><br>";
                  print "<a href=/tmp/tht/$filenameK target=\"_blank\" type=\"text/csv\">Export Kinship matrix</a> ";
        }
        if (file_exists("/tmp/tht/$filename5")) {
           $h = fopen("/tmp/tht/$filename5", "r");
           while ($line=fgets($h)) {
               echo "$line<br>\n";
           }
           fclose($h);
        } 
    } 

    private function run_kin() {
        $unique_str = $_GET['unq'];
        $dir = '/tmp/tht/';
        if (isset($_SESSION['training_traits'])) {
            $phenotype = $_SESSION['training_traits'];
            $phenotype = $phenotype[0];
        } elseif (isset($_SESSION['selected_traits'])) {
            $phenotype = $_SESSION['selected_traits'];
            $phenotype = $phenotype[0];
        }

        $filename1 = 'THTdownload_snp_t_' . $unique_str . '.txt';
        $filename9 = 'THTdownload_hmp_' . $unique_str. '.txt';
        $filename2 = 'THTdownload_traits_' . $unique_str . '.txt';
        $filename3 = 'THTdownload_kin_' . $unique_str . '.R';
        $filename4 = 'THTdownload_kin_' . $unique_str . '.png';
        $filename5 = 'process_error_kin_' . $unique_str . '.txt';
        $filename6 = 'R_error_kin_' . $unique_str . '.txt';
        $filename10 = 'THTdownload_traits_u' . $unique_str . '.txt';

        $sql = "select phenotypes_name, unit_name from phenotypes, units
               where phenotypes.unit_uid = units.unit_uid
               and phenotype_uid = $phenotype";
        $res = mysql_query($sql) or die(mysql_error());
        $row = mysql_fetch_array($res);
        $phenolabel = $row[0];
        $phenounit = $row[1];

        if(!file_exists($dir.$filename3)){
            $h = fopen($dir.$filename3, "w+");
            $png1 = "png(\"$dir$filename4\", width=800, height=400)\n";
            $png3 = "dev.set(2)\n";
            $cmd1 = "snpData <- read.table(\"$dir$filename1\", header=TRUE, stringsAsFactors=FALSE, sep=\"\\t\", row.names=1)\n";
            $cmd2 = "phenolabel <- \"$phenolabel\"\n";
            $cmd3 = "phenoData <- read.table(\"$dir$filename10\", header=TRUE, na.strings=\"-999\", stringsAsFactors=FALSE, sep=\"\\t\", row.names=NULL)\n";
            $cmd4 = "hmpData <- read.table(\"$dir$filename9\", header=TRUE, stringsAsFactors=FALSE, sep=\"\\t\", check.names = FALSE)\n";
            $cmd5 = "fileerr <- \"$dir$filename6\"\n";
            fwrite($h, $png1);
            fwrite($h, $png2);
            fwrite($h, $png3);
            fwrite($h, $cmd1);
            fwrite($h, $cmd2);
            fwrite($h, $cmd3);
            fwrite($h, $cmd4);
            fwrite($h, $cmd5);
            fclose($h);
        }
        //exec("cat /tmp/tht/$filename3 R/GSforKIN.R | R --vanilla > /dev/null 2> /tmp/tht/$filename5");
        //if (file_exists("/tmp/tht/$filename4")) {
        //          print "<img src=\"/tmp/tht/$filename4\" /><br>";
        //} else {
        //          echo "Error in R script<br>\n";
        //          echo "cat /tmp/tht/$filename3 R/GSforKIN.R | R --vanilla <br>";
        //}
        if (file_exists("/tmp/tht/$filename5")) {
           $h = fopen("/tmp/tht/$filename5", "r");
           while ($line=fgets($h)) {
               echo "$line<br>\n";
           }
           fclose($h);
        }
    }

        
    private function run_rscript() {
        $unique_str = $_GET['unq'];
        $filename1 = 'THTdownload_hapmap_' . $unique_str . '.txt';
        $filename2 = 'THTdownload_traits_' . $unique_str . '.txt';
        $filename3 = 'THTdownload_gensel_' . $unique_str . '.R';
        $filename10 = 'THTdownload_gensel2_' . $unique_str . '.png';
        $filename4 = 'THTdownload_gensel_' . $unique_str . '.png';
        $filename5 = 'THT_process_error_' . $unique_str . '.txt';
        $filename6 = 'THT_R_error_' . $unique_str . '.txt';
        $filename7 = 'THT_result_' . $unique_str . '.csv';
        exec("cat /tmp/tht/$filename3 R/GSforT34.R | R --vanilla > /dev/null 2> /tmp/tht/$filename5");
        if (file_exists("/tmp/tht/$filename10")) {
                  print "<img src=\"/tmp/tht/$filename10\" /><br>";
        } else {
                  echo "Error in R script<br>\n";
                  echo "cat /tmp/tht/$filename3 R/GSforT3.R | R --vanilla <br>";
        }
        if (file_exists("/tmp/tht/$filename4")) {
             print "<img src=\"/tmp/tht/$filename4\" /><br>";
             //if (isset($_SESSION['selected_traits'])) { use when multiple traits is supported
             if (isset($_SESSION['phenotype'])) {
                  print "<a href=/tmp/tht/$filename7 target=\"_blank\" type=\"text/csv\">Export prediction to CSV file</a><br><br>";
             } else { 
                  print "Cross-validation of training set using 5 folds and 2 repeats.<br>\n";
                  print "<a href=/tmp/tht/$filename7 target=\"_blank\" type=\"text/csv\">Export Cross-validated prediction to CSV file</a><br><br>";
             }
        } else {
                  echo "Error in R script<br>\n";
                  echo "cat /tmp/tht/$filename3 R/GSforT3.R | R --vanilla <br>";
        }

        if (file_exists("/tmp/tht/$filename5")) {
                  $h = fopen("/tmp/tht/$filename5", "r");
                  while ($line=fgets($h)) {
                   echo "$line<br>\n";
                  }
                  fclose($h);
        }
        if (file_exists("/tmp/tht/$filename6")) {
                  $h = fopen("/tmp/tht/$filename6", "r");
                  while ($line=fgets($h)) {
                    echo "$line<br>\n";
                  }
                  fclose($h);
        }

    }
    
    /**
     * use this download when selecting program and year
     * @param string $version Tassel version of output
     */
    private function type1_session($version)
	{
            global $config;
	    $datasets_exp = "";
            $unique_str = $_GET['unq'];
            $max_missing = $_GET['mmm'];
            $max_miss_line = $_GET['mml'];
            $min_maf = $_GET['mmaf'];
            $model_opt = $_GET['fixed1'];
            $triallabel = "";
            if (isset($_SESSION['training_trials'])) {
              $trial = $_SESSION['training_trials'];
              foreach ($trial as $uid) {
                $sql = "select trial_code from experiments where experiment_uid = $uid";
                      $res = mysql_query($sql) or die(mysql_error());
                      if ($row = mysql_fetch_array($res)) {
                        $trial = $row[0];
                      }
                      if ($triallabel == "") {
                         $triallabel = "triallabel <- list()\n";
                      }
                      $triallabel .= "triallabel[$uid] <- \"$trial\"\n";
              }
            }
            if (isset($_SESSION['selected_trials'])) {
              $trial = $_SESSION['selected_trials'];
              foreach ($trial as $uid) {
                $sql = "select trial_code from experiments where experiment_uid = $uid";
                      $res = mysql_query($sql) or die(mysql_error());
                      if ($row = mysql_fetch_array($res)) {
                        $trial = $row[0];
                      }
                      if ($triallabel == "") {
                         $triallabel = "triallabel <- list()\n";
                      }
                      $triallabel .= "triallabel[$uid] <- \"$trial\"\n";
              }
            }
                if (isset($_SESSION['training_trials'])) {
                        $experiments_t = $_SESSION['training_trials'];
                        $experiments_t = implode(",",$experiments_t);
                } elseif (isset($_SESSION['selected_trials'])) {
                        $trials = $_SESSION['selected_trials'];
                        $experiments_t = implode(",",$trials);
                } else {
                        $experiments_t = "";
                }
                if (isset($_SESSION['training_lines'])) {
                        $training_lines = $_SESSION['training_lines'];
                } else {
                        $training_lines = "";
                }
		if (isset($_SESSION['selected_lines'])) {
			$selectedlinescount = count($_SESSION['selected_lines']);
			$lines = $_SESSION['selected_lines'];
		} else {
			$lines = "";
		}
		if (isset($_SESSION['clicked_buttons'])) {
		    $selectcount = $_SESSION['clicked_buttons'];
		    $markers = $_SESSION['clicked_buttons'];
		    $markers_str = implode(",", $_SESSION['clicked_buttons']);
		} else {
		    $markers = array();
                    $markers_str = "";
		}
		if (isset($_SESSION['training_traits'])) {
		    $phenotype = $_SESSION['training_traits'];
                    $phenotype = $phenotype[0];
                    $sql = "select phenotypes_name from phenotypes where phenotype_uid = $phenotype";
                    $res = mysql_query($sql) or die(mysql_error());
                    $row = mysql_fetch_array($res);
                    $phenolabel = $row[0];
                //} elseif (isset($_SESSION['selected_traits'])) {
                  } elseif (isset($_SESSION['phenotype'])) {
                    $phenotype = $_SESSION['phenotype'];
                    $sql = "select phenotypes_name from phenotypes where phenotype_uid = $phenotype";
                    $res = mysql_query($sql) or die(mysql_error());
                    $row = mysql_fetch_array($res);
                    $phenolabel = $row[0];
		} else {
		    $phenotype = "";
		}
                ?>
                <img alt="spinner" id="spinner" src="images/ajax-loader.gif" style="display:none;" />
                <?php
              
                if ($training_lines == "") {
                //  calculate_af($lines, $min_maf, $max_missing, $max_miss_line);
                  $lines = $_SESSION['filtered_lines'];
                } else {
                //  calculate_af($training_lines, $min_maf, $max_missing, $max_miss_line);
                  $training_lines = $_SESSION['filtered_lines'];
                }
                $markers = $_SESSION['filtered_markers'];
                $estimate = round((count($markers) + count($lines)) / 700,1);
                echo "<br>Estimated analysis time is $estimate minutes.<br>";
                if ($estimate > 1) {
                  ?>
                  Use this link to check status.<br>
                  <input type="button" value="Check Results" onclick="javascript: run_status('<?php echo $unique_str; ?>');"/>
                  <?php 
                }

                //combine the training set and the prediction set for genotype data
                $all_lines = $lines;
	        $p_uid = $_SESSION['training_traits'];
                $p_uid = $p_uid[0];
                $count_training = count($_SESSION['training_lines']);
                if (count($_SESSION['training_lines']) > 0) {
                  $selectedlines = $_SESSION['training_lines'];
                  foreach ($selectedlines as $uid) {
                    if (!in_array($uid,$all_lines)) {
                      $all_lines[] = $uid;
                    }
                  }
                }

		$dir = '/tmp/tht/';
                $filename1 = 'THTdownload_snp_p_' . $unique_str . '.txt';
                $filename8 = 'THTdownload_snp_t_' . $unique_str . '.txt';
                $filename9 = 'THTdownload_hmp_' . $unique_str . '.txt';
                $filename2 = 'THTdownload_traits_' . $unique_str . '.txt';
                $filename3 = 'THTdownload_gensel_' . $unique_str . '.R';
                $filename4 = 'THTdownload_gensel_' . $unique_str . '.png';
                $filename10 = 'THTdownload_gensel2_' . $unique_str . '.png';
                $filename5 = 'THT_process_error_' . $unique_str . '.txt';
                $filename6 = 'THT_R_error_' . $unique_str . '.txt';
                $filename7 = 'THT_result_' . $unique_str . '.csv';

                //create genotype file for prediction set
                if ($version == "V4") {
                  if ($training_lines == "") {
                    if(!file_exists($dir.$filename8)){
                      $dtype = "qtlminer";
                      $h = fopen($dir.$filename8, "w+");
                      fwrite($h,$this->type2_build_markers_download($lines,$markers,$dtype));
                      fclose($h);
                    }
                  } else {
                    //remove duplicate lines from prediction
                    foreach ($lines as $key => $value) {
                      if(in_array($value, $training_lines)){
                        unset($lines[$key]);
                      }
                    }
                    if(!file_exists($dir.$filename8)) {
                      $dtype = "qtlminer";
                      $h = fopen($dir.$filename8, "w+");
                      fwrite($h,$this->type2_build_markers_download($training_lines,$markers,$dtype));
                      fclose($h);
                    }
                    if(!file_exists($dir.$filename1)) {
                      $dtype = "qtlminer";
                      $h = fopen($dir.$filename1, "w+");
                      fwrite($h,$this->type2_build_markers_download($lines,$markers,$dtype));
                      fclose($h);
                    }
                  } 
                } elseif ($version == "V3") {
                  if ($training_lines == "") {
                    if(!file_exists($dir.$filename9)){
                      $dtype = "qtlminer";
                      $h = fopen($dir.$filename9, "w+");
                      fwrite($h,$this->type3_build_markers_download($lines,$markers,$dtype));
                      fclose($h);
                    }
                  } else {
                    if(!file_exists($dir.$filename9)){
                      $dtype = "qtlminer";
                      $h = fopen($dir.$filename9, "w+");
                      fwrite($h,$this->type3_build_markers_download($training_lines,$markers,$dtype));
                      fclose($h);
                    }  
                  }
                }

                if(!file_exists($dir.$filename2)){
                    $h = fopen($dir.$filename2, "w+");
                    $datasets_exp = "";
                    $subset = "yes";
                    fwrite($h,$this->type1_build_tassel_traits_download($experiments_t,$phenotype,$datasets_exp,$subset));
                    fclose($h);
                }
                if(!file_exists($dir.$filename3)){
                    $h = fopen($dir.$filename3, "w+");
                    $png = "png(\"$dir$filename4\", width=900, height=500)\n";
                    $png2 = "png(\"$dir$filename10\", width=600, height=500)\n";
                    $cmd1 = "snpData_p <- read.table(\"$dir$filename1\", header=TRUE, stringsAsFactors=FALSE, sep=\"\\t\", row.names=1)\n";
                    $cmd2 = "snpData_t <- read.table(\"$dir$filename8\", header=TRUE, stringsAsFactors=FALSE, sep=\"\\t\", row.names=1)\n";
                    $cmd3 = "phenoData <- read.table(\"$dir$filename2\", header=TRUE, na.strings=\"-999\", stringsAsFactors=FALSE, sep=\"\\t\", row.names=NULL)\n";
                    if ($training_lines == "") {
                      $cmd4 = "yesPredPheno <- 0\n"; #no prediction set, do cross validation
                    } else {
                      $cmd4 = "yesPredPheno <- 1\n"; #yes prediction set, calculate prediction
                    }
                    $cmd5 = "fileerr <- \"$filename6\"\n";
                    $cmd6 = "fileout <- \"$filename7\"\n";
                    $cmd7 = "phenolabel <- \"$phenolabel\"\n";
                    $cmd8 = "common_code <- \"" . $config['root_dir'] . "R/AmatrixStructure.R\"\n";
                    $cmd9 = $triallabel;
                    fwrite($h, $png);
                    fwrite($h, $png2);
                    if ($training_lines != "") {
                      fwrite($h, $cmd1);
                    }
                    fwrite($h, $cmd2);
                    fwrite($h, $cmd3);
                    fwrite($h, $cmd4);
                    fwrite($h, $cmd5);
                    fwrite($h, $cmd6);
                    fwrite($h, $cmd7);
                    fwrite($h, $cmd8);
                    fwrite($h, $cmd9);
                    fwrite($h, "model <- \"$model_opt\"\n");
                    fwrite($h, "setwd(\"/tmp/tht/\")\n");
                    fclose($h);
                }
    
                if (($version == "V4") && (isset($_SESSION['training_lines']))) {
                  if (count($_SESSION['training_lines']) < 50) {
                  echo "skip CrossValidation because traing set has less than 50 lines<br>\n";
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
		
		calculate_af($lines, $min_maf, $max_missing, $max_miss_line);
		
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
	
	 calculate_af($lines, $min_maf, $max_missing, $max_miss_line);
	
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
     *
     * modified to work with only one trait
     */
    function type1_build_tassel_traits_download($experiments, $traits, $datasets, $subset) {
      $delimiter = "\t";
      $output = '';
      $outputheader1 = '';
      $outputheader3 = "<Trial>";
      
      //only use first trait
      $pattern = "/([0-9]+)/";
      if (preg_match($pattern,$traits,$match)) {
        $traits = $match[1];
      } else {
        echo "error - can not identify trait $traits\n";
        die();
      }
      
      if (isset($_SESSION['filtered_lines'])) {
        $lines = $_SESSION['filtered_lines'];
      } else {
        die("Error: should have lines selected<br>\n");
      }
      $selectedlines = implode(",", $lines);
      $outputheader2 = "gid" . $delimiter . "pheno" . $delimiter . "trial" . $delimiter . "year";

		$sql_option = "";
		if ($subset == "yes" && count($_SESSION['filtered_lines']) > 0) {
		  $selectedlines = implode(",", $_SESSION['filtered_lines']);
		  $sql_option = " AND lr.line_record_uid IN ($selectedlines)";
                } else {
                  die("Error: should have lines selected<br>\n");
                }
		if (preg_match("/\d/",$experiments)) {
		  $sql_option .= "AND tb.experiment_uid IN ($experiments)";
		}
		if (preg_match("/\d/",$datasets)) {
		  $sql_option .= "AND ((tht_base.datasets_experiments_uid in ($datasets) AND tht_base.check_line='no') OR (tht_base.check_line='yes'))";
		}
			
          // get a list of all line names in the selected datasets and experiments,
	  // INCLUDING the check lines // AND tht_base.check_line IN ('no')
      $sql = "SELECT DISTINCT lr.line_record_name, lr.line_record_uid
               FROM line_records as lr, tht_base as tb, phenotype_data as pd
	       WHERE lr.line_record_uid=tb.line_record_uid
               AND pd.tht_base_uid = tb.tht_base_uid
               AND pd.phenotype_uid = $traits
                 $sql_option";
      $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
      while($row = mysql_fetch_array($res)) {
         $lines_names[] = $row['line_record_name'];
         $line_uid[] = $row['line_record_uid'];
      }
      $nlines = count($lines_names);
      //die($sql . "<br>" . $nlines);

          $outputheader1 = "$nlines".$delimiter."$ncols".$delimiter.$nheaderlines;
          $output = $outputheader2."\n";
	  
          //add lines from pred set
        if(isset($_SESSION['training_lines'])) {
          if (isset($_SESSION['selected_lines'])){
            $selectedlines = $_SESSION['selected_lines'];
          }
          if (isset($_SESSION['selected_trials'])) {
            $selectedtrials = $_SESSION['selected_trials'];
            $selectedtrials = implode(",",$selectedtrials);
          }
        } else {
          $selectedlines = array();
          $selectedtrials = "";
        }
        if (preg_match("/\d/",$selectedtrials)) {
          $sql_option = " WHERE tb.experiment_uid IN ($selectedtrials) AND ";
        } else{
          $sql_option = " WHERE ";
        }
        foreach ($selectedlines as $uid) {
          if (!in_array($uid,$line_uid)) {
            $sql = "SELECT line_record_name, tb.experiment_uid, experiment_year as exper 
                    from line_records as lr, tht_base as tb, experiments as exp
                    $sql_option
                    lr.line_record_uid=tb.line_record_uid
                    and tb.experiment_uid = exp.experiment_uid
                    and lr.line_record_uid = $uid";
            //echo "$sql<br>\n";
            $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
            if (preg_match("/\d/",$selectedtrials)) {	//for case where there are phenotype measurements
            while ($row = mysql_fetch_array($res)) {
              $line_name = $row[0];
              $exper = $row[1];
              $year = $row[2];
              $sql = "select pd.value as value
                     from tht_base as tb, phenotype_data as pd
                     WHERE tb.experiment_uid = $exper AND 
                     tb.line_record_uid  = $uid
                     AND pd.tht_base_uid = tb.tht_base_uid
                     AND pd.phenotype_uid = $traits";
              $res2 = mysql_query($sql) or die(mysql_error() . "<br>$sql");
              if ($row2 = mysql_fetch_array($res2)) {
                $value = $row2['value'];
              } else {
                $value = "-999";
              }

              $outline = $line_name.$delimiter.$value.$delimiter.$exper.$delimiter.$year."\n";
              $output .= $outline;
            }
            } else {	//for case where there are no phenotype measurements
            if ($row = mysql_fetch_array($res)) {
              $line_name = $row[0];
              $year = $row[2];
              $exper = 0;    //use 0 to indicate the prediction set
              $value = "-999";
              $outline = $line_name.$delimiter.$value.$delimiter.$exper.$delimiter.$year."\n";
              $output .= $outline;
            }
            }
          } else {
            //echo "dropped from prediction $uid<br>\n";
          }
        }
 
	  // loop through all the lines in the file
		for ($i=0;$i<$nlines;$i++) {
			if (preg_match("/\d/",$experiments)) {
			  $sql_option = " WHERE tb.experiment_uid IN ($experiments) AND ";
			} else {
			  $sql_option = " WHERE ";
			}
			$sql = "SELECT pd.value as value,pd.phenotype_uid,tb.experiment_uid as exper, experiment_year
					FROM tht_base as tb, phenotype_data as pd, experiments as exp
					$sql_option
						tb.line_record_uid  = $line_uid[$i]
						AND pd.tht_base_uid = tb.tht_base_uid
                                                AND tb.experiment_uid = exp.experiment_uid
						AND pd.phenotype_uid = $traits 
					GROUP BY tb.tht_base_uid, pd.phenotype_uid";
		//echo "$sql<br>\n";	
            $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
            $found = 0;
            while ($row = mysql_fetch_array($res)) {
               $found = 1;
               $outline = $lines_names[$i].$delimiter.$row['value'].$delimiter.$row['exper'].$delimiter.$row['experiment_year']."\n";
               $output .= $outline;
            }
            if ($found == 0) {
               $outline = $lines_names[$i].$delimiter."999".$delimiter."999".$delimiter."999\n";
               $output .= $outline;
            }

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
	 $output = $outputheader2."\n";
	 } else {
	 $output = $outputheader3."\n";
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
                $max_missing = 10;
                $min_maf = 5;
		
		if (isset($_GET['mm']) && !empty($_GET['mm']) && is_numeric($_GET['mm']))
			$max_missing = $_GET['mm'];
		if ($max_missing>100)
			$max_missing = 100;
		elseif ($max_missing<0)
		$max_missing = 0;
		// $firephp->log("in sort markers2");
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
		  die("error - markers should be selected before download\n");
		}
		if (count($lines)>0) {
		  $lines_str = implode(",", $lines);
		} else {
		  $lines_str = "";
                  die("error - must make line selection first<br>\n");
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
				$outputheader .= $marker_name.$delimiter;
				$marker_uid[] = $marker_id;
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
                      $i=0;
                      foreach ($marker_list as $marker_id) {
                        if (isset($marker_lookup[$marker_id])) {
                          $outarray2[]=$lookup[""];
                        }
                        $i++;
                      }
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
	  die("error - markers should be selected before download");
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
	 $sql = "select markers.marker_uid,  mim.chromosome, mim.start_position from markers, markers_in_maps as mim, map, mapset
	 where markers.marker_uid IN ($markers_str)
	 AND mim.marker_uid = markers.marker_uid
	 AND mim.map_uid = map.map_uid
	 AND map.mapset_uid = mapset.mapset_uid
	 AND mapset.mapset_uid = $selected_map 
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
	 $outputheader = "rs\talleles\tchrom\tpos";
	 $sql = "select line_record_name from line_records where line_record_uid IN ($lines_str)";
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

          $lookup = array(
           'AA' => -1,
           'BB' =>  1,
           '--' => 'NA',
           'AB' =>  0,
           'BA' =>  0,
           '' => 'NA'
          );

	     $sql = "select A_allele, B_allele, mim.chromosome, mim.start_position from markers, markers_in_maps as mim, map, mapset where markers.marker_uid = $marker_id
	         AND mim.marker_uid = markers.marker_uid
	         AND mim.map_uid = map.map_uid
	         AND map.mapset_uid = mapset.mapset_uid
	         AND mapset.mapset_uid = $selected_map";
	     $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
	     if ($row = mysql_fetch_array($res)) {
                $chrom = $row[2];
                if (preg_match('/[0-9]+/',$chrom, $match)) {
                  $chrom = $match[0];
                  $pos = 100 * $row[3];
                } else {
                  $chrom = 0;
                  $pos = $pos_index;
                  $pos_index += 10;
                }
	     } else {
	        $chrom = 0;
	        $pos = $pos_index;
                $pos_index += 10;
	     }
	     $output .= "$marker_name\t$allele\t$chrom\t$pos";
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
		// $firephp->log("in sort markers".$max_missing."  ".$min_maf);
		
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
