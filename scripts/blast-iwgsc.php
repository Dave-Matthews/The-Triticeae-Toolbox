<?php

$db_user = '';
$db_pass = '';
$db_host = 'localhost';
$linkID = mysqli_connect($db_host, $db_user, $db_pass);
mysqli_select_db($linkID, "T3wheat");

/** check how many processes are running */
function isRunning()
{
    $count = shell_exec('ps | grep -c blastn');
    $count = rtrim($count);
    return $count;
}

if ($handle = opendir('/var/www/html/t3/gbs_files/blast')) {
    while (false !== ($entry = readdir($handle))) {
        if (preg_match("/(wheat_IWGSC2_\d[^\.]+)\.nin/", $entry, $match)) {
            echo "$entry $match[1];\n";
            $db = $match[1];
            $chrarm_list[] = $db;
            $blastout = "blast" . $db . ".out1";
            $command = "rm $blastout";
            //echo "$command\n";
            exec($command);
            $blastout = "blast" . $db . ".out2";
            $command = "rm $blastout";
            //echo "$command\n";
            exec($command);
        }
    }
} else {
    echo "Error:\n";
    die("Error: can not open file\n");
}

if (is_file('/proc/cpuinfo')) {
    $cpuinfo = file_get_contents('/proc/cpuinfo');
    preg_match_all('/^processor/m', $cpuinfo, $matches);
    $numCpus = count($matches[0]);
    echo "this computer has $numCpus for parallel processing\n";
} else {
    die("Error: can not determine number of CPUs\n");
}

$seq1 = "";
$seq2 = "";
$count = 0;
$count_wcss = 0;
$count_total = 0;
$count_good = 0;
$tmpfile1 = "temp1.fasta";
$tmpfile2 = "temp2.fasta";
$fh1 = fopen($tmpfile1, "w") or die("Unable to open file $tmpfile1");
$fh2 = fopen($tmpfile2, "w") or die("Unable to open file $tmpfile2");
$sql = "select marker_name, sequence from markers
    where sequence is not NULL";
$res = mysqli_query($linkID, $sql) or die(mysqli_error($linkID) . "<br>" . $sql);
while ($row = mysqli_fetch_array($res)) {
    $count_total++;
    $name = $row[0];
    $seq = strtoupper($row[1]);
    $pattern = "/WCSS1/";
    if (preg_match($pattern, $name)) {
        $count_wcss++;
        continue;
    }
    $pattern = "/([A-Z]*)\[([A-Z])\/([A-Z])\]([A-Z]*)/";
    if (preg_match($pattern, $seq, $matches)) {
        $m1 = $matches[1] . $matches[2] . $matches[4];
        $m2 = $matches[1] . $matches[3] . $matches[4];
        //echo "$name\n$seq\n$m1\n$m2\n\n";
    } else {
        echo "Error: $count_total $count_good $name  bad sequence $seq\n";
        continue;
    }
    $count_good++;
    $count++;
    $seq1 .= ">$name\n$m1\n";
    $seq2 .= ">$name\n$m2\n";
    if ($count == 1000) {
        $countRunning = isRunning();
        while ($countRunning > 0) {
            echo "running $countRunning sleep 10 sec\n";
            sleep(10);
            $countRunning = isRunning();
        }
        $fh1 = fopen($tmpfile1, "w") or die("Unable to open file $tmpfile1");
        $fh2 = fopen($tmpfile2, "w") or die("Unable to open file $tmpfile2");
        fwrite($fh1, $seq1);
        fwrite($fh2, $seq2);
        fclose($fh1);
        fclose($fh2);
        foreach ($chrarm_list as $db) {
          $blastdb = "/var/www/html/t3/gbs_files/blast/$db";
          $blastout1 = "blast" . $db . ".out1";
          $blastout2 = "blast" . $db . ".out2";
          $command = "/var/www/html/t3/wheat/viroblast/blastplus/bin/blastn -outfmt 6 -dust no -word_size 16 -task megablast -evalue 1e-08 -query $tmpfile1 -db $blastdb >> $blastout1 & echo $!";
          $tmp1 = shell_exec($command);
          $command = "/var/www/html/t3/wheat/viroblast/blastplus/bin/blastn -outfmt 6 -dust no -word_size 16 -task megablast -evalue 1e-08 -query $tmpfile2 -db $blastdb >> $blastout2 & echo $!";
          $tmp2 = shell_exec($command);
          //echo "$command\n";
        }
        $seq1 = "";
        $seq2 = "";
        $count = 0;
        echo "count_total = $count_total count_good = $count_good\n";
    }
}
       $countRunning = isRunning();
        while ($countRunning > 0) {
            echo "running $countRunning sleep 10 sec\n";
            sleep(10);
            $countRunning = isRunning();
        }
        $fh1 = fopen($tmpfile1, "w") or die("Unable to open file $tmpfile1");
        $fh2 = fopen($tmpfile2, "w") or die("Unable to open file $tmpfile2");
        fwrite($fh1, $seq1);
        fwrite($fh2, $seq2);
        fclose($fh1);
        fclose($fh2);
        foreach ($chrarm_list as $db) {
          $blastdb = "/var/www/html/t3/gbs_files/blast/$db";
          $blastout1 = "blast" . $db . ".out1";
          $blastout2 = "blast" . $db . ".out2";
          $command = "/var/www/html/t3/wheat/viroblast/blastplus/bin/blastn -outfmt 6 -dust no -word_size 16 -task megablast -evalue 1e-08 -query $tmpfile1 -db $blastdb >> $blastout1 & echo $!";
          $tmp1 = shell_exec($command);
          $command = "/var/www/html/t3/wheat/viroblast/blastplus/bin/blastn -outfmt 6 -dust no -word_size 16 -task megablast -evalue 1e-08 -query $tmpfile2 -db $blastdb >> $blastout2 & echo $!";
          //echo "$command\n";
        }
        echo "count_total = $count_total count_good = $count_good\n";

