<?php
/**
 * Display Markers
 *
 * PHP version 5.3
 * Prototype version 1.5.0
 *
 * @author  Clay Birkett <clb343@cornell.edu>
 * @license http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link    http://triticeaetoolbox.org/wheat/genotyping/display_markers.php
 */
require 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
$mysqli = connecti();
ini_set('memory_limit', '2G');

$function = $_GET['function'];
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

if (isset($_SESSION['clicked_buttons']) && (count($_SESSION['clicked_buttons']) > 0)) {
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
        ?>
        <h2>Marker Information</h2>
        <table><tr><th>name<th>type<th>A_allele<th>B_allele<th>synonym<th>mapped<th>lines genotyped<th>sequence
        <style type="text/css">
        th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
        table {background: none; border-collapse: collapse}
        td {border: 1px solid #eee !important;}
        h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
        </style>

        <style type="text/css">
        table.marker {background: none; border-collapse: collapse}
        th.marker {background: #5b53a6; color: #fff; padding: 5px 0; border: 0; border-color: #fff}
        td.marker {padding: 5px 0; border: 0 !important;}
        </style>
        <?php
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
                echo "<tr><td><a href=view.php?table=markers&uid=$mkruid>$selval</a><td nowrap>$type<td>$a_allele<td>$b_allele<td nowrap>$syn<td>$mkr_mapped<td>$lines_geno<td>$seq\n";
            }
        } else {
            if ($use_file) {
                fwrite($h, "$mkruid,not found\n");
            } else {
            }
        }
    }
} elseif (isset($_SESSION['geno_exps'])) {
    $use_file = 1;
    $dir = "/tmp/tht/";
    $unique_str = chr(rand(65, 80)).chr(rand(65, 80)).chr(rand(65, 80)).chr(rand(65, 80));
    $filename = "selected_markers_" . $unique_str . ".csv";
    $h = fopen($dir.$filename, "w+");
    fwrite($h, "name,type,A_allele,B_allele,synonym,mapped,lines genotyped,sequence\n");
    $exp = $_SESSION['geno_exps'];
    $exp = $exp[0];
    $sql = "select markers.marker_uid, marker_name, A_allele, B_allele, sequence, marker_type_name from markers, marker_types, allele_frequencies
         where markers.marker_type_uid = marker_types.marker_type_uid
         and markers.marker_uid = allele_frequencies.marker_uid
         and allele_frequencies.experiment_uid = $exp";
    $result=mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row=mysqli_fetch_array($result)) {
        $mkruid=$row['marker_uid'];
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
            echo "<tr><td><a href=view.php?table=markers&uid=$mkruid>$selval</a><td nowrap>$type<td>$a_allele<td>$b_allele<td nowrap>$syn<td>$mkr_mapped<td>$lines_geno<td>$seq\n";
        }
    }
} elseif (isset($_GET['geno_exp'])) {
    $use_file = 1;
    $dir = "/tmp/tht/";
    $unique_str = chr(rand(65, 80)).chr(rand(65, 80)).chr(rand(65, 80)).chr(rand(65, 80));
    $filename = "selected_markers_" . $unique_str . ".csv";
    $h = fopen($dir.$filename, "w+");
    fwrite($h, "name,type,A_allele,B_allele,synonym,mapped,lines genotyped,sequence\n");
    $exp = $_GET['geno_exp'];
    $exp = intval($exp);
    $sql = "select markers.marker_uid, marker_name, A_allele, B_allele, sequence, marker_type_name from markers, marker_types, allele_frequencies
         where markers.marker_type_uid = marker_types.marker_type_uid
         and markers.marker_uid = allele_frequencies.marker_uid
         and allele_frequencies.experiment_uid = $exp";
    $result=mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row=mysqli_fetch_array($result)) {
        $mkruid=$row['marker_uid'];
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
        fwrite($h, "$selval,$type,$a_allele,$b_allele,\"$syn\",$mkr_mapped,$lines_geno,$seq\n");
    }
}
if ($use_file) {
    fclose($h);
    header("Location: ".$dir.$filename);
} else {
    echo "</table><br>";
    print "<a href=genotyping/display_markers.php?function=download>Download marker information</a><br>\n";
}
