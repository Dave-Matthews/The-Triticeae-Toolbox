<?php
require 'config.php';
include $config['root_dir'].'includes/bootstrap.inc';
$mysqli = connecti();

include $config['root_dir'].'theme/admin_header2.php';

if (!isset($ensemblLinkVEP)) {
    echo "Error: Please define VEP in directory config.php file";
}

echo "<h2>Variant Effects</h2>\n";
echo "This page provides links to Sorting Intolerant From Tolerant (SIFT) and Variant Effect Predictor (VEP) to predict whether an amino aid substitution affect protein function.<br>";

if (isset($_SESSION['clicked_buttons'])) {
    $selected_markers = $_SESSION['clicked_buttons'];
} else {
    echo "<br>Please select one or more <a href = \"genotyping/marker_selection.php\">markers</a><br>\n";
}

$assembly_list = array();
$sql = "select distinct(assembly) from marker_report_reference";
$result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
while ($row=mysqli_fetch_row($result)) {
    $assembly_list[] = $row[0];
}

$notFound = "";
$vepFound = "";
$geneFound = "";
$linkOut = "";

//get latest assembly
$sql = "select distinct(assembly_ver) from qtl_annotations order by assembly_ver";
$result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
while ($row = mysqli_fetch_row($result)) {
    $assembly = $row[0];
}
echo "<br>Using assembly $assembly. \n";

$count = 0;
$sql = "select marker_name, gene, description from qtl_annotations where assembly_ver = \"$assembly\"";
$result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
while ($row = mysqli_fetch_row($result)) {
    $count++;
    $marker = $row[0];
    $gene = $row[1];
    $geneFound[$marker] = "<a target=\"_new\" href=$ensemblLink/Gene/Variation_Gene/Table?g=$gene>$gene</a>";
}

echo "<br><pre>\n";
foreach ($selected_markers as $marker_uid) {
    $sql = "select markers.marker_name, chrom, bin, pos, A_allele, B_allele, strand from marker_report_reference, markers
    where marker_report_reference.marker_uid = markers.marker_uid
    and marker_report_reference.marker_uid = $marker_uid";
    $result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    if ($row = mysqli_fetch_row($result)) {
        $marker = $row[0];
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
        $jbrowse = "<a target=\"_new\" href=$ensemblLink/Location/View?r=$row[2]:$start-$stop>$row[2]</a>";
        $linkOut .= "<tr><td>$row[0]<td>$jbrowse";
        if (isset($geneFound[$marker])) {
            $linkOut .= "<td>$geneFound[$marker]\n";
        } else {
            $linkOut .= "<td><td>\n";
        }
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
echo "</pre>";
if ($linkOut != "") {
    echo "The links in the region column show known variations in a browser and their effects at Ensembl Plants. The region is shows 1000 bases to either side of marker. ";
    echo "The links in the gene column show a table with known variaitions, consequence type, and SIFT score.<br><table>\n<tr><td>marker<td>region<td>gene\n$linkOut</table>\n";
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
