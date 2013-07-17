<?php
/**
 * Download
 *
 * PHP version 5.3
 *
 * @category PHP
 * @package  T3
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/downloads/downloadClass.php
 *
 */

/**
 * Download Class
 *
 * PHP version 5.3
 *
 * @category PHP
 * @package  T3
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/downloads/downloadClass.php
 *
 */
class Downloads
{
    /**
     * create data for traits file
     * 
     * @param array   $experiments two trials
     * @param integer $traits      one trait
     * @param unknown $datasets    not used
     * @param unknown $subset      not used
     * 
     * @return string
     **/
    public function type1BuildTasselTraitsDownload($experiments, $traits, $datasets, $subset)
    {
        global $mysqli;
        $delimiter = "\t";
        $experiment_str = implode(",", $experiments);
        if (isset($_SESSION['selected_lines']) && (count($_SESSION['selected_lines']) > 0)) {
            $lines = $_SESSION['selected_lines'];
        } else {
            $found = 0;
            $sql = "select DISTINCT lr.line_record_uid from tht_base as tb,
                phenotype_data as pd, phenotypes as p, line_records as lr
                where pd.tht_base_uid = tb.tht_base_uid
                AND p.phenotype_uid = pd.phenotype_uid
                AND lr.line_record_uid = tb.line_record_uid
                AND pd.phenotype_uid = $traits
                AND tb.experiment_uid in ($experiment_str)";
            $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
            while ($row = mysqli_fetch_array($res)) {
                $lines[] = $row[0];
                $found++;
            }
            if ($found == 0) {
                echo"Error: no lines found for experiments = $experiment_str, traits = $traits<br>\n";
                die("Error: Need lines selection to continue<br>\n");
            } else {
                //echo "Found $found lines using selected experiments and trait<br>\n";
            }
        }
        $selectedlines = implode(",", $lines);
        $outputheader2 = "gid";
        foreach ($experiments as $exp_uid) {
            $sql = "select trial_code from experiments where experiment_uid = $exp_uid";
            $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
            if ($row = mysqli_fetch_array($res)) {
                $exp_name = $row[0];
            } else {
                $exp_name = "unknown";
            }
            $outputheader2 .= $delimiter . $exp_name;
        }
        $sql_option = "";
        if (preg_match("/\d/", $experiment_str)) {
            $sql_option .= "AND tb.experiment_uid IN ($experiment_str)";
        }
        if (preg_match("/\d/", $datasets)) {
            $sql_option .= "AND ((tht_base.datasets_experiments_uid in ($datasets) AND tht_base.check_line='no') OR (tht_base.check_line='yes'))";
        }
        $sql = "SELECT DISTINCT lr.line_record_name, lr.line_record_uid
        FROM line_records as lr, tht_base as tb, phenotype_data as pd
        WHERE lr.line_record_uid=tb.line_record_uid
        AND pd.tht_base_uid = tb.tht_base_uid
        AND pd.phenotype_uid = $traits
        $sql_option";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
        while ($row = mysqli_fetch_array($res)) {
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
            $selectedtrials = implode(",", $selectedtrials);
        }
        $found = 0;
        foreach ($lines as $uid) {
            foreach ($experiments as $exp_uid) {
                $sql = "SELECT line_record_name, tb.experiment_uid, experiment_year as exper
                from line_records as lr, tht_base as tb, experiments as exp WHERE
                lr.line_record_uid=tb.line_record_uid
                and tb.experiment_uid = exp.experiment_uid
                and exp.experiment_uid = $exp_uid
                and lr.line_record_uid = $uid";
                //echo "$sql<br>\n";
                $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
                while ($row = mysqli_fetch_array($res)) {
                    $found = 1;
                    $line_name = $row[0];
                    $exper = $row[1];
                    $sql = "select pd.value as value
                    from tht_base as tb, phenotype_data as pd
                    WHERE tb.experiment_uid = $exper AND
                    tb.line_record_uid  = $uid
                    AND pd.tht_base_uid = tb.tht_base_uid
                    AND pd.phenotype_uid = $traits";
                    $res2 = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
                    if ($row2 = mysqli_fetch_array($res2)) {
                        $value[$exper] = $row2['value'];
                    } else {
                        $value[$exper] = "NA";
                    }
                }
            }
            $outline = $line_name;
            foreach ($experiments as $exp_uid) {
                $outline = $outline.$delimiter.$value[$exp_uid];
            }
            $outline = $outline."\n";
            $output .= $outline;
        }
        if ($found == 0) {
            die("<font color=\"red\">Error: no phenotype measurements for this combination of traits and trials</font>");
        }

        return $output;

    }
}

$Download = new Downloads();

?>
