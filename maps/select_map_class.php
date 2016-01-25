<?php

/**
 *  Using a PHP class to implement the "Select Map" feature
 *
 * PHP version 5.3
 *
 * @author  Clay Birkett <clb343@cornell.edu>
 * @license http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link    http://triticeaetoolbox.org/wheat/maps/select_map.php
 */

namespace T3;

class Maps
{
    /**
     * Using the class's constructor to decide which action to perform
     *
     * @param string $function action to perform
     */
    public function __construct($function = null)
    {
        switch ($function) {
            case 'Save':
                $this->typeMapSave();
                break;
            case 'Markers':
                $this->typeMapMarker(); /* this is called by javascript using ajax because it can be slow */
                break;
            default:
                $this->typeMapSet(); /* initial case */
                break;
        }
    }

    /**
     * The wrapper action for the typeMapset . Handles outputting the header
     * and footer and calls the first real action of the typeMapset .
     *
     * @return avaiable maps
     */
    public function typeMapSet()
    {
        global $config;
        global $mysqli;
        include $config['root_dir'].'theme/normal_header.php';

        echo "<h2>Map Sets</h2>";
        echo "<div id=\"step1\">";
        $this->typeMapSetDisplay();
        echo "</div>";
        echo "<div id=\"step2\">";
        //echo "<img id=\"spinner\" src=\"images/ajax-loader.gif\" style=\"display:none;\" />";
        echo "</div>";
        if (isset($_SESSION['geno_exps'])) {
            $this->typeGenoExpDisplay();
        }
        $mapset_list = "";
        $sql = "select distinct(mapset.mapset_uid) from mapset, markers_in_maps as mim, map
               WHERE mim.map_uid = map.map_uid
               AND map.mapset_uid = mapset.mapset_uid";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        while ($row = mysqli_fetch_array($res)) {
            $uid = $row[0];
            if ($mapset_list == "") {
                $mapset_list = $uid;
            } else {
                $mapset_list .= ",$uid";
            }
        }
        ?>
        <div id=step3></div>
        <div id=step4><br>
        <button onclick="javascript: load_markersInMap(<?php echo $mapset_list ?>)">
        Calculate markers in map for selected lines</button>
        </div>
        <?php
        if (isset($_SESSION['selected_lines']) or isset($_SESSION['clicked_buttons'])) { ?>
            <script type="text/javascript">
            <!--  window.onload = load_markersInMap();-->
            </script>
            <?php
        }
        $footer_div = 1;
        include $config['root_dir'].'theme/footer.php';
    }

    /**
     * Display a table of available maps
     *
     * @return  available maps
     */
    public function typeMapSetDisplay()
    {
        global $mysqli;
        if (isset($_GET['map'])) {
            $map = $_GET['map'];
            $_SESSION['selected_map'] = $map;
            echo "Map selection saved.<br><br>\n";
        }
        ?>
        <style type="text/css">
         th {background: #5B53A6 !important; color: white !important; }
         table {background: none; border-collapse: collapse; }
         td {border: 1px solid #eee !important;}
         h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
        </style>
        <script type="text/javascript" src="maps/select_map06.js">
        </script>
        <form name="myForm" action="maps/select_map.php">
        <?php
        if (isset($_SESSION['selected_map'])) {
            $selected_map = $_SESSION['selected_map'];
        }
        $sql = "select count(*) as countm, mapset_name, mapset.mapset_uid as mapuid, mapset.comments as mapcmt from mapset, markers_in_maps as mim, map
          WHERE mim.map_uid = map.map_uid
          AND map.mapset_uid = mapset.mapset_uid
          GROUP BY mapset.mapset_uid";
        echo "This table lists the total markers in each map.\n";
        echo "If a marker is not in the the selected map set then it will be assigned to chromosome 0.<br><br>\n";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        echo "<table>\n";
        echo "<tr><td>select<td>markers<br>(total)<td>markers<br>(in selected lines)<td>map set name<td>comment (mouse over item for complete text)\n";
        while ($row = mysqli_fetch_assoc($res)) {
            $count = $row["countm"];
            $val = $row["mapset_name"];
            $uid = $row["mapuid"];
            $comment = $row["mapcmt"];
            $comm = substr($comment, 0, 100);
            if ($uid == $selected_map) {
                $checked = "checked=\"checked\"";
            } else {
                $checked = "";
            }
            echo "<tr><td><input type=\"radio\" name=\"map\" value=\"$uid\" $checked onchange=\"javascript: save_map(this.value)\"><td>$count";
            echo "<td><div id=$uid><img id=\"spinner$uid\" src=\"images/ajax-loader.gif\" style=\"display:none;\"></div><td>$val<td><article title=\"$comment\">$comm</article>\n";
        }
        echo "</table>";
        echo "</form><br>";
    }

    /**
     * If genotype experiment selected check for map location
     *
     */
    public function typeGenoExpDisplay()
    {
        global $mysqli;
        if (isset($_SESSION['geno_exps'])) {
            $found = 0;
            $geno_exp = $_SESSION['geno_exps'];
            $geno_str = implode(",", $geno_exp);
            $sql = "select experiments.experiment_uid, trial_code, genotype_experiment_info.comments from experiments, genotype_experiment_info
                where experiments.experiment_uid = genotype_experiment_info.experiment_uid
                and experiments.experiment_uid IN ($geno_str)";
            $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . $sql);
            while ($row = mysqli_fetch_array($res)) {
                $uid = $row[0];
                $name = $row[1];
                $comments = $row[2];
                $sql = "select marker_uid from allele_bymarker_exp_101 where experiment_uid = $uid and pos is not null limit 10";
                $res2 = mysql_query($sql) or die(mysql_error() . $sql);
                if ($row = mysql_fetch_array($res2)) {
                    if ($found == 0) {
                        echo "<table><tr><td>Genotype Experiment<td>comment";
                        $found++;
                    }
                    echo "<tr><td>$name<td>$comments<br><br>map information has been loaded into the database with the genotype experiment<br>you do not have to select a map set";
                }
            }
            if ($found > 0) {
                echo "</table>";
            }
        }
    }

    /**
     * called through ajax to display how many of the selected markers are in each map set
     *
     * @return null
     */
    public function typeMapMarker()
    {
        global $mysqli;
        $mapset_uid = $_GET['mapset'];
        $sql = "select marker_uid from mapset, markers_in_maps as mim, map
            WHERE mim.map_uid = map.map_uid
            AND map.mapset_uid = mapset.mapset_uid
            AND map.mapset_uid = $mapset_uid";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        while ($row = mysqli_fetch_array($res)) {
            $markers_map[] = $row[0];
        }
        $count = count($marker_map);
        if ($count > 100000) {
            echo "skip too large\n";
            return;
        }

        if (isset($_SESSION['clicked_buttons'])) {
            $markers = $_SESSION['clicked_buttons'];
        } elseif (isset($_SESSION['geno_exps'])) {
            $experiments_g = $_SESSION['geno_exps'];
            $geno_str = $experiments_g[0];
            $sql = "SELECT marker_uid from allele_bymarker_exp_ACTG where experiment_uid = $geno_str";
            $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
            while ($row = mysqli_fetch_row($res)) {
                $uid = $row[0];
                $markers[$uid] = 1;
            }
   
            foreach ($markers_map as $i => $marker_uid) {
                if (isset($markers[$marker_uid])) {
                    $markers_filtered[] = $marker_uid;
                }
            }
            
        } elseif (isset($_SESSION['selected_lines'])) {
            $selected_lines = $_SESSION['selected_lines'];
            $sql = "select marker_uid, marker_name from allele_byline_idx order by marker_uid";
            $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>" . $sql);
            $i=0;
            while ($row = mysqli_fetch_array($res)) {
                $uid = $row[0];
                $marker_list[$i] = $row[0];
                $marker_list_loc[$uid] = $i;
                $i++;
            }
            $outarray = array();
            foreach ($selected_lines as $line_record_uid) {
                $sql = "select alleles from allele_byline where line_record_uid = $line_record_uid";
                $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>" . $sql);
                if ($row = mysqli_fetch_array($res)) {
                    $alleles = $row[0];
                    $outarray = $outarray + explode(',', $alleles);
                }
            }

            foreach ($markers_map as $i => $marker_uid) {
                $loc = $marker_list_loc[$marker_uid];
                if (isset($outarray[$loc]) && !empty($outarray[$loc])) {
                    $markers_filtered[] = $marker_uid;
                }
            }

        } else {
            die("Error - must select lines or markers<br>\n");
        }
     
        $count = count($markers_filtered);
        echo "$count";
    }

    /**
     * save map in session variable
     *
     * @return null
     */
    public function typeMapSave()
    {
        global $mysqli;
        $map = $_GET['map'];
        $_SESSION['selected_map'] = $map;
        $sql = "select mapset_name from mapset where mapset_uid = $map";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . $sql);
        if ($row = mysqli_fetch_assoc($res)) {
            $map_name = $row["mapset_name"];
        } else {
            $map_name = "unknown";
        }
        echo "<br>Current selection = $map_name<br>\n";
    }
}
