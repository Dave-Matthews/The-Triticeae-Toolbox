<?php
/**
 * Canopy Spectral Reflectance, System File import
 *
 * PHP version 5.3
 * Prototype version 1.5.0
 *
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/curator_data/input_csr_spec_check.php
 *
 */

require 'config.php';
include $config['root_dir'] . 'includes/bootstrap_curator.inc';
set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/PHPExcel/Classes');
include '../lib/PHPExcel/Classes/PHPExcel/IOFactory.php';

$mysqli = connecti();
loginTest();

$user = loadUser($_SESSION['username']);
$userid = $user['users_uid'];
$username = $user['name'];

//needed for mac compatibility
ini_set('auto_detect_line_endings', true);

ob_start();
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();

new InstrumentCheck($_GET['function']);

/**
 * CSR instrument system description
 */

class InstrumentCheck
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
                $this->typeInstrumentCheck(); /* initial case*/
                break;
        }
    }

/**
 * check experiment data before loading into database
 */
  private function typeInstrumentCheck() {
    global $config;
    $row = loadUser($_SESSION['username']);
    $username=$row['name'];
    $tmp_dir="uploads/tmpdir_".$username."_".rand();
    $raw_path= "../raw/phenotype/".$_FILES['file']['name'][0];
    include($config['root_dir'] . 'theme/admin_header.php');
    echo "<h2>CSR Instrument System Validation</h2>";
    $this->type_Instrument_Name();
    $footer_div = 1;
    include $config['root_dir'].'theme/footer.php';
  }

/**
 * check data before loading into database
 */
 private function type_Instrument_Name() {
   global $mysqli;
   global $userid;
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
  $replace_flag = $_POST['replace'];

  if (empty($_FILES['file']['name'][0])) {
    if (empty($_POST['filename'])) {
      echo "missing System file\n";
    } else {
      $metafile = $_POST['filename'];
      $raw_path = $_POST['filename'];
    }
  } else {
    $filename0 = $_FILES['file']['name'][0];
  }
  
  if(!file_exists($tmp_dir) || !is_dir($tmp_dir)) {
      mkdir($tmp_dir, 0777);
  }
  $target_path=$tmp_dir."/";
  if (($_FILES['file']['name'][0] == "") && ($metafile == "")) {
     error(1, "No File Uploaded");
     print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
  } else {
    if (!empty($_FILES['file']['name'][0])) {
      $uploadfile=$_FILES['file']['name'][0];
      $rawdatafile = $_FILES['file']['name'][1];
      $raw_path= "../raw/phenotype/".$_FILES['file']['name'][0];
      $uftype=$_FILES['file']['type'][0];
      if (move_uploaded_file($_FILES['file']['tmp_name'][0], $raw_path) !== TRUE) {
        echo "error - could not upload file $uploadfile<br>\n";
      } else {
        echo $_FILES['file']['name'][0] . " $FileType<br>\n";
      }
      $metafile = $raw_path;
      echo "using $metafile<br>\n";
    } else {
      echo "using $metafile<br>\n";
    }
    echo "<br>\n";

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
               $i = 1;
               $found = 1;
               while ($found) {
                 $tmp1 = $sheetData[$i]["A"];
                 $tmp2 = $sheetData[$i]["B"];
                 if (preg_match("/[A-Za-z0-9]+/",$tmp1)) {
                   $data[$i] = $tmp1;
                   $value[$i] = $tmp2;
                   $i++;
                 } else {
                   $found = 0;
                 }
               }
               $lines_found = $i - 1;

               $error_flag = 0;
               if ($data[2] != "Spectrometer System Name") {
                 echo "expected \"Spectometer System Name\" found $data[2]<br>\n";
                 $error_flag = 1;
               }
               if ($data[3] != "Instrument [JAZ, CropScan, USB2000+]") {
                 echo "expected \"Instrument[JAZ, CropScan, USB2000+]\" found $data[3]<br>\n";
                 $error_flag = 1;
               }
               if ($data[4] !== "Spectrometer serial number") {
                 echo "expected \"Spectrometer serial number\" found $data[4]<br>";
                 $error_flag = 1;
               }

               //check for missing entries

               if ($value[7] == "") {
               } elseif (preg_match("/[A-Za-z0-9]/", $value[7])) {
               } else {
                   die("<font color=red>Error - Collection lens should be character</font>, found $value[7]<br>");
               }

               if ($value[8] == "") {
               } elseif (preg_match("/\d+/", $value[8])) {
               } else {
                   die("<font color=red>Error - Longpass filter should be integer</font>, found $value[8]<br>");
               }

               if (preg_match("/[A-Za-z]/", $value[9])) {
                   die("<font color=red>Error - slit aperature should be integer</font>, found $value[9]<br>");
               } elseif (preg_match("/\d+/", $value[9])) {
                   $slit_aperature = $value[9];
               } else {
                   $slit_aperature = "NULL";
               }

	       $sql = "select system_uid from csr_system where system_name = \"$value[2]\"";
               $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
               $row = mysqli_fetch_array($res);
               if (mysqli_num_rows($res) == 0) {
                 $new_record = 1;
                 $system_uid = NULL;
               } else {
                 $system_uid = $row[0];
                 if (!$replace_flag) {
                   echo "<font color=red>Warning - record with system name = $value[2] already exists. ";
                   echo "Do you want to overwrite?</font>";
                   ?>
                   <form action="curator_data/input_csr_spect_check.php" method="post" enctype="multipart/form-data">
                   <input id="system_uid" type="hidden" name="system_uid" value="<?php echo $system_uid; ?>">
                   <input id="filename" type="hidden" name="filename" value="<?php echo $raw_path; ?>">
                   <input id="replace" type="hidden" name="replace" value="Yes">
                   <input type="submit" value="Yes">
                   </form>
                   <?php
                   $error_flag = 1;
                 }
                 $new_record = 0;
               }
        
               if ($error_flag == 0) {
                 if ($new_record) {
                   $sql = "insert into csr_system (system_name, instrument, serial_num, serial_num2, grating, collection_lens, longpass_filter, slit_aperture, reference, cable_type, wavelengths, bandwidths, comments) values ('$value[2]','$value[3]','$value[4]','$value[5]','$value[6]','$value[7]','$value[8]',$slit_aperature,'$value[10]','$value[11]','$value[12]','$value[13]','$value[14]')";
                   $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
                   echo "saved to database<br>\n";
                 } else {
                   $sql = "delete from csr_system where system_uid  = $system_uid";
                   $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
                   echo "deleted old entries from database where system_uid = $system_uid<br>\n";
                   $sql = "insert into csr_system (system_name, instrument, serial_num, serial_num2, grating, collection_lens, longpass_filter, slit_aperture, reference, cable_type, wavelengths, bandwidths, comments) values ('$value[2]','$value[3]','$value[4]','$value[5]','$value[6]','$value[7]','$value[8]',$slit_aperature,'$value[10]','$value[11]','$value[12]','$value[13]','$value[14]')";
                   $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
                   echo "saved to database<br>\n";
                 }
                 $sql = "select system_uid from csr_system where system_name = \"$value[2]\"";
                 $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
                 $row = mysqli_fetch_array($res);
                 echo "<br>Check results by viewing <a href=display_csr_spe.php?uid=$row[0]>data stored in database</a><br>";
               }
               echo "<br>Data read from import file<table>\n";
               for ($i=1; $i<=$lines_found; $i++) {
                 echo "<tr><td>$i<td>$data[$i]<td>$value[$i]\n";
               }
               echo "</table>";
    }
  //}

}

}
