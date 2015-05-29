<?php
require 'config.php';
require_once $config['root_dir'].'includes/bootstrap.inc';

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
    This tool uses <a href="http://faculty.washington.edu/browning/beagle/beagle.html">Beagle version 4.0</a> to impute genotype data using these inputs<br><br>
    <?php
    global $mysqli;
    if (isset($_SESSION['geno_exps'])) {
        $geno_exps = $_SESSION['geno_exps'];
        $geno_exps = $geno_exps[0];
        $sql = "select trial_code from experiments where experiment_uid = $geno_exps";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . $sql);
        if ($row = mysqli_fetch_array($res)) {
            $trial_code = $row[0];
            echo "Reference experiment: $ref_genotype<br>\n";
            echo "Genotype experiment: $trial_code<br>\n";
            $sql = "select count(*) from allele_frequencies where experiment_uid = $geno_exps";
            $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . $sql);
            if ($row = mysqli_fetch_array($res)) {
                $count = $row[0];
                echo "Genotype experiment count: $count<br>\n";
            } else {
                echo "Genotype experiment count: none<br>\n";
            }
            $sql = "select count(distinct(marker_report_reference.marker1_uid)), count(allele_frequencies.marker_uid) from marker_report_reference, allele_frequencies
            where marker_report_reference.marker1_uid=allele_frequencies.marker_uid
            and experiment_uid = $geno_exps";
            $res = mysqli_query($mysqli, $sql);
            echo "Match to contigs: ";
            if ($res) {
                if ($row = mysqli_fetch_array($res)) {
                    $count = $row[0];
                    echo "$count<br>\n";
                } else {
                    echo "none<br>\n";
                }
            } else {
                echo "database table not found<br>\n";
            }
            ?>
            <input type="button" value="Create file" onclick="javascript:createVCF('');">
            <?php
        } else {
            echo "Experiment: unknown<br>\n";
        }
    } else {
        echo "Please select a genotype experiment\n";
    }
    echo "</div>";
    require_once $config['root_dir'].'theme/footer.php';
    }

    public function createVCF()
    {
    global $config;
    require_once $config['root_dir'].'downloads/marker_filter.php';
    $max_missing = $_GET['mm'];
    $min_maf = $_GET['mmaf'];
    if (isset($_SESSION['geno_exps'])) {
        $geno_exp = $_SESSION['geno_exps'];
        $geno_exp = $geno_exp[0];
        $unique_str = chr(rand(65, 90)) .chr(rand(65, 90)) .chr(rand(65, 90)) .chr(rand(65, 90));
        $filename = "download_" . $unique_str;
        mkdir("/tmp/tht/$filename");
        $h = fopen("/tmp/tht/download_$unique_str/$filename", "w");
        typeVcfMarkersDownload($geno_exp, $min_maf, $max_missing, $h);
    } else {
        echo "Error: Please select genotype experiment\n";
    }
    }
}
