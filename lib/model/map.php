<?php
/**
 * Auto Generated Class
 * Represents a row from the table 'map'
 */
class map
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
  protected $map_uid = null;
  protected $mapset_uid = null;
  protected $map_name = null;
  protected $map_start = null;
  protected $map_end = null;
  protected $updated_on = null;
  protected $created_on = null;


  public function __construct($map_uid, $mapset_uid, $map_name, $map_start, $map_end, $updated_on, $created_on){
    $this->map_uid = $map_uid;
    $this->mapset_uid = $mapset_uid;
    $this->map_name = $map_name;
    $this->map_start = $map_start;
    $this->map_end = $map_end;
    $this->updated_on = $updated_on;
    $this->created_on = $created_on;

  }

  public function get_map_uid(){ return $this->map_uid; }
  public function get_mapset_uid(){ return $this->mapset_uid; }
  public function get_map_name(){ return $this->map_name; }
  public function get_map_start(){ return $this->map_start; }
  public function get_map_end(){ return $this->map_end; }
  public function get_updated_on(){ return $this->updated_on; }
  public function get_created_on(){ return $this->created_on; }


  public function set_map_uid($arg0){ $this->map_uid = $arg0; }
  public function set_mapset_uid($arg0){ $this->mapset_uid = $arg0; }
  public function set_map_name($arg0){ $this->map_name = $arg0; }
  public function set_map_start($arg0){ $this->map_start = $arg0; }
  public function set_map_end($arg0){ $this->map_end = $arg0; }
  public function set_updated_on($arg0){ $this->updated_on = $arg0; }
  public function set_created_on($arg0){ $this->created_on = $arg0; }


  public function copy_from()
  {
    return get_object_vars($this);
  }

  protected function copy_to($var_arr)
  {
    $vars = get_class_vars(__CLASS__);
    foreach($vars as $varname => $value){
      $this->$varname = $var_arr[$varname];
    }
  }


  /* end-auto-gen */
}