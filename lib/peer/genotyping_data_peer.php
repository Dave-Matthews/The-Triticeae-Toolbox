<?php
/**
 * Auto Generated Class
 * Contains methods for extracting rows from the table 'genotyping_data'
 */
class genotyping_data_peer
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
    protected static $base_sql = 'select genotyping_data_uid, tht_base_uid, marker_uid, genotyping_status_uid, genotyping_data_name, masked, set_number, primer_forward_id, primer_reverse_id, updated_on, created_on from genotyping_data';


    // auto-generated method
  // get all records from db
  public static function get_all() {
    $results = array();
    $query = mysql_query(self::$base_sql);
    if (mysql_num_rows($query) <= 0) return $results;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $results[] =& new $modelname($row['genotyping_data_uid'], $row['tht_base_uid'], $row['marker_uid'], $row['genotyping_status_uid'], $row['genotyping_data_name'], $row['masked'], $row['set_number'], $row['primer_forward_id'], $row['primer_reverse_id'], $row['updated_on'], $row['created_on']);
    }
    return $results;
  }

  
  # auto-generated function
  public static function get_by_genotyping_data_uid($arg0) {
    $sql = self::$base_sql.' where genotyping_data_uid = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['genotyping_data_uid'], $row['tht_base_uid'], $row['marker_uid'], $row['genotyping_status_uid'], $row['genotyping_data_name'], $row['masked'], $row['set_number'], $row['primer_forward_id'], $row['primer_reverse_id'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_genotyping_data_uid_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where genotyping_data_uid in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['genotyping_data_uid'], $row['tht_base_uid'], $row['marker_uid'], $row['genotyping_status_uid'], $row['genotyping_data_name'], $row['masked'], $row['set_number'], $row['primer_forward_id'], $row['primer_reverse_id'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['genotyping_data_uid'], $row['tht_base_uid'], $row['marker_uid'], $row['genotyping_status_uid'], $row['genotyping_data_name'], $row['masked'], $row['set_number'], $row['primer_forward_id'], $row['primer_reverse_id'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['genotyping_data_uid'], $row['tht_base_uid'], $row['marker_uid'], $row['genotyping_status_uid'], $row['genotyping_data_name'], $row['masked'], $row['set_number'], $row['primer_forward_id'], $row['primer_reverse_id'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['genotyping_data_uid'], $row['tht_base_uid'], $row['marker_uid'], $row['genotyping_status_uid'], $row['genotyping_data_name'], $row['masked'], $row['set_number'], $row['primer_forward_id'], $row['primer_reverse_id'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['genotyping_data_uid'], $row['tht_base_uid'], $row['marker_uid'], $row['genotyping_status_uid'], $row['genotyping_data_name'], $row['masked'], $row['set_number'], $row['primer_forward_id'], $row['primer_reverse_id'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_genotyping_status_uid($arg0) {
    $sql = self::$base_sql.' where genotyping_status_uid = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['genotyping_data_uid'], $row['tht_base_uid'], $row['marker_uid'], $row['genotyping_status_uid'], $row['genotyping_data_name'], $row['masked'], $row['set_number'], $row['primer_forward_id'], $row['primer_reverse_id'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_genotyping_status_uid_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where genotyping_status_uid in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['genotyping_data_uid'], $row['tht_base_uid'], $row['marker_uid'], $row['genotyping_status_uid'], $row['genotyping_data_name'], $row['masked'], $row['set_number'], $row['primer_forward_id'], $row['primer_reverse_id'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_genotyping_data_name($arg0) {
    $sql = self::$base_sql.' where genotyping_data_name = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['genotyping_data_uid'], $row['tht_base_uid'], $row['marker_uid'], $row['genotyping_status_uid'], $row['genotyping_data_name'], $row['masked'], $row['set_number'], $row['primer_forward_id'], $row['primer_reverse_id'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_genotyping_data_name_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where genotyping_data_name in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['genotyping_data_uid'], $row['tht_base_uid'], $row['marker_uid'], $row['genotyping_status_uid'], $row['genotyping_data_name'], $row['masked'], $row['set_number'], $row['primer_forward_id'], $row['primer_reverse_id'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_masked($arg0) {
    $sql = self::$base_sql.' where masked = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['genotyping_data_uid'], $row['tht_base_uid'], $row['marker_uid'], $row['genotyping_status_uid'], $row['genotyping_data_name'], $row['masked'], $row['set_number'], $row['primer_forward_id'], $row['primer_reverse_id'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_masked_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where masked in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['genotyping_data_uid'], $row['tht_base_uid'], $row['marker_uid'], $row['genotyping_status_uid'], $row['genotyping_data_name'], $row['masked'], $row['set_number'], $row['primer_forward_id'], $row['primer_reverse_id'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_set_number($arg0) {
    $sql = self::$base_sql.' where set_number = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['genotyping_data_uid'], $row['tht_base_uid'], $row['marker_uid'], $row['genotyping_status_uid'], $row['genotyping_data_name'], $row['masked'], $row['set_number'], $row['primer_forward_id'], $row['primer_reverse_id'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_set_number_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where set_number in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['genotyping_data_uid'], $row['tht_base_uid'], $row['marker_uid'], $row['genotyping_status_uid'], $row['genotyping_data_name'], $row['masked'], $row['set_number'], $row['primer_forward_id'], $row['primer_reverse_id'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_primer_forward_id($arg0) {
    $sql = self::$base_sql.' where primer_forward_id = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['genotyping_data_uid'], $row['tht_base_uid'], $row['marker_uid'], $row['genotyping_status_uid'], $row['genotyping_data_name'], $row['masked'], $row['set_number'], $row['primer_forward_id'], $row['primer_reverse_id'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_primer_forward_id_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where primer_forward_id in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['genotyping_data_uid'], $row['tht_base_uid'], $row['marker_uid'], $row['genotyping_status_uid'], $row['genotyping_data_name'], $row['masked'], $row['set_number'], $row['primer_forward_id'], $row['primer_reverse_id'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_primer_reverse_id($arg0) {
    $sql = self::$base_sql.' where primer_reverse_id = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['genotyping_data_uid'], $row['tht_base_uid'], $row['marker_uid'], $row['genotyping_status_uid'], $row['genotyping_data_name'], $row['masked'], $row['set_number'], $row['primer_forward_id'], $row['primer_reverse_id'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_primer_reverse_id_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where primer_reverse_id in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['genotyping_data_uid'], $row['tht_base_uid'], $row['marker_uid'], $row['genotyping_status_uid'], $row['genotyping_data_name'], $row['masked'], $row['set_number'], $row['primer_forward_id'], $row['primer_reverse_id'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['genotyping_data_uid'], $row['tht_base_uid'], $row['marker_uid'], $row['genotyping_status_uid'], $row['genotyping_data_name'], $row['masked'], $row['set_number'], $row['primer_forward_id'], $row['primer_reverse_id'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['genotyping_data_uid'], $row['tht_base_uid'], $row['marker_uid'], $row['genotyping_status_uid'], $row['genotyping_data_name'], $row['masked'], $row['set_number'], $row['primer_forward_id'], $row['primer_reverse_id'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['genotyping_data_uid'], $row['tht_base_uid'], $row['marker_uid'], $row['genotyping_status_uid'], $row['genotyping_data_name'], $row['masked'], $row['set_number'], $row['primer_forward_id'], $row['primer_reverse_id'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['genotyping_data_uid'], $row['tht_base_uid'], $row['marker_uid'], $row['genotyping_status_uid'], $row['genotyping_data_name'], $row['masked'], $row['set_number'], $row['primer_forward_id'], $row['primer_reverse_id'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  /* end-auto-gen */
}