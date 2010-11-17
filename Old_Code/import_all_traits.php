<?php
/**
 * This script is designed to read the AllTraits_annotatedpmh.xls file and
 * insert it into the THT database.
 * 
 * @author Gavin Monroe
 *
 */

// config
define("SRCFILE", "AllTraits_annotatedpmh.xls");
define("DBHOST", "localhost");
define("DBUSER", "yhames04");
define("DBPASS", "gdcb07");
define("DBNAME", "sandbox_yhames04_dev");
define("TRAITCATEGORY", 1);
define("TRAITNAME", 2);
define("TRAITUNITS", 3);
define("TRAITDESCRIPTION", 4);
define("TRAITTYPE", 5);
define("TRAITRANGE", 6);
define("ROWSTART", 3);
define("ROWSTOP", 59);

require_once("../../lib/Excel/reader.php"); // excel library

$reader = & new Spreadsheet_Excel_Reader();
$reader->setOutputEncoding('CP1251');
$reader->read(SRCFILE);
$sheet = & $reader->sheets[0];

$dblink = mysql_connect(DBHOST, DBUSER, DBPASS);
if (!$dblink){
	die ("MySQL connection failed: " . mysql_error());
}
if (!mysql_select_db(DBNAME, $dblink)){
	die ("Database selection failed: " . mysql_error());
}

// clear previous data
mysql_query("DELETE FROM phenotype_category;") or die(mysql_error());
mysql_query("DELETE FROM phenotypes;") or die(mysql_error());
mysql_query("DELETE FROM units;") or die(mysql_error());
mysql_query("ALTER TABLE phenotype_category AUTO_INCREMENT = 0;") or die(mysql_error());
mysql_query("ALTER TABLE phenotypes AUTO_INCREMENT = 0;") or die(mysql_error());
mysql_query("ALTER TABLE units AUTO_INCREMENT = 0;") or die(mysql_error());

$start = ROWSTART;
$stop = ROWSTOP;
$temp_trait_category = "";
$temp_trait_category_id = -1;
$temp_units = array();
$droughtResponseFlag = false;

for($i = $start; $i <= $stop; $i++){
	$temp_trait_name = "";
	$temp_trait_units = "";
	$temp_trati_unit_id = 0;
	$temp_trait_description = "";
	$temp_trait_type = "";
	$temp_trait_range = "";
	
	$temp_cur_cell = & $sheet['cells'][$i];
	//$incomplete = FALSE;
	
	// get category
	if(isset($temp_cur_cell[TRAITCATEGORY])){
		$temp_trait_category = trim($temp_cur_cell[TRAITCATEGORY]);
	}
	
	// get name
	if(isset($temp_cur_cell[TRAITNAME])){
		$temp_trait_name = trim($temp_cur_cell[TRAITNAME]);
	}else{
		die("Invalid name at row " . $i);
	}
	
	// get units
	if(isset($temp_cur_cell[TRAITUNITS])){
		$temp_trait_units = trim($temp_cur_cell[TRAITUNITS]);
	}
	
	if(isset($temp_cur_cell[TRAITDESCRIPTION])){
		$temp_trait_description = trim($temp_cur_cell[TRAITDESCRIPTION]);
	}
	
	// get range
	if(isset($temp_cur_cell[TRAITTYPE])){
		$temp_trait_type = trim($temp_cur_cell[TRAITTYPE]);
		if (preg_match('/^\sinteger\s$/is', $temp_trait_type))
			$temp_trait_type = 'integer';
		if (preg_match('/^\scontinuous\s$/is', $temp_trait_type))
			$temp_trait_type = 'continuous';		
	}else{
		$temp_trait_type = 'unknown';
	}
	
	// parse range
	$min_val = 'NULL';
	$max_val = 'NULL';
	if(isset($temp_cur_cell[TRAITRANGE])){
		$temp_trait_range = $temp_cur_cell[TRAITRANGE];
		$range = array();
		if (preg_match('/([0-9\.]+),\s*([0-9\.]+)/is', $temp_trait_range, $range)){
			$min_val = "'$range[1]'";
			$max_val = "'$range[2]'";
		}
	}
	
	if(!empty($temp_trait_category)){
		if (preg_match('/^\s*Drought\sResponse\s*$/is', $temp_trait_category)){
			$temp_trait_category = "";
			$droughtResponseFlag = true;
		}else{
			$sql = "
				INSERT INTO
					phenotype_category (phenotype_category_name, created_on)
				VALUES
					('$temp_trait_category', NOW())
			";
			$result = mysql_query($sql);
			if(!$result)
				die("MySQL query failed: " . mysql_error() . "<br>" . $sql);
			$temp_trait_category_id = mysql_insert_id();
			$droughtResponseFlag = false;
		}
		$temp_trait_category = "";
	}
	
	if(!$droughtResponseFlag){
	
		if(!empty($temp_trait_units) && !preg_match('/^\s*\?\s*$/is', $temp_trait_units)){
			if(!in_array($temp_trait_units, $temp_units)){
				$sql = "
					INSERT INTO
						units
							(unit_name, created_on)
						VALUES
							('$temp_trait_units', NOW())
				";
				$result = mysql_query($sql);
				if(!$result)
					die("MySQL query failed: " . mysql_error() . "<br>" . $sql);
				$temp_trait_unit_id = mysql_insert_id();
				$temp_units[$temp_trait_unit_id] = $temp_trait_units;
				$temp_trait_unit_id = "'". $temp_trait_unit_id . "'";
			}else{
				$temp_trait_unit_id = "'". array_search($temp_trait_units, $temp_units) . "'";
			}
		}else{
			$temp_trait_unit_id = 'NULL';
		}
		
		$sql = "
			INSERT INTO
				phenotypes
					(phenotype_category_uid, phenotypes_name, unit_uid, description, datatype, min_val, max_val, created_on)
			VALUES
				('$temp_trait_category_id', '$temp_trait_name', $temp_trait_unit_id, '$temp_trait_description', '$temp_trait_type', $min_val, $max_val, NOW())";
		$result = mysql_query($sql);
		if(!$result)
			die("MySQL query failed: " . mysql_error() . "<br>" . $sql);
	}
	
}

?>
done!


