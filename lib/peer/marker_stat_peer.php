<?php
/**
 * Auto Generated Class
 * Contains methods for extracting rows from the table 'marker_stat'
 */
class marker_stat_peer
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
    protected static $base_sql = 'select marker_stat_uid, datasets_uid, marker_uid, aa_freq, ab_freq, bb_freq, gentrain_score, note, updated_on, created_on from marker_stat';


    // auto-generated method
  // get all records from db
  public static function get_all() {
    $results = array();
    $query = mysql_query(self::$base_sql);
    if (mysql_num_rows($query) <= 0) return $results;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $results[] =& new $modelname($row['marker_stat_uid'], $row['datasets_uid'], $row['marker_uid'], $row['aa_freq'], $row['ab_freq'], $row['bb_freq'], $row['gentrain_score'], $row['note'], $row['updated_on'], $row['created_on']);
    }
    return $results;
  }

  
  # auto-generated function
  public static function get_by_marker_stat_uid($arg0) {
    $sql = self::$base_sql.' where marker_stat_uid = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['marker_stat_uid'], $row['datasets_uid'], $row['marker_uid'], $row['aa_freq'], $row['ab_freq'], $row['bb_freq'], $row['gentrain_score'], $row['note'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_marker_stat_uid_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where marker_stat_uid in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['marker_stat_uid'], $row['datasets_uid'], $row['marker_uid'], $row['aa_freq'], $row['ab_freq'], $row['bb_freq'], $row['gentrain_score'], $row['note'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_datasets_uid($arg0) {
    $sql = self::$base_sql.' where datasets_uid = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['marker_stat_uid'], $row['datasets_uid'], $row['marker_uid'], $row['aa_freq'], $row['ab_freq'], $row['bb_freq'], $row['gentrain_score'], $row['note'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_datasets_uid_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where datasets_uid in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['marker_stat_uid'], $row['datasets_uid'], $row['marker_uid'], $row['aa_freq'], $row['ab_freq'], $row['bb_freq'], $row['gentrain_score'], $row['note'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['marker_stat_uid'], $row['datasets_uid'], $row['marker_uid'], $row['aa_freq'], $row['ab_freq'], $row['bb_freq'], $row['gentrain_score'], $row['note'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['marker_stat_uid'], $row['datasets_uid'], $row['marker_uid'], $row['aa_freq'], $row['ab_freq'], $row['bb_freq'], $row['gentrain_score'], $row['note'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_aa_freq($arg0) {
    $sql = self::$base_sql.' where aa_freq = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['marker_stat_uid'], $row['datasets_uid'], $row['marker_uid'], $row['aa_freq'], $row['ab_freq'], $row['bb_freq'], $row['gentrain_score'], $row['note'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_aa_freq_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where aa_freq in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['marker_stat_uid'], $row['datasets_uid'], $row['marker_uid'], $row['aa_freq'], $row['ab_freq'], $row['bb_freq'], $row['gentrain_score'], $row['note'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_ab_freq($arg0) {
    $sql = self::$base_sql.' where ab_freq = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['marker_stat_uid'], $row['datasets_uid'], $row['marker_uid'], $row['aa_freq'], $row['ab_freq'], $row['bb_freq'], $row['gentrain_score'], $row['note'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_ab_freq_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where ab_freq in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['marker_stat_uid'], $row['datasets_uid'], $row['marker_uid'], $row['aa_freq'], $row['ab_freq'], $row['bb_freq'], $row['gentrain_score'], $row['note'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_bb_freq($arg0) {
    $sql = self::$base_sql.' where bb_freq = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['marker_stat_uid'], $row['datasets_uid'], $row['marker_uid'], $row['aa_freq'], $row['ab_freq'], $row['bb_freq'], $row['gentrain_score'], $row['note'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_bb_freq_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where bb_freq in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['marker_stat_uid'], $row['datasets_uid'], $row['marker_uid'], $row['aa_freq'], $row['ab_freq'], $row['bb_freq'], $row['gentrain_score'], $row['note'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_gentrain_score($arg0) {
    $sql = self::$base_sql.' where gentrain_score = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['marker_stat_uid'], $row['datasets_uid'], $row['marker_uid'], $row['aa_freq'], $row['ab_freq'], $row['bb_freq'], $row['gentrain_score'], $row['note'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_gentrain_score_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where gentrain_score in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['marker_stat_uid'], $row['datasets_uid'], $row['marker_uid'], $row['aa_freq'], $row['ab_freq'], $row['bb_freq'], $row['gentrain_score'], $row['note'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_note($arg0) {
    $sql = self::$base_sql.' where note = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['marker_stat_uid'], $row['datasets_uid'], $row['marker_uid'], $row['aa_freq'], $row['ab_freq'], $row['bb_freq'], $row['gentrain_score'], $row['note'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_note_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where note in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['marker_stat_uid'], $row['datasets_uid'], $row['marker_uid'], $row['aa_freq'], $row['ab_freq'], $row['bb_freq'], $row['gentrain_score'], $row['note'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['marker_stat_uid'], $row['datasets_uid'], $row['marker_uid'], $row['aa_freq'], $row['ab_freq'], $row['bb_freq'], $row['gentrain_score'], $row['note'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['marker_stat_uid'], $row['datasets_uid'], $row['marker_uid'], $row['aa_freq'], $row['ab_freq'], $row['bb_freq'], $row['gentrain_score'], $row['note'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['marker_stat_uid'], $row['datasets_uid'], $row['marker_uid'], $row['aa_freq'], $row['ab_freq'], $row['bb_freq'], $row['gentrain_score'], $row['note'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['marker_stat_uid'], $row['datasets_uid'], $row['marker_uid'], $row['aa_freq'], $row['ab_freq'], $row['bb_freq'], $row['gentrain_score'], $row['note'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  /* end-auto-gen */
}