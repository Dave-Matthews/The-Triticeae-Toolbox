<?php

require 'config.php';
require $config['root_dir'] . 'includes/bootstrap.inc';

$mysqli = connecti();

global $config;
require $config['root_dir'] . 'theme/admin_header.php';
if (isset($_GET['uid'])) {
    $experiment_uid = $_GET['uid'];
} else {
    die("Error - no experiment found<br>\n");
}

$count = 0;
$sql = "select * from csr_system where system_uid =$experiment_uid";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
echo "<h2>CSR Spectrometer System</h2>\n";
echo "<table>";
while ($row = mysqli_fetch_assoc($res)) {
    $system_name = $row["system_name"];
    $instrument = $row["instrument"];
    $serial_num = $row["serial_num"];
    $serial_num2 = $row["serial_num2"];
    $grating = $row["grating"];
    $collection = $row["collection_lens"];
    $longpass = $row["longpass_filter"];
    $slit_aperature = $row["slit_aperture"];
    $reference = $row["reference"];
    $cable_type = $row["cable_type"];
    $wavelengths = $row["wavelengths"];
    $bandwidths = $row["bandwidths"];
    $comments = $row["comments"];

    echo "<tr><td>System Name<td>$system_name";
    echo "<tr><td>Instrument<td>$instrument";
    echo "<tr><td>Serial number<td>$serial_num";
    echo "<tr><td>Second serial number<td>$serail_num2";
    echo "<tr><td>Grating number<td>$grating";
    echo "<tr><td>Collection lense<td>$collection";
    echo "<tr><td>Longpass filter<td>$longpass";
    echo "<tr><td>Entrance slit aperture<td>$slit_aperature";
    echo "<tr><td>Reference<td>$reference";
    echo "<tr><td>Fiber optic cable<td>$cable_type";
    echo "<tr><td>Focal wavelength<td>$wavelengths";
    echo "<tr><td>Bandwidths<td>$bandwidths";
    echo "<tr><td>Comments<td>$comments\n";
    $count++;
}
echo "</table>";
