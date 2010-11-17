<?php
/**
 * Auto Generated Class
 * Contains methods for extracting rows from the table 'experiments'
 */
class experiments_peer
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
    protected static $base_sql = 'select experiment_uid, experiment_type_uid, datasets_uid, experiment_name, experiment_year, planting_date, seeding_rate, harvest_date, experiment_design, number_replications, plot_size, harvest_area, irrigation, collect_site_name, longitude, latitude, other_remarks, updated_on, created_on from experiments';


    // auto-generated method
  // get all records from db
  public static function get_all() {
    $results = array();
    $query = mysql_query(self::$base_sql);
    if (mysql_num_rows($query) <= 0) return $results;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $results[] =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_experiment_type_uid($arg0) {
    $sql = self::$base_sql.' where experiment_type_uid = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_experiment_type_uid_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where experiment_type_uid in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_experiment_name($arg0) {
    $sql = self::$base_sql.' where experiment_name = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_experiment_name_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where experiment_name in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_experiment_year($arg0) {
    $sql = self::$base_sql.' where experiment_year = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_experiment_year_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where experiment_year in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_planting_date($arg0) {
    $sql = self::$base_sql.' where planting_date = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_planting_date_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where planting_date in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_seeding_rate($arg0) {
    $sql = self::$base_sql.' where seeding_rate = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_seeding_rate_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where seeding_rate in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_harvest_date($arg0) {
    $sql = self::$base_sql.' where harvest_date = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_harvest_date_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where harvest_date in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_experiment_design($arg0) {
    $sql = self::$base_sql.' where experiment_design = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_experiment_design_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where experiment_design in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_number_replications($arg0) {
    $sql = self::$base_sql.' where number_replications = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_number_replications_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where number_replications in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_plot_size($arg0) {
    $sql = self::$base_sql.' where plot_size = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_plot_size_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where plot_size in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_harvest_area($arg0) {
    $sql = self::$base_sql.' where harvest_area = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_harvest_area_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where harvest_area in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_irrigation($arg0) {
    $sql = self::$base_sql.' where irrigation = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_irrigation_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where irrigation in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_collect_site_name($arg0) {
    $sql = self::$base_sql.' where collect_site_name = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_collect_site_name_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where collect_site_name in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_longitude($arg0) {
    $sql = self::$base_sql.' where longitude = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_longitude_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where longitude in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_latitude($arg0) {
    $sql = self::$base_sql.' where latitude = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_latitude_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where latitude in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  # auto-generated function
  public static function get_by_other_remarks($arg0) {
    $sql = self::$base_sql.' where other_remarks = \''.$arg0.'\' limit 1';
    $query = mysql_query($sql);
    if (mysql_num_rows($query) <= 0) return null;
    $row = mysql_fetch_assoc($query);
    $modelname = substr(__CLASS__, 0, -5);
    $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
    return $temp;
  }

  # auto-generated function
  public static function get_by_other_remarks_array(array $arg0) {
    if (empty($arg0)) return null;
    $sql = self::$base_sql.' where other_remarks in ('.implode(',', $arg0).')';
    $query = mysql_query($sql);
    $results = null;
    while ($row = mysql_fetch_assoc($query)) {
      $modelname = substr(__CLASS__, 0, -5);
      $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
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
    $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
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
      $temp =& new $modelname($row['experiment_uid'], $row['experiment_type_uid'], $row['datasets_uid'], $row['experiment_name'], $row['experiment_year'], $row['planting_date'], $row['seeding_rate'], $row['harvest_date'], $row['experiment_design'], $row['number_replications'], $row['plot_size'], $row['harvest_area'], $row['irrigation'], $row['collect_site_name'], $row['longitude'], $row['latitude'], $row['other_remarks'], $row['updated_on'], $row['created_on']);
      $results[] = $temp;
    }
    return $results;
  }

  /* end-auto-gen */
}