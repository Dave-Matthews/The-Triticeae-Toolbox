<?php
/**
 * Download Gateway New
 *
 * PHP version 5.3
 * Prototype version 1.5.0
 *
 * @author  Clay Birkett <clb343@cornell.edu>
 * @license http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link    http://triticeaetoolbox.org/wheat/downloads/downloads.php
 *
 *
 * The purpose of this script is to provide the user with an interface
 * for downloading certain kinds of files from THT.
 */

set_time_limit(0);
ini_set('memory_limit', '4G');

// For live website file
require 'config.php';
require_once $config['root_dir'].'includes/bootstrap.inc';
set_include_path(GET_INCLUDE_PATH() . PATH_SEPARATOR . '../pear/');
date_default_timezone_set('America/Los_Angeles');

require_once $config['root_dir'].'includes/MIME/Type.php';

// connect to database
connect();
$mysqli = connecti();

require_once $config['root_dir'].'downloads/marker_filter.php';

new Downloads($_GET['function']);

/**
 * Using a PHP class to implement the "Download Gateway" feature
 *
 * @author  Clay Birkett <clb343@cornell.edu>
 * @license http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link    http://triticeaetoolbox.org/wheat/downloads/downloads.php
 **/
class Downloads
{
    /**
     * Delimiter used for output files
     */
    public $delimiter = "\t";
    
    /**
     * Using the class's constructor to decide which action to perform
     *
     * @param string $function action to perform
     */
    public function __construct($function = null)
    {
        switch ($function) {
            case 'genomic_prediction':
                $this->genomic_prediction();
                break;
            case 'run_histo':
                $this->run_histo();
                break;
            case 'run_gwa':
                $this->run_gwa();
                break;
            case 'run_gwa2':
                $this->run_gwa2();
                break;
            case 'run_rscript':
                $this->run_rscript();
                break;
            case 'run_rscript2':
                $this->run_rscript2();
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
                echo $this->filterLines();
                break;
            default:
                $this->type1Select();
                break;
        }
    }

    /**
     * load header and footer then check session to use existing data selection
     */
    private function type1Select()
    {
        global $config;
        require_once $config['root_dir'].'theme/normal_header.php';
        $phenotype = "";
        $lines = "";
        $markers = "";
        $saved_session = "";
        $this->type1Checksession();
        require_once 'downloads/select-map.php';
        require_once $config['root_dir'].'theme/footer.php';
    }

    /**
     * Checks the session variable, if there is lines data saved then go directly to the lines menu
     */
    private function type1Checksession()
    {
        global $mysqli;
        ?>
        <style type="text/css">
			th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
			table {background: none; border-collapse: collapse}
			td {border: 1px solid #eee !important;}
			h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
	</style>
        <link rel="stylesheet" href="//code.jquery.com/ui/1.11.0/themes/smoothness/jquery-ui.css">
        <script type="text/javascript" src="downloads/download_gs03.js"></script>
        <script type="text/javascript" src="downloads/downloadsjq02.js"></script>
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
        if (empty($_SESSION['phenotype'])) {
            echo "<font color=red>Select a set of traits and phenotype trials</font><br><br>";
        } elseif (empty($_SESSION['selected_lines'])) {
            echo "<br>Select validation set containing trait measurements to plot prediction vs observed. ";
            echo "<a href=";
            echo $config['base_url'];
            echo "downloads/select_all.php>Wizard</a><br>";
            echo "Select prediction set without trait measurements to predict the traits. ";
            echo "<a href=";
            echo $config['base_url'];
            echo "pedigree/line_properties.php>Lines by Properties</a>, ";
            echo "<a href=";
            echo $config['base_url'];
            echo "downloads/select_genotype.php>Lines by Genotype Experiment</a><br>";
        } elseif (empty($_SESSION['phenotype']) && empty($_SESSION['training_traits'])) {
            echo "Please select traits before using this feature.<br><br>";
            echo "<a href=";
            echo $config['base_url'];
            echo "phenotype/phenotype_selection.php>Select Traits</a><br><br>";
            echo "<a href=";
            echo $config['base_url'];
            echo "downloads/select_all.php>Wizard (Lines, Traits, Trials)</a>";
        } elseif (empty($_SESSION['selected_map'])) {
            if (isset($_SESSION['geno_exps'])) {
                $geno_exp = $_SESSION['geno_exps'];
                $geno_str = $geno_exp[0];
                $sql = "select marker_uid from allele_bymarker_exp_101 where experiment_uid = $geno_str and pos is not null limit 10";
                $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . $sql);
                if ($row = mysqli_fetch_array($res)) {
                } else {
                    echo "<font color=red>Select a genetic map.</font>";
                    echo "<input type=button value=\"Genetic map\" onclick=\"javascript: select_map()\"><br>";
                }
            } else {
                echo "<font color=red>Select a genetic map.</font>";
                echo "<input type=button value=\"Genetic map\" onclick=\"javascript: select_map()\"><br>";
            }
        }
        if (!empty($_SESSION['training_lines']) && !empty($_SESSION['selected_lines'])) {
            if (empty($_SESSION['selected_trials'])) {
                echo "<tr><td>Prediction<td>";
            } else {
                echo "<tr><td>Validation<td>";
                $tmp = $_SESSION['selected_trials'];
                $e_uid = implode(",", $tmp);
                $sql = "select trial_code from experiments where experiment_uid IN ($e_uid)";
                $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . $sql);
                while ($row = mysqli_fetch_array($res)) {
                    echo "$row[0]<br>";
                }
            }
   
            $count = count($_SESSION['selected_lines']);
            $markers = $_SESSION['filtered_markers'];
            $estimate = count($markers) + count($lines);
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
                if (in_array($uid, $tmp1)) {
                    $count_dup++;
                } else {
                    $count++;
                }
            }
            if ($count < 5) {
                 echo " <font color=red>(Error - $count unique lines in prediction set)";
            }
        }
        echo "</table>";
        if ($count_dup > 0) {
            if (empty($_SESSION['selected_trials'])) {
                 echo " Warning - $count_dup lines removed from prediction set because they are in training set";
            } else {
                 echo " Warning - $count_dup lines removed from validation set because they are in training set";
            }
        }
                $min_maf = 5;
                $max_missing = 10;
                $max_miss_line = 10;
                $unique_str = chr(rand(65, 80)).chr(rand(65, 80)).chr(rand(65, 80)).chr(rand(65, 80));
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
                  <div id="filter" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%"></div>
                  <div id="step1" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%">
                  <img alt="spinner" id="spinner" src="images/ajax-loader.gif" style="display:none;" /></div>
                  <div id="step2" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%">
                  <table>
                  <!--tr><td><td>fixed effect (trial is always included)-->
                  <tr><td><input type="button" value="G-BLUP Analysis" onclick="javascript:load_genomic_prediction(<?php echo $estimate; ?>)">
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

    /**
     * filters markers and lines based on settings
     */
    private function filterLines()
    {
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
        if (isset($_SESSION['training_lines'])) {
            $training_lines = $_SESSION['training_lines'];
        } else {
            $training_lines = "";
        }
        if (isset($_SESSION['geno_exps'])) {
            $experiment_uid = $_SESSION['geno_exps'][0];
            calculate_afe($experiment_uid, $min_maf, $max_missing, $max_miss_line);
            findCommonLines($lines);
        } elseif ($training_lines == "") {
            calculate_af($lines, $min_maf, $max_missing, $max_miss_line);
        } else {
            calculate_af($training_lines, $min_maf, $max_missing, $max_miss_line);
        }
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
        <table>
        <tr><td><b>Genome Wide Association (consensus genotype)</b><br>
        1. Select a <a href="downloads/select_all.php">set of lines, trait, and trials</a> (one trait).<br>
        2. Select the <a href="maps/select_map.php">genetic map</a> which has the best coverage for this set.<br>
        3. Return to this page and select model options then GWAS Analysis<br>
    
        <td><b>Genome Wide Association (single genotype experiment)</b><br>
        1. Select a <a href="downloads/select_genotype.php">set of lines by genotype experiment</a>.<br>
        2. Select a <a href="phenotype/phenotype_selection.php">trait and phenotype trial</a>.<br>
        3. Select the <a href="maps/select_map.php">genetic map</a> which has the best coverage for this set.<br>
        4. Return to this page and select model options then GWAS Analysis<br> 

        <tr><td colspan=2><b>Genomic Prediction</b><br>
        1. Select a <a href="downloads/select_all.php">set of lines, trait, and trials</a> (one trait).<br>
        2. Return to this page and select G-BLUP Analysis for cross-validation of the training set. Then save Training Set.<br>
        3. To select a validation set, select a new set of lines using a different trial, then return to this page for analysis.<br>
        4. To select a prediction set, select a new set of lines without phenotype measurements, then return to this page for analysis.<br>
        </table>
        
        <p><a href="downloads/genomic-tools.php">Additional notes on GWAS and G-BLUP methods</a><br>
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
      } elseif (!empty($_SESSION['phenotype']) && !empty($_SESSION['selected_trials']) ) {
        ?>
        <table>
        <tr><td>Traits<td>Trials<td>Lines<td>Genetic Map
        <tr><td>
        <?php
        $traits = $_SESSION['phenotype'];
        $map = $_SESSION['selected_map'];
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
        $count = count($_SESSION['selected_lines']);
        echo "$count<td>";
        if (isset($_SESSION['geno_exps'])) {
            $geno_exp = $_SESSION['geno_exps'];
            $geno_str = $geno_exp[0];
            $sql = "select marker_uid from allele_bymarker_exp_101 where experiment_uid = $geno_str and pos is not null limit 10";
            $res = mysql_query($sql) or die(mysql_error() . $sql);
            if ($row = mysql_fetch_array($res)) {
                $sql = "select trial_code from experiments where experiment_uid = $geno_str";
                $res = mysql_query($sql) or die(mysql_error() . $sql);
                $row = mysql_fetch_array($res);
                $name = $row[0];
                echo "using map from genotype experiment<br>$name";
            } elseif (isset($_SESSION['selected_map'])) {
                $sql = "select mapset_name from mapset where mapset_uid = $map";
                $res = mysql_query($sql) or die(mysql_error());
                $row = mysql_fetch_assoc($res);
                $map_name = $row['mapset_name'];
                echo "$map_name";
            }
        } elseif (isset($_SESSION['selected_map'])) {
            $sql = "select mapset_name from mapset where mapset_uid = $map";
            $res = mysql_query($sql) or die(mysql_error());
            $row = mysql_fetch_assoc($res);
            $map_name = $row['mapset_name'];
            echo "$map_name";
        }
        echo "</table>";
        if ($count < 10) {
            echo "<font color=red>Warning: analysis may fail with only $count lines selected</font><td>";
        }
        $min_maf = 5;
        $max_missing = 10;
        $max_miss_line = 10;
        $lines = $_SESSION['selected_lines'];
        $count_markers = calculate_db($lines, $min_maf, $max_missing, $max_miss_line);
        $count_lines = count($lines);
        $estimate = ($count_markers * $count_lines) / 10000;
        if ($count > 0) {
          ?>
          <p>Minimum MAF &ge; <input type="text" name="mmaf" id="mmaf" size="2" value="<?php echo ($min_maf) ?>" />%
        &nbsp;&nbsp;&nbsp;&nbsp;
        Remove markers missing &gt; <input type="text" name="mmm" id="mmm" size="2" value="<?php echo ($max_missing) ?>" />% of data
        &nbsp;&nbsp;&nbsp;&nbsp;
        <?php
            if (!isset($_SESSION['geno_exps'])) { 
            ?>
        Remove lines missing &gt; <input type="text" name="mml" id="mml" size="2" value="<?php echo ($max_miss_line) ?>" />% of data
        &nbsp;&nbsp;&nbsp;&nbsp;
            <?php
        } else {
            ?>
            <input type="hidden" name="mml" id="mml">
            <?php
        }
        ?>
          <input type="button" value="Filter Lines and Markers" onclick="javascript:filter_lines();"/>
          </div>
          <div id="filter" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%">
          <img alt="spinner" id="spinner" src="images/ajax-loader.gif" style="display:none;" /></div>
          <div id="step1" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%"></div>
          <div id="step2" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%">

          <table border=0>
          <tr><td rowspan=2>
          <input type="button" value="Analyze" onclick="javascript:load_genomic_gwas(<?php echo $estimate; ?>)"> GWAS
          <td>principal components
          <td><select name="model2" onchange="javascript: update_fixed(this.value)">
          <option>0</option>
          <option>1</option>
          <option>2</option>
          <option>3</option>
          <option>4</option>
          <option>5</option>
          </select>
          <tr><td>method
          <td><input type="radio" name="P3D" checked value="TRUE">EMMAX (faster but can underestimate significance)<br>
          <input type="radio" name="P3D" value="FALSE">EMMA with REML
          <tr><td><input type="button" value="Analyze" onclick="javascript:load_genomic_prediction('<?php echo $estimate; ?>')"> G-BLUP
          <td>
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
    
    /**
     * call R program for displaying histograms
     */
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
            $cmd1 = "phenoData <- as.matrix(read.delim(\"$dir$filename2\", header=TRUE, na.strings=\"-999\", stringsAsFactors=FALSE, sep=\"\\t\", row.names=1))\n";
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

    private function display_gwas_hits($h) {
        echo "Top five marker scores from GWAS analysis<br>";
        echo "<table><tr><td>marker<td>chrom<td>pos<td>value<td>external link (resource name)";
        $line= fgetcsv($h);
        while ($line= fgetcsv($h)) {
            $link = "";
            $sql = "select value, name_annotation, linkout_string_for_annotation
                from markers, marker_annotations, marker_annotation_types
                where markers.marker_uid = marker_annotations.marker_uid
                and marker_annotations.marker_annotation_type_uid = marker_annotation_types.marker_annotation_type_uid
                and marker_name = \"$line[1]\"";
            $res = mysql_query($sql) or die(mysql_error());
            while ($row = mysql_fetch_assoc($res)) {
                $reg_pattern = "XXXX";
                $replace_string = $row['value'];
                $name = $row['name_annotation'];
                $source_string = $row['linkout_string_for_annotation'];
                $linkString = ereg_replace($reg_pattern, $replace_string, $source_string);
                if ($link == "") {
                    if ($linkString != "") {
                      $link = "<a href=\"$linkString\" target=\"_new\">$replace_string</a> ($name)";
                    }
                } else {
                    if ($linkString != "") {
                      $link .= "<br><a href=\"$linkString\" target=\"_new\">$replace_string</a> ($name)";
                    }
                }
            }
            if ($count < 5) {
	      $markerlink = "<a href=$config[base_url]view.php?table=markers&name=$line[1]>$line[1]</a>";
	      echo "<tr><td>$markerlink<td>$line[2]<td>$line[3]<td>$line[4]<td>$link\n";
            }
            $count++;
        }
        fclose($h);
        echo "</table>";
    }

    /**
     * display gwas results
     */
    private function status_gwas() {
        $unique_str = $_GET['unq'];
        $dir = '/tmp/tht/';
        $found = 1;
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
        } else {
          //echo "$filename7 not ready<br>\n";
          $found = 0;
        }
        if (file_exists("/tmp/tht/$filename10")) {
        } else {
          //echo "$filename10 not ready<br>\n";
          $found = 0;
        }
        if (file_exists("/tmp/tht/$filename4")) {
        } else {
          //echo "$filename4 not ready<br>\n";
          $found = 0;
        }
        if (file_exists("/tmp/tht/$filename5")) {
           $h = fopen("/tmp/tht/$filename5", "r");
           while ($line=fgets($h)) {
               echo "$line<br>\n";
           }
           fclose($h);
        }
        if (file_exists("/tmp/tht/$filename3")) {
	  // Extract the Trait name from the .R file.
	  $h = fopen("/tmp/tht/$filename3", "r");
	  while ($line=fgets($h)) {
            if (strpos($line, 'phenolabel') !== FALSE) {
              $traitname = preg_replace('/phenolabel <- "(.*)"/', '$1', $line);
	    }
	  }
	  fclose($h);
        }
        if ($found) {
            print "<img src=\"/tmp/tht/$filename7\" width=\"800\"/><br>";
            print "<img src=\"/tmp/tht/$filename10\" width=\"800\"/><br>";
            print "<img src=\"/tmp/tht/$filename4\" width=\"800\" /><br>";
	    print "Trait: <b>$traitname</b><p>";
            print "<a href=/tmp/tht/$filename1 target=\"_blank\" type=\"text/csv\">Export GWAS results to CSV file</a> ";
            print "with columns for marker name, chromosome, position, marker score<br><br>";
            print "<a href=/tmp/tht/$filenameK target=\"_blank\" type=\"text/csv\">Export Kinship matrix</a><br><br>";
            $count = 0;
            $h = fopen("/tmp/tht/$filename1", "r");
            if ($h) {
                $this->display_gwas_hits($h);
            }
        } else {
          if (isset($_SESSION['filtered_ines'])) {
              $lines = $_SESSION['filtered_lines'];
          } else {
              $lines = $_SESSION['selected_lines'];
          }
          if (isset($_SESSION['filtered_markers'])) {
              $markers = $_SESSION['filtered_markers'];
          } else {
              $markers = $_SESSION['geno_exps_cnt'];
          }
          $estimate = count($lines) * count($markers);
          $estimate = round($estimate/6000000,1);
          echo "Results not ready yet. Estimated analysis time is $estimate minutes using default options.<br>";
          ?>
          <font color=red>Select the "Check Results" button to retrieve results.<br>
          <input type="button" value="Check Results" onclick="javascript: run_status('<?php echo $unique_str; ?>');"/>
          </font>
          <?php
        }
    }

    /**
     * display genomic prediction results
     */
    private function status_pred() {
        $unique_str = $_GET['unq'];
        $dir = '/tmp/tht/';
        $found = 1;
        $filename1 = 'THTdownload_hapmap_' . $unique_str . '.txt';
        $filename2 = 'THTdownload_traits_' . $unique_str . '.txt';
        $filename3 = 'THTdownload_gensel_' . $unique_str . '.R';
        $filename10 = 'THTdownload_gensel2_' . $unique_str . '.png';
        $filename4 = 'THTdownload_gensel_' . $unique_str . '.png';
        $filename5 = 'THT_process_error_' . $unique_str . '.txt';
        $filename6 = 'THT_R_error_' . $unique_str . '.txt';
        $filename7 = 'THT_result_' . $unique_str . '.csv';
        if (file_exists("/tmp/tht/$filename10")) {
                  print "<img src=\"/tmp/tht/$filename10\" /><br>";
        } else {
            $found = 0;
        }
        if (file_exists("/tmp/tht/$filename4")) {
             print "<img src=\"/tmp/tht/$filename4\" /><br>";
             if (isset($_SESSION['selected_trials'])) {
                  print "<a href=/tmp/tht/$filename7 target=\"_blank\" type=\"text/csv\">Export prediction to CSV file</a><br><br>";
             } else {
                  print "Cross-validation of training set using 5 folds and 2 repeats.<br>\n";
                  print "<a href=/tmp/tht/$filename7 target=\"_blank\" type=\"text/csv\">Export Cross-validated prediction to CSV file</a><br><br>";
             }
        } else {
            $found = 0;
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
        if ($found == 0) {
          $lines = $_SESSION['filtered_lines'];
          $markers = $_SESSION['filtered_markers'];
          $estimate = count($lines) + count($markers);
          $estimate = round($estimate/700,1);
          echo "Results not ready yet. Estimated analysis time is $estimate minutes.<br>";
          ?>
          <font color=red>Select the "Check Results" button to retrieve results.<br>
          <input type="button" value="Check Results" onclick="javascript: run_status('<?php echo $unique_str; ?>');"/>
          </font>
          <?php
        }
    }
  
    /**
     * display results from GWAS
     */
    private function run_gwa() {
        $unique_str = $_GET['unq'];
        $model_opt = $_GET['fixed2'];
        $p3d = $_GET['p3d'];
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
        $res = mysql_query($sql) or die(mysql_error() . $sql);
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
            $png1 = "png(\"$dir$filename4\", width=1200, height=400)\n";
            $png2 = "png(\"$dir$filename7\", width=1200, height=400)\n";
            $png3 = "png(\"$dir$filename10\", width=1200, height=400)\n"; 
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
            fwrite($h, "p3d <- $p3d\n");
            fwrite($h, "setwd(\"/tmp/tht/\")\n");
            fclose($h);
        }
        exec("cat /tmp/tht/$filename3 R/GSforGWA.R | R --vanilla > /dev/null 2> /tmp/tht/$filename5");
        if (file_exists("/tmp/tht/$filename7")) {
                  print "<img src=\"/tmp/tht/$filename7\" width=\"800\" /><br>";
        } else {
                  echo "Error in R script<br>\n";
                  echo "cat /tmp/tht/$filename3 R/GSforT3.R | R --vanilla <br>";
        }
        if (file_exists("/tmp/tht/$filename10")) {
                  print "<img src=\"/tmp/tht/$filename10\" width=\"800\"/><br>";
        }
        if (file_exists("/tmp/tht/$filename4")) {
            print "<img src=\"/tmp/tht/$filename4\" width=\"800\" /><br>";
	    print "Trait: $phenolabel<p>";
            print "<a href=/tmp/tht/$filename1 target=\"_blank\" type=\"text/csv\">Export GWAS results to CSV file</a> ";
            print "with columns for marker name, chromosome, position, marker score<br><br>";
            print "<a href=/tmp/tht/$filenameK target=\"_blank\" type=\"text/csv\">Export Kinship matrix</a><br><br>";
            $count = 0;
            $h = fopen("/tmp/tht/$filename1", "r");
            if($h) {
                $this->display_gwas_hits($h);
            } else {
                echo "error - could not open $filename1\n";
            }
        }
        if (file_exists("/tmp/tht/$filename5")) {
           $h = fopen("/tmp/tht/$filename5", "r");
           while ($line=fgets($h)) {
               echo "$line<br>\n";
           }
           fclose($h);
        } 
    } 
   
    /**
     * run GWAS results in background and notify when complete
     */
    private function run_gwa2() {
        global $config;
        $unique_str = $_GET['unq'];
        $model_opt = $_GET['fixed2'];
        $p3d = $_GET['p3d'];
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
            $png1 = "png(\"$dir$filename4\", width=1200, height=400)\n";
            $png2 = "png(\"$dir$filename7\", width=1200, height=400)\n";
            $png3 = "png(\"$dir$filename10\", width=1200, height=400)\n";
            $png4 = "dev.set(3)\n";
            $cmd3 = "phenoData <- read.table(\"$dir$filename2\", header=TRUE, na.strings=\"-999\", stringsAsFactors=FALSE, sep=\"\\t\", row.names=NULL)\n";
            $cmd4 = "hmpData <- read.table(\"$dir$filename9\", header=TRUE, stringsAsFactors=FALSE, sep=\"\\t\", check.names = FALSE)\n";
            $cmd5 = "phenolabel <- \"$phenolabel\"\n";
            $cmd6 = "fileerr <- \"$dir$filename6\"\n";
            $cmd7 = "fileout <- \"$filename1\"\n";
            $cmd8 = "model_opt <- \"$model_opt\"\n";
            $cmd9 = "fileK <- \"$filenameK\"\n";
            if (isset($_SESSION['username'])) {
              $emailAddr = $_SESSION['username'];
              $emailAddr = "email <- \"$emailAddr\"\n";
              fwrite($h, $emailAddr);
              $result_url = $config['base_url'] . "gensel.php?function=gwas_status&unq=$unique_str";
              $result_url = "result_url <- \"$result_url\"\n";
              fwrite($h, $result_url);
            } 
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
            fwrite($h, "p3d <- $p3d\n");
            fwrite($h, "setwd(\"/tmp/tht/\")\n");
            fclose($h);
        }
        exec("cat /tmp/tht/$filename3 R/GSforGWA.R | R --vanilla > /dev/null 2> /tmp/tht/$filename5 &");
       
        if (isset($_SESSION['filtered_lines'])) {
            $lines = $_SESSION['filtered_lines']; 
        } else {
            $lines = $_SESSION['selected_lines'];
        }
        if (isset($_SESSION['filtered_markers'])) {
            $markers = $_SESSION['filtered_markers'];
        } else {
            $markers = $_SESSION['geno_exps_cnt'];
        }
        $estimate = count($lines) * count($markers);
        $estimate = round($estimate/600000,1);
        echo "Estimated analysis time is $estimate minutes using default options.<br>";
        $emailAddr = $_SESSION['username'];
        if (isset($_SESSION['username'])) {
          echo "An email will be sent to $emailAddr when the job is complete<br>\n";
        } else {
          echo "If you <a href=login.php>Login</a> a notification will be sent upon completion<br>\n";
        }
        ?>
        <font color=red>Select the "Check Results" button to retrieve results.<br>
        <input type="button" value="Check Results" onclick="javascript: run_status('<?php echo $unique_str; ?>');"/>
        </font>
        <?php
    }

    /**
     * run rrBLUP R script for genomic prediction
     */
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
             if (isset($_SESSION['selected_trials'])) {
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
     * run rrBLUP R script in background and notify when complete
     */
    private function run_rscript2() {
    	$unique_str = $_GET['unq'];
    	$filename1 = 'THTdownload_hapmap_' . $unique_str . '.txt';
    	$filename2 = 'THTdownload_traits_' . $unique_str . '.txt';
    	$filename3 = 'THTdownload_gensel_' . $unique_str . '.R';
    	$filename10 = 'THTdownload_gensel2_' . $unique_str . '.png';
    	$filename4 = 'THTdownload_gensel_' . $unique_str . '.png';
    	$filename5 = 'THT_process_error_' . $unique_str . '.txt';
    	$filename6 = 'THT_R_error_' . $unique_str . '.txt';
    	$filename7 = 'THT_result_' . $unique_str . '.csv';
    	exec("cat /tmp/tht/$filename3 R/GSforT34.R | R --vanilla > /dev/null 2> /tmp/tht/$filename5 &");
    	$lines = $_SESSION['filtered_lines'];
    	$markers = $_SESSION['filtered_markers'];
    	$estimate = count($lines) + count($markers);
    	$estimate = round($estimate/700,1);
    	echo "Estimated analysis time is $estimate minutes.<br>";
    	$emailAddr = $_SESSION['username'];
    	if (isset($_SESSION['username'])) {
    		echo "An email will be sent to $emailAddr when the job is complete<br>\n";
    	} else {
    		echo "If you <a href=login.php>Login</a> a notification will be sent upon completion<br>\n";
        }
    	?>
    	<font color=red>Select the "Check Results" button to retrieve results.<br>
    	<input type="button" value="Check Results" onclick="javascript: run_status('<?php echo $unique_str; ?>');"/>
    	</font>
    	<?php
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

            if (isset($_SESSION['geno_exps'])) {
                $count = 0;
                $experiments_g = $_SESSION['geno_exps'];
                $geno_str = $experiments_g[0];
                $sql = "SELECT marker_uid from allele_bymarker_exp_ACTG where experiment_uid = $geno_str";
                $res = mysql_query($sql) or die(mysql_error());
                while ($row = mysql_fetch_row($res)) {
                    $uid = $row[0];
                    $markers[] = $uid;
                    $count++;
                }
                echo "$count markers found\n";
            } else {
                $markers = $_SESSION['filtered_markers'];
            }

                ?>
                <img alt="spinner" id="spinner" src="images/ajax-loader.gif" style="display:none;" />
                <?php
              
                if ($training_lines == "") {
                  //calculate_af($lines, $min_maf, $max_missing, $max_miss_line);
                  $lines = $_SESSION['filtered_lines'];
                } else {
                  //calculate_af($training_lines, $min_maf, $max_missing, $max_miss_line);
                  $training_lines = $_SESSION['filtered_lines'];
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
                  if (isset($_SESSION['geno_exps'])) {
                    $experiment = $_SESSION['geno_exps'];
                    $geno_str = $experiment[0]; 
                    $tmp = count($markers);
                    if(!file_exists($dir.$filename9)){
                      $dtype = "qtlminer";
                      $h = fopen($dir.$filename9, "w+");
                      $output = type4BuildMarkersDownload($geno_str, $min_maf, $max_missing, $dtype, $h);
                      fclose($h);
                    }
                  } elseif ($training_lines == "") {
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
                    $cmd5 = "fileerr <- \"$filename6\"\n";
                    $cmd6 = "fileout <- \"$filename7\"\n";
                    $cmd7 = "phenolabel <- \"$phenolabel\"\n";
                    $cmd8 = "common_code <- \"" . $config['root_dir'] . "R/AmatrixStructure.R\"\n";
                    $cmd9 = $triallabel;
                    if (isset($_SESSION['username'])) {
                      $emailAddr = $_SESSION['username'];
                      $emailAddr = "email <- \"$emailAddr\"\n";
                      fwrite($h, $emailAddr);
                      $result_url = $config['base_url'] . "gensel.php?function=pred_status&unq=$unique_str";
                      $result_url = "result_url <- \"$result_url\"\n";
                      fwrite($h, $result_url);
                    }

                    fwrite($h, $png);
                    fwrite($h, $png2);
                    if ($training_lines != "") {
                      fwrite($h, $cmd1);
                    }
                    fwrite($h, $cmd2);
                    fwrite($h, $cmd3);
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
     * for R script the line names have to be quoted or special characters will cause problems
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
               $outline = "'$lines_names[$i]'".$delimiter.$row['value'].$delimiter.$row['exper'].$delimiter.$row['experiment_year']."\n";
               $output .= $outline;
            }
            if ($found == 0) {
               $outline = "'$lines_names[$i]'".$delimiter."999".$delimiter."999".$delimiter."999\n";
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
	  die("<font color=red>Error - markers should be selected before analysis</font>");
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
         order by mim.chromosome, CAST(1000*mim.start_position as UNSIGNED), BINARY markers.marker_name";
	 $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
	 while ($row = mysql_fetch_array($res)) {
           $marker_uid = $row[0];
           $chr = $row[1];
           $pos = $row[2];
	   $marker_list_mapped[$marker_uid] = $pos;
           $marker_list_chr[$marker_uid] = $chr;
	 }

         $marker_list_all = $marker_list_mapped;	
	 //generate an array of selected markers and add map position if available
         $sql = "select marker_uid, marker_name, A_allele, B_allele, marker_type_name from markers, marker_types
         where marker_uid IN ($markers_str)
         AND markers.marker_type_uid = marker_types.marker_type_uid
         order by BINARY marker_name";
         $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
         while ($row = mysql_fetch_array($res)) {
           $marker_uid = $row[0];
           $marker_name = $row[1];
           if (isset($marker_list_all[$marker_uid])) {
           } else {
             $marker_list_all[$marker_uid] = 0;
           }
           if (preg_match("/[A-Z]/",$row[2]) && preg_match("/[A-Z]/",$row[3])) {
                $allele = $row[2] . "/" . $row[3];
           } elseif (preg_match("/DArT/",$row[4])) {
                $allele = $row[2] . "/" . $row[3];
           } else {
                $allele = "N/N";
           }
           $marker_list_name[$marker_uid] = $marker_name;
           $marker_list_allele[$marker_uid] = $allele;
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
	 foreach ($marker_list_all as $marker_id => $val) {
	  $marker_idx = $marker_idx_list[$marker_id];
          $marker_name = $marker_list_name[$marker_id];
          $allele = $marker_list_allele[$marker_id];

          $lookup = array(
           'AA' => 1,
           'BB' => -1,
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
             $outarray2 = array();
             $sql = "select marker_name, alleles from allele_bymarker where marker_uid = $marker_id";
             $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
             if ($row = mysql_fetch_array($res)) {
               $alleles = $row[1];
               $outarray = explode(',',$alleles);
               foreach ($outarray as $key=>$allele) {
                 $line_id = $line_list[$key];
                 if (isset($line_lookup[$line_id])) {
                   $outarray2[]=$lookup[$allele];
                 }
               }
               $allele_str = implode("\t",$outarray2);
               $output .= "$marker_name\t$allele\t$chrom\t$pos";
               $output .= "\t$allele_str\n";
             } else {
               echo "Error - could not find marker_uid $marker_id<br>\n";
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

}// end class
