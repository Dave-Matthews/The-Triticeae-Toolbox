<?php
/**
 * Auto Generated Class
 * Represents a row from the table 'markers_in_maps'
 */
class markers_in_maps
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
  protected $markers_in_maps_uid = null;
  protected $marker_uid = null;
  protected $map_uid = null;
  protected $start_position = null;
  protected $end_position = null;
  protected $bin_name = null;
  protected $chromosome = null;
  protected $updated_on = null;
  protected $created_on = null;


  public function __construct($markers_in_maps_uid, $marker_uid, $map_uid, $start_position, $end_position, $bin_name, $chromosome, $updated_on, $created_on){
    $this->markers_in_maps_uid = $markers_in_maps_uid;
    $this->marker_uid = $marker_uid;
    $this->map_uid = $map_uid;
    $this->start_position = $start_position;
    $this->end_position = $end_position;
    $this->bin_name = $bin_name;
    $this->chromosome = $chromosome;
    $this->updated_on = $updated_on;
    $this->created_on = $created_on;

  }

  public function get_markers_in_maps_uid(){ return $this->markers_in_maps_uid; }
  public function get_marker_uid(){ return $this->marker_uid; }
  public function get_map_uid(){ return $this->map_uid; }
  public function get_start_position(){ return $this->start_position; }
  public function get_end_position(){ return $this->end_position; }
  public function get_bin_name(){ return $this->bin_name; }
  public function get_chromosome(){ return $this->chromosome; }
  public function get_updated_on(){ return $this->updated_on; }
  public function get_created_on(){ return $this->created_on; }


  public function set_markers_in_maps_uid($arg0){ $this->markers_in_maps_uid = $arg0; }
  public function set_marker_uid($arg0){ $this->marker_uid = $arg0; }
  public function set_map_uid($arg0){ $this->map_uid = $arg0; }
  public function set_start_position($arg0){ $this->start_position = $arg0; }
  public function set_end_position($arg0){ $this->end_position = $arg0; }
  public function set_bin_name($arg0){ $this->bin_name = $arg0; }
  public function set_chromosome($arg0){ $this->chromosome = $arg0; }
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