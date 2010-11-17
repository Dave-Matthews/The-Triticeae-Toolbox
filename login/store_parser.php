<?php
require 'config.php';
/*
 * Logged in page initialization
 */
include("../includes/bootstrap.inc");

connect();
loginTest();

/* ******************************* */
$row = loadUser($_SESSION['username']);

/* ****************************** */
////////////////////////////////////////////////////////////////////////////////
ob_start();
include("../theme/admin_header.php");
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();
////////////////////////////////////////////////////////////////////////////////

/**
 * write the data input form
 *
 * @param array $tbl_info table information for generating the form
 * @return string $form_html the html codes for write the form
 */
function write_data_input_form ($tablename, $tbl_info, $action_href) {
	$form_str="";
	$formname=$tablename."_inputform";
	$pkf=$tbl_info[0]['field'];
	$form_str.="<form method='post' enctype='multipart/form-data' id='$formname' onsubmit=\"AddDataByAjax('$tablename', '$pkf'); return false\">";
	$form_str.="<table class=\"generic\">";
	$firstrow="disabled";
	foreach ($tbl_info as $row_info) {
		$field_name=strtolower($row_info['field']);
		if ($field_name=='created_on' || $field_name=='updated_on' || $field_name=='published_on' || $field_name=='date') continue;
		$form_str.="<tr><td>".$row_info['field']."</td><td><input type='text' name=\"".$row_info['field']."\" $firstrow></td></tr>\n";
		$firstrow="";
		$form_str.="<input type='hidden' name='".$row_info['field']."_isnum' value=".$row_info['isnum']."></td></tr>\n";
	}
	$form_str.="<input type='hidden' name='".$tablename."_tblid' value=$tablename ></td></tr>\n";
	$form_str.="<tr><td></td><td><button type='submit'>Submit</button> <button type='reset'>Clear</button></td></tr>\n";
	$form_str.="</table></form>\n";
	return $form_str;
}

/**
 * get the name of the tables that are implicitly referenced
 *
 * @param string $nm search table
 * @return array $refernced_tables referenced tables
 */
function chk_tbl_reference($nm) {
	// searching for foreign tables stop at datasets
	$dptbls=array();
	// if ($nm=="datasets") return $dptbls;
	// the tables to ignore
	$tbl_ignore=array('barley_pedigree_catalog');

	$result=mysql_query("show create table $nm");
	while( $row = mysql_fetch_assoc($result) ) {
		$cstr=$row['Create Table'];
		preg_match_all('/REFERENCES\s`(.*?)`/', $cstr, $mts);
		foreach ($mts[1] as $mt) {
			if (in_array($mt,$tbl_ignore)) continue;
			array_push($dptbls, $mt);
		}
	}
	return $dptbls;
}

/**
 * This will reorder the tables based on their dependence
 *
 * @param array $tbnms the names of the tables
 * @return array $tbs the tables in the order of dependence
 */
function get_tbl_order(array $tbnms) {
	// $rtb will be the return array with no conflicts in dependence
	$rtb=$tbnms;

	// The tables that are reference in foreign keys but are not explictly displayed in the data

	$tbstack=$rtb;
	$tbexamed=$rtb;
	$foreign_tbls=array();
	while (count($tbstack)>0) {
		$tb2chk=array_shift($tbstack);
		$ftbls=chk_tbl_reference($tb2chk);
		foreach ($ftbls as $ftbl) {
			if (! in_array($ftbl, $tbexamed)) {
				if (! in_array($ftbl, $foreign_tbls)) array_push($foreign_tbls, $ftbl);
				array_push($tbstack, $ftbl);
				array_push($tbexamed, $ftbl);
			}
		}
	}
	$all_tables=array_merge($rtb, $foreign_tbls);
	/* take care of the 2 m:n tables between datasets, experiments and datasets, breeding_programs */
	if (in_array("datasets", $all_tables) && in_array("breeding_programs", $all_tables)  && ! in_array("datasets_breeders",$all_tables)) {
		array_push($all_tables, "datasets_breeders");
	}
	// if (in_array("datasets", $all_tables) && in_array("experiments", $all_tables)  && ! in_array("datasets_experiments",$all_tables)) {
		// array_push($all_tables, "datasets_experiments");
	// }
	// get the dependence lists
	$tbl_dep=array();
	for($i=0; $i<count($all_tables); $i++) {
		$dep_info=chk_tbl_dep($all_tables[$i]);
		if ($dep_info[0]>0) {
			$tbl_dep[$all_tables[$i]]=$dep_info[1];
		}
	}

	// order the tables based on the references

	$tmparr=$all_tables;
	$tbstack=array();
	$tbexamed=array();
	$sorted_tbls=array();
	while (count($tmparr)>0) {
		$tmp=array_pop($tmparr);
		if (in_array($tmp, $tbstack) || in_array($tmp, $sorted_tbls)) {
			continue;
		}
		else {
			array_push($tbstack, $tmp);
		}
		while (count($tbstack)>0) {
			$cfbls=chk_tbl_reference($tbstack[count($tbstack)-1]);
			if (count($cfbls)<=0) {
				array_push($sorted_tbls, array_pop($tbstack));
			}
			else {
				$flag=0;
				foreach ($cfbls as $cfbl) {
			 		if (! in_array($cfbl, $tbstack) && ! in_array($cfbl, $sorted_tbls)) {
			 			array_push($tbstack, $cfbl);
			 			$flag++;
			 		}

			 	}
			 	if ($flag==0) array_push($sorted_tbls, array_pop($tbstack));
			 }

		}

	}
	return array($sorted_tbls, $tbl_dep, $foreign_tbls);
}

/**
 * This function parse the input from excel and get the table names and attributes
 */
function get_table_names (array $cols, array $dfls) {
	/* get the table names (sort later in get_tbl_order*/
	$tbls=array(); // will hold all the tables named in the def file
	$repdef=array(); //
	$coldef=preg_grep('/^#/', $cols);
	foreach ($coldef as $k => $v) {
		$v=preg_replace('/^#/', '', $v);
		$line=explode('/',$v);
		$v2=preg_replace('/^#/', '', $dfls[$k]);
		$line2=explode('/', $v2);
		array_push($repdef, array_merge(array($k), $line, $line2));
		if (! in_array($line[1], $tbls)) array_push($tbls, $line[1]);
		if (! in_array($line2[1], $tbls)) array_push($tbls, $line2[1]);
	}



	$dbdef=array();
	foreach ($dfls as $k=>$v) {
		if (preg_match('/^#/', $v)) continue;
		$line=explode('/', $v);
		array_push($dbdef, array($k, $line[0], $line[1], $line[2]));
		if (! in_array($line[1], $tbls)) array_push($tbls, $line[1]);
	}
	$tbl_ord_dep=get_tbl_order($tbls);
	$tbls=$tbl_ord_dep[0];
	$tbldep=$tbl_ord_dep[1];
	$foreign_tables=$tbl_ord_dep[2];
	return (array($tbls, $tbldep, $foreign_tables, $dbdef, $repdef));
}

$associative_entities=array("genotyping_data", "phenotype_data", "tht_base");
?>

<div id="primaryContentContainer">
	<div id="primaryContent">
  		<div class="box">

<?php

	//
	//The pre-processing part
	//

	$infilename = $_POST['infilename'];
	print "<h2>Generic input storation</h2>";

	print "<div class=\"boxContent\">";

	print "<h3> The input definition file: ". basename($infilename) . "</h3>";
	require_once("../includes/excel/reader.php");	//include excel reader

	// Parse the definition file

	/* Creating the object */
	$data = new Spreadsheet_Excel_Reader();
	$data->setOutputEncoding('CP1251');
	$data->read($infilename);
	$data->trimSheet(0); 	//new function that I added to trim columns
	/* Setting Error Reporting */
	error_reporting(E_ALL ^ E_NOTICE);
	/* Parse the Sheet */
	$colnames=array();
	$defline=array();
	/* Iterate through row starting at row 1 */
	$parser_name="";
	for ($i = 1; $i <= $data->sheets[0]['numRows']; $i++) {
		if ($i==1) {
			$parser_name=strtolower($data->sheets[0]['cells'][$i][1]);
			continue;
		}
		/* Iterate through each column */
		for ($j = 1; $j <= $data->sheets[0]['numCols']; $j++) {

			if ($i==2) {
				//get column names
				// if no def in line 3, ignore the current column
				if (! isset($data->sheets[0]['cells'][$i+1][$j]) || strlen($data->sheets[0]['cells'][$i+1][$j])<1) {
					continue;
				}
				$colnames[$j]=strtolower($data->sheets[0]['cells'][$i][$j]);
			}
			else {
				if (! isset($data->sheets[0]['cells'][$i][$j]) || strlen($data->sheets[0]['cells'][$i][$j])<1) {
					continue;
				}
				$defline[$j]=trim(strtolower($data->sheets[0]['cells'][$i][$j]));
			}
		}
	}

	// Get the names of the table presented in the dataset

	$table_build_info=get_table_names($colnames, $defline);
	$_SESSION['_table_build_info']=$table_build_info;

	// Take care of the foreign tables that are refered but not defined in the dataset
	print "<div id=\"foreign_references\">\n";
	if (is_array($table_build_info[2]) && count($table_build_info[2])>0) {

		print "<p><h3>Foreign references</h3></p>\n";

		print "<p>The following entities (tables) are referenced in the dataset, but is not defined in the dataset.<br>";
		print "You will have to supply information for these tables here.</p>";
		$tbl_ignore=array(); // the referenced foreign tables that have 1-1 relationships to entities defined in the data
		if (preg_match('/^cap_fieldtrials_def/i', basename($infilename))) {
			$tbl_ignore=array("units", "phenotype_category");
			// print "The foreign tables: \"units\" and \"phenotype_category\" are passed here, as they have 1-1 relationships to entities in the data.";
		}
		// If an associative table is referenced, a record will be automatically generated into that table
		$foreign_associative=array();
		foreach ($table_build_info[2] as $ftb) {
			if (in_array($ftb, $associative_entities)) {
				array_push($foreign_associative, $ftb);
				continue;
			}
			if (in_array($ftb, $tbl_ignore)) {
				print "$ftb has 1-1 relationship with entities in data, just choose the first entry here<br>";
			}
			$foreign_table_info=get_table_info($ftb);
			//primary key field (pkf)
			$pkf=$foreign_table_info[0]['field'];
			print "<p><strong>$ftb:</strong>\n <select id=\"".$ftb."_sel\" onChange=\"DispSelChg(this.value, '$ftb', '$pkf')\">\n";
			print "<option value='Select' selected='selected'>Select</option>\n";
			print generic_sel_options ($ftb);
			print "<option value='New'>New</option>\n";
			print "</select></p>\n\n";
			print "<div id=\"".$ftb."_div\" style=\"display:none;\">\n";
			print write_data_input_form($ftb, $foreign_table_info, "login/store_parser?");
			print "</div>\n";
			print "<div id=\"".$ftb."_info\" style=\"display:none;\">\n";
			print "</div>\n";
		}
		print "<p><h3>Foreign Associative tables</h3></p>";
		print "<p><br>The associative tables referenced will be populated automatically:<br>";
		print implode("<br>",$foreign_associative)."</p>";
		print "<button onClick=\"validate_foreign_sels();\">OK</button>";


	}
	else {
		print "Foreign tables examined\n Press OK to proceed\n";
		print "<button onClick=\"validate_foreign_sels();\">OK</button>";
	}
	print "</div>\n"; // for div foreignreferences
	// The process division

	print "<div id=\"processing_tables\" style=\"display:none\">\n";
	print "<h3>Pour down something for your worms, put on a few pounds of weight, and just wait a little while.</h3>";
	print "<img src=\"images/coffee_cup.gif\" alt=\"Wait\" >";
		// The code will be dynamically generated by InsertTableData in ajaxlip.php
	print "</div>\n";

?>

			</div> <!-- end boxContent -->
		</div>
	</div>
</div>
</div>


<?php include("../theme/footer.php");?>
