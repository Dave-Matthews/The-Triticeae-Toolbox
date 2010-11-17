<?php
/**
 * Auto Generated Class
 * Contains methods for extracting rows from the table 'users'
 */
class users_peer
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
    protected static $base_sql = 'select users_uid, user_types_uid, institutions_uid, users_name, pass, name, email, lastaccess, updated_on, created_on from users';


    // auto-generated method
  // get all records from db
  public static function get_all() {
    $results = array();
    $query = mysql_query(self::$base_sql);
    if (mysql_num_rows($query) <= 0) return $results;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $results[] =& new $modelname($row['users_uid'], $row['user_types_uid'], $row['institutions_uid'], $row['users_name'], $row['pass'], $row['name'], $row['email'], $row['lastaccess'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['users_uid'], $row['user_types_uid'], $row['institutions_uid'], $row['users_name'], $row['pass'], $row['name'], $row['email'], $row['lastaccess'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['users_uid'], $row['user_types_uid'], $row['institutions_uid'], $row['users_name'], $row['pass'], $row['name'], $row['email'], $row['lastaccess'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_user_types_uid($arg0) {
    $sql = self::$base_sql.' where user_types_uid = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['users_uid'], $row['user_types_uid'], $row['institutions_uid'], $row['users_name'], $row['pass'], $row['name'], $row['email'], $row['lastaccess'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_user_types_uid_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where user_types_uid in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['users_uid'], $row['user_types_uid'], $row['institutions_uid'], $row['users_name'], $row['pass'], $row['name'], $row['email'], $row['lastaccess'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_institutions_uid($arg0) {
    $sql = self::$base_sql.' where institutions_uid = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['users_uid'], $row['user_types_uid'], $row['institutions_uid'], $row['users_name'], $row['pass'], $row['name'], $row['email'], $row['lastaccess'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_institutions_uid_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where institutions_uid in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['users_uid'], $row['user_types_uid'], $row['institutions_uid'], $row['users_name'], $row['pass'], $row['name'], $row['email'], $row['lastaccess'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['users_uid'], $row['user_types_uid'], $row['institutions_uid'], $row['users_name'], $row['pass'], $row['name'], $row['email'], $row['lastaccess'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['users_uid'], $row['user_types_uid'], $row['institutions_uid'], $row['users_name'], $row['pass'], $row['name'], $row['email'], $row['lastaccess'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_pass($arg0) {
    $sql = self::$base_sql.' where pass = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['users_uid'], $row['user_types_uid'], $row['institutions_uid'], $row['users_name'], $row['pass'], $row['name'], $row['email'], $row['lastaccess'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_pass_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where pass in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['users_uid'], $row['user_types_uid'], $row['institutions_uid'], $row['users_name'], $row['pass'], $row['name'], $row['email'], $row['lastaccess'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_name($arg0) {
    $sql = self::$base_sql.' where name = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['users_uid'], $row['user_types_uid'], $row['institutions_uid'], $row['users_name'], $row['pass'], $row['name'], $row['email'], $row['lastaccess'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_name_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where name in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['users_uid'], $row['user_types_uid'], $row['institutions_uid'], $row['users_name'], $row['pass'], $row['name'], $row['email'], $row['lastaccess'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_email($arg0) {
    $sql = self::$base_sql.' where email = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['users_uid'], $row['user_types_uid'], $row['institutions_uid'], $row['users_name'], $row['pass'], $row['name'], $row['email'], $row['lastaccess'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_email_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where email in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['users_uid'], $row['user_types_uid'], $row['institutions_uid'], $row['users_name'], $row['pass'], $row['name'], $row['email'], $row['lastaccess'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_lastaccess($arg0) {
    $sql = self::$base_sql.' where lastaccess = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['users_uid'], $row['user_types_uid'], $row['institutions_uid'], $row['users_name'], $row['pass'], $row['name'], $row['email'], $row['lastaccess'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_lastaccess_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where lastaccess in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['users_uid'], $row['user_types_uid'], $row['institutions_uid'], $row['users_name'], $row['pass'], $row['name'], $row['email'], $row['lastaccess'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['users_uid'], $row['user_types_uid'], $row['institutions_uid'], $row['users_name'], $row['pass'], $row['name'], $row['email'], $row['lastaccess'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['users_uid'], $row['user_types_uid'], $row['institutions_uid'], $row['users_name'], $row['pass'], $row['name'], $row['email'], $row['lastaccess'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['users_uid'], $row['user_types_uid'], $row['institutions_uid'], $row['users_name'], $row['pass'], $row['name'], $row['email'], $row['lastaccess'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['users_uid'], $row['user_types_uid'], $row['institutions_uid'], $row['users_name'], $row['pass'], $row['name'], $row['email'], $row['lastaccess'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  /* end-auto-gen */
}