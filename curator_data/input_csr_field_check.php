<?php
/**
 * Canopy Spectral Reflectance, Fieldbook import
 *
 * PHP version 5.3
 * Prototype version 1.5.0
 *
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/curator_data/input_csr_field_check.php
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

/** Using a PHP class to implement Field Book import
 *
 * @category PHP
 * @package  T3
 * @author   Clay Birkett <claybirkett@gmail.com>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/curator_data/input_csr_field_check.php
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
     * @return null
     */
    private function typeExperimentCheck()
    {
        global $config;
        include $config['root_dir'] . 'theme/admin_header.php';
        echo "<h2>CSR Field Book Data Validation</h2>";
        $this->type_Experiment_Name();
        $footer_div = 1;
        include $config['root_dir'].'theme/footer.php';
    }

    /**
     * check experiment data before loading into database
     *
     * @return null
     */
    private function type_Experiment_Name()
    {
        global $mysqli;
        ?>
        <script type="text/javascript">
        function update_database(filepath, filename, username, rawdatafile) {
        var url='<?php echo $_SERVER[PHP_SELF];?>?function=typeDatabase&expdata=' + filepath + '&file_name=' + filename + '&user_name=' + username + '&raw_data_file=' + rawdatafile;
        // Opens the url in the same window
        window.open(url, "_self");
        }
        function goBack() {
            window.history.back()
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
        //$experiment_uid = $_POST['exper_uid'];
        //if (preg_match("/[0-9]/",$experiment_uid)) {
        //} else {
        //  die("Error: Must select a trial name<br>\n");
        //}
        $replace_flag = $_POST['replace'];
        $meta_path= "raw/phenotype/".$_FILES['file']['name'][0];
        $raw_path= "../raw/phenotype/".$_FILES['file']['name'][0];
  //$sql = "select trial_code from experiments where experiment_uid = $experiment_uid";
  //$res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
  //if ($row = mysqli_fetch_assoc($res)) {
  //  $trial_code = $row['trial_code'];
  //} else {
  //  echo "$sql<br>\n";
  //  die("Error: could not find trial code in database $experiment_uid<br>\n");
  //}
    if (file_exists($raw_path)) {
        $unique_str = chr(rand(65, 80)).chr(rand(65, 80)).chr(rand(64, 80));
        $tmp1 = $_FILES['file']['name'][0];
        $unq_file_name = $unique_str . "_" . $_FILES['file']['name'][0];
        //echo "replace $tmp1 $tmp2 $raw_path<br>\n";
        $meta_path = str_replace("$tmp1", "$unq_file_name", "$meta_path",$count);
        $raw_path = str_replace("$tmp1", "$unq_file_name", "$raw_path",$count);
    } else {
        $unq_file_name = $_FILES['file']['name'][0];
    }
  if (empty($_FILES['file']['name'][0])) {
    if (empty($_POST['filename'])) {
      echo "missing Data file\n";
    } else {
      $metafile = $_POST['filename'];
      $meta_path = $_POST['filename_meta'];
      $raw_path = $_POST['filename'];
    }
  }
  if(!file_exists($tmp_dir) || !is_dir($tmp_dir)) {
      mkdir($tmp_dir, 0777);
  }
  $target_path=$tmp_dir."/";
  if (($_FILES['file']['name'][0] == "") && ($metafile == "")){
     error(1, "No File Uploaded");
     print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
  } else {
    if (!empty($_FILES['file']['name'][0])) {
      $uploadfile=$_FILES['file']['name'][0];
      $uftype=$_FILES['file']['type'][0];
      if (move_uploaded_file($_FILES['file']['tmp_name'][0], $raw_path) !== TRUE) {
          echo "error - could not upload file $uploadfile<br> Can not move " . $_FILES['file']['tmp_name'][0] . " to $raw_path <br>Possibly folder permission problem.\n";
      } else {
          echo $_FILES['file']['name'][0] . " $FileType<br>\n";
          $metafile = $raw_path;
      }
    } else {
      echo "using $metafile<br>\n";
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

               /* Read the Means file */
               $objPHPExcel = PHPExcel_IOFactory::load($metafile);
               $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

               //read in spreadsheet until blank entry found in column "A"
               $i = 1;
               $found = 1;
               $error_flag = 0;
               while ($found) {
                 for ($j = "A"; $j <= "Q"; $j++) {
                   $tmp = $sheetData[$i]["$j"];
                   $data[$i]["$j"] = $tmp;
                 }
                 if (preg_match("/[A-Za-z0-9]/",$data[$i]["A"])) {
                   $i++;
                 } else {
                   $found = 0;
                 }
               }
               $lines_found = $i - 1;

               //check for old style format
               if ($data[1]["A"] == "Trial:") {
                 echo "<font color=red>Error in column header - You are using an old verion of the template. Please
                       download the current version.<br>\n";
                 $error_flag = 1;
                 $msg = "<input type=\"button\" value=\"Back\" onclick=\"goBack()\">";
                 die("<br>$msg<br>");
               //check data format
               } elseif ($data[1]["B"] != "trial") {
                 $tmp = $data[1]["B"];
                 echo "<font color=red>Error in column header - expected \"trial\" in column B found $tmp</font><br>\n";
                 $error_flag = 1;
               }
               if ($data[1]["A"] != "plot") {
                 $tmp = $data[1]["A"];
                 echo "<font color=red>Error in column header - expected \"plot\" in column A found $tmp</font><br>\n";
                 $error_flag = 1;
               }

               //$sql = "select trial_code from experiments where experiment_uid = $experiment_uid";
               //$res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
               //$row = mysqli_fetch_array($res);
               //if ($row[0] != $tmp) {
               //  echo "<font color=red>Error: Trial Name in the Field Book File \"$tmp\" does not match the Trial Name selected in the drop-down list<br></font>\n";
               //  $error_flag = 1;
               //  die();
               //}

               //check trial and line names
               $experiment_list = array();
               $trial_code_list = "";
               for ($i = 2; $i <= $lines_found; $i++) {
                 $tmp = $data[$i]["B"];
                 $sql = "select experiment_uid from experiments where trial_code = \"$tmp\"";
                 $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
                 if ($row = mysqli_fetch_assoc($res)) {
                    $exp_uid = $row['experiment_uid'];
                    if (in_array($exp_uid, $experiment_list)) {
                    } else {
                      if ($trial_code_list == "") {
                        $trial_code_list = $tmp;
                      } else {
                        $trial_code_list = $trial_code_list . ", $tmp";
                      }
                      $experiment_list[] = $row['experiment_uid'];
                    }
                 } else {
                   echo "<font color=red>Error: The entry for trial \"$tmp\" is not valid<br></font>\n";
                   $error_flag = 1;
                   die();
                 } 
                 $tmp = $data[$i]["C"];
                 $sql = "select line_record_uid from line_records where line_record_name = '$tmp'";
                 $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
                 if ($row = mysqli_fetch_assoc($res)) {
                    $data[$i]["Q"] = $row['line_record_uid'];
                 } else {
                    $sql = "select line_record_uid from line_synonyms where line_synonym_name= '$tmp'";
                    $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
                    if ($row = mysqli_fetch_assoc($res)) {
                      $data[$i]["Q"] = $row['line_record_uid'];
                    } else {
                      if (isset($unique_line[$tmp])) {
                      } else {
                        $unique_line[$tmp] = 1;
                        echo "Error: could not find Line Name $tmp in line_records table, please load this line name first<br>\n"; 
                      }
                      $error_flag = 1;
                    }
                 }
               }

               //check unique plot#
               for ($i = 2; $i <= $lines_found; $i++) {
                    $tmp = $data[$i]["A"];
                    if (isset($unique_plot[$tmp])) {
                      echo "Error: duplicate plot number $tmp<br>\n";
                      $error_flag = 1;
                    } else {
                      $unique_plot{$tmp} = 1;
                    }
               }

               //check unique row column
               for ($i = 2; $i <= $lines_found; $i++) {
                   $tmp = $data[$i]["D"] . ":" . $data[$i]["E"];
                   if (preg_match("/[0-9]/", $tmp)) {
                       if (isset($unique_rc[$tmp])) {
                           echo "Error: duplicate row column $tmp on line $i<br>\n";
                           $error_flag = 1;
                       } else {
                         $unique_rc[$tmp] = 1;
                       }
                   }
               }

               $experiment_str = implode(',', $experiment_list);
               $sql = "select fieldbook_info_uid from fieldbook_info where experiment_uid IN ($experiment_str)";
               $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
               //echo "found mysql_num_rows($rew)<br>\n";
               if (mysqli_num_rows($res)==0) {
                 $new_record = 1;
                 //echo "new record<br>\n";
               } else {
                 if (!$replace_flag && ($error_flag == 0)) {
                   if (count($experiment_list) > 1) {
                     echo "<font color=red>Warning - fieldbook entries with Trial Names = $trial_code_list already exist, do you want to overwrite?</font>";
                   } else {
                     echo "<font color=red>Warning - fieldbook entries  with Trial Name = $trial_code_list already exist, do you want to overwrite?</font>";
                   }
                   ?>
                   <form action="curator_data/input_csr_field_check.php" method="post" enctype="multipart/form-data">
                   <input id="fieldbook" type="hidden" name="exper_uid" value="<?php echo $experiment_uid; ?>">
                   <input id="replace" type="hidden" name="replace" value="Yes">
                   <input id="filename" type="hidden" name="filename" value="<?php echo $raw_path; ?>">
                   <input id="filename_meta" type="hidden" name="filename_meta" value="<?php echo $meta_path; ?>">
                   <input type="submit" value="Yes">
                   </form>
                   <?php
                   $error_flag = 1;
                 }
                 $new_record = 0;
               }

           if ($error_flag == 0) {

               if ($new_record) {
                   foreach ($experiment_list as $experiment_uid) {
                     $sql = "insert into fieldbook_info (experiment_uid, fieldbook_file_name, updated_on, created_on) values ($experiment_uid, '$meta_path', NOW(), NOW())";
                     $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
                     echo "saved to file system $experiment_uid<br>\n";
                   }
               } else {
                   foreach ($experiment_list as $experiment_uid) {
                     $sql = "update fieldbook_info set fieldbook_file_name = '$meta_path', updated_on = NOW() where experiment_uid = $experiment_uid";
                     $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
                     $sql = "delete from fieldbook where experiment_uid = $experiment_uid";
                     $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
                     echo "deleted old entries from fieldbook database where experiment_uid = $experiment_uid<br>\n";
                   }
               }

               for ($i=2; $i<=$lines_found; $i++) {
                 $tmpA = $data[$i]["A"];
                 $tmpB = $data[$i]["B"];
                 $tmpC = $data[$i]["C"];
                 $tmpD = $data[$i]["D"];
                 $tmpE = $data[$i]["E"];
                 $tmpF = $data[$i]["F"];
                 $tmpG = $data[$i]["G"];
                 $tmpH = $data[$i]["H"];
                 $tmpI = $data[$i]["I"];
                 $tmpJ = $data[$i]["J"];
                 $tmpK = $data[$i]["K"];
                 $tmpL = $data[$i]["L"];
                 $tmpM = $data[$i]["M"];
                 $tmpN = $data[$i]["N"];
                 $tmpO = $data[$i]["O"];
                 $tmpP = $data[$i]["P"];
                 $tmpQ = $data[$i]["Q"];   //*line_uid from database*//

                 $sql = "select experiment_uid from experiments where trial_code = \"$tmpB\"";
                 $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
                 if ($row = mysqli_fetch_assoc($res)) {
                    $experiment_uid = $row['experiment_uid'];
                 } else {
                    echo "Error - could not find trial_code $tmpB<br>\n";
                    die();
                 }

                 //correct missing data to avoid sql error
                 if ($tmpD == "") {
                   $tmpD = "NULL";
                 } elseif (preg_match("/\d+/",$tmpD,$match)) {
                 } else {
                   die("Error - row field should be integer, found $tmpD in line $i<br>");
                 }
                 if ($tmpE == "") {
                   $tmpE = "NULL";
                 } elseif (preg_match("/[0-9]/",$tmpE,$match)) {
                 } else {
                   die("Error - column field should be integer, found $tmpE in line $i<br>");
                 }
                 if ($tmpF == "") {
                   $tmpF = "NULL";
                 } elseif (preg_match("/[0-9]/",$tmpF,$match)) {
                 } else {
                   die("Error - entry field should be integer, found $tmpF in line $i<br>");
                 }
                 if ($tmpG == "") {
                   $tmpG = "NULL";
                 } elseif (preg_match("/[0-9]/",$tmpG,$match)) {
                 } else {
                   die("Error - replication field should be integer, found $tmpG in line $i<br>");
                 }
                 if ($tmpH == "") {
                   $tmpH = "NULL";
                 } elseif (preg_match("/[0-9]/",$tmpH,$match)) {
                 } else {
                   die("Error - replication field should be integer, found $tmpH in line $i<br>");
                 }
                 if ($tmpI == "") {
                   $tmpI = "NULL";
                 } elseif (preg_match("/[0-9]/",$tmpI,$match)) {
                 } else {
                   die("Error - replication field should be integer, found $tmpI in line $i<br>");
                 }


                 if (preg_match("/([01])/",$tmpM,$match)) {
                   $tmpM = $match[1];
                 } elseif ($tmpL == "") {
                   $tmpM = "NULL";
                 } else {
                   die("Error - check field should be 0 or 1, found $tmpM in line $i<br>");
                 }

                 $sql = "insert into fieldbook (experiment_uid, plot, line_uid, row_id, column_id, entry, replication, block, subblock, treatment, block_tmt, subblock_tmt, check_id, field_id, note ) values ($experiment_uid,$tmpA,$tmpQ,$tmpD,$tmpE,$tmpF,$tmpG,$tmpH,$tmpI,'$tmpJ','$tmpK','$tmpL',$tmpM,'$tmpN','$tmpO')";
                 $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
               }
               echo "saved to database<br>\n";
               echo "<br>Check results by viewing <a href=display_fieldbook.php?uid=$experiment_uid>data stored in database</a><br>";
           }   
           echo "<br>Data read from import file<table>\n";
           for ($i=1; $i<=$lines_found; $i++) {
                 echo "<tr><td>$i";
                 for ($j="A"; $j <= "P"; $j++) {
                   $tmp = $data[$i]["$j"];
                   echo "<td>$tmp";
                 }
                 echo "\n";
           }
           echo "</table>"; 
    }
  //}

}

}
