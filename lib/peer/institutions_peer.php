<?php
/**
 * Auto Generated Class
 * Contains methods for extracting rows from the table 'institutions'
 */
class institutions_peer
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
    protected static $base_sql = 'select institutions_uid, institutions_name, institution_code, institute_acronym, institute_address, phone, email, updated_on, created_on from institutions';


    // auto-generated method
  // get all records from db
  public static function get_all() {
    $results = array();
    $query = mysql_query(self::$base_sql);
    if (mysql_num_rows($query) <= 0) return $results;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $results[] =& new $modelname($row['institutions_uid'], $row['institutions_name'], $row['institution_code'], $row['institute_acronym'], $row['institute_address'], $row['phone'], $row['email'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['institutions_uid'], $row['institutions_name'], $row['institution_code'], $row['institute_acronym'], $row['institute_address'], $row['phone'], $row['email'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['institutions_uid'], $row['institutions_name'], $row['institution_code'], $row['institute_acronym'], $row['institute_address'], $row['phone'], $row['email'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_institutions_name($arg0) {
    $sql = self::$base_sql.' where institutions_name = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['institutions_uid'], $row['institutions_name'], $row['institution_code'], $row['institute_acronym'], $row['institute_address'], $row['phone'], $row['email'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_institutions_name_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where institutions_name in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['institutions_uid'], $row['institutions_name'], $row['institution_code'], $row['institute_acronym'], $row['institute_address'], $row['phone'], $row['email'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_institution_code($arg0) {
    $sql = self::$base_sql.' where institution_code = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['institutions_uid'], $row['institutions_name'], $row['institution_code'], $row['institute_acronym'], $row['institute_address'], $row['phone'], $row['email'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_institution_code_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where institution_code in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['institutions_uid'], $row['institutions_name'], $row['institution_code'], $row['institute_acronym'], $row['institute_address'], $row['phone'], $row['email'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_institute_acronym($arg0) {
    $sql = self::$base_sql.' where institute_acronym = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['institutions_uid'], $row['institutions_name'], $row['institution_code'], $row['institute_acronym'], $row['institute_address'], $row['phone'], $row['email'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_institute_acronym_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where institute_acronym in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['institutions_uid'], $row['institutions_name'], $row['institution_code'], $row['institute_acronym'], $row['institute_address'], $row['phone'], $row['email'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_institute_address($arg0) {
    $sql = self::$base_sql.' where institute_address = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['institutions_uid'], $row['institutions_name'], $row['institution_code'], $row['institute_acronym'], $row['institute_address'], $row['phone'], $row['email'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_institute_address_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where institute_address in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['institutions_uid'], $row['institutions_name'], $row['institution_code'], $row['institute_acronym'], $row['institute_address'], $row['phone'], $row['email'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_phone($arg0) {
    $sql = self::$base_sql.' where phone = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['institutions_uid'], $row['institutions_name'], $row['institution_code'], $row['institute_acronym'], $row['institute_address'], $row['phone'], $row['email'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_phone_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where phone in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['institutions_uid'], $row['institutions_name'], $row['institution_code'], $row['institute_acronym'], $row['institute_address'], $row['phone'], $row['email'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['institutions_uid'], $row['institutions_name'], $row['institution_code'], $row['institute_acronym'], $row['institute_address'], $row['phone'], $row['email'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['institutions_uid'], $row['institutions_name'], $row['institution_code'], $row['institute_acronym'], $row['institute_address'], $row['phone'], $row['email'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['institutions_uid'], $row['institutions_name'], $row['institution_code'], $row['institute_acronym'], $row['institute_address'], $row['phone'], $row['email'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['institutions_uid'], $row['institutions_name'], $row['institution_code'], $row['institute_acronym'], $row['institute_address'], $row['phone'], $row['email'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['institutions_uid'], $row['institutions_name'], $row['institution_code'], $row['institute_acronym'], $row['institute_address'], $row['phone'], $row['email'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['institutions_uid'], $row['institutions_name'], $row['institution_code'], $row['institute_acronym'], $row['institute_address'], $row['phone'], $row['email'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  /* end-auto-gen */
}