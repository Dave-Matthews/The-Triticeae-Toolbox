<?php
require 'config.php';
include $config['root_dir'].'includes/bootstrap.inc';
$pageTitle = "Variant Effects";
$mysqli = connecti();

include $config['root_dir'].'theme/admin_header2.php';
?>
<script type="text/javascript" src="genotyping/variations.js"></script>
<?php

if (!isset($varLink)) {
    echo "Error: Please define VEP in directory config.php file";
}

echo "<h2>Variant Effects</h2>\n";
echo "This page provides links to Sorting Intolerant From Tolerant (SIFT) and Variant Effect Predictor (VEP) to predict whether an amino aid substitution affects protein function.<br>";
echo "SIFT missense predictions for genomes: <a target=\"_new\" href=\"http://www.nature.com/nprot/journal/v11/n1/abs/nprot.2015.123.html\">Nature Protocols 2016; 11:1-9</a>. ";
echo "The Ensembl Variant Effect Predictor: Genome Biology Jun 6;17(1):122. (2016) <a target=\"_new\" href=\"https://genomebiology.biomedcentral.com/articles/10.1186/s13059-016-0974-4\">doi:10.1186/s13059-016-0974-4</a>.<br><br>";

if (isset($_SESSION['clicked_buttons'])) {
    $selected_markers = $_SESSION['clicked_buttons'];
} elseif (isset($_SESSION['geno_exps'])) {
    $geno_exp = $_SESSION['geno_exps'][0];
    $sql = "select marker_index from allele_byline_expidx where experiment_uid = $geno_exp";
    $result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    if ($row = mysqli_fetch_row($result)) {
        $selected_markers = json_decode($row[0], true);
        $selected_markers_count = count($selected_markers);
        if ($selected_markers_count > 1000) {
            echo "<br>Warning: $count markers selected. Truncating to 1000 markers.<br>\n";
            $selected_markers = array_slice($selected_markers, 0, 1000);
        }
    } else {
        die("Genotype experiment not found\n");
    }
} else {
    echo "<br>Please select one or more <a href = \"genotyping/marker_selection.php\">markers</a><br>\n";
    $selected_markers = array();
}

$notFound = "";
$geneFound = "";

//get list of assemblies
$sql = "select distinct(qtl_annotations.assembly_name), data_public_flag from qtl_annotations, assemblies
    where qtl_annotations.assembly_name = assemblies.assembly_name  order by assembly_name";
$result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
while ($row = mysqli_fetch_row($result)) {
    //pick latest assembly as default
    if ($row[1] == 1) {
        $assembly = $row[0];
        $assembly = $row[0];
        $assemblyList[] = $row[0];
        $assemblyFlag[] = $row[1];
    // do not show ones that are private
    } elseif (($row[1] == 0) && authenticate(array(USER_TYPE_PARTICIPANT, USER_TYPE_CURATOR, USER_TYPE_ADMINISTRATOR))) {
        $assembly = $row[0];
        $assemblyList[] = $row[0];
        $assemblyFlag[] = $row[1];
    }
}
//get assembly from genotype experiment if available
if (isset($_SESSION['geno_exps'])) {
    $geno_exp = $_SESSION['geno_exps'][0];
    $sql = "select assembly_name from genotype_experiment_info where experiment_uid = $geno_exp";
    $result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql<br>");
    if ($row = mysqli_fetch_row($result)) {
        if (preg_match("/[A-Z0-9]/", $row[0])) {
            $assembly = $row[0];
        }
    }
}

if (isset($_GET['assembly'])) {
    $assembly = $_GET['assembly'];
} elseif (isset($_SESSION['assembly'])) {
    $assembly = $_SESSION['assembly'];
}

//display list of assemblies
echo "<br>Genome Assembly <select id=\"assembly\" onchange=\"reload()\">\n";
foreach ($assemblyList as $key => $ver) {
    if ($ver == $assembly) {
        $selected = "selected";
    } else {
        $selected = "";
    }
    echo "<option value=$ver $selected $disabled>$ver</option>";
}
echo "</select>";
$sql = "select * from assemblies where data_public_flag = 0";
$result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
if ($row = mysqli_fetch_row($result)) {
    if (!authenticate(array(USER_TYPE_PARTICIPANT, USER_TYPE_CURATOR, USER_TYPE_ADMINISTRATOR))) {
        echo "  To access additional assemblies <a href=\"login.php\">Login</a>.<br>";
    }
}
echo "<br><br>";

$sql = "select marker_name, gene, description from qtl_annotations where assembly_name = \"$assembly\"";
$result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
while ($row = mysqli_fetch_row($result)) {
    $marker = $row[0];
    $gene = $row[1];
    $geneFound[$marker] = "<a target=\"_new\" href=" . $varLink[$assembly] . "?g=$gene>$gene</a>";
}

$linkOutIdx = array();
$vepList = array();
//echo "using assembly $assembly<br>\n";
/* check in loaded file first if not found then check marker_report_reference */
foreach ($selected_markers as $marker_uid) {
    $found = 0;
    if (isset($_SESSION['geno_exps'])) {
        $geno_exp = $_SESSION['geno_exps'][0];
        $sql = "select A_allele, B_allele from markers where marker_uid = $marker_uid";
        $result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        if ($row = mysqli_fetch_row($result)) {
            $a_allele = $row[0];
            $b_allele = $row[1];
        } else {
            die("Error: invalid marker\n");
        }
        $sql = "select marker_name, chrom, pos from allele_bymarker_exp_ACTG where experiment_uid = $geno_exp and marker_uid = $marker_uid";
        //echo "$sql<br>\n";
        $result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        if ($row = mysqli_fetch_row($result)) {
            $marker = $row[0];
            $chrom = $row[1];
            $pos = $row[2];
            $start = $pos - 1000;
            if ($start < 0) {
                $start = 0;
            }
            $stop = $pos + 1000;
            if (preg_match("/RefSeq/", $assembly)) {
                $jbrowse = "<a target=\"_new\" href=\"" . $browserLink[$assembly] . "$chrom:$start..$stop\">$chrom:$pos</a>";
            } else {
                $jbrowse = "<a target=\"_new\" href=\"" . $browserLink[$assembly] . "$chrom:$start-$stop\">$chrom:$pos</a>";
            }
            $linkOut = "<tr><td><a href=\"" . $config['base_url'] . "view.php?table=markers&name=$marker\">$marker</a><td>$jbrowse";
            if (isset($geneFound[$marker])) {
                $linkOut .= "<td>$geneFound[$marker]\n";
            } else {
                $linkOut .= "<td><td>\n";
            }
        }
        if (preg_match("/[0-9]/", $chrom) && preg_match("/[0-9]/", $pos)) {
            $found = 1;
            $vepList[] = "<tr><td>$row[1] $pos $pos $a_allele/$b_allele + $marker\n";
            $linkOutSort[] = $linkOut;
            $linkOutIndx[] = $chrom . $pos;
        }
    }
    if (!$found) {
        $sql = "select markers.marker_name, chrom, bin, pos, A_allele, B_allele, strand from marker_report_reference, markers
        where marker_report_reference.marker_uid = markers.marker_uid
        and assembly_name = \"$assembly\"
        and marker_report_reference.marker_uid = $marker_uid";
        //echo "$sql<br>\n";
        $result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli . "<br>$sql<br>"));
        if ($row = mysqli_fetch_row($result)) {
            $found = 1;
            $marker = $row[0];
            $chrom = $row[1];
            $bin = $row[2];
            $pos = $row[3];
            $strand = $row[6];
            $start = $pos - 1000;
            if ($start < 0) {
                $start = 0;
            }
            $stop = $pos + 1000;
            if ($strand == "F") {
                $strand = "+";
            } elseif ($strand == "R") {
                $strand = "-";
            }
            if (empty($bin)) {
                $bin = $chrom;
            }
            $vepList[] = "<tr><td>$bin $pos $pos $row[4]/$row[5] $strand $marker\n";
            if (preg_match("/RefSeq/", $assembly)) {
                $jbrowse = "<a target=\"_new\" href=\"" . $browserLink[$assembly] . "$chrom:$start..$stop\">$chrom:$pos</a>";
            } else {
                $jbrowse = "<a target=\"_new\" href=\"" . $browserLink[$assembly] . "$bin:$start-$stop\">$bin:$pos</a>";
            }
            $linkOut = "<tr><td><a href=\"" . $config['base_url'] . "view.php?table=markers&name=$marker\">$marker</a><td>$jbrowse";
            if (isset($geneFound[$marker])) {
                $linkOut .= "<td>$geneFound[$marker]\n";
            } else {
                $linkOut .= "<td><td>\n";
            }
            $linkOutSort[] = $linkOut;
            $linkOutIndx[] = $chrom . $pos;
        }
    }
    if (!$found) {
        $sql = "select marker_name from markers where marker_uid = $marker_uid";
        $result2 = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        if ($row2 = mysqli_fetch_row($result2)) {
            $notFound .= "$row2[0]<br>\n";
        } else {
            echo "Error: marker_uid = $marker_uid not found<br>\n";
        }
    }
}

$count = count($linkOutIndx);
//echo "$count matches found<br><br>\n";
$unique_str = chr(rand(65, 80)).chr(rand(65, 80)).chr(rand(65, 80)).chr(rand(65, 80));
if ($count > 0) {
    asort($linkOutIndx);
    echo "The links in the region column show known variations in a genome browser and their effects. The region is 1000 bases to either side of marker. ";
    echo "The links in the gene column show a table with known variations, consequence type, and SIFT score.<br>\n";
    if ($selected_markers_count > 1000) {
        $dir = "/tmp/tht/";
        $filename = $dir . "ensembl_links_" . $unique_str . ".html";
        ?>
        <input type="button" value="Open Annotation File"
                onclick="javascript:window.open('<?php echo $filename ?>');"><br><br>
        <?php
        $h = fopen($filename, "w");
        fwrite($h, "<html lang=\"en\"><table><tr><td>marker<td>region<td>gene\n");
        foreach ($linkOutIndx as $key => $val) {
            fwrite($h, $linkOutSort[$key]);
        }
        fwrite($h, "</table>");
        fclose($h);
    } else {
        echo "<table><tr><td>marker<td>region<td>gene\n";
        foreach ($linkOutIndx as $key => $val) {
            echo "$linkOutSort[$key]\n";
        }
        echo "</table>\n";
    }
}
$count = count($vepList);
if (($count > 0) && preg_match("/TGACv1/", $assembly)) {
    echo "<br>To run Variant Effect Predictor, copy the data below and paste it into the text box on the website <a href=\"$ensemblLinkVEP\" target=\"_new\">Ensembl Plant VEP</a>. ";
    echo "Calculations take about 5 minutes per marker.<br>\n";
    if ($selected_markers_count > 1000) {
        $filename = $dir . "vep_submission_" . $unique_str . ".html";
        ?>
        <input type="button" value="Open VEP input file"
                onclick="javascript:window.open('<?php echo $filename ?>');"><br><br>
        <?php
        $h = fopen($filename, "w");
        fwrite($h, "<table>\n");
        foreach ($vepList as $val) {
            fwrite($h, $val);
        }
        fwrite($h, "</table>");
        fclose($h);
    } else {
        echo "<table>";
        foreach ($vepList as $val) {
            echo $val;
        }
        echo "</table>\n";
    }
}
if ($notFound != "") {
    echo "<br>Markers that do not have BLAST match to assembly<br>$notFound<br>\n";
}
echo "</div>";
include $config['root_dir'].'theme/footer.php';
