<?php
require 'config.php';
include $config['root_dir'].'includes/bootstrap.inc';
$pageTitle = "Variant Effects";
$mysqli = connecti();

include $config['root_dir'].'theme/admin_header2.php';
?>
<script type="text/javascript" src="genotyping/variations.js"></script>
<?php

if (!isset($ensemblLinkVEP)) {
    echo "Error: Please define VEP in directory config.php file";
}

echo "<h2>Variant Effects</h2>\n";
echo "This page provides links to Sorting Intolerant From Tolerant (SIFT) and Variant Effect Predictor (VEP) to predict whether an amino aid substitution affects protein function.<br>";
echo "SIFT missense predictions for genomes. <a href=\"http://sift.bii.a-star.edu.sg/www/nprot2016_vaser.pdf\">Nature Protocols 2016; 11:1-9</a>. ";
echo "The Ensembl Variant Effect Predictor. Genome Biology Jun 6;17(1):122. (2016) <a href=\"https://genomebiology.biomedcentral.com/articles/10.1186/s13059-016-0974-4\">doi:10.1186/s13059-016-0974-4</a>.<br><br>";

if (isset($_SESSION['clicked_buttons'])) {
    $selected_markers = $_SESSION['clicked_buttons'];
} else {
    echo "<br>Please select one or more <a href = \"genotyping/marker_selection.php\">markers</a><br>\n";
    $selected_markers = array();
}

$notFound = "";
$vepFound = "";
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
    } elseif (($row[1] == 0) && authenticate(array(USER_TYPE_CURATOR, USER_TYPE_ADMINISTRATOR))) {
        $assembly = $row[0];
        $assemblyList[] = $row[0];
        $assemblyFlag[] = $row[1];
    }
}
if (isset($_GET['assembly'])) {
    $assembly = $_GET['assembly'];
} elseif (isset($_SESSION['assembly'])) {
    $assembly = $_SESSION['assembly'];
}

//display list of assemblies
echo "<br><select id=\"assembly\" onchange=\"reload()\">\n";
foreach ($assemblyList as $key => $ver) {
    if ($ver == $assembly) {
        $selected = "selected";
    } else {
        $selected = "";
    }
    echo "<option value=$ver $selected $disabled>$ver</option>";
}
echo "</select><br><br>";

$count = 0;
$sql = "select marker_name, gene, description from qtl_annotations where assembly_name = \"$assembly\"";
$result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
while ($row = mysqli_fetch_row($result)) {
    $count++;
    $marker = $row[0];
    $gene = $row[1];
    $geneFound[$marker] = "<a target=\"_new\" href=$ensemblLink/Gene/Variation_Gene/Table?g=$gene>$gene</a>";
}

foreach ($selected_markers as $marker_uid) {
    $sql = "select markers.marker_name, chrom, bin, pos, A_allele, B_allele, strand from marker_report_reference, markers
    where marker_report_reference.marker_uid = markers.marker_uid
    and assembly_name = \"$assembly\"
    and marker_report_reference.marker_uid = $marker_uid";
    $result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli . "<br>$sql<br>"));
    if ($row = mysqli_fetch_row($result)) {
        $marker = $row[0];
        $chrom = $row[1];
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
        $vepFound .= "<tr><td>$row[2] $row[3] $row[3] $row[4]/$row[5] $strand $row[0]\n";
        $jbrowse = "<a target=\"_new\" href=$ensemblLink/Location/View?r=$row[2]:$start-$stop>$row[2]:$pos</a>";
        $linkOut = "<tr><td><a href=\"view.php?table=markers&name=$marker\"</a>$marker<td>$jbrowse";
        if (isset($geneFound[$marker])) {
            $linkOut .= "<td>$geneFound[$marker]\n";
        } else {
            $linkOut .= "<td><td>\n";
        }
        $linkOutSort[] = $linkOut;
        $linkOutIndx[] = $chrom . $pos;
    } elseif (isset($_SESSION['geno_exps'])) {
        $geno_exp = $_SESSION['geno_exps'][0];
        $sql = "select marker_name, chrom, pos from allele_bymarker_exp_ACTG where experiment_uid = $geno_exp and marker_uid = $marker_uid";
        $result2 = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        if ($row2 = mysqli_fetch_row($result2)) {
            $marker = $row2[0];
            $chrom = $row2[1];
            $pos = $row2[3];
            $jbrowse = "<a target=\"_new\" href=$ensemblLink/Location/View?r=$row2[2]:$start-$stop>$row2[2]:$pos</a>";
            $linkOut = "<tr><td><a href=\"view.php?table=markers&name=$marker\"</a>$marker<td>$jbrowse";
            if (isset($geneFound[$marker])) {
                $linkOut .= "<td>$geneFound[$marker]\n";
            } else {
                $linkOut .= "<td><td>\n";
            }
            $linkOutSort[] = $linkOut;
            $linkOutIndx[] = $chrom . $pos;
        } else {
            $sql = "select marker_name from markers where marker_uid = $marker_uid";
            $result2 = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
            if ($row2 = mysqli_fetch_row($result2)) {
                $notFound .= "$row2[0]<br>\n";
            } else {
                echo "Error: marker_uid = $marker_uid not found<br>\n";
            }
        }
    }
}

asort($linkOutIndx);
if (!empty($linkOutSort)) {
    echo "The links in the region column show known variations in a browser and their effects at Ensembl Plants. The region is 1000 bases to either side of marker. ";
    echo "The links in the gene column show a table with known variations, consequence type, and SIFT score.<br><table>\n";
    echo "<tr><td>marker<td>region<td>gene\n";
    foreach ($linkOutIndx as $key => $val) {
        echo "$linkOutSort[$key]\n";
    }
    echo "</table>\n";
}
if ($vepFound != "") {
    echo "<br>To run Variant Effect Predictor, copy the data below and paste it into the text box on the website <a href=\"$ensemblLinkVEP\" target=\"_new\">Ensembl Plant VEP</a>. ";
    echo "Calculations take about 5 minutes per marker.\n";
    echo "<table>$vepFound</table>\n";
}
if ($notFound != "") {
    echo "<br>Markers that do not have BLAST match to assembly<br>$notFound<br>\n";
}
echo "</div>";

include $config['root_dir'].'theme/footer.php';
