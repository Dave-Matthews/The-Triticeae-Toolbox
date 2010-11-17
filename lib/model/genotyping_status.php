<?php
/**
 * Auto Generated Class
 * Represents a row from the table 'genotyping_status'
 */
class genotyping_status
{
  # IMPORTANT: Do not modify this file
  /* begin-auto-gen */
  protected $genotyping_status_uid = null;
  protected $genotyping_status_name = null;
  protected $description = null;
  protected $updated_on = null;
  protected $created_on = null;


  public function __construct($genotyping_status_uid, $genotyping_status_name, $description, $updated_on, $created_on){
    $this->genotyping_status_uid = $genotyping_status_uid;
    $this->genotyping_status_name = $genotyping_status_name;
    $this->description = $description;
    $this->updated_on = $updated_on;
    $this->created_on = $created_on;

  }

  public function get_genotyping_status_uid(){ return $this->genotyping_status_uid; }
  public function get_genotyping_status_name(){ return $this->genotyping_status_name; }
  public function get_description(){ return $this->description; }
  public function get_updated_on(){ return $this->updated_on; }
  public function get_created_on(){ return $this->created_on; }


  public function set_genotyping_status_uid($arg0){ $this->genotyping_status_uid = $arg0; }
  public function set_genotyping_status_name($arg0){ $this->genotyping_status_name = $arg0; }
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