<?php


/*
 * Types:
 *	Warning - 0
 *	Fatal - 1
 *
 * This function displays errors encountered in the program. It handles them and displays them in a pretty format
 * 
 */
function error($type, $text) {

	switch($type) {

	case 0:
		echo "<p class=\"warning\"><strong>Warning:</strong> $text</p>\n";
		break;

	case 1:
		echo "<p class=\"error\"><strong>Error:</strong> $text</p>\n";
		break;

	}

}


/*
 * A wrapper for the error function
 */
function warning($text) {
	error(0, $text);
}

/*
 * This function does the same thing as error() except it returns a string instead of outputting to the
 * screen.
 */
function error_string($text) {
	return "<p class=\"error\"><strong>Error:</strong> $text</p>\n";
}


/*
 * This function handles mysql_errors, ignore for right now its not done.
 */
function dbError($duplicates=0) {

	$error = mysql_errno(); 
	$str = "";

	switch($error) {

	case 1062:	//duplicate entry
		$duplicates++;
		break;	

	default: 
		$str = error_string(mysql_error());	
	}

	return $str;

}

?>
