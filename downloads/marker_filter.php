<?php
/**
 * Library used for marker and line filtering
 *
 * PHP version 5.3
 *
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/downloads/marker_filter.php
 */

/**
 * calculate allele frequencies using allele_frequencies table
 *
 * @param array $lines         selected lines
 * @param float $min_maf       minimum marker allele frequence
 * @param float $max_missing   maximum missing markers
 * @param float $max_miss_line maximum missing lines
 *
 * @return $markers_filtered
 */
function calculate_db($lines, $min_maf, $max_missing, $max_miss_line)
{
    global $mysqli;
    $tmp = count($lines);
    if ($tmp == 0) {
        return;
    }
    $selectedlines = implode(",", $lines);

    if (isset($_SESSION['geno_exps'])) {
        $count = $_SESSION['geno_exps_cnt'];
        return $count;
        $experiment_uid = $_SESSION['geno_exps'];
        $experiment_uid = $experiment_uid[0];
        $sql = "SELECT marker_uid, maf, missing, total from allele_frequencies where experiment_uid = $experiment_uid";
        $res = mysql_query($sql) or die(mysql_error() . $sql);
        while ($row = mysql_fetch_row($res)) {
            $marker_uid = $row[0];
            $maf = $row[1];
            $miss = $row[2];
            $total = $row[3];
            $miss_per = 100 * ($miss / $total);
            if (($miss_per > $max_missing) or ($maf < $min_maf)) {
            } else {
                $markers_filtered[$marker_uid] = 1;
            }
        }
    } else {
        //get genotype experiments that correspond with the Datasets (BP and year)
        //selected for the experiments
        $sql_exp = "SELECT DISTINCT e.experiment_uid AS exp_uid
        FROM experiments e, experiment_types as et, line_records as lr, tht_base as tb
        WHERE e.experiment_type_uid = et.experiment_type_uid
        AND lr.line_record_uid = tb.line_record_uid
        AND e.experiment_uid = tb.experiment_uid
        AND lr.line_record_uid in ($selectedlines)
        AND et.experiment_type_name = 'genotype'";
        $res = mysqli_query($mysqli, $sql_exp) or die(mysqli_error($mysqli) . "<br>" . $sql_exp);
        if (mysqli_num_rows($res)>0) {
            while ($row = mysqli_fetch_array($res)) {
                $exp[] = $row["exp_uid"];
            }
            $exp = implode(',', $exp);
        }

        $sql_mstat = "SELECT af.marker_uid as marker, SUM(af.aa_cnt) as sumaa,
         SUM(af.missing)as summis, SUM(af.bb_cnt) as sumbb,
         SUM(af.total) as total, SUM(af.ab_cnt) AS sumab
         FROM allele_frequencies AS af
         WHERE af.experiment_uid in ($exp)
         group by af.marker_uid";

        $res = mysqli_query($mysqli, $sql_mstat) or die(mysqli_error($mysqli));
        $num_mark = mysqli_num_rows($res);
        $num_maf = $num_miss = $num_removed = 0;

        while ($row = mysqli_fetch_array($res)) {
            $marker_uid = $row["marker"];
            $maf1 = (2*$row["sumaa"]+$row["sumab"])/(2*$row["total"]);
            $maf2 = ($row["sumab"]+2*$row["sumbb"])/(2*$row["total"]);
            $maf = round(100*min($maf1, $maf2), 1);
            $miss = round(100*$row["summis"]/$row["total"], 1);
            if (($miss > $max_missing) or ($maf < $min_maf)) {
            } else {
                $markers_filtered[] = $marker_uid;
            }
        }
    }
    $count = count($markers_filtered);
    return $count;
}

    /**
     * calculate allele frequence and missing data using selected lines
     *
     * @param array  $lines         selected lines
     * @param floats $min_maf       minimum marker allele frequency
     * @param floats $max_missing   maximum missing markers
     * @param floats $max_miss_line maximum missing lines
     *
     * @return $markers_filtered, $lines_filtered
    */
function calculate_af($lines, $min_maf, $max_missing, $max_miss_line)
{
    global $mysqli;
    if (isset($_SESSION['clicked_buttons'])) {
        $tmp = count($_SESSION['clicked_buttons']);
        $saved_session = $saved_session . ", $tmp markers";
        $markers = $_SESSION['clicked_buttons'];
        $marker_str = implode(',', $markers);
    } else {
        $markers_filtered = array();
        $markers = array();
        $marker_str = "";
    }

    //create list of selected markers
    foreach ($markers as $key => $marker_uid) {
        $selected_markers[$marker_uid] = 1;
        //echo "selected $marker_uid\n";
    }

    //get location information for markers
    $sql = "select marker_uid, marker_name from allele_byline_idx order by marker_uid";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>" . $sql);
    $i=0;
    while ($row = mysqli_fetch_array($res)) {
        $uid = $row[0];
        $marker_list[$i] = $row[0];
        $marker_list_name[$i] = $row[1];
        $marker_list_loc[$uid] = $i;
        $i++;
    }

    //get location information for lines
    $sql = "select line_record_uid, line_record_name from line_records";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>" . $sql);
    while ($row = mysqli_fetch_array($res)) {
        $uid = $row[0];
        $line_list_name[$uid] = $row[1];
    }
   
    //calculate allele frequence and missing
    $marker_misscnt = array();
    foreach ($lines as $line_record_uid) {
        $sql = "select alleles from allele_byline where line_record_uid = $line_record_uid";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>" . $sql);
        if ($row = mysqli_fetch_array($res)) {
            $alleles = $row[0];
            $outarray = explode(',', $alleles);
            $i=0;
            foreach ($outarray as $allele) {
                if ($allele=='AA') {
                    $marker_aacnt[$i]++;
                } elseif (($allele=='AB') or ($allele=='BA')) {
                    $marker_abcnt[$i]++;
                } elseif ($allele=='BB') {
                    $marker_bbcnt[$i]++;
                //need to check for both conditions otherwise the output will include markers with missing data
                } elseif (($allele=='--') || ($allele=='')) {
                    $marker_misscnt[$i]++;
                } else {
                    echo "illegal genotype value $allele for marker $marker_list_name[$i]<br>";
                }
                $i++;
            }
        } else {
            foreach ($marker_list as $i => $value) {
                $marker_misscnt[$i]++;
            }
        }
    }
    $num_mark = 0;
    $num_maf = $num_miss = $num_removed = 0;
    foreach ($marker_list as $i => $marker_uid) {
        //if there are selected markers then only calculate allele frequencies for these
        if (isset($_SESSION['clicked_buttons']) && !isset($selected_markers[$marker_uid])) {
            continue;
        }
        $total_af = $marker_aacnt[$i] + $marker_abcnt[$i] + $marker_bbcnt[$i];
        $total = $total_af + $marker_misscnt[$i];
        if ($total_af > 0) {
            $maf1 = (2 * $marker_aacnt[$i] + $marker_abcnt[$i]) / (2 * $total_af);
            $maf2 = ($marker_abcnt[$i] + 2 * $marker_bbcnt[$i]) / (2 * $total_af);
            $maf = round(100 * min($maf1, $maf2), 1);
            $miss = 100 * $marker_misscnt[$i]/$total;
            if ($maf < $min_maf) {
                $num_maf++;
            }
            if ($miss > $max_missing) {
                //echo "$total_af $total $miss<br>\n";
                $num_miss++;
            }
            if (($miss > $max_missing) or ($maf < $min_maf)) {
                $num_removed++;
            } else {
                $markers_filtered[] = $marker_uid;
            }
            $num_mark++;
        }
    }
    //echo "<br>num of markers with data = $num_mark<br>\n";
    $_SESSION['filtered_markers'] = $markers_filtered;
    $count = count($markers_filtered);
    if ($count == 0) {
          //if none of markers meet maf requirements then we can not filter lines by missing data
          $lines_filtered = $lines;
    } else {
        //calculate missing from each line
        foreach ($lines as $line_record_uid) {
            $sql = "select alleles from allele_byline where line_record_uid = $line_record_uid";
            $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>" . $sql);
            if ($row = mysqli_fetch_array($res)) {
                $alleles = $row[0];
                $outarray = explode(',', $alleles);
                $line_misscnt[$line_record_uid] = 0;
                foreach ($markers_filtered as $marker_uid) {
                    $loc = $marker_list_loc[$marker_uid];
                    $allele = $outarray[$loc];
                    if ($allele=='--') {
                        $line_misscnt[$line_record_uid]++;
                    }
                }
            } else {
                $line_misscnt[$line_record_uid] = $count;
            }
        }
        $lines_removed = 0;
        $lines_removed_name = "";
        $num_line = 0;
        foreach ($lines as $line_record_uid) {
            $miss = 100*$line_misscnt[$line_record_uid]/$count;
            if ($miss > $max_miss_line) {
                $lines_removed++;
                if ($lines_removed_name == "") {
                    $lines_removed_name = $line_list_name[$line_record_uid];
                } else {
                    $lines_removed_name = $lines_removed_name . ", $line_list_name[$line_record_uid]";
                }
            } else {
                $lines_filtered[] = $line_record_uid;
            }
            $num_line++;
        }
    }
    $_SESSION['filtered_lines'] = $lines_filtered;
    if (strlen($lines_removed_name) > 75) {
         $comm = substr($lines_removed_name, 0, 75) . " ...";
    } else {
         $comm = $lines_removed_name;
    }
    $count2 = count($lines_filtered);

    ?>
    <table>
    <tr><td>Removed by filtering <a onclick="filterDesc( <?php echo ($min_maf) ?>, <?php echo ($max_missing) ?>, <?php echo ($max_miss_line) ?>)">(description)</a><td>Remaining
    <tr><td><?php echo ($num_maf) ?><i> markers have a minor allele frequency (MAF) less than </i><b><?php echo ($min_maf) ?></b><i>%
    <br><?php echo ($num_miss) ?><i> markers are missing more than </i><b><?php echo ($max_missing) ?></b><i>% of data
    <br><b><?php echo ($num_removed) ?></b><i> markers removed</i>
    <td><b><?php echo ("$count") ?></b><i> markers</i>
    <tr><td><?php
    if ($lines_removed == 1) {
        echo ("</i><b>$lines_removed") ?></b><i> line is missing more than </i><b><?php echo ($max_miss_line) ?></b><i>% of data</b></i>
        <?php
    } else {
        echo ("</i><b>$lines_removed") ?></b><i> lines are missing more than </i><b><?php echo ($max_miss_line) ?></b><i>% of data </b></i>
        <?php
    }
    if ($lines_removed_name != "") {
        ?><br>(<a onclick="linesRemoved('<?php echo ($lines_removed_name) ?>')"><?php echo ($comm) ?></a>)
        <?php
    }
    echo "<td><b>$count2</b><i> lines</a>";
    echo ("</table>");
    if ($count == 0) {
        echo "<font color=red>Warning: Please select new filter paramaters</font>";
    }
}

    /**
     * calculate allele frequence and missing data using selected lines and allele_frequencies table
     *
     * @param array  $lines         selected lines
     * @param floats $min_maf       minimum marker allele frequency
     * @param floats $max_missing   maximum missing markers
     * @param floats $max_miss_line maximum missing lines
     *
     * @return $markers_filtered, $lines_filtered
    */
function calculate_afe($lines, $min_maf, $max_missing, $max_miss_line)
{
    global $mysqli;
    if (isset($_SESSION['geno_exps'])) {
        $experiment_uid = $_SESSION['geno_exps'];
        $experiment_uid = $experiment_uid[0];
    } else {
        echo "Error: should select genotype experiment befor download\n";
    }

    $num_maf = 0;
    $num_miss = 0;
    $num_mark = 0;
    $num_removed = 0;
    $sql = "SELECT marker_uid, maf, missing, total from allele_frequencies where experiment_uid = $experiment_uid";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . $sql);
    while ($row = mysqli_fetch_row($res)) {
        $marker_uid = $row[0];
        $maf = $row[1];
        $miss = $row[2];
        $total = $row[3];
        $miss_per = 100 * ($miss / $total);
        if ($maf < $min_maf) {
            $num_maf++;
        }
        if ($miss_per > $max_missing) {
            $num_miss++;
        }
        if (($miss_per > $max_missing) or ($maf < $min_maf)) {
            $num_removed++;
        } else {
            $markers_filtered[$marker_uid] = 1;
        }
        $num_mark++;
    }
    $_SESSION['filtered_markers'] = $markers_filtered;
    $count = count($markers_filtered);
    ?>
    <table>
    <tr><td><a onclick="filterDesc( <?php echo ($min_maf) ?>, <?php echo ($max_miss_line) ?>, <?php echo ($max_miss_line) ?>)">Removed by filtering</a><td>Remaining
    <tr><td><?php echo ($num_maf) ?><i> markers have a minor allele frequency (MAF) less than </i><b><?php echo ($min_maf) ?></b><i>%
    <br><?php echo ($num_miss) ?><i> markers are missing more than </i><b><?php echo ($max_missing) ?></b><i>% of data
    <br><b><?php echo ($num_removed) ?></b><i> markers removed</i>
    <td><b><?php echo ("$count") ?></b><i> markers</i>
    <?php
    echo ("</table>");
    if ($count == 0) {
        echo "<font color=red>Warning: Please select new filter paramaters</font>";
    }
}

/**
 * find lines that are common between phenotype and genotype experiment
 *
 */
function findCommonLines($lines)
{
    global $mysqli;
    $count_selected = count($lines);
    $selectedlines = implode(",", $lines);
    if (isset($_SESSION['selected_trials'])) {
        $exp_array = $_SESSION['selected_trials'];
        $e_uid = $exp_array[0];
    } else {
        die("Error: must select phenotype trial\n");
    }
    if (isset($_SESSION['phenotype'])) {
        $phenotype_ary = $_SESSION['selected_traits'];
        $p_uid = $phenotype_ary[0];
    } else {
        die("Error: must select trait\n");
    }
        
    $sql = "SELECT DISTINCT lr.line_record_uid as id, lr.line_record_name as name
        FROM tht_base as tb, phenotype_data as pd, phenotypes as p, line_records as lr
        WHERE pd.tht_base_uid = tb.tht_base_uid
        AND p.phenotype_uid = pd.phenotype_uid
        AND lr.line_record_uid = tb.line_record_uid
        AND pd.phenotype_uid = $p_uid
        AND tb.experiment_uid = $e_uid
        AND lr.line_record_uid IN ($selectedlines)";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    $l_count = 0;
    while ($row = mysqli_fetch_array($res)) {
        $l_count++;
        $uid = $row[0];
        $lines_filtered[] = $uid;
    }
    $lines_removed = $count_selected - $l_count;
    if ($lines_removed == 1) {
        echo "$lines_removed line removed because it is not in genotype experiment, ";
    } elseif ($lines_removed > 0) {
        echo "$lines_removed lines removed because they are not in genotype experiment, ";
    }
    echo "using $l_count lines\n";

    $_SESSION['filtered_lines'] = $lines_filtered;
}

    /**
     * build genotype data files for tassel and rrBLUP using genotype experiment
     *
     * @param unknown_type $lines   lines
     * @param unknown_type $markers markers
     * @param integer      $dtype   file format
     * @param file         $h       file handle
     *
     * @return null
     */
function type4BuildMarkersDownload($geno_exp, $min_maf, $max_missing, $dtype, $h)
{
    $output = '';
    $outputheader = '';
    $delimiter ="\t";

    //if map is genetic then multiply by 100 because tassel requires integer position
    if (isset($_SESSION['selected_map'])) {
        $selected_map = $_SESSION['selected_map'];
        $sql = "select map_type from mapset where mapset_uid = $selected_map";
        $res = mysql_query($sql) or die(mysql_error() . $sql);
        if ($row = mysql_fetch_row($res)) {
            $map_type = $row[0];
        } else {
            $map_type = "";
        }
    } else {
        $selected_map = "";
    }

    //calculate number of lines for selected experiment
    $sql = "select max(total) from allele_frequencies where experiment_uid = $geno_exp";
    $res = mysql_query($sql) or die(mysql_error() . $sql);
    if ($row = mysql_fetch_row($res)) {
        $measured_lines = $row[0];
        $max_missing_count = round($max_missing * ($measured_lines / 100));
    }

    $sql = "SELECT marker_uid, maf, missing,total from allele_frequencies where experiment_uid = $geno_exp";
    $res = mysql_query($sql) or die(mysql_error() . $sql);
    while ($row = mysql_fetch_row($res)) {
        $marker_uid = $row[0];
        $maf = $row[1];
        $miss = $row[2];
        if (($miss > $max_missing_count) or ($maf < $min_maf)) {
        } else {
            $marker_lookup[$marker_uid] = 1;
        }
        $num_mark++;
    }

    //order the markers by map location
    //tassel v5 needs markers sorted when position is not unique
    if ($selected_map == "") {
        $marker_list_mapped = array();
        $marker_list_chr = array();
    } else {
        if ($map_type == "Physical") {
          $sql = "select markers.marker_uid, CAST(mim.start_position as UNSIGNED), mim.chromosome from markers, markers_in_maps as mim, map
          where mim.marker_uid = markers.marker_uid
          AND mim.map_uid = map.map_uid
          AND map.mapset_uid = $selected_map";
        } else {
          $sql = "select markers.marker_uid, CAST(1000*mim.start_position as UNSIGNED), mim.chromosome from markers, markers_in_maps as mim, map
          where mim.marker_uid = markers.marker_uid
          AND mim.map_uid = map.map_uid
          AND map.mapset_uid = $selected_map";
        }
        $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
        while ($row = mysql_fetch_array($res)) {
               $marker_uid = $row[0];
               $pos = $row[1];
               $chr = $row[2];
               $marker_list_mapped[$marker_uid] = $pos;
               $marker_list_chr[$marker_uid] = $chr;
        }
    }

    //generate an array of selected markers and add map position if available
    $sql = "select markers.marker_uid, markers.marker_name, A_allele, B_allele from markers, marker_types, allele_bymarker_exp_101
    where markers.marker_uid = allele_bymarker_exp_101.marker_uid
    and markers.marker_type_uid = marker_types.marker_type_uid
    and experiment_uid = $geno_exp";
    $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
    while ($row = mysql_fetch_array($res)) {
        $marker_uid = $row[0];
        $marker_name = $row[1];
        $allele = $row[2] . "/" . $row[3];
        $marker_list_name[$marker_uid] = $marker_name;
        $marker_list_allele[$marker_uid] = $allele;
    }

    //get header, tassel requires all fields even if they are empty
    $sql = "select line_index from allele_bymarker_expidx where experiment_uid = $geno_exp";
    $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
    if ($row = mysql_fetch_array($res)) {
        $uid_list = json_decode($row[0], true);
    } else {
        die("<font color=red>Error - genotype experiment should be selected before download</font>");
    }
    foreach ($uid_list as $uid) {
        $sql = "select line_record_name from line_records where line_record_uid = $uid";
        $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
        if ($row = mysql_fetch_array($res)) {
            $name[] = $row[0];
        } else {
            $name[] = "unknown";
        }
    }
    $name_list = implode("'\t'", $name);
    if ($dtype == "qtlminer") {
        $outputheader = "rs\talleles\tchrom\tpos\t";
        $outputheader .= "'" . implode("'\t'", $name) . "'";
    } else {
        $outputheader = "rs#\talleles\tchrom\tpos\tstrand\tassembly#\tcenter\tprotLSID\tassayLSID\tpanelLSID\tQCcode\t";
        $outputheader .= implode("\t", $name);
    }

    $nelem = count($line_names);
    fwrite($h, "$outputheader\n");

    $pos_index = 0;
    if ($dtype == "qtlminer") {
        $sql = "select marker_uid, marker_name, chrom, pos, alleles from allele_bymarker_exp_101 where experiment_uid = $geno_exp order by BINARY chrom, pos, BINARY marker_name";
    } else {
        $sql = "select marker_uid, marker_name, chrom, pos, alleles from allele_bymarker_exp_ACTG where experiment_uid = $geno_exp order by BINARY chrom, pos";
    }
    $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
    while ($row = mysql_fetch_array($res)) {
        $marker_id = $row[0];
        $marker_name = $row[1];
        $chrom = $row[2];
        $pos = $row[3];
        $alleles = $row[4];
        $allele = $marker_list_allele[$marker_id];
        if (isset($marker_list_mapped[$marker_id])) {
            $chrom = $marker_list_chr[$marker_id];
            $pos = $marker_list_mapped[$marker_id];
        }
        if (isset($marker_lookup[$marker_id])) {
            if (empty($chrom)) {
                    $chrom = 'UNK';
                    $pos = $pos_index;
                    $pos_index += 10;
            }
            if ($dtype == "qtlminer") {
                    fwrite($h, "$marker_name\t$allele\t$chrom\t$pos");
            } else {
                    fwrite($h, "$marker_name\t$allele\t$chrom\t$pos\t\t\t\t\t\t\t");
            }
            $alleles = preg_replace("/,/", "\t", $alleles);
            fwrite($h, "\t$alleles\n");
        }
    }
    $count = count($unique);
}

function typeVcfMarkersDownload($lines, $chr, $min_maf, $max_missing, $h)
{
    global $mysqli;
    $outputheader = "";
}

function typeVcfReferenceDownload($chr, $f1, $h1)
{
    global $config;
    ini_set("auto_detect_line_endings", true);

    //read in chromosome map
    $count = 0;
    $chr = strtolower($chr);
    $file = $config['root_dir'] . $f1;
    $ref_file = fopen($file, "r");
    $pattern = "/_$chr/";
    if ($ref_file == false) {
        echo "Error: reference not found $f1 $file\n";
    } else {
        echo "Reading reference $f1<br>\n";
        $line = fgets($ref_file);
        fwrite($h1, $line);
        $line = fgets($ref_file);
        fwrite($h1, $line);
        while ($line_ary = fgetcsv($ref_file, 0, "\t")) {
            $contig = $line_ary[0];
            if (preg_match("/(\d+)_([0-9a-z]+)/", $contig, $match)) {
                $contig_new = strtoupper($match[2]) . "_" . $match[1];
            } else {
                echo "Error: $contig bad name<br>\n";
            }
            $pos = $line_ary[1];
            $id = $contig . "_" . $pos;
            if (preg_match($pattern, $line_ary[0])) {
                $count++;
                //$line = implode("\t", $line_ary);
                foreach ($line_ary as $key => $value) {
                    if ($key == 0) {
                        $line = $contig_new;
                    } elseif ($key == 1) {
                        $line .= "\t" . $pos;
                    } else {
                        $line .= "\t" . $value;
                    }
                }
                fwrite($h1, "$line\n");
            }
        }
        echo "$count with map to $h2<br>\n";
    }
}
/** 
  * used to test accuracy of imputation
  */
function typeVcfExpMarkersDownloadVerify($geno_exp, $ref_line, $chr, $min_maf, $max_missing, $fh1, $fh2, $index)
{
    global $mysqli;
    global $config;
    $outputheader = "";
    $empty = "";

    //get header for VCF
    //rename lines that duplicate those in reference
    $count = 0;
    $sql = "select line_index from allele_bymarker_expidx where experiment_uid = $geno_exp";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    if ($row = mysqli_fetch_array($res)) {
        $uid_list = json_decode($row[0], true);
    } else {
        die("<font color=red>Error - genotype experiment should be selected before download</font>");
    }
    foreach ($uid_list as $uid) {
        $sql = "select line_record_name from line_records where line_record_uid = $uid";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br> 2" . $sql);
        if ($row = mysqli_fetch_array($res)) {
            $name[] = $row[0];
        } else {
            $name[] = "unknown";
        }
    }
    foreach ($name as $line_name) {
        $count++;
        if ($outputheader != "") {
            $outputheader .= "\t";
            $empty .= "\t.";
        } else {
            $empty = ".";
        }
        if (isset($ref_line[$line_name])) {
            $outputheader .= "$line_name" . "_duplicate";
            fwrite($fh2, "renaming conflict with reference $line_name\n");
        } elseif (isset($unique_name[$line_name])) {
            $outputheader .= "$line_name" . "_duplicate";
            fwrite($fh2, "renaming duplicate within target $line_name\n");
        } else {
            $outputheader .= "$line_name";
        }
        $unique_name[$line_name] = 1;
    }

    $infile2 = "/var/www/html/t3/wheat/raw/genotype/" . $chr . "_WEC_var_phased.vcf.gz";
    $lines = gzfile($infile2);
    foreach ($lines as $line) {
        if (preg_match("/^#/", $line)) {
            continue;
        } elseif (preg_match("/[A-Za-z]/", $line)) {
            $line_ary = explode("\t", $line);
            $id = $line_ary[2];
            $ref = $line_ary[3];
            $alt = $line_ary[4];
            $ref_list[$id][0] = $ref;
            $ref_list[$id][1] = $alt;
        }
    }

    //get synonyms for T3 markers so they will match the referenece
    $sql = "select marker1_uid, marker1_name, contig
           from marker_report_reference, allele_frequencies
           where marker_report_reference.marker1_uid = allele_frequencies.marker_uid
           and experiment_uid = $geno_exp";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row = mysqli_fetch_array($res)) {
        $marker_uid = $row[0];
        $marker_name = $row[1];
        $contig = $row[2];
        $contig_list[$contig]= $marker_name;
    }

    $count = 0;
    fwrite($fh1, "##fileformat=VCFv4.2\n");
    fwrite($fh1, "#CHROM\tPOS\tID\tREF\tALT\tQUAL\tFILTER\tINFO\tFORMAT\t");
    fwrite($fh1, "$outputheader\n");

    //get genotypes from selected experiment
    $count = 0;
    $count_skip = 0;
    $count_mapped = 0;
    $sql = "select marker1_uid, marker1_name, contig, marker_report_reference.chrom, marker_report_reference.chrom_pos, A_allele, B_allele, alleles, scaffold, contig_strand, file_strand
        from marker_report_reference, allele_bymarker_exp_101, markers 
        where marker_report_reference.marker1_uid = allele_bymarker_exp_101.marker_uid
        and marker_report_reference.marker1_uid = markers.marker_uid
        and experiment_uid = $geno_exp
        and marker_report_reference.scaffold = \"$chr\"
        and scaffold is not NULL
        order by chrom_pos";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql<br>");
    while ($row = mysqli_fetch_array($res)) {
        $marker_id = $row[0];
        $marker_name = $row[1];
        $contig = $row[2];
        $chrom = $row[3];
        $pos = $row[4];
        $ref = $row[5];
        $alt = $row[6];
        $alleles = $row[7];
        $scaffold = $row[8];
        $contig_strand = $row[9];
        $file_strand = $row[10];
        $index1 = $scaffold . "_" . $pos;
        if (isset($ref_list[$contig])) {
            $ref1 = $ref_list[$contig][0];
            $alt1 = $ref_list[$contig][1];
        } else {
            //echo "$contig not defined<br>\n";
            continue;
        }
        $count++;
        //compliment
        //T3 format AA=1, BB = -1 AB = 0
        //VCF format 0 = ref, 1 = alt
        $trans = array("A" => "T", "C" => "G", "T" => "A", "G" => "C");
        $ref3 = $ref_list[$contig][0];
        $alt3 = $ref_list[$contig][1];
        $ref2 = strtr($ref3, $trans);
        $alt2 = strtr($alt3, $trans);
        $lookup = array(
            '-1' => '1/1',  //BB
            '0' => '0/1',   //AB
            '1' => '0/0',   //AA
            'NA' => './.',   //--
            '' => './.'
            );
        if (($ref == $ref3) && ($alt == $alt3)) {
        } elseif (($ref == $ref2) && ($alt == $alt2)) {
            $ref = $ref3;
            $alt = $alt3;
        } elseif (($ref == $alt3) && ($alt == $ref3)) {
        } elseif (($ref == $alt2) && ($alt == $ref2)) {
        }
        fwrite($fh2, "$marker_name $contig $ref $alt $ref1 $alt1\n");
        if (isset($unique[$index1])) {
            fwrite($fh2, "skip $marker_name $index1 duplicates $unique[$index1]\n");
        } else {
            $af = array();
            $unique[$index1] = $marker_name;
            $allele_ary = explode(",", $alleles);
            $allele_ary2 = array();
            if ($count == $index) {
                $_SESSION['verifyContig'] = $contig;
                foreach ($allele_ary as $i => $allele) {
                    $allele_ary2[] = ".|.";
                }
            } else {
                foreach ($allele_ary as $i => $allele) {
                    $allele_ary2[] = $lookup[$allele];
                }
            }
            $allele_str = implode("\t", $allele_ary2);
            fwrite($fh1, "$scaffold\t$pos\t$contig\t$ref\t$alt\t.\tPASS\t.\tGT\t$allele_str\n");
        }
    }
    fwrite($fh2, "$count markers written to target genotype file\n");
}

/**
     * build genotype data files in VCF format using genotype experiment (does not work for large GBS)
     *
     * @param integer $geno_exp  genotype experiment
     * @param real  $min_maf     minimum marker allele frequency
     * @param real  $max_missing max missing markers
     * @param file  $h           file handle
     *
     * @return null
     */
function typeVcfExpMarkersDownload($geno_exp, $ref_line, $chr, $min_maf, $max_missing, $fh1, $fh2)
{
    global $mysqli;
    global $config;
    $outputheader = "";
    $empty = "";

    //get header for VCF
    //rename lines that duplicate those in reference
    $count = 0;
    $sql = "select line_index from allele_bymarker_expidx where experiment_uid = $geno_exp";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    if ($row = mysqli_fetch_array($res)) {
        $uid_list = json_decode($row[0], true);
    } else {
        die("<font color=red>Error - genotype experiment should be selected before download</font>");
    }
    foreach ($uid_list as $uid) {
        $sql = "select line_record_name from line_records where line_record_uid = $uid";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br> 2" . $sql);
        if ($row = mysqli_fetch_array($res)) {
            $name[] = $row[0];
        } else {
            $name[] = "unknown";
        }
    }
    foreach ($name as $line_name) {
        $count++;
        if ($outputheader != "") {
            $outputheader .= "\t";
            $empty .= "\t.";
        } else {
            $empty = ".";
        }
        if (isset($ref_line[$line_name])) {
            $outputheader .= "$line_name" . "_duplicate";
            fwrite($fh2, "renaming conflict with reference $line_name\n");
        } elseif (isset($unique_name[$line_name])) {
            $outputheader .= "$line_name" . "_duplicate";
            fwrite($fh2, "renaming duplicate within target $line_name\n");
        } else {
            $outputheader .= "$line_name";
        }
        $unique_name[$line_name] = 1;
    }

    $infile2 = "/var/www/html/t3/wheat/raw/genotype/" . $chr . "_WEC_var_phased.vcf.gz";
    $lines = gzfile($infile2);
    foreach ($lines as $line) {
        if (preg_match("/^#/", $line)) {
            continue;
        } elseif (preg_match("/[A-Za-z]/", $line)) {
            $line_ary = explode("\t", $line);
            $id = $line_ary[2];
            $ref = $line_ary[3];
            $alt = $line_ary[4];
            $ref_list[$id][0] = $ref;
            $ref_list[$id][1] = $alt;
        }
    }
  
    //get synonyms for T3 markers so they will match the referenece
    $sql = "select marker1_uid, marker1_name, contig
           from marker_report_reference, allele_frequencies
           where marker_report_reference.marker1_uid = allele_frequencies.marker_uid
           and experiment_uid = $geno_exp";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row = mysqli_fetch_array($res)) {
        $marker_uid = $row[0];
        $marker_name = $row[1];
        $contig = $row[2];
        $contig_list[$contig]= $marker_name;
    }

    $count = 0;
    fwrite($fh1, "##fileformat=VCFv4.2\n");
    fwrite($fh1, "#CHROM\tPOS\tID\tREF\tALT\tQUAL\tFILTER\tINFO\tFORMAT\t");
    fwrite($fh1, "$outputheader\n");

    //get genotypes from selected experiment
    $count = 0;
    $count_skip = 0;
    $count_mapped = 0;
    $sql = "select marker1_uid, marker1_name, contig, marker_report_reference.chrom, marker_report_reference.chrom_pos, A_allele, B_allele, alleles, scaffold, contig_strand, file_strand
        from marker_report_reference, allele_bymarker_exp_101, markers 
        where marker_report_reference.marker1_uid = allele_bymarker_exp_101.marker_uid
        and marker_report_reference.marker1_uid = markers.marker_uid
        and experiment_uid = $geno_exp
        and marker_report_reference.scaffold = \"$chr\"
        and scaffold is not NULL
        order by chrom_pos";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql<br>");
    while ($row = mysqli_fetch_array($res)) {
        $marker_id = $row[0];
        $marker_name = $row[1];
        $contig = $row[2];
        $chrom = $row[3];
        $pos = $row[4];
        $ref = $row[5];
        $alt = $row[6];
        $alleles = $row[7];
        $scaffold = $row[8];
        $contig_strand = $row[9];
        $file_strand = $row[10];
        $index1 = $scaffold . "_" . $pos;
        if (!isset($ref_list[$contig])) {
            //echo "$contig not defined<br>\n";
            continue;
        }
        $count++;
        //compliment
        $trans = array("A" => "T", "C" => "G", "T" => "A", "G" => "C");
        $ref3 = $ref_list[$contig][0];
        $alt3 = $ref_list[$contig][1];
        $ref2 = strtr($ref3, $trans);
        $alt2 = strtr($alt3, $trans);
        $lookup = array(
            '-1' => '1/1',  //BB
            '0' => '0/1',   //AB
            '1' => '0/0',   //AA
            'NA' => './.',   //--
            '' => './.'
            );
        if (($ref == $ref3) && ($alt == $alt3)) {
        } elseif (($ref == $ref2) && ($alt == $alt2)) {
            $ref = $ref3;
            $alt = $alt3;
        } elseif (($ref == $alt3) && ($alt == $ref3)) {
        } elseif (($ref == $alt2) && ($alt == $ref2)) {
        } else {
            fwrite($fh2, "Error: $contig $ref $alt $ref2 $alt2 $ref3 $alt3\n");
        }
        if (isset($unique[$index1])) {
            fwrite($fh2, "skip $marker_name $index1 duplicates $unique[$index1]\n");
        } else {
            $af = array();
            $unique[$index1] = $marker_name;
            $allele_ary = explode(",", $alleles);
            $allele_ary2 = array();
            foreach ($allele_ary as $i => $allele) {
                $allele_ary2[] = $lookup[$allele];
            }
            $allele_str = implode("\t", $allele_ary2);
            fwrite($fh1, "$scaffold\t$pos\t$contig\t$ref\t$alt\t.\tPASS\t.\tGT\t$allele_str\n");
        }
    }
    fwrite($fh2, "$count markers written to target genotype file\n");
}

function typeVcfGetMarkerRef($chr, $f1)
{
    global $config;
    ini_set("auto_detect_line_endings", true);

    //must have min and max position for each contig for Beagle to work
    $count = 0;
    $chr = strtolower($chr);
    $file = $config['root_dir'] . $f1;
    $ref_file = fopen($file, "r");
    $pattern = "/_$chr/";
    if ($ref_file == false) {
        echo "Error: reference not found $f1 $file\n";
    } else {
        echo "Reading reference $f1<br>\n";
        $line = fgets($ref_file);
        $line = fgets($ref_file);
        while ($line_ary = fgetcsv($ref_file, 0, "\t")) {
            $contig = $line_ary[0];
            $pos = $line_ary[1];
            if (preg_match("/(\d+)_([0-9a-z]+)/", $contig, $match)) {
                $contig_new = strtoupper($match[2]) . "_" . $match[1];
            } else {
                echo "Error: $contig bad name<br>\n";
            }
            if ($contig_new != $contig_pre) {
                $ref_marker[$contig_pre][0] = $min;
                $ref_marker[$contig_pre][1] = $max;
                $min = 999999999;
                $max = 0;
                $contig_pre = $contig_new;
            }
            if (preg_match($pattern, $line_ary[0])) {
                $count++;
                if ($pos > $max) {
                    $max = $pos;
                }
                if ($pos < $min) {
                    $min = $pos;
                }
            }
        }
    }
    echo "$count from $file<br>\n";
    return $ref_marker;
}
