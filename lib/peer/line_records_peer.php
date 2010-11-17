<?php
/**
 * Auto Generated Class
 * Contains methods for extracting rows from the table 'line_records'
 */
class line_records_peer
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
    protected static $base_sql = 'select line_record_uid, barley_pedigree_catalog_uid, taxa_uid, line_record_name, synonym, other_number, variety, pedigree_string, barley_type, origin, row_type, primary_end_use, record_status, breed_year, note, updated_on, created_on from line_records';


    // auto-generated method
  // get all records from db
  public static function get_all() {
    $results = array();
    $query = mysql_query(self::$base_sql);
    if (mysql_num_rows($query) <= 0) return $results;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $results[] =& new $modelname($row['line_record_uid'], $row['barley_pedigree_catalog_uid'], $row['taxa_uid'], $row['line_record_name'], $row['synonym'], $row['other_number'], $row['variety'], $row['pedigree_string'], $row['barley_type'], $row['origin'], $row['row_type'], $row['primary_end_use'], $row['record_status'], $row['breed_year'], $row['note'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['line_record_uid'], $row['barley_pedigree_catalog_uid'], $row['taxa_uid'], $row['line_record_name'], $row['synonym'], $row['other_number'], $row['variety'], $row['pedigree_string'], $row['barley_type'], $row['origin'], $row['row_type'], $row['primary_end_use'], $row['record_status'], $row['breed_year'], $row['note'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['line_record_uid'], $row['barley_pedigree_catalog_uid'], $row['taxa_uid'], $row['line_record_name'], $row['synonym'], $row['other_number'], $row['variety'], $row['pedigree_string'], $row['barley_type'], $row['origin'], $row['row_type'], $row['primary_end_use'], $row['record_status'], $row['breed_year'], $row['note'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_barley_pedigree_catalog_uid($arg0) {
    $sql = self::$base_sql.' where barley_pedigree_catalog_uid = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['line_record_uid'], $row['barley_pedigree_catalog_uid'], $row['taxa_uid'], $row['line_record_name'], $row['synonym'], $row['other_number'], $row['variety'], $row['pedigree_string'], $row['barley_type'], $row['origin'], $row['row_type'], $row['primary_end_use'], $row['record_status'], $row['breed_year'], $row['note'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_barley_pedigree_catalog_uid_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where barley_pedigree_catalog_uid in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['line_record_uid'], $row['barley_pedigree_catalog_uid'], $row['taxa_uid'], $row['line_record_name'], $row['synonym'], $row['other_number'], $row['variety'], $row['pedigree_string'], $row['barley_type'], $row['origin'], $row['row_type'], $row['primary_end_use'], $row['record_status'], $row['breed_year'], $row['note'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_taxa_uid($arg0) {
    $sql = self::$base_sql.' where taxa_uid = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['line_record_uid'], $row['barley_pedigree_catalog_uid'], $row['taxa_uid'], $row['line_record_name'], $row['synonym'], $row['other_number'], $row['variety'], $row['pedigree_string'], $row['barley_type'], $row['origin'], $row['row_type'], $row['primary_end_use'], $row['record_status'], $row['breed_year'], $row['note'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_taxa_uid_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where taxa_uid in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['line_record_uid'], $row['barley_pedigree_catalog_uid'], $row['taxa_uid'], $row['line_record_name'], $row['synonym'], $row['other_number'], $row['variety'], $row['pedigree_string'], $row['barley_type'], $row['origin'], $row['row_type'], $row['primary_end_use'], $row['record_status'], $row['breed_year'], $row['note'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_line_record_name($arg0) {
    $sql = self::$base_sql.' where line_record_name = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['line_record_uid'], $row['barley_pedigree_catalog_uid'], $row['taxa_uid'], $row['line_record_name'], $row['synonym'], $row['other_number'], $row['variety'], $row['pedigree_string'], $row['barley_type'], $row['origin'], $row['row_type'], $row['primary_end_use'], $row['record_status'], $row['breed_year'], $row['note'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_line_record_name_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where line_record_name in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['line_record_uid'], $row['barley_pedigree_catalog_uid'], $row['taxa_uid'], $row['line_record_name'], $row['synonym'], $row['other_number'], $row['variety'], $row['pedigree_string'], $row['barley_type'], $row['origin'], $row['row_type'], $row['primary_end_use'], $row['record_status'], $row['breed_year'], $row['note'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_synonym($arg0) {
    $sql = self::$base_sql.' where synonym = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['line_record_uid'], $row['barley_pedigree_catalog_uid'], $row['taxa_uid'], $row['line_record_name'], $row['synonym'], $row['other_number'], $row['variety'], $row['pedigree_string'], $row['barley_type'], $row['origin'], $row['row_type'], $row['primary_end_use'], $row['record_status'], $row['breed_year'], $row['note'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_synonym_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where synonym in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['line_record_uid'], $row['barley_pedigree_catalog_uid'], $row['taxa_uid'], $row['line_record_name'], $row['synonym'], $row['other_number'], $row['variety'], $row['pedigree_string'], $row['barley_type'], $row['origin'], $row['row_type'], $row['primary_end_use'], $row['record_status'], $row['breed_year'], $row['note'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_other_number($arg0) {
    $sql = self::$base_sql.' where other_number = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['line_record_uid'], $row['barley_pedigree_catalog_uid'], $row['taxa_uid'], $row['line_record_name'], $row['synonym'], $row['other_number'], $row['variety'], $row['pedigree_string'], $row['barley_type'], $row['origin'], $row['row_type'], $row['primary_end_use'], $row['record_status'], $row['breed_year'], $row['note'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_other_number_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where other_number in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['line_record_uid'], $row['barley_pedigree_catalog_uid'], $row['taxa_uid'], $row['line_record_name'], $row['synonym'], $row['other_number'], $row['variety'], $row['pedigree_string'], $row['barley_type'], $row['origin'], $row['row_type'], $row['primary_end_use'], $row['record_status'], $row['breed_year'], $row['note'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_variety($arg0) {
    $sql = self::$base_sql.' where variety = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['line_record_uid'], $row['barley_pedigree_catalog_uid'], $row['taxa_uid'], $row['line_record_name'], $row['synonym'], $row['other_number'], $row['variety'], $row['pedigree_string'], $row['barley_type'], $row['origin'], $row['row_type'], $row['primary_end_use'], $row['record_status'], $row['breed_year'], $row['note'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_variety_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where variety in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['line_record_uid'], $row['barley_pedigree_catalog_uid'], $row['taxa_uid'], $row['line_record_name'], $row['synonym'], $row['other_number'], $row['variety'], $row['pedigree_string'], $row['barley_type'], $row['origin'], $row['row_type'], $row['primary_end_use'], $row['record_status'], $row['breed_year'], $row['note'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_pedigree_string($arg0) {
    $sql = self::$base_sql.' where pedigree_string = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['line_record_uid'], $row['barley_pedigree_catalog_uid'], $row['taxa_uid'], $row['line_record_name'], $row['synonym'], $row['other_number'], $row['variety'], $row['pedigree_string'], $row['barley_type'], $row['origin'], $row['row_type'], $row['primary_end_use'], $row['record_status'], $row['breed_year'], $row['note'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_pedigree_string_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where pedigree_string in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['line_record_uid'], $row['barley_pedigree_catalog_uid'], $row['taxa_uid'], $row['line_record_name'], $row['synonym'], $row['other_number'], $row['variety'], $row['pedigree_string'], $row['barley_type'], $row['origin'], $row['row_type'], $row['primary_end_use'], $row['record_status'], $row['breed_year'], $row['note'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_barley_type($arg0) {
    $sql = self::$base_sql.' where barley_type = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['line_record_uid'], $row['barley_pedigree_catalog_uid'], $row['taxa_uid'], $row['line_record_name'], $row['synonym'], $row['other_number'], $row['variety'], $row['pedigree_string'], $row['barley_type'], $row['origin'], $row['row_type'], $row['primary_end_use'], $row['record_status'], $row['breed_year'], $row['note'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_barley_type_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where barley_type in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['line_record_uid'], $row['barley_pedigree_catalog_uid'], $row['taxa_uid'], $row['line_record_name'], $row['synonym'], $row['other_number'], $row['variety'], $row['pedigree_string'], $row['barley_type'], $row['origin'], $row['row_type'], $row['primary_end_use'], $row['record_status'], $row['breed_year'], $row['note'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_origin($arg0) {
    $sql = self::$base_sql.' where origin = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['line_record_uid'], $row['barley_pedigree_catalog_uid'], $row['taxa_uid'], $row['line_record_name'], $row['synonym'], $row['other_number'], $row['variety'], $row['pedigree_string'], $row['barley_type'], $row['origin'], $row['row_type'], $row['primary_end_use'], $row['record_status'], $row['breed_year'], $row['note'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_origin_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where origin in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['line_record_uid'], $row['barley_pedigree_catalog_uid'], $row['taxa_uid'], $row['line_record_name'], $row['synonym'], $row['other_number'], $row['variety'], $row['pedigree_string'], $row['barley_type'], $row['origin'], $row['row_type'], $row['primary_end_use'], $row['record_status'], $row['breed_year'], $row['note'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_row_type($arg0) {
    $sql = self::$base_sql.' where row_type = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['line_record_uid'], $row['barley_pedigree_catalog_uid'], $row['taxa_uid'], $row['line_record_name'], $row['synonym'], $row['other_number'], $row['variety'], $row['pedigree_string'], $row['barley_type'], $row['origin'], $row['row_type'], $row['primary_end_use'], $row['record_status'], $row['breed_year'], $row['note'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_row_type_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where row_type in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['line_record_uid'], $row['barley_pedigree_catalog_uid'], $row['taxa_uid'], $row['line_record_name'], $row['synonym'], $row['other_number'], $row['variety'], $row['pedigree_string'], $row['barley_type'], $row['origin'], $row['row_type'], $row['primary_end_use'], $row['record_status'], $row['breed_year'], $row['note'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_primary_end_use($arg0) {
    $sql = self::$base_sql.' where primary_end_use = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['line_record_uid'], $row['barley_pedigree_catalog_uid'], $row['taxa_uid'], $row['line_record_name'], $row['synonym'], $row['other_number'], $row['variety'], $row['pedigree_string'], $row['barley_type'], $row['origin'], $row['row_type'], $row['primary_end_use'], $row['record_status'], $row['breed_year'], $row['note'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_primary_end_use_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where primary_end_use in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['line_record_uid'], $row['barley_pedigree_catalog_uid'], $row['taxa_uid'], $row['line_record_name'], $row['synonym'], $row['other_number'], $row['variety'], $row['pedigree_string'], $row['barley_type'], $row['origin'], $row['row_type'], $row['primary_end_use'], $row['record_status'], $row['breed_year'], $row['note'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_record_status($arg0) {
    $sql = self::$base_sql.' where record_status = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['line_record_uid'], $row['barley_pedigree_catalog_uid'], $row['taxa_uid'], $row['line_record_name'], $row['synonym'], $row['other_number'], $row['variety'], $row['pedigree_string'], $row['barley_type'], $row['origin'], $row['row_type'], $row['primary_end_use'], $row['record_status'], $row['breed_year'], $row['note'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_record_status_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where record_status in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['line_record_uid'], $row['barley_pedigree_catalog_uid'], $row['taxa_uid'], $row['line_record_name'], $row['synonym'], $row['other_number'], $row['variety'], $row['pedigree_string'], $row['barley_type'], $row['origin'], $row['row_type'], $row['primary_end_use'], $row['record_status'], $row['breed_year'], $row['note'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_breed_year($arg0) {
    $sql = self::$base_sql.' where breed_year = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['line_record_uid'], $row['barley_pedigree_catalog_uid'], $row['taxa_uid'], $row['line_record_name'], $row['synonym'], $row['other_number'], $row['variety'], $row['pedigree_string'], $row['barley_type'], $row['origin'], $row['row_type'], $row['primary_end_use'], $row['record_status'], $row['breed_year'], $row['note'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_breed_year_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where breed_year in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['line_record_uid'], $row['barley_pedigree_catalog_uid'], $row['taxa_uid'], $row['line_record_name'], $row['synonym'], $row['other_number'], $row['variety'], $row['pedigree_string'], $row['barley_type'], $row['origin'], $row['row_type'], $row['primary_end_use'], $row['record_status'], $row['breed_year'], $row['note'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['line_record_uid'], $row['barley_pedigree_catalog_uid'], $row['taxa_uid'], $row['line_record_name'], $row['synonym'], $row['other_number'], $row['variety'], $row['pedigree_string'], $row['barley_type'], $row['origin'], $row['row_type'], $row['primary_end_use'], $row['record_status'], $row['breed_year'], $row['note'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['line_record_uid'], $row['barley_pedigree_catalog_uid'], $row['taxa_uid'], $row['line_record_name'], $row['synonym'], $row['other_number'], $row['variety'], $row['pedigree_string'], $row['barley_type'], $row['origin'], $row['row_type'], $row['primary_end_use'], $row['record_status'], $row['breed_year'], $row['note'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['line_record_uid'], $row['barley_pedigree_catalog_uid'], $row['taxa_uid'], $row['line_record_name'], $row['synonym'], $row['other_number'], $row['variety'], $row['pedigree_string'], $row['barley_type'], $row['origin'], $row['row_type'], $row['primary_end_use'], $row['record_status'], $row['breed_year'], $row['note'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['line_record_uid'], $row['barley_pedigree_catalog_uid'], $row['taxa_uid'], $row['line_record_name'], $row['synonym'], $row['other_number'], $row['variety'], $row['pedigree_string'], $row['barley_type'], $row['origin'], $row['row_type'], $row['primary_end_use'], $row['record_status'], $row['breed_year'], $row['note'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['line_record_uid'], $row['barley_pedigree_catalog_uid'], $row['taxa_uid'], $row['line_record_name'], $row['synonym'], $row['other_number'], $row['variety'], $row['pedigree_string'], $row['barley_type'], $row['origin'], $row['row_type'], $row['primary_end_use'], $row['record_status'], $row['breed_year'], $row['note'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['line_record_uid'], $row['barley_pedigree_catalog_uid'], $row['taxa_uid'], $row['line_record_name'], $row['synonym'], $row['other_number'], $row['variety'], $row['pedigree_string'], $row['barley_type'], $row['origin'], $row['row_type'], $row['primary_end_use'], $row['record_status'], $row['breed_year'], $row['note'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  /* end-auto-gen */
}