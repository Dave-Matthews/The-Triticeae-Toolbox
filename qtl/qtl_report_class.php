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
            case 'downloadQTL':
                $this->downloadQTL();
                break;
            case 'displayQTL':
                $this->displayQTL();
                break;
            case 'sort':
                $this->displaySort();
                break;
            case 'search':
                $this->displaySearch();
                break;
            case 'downloadDetailQTL':
                $this->downloadDetailQTL();
                break;
            case 'detail':
                $this->displayDetail();
                break;
            case 'refreshtitle':
                $this->refreshTitle();
                break;
            default:
                $this->type1Select();
                break;
        }
    }

    private function type1Select()
    {
        global $config;
        include $config['root_dir'].'theme/admin_header2.php';
        ?>
        <table>
        </table>
        <div id="title">
        <?php
        $this->refreshTitle();
        ?>
        </div>
        <div id="step1" style="float: left; margin-bottom: 1.5em;">
        <script type="text/javascript" src="qtl/menu08.js"></script><br>
        <?php
        if (isset($_SESSION['selected_traits']) && isset($_SESSION['selected_trials'])) {
            ?>
            <script type="text/javascript">
            if ( window.addEventListener ) {
                window.addEventListener( "load", display_qtl(), false );
            } else if ( window.onload ) {
                window.onload = "display_qtl()";
            }
            </script>
            <?php
        } else {
            $this->step1Phenotype();
        }
        ?>
        </div>
        <div id="step2" style="float: left; margin-bottom: 1.5em;"></div>
        <div id="setp2b" style="clear: both; margin-bottom: 1.5em;">
        <img alt="spinner" id="spinner" src="images/ajax-loader.gif" style="display:none;" /></div>
        <div id="step3" style="clear: both; margin-bottom: 1.5em;"></div>
        <div id="step4" style="float: left; margin-bottom: 1.5em;"></div>
        </div>
        <?php
        include $config['root_dir'].'theme/footer.php';
    }


    private function refreshTitle()
    {
        global $mysqli;
        global $config;
        ?>
        <h2>QTL Report</h2>
        This analysis can be used to identify quantitative trait locus (QTL) by displaying associations between
        markers and traits for trials within the T3 database.
        If <a href='<?php echo $config['base_url']; ?>/phenotype/phenotype_selection.php'>traits and trials</a>
        are selected then only results for the selected trials are shown otherwise results are shown for all
        trials.
        <a href='<?php echo $config['base_url']; ?>/qtl/zbrowse.html'>ZBrowse instructions</a>
        <br><br>
        <b>Analysis Methods:</b> The analysis includes genotype and phenotype trials where there were more than 50
        germplasm lines in common. The platforms include Infinium 9K, Infinium 90K, and GBS restriction site.<br>
        1. GWAS is done on each phenotype trial, no fixed effects.<br>
        2. GWAS is done on each phenotype experiment, which is a set of related phenotype trials 
        (different location and same year or same location different year).
        Principle Components that accounted for more than 5% of the relationship matrix variance were included as fixed effects in the analysis.
        Each phenotype trial (if more than one) was included as a fixed effect.<br>
        <!---3. GWAS is done on each phenotype trial, no fixed effects. The genotype data is imputed with 1.2M SNP HapMap panel.
        Beagle version 4.0 was used for phasing and imputation.<br><br>-->
        <b>GWAS:</b> The analysis use rrBLUP GWAS package (Endleman, Jeffery, "Ridge Regression and Other Kernels for Genomic Selection with R package rrBLUP", The Plant Genome Vol 4 no. 3).
        The settings are: MAF > 0.05, P3D = TRUE (equivalent to EMMAX).
        The q-value is an estimate of significance given p-values from multiple comparisons using a false discovery rate of 0.05.
        To view the p-value and q-value for each trial, select the trial count link.<br><br>
        <?php
        if (isset($_SESSION['selected_traits'])) {
            $ntraits=count($_SESSION['selected_traits']);
            echo "<table>";
            echo "<tr><th>Currently selected traits</th><td><th>Currently selected trials</th>";
            print "<tr><td><select name=\"deselLines[]\" multiple=\"multiple\" onchange=\"javascript: remove_phenotype_items(this.options)\">";
            $phenotype_ary = $_SESSION['selected_traits'];
            foreach ($phenotype_ary as $uid) {
                $result=mysqli_query($mysqli, "select phenotypes_name from phenotypes where phenotype_uid=$uid") or die("invalid line uid\n");
                while ($row=mysqli_fetch_assoc($result)) {
                    $selval=$row['phenotypes_name'];
                    print "<option value=\"$uid\" >$selval</option>\n";
                }
            }
            print "</select>";
            echo "<td><td><select name=\"deseLines[]\" multiple=\"multiple\" onchange=\"javascript: remove_trial_items(this.options)\">";
            if (isset($_SESSION['selected_trials'])) {
                $trials_ary = $_SESSION['selected_trials'];
                foreach ($trials_ary as $uid) {
                    $result=mysqli_query($mysqli, "select trial_code from experiments where experiment_uid=$uid") or die("invalid line uid\n");
                    while ($row=mysqli_fetch_assoc($result)) {
                        $selval=$row['trial_code'];
                        print "<option value=\"$uid\" >$selval</option>\n";
                    }
                }
            }
            print "</select>";
            ?>
            <tr><td><input type="button" value="Deselect highlighted traits" onclick="javascript:phenotype_deselect();" /></td><td><td>
            <input type="button" value="Deselect highlighted trials" onclick="javascript:trials_deselect();" />
            </table>
            <?php
        }
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
            <select name="phenotype_categories" id="pheno_cat" multiple="multiple" style="height: 12em;" onchange="javascript: update_phenotype_categories(this.options)">
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
                <select id="pheno_itm" name="phenotype_items" multiple="multiple" style="height: 12em;" onClick="javascript: update_phenotype_items(this.options)">
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

    private function displaySearch()
    {
        global $mysqli;
        $database = "qtl_raw";
        $muid = $_GET['marker'];

        $sql = "select experiment_uid, trial_code from experiments";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
        while ($row = mysqli_fetch_array($res)) {
            $uid = $row[0];
            $trial_code = $row[1];
            $trial_list[$uid] = $trial_code;
        }

        echo "<table><tr><td>marker<td>chrom<td>pos<td>z-score<td>q-value<td>p-value<td>phenotype trial<td>genotype trial<td>plot";
        $sql = "select genotype_exp, phenotype_exp, gwas from $database";
        $res = mysqli_query($mysqli, $sql, MYSQLI_USE_RESULT) or die(mysqli_error($mysqli) . "<br>$sql");
        while ($row = mysqli_fetch_array($res)) {
            $gexp = $row[0];
            $pexp = $row[1];
            $gwas = json_decode($row[2]);
            foreach ($gwas as $val) {
                $marker = $val[0];
                $chrom = $val[1];
                $pos = $val[2];
                $gene = $annot_list1[$marker];
                if ($marker == $muid) {
                    $zvalue = number_format($val[3], 3);
                    $qvalue = number_format($val[4], 3);
                    $pvalue = number_format($val[5], 5);
                    $location = "$chrom $pos";
                    $link1 = "/jbrowse/?data=wheat&loc=$chrom:$pos";
                    if ($database == "qtl_imputed") {
                        $link2 = "<a target=\"_new\" href=\"$target_url" . $chrom . "THTdownload_gwa1_" . $gexp . "_" . $pexp . "_" . $puid . ".png\">Manhattan</a>";
                        $link3 = "<a target=\"_new\" href=\"$target_url" . $chrom . "THTdownload_gwa3_" . $gexp . "_" . $pexp . "_" . $puid . ".png\">Q-Q</a>";
                    } else {
                        $link2 = "<a target=\"_new\" href=\"$target_url" . "THTdownload_gwa1_" . $gexp . "_" . $pexp . "_" . $puid . ".png\">Manhattan</a>";
                        $link3 = "<a target=\"_new\" href=\"$target_url" . "THTdownload_gwa3_" . $gexp . "_" . $pexp . "_" . $puid . ".png\">Q-Q</a>";
                    }
                    if ($_GET['method'] == "set") {
                        $trial = $exp_list[$pexp];
                    } else {
                        $trial = "$trial_list[$pexp]";
                    }
                    echo "<tr><td>$marker<td>$chrom<td>$location<td>$zvalue<td>$qvalue<td>$pvalue<td>$trial<td>$trial_list[$gexp]<td>$link2 $link3\n";
                }
            }
        }
        mysqli_free_result($res);
        echo "</table>";
    }

    private function displayDetail()
    {
        global $mysqli;
        global $config;
        if (isset($_SESSION['selected_traits'])) {
            $phenotype_ary = $_SESSION['selected_traits'];
            $puid = $phenotype_ary[0];
        } elseif (isset($_GET['pi'])) {
            $puid = $_GET['pi'];
        } else {
            die("Error: no phenotypes selected\n");
        }
        if (isset($_GET['method'])) {
            if ($_GET['method'] == 'set') {
                $database = "qtl_set";
                $select_set = "checked";
                $select_sig = "";
                $select_imput = "";
                $target_base = $config['root_dir'] . "raw/gwas/set/";
                $target_url = $config['base_url'] . "raw/gwas/set/";
            } elseif ($_GET['method'] == 'imput') {
                $database = 'qtl_imputed';
                $select_set = "";
                $select_sig = "";
                $select_imput = "checked";
                $target_base = $config['root_dir'] . "raw/gwas/imput/";
                $target_url = $config['base_url'] . "raw/gwas/imput/";
            } else {
                $database = "qtl_raw";
                $select_set = "";
                $select_sig = "checked";
                $select_imput = "";
                $target_base = $config['root_dir'] . "raw/gwas/single/";
                $target_url = $config['base_url'] . "raw/gwas/single/";
            }
        } else {
            $database = "qtl_raw";
            $select_set = "";
            $select_sig = "checked";
            $select_imput = "";
            $target_base = $config['root_dir'] . "raw/gwas/single/";
            $target_url = $config['base_url'] . "raw/gwas/single/";
        }

        $sql = "select experiment_uid, trial_code from experiments";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
        while ($row = mysqli_fetch_array($res)) {
            $uid = $row[0];
            $trial_code = $row[1];
            $trial_list[$uid] = $trial_code;
        }
        $sql = "select experiment_set_uid, experiment_set_name from experiment_set";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
        while ($row = mysqli_fetch_array($res)) {
            $uid = $row[0];
            $set_name = $row[1];
            $exp_list[$uid] = $set_name;
        }

        $sql = "select experiments.experiment_uid, platform_name from experiments, genotype_experiment_info, platform
            where experiments.experiment_uid = genotype_experiment_info.experiment_uid
            and genotype_experiment_info.platform_uid = platform.platform_uid";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
        while ($row = mysqli_fetch_array($res)) {
            $uid = $row[0];
            $platform = $row[1];
            $platform_list[$uid] = $platform;
        }

        $muid = "none";
        $guid = "none";
        if (isset($_GET['uid'])) {
            $muid = $_GET['uid'];
            echo "<h2>QTL results for marker = $muid</h2>\n";
            if (isset($annot_list1[$marker_name])) {
                $gene = $annot_list1[$marker_name];
                echo "gene = $gene<br>\n";
            }
        } elseif (isset($_GET['gene'])) {
            $guid = $_GET['gene'];
            echo "<h2>QTL results for gene = $guid</h2>\n";
        }

        $sql = "select marker_name, gene, description from qtl_annotation";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
        while ($row = mysqli_fetch_array($res)) {
            $marker = $row[0];
            $gene = $row[1];
            $desc = $row[2];
            $annot_list1[$marker] = $gene;
            $annot_list2[$marker] = $desc;
        }

        $sql = "select experiment_uid, number_entries from phenotype_experiment_info";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
        while ($row = mysqli_fetch_array($res)) {
            $pheno_exp = $row[0];
            $count = $row[1];
            $linesInExp[$pheno_exp] = $row[1];
            //echo "$geno_exp $count<br>\n";
        }

        echo "<table><tr><td>marker<td>chrom<td>pos<td>z-score<td>q-value<td>p-value<td>phenotype trial (lines)<td>genotype trial<td>plot";
        $sql = "select genotype_exp, phenotype_exp, gwas from $database where phenotype_uid IN ($puid)";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
        while ($row = mysqli_fetch_array($res)) {
            $gexp = $row[0];
            $pexp = $row[1];
            $gwas = json_decode($row[2]);
            foreach ($gwas as $val) {
                $marker = $val[0];
                $chrom = $val[1];
                $pos = $val[2];
                $gene = $annot_list1[$marker];
                if (($marker == $muid) || ($gene == $guid)) {
                    $zvalue = number_format($val[3], 3);
                    $qvalue = number_format($val[4], 3);
                    $pvalue = number_format($val[5], 5);
                    $location = "$chrom $pos";
                    $link1 = "/jbrowse/?data=wheat&loc=$chrom:$pos";
                    if ($database == "qtl_imputed") {
                        $link2 = "<a target=\"_new\" href=\"$target_url" . $chrom . "THTdownload_gwa1_" . $gexp . "_" . $pexp . "_" . $puid . ".png\">Manhattan</a>";
                        $link3 = "<a target=\"_new\" href=\"$target_url" . $chrom . "THTdownload_gwa3_" . $gexp . "_" . $pexp . "_" . $puid . ".png\">Q-Q</a>";
                    } else {
                        $link2 = "<a target=\"_new\" href=\"$target_url" . "THTdownload_gwa1_" . $gexp . "_" . $pexp . "_" . $puid . ".png\">Manhattan</a>";
                        $link3 = "<a target=\"_new\" href=\"$target_url" . "THTdownload_gwa3_" . $gexp . "_" . $pexp . "_" . $puid . ".png\">Q-Q</a>";
                    }
                    if ($_GET['method'] == "set") {
                        $trial = $exp_list[$pexp];
                    } else {
                        $trial = "$trial_list[$pexp] ($linesInExp[$pexp])";
                    }
                    echo "<tr><td>$marker<td>$chrom<td>$location<td>$zvalue<td>$qvalue<td>$pvalue<td>$trial<td>$trial_list[$gexp]<td>$link2 $link3\n";
                }
            }
        }
        echo "</table>";
    }

    private function downloadQTL()
    {
        global $mysqli;
        if (isset($_GET['pi'])) {
            $puid_list = explode(",", $_GET['pi']);
        }
        if (isset($_GET['method'])) {
            if ($_GET['method'] == 'set') {
                $database = "qtl_set";
            } elseif ($_GET['method'] == 'imput') {
                $database = 'qtl_imputed';
            } else {
                $database = "qtl_raw";
            }
        } else {
            $database = "qtl_raw";
        }

        $sql = "select marker_name, gene, description from qtl_annotation";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
        while ($row = mysqli_fetch_array($res)) {
            $marker = $row[0];
            $gene = $row[1];
            $desc = $row[2];
            $annot_list1[$marker] = $gene;
            $annot_list2[$marker] = $desc;
        }

        header('Content-type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="qtl_meta.csv"');

        $sql = "select experiment_uid, number_entries from phenotype_experiment_info";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
        while ($row = mysqli_fetch_array($res)) {
            $pheno_exp = $row[0];
            $count = $row[1];
            $linesInExp[$pheno_exp] = $row[1];
            //echo "$geno_exp $count<br>\n";
        }

        foreach ($puid_list as $puid) {
            $sql = "select phenotype_exp, gwas from $database where phenotype_uid = ?";
            if ($stmt = mysqli_prepare($mysqli, $sql)) {
                mysqli_stmt_bind_param($stmt, "i", $puid);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $pheno_exp, $tmp);
                while (mysqli_stmt_fetch($stmt)) {
                    $gwas = json_decode($tmp);
                    foreach ($gwas as $val) {
                        $marker_name = $val[0];
                        $zsum[$marker_name] += $val[3] * sqrt($linesInExp[$pheno_exp]);
                        $ztot[$marker_name]++;
                        $wghtSum[$marker_name] += $linesInExp[$pheno_exp];
                    }
                }
                mysqli_stmt_close($stmt);
            }
            foreach ($zsum as $marker_name => $val) {
                $zmeta[$marker_name] = $val/(sqrt($wghtSum[$marker_name]));
            }
        }

        echo "\"trait\",\"marker\",\"chromosome\",position,\"gene\",\"feature\",z-score\n";
        foreach ($puid_list as $puid) {
            $sql = "select phenotypes_name from phenotypes where phenotype_uid = ?";
            if ($stmt = mysqli_prepare($mysqli, $sql)) {
                mysqli_stmt_bind_param($stmt, "i", $puid);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $desc);
                mysqli_stmt_fetch($stmt);
                mysqli_stmt_close($stmt);
            }
            $sql = "select gwas from $database where phenotype_uid = ?";
            if ($stmt = mysqli_prepare($mysqli, $sql)) {
                mysqli_stmt_bind_param($stmt, "i", $puid);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $tmp);
                while (mysqli_stmt_fetch($stmt)) {
                    $gwas = json_decode($tmp);
                    foreach ($gwas as $val) {
                        $marker = $val[0];
                        $chrom = $val[1];
                        $pos = $val[2];
                        $zvalue = $val[3];
                        $qvalue = $val[4];
                        $pvalue = $val[5];
                        if (preg_match("/scaff/", $chrom)) {
                            $chrom = "UNK";
                        } elseif (preg_match("/v44/", $chrom)) {
                            $chrom = "UNK";
                        }
                        if ($pvalue < 0.05) {
                            if (isset($annot_list1[$marker])) {
                                $gene = $annot_list1[$marker];
                                $feature = $annot_list2[$marker];
                            } else {
                                $gene = "";
                                $feature = "";
                            }
                            if (empty($pos)) {
                                $pos = 0;
                                $feature = "unknown location";
                            }
                            if (!isset($unique[$marker])) {
                                $unique[$marker] = 1;
                                $output_index[] = $zmeta[$marker];
                                $output_list[] = "\"$desc\",\"$marker\",\"$chrom\",$pos,\"$gene\",\"$feature\",$zmeta[$marker]";
                            }
                        }
                    }
                }
                mysqli_stmt_close($stmt);
            }
        }
        arsort($output_index);
        $count = 1;
        foreach ($output_index as $key => $val) {
            if ($count > 2500) {
                break;
            } else {
                $count++;
                echo "$output_list[$key]\n";
            }
        }
    }

    private function downloadDetailQTL()
    {
        global $mysqli;
        if (isset($_GET['pi'])) {
            $puid_list = explode(",", $_GET['pi']);
        }
        if (isset($_GET['method'])) {
            if ($_GET['method'] == 'set') {
                $database = "qtl_set";
            } elseif ($_GET['method'] == 'imput') {
                $database = 'qtl_imputed';
            } else {
                $database = "qtl_raw";
            }
        } else {
            $database = "qtl_raw";
        }

        $sql = "select marker_name, gene, description from qtl_annotation";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
        while ($row = mysqli_fetch_array($res)) {
            $marker = $row[0];
            $gene = $row[1];
            $desc = $row[2];
            $annot_list1[$marker] = $gene;
            $annot_list2[$marker] = $desc;
        }

        $sql = "select experiment_uid, trial_code from experiments";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
        while ($row = mysqli_fetch_array($res)) {
            $uid = $row[0];
            $trial_code = $row[1];
            $trial_list[$uid] = $trial_code;
        }

        header('Content-type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="qtl_detail.csv"');

        echo "\"trait\",\"marker\",\"chromosome\",position,gene,z-score,q-value,p-value,\"phenotype/genotype trial\"\n";
        foreach ($puid_list as $puid) {
            $sql = "select phenotypes_name from phenotypes where phenotype_uid = ?";
            if ($stmt = mysqli_prepare($mysqli, $sql)) {
                mysqli_stmt_bind_param($stmt, "i", $puid);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $name);
                mysqli_stmt_fetch($stmt);
                mysqli_stmt_close($stmt);
            }
            $sql = "select genotype_exp, phenotype_exp, gwas from $database where phenotype_uid = ?";
            if ($stmt = mysqli_prepare($mysqli, $sql)) {
                mysqli_stmt_bind_param($stmt, "i", $puid);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $gexp, $pexp, $tmp);
                while (mysqli_stmt_fetch($stmt)) {
                    $gwas = json_decode($tmp);
                    foreach ($gwas as $val) {
                        $marker = $val[0];
                        $chrom = $val[1];
                        $pos = $val[2];
                        $zvalue = $val[3];
                        $qvalue = $val[4];
                        $pvalue = $val[5];
                        if (preg_match("/scaff/", $chrom)) {
                            $chrom = "UNK";
                        } elseif (preg_match("/v44/", $chrom)) {
                            $chrom = "UNK";
                        }
                        if (isset($annot_list1[$marker])) {
                            $gene = $annot_list1[$marker];
                            $feature = $annot_list2[$marker];
                        } else {
                            $gene = "";
                            $feature = "";
                        }
                        if (empty($pos)) {
                            $pos = 0;
                        }
                        $output_index[] = $qvalue;
                        $output_list[] =  "\"$name\",\"$marker\",\"$chrom\",$pos,$gene,$zvalue,$qvalue,$pvalue,\"$trial_list[$gexp] $trial_list[$pexp]\"";
                    }
                }
                mysqli_stmt_close($stmt);
            }
        }
        asort($output_index);
        $count = 1;
        foreach ($output_index as $key => $val) {
            if ($count > 2500) {
                break;
            } else {
                $count++;
                echo "$output_list[$key]\n";
            }
        }
    }

 
    private function displayQTL()
    {
        global $mysqli;
        // get species
        if (preg_match("/([A-Za-z]+)\/[^\/]+\/[^\/]+$/", $_SERVER['PHP_SELF'], $match)) {
            $species = $match[1];
        } else {
            $species = "";
        }
        if (isset($_SESSION['selected_traits'])) {
            $phenotype_ary = $_SESSION['selected_traits'];
            $puid = $phenotype_ary[0];
        } elseif (isset($_GET['pi'])) {
            $puid = $_GET['pi'];
        } else {
            die("Error: no phenotypes selected\n");
        }
        if (isset($_SESSION['selected_trials'])) {
            $trial_ary = $_SESSION['selected_trials'];
            $trial_str = implode(",", $trial_ary);
        }
        if (isset($_GET['sortby'])) {
            $tmp = $_GET['sortby'];
            if ($tmp == "posit") {
                $select_posit = "checked";
                $select_score = "";
            } elseif ($tmp == "score") {
                $select_posit = "";
                $select_score = "checked";
            } else {
            }
        } else {
            $select_posit = "";
            $select_score = "checked";
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
        if (isset($_GET['method'])) {
            if ($_GET['method'] == 'set') {
                $database = "qtl_set";
                $select_set = "checked";
                $select_sig = "";
                $select_imput = "";
            } elseif ($_GET['method'] == 'imput') {
                $database = 'qtl_imputed';
                $select_set = "";
                $select_sig = "";
                $select_imput = "checked";
            } else {
                $database = "qtl_raw";
                $select_set = "";
                $select_sig = "checked";
                $select_imput = "";
            }
        } else {
            $database = "qtl_raw";
            $select_set = "";
            $select_sig = "checked";
            $select_imput = "";
        }

        echo "<table><tr><td>Analysis Method<td>Group by<td>Sort by";
        echo "<tr><td>";
        echo "<input type=\"radio\" name=\"meth\" id=\"meth\" onclick=\"selectDb('single')\" $select_sig> phenotype trial<br>";
        echo "<input type=\"radio\" name=\"meth\" id=\"meth\" onclick=\"selectDb('set')\" $select_set> phenotype experiment (set of trials)<br>";
        echo "<input type=\"radio\" name=\"meth\" id=\"meth\" onclick=\"selectDb('imput')\" $select_imput> phenotype trial, genotype imputed";
        echo "<td>";
        echo "<input type=\"radio\" name=\"group\" id=\"group\" onclick=\"group('marker')\" $select_m> marker<br>";
        echo "<input type=\"radio\" name=\"group\" id=\"group\" onclick=\"group('gene')\" $select_g> gene<br>";
        echo "<td>";
        echo "<input type=\"radio\" name=\"sort\" id=\"sort\" onclick=\"sort('score')\" $select_score> score<br>";
        echo "<input type=\"radio\" name=\"sort\" id=\"sort\" onclick=\"sort('posit')\" $select_posit> position<br>";
        echo "</table><br>";

        $sql = "select marker_name, gene, description from qtl_annotation";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
        while ($row = mysqli_fetch_array($res)) {
            $marker = $row[0];
            $gene = $row[1];
            $desc = $row[2];
            $annot_list1[$marker] = $gene;
            $annot_list2[$marker] = $desc;
        }
        $sql = "select experiment_uid, number_entries from phenotype_experiment_info";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
        while ($row = mysqli_fetch_array($res)) {
            $pheno_exp = $row[0];
            $count = $row[1];
            $linesInExp[$pheno_exp] = $row[1];
            //echo "$geno_exp $count<br>\n";
        }

//get z-stat
//get count of significant qtls when grouping by marker_name
        if ($gb == "marker") {
            if (isset($trial_str)) {
                $sql = "select phenotype_exp, gwas from $database where phenotype_uid IN ($puid) and phenotype_exp IN ($trial_str)";
                echo "$sql\n";
            } else {
                $sql = "select phenotype_exp, gwas from $database  where phenotype_uid IN ($puid)";
            }
            $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
            while ($row = mysqli_fetch_array($res)) {
                $pheno_exp = $row[0];
                $gwas = json_decode($row[1]);
                if ($_GET['method'] == 'set') {
                    foreach ($gwas as $val) {
                        $marker_name = $val[0];
                        $zsum[$marker_name] += $val[3];
                        $wghtSum[$marker_name]++;
                    }
                } else {
                    foreach ($gwas as $val) {
                        $marker_name = $val[0];
                        $zsum[$marker_name] += $val[3] * $linesInExp[$pheno_exp];
                        $wghtSum[$marker_name] += ($linesInExp[$pheno_exp] * $linesInExp[$pheno_exp]);
                    }
                }
            }
            foreach ($zsum as $marker_name => $val) {
                if ($wghtSum[$marker_name] > 0) {
                    $zmeta[$marker_name] = $val/(sqrt($wghtSum[$marker_name]));
                } else {
                    $zmeta[$marker_name] = 0;
                }
            }
        } else {
//get count of significant qtls when groupinng by gene
            $sql = "select gwas from $database where phenotype_uid IN ($puid)";
            $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
            while ($row = mysqli_fetch_array($res)) {
                $gwas = json_decode($row[0]);
                foreach ($gwas as $val) {
                    $marker_name = $val[0];
                    if (isset($annot_list1[$marker_name])) {
                        $gene = $annot_list1[$marker_name];
                        $zsum[$gene] += $val[3] * $linesInExp[$pheno_exp];
                        $ztot[$gene]++;
                        $wghtSum[$gene] += $linesInExp[$pheno_exp];
                    }
                }
            }
            foreach ($zsum as $gene => $val) {
                $zmeta[$gene] = $zsum[$gene]/$wghtSum[$gene];
            }
        }

        $count = 0;
        $sql = "select gwas from $database where phenotype_uid IN ($puid)";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
        while ($row = mysqli_fetch_array($res)) {
            $gwas = json_decode($row[0]);
            foreach ($gwas as $val) {
                $marker = $val[0];
                $chrom = $val[1];
                $pos = $val[2];
                $zvalue = $val[3];
                $qvalue = $val[4];
                $pvalue = $val[5];
                if ($pvalue < 0.05) {
                    $count++;
                    if (isset($annot_list1[$marker])) {
                        $gene = $annot_list1[$marker];
                        $desc2 = $annot_list2[$marker];
                        $wheatexp = "<a target=\"_new\" href=\"www.wheat-expression.com/genes";
                        $wheatexp = "<a target=\"_new\" href=\"http://wheat.pw.usda.gov/WheatExp/graph_and_table.php?seq_id=$gene\">WheatExp</a>";
                    } else {
                        $gene = "";
                        $desc2 = "";
                        $wheatexp = "";
                    }
                    if ($chrom == "UNK") {
                        $chrom_num = 4;
                        $chrom_arm = "";
                    } else {
                        $chrom_num = substr($chrom, 0, 1);
                        $chrom_arm = substr($chrom, 1, 1);
                    }
                    if (isset($_GET['sortby'])) {
                        $sort_type = $_GET['sortby'];
                        if ($sort_type == "posit") {
                            if ($chrom_arm == "A") {
                                $sort_index = (($chrom_num * 10) + 1) * 10000000 + $pos;
                            } elseif ($chrom_arm == "B") {
                                $sort_index = (($chrom_num * 10) + 2) * 10000000 + $pos;
                            } elseif ($chrom_arm == "D") {
                                $sort_index = (($chrom_num * 10) + 3) * 10000000 + $pos;
                            } else {
                                $sort_index = (($chrom_num * 10) + 4) * 10000000 + $pos;
                            }
                        } else {
                            $sort_type = "score";
                            if ($gb == "marker") {
                                $sort_index = $zmeta[$marker];
                            } else {
                                $sort_index = $zmeta[$gene];
                            }
                        }
                    } else {
                        $sort_type = "score";
                        if ($gb == "marker") {
                            $sort_index = $zmeta[$marker];
                        } else {
                            $sort_index = $zmeta[$gene];
                        }
                    }
                    $jbrowse = "<a target=\"_new\" href=\"/jbrowse/?data=$species&loc=$chrom:$pos\">JBrowse</a>";
                    if ($gb == "marker") {
                        if (isset($marker_list[$marker])) {
                        } else {
                            $marker_list[$marker] = 1;
                            $zvalue = number_format($zmeta[$marker], 3);
                            $output_index[] = $sort_index;
                            $output_list[] = "<tr><td>$marker<td>$chrom<td>$pos<td>$gene<td>$desc2<td><a id=\"detail\" onclick=\"detailM('$marker')\">$zvalue</a><td>$ztot[$marker]<td>$jbrowse<td>$wheatexp";
                        }
                    } else {
                        if ($gene == "") {
                        } elseif (isset($gene_list[$gene])) {
                        } else {
                            $gene_list[$gene] = 1;
                            $zvalue = number_format($zmeta[$gene], 3);
                            $output_index[] = $sort_index;
                            $output_list[] = "<tr><td>$gene<td>$chrom<td>$desc2<td><a id=\"detail\" onclick=\"detailG('$gene')\">$zvalue</a><td>$ztot[$gene]<td>$jbrowse";
                        }
                    }
                }
            }
        }
        if ($count > 0) {
            echo "<a href=\"qtl/qtl_report.php?function=downloadQTL&pi=" . $puid . "&method=" . $_GET['method'] . "\">Download meta data</a>, ";
            echo "<a href=\"qtl/qtl_report.php?function=downloadDetailQTL&pi=" . $puid . "&method=" . $_GET['method'] . "\">Download detail data</a><br>";
            $count_display = 0;
            if ($gb == "marker") {
                //echo "<table><tr><td>marker<td><a id=\"sort2\" onclick=\"sort('pos')\">location</a>";
                echo "<table><tr><td>marker<td>chromosome<td>location";
                echo "<td>gene<td>feature<td nowrap>Z-score<td>Trial Count<td>Genome Browser<td>Expression";
            } else {
                //echo "<table><tr><td>gene<td><a id=\"sort2\" onclick=\"sort('pos')\">location</a>";
                echo "<table><tr><td>gene<td>location";
                echo "<td>feature<td>Z-score<td>Count<td>Geneome Browser";
            }

            if ($sort_type == "score") {
                arsort($output_index);
                foreach ($output_index as $key => $val) {
                    $count_display++;
                    echo "$output_list[$key]";
                    if ($count_display > 100) {
                        break;
                    }
                }
            } else {
                asort($output_index);
                foreach ($output_index as $key => $val) {
                    echo "$output_list[$key]";
                }
            }
            echo "</table>";
        } else {
            echo "no significant QTLs found<br>$sql\n";
        }
    }
}