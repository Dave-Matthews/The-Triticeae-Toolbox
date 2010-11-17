<?

require 'config.php';
/*
 * Logged in page initialization
 */
include($config['root_dir'] . 'includes/bootstrap.inc');

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

		echo "<h2> New Lines Validation</h2>"; 
		
			
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
		
		
		
<?
require_once("../lib/Excel/reader.php"); // Microsoft Excel library
 $row = loadUser($_SESSION['username']);
	
	ini_set("memory_limit","24M");
	
	$username=$row['name'];
	
	$tmp_dir="uploads/tmpdir_".$username."_".rand();
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
			if(move_uploaded_file($_FILES['file']['tmp_name'], $target_path.$uploadfile)) {


    			echo "<h2>The file ".basename( $uploadfile)." has been uploaded. </h2>\n";
    			
    			
    			
    			/* start reading the excel */
		
		
		$datafile = $target_path.$uploadfile;
		echo "file name is ". $uploadfile; 
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
			echo $teststr."  ";
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
			echo("required columns were not found in datafile" );
		}
		
	//	if (DEBUG||DEBUG2) echo "<div><pre>\$columnOffsets = ".print_r($columnOffsets, true)."</pre></div>";


    			
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
    				error(1,"There was an error uploading the file, please try again!");
			}
		}
		
		
		
		
		
		
		
		
	
	}
	$this->fileDataInput_withkeys($datafile);
	
	} /* end of type_GenoType_Display function*/
	
	function fileDataInput_withkeys($filename) {
        /* Part 1: read in header data*/
	//include paths.inc;
//	$filename = $dataimportpath.$filename;
        
				echo " testing************";
				$handle = fopen($filename, "r");
        
	
	//2. read in header
	$data = fgetcsv($handle, 0, "\t");
        $keys = $data;
        
         $mat_uid = array_combine($keys,$mat_uid);
        
        // 3. Read in data from file
	$cnt = 0;
        while (($data = fgetcsv($handledata, 0, "\t")) !== FALSE) {
                $new_row = array_combine($keys,$data);
		if ($cnt ==0){
		    $data_matrix = $new_row;
		} else {
		    $data_matrix = array_push($data_matrix,$new_row);
		}
        }

  echo " dump of th edata matrix".var_dump($data_matrix);

	//return($data_matrix);
  }
	
	
	

} /* end of class */

?>