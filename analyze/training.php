<?php
/**
 * Display outlier traits
 *
 * @author  Clay Birkett <clb343@cornell.edu>
 * @license http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link    http://triticeaetoolbox.org/wheat/analyze/training.php
 *
 * The purpose of this script is to generate a training set of lines
 */

namespace T3;

require_once 'config.php';
require $config['root_dir'] . 'includes/bootstrap2.inc';
require $config['root_dir'] . 'downloads/marker_filter.php';
require $config['root_dir'] . 'downloads/downloads_class.php';
set_time_limit(0);

$mysqli = connecti();

$dObj = new Downloads();
new Training($_GET['function']);

class Training
{
    /**
     * Using the class's constructor to decide which action to perform
     *
     * @param string $function action to perform
     */
    public function __construct($function = null)
    {
        switch ($function) {
            case "displayOut":
                $this->displayOut();
                break;
            case "download_session_v4":
                $this->type1Session();
                break;
            case "status":
                $this->showStatus();
                break;
            case "filter_lines":
                $this->filterLines();
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
        include $config['root_dir'].'theme/admin_header2.php';
        $phenotype = "";
        $lines = "";
        $markers = "";
        $saved_session = "";
        $this->type1Checksession();
        include $config['root_dir'].'theme/footer.php';
    }

    /**
     * Check for required inputs
     *
     * @return NULL
     */
    private function type1Checksession()
    {
        global $mysqli;
        ?>
        <!--style type="text/css">
           th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
           table {background: none; border-collapse: collapse}
           td {border: 1px solid #eee !important;}
           h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
        </style-->
        <script type="text/javascript" src="analyze/training04.js"></script>
        <h2>Selection of an Optimized Training set for use in Genomic Prediction</h2>
        <div id="step1" style="float: left; margin-bottom: 1.5em; width: 100%">
        This analysis is used prediction problems where per individual cost of observing / analyzing the response variable is high and therefore a small number of training examples is sought or when the candidate set from which the training set must be chosen (is not representative of the test data set). The optimized training sets are calculated via a genetic algorithm combined with a reliability measure of genomic estimated breeding values (GEBV) for any given test set.
        The functions to perform these analyses are available in the <a href="https://CRAN.R-project.org/package=STPGA">STPGA 3.0 R package</a>. The default values are npop = 100 and niterations = 1000.
        Calculation of the training set can typically take anywhere from 5 minutes to 4 hours depending on the size of the dataset and the parameters selected.
        You will receive an email notification when your results are available. 
        Reference: Deniz Akdemir, Julio Sanchez and Jean-Luc Jannink. Genetics Selection Evolution201547:38 DOI: 10.1186/s12711-015-0116-6.
        <a target="_new" href="https://gsejournal.biomedcentral.com/articles/10.1186/s12711-015-0116-6">Optimization of genomic selection training populations with a genetic algorithm.</a><br>
        Missing genotype data can cause inaccurate results, the default filter setting removes markers missing greater than 10% of data.
        If a Test set is used it should have common markers with the Candidate set.<br>
        </div>
        <div id="step2" style="float: left; margin-bottom: 1.5em; width: 100%">
        <?php
        echo "<b>Consensus Genotype data</b> - Select <a href=\"downloads/select_all.php\">lines, trait, and trials</a> for a Candidate Set. ";
        echo "To select a test set first select \"Save Candidates\" then use the <a href=\"downloads/select_all.php\">lines, trait, and trials</a> page to pick any set of lines with common markers with the Candidate set.<br>";
        echo "<b>Single Experiment Genotype data</b> - Select <a href=\"downloads/select_genotype.php\">lines by genotype experiment</a> for a Candidate Set. ";
        echo "To select a test set first select \"Save Candidates\", highlight the lines not desired in the Test set, select \"Deselect highlighted lines\", then \"Analyze\".<br><br>";
        if (!isset($_SESSION['username'])) {
            echo "Login, to receive email notification when the analysis is finished.<br>\n";
        }
        if (isset($_SESSION['candidate_lines']) && !isset($_SESSION['selected_lines'])) {
        } elseif (!isset($_SESSION['selected_lines']) || (count($_SESSION['selected_lines']) == 0)) {
        }
        ?>
        </div>
        <?php
        $command = (isset($_GET['cmd']) && !empty($_GET['cmd'])) ? $_GET['cmd'] : null;
        if ($command == "saveC") {
            if (isset($_SESSION['geno_exps'])) {
                $_SESSION['candiated_exp'] = $_SESSION['geno_exps'];
                foreach ($_SESSION['selected_lines'] as $line_uid) {
                    $_SESSION['candidate_lines'][] = $line_uid;
                }
            } elseif (isset($_SESSION['selected_lines'])) {
                foreach ($_SESSION['selected_lines'] as $line_uid) {
                    $_SESSION['candidate_lines'][] = $line_uid;
                }
                unset($_SESSION['selected_lines']);
                $temp = count($_SESSION['candidate_lines']);
                //echo "<br>saved selected lines = $temp\n";
            } else {
                echo "<br>error - no selection found";
            }
        } elseif ($command == "clearC") {
            unset($_SESSION['candidate_exp']);
            if (isset($_SESSION['candidate_lines'])) {
                unset($_SESSION['candidate_lines']);
            } else {
                unset($_SESSION['selected_lines']);
            }
        } elseif ($command == "clearT") {
            unset($_SESSION['selected_lines']);
        } elseif (isset($_POST['deselLines'])) {
            $selected_lines = $_SESSION['selected_lines'];
            foreach ($_POST['deselLines'] as $line_uid) {
                if (($lineidx = array_search($line_uid, $selected_lines)) !== false) {
                    array_splice($selected_lines, $lineidx, 1);
                }
            }
            $_SESSION['selected_lines']=$selected_lines;
        }
        ?>
        <div id="step3" style="float: left; margin-bottom: 1.5em; width: 50%">
        <?php
        if (isset($_SESSION['candidate_lines'])) {
            $count = count($_SESSION['candidate_lines']);
            $display = $_SESSION['candidate_lines'];
            print "<form action=\"analyze/training.php\">";
            print "<b>Candidates</b> $count lines\n";
            print "<input type=\"hidden\" value=\"clearC\" name=\"cmd\">";
            print "<input type='submit' value='Clear Candidates' /> ";
            print "Candidates include all individuals in your population.";
            print "</form>";
            ?>
            <select multiple="multiple" style="height: 15em;width: 13em">
            <?php
            $sql = "select line_record_name from line_records where line_record_uid= ?";
            if ($stmt = mysqli_prepare($mysqli, $sql)) {
                foreach ($display as $lineuid) {
                    mysqli_stmt_bind_param($stmt, "i", $lineuid);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_bind_result($stmt, $selval);
                    mysqli_stmt_fetch($stmt);
                    print "<option value=\"$lineuid\">$selval</option>\n";
                }
                mysqli_stmt_close($stmt);
            }
            print "</select><br><br>";
            if (isset($_SESSION['selected_lines'])) {
                $count2 = count($_SESSION['selected_lines']);
                $display = $_SESSION['selected_lines'];
                print "<form action=\"analyze/training.php\">";
                echo "<b>Test</b> $count2 lines\n";
                print "<input type=\"hidden\" value=\"clearT\" name=\"cmd\">";
                print "<input type='submit' value='Clear Test' /> ";
                ?>
                The test set are the individuals for which you would like to select an optimized training set. If a test set is not defined then a training set will be selected that optimizes the prediction accuracy of the entire population.<br>
                </form>
                <form id="deselLinesForm" action="analyze/training.php" method="post">
                <select name="deselLines[]" multiple="multiple" style="height: 15em;width: 13em">
                <?php
                $sql = "select line_record_name from line_records where line_record_uid= ?";
                if ($stmt = mysqli_prepare($mysqli, $sql)) {
                    foreach ($display as $lineuid) {
                        mysqli_stmt_bind_param($stmt, "i", $lineuid);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_bind_result($stmt, $selval);
                        mysqli_stmt_fetch($stmt);
                        print "<option value=\"$lineuid\" selected>$selval</option>\n";
                    }
                    mysqli_stmt_close($stmt);
                }
                print "</select><br><br>";
                if (isset($_SESSION['geno_exps'])) {
                    print "<input type='submit' name='WhichBtn' value='Deselect highlighted lines' />";
                }
                print "</form>";
            }
             //check overlap between candidate and test line selection
            if (isset($_SESSION['selected_lines']) && (!isset($_SESSION['geno_exps']))) {
                $tmp = $_SESSION['candidate_lines'];
                foreach ($_SESSION['selected_lines'] as $lineuid) {
                    if (in_array($lineuid, $tmp)) {
                        $lineName = mysql_grab("select line_record_name from line_records where line_record_uid = $lineuid");
                        $duplicateList[] = " $lineName";
                    }
                }
                $cnt = count($duplicateList);
                if ($cnt > 0) {
                    $duplicateList = implode(",", $duplicateList);
                    echo "Duplicates: $duplicateList\n";
                } else {
                    echo "Duplicates: none\n";
                }
            }
            $min_maf = 0;
            $max_missing = 10;
            $max_miss_line = 10;

            ?>
            </div>
            <div id="step4" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%">
            <table><tr><td>Remove markers missing &gt; <input type="text" name="MMM" id="mmm" size="2" value="<?php echo ($max_missing) ?>" />% of data<br>
                           Remove lines missing &gt; <input type="text" name="mml" id="mml" size="2" value="<?php echo ($max_miss_line) ?>" />% of data<br>
            </table><br>
            <table>
            <tr><td><input type="button" value="Analyze" onclick="javascript:use_session();">
            <td><input type="text" id="notoselect" size=4 value="25" /> Number of lines to select for training set<br>
              <tr><td><select name="errorstat" id="errorstat">
              <option value="PEVMEAN"> PEVMEAN
              <option value="CDMEAN"> CDMEAN
              <option value="DOPT"> DOPT
              <option value="AOPT"> AOPT
              </select>
            </table>
            <img alt="spinner" id="spinner" src="images/ajax-loader.gif" style="display:none;" />
            </div>
            <?php
        } elseif (isset($_SESSION['selected_lines'])) {
            $count = count($_SESSION['selected_lines']);
            $display = $_SESSION['selected_lines'] ? "":" style='display: none;'";
            print "<form action=\"analyze/training.php\">";
            print "<b>Candidates</b> $count lines\n";
            print "<input type=\"hidden\" value=\"clearC\" name=\"cmd\">";
            print "<input type='submit' value='Clear Candidates' /> ";
            echo "Candidates include all individuals in your population.";
            echo "This will be the pool of candidates from which the training set is selected (minus any individuals identified in the prediction/test set).<br>";
            ?>
            <form action="training.php">
            <select multiple="multiple" style="height: 15em;width: 13em">
            <?php
            $sql = "select line_record_name from line_records where line_record_uid= ?";
            if ($stmt = mysqli_prepare($mysqli, $sql)) {
                foreach ($_SESSION['selected_lines'] as $lineuid) {
                    mysqli_stmt_bind_param($stmt, "i", $lineuid);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_bind_result($stmt, $selval);
                    mysqli_stmt_fetch($stmt);
                    print "<option value=\"$lineuid\" selected>$selval</option>\n";
                }
                mysqli_stmt_close($stmt);
            }
            print "</select>";
            print "</form>";

            $min_maf = 0;
            $max_missing = 10;
            $max_miss_line = 10;
            ?>
            </div>
            <div id="step3a" style="float: left; margin-bottom: 1.5em; margin-left: 100px; margin-top: 50px;"> 
            <?php
            /**print "<br><br><input type='submit' name='WhichBtn' value='Select Test Set' /> highlight lines for subset<br><br>";
            print "</form>"; **/
            ?>
            </div>
            <div id="step4" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%">
            <table><tr><td>Remove markers missing &gt; <input type="text" name="MMM" id="mmm" size="2" value="<?php echo ($max_missing) ?>" />% of data<br>
                           Remove lines missing &gt; <input type="text" name="mml" id="mml" size="2" value="<?php echo ($max_miss_line) ?>" />% of data<br>
            </table><br>
            <table>
            <tr><td rowspan="2"><input type="button" value="Analyze" onclick="javascript:use_session();">
            <td><input type="text" id="notoselect" size=4 value="25" /><td>Number of lines to select for training set
                  <tr><td><select name="errorstat" id="errorstat">
                  <option value="PEVMEAN"> PEVMEAN
                  <option value="CDMEAN"> CDMEAN
                  <option value="DOPT"> DOPT
                  <option value="AOPT"> AOPT
                  </select>
                <td>optimality criterion
            </table>
            <?php
            print "<form action=\"analyze/training.php\">";
            print "<input type=\"hidden\" value=\"saveC\" name=\"cmd\">";
            print "<br><input type='submit' value='Save Candidates' /> then continue to select prediction set<br><br>";
            ?>
            </form>
            </div>
        <?php
        } else {
            echo "</div>";
        }
        ?>
        <div id="step5" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%">
        <img alt="spinner" id="spinner" src="images/ajax-loader.gif" style="display:none;" />
        </div>
        <div id="step6" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%">
        <img alt="spinner" id="spinner" src="images/ajax-loader.gif" style="display:none;" />
        </div>
        <div id="step7" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%"></div></div>
        <?php
    }

    private function type1Session()
    {
        global $config;
        global $mysqli;
        $unique_str = intval($_GET['unq']);
        $notoselect = intval($_GET['notoselect']);
        $min_maf = 0;
        $max_missing = $_GET['mmm'];
        $errorstat = $_GET['err'];
     
        if (isset($_SESSION['geno_exps'])) {
            $typeGE = "true";
            $lines = $_SESSION['selected_lines'];
            $experiment_uid = $_SESSION['geno_exps'][0];
        } elseif (isset($_SESSION['filtered_lines'])) {
            $lines = $_SESSION['filtered_lines'];
        } else {
            die("Error - please select lines.\n");
        }
        $markers = $_SESSION['filtered_markers'];

        //check for illegal conditions
        if (isset($_SESSION['selected_lines'])) {
            $tmp = $_SESSION['selected_lines'];
            $count = count($tmp);
            if ($notoselect > $count) {
                die("Error - Entry for \"Number of lines to select\" must be less than number of lines in Test or Candidate set<br>\n");
            }
        }

        if (isset($_SESSION['username'])) {
            $emailAddr = $_SESSION['username'];
        }
        $dir = '/tmp/tht/';
        $filename3 = 'THTdownload_gensel.R';
        $filename4 = "iterat.png";
        $filename4b = "pca.png";
        $filename5 = 'THT_process_error.txt';
        $filename6 = 'THT_R_error.txt';
        $filename7 = 'THT_result1.csv';
        $filename8 = 'THT_result2.csv';
        $filename9 = 'THT_result3.csv';

        global $dObj;
        $dir = "/tmp/tht/download_" . $unique_str;
        mkdir("$dir");
        $filename = "genotype.hmp.txt";
        $dtype = "qtlminer";
        $h = fopen("$dir/$filename", "w");
        if ($typeGE == "true") {
            type4BuildMarkersDownload($experiment_uid, $min_maf, $max_missing, $dtype, $h);
        } else {
            $dObj->type3BuildMarkersDownload($lines, $markers, $dtype, $h);
        }
        fclose($h);
        $estimate = count($lines) + count($markers);
      
        if (isset($_SESSION['candidate_lines']) && isset($_SESSION['selected_lines'])) {
            $line_ary = $_SESSION['selected_lines'];
            $sql = "select line_record_name from line_records where line_record_uid= ?";
            if ($stmt = mysqli_prepare($mysqli, $sql)) {
                foreach ($line_ary as $lineuid) {
                    mysqli_stmt_bind_param($stmt, "i", $lineuid);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_bind_result($stmt, $line_record_name);
                    mysqli_stmt_fetch($stmt);
                    $line_ary2[] = $line_record_name;
                }
                mysqli_stmt_close($stmt);
            }
            $line_str = "\"" . implode("\",\"", $line_ary2) . "\"";
        } else {
            $line_str = "";
        }
        if (!file_exists("$dir/$filename3")) {
            $h = fopen("$dir/$filename3", "w");
            $cmd1 = "snpData <- read.table(\"$dir/$filename\", sep=\"\\t\", header=TRUE, stringsAsFactors=FALSE, na.strings='NA', row.names=1, check.names=FALSE)\n";
            $cmd2 = "setwd(\"$dir\")\n";
            $png1 = "png(\"$dir/$filename4\", width=500, height=400)\n";
            $png2 = "png(\"$dir/$filename4b\", width=500, height=400)\n";
            $cmd4 = "notoselect <- $notoselect\n";
            $cmd5 = "errorstat <- \"$errorstat\"\n";
            $cmd6 = "test <- data.frame(y=c($line_str))\n";
            $tmp = $config['root_dir'] . "R/STPGA3/GenAlgForSubsetSelection.R";
            $cmd7 = "source(\"$tmp\")\n";
            $tmp = $config['root_dir'] . "R/STPGA3/GenAlgForSubsetSelection.R";
            $cmd8 = "source(\"$tmp\")\n";
            $tmp = $config['root_dir'] . "R/STPGA3/GenAlgForSubsetSelectionNoTest.R";
            $cmd9 = "source(\"$tmp\")\n";
            $tmp = $config['root_dir'] . "R/STPGA3/GenerateCrossesfromElites.R";
            $cmd10 = "source(\"$tmp\")\n";
            $tmp = $config['root_dir'] . "R/STPGA3/PEVMEAN.R";
            $cmd11 = "source(\"$tmp\")\n";
            $tmp = $config['root_dir'] . "R/STPGA3/PEVMAX.R";
            $cmd12 = "source(\"$tmp\")\n";
            $tmp = $config['root_dir'] . "R/STPGA3/AOPT.R";
            $cmd13 = "source(\"$tmp\")\n";
            $tmp = $config['root_dir'] . "R/STPGA3/CDMEAN.R";
            $cmd14 = "source(\"$tmp\")\n";
            $tmp = $config['root_dir'] . "R/STPGA3/makeonecross.R";
            $cmd15 = "source(\"$tmp\")\n";
            fwrite($h, $png1);
            fwrite($h, $png2);
            fwrite($h, $cmd1);
            fwrite($h, $cmd2);
            fwrite($h, $cmd4);
            fwrite($h, $cmd5);
            fwrite($h, $cmd6);
            $result_url = $config['base_url'] . "analyze/training.php?function=status&unq=$unique_str";
            if (isset($_SESSION['username'])) {
                $emailAddr = $_SESSION['username'];
                $emailAddr = "email <- \"$emailAddr\"\n";
                fwrite($h, $emailAddr);
                $result_url = "result_url <- \"$result_url\"\n";
                fwrite($h, $result_url);
            }
            fclose($h);
        }
        if ($estimate > 3000) {
            exec("cat $dir/$filename3 ../R/optimizedTrainingSet.R | R --vanilla > /dev/null 2> $dir/$filename5 &\n");
            echo "<br>Analysis will be run in background. <a target=\"_new\" href=\"$result_url\">Check this link</a> for results.\n";
        } elseif ($estimate > 0) {
            exec("cat $dir/$filename3 ../R/optimizedTrainingSet.R | R --vanilla > /dev/null 2> $dir/$filename5\n");
            if (file_exists("$dir/$filename5")) {
                $h = fopen("$dir/$filename5", "r");
                echo "<br>\n";
                while ($line=fgets($h)) {
                    echo "$line<br>\n";
                }
                fclose($h);
            } else {
                echo "Error in R script, result file not found<br>\n";
            }
        } else {
            echo "<br><font color=\"red\">Error - no lines selected</font>\n";
        }
    }

    private function displayOut()
    {
        $unique_str = intval($_GET['unq']);
        $dir = "/tmp/tht/download_" . $unique_str;

        $result_url = $config['base_url'] . "analyze/training.php?function=status&unq=$unique_str";

        $filename = "$dir/THT_process_error.txt";
        if (file_exists("$filename")) {
            $h = fopen("$filename", "r");
            while ($line=fgets($h)) {
                echo "$line\n";
            }
            fclose($h);
        }
        $filename = "$dir/OptimizedTrainingList.txt";
        if (file_exists("$filename")) {
            $filename4 = "iterat.png";
            $filename4b = "pca.png";
            echo "<table><tr><td>";
            echo "<a href=\"$filename\" target=\"_new\">Download File</a><br>";
            $h = fopen("$filename", "r");
            echo "<table>";
            echo "<tr><td><b>Optimized training set</b><td>Plots\n";
            while ($line=fgetcsv($h, 0, "\t")) {
                echo "<tr>";
                foreach ($line as $val) {
                    echo "<td>$val\n";
                }
            }
            echo "</table>";
            fclose($h);
            if (file_exists("$dir/$filename4")) {
                print "<td><img src=\"$dir/$filename4\" /><br>";
            }
            if (file_exists("$dir/$filename4b")) {
                print "convergence of genetic algorithm<br>";
                print "<img src=\"$dir/$filename4b\" /><br>";
            }
            echo "</table>";
        }
    }

    private function showStatus()
    {
        global $config;
        include $config['root_dir'].'theme/normal_header.php';
      
        $unique_str = intval($_GET['unq']);
        $dir = "/tmp/tht/download_" . $unique_str;

        $filename = "$dir/THT_process_error.txt";
        if (file_exists("$filename")) {
            $h = fopen("$filename", "r");
            while ($line=fgets($h)) {
                echo "$line\n";
            }
            fclose($h);
        }
        $filename = "$dir/OptimizedTrainingList.txt";
        if (file_exists("$filename")) {
            $this->displayOut();
        } else {
            echo "Analysis not complete\n";
        }
        echo "</div>";
        include $config['root_dir'].'theme/footer.php';
    }

    /**
     * filters markers and lines based on settings
     */
    private function filterLines()
    {
        $min_maf = 0;
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
        if (isset($_SESSION['geno_exps'])) {
            $experiment_uid = $_SESSION['geno_exps'][0];
            calculate_afe($experiment_uid, $min_maf, $max_missing, $max_miss_line);
            //findCommonLines($lines);
        } elseif (isset($_SESSION['candidate_exp'])) {
            $experiment_uid = $_SESSION['candidate_exp'][0];
            calculate_afe($experiment_uid, $min_maf, $max_missing, $max_miss_line);
            //findCommonLines($lines);
        } elseif (isset($_SESSION['candidate_lines'])) {
            // when there is both candidate and test then call filter on each set because they will have differenct amount of missing data
            // then combine the results of filtered data and save for download and analysis
            $lines = $_SESSION['candidate_lines'];
            echo "Candidates<br>\n";
            calculate_af($lines, $min_maf, $max_missing, $max_miss_line);
            if (isset($_SESSION['selected_lines'])) {
                $filtered_lines = $_SESSION['filtered_lines'];
                $filtered_markers = $_SESSION['filtered_markers'];
                $lines2 = $_SESSION['selected_lines'];
                $selectedlinescount = count($filtered_lines);
                echo "<br>Test<br>\n";
                calculate_af($lines2, $min_maf, $max_missing, $max_miss_line);
                $filtered_lines2 = $_SESSION['filtered_lines'];
                $filtered_markers2 = $_SESSION['filtered_markers'];
                $tmp = count($filtered_lines2);
                foreach ($filtered_lines2 as $line) {
                    if (!in_array($line, $filtered_lines)) {
                        $filtered_lines[] = $line;
                        $selectedlinescount++;
                    }
                }
                $_SESSION['filtered_lines'] = $filtered_lines;
                echo "<br>Total lines = $selectedlinescount<br>\n";
                $selectedmarkerscount = 0;
                foreach ($filtered_markers2 as $marker) {
                    if (in_array($marker, $filtered_markers)) {
                        $selectedmarkerscount++;
                    }
                }
                echo "Common markers = $selectedmarkerscount ";
                if ($selectedmarkerscount < (0.5 * $markers1count)) {
                    echo "<font color=\"red\">Warning: Test set should have common markers with Candidates for accurate prediction</font>";
                } else {
                    echo "<br>\n";
                }
            } elseif (isset($_SESSION['selected_lines'])) {
                $lines = $_SESSION['selected_lines'];
                calculate_af($lines, $min_maf, $max_missing, $max_miss_line);
            }
        } elseif (isset($_SESSION['selected_lines'])) {
            $lines = $_SESSION['selected_lines'];
            calculate_af($lines, $min_maf, $max_missing, $max_miss_line);
        } else {
            echo "Error - no lines selected\n";
        }
    }
}
