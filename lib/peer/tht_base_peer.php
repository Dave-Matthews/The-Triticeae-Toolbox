<?php
/**
 * Auto Generated Class
 * Contains methods for extracting rows from the table 'tht_base'
 */
class tht_base_peer
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
    protected static $base_sql = 'select tht_base_uid, line_record_uid, experiment_uid, tht_base_name, number, institution_id, plant_passport, donor_code, donor_number, acqdate, collnumb, colldate, collcode, duplsite, updated_on, created_on from tht_base';


    // auto-generated method
  // get all records from db
  public static function get_all() {
    $results = array();
    $query = mysql_query(self::$base_sql);
    if (mysql_num_rows($query) <= 0) return $results;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $results[] =& new $modelname($row['tht_base_uid'], $row['line_record_uid'], $row['experiment_uid'], $row['tht_base_name'], $row['number'], $row['institution_id'], $row['plant_passport'], $row['donor_code'], $row['donor_number'], $row['acqdate'], $row['collnumb'], $row['colldate'], $row['collcode'], $row['duplsite'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['tht_base_uid'], $row['line_record_uid'], $row['experiment_uid'], $row['tht_base_name'], $row['number'], $row['institution_id'], $row['plant_passport'], $row['donor_code'], $row['donor_number'], $row['acqdate'], $row['collnumb'], $row['colldate'], $row['collcode'], $row['duplsite'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['tht_base_uid'], $row['line_record_uid'], $row['experiment_uid'], $row['tht_base_name'], $row['number'], $row['institution_id'], $row['plant_passport'], $row['donor_code'], $row['donor_number'], $row['acqdate'], $row['collnumb'], $row['colldate'], $row['collcode'], $row['duplsite'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_line_record_uid($arg0) {
    $sql = self::$base_sql.' where line_record_uid = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['tht_base_uid'], $row['line_record_uid'], $row['experiment_uid'], $row['tht_base_name'], $row['number'], $row['institution_id'], $row['plant_passport'], $row['donor_code'], $row['donor_number'], $row['acqdate'], $row['collnumb'], $row['colldate'], $row['collcode'], $row['duplsite'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_line_record_uid_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where line_record_uid in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['tht_base_uid'], $row['line_record_uid'], $row['experiment_uid'], $row['tht_base_name'], $row['number'], $row['institution_id'], $row['plant_passport'], $row['donor_code'], $row['donor_number'], $row['acqdate'], $row['collnumb'], $row['colldate'], $row['collcode'], $row['duplsite'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_experiment_uid($arg0) {
    $sql = self::$base_sql.' where experiment_uid = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['tht_base_uid'], $row['line_record_uid'], $row['experiment_uid'], $row['tht_base_name'], $row['number'], $row['institution_id'], $row['plant_passport'], $row['donor_code'], $row['donor_number'], $row['acqdate'], $row['collnumb'], $row['colldate'], $row['collcode'], $row['duplsite'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_experiment_uid_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where experiment_uid in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['tht_base_uid'], $row['line_record_uid'], $row['experiment_uid'], $row['tht_base_name'], $row['number'], $row['institution_id'], $row['plant_passport'], $row['donor_code'], $row['donor_number'], $row['acqdate'], $row['collnumb'], $row['colldate'], $row['collcode'], $row['duplsite'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_tht_base_name($arg0) {
    $sql = self::$base_sql.' where tht_base_name = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['tht_base_uid'], $row['line_record_uid'], $row['experiment_uid'], $row['tht_base_name'], $row['number'], $row['institution_id'], $row['plant_passport'], $row['donor_code'], $row['donor_number'], $row['acqdate'], $row['collnumb'], $row['colldate'], $row['collcode'], $row['duplsite'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_tht_base_name_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where tht_base_name in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['tht_base_uid'], $row['line_record_uid'], $row['experiment_uid'], $row['tht_base_name'], $row['number'], $row['institution_id'], $row['plant_passport'], $row['donor_code'], $row['donor_number'], $row['acqdate'], $row['collnumb'], $row['colldate'], $row['collcode'], $row['duplsite'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_number($arg0) {
    $sql = self::$base_sql.' where number = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['tht_base_uid'], $row['line_record_uid'], $row['experiment_uid'], $row['tht_base_name'], $row['number'], $row['institution_id'], $row['plant_passport'], $row['donor_code'], $row['donor_number'], $row['acqdate'], $row['collnumb'], $row['colldate'], $row['collcode'], $row['duplsite'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_number_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where number in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['tht_base_uid'], $row['line_record_uid'], $row['experiment_uid'], $row['tht_base_name'], $row['number'], $row['institution_id'], $row['plant_passport'], $row['donor_code'], $row['donor_number'], $row['acqdate'], $row['collnumb'], $row['colldate'], $row['collcode'], $row['duplsite'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_institution_id($arg0) {
    $sql = self::$base_sql.' where institution_id = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['tht_base_uid'], $row['line_record_uid'], $row['experiment_uid'], $row['tht_base_name'], $row['number'], $row['institution_id'], $row['plant_passport'], $row['donor_code'], $row['donor_number'], $row['acqdate'], $row['collnumb'], $row['colldate'], $row['collcode'], $row['duplsite'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_institution_id_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where institution_id in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['tht_base_uid'], $row['line_record_uid'], $row['experiment_uid'], $row['tht_base_name'], $row['number'], $row['institution_id'], $row['plant_passport'], $row['donor_code'], $row['donor_number'], $row['acqdate'], $row['collnumb'], $row['colldate'], $row['collcode'], $row['duplsite'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_plant_passport($arg0) {
    $sql = self::$base_sql.' where plant_passport = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['tht_base_uid'], $row['line_record_uid'], $row['experiment_uid'], $row['tht_base_name'], $row['number'], $row['institution_id'], $row['plant_passport'], $row['donor_code'], $row['donor_number'], $row['acqdate'], $row['collnumb'], $row['colldate'], $row['collcode'], $row['duplsite'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_plant_passport_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where plant_passport in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['tht_base_uid'], $row['line_record_uid'], $row['experiment_uid'], $row['tht_base_name'], $row['number'], $row['institution_id'], $row['plant_passport'], $row['donor_code'], $row['donor_number'], $row['acqdate'], $row['collnumb'], $row['colldate'], $row['collcode'], $row['duplsite'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_donor_code($arg0) {
    $sql = self::$base_sql.' where donor_code = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['tht_base_uid'], $row['line_record_uid'], $row['experiment_uid'], $row['tht_base_name'], $row['number'], $row['institution_id'], $row['plant_passport'], $row['donor_code'], $row['donor_number'], $row['acqdate'], $row['collnumb'], $row['colldate'], $row['collcode'], $row['duplsite'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_donor_code_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where donor_code in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['tht_base_uid'], $row['line_record_uid'], $row['experiment_uid'], $row['tht_base_name'], $row['number'], $row['institution_id'], $row['plant_passport'], $row['donor_code'], $row['donor_number'], $row['acqdate'], $row['collnumb'], $row['colldate'], $row['collcode'], $row['duplsite'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_donor_number($arg0) {
    $sql = self::$base_sql.' where donor_number = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['tht_base_uid'], $row['line_record_uid'], $row['experiment_uid'], $row['tht_base_name'], $row['number'], $row['institution_id'], $row['plant_passport'], $row['donor_code'], $row['donor_number'], $row['acqdate'], $row['collnumb'], $row['colldate'], $row['collcode'], $row['duplsite'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_donor_number_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where donor_number in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['tht_base_uid'], $row['line_record_uid'], $row['experiment_uid'], $row['tht_base_name'], $row['number'], $row['institution_id'], $row['plant_passport'], $row['donor_code'], $row['donor_number'], $row['acqdate'], $row['collnumb'], $row['colldate'], $row['collcode'], $row['duplsite'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_acqdate($arg0) {
    $sql = self::$base_sql.' where acqdate = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['tht_base_uid'], $row['line_record_uid'], $row['experiment_uid'], $row['tht_base_name'], $row['number'], $row['institution_id'], $row['plant_passport'], $row['donor_code'], $row['donor_number'], $row['acqdate'], $row['collnumb'], $row['colldate'], $row['collcode'], $row['duplsite'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_acqdate_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where acqdate in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['tht_base_uid'], $row['line_record_uid'], $row['experiment_uid'], $row['tht_base_name'], $row['number'], $row['institution_id'], $row['plant_passport'], $row['donor_code'], $row['donor_number'], $row['acqdate'], $row['collnumb'], $row['colldate'], $row['collcode'], $row['duplsite'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_collnumb($arg0) {
    $sql = self::$base_sql.' where collnumb = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['tht_base_uid'], $row['line_record_uid'], $row['experiment_uid'], $row['tht_base_name'], $row['number'], $row['institution_id'], $row['plant_passport'], $row['donor_code'], $row['donor_number'], $row['acqdate'], $row['collnumb'], $row['colldate'], $row['collcode'], $row['duplsite'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_collnumb_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where collnumb in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['tht_base_uid'], $row['line_record_uid'], $row['experiment_uid'], $row['tht_base_name'], $row['number'], $row['institution_id'], $row['plant_passport'], $row['donor_code'], $row['donor_number'], $row['acqdate'], $row['collnumb'], $row['colldate'], $row['collcode'], $row['duplsite'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_colldate($arg0) {
    $sql = self::$base_sql.' where colldate = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['tht_base_uid'], $row['line_record_uid'], $row['experiment_uid'], $row['tht_base_name'], $row['number'], $row['institution_id'], $row['plant_passport'], $row['donor_code'], $row['donor_number'], $row['acqdate'], $row['collnumb'], $row['colldate'], $row['collcode'], $row['duplsite'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_colldate_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where colldate in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['tht_base_uid'], $row['line_record_uid'], $row['experiment_uid'], $row['tht_base_name'], $row['number'], $row['institution_id'], $row['plant_passport'], $row['donor_code'], $row['donor_number'], $row['acqdate'], $row['collnumb'], $row['colldate'], $row['collcode'], $row['duplsite'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_collcode($arg0) {
    $sql = self::$base_sql.' where collcode = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['tht_base_uid'], $row['line_record_uid'], $row['experiment_uid'], $row['tht_base_name'], $row['number'], $row['institution_id'], $row['plant_passport'], $row['donor_code'], $row['donor_number'], $row['acqdate'], $row['collnumb'], $row['colldate'], $row['collcode'], $row['duplsite'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_collcode_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where collcode in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['tht_base_uid'], $row['line_record_uid'], $row['experiment_uid'], $row['tht_base_name'], $row['number'], $row['institution_id'], $row['plant_passport'], $row['donor_code'], $row['donor_number'], $row['acqdate'], $row['collnumb'], $row['colldate'], $row['collcode'], $row['duplsite'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_duplsite($arg0) {
    $sql = self::$base_sql.' where duplsite = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['tht_base_uid'], $row['line_record_uid'], $row['experiment_uid'], $row['tht_base_name'], $row['number'], $row['institution_id'], $row['plant_passport'], $row['donor_code'], $row['donor_number'], $row['acqdate'], $row['collnumb'], $row['colldate'], $row['collcode'], $row['duplsite'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_duplsite_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where duplsite in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['tht_base_uid'], $row['line_record_uid'], $row['experiment_uid'], $row['tht_base_name'], $row['number'], $row['institution_id'], $row['plant_passport'], $row['donor_code'], $row['donor_number'], $row['acqdate'], $row['collnumb'], $row['colldate'], $row['collcode'], $row['duplsite'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['tht_base_uid'], $row['line_record_uid'], $row['experiment_uid'], $row['tht_base_name'], $row['number'], $row['institution_id'], $row['plant_passport'], $row['donor_code'], $row['donor_number'], $row['acqdate'], $row['collnumb'], $row['colldate'], $row['collcode'], $row['duplsite'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['tht_base_uid'], $row['line_record_uid'], $row['experiment_uid'], $row['tht_base_name'], $row['number'], $row['institution_id'], $row['plant_passport'], $row['donor_code'], $row['donor_number'], $row['acqdate'], $row['collnumb'], $row['colldate'], $row['collcode'], $row['duplsite'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['tht_base_uid'], $row['line_record_uid'], $row['experiment_uid'], $row['tht_base_name'], $row['number'], $row['institution_id'], $row['plant_passport'], $row['donor_code'], $row['donor_number'], $row['acqdate'], $row['collnumb'], $row['colldate'], $row['collcode'], $row['duplsite'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['tht_base_uid'], $row['line_record_uid'], $row['experiment_uid'], $row['tht_base_name'], $row['number'], $row['institution_id'], $row['plant_passport'], $row['donor_code'], $row['donor_number'], $row['acqdate'], $row['collnumb'], $row['colldate'], $row['collcode'], $row['duplsite'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  /* end-auto-gen */
}