<?php

	if($_POST['confirm'] == "Yes") {

		$linkID = mysql_connect("lab.bcb.iastate.edu", "yhames04", "gdcb07") or die(mysql_error());
		mysql_select_db("sandbox_yhames04", $linkID) or die(mysql_error());

		/* We have connected */
		$safe = mysql_real_escape_string($_GET['table']);
		$query = mysql_query("DELETE FROM $safe") or die(mysql_error());

		echo "Successfully emptied the table: $safe <br /> <a href=\"viewtable.php?table=$safe\">View Table</a>";

	} else if($_POST['reject'] == "No") {

		header("Location: index.php");
		die();

	} else {

		echo "
		<form action=\"deleteTable.php?table=$_GET[table]\" method=\"post\">
		<p>Are you sure you want to do that?</p>
		<p><input type=\"submit\" name=\"confirm\" value=\"Yes\" /> <input type=\"submit\" name=\"reject\" value=\"No\" />";

	}
?>
