<?php
/**
 * Download Gateway New
 * 
 * PHP version 5.3
 * Prototype version 1.5.0
 * 
 * @category PHP
 * @package  T3
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @version  GIT: 2
 * @link     http://triticeaetoolbox.org/wheat/histo.php
 * 
 * The purpose of this script is to generate one or more histograms of phenotype data
 *
 */

require_once 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';

connect();

new Downloads($_GET['function']);

class Downloads
{
    /** 
     * Using the class's constructor to decide which action to perform
     * @param string $function action to perform
     */
    public function __construct($function = null)
    {
        switch($function)
        {
        case 'run_histo':
            $this->run_histo();
            break;
        case "download_session_v4":
            $this->type1_session(V4);
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

    private function type1_checksession() {
      ?>
      <style type="text/css">
                        th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
                        table {background: none; border-collapse: collapse}
                        td {border: 1px solid #eee !important;}
                        h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
      </style>
      <script type="text/javascript" src="downloads/download_gs.js"></script>
      <h2>Histogram of selected phenotypes</h2>
      <div id="step1" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%">
      Display a histogram of the selected phenotype data for each trial<br><br>
      <?php
      if (isset($_SESSION['selected_traits'])) {
          $phenotype = $_SESSION['selected_traits'];
          echo "<table><tr><td><b>Trait</b><td>";
          foreach ($phenotype as $pheno) {
              $sql = "select phenotypes_name, unit_name from phenotypes, units
               where phenotypes.unit_uid = units.unit_uid
               and phenotype_uid = $pheno";
            $res = mysql_query($sql) or die(mysql_error());
            $row = mysql_fetch_array($res);
            $phenolabel = $row[0];
            ?>
            <tr><td><?php echo $phenolabel ?><td><input type="button" value="Histogram" onclick="javascript:load_histogram('<?php echo $pheno; ?>')">
            <?php 
          }
        ?>
        </table></div>
        <div id="step2" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%">
        <img alt="spinner" id="spinner" src="images/ajax-loader.gif" style="display:none;" />
        </div>
        <div id="step3" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%"></div>
        <div id="step4" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%"></div>
        <div id="step5" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%"></div>
        <?php
      } else {
        echo "Select a set of <a href=\"downloads/select_all.php\">lines, trials, and traits.</a><br></div>\n";
      }
      echo "</div>";
    }

    private function run_histo() {
        $unique_str = $_GET['unq'];
        $phenotype = $_GET['pheno'];
        $dir = '/tmp/tht/';
        $filename1 = 'THTdownload_hapmap_' . $unique_str . '.txt';
        $filename2 = 'THTdownload_traits_' . $unique_str . '.txt';
        $filename3 = 'THTdownload_histo_' . $unique_str . '.R';
        $filename4 = 'THTdownload_histo_' . $unique_str . '.png';
        $filename5 = 'process_error_histo_' . $unique_str . '.txt';
        //if (isset($_SESSION['selected_traits'])) {
        //    $phenotype = $_SESSION['selected_traits'];
        //    $phenotype = $phenotype[0];
        //}
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
        if (file_exists("/tmp/tht/$filename2")) {
          exec("cat /tmp/tht/$filename3 R/GShisto.R | R --vanilla > /dev/null 2> /tmp/tht/$filename5");
        } else {
          die("Error: no file for analysis<br>\n");
        }
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
    function type1_build_tassel_traits_download($experiments, $traits, $datasets) {
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

      if (isset($_SESSION['selected_lines'])) {
        $lines = $_SESSION['selected_lines'];
      } else {
        die("Error: should have lines selected<br>\n");
      }
      $selectedlines = implode(",", $lines);
      $outputheader2 = "gid" . $delimiter . "pheno" . $delimiter . "trial" . $delimiter . "year";

      $sql_option = "";
      if (preg_match("/\d/",$experiments)) {
         $sql_option .= "AND tb.experiment_uid IN ($experiments)";
      }
      if (preg_match("/\d/",$datasets)) {
         $sql_option .= "AND ((tht_base.datasets_experiments_uid in ($datasets) AND tht_base.check_line='no') OR (tht_base.check_line='yes'))";
      }
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
      if ($nlines == 0) {
        die("<font color=\"red\">Error: no phenotype measurements for this combination of traits and trials</font>");
      }      

      $outputheader1 = "$nlines".$delimiter."$ncols".$delimiter.$nheaderlines;
          $output = $outputheader2."\n";

      if (isset($_SESSION['selected_trials'])) {
          $selectedtrials = $_SESSION['selected_trials'];
          $selectedtrials = implode(",",$selectedtrials);
      }
      if (preg_match("/\d/",$selectedtrials)) {
          $sql_option = " WHERE tb.experiment_uid IN ($selectedtrials) AND ";
      } else{
          $sql_option = " WHERE ";
      }
      $found = 0;
      foreach ($lines as $uid) {
          $sql = "SELECT line_record_name, tb.experiment_uid, experiment_year as exper 
                    from line_records as lr, tht_base as tb, experiments as exp
                    $sql_option
                    lr.line_record_uid=tb.line_record_uid
                    and tb.experiment_uid = exp.experiment_uid
                    and lr.line_record_uid = $uid";
            //echo "$sql<br>\n";
            $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
            if (preg_match("/\d/",$selectedtrials)) {   //for case where there are phenotype measurements
            while ($row = mysql_fetch_array($res)) {
              $found = 1;
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
            } else {    //for case where there are no phenotype measurements
            if ($row = mysql_fetch_array($res)) {
              $line_name = $row[0];
              $year = $row[2];
              $exper = 0;    //use 0 to indicate the prediction set
              $value = "-999";
              $outline = $line_name.$delimiter.$value.$delimiter.$exper.$delimiter.$year."\n";
              $output .= $outline;
            }
         }
      }
      if ($found == 0) {
        die("<font color=\"red\">Error: no phenotype measurements for this combination of traits and trials</font>");
      }

      return $output;
    }

    private function type1_session() {
        global $config;
        $unique_str = $_GET['unq'];
        $phenotype = $_GET['pheno'];
            if (isset($_SESSION['selected_trials'])) {
              $trial = $_SESSION['selected_trials'];
              $experiments_t = implode(",",$trial);
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
            if (isset($_SESSION['selected_lines'])) {
                  $selectedlinescount = count($_SESSION['selected_lines']);
                  $lines = $_SESSION['selected_lines'];
            } else {
                  $lines = "";
            }
            //if (isset($_SESSION['selected_traits'])) {
            //        $phenotype = $_SESSION['selected_traits'];
            //        $phenotype = $phenotype[0];
                    $sql = "select phenotypes_name from phenotypes where phenotype_uid = $phenotype";
                    $res = mysql_query($sql) or die(mysql_error());
                    $row = mysql_fetch_array($res);
                    $phenolabel = $row[0];
            //    } else {
            //        $phenotype = "";
            //    }
      
       $dir = '/tmp/tht/'; 
       $filename2 = 'THTdownload_traits_' . $unique_str . '.txt';
       $filename3 = 'THTdownload_gensel_' . $unique_str . '.R';
       $filename4 = 'THTdownload_gensel_' . $unique_str . '.png';
       $filename5 = 'THT_process_error_' . $unique_str . '.txt';
       $filename6 = 'THT_R_error_' . $unique_str . '.txt';
       $filename7 = 'THT_result_' . $unique_str . '.csv';

       if(!file_exists($dir.$filename2)){
                    //$h = fopen($dir.$filename2, "w+");
                    $datasets_exp = "";
                    $subset = "yes";
                    $output = $this->type1_build_tassel_traits_download($experiments_t,$phenotype,$datasets_exp,$subset);
                    if ($output != NULL) {
                      $h = fopen($dir.$filename2, "w+");
                      fwrite($h,$output);
                      fclose($h);
                    }
                    //fwrite($h,$this->type1_build_tassel_traits_download($experiments_t,$phenotype,$datasets_exp,$subset));
                    //fclose($h);
       }
    }
}
