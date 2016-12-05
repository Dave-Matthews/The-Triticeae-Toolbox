<?php
/**
 * Canopy Spectral Reflectance, Fieldbook import
 *
 * PHP version 5.3
 * Prototype version 1.5.0
 *
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/curator_data/input_experiment_plot_check.php
 *
 */

require 'config.php';
require $config['root_dir'] . 'includes/bootstrap2.inc';

$mysqli = connecti();

new DataCheck($_GET['function']);

class DataCheck
{
    /**
     * Using the class's constructor to decide which action to perform
     * @param unknown_type $function
     */
    public function __construct($function = null)
    {
        switch ($function) {
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
        include $config['root_dir'] . 'theme/admin_header2.php';
        echo "<h2>Heatmap of trait by field position</h2>";
        $this->type_Experiment_Name();
        $footer_div = 1;
        include $config['root_dir'].'theme/footer.php';
    }

    /**
     * create data file, separate result for each trait
     * 1. display table or values in row column format
     * 2. call R script for displaying heatmap
     */
    private function type_Experiment_Name()
    {
        global $mysqli;
        $sql = "select phenotype_uid, phenotypes_name from phenotypes";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli). $sql);
        while ($row = mysqli_fetch_assoc($res)) {
            $phen_uid = $row["phenotype_uid"];
            $name = $row["phenotypes_name"];
            $phen_list[$phen_uid] = $name;
        }

   if (empty($_GET['uid'])) {
     echo "Error: invalid experiment uid<br>\n";
     die();
   } 
   $exp_uid = $_GET['uid'];

   $sql = "select trial_code from experiments where experiment_uid = ?";
   if ($stmt = mysqli_prepare($mysqli, $sql)) {
       mysqli_stmt_bind_param($stmt, "i", $exp_uid);
       mysqli_stmt_execute($stmt);
       mysqli_stmt_bind_result($stmt, $name);
       mysqli_stmt_fetch($stmt);
       mysqli_stmt_close($stmt);
   }
   echo "$name - \n";
   echo "<a href=display_map_exp.php?uid=$exp_uid>Numeric map</a><br>";

   $sql = "select distinct phenotype_uid from phenotype_plot_data where experiment_uid = ?";
   if ($stmt = mysqli_prepare($mysqli, $sql)) {
       mysqli_stmt_bind_param($stmt, "i", $exp_uid);
       mysqli_stmt_execute($stmt);
       mysqli_stmt_bind_result($stmt, $uid);
       while (mysqli_stmt_fetch($stmt)) {
           $name = $phen_list[$uid];
           $trait_list[$uid] = $name;
       }
       mysqli_stmt_close($stmt);
   }
  
   $max_row = 0;
   $max_col = 0;
   $found = 0;
   $sql = "select plot_uid, row_id, column_id from fieldbook where experiment_uid = ?";
   if ($stmt = mysqli_prepare($mysqli, $sql)) {
       mysqli_stmt_bind_param($stmt, "i", $exp_uid);
       mysqli_stmt_execute($stmt);
       mysqli_stmt_bind_result($stmt, $plot, $row_id, $col_id);
       while (mysqli_stmt_fetch($stmt)) {
           $found = 1;
           $row_list[$plot] = $row_id;
           $col_list[$plot] = $col_id;
           if ($row_id > $max_row) { $max_row = $row_id; }
           if ($col_id > $max_col) { $max_col = $col_id; }
       }
       mysqli_stmt_close($stmt);
   }
   if ($found) {
       if (($max_row == 0) || ($max_col == 0)) {
          echo "Error: row or column information is missing from field book<br>\n";
          die();
       }
   } else {
     echo "$sql<br>\n";
     die("Error: no fieldbook entries found");
   }
   echo "<br>\n";

   $inputFile = "plotMap.txt";
   $filename1 = "setup.R";
   $errFile = "HeatMapErr.txt";
   $unique_str = chr(rand(65,80)).chr(rand(65,80)).chr(rand(65,80)).chr(rand(65,80));
   mkdir("/tmp/tht/$unique_str");
   foreach ($trait_list as $key => $val) { 
     echo "<h3>Trait = $val</h3><br>\n";
     $outputFile = "HeatMap" . $key . ".png";
     $max_val = 0;
     $sql = "select plot_uid, value from phenotype_plot_data where experiment_uid = ? and phenotype_uid = $key";
     if ($stmt = mysqli_prepare($mysqli, $sql)) {
         mysqli_stmt_bind_param($stmt, "i", $exp_uid);
         mysqli_stmt_execute($stmt);
         mysqli_stmt_bind_result($stmt, $plot_uid, $value);
         while (mysqli_stmt_fetch($stmt)) {
             $row_id = $row_list[$plot_uid];
             $col_id = $col_list[$plot_uid];
             if ($value > $max_val) $max_val = $value;
             $pheno_val[$row_id][$col_id] = $value;
         }
         mysqli_stmt_close($stmt);
     }

     $h = fopen("/tmp/tht/$unique_str/$inputFile", "w");
     for ($j=1; $j<=$max_col; $j++) {
       if ($j == 1) {
         fwrite($h,"$j");
       } else {
         fwrite($h,"\t$j");
       }
     }
     fwrite($h,"\n");
     for ($i=1; $i<=$max_row; $i++) {
        $output = "";
        for ($j=1; $j<=$max_col; $j++) {
           $value = $pheno_val[$i][$j];
           if ($j == 1) {
             $output = $value;
           } else {
             $output = $output . "\t$value";
           }
        }
        if (preg_match("/[0-9]/", $output)) {
          fwrite($h,"$i\t$output\n");
        }
     }
     fclose($h);
     $png1 = "png(\"/tmp/tht/$unique_str/$outputFile\", width=600, height=600)\n";
     $h = fopen("/tmp/tht/$unique_str/$filename1", "w");
     fwrite($h,"setwd(\"/tmp/tht/$unique_str\")\n");
     fwrite($h,$png1);
     fclose($h);
     exec("cat /tmp/tht/$unique_str/$filename1 R/PlotHeatMap.R | R --vanilla > /dev/null 2> /tmp/tht/$unique_str/$errFile");
     if (file_exists("/tmp/tht/$unique_str/$errFile")) {
         $h = fopen("/tmp/tht/$unique_str/$errFile", "r");
         while ($line=fgets($h)) {
             echo "$line<br>\n";
         }
         fclose($h);
     }
     if (file_exists("/tmp/tht/$unique_str/$outputFile")) {
         print "<img src=\"/tmp/tht/$unique_str/$outputFile\" /><br>";
     } else {
         echo "Error in R script R/PlotHeatMap.R<br>\n";
     }
   }
}
}
