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
require $config['root_dir'] . 'includes/bootstrap.inc';

$mysqli = connecti();

new DataCheck($_GET['function']);

class DataCheck
{
  /**
   * Using the class's constructor to decide which action to perform
   * @param string $function
   */
    public function __construct($function = null)
    {
        switch ($function) {
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
        echo "<h2>Numeric map of trait by field position</h2>";
        $this->typeExperimentName();
        $footer_div = 1;
        include $config['root_dir'].'theme/footer.php';
    }

/***
 * create data file, separate result for each trait
 * 1. display table or values in row column format
 * 2. call R script for displaying heatmap
 */
    private function typeExperimentName()
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
   echo "<a href=display_heatmap_exp.php?uid=$exp_uid>Heat map</a><br>";
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
     die("Error: no fieldbook entries found");
   }
   echo "<br>\n";

   foreach ($trait_list as $key => $val) { 
     echo "$val<br>\n";
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
