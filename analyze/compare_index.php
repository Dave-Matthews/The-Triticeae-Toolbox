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
        case 'status':
            $this->updatePheno();
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
        to choose two trials and one or more traits.<br>
        2. Select which trial is the normal or baseline condition.<br>
        3. Select the Index to be used for the calculation.<br>
        The formula may be modified from the selected index using valid R script notation.
        <br><br>
        <?php
        $this->displayForm();
        ?>
        <div id="step2"></div>
        <div id="step3"></div>
        <div id="step4"></div>
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
            $experiments = implode(",", $selected_trials);
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
        </select><td><input type="radio" checked name="control" onchange="javascript: update_control(this.form)">
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
        <select id="pheno" name="pheno" multiple="multiple" onchange="javascript: update_pheno(this.options)">
        <?php 
        $query = "select p.phenotype_uid, phenotypes_name
            FROM phenotypes AS p, tht_base AS t, phenotype_data AS pd
            WHERE pd.tht_base_uid = t.tht_base_uid
            AND p.phenotype_uid = pd.phenotype_uid
            AND t.experiment_uid IN ($experiments)
            GROUP by p.phenotype_uid";
        $result = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));
        echo "<option value=''>Select a trait</option>\n";
        while ($row = mysqli_fetch_row($result)) {
            $uid = $row[0];
            $pheno = $row[1];
            if (in_array($uid, $selected_traits)) {
                echo "<option value='$uid' selected>$pheno</option>\n";
            } else {
                echo "<option value='$uid'>$pheno</optoion>\n";
            }
        }
        ?>
        </select>
        <tr><td>Index:<td>
        <select id="formula1" name="formula1" onchange="javascript: update_f1()">
        <option value="DI" selected>Difference</option>
        <option value="PD">Percent Difference (PD)</option>
        <option value="GM">Geometric Mean (GM)</option>
        <option value="STI">Stress Tolerance Index (STI)</option>
        <option value="SSI">Stress Susceptibility Index (SSI)</option>
        </select>
    
        <tr><td>Formula:<td><input type="text" size="50" id="formula2" name="formula2" value="(data$trial1 - data$trial2)" onchange="javascript: update_f2()">
        </table><br><br>
    
        <p><input type="button" value="Scatterplot and Calculate Index" onclick="javascript:cal_index()"/></p>
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
        $trait_ary = $_SESSION['selected_traits'];
        $unique_str = $_GET['unq'];
        if (preg_match("/([A-Z0-9]+)/", $unique_str, $matches)) {
            $unique_str = $matches[0]; 
            mkdir("/tmp/tht/$unique_str");
        }

        //check if selection is valid
        foreach ($trait_ary as $t) {
            $query = "select phenotypes_name from phenotypes where phenotype_uid = $t";
            $result = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));
            if ($row = mysqli_fetch_row($result)) {
                $trait_name = $row[0];
            } 
            $query = "select p.phenotype_uid, phenotypes_name
            FROM phenotypes AS p, tht_base AS t, phenotype_data AS pd
            WHERE pd.tht_base_uid = t.tht_base_uid
            AND p.phenotype_uid = pd.phenotype_uid
            AND pd.phenotype_uid = $t
            AND t.experiment_uid = $exp1";
            $result = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));
            if (mysqli_num_rows($result) == 0) { 
                echo "Error: trait $trait_name has no measurements for experiment $exp1<br>\n";
            }
            $query = "select p.phenotype_uid, phenotypes_name
            FROM phenotypes AS p, tht_base AS t, phenotype_data AS pd
            WHERE pd.tht_base_uid = t.tht_base_uid
            AND p.phenotype_uid = pd.phenotype_uid
            AND pd.phenotype_uid = $t
            AND t.experiment_uid = $exp2"; 
            $result = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));
            if (mysqli_num_rows($result) == 0) {
                echo "Error: trait $trait_name has no measurements for experiment $exp2<br>\n";
            }
        }

        $datasets = "";
        $subset = "yes";
        $experiments[] = $exp1;
        $experiments[] = $exp2;
        $trait_cnt = count($trait_ary);
        if ($trait_cnt > 1) {
            foreach ($trait_ary as $trait) {
                $filename = "traits" . $trait . ".txt";
                $fullfilename = "/tmp/tht/$unique_str/$filename";
                $output = $Download->type1BuildTasselTraitsDownload($experiments, $trait, $datasets, $subset);
                if ($output != null) {
                    $h = fopen($fullfilename, "w+");
                    fwrite($h, $output);
                    fclose($h);
                }
            }    
        } else {
            $filename = "traits" . $trait . ".txt";
            $fullfilename = "/tmp/tht/$unique_str/$filename";
            $output = $Download->type1BuildTasselTraitsDownload($experiments, $trait, $datasets, $subset);
            if ($output != null) {
                $h = fopen($fullfilename, "w+");
                fwrite($h, $output);
                fclose($h);
            }
        }
    }

    /** save trait selection
     *
     * @return null
     */
    function updatePheno()
    {
        if (!empty($_GET['pheno'])) {
            $traits = $_GET['pheno'];
            $traits = explode(",", $traits);
            $_SESSION['selected_traits'] = $traits;
            $count = count($traits);
            echo "$count traits selected<br>\n";
        } else {
            echo "Error: no traits selected<br>\n";
        }
    }

    /** calculate index from two traits
     * 
     * @return null
     */
    function calculateIndex()
    {
        global $mysqli;
        $unique_str = $_GET['unq'];
        $index = $_GET['index'];
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
            echo "<font color=red>Error: missing formula</font>";
            return false;
        }

        if (!empty($_SESSION['selected_traits'])) {
            $selected_traits = $_SESSION['selected_traits'];
            $trait = $selected_traits[0];
            $query = "select phenotypes_name from phenotypes where phenotype_uid = $trait";
            $result = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));
            if ($row = mysqli_fetch_row($result)) {
                $trait = $row[0];
            }
        } else {
            die("must select trait first");
        }
        
        //create R script file
        $files = scandir("/tmp/tht/$unique_str");
        foreach ($files as $file_traits) {
            if (!preg_match("/traits(\d+)\.txt/", $file_traits, $match)) {
                continue;
            }
            $pheno_uid = $match[1];
            $query = "select phenotypes_name from phenotypes where phenotype_uid = $pheno_uid";
            $result = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));
            if ($row = mysqli_fetch_row($result)) {
                $trait = $row[0];
            }

            $pattern[0] = "/traits/";
            $pattern[1] = "/txt/";
            $replace[0] = "compare";
            $replace[1] = "png";
            $file_img = preg_replace($pattern, $replace, "$file_traits");
            $replace[0] = "results";
            $replace[1] = "csv";
            $file_out = preg_replace($pattern, $replace, "$file_traits");
            $file_traits = "/tmp/tht/$unique_str/" . $file_traits;
            $file_r = "/tmp/tht/$unique_str/compare.R";
            $h = fopen($file_r, "w+");
            fwrite($h, "tmp <- read.delim(\"$file_traits\", check.names = FALSE)\n");
            fwrite($h, "data <- data.frame(trial1=tmp[,2], trial2=tmp[,3])\n");
            $png = "png(\"/tmp/tht/$unique_str/compare.png\", width=500, height=500)\n";
            $png = "png(\"/tmp/tht/$unique_str/$file_img\", width=500, height=500)\n";
            fwrite($h, "$png");
            fwrite($h, "cn <- colnames(tmp)\n");
            fwrite($h, "plot(tmp[,2], tmp[,3], xlab=cn[2], ylab=cn[3], main=\"Scatterplot of $trait\")\n");
            fwrite($h, "dev.off()\n");
            fwrite($h, "formula <- $formula\n");
            fwrite($h, "index <- formula\n");
            fwrite($h, "results <- data.frame(line=tmp[,1], trial1=tmp[,2], trial2=tmp[,3], index=index)\n");
            fwrite($h, "colnames(results)[2] <- colnames(tmp)[2]\n");
            fwrite($h, "colnames(results)[3] <- colnames(tmp)[3]\n");
            fwrite($h, "file_out <- \"/tmp/tht/$unique_str/$file_out\"\n");
            fwrite($h, "write.csv(results, file_out)\n");
            fclose($h); 
            $file_err = "/tmp/tht/$unique_str/error.txt";
            exec("cat /tmp/tht/$unique_str/compare.R | R --vanilla > /dev/null 2> $file_err");
      
            $found = 0; 
            if (file_exists($file_err)) {
                $pattern2 = "/[A-Za-z]/";
                $h = fopen("/tmp/tht/$unique_str/error.txt", "r");
                while ($line=fgets($h)) {
                    if (preg_match($pattern2, $line)) { 
                        $found = 1;
                    }
                }
                fclose($h);
                if ($found) {
                    echo "<img style=\"float: left\" alt=\"Error: in processing $trait\" />\n";
                }
            }
            if (file_exists("/tmp/tht/$unique_str/$file_img")) {
                echo "<img style=\"float: left\" src=\"/tmp/tht/$unique_str/$file_img\" />\n";
            }
        }

        echo "<br style=\"clear: both\">\n";
        echo "Index Calculation (<b>$index</b>)<br>\n";
        echo "<table cellspacing=0 cellpadding=0 class=\"infosectionhead\">";
        foreach ($files as $file_traits) {
            if (!preg_match("/traits(\d+)\.txt/", $file_traits, $match)) {
                continue;
            }
            $pheno_uid = $match[1];
            $query = "select phenotypes_name from phenotypes where phenotype_uid = $pheno_uid";
            $result = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));
            if ($row = mysqli_fetch_row($result)) {
                $trait = $row[0];
            }

            $pattern[0] = "/traits/";
            $pattern[1] = "/txt/";
            $replace[0] = "results";
            $replace[1] = "csv";
            $file_out = preg_replace($pattern, $replace, "$file_traits");
            $file_traits = "/tmp/tht/$unique_str/" . $file_traits;
            if (file_exists("/tmp/tht/$unique_str/$file_out")) {
                $link = "<a href=\"/tmp/tht/$unique_str/$file_out\" target=\"_blank\" type=\"text/csv\">download results</a>";
                $h = fopen("/tmp/tht/$unique_str/$file_out", "r");
                ?>
                <tr><td>
                <a class="collapser" id="on_switch<?php echo $pheno_uid; ?>" style="border-bottom:none" onclick="javascript:disp_index(<?php echo $pheno_uid;?>);return false;">
                <img src="images/collapser_plus.png" /> <?php echo "$trait"; ?></a>
                <a class="collapser" id="off_switch<?php echo $pheno_uid; ?>" style="display:none; border-bottom:none" onclick="javascript:hide_index(<?php echo $pheno_uid;?>);return false;">
                <img src="images/collapser_minus.png"> <?php echo "$trait"; ?></a>
                <td><?php echo $link; ?>
                <tr><td><table id="content<?php echo $pheno_uid; ?>" style="display:none">
                <?php
                while ($line=fgetcsv($h)) {
                    if (is_numeric($line[4])) {
                        $calindex = number_format($line[4], 2, '.', ',');
                    } else {
                        $calindex = $line[4];
                    }
                    if (is_numeric($line[2])) {
                        $line[2] = round($line[2], 2);
                    }
                    if (is_numeric($line[3])) {
                        $line[3] = round($line[3], 2);
                    }
                    echo "<tr><td>$line[1]<td>$line[2]<td>$line[3]<td>$calindex\n";
                }
                fclose($h);
                echo "</table>";
            } else {
                echo "Error: calculation of index for $trait<br>\n";
            }
        }
        echo "</table>";
    }
}
