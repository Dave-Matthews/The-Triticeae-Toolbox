<?php
/**
 * Auto Generated Class
 * Contains methods for extracting rows from the table 'barley_pedigree_catalog'
 */
class barley_pedigree_catalog_peer
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
    protected static $base_sql = 'select barley_pedigree_catalog_uid, barley_pedigree_catalog_name, vurv_num, updated_on, created_on from barley_pedigree_catalog';


    // auto-generated method
  // get all records from db
  public static function get_all() {
    $results = array();
    $query = mysql_query(self::$base_sql);
    if (mysql_num_rows($query) <= 0) return $results;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $results[] =& new $modelname($row['barley_pedigree_catalog_uid'], $row['barley_pedigree_catalog_name'], $row['vurv_num'], $row['updated_on'], $row['created_on']);
    }
    return $results;
  }

  
  # auto-generated function
  public static function get_by_barley_pedigree_catalog_uid($arg0) {
    $sql = self::$base_sql.' where barley_pedigree_catalog_uid = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['barley_pedigree_catalog_uid'], $row['barley_pedigree_catalog_name'], $row['vurv_num'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_barley_pedigree_catalog_uid_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where barley_pedigree_catalog_uid in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['barley_pedigree_catalog_uid'], $row['barley_pedigree_catalog_name'], $row['vurv_num'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_barley_pedigree_catalog_name($arg0) {
    $sql = self::$base_sql.' where barley_pedigree_catalog_name = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['barley_pedigree_catalog_uid'], $row['barley_pedigree_catalog_name'], $row['vurv_num'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_barley_pedigree_catalog_name_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where barley_pedigree_catalog_name in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['barley_pedigree_catalog_uid'], $row['barley_pedigree_catalog_name'], $row['vurv_num'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_vurv_num($arg0) {
    $sql = self::$base_sql.' where vurv_num = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['barley_pedigree_catalog_uid'], $row['barley_pedigree_catalog_name'], $row['vurv_num'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_vurv_num_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where vurv_num in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['barley_pedigree_catalog_uid'], $row['barley_pedigree_catalog_name'], $row['vurv_num'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['barley_pedigree_catalog_uid'], $row['barley_pedigree_catalog_name'], $row['vurv_num'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['barley_pedigree_catalog_uid'], $row['barley_pedigree_catalog_name'], $row['vurv_num'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['barley_pedigree_catalog_uid'], $row['barley_pedigree_catalog_name'], $row['vurv_num'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['barley_pedigree_catalog_uid'], $row['barley_pedigree_catalog_name'], $row['vurv_num'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  /* end-auto-gen */
}