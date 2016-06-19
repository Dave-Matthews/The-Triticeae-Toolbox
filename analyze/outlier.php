<?php
/**
 * Display outlier traits
 *
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/analyze/outlier.php
 *
 * The purpose of this script is to identify and remove outliers from phenotype data
 *
 */

namespace T3;

require_once 'config.php';
require $config['root_dir'] . 'includes/bootstrap.inc';
require $config['root_dir'] . 'downloads/downloads_class2.php';

$mysqli = connecti();

$dObj = new Downloads();
new Outlier($_GET['function']);

/** Using a PHP class to display histogram
 *
 * @author   Clay Birkett <claybirkett@gmail.com>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/histo.php
 *
 */
class Outlier
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
                $this->type1Session();
                break;
            case "displayOut":
                $this->displayOut();
                break;
            case "displayAll":
                $this->displayAll();
                break;
            case "saveOutlier":
                $this->saveOutlier();
                break;
            case "clearOutlier":
                $this->clearOutlier();
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

    private function dispOutliers()
    {
        echo "Current list of outliers\n";
        ?>
        <div id="step21" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%; height:200px; overflow:auto;">
        <?php
        $outlier_list = $_SESSION['outliers'];
        echo "<table>";
        foreach ($outlier_list as $key1 => $val1) {
            foreach ($val1 as $key2 => $val2) {
                foreach ($val2 as $key3 => $val3) {
                    echo "<tr><td>$key1<td>$key2<td>$key3<td>$val3";
                }
            }
        }
        echo "</table></div>";
        echo "<input type='button' value='Clear' onclick='javascript:clear_session();'>";
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
        <script type="text/javascript" src="analyze/outlier.js"></script>
        <h2>Display outliers in selected traits</h2>
        <div id="step1" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%">
        Bonferroni-Holm Test for Outlier Detection in Trial Mean Data<br>
        Outliers can be saved in your <b>Current selection</b> then you will have the option of ignoring these measurements from Analysis and Download functions.<br>
        </div>
        <?php
        if (isset($_SESSION['outliers'])) {
            ?>
            <div id="step2" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%;">
            <?php
            $outlier_list = $_SESSION['outliers'];
            $this->dispOutliers();
        } else {
            ?>
            <div id="step2" style="clear: both; float: left; margin-bottom: 1.5em;">
            <?php
        }
        echo "</div>";
          
        if (!isset($_SESSION['selected_lines']) || (count($_SESSION['selected_lines']) == 0)) {
            echo "Select a set of <a href=\"downloads/select_all.php\">lines, trials, and traits.</a><br></div>\n";
            return false;
        }
        if (isset($_SESSION['selected_traits'])) {
            $phenotype = $_SESSION['selected_traits'];
            ?>
            <div id="step3" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%">
            <input type="radio" name="outlier" value="yes"         onclick="javascript:displayOut();"> just outliers
            <input type="radio" name="outlier" value="no"  checked onclick="javascript:displayAll();"> all data (outliers in red)<br><br>
            <table>
            <tr><td><input type="button" value="Analyze" onclick="javascript:use_session('v4');">
            <td><input type="text" id="thresh" name="thresh" size=3 value="0.05" /> Outlier Threshold
            </table>
            </div>
            <div id="step4" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%">
            <img alt="spinner" id="spinner" src="images/ajax-loader.gif" style="display:none;" />
            </div>
            </div>
            <?php
        } else {
            echo "Select a set of <a href=\"downloads/select_all.php\">lines, trials, and traits.</a><br></div>\n";
        }
        echo "</div>";
    }

    private function clearOutlier()
    {
        unset($_SESSION['outliers']);
    }

    private function saveOutlier()
    {
        $unique_str = intval($_GET['unq']);
        $outlier_list = array();
        $count = 0;
        $filename7 = 'THT_result1_' . $unique_str . '.csv';
        if (file_exists("/tmp/tht/$filename7")) {
            $h = fopen("/tmp/tht/$filename7", "r");
            $header1 = fgetcsv($h, 0, "\t");
            $header2 = fgetcsv($h, 0, "\t");
            echo "<table>";
            while ($line=fgetcsv($h, 0, "\t")) {
                foreach ($line as $key => $val) {
                    //echo "all $key $val\n";
                    if ($key > 0) {
                        //echo "$key $val \n";
                        if (preg_match("/\d/", $val)) {
                            $trait = $header1[$key];
                            $exp = $header2[$key];
                            $outlier_list[$line[0]][$trait][$exp] = $val;
                        }
                    }
                }
                if (preg_match("/\d/", $outlier_line)) {
                    echo "<tr><td>$line[0]$outlier_line\n";
                }
                $count++;
            }
            echo "</table>";
            $this->dispOutliers();
            fclose($h);
            $_SESSION['outliers'] = $outlier_list;
        } else {
            echo "$filename7 not found\n";
        }
    }

    private function displayOut()
    {
        $unique_str = intval($_GET['unq']);
        ?>
        <img alt="spinner" id="spinner" src="images/ajax-loader.gif" style="display:none;" />
        <?php
        $filename = 'THT_result2_' . $unique_str . '.csv';
        if (file_exists("/tmp/tht/$filename")) {
            echo "<input type='button' value='Save outliers' onclick=save_outlier()>";
            $h = fopen("/tmp/tht/$filename", "r");
            echo "<table>";
            while ($line=fgetcsv($h, 0, "\t")) {
                echo "<tr>";
                foreach ($line as $val) {
                    echo "<td>$val\n";
                }
            }
            echo "</table>";
            fclose($h);
        }
    }

    private function displayAll()
    {
        $unique_str = intval($_GET['unq']);
        ?>
        <img alt="spinner" id="spinner" src="images/ajax-loader.gif" style="display:none;" />
        <?php
        $filename = 'THT_result1_' . $unique_str . '.csv';
        if (file_exists("/tmp/tht/$filename")) {
            echo "<input type='button' value='Save outliers' onclick=save_outlier()>";
            $count = 1;
            $h = fopen("/tmp/tht/$filename", "r");
            $header1 = fgetcsv($h, 0, "\t");
            $header2 = fgetcsv($h, 0, "\t");
            while ($line=fgetcsv($h, 0, "\t")) {
                foreach ($line as $key => $val) {
                    if ($key > 0) {
                        if (preg_match("/\d+/", $val)) {
                            $outlier[$count][$key] = "<font color=red>$val</font>";
                            //echo "found outlier $val<br>\n";
                        }
                    }
                }
                $count++;
            }
            fclose($h);
        }
 
        $filename = 'THT_result3_' . $unique_str . '.csv';
        if (file_exists("/tmp/tht/$filename")) {
            $count = 1;
            $h = fopen("/tmp/tht/$filename", "r");
            echo "<table>";
            while ($line=fgetcsv($h, 0, "\t")) {
                echo "<tr>";
                foreach ($line as $key => $val) {
                    if (isset($outlier[$count][$key])) {
                        $val2 = $outlier[$count][$key];
                        echo "<td>$val2\n";
                    } else {
                        echo "<td>$val\n";
                    }
                }
                $count++;
            }
            echo "</table>";
            fclose($h);
        }
    }

    private function type1Session()
    {
        global $config;
        global $mysqli;
        if (isset($_GET['thresh'])) {
            $thresh = $_GET['thresh'];
        } else {
            $thresh = "0.05";
        }
        $unique_str = intval($_GET['unq']);
        if (isset($_SESSION['selected_trials'])) {
            $trial = $_SESSION['selected_trials'];
            $experiments_t = implode(",", $trial);
            foreach ($trial as $uid) {
                $sql = "select trial_code from experiments where experiment_uid = $uid";
                $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
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
        if (isset($_SESSION['selected_traits'])) {
            $phenotype_ary = $_SESSION['selected_traits'];
            $phenotype = implode(",", $phenotype_ary);
            foreach ($phenotype_ary as $val) {
                $sql = "select phenotypes_name from phenotypes where phenotype_uid = $val";
                $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
                $row = mysqli_fetch_array($res);
                $phenolabel = $row[0];
            }
        }
      
        $dir = '/tmp/tht/';
        $filename2 = 'THTdownload_traits_' . $unique_str . '.txt';
        $filename3 = 'THTdownload_gensel_' . $unique_str . '.R';
        $filename4 = 'THTdownload_gensel_' . $unique_str . '.png';
        $filename5 = 'THT_process_error_' . $unique_str . '.txt';
        $filename6 = 'THT_R_error_' . $unique_str . '.txt';
        $filename7 = 'THT_result1_' . $unique_str . '.csv';
        $filename8 = 'THT_result2_' . $unique_str . '.csv';
        $filename9 = 'THT_result3_' . $unique_str . '.csv';

        global $dObj;
        if (!file_exists($dir.$filename2)) {
            $datasets_exp = "";
            $output = $dObj->type1_build_traits_download($experiments_t, $phenotype, $datasets_exp);
            if ($output != null) {
                $h = fopen($dir.$filename2, "w+");
                fwrite($h, $output);
                fclose($h);
            } else {
                echo "Error: no output\n";
            }
        }

            $h = fopen($dir.$filename3, "w");
            $cmd1 = "trialData <- read.table(\"$dir$filename2\", sep=\"\\t\", header=TRUE, stringsAsFactors=FALSE, check.names=FALSE)\n";
            $cmd2 = "fileout1 <- \"$filename7\"\n";
            $cmd3 = "fileout2 <- \"$filename8\"\n";
            $cmd4 = "fileout3 <- \"$filename9\"\n";
            $cmd5 = "OutlierThreshold <- $thresh\n";
            fwrite($h, $cmd1);
            fwrite($h, $cmd2);
            fwrite($h, $cmd3);
            fwrite($h, $cmd4);
            fwrite($h, $cmd5);
            fclose($h);
        
        if (file_exists("/tmp/tht/$filename2")) {
              //exec("cat /tmp/tht/$filename3 ../R/outlierMeanAnalysis2.R | R --vanilla > /dev/null 2> /tmp/tht/$filename5");
              exec("cat /tmp/tht/$filename3 ../R/outlierMeanCuration3.R | R --vanilla > /dev/null 2> /tmp/tht/$filename5");
        } else {
              die("Error: no file for analysis<br>\n");
        }
        if (file_exists("/tmp/tht/$filename5")) {
            $h = fopen("/tmp/tht/$filename5", "r");
            while ($line=fgets($h)) {
                //echo "$line<br>\n";
            }
            fclose($h);
        }
    }
}
