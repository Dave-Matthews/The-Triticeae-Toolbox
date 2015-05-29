<?php
require 'config.php';
require_once $config['root_dir'].'includes/bootstrap.inc';

set_time_limit(0);

$mysqli = connecti();
$ref_genotype = "2014_HapMap_WEC";

new Impute($_GET['function']);

/** Use a PHP class to implement the "Imputation" feature
 *
 * author Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/downloads/downloads.php
 **/
class Impute
{
    public function __construct($function = null)
    {
        switch($function)
        {
            case 'createVCF':
                $this->createVCF();
                break;
            default:
                $this->displayPage();
                break;
        }
    }

    private function displayPage()
    {
        global $config;
        require_once $config['root_dir'].'theme/normal_header.php';
        ?>
    <script src="//code.jquery.com/jquery-1.11.1.js"></script>
    <script type="text/javascript" src="downloads/impute.js"></script>
    <h2>Imputation using experiment: 2014_HapMap_WEC</h2>
    <h3>This tool uses <a href="http://faculty.washington.edu/browning/beagle/beagle.html">Beagle version 4.0</a> to impute genotype data.</h3>
    <table><tr><td>
    <b>Concensus Genotype data</b><br>
    1. Select a set of lines<br>
    2. Select a chromosome arm<br>
    3. Create a VCF file<br><br>
    <td>
    <b>Single Experiment Genotype data</b><br>
    1. Select a Genotype Experiment<br>
    2. Select a chromosome arm<br>
    3. Create a VCF file<br><br>
    </table><br>
    <?php
    global $mysqli;
    global $ref_genotype;
    $selected_map = "Chromosome Survey Sequence, 2014";

    if (isset($_SESSION['geno_exps'])) {
        $geno_exps = $_SESSION['geno_exps'];
        $geno_exps = $geno_exps[0];
        $sql = "select trial_code from experiments where experiment_uid = $geno_exps";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . $sql);
        if ($row = mysqli_fetch_array($res)) {
            $trial_code = $row[0];
            echo "Reference experiment: $ref_genotype<br>\n";
            echo "Genotype experiment name: $trial_code<br>\n";
            echo "Genotype experiment lines: ";
            $sql = "select count(*) from allele_frequencies where experiment_uid = $geno_exps";
            $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . $sql);
            if ($row = mysqli_fetch_array($res)) {
                $count = $row[0];
                echo "$count<br>\n";
            } else {
                echo "none<br>\n";
            }
            $sql = "select count(distinct(marker_report_reference.marker1_uid)), count(allele_frequencies.marker_uid) from marker_report_reference, allele_frequencies
            where marker_report_reference.marker1_uid=allele_frequencies.marker_uid
            and experiment_uid = $geno_exps";
            $res = mysqli_query($mysqli, $sql);
            echo "Markers matched to reference contigs: ";
            if ($res) {
                if ($row = mysqli_fetch_array($res)) {
                    $count = $row[0];
                    echo "$count<br>\n";
                } else {
                    echo "none<br>\n";
                }
            } else {
                echo "no results in database<br>\n";
            }
        } else {
            echo "Experiment: unknown<br>\n";
        }
    } elseif (isset($_SESSION['selected_lines'])) {
        $lines = $_SESSION['selected_lines'];
        $count_lines = count($lines);
        echo "Reference experiment: $ref_genotype<br>\n";
        echo "Selected lines: $count_lines<br>\n";
    } else {
        echo "<font color=red>Please select a set of lines or genotype experiment</font>\n";
    }

    $sql = "select mapset_uid from mapset where mapset_name = \"$selected_map\"";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    if ($row = mysqli_fetch_array($res)) {
        $mapset_uid = $row[0];
    } else {
        echo "Error: can not find reference map\n";
    }

    if (isset($_SESSION['geno_exps']) || isset($_SESSION['selected_lines'])) {
        echo "<br>Select chromosome<br><select>";
        $sql = "select distinct(chromosome) from markers_in_maps
            order by chromosome";
        $sql = "select distinct(mim.chromosome) from markers, markers_in_maps as mim, map
        where mim.marker_uid = markers.marker_uid
        AND mim.map_uid = map.map_uid
        AND map.mapset_uid = $mapset_uid";
        $res = mysqli_query($mysqli, $sql);
        while ($row = mysqli_fetch_array($res)) {
            $chr = $row[0];
            echo "<option value=$chr>$chr</option>\n";
        }
        echo "</select><br><br>";
        ?>
        <input type="button" value="Create VCF file" onclick="javascript:createVCF('');"><br><br>
        <?php
    }
    ?>
    <div id="result"></div>
    </div>;
    <?php
    require_once $config['root_dir'].'theme/footer.php';
    }

    public function createVCF()
    {
        global $config;
        $max_missing = 50;
        $min_maf = 5;
        require_once $config['root_dir'].'downloads/marker_filter.php';
        if (isset($_GET['mm'])) {
            $max_missing = $_GET['mm'];
        }
        if (isset($_GET['mmaf'])) {
            $min_maf = $_GET['mmaf'];
        }
        if (isset($_SESSION['geno_exps']) || isset($_SESSION['selected_lines'])) {
            $unique_str = chr(rand(65, 90)) .chr(rand(65, 90)) .chr(rand(65, 90)) .chr(rand(65, 90));
            $filename = "download_" . $unique_str;
            mkdir("/tmp/tht/$filename");
            $filename = "selection_paramaters.txt";
            $h = fopen("/tmp/tht/download_$unique_str/$filename", "w");
            fwrite($h, "Minimum MAF = $min_maf\n");
            fwrite($h, "Maximum Missing = $max_missing\n");
            fclose($h);
            $filename = "genotype.vcf";
            $h = fopen("/tmp/tht/download_$unique_str/$filename", "w");
        }
        $filename = "/tmp/tht/download_$unique_str";
        if (isset($_SESSION['geno_exps'])) {
            $geno_exp = $_SESSION['geno_exps'];
            $geno_exp = $geno_exp[0];
            $chr = "1AL";
            typeVcfExpMarkersDownload($geno_exp, $chr, $min_maf, $max_missing, $h);
            fclose($h);
            ?>
            <input type="button" value="Download Zip file of results" onclick="javascript:window.open('<?php echo "$filename"; ?>');" />
            <?php
        } elseif (isset($_SESSION['selected_lines'])) {
            $lines = $_SESSION['selected_lines'];
            typeVcfMarkersDownload($lines, $chr, $min_maf, $max_missing, $h);
            fclose($h);
            ?>
            <input type="button" value="Download Zip file of results" onclick="javascript:window.open('<?php echo "$filename"; ?>');" />
            <?php
        } else {
            echo "Error: Please select a set of lines or genotype experiment\n";
        }
    }
}
