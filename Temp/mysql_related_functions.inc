<?php

/**
 *	Functions for connecting and manipulating the MySQL database
 *
 */


/*
 * Function that get connected to a MySQL database
 * @params string host, string user, string password, string database
 * @return database object
 */

function get_dbh($host, $db_user, $db_pass, $database) {
	$link = mysql_connect($host, $db_user, $db_pass) || die('Could not connect: ' . mysql_error());
	mysql_select_db($database) || die('Could not select database $database');
	return $link;
}

?>