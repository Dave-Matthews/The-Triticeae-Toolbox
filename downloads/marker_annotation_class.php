<?php

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
        include($config['root_dir'].'theme/normal_header.php');
        echo "<h2>Download the annotation for selected markers or genotype experiment</h2>\n";
        if (isset($_SESSION['geno_exps'])) {
            $this->downloadGenoExp();
        } elseif (isset($_SESSION['clicked_buttons'])) {
            $this->downloadMarkers();
        } elseif (isset($_SESSION['selected_lines'])) {
            $this->downloadMarkers2();
        } else {
            echo "Please select a genotype experiment or a set of markers<br>\n";
        }
        echo "</div>";
        include $config['root_dir'].'theme/footer.php';
    }

    public function downloadMarkers()
    {
        global $mysqli;
        echo "<h3>Marker annotation for slected markers</h3>";
        $markers = $_SESSION['clicked_buttons'];
        $markers_str = implode(",", $markers);
        $count = count($markers);
        echo "$count markers found<br>\n";
       
        echo "<table>";
        echo "<tr><td>Marker<td>Chromosome<td>Position\n";
        $sql = "select marker_name, chromosome, start_position from markers_in_maps, markers, map
            where markers.marker_uid IN ($markers_str) 
            and markers_in_maps.marker_uid = markers.marker_uid
            and markers_in_maps.map_uid = map.map_uid
            and map.mapset_uid = 15";
        if ($res = mysqli_query($mysqli, $sql)) {
            while ($row = mysqli_fetch_array($res)) {
                echo "<tr><td>$row[0]<td>$row[1]<td>$row[2]\n";
            }
        }
        echo "</table>";
    }

    public function downloadMarkers2()
    {
        global $mysqli;
        echo "<h3>Marker annotation for slected lines</h3>";
        $lines = $_SESSION['selected_lines'];
        $selectedlines = implode(",", $lines);

        $sql_exp = "SELECT DISTINCT e.experiment_uid AS exp_uid
        FROM experiments e, experiment_types as et, line_records as lr, tht_base as tb
        WHERE e.experiment_type_uid = et.experiment_type_uid
        AND lr.line_record_uid = tb.line_record_uid
        AND e.experiment_uid = tb.experiment_uid
        AND lr.line_record_uid in ($selectedlines)
        AND et.experiment_type_name = 'genotype'";
        if ($res = mysqli_query($mysqli, $sql_exp)) {
            if (mysqli_num_rows($res)>0) {
                while ($row = mysqli_fetch_array($res)) {
                    $exp[] = $row["exp_uid"];
                }
                $exp = implode(',', $exp);
            }
            echo "using markers from these experiments $exp<br>\n";
        }

        echo "<table>";
        echo "<tr><td>Marker<td>Chromosome<td>Position\n";
        $sql = "select DISTINCT marker_name, chromosome, start_position from markers_in_maps,
            allele_frequencies, map, markers
            where allele_frequencies.experiment_uid in ($exp)
            and markers.marker_uid = markers_in_maps.marker_uid
            and markers_in_maps.marker_uid = allele_frequencies.marker_uid
            and markers_in_maps.map_uid = map.map_uid
            and map.mapset_uid = 15";
        if ($res = mysqli_query($mysqli, $sql)) {
            while ($row = mysqli_fetch_array($res)) {
                echo "<tr><td>$row[0]<td>$row[1]<td>$row[2]\n";
            }
        }
        echo "</table>";
    }

    public function downloadGenoExp()
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
       
        echo "<table>";
        echo "<tr><td>Marker<td>Chromosome<td>Position\n";
        $sql = "select marker_name, chromosome, start_position from markers_in_maps, allele_bymarker_exp_101, map
            where experiment_uid = $geno_str
            and markers_in_maps.marker_uid = allele_bymarker_exp_101.marker_uid
            and markers_in_maps.map_uid = map.map_uid
            and map.mapset_uid = 15"; 
        if ($res = mysqli_query($mysqli, $sql)) {
            while ($row = mysqli_fetch_array($res)) {
                echo "<tr><td>$row[0]<td>$row[1]<td>$row[2]\n";
            }
        }
        echo "</table>";
    }
}
