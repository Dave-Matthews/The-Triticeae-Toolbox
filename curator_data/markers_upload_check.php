<?php
/**
 * Marker importer
 *
 * PHP version 5.3
 * Prototype version 1.5.0
 *
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @version  GIT: 2
 * @link     http://triticeaetoolbox.org/wheat/curator_data/markers_upload_check.php
 *
 * 04/08/2014  CLB    for GBS markers the A and B alleles should be alphabetically ordered
 * 11/09/2011  JLee   Fix problem with empty lines in SNP file
 * 10/25/2011  JLee   Ignore "cut" portion in annotation input file
 * 08/02/2011  JLee   Allow for empty synonyms and annotations
 *
 * Author: John Lee         6/15/2011
 */

require 'config.php';
/*
 * Logged in page initialization
 */
require $config['root_dir'] . 'includes/bootstrap_curator.inc';
require $config['root_dir'] . 'curator_data/lineuid.php';
set_time_limit(3000);

connect();
loginTest();

//needed for mac compatibility
ini_set('auto_detect_line_endings', true);
//needed to hold all marker sequences in memory
ini_set('memory_limit', '2G');

/* ******************************* */
$row = loadUser($_SESSION['username']);

////////////////////////////////////////////////////////////////////////////////
ob_start();

authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();

new MarkersCheck($_GET['function']);

/** Using a PHP class to implement the marker import feature
 *
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/curator_data/markers_upload_check.php
 **/
class MarkersCheck
{
    public $delimiter = "\t";
    public $storageArr = array (array());
    /** Using the class's constructor to decide which action to perform
     *
     * @param string $function action to perform
     */
    public function __construct($function = null)
    {
        switch($function)
        {
            case 'typeDatabaseAnnot':
                $this->type_DatabaseAnnot(); /* update Marker Info */
                break;
            case 'typeDatabaseSNP':
                $this->type_DatabaseSNP(); /* update Allele SNP */
                break;
            case 'typeCheckSynonym':
                $this->type_MarkersSNP(); /* check marker sequence */
                break;
            case 'typeCheckBlast':
                $this->type_MarkersSNP2(); /* check marker sequence */
                break;
            case 'typeCheckProgress':
                $this->typeMarkersProgress(); /* check progress of typeMarkersSynonym */
                break;
            default:
                $this->typeMarkersCheck(); /* intial case*/
                break;
        }
    }

    /**
     * display header footer and call load functions
     *
     * @return null
     */
    function typeMarkersCheck()
    {
        global $config;
        include $config['root_dir'] . 'theme/admin_header.php';
        if ($_FILES['file']['name'][0] != "") {
            echo "<h2>Marker Annotation</h2>";
        } else {
            ?>
            <h2>Enter/Update Markers: Validation</h2>
            <img alt="spinner" id="spinner" src="images/ajax-loader.gif" style="display:none;" />
            <script type="text/javascript" src="curator_data/marker04.js"></script>
            <div id=update></div>
            <div id=checksyn>
            <?php
                //echo "The import scrip first check if the marker name is in the databae. ";
                //echo "If no matching name is found then it will check if the marker sequence";
                //echo "  matches an entry in the database.";
        }

        $infile = $_GET['linedata'];
        if ($_FILES['file']['name'][0] != "") {
            $this->_typeMarkersAnnot();
        } elseif ($_FILES['file']['name'][1] != "") {
            $this->type_LoadFile1();
        } elseif ($infile != "") {
            $this->type_MarkersSNP();
        } else {
            error(1, "No File Uploaded");
            print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
        }
        echo "</div>";
        echo "<div id=result></div>";
        $footer_div = 1;
        include $config['root_dir'].'theme/footer.php';
    }

    /**
     * estimate execution time for check marker name and sequence
     *
     * @return null
     */
    function typeMarkersProgress()
    {
        if (empty($_GET['linedata'])) {
            echo "missing data file\n";
        } else {
            $infile = $_GET['linedata'];
        }
        if (($reader = fopen($infile, "r")) == false) {
            error(1, "Unable to access file.");
            exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
        }
        $count_total = 0;
        while (!feof($reader)) {
            $line = fgets($reader);
            $count_total++;
        }
        fclose($reader);

        $sql = "select count(*) from markers";
        $res = mysql_query($sql) or die("Database Error: Marker types lookup - ".mysql_error() ."<br>".$sql);
        if ($row = mysql_fetch_row($res)) {
            $count_db = $row[0];
            $exec_time = round(($count_total * $count_db)/500000000, 0);
            echo "<br>Checking marker name and sequence.<br>Predicted execution time is $exec_time seconds<br>\n";
        }
    }

    /**
     * for GBS markers without reference sequence the alleles should be ordered alphabetically
     *
     * @param array  &$storageArr contents of import file
     * @param string $nameIdx     index of name column
     * @param string $alleleAIdx  index of alleleA column
     * @param string $alleleBIdx  index of alleleB column
     * @param string $sequenceIdx index of sequence column
     *
     * @return null
     */
    function typeCheckAlleleOrder(&$storageArr, $nameIdx, $alleleAIdx, $alleleBIdx, $sequenceIdx)
    {
        $count_allele = 0;
        $count_seq = 0;
        $count = 0;
        $infile = $_GET['linedata'];
        $target_Path = substr($infile, 0, strrpos($infile, '/')+1);
        $tPath = str_replace('./', '', $target_Path);
        $change_file = $tPath . "markerProc1.out";
        if (($fh = fopen($change_file, "w")) == false) {
            echo "Error creating change file $change_file<br>\n";
        }
        fwrite($fh, "name\torig/cor\tA_allele\tB_allele\tsequence\n");
        //look for the case where A and B allele are reversed
        $limit = count($storageArr);
        for ($i = 1; $i <= $limit; $i++) {
            $found = 0;
            $name = $storageArr[$i][$nameIdx];
            $allele = array($storageArr[$i][$alleleAIdx], $storageArr[$i][$alleleBIdx]);
            $allele_sort = $allele;
            if (!sort($allele_sort)) {
                echo "Error in sorting alleles\n";
            }
            if ($allele[0] != $allele_sort[0]) {
                $found = 1;
                $storageArr[$i][$alleleAIdx] = $allele_sort[0];
                $storageArr[$i][$alleleBIdx] = $allele_sort[1];
            }
            $seq = strtoupper($storageArr[$i][$sequenceIdx]);
            if (preg_match("/([A-Z]*)\[([ACTG])\/([ACTG])\]([A-Z]*)/", $seq, $match)) {
                $seq_snp = array($match[2], $match[3]);
                $seq_snp_sort = array($match[2], $match[3]);
                sort($seq_snp_sort);
                if ($seq_snp[0] != $seq_snp_sort[0]) {
                    $found = 1;
                    $count_seq++;
                    $seq_sort = $match[1] . "[" . $match[3] . "/" . $match[2] . "]" . $match[4];
                    $storageArr[$i][$sequenceIdx] = $seq_sort;
                }
            } else {
                echo "Error in format of sequence $name $seq<br>\n";
            }
            if ($found) {
                $count_allele++;
                fwrite($fh, "$name\toriginal\t$allele[0]\t$allele[1]\t$seq\n");
                fwrite($fh, "$name\tcorrected\t$allele_sort[0]\t$allele_sort[1]\t$seq_sort\n");
            }
        }
        fclose($fh);
        echo "<table><tr><th>marker<th>corrected allele order\n";
        echo "<tr><td>$limit<td>$count_allele<td><a href=\"curator_data/$change_file\" target=\"_new\">Download Corrections</a>\n";
        echo "</table>";
    }

    function revCmp($seq)
    {
        $seq = strrev($seq);
        // change the sequence to upper case
        $seq = strtoupper ($seq);
        // the system used to get the complementary sequence is simple but fas
        $seq=str_replace("A", "t", $seq);
        $seq=str_replace("T", "a", $seq);
        $seq=str_replace("G", "c", $seq);
        $seq=str_replace("C", "g", $seq);
        $seq=str_replace("Y", "r", $seq);
        $seq=str_replace("R", "y", $seq);
        $seq=str_replace("W", "w", $seq);
        $seq=str_replace("S", "s", $seq);
        $seq=str_replace("K", "m", $seq);
        $seq=str_replace("M", "k", $seq);
        $seq=str_replace("D", "h", $seq);
        $seq=str_replace("V", "b", $seq);
        $seq=str_replace("H", "d", $seq);
        $seq=str_replace("B", "v", $seq);
        // change the sequence to upper case again for output
        $seq = strtoupper ($seq);
        return $seq;
    }

    /**
     * check database for name and sequence matches 
     *
     * @param array   &$storageArr contents of import file
     * @param string  $nameIdx     index of name column
     * @param integer $sequenceIdx index of sequence column
     * @param bolean  $overwrite   change the contents of import
     * @param bolean  $expand      expand listing of results
     *
     * @return null
     */
    function typeCheckSynonym(&$storageArr, $nameIdx, $sequenceIdx, $overwrite, $expand)
    {
        global $mysqli;
        $infile = $_GET['linedata'];
        $target_Path = substr($infile, 0, strrpos($infile, '/')+1);
        $tPath = str_replace('./', '', $target_Path);
        $change_file2 = $tPath . "markerProc2.out";
        $change_file3 = $tPath . "markerProc3.out";
        $change_file4 = $tPath . "markerProc4.out";
        if (($fh2 = fopen($change_file2, "w")) == false) {
            echo "Error creating change file $change_file2<br>\n";
        }
        if (($fh3 = fopen($change_file3, "w")) == false) {
            echo "Error creating change file $change_file3<br>\n";
        }
        if (($fh4 = fopen($change_file4, "w")) == false) {
            echo "Error creating change file $change_file4<br>\n";
        }
        $sql = "select marker_uid, value from marker_synonyms";
        $res = mysql_query($sql) or die("Database Error: Marker types lookup - ".mysql_error() ."<br>".$sql);
        while ($row = mysql_fetch_assoc($res)) {
            $name = $row['value'];
            $marker_syn_list[$name] = 1;
        }
        //convert SNP to IUPAC Ambiguity Code before checking for sequence matches
        $pheno_uid = 1;
        $sql = "select marker_name, sequence from markers where sequence is not NULL";
        $res = mysql_query($sql) or die("Database Error: Marker types lookup - ".mysql_error() ."<br>".$sql);
        while ($row = mysql_fetch_assoc($res)) {
            $name = $row['marker_name'];
            $seq = strtoupper($row['sequence']);
            if (preg_match("/([A-Za-z]*)\[([ACTG])\/([ACTG])\]([A-Za-z]*)/", $seq, $match)) {
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
                    echo "bad SNP in database<br>$name<br>$allele<br>\n";
                }
                $seq = $match[1] . $allele . $match[4];
                $marker_name[$name] =  1;
                $marker_seq[$seq] = $name;
            } else {
                //echo "bad sequence in database<br>$name<br>$seq<br>\n";
            }
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
        $limit = count($storageArr);
        for ($i = 1; $i <= $limit; $i++) {
            $name = $storageArr[$i][$nameIdx];
            $seq = strtoupper($storageArr[$i][$sequenceIdx]);
            $found_name = 0;
            $found_seq = 0;
            $found_seq_name = "";
            $seq_match = "";
            if (preg_match("/[A-Za-z0-9]/", $name)) {
                if (isset($marker_name[$name]) || isset($marker_syn_list[$name])) {
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
                if (isset($marker_seq[$seq]) && ($marker_seq[$seq] != $name)) {
                    $found_seq = 1;
                    $found_seq_name = $marker_seq[$seq];
                }
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
            $results .= "<tr><td>$name<td>$name_match<td><font color=blue>$seq_match</font><td>$action\n";
        }
        if ($expand == 1) {
            $display1 = "display:none;";
            $display2 = "";
        } else {
            $display1 = "";
            $display2 = "display:none;";
        }
        if ($limit < 1000) {
            ?>
            <table><tr><td>
            <a class="collapser" id="on_switch<?php echo $pheno_uid; ?>" style="<?php echo $display1; ?> border-bottom:none" onclick="javascript:disp_index(<?php echo $pheno_uid;?>);return false;">
            <img src="images/collapser_plus.png" /> Expand</a>
            <a class="collapser" id="off_switch<?php echo $pheno_uid; ?>" style="<?php echo $display2; ?> border-bottom:none" onclick="javascript:hide_index(<?php echo $pheno_uid;?>);return false;">
            <img src="images/collapser_minus.png"/> Compress</a>
            </table>
            <?php
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

    /**
     * check import file for name and sequence matches 
     *
     * @param array   &$storageArr contents of import file
     * @param string  $nameIdx     index of name column
     * @param integer $sequenceIdx index of sequence column
     * @param bolean  $overwrite   change the contents of import
     * @param bolean  $expand      expand listing of results
     *
     * @return null
     */
    function typeCheckImport(&$storageArr, $nameIdx, $sequenceIdx, $overwrite, $expand)
    {
        $limit = count($storageArr);
        $marker_seq_dup = "";
        $marker_name_dup = "";
        for ($i = 1; $i <= $limit; $i++) {
            $name = $storageArr[$i][$nameIdx];
            $seq = strtoupper($storageArr[$i][$sequenceIdx]);
            if (isset($marker_seq_import[$seq])) {
                if ($marker_seq_dup == "") {
                    $marker_seq_dup = $name;
                } else {
                    $marker_seq_dup = $marker_seq_dup . ", $name";
                }
                $storageArr[$i]["syn"] = "skip duplicate seq";;
            } else {
                $marker_seq_import[$seq] = $name;
            }
            if (isset($marker_name_import[$name])) {
                if ($marker_name_dup == "") {
                    $marker_name_dup = $name;
                } else {
                    $marker_name_dup = $marker_name_dup . ", $name";
                }
                $storageArr[$i]["syn"] = "skip duplicate name";
            } else {
                $marker_name_import[$name] = 1;
            }
        }
        if ($marker_name_dup != "") {
            echo "<font color=red>Error: marker(s) removed from import because name duplicates previous entry</font><br>$marker_name_dup<br><br>\n";
        }
        if ($marker_seq_dup != "") {
            echo "<font color=red>Error: marker(s) removed from import because sequence duplicates previous entry</font><br>$marker_seq_dup<br><br>\n";
        }
        if (($marker_seq_dup == "") && ($marker_name_dup == "")) {
            echo "No duplicate names or sequences within import file\n";
        }
    }

    /**
     * Load annotation file
     *
     * @return null
     */
    private function _typeMarkersAnnot()
    {
        ?>
        <script type="text/javascript">
	
        function update_databaseAnnot(filepath, filename, username) {
            var url='<?php echo $_SERVER[PHP_SELF];?>?function=typeDatabaseAnnot&linedata=' + filepath + '&file_name=' + filename + '&user_name=' + username;
	
            // Opens the url in the same window
            window.open(url, "_self");
        }

        </script>
	
	<style type="text/css">
		th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
		table {background: none; border-collapse: collapse}
		td {border: 0px solid #eee !important;}
		h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
	</style>
		
	<style type="text/css">
        table.marker {background: none; border-collapse: collapse}
        th.marker { background: #5b53a6; color: #fff; padding: 5px 0; border: 0; }
        td.marker { padding: 5px 0; border: 0 !important; }
    </style>
<?php
        $error_flag = 0;
        $row = loadUser($_SESSION['username']);
		$username=$row['name'];
                $username = preg_replace("/\s/", "", $username);
		$tmp_dir="./uploads/tmpdir_".$username."_".rand();
        //	$raw_path= "rawdata/".$_FILES['file']['name'][1];
        //	copy($_FILES['file']['tmp_name'][1], $raw_path);
        umask(0);
        
        if(!file_exists($tmp_dir) || !is_dir($tmp_dir)) {
            mkdir($tmp_dir, 0777);
        }

        $target_path=$tmp_dir."/";
 	
 		$uploadfile =$_FILES['file']['name'][0];
             
        $uftype=$_FILES['file']['type'][0];
        if (strpos($uploadfile, ".txt") === FALSE) {
            error(1, "Expecting an tab-delimited text file. <br> The type of the uploaded file is ".$uftype);
            print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
        }
        else {
		    if(move_uploaded_file($_FILES['file']['tmp_name'][0], $target_path.$uploadfile)) {
            /* start reading the input */
            	$annotfile = $target_path.$uploadfile;
                //echo "Annotate file - " . $annotfile . "<br>";
                /* Read the annotation file */
                if (($reader = fopen($annotfile, "r")) == FALSE) {
                    error(1, "Unable to access file.");
                    exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
                }
                // Check first line for header information
                if (($line = fgets($reader)) == FALSE) {
                    error(1, "Unable to locate header names on first line of file.");
                    exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
                }     
                $header = str_getcsv($line,"\t");
                 
                // Set up header column; all columns are required
                for ($x = 0; $x < count($header); $x++) {
                    switch ($header[$x] ) {
                        case 'Name':
                            $nameIdx = $x;
                            break;
                        case 'Marker_type':
                            $markerTypeIdx = $x;
                            break;
                        case 'Synonym_type':
                            $synonymTypeIdx = $x;
                            break;
                        case 'Annotation_type':
                            $annotationTypeIdx= $x;
                            break;
                        case 'Synonym':
                            $synonymIdx = $x;
                            break;
                        case 'Annotation':
                            $annotationIdx = $x;
                            break;
                    }
                }
  
                // Check if a required col is missing
                if ((!is_numeric($nameIdx)) || (!is_numeric($markerTypeIdx)) || (!is_numeric($synonymIdx)) ||
                    (!is_numeric($synonymTypeIdx)) || (!is_numeric($annotationIdx)) || (!is_numeric($annotationTypeIdx))) {
                    echo "ERROR: Missing One of these required Columns. Please correct it and upload again: <br> Name - ".$nameIdx.
                            "<br>"." Marker_type - ".$markerTypeIdx."<br>"." Synonym - ". $synonymIdx.
                            "<br>"." Synonym_type - ".$synonymTypeIdx."<br>"."Annotation - ".$annotationIdx.
                            "<br>"." Annotation_type - ". $annotationTypeIdx ."<br>";
                    exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
                }
                  
                // Store header
                $i = 0;
                foreach ($header as $value)  {                     
                    $storageArr[0][$i++] = $value;   
                }
                // Store individual records
                $i = 1;
                while(($line = fgets($reader)) !== FALSE) { 
                    if ((stripos($line, '- cut -') > 0 )) {
                        $error_flag = 0; 
                        break;
                    }
                                        
                    if (trim($line) == '') {
                        continue;
                    }
		    
                    if ($i  > 50) {
                          break;
                    }
                    $j = 0;
                    $data = str_getcsv($line,"\t");
                    
                    //Check for junk line
                    if (count($data) != 6) {
                        echo "ERROR DETECT: Line does not contain 6 columns.". "<br/>". $line . "<br/>" ;
                        exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
                    }    
                                            
                    foreach ($data as $value)  {
                        //echo $value."<br>";
                        if (($j == $nameIdx) && ($value == "")){
                            $error_flag = 1;
                        }
                        $storageArr[$i][$j++] = $value;   
                    }
                    $i ++;
                }  
                unset ($value);
                fclose($reader);   

                if ($error_flag > 0) {
                    echo "ERROR DETECT: The 'Name' field cannot be blank"."<br/>";
                    exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
                } else {
                    // display input data into table for validation 
                    echo "<h3>Here is a sample of the first 50 lines of data from the uploaded Annotation File</h3>";
                    echo "<table >";
                    echo "<thead>";
                    echo "<tr>";
                    echo "<th >" . $storageArr[0][$nameIdx] . "</th>";
                    echo "<th >" . $storageArr[0][$markerTypeIdx] . "</th>";
                    echo "<th >" . $storageArr[0][$synonymIdx] . "</th>";
                    echo "<th >" . $storageArr[0][$synonymTypeIdx] . "</th>";
                    echo "<th >" . $storageArr[0][$annotationIdx] . "</th>";
                    echo "<th >" . $storageArr[0][$annotationTypeIdx] . "</th>";
                    echo "</tr>"."<br/>";
                    echo "<thead>"."<br/>";
                    ?>                   
                    <tbody style="padding: 0; height: 200px; width: 4000px; overflow: scroll; ">	
                    <?php
                    for ($i = 1; $i <= count($storageArr) ; $i++)  {
                        //Extract data
                    ?>
                        <tr>
                        <td >
                        <?php $newtext = wordwrap($storageArr[$i][$nameIdx], 10, "<br>", true);  echo $newtext ?>
                        </td> 
                        <td >
                        <?php $newtext = wordwrap($storageArr[$i][$markerTypeIdx], 20, "<br>", true); echo $newtext ?>
                        </td>
                        <td>
                        <?php $newtext = wordwrap($storageArr[$i][$synonymIdx], 20, "<br>", true); echo $newtext ?>
                        </td> 
                        <td >
                        <?php $newtext = wordwrap($storageArr[$i][$synonymTypeIdx], 20, "<br>", true); echo $newtext ?>
                        </td> 
                        <td >
                        <?php $newtext = wordwrap($storageArr[$i][$annotationIdx], 30, "<br>", true); echo $newtext ?>
                        </td> 
                        <td >
                        <?php $newtext = wordwrap($storageArr[$i][$annotationTypeIdx], 20, "<br>", true); echo $newtext ?>
                        </td> 
                        </tr>
                    <?php
                    }/* end of for loop */
                    ?>
                    </tbody>
                    </table>
                    <br>
                    <input type="Button" value="Accept" 
                    onclick="javascript: update_databaseAnnot('<?php echo $annotfile?>','<?php echo $uploadfile?>','<?php echo $username?> ')"/>
                    <input type="Button" value="Cancel" onclick="history.go(-1); return;"/>
                    <?php
                }
		    } // end of if(move_uploaded_file())
		    else {
		      echo "<b>Error</b>: Couldn't save file in curator_data/uploads/ directory.<p>";
		      echo "<input type='Button' value='Cancel' onclick='history.go(-1); return;'/>";
		    }
	}
    } /* end of MarkersAnnot function*/

//**************************************************************

    private function type_LoadFile1() {
        $error_flag = 0;
        $row = loadUser($_SESSION['username']);
                $username=$row['name'];
                $username = preg_replace("/\s/", "", $username);
                $tmp_dir="./uploads/tmpdir_".$username."_".rand();
        $infile = "";
        umask(0);

        if(!file_exists($tmp_dir) || !is_dir($tmp_dir)) {
            mkdir($tmp_dir, 0777);
        }

        $target_path=$tmp_dir."/";
        $uploadfile = $_FILES['file']['name'][1];
        $ext = substr(strrchr($uploadfile, '.'), 1);

        $overwrite = NULL;
        $uftype = $_FILES['file']['type'][1];
        //Read header to check if Golden Gate or Infinium
        if(move_uploaded_file($_FILES['file']['tmp_name'][1], $target_path.$uploadfile))    {
          echo "uploaded file $uploadfile<br>\n";
          $infile = $target_path.$uploadfile;
          if (($reader = fopen($infile, "r")) == FALSE) {
            error(1, "Unable to access file.");
            exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
          }
          $fileFormat = 0;
          $fileFormatName = "generic";
          $numberColumns = 5;
          while(!feof($reader)) {
            $line = fgets($reader);
            if (stripos($line, 'Golden Gate') !== false) {
              $fileFormat = 1;
              $numberColumns = 4;
              $fileFormatName = "Golden Gate";
            } elseif (stripos($line, 'Infinium HD') !== false) {
              $fileFormat = 2;
              $numberColumns = 4;
              $fileFormatName = "Infinium HD";
            } elseif (stripos($line, 'DArT') !== false) {
              $fileFormat = 3;
              $numberColumns = 3;
              $fileFormatName = "DArT";
            }
          }
          fclose($reader);
        } else {
          error(1, "Unable to upload file to tempory location.");
          exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
        }
        //Illumina format
        if ($fileFormat == 1) {
            $infile = $target_path.$uploadfile;
            // Convert it to generic format
            $cmd = "perl AB_to_ATCG.pl \"$infile\"";
            //echo "Cmd - " . $cmd . "<br>";
            exec($cmd);
            $infile = $target_path.$uploadfile.".txt";
            if (!file_exists($infile)) {
                  echo "Using $fileFormatName file format.<br>\n";
                  error(1, "Conversion of .opa file failed.");
                  exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
            }
        } elseif ($fileFormat == 2) {
            $infile = $target_path.$uploadfile;
            // Convert it to generic format
            $cmd = "perl AB_to_ATCG_Infinium.pl \"$infile\"";
            //echo "Cmd - " . $cmd . "<br>";
            exec($cmd);
            $infile = $target_path.$uploadfile.".txt";
            if (!file_exists($infile)) {
                  echo "Using $fileFormatName file format.<br>\n";
                  error(1, "Conversion of manifest file failed.");
                  exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
            }

        // DArT format
        } elseif ($fileFormat == 3) {
            $infile = $target_path.$uploadfile;

        // Generic SNP format
        } else {
                /* start reading the input */
                $infile = $target_path.$uploadfile;
        }
        ?>
        <input type=hidden name="check_seq" id="use_imp" value="">
        <input type=hidden name="orderAllele" id="order_yes" value"">
        <script type="text/javascript">
        if ( window.addEventListener ) {
            window.addEventListener( "load", CheckSynonym('<?php echo $infile?>','<?php echo $uploadfile?>','<?php echo $username?>','<?php echo $fileFormat?>','<?php echo $_POST['blast']?>'), false );
        } else if ( window.onload ) {
            window.onload = "CheckSynonym('<?php echo $infile?>','<?php echo $uploadfile?>','<?php echo $username?>','<?php echo $fileFormat?>','<?php echo $_POST['blast']?>')";
        }
        </script>
        <?php
    }

    private function type_MarkersSNP2() {
        global $config;
        ?>
        <style type="text/css">
                th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
                table {background: none; border-collapse: collapse}
                td {border: 0px solid #eee !important;}
                h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
        </style>

        <style type="text/css">
        table.marker {background: none; border-collapse: collapse}
        th.marker { background: #5b53a6; color: #fff; padding: 5px 0; border: 0; }
        td.marker { padding: 5px 0; border: 0 !important; }
        </style>
        <?php

        $infile = $_GET['linedata'];
        $uploadfile = $_GET['file_name'];
        $fileFormat = $_GET['file_type'];
        $overwrite = $_GET['overwrite'];
        $expand = $_GET['expand'];
        $orderAllele = $_GET['orderAllele'];

        $emailAddr = $_SESSION['username'];
        $row = loadUser($_SESSION['username']);
        $username=$row['name'];
        $username = preg_replace("/\s/", "", $username);
        $target_path="uploads/".$username."_".rand();
        $target_path = substr($infile, 0, strrpos($infile, '/')+1);
        $processOut = "$target_path/genoProc.out";
        $tPath = str_replace('./', '', $target_path);
        $outfile1 = preg_replace("/(\.)/", '_filtered.', $uploadfile);
        $outfile2 = preg_replace("/(\.)/", '_synonym.', $uploadfile);
        $urlAddr = $config['base_url'] . "curator_data/$tPath";

        $cmd = "/usr/bin/php check_synonym_offline.php $infile $emailAddr $urlAddr> ". $processOut . " &";
        echo "Running BLAST to compare import file to markers sequences in database.<br>\n";
        echo "The results files for this analysis are<br><br>\n";
        echo "$outfile1 - import file with entries matching to markers in the database removed.<br>\n";
        echo "$outfile2 - markers that match to existing entries.(Formated as Marker Annotation file)<br><br>\n";
        echo "An email will be sent to $emailAddr when completed.<br><br>\n";
        echo "<a href=\"curator_data/$target_path\" target=\"_new\">Working files</a><br>\n";
        //echo "$cmd<br>\n";
        exec($cmd);
    }


    private function type_MarkersSNP() {
	?>
	<style type="text/css">
		th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
		table {background: none; border-collapse: collapse}
		td {border: 0px solid #eee !important;}
		h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
	</style>
		
	<style type="text/css">
        table.marker {background: none; border-collapse: collapse}
        th.marker { background: #5b53a6; color: #fff; padding: 5px 0; border: 0; }
        td.marker { padding: 5px 0; border: 0 !important; }
    </style>
<?php

        //$t = microtime();
        //echo "start MarkersSNP $t<br>\n";
        $error_flag = 0;
        $row = loadUser($_SESSION['username']);
		$username=$row['name'];
		$tmp_dir="./uploads/tmpdir_".$username."_".rand();
	
        if (empty($_GET['linedata'])) {
            $infile = "";
            echo "missing data file\n";
        } else {
            $infile = $_GET['linedata'];
            $uploadfile = $_GET['file_name'];
            $fileFormat = $_GET['file_type'];
            $overwrite = $_GET['overwrite'];
            $expand = $_GET['expand'];
            $orderAllele = $_GET['orderAllele'];
        }
        if ($fileFormat == 0) {
            $fileFormatName = "generic";
        }
        if ($overwrite) {
            $checked_imp = "checked";
            $checked_db = "";
        } else {
            $checked_imp = "";
            $checked_db = "checked";
        }
        if ($orderAllele) {
            $order_yes = "checked";
            $order_no = "";
        } else {
            $order_yes = "";
            $order_no = "checked";
        }

        /* Read the file */
        if (($reader = fopen($infile, "r")) == FALSE) {
            error(1, "Unable to access file.");
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
          
        // Store header
        //$i = 0;
        //foreach ($header as $value)  {                     
        //    $storageArr[0][$i++] = $value;   
        //}
        // Store individual records
        $i = 1;
        while (($line = fgets($reader)) !== FALSE) { 
            if (strlen($line) < 2) continue;
            
            if (empty($line)) {
                continue;
            }
            //if ($i  > 50) {
            //    break;
            //}
            $j = 0;
            $data = str_getcsv($line,",");
            
            //Check for junk line
            if (count($data) < $numberColumns) {
                echo "Using $fileFormatName file format<br>\n";
                echo "ERROR DETECT: Line does not contain $numberColumns columns.". "<br/>". $line . "<br/>" ;
                exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
            } 
                                    
            foreach ($data as $value)  {
                //echo $value."<br>";
                if (($j == $nameIdx) && ($value == "")){
                    $error_flag = 1;
                }
                $storageArr[$i][$j++] = $value;   
            }
            //$storageArr1[$i] = $data[0];
            //$storageArr2[$i] = $data[4];
            if (feof($reader)) {
                break;
            } else {
                $i ++;
            }
        }  
        unset ($value);
        fclose($reader); 
        ?>
        <h3>Options</h3>
        <table>
	  <tr>
            <td><input type=radio name="check_seq" id="use_db" value="db" <?php echo $checked_db ?>
            onclick="javascript: CheckSynonym('<?php echo $infile?>','<?php echo $uploadfile?>','<?php echo $username?>','<?php echo $fileFormat?>')"
            > No
        <input type=radio name="check_seq" id="use_imp" value="imp" <?php echo $checked_imp ?>
            onclick="javascript: CheckSynonym('<?php echo $infile?>','<?php echo $uploadfile?>','<?php echo $username?>','<?php echo $fileFormat?>')"
            > Yes
	    <td>If the sequence matches, add the marker as a synonym.<br>The import squence is compared to existing markers to find an exact match. This is very fast but does not find matches where one sequence is shorter than the other<br>
              Check <b>Yes</b> unless the marker names have been published or have mapping data.
        <?php
        if ($fileFormat == 0) {
        ?>
        <tr>
        <td><input type=radio name="check_ord" id="order_no" value="no" <?php echo $order_no ?>
            onclick="javascript: CheckSynonym('<?php echo $infile?>','<?php echo $uploadfile?>','<?php echo $username?>','<?php echo $fileFormat?>')"
            > No
        <input type=radio name="check_ord" id="order_yes" value="yes" <?php echo $order_yes ?>
            onclick="javascript: CheckSynonym('<?php echo $infile?>','<?php echo $uploadfile?>','<?php echo $username?>','<?php echo $fileFormat?>')"
           > Yes
	<td>Order A and B alleles alphabetically.<br>
	  Check <b>Yes</b> unless a reference genome sequence was used to anchor the markers.
        <?php
        }
        ?>
        </table>
        <h3>Validation Details</h3>
        <?php
        if ($overwrite) {
          ?>
          <ul>The marker name in the import file is compared to pre-existing markers.
            <ul><li>If found, the database entry will be updated.</li>
                <li>Otherwise the marker sequence is compared to pre-existing markers.</li>
	    <ul><li>If found, the marker will be added as a synonym.
                    <li>Otherwise the new marker will be added.<br>
                </ul>
           </ul>
          
        <?php
        } else {
          ?>
          <ul>The marker name in the import file is compared to pre-existing markers.
            <ul><li>If found, the database entry will be updated.</li>
                <li>Otherwise the new marker will be added.<br>
            </ul>
         
          <?php
        }
        /* if ($orderAllele) { */
        /*     echo "<li>Order A and B alleles alphabetically"; */
        /* } */
        /* echo "<li>Check duplicates within import file</li>"; */
        echo "</ul>";
        echo "<h3>Results</h3>\n";
        if ($overwrite) {
            $numMatch = $this->typeCheckSynonym($storageArr, $nameIdx, $sequenceIdx, $overwrite, $expand);
        }
        if (($fileFormat == 0) && ($orderAllele)) {
            $this->typeCheckAlleleOrder($storageArr, $nameIdx, $alleleAIdx, $alleleBIdx, $sequenceIdx);
        }
        $this->typeCheckImport($storageArr, $nameIdx, $sequenceIdx, $overwrite, $expand);
        if ($numMatch["dupSeq"] > 0) {
        } else {
            ?>
            <input type=hidden name="check_seq" id="use_imp" value="">
            <?php
        }
        if ($error_flag > 0)  {
            echo "ERROR DETECT: The 'marker_name' field cannot be blank"."<br/>";
            exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
        } 	else {
            // display input data into table for validation 
            ?>
            <h3>Here is a sample of the first 50 lines of data from the import file</h3>
            <input type="Button" value="Accept" 
            onclick="javascript: update_databaseSNP('<?php echo $infile?>','<?php echo $uploadfile?>','<?php echo $username?>','<?php echo $fileFormat?>')"/>
            <input type="Button" value="Cancel" onclick="history.go(-1); return;"/>
            <table >
            <?php
            echo "<thead>";
            echo "<tr>";
            echo "<th >" . $storageArr[0][$nameIdx] . "</th>";
            if ($fileFormat == 0) {
                echo "<th >" . $storageArr[0][$typeIdx] . "</th>";
            }
            echo "<th >" . $storageArr[0][$alleleAIdx] . "</th>";
            echo "<th >" . $storageArr[0][$alleleBIdx] . "</th>";
            echo "<th >" . $storageArr[0][$sequenceIdx] . "</th>";
            echo "</tr>"."<br/>";
            echo "</thead>"."<br/>";
            ?>                   
            <tbody style="padding: 0; height: 200px; width: 4000px;  overflow: scroll;">	
            <?php
            $limit = count($storageArr);
            if ($limit > 50) {
                $limit = 50;
            }
            for ($i = 1; $i <= $limit; $i++)  {
                //Extract data
            ?>
                <tr>
                <td >
                <?php $newtext = wordwrap($storageArr[$i][$nameIdx], 20, "<br>", true);  echo $newtext ?>
                </td> 
                <?php
                if ($fileFormat == 0) {
                    $newtext = wordwrap($storageArr[$i][$typeIdx], 10, "<br>", true);
                    echo "<td>$newtext</td>";
                }
                ?>    
                <td >
                <?php $newtext = wordwrap($storageArr[$i][$alleleAIdx], 10, "<br>", true); echo $newtext ?>
                </td>
                <td>
                <?php $newtext = wordwrap($storageArr[$i][$alleleBIdx], 10, "<br>", true); echo $newtext ?>
                </td> 
                <td >
                <?php $newtext = wordwrap($storageArr[$i][$sequenceIdx], 50, "<br>", true); echo $newtext ?>
                </td> 
                </tr>
            <?php
            }/* end of for loop */
            ?>
            </tbody>
            </table>
            </div>
            <?php
        }
    } /* end of MarkersSNP function*/
 

//**************************************************************
     private function type_DatabaseAnnot() {
 
        global $config;
        include($config['root_dir'] . 'theme/admin_header.php');

        //echo "You are in DB portion of annotation import.<br>";
 
       $datafile = $_GET['linedata'];
       $filename = $_GET['file_name'];
       $username = $_GET['user_name'];
        
        //echo "datafile = ".  $datafile  . "<br>";
        //echo "filename = " . $filename . "<br>";
        //echo "username = " . $username . "<br>";
        //exit(0);
        
        $mTypeHash = array ();
        $mAnnotTypeHash = array ();
        $mSynmTypeHash = array ();
        
        $linkID = connect();  
        
        // Setup hash table for the various type lookup
        $sql = "SELECT marker_type_uid, marker_type_name 
            FROM marker_types";
        $res = mysql_query($sql) or die("Database Error: Marker types lookup - ".mysql_error() ."<br>".$sql);
        while ($row = mysql_fetch_assoc($res)) {
           $tempStr =  strtolower($row['marker_type_name']);
           $mTypeHash[$tempStr] =  $row['marker_type_uid'];     
        }
        
        //print_r($mTypeHash);
        //echo "<br>";
        
        $sql = "SELECT marker_annotation_type_uid, name_annotation 
            FROM marker_annotation_types";
        $res = mysql_query($sql) or die("Database Error: Marker annotation types lookup - ".mysql_error() ."<br>".$sql);
        while ($row = mysql_fetch_assoc($res)) {
            $tempStr =  strtolower($row['name_annotation']);
            $mAnnotTypeHash[$tempStr] = $row['marker_annotation_type_uid'];     
        }
        //print_r($mAnnotTypeHash);
        //echo "<br>";
         
        $sql = "SELECT marker_synonym_type_uid, name 
            FROM marker_synonym_types";
        $res = mysql_query($sql) or die("Database Error: Marker synonym types lookup - ".mysql_error() ."<br>".$sql);
        while ($row = mysql_fetch_assoc($res)) {
            $tempStr =  strtolower($row['name']);
            $mSynmTypeHash[$tempStr] = $row['marker_synonym_type_uid'];     
        }
        //print_r( $mSynmTypeHash);
        //echo "<br>";

        //exit(0);
         
        if (($reader = fopen($datafile, "r")) == FALSE) {
            error(1, "Unable to access file.");
            exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
        }
        // Check first line for header information
        if (($line = fgets($reader)) == FALSE) {
            error(1, "Unable to locate header names on first line of file.");
            exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
        }     
        $header = str_getcsv($line,"\t");
                    
        // Set up header column; all columns are required
        for ($x = 0; $x < count($header); $x++)  {
            switch ($header[$x] ) {
                case 'Name':
                    $nameIdx = $x;
                    break;
                case 'Marker_type':
                    $markerTypeIdx = $x;
                    break;
                case 'Synonym_type':
                    $synonymTypeIdx = $x;
                    break;
                case 'Annotation_type':
                    $annotationTypeIdx= $x;
                    break;
                case 'Synonym':
                    $synonymIdx = $x;
                    break;
                case 'Annotation':
                    $annotationIdx = $x;
                    break;
            }
        } 
        // Store individual records
        $i = 1;
        while (($line = fgets($reader)) !== FALSE) { 
	    if ( trim($line) == '') {
            	continue;
            }
            
            if ((stripos($line, '- cut -') > 0 )) {
                break;
            }
            $j = 0;
            $data = str_getcsv($line,"\t");
                        
            //Check for junk line
            if (count($data) != 6) {
                echo "ERROR DETECT: Invalid number of columns in line $i.<br/>";
				echo "The offending row contains:<br>\"$line\"<br>";
                print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-2); return;\"><br>";
				exit;
            }     
                                                
            foreach ($data as $value) {
                //echo $value."<br>";
                $storageArr[$i][$j++] = trim($value);   
            }
        
	        $lastmarker = $data[0];
	        $lastline = $i-1;
                $i++;
        }  
        unset ($value);
        fclose($reader);   

        //cache the marker and synonym names
        $sql = "SELECT marker_uid, marker_name FROM markers";
        $res = mysql_query($sql) or die("Database Error: marker name lookup - ".mysql_error() ."<br>".$sql);
        while ($rdata = mysql_fetch_assoc($res)) {
            $m_uid=$rdata['marker_uid'];
            $m_nam=$rdata['marker_name'];
            $markerNameLookup[$m_nam] = $m_uid;
        }
        $sql = "SELECT marker_uid, value FROM marker_synonyms";
        $res = mysql_query($sql) or die("Database Error: marker synonym name lookup - ".mysql_error() ."<br>".$sql);
        while ($rdata = mysql_fetch_assoc($res)) {
            $m_uid=$rdata['marker_uid'];
            $m_nam=$rdata['value'];
            $markerSynLookup[$m_nam] = $m_uid;
        }
 
        $curMarker = '';
        $markerUid = 0;

        for ($i = 1; $i <= count($storageArr); $i++) {

            $marker = $storageArr[$i][$nameIdx];
            $markerType = $storageArr[$i][$markerTypeIdx];
            $synonym = $storageArr[$i][$synonymIdx];
            $synonymType = $storageArr[$i][$synonymTypeIdx];
            $annotation = $storageArr[$i][$annotationIdx];
            $annotationType = $storageArr[$i][$annotationTypeIdx];
            
            if ($marker == "") continue;
            // handle repeating marker entries
            if (strcmp($marker, $curMarker) == 0) {
                
                $doMarker = 0;
                if (empty($synonym) ) {
                    $doSynonym = 0;
                } else {
                    $doSynonym = 1;
                }   
                if (empty($annotation)) {
                    $doAnnotation = 0;
                } else {
                    $doAnnotation = 1;
                }
            } else {
                $curMarker = $marker;
                $doMarker = 1;
                if (empty($annotation)) 
                    $doAnnotation = 0;
                else 
                    $doAnnotation = 1;

                if (empty($synonym))
                    $doSynonym = 0;
                else 
                    $doSynonym = 1;
            }
            
            if ($doMarker == 1 ) {
                $tmp = strtolower($markerType);
                $markerTypeID = $mTypeHash[$tmp];
                
                if (empty($markerTypeID)) {
                    echo "ERROR DETECT in marker type field:'  ". $markerType ."' has not been defined in the database.<br/> Please check your input file and resubmit it. <br><br><br>";
                    exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-2); return;\">");
                }
                
                //echo "Marker type - ".$markerType . " value = " . $markerTypeID . "<br>";  
                //Check to see if marker already exists
                $m_uid = null;
                if (isset($markerNameLookup[$marker])) {
                    $m_uid=$markerNameLookup[$marker];
                    // Check synomyn
                } elseif (isset($markerSynLookup[$marker])) {
                    $m_uid = $markerSynLookup[$marker];
                }
                // if no existing name or synonym 
                if (empty($m_uid)) {
                    $sql = "INSERT INTO markers (marker_type_uid, marker_name, updated_on, created_on)
                            VALUES ($markerTypeID,  '$curMarker', NOW(),  NOW())"; 
                    $res = mysql_query($sql) or die("Database Error: marker insert - ". mysql_error() ."<br>".$sql);
                    $sql = "SELECT marker_uid
                        FROM markers
                        WHERE marker_name = '$marker'";
                    $res = mysql_query($sql) or die("Database Error: marker uid lookup - ".mysql_error() ."<br>".$sql);
                    $rdata = mysql_fetch_assoc($res);
                    $markerUid = $rdata['marker_uid'];
                } else {
                    $sql = "UPDATE markers SET marker_type_uid = '$markerTypeID', updated_on = NOW()
                        WHERE marker_uid = '$m_uid'"; 
                    $res = mysql_query($sql) or die("Database Error: marker update - ". mysql_error() ."<br>".$sql);
                    $markerUid = $m_uid;
                }
            }
            
            if ($doSynonym == 1) {
                $tmp = strtolower($synonymType);
                $synonymTypeID = $mSynmTypeHash[$tmp];
                //echo "Synonym type - ".$synonymType . " value = " . $synonymTypeID . "<br>";  
                if (empty($synonymTypeID)) {
                    echo "ERROR DETECT in synonym type field: ". $synonymType . " has not been defined in the database.<br/> Please check your input file in " . $marker. " and resubmit it. <br><br><br>";
                    exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
                }
                //Check to see if synonym name already exists
                $sql = "SELECT marker_synonym_uid
                        FROM marker_synonyms
                        WHERE value = '$synonym'";
                $res = mysql_query($sql) or die("Database Error: marker synonym name lookup - ".mysql_error() ."<br>".$sql);
                $rdata = mysql_fetch_assoc($res);
                $mSynonym_uid=$rdata['marker_synonym_uid'];
                
                if (empty($mSynonym_uid)) {
                    $sql = "INSERT INTO marker_synonyms (marker_uid, marker_synonym_type_uid, value, updated_on)
                            VALUES ($markerUid, $synonymTypeID, '$synonym', NOW())"; 
                    $res = mysql_query($sql) or die("Database Error: marker synonym insert - ". mysql_error(). "<br>".$sql);
                } else {
                    $sql = "UPDATE marker_synonyms SET marker_uid = '$markerUid', 
                        marker_synonym_type_uid = '$synonymTypeID', updated_on = NOW()
                        WHERE marker_synonym_uid = '$mSynonym_uid'"; 
                    $res = mysql_query($sql) or die("Database Error: marker synonym update - ". mysql_error() ."<br>".$sql);
                }
            }
            
            if ($doAnnotation == 1) {
                $tmp = strtolower($annotationType);
                $annotTypeID = $mAnnotTypeHash[$tmp];
                if (empty($annotTypeID)) {
                    echo "ERROR DETECT in annotation field: ". $annotationType . " has not been defined in the database.<br/> Please check your input file and resubmit it. <br><br><br>";
                    exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
                }
                
                //Check to see if annot value already exists
                // 
                $pos = stripos($annotationType, 'description');
                if ($pos > 0) {
                    $sql = "SELECT marker_annotation_uid
                            FROM marker_annotations
                            WHERE marker_uid = '$markerUid' AND marker_annotation_type_uid = '$annotTypeID'";
                    $res = mysql_query($sql) or die("Database Error: marker annotation lookup - ".mysql_error() ."<br>".$sql);
                    $rdata = mysql_fetch_assoc($res);
                } else {
                    $sql = "SELECT marker_annotation_uid
                            FROM marker_annotations
                            WHERE value = '$annotation' AND marker_uid = '$markerUid' AND marker_annotation_type_uid = '$annotTypeID'";
                    $res = mysql_query($sql) or die("Database Error: marker annotation lookup - ".mysql_error() ."<br>".$sql);
                    $rdata = mysql_fetch_assoc($res);
                }
                $mAnnot_uid = $rdata['marker_annotation_uid'];
                
                if (empty($mAnnot_uid)) {
                    $sql = "INSERT INTO marker_annotations (marker_uid, marker_annotation_type_uid, value, updated_on, created_on)
                            VALUES ($markerUid, $annotTypeID, '$annotation', NOW(), NOW())"; 
                    $res = mysql_query($sql) or die("Database Error: marker annotation insert - ". mysql_error() ."<br>".$sql);
                } else {
                    $sql = "UPDATE marker_annotations SET marker_uid = '$markerUid', 
                        marker_annotation_type_uid = '$annotTypeID', value = '$annotation', updated_on = NOW()
                        WHERE marker_annotation_uid = '$mAnnot_uid'"; 
                    $res = mysql_query($sql) or die("Database Error: marker annotation update - ". mysql_error() ."<br>".$sql);
                }
                echo "$sql<br>\n";
            }
        }
        echo " <b>The Data is inserted/updated successfully </b><br>";
    	echo "$lastline lines read, last marker = $lastmarker.<br>";
    	echo "Size of storageArr = ".count($storageArr);
        echo "<br/><br/>";
?>
        <a href="./curator_data/markers_upload.php"> Go Back To Main Page </a>
    <?php
        $sql = "SELECT input_file_log_uid from input_file_log 
            WHERE file_name = '$filename'";
        $res = mysql_query($sql) or die("Database Error: input_file lookup  - ". mysql_error() ."<br>".$sql);
        $rdata = mysql_fetch_assoc($res);
        $input_uid = $rdata['input_file_log_uid'];
        
         if (empty($input_uid)) {
            $sql = "INSERT INTO input_file_log (file_name,users_name, created_on)
                VALUES('$filename', '$username', NOW())";
		} else {
            $sql = "UPDATE input_file_log SET users_name = '$username', created_on = NOW()
                 WHERE input_file_log_uid = '$input_uid'"; 
        }
        $lin_table = mysql_query($sql) or die("Database Error: Log record insertion failed - ". mysql_error() ."<br>".$sql);
        $footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
    } /* end of function type_databaseAnnot */
 
 //**************************************************************
    private function type_DatabaseSNP() {

        global $config;
        $progPath = realpath(dirname(__FILE__).'/../').'/';
        
        //echo "You are in DB portion of SNP import." . "<br>";
         
        $datafile = $_GET['linedata'];
        $filename = $_GET['file_name'];
        $username = $_GET['user_name'];
        $fileFormat = $_GET['file_type'];
        $overwrite = $_GET['overwrite'];  //overwrite = 1 means check for sequence match. If match found then add marker as synonym. If marker not loaded then create.
                                          //overwrite = 0 means do not check sequence. If marker not already loaded give error
        $orderAllele = $_GET['orderAllele'];  //orderAllele = 1 mean check for alphabetical order of alleles
        
        //echo "datafile = ".  $datafile  . "<br>";
        //echo "filename = " . $filename . "<br>";
        //echo "username = " . $username . "<br>";
        //exit(0);

        if (($reader = fopen($datafile, "r")) == FALSE) {
            error(1, "Unable to access file.");
            exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
       }
        //Advance to data header area
        while(!feof($reader)) {
            $line = fgets($reader);
            if (stripos($line, 'marker_name') !== false) break;    
        }
        
        if (feof($reader)) {
            error(1, "Unable to locate genotype header line.");
            exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
        }
        $header = str_getcsv($line,",");
         
        // Set up header column; all columns are required
        $nameIdx = implode(find("marker_name", $header),"");
        $typeIdx = implode(find("marker_type", $header),"");
        $alleleAIdx = implode(find("A_allele", $header),"");
        $alleleBIdx = implode(find("B_allele", $header),"");
        $sequenceIdx = implode(find("sequence", $header),"");
 
        $sql = "select marker_synonym_type_uid, name from marker_synonym_types"; 
        $res = mysql_query($sql) or die("Database Error: Marker synonym lookup - ".mysql_error() ."<br>".$sql);
        while ($rdata = mysql_fetch_assoc($res)) {
            $name = $rdata['name'];
            $sTypeHash[$name] = $rdata['marker_synonym_type_uid'];            
        }

        // Store individual records
        $i = 1;
        while(($line = fgets($reader)) !== FALSE) { 
            if ( trim($line) == '') {
                continue;
            }
            
            $j = 0;
            $data = str_getcsv($line,",");
                        
            //Check for junk line
            //if (count($data) != 4 )  break;   
                                                
            foreach ($data as $value)  {
                //echo $value."<br>";
                if ($fileFormat == 3) {
                } elseif (empty($value)) {
                    $error_flag = 1;
                }
                $storageArr[$i][$j++] = trim($value);   
            }
            if (feof($reader)) {
                break;
            } else {
              $i ++;
            }
        }  
        unset ($value);
        fclose($reader);   
        $expand = 0;

        echo "<h3>Check import file</h3>\n";
        if ($overwrite) {
          $this->typeCheckSynonym($storageArr, $nameIdx, $sequenceIdx, $overwrite, $expand);
        }
        if (($fileFormat == 0) && ($orderAllele == 1)) {
          $this->typeCheckAlleleOrder($storageArr, $nameIdx, $alleleAIdx, $alleleBIdx, $sequenceIdx);
        }
        flush();
        $this->typeCheckImport($storageArr, $nameIdx, $sequenceIdx, $overwrite, $expand);
        flush();

        //cache the marker and synonym names
        $sql = "SELECT marker_uid, marker_name FROM markers";
        $res = mysql_query($sql) or die("Database Error: marker name lookup - ".mysql_error() ."<br>".$sql);
        while ($rdata = mysql_fetch_assoc($res)) {
            $m_uid=$rdata['marker_uid'];
            $m_nam=$rdata['marker_name'];
            $markerNameLookup[$m_nam] = $m_uid;
        }
        $sql = "SELECT marker_uid, value FROM marker_synonyms";
        $res = mysql_query($sql) or die("Database Error: marker synonym name lookup - ".mysql_error() ."<br>".$sql);
        while ($rdata = mysql_fetch_assoc($res)) {
            $m_uid=$rdata['marker_uid'];
            $m_nam=$rdata['value'];
            $markerSynLookup[$m_nam] = $m_uid;
        }

        ?>
        <br><h3>Loading import file into database</h3>
        <?php
  
        $count_added = 0; 
        $count_added_syn = 0;
        if ($error_flag > 0)  {
            echo "ERROR DETECT: One or more fields contained blank values"."<br/>";
            print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
        } 	else {
            $linkID = connect(); 
            $missing = 0;
            for ($i = 1; $i <= count($storageArr) ; $i++)  {

                $marker = $storageArr[$i][$nameIdx];
                $markerType = $storageArr[$i][$typeIdx];
                $alleleA = $storageArr[$i][$alleleAIdx];
                $alleleB = $storageArr[$i][$alleleBIdx];
                $sequence = $storageArr[$i][$sequenceIdx];
                $synonym = $storageArr[$i]["syn"];
                
                if ($missing > 50 ) {
                    error(1, "There are too many invalid marker names. <br> Please fix and re-import." );
                    exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
                }
 
                //Check to see if marker already exists
                $marker_uid = null;
                if (isset($markerNameLookup[$marker])) {
                    $marker_uid=$markerNameLookup[$marker];
                    // Check synomyn
                } elseif (isset($markerSynLookup[$marker])) {
                    $mmarker_uid = $markerSynLookup[$marker];
                }
               
                //Check if duplicate name or seq within import file
                if (($synonym == "skip duplicate name") || ($synonym == "skip duplicate seq")) { 
                    echo "$synonym $marker<br>\n";
                    continue;
                //Check to see if synonym name already exists
                } elseif (empty($marker_uid) && $overwrite && ($synonym != "")) {
                        if (isset($sTypeHash["GBS sequence tag"])) {
                            $synonymTypeID = $sTypeHash["GBS sequence tag"];
                        } else {
                            $sql = "insert into marker_synonym_types (name, comments) values (\"GBS sequence tag\", \"N/A\")";
                            $res = mysql_query($sql) or die("Database Error: marker synonym insert - ". mysql_error(). "<br>".$sql);
                            echo "$sql<br>\n";
                            $synonymTypeID = mysql_insert_id();
                        }
                        $sql = "select marker_uid from markers where marker_name = \"$synonym\"";
                        $res = mysql_query($sql) or die("Database Error: marker synonym insert - ". mysql_error(). "<br>".$sql);
                        if ($row = mysql_fetch_assoc($res)) {
                            $marker_uid = $row['marker_uid'];
                        } else {
                            die("Error: could not find synonym entry for $synonym<br>$sql\n");
                        }
                        $sql = "SELECT marker_synonym_uid
                        FROM marker_synonyms
                        WHERE value = '$marker'";
                        $res = mysql_query($sql) or die("Database Error: marker synonym name lookup - ".mysql_error() ."<br>".$sql);
                        $rdata = mysql_fetch_assoc($res);
                        $mSynonym_uid=$rdata['marker_synonym_uid'];
 
                        if (empty($mSynonym_uid)) {
                            $sql = "INSERT INTO marker_synonyms (marker_uid, marker_synonym_type_uid, value, updated_on)
                            VALUES ($marker_uid, $synonymTypeID, '$marker', NOW())";
                            $res = mysql_query($sql) or die("Database Error: marker synonym insert - ". mysql_error(). "<br>".$sql);
                            $count_added_syn++;
                        } else {
                            echo "skipping marker $marker marker_uid $marker_uid synonym $marker, already in database<br>\n";
                        }
                } elseif (empty ($marker_uid) && ($typeIdx != "")) {
                    $sql = "SELECT marker_type_uid, marker_type_name
                    FROM marker_types";
                    $res = mysql_query($sql) or die("Database Error: Marker types lookup - ".mysql_error() ."<br>".$sql);
                    while ($row = mysql_fetch_assoc($res)) {
                        $tempStr = $row['marker_type_name'];
                        $mTypeHash[$tempStr] =  $row['marker_type_uid'];
                    }
                    if (isset($mTypeHash["$markerType"])) {
                        $markerTypeID = $mTypeHash["$markerType"];
                        $sql = "insert into markers (marker_type_uid, marker_name, A_allele, B_allele, sequence, updated_on, created_on) 
                        values ($markerTypeID, \"$marker\", \"$alleleA\", \"$alleleB\", \"$sequence\", NOW(), NOW())";
                        $res = mysql_query($sql) or die("Database Error: " . mysql_error() . "<br>$sql");
                        $marker_uid = mysql_insert_id();
                        //echo "$sql<br>\n";
                        $count_added++;
                    } else {
                        error(1, "Marker type - $markerType not defined in DB for $marker. <br> Skipping entry ...");
                        $missing++;
                        continue;
                    }
                } else {
                    $sql = "UPDATE markers SET A_allele = '$alleleA', B_allele='$alleleB', sequence='$sequence', updated_on=NOW() 
                            WHERE marker_uid = '$marker_uid'";
                    $res = mysql_query($sql) or die("Database Error: SNP sequence update failed - ". mysql_error() ."<br>".$sql);
                    //echo "update $marker_uid<br>$sql<br>\n";
                }
                  
                // marker name don't exist in DB
                if (empty ($marker_uid)) {
                    error(1, "Marker name - ". $marker . " does not exist in DB. <br> Skipping this entry ..." );
                    $missing++;
                    continue;
                }
                //echo "marker_uid ".$marker_uid."<br>";
            }
            if ($count_added > 0) {
                echo "$count_added markers added to database<br>\n";
            }
            if ($count_added_syn > 0) {
                echo "$count_added_syn synonyms added to database<br>\n";
            }
            echo " <b>The Data is inserted/updated successfully </b><br>";
            echo "<br/><br/>";
        }
?>
        <a href="./curator_data/markers_upload.php"> Go Back To Main Page </a>
<?php
        $sql = "SELECT input_file_log_uid from input_file_log 
            WHERE file_name = '$filename'";
        $res = mysql_query($sql) or die("Database Error: input_file lookup  - ". mysql_error() ."<br>".$sql);
        $rdata = mysql_fetch_assoc($res);
        $input_uid = $rdata['input_file_log_uid'];
        
         if (empty($input_uid)) {
            $sql = "INSERT INTO input_file_log (file_name,users_name, created_on)
                VALUES('$filename', '$username', NOW())";
        } else {
            $sql = "UPDATE input_file_log SET users_name = '$username', created_on = NOW()
                        WHERE input_file_log_uid = '$input_uid'"; 
        }
        $lin_table = mysql_query($sql) or die("Database Error: Log record insertion failed - ". mysql_error() ."<br>".$sql);
        echo "<br><br>Running update of BLAST database<br>\n";
        $cmd = "/usr/bin/php " . $progPath . "curator_data/format-fasta.php";
        exec($cmd, $output);
        foreach ($output as $line) {
            echo "$line<br>\n";
        }

    } /* end of function type_databaseSNP */
    
} /* end of class */
  
?>

