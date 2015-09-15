<?php

$db_name = "T3wheat";
echo "using database = $db_name\n";
$db_user = '';
$db_pass = '';
$db_host = 'localhost';
$linkID = mysqli_connect($db_host, $db_user, $db_pass);
mysqli_select_db($linkID, $db_name);
ini_set('memory_limit', '2G');

$file = "blast.results.txt";
if (($reader = fopen($file, "r")) == false) {
    die("can not open $file\n");
}
$header = fgets($reader);
//* get markers
$sql = "select marker_uid, marker_name from markers";
$res = mysqli_query($linkID, $sql) or die(mysql_error());
while ($row = mysqli_fetch_array($res)) {
    $marker_uid = $row[0];
    $marker_name = $row[1];
    $marker_list[$marker_name] = $marker_uid;
}

$type = 11;
$count = 0;
$count_update = 0;
$count_unq_mrk = 0;
while (!feof($reader)) {
    $line = fgetcsv($reader);
    $num = count($line);
    $marker1 = $line[0];
    $marker2 = $line[2];
    $perc = $line[3];
    $length = $line[4];
    if (preg_match("/([A-Z0-9]+):(\d+)-(\d+)/", $marker2, $match)) {
        $chrom = $match[1];
        $pos1 = $match[2];
        $pos2 = $match[3];
    } else {
        echo "Error: $line\n $marker2\n";
    }
    if (isset($marker_list[$marker1])) {
        $marker_uid = $marker_list[$marker1];
    } else {
        echo "invalid marker $marker1 $line\n";
        continue;
    }
    if ($num < 6) {
        $perfect = 0;
        $desc = "$line[3]% over $line[4] bases";
    } else {
        $perfect = 1;
        $desc = "$line[3]% over $line[4] bases, $line[5]";
    }
    $index2 = $marker1 . $marker2;
    if (isset($unique_list[$marker1])) {
        if ($perc > $unique_list[$marker1]) {
            $unique_list[$marker1] = $perc;
            $unique_list2[$marker1] = $perfect;
            //$map[$marker1] = "$marker1\t$pos1\t$pos2\t$chrom";
            $map[$marker1] = "$marker1\t$pos1\t$chrom";
            $count_update++;
        }
    } else {
        $unique_list[$marker1] = $perc;
        $unique_list2[$index2] = $perfect;
        //$map[$marker1] = "$marker1\t$pos1\t$pos2\t$chrom";
        $map[$marker1] = "$marker1\t$pos1\t$chrom";
        $count++;
    }
}
echo "$count unique from $file\n";
echo "$count_update update from $file\n";

$file = "iwgs_map.txt";
$fh = fopen($file, "w") or die("Can not open file\n");
//fwrite($fh, "Marker\tStart_pos\tEnd_pos\tChrom\n");
fwrite($fh, "Marker\tStart_pos\tChrom\n");
foreach ($map as $line) {
    fwrite($fh, "$line\n");
}
fclose($fh);
