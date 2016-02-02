<?php
/**
 * Download Gateway New
 *
 * PHP version 5.3
 * Prototype version 1.5.0
 *
 * @author  Clay Birkett <clb343@cornell.edu>
 * @license http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link    http://triticeaetoolbox.org/wheat/downloads/downloads.php
 *
 */
// |                                                                      |
// | The purpose of this script is to provide the user with an interface  |
// | for downloading certain kinds of files from T3.                     |

/** Using a PHP class to implement the "Download Gateway" feature
 *
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/downloads/downloads.php
 **/

namespace T3;

class Downloads
{
    /**
     * delimiter used for output files
     */
    public $delimiter = "\t";
    
    /**
     * Using the class's constructor to decide which action to perform
     *
     * @param string $function action to perform
     */
    public function __construct($function = null)
    {
        switch ($function) {
            case 'step1lines':
                $this->step1_lines();
                break;
            case 'step2lines':
                $this->step2_lines();
                break;
            case 'step3lines':
                $this->step3_lines();
                break;
            case 'step4lines':
                $this->step4_lines();
                break;
            case 'step5lines':
                $this->step5_lines();
                break;
            case 'step6lines':
                $this->step6_lines();
                break;
            case 'step1yearprog':
                $this->step1_yearprog();
                break;
            case 'type1build_tassel_v3':
                echo $this->type1_build_tassel_v3();
                break;
            case 'step2lines':
                echo $this->step2_lines();
                break;
            case 'searchLines':
                echo $this->step1_search_lines();
                break;
            case 'download_session_v4':
                echo $this->type1_session(V4);
                break;
            case 'download_session_v5':
                echo $this->type1_session(V5);
                break;
            case 'download_session_v6':
                echo $this->type1_session(V6);
                break;
            case 'download_session_v7':
                echo $this->type1_session(V7);
                break;
            case 'download_session_v8':
                echo $this->type2_session(V8);
                break;
            case 'download_session_v9':
                echo $this->type2_session(V9);
                break;
            case 'refreshtitle':
                echo $this->refresh_title();
                break;
            case 'verifyLines':
                echo $this->verifyLines();
                break;
            default:
                $this->type1Select();
                break;
        }
    }

    /**
     * load header and footer then check session to use existing data selection
     *
     * @return null
     */
    private function type1Select()
    {
        global $config;
        include $config['root_dir'].'theme/normal_header.php';
        $this->type1Checksession();
        ?>
        <link rel="stylesheet" href="//code.jquery.com/ui/1.11.0/themes/smoothness/jquery-ui.css">
        <script type="text/javascript" src="downloads/downloadsjq04.js"></script>
        <?php
        include $config['root_dir'].'theme/footer.php';
    }

    /**
     * Checks the session variable, if there is lines data saved then go directly to the lines menu
     *
     * @return null
     */
    private function type1Checksession()
    {
        ?>
        <div id="title">
	<?php
        $phenotype = "";
        $lines = "";
        $markers = "";
        $saved_session = "";
        $message1 = $message2 = "";
        $download_geno = "";
        $download_genoe = "";
        $download_pheno = "";

        $typeG = $_GET['typeG'];
        $typeGE = $_GET['typeGE'];
        if (isset($_SESSION['phenotype'])) {
            $tmp = count($_SESSION['phenotype']);
            if ($tmp==1) {
                $saved_session = "$tmp phenotype ";
            } else {
                $saved_session = "$tmp phenotypes ";
            }
            $message2 = "download phenotype and genotype data";
            $phenotype = $_SESSION['phenotype'];
            $download_pheno = "checked";
        } else {
            $message1 = "0 phenotypes";
            $message2 = " download genotype data";
        }
        if (isset($_SESSION['selected_lines'])) {
            $countLines = count($_SESSION['selected_lines']);
            if ($saved_session == "") {
                $saved_session = "$countLines lines";
            } else {
                $saved_session = $saved_session . ", $countLines lines";
            }
            $lines = $_SESSION['selected_lines'];
            if (isset($_SESSION['geno_exps'])) {
                $download_genoe = "checked";
            } else {
                $download_geno = "checked";
            }
        }
        if (isset($_SESSION['clicked_buttons'])) {
             $tmp = count($_SESSION['clicked_buttons']);
             $saved_session = $saved_session . ", $tmp markers";
             $markers = $_SESSION['clicked_buttons'];
             $download_geno = "checked";
        } else {
             if ($message2 == "") {
                 $message1 = "0 markers ";
                 $message2 = "for all markers.";
             } else {
                 $message1 = $message1 . ", 0 markers ";
                 $message2 = $message2 . " for all markers";
             }
        }
         
        $this->refresh_title();
        ?>        
        </div>
        <div id="title2">
        <?php
        if (isset($_SESSION['selected_lines'])) {
            echo "Select the filter options then select the Create file button with the desired file format.";
        } else {
            echo "Download selected data for use in external analysis programs (TASSEL, rrBLUP, Flapjack, synbreed)";
        }
        echo "<br><br>";
                    echo "<b>Phenotype and Consensus Genotype data</b><br>";
                    echo "1. Select a set of <a href=\"" . $config['base_url'];
                    echo "downloads/select_all.php\">Lines, Traits, and Trials</a>.<br>";
                    ?>
                    2. Select a genetic map which has the best coverage for your selection.<br><br>
                    <b>Phenotype and Single Experiment Genotype data</b><br>
                    <?php
                    echo "1. Select a set of <a href=\"" . $config['base_url'];
                    echo "downloads/select_genotype.php\">Lines by Genotype Experiment</a>.<br>";
                    ?>
                    2. Select a genetic map which has the best coverage for your selection.<br><br>
        <input type="button" value="Detailed instruction" onclick="javascript: define_terms()"><br><br>
        </div><br>
        <input type="checkbox" id="typeP" value="pheno" onclick="javascript:select_download(this.id);" <?php echo $download_pheno ?>>Phenotype
        <input type="checkbox" id="typeG" value="geno" onclick="javascript:select_download(this.id);"<?php echo $download_geno ?>>Genotype consensus
        <input type="checkbox" id="typeGE" value="genoE" onclick="javascript:select_download(this.id);"<?php echo $download_genoe ?>>Genotype single experiment<br><br>
        <div id="step1" style="float: left; margin-bottom: 1.5em;">
        <?php
        $this->type1_lines_trial_trait();
        include 'select-map.php';
        include 'definition-of-terms.inc';
        //echo "</div>";
    }

    /**
     * display a spinning activity image when a slow function is running
     *
     * @return null
     */
    function refresh_title()
    {
        ?>
        <h2>Download Genotype and Phenotype Data</h2>
        <img alt="creating download file" id="spinner" src="images/ajax-loader.gif" style="display:none;">
        <?php
    }
    
    /**
     * use this download when selecting program and year
     *
     * @param string $version Tassel version of output
     */
    private function type1_session($version)
    {
        $datasets_exp = "";
        if (isset($_SESSION['selected_trials'])) {
            $experiments_t = $_SESSION['selected_trials'];
            $experiments_t = implode(",", $experiments_t);
        } else {
            $experiments_t = "";
        }
        if (isset($_SESSION['filtered_lines'])) {
            $selectedcount = count($_SESSION['filtered_lines']);
            $lines = $_SESSION['filtered_lines'];
            $lines_str = implode(",", $lines);
        } else {
            $lines = "";
            $lines_str = "";
	}
        if (isset($_SESSION['filtered_markers'])) {
		    $selectcount = $_SESSION['filtered_markers'];
		    $markers = $_SESSION['filtered_markers'];
		    $markers_str = implode(",", $markers);
        }
        if (isset($_SESSION['selected_traits'])) {
		    $phenotype = $_SESSION['selected_traits'];
                    $phenotype = implode(",", $phenotype);
	} else {
		    $phenotype = "";
	}

	//get genotype experiments
        $typeG = $_GET['typeG'];
        $typeGE = $_GET['typeGE'];
        $max_missing = $_GET['mm'];
        $min_maf = $_GET['mmaf'];
        if ($typeGE == "true") {
            $lines = $_SESSION['selected_lines'];
            $lines_str = implode(",", $lines);
            $experiments_g = $_SESSION['geno_exps'];
            $geno_str = $experiments_g[0];
            $sql = "SELECT marker_uid from allele_bymarker_exp_ACTG where experiment_uid = $geno_str";
            $res = mysql_query($sql) or die(mysql_error());
            while ($row = mysql_fetch_row($res)) {
                $uid = $row[0];
                $markers[] = $uid;
            }
        } else {
            $experiments_g = "";
        }

        $count_markers = count($markers);
        if ($count_markers <= 1) {
            echo "<font color=red>Error: no markers selected</font><br>\n";
            return;
        }

        // Clean up old files, older than 1 day
        $dir = "/tmp/tht";
        $item_ary = scandir($dir);
        $pattern = "/download_/";
        foreach ($item_ary as $item) {
            $path = $dir . "/$item";
            if (preg_match($pattern, $item)) {
                $mtime = filemtime($path);
                $yesterday = mktime(0, 0, 0, date("m"), date("d")-1, date("Y"));
                if ($mtime < $yesterday) {
                    if (is_dir($path)) {
                        $file_ary = scandir($path);
                        foreach ($file_ary as $file) {
                            $path2 = $path . "/$file";
                            if (is_file($path2)) {
                                unlink($path2);
                                //echo "delete file $path2<br>\n";
                            }
                        }
                        rmdir($path);
                        //echo "delete dir $path<br>\n";
                    } else {
                        unlink($path);
                        //echo "delete file $path<br>\n";
                    }
                }
            }
        }

        $unique_str = chr(rand(65, 90)) .chr(rand(65, 90)) .chr(rand(65, 90)) .chr(rand(65, 90));
        $filename = "download_" . $unique_str;
        mkdir("/tmp/tht/$filename");
        $subset = "yes";
        $dtype = "";
       
        if (isset($_SESSION['selected_map'])) {
            $filename = "geneticMap.txt";
            $h = fopen("/tmp/tht/download_$unique_str/$filename", "w");
            $output = $this->type1_build_geneticMap($lines, $markers, $dtype);
            fwrite($h, $output);
            fclose($h);
        }
        $filename = "selection_parameters.txt";
        $h = fopen("/tmp/tht/download_$unique_str/$filename", "w");
        fwrite($h, "Minimum MAF = $min_maf\n");
        fwrite($h, "Maximum Missing = $max_missing\n");
        fclose($h);
        if ($version == "V3") {
            $filename = "snpfile.txt";
            $h = fopen("/tmp/tht/download_$unique_str/$filename", "w");
            $output = $this->type2_build_markers_download($lines, $markers, $dtype, $h);
            fclose($h);
        } elseif ($version == "V4") { //Download for Tassel
            if (isset($_SESSION['phenotype']) && isset($_SESSION['selected_trials'])) {
                $filename = "traits.txt";
                $h = fopen("/tmp/tht/download_$unique_str/$filename", "w");
                $output = $this->type1_build_tassel_traits_download($experiments_t, $phenotype, $datasets_exp, $subset);
                fwrite($h, $output);
                fclose($h);
            }
            $filename = "genotype.hmp.txt";
            $h = fopen("/tmp/tht/download_$unique_str/$filename", "w");
            if ($typeGE == "true") {
                $output = type4BuildMarkersDownload($geno_str, $min_maf, $max_missing, $dtype, $h);
            } else {
                $output = $this->type3BuildMarkersDownload($lines, $markers, $dtype, $h);
            }
            fclose($h);
        } elseif ($version == "V5") { //Download for R
            $dtype = "qtlminer";
            if (isset($_SESSION['phenotype']) && isset($_SESSION['selected_trials'])) {
                $filename = "traits.txt";
                $h = fopen("/tmp/tht/download_$unique_str/$filename", "w");
                $output = $this->type1_build_traits_download($experiments_t, $phenotype, $datasets_exp);
                fwrite($h, $output);
                fclose($h);
            }
            if ($typeG == "true") {
                $filename = "snpfile.txt";
                $h = fopen("/tmp/tht/download_$unique_str/$filename", "w");
                $output = $this->type2_build_markers_download($lines, $markers, $dtype, $h);
                fwrite($h, $output);
                fclose($h);
            }
            $filename = "genotype.hmp.txt";
            $h = fopen("/tmp/tht/download_$unique_str/$filename", "w");
            if ($typeGE == "true") {
                $output = type4BuildMarkersDownload($geno_str, $min_maf, $max_missing, $dtype, $h);
            } else {
                $output = $this->type3BuildMarkersDownload($lines, $markers, $dtype,$h);
            }
            fclose($h);
        } elseif ($version == "V6") {  //Download for Flapjack
            $dtype = "AB";
            if (isset($_SESSION['phenotype']) && isset($_SESSION['selected_trials'])) {
                $filename = "traits.txt";
                $h = fopen("/tmp/tht/download_$unique_str/$filename","w");
                $output = $this->type1_build_tassel_traits_download($experiments_t,$phenotype,$datasets_exp, $subset);
                fwrite($h, $output);
                fclose($h);
            }
            $filename = "snpfile.txt";
            $h = fopen("/tmp/tht/download_$unique_str/$filename","w");
            $output = $this->type2_build_markers_download($lines,$markers,$dtype,$h);
            fclose($h);
        } elseif ($version == "V7") {  //Download for synbreed 
            $dtype = "AB";
            if (isset($_SESSION['phenotype']) && isset($_SESSION['selected_trials'])) {
                $filename = "traits.txt";
                $h = fopen("/tmp/tht/download_$unique_str/$filename","w");
                $output = $this->type1_build_traits_download($experiments_t,$phenotype,$datasets_exp);
                fwrite($h, $output);
                fclose($h);
            }
            $filename = "snpfile.txt";
            $h = fopen("/tmp/tht/download_$unique_str/$filename","w");
            $output = $this->type2_build_markers_download($lines,$markers,$dtype,$h);
            fclose($h);
        }
        if ($typeG == "true") {
            $filename = "allele_conflict.txt";
            $h = fopen("/tmp/tht/download_$unique_str/$filename","w");
            $output = $this->type2_build_conflicts_download($lines,$markers);
            fwrite($h, $output);
            fclose($h);
        }
        $filename = "/tmp/tht/download_" . $unique_str . ".zip";
        exec("cd /tmp/tht; /usr/bin/zip -r $filename download_$unique_str"); 
       
        ?>
        <input type="button" value="Download Zip file of results" onclick="javascript:window.open('<?php echo "$filename"; ?>');" />
        <?php
	}

    /**
     * use this download when selecting program and year
     * @param string $version Tassel version of output
     */
    private function type2_session($version)
    {
        $subset = "yes";
        $datasets_exp = "";
        if (isset($_SESSION['selected_trials'])) {
                        $experiments_t = $_SESSION['selected_trials'];
                        $experiments_t = implode(",", $experiments_t);
        } else {
                        $experiments_t = "";
        }
        if (isset($_SESSION['selected_traits'])) {
                    $phenotype = $_SESSION['selected_traits'];
                    $phenotype = implode(",", $phenotype);
        } else {
                    $phenotype = "";
        }

        // Clean up old files, older than 1 day
        $dir = "/tmp/tht";
        $item_ary = scandir($dir);
        $pattern = "/download_/";
        foreach ($item_ary as $item) {
            $path = $dir . "/$item";
            if (preg_match($pattern, $item)) {
                $mtime = filemtime($path);
                $yesterday = mktime(0, 0, 0, date("m"), date("d")-1, date("Y"));
                if ($mtime < $yesterday) {
                    if (is_dir($path)) {
                        $file_ary = scandir($path);
                        foreach ($file_ary as $file) {
                            $path2 = $path . "/$file";
                            if (is_file($path2)) {
                                unlink($path2);
                                //echo "delete file $path2<br>\n";
                            }
                        }
                        rmdir($path);
                        //echo "delete dir $path<br>\n";
                    } else {
                        unlink($path);
                        //echo "delete file $path<br>\n";
                    }
                }
            }
        }

        $unique_str = chr(rand(65, 90)) .chr(rand(65, 90)) .chr(rand(65, 90)) .chr(rand(65, 90));
        $filename = "download_" . $unique_str;
        mkdir("/tmp/tht/$filename");
        $subset = "yes";
        $dtype = "";
 
        $filename = "traits.txt";
        $h = fopen("/tmp/tht/download_$unique_str/$filename","w");
        if ($version == "V8") {
            $output = $this->type1_build_traits_download($experiments_t,$phenotype,$datasets_exp);
        } elseif ($version == "V9") {
            $output = $this->type1_build_tassel_traits_download($experiments_t, $phenotype, $datasets_exp, $subset); 
        }
        fwrite($h, $output);
        fclose($h);
        
        $filename = "/tmp/tht/download_" . $unique_str . ".zip";
        //$filename = "/tmp/tht/download_" . $unique_str . "/traits.txt";
        exec("cd /tmp/tht; /usr/bin/zip -r $filename download_$unique_str");
 
        ?>
        <input type="button" value="Download Zip file of results" onclick="javascript:window.open('<?php echo "$filename"; ?>');" />
        <?php

    }
	
    /**
     * starting with year
     */
    private function step1_yearprog()
    {
     ?>
    <div id="step11" style="float: left; margin-bottom: 1.5em;">
    <table id="phenotypeSelTab" class="tableclass1">
    <tr>
    <th>Year</th>
    </tr>
    <tr><td>
    <select name="year" multiple="multiple" style="height: 12em;" onchange="javascript: update_years(this.options)">
    <?php
    $sql = "SELECT e.experiment_year AS year FROM experiments AS e, experiment_types AS et
    WHERE e.experiment_type_uid = et.experiment_type_uid
    AND et.experiment_type_name = 'phenotype'
    GROUP BY e.experiment_year ASC";
    $res = mysql_query($sql) or die(mysql_error());
    while ($row = mysql_fetch_assoc($res))
    {
    ?>
    <option value="<?php echo $row['year'] ?>"><?php echo $row['year'] ?></option>
    <?php
    }
    ?>
    </select>
    </td>
    </table>
    </div>
    <?php
    }
    
	/**
	 * main entry point when there is a line selection in session variable
	 */
    private function type1_lines_trial_trait()
    {
		?>
		<div id="step11">
		<?php
	    $this->step1_lines();
		?>
	    </div></div>    
	    <div id="step2" style="float: left; margin-bottom: 1.5em;">
	    <?php 
	    $this->step2_lines();
	    ?></div>
	    <div id="step3" style="float: left; margin-bottom: 1.5em;">
            <?php
            $this->step3_lines();
            ?></div>
	    <div id="step4" style="float: left; margin-bottom: 1.5em;">
            <?php
            $this->step4_lines();
            ?></div>
            <div id="step4b" style="float: left; margin-bottom: 1.5em;"></div>
            <div id="step5" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%"></div>
            <div id="step6" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%">
            <script type="text/javascript" src="downloads/downloads07.js"></script>
            <script type="text/javascript">
            var mm = 50;
            var mmaf = 5;
            if ( window.addEventListener ) {
                    window.addEventListener( "load", select_download(), false );
            } else if ( window.attachEvent ) {
                    window.attachEvent( "onload", select_download);
            } else if ( window.onload ) {
                    window.onload = select_download();
            }
            </script>
	    </div></div>
	    <?php 	
	}
	
	/**
	 * starting with lines display the selected lines
	 */
	private function step1_lines()
	{
            ?>
            <table id="phenotypeSelTab" class="tableclass1">
            <tr>
            <th>Lines</th>
            <tr><td>
            <?php
		if (isset($_SESSION['selected_lines'])) {
			$selectedlines= $_SESSION['selected_lines'];
	        $count = count($_SESSION['selected_lines']);
		?>
	    <select name="lines" multiple="multiple" style="height: 12em;">
	    <?php
	    foreach($selectedlines as $uid) {
	      $sql = "SELECT line_record_name from line_records where line_record_uid = $uid";
	      $res = mysql_query($sql) or die(mysql_error());
	      $row = mysql_fetch_assoc($res)
	      ?>
	      <option disabled value="<?php $uid ?>">
	      <?php echo $row['line_record_name'] ?>
	      </option>
	      <?php
	    }
	    ?>
	    </select>
	    </td>
	    <?php 
	    } else {
                echo "none selected";
            }
            echo "</table>";
	}
	
	/**
	 * starting with lines display trials
	 */
	private function step2_lines()
	{
	    ?>
	    <table id="linessel" class="tableclass1">
	    <tr>
	    <th>Markers</th>
	    </tr>
	    <tr><td>
	    <?php 
	    if (isset($_SESSION['clicked_buttons'])) {
	      $selected = $_SESSION['clicked_buttons'];
		  ?>
	      <select name="markers" multiple="multiple" style="height: 12em;">
	      <?php
	      foreach($selected as $uid) {
	        $sql = "SELECT marker_name from markers where marker_uid = $uid";
	        $res = mysql_query($sql) or die(mysql_error());
	        $row = mysql_fetch_assoc($res)
	        ?>
	        <option disabled value="
	        <?php $uid ?>">
	        <?php echo $row['marker_name'] ?>
	        </option>
	        <?php
	      }
              echo "</select>";
	    } else {
	      echo "All";
	    }
	    ?>
	    </td>
	    </table>
	    <?php  
	}
	
	/**
	 * starting with lines display phenotype items
	 */
	private function step3_lines()
	{
	    ?>
	    <table class="tableclass1">
	    <tr>
	    <th>Traits</th>
	    </tr>
	    <tr><td>
	    <?php
            if (isset($_SESSION['selected_traits'])) {
              $selected = $_SESSION['selected_traits'];
              ?>
              <select name="traits" multiple="multiple" style="height: 12em;">
              <?php
              foreach($selected as $uid) {
                $sql = "SELECT phenotypes_name from phenotypes where phenotype_uid = $uid";
                $res = mysql_query($sql) or die(mysql_error());
                $row = mysql_fetch_assoc($res)
                ?>
                    <option disabled value="<?php echo $row['phenotypes_name'] ?>">
                     <?php echo $row['phenotypes_name'] ?>
                    </option>
                    <?php
                }
              echo "</select>";
            } else {
              echo "none selected";
            }
            ?>
            </table>
             <?php
        }

        /**
         * starting with lines display phenotype items
         */
        private function step4_lines()
        {
            ?>
            <table class="tableclass1">
            <tr>
            <th>Trials</th>
            </tr>
            <tr><td>
            <?php
            if (isset($_SESSION['selected_trials'])) {
              $selected = $_SESSION['selected_trials'];
              ?>
              <select name="traits" multiple="multiple" style="height: 12em;">
              <?php
              foreach($selected as $uid) {
                $sql = "SELECT trial_code from experiments where experiment_uid = $uid";
                $res = mysql_query($sql) or die(mysql_error());
                $row = mysql_fetch_assoc($res)
                ?>
                    <option disabled value="<?php echo $row['trial_code'] ?>">
                     <?php echo $row['trial_code'] ?>
                    </option>
                    <?php
                }
              echo "</select>";
            } else {
              echo "none selected";
            }
            ?>
            </table>
             <?php
        }

    /**
     * verify input
     *
     * @return null
     */
    private function verifyLines()
    {
        $typeP = $_GET['typeP'];
        $typeG = $_GET['typeG'];
        $typeGE = $_GET['typeGE'];
        $saved_session = "";
        if (isset($_SESSION['selected_lines'])) {
            $lines = $_SESSION['selected_lines'];
            $countLines = count($lines);
            $saved_session = "$countLines lines";
        } else {
            $countLines = 0;
            echo "<font color=\"red\">Choose one or more lines before using a saved selection. </font>";
            echo "<a href=";
            echo $config['base_url'];
            echo "pedigree/line_properties.php>Select lines</a><br>";
        }
        if ($typeP == "true") {
            if (!isset($_SESSION['phenotype'])) {
                echo "<font color=\"red\">Choose one or more traits before downloading phenotype data. </font>";
                echo "<a href=";
                echo $config['base_url'];
                echo "downloads/select_all.php>Select Traits</a><br>";
            } elseif (!isset($_SESSION['selected_trials'])) {
                echo "<font color=\"red\">Choose one or more trials before downloading genotype data. </font>";
                echo "<a href=";
                echo $config['base_url'];
                echo "phenotype/phenotype_selection.php>Select Trials</a><br>";
            }
        }
        if ($typeG == "true") {
            if (isset($_SESSION['clicked_buttons'])) {
                $markers = $_SESSION['clicked_buttons'];
                if (count($markers) > 1000) {
                    echo "<font color=\"red\">Downloading a larger number of arbitrary markers is a slow process. Select a genotype experiment for quicker response. </font>";
                    echo "<a href=";
                    echo $config['base_url'];
                    echo "downloads/select_genotype.php>Select genotype experiment</a><br>";
                }
            } else {
                if ($countLines > 100) {
                    echo "<font color=\"red\">Warning: It is a slow process to calculate the markers measured for selected lines. Select markers or genotype experiment for quicker response.</font><br>";
                }
            }
            if (isset($_SESSION['selected_map'])) {
                $selected_map = $_SESSION['selected_map'];
                $sql = "select mapset_name from mapset where mapset_uid = $selected_map";
                $res = mysql_query($sql) or die(mysql_error());
                $row = mysql_fetch_assoc($res);
                $map_name = $row['mapset_name'];
                if ($saved_session == "") {
                    $saved_session = "map set = $map_name";
                } else {
                    $saved_session .= ", map set = $map_name";
                }
                $saved_session .= " <input type=\"button\" value=\"change map set\" onclick=\"javascript: select_map()\"><br>";
            } else {
                ?><font color="red">Choose a  
                <input type="button" value="genetic map" onclick="javascript: select_map()"> to include marker location data.</font><br>
                <?php
            }
        }
        if ($typeGE == "true") {
            if (isset($_SESSION['geno_exps'])) {
                $geno_exp = $_SESSION['geno_exps'];
                $geno_str = $geno_exp[0];
                $sql = "select experiment_short_name, trial_code, platform_uid from experiments, genotype_experiment_info
                    where experiments.experiment_uid = genotype_experiment_info.experiment_uid
                    and experiments.experiment_uid = $geno_str";
                $res = mysql_query($sql) or die(mysql_error());
                $row = mysql_fetch_assoc($res);
                $geno_name = $row['trial_code'];
                if ($saved_session == "") {
                    $saved_session = "genotype experiment = $geno_name";
                } else {
                    $saved_session = $saved_session . ", genotype experiment = $geno_name";
                }
                $sql = "select count(*) from allele_bymarker_exp_101 where experiment_uid = $geno_str and pos is not null limit 10";
                $res = mysql_query($sql) or die(mysql_error() . $sql);
                $row = mysql_fetch_array($res);
                $count = $row[0];
                if ($count > 0) {
                    $saved_session .= ", map information loaded with this experiment";
                } elseif (isset($_SESSION['selected_map'])) {
                    $selected_map = $_SESSION['selected_map'];
                    $sql = "select mapset_name from mapset where mapset_uid = $selected_map";
                    $res = mysql_query($sql) or die(mysql_error());
                    $row = mysql_fetch_assoc($res);
                    $map_name = $row['mapset_name'];
                    if ($saved_session == "") {
                        $saved_session = "map set = $map_name";
                    } else {
                        $saved_session .= ", map set = $map_name";
                    }
                    $saved_session .= " <input type=\"button\" value=\"change map set\" onclick=\"javascript: select_map()\"><br>";
                } else {
                    ?><font color="red">Choose a 
                    <input type="button" value="genetic map" onclick="javascript: select_map()"> to include marker location data.</font><br>
                    <?php
                }
            } else {
                echo "<font color=\"red\">Select one Genotype experiment. </font>";
                echo "<a href=";
                echo $config['base_url'];
                echo "downloads/select_genotype.php>Genotype experiment</a><br>";
            }
        }
        if ($saved_session != "") {
            echo "<br>current data selection = $saved_session<br>";
        }
    }

    /**
     * starting with lines display marker data
     *
     * @return null
     */
    function step5_lines()
    {
        if (isset($_GET['use_line']) && ($_GET['use_line'] == "yes")) {
            $use_database = 0;
        } else {
            $use_database = 1;
        }
        if (isset($_SESSION['phenotype'])) {
            $phenotype = $_SESSION['phenotype'];
            $message2 = "create phenotype and genotype data file";
        } else {
            $phenotype = "";
            $message2 = " create genotype data file";
        }
        if (isset($_SESSION['selected_lines'])) {
             $countLines = count($_SESSION['selected_lines']);
             $lines = $_SESSION['selected_lines'];
             $selectedlines = implode(",", $_SESSION['selected_lines']);
        } else {
             $countLines = 0;
        }
        if (isset($_SESSION['selected_trials'])) {
            $countTrials = count($_SESSION['selected_trials']);
        } else {
            $countTrials = 0;
        }
        if (isset($_SESSION['selected_map'])) {
            $selected_map = $_SESSION['selected_map'];
        }
        if (isset($_SESSION['geno_exps'])) {
            $geno_exps = $_SESSION['geno_exps'];
        } else {
            $geno_exps = "";
        }
         
	if (isset($_SESSION['clicked_buttons'])) {
            $tmp = count($_SESSION['clicked_buttons']);
            $saved_session = $saved_session . ", $tmp markers";
            $markers = $_SESSION['clicked_buttons']; 
            $marker_str = implode(',',$markers);
	} else {
            $markers = "";
            $marker_str = "";
        }
        $typeGE = $_GET['typeGE'];


	 // initialize markers and flags if not already set
	 $max_missing = 99.9;//IN PERCENT
	 if (isset($_GET['mm']) && !empty($_GET['mm']) && is_numeric($_GET['mm']))
	  $max_missing = $_GET['mm'];
	 if ($max_missing>100)
	  $max_missing = 100;
	 elseif ($max_missing<0)
	 $max_missing = 0;
	 $min_maf = 0.01;//IN PERCENT
	 if (isset($_GET['mmaf']) && !is_null($_GET['mmaf']) && is_numeric($_GET['mmaf']))
	  $min_maf = $_GET['mmaf'];
	 if ($min_maf>100)
	  $min_maf = 100;
	 elseif ($min_maf<0)
	  $min_maf = 0;
         $max_miss_line = 10;
         if (isset($_GET['mml']) && !empty($_GET['mml']) && is_numeric($_GET['mml']))
           $max_miss_line = $_GET['mml'];
         if ($countLines > 0) {
            ?>
            <p>
        Minimum MAF &ge; <input type="text" name="mmaf" id="mmaf" size="2" value="<?php echo ($min_maf) ?>" />%
        &nbsp;&nbsp;&nbsp;&nbsp;
        Remove markers missing &gt; <input type="text" name="mm" id="mm" size="2" value="<?php echo ($max_missing) ?>" />% of data
        &nbsp;&nbsp;&nbsp;&nbsp;
            <?php
            if ($typeGE == "false") {
            ?>
        Remove lines missing &gt <input type="text" name="mml" id="mml" size="2" value="<?php echo ($max_miss_line) ?>" />% of data
            <?php
             } else {
             ?>
             <input type="hidden" name="mml" id="mml">
             <?php
             }
             if ($use_database) {
                //calculate_db($lines, $min_maf, $max_missing, $max_miss_line);
                echo "<br>Filter lines and markers then $message2";
             } elseif ($typeGE == "true") {
                calculate_afe($lines, $min_maf, $max_missing, $max_miss_line);
                $countFilterLines = count($lines);
                $countFilterMarkers = count($_SESSION['filtered_markers']);
             } else {
                calculate_af($lines, $min_maf, $max_missing, $max_miss_line);
                $countFilterLines = count($_SESSION['filtered_lines']);
                $countFilterMarkers = count($_SESSION['filtered_markers']);
             }
         }
         if (!$use_database) {
             if ($countFilterLines < 1) {
             echo "<font color=red>Error: No lines selected, increase the lines missing parameter to a larger number<br></font>\n";
             } elseif ($countFilterMarkers < 1) {
             echo "<font color=red>Error: No markers selected, decrease the MAF or increase the markers missing parameter to a larger number<br></font>\n";
             }
         }
         echo "<br><br>";

         if ($countLines == 0) {
         } elseif (($typeGE == "true") && ($geno_exps == "")) {
         } else {
             ?>
             <table border=0>
             <!--tr><td><input type="button" value="Download for Tassel V3" onclick="javascript:use_session('v3');" /-->
             <!--td>genotype coded as {AA=1:1, BB=2:2, AB=1:2, missing=?} --> 
             <tr><td><input type="button" value="Create file" onclick="javascript:use_session('v4');">
             <td>SNP data coded as {A,C,T,G,N}<br>DArT data coded as {+,-,N}<br>used with <b>TASSEL</b> Version 3, 4, or 5 
             <tr><td><input type="button" value="Create file" onclick="javascript:use_session('v5');">
             <td>genotype coded as {AA=1, BB=-1, AB=0, missing=NA}<br>used by <b>rrBLUP</b>
             <?php 
             if ($typeGE == "true") {
             } else {
             ?>
             <tr><td><input type="button" value="Create file" onclick="javascript:use_session('v6');">
             <td>genotype coded as {AA, AB, BB}<br>used by <b>Flapjack</b>
             <tr><td><input type="button" value="Create file" onclick="javascript:use_session('v7');">
             <td>genotype coded as {AA, AB, BB}<br>used by <b>synbreed</b>
             <?php
             }
             echo "</table>";
          ?><br><br>
          The snpfile.txt file has one row for each germplasm line.<br>
          The genotype.hmp.txt file has one row for each marker similar to the  HapMap format and contains map information.<br>
          The allele_conflict.txt file list all cases where there have been different results for the same line and marker.<br>
          The genotype files contains one measurement for each line and marker.<br>
          Documentation and loading instructions for analysis tools can be found at: <a href="http://www.maizegenetics.net/tassel" target="_blank">Tassel</a>
            , <a href="http://www.r-project.org" target="_blank">R (programming language)</a>
            , <a href="http://bioinf.scri.ac.uk/flapjack" target="_blank">Flapjack - Graphical Genotyping</a>
            , <a href="downloads/synbreed.doc">synbreed</a>.
          <?php
        }
	}

    /**
    * when no marker data selected then only show phenotype download button
    *
    * @return null
    */
    function step6_lines()
    {
        if (isset($_SESSION['selected_lines'])) {
            $countLines = count($_SESSION['selected_lines']);
            $lines = $_SESSION['selected_lines'];
            $selectedlines = implode(",", $_SESSION['selected_lines']);
        }
        if (isset($_SESSION['selected_traits']) && isset($_SESSION['selected_trials'])) {
            ?>
            <table border=0>
            <tr><td><input type="button" value="Create file" onclick="javascript:create_file('v8');" />
            <td>one column for each trait
            <tr><td><input type="button" value="Create file" onclick="javascript:create_file('v9');" />
            <td>one column for each trial<br>used by <b>TASSEL</b>
            </table>
        <?php
        }
    }

	/**
	 * used by uasort() to order an array
	 * @param integer $a
	 * @param integer $b
	 * @return number
	 */
	private function cmp($a, $b) {
	  if ($a == $b) {
	    return 0;
	  }
	  return ($a < $b) ? -1 : 1;
	}
	
	/**
	 * build download files for tassel V2
	 */
	function type1_build_tassel()
	{
		$experiments_t = (isset($_GET['e']) && !empty($_GET['e'])) ? $_GET['e'] : null;
		$traits = (isset($_GET['t']) && !empty($_GET['t'])) ? $_GET['t'] : null;
		$CAPdataprogram = (isset($_GET['bp']) && !empty($_GET['bp'])) ? $_GET['bp'] : null;
		$years = (isset($_GET['yrs']) && !empty($_GET['yrs'])) ? $_GET['yrs'] : null;
		$subset = (isset($_GET['subset']) && !empty($_GET['subset'])) ? $_GET['subset'] : null;

		$dtype = "tassel";
		
				// Get dataset IDs
			$sql_exp = "SELECT DISTINCT dse.datasets_experiments_uid as id
							FROM  datasets as ds, CAPdata_programs as cd, datasets_experiments as dse
							WHERE cd.CAPdata_programs_uid = ds.CAPdata_programs_uid
								AND dse.datasets_uid = ds.datasets_uid
								AND ds.breeding_year IN ($years)
								AND ds.CAPdata_programs_uid IN ($CAPdataprogram)";
			$res = mysql_query($sql_exp) or die(mysql_error());
			
			while ($row = mysql_fetch_array($res)){
				$datasets[] = $row["id"];
			}
			
			$datasets_exp = implode(',',$datasets);		
		
		// Get genotype experiments
		$sql_exp = "SELECT DISTINCT e.experiment_uid AS exp_uid
				FROM experiments e, experiment_types et, datasets_experiments as dse
				WHERE
					e.experiment_type_uid = et.experiment_type_uid
					AND et.experiment_type_name = 'genotype'
					AND e.experiment_uid = dse.experiment_uid
					AND dse.datasets_experiments_uid IN ($datasets_exp)";
		$res = mysql_query($sql_exp) or die(mysql_error());
			
		while ($row = mysql_fetch_array($res)){
				$exp[] = $row["exp_uid"];
		}
		$experiments_g = implode(',',$exp);
		//$firephp = FirePHP::getInstance(true);

		//$firephp->error("Curent location: ". getcwd());
		if (! file_exists('/tmp/tht')) mkdir('/tmp/tht');			
		$dir = '/tmp/tht/';
		$filename = 'T3download_tassel_'.chr(rand(65,80)).chr(rand(65,80)).chr(rand(64,80)).'.zip';
		
        // File_Archive doesn't do a good job of creating files, so we'll create it first
		if(!file_exists($dir.$filename)){
			$h = fopen($dir.$filename, "w+");
			fclose($h);
		}
		
        // Now let File_Archive do its thing
		$zip = File_Archive::toArchive($dir.$filename, File_Archive::toFiles());
		$zip->newFile("traits.txt");
		// $firephp->log("into traits ".$experiments_t." N".$traits." N".$datasets_exp);
		$zip->writeData($this->type1_build_tassel_traits_download($experiments_t, $traits, $datasets_exp, $subset));
		// $firephp->log("after traits 1 ".$experiments_t);

		$zip->newFile("snpfile.txt");
		// $firephp->log("before first marker file".$experiments_g);
		$zip->writeData($this->type1_build_markers_download($experiments_g, $dtype));
		// $firephp->log("after first marker file".$experiments_g);
		$zip->newFile("allele_conflict.txt");
		$zip->writeData($this->type1_build_conflicts_download($experiments_g, $dtype));
		$zip->newFile("annotated_alignment.txt");
		$zip->writeData($this->type1_build_annotated_align($experiments_g));
		// $firephp->log("after alignment marker file".$experiments_g);

		$zip->close();
		
		header("Location: ".$dir.$filename);
	}

	/**
	 * build download files for tassel V3
	 */
	function type1_build_tassel_v3()
	{
		$experiments_t = (isset($_GET['e']) && !empty($_GET['e'])) ? $_GET['e'] : null;
		$traits = (isset($_GET['t']) && !empty($_GET['t'])) ? $_GET['t'] : null;
		$CAPdataprogram = (isset($_GET['bp']) && !empty($_GET['bp'])) ? $_GET['bp'] : null;
		$years = (isset($_GET['yrs']) && !empty($_GET['yrs'])) ? $_GET['yrs'] : null;
		$subset = (isset($_GET['subset']) && !empty($_GET['subset'])) ? $_GET['subset'] : null;
		
		$dtype = "tassel";
		
				// Get dataset IDs
			$sql_exp = "SELECT DISTINCT dse.datasets_experiments_uid as id
							FROM  datasets as ds, CAPdata_programs as cd, datasets_experiments as dse
							WHERE cd.CAPdata_programs_uid = ds.CAPdata_programs_uid
								AND dse.datasets_uid = ds.datasets_uid
								AND ds.breeding_year IN ($years)
								AND ds.CAPdata_programs_uid IN ($CAPdataprogram)";
			$res = mysql_query($sql_exp) or die(mysql_error());
			
			while ($row = mysql_fetch_array($res)){
				$datasets[] = $row["id"];
			}
			
			$datasets_exp = implode(',',$datasets);		
		
		// Get genotype experiments
		$sql_exp = "SELECT DISTINCT e.experiment_uid AS exp_uid
				FROM experiments e, experiment_types et, datasets_experiments as dse
				WHERE
					e.experiment_type_uid = et.experiment_type_uid
					AND et.experiment_type_name = 'genotype'
					AND e.experiment_uid = dse.experiment_uid
					AND dse.datasets_experiments_uid IN ($datasets_exp)";
		$res = mysql_query($sql_exp) or die(mysql_error());
			
		while ($row = mysql_fetch_array($res)){
				$exp[] = $row["exp_uid"];
		}
		$experiments_g = implode(',',$exp);
		//$firephp = FirePHP::getInstance(true);

		//$firephp->error("Curent location: ". getcwd());
		if (! file_exists('/tmp/tht')) mkdir('/tmp/tht');			
		$dir = '/tmp/tht/';
		$filename = 'T3download_tasselV3_'.chr(rand(65,80)).chr(rand(65,80)).chr(rand(64,80)).'.zip';
		
        // File_Archive doesn't do a good job of creating files, so we'll create it first
		if(!file_exists($dir.$filename)){
			$h = fopen($dir.$filename, "w+");
			fclose($h);
		}
		
        // Now let File_Archive do its thing
		$zip = File_Archive::toArchive($dir.$filename, File_Archive::toFiles());
		$zip->newFile("traits.txt");
		// $firephp->log("into traits ".$experiments_t." N".$traits." N".$datasets_exp);
		$zip->writeData($this->type1_build_tassel_traits_download($experiments_t, $traits, $datasets_exp, $subset));
		// $firephp->log("after traits 1 ".$experiments_t);

		$zip->newFile("snpfile.txt");
		// $firephp->log("before first marker file".$experiments_g);
		$zip->writeData($this->type1_build_markers_download($experiments_g, $dtype));
		// $firephp->log("after first marker file".$experiments_g);
		$zip->newFile("allele_conflict.txt");
		$zip->writeData($this->type1_build_conflicts_download($experiments_g, $dtype));
		$zip->newFile("geneticMap.txt");
		$zip->writeData($this->type1_build_geneticMap($experiments_g));
		// $firephp->log("after alignment marker file".$experiments_g);

		$zip->close();
		
		header("Location: ".$dir.$filename);
	}
	
    /**
     * generate download files in R format
     * @param unknown_type $experiments
     * @param unknown_type $traits
     * @param unknown_type $datasets
     * 
     * @return NULL
     */
    function type1_build_traits_download($experiments, $traits, $datasets)
    {
        global $mysqli;
        $delimiter = "\t";

        $sql = "select line_record_name, line_record_uid from line_records";
        $res = mysql_query($sql) or die(mysql_error(). "<br>$sql");
        while ($row = mysql_fetch_array($res)) {
            $line_name = $row[0];
            $line_uid = $row[1];
            $line_list[$line_uid] = $line_name;
        }

        $trait_name = "";
        $sql = "select phenotype_uid, phenotypes_name from phenotypes where phenotype_uid IN ($traits)";
        $res = mysql_query($sql) or die(mysql_error(). "<br>$sql");
        while ($row = mysql_fetch_array($res)) {
            $uid = $row[0];
            $trait_name = $row[1];
            $trait_list[$uid] = $trait_name;
            $empty[$uid] = "NA";
        }

        $sql = "select experiment_uid, trial_code from experiments where experiment_uid IN ($experiments)";
        $res = mysql_query($sql) or die(mysql_error(). "<br>$sql");
        while ($row = mysql_fetch_array($res)) {
            $uid = $row[0];
            $expr_name = $row[1];
            $expr_list[$uid] = $expr_name;
        }

        $sql = "select distinct(tb.line_record_uid)
            from tht_base as tb, phenotype_data as pd
            where tb.experiment_uid IN ($experiments) AND
            pd.tht_base_uid = tb.tht_base_uid
            and pd.phenotype_uid IN ($traits)";
        $res = mysql_query($sql) or die(mysql_error(). "<br>$sql");
        while ($row = mysql_fetch_array($res)) {
            $lines[] = $row[0];
        }

            $output = implode($delimiter, $trait_list);
            $output = 'line' . $delimiter . 'trial' . $delimiter . $output . "\n"; 

            $sql = "select pd.phenotype_uid, pd.value as value
            from tht_base as tb, phenotype_data as pd
            where tb.line_record_uid = ? AND
            tb.experiment_uid = ? AND
            pd.tht_base_uid = tb.tht_base_uid
            and pd.phenotype_uid IN ($traits)";
            $stmt = mysqli_prepare($mysqli, $sql) or die(mysqli_error($mysqli));
            mysqli_stmt_bind_param($stmt, "ii", $line_uid, $expr_uid) or die(mysqli_error($mysqli));
            $ncols = count($empty);
            foreach ($lines as $key=>$line_uid) {
                $line_name = $line_list[$line_uid];
                $count = 0;
                foreach ($expr_list as $expr_uid=>$expr_name) {
                    $outarray = $empty;
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_bind_result($stmt, $trait_uid, $value);
                    while (mysqli_stmt_fetch($stmt)) {
                      $outarray[$trait_uid]= $value;
                    }
                    if ($outarray != $empty) {
                        $tmp = implode($delimiter, $outarray);
                        $output .= $line_name.$delimiter.$expr_name.$delimiter.$tmp."\n";
                    }
                }
            }
        mysqli_stmt_close($stmt);
        return $output;
    }

    /**
     * Build trait download file for Tassel program interface
     * @param unknown_type $experiments
     * @param unknown_type $traits
     * @param unknown_type $datasets
     * @param unknown_type $subset
     * @return string
     */
    function type1_build_tassel_traits_download($experiments, $traits, $datasets, $subset)
    {
        global $mysqli;
        $delimiter = "\t";
        $output = '';
        $outputheader1 = '';
	$outputheader2 = "<Trait>";
	$outputheader3 = "<Trial>";
      
      //count number of traits and number of experiments
      $ntraits=substr_count($traits, ',')+1;
      $nexp=substr_count($experiments, ',')+1;
      
      //$traits = explode(',', $traits);
      //$experiments = explode(',', $experiments);
      
      // figure out which traits are at which location
      if ($experiments=="") {
        $sql_option = "";
      } else {
        $sql_option = "AND tb.experiment_uid IN ($experiments)";
      }
      $selectedlines = implode(",", $_SESSION['selected_lines']);
      if (count($_SESSION['selected_lines']) > 0) {
         $sql_option = $sql_option . " AND tb.line_record_uid IN ($selectedlines)";
      }
      $sql = "SELECT DISTINCT e.trial_code, e.experiment_uid, p.phenotypes_name,p.phenotype_uid
               FROM experiments as e, tht_base as tb, phenotype_data as pd, phenotypes as p
               WHERE 
                  e.experiment_uid = tb.experiment_uid
                  $sql_option
                  AND pd.tht_base_uid = tb.tht_base_uid
                  AND p.phenotype_uid = pd.phenotype_uid
                  AND pd.phenotype_uid IN ($traits)  
               ORDER BY p.phenotype_uid,e.experiment_uid";
      $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
      $ncols = mysqli_num_rows($res);
      while($row = mysqli_fetch_array($res)) {
         $outputheader2 .= $delimiter . str_replace(" ","_",$row['phenotypes_name']);
         $outputheader3 .= $delimiter . $row['trial_code'];
         $keys[] = $row['phenotype_uid'].":".$row['experiment_uid'];
      }
      $nexp=$ncols;

		// dem 5jan11: If $subset="yes", use $_SESSION['selected_lines'].
		$sql_option = "";
		if ($subset == "yes" && count($_SESSION['selected_lines']) > 0) {
		  $selectedlines = implode(",", $_SESSION['selected_lines']);
		  $sql_option = " AND line_records.line_record_uid IN ($selectedlines)";
		} 
		if (preg_match("/\d/",$experiments)) {
		  $sql_option .= "AND tht_base.experiment_uid IN ($experiments)";
		}
		if (preg_match("/\d/",$datasets)) {
		  $sql_option .= "AND ((tht_base.datasets_experiments_uid in ($datasets) AND tht_base.check_line='no') OR (tht_base.check_line='yes'))";
		}
			
          // get a list of all line names in the selected datasets and experiments,
	  // INCLUDING the check lines // AND tht_base.check_line IN ('no')
      $sql = "SELECT DISTINCT line_records.line_record_name, line_records.line_record_uid
               FROM line_records, tht_base
	       WHERE line_records.line_record_uid=tht_base.line_record_uid
                 $sql_option";
      $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
      while($row = mysqli_fetch_array($res)) {
         $lines[] = $row['line_record_name'];
         $line_uid[] = $row['line_record_uid'];
      }
      $nlines = count($lines);

	  if ($nexp ===1){
			$nheaderlines = 1;
		} else {
			$nheaderlines = 2;
		}
      $outputheader1 = "$nlines".$delimiter."$ncols".$delimiter.$nheaderlines;
	  if ($nexp ===1){
                        $output = $outputheader2."\n";
		} else {
                        $output = $outputheader2."\n".$outputheader3."\n";
		}
	  if (preg_match("/\d/",$experiments)) {
              $sql = "SELECT pd.value as value,pd.phenotype_uid,tb.experiment_uid 
                  FROM tht_base as tb, phenotype_data as pd
                  WHERE tb.experiment_uid IN ($experiments) AND
                  tb.line_record_uid  = ?
                  AND pd.tht_base_uid = tb.tht_base_uid
                  AND pd.phenotype_uid IN ($traits)
                  GROUP BY tb.tht_base_uid, pd.phenotype_uid";
          } else {
              $sql = "SELECT pd.value as value,pd.phenotype_uid,tb.experiment_uid 
                  FROM tht_base as tb, phenotype_data as pd
                  WHERE tb.line_record_uid  = ? 
                  AND pd.tht_base_uid = tb.tht_base_uid
                  AND pd.phenotype_uid IN ($traits) 
                  GROUP BY tb.tht_base_uid, pd.phenotype_uid";
          }
          $stmt = mysqli_prepare($mysqli, $sql) or die(mysqli_error($mysqli));
          for ($i=0;$i<$nlines;$i++) {
              $outline = "'$lines[$i]'".$delimiter;
              mysqli_stmt_bind_param($stmt, "i", $line_uid[$i]) or die(mysqli_error($mysqli));
              mysqli_stmt_execute($stmt) or die(mysqli_error($mysqli));
              mysqli_stmt_bind_result($stmt, $value, $trait_uid, $exp_uid) or die(mysqli_error($mysqli));
              if ($ncols > 0) {
		$outarray = array_fill(0,$ncols,-999);
		$outarray = array_combine($keys  , $outarray);
              }
              while (mysqli_stmt_fetch($stmt)) {
                $keyval = $trait_uid.":".$exp_uid;
                $outarray[$keyval]= $value;
              }
              $outline .= implode($delimiter,$outarray)."\n";
              $output .= $outline;
          }
          mysqli_stmt_close($stmt);
	  return $output;
	}

	/**
	 * build genotype data file for tassle V2 and V3
	 * @param unknown_type $experiments
	 * @param unknown_type $dtype
	 */
	function type1_build_markers_download($experiments,$dtype)
	{
		// $firephp = FirePHP::getInstance(true);
		$outputheader = '';
		$output = '';
		$doneheader = false;
		$delimiter ="\t";
      
		 if (isset($_GET['mm']) && !empty($_GET['mm']) && is_numeric($_GET['mm']))
            $max_missing = $_GET['mm'];
		if ($max_missing>100)
			$max_missing = 100;
		elseif ($max_missing<0)
			$max_missing = 0;
			// $firephp->log("in sort markers2");
        $min_maf = 0.01;//IN PERCENT
        if (isset($_GET['mmaf']) && !is_null($_GET['mmaf']) && is_numeric($_GET['mmaf']))
            $min_maf = $_GET['mmaf'];
		if ($min_maf>100)
			$min_maf = 100;
		elseif ($min_maf<0)
			$min_maf = 0;
			// $firephp->log("in sort markers".$max_missing."  ".$min_maf);

	 //get lines and filter to get a list of markers which meet the criteria selected by the user
          $sql_mstat = "SELECT af.marker_uid as marker, m.marker_name as name, SUM(af.aa_cnt) as sumaa, SUM(af.missing)as summis, SUM(af.bb_cnt) as sumbb,
					SUM(af.total) as total, SUM(af.ab_cnt) AS sumab
					FROM allele_frequencies AS af, markers as m
					WHERE m.marker_uid = af.marker_uid
						AND af.experiment_uid in ($experiments)
					group by af.marker_uid"; 

			$res = mysql_query($sql_mstat) or die(mysql_error());
			$num_maf = $num_miss = 0;
			while ($row = mysql_fetch_array($res)){
			  $maf = round(100*min((2*$row["sumaa"]+$row["sumab"])/(2*$row["total"]),($row["sumab"]+2*$row["sumbb"])/(2*$row["total"])),1);
			  $miss = round(100*$row["summis"]/$row["total"],1);
			  if (($maf >= $min_maf)AND ($miss<=$max_missing)) {
			    $marker_names[] = $row["name"];
			    $outputheader .= $row["name"].$delimiter;
			    $marker_uid[] = $row["marker"];
			  }
			}
			$nelem = count($marker_names);
			if ($nelem == 0) {
			    die("error - no genotype or marker data for this experiment, experiment_uid=$experiments");
			}
			$marker_uid = implode(",",$marker_uid);
        
		if ($dtype=='qtlminer') {
		  $lookup = array(
			  'AA' => '1',
			  'BB' => '-1',
			  '--' => 'NA',
			  'AB' => '0'
		  );
	   } else {
		  $lookup = array(
			  'AA' => '1:1',
			  'BB' => '2:2',
			  '--' => '?',
			  'AB' => '1:2'
		  );
		}
		
			// make an empty line with the markers as array keys, set default value
			//  to the default missing value for either qtlminer or tassel
			// places where the lines may have different values
			
		  if ($dtype =='qtlminer')  {
				$empty = array_combine($marker_names,array_fill(0,$nelem,'NA'));
		  } else {
				$empty = array_combine($marker_names,array_fill(0,$nelem,'?'));
		  }
			
			
         $sql = "SELECT line_record_name, marker_name AS name,
                    alleles AS value
			FROM
            allele_cache as a
			WHERE
				a.marker_uid IN ($marker_uid)
				AND a.experiment_uid IN ($experiments)
		  ORDER BY a.line_record_uid, a.marker_uid";


		$last_line = "some really silly name that noone would call a plant";
		$res = mysql_query($sql) or die(mysql_error());
		
		$outarray = $empty;
		$cnt = $num_lines = 0;
		while ($row = mysql_fetch_array($res)){
				//first time through loop
				if ($cnt==0) {
					$last_line = $row['line_record_name'];
				}
				
			if ($last_line != $row['line_record_name']){  
					// Close out the last line
					$output .= "$last_line\t";
					$outarray = implode($delimiter,$outarray);
					$output .= $outarray."\n";
					//reset output arrays for the next line
					$outarray = $empty;
					$mname = $row['name'];				
					$outarray[$mname] = $lookup[$row['value']];
					$last_line = $row['line_record_name'];
					$num_lines++;
			} else {
					 $mname = $row['name'];				
					 $outarray[$mname] = $lookup[$row['value']];
			}
			$cnt++;
		}
		//NOTE: there is a problem with the last line logic here. Must fix.
		  //save data from the last line
		  $output .= "$last_line\t";
		  $outarray = implode($delimiter,$outarray);
		  $output .= $outarray."\n";
		  $num_lines++;
		  
		if ($dtype =='qtlminer')  {
		  return $outputheader."\n".$output;
		} else {
		  return $num_lines.$delimiter.$nelem.":2\n".$outputheader."\n".$output;
	   }
	}
	
	/**
	 * build file listing conflicts in genotype data
	 * @param unknown_type $experiments
	 * @param unknown_type $dtype
	 */
	function type1_build_conflicts_download($experiments,$dtype) {
	 
	  //get lines and filter to get a list of markers which meet the criteria selected by the user
	  $sql_mstat = "SELECT af.marker_uid as marker, m.marker_name as name, SUM(af.aa_cnt) as sumaa, SUM(af.missing)as summis, SUM(af.bb_cnt) as sumbb,
	  SUM(af.total) as total, SUM(af.ab_cnt) AS sumab
	  FROM allele_frequencies AS af, markers as m
	  WHERE m.marker_uid = af.marker_uid
	  AND af.experiment_uid in ($experiments)
	  group by af.marker_uid";
	 
	  $res = mysql_query($sql_mstat) or die(mysql_error());
	  $num_maf = $num_miss = 0;
	  while ($row = mysql_fetch_array($res)){
	    $maf = round(100*min((2*$row["sumaa"]+$row["sumab"])/(2*$row["total"]),($row["sumab"]+2*$row["sumbb"])/(2*$row["total"])),1);
	    $miss = round(100*$row["summis"]/$row["total"],1);
	    if (($maf >= $min_maf)AND ($miss<=$max_missing)) {
	      $marker_uid[] = $row["marker"];
	    }
	  }
	  $marker_uid = implode(",",$marker_uid);
	  $output = "line name\tmarker name\talleles\texperiment\n";
	  $query = "select l.line_record_name, m.marker_name, a.alleles, e.trial_code
	  from allele_conflicts a, line_records l, markers m, experiments e
	  where a.line_record_uid = l.line_record_uid
	  and a.marker_uid = m.marker_uid
	  and a.experiment_uid = e.experiment_uid
	  and a.alleles != '--'
	  and a.marker_uid IN ($marker_uid)
	  order by l.line_record_name, m.marker_name, e.trial_code";
	  $res = mysql_query($query) or die(mysql_error() . "<br>" . $sql_exp);
	  if (mysql_num_rows($res)>0) {
	   while ($row = mysql_fetch_row($res)){
	    $output.= "$row[0]\t$row[1]\t$row[2]\t$row[3]\n";
	   }
	  }
	  return $output;
	}
	
	/**
	 * build genotype data file when given set of lines and markers
	 * @param unknown_type $lines
	 * @param unknown_type $markers
	 * @param unknown_type $dtype
	 */
	function type2_build_markers_download($lines,$markers,$dtype, $h)
	{
		$output = '';
		$doneheader = false;
		$delimiter ="\t";
                $outputheader = '';
		
		if (count($markers)>0) {
		  $markers_str = implode(",", $markers);
		} else {
		  die("error - markers should be selected before download!");
		}
		if (count($lines)>0) {
		  $lines_str = implode(",", $lines);
		} else {
		  $lines_str = "";
		}
	
                //generate an array of selected markers that can be used with isset statement
                foreach ($markers as $temp) {
                  $marker_lookup[$temp] = 1;
                }
	
		$sql = "select marker_uid, marker_name from allele_byline_idx order by marker_uid";
		$res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
		$i=0;
		while ($row = mysql_fetch_array($res)) {
		   $marker_list[$i] = $row[0];
		   $marker_list_name[$i] = $row[1];
		   $i++;
		}

                foreach ($marker_list as $i => $marker_id) {
		  $marker_name = $marker_list_name[$i];
		  if (isset($marker_lookup[$marker_id])) {
		    $marker_names[] = $marker_name;
                    if ($outputheader == '') {
                       $outputheader .= $marker_name;
                    } else {
		       $outputheader .= $delimiter.$marker_name;
                    }
		  }
		}

                $nelem = count($marker_names);
                $num_lines = count($lines);	
                if ($dtype =='qtlminer')  {
                    fwrite($h, "$outputheader\n");
                } elseif ($dtype == 'AB') {
                    fwrite($h, "# fjFile = GENOTYPE\n".$delimiter.$outputheader."\n");
                } else {
                    fwrite($h, "$num_lines.$delimiter.$nelem.:2\n".$outputheader."\n");
                }

		if ($dtype=='qtlminer') {
		 $lookup = array(
		   'AA' => '1',
		   'BB' => '-1',
		   '--' => 'NA',
		   'AB' => '0',
		   '' => 'NA'
		 );
                } elseif ($dtype=='AB') {
                  $lookup = array(
                  'AA' => 'AA',
                  'BB' => 'BB',
                  '--' => '-',
                  'AB' => 'AB'
                  );
		} else {
		 $lookup = array(
		   'AA' => '1:1',
		   'BB' => '2:2',
		   '--' => '?',
		   'AB' => '1:2',
		   '' => '?'
		 );
		}
		
		foreach ($lines as $line_record_uid) {
                  $outarray2 = array();
		  $sql = "select line_record_name, alleles from allele_byline where line_record_uid = $line_record_uid";
		  $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
		  if ($row = mysql_fetch_array($res)) {
                    $line_name = $row[0];
                    $alleles = $row[1];
		    $outarray = explode(',',$alleles);
		    foreach ($outarray as $key=>$allele) {
		  	$marker_id = $marker_list[$key];
		  	if (isset($marker_lookup[$marker_id])) {
		  	  $outarray2[]=$lookup[$allele];
                        }
		    }
                  } else {
                    $sql = "select line_record_name from line_records where line_record_uid = $line_record_uid";
                    $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
                    if ($row = mysql_fetch_array($res)) {
                      $line_name = $row[0];
                      foreach ($marker_list as $marker_id) {
                        if (isset($marker_lookup[$marker_id])) {
                          $outarray2[]=$lookup[""];
                        }
                      }
                    } else {
                      echo "Error - could not find line_uid $line_record_uid\n";
                    }
                  }
		  $allele_str = implode($delimiter,$outarray2);
                  fwrite($h, "$line_name\t$allele_str\n");
		}
		if ($nelem == 0) {
		   die("error - no genotype or marker data for this selection");
		}
	}
  
	/**
	 * build genotype data files for tassel and rrBLUP using consensus genotype
         * 
	 * @param unknown_type $lines
	 * @param unknown_type $markers
	 * @param unknown_type $dtype
	 */
	function type3BuildMarkersDownload($lines,$markers,$dtype,$h)
	{
	 $output = '';
	 $outputheader = '';
	 $delimiter ="\t";
	
         if (isset($_SESSION['selected_map'])) {
           $selected_map = $_SESSION['selected_map'];
         } else {
           $selected_map = "";
           echo "<font color=red>Warning - no marker location will be given</font>";
         }
	
	 if (count($markers)>0) {
	  $markers_str = implode(",", $markers);
	 } else {
	  die("<font color=red>Error - markers should be selected before download</font>");
	 }
	 if (count($lines)>0) {
	  $lines_str = implode(",", $lines);
	 } else {
	  $lines_str = "";
	 }
	 
         //generate an array of selected lines that can be used with isset statement
         foreach ($lines as $temp) {
           $line_lookup[$temp] = 1;
         }

         $sql = "select line_record_uid, line_record_name from allele_bymarker_idx order by line_record_uid";
         $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
         $i=0;
         while ($row = mysql_fetch_array($res)) {
            $line_list[$i] = $row[0];
            $line_list_name[$i] = $row[1];
            $i++;
         }

	 //order the markers by map location
         //tassel v5 needs markers sorted when position is not unique
         if ($selected_map == "") {
             $marker_list_mapped = array();
             $marker_list_chr = array();
         } else {
	 $sql = "select markers.marker_uid, CAST(1000*mim.start_position as UNSIGNED), mim.chromosome from markers, markers_in_maps as mim, map, mapset
	 where markers.marker_uid IN ($markers_str)
	 AND mim.marker_uid = markers.marker_uid
	 AND mim.map_uid = map.map_uid
	 AND map.mapset_uid = mapset.mapset_uid
	 AND mapset.mapset_uid = $selected_map 
	 order by mim.chromosome, CAST(1000*mim.start_position as UNSIGNED), BINARY markers.marker_name";
	 $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
	 while ($row = mysql_fetch_array($res)) {
           $marker_uid = $row[0];
           $pos = $row[1];
           $chr = $row[2];
	   $marker_list_mapped[$marker_uid] = $pos;
           $marker_list_chr[$marker_uid] = $chr;
	 }
         }

         $marker_list_all = $marker_list_mapped;	
	 //generate an array of selected markers and add map position if available
         $sql = "select marker_uid, marker_name, A_allele, B_allele, marker_type_name from markers, marker_types
         where marker_uid IN ($markers_str)
         AND markers.marker_type_uid = marker_types.marker_type_uid
         order by BINARY marker_name";
         $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
         while ($row = mysql_fetch_array($res)) {
           $marker_uid = $row[0];
           $marker_name = $row[1];
           if (isset($marker_list_all[$marker_uid])) {
           } else {
             $marker_list_all[$marker_uid] = 0;
           }
           if (preg_match("/[A-Z]/",$row[2]) && preg_match("/[A-Z]/",$row[3])) {
                $allele = $row[2] . "/" . $row[3];
           } elseif (preg_match("/DArT/",$row[4])) {
                $allele = $row[2] . "/" . $row[3];
           } else {
                $allele = "N/N";
           }
           $marker_list_name[$marker_uid] = $marker_name;
           $marker_list_allele[$marker_uid] = $allele;
           $marker_list_type[$marker_uid] = $row[4];
         }

	 //get location in allele_byline for each marker
	 $sql = "select marker_uid, marker_name from allele_byline_idx order by marker_uid";
	 $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
	 $i=0;
	 while ($row = mysql_fetch_array($res)) {
	   $marker_idx_list[$row[0]] = $i;
	   $i++;
	 }
	 
	 //get header, tassel requires all fields even if they are empty
         if ($dtype == "qtlminer") {
           $outputheader = "rs\talleles\tchrom\tpos";
         } else {
	   $outputheader = "rs#\talleles\tchrom\tpos\tstrand\tassembly#\tcenter\tprotLSID\tassayLSID\tpanelLSID\tQCcode";
         }
	 $sql = "select line_record_name from line_records where line_record_uid IN ($lines_str) order by line_record_uid";
	 $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
	 while ($row = mysql_fetch_array($res)) {
	  $name = $row[0];
	  $outputheader .= "\t$name";
	 }
         fwrite($h, "$outputheader\n");
	 
	 $lookup_chrom = array(
	   '1H' => '1','2H' => '2','3H' => '3','4H' => '4','5H' => '5',
	   '6H' => '6','7H' => '7','UNK'  => '0');
	
	 //using a subset of markers so we have to translate into correct index
         $pos_index = 0;
	 foreach ($marker_list_all as $marker_id => $val) {
	  $marker_idx = $marker_idx_list[$marker_id];
          $marker_name = $marker_list_name[$marker_id];
          $allele = $marker_list_allele[$marker_id];
          $marker_type = $marker_list_type[$marker_id];
          if (isset($marker_list_mapped[$marker_id])) {
            $chrom = $marker_list_chr[$marker_id];
            $pos = $marker_list_mapped[$marker_id];
          } else {
            $chrom = 'UNK';
            $pos = $pos_index;
            $pos_index += 10;
          }

          if ($dtype == "qtlminer") {
           $lookup = array(
           'AA' => '1',
           'BB' => '-1',
           '--' => 'NA',
           'AB' => '0',
            '' => 'NA'
           );
          } elseif (preg_match("/DArT/", $marker_type)) {
           $lookup = array(
            'AA' => '+',
            'BB' => '-',
            '--' => 'N'
            ); 
          } else {
           $lookup = array(
           'AA' => substr($allele,0,1) . substr($allele,0,1),
           'BB' => substr($allele,2,1) . substr($allele,2,1),
           '--' => 'NN',
           'AB' => substr($allele,0,1) . substr($allele,2,1),
           'BA' => substr($allele,2,1) . substr($allele,0,1),
           '' => 'NN'
          );
           }

             if ($dtype == "qtlminer") {
               fwrite($h, "$marker_name\t$allele\t$chrom\t$pos");
             } else {
	       fwrite($h, "$marker_name\t$allele\t$chrom\t$pos\t\t\t\t\t\t\t");
             }
             $outarray2 = array();
             $sql = "select marker_name, alleles from allele_bymarker where marker_uid = $marker_id";
             $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
             if ($row = mysql_fetch_array($res)) {
               $marker_name = $row[0];
               $alleles = $row[1];
               $outarray = explode(',',$alleles);
               foreach ($outarray as $key=>$allele) {
                 $line_id = $line_list[$key];
                 if (isset($line_lookup[$line_id])) {
                   $outarray2[]=$lookup[$allele];
                 }
               }
             } else {
               echo "Error - could not find marker_uid $marker_id<br>\n";
             }
	     $allele_str = implode("\t",$outarray2);
             fwrite($h, "\t$allele_str\n"); 
        }
	}

	/**
	 * build genotype conflicts file when given set of lines and markers
	 * @param unknown_type $lines
	 * @param unknown_type $markers
	 * @return string
	 */
	function type2_build_conflicts_download($lines,$markers) {
	 
	  if (count($markers)>0) {
	    $markers_str = implode(",",$markers);
	  } else {
	    die("error - markers should be selected before download");
	  }
	  if (count($lines)>0) {
	    $lines_str = implode(",",$lines);
	  } else {
	    $lines_str = "";
	  }

	  $output = "line name\tmarker name\talleles\texperiment\n";
	  $query = "select l.line_record_name, m.marker_name, a.alleles, e.trial_code
	  from allele_conflicts a, line_records l, markers m, experiments e
	  where a.line_record_uid = l.line_record_uid
	  and a.marker_uid = m.marker_uid
	  and a.experiment_uid = e.experiment_uid
	  and a.alleles != '--'
	  and a.line_record_uid IN ($lines_str)
	  and a.marker_uid IN ($markers_str)
	  order by l.line_record_name, m.marker_name, e.trial_code";
	  $res = mysql_query($query) or die(mysql_error() . "<br>" . $sql_exp);
	  if (mysql_num_rows($res)>0) {
	    while ($row = mysql_fetch_row($res)){
	      $output.= "$row[0]\t$row[1]\t$row[2]\t$row[3]\n";
	    }
	  }
	  return $output;
	}

	/**
	 * create map file in Tassel V2 format
	 * @param string $experiments
	 * @return string
	 */
	function type1_build_annotated_align($experiments)
	{
		$delimiter ="\t";
		// $firephp = FirePHP::getInstance(true);
		$output = '';
		$doneheader = false;
		        if (isset($_GET['mm']) && !empty($_GET['mm']) && is_numeric($_GET['mm']))
            $max_missing = $_GET['mm'];
		if ($max_missing>100)
			$max_missing = 100;
		elseif ($max_missing<0)
			$max_missing = 0;
			// $firephp->log("in sort markers2");
        $min_maf = 0.01;//IN PERCENT
        if (isset($_GET['mmaf']) && !is_null($_GET['mmaf']) && is_numeric($_GET['mmaf']))
            $min_maf = $_GET['mmaf'];
		if ($min_maf>100)
			$min_maf = 100;
		elseif ($min_maf<0)
			$min_maf = 0;
			// $firephp->log("in sort markers".$max_missing."  ".$min_maf);
        if (isset($_SESSION['selected_map'])) {
           $selected_map = $_SESSION['selected_map'];
         } else {
           $selected_map = 1;
         }

	 //get lines and filter to get a list of markers which meet the criteria selected by the user
          $sql_mstat = "SELECT af.marker_uid as marker, m.marker_name as name, SUM(af.aa_cnt) as sumaa, SUM(af.missing)as summis, SUM(af.bb_cnt) as sumbb,
					SUM(af.total) as total, SUM(af.ab_cnt) AS sumab
					FROM allele_frequencies AS af, markers as m
					WHERE m.marker_uid = af.marker_uid
						AND af.experiment_uid in ($experiments)
					group by af.marker_uid"; 

			$res = mysql_query($sql_mstat) or die(mysql_error());
			$num_maf = $num_miss = 0;

			while ($row = mysql_fetch_array($res)){
			  $maf = round(100*min((2*$row["sumaa"]+$row["sumab"])/(2*$row["total"]),($row["sumab"]+2*$row["sumbb"])/(2*$row["total"])),1);
			  $miss = round(100*$row["summis"]/$row["total"],1);
					if (($maf >= $min_maf)AND ($miss<=$max_missing)) {
						$marker_names[] = $row["name"];
						$outputheader .= $delimiter.$row["name"];
						$marker_uid[] = $row["marker"];
						
					}
			}
			// $firephp->log($marker_uid);
   		
		  $lookup = array(
			  'AA' => 'A','BB' => 'B','--' => '-','AB' => 'C'
		  );
		  $lookup_chrom = array(
			  '1H' => '1','2H' => '2','3H' => '3','4H' => '4','5H' => '5',
			  '6H' => '6','7H' => '7','UNK'  => '10'
		  );
		
		  // finish writing file header using a list of line names
		  $sql = "SELECT DISTINCT lr.line_record_name AS line_name
					 FROM line_records AS lr, tht_base AS tb
					 WHERE
						  lr.line_record_uid = tb.line_record_uid
						  AND tb.experiment_uid IN ($experiments)
						  ORDER BY line_name";
		  $res = mysql_query($sql) or die(mysql_error());
		  while ($row = mysql_fetch_array($res)) {
				$line_names[] = $row['line_name'];
			  }
			  
			// make an empty marker with the lines as array keys 
			$nelem = count($marker_uid);
			$n_lines = count($line_names);
			$empty = array_combine($line_names,array_fill(0,$n_lines,'-'));
			$nemp = count($empty);
			$marker_uid = implode(",",$marker_uid);
			$line_str = implode($delimiter,$line_names);
			// $firephp = log($nelem." ".$n_lines);
			
			// write output file header
			$outputheader = "<Annotated>\n<Transposed>".$delimiter."Yes\n";
			$outputheader .= "<Taxa_Number>".$delimiter.$n_lines."\n";
			$outputheader .= "<Locus_Number>".$delimiter.$nelem."\n";
			$outputheader .= "<Poly_Type>".$delimiter."Catagorical\n";
			$outputheader .= "<Delimited_Values>".$delimiter."No\n";
			$outputheader .= "<Taxon_Name>".$delimiter.$line_str."\n";
			$outputheader .= "<Chromosome_Number>".$delimiter."<Genetic_Position>".$delimiter."<Locus_Name>".$delimiter."<Value>\n";

         $sql = "SELECT mim.chromosome, mim.start_position, lr.line_record_name as lname, m.marker_name AS mname,
                    CONCAT(a.allele_1,a.allele_2) AS value
			FROM
            markers as m,
			markers_in_maps as mim,
			map,
			mapset,
            line_records as lr,
            alleles as a,
            tht_base as tb,
            genotyping_data as gd
			WHERE
            a.genotyping_data_uid = gd.genotyping_data_uid
				AND mim.marker_uid = m.marker_uid
				AND m.marker_uid = gd.marker_uid
				AND gd.marker_uid IN ($marker_uid)
				AND mim.map_uid = map.map_uid
				AND map.mapset_uid = mapset.mapset_uid
				AND mapset.mapset_uid = $selected_map
				AND tb.line_record_uid = lr.line_record_uid
				AND gd.tht_base_uid = tb.tht_base_uid
				AND tb.experiment_uid IN ($experiments)
		  ORDER BY mim.chromosome,mim.start_position, m.marker_uid, lname";


		$last_marker = "somemarkername";
		$res = mysql_query($sql) or die(mysql_error());
		
		$outarray = $empty;
		$cnt = $num_markers = 0;
		while ($row = mysql_fetch_array($res)){
				//first time through loop
				if ($cnt==0) {
					$last_marker = $row['mname'];
					$pos = $row['start_position'];
					$chrom = $row['chromosome'];
				}
				
			if ($last_marker != $row['mname']){  
					// Close out the last marker
					$output .= "$chrom\t$pos\t$last_marker\t";
					$outarray = implode("",$outarray);
					$output .= $outarray."\n";
					//reset output arrays for the next line
					$outarray = $empty;
					$lname = $row['lname'];	//start new line			
					$outarray[$lname] = $lookup[$row['value']];
					$last_marker = $row['mname'];
					$pos = $row['start_position'];
					$chrom = $row['chromosome'];
					$num_markers++;
			} else {
					 $lname = $row['lname'];				
					 $outarray[$lname] = $lookup[$row['value']];
			}
			$cnt++;
		}
		
		  //save data from the last line
		  $output .= "$chrom\t$pos\t$last_marker\t";
		  $outarray = implode("",$outarray);
		  $output .= $outarray."\n";
		  $num_markers++;
		  

		  return $outputheader.$output;

	}

	/**
	 * create map file for tassel V3
	 * @param array $lines
         * @param array $markers
         * @param string $dtype
	 * @return string
	 */
	function type1_build_geneticMap($lines,$markers,$dtype)
	{
		$delimiter ="\t";
		$output = '';
		$doneheader = false;
	
         if (isset($_SESSION['selected_map'])) {
           $selected_map = $_SESSION['selected_map'];
         } else {
           die("<font color=red>Error - map should be selected before download</font>");
         }

         if (count($markers)>0) {
           $markers_str = implode(",", $markers);
         } else {
           die("<font color=red>Error - markers should be selected before download</font>");
         }


                //generate an array of selected markers that can be used with isset statement
                foreach ($markers as $temp) {
                  $marker_lookup[$temp] = 1;
                }

                $sql = "select marker_uid, marker_name from allele_byline_idx order by marker_uid";
                $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
                $i=0;
                while ($row = mysql_fetch_array($res)) {
                  $marker_list[$i] = $row[0];
                  $marker_list_name[$i] = $row[1];
                  $i++;
                }

		$sql = "select markers.marker_uid,  mim.chromosome, CAST(1000*mim.start_position as UNSIGNED) from markers, markers_in_maps as mim, map, mapset
		where markers.marker_uid IN ($markers_str)
                AND mim.marker_uid = markers.marker_uid
		AND mim.map_uid = map.map_uid
		AND map.mapset_uid = mapset.mapset_uid
		AND mapset.mapset_uid = $selected_map
                order by mim.chromosome, CAST(100*mim.start_position as UNSIGNED), markers.marker_name";
		$res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
		while ($row = mysql_fetch_array($res)) {
		  $uid = $row[0];
		  $chr = $row[1];
		  $pos = $row[2];
		  $marker_list_mapped[$uid] = "$chr\t$pos";
		}
	
                $marker_list_all = $marker_list_mapped;
                //get lines and filter to get a list of markers which meet the criteria selected by the user
                $num_maf = $num_miss = 0;
                foreach ($marker_list as $i => $uid) {
                  $marker_name = $marker_list_name[$i];
                  $marker_list_all_name[$uid] = $marker_name;
                  if (isset($marker_lookup[$uid])) {
                      if (isset($marker_list_all[$uid])) {
                      } else {
                        $marker_list_all[$uid] = 0;
                      }
                  }
                }
                if (count($marker_list_all) == 0) {
                   $output = "no mapped data found";
                   return $output;
                }

        // make an empty marker with the lines as array keys 
        $nelem = count($marker_uid);
        $n_lines = count($lines);
                $empty = array_combine($lines,array_fill(0,$n_lines,'-'));
                $nemp = count($empty);
                $line_str = implode($delimiter,$lines);
                // $firephp = log($nelem." ".$n_lines);

                // write output file header
                if ($dtype == "FJ") {
                  $outputheader = "# fjFile = MAP\n";
                } elseif ($dtype == "R") {
                  $outputheader = "chr\tpos\n";
                } else {
                  $outputheader = "<Map>\n";
                }

		$num_markers = 0;
		/* foreach( $marker_uid as $cnt => $uid) { */
		foreach($marker_list_all as $uid=>$value) {
		    $marker_name = $marker_list_all_name[$uid];
		    $map_loc = $marker_list_mapped[$uid];
		    $output .= "$marker_name\t$map_loc\n";
		    $num_markers++;
		}
		
	  return $outputheader.$output;
    }

    /**
     * create pedigree output file for qtlminer
     * @param string $experiments
     */
	function type1_build_pedigree_download($experiments)
	{
		$delimiter ="\t";
		// output file header for QTL Miner Pedigree files
		$outputheader = "Inbred" . $delimiter . "Parent1" . $delimiter . "Parent2" . $delimiter . "Contrib1" . $delimiter . "Contrib2";
		//echo "Inbred  Parent1   Parent2 Contrib1  Contrib2";
        // get all line records in the incoming experiments
      //// $firephp = FirePHP::getInstance(true);
		//// $firephp->log($outputheader);  
		$sql = "SELECT DISTINCT datasets_uid
					FROM datasets_experiments
					WHERE experiment_uid IN ($experiments)";

		$res=	mysql_query($sql) or die(mysql_error());
        		
		//loop through the datasets
		$output = '';
		while($row=mysql_fetch_array($res))
		{
			$datasets_uid[]=$row['datasets_uid'];
			//// $firephp->log($row['datasets_uid']); 
		}
		foreach($datasets_uid as $ds){
			
		  $sql_ds = "SELECT datasets_pedigree_data FROM datasets WHERE datasets_uid = $ds";
		  //// $firephp->log($sql_ds);
		  $res=	mysql_query($sql_ds) or die(mysql_error());
		  $resdata=mysql_fetch_array($res);
		   $outdata=$resdata['datasets_pedigree_data'];
		   //// $firephp->log($outdata);
		  $output .= $outdata;
		}

		return $outputheader."\n".$output;
	}
	
	/**
	 * create output file for qtlminer
	 * @param string $experiments
	 * @return string
	 */
	function type1_build_inbred_download($experiments)
	{
		$newline ="\n";
		// output file header for QTL Miner Pedigree files
		$output = "Inbred\n";
		
        // get all line records in the incoming experiments
      //// $firephp = FirePHP::getInstance(true);
		//// $firephp->log($outputheader);  
		$sql = "SELECT DISTINCT line_record_name
					FROM tht_base,line_records
					WHERE line_records.line_record_uid=tht_base.line_record_uid
					AND experiment_uid IN ($experiments)";

		$res=	mysql_query($sql) or die(mysql_error());
        		
		//loop through the lines

		while($row=mysql_fetch_array($res))
		{
			$output .=$row['line_record_name']."\n";
			//// $firephp->log($row['datasets_uid']); 
		}

		return $output;
	}	
	
}// end class
