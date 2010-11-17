<?php
/**
 * Auto Generated Class
 * Contains methods for extracting rows from the table 'pedigree_relations'
 */
class pedigree_relations_peer
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
    protected static $base_sql = 'select pedigree_relation_uid, line_record_uid, parent_id, relation, contribution, selfing, comments, updated_on, created_on from pedigree_relations';


    // auto-generated method
  // get all records from db
  public static function get_all() {
    $results = array();
    $query = mysql_query(self::$base_sql);
    if (mysql_num_rows($query) <= 0) return $results;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $results[] =& new $modelname($row['pedigree_relation_uid'], $row['line_record_uid'], $row['parent_id'], $row['relation'], $row['contribution'], $row['selfing'], $row['comments'], $row['updated_on'], $row['created_on']);
    }
    return $results;
  }

  
  # auto-generated function
  public static function get_by_pedigree_relation_uid($arg0) {
    $sql = self::$base_sql.' where pedigree_relation_uid = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['pedigree_relation_uid'], $row['line_record_uid'], $row['parent_id'], $row['relation'], $row['contribution'], $row['selfing'], $row['comments'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_pedigree_relation_uid_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where pedigree_relation_uid in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['pedigree_relation_uid'], $row['line_record_uid'], $row['parent_id'], $row['relation'], $row['contribution'], $row['selfing'], $row['comments'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['pedigree_relation_uid'], $row['line_record_uid'], $row['parent_id'], $row['relation'], $row['contribution'], $row['selfing'], $row['comments'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['pedigree_relation_uid'], $row['line_record_uid'], $row['parent_id'], $row['relation'], $row['contribution'], $row['selfing'], $row['comments'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_parent_id($arg0) {
    $sql = self::$base_sql.' where parent_id = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['pedigree_relation_uid'], $row['line_record_uid'], $row['parent_id'], $row['relation'], $row['contribution'], $row['selfing'], $row['comments'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_parent_id_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where parent_id in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['pedigree_relation_uid'], $row['line_record_uid'], $row['parent_id'], $row['relation'], $row['contribution'], $row['selfing'], $row['comments'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_relation($arg0) {
    $sql = self::$base_sql.' where relation = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['pedigree_relation_uid'], $row['line_record_uid'], $row['parent_id'], $row['relation'], $row['contribution'], $row['selfing'], $row['comments'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_relation_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where relation in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['pedigree_relation_uid'], $row['line_record_uid'], $row['parent_id'], $row['relation'], $row['contribution'], $row['selfing'], $row['comments'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_contribution($arg0) {
    $sql = self::$base_sql.' where contribution = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['pedigree_relation_uid'], $row['line_record_uid'], $row['parent_id'], $row['relation'], $row['contribution'], $row['selfing'], $row['comments'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_contribution_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where contribution in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['pedigree_relation_uid'], $row['line_record_uid'], $row['parent_id'], $row['relation'], $row['contribution'], $row['selfing'], $row['comments'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_selfing($arg0) {
    $sql = self::$base_sql.' where selfing = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['pedigree_relation_uid'], $row['line_record_uid'], $row['parent_id'], $row['relation'], $row['contribution'], $row['selfing'], $row['comments'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_selfing_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where selfing in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['pedigree_relation_uid'], $row['line_record_uid'], $row['parent_id'], $row['relation'], $row['contribution'], $row['selfing'], $row['comments'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_comments($arg0) {
    $sql = self::$base_sql.' where comments = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['pedigree_relation_uid'], $row['line_record_uid'], $row['parent_id'], $row['relation'], $row['contribution'], $row['selfing'], $row['comments'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_comments_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where comments in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['pedigree_relation_uid'], $row['line_record_uid'], $row['parent_id'], $row['relation'], $row['contribution'], $row['selfing'], $row['comments'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['pedigree_relation_uid'], $row['line_record_uid'], $row['parent_id'], $row['relation'], $row['contribution'], $row['selfing'], $row['comments'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['pedigree_relation_uid'], $row['line_record_uid'], $row['parent_id'], $row['relation'], $row['contribution'], $row['selfing'], $row['comments'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['pedigree_relation_uid'], $row['line_record_uid'], $row['parent_id'], $row['relation'], $row['contribution'], $row['selfing'], $row['comments'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['pedigree_relation_uid'], $row['line_record_uid'], $row['parent_id'], $row['relation'], $row['contribution'], $row['selfing'], $row['comments'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  /* end-auto-gen */
}