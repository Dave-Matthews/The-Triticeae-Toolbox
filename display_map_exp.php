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
 * @link     http://triticeaetoolbox.org/wheat/curator_data/input_experiment_plot_check.php
 * 
 */

require 'config.php';
include($config['root_dir'] . 'includes/bootstrap_curator.inc');

$mysqli = connecti();

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
                echo "<h2>Display map of trait value</h2>";
                $this->type_Experiment_Name();
                $footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
        }

/***
 * create data file, separate result for each trait
 * 1. display table or values in row column format
 * 2. call R script for displaying heatmap
 */
private function type_Experiment_Name() {
   global $mysqli;
   $sql = "select phenotype_uid, phenotypes_name from phenotypes";
   $res = mysqli_query($mysqli,$sql) or die (mysqli_error($mysqli). $sql);
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
   $sql = "select distinct phenotype_uid from phenotype_plot_data where experiment_uid = $exp_uid";  
   $res = mysqli_query($mysqli,$sql) or die (mysqli_error($mysqli));
   while ($row = mysqli_fetch_assoc($res)) {
       $uid = $row["phenotype_uid"];
       $name = $phen_list[$uid];
       $trait_list[$uid] = $name;
       //echo "$name<br>\n";
   }
  
   $max_row = 0;
   $max_col = 0;
   $found = 0;
   $sql = "select plot_uid, row_id, column_id from fieldbook where experiment_uid = $exp_uid";
   $res = mysqli_query($mysqli,$sql) or die (mysqli_error($mysqli) . $sql);
   while ($row = mysqli_fetch_assoc($res)) {
       $found = 1;
       $plot = $row["plot_uid"];
       $row_id = $row["row_id"];
       $col_id = $row["column_id"];
       $row_list[$plot] = $row_id;
       $col_list[$plot] = $col_id;
       if ($row_id > $max_row) { $max_row = $row_id; }
       if ($col_id > $max_col) { $max_col = $col_id; }
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

   foreach ($trait_list as $key => $val) { 
     echo "$val<br>\n";
     $sql = "select plot_uid, value from phenotype_plot_data where experiment_uid = $exp_uid and phenotype_uid = $key";
     $res = mysqli_query($mysqli,$sql) or die (mysqli_error($mysqli));
     while ($row = mysqli_fetch_assoc($res)) {
       $plot_uid = $row["plot_uid"];
       $row_id = $row_list[$plot_uid];
       $col_id = $col_list[$plot_uid];
       $value = $row["value"];
       $pheno_val[$row_id][$col_id] = $value;
     }

     echo "<table><tr><td>";
     for ($j=1; $j<=$max_col; $j++) {
       echo "<td>$j\n";
     }
     for ($i=1; $i<=$max_row; $i++) {
        echo "<tr><td>$i\n";
        for ($j=1; $j<=$max_col; $j++) {
           $value = $pheno_val[$i][$j];
           echo "<td>$value"; 
        }
        echo "<br>\n";
     }
     echo "</table>";
     echo "<br><br>\n";
   }
}
}
