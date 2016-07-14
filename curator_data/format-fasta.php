<?php
/**
 * format-fasta.php
 * create fasta file for all the markers in the database
 *
 * PHP version 5
 *
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/curator_data/format-fasta.php
*/

require 'config.php';
require $config['root_dir'].'includes/bootstrap_curator.inc';
set_time_limit(3000);
$mysqli = connecti();

//needed for mac compatibility
ini_set('auto_detect_line_endings', true);

$sql = "select value from settings where name = \"database\"";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>" . $sql);
if ($row = mysqli_fetch_array($res)) {
    $database = $row[0];
}
  
//create file of SNP fasta
$file1 = $config['root_dir'] . "viroblast/db/nucleotide/wheat-markers";
$file2 = $config['root_dir'] . "viroblast/db/nucleotide/index.csv";
if ($fh1 = fopen("$file1", "w")) {
    echo "opened output file $file1<br>\n";
} else {
    die("can not open output $file1\n");
}
if ($fh2 = fopen("$file2", "w")) {
    echo "opened output file $file2<br>\n";
} else {
    die("can not open output $file2\n");
}

$count = 0;
$sql = "select marker_name, marker_type_name, sequence from markers, marker_types
     where markers.marker_type_uid = marker_types.marker_type_uid";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>" . $sql);
while ($row = mysqli_fetch_array($res)) {
    $name = $row[0];
    $marker_type = $row[1];
    $seq = $row[2];
    $pattern = "/[A-Za-z0-9-_\.]+/";
    if (preg_match($pattern, $name, $match)) {
        $name = $match[0];
    }
    $pattern = "/[A-Za-z]/";
    if (preg_match($pattern, $seq)) {
        $replace = "R";
        $pattern = "/\[A\/G\]/";
        $seq = preg_replace($pattern, $replace, $seq);
        $pattern = "/\[G\/A\]/";
        $seq = preg_replace($pattern, $replace, $seq);

        $replace = "Y";
        $pattern = "/\[T\/C\]/";
        $seq = preg_replace($pattern, $replace, $seq);
        $pattern = "/\[C\/T\]/";
        $seq = preg_replace($pattern, $replace, $seq);

        $replace = "M";
        $pattern = "/\[A\/C\]/";
        $seq = preg_replace($pattern, $replace, $seq);
        $pattern = "/\[C\/A\]/";
        $seq = preg_replace($pattern, $replace, $seq);

        $replace = "K";
        $pattern = "/\[T\/G\]/";
        $seq = preg_replace($pattern, $replace, $seq);
        $pattern = "/\[G\/T\]/";
        $seq = preg_replace($pattern, $replace, $seq);

        $replace = "S";
        $pattern = "/\[C\/G\]/";
        $seq = preg_replace($pattern, $replace, $seq);
        $pattern = "/\[G\/C\]/";
        $seq = preg_replace($pattern, $replace, $seq);

        $replace = "W";
        $pattern = "/\[A\/T\]/";
        $seq = preg_replace($pattern, $replace, $seq);
        $pattern = "/\[T\/A\]/";
        $seq = preg_replace($pattern, $replace, $seq);

        $length = strlen($seq);
        //fwrite($fh,">gnl|$database|$name\n$seq\n");
        fwrite($fh1, ">$name\n$seq\n");
        fwrite($fh2, "$name,$length,$marker_type\n");
        $count++;
    } else {
        //echo "skip $name $seq<br>\n";
    }
}

echo "$count markers found<br>\n";
$out = $config['root_dir'] . "viroblast/db/nucleotide";
chdir($out);
$cmd = "cd ../viroblast/db/nucleotide;" . $config['root_dir'] .
    "viroblast/blastplus/bin/formatdb -i wheat-markers -p F -o T 2> /tmp/tht/tmp_formatdb.out";
$cmd = "cd ../viroblast/db/nucleotide;" . $config['root_dir'] .
    "viroblast/blastplus/bin/makeblastdb -in wheat-markers -out wheat-markers -title wheat-markers -dbtype nucl";
//echo "$cmd<br>\n";
exec($cmd);
