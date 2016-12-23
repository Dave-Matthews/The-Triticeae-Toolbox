<?php
/**
 * load 2D table where rows contain marker names and columns contain lines
 *
 * PHP version 5
 *
 * @author Clay Birkett <claybirkett@gmail.com>
*/

if (!isset($argv) || ($argc != 4)) {
    die("Usage: load_gbs_bymarker.php database input_file trial_code\n");
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
}

if (($fh = fopen($file, "r")) == false) {
    echo("can not open file $file\n");
    exit();
}

$header = fgets($fh);
$header_ary = explode("\t", $header);
$count = 0;
$line_index = "";
foreach ($header_ary as $line_name) {
  $pattern = "/\s+/";
  $line_name = preg_replace($pattern, "", $line_name);
  $count++;
  if ($count == 1) {
    continue;
  }
  $sql = "select line_record_uid, line_record_name from line_records where line_record_name = \"$line_name\"";
  $res = mysql_query($sql) or die(mysql_error());
  if ($row = mysql_fetch_array($res)) {
    $uid = $row[0];
    $name = $row[1];
  } else {
    $sql = "select line_records.line_record_uid, line_record_name
         from line_synonyms, line_records
         where line_synonyms.line_record_uid = line_records.line_record_uid
         and line_synonym_name = \"$line_name\"";
    $res = mysql_query($sql) or die(mysql_error());
    if ($row = mysql_fetch_array($res)) {
      $uid = $row[0];
      $name = $row[1];
    } else {
      echo "Error: $line_name not found\n";
      continue;
    }
  }
  if (isset($unique_line{$uid})) {
     echo "Error: not unique line $line_name\n";
  }  else {
     $unique_line{$uid} = 1;
  } 
  if ($line_index == "") {
        $line_index = $uid;
        $line_name_index = $name;
  } else {
        $line_index = $line_index . ", $uid";
        $line_name_index = $line_name_index . ", $name";
  }
  $sql = "insert into tht_base (line_record_uid, experiment_uid) values ($uid, $experiment_uid)";
  $res = mysql_query($sql) or die(mysql_error());
}
$line_name_index = preg_replace("/\n/", "", $line_name_index);
$line_name_index = str_replace(" ", "", $line_name_index);

$sql = "select marker_uid, marker_name from markers, marker_types where 
    markers.marker_type_uid = marker_types.marker_type_uid
    and marker_type_name = \"GBS\"";
$res = mysql_query($sql) or die(mysql_error());
while ($row = mysql_fetch_array($res)) {
    $marker_uid = $row[0];
    $marker_name = $row[1];
    $marker_list[$marker_name] = $marker_uid;
}

$sql = "select marker_uid, value from marker_synonyms";
$res = mysql_query($sql) or die(mysql_error());
while ($row = mysql_fetch_array($res)) {
    $marker_uid = $row[0];
    $marker_name = $row[1];
    $marker_list_syn[$marker_name] = $marker_uid;
}

$sql = "insert into allele_bymarker_expidx(experiment_uid, line_index, line_name_index) values ($experiment_uid, \"$line_index\", \"$line_name_index\")";
//$res = mysql_query($sql) or die(mysql_error());
$count = 0;
$count_line_prev = "";
while (!feof($fh)) {
    $line = fgets($fh);
    $lineA = str_getcsv($line, "\t");
    $count_line = count($lineA);
    if ($count_line != $count_line_prev) {
        echo "line count error $count_line $count_line_prev\n";
    }
    $count_line_prev = $count_line;
    $marker = $lineA[0];
    $chrom = $lineA[1];
    $pos = $lineA[2];
    $pattern = "/^[^\t]+\t[^\t]+\t[^\t]+\t/";
    $alleles = preg_replace($pattern, '', $line, 1);
    //$pattern = "/\t/";
    //$alleles = preg_replace($pattern, ',', $alleles);
    $pattern = "/\n/";
    $alleles = preg_replace($pattern, '', $alleles);
    $alleles = str_replace(" ", "", $alleles);
    $count++;
    if (isset($marker_list[$marker])) {
        $marker_uid = $marker_list[$marker];
        $sql = "insert into allele_bymarker_exp_ACTG(experiment_uid, marker_uid, marker_name, chrom, pos, alleles)
            values ($experiment_uid, $marker_uid, \"$marker\", \"$chrom\", \"$pos\", \"$alleles\")";
        //$sql = "insert into allele_bymarker_exp_101(experiment_uid, marker_uid, marker_name, chrom, pos, alleles)
        //    values ($experiment_uid, $marker_uid, \"$marker\", \"$chrom\", \"$pos\", \"$alleles\")";
        $res = mysql_query($sql) or die(mysql_error() . $sql);
        if (($count % 10000) == 0) {
            echo "finished $count\n";
        }
    } elseif (isset($marker_list_syn[$marker])) {
        $marker_uid = $marker_list_syn[$marker];
        echo "error - can not insert duplicate for synonym $marker\n";
    } else {
        echo "$marker not found\n";
    }
}
echo "$count lines from $file\n";
fclose($fh);
