<?php
/**
 * Canopy Spectral Reflectance
*
* PHP version 5.3
* Prototype version 1.5.0
*
* @author   Clay Birkett <clb343@cornell.edu>
* @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
* @link     http://triticeaetoolbox.org/wheat/curator_data/cal_index.php
*
*/

require 'config.php';
/*
 * Logged in page initialization
 */
require $config['root_dir'] . 'includes/bootstrap.inc';

connect();
global $mysqli;
$mysqli = connecti();
//loginTest();

/* ******************************* */
$row = loadUser($_SESSION['username']);

////////////////////////////////////////////////////////////////////////////////
//ob_start();
//authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_PARTICIPANT));
//ob_end_flush();


$Experiment = new Experiments($_GET['function']);

/** CSR phenotype experiment
 *
 * @category PHP
 * @package  T3
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/curator_data/cal_index.php
 *
 */

class Experiments
{

    /**
     * Using the class's constructor to decide which action to perform
     *
     * @param string $function action to perform
     */
    public function __construct($function = null)
    {
        switch ($function) {
            case 'display':
                $this->typeDisplay();
                break;
            case 'selDateTime':
                $this->selectDateTime();
                break;
            case 'statusLines':
                $this->statusLines();
                break;
            case 'selLines':
                $this->selectLines();
                break;
            case 'showExper':
                $this->showExper();
                break;
            case 'save':
                $this->saveSession();
                break;
            case 'selectDownload':
                $this->selectDownload();
                break;
            case 'download':
                $this->download();
                break;
            default:
                $this->typeExperiments(); /* intial case*/
                break;
        }
    }

    /**
     * display the file that has been loaded
     *
     * @return NULL
     */
    private function typeDisplay()
    {
        global $config;
        include $config['root_dir'] . 'theme/admin_header.php';
        if (isset($_GET['uid'])) {
            $experiment_uid = $_GET['uid'];
        } else {
            die("Error - no experiment found<br>\n");
        }
        $sql = "select trial_code from experiments where experiment_uid = $experiment_uid";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        if ($row = mysqli_fetch_assoc($res)) {
            $trial_code = $row["trial_code"];
        } else {
            die("Error - invalid uid $uid<br>\n");
        }

        //get line names
        $sql = "select line_record_uid, line_record_name from line_records";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        while ($row = mysqli_fetch_assoc($res)) {
            $uid = $row["line_record_uid"];
            $line_name = $row["line_record_name"];
            $line_list[$uid] = $line_name;
        }

        $count = 0;
        $sql = "select * from fieldbook order by plot";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        echo "<h2>Field Book for $trial_code</h2>\n";
        echo "<table>";
        echo "<tr><td>plot<td>line_name<td>row<td>column<td>entry<td>replication<td>block
                  <td>subblock<td>treatment<td>block_tmt<td>subblock_tmt<td>check<td>Field_ID<td>note";
        while ($row = mysqli_fetch_assoc($res)) {
            $expr = $row["experiment_uid"];
            $range = $row["range_id"];
            $plot = $row["plot"];
            $entry = $row["entry"];
            $line_uid = $row["line_uid"];
            $field_id = $row["field_id"];
            $note = $row["note"];
            $rep = $row["replication"];
            $block = $row["block"];
            $subblock = $row["subblock"];
            $row_id = $row["row_id"];
            $col_id = $row["column_id"];
            $treatment = $row["treatment"];
            $main_plot_tmt = $row["block_tmt"];
            $subblock_tmt = $row["subblock_tmt"];
            $check = $row["check_id"];
            echo "<tr><td>$plot<td>$line_list[$line_uid]<td>$row_id<td>$col_id<td>$entry<td>$rep
                  <td>$block<td>$subblock<td>$treatment<td>$main_plot_tmt<td>$subblock_tmt
                  <td>$check<td>$field_id<td>$note\n";
            $count++;
        }
        echo "</table>";
    }

    /**
     * wrapper to display header and footer for the input form
     *
     * @return NULL
     */
    private function typeExperiments()
    {
        global $config;
        global $mysqli;
        include $config['root_dir'] . 'theme/admin_header.php';

        echo "<h2>Canopy Spectral Reflectance (CSR)</h2>";
        echo "<h3>1. Select a Trial and Date/Time</h3>";
        echo "The line selection can be saved for use in other analysis or download functions.<br>";
        echo "Either All Lines or only the Check Lines can be used based on the information from the fieldbook.<br><br>";

        echo "<form action=\"curator_data/cal_index_check.php\" enctype=\"multipart/form-data\">";
        echo "<div id=col1 style=\"float: left;\">";
        $this->selectExperiment();
        echo "</div>";
        echo "<div id=col2 style=\"float: left;\"></div>";
        echo "<div id=col3 style=\"float: left;\"></div>";
        echo "<div id=col4 style=\"float: left;\"></div>";
        echo "<div id=download style=\"clear: both;\">";
        $this->selectDownload();
        echo "</div>";
        echo "<div id=status style=\"clear: both;\"></div>";

        $this->calculateIndex();

        $footer_div = 1;
        include $config['root_dir'].'theme/footer.php';
    }

    /**
     * save trial and line selection
     *
     * @return NULL
     */
    private function saveSession()
    {
        global $mysqli;
        $exp[] = $_GET['trial'];
        $_SESSION['selected_trials'] = $exp;
        $count = 0;
        $exp = $_GET['trial'];
        $_SESSION['experiments'] = $exp;
        $subset = $_GET['subset'];
        if ($subset == "check") {
            $sql_opt = "and check_id = 1";
        } else {
            $sql_opt = "";
        }
        $sql = "select distinct(line_record_uid), line_record_name from line_records, fieldbook
        where line_records.line_record_uid = fieldbook.line_uid
        and fieldbook.experiment_uid = $exp $sql_opt";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
        while ($row = mysqli_fetch_row($res)) {
            $lines[] = $row[0];
            $count++;
        }
        $_SESSION['selected_lines'] = $lines;
        echo "current data selection = $count lines<br>\n";
        echo "Selection saved<br><br>";
    }

    /**
     * select experiment
     *
     * @return NULL
     */
    function selectExperiment()
    {
        global $config;
        global $mysqli;
        ?>

        <script type="text/javascript" src="curator_data/csr01.js"></script>
        <table style="heidht:100px">
        <tr><th>Trial</th>
        <tr><td style="height:100px; vertical-align:text-top"><select id="trial" name="trial" onchange="javascript: update_trial()">
        <?php
        $sql = "select distinct(trial_code), experiments.experiment_uid from experiments, csr_measurement
            where experiments.experiment_uid = csr_measurement.experiment_uid";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
        //echo "$sql<br>\n";
        echo "<option>select a trial</option>\n";
        while ($row = mysqli_fetch_row($res)) {
            $tc = $row[0];
            $uid = $row[1];
            $trial_list[$uid] = $tc;
            echo "<option value=\"$uid\">$tc</option>\n";
        }
        ?>
        </select></table>
        <?php
    }

    /**
     * select data and time
     *
     * @return NULL
     */
    function selectDateTime()
    {
        global $config;
        global $mysqli;
        ?>

        <table>
        <tr><th>Date/Time</th>
        <tr><td style="height:100px; vertical-align:text-top"><select id="muid" name="muid" onchange="javascript: update_DateTime()">
        <?php
        $exp = $_GET['trial'];
        $sql = "select measurement_uid, date_format(measure_date,'%m-%d-%y'), time_format(start_time, '%H:%i')
        from experiments, csr_measurement where experiments.experiment_uid = csr_measurement.experiment_uid and
        experiments.experiment_uid = $exp order by measure_date, start_time";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
        echo "<option>select a date and time</option>\n";
        while ($row = mysqli_fetch_row($res)) {
            $mid = $row[0];
            $date = $row[1];
            $time = $row[2];
            $trial_list[$uid] = $tc;
            echo "<option value=\"$mid\">$date $time</option>\n";
        }
        echo "</select></table>";
    }

    /**
     * select lines
     *
     * @return lines
     */
    function selectLines()
    {
        global $mysqli;
        ?>
        <table>
        <tr><th>Lines</th>
        <tr><td style="height:100px; vertical-align:text-top"><select id="lines" name="lines" multiple>
        <?php
        $exp = $_GET['trial'];
        $subset = $_GET['subset'];
        if ($subset == "check") {
            $sql_opt = "and check_id = 1";
        } else {
            $sql_opt = "";
        }
        $sql = "select distinct(line_record_uid), line_record_name from line_records, fieldbook
        where line_records.line_record_uid = fieldbook.line_uid
        and fieldbook.experiment_uid = $exp $sql_opt";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
        while ($row = mysqli_fetch_row($res)) {
                $uid = $row[0];
                $line_name = $row[1];
                echo "<option value=\"$uid\" disabled=\"disabled\">$line_name</option>\n";
        }
        if (!empty($_GET['subset'])) {
            $subset = $_GET['subset'];
        } else {
            $subset = "all";
        }
        if ($subset == "check") {
            $checked_all = "";
            $checked_chk = "checked";
        } else {
            $checked_all = "checked";
            $checked_chk = "";
        }
        ?>
        </select><br>
        <input type="radio" <?php echo $checked_all; ?> name="subset" value="All" onclick="javascript: update_subset(this.form)">All Lines
        <input type="radio" <?php echo $checked_chk; ?> name="subset" value="Check" onclick="javascript: update_subset(this.form)">Check Lines
        </table>
        <?php
    }

    /**
     * show experiment annotation
     *
     * @return null
     */
    function showExper()
    {
        global $mysqli;
        $muid = $_GET['muid'];
        ?>
        <table>
        <tr><th>Annotation</th>
        <tr><td style="height:100px; vertical-align:text-top">
        <?php
        $sql = "select weather, system_name, direction from csr_measurement, csr_system, csr_measurement_rd
          where csr_measurement.spect_sys_uid = csr_system.system_uid
          and csr_measurement.radiation_dir_uid = csr_measurement_rd.radiation_dir_uid
          and measurement_uid = $muid";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
        if ($row = mysqli_fetch_row($res)) {
            $weather = $row[0];
            $name = $row[1];
            $dir = $row[2];
            echo "<select multiple>\n";
            echo "<option disabled=\"disabled\">weather = $weather</option>\n";
            echo "<option disabled=\"disabled\">system = $name</option>\n";
            echo "<option disabled=\"disabled\">direction = $dir</option>\n";
            echo "</select>";
        }
        ?>
        </table>
        <?php
    }

    /**
     * line selection status
     *
     * @return NULL
     */
    function statusLines()
    {
        global $mysqli;
        $count = 0;
        $exp = $_GET['trial'];
        $subset = $_GET['subset'];
        if ($subset == "check") {
            $sql_opt = "and check_id = 1";
        } else {
            $sql_opt = "";
        }
        $sql = "select distinct(line_record_uid), line_record_name from line_records, fieldbook
        where line_records.line_record_uid = fieldbook.line_uid
        and fieldbook.experiment_uid = $exp $sql_opt";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
        while ($row = mysqli_fetch_row($res)) {
            $count++;
        }
        echo "To save the $count selected lines for other Analysis <input type=button value=\"Save\" onclick=\"javascript: save_session();\">";
        echo "<br><br>";
    }

    /**
     * display inputs for download
     *
     * @return NULL
     */
    function selectDownload()
    {
        global $config;
        global $mysqli;

        //needed for mac compatibility
        ini_set('auto_detect_line_endings', true);
        $unique_str = chr(rand(65, 80)).chr(rand(65, 80)).chr(rand(65, 80)).chr(rand(65, 80));

        $raw_path = "";
        if (!empty($_GET['muid'])) {
            $muid = $_GET['muid'];
            $sql = "select raw_file_name, experiment_uid from csr_measurement where measurement_uid = $muid";
            $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
            if ($row = mysqli_fetch_row($res)) {
                $raw_file = $row[0];
                $experiment_uid = $row[1];
                $raw_path = $config['root_dir'] . "raw/phenotype/" . $raw_file;
                $raw_path2 = $config['root_dir'] . "raw/phenotype/CSR/" . $raw_file;  //new location for files
            }
        }
        if (!empty($_GET['trial'])) {
            $trial = $_GET['trial'];
            $subset = $_GET['subset'];
        }

        echo "<br>To view the raw CSR Data ";
        if ($raw_path == "") {
            echo "<input type=\"button\" value=\"Download\" disabled><br><br>";
            return;
        }
        if (($reader = fopen($raw_path, "r")) == false) {
            if (($reader = fopen($raw_path2, "r")) == false) {
                die("error - can not read file $raw_path or $raw_path2");
            }
        }
        $out_path = "/tmp/tht/csr_data_" . $unique_str . ".txt";
        $url_path = $root . $out_path;
        if (($writer = fopen($out_path, "w")) == false) {
            die("error - can not write file $out_path");
        }
        echo "<input type=\"button\" value=\"Download\"
            onclick=\"javascript: start_download('$url_path');\">";
        echo "<br><br>";

        //get list of line names for each plot
        if ($subset == "all") {
            $sql_opt = "";
        } else {
            $sql_opt = "and check_id = 1";
        }
        $sql = "select plot, line_record_name from fieldbook, line_records
            where line_records.line_record_uid = fieldbook.line_uid and experiment_uid = $experiment_uid
            $sql_opt";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
        while ($row = mysqli_fetch_array($res)) {
            $plot = $row[0];
            $line_name = $row[1];
            $line_list[$plot] = $line_name;
            //echo "$plot $line_name<br>\n";
        }

        //first line should be trial
        $line = fgets($reader);
        fwrite($writer, $line);

        //read in plot numbers
        $line = fgets($reader);
        $plot_list = str_getcsv($line, "\t");
        $count = count($plot_list);

        if ($subset == "all") {
            fwrite($writer, $line);
        } else {
            fwrite($writer, "Plot\t");
            for ($i=1; $i<=$count; $i++) {
                if (isset($line_list[$plot_list[$i]])) {
                    fwrite($writer, "$plot_list[$i]\t");
                }
            }
            fwrite($writer, "\n");
        }

        //write out line names
        fwrite($writer, "Line_name\t");
        for ($i=1; $i<=$count; $i++) {
            $line_name = $line_list[$plot_list[$i]];
            if ($subset == "all") {
                fwrite($writer, "$line_name\t");
            } elseif (isset($line_list[$plot_list[$i]])) {
                fwrite($writer, "$line_name\t");
            }
            //echo "$i $plot_list[$i] $line_name<br>\n";
        }
        fwrite($writer, "\n");

        //read in start stop time
        for ($j=1; $j<=2; $j++) {
            $line = fgets($reader);
            if ($subset == "all") {
                fwrite($writer, $line);
            } else {
                $start_list = str_getcsv($line, "\t");
                fwrite($writer, "$start_list[0]\t");
                for ($i=1; $i<=$count; $i++) {
                    if (isset($line_list[$plot_list[$i]])) {
                        fwrite($writer, "$start_list[$i]\t");
                    }
                }
                fwrite($writer, "\n");
            }
        }

        //read in measurements
        while ($line = fgets($reader)) {
            if ($subset == "all") {
                fwrite($writer, $line);
            } else {
                $csr_list = str_getcsv($line, "\t");
                fwrite($writer, "$csr_list[0]\t");
                for ($i=1; $i<=$count; $i++) {
                    if (isset($line_list[$plot_list[$i]])) {
                        fwrite($writer, "$csr_list[$i]\t");
                    }
                }
                fwrite($writer, "\n");
            }
        }
        fclose($reader);
        fclose($writer);
    }

    /**
     * locate download file
     *
     * @return file
     */
    private function download()
    {
        //header('Content-type: application/pdf');
        //header('Content-Disposition: attachment; filename=download.pdf');
        //readfile('/tmp/tht/Rplots.pdf');
        header("Location: /tmp/tht/Rplots.pdf");
    }

    /**
     * display input CSR index input form
     *
     * @return NULL
     */
    private function calculateIndex()
    {
        ?>
        <h3>2. Calculate CSR Index</h3>
        The CSR indices are calculated from plot phenotype data.
        Select an Index then click the Calculate Index button.<br>
        The wavelength paramaters (W1, W2) and the formula may be modified from their default values.<br><br>
        <table>
        <tr><td><strong>Smoothing:</strong><td>
        <select id="smooth" name="smooth" onchange="javascript: update_smooth()">
        <option value="0">0</option>
        <option value="5">11</option>
        <option value="10">21</option>
        </select>
        <td id="smooth2">no smoothing

        <tr><td><strong>Index:</strong><td>
        <select id="formula1" name="formula1" onchange="javascript: update_f1()">
        <option value=''>Select a formula</option>
        <option value="SR">SR</option>
        <option value="NWI1">NWI 1</option>
        <option value="NWI3">NWI 3</option>
        <option value="PRI">PRI</option>
        <option value="EVI">EVI</option>
        <option value="NDVI">NDVI</option>
        <option value="NDVIR">NDVI Red</option>
        <option value="NDVIG">NDVI Green</option>
        <option value="OSAVI">OSAVI</option>
        <option value="TCARI">TCARI</option>
        </select>
        <td id="formdesc">
        <tr><td><strong>W1:</strong><td><input type="text" id="W1" name="W1" onchange="javascript: update_w1()"><td>
        <tr><td><strong>W2:</strong><td><input type="text" id="W2" name="W2" onchange="javascript: update_w2()"><td>
        <tr><td><strong>W3:</strong><td><input type="text" id="W3" name="W3" onchange="javascript: update_w3()"><td>
        <tr><td><strong>Formula:</strong><td><input type="text" id="formula2" name="formula2" size="40" onchange="javascript: update_f2()"><td>
        <tr><td><strong>plot CSR:</strong><td>
        <input type="radio" name="xrange" value="zoomout" onchange="javascript: update_zoom(this.form)">entire range
        <input type="radio" name="xrange" value="zoomin" checked onchange="javascript: update_zoom(this.form)">within (W1,W2,W3)
        <tr><td colspan=3><input type="button" value="Calculate Index" onclick="javascript:cal_index()"/></p>
        </form>
        </td></tr></table>

        <!--a href=login/edit_csr_field.php>Edit Field Book Table</a><br-->
        <div id="step1" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%">
        </div>
        <div id="step2" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%">
        <img alt="spinner" id="spinner" src="images/ajax-loader.gif" style="display:none;" />
        </div>
        <?php
    } /* end of type_Experiment_Name function*/
}
