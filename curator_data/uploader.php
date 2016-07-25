<?php
require 'config.php';
//***********************************************************
//
// 6/27/11 JLee		Modify to running in curator_data folder
//					& misc fixes.
 //**********************************************************

/*
 * Logged in page initialization
 */
include $config['root_dir'] . 'includes/bootstrap_curator.inc';

$mysqli = connecti();
loginTest();

$row = loadUser($_SESSION['username']);


/* ****************************** */

////////////////////////////////////////////////////////////////////////////////
ob_start();
include $config['root_dir'] . 'theme/admin_header.php';
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();
////////////////////////////////////////////////////////////////////////////////
?>
<div id="primaryContentContainer">
	<div id="primaryContent">

<?php

	ini_set("memory_limit","24M");
	$uploadtype = $_GET['type'];

	$username=$row['name'];
	$tmp_dir="uploads/tmpdir_".$username."_".rand();
	umask(0);
	if(!file_exists($tmp_dir) || !is_dir($tmp_dir)) {
		mkdir($tmp_dir, 0777);
	}
	$target_path=$tmp_dir."/";
	if ($_FILES['file']['name'] == ""){
		error(1, "No File Uploaded");
	}
	else {
		$uploadfile=$_FILES['file']['name'];
		$uftype=$_FILES['file']['type'];
		if (strpos($uploadfile, ".xls") === FALSE) {
			error(1, "Expecting an Excel file. <br> The type of the uploaded file is ".$uftype);
		}
		else {
			if(move_uploaded_file($_FILES['file']['tmp_name'], $target_path.$uploadfile)) {

				/* logging the file processing information in $_SESSION['fileProcessInfo'] */
				writeUploadLog($uploadfile);  // old loging method
				if (! isset($_SESSION['fileProcessInfo']) || $uploadtype=="user_data") {
					$_SESSION['fileProcessInfo'] = array(); // start/reset the $_SESSION['fileProcessInfo']
				}
				if ($uploadtype=="user_data") {
					$_SESSION['fileProcessInfo']['file_name'] = $uploadfile;
				}
				elseif ($uploadtype=="user_def") {
					$_SESSION['fileProcessInfo']['def_file_name'] = $uploadfile;
				}
				$_SESSION['fileProcessInfo']['dir_destination'] = $target_path;
				$_SESSION['fileProcessInfo']['users_name']=$_SESSION['username'];
				if (! isset($_SESSION['fileProcessInfo']['process_program'])) $_SESSION['fileProcessInfo']['process_program']="";
				$_SESSION['fileProcessInfo']['process_program'].=$_SERVER['PHP_SELF'].",";
				if (isset($_GET['dataset_name']) && strlen($_GET['dataset_name'])>1) $_SESSION['fileProcessInfo']['dataset_name']=$_GET['dataset_name'];
				/* other logging information filled in the process page */
	   			$infilename=$target_path.$uploadfile;
    			//echo $infilename . "<br>";

    			echo "<h2>The file ".basename($uploadfile)." has been uploaded to the server. </h2>\n";
				echo "<h3> The file contains the following records: </h3><br>";
    			display_uploaded_file($target_path.$uploadfile);
				$action_url="curator_data/store_$uploadtype.php";
    			print "<br>";
    			if ($uploadtype=="user_data") {
    				$action_url="curator_data/parser_add.php";
    				$_SESSION['user_data_file']=$infilename;
    			}
    			elseif ($uploadtype=="user_def") {
    				$action_url="curator_data/store_parser.php";
    				$_SESSION['user_def_file']=$infilename;
    			}
    			print "<form name=\"acceptUpload\" action=$action_url method=\"post\">";
    			print "<input name=\"infilename\" type=\"hidden\" value=\"$infilename\">";
				print "<input name=\"tmpdir\" type=\"hidden\" value=\"$target_path\">";
				if(count($_POST) > 0) {	//there was more than just a file sent here, so pass it along.
					foreach($_POST as $k=>$v) echo "\n\t<input type=\"hidden\" name=\"$k\" value=\"$v\" />";
				}
    			print "<input type=\"Submit\" value=\"Accept\">";
    			print "<input type=\"Button\" value=\"Cancel\" onClick=\"history.go(-1); return;\">";
    			print "</form>";
   			} else {
    			error(1,"There was an error uploading the file to the server, please contact website administrator to resolve the problem.");
			}
		}
	}


function writeUploadLog($fname) {

	$log_name = "uploads/upload.log";
	$date = date("m-d-Y H:i:s");

	$log = @fopen($log_name, "a");
	if($log !== FALSE && is_writable($log_name)) {

		$newline = $fname . ",\t" . $date . ",\t" . $_SESSION['username'] . "\n";

		if (fwrite($log, $newline) === FALSE) {
       			error(1, "Cannot write to upload log file");
    		}

		fclose($log);
		return TRUE;
	}
	else {
		error(1, "Can not open the upload log file");
		return FALSE;
	}
}

?>
	</div>
</div>
</div>

<?php include $config['root_dir'] . '/theme/footer.php';?>
