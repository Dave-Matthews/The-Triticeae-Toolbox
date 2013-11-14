<?php
/**
 * Display Markers
 *
 * PHP version 5.3
 * Prototype version 1.5.0
 *
 * @category PHP
 * @package  T3
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @version  GIT: 2
 * @link     http://triticeaetoolbox.org/wheat/genotyping/display_markers.php
 *
 */
require 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
$mysqli = connecti();
if (isset($_SESSION['clicked_buttons']) && (count($_SESSION['clicked_buttons']) > 0)) {
    $dir = "/tmp/tht/";
    $unique_str = chr(rand(65, 80)).chr(rand(65, 80)).chr(rand(65, 80)).chr(rand(65, 80));
    $filename = "selected_markers_" . $unique_str . ".csv";
    $h = fopen($dir.$filename, "w+");
    fwrite($h, "name,A_allele,B_allele,sequence\n");
    foreach ($_SESSION['clicked_buttons'] as $mkruid) {
        $count_markers++;
        $sql = "select marker_name, A_allele, B_allele, sequence from markers where marker_uid=$mkruid";
        $result=mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        if ($row=mysqli_fetch_assoc($result)) {
            $selval=$row['marker_name'];
            $a_allele=$row['A_allele'];
            $b_allele=$row['B_allele'];
            $seq = $row['sequence'];
            fwrite($h, "$selval,$a_allele,$b_allele,$seq\n");
        } else {
            fwrite($h, "$mkruid,not found\n";
        }
    }
}
fclose($h);
header("Location: ".$dir.$filename);
