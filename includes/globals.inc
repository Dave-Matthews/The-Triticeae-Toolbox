<?php
/**
 * globals.inc
 *
 * Created on Aug 31, 2007
 */
  // require_once("bootstrap.inc");
connect();

define("BASEURL", $config['base_url']);
$user_types = array();

function load_user_types() {
  global $user_types;
  $sql = "select `user_types_uid`, `user_types_name`
from `user_types`";
  $result = mysql_query($sql) or die("<pre>" . mysql_error() .
				     "\n$sql.");
  while ($row = mysql_fetch_assoc($result))
    $user_types[$row['user_types_name']] = $row['user_types_uid'];
  if (!isset($user_types['public']) ||
      !isset($user_types['CAPprivate']) ||
      !isset($user_types['CAPcurator']) ||
      !isset($user_types['CAPadministrator']))
    die("user types not specified in the database");
  define("USER_TYPE_PUBLIC", $user_types['public']);
  define("USER_TYPE_PARTICIPANT", $user_types['CAPprivate']);
  define("USER_TYPE_CURATOR", $user_types['CAPcurator']);
  define("USER_TYPE_ADMINISTRATOR", $user_types['CAPadministrator']);
}
load_user_types();

function setting($name, $default=NULL) {
  $name = mysql_real_escape_string($name);
  $sql = "select value from settings where name='$name';";
  $result = mysql_query($sql) or die("<pre>" . mysql_error() .
				     "\n$sql");
  $row = mysql_fetch_assoc($result);
  if (!$row)
    if ($default === NULL)
      die("no value found for $name setting");
    else
      return $default;
  return $row['value'];
}

?>
