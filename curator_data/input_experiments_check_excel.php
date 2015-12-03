<?php 

// todo: 
//  Reword input_experiments_upload_excel.php, "single" Trial
//  Implement "--" deletes existing value.

/**
 * Phenotype Experiment Results
 * @category PHP
 * @package  T3
 */
// 02/25/2014 DEM   Allow loading multiple trials in the same file.
// 02/01/2011 JLee  Fix indentations and fatal error not presenting data
// 02/01/2011 JLee  Fix problem with line with the value of 0
// 12/14/2010 JLee  Change to use curator bootstrap

require 'config.php';
include $config['root_dir'] . 'includes/bootstrap_curator.inc';
include $config['root_dir'] . 'curator_data/lineuid.php';
require_once "../lib/Excel/reader.php"; // Microsoft Excel library
connect();
loginTest();
ob_start();
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();

// Define reserved words that are trial summary statistics instead of Line names.
$stats = array('*Trial Mean', '*Std. Error', '*Replications');
// Translate to the corresponding column name in table phenotype_mean_data.
$col_lookup = array('trialmean' => 'mean_value', 'std.error' => 'standard_error', 
		    'std.errordiff.' => 'std_err_diff', 'prob>f' => 'prob_gt_F', 
		    'coef.var.' => 'cv', 'replications' => 'number_replicates');

/* Returns $arg1 if it is set, else fatal with error message $msg. */
function ForceValue(& $arg1, $msg) {
  if (isset($arg1))   
    return $arg1;
  die($msg);
}

new LineNames_Check($_GET['function']);
class LineNames_Check {
  /* Using the class's constructor to decide which action to perform
   * @param unknown_type $function
   */
  public function __construct($function = null) {	
    switch($function) {
      case 'typeDatabase':
	$this->type_Database(); /* update database */
	break;
      default:
	$this->typeExperimentCheck(); /* initial case*/
	break;
      }	
  }

/* header and footer wrapped around type_Experiment_Name() */
  private function typeExperimentCheck() {
    global $config;
    include($config['root_dir'] . 'theme/admin_header.php');
    echo "<h2>Phenotype Data Validation</h2>"; 
    $this->type_Experiment_Name();
    $footer_div = 1;
    include($config['root_dir'].'theme/footer.php');
  }
	
/* check experiment data before loading into database */	
  private function type_Experiment_Name() {
    global $config;
    global $col_lookup, $stats;
    // Create the upload directory.
    $row = loadUser($_SESSION['username']);
    $username=$row['name'];
    $tmp_dir="uploads/tmpdir_".$username."_".rand();
    umask(0);
    if(!file_exists($tmp_dir) || !is_dir($tmp_dir)) 
      mkdir($tmp_dir, 0777);
    $target_path=$tmp_dir."/";
    /* No longer needed.? */
    /* $raw_path= "../raw/phenotype/".$_FILES['file']['name'][1]; */
    /* if (!empty($_FILES['file']['name'][1])) */
    /*   if (move_uploaded_file($_FILES['file']['tmp_name'][1], $raw_path) !== TRUE) */
    /* 	echo "<font color=red><b>Oops!</b></font> Your raw data file <b>" */
    /* 	  .$_FILES['file']['name'][1]."</b> was not saved in directory ".$config['root_dir']."raw/ and */
    /*            will be lost.  Please <a href='".$config['base_url']."feedback.php'>contact the  */
    /*            programmers</a>.<p>"; */
    // Upload the means file.
    if ($_FILES['file']['name'][0] == ""){
      error(1, "No File Uploaded");
      print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
    }
    else {
      $uploadfile=$_FILES['file']['name'][0];
      /* $rawdatafile = $_FILES['file']['name'][1]; */
      $uftype=$_FILES['file']['type'][0];
      if (strpos($uploadfile, ".xls") === FALSE) {
	error(1, "Expecting an Excel .xls file. <br> The type of the uploaded file is ".$uftype);
	print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
      }
      else {
	if (move_uploaded_file($_FILES['file']['tmp_name'][0], $target_path.$uploadfile)) {
	  // if successful upload
	  $meansfile = $target_path.$uploadfile;
	  /* Read the Means file */
	  $reader = new Spreadsheet_Excel_Reader();
	  $reader->setOutputEncoding('CP1251');
	  $reader->read($meansfile);
	  $means = $reader->sheets[0];
	  $cols = $reader->sheets[0]['numCols'];
	  $rows = $reader->sheets[0]['numRows'];

	  // Determine whether the single-trial or multi-trial format is being used.
	  if ($means['cells'][5][1] == "*Line Name") {
	    $singletrial = TRUE;
	    $namecolumn = 1;
	    $headerrow = 5;
	  }
	  else if ($means['cells'][3][2] == "*Line Name") {
	    $singletrial = FALSE;
	    $namecolumn = 2;
	    $headerrow = 3;
	  }
	  else {
	    echo "<b>Error</b>: Can't read the spreadsheet's format. \"<b>*Line Name</b>\" must be in ";
	    echo "cell A5 for single-trial template, or cell B3 for multi-trial files.<p>";
	    exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
	  }

	  if ($singletrial) {
	    /* Figure out which experiment to use */
	    $trial_code = $means['cells'][4][2];
	    $experiment_uid = mysql_grab("select experiment_uid as id from experiments where trial_code = '$trial_code'");
	    if (!$experiment_uid) {
	      echo "<b>Error</b>: Trial ".$trial_code. " does not exist <p>";
	      exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
	    } 
	  } // end if $singletrial format
	  /**
	   * Columns in the means file header row, row 3 (multitrial) or 5 (singletrial).
	   * Tells the script which column is which.
	   **/
	  $COL_LINENAME = $COL_CHECK = $COL_GENERATION = $COL_SEEDYEAR = $COL_SEEDEXPT = $COL_SEED_ID = 0;
	  for ($i = $namecolumn; $i <= $cols; $i++) {
	    $teststr = str_replace(array(' ','*'), '', strtolower($means['cells'][$headerrow][$i]));
	    if (stripos($teststr,'linename')!==FALSE)
	      $COL_LINENAME = $i;
	    if (stripos($teststr,'check')!==FALSE)
	      $COL_CHECK = $i;
	    if (stripos($teststr,'filial')!==FALSE)
	      $COL_GENERATION = $i;
	  }
	  // Check if a required col is missing
	  if (($COL_LINENAME*$COL_CHECK)==0) {
	    echo "COL_LINENAME = $COL_LINENAME; COL_CHECK = $COL_CHECK; headerrow = $headerrow<p>";
	    echo "Missing column: Line Name and Check are required.<p>";
	    exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\"><br>");
	  }
	  // Accommodate variant templates with four seed stock columns.
	  if ($COL_GENERATION != 0)
	    $offset = $COL_LINENAME + 6;//column where phenotype data starts
	  else
	    $offset = $COL_LINENAME + 2;
	  // Read in the trait names.
	  $phenonames = array();
	  $phenoids = array();
	  for ($i = $offset; $i <= $cols; $i++) {
	    $teststr= addcslashes(trim($means['cells'][$headerrow][$i]),"\0..\37!@\177..\377");
	    // DEM dec13: Don't assume there are no empty columns interspersed. It has happened.
	    if (empty($teststr)) break;
	    else {
	      $teststr= str_replace('\\n',' ',$teststr);
	      $pheno_cur =trim($teststr);
	      $sql = "SELECT phenotype_uid as id,phenotypes_name as name, 
                               max_pheno_value as maxphen, min_pheno_value as minphen, datatype
		             FROM phenotypes
			     WHERE phenotypes_name = '$pheno_cur'";
	      $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
	      if ($row = mysql_fetch_assoc($res)) {
		$datatypes[] = $row['datatype'];
		$phenonames[] =  $row['name'];
		$phenoids[] = $row['id'];//$phenotype_uid;
		$pheno_max[] = $row['maxphen'];
		$pheno_min[] = $row['minphen'];
		$pheno_idx[$row['name']] = $i;
	      } 
	      else 
		$eflgs[] = $pheno_cur;
	    } // end test for empty columns
	  } // end for $i++
	  $pheno_num = count($phenoids);
	  if (count($eflgs) > 0) {
	    foreach ($eflgs as $bad)
	      echo "Trait <font color = red>$bad</font> does not exist in the database.<br> ";
	    exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\"><br>");
	  }
   
	  // Process the data cells.
	  if ($singletrial === TRUE) {
	    // Case 1: We're using the single-trial template format.
	    $current = NULL;	// the current row
	    $BeginLinesInput = FALSE;
	    for($i = $headerrow + 1; $i <= $rows; $i++) {
	      $current = $means['cells'][$i];
	      // Test if row is empty, if yes then skip to the next row.
	      if (!empty($current)) {
		// Test if this row is a summary stat.
		if ($BeginLinesInput === FALSE) {
		  $statname = str_replace(array(" ", "*"),"",strtolower(trim($current[$namecolumn])));
		  $fieldname = $col_lookup[$statname];
		  for ($j = 0; $j < $pheno_num; $j++) {
		    $pheno_uid =$phenoids[$j];
		    $phenotype_data = trim($current[$offset+$j]);
		    if (strlen($phenotype_data) == 0) 
		      $phenotype_data = "NULL";
		    else if ( (!is_numeric($phenotype_data)) AND ($fieldname != 'prob_gt_F') ) 
		      echo "<font color=red><b>Error:</b></font> Value is not numeric. <b>".$current[1]."</b> for 
                         <b>". $phenonames[$j]."</b> = '".$phenotype_data."'<br>";
		  } // end for($j)
		  if (preg_match('/^trialinformationgoesabove/', $statname)) {
		    // Finished reading summary stats.  Remaining rows are data for individual lines.
		    $BeginLinesInput = TRUE;
		    $current = $means['cells'][$i];
		  } 
		} // end if($BeginLinesInput === FALSE), collecting trial statistics
		else {
		  // This row is data for a line.
		  // Get required columns LineName and Check, and validate.
		  $line_name = ForceValue($current[$COL_LINENAME], "<b>Error</b>: missing Line Name at row " . $i);
		  $line_record_uid = mysql_grab("select line_record_uid from line_records where line_record_name = '$line_name'");
	  if (!$line_record_uid) {  
		    /* Translate synonyms */
		    $line_record_uid = mysql_grab("select line_record_uid from line_synonyms where line_synonym_name = '$line_name'");
		    if (!$line_record_uid) {
		      echo "Line name or synonym not found for line <b>$line_name</b> at row <b>$i</b><p>";
		      exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
		    }
		  }
		  $check = ForceValue($current[$COL_CHECK], "<b>Error</b>: missing Check value at row " . $i);
		  if ($check != 0 AND $check != 1) {
		    echo "Error, row $i: Check must be either 0 or 1.<p>";
		    exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
		  }
		  for ($j=0;$j<$pheno_num;$j++) {
		    $pheno_uid =$phenoids[$j];
		    $phenotype_data = $current[$offset+$j];
		    if ((!is_null($phenotype_data)) && ($phenotype_data!="")) {
		      // Test that the value is numeric if the schema says it must be.
		      $dt = $datatypes[$j];
		      if ( (!is_numeric($phenotype_data)) AND ($dt != "string") AND ($dt != "text") ) {
			echo "<font color=red><b>Error:</b></font> Data not numeric. 
                                     <b>".$line_name."</b>: ".$phenonames[$j]." = 
                                     <font color=red><b>'".$phenotype_data."'</b></font><br>";
		      } 
		      // Fix occasional Excel problem with zeros coming up as very small negative numbers like E-12.
		      if (abs($phenotype_data) < .00001)
			$phenotype_data = 0;
		      //Test if phenotype data is within the specified range given in the database.
		      if (($pheno_min[$j] !=$pheno_max[$j]) && (($phenotype_data<$pheno_min[$j]) ||($phenotype_data>$pheno_max[$j]))) 								
			echo "<font color=red><b>Error:</b></font> Out of bounds 
                           line,trait,value: ".$line_name.",".$phenonames[$j].",".$phenotype_data."<br>";
		    } // end if !is_null, empty cell
		  } // end for $j++ over columns
		$lines_count++;
	      } // end Read each line's data, $BeginLinesInput is TRUE.
	    } // end not skipping a row, if (!empty($current))
	  } // end for loop through rows $i
	} // end single-trial format
	else {
	  // Case 2: We're using the multi-trial template format.
	  $tcodes = array();
	  for($i = $headerrow + 1; $i <= $rows; $i++) {
	    $current = $means['cells'][$i];
	    // Test if row is empty, if yes then skip to the next row.
	    if (!empty($current)) {
	      // Read trial_code (column 1) and check it.
	      $trial_code = $current[1];
	      $experiment_uid = mysql_grab("select experiment_uid from experiments where trial_code='$trial_code'");
	      if (!$experiment_uid)
		echo "<font color=red><b>Error</b></font>: Trial '<b>$trial_code</b>' is 
                      not in the database, row <b>$i</b>.<br>";
	      else
		if (!in_array($trial_code, $tcodes))
		  $tcodes[] = $trial_code;
	      // Test if this row is a summary statistic.
	      $nm = $current[$namecolumn];
	      if (preg_match('/^\*/', $nm)) {
		if (!in_array($nm, $stats)) 
		  echo "<font color=red>Unexpected</font> line name <b><font color=red>'$nm'</font></b>, row <b>$i</b><br>";
		else {
		  // Validate stats that the values are numeric.
		  $statname = str_replace(array(" ", "*"),"",strtolower(trim($nm)));
		  $fieldname = $col_lookup[$statname];
		  for ($j = 0; $j < $pheno_num; $j++) {
		    $pheno_uid =$phenoids[$j];
		    $phenotype_data = trim($current[$offset+$j]);
		    if (strlen($phenotype_data) == 0) 
		      $phenotype_data = "NULL";
                    // Allow "--" to force missing data.
		    else if ( (!is_numeric($phenotype_data)) AND ($phenotype_data != '--') AND ($fieldname != 'prob_gt_F') ) 
		      echo "<font color=red><b>Error:</b></font> Value is not numeric.
                            Trait <b>". $phenonames[$j]."</b> for line <b>".$current[$namecolumn]."</b>
                            = <font color=red>'$phenotype_data'</font> , row <b>$i</b><br>";
		  } // end for($j) over columns, stats for each trait
		}
	      } // end 'Line Name' begins with '*', summary stat.
	      else {
		// This row is data for a line.
		// Get required columns LineName and Check, and validate.
		$line_name = ForceValue($current[$COL_LINENAME], "<b>Error</b>: missing Line Name at row " . $i);
		$line_record_uid = mysql_grab("select line_record_uid from line_records where line_record_name = '$line_name'");
		if (!$line_record_uid) {  
		  /* Translate synonyms */
		  $line_record_uid = mysql_grab("select line_record_uid from line_synonyms where line_synonym_name = '$line_name'");
		  if (!$line_record_uid) 
		    echo "<font color=red><b>Error</font></b>: Line name <font color=red>'$line_name'</font> is not in the database, row <b>$i</b><br>";
		}
		$check = ForceValue($current[$COL_CHECK], "<b>Error</b>: missing Check value at row " . $i);
		if ($check != 0 AND $check != 1) {
		  echo "<b><font color=red>Error</font></b>, row <b>$i</b>: Check must be either 0 or 1.<p>";
		  exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
		}
		for ($j=0;$j<$pheno_num;$j++) {
		  $pheno_uid =$phenoids[$j];
		  $phenotype_data = $current[$offset+$j];
		  if ((!is_null($phenotype_data)) && ($phenotype_data!="")) {
		    // Test that the value is numeric if the schema says it must be.
		    // Allow "--" to force missing data.
		    $dt = $datatypes[$j];
		    if ( (!is_numeric($phenotype_data)) AND ($phenotype_data != '--') AND ($dt != "string") AND ($dt != "text") ) {
		      echo "<font color=red><b>Error</b></font>, row <b>$i</b>: Data not numeric. 
                                     <b>".$line_name."</b>: ".$phenonames[$j]." = 
                                     <font color=red><b>'".$phenotype_data."'</b></font><br>";
		    } 
		    // Fix occasional Excel problem with zeros coming up as very small negative numbers like E-12.
		    if (abs($phenotype_data) < .00001)
		      $phenotype_data = 0;
		    //Test if phenotype data is within the specified range given in the database.
		    if (($pheno_min[$j] !=$pheno_max[$j]) && (($phenotype_data<$pheno_min[$j]) ||($phenotype_data>$pheno_max[$j]))) 								
		      echo "<font color=red><b>Error</b></font>, row <b>$i</b>: 
                          Value out of bounds. <b>".$line_name."</b>, ".$phenonames[$j]." = 
                          <font color=red>".$phenotype_data."</font><br>";
		  } // end if !is_null, non-empty cell
		} // end for $j++ over columns
		$lines_count++;
	      }
	    }
	  }
	}
?>

<script type="text/javascript">
  function update_database(filepath, filename, username, rawdatafile) {
  var url='<?php echo $_SERVER[PHP_SELF];?>?function=typeDatabase&expdata=' + filepath + '&file_name=' + filename + '&user_name=' + username + '&raw_data_file=' + rawdatafile;
  // Opens the url in the same window
  window.open(url, "_self");
  }
</script>
   
<style type="text/css">
  td {border: 1px solid #eee;}
</style>

<h3>Data in the uploaded file</h3>
File:  <i><?php echo $uploadfile ?></i><br>

<?php 
         //Show the user what we see in the file.
	  if ($singletrial)
	    echo "Trial: <b>$trial_code</b><br>";
	  else {
	    $trnum = count($tcodes);
	    $trlist = implode(", ", $tcodes);
	    echo "Data read for <b>$trnum</b> trials: <b>$trlist</b><br>";
	  }
          if (!$pheno_num) $pheno_num = 0;
	  if (!$lines_count) $lines_count = 0;
	  echo "Data read for <b>$pheno_num</b> traits, to the first empty column.<br>";
	  if ($singletrial)  // $lines_count isn't correct for the multitrial case.
	    echo "Data read for <b>$lines_count</b> lines.";
	  // Table header
	  echo "<p> <table> <thead> <tr>";
	  for ($i = 1; $i < ($namecolumn + 2); $i++) 
	      echo "<th>".$means['cells'][$headerrow][$i]."</th>";
	  // Trait names 
	  for ($i = 0; $i < $pheno_num;  $i++) 
	    echo "<th>$phenonames[$i]";
?>
    </tr>
  </thead>
<tbody style="padding: 0; width: 700px; overflow: scroll;">
<?php
	/* printing the values onto the page for user*/
	for ($i = $headerrow + 1; $i <= $rows; $i++) {
	  echo "<tr>";
	  $current_row = $means['cells'][$i];
	  // Don't display this internal template instruction.
	  if (!preg_match('/^Trial information goes above/', $current_row[1])) {
	    // first 2 or three columns:
	    for ($j = 1; $j <= $namecolumn+1; $j++) 
		echo "<td>$current_row[$j]";
	    // trait values:
	    for ($j = $offset; $j < $offset+$pheno_num; $j++) 
		echo "<td>$current_row[$j]";
	    echo "</tr>";
	  }
	} 
?>
</tbody>
</table>
<input type="Button" value="Accept" onclick="javascript: update_database('<?php echo $meansfile?>','<?php echo $uploadfile?>','<?php echo $username?>','<?php echo $rawdatafile ?>' )"/>
<input type="Button" value="Cancel" onclick="history.go(-1);" />

<?php
        } // end if successful upload
	else {
	  error(1,"There was an error uploading the file.");
	  print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
	}
      }
    }
  } /* end of type_Experiment_Name function*/

 /**
  * after accepting data load into database
  */
 private function type_Database() {
	
   global $config;
   include($config['root_dir'] . 'theme/admin_header.php');
	
   //connect_dev();	/* connecting to development database */
	
   $meansfile = $_GET['expdata'];
   $filename = $_GET['file_name'];
   $username = $_GET['user_name'];
   $rawdatafile = $_GET['raw_data_file'];
	
   $reader = new Spreadsheet_Excel_Reader();
   $reader->setOutputEncoding('CP1251');
   $reader->read($meansfile);
   $means = $reader->sheets[0];
   $cols = $reader->sheets[0]['numCols'];
   $rows = $reader->sheets[0]['numRows'];

   // Determine whether the single-trial or multi-trial format is being used.
   if ($means['cells'][5][1] == "*Line Name") {
     $singletrial = TRUE;
     $namecolumn = 1;
     $headerrow = 5;
   }
   else if ($means['cells'][3][2] == "*Line Name") {
     $singletrial = FALSE;
     $namecolumn = 2;
     $headerrow = 3;
   }

   if ($singletrial) {
     /* Figure out which experiment to use */
     $trial_code = $means['cells'][4][2];
     $experiment_uid = mysql_grab("select experiment_uid as id from experiments where trial_code = '$trial_code'");
     $exptids[$trial_code] = $experiment_uid;
     // dem 22feb14: Don't use this value! Use the one loaded already in the Trial Annotation. 
     /* $breeding_program_name = $means['cells'][3][2]; */
     $breeding_program_name = mysql_grab("select data_program_code
		    from CAPdata_programs c, experiments e
		    where trial_code = '$trial_code' 
		    and c.CAPdata_programs_uid = e.CAPdata_programs_uid");
   } // end if $singletrial format
	
   /**
    * Columns in the means file header row, row 3 (multitrial) or 5 (singletrial).
    * Tells the script which column is which.
    **/
   $COL_LINENAME = $namecolumn;
   $COL_CHECK = $COL_FILGEN = $COL_SSYEAR = $COL_SSEXPT = $COL_SSID = 0;
   for ($i = $namecolumn; $i <= $cols; $i++) {
     $teststr = str_replace(array(' ','*'), '', strtolower($means['cells'][$headerrow][$i]));
     if (stripos($teststr,'check') !== FALSE) $COL_CHECK = $i;
     elseif (stripos($teststr,'filial') !== FALSE) $COL_GENERATION = $i;
   }
   // Accommodate variant templates with four seed stock columns.
   if ($COL_GENERATION != 0)
     $offset = $COL_LINENAME + 6;//column where phenotype data starts
   else
     $offset = $COL_LINENAME + 2;
   // Read in the trait names.
   $phenonames = array();
   $phenoids = array();
   for ($i = $offset; $i <= $cols; $i++) {
     $teststr= addcslashes(trim($means['cells'][$headerrow][$i]),"\0..\37!@\177..\377");
     // DEM dec13: Don't assume there are no empty columns interspersed. It has happened.
     if (empty($teststr)) break;
     else {
       // Assume any non-empty cell is the name of a trait.
       $teststr= str_replace('\\n',' ',$teststr);
       $pheno_cur =trim($teststr);
       $sql = "SELECT phenotype_uid as id,phenotypes_name as name, 
                   max_pheno_value as maxphen, min_pheno_value as minphen, datatype
   	       FROM phenotypes
	       WHERE phenotypes_name = '$pheno_cur'";
       $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
       while ($row = mysql_fetch_assoc($res)) {
	 $datatypes[] = $row['datatype'];
	 $phenonames[] =  $row['name'];
	 $phenoids[] = $row['id'];//$phenotype_uid;
	 $pheno_max[] = $row['maxphen'];
	 $pheno_min[] = $row['minphen'];
	 $pheno_idx[$row['name']] = $i;
       } 
     }
   }
   $pheno_num = count($phenoids);
   
   // Process the data cells.
   global $col_lookup;
   $current = NULL;	// the current row
   /* $num_exp = 0; */
   /* $experiment_uids[$num_exp] = -1; */
   if ($singletrial) {
     // Case 1: We're using the single-trial template format.
     // Remove checkline data for the phenotypes in this experiment from phenotype_data table.
     // This will help deal with multiple copies of a check_line.
     // Get tht-base_uids for checklines. Only do this the first time through for an experiment.
     $pheno_uids = implode(",", $phenoids);
     $sql = "SELECT tht_base_uid FROM tht_base
                    WHERE check_line = 'yes' AND experiment_uid = '$experiment_uid'";
     $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
     if (mysql_num_rows($res) > 0) {
       while ($row = mysql_fetch_array($res))
	 $tht_base_uids[] = $row['tht_base_uid'];
       $tht_base_uids = implode(',',$tht_base_uids);
       $sql = "DELETE FROM phenotype_data
	     		WHERE tht_base_uid IN ($tht_base_uids) AND phenotype_uid IN ($pheno_uids)";
       $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
       unset($tht_base_uids);
     }
     $BeginLinesInput = FALSE;   
     for($i = $headerrow + 1; $i <= $rows; $i++)    {
       $current = $means['cells'][$i];
       // If row is empty then skip to the next row.
       if (!empty($current)) {
	 $statname = str_replace(array(" ", "*"),"",strtolower(trim($current[$namecolumn])));
	 if (preg_match('/^trialinformationgoesabove/', $statname)) {
	   $BeginLinesInput = TRUE;
	   $i++;
	   $current = $means['cells'][$i];
	 } 
	 if ($BeginLinesInput === FALSE) {
	   // Not yet down to the data for individual lines. Deal with statistics.
	   // Identify which statistic it is.
	   $fieldname = $col_lookup[$statname];
	   for ($j=0;$j<$pheno_num;$j++) {
	     $pheno_uid =$phenoids[$j];
	     $phenotype_data = trim($current[$offset+$j]);
	     // If the spreadsheet cell is either empty or "--", set the MySQL table's phenotype_data.value to NULL.
	     if (strlen($phenotype_data) == 0 OR $phenotype_data == "--") 
	       $phenotype_data = "NULL";
	     else
	       $phenotype_data = "'".$phenotype_data."'";
	     // Check if there are existing statistics data for this experiment. If yes then update.
	     $sql = "SELECT phenotype_mean_data_uid FROM phenotype_mean_data
                                WHERE phenotype_uid = '$phenoids[$j]'
                                AND experiment_uid = '$experiment_uid'";
	     $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
	     if (mysql_num_rows($res) > 0) 
	       $sql = "UPDATE phenotype_mean_data SET $fieldname = $phenotype_data, updated_on=NOW()
                                    WHERE experiment_uid = '$experiment_uid' AND phenotype_uid = '$phenoids[$j]'";
	     else 
	       $sql = "INSERT INTO phenotype_mean_data SET $fieldname = $phenotype_data,
                                    experiment_uid = '$experiment_uid', phenotype_uid = '$phenoids[$j]',
                                    updated_on=NOW(), created_on = NOW()";
	     $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
	   }
	 } // end of if ($BeginLinesInput === FALSE), finished collecting trial statistics
	 else {
	   // All remaining rows are for individual lines.
	   $line_name = ForceValue($current[$COL_LINENAME], "<b>Error</b>: Missing line name at row " . $i);
	   $line_record_uid = mysql_grab("select line_record_uid from line_records where line_record_name = '$line_name'");
	   if (!$line_record_uid) {  
	     /* Translate synonyms */
	     $line_record_uid = mysql_grab("select line_record_uid from line_synonyms where line_synonym_name = '$line_name'");
	   }
	   $check =	ForceValue($current[$COL_CHECK], "<b>Error</b>: Missing Check value at row " . $i);
	   if ($check == 0) {
	     /* Figure out which dataset to use if this is not a checkline.
	        Checks are not considered part of the dataset. */
	     $BPcode_uid = mysql_grab("SELECT CAPdata_programs_uid FROM CAPdata_programs
                                      WHERE data_program_code = '$breeding_program_name'");
	     // Is there already a dataset for this experiment?
	     $sql = "SELECT de.datasets_experiments_uid as id
                     FROM datasets_experiments AS de, datasets AS ds
                     WHERE de.datasets_uid = ds.datasets_uid
		     AND experiment_uid = '$experiment_uid' limit 1";
	     $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
	     if (mysql_num_rows($res) != 0) {
	       $row = mysql_fetch_assoc($res);
	       $de_uid = $row['id'];
	     } 
	     else {
	       // No dataset for this experiment.  
	       // Dataset name is data program name plus year.  Get year from 
	       // previously loaded experiment annotation.
	       $year = mysql_grab("select experiment_year from experiments where trial_code = '$trial_code'");
	       $ds_name = $breeding_program_name . substr($year, -2);
	       // Get datasets_uid if the dataset already exists.
	       $sql = "SELECT datasets_uid as id
                        FROM  datasets
                        WHERE dataset_name ='$ds_name'";
	       $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
	       if (mysql_num_rows($res) == 0) { 
		 // Doesn't exist yet. Set new dataset experiment code.
		 $sql = "INSERT INTO datasets SET CAPdata_programs_uid='$BPcode_uid',
                           breeding_year = '$year', dataset_name = '$ds_name', updated_on=NOW(),
                           created_on = NOW()";
		 $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
		 $ds_uid = mysql_insert_id();
	       } 
	       elseif (mysql_num_rows($res) == 1) {
		 $row = mysql_fetch_assoc($res);
		 $ds_uid = $row['id'];
	       }
	       // Add dataset/experiment link.
	       $sql = "INSERT INTO datasets_experiments SET experiment_uid='$experiment_uid',
                           datasets_uid = '$ds_uid', updated_on=NOW(),
                           created_on = NOW()";
	       $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
	       $de_uid = mysql_insert_id();
	     } // end No dataset for this experiment. 
	   } // end if $check == 0, not a checkline

	   /* Insert line into tht-base.  */
	   $check_val ='no';
	   if ($check == 1) 
	     $check_val ='yes';
	   // Test whether tht_base_uid already exists for this line, Check condition, and trial.
	   $sql = "SELECT tht_base_uid FROM tht_base
                   WHERE line_record_uid='$line_record_uid' AND experiment_uid='$experiment_uid'
  	  	   AND check_line ='$check_val' limit 1";
	   $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
	   if (mysql_num_rows($res) == 1) {
	     // tht_base entry exists. Update it.  E.g. change Check status.
	     $row = mysql_fetch_assoc($res);
	     $tht_base_uid = $row['tht_base_uid'];
	     $sql = "UPDATE tht_base
                        SET line_record_uid = '$line_record_uid',
                        experiment_uid = '$experiment_uid',";
	     if ($check == 1) 
	       $sql .= "check_line='yes', datasets_experiments_uid=NULL,";
	     else 
	       $sql .= "check_line='no', datasets_experiments_uid='$de_uid',";
	     $sql .= "updated_on=NOW()
                        WHERE tht_base_uid = '$tht_base_uid'";
	     $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
	   } 
	   else {
	     // No tht_base entry yet.  Add it.
	     $sql = "INSERT INTO tht_base
                        SET line_record_uid = '$line_record_uid',
                        experiment_uid = '$experiment_uid',";
	     if ($check ==1) 
	       $sql .= "check_line='yes', datasets_experiments_uid=NULL,";
	     else 
	       $sql .= "datasets_experiments_uid='$de_uid',";
	     $sql .= " updated_on=NOW(),created_on = NOW()";
	     $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
	     $tht_base_uid = mysql_insert_id();
	   }   

	   /* Enter phenotype values into the database for this particular line in this
	    * particular experiment. First check if data just needs to be updated.
	    * If not, then insert new data. */
	   // Get phenotypedata columns
	   for ($j=0; $j < $pheno_num; $j++) {
	     $pheno_uid = $phenoids[$j];
	     $phenotype_data = $current[$offset+$j];
	     if (!is_null($phenotype_data)) {
	       $dt = $datatypes[$j];
	       if ( ($dt != "string") AND ($dt != "text") ) {
		 // Fix occasional excel problem with zeros coming up as very small negative numbers (E-12-E-15).
		 if (abs($phenotype_data) < .00001)
		   $phenotype_data = '0';
		 /* //Check if phenotype data is within the specified range given in the database. */
		 /* if (($pheno_min[$j]!=$pheno_max[$j])&&(($phenotype_data<$pheno_min[$j])||($phenotype_data>$pheno_max[$j]))){ */
		 /*   echo "<font color=red><b>Error:</b></font> Out of bounds  */
                 /*     line,trait,value: ".$line_name.",".$phenonames[$j].",".$phenotype_data."<p>"; */
		 /*   exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-2); return;\"><p>"); */
		 /* }  */
	       }
	       if ($check == 0) {
		 // It's an experimental line entry, not a Check.
		 // Test if there is existing data for this experiment, if yes then update.
		 $sql = "SELECT phenotype_data_uid FROM phenotype_data
			WHERE phenotype_uid = '$phenoids[$j]'
			AND tht_base_uid = '$tht_base_uid'";
		 $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
		 // If the phenotype value is the string 'NULL', don't quote it in the SQL statement.
		 if ($phenotype_data != "NULL") 
		   $phenotype_data = "'".$phenotype_data."'";
		 if ( mysql_num_rows($res) > 0) 
		   $sql = "UPDATE phenotype_data SET value = $phenotype_data, updated_on=NOW()
		       WHERE tht_base_uid = '$tht_base_uid' AND phenotype_uid = '$phenoids[$j]'";
		 else
		   $sql = "INSERT INTO phenotype_data SET phenotype_uid = '$phenoids[$j]',
                                       tht_base_uid = '$tht_base_uid', value = $phenotype_data,
                                       updated_on=NOW(), created_on = NOW()";
		 $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
	       } // end if $check is 0, not a Check
	       elseif ($check == 1) {
		 //Insert only as all checklines were deleted at the beginning. The problem
		 //occurs when an experiment has multiple values for the same checklines (e.g., MN data)
		 if (!is_null($phenotype_data)) {
		   $sql = "insert into phenotype_data set phenotype_uid = '$phenoids[$j]',
                             tht_base_uid = '$tht_base_uid', value = '$phenotype_data',
                             updated_on=NOW(), created_on = NOW()";
		   $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
		 }
	       }
	     }
	   }
	 } // end handling a Line's data
       } // end row isn't empty, don't skip
     } // end for loop through rows $i
   } // end Case 1, single-trial format
   else {
     // Case 2: We're using the multi-trial template format.
     // First, cycle through and populate the arrays of Trials.
     $exptids = array();
     for($i = $headerrow + 1; $i <= $rows; $i++)    {
       $current = $means['cells'][$i];
       // If first cell of the row is empty then skip to the next row.
       if (!empty($current[1])) {
	 $trial_code = $current[1];
	 if (!in_array($trial_code, array_keys($exptids))) {
	   // If we haven't seen this Trial before, add it.
	   $exptids[$trial_code] = mysql_grab("select experiment_uid from experiments where trial_code = '$trial_code'");
	   $breeding_program_name[$trial_code] = mysql_grab("select data_program_code
						  from CAPdata_programs c, experiments e
						  where trial_code = '$trial_code' 
						  and c.CAPdata_programs_uid = e.CAPdata_programs_uid");
	   $BPcode_uid[$trial_code] = mysql_grab("SELECT CAPdata_programs_uid FROM CAPdata_programs
                                                  WHERE data_program_code = '$breeding_program_name[$trial_code]'");
	   // Remove checkline data for the phenotypes in this experiment from phenotype_data table.
	   // This will help deal with multiple copies of a check_line.
	   // Get tht-base_uids for checklines. Only do this the first time through for an experiment.
	   $pheno_uids = implode(",", $phenoids);
	   $sql = "SELECT tht_base_uid FROM tht_base
                   WHERE check_line = 'yes' AND experiment_uid = '$exptids[$trial_code]'";
	   $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
	   if (mysql_num_rows($res) > 0) {
	     while ($row = mysql_fetch_array($res))
	       $tht_base_uids[] = $row['tht_base_uid'];
	     $tht_base_uids = implode(',', $tht_base_uids);
	     $sql = "DELETE FROM phenotype_data
                     WHERE tht_base_uid IN ($tht_base_uids) AND phenotype_uid IN ($pheno_uids)";
	     $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
	     unset($tht_base_uids);
	   }
	 } // end if haven't seen this Trial before
       } // end if (!empty($current)) 
     } // end for each row $i, first cycle-through
     // Cycle through again to collect the values for each trial and trait.
     for($i = $headerrow + 1; $i <= $rows; $i++)    {
       $current = $means['cells'][$i];
       // If first cell of the row is empty then skip to the next row.
       if (!empty($current[1])) {
	 $trial_code = $current[1];
	 $nm = $current[$namecolumn];
	 if (preg_match('/^\*/', $nm)) {
	   // Name begins with '*' so this row is a summary statistic.
	   $statname = str_replace(array(" ", "*"),"",strtolower(trim($current[$namecolumn])));
	   $fieldname = $col_lookup[$statname];
	   for ($j = 0; $j < $pheno_num; $j++) {
	     $pheno_uid =$phenoids[$j];
	     $phenotype_data = trim($current[$offset+$j]);
	     // If the spreadsheet cell is either empty or "--", set the MySQL table's phenotype_data.value to NULL.
	     if (strlen($phenotype_data) == 0 OR $phenotype_data == "--") 
	       $phenotype_data = "NULL";
	     /* else */
	     /*   $phenotype_data = "'".$phenotype_data."'"; */
	     // Check if there are existing statistics data for this experiment. If yes then update.
	     $sql = "SELECT phenotype_mean_data_uid FROM phenotype_mean_data
                                WHERE phenotype_uid = '$phenoids[$j]'
                                AND experiment_uid = '$exptids[$trial_code]'";
	     $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
	     if (mysql_num_rows($res) > 0) 
	       $sql = "UPDATE phenotype_mean_data SET $fieldname = $phenotype_data, updated_on=NOW()
                                    WHERE experiment_uid = '$exptids[$trial_code]' AND phenotype_uid = '$phenoids[$j]'";
	     else 
	       $sql = "INSERT INTO phenotype_mean_data SET $fieldname = $phenotype_data,
                                    experiment_uid = '$exptids[$trial_code]', phenotype_uid = '$phenoids[$j]',
                                    updated_on=NOW(), created_on = NOW()";
	     $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
	   } // end for($j) over columns, stats for each trait
	 } // end 'Line Name' begins with '*', summary stat.
	 else {
	   // It's the name of a Line.
	   $line_name = ForceValue($nm, "<b>Error</b>: Missing line name at row " . $i);
	   $line_record_uid = mysql_grab("select line_record_uid from line_records where line_record_name = '$line_name'");
	   if (!$line_record_uid) {  
	     /* Translate synonyms */
	     $line_record_uid = mysql_grab("select line_record_uid from line_synonyms where line_synonym_name = '$line_name'");
	   }
	   $check = ForceValue($current[$COL_CHECK], "<b>Error</b>: Missing Check value at row " . $i);
	   $extrasql = ""; // Initialize to empty. Leave it that way for Check lines.
	   if ($check == 0) {
	     /* not a Check line.  Checks are not considered part of a dataset. */
	     // Figure out which dataset to use. Is there already a dataset linked to this trial?
	     $de_uid = mysql_grab("SELECT de.datasets_experiments_uid
                     FROM datasets_experiments de, datasets ds
                     WHERE de.datasets_uid = ds.datasets_uid
		     AND experiment_uid = '$exptids[$trial_code]' limit 1");
 	     if (empty($de_uid)) {
 	       // Not yet so link to one.  Does the appropriate dataset already exist?
 	       // Dataset name is data program name plus year.  
 	       // Get year from previously loaded experiment annotation.
 	       $year = mysql_grab("select experiment_year from experiments where trial_code = '$trial_code'");
 	       $ds_name = $breeding_program_name . substr($year, -2);
 	       // Get datasets_uid if the dataset already exists.
	       $ds_uid = mysql_grab("SELECT datasets_uid FROM datasets WHERE dataset_name ='$ds_name'");
	       if (empty($ds_uid)) {
		   // Dataset doesn't exist so create it.
		   $sql = "INSERT INTO datasets SET CAPdata_programs_uid='$BPcode_uid[$trial_code]',
                           breeding_year = '$year', dataset_name = '$ds_name', updated_on=NOW(),
                           created_on = NOW()";
		   $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
		   $ds_uid = mysql_insert_id();
		 }
	       // Add dataset/experiment link.
	       $sql = "INSERT INTO datasets_experiments SET experiment_uid='$exptids[$trial_code]',
                           datasets_uid = '$ds_uid', updated_on=NOW(),
                           created_on = NOW()";
	       $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
	       $de_uid = mysql_insert_id();
	       // for editing table tht_base. Experimental lines go into datasets_experiments, Check lines don't.
	       $extrasql = "datasets_experiments_uid = $de_uid, ";
	     } // end if (empty($de_uid)), add datasets_experiments link
	   } // end if $check is 0, not a Check
	   /* Insert line into tht-base.  */
	   if ($check == 1) $check_val = 'yes';
	   else $check_val = 'no';
	   // Test whether tht_base_uid already exists for this line, check condition, and trial.
	   $tht_base_uid = mysql_grab("SELECT tht_base_uid FROM tht_base
                   WHERE line_record_uid='$line_record_uid' AND experiment_uid='$exptids[$trial_code]'
  	  	   AND check_line ='$check_val' limit 1");
	   if (!empty($tht_base_uid)) {
	     // The tht_base entry exists, so update it.
	     $sql = "UPDATE tht_base SET line_record_uid = '$line_record_uid',
		       experiment_uid = '$exptids[$trial_code]', check_line = '$check_val', 
		       $extrasql updated_on=NOW() 
                     WHERE tht_base_uid = '$tht_base_uid'";
	     $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
	   }
	   else {
	     // No tht_base entry yet.  Add it.
	     $sql = "INSERT INTO tht_base SET line_record_uid = '$line_record_uid',
                     experiment_uid = '$exptids[$trial_code]', check_line = '$check_val',
                     $extrasql updated_on=NOW(), created_on = NOW()";
	     $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
	     $tht_base_uid = mysql_insert_id();
	   }

	   /* Enter phenotype values into the database for this particular line in this
	    * particular experiment. First check if data just needs to be updated. If not, insert it. */
	   for ($j=0; $j < $pheno_num; $j++) {
	     $pheno_uid = $phenoids[$j];
	     $phenotype_data = $current[$offset+$j];
	     if (!is_null($phenotype_data)) {
	       $dt = $datatypes[$j];
	       if ( ($dt != "string") AND ($dt != "text") ) {
		 // Fix occasional excel problem with zeros coming up as very small negative numbers (E-12-E-15).
		 if (is_numeric($phenotype_data) AND abs($phenotype_data) < .00001)
		   $phenotype_data = '0';
		 // If the spreadsheet cell is either empty or "--", set the MySQL table's phenotype_data.value to empty.
		 if (strlen($phenotype_data) == 0 OR $phenotype_data == "--") 
		   $phenotype_data = "";
		 // If the phenotype value is the string 'NULL', don't quote it in the SQL statement.
		 if ($phenotype_data !== "NULL") 
		   $phenotype_data = "'".$phenotype_data."'";
	       }
	       if ($check == 0) {
		 // It's an experimental line entry, not a Check.
		 // Test if there is existing data for this experiment, if yes then update, else insert.
		 $sql = "SELECT phenotype_data_uid FROM phenotype_data
			WHERE phenotype_uid = '$phenoids[$j]'
			AND tht_base_uid = '$tht_base_uid'";
		 $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
		 if ( mysql_num_rows($res) > 0) 
		   $sql = "UPDATE phenotype_data SET value = $phenotype_data, updated_on=NOW()
		           WHERE tht_base_uid = '$tht_base_uid' AND phenotype_uid = '$phenoids[$j]'";
		 else 
		   $sql = "INSERT INTO phenotype_data SET phenotype_uid = '$phenoids[$j]',
			   tht_base_uid = '$tht_base_uid', value = $phenotype_data,
			   updated_on=NOW(), created_on = NOW()";
		 $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
	       } // end if $check is 0, not a Check
	       elseif ($check == 1) {
		 //Insert only as all checklines were deleted at the beginning. The problem
		 //occurs when an experiment has multiple values for the same checklines (e.g., MN data)
		 if (DEBUG>2) {echo "checkline data ".$phenotype_data."\n";}
		 if (!is_null($phenotype_data)) {
		   $sql = "insert into phenotype_data set phenotype_uid = '$phenoids[$j]',
                             tht_base_uid = '$tht_base_uid', value = $phenotype_data,
                             updated_on=NOW(), created_on = NOW()";
		   $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
		 }
	       }
	     } // end if $phenotype_data not null
	   } // end for each phenoids[$j]
	 } // end it's the name of a Line, not a statistic
       } // end if !empty($current)
     } // end for each row $i
   } // end of Case 2, multitrial format 

   // Update statistics for all traits in the database.
   $trait_stats = calcPhenoStats_mysql ($phenoids);
   //print_h($trait_stats);
   if ($trait_stats === FALSE) die ("Calculating stats on non-numeric data.");
   if (count($trait_stats) == 0) die ("Calculating stats on non-numeric data.");
   for ($i = 0;$i<count($phenoids);$i++){
     //check if record there
     $max_val= $trait_stats[$i][max_val];
     $min_val= $trait_stats[$i][min_val];
     $mean_val= $trait_stats[$i][mean_val];
     $std_val= $trait_stats[$i][std_val];
     $sample_size= $trait_stats[$i][sample_size];
     $pheno_uid = $trait_stats[$i][phenotype_uid];
     // Store back to the database.
     $sql = "SELECT * FROM phenotype_descstat WHERE phenotype_uid = $pheno_uid";
     $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
     if (mysql_num_rows($res)>0) 
       $sql = "UPDATE phenotype_descstat SET mean_val = $mean_val,
	       max_val = '$max_val', min_val = '$min_val',
	       std_val = $std_val, sample_size = $sample_size,updated_on=NOW()
	       WHERE phenotype_uid = '$pheno_uid'";
     else 
       $sql = "INSERT INTO phenotype_descstat SET mean_val = $mean_val,
	       max_val = $max_val, min_val = $min_val,
	       std_val = $std_val, sample_size = $sample_size,
	       phenotype_uid = $pheno_uid, updated_on=NOW(), created_on = NOW()";
     $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
   }
    
   // Go through experiments in this file to update string of measured traits in
   // the experiment and file name
   //for ($i = 0; $i < $num_exp; $i++){
   foreach (array_values($exptids) as $exid) {
     unset($phenotypes);
     $sql = "SELECT p.phenotype_uid AS id, p.phenotypes_name AS name
	     FROM phenotypes AS p, tht_base AS t, phenotype_data AS pd
	     WHERE pd.tht_base_uid = t.tht_base_uid
	     AND p.phenotype_uid = pd.phenotype_uid
	     AND t.experiment_uid = $exid
	     GROUP BY p.phenotype_uid";
     $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
     while ($row = mysql_fetch_array($res))
       $phenotypes[] = $row['name'];
     $countfound = count($phenotypes);
     if ($countfound > 0) {
       $phenotypes = implode(', ',$phenotypes);
       $sql = "UPDATE experiments SET traits =('$phenotypes') WHERE experiment_uid = $exid";
       $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
     } 
     else {
       $badtc = mysql_grab("select trial_code from experiments where experiment_uid = $exid");
       echo "Warning: There are no trait values for Trial <b>$badtc</b>.<br>";
       $emptytrials[$badtc] = $exid;
     }
		
     // Add meansfile name to experiments.input_data_file_name, append to existing list if different.
     $meansfile = basename($meansfile);
     $input_data_file_name = mysql_grab("SELECT input_data_file_name FROM experiments WHERE experiment_uid = '$exid'");
     if ($input_data_file_name === NULL) 
       $infile = $meansfile;
     else 
       $infile = $input_data_file_name;
     if (stripos($infile, $meansfile) === FALSE) 
       $infile .= ", ".$meansfile;
     $sql = "UPDATE experiments SET input_data_file_name = '$infile', updated_on=NOW()
             WHERE experiment_uid = '$exid'";
     $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
   }

   // Announce success. Exclude any trials which have no data.
   echo "<p>Data was added or updated for the following Trials.<br><ul>";
   if ($emptytrials)
     $exptids = array_diff($exptids, $emptytrials);
   foreach (array_keys($exptids) as $tc)
     echo "<li><a href='$config[base_url]display_phenotype.php?trial_code=$tc'>$tc</a><br>";
   echo "</ul><input type=\"Button\" value=\"Return\" onClick=\"history.go(-2); return;\"><p>";

   $footer_div = 1;
   include($config['root_dir'].'theme/footer.php');

   }/* end of function type_Database() */
 } /* end of class */
