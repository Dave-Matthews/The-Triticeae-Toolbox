<?php
/**
 * Use a PHP class to implement the "Imputation" feature
 *
 * @author  Clay Birkett <clb343@cornell.edu>
 * @license http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link    http://triticeaetoolbox.org/wheat/downloads/downloads.php
 **/
function selectTarExec()
{
    global $mysqli;
    global $config;
    $chrom = $_GET['chr'];
    if (isset($_SESSION['geno_exps'])) {
        $geno_exps = $_SESSION['geno_exps'];
        $geno_exp = $geno_exps[0];
    } else {
        echo "Error: experiment not selected\n";
    }

    //look in target directory
    $sql = "select trial_code from experiments where experiment_uid = $geno_exp";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    if ($row = mysqli_fetch_array($res)) {
        $trial_code = $row[0];
        $target1 = $config['root_dir'] . "raw/genotype/imputed/" . $geno_exp;
        $target2 = $config['base_url'] . "raw/genotype/imputed/" . $geno_exp;
        $target_file = $target1 . "/$chrom" . "_genotype_imputed.vcf.gz";
        $target_url = $target2 .  "/$chrom" . "_genotype_imputed.vcf.gz";
        $target2_file = $target2 . "/$chrom" . "_histo.jpg";
        if (file_exists($target_file)) {
            ?><input type="button" value="Download" onclick="javascript:filter_vcf('<?php echo $geno_exp ?>','<?php echo $chrom ?>');"/><br>
            <?php
            echo "<img src=\"$target2_file\">";
        } else {
            //echo "$target_file not found<br>\n";
            echo "<button onclick=createVCF()>Run Beagle</button>";
        }
    }
}

/** remove from vcf entries where AR2 is less than filter setting **/
function filterVCF()
{
    global $config;

    $geno_exp = $_GET['geno_exp'];
    $chrom = $_GET['chrom'];
    $filter = floatval($_GET['filter']);

    $name = "$chrom" . "_genotype_imputed.vcf";
    header('Cache-Control:');
    header('Pragma:');
    header('Content-type: text/plain');
    header("Content-Disposition: attachment; filename=$name");
    header('Pragma: no-cache');
    header('Expires: 0');

    $target1 = $config['root_dir'] . "raw/genotype/imputed/" . $geno_exp;
    $target2 = $config['base_url'] . "raw/genotype/imputed/" . $geno_exp;
    $target_file = $target1 . "/$chrom" . "_genotype_imputed.vcf.gz";
    $target_url = $target2 .  "/$chrom" . "_genotype_imputed.vcf.gz";
    if (file_exists($target_file)) {
        $handle = gzopen($target_file, "r");
        while (!gzeof($handle)) {
            $line = gzgets($handle);
            if (preg_match("/^#/", $line)) {
                echo $line;
            } elseif (preg_match("/AR2=([^;]+)/", $line, $match)) {
                $ar = floatval($match[1]);
                if ($ar > $filter) {
                    echo $line;
                }
            }
        }
    } else {
        echo "$target_file does not exists\n";
    }
}

/** used to create VCF file from genotype experiment selection for TASSEL **/
/** does not sort by position and does not work for large exeriments **/
function createVcfDownload($unique_str, $min_maf, $max_missing)
{
    global $config;
    global $mysqli;
    global $tmpdir;
    $max_missing = 50;
    $min_maf = 5;
    include_once $config['root_dir'].'downloads/marker_filter.php';
    $selected_map = $_SESSION['selected_map'];
    if (isset($_SESSION['geno_exps'])) {
        $geno_exp = $_SESSION['geno_exps'];
        $geno_exp = $geno_exp[0];
        $sql = "select trial_code from experiments where experiment_uid = $geno_exp";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        $row = mysqli_fetch_array($res);
        $trial_code = $row[0];
    } else {
        die("Error: no genotype experiment selected");
    }
    if (isset($_SESSION['selected_map'])) {
        $selected_map = $_SESSION['selected_map'];
        $sql = "select mapset_name from mapset where mapset_uid = $selected_map";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>" . $sql);
        if ($row = mysqli_fetch_array($res)) {
            $mapset_name = "mapset:$row[0]";
        } else {
            echo "Error: map not defined\n";
        }
        $sql = "select markers.marker_uid, CAST(mim.start_position as UNSIGNED), mim.chromosome
          from markers, markers_in_maps as mim, map
          where mim.marker_uid = markers.marker_uid
          AND mim.map_uid = map.map_uid
          AND map.mapset_uid = $selected_map";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>" . $sql);
        while ($row = mysqli_fetch_array($res)) {
            $marker_uid = $row[0];
            $mappos = $row[1];
            $mapchr = $row[2];
            $marker_list_mapped[$marker_uid] = $mappos;
            $marker_list_chr[$marker_uid] = $mapchr;
        }
    } else {
        $mapset_name = "NoMapSelected";
    }

    $filename1 = "genotype.vcf";
    $fh1 = fopen("$tmpdir/download_$unique_str/$filename1", "w");

    //get filtered markers
    $sql = "SELECT marker_uid, maf, missing, total from allele_frequencies where experiment_uid = $geno_exp";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . $sql);
    while ($row = mysqli_fetch_row($res)) {
        $marker_uid = $row[0];
        $maf = $row[1];
        $miss = $row[2];
        $total = $row[3];
        if ($total > 0) {
            $miss_per = 100 * ($miss / $total);
        } else {
            $miss_per = 100;
        }
        if (($miss_per > $max_missing) or ($maf < $min_maf)) {
        } else {
            $marker_lookup[$marker_uid] = 1;
        }
    }

    //get header
    $sql = "select line_index from allele_bymarker_expidx where experiment_uid = $geno_exp";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row = mysqli_fetch_array($res)) {
        $uid_list = json_decode($row[0], true);
    }
    foreach ($uid_list as $uid) {
        $sql = "select line_record_name from line_records where line_record_uid = $uid";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        if ($row = mysqli_fetch_array($res)) {
            $name[] = $row[0];
        } else {
            $name[] = "unknown";
        }
    }
    $outputheader .= implode("\t", $name);

    fwrite($fh1, "##fileformat=VCFv4.2\n");
    fwrite($fh1, "##reference=triticeaetoolbox.org,GenotypeTrial:$trial_code\n");
    fwrite($fh1, "##source=$mapset_name\n");
    fwrite($fh1, "##FORMAT=<ID=GT,Number=1,Type=String,Description=\"Genotype\">\n");
    fwrite($fh1, "#CHROM\tPOS\tID\tREF\tALT\tQUAL\tFILTER\tINFO\tFORMAT\t");
    fwrite($fh1, "$outputheader\n");

    $lookup = array(
        '-1' => '1/1',  //BB
        '0' => '0/1',   //AB
        '1' => '0/0',   //AA
        'NA' => './.',   //--
        '' => './.'
    );
    $posUnk = 0;
    $sql = "select markers.marker_uid, markers.marker_name, A_allele, B_allele, alleles
        from allele_bymarker_exp_101, markers 
        where allele_bymarker_exp_101.marker_uid = markers.marker_uid
        AND experiment_uid = $geno_exp";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql<br>");
    while ($row = mysqli_fetch_array($res)) {
        $marker_uid = $row[0];
        $marker_name = $row[1];
        $ref = $row[2];
        $alt = $row[3];
        $alleles = $row[4];
        if (($ref == "") || ($alt == "")) {
            continue;
        }
        if (isset($marker_list_mapped[$marker_uid])) {
            $chromosome = $marker_list_chr[$marker_uid];
            $pos = $marker_list_mapped[$marker_uid];
        } else {
            $chromosome = 0;
            $pos = $posUnk;
            $posUnk = $posUnk + 10;
        }
        if (isset($marker_lookup[$marker_uid])) {
            $allele_ary = explode(",", $alleles);
            $allele_ary2 = array();
            foreach ($allele_ary as $i => $allele) {
                $allele_ary2[] = $lookup[$allele];
            }
            $allele_str = implode("\t", $allele_ary2);
            fwrite($fh1, "$chromosome\t$pos\t$marker_name\t$ref\t$alt\t.\tPASS\t.\tGT\t$allele_str\n");
        }
    }
    fclose($fh1);
}

/** used to create VCF file from genotype experiment selection for Beagle **/
function createVcfBeagle()
{
    //need ref_line to rename dupicates
    //need ref_markers to filter out markers that are not in reference genotype
    global $config;
    global $mysqli;
    global $tmpdir;
    $max_missing = 50;
    $min_maf = 5;
    $unique_str = $_GET['unq'];
    include_once $config['root_dir'].'downloads/marker_filter.php';
    ini_set("auto_detect_line_endings", true);
    if (isset($_GET['mm'])) {
        $max_missing = $_GET['mm'];
    }
    if (isset($_GET['mmaf'])) {
        $min_maf = $_GET['mmaf'];
    }
    global $ref_genotype;
    $sql = "select experiment_uid from experiments where trial_code = \"$ref_genotype\"";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . $sql);
    if ($row = mysqli_fetch_array($res)) {
        $ref_exp_uid = $row[0];
    } else {
        die("Error: could not find reference experiment\n");
    }
    $sql = "select line_name_index from allele_bymarker_expidx where experiment_uid = $ref_exp_uid";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . $sql);
    if ($row = mysqli_fetch_array($res)) {
        $tmp = json_decode($row[0], true);
    } else {
        die("Error: could not find reference experiment\n");
    }
    foreach ($tmp as $line_name) {
        $ref_line[$line_name] = 1;
    }
    $chr = $_GET['chr'];
    //$f1 = "raw/genotype/WEC_filtered_SNPs.vcf";
    //$ref_marker = typeVcfGetMarkerRef($chr, $f1);
    if (isset($_SESSION['geno_exps']) || isset($_SESSION['selected_lines'])) {
        //$unique_str = chr(rand(65, 90)) .chr(rand(65, 90)) .chr(rand(65, 90)) .chr(rand(65, 90));
        $filename = "download_" . $unique_str;
        umask(0);
        //echo "mkdir $tmpdir/$filename\n";
        mkdir("$tmpdir/$filename");
        $filename = "selection_parameters.txt";
        $h = fopen("$tmpdir/download_$unique_str/$filename", "w");
        fwrite($h, "Minimum MAF = $min_maf\n");
        fwrite($h, "Maximum Missing = $max_missing\n");
        $filename1 = "genotype.vcf";
        $h1 = fopen("$tmpdir/download_$unique_str/$filename1", "w");
        $filename2 = "reference_cont.vcf";
        $h2 = fopen("$tmpdir/download_$unique_str/$filename2", "w");
        $h3 = fopen("$tmpdir/download_$unique_str/target.log", "w");
    }
    $filename = "$tmpdir/download_$unique_str";
    if (isset($_SESSION['geno_exps'])) {
        $geno_exp = $_SESSION['geno_exps'];
        $geno_exp = $geno_exp[0];
        $sql = "select trial_code from experiments where experiment_uid = $geno_exp";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . $sql);
        if ($row = mysqli_fetch_array($res)) {
            $trial_code = $row[0];
            fwrite($h, "Target Trial Code = $trial_code\n");
        }
        $chr = $_GET['chr'];
        $f1 = "raw/genotype/WEC_filtered_SNPs.vcf";
        typeVcfExpMarkersDownload($geno_exp, $ref_line, $chr, $min_maf, $max_missing, $h1, $h2);
        fclose($h);
        fclose($h1);
        fclose($h2);
   
        $infile2 = "$tmpdir/download_$unique_str/reference_cont_phased";   //refernece phased
        $infile3 = "$tmpdir/download_$unique_str/genotype.vcf";            //target file
        $infile4 = "$tmpdir/download_$unique_str/genotype_phased";         //target file phased
        $infile6 = "$tmpdir/download_$unique_str/genotype_conform";
        $logfile2 = "$tmpdir/download_$unique_str/process_error2.txt";
        $logfile3 = "$tmpdir/download_$unique_str/process_error3.txt";
        $cmd = "java -jar /usr/local/bin/beagle.r1399.jar gt=$infile3 out=$infile4 > /dev/null 2> $logfile2";
        $cmd = "java -jar /usr/local/bin/beagle.27Jul16.86a.jar gt=$infile3 out=$infile4 window=5000 overlap=500 > /dev/null 2> $logfile2";
        //echo "Creating a phased genotype\n";
        //exec($cmd);

        $infile2 = "/var/www/html/t3/wheat/raw/genotype/" . $chr . "_WEC_var_phased.vcf.gz";
        $infile4 .= ".vcf.gz";

        $infile6 .= ".vcf.gz";
    } elseif (isset($_SESSION['selected_lines'])) {
        $lines = $_SESSION['selected_lines'];
        $f1 = "raw/genotype/WEC_filtered_SNPs.vcf";
        typeVcfMarkersDownload($lines, $chr, $min_maf, $max_missing, $h);
        typeVcfReferenceDownload($chr, $f1, $h2);
        fclose($h);
        ?>
        <input type="button" value="Download Zip file of results" onclick="javascript:window.open('<?php echo "$filename"; ?>');" />
        <?php
    } else {
        echo "Error: Please select a set of lines or genotype experiment\n";
    }
}

function runBeagle($impute)
{
    global $mysqli;
    global $config;
    global $tmpdir;
    global $target_base;
    if (isset($_SESSION['geno_exps'])) {
        $geno_exps = $_SESSION['geno_exps'];
        $geno_exp = $geno_exps[0];
    } else {
        echo "Error: experiment not selected\n";
    }
    $chr = $_GET['chr'];
    $unique_str = $_GET['unq'];

    $sql = "select experiment_uid, trial_code from experiments where experiment_uid = $geno_exp";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    if ($row = mysqli_fetch_array($res)) {
        $uid = $row[0];
        $trial_code = $row[1];
        $target_dir = $target_base . "/$uid";
    }

    $filename = "$tmpdir/download_$unique_str";
    $file_url = "$tmpdir/download_$unique_str/$chr" . "_genotype_imputed.vcf.gz";
    $infile1 = "$tmpdir/download_$unique_str/reference_cont.vcf";      //reference filtered by chromosome
    $infile2 = "$tmpdir/download_$unique_str/reference_cont_phased";   //refernece filtered by chromosome, phased
    $infile3 = "$tmpdir/download_$unique_str/genotype.vcf";            //file to be imputed at higher marker density
    $infile4 = "$tmpdir/download_$unique_str/genotype_phased";         //file to be imputed, phased
    $infile6 = "$tmpdir/download_$unique_str/genotype_conform";
    $infile7 = "$tmpdir/download_$unique_str/genotype_phased.vcf.gz";
    $outfile1 = "$tmpdir/download_$unique_str/$chr" . "_genotype_imputed";
    $outfile2 = "$tmpdir/download_$unique_str/genotype_imputed_conform";
    $outfile3 = "$tmpdir/download_$unique_str/histo.txt";
    $outfile4 = "$tmpdir/download_$unique_str/$chr" . "_histo.jpg";
    $logfile1 = "$tmpdir/download_$unique_str/process_error1.txt";
    $logfile2 = "$tmpdir/download_$unique_str/process_error2.txt";
    $logfile3 = "$tmpdir/download_$unique_str/process_error3.txt";
    $logfile4 = "$tmpdir/download_$unique_str/process_error4.txt";
    $logfile5 = "$tmpdir/download_$unique_str/process_error5.txt";
    $target2 = $config['base_url'] . "raw/genotype/imputed/" . $geno_exp .  "/$chr" . "_histo.jpg";
    $infile2 = "/var/www/html/t3/wheat/raw/genotype/" . $chr . "_WEC_var_phased.vcf.gz";

    $cmd = "java -jar /usr/local/bin/beagle.r1399.jar gt=$infile3 out=$infile4 > /dev/null 2> $logfile2";
    $cmd = "java -jar /usr/local/bin/beagle.27Jul16.86a.jar gt=$infile3 out=$infile4 window=5000 overlap=500 > /dev/null 2> $logfile2";
    echo "<br>Creating a phased genotype.<br>$cmd\n";
    exec($cmd);

    $infile4 .= ".vcf.gz";
    $cmd = "java -jar /usr/local/bin/conform-gt.24May16.cee.jar gt=$infile4 ref=$infile2 chrom=$chr out=$infile6 > /dev/null 2> $logfile3";
    echo "<br>Running conform-gt $cmd\n";
    exec($cmd);

    $infile6 .= ".vcf.gz";
    $cmd = "java -jar /usr/local/bin/beagle.r1399.jar gt=$infile6 ref=$infile2 out=$outfile1 nthreads=20 window=5000 overlap = 500 > /dev/null 2> $logfile4";
    $cmd = "java -jar /usr/local/bin/beagle.27Jul16.86a.jar gt=$infile6 ref=$infile2 out=$outfile1 nthreads=20 window=5000 overlap=500 > /dev/null 2> $logfile4";
    echo "<br>Running beagle.r1399 to impute target<br>\n$cmd\n";
    exec($cmd);
    $outfile1 .= ".vcf.gz";
    if (!file_exists($outfile1)) {
        echo "Error: no output file from beagle $outfile1\n";
        return;
    }
    $countz = 0;
    $handle = gzopen($outfile1, "r");
    $x = 'x<-c(';
    while (!gzeof($handle)) {
        $line = gzgets($handle, 4096);
        if (preg_match("/AR2=([^;]+)/", $line, $match)) {
            $ar = $match[1];
            if ($ar < 0.01) {
                $countz++;
            } else {
                $ar = number_format($ar, 2);
                $x .= "$ar,";
            }
        }
    }
    gzclose($handle);
    $x = trim($x, ",");
    $x .= ")";
    $fh = fopen($outfile3, "w");
    $title = "main = \"Histogram of Imputed Genotypes, AR2=0 for $countz\"";
    $xlab = "xlab = \"Allelic R-Squared\"";
    fwrite($fh, "$x\n");
    fwrite($fh, "jpeg(\"$outfile4\", width=500, height=500)\n");
    fwrite($fh, "hist(x,$title,$xlab)\n");
    fclose($fh);
    exec("cat $outfile3 | R --vanilla > /dev/null 2> $tmpdir/histo.log");

    $cmd = "cp $outfile1 $target_dir";
    exec($cmd);
    $cmd = "cp $infile1 $target_dir";
    exec($cmd);
    $cmd = "cp $outfile4 $target_dir";
    exec($cmd);
    ?>
    <input type="button" value="Open working directory of results" onclick="javascript:window.open('<?php echo "$filename"; ?>');" />
    <input type="button" value="Download VCF results file" onclick="javascript:window.open('<?php echo "$file_url"; ?>');" />
    <?php
    echo "<br><img src=\"$target2\">";
}

function runBeagleVerify($impute)
{
    global $mysqli;
    global $config;
    global $tmpdir;
    global $target_base;
    if (isset($_SESSION['geno_exps'])) {
        $geno_exps = $_SESSION['geno_exps'];
        $geno_exp = $geno_exps[0];
    } else {
        echo "Error: experiment not selected\n";
    }
    $chr = $_GET['chr'];
    $unique_str = $_GET['unq'];

    $sql = "select experiment_uid, trial_code from experiments where experiment_uid = $geno_exp";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    if ($row = mysqli_fetch_array($res)) {
        $uid = $row[0];
        $trial_code = $row[1];
        $target_dir = $target_base . "/$uid";
    }

    $filename = "$tmpdir/download_$unique_str";
    $file_url = "$tmpdir/download_$unique_str/$chr" . "_genotype_imputed.vcf.gz";
    $infile1 = "$tmpdir/download_$unique_str/reference_cont.vcf";      //reference filtered by chromosome
    $infile2 = "$tmpdir/download_$unique_str/reference_cont_phased";   //refernece filtered by chromosome, phased
    $infile3 = "$tmpdir/download_$unique_str/genotype.vcf";            //file to be imputed at higher marker density
    $infile4 = "$tmpdir/download_$unique_str/genotype_phased";         //file to be imputed, phased
    $infile6 = "$tmpdir/download_$unique_str/genotype_conform";
    $infile7 = "$tmpdir/download_$unique_str/genotype_phased.vcf.gz";
    $outfile1 = "$tmpdir/download_$unique_str/$chr" . "_genotype_imputed";
    $outfile2 = "$tmpdir/download_$unique_str/genotype_imputed_conform";
    $outfile3 = "$tmpdir/download_$unique_str/histo.txt";
    $outfile4 = "$tmpdir/download_$unique_str/$chr" . "_histo.jpg";
    $logfile1 = "$tmpdir/download_$unique_str/process_error1.txt";
    $logfile2 = "$tmpdir/download_$unique_str/process_error2.txt";
    $logfile3 = "$tmpdir/download_$unique_str/process_error3.txt";
    $logfile4 = "$tmpdir/download_$unique_str/process_error4.txt";
    $logfile5 = "$tmpdir/download_$unique_str/process_error5.txt";
    $target2 = $config['base_url'] . "raw/genotype/imputed/" . $geno_exp .  "/$chr" . "_histo.jpg";
    $infile2 = "/var/www/html/t3/wheat/raw/genotype/" . $chr . "_WEC_var_phased.vcf.gz";

    $cmd = "java -jar /usr/local/bin/beagle.r1399.jar gt=$infile3 out=$infile4 > /dev/null 2> $logfile2";
    $cmd = "java -jar /usr/local/bin/beagle.27Jul16.86a.jar gt=$infile3 out=$infile4 window=5000 overlap=500 > /dev/null 2> $logfile2";
    echo "<br>Creating a phased genotype.<br>$cmd\n";
    exec($cmd);

    $infile4 .= ".vcf.gz";
    $cmd = "java -jar /usr/local/bin/conform-gt.24May16.cee.jar gt=$infile4 ref=$infile2 chrom=$chr out=$infile6 > /dev/null 2> $logfile3";
    echo "<br>Running conform-gt $cmd\n";
    exec($cmd);

    $infile6v = $infile6 . "v" . ".vcf.gz";
    $infile6 .= ".vcf.gz";

    if (isset($_SESSION['verify'])) {
        $index = $_SESSION['verify'];
    } else {
        die("Error: must select which marker to verify\n");
    }
    $count = 1;
    $lines = gzfile($infile6);
    $fh = gzopen($infile6v, "w");
    foreach ($lines as $line) {
        if (preg_match("/^#/", $line)) {
            gzwrite($fh, $line);
        } else {
            $allele_ary = explode("\t", $line);
            $contig = $allele_ary[2];
            if ($count == $index) {
                echo "found marker $index $contig\n";
                $_SESSION['verifyContig'] = $contig;
                $allele_ary2 = array();
                foreach ($allele_ary as $i => $allele) {
                    if ($i > 9) {
                        $allele_ary2[] = ".|.";
                    } else {
                        $allele_ary2[] = $allele;
                    }
                }
                $allele_str = implode("\t", $allele_ary2);
                //gzwrite($fh, "$allele_ary[0]\t$allele_ary[1]\t$allele_ary[2]\t$allele_ary[3]\t$allele_ary[4]\t.\tPASS\t.\tGT\t$allele_str\n");
                gzwrite($fh, "$allele_str\n");
            } else {
                //echo "not found $count $index\n";
                gzwrite($fh, $line);
            }
            $count++;
        }
    }
    fclose($fh);

    $cmd = "java -jar /usr/local/bin/beagle.r1399.jar gt=$infile6v ref=$infile2 out=$outfile1 nthreads=20 impute=false > /dev/null 2> $logfile4";
    $cmd = "java -jar /usr/local/bin/beagle.27Jul16.86a.jar gt=$infile6v ref=$infile2 out=$outfile1 nthreads=20 impute=false > /dev/null 2> $logfile4";
    echo "<br>Running beagle.r1399 to impute target<br>\n$cmd\n";
    exec($cmd);
    $outfile1 .= ".vcf.gz";
    if (!file_exists($outfile1)) {
        echo "Error: no output file from beagle $outfile1\n";
        return;
    }

    $cmd = "cp $outfile1 $target_dir";
    exec($cmd);
    $cmd = "cp $infile1 $target_dir";
    exec($cmd);
}

function runBeagleChrom()
{
        $unique_str = $_GET['unq'];
        $infile1 = "/tmp/tht/download_$unique_str/reference_chro.vcf";      //reference filtered by chromosome
        $infile2 = "/tmp/tht/download_$unique_str/reference_chro_phased";   //refernece filtered by chromosome, phased
        $infile3 = "/tmp/tht/download_$unique_str/genotype.vcf";            //file to be imputed at higher marker density
        $infile4 = "/tmp/tht/download_$unique_str/genotype_phased";         //file to be imputed, phased
        $infile6 = "/tmp/tht/download_$unique_str/genotype_conform";
        $outfile = "/tmp/tht/download_$unique_str/genotype_imputed";
        $logfile1 = "/tmp/tht/download_$unique_str/process_error1.txt";
        $logfile2 = "/tmp/tht/download_$unique_str/process_error2.txt";
        $logfile3 = "/tmp/tht/download_$unique_str/process_error3.txt";
        $logfile4 = "/tmp/tht/download_$unique_str/process_error4.txt";

        //$cmd = "java -jar /usr/local/bin/beagle.r1399.jar gt=$infile1 out=$infile2 > /dev/null 2> $logfile1";
        //echo "<br>Creating a phased refernece.<br>$cmd\n";
        //exec($cmd);

        $cmd = "java -jar /usr/local/bin/beagle.r1399.jar gt=$infile3 out=$infile4 > /dev/null 2> $logfile2";
        $cmd = "java -jar /usr/local/bin/beagle.27Jul16.86a.jar gt=$infile3 out=$infile4 > /dev/null 2> $logfile2";
        echo "<br>Creating a phased genotype.<br>\n";
        exec($cmd);

        $infile2 .= ".vcf.gz";
        $infile4 .= ".vcf.gz";
        $cmd = "java -jar /usr/local/bin/conform-gt.24May16.cee.jar gt=$infile4 ref=$infile2 out=$infile6 > /dev/null 2> $logfile3";
        echo "<br>Running conform-gt $cmd\n";
        exec($cmd);

        $infile6 .= ".vcf.gz";
        $cmd = "java -jar /usr/local/bin/beagle.r1399.jar gt=$infile2 ref=$infile6 out=$outfile > /dev/null 2> $logfile4";
        $cmd = "java -jar /usr/local/bin/beagle.27Jul16.86a.jar gt=$infile2 ref=$infile6 out=$outfile > /dev/null 2> $logfile4";
        echo "<br>Imputing target data.<br>$cmd\n";
        exec($cmd);
}

