<?php
/**
 * Auto Generated Class
 * Contains methods for extracting rows from the table 'phenotype_descstat'
 */
class phenotype_descstat_peer
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
    protected static $base_sql = 'select phenotype_descstat_uid, phenotype_uid, mean_val, max_val, min_val, sample_size, std, updated_on, created_on from phenotype_descstat';


    // auto-generated method
  // get all records from db
  public static function get_all() {
    $results = array();
    $query = mysql_query(self::$base_sql);
    if (mysql_num_rows($query) <= 0) return $results;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $results[] =& new $modelname($row['phenotype_descstat_uid'], $row['phenotype_uid'], $row['mean_val'], $row['max_val'], $row['min_val'], $row['sample_size'], $row['std'], $row['updated_on'], $row['created_on']);
    }
    return $results;
  }

  
  # auto-generated function
  public static function get_by_phenotype_descstat_uid($arg0) {
    $sql = self::$base_sql.' where phenotype_descstat_uid = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['phenotype_descstat_uid'], $row['phenotype_uid'], $row['mean_val'], $row['max_val'], $row['min_val'], $row['sample_size'], $row['std'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_phenotype_descstat_uid_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where phenotype_descstat_uid in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['phenotype_descstat_uid'], $row['phenotype_uid'], $row['mean_val'], $row['max_val'], $row['min_val'], $row['sample_size'], $row['std'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['phenotype_descstat_uid'], $row['phenotype_uid'], $row['mean_val'], $row['max_val'], $row['min_val'], $row['sample_size'], $row['std'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['phenotype_descstat_uid'], $row['phenotype_uid'], $row['mean_val'], $row['max_val'], $row['min_val'], $row['sample_size'], $row['std'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_mean_val($arg0) {
    $sql = self::$base_sql.' where mean_val = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['phenotype_descstat_uid'], $row['phenotype_uid'], $row['mean_val'], $row['max_val'], $row['min_val'], $row['sample_size'], $row['std'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_mean_val_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where mean_val in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['phenotype_descstat_uid'], $row['phenotype_uid'], $row['mean_val'], $row['max_val'], $row['min_val'], $row['sample_size'], $row['std'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_max_val($arg0) {
    $sql = self::$base_sql.' where max_val = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['phenotype_descstat_uid'], $row['phenotype_uid'], $row['mean_val'], $row['max_val'], $row['min_val'], $row['sample_size'], $row['std'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_max_val_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where max_val in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['phenotype_descstat_uid'], $row['phenotype_uid'], $row['mean_val'], $row['max_val'], $row['min_val'], $row['sample_size'], $row['std'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_min_val($arg0) {
    $sql = self::$base_sql.' where min_val = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['phenotype_descstat_uid'], $row['phenotype_uid'], $row['mean_val'], $row['max_val'], $row['min_val'], $row['sample_size'], $row['std'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_min_val_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where min_val in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['phenotype_descstat_uid'], $row['phenotype_uid'], $row['mean_val'], $row['max_val'], $row['min_val'], $row['sample_size'], $row['std'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_sample_size($arg0) {
    $sql = self::$base_sql.' where sample_size = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['phenotype_descstat_uid'], $row['phenotype_uid'], $row['mean_val'], $row['max_val'], $row['min_val'], $row['sample_size'], $row['std'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_sample_size_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where sample_size in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['phenotype_descstat_uid'], $row['phenotype_uid'], $row['mean_val'], $row['max_val'], $row['min_val'], $row['sample_size'], $row['std'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_std($arg0) {
    $sql = self::$base_sql.' where std = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['phenotype_descstat_uid'], $row['phenotype_uid'], $row['mean_val'], $row['max_val'], $row['min_val'], $row['sample_size'], $row['std'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_std_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where std in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['phenotype_descstat_uid'], $row['phenotype_uid'], $row['mean_val'], $row['max_val'], $row['min_val'], $row['sample_size'], $row['std'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['phenotype_descstat_uid'], $row['phenotype_uid'], $row['mean_val'], $row['max_val'], $row['min_val'], $row['sample_size'], $row['std'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['phenotype_descstat_uid'], $row['phenotype_uid'], $row['mean_val'], $row['max_val'], $row['min_val'], $row['sample_size'], $row['std'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['phenotype_descstat_uid'], $row['phenotype_uid'], $row['mean_val'], $row['max_val'], $row['min_val'], $row['sample_size'], $row['std'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['phenotype_descstat_uid'], $row['phenotype_uid'], $row['mean_val'], $row['max_val'], $row['min_val'], $row['sample_size'], $row['std'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  /* end-auto-gen */
}