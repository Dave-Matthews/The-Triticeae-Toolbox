<?php

require 'config.php';
//require_once("../includes/common_import.inc");
/*
 * Logged in page initialization
 */
include($config['root_dir'] . 'includes/bootstrap.inc');
//include($config['root_dir'] . 'includes/common_import.inc');

require_once("../lib/Excel/reader.php"); // Microsoft Excel library

connect();
loginTest();

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
			case 'type1experiments':
				$this->type1_experiments(); /* display experiments */
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

		echo "<h2> Enter/Update Line Information: Validation</h2>"; 
		
			
		$this->type_Line_Name();

		$footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
	}
	
	
	private function type_Line_Name()
	{
	?>
	<script type="text/javascript">
	
	
	</script>
	
	<style type="text/css">
			th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
			table {background: none; border-collapse: collapse}
			td {border: 0px solid #eee !important;}
			h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
		</style>
		
		
		
<?php




 $row = loadUser($_SESSION['username']);
	
	ini_set("memory_limit","24M");
	
	$username=$row['name'];
	
	$tmp_dir="curator_uploads/tmpdir_".$username."_".rand();
	umask(0);
	
	if(!file_exists($tmp_dir) || !is_dir($tmp_dir)) {
		mkdir($tmp_dir, 0777);
	}
	$target_path=$tmp_dir."/";
	if ($_FILES['file']['name'] == ""){
		error(1, "No File Uploaded");
		print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
	}
	else {
		
		$uploadfile=$_FILES['file']['name'];
		$uftype=$_FILES['file']['type'];
		if (strpos($uploadfile, ".xls") === FALSE) {
			error(1, "Expecting an Excel file. <br> The type of the uploaded file is ".$uftype);
			print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
		}
		else {
			if(move_uploaded_file($_FILES['file']['tmp_name'], $target_path.$uploadfile)) 
			{


    			echo "<h2>The file ".basename( $uploadfile)." has been verified. Upload to database?</h2>\n";
    			
    			
    			
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
		/*
 * The following code allows the curator to put the columns in any order.
 * It also allows him/her to supply useless columns
 */

// These are the required columns (-1 means that the column has not been found).
		$columnOffsets = array(
			'CAP_entry_code' => -1,
			'breeding_program' => -1,
			//'origin' => -1,
			'line_name' => -1,
			'pedigree' => -1,
			'growth_habit' => -1,
			'row_type' => -1,
			'end_use' => -1,
			'hull' => -1,
			'comments' => -1
		);

		/* Attempt to find each required column */
		// First, locate first line of data in the file
		$firstline = 0;
		$header = array();
		for ($irow = 1; $irow <=$rows; $irow++) {
			$teststr= addcslashes(trim($linedata['cells'][$irow][1]),"\0..\37!@\177..\377");
			//echo $teststr."  ";
			if (empty($teststr)){
			   break; 
			} elseif (strtolower($teststr) =="order") {
				$firstline = $irow;
				// read out header line
				for ($icol = 1; $icol <= $cols; $icol++) {
					$firstline = $irow;
					$value = addcslashes(trim($linedata['cells'][$irow][$icol]),"\0..\37!@\177..\377");
					$header[] = $value;
					//if (DEBUG2) echo "row ".$irow." col ".$icol." name ".$value."\n";
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
			// DEBUG
			//if (DEBUG2) echo "\n\$columnOffset = ".$columnOffset." => \$columnName = ".$columnName;
			// Determine the column offset of "CAP_entry_code"...
			if (strpos( $columnName,'code'))
				$columnOffsets['CAP_entry_code'] = $columnOffset+1;
				
			// Determine the column offset of "Breeding Program"...
			if (preg_match('/^\s*breedingprogram\s*$/is', trim($columnName)))
				$columnOffsets['breeding_program'] = $columnOffset+1;
		
			// Determine the column offset of "origin"...
			if (preg_match('/^\s*origin\s*$/is', trim($columnName)))
				$columnOffsets['origin'] = $columnOffset+1;
		
			// Determine the column offset of "Line Name"...
			if (preg_match('/^\s*linename\s*$/is', trim($columnName)))
				$columnOffsets['line_name'] = $columnOffset+1;
		
			// Determine the column offset of "Pedigree"...
			if (preg_match('/^\s*pedigree\s*$/is', trim($columnName)))
				$columnOffsets['pedigree'] = $columnOffset+1;
		
			// Determine the column offset of "Growth Habit"...
			if (preg_match('/^\s*growthhabit\s*$/is', trim($columnName)))
				$columnOffsets['growth_habit'] = $columnOffset+1;
		
			// Determine the column offset of "Row Type"...
			if (preg_match('/^\s*rowtype\s*$/is', trim($columnName)))
				$columnOffsets['row_type'] = $columnOffset+1;
		
			// Determine the column offset of "End Use"...
			if (strpos($columnName,'use'))
				$columnOffsets['end_use'] = $columnOffset+1;
				
			// Determine the column offset of "hull"...
			if (preg_match('/^\s*hull\s*$/is', trim($columnName)))
				$columnOffsets['hull'] = $columnOffset+1;
				// Determine the column offset of "hull"...
			if (preg_match('/^\s*comments\s*$/is', trim($columnName)))
				$columnOffsets['comments'] = $columnOffset+1;
		}
		//if (DEBUG2) print_r($columnOffsets);
		/* Now check to see if any required columns weren't found */
		if (in_array(-1, $columnOffsets)) {
			echo("required columns were not found in datafile <br/><br/>" );
			print_r($columnOffsets);
			//print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
			exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
		}
		
	//	if (DEBUG||DEBUG2) echo "<div><pre>\$columnOffsets = ".print_r($columnOffsets, true)."</pre></div>";

				
				/* my insert update script goes here */
				
				
				
				
				
				
				
				
				$icnt= 0;
				$cnt = 0;
				$line_inserts_str = "";
				$line_uid = "";
				$line_uids = "";
				$line_uids_multiple = "";
				$line_uid_Synonyms = "";
				$CAPcode_Synonyms = "";
				$linesyn_uids_mismatch = "";
				$linesyn_uids_multiple = "";
				
				
				
				
				
				
				
				
				function get_lineuid ($line) {
               // find line name list and group it into th proper experiment
                // If the name does not work, also check versions with spaces removed
                // and spaces replaced by underscores
                $line_nosp = str_replace(" ","",$line);
                $line_us = str_replace("_","",$line);
                $line_hyp = str_replace("-","",$line);
                $line_sql = mysql_query("SELECT line_record_uid AS lruid
                    FROM line_records
                    WHERE line_record_name = '$line'
                        OR line_record_name = '$line_nosp'
                        OR line_record_name = '$line_us'
                        OR line_record_name = '$line_hyp'
                        OR CAP_entry_code = '$line'
                        OR CAP_entry_code = '$line_nosp'
                        OR CAP_entry_code = '$line_us'");

                
                if (mysql_num_rows($line_sql)==0)  {
                    //echo "Line ".$line." ".$line_us.$line_hyp.$line_nosp.$line_sql." \n";
                    $line_sql = mysql_query("SELECT line_record_uid AS lruid
                        FROM line_synonyms
                        WHERE line_synonym_name = '$line'
                            OR line_synonym_name = '$line_nosp'
                            OR line_synonym_name = '$line_us'
                            OR line_synonym_name = '$line_hyp'");
                    
                    if (mysql_num_rows($line_sql)==0) {
                       // echo "Line ".$line." is not in the line record or synonym table\n";
                        return FALSE;
                    }
                }


                while ($row = mysql_fetch_array($line_sql, MYSQL_ASSOC)) {
                    $result[] = $row["lruid"];  
                }
            return $result;


}
				
				
				
				
				
				
			for ($irow = $firstline+1; $irow <=$rows; $irow++)  {
				//Extract data
				//if ($cnt>4) exit;
				$line = strtoupper(trim($linedata['cells'][$irow][$columnOffsets['line_name']]));
				$CAPcode = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['CAP_entry_code']]),"\0..\37!@\177..\377");
				$pedstring=addcslashes(trim($linedata['cells'][$irow][$columnOffsets['pedigree']]),"\0..\37!@\177..\377");
				$comments = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['comments']]),"\0..\37!@\177..\377");
				$growth = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['growth_habit']]),"\0..\37!@\177..\377");
				$rowtype = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['row_type']]),"\0..\37!@\177..\377");
				$enduse = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['end_use']]),"\0..\37!@\177..\377");
				$bp = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['breeding_program']]),"\0..\37!@\177..\377");
				$hull = addcslashes(trim($linedata['cells'][$irow][$columnOffsets['hull']]),"\0..\37!@\177..\377");
			
				//echo " CAPcode is ". $CAPcode ."<br/>";
			
				
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
						$line_inserts_str .= implode(",",$line_inserts);
						
					//	echo " line inserts are" . $line_inserts_str;
						
				} 
				
				elseif (count($line_uid)==1) 
				{ 
						//update the line record, everything looks okay
						
						
						$line_uids[$icnt] = implode(",",$line_uid);
						
						$icnt++;
						
						
						
					//echo " updated lines Uid's  are". $line_uids[$icnt];
						
												
						//if (DEBUG2) echo $line." will be updated"."\n";
						
				}
				
				else {
						$line_uids_multiple .= implode(",",$line_uid);
						
					//	echo " line in multiple records". $line_uids_multiple;
						$cnt++; /* if this counter is not 0 then no accept option is displayed*/
						
						error(0, "$line is found in multiple records ($line_uids_multiple), in line record table, please fix");
				}
				
				
				if (!empty($CAPcode)){
				
				
					$linesyn_uid = get_lineuid($CAPcode);
					echo " synonym lines Uid's  are". $linesyn_uid;
					if ($linesyn_uid===FALSE) {
					
					// Insert CAPentry code as a synonym into database
					
						$line_uid_Synonyms[] = $line_uid;
						$line_uid_Synonyms .= implode(",",$line_uid_Synonyms);
						$CAPcode_Synonyms[] = $CAPcode;
						$CAPcode_Synonyms .= implode(",",$CAPcode_Synonyms);
						
						echo " line uid's inserting into line synonyms". $line_uid_Synonyms;
						
					} 
					elseif ((count($linesyn_uid)==1)AND($linesyn_uid!=$line_uid))
					{
						print_r(array_diff($linesyn_uid,$line_uid));
					//	echo " line uid here in Cap code is ". $line_uid;
						$linesyn_uids_mismatch .= implode(",",$line_uid);
						
					//	var_dump($linesyn_uids_mismatch);
						echo " line syn mis match"."  ".$linesyn_uids_mismatch;
					//	echo $CAPcode."is linked to a diffent line".$linesyn_uids_mismatch."in line record table, please fix";
						$cnt++; /* if this counter is not 0 then no accept option is displayed*/
						error(0, "$CAPcode is linked to a diffent line $linesyn_uids_mismatch, in line record table, please fix");
					} 
					elseif (count($linesyn_uid)>1) 
					{
						$linesyn_uids_multiple .= implode(",",$linesyn_uid);
						//echo $CAPcode."is linked multiple lines".$linesyn_uids_multiple."in line record table, please fix";
						$cnt++;/* if this counter is not 0 then no accept option is displayed*/
						error(0, "$CAPcode is linked to multiple lines ($linesyn_uids_multiple), in line record table, please fix");
					}
				}

				//if (DEBUG2) $cnt++;
			}
			
			$line_updates =implode(",",$line_uids);
			//echo $line_updates;
			// get line names
			$line_sql = mysql_query("SELECT line_record_name as name
                        FROM line_records
                        WHERE line_record_uid IN ($line_updates)");

           while ($row = mysql_fetch_array($line_sql, MYSQL_ASSOC)) {
                    $line_update_names[] = $row["name"];
					//echo "line name ".$row["name"];
                }

				
			if ($cnt != 0)
			{
				$line_insert_data = explode(",",$line_inserts_str);
				$line_update_data = $line_update_names;
			?>
		<style type="text/css">
                   table.marker
                   {background: none; border-collapse: collapse}
                    th.marker
                    { background: #5b53a6; color: #fff; padding: 5px 0; border: 0; }
                    
                    td.marker
                    { padding: 5px 0; border: 0 !important; }
                </style>
		
			
			<h2>Line Upload Details</h2>
 <table >
	<tr>
	<th style="width: 140px;" class="marker">Lines Inserted</th>
	<th style="width: 150px;" class="marker" >Line's Updated </th>
	</tr>
	</table>
			
			
			<div id="test" style="padding: 0; height: 200px; width: 290px;  overflow: scroll;border: 1px solid #5b53a6;">
			<table>
			<?php
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
			?>
			
			</table>
			</div>
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			<?php
				echo " <b>Please fix these errors before you insert into/update the database </b><br/><br/>";
				
				exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
			}
			elseif ($cnt == 0)
				{
    			print "<input type=\"Button\" value=\"Accept\" onClick=\"javascript: update_database(this.options)\">";
    			print "<input type=\"Button\" value=\"Cancel\" onClick=\"history.go(-1); return;\">";
    			}
    			
   			}
				 
				 else {
    				error(1,"There was an error uploading the file, please try again!");
							}
							
				
		}
		
		
		
		
		
		
		
		
	
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	} /* end of type_GenoType_Display function*/
	
	
	
	
	
	
	

} /* end of class */

?>
