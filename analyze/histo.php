<?php
/**
 * Display histogram
 *
 * PHP version 5.3
 * Prototype version 1.5.0
 *
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/histo.php
 *
 * The purpose of this script is to generate one or more histograms of phenotype data
 *
 */

namespace T3;

require_once 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
require $config['root_dir'] . 'downloads/downloads_class.php';

$mysqli = connecti();

$dObj = new Downloads();
new Histo($_GET['function']);

/** Using a PHP class to display histogram
 *
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/histo.php
 *
 */
class Histo
{
    /**
     * Using the class's constructor to decide which action to perform
     *
     * @param string $function action to perform
     */
    public function __construct($function = null)
    {
        switch ($function) {
            case 'run_histo':
                $this->runHisto();
                break;
            case "download_session_v4":
                $this->type1Session(V4);
                break;
            default:
                $this->type1Select();
                break;
        }
    }

    /**
     * load header and footer then check session to use existing data selection
     *
     * @return NULL
     */
    private function type1Select()
    {
        global $config;
        include $config['root_dir'].'theme/normal_header.php';
        $phenotype = "";
        $lines = "";
        $markers = "";
        $saved_session = "";
        $this->type1Checksession();
        include $config['root_dir'].'theme/footer.php';
    }

    /**
     * check for required inputs
     *
     * @return NULL
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
        <script type="text/javascript" src="downloads/download_gs.js"></script>
        <h2>Histogram of selected phenotypes</h2>
        <div id="step1" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%">
        Display a histogram of the selected phenotype data for each trial<br><br>
        <?php
        if (!isset($_SESSION['selected_lines']) || (count($_SESSION['selected_lines']) == 0)) {
            echo "Select a set of <a href=\"downloads/select_all.php\">lines, trials, and traits.</a><br></div></div>\n";
            return false;
        }
        if (isset($_SESSION['selected_traits'])) {
            $phenotype = $_SESSION['selected_traits'];
            echo "<table><tr><td><b>Trait</b><td>";
            foreach ($phenotype as $pheno) {
                $sql = "select phenotypes_name, unit_name from phenotypes, units
                where phenotypes.unit_uid = units.unit_uid
                and phenotype_uid = $pheno";
                $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
                $row = mysqli_fetch_array($res);
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

    /**
     * run R on input files
     *
     * @return NULL
     */
    private function runHisto()
    {
        global $mysqli;
        $unique_str = $_GET['unq'];
        $phenotype = $_GET['pheno'];
        $dir = '/tmp/tht/';
        $filename1 = 'THTdownload_hapmap_' . $unique_str . '.txt';
        $filename2 = 'THTdownload_traits_' . $unique_str . '.txt';
        $filename3 = 'THTdownload_histo_' . $unique_str . '.R';
        $filename4 = 'THTdownload_histo_' . $unique_str . '.png';
        $filename5 = 'process_error_histo_' . $unique_str . '.txt';
            $sql = "select phenotypes_name, unit_name from phenotypes, units
               where phenotypes.unit_uid = units.unit_uid
               and phenotype_uid = $phenotype";
            $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
            $row = mysqli_fetch_array($res);
            $phenolabel = $row[0];
            $phenounit = $row[1];
            
        $ntrials = 0;
        $triallabel = "";
        if (isset($_SESSION['selected_trials'])) {
            $trials = $_SESSION['selected_trials'];
            foreach ($trials as $uid) {
                $sql = "select trial_code from experiments where experiment_uid = $uid";
                $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
                if ($row = mysqli_fetch_array($res)) {
                    $trial = $row[0];
                }
                if ($triallabel == "") {
                    $triallabel = "triallabel <- list()\n";
                }
                $triallabel .= "triallabel[\"$trial\"] <- \"$trial\"\n";
                $ntrials++;
            }
        }

        $histo_width = 800;
        if ($ntrials > 3) {
            $histo_width = 800 + ($ntrials - 3) * 200;
        }
        
        if (!file_exists($dir.$filename3)) {
            $h = fopen($dir.$filename3, "w+");
            $png = "png(\"$dir$filename4\", width=$histo_width, height=300)\n";
            $cmd1 = "phenoData <- read.delim(\"$dir$filename2\", header=TRUE, na.strings=\"-999\", stringsAsFactors=FALSE, sep=\"\\t\", row.names=NULL)\n";
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
              exec("cat /tmp/tht/$filename3 ../R/histo.R | R --vanilla > /dev/null 2> /tmp/tht/$filename5");
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
            print "<img src=\"/tmp/tht/$filename4\"/>";
            print "<form method=\"get\" action=\"/tmp/tht/$filename4\">";
            print "<button type=\"submit\">Download Image</button>";
            print "</form>";
        } else {
            echo "Error in R script R/GShisto.R<br>\n";
        }
    }

    private function type1Session()
    {
        global $config;
        global $mysqli;
        $unique_str = $_GET['unq'];
        $phenotype = $_GET['pheno'];
            if (isset($_SESSION['selected_trials'])) {
              $trial = $_SESSION['selected_trials'];
              $experiments_t = implode(",", $trial);
              foreach ($trial as $uid) {
                $sql = "select trial_code from experiments where experiment_uid = $uid";
                      $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
                      if ($row = mysqli_fetch_array($res)) {
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
                    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
                    $row = mysqli_fetch_array($res);
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

       global $dObj;
       if(!file_exists($dir.$filename2)){
                    //$h = fopen($dir.$filename2, "w+");
                    $datasets_exp = "";
                    $subset = "yes";
                    //$output = $this->_type1BuildTasselTraitsDownload($experiments_t,$phenotype,$datasets_exp,$subset);
                    $output = $dObj->type1_build_traits_download($experiments_t, $phenotype, $datasets_exp);
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
