<?php
/**
 * Auto Generated Class
 * Contains methods for extracting rows from the table 'file_process'
 */
class file_process_peer
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
    protected static $base_sql = 'select file_process_uid, file_name, def_file_name, dir_destination, file_desc, dataset_name, process_program, target_tables, users_name, process_result, updated_on, created_on from file_process';


    // auto-generated method
  // get all records from db
  public static function get_all() {
    $results = array();
    $query = mysql_query(self::$base_sql);
    if (mysql_num_rows($query) <= 0) return $results;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $results[] =& new $modelname($row['file_process_uid'], $row['file_name'], $row['def_file_name'], $row['dir_destination'], $row['file_desc'], $row['dataset_name'], $row['process_program'], $row['target_tables'], $row['users_name'], $row['process_result'], $row['updated_on'], $row['created_on']);
    }
    return $results;
  }

  
  # auto-generated function
  public static function get_by_file_process_uid($arg0) {
    $sql = self::$base_sql.' where file_process_uid = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['file_process_uid'], $row['file_name'], $row['def_file_name'], $row['dir_destination'], $row['file_desc'], $row['dataset_name'], $row['process_program'], $row['target_tables'], $row['users_name'], $row['process_result'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_file_process_uid_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where file_process_uid in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['file_process_uid'], $row['file_name'], $row['def_file_name'], $row['dir_destination'], $row['file_desc'], $row['dataset_name'], $row['process_program'], $row['target_tables'], $row['users_name'], $row['process_result'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_file_name($arg0) {
    $sql = self::$base_sql.' where file_name = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['file_process_uid'], $row['file_name'], $row['def_file_name'], $row['dir_destination'], $row['file_desc'], $row['dataset_name'], $row['process_program'], $row['target_tables'], $row['users_name'], $row['process_result'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_file_name_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where file_name in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['file_process_uid'], $row['file_name'], $row['def_file_name'], $row['dir_destination'], $row['file_desc'], $row['dataset_name'], $row['process_program'], $row['target_tables'], $row['users_name'], $row['process_result'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_def_file_name($arg0) {
    $sql = self::$base_sql.' where def_file_name = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['file_process_uid'], $row['file_name'], $row['def_file_name'], $row['dir_destination'], $row['file_desc'], $row['dataset_name'], $row['process_program'], $row['target_tables'], $row['users_name'], $row['process_result'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_def_file_name_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where def_file_name in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['file_process_uid'], $row['file_name'], $row['def_file_name'], $row['dir_destination'], $row['file_desc'], $row['dataset_name'], $row['process_program'], $row['target_tables'], $row['users_name'], $row['process_result'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_dir_destination($arg0) {
    $sql = self::$base_sql.' where dir_destination = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['file_process_uid'], $row['file_name'], $row['def_file_name'], $row['dir_destination'], $row['file_desc'], $row['dataset_name'], $row['process_program'], $row['target_tables'], $row['users_name'], $row['process_result'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_dir_destination_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where dir_destination in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['file_process_uid'], $row['file_name'], $row['def_file_name'], $row['dir_destination'], $row['file_desc'], $row['dataset_name'], $row['process_program'], $row['target_tables'], $row['users_name'], $row['process_result'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_file_desc($arg0) {
    $sql = self::$base_sql.' where file_desc = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['file_process_uid'], $row['file_name'], $row['def_file_name'], $row['dir_destination'], $row['file_desc'], $row['dataset_name'], $row['process_program'], $row['target_tables'], $row['users_name'], $row['process_result'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_file_desc_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where file_desc in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['file_process_uid'], $row['file_name'], $row['def_file_name'], $row['dir_destination'], $row['file_desc'], $row['dataset_name'], $row['process_program'], $row['target_tables'], $row['users_name'], $row['process_result'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_dataset_name($arg0) {
    $sql = self::$base_sql.' where dataset_name = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['file_process_uid'], $row['file_name'], $row['def_file_name'], $row['dir_destination'], $row['file_desc'], $row['dataset_name'], $row['process_program'], $row['target_tables'], $row['users_name'], $row['process_result'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_dataset_name_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where dataset_name in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['file_process_uid'], $row['file_name'], $row['def_file_name'], $row['dir_destination'], $row['file_desc'], $row['dataset_name'], $row['process_program'], $row['target_tables'], $row['users_name'], $row['process_result'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_process_program($arg0) {
    $sql = self::$base_sql.' where process_program = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['file_process_uid'], $row['file_name'], $row['def_file_name'], $row['dir_destination'], $row['file_desc'], $row['dataset_name'], $row['process_program'], $row['target_tables'], $row['users_name'], $row['process_result'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_process_program_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where process_program in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['file_process_uid'], $row['file_name'], $row['def_file_name'], $row['dir_destination'], $row['file_desc'], $row['dataset_name'], $row['process_program'], $row['target_tables'], $row['users_name'], $row['process_result'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_target_tables($arg0) {
    $sql = self::$base_sql.' where target_tables = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['file_process_uid'], $row['file_name'], $row['def_file_name'], $row['dir_destination'], $row['file_desc'], $row['dataset_name'], $row['process_program'], $row['target_tables'], $row['users_name'], $row['process_result'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_target_tables_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where target_tables in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['file_process_uid'], $row['file_name'], $row['def_file_name'], $row['dir_destination'], $row['file_desc'], $row['dataset_name'], $row['process_program'], $row['target_tables'], $row['users_name'], $row['process_result'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_users_name($arg0) {
    $sql = self::$base_sql.' where users_name = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['file_process_uid'], $row['file_name'], $row['def_file_name'], $row['dir_destination'], $row['file_desc'], $row['dataset_name'], $row['process_program'], $row['target_tables'], $row['users_name'], $row['process_result'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_users_name_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where users_name in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['file_process_uid'], $row['file_name'], $row['def_file_name'], $row['dir_destination'], $row['file_desc'], $row['dataset_name'], $row['process_program'], $row['target_tables'], $row['users_name'], $row['process_result'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_process_result($arg0) {
    $sql = self::$base_sql.' where process_result = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['file_process_uid'], $row['file_name'], $row['def_file_name'], $row['dir_destination'], $row['file_desc'], $row['dataset_name'], $row['process_program'], $row['target_tables'], $row['users_name'], $row['process_result'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_process_result_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where process_result in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['file_process_uid'], $row['file_name'], $row['def_file_name'], $row['dir_destination'], $row['file_desc'], $row['dataset_name'], $row['process_program'], $row['target_tables'], $row['users_name'], $row['process_result'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['file_process_uid'], $row['file_name'], $row['def_file_name'], $row['dir_destination'], $row['file_desc'], $row['dataset_name'], $row['process_program'], $row['target_tables'], $row['users_name'], $row['process_result'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['file_process_uid'], $row['file_name'], $row['def_file_name'], $row['dir_destination'], $row['file_desc'], $row['dataset_name'], $row['process_program'], $row['target_tables'], $row['users_name'], $row['process_result'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['file_process_uid'], $row['file_name'], $row['def_file_name'], $row['dir_destination'], $row['file_desc'], $row['dataset_name'], $row['process_program'], $row['target_tables'], $row['users_name'], $row['process_result'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['file_process_uid'], $row['file_name'], $row['def_file_name'], $row['dir_destination'], $row['file_desc'], $row['dataset_name'], $row['process_program'], $row['target_tables'], $row['users_name'], $row['process_result'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  /* end-auto-gen */
}