<?php

require 'config.php';
include($config['root_dir'] . 'includes/bootstrap_curator.inc');
set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/PHPExcel/Classes');
include '../lib/PHPExcel/Classes/PHPExcel/IOFactory.php';

connect();
loginTest();

$row = loadUser($_SESSION['username']);

//needed for mac compatibility
ini_set('auto_detect_line_endings',true);

ob_start();
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();

new Data_Check($_GET['function']);

/**
 * 
 * CSR Field Book
 *
 */

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
                echo "<h2>CSR Field Book Data Validation</h2>";
                $this->type_Experiment_Name();
                $footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
        }

/**
 * check experiment data before loading into database
 */
 private function type_Experiment_Name() {
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
  $fieldbookname = $_POST['fieldbook'];
  $meta_path= "uploads/".$_FILES['file']['name'][0];
  $raw_path= "../raw/phenotype/".$_FILES['file']['name'][1];
  if (file_exists($raw_path)) {
    $unique_str = chr(rand(65,80)).chr(rand(65,80)).chr(rand(64,80));
    $tmp1 = $_FILES['file']['name'][1];
    $unq_file_name = $unique_str . "_" . $_FILES['file']['name'][1];
    //echo "replace $tmp1 $tmp2 $raw_path<br>\n";
    $raw_path = str_replace("$tmp1","$unq_file_name","$raw_path",$count);
  } else {
    $unq_file_name = $_FILES['file']['name'][1];
  }
  if (empty($_FILES['file']['name'][0])) {
    echo "missing Data file\n";
  }
  if(!file_exists($tmp_dir) || !is_dir($tmp_dir)) {
      mkdir($tmp_dir, 0777);
  }
  $target_path=$tmp_dir."/";
  if ($_FILES['file']['name'][0] == ""){
     error(1, "No File Uploaded");
     print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
  } else {
    $uploadfile=$_FILES['file']['name'][0];
    //$rawdatafile = $_FILES['file']['name'][1];

    $uftype=$_FILES['file']['type'][0];
    if (strpos($uploadfile, ".xlsx") === FALSE) {
             error(1, "Expecting an Excel file. <br> The type of the uploaded file is ".$uftype);
             print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
    } else {
      if (move_uploaded_file($_FILES['file']['tmp_name'][0], $target_path.$uploadfile) !== TRUE) {
          echo "error - could not upload file $uploadfile<br>\n";
      } else {
          echo $_FILES['file']['name'][0] . "<br>\n";
      }
               $metafile = $target_path.$uploadfile;
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

               //check data format
               if ($data[1]["A"] != "range") {
                 $tmp = $data[1]["A"];
                 echo "<font color=red>Error in column header - expected \"range\" found $tmp</font><br>\n";
                 $error_flag = 1;
               }
               if (!preg_match("/[A-Za-z]/",$fieldbookname)) {
                 echo "<font color=red>Error - missing Field Book Name</font><br>\n";
                 $error_flag = 1;
               }
               for ($i = 2; $i <= $lines_found; $i++) {
                 $tmp = $data[$i]["E"];
                 $sql = "select line_record_uid from line_records where line_record_name = '$tmp'";
                 $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                 if (mysql_num_rows($res)==0) {
                    echo "Error: could not find Line Name $tmp in line_records table, please load this line name first<br>\n"; 
                    $error_flag = 1;
                 }
               }

           if ($error_flag == 0) {
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
                 $tmpQ = $data[$i]["Q"];

                 //correct missing data to avoid sql error
                 if (!preg_match("/[0-9]/",$tmpI)) {
                   $tmpI = "NULL";
                 }
                 if (!preg_match("/[0-9]/",$tmpJ)) {
                   $tmpJ = "NULL";
                 }
                 if (!preg_match("/[0-9]/",$tmpK)) {
                   $tmpK = "NULL";
                 }
                 $sql = "insert into csr_fieldbook (fieldbook_name, range_id, plot, entry, plot_id, line_name, trial, field_id, note, replication, block, subblock, row_id, column_id, treatment, main_plot_tmt, subplot_tmt, check_id) values ('$fieldbookname',$tmpA,$tmpB,'$tmpC','$tmpD','$tmpE','$tmpF','$tmpG','$tmpH',$tmpI,$tmpJ,$tmpK,'$tmpL','$tmpM','$tmpN','$tmpO','$tmpP','$tmpQ')";
                 $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
               }
               echo "saved to database and file system<br>\n";
           }   
           echo "<br><table>\n";
           for ($i=1; $i<=$lines_found; $i++) {
                 echo "<tr><td>$i";
                 for ($j="A"; $j <= "Q"; $j++) {
                   $tmp = $data[$i]["$j"];
                   echo "<td>$tmp";
                 }
                 echo "\n";
           }
           echo "</table>"; 
    }
  }

}

}
