<?php
/**
 * Genotype data importer
 *
 * PHP version 5.3
 * Prototype version 1.5.0
 * 
 * @category PHP
 * @package  T3
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @version  GIT: 2
 * @link     http://triticeaetoolbox.org/wheat/curator_data/genotype_data_check.php
 * 
 * 10/17/2011 JLee  Pass username to offline app
 * 04/11/2011 Jlee  Add zip file handling
 */
//
// Written By: John Lee
//*********************************************

require 'config.php';
/*
 * Logged in page initialization
 */
require $config['root_dir'] . 'includes/bootstrap_curator.inc';
//require_once $config['root_dir'] . 'includes/email.inc';

connect();
loginTest();

/* ******************************* */
//$row = loadUser($_SESSION['username']);

////////////////////////////////////////////////////////////////////////////////
ob_start();

authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();

new gLineNames_Check($_GET['function']);

/** Using a PHP class to implement the "Download Gateway" feature
 * 
 * @category PHP
 * @package  T3
 * @author   Clay Birkett <claybirkett@gmail.com>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/curator_data/genotype_data_check.php
 **/
class gLineNames_Check
{
    private $delimiter = "\t";

    /**
     * Using the class's constructor to decide which action to perform
     */
    public function __construct($function = null)
	{	
		switch($function)
		{
             default:
				$this->typeExperimentIn(); /* intial case*/
				break;
		}	
	}


    private function typeExperimentIn() {
		global $config;
		include($config['root_dir'] . 'theme/admin_header.php');

		echo "<h2> Genotype Data Processing</h2>"; 
		$this->type_Experiment_Rec();

		$footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
	}
	
	
	private function type_Experiment_Rec() 	{

		global $root;
        $row = loadUser($_SESSION['username']);
        ini_set('memory_limit','1024M');
		$username=$row['name'];

        $username = str_replace(" ", "", $username);
		$tmp_dir="./uploads/".$username."_".rand();
		$url = $root;
        //	$raw_path= "rawdata/".$_FILES['file']['name'][1];
        //	copy($_FILES['file']['tmp_name'][1], $raw_path);
        umask(0);
           
        if(!file_exists($tmp_dir) || !is_dir($tmp_dir)) {
            mkdir($tmp_dir, 0777);
        }

        $target_path=$tmp_dir."/";

        if ($_FILES['file']['name'][0] == "") {
            error(1, "No Line Translation File Uploaded");
            exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
        }  
  
        if ($_FILES['file']['name'][1] == "") {
            error(1, "No Genotype Data Uploaded");
            exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
        }  
        
	// Test whether file upload succeeded.
	if ($_FILES['file']['error'][0] != 0 or $_FILES['file']['error'][1] != 0) {
	  error(1, "File upload failed.  If it fails again please contact the programmers.");
	  error_log("Upload error: A file, possibly" . $_FILES['file']['name'][1] . ", failed to upload.");
          error_log($_FILES['file']['error'][0] . " " . $_FILES['file']['error'][1]);
	  // $_FILES['file']['error'][*] == 1 if php.ini upload_max_filesize was exceeded.
	  exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
	}

        // Check filetype
        $uploadFile1 = $_FILES['file']['name'][0];
        $uploadFile2 = $_FILES['file']['name'][1];
 				
        $uftype=$_FILES['file']['type'][0];
        if (strpos($uploadFile1, ".txt") === FALSE) {
            error(1, "Expecting an tab-delimited text file. <br> The line translation file is type - ".$uftype);
            exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
        }
        
        $uftype=$_FILES['file']['type'][1];
        if ((strpos($uploadFile2, ".txt") === FALSE) && (strpos($uploadFile2, ".zip") === FALSE)) {
            error(1, "Expecting an tab-delimited text file or a zipped tab-delimited text file. <br> The genotype data file is type - ".$uftype);
            exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
        }

        $userEmail = $_POST['emailAddr'];
        $translateFile = $target_path.$uploadFile1;
        $genoDataFile = $target_path.$uploadFile2;
        $processOut = $target_path. "genoProc.out";
        
        if(move_uploaded_file($_FILES['file']['tmp_name'][0], $translateFile) == FALSE) {
            error(1, "Unable to move the translation file to the upload directory.");
            exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");           
        }
        
        if (move_uploaded_file($_FILES['file']['tmp_name'][1], $genoDataFile) == FALSE) {
            error(1, "Unable to move the genotype data file to the upload directory.");
            exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");           
       	}
        if ($_POST['submitButton'] == 'Verify Files') {
            $cmd = "php genoDataVerify2D.php \"$translateFile\" \"$genoDataFile\" $userEmail $url $username" ." > " . $processOut ;
            exec($cmd);
            if (file_exists($processOut)) {
              $h = fopen($processOut, "r");
              while ($line= fgets($h)) {
                echo "$line<br>\n";
              }
              fclose($h);
            }
       	} elseif ($_POST['data_format'] == '1D') { 
       	    $cmd = "php genoDataOffline.php \"$translateFile\" $genoDataFile $userEmail $url $username" ." > " . $processOut . " &";
            exec($cmd);
            echo "<h3>The files have been uploaded and submitted to the off-line processor.<br>";
            echo "A report will be emailed to you once the data import to the database has been completed. </h3>";
        } else {
            $cmd = "php genoDataOffline2D.php \"$translateFile\" \"$genoDataFile\" $userEmail $url $username" ." > " . $processOut . " &";
            exec($cmd);
            echo "<h3>The files have been uploaded and submitted to the off-line processor.<br>";
            echo "A report will be emailed to you once the data import to the database has been completed. </h3>";
        }
        echo "<br><br>";
        exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
   }
 
} /* end of class */        
   
?>    
