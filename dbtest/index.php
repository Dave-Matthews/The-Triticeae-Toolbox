<?php

	$linkID = mysql_connect("lab.bcb.iastate.edu", "yhames04", "gdcb07") or die(mysql_error());
	mysql_select_db("sandbox_yhames04", $linkID) or die(mysql_error());

	/* We have connected */

	$tables = array();
	$i = 0;

	$query = mysql_query("SHOW TABLES");
	while($row = mysql_fetch_row($query)) {

		$tables[$i] = $row[0];
		$i++;

	}

	for($i=0; $i<count($tables); $i++) {

		$query = mysql_query("DESCRIBE $tables[$i]");
		$fc = 1;

		echo "<strong>$tables[$i]</strong>: <a href=\"viewtable.php?table=$tables[$i]\">View Contents</a> <br />";// ---- <a href=\"deleteTable.php?table=$tables[$i]\">Delete Contents</a> <br />";
		echo "<table border=\"1\">\n<tr>\n\t";

		while($row = mysql_fetch_assoc($query)) {

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
