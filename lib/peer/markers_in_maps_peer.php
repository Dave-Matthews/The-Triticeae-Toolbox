<?php
/**
 * Auto Generated Class
 * Contains methods for extracting rows from the table 'markers_in_maps'
 */
class markers_in_maps_peer
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
    protected static $base_sql = 'select markers_in_maps_uid, marker_uid, map_uid, start_position, end_position, bin_name, chromosome, updated_on, created_on from markers_in_maps';


    // auto-generated method
  // get all records from db
  public static function get_all() {
    $results = array();
    $query = mysql_query(self::$base_sql);
    if (mysql_num_rows($query) <= 0) return $results;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $results[] =& new $modelname($row['markers_in_maps_uid'], $row['marker_uid'], $row['map_uid'], $row['start_position'], $row['end_position'], $row['bin_name'], $row['chromosome'], $row['updated_on'], $row['created_on']);
    }
    return $results;
  }

  
  # auto-generated function
  public static function get_by_markers_in_maps_uid($arg0) {
    $sql = self::$base_sql.' where markers_in_maps_uid = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['markers_in_maps_uid'], $row['marker_uid'], $row['map_uid'], $row['start_position'], $row['end_position'], $row['bin_name'], $row['chromosome'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_markers_in_maps_uid_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where markers_in_maps_uid in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['markers_in_maps_uid'], $row['marker_uid'], $row['map_uid'], $row['start_position'], $row['end_position'], $row['bin_name'], $row['chromosome'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_marker_uid($arg0) {
    $sql = self::$base_sql.' where marker_uid = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['markers_in_maps_uid'], $row['marker_uid'], $row['map_uid'], $row['start_position'], $row['end_position'], $row['bin_name'], $row['chromosome'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_marker_uid_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where marker_uid in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['markers_in_maps_uid'], $row['marker_uid'], $row['map_uid'], $row['start_position'], $row['end_position'], $row['bin_name'], $row['chromosome'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_map_uid($arg0) {
    $sql = self::$base_sql.' where map_uid = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['markers_in_maps_uid'], $row['marker_uid'], $row['map_uid'], $row['start_position'], $row['end_position'], $row['bin_name'], $row['chromosome'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_map_uid_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where map_uid in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['markers_in_maps_uid'], $row['marker_uid'], $row['map_uid'], $row['start_position'], $row['end_position'], $row['bin_name'], $row['chromosome'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_start_position($arg0) {
    $sql = self::$base_sql.' where start_position = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['markers_in_maps_uid'], $row['marker_uid'], $row['map_uid'], $row['start_position'], $row['end_position'], $row['bin_name'], $row['chromosome'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_start_position_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where start_position in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['markers_in_maps_uid'], $row['marker_uid'], $row['map_uid'], $row['start_position'], $row['end_position'], $row['bin_name'], $row['chromosome'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_end_position($arg0) {
    $sql = self::$base_sql.' where end_position = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['markers_in_maps_uid'], $row['marker_uid'], $row['map_uid'], $row['start_position'], $row['end_position'], $row['bin_name'], $row['chromosome'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_end_position_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where end_position in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['markers_in_maps_uid'], $row['marker_uid'], $row['map_uid'], $row['start_position'], $row['end_position'], $row['bin_name'], $row['chromosome'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_bin_name($arg0) {
    $sql = self::$base_sql.' where bin_name = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['markers_in_maps_uid'], $row['marker_uid'], $row['map_uid'], $row['start_position'], $row['end_position'], $row['bin_name'], $row['chromosome'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_bin_name_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where bin_name in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['markers_in_maps_uid'], $row['marker_uid'], $row['map_uid'], $row['start_position'], $row['end_position'], $row['bin_name'], $row['chromosome'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_chromosome($arg0) {
    $sql = self::$base_sql.' where chromosome = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['markers_in_maps_uid'], $row['marker_uid'], $row['map_uid'], $row['start_position'], $row['end_position'], $row['bin_name'], $row['chromosome'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_chromosome_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where chromosome in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['markers_in_maps_uid'], $row['marker_uid'], $row['map_uid'], $row['start_position'], $row['end_position'], $row['bin_name'], $row['chromosome'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['markers_in_maps_uid'], $row['marker_uid'], $row['map_uid'], $row['start_position'], $row['end_position'], $row['bin_name'], $row['chromosome'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['markers_in_maps_uid'], $row['marker_uid'], $row['map_uid'], $row['start_position'], $row['end_position'], $row['bin_name'], $row['chromosome'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['markers_in_maps_uid'], $row['marker_uid'], $row['map_uid'], $row['start_position'], $row['end_position'], $row['bin_name'], $row['chromosome'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['markers_in_maps_uid'], $row['marker_uid'], $row['map_uid'], $row['start_position'], $row['end_position'], $row['bin_name'], $row['chromosome'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  /* end-auto-gen */
}