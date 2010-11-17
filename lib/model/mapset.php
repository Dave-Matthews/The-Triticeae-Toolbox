<?php
/**
 * Auto Generated Class
 * Represents a row from the table 'mapset'
 */
class mapset
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
  protected $mapset_uid = null;
  protected $mapset_name = null;
  protected $species = null;
  protected $map_type = null;
  protected $map_unit = null;
  protected $published_on = null;
  protected $updated_on = null;
  protected $created_on = null;


  public function __construct($mapset_uid, $mapset_name, $species, $map_type, $map_unit, $published_on, $updated_on, $created_on){
    $this->mapset_uid = $mapset_uid;
    $this->mapset_name = $mapset_name;
    $this->species = $species;
    $this->map_type = $map_type;
    $this->map_unit = $map_unit;
    $this->published_on = $published_on;
    $this->updated_on = $updated_on;
    $this->created_on = $created_on;

  }

  public function get_mapset_uid(){ return $this->mapset_uid; }
  public function get_mapset_name(){ return $this->mapset_name; }
  public function get_species(){ return $this->species; }
  public function get_map_type(){ return $this->map_type; }
  public function get_map_unit(){ return $this->map_unit; }
  public function get_published_on(){ return $this->published_on; }
  public function get_updated_on(){ return $this->updated_on; }
  public function get_created_on(){ return $this->created_on; }


  public function set_mapset_uid($arg0){ $this->mapset_uid = $arg0; }
  public function set_mapset_name($arg0){ $this->mapset_name = $arg0; }
  public function set_species($arg0){ $this->species = $arg0; }
  public function set_map_type($arg0){ $this->map_type = $arg0; }
  public function set_map_unit($arg0){ $this->map_unit = $arg0; }
  public function set_published_on($arg0){ $this->published_on = $arg0; }
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