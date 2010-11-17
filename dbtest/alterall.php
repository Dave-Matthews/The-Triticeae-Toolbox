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

		//$query = mysql_query("ALTER TABLE $tables[$i] CHANGE created_on created_on TIMESTAMP NOT NULL DEFAULT 0") or die(mysql_error());
	}

?>
