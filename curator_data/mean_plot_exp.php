<?php
/**
 * Plot level phenotype import
 *
 * PHP version 5.3
 * Prototype version 1.5.0
 *
 * @author  Clay Birkett <clb343@cornell.edu>
 * @license http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link    http://triticeaetoolbox.org/wheat/curator_data/mean_plot_exp.php
 */

require 'config.php';
require $config['root_dir'] . 'includes/bootstrap_curator.inc';

$mysqli = connecti();
loginTest();

new Data_Check($_POST['function']);

/**
 *  Using a PHP class to implement the import feature
 *
 * @author  Clay Birkett <clb343@cornell.edu>
 * @license http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link    http://triticeaetoolbox.org/wheat/curator_data/mean_plot_exp.php
 **/

class Data_Check
{
    /**
     * Using the class's constructor to decide which action to perform
     *
     * @param string $function action to perform
     */
    public function __construct($function = null)
    {
        switch ($function) {
            case 'typeDatabase':
                $this->typeDatabase(); /* update database */
                break;
            default:
                $this->typeExperimentCheck(); /* intial case*/
                break;
        }
    }

    /**
     * Display header and footer
     *
     * @return NULL
     */
    private function typeDatabase()
    {
        global $config;
        include $config['root_dir'] . 'theme/admin_header.php';
        echo "<h2>Calculate and load Plot Level Data</h2>";
        $this->typeDatabaseLoad();
        $footer_div = 1;
        include_once $config['root_dir'].'theme/footer.php';
    }

    /**
     * Save calculated means to database
     *
     *  @return NULL
     */
    private function typeDatabaseLoad()
    {
        global $mysqli;
        $check_list = array();
        $unique_str = $_POST['unique_str'];
        $experiment_uid = $_POST['exper_uid'];

        $sql = "select trial_code from experiments where experiment_uid = $experiment_uid";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
        if ($row = mysqli_fetch_array($res)) {
            $trial_code = $row[0];
            echo "<h3>$trial_code</h3>";
        } else {
            die("Error: invalid experiment uid\n");
        }


        if (file_exists("/tmp/tht/$unique_str/mean-output.txt")) {
            echo "Line means<br>\n";

            //get traits from original input file because R will change rownames
            //query fieldbook table to determine if line is a check
            $count = 0;
            $h = fopen("/tmp/tht/$unique_str/plot-pheno.txt", "r");
            while ($line=fgetcsv($h, 0, ",")) {
                if ($count == 0) {
                    $count_item = 1;
                    $count_trait = 1;
                    //echo "count = $count<br>\n";
                    foreach ($line as $trait) {
                        //echo "count_item = $count_item<br>\n";
                        if ($count_item > 6) {
                            $sql = "select phenotype_uid from phenotypes where phenotypes_name = '$trait'";
                            $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
                            if ($row = mysqli_fetch_array($res)) {
                                $phenotype_list[$count_trait] = $row[0];
                                $phenotype_name[$count_trait] = $trait;
                                //echo "phenotype_list[$count_trait] = $row[0]<br>\n";
                            } else {
                                echo "$trait not found<br>\n";
                            }
                            $count_trait++;
                        }
                        $count_item++;
                    }
                } else {
                    $line_uid = $line[0];
                    $sql = "select check_id from fieldbook where experiment_uid = $experiment_uid and line_uid = $line_uid order by check_id desc";
                    //echo "$sql<br>\n";
                    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
                    if ($row = mysqli_fetch_array($res)) {
                        $check = $row[0];
                        if ($check == 1) {
                            $check_list[$line_uid] = 1;
                        }
                    }
                }
                $count++;
            }
            fclose($h);

         //how many lines are check
            $check_list_str = "";
            foreach ($check_list as $key => $value) {
                if ($check_list_str == "") {
                    $check_list_str = $key;
                } else {
                    $check_list_str = $check_list_str . ", $key";
                }
            }
            $count = count($check_list);
            if ($count > 0) {
                echo "identified line $check_list_str as check<br>\n";
            }
         if ($count == 0) {
             echo "no lines identified as check<br>\n";
         }
       
         $count = 1;
         $h = fopen("/tmp/tht/$unique_str/mean-output.txt","r");
         echo "<table><tr><td><td>";
         while ($line=fgetcsv($h, 0, " ")) {
             if ($count == 1) {
                 $count_item = 0;
                 foreach ($line as $line_item) {
                     echo "<td>$line_item<td>";
                 }
             } else {
                 $count_item = 0;
                 echo "<tr>";
                 foreach ($line as $line_item) {
                     if ($count_item == 0) {
                         echo "<td>$line_item";
                         if ($check_list[$line_item]) {
                             echo " check";
                         }
                     } else {
                         $line_uid = $line[0];
                         if ($check_list[$line_uid]) {
                             $sql_opt = " and check_line = 'yes'";
                             $check_line = "yes";
                         } else {
                             $sql_opt = "";
                             $check_line = "no";
                         }
                         $sql = "select tht_base_uid from tht_base where line_record_uid=$line_uid and experiment_uid=$experiment_uid $sql_opt";
                         //echo "$sql<br>\n";
                         $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
                         if ($row = mysqli_fetch_array($res)) {
                             $tht_base_uid = $row[0];
                         } else {
                             $sql = "insert into tht_base (line_record_uid, experiment_uid, check_line) values ($line_uid, $experiment_uid, '$check_line')";
                             $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
                             $tht_base_uid = mysqli_insert_id($mysqli);
                             //echo "$sql<br>\n";
                         }
                         //echo "tht_base_uid = $tht_base_uid<br>\n";
                         $phenotype_uid = $phenotype_list[$count_item];
                         $trait = $phenotype_name[$count_item];
                         //echo "phenotype_uid = $phenotype_uid $count_item<br>\n";
                         $sql = "select phenotype_data_uid from phenotype_data where phenotype_uid = $phenotype_uid and tht_base_uid = $tht_base_uid";
                         //echo "$sql<br>\n";
                         $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql<br>$count_item");
                         if ($row = mysqli_fetch_array($res)) {
                             if (preg_match("/\d/", $line_item)) {
                                 $sql = "update phenotype_data set phenotype_uid = $phenotype_uid, tht_base_uid = $tht_base_uid, value = '$line_item', updated_on = NOW() where phenotype_uid = $phenotype_uid and tht_base_uid = $tht_base_uid";
                                 $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
                             } else {
                                 $sql = "update phenotype_data set phenotype_uid = $phenotype_uid, tht_base_uid = $tht_base_uid, value = NULL, updated_on = NOW() where phenotype_uid = $phenotype_uid and tht_base_uid = $tht_base_uid";
                                 $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
                             }
                             $msg = "update<td>$line_item\n";
                         } else {
                             if (preg_match("/\d/", $line_item)) {
                                 $sql = "insert into phenotype_data (phenotype_uid, tht_base_uid, value, updated_on, created_on) values ($phenotype_uid, $tht_base_uid, '$line_item', NOW(), NOW())";
                                 $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
                                 $msg = "insert<td>$line_item\n";
                             } else {
                                 $msg = "ignore<td>NULL\n";
                             }
                         }
                         echo "<td>$msg";
                     }
                     $count_item++;
                 }
             }
             $count++;
         }
         fclose($h);
         echo "</table>\n";
     } else {
         echo "$unique_str file does not exists\n";
     }

     if (file_exists("/tmp/tht/$unique_str/metaData.txt")) {
         $count = 0;
         echo "<br>Trial summary<br>\n";
         echo "<table><tr><td>trait<td><td>mean<td>standard error<td>num of repl";
         $h = fopen("/tmp/tht/$unique_str/metaData.txt","r");
         while ($line=fgetcsv($h, 0, " ")) {
             if ($count == 0) {
             } else {
                 echo "<tr><td>$line[0]";
                 //in some cases the stderr will not be calculated
                 if (!preg_match("/\d/", $line[2])) {
                     $line[2] = "NULL";
                 }
                 $phenotype_uid = $phenotype_list[$count];
                 $sql = "select phenotype_mean_data_uid from phenotype_mean_data where phenotype_uid = $phenotype_uid and experiment_uid = $experiment_uid";
                 //echo "$sql<br>\n";
                 $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
                 if ($row = mysqli_fetch_array($res)) {
                     $sql = "update phenotype_mean_data set mean_value = $line[1], standard_error = $line[2], number_replicates = $line[3], mean_calculation = 'calculated', updated_on = NOW()
                     where phenotype_uid = $phenotype_uid and experiment_uid = $experiment_uid";
                     $msg = "<td>update<td>$line[1]<td>$line[2]<td>$line[3]";
                 } else {
                     $sql = "insert into phenotype_mean_data (experiment_uid, phenotype_uid, mean_value, standard_error, number_replicates, mean_calculation, created_on, updated_on) values ($experiment_uid, $phenotype_uid, $line[1], $line[2], $line[3], \"calculated\", NOW(), NOW())";
                     $msg = "<td>insert<td>$line[1]<td>$line[2]<td>$line[3]";
                 }
                 $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
                 echo "$msg";
             }
             $count++;
         }
         fclose($h);
         echo "</table>\n";
     }

     //update string of measured traits in the experiment
     $sql = "SELECT p.phenotype_uid AS id, p.phenotypes_name AS name
             FROM phenotypes AS p, tht_base AS t, phenotype_data AS pd
             WHERE pd.tht_base_uid = t.tht_base_uid
             AND p.phenotype_uid = pd.phenotype_uid
             AND t.experiment_uid = $experiment_uid
             GROUP BY p.phenotype_uid";
     $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
     while ($row = mysqli_fetch_array($res)) {
       $phenotypes[] = $row['name'];
     }
     $countfound = count($phenotypes);
     if ($countfound > 0) {
       $phenotypes = implode(', ',$phenotypes);
       $sql = "UPDATE experiments SET traits =('$phenotypes') WHERE experiment_uid = $experiment_uid";
       $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>$sql");
     } else {
       $badtc = mysql_grab("select trial_code from experiments where experiment_uid = $experiment_uid");
       echo "Warning: There are no trait values for Trial <b>$badtc</b>.<br>";
       $emptytrials[$badtc] = $exid;
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
     $unique_str = chr(rand(65,90)).chr(rand(65,90)).chr(rand(65,90)).chr(rand(65,90));
     mkdir("/tmp/tht/$unique_str");
     $filename1 = "plot-pheno.txt";
     $filename2 = "plot-map.txt";
     $filename3 = "mean-cmd.R";
     $filename4 = "mean-output.txt";
     $filename5 = "process_error.txt";
     $h = fopen("/tmp/tht/$unique_str/$filename1","w");

     $uid = $_POST['exper_uid'];
     $sql = "select trial_code from experiments where experiment_uid = $uid";
     $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
     if ($row = mysqli_fetch_array($res)) {
       $trial_code = $row[0];
       echo "<h3>$trial_code</h3>";
     } else {
       die("Error: invalid experiment uid\n");
     }
 
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
     foreach ($pheno_ary as $puid) {
        $sql = "select mean_calculation from phenotype_mean_data where experiment_uid = $uid and phenotype_uid = $puid";
        $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
        if ($row = mysqli_fetch_array($res)) {
          $mean_calculation[$puid] = $row[0];
        } else {
          $mean_calculation[$puid] = "not loaded";
        }
     }

     //generate input data file for R, trait file with fieldbook information
     $nelem = count($pheno_ary);
     $empty = array_combine($pheno_ary, array_fill(1, $nelem, 'NA'));
     //$out_list = "'" . implode("','", $pheno_ary_name) . "'";
     $out_list = implode(",", $pheno_ary_name);
     //$out_list = implode(",", $pheno_ary);
     fwrite($h,"line,plot,replication,block,subblock,treatment,$out_list\n");
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
       } else {
         die("Error: $sql");
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
       $out_list = implode (",", $output);
       fwrite($h,"$line_uid,$plot,$repl,$block,$subblock,$treatment,$out_list\n");
     }
     fclose($h);

     //generate R command file
     $h = fopen("/tmp/tht/$unique_str/$filename3","w");
     fwrite($h, "setwd(\"/tmp/tht/$unique_str\")\n");
     //fwrite($h, "file_out <- \"$filename4\"\n");
     //fwrite($h, "plotData <- read.table(\"$filename1\", sep=\"\\t\", header=TRUE, stringsAsFactors=FALSE)\n");
     fclose($h);

     //use R to calculate statistics
     //exec("cat /tmp/tht/$unique_str/$filename3 ../R/mean-exp.R | R --vanilla > /dev/null 2> /tmp/tht/$unique_str/$filename5");
     exec("cat /tmp/tht/$unique_str/$filename3 ../R/getTrialMeans.R | R --vanilla > /dev/null 2> /tmp/tht/$unique_str/$filename5");

     if (file_exists("/tmp/tht/$unique_str/$filename4")) {
         echo "Line means<br>\n";
         echo "<table>";
         $count = 1;
         $h = fopen("/tmp/tht/$unique_str/$filename4","r");
         while ($line=fgetcsv($h, 0, " ")) {
             if ($count == 1) {
                 echo "<tr><td style=\"width:150px\">line";
             } else {
                 echo "<tr>";
                 $line_uid = $line[0];
                 $sql = "select line_record_name from line_records where line_record_uid = $line_uid";
                 $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
                 if ($row = mysqli_fetch_array($res)) {
                     $line[0] = $row[0];
                 } else {
                     $line[0] = "unknown $line[0]";
                 }
             }
             $count++;
             foreach ($line as $key => $val) {
                 if (($key > 0) && is_numeric($val)) {
                     $val = number_format ($val, 2);
                 }
                 echo "<td style=\"width:100px\">$val";
             }
         }
         fclose($h);
         echo "</table><br>";
     }

     if (file_exists("/tmp/tht/$unique_str/metaData.txt")) {
         $count = 1;
         echo "<tr><td colspan=3>Trial summary\n";
         echo "<table>";
         $h = fopen("/tmp/tht/$unique_str/metaData.txt","r");
         while ($line=fgetcsv($h, 0, " ")) {
             if ($count == 1) {
                 //echo "<tr><td><td>$line[0]<td>$line[1]<td>$line[2]\n";
                 $header = $line;
             } else {
                 //echo "<tr><td>$line[0]<td>$line[1]<td>$line[2]<td>$line[3]\n";
                 $line_array[] = $line;
             }
             $count++;
         }
         fclose($h);
         echo "\n<tr><td style=\"width:150px\">";
         foreach ($line_array as $trait) {
             echo "<td style=\"width:100px\">$trait[0]";
         }
         echo "\n<tr><td>$header[0]";
         foreach ($line_array as $trait) {
             if (is_numeric($trait[1])) {
                 $trait[1] = number_format ($trait[1], 2);
             }
             echo "<td style=\"width:100px\">$trait[1]";
         }
         echo "\n<tr><td>$header[1]";
         foreach ($line_array as $trait) {
             if (preg_match("/\d/", $trait[2])) {
                 $tmp = number_format($trait[2],3);
             } 
             echo "<td>$tmp";
         }
         echo "\n<tr><td>$header[2]";
         foreach ($line_array as $trait) {
             $tmp = number_format($trait[3],3);
             echo "<td>$tmp";
         }
         echo "\n</table><br>";
     }
     echo "<tr><td>Current calculation method in database\n";
     echo "<table>";
     echo "<tr><td style=\"width:150px\">";
     foreach ($pheno_ary_name as $name) {
         echo "<td style=\"width:100px\">$name";
     }
     echo "<tr><td style=\"width:150px\"><td>";
     $warning = "";
     foreach ($pheno_ary as $puid) {
         $method = $mean_calculation[$puid];
         if ($method == "import") {
             $warning = "<font color=red>Warning: previously imported values will be overwritten</font>";
         }
         echo "$mean_calculation[$puid]<td>";
     }
     echo "</table><br>";
          
     echo "$warning<br>\n";
     if (file_exists("/tmp/tht/$unique_str/$filename5")) {
       $h = fopen("/tmp/tht/$unique_str/$filename5","r");
       while ($line=fgets($h)) {
         echo "$line<br>\n";
       }
       fclose($h);
     } 

     ?>
     <br>
     <form action="curator_data/mean_plot_exp.php" method="POST" enctype="multipart/form-data">
     <input type="hidden" name="function" value="typeDatabase">
     <input type="hidden" name="exper_uid" value="<?php echo $uid; ?>">
     <input type="hidden" name="unique_str" value="<?php echo $unique_str; ?>">
     <input type="submit" value="Save calculated means">
     </form>
     <?php

  }
}
}
