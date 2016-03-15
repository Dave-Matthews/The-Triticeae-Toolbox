<?php
/**
 * Experiment Design
 *
 * PHP version 5.3
 * jQuery version 1.11
 * jQueryUI version 1.11
 *
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/downloads/exp_design.php
 *
 */

/** Using a PHP class to implement the Experiment Design feature
 *
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/curator_data/exp_design.php
 **/

class Fieldbook
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
            case 'designField':
                $this->designField();
                break;
            case 'selectProg':
                $this->selectProg();
                break;
            case 'designTrial':
                $this->design3();
                break;
            case 'displayTrial':
                $this->displayTrial();
                break;
            case 'selectTrial':
                $this->design2();
                break;
            case 'addTrial':
                $this->design3();
                break;
            case 'design_results':
                $this->design_results();
                break;
            case 'create_trial':
                $this->createTrial();
                break;
            case 'create_field':
                $this->createField();
                break;
            case 'saveChecks':
                $this->saveChecks();
                break;
            case 'searchLine':
                $this->searchLine();
                break;
            case 'saveLine':
                $this->saveLines();
                break;
            default:
                $this->checksession();
                break;
        }
    }

    /**
     * add header and footer to page
     *
     * @return NULL
     */
    private function checksession()
    {
        global $config;
        include $config['root_dir'].'theme/admin_header.php';
        ?>
        <h2>Manage Phenotype trials</h2>
        <?php
        $this->design();
        ?>
        </div>
        <?php
        include $config['root_dir'].'theme/footer.php';
    }

    /**
     * search for line
     *
     * @return string
     */
    private function searchLine()
    {
        global $mysqli;
        $nonHits = array();
        if (!empty($_POST)) {
            $linenames = $_POST['LineSearchInput'];
        }
        if (strlen($linenames) != 0) {
            // Assume input is punctuated either with commas, tabs or linebreaks. Change to commas.
            $linenames = str_replace(array('\r\n', ', '), '\t', $linenames);
            $linenames = str_replace(array('\n', ', '), '\t', $linenames);
            $lineList = explode('\t', $linenames);
            foreach ($lineList as $word) {
                echo "$word<br>\n";
                $found = false;
                $word = str_replace('*', '%', $word);  // Handle "*" wildcards.
                $word = str_replace('&amp;', '&', $word);  // Allow "&" character in line names.
                // First check line_records.line_record_name.
                $sql = "SELECT line_record_name from line_records where line_record_name like ?";
                if ($stmt = mysqli_prepare($mysqli, $sql)) {
                    mysqli_stmt_bind_param($stmt, "s", $word);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_bind_result($stmt, $hits);
                    while (mysqli_stmt_fetch($stmt)) {
                        $linesFound[] = $hits;
                    }
                    mysqli_stmt_close($stmt);
                    if (isset($linesFound)) {
                        $found = true;
                    }
                }
                // Now check line_synonyms.line_synonym_name.
                $sql = "select line_record_name from line_synonyms ls, line_records lr where line_synonym_name like ? and ls.line_record_uid = lr.line_record_uid";
                if ($stmt = mysqli_prepare($mysqli, $sql)) {
                    mysqli_stmt_bind_param($stmt, "s", $word);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_bind_result($stmt, $hits);
                    while (mysqli_stmt_fetch($stmt)) {
                        $linesFound[] = $hits;
                    }
                    mysqli_stmt_close($stmt);
                    if (isset($linesFound)) {
                        $found = true;
                    }
                }
                if ($found === false) {
                    $nonHits[] = $word;
                }
            }
            // Generate the translated line names
            if (count($linesFound) > 0) {
                $linenames = implode("','", $linesFound);
            }
        } // end if (strlen($linenames) != 0)
        /* Build the search string $where. */
        $count = 0;
        if (strlen($linenames) > 0) {
            if ($count == 0) {
                $where .= "line_record_name in ('".$linenames."')";
            } else {
                $where .= " AND line_record_name in ('".$linenames."')";
            }
            $count++;
            $TheQuery = "select line_record_uid, line_record_name from line_records where $where";
            $result=mysqli_query($mysqli, $TheQuery) or die(mysqli_error($mysqli)."<br>Query was:<br>".$TheQuery);
            $linesfound = mysqli_num_rows($result);
        }

        /* Search Results: */
        /* echo "</div><div class='boxContent'><table width=500px><tr><td>"; */
        echo "<form name='lines' action=".$_SERVER['PHP_SELF']." method='get'>";
        // Show failures from the Name box that don't match any line names.
        foreach ($nonHits as $i) {
            if ($i != '') {
                echo "<font color=red><b>Line \"$i\" not found.</font></b><br>";
            }
        }
        print "<b>Lines found: $linesfound </b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

        /* If any hits. */
        if ($linesfound > 0) {
            echo " <input type='button' name='WhichBtn' value='Add to check lines' onclick='javascript: save_line(this.options)'/>";
            print "<br><select name='selLines' id='selLines' multiple='multiple' style='height: 17em; width: 13em'>";
            $_SESSION['linesfound'] = array();
            while ($row = mysqli_fetch_assoc($result)) {
                $line_record_name = $row['line_record_name'];
                $line_record_uid = $row['line_record_uid'];
                echo "<option value='$line_record_uid' selected>$line_record_name</option>";
                $_SESSION['linesfound'][] = $line_record_uid;
            }
            print "</select><br>";

        } // end if ($linesfound > 0)
        print "</form>";

    }

    private function design()
    {
        ?>
        This tool allows you to view a trial, create a trial, or create an experiment design.<br> 
        The results can be uploaded in the sandbox, downloaded to a tablet device, or submitted to the curator for loading into the production website.<br><br>
        <input type="radio" name="myRadio" checked="checked" onclick="javascript: select_trial();"/>Select existing trial<br>
        <input type="radio" name="myRadio" onclick="javascript: add_trial();"/>Create new trial<br><br>
        <!--input type=button value="View existing trial" onclick="javascript: select_trial()"  style="display: inline-block">
        <input type=button value="Upload Phenotype trial" onclick="javascript: upload_trial()" style="display: inline-block">
        <input type=button value="Upload Field layout" onclick="javascript: upload_field()" style="display: inline-block">
        <input type=button value="Create new trial" onclick="javascript: add_trial()" style="display: inline-block"-->
        <div id="dialog-form" title="Upload Phenotype Trial">
        <form action="curator_data/input_annotations_check_excel.php" method="post" enctype="multipart/form-data">
          <p>Do you want the data from this trial to be <b>Public</b>?
          <input type='radio' name='flag' value="1" checked style="display: inline-block"/> Yes &nbsp;&nbsp;
          <input type='radio' name='flag' value="0" style="display: inline-block"/> No
          <p>Trial description file: <input id="trial_upload_file[]" type="file" name="file[]" style="display: inline-block" />
          <p><a href="curator_data/examples/T3/TrialSubmissionForm.xls">Example</a>
          <p><input type="submit" value="Upload" />
        </form>
        </div>
        <div id="dialog-form-field" title="Upload Field Layout">
        <form action="curator_data/input_csr_field_check.php" method="post" enctype="multipart/form-data">
          <p>Field Book File: <input id="field_upload_file[]" type="file" name="file[]" style="display: inline-block" />
          <p><a href="curator_data/examples/T3/fieldbook_template.xlsx">Example</a>
          <p><input type="submit" value="Upload" />
        </form>
        </div>
        <div id="dialog-form-checks" title="Select check lines">
        <form id="searchLines" action="<?php echo $_SERVER['SCRIPT_NAME'] ?>" method="POST">
          <tr style="vertical-align: top">
            <td><b>Name</b> <br>
                <textarea name="LineSearchInput" id="LineSearchInput" rows="3" cols="18" style="height: 6em;">
                <?php $nm = explode('\r\n', $name);
                foreach ($nm as $n) {
                    echo $n."\n";
                }
                ?></textarea>
                <br> E.g. Cayuga, tur*ey, iwa860*<br>
                Synonyms will be translated.<br>
          <input type="button" value="Search" onclick="javascript: search_line()"/>
        </form>
        <div id="dialog_r"></div>
        </div>
        <div class="step1"></div><div class="step1a"></div><div class="step1b"></div>
        <div class="step2"></div>
        <div class="step3"></div>
        <img alt="creating download file" id="spinner" src="images/ajax-loader.gif" style="display:none;">
        <div class="step4"></div>
        <div class="step5"></div>
        <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
        <script src="//code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
        <script type="text/javascript" src="curator_data/design08.js"></script>
        <script type="text/javascript">
        if ( window.addEventListener ) {
            window.addEventListener( "load", select_trial(), false );
        } else if ( window.onload ) {
            window.onload = "select_trial()";
        }
        </script>
        <?php
    }

    private function selectProg()
    {
        global $mysqli;
        ?>
        To create an experiment design, select a design type from the drop-down list<br><br>
        <table>
        <tr><td width="120">Program:
        <td><select id="program" onchange="javascript: update_step1()">
        <option value="">select program</option>
        <?php
        $sql = "SELECT DISTINCT dp.CAPdata_programs_uid AS id, data_program_name AS name, data_program_code AS code
                  FROM experiments AS e, CAPdata_programs AS dp
                  WHERE dp.CAPdata_programs_uid = e.CAPdata_programs_uid
                  order by data_program_name asc";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        while ($row = mysqli_fetch_assoc($res)) {
            ?>
            <option value="<?php echo $row['id'] ?>"><?php echo $row['name'];
            echo " (";
            echo $row['code'];
            echo ")"?></option>
            <?php
        }
        ?>
        </select>
        </table>
        <?php
        //WHERE program_type = 'phenotyping'
    }

    private function displayTrial()
    {
        global $mysqli;
        $trial = $_GET['trial'];
        $sql = "select experiment_short_name, experiment_year, location, latitude, longitude, collaborator, planting_date, greenhouse_trial,
            seeding_rate, experiment_design, irrigation, other_remarks
            from experiments, phenotype_experiment_info
            where experiments.experiment_uid = ? 
            and experiments.experiment_uid = phenotype_experiment_info.experiment_uid";
        if ($stmt = mysqli_prepare($mysqli, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $trial);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $name, $year, $loc, $lat, $long, $colb, $plant_date, $greenhouse, $seeding, $design, $irrigation, $remarks);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);
        }
        $sel_alpha = "";
        $sel_bid = "";
        $sel_crd = "";
        $sel_lattice = "";
        $design = strtolower($design);
        if ($design == "alpha") {
            $sel_alpha = "selected";
        } elseif ($design == "bib") {
            $sel_bid = "selected";
        } elseif ($design == "crd") {
            $sel_crd = "selected";
        } elseif ($design == "lattice") {
            $sel_lattice = "selected";
        } 
        ?>
        <table>
        <tr><td width="120">Year:<td><?php echo $year; ?>
        <tr><td>Location:<td><?php echo $loc; ?>
        <tr><td width="120">Experiment Name:<td><?php echo $name; ?>
        <tr><td>Latitude of field:<td><?php echo $lat; ?>
        <tr><td>Longitude of field:<td><?php echo $long; ?>
        <tr><td>Collaborator:<td><?php echo $colb; ?>
        <tr><td>Trial description:<td><?php echo $desc; ?>
        <tr><td>Planting date:<td><?php echo $plant_date; ?>
        <tr><td>Greenhouse trial?<td><?php echo $greenhouse; ?>
        <tr><td>Seeding rate:<td><?php echo $seeding; ?>
        <tr><td id="type" name="type">Design type:<td>
        <select id="design" name="design" onchange="javascript: update_type(this.options)">
        <option value="">select design</option>
        <option value="alpha" <?php echo "$sel_alpa"; ?>>Alpha</option>
        <option value="bib" <?php echo "$sel_bid"; ?>>Random Balanced ICB</option>
        <option value="crd" <?php echo "$sel_crd"; ?>>Completely Random</option>
        <option value="lattice" <?php echo "$sel_lattice"; ?>>Lattice</option>
        <option value="dau">Augmented</option>
        <option value="rcbd">Random Complete Block</option>
        <option value="madii">Mod. Aug. Design</option>
        </select>
        <tr><td>Irrigation:<td>
        <tr><td>Other remarks:<td><?php echo $remarks; ?>
        </table><br><br>
        <script type="text/javascript">
        if ( window.addEventListener ) {
            window.addEventListener( "load", update_type(this.options), false );
        } else if ( window.onload ) {
            window.onload = "update_type()";
        }
        </script>
        <?php
    }

    private function design2()
    {
        global $mysqli;
        $CAPdata_program = $_GET['prog'];
        ?>
        <table>
        <tr><td width="120">Trial Name:
        <td><select id="trial" onchange="javascript: update_trial()">
        <option value="">select trial</option>
        <?php
        $sql = "SELECT DISTINCT e.experiment_uid AS id, e.trial_code as name, e.experiment_year AS year
                                FROM experiments AS e, CAPdata_programs, experiment_types AS e_t
                                WHERE e.CAPdata_programs_uid = CAPdata_programs.CAPdata_programs_uid
                                AND CAPdata_programs.CAPdata_programs_uid = ? 
                                AND e.experiment_type_uid = e_t.experiment_type_uid
                                AND e_t.experiment_type_name = 'phenotype'
                                order by name";
        if ($stmt = mysqli_prepare($mysqli, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $CAPdata_program);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $id, $name);
            while (mysqli_stmt_fetch($stmt)) {
              ?>
              <option value="<?php echo $id ?>"><?php echo $name;
              ?></option>
              <?php
            }
            mysqli_stmt_close($stmt);
        }
        echo "</select>";
        echo "</table>";
    }

    private function design3()
    {
        global $mysqli;
        ?>
        The trial design is generated by the <a href="http://cran.r-project.org/web/packages/agricolae/agricolae.pdf" target="blank">
        agricolae</a> package except for the Mod. Aug. Design type which uses a custom R script.<br>
        To create an experiment design, select a design type from the drop-down list<br><br>
        <?php
        if (isset($_SESSION['selected_lines'])) {
        } else {
           echo "<font color=red>Error:</font>
           Please select a <a href=\"pedigree/line_properties.php\">set of lines</a>.<br><br>\n";
           return;
        }
        ?>
        <table>
        <tr><td>Program:
        <td colspan=2><select id="program" name="program" onchange="javascript: update_step1()">
        <option value="">select program</option>
        <?php
        $sql = "SELECT CAPdata_programs_uid, data_program_name, institutions_uid, collaborator_name, data_program_code
            from CAPdata_programs
            order by data_program_name asc";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        while ($row = mysqli_fetch_assoc($res)) {
           ?>
           <option value="<?php echo $row['data_program_code'] ?>"><?php echo $row['data_program_name'];
           echo " ("; echo $row['data_program_code']; echo ")"?></option>
           <?php
        }
        ?>
        </select>
        <td>Program responsible for data collection
        <tr><td>Trial Name:<td colspan=2><input type="text" id="trial_name" onchange="javascript: update_step1()">
        <td>Format: "Experiment_YYYY_Location", where Experiment is short but descriptive, YYYY=Trial Year.<br>
                Trial Names should be unique across T3 for a crop. A trial is carried out at one location in one year.  
        <tr><td>Year:<td colspan=2>
        <select id="year" name="year" onchange="javascript: update_step1()">
        <option value="2015">2015</option>
        <option value="2014">2014</option>
        <option value="2013">2013</option>
        <option value="2012">2012</option>
        <option value="2011">2011</option>
        <option value="2010">2010</option> 
        </select>
        <tr><td>Location:<td colspan=2>
        <select id="location" onchange="javascript: update_step1()">
        <option value="">select location</option>
        <?php
        $sql = "SELECT distinct location as name from phenotype_experiment_info where location is not NULL order by location";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        while ($row = mysqli_fetch_assoc($res)) {
           ?>
           <option value="<?php echo $row['name'] ?>"><?php echo $row['name'] ?></option>
           <?php
         }
         ?>
         </select>
        <tr><td>Experiment Name:<td colspan=2><input type="text" id="exp_name" onclick="javascript: update_step1()">
        <td>Optional. The experiment is one hierarchical level above the trial.<br>
                An experiment may have several trials with similar or identical entry lists
        <tr><td>Latitude of field:<td colspan=2><input type="number" id="lat"><td rowspan="2">Decimal Degrees format, without a degree symbol, e.g. "47.824".<br>
                There is a converter at http://boulter.com/gps/.<br>
                GPS coordinates can be found by address at http://itouchmap.com/latlong.html.
        <tr><td>Longitude of field:<td colspan=2><input type="number" id="long">
        <tr><td>Collaborator:<td colspan=2><input type="text" id="collab"><td>Name of principal scientist.
        <tr><td>Trial description:<td colspan=2><input type="text" id="desc"><td> 
        <tr><td>Planting date:<td colspan=2><input type="text" id="plant_date"><td>This should be the one date on which planting was begun.<br>
                Use Excel "Text", not "Date", format. The value should be given as m/d/yyyy with no leading zeros, e.g. "5/7/2012".
        <tr><td>Harvest date:<td colspan=2><input type="text" id="harvest_date"><td>Use Excel "Text", not "Date", format. if the trial was not harvested,
                use the date of last data collection.
        <tr><td>Begin weather date:<td colspan=2><input type ="text" id="bwdate"><td>Optional, if T3 should store weather data starting at some point before planting (e.g., to track soil moisture status).
        <tr><td>Greenhouse trial
            <td><input type="radio" name="greenhouse" id="greenhouse" checked value="no">No
            <td><input type="radio" name="greenhouse" id="greenhouse" value="yes">Yes
        <tr><td>Seeding rate<td colspan=2><input type="text" id="seed"><td>This is the target density for the trial, not the actual rate for each line
        <tr><td>Design type:<td colspan=2>
        <select id="design" name="design" onchange="javascript: update_type(this.options)">
        <option value="">select design</option>
        <option value="alpha">Alpha</option>
        <option value="bib">Random Balanced ICB</option>
        <option value="crd">Completely Random</option>
        <option value="lattice">Lattice</option>
        <option value="dau">Augmented</option>
        <option value="rcbd">Random Complete Block</option>
        <option value="madii">Mod. Aug. Design</option>
        </select>
        <td><div id="design_desc"></div>

        <tr><td>Irrigation<td><input type="radio" name="irrigation" id="irrigation" checked value="no">No
            <td><input type="radio" name="irrigation" value="yes">Yes
        <tr><td>Other remarks<td colspan=2><input type="text"><td>Optional. Adjustments to means in data analysis or other specifics of statistical analysis.<br>Other notes that may help in interpretation of results, for example that harvest was delayed due to weather.
        </table>
        <?php
    }

    private function response1()
    {
        ?>
        <div class="step1">
        <input type="submit" value="Create" onclick="javascript: createTrial()">
        </div>
        <h3>Trial Design</h3>
        Design an experiment using <a href="http://cran.r-project.org/web/packages/agricolae" target="_new">agricolae</a> package.<br>
        <div class="step2">
        <table>
        <?php
        echo "<tr><td>Design type:<td><font color=red>Error: </font>Please select design type";
        if (isset($_SESSION['selected_lines'])) {
            $count = count($_SESSION['selected_lines']);
            echo "<tr><td>Treatment:<td>$count lines selected";
        } else {
            echo "<tr><td>Treatment:<td><font color=red>Error: </font>Please select a <a href=pedigree/line_properties.php>set of lines</a>";
        }
        ?> 
        </table>
        </div>
        <div class="step3"></div>
        <?php
    }

    private function designField()
    {
        global $mysqli;
        $type = $_GET['type'];
        if (!preg_match("/[A-Za-z]+/", $type)) {
            echo "Please select a design type\n";
            return;
        }
        $trial = $_GET['trial'];
        $count = "";
        if (preg_match("/\d/", $trial)) {
            $sql = "select count(distinct lr.line_record_uid) from tht_base as tb, line_records as lr
                where lr.line_record_uid = tb.line_record_uid
                and tb.experiment_uid = $trial";
            $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
            if ($row = mysqli_fetch_row($res)) {
                $count = $row[0];
            } elseif (isset($_SESSION['selected_lines'])) {
                $count = count($_SESSION['selected_lines']);
            } else {
                $msg = "<tr><td>Treatment:<td><font color=red>Error: </font>Please select a <a href=pedigree/line_properties.php>set of lines</a>"; 
            }
        } elseif (isset($_SESSION['selected_lines'])) {
            $count = count($_SESSION['selected_lines']);
        } else {
           $msg = "<tr><td>Treatment:<td><font color=red>Error: </font>Please select a <a href=pedigree/line_properties.php>set of lines</a>";
        }
        echo "<h3>Field layout</h3>";
        echo "<table>";
        if ($type == "alpha") {
            if ($count != "") {
                echo "<tr><td>Treatment:<td>$count lines selected";
            } else {
                echo "$msg";
            }
            ?>
            <tr><td>Size of block(k):<td><input type="text" id="size_blk" onclick="javascript: update_step3()">
            <tr><td>Number of replicates(r):<td><input type="text" id="num_rep" onclick="javascript: update_step3()">
            <tr><td><td>s=number of blocks
            <tr><td><td>I. r=2, k <= s; II. r=3, s odd, k <= s; III.r=3, s even, k <= s-1; IV. r=4, s odd but not a multiple of 3, k<=s
            <?php
        } elseif ($type == "bib") {
            if ($count != "") {
                echo "<tr><td>Treatment:<td>$count lines selected";
                if ($count > 100) {
                    echo "<td><font color=red>warning - ICB design is slow for over 100 lines</font>";
                }
            } else {
                echo "$msg";
            }
            ?>
            <tr><td>Size of block:<td>2
            <input type="hidden" id="size_blk" value=2>
            <?php
        } elseif ($type == "crd") {
            if ($count != "") {
                echo "<tr><td>Treatment:<td>$count lines selected";
            } else {
                echo "$msg";
            }
            ?>
            <tr><td>Number of replicates:<td><input type="text" id="num_rep" onclick="javascript: update_step3()">
            <?php
        } elseif ($type == "lattice") {
            if ($count != "") {
                $tmp1 = sqrt($count);
                $tmp2 = floor($tmp1);
                echo "<tr><td>Treatment:<td>$count lines selected";
                if (($tmp1 - $tmp2) > 0) {
                    echo "<td><font color=red>Error: </font>the square root of lines selected must be an integer";
                }
            } else {
                echo "$msg";
            }
            ?>
            <tr><td>Number of replicates:<td><input type="text" id="num_rep" onclick="javascript: update_step3()"><td>Use either 2 or 3
            <?php
        } elseif ($type == "rcb") {
            if ($count != "") {
                echo "<tr><td>Treatment:<td>$count lines selected";
            } else {
                echo "$msg";
            }
            ?>
            <tr><td>Number of replicates:<td><input type="text" id="num_rep" onclick="javascript: update_step3()">
            <?php
        } elseif ($type == "dau") {
            if (isset($_SESSION['check_lines'])) {
                $lines = $_SESSION['check_lines'];
                $lines_count = count($_SESSION['check_lines']);
                $lines_str = implode (",", $lines);
                $name = "";
                $sql = "select line_record_name from line_records where line_record_uid IN ($lines_str)";
                $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . $sql);
                while ($row = mysqli_fetch_assoc($res)) {
                    $tmp = $row['line_record_name'];
                    if ($name == "") {
                        $name = $tmp;
                    } else {
                        $name = $name . ", $tmp";
                    }
                }
                echo "<tr><td>Check lines:<td>$name<td>";
                echo "<input type=\"button\" value=\"Select check lines\" onclick=\"javascript: select_check()\">";
                if ($count != "") {
                    echo "<tr><td>Treatment:<td>$count lines selected";
                } else {
                    echo "$msg";
                }
                ?>
                <tr><td>Replications or Blocks:<td><input type="text" id="num_rep" onclick="javascript: update_step3()">
                <?php
             } else {
               echo "<tr><td><input type=\"button\" value=\"Select check lines\" onclick=\"javascript: select_check()\">
                <td>Select 1 or more check lines";
            }
        } elseif ($type == "rcbd") {
            if ($count != "") {
                echo "<tr><td>Treatment:<td>$count lines selected";
            } else {
                echo "$msg";
            }
            ?>
            <tr><td>Replications or Blocks:<td><input type="text" id="num_rep" onclick="javascript: update_step3()">
            <td>greater than or equal to 2
            <?php
        } elseif ($type == "madii") {
            if (isset($_SESSION['check_lines'])) {
                $lines = $_SESSION['check_lines'];
                $lines_count = count($lines);
                $nPriChk = 1;
                $nSecChk = $lines_count - 1;
                $lines_str = implode (",", $lines);
                $name = "";
                $sql = "select line_record_name from line_records where line_record_uid IN ($lines_str)";
                $res = mysqli_query($mysqli, $sql) or die(mysqli_error() . $sql);
                while ($row = mysqli_fetch_assoc($res)) {
                    $tmp = $row['line_record_name'];
                    if ($name == "") {
                        $name = $tmp;
                    } else {
                        $name = $name . ", $tmp";
                    }
                }
                echo "<tr><td>Check lines:<td>$name<td>";
                if ($lines_count < 2) {
                    echo "<font color=red>Error: select 2 or more check lines for MADII</font>";
                }
                echo "<input type=\"button\" value=\"Select check lines\" onclick=\"javascript: select_check()\">";
                if ($count != "") {
                    echo "<tr><td>Treatment:<td>$count lines selected";
                    ?>
                    <tr><td>Rows:<td><input type="text" id="rows"><td>also known as range
                    <tr><td>Columns:<td><input type="text" id="columns"><td>optional
                    <tr><td>Rows / Block:<td><input type="text" id="nRowPerBlk"><td>should be less then or equal to Rows
                    <tr><td>Columns / Block:<td><input type="text" id="nColPerBlk">
                    <tr><td>Extra Plot Fill:<td><select id="fillWith">
                                      <option value='Chk'>Check</option>
                                      <option value='Entry'>Entry</option>
                                      <option value='Filler'>Filler</option>
                        </select><td>extra plots in the field should be filled with
                    <tr><td>Primary Checks:<td><div id="nPriChk"><?php echo $nPriChk; ?></div><td>number of primary checks
                    <tr><td>Secondary Checks:<td><?php echo $nSecChk; ?><td>number of secondary checks
                    <tr><td>Checks / Block:<td><input type="text" id="nChksPerBlk" value="2"><td>number of checks per block
                         <br>
                    <?php
                } else {
                    echo "$msg";
                }
            } else {
               echo "<tr><td><input type=\"button\" value=\"Select check lines\" onclick=\"javascript: select_check()\">
                <td>Select 2 or more check lines";
            }
        }
        ?>
        </table>
        <?php
    }
 
    private function design_results()
    {
        $type = $_GET['type'];
        $trial = $_GET['trial'];
        if (($type == "madii") || ($type == "dau")) {
            if (isset($_SESSION['check_lines'])) {
                ?>
                <input type="submit" value="Create field layout" onclick="javascript: create_field()">
                <?php
            }
        } elseif (preg_match("/\d/", $trial)) {
                ?>
                <input type="submit" value="Create field layout" onclick="javascript: create_field()">
                <?php
        } elseif (isset($_SESSION['selected_lines'])) {
                ?>
                <input type="submit" value="Create field layout" onclick="javascript: create_field()">
                <?php
        }
    }

    function createTrial()
    {
        global $mysqli;
        if (isset($_GET['prg'])) {
            $program = $_GET['prg'];
        }
        if (isset($_GET['trial_name'])) {
            $trial_name = $_GET['trial_name'];
        }
        if (isset($_GET['year'])) {
            $year = $_GET['year'];
        }
        if (isset($_GET['exp_name'])) {
            $exp_name = $_GET['exp_name'];
        }
        if (isset($_GET['location'])) {
            $location = $_GET['location'];
        }
        if (isset($_GET['lat'])) {
            $lat = $_GET['lat'];
        }
        if (isset($_GET['long'])) {
            $long = $_GET['long'];
        }
        if (isset($_GET['collab'])) {
            $collab = $_GET['collab'];
        }
        $description = $_GET['desc'];
        $pdate = $_GET['pdate'];
        $hdate = $_GET['hdate'];
        $bwdate = $_GET['bwdate'];
        $greenhouse = $_GET['greenhouse'];
        $seed = $_GET['seed'];
        $design = $_GET['design']; 
        $irrigation = $_GET['irrigation'];

        $sql = "select value from settings where name='database'";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        if ($row = mysqli_fetch_assoc($res)) {
            $database = $row['value'];
            if (preg_match("/wheat/", $database)) {
                $database = "wheat";
            } elseif (preg_match("/barley/", $database)) {
                $database = "barley";
            }
        } else {
            $database = "unknown";
        }
         
        $filename = "/tmp/tht/TrialSubmission.xls";
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Trial Submission Form');
        $objPHPExcel->getActiveSheet()->SetCellValue('A2', 'Template version');
        $objPHPExcel->getActiveSheet()->SetCellValue('B2', '4Dec12');
        $objPHPExcel->getActiveSheet()->SetCellValue('A3', 'Crop');
        $objPHPExcel->getActiveSheet()->SetCellValue('B3', $database);
        $objPHPExcel->getActiveSheet()->SetCellValue('A4', 'Breeding Program Code');
        $objPHPExcel->getActiveSheet()->SetCellValue('B4', $program);
        $objPHPExcel->getActiveSheet()->SetCellValue('B5', 'TRIAL #1');
        $objPHPExcel->getActiveSheet()->SetCellValue('A6', 'Trial Name');
        $objPHPExcel->getActiveSheet()->SetCellValue('B6', $trial_name);
        $objPHPExcel->getActiveSheet()->SetCellValue('A7', 'Trial Year');
        $objPHPExcel->getActiveSheet()->SetCellValue('B7', $year);
        $objPHPExcel->getActiveSheet()->SetCellValue('A8', 'Experiment Name');
        $objPHPExcel->getActiveSheet()->SetCellValue('B8', $exp_name);
        $objPHPExcel->getActiveSheet()->SetCellValue('A9', 'Location');
        $objPHPExcel->getActiveSheet()->SetCellValue('B9', $location);
        $objPHPExcel->getActiveSheet()->SetCellValue('A10', 'Latitude of Field');
        $objPHPExcel->getActiveSheet()->SetCellValue('B10', $lat);
        $objPHPExcel->getActiveSheet()->SetCellValue('A11', 'Longitude of Field');
        $objPHPExcel->getActiveSheet()->SetCellValue('B11', $long);
        $objPHPExcel->getActiveSheet()->SetCellValue('A12', 'Collaborator');
        $objPHPExcel->getActiveSheet()->SetCellValue('B12', $collab);
        $objPHPExcel->getActiveSheet()->SetCellValue('A13', 'Trial description');
        $objPHPExcel->getActiveSheet()->SetCellValue('B13', $description);
        $objPHPExcel->getActiveSheet()->SetCellValue('A14', 'Planting date');
        $objPHPExcel->getActiveSheet()->SetCellValue('B14', $pdate);
        $objPHPExcel->getActiveSheet()->SetCellValue('A15', 'Harvest date');
        $objPHPExcel->getActiveSheet()->SetCellValue('B15', $hdate);
        $objPHPExcel->getActiveSheet()->SetCellValue('A16', 'Begin weather date');
        $objPHPExcel->getActiveSheet()->SetCellValue('B16', $bwdate);
        $objPHPExcel->getActiveSheet()->SetCellValue('A17', 'Greenhouse trial? (yes or no)');
        $objPHPExcel->getActiveSheet()->SetCellValue('B17', $greenhouse);
        $objPHPExcel->getActiveSheet()->SetCellValue('A18', 'Seeding rate (seeds/m2)');
        $objPHPExcel->getActiveSheet()->SetCellValue('B18', $seed);
        $objPHPExcel->getActiveSheet()->SetCellValue('A19', 'Experimental design');
        $objPHPExcel->getActiveSheet()->SetCellValue('B19', $design);
        $objPHPExcel->getActiveSheet()->SetCellValue('A20','Irrigation (yes or no)');
        $objPHPExcel->getActiveSheet()->SetCellValue('B20',$irrigation);
        $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
        $objWriter->save($filename);
        echo "<a href=\"$filename\">Download Trial Description</a><br><br>";
    }

    function createField()
    {
        global $mysqli;
        $objRWrap = new RWrap();
        global $config;
        $filename2 = "design.csv";
        $filename3 = "run.log";
        if (isset($_GET['type'])) {
            $exp_type = $_GET['type'];
            $cmd = "type <-c(\"$exp_type\")\n";
            $objRWrap->addCommand($cmd);
        }
        $trial = $_GET['trial'];
        if (preg_match("/\d/", $trial)) {
            $sql = "select trial_code from experiments where experiment_uid = $trial";
            $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
            if ($row = mysqli_fetch_row($res)) {
                $trial_code = $row[0];
            }
            $sql = "select distinct lr.line_record_uid, tb.check_line from tht_base as tb, line_records as lr
                where lr.line_record_uid = tb.line_record_uid
                and tb.check_line = \"no\"
                and tb.experiment_uid = $trial";
            $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
            while ($row = mysqli_fetch_row($res)) {
                $line_list[] = $row[0];
            }
            $count_lines = count($line_list);
            /* if there are no lines in selected experiment then use lines from session */
            if (($count_lines < 1) && isset($_SESSION['selected_lines'])) {
                $line_list = $_SESSION['selected_lines'];
            }
            $count_lines = count($line_list); 
            if ($count_lines > 1) {
                $exp = "";
                foreach ($line_list as $item) {
                    $sql = "select line_record_name from line_records where line_record_uid = $item";
                    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . $sql);
                    if ($row = mysqli_fetch_assoc($res)) {
                        $name = $row['line_record_name'];
                    } else {
                        die("Error: could not find line record $item<br>\n");
                    }
                    if ($exp == "") {
                        $exp = $exp . "\"$name\"";
                    } else {
                        $exp = $exp . ",\"$name\"";
                    }
                }
                $cmd = "trt <-c($exp)\n";
            }
            $objRWrap->addCommand($cmd);
        } elseif (isset($_SESSION['selected_lines'])) {
            $tmp = $_SESSION['selected_lines'];
            $count_lines = count($tmp);
            if ($count_lines > 1) {
                $exp = "";
                foreach ($tmp as $item) {
                    $sql = "select line_record_name from line_records where line_record_uid = $item";
                    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . $sql);
                    if ($row = mysqli_fetch_assoc($res)) { 
                        $name = $row['line_record_name'];
                    } else {
                        die("Error: could not find line record $item<br>\n");
                    }
                    if ($exp == "") {
                        $exp = $exp . "\"$name\"";
                    } else {
                        $exp = $exp . ",\"$name\"";
                    }
                }
                $cmd = "trt <-c($exp)\n";
            }
            $objRWrap->addCommand($cmd);
        } else {
            die("Error: no lines selected");
        }
        if (preg_match("/\d/", $trial)) {
            $sql = "select trial_code from experiments where experiment_uid = $trial";
            $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
            if ($row = mysqli_fetch_row($res)) {
                $trial_code = $row[0];
            }
            $sql = "select distinct lr.line_record_uid, tb.check_line from tht_base as tb, line_records as lr
                where lr.line_record_uid = tb.line_record_uid
                and tb.check_line = \"yes\"
                and tb.experiment_uid = $trial";
            $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
            while ($row = mysqli_fetch_row($res)) {
                $check_list[] = $row[0];
            }
            $count_check = count($check_list);
            /* if there are no lines in selected experiment then use lines from session */
            if (($count_checks < 1) && isset($_SESSION['check_lines'])) {
                $check_list = $_SESSION['check_lines'];
            }
            $exp = "";
            foreach ($check_list as $item) {
                $sql = "select line_record_name from line_records where line_record_uid = $item";
                $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . $sql);
                if ($row = mysqli_fetch_assoc($res)) {
                    $name = $row['line_record_name'];
                } else {
                    die("Error: could not find line record $item<br>\n");
                }
                if ($exp == "") {
                    $exp = $exp . "\"$name\"";
                } else {
                    $exp = $exp . ",\"$name\"";
                }
            }
            $cmd = "trt2 <-c($exp)\n";
            $objRWrap->addCommand($cmd);
        } elseif (isset($_SESSION['check_lines'])) {
            $tmp = $_SESSION['check_lines'];
            $count_lines = count($tmp);
            $exp = "";
            foreach ($tmp as $item) {
                $sql = "select line_record_name from line_records where line_record_uid = $item";
                $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
                if ($row = mysqli_fetch_assoc($res)) {
                    $name = $row['line_record_name'];
                } else {
                    die("Error: could not find line record $item<br>\n");
                }
                if ($exp == "") {
                    $exp = $exp . "\"$name\"";
                } else {
                    $exp = $exp . ",\"$name\"";
                }
            }
            $cmd = "trt2 <-c($exp)\n";
            $objRWrap->addCommand($cmd);
        }
        if (isset($_GET['size_blk']) && (!empty($_GET['size_blk']))) {
            $size_blk = $_GET['size_blk'];
            $cmd = "k <- $size_blk\n";
            $objRWrap->addCommand($cmd);
        }
        if (isset($_GET['num_rep']) && (!empty($_GET['num_rep']))) {
            $num_rep = $_GET['num_rep'];
            $cmd = "r <- $num_rep\n";
            $objRWrap->addCommand($cmd);
        }
        if (isset($_GET['num_row']) && (!empty($_GET['num_row']))) {
            $num_row = $_GET['num_row'];
            $cmd = "num_row <- $num_row\n";
        } else {
            $cmd = "num_row <- NULL\n";
        }
        $objRWrap->addCommand($cmd);
        if (isset($_GET['num_col']) && (!empty($_GET['num_col']))) {
            $num_col = $_GET['num_col'];
            $cmd = "num_col <- $num_col\n";
        } else {
            $cmd = "num_col <- NULL\n";
        }
        $objRWrap->addCommand($cmd);
        if (isset($_GET['nRowsPerBlk'])) {
            $nRowsPerBlk = $_GET['nRowsPerBlk'];
            $cmd = "nRowsPerBlk <- $nRowsPerBlk\n";
            $objRWrap->addCommand($cmd);
        }
        if (isset($_GET['nColsPerBlk'])) {
            $nColsPerBlk = $_GET['nColsPerBlk'];
            $cmd = "nColsPerBlk <- $nColsPerBlk\n";
            $objRWrap->addCommand($cmd);
        }
        if (isset($_GET['fillWith'])) {
            $fillWith = $_GET['fillWith'];
            $cmd = "fillWith <- \"$fillWith\"\n";
            $objRWrap->addCommand($cmd);
        }
        if (isset($_GET['nSecChk'])) {
            $nSecChk = $_GET['nSecChk'];
            $cmd = "nSecChk <- $nSecChk\n";
            $objRWrap->addCommand($cmd);
        }
        if (isset($_GET['nChksPerBlk'])) {
            $nChksPerBlk = $_GET['nChksPerBlk'];
            $cmd = "nChksPerBlk <- $nChksPerBlk\n";
            $objRWrap->addCommand($cmd);
        }
        if (isset($_GET['trial_name']) && !empty($_GET['trial_name'])) {
            $trial_code = $_GET['trial_name'];
        }
 
        /*error checking*/
        if ($exp_type == "alpha") {
            if ($count_lines % $size_blk) {
            } else {
                $tmp = $count_lines % $size_blk;
                echo "results = $tmp\n";
                die("Error: The size of the block is not appropriate\n the number of treatments must be multiple of k (size block)");
            }
        }
 
        $cmd = "outfile <- \"$filename2\"\n";
        $objRWrap->addCommand($cmd);
        $cmd = "exp <- \"$trial_code\"\n";
        $objRWrap->addCommand($cmd);
        $cmd = "common_code <- \"" . $config['root_dir'] . "R/madii.R\"\n";
        $objRWrap->addCommand($cmd);
        //$cmd = "setwd(\"/tmp/tht/$unique_str\")\n";
        //$objRWrap->addCommand($cmd);
        $objRWrap->close();

        if ($exp_type == "madii") {
            $objRWrap->runCommand("madii.R");
            //exec("cat /tmp/tht/$unique_str/$filename1 ../R/design_madii.R | R --vanilla > /dev/null 2> /tmp/tht/$unique_str/$filename3");
            $filename2 = "fieldlayout.csv";
        } else {
            $objRWrap->runCommand("design.R");
            //exec("cat /tmp/tht/$unique_str/$filename1 ../R/design.R | R --vanilla > /dev/null 2> /tmp/tht/$unique_str/$filename3"); 
        }
        $log = $objRWrap->getResults($filename3);
        echo "$log\n";
        if (file_exists("/tmp/tht/$unique_str/$filename3")) {
            $h = fopen("/tmp/tht/$unique_str/$filename3", "r");
            while ($line=fgets($h)) {
                echo "$line<br>\n";
            }
            fclose($h);
        }

        $log = $objRWrap->getLink($filename2);
        if ($log == "") {
            echo "no output file $filename2\n";
        } else {
            echo "<a href=\"$log\" target=\"_new\">Download Trial Design</a><br><br>";
        }
        echo "<input type=\"button\" value=\"Clear Selection\" onclick=\"javascript: update_type(this.options)\">";
    }

    /**
     * save selected lines as checks
     *
     * @return null
     */ 
    function saveLines()
    {
        if (isset($_POST['LineSearchInput'])) {
            $line_str = $_POST['LineSearchInput'];
            $lines = explode(',', $line_str);
            $_SESSION['check_lines'] = $lines;
        } else {
            echo "Error: no lines selected\n";
        }
    }
}
