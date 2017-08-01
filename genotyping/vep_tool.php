<?php
require 'config.php';
include $config['root_dir'].'includes/bootstrap.inc';
$mysqli = connecti();

include $config['root_dir'].'theme/admin_header2.php';

if (!isset($ensemblLinkVEP)) {
    echo "Error: Please define VEP in directory config.php file";
}

echo "<h2>Variant Effect Predictor</h2>\n";
echo "VEP determines the effect of variants (SNPs, insertions, deletions, CNVs or structural variants) on genes, transcripts, and protein sequence, as well as regulatory regions.<br>";
echo " To run Variant Effect Predictor, copy the data below and paste it into the text box on the website <a href=\"$ensemblLinkVEP\" target=\"_new\">Ensembl Plant VEP</a>.";
echo " For a description of the method see <a href=\"http://www.ncbi.nlm.nih.gov/pubmed/20562413\" target=\"_new\">McLaren et. al.</a>.<br>\n";

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
echo "<br><pre>\n";
foreach ($selected_markers as $marker_uid) {
    $sql = "select markers.marker_name, chrom, bin, pos, A_allele, B_allele, strand from marker_report_reference, markers
    where marker_report_reference.marker_uid = markers.marker_uid
    and marker_report_reference.marker_uid = $marker_uid";
    $result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    if ($row = mysqli_fetch_row($result)) {
        $strand = $row[6];
        if ($strand == "F") {
            $strand = "+";
        } elseif ($strand == "R") {
            $strand = "-";
        }
        $vepFound .= "$row[2] $row[3] $row[3] $row[4]/$row[5] $strand $row[0]\n";
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
if ($vepFound != "") {
    echo "Data for VEP tool<br><br>\n$vepFound<br>\n";
}
if ($notFound != "") {
    echo "<br>Markers that do not have BLAST match to assembly<br>$notFound<br>\n";
}
echo "</div>";

include $config['root_dir'].'theme/footer.php';
