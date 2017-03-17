<?php
/**
 * This is a library for ajax related functions. This file is reffered to by the ajax function
 * giving it at least 1 parameter "func" which is the function in this library to call.
 * These functions are only called by javascript functions located in core.js
 *
 * Note: Not all of these functions are documented, it would be redundant. Check core.js concerning what these do.
 * 3/20/2011 JLee Enable write privilege to DB
 *
 * PHP version 5.3
 * Prototype version 1.5.0
 *
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/includes/ajaxlib.php
 */

if (!((isset($config['base_url']))&(isset($config['root_dir'])))) {
    include 'config.php';
}
set_time_limit(3000);

//does the function exist?
if (!isset($_GET['func'])) {
} elseif (!function_exists($_GET['func'])) {
    echo "";  //if not then just echo nothing as an appropriate ajax return.
} else {
    $function = $_GET['func'];
    unset($_GET['func']);  //removing function name

    //global includes to load all functions in all other libraries
    //note this also runs all of the input validation on the $_GET array, so they should not be hostile.
    include "bootstrap_curator.inc";

    //give function database access
    $link = connecti();

    //execute function
    call_user_func($function, $_GET);

    //close mysql connection to prevent overloading.
    mysqli_close($link);
}


/**
 * This function shows the contents of a particular mapset entry in table format
 *
 * @param array $arr ajax is a bit restricting so we simply pass it the entire array as parameters.
 *
 * @return nothing - it echos the table.
 */
function showMapsetContents($arr)
{
    global $mysqli;
    if ($arr['id'] == "") {
        echo "Wrong Function: showMapsetContents()";
    }

    $res = mysqli_query($mysqli, "SELECT * FROM mapset WHERE mapset_uid = $arr[id]")
        or die(mysqli_error($mysqli));

    if (mysqli_num_rows($res) > 1) {
        echo "Number of Rows Exceeds 1, we have a database problem";
    }


    echo "<table class=\"tableclass1\">\n";
    $row = mysqli_fetch_assoc($res);
    foreach ($row as $k => $v) {
        echo "\t<tr>\n";
        echo "\t\t<td><strong>$k</strong></td>\n";
        echo "\t\t<td>$v</td>\n";
        echo "\t</tr>\n";
    }
    echo "</table>\n";
}

function DispSelContents($arr)
{
    global $mysqli;
    if ($arr['id'] == "" || $arr['tablename']=="" || $arr['field']=="") {
        echo "Invalid Input for DispSelContents";
    }
    // print "SELECT * FROM ".$arr['tablename']." WHERE ".$arr['field']." = ".$arr['id'];
    $sql = "SELECT * FROM " . $arr['tablename'] . " WHERE " . $arr['field'] . "=" . $arr['id'];
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));

    if (mysqli_num_rows($res) > 1) {
        warning("key is not unique");
    }
    echo "<table class=\"tableclass1\">\n";
    $row = mysqli_fetch_assoc($res);
    foreach ($row as $k => $v) {
        echo "\t<tr>\n";
        echo "\t\t<td><strong>$k</strong></td>\n";
        echo "\t\t<td>$v</td>\n";
        echo "\t</tr>\n";
    }
    echo "</table>\n";
}

function InsertByAjax($arr) {
    global $mysqli;
    if ($arr['tablename']=="") {
	print "Invalid Input for InsertByAjax";
    } else {
	// print "Table name ".$arr['tablename']."\n";
    }
	$tablename=$arr['tablename'];
	unset($arr['tablename']);
	$result=mysqli_query($mysqli, "show tables like \"$tablename\"");
	if(mysqli_num_rows($result) <= 0) {
		print "Invalid table name";
		return;
	}
	$attributes=array_keys($arr);

	$vals=array();
	$isnum=array();
	// start from 1 to allow for the first index row
	for ($i=1; $i<count($attributes); $i++) {
		if (in_array($attributes[$i]."_isnum", $attributes) && isset($arr[$attributes[$i]]) && $arr[$attributes[$i]]!=="") {
			$vals[$attributes[$i]]=$arr[$attributes[$i]];
			array_push($isnum, $arr[$attributes[$i]."_isnum"]);
		}
	}
	// take care of created_on and updated_on
	$result=mysqli_query($mysqli, "show columns from $tablename");
	$tbl_fields=array();
	if (mysqli_num_rows($result)>0) {
		while ($row = mysqli_fetch_assoc($result)) {
			array_push($tbl_fields, $row['Field']);
		}
	}
	if (in_array('created_on', $tbl_fields)) {
		$vals['created_on']='now()';
		array_push($isnum, 1);
		$vals['updated_on']='now()';
		array_push($isnum, 1);
	}
	if (in_array('published_on', $tbl_fields)) {
		$vals['published_on']='now()';
		array_push($isnum, 1);
	}
	if (in_array('date', $tbl_fields)) {
		$vals['date']='now()';
		array_push($isnum, 1);
	}
	// put the data into the database
	$add_info=add_array_attributes($vals, $isnum, $tablename, "1", "0", "");

	// Return the response in XML to javascript function AddDataByAjax;
	// Begin XML response
	// XML Preamble
	$dom = new DOMDocument("1.0");
	// display document in browser as plain text
	// for readability purposes
	header("Content-Type: text/xml");

	// create root element
	$root = $dom->createElement("xmlresponse");
	$dom->appendChild($root);

	// create child element
	$item = $dom->createElement("add_success");
	$root->appendChild($item);
	// create text node
	$text = $dom->createTextNode($add_info[0]);
	$item->appendChild($text);

	$item2=$dom->createElement("add_uid");
	$root->appendChild($item2);
	$text2=$dom->createTextNode($add_info[1]);
	$item2->appendChild($text2);

	$item3=$dom->createElement("display_text");
	$root->appendChild($item3);
	$text3=$dom->createTextNode($add_info[1]." ".implode(" ", array_slice($vals, 0, 2)));
	$item3->appendChild($text3);

	// save and display tree
	// Output XML string
	echo $dom->saveXML();


	// add_array_attributes($vals, $isnum, $tablename, $testKey, $testVal, $idkey)
}

/**
 * The workhorse for store_parser, stores the data into the database
 *
 * @param array $arr contains the forein table information passed from core.js->InsertTableByAjax
 */
function InsertTableByAjax($arr) {
    global $mysqli;
	session_start();

	// load the data excel file

	require_once("../includes/excel/reader.php");	//include excel reader
	$infilename=$_SESSION['user_data_file'];
	$infilename="../login/".$infilename;
	/* Creating the object */
	$data = new Spreadsheet_Excel_Reader();
	$data->setOutputEncoding('CP1251');
	$data->read($infilename);
	$data->trimSheet(0); 	//new function that I added to trim columns
	/* Setting Error Reporting */
	error_reporting(E_ALL ^ E_NOTICE);
	/* Parse the Sheet */
	$first_row=array();
	$start_row=2;
	/* Iterate through row starting at row 1 */
	for ($i = $start_row; $i <= $data->sheets[0]['numRows']; $i++) {
		if ($i==$start_row) {
			$first_row=$data->sheets[0]['cells'][$i];
			continue;
		}
		/* Iterate through each column */
		for ($j = 1; $j <= $data->sheets[0]['numCols']; $j++) {
		}
	}
	/* Inserting data into the database
 	   1. foreign tables that are referenced, just pass as the data input for
		  these tables should have been done at this stage
	   2. tables defined in the first row of the definition excel file
	   3. tables defined outside of the grid
	   4. tables of the grid data (defined in the second row, with a # prefix
	   5. associative tables (in the grid), that is implicitly defined
	*/

	print "<pre>";
	// The foreign table information passed from javascript
	$foreign_table_ids=array();
	$selvalues=explode(':',$arr['selstr']);
	foreach ($selvalues as $selval) {
		if (preg_match('/(.*?)_sel,(.*)/', $selval, $mts)) {
			$foreign_table_ids[$mts[1]]=$mts[2];
		}
	}

	// The table building information passed from store_parser.php
	$table_build_info=$_SESSION['_table_build_info'];
	$tables=$table_build_info[0];  // all the tables ordered by their dependence
	$tbl_dep=$table_build_info[1]; // foreign table information

	// The grid data defined by the # columns in the data definition table
	$grid_colstart=-1;
	$grid_rowstart=$start_row+1; // default to the second row
	$grid_width=-1;
	$ukey=get_unique_keys();
	$count=array();
	foreach ($tables as $tbl) {
		// keep track of the insertions
		$count[$tbl]=array(0,0,0);
		// external foreign tables are supposed to have been defined at this stage
		if (array_key_exists($tbl, $foreign_table_ids)) {
			continue;
		}

		// all the data to insert are in $vals, $isnum contains flags of the type of the data
		$vals=array();
		$isnum=array();

		// check if all the foreignkeys are defined
		$foreign_table_flag=0;
		if (array_key_exists($tbl, $tbl_dep)) {
			foreach ($tbl_dep[$tbl] as $frntbl=>$frnfld) {
				if (! array_key_exists($frntbl, $foreign_table_ids)) {
					$foreign_table_flag++;
				}
				elseif (! is_array($foreign_table_ids[$frntbl])) {
					$vals[$frnfld]=$foreign_table_ids[$frntbl];
					$isnum[$frnfld]=1;
				}
				else {
					$vals[$frnfld]="";
					$isnum[$frnfld]=-1;
				}

			}
		}
		// if ($foreign_table_flag>0) error(0,"Some of the foreign keys is not identified for table $tbl");

		$pkey=get_pkey($tbl);
		// take care of the data in the first row
		$first_row_def=$table_build_info[4][0];
		if ($tbl==$first_row_def[2]) {
			// foreign keys for other tables
			$frnkey_mx=array();
			$frnkey_mx['row']=array();
			$num_rep=$first_row_def[3];
			$rstart=$first_row_def[0];
			$grid_colstart=$rstart;
			$grid_width=$num_rep;
			$rfield=$first_row_def[1];
			$risnum=$first_row_def[4];
			/* for complicated def */
			if (preg_match('/(.*?)::(.*?)::(.*)/', $rfield, $mts)) {
				$rfld1=$mts[1];
				$rfld2=$mts[2];
				$rtbl2=$mts[3];
				for ($i=0; $i<$num_rep; $i++) {
					$rdata=$first_row[$rstart+$i];
					if (preg_match('/(.*?)::(.*)/', $rdata, $dmts)) {
						$rdata1=trim($dmts[1]);
						$rdata2=trim($dmts[2]);
						$pkey2=get_pkey($rtbl2);
						// print "select $pkey from $tbl,$rtbl2 where $tbl.$pkey2=$rtbl2.$pkey2 and $rfld1=\"$rdata1\" and $rfld2=\"$rdata2\"\n";
						// $qres=mysql_query("select $pkey from $tbl natural join $rtbl2 where $rfld1=\"$rdata1\" and $rfld2=\"$rdata2\"") or die(mysql_error());
						$qres=mysqli_query($mysqli, "select $pkey from $tbl,$rtbl2 where $tbl.$pkey2=$rtbl2.$pkey2 and $rfld1=\"$rdata1\" and $rfld2=\"$rdata2\"") or die(mysqli_error($mysqli));
						if (mysqli_num_rows($qres)>0) {
							$qrow = mysqli_fetch_assoc($qres);
							$count[$tbl][0]++;
							$frnkey_mx['row'][$rstart+$i]=$qrow[$pkey];
						}
						// assumes the correspondent record has been inputed already
					}
					else {
						// no data or data is not in the correct format
						continue;
					}
				}

			}
			else {
				print "Should not be here\n";
				for ($i=0; $i<$num_rep; $i++) {
					$rdata=$first_row[$rstart+$i];
					$vals[$rfield]=$rdata;
					$isnum[$rfield]=$risnum;
					$insinfo=add_array_data($vals, $isnum, $tbl, array($rfield), array($rdata), $pkey, "" );
					$count[$tbl][$insinfo[0]*2]++;
					$frnkey_mx['row'][$rstart+$i]=$insinfo[1];  // to be set as the returned uid
				}
			}
			$foreign_table_ids[$tbl]=$frnkey_mx;
			continue;
		}

		// take care of the columns defined outside of the grid
		$coldef=$table_build_info[3];
		$insert_fields=array();
		for ($i=0; $i<count($coldef); $i++) {
			if (in_array($tbl, $coldef[$i])) {
				$rstart=$coldef[$i][0];
				$rfield=$coldef[$i][1];
				$risnum=$coldef[$i][3];
				array_push($insert_fields, array($rstart, $rfield, $risnum));
			}
		}
		if (count($insert_fields)>0) {
			$frnkey_mx=array();
			$frnkey_mx['col']=array();
			for ($i = $grid_rowstart; $i <= $data->sheets[0]['numRows']; $i++) {
				$frnkey_mx[$i]=array();
				foreach ($insert_fields as $insfld) {
					$grid_val=$data->sheets[0]['cells'][$i][$insfld[0]];
					if (preg_match('/same\sas\sabove/i', $grid_val) || trim(strtolower($grid_val))=="saa") {
						$grid_val=get_saa($insfld[0], $i-1, $data->sheets[0]['cells']);
					}
					$rdata=$grid_val;
					$vals[$insfld[1]]=$rdata;
					$isnum[$insfld[1]]=$insfld[2];
				}
				// take care of the foreign keys
				if (! isset($tbl_dep[$tbl])) $tbl_dep[$tbl]=array();
				foreach ($tbl_dep[$tbl] as $frntbl=>$frnfld) {
					if (! is_array($foreign_table_ids[$frntbl])) {
						$vals[$frnfld]=$foreign_table_ids[$frntbl];
					}
					elseif (array_key_exists('row', $foreign_table_ids[$frntbl])) {
						$vals[$frnfld]=$foreign_table_ids[$frntbl]['row'][$i];
					}
					elseif (array_key_exists('col', $foreign_table_ids[$frntbl])) {
						$vals[$frnfld]=$foreign_table_ids[$frntbl]['col'][$i];
					}
					else {
						// not apply here
					}
					$isnum[$frnfld]=1;
				}
				$tstFlds=array();
				if (isset($ukey[$tbl])) $tstFlds=$ukey[$tbl];
				else $tstFlds=get_ukey($tbl);
				$tstVals=array();
				foreach ($tstFlds as $tstfld) {
					array_push($tstVals, $vals[$tstfld]);
				}
				$prtcmd="";
				$insinfo=add_array_data($vals, $isnum, $tbl, $tstFlds, $tstVals, $pkey, $prtcmd );
				$count[$tbl][$insinfo[0]*2]++;
				$frnkey_mx['col'][$i]=$insinfo[1]; // to be set as the returned uid
			}

			$foreign_table_ids[$tbl]=$frnkey_mx;
			continue;
		}

		// take care of the grid data
		if ($tbl==$first_row_def[6]) {
			// this will be the table correspondent to the grid data
			$rfield=$first_row_def[5];
			$risnum=$first_row_def[8];
			$frnkey_mx=array();
			for ($i=$grid_rowstart; $i <= $data->sheets[0]['numRows']; $i++) {
				$frnkey_mx[$i]=array();
				for ($j=$grid_colstart; $j<=$grid_colstart+$grid_width-1; $j++) {
					// take care of the grid foreign keys
					foreach ($tbl_dep[$tbl] as $frntbl=>$frnfld) {
						if (! is_array($foreign_table_ids[$frntbl])) {
							$vals[$frnfld]=$foreign_table_ids[$frntbl];
						}
						elseif (array_key_exists('row', $foreign_table_ids[$frntbl])) {
							$vals[$frnfld]=$foreign_table_ids[$frntbl]['row'][$j];
						}
						elseif (array_key_exists('col', $foreign_table_ids[$frntbl])) {
							$vals[$frnfld]=$foreign_table_ids[$frntbl]['col'][$i];
						}
						else {
							$vals[$frnfld]=$foreign_table_ids[$frntbl][$i][$j];
						}
						$isnum[$frnfld]=1;
					}
					// get the grid data
					$grid_val=$data->sheets[0]['cells'][$i][$j];
					if (preg_match('/same\sas\sabove/i', $grid_val) || trim(strtolower($grid_val))=="saa") {
						$grid_val=get_saa($j, $i-1, $data->sheets[0]['cells'][$i][$j]);
					}
					$vals[$rfield]=$grid_val;
					$isnum[$rfield]=$risnum;
					// inster here
					$tstFlds=array();
					if (isset($ukey[$tbl])) $tstFlds=$ukey[$tbl];
					else $tstFlds=get_ukey($tbl);
					$tstVals=array();
					foreach ($tstFlds as $tstfld) {
						array_push($tstVals, $vals[$tstfld]);
					}
					$insinfo=add_array_data($vals, $isnum, $tbl, $tstFlds, $tstVals, $pkey, "" );
					$count[$tbl][$insinfo[0]*2]++;
					// store the key for reference
					$frnkey_mx[$i][$j]=$insinfo[1];
				}
			}
			$foreign_table_ids[$tbl]=$frnkey_mx;

		}
		elseif ($grid_colstart<0 && $grid_width<0) { // associative table before the grid
			$frnkey_mx=array();
			for ($i=$grid_rowstart; $i <= $data->sheets[0]['numRows']; $i++) {
				foreach ($tbl_dep[$tbl] as $frntbl=>$frnfld) {
					if (! is_array($foreign_table_ids[$frntbl])) {
						$vals[$frnfld]=$foreign_table_ids[$frntbl];
					}
					elseif (array_key_exists('col', $foreign_table_ids[$frntbl])) {
						$vals[$frnfld]=$foreign_table_ids[$frntbl]['col'][$i];
					}
					else {
						die("Foreign key not defined");
					}
					$isnum[$frnfld]=1;
				}
				// instert here
				$tstFlds=array();
				if (isset($ukey[$tbl])) $tstFlds=$ukey[$tbl];
				else $tstFlds=get_ukey($tbl);
				$tstVals=array();
				foreach ($tstFlds as $tstfld) {
					array_push($tstVals, $vals[$tstfld]);
				}
				$insinfo=add_array_data($vals, $isnum, $tbl, $tstFlds, $tstVals, $pkey, "" );
				$count[$tbl][$insinfo[0]*2]++;
				// store the key for reference
				$frnkey_mx[$i]=$insinfo[1];
			}
			$foreign_table_ids[$tbl]=$frnkey_mx;
		}
		else {
			// since the table name does not show up in the definition, it must be an associative table
			$frnkey_mx=array();
			for ($i=$grid_rowstart; $i <= $data->sheets[0]['numRows']; $i++) {
				$frnkey_mx[$i]=array();
				for ($j=$grid_colstart; $j<=$grid_colstart+$grid_width-1; $j++) {
					foreach ($tbl_dep[$tbl] as $frntbl=>$frnfld) {
						if (! is_array($foreign_table_ids[$frntbl])) {
							$vals[$frnfld]=$foreign_table_ids[$frntbl];
						}
						elseif (array_key_exists('row', $foreign_table_ids[$frntbl])) {
							$vals[$frnfld]=$foreign_table_ids[$frntbl]['row'][$j];
						}
						elseif (array_key_exists('col', $foreign_table_ids[$frntbl])) {
							$vals[$frnfld]=$foreign_table_ids[$frntbl]['col'][$i];
						}
						else {
							$vals[$frnfld]=$foreign_table_ids[$frntbl][$i][$j];
						}
						$isnum[$frnfld]=1;
					}
					// instert here
					$tstFlds=array();
					if (isset($ukey[$tbl])) $tstFlds=$ukey[$tbl];
					else $tstFlds=get_ukey($tbl);
					$tstVals=array();
					foreach ($tstFlds as $tstfld) {
						array_push($tstVals, $vals[$tstfld]);
					}
					$insinfo=add_array_data($vals, $isnum, $tbl, $tstFlds, $tstVals, $pkey, "" );
					$count[$tbl][$insinfo[0]*2]++;
					// store the key for reference
					$frnkey_mx[$i][$j]=$insinfo[1];
				}
			}
			$foreign_table_ids[$tbl]=$frnkey_mx;
		}

	}
	$process_result="";
	foreach ($tables as $tbl) {
		if (array_sum($count[$tbl])==0) {
			print "\t$tbl: a freign table that has been taken care of previously\n";
			$process_result.=$tbl.": foreign;\n";
		}
		else {
			print "\t$tbl: inserted <span style='color:red'>".$count[$tbl][2]." </span>records; Updated <span style='color: blue'>".$count[$tbl][1]."</span> recordes; and ignored <span style='color: lime'>".$count[$tbl][0]."</span> records\n";
			$process_result.=$tbl.": "."inserted ".$count[$tbl][2].", Updated ".$count[$tbl][1].", and ignored ".$count[$tbl][0].";\n";
		}
	}

	/* logging the storing process */
	$_SESSION['fileProcessInfo']['process_program']=$_SERVER['PHP_SELF'];
	$_SESSION['fileProcessInfo']['process_result']=$process_result;
	store_file_process_info();
	unset($_SESSION['user_data_file']);
	unset($_SESSION['user_def_file']);
	print "<a href='".$config['base_url']."login'>Done</a>";
	print "</pre>";

}

/**
 * get the unique keys (will be implemented in the database later)
 */
function get_unique_keys () {
	$ukeys=array('datasets'=>array('dataset_name'),
				 'taxa'=>array('taxa_name'),
				 'line_records'=>array('line_record_name'),
				 'pedigree_relations'=>array('line_record_uid', 'parent_id'),
				 'tht_base'=>array('experiment_uid', 'line_record_uid'),
				 'genotyping_data'=>array('tht_base_uid', 'marker_uid'),
				 'mapset'=>array('mapset_name'),
				 'map'=>array('map_name'),
				 'markers_in_maps'=>array('marker_uid', 'map_uid'),
				 'marker_stat'=>array('marker_uid', 'datasets_uid'),
				 'unigene'=>array('unigene_name'),
				 'markers'=>array('marker_name'),
				 'marker_types'=>array('marker_type_name'),
				 'alleles'=>array('genotyping_data_uid'),
				 'genotyping_status'=>array('genotyping_status_name'),
				 'phenotype_category'=>array('phenotype_category_name'),
				 'units'=>array('unit_name'),
				 'phenotype_descstat'=>array('phenotype_uid'),
				 'gramene'=>array('term'),
				 'phenotypes'=>array('phenotypes_name', 'phenotype_category_uid'));
	return $ukeys;
}

function get_saa($xidx, $idx, $arr) {
	while (isset($arr[$idx][$xidx]) && strlen($arr[$idx][$xidx])>0 && (preg_match('/same\sas\sabove/i',$arr[$idx][$xidx])|| trim(strtolower($arr[$idx][$xidx])) == "saa") && $idx>2) {
		$idx--;
	}
	return $arr[$idx][$xidx];
}

/**
 * Handles login in ajax
 */
function ajaxLogin ($arr) {
	if($arr["loginstr"]=='') {
		echo "Invalid Input for Login";
	}
	else {
		preg_match('/text,(.*?);password,(.*?);/', $arr["loginstr"], $mts);
		$user=$mts[1];
		$pswd=$mts[2];
		if ( ($enc = login($user, $pswd) ) !== FALSE  ) {
			session_start();
			$_SESSION['username'] = $user;
			$_SESSION['password'] = $enc;
			$_SESSION['logintime'] = date("Y-m-d H:i:s");
			print "<br><br><br><br><input type='button' value='Logout $user' onClick='ajaxLogout()'>";

			// get the stored session variables
			$username=$_SESSION['username'];
			$svkeys=array("clicked_buttons", "selected_lines", "selected_traits");
			foreach ($svkeys as $svkey) {
				$mrks=retrieve_session_variables($svkey, $username);
				if (isset($mrks) && $mrks!== -1) $_SESSION[$svkey]=$mrks;
			}
		}
		else {
			print <<<_ULLOGIN
			<form id="upperleftlogin" method="post">
				<span>Username:</span><br />
				<input type="text" class="text" size="18" maxlength="32" name="username" />
				<span>Password:</span><br />
				<input type="password" class="text" size="18" maxlength="32" name="password" />
				<input type="button" value="Login" onClick="ajaxLogin()">
				<input type="button" value="Register!" onClick="display_reginput()" >
			</form>
_ULLOGIN;

		}
	}
}

/**
 * Handel logout in ajax
 */
function ajaxLogout () {
	session_start();
   	if(isLoggedIn($_SESSION['username'], $_SESSION['password'])) {
   		updateLastAccess($_SESSION['username'], $_SESSION['logintime']);
		session_destroy();	//destroy session array. Note "unset()" does not work with arrays.
   	}
	print <<<_ULLOGIN
	<form id="upperleftlogin" method="post">
		<span>Username:</span><br />
		<input type="text" class="text" size="18" maxlength="32" name="username" />
		<span>Password:</span><br />
		<input type="password" class="text" size="18" maxlength="32" name="password" />
		<input type="button" value="Login" onClick="ajaxLogin()">
		<input type="button" value="Register!" onClick="display_reginput()" >
	</form>
_ULLOGIN;
}

/**
 * Handle user registration in ajax
 */
function ajaxRegister($arr) {
	$flag=0; // $flag==1 if registration is successful
	$msg="Registration message";
	if($arr["regstr"]=='') {
		$msg = "Invalid Input for Login";
	}
	else {
		preg_match_all('/(.*?),(.*?);/', $arr["regstr"], $mts);
		$user=$mts[2][0];
		$pswd=$mts[2][1];
		$name=$mts[2][2];
		$email=$mts[2][3];
		$institute=$mts[2][4];
		$capmember=$mts[2][5];
		if(isset($user) && strlen($user)>0) {
			if(validateForm(array($user, $pswd, $name, $email, $institute, $capmember))) {
				if(checkForUser($user)) { //username is available
	   				if(checkGoodPassword($pswd)) { //password is acceptable
						addUser($user, $pswd, $capmember, $institute, $name, $email);
		   				$msg = "<p>You have successfully registered</p>";
		   				$msg.="<p><input type='button' value='OK' onClick='display_reginput()'></p>";
		   				$flag=1;
					}
					else {
		   				$msg = "Your Password must be at least 6 characters and contain at least 1 non-alphanumeric character.";
					}
   				}
   				else {
	   				$msg = "Sorry but that username is already taken. Please try a different name.";
   				}
   			}
   			else {
				$msg = "You must fill in all of the fields and provide a valid e-mail address. <br /> Example: name@domain.com";
   			}
		} else {
			$msg="Invalid User Name";
		}
	}
	// return the response in xml
	$dom = new DOMDocument("1.0");
	header("Content-Type: text/xml");

	// create root element
	$root = $dom->createElement("xmlresponse");
	$dom->appendChild($root);

	// create child element
	$item = $dom->createElement("register_success");
	$root->appendChild($item);
	// create text node
	$text = $dom->createTextNode($flag);
	$item->appendChild($text);

	$item2=$dom->createElement("register_message");
	$root->appendChild($item2);
	$text2=$dom->createTextNode($msg);
	$item2->appendChild($text2);

	// save and display tree
	// Output XML string
	echo $dom->saveXML();
}

/**
 * display the input form of a table
 */
function ajaxTableForm ($arr) {
	$flag=0; // flag=1 if error
	$form_str="";
	if (! isset($arr['tablename']) || strlen($arr['tablename'])<1) {
		$flag=1;
	}
	else {
		$tablename=$arr['tablename'];
		$tableinfo=get_table_info ($tablename);
		$reftable=chk_tbl_dep($tablename);
		$formname=$tablename."_inputform";
		$pkf=$tableinfo[0]['field'];
		$form_str.="<form method='post' enctype='multipart/form-data' id='$formname' onsubmit=\"return false\">";
		$form_str.="<table class=\"tableclass1\" id='dataInputTable'>";
		$form_str.="<thead><tr><td>field</td><td></td></tr></thead><tbody>";
		$firstrow="disabled";
		foreach ($tableinfo as $row_info) {
			$field_name=strtolower($row_info['field']);
			// if ($field_name=="longitude" || $field_name=="latitude") continue;
			if ($field_name=='created_on' || $field_name=='updated_on' || $field_name=='published_on') continue;
			if ((in_array($field_name, $reftable[1]))) {
				$ftbl=array_search($field_name, $reftable[1]);
				$optstr=get_selopts_str($ftbl);
				$form_str.="<tr><td>".$row_info['field']."<br>correspondent value</td><td><select name=\"".$field_name."\">$optstr</select></td></tr>";
			}
			elseif (preg_match('/^enum(.*)/',$row_info['type'], $emts)) {
				$form_str.="<tr><td>".$row_info['field']."<br>enum, input can only be ".$emts[1]."</td><td><input type='text' name=\"".$row_info['field']."\" $firstrow></td></tr>\n";
			}
			elseif (preg_match('/^(date|time)/i', $row_info['type'])) {
				$form_str.="<tr><td>".$row_info['field']."</td><td><input type='text' name=\"".$row_info['field']."\" value=\"20060000\" $firstrow></td></tr>";
			}
			else {
				$form_str.="<tr><td>".$row_info['field']."</td><td><input type='text' name=\"".$row_info['field']."\" $firstrow></td></tr>";
			}
			$firstrow="";
			$form_str.="<input type='hidden' name='".$row_info['field']."_isnum' value=".$row_info['isnum'].">";
		}
		$form_str.="</tbody></table></form>";
		$form_str.="<input type='hidden' name='".$tablename."_tblid' value=\"$tablename\" >";
		$form_str.="<input type='submit' value=\"Submit\" onClick='submitFormInput(\"$tablename\")'>";
		$form_str.="<input type=\"reset\" value=\"Clear\">";
		$form_str.="<input type='button' value=\"Cancel\" onClick=\"window.location='login/general_table_input.php'\">";

	}
	print $form_str;
	// return the response in xml
	/*
	$dom = new DOMDocument("1.0");
	header("Content-Type: text/xml");

	// create root element
	$root = $dom->createElement("xmlresponse");
	$dom->appendChild($root);

	// create child element
	$item = $dom->createElement("form_success");
	$root->appendChild($item);
	// create text node
	$text = $dom->createTextNode($flag);
	$item->appendChild($text);

	$item2=$dom->createElement("form_code");
	$root->appendChild($item2);
	$text2=$dom->createTextNode($form_str);
	$item2->appendChild($text2);

	// save and display tree
	// Output XML string
	echo $dom->saveXML();
	*/
}

/**
 * get the selection string, with the values of unique keys as the options
 */
function get_selopts_str($table) {
    global $mysqli;
	$uniquekeys=get_ukey($table);
	$ids="";
	for ($i=0; $i<count($uniquekeys); $i++) {
		$ids.=$uniquekeys[$i];
		if ($i!=count($uniquekeys)-1) {
			$ids.=",";
		}
	}
	$pid = get_pkey($table);
	if (strlen($ids)>3) {
		$ids.=", $pid";
		// array_push($uniquekeys, $pid);
	}
	else {
		$ids=$pid;
	}
	$result = mysqli_query($mysqli, "SELECT $ids FROM $table") or die("Error in SELECT $ids FROM $table"); // || mysql_error();
	$rstr="";
	$optcount=0;
	while($row = mysqli_fetch_assoc($result)) {
		$sel = implode(" ", array_splice($row, 0, count($uniquekeys)));
		$pidval=$row[$pid];
		$rstr.="\n\t<option value=\"$pidval\">$sel</option>";
		if ($optcount++>200) break; // maximum 200 options
	}
	return utf8_encode($rstr);
}

/**
 * submit the form
 */
function ajaxSubmitForm ($arr) {
	$flag=0; // flag>0 if error
	$submit_message;
	$tablename="";
	$flds=array();
	if (! isset($arr['formsubmitstr']) || strlen($arr['formsubmitstr'])<1) {
		$flag=1; // 1 -> no data
		$submit_message="No data defined in the form.";
	}
	else {
		/* put the form content into the flds array */
		$submit_pairs=explode(";",$arr['formsubmitstr']);
		foreach ($submit_pairs as $submit_pair) {
			$submit_content=explode(",", $submit_pair);
			if ($submit_content[0]=="tablename") {
				$tablename=$submit_content[1];
			}
			else {
				if (strlen($submit_content[0])>1) {
					$flds[$submit_content[0]]=implode(",", array_slice($submit_content, 1));
				}
			}
		}
	}
	if (! isset($tablename) || strlen($tablename)<1) {
		$flag=2; // 2 -> no table name
		$submit_message="Table name not defined.";
	}
	else {
		/* set up $vals $isnum and $ukeys and $uvals for data insertion */
		$tableinfo=get_table_info ($tablename);
		$vals=array();
		$isnum=array();
 		$ukeys=get_ukey($tablename);
		$uvals=array();
		foreach ($ukeys as $ukey) {
			if (! array_key_exists($ukey, $flds) || strlen($flds[$ukey])<1) {
				$flag=3; // 3 -> unique keys empty
				$submit_message="Unique keys can not be empty";
			}
			else {
				array_push($uvals, $flds[$ukey]);
			}
		}
		$pkey=get_pkey($tablename);
		foreach ($tableinfo as $row_info) {
			$field_name=strtolower($row_info['field']);
			if ($pkey == $field_name) continue;
			if(array_key_exists($field_name, $flds)) {
				$vals[$field_name]=$flds[$field_name];
				if (isset($flds[$field_name."_isnum"]) && $flds[$field_name."_isnum"]==1) {
					array_push($isnum, 1);
				}
				else {
					array_push($isnum, 0);
				}
			}
			elseif (preg_match('/ed_on$/', $field_name) && $field_name != "created_on" && $field_name !="updated_on") {
				$vals[$field_name]="now()";
				array_push($isnum, 1);
			}
		}
		if ($flag==0) {
			$result = add_array_data($vals, $isnum, $tablename, $ukeys, $uvals, $pkey, "force_update");
  			$outflag = $result[0]; $uid = $result[1];
//    			$submit_message="Submitted successfully.";
			//Debugging, in case "successfully" was a lie:
//  			$submit_message="add_array_data result code: $outflag<br>uid affected: $uid<br>";
//  			$submit_message .="conflict-flag: $result[2]<br>update-flag: $result[3]<br>";
//  			$submit_message .="testStr: $result[4]<br>";
			if ($outflag == 0) $submit_message = "Nothing was changed.";
			elseif ($outflag == 0.5) $submit_message = "The existing record was modified.";
			elseif ($outflag == 1) $submit_message = "The new record was added.";
		}
	}
	$submit_message.="<p><button type='button' onClick=\"window.location='login/general_table_input.php'>Return</button></p>";
    // return the response in xml
	$dom = new DOMDocument("1.0");
	header("Content-Type: text/xml");

	// create root element
	$root = $dom->createElement("xmlresponse");
	$dom->appendChild($root);

	// create child element
	$item = $dom->createElement("submit_success");
	$root->appendChild($item);
	// create text node
	$text = $dom->createTextNode($flag);
	$item->appendChild($text);

	$item2=$dom->createElement("submit_message");
	$root->appendChild($item2);
	$text2=$dom->createTextNode($submit_message);
	$item2->appendChild($text2);

	// save and display tree
	// Output XML string
	echo $dom->saveXML();
}

/**
 * Display the map range based on the map name
 */
function DispMapSel ($arr) {
    global $mysqli;
    if (! isset($arr['mapname']) || strlen($arr['mapname'])<1) {
	print "Invalid input of map name";
	return;
    }
    $mapname=$arr['mapname'];
    $result=mysqli_query($mysqli, "select map_uid from map where map_name=\"$mapname\"") or die("Invalid map name");
    if (mysqli_num_rows($result)>0) {
        $row=mysqli_fetch_assoc($result);
	$mapuid=$row['map_uid'];
	// print "select min(start_position), max(start_position) from markers_in_maps where map_uid=$mapuid";
	$res2=mysqli_query($mysqli, "select min(start_position), max(start_position) from markers_in_maps where map_uid=$mapuid");
	if (mysqli_num_rows($res2)>0) {
		$row=mysqli_fetch_assoc($res2);
		$mstt=round(array_shift($row));
		$mend=round(array_shift($row));
		print "Map start: $mstt<br>";
		print "Map end: $mend<br>";
		print "Range:<br>";
		print "From <input type='text' style='width:30px' id='mapstt' value=\"$mstt\"> ";
		print "to <input type='text' style='width:30px' id='mapend' value=\"$mend\"><br>";
		print "<input type='button' style=\"color: black\" value=\"Show markers\" onClick=\"DispMarkers('$mapuid')\"><br>";
	}
    }
}

function DispExperiment ($arr) {
    global $mysqli;
    if (! isset($arr['platform'])) {
        print "Invalid input of platform";
        return;
    } else {
        $platform = $arr['platform'];
    }
    ?>
    <table><tr><td><select name='expt[]' size=10 multiple onchange="javascript: update_exper(this.options)">
    <?php
    $result=mysqli_query($mysqli, "select experiments.experiment_uid, trial_code from experiments, genotype_experiment_info 
        where experiments.experiment_uid = genotype_experiment_info.experiment_uid
        and genotype_experiment_info.platform_uid IN ($platform)") or die(mysqli_error($mysqli));
    while ($row=mysqli_fetch_assoc($result)) {
        $uid=$row['experiment_uid'];
        $val=$row['trial_code'];
        print "<option value=$uid>$val</option>\n";
    }
    ?>
    </select>
    <td>Choose experiments.
    <p><input type=button value=Select style=color:blue onclick="javascript: select_exper()">
    </table>
    <?php
}

function SelcMarkerSet ($arr) {
    global $mysqli;
    if (! isset($arr['set'])) {
        print "Invalid input of marker panel";
        return;
    } else {
        $panel_str = $arr['set'];
    }
    echo "<h3>Currently selected markers</h3>";
    $sql = "select marker_ids from markerpanels where name = \"$panel_str\"";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    if ($row = mysqli_fetch_array($res)) {
        $mkruid=$row[0];
        $marker_list = explode(',', $mkruid);
        foreach ($marker_list as $uid) {
            $clkmkrs[] = $uid;
            $sql = "select marker_name from markers where marker_uid=$uid";
            $res=mysqli_query($mysqli, $sql) or die("invalid marker $sql\n");
            if ($row = mysqli_fetch_array($res)) {
                $name = $row[0];
            }
        }
        $_SESSION['clicked_buttons'] = $clkmkrs;
    }
    $markerlist = array();
    if ((count($_SESSION['clicked_buttons']) > 0) && (count($_SESSION['clicked_buttons']) < 1000)) {
        print "<form id='deselMkrsForm' action='genotyping/marker_selection.php' method='post'>";
        print "<table><tr><td>\n";
        print "<select name='deselMkrs[]' multiple='multiple' size=10>";
        foreach ($_SESSION['clicked_buttons'] as $mkruid) {
            $count_markers++;
            $sql = "select marker_name from markers where marker_uid=$mkruid";
            $result=mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
            while ($row=mysqli_fetch_assoc($result)) {
              $selval=$row['marker_name'];
              if(! in_array($selval,$markerlist)) {
                 array_push($markerlist, $selval);
                 print "<option value='$mkruid'>$selval</option>\n";
              }
            }
         }
         print "</select></table>";
         print "<p><input type='submit' value='Remove marker' style='color: blue' /></p>";
         print "</form>";
    }
    if (isset($_SESSION['clicked_buttons']) && (count($_SESSION['clicked_buttons']) > 0)) {
        $count = count($_SESSION['clicked_buttons']);
        print "$count markers selected. ";
        print "<a href=genotyping/display_markers.php>Download list of markers</a><br>\n";
    }
}

function SelcExperiment($arr)
{
    global $mysqli;
    $lines_unique = array();
    if (!isset($arr['experiment'])) {
        print "Invalid input of experiment";
        return;
    } else {
        $expt_str = $arr['experiment'];
        $expt = explode(",", $expt_str);
    }
    echo "<h3>Currently selected markers</h3>";
    $clkmkrs=array();
    $trial_code = "";
    $sql = "select trial_code from experiments
        where experiment_uid IN ($expt_str)";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row = mysqli_fetch_row($res)) {
        if ($trial_code == "") {
            $trial_code = $row[0];
        } else {
            $trial_code .= ", $row[0]";
        }
    }
    echo "Markers added from experiment(s) <b>$trial_code</b><p>";
    $_SESSION['geno_exps'] = $expt;
    $sql = "select distinct(marker_uid) from allele_bymarker_exp_101 where experiment_uid in ($expt_str)";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    $row_cnt = mysqli_num_rows($res);
    $_SESSION['geno_exps_cnt'] = $row_cnt;
    if ($row_cnt < 100000) {
        while ($row = mysqli_fetch_row($res)) {
            $clkmkrs[] = $row[0];
        }
        $_SESSION['clicked_buttons'] = $clkmkrs;
        print "$row_cnt markers selected. ";
    } else {
        unset($_SESSION['clicked_buttons']);
        print "$row_cnt markers in experiment<br>\n";
    }

    $sql = "select line_index from allele_bymarker_expidx where experiment_uid IN ($expt_str)";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    while ($row = mysqli_fetch_array($res)) {
        $lines = json_decode($row[0], true);
        //*check for duplicates
        foreach ($lines as $line_record) {
            if (isset($unique_list[$line_record])) {
                $skipped .= "$line_record ";
            } else {
                $lines_unique[] = $line_record;
                $unique_list[$line_record] = 1;
            }
        }
    }
    $_SESSION['selected_lines'] = $lines_unique;
    if ((count($_SESSION['clicked_buttons']) > 0) && (count($_SESSION['clicked_buttons']) < 1000)) {
        print "<form id='deselMkrsForm' action='".$_SERVER['PHP_SELF']."' method='post'>";
        print "<table><tr><td>\n";
        print "<select id='mlist' name='deselMkrs[]' multiple='multiple' size=10>";
        $mapids = $_SESSION['mapids'];
        if (!isset($mapids) || !is_array($mapids)) {
            $mapids = array();
        }
        reset($mapids);

        $chrlist = array();
        $markerlist = array();
        $count_markers = 0;
        foreach ($_SESSION['clicked_buttons'] as $mkruid) {
            $count_markers++;
            $mapid = current($mapids);
            next($mapids);
            $sql = "select marker_name from markers where marker_uid=$mkruid";
            $result=mysqli_query($mysqli, $sql)
            or die(mysqli_error($mysqli));
            while ($row=mysqli_fetch_assoc($result)) {
                $selval=$row['marker_name'];
                $selchr=$row['chromosome'];
                if (! in_array($selval, $markerlist)) {
                    array_push($markerlist, $selval);
                    array_push($chrlist, $selchr);
                    print "<option value='$mkruid'>$selval</option>\n";
                }
            }
        }
        $chrlist = array_unique($chrlist);
        print "</select></table>";
        //print "</td><td>\n";
    }
    print "<a href=genotyping/display_markers.php>Download selected markers</a><br>\n";
}

/**
 * Display markers in marker_selection.php
 */
function DispMarkers($arr)
{
    global $mysqli;
    if (! isset($arr['mapuid']) || ! isset($arr['mapstt']) || ! isset($arr['mapend'])) {
	print "Invalid inputs";
	return;
    }
    $mapuid=$arr['mapuid'];
    $mapstt=$arr['mapstt'];
    $mapend=$arr['mapend'];
    if (! is_numeric($mapstt) || ! is_numeric($mapend) || $mapstt>=$mapend) {
	print "Invalid inputs";
	return;
    }
    $result=mysqli_query($mysqli, "select marker_uid from markers_in_maps where map_uid=$mapuid and start_position between $mapstt and $mapend order by start_position");
	if (mysqli_num_rows($result)>0) {
		print "<select name=\"selMkrs[]\" multiple=\"multiple\" size=10>";
		while ($row=mysqli_fetch_assoc($result)) {
			$mkruid=$row['marker_uid'];
			$res2=mysqli_query($mysqli, "select marker_name from markers where marker_uid=$mkruid") or die("invalid marker uid\n");
			while ($row2=mysqli_fetch_assoc($res2)) {
				$selval=$row2['marker_name'];
				print "<option value=\"$mkruid\">$selval</option>\n";
			}
		}
		print "</select>";
		print "<p><input type='submit' value='Select markers' style='color: blue'>";

	}
}

function DispMarkerSet ($arr) {
    global $mysqli;
    if (! isset($arr)) {
        print "Invalid inputs";
        return;
    }
    $set = $arr["set"];
    ?>
    <p><input type=button value=Select style=color:blue onclick="javascript: select_set()">
    <?php
    $sql = "select marker_ids from markerpanels where name = \"$set\"";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    if ($row = mysqli_fetch_array($res)) {
        $mkruid=$row[0];
        $marker_list = explode(',', $mkruid);
        //print "<textarea disabled rows=10>";
        foreach ($marker_list as $uid) {
            $sql = "select marker_name from markers where marker_uid=$uid";
            $res=mysqli_query($mysqli, $sql) or die("invalid marker $sql\n");
            if ($row = mysqli_fetch_array($res)) {
                $name = $row[0];
         //       print "$name\n";
            }
        }
        //print "</textarea>";
    } else {
      print "$sql\n";
    }
}

/**
 * This function is used in advanced_search.php. It is the backend for the phenotype selection table, cell 1.
 */
function DispCategorySel($arr) {
    global $mysqli;
	if(! isset($arr['id']) || !is_numeric($arr['id']) ) {
		echo "Please Select A Category";
		return;
	}

	// make local the array variables please.
	extract($arr);

	// query please
	$query = mysqli_query($mysqli, "SELECT phenotype_uid, phenotypes_name FROM phenotypes WHERE phenotype_category_uid = $id AND datatype != 'string'") or die(mysqli_error($mysqli));

	// display in selection box please
	if(mysqli_num_rows($query) > 0) {
		echo "<select name='phenotype' size=10 onfocus=\"DispPhenoSel(this.value, 'Phenotype')\" onchange=\"DispPhenoSel(this.value, 'Phenotype')\">";
		while($row = mysqli_fetch_row($query)) {
			echo "\n\t<option value=$row[0]>$row[1]</option>";
		}
		echo "</select>";
	}
	else {
	  echo "<p style='color: red;'>There are no traits available for this category.</p>";
	}
}

/**
 * This function is used in advanced_search.php. It is the backend for the phenotype selection table, cell 2
 */
function DispPhenotypeSel($arr) {
    global $mysqli;
	if(! isset($arr['id']) || !is_numeric($arr['id']) ) {
		echo "Please Select A Trait";
		return;
	}
	// make local the array variables please.
	extract($arr);

	// Store the current phenotype in a cookie.
	$_SESSION['phenotype'] = $id;
	// No experiments selected yet so unset the cookie.
	unset($_SESSION['experiments']);

	$pquery = mysqli_query($mysqli, "SELECT phenotypes_name from phenotypes where phenotype_uid = $id") or die(mysqli_error($mysqli));
	$pname = mysqli_fetch_row($pquery);
	$pn = $pname[0];
	// Show only public trials unless signed in as at least Participant.
	if( authenticate( array( USER_TYPE_PARTICIPANT, USER_TYPE_CURATOR, USER_TYPE_ADMINISTRATOR ) ) )
	  $filter = "";
	else
	  $filter = " AND data_public_flag = 1";
        if ((count($_SESSION['selected_lines']) > 0) && ($_SESSION['selectWithin'] == "Yes")) {
	  $sql = "SELECT DISTINCT experiments.experiment_uid, trial_code
		    FROM experiments, line_records as lr, tht_base, phenotype_data pd
		    WHERE experiments.experiment_uid = tht_base.experiment_uid
		    and tht_base.tht_base_uid = pd.tht_base_uid
		    AND lr.line_record_uid = tht_base.line_record_uid
		    AND lr.line_record_uid IN (" . implode(",", $_SESSION['selected_lines']) . ")" .
		    "AND pd.phenotype_uid = $id $filter ORDER BY trial_code";
            $errMsg = "There are no public trials for this trait within selected lines.";
          $query = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        } else {
	  $query = mysqli_query($mysqli, "select distinct e.experiment_uid, trial_code
		    from tht_base tb, phenotype_data pd, experiments e
		    where pd.phenotype_uid = $id
		    and tb.tht_base_uid = pd.tht_base_uid
		    and tb.experiment_uid =  e.experiment_uid
		    $filter ORDER BY trial_code") or die(mysqli_error($mysqli));
          $errMsg = "There are no public trials for this trait.";
        }

	// display in selection box please
	if(mysqli_num_rows($query) > 0) {
		/* echo "<select name='trial[]' id='trialoptions' multiple size=10 onfocus=\"DispPhenoSel(this.value, 'Trial', $id)\" onchange=\"DispPhenoSel(this.value, 'Trial', $id)\" onmouseover=\"DispPhenoSel(this.value, 'Trial', $id)\">"; */
		echo "<select name='trial[]' id='trialoptions' multiple size=10 onfocus=\"DispPhenoSel(this.value, 'Trial', $id)\" onchange=\"DispPhenoSel(this.value, 'Trial', $id)\" >";
		while($row = mysqli_fetch_row($query)) {
			/* echo "\n\t<option value=$row[0] selected>$row[1]</option>"; */
			echo "\n\t<option value=$row[0]>$row[1]</option>";
		}  
		echo "</select>";
	}
	else {
	  echo "<p style='color: red;'>$errMsg</p>";
	}
}


/**
 * This function is used in advanced_search.php. It is the backend for the trial selection table, cell 3
 */
function DispTrialSel($arr) {
    global $mysqli;
    if(! isset($arr['id']) || !is_numeric($arr['id']) ) {
	echo "<br>No trials selected";
	return;
    }
    // make local the array variables please.  
    //$id = most recently clicked experiment_uid (selected or deselected). Ignore.
    //$phenotypeid = current phenotype_uid
    //$trialsSelected is a comma-separated list of all experiment_uid's currently selected.
    extract($arr);
    // Remove the trailing ",".
    $trialsSelected = trim($trialsSelected, ",");
    // Store it in a cookie.
    $_SESSION['experiments'] = $trialsSelected;

    $query = mysqli_query($mysqli, "select avg(value) as avg,
       stddev_samp(value) as std,
       count(value) as num,
       min(cast(value as decimal(10,4))) as min, 
       max(cast(value as decimal(10,4))) as max
from phenotypes, phenotype_data, tht_base, experiments
where phenotypes.phenotype_uid = $phenotypeid
and tht_base.experiment_uid = experiments.experiment_uid
and phenotype_data.tht_base_uid = tht_base.tht_base_uid
and phenotype_data.phenotype_uid = phenotypes.phenotype_uid
and experiments.experiment_uid IN ($trialsSelected)
") or die(mysqli_error($mysqli));

    if(mysqli_num_rows($query) > 0) {
        $row = mysqli_fetch_assoc($query);
        // Actually number_format should use units.sigdigits_display as done in compare.php.
        $avg = number_format($row['avg'],1);
        $std = number_format($row['std'],1);
        $num = $row['num'];
        $min = number_format($row['min'],1);
        $max = number_format($row['max'],1);

        echo "<b>Values</b><br>";
        echo "Mean: $avg &plusmn; $std, n = $num<br>";
        echo "Range: " . $min . " - " . $max;
        // number_format adds commas for thousands, and rounds. Better be inclusive by default.
       $min = floor(str_replace(",","",$min));
       $max = ceil(str_replace(",","",$max));
       echo "<p>Search between:<br> <input type='text' name='first_value' value=$min><br>and<br><input type='text' name='last_value' value=$max>";
       echo "<input type='hidden' name='phenoSearch'>";
       echo "<br><input type='submit' value='Search'></form>";

       // DLH R plotting for histogram      
        $phen_name = mysqli_query($mysqli, "select phenotypes_name,unit_name from phenotypes,units where phenotype_uid = $phenotypeid
                                        AND units.unit_uid = phenotypes.unit_uid;");
        $pname = mysqli_fetch_row($phen_name);
        $hist_query = mysqli_query($mysqli, "select value from phenotype_data as pd, experiments as e, tht_base
	  where phenotype_uid = $phenotypeid 
	  and tht_base.tht_base_uid = pd.tht_base_uid
	  and e.experiment_uid = tht_base.experiment_uid
          and e.experiment_uid in ($trialsSelected)"
				  ) or die(mysqli_error($mysqli));
        $x = 'x <- c(';
        while($row = mysqli_fetch_row($hist_query)) {
	  $x .= "$row[0],";
        }
        $x = trim($x, ",");
        $x .= ")";
	//        $date = date("Uu");
        $out = "jpeg(\\\"/tmp/tht/histogram.jpg\\\", width=150, height=200)";
        $title = "main='" . $pname[0] . "'";
	$xlab = "xlab='" . html_entity_decode($pname[1]) . "'";
        $rcmd = "hist(x,$title,$xlab)";
	exec("echo \"$x;$out;$rcmd\" | R --vanilla");
    } else {
        echo "<p style='color: red;'>There is no data available for this phenotype</p>";
    }
}

// Modified DispCategorySel() for Select Lines by Properties.
// $arr is a one-pair array('id' => phenotype_category_uid).
// Called by includes/core.js function DispPropSel(val, middle).
function DispPropCategorySel($arr) {
  global $mysqli;
  if(! isset($arr['id']) || !is_numeric($arr['id']) ) 
    echo "Please select a category.";
  else {
    extract($arr);
    $query = mysqli_query($mysqli, "SELECT properties_uid, name 
     FROM properties 
     WHERE phenotype_category_uid = $id 
     order by name") or die(mysqli_error($mysqli));
    if(mysqli_num_rows($query) > 0) {
      echo "<select name='property' size=5 
     onfocus=\"DispPropSel(this.value, 'Property')\" 
     onchange=\"DispPropSel(this.value, 'Property')\">";
      while($row = mysqli_fetch_row($query)) 
	echo "<option value=$row[0]>$row[1]</option>";
      echo "</select>";
    }
    else
      echo "<p style='color: red;'>Nothing available in this category.</p>";
  }
}

// Modified DispTrialSel() for Select Lines by Properties.
function DispPropertySel($arr) {
  global $mysqli;
  if(! isset($arr['id']) || !is_numeric($arr['id']) ) 
    echo "Please select a property.";
  else {
    extract($arr);
    $query = mysqli_query($mysqli, "SELECT property_values_uid, property_values.value 
     FROM property_values 
     WHERE property_uid = $id") or die(mysqli_error($mysqli));
    if(mysqli_num_rows($query) > 0) {
      // Strange.  (this.value..) works in IE and Chrome in DispPropCategorySel() but not here.
      echo "<select multiple size=3 onchange=\"javascript: update_propery(this.options)\">";
      while($row = mysqli_fetch_row($query)) 
	echo "<option value='$row[0]'>$row[1]</option>";
      echo "</select><br><br>";
      echo "<input type=\"button\" value=\"Add\" onclick=\"DispPropSel()\">";
    }
  }
}

// Modified DispPhenotypeSel() for Select Lines by Properties.
function DispPropValueSel($arr) {
  global $mysqli;
  if(! isset($arr['id']) || !is_numeric($arr['id']) ) 
    echo "Please select a value.";
  else {
  extract($arr);
  $query = mysqli_query($mysqli, "select name, value
     from property_values pv, properties pr
     where property_values_uid = $id
     and pr.properties_uid = pv.property_uid") or die (mysqli_error($mysqli));
  $row = mysqli_fetch_row($query);
  $count = count($_SESSION['propvals']);
  if ($count == 0) {
      echo "$row[0] = $row[1]";
  } else {
      echo " or $row[0] = $row[1]";
  }
  // Doesn't work:
  //echo "<input type=hidden name='charlie' value='bill'>";
  // All I can think of to return this value to line_properties.php is via cookie.
  $_SESSION['propvals'][] = array($id, $row[0], $row[1]);
  }
}

/**
 * store or retrieve session variables
 */
function ajaxSessionVariableFunc ($arr) {
	if (! isset($arr['action']) || strlen($arr['action'])<1) {
		die('Invalid input for ajaxSessionVarialbeFunc');
	}
	else {
		if ($arr['action']=="retrieve") {
			if (! isset($arr['svkey']) || strlen($arr['svkey'])<1) die('Invalid Session name');
			$svkey=$arr['svkey'];
			$username=$_SESSION['username'];
    		if (! isset($username) || strlen($username)<1) $username="Public";
    		$svval=retrieve_session_variables($svkey, $username);
				if (isset($svval) && $svval!== -1) $_SESSION[$svkey]=$svval;
				else die("No storeed session variable for $svkey and $username");
		}
		elseif ($arr['action']=="store") {
			if (! isset($arr['svkey']) || strlen($arr['svkey'])<1) die('Invalid Session name');
			$svkey=$arr['svkey'];
			$username=$_SESSION['username'];
    		if (! isset($username) || strlen($username)<1) $username="Public";
    		// print "Storing $svkey $username";
    		store_session_variables ($svkey, $username);
		}
		else {
			die("Invalid action option");
		}
	}
}
?>

