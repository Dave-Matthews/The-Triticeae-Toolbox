<?php

require 'config.php';
include($config['root_dir'] . 'includes/bootstrap_curator.inc');
set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/PHPExcel/Classes');
include '../lib/PHPExcel/Classes/PHPExcel/IOFactory.php';

connect();
loginTest();

$row = loadUser($_SESSION['username']);

ob_start();
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();

new Data_Check($_GET['function']);

/**
 * 
 * Phenotype Experiment Results
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
                echo "<h2>CSR Phenotype Data Validation</h2>";
                $this->type_Experiment_Name();
                $footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
        }

public function save_raw_file($header) {
  try {
      $dbh = new PDO('sqlite:/tmp/foo.db');
      echo "saving raw file<br>\n";
  } catch (PDOException $e) {
      print "Error!: " . $e->getMessage() . "<br/>";
  }
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
  $meta_paht= "uploads/".$_FILES['file']['name'][0];
  $raw_path= "../raw/phenotype/".$_FILES['file']['name'][1];
  if (!empty($_FILES['file']['name'][1])) {
         if (move_uploaded_file($_FILES['file']['tmp_name'][1], $raw_path) !== TRUE) {
             echo "<font color=red><b>Oops!</b></font> Your raw data file <b>"
             .$_FILES['file']['name'][1]."</b> was not saved in directory ".$config['root_dir']."raw/ and
             will be lost.  Please <a href='".$config['base_url']."feedback.php'>contact the 
             programmers</a>.<p>";
         } else {
             echo $_FILES['file']['name'][1] . "<br/>";
             //check file for readability
             $i = 0;
             if (($reader = fopen($raw_path, "r")) == false) {
               die("error - can not read file $raw_path<br>\n");
             }
             while ($line = fgets($reader)) {
               $temp = str_getcsv($line,"\t");
               if ($size == 0) {
                 $size = count($temp);
               } else {
                 $size_t = count($temp);
                 if ($size != $size_t) {
                   echo "error $size $size_t<br>\n";
                 }
               }
               $i++;
             }
             $i--;
             echo "$i lines (Wavelengths), $size columns in file<br>\n";
          
             //save to SQLite
             $this->save_raw_file($reader);   
             fclose($reader);
             echo "<br>\n";
 
             $objPHPExcel = new PHPExcel();
             $objPHPExcel->setActiveSheetIndex(0);
             $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'test');
             #$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
             $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
             $objWriter->save('/tmp/tht/testfile.xls');
         }
         umask(0);
  } else {
    echo "missing Meta file\n";
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
    $rawdatafile = $_FILES['file']['name'][1];

    $uftype=$_FILES['file']['type'][0];
    if (strpos($uploadfile, ".xlsx") === FALSE) {
             error(1, "Expecting an Excel file. <br> The type of the uploaded file is ".$uftype);
             print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
    } else {
      if (move_uploaded_file($_FILES['file']['tmp_name'][0], $target_path.$uploadfile) !== TRUE) {
          echo "error - could not upload file $uploadfile<br>\n";
      } else {
          echo "uploaded file - " . $_FILES['file']['name'][0] . "<br>\n";
      }
               $metafile = $target_path.$uploadfile;
               /* Read the Means file */
               $objPHPExcel = PHPExcel_IOFactory::load($metafile);
               $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
               $i = 2;
               $found = 1;
               echo "<table border=1><tr><td>Paramater<td>Value";
               while ($found) {
                 $tmp1 = $sheetData[$i]["A"];
                 $tmp2 = $sheetData[$i]["B"];
                 if (preg_match("/[A-Za-z0-9]+/",$tmp1)) {
                   $data[$i] = $tmp1;
                   $value[$i] = $tmp2;
                   echo "<tr><td>$tmp1<td>$tmp2\n";
                   $i++;
                 } else {
                   $found = 0;
                 }
               }
               $i--;
               echo "</table>\n";
               echo "$i lines read from spreadsheet, ";
               if ($data[2] != "Trial Name") {
                 echo "expected \"Trial Name\" found $data[2]<br>\n";
               }
               if ($data[3] != "Upwelling / Downwelling") {
                 echo "expected \"Upwelling \/ Downwelling\" found $data[3]<br>\n";
               }
               if ($data[4] !== "Measurement date") {
                 echo "expected \"Measurement date\" found $data[4]<br>";
               }
               if ($data[5] != "Growth Stage") {
                 echo "expected \"Growth Stage\" found $data[5]<br>";
               }
               echo "saved to database<br>\n";
               $sql = "insert into csr_trial (trial, radiation_direction, measure_date, growth_stage, start_time, end_time, integration_time, weather, instrument, instrument_detail, spectrometer_serial, grating, collection_lens, longpass_filter, slit_aperature, cable_type, num_measurements, height_from_canopy, reference, incident_adj, comments) values ('$value[2]','$value[3]',str_to_date('$value[4]','%m/%d/%Y'),'$value[5]','$value[6]','$value[7]','$value[8]','$value[9]','$value[10]','$value[11]','$value[12]','$value[13]','$value[14]','$value[15]','$value[16]','$value[17]',$value[18],$value[19],'$value[20]','$value[21]','$value[22]')";
               $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
    }
  }

}

}
