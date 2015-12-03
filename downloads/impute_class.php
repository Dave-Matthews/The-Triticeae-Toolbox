<?php
/** Use a PHP class to implement the "Imputation" feature
 *
 * author Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/downloads/downloads.php
 **/

namespace T3download;

class Impute
{

    public function __construct($function = null)
    {
        switch ($function) {
            case 'selectRefChr':
                $this->selectRefChr();
                break;
            case 'selectTarChr':
                $this->selectTarChr();
                break;
            case 'selectTarExec':
                selectTarExec();
                break;
            case 'createVCF':
                createVcfBeagle();
                break;
            case 'runBeagle':
                runBeagle();
                break;
            case 'filterVCF':
                filterVCF();
                break;
            default:
                $this->displayPage();
                break;
        }
    }

    private function displayPage()
    {
        global $config;
        include_once $config['root_dir'].'theme/normal_header.php';
        ?>
    <script type="text/javascript" src="downloads/impute03.js"></script>
    <h2>Download Imputed Genotype Data</h2>
    <b>Genotype Imputation Description</b><br>
    The reference genotype is <a href="display_genotype.php?trial_code=2014_HapMap_WEC">2014_HapMap_WEC</a>.
    The imputation has been done using <a target="_new" href="https://faculty.washington.edu/browning/beagle/beagle.03Mar15.pdf">Beagle 4.0</a>.<br>
    The output file contains an <a target="_new" href="http://www.ncbi.nlm.nih.gov/pmc/articles/PMC2668004">Allelic R-squared</a> (AR2)
    for each marker to give an estimate of the imputation accuracy.<br>
    The histogram shows the distribution of AR2 values. The frequency for AR2 = 0 is not shown to make the graph more readable.<br>
    The output file is in VCF format and can be filtered to remove markers with low confidence.<br>
    The genotype result file can be imported into <a target="_new" href="http://www.maizegenetics.net/#!tassel/">TASSEL</a>.
    To download the associated trait, select <a href="phenotype/phenotype_selection.php">Traits and Trials</a>
    and then <a href="downloads/downloads.php">Download</a>.<br><br>
    <!--b>Consensus Genotype data</b><br>
    1. Select a set of lines<br>
    2. Select a chromosome<br>
    3. Download Zip file of results<br><br>
    <td-->
    <b>Instruction for selecting data</b><br>
    1. Select a Genotype Experiment<br>
    2. Select a chromosome<br>
    3. Enter an AR2 value for filtering and Download the VCF file<br><br>
    <?php
    global $mysqli;
    global $ref_genotype;

    $sql = "select experiment_uid from experiments where trial_code = \"$ref_genotype\"";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . $sql);
    if ($row = mysqli_fetch_array($res)) {
        $ref_exp_uid = $row[0];
    } else {
        die("Error: could not find reference experiment\n");
    }
    $sql = "select count(*) from allele_bymarker_exp_101 where experiment_uid = $ref_exp_uid";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . $sql);
    if ($row = mysqli_fetch_array($res)) {
        $count_ref_markers = number_format($row[0]);
    } else {
        die("Error: could not find reference genotype data\n");
    }
    $sql = "select line_name_index from allele_bymarker_expidx where experiment_uid = $ref_exp_uid";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . $sql);
    if ($row = mysqli_fetch_array($res)) {
        $tmp = json_decode($row[0], true);
        $count_ref_lines = count($tmp);
    } else {
        die("Error: could not find reference genotype data\n");
    }
 
    $selected_map = "Chromosome Survey Sequence, 2014";

    if (isset($_SESSION['selected_lines'])) {
        $lines = $_SESSION['selected_lines'];
        $count_lines = count($lines);
    } else {
        $count_lines = 0;
    }
    if (isset($_SESSION['geno_exps'])) {
        $geno_exps = $_SESSION['geno_exps'];
        $geno_exps = $geno_exps[0];
        $sql = "select trial_code from experiments where experiment_uid = $geno_exps";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . $sql);
        if ($row = mysqli_fetch_array($res)) {
            $trial_code = $row[0];
            $sql = "select count(*) from allele_bymarker_exp_101 where experiment_uid = $geno_exps";
            $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . $sql);
            if ($row = mysqli_fetch_array($res)) {
                $count_tar_markers = number_format($row[0]);
            } else {
                die("Error: could not find target genotype data\n");
            }

            echo "<table>\n";
            echo "<tr><td>Reference genotype experiment:";
            echo "<td><a target=\"_new\" href=\"display_genotype.php?trial_code=$ref_genotype\">$ref_genotype</a>\n";
            echo "<tr><td>Markers:<td><div id=\"RefCount\">$count_ref_markers</div>";
            echo "<tr><td>Germplasm lines:<td>$count_ref_lines";
            echo "</table><br><table>";
            echo "<tr><td>Target Genotype experiment:<td>$trial_code\n";
            echo "<tr><td>Genome map:<td>$selected_map\n";
            echo "<tr><td>Markers:<td><div id=\"TarCount1\">$count_tar_markers</div>";
            echo "<tr><td>Germplasm lines:<td>$count_lines";
            echo "</table><br>";
            //first check if markers are asigned to reference
            $sql = "select marker_name from allele_bymarker_exp_101 where experiment_uid = $geno_exps";
            $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . $sql);
            if ($row = mysqli_fetch_array($res)) {
                $marker_name = $row[0];
                if (preg_match("/WCSS1/", $marker_name)) {
                    $gbs_marker = true;
                } else {
                    $gbs_marker = false;
                }
            }
            $count_m1 = 0;
            $count_m2 = 0;
            $sql = "select count(distinct(marker_report_reference.marker1_uid)), count(allele_frequencies.marker_uid) from marker_report_reference, allele_frequencies
            where marker_report_reference.marker1_uid=allele_frequencies.marker_uid
            and experiment_uid = $geno_exps";
            $res = mysqli_query($mysqli, $sql);
            if ($row = mysqli_fetch_array($res)) {
                $count_m2 = number_format($row[0]);
            }
            echo "<div id=\"TarCount2\"></div>";
        } else {
            echo "Experiment: unknown<br>\n";
        }
    } elseif (isset($_SESSION['selected_lines'])) {
        echo "<table>\n";
        echo "<tr><td>Physical map:<td>$selected_map\n";
        echo "<tr><td>Reference genotype experiment:<td>$ref_genotype\n";
        echo "<tr><td>Selected lines:<td>$count_lines\n";
        echo "</table>\n";
    } else {
        echo "<font color=red>Please select a <a href=\"downloads/select_genotype.php\">genotype experiment</a></font>\n";
    }

    $sql = "select mapset_uid from mapset where mapset_name = \"$selected_map\"";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    if ($row = mysqli_fetch_array($res)) {
        $mapset_uid = $row[0];
    } else {
        echo "Error: can not find reference map\n";
    }

    if (isset($_SESSION['geno_exps']) || isset($_SESSION['selected_lines'])) {
        ?><br><table><tr><td>Select chromosome:<td><select name=chr onclick="javascript: update_chr(this.value)">
        <?php
        $sql = "select distinct(mim.chromosome) from markers, markers_in_maps as mim, map
        where mim.marker_uid = markers.marker_uid
        AND mim.map_uid = map.map_uid
        AND map.mapset_uid = $mapset_uid
        order by mim.chromosome";
        $sql = "select distinct(substr(contig, 1, 3)) from marker_report_reference
        order by contig";
        $sql = "select distinct(chrom) from marker_report_reference order by chrom";
        $res = mysqli_query($mysqli, $sql) or die("Error: $sql\n");
        if ($res == true) {
            while ($row = mysqli_fetch_array($res)) {
                $chr = $row[0];
                echo "<option value=$chr>$chr</option>\n";
            }
        } else {
            echo "option value=none>none found</option>\n";
        }
        echo "</select>";
        ?>
        <!--button onclick=createVCF()>Run Beagle</button-->
        <tr><td>Filter imputed:<td><input type="text" id="filter" size="3" value="0.3">
        <td>remove markers with Allelic R-Squared(AR2) less than this value
        </table><br>
        <img alt="spinner" id="spinner" src="images/ajax-loader.gif" style="display:none;" />
        <?php
    }
    ?>
    <div id="runbeagle"></div>
    <div id="result1"></div>
    <div id="result2"></div>
    </div>
    <script type="text/javascript">
    if ( window.addEventListener ) {
        window.addEventListener( "load", display_chr(), false );
    }
    </script>
    <?php
    include_once $config['root_dir'].'theme/footer.php';
    }

    /** count of markers in reference **/
    public function selectRefChr()
    {
        global $mysqli;
        global $ref_genotype;
        $chrom = $_GET['chr'];
 
        $sql = "select experiment_uid from experiments where trial_code = \"$ref_genotype\"";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . $sql);
        if ($row = mysqli_fetch_array($res)) {
            $ref_exp_uid = $row[0];
        } else {
            die("Error: could not find reference experiment\n");
        }

        if (isset($_SESSION['geno_exps'])) {
            $geno_exps = $_SESSION['geno_exps'];
            $geno_exp = $geno_exps[0];
        } else {
            echo "Error: experiment not selected\n";
        }
        $sql = "select count(*) from allele_bymarker_exp_101
            where experiment_uid = $ref_exp_uid
            and chrom like \"$chrom%\"";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . $sql);
        if ($row = mysqli_fetch_array($res)) {
            $count = $row[0];
        }
        $_SESSION['ref_genotype_count'] = $count;
        echo "$count in $chrom";
    }

    /** cout of markers in target that match reference **/
    public function selectTarChr()
    {
        global $mysqli;
        global $config;
        global $target_dir;
        $count1 = 0;
        $chrom = $_GET['chr'];
        if (isset($_SESSION['geno_exps'])) {
            $geno_exps = $_SESSION['geno_exps'];
            $geno_exp = $geno_exps[0];
        } else {
            echo "Error: experiment not selected\n";
        }

        //look in target directory
        $sql = "select trial_code from experiments where experiment_uid = $geno_exp";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        if ($row = mysqli_fetch_array($res)) {
            $trial_code = $row[0];
            $target1 = $config['root_dir'] . "raw/genotype/imputed/" . $geno_exp;
            $target2 = $config['base_url'] . "raw/genotype/imputed/" . $geno_exp;
            
            $target_dir = $target1;
            if (!file_exists($target1)) {
                echo "creating $target directory\n";
                mkdir($target1);
            } else {
                echo "found $target directory\n";
                $imputed_list = scandir($target1);
            }
        } else {
            die("trial not found\n");
        }
        //get synonyms for T3 markers so they will match the referenece
        $sql = "select marker1_uid, marker1_name, contig
           from marker_report_reference, allele_frequencies
           where marker_report_reference.marker1_uid = allele_frequencies.marker_uid
           and experiment_uid = $geno_exp";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        while ($row = mysqli_fetch_array($res)) {
            $marker_uid = $row[0];
            $marker_name = $row[1];
            $contig = $row[2];
            $contig_list[$contig]= $marker_name;
        }

        $totalSort = 0;
        $sql = "select marker1_uid, chrom, scaffold
            from marker_report_reference, allele_frequencies
            where marker_report_reference.marker1_uid = allele_frequencies.marker_uid
            and allele_frequencies.experiment_uid = $geno_exp
            and scaffold is not NULL
            order by chrom";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        while ($row = mysqli_fetch_array($res)) {
            $marker_uid = $row[0];
            $chrom = $row[1];
            $scaffold = $row[2];
            $countTar[$chrom]++;
            $totalSort++;
        }
        echo "<table><tr><td>chromosome";
        foreach ($countTar as $chr => $cnt) {
            echo "<td>$chr";
        }

        echo "<td>Total";
        echo "<tr><td>markers matched to<br>Reference";
        foreach ($countTar as $i => $cnt) {
            echo "<td>$cnt";
        }
        echo "<td>$totalSort</table>";
    }
}
