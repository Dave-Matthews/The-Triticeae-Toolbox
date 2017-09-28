<?php
require 'config.php';
include $config['root_dir'].'includes/bootstrap.inc';
$pageTitle = "Variant Effects Gene";
$mysqli = connecti();

include $config['root_dir'].'theme/admin_header2.php';

if (isset($_GET['g'])) {
    $gene = $_GET['g'];

    echo "<h2>Variant Effects Gene $gene</h2>\n";
    echo "ENSEMBL VARIANT EFFECT PREDICTOR v90.4<br>\n";
    echo "Citation: McLaren et. al. 2016 (doi:10.1186/s13059-016-0974-4)<br><br>\n";

    echo "<table><tr><td>marker name<td>location<td>feature<td>consequence<td>impact\n";
    $sql = "select marker_name, location, feature, consequence, impact from vep_annotations where gene = \"$gene\"";
    $result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row = mysqli_fetch_row($result)) {
        $marker_name = $row[0];
        echo "<tr><td><a href=\"" . $config['base_url'] . "view.php?table=markers&name=$marker_name\">$marker_name</a><td>$row[1]<td>$row[2]<td>$row[3]<td>$row[4]";
    }
    echo "</table>\n";
} else {
    echo "<br>Please select gene<br>\n";
}
echo "</div>";
include $config['root_dir'].'theme/footer.php';
