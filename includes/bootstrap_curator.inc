<?php

# Function connect() below requires the connection information in
# /data/tht_data/mysql_connections/T3wheat_writeaccess.inc

if (!isset($dontconnect))
$dontconnect = false;

error_reporting(E_ERROR | E_WARNING | E_PARSE);

session_start();
/**
 * Site-wide maintenance switch
 *
 * The $maintenance variable represents whether the site is undergoing site-wide
 * maintenance. If true, then you will not be able to access the site until you
 * attach ?maintenence=override to the url. You only have to do this once. If
 * false, everyone has access to the site as normal.
 *
 */
$maintenance = false;

/******************************************************************************/
if (!isset($_SESSION['maintenance_override'])) {
	if (isset($_GET['maintenance']) && $_GET['maintenance'] == 'override')
		$_SESSION['maintenance_override'] = true;
	else
		$_SESSION['maintenance_override'] = false;
}
else if ($_SESSION['maintenance_override'] === false)
	if (isset($_GET['maintenance']) && $_GET['maintenance'] == 'override')
		$_SESSION['maintenance_override'] = true;

if ($maintenance && $_SESSION['maintenance_override'] !== true)
	die("<h1>This site is currently undergoing maintenance. Please check back later. Thank you.</h1>");
if (!$maintenance && isset($_SESSION['maintenance_override']))
	unset($_SESSION['maintenance_override']);
/******************************************************************************/

if (!(isset($config['base_url']) && isset($config['root_dir']))) {
	require 'config.php';
}

/****************************************************
 * Initialization
 *
 * include all files in the includes directory
 */

	// universal libraries
	require('common.inc');
	require('error.inc');
	require('login.inc');
	//require($config['root_dir'].'includes/model.inc');

	// specific libraries
	require('general.inc');
	require('traits.inc');
	require('pedigree.inc');
	require('image.inc');
    require('marker.inc');
	require('search.inc');
	require('globals.inc');



/*
 * Applying Security Filters
 *
 *	SQL Injection       - using mysql_real_escape_string
 *	XSS 		           - completely stripping out all html, css, comments, AND javascript
 *      Cleanliness          - using trim()
 *
 * Whenever bootstrap is called, this segment runs on $_POST, $_GET, and $_REQUEST.
 * It does a nice job of filtering nearly all XSS and Injection attacks that I could muster.
 *
 * It does NOT run inside of arrays, so avoid doing this "<input type="text" name="ref[]" />"
 *
 * Also do not get rid of the is_array if statement. That will cause very annoying bugs.
 */

	if($dontconnect == FALSE || !isset($dontconnect)){
		$initial_connect = connect();

	foreach($_POST as $k=>$v) {

		if(!is_array($v)) {
			$v = htmlentities(trim($v));
			$_POST[$k] = mysql_real_escape_string($v);
		}
	}
	foreach($_REQUEST as $k=>$v) {

		if(!is_array($v)) {
			$v = html2txt(trim($v));
			$_REQUEST[$k] = mysql_real_escape_string(trim($v));
		}
	}
	foreach($_GET as $k=>$v) {

		if(!is_array($v)) {
			$v = html2txt(trim($v));;
			$_GET[$k] = mysql_real_escape_string(trim($v));
		}
	}

		mysql_close($initial_connect);
	}


/*
 * Applying Word Filter
 */

//	Comment out if you want to get rid of the censoring.
	censor();

//	Starting the Session, why? because everything uses the damn session array
	if(session_test())
	{
		session_start();
	}

// 	Comment out if you don't want to set the memory limit
//	ini_set("memory_limit","36M");




/*
 *  End of Initialization
 ***************************************************/


function array_merge_recursive_unique($array0, $array1)
{
   $arrays = func_get_args();
   $remains = $arrays;

   // We walk through each arrays and put value in the results (without
   // considering previous value).
   $result = array();

   // loop available array
   foreach($arrays as $array) {

       // The first remaining array is $array. We are processing it. So
       // we remove it from remaing arrays.
       array_shift($remains);

       // We don't care non array param, like array_merge since PHP 5.0.
       if(is_array($array)) {
           // Loop values
           foreach($array as $key => $value) {
               if(is_array($value)) {
                   // we gather all remaining arrays that have such key available
                   $args = array();
                   foreach($remains as $remain) {
                       if(array_key_exists($key, $remain)) {
                           array_push($args, $remain[$key]);
                       }
                   }

                   if(count($args) > 2) {
                       // put the recursion
                       $result[$key] = call_user_func_array(__FUNCTION__, $args);
                   } else {
                       foreach($value as $vkey => $vval) {
                           $result[$key][$vkey] = $vval;
                       }
                   }
               } else {
                   // simply put the value
                   $result[$key] = $value;
               }
           }
       }
   }
   return $result;
}



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
print_r($ini_config);
function connect() {
  global $dontconnect;
  $ini_path = dirname(__FILE__) . '/tht.ini';
  $ini_config = is_readable($ini_path) ?
    parse_ini_file($ini_path, false):array();
  if ($dontconnect == true) return null;
  // Get $db_config = array(db_name,db_user,db_pass,db_host).
  include('/data/tht_data/mysql_connections/T3wheat_writeaccess.inc');
  $db_config = array_merge_recursive_unique($db_config,
					    $ini_config);
  extract($db_config);
  //$linkID = mysql_connect($db_host, $db_user, $db_pass);
  //if(!$linkID)
  //  die(mysql_error());
  //else {
  //  mysql_select_db($db_name, $linkID);
  //  mysql_query("set SESSION sql_mode='STRICT_ALL_TABLES'") or die(mysql_error());
  //}
  return $linkID;
}

/*
 * MySQL improved extension, it is strongly recommended that you use this extension.
 * object-oriented, support for prepared statements, support for multiple statements, support for transactions, enhanced debug
 *
 * @return $linkID
 */
function connecti() {
  global $dontconnect;
  $ini_path = dirname(__FILE__) . '/tht.ini';
  $ini_config = is_readable($ini_path) ?
    parse_ini_file($ini_path, false):array();
  if ($dontconnect == true) return null;
  // Get $db_config = array(db_name,db_user,db_pass,db_host).
  include('/data/tht_data/mysql_connections/T3wheat_writeaccess.inc');
  $db_config = array_merge_recursive_unique($db_config,
                                            $ini_config);
  extract($db_config);
  $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
  if ($mysqli->connect_errno) {
    die($mysqli->connect_errno() . " " . $mysqli->connect_error);
  } else {
    $mysqli->query("set SESSION sql_mode='STRICT_ALL_TABLES'") or die(mysql_error());
  }
  return $mysqli;
}

/*
 * This function will test to ensure that a user has logged in before they access
 * the page. If the user is not logged in, then he/she is redirected to the forbidden page.
 *
 * Just stick this function in the page if you want it to be a password protected page.
 */
function loginTest() {
	// Has the Session been started?
	if(session_test()) {
		session_start();
	}
	if(!isLoggedIn($_SESSION['username'], $_SESSION['password'])) {
	  header("Location: ".$config['base_url']."403.php");
	  die();
	}

}

/**
 * @author Gavin Monroe
 */
function loginTest2() {
	if(session_test()) {
		session_start();
	}
	return isLoggedIn($_SESSION['username'], $_SESSION['password']);
}




/*
 * This is the censoring function. This censors all inappropriate words
 * out of the input.
 *
 * Note: this is not an indepth word filter. It takes care of simple childish
 *	 things but doesn't cover every bad word in the english language.
 *	 Feel free to add.
 *
 * Also: the word "sex" is in a line record name, so do not filter.
 */
function censor() {

	$words = array('shit', 'bitch', 'blowjob', 'clit', 'fuck', 'ass',
			'asshole', 'b!tch', 'b1tch', 'bastard', 'bi+ch',
			'boiolas','buceta', 'c0ck','cawk', 'chink',
			'clits', 'cock','cum','cunt','dildo','dirsa','ejakulate',
			'fatass', 'fcuk','fuk','fux0r', 'hoer', 'hore', 'jism',
			'kawk', 'l3itch', 'l3i+ch', 'lesbian','masturbate',
			'masterbat*', 'masterbat3','motherfucker', 's.o.b.', 'mofo',
			'nazi', 'nigga', 'nigger', 'nutsack',  'phuck', 'pimpis',
			'pusse', 'pussy', 'scrotum','sh!t','shemale','shit',
			'shi+','sh!+', 'slut', 'smut', 'teets','tits', 'boobs', 'b00bs',
			'teez','testical', 'testicle', 'tit','w00se','jackoff',
			'wank','whoar','whore');

	/*
	 * Place the / wrappers around each word, add case insensitive searching and place a
	 * space behind each word so as not to replace words inside of words
	 */
	foreach($words as $k=>$v) {
		$words[$k] = "/ " . $v . " /i";
	}

	$replace = array_fill(0, count($words), "[censored]");

	// Walking through the globals....
	foreach($_POST as $k=>$v) {
		$_POST[$k] = preg_replace($words, $replace, $v);
	}
	foreach($_REQUEST as $k=>$v) {
		$_REQUEST[$k] = preg_replace($words, $replace, $v);
	}
	foreach($_GET as $k=>$v) {
		$_GET[$k] = preg_replace($words, $replace, $v);
	}

}

/*
 * This function completely cleans all html out of a string including
 * javascript, style tags, and multiline comments including CDATA comments
 *
 * Note: this function was written by "admin at automapit dot com", modified by
 *      "uersoy at tnn dot net" and can be found
 * 	 http://us2.php.net/manual/en/function.strip-tags.php#71962
 *
 */
function html2txt($document){

	$search = array(
		'@<script[^>]*?>.*?</script>@si',   // Strip out javascript
                '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
                '@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
                '@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments including CDATA
	);

	$text = preg_replace($search, '', $document);
	return $text;

}











/*
 * This function tests whether or not a session has been started
 * this is pretty much just a formality, session_start() doesn't require this function.
 *
 * @returns TRUE if the session has been started, FALSE if not.
 */
function session_test() {
	return (session_id() == "");
}

/*
 * This session cleans up extra session variables that don't need to be there.
 * These are mostly leftovers from various places.
 */
function tht_session_cleanup() {

	/* Safely close the session */
	if(! session_test()) {

		// unregister the PMA session variables
		session_unregister("PMA_Config");
		session_unregister("PMA_Theme_Manager");
		session_unregister("PMA_Theme");
		session_unregister(" PMA_token ");

		//safely close session
		session_write_close();
	}

}

/**
 * Updated method of authenticating users.
 * @author Gavin Monroe
 */
function authenticate_redirect($allowed_user_types){
	if(!authenticate($allowed_user_types)){
	  header("Location: ".$config['base_url']."401.php");
	  die();
	}
}

/**
 * Update method of authenticating users.
 * @author Gavin Monroe
 */
function authenticate($allowed_user_types){
  	connect();
	if(loginTest2()){
		$row = loadUser($_SESSION['username']);
		$user_type_id = $row['user_types_uid'];
	}else{
		$user_type_id = USER_TYPE_PUBLIC;
	}
	return in_array($user_type_id, $allowed_user_types);
}


define("ROOTURL", "http://localhost"); /* I'd like to rewrite some scripts so that they no longer depend on this */

?>
