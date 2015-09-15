<?php

ini_set('memory_limit', '4G');
$file_index = "/var/www/html/t3/wheat/viroblast/db/nucleotide/wheat-index.csv";
$file_out = "blast.results.txt";

$db_name = "T3wheat";
$db_user = '';
$db_pass = '';
$db_host = 'localhost';
$linkID = mysqli_connect($db_host, $db_user, $db_pass);
mysqli_select_db($linkID, $db_name);
$unique_list = array();

$marker_uid_list = array();
$marker_name_list = array();
$max_markers = 0;
$sql = "select marker_uid, marker_name, sequence from markers";
$res = mysqli_query($linkID, $sql) or die(mysqli_error($linkID));
while ($row = mysqli_fetch_array($res)) {
    $uid = $row[0];
    $name = $row[1];
    $seq = $row[2];
    $pattern = "/([A-Z]*)\[([A-Z])\/([A-Z])\]([A-Z]*)/";
    if (preg_match($pattern, $seq, $matches)) {
        $pos = strlen($matches[1]);
        $marker_pos_list[$name] = $pos;
    } else {
        echo "Error: bad seqeunce $seq\n";
    }
    $marker_uid_list[$name] = $uid;
    $max_markers++;
}
echo "$max_markers markers\n";

//get length of sequence
$count = 0;
echo "reading size and type from $file_index\n";
$fh = fopen($file_index, "r") or die("Unable to open file $blastfile\n");
while (!feof($fh)) {
        $match = fgetcsv($fh);
        $count++;
        $name = $match[0];
        $size = $match[1];
        $type = $match[2];
        $queryList[$name] = $size;
        $queryListType[$name] = $type;
}
echo "found $count markers in $file_index\n";
fclose($fh);
$fh2 = fopen($file_out, "w") or die("Unable to open file $outfile2\n");
fwrite($fh2, "Name,Marker_type,Synonym,Synonym_type\n");

function typeBlastParse($file_blast)
{
    global $queryList;
    global $queryListType;
    global $fh2;
    global $file_index;
    global $marker_uid_list;
    global $marker_pos_list;
    global $linkID;
    global $unique_list;
    $fh = fopen($file_blast, "r") or die("Unable to open file $blastout\n");
    $count = 0;
    while (!feof($fh)) {
        $line = fgets($fh);
        if (preg_match("/^\# /", $line)) {
        } elseif (preg_match("/([^\s]+)\s+([^\s]+)\s+([^\s]+)\s+([^\s]+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/", $line, $match)) {
            $name = $match[1];
            if ($match[9] > $match[10]) {
                $name2 = $match[2] . ":" . $match[10] . "-" . $match[9];
            } else {
                $name2 = $match[2] . ":" . $match[9] . "-" . $match[10];
            }
            $perc = $match[3];
            $length = $match[4];
            $mismatch = $match[5];
            if (isset($marker_uid_list[$name])) {
                $queryUid = $marker_uid_list[$name];
            } else {
                echo "Error: bad name $name\n";
                die();
            }
            if (isset($marker_pos_list[$name])) {
                $queryPos = $marker_pos_list[$name];
            } else {
                echo "Error: no position\n";
                $queryPos = "unknown";
            }
            if (isset($queryList[$name])) {
                $querySize = $queryList[$name];
                $minSize = .95 * $querySize;
                $queryType = $queryListType[$name];
            } else {
                echo "Error: $name not defined in $file_index\n$line\n";
                continue;
            }
            if ($match[1] != $match[2]) {
                //echo "$name $name2 $length $querySize $subjSize\n";
                $good = 0;
                if (($match[3] == "100.00") && ($length == $querySize)) {
                    fwrite($fh2, "$name,$queryType,$name2,$match[3],$queryPos,$length,all bases in marker match\n");
                    $matched[$name] = 1;
                    $count++;
                    $good = 1;
                } elseif (($match[3] > 98) && ($length > $minSize)) {
                    fwrite($fh2, "$name,$queryType,$name2,$match[3],$queryPos,$length\n");
                    $matched[$name] = 1;
                    $count++;
                    $good = 1;
                } elseif (($match[5] < 2) && ($match[6] < 1) && ($length > $minSize)) {
                    fwrite($fh2, "$name,$queryType,$name2,$match[3],$queryPos,$length\n");
                    $matched[$name] = 1;
                    $count++;
                    $good = 1;
                }
                if ($good == 1) {
                    $index = $queryUid . "_" . $name2;
                    if (isset($unique_list[$index])) {
                        $sql = "update marker_report_reference set perc = $match[3], length = $length where marker1_uid = $queryUid and contig = \"$name2\"";
                    } else {
                        $sql = "insert into marker_report_reference (marker1_uid, marker1_name, contig, perc, length) values ($queryUid, \"$name\", \"$name2\", $match[3], $length)";
                        $unique_list[$index] = 1;
                    }
                    //mysqli_query($linkID, $sql) or die(mysqli_error($linkID) . "\n$sql");
                }
            }
        }
    }
    echo "found $count matches\n";
}

if ($handle = opendir('.')) {
    while (false !== ($entry = readdir($handle))) {
        if (preg_match("/out[1,2]/", $entry)) {
            echo "$entry\n";
            typeBlastParse($entry);
        }
    }
}
