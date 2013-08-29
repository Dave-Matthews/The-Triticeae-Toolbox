<?php
/**
 * Compare trait index
 *
 * PHP version 5.3
 * Prototype version 1.5.0
 *
 * @category PHP
 * @package  T3
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/analyze/compare_index.php
 *
 */

require 'config.php';
require $config['root_dir'] . 'includes/bootstrap.inc';

$mysqli = connecti();
require $config['root_dir'] . 'downloads/downloadClass.php';

$Compare = new CompareTrials($_GET['function']);

/** Using a PHP class to implement compare trait index
 * 
 * @category PHP
 * @package  T3
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/analyze/compare_index.php
 **/
class CompareTrials
{
    /**
     * Using the class's constructor to decide which action to perform
     * 
     * @param string $function action to perform
     */
    public function __construct($function = null)
    {
        switch($function)
        {  
        case 'download_session_v4':
            $this->type1Session();
            break;
        case 'calculate':
            $this->calculateIndex();
            break;
        default:
            $this->displayPage();
            break;
        }
    }
    
    /**
     * display page for comparing two trials
     * 
     * @return NULL
     */    
    function displayPage()
    {
        global $config;
        include $config['root_dir'].'theme/normal_header.php';
        $phenotype = "";
        $lines = "";
        $markers = "";
        $saved_session = "";
        ?>
        <h2>Compare the trait values for 2 trials</h2>
        1. Use the <a href="downloads/select_all.php">Select Wizard</a> or 
        <a href="phenotype/phenotype_selection.php">Select Traits and Trials</a>
        to choose two trials and one trait.<br>
        2. Select which trial is the normal or baseline condition.<br>
        3. Select the Index to be used for the calculation.<br>
        The formula may be modified from the selected index using valid R script notation.
        <br><br><?php
        $this->displayForm();
        ?>
        <div id="step2"></div>
        <div id="step3"></div>
        </div>
        <?php 
        include $config['root_dir'].'theme/footer.php';
    }

    /**
     * display form for selection criteria
     * 
     * @return null
    */
    function displayForm()
    {
        global $mysqli;
        $message = "";
        if (!empty($_SESSION['selected_trials'])) {
            $selected_trials = $_SESSION['selected_trials'];
            $trial1 = $selected_trials[0];
            $trial2 = $selected_trials[1];
        } else {
            $message = "Error: Select trials before using this page<br>";
        }
        if (!empty($_SESSION['selected_traits'])) {
            $selected_traits = $_SESSION['selected_traits'];
            $trait = $selected_traits[0];
        } else {
            $message = $message . "Error: Select traits before using this page";
        }
        if ($message != "") {
            echo "$message";
            return false;
        }
        ?>
    
        <style type="text/css">
        th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
        table {background: none; border-collapse: collapse}
        td {border: 0px solid #eee !important;}
        h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
        </style>
        <script type="text/javascript" src="analyze/compare.js"></script>
        <br>
        <form action="" enctype="multipart/form-data">
        <table>
        <tr><td><td><td>Normal
        <tr><td>Trial 1:<td>
        <select id="trial1" name="trial1" value="1" onchange="javascript: update_t1()">
	    <?php 
        $query = "select experiment_uid, trial_code, experiment_year as year from experiments order by experiment_year desc";
        $res = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli) . $query);
        $last_year = null;
        while ($row = mysqli_fetch_row($res)) {
            $uid = $row[0];
            $tc = $row[1];
            $year = $row[2];
            if ($last_year == null) {
                ?>
                <optgroup label="<?php echo $year ?>">
                <?php
                $last_year = $year;
            } else if ($year != $last_year) {
                ?>
                </optgroup>
                <optgroup label="<?php echo $year ?>">
                <?php
                $last_year = $year;
            }
            if ($uid == $trial1) {
                echo "<option value=\"$uid\" selected>$tc</option>";
            } else {
                echo "<option value='$uid'>$tc</optoion>";
            }
        }
        ?>
        </select><td><input type="radio" name="control" onchange="javascript: update_control(this.form)">
        <tr><td>Trial 2:<td>
        <select id="trial2" name="trial2" onchange="javascript: update_t2()">
        <?php 
        $query = "select experiment_uid, trial_code, experiment_year as year from experiments order by experiment_year desc";
        $result = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));
        $last_year = null;
        while ($row = mysqli_fetch_row($result)) {
            $uid = $row[0];
            $tc = $row[1];
            $year = $row[2];
            if ($last_year == null) {
                ?>
                <optgroup label="<?php echo $year ?>">
                <?php
                $last_year = $year;
            } else if ($year != $last_year) {
                ?>
                </optgroup>
                <optgroup label="<?php echo $year ?>">
                <?php
                $last_year = $year;
            }
            if ($uid == $trial2) {
                echo "<option value=\"$uid\" selected>$tc</option>";
            } else {
                echo "<option value='$uid'>$tc</optoion>\n";
            }
        }
        ?>
        </select><td><input type="radio" name="control" value="2" onchange="javascript: update_control(this.form)">
        <tr><td>Trait:<td>
        <select id="pheno" name="pheno" onchange="javascript: update_pheno()">
        <?php 
        $query = "select phenotype_uid, phenotypes_name from phenotypes";
        $result = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));
        echo "<option value=''>Select a trait</option>\n";
        while ($row = mysqli_fetch_row($result)) {
            $uid = $row[0];
            $pheno = $row[1];
            if ($uid == $trait) {
                echo "<option value='$uid' selected>$pheno</option>\n";
            } else {
                echo "<option value='$uid'>$pheno</optoion>\n";
            }
        }
        ?>
        </select>
        <tr><td>Index:<td>
        <select id="formula1" name="formula1" onchange="javascript: update_f1()">
        <option value="">Select a formula</option>
        <option value="PD">Percent Difference (PD)</option>
        <option value="GM">Geometric Mean (GM)</option>
        <option value="STI">Stress Tolerance Index (STI)</option>
        <option value="SSI">Stress Susceptibility Index (SSI)</option>
        </select>
    
        <tr><td>Formula:<td><input type="text" size="50" id="formula2" name="formula2" onchange="javascript: update_f2()">
        </table><br><br>
    
        <p><input type="button" value="Calculate" onclick="javascript:cal_index()"/></p>
        </form>
        <?php
    }

    /** defines the data set for file in tmp directory
     * 
     * @return null
     */
    function type1Session()
    {
        global $mysqli;
        global $Download;
        $exp1 = $_GET['trial1'];
        $exp2 = $_GET['trial2'];
        $trait = $_GET['pheno'];
        $unique_str = $_GET['unq'];
        
        $unique_str = filter_var($unique_str, FILTER_SANITIZE_NUMBER_INT);
        mkdir("/tmp/tht/$unique_str");
        $filename = "traits.txt";
        $fullfilename = "/tmp/tht/$unique_str/traits.txt";
    
        if (!file_exists($fullfilename)) {
            $datasets = "";
            $subset = "yes";
            $experiments[] = $exp1;
            $experiments[] = $exp2;
            $output = $Download->type1BuildTasselTraitsDownload($experiments, $trait, $datasets, $subset);
            if ($output != null) {
                $h = fopen($fullfilename, "w+");
                fwrite($h, $output);
                fclose($h);
            }
        }
    }

    /** calculate index from two traits
     * 
     * @return null
     */
    function calculateIndex()
    {
        $unique_str = $_GET['unq'];
        $formula = $_GET['formula'];
        
        //check for illegal entry
        if (preg_match("/system/", $formula)) {
            echo "<font color=red>Error: Illegal formula</font>";
            return false;
        } elseif (preg_match("/shell/", $formula)) {
            echo "<font color=red>Error: Illegal formula</font>";
            return false;
        } elseif (preg_match("/[{}]/", $formula)) {
            echo "<font color=red>Error: Illegal formula</font>";
            return false;
        } elseif (preg_match("/write/", $formula)) {
            echo "<font color=red>Error: Illegal formula</font>";
            return false;
        } elseif (preg_match("/read/", $formula)) {
            echo "<font color=red>Error: Illegal formula</font>";
            return false;
        } elseif ($formula == "") {
            echo "<font color=red>Error: missing formuls</font>";
            return false;
        }
        
        //create R script file
        $file_traits = "/tmp/tht/$unique_str/traits.txt";
        $file_r = "/tmp/tht/$unique_str/compare.R";
        $h = fopen($file_r, "w+");
        fwrite($h, "tmp <- read.delim(\"$file_traits\")\n");
        fwrite($h, "data <- data.frame(trial1=tmp[,2], trial2=tmp[,3])\n");
        fwrite($h, "formula <- $formula\n");
        fwrite($h, "index <- formula\n");
        fwrite($h, "results <- data.frame(line=tmp[,1], trial1=tmp[,2], trial2=tmp[,3], index=index)\n");
        fwrite($h, "colnames(results)[2] <- colnames(tmp)[2]\n");
        fwrite($h, "colnames(results)[3] <- colnames(tmp)[3]\n");
        fwrite($h, "file_out <- \"/tmp/tht/$unique_str/results.csv\"\n");
        fwrite($h, "write.csv(results, file_out)\n");
        fclose($h); 
        exec("cat /tmp/tht/$unique_str/compare.R | R --vanilla > /dev/null 2> /tmp/tht/$unique_str/error.txt");
        
        if (file_exists("/tmp/tht/$unique_str/error.txt")) {
            $h = fopen("/tmp/tht/$unique_str/error.txt", "r");
            while ($line=fgets($h)) {
                echo "$line<br>\n";
            }
            fclose($h);
        }
        if (file_exists("/tmp/tht/$unique_str/results.csv")) {
            echo "calculated index, \n";
            print "<a href=\"/tmp/tht/$unique_str/results.csv\" target=\"_blank\" type=\"text/csv\">download results file</a><br><br>\n";
            $h = fopen("/tmp/tht/$unique_str/results.csv", "r");
            echo "<table>";
            while ($line=fgetcsv($h)) {
                if (is_numeric($line[4])) {
                    $index = number_format($line[4], 3, '.', ',');
                } else {
                    $index = $line[4];
                }
                echo "<tr><td>$line[1]<td>$line[2]<td>$line[3]<td>$index\n";
            }
            fclose($h);
            echo "</table>";
        } else {
            echo "Error: calculation of index failed<br>\n";
        }
    }
}
