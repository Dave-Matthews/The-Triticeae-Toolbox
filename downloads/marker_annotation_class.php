<?php

/*
 * report map location and gene annotations for selected markers 
 * if map not selected map not in SESSION then use value from settings table
 */

namespace T3;

class Downloads
{
    public function __construct($function = null)
    {
        $this->displaySelection();
    }

    public function displaySelection()
    {
        global $config;
        global $mysqli;
        include($config['root_dir'].'theme/normal_header.php');

        echo "<h2>Download the annotation for selected markers or genotype experiment</h2>\n";

        if (!isset($_SESSION['geno_exps']) && !isset($_SESSION['clicked_buttons']) && !isset($_SESSION['selected_lines'])) {
            echo "Please select a genotype experiment or a set of markers.<br>\n";
        }
        if (isset($_SESSION['selected_map'])) {
            $selected_map = $_SESSION['selected_map'];
            if (isset($_SESSION['geno_exps'])) {
                $this->downloadGenoExp($selected_map);
            } elseif (isset($_SESSION['clicked_buttons'])) {
                $this->downloadMarkers($selected_map);
            } else {
                echo "Please select a genotype experiment or a set of markers<br>\n";
            }
        } else {
            echo "Please <a href='maps/select_map.php'>select a map</a>.<br>\n";
        }
        echo "</div>";
        include $config['root_dir'].'theme/footer.php';
    }

    public function downloadMarkers($map)
    {
        global $mysqli;
        echo "<h3>Marker annotation for slected markers</h3>";
        $markers = $_SESSION['clicked_buttons'];
        $markers_str = implode(",", $markers);
        $count = count($markers);
        echo "$count markers found<br>\n";

        $sql = "select marker_uid, value, name_annotation, comments from marker_annotations, marker_annotation_types
                where marker_annotations.marker_annotation_type_uid = marker_annotation_types.marker_annotation_type_uid
                and marker_uid IN ($markers_str)";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        while ($row = mysqli_fetch_array($res)) {
            $marker_uid = $row[0];
            $annot_list[$marker_uid][] = "$row[1]<td>$row[3]";
        }
       
        $sql = "select marker_uid, marker_name from markers where marker_uid IN ($markers_str)";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        $count = mysqli_num_rows($res);
        if ($count > 0) {
            echo "<table>";
            echo "<tr><td>Marker<td>Entry<td>Description\n";
            while ($row = mysqli_fetch_array($res)) {
                $marker_uid = $row[0];
                echo "<tr><td>$row[1]";
                if (isset($annot_list[$marker_uid])) {
                    $count = 1;
                    foreach ($annot_list[$marker_uid] as $val) {
                        if ($count == 1) {
                            echo "<td>$val\n";
                        } else {
                            echo "<tr><td><td>$val";
                        }
                        $count++;
                    }
                }
                echo "\n";
            }
            echo "<table>";
        } else {
            echo "<br>No annotation entries found<br>\n";
        }
    }

    public function downloadGenoExp($map)
    {
        global $mysqli;
        echo "<h3>Marker annotation for Genotype Experiment</h3>";
        $geno_exp = $_SESSION['geno_exps'];
        $geno_str = $geno_exp[0];
        $sql = "select trial_code, platform_uid from experiments, genotype_experiment_info
                where experiments.experiment_uid = genotype_experiment_info.experiment_uid
                and experiments.experiment_uid = $geno_str";
        if ($res = mysqli_query($mysqli, $sql)) {
            $row = mysqli_fetch_assoc($res);
            $geno_name = $row['trial_code'];
        }
        $sql = "select count(*) from allele_bymarker_exp_101 where experiment_uid = $geno_str";
        if ($res = mysqli_query($mysqli, $sql)) {
            $row = mysqli_fetch_array($res);
            $count = $row[0];
            echo "$count markers found in experiment $geno_name<br>\n";
        }

        $sql = "select marker_uid, value, name_annotation, comments from marker_annotations, marker_annotation_types
                where marker_annotations.marker_annotation_type_uid = marker_annotation_types.marker_annotation_type_uid";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        while ($row = mysqli_fetch_array($res)) {
            $marker_uid = $row[0];
            $annot_list[$marker_uid][] = "$row[1]<td>$row[3]";
        }

        $sql = "select allele_bymarker_exp_101.marker_uid, marker_name, chromosome, start_position from markers_in_maps, allele_bymarker_exp_101, map
            where experiment_uid = $geno_str
            and markers_in_maps.marker_uid = allele_bymarker_exp_101.marker_uid
            and markers_in_maps.map_uid = map.map_uid
            and map.mapset_uid = $map";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        $count = mysqli_num_rows($res);
        if ($count > 0) {
            echo "<table>";
            echo "<tr><td>Marker<td>Chromosome<td>Position<td>Entry<td>Description\n";
            while ($row = mysqli_fetch_array($res)) {
                $marker_uid = $row[0];
                echo "<tr><td>$row[1]<td>$row[2]<td>$row[3]\n";
                if (isset($annot_list[$marker_uid])) {
                    $count = 1;
                    foreach ($annot_list[$marker_uid] as $val) {
                        if ($count == 1) {
                            echo "<td>$val\n";
                        } else {
                            echo "<tr><td><td><td><td>$val";
                        }
                        $count++;
                    }
                }
            }
            echo "</table>";
        } else {
            echo "<br>No map entries found<br>\n";
        }
    }
}
