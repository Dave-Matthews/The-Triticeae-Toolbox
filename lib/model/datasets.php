<?php
/**
 * Auto Generated Class
 * Represents a row from the table 'datasets'
 */
class datasets
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
  protected $datasets_uid = null;
  protected $breeding_programs_uid = null;
  protected $dataset_name = null;
  protected $description = null;
  protected $date = null;
  protected $updated_on = null;
  protected $created_on = null;


  public function __construct($datasets_uid, $breeding_programs_uid, $dataset_name, $description, $date, $updated_on, $created_on){
    $this->datasets_uid = $datasets_uid;
    $this->breeding_programs_uid = $breeding_programs_uid;
    $this->dataset_name = $dataset_name;
    $this->description = $description;
    $this->date = $date;
    $this->updated_on = $updated_on;
    $this->created_on = $created_on;

  }

  public function get_datasets_uid(){ return $this->datasets_uid; }
  public function get_breeding_programs_uid(){ return $this->breeding_programs_uid; }
  public function get_dataset_name(){ return $this->dataset_name; }
  public function get_description(){ return $this->description; }
  public function get_date(){ return $this->date; }
  public function get_updated_on(){ return $this->updated_on; }
  public function get_created_on(){ return $this->created_on; }


  public function set_datasets_uid($arg0){ $this->datasets_uid = $arg0; }
  public function set_breeding_programs_uid($arg0){ $this->breeding_programs_uid = $arg0; }
  public function set_dataset_name($arg0){ $this->dataset_name = $arg0; }
  public function set_description($arg0){ $this->description = $arg0; }
  public function set_date($arg0){ $this->date = $arg0; }
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