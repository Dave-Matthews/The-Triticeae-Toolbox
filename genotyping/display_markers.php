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
session_start();
require $config['root_dir'].'theme/admin_header.php';
?>

<h3>Currently selected markers</h3>
<?php
if (isset($_SESSION['clicked_buttons']) && (count($_SESSION['clicked_buttons']) > 0)) {
    print "<table><tr><td>name<td>A_allele<td>B_allele\n";
    foreach ($_SESSION['clicked_buttons'] as $mkruid) {
        $count_markers++;
        $sql = "select marker_name, A_allele, B_allele from markers where marker_uid=$mkruid";
        $result=mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        while ($row=mysqli_fetch_assoc($result)) {
            $selval=$row['marker_name'];
            $a_allele=$row['A_allele'];
            $b_allele=$row['B_allele'];
            print "<tr><td>$selval<td>$a_allele<td>$b_allele\n";
        }
    }
    print "</table>";
}
