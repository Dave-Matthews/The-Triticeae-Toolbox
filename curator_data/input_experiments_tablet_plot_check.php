<?php
/**
 * Canopy Spectral Reflectance, Phenotype Results import
 *
 * PHP version 5.3
 * Prototype version 1.5.0
 *
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/curator_data/input_tablet_plot_check.php
 *
 */

require 'config.php';
require $config['root_dir'] . 'includes/bootstrap_curator.inc';
set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/PHPExcel/Classes');
require '../lib/PHPExcel/Classes/PHPExcel/IOFactory.php';

$mysqli = connecti();
loginTest();

$row = loadUser($_SESSION['username']);

//needed for mac compatibility
ini_set('auto_detect_line_endings', true);

ob_start();
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();

new Data_Check($_GET['function']);

/** Using a PHP class to implement Phenotype Results import
 *
 * @category PHP
 * @package  T3
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/curator_data/input_tablet_plot_check.php
 *
 */
class Data_Check
{
    /**
     * Using the class's constructor to decide which action to perform
     *
     * @param unknown_type $function action to perform
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
    *
    * @return NULL
    */
    public function typeExperimentCheck()
    {
        global $config;
        include $config['root_dir'] . 'theme/admin_header.php';
        echo "<h2>Plot Level Data Import</h2>";
        $this->typeExperimentName();
        $footer_div = 1;
        include $config['root_dir'].'theme/footer.php';
    }

    /**
     * check experiment data before loading into database
     *
     * @return NULL
     */
    public function typeExperimentName()
    {
        global $mysqli;
        ?>
        <script type="text/javascript">
        function update_database(filepath, filename, username, rawdatafile) {
        var url='<?php echo $_SERVER[PHP_SELF];?>?function=typeDatabase&expdata=' + filepath + '&file_name=' + filename + '&user_name=' + username + '&raw_data_file=' + rawdatafile;
        // Opens the url in the same window
       window.open(url, "_self");
    }
    </script>
    <!-- style type="text/css">
     th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
     table {background: none; border-collapse: collapse}
     td {border: 0px solid #eee !important;}
     h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
    </style-->
    <?php
    global $config;
    $row = loadUser($_SESSION['username']);
    $username=$row['name'];
    $tmp_dir="uploads/tmpdir_".$username."_".rand();
    $meta_path= "raw/phenotype/".$_FILES['file']['name'][0];
    $raw_path= "../raw/phenotype/".$_FILES['file']['name'][0];
 
    $replace_flag = $_POST['replace'];
    if (empty($_FILES['file']['name'][0])) {
        if (empty($_POST['filename0'])) {
             echo "missing data file<br>\n";
        } else {
            $metafile = $_POST['filename0'];
            $filename0 = $_POST['filename0'];
        }
    } else {
        $filename0 = $_FILES['file']['name'][0];
    }
    if (($_FILES['file']['name'][0] == "") && ($metafile == "")) {
        error(1, "No File Uploaded");
        print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
    } else {
        if (!empty($_FILES['file']['name'][0])) {
        $uploadfile=$_FILES['file']['name'][0];
        $uftype=$_FILES['file']['type'][0];
        if (move_uploaded_file($_FILES['file']['tmp_name'][0], $raw_path) !== TRUE) {
            echo "error - could not upload file $uploadfile<br>\n";
        } else {
            echo "Plot file: <strong>" . $_FILES['file']['name'][0] . " $FileType</strong><br>\n";
            $metafile = $raw_path;
        }
    } else {
      echo "Plot file: <strong>$metafile</strong><br>\n";
      $metafile = "../raw/phenotype/".$metafile;
    }
      $FileType = PHPExcel_IOFactory::identify($raw_path);
      switch ($FileType) {
        case 'Excel2007':
          break;
        case 'Excel5':
          break;
        case 'CSV':
          break;
        default:
          error(1, "Expecting an Excel file. <br> The type of the uploaded file is ".$FileType);
          print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
          die();
      }

      /* Read the Plot file */
      $objPHPExcel = PHPExcel_IOFactory::load($metafile);
      $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

      //read in spreadsheet until blank entry found in column "A"
      $i = 1;
      $found = 1;
      $data_line = 1;
      $last_col = "A";
      $error_flag = 0;
      $trial_code = "";
      $trial_code_array = array();
      while ($found) {
        if ($sheetData[$i]["A"] == "plot_id") {
           for ($j = "A"; $j <= "Z"; $j++) {
             $tmp = $sheetData[$i]["$j"];
             if (preg_match("/[A-Za-z0-9]/",$tmp)) {
                $header["$j"] = $tmp;
                $last_col = $j;
             }
           }
           //echo "found header line $i<br>last column = $last_col\n";
        } else {
          $tmp = $sheetData[$i]["A"];
          if (preg_match("/[A-Za-z0-9]/",$tmp)) {
            $lines_found = $data_line;
            for ($j = "A"; $j <= $last_col; $j++) {
              $tmp = $sheetData[$i]["$j"];
              $data[$data_line]["$j"] = $tmp;
              //echo "save $data_line $j $tmp<br>\n";
            }
            $plot_uid_name = $sheetData[$i]["A"];
            if (preg_match("/([^\s]+)_(\d+)_(\d+)/", $plot_uid_name, $match)) {
                $trial_code = $match[1];
                $column_id = $match[2];
                $row_id = $match[3];
                $sql = "select experiment_uid from experiments
                where trial_code = \"$trial_code\"";
                $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
                if ($row = mysqli_fetch_array($res)) {
                    $experiment_uid = $row[0];
                } else {
                    $error_flag = 1;
                    echo "<font color=red>Error: Trial code $trisl_code not found</font><br>\n";
                }
                $sql = "select plot_uid from fieldbook
                where experiment_uid = $experiment_uid 
                and row_id = $row_id
                and column_id = $column_id";
                $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
                if ($row = mysqli_fetch_array($res)) {
                    $plot_uid = $row[0];
                } else {
                    $error_flag = 1;
                    echo "<font color=red>Error: column $column_id, row $row_id not found</font><br>\n";
                }
            } else {
                echo "<font color=red>Error: Invalid plot_uid $plot_uid</font><br>\n";
            }  
            if (!isset($trial_code_array[$experiment_uid])) {
                $trial_code_array[$experiment_uid] = $trial_code;
            }
          } else {
            $found = 0;
          }
          $data_line++;
        }
        $i++;
      }
      if (!isset($experiment_uid)) {
          die("Error: No experiment found<br>\n");
      }

       $trial_code_list = implode(",", $trial_code_array);
       echo "Trial: <strong>$trial_code_list</strong><br>\n";

       //check for valid trait names
       $done = 0;
       $error = 0;
       $j = "H";
       $pheno_found = "";
       while (!$done) {
         $tmp = $header[$j];
         if (preg_match("/[A-Za-z0-9]/",$header[$j])) {
           $val = $header[$j];
           $sql = "select phenotype_uid from phenotypes where phenotypes_name = \"$val\"";
           $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
           if ($row = mysqli_fetch_array($res)) {
             $phenotype_uid = $row[0];
             $phenotype_list[$j] = $phenotype_uid;
             if ($pheno_found == "") {
               $pheno_found = $val;
             } else {
               $pheno_found = $pheno_found . ",$val"; 
             }
           } else {
             $error = 1;
             $error_flag = 1;
             echo "$val not defined in phenotypes table<br>\n";
           }
         } else {
           $done = 1;
         }
         $j++;
       }
       if ($pheno_found != "") {
         echo "Traits: <strong>$pheno_found</strong><br>\n";
       } else {
         echo "<font color=red>Error: no traits found</font><br>\n";
         return;
       }

       //check for duplicate data
       $error = 0;
       $duplicate_flag = 0;
       $count_new = 0;
       $count_upd = 0;
       for ($i=1; $i<=$lines_found; $i++) { 
         $j = "H";
         $done = 0;
         while (!$done) {
           if (isset($phenotype_list[$j])) {
             $uid = $phenotype_list[$j];
             $plot_uid_name = $data[$i]["A"];
             if (preg_match("/([^\s]+)_(\d+)_(\d+)/", $plot_uid_name, $match)) {
                $trial_code = $match[1];
                $column_id = $match[2];
                $row_id = $match[3];
                $sql = "select experiment_uid from experiments
                where trial_code = \"$trial_code\"";
                $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
                if ($row = mysqli_fetch_array($res)) {
                    $experiment_uid = $row[0];
                } else {
                    $error_flag = 1;
                    echo "<font color=red>Error: Trial code $trisl_code not found</font><br>\n";
                }
                $sql = "select plot_uid from fieldbook
                where experiment_uid = $experiment_uid 
                and row_id = $row_id
                and column_id = $column_id";
                $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
                if ($row = mysqli_fetch_array($res)) {
                    $plot_uid = $row[0];
                } else {
                    $error_flag = 1;
                    echo "<font color=red>Error: column $column_id, row $row_id not found</font><br>\n";
                }
             } else {
                echo "<font color=red>Error: bad plot_id format $plot_uid_name</font><br>\n";
             }
             $plot = $data[$i]["B"];
             if (preg_match("/\d/",$plot_uid)) {
                 $sql = "select phenotype_data_uid from phenotype_plot_data where phenotype_uid = $uid and experiment_uid = $experiment_uid and plot_uid = $plot_uid";
                 $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
                 //echo "$sql<br>\n";
                 if ($row = mysqli_fetch_array($res)) {
                     $duplicate_flag = 1;
                     $count_upd++;
                 } else {
                     $count_new++;
                 }
             }
           } else {
             $done = 1;
           }
           $j++;
         }
       }

       if ($error_flag) {
         echo "Error in data, file not loaded!<br>\n";
       } elseif (!$replace_flag && $duplicate_flag) {
         if ($count_upd > 0) {
           echo "Found $count_upd trait measurements previously loaded for plot level data<br>\n";
         } 
         if ($count_new > 0) {
           echo "Found $count_new new trait measurements for plot level data<br>\n";
         }
                   ?>
                   <form action="curator_data/input_experiments_plot_check.php" method="post" enctype="multipart/form-data">
                   Do you want to overwrite previously loaded plot level data?
                   <input id="exper_uid" type="hidden" name="exper_uid" value="<?php echo $experiment_uid; ?>">
                   <input id="replace" type="hidden" name="replace" value="Yes">
                   <input id="filename0" type="hidden" name="filename0" value="<?php echo $filename0; ?>">
                   <input type="submit" value="Yes">
                   </form>
                   <?php
       } else {

       $error_flag = 0; 
       if ($error_flag == 0) {
         $done = 0;
         $j = "B";
         for ($i = 1; $i <= $lines_found; $i++) {
           $done = 0;
           $j = "H";
           while (!$done) {
             if (isset($phenotype_list[$j])) {
                 $phenotype_uid = $phenotype_list[$j];
             } else {
                 //echo "<font color=red>Error: no trait defined for column $j</font><br>\n";
                 continue;
             }
             if (preg_match("/[A-Za-z0-9]/",$data[$i]["$j"])) {
               $val = $data[$i]["$j"];
               $plot = $data[$i]["B"];
               $plot_uid_name = $data[$i]["A"];
               if (preg_match("/([^\s]+)_(\d+)_(\d+)/", $plot_uid_name, $match)) {
                $trial_code = $match[1];
                $column_id = $match[2];
                $row_id = $match[3];
                $sql = "select experiment_uid from experiments
                where trial_code = \"$trial_code\"";
                $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
                if ($row = mysqli_fetch_array($res)) {
                    $experiment_uid = $row[0];
                } else {
                    $error_flag = 1;
                    echo "<font color=red>Error: Trial code $trisl_code not found</font><br>\n";
                }
                $sql = "select plot_uid from fieldbook
                where experiment_uid = $experiment_uid 
                and row_id = $row_id
                and column_id = $column_id";
                $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
                if ($row = mysqli_fetch_array($res)) {
                    $plot_uid = $row[0];
                } else {
                    $error_flag = 1;
                    echo "<font color=red>Error: column $column_id, row $row_id not found</font><br>\n";
                }
             } 
               $sql = "select phenotype_data_uid from phenotype_plot_data where phenotype_uid = $phenotype_uid and experiment_uid = $experiment_uid and plot_uid = $plot_uid";
               $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
               if ($row = mysqli_fetch_array($res)) {
                 $sql = "update phenotype_plot_data set value = '$val' where phenotype_uid = $phenotype_uid and experiment_uid = $experiment_uid and plot_uid = $plot_uid";
               } else {
                 $sql = "insert into phenotype_plot_data (phenotype_uid, experiment_uid, plot_uid, value, updated_on, created_on) values ( $phenotype_uid, $experiment_uid, $plot_uid, '$val', now(), now())";
               }
               $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
               //echo "$sql<br>\n";
             //} else {
             //  echo "$i $j no data<br>\n";
             }
             if ($j < $last_col) {
               $j++;
             } else {
               $done = 1;
             }
           }
         }

         /* check mean caluculation method for this experiment */
         $mean_calculation = "";
         $sql = "select mean_calculation from phenotype_experiment_info where experiment_uid = $uid";
         $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
         if ($row = mysqli_fetch_array($res)) {
           $mean_calculation = $row[0];
         }

         /* check if mean file loaded for each trait*/
         $count_new = 0;
         $count_upd = 0;
         $found_mean_data = 0;
         for ($j = "H"; $j <= $last_col; $j++) {
           $uid = $phenotype_list[$j];
           $sql = "select phenotype_mean_data_uid from phenotype_mean_data where phenotype_uid = $uid";
           $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
           if ($row = mysqli_fetch_array($res)) {
             $found_mean_data = 1;
             $count_upd++;
           } else {
             $count_new++;
           }
         }
         $total = $count_new + $count_upd;

         echo "<br>Plot file loaded successfully<br>\n";

         echo "<br><h3>Check Results</h3>\n";
         echo "<table>";
         foreach ($trial_code_array as $experiment_uid=>$trial_code) {
             echo "<tr><td>$trial_code";
             echo "<td><form action=display_plot_exp.php target=\"_blank\">";
             echo "<input type=hidden name=uid value=$experiment_uid>";
             echo "<input type=submit value=\"View database entries\">";
             //echo "<tr><td>$trial_code<td><a href=display_plot_exp.php?uid=$experiment_uid>View database entries</a>";
             echo "</form>";
             echo "<td><form action=display_map_exp.php target=\"_blank\">";
             echo "<input type=hidden name=uid value=$experiment_uid>";
             echo "View field layout <input type=submit value=\"trait values\">";
             echo "</form>";
             echo "<td><form action=display_heatmap_exp.php target=\"_blank\">";
             echo "<input type=hidden name=uid value=$experiment_uid>";
             echo "<input type=submit value=\"trait heatmap\">";
             echo "</form>";
         }
         echo "</table><br>\n";

         echo "<h3>Calculate then save mean data</h3>";
         echo "The next step is to calculate the line and trial means from the plot data.";
         echo "<br>The model is chosen base on replication and block fields of the fieldbook data";
         echo "<ul><li>CRD: use simple averages<li>RCBC: use fixed effects and return LS means<li>blocks as random effects with no replication effect";
         echo "<li>Incomplete block model: Replications fixed and blocks random</ul>";

         echo "<table>";
         foreach ($trial_code_array as $experiment_uid=>$trial_code) {
             echo "<tr><td>$trial_code<td>";
             ?>
             <form action="curator_data/mean_plot_exp.php" method="post" enctype="multipart/form-data">
             <input id="exper_uid" type="hidden" name="exper_uid" value="<?php echo $experiment_uid; ?>">
             <input id="replace" type="hidden" name="replace" value="Yes">
             <input id="mean" type="hidden" name="function" value="Yes">
             <input id="filename0" type="hidden" name="filename0" value="<?php echo $filename0; ?>">
             <input type="submit" value="Calculate trial means">
             </form>
             <?php
         }
         echo "</table>";
         $sql = "SELECT input_file_log_uid from input_file_log where file_name = '$filename0'";
         $res = mysqli_query($mysqli,$sql) or die("Database Error: input_file lookup  - ". mysqli_error($mysqli) ."<br>".$sql);
         $rdata = mysqli_fetch_assoc($res);
         $input_uid = $rdata['input_file_log_uid'];
         if (empty($input_uid)) {
           $sql = "INSERT INTO input_file_log (file_name,users_name, created_on) VALUES('$filename0', '$username', NOW())";
         } else {
           $sql = "UPDATE input_file_log SET users_name = '$username', created_on = NOW() WHERE input_file_log_uid = '$input_uid'";
         }
         $lin_table = mysqli_query($mysqli, $sql) or die("Database Error: Log record insertion failed - ". mysqli_error($mysqli) ."<br>".$sql);
       }  else {
          echo "<br><font color=red>Error - data not saved</font><br>\n";
       }
    }

  }
  }
}
