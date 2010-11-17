<?php
include '../includes/bootstrap.inc';
connect();
include("../theme/normal_header.php");
?>
<h2>Import Means Data</h2>
<div class="section">
<p>
<?php

set_error_handler("my_error_handler");

// The higher the debug level, the more output
define('DEBUG', false);
define('DEBUG2', false);

$validMimeTypes = "application/msexcel,application/x-msexcel,application/x-ms-excel,application/vnd.ms-excel,application/x-excel,application/x-dos_ms_excel,application/xls,application/x-xls,zz-application/zz-winassoc-xls";

/* The error codes are powers of two.
 * This allows one to call multiple errors by adding their error codes.
 */
$error = array(
	1 => 'cannot open source file',
	2 => 'source file is empty',
	4 => 'source file is missing a required column',
	8 => 'invalid source file',
	16 => 'the specified breading program does not exist'
);

/**
 * Returns the proper error message(s) for the specified code
 *
 * @param integer $code the error code
 * @return string the error message(s)
 */
function getErrorMsg($code) {
  global $error;

  $error_msg = "";
  $errorCodes = array_keys($error);
  while (!empty($errorCodes))
  {
    $errorCode = array_pop($errorCodes);
    if ($errorCode <= $code){
      $code -= $errorCode;
      $error_msg .= "\nError ({$errorCode}): {$error[$errorCode]}.";
    }
  }
  return $error_msg."\n";
}

include(realpath('../lib/Spyc/').'/spyc.php'); // include the YAML parser
$config = Spyc::YAMLLoad(realpath('config/').'/phenotypes.yml'); // parse the config file
include(realpath('../lib/Excel/').'/reader.php'); // include the Excel spreadsheet parser
$reader= new Spreadsheet_Excel_Reader();
$reader->setOutputEncoding('CP1251');

/**
 * Returns the html code for a file upload form
 *
 * @return string the html code for a file upload form
 * @author Gavin Monroe
 */
function doHTMLFileUploadForm()
{
  return <<< HTML
  <form action="{$_SERVER['PHP_SELF']}" method="POST" enctype="multipart/form-data">
    <label for="file" style="display: block">File Name:
      <input type="file" name="file" id="file" />
    </label>
    <input type="submit" name="submit" value="Submit" />
  </form>
HTML;
}


if (!isset($_POST['submit']) || !isset($_FILES['file']))
{
	echo doHTMLFileUploadForm();
	echo "<br /><br /><br /><br /><br /><br /></p></div></div>";
	include "../theme/footer.php";
	die();

}
elseif ($_FILES['file']['error'] > 0)
{
  die("<div class=\"form_error\">Error:".$_FILES['file']['error'].doHTMLFileUploadForm());
}
elseif (!stripos($validMimeTypes, $_FILES['file']['type']))
{
	echo doHTMLFileUploadForm();
	trigger_error(getErrorMsg(8), E_USER_ERROR);
}
else
{
	$filename = $_FILES['file']['tmp_name'];
	echo doHTMLFileUploadForm();
}


//$filename = 'OR06Corvallis_mn.xls';
$reader->read($filename); // parse the excel spreadsheet

// If the spreadsheet is empty...
if ($reader->sheets[0]['numRows'] == 0){
  trigger_error(getErrorMsg(1), E_USER_ERROR);
}

/*
 * The following code allows the curator to put the columns in any order.
 * It also allows him/her to supply useless columns
 */

// These are the required columns (-1 means that the column has not been found).
$columnOffsets = array(
  'breeding_program' => -1,
  'year' => -1,
  'trial_code' => -1,
  'trial_entry_no' => -1,
  'line_name' => -1,
  'pedigree' => -1,
  'growth_habit' => -1,
  'row_type' => -1,
  'end_use' => -1
);

/* Attempt to find each required column */
foreach($reader->sheets[0]['cells'][1] as $columnOffset => $columnName){ // loop through the columns in the header row

	// DEBUG
	if (DEBUG2) echo "\n\$columnOffset = ".$columnOffset." => \$columnName = ".$columnName;

	// Determine the column offset of "Breeding Program"...
	if (preg_match('/^\s*breeding\s*program\s*$/is', $columnName))
		$columnOffsets['breeding_program'] = $columnOffset;

	// Determine the column offset of "Year"...
	if (preg_match('/^\s*year\s*$/is', $columnName))
		$columnOffsets['year'] = $columnOffset;

	// Determine the column offset of "Trial Code"...
	if (preg_match('/^\s*trial\s*code\s*$/is', $columnName))
		$columnOffsets['trial_code'] = $columnOffset;

	// Determine the column offset of "Trial Entry No."...
	if (preg_match('/^\s*trial\s*entry\s*no\.*\s*$/is', $columnName))
		$columnOffsets['trial_entry_no'] = $columnOffset;

	// Determine the column offset of "Line Name"...
	if (preg_match('/^\s*line\s*name\s*$/is', $columnName))
		$columnOffsets['line_name'] = $columnOffset;

	// Determine the column offset of "Pedigree"...
	if (preg_match('/^\s*pedigree\s*$/is', $columnName))
		$columnOffsets['pedigree'] = $columnOffset;

	// Determine the column offset of "Growth Habit"...
	if (preg_match('/^\s*growth\s*habit\s*$/is', $columnName))
		$columnOffsets['growth_habit'] = $columnOffset;

	// Determine the column offset of "Row Type"...
	if (preg_match('/^\s*row\s*type\s*$/is', $columnName))
		$columnOffsets['row_type'] = $columnOffset;

	// Determine the column offset of "End Use"...
	if (preg_match('/^\s*end\s*use\s*$/is', $columnName))
		$columnOffsets['end_use'] = $columnOffset;
}

/* Now check to see if any required columns weren't found */
if (in_array(-1, $columnOffsets)) {
	trigger_error(getErrorMsg(4), E_USER_ERROR);
}

if (DEBUG||DEBUG2) echo "<div><pre>\$columnOffsets = ".print_r($columnOffsets, true)."</pre></div>";



/* Determine the column offset of each known phenotype */
$phenotypes = array();
foreach ($config['phenotypes'] as $phenotype){
	foreach ($reader->sheets[0]['cells'][1] as $column_offset => $column_name){
		$column_name = str_replace("\n",' ',$column_name);
		if (preg_match("/^{$phenotype['name']}.*{$phenotype['dbunits']}.*$/i", $column_name)){
			$phenotype['column_offset'] = $column_offset;
			$phenotypes[] = $phenotype;
		}
	}
}

if (DEBUG||DEBUG2) echo "<div><pre>\$phenotypes = ".print_r($phenotypes, true)."</pre></div>";

/* Remove the header row */
array_shift($reader->sheets[0]['cells']);



/*
 * At this point, the script know the locations of all the required columns and
 * some or all of the optional phenotypes
 */




//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
// Build an object model for this spreadsheet
//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>

$trial_codes = array();
foreach ($reader->sheets[0]['cells'] as $cell){ /* for each row */
	$trial_codes[] = $cell[$columnOffsets['trial_code']];
}
$trial_codes = array_unique($trial_codes); /* remove duplicates */
/* sometimes NULL finds its way into the array, so remove it */
unset($trial_codes[array_search(NULL, $trial_codes)]);



$rowCounter = 0;



/*
 * Handle the first row
 */

$firstRow = $reader->sheets[0]['cells'][$rowCounter];

/* DEBUG */
if (DEBUG2) echo "<div><pre>\$currentRow = ".print_r($current_row, true)."</pre></div>";

// Get the breeding program
$breedingProgram =& new my_breeding_programs(my_breeding_programs_peer::get_by_breeding_programs_name($firstRow[$columnOffsets['breeding_program']]));
if ($breedingProgram == null) // The specified breeding program doesn't exist
{
  trigger_error(getErrorMsg(16), E_USER_ERROR);
}
$newDataSetName = basename($_FILES['file']['name'], ".xls"); // The file name is the dataset name
$datasets =& $breedingProgram->get_datasets(); // Get the breading program's current datasets

// Add the new dataset
$dataset =& new my_datasets(new datasets(null, $breedingProgram->get_breeding_programs_uid(), $newDataSetName, null, null, null, null));

$experiments = array();
foreach ($trial_codes as $trial_code){
	// Add a new experiment
	$experiments[$trial_code] =& new my_experiments(
		new experiments(
			$experiment_uid			= null,
			$experiment_type_uid	= null,
			$datasets_uid			= null,
			$experiment_name		= $trial_code,
			$experiment_year 		= null,
			$planting_date 			= null,
			$seeding_rate 			= null,
			$harvest_date 			= null,
			$experiment_design 		= null,
			$number_replications 	= null,
			$plot_size 				= null,
			$harvest_area 			= null,
			$irrigation 			= null,
			$collect_site_name 		= null,
			$longitude 				= null,
			$latitude 				= null,
			$other_remarks 			= null,
			$updated_on 			= null,
			$created_on 			= null
		)
	);
}

// Loop through the rows of the spreadsheet
foreach ($reader->sheets[0]['cells'] as &$row)
{
	$lineToAdd = null;	// The line to add to an experiment

	/*
	 * TODO: Allow certain incomplete rows
	 */
	if (isCompleteRow($row)){
		$lineToAdd = new my_line_records(
			new line_records(
				$line_record_uid 				= null,
				$barley_pedigree_catalog_uid 	= null,
				$taxa_uid 						= null,
				$line_record_name 				= $row[$columnOffsets['line_name']],
				$synonym 						= null,
				$other_number 					= null,
				$variety 						= $columnOffsets['growth_habit'],
				$pedigree_string 				= $columnOffsets['pedigree'],
				$barley_type 					= null,
				$origin 						= null,
				$row_type 						= $columnOffsets['row_type'],
				$primary_end_use 				= $columnOffsets['end_use'],
				$record_status 					= null,
				$breed_year 					= null,
				$note 							= null,
				$updated_on 					= null,
				$created_on 					= null
			)
		);

		// Attach the phenotype data to the line
		foreach ($phenotypes as $phenotype){
			$phenotypeToAdd =& new my_phenotype_data(
				new phenotype_data(
					$phenotype_data_uid = null,
					$phenotype_uid = null,
					$tht_base_uid = null,
					$phenotype_data_name = $phenotype['dbname'],
					$value = $row[$phenotype['column_offset']],
					$recording_data = null,
					$updated_on = null,
					$created_on = null
				)
			);
			$lineToAdd->add_phenotype_data($phenotypeToAdd);
		}

		$retVal = $experiments[$row[$columnOffsets['trial_code']]]->add_line($lineToAdd);
		if ($retVal == false){  // The line already exists in the experiment
			// do nothing
		}
	}
}

/*foreach($experiments as &$experiment){
	$experiment->attach_phenotype_data();
}*/

$dataset->set_experiments(array_values($experiments));  // add the experiments to the dataset

$datasets[] =& $dataset; // add the dataset to the breeding program's datasets
$breedingProgram->set_datasets($datasets);  // update the breeding program's datasets


if (DEBUG) echo "<div><pre>\$breedingProgram = ".print_r($breedingProgram, true)."</pre></div>";

$bp_name = $breedingProgram->get_breeding_programs_name();
$ds_name = "";
$e_name = "";
$l_name = "";
$experiments = array();
$lines = array();
$phenotypes = array();

$output = "
		</div>
	<br />
	<br />
	<style>
		.red{color:red}
	</style>
	<h2>Result</h2>
	<div class=\"section\">
		<div style=\"border: 3px solid green; background: lightgreen; padding: 10px; margin: 10px;\">
			After reviewing your file, we have determined that it can be stored in our database.
			Please verify that the information below is correct.
		</div>
		<div style=\"border: 3px solid blue; background: lightblue; padding: 10px; margin: 10px;\">
			<pre style=\"font-size: 9pt; font-family: arial, helvetica, sans-serif;\">
> Breading Program: <span class=\"red\"><b>{$bp_name}</b></span>\n";
foreach ($datasets as $dataset){
	$ds_name = $dataset->get_dataset_name();
	$output .= "    > Dataset: <span class=\"red\"><b>{$ds_name}</b></span>\n";

	$experiments = $dataset->get_experiments();
	foreach ($experiments as $experiment){
		$e_name = $experiment->get_experiment_name();
		$output .= "        > Experiment: <span class=\"red\"><b>{$e_name}</b></span>\n";

		$lines = $experiment->get_lines();
		foreach ($lines as $line){
			$l_name = $line->get_line_record_name();
			$output .= "            > Line: <span class=\"red\"><b>{$l_name}</b></span> (";

			$phenotypes = $line->get_phenotype_datas();
			foreach ($phenotypes as $phenotype)
			{
				$output .= "<u>".$phenotype->get_phenotype_data_name()."</u>: ".$phenotype->get_value()."; ";
			}
			$output = substr($output, 0, -2);
			$output .= ")\n";
		}
	}
}
$output .= "</pre></div>";
echo $output;




//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
// Helper functions
//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>

/**
 * Determines if a row of data is complete.
 *
 * This function does not determine if a row is valid.
 *
 * @param array $row the row in question
 * @return boolean TRUE if the row is complete, FALSE otherwise
 * @author Gavin Monroe <gemonroe@iastate.edu>
 */
function isCompleteRow(&$row){
global $columnOffsets, $phenotypes;
	$minNumRows = count($columnOffsets) + count($phenotypes);
	if (count($row) < $minNumRows){
		return false;
	}
	/* At this point, the row has the minimum number of rows */
	foreach ($columnOffsets as &$columnOffset){
		if (!isset($row[$columnOffset])){
			return false;
		}
	}
	/* At this point, the row has values for all the required columns */
	foreach ($phenotypes as &$phenotype){
		if (!isset($row[$phenotype['column_offset']])){
			return false;
		}
	}
	/* At this point, the row has values for all of the phenotypes */
	return true;
}


function my_error_handler($errno, $errstr, $errfile, $errline){
	switch($errno){
		case E_USER_ERROR:
			$output = "
				</div><br /><br />
				<h2>Result</h2>
				<div class=\"section\">
				<div style=\"border: 3px solid red; background: pink; padding: 10px; margin: 10px;\">
				<img style=\"float: left; margin: 0 10px 10px 0\" src=\"images/error.png\" />
				After reviewing your file, we have determined that it cannot be stored in our database.
				The application provided the following error message: <br /><br />
				<pre style=\"background: red; color: white; padding: 10px; font-size: 16px; clear: both;\">{$errstr}</pre>
				</div></p></div></div>
				";
			echo $output;
			include "../theme/footer.php";
			exit(1);
			break;
	}
}


?>
</p>
</div>
</div>

<?php include "../theme/footer.php";?>