<?php

/**
 * 9apr2013 dem: Genetics characters of lines, e.g. gene or QTL alleles
 * from: ./input_line_names_check.php
 */

require 'config.php';
/*
 * Logged in page initialization
 */
require $config['root_dir'] . 'includes/bootstrap_curator.inc';
require_once "../lib/Excel/reader.php"; // Microsoft Excel library

$mysqli = connecti();
loginTest();
$cnt = 0;  // Count of errors

function die_nice($message = "")
{
    //Actually don't die at all yet, just show the error message.
    global $cnt;
    if ($cnt == 0) {
        echo "<h3>Errors</h3>";
    }
    $cnt++;
    echo "<b>$cnt:</b> $message<br>";
    return false;
}

/* Show more informative messages when we get invalid data. */
function errmsg($sql, $err)
{
  global $mysqli;
  if (preg_match('/^Data truncated/', $err)) {
    // Undefined value for an enum type
    $pieces = preg_split("/'/", $err);
    $column = $pieces[1];
    $msg = "Unallowed value for field <b>$column</b>. ";
    // Only works for table line_records.  Could pass table name as parameter.
    $r = mysqli_query($mysqli, "describe line_records $column");
    $columninfo = mysqli_fetch_row($r);
    $msg .= "Allowed values are: ".$columninfo[1];
    $msg .= "<br>Command: ".$sql."<br>";
    die_nice($msg);
  }
  elseif (preg_match('/^Duplicate entry/', $err)) {
  die_nice($err."<br>".$sql);
  }
  else die_nice("MySQL error: ".$err."<br>The command was:<br>".$sql."<br>");
}

/* ******************************* */
$row = loadUser($_SESSION['username']);

ob_start();
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();

new LineNames_Check($_GET['function']);

class LineNames_Check
{
    private $delimiter = "\t";
    // Using the class's constructor to decide which action to perform
    public function __construct($function = null)
    {
      switch($function)
	{
	case 'typeDatabase':
	  $this->type_Database(); /* update database */
	  break;
	default:
	  $this->typeLineNameCheck(); /* initial case*/
	  break;
	}
    }

    private function typeLineNameCheck() {
      global $config;
      include($config['root_dir'] . 'theme/admin_header.php');
      echo "<h2>Genetic Characters: Validation</h2>"; 
      $this->type_Line_Name();
      $footer_div = 1;
      include($config['root_dir'].'theme/footer.php');
    }
	
    // Initial check of the data, no write to the database.
    private function type_Line_Name() {
      global $cnt;
      global $mysqli;
?>
      <script type="text/javascript">
	 function update_database(filepath, filename, username) {
	var url='<?php echo $_SERVER[PHP_SELF];?>?function=typeDatabase&linedata=' + filepath + '&file_name=' + filename + '&user_name=' + username;
	// Opens the url in the same window
	window.open(url, "_self");
      }
      </script>

      <!-- 	  <style type="text/css"> -->
      <!-- 	  th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6} -->
      <!-- table {background: none; border-collapse: collapse} -->
      <!-- td {border: 0px solid #eee !important;} -->
      <!-- h3 {border-left: 4px solid #5B53A6; padding-left: .5em;} -->
      <!-- </style> -->

      <!-- 	  <style type="text/css"> -->
      <!-- 	  table.marker -->
      <!-- 	  {background: none; border-collapse: collapse} -->
      <!-- th.marker -->
      <!-- 	{ background: #5b53a6; color: #fff; padding: 5px 0; border: 0; } -->
      <!-- td.marker -->
      <!-- 	{ padding: 5px 0; border: 0 !important; } -->
      <!-- </style> -->
		
<?php
      $row = loadUser($_SESSION['username']);
      $username=$row['name'];
	
      if ($_FILES['file']['name'] == ""){
	error(1, "No File Uploaded");
	print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
      }
      else {
        $tmp_dir="uploads/".str_replace(' ', '_', $username)."_".date('yMd_G:i');
	umask(0);
	if(!file_exists($tmp_dir) || !is_dir($tmp_dir)) {
	  mkdir($tmp_dir, 0777);
	}
	$target_path=$tmp_dir."/";
	$uploadfile=$_FILES['file']['name'];
	$uftype=$_FILES['file']['type'];
	if (preg_match('/\.xls$/', $uploadfile) == 0) {
	  error(1, "Only xls format is accepted. <br>");
	  print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
	}
	else {
	  if (move_uploaded_file($_FILES['file']['tmp_name'], $target_path.$uploadfile)) {
	    /* start reading the excel */
	    $datafile = $target_path.$uploadfile;
	    $reader = new Spreadsheet_Excel_Reader();
	    $reader->setOutputEncoding('CP1251');
	    $reader->read($datafile);
	    $linedata = $reader->sheets[0];
	    $cols = $reader->sheets[0]['numCols'];
	    $rows = $reader->sheets[0]['numRows'];

	    /* The following code allows the curator to put the columns in any order.
	       It also allows him/her to supply useless columns */
	    // These are the required columns. -1 means that the column has not been found.
	    $columnOffsets = array('line_name' => -1);
	    // Available line properties:
	    $res = mysqli_query($mysqli, "select name from properties") or die(mysqli_error($mysqli));
	    while ($r = mysqli_fetch_row($res))
	      $properties[] = $r[0];

	    // First, read in the header line.
	    $firstline = 0;
	    $header = array();
	    for ($irow = 2; $irow <= $rows; $irow++) {
	      $teststr= addcslashes(trim($linedata['cells'][$irow][1]),"\0..\37!@\177..\377");
	      if (empty($teststr))
		break; 
	      elseif (strtolower($teststr) == "*line name") {
		$firstline = $irow;
		// read out header line
		for ($icol = 1; $icol <= $cols; $icol++) {
		  $value = addcslashes(trim($linedata['cells'][$irow][$icol]),"\0..\37!@\177..\377");
		  $header[] = $value;
		}
		break 1;
	      }
	      else {
		echo "The header row must begin with '*Line Name'.";
		exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
	      }
	    }
	    // Parse the header cells.
	    foreach($header as $columnOffset => $columnName) { // Loop through the columns in the header row.
	      // Determine the column offset of "*Line Name"...
	      if (strtolower(trim($columnName)) == '*line name')
		$columnOffsets['line_name'] = $columnOffset+1;
	      else {
		// Find Properties, and determine the column offset...
		$pr = trim($columnName);
		if (in_array($pr, $properties)) {
		  // Get this property's allowed values.
		  $propuid = mysql_grab("select properties_uid from properties where name = '$pr'");
		  $sql = "select value from property_values where property_uid = $propuid";
		  $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
		  while ($r = mysqli_fetch_row($res)) 
		    $allowedvals[$pr][] = $r[0];
		  $columnOffsets[$columnName] = $columnOffset+1;
		  $ourprops[] = $pr;
		}
		else 
		  if (!empty($pr))
		    die_nice("Property '$pr' not found.");
	      }
	    } // end foreach($header as $columnOffset => $columnName)

	    // Read in the property values.  Ignore the next row after the header.
	    for ($irow = $firstline+2; $irow <=$rows; $irow++)  {
	      // Ignore rows with first cell empty.
	      if (empty($linedata['cells'][$irow][1])) break;
	      // Extract and validate data.
	      $line = strtoupper(trim($linedata['cells'][$irow][$columnOffsets['line_name']]));
	      if (empty($line)) die_nice("Row $irow: Line name is required.");
	      foreach ($ourprops as $pr) {
		$propval[$pr] = addcslashes(trim($linedata['cells'][$irow][$columnOffsets[$pr]]),"\0..\37!@\177..\377");
		if (!empty($propval[$pr])) {
		  // Test for allowed value.
		  if (!in_array($propval[$pr], $allowedvals[$pr])) {
		    $alllist = implode(",", $allowedvals[$pr]);
		    die_nice("<b>$propval[$pr]: </b>Allowed values of property $pr are only <b>$alllist</b>.");
		  }
		}
	      }

	      // Check if line is in database, as either a line name or synonym.
	      $line_uid = get_lineuid($line);
	      // $line_uid is an array.
	      if ($line_uid === FALSE) {
		die_nice("Row $irow: Line '$line' not found.");
	      }
	    } /* end of for ($irow) */
	    // If any errors, show them and stop.
	    if ($cnt > 0) {
	      echo " <p>Please fix these errors and try again.<br/><br/>";
	      exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
	    }
	    else {
	      // All seems well.  Show and ask for confirmation.
	      echo "<h3>The file is read as follows.</h3>\n";
	      ?>
<table style="table-layout:fixed; width: 650px;">
   <tr>
    <th style="width: 80px;">Line Name</th>
     <?php 
     foreach ($ourprops as $pr)
     echo "<th style='width: 40px;'>".$pr."</th>";
	      ?>
</table>
  
<div id="test" style="padding: 0; height: 300px; width: 665px; overflow: scroll;border: 1px solid #5b53a6;">
  <table style="table-layout:fixed;  width: 665px; word-wrap: break-word;">
      <?php 
      for ($irow = $firstline+2; $irow <=$rows; $irow++)  {
	$line = strtoupper(trim($linedata['cells'][$irow][$columnOffsets['line_name']]));
	if (!empty($line)) {
	  echo "<tr><td style='width: 100px;'>".$line;
	  foreach ($ourprops as $pr) {
	    $propval[$pr] = addcslashes(trim($linedata['cells'][$irow][$columnOffsets[$pr]]),"\0..\37!@\177..\377");
	    echo "<td style='width: 40px;'>".$propval[$pr]."</td>";
	  }
	}
      }  
	      ?>
	   </table>
</div>

<p>
  <input type="Button" value="Accept" 
	 onclick="javascript: update_database('<?php echo $datafile?>','<?php echo $uploadfile ?>','<?php echo $username?>' )"/>
  <input type="Button" value="Cancel" 
	 onclick="history.go(-1); return;"/>

    <?php	
    } // end of $cnt == 0 (no errors)
	  } // end of if(move_uploaded_file...)
	  else 
	    error(1,"There was an error uploading the file, please try again.");
	}
      }
    } /* end of function type_Line_Name() */
	

    /* Validation completed, now load the database. */
    private function type_Database() {
      global $config;
      global $mysqli;
      include($config['root_dir'] . 'theme/admin_header.php');
      global $cnt;
      $datafile = $_GET['linedata'];
      $filename = $_GET['file_name'];
      $username = $_GET['user_name'];
	
      $reader = new Spreadsheet_Excel_Reader();
      $reader->setOutputEncoding('CP1251');
      $reader->read($datafile);
      $linedata = $reader->sheets[0];
      $cols = $reader->sheets[0]['numCols'];
      $rows = $reader->sheets[0]['numRows'];
	
      // Available line properties:
      $res = mysqli_query($mysqli, "select name from properties") or die (mysqli_error($mysqli));
      while ($r = mysqli_fetch_row($res))
	$properties[] = $r[0];

      // First, locate the header line.
      $firstline = 0;
      $header = array();
      for ($irow = 2; $irow <=$rows; $irow++) {
	$teststr= addcslashes(trim($linedata['cells'][$irow][1]),"\0..\37!@\177..\377");
	if (empty($teststr)){
	  break; 
	} 
	elseif (strtolower($teststr) =="*line name") {
	  $firstline = $irow;
	  // read out header line
	  for ($icol = 1; $icol <= $cols; $icol++) {
	    $value = addcslashes(trim($linedata['cells'][$irow][$icol]),"\0..\37!@\177..\377");
	    $header[] = $value;
	  }
	  break 1;
	}
      }
      // Parse the header cells.
      foreach($header as $columnOffset => $columnName) { // Loop through the columns in the header row.
	// Determine the column offset of "*Line Name"...
	if (strtolower(trim($columnName)) == '*line name')
	  $columnOffsets['line_name'] = $columnOffset+1;
	// Find Properties, and determine the column offset...
	$pr = trim($columnName);
	if (in_array($pr, $properties)) {
	  // Get this property's allowed values.
	  $propuid = mysql_grab("select properties_uid from properties where name = '$pr'");
	  $sql = "select value from property_values where property_uid = $propuid";
	  $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
	  while ($r = mysqli_fetch_row($res)) 
	    $allowedvals[$pr][] = $r[0];
	  $columnOffsets[$columnName] = $columnOffset+1;
	  $ourprops[] = $pr;
	}
      } // end foreach($header as $columnOffset => $columnName)

      for ($irow = $firstline+2; $irow <=$rows; $irow++)  {
	// Ignore rows with first cell empty.
	if (empty($linedata['cells'][$irow][1])) break;
	//Extract data
	$line = strtoupper(trim($linedata['cells'][$irow][$columnOffsets['line_name']]));
	if (!empty($line)) {
	  $line_uid=get_lineuid($line);  // $line_uid is an array.
	  $line_uids = implode(",",$line_uid);  // $line_uids is a string containing a single uid. 
	  // Update property values in table line_properties.
	  foreach ($ourprops as $pr) {
	    $propval[$pr] = addcslashes(trim($linedata['cells'][$irow][$columnOffsets[$pr]]),"\0..\37!@\177..\377");
	    if (!empty($propval[$pr])) {
	      $propval[$pr] = mysqli_real_escape_string($mysqli, $propval[$pr]);
	      $propuid = mysql_grab("select properties_uid from properties where name = '$pr'");
	      $propvaluid = mysql_grab("select property_values_uid from property_values 
                                          where property_uid = $propuid and value = '$propval[$pr]'");
	      // Is there already a value for this line and property?  If so, replace.
	      $linepropuid = mysql_grab("select line_properties_uid
			      from line_properties lp, property_values pv
			      where line_record_uid = $line_uids and property_uid = $propuid
			      and lp.property_value_uid = pv.property_values_uid");
	      if (!empty($linepropuid)) {
		mysqli_query($mysqli, "update line_properties set property_value_uid = $propvaluid
                        where line_properties_uid = $linepropuid") or errmsg($sql, mysqli_error($mysqli));
	      }
	      else {
		mysqli_query($mysqli, "insert into line_properties (line_record_uid, property_value_uid) 
                          values ($line_uids, $propvaluid)") or errmsg($sql, mysqli_error($mysqli));
	      }
	    }
	  }
      } /* end of if (!empty($line)) */
    }
		 
      echo "<h3>Loaded</h3>";
      echo "The data was loaded successfully. You can check it with <a href='".$config['base_url']."search.php'>Quick search...</a>";
      // Timestamp, e.g. _28Jan12_23:01
      $ts = date("_jMy_H:i");
      $filename = $filename . $ts;
      $devnull = mysqli_query($mysqli, "INSERT INTO input_file_log (file_name,users_name) VALUES('$filename', '$username')") or die(mysqli_error($mysqli));

      $footer_div = 1;
      include $config['root_dir'].'theme/footer.php';
    } /* end of function type_Database */
} /* end of class */
