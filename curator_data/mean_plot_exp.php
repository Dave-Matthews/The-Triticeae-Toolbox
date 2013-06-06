<?php
/**
 * Canopy Spectral Reflectance, Fieldbook import
 * 
 * PHP version 5.3
 * Prototype version 1.5.0
 * 
 * @category PHP
 * @package  T3
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @version  GIT: 2
 * @link     http://triticeaetoolbox.org/wheat/curator_data/mean_plot_exp.php
 * 
 */

require 'config.php';
include($config['root_dir'] . 'includes/bootstrap_curator.inc');

connect();
$mysqli = connecti();
loginTest();

new Data_Check($_GET['function']);

class Data_Check
{
  /**
   * Using the class's constructor to decide which action to perform
   * @param unknown_type $function
   */
  public function __construct($function = null) {
    switch($function)
      {
      case 'typeDatabase':
        $this->type_Database(); /* update database */
        break;
      default:
        $this->typeExperimentCheck(); /* intial case*/
        break;
      }
  }

/**
 * check experiment data before loading into database
 */
private function typeExperimentCheck()
        {
                global $config;
                include($config['root_dir'] . 'theme/admin_header.php');
                echo "<h2>Calculate and load Plot Level Data</h2>";
                $this->type_Experiment_Name();
                $footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
        }

/***
 * check experiment data before loading into database
 */
private function type_Experiment_Name() {
   global $mysqli;
   if (empty($_POST['exper_uid'])) {
     echo "Error: invalid experiment uid<br>\n";
   } else {
     $unique_str = chr(rand(65,80)).chr(rand(65,80)).chr(rand(65,80)).chr(rand(65,80));
     mkdir("/tmp/tht/$unique_str");
     $filename1 = "plot-pheno.txt";
     $filename2 = "plot-map.txt";
     $filename3 = "mean-cmd.R";
     $filename4 = "mean-output.txt";
     $filename5 = "process_error.txt";
     $h = fopen("/tmp/tht/$unique_str/$filename1","w");

     $uid = $_POST['exper_uid'];
 
     $sql = "select DISTINCT line_uid from phenotype_plot_data, fieldbook where fieldbook.plot_uid = phenotype_plot_data.plot_uid AND phenotype_plot_data.experiment_uid = $uid";
     //echo "$sql<br>\n";
     $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
     while ($row = mysqli_fetch_array($res)) {
       $line_ary[] = $row[0];
     }
     $sql = "select DISTINCT plot_uid from phenotype_plot_data where experiment_uid = $uid";
     $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
     while ($row = mysqli_fetch_array($res)) {
       $plot_ary[] = $row[0];
     }
     $sql = "select DISTINCT phenotype_plot_data.phenotype_uid, phenotypes_name from phenotype_plot_data, phenotypes  where phenotype_plot_data.phenotype_uid = phenotypes.phenotype_uid AND experiment_uid = $uid order by phenotype_plot_data.phenotype_uid";
     $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
     while ($row = mysqli_fetch_array($res)) {
       $pheno_ary[] = $row[0];
       $pheno_ary_name[] = $row[1];
     }

     //generate input data file for R, trait file with fieldbook information
     $nelem = count($pheno_ary);
     $empty = array_combine($pheno_ary, array_fill(1, $nelem, 'NA'));
     $out_list = implode("\t", $pheno_ary_name);
     fwrite($h,"line\tplot\treplication\tblock\tsubblock\ttreatment\t$out_list\n");
     foreach ($plot_ary as $plot_uid) {
       $output = $empty;
       $sql = "select plot, replication, block, subblock, treatment, check_id, line_uid from fieldbook where plot_uid = $plot_uid";
       $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
       if ($row = mysqli_fetch_array($res)) {
         $plot = $row[0];
         $repl = $row[1];
         $block = $row[2];
         $subblock = $row[3];
         $treatment = $row[4];
         $check = $row[5];
         $line_uid = $row[6];
       }
       $sql = "select value, phenotype_uid from phenotype_plot_data where experiment_uid = $uid AND plot_uid = $plot_uid order by phenotype_uid";
       //echo "$sql<br>\n";
       $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
       while ($row = mysqli_fetch_array($res)) {
          $val = $row[0];
          $phen_uid = $row[1];
          //echo "found $plot pheno=$phen_uid val=$val<br>\n";
          $output[$phen_uid] = $val;
       }
       $out_list = implode ("\t", $output);
       fwrite($h,"$line_uid\t$plot\t$repl\t$block\t$subblock\t$treatment\t$out_list\n");
     }
     fclose($h);

     //generate R command file
     $h = fopen("/tmp/tht/$unique_str/$filename3","w");
     fwrite($h, "setwd(\"/tmp/tht/$unique_str\")\n");
     fwrite($h, "file_out <- \"$filename4\"\n");
     fwrite($h, "plotData <- read.table(\"$filename1\", sep=\"\t\", header=TRUE, stringsAsFactors=FALSE)\n");
     fclose($h);

     //use R to calculate statistics
     exec("cat /tmp/tht/$unique_str/$filename3 ../R/mean-exp.R | R --vanilla > /dev/null 2> /tmp/tht/$unique_str/$filename5");

     if (file_exists("/tmp/tht/$unique_str/$filename4")) {
       echo "found mean results from R script<br>\n";
       $h = fopen("/tmp/tht/$unique_str/$filename4","r");
       while ($line=fgets($h)) {
         echo "$line<br>\n";
       }
       fclose($h);
     }

     if (file_exists("/tmp/tht/$unique_str/$filename5")) {
       $h = fopen("/tmp/tht/$unique_str/$filename5","r");
       while ($line=fgets($h)) {
         echo "$line<br>\n";
       }
       fclose($h);
     }

     $h = fopen("/tmp/tht/$unique_str/$filename2","w");
     $max_row = $max_col = 0;
     $sql = "select row_id, column_id from fieldbook where experiment_uid = $uid";
     $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
     while ($row = mysqli_fetch_array($res)) {
       $row_id = $row[0];
       $col_id = $row[1];
       $row_ary[] = $row_id;
       $col_ary[] = $col_id;
       if ($row_id > $max_row) { $max_row = $row_id; }
       if ($col_id > $max_col) { $max_col = $col_id; }
     }
     $nelem = count($col_ary);
     $empty = array_combine($col_ary, array_fill(1, $nelem, 'NA'));
     $out_list = implode("\t", $col_ary);
     fwrite($h,"row\t$out_list\n");
     foreach ($row_ary as $row_id) {
       $sql = "select column_id, value from phenotype_plot_data, fieldbook where phenotype_plot_data.plot_uid = fieldbook.plot_id AND row_id = $row_id";
       //echo "$sql<br>\n";
       $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
       while ($row = mysqli_fetch_array($res)) {
         $col_id = $row[0];
         $val = $row[1];
         $output[$col_id] = $val;
       }
       $out_list = implode ("\t", $output);
       fwrite($h,"$row_id\t$out_list\n");
    }
    fclose($h);
  }
}
}
