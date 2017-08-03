<?php
/**
 * load allele_frequencies table
 *
 * PHP version 5
 *
 * @author Clay Birkett <clb343@cornell.edu>
 * for over 100K rows it is much faster to load data infile
 * load data infile '/var/lib/mysql-files/2017_WheatCAP_af.tsv' into table allele_frequencies (marker_uid, experiment_uid, missing, aa_cnt, aa_freq, ab_cnt, ab_freq, bb_cnt, bb_freq, total, monomorphic, maf, gentrain_score, description, created_on);
 *
 * 072017 - added compatibility with indels
*/

ini_set('memory_limit', '4G');
if ($argc != 3) {
    die("Usage: allele_bymarker.php <database> <trial code>\n'");
}
$db_name = $argv[1];
$trialcode = $argv[2];
echo "using database = $db_name\n";
echo "using trial_code = $trialcode\n";
$date = date("Y-m-d");
$db_user = '';
$db_pass = '';
$db_host = 'localhost';
$linkID = mysqli_connect($db_host, $db_user, $db_pass);
mysqli_select_db($linkID, $db_name);

$file = $trialcode . "_af.tsv";
$fh = fopen($file, "w") or die("Error: can not open $file\n");

$sql = "select experiment_uid from experiments where trial_code = \"$trialcode\"";
$res = mysqli_query($linkID, $sql) or die(mysqli_error($linkID));
if ($row = mysqli_fetch_array($res)) {
    $experiment_uid = $row[0];
} else {
    echo "Error: $sql\n";
    die();
}

$count = 0;
$sql = "select marker_uid, A_allele, B_allele from markers, marker_types
    where markers.marker_type_uid = marker_types.marker_type_uid
    and marker_type_name = \"GBS\"";
$res = mysqli_query($linkID, $sql) or die(mysqli_error($linkID));
while ($row = mysqli_fetch_array($res)) {
    $marker_uid = $row[0];
    $a_alleles[$marker_uid] = $row[1];
    $b_alleles[$marker_uid] = $row[2];
    $count++;
}
echo "$count from markers table\n";

$count = 0;
$count_marker_bad = 0;
$sql = "select marker_uid, marker_name, alleles from allele_bymarker_exp_ACTG where experiment_uid = $experiment_uid";
$res = mysqli_query($linkID, $sql) or die(mysqli_error($linkID));
while ($row = mysqli_fetch_array($res)) {
    $aacnt = 0;
    $abcnt = 0;
    $bbcnt = 0;
    $misscnt = 0;
    $count++;
    $count_bad = 0;
    $marker_uid = $row[0];
    $mname = $row[1];
    if (isset($a_alleles[$marker_uid])) {
        $a_allele = $a_alleles[$marker_uid];
        $b_allele = $b_alleles[$marker_uid];
    } else {
        die("Error: $marker_uid not defined\n");
    }
    $alleles = explode(",", $row[2]);
    foreach ($alleles as $allele) {
        if ($allele == $a_allele) {
            $aacnt++;
        } elseif ($allele == "$a_allele$a_allele") {
            $aacnt++;
        } elseif ($allele == $b_allele) {
            $bbcnt++;
        } elseif ($allele == "$b_allele$b_allele") {
            $bbcnt++;
        } elseif ($allele == 'N') {
            $misscnt++;
        } elseif ($allele == 'NN') {
            $misscnt++;
        } elseif (preg_match("/[MRWSYK]/", $allele)) {
            $abcnt++;
        } elseif (($allele == "$a_allele$b_allele") || ($allele == "$b_allele$a_allele")) {
            $abcnt++;
        } elseif ($allele == "") {
        } else {
            $count_bad++;
            echo "Error: $mname $allele ab0 $ab[0] ab1 $ab[1]\n";
            continue;
        }
    }
    $total = $aacnt + $abcnt + $bbcnt;
    $total2 = $aacnt + $abcnt + $bbcnt + $misscnt;
    if ($total > 0) {
        $aafreq = round($aacnt / $total, 3);
        $bbfreq = round($bbcnt / $total, 3);
        $abfreq = round($abcnt / $total, 3);
        $maf1 = (2 * $aacnt + $abcnt) / (2 * $total);
        $maf2 = ($abcnt + 2 * $bbcnt) / (2 * $total);
        $maf = round(100 * min($maf1, $maf2), 1);
    } else {
        $aafreq = 0;
        $bbfreq = 0;
        $abfreq = 0;
        $maf = 0;
        $count_marker_bad++;
    }
    if (($aacnt == $total) or ($abcnt == $total) or ($bbcnt == $total)) {
        $mono = "Y";
    } else {
        $mono = "N";
    }
    //echo "$mname\t$aacnt $bbcnt $abcnt $misscnt, $aafreq $bbfreq $abfreq $total $total2 $maf\n";
    //use $total for allele freq calculation and $total for missing calculation
    /*    $sql = "UPDATE allele_frequencies
                SET missing = '$misscnt', aa_cnt = '$aacnt', aa_freq = $aafreq, ab_cnt = $abcnt, ab_freq = $abfreq, bb_cnt = $bbcnt,
                                                bb_freq = $bbfreq, total = $total2, monomorphic = '$mono', maf= $maf,
                        description = '$mname', updated_on = NOW()
                                                WHERE experiment_uid = $experiment_uid and marker_uid = $marker_uid";
        $sql = "INSERT INTO allele_frequencies (marker_uid, experiment_uid, missing, aa_cnt, aa_freq, ab_cnt, ab_freq,
                bb_cnt, bb_freq, total, monomorphic, maf, gentrain_score, description,  updated_on, created_on)
                VALUES ($marker_uid, $experiment_uid, $misscnt, $aacnt, $aafreq, $abcnt, $abfreq, $bbcnt, $bbfreq, $total2, '$mono',
                $maf, 0, '$mname', NOW(), NOW())";
    */
    fwrite($fh, "$marker_uid\t$experiment_uid\t$misscnt\t$aacnt\t$aafreq\t$abcnt\t$abfreq\t$bbcnt\t$bbfreq\t$total2\t$mono\t$maf\t0\t$mname\t$date\n");

    if (($count % 10000) == 0) {
        echo "$count bad = $count_marker_bad $mname\n";
    }
}
echo "$count from allele_bymarker_exp_ACTG\n";
