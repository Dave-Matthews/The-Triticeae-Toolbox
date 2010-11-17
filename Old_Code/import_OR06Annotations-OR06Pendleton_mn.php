<?php
/**
 * This script imports annotations and means files
 *
 * This script usual a semi-automatic method of importing annotations and means
 * files into the THT database. With a little elbow grease, you can modify this
 * script to import the a particular means file into the database.
 *
 * It might be best to simply copy this file multiple times for each
 * configuration of it.
 *
 * @author Gavin Monroe
 */

/*
 * The information below is particular to the files you are processing
 */
define("DATASETNAME", "OR06");					// give the dataset a name
define("DATASETDATE", "2006-04-24 00:00:00");	// give the dataset a timestamp
define("ANNOTATIONSFILE", "OR06Annotations");	// the name of the annotations file without the file extension (must be in this directory)
define("SOURCEFILE", "OR06Pendleton_mn");		// the name of the meanse file without the file extension (must be in this directory)
define("EXPERIMENT_LOCATION", "Pendleton, Oregon");	// the location from which the data was taken
//define("EXPERIMENT_LOCATION", "Corvallis, Oregon");		// the location from which the data was taken

/*
 * Database configuration
 */
define("DBHOST", "localhost");
define("DBUSER", "yhames04");
define("DBPASS", "gdcb07");
define("DBNAME", "sandbox_yhames04_dev");



/**
 * Columns in the means file
 *
 * Tells the script which column is which. (Starting at 1)
 */
define("COL_CAPENTRYCODE", 1);		// required
define("COL_CAPENTRYNO", 2);		// required
define("COL_BREEDINGPROGRAM", 3);	// required
define("COL_YEAR", 4);				// required
define("COL_TRIALCODE", 5);			// required
define("COL_TRIALENTRYNO", 6);		// required
define("COL_LINENAME", 7);			// required
define("COL_PEDIGREE", 8);			// required
define("COL_GROWTHHABIT", 9);		// required
define("COL_ROWTYPE", 10);			// required
define("COL_ENDUSE", 11);			// required
define("COL_OHTERFEATURES", 12);
define("COL_GRAINYIELD", 13);
define("COL_PLANTHEIGHT", 14);
//define("COL_HEADINGDATE", 15);
define("COL_LODGING", 15);
define("COL_PLUMPGRAIN", 16);
define("COL_TESTWEIGHT", 17);
//define("COL_WINTERHARDINESS", 19);
//define("COL_SCALD", 20);
//define("COL_SPOTBLOTCH", 21);
//define("COL_STRIPERUST", 22);


define("ROW_START", 2);
define("ROW_STOP", 97);

/**
 * Columns in the annotations file
 *
 * Tells the script which column is which. (Starting at 1) 
 */
define("ROW_YEAR", 2);
define("ROW_LOCATION", 3);
define("ROW_COLLABORATOR", 4);
define("ROW_TRIALNAME", 5);
define("ROW_PLANTINGDATE", 6);
define("ROW_SEEDINGRATE", 7);
define("ROW_EXPERIMENTALDESIGN", 8);
define("ROW_NUMBEROFREPLICATIONS", 9);
define("ROW_PLOTSIZE", 10);
define("ROW_HARVESTEDAREA", null);
define("ROW_IRRIGATION", 11);
define("ROW_OHTERREMARKS", 12);
define("NUMEXPERIMENTS", 6);

/*******************************************************************************
 Try to avoid editing below here
*******************************************************************************/







require_once("../../lib/Excel/reader.php"); // Microsoft Excel library

/* Read the mean files */
$reader = & new Spreadsheet_Excel_Reader();
$reader->setOutputEncoding('CP1251');
$reader->read(SOURCEFILE . ".xls");
$sheet = $reader->sheets[0];

/* Read the annotations file */
$reader = & new Spreadsheet_Excel_Reader();
$reader->setOutputEncoding('CP1251');
$reader->read(ANNOTATIONSFILE . ".xls");
$annots = $reader->sheets[0];

/* Establish a connection to the database */
$dblink = mysql_connect(DBHOST, DBUSER, DBPASS);
if (!$dblink)
{
	die ("MySQL connection failed: " . mysql_error());
}
if (!mysql_select_db(DBNAME, $dblink))
{
	die ("Database selection failed: " . mysql_error());
}

/* Clean old data */
/*mysql_query("DELETE FROM line_records");
mysql_query("ALTER TABLE line_records AUTO_INCREMENT = 0");
mysql_query("DELETE FROM tht_base");
mysql_query("ALTER TABLE tht_base AUTO_INCREMENT = 0");
mysql_query("DELETE FROM phenotype_data");
mysql_query("ALTER TABLE phenotype_data AUTO_INCREMENT = 0");
mysql_query("DELETE FROM datasets");
mysql_query("ALTER TABLE datasets AUTO_INCREMENT = 0");
mysql_query("DELETE FROM experiments");
mysql_query("ALTER TABLE experiments AUTO_INCREMENT = 0");*/

/*
 * Process the annotations file
 */
class experiment
{
	var $year;
	var $location;
	var $collaborator;
	var $trialname;
	var $plantingdate;
	var $seedingrate;
	var $experimentaldesign;
	var $numberofreplicaions;
	var $plotsize;
	var $harvestedarea;
	var $irregation;
	var $otherremarks;
}
$experiments = array();
for ($i = 0; $i < NUMEXPERIMENTS; $i++)
{
	$experiments[] = new experiment();
}

$year_row =					$annots['cells'][ROW_YEAR];
$location_row =				$annots['cells'][ROW_LOCATION];
$collaborator_row =			$annots['cells'][ROW_COLLABORATOR];
$trialname_row =			$annots['cells'][ROW_TRIALNAME];
$plantingdate_row =			$annots['cells'][ROW_PLANTINGDATE];
$seedingrate_row =			$annots['cells'][ROW_SEEDINGRATE];
$experimentaldesign_row =	$annots['cells'][ROW_EXPERIMENTALDESIGN];
$numberofreplicaions_row =	$annots['cells'][ROW_NUMBEROFREPLICATIONS];
$plotsize_row =				$annots['cells'][ROW_PLOTSIZE];
$harvestedarea_row =		$annots['cells'][ROW_HARVESTEDAREA];
$irrigation_row =			$annots['cells'][ROW_IRRIGATION];
$otherremarks_row =			$annots['cells'][ROW_OHTERREMARKS];

for ($i = 2; 2 + NUMEXPERIMENTS > $i; $i += 1)
{
	$index = $i - 2;
	
	
	// Year
	$experiments[$index]->year = $year_row[$i];
	
	
	// Location
	$experiments[$index]->location = $location_row[$i];
	// make corrections
	if (FALSE !== stripos($experiments[$index]->location, "corvallis"))
	{
		$experiments[$index]->location = 'Corvallis, Oregon';
	}
	else if (FALSE !== stripos($experiments[$index]->location, "pendleton"))
	{
		$experiments[$index]->location = 'Pendleton, Oregon';
	}
	
	
	// Collaborator
	$experiments[$index]->collaborator = $collaborator_row[$i];
	// make corrections
	if (FALSE !== stripos($experiments[$index]->collaborator, "hayes"))
	{
		$experiments[$index]->collaborator = 'Patrick Hayes';
	}

	
	// Experiment Name
	$experiments[$index]->trialname = $trialname_row[$i];

	
	// Planting Date
	// convert Microsoft Excel timestamp to Unix timestamp
	$date = (intval(trim($plantingdate_row[$i])) - 25568) * 86400;
	$experiments[$index]->plantingdate = date("Y-m-d", $date);

	
	// Seeding Rate
	$experiments[$index]->seedingrate = $seedingrate_row[$i];
	
	
	// Experimental Design
	$experiments[$index]->experimentaldesign = $experimentaldesign_row[$i];
	
	
	// Number of Replications
	$experiments[$index]->numberofreplicaions = intval($numberofreplicaions_row[$i]);
	
	
	// Plot Size
	$experiments[$index]->plotsize = $plotsize_row[$i];
	
	
	// Harvest Area
	$experiments[$index]->harvestedarea = $harvestedarea_row[$i];
	// make corrections
	if (FALSE !== stripos($irrigation_row[$i], "yes"))
	{
		$experiments[$index]->irrigation = 'yes';
	}
	else
	{
		$experiments[$index]->irrigation = 'no';
	}
	
	
	// Other Remarks
	$experiments[$index]->otherremarks = $otherremarks_row[$i];
}
$dataset_name = DATASETNAME;
$dataset_date = DATASETDATE;
$sql = "select datasets_uid from datasets where dataset_name = '$dataset_name' limit 1";
$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
if (1 == mysql_num_rows($res))
{
	$row = mysql_fetch_assoc($res);
	$dataset_id = $row['datasets_uid'];
}
else
{
	$sql = "insert into datasets set dataset_name = '$dataset_name', date = '$dataset_date', created_on = NOW()";
	mysql_query($sql) or die(mysql_error() . "<br>$sql");
	$dataset_id = mysql_insert_id();
}

// NOTE: Seperate loop for this is not needed. Do this inside the loop above to
// save time and resources.
foreach ($experiments as $experiment)
{
	$sql = "
		insert into
			experiments
		set
			datasets_uid = '$dataset_id',
			experiment_name = '{$experiment->trialname}',
			collaborator = '{$experiment->collaborator}',
			experiment_year = '{$experiment->year}',
			planting_date = '{$experiment->plantingdate}',
			seeding_rate = '{$experiment->seedingrate}',
			experiment_design = '{$experiment->experimentaldesign}',
			number_replications = '{$experiment->numberofreplicaions}',
			plot_size = '{$experiment->plotsize}',
			irrigation = '{$experiment->irrigation}',
			other_remarks = '{$experiment->otherremarks}',
			location = '{$experiment->location}',
			created_on = NOW()
	";
	mysql_query($sql) or die(mysql_error() . "<br>$sql");
} // end foreach




$current = NULL;	// the current row
for($i = ROW_START; $i <= ROW_STOP; $i++)
{
	$current = & $sheet['cells'][$i];
	
	$dataset_name = SRCFILE;

	// Get required columns
	$breeding_program_name =	ForceValue($current[COL_BREEDINGPROGRAM], "Fatal Error: Missing breeding program at row " . $i);
	$experiment_name =			ForceValue($current[COL_TRIALCODE], "Fatal Error: Missing trial code at row " . $i);
	$line_name =				ForceValue($current[COL_LINENAME], "Fatal Error: Missing line name at row " . $i);
	$pedigree_string =			ForceValue($current[COL_PEDIGREE], "Fatal Error: Missing pedigree at row " . $i);
	$variety =					ForceValue($current[COL_GROWTHHABIT], "Fatal Error: Missing growth habit at row " . $i);
	$row_type =					ForceValue($current[COL_ROWTYPE], "Fatal Error: Missing row type at row " . $i);
	$primary_end_use =			ForceValue($current[COL_ENDUSE], "Fatal Error: Missing end use at row " . $i);

	// Get optional columns
	$phenotype_data = array();
	$phenotype_data['yield'] =				SetValue($current[COL_GRAINYIELD], 'NULL');
	$phenotype_data['plant_height'] =		SetValue($current[COL_PLANTHEIGHT], 'NULL');
	//$phenotype_data['heading_date'] =		SetValue($current[COL_HEADINGDATE], 'NULL');
	$phenotype_data['lodging'] =			SetValue($current[COL_LODGING], 'NULL');
	$phenotype_data['plump_grain'] =		SetValue($current[COL_PLUMPGRAIN], 'NULL');
	$phenotype_data['test_weight'] =		SetValue($current[COL_TESTWEIGHT], 'NULL');
	//$phenotype_data['winter_hardiness'] =	SetValue($current[COL_WINTERHARDINESS], 'NULL');
	//$phenotype_data['scald'] =				SetValue($current[COL_SCALD], 'NULL');
	//$phenotype_data['spot_blotch'] =		SetValue($current[COL_SPOTBLOTCH], 'NULL');
	//$phenotype_data['stripe_rust'] =		SetValue($current[COL_STRIPERUST], 'NULL');
	
	/*
	 * Create a database entry to represent the current line record
	 */
	$sql = "select line_record_uid, pedigree_string as id from line_records where line_record_name = '$line_name' limit 1";
	$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
	if (1 == mysql_num_rows) // the specified line record already exists in the database
	{
		$line_record = mysql_fetch_assoc($res);
		$line_record_uid = $line_record['id'];
		
		// add the pedigree string if it is missing from the database but is in the excel file
		if (empty($line_record['pedigree_string']) && !empty($pedigree_string))
		{
			$sql = "update line_records set pedigree_string = '$pedigree_string' where line_record_uid = '$line_record_uid'";
			$res = mysql_query($sql) or die(mysql_error() . "<br>$sql";
		}
	}
	else
	{
		$sql = "insert into line_records set line_record_name = '$line_name', pedigree_string = '$pedigree_string', variety = '$variety', row_type = '$row_type', primary_end_use = '$primary_end_use', created_on = NOW()";
		$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
		$line_record_uid = mysql_insert_id();
	}
	
	/*
	 * Figure out which experiment to use
	 */
	$experiment_location = EXPERIMENT_LOCATION;
	$sql = "select experiment_uid as id from experiments where experiment_name = '$experiment_name' and location = '$experiment_location' limit 1";
	$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
	if (1 == mysql_num_rows($res))
	{
		$experiment = mysql_fetch_assoc($res);
		$experiment_uid = $experiment['id'];
	}
	else
	{
		die ("Fatal Error: experiment '$experiment_name' does not exist at row " . $i);
	} // end if
	
	$sql = "insert into tht_base set line_record_uid = '$line_record_uid', experiment_uid = '$experiment_uid', created_on = NOW()";
	$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
	$tht_base_uid = mysql_insert_id();
	
	/*
	 * Enter phenotype values into the database for this particular line in this
	 * particular experiment
	 */
	foreach ($phenotype_data as $key => $phenotype)
	{
		$phenotype_name = str_replace("_", " ", trim($key));
		$sql = "select phenotype_uid from phenotypes where phenotypes_name = '$phenotype_name' limit 1";
		$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
		if (1 == mysql_num_rows($res))
		{
			$row = mysql_fetch_assoc($res);
			$phenotype_uid = $row['phenotype_uid'];
			if ($phenotype != 'NULL') $phenotype = "'$phenotype'";
			$sql = "insert into phenotype_data set phenotype_uid = '$phenotype_uid', tht_base_uid = '$tht_base_uid', value = $phenotype, created_on = NOW()";
			$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
		}
		else
		{
			die("Phenotype $key doesn't exist in the database at row" . $i);
		} // end if
	} // end foreach
} // end for
echo ("<h1>Done!</h1>");









/*******************************************************************************
 Utility Functions
*******************************************************************************/

/**
 * Returns $arg1 if it is set, else fatal error
 */
function ForceValue(& $arg1, $msg)
{
	if (isset($arg1))
	{
		return $arg1;
	}
	die($msg);
}

/**
 * Returns $arg1 if it is set, else $arg2
 */
function SetValue(& $arg1, $arg2 = NULL)
{
	if (isset($arg1))
	{
		return $arg1;
	return $arg2;
	}
}