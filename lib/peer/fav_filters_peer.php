<?php
/**
 * Auto Generated Class
 * Contains methods for extracting rows from the table 'fav_filters'
 */
class fav_filters_peer
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
    protected static $base_sql = 'select fav_filters_uid, users_uid, to_string, name from fav_filters';


    // auto-generated method
  // get all records from db
  public static function get_all() {
    $results = array();
    $query = mysql_query(self::$base_sql);
    if (mysql_num_rows($query) <= 0) return $results;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $results[] =& new $modelname($row['fav_filters_uid'], $row['users_uid'], $row['to_string'], $row['name']);
    }
    return $results;
  }

  
  # auto-generated function
  public static function get_by_fav_filters_uid($arg0) {
    $sql = self::$base_sql.' where fav_filters_uid = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['fav_filters_uid'], $row['users_uid'], $row['to_string'], $row['name']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_fav_filters_uid_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where fav_filters_uid in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['fav_filters_uid'], $row['users_uid'], $row['to_string'], $row['name']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_users_uid($arg0) {
    $sql = self::$base_sql.' where users_uid = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['fav_filters_uid'], $row['users_uid'], $row['to_string'], $row['name']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_users_uid_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where users_uid in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['fav_filters_uid'], $row['users_uid'], $row['to_string'], $row['name']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_to_string($arg0) {
    $sql = self::$base_sql.' where to_string = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['fav_filters_uid'], $row['users_uid'], $row['to_string'], $row['name']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_to_string_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where to_string in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['fav_filters_uid'], $row['users_uid'], $row['to_string'], $row['name']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_name($arg0) {
    $sql = self::$base_sql.' where name = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['fav_filters_uid'], $row['users_uid'], $row['to_string'], $row['name']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_name_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where name in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['fav_filters_uid'], $row['users_uid'], $row['to_string'], $row['name']);
      $results[] = $temp;
    }
    return $results;
  }

  /* end-auto-gen */
}