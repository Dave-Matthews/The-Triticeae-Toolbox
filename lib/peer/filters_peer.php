<?php
/**
 * Auto Generated Class
 * Contains methods for extracting rows from the table 'filters'
 */
class filters_peer
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
    protected static $base_sql = 'select filter_uid, users_uid, filter_set_uid, data from filters';


    // auto-generated method
  // get all records from db
  public static function get_all() {
    $results = array();
    $query = mysql_query(self::$base_sql);
    if (mysql_num_rows($query) <= 0) return $results;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $results[] =& new $modelname($row['filter_uid'], $row['users_uid'], $row['filter_set_uid'], $row['data']);
    }
    return $results;
  }

  
  # auto-generated function
  public static function get_by_filter_uid($arg0) {
    $sql = self::$base_sql.' where filter_uid = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['filter_uid'], $row['users_uid'], $row['filter_set_uid'], $row['data']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_filter_uid_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where filter_uid in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['filter_uid'], $row['users_uid'], $row['filter_set_uid'], $row['data']);
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
    $temp =& new $modelname($row['filter_uid'], $row['users_uid'], $row['filter_set_uid'], $row['data']);
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
      $temp =& new $modelname($row['filter_uid'], $row['users_uid'], $row['filter_set_uid'], $row['data']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_filter_set_uid($arg0) {
    $sql = self::$base_sql.' where filter_set_uid = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['filter_uid'], $row['users_uid'], $row['filter_set_uid'], $row['data']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_filter_set_uid_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where filter_set_uid in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['filter_uid'], $row['users_uid'], $row['filter_set_uid'], $row['data']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_data($arg0) {
    $sql = self::$base_sql.' where data = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['filter_uid'], $row['users_uid'], $row['filter_set_uid'], $row['data']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_data_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where data in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['filter_uid'], $row['users_uid'], $row['filter_set_uid'], $row['data']);
      $results[] = $temp;
    }
    return $results;
  }

  /* end-auto-gen */
}