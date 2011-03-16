<?php

// 12/14/2010 JLee  Change to use curator bootstrap

require 'config.php';
/*
 * Logged in page initialization
 */
include($config['root_dir'] . 'includes/bootstrap_curator.inc');
include($config['root_dir'] . 'curator_data/lineuid.php');
require_once("../lib/Excel/reader.php"); // Microsoft Excel library

connect();
loginTest();

function die_nice($message = "") {
  global $config;
  echo $message;
  echo "<p><input type='Button' value='Return' onclick='history.go(-1); return;'>";
  $footer_div = 1;
  include($config['root_dir'].'theme/footer.php');
  exit;
}

/* Show more informative messages when we get invalid data. */
function errmsg($sql, $err) {
  if (preg_match('/^Data truncated/', $err)) {
    $pieces = preg_split("/'/", $err);
    $column = $pieces[1];
    echo "Unallowed value for field <b>$column</b>.";
    // Only works for table line_records.  Could pass table name as parameter.
    $r = mysql_query("describe line_records $column");
    $columninfo = mysql_fetch_row($r);
    echo "<p>Allowed values are: ".$columninfo[1];
    echo "<p>MySQL error message: $err";
    echo "<p>The command was:<br>".$sql;
    die_nice();
  }
  else die_nice("MySQL error: ".$err."<p>The command was:<br>".$sql);
}


/* ******************************* */
$row = loadUser($_SESSION['username']);

////////////////////////////////////////////////////////////////////////////////
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
				
			case 'typeLineData':
				$this->type_Line_Data(); /* Handle Line Data */
				break;
			
			default:
				$this->typeLineNameCheck(); /* intial case*/
				break;
			
		}	
	}


private function typeLineNameCheck()
	{
		global $config;
		include($config['root_dir'] . 'theme/admin_header.php');

		echo "<h2>Line information: Validation</h2>"; 
			
		$this->type_Line_Name();

		$footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
	}
	
	
	private function type_Line_Name()
	{
	?>

	<script type="text/javascript">
	function update_database(filepath, filename, username) {
	  var url='<?php echo $_SERVER[PHP_SELF];?>?function=typeDatabase&linedata=' + filepath + '&file_name=' + filename + '&user_name=' + username;
	  // Opens the url in the same window
	  window.open(url, "_self");
	}
	</script>
	
	<style type="text/css">
	    th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
		table {background: none; border-collapse: collapse}
		td {border: 0px solid #eee !important;}
		h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
		</style>
		
		<style type="text/css">
                   table.marker
                   {background: none; border-collapse: collapse}
                    th.marker
                    { background: #5b53a6; color: #fff; padding: 5px 0; border: 0; }
                    td.marker
                    { padding: 5px 0; border: 0 !important; }
                </style>
		
<?php

	ini_set("memory_limit","24M");
        $row = loadUser($_SESSION['username']);
	$username=$row['name'];
	
	if ($_FILES['file']['name'] == ""){
		error(1, "No File Uploaded");
		print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
	}
	else {
	//$tmp_dir="uploads/tmpdir_".$username."_".rand();
        $tmp_dir="uploads/".str_replace(' ', '_', $username)."_".date('yMd_G:i');
	umask(0);
	if(!file_exists($tmp_dir) || !is_dir($tmp_dir)) {
		mkdir($tmp_dir, 0777);
	}
	$target_path=$tmp_dir."/";
	$uploadfile=$_FILES['file']['name'];
	$uftype=$_FILES['file']['type'];
	//	if (strpos($uploadfile, ".xls") === FALSE) {
	if (preg_match('/\.xls$/', $uploadfile) == 0) {
	  error(1, "Only xls format is accepted. <br>");
	  print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
	}
	else {
	  if(move_uploaded_file($_FILES['file']['tmp_name'], $target_path.$uploadfile)) 
	    {
	      /* start reading the excel */
	      $datafile = $target_path.$uploadfile;
	//	echo "file name is ". $uploadfile; 
	      $reader = & new Spreadsheet_Excel_Reader();
	$reader->setOutputEncoding('CP1251');
	$reader->read($datafile);
	$linedata = $reader->sheets[0];
	$cols = $reader->sheets[0]['numCols'];
	$rows = $reader->sheets[0]['numRows'];
//	if (DEBUG) {
	//	echo "nrows ".$rows." ncols ".$cols."\n";
	//}

	//if (DEBUG) echo "Input File Name: ".$datafile."\n";

	      // Read the Breeding Program from row 3.
	      if ($linedata['cells'][3][1] != "*Breeding Program Code") {
		die("Row 3 must begin with '*Breeding Program Code'.");
		  }
	      $bp = $linedata['cells'][3][2];
	      if (strlen($bp) != 3) {
		die("Invalid or missing Breeding Program Code.");
	      }

/*
 * The following code allows the curator to put the columns in any order.
 * It also allows him/her to supply useless columns
 */
// These are the required columns. -1 means that the column has not been found.
	      $columnOffsets = array(
			'line_name' => -1,
			'species' => -1,
			'growth_habit' => -1
		);

		/* Attempt to find each required column */
		// First, locate the header line.
		$firstline = 0;
		$header = array();
		for ($irow = 4; $irow <=$rows; $irow++) {
			$teststr= addcslashes(trim($linedata['cells'][$irow][1]),"\0..\37!@\177..\377");
			if (empty($teststr)){
			   break; 
			} 
			elseif (strtolower($teststr) == "line name") {
			  $firstline = $irow;
			  // read out header line
			  for ($icol = 1; $icol <= $cols; $icol++) {
			    $value = addcslashes(trim($linedata['cells'][$irow][$icol]),"\0..\37!@\177..\377");
			    $header[] = $value;
			    //if (DEBUG2) echo "row ".$irow." col ".$icol." name ".$value."\n";
			  }
			  break 1;
			}
			else { die("The header row must begin with 'Line Name'.");}
		}
		
		foreach($header as $columnOffset => $columnName){ // loop through the columns in the header row
			//clean up column name so that it can be matched
			$columnName = strtolower(trim($columnName));
			 //break column title into pieces based on spaces and newlines
			//$colpart= explode('\\n',$columnName);
			//$colpart = implode(" ",$columnName);
			$order = array("\n","\t"," ");
			$replace = array(" ",'','');
			$columnName = str_replace($order, $replace, $columnName);
			// DEBUG
			//if (DEBUG2) echo "\n\$columnOffset = ".$columnOffset." => \$columnName = ".$columnName;

			// Determine the column offset of "Line Name"...
			if (preg_match('/^\s*linename\s*$/is', trim($columnName)))
				$columnOffsets['line_name'] = $columnOffset+1;

			// !! add Aliases.
		
			// Determine the column offset of "GRIN Accession"...
			if (preg_match('/^\s*grinaccession\s*$/is', trim($columnName)))
				$columnOffsets['grin'] = $columnOffset+1;
		
			// Determine the column offset of "Pedigree"...
			if (preg_match('/^\s*pedigree\s*$/is', trim($columnName)))
				$columnOffsets['pedigree'] = $columnOffset+1;
		
			// Determine the column offset of "Filial Generation"...
			if (preg_match('/^\s*filialgeneration\s*$/is', trim($columnName)))
				$columnOffsets['generation'] = $columnOffset+1;
		
			// Determine the column offset of "Hard / Soft"...
			if (preg_match('/^\s*hard\/soft\s*$/is', trim($columnName)))
				$columnOffsets['hardness'] = $columnOffset+1;
		
			// Determine the column offset of "Red / White"...
			if (preg_match('/^\s*red\/white\s*$/is', trim($columnName)))
				$columnOffsets['color'] = $columnOffset+1;
		
			// Determine the column offset of "Spring / Winter / Facultative"...
			//if (preg_match('/^\s*growthhabit\s*$/is', trim($columnName)))
			if (preg_match('/^\s*spring\/winter\/facultative\s*$/is', trim($columnName)))
				$columnOffsets['growth_habit'] = $columnOffset+1;
		
			// Determine the column offset of "aestivum / durum"...
			if (preg_match('/^\s*aestivum\/durum\s*$/is', trim($columnName)))
				$columnOffsets['species'] = $columnOffset+1;
		
			// Determine the column offset of "Awned / Awnless"...
			if (preg_match('/^\s*awned\/awnless\s*$/is', trim($columnName)))
				$columnOffsets['awned'] = $columnOffset+1;
		
			// Determine the column offset of "Row Type"...
			if (preg_match('/^\s*rowtype\s*$/is', trim($columnName)))
				$columnOffsets['row_type'] = $columnOffset+1;
		
			// Determine the column offset of "End Use"...
			if (strpos($columnName,'use'))
				$columnOffsets['end_use'] = $columnOffset+1;
				
			// Determine the column offset of "hull"...
			if (preg_match('/^\s*hull\s*$/is', trim($columnName)))
				$columnOffsets['hull'] = $columnOffset+1;

			// Determine the column offset of "Comments"...
			if (preg_match('/^\s*comments\s*$/is', trim($columnName)))
				$columnOffsets['comments'] = $columnOffset+1;
		}
		/* Now check to see if any required columns weren't found */
		if (in_array(-1, $columnOffsets)) {
		  echo "Some required columns were not found, indicated as -1.<br/>";
		  echo "Please don't change the column labels in the header row.<br/>";
		  echo "<pre>"; print_r($columnOffsets); echo "</pre>";
		  exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
		}
	//	if (DEBUG||DEBUG2) echo "<div><pre>\$columnOffsets = ".print_r($columnOffsets, true)."</pre></div>";
				
	      /* my insert update script goes here */
				
	      $icnt= 0;
	      $cnt = 0;
	      $line_inserts_str = "";
	      $line_info_str = "";
	      $line_uid = "";
	      $line_uids = "";
	      $line_uids_multiple = "";
	      $line_uid_Synonyms = "";
	      $CAPcode_Synonyms = "";
	      $linesyn_uids_mismatch = "";
	      $linesyn_uids_multiple = "";
				
	      //Ignore the next row after the header.  Or error.
 	      if ($linedata['cells'][$firstline+1][2] != "comma separated values") {
 		die("Row 2 must be the descriptions of the columns.  Please don't delete it.<br><br>"); 
 	      }
	      for ($irow = $firstline+2; $irow <=$rows; $irow++)  {
		//Extract data
		//if ($cnt>4) exit;
		$line = strtoupper(trim($linedata['cells'][$irow][$columnOffsets['line_name']]));
		$hardness = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['hardness']]),"\0..\37!@\177..\377");
		$color = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['color']]),"\0..\37!@\177..\377");
		$growth = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['growth_habit']]),"\0..\37!@\177..\377");
		$species = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['species']]),"\0..\37!@\177..\377");
		$species = preg_replace("/^a$/", "aestivum", $species);
		$species = preg_replace("/^d$/", "durum", $species);
		$awned = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['awned']]),"\0..\37!@\177..\377");
		$pedstring=addcslashes(trim($linedata['cells'][$irow][$columnOffsets['pedigree']]),"\0..\37!@\177..\377");
		$comments = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['comments']]),"\0..\37!@\177..\377");
		/* For barley.
		 $enduse = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['end_use']]),"\0..\37!@\177..\377");
		 $rowtype = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['row_type']]),"\0..\37!@\177..\377");
		 $hull = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['hull']]),"\0..\37!@\177..\377");
		*/
			
		// Line Name is required.
		if (!empty($line)) {
		  $line_info[] = $line;
		  $line_info_str .= implode(",",$line_info);
		  //	echo " line info string is". $line_info_str;
			
				
		  //	if (DEBUG2) echo $line." ".$pedstring." ".$CAPcode." ".$growth." ".$rowtype." ".$enduse." ".$bp." ".$hull." ".$comments."\n";
		  //check if line is in database
		  $line_uid=get_lineuid($line);
		  //print_r( $line_uid);
		  //if (DEBUG2) print_r($line_uid);
		  if ($line_uid===FALSE) {
		    // Insert new line into database
		    //if (DEBUG||DEBUG2) echo "\n"."***LINE NAME is not present add:".$line."***\n";
		    //convert line name to upper case and replace spaces with an underscore
		    $line = strtoupper(str_replace(" ","_",$line));
		    $line_inserts[] = $line;
		    $line_inserts_str = implode(",",$line_inserts);
		    //	echo " line inserts are" . $line_inserts_str;
		  } 
		  elseif (count($line_uid)==1) { 
		    //update the line record
		    $line_uids[] = implode(",",$line_uid);
		    //echo "var dump of line uids".var_dump($line_uids); 
		    //	echo " updated lines Uid's  are". $line_uids[$icnt];
		    //if (DEBUG2) echo $line." will be updated"."\n";
		  }
		  else {
		    $line_uids_multiple .= implode(",",$line_uid);
		    //	echo " line in multiple records". $line_uids_multiple;
		    $cnt++; /* if this counter is not 0 then no accept option is displayed*/
		    error(0, "$line is found in multiple records ($line_uids_multiple), in line record table, please fix");
		  }
		  // Todo: Adapt this section to validate synonyms.
		  if (!empty($CAPcode)){
		    $linesyn_uid = get_lineuid($CAPcode);
		    // echo " synonym lines Uid's  are". $linesyn_uid;
		    if ($linesyn_uid===FALSE) {
		      // Insert CAPentry code as a synonym into database
		      /*	$line_uid_Synonyms[] = $line_uid;
		       $line_uid_Synonyms .= implode(",",$line_uid_Synonyms);
		       $CAPcode_Synonyms[] = $CAPcode;
		       $CAPcode_Synonyms .= implode(",",$CAPcode_Synonyms);
		      */
		      //	echo " line uid's inserting into line synonyms". $line_uid_Synonyms;
		    } 
		    elseif ((count($linesyn_uid)==1)AND($linesyn_uid!=$line_uid)){
		      if($line_uid !=FALSE)
			{
			  $linesyn_uids = implode(",",$line_uid);
			  $sql = mysql_query("SELECT line_record_name from line_records where line_record_uid in ($linesyn_uids)");
			  $row = mysql_fetch_array($sql);
			  $line_name = $row['line_record_name'];
			  $cnt++;/* if this counter is not 0 then no accept option is displayed*/
			  error(0, "$CAPcode is linked to a diffent line ($line_name), in line record table, please fix");
			}
		    }
		    elseif (count($linesyn_uid)>1) 
		      {
			$linesyn_uids_multiple .= implode(",",$linesyn_uid);
			//echo $CAPcode."is linked multiple lines".$linesyn_uids_multiple."in line record table, please fix";
			$cnt++;/* if this counter is not 0 then no accept option is displayed*/
			error(0, "$CAPcode is linked to multiple lines ($linesyn_uids_multiple), in line record table, please fix");
		      }
		  } /* end of if (!empty($CAPcode)) */
		} /* end of if (!empty($line)) */
	      }
	      if (($line_uids) != "") {
		$line_updates =implode(",",$line_uids);
		// Get line names.
		$line_sql = mysql_query("SELECT line_record_name as name
                        FROM line_records
                        WHERE line_record_uid IN ($line_updates)");
		while ($row = mysql_fetch_array($line_sql, MYSQL_ASSOC)) {
		  $line_update_names[] = $row["name"];
		}
		$line_update_data = $line_update_names;
	      }
	      else $line_update_data = "";
	      $line_insert_data = explode(",",$line_inserts_str);
	      // If any errors, show what we read and stop.
	      if ($cnt != 0) {
		  ?>
			
		  <h3>We were reading the following data from the uploaded file.</h3>
		  Breeding Program: <?php echo $bp ?><p>
		<table >
	<tr>
	<th style="width: 140px;" class="marker">Line Name</th>
	<th style="width: 150px;" class="marker">GRIN</th>
	<th style="width: 70px;" class="marker">Generation</th>
	<th style="width: 50px;" class="marker">Hardness</th>
	<th style="width: 50px;" class="marker">Color</th>
	<th style="width: 50px;" class="marker">Growth Habit</th>
	<th style="width: 100px;" class="marker">Species</th>
	<th style="width: 50px;" class="marker">Awned</th>
	<th style="width: 180px;" class="marker">Pedigree</th>
	<th style="width: 180px;" class="marker">Comments</th>
        <!-- For barley.
	<th style="width: 40px;" class="marker" >Row Type </th>
	<th style="width: 50px;" class="marker" >End Use </th>
	<th style="width: 70px;" class="marker" >Hull </th>
        -->
	</tr>
	</table>
		
		<div id="test" style="padding: 0; height: 200px; width: 800px;  overflow: scroll;border: 1px solid #5b53a6;">
			<table>
		  <?php
		  for ($irow = $firstline+2; $irow <=$rows; $irow++)  {
		    //Extract data
		    $line = strtoupper(trim($linedata['cells'][$irow][$columnOffsets['line_name']]));
		    // Line Name is required.
		    if (!empty($line)) {
		      $grin = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['grin']]),"\0..\37!@\177..\377");
		      $generation = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['generation']]),"\0..\37!@\177..\377");
		      $hardness = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['hardness']]),"\0..\37!@\177..\377");
		      $color = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['color']]),"\0..\37!@\177..\377");
		      $growth = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['growth_habit']]),"\0..\37!@\177..\377");
		      $species = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['species']]),"\0..\37!@\177..\377");
		      $species = preg_replace("/^a$/", "aestivum", $species);
		      $species = preg_replace("/^d$/", "durum", $species);
		      $awned = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['awned']]),"\0..\37!@\177..\377");
		      $pedstring=addcslashes(trim($linedata['cells'][$irow][$columnOffsets['pedigree']]),"\0..\37!@\177..\377");
		      $comments = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['comments']]),"\0..\37!@\177..\377");
		      /* For barley.
		       $enduse = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['end_use']]),"\0..\37!@\177..\377");
		       $rowtype = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['row_type']]),"\0..\37!@\177..\377");
		       $hull = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['hull']]),"\0..\37!@\177..\377");
		      */
		      ?>
			<tr>
			   <td style="width: 120px;">
			   <?php echo $line ?></td> 
			   <td style="width: 120px;">
			   <?php echo $grin ?></td> 
			   <td style="width: 70px;">
			   <?php echo $generation ?></td> 
			   <td style="width: 50px;">
			   <?php echo $hardness ?></td> 
			   <td style="width: 50px;">
			   <?php echo $color ?></td> 
			   <td style="width: 50px;">
			   <?php echo $growth ?></td> 
			   <td style="width: 100px;">
			   <?php echo $species ?></td> 
			   <td style="width: 50px;">
			   <?php echo $awned ?></td> 
			   <td style="width: 180px;">
			   <?php echo $pedstring ?></td> 
			   <td style="width: 180px;">
			   <?php echo $comments ?></td> 
			   <!-- For barley.
			   <td style="width: 50px;">
			   <?php echo $enduse ?></td> 
			   <td style="width: 30px;">
			   <?php echo $rowtype ?></td> 
			   <td style="width: 60px;">
			   <?php echo $hull ?></td> 
			   -->
			   </tr>
			   <?php
			   } /* end of if (!empty($line)) */
		  }/* end of for loop */
		?>
		  </table>
		  </div>
		
		      <h3>The following lines will be added or updated.</h3>
		      Please verify that the lines to be added are new and 
		      the lines to be updated are the same as you intend to change.
		      <p>
        <table >
	<tr>
	<th style="width: 140px;" class="marker">Lines Added</th>
	<th style="width: 150px;" class="marker" >Lines Updated </th>
	</tr>
	</table>
			
			<div id="test" style="padding: 0; height: 200px; width: 290px;  overflow: scroll;border: 1px solid #5b53a6;">
			<table>
			<?php
			if($line_update_data !="") {
			  for ($i = 0; $i < max(count($line_insert_data),count($line_update_data)); $i++) {				
			?>
			
			<tr>
			<td style="width: 130px;">
			<?php echo $line_insert_data[$i] ?>
			</td> 
			<td style="width: 160px;">
			<?php echo $line_update_data[$i] ?>
			</td>
			<?php
			  }  /* end of for loop */
			}
			else {
				for ($i = 0; $i < count($line_insert_data); $i++) {
			?>
			
			<tr>
			<td style="width: 130px;">
			<?php echo $line_insert_data[$i] ?>
			</td> 
			<td style="width: 160px;">
			<?php echo "No Updates" ?>
			</td>
			<?php
				}/* end of for loop */
				}
				
			?>
			
			</table>
			</div>
			
			<?php
				echo " <b>Please fix these errors before you insert into/update the database </b><br/><br/>";
				exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
			}
			elseif ($cnt == 0) {
			  // No errors so far.
			  echo "<h3>The file is read as follows.</h3>\n";
			  echo "Breeding Program: $bp";
			  ?>
			  <p>		      		
		<table >
	<tr>
	<th style="width: 140px;" class="marker">Line Name</th>
	<th style="width: 150px;" class="marker">GRIN</th>
	<th style="width: 70px;" class="marker">Generation</th>
	<th style="width: 50px;" class="marker">Hardness</th>
	<th style="width: 50px;" class="marker">Color</th>
	<th style="width: 50px;" class="marker">Growth Habit</th>
	<th style="width: 100px;" class="marker">Species</th>
	<th style="width: 50px;" class="marker">Awned</th>
	<th style="width: 180px;" class="marker">Pedigree</th>
	<th style="width: 180px;" class="marker">Comments</th>
	</tr>
	</table>
		
		<div id="test" style="padding: 0; height: 200px; width: 800px;  overflow: scroll;border: 1px solid #5b53a6;">
			<table>
			<?php 
				for ($irow = $firstline+2; $irow <=$rows; $irow++)  {
				//Extract data.
				$line = strtoupper(trim($linedata['cells'][$irow][$columnOffsets['line_name']]));
				// Line Name is required.
				if (!empty($line)) {
				  $grin = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['grin']]),"\0..\37!@\177..\377");
				  $generation = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['generation']]),"\0..\37!@\177..\377");
				  $hardness = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['hardness']]),"\0..\37!@\177..\377");
				  $color = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['color']]),"\0..\37!@\177..\377");
				  $growth = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['growth_habit']]),"\0..\37!@\177..\377");
				  $species = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['species']]),"\0..\37!@\177..\377");
				  $species = preg_replace("/^a$/", "aestivum", $species);
				  $species = preg_replace("/^d$/", "durum", $species);
				  $awned = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['awned']]),"\0..\37!@\177..\377");
				  $pedstring=addcslashes(trim($linedata['cells'][$irow][$columnOffsets['pedigree']]),"\0..\37!@\177..\377");
				  $comments = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['comments']]),"\0..\37!@\177..\377");
				  /* For barley.
				   $enduse = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['end_use']]),"\0..\37!@\177..\377");
				   $rowtype = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['row_type']]),"\0..\37!@\177..\377");
				   $hull = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['hull']]),"\0..\37!@\177..\377");
				  */
				  ?>
			
				    <tr>
				       <td style="width: 120px;">
				       <?php echo $line ?></td> 
				       <td style="width: 120px;">
				       <?php echo $grin ?></td> 
				       <td style="width: 70px;">
				       <?php echo $generation ?></td> 
				       <td style="width: 50px;">
				       <?php echo $hardness ?></td> 
				       <td style="width: 50px;">
				       <?php echo $color ?></td> 
				       <td style="width: 50px;">
				       <?php echo $growth ?></td> 
				       <td style="width: 100px;">
				       <?php echo $species ?></td> 
				       <td style="width: 50px;">
				       <?php echo $awned ?></td> 
				       <td style="width: 180px;">
				       <?php echo $pedstring ?></td> 
				       <td style="width: 180px;">
				       <?php echo $comments ?></td> 
				       <!-- For barley.
				       <td style="width: 50px;">
				       <?php echo $enduse ?></td> 
				       <td style="width: 30px;">
				       <?php echo $rowtype ?></td> 
				       <td style="width: 60px;">
				       <?php echo $hull ?></td> 
				       -->
				       </tr>
				       <?php
				       } /* end of if (!empty($line))*/
				}  /* end of for loop */
			?>
			</table>
			</div>
		
			<h3>The following lines will be added or updated.</h3>
			    Please verify that the lines to be added are new and 
                            the lines to be updated are the same as you intend to change.
<p>
 <table >
	<tr>
	<th style="width: 140px;" class="marker">Lines Added</th>
	<th style="width: 150px;" class="marker" >Lines Updated </th>
	</tr>
	</table>
			
			<div id="test" style="padding: 0; height: 200px; width: 290px;  overflow: scroll;border: 1px solid #5b53a6;">
			<table>
			<?php
				if($line_update_data !="") 
			{
				for ($i = 0; $i < max(count($line_insert_data),count($line_update_data)); $i++)
				{
			?>
			
			<tr>
			<td style="width: 130px;">
			<?php echo $line_insert_data[$i] ?>
			</td> 
			<td style="width: 160px;">
			<?php echo $line_update_data[$i] ?>
			</td>
			<?php
				}/* end of for loop */
				}
				else
				{
				for ($i = 0; $i < count($line_insert_data); $i++)
				{
			?>
			
			<tr>
			<td style="width: 130px;">
			<?php echo $line_insert_data[$i] ?>
			</td> 
			<td style="width: 160px;">
			<?php echo "No Updates" ?>
			</td>
			<?php
				}/* end of for loop */
				}
				
			?>
			
			</table>
			</div>
				
    			<input type="Button" value="Accept" onclick="javascript: update_database('<?php echo $datafile?>','<?php echo $uploadfile ?>','<?php echo $username?>' )"/>
    			<input type="Button" value="Cancel" onclick="history.go(-1); return;"/>
    			
				<?php	}
   			}
				 else {
    				error(1,"There was an error uploading the file, please try again!");
							}
		}
	}
	
	} /* end of function type_Line_Name */
	
	private function type_Database() {
	global $config;
	include($config['root_dir'] . 'theme/admin_header.php');
	
	$datafile = $_GET['linedata'];
	$filename_old = $_GET['file_name'];
	$filename = $filename_old.rand();
	$username = $_GET['user_name'];
	
	$reader = & new Spreadsheet_Excel_Reader();
	$reader->setOutputEncoding('CP1251');
	$reader->read($datafile);
	$linedata = $reader->sheets[0];
	$cols = $reader->sheets[0]['numCols'];
	$rows = $reader->sheets[0]['numRows'];
	
	// Read the Breeding Program from row 3.
	if ($linedata['cells'][3][1] != "*Breeding Program Code") {
	  die("Row 3 must begin with '*Breeding Program Code'.");
	}
	$bp = $linedata['cells'][3][2];
	if (strlen($bp) != 3) {
	  die("Invalid or missing Breeding Program Code.");
	}

// 	$columnOffsets = array(
// 			       'line_name' => -1,
// 			       'species' => -1,
// 			       'growth_habit' => -1
// 			       'pedigree' => -1,
// 			       'comments' => -1
// 			       );
	

	/* Attempt to find each required column */
	// First, locate the header line.
	$firstline = 0;
	$header = array();
	for ($irow = 4; $irow <=$rows; $irow++) {
	  $teststr= addcslashes(trim($linedata['cells'][$irow][1]),"\0..\37!@\177..\377");
	  if (empty($teststr)){
	    break; 
	  } elseif (strtolower($teststr) =="line name") {
	    $firstline = $irow;
	    // read out header line
	    for ($icol = 1; $icol <= $cols; $icol++) {
	      $firstline = $irow;
	      $value = addcslashes(trim($linedata['cells'][$irow][$icol]),"\0..\37!@\177..\377");
	      $header[] = $value;
	    }
	    break 1;
	  }
	}
		
	foreach($header as $columnOffset => $columnName){ // loop through the columns in the header row
	  //clean up column name so that it can be matched
	  $columnName = strtolower(trim($columnName));
	  //break column title into pieces based on spaces and newlines
	  //$colpart= explode('\\n',$columnName);
	  //$colpart = implode(" ",$columnName);
	  $order = array("\n","\t"," ");
	  $replace = array(" ",'','');
	  $columnName = str_replace($order, $replace, $columnName);
			
	  // Determine the column offset of "Line Name"...
	  if (preg_match('/^\s*linename\s*$/is', trim($columnName)))
	    $columnOffsets['line_name'] = $columnOffset+1;
		
	  // !! add Aliases.
		
	  // Determine the column offset of "GRIN Accession"...
	  if (preg_match('/^\s*grinaccession\s*$/is', trim($columnName)))
	    $columnOffsets['grin'] = $columnOffset+1;
		
	  // Determine the column offset of "Pedigree"...
	  if (preg_match('/^\s*pedigree\s*$/is', trim($columnName)))
	    $columnOffsets['pedigree'] = $columnOffset+1;
		
	  // Determine the column offset of "Filial Generation"...
	  if (preg_match('/^\s*filialgeneration\s*$/is', trim($columnName)))
	    $columnOffsets['generation'] = $columnOffset+1;
		
	  // Determine the column offset of "Hard / Soft"...
	  if (preg_match('/^\s*hard\/soft\s*$/is', trim($columnName)))
	    $columnOffsets['hardness'] = $columnOffset+1;
		
	  // Determine the column offset of "Red / White"...
	  if (preg_match('/^\s*red\/white\s*$/is', trim($columnName)))
	    $columnOffsets['color'] = $columnOffset+1;
		
	  // Determine the column offset of "Spring / Winter / Facultative"...
	  //if (preg_match('/^\s*growthhabit\s*$/is', trim($columnName)))
	  if (preg_match('/^\s*spring\/winter\/facultative\s*$/is', trim($columnName)))
	    $columnOffsets['growth_habit'] = $columnOffset+1;

	  // Determine the column offset of "aestivum / durum"...
	  if (preg_match('/^\s*aestivum\/durum\s*$/is', trim($columnName)))
	    $columnOffsets['species'] = $columnOffset+1;
		
	  // Determine the column offset of "Awned / Awnless"...
	  if (preg_match('/^\s*awned\/awnless\s*$/is', trim($columnName)))
	    $columnOffsets['awned'] = $columnOffset+1;
		
	  // Determine the column offset of "Row Type"...
	  if (preg_match('/^\s*rowtype\s*$/is', trim($columnName)))
	    $columnOffsets['row_type'] = $columnOffset+1;
		
	  // Determine the column offset of "End Use"...
	  if (strpos($columnName,'use'))
	    $columnOffsets['end_use'] = $columnOffset+1;
				
	  // Determine the column offset of "Hull"...
	  if (preg_match('/^\s*hull\s*$/is', trim($columnName)))
	    $columnOffsets['hull'] = $columnOffset+1;

	  // Determine the column offset of "Comments"...
	  if (preg_match('/^\s*comments\s*$/is', trim($columnName)))
	    $columnOffsets['comments'] = $columnOffset+1;
	}

	//Ignore the next row after the header.  Or error.
	if ($linedata['cells'][$firstline+1][2] != "comma separated values") {
	  die("Row 2 must be the descriptions of the columns.  Please don't delete it.<br><br>"); 
	}
	for ($irow = $firstline+2; $irow <=$rows; $irow++)  {
	  //Extract data
	  $line = strtoupper(trim($linedata['cells'][$irow][$columnOffsets['line_name']]));
	  // Line Name is required.
	  if (!empty($line)) {
	  $grin = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['grin']]),"\0..\37!@\177..\377");
	  $generation = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['generation']]),"\0..\37!@\177..\377");
	  $hardness = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['hardness']]),"\0..\37!@\177..\377");
	  $color = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['color']]),"\0..\37!@\177..\377");
	  $growth = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['growth_habit']]),"\0..\37!@\177..\377");
	  $species = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['species']]),"\0..\37!@\177..\377");
	  $species = preg_replace("/^a$/", "aestivum", $species);
          $species = preg_replace("/^d$/", "durum", $species);
	  $awned = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['awned']]),"\0..\37!@\177..\377");
	  $pedstring=addcslashes(trim($linedata['cells'][$irow][$columnOffsets['pedigree']]),"\0..\37!@\177..\377");
	  $comments = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['comments']]),"\0..\37!@\177..\377");
	  /* For barley.
	   $enduse = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['end_use']]),"\0..\37!@\177..\377");
	   $rowtype = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['row_type']]),"\0..\37!@\177..\377");
	   $hull = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['hull']]),"\0..\37!@\177..\377");
	  */
				
	  //check if line is in database
	  $line_uid=get_lineuid($line);
	  if ($line_uid===FALSE) {
	    // Insert new line into database
	    // Required fields: species
	    if (empty($species)) {
	      die_nice("Field <b>species</b> is required, values a or d.");
	    }
	    //convert line name to upper case and replace spaces with an underscore
	    $line = strtoupper(str_replace(" ","_",$line));
	    $sql_beg = "INSERT INTO line_records (line_record_name,";
	    $sql_mid = "updated_on, created_on) VALUES('$line', ";
	    $sql_end = "NOW(),NOW())";
	    if (!empty($pedstring)) {
	      $sql_beg .= "pedigree_string,";
	      $pedstring = mysql_real_escape_string($pedstring);
	      $sql_mid .= "'$pedstring', ";
	    }
	    if (!empty($growth)) {
	      $sql_beg .= "growth_habit,";
	      $sql_mid .= "'$growth', ";
	    }
	    if (!empty($bp)) {
	      $sql_beg .= "breeding_program_code,";
	      $sql_mid .= "'$bp', ";
	    }
	    // For numbers, 0 is empty.
	    if (isset($generation) AND $generation != "") {
	      $sql_beg .= "generation,";
	      $sql_mid .= "'$generation', ";
	    }
	    if (!empty($hardness)) {
	      $sql_beg .= "hardness,";
	      $sql_mid .= "'$hardness', ";
	    }
	    if (!empty($color)) {
	      $sql_beg .= "color,";
	      $sql_mid .= "'$color', ";
	    }
	    if (!empty($species)) {
	      $sql_beg .= "species,";
	      $sql_mid .= "'$species', ";
	    }
	    if (!empty($awned)) {
	      $sql_beg .= "awned,";
	      $sql_mid .= "'$awned', ";
	    }
	    if (!empty($comments)) {
	      $sql_beg .= "description,";
	      $sql_mid .= "'$comments', ";
	    }
	    /* For barley.
	    if (!empty($rowtype)) {
	      $sql_beg .= "row_type,";
	      $sql_mid .= "$rowtype, ";
	    }
	    if (!empty($enduse)) {
	      $sql_beg .= "primary_end_use,";
	      $sql_mid .= "'$enduse', ";
	    }
	    if (!empty($hull)) {
	      $sql_beg .= "hull,";
	      $sql_mid .= "'$hull', ";
	    }
	    */
	    $sql = $sql_beg.$sql_mid.$sql_end;
	    $rlinsyn=mysql_query($sql) or errmsg($sql, mysql_error());
	    $line_uid = mysql_insert_id();

	    // Insert GRIN accession.
	    if (!empty($grin)) {
	      $sql = "insert into barley_pedigree_catalog_ref 
                (barley_pedigree_catalog_uid, line_record_uid, barley_ref_number, 
                updated_on, created_on) values ('2', '$line_uid', '$grin', NOW(),NOW())";
	      $res = mysql_query($sql) or errmsg($sql, mysql_error());
	    }
						
	  } elseif (count($line_uid)==1) { 
	    //update the line record
	    $line_uids = implode(",",$line_uid);
				
	    $sql_beg = "UPDATE LOW_PRIORITY line_records SET ";
	    $sql_mid = "";
	    $sql_end = "updated_on=NOW() WHERE line_record_uid = '$line_uids'";
	    if (!empty($pedstring)) {
	      $pedstring = mysql_real_escape_string($pedstring);
	      $sql_mid .= "pedigree_string = '$pedstring', ";
	    }
	    if (!empty($growth)) {
	      $sql_mid .= "growth_habit='$growth', ";
	    }
	    if (!empty($bp)) {
	      $sql_mid .= "breeding_program_code='$bp', ";
	    }
	    // For numbers, 0 is empty.
	    if (isset($generation) AND $generation != "") {
	      $sql_mid .= "generation = '$generation', ";
	    }
	    if (!empty($hardness)) {
	      $sql_mid .= "hardness = '$hardness', ";
	    }
	    if (!empty($color)) {
	      $sql_mid .= "color = '$color', ";
	    }
	    if (!empty($species)) {
	      $sql_mid .= "species = '$species', ";
	    }
	    if (!empty($awned)) {
	      $sql_mid .= "awned = '$awned', ";
	    }
	    if (!empty($comments)) {
	      $sql_mid .= "description = '$comments', ";
	    }
	    /* For barley.
	    if (!empty($rowtype)) {
	      $sql_mid .= "row_type='$rowtype', ";
	    }
	    if (!empty($enduse)) {
	      $sql_mid .= "primary_end_use='$enduse', ";
	    }
	    if (!empty($hull)) {
	      $sql_mid .= "hull='$hull', ";
	    }
	    */
	    $sql = $sql_beg.$sql_mid.$sql_end;

	    $rlinsyn=mysql_query($sql) or errmsg($sql, mysql_error());

	    // Update GRIN accession.
	    if (!empty($grin)) {
	      // Is there already a value?
	      $sql = "select barley_pedigree_catalog_ref_uid from barley_pedigree_catalog_ref
                WHERE barley_pedigree_catalog_uid=2
                AND line_record_uid = '$line_uids'";
	      $res = mysql_query($sql) or errmsg($sql, mysql_error());
	      if (mysql_num_rows($res) > 0) {
		$sql = "update barley_pedigree_catalog_ref set barley_ref_number = '$grin',
                updated_on=NOW() WHERE barley_pedigree_catalog_uid=2 
                AND line_record_uid = '$line_uids'";
		$res = mysql_query($sql) or errmsg($sql, mysql_error());
	      }
	      else {
		$sql = "insert into barley_pedigree_catalog_ref 
                (barley_pedigree_catalog_uid, line_record_uid, barley_ref_number, 
                updated_on, created_on) values ('2', '$line_uids', '$grin', NOW(),NOW())";
		$res = mysql_query($sql) or errmsg($sql, mysql_error());
	      }
	    }

						
	  }else {
	    $line_uids = implode(",",$line_uid);
	    error(0, "$line is found in multiple records($line_uids), in line record table, please fix");
	  }

				
				if (!empty($CAPcode)){
				if ($CAPcode == $line)
				{
					continue; /* for 2008 data*/
				}
				else
				{				
					$linesyn_uid = get_lineuid($CAPcode);
					if ($linesyn_uid===FALSE) {
						// Insert CAPentry code as a synonym into database
						$sql = "INSERT INTO line_synonyms (line_record_uid,line_synonym_name, updated_on, created_on)
										VALUES($line_uid, '$CAPcode', NOW(),NOW())";
					} elseif ((count($linesyn_uid)==1)AND($linesyn_uid!=$line_uid)){
						$linesyn_uids = implode(",",$line_uid);
						
						$sql = mysql_query("SELECT line_record_name from line_records where line_record_uid in ($linesyn_uids)");
						
						$row = mysql_fetch_array($sql);
						
						$line_name = $row['line_record_name'];
						
						
						error(0, "$CAPcode is linked to a diffent line ($line_name), in line record table, please fix");
					} elseif (count($linesyn_uid)>1) {
						$linesyn_uids = implode(",",$line_uid);
						error(0, "$CAPcode is linked multiple lines ($linesyn_uids), in line record table, please fix");
					}
				}
				}
	  } /* end of if (!empty($line)) */
	}
		 
	echo "<b>The data was inserted/updated successfully.</b>";

	$sql = "INSERT INTO input_file_log (file_name,users_name) VALUES('$filename', '$username')";
	$lin_table=mysql_query($sql) or die(mysql_error());


	$footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
		
	} /* end of function type_Database */

} /* end of class */

?>
