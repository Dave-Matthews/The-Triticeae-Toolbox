<?php

    include "check_synonym_class.php";
    //needed for mac compatibility
    ini_set('auto_detect_line_endings', true);
    //needed to hold all marker sequences in memory
    ini_set('memory_limit', '2G');

    $error_flag = 0;
    if ($argc < 3) {
        die("Usage: filename\n");

    } else {
            $infile = $argv[1];
            $emailAddr = $argv[2];
            $urlPath = $argv[3];
            //$uploadfile = $_GET['file_name'];
            //$fileFormat = $_GET['file_type'];
            //$overwrite = $_GET['overwrite'];
            //$expand = $_GET['expand'];
            //$orderAllele = $_GET['orderAllele'];
            $outfile = $infile . ".blast";
            $outfile2 = $infile . ".index";
        }
        $fileFormat = 0;
        $fileFormatName = "generic";

        /* Read the file */
        if (($reader = fopen($infile, "r")) == FALSE) {
            error(1, "Unable to access file $infile.");
            exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
        }
        if (($writer = fopen($outfile, "w")) == FALSE) {
            error(1, "Unable to access file $outfile.");
            exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
        }
        if (($writer2 = fopen($outfile2, "w")) == FALSE) {
            error(1, "Unable to access file $outfile2.");
            exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
        }

        //Advance to data header area, for Illumina this is the converted file
        while(!feof($reader))  {
            $line = fgets($reader);
            if (stripos($line, 'marker_name') !== false) break;
        }

        if (feof($reader)) {
            echo "Using $fileFormatName file format.<br>\n";
            error(1, "Unable to locate genotype header line.");
            exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
        }
        $header = str_getcsv($line,",");

        if (count($header) < 2) {
            echo "Using $fileFormatName file format.<br>\n";
            error(1, "File is not in correct CSV format, must include comma seperators.\n");
            exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
        }

        // Set up header column; all columns are required
        $nameIdx = implode(find("marker_name", $header),"");
        $typeIdx = implode(find("marker_type", $header),"");
        $alleleAIdx = implode(find("A_allele", $header),"");
        $alleleBIdx = implode(find("B_allele", $header),"");
        $sequenceIdx = implode(find("sequence", $header),"");
        $numberColumns = 5;

        // Check if a required col is missing
        if ($fileFormat == 0) {  //check for old version of file
            if ($typeIdx == "") {
              echo "Using $fileFormatName file format.<br>\n";
              echo "ERROR: Missing the marker_type column. You are using an old template file<br>\n";
              exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
            }
            if (($nameIdx == "")||($alleleAIdx == "")||($alleleBIdx == "")|| ($sequenceIdx == "")) {
              echo "Using $fileFormatName file format.<br>\n";
              echo "ERROR: Missing One of these required Columns. Please correct it and upload again: <br> marker_name - ".$nameIdx.
                    "<br>"." A_allele - ".$alleleAIdx."<br>"." B_allele - ". $alleleBIdx.
                    "<br>"." sequence - ".$sequenceIdx."<br>";
              exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
            }
        } elseif ($fileFormat == 3) {  //only require name
            if ($nameIdx == "") {
              echo "Using $fileFormatName file format.<br>\n";
              echo "ERROR: Missing One of these required Columns. Please correct it and upload again: <br> marker_name - ".$nameIdx.
                    "<br>"." A_allele - ".$alleleAIdx."<br>"." B_allele - ". $alleleBIdx.
                    "<br>"." sequence - ".$sequenceIdx."<br>";
              exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
            }
        } else {
          if (($nameIdx == "")||($alleleAIdx == "")||($alleleBIdx == "")|| ($sequenceIdx == "")) {
            echo "Using $fileFormatName file format.<br>\n";
            echo "ERROR: Missing One of these required Columns. Please correct it and upload again: <br> marker_name - ".$nameIdx.
                    "<br>"." A_allele - ".$alleleAIdx."<br>"." B_allele - ". $alleleBIdx.
                    "<br>"." sequence - ".$sequenceIdx."<br>";
            exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
          }
        }

        $i = 1;
        while (($line = fgets($reader)) !== FALSE) {
            if (strlen($line) < 2) continue;

            if (empty($line)) {
                continue;
            }
            $j = 0;
            $data = str_getcsv($line,",");

            //Check for junk line
            if (count($data) < $numberColumns) {
                echo "Using $fileFormatName file format<br>\n";
                echo "ERROR DETECT: Line does not contain $numberColumns columns.". "<br/>". $line . "<br/>" ;
                exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
            }
            $name = $data[0];
            $type = $data[1];
            $sequence = $data[4];
            $pattern =  "/([A-Za-z]*)\[([ACTG])\/([ACTG])\]([A-Za-z]*)/";
            if (preg_match($pattern, $sequence, $match)) {
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
                    echo "bad SNP in database<br>$name\n$allele\t$sequence\n";
                }
                $seq = $match[1] . $allele . $match[4];
                $length = strlen($seq);
                fwrite($writer, ">$name\n$seq\n");
                fwrite($writer2, "$name,$length,$type\n");
            } else {
                fwrite($writer, "error $sequence\n");
            }
        }
        fclose($reader);
        fclose($writer);
        fclose($writer2);

        typeBlastRun($infile);
        typeBlastParse($infile);
      
    //send email so user can check on status of import
    $target_Path = substr($infile, 0, strrpos($infile, '/')+1);
    $tPath = str_replace('./', '', $target_Path);
    $body = "The offline checking of marker sequence has finished.\n
      Additional information can be found at ".$urlPath;
    mail($emailAddr, "Marker sequence BLAST finished", $body);


