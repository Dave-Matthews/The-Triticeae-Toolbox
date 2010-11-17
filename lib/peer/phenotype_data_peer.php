<?php
/**
 * Auto Generated Class
 * Contains methods for extracting rows from the table 'phenotype_data'
 */
class phenotype_data_peer
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
    protected static $base_sql = 'select phenotype_data_uid, phenotype_uid, tht_base_uid, phenotype_data_name, value, recording_date, updated_on, created_on from phenotype_data';


    // auto-generated method
  // get all records from db
  public static function get_all() {
    $results = array();
    $query = mysql_query(self::$base_sql);
    if (mysql_num_rows($query) <= 0) return $results;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $results[] =& new $modelname($row['phenotype_data_uid'], $row['phenotype_uid'], $row['tht_base_uid'], $row['phenotype_data_name'], $row['value'], $row['recording_date'], $row['updated_on'], $row['created_on']);
    }
    return $results;
  }

  
  # auto-generated function
  public static function get_by_phenotype_data_uid($arg0) {
    $sql = self::$base_sql.' where phenotype_data_uid = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['phenotype_data_uid'], $row['phenotype_uid'], $row['tht_base_uid'], $row['phenotype_data_name'], $row['value'], $row['recording_date'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_phenotype_data_uid_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where phenotype_data_uid in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['phenotype_data_uid'], $row['phenotype_uid'], $row['tht_base_uid'], $row['phenotype_data_name'], $row['value'], $row['recording_date'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_phenotype_uid($arg0) {
    $sql = self::$base_sql.' where phenotype_uid = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['phenotype_data_uid'], $row['phenotype_uid'], $row['tht_base_uid'], $row['phenotype_data_name'], $row['value'], $row['recording_date'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_phenotype_uid_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where phenotype_uid in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['phenotype_data_uid'], $row['phenotype_uid'], $row['tht_base_uid'], $row['phenotype_data_name'], $row['value'], $row['recording_date'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_tht_base_uid($arg0) {
    $sql = self::$base_sql.' where tht_base_uid = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['phenotype_data_uid'], $row['phenotype_uid'], $row['tht_base_uid'], $row['phenotype_data_name'], $row['value'], $row['recording_date'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_tht_base_uid_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where tht_base_uid in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['phenotype_data_uid'], $row['phenotype_uid'], $row['tht_base_uid'], $row['phenotype_data_name'], $row['value'], $row['recording_date'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_phenotype_data_name($arg0) {
    $sql = self::$base_sql.' where phenotype_data_name = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['phenotype_data_uid'], $row['phenotype_uid'], $row['tht_base_uid'], $row['phenotype_data_name'], $row['value'], $row['recording_date'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_phenotype_data_name_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where phenotype_data_name in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['phenotype_data_uid'], $row['phenotype_uid'], $row['tht_base_uid'], $row['phenotype_data_name'], $row['value'], $row['recording_date'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_value($arg0) {
    $sql = self::$base_sql.' where value = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['phenotype_data_uid'], $row['phenotype_uid'], $row['tht_base_uid'], $row['phenotype_data_name'], $row['value'], $row['recording_date'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_value_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where value in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['phenotype_data_uid'], $row['phenotype_uid'], $row['tht_base_uid'], $row['phenotype_data_name'], $row['value'], $row['recording_date'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_recording_date($arg0) {
    $sql = self::$base_sql.' where recording_date = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['phenotype_data_uid'], $row['phenotype_uid'], $row['tht_base_uid'], $row['phenotype_data_name'], $row['value'], $row['recording_date'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_recording_date_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where recording_date in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['phenotype_data_uid'], $row['phenotype_uid'], $row['tht_base_uid'], $row['phenotype_data_name'], $row['value'], $row['recording_date'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_updated_on($arg0) {
    $sql = self::$base_sql.' where updated_on = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['phenotype_data_uid'], $row['phenotype_uid'], $row['tht_base_uid'], $row['phenotype_data_name'], $row['value'], $row['recording_date'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_updated_on_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where updated_on in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['phenotype_data_uid'], $row['phenotype_uid'], $row['tht_base_uid'], $row['phenotype_data_name'], $row['value'], $row['recording_date'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_created_on($arg0) {
    $sql = self::$base_sql.' where created_on = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['phenotype_data_uid'], $row['phenotype_uid'], $row['tht_base_uid'], $row['phenotype_data_name'], $row['value'], $row['recording_date'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_created_on_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where created_on in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['phenotype_data_uid'], $row['phenotype_uid'], $row['tht_base_uid'], $row['phenotype_data_name'], $row['value'], $row['recording_date'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  /* end-auto-gen */
}