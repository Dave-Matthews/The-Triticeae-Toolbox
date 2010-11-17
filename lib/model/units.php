<?php
/**
 * Auto Generated Class
 * Represents a row from the table 'units'
 */
class units
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
  protected $unit_uid = null;
  protected $unit_name = null;
  protected $unit_abbreviation = null;
  protected $unit_description = null;
  protected $updated_on = null;
  protected $created_on = null;


  public function __construct($unit_uid, $unit_name, $unit_abbreviation, $unit_description, $updated_on, $created_on){
    $this->unit_uid = $unit_uid;
    $this->unit_name = $unit_name;
    $this->unit_abbreviation = $unit_abbreviation;
    $this->unit_description = $unit_description;
    $this->updated_on = $updated_on;
    $this->created_on = $created_on;

  }

  public function get_unit_uid(){ return $this->unit_uid; }
  public function get_unit_name(){ return $this->unit_name; }
  public function get_unit_abbreviation(){ return $this->unit_abbreviation; }
  public function get_unit_description(){ return $this->unit_description; }
  public function get_updated_on(){ return $this->updated_on; }
  public function get_created_on(){ return $this->created_on; }


  public function set_unit_uid($arg0){ $this->unit_uid = $arg0; }
  public function set_unit_name($arg0){ $this->unit_name = $arg0; }
  public function set_unit_abbreviation($arg0){ $this->unit_abbreviation = $arg0; }
  public function set_unit_description($arg0){ $this->unit_description = $arg0; }
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