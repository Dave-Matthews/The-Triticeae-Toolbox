<?php
/**
 * Auto Generated Class
 * Contains methods for extracting rows from the table 'mapset'
 */
class mapset_peer
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
    protected static $base_sql = 'select mapset_uid, mapset_name, species, map_type, map_unit, published_on, updated_on, created_on from mapset';


    // auto-generated method
  // get all records from db
  public static function get_all() {
    $results = array();
    $query = mysql_query(self::$base_sql);
    if (mysql_num_rows($query) <= 0) return $results;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $results[] =& new $modelname($row['mapset_uid'], $row['mapset_name'], $row['species'], $row['map_type'], $row['map_unit'], $row['published_on'], $row['updated_on'], $row['created_on']);
    }
    return $results;
  }

  
  # auto-generated function
  public static function get_by_mapset_uid($arg0) {
    $sql = self::$base_sql.' where mapset_uid = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['mapset_uid'], $row['mapset_name'], $row['species'], $row['map_type'], $row['map_unit'], $row['published_on'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_mapset_uid_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where mapset_uid in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['mapset_uid'], $row['mapset_name'], $row['species'], $row['map_type'], $row['map_unit'], $row['published_on'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_mapset_name($arg0) {
    $sql = self::$base_sql.' where mapset_name = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['mapset_uid'], $row['mapset_name'], $row['species'], $row['map_type'], $row['map_unit'], $row['published_on'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_mapset_name_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where mapset_name in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['mapset_uid'], $row['mapset_name'], $row['species'], $row['map_type'], $row['map_unit'], $row['published_on'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_species($arg0) {
    $sql = self::$base_sql.' where species = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['mapset_uid'], $row['mapset_name'], $row['species'], $row['map_type'], $row['map_unit'], $row['published_on'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_species_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where species in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['mapset_uid'], $row['mapset_name'], $row['species'], $row['map_type'], $row['map_unit'], $row['published_on'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_map_type($arg0) {
    $sql = self::$base_sql.' where map_type = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['mapset_uid'], $row['mapset_name'], $row['species'], $row['map_type'], $row['map_unit'], $row['published_on'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_map_type_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where map_type in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['mapset_uid'], $row['mapset_name'], $row['species'], $row['map_type'], $row['map_unit'], $row['published_on'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_map_unit($arg0) {
    $sql = self::$base_sql.' where map_unit = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['mapset_uid'], $row['mapset_name'], $row['species'], $row['map_type'], $row['map_unit'], $row['published_on'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_map_unit_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where map_unit in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['mapset_uid'], $row['mapset_name'], $row['species'], $row['map_type'], $row['map_unit'], $row['published_on'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_published_on($arg0) {
    $sql = self::$base_sql.' where published_on = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['mapset_uid'], $row['mapset_name'], $row['species'], $row['map_type'], $row['map_unit'], $row['published_on'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_published_on_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where published_on in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['mapset_uid'], $row['mapset_name'], $row['species'], $row['map_type'], $row['map_unit'], $row['published_on'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['mapset_uid'], $row['mapset_name'], $row['species'], $row['map_type'], $row['map_unit'], $row['published_on'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['mapset_uid'], $row['mapset_name'], $row['species'], $row['map_type'], $row['map_unit'], $row['published_on'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['mapset_uid'], $row['mapset_name'], $row['species'], $row['map_type'], $row['map_unit'], $row['published_on'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['mapset_uid'], $row['mapset_name'], $row['species'], $row['map_type'], $row['map_unit'], $row['published_on'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  /* end-auto-gen */
}