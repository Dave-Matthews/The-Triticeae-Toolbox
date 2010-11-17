<?php
// import_traits.php
include '../includes/bootstrap.inc';
connect();

define('DEBUG_1', TRUE);

$error = array(
	
);

$validMimeTypes = "application/msexcel,application/x-msexcel,application/x-ms-excel,application/vnd.ms-excel,application/x-excel,application/x-dos_ms_excel,application/xls,application/x-xls,zz-application/zz-winassoc-xls";

$filename = 'AllTraits.xls';
include(realpath('../lib/Excel/').'/reader.php');
$reader= new Spreadsheet_Excel_Reader();
$reader->setOutputEncoding('CP1251');
$reader->read($filename);

$spreadsheet = $reader->sheets[0];

// remove heading
array_shift($spreadsheet['cells']);

$columnNames = &array_shift($spreadsheet['cells']);
$columnOffsets = array('category' => 1);
foreach ($columnNames as $columnOffset => &$columnName)
{
	if (preg_match('/^\s*cap\s*name\s*$/is', $columnName))
		$columnOffsets['name'] = $columnOffset;
	if (preg_match('/^\s*units\s*$/is', $columnName))
		$columnOffsets['units'] = $columnOffset; 
	if (preg_match('/^\s*description\s*$/is', $columnName))
		$columnOffsets['description'] = $columnOffset;
}	

// echo "<pre>";
// print_r($spreadsheet['cells']);
// die();

$units = array();
$lastCategory = null;
foreach ($spreadsheet['cells'] as $row)
{
	if (isset($row[$columnOffsets['category']]))
	{
		$lastCategory = $row[$columnOffsets['category']];
	}
	else if (is_null($lastCategory))
	{
		// error
	}
	$name = $row[$columnOffsets['name']];
	$unitName = $row[$columnOffsets['units']];
	$description = $row[$columnOffsets['description']];
	
	$unit = my_units_peer::get_by_unit_name($unitName);
	if (is_null($unit)){
		$unit = new units(
			$unit_uid = null,
			$unit_name = $unitNmae,
			$unit_abbreviation = null,
			$unit_description = null,
			$updated_on = null,
			$created_on = null
		);
	}
	$unit = new my_units($unit);
	if (weakContains($units, $unit))
		$unit = $units[$unitName];
	else
		$units[$unitName] = $unit;
	
	$phenotype = my_phenotypes_peer::get_by_phenotypes_name($name);
	if (is_null($phenotype)) {
		$phenotype = new phenotypes(
			$phenotype_uid = null,
			$unit_uid = null,
			$phenotype_category_uid = null,
			$phenotypes_name = $name,
			$short_name = null,
			$description = $description,
			$datatype = null,
			$updated_on = null,
			$created_on = null
		);
	}
	$phenotype = new my_phenotypes($phenotype);
	$unit->add_phenotype($phenotype);
}

if (DEBUG_1) {
	echo "<pre>";
	print_r($units);
}