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
 * @link     http://triticeaetoolbox.org/wheat/downloads/downloads.php
 * 
 */

require_once 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/PHPExcel/Classes');
require $config['root_dir'] . 'lib/PHPExcel/Classes/PHPExcel/IOFactory.php';
connect();

new Fieldbook($_GET['function']);

class Fieldbook
{
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
            $this->create_trial();
            break;
        case 'create_field':
            $this->create_field();
            break;
        case 'saveChecks':
            $this->saveChecks();
            break;
        default:
            $this->type_checksession();
            break;
        }
    }

    private function type_checksession()
    {
        global $config;
        include $config['root_dir'].'theme/admin_header_new.php';
        ?>
        <h2>Manage trials</h2>
        <?php
        $this->design();
        ?>
        </div>
        <?php
        include $config['root_dir'].'theme/footer_new.php';
    }
 
    private function design()
    {
        ?>
        <br>
        <b>Phenotype Trial</b>
        <table><tr><td>
        <td><input type=button value="Select" onclick="javascript: select_trial()">
        <td><input type=button value="Upload trial" onclick="javascript: upload_trial()">
        <td><input type=button value="Add trial" onclick="javascript: add_trial()">
        </table><br>
        <div class="step1"></div>
        <div class="step2"></div>
        <div class="step3"></div>
        <div class="step4"></div>
        <div class="step5"></div>
        <script type="text/javascript" src="curator_data/design.js"></script>
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
        ?>
        <table>
        <tr><td width="120">Program:
        <td><select id="program" onchange="javascript: update_step1()">
        <option value="">select program</option>
        <?php
        $sql = "SELECT DISTINCT dp.CAPdata_programs_uid AS id, data_program_name AS name, data_program_code AS code
                  FROM experiments AS e, CAPdata_programs AS dp
                  WHERE program_type = 'breeding'
                  AND dp.CAPdata_programs_uid = e.CAPdata_programs_uid
                  order by data_program_name asc";
        $res = mysql_query($sql) or die(mysql_error());
        while ($row = mysql_fetch_assoc($res)) {
           ?>
           <option value="<?php echo $row['id'] ?>"><?php echo $row['name'];
           echo " ("; echo $row['code']; echo ")"?></option>
           <?php
        }
        ?>
        </select>
        </table>
        <?php
    }

    private function displayTrial()
    {
        $trial = $_GET['trial'];
        $sql = "select experiment_short_name, experiment_year, location, latitude, longitude, collaborator, planting_date, greenhouse_trial,
            seeding_rate, experiment_design, irrigation, other_remarks
            from experiments, phenotype_experiment_info
            where experiments.experiment_uid = $trial
            and experiments.experiment_uid = phenotype_experiment_info.experiment_uid";
        $res = mysql_query($sql) or die(mysql_error());
        if ($row = mysql_fetch_assoc($res)) {
            $year = $row['experiment_year'];
            $loc = $row['location'];
            $name = $row['experiment_short_name'];
            $lat = $row['latitude'];
            $long = $row['longitude'];
            $colb = $row['collaborator'];
            $plant_date = $row['planting_data'];
            $greenhouse = $row['greenhouse_trial'];
            $seeding = $row['seeding_rage'];
            $design = $row['experiment_design'];
            $irrigation = $row['irrigation'];
            $remarks = $row['other_remarks'];
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
        <tr><td id="type" name="type">Design type:<td><?php echo $design; ?>
        <tr><td>Irrigation:<td>
        <tr><td>Other remarks:<td><?php echo $remarks; ?>
        </table>
        <script type="text/javascript">
        //if ( window.addEventListener ) {
        //    window.addEventListener( "load", update_type(), false );
        //} else if ( window.onload ) {
        //    window.onload = "update_type()";
        //}
        </script>
        <?php
    }

    private function design2()
    {
        $CAPdata_program = $_GET['prog'];
        ?>
        <table>
        <tr><td width="120">Trial Name:
        <td><select id="trial" onchange="javascript: update_trial()">
        <option value="">select trial</option>
        <?php
        $sql = "SELECT DISTINCT e.experiment_uid AS id, e.trial_code as name, e.experiment_year AS year
                                FROM experiments AS e, datasets AS ds, datasets_experiments AS d_e, experiment_types AS e_t
                                WHERE e.experiment_uid = d_e.experiment_uid
                                AND d_e.datasets_uid = ds.datasets_uid
                                AND ds.CAPdata_programs_uid IN ($CAPdata_program)
                                AND e.experiment_type_uid = e_t.experiment_type_uid
                                AND e_t.experiment_type_name = 'phenotype'";
        $res = mysql_query($sql) or die(mysql_error());
        while ($row = mysql_fetch_assoc($res)) {
           ?>
           <option value="<?php echo $row['id'] ?>"><?php echo $row['name'];
           ?></option>
           <?php
        }
        echo "</select>";
        echo "</table>";
    }

    private function design3()
    {
        ?>
        This tool allows you to create a new trial.
        The results can be downloaded or submitted to the curator for loading into the production website.<br>
        <?php
        if (isset($_SESSION['selected_lines'])) {
        } else {
           echo "<font color=red>Error:</font>
           Please select a <a href=\"pedigree/line_properties.php\">set of lines</a>.<br><br>\n";
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
        $res = mysql_query($sql) or die(mysql_error());
        while ($row = mysql_fetch_assoc($res)) {
           ?>
           <option value="<?php echo $row['CAPdata_programs_uid'] ?>"><?php echo $row['data_program_name'];
           echo " ("; echo $row['data_program_code']; echo ")"?></option>
           <?php
        }
        ?>
        </select>
        <td>Program responsible for data collection
        <tr><td>Trial Name:<td colspan=2><input type="text" id="name" onchange="javascript: update_step1()">
        <td>Format: "Experiment_YYYY_Location", where Experiment is short but descriptive, YYYY=Trial Year.<br>
                Trial Names should be unique across T3 for a crop. A trial is carried out at one location in one year.  
        <tr><td>Year:<td colspan=2>
        <select id="year" name="year" onchange="javascript: update_step1()">
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
        $res = mysql_query($sql) or die(mysql_error());
        while ($row = mysql_fetch_assoc($res)) {
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
        <tr><td>Harvest data:<td colspan=2><input type="text" id="harvest_date"><td>Use Excel "Text", not "Date", format. if the trial was not harvestd,
                use the date of last data collection.
        <tr><td>Greenhouse trial<td><input type="radio" name="greenhouse" checked value="no">No<td><input type="radio" name="greenhouse" value="yes">Yes
        <tr><td>Seeding rate<td colspan=2><input type="text"><td>This is the target density for the trial, not the actual rate for each line
        <tr><td>Design type:<td colspan=2>
        <select id="type" name="type" onchange="javascript: update_type(this.options)">
        <option>select design</option>
        <option value="alpha">Alpha</option>
        <option value="bib">Random Balanced ICB</option>
        <option value="crd">Completely Random</option>
        <option value="lattice">Lattice</option>
        <option value="dau">Augmented</option>
        <option value="rcbd">Random Complete Block</option>
        <option value="madii">MADII</option>
        </select>

        <tr><td>Irrigation<td><input type="radio" name="irrigation" checked value="no">No<td><input type="radio" name="irrigation" value="yes">Yes
        <tr><td>Other remarks<td colspan=2><input type="text"><td>Optional. Adjustments to means in data analysis or other specifics of statistical analysis.<br>Other notes that may help in interpretation of results, for example that harvest was delayed due to weather.
        </table>
        <?php
    }

    private function response1()
    {
        ?>
        <div class="step1">
        <input type="submit" value="Create" onclick="javascript: create_trial()">
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
        $type = $_GET['type'];
        echo "<table>";
        if (isset($_GET['type'])) {
            //echo "<tr><td>Design type:<td>$type";
        } else {
            //echo "<tr><td>Design type:<td><font color=red>Error: </font>Please select design type";
        }
        if ($type == "alpha") {
            if (isset($_SESSION['selected_lines'])) {
            $count = count($_SESSION['selected_lines']);
                echo "<tr><td>Treatment:<td>$count lines selected";
            } else {
                echo "<tr><td>Treatment:<td><font color=red>Error: </font>Please select a <a href=pedigree/line_properties.php>set of lines</a>";
            }
            ?>
            <tr><td>Size of block:<td><input type="text" id="size_blk" onclick="javascript: update_step3()">
            <tr><td>Number of replicates:<td><input type="text" id="num_rep" onclick="javascript: update_step3()">
            <?php
        } elseif ($type == "bib") {
            if (isset($_SESSION['selected_lines'])) {
                $count = count($_SESSION['selected_lines']);
                echo "<tr><td>Treatment:<td>$count lines selected";
            } else {
                echo "<tr><td>Treatment:<td><font color=red>Error: </font>Please select a <a href=pedigree/line_properties.php>set of lines</a>";
            }
            ?>
            <tr><td>Size of block:<td><input type="text" id="size_blk" onclick="javascript: update_step3()">
            <?php
        } elseif ($type == "crd") {
            if (isset($_SESSION['selected_lines'])) {
                $count = count($_SESSION['selected_lines']);
                echo "<tr><td>Treatment:<td>$count lines selected";
            } else {
                echo "<tr><td>Treatment:<td><font color=red>Error: </font>Please select a <a href=pedigree/line_properties.php>set of lines</a>";
            }
            ?>
            <tr><td>Number of replicates:<td><input type="text" id="num_rep" onclick="javascript: update_step3()">
            <?php
        } elseif ($type == "lattice") {
            if (isset($_SESSION['selected_lines'])) {
                $count = count($_SESSION['selected_lines']);
                echo "<tr><td>Treatment:<td>$count lines selected";
            } else {
                echo "<tr><td>Treatment:<td><font color=red>Error: </font>Please <a href=pedigree/line_properties.php>select a set of lines</a>";
            }
            ?>
            <tr><td>Number of replicates:<td><input type="text" id="num_rep" onclick="javascript: update_step3()">
            <?php
        } elseif ($type == "rcb") {
            if (isset($_SESSION['selected_lines'])) {
                $count = count($_SESSION['selected_lines']);
                echo "<tr><td>Treatment:<td>$count lines selected";
            } else {
                echo "<tr><td>Treatment:<td><font color=red>Error: </font>Please <a href=pedigree/line_properties.php>select a set of lines</a>";
            }
            ?>
            <tr><td>Number of replicates:<td><input type="text" id="num_rep" onclick="javascript: update_step3()">
            <?php
        } elseif ($type == "dau") {
            if (isset($_SESSION['check_lines'])) {
                $count = count($_SESSION['check_lines']);
                echo "<tr><td>Check lines:<td>$count lines selected";
                if (isset($_SESSION['selected_lines'])) {
                    $count = count($_SESSION['selected_lines']);
                    echo "<tr><td>Treatment:<td>$count lines selected";
                } else {
                    echo "<tr><td>Treatment:<td><font color=red>Error: </font>Please <a href=pedigree/line_properties.php>select a set of lines</a>";
                }
                ?>
                <tr><td>Replications or Blocks:<td><input type="text" id="num_rep" onclick="javascript: update_step3()">
                <?php
             } elseif (isset($_SESSION['selected_lines'])) {
                $count = count($_SESSION['selected_lines']);
                echo "<tr><td>$count Lines selected<td><input type=\"button\" value=\"Save as checks\" onclick=\"javascript: saveChecks()\">
                <td>Then select treatment lines and return to this page";
             } else {
                echo "<tr><td>Treatment:<td><font color=red>Error: </font>Please <a href=pedigree/line_properties.php>select a set of lines</a>";
             }
        } elseif ($type == "rcbd") {
            ?>
            <tr><td>Replications or Blocks:<td><input type="text" id="num_rep" onclick="javascript: update_step3()">
            <td>greater than or equal to 2
            <?php
        } elseif ($type == "madii") {
            if (isset($_SESSION['check_lines'])) {
                $count = count($_SESSION['check_lines']);
                echo "<tr><td>Check lines:<td>$count lines selected";
                if (isset($_SESSION['selected_lines'])) {
                    $count = count($_SESSION['selected_lines']);
                    echo "<tr><td>Treatment:<td>$count lines selected";
                    ?>
                    <tr><td>Rows:<td><input type="text" id="rows">
                    <tr><td>Columns:<td><input type="text" id="columns">
                    <?php
                } else {
                    echo "<tr><td>Treatment:<td><font color=red>Error: </font>Please <a href=pedigree/line_properties.php>select a set of lines</a>";
                }
            } elseif (isset($_SESSION['selected_lines'])) {
                $count = count($_SESSION['selected_lines']);
                echo "<tr><td>$count Lines selected<td><input type=\"button\" value=\"Save as checks\" onclick=\"javascript: saveChecks()\">
                <td>Then select treatment lines and return to this page";
            } else {
                echo "<tr><td>Treatment:<td><font color=red>Error: </font>Please <a href=pedigree/line_properties.php>select a set of lines</a>";
            }
        }
        ?>
        </table>
        <?php
    }
 
    private function design_results()
    {
        ?>
        <input type="submit" value="Create field layout" onclick="javascript: create_field()">
        <?php
    }

    private function create_trial()
    {
        if (isset($_GET['prg'])) {
            $program = $_GET['prg'];
        }
        $program = $_POST["program"];
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

        $sql = "select value from settings where name='database'";
        $res = mysql_query($sql) or die(mysql_error());
        if ($row = mysql_fetch_assoc($res)) {
            $database = $row['value'];
            if (preg_match("/wheat/", $database)) {
                $database = "wheat";
            } elseif (preg_match("/barley/", $database)) {
                $database = "barley";
            }
        } else {
            $database = "unknown";
        }
         
        $filename = "/tmp/tht/testfile.xls";
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
        $objPHPExcel->getActiveSheet()->SetCellValue('A19', 'Experiment design');
        $objPHPExcel->getActiveSheet()->SetCellValue('B19', $design);
        $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
        $objWriter->save($filename);
        echo "<a href=\"$filename\">Download Trial Description</a><br>";
    }

    private function create_field()
    {
        $unique_str = $_GET['unq'];
        $dir = "/tmp/tht/$unique_str/";
        $filename1 = "argico.R";
        $filename2 = "design.csv";
        $filename3 = "design.log";
        mkdir("/tmp/tht/$unique_str");
        $h = fopen($dir.$filename1, "w+");
        if (isset($_GET['type'])) {
            $exp_type = $_GET['type'];
            $cmd = "type <-c(\"$exp_type\")\n";
            fwrite($h, $cmd);
        }
        if (isset($_GET['trial_name'])) {
            $trial_code = $_GET['trial_name'];
        }
        if (isset($_SESSION['selected_lines'])) {
            $tmp = $_SESSION['selected_lines'];
            $count_lines = count($tmp);
            if ($count_lines > 1) {
                $exp = "";
                foreach($tmp as $item) {
                  $sql = "select line_record_name from line_records where line_record_uid = $item";
                  $res = mysql_query($sql) or die(mysql_error());
                  if ($row = mysql_fetch_assoc($res)) { 
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
            } else {
                $cmd = "trt <-c($tmp_str)\n";
            }
            fwrite($h, $cmd);
        } else {
          die("Error: no lines selected");
        }
        if (isset($_SESSION['check_lines'])) {
            $tmp = $_SESSION['check_lines'];
            $count_lines = count($tmp);
            if ($count_lines > 1) {
                $exp = "";
                foreach($tmp as $item) {
                  $sql = "select line_record_name from line_records where line_record_uid = $item";
                  $res = mysql_query($sql) or die(mysql_error());
                  if ($row = mysql_fetch_assoc($res)) {
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
            } else {
                $cmd = "trt2 <-c($tmp_str)\n";
            }
            fwrite($h, $cmd);
        }
        if (isset($_GET['size_blk']) && (!empty($_GET['size_blk']))) {
            $size_blk = $_GET['size_blk'];
            $cmd = "k <- $size_blk\n";
            fwrite($h, $cmd);
        }
        if (isset($_GET['num_rep']) && (!empty($_GET['num_rep']))) {
            $num_rep = $_GET['num_rep'];
            $cmd = "r <- $num_rep\n";
            fwrite($h, $cmd);
        }

        /*error checking*/
        if ($exp_type == "alpha") {
            if (($count_lines % $size_blk) != 0) {
                die("Error: The size of the block is not appropriate\n the number of treatments must be multiple of k (size block)");
            }
        }
 
        $cmd = "outfile <- \"$filename2\"\n";
        fwrite($h, $cmd);
        $cmd = "exp <- \"$trial_code\"\n";
        fwrite($h, $cmd);
        fwrite($h, "setwd(\"/tmp/tht/$unique_str\")\n");
        fclose($h);

        exec("cat /tmp/tht/$unique_str/$filename1 ../R/design.R | R --vanilla > /dev/null 2> /tmp/tht/$unique_str/$filename3");
        if (file_exists("/tmp/tht/$unique_str/$filename3")) {
            $h = fopen("/tmp/tht/$unique_str/$filename3", "r");
            while ($line=fgets($h)) {
                echo "$line<br>\n";
            }
            fclose($h);
        }

        if (file_exists("/tmp/tht/$unique_str/$filename2")) {
            echo "<a href=\"/tmp/tht/$unique_str/$filename2\" target=\"_new\">Download Trial Design</a>";
        } else {
            echo "no output file\n";
        }
    }

    private function saveChecks()
    {
        if (isset($_SESSION['selected_lines'])) {
          $_SESSION['check_lines'] = $_SESSION['selected_lines'];
          unset($_SESSION['selected_lines']);
          echo "Saved selected lines as checks<br>\n";
        } else {
          echo "Error: no line selection<br>\n";
        }
    }
}
