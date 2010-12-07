<?php

/*
 * Function that simplifies the connection process
 * Use this function if you want to connect to the database.
 *
 * NOTE: there are only 2 places in this entire application where the database name,
 *          database username, database password, and database location are found.
 *          This function is one of those locations. The other is in theme/admin_header.php
 *
 * @return $linkID - the mysql_connection resource pointer.  returned incase you ever need it.
 */
function connect_dev() {
	//global $dontconnect;
//	if ($dontconnect == true) return null;

	$database = "THT_database";
	$db_user = "tht";
	$db_pass = "wheat_2008";
	$host = "localhost";
	$linkID_dev = mysql_connect($host, $db_user, $db_pass);
	if(!$linkID_dev) {
		die(mysql_error());
	}
    	else {
		mysql_select_db($database, $linkID_dev);
	}
	return $linkID_dev;
}

?>
