<?php

namespace T3;

class Downloads
{
    public function __construct($function = null)
    {
        switch ($function) {
            case 'step2phenotype':
                $this->step2Phenotype();
                break;
            case 'displayQTL':
                $this->displayQTL();
                break;
            case 'sort':
                $this->displaySort();
                break;
            case 'detail':
                $this->detail();
                break;
            default:
                $this->type1Select();
                break;
        }
    }

    private function type1Select()
    {
        global $config;
        include $config['root_dir'].'theme/normal_header.php';
        ?>
        <table>
        </table>
        <div id="title">
        <?php
        $this->refreshTitle();
        ?>
        <div id="step1" style="float: left; margin-bottom: 1.5em;">
        <script type="text/javascript" src="qtl/menu02.js"></script><br>
        <?php
        $this->step1Phenotype();
        ?>
        </div>
        <div id="step2" style="float: left; margin-bottom: 1.5em;"></div>
        <div id="step3" style="clear: both; margin-bottom: 1.5em;"></div>
        <div id="step4" style="float: left; margin-bottom: 1.5em;"></div>
        </div></div>
        <?php
        include $config['root_dir'].'theme/footer.php';
    }


    private function refreshTitle()
    {
        global $mysqli;
        ?>
        <h2>QTL Report</h2>
        This analysis shows the significant associations between markers and traits for experiments within the T3 database. 
        This analysis use rrBLUP GWAS package (Endleman, Jeffery, "Ridge Regression and Other Kernels for Genomic Selection with R package rrBLUP", The Plant Genome Vol 4 no. 3).
        The settings are: MAF > 0.05, principal components = 0, P3D = TRUE (equivalent to EMMAX). 
        The q-value is an estimate of significance given p-values from multiple comparisons using a false discovery rate of 0.05.<br>
        When the same marker is significant in more than one trial the q-value shown is a simple average. To view the q-value for each trial, select the trial count link.<br>
        <?php
    }

    private function step1Phenotype()
    {
        global $mysqli;
        ?>
        <table id="phenotypeSelTab" class="tableclass1">
            <tr>
            <th>Category</th>
            </tr>
            <tr><td>
            <select name="phenotype_categories" multiple="multiple" style="height: 12em;" onchange="javascript: update_phenotype_categories(this.options)">
            <?php
            $sql = "SELECT phenotype_category_uid AS id, phenotype_category_name AS name from phenotype_category";
            $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
            while ($row = mysqli_fetch_assoc($res)) { ?>
                <option value="<?php echo $row['id'] ?>">
                <?php echo $row['name'] ?>
                </option>
                <?php
            }
            ?>
            </select>
            </td>
            </table>
            <?php
    }

    private function step2Phenotype()
    {
        global $mysqli;
        $phen_cat = $_GET['pc'];
        $lines_within = $_GET['lw'];
        if (isset($_SESSION['selected_lines'])) {
             $selectedlines= $_SESSION['selected_lines'];
             $selectedlines = implode(',', $selectedlines);
        }
                ?><br>
        <table id="phenotypeSelTab" class="tableclass1">
                <tr>
                        <th>Traits</th>
                </tr>
                <tr><td>
                <select id="traitsbx" name="phenotype_items" multiple="multiple" style="height: 12em;" onClick="javascript: update_phenotype_items(this.options)">
                <?php

                if ($lines_within == "yes") {
                    $sql = "SELECT DISTINCT phenotypes.phenotype_uid AS id, phenotypes_name AS name from phenotypes, phenotype_category, phenotype_data, line_records, tht_base
                    where phenotypes.phenotype_uid = phenotype_data.phenotype_uid
                    AND phenotypes.phenotype_category_uid = phenotype_category.phenotype_category_uid
                    AND phenotype_data.tht_base_uid = tht_base.tht_base_uid 
                    AND line_records.line_record_uid = tht_base.line_record_uid 
                    AND phenotype_category.phenotype_category_uid in ($phen_cat)
                    AND line_records.line_record_uid IN ($selectedlines)
                    ORDER BY name";
                } else {
                    $sql = "SELECT phenotype_uid AS id, phenotypes_name AS name from phenotypes, phenotype_category
                    where phenotypes.phenotype_category_uid = phenotype_category.phenotype_category_uid
                    AND phenotype_category.phenotype_category_uid in ($phen_cat)
                    ORDER BY name";
                }
                $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
                while ($row = mysqli_fetch_assoc($res)) { ?>
                    <option value="<?php echo $row['id'] ?>">
                    <?php echo $row['name'] ?>
                    </option>
                    <?php
                }
                ?>
                </select>
                </table>
                <?php
    }

    private function detail()
    {
        global $mysqli;
        $puid = $_GET['pi'];
        $muid = $_GET['uid'];
        $sql = "select experiment_uid, trial_code from experiments";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
        while ($row = mysqli_fetch_array($res)) {
            $uid = $row[0];
            $trial_code = $row[1];
            $trial_list[$uid] = $trial_code;
        }
        $sql = "select marker_name, chrom, scaffold, pos, qvalue, pvalue, phenotype_exp, genotype_exp, gene
                from qtl_report
                where phenotype_uid IN ($puid) and marker_uid = $muid";
        echo "<table><tr><td>marker<td>location<td>q-value<td>p-value<td>phenotype trial<td>genotype trial<td>gene";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
        while ($row = mysqli_fetch_array($res)) {
            $name = $row[0];
            $chrom = $row[1];
            $scaf = $row[2];
            $pos = $row[3];
            $qvalue = $row[4];
            $pvalue = $row[5];
            $pexp = $row[6];
            $gexp = $row[7];
            $gene = $row[8];
            if ($chrom != $scaf) {
                $location = $scaf;
                $link1 = "/jbrowse/?data=wheat&loc=UNK:$pos";
            } else {
                $location = "$chrom $pos";
                $link1 = "/jbrowse/?data=wheat&loc=$chrom:$pos";
            }
            echo "<tr><td>$name<td>$location<td>$qvalue<td>$pvalue<td>$trial_list[$pexp]<td>$trial_list[$gexp]<td>$gene\n";
        }
        echo "</table>";
    }
 
    private function displayQTL()
    {
        global $mysqli;
        $puid = $_GET['pi'];
        if (isset($_GET['sortby'])) {
            $tmp = $_GET['sortby'];
            if ($tmp == "pos") {
                $opt = "order by chrom, pos";
            } elseif ($tmp == "qvalue") {
                $opt = "order by qvalue";
            } else {
                $opt = "order by marker_name";
            }
        } else {
            $opt = "order by chrom, pos";
        }
        if (isset($_GET['group'])) {
            $gb = $_GET['group'];
            if ($gb == "marker") {
                $opt2 = "group by marker_name";
                $select_m = "checked";
                $select_g = "";
            } elseif ($gb == "gene") {
                $opt2 = "group by gene";
                $select_m = "";
                $select_g = "checked";
            } else {
                $opt2 = "group by marker_name";
                $select_m = "checked";
                $select_g = "";
            }
        } else {
            $gb = "marker";
            $opt = "group by marker_name";
            $select_m = "checked";
            $select_g = "";
        }

        $sql = "select description, TO_number from phenotypes where phenotype_uid = $puid";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
        if ($row = mysqli_fetch_array($res)) {
            $des = $row[0];
            $to = $row[1];
            echo "Trait description - $des\n";
            echo "<br>Trait ontology - $to\n";
            echo "<br><br>";
        }
        echo "Group by: ";
        echo "<input type=\"radio\" name=\"group\" id=\"group\" onclick=\"group('marker')\" $select_m>marker";
        echo "<input type=\"radio\" name=\"group\" id=\"group\" onclick=\"group('gene')\" $select_g>gene";

        $sql = "select marker_uid, marker_name, chrom, scaffold, pos, count(qvalue), AVG(qvalue), AVG(pvalue), gene
                from qtl_report where phenotype_uid IN ($puid) $opt2 $opt";
        echo "<table><tr>";
        if ($gb == "marker") {
            echo "<td>marker";
            echo "<td><a id=\"sort2\" onclick=\"sort('pos')\">location</a>";
            echo "<td><a id=\"sort3\" onclick=\"sort('qvalue')\">q-value</a>";
            echo "<td>trial count<td>gene<td>Genome Browser";
        } else {
            echo "<td>gene";
            echo "<td><a id=\"sort2\" onclick=\"sort('pos')\">location</a>";
            echo "<td><a id=\"sort3\" onclick=\"sort('qvalue')\">q-value</a>";
            echo "<td>trial count<td>gene<td>Genome Browser";
        }
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
        while ($row = mysqli_fetch_array($res)) {
            $uid = $row[0];
            $name = $row[1];
            $chrom = $row[2];
            $scaf = $row[3];
            $pos = $row[4];
            $count_exp = $row[5];
            $qvalue = $row[6];
            $pvalue = $row[7];
            $gene = $row[8];
            if ($chrom != $scaf) {
                $location = $scaf;
                $link = "/jbrowse/?data=wheat&loc=UNK:$pos";
            } else {
                $location = "$chrom $pos";
                $link = "/jbrowse/?data=wheat&loc=$chrom:$pos";
            }
            if ($gb == "marker") {
                echo "<tr><td><a href=\"view.php?table=markers&uid=$uid\">$name</a>";
                echo "<td>$location<td>$qvalue";
                echo "<td><a id=\"detail\" onclick=\"detail($uid)\">$count_exp</a><td>$gene";
                echo "<td><a target=\"_new\" href=\"$link\">JBrowse</a>\n";
            } else {
                echo "<tr><td>$gene<td>$location<td>$qvalue";
                echo "<td><a id=\"detail\" onclick=\"detail($uid)\">$count_exp</a><td>$gene";
                echo "<td><a target=\"_new\" href=\"$link\">JBrowse</a>\n";
            }
        }
        echo "</table>";
    }
}
