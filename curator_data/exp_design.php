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

new Fieldbook($_GET['function']);

class Fieldbook
{
    public function __construct($function = null)
    {
        switch($function)
        {
        case 'design':
            $this->settings();
            break;
        case 'create':
            $this->create();
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
        Design an experiment using <a href="http://cran.r-project.org/web/packages/agricolae" target="_new">agricolae</a> package.<br>
        1. First select a set of lines.<br>
        2. Return to this page and select trial name, program, and location.<br>
        2. Then select design type, replicates, blocks, block size, and checks.<br>
        3. Then either download the Field Layout or submit experiment to curator for loading on the production server.<br><br>
        <?php
        $mgs1 = "";
        $msg2 = "";

        if (isset($_SESSION['selected_lines'])) {
            $lines = $_SESSION['selected_lines'];
            $selectedcount = count($lines);
            $msg1 = "$selectedcount lines selected";
        } else {
            if ($mgs2 = "") {
                $msg2 = "Select a set of lines";
            } else {
                $msg2 = $msg2 . " and a set of lines.";
            }
        }
        if ($msg2 != "") {
            echo "$msg2";
        }
        if ($msg2 == "") {
            $this->design();
        }
        ?>
        </div>
        <script type="text/javascript" src="curator_data/design.js"></script>
        <?php
        include $config['root_dir'].'theme/footer_new.php';
    }
 
    private function design()
    {
        ?>
        <h3>Experiment Description</h3>
        <table>
        <tr><td>Program Code:<td><input type="text"><td>Program responsible for data collection (barley: 2-letter code; wheat: 3-lettercode)<br>
                Find codes at the T3 Homepage 'About T3' menu, 'CAP programs
        <tr><td>Trial Name:<td><input type="text"><td>Format: "Experiment_YYYY_Location", where Experiment is short but descriptive, YYYY=Trial Year.<br>
                Trial Names should be unique across T3 for a crop. A trial is carried out at one location in one year.  
        <tr><td>Year:<td>
        <select id="year" name="year">
        <option value="2014">2014</option>
        </select>
        <tr><td>Location:<td>
        <select id="location">
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
        <tr><td>Experiment Name:<td><input type="text"><td>Optional. The experiment is one hierarchical level above the trial.<br>
                An experiment may have several trials with similar or identical entry lists
        <tr><td>Latitude of field:<td><input type="text"><td rowspan="2">Decimal Degrees format, without a degree symbol.<br>
                There is a converter at http://boulter.com/gps/.<br>
                GPS coordinates can be found by address at http://itouchmap.com/latlong.html.
        <tr><td>Longitude of field:<td><input type="text">
        <tr><td>Collaborator:<td><input type="text"><td>Name of principal scientist.
        <tr><td>Planting date:<td><input type="text"><td>This should be the one date on which planting was begun.<br>
                Use Excel "Text", not "Date", format. The value should be given as m/d/yyyy with no leading zeros, e.g. "5/7/2012".
        <tr><td>Greenhouse trial?<td><input type="radio" name="greenhouse" value="no">No<input type="radio" name="greenhouse" value="yes">Yes
        <tr><td>Seeding rate<td><input type="text"><td>This is the target density for the trial, not the actual rate for each line
        <tr><td>Irrigation?<td><input type="radio" name="irrigation" value="no">No<input type="radio" name="irrigation" value="yes">Yes
        <tr><td>Other remarks<td><input type="text"><td>Optional. Adjustments to means in data analysis or other specifics of statistical analysis.<br>Other notes that may help in interpretation of results, for example that harvest was delayed due to weather.
        </table>
        <h3>Experiment Design</h3>
        <table>
        <tr><td>Design type:<td>
        <select id="type" name="type" onchange="javascript: update_type(this.options)">
        <option>select design</option>
        <option value="alpha" disabled>Alpha</option>
        <option value="bib" disabled>Random Balanced ICB</option>
        <option value="crd">Completely Random</option>
        <option value="lattice" disabled>Lattice</option>
        <option value="dau" disabled>Augmented</option>
        <option value="rcbd" disabled>Random Complete Block</option>
        </select>
        </table>
        <div class="step1"></div>
        <div class="step2"></div>
        <?php
    }

    private function settings()
    {
        $type = $_GET['type'];
        echo "<table>";
        if ($type == "alpha") {
            ?>
            <tr><td>Treatment:<td><input type="text" id="trt"><td>(Exp. 1,2,3..)
            <tr><td>Size of block:<td><input type="text" id="size_blk">
            <tr><td>Number of replicates:<td><input type="text" id="num_rep">
            <?php
        } elseif ($type == "bib") {
            ?>
            <tr><td>Treatment:<td><input type="text" id="trt"><td>(Exp. 1,2,3..)
            <tr><td>Size of block:<td><input type="text" id="size_blk">
            <?php
        } elseif ($type == "crd") {
            ?>
            <tr><td>Treatment:<td><input type="text" id="trt"><td>(Exp. 1,2,3.. or A,B,C)
            <tr><td>Number of replicates:<td><input type="text"id="num_rep">
            <?php
        } elseif ($type == "lattice") {
            echo "<tr><td>Treatment:<td><p1><input type='text'></p1>\n";
            echo "<tr><td>Number of replicates:<td><p3><input type='text'><td>valid values (2,3)</p3>\n";
        } elseif ($type == "rcb") {
            echo "<tr><td>Treatment:<td><p1><input type='text'></p1>\n";
            echo "<tr><td>Number of replicates:<td><p3><input type='text'></p3>\n";
        } elseif ($type == "dau") {
            ?>
            <tr><td>Checks:<td><p1><input type="text" id="check">
            <tr><td>New:<td><p2><input type="text" id="check">
            <tr><td>Replications or Blocks:<td><input type="text" id="repl">
            <?php
        } elseif ($type == "rcbd") {
            ?>
            <tr><td>Treatment:<td><input type="text" id="trt">
            <tr><td>Replications or Blocks:<td><input type="text" id="repl">
            <?php
        }
        ?>
        </table>
        <input type="submit" value="Create" onclick="javascript: create()">
        <?php
    }

    private function create()
    {
        $dir = '/tmp/tht/';
        $filename1 = "test.R";
        $filename2 = "/tmp/tht/test.out";
        $filename3 = "test.log";
        $h = fopen($dir.$filename1, "w+");
        if (isset($_GET['type'])) {
            $tmp = $_GET['type'];
            $cmd = "type <-c(\"$tmp\")\n";
            fwrite($h, $cmd);
        }
        if (isset($_GET['trt'])) {
            $tmp_str = $_GET['trt'];
            $tmp = explode(',', $tmp_str);
            $count = count($tmp);
            if ($count > 1) {
                $exp = "";
                foreach($tmp as $item) {
                  if ($exp == "") {
                      $exp = $exp . "\"$item\"";
                  } else {
                      $exp = $exp . ",\"$item\"";
                  }
                }
                $cmd = "trt <-c($exp)\n";
            } else {
                $cmd = "trt <-c($tmp_str)\n";
            }
            fwrite($h, $cmd);
        }
        if (isset($_GET['size_blk'])) {
            $tmp = $_GET['size_blk'];
            $cmd = "k <- $tmp\n";
            fwrite($h, $cmd);
        }
         if (isset($_GET['num_rep'])) {
            $tmp = $_GET['num_rep'];
            $cmd = "r <- $tmp\n";
            fwrite($h, $cmd);
        }
 
        $cmd = "outfile <- \"$filename2\"\n";
        fwrite($h, $cmd);
        fclose($h);

        exec("cat /tmp/tht/$filename1 ../R/design.R | R --vanilla > /dev/null 2> /tmp/tht/$filename3");
        if (file_exists($filename3)) {
            $h = fopen($filename3, "r");
            while ($line=fgets($h)) {
                echo "$line<br>\n";
            }
            fclose($h);
        }

        if (file_exists($filename2)) {
            $h = fopen($filename2, "r");
            while ($line=fgets($h)) {
                echo "$line<br>\n";
            }
            fclose($h);
        } else {
            echo "no output file\n";
        }
    }
}
