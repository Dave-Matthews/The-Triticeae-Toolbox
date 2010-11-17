<?php
/**
 * Auto Generated Class
 * Represents a row from the table 'breeding_programs'
 */
class breeding_programs
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
  protected $breeding_programs_uid = null;
  protected $institutions_uid = null;
  protected $breeding_programs_name = null;
  protected $description = null;
  protected $updated_on = null;
  protected $created_on = null;


  public function __construct($breeding_programs_uid, $institutions_uid, $breeding_programs_name, $description, $updated_on, $created_on){
    $this->breeding_programs_uid = $breeding_programs_uid;
    $this->institutions_uid = $institutions_uid;
    $this->breeding_programs_name = $breeding_programs_name;
    $this->description = $description;
    $this->updated_on = $updated_on;
    $this->created_on = $created_on;

  }

  public function get_breeding_programs_uid(){ return $this->breeding_programs_uid; }
  public function get_institutions_uid(){ return $this->institutions_uid; }
  public function get_breeding_programs_name(){ return $this->breeding_programs_name; }
  public function get_description(){ return $this->description; }
  public function get_updated_on(){ return $this->updated_on; }
  public function get_created_on(){ return $this->created_on; }


  public function set_breeding_programs_uid($arg0){ $this->breeding_programs_uid = $arg0; }
  public function set_institutions_uid($arg0){ $this->institutions_uid = $arg0; }
  public function set_breeding_programs_name($arg0){ $this->breeding_programs_name = $arg0; }
  public function set_description($arg0){ $this->description = $arg0; }
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