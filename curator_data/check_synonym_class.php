<?php

/**
 * Takes a needle and haystack (just like in_array()) and does a wildcard search on it's values.
 *
 * @param string $string Needle to find
 * @param array  $array  Haystack to look through
 *
 * @result array Returns the elements that the $string was found in
*/
function find($string, $array = array ())
{
    foreach ($array as $key => $value) {
        unset ($array[$key]);
        if (strpos($value, $string) !== false) {
                $array[$key] = $key;
        }
    }
    return $array;
}

function error($type, $text)
{
    switch($type) {

        case 0:
            echo "<p class=\"warning\"><strong>Warning:</strong> $text</p>\n";
            break;

        case 1:
            echo "<p class=\"error\"><strong>Error:</strong> $text</p>\n";
            break;

    }
}

/** check how many processes are running */
function isRunning($pidList)
{
    $count = 0;
    foreach ($pidList as $pid) {
        $result = shell_exec(sprintf('ps %d', $pid));
        //echo "$pid $result\n";
        if (count(preg_split("/\n/", $result)) > 2) {
            $count++;
        }
    }
    return $count;
}
 
/** break query into files of 1000 to improve performance
 * use word size of half the shortest marker to find all matches
 * detect number of processors
 */
function typeBlastRun($infile)
{
    if (is_file('/proc/cpuinfo')) {
        $cpuinfo = file_get_contents('/proc/cpuinfo');
        preg_match_all('/^processor/m', $cpuinfo, $matches);
        $numCpus = count($matches[0]);
        echo "this computer has $numCpus for parallel processing\n";
    }
    $seq = "";
    $count = 0;
    $count2 = 0;
    $count_file = 1;
    $pidList = array();
    echo "Start time - ". date("m/d/y : H:i:s", time()) ."\n";
    echo "marker\tpid\n";
    $target_Path = substr($infile, 0, strrpos($infile, '/')+1);
    $tPath = str_replace('./', '', $target_Path);
    $blastfile = $infile . ".blast";
    $tmpfile = $tPath . "temp" . $count_file . ".fasta";
    $blastout = $tPath . "blast" . $count_file . ".out";
    $fh = fopen($blastfile, "r") or die("Unable to open file $blastfile");
    $fh2 = fopen($tmpfile, "w") or die("Unable to open file $tmpfile");
    while (!feof($fh)) {
        $line = fgets($fh);
        if (preg_match("/>/", $line)) {
            $seq .= $line;
        } else {
            $seq .= $line;
            $count++;
            $count2++;
        }
        if ($count == 1000) {
            fwrite($fh2, $seq);
            fclose($fh2);
            $command = "../viroblast/blastplus/bin/megablast -D 3 -F F -W 14 -e 1 -i $tmpfile -d ../viroblast/db/nucleotide/wheat-markers >> $blastout & echo $!";
            $tmp = shell_exec($command);
            $pidList[$count_file] = rtrim($tmp);
            echo "$count2\t$pidList[$count_file] running BLAST on $count queries";
            $countRunning = isRunning($pidList);
            echo "\trunning $countRunning";
            if ($countRunning > $numCpus) {
                echo "\twaiting 20 seconds for free processor\n";
                sleep(20);
            } else {
                echo "\n";
            }
           
            $count_file++;
            $seq = "";
            $tmpfile = $tPath . "temp" . $count_file . ".fasta";
            $blastout = $tPath . "blast" . $count_file . ".out";
            $fh2 = fopen($tmpfile, "w") or die("Unable to open file $tmpfile");
            $count = 0;
        }
    }
    fwrite($fh2, $seq);
    fclose($fh2);
    $command = "../viroblast/blastplus/bin/megablast -D 3 -F F -W 14 -e 1 -i $tmpfile -d ../viroblast/db/nucleotide/wheat-markers >> $blastout & echo $!";
    $tmp = shell_exec($command);
    $pidList[$count_file] = rtrim($tmp);
    echo "$count2\t$pidList[$count_file] running BLAST on $count queries";
    fclose($fh);
    $running = 1;
    while ($running) {
        $countRunning = isRunning($pidList);
        echo "\trunning $countRunning";
        if ($countRunning == 0) {
            break;
        } else {
            echo "\twaiting 10 seconds for process\n";
            sleep(10);
        }
    }
    echo "Stop time - ". date("m/d/y : H:i:s", time()) ."\n";
    $command = "cat " . $tPath . "blast*.out >> " . $tPath . "sumblast.out";
    echo "$command\n";
    exec($command);
}

function die_nice($msg)
{
    echo $msg;
    die();
}

function typeBlastParse($infile)
{
    $target_Path = substr($infile, 0, strrpos($infile, '/')+1);
    $tPath = str_replace('./', '', $target_Path);
    $blastout = $tPath . "sumblast.out";
    $blastfile = $infile . ".blast";
    $blastfileindex = $infile . ".index";
    $outfile1 = $infile;
    $outfile1 = preg_replace("/(\.\w+)$/", '_filtered$1', $outfile1);
    $outfile2 = $infile;
    $outfile2 = preg_replace("/(\.\w+)$/", '_synonym$1', $outfile2);
    $blastdb = "../viroblast/db/nucleotide/wheat-markers";
    $blastindex = "../viroblast/db/nucleotide/index.csv";

    //get lenghth of sequence from import file
    $count = 0;
    echo "reading size and type from $blastfileindex\n";
    $fh = fopen($blastfileindex, "r") or die_nice("Unable to open file $blastfile\n");
    while (!feof($fh)) {
        $match = fgetcsv($fh);
        $count++;
        $name = $match[0];
        $size = $match[1];
        $type = $match[2];
        $queryList[$name] = $size;
        $queryListType[$name] = $type;
    }
    echo "found $count markers in $blastfileindex\n";
    fclose($fh);

    //get length of sequence from blast database
    $count = 0;
    echo "reading size and type from $blastindex\n";
    $fh = fopen($blastindex, "r") or die_nice("Unable to open file $blastindex\n");
    while (!feof($fh)) {
        $match = fgetcsv($fh);
        $count++;
        $name = $match[0];
        $size = $match[1];
        $type = $match[2];
        $subjList[$name] = $size;
        $subjListType[$name] = $type;
    }
    echo "found $count markers in $blastindex\n";
    fclose($fh);

    $fh = fopen($blastout, "r") or die_nice("Unable to open file $blastout\n");
    $fh2 = fopen($outfile2, "w") or die_nice("Unable to open file $outfile2\n");
    fwrite($fh2, "Name,Marker_type,Synonym,Synonym_type\n");
    $count = 0;
    while (!feof($fh)) {
        $line = fgets($fh);
        if (preg_match("/^\# /", $line)) {
        } elseif (preg_match("/([^\s]+)\s+([^\s]+)\s+([^\s]+)\s+([^\s]+)/", $line, $match)) {
            $name = $match[1];
            $name2 = $match[2];
            $length = $match[4];
            if (isset($queryList[$name])) {
                $querySize = $queryList[$name];
                $queryType = $queryListType[$name];
            } else {
                die("Error: $name not defined in $blastfileindex\n");
            }
            if (isset($subjList[$name2])) {
                $subjSize = $subjList[$name2];
                $subjType = $subjListType[$name2];
            } else {
                die("Error: $name2 not defined in $blastindex\n");
            }
            if ($match[1] != $match[2]) {
                //echo "$name $name2 $length $querySize $subjSize\n";
                if (($match[3] == "100.00") && ($querySize == $length)) {
                    fwrite($fh2, "$name,$queryType,$name2,$subjType\n");
                    $matched[$name] = 1;
                    $count++;
                } elseif (($match[3] == "100.00") && ($subjSize == $length)) {
                    fwrite($fh2, "$name,$queryType,$name2,$subjType\n");
                    $matched[$name] = 1;
                    $count++;
                }
            }
        } else {
            //echo "bad $line\n";
        }
    }
    echo "$count blast matches found\n";
    fclose($fh);
    fclose($fh2);
    $fh = fopen($infile, "r") or die("Unable to open file $infile\n");
    $fh2 = fopen($outfile1, "w") or die("Unable to open file $outfile1\n");
    while (!feof($fh)) {
        $line = fgets($fh);
        $data = str_getcsv($line, ",");
        $name = $data[0];
        if (!isset($matched[$name])) {
            fwrite($fh2, $line);
        }
    }
    fclose($fh);
    fclose($fh2);
}

    /**
     * check database for name and sequence matches
     */

function typeCheckSynonym($infile)
{
    $target_Path = substr($infile, 0, strrpos($infile, '/')+1);
    $tPath = str_replace('./', '', $target_Path);
    $change_file2 = $tPath . "markerProc2.out";
    $change_file3 = $tPath . "markerProc3.out";
    $change_file4 = $tPath . "markerProc4.out";
    $change_file5 = $tPath . "markerSyn.out";
    if (($fh2 = fopen($change_file2, "w")) == false) {
        echo "Error creating change file $change_file2 $infile<br>\n";
    }
    if (($fh3 = fopen($change_file3, "w")) == false) {
            echo "Error creating change file $change_file3<br>\n";
    }
    if (($fh4 = fopen($change_file4, "w")) == false) {
        echo "Error creating change file $change_file4<br>\n";
    }
    if (($fh5 = fopen($change_file5, "w")) == false) {
        echo "Error creating change file $change_file4<br>\n";
    }

    $count_dup_name = 0;
    $dup_name_results = "";
    $count_dup_seq = 0;
    $count_total = 0;
    $count_update = 0;
    $count_insert = 0;
    $count_add_syn = 0;
    $results = "<thead><tr><th>marker<th>match by name<th>match by sequence<th>database change</thead>\n";
    fwrite($fh2, "marker\tmatch by name\tmatch by sequence\tdatabase change\n");
    fwrite($fh3, "marker\tmatch by name\tmatch by sequence\tdatabase change\n");
    fwrite($fh4, "marker\tmatch by name\tmatch by sequence\tdatabase change\n");
    fwrite($fh5, "marker,$marker_type,A_allele,B_allele,sequence\n");
    $limit = count($storageArr);
    for ($i = 1; $i <= $limit; $i++) {
        if ($i % 1000 == 0) {
            echo "finished $i<br>\n";
            flush();
        }
        $name = $storageArr[$i][$nameIdx];
        $seq = strtoupper($storageArr[$i][$sequenceIdx]);
        $found_name = 0;
        $found_seq = 0;
        $found_seq_name = "";
        $seq_match = "";
        if (preg_match("/[A-Za-z0-9]/", $name)) {
            //if (isset($marker_name[$name]) || isset($marker_syn_list[$name])) {
            if (isset($marker_name[$name])) {
                $found_name = 1;
                $name_match = "yes";
                $count_dup_name++;
            } else {
                $name_match = "";
            }
        } else {
            echo "Error: bad name $name line $i<br>\n";
        }
        if (preg_match("/([A-Za-z]*)\[([ACTG])\/([ACTG])\]([A-Za-z]*)/", $seq, $match)) {
            $count_total++;
            $allele = $match[2] . $match[3];
            if (($allele == "AC") || ($allele == "CA")) {
                $allele = "M";
            } elseif (($allele == "AG") || ($allele == "GA")) {
                $allele = "R";
            } elseif (($allele == "AT") || ($allele == "TA")) {
                $allele = "W";
            } elseif (($allele == "CG") || ($allele == "GC")) {
                $allele = "S";
            } elseif (($allele == "CT") || ($allele == "TC")) {
                $allele = "Y";
            } elseif (($allele == "GT") || ($allele == "TG")) {
                $allele = "K";
            } else {
                echo "bad SNP in import file<br>$name<br>$allele<br>\n";
            }
            $seq = $match[1] . $allele . $match[4];
            foreach ($marker_seq as $seqdb => $namedb) {
                $pos = strpos($seqdb, $seq);
                if (($pos !== false) && ($namedb != $name)) {
                    $found_seq = 1;
                    $found_seq_name = $namedb;
                }
            }
                //if (isset($marker_seq[$seq]) && ($marker_seq[$seq] != $name)) {
                //    $found_seq = 1;
                //    $found_seq_name = $marker_seq[$seq];
                //}
                //if sequence match found then change name in import file
                //if more than one match found then latest one will be used
            if ($found_seq && $overwrite) {
                $storageArr[$i]["syn"] = $found_seq_name;
            }
            if ($found_seq) {
                if ($seq_match == "") {
                    $seq_match = $found_seq_name;
                } else {
                    $seq_match = $seq_match . ", $found_seq_name";
                }
            }
        } else {
            echo "bad sequence $seq<br>\n";
        }

        if ($found_seq) {
            $count_dup_seq++;
            if ($overwrite == 1) {
                if ($found_name) {
                    $count_update++;
                    $action = "update marker";
                    fwrite($fh2, "$name\t$name_match\t$seq_match\t$action\n");
                } else {
                    $count_add_syn++;
                    $action = "add synonym";
                    fwrite($fh4, "$name\t$name_match\t$seq_match\t$action\n");
                }
            } elseif ($found_name) {
                    $count_update++;
                    $action = "update marker";
                    fwrite($fh2, "$name\t$name_match\t$seq_match\t$action\n");
            } else {
                    $count_insert++;
                    $action = "add marker";
                    fwrite($fh3, "$name\t$name_match\t$seq_match\t$action\n");
            }
        } elseif ($found_name) {
                    $count_update++;
                    $action = "update marker";
                    fwrite($fh2, "$name\t$name_match\t$seq_match\t$action\n");
        } else {
                    $count_insert++;
                    $action = "add marker";
                    fwrite($fh3, "$name\t$name_match\t$seq_match\t$action\n");
        }
        fwrite($fh5, "$name\t$name_match\t$seq_match\t$action\n");
        $results .= "<tr><td>$name<td>$name_match<td><font color=blue>$seq_match</font><td>$action\n";
    }
    if ($expand == 1) {
        $display1 = "display:none;";
        $display2 = "";
    } else {
        $display1 = "";
        $display2 = "display:none;";
    }
        ?>
        <table id="content1<?php echo $pheno_uid; ?>" style="<?php echo $display1; ?>">
        <?php
        echo "<thead><tr><th>marker<th>match by name<th>match by sequence<th>database change\n";
        echo "<tr><td>$count_total<td>$count_dup_name<td><font color=blue>$count_dup_seq</font><td>";
        echo "$count_update update marker<br>";
        echo "$count_insert add marker<br>";
        if ($overwrite) {
            echo "$count_add_syn add synonym\n";
        }
        echo "<td><a href=\"curator_data/$change_file2\" target=\"_new\">Download Update Changes</a>\n";
        echo "<br><a href=\"curator_data/$change_file3\" target=\"_new\">Download Add Marker Changes</a>\n";
        echo "<br><a href=\"curator_data/$change_file4\" target=\"_new\">Download ADD Synonym Changes</a>\n";
        ?></table>
        <table id="content2<?php echo $pheno_uid; ?>" style="<?php echo $display2; ?>">
        <?php
        if ($limit < 1000) {
            echo "$results\n";
        } else {
            echo "<tr><td>Too many entries, please download.";
        }
        echo "</table>";
        fclose($fh2);
        fclose($fh3);
        fclose($fh4);
        $pheno_uid = 2;
        $change["update"] = $count_update;
        $change["insert"] = $count_insert;
        $change["dupSeq"] = $count_dup_seq;
        $change["addSyn"] = $count_add_syn;
        return $change;
}
