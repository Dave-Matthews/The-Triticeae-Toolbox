<?php
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/normal_header.php');
connect();

	$table = mysql_real_escape_string($_GET['table']);

	$query = mysql_query("SELECT * FROM $table");

	$fc = 1;

	echo "<strong>$table</strong>:<br />";
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

	echo "\n</tr>\n</table>";
	
?>