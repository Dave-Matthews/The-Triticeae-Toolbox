<?php
/**
 * load allele_frequencies table
 *
 * PHP version 5
 *
 * @author Clay Birkett <claybirkett@gmail.com>
*/

if ($argc != 4) {
    die("Usage: load_gbs_frequencies.php database input_file trial_code");
}
$db_name = $argv[1];
$file = $argv[2];
$trialcode = $argv[3];
echo "using database = $db_name\n";
echo "using file = $file\n";
echo "using trial_code = $trialcode\n";
$db_user = '';
$db_pass = '';
$db_host = 'localhost';
$linkID = mysql_connect($db_host, $db_user, $db_pass);
mysql_select_db($db_name, $linkID);

$sql = "select experiment_uid from experiments where trial_code = \"$trialcode\"";
$res = mysql_query($sql) or die(mysql_error());
if ($row = mysql_fetch_array($res)) {
  $experiment_uid = $row[0];
} else {
  echo "Error: $sql\n";
  die();
}

if (($fh = fopen($file, "r")) == false) {
  echo("can not open file $file\n");
  exit();
}

$header = fgets($fh);
$header_ary = explode("\t", $header);
$count = 0;
$line_index = "";

$sql = "select marker_uid, marker_name from markers, marker_types where 
    markers.marker_type_uid = marker_types.marker_type_uid
    and marker_type_name = \"GBS\"";
$res = mysql_query($sql) or die(mysql_error());
while ($row = mysql_fetch_array($res)) {
    $marker_uid = $row[0];
    $marker_name = $row[1];
    $marker_list[$marker_name] = $marker_uid;
}

$count = 0;
while (!feof($fh)) {
    $line = fgets($fh);
    $lineA = str_getcsv($line, "\t");
    $marker = $lineA[0];
    $missing = $lineA[1];
    $aa_cnt = $lineA[2];
    $ab_cnt = $lineA[3];
    $bb_cnt = $lineA[4];
    $total = $lineA[5];
    $maf = $lineA[6];
    if (isset($marker_list[$marker])) {
        $marker_uid = $marker_list[$marker];
    } else {
        echo "$marker not found\n";
        continue;
    }
    $count++;
    if (isset($marker_list[$marker])) {
        $marker_uid = $marker_list[$marker];
        $sql = "insert into allele_frequencies (marker_uid, experiment_uid, missing, aa_cnt, ab_cnt, bb_cnt, total, maf)
            values ($marker_uid, $experiment_uid, $missing, $aa_cnt, $ab_cnt, $bb_cnt, $total, $maf)";
        //echo "$sql\n";
        $res = mysql_query($sql) or die(mysql_error() . $sql);
        if (($count % 1000) == 0) {
            echo "finished $count\n";
        }
    } else {
        echo "$marker not found\n";
    }
}
echo "$count lines from $file\n";
fclose($fh);
