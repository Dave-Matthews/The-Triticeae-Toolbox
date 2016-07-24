<?php
/**
 * login/ajaxfunctions.php, dem jul2013
 * PHP functions that are called via html GET by javascript functions in this directory.
 */

require 'config.php';
require "$config[root_dir]/includes/bootstrap_curator.inc";
$link = connecti();

$functioncall = $_GET[func];
unset($_GET['func']);   //removing function name                                                            
call_user_func($functioncall, $_GET);
// Close mysql connection to prevent overloading.
mysqli_close($link);


// called by line_panels.php pickpanel()
function dispDesc($args) {
  $panelid = $args[panelid];
  $desc = mysql_grab("select comment from linepanels where linepanels_uid = $panelid");
  echo $desc;
}

?>
