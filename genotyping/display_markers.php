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
$function = $_GET['function'];
if (isset($_SESSION['clicked_buttons']) && (count($_SESSION['clicked_buttons']) > 0)) {
    $sql = "select marker_uid, value from marker_synonyms";
    $result=mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row=mysqli_fetch_assoc($result)) {
        $marker_uid = $row['marker_uid'];
        $value = $row['value'];
        if (isset($synonym[$marker_uid])) {
                $synonym[$marker_uid] .= ", $value";
        } else {
                $synonym[$marker_uid] = $value;
        }
    }
    $sql = "select marker_uid from markers_in_maps";
    $result=mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row=mysqli_fetch_assoc($result)) {
        $marker_uid = $row['marker_uid'];
        $mapped[$marker_uid] = 1;
    }
    $sql = "select marker_uid, sum(total) from allele_frequencies group by marker_uid";
    $result=mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row=mysqli_fetch_assoc($result)) {
        $marker_uid = $row['marker_uid'];
        $sum = $row['sum(total)'];
        $lines[$marker_uid] = $sum;
    }
    if ((count($_SESSION['clicked_buttons']) > 1000) || ($function == "download")) {
        $use_file = 1;
        $dir = "/tmp/tht/";
        $unique_str = chr(rand(65, 80)).chr(rand(65, 80)).chr(rand(65, 80)).chr(rand(65, 80));
        $filename = "selected_markers_" . $unique_str . ".csv";
        $h = fopen($dir.$filename, "w+");
        fwrite($h, "name,type,A_allele,B_allele,synonym,mapped,lines genotyped,sequence\n");
    } else {
        include $config['root_dir'].'theme/admin_header.php';
        $use_file = 0;
        echo "<table><tr><td>name<td>type<td>A_allele<td>B_allele<td>synonym<td>mapped<td>lines genotyped<td>sequence\n";
    }
    foreach ($_SESSION['clicked_buttons'] as $mkruid) {
        $count_markers++;
        $sql = "select marker_name, A_allele, B_allele, sequence, marker_type_name from markers, marker_types where markers.marker_type_uid = marker_types.marker_type_uid and markers.marker_uid=$mkruid";
        $result=mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        if ($row=mysqli_fetch_assoc($result)) {
            $selval=$row['marker_name'];
            $a_allele=$row['A_allele'];
            $b_allele=$row['B_allele'];
            $seq = $row['sequence'];
            $type = $row['marker_type_name'];
            if (isset($mapped[$mkruid])) {
                $mkr_mapped = "Yes";
            } else {
                $mkr_mapped = "";
            }
            if (isset($synonym[$mkruid])) {
                $syn = $synonym[$mkruid];
            } else {
                $syn = "";
            }
            if (isset($lines[$mkruid])) {
                $lines_geno = $lines[$mkruid];
            } else {
                $lines_geno = "";
            }
            if ($use_file) {
                fwrite($h, "$selval,$type,$a_allele,$b_allele,\"$syn\",$mkr_mapped,$lines_geno,$seq\n");
            } else {
                echo "<tr><td>$selval<td nowrap>$type<td>$a_allele<td>$b_allele<td nowrap>$syn<td>$mkr_mapped<td>$lines_geno<td>$seq\n";
            }
        } else {
            if ($use_file) {
                fwrite($h, "$mkruid,not found\n");
            } else {
            }
        }
    }
}
if ($use_file) {
    fclose($h);
    header("Location: ".$dir.$filename);
} else {
    echo "</table>";
    print "<a href=genotyping/display_markers.php?function=download>Download marker information</a><br>\n";
}
