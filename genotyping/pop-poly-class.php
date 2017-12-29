<?php

namespace T3;

class SelectMarkers
{
    public function __construct($function = null)
    {
        switch ($function) {
            case 'chrom':
                $this->displayChrom();
                break;
            case 'save':
                $this->save();
                break;
            default:
                $this->displayAll();
                break;
        }
    }

    public function save()
    {
        $count = count($_SESSION['selected_markers']);
        echo "Saved $count markers\n";
        $_SESSION['clicked_buttons'] = $_SESSION['selected_markers'];
    }

    public function findMarkers()
    {
        global $mysqli;
        global $config;

        echo "<h2>Polymorphisms for a population</h2>\n";
        echo "This tool finds polymorphisms between germplasm lines. Typically you will select the parent lines.<br>\n";
        echo "First select a genotype experiment then deselect lines until only 2 germplasm lines remain.<br><br>\n";

        $option = "";
        $option2 = "";
        if (isset($_GET['start']) && !empty($_GET['start'])) {
            $start = $_GET['start'];
            $option .= " pos > $start";
        }
        if (isset($_GET['stop']) && !empty($_GET['stop'])) {
            $stop = $_GET['stop'];
            $option .= " and pos < $stop";
        }
        if (isset($_GET['value']) && !empty($_GET['value'])) {
            $selected_chrom = $_GET['value'];
            $option .= " and chrom = \"$selected_chrom\"";
            $option2 = "AND mim.chromosome = \"$selected_chrom\"";
        }

        if (isset($_SESSION['selected_map'])) {
            $map = $_SESSION['selected_map'];
            $sql1 = "select distinct(chromosome) from markers_in_maps, map, mapset
                where markers_in_maps.map_uid = map.map_uid
                and map.mapset_uid = mapset.mapset_uid
                and map.mapset_uid = $map
                order by chromosome";
            $sql2 = "select min(mim.start_position), max(mim.start_position) from markers_in_maps as mim, map
                where mim.map_uid = map.map_uid
                AND map.mapset_uid = $map";
        } elseif (isset($_SESSION['geno_exps'])) {
            $geno_exp = $_SESSION['geno_exps'][0];
            $count = 0;
            $sql1 = "select distinct(chrom), min(pos), max(pos) from allele_bymarker_exp_ACTG where experiment_uid = $geno_exp group by chrom";
            $sql2 = "select min(pos), max(pos) from allele_bymarker_exp_ACTG where experiment_uid = $geno_exp";
        } else {
            //echo "<br>Please <a href=\"genotyping/marker_selection.php\">select genotype experiment</a> or <a href=\"maps\select_map.php\">map</a>.<br>\n";
            echo "<br>Please <a href=\"genotyping/marker_selection.php\">select genotype experiment</a>.<br>\n";
            die();
        }

        if (isset($_SESSION['selected_lines'])) {
            $line_ary = $_SESSION['selected_lines'];
            $line_count = count($line_ary);
            echo "$line_count lines selected<br>\n";
        } else {
            echo "<br>Please <a href=\"pedigree/line_properties.php\">select two or more germplasm lines</a>.<br>\n";
        }

        echo "<table>";
        echo "<tr><td>Chromosome:<td><select id=\"chrom\">";
        echo "<option>select</option>";
        $count = 0;

        $res = mysqli_query($mysqli, $sql1) or die(mysqli_error($mysqli));
        while ($row = mysqli_fetch_array($res)) {
            $chrom = $row[0];
            $start_list[$chrom] = $row[1];
            $stop_list[$chrom] = $row[2];
            if (preg_match("/[0-9]/", $row[0]) && preg_match("/[0-9]/", $row[2])) {
                $count++;
            }
            if (isset($_GET['value'])) {
                $selected_chrom = $_GET['value'];
                if ($chrom == $selected_chrom) {
                    $selected = "selected";
                } else {
                    $selected = "";
                }
            }
            echo "<option value=$chrom $selected>$chrom</option>\n";
        }
        echo "</select><br>\n";

        if (($count == 0) && !isset($_SESSION['selected_map'])) {
            echo "</table><br>";
            echo "No default map loaded with genotype experiment. Please select a map<br>\n";
            die();
        }

        $res = mysqli_query($mysqli, $sql2) or die(mysqli_error($mysqli));
        if ($row = mysqli_fetch_array($res)) {
            $min = $row[0];
            $max = $row[1];
        } else {
            die("Error: can not find map positions<br>\n");
        }
 
        echo "<tr><td>Start:<td><input type=\"text\" id=\"start\" value=\"$start\"><td>$min\n";
        echo "<tr><td>Stop:<td><input type=\"text\" id=\"stop\" value=\"$stop\"><td>$max\n";
        echo "<tr><td><input type=\"button\" value=\"Query\" onclick=\"select_chrom()\"/>";
        echo "</table><br>";
        $geno_exp = $_SESSION['geno_exps'][0];
        $sql = "select line_index, line_name_index from allele_bymarker_expidx where experiment_uid = $geno_exp";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
        if ($row = mysqli_fetch_array($res)) {
            $line_index = $row[0];
            $line_name = $row[1];
            $uid_list = json_decode($line_index, true);
            $name_list = json_decode($line_name, true);
            //echo "uid_list $line_index<br>\n";
        }
 
        $count = 0;
        $count_inpos = 0;
        $count_inmap = 0;
        $found_list = array();
        if (isset($_GET['value']) && !empty($_GET['value'])) {
            $geno_exp = $_SESSION['geno_exps'][0];

            if (isset($_SESSION['selected_map'])) {
                $sql = "select marker_uid, mim.start_position from markers_in_maps as mim, map
                where mim.map_uid = map.map_uid
                AND map.mapset_uid = $map $option2";
                $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql<br>\n");
                while ($row = mysqli_fetch_array($res)) {
                    $marker_uid = $row[0];
                    $pos = $row[1];
                    $map_list[$marker_uid] = $pos;
                }
                $temp = count($map_list);
                //echo "$temp found in map<br>\n";
                $sql = "select marker_uid, marker_name, alleles from allele_bymarker_exp_ACTG where experiment_uid = $geno_exp";
                //echo "$sql<br>\n";
                $res = mysqli_query($mysqli, $sql, MYSQLI_USE_RESULT) or die(mysqli_error($mysqli) . "<br>$sql<br>");
                while ($row = mysqli_fetch_array($res)) {
                    $count++;
                    $marker_uid = $row[0];
                    $marker_name = $row[1];
                    $alleles = $row[2];
                    if (isset($map_list[$marker_uid])) {
                        $count_inmap++;
                        $map_pos = $map_list[$marker_uid];
                        //echo "$marker_name $map_pos $start $stop<br>\n";
                        if (($map_pos > $start) && ($map_pos < $stop)) {
                            $count_inpos++;
                            if (preg_match("/,/", $alleles)) {
                                $allele_ary = explode(",", $alleles);
                            } else {
                                $allele_ary = explode("\t", $alleles);
                            }
                            $found = false;
                            $firstAllele = "";
                            $alleleList = "";
                            //echo "$count $pos $alleles<br>\n";
                            foreach ($allele_ary as $key => $allele) {
                                $uid = $uid_list[$key];
                                if (in_array($uid, $line_ary)) {
                                    if ($allele == "NN") {
                                        $allele = "--";
                                    } elseif ($allele == "N") {
                                        $allele = "-";
                                    } elseif ($allele == "+") {
                                        $allele = 0;
                                    } elseif ($allele = "-") {
                                        $allele = "1";
                                    }
                                    if ($alleleList == "") {
                                        $alleleList = "$allele";
                                    } else {
                                        $alleleList .= "\t$allele";
                                    }
                                    if (($allele == "-") || ($allele == "--")) {
                                    } elseif ($firstAllele == "") {
                                        $firstAllele = $allele;
                                    } elseif ($firstAllele != $allele) {
                                        $found = true;
                                    }
                                } else {
                                    echo "Error: $uid not found\n";
                                }
                            }
                            if ($found) {
                                $found_list[] = $marker_uid;
                                $poly[] = "$marker_name\t$alleleList";
                                $marker_map[] = "$marker_name\t$selected_chrom\t$map_pos";
                            }
                        }
                    }
                }
                if ($count_inmap == 0) {
                    echo "<font color=\"red\">Selected map does not contain markers for this experiment. Try another map or clear map selection.</font><br>\n";
                } else {
                    echo "$count_inmap markers within selected map.<br>\n";
                }
            } else {
                $sql = "select marker_uid, marker_name, chrom, pos, alleles from allele_bymarker_exp_ACTG where experiment_uid = $geno_exp AND $option";
                //echo "$sql<br>\n";
                $res = mysqli_query($mysqli, $sql, MYSQLI_USE_RESULT) or die(mysqli_error($mysqli) . "<br>$sql<br>");
                while ($row = mysqli_fetch_array($res)) {
                    $count++;
                    $marker_uid = $row[0];
                    $marker_name = $row[1];
                    $chrom = $row[2];
                    $map_pos = $row[3];
                    $alleles = $row[4];
                    if (preg_match("/,/", $alleles)) {
                        $allele_ary = explode(",", $alleles);
                    } else {
                        $allele_ary = explode("\t", $alleles);
                    }
                    $found = false;
                    $firstAllele = "";
                    $alleleList = "";
                    //convert to Flapjack format
                    foreach ($allele_ary as $key => $allele) {
                        $uid = $uid_list[$key];
                        if (in_array($uid, $line_ary)) {
                            if ($allele == "NN") {
                                $allele = "--";
                            } elseif ($allele == "N") {
                                $allele = "-";
                            } elseif ($allele == "+") {
                                $allele = 0;
                            } elseif ($allele = "-") {
                                $allele = "1";
                            }

                            if ($alleleList == "") {
                                $alleleList = "$allele";
                            } else {
                                $alleleList .= "\t$allele";
                            }
                            if (($allele == "-") || ($allele == "--")) {
                            } elseif ($firstAllele == "") {
                                $firstAllele = $allele;
                            } elseif ($firstAllele != $allele) {
                                $found = true;
                                //echo "$firstAllele $allele<br>\n";
                            }
                        }
                    }
                    if ($found) {
                        $found_list[] = $marker_uid;
                        $poly[] = "$marker_name\t$alleleList";
                        $marker_map[] = "$marker_name\t$selected_chrom\t$map_pos";
                    }
                }
                echo "$count markers within $option<br>\n";
            }
            $countp = count($poly);
            $unique_str = chr(rand(65, 80)).chr(rand(65, 80)).chr(rand(65, 80)).chr(rand(65, 80));
            $dir = "/tmp/tht/download_" . $unique_str;
            mkdir($dir);
            $filename1 = $dir . "/markers.hapmap";
            $filename2 = $dir . "/map.tsv";
            $filename3 = $dir . "/markersT.tsv";
            $filename4 = $dir . "/proc_error.txt";
            $filename5 = "genotype.flapjack.tsv";

            echo "$countp markers with polymorphisms<br><br>\n";
            $filename = "/tmp/tht/download_" . $unique_str . ".zip";
            ?>
            <input type="button" value="Save marker selection" onclick="save()"><br>
            <input type="button" value="Download file of results"
                onclick="javascript:window.open('<?php echo $filename ?>');">
                Flapjack format (ACTG, missing = "-", INS = 0, DEL = 1<br><br>
            <?php

            if ($countp < 1000) {
                foreach ($poly as $line) {
                    echo "$line<br>\n";
                }
            }
            $h1 = fopen($filename1, "w") or die("Error: cannot create $filename1\n");
            $h2 = fopen($filename2, "w") or die("Error: cannot create $filename2\n");
            //fwrite($h, "marker,chromosome,position");
            foreach ($uid_list as $key => $uid) {
                if (in_array($uid, $line_ary)) {
                    fwrite($h1, "\t$name_list[$key]");
                }
            }
            fwrite($h1, "\n");
            foreach ($poly as $key => $line) {
                fwrite($h1, "$line\n");
                fwrite($h2, "$marker_map[$key]\n");
            }
            fclose($h1);
            fclose($h2);
            $_SESSION['selected_markers'] = $found_list;
            $cmd = "Rscript --vanilla " . $config['root_dir'] . "genotyping/transpose.R $filename1 $filename3 > /dev/null 2> $filename4";
            //echo "$cmd<br>\n";
            exec($cmd);
            if (file_exists("$filename3")) {
                $h1 = fopen($filename3, "r");
                $h2 = fopen("$dir/$filename5", "w");
                fwrite($h2, "\t");
                while ($line=fgets($h1)) {
                    fwrite($h2, $line);
                }
                fclose($h1);
                fclose($h2);
                exec("cd $dir; /usr/bin/zip -r $filename $filename5 map.tsv proc_error.txt");
            } else {
                echo "Error: no output file from R script<br>\n";
            }
            if (file_exists("$filename4")) {
                $h = fopen($filename4, "r");
                while ($line=fgets($h)) {
                    echo "$line<br>\n";
                }
            }
        }
    }
    private function displayAll()
    {
        global $config;
        include $config['root_dir'].'theme/admin_header2.php';
        ?>
        <script type="text/javascript" src="genotyping/pop-poly01.js"></script>
        <div id="step2">
        <?php
      
        $this->findMarkers();
        echo "</div></div>";
        include $config['root_dir'].'theme/footer.php';
    }

    private function displayChrom()
    {
        global $config;
        $this->findMarkers();
    }
}
