<?php
/**
 * globals.inc
 *
 * Created on Aug 31, 2007
 */
  // require_once("bootstrap.inc");
$mysqli = connecti();

define("BASEURL", $config['base_url']);
$user_types = array();

function load_user_types()
{
    global $user_types, $mysqli;
    $sql = "select `user_types_uid`, `user_types_name`
      from `user_types`";
    $result = mysqli_query($mysqli, $sql) or die("<pre>" . mysqli_error($mysqli));
    while ($row = mysqli_fetch_assoc($result)) {
        $user_types[$row['user_types_name']] = $row['user_types_uid'];
    }
    if (!isset($user_types['public']) ||
        !isset($user_types['CAPprivate']) ||
        !isset($user_types['CAPcurator']) ||
        !isset($user_types['CAPadministrator'])) {
        die("user types not specified in the database");
    }
    define("USER_TYPE_PUBLIC", $user_types['public']);
    define("USER_TYPE_PARTICIPANT", $user_types['CAPprivate']);
    define("USER_TYPE_CURATOR", $user_types['CAPcurator']);
    define("USER_TYPE_ADMINISTRATOR", $user_types['CAPadministrator']);
}

load_user_types();

function setting($name, $default = null)
{
    global $mysqli;
    $sql = "select value from settings where name = ?";
    if ($stmt = mysqli_prepare($mysqli, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $name);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $result);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    } else {
        if ($default === null) {
            die("no value found for $name setting");
        } else {
            return $default;
        }
    }
}
