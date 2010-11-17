<?php
/**
 * Auto Generated Class
 * Represents a row from the table 'datasets_breeders'
 */
class datasets_breeders
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
  protected $datasets_breaders_uid = null;
  protected $datasets_uid = null;
  protected $breeding_programs_uid = null;
  protected $updated_on = null;
  protected $created_on = null;


  public function __construct($datasets_breaders_uid, $datasets_uid, $breeding_programs_uid, $updated_on, $created_on){
    $this->datasets_breaders_uid = $datasets_breaders_uid;
    $this->datasets_uid = $datasets_uid;
    $this->breeding_programs_uid = $breeding_programs_uid;
    $this->updated_on = $updated_on;
    $this->created_on = $created_on;

  }

  public function get_datasets_breaders_uid(){ return $this->datasets_breaders_uid; }
  public function get_datasets_uid(){ return $this->datasets_uid; }
  public function get_breeding_programs_uid(){ return $this->breeding_programs_uid; }
  public function get_updated_on(){ return $this->updated_on; }
  public function get_created_on(){ return $this->created_on; }


  public function set_datasets_breaders_uid($arg0){ $this->datasets_breaders_uid = $arg0; }
  public function set_datasets_uid($arg0){ $this->datasets_uid = $arg0; }
  public function set_breeding_programs_uid($arg0){ $this->breeding_programs_uid = $arg0; }
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