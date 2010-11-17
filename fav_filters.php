<?php
include("includes/bootstrap.inc");
connect();

include("cookie/cookie.php");
$mycookie = new MyCookie($_SESSION['username']);

$mycookie->myfilters = $_GET['tostring'];
$mycookie->to_file();

// get user id
$user = mysql_fetch_row(mysql_query('SELECT users_uid FROM users WHERE users_name = \'' . $_SESSION['username'] . '\'')) or die(mysql_error());
// store to database
$query = mysql_query('SELECT * FROM fav_filters WHERE to_string = \''. $_GET['tostring'] .'\'') or die(mysql_error());
$rows = intval(mysql_num_rows($query));
if ($rows == 0){
	mysql_query('INSERT INTO fav_filters (users_uid, to_string, name) VALUES (' . intval($user[0]) . ', \'' . $_GET['tostring'] .'\', \'' . $_POST['filter_name'] . '\')') or die(mysql_error());
}
