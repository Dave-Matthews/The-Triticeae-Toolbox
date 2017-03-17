
<?php
//***********************************************************
//
// 3/28/2011 Jlee Fix to prevent crashing browser on large tables   
// 3/25/2011 JLee fix to workin system 
//***********************************************************
require 'config.php';
/*
 * Logged in page initialization
 */
include $config['root_dir'] . 'includes/bootstrap_curator.inc';
include $config['root_dir'].'theme/normal_header.php';
$mysqli = connecti();
loginTest();

/* ************************************/

////////////////////////////////////////////////////////////////////////////////
ob_start();

authenticate_redirect(array(USER_TYPE_ADMINISTRATOR));
ob_end_flush();



//$linkID = mysql_connect("lab.bcb.iastate.edu", "yhames04", "gdcb07") or die(mysql_error());
//mysql_select_db("sandbox_yhames04", $linkID) or die(mysql_error());

	/* We have connected */

	$tables = array();
	$i = 0;

	$query = mysqli_query($mysqli, "SHOW TABLES");
	while($row = mysqli_fetch_row($query)) {

		$tables[$i] = $row[0];
		$i++;

	}

	for($i=0; $i<count($tables); $i++) {

		$query = mysqli_query($mysqli, "DESCRIBE $tables[$i]");
		$fc = 1;

		echo "<strong>$tables[$i]</strong>: <a href=\"dbtest/viewtable.php?table=$tables[$i]&start=0 \">View Contents</a> <br />";// ---- <a href=\"deleteTable.php?table=$tables[$i]\">Delete Contents</a> <br />";
		echo "<table border=\"1\">\n<tr>\n\t";

		while($row = mysqli_fetch_assoc($query)) {

			if($fc == 1) {

				foreach($row as $k=>$v) {
					echo "\n\t\t<td><strong>$k</strong></td>";
				}
			}

			echo "\n</tr>\n<tr>\n\t";

			foreach($row as $k=>$v) {
				
				echo "\n\t\t<td>$v</td>";

			}
			$fc++;
		}

		echo "\n</tr>\n</table>\n<br />";
	}

?>


